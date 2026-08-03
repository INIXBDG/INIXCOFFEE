<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PickupDriverTelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PickupDriverWebhookController extends Controller
{
    public function __construct(private PickupDriverTelegramService $telegram) {}

    public function handle(Request $request)
    {
        Log::info('Webhook Pickup Driver Incoming Request:', $request->all());

        if (isset($request['message']['text']) && isset($request['message']['reply_to_message']['text'])) {
            $chatId = $request['message']['chat']['id'];
            $text = trim($request['message']['text']);
            $repliedText = $request['message']['reply_to_message']['text'];

            if (str_contains($repliedText, 'Input Kepulangan')) {
                $this->telegram->processKepulanganReply($chatId, $text, $repliedText);
                return response()->json(['success' => true]);
            }
        }

        if (!isset($request['callback_query'])) {
            return response()->json(['success' => true]);
        }

        $callback = $request['callback_query'];
        $callbackId = $callback['id'];
        $data = $callback['data'];
        $messageId = $callback['message']['message_id'] ?? null;
        $chatId = $callback['message']['chat']['id'] ?? null;

        try {
            match (true) {
                str_starts_with($data, 'terima_') => $this->handleTerima($data, $callbackId),
                str_starts_with($data, 'detail_') => $this->handleDetail($data, $callbackId, $chatId, $messageId),
                str_starts_with($data, 'back_') => $this->handleBack($data, $callbackId, $chatId, $messageId),
                str_starts_with($data, 'selesaikan_') => $this->handleSelesaikan($data, $callbackId, $chatId),
                default => null,
            };
        } catch (\Throwable $e) {
            Log::error('Gagal memproses callback Pickup Driver', ['message' => $e->getMessage()]);
            $this->telegram->answerCallback($callbackId, '❌ Gagal memproses aksi.', showAlert: true);
        }

        return response()->json(['success' => true]);
    }

    private function handleTerima(string $data, string $callbackId): void
    {
        $id = (int) str_replace('terima_', '', $data);
        $result = $this->telegram->processTerimaFromTelegram($id);
        $this->telegram->answerCallback($callbackId, $result['message']);
    }

    private function handleDetail(string $data, string $callbackId, int $chatId, ?int $messageId): void
    {
        $id = (int) str_replace('detail_', '', $data);
        $this->telegram->answerCallback($callbackId);
        $detailText = $this->telegram->getDetailMessage($id);
        $this->telegram->editMessage($chatId, $messageId, $detailText, [
            [['text' => '⬅️ Kembali ke Ringkasan', 'callback_data' => "back_{$id}"]],
        ]);
    }

    private function handleBack(string $data, string $callbackId, int $chatId, ?int $messageId): void
    {
        $id = (int) str_replace('back_', '', $data);
        $this->telegram->answerCallback($callbackId);
        $summary = $this->telegram->getSummaryTextAndButtons($id);
        $this->telegram->editMessage($chatId, $messageId, $summary['text'], $summary['buttons']);
    }

    private function handleSelesaikan(string $data, string $callbackId, int $chatId): void
    {
        $id = (int) str_replace('selesaikan_', '', $data);
        $this->telegram->answerCallback($callbackId);
        $this->telegram->startKepulanganInput($id, $chatId);
    }
}