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

        $contentSchedules = ContentSchedule::whereBetween('upload_date', [$start, $end])
            ->whereNotNull('upload_date')
            ->get();

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

        $contentSchedules = ContentSchedule::whereBetween('upload_date', [$start, $end])
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

            $dailyBreakdownPerWeek[$weekKey][$dayKey] =
                ($dailyBreakdownPerWeek[$weekKey][$dayKey] ?? 0) + 1;
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

        $nilaiTarget = $details->pluck('nilai_target')->first() ?? 0;
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

        $dataColaborator = colaborator::whereBetween('created_at', [$start, $end])->get();

        $quartersWith = [];

        foreach ($dataColaborator as $colab) {
            $month = $colab->created_at->month;
            $quarter = (int) ceil($month / 3);
            $quartersWith[$quarter] = true;
        }

        $filledQuartersCount = count($quartersWith);

        return (string) round($filledQuartersCount);
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

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 1) {
            if ($tahun < 2000 || $tahun > now()->year + 1) {
                $tahun = now()->year;
            }
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $dataColaborator = colaborator::whereBetween('created_at', [$start, $end])->get();

        $totalData = $dataColaborator->count();

        if ($totalData === 0) {
            return array_merge($this->getDefaultDetailResponse(), [
                'gap' => rtrim(rtrim(sprintf('%.1f', (float)(0 - $nilaiTarget)), '0'), '.'),
                'pie_chart' => ['above' => 0, 'below' => 4],
            ]);
        }

        $totalQuarters = 4;
        $quartersWith = [];

        foreach ($dataColaborator as $colab) {
            $month = (int) $colab->created_at->month;
            $quarter = (int) ceil($month / 3);
            $quartersWith[$quarter] = true;
        }

        $filledQuartersCount = count($quartersWith);
        $konsistensiPersen = (float) $filledQuartersCount;
        $progress = (float) round($konsistensiPersen);

        $gapRaw = (float) ($progress - $nilaiTarget);
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = (int) $filledQuartersCount;
        $below = (int) ($totalQuarters - $filledQuartersCount);

        $dailyValues = [];

        foreach ($dataColaborator as $colab) {
            $tanggal = Carbon::parse($colab->created_at);
            $dateKey = $tanggal->format('Y-m-d');

            if (!isset($dailyValues[$dateKey])) {
                $dailyValues[$dateKey] = [];
            }

            $dailyValues[$dateKey][] = 1;
        }

        $dailyAverages = [];
        foreach ($dailyValues as $dateStr => $values) {
            $dailyAverages[$dateStr] = (float) round(array_sum($values) / count($values), 1);
        }

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($dailyAverages as $dateStr => $avg) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $monthlyData[$monthKey][] = $avg;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $avg;

            $progressVal = (float) round(min($avg * 100, 100), 1);

            $monthlyProgress[$monthKey][] = $progressVal;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $progressVal;
        }

        $monthlyAverages = [];
        $monthlyProgressAvg = [];

        foreach ($monthlyData as $month => $dailyVals) {
            $monthlyAverages[$month] = (float) round(array_sum($dailyVals) / count($dailyVals), 1);
        }

        foreach ($monthlyProgress as $month => $vals) {
            $monthlyProgressAvg[$month] = (float) round(array_sum($vals) / count($vals), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAvg);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAvg,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }
}
