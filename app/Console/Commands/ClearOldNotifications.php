<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use Carbon\Carbon;

class ClearOldNotifications extends Command
{
    /**
     * Nama dan signature dari console command.
     *
     * @var string
     */
    protected $signature = 'notifications:clear-old';

    /**
     * Deskripsi dari console command.
     *
     * @var string
     */
    protected $description = 'Menghapus data notifikasi yang memiliki created_at lebih dari 3 bulan';

    /**
     * Eksekusi console command.
     */
    public function handle()
    {
        // Tetapkan batas waktu 3 bulan ke belakang dari waktu sekarang
        $dateLimit = Carbon::now()->subMonths(3);

        // Eksekusi kueri penghapusan
        $deletedCount = Notification::where('created_at', '<', $dateLimit)->delete();

        // Tampilkan output pada terminal untuk keperluan logging
        $this->info("Eksekusi berhasil: {$deletedCount} data notifikasi lama telah dihapus.");
    }
}
