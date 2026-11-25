<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\TicketController;

class TelegramController extends Controller
{
    private $botToken;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
    }

    private function internalTicketToken(): string
    {
        $configured = (string) config('services.internal_api.ticket_token', '');
        if ($configured !== '') {
            return $configured;
        }

        return hash_hmac('sha256', 'internal-ticket-token', (string) config('app.key'));
    }

    /**
     * Entry point Webhook Telegram (Menerima klik tombol & chat)
     */
    public function webhook(Request $request)
    {
        $webhookSecret = (string) config('services.telegram.webhook_secret', '');
        $providedSecret = (string) $request->header('X-Telegram-Bot-Api-Secret-Token', '');

        if ($webhookSecret === '') {
            if (app()->isProduction()) {
                Log::error('TELEGRAM_WEBHOOK_SECRET belum dikonfigurasi pada production.');
                return response()->json(['message' => 'Webhook secret not configured'], 500);
            }
        } elseif (!hash_equals($webhookSecret, $providedSecret)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $update = $request->all();
        Log::info('Telegram Webhook Masuk:', $update);

        // ---------------------------------------------------
        // KASUS 1: JIKA DARI KLIK TOMBOL (Callback Query)
        // ---------------------------------------------------
        if (isset($update['callback_query'])) {
            $callback   = $update['callback_query'];
            $telegramId = $callback['from']['id'];
            $firstName  = $callback['from']['first_name'] ?? 'User';
            $data       = $callback['data'] ?? '';

            // Dapatkan nama asli penekan tombol
            $picName = $this->getRealName($telegramId, $firstName);

            $parts    = explode(':', $data, 2);
            $action   = $parts[0] ?? '';
            $ticketId = $parts[1] ?? '';

            Log::info("Aksi Tombol: {$action} | Ticket ID: {$ticketId} | PIC: {$picName}");

            $result = $this->processTicketUpdate(
                $action,
                $ticketId,
                $picName,
                $action === 'reject' ? 'Ditolak via Telegram' : 'Diselesaikan via Telegram'
            );
            $resultData = method_exists($result, 'getData') ? $result->getData(true) : [];
            $success = method_exists($result, 'getStatusCode') && $result->getStatusCode() < 300;
            $callbackMessage = $resultData['message'] ?? ($success ? 'Tiket berhasil diproses.' : 'Tiket gagal diproses.');

            // Beritahu Telegram agar loading di tombol berhenti
            $this->answerCallback($callback['id'], $callbackMessage);

            if ($success && isset($callback['message']['chat']['id'], $callback['message']['message_id'])) {
                Http::withOptions(['curl' => [CURLOPT_RESOLVE => ['api.telegram.org:443:149.154.166.110'], CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]])->timeout(10)->post("https://api.telegram.org/bot{$this->botToken}/editMessageReplyMarkup", [
                    'chat_id' => $callback['message']['chat']['id'],
                    'message_id' => $callback['message']['message_id'],
                    'reply_markup' => json_encode(['inline_keyboard' => []]),
                ]);
            }

            return response()->json([
                'status' => 'callback_processed',
                'message' => $callbackMessage,
            ]);
        }

        // ---------------------------------------------------
        // KASUS 2: JIKA DARI PESAN TEKS MANUAL
        // ---------------------------------------------------
        if (isset($update['message']['text'])) {
            $text       = trim($update['message']['text']);
            $telegramId = $update['message']['from']['id'] ?? null;
            $firstName  = $update['message']['from']['first_name'] ?? 'User';
            $picName    = $this->getRealName($telegramId, $firstName);

            if (preg_match('/^\/(terima|tolak|selesai)(?:@[A-Za-z0-9_]+)?\s+(\S+)(?:\s+(.+))?$/isu', $text, $matches)) {
                $command = strtolower($matches[1]);
                $action = [
                    'terima' => 'accept',
                    'tolak' => 'reject',
                    'selesai' => 'finish',
                ][$command];
                $ticketId = trim($matches[2]);
                $keterangan = trim($matches[3] ?? '') ?: ($action === 'reject'
                    ? 'Ditolak via Telegram'
                    : 'Diselesaikan via Telegram');

                $result = $this->processTicketUpdate($action, $ticketId, $picName, $keterangan);
                $resultData = method_exists($result, 'getData') ? $result->getData(true) : [];

                return response()->json([
                    'status' => 'text_processed',
                    'message' => $resultData['message'] ?? 'Perintah diproses.',
                ]);
            }

            return response()->json(['status' => 'text_ignored']);
        }

        return response()->json(['status' => 'ignored']);
    }

    private function answerCallback($callbackId, $message)
    {
        Http::withOptions(['curl' => [CURLOPT_RESOLVE => ['api.telegram.org:443:149.154.166.110'], CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]])->timeout(10)->post("https://api.telegram.org/bot{$this->botToken}/answerCallbackQuery", [
            'callback_query_id' => $callbackId,
            'text'              => mb_substr($message, 0, 200),
        ]);
    }

    /**
     * Memanggil fungsi update tiket internal di TicketController.
     */
    private function processTicketUpdate($action, $ticketId, $picName, $keterangan = null)
    {
        $internalReq = new Request([
            'action'     => $action,
            'ticket_id'  => $ticketId,
            'pic_name'   => $picName,
            'keterangan' => $keterangan ?? 'Diproses via Telegram',
        ]);

        $internalReq->headers->set('X-Internal-Token', $this->internalTicketToken());

        return app(TicketController::class)->handleInternalUpdate($internalReq);
    }

    /**
     * Mapping ID Telegram ke Nama Asli Karyawan.
     */
    private function getRealName($telegramId, $telegramFirstName)
    {
        $userMap = [
           '6284939842' => 'Juli',
            '2021670238' => 'Ardhan',
            '1564401546' => 'Ferdi',
            '1050252661' => 'Eggi',
            '5004624382' => 'Sergio',
            '6619591483' => 'Valen',
            '8433454495' => 'Stephan',
            '7219122230' => 'Eurisko',
            '5831857683' => 'Vicky',
            '8261377656' => 'Yendra',
            '8019408343' => 'Ravael',
        ];

        return $userMap[$telegramId] ?? $telegramFirstName;
    }

    /**
     * Helper untuk Set Webhook langsung lewat browser.
     */
    public function setWebhook(Request $request)
    {
        $webhookUrl = config('services.telegram.webhook_url');
        $webhookSecret = config('services.telegram.webhook_secret');

        if (!$this->botToken || !$webhookUrl) {
            return response()->json([
                'message' => 'TELEGRAM_BOT_TOKEN dan TELEGRAM_WEBHOOK_URL wajib diisi.',
            ], 422);
        }

        $payload = ['url' => $webhookUrl];
        if ($webhookSecret) {
            $payload['secret_token'] = $webhookSecret;
        }

        $response = Http::withOptions(['curl' => [CURLOPT_RESOLVE => ['api.telegram.org:443:149.154.166.110'], CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]])->timeout(10)->post("https://api.telegram.org/bot{$this->botToken}/setWebhook", $payload);

        return response()->json([
            'target_url' => $payload['url'],
            'telegram_response' => $response->json(),
        ], $response->successful() ? 200 : 502);
    }
}
