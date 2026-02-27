<?php

namespace App\Services;

use App\Models\PushSubscription;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Illuminate\Support\Facades\Log;
use Exception;

class WebPushService
{
    protected $webPush;
    protected $maxRetries = 3;
    protected $batchSize = 100;

    public function __construct()
    {
        try {
            $this->webPush = new WebPush([
                'VAPID' => [
                    'subject' => env('VAPID_SUBJECT', 'mailto:support@inixindobdg.co.id'),
                    'publicKey' => env('VAPID_PUBLIC_KEY'),
                    'privateKey' => env('VAPID_PRIVATE_KEY'),
                ],
                'timeout' => 30,
                'connection_timeout' => 10,
            ]);
            $this->webPush->setDefaultOptions(['TTL' => 2419200]);
            $this->webPush->setAutomaticPadding(false);
        } catch (Exception $e) {
            Log::error('WebPushService initialization error: ' . $e->getMessage());
            throw new Exception('Gagal menginisialisasi WebPush service: ' . $e->getMessage());
        }
    }

    public function subscribe($userId, $subscriptionData)
    {
        $startTime = microtime(true);

        try {
            if (empty($subscriptionData['endpoint'])) {
                throw new Exception('Endpoint tidak boleh kosong');
            }

            if (empty($subscriptionData['keys']['p256dh']) || empty($subscriptionData['keys']['auth'])) {
                throw new Exception('Keys tidak lengkap');
            }

            if (!filter_var($subscriptionData['endpoint'], FILTER_VALIDATE_URL)) {
                throw new Exception('Endpoint URL tidak valid');
            }

            $subscription = \App\Models\PushSubscription::updateOrCreate(
                ['endpoint' => $subscriptionData['endpoint']],
                [
                    'user_id' => $userId,
                    'public_key' => $subscriptionData['keys']['p256dh'],
                    'auth_token' => $subscriptionData['keys']['auth'],
                    'content_encoding' => $subscriptionData['contentEncoding'] ?? 'aes128gcm',
                    'last_active_at' => now(),
                ],
            );

            $duration = microtime(true) - $startTime;
            Log::info('WebPush subscribe success', [
                'user_id' => $userId,
                'endpoint' => substr($subscriptionData['endpoint'], 0, 50) . '...',
                'duration' => round($duration, 4) . 's',
                'subscription_id' => $subscription->id,
            ]);

            return [
                'success' => true,
                'message' => 'Subscription berhasil',
                'data' => $subscription,
            ];
        } catch (Exception $e) {
            Log::error('WebPush subscribe error', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal subscribe: ' . $e->getMessage(),
            ];
        }
    }

    public function unsubscribe($endpoint)
    {
        try {
            if (empty($endpoint)) {
                throw new Exception('Endpoint tidak boleh kosong');
            }

            $deleted = \App\Models\PushSubscription::where('endpoint', $endpoint)->delete();

            if ($deleted) {
                Log::info('WebPush unsubscribe success', ['endpoint' => substr($endpoint, 0, 50) . '...']);
                return [
                    'success' => true,
                    'message' => 'Unsubscribe berhasil',
                ];
            }

            return [
                'success' => false,
                'message' => 'Subscription tidak ditemukan',
            ];
        } catch (Exception $e) {
            Log::error('WebPush unsubscribe error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal unsubscribe: ' . $e->getMessage(),
            ];
        }
    }

    public function unsubscribeByUserId($userId)
    {
        try {
            $deleted = \App\Models\PushSubscription::where('user_id', $userId)->delete();

            Log::info('WebPush unsubscribe by user ID', [
                'user_id' => $userId,
                'deleted_count' => $deleted,
            ]);

            return [
                'success' => true,
                'message' => "Berhasil menghapus $deleted subscription",
                'count' => $deleted,
            ];
        } catch (Exception $e) {
            Log::error('WebPush unsubscribe by user ID error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal unsubscribe: ' . $e->getMessage(),
            ];
        }
    }

    public function sendNotificationToUser($userId, $title, $body, $options = [])
    {
        try {
            if (empty($userId)) {
                return [
                    'success' => false,
                    'message' => 'User ID tidak boleh kosong',
                ];
            }

            if (empty($title) || empty($body)) {
                return [
                    'success' => false,
                    'message' => 'Title dan body tidak boleh kosong',
                ];
            }

            $subscriptions = \App\Models\PushSubscription::where('user_id', $userId)
                ->get();

            if ($subscriptions->isEmpty()) {
                Log::warning('User tidak memiliki subscription aktif', ['user_id' => $userId]);
                return [
                    'success' => false,
                    'message' => 'User tidak memiliki subscription',
                    'sent' => 0,
                    'failed' => 0,
                ];
            }

            Log::info('Sending notification to user', [
                'user_id' => $userId,
                'subscription_count' => $subscriptions->count(),
                'title' => $title,
            ]);

            return $this->sendToSubscriptions($subscriptions, $title, $body, $options);
        } catch (Exception $e) {
            Log::error('WebPush send to user error', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengirim notifikasi: ' . $e->getMessage(),
                'sent' => 0,
                'failed' => 0,
            ];
        }
    }

    public function sendNotificationToAll($title, $body, $options = [])
    {
        try {
            if (empty($title) || empty($body)) {
                return [
                    'success' => false,
                    'message' => 'Title dan body tidak boleh kosong',
                ];
            }

            $subscriptions = \App\Models\PushSubscription::all();

            if ($subscriptions->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada subscription',
                    'sent' => 0,
                    'failed' => 0,
                ];
            }

            Log::info('Sending notification to all users', [
                'total_subscriptions' => $subscriptions->count(),
                'title' => $title,
            ]);

            $totalSent = 0;
            $totalFailed = 0;
            $totalResults = [];

            foreach ($subscriptions->chunk($this->batchSize) as $batch) {
                $result = $this->sendToSubscriptions($batch, $title, $body, $options);
                $totalSent += $result['sent'] ?? 0;
                $totalFailed += $result['failed'] ?? 0;

                if (isset($result['results'])) {
                    $totalResults = array_merge($totalResults, $result['results']);
                }

                if ($batch->count() === $this->batchSize) {
                    usleep(100000);
                }
            }

            return [
                'success' => $totalSent > 0,
                'message' => "Notifikasi terkirim: $totalSent berhasil, $totalFailed gagal",
                'sent' => $totalSent,
                'failed' => $totalFailed,
                'total' => $subscriptions->count(),
                'results' => $totalResults,
            ];
        } catch (Exception $e) {
            Log::error('WebPush send to all error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal mengirim notifikasi: ' . $e->getMessage(),
                'sent' => 0,
                'failed' => 0,
            ];
        }
    }

    public function sendNotificationToUsers(array $userIds, $title, $body, $options = [])
    {
        try {
            if (empty($userIds)) {
                return [
                    'success' => false,
                    'message' => 'User IDs tidak boleh kosong',
                ];
            }

            if (empty($title) || empty($body)) {
                return [
                    'success' => false,
                    'message' => 'Title dan body tidak boleh kosong',
                ];
            }

            $subscriptions = \App\Models\PushSubscription::whereIn('user_id', $userIds)
                ->get();

            if ($subscriptions->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada subscription untuk user yang dipilih',
                    'sent' => 0,
                    'failed' => 0,
                ];
            }

            Log::info('Sending notification to multiple users', [
                'user_count' => count($userIds),
                'subscription_count' => $subscriptions->count(),
                'title' => $title,
            ]);

            return $this->sendToSubscriptions($subscriptions, $title, $body, $options);
        } catch (Exception $e) {
            Log::error('WebPush send to users error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal mengirim notifikasi: ' . $e->getMessage(),
                'sent' => 0,
                'failed' => 0,
            ];
        }
    }

    protected function sendToSubscriptions($subscriptions, $payloadOrTitle, $bodyOrOptions = [], $options = [])
    {
        try {
            $isJsonPayload = is_string($payloadOrTitle) && json_decode($payloadOrTitle) !== null && empty($bodyOrOptions);
            $payload = $isJsonPayload ? $payloadOrTitle : $this->buildPayload($payloadOrTitle, $bodyOrOptions, $options);

            if (!$this->validatePayload($payload)) {
                throw new Exception('Payload tidak valid');
            }

            $queued = [];
            $results = [];
            $sent = 0;
            $failed = 0;

            foreach ($subscriptions as $subscription) {
                try {
                    $sub = Subscription::create([
                        'endpoint' => $subscription->endpoint,
                        'publicKey' => $subscription->public_key,
                        'authToken' => $subscription->auth_token,
                        'contentEncoding' => $subscription->content_encoding,
                    ]);

                    $this->webPush->queueNotification($sub, $payload);
                    $queued[$subscription->endpoint] = $subscription;
                } catch (Exception $e) {
                    Log::warning('Failed to queue notification', [
                        'endpoint' => substr($subscription->endpoint, 0, 50) . '...',
                        'error' => $e->getMessage(),
                    ]);
                    $failed++;
                    $results[] = [
                        'success' => false,
                        'reason' => 'Queue failed: ' . $e->getMessage(),
                        'endpoint' => $subscription->endpoint,
                        'user_id' => $subscription->user_id,
                    ];
                }
            }

            $reports = $this->webPush->flush();

            foreach ($reports as $report) {
                $endpoint = (string) $report->getRequest()->getUri();
                $shortEndpoint = substr($endpoint, 0, 50) . '...';

                Log::info('WebPush report detail', [
                    'endpoint' => $shortEndpoint,
                    'success' => $report->isSuccess(),
                    'reason' => $report->getReason(),
                    'status_code' => $report->getResponse()?->getStatusCode(),
                ]);

                if ($report->isSuccess()) {
                    $results[] = [
                        'success' => true,
                        'reason' => 'Delivered',
                        'endpoint' => $endpoint,
                        'user_id' => $queued[$endpoint]->user_id ?? null,
                    ];
                    $sent++;
                } else {
                    $reason = $report->getReason();
                    $statusCode = $report->getResponse()?->getStatusCode();

                    $results[] = [
                        'success' => false,
                        'reason' => $reason,
                        'status_code' => $statusCode,
                        'endpoint' => $endpoint,
                        'user_id' => $queued[$endpoint]->user_id ?? null,
                    ];
                    $failed++;

                    Log::error('WebPush delivery failed', [
                        'endpoint' => $shortEndpoint,
                        'reason' => $reason,
                        'status_code' => $statusCode,
                    ]);

                    if ($this->shouldDeleteSubscription($report)) {
                        if (isset($queued[$endpoint])) {
                            $queued[$endpoint]->delete();
                            Log::info('Deleted expired/invalid subscription', [
                                'endpoint' => $shortEndpoint,
                                'reason' => $reason,
                            ]);
                        }
                    }
                }
            }

            Log::info('Notification delivery summary', [
                'sent' => $sent,
                'failed' => $failed,
                'total' => $sent + $failed,
            ]);

            return [
                'success' => $sent > 0,
                'message' => "Notifikasi terkirim: $sent berhasil, $failed gagal",
                'sent' => $sent,
                'failed' => $failed,
                'total' => count($subscriptions),
                'results' => $results,
            ];
        } catch (Exception $e) {
            Log::error('Send to subscriptions error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengirim notifikasi: ' . $e->getMessage(),
                'sent' => 0,
                'failed' => count($subscriptions),
            ];
        }
    }

    protected function buildPayload($title, $body, $options = [])
    {
        $title = $this->sanitizeText($title, 200);
        $body = $this->sanitizeText($body, 500);

        $data = [
            'title' => $title,
            'body' => $body,
            'icon' => $this->validateUrl($options['icon'] ?? '/icons/icon-192x192.png'),
            'badge' => $this->validateUrl($options['badge'] ?? '/icons/badge-96x96.png'),
            'image' => isset($options['image']) ? $this->validateUrl($options['image']) : null,
            'data' => is_array($options['data'] ?? null) ? $options['data'] : [],
            'requireInteraction' => !empty($options['requireInteraction']),
            'silent' => !empty($options['silent']),
            'tag' => $options['tag'] ?? 'notification-' . time(),
            'timestamp' => now()->timestamp * 1000,
            'renotify' => true,
            'vibrate' => $this->getVibratePattern($options),
            'actions' => $this->getActions($options),
        ];

        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('JSON encode error', [
                'error' => json_last_error_msg(),
                'data' => $data,
            ]);

            return json_encode(
                [
                    'title' => $title,
                    'body' => $body,
                    'icon' => '/icons/icon-192x192.png',
                    'timestamp' => now()->timestamp * 1000,
                ],
                JSON_UNESCAPED_UNICODE,
            );
        }

        return $json;
    }

    public function sendPayloadToUser($userId, string $payloadJson, array $options = [])
    {
        $subscriptions = \App\Models\PushSubscription::where('user_id', $userId)
            ->get();

        if ($subscriptions->isEmpty()) {
            return ['success' => false, 'message' => 'No subscriptions', 'sent' => 0, 'failed' => 0];
        }

        return $this->sendToSubscriptions($subscriptions, $payloadJson, $options);
    }

    protected function validatePayload($payload)
    {
        if (empty($payload)) {
            return false;
        }

        if (strlen($payload) > 4096) {
            Log::warning('Payload size exceeds limit', [
                'size' => strlen($payload),
                'limit' => 4096,
            ]);
            return false;
        }

        json_decode($payload);
        return json_last_error() === JSON_ERROR_NONE;
    }

    protected function sanitizeText($text, $maxLength = 500)
    {
        if (!is_string($text)) {
            return '';
        }

        $text = strip_tags($text);
        $text = trim($text);

        if (strlen($text) > $maxLength) {
            $text = substr($text, 0, $maxLength - 3) . '...';
        }

        return $text;
    }

    protected function validateUrl($url)
    {
        if (empty($url)) {
            return null;
        }

        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        if (strpos($url, '/') === 0) {
            return url($url);
        }

        return url('/' . ltrim($url, '/'));
    }

    protected function shouldDeleteSubscription($report)
    {
        if (!$report->isSubscriptionExpired()) {
            return false;
        }

        $statusCode = $report->getResponse()?->getStatusCode();

        return in_array($statusCode, [404, 410, 403, 400, 401]);
    }

    public function getVapidPublicKey()
    {
        $key = env('VAPID_PUBLIC_KEY');

        if (empty($key)) {
            Log::error('VAPID_PUBLIC_KEY is not set in environment');
            throw new Exception('VAPID_PUBLIC_KEY tidak dikonfigurasi');
        }

        return $key;
    }

    public function getSubscriptionCount($userId = null)
    {
        try {
            $query = \App\Models\PushSubscription::query();

            if ($userId) {
                $query->where('user_id', $userId);
            }

            return $query->count();
        } catch (Exception $e) {
            Log::error('Get subscription count error: ' . $e->getMessage());
            return 0;
        }
    }

    public function getActiveSubscriptions($userId = null)
    {
        try {
            $query = \App\Models\PushSubscription::query()->where('created_at', '>=', now()->subMonths(6));

            if ($userId) {
                $query->where('user_id', $userId);
            }

            return $query->get();
        } catch (Exception $e) {
            Log::error('Get active subscriptions error: ' . $e->getMessage());
            return collect([]);
        }
    }

    public function cleanupExpiredSubscriptions()
    {
        try {
            $expired = \App\Models\PushSubscription::where('created_at', '<', now()->subMonths(6))
                ->delete();

            Log::info('Cleanup expired subscriptions', ['deleted' => $expired]);

            return [
                'success' => true,
                'message' => "Berhasil menghapus $expired subscription yang expired",
                'deleted' => $expired,
            ];
        } catch (Exception $e) {
            Log::error('Cleanup expired subscriptions error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal cleanup subscription',
                'deleted' => 0,
            ];
        }
    }

    private function getVibratePattern($options)
    {
        if (isset($options['vibrate']) && is_array($options['vibrate'])) {
            return array_map('intval', $options['vibrate']);
        }
        if (!empty($options['vibrate'])) {
            return [200, 100, 200];
        }
        return null;
    }

    private function getActions($options)
    {
        if (!isset($options['actions']) || !is_array($options['actions'])) {
            return [];
        }

        return array_map(function ($action) {
            return [
                'action' => $action['action'] ?? '',
                'title' => $action['title'] ?? '',
                'icon' => $action['icon'] ?? null,
            ];
        }, $options['actions']);
    }

    public function userHasActiveSubscription($userId = null)
    {
        $userId = $userId ?? auth()->user()->id;

        if (!$userId) {
            return false;
        }


        return PushSubscription::where('user_id', $userId)->exists();
    }
}
