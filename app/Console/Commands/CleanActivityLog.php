<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log; // Sesuaikan dengan namespace model Anda

class CleanActivityLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:clean-activity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pembersihan Otomatis Activity Log (visit, login, logout, absen)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai pembersihan activity log...');

        try {
            // Gunakan nama model yang sesuai (ActivityLog atau activityLog)
            $deleted = ActivityLog::query()
                ->whereIn('status', [
                    'visit',
                    'login',
                    'logout',
                    'Absen Masuk',
                    'Absen Keluar',
                ])
                ->delete();

            $message = "Berhasil menghapus {$deleted} activity log dari status visit/login/logout/absen.";

            Log::info($message);
            $this->info($message); // Menampilkan output di terminal

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            Log::error('Command log:clean-activity gagal: '.$e->getMessage());
            $this->error('Gagal: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
