<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Perusahaan;
use App\Models\RiwayatStatusPerusahaan;
use Illuminate\Support\Facades\DB;

class MigrateHistoryStatusData extends Command
{
    protected $signature = 'migrate:history-status';
    protected $description = 'Memindahkan data history_status JSON ke tabel relasional riwayat_status_perusahaans';

    public function handle()
    {
        $this->info('Memulai migrasi data...');

        $perusahaans = Perusahaan::whereNotNull('history_status')->get();
        $totalMigrated = 0;

        DB::beginTransaction();
        try {
            foreach ($perusahaans as $perusahaan) {
                // Decode JSON menjadi Array
                $histories = is_string($perusahaan->history_status) 
                    ? json_decode($perusahaan->history_status, true) 
                    : $perusahaan->history_status;

                if (is_array($histories)) {
                    foreach ($histories as $history) {
                        RiwayatStatusPerusahaan::create([
                            'perusahaan_id'   => $perusahaan->id,
                            'status_lama'     => $history['status_lama'] ?? null,
                            'status_baru'     => $history['status_baru'] ?? null,
                            'diubah_oleh'     => $history['diubah_oleh'] ?? null,
                            'waktu_perubahan' => isset($history['waktu_perubahan']) ? date('Y-m-d H:i:s', strtotime($history['waktu_perubahan'])) : null,
                        ]);
                        $totalMigrated++;
                    }
                }
            }
            DB::commit();
            $this->info("Migrasi selesai. {$totalMigrated} baris data berhasil dipindahkan ke tabel baru.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
