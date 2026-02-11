<?php

namespace App\Services;

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
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
                'timeout' => 30, // Timeout 30 detik
                'connection_timeout' => 10,
            ]);
            $this->webPush->setDefaultOptions(['TTL' => 2419200]); 
            $this->webPush->setAutomaticPadding(false);
        } catch (Exception $e) {
            Log::error('WebPushService initialization error: ' . $e->getMessage());
            throw new Exception('Gagal menginisialisasi WebPush service: ' . $e->getMessage());
        }
    }

    /**
     * Subscribe user ke push notification
     */
    public function subscribe($userId, $subscriptionData)
    {
        $startTime = microtime(true);

        try {
            // Validasi subscription data
            if (empty($subscriptionData['endpoint'])) {
                throw new Exception('Endpoint tidak boleh kosong');
            }

            if (empty($subscriptionData['keys']['p256dh']) || empty($subscriptionData['keys']['auth'])) {
                throw new Exception('Keys tidak lengkap');
            }

            // Validasi URL endpoint
            if (!filter_var($subscriptionData['endpoint'], FILTER_VALIDATE_URL)) {
                throw new Exception('Endpoint URL tidak valid');
            }

            // Simpan atau update subscription
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

    /**
     * Unsubscribe user dari push notification
     */
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
            } else {
                return [
                    'success' => false,
                    'message' => 'Subscription tidak ditemukan',
                ];
            }
        } catch (Exception $e) {
            Log::error('WebPush unsubscribe error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal unsubscribe: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Unsubscribe by user ID
     */
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

    /**
     * Kirim notifikasi ke user tertentu
     */
    public function sendNotificationToUser($userId, $title, $body, $options = [])
    {
        try {
            // Validasi input
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

            // Cek apakah user punya subscription
            $subscriptions = \App\Models\PushSubscription::where('user_id', $userId)->get();

            if ($subscriptions->isEmpty()) {
                Log::warning('User tidak memiliki subscription', ['user_id' => $userId]);
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

            // Kirim notifikasi
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

    /**
     * Kirim notifikasi ke semua user
     */
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

            // Proses dalam batch untuk performa
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

                // Delay antar batch untuk menghindari rate limiting
                if ($batch->count() === $this->batchSize) {
                    usleep(100000); // 100ms delay
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

    /**
     * Kirim notifikasi ke multiple users
     */
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

            $subscriptions = \App\Models\PushSubscription::whereIn('user_id', $userIds)->get();

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

    /**
     * Kirim notifikasi ke koleksi subscriptions
     */
    protected function sendToSubscriptions($subscriptions, $title, $body, $options = [])
    {
        try {
            $payload = $this->buildPayload($title, $body, $options);

            // Validasi payload
            if (!$this->validatePayload($payload)) {
                throw new Exception('Payload tidak valid');
            }

            $queued = [];
            $results = [];
            $sent = 0;
            $failed = 0;

            // Queue semua notifikasi
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

            // Flush dan proses hasil
            $reports = $this->webPush->flush();

            foreach ($reports as $report) {
                $endpoint = (string) $report->getRequest()->getUri();

                if ($report->isSuccess()) {
                    $results[] = [
                        'success' => true,
                        'reason' => 'Delivered',
                        'endpoint' => $endpoint,
                        'user_id' => $queued[$endpoint]->user_id ?? null,
                    ];
                    $sent++;

                    // Update last_active_at
                    if (isset($queued[$endpoint])) {
                        $queued[$endpoint]->update(['last_active_at' => now()]);
                    }
                } else {
                    $reason = $report->getReason();
                    $statusCode = $report->getResponse() ? $report->getResponse()->getStatusCode() : null;

                    $results[] = [
                        'success' => false,
                        'reason' => $reason,
                        'status_code' => $statusCode,
                        'endpoint' => $endpoint,
                        'user_id' => $queued[$endpoint]->user_id ?? null,
                    ];
                    $failed++;

                    Log::error('WebPush delivery failed', [
                        'endpoint' => substr($endpoint, 0, 50) . '...',
                        'reason' => $reason,
                        'status_code' => $statusCode,
                    ]);

                    // Hapus subscription yang expired atau tidak valid
                    if ($this->shouldDeleteSubscription($report)) {
                        if (isset($queued[$endpoint])) {
                            $queued[$endpoint]->delete();
                            Log::info('Deleted expired/invalid subscription', [
                                'endpoint' => substr($endpoint, 0, 50) . '...',
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

    /**
     * Build payload untuk notifikasi
     */
    protected function buildPayload($title, $body, $options = [])
    {
        // Sanitize input
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
            'badge' => $options['badge'] ?? '/icons/badge-96x96.png',
        ];

        // Vibrate pattern
        if (isset($options['vibrate']) && is_array($options['vibrate'])) {
            $data['vibrate'] = array_map('intval', $options['vibrate']);
        } elseif (!empty($options['vibrate'])) {
            $data['vibrate'] = [200, 100, 200];
        }

        // Actions
        if (isset($options['actions']) && is_array($options['actions'])) {
            $data['actions'] = array_map(function ($action) {
                return [
                    'action' => $action['action'] ?? '',
                    'title' => $action['title'] ?? '',
                    'icon' => $action['icon'] ?? null,
                ];
            }, $options['actions']);
        }

        // Encode ke JSON dengan error handling
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = json_last_error_msg();
            Log::error('JSON encode error', [
                'error' => $error,
                'data' => $data,
            ]);

            // Fallback ke format minimal
            $json = json_encode(
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
        $subscriptions = \App\Models\PushSubscription::where('user_id', $userId)->get();

        if ($subscriptions->isEmpty()) {
            return ['success' => false, 'message' => 'No subscriptions', 'sent' => 0, 'failed' => 0];
        }

        return $this->sendToSubscriptions($subscriptions, $payloadJson, $options);
    }

    /**
     * Validasi payload JSON
     */
    protected function validatePayload($payload)
    {
        if (empty($payload)) {
            return false;
        }

        // Cek ukuran payload (max 4KB untuk FCM)
        if (strlen($payload) > 4096) {
            Log::warning('Payload size exceeds limit', [
                'size' => strlen($payload),
                'limit' => 4096,
            ]);
            return false;
        }

        // Cek apakah valid JSON
        json_decode($payload);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Sanitize text input
     */
    protected function sanitizeText($text, $maxLength = 500)
    {
        if (!is_string($text)) {
            return '';
        }

        // Remove HTML tags
        $text = strip_tags($text);

        // Trim whitespace
        $text = trim($text);

        // Limit length
        if (strlen($text) > $maxLength) {
            $text = substr($text, 0, $maxLength - 3) . '...';
        }

        return $text;
    }

    /**
     * Validasi URL
     */
    protected function validateUrl($url)
    {
        if (empty($url)) {
            return null;
        }

        // Jika sudah URL lengkap, return langsung
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        // Jika relative path, tambahkan base URL
        if (strpos($url, '/') === 0) {
            return url($url);
        }

        return url('/' . ltrim($url, '/'));
    }

    /**
     * Cek apakah subscription harus dihapus
     */
    protected function shouldDeleteSubscription($report)
    {
        if (!$report->isSubscriptionExpired()) {
            return false;
        }

        $statusCode = $report->getResponse() ? $report->getResponse()->getStatusCode() : null;

        // Hapus jika:
        // - Status 404 (Not Found)
        // - Status 410 (Gone)
        // - Status 403 (Forbidden)
        // - Status 400 (Bad Request)
        $deleteStatusCodes = [404, 410, 403, 400, 401];

        return in_array($statusCode, $deleteStatusCodes);
    }

    /**
     * Get VAPID public key
     */
    public function getVapidPublicKey()
    {
        $key = env('VAPID_PUBLIC_KEY');

        if (empty($key)) {
            Log::error('VAPID_PUBLIC_KEY is not set in environment');
            throw new Exception('VAPID_PUBLIC_KEY tidak dikonfigurasi');
        }

        return $key;
    }

    /**
     * Get subscription count by user
     */
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

    /**
     * Get all active subscriptions
     */
    public function getActiveSubscriptions($userId = null)
    {
        try {
            $query = \App\Models\PushSubscription::query()->where('created_at', '>=', now()->subMonths(6)); // Hanya subscription < 6 bulan

            if ($userId) {
                $query->where('user_id', $userId);
            }

            return $query->get();
        } catch (Exception $e) {
            Log::error('Get active subscriptions error: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Cleanup expired subscriptions
     */
    public function cleanupExpiredSubscriptions()
    {
        try {
            $expired = \App\Models\PushSubscription::where('last_active_at', '<', now()->subMonths(6))
                ->orWhereNull('last_active_at')
                ->where('created_at', '<', now()->subMonths(6))
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
}
