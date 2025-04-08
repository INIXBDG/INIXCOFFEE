<?php

namespace App\Console\Commands;

use App\Models\AbsensiKaryawan;
use Illuminate\Console\Command;
use Carbon\Carbon;

class AbsenCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'absen:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis jika absen pulang kosong maka akan terisi pulang jam 17:00:00';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Cari semua absensi yang jam masuk-nya antara 05:00:00 dan 12:00:00 dan jam keluar-nya masih null
        $transactions = AbsensiKaryawan::whereNull('jam_keluar')
            ->whereTime('jam_masuk', '>=', '05:00:00')
            ->whereTime('jam_masuk', '<=', '12:00:00')
            ->get();

        $defaultPulangTime = '17:00:00'; // Jam default untuk pulang
        
        foreach ($transactions as $transaction) {
            // Set jam_keluar menjadi default pulang time
            $transaction->jam_keluar = $defaultPulangTime;
            $transaction->save(); // Simpan setiap record yang diupdate
        }

        $this->info('Proses update absen selesai.');
    }

}
