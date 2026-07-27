<?php

namespace App\Services\KPI\Jabatan;

use App\Models\DokumentasiExam;
use App\Models\NomorModul;
use App\Models\Registrasi;
use App\Traits\KPIDefaultResponseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminHoldingKPIService
{
    use KPIDefaultResponseTrait;

    private function calculateAndFormatGap(float $progress, float $target): string
    {
        $gapRaw = $progress - $target;
        
        if (abs($gapRaw) < 0.01) {
            return '0';
        }

        $gap = rtrim(rtrim(sprintf('%.2f', $gapRaw), '0'), '.');
        return $gap === '' ? '0' : $gap;
    }

    private function getEmptyResponse(): array
    {
        return [
            'progress' => 0.0,
            'gap' => '0',
            'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
        ];
    }

    private function hitungSkorKetepatanPo($uploadedStr, $awalTrainingStr, $delay)
    {
        if (!$uploadedStr || !$awalTrainingStr) return 0.0;

        $uploaded = Carbon::parse($uploadedStr)->startOfDay();
        $awalTraining = Carbon::parse($awalTrainingStr)->startOfDay();
        
        $daysBefore = $awalTraining->diffInDays($uploaded);

        if ($uploaded->gt($awalTraining)) {
            return 0.0;
        }

        if ($daysBefore >= 7) {
            return 100.0;
        } 
 
        if ($daysBefore == 0) {
            return 100.0; 
        }

        if ($daysBefore > 0) {
            $isSpecialDelay = ($delay !== null && strtoupper(trim($delay)) !== 'ADMIN');
            return $isSpecialDelay 
                ? min(100.0, ($daysBefore * 150.0) / 7.0)
                : ($daysBefore * 100.0) / 7.0;
        }

        return 0.0;
    }

    public function calculateKetepatanWaktuPo($item, $personId = null)
    {
        $detail = $item->detailTargetKPI->first();
        
        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return 0.0;
        }

        $tahun = (int) $detail->detail_jangka;
        $nilaiTarget = (float) $detail->nilai_target;

        if ($tahun < 2000 || $tahun > now()->year + 5 || $nilaiTarget <= 0) {
            return 0.0;
        }

        $result = NomorModul::selectRaw('
            AVG(
                CASE
                    WHEN DATEDIFF(m.awal_training, nm.uploaded) < 0 THEN 0.0
                    WHEN DATEDIFF(m.awal_training, nm.uploaded) >= 7 THEN 100.0
                    WHEN DATEDIFF(m.awal_training, nm.uploaded) = 0 THEN 100.0
                    WHEN nm.delay IS NOT NULL AND UPPER(TRIM(nm.delay)) != "ADMIN" 
                        THEN LEAST(100.0, (DATEDIFF(m.awal_training, nm.uploaded) * 150.0) / 7.0)
                    ELSE (DATEDIFF(m.awal_training, nm.uploaded) * 100.0) / 7.0
                END
            ) as avg_progress,
            COUNT(nm.id) as total_count,
            SUM(
                CASE WHEN 
                    (CASE
                        WHEN DATEDIFF(m.awal_training, nm.uploaded) < 0 THEN 0.0
                        WHEN DATEDIFF(m.awal_training, nm.uploaded) >= 7 THEN 100.0
                        WHEN DATEDIFF(m.awal_training, nm.uploaded) = 0 THEN 100.0
                        WHEN nm.delay IS NOT NULL AND UPPER(TRIM(nm.delay)) != "ADMIN" 
                            THEN LEAST(100.0, (DATEDIFF(m.awal_training, nm.uploaded) * 150.0) / 7.0)
                        ELSE (DATEDIFF(m.awal_training, nm.uploaded) * 100.0) / 7.0
                    END) >= ? THEN 1 ELSE 0 END
            ) as above_count
        ', [$nilaiTarget])
        ->from('nomor_moduls as nm')
        ->join('moduls as m', 'nm.id', '=', 'm.no_modul')
        ->whereYear('nm.created_at', $tahun)
        ->whereNotNull('nm.uploaded')
        ->whereNotNull('m.awal_training')
        ->first();

        if (!$result || $result->total_count == 0) {
            return 0.0;
        }

        return round((float) $result->avg_progress, 2);
    }

    public function calculateKetepatanWaktuPoDetail($item, $personId = null)
    {
        $detail = $item->detailTargetKPI->first();
        
        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $this->getEmptyResponse();
        }

        $tahun = (int) $detail->detail_jangka;
        $nilaiTarget = (float) $detail->nilai_target;

        if ($tahun < 2000 || $tahun > now()->year + 5 || $nilaiTarget <= 0) {
            return $this->getEmptyResponse();
        }

        $posQuery = NomorModul::select('id', 'uploaded', 'delay')
            ->with('moduls:id,no_modul,awal_training')
            ->whereYear('created_at', $tahun)
            ->whereNotNull('uploaded');

        $pos = $posQuery->cursor();

        $totalPercent = 0.0;
        $count = 0;
        $aboveTarget = 0;

        $monthlyDataRaw = [];
        $dailyDataRaw = [];

        foreach ($pos as $po) {
            foreach ($po->moduls as $modul) {
                if (!$modul->awal_training) continue;

                $percent = $this->hitungSkorKetepatanPo($po->uploaded, $modul->awal_training, $po->delay);
                
                $totalPercent += $percent;
                $count++;

                if ($percent >= $nilaiTarget) {
                    $aboveTarget++;
                }

                $date = Carbon::parse($po->uploaded);
                $monthKey = $date->format('Y-m');
                $dayKey = $date->format('Y-m-d');

                $monthlyDataRaw[$monthKey][] = $percent;
                $dailyDataRaw[$monthKey][$dayKey][] = $percent;
            }
        }

        if ($count === 0) return $this->getEmptyResponse();

        $progress = round($totalPercent / $count, 2);
        $gap = $this->calculateAndFormatGap($progress, $nilaiTarget);

        $monthlyAverages = [];
        $dailyBreakdownPerMonth = [];

        foreach ($monthlyDataRaw as $month => $values) {
            $monthlyAverages[$month] = round(array_sum($values) / count($values), 2);
        }

        foreach ($dailyDataRaw as $month => $days) {
            foreach ($days as $day => $values) {
                $dailyBreakdownPerMonth[$month][$day] = round(array_sum($values) / count($values), 2);
            }
            ksort($dailyBreakdownPerMonth[$month]);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $aboveTarget,
                'below' => max(0, $count - $aboveTarget),
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyAverages, 
            'daily_progress_per_month' => $dailyBreakdownPerMonth,
        ];
    }

    public function calculatekualitasDokumentasiSupportDanProctor($item, $personId = null)
    {
        $detailResult = $this->calculatekualitasDokumentasiSupportDanProctorDetail($item, $personId);
        return $detailResult['progress'];
    }

    public function calculatekualitasDokumentasiSupportDanProctorDetail($item, $personId = null)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $this->getEmptyResponse();
        }

        $tahun = (int) $detail->detail_jangka;
        $nilaiTarget = (float) $detail->nilai_target;

        if ($tahun < 2000 || $tahun > now()->year + 5 || $nilaiTarget <= 0) {
            return $this->getEmptyResponse();
        }

        $tzExpression = "CONVERT_TZ(created_at, '+00:00', 'Asia/Jakarta')";

        $registrasiPerMonth = Registrasi::selectRaw("DATE_FORMAT({$tzExpression}, '%Y-%m') as month, COUNT(*) as total")
            ->whereYear('created_at', $tahun)
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $totalRegistrasi = array_sum($registrasiPerMonth);

        if ($totalRegistrasi === 0) {
            return $this->getEmptyResponse();
        }

        // 2. Total dokumentasi valid per bulan
        $dokumentasiPerMonth = DokumentasiExam::selectRaw("DATE_FORMAT({$tzExpression}, '%Y-%m') as month, COUNT(*) as total")
            ->whereYear('created_at', $tahun)
            ->where(function ($q) {
                $q->whereNotNull('skor')->orWhereNotNull('dokumentasi');
            })
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // 3. Breakdown harian
        $dailyDokumentasi = DokumentasiExam::selectRaw(
            "DATE_FORMAT({$tzExpression}, '%Y-%m') as month, 
             DATE_FORMAT({$tzExpression}, '%Y-%m-%d') as day, 
             COUNT(*) as total"
        )
            ->whereYear('created_at', $tahun)
            ->where(function ($q) {
                $q->whereNotNull('skor')->orWhereNotNull('dokumentasi');
            })
            ->groupBy('month', 'day')
            ->get();

        $totalDokumentasi = array_sum($dokumentasiPerMonth);
        $progress = round(($totalDokumentasi / $totalRegistrasi) * 100, 2);
        $gap = $this->calculateAndFormatGap($progress, $nilaiTarget);

        $monthlyPercentages = [];
        $aboveTargetMonths = 0;
        $belowTargetMonths = 0;

        foreach ($registrasiPerMonth as $month => $regCount) {
            $dokCount = $dokumentasiPerMonth[$month] ?? 0;
            $percent = round(($dokCount / $regCount) * 100, 2);
            $monthlyPercentages[$month] = $percent;

            if ($percent >= $nilaiTarget) {
                $aboveTargetMonths++;
            } else {
                $belowTargetMonths++;
            }
        }

        $dailyBreakdownPerMonth = [];
        foreach ($dailyDokumentasi as $row) {
            $dailyBreakdownPerMonth[$row->month][$row->day] = (int) $row->total;
        }

        ksort($monthlyPercentages);
        ksort($dailyBreakdownPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $aboveTargetMonths,
                'below' => $belowTargetMonths,
            ],
            'monthly_data' => $monthlyPercentages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyPercentages,
            'daily_progress_per_month' => $dailyBreakdownPerMonth,
        ];
    }
}