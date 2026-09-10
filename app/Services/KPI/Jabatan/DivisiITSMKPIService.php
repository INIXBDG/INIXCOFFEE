<?php

namespace App\Services\KPI\Jabatan;

use App\Models\Nilaifeedback;
use App\Models\IdeInovasi;
use App\Traits\KPIDefaultResponseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DivisiITSMKPIService
{
    use KPIDefaultResponseTrait;

    public function calculateProgressKepuasanClientITSM($item, $personId)
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

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $feedbacks = Nilaifeedback::select('F1', 'F2', 'F3', 'F4', 'F5')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        if ($feedbacks->isEmpty()) {
            return 0;
        }

        $totalResponden = 0;
        $respondenPuas = 0;

        foreach ($feedbacks as $fb) {
            $f1 = (float) ($fb->F1 ?? 0);
            $f2 = (float) ($fb->F2 ?? 0);
            $f3 = (float) ($fb->F3 ?? 0);
            $f4 = (float) ($fb->F4 ?? 0);
            $f5 = (float) ($fb->F5 ?? 0);

            $avg = min(4, max(1, ($f1 + $f2 + $f3 + $f4 + $f5) / 5));
            $totalResponden++;

            if ($avg >= 3.0) {
                $respondenPuas++;
            }
        }

        return $totalResponden > 0 ? round(($respondenPuas / $totalResponden) * 100, 1) : 0;
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

        $feedbacks = Nilaifeedback::select('F1', 'F2', 'F3', 'F4', 'F5', 'created_at')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        if ($feedbacks->isEmpty()) {
            return $this->getDefaultDetailResponse();
        }

        $totalResponden = 0;
        $respondenPuas = 0;
        $monthlyData = [];
        $monthlyProgress = [];
        $dailyBreakdownPerMonth = [];
        $dailyProgressPerMonth = [];

        foreach ($feedbacks as $fb) {
            $f1 = (float) ($fb->F1 ?? 0);
            $f2 = (float) ($fb->F2 ?? 0);
            $f3 = (float) ($fb->F3 ?? 0);
            $f4 = (float) ($fb->F4 ?? 0);
            $f5 = (float) ($fb->F5 ?? 0);

            $avg = min(4, max(1, ($f1 + $f2 + $f3 + $f4 + $f5) / 5));
            $totalResponden++;

            if ($avg >= 3.0) {
                $respondenPuas++;
            }

            $date = Carbon::parse($fb->created_at);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $progressVal = $avg >= 3.0 ? 100 : round(($avg / 4) * 100, 1);

            $monthlyData[$monthKey][] = $avg;
            $monthlyProgress[$monthKey][] = $progressVal;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $avg;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $progressVal;
        }

        $progress = $totalResponden > 0 ? round(($respondenPuas / $totalResponden) * 100, 1) : 0;
        $gapRaw = $progress - $nilaiTarget;
        $gap = $progress > $nilaiTarget ? 0 : rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $monthlyAverages = [];
        $monthlyProgressAvg = [];

        foreach ($monthlyData as $month => $dailyVals) {
            $monthlyAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
        }

        foreach ($monthlyProgress as $month => $vals) {
            $monthlyProgressAvg[$month] = round(array_sum($vals) / count($vals), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAvg);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $respondenPuas,
                'below' => max(0, $totalResponden - $respondenPuas),
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAvg,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    public function calculateInovationAdaptionRate($item, $personId)
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

        $totalIde = IdeInovasi::whereYear('created_at', $tahun)->count();

        return $totalIde > 0 ? 100.0 : 0.0;
    }

    public function calculateInovationAdaptionRateDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->nilai_target)) {
            return $this->getDefaultDetailResponse();
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) ($detail->detail_jangka ?? now()->year);

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return $this->getDefaultDetailResponse();
        }

        $start = "$tahun-01-01";
        $end = "$tahun-12-31";

        $ideInovasi = IdeInovasi::selectRaw('DATE(created_at) as tanggal')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('tanggal')
            ->get();

        if ($ideInovasi->isEmpty()) {
            return $this->getDefaultDetailResponse();
        }

        $totalDays = $ideInovasi->count();
        $progress = 100.0;
        $gapRaw = $progress - $nilaiTarget;
        $gap = $progress > $nilaiTarget ? 0 : rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($ideInovasi as $row) {
            $date = Carbon::parse($row->tanggal);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $monthlyData[$monthKey][] = 100;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = 100;
            $monthlyProgress[$monthKey][] = 100;
            $dailyProgressPerMonth[$monthKey][$dayKey] = 100;
        }

        $monthlyAverages = [];
        $monthlyProgressAvg = [];

        foreach ($monthlyData as $month => $values) {
            $monthlyAverages[$month] = 100.0;
        }

        foreach ($monthlyProgress as $month => $values) {
            $monthlyProgressAvg[$month] = 100.0;
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAvg);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $totalDays,
                'below' => 0,
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAvg,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }
}