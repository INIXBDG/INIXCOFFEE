<?php

namespace App\Services\KPI\Jabatan;

use App\Models\SurveyKepuasan;
use App\Models\ActivityLog;
use App\Models\DetailPersonKPI;
use App\Traits\KPIDefaultResponseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class KoordinatorITSMKPIService
{
    use KPIDefaultResponseTrait;

    private function formatChartData($progress, $nilaiTarget, $above, $below, $rawDailyData)
    {
        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $dailyAverages = [];
        foreach ($rawDailyData as $dateStr => $values) {
            if (count($values) > 0) {
                $dailyAverages[$dateStr] = round(array_sum($values) / count($values), 1);
            }
        }

        $monthlyData = [];
        $monthlyProgress = [];
        $dailyBreakdownPerMonth = [];
        $dailyProgressPerMonth = [];

        foreach ($dailyAverages as $dateStr => $avg) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [];
                $monthlyProgress[$monthKey] = [];
            }
            $monthlyData[$monthKey][] = $avg;
            $monthlyProgress[$monthKey][] = $avg;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
                $dailyProgressPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $avg;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $avg;
        }

        $monthlyAverages = [];
        $monthlyProgressAverages = [];
        foreach ($monthlyData as $month => $dailyVals) {
            if (count($dailyVals) > 0) {
                $monthlyAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
            }
        }
        foreach ($monthlyProgress as $month => $dailyVals) {
            if (count($dailyVals) > 0) {
                $monthlyProgressAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
            }
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAverages);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

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

        $query = SurveyKepuasan::whereBetween('created_at', [$start, $end])
            ->select('q1', 'q2', 'q4');

        $dataSurvey = $query->get();

        if ($dataSurvey->isEmpty()) {
            return 0;
        }

        $totalResponden = 0;
        $respondenPuas = 0;

        foreach ($dataSurvey as $survey) {
            $nilaiQ1 = match ((int) $survey->q1) {
                1 => 10, 2 => 20, 3 => 30, 4 => 40, default => 0,
            };

            $nilaiQ4 = match ((int) $survey->q4) {
                1 => 10, 2 => 20, 3 => 30, 4 => 40, default => 0,
            };

            $nilaiQ2 = match ((string) $survey->q2) {
                'Ya' => 20, 'Tidak' => 10, default => 0,
            };

            $totalBaris = min(100, max(0, $nilaiQ1 + $nilaiQ2 + $nilaiQ4));
            $skor = 1 + ($totalBaris * 3) / 100;

            $totalResponden++;
            if ($skor >= 3.0) {
                $respondenPuas++;
            }
        }

        if ($totalResponden === 0) {
            return 0;
        }

        $progress = ($respondenPuas / $totalResponden) * 100;
        return round($progress, 1);
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

        $query = SurveyKepuasan::whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, q1, q2, q4');
            
        $dataSurvey = $query->get();

        if ($dataSurvey->isEmpty()) {
            return $this->getDefaultDetailResponse();
        }

        $totalResponden = 0;
        $respondenPuas = 0;
        $rawDailyData = [];

        foreach ($dataSurvey as $survey) {
            $nilaiQ1 = match ((int) $survey->q1) {
                1 => 10, 2 => 20, 3 => 30, 4 => 40, default => 0,
            };

            $nilaiQ4 = match ((int) $survey->q4) {
                1 => 10, 2 => 20, 3 => 30, 4 => 40, default => 0,
            };

            $nilaiQ2 = match ((string) $survey->q2) {
                'Ya' => 20, 'Tidak' => 10, default => 0,
            };

            $totalBaris = min(100, max(0, $nilaiQ1 + $nilaiQ2 + $nilaiQ4));
            $skor = 1 + ($totalBaris * 3) / 100;

            $totalResponden++;
            $isPuas = $skor >= 3.0 ? 1 : 0;
            
            if ($isPuas) {
                $respondenPuas++;
            }

            $rawDailyData[$survey->date][] = $isPuas * 100;
        }

        $progress = $totalResponden > 0 ? round(($respondenPuas / $totalResponden) * 100, 1) : 0;

        return $this->formatChartData($progress, $nilaiTarget, $respondenPuas, $totalResponden - $respondenPuas, $rawDailyData);
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

        $stats = ActivityLog::whereBetween('status', ['100', '599'])
            ->whereBetween('checked_at', [$start, $end])
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN is_up = 1 THEN 1 ELSE 0 END) as up_count')
            ->first();

        if (!$stats || $stats->total == 0) {
            return 0;
        }

        $availability = ($stats->up_count / $stats->total) * 100;
        return round($availability, 1);
    }

    public function calculateAvailabilitySistemInternalKritisDetail($itemDetail, $personId = null)
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

        $logs = ActivityLog::whereBetween('status', ['100', '599'])
            ->whereBetween('checked_at', [$start, $end])
            ->selectRaw('DATE(checked_at) as date, is_up')
            ->get();

        if ($logs->isEmpty()) {
            return $this->getDefaultDetailResponse();
        }

        $totalChecks = $logs->count();
        $upChecks = $logs->sum('is_up');
        
        if ($totalChecks === 0) {
            return $this->getDefaultDetailResponse();
        }

        $progress = round(($upChecks / $totalChecks) * 100, 1);

        $rawDailyData = [];
        foreach ($logs as $log) {
            $rawDailyData[$log->date][] = $log->is_up ? 100 : 0;
        }

        return $this->formatChartData($progress, $nilaiTarget, $upChecks, $totalChecks - $upChecks, $rawDailyData);
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

        $query = DetailPersonKPI::whereIn('detailTargetKey', $detailIds)
            ->select('presentase_kemampuan', 'presentase_standar');

        if ($personId !== null) {
            $query->where('id_karyawan', $personId);
        }

        $detailPersons = $query->get();

        if ($detailPersons->isEmpty()) {
            return 0;
        }

        $totalKemampuan = 0;
        $totalStandar = 0;

        foreach ($detailPersons as $detailPerson) {
            $standar = (float) $detailPerson->presentase_standar;
            if ($standar <= 0) {
                continue;
            }

            $totalKemampuan += (float) $detailPerson->presentase_kemampuan;
            $totalStandar += $standar;
        }

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

        $query = DetailPersonKPI::whereIn('detailTargetKey', $detailIds)
            ->select('presentase_kemampuan', 'presentase_standar');

        if ($personId !== null) {
            $query->where('id_karyawan', $personId);
        }

        $detailPersons = $query->get();

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

            if ($standar <= 0) {
                continue;
            }

            $totalKemampuan += $kemampuan;
            $totalStandar += $standar;

            if ($kemampuan >= $standar) {
                $above++;
            } else {
                $below++;
            }
        }

        if ($totalStandar <= 0) {
            $progress = 0;
            $gap = 0;
        } else {
            $progress = round(min(($totalKemampuan / $totalStandar) * 100, 100), 1);
            $gap = round($progress - $nilaiTarget, 1);
        }

        return $this->formatChartData($progress, $nilaiTarget, $above, $below, []);
    }
}