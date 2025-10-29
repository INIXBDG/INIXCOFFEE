<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class UpdateStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, IsMonitored;

    public function __construct()
    {
        // Konstruktor bisa kosong
    }

    public function handle(): void
    {
        Log::info('Menjalankan Job: UpdateStatusJob (command app:update-status)...');

        try {
            // Menjalankan command artisan
            Artisan::call('app:update-status');

            Log::info('Job UpdateStatusJob selesai. Output Command: ' . Artisan::output());
        } catch (\Exception $e) {
            Log::error('Job UpdateStatusJob GAGAL: ' . $e->getMessage());
            throw $e;
        }
    }
}
