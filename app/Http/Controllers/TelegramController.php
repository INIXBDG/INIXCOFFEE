<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class TelegramController extends Controller
{

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
