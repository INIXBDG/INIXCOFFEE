<?php

namespace App\Services\KPI\Jabatan;

use App\Models\SurveyKepuasan;
use App\Models\activityLog;
use App\Models\detailPersonKPI;
use App\Traits\KPIDefaultResponseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class KoordinatorITSMKPIService
{
    use KPIDefaultResponseTrait;

    public function calculateMeningkatkanKepuasanDanLoyalitasPeserta($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $dataSurvey = SurveyKepuasan::select('q1', 'q2', 'q4')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        if ($dataSurvey->isEmpty()) {
            return 0;
        }

        $totalResponden = 0;
        $respondenPuas = 0;

        foreach ($dataSurvey as $survey) {
            $nilaiQ1 = match ($survey->q1) {
                1 => 10, 2 => 20, 3 => 30, 4 => 40, default => 0,
            };

            $nilaiQ4 = match ($survey->q4) {
                1 => 10, 2 => 20, 3 => 30, 4 => 40, default => 0,
            };

            $nilaiQ2 = match ($survey->q2) {
                'Ya' => 20, 'Tidak' => 10, default => 0,
            };

            $totalBaris = min(100, max(0, $nilaiQ1 + $nilaiQ2 + $nilaiQ4));
            $skor = 1 + ($totalBaris * 3) / 100;

            $totalResponden++;
            if ($skor >= 3.0) {
                $respondenPuas++;
            }
        }

        return round(($respondenPuas / $totalResponden) * 100, 1);
    }

    public function calculateMeningkatkanKepuasanDanLoyalitasPesertaDetail($itemDetail, $personId = null)
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

        $dataSurvey = SurveyKepuasan::select('q1', 'q2', 'q4', 'created_at')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        if ($dataSurvey->isEmpty()) {
            return $this->getDefaultDetailResponse();
        }

        $totalResponden = 0;
        $respondenPuas = 0;
        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($dataSurvey as $survey) {
            $nilaiQ1 = match ($survey->q1) {
                1 => 10, 2 => 20, 3 => 30, 4 => 40, default => 0,
            };

            $nilaiQ4 = match ($survey->q4) {
                1 => 10, 2 => 20, 3 => 30, 4 => 40, default => 0,
            };

            $nilaiQ2 = match ($survey->q2) {
                'Ya' => 20, 'Tidak' => 10, default => 0,
            };

            $totalBaris = min(100, max(0, $nilaiQ1 + $nilaiQ2 + $nilaiQ4));
            $skor = 1 + ($totalBaris * 3) / 100;

            $totalResponden++;
            $isPuas = $skor >= 3.0 ? 100 : 0;
            if ($skor >= 3.0) $respondenPuas++;

            $date = Carbon::parse($survey->created_at);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $monthlyData[$monthKey][] = $skor;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $skor;
            $monthlyProgress[$monthKey][] = $isPuas;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $isPuas;
        }

        $progress = round(($respondenPuas / $totalResponden) * 100, 1);
        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

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
            'pie_chart' => ['above' => $respondenPuas, 'below' => $totalResponden - $respondenPuas],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAvg,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    public function calculateAvailabilitySistemInternalKritis($item, $personId)
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

        $stats = activityLog::whereBetween('status', ['100', '599'])
            ->whereBetween('checked_at', [$start, $end])
            ->selectRaw('COUNT(*) as total_checks, SUM(CASE WHEN is_up = 1 THEN 1 ELSE 0 END) as up_checks')
            ->first();

        $totalChecks = $stats->total_checks ?? 0;
        $upChecks = $stats->up_checks ?? 0;

        if ($totalChecks == 0) {
            return 0;
        }

        return round(($upChecks / $totalChecks) * 100, 1);
    }

    public function calculateAvailabilitySistemInternalKritisDetail($itemDetail, $personId = null)
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

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $logs = activityLog::select('checked_at', 'is_up')
            ->whereBetween('status', ['100', '599'])
            ->whereBetween('checked_at', [$start, $end])
            ->get();

        if ($logs->isEmpty()) {
            return $this->getDefaultDetailResponse();
        }

        $totalChecks = $logs->count();
        $upChecks = $logs->where('is_up', 1)->count();

        $progress = round(($upChecks / $totalChecks) * 100, 1);
        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($logs as $log) {
            $date = Carbon::parse($log->checked_at);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $value = $log->is_up ? 100 : 0;

            $monthlyData[$monthKey][] = $value;
            $dailyBreakdownPerMonth[$monthKey][$dayKey][] = $value;
            $monthlyProgress[$monthKey][] = $value;
            $dailyProgressPerMonth[$monthKey][$dayKey][] = $value;
        }

        $monthlyAverages = [];
        $monthlyProgressAvg = [];

        foreach ($monthlyData as $month => $values) {
            $monthlyAverages[$month] = round(array_sum($values) / count($values), 1);
        }

        foreach ($monthlyProgress as $month => $values) {
            $monthlyProgressAvg[$month] = round(array_sum($values) / count($values), 1);
        }

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $day => $values) {
                $dailyBreakdownPerMonth[$month][$day] = round(array_sum($values) / count($values), 1);
            }
        }

        foreach ($dailyProgressPerMonth as $month => $days) {
            foreach ($days as $day => $values) {
                $dailyProgressPerMonth[$month][$day] = round(array_sum($values) / count($values), 1);
            }
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAvg);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $upChecks, 'below' => $totalChecks - $upChecks],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAvg,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    public function calculatePersentaseGapKompetensi($item, $personId = null)
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

        $detailIds = $item->detailTargetKPI->pluck('id');

        $query = detailPersonKPI::whereIn('detailTargetKey', $detailIds);

        if ($personId !== null) {
            $query->where('id_karyawan', $personId);
        }

        $stats = $query->where('presentase_standar', '>', 0)
            ->selectRaw('SUM(presentase_kemampuan) as total_kemampuan, SUM(presentase_standar) as total_standar')
            ->first();

        $totalKemampuan = (float) ($stats->total_kemampuan ?? 0);
        $totalStandar = (float) ($stats->total_standar ?? 0);

        if ($totalStandar <= 0) {
            return 0;
        }

        $progress = ($totalKemampuan / $totalStandar) * 100;

        return round(min($progress, 100), 1);
    }

    public function calculatePersentaseGapKompetensiDetail($itemDetail, $personId = null)
    {
        $details = $itemDetail->detailTargetKPI;

        if ($details->isEmpty()) {
            return $this->getDefaultDetailResponse();
        }

        $firstDetail = $details->first();
        $nilaiTarget = (float) $firstDetail->nilai_target;
        $tahun = (int) $firstDetail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $this->getDefaultDetailResponse();
        }

        $detailIds = $details->pluck('id');

        $query = detailPersonKPI::whereIn('detailTargetKey', $detailIds);

        if ($personId !== null) {
            $query->where('id_karyawan', $personId);
        }

        $detailPersons = $query->select('presentase_kemampuan', 'presentase_standar')
            ->where('presentase_standar', '>', 0)
            ->get();

        if ($detailPersons->isEmpty()) {
            return $this->getDefaultDetailResponse();
        }

        $totalKemampuan = 0;
        $totalStandar = 0;
        $above = 0;
        $below = 0;

        foreach ($detailPersons as $dp) {
            $kemampuan = (float) $dp->presentase_kemampuan;
            $standar = (float) $dp->presentase_standar;

            $totalKemampuan += $kemampuan;
            $totalStandar += $standar;

            if ($kemampuan >= $standar) {
                $above++;
            } else {
                $below++;
            }
        }

        $progress = ($totalStandar > 0) ? round(min(($totalKemampuan / $totalStandar) * 100, 100), 1) : 0;
        $gap = round(100 - $progress, 1);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
        ];
    }
}