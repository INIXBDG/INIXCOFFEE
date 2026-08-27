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

        $allScores = [];

        $feedbacks = Nilaifeedback::whereBetween('created_at', [$start, $end])->get();

        foreach ($feedbacks as $fb) {
            $f1 = is_numeric($fb->F1) ? (float) $fb->F1 : 0;
            $f2 = is_numeric($fb->F2) ? (float) $fb->F2 : 0;
            $f3 = is_numeric($fb->F3) ? (float) $fb->F3 : 0;
            $f4 = is_numeric($fb->F4) ? (float) $fb->F4 : 0;
            $f5 = is_numeric($fb->F5) ? (float) $fb->F5 : 0;

            $avg = ($f1 + $f2 + $f3 + $f4 + $f5) / 5;
            $avg = min(4, max(1, $avg));
            $allScores[] = $avg;
        }

        if (empty($allScores)) {
            return 0;
        }

        $totalResponden = count($allScores);
        $respondenPuas = 0;

        foreach ($allScores as $skor) {
            if ($skor >= 3.5) {
                $respondenPuas++;
            }
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

        $allScores = [];
        $scoreDatePairs = [];

        $feedbacks = Nilaifeedback::whereBetween('created_at', [$start, $end])->get();

        foreach ($feedbacks as $fb) {
            $f1 = is_numeric($fb->F1) ? (float) $fb->F1 : 0;
            $f2 = is_numeric($fb->F2) ? (float) $fb->F2 : 0;
            $f3 = is_numeric($fb->F3) ? (float) $fb->F3 : 0;
            $f4 = is_numeric($fb->F4) ? (float) $fb->F4 : 0;
            $f5 = is_numeric($fb->F5) ? (float) $fb->F5 : 0;

            $avg = ($f1 + $f2 + $f3 + $f4 + $f5) / 5;
            $avg = min(4, max(1, $avg));

            $allScores[] = $avg;
            $scoreDatePairs[] = [
                'score' => $avg,
                'date' => $fb->created_at->format('Y-m-d'),
            ];
        }

        if (empty($allScores)) {
            return $this->getDefaultDetailResponse();
        }

        $totalResponden = count($allScores);
        $respondenPuas = 0;

        foreach ($allScores as $skor) {
            if ($skor >= 3.5) {
                $respondenPuas++;
            }
        }

        $progress = ($respondenPuas / $totalResponden) * 100;
        $progress = round($progress, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($scoreDatePairs as $pair) {
            $date = Carbon::parse($pair['date']);
            $monthKey = $date->format('Y-m');
            $dayKey = $pair['date'];
            $score = $pair['score'];
            $pct = round($score * 25, 1);

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [];
                $monthlyProgress[$monthKey] = [];
            }
            $monthlyData[$monthKey][] = $score;
            $monthlyProgress[$monthKey][] = $pct;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
                $dailyProgressPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $score;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $pct;
        }

        $monthlyAverages = [];
        $monthlyProgressAverages = [];
        foreach ($monthlyData as $month => $dailyVals) {
            $monthlyAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
        }
        foreach ($monthlyProgress as $month => $dailyVals) {
            $monthlyProgressAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
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
                'below' => $totalResponden - $respondenPuas,
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

        if ($personId !== null) {
            $daftarTugas = KontrolTugas::whereYear('created_at', $tahun)
                ->where('id_karyawan', $personId);
        } else {
            $daftarTugas = KontrolTugas::whereYear('created_at', $tahun);
        }

        $jumlahTugas = $daftarTugas->count();

        if ($jumlahTugas === 0) {
            return 0;
        }

        $jumlahTugasSelesai = $daftarTugas->where('status', '1')->count();

        $presentase = ($jumlahTugasSelesai / $jumlahTugas) * 100;

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

        $query = KontrolTugas::whereYear('created_at', $tahun);

        if ($personId !== null) {
            $query->where('id_karyawan', $personId);
        }

        $tugas = $query->get();

        if ($tugas->isEmpty()) {
            return $this->getDefaultDetailResponse();
        }

        $jumlahTugas = $tugas->count();
        // Diseragamkan menggunakan filter collection agar aman dari tipe data string/int
        $jumlahTugasSelesai = $tugas->filter(fn($t) => $t->status == 1)->count();

        $progress = round(($jumlahTugasSelesai / $jumlahTugas) * 100, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');
        if ($gap === '') $gap = '0';

        // Array penampung akumulasi data mentah
        $monthlyDataRaw = [];
        $dailyDataRaw = [];

        foreach ($tugas as $t) {
            $date = Carbon::parse($t->created_at);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $isSelesai = $t->status == 1 ? 1 : 0;

            // Kumpulkan akumulasi bulanan
            if (!isset($monthlyDataRaw[$monthKey])) {
                $monthlyDataRaw[$monthKey] = ['total' => 0, 'selesai' => 0];
            }
            $monthlyDataRaw[$monthKey]['total']++;
            $monthlyDataRaw[$monthKey]['selesai'] += $isSelesai;

            // Kumpulkan akumulasi harian (Mencegah Bug Timpa Data)
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

        // Hitung persentase bulanan
        foreach ($monthlyDataRaw as $month => $data) {
            $persentase = round(($data['selesai'] / $data['total']) * 100, 1);
            $monthlyAverages[$month] = $persentase; 
            $monthlyProgressAverages[$month] = $persentase; 
        }

        // Hitung persentase harian
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
                'below' => $jumlahTugas - $jumlahTugasSelesai,
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }
}