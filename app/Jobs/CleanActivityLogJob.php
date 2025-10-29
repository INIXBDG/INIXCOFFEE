<?php

namespace App\Jobs;

use App\Models\ActivityLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class CleanActivityLogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, IsMonitored;

    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        Log::info('Menjalankan Job: CleanActivityLogJob...');

        try {
            ActivityLog::whereNotIn('status', [
                'login',
                'logout',
                'visit',
            ])->delete();

            Log::info('Job CleanActivityLogJob: Data ActivityLog berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Job CleanActivityLogJob GAGAL: ' . $e->getMessage());
            throw $e;
        }
    }
}
