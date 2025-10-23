<?php

namespace App\Console\Commands;

use App\Models\activityLog;
use App\Models\UptimeCheck;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

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

        foreach ($urls as $url) {
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                $this->warn("Invalid URL skipped: {$url}");
                continue;
            }

            $start = microtime(true);
            try {
                $response = Http::withOptions(['verify' => false])
                    ->timeout(10)
                    ->get($url);

                $isUp = $response->successful();
                $responseTime = (microtime(true) - $start) * 1000;
            } catch (\Exception $e) {
                $isUp = false;
                $responseTime = null;
            }

            ActivityLog::create([
                'url' => $url,
                'is_up' => $isUp,
                'response_time_ms' => $responseTime,
                'checked_at' => now(),
            ]);

            $status = $isUp ? 'UP' : 'DOWN';
            $time = $responseTime ? sprintf(' (%.2f ms)', $responseTime) : '';
            $this->info("Checked {$url}: {$status}{$time}");
        }
    }
}
