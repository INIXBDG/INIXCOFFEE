<?php

namespace App\Services\KPI\Jabatan;

use App\Models\Nilaifeedback;
use App\Models\ApprovalPendapatan;
use App\Models\AnalysisReport;
use App\Models\LeadProject;
use App\Models\karyawan;
use App\Models\detailPersonKPI;
use App\Models\targetKPI;
use App\Traits\KPIDefaultResponseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GMKPIService
{
    use KPIDefaultResponseTrait;

    /**
     * Helper untuk standarisasi perhitungan gap dan pembulatan
     */
    private function calculateAndFormatGap(float $progress, float $target): string
    {
        $gapRaw = $progress - $target;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');
        return $gap === '' || $gap === '-0' ? '0' : $gap;
    }

    /**
     * Helper untuk struktur response kosong yang konsisten
     */
    private function getDefaultDetailResponse(array $extra = []): array
    {
        return array_merge(
            [
                'progress' => 0.0,
                'gap' => '0',
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ],
            $extra,
        );
    }

    // ==========================================
    // 1. KEPUASAN PELANGGAN
    // ==========================================

    public function calculateProgressKepuasanPelanggan($item, $personId)
    {
        $detailResult = $this->calculateProgressKepuasanPelangganDetail($item, $personId);
        return $detailResult['progress'];
    }

    public function calculateProgressKepuasanPelangganDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();
        $empty = $this->getDefaultDetailResponse([
            'category_scores' => [],
            'top_performer' => null,
            'lowest_performer' => null,
            'trend' => 'stable',
            'trend_value' => 0,
            'consistency' => 'stable',
            'target_status' => 'behind',
            'prediction' => 0,
            'total_feedback' => 0,
            'total_sessions' => 0,
            'insight' => '',
        ]);

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $empty;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $empty;
        }

        $start = "$tahun-01-01";
        $end = "$tahun-12-31";

        $feedbacks = Nilaifeedback::select('id', 'created_at', 'M1', 'M2', 'M3', 'M4', 'P1', 'P2', 'P3', 'P4', 'P5', 'P6', 'P7', 'F1', 'F2', 'F3', 'F4', 'F5', 'I1', 'I2', 'I3', 'I4', 'I5', 'I6', 'I7', 'I8', 'I1b', 'I2b', 'I3b', 'I4b', 'I5b', 'I6b', 'I7b', 'I8b', 'I1as', 'I2as', 'I3as', 'I4as', 'I5as', 'I6as', 'I7as', 'I8as', 'id_rkm')
            ->with(['rkm:id,materi_id,tanggal_awal', 'rkm.materi:id,nama_materi'])
            ->whereBetween('created_at', [$start, $end])
            ->cursor();

        $groupedFeedbacks = [];
        $totalFeedbackCount = 0;

        foreach ($feedbacks as $fb) {
            $totalFeedbackCount++;
            $materiNama = optional($fb->rkm)->materi?->nama_materi ?? 'unknown';
            $tanggalAwal = optional($fb->rkm)->tanggal_awal ?? '0000-00-00';
            $key = $materiNama . '/' . $tanggalAwal;

            if (!isset($groupedFeedbacks[$key])) {
                $groupedFeedbacks[$key] = ['count' => 0, 'sums' => array_fill_keys(['M', 'P', 'F', 'I', 'Ib', 'Ias'], 0), 'date' => $fb->created_at->format('Y-m-d')];
            }

            $groupedFeedbacks[$key]['count']++;
            $groupedFeedbacks[$key]['sums']['M'] += $fb->M1 + $fb->M2 + $fb->M3 + $fb->M4;
            $groupedFeedbacks[$key]['sums']['P'] += $fb->P1 + $fb->P2 + $fb->P3 + $fb->P4 + $fb->P5 + $fb->P6 + $fb->P7;
            $groupedFeedbacks[$key]['sums']['F'] += $fb->F1 + $fb->F2 + $fb->F3 + $fb->F4 + $fb->F5;
            $groupedFeedbacks[$key]['sums']['I'] += $fb->I1 + $fb->I2 + $fb->I3 + $fb->I4 + $fb->I5 + $fb->I6 + $fb->I7 + $fb->I8;
            $groupedFeedbacks[$key]['sums']['Ib'] += $fb->I1b + $fb->I2b + $fb->I3b + $fb->I4b + $fb->I5b + $fb->I6b + $fb->I7b + $fb->I8b;
            $groupedFeedbacks[$key]['sums']['Ias'] += $fb->I1as + $fb->I2as + $fb->I3as + $fb->I4as + $fb->I5as + $fb->I6as + $fb->I7as + $fb->I8as;
        }

        if (empty($groupedFeedbacks)) {
            return $empty;
        }

        $averageFeedbacks = [];
        $sessionScores = [];
        $dailyAverages = [];
        $dailyProgresses = [];
        $categoryTotals = ['M' => 0, 'P' => 0, 'F' => 0, 'I' => 0, 'Ib' => 0, 'Ias' => 0];
        $categoryCounts = ['M' => 0, 'P' => 0, 'F' => 0, 'I' => 0, 'Ib' => 0, 'Ias' => 0];

        foreach ($groupedFeedbacks as $key => $group) {
            $count = $group['count'];
            if ($count === 0) {
                continue;
            }

            $avgM = round($group['sums']['M'] / ($count * 4), 1);
            $avgP = round($group['sums']['P'] / ($count * 7), 1);
            $avgF = round($group['sums']['F'] / ($count * 5), 1);
            $avgI = round($group['sums']['I'] / ($count * 8), 1);
            $avgIb = round($group['sums']['Ib'] / ($count * 8), 1);
            $avgIas = round($group['sums']['Ias'] / ($count * 8), 1);

            $values = [$avgM, $avgP, $avgF, $avgI];
            if ($avgIb > 0) {
                $values[] = $avgIb;
            }
            if ($avgIas > 0) {
                $values[] = $avgIas;
            }

            $finalAvg = round(array_sum($values) / count($values), 1);
            $averageFeedbacks[] = $finalAvg;
            $sessionScores[$key] = $finalAvg;

            $dailyAverages[$group['date']] = $finalAvg;
            $dailyProgresses[$group['date']] = round($finalAvg * 20, 1);

            foreach (['M' => $avgM, 'P' => $avgP, 'F' => $avgF, 'I' => $avgI, 'Ib' => $avgIb, 'Ias' => $avgIas] as $k => $v) {
                if ($v > 0) {
                    $categoryTotals[$k] += $v;
                    $categoryCounts[$k]++;
                }
            }
        }

        $totalGroups = count($averageFeedbacks);
        $above = count(array_filter($averageFeedbacks, fn($v) => $v >= 3.5));
        $below = $totalGroups - $above;
        $progress = $totalGroups > 0 ? round(($above / $totalGroups) * 100, 1) : 0.0;
        $gap = $this->calculateAndFormatGap($progress, $nilaiTarget);

        $monthlyData = [];
        $monthlyProgress = [];
        $dailyBreakdownPerMonth = [];
        $dailyProgressPerMonth = [];

        foreach ($dailyAverages as $dateStr => $avg) {
            $monthKey = Carbon::parse($dateStr)->format('Y-m');
            $dayKey = Carbon::parse($dateStr)->format('Y-m-d');
            $pct = $dailyProgresses[$dateStr] ?? 0;

            $monthlyData[$monthKey][] = $avg;
            $monthlyProgress[$monthKey][] = $pct;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $avg;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $pct;
        }

        $monthlyAverages = [];
        $monthlyProgressAverages = [];
        foreach ($monthlyData as $month => $vals) {
            $monthlyAverages[$month] = round(array_sum($vals) / count($vals), 1);
            $monthlyProgressAverages[$month] = round(array_sum($monthlyProgress[$month]) / count($monthlyProgress[$month]), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAverages);
        ksort($dailyProgressPerMonth);

        $mapping = ['M' => 'Materi', 'P' => 'Pelayanan', 'F' => 'Fasilitas', 'I' => 'Instruktur', 'Ib' => 'Instruktur 2', 'Ias' => 'Asisten Instruktur'];
        $categoryScores = [];
        foreach ($categoryTotals as $k => $total) {
            $categoryScores[$mapping[$k] ?? $k] = $categoryCounts[$k] > 0 ? round($total / $categoryCounts[$k], 1) : 0;
        }

        arsort($sessionScores);
        $top = key($sessionScores);
        $topVal = current($sessionScores);
        asort($sessionScores);
        $low = key($sessionScores);
        $lowVal = current($sessionScores);

        $months = array_values($monthlyAverages);
        $trend = 'stable';
        $trendValue = 0;
        if (count($months) >= 2) {
            $trendValue = round(end($months) - prev($months), 1);
            if ($trendValue > 0) {
                $trend = 'up';
            } elseif ($trendValue < 0) {
                $trend = 'down';
            }
        }

        $mean = count($averageFeedbacks) > 0 ? array_sum($averageFeedbacks) / count($averageFeedbacks) : 0;
        $variance = count($averageFeedbacks) > 0 ? array_sum(array_map(fn($v) => pow($v - $mean, 2), $averageFeedbacks)) / count($averageFeedbacks) : 0;
        $consistency = sqrt($variance) < 0.3 ? 'stable' : 'fluctuating';
        $targetStatus = $progress >= $nilaiTarget ? 'on_track' : ($progress >= $nilaiTarget - 5 ? 'at_risk' : 'behind');
        $prediction = count($months) > 0 ? round(array_sum(array_slice($months, -3)) / min(3, count($months)), 1) : 0;

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
            'category_scores' => $categoryScores,
            'top_performer' => ['label' => $top, 'value' => $topVal],
            'lowest_performer' => ['label' => $low, 'value' => $lowVal],
            'trend' => $trend,
            'trend_value' => $trendValue,
            'consistency' => $consistency,
            'target_status' => $targetStatus,
            'prediction' => $prediction,
            'total_feedback' => $totalFeedbackCount,
            'total_sessions' => $totalGroups,
            'insight' => "Kepuasan pelanggan {$trend} dengan perubahan {$trendValue}. Konsistensi {$consistency}.",
        ];
    }

    public function calculatePemasukanKotor($item, $personId)
    {
        $detailResult = $this->calculatePemasukanKotorDetail($item, $personId);
        return $detailResult['progress'];
    }

    public function calculatePemasukanKotorDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();
        $defaultResponse = $this->getDefaultDetailResponse(['triwulan_data' => [], 'sales_performance' => null, 'dataManual' => ['manual_document' => $detail->manual_document ?? null]]);

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $defaultResponse;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $defaultResponse;
        }

        $salesAggregated = ApprovalPendapatan::selectRaw(
            '
                DATE_FORMAT(tanggal_mulai, "%Y-%m") as month_key,
                DATE_FORMAT(tanggal_mulai, "%Y-%m-%d") as day_key,
                SUM(CAST(harga_net AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total
            ',
        )
            ->whereYear('tanggal_mulai', $tahun)
            ->groupBy('month_key', 'day_key')
            ->get();

        $totalSales = 0;
        $triwulanDataTemp = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
        $monthlyDataTemp = [];
        $dailyBreakdownPerMonth = [];

        foreach ($salesAggregated as $row) {
            $total = (int) round($row->total ?? 0);
            $totalSales += $total;

            $monthlyDataTemp[$row->month_key] = ($monthlyDataTemp[$row->month_key] ?? 0) + $total;
            $dailyBreakdownPerMonth[$row->month_key][$row->day_key] = $total;

            $triwulan = (int) ceil(Carbon::parse($row->month_key)->month / 3);
            $triwulanDataTemp[$triwulan] += $total;
        }

        $progressGlobal = $totalSales;
        $progressPercent = $totalSales;
        $gap = $this->calculateAndFormatGap($progressPercent, 100.0);

        $revenueBySalesKey = ApprovalPendapatan::query()->join('r_k_m_s as rkm', 'approval_pendapatans.id_rkm', '=', 'rkm.id')->join('karyawans as k', 'rkm.sales_key', '=', 'k.kode_karyawan')->select('k.kode_karyawan', DB::raw('SUM(total_penjualan_sales) as revenue'))->whereYear('tanggal_mulai', $tahun)->whereNotNull('rkm.sales_key')->whereNull('rkm.deleted_at')->groupBy('k.kode_karyawan')->get()->keyBy('kode_karyawan');

        $allKaryawan = karyawan::select('id', 'kode_karyawan', 'nama_lengkap')
            ->where('status_aktif', '1')
            ->whereNotIn('jabatan', ['Outsource', 'Pilih Jabatan'])
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNotNull('nip')
            ->whereNot('divisi', 'Direksi')
            ->where(function ($q) {
                $q->where('jabatan', 'Sales')->orWhereNull('jabatan');
            })
            ->get();

        $dataTarget = \App\Models\TargetPenjualan::where('tahun', $tahun)->pluck('nilai_target', 'id_sales')->toArray();

        $allSalesData = [];
        foreach ($allKaryawan as $karyawan) {
            $salesKey = $karyawan->kode_karyawan;
            if (!$salesKey) {
                continue;
            }

            $salesRevenue = (int) round($revenueBySalesKey[$salesKey]->revenue ?? 0);

            $targetPersonal = $dataTarget[$salesKey] ?? ($dataTarget[$karyawan->id] ?? 0);

            $percentage = $targetPersonal > 0 ? round(($salesRevenue / $targetPersonal) * 100) : 0;

            $allSalesData[] = [
                'kode_karyawan' => $salesKey,
                'nama' => $karyawan->nama_lengkap ?? $salesKey,
                'revenue' => $salesRevenue,
                'presentase_kemampuan' => $targetPersonal,
                'percentage' => $percentage,
                'status' => $salesRevenue >= $targetPersonal ? 'achieved' : 'pending',
            ];
        }

        $monthlyData = collect($monthlyDataTemp)->sortKeys()->map(fn($v) => (int) round($v))->toArray();
        $triwulanData = collect($triwulanDataTemp)->mapWithKeys(fn($value, $key) => ['Triwulan_' . $key => (int) round($value)])->toArray();

        ksort($dailyBreakdownPerMonth);

        return array_merge($defaultResponse, [
            'progress' => $progressPercent,
            'gap' => $gap,
            'pie_chart' => ['above' => $totalSales >= $nilaiTarget ? 1 : 0, 'below' => $totalSales >= $nilaiTarget ? 0 : 1],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'triwulan_data' => $triwulanData,
            'sales_performance' => ['type' => 'all', 'data' => $allSalesData],
        ]);
    }

    public function calculatePemasukanBersih($item, $personId)
    {
        $detailResult = $this->calculatePemasukanBersihDetail($item, $personId);
        return $detailResult['progress'];
    }

    public function calculatePemasukanBersihDetail($itemDetail, $personId = null)
    {
        $empty = $this->getDefaultDetailResponse(['previous_quarter' => []]);
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $empty;
        }

        $tahun = (int) $detail->detail_jangka;
        $labaKotorNominal = (float) (ApprovalPendapatan::whereYear('tanggal_mulai', $tahun)->sum(DB::raw('CAST(harga_net AS UNSIGNED) * CAST(pax AS UNSIGNED)')) ?? 0);

        if ($labaKotorNominal <= 0) {
            return $empty;
        }

        $nilaiTarget = (float) $detail->nilai_target;

        $nominal = (float) (AnalysisReport::where('year', $tahun)->sum('nilai') ?? 0);

        if ($nominal === 0) {
            return $empty;
        }

        $progress = round(($nominal / $labaKotorNominal) * 100, 2);
        $gap = $this->calculateAndFormatGap($progress, $nilaiTarget);

        $currentQuarter = ceil(now()->month / 3);
        $prevQuarter = $currentQuarter - 1;
        $prevYear = $tahun;
        if ($prevQuarter < 1) {
            $prevQuarter = 4;
            $prevYear--;
        }

        $previousQuarterData = AnalysisReport::where('year', $prevYear)->get()->toArray();

        $monthly_data = [];
        $monthly_progress = [];
        $daily_breakdown_per_month = [];
        $daily_progress_per_month = [];

        $reports = AnalysisReport::select('month', 'nilai')->where('year', $tahun)->get();
        foreach ($reports as $report) {
            if (is_null($report->nilai)) {
                continue;
            }
            $monthKey = $tahun . '-' . str_pad($report->month, 2, '0', STR_PAD_LEFT);
            $nilai = (float) $report->nilai;

            $monthly_data[$monthKey] = $nilai;
            $monthly_progress[$monthKey] = round(($nilai / $labaKotorNominal) * 100, 1);

            $dayKey = $monthKey . '-01';
            $daily_breakdown_per_month[$monthKey][$dayKey] = $nilai;
            $daily_progress_per_month[$monthKey][$dayKey] = round(($nilai / $labaKotorNominal) * 100, 1);
        }

        ksort($monthly_data);
        ksort($monthly_progress);
        ksort($daily_breakdown_per_month);
        ksort($daily_progress_per_month);

        return array_merge($empty, [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $reports->count(), 'below' => max(0, 4 - $reports->count())],
            'monthly_data' => $monthly_data,
            'daily_breakdown_per_month' => $daily_breakdown_per_month,
            'monthly_progress' => $monthly_progress,
            'daily_progress_per_month' => $daily_progress_per_month,
            'previous_quarter' => ['year' => $prevYear, 'data' => $previousQuarterData],
        ]);
    }

    // ==========================================
    // 4. TARGET PENJUALAN PROJECT TAHUNAN
    // ==========================================

    public function calculateTargetPenjualanProjectTahunan($item, $personId)
    {
        $detailResult = $this->calculateTargetPenjualanProjectTahunanDetail($item, $personId);
        return $detailResult['progress'];
    }

    public function calculateTargetPenjualanProjectTahunanDetail($itemDetail, $personId = null)
    {
        $empty = $this->getDefaultDetailResponse(['triwulan_data' => [], 'sales_performance' => null]);
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $empty;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $empty;
        }

        $query = LeadProject::where('status', 'won')->where('tahun_periode', $tahun);
        if ($personId !== null) {
            $kodeKaryawan = karyawan::where('id', $personId)->value('kode_karyawan');
            if ($kodeKaryawan) {
                $query->where('sales_id', $kodeKaryawan);
            }
        }

        $salesAggregated = (clone $query)
            ->selectRaw(
                '
                DATE_FORMAT(tahun_periode, "%Y-%m") as month_key,
                DATE_FORMAT(tahun_periode, "%Y-%m-%d") as day_key,
                SUM(estimasi_nilai) as total
            ',
            )
            ->groupBy('month_key', 'day_key')
            ->get();

        $totalSales = 0;
        $triwulanDataTemp = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
        $monthlyDataTemp = [];
        $dailyBreakdownPerMonth = [];

        foreach ($salesAggregated as $row) {
            $total = (float) ($row->total ?? 0);
            $totalSales += $total;
            $monthlyDataTemp[$row->month_key] = ($monthlyDataTemp[$row->month_key] ?? 0) + $total;
            $dailyBreakdownPerMonth[$row->month_key][$row->day_key] = $total;
            $triwulan = (int) ceil(Carbon::parse($row->month_key)->month / 3);
            $triwulanDataTemp[$triwulan] += $total;
        }

        $progressGlobal = $totalSales;
        $progressPercent = $nilaiTarget > 0 ? round(($totalSales / $nilaiTarget) * 100, 1) : 0.0;
        $gap = $this->calculateAndFormatGap($progressPercent, 100.0);

        $salesPerformance = null;
        if ($personId === null) {
            $allKaryawan = karyawan::select('id', 'kode_karyawan', 'nama_lengkap')
                ->where('status_aktif', '1')
                ->whereNotIn('jabatan', ['Outsource', 'Pilih Jabatan'])
                ->where('kode_karyawan', 'NOT LIKE', 'OL%')
                ->whereNotNull('nip')
                ->whereNot('divisi', 'Direksi')
                ->whereIn('jabatan', ['Sales', 'Sales Executive', 'Account Manager'])
                ->get();

            $revenueBySales = LeadProject::select('sales_id', DB::raw('SUM(estimasi_nilai) as total'))->where('status', 'won')->where('tahun_periode', $tahun)->groupBy('sales_id')->get()->keyBy('sales_id');

            // PERBAIKAN: Ambil target personal dari tabel TargetPenjualan sesuai tahun
            $dataTarget = \App\Models\TargetPenjualan::where('tahun', $tahun)->pluck('nilai_target', 'id_sales')->toArray();

            $allSalesData = [];
            foreach ($allKaryawan as $k) {
                if (!$k->kode_karyawan) {
                    continue;
                }
                $rev = (float) ($revenueBySales[$k->kode_karyawan]->total ?? 0);

                // Mencocokkan target berdasarkan id_sales (fallback ke id karyawan jika perlu)
                $targetPersonal = $dataTarget[$k->kode_karyawan] ?? ($dataTarget[$k->id] ?? 0);

                $pct = $targetPersonal > 0 ? round(($rev / $targetPersonal) * 100, 1) : 0;

                $allSalesData[] = [
                    'kode_karyawan' => $k->kode_karyawan,
                    'nama' => $k->nama_lengkap ?? $k->kode_karyawan,
                    'revenue' => $rev,
                    'presentase_kemampuan' => $targetPersonal, // Key tetap dipertahankan agar kompatibel
                    'percentage' => $pct,
                    'status' => $rev >= $targetPersonal ? 'achieved' : 'pending',
                ];
            }
            $salesPerformance = ['type' => 'all', 'data' => $allSalesData];
        }

        return array_merge($empty, [
            'progress' => $progressPercent,
            'gap' => $gap,
            'pie_chart' => ['above' => $totalSales >= $nilaiTarget ? 1 : 0, 'below' => $totalSales >= $nilaiTarget ? 0 : 1],
            'monthly_data' => collect($monthlyDataTemp)->sortKeys()->toArray(),
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'triwulan_data' => collect($triwulanDataTemp)->mapWithKeys(fn($v, $k) => ['Triwulan_' . $k => $v])->toArray(),
            'sales_performance' => $salesPerformance,
        ]);
    }

    // ==========================================
    // 5. RASIO BIAYA OPERASIONAL
    // ==========================================

    public function calculateRasioBiayaOperasionalTerhadapRevenue($item, $personId)
    {
        $detailResult = $this->calculateRasioBiayaOperasionalTerhadapRevenueDetail($item, $personId);
        return $detailResult['progress'];
    }

    public function calculateRasioBiayaOperasionalTerhadapRevenueDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();
        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target) || is_null($detail->manual_value)) {
            return $this->getDefaultDetailResponse(['dataManual' => ['manual_document' => $detail->manual_document ?? null]]);
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $manualValue = (float) $detail->manual_value;

        $tahun = (int) $detail->detail_jangka;
        $labaKotorNominal = (float) (ApprovalPendapatan::whereYear('tanggal_mulai', $tahun)->sum(DB::raw('CAST(harga_net AS UNSIGNED) * CAST(pax AS UNSIGNED)')) ?? 0);

        if ($labaKotorNominal <= 0 || $manualValue <= 0) {
            return $this->getDefaultDetailResponse(['dataManual' => ['manual_document' => $detail->manual_document ?? null]]);
        }

        $rasio = ($manualValue / $labaKotorNominal) * 100;
        $progress = $rasio > 0 ? round(($nilaiTarget / $rasio) * 100, 1) : 0.0;
        $gap = $this->calculateAndFormatGap($progress, 100.0);

        return array_merge($this->getDefaultDetailResponse(), [
            'progress' => $progress,
            'gap' => $gap,
            'dataManual' => ['manual_document' => $detail->manual_document ?? null],
        ]);
    }

    // ==========================================
    // 6. PERFORMA KPI DEPARTEMEN
    // ==========================================

    private function resolveKpiProgress($item, $personId, $route)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !is_numeric($detail->nilai_target)) {
            return 0.0;
        }

        $targetVal = (float) $detail->nilai_target;
        if ($targetVal <= 0) {
            return 0.0;
        }

        $data = 0.0;
        match (strtolower($route)) {
            'pemasukan kotor' => ($data = (float) (ApprovalPendapatan::whereYear('tanggal_mulai', now()->year)->sum(DB::raw('CAST(harga_net AS UNSIGNED) * CAST(pax AS UNSIGNED)')) ?? 0)),
            'target penjualan project tahunan' => ($data = (float) (LeadProject::where('status', 'won')->where('tahun_periode', now()->year)->sum('estimasi_nilai') ?? 0)),
            default => ($data = 0.0),
        };

        return $targetVal > 0 ? max(0, min(100, round(($data / $targetVal) * 100, 1))) : 0.0;
    }

    public function calculatePerformaKPIDepartemen($item, $personId)
    {
        $detailResult = $this->calculatePerformaKPIDepartemenDetail($item, $personId);
        return $detailResult['progress'];
    }

    public function calculatePerformaKPIDepartemenDetail($itemDetail, $personId = null)
    {
        $empty = $this->getDefaultDetailResponse([
            'division_breakdown' => [],
            'top_division' => null,
            'lowest_division' => null,
            'trend' => 'stable',
            'trend_value' => 0,
            'consistency' => 'stable',
            'target_status' => 'behind',
            'total_kpi' => 0,
            'total_division' => 0,
            'risk_divisions' => [],
            'insight' => '',
        ]);

        $allTargets = targetKPI::with(['detailTargetKPI.dataTarget:id,id_targetKPI,nilai_target,tipe_target,asistant_route', 'detailTargetKPI:id,id_targetKPI,divisi'])
            ->whereYear('created_at', now()->year)
            ->get();

        $targetsByDivisi = [];
        foreach ($allTargets as $target) {
            foreach ($target->detailTargetKPI as $detail) {
                if ($detail->divisi && $detail->dataTarget) {
                    $targetsByDivisi[$detail->divisi][] = ['target' => $target, 'detail' => $detail];
                }
            }
        }

        $divisionAverages = [];
        $allProgress = [];

        foreach ($targetsByDivisi as $divisi => $items) {
            $progresses = [];
            foreach ($items as $item) {
                $detail = $item['detail'];
                $route = strtolower($detail->dataTarget->asistant_route ?? '');
                if ($route === 'performa kpi departemen') {
                    continue;
                }

                $progress = $this->resolveKpiProgress($item['target'], $personId, $route);

                if (is_numeric($progress)) {
                    $progresses[] = $progress;
                    $allProgress[] = $progress;
                }
            }

            if (!empty($progresses)) {
                $divisionAverages[$divisi] = round(array_sum($progresses) / count($progresses), 1);
            }
        }

        $progress = !empty($divisionAverages) ? round(array_sum($divisionAverages) / count($divisionAverages), 1) : 0.0;
        $averageTarget = 100.0;
        $gap = $this->calculateAndFormatGap($progress, $averageTarget);

        arsort($divisionAverages);
        $topDiv = key($divisionAverages);
        $topVal = current($divisionAverages);
        asort($divisionAverages);
        $lowDiv = key($divisionAverages);
        $lowVal = current($divisionAverages);

        $mean = count($allProgress) ? array_sum($allProgress) / count($allProgress) : 0;
        $variance = count($allProgress) ? array_sum(array_map(fn($v) => pow($v - $mean, 2), $allProgress)) / count($allProgress) : 0;
        $consistency = sqrt($variance) < 10 ? 'stable' : 'fluctuating';
        $targetStatus = $progress >= $averageTarget ? 'on_track' : ($progress >= $averageTarget - 5 ? 'at_risk' : 'behind');

        $riskDivisions = [];
        foreach ($divisionAverages as $div => $val) {
            if ($val < 70) {
                $riskDivisions[] = ['name' => $div, 'value' => $val];
            }
        }

        $insight = "Performa KPI departemen {$consistency} dengan rata-rata {$progress}%. ";
        $insight .= $topDiv ? "Divisi terbaik {$topDiv} ({$topVal}%). " : '';
        $insight .= $lowDiv ? "Terendah {$lowDiv} ({$lowVal}%). " : '';
        $insight .= "Status target: {$targetStatus}.";

        return array_merge($empty, [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => round(max(0, $progress), 1), 'below' => round(max(0, 100 - $progress), 1)],
            'division_breakdown' => $divisionAverages,
            'top_division' => $topDiv ? ['name' => $topDiv, 'value' => $topVal] : null,
            'lowest_division' => $lowDiv ? ['name' => $lowDiv, 'value' => $lowVal] : null,
            'trend' => 'stable',
            'trend_value' => 0,
            'consistency' => $consistency,
            'target_status' => $targetStatus,
            'total_kpi' => $allTargets->count(),
            'total_division' => count($divisionAverages),
            'risk_divisions' => $riskDivisions,
            'insight' => $insight,
        ]);
    }
}
