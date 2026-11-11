<?php

namespace App\Console\Commands;

use App\Models\activityLog;
use App\Models\karyawan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CheckUptime extends Command
{
    protected $signature = 'uptime:check';
    protected $description = 'Periksa apakah situs web aktif';

    public function handle()
    {
        $koorITSM = karyawan::where('jabatan', 'Koordinator ITSM')->first();
        $services = config('uptime.services');

        if (!is_array($services) || empty($services)) {
            $rawUrls = config('uptime.urls');
            if (empty($rawUrls)) {
                $this->error('No URLs configured in UPTIME_URLS');
                return;
            }

            $urls = is_array($rawUrls)
                ? $rawUrls
                : array_filter(array_map('trim', explode(',', $rawUrls)));
            $services = ['service' => array_values($urls)];
        }

        foreach ($services as $serviceName => $urls) {
            $urls = is_array($urls) ? $urls : [$urls];
            $urls = array_values(array_filter(array_map(
                fn($url) => rtrim(trim((string) $url), '/') . '/',
                $urls
            )));

            $result = $this->checkFallbackUrls((string) $serviceName, $urls);

            activityLog::create([
                'user_id' => $koorITSM ? (string) $koorITSM->id : null,
                'status' => (string) $result['status'],
                'url' => $result['url'],
                'ip' => gethostbyname(gethostname()),
                'user_agent' => 'Laravel Scheduler',
                'platform' => PHP_OS,
                'browser' => 'SYSTEM',
                'device' => 'SERVER',
                'method' => 'GET',
                'detail' => $result['detail'],
                'is_up' => $result['is_up'],
                'response_time_ms' => (int) $result['response_time_ms'],
                'checked_at' => now(),
            ]);
        }
    }

    protected function checkFallbackUrls(string $serviceName, array $urls): array
    {
        $failedUrl = null;
        $failedStatus = 0;
        $failedResponseTime = 0;

        foreach ($urls as $url) {
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                $this->warn("Invalid URL skipped: {$url}");
                continue;
            }

            $start = microtime(true);
            $responseTime = 0;
            $isUp = false;
            $httpStatus = null;

            try {
                $response = Http::timeout(10)->get($url);

                $isUp = $response->successful();
                $httpStatus = $response->status();
                $responseTime = (microtime(true) - $start) * 1000;
            } catch (\Exception $e) {
                $responseTime = 10000;
                $httpStatus = 0;
            }

            $statusText = $isUp ? 'UP' : 'DOWN';
            $this->info("Checked {$url}: {$statusText} ({$responseTime} ms)");

            if ($isUp) {
                return [
                    'url' => $url,
                    'status' => $httpStatus,
                    'is_up' => true,
                    'response_time_ms' => (int) $responseTime,
                    'detail' => "Uptime OK ({$serviceName}) via {$url}",
                ];
            }

            $failedUrl ??= $url;
            $failedStatus = $httpStatus;
            $failedResponseTime = max($failedResponseTime, (int) $responseTime);
        }

        return [
            'url' => $failedUrl ?? ($urls[0] ?? ''),
            'status' => $failedStatus,
            'is_up' => false,
            'response_time_ms' => $failedResponseTime,
            'detail' => "Uptime DOWN ({$serviceName}) after checking all fallback URLs",
        ];
    }
}
