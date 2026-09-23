<?php

namespace App\Console\Commands;

use App\Models\AbsensiKaryawan;
use Carbon\Carbon;
use Illuminate\Console\Command;

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
    protected $description = 'Otomatis jika absen pulang kosong maka akan terisi pulang jam 17:00 dengan menit random';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Cari absensi HARI INI yang jam masuk-nya antara 05:00:00 - 12:00:00 dan jam keluar-nya masih null
        $jabatan = ['Instruktur', 'Programmer', 'Technical Support'];

        $transactions = AbsensiKaryawan::with('karyawan')
            ->whereHas('karyawan', function ($query) use ($jabatan) {
                // Filter karyawan yang divisinya ada di dalam array $jabatan
                $query->whereIn('jabatan', $jabatan);
            })
            ->whereDate('tanggal', Carbon::today()) // <--- TAMBAHKAN BARIS INI (Cek hanya hari ini)
            ->whereNull('jam_keluar')
            ->get();

        foreach ($transactions as $transaction) {
            $randomMinute = rand(0, 30);
            $randomSecond = rand(0, 59);

            // 1. Buat object Carbon untuk waktu random
            $randomTime = Carbon::createFromTime(17, $randomMinute, $randomSecond);

            // 2. Gabungkan tanggal absensi dengan waktu random untuk format Y-m-d H:i:s
            $fullDateTime = Carbon::parse($transaction->tanggal)->setTimeFrom($randomTime);

            // 3. Set jam_keluar
            $transaction->jam_keluar = $randomTime->format('H:i:s');
            $transaction->keterangan_pulang = 'Pulang (Kantor)'; // Opsional untuk penanda

            // 4. Matikan update timestamp otomatis Laravel
            $transaction->timestamps = false;

            // 5. Timpa updated_at (dan created_at jika mau) dengan waktu random
            $transaction->updated_at = $fullDateTime;
            $transaction->created_at = $fullDateTime; // Uncomment jika memang ingin disamakan

            $transaction->save();
        }

        $this->info('Proses update absen selesai dengan jam random dan custom timestamps (Khusus hari ini).');
    }
}
