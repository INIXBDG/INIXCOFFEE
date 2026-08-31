<?php

namespace App\Services\KPI\Jabatan;

use App\Models\colaborator;
use App\Traits\KPIDefaultResponseTrait;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


class ProjectAdminKPIService
{
    use KPIDefaultResponseTrait;

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
