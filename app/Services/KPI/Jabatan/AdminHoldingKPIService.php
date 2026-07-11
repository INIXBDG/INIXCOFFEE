<?php

namespace App\Services\KPI\Jabatan;

use App\Models\DokumentasiExam;
use App\Models\NomorModul;
use App\Models\Registrasi;
use App\Traits\KPIDefaultResponseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AdminHoldingKPIService
{
    use KPIDefaultResponseTrait;

    private function hitungSkorKetepatanPo($uploadedStr, $awalTrainingStr, $delay)
    {
        if (!$uploadedStr || !$awalTrainingStr) return 0;

        $uploaded = Carbon::parse($uploadedStr)->startOfDay();
        $awalTraining = Carbon::parse($awalTrainingStr)->startOfDay();
        
        $daysBefore = $awalTraining->diffInDays($uploaded);

        // Jika diunggah setelah hari H (minus), diffInDays tetap positif, maka cek manual arahnya
        if ($uploaded->gt($awalTraining)) {
            return 0;
        }

        if ($daysBefore >= 7) {
            return 100;
        } elseif ($daysBefore > 0) {
            return ($delay !== null && $delay !== 'Admin')
                ? min(100, ($daysBefore * 150) / 7)
                : ($daysBefore * 100) / 7;
        }

        return 0;
    }

    public function calculateKetepatanWaktuPo($item, $personId = null)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) return 0;

        $pos = NomorModul::with('moduls')->whereYear('created_at', $tahun)->get();
        if ($pos->isEmpty()) return 0.0;

        $totalPercent = 0;
        $count = 0;

        foreach ($pos as $po) {
            if (!$po->uploaded) continue;

            foreach ($po->moduls as $modul) {
                if (!$modul->awal_training) continue;

                $totalPercent += $this->hitungSkorKetepatanPo($po->uploaded, $modul->awal_training, $po->delay);
                $count++;
            }
        }

        if ($count === 0) return 0.0;

        return round($totalPercent / $count, 1);
    }

    public function calculateKetepatanWaktuPoDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();
        $emptyResponse = [
            'progress' => 0, 'gap' => 0, 'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [], 'daily_breakdown_per_month' => [],
            'monthly_progress' => [], 'daily_progress_per_month' => [],
        ];

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $emptyResponse;
        }

        $tahun = (int) $detail->detail_jangka;
        $nilaiTarget = (float) $detail->nilai_target;

        if ($tahun < 2000 || $tahun > now()->year + 5 || $nilaiTarget <= 0) return $emptyResponse;

        $pos = NomorModul::with('moduls')->whereYear('created_at', $tahun)->get();
        if ($pos->isEmpty()) return $emptyResponse;

        $totalPercent = 0;
        $count = 0;
        $aboveTarget = 0;

        $monthlyDataRaw = [];
        $dailyDataRaw = [];

        foreach ($pos as $po) {
            if (!$po->uploaded) continue;
            
            $date = Carbon::parse($po->uploaded);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            foreach ($po->moduls as $modul) {
                if (!$modul->awal_training) continue;

                $percent = $this->hitungSkorKetepatanPo($po->uploaded, $modul->awal_training, $po->delay);
                
                $totalPercent += $percent;
                $count++;

                if ($percent >= $nilaiTarget) {
                    $aboveTarget++;
                }

                // Kumpulkan untuk grafik rata-rata bulanan & harian
                $monthlyDataRaw[$monthKey][] = $percent;
                $dailyDataRaw[$monthKey][$dayKey][] = $percent;
            }
        }

        if ($count === 0) return $emptyResponse;

        // PERBAIKAN SINKRONISASI: Progress sekarang dihitung dari rata-rata kumulatif, sama dengan Primer
        $progress = round($totalPercent / $count, 1);
        
        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');
        if ($gap === '') $gap = '0';

        $monthlyAverages = [];
        $dailyBreakdownPerMonth = [];

        foreach ($monthlyDataRaw as $month => $values) {
            $monthlyAverages[$month] = round(array_sum($values) / count($values), 1);
        }

        foreach ($dailyDataRaw as $month => $days) {
            foreach ($days as $day => $values) {
                $dailyBreakdownPerMonth[$month][$day] = round(array_sum($values) / count($values), 1);
            }
            ksort($dailyBreakdownPerMonth[$month]);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $aboveTarget, // Jumlah modul yang memenuhi/melebihi target nilai kpi
                'below' => max(0, $count - $aboveTarget),
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyAverages, 
            'daily_progress_per_month' => $dailyBreakdownPerMonth,
        ];
    }

    public function calculatekualitasDokumentasiSupportDanProctor($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $registrasi = Registrasi::whereYear('created_at', $tahun)
            ->count();

        if ($registrasi === 0) {
            return 0.0;
        }

        $dataTerdokumentasi = DokumentasiExam::whereYear('created_at', $tahun)
            ->where(function ($q) {
                $q->whereNotNull('skor')
                    ->orWhereNotNull('dokumentasi');
            })
            ->count();

        $progress = ($dataTerdokumentasi / $registrasi) * 100;

        return round($progress, 2);
    }

    public function calculatekualitasDokumentasiSupportDanProctorDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (
            !$detail ||
            !is_numeric($detail->detail_jangka) ||
            !is_numeric($detail->nilai_target)
        ) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $tahun = (int) $detail->detail_jangka;
        $nilaiTarget = (float) $detail->nilai_target;

        if ($tahun < 2000 || $tahun > now()->year + 5 || $nilaiTarget <= 0) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $registrasi = Registrasi::whereBetween('created_at', [$start, $end])->get();

        if ($registrasi->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $dokumentasi = DokumentasiExam::whereBetween('created_at', [$start, $end])
            ->where(function ($q) {
                $q->whereNotNull('skor')
                    ->orWhereNotNull('dokumentasi');
            })
            ->get();

        $totalRegistrasi = $registrasi->count();
        $totalDokumentasi = $dokumentasi->count();

        $progress = ($totalDokumentasi / $totalRegistrasi) * 100;
        $progress = round($progress, 2);

        if ($progress > $nilaiTarget) {
            $gapRaw = 0;
        } else {
            $gapRaw = $progress - $nilaiTarget;
        }
        $gap = rtrim(rtrim(sprintf('%.2f', $gapRaw), '0'), '.');

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($dokumentasi as $doc) {
            $date = $doc->created_at;
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = 0;
                $monthlyProgress[$monthKey] = 0;
            }
            $monthlyData[$monthKey]++;
            $monthlyProgress[$monthKey]++;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
                $dailyProgressPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dayKey] =
                ($dailyBreakdownPerMonth[$monthKey][$dayKey] ?? 0) + 1;
            $dailyProgressPerMonth[$monthKey][$dayKey] =
                ($dailyProgressPerMonth[$monthKey][$dayKey] ?? 0) + 1;
        }

        $monthlyPercentages = [];
        $monthlyProgressPercentages = [];

        foreach ($monthlyData as $month => $countDok) {
            $registrasiPerMonth = $registrasi->filter(function ($r) use ($month) {
                return $r->created_at->format('Y-m') === $month;
            })->count();

            if ($registrasiPerMonth > 0) {
                $monthlyPercentages[$month] = round(($countDok / $registrasiPerMonth) * 100, 2);
                $monthlyProgressPercentages[$month] = round(($countDok / $registrasiPerMonth) * 100, 2);
            } else {
                $monthlyPercentages[$month] = 0;
                $monthlyProgressPercentages[$month] = 0;
            }
        }

        ksort($monthlyPercentages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressPercentages);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $totalDokumentasi,
                'below' => $totalRegistrasi - $totalDokumentasi,
            ],
            'monthly_data' => $monthlyPercentages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressPercentages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }
}