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

    /**
     * Fungsi Helper Terpusat untuk menghitung skor feedback.
     * Menjamin konsistensi matematika antara fungsi Main dan Detail (DRY).
     */
    private function calculateFeedbackScore($f1, $f2, $f3, $f4, $f5)
    {
        $f1 = is_numeric($f1) ? (float) $f1 : 0;
        $f2 = is_numeric($f2) ? (float) $f2 : 0;
        $f3 = is_numeric($f3) ? (float) $f3 : 0;
        $f4 = is_numeric($f4) ? (float) $f4 : 0;
        $f5 = is_numeric($f5) ? (float) $f5 : 0;

        $avg = ($f1 + $f2 + $f3 + $f4 + $f5) / 5;
        $avg = min(4.0, max(1.0, $avg));
        
        return [
            'avg' => $avg,
            'is_puas' => $avg >= 3.5
        ];
    }

    /**
     * Fungsi Helper Terpusat untuk memformat data Chart/Visualisasi.
     * Menjamin konsistensi format di seluruh fungsi Detail dan mencegah duplikasi kode.
     */
    private function formatChartData($progress, $nilaiTarget, $above, $below, $rawDailyData)
    {
        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');
        if ($gap === '' || $gap === '-') {
            $gap = '0';
        }

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

        $query = Nilaifeedback::whereBetween('created_at', [$start, $end])
            ->select('F1', 'F2', 'F3', 'F4', 'F5');

        $feedbacks = $query->get();

        if ($feedbacks->isEmpty()) {
            return 0;
        }

        $totalResponden = 0;
        $respondenPuas = 0;

        foreach ($feedbacks as $fb) {
            $score = $this->calculateFeedbackScore($fb->F1, $fb->F2, $fb->F3, $fb->F4, $fb->F5);
            $totalResponden++;
            if ($score['is_puas']) {
                $respondenPuas++;
            }
        }

        if ($totalResponden === 0) {
            return 0;
        }

        $progress = ($respondenPuas / $totalResponden) * 100;
        return round($progress, 1);
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

        $query = Nilaifeedback::whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, F1, F2, F3, F4, F5')
            ->whereNotNull('created_at');

        $feedbacks = $query->get();

        if ($feedbacks->isEmpty()) {
            return $this->getDefaultDetailResponse();
        }

        $totalResponden = 0;
        $respondenPuas = 0;
        $rawDailyData = [];

        foreach ($feedbacks as $fb) {
            $score = $this->calculateFeedbackScore($fb->F1, $fb->F2, $fb->F3, $fb->F4, $fb->F5);
            $totalResponden++;
            
            if ($score['is_puas']) {
                $respondenPuas++;
            }

            // Null safe date handling
            $dateKey = $fb->date ?: now()->format('Y-m-d');
            $rawDailyData[$dateKey][] = $score['is_puas'] ? 100 : 0;
        }

        $progress = $totalResponden > 0 ? round(($respondenPuas / $totalResponden) * 100, 1) : 0;

        return $this->formatChartData($progress, $nilaiTarget, $respondenPuas, $totalResponden - $respondenPuas, $rawDailyData);
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

        // PERBAIKAN: Agregasi langsung di level database (O(1) memory) untuk menghindari mutasi query builder
        // dan mencegah code smell dari penggunaan instance query yang sama untuk dua operasi count.
        $stats = (clone $query)->selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN status = 1 OR status = "1" THEN 1 ELSE 0 END) as selesai
        ')->first();

        if (!$stats || $stats->total == 0) {
            return 0;
        }

        $presentase = ($stats->selesai / $stats->total) * 100;
        return round($presentase, 1);
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

        // PERBAIKAN: Optimasi memori dengan hanya mengambil kolom yang diperlukan
        $query = KontrolTugas::whereYear('created_at', $tahun)
            ->selectRaw('DATE(created_at) as date, status')
            ->whereNotNull('created_at');

        if ($personId !== null) {
            $query->where('id_karyawan', $personId);
        }

        $tugas = $query->get();

        if ($tugas->isEmpty()) {
            return $this->getDefaultDetailResponse();
        }

        $jumlahTugas = $tugas->count();
        
        // PERBAIKAN: Standarisasi penanganan tipe data status (aman untuk string '1' atau integer 1)
        $jumlahTugasSelesai = $tugas->filter(fn($t) => (string) $t->status === '1')->count();

        $progress = $jumlahTugas > 0 ? round(($jumlahTugasSelesai / $jumlahTugas) * 100, 1) : 0;

        $rawDailyData = [];
        foreach ($tugas as $t) {
            // Null safe date handling
            $dateKey = $t->date ?: now()->format('Y-m-d');
            $isSelesai = ((string) $t->status === '1') ? 100 : 0;
            $rawDailyData[$dateKey][] = $isSelesai;
        }

        return $this->formatChartData($progress, $nilaiTarget, $jumlahTugasSelesai, $jumlahTugas - $jumlahTugasSelesai, $rawDailyData);
    }
}