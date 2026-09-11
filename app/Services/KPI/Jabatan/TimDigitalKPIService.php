<?php

namespace App\Services\KPI\Jabatan;

use App\Models\ContentSchedule;
use App\Traits\KPIDefaultResponseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TimDigitalKPIService
{
    use KPIDefaultResponseTrait;

    public function calculateKonsistensiCampaignDigital($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail || !$detail->detail_jangka) {
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return 0;
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $contentSchedules = ContentSchedule::select('upload_date')
            ->whereBetween('upload_date', [$start, $end])
            ->whereNotNull('upload_date')
            ->get();

        if ($contentSchedules->isEmpty()) {
            return 0;
        }

        $weeklyCounts = [];

        foreach ($contentSchedules as $schedule) {
            $date = Carbon::parse($schedule->upload_date);
            $weekKey = $date->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d') . '_' . 
                       $date->copy()->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

            $weeklyCounts[$weekKey] = ($weeklyCounts[$weekKey] ?? 0) + 1;
        }

        $targetMingguan = 3;
        $compliantWeeks = 0;
        $totalWeeksWithData = 0;

        foreach ($weeklyCounts as $count) {
            if ($count >= 1) {
                $totalWeeksWithData++;
                if ($count >= $targetMingguan) {
                    $compliantWeeks++;
                }
            }
        }

        $CS = $totalWeeksWithData === 0 ? 0 : $compliantWeeks / $totalWeeksWithData;
        $totalKonten = $contentSchedules->count();

        $jumlahMinggu = 0;
        $current = $start->copy()->startOfWeek(Carbon::MONDAY);
        $endOfYearWeek = $end->copy()->endOfWeek(Carbon::SUNDAY);

        while ($current <= $endOfYearWeek) {
            $jumlahMinggu++;
            $current->addWeek();
        }

        $PS = $totalKonten / ($targetMingguan * $jumlahMinggu);
        $PS = min($PS, 1);

        $finalScore = ($CS * 0.6) + ($PS * 0.4);

        return round($finalScore * 100, 1);
    }

    public function calculateKonsistensiCampaignDigitalDetail($itemDetail, $personId = null)
    {
        $details = $itemDetail->detailTargetKPI;

        if ($details->isEmpty()) {
            return array_merge($this->getDefaultDetailResponse(), [
                'consistency_score' => 0,
                'productivity_score' => 0,
            ]);
        }

        $tahun = (int) $details->first()->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return array_merge($this->getDefaultDetailResponse(), [
                'consistency_score' => 0,
                'productivity_score' => 0,
            ]);
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $contentSchedules = ContentSchedule::select('upload_date')
            ->whereBetween('upload_date', [$start, $end])
            ->whereNotNull('upload_date')
            ->get();

        if ($contentSchedules->isEmpty()) {
            return array_merge($this->getDefaultDetailResponse(), [
                'consistency_score' => 0,
                'productivity_score' => 0,
            ]);
        }

        $weeklyCounts = [];
        $dailyBreakdownPerWeek = [];

        foreach ($contentSchedules as $schedule) {
            $date = Carbon::parse($schedule->upload_date);

            $weekStart = $date->copy()->startOfWeek(Carbon::MONDAY);
            $weekEnd = $date->copy()->endOfWeek(Carbon::SUNDAY);

            $weekKey = $weekStart->format('Y-m-d') . '_' . $weekEnd->format('Y-m-d');
            $dayKey = $date->format('Y-m-d');

            $weeklyCounts[$weekKey] = ($weeklyCounts[$weekKey] ?? 0) + 1;

            if (!isset($dailyBreakdownPerWeek[$weekKey])) {
                $dailyBreakdownPerWeek[$weekKey] = [];
            }

            $dailyBreakdownPerWeek[$weekKey][$dayKey] = ($dailyBreakdownPerWeek[$weekKey][$dayKey] ?? 0) + 1;
        }

        $targetMingguan = 3;
        $compliantWeeks = 0;
        $totalWeeksWithData = 0;

        foreach ($weeklyCounts as $count) {
            if ($count >= 1) {
                $totalWeeksWithData++;
                if ($count >= $targetMingguan) {
                    $compliantWeeks++;
                }
            }
        }

        $CS = $totalWeeksWithData === 0 ? 0 : $compliantWeeks / $totalWeeksWithData;
        $totalKonten = $contentSchedules->count();

        $jumlahMinggu = 0;
        $current = $start->copy()->startOfWeek(Carbon::MONDAY);
        $endOfYearWeek = $end->copy()->endOfWeek(Carbon::SUNDAY);

        while ($current <= $endOfYearWeek) {
            $jumlahMinggu++;
            $current->addWeek();
        }

        $PS = $totalKonten / ($targetMingguan * $jumlahMinggu);
        $PS = min($PS, 1);

        $finalScore = ($CS * 0.6) + ($PS * 0.4);

        $progress = round($finalScore * 100, 1);
        $CSPercent = round($CS * 100, 1);
        $PSPercent = round($PS * 100, 1);

        $nilaiTarget = (float) ($details->pluck('nilai_target')->first() ?? 0);
        $gap = round($progress - $nilaiTarget, 1);

        $expectedTotal = $targetMingguan * $jumlahMinggu;
        $above = min($totalKonten, $expectedTotal);
        $below = max($expectedTotal - $totalKonten, 0);

        ksort($weeklyCounts);
        ksort($dailyBreakdownPerWeek);

        return [
            'progress' => $progress,
            'consistency_score' => $CSPercent,
            'productivity_score' => $PSPercent,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $above,
                'below' => $below
            ],
            'monthly_data' => $weeklyCounts,
            'daily_breakdown_per_month' => $dailyBreakdownPerWeek,
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
        ];
    }
}