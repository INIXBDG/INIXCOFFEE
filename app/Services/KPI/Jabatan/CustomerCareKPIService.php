<?php

namespace App\Services\KPI\Jabatan;

use App\Models\Nilaifeedback;
use App\Models\KomplainPeserta;
use App\Models\RKM;
use App\Models\ChecklistKeperluan;
use App\Traits\KPIDefaultResponseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerCareKPIService
{
    use KPIDefaultResponseTrait;

    private function calculateAndFormatGap(float $progress, float $target): string
    {
        $gapRaw = $progress - $target;
        
        if (abs($gapRaw) < 0.05) {
            return '0';
        }

        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');
        return $gap === '' ? '0' : $gap;
    }

    private function getDefaultDetailResponse(): array
    {
        return [
            'progress' => 0.0,
            'gap' => '0',
            'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
        ];
    }

    public function calculatePesertaPuasDenganPelayananDanFasilitasTraining($item, $personId = null)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return 0.0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) return 0.0;

        $stats = Nilaifeedback::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN (
                COALESCE(F1,0) + COALESCE(F2,0) + COALESCE(F3,0) + COALESCE(F4,0) + COALESCE(F5,0) +
                COALESCE(P1,0) + COALESCE(P2,0) + COALESCE(P3,0) + COALESCE(P4,0) + COALESCE(P5,0) + COALESCE(P6,0) + COALESCE(P7,0)
            ) / 12.0 >= 3.5 THEN 1 ELSE 0 END) as puas
        ")
            ->whereYear('created_at', $tahun)
            ->first();

        $total = $stats->total ?? 0;
        if ($total === 0) return 0.0;

        return round(($stats->puas / $total) * 100, 1);
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

        $monthlyData = [];
        $monthlyProgress = [];
        $dailyBreakdownPerMonth = [];
        $dailyProgressPerMonth = [];

        $feedbacks = Nilaifeedback::select('id', 'created_at', 'F1', 'F2', 'F3', 'F4', 'F5', 'P1', 'P2', 'P3', 'P4', 'P5', 'P6', 'P7')
            ->whereBetween('created_at', [$start, $end])
            ->cursor();

        $totalResponden = 0;
        $respondenPuas = 0;

        foreach ($feedbacks as $fb) {
            $values = [
                $fb->F1, $fb->F2, $fb->F3, $fb->F4, $fb->F5,
                $fb->P1, $fb->P2, $fb->P3, $fb->P4, $fb->P5, $fb->P6, $fb->P7
            ];

            $cleanValues = array_map(fn($v) => is_numeric($v) ? (float) $v : 0.0, $values);
            $avg = min(4.0, max(1.0, array_sum($cleanValues) / 12.0));

            $totalResponden++;
            $isPuas = $avg >= 3.5;
            if ($isPuas) {
                $respondenPuas++;
            }

            $date = Carbon::parse($fb->created_at);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');
            
            $pct = $isPuas ? 100.0 : 0.0;

            $monthlyData[$monthKey][] = $pct;
            $monthlyProgress[$monthKey][] = $pct;
            
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $avg; 
            $dailyProgressPerMonth[$monthKey][$dayKey] = $pct;
        }

        if ($totalResponden === 0) {
            return $this->getDefaultDetailResponse();
        }

        $progress = round(($respondenPuas / $totalResponden) * 100, 1);
        $gap = $this->calculateAndFormatGap($progress, $nilaiTarget);

        $monthlyAverages = [];
        $monthlyProgressAverages = [];
        foreach ($monthlyData as $month => $vals) {
            $monthlyAverages[$month] = round(array_sum($vals) / count($vals), 1);
            $monthlyProgressAverages[$month] = round(array_sum($monthlyProgress[$month]) / count($monthlyProgress[$month]), 1);
        }

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            ksort($dailyBreakdownPerMonth[$month]);
            ksort($dailyProgressPerMonth[$month]);
        }

        ksort($monthlyAverages);
        ksort($monthlyProgressAverages);
        ksort($dailyBreakdownPerMonth);
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

    public function calculateDorongInovasiPelayanan($item, $personId = null)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !is_numeric($detail->nilai_target)) {
            return 0.0;
        }

        $progress = !is_null($detail->manual_value) && (float) $detail->manual_value > 0 
            ? (float) $detail->manual_value 
            : 0.0;

        return round($progress, 1);
    }

    public function calculateDorongInovasiPelayananDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();
        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return array_merge($this->getDefaultDetailResponse(), [
                'dataManual' => ['manual_document' => null],
            ]);
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return array_merge($this->getDefaultDetailResponse(), [
                'dataManual' => ['manual_document' => $detail->manual_document ?? null],
            ]);
        }

        $progress = !is_null($detail->manual_value) && (float) $detail->manual_value > 0 
            ? (float) $detail->manual_value 
            : 0.0;

        $progress = round($progress, 1);
        $gap = $this->calculateAndFormatGap($progress, $nilaiTarget);

        return array_merge($this->getDefaultDetailResponse(), [
            'progress' => $progress,
            'gap' => $gap,
            'dataManual' => [
                'manual_document' => $detail->manual_document ?? null,
            ]
        ]);
    }

    public function calculatePenangananKomplainPerseta($item, $personId = null)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return 0.0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) return 0.0;

        $total = KomplainPeserta::whereYear('created_at', $tahun)
            ->count();

        if ($total === 0) return 0.0;

        $tepatWaktu = KomplainPeserta::whereYear('created_at', $tahun)
            ->whereNotNull('tanggal_selesai')
            ->whereRaw('TIMESTAMPDIFF(HOUR, created_at, tanggal_selesai) <= 24')
            ->count();

        return round(($tepatWaktu / $total) * 100, 1);
    }

    public function calculatePenangananKomplainPersetaDetail($itemDetail, $personId = null)
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

        $dataTepatWaktu = 0;
        $dataTidakTepatWaktu = 0;

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        $komplainData = KomplainPeserta::select('id', 'created_at', 'tanggal_selesai')
            ->whereBetween('created_at', [$start, $end])
            ->cursor();

        foreach ($komplainData as $data) {
            $createdDate = Carbon::parse($data->created_at);
            $dateKey = $createdDate->format('Y-m-d');
            $monthKey = $createdDate->format('Y-m');

            $isTepatWaktu = 0;
            if ($data->tanggal_selesai) {
                $finishedDate = Carbon::parse($data->tanggal_selesai);
                if ($createdDate->diffInHours($finishedDate) <= 24) {
                    $dataTepatWaktu++;
                    $isTepatWaktu = 1;
                } else {
                    $dataTidakTepatWaktu++;
                }
            } else {
                $dataTidakTepatWaktu++;
            }

            $pct = $isTepatWaktu * 100.0;

            $monthlyData[$monthKey][] = $pct;
            $monthlyProgress[$monthKey][] = $pct;
            $dailyBreakdownPerMonth[$monthKey][$dateKey] = $pct;
            $dailyProgressPerMonth[$monthKey][$dateKey] = $pct;
        }

        $totalData = $dataTepatWaktu + $dataTidakTepatWaktu;

        if ($totalData === 0) {
            return $this->getDefaultDetailResponse();
        }

        $progress = round(($dataTepatWaktu / $totalData) * 100, 1);
        $gap = $this->calculateAndFormatGap($progress, $nilaiTarget);

        $monthlyAverages = [];
        $monthlyProgressAverages = [];
        foreach ($monthlyData as $month => $vals) {
            $monthlyAverages[$month] = round(array_sum($vals) / count($vals), 1);
            $monthlyProgressAverages[$month] = round(array_sum($monthlyProgress[$month]) / count($monthlyProgress[$month]), 1);
        }

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            ksort($dailyBreakdownPerMonth[$month]);
            ksort($dailyProgressPerMonth[$month]);
        }

        ksort($monthlyAverages);
        ksort($monthlyProgressAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $dataTepatWaktu,
                'below' => max(0, $dataTidakTepatWaktu),
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    public function calculateReportPersiapanKelas($item, $personId = null)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return 0.0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) return 0.0;

        $totalRkm = RKM::where('status', '0')
            ->whereYear('tanggal_awal', $tahun)
            ->count();

        if ($totalRkm === 0) return 0.0;

        $totalTuntas = ChecklistKeperluan::whereYear('created_at', $tahun)
            ->whereNotNull('tanggal_keperluan')
            ->where('materi', 1)->where('kelas', 1)->where('cb', 1)->where('maksi', 1)->where('keperluan_kelas', 1)
            ->whereHas('subChecklistKeperluans', function ($subQ) {
                $subQ->where('materi_module', 1)->where('materi_elearning', 1)->where('cb_instruktur', 1)
                     ->where('cb_peserta', 1)->where('maksi_instruktur', 1)->where('maksi_peserta', 1)
                     ->where('kelas_ac', 1)->where('kelas_jam', 1)->where('kelas_buku', 1)->where('kelas_pulpen', 1)
                     ->where('kelas_permen', 1)->where('kelas_camilan', 1)->where('kelas_minuman', 1)
                     ->where('kelas_lampu', 1)->where('kelas_kondisi_kebersihan', 1);
            })
            ->whereHas('rkm', function ($q) use ($tahun, $personId) {
                $q->where('status', '0')->whereYear('tanggal_awal', $tahun);
            })
            ->count();

        return round(($totalTuntas / $totalRkm) * 100, 1);
    }

    public function calculateReportPersiapanKelasDetail($itemDetail, $personId = null)
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

        $totalRkmPerMonth = RKM::selectRaw("DATE_FORMAT(tanggal_awal, '%Y-%m') as month, COUNT(*) as total")
            ->where('status', '0')
            ->whereYear('tanggal_awal', $tahun)
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // FIX 4: Menggunakan cursor() alih-alih ->get() untuk menghindari OOM pada data ribuan baris
        $checklistQuery = ChecklistKeperluan::select('tanggal_keperluan', 'created_at')
            ->whereYear('created_at', $tahun)
            ->whereNotNull('tanggal_keperluan')
            ->where('materi', 1)->where('kelas', 1)->where('cb', 1)->where('maksi', 1)->where('keperluan_kelas', 1)
            ->whereHas('subChecklistKeperluans', function ($subQuery) {
                $subQuery->where('materi_module', 1)->where('materi_elearning', 1)->where('cb_instruktur', 1)
                    ->where('cb_peserta', 1)->where('maksi_instruktur', 1)->where('maksi_peserta', 1)
                    ->where('kelas_ac', 1)->where('kelas_jam', 1)->where('kelas_buku', 1)->where('kelas_pulpen', 1)
                    ->where('kelas_permen', 1)->where('kelas_camilan', 1)->where('kelas_minuman', 1)
                    ->where('kelas_lampu', 1)->where('kelas_kondisi_kebersihan', 1);
            })
            ->cursor();

        $monthlyTuntas = [];
        $dailyBreakdownPerMonth = [];
        $dailyProgressPerMonth = [];

        foreach ($checklistQuery as $row) {
            $date = Carbon::parse($row->tanggal_keperluan ?? $row->created_at ?? now());
            $dateKey = $date->format('Y-m-d');
            $monthKey = $date->format('Y-m');

            $monthlyTuntas[$monthKey] = ($monthlyTuntas[$monthKey] ?? 0) + 1;
            $dailyBreakdownPerMonth[$monthKey][$dateKey] = ($dailyBreakdownPerMonth[$monthKey][$dateKey] ?? 0) + 1;
        }

        $totalTuntas = array_sum($monthlyTuntas);
        $totalRkm = array_sum($totalRkmPerMonth);
        
        $progress = $totalRkm > 0 ? round(($totalTuntas / $totalRkm) * 100, 1) : 0.0;
        $gap = $this->calculateAndFormatGap($progress, $nilaiTarget);

        $monthlyData = [];
        $monthlyProgress = [];

        foreach ($totalRkmPerMonth as $month => $rkmCount) {
            $tuntasCount = $monthlyTuntas[$month] ?? 0;
            $monthProgress = $rkmCount > 0 ? round(($tuntasCount / $rkmCount) * 100, 1) : 0.0;
            
            $monthlyData[$month] = $tuntasCount;
            $monthlyProgress[$month] = $monthProgress;
        }

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            $rkmCount = $totalRkmPerMonth[$month] ?? 1;
            $daysInMonth = Carbon::parse($month . '-01')->daysInMonth;
            $dailyTargetRkm = max(1, $rkmCount / $daysInMonth); 

            foreach ($days as $day => $tuntas) {
                $dailyProgressPerMonth[$month][$day] = round(($tuntas / $dailyTargetRkm) * 100, 1);
            }
            ksort($dailyBreakdownPerMonth[$month]);
            ksort($dailyProgressPerMonth[$month]);
        }

        ksort($monthlyData);
        ksort($monthlyProgress);
        ksort($dailyBreakdownPerMonth);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $totalTuntas,
                'below' => max(0, $totalRkm - $totalTuntas),
            ],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }
}