<?php

namespace App\Services\KPI\Jabatan;

use App\Models\Nilaifeedback;
use App\Models\KomplainPeserta;
use App\Models\RKM;
use App\Models\ChecklistKeperluan;
use App\Traits\KPIDefaultResponseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CustomerCareKPIService
{
    use KPIDefaultResponseTrait;

    public function calculatePesertaPuasDenganPelayananDanFasilitasTraining($item, $personId)
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

        $nilaiTarget = (float) $detail->nilai_target;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
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
            $p1 = is_numeric($fb->P1) ? (float) $fb->P1 : 0;
            $p2 = is_numeric($fb->P2) ? (float) $fb->P2 : 0;
            $p3 = is_numeric($fb->P3) ? (float) $fb->P3 : 0;
            $p4 = is_numeric($fb->P4) ? (float) $fb->P4 : 0;
            $p5 = is_numeric($fb->P5) ? (float) $fb->P5 : 0;
            $p6 = is_numeric($fb->P6) ? (float) $fb->P6 : 0;
            $p7 = is_numeric($fb->P7) ? (float) $fb->P7 : 0;

            $avg = ($f1 + $f2 + $f3 + $f4 + $f5 + $p1 + $p2 + $p3 + $p4 + $p5 + $p6 + $p7) / 12;
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

    public function calculatePesertaPuasDenganPelayananDanFasilitasTrainingDetail($itemDetail, $personId = null)
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

        $feedbacks = Nilaifeedback::whereBetween('created_at', [$start, $end])->get();

        if ($feedbacks->isEmpty()) {
            return $this->getDefaultDetailResponse();
        }

        $allScores = [];
        $scoreDatePairs = [];

        foreach ($feedbacks as $fb) {

            $values = [
                $fb->F1,
                $fb->F2,
                $fb->F3,
                $fb->F4,
                $fb->F5,
                $fb->P1,
                $fb->P2,
                $fb->P3,
                $fb->P4,
                $fb->P5,
                $fb->P6,
                $fb->P7
            ];

            $cleanValues = [];

            foreach ($values as $v) {
                $cleanValues[] = is_numeric($v) ? (float) $v : 0;
            }

            $avg = array_sum($cleanValues) / 12;
            $avg = min(4, max(1, $avg));

            $allScores[] = $avg;

            $scoreDatePairs[] = [
                'score' => $avg,
                'date' => $fb->created_at->format('Y-m-d')
            ];
        }

        $totalResponden = count($allScores);
        $respondenPuas = 0;

        foreach ($allScores as $score) {
            if ($score >= 3.5) {
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

        $monthlyTarget = $nilaiTarget / 12;
        $dailyTarget = $nilaiTarget / 365;

        foreach ($scoreDatePairs as $pair) {

            $date = Carbon::parse($pair['date']);
            $monthKey = $date->format('Y-m');
            $dayKey = $pair['date'];
            $score = $pair['score'];
            $pct = $score >= 3.5 ? 100 : 0;

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

        foreach ($monthlyData as $month => $vals) {
            $monthlyAverages[$month] = round(array_sum($vals) / count($vals), 1);
        }

        foreach ($monthlyProgress as $month => $vals) {
            $monthlyProgressAverages[$month] = round(array_sum($vals) / count($vals), 1);
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

    public function calculateDorongInovasiPelayanan($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $progress = 0;

        if (!is_null($detail->manual_value)) {
            $manualValue = (float) $detail->manual_value;

            if ($manualValue > 0) {
                $progress = $manualValue;
            }
        }

        return round($progress);
    }

    public function calculateDorongInovasiPelayananDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return array_merge($this->getDefaultDetailResponse(), [
                'dataManual' => ['manual_document' => $detail->manual_document ?? null],
            ]);
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return array_merge($this->getDefaultDetailResponse(), [
                'dataManual' => ['manual_document' => $detail->manual_document ?? null],
            ]);
        }

        $progress = 0;

        if (!is_null($detail->manual_value)) {
            $manualValue = (float) $detail->manual_value;

            if ($manualValue > 0) {
                $progress = $manualValue;
            }
        }

        $progress = round($progress);
        $gapRaw = $progress - $nilaiTarget;
        if ($progress > $nilaiTarget) {
            $gap = 0;
        } else {
            $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');
        }

        return array_merge($this->getDefaultDetailResponse(), [
            'progress' => $progress,
            'gap' => $gap,
            'dataManual' => [
                'manual_document' => $detail->manual_document,
            ]
        ]);
    }

    public function calculatePenangananKomplainPerseta($item, $personId)
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

        $nilaiTarget = (float) $detail->nilai_target;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return 0;
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $komplainData = KomplainPeserta::whereBetween('created_at', [$start, $end])->get();

        $totalData = $komplainData->count();

        if ($totalData === 0) {
            return 0;
        }

        $dataTepatWaktu = 0;

        foreach ($komplainData as $data) {
            if ($data->tanggal_selesai) {
                $createdDate = Carbon::parse($data->created_at);
                $finishedDate = Carbon::parse($data->tanggal_selesai);

                if ($createdDate->format('Y-m-d') === $finishedDate->format('Y-m-d')) {
                    $dataTepatWaktu++;
                }
            }
        }

        $presentase = ($dataTepatWaktu / $totalData) * 100;

        return round($presentase, 1);
    }

    public function calculatePenangananKomplainPersetaDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || is_null($detail->nilai_target) || is_null($detail->detail_jangka)) {
            return $this->getDefaultDetailResponse();
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $this->getDefaultDetailResponse();
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $komplainData = KomplainPeserta::whereBetween('created_at', [$start, $end])->get();

        $totalData = $komplainData->count();

        if ($totalData === 0) {
            return $this->getDefaultDetailResponse();
        }

        $dataTepatWaktu = 0;
        $dataTidakTepatWaktu = 0;
        $dailyValues = [];

        foreach ($komplainData as $data) {
            $createdDate = Carbon::parse($data->created_at);
            $dateKey = $createdDate->format('Y-m-d');

            $isTepatWaktu = 0;

            if ($data->tanggal_selesai) {
                $finishedDate = Carbon::parse($data->tanggal_selesai);

                if ($createdDate->format('Y-m-d') === $finishedDate->format('Y-m-d')) {
                    $dataTepatWaktu++;
                    $isTepatWaktu = 1;
                } else {
                    $dataTidakTepatWaktu++;
                }
            } else {
                $dataTidakTepatWaktu++;
            }

            if (!isset($dailyValues[$dateKey])) {
                $dailyValues[$dateKey] = [];
            }
            $dailyValues[$dateKey][] = $isTepatWaktu * 100;
        }

        $presentase = ($dataTepatWaktu / $totalData) * 100;
        $progress = round($presentase, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $dataTepatWaktu;
        $below = $dataTidakTepatWaktu;

        $dailyAverages = [];
        foreach ($dailyValues as $dateStr => $values) {
            $dailyAverages[$dateStr] = round(array_sum($values) / count($values), 1);
        }

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
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
            $monthlyProgress[$monthKey][] = $avg >= 100 ? 100 : 0;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
                $dailyProgressPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $avg;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $avg >= 100 ? 100 : 0;
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
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    public function calculateReportPersiapanKelas($item, $personId)
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

        $nilaiTarget = (float) $detail->nilai_target;

        $totalRkm = RKM::with('materi', 'instruktur', 'instruktur2', 'asisten', 'nilaifeedback')
                ->where('status', '0')
                ->whereYear('tanggal_awal', $tahun)->count();

        $totalTuntas = ChecklistKeperluan::whereHas('rkm', function ($query) use ($tahun) {
            $query->whereYear('tanggal_awal', $tahun);
        })
            ->whereNotNull('tanggal_keperluan')
            ->where('materi', '1')
            ->where('kelas', '1')
            ->where('cb', '1')
            ->where('maksi', '1')
            ->where('keperluan_kelas', '1')
            ->whereHas('subChecklistKeperluans', function ($subQuery) {
                $subQuery->where('materi_module', '1')
                    ->where('materi_elearning', '1')
                    ->where('cb_instruktur', '1')
                    ->where('cb_peserta', '1')
                    ->where('maksi_instruktur', '1')
                    ->where('maksi_peserta', '1')
                    ->where('kelas_ac', '1')
                    ->where('kelas_jam', '1')
                    ->where('kelas_buku', '1')
                    ->where('kelas_pulpen', '1')
                    ->where('kelas_permen', '1')
                    ->where('kelas_camilan', '1')
                    ->where('kelas_minuman', '1')
                    ->where('kelas_lampu', '1')
                    ->where('kelas_kondisi_kebersihan', '1');
            })
            ->count();

        if ($totalRkm > 0) {
            $progress = ($totalTuntas / $totalRkm) * 100;
        } else {
            $progress = 0;
        }

        return round($progress, 1);
    }

    public function calculateReportPersiapanKelasDetail($itemDetail, $personId = null)
    {
        $details = $itemDetail->detailTargetKPI;
        $detail = $details->first();

        $nilaiTarget = (float) optional($detail)->nilai_target;
        $tahun = (int) optional($detail)->detail_jangka ?? now()->year;

        if ($details->isEmpty() || $nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $this->getDefaultDetailResponse();
        }

        $totalRkm = RKM::with('materi', 'instruktur', 'instruktur2', 'asisten', 'nilaifeedback')
                ->where('status', '0')
                ->whereYear('tanggal_awal', $tahun)->count();

        $checklistItems = ChecklistKeperluan::whereHas('rkm', function ($query) use ($tahun) {
            $query->whereYear('tanggal_awal', $tahun);
        })
            ->whereNotNull('tanggal_keperluan')
            ->where('materi', 1)
            ->where('kelas', 1)
            ->where('cb', 1)
            ->where('maksi', 1)
            ->where('keperluan_kelas', 1)
            ->whereHas('subChecklistKeperluans', function ($subQuery) {
                $subQuery->where('materi_module', 1)
                    ->where('materi_elearning', 1)
                    ->where('cb_instruktur', 1)
                    ->where('cb_peserta', 1)
                    ->where('maksi_instruktur', 1)
                    ->where('maksi_peserta', 1)
                    ->where('kelas_ac', 1)
                    ->where('kelas_jam', 1)
                    ->where('kelas_buku', 1)
                    ->where('kelas_pulpen', 1)
                    ->where('kelas_permen', 1)
                    ->where('kelas_camilan', 1)
                    ->where('kelas_minuman', 1)
                    ->where('kelas_lampu', 1)
                    ->where('kelas_kondisi_kebersihan', 1);
            })
            ->select('tanggal_keperluan', 'created_at')
            ->get();

        $totalTuntas = $checklistItems->count();

        if ($totalRkm > 0) {
            $progress = ($totalTuntas / $totalRkm) * 100;
        } else {
            $progress = 0;
        }

        $dailyBreakdownPerMonth = [];
        $monthlyTotals = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        $monthlyTarget = $nilaiTarget / 12;
        $dailyTarget = $nilaiTarget / 365;

        foreach ($checklistItems as $row) {
            $date = Carbon::parse($row->tanggal_keperluan ?? $row->created_at ?? now());
            $dateKey = $date->format('Y-m-d');
            $monthKey = $date->format('Y-m');

            $value = 1;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
                $dailyProgressPerMonth[$monthKey] = [];
            }

            if (!isset($dailyBreakdownPerMonth[$monthKey][$dateKey])) {
                $dailyBreakdownPerMonth[$monthKey][$dateKey] = 0;
                $dailyProgressPerMonth[$monthKey][$dateKey] = 0;
            }

            $dailyBreakdownPerMonth[$monthKey][$dateKey] += $value;
            $dailyProgressPerMonth[$monthKey][$dateKey] = $dailyTarget > 0
                ? round(($dailyBreakdownPerMonth[$monthKey][$dateKey] / $dailyTarget) * 100, 1)
                : 100;

            if (!isset($monthlyTotals[$monthKey])) {
                $monthlyTotals[$monthKey] = 0;
                $monthlyProgress[$monthKey] = 0;
            }
            $monthlyTotals[$monthKey] += $value;
            $monthlyProgress[$monthKey] = $monthlyTarget > 0
                ? round(($monthlyTotals[$monthKey] / $monthlyTarget) * 100, 1)
                : 100;
        }

        ksort($monthlyTotals);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgress);
        ksort($dailyProgressPerMonth);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        return [
            'progress' => round($progress, 1),
            'gap' => $gap,
            'pie_chart' => [
                'above' => $totalTuntas,
                'below' => max(0, $totalRkm - $totalTuntas),
            ],
            'monthly_data' => $monthlyTotals,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }
}