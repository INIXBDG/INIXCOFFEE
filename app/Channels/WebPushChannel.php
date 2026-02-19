<?php

namespace App\Channels;

use App\Services\WebPushService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class WebPushChannel
{
    protected $webPushService;

    public function __construct(WebPushService $webPushService)
    {
        $this->webPushService = $webPushService;
    }

    public function send($notifiable, Notification $notification)
    {
        try {
            $userId = $this->getUserId($notifiable);
            if (!$userId) {
                Log::warning('WebPush: User ID tidak ditemukan', ['notifiable' => get_class($notifiable)]);
                return;
            }

            // Cek subscription aktif
            $subscriptions = \App\Models\PushSubscription::where('user_id', $userId)->get();

            if ($subscriptions->isEmpty()) {
                return;
            }

            if (!method_exists($notification, 'toWebPush')) {
                Log::warning('toWebPush method tidak ada', ['notification' => get_class($notification)]);
                return;
            }

            $payload = $notification->toWebPush($notifiable);

            if (empty($payload) || !is_string($payload)) {
                Log::warning('Payload webpush kosong atau bukan string');
                return;
            }

            $result = $this->webPushService->sendPayloadToUser($userId, $payload);

            if ($result['success']) {
                Log::info('WebPush terkirim', ['user_id' => $userId, 'sent' => $result['sent']]);
            } else {
                Log::warning('WebPush gagal', ['user_id' => $userId, 'message' => $result['message']]);
            }
        } catch (\Exception $e) {
            Log::error('WebPush channel error: ' . $e->getMessage());
        }
    }
    protected function getUserId($notifiable)
    {
        // Coba ambil dari berbagai sumber
        if (isset($notifiable->id)) {
            return $notifiable->id;
        }
        if (isset($notifiable->user_id)) {
            return $notifiable->user_id;
        }

        // Untuk model User yang punya relasi ke karyawan
        if (method_exists($notifiable, 'karyawan') && $notifiable->karyawan) {
            $user = \App\Models\User::where('username', $notifiable->karyawan->kode_karyawan)->first();
            return $user?->id;
        }

        return null;
    }
}
