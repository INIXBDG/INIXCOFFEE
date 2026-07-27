<?php

namespace App\Services\KPI\Jabatan;

use App\Models\AbsensiKaryawan;
use App\Models\AdministrasiKaryawan;
use App\Models\JenisTunjangan;
use App\Models\Kegiatan;
use App\Models\TunjanganKaryawan;
use App\Traits\KPIDefaultResponseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HRDKPIService
{
    use KPIDefaultResponseTrait;

    private function calculateAndFormatGap(float $progress, float $target): string
    {
        $gapRaw = $progress - $target;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');
        return $gap === '' ? '0' : $gap;
    }

    public function calculatePelaksanaanKegiatanKaryawan($item, $personId = null)
    {
        $detailResult = $this->calculatePelaksanaanKegiatanKaryawanDetail($item, $personId);
        return $detailResult['progress'];
    }

    public function calculatePelaksanaanKegiatanKaryawanDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $this->getDefaultDetailResponse();
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return $this->getDefaultDetailResponse();
        }

        $startOfYear = Carbon::create($tahun, 1, 1)->startOfDay();
        $endOfYear = Carbon::create($tahun, 12, 31)->endOfDay();

        $attendanceLookup = [];
        AbsensiKaryawan::select('id_karyawan', 'created_at')
            ->whereBetween('created_at', [$startOfYear, $endOfYear])
            ->where('keterangan', 'Masuk')
            ->cursor()
            ->each(function ($absen) use (&$attendanceLookup) {
                $date = Carbon::parse($absen->created_at)->format('Y-m-d');
                $attendanceLookup[$date][$absen->id_karyawan] = true;
            });

        $totalKegiatan = 0;
        $totalKehadiranValid = 0;
        $dailyScores = [];

        Kegiatan::select('id_peserta', 'waktu_kegiatan')
            ->whereBetween('created_at', [$startOfYear, $endOfYear])
            ->cursor() 
            ->each(function ($kegiatan) use (&$totalKegiatan, &$totalKehadiranValid, &$dailyScores, &$attendanceLookup) {
                $pesertaIds = is_array($kegiatan->id_peserta) ? $kegiatan->id_peserta : json_decode($kegiatan->id_peserta, true);
                if (empty($pesertaIds)) return;

                $totalKegiatan++;
                $tanggalKey = Carbon::parse($kegiatan->waktu_kegiatan)->format('Y-m-d');
                $hadirCount = 0;

                foreach ($pesertaIds as $pid) {
                    if (isset($attendanceLookup[$tanggalKey][$pid])) {
                        $hadirCount++;
                    }
                }

                $persentase = ($hadirCount / count($pesertaIds)) * 100;
                
                $dailyScores[$tanggalKey][] = $persentase;

                if ($persentase >= 80) {
                    $totalKehadiranValid++;
                }
            });

        if ($totalKegiatan === 0) {
            return $this->getDefaultDetailResponse();
        }

        $progress = round(($totalKehadiranValid / $totalKegiatan) * 100, 1);
        $gap = $this->calculateAndFormatGap($progress, $nilaiTarget);

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($dailyScores as $dateStr => $scores) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');
            
            $avgScore = round(array_sum($scores) / count($scores), 1);

            $monthlyData[$monthKey][] = $avgScore;
            $monthlyProgress[$monthKey][] = $avgScore;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $avgScore;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $avgScore;
        }

        $monthlyAverages = [];
        $monthlyProgressAverages = [];
        foreach ($monthlyData as $month => $vals) {
            $monthlyAverages[$month] = round(array_sum($vals) / count($vals), 1);
            $monthlyProgressAverages[$month] = round(array_sum($monthlyProgress[$month]) / count($monthlyProgress[$month]), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAverages);
        ksort($dailyProgressPerMonth);

        $aboveCount = $totalKehadiranValid;
        $belowCount = $totalKegiatan - $totalKehadiranValid;

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $aboveCount, 'below' => max(0, $belowCount)],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    public function calculatePengeluaranBiayaKaryawan($item, $personId = null)
    {
        $detailResult = $this->calculatePengeluaranBiayaKaryawanDetail($item, $personId);
        return $detailResult['progress'];
    }

    public function calculatePengeluaranBiayaKaryawanDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();
        $defaultDataManual = ['gaji' => 0, 'bpjs' => 0, 'rekrutmen' => 0, 'manual_document' => null];

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return array_merge($this->getDefaultDetailResponse(), ['dataManual' => $defaultDataManual]);
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return array_merge($this->getDefaultDetailResponse(), ['dataManual' => $defaultDataManual]);
        }

        $startOfYear = Carbon::create($tahun, 1, 1)->startOfDay();
        $endOfYear = Carbon::create($tahun, 12, 31)->endOfDay();

        $parts = explode(',', $detail->manual_value ?? '');
        $gaji = (float) ($parts[0] ?? 0);
        $bpjsManual = (float) ($parts[1] ?? 0);
        $rekrutmenManual = (float) ($parts[2] ?? 0);

        $bpjsIds = JenisTunjangan::whereIn('nama_tunjangan', ['BPJS Tenaga Kerja', 'BPJS Kesehatan'])->pluck('id');

        $bpjsBudget = (float) (TunjanganKaryawan::whereBetween('created_at', [$startOfYear, $endOfYear])
            ->whereIn('jenis_tunjangan', $bpjsIds)->sum('total') ?? 0);

        $rekrutmenBudget = (float) (Kegiatan::whereBetween('created_at', [$startOfYear, $endOfYear])
            ->where('tipe', 'rekrutment')->sum('realisasi') ?? 0);

        $kegiatanRealisasi = (float) (Kegiatan::whereBetween('created_at', [$startOfYear, $endOfYear])
            ->where('tipe', 'kegiatan')->sum('realisasi') ?? 0);

        $kegiatanBudget = (float) (DB::table('pengajuanbarangs')
            ->join('detail_pengajuan_barangs', 'pengajuanbarangs.id', '=', 'detail_pengajuan_barangs.id_pengajuan_barang')
            ->join('kegiatans', 'pengajuanbarangs.id_kegiatan', '=', 'kegiatans.id')
            ->whereBetween('kegiatans.created_at', [$startOfYear, $endOfYear])
            ->where('kegiatans.tipe', 'kegiatan')
            ->sum(DB::raw('detail_pengajuan_barangs.qty * detail_pengajuan_barangs.harga')) ?? 0);

        $score = 0;
        if ($gaji > 0) $score++;
        if ($bpjsManual > 0 && $bpjsManual <= $bpjsBudget) $score++;
        if ($rekrutmenManual > 0 && $rekrutmenManual <= $rekrutmenBudget) $score++;
        if ($kegiatanRealisasi > 0 && $kegiatanRealisasi <= $kegiatanBudget) $score++;

        $progress = round(($score / 4) * 100, 1);
        $gap = $this->calculateAndFormatGap($progress, $nilaiTarget);

        return array_merge($this->getDefaultDetailResponse(), [
            'progress' => $progress,
            'gap' => $gap,
            'dataManual' => [
                'gaji' => $gaji,
                'bpjs' => $bpjsManual,
                'rekrutmen' => $rekrutmenManual,
                'manual_document' => $detail->manual_document ?? null,
            ],
            'pie_chart' => ['above' => $score, 'below' => max(0, 4 - $score)],
        ]);
    }

    public function calculateAdministrasiKaryawan($item, $personId = null)
    {
        $detailResult = $this->calculateAdministrasiKaryawanDetail($item, $personId);
        return $detailResult['progress'];
    }

    public function calculateAdministrasiKaryawanDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $this->getDefaultDetailResponse();
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return $this->getDefaultDetailResponse();
        }

        $penaltyPerDay = 0.1;
        $maxLateDays = 7;
        $totalRecords = 0;
        $totalSkor = 0;
        $perfectCount = 0;
        $dailyScores = []; 

        AdministrasiKaryawan::select('status', 'dateline', 'tanggal_selesai', 'created_at')
            ->whereYear('created_at', $tahun)
            ->cursor()
            ->each(function ($data) use (&$totalRecords, &$totalSkor, &$perfectCount, &$dailyScores, $penaltyPerDay, $maxLateDays, $tahun) {
                $totalRecords++;
                $skor = 0;

                if ($data->status === 'selesai' && $data->dateline && $data->tanggal_selesai) {
                    $dateline = Carbon::parse($data->dateline);
                    $selesai = Carbon::parse($data->tanggal_selesai);
                    $daysLate = $selesai->greaterThan($dateline) ? $selesai->diffInDays($dateline) : 0;

                    if ($daysLate < $maxLateDays) {
                        $skor = max(0, 1 - ($daysLate * $penaltyPerDay));
                    }
                }

                $totalSkor += $skor;
                if ($skor == 1) $perfectCount++;

                $dayKey = $data->tanggal_selesai 
                    ? Carbon::parse($data->tanggal_selesai)->format('Y-m-d') 
                    : sprintf('%04d-%02d-10', $tahun, (int) Carbon::parse($data->created_at)->format('m'));

                $dailyScores[$dayKey][] = $skor * 100;
            });

        if ($totalRecords === 0) {
            return $this->getDefaultDetailResponse();
        }

        $progress = round(($totalSkor / $totalRecords) * 100, 1);
        $gap = $this->calculateAndFormatGap($progress, $nilaiTarget);

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($dailyScores as $dayKey => $scores) {
            $date = Carbon::parse($dayKey);
            $monthKey = $date->format('Y-m');
            
            $avgScore = round(array_sum($scores) / count($scores), 1);

            $monthlyData[$monthKey][] = $avgScore;
            $monthlyProgress[$monthKey][] = $avgScore;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $avgScore;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $avgScore;
        }

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $monthKey = sprintf('%04d-%02d', $tahun, $bulan);
            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [0];
                $monthlyProgress[$monthKey] = [0];
                $dailyBreakdownPerMonth[$monthKey][sprintf('%04d-%02d-10', $tahun, $bulan)] = 0;
                $dailyProgressPerMonth[$monthKey][sprintf('%04d-%02d-10', $tahun, $bulan)] = 0;
            }
        }

        $monthlyAverages = [];
        $monthlyProgressAverages = [];
        foreach ($monthlyData as $month => $vals) {
            $monthlyAverages[$month] = round(array_sum($vals) / count($vals), 1);
            $monthlyProgressAverages[$month] = round(array_sum($monthlyProgress[$month]) / count($monthlyProgress[$month]), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAverages);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $perfectCount, 'below' => max(0, $totalRecords - $perfectCount)],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }
}