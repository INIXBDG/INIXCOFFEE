<?php

namespace App\Jobs;

// 1. Tambahkan use statement yang dibutuhkan
use App\Models\AbsensiKaryawan;
use App\Models\NamaModelRekap; // <-- Ganti dengan model rekap yang benar
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // <-- Penting
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;      // <-- Untuk query
use Illuminate\Support\Facades\Log;     // <-- Untuk logging
use romanzipp\QueueMonitor\Traits\IsMonitored; // <-- Untuk monitoring

class GenerateAbsensiReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, IsMonitored;

    protected $tanggalAwal;
    protected $tanggalAkhir;
    protected $jenisRekap;

    public function __construct(string $tanggalAwal, string $tanggalAkhir, string $jenisRekap = 'harian')
    {
        $this->tanggalAwal = $tanggalAwal;
        $this->tanggalAkhir = $tanggalAkhir;
        $this->jenisRekap = $jenisRekap;
    }

    public function handle(): void
    {
        Log::info("Memulai Job: GenerateAbsensiReportJob ({$this->jenisRekap}) untuk periode {$this->tanggalAwal} s/d {$this->tanggalAkhir}...");

        try {
            $absensi = AbsensiKaryawan::whereBetween('tanggal', [$this->tanggalAwal, $this->tanggalAkhir])
                ->with('karyawan')
                ->get();

            $rekapData = [];
            foreach ($absensi as $absen) {
                $idKaryawan = $absen->id_karyawan;
                if (!isset($rekapData[$idKaryawan])) {
                    $rekapData[$idKaryawan] = [
                        'nama_karyawan' => $absen->karyawan->nama_lengkap ?? 'N/A',
                        'total_hari_masuk' => 0,
                        'total_keterlambatan_detik' => 0,
                    ];
                }

                $rekapData[$idKaryawan]['total_hari_masuk']++;

                if ($absen->waktu_keterlambatan && $absen->waktu_keterlambatan != '00:00:00') {
                    try {
                        $keterlambatanCarbon = Carbon::parse($absen->waktu_keterlambatan);
                        $rekapData[$idKaryawan]['total_keterlambatan_detik'] +=
                            $keterlambatanCarbon->hour * 3600 +
                            $keterlambatanCarbon->minute * 60 +
                            $keterlambatanCarbon->second;
                    } catch (\Exception $e) {
                        Log::warning("Format waktu_keterlambatan tidak valid untuk absensi ID: {$absen->id}");
                    }
                }
            }

            // Contoh penyimpanan (uncomment kalau sudah punya model rekap)
            /*
            foreach ($rekapData as $id => $data) {
                NamaModelRekap::updateOrCreate(
                    ['id_karyawan' => $id, 'periode_awal' => $this->tanggalAwal, 'periode_akhir' => $this->tanggalAkhir],
                    [
                        'nama_karyawan' => $data['nama_karyawan'],
                        'total_hari_masuk' => $data['total_hari_masuk'],
                        'total_keterlambatan_detik' => $data['total_keterlambatan_detik'],
                    ]
                );
            }
            */

            Log::info("Job GenerateAbsensiReportJob ({$this->jenisRekap}) selesai.");
        } catch (\Exception $e) {
            Log::error("Job GenerateAbsensiReportJob ({$this->jenisRekap}) GAGAL: " . $e->getMessage());
            throw $e;
        }
    }
}
