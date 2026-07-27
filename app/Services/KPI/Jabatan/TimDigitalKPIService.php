<?php

namespace App\Services\KPI\Jabatan;

use App\Models\ContentSchedule;
use App\Models\colaborator;
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

        $query = ContentSchedule::whereBetween('upload_date', [$start, $end])
            ->whereNotNull('upload_date')
            ->select('upload_date');

        $contentSchedules = $query->get();

        if ($contentSchedules->isEmpty()) {
            return 0;
        }

        $weeklyCounts = [];

        foreach ($contentSchedules as $schedule) {
            $date = Carbon::parse($schedule->upload_date);

            $weekStart = $date->copy()->startOfWeek(Carbon::MONDAY);
            $weekEnd = $date->copy()->endOfWeek(Carbon::SUNDAY);

            $weekKey = $weekStart->format('Y-m-d') . '_' . $weekEnd->format('Y-m-d');

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

        $finalScore = $CS * 0.6 + $PS * 0.4;

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

        $query = ContentSchedule::whereBetween('upload_date', [$start, $end])
            ->whereNotNull('upload_date')
            ->select('upload_date');

        $contentSchedules = $query->get();

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

        $finalScore = $CS * 0.6 + $PS * 0.4;

        $progress = round($finalScore * 100, 1);
        $CSPercent = round($CS * 100, 1);
        $PSPercent = round($PS * 100, 1);

        $nilaiTarget = $details->pluck('nilai_target')->first() ?? 0;
        $gap = round($progress - $nilaiTarget, 1);

        $expectedTotal = $targetMingguan * $jumlahMinggu;

        $above = min($totalKonten, $expectedTotal);
        $below = max($expectedTotal - $totalKonten, 0);

        ksort($weeklyCounts);
        ksort($dailyBreakdownPerWeek);

        return array_merge($this->getDefaultDetailResponse(), [
            'progress' => $progress,
            'consistency_score' => $CSPercent,
            'productivity_score' => $PSPercent,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $above,
                'below' => $below,
            ],
            'weekly_data' => $weeklyCounts,
            'daily_breakdown_per_week' => $dailyBreakdownPerWeek,
        ]);
    }

    public function calculateEfektifitasDiitalMarketing($item, $personId)
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

        $query = colaborator::whereBetween('created_at', [$start, $end])->select('created_at');

        $dataColaborator = $query->get();

        $quartersWith = [];

        foreach ($dataColaborator as $colab) {
            $month = $colab->created_at->month;
            $quarter = (int) ceil($month / 3);
            $quartersWith[$quarter] = true;
        }

        $filledQuartersCount = count($quartersWith);

        return round(($filledQuartersCount / 4) * 100, 1);
    }

    public function calculateEfektifitasDiitalMarketingDetail($itemDetail, $personId = null)
    {
        $details = $itemDetail->detailTargetKPI;
        $detail = $details->first();

        if (is_null($detail) || is_null($detail->nilai_target) || is_null($detail->detail_jangka)) {
            return $this->getDefaultDetailResponse();
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $this->getDefaultDetailResponse();
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $query = colaborator::whereBetween('created_at', [$start, $end])->select('created_at');

        $dataColaborator = $query->get();

        $totalQuarters = 4;
        $quartersWith = [];

        foreach ($dataColaborator as $colab) {
            $month = (int) $colab->created_at->month;
            $quarter = (int) ceil($month / 3);
            $quartersWith[$quarter] = true;
        }

        $filledQuartersCount = count($quartersWith);

        $progress = round(($filledQuartersCount / $totalQuarters) * 100, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $filledQuartersCount;
        $below = $totalQuarters - $filledQuartersCount;

        $quarterlyData = [
            'Q1' => isset($quartersWith[1]) ? 100 : 0,
            'Q2' => isset($quartersWith[2]) ? 100 : 0,
            'Q3' => isset($quartersWith[3]) ? 100 : 0,
            'Q4' => isset($quartersWith[4]) ? 100 : 0,
        ];

        return array_merge($this->getDefaultDetailResponse(), [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'quarterly_data' => $quarterlyData,
        ]);
    }
}
