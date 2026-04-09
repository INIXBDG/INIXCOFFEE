<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class TelegramController extends Controller
{
    private const PERSONAL_BOT_TOKEN = '8619211414:AAHnpchtKmY_FEKrOnj1VQTUsYKqp3Smuhw';
    private const PERSONAL_CHAT_ID = '-1003758833562';

    public function sendPersonalTelegramMessage(array $data): bool
    {
        try {
            $title = $data['title'] ?? 'Notifikasi';
            $message = $data['message'] ?? '';
            $url = $data['url'] ?? null;
            $buttons = $data['buttons'] ?? [];

            $text = "<b>{$title}</b>\n";
            $text .= "──────────────\n";
            $text .= strip_tags($message);

            if ($url) {
                $text .= "\n🔗 <a href=\"{$url}\">Lihat Detail</a>";
            }

            $payload = [
                'chat_id' => self::PERSONAL_CHAT_ID,
                'text' => $text,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ];

            if (!empty($buttons)) {
                $keyboard = array_map(
                    fn($btn) => [
                        'text' => $btn['text'],
                        'url' => $btn['url'],
                    ],
                    $buttons,
                );

                $payload['reply_markup'] = json_encode([
                    'inline_keyboard' => [$keyboard],
                ]);
            }

            $response = Http::timeout(10)->post('https://api.telegram.org/bot' . self::PERSONAL_BOT_TOKEN . '/sendMessage', $payload);

            if ($response->successful()) {
                Log::info('Personal Telegram sent', ['chat_id' => self::PERSONAL_CHAT_ID]);
                return true;
            }

            Log::error('Failed to send personal Telegram', [
                'response' => $response->body(),
                'payload' => $payload,
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Telegram personal send error: ' . $e->getMessage());
            return false;
        }
    }

    public function formatPersonalCoordinationMessage(array $coordinationData): array
    {
        $id = $coordinationData['id_pengajuan'] ?? '-';
        $creator = $coordinationData['creator_name'] ?? '-';
        $driver = $coordinationData['driver_name'] ?? '-';
        $budget = isset($coordinationData['budget']) && $coordinationData['budget'] ? 'Rp ' . number_format($coordinationData['budget'], 0, ',', '.') : 'Tidak Ada Budget';
        $time = isset($coordinationData['tanggal_pembuatan']) ? \Carbon\Carbon::parse($coordinationData['tanggal_pembuatan'])->format('d M Y, H:i') : '-';
        $status = $coordinationData['status_text'] ?? '-';
        $statusApply = $coordinationData['status_apply'] ?? 0;
        $logText = $coordinationData['log_text'] ?? null;

        $detailsText = '';
        if ($logText) {
            $detailsText = $logText;
        } else {
            $tipes = $coordinationData['tipe'] ?? [];
            $lokasis = $coordinationData['lokasi'] ?? [];
            $tanggals = $coordinationData['tanggal'] ?? [];
            $waktus = $coordinationData['waktu'] ?? [];
            $details = $coordinationData['detail'] ?? [];

            if (is_array($tipes) && count($tipes) > 0) {
                foreach ($tipes as $i => $tipe) {
                    $lokasi = $lokasis[$i] ?? '-';
                    $tanggal = $tanggals[$i] ?? '-';
                    $waktu = $waktus[$i] ?? '-';
                    $info = $details[$i] ?? '-';
                    $detailsText .= "• <b>{$tipe}</b>\n  {$lokasi}\n  {$tanggal} | {$waktu}\n  {$info}\n\n";
                }
            }
        }

        $message = "ID: <code>#{$id}</code>\n" . "Dibuat: {$creator}\n" . "Driver: {$driver}\n" . "Budget: {$budget}\n" . "Waktu: {$time}\n" . "Status: {$status}\n" . "──────────────\n" . ($detailsText ? "<b>Rincian Perjalanan:</b>\n{$detailsText}" : '');

        $baseUrl = url('/office/pickup-driver');
        $detailUrl = "{$baseUrl}/{$id}";
        $buttons = [['text' => '🔍 Lihat Detail', 'url' => $detailUrl]];

        if ($statusApply == 0) {
            $token = substr(md5($id . now()->timestamp . self::PERSONAL_CHAT_ID), 0, 10);
            $buttons[] = ['text' => '✅ Terima', 'url' => "{$baseUrl}/action/terima/{$id}?token={$token}&from_personal=1"];
        } elseif ($statusApply == 1) {
            $token = substr(md5($id . now()->timestamp . self::PERSONAL_CHAT_ID), 0, 10);
            $buttons[] = ['text' => '🏁 Selesaikan', 'url' => "{$baseUrl}/action/selesaikan/{$id}?token={$token}&from_personal=1"];
        }

        return [
            'title' => $coordinationData['title'] ?? '🆕 Koordinasi Driver',
            'message' => $message,
            'url' => $detailUrl,
            'buttons' => $buttons,
            'id' => $id,
            'status_apply' => $statusApply,
        ];
    }

    public function webhook(Request $request)
    {
        $update = $request->all();
        Log::info('Telegram update:', $update);

        $chatId = null;
        $username = null;

        if (isset($update['message']['chat']['id'])) {
            $chatId = $update['message']['chat']['id'];
            $username = $update['message']['chat']['username'] ?? null;
        } elseif (isset($update['callback_query']['message']['chat']['id'])) {
            $chatId = $update['callback_query']['message']['chat']['id'];
            $username = $update['callback_query']['from']['username'] ?? null;
        }

        if ($chatId) {
            Log::info("Chat ID dari user @$username adalah: $chatId");
        } else {
            Log::warning('Tidak ditemukan chat ID pada update ini.');
        }

        return response()->json(['status' => 'ok']);
    }

    public function test()
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $webhookUrl = 'https://48b97abbd17b.ngrok-free.app/telegram/webhook';

        $response = Http::post("https://api.telegram.org/bot$token/setWebhook", [
            'url' => $webhookUrl,
        ]);

        if ($response->successful()) {
            echo 'Webhook berhasil diset.';
            dd($response->json());
        } else {
            echo 'Gagal menyetel webhook.';
            dd($response->body());
        }
    }
}
