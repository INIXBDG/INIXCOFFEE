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

    private function ambilSkorTimGap($detailIds)
    {
        return detailPersonKPI::whereIn('detailTargetKey', $detailIds)
            ->whereNotNull('presentase_kemampuan')
            ->where('presentase_standar', '>', 0)
            ->whereHas('karyawan', fn($q) => $q->where('jabatan', '!=', 'Koordinator ITSM'))
            ->get(['id_karyawan', 'presentase_kemampuan', 'presentase_standar']);
    }

    private function hitungGapDariSkor($skor): array
    {
        $avgKemampuan = (float) $skor->avg('presentase_kemampuan');
        $avgStandar   = (float) $skor->avg('presentase_standar');

        $gap = $avgStandar > 0
            ? round(min(max($avgStandar - $avgKemampuan, 0), 100), 1)
            : 0;

        return [
            'gap'            => $gap,
            'avg_kemampuan'  => round($avgKemampuan, 1),
            'avg_standar'    => round($avgStandar, 1),
        ];
    }

    public function calculatePersentaseGapKompetensi($item, $personId = null)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail) {
            return null;
        }

        $skor = $this->ambilSkorTimGap($item->detailTargetKPI->pluck('id'));

        if ($skor->isEmpty()) {
            return null;
        }

        // Progress = rata-rata kemampuan langsung (contoh: 77.5)
        return $this->hitungGapDariSkor($skor)['avg_kemampuan'];
    }

    public function calculatePersentaseGapKompetensiDetail($itemDetail, $personId = null)
    {
        $details = $itemDetail->detailTargetKPI;

        if ($details->isEmpty()) {
            return $this->getDefaultDetailResponse();
        }

        $nilaiTarget = (float) $details->first()->nilai_target;
        if ($nilaiTarget <= 0) {
            return $this->getDefaultDetailResponse();
        }

        $skor = $this->ambilSkorTimGap($details->pluck('id'));

        if ($skor->isEmpty()) {
            return $this->getDefaultDetailResponse();
        }

        $hasil = $this->hitungGapDariSkor($skor);
        $progress = $hasil['avg_kemampuan']; // 77.5
        $gap      = $hasil['gap'];          // 100 - 77.5 = 22.5

        $above = $skor->filter(fn($s) => $s->presentase_kemampuan >= $s->presentase_standar)->count();
        $below = $skor->count() - $above;

        return [
            'progress'       => $progress,                    // 77.5
            'gap'            => rtrim(rtrim(sprintf('%.1f', $gap), '0'), '.'), // 22.5
            'is_inverse'     => false,                         // semakin tinggi semakin bagus
            'target_maks'    => $nilaiTarget,
            'tercapai'       => $progress >= $nilaiTarget,    // sesuaikan target di DataTarget
            'avg_kemampuan'  => $hasil['avg_kemampuan'],
            'jumlah_dinilai' => $skor->count(),
            'pie_chart'      => ['above' => $above, 'below' => $below],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
        ];
    }
}