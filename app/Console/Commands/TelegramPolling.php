<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\PickupDriverTelegramService;

class TelegramPolling extends Command
{
    protected $signature = 'telegram:poll';

    protected $description = 'Polling Telegram Callback untuk PickupDriver';

    private const PERSONAL_BOT_TOKEN = '8619211414:AAHnpchtKmY_FEKrOnj1VQTUsYKqp3Smuhw';

    public function handle()
    {
        $token = env('8619211414:AAHnpchtKmY_FEKrOnj1VQTUsYKqp3Smuhw');

        $service = app(PickupDriverTelegramService::class);

        while (true) {

            try {

                $offset = Cache::get('telegram_offset', 0);

                $response = Http::timeout(35)
                    ->get(
                        'https://api.telegram.org/bot' . self::PERSONAL_BOT_TOKEN . '/getUpdates',
                        [
                            'offset' => $offset,
                            'timeout' => 30
                        ]
                    );

                $updates = $response->json();

                foreach ($updates['result'] ?? [] as $update) {

                    Log::info('UPDATE DITERIMA', [
                        'update_id' => $update['update_id']
                    ]);

                    Cache::put(
                        'telegram_offset',
                        $update['update_id'] + 1
                    );

                    if (!isset($update['callback_query'])) {
                        continue;
                    }

                    $callback = $update['callback_query'];

                    $callbackId = $callback['id'];

                    $data = $callback['data'];

                    if (str_starts_with($data, 'terima_')) {

                        $id = str_replace(
                            'terima_',
                            '',
                            $data
                        );

                        $result =
                            $service->terimaKoordinasi($id);

                        $this->answerCallback(
                            $token,
                            $callbackId,
                            $result['message']
                        );
                    }

                    if (str_starts_with($data, 'selesaikan_')) {

                        $id = str_replace(
                            'selesaikan_',
                            '',
                            $data
                        );

                        $result =
                            $service->selesaikanKoordinasi($id);

                        $this->answerCallback(
                            $token,
                            $callbackId,
                            $result['message']
                        );
                    }
                }
            } catch (\Throwable $e) {

                Log::error(
                    'Telegram Polling Error',
                    [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                    ]
                );
            }

            sleep(1);
        }
    }

    private function answerCallback(
        $token,
        $callbackId,
        $message
    ) {

        Http::post(
            "https://api.telegram.org/bot{$token}/answerCallbackQuery",
            [
                'callback_query_id' => $callbackId,
                'text' => $message,
                'show_alert' => false
            ]
        );
    }
}