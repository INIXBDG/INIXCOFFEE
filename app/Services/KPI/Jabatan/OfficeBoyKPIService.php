<?php

namespace App\Services\KPI\Jabatan;

use App\Models\Nilaifeedback;
use App\Models\KontrolTugas;
use App\Traits\KPIDefaultResponseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class OfficeBoyKPIService
{
    use KPIDefaultResponseTrait;

    public function calculateFeedbackKebersihanDanKenyamanan($item, $personId)
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

            if ($avg >= 3.5) {
                $respondenPuas++;
            }
        }

        return round(($respondenPuas / $totalResponden) * 100, 1);
    }

    public function calculateFeedbackKebersihanDanKenyamananDetail($itemDetail, $personId = null)
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
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($feedbacks as $fb) {
            $f1 = (float) ($fb->F1 ?? 0);
            $f2 = (float) ($fb->F2 ?? 0);
            $f3 = (float) ($fb->F3 ?? 0);
            $f4 = (float) ($fb->F4 ?? 0);
            $f5 = (float) ($fb->F5 ?? 0);

            $avg = min(4, max(1, ($f1 + $f2 + $f3 + $f4 + $f5) / 5));
            $totalResponden++;
            
            $pct = round($avg * 25, 1);
            if ($avg >= 3.5) {
                $respondenPuas++;
            }

            $date = Carbon::parse($fb->created_at);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $monthlyData[$monthKey][] = $avg;
            $monthlyProgress[$monthKey][] = $pct;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $avg;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $pct;
        }

        $progress = round(($respondenPuas / $totalResponden) * 100, 1);
        $gapRaw = $progress - $nilaiTarget;
        $gap = $progress > $nilaiTarget ? 0 : rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');
        if ($gap === '') $gap = '0';

        $monthlyAverages = [];
        $monthlyProgressAverages = [];
        foreach ($monthlyData as $month => $dailyVals) {
            $monthlyAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
            $monthlyProgressAverages[$month] = round(array_sum($monthlyProgress[$month]) / count($monthlyProgress[$month]), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAverages);
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
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    public function calculatePenyelesaianTugasHarian($item, $personId)
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

        $query = KontrolTugas::whereYear('created_at', $tahun);

        if ($personId !== null) {
            $query->where('id_karyawan', $personId);
        }

        $jumlahTugas = $query->count();

        if ($jumlahTugas === 0) {
            return 0;
        }

        $jumlahTugasSelesai = (clone $query)->where('status', '1')->count();

        return round(($jumlahTugasSelesai / $jumlahTugas) * 100, 1);
    }

    public function calculatePenyelesaianTugasHarianDetail($itemDetail, $personId = null)
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

        $query = KontrolTugas::select('created_at', 'status')->whereYear('created_at', $tahun);

        if ($personId !== null) {
            $query->where('id_karyawan', $personId);
        }

        $tugas = $query->get();

        if ($tugas->isEmpty()) {
            return $this->getDefaultDetailResponse();
        }

        $jumlahTugas = $tugas->count();
        $jumlahTugasSelesai = $tugas->filter(fn($t) => $t->status == 1)->count();

        $progress = round(($jumlahTugasSelesai / $jumlahTugas) * 100, 1);
        $gapRaw = $progress - $nilaiTarget;
        $gap = $progress > $nilaiTarget ? 0 : rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');
        if ($gap === '') $gap = '0';

        $monthlyDataRaw = [];
        $dailyDataRaw = [];

        foreach ($tugas as $t) {
            $date = Carbon::parse($t->created_at);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $isSelesai = $t->status == 1 ? 1 : 0;

            if (!isset($monthlyDataRaw[$monthKey])) {
                $monthlyDataRaw[$monthKey] = ['total' => 0, 'selesai' => 0];
            }
            $monthlyDataRaw[$monthKey]['total']++;
            $monthlyDataRaw[$monthKey]['selesai'] += $isSelesai;

            if (!isset($dailyDataRaw[$monthKey][$dayKey])) {
                $dailyDataRaw[$monthKey][$dayKey] = ['total' => 0, 'selesai' => 0];
            }
            $dailyDataRaw[$monthKey][$dayKey]['total']++;
            $dailyDataRaw[$monthKey][$dayKey]['selesai'] += $isSelesai;
        }

        $monthlyAverages = [];
        $monthlyProgressAverages = [];
        $dailyBreakdownPerMonth = [];
        $dailyProgressPerMonth = [];

        foreach ($monthlyDataRaw as $month => $data) {
            $persentase = round(($data['selesai'] / $data['total']) * 100, 1);
            $monthlyAverages[$month] = $persentase;
            $monthlyProgressAverages[$month] = $persentase;
        }

        foreach ($dailyDataRaw as $month => $days) {
            foreach ($days as $day => $data) {
                $persentase = round(($data['selesai'] / $data['total']) * 100, 1);
                $dailyBreakdownPerMonth[$month][$day] = $persentase;
                $dailyProgressPerMonth[$month][$day] = $persentase;
            }
            ksort($dailyBreakdownPerMonth[$month]);
            ksort($dailyProgressPerMonth[$month]);
        }

        ksort($monthlyAverages);
        ksort($monthlyProgressAverages);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $jumlahTugasSelesai,
                'below' => max(0, $jumlahTugas - $jumlahTugasSelesai),
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }
}