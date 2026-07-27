<?php

namespace App\Services\KPI\Jabatan;

use App\Models\Nilaifeedback;
use App\Models\IdeInovasi;
use App\Traits\KPIDefaultResponseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DivisiITSMKPIService
{
    use KPIDefaultResponseTrait;

    private function calculateAndFormatGap(float $progress, float $target): string
    {
        $gapRaw = $progress - $target;
        
        if (abs($gapRaw) < 0.05) {
            return '0';
        }

        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');
        return $gap === '' ? '0' : $gap;
    }

    private function getDefaultDetailResponse(): array
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

    public function calculateProgressKepuasanClientITSM($item, $personId = null)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return 0.0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) return 0.0;

        $stats = Nilaifeedback::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN (
                COALESCE(F1,0) + COALESCE(F2,0) + COALESCE(F3,0) + COALESCE(F4,0) + COALESCE(F5,0)
            ) / 5.0 >= 3.0 THEN 1 ELSE 0 END) as puas
        ")
            ->whereYear('created_at', $tahun)
            ->first();

        $total = $stats->total ?? 0;
        if ($total === 0) return 0.0;

        return round(($stats->puas / $total) * 100.0, 1);
    }

    public function calculateProgressKepuasanClientITSMDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();
        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $this->getDefaultDetailResponse();
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $this->getDefaultDetailResponse();
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $totalResponden = 0;
        $respondenPuas = 0;

        $monthlyData = [];
        $monthlyProgress = [];
        $dailyBreakdownPerMonth = [];
        $dailyProgressPerMonth = [];

        $feedbacks = Nilaifeedback::select('created_at', 'F1', 'F2', 'F3', 'F4', 'F5')
            ->whereBetween('created_at', [$start, $end])
            ->cursor();

        foreach ($feedbacks as $fb) {
            $f1 = is_numeric($fb->F1) ? (float) $fb->F1 : 0.0;
            $f2 = is_numeric($fb->F2) ? (float) $fb->F2 : 0.0;
            $f3 = is_numeric($fb->F3) ? (float) $fb->F3 : 0.0;
            $f4 = is_numeric($fb->F4) ? (float) $fb->F4 : 0.0;
            $f5 = is_numeric($fb->F5) ? (float) $fb->F5 : 0.0;

            $avg = ($f1 + $f2 + $f3 + $f4 + $f5) / 5.0;
            $avg = min(4.0, max(1.0, $avg));

            $totalResponden++;
            $isPuas = $avg >= 3.0;
            
            if ($isPuas) {
                $respondenPuas++;
            }

            $date = Carbon::parse($fb->created_at);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $progressVal = $isPuas ? 100.0 : 0.0;

            $monthlyData[$monthKey][] = $avg;
            $monthlyProgress[$monthKey][] = $progressVal;
            
            $dailyBreakdownPerMonth[$monthKey][$dayKey][] = $avg;
            $dailyProgressPerMonth[$monthKey][$dayKey][] = $progressVal;
        }

        if ($totalResponden === 0) {
            return $this->getDefaultDetailResponse();
        }

        $progress = round(($respondenPuas / $totalResponden) * 100.0, 1);
        $gap = $this->calculateAndFormatGap($progress, $nilaiTarget);

        $monthlyAverages = [];
        $monthlyProgressAvg = [];
        foreach ($monthlyData as $month => $vals) {
            $monthlyAverages[$month] = round(array_sum($vals) / count($vals), 1);
            $monthlyProgressAvg[$month] = round(array_sum($monthlyProgress[$month]) / count($monthlyProgress[$month]), 1);
        }

        $dailyAverages = [];
        $dailyProgressAvg = [];
        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $day => $vals) {
                $dailyAverages[$month][$day] = round(array_sum($vals) / count($vals), 1);
                $dailyProgressAvg[$month][$day] = round(array_sum($dailyProgressPerMonth[$month][$day]) / count($dailyProgressPerMonth[$month][$day]), 1);
            }
            ksort($dailyAverages[$month]);
            ksort($dailyProgressAvg[$month]);
        }

        ksort($monthlyAverages);
        ksort($monthlyProgressAvg);
        ksort($dailyAverages);
        ksort($dailyProgressAvg);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $respondenPuas,
                'below' => max(0, $totalResponden - $respondenPuas),
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyAverages,
            'monthly_progress' => $monthlyProgressAvg,
            'daily_progress_per_month' => $dailyProgressAvg,
        ];
    }

    public function calculateInovationAdaptionRate($item, $personId = null)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return 0.0;
        }

        $tahun = (int) $detail->detail_jangka;
        $nilaiTarget = (float) $detail->nilai_target;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return 0.0;
        }

        $totalIde = IdeInovasi::whereYear('created_at', $tahun)
            ->count();

        return round(min(100.0, ($totalIde / $nilaiTarget) * 100.0), 1);
    }

    public function calculateInovationAdaptionRateDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();
        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $this->getDefaultDetailResponse();
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $this->getDefaultDetailResponse();
        }

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        $ideInovasi = IdeInovasi::select('created_at')
            ->whereYear('created_at', $tahun)
            ->cursor();

        foreach ($ideInovasi as $ide) {
            $date = Carbon::parse($ide->created_at);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $dailyBreakdownPerMonth[$monthKey][$dayKey] = ($dailyBreakdownPerMonth[$monthKey][$dayKey] ?? 0) + 1;
            $monthlyData[$monthKey] = ($monthlyData[$monthKey] ?? 0) + 1;
        }

        $totalIde = array_sum($monthlyData);
        $progress = $nilaiTarget > 0 ? round(min(100.0, ($totalIde / $nilaiTarget) * 100.0), 1) : 0.0;
        $gap = $this->calculateAndFormatGap($progress, $nilaiTarget);

        foreach ($monthlyData as $month => $count) {
            $monthlyProgress[$month] = $nilaiTarget > 0 ? round(($count / $nilaiTarget) * 100.0, 1) : 0.0;
        }

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $day => $count) {
                $dailyProgressPerMonth[$month][$day] = $nilaiTarget > 0 ? round(($count / $nilaiTarget) * 100.0, 1) : 0.0;
            }
            ksort($dailyBreakdownPerMonth[$month]);
            ksort($dailyProgressPerMonth[$month]);
        }

        ksort($monthlyData);
        ksort($monthlyProgress);
        ksort($dailyBreakdownPerMonth);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $totalIde,
                'below' => max(0, ceil($nilaiTarget) - $totalIde),
            ],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }
}