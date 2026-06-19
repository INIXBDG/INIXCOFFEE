<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Perusahaan;
use Carbon\Carbon;

class EvaluasiStatusPerusahaan extends Command
{
    /**
     * Nama dan signature dari console command.
     *
     * @var string
     */
    protected $signature = 'perusahaan:evaluasi-status';

    /**
     * Deskripsi console command.
     *
     * @var string
     */
    protected $description = 'Mengevaluasi dan memperbarui status Perusahaan berdasarkan jumlah peluang tahap Merah dalam 1 tahun terakhir.';

    /**
     * Eksekusi console command.
     *
     * @return int
     */
    public function handle()
    {
        $perusahaans = Perusahaan::all();
        $batasWaktuTahunLalu = now()->subDays(365);

        foreach ($perusahaans as $perusahaan) {
            $statusLama = $perusahaan->status;
            $statusBaru = $statusLama;

            // Validasi nilai null pada atribut created_at menggunakan ternary operator
            $umurPerusahaanHari = $perusahaan->created_at ? $perusahaan->created_at->diffInDays(now()) : 0;

            // Menghitung jumlah aktivitas pelatihan berdasarkan peluang dengan tahap Merah
            $jumlahPelatihan = $perusahaan->peluang()
                ->where('tahap', 'Merah')
                ->where('created_at', '>=', $batasWaktuTahunLalu)
                ->count();

            // Logika Kenaikan Status
            if ($jumlahPelatihan > 1) {
                $statusBaru = 'Q1';
            } elseif ($jumlahPelatihan == 1) {
                if ($statusLama === 'Q4') {
                    $statusBaru = 'Q3';
                } elseif ($statusLama === 'Q3') {
                    $statusBaru = 'Q2';
                } elseif ($statusLama === 'Q2') {
                    $statusBaru = 'Q1';
                } elseif (empty($statusLama) || strtolower($statusLama) === 'baru') {
                    $statusBaru = 'Q2';
                }
            }
            // Logika Penurunan Status (Hanya berlaku jika umur entitas >= 365 hari)
            else {
                if ($umurPerusahaanHari >= 365) {
                    if ($statusLama === 'Q1') {
                        $statusBaru = 'Q2';
                    } elseif ($statusLama === 'Q2') {
                        $statusBaru = 'Q3';
                    } elseif ($statusLama === 'Q3') {
                        $statusBaru = 'Q4';
                    } elseif (empty($statusLama) || strtolower($statusLama) === 'baru') {
                        $statusBaru = 'Q2';
                    }
                }
            }

            // Pengecekan dan pencatatan riwayat status
            if (!empty($statusBaru) && $statusLama !== $statusBaru) {
                $historyStatus = $perusahaan->history_status_array;

                $historyStatus[] = [
                    'status_lama' => $statusLama ?? '-',
                    'status_baru' => $statusBaru,
                    'waktu_perubahan' => now()->toDateTimeString(),
                    'diubah_oleh' => 'sistem'
                ];

                // Memperbarui atribut perusahaan
                $perusahaan->status = $statusBaru;
                $perusahaan->history_status = json_encode($historyStatus);
                $perusahaan->save();
            }
        }

        $this->info('Evaluasi status perusahaan berdasarkan peluang Merah selesai dijalankan.');
        return Command::SUCCESS;
    }
}
