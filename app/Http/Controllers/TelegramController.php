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
        $this->botToken = env('TELEGRAM_BOT_TOKEN');
    }

    /**
     * Entry point Webhook Telegram (Menerima klik tombol & chat)
     */
    public function webhook(Request $request)
    {
        $update = $request->all();
        Log::info('Telegram Webhook Masuk:', $update);

        // ---------------------------------------------------
        // KASUS 1: JIKA DARI KLIK TOMBOL (Callback Query)
        // ---------------------------------------------------
        if (isset($update['callback_query'])) {
            $callback   = $update['callback_query'];
            $telegramId = $callback['from']['id'];
            $firstName  = $callback['from']['first_name'] ?? 'User';
            $data       = $callback['data']; // Format: action:ticket_id (contoh: accept:NIX260917b)

            // Dapatkan nama asli penekan tombol
            $picName = $this->getRealName($telegramId, $firstName);

            $parts    = explode(':', $data);
            $action   = $parts[0] ?? '';
            $ticketId = $parts[1] ?? '';

            Log::info("Aksi Tombol: {$action} | Ticket ID: {$ticketId} | PIC: {$picName}");

            // Panggil fungsi pemroses tiket
            if ($action === 'accept') {
                $this->processTicketUpdate('accept', $ticketId, $picName);
            } elseif ($action === 'reject') {
                $this->processTicketUpdate('reject', $ticketId, $picName, 'Ditolak via Telegram');
            } elseif ($action === 'finish') {
                $this->processTicketUpdate('finish', $ticketId, $picName, 'Diselesaikan via Telegram');
            }

            // Beritahu Telegram agar loading di tombol berhenti
            Http::withoutVerifying()->post("https://api.telegram.org/bot{$this->botToken}/answerCallbackQuery", [
                'callback_query_id' => $callback['id'],
                'text'              => "Tiket {$ticketId} berhasil diproses!",
            ]);

            return response()->json(['status' => 'callback_processed']);
        }

        // ---------------------------------------------------
        // KASUS 2: JIKA DARI PESAN TEKS MANUAL (Contoh: /terima NIX...)
        // ---------------------------------------------------
        if (isset($update['message']['text'])) {
            $text       = trim($update['message']['text']);
            $telegramId = $update['message']['from']['id'];
            $firstName  = $update['message']['from']['first_name'] ?? 'User';
            $picName    = $this->getRealName($telegramId, $firstName);

            // Perintah /terima
            if (strpos($text, '/terima ') === 0) {
                $ticketId = trim(substr($text, 8));
                $this->processTicketUpdate('accept', $ticketId, $picName);
            }
            // Perintah /selesai
            elseif (strpos($text, '/selesai ') === 0) {
                $parts      = explode(' ', $text, 3);
                $ticketId   = $parts[1] ?? null;
                $keterangan = $parts[2] ?? 'Diselesaikan via Telegram';

                if ($ticketId) {
                    $this->processTicketUpdate('finish', $ticketId, $picName, $keterangan);
                }
            }

            return response()->json(['status' => 'text_processed']);
        }

        return response()->json(['status' => 'ignored']);
    }

    /**
     * Memanggil fungsi update tiket internal di TicketController
     */
    private function processTicketUpdate($action, $ticketId, $picName, $keterangan = null)
    {
        $internalReq = new Request([
            'action'     => $action,
            'ticket_id'  => $ticketId,
            'pic_name'   => $picName,
            'keterangan' => $keterangan ?? 'Diproses via Telegram',
        ]);

        $internalReq->headers->set('X-Internal-Token', 'TOKEN_RAHASIA_KITA_123');

        $ticketController = app(TicketController::class);
        return $ticketController->handleInternalUpdate($internalReq);
    }

    /**
     * Mapping ID Telegram ke Nama Asli Karyawan
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
            '8019408343' => 'Ravael', // ID Telegram Anda
        ];

        return $userMap[$telegramId] ?? $telegramFirstName;
    }

    /**
     * Helper untuk Set Webhook langsung lewat browser
     */
    public function setWebhook(Request $request)
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        
        

        $response = Http::withoutVerifying()->post("https://api.telegram.org/bot{$token}/setWebhook", [
            'url' => $targetUrl,
        ]);

        return response()->json([
            'target_url' => $targetUrl,
            'telegram_response' => $response->json(),
        ]);
    }
}
//