<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\karyawan;
use App\Models\UptimeCheck;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Jenssegers\Agent\Agent;

class CheckUptime extends Command
{
    protected $signature = 'uptime:check';
    protected $description = 'Periksa apakah situs web aktif';

    public function handle()
    {
        $rawUrls = config('uptime.urls');
        if (empty($rawUrls)) {
            $this->error('No URLs configured in UPTIME_URLS');
            return;
        }

        $urls = array_filter(array_map('trim', explode(',', $rawUrls)));

        $koorITSM = karyawan::where('jabatan', 'Koordinator ITSM')->first();

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
                $response = Http::withOptions(['verify' => false])
                    ->timeout(10)
                    ->get($url);

                $isUp = $response->successful();
                $httpStatus = $response->status();
                $responseTime = (microtime(true) - $start) * 1000;
            } catch (\Exception $e) {
                $responseTime = 10000;
                $httpStatus = 0;
            }

            ActivityLog::create([
                'user_id' => (string) $koorITSM->id,
                'status' => (string) $httpStatus,
                'url' => $url,
                'ip' => gethostbyname(gethostname()),
                'user_agent' => 'Laravel Scheduler',
                'platform' => PHP_OS,
                'browser' => 'SYSTEM',
                'device' => 'SERVER',
                'method' => 'GET',
                'detail' => $isUp ? 'Uptime OK' : 'Uptime DOWN',
                'is_up' => $isUp,
                'response_time_ms' => (int) $responseTime,
                'checked_at' => now(),
            ]);

            $statusText = $isUp ? 'UP' : 'DOWN';
            $this->info("Checked {$url}: {$statusText} ({$responseTime} ms)");
        }
    }
}
