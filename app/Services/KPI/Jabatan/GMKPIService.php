<?php

namespace App\Services\KPI\Jabatan;

use App\Models\AnalysisReport;
use App\Models\ApprovalPendapatan;
use App\Models\detailPersonKPI;
use App\Models\IncomeTransaction;
use App\Models\karyawan;
use App\Models\LeadProject;
use App\Models\Nilaifeedback;
use App\Models\targetKPI;
use App\Traits\KPIDefaultResponseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GMKPIService
{
    use KPIDefaultResponseTrait;

    public function calculateProgressKepuasanPelanggan($item, $personId)
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

        $start = "$tahun-01-01";
        $end = "$tahun-12-31";

        $feedbacks = Nilaifeedback::with('rkm.materi')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $groupedFeedbacks = $feedbacks
            ->groupBy(function ($feedback) {
                return optional($feedback->rkm)->materi?->nama_materi.'/'.optional($feedback->rkm)->tanggal_awal;
            })
            ->filter();

        $averageFeedbacks = [];

        foreach ($groupedFeedbacks as $group) {
            $totalFeedbacks = $group->count();
            if ($totalFeedbacks === 0) {
                continue;
            }

            $averageM = round(($group->sum('M1') + $group->sum('M2') + $group->sum('M3') + $group->sum('M4')) / ($totalFeedbacks * 4), 1);
            $averageP = round(($group->sum('P1') + $group->sum('P2') + $group->sum('P3') + $group->sum('P4') + $group->sum('P5') + $group->sum('P6') + $group->sum('P7')) / ($totalFeedbacks * 7), 1);
            $averageF = round(($group->sum('F1') + $group->sum('F2') + $group->sum('F3') + $group->sum('F4') + $group->sum('F5')) / ($totalFeedbacks * 5), 1);
            $averageI = round(($group->sum('I1') + $group->sum('I2') + $group->sum('I3') + $group->sum('I4') + $group->sum('I5') + $group->sum('I6') + $group->sum('I7') + $group->sum('I8')) / ($totalFeedbacks * 8), 1);

            $averageIb = round(($group->sum('I1b') + $group->sum('I2b') + $group->sum('I3b') + $group->sum('I4b') + $group->sum('I5b') + $group->sum('I6b') + $group->sum('I7b') + $group->sum('I8b')) / ($totalFeedbacks * 8), 1);
            $averageIas = round(($group->sum('I1as') + $group->sum('I2as') + $group->sum('I3as') + $group->sum('I4as') + $group->sum('I5as') + $group->sum('I6as') + $group->sum('I7as') + $group->sum('I8as')) / ($totalFeedbacks * 8), 1);

            $averageValues = [$averageM, $averageP, $averageF, $averageI];
            if ($averageIb > 0) {
                $averageValues[] = $averageIb;
            }
            if ($averageIas > 0) {
                $averageValues[] = $averageIas;
            }

            $averageTotal = round(array_sum($averageValues) / count($averageValues), 1);
            $averageFeedbacks[] = $averageTotal;
        }

        $total = count($averageFeedbacks);
        if ($total > 0) {
            $above = count(array_filter($averageFeedbacks, fn ($v) => $v >= 3.5));

            return round(($above / $total) * 100, 1);
        }

        return 0;
    }

    public function calculateProgressKepuasanPelangganDetail($itemDetail, $personId = null)
    {
        $empty = array_merge($this->getDefaultDetailResponse(), [
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

        $detailJangkas = $itemDetail->detailTargetKPI->pluck('detail_jangka')->filter();

        if ($detailJangkas->isEmpty()) {
            return $empty;
        }

        $tahun = (int) $detailJangkas->first();

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return $empty;
        }

        $start = "$tahun-01-01";
        $end = "$tahun-12-31";

        $feedbacks = Nilaifeedback::with('rkm.materi')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        if ($feedbacks->isEmpty()) {
            return $empty;
        }

        $groupedFeedbacks = $feedbacks
            ->groupBy(function ($feedback) {
                $materiNama = optional($feedback->rkm)->materi?->nama_materi ?? 'unknown';
                $tanggalAwal = optional($feedback->rkm)->tanggal_awal ?? '0000-00-00';

                return $materiNama.'/'.$tanggalAwal;
            })
            ->filter();

        $averageFeedbacks = [];
        $dailyAverages = [];
        $dailyProgresses = [];
        $categoryTotals = ['M' => 0, 'P' => 0, 'F' => 0, 'I' => 0, 'Ib' => 0, 'Ias' => 0];
        $categoryCounts = ['M' => 0, 'P' => 0, 'F' => 0, 'I' => 0, 'Ib' => 0, 'Ias' => 0];
        $sessionScores = [];

        foreach ($groupedFeedbacks as $key => $group) {
            $totalFeedbacks = $group->count();
            if ($totalFeedbacks === 0) {
                continue;
            }

            $sums = [
                'M' => $group->sum('M1') + $group->sum('M2') + $group->sum('M3') + $group->sum('M4'),
                'P' => $group->sum('P1') + $group->sum('P2') + $group->sum('P3') + $group->sum('P4') + $group->sum('P5') + $group->sum('P6') + $group->sum('P7'),
                'F' => $group->sum('F1') + $group->sum('F2') + $group->sum('F3') + $group->sum('F4') + $group->sum('F5'),
                'I' => $group->sum('I1') + $group->sum('I2') + $group->sum('I3') + $group->sum('I4') + $group->sum('I5') + $group->sum('I6') + $group->sum('I7') + $group->sum('I8'),
                'Ib' => $group->sum('I1b') + $group->sum('I2b') + $group->sum('I3b') + $group->sum('I4b') + $group->sum('I5b') + $group->sum('I6b') + $group->sum('I7b') + $group->sum('I8b'),
                'Ias' => $group->sum('I1as') + $group->sum('I2as') + $group->sum('I3as') + $group->sum('I4as') + $group->sum('I5as') + $group->sum('I6as') + $group->sum('I7as') + $group->sum('I8as'),
            ];

            $avgM = round($sums['M'] / ($totalFeedbacks * 4), 1);
            $avgP = round($sums['P'] / ($totalFeedbacks * 7), 1);
            $avgF = round($sums['F'] / ($totalFeedbacks * 5), 1);
            $avgI = round($sums['I'] / ($totalFeedbacks * 8), 1);
            $avgIb = round($sums['Ib'] / ($totalFeedbacks * 8), 1);
            $avgIas = round($sums['Ias'] / ($totalFeedbacks * 8), 1);

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

            $sampleDate = $group->first()->created_at->format('Y-m-d');
            $dailyAverages[$sampleDate] = $finalAvg;
            $dailyProgresses[$sampleDate] = round($finalAvg * 20, 1);

            foreach (['M' => $avgM, 'P' => $avgP, 'F' => $avgF, 'I' => $avgI, 'Ib' => $avgIb, 'Ias' => $avgIas] as $k => $v) {
                if ($v > 0) {
                    $categoryTotals[$k] += $v;
                    ++$categoryCounts[$k];
                }
            }
        }

        $totalGroups = count($averageFeedbacks);
        $above = count(array_filter($averageFeedbacks, fn ($v) => $v >= 3.5));
        $below = $totalGroups - $above;
        $progress = $totalGroups > 0 ? round(($above / $totalGroups) * 100, 1) : 0;

        $nilaiTarget = $itemDetail->detailTargetKPI->pluck('nilai_target')->first() ?? 0;
        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

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

            $pct = $dailyProgresses[$dateStr] ?? 0;
            $monthlyProgress[$monthKey][] = $pct;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $pct;
        }

        $monthlyAverages = [];
        foreach ($monthlyData as $month => $vals) {
            $monthlyAverages[$month] = round(array_sum($vals) / count($vals), 1);
        }

        $monthlyProgressAverages = [];
        foreach ($monthlyProgress as $month => $vals) {
            $monthlyProgressAverages[$month] = round(array_sum($vals) / count($vals), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAverages);
        ksort($dailyProgressPerMonth);

        $mapping = [
            'M' => 'Materi',
            'P' => 'Pelayanan',
            'F' => 'Fasilitas',
            'I' => 'Instruktur',
            'Ib' => 'Instruktur 2',
            'Ias' => 'Asisten Instruktur',
        ];

        $categoryScores = [];
        foreach ($categoryTotals as $k => $total) {
            $label = $mapping[$k] ?? $k;
            $categoryScores[$label] = $categoryCounts[$k] > 0 ? round($total / $categoryCounts[$k], 1) : 0;
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
        $variance = 0;
        foreach ($averageFeedbacks as $v) {
            $variance += pow($v - $mean, 2);
        }
        $variance = count($averageFeedbacks) > 0 ? $variance / count($averageFeedbacks) : 0;
        $stdDev = sqrt($variance);
        $consistency = $stdDev < 0.3 ? 'stable' : 'fluctuating';

        $targetStatus = 'behind';
        if ($progress >= $nilaiTarget) {
            $targetStatus = 'on_track';
        } elseif ($gapRaw >= -5) {
            $targetStatus = 'at_risk';
        }

        $prediction = count($months) > 0 ? round(array_sum(array_slice($months, -3)) / min(3, count($months)), 1) : 0;
        $insight = "Kepuasan pelanggan {$trend} dengan perubahan {$trendValue}. Konsistensi {$consistency}.";

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
            'total_feedback' => $feedbacks->count(),
            'total_sessions' => $totalGroups,
            'insight' => $insight,
        ];
    }

    public function calculatePemasukanKotor($item, $personId)
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
        if ($nilaiTarget <= 0) {
            return 0;
        }

        $totalSales = ApprovalPendapatan::whereYear('tanggal_mulai', $tahun)
            ->select(DB::raw('SUM(CAST(harga_net AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total_sales'))
            ->value('total_sales');

        return (float) ($totalSales ?? 0);
    }

    public function calculatePemasukanKotorDetail($itemDetail, $personId = null)
    {
        $details = $itemDetail->detailTargetKPI;
        $detail = $details->first();

        $defaultResponse = array_merge($this->getDefaultDetailResponse(), [
            'triwulan_data' => [],
            'sales_performance' => null,
            'dataManual' => [
                'manual_document' => $detail->manual_document ?? null,
            ],
        ]);

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $defaultResponse;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $defaultResponse;
        }

        $sales = ApprovalPendapatan::query()
            ->whereYear('tanggal_mulai', $tahun)
            ->selectRaw('tanggal_mulai, SUM(CAST(harga_net AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total')
            ->groupBy('tanggal_mulai')
            ->get();

        $totalSales = 0;
        $dailyBreakdownPerMonth = [];
        $monthlyDataTemp = [];
        $triwulanDataTemp = [1 => 0, 2 => 0, 3 => 0, 4 => 0];

        foreach ($sales as $row) {
            $date = Carbon::parse($row->tanggal_mulai);
            $dateKey = $date->format('Y-m-d');
            $monthKey = $date->format('Y-m');
            $total = (int) round($row->total ?? 0);

            $totalSales += $total;
            $dailyBreakdownPerMonth[$monthKey][$dateKey] = $total;
            $monthlyDataTemp[$monthKey] = ($monthlyDataTemp[$monthKey] ?? 0) + $total;

            $triwulan = (int) ceil($date->month / 3);
            $triwulanDataTemp[$triwulan] += $total;
        }

        $monthlyData = collect($monthlyDataTemp)->sortKeys()->map(fn ($v) => (int) round($v))->toArray();
        ksort($dailyBreakdownPerMonth);

        $triwulanData = collect($triwulanDataTemp)
            ->mapWithKeys(fn ($value, $key) => ['Triwulan_'.$key => (int) round($value)])
            ->toArray();

        $progressGlobal = (int) round($totalSales);
        $gap = (int) round($progressGlobal - $nilaiTarget);
        $above = $totalSales >= $nilaiTarget ? 1 : 0;
        $below = $above ? 0 : 1;

        $monthlyProgress = [];
        $runningMonth = 0;
        foreach ($monthlyData as $month => $value) {
            $runningMonth += $value;
            $monthlyProgress[$month] = $nilaiTarget > 0 ? round(($runningMonth / $nilaiTarget) * 100) : 0;
        }

        $dailyProgressPerMonth = [];
        $runningDay = 0;
        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $day => $value) {
                $runningDay += $value;
                $dailyProgressPerMonth[$month][$day] = $nilaiTarget > 0 ? round(($runningDay / $nilaiTarget) * 100) : 0;
            }
        }

        $allKaryawan = karyawan::query()
            ->where(function ($q) {
                $q->where('status_aktif', '1')
                    ->whereNot('jabatan', 'Outsource')
                    ->where('kode_karyawan', 'NOT LIKE', 'OL%')
                    ->whereNot('jabatan', 'Pilih Jabatan')
                    ->whereNotNull('nip')
                    ->whereNot('divisi', 'Direksi')
                    ->orWhereNull('status_aktif');
            })
            ->where(function ($q) {
                $q->where('jabatan', 'Sales')->orWhereNull('jabatan');
            })
            ->get();

        $revenueBySalesKey = ApprovalPendapatan::with('rkm')
            ->whereYear('tanggal_mulai', $tahun)
            ->get()
            ->reduce(function ($carry, $item) {
                if ($item->rkm && $item->rkm->sales_key) {
                    $key = $item->rkm->sales_key;
                    $carry[$key] = ($carry[$key] ?? 0) + (float) ($item->total_penjualan_sales ?? 0);
                }

                return $carry;
            }, []);

        $targetPenjualanTahunan = targetKPI::whereHas('detailTargetKPI.dataTarget', function ($q) {
            $q->where('asistant_route', 'target penjualan tahunan');
        })->first();

        $idTargetToUse = $targetPenjualanTahunan ? $targetPenjualanTahunan->id : $itemDetail->id;

        $detailPersons = detailPersonKPI::query()
            ->where('id_target', $idTargetToUse)
            ->whereIn('id_karyawan', $allKaryawan->pluck('id'))
            ->get()
            ->keyBy('id_karyawan');

        $allSalesData = [];
        foreach ($allKaryawan as $karyawan) {
            $salesKey = $karyawan->kode_karyawan;
            if (!$salesKey) {
                continue;
            }

            $salesRevenue = (int) round($revenueBySalesKey[$salesKey] ?? 0);
            $detailPerson = $detailPersons->get($karyawan->id);
            $presentaseKemampuan = $detailPerson ? (int) round($detailPerson->presentase_kemampuan ?? 0) : 0;
            $percentage = $presentaseKemampuan > 0 ? round(($salesRevenue / $presentaseKemampuan) * 100) : 0;

            $allSalesData[] = [
                'kode_karyawan' => $salesKey,
                'nama' => $karyawan->nama_lengkap ?? $karyawan->nama ?? $salesKey,
                'revenue' => $salesRevenue,
                'id_detailPerson' => $detailPerson?->id,
                'presentase_kemampuan' => $presentaseKemampuan,
                'percentage' => $percentage,
                'status' => $salesRevenue >= $presentaseKemampuan ? 'achieved' : 'pending',
            ];
        }

        return [
            'progress' => $progressGlobal,
            'gap' => $gap,
            'dataManual' => ['manual_document' => $detail->manual_document ?? null],
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
            'triwulan_data' => $triwulanData,
            'sales_performance' => ['type' => 'all', 'data' => $allSalesData],
        ];
    }

    public function calculatePemasukanBersih($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");

            return 0;
        }

        $labaKotor = $this->calculatePemasukanKotor($item, $personId);
        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5 || $labaKotor == 0) {
            return 0;
        }

        $nominal = AnalysisReport::where('year', $tahun)->sum('nilai');
        if ($nominal === 0 || $labaKotor < $nominal) {
            return 0;
        }

        return round(($nominal / $labaKotor) * 100, 2);
    }

    public function calculatePemasukanBersihDetail($itemDetail, $personId = null)
    {
        $empty = array_merge($this->getDefaultDetailResponse(), ['previous_quarter' => []]);
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $empty;
        }

        $labaKotor = $this->calculatePemasukanKotor($itemDetail, $personId);
        if ($labaKotor <= 0) {
            return $empty;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $empty;
        }

        $currentMonth = now()->month;
        $currentQuarter = ceil($currentMonth / 3);
        $prevQuarter = $currentQuarter - 1;
        $prevYear = $tahun;

        if ($prevQuarter < 1) {
            $prevQuarter = 4;
            --$prevYear;
        }

        $previousQuarterData = AnalysisReport::where('year', $prevYear)->get();
        $dataAnalisis = AnalysisReport::where('year', $tahun)->get();
        $nominal = $dataAnalisis->sum('nilai');

        if ($nominal === 0) {
            return $empty;
        }

        $progress = $labaKotor > 0 ? round(($nominal / $labaKotor) * 100, 2) : 0;
        $gap = $progress < $nilaiTarget ? rtrim(rtrim(sprintf('%.1f', $progress - $nilaiTarget), '0'), '.') : 0;

        $monthly_data = [];
        $daily_breakdown_per_month = [];
        $monthly_progress = [];
        $daily_progress_per_month = [];

        foreach ($dataAnalisis as $report) {
            if (is_null($report->nilai)) {
                continue;
            }

            $month = (int) $report->month;
            $monthKey = $tahun.'-'.str_pad($month, 2, '0', STR_PAD_LEFT);
            $nilai = (float) $report->nilai;

            $monthly_data[$monthKey] = $nilai;
            $monthly_progress[$monthKey] = $labaKotor > 0 ? round(($nilai / $labaKotor) * 100, 1) : 0;

            $dayKey = $monthKey.'-01';
            if (!isset($daily_breakdown_per_month[$monthKey])) {
                $daily_breakdown_per_month[$monthKey] = [];
                $daily_progress_per_month[$monthKey] = [];
            }

            $daily_breakdown_per_month[$monthKey][$dayKey] = $nilai;
            $daily_progress_per_month[$monthKey][$dayKey] = $labaKotor > 0 ? round(($nilai / $labaKotor) * 100, 1) : 0;
        }

        ksort($monthly_data);
        ksort($monthly_progress);
        ksort($daily_breakdown_per_month);
        ksort($daily_progress_per_month);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'dataManual' => ['manual_document' => $detail->manual_document],
            'pie_chart' => ['above' => $dataAnalisis->count(), 'below' => $dataAnalisis->count() - 4],
            'monthly_data' => $monthly_data,
            'daily_breakdown_per_month' => $daily_breakdown_per_month,
            'monthly_progress' => $monthly_progress,
            'daily_progress_per_month' => $daily_progress_per_month,
            'previous_quarter' => ['year' => $prevYear, 'data' => $previousQuarterData],
        ];
    }

    public function calculateTargetPenjualanProjectTahunan($item, $personId)
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

        $query = LeadProject::where('status', 'won')->where('tahun_periode', $tahun);

        if ($personId !== null) {
            $kodeKaryawan = karyawan::where('id', $personId)->value('kode_karyawan');
            if (!$kodeKaryawan) {
                return 0;
            }
            $query->where('lead_projects.sales_id', $kodeKaryawan);
        }

        $totalSales = (float) ($query->select(DB::raw('SUM(lead_projects.estimasi_nilai) as total_sales'))->value('total_sales') ?? 0);

        return round($totalSales);
    }

    public function calculateTargetPenjualanProjectTahunanDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();
        $empty = array_merge($this->getDefaultDetailResponse(), ['triwulan_data' => [], 'sales_performance' => null]);

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $empty;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $empty;
        }

        $kodeKaryawan = null;
        $karyawanData = null;

        if ($personId !== null) {
            $karyawanData = karyawan::find($personId);
            $kodeKaryawan = $karyawanData ? $karyawanData->kode_karyawan : null;
        }

        $query = LeadProject::where('status', 'won')->where('tahun_periode', $tahun);
        if ($kodeKaryawan) {
            $query->where('lead_projects.sales_id', $kodeKaryawan);
        }

        $sales = $query->select('lead_projects.tahun_periode', DB::raw('SUM(lead_projects.estimasi_nilai) as total'))
            ->groupBy('lead_projects.tahun_periode')
            ->get();

        $totalSales = 0;
        $dailyBreakdownPerMonth = [];
        $monthlyDataTemp = [];
        $triwulanDataTemp = [1 => 0, 2 => 0, 3 => 0, 4 => 0];

        foreach ($sales as $row) {
            $date = Carbon::parse($row->tahun_periode);
            $dateKey = $date->format('Y-m-d');
            $monthKey = $date->format('Y-m');
            $total = (float) ($row->total ?? 0);

            $totalSales += $total;
            $dailyBreakdownPerMonth[$monthKey][$dateKey] = (float) number_format($total, 1, '.', '');
            $monthlyDataTemp[$monthKey] = ($monthlyDataTemp[$monthKey] ?? 0) + $total;

            $month = (int) $date->format('m');
            $triwulan = (int) ceil($month / 3);
            if (isset($triwulanDataTemp[$triwulan])) {
                $triwulanDataTemp[$triwulan] += $total;
            }
        }

        $monthlyData = [];
        foreach ($monthlyDataTemp as $month => $total) {
            $monthlyData[$month] = (float) number_format($total, 1, '.', '');
        }

        ksort($monthlyData);
        ksort($dailyBreakdownPerMonth);

        $triwulanData = [];
        for ($i = 1; $i <= 4; ++$i) {
            $triwulanData['Triwulan_'.$i] = (float) number_format($triwulanDataTemp[$i], 1, '.', '');
        }

        $progressGlobal = (float) $totalSales;
        $gap = $progressGlobal - $nilaiTarget;
        $above = $totalSales >= $nilaiTarget ? 1 : 0;
        $below = 1 - $above;

        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($monthlyData as $month => $value) {
            $monthlyProgress[$month] = $nilaiTarget > 0 ? (float) number_format(((float) $value / $nilaiTarget) * 100, 1, '.', '') : 0;
        }

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $day => $value) {
                if (!isset($dailyProgressPerMonth[$month])) {
                    $dailyProgressPerMonth[$month] = [];
                }
                $dailyProgressPerMonth[$month][$day] = $nilaiTarget > 0 ? (float) number_format(((float) $value / $nilaiTarget) * 100, 1, '.', '') : 0;
            }
        }

        $salesPerformance = null;

        if ($personId === null) {
            $allKaryawan = karyawan::where(function ($q) {
                $q->where('status_aktif', '1')
                    ->whereNot('jabatan', 'Outsource')
                    ->where('kode_karyawan', 'NOT LIKE', 'OL%')
                    ->whereNot('jabatan', 'Pilih Jabatan')
                    ->whereNotNull('nip')
                    ->whereNot('divisi', 'Direksi')
                    ->orWhereNull('status_aktif');
            })
            ->where(function ($q) {
                $q->where('jabatan', 'Sales')
                    ->orWhere('jabatan', 'Sales Executive')
                    ->orWhere('jabatan', 'Account Manager')
                    ->orWhereNull('jabatan')
                    ->where('status_aktif', '1');
            })->get();

            $salesRevenues = LeadProject::where('status', 'won')
                ->where('tahun_periode', $tahun)
                ->select('sales_id', DB::raw('SUM(estimasi_nilai) as total'))
                ->groupBy('sales_id')
                ->pluck('total', 'sales_id');

            $detailPersons = detailPersonKPI::where('id_target', $itemDetail->id)
                ->whereIn('id_karyawan', $allKaryawan->pluck('id'))
                ->get()
                ->keyBy('id_karyawan');

            $allSalesData = [];
            foreach ($allKaryawan as $karyawanItem) {
                $salesKey = $karyawanItem->kode_karyawan;
                if (!$salesKey) {
                    continue;
                }

                $salesRevenue = (float) ($salesRevenues[$salesKey] ?? 0);
                $detailPerson = $detailPersons->get($karyawanItem->id);
                $presentaseKemampuan = (float) ($detailPerson->presentase_kemampuan ?? 0);
                $percentage = $presentaseKemampuan > 0 ? ($salesRevenue / $presentaseKemampuan) * 100 : 0;

                $allSalesData[] = [
                    'kode_karyawan' => (string) $salesKey,
                    'nama' => (string) ($karyawanItem->nama_lengkap ?? $karyawanItem->nama ?? $salesKey),
                    'revenue' => (float) number_format($salesRevenue, 1, '.', ''),
                    'id_detailPerson' => $detailPerson?->id,
                    'presentase_kemampuan' => (float) number_format($presentaseKemampuan, 1, '.', ''),
                    'percentage' => (float) number_format($percentage, 1, '.', ''),
                    'status' => $salesRevenue >= $presentaseKemampuan ? 'achieved' : 'pending',
                ];
            }

            $salesPerformance = ['type' => 'all', 'data' => $allSalesData];
        } else {
            $detailPerson = detailPersonKPI::where('id_target', $itemDetail->id)->where('id_karyawan', $personId)->first();
            $presentaseKemampuan = (float) ($detailPerson->presentase_kemampuan ?? 0);
            $percentage = $presentaseKemampuan > 0 ? ($totalSales / $presentaseKemampuan) * 100 : 0;
            $karyawanName = $karyawanData ? ($karyawanData->nama_lengkap ?? $karyawanData->nama ?? '') : '';

            $salesPerformance = [
                'type' => 'individual',
                'data' => [
                    'kode_karyawan' => (string) $kodeKaryawan,
                    'nama' => (string) $karyawanName,
                    'revenue' => (float) number_format($totalSales, 1, '.', ''),
                    'id_detailPerson' => $detailPerson?->id,
                    'presentase_kemampuan' => (float) number_format($presentaseKemampuan, 1, '.', ''),
                    'percentage' => (float) number_format($percentage, 1, '.', ''),
                    'status' => $totalSales >= $presentaseKemampuan ? 'achieved' : 'pending',
                ],
            ];
        }

        return [
            'progress' => round($progressGlobal, 2),
            'gap' => round($gap, 1),
            'dataManual' => ['manual_document' => $detail->manual_document],
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
            'triwulan_data' => $triwulanData,
            'sales_performance' => $salesPerformance,
        ];
    }

    public function calculateRasioBiayaOperasionalTerhadapRevenue($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            Log::warning("Data detail tidak valid untuk target ID: {$item->id}");

            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun atau nilai target tidak valid untuk target ID: {$item->id}");

            return 0;
        }

        $labaKotor = $this->calculatePemasukanKotor($item, $personId);
        if ($labaKotor == 0 || is_null($detail->manual_value)) {
            return 0;
        }

        $incomeTransaksi = IncomeTransaction::where('year', $tahun)
            ->whereIn('item_code', [
                'fc_24', 'fc_25', 'fc_26', 'fc_27', 'fc_28', 'fc_29', 'fc_30', 'fc_31',
                'fc_32', 'fc_33', 'fc_34', 'fc_35', 'fc_36', 'fc_37', 'fc_38', 'fc_39',
                'fc_40', 'fc_44', 'fc_49', 'fc_50', 'fc_51', 'fc_84', 'fc_87',
            ])
            ->sum('amount');

        $progress = 0;
        if ($incomeTransaksi > 0 && $labaKotor > 0) {
            $rasio = ($incomeTransaksi / $labaKotor) * 100;
            if ($rasio > 0) {
                $progress = ($nilaiTarget / $rasio) * 100;
            }
        }

        return max(0, min(100, round($progress, 1)));
    }

    public function calculateRasioBiayaOperasionalTerhadapRevenueDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();
        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $this->getDefaultDetailResponse();
        }

        $labaKotor = $this->calculatePemasukanKotor($itemDetail, $personId);
        if ($labaKotor == 0 || is_null($detail->manual_value)) {
            return $this->getDefaultDetailResponse();
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $this->getDefaultDetailResponse();
        }

        $incomeTransaksi = IncomeTransaction::where('year', $tahun)
            ->whereIn('item_code', [
                'fc_24', 'fc_25', 'fc_26', 'fc_27', 'fc_28', 'fc_29', 'fc_30', 'fc_31',
                'fc_32', 'fc_33', 'fc_34', 'fc_35', 'fc_36', 'fc_37', 'fc_38', 'fc_39',
                'fc_40', 'fc_44', 'fc_49', 'fc_50', 'fc_51', 'fc_84', 'fc_87',
            ])
            ->sum('amount');

        $progress = 0;
        $rasio = 0;

        if ($incomeTransaksi > 0 && $labaKotor > 0) {
            $rasio = ($incomeTransaksi / $labaKotor) * 100;
            if ($rasio > 0) {
                $progress = ($nilaiTarget / $rasio) * 100;
            }
        }

        $progress = max(0, min(100, round($progress, 1)));
        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $monthlyIncomes = IncomeTransaction::selectRaw('MONTH(month) as bulan, SUM(amount) as total')
            ->where('year', $tahun)
            ->groupByRaw('MONTH(month)')
            ->pluck('total', 'bulan')
            ->toArray();

        $monthlyAverages = [];
        $monthlyProgressAverages = [];
        $dailyBreakdownPerMonth = [];
        $dailyProgressPerMonth = [];
        $aboveCount = 0;
        $belowCount = 0;

        for ($bulan = 1; $bulan <= 12; ++$bulan) {
            $monthKey = sprintf('%d-%02d', $tahun, $bulan);
            $incomeBulan = $monthlyIncomes[$bulan] ?? 0;
            $progressBulan = 0;

            if ($incomeBulan > 0 && $labaKotor > 0) {
                $rasioBulan = ($incomeBulan / $labaKotor) * 100;
                if ($rasioBulan > 0) {
                    $progressBulan = ($nilaiTarget / $rasioBulan) * 100;
                }
            }

            $progressBulan = max(0, min(100, round($progressBulan, 1)));
            $monthlyAverages[$monthKey] = $progressBulan;
            $monthlyProgressAverages[$monthKey] = $progressBulan;

            $dayKey = sprintf('%d-%02d-01', $tahun, $bulan);
            $dailyBreakdownPerMonth[$monthKey] = [$dayKey => $progressBulan];
            $dailyProgressPerMonth[$monthKey] = [$dayKey => $progressBulan];

            if ($progressBulan >= $nilaiTarget) {
                ++$aboveCount;
            } else {
                ++$belowCount;
            }
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAverages);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $aboveCount, 'below' => $belowCount],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
            'dataManual' => ['manual_document' => $detail->manual_document],
        ];
    }

    public function calculatePerformaKPIDepartemen($item, $personId)
    {
        $routeCalculators = [
            'pemasukan kotor' => fn ($t, $p) => $this->calculatePemasukanKotor($t, $p),
            'target penjualan project tahunan' => fn ($t, $p) => $this->calculateTargetPenjualanProjectTahunan($t, $p),
            'meningkatkan revenue perusahaan' => fn ($t, $p) => app(SPVSalesKPIService::class)->calculateMeningkatkanRevenuePerusahaan($t, $p),
            'pendapatan penjualan project' => fn ($t, $p) => app(SPVSalesKPIService::class)->calculatePendapatanPenjualanProject($t, $p),
        ];

        $allTargets = targetKPI::with(['detailTargetKPI.dataTarget'])->whereYear('created_at', now()->year)->get();
        $targetsByDivisi = [];

        foreach ($allTargets as $target) {
            $details = $target->detailTargetKPI;
            if (!$details || $details->isEmpty()) {
                continue;
            }

            foreach ($details->pluck('divisi')->unique()->filter() as $divisi) {
                $targetsByDivisi[$divisi][] = $target;
            }
        }

        $divisionAverages = [];
        foreach ($targetsByDivisi as $divisi => $items) {
            $progresses = [];
            foreach ($items as $itemTarget) {
                $detail = $itemTarget->detailTargetKPI->firstWhere('divisi', $divisi);
                if (!$detail) {
                    continue;
                }

                $route = strtolower($detail->dataTarget?->asistant_route ?? '');
                if ($route === 'performa kpi departemen') {
                    continue;
                }

                $calculator = $routeCalculators[$route] ?? null;
                if (!$calculator) {
                    continue;
                }

                $rawValue = $calculator($itemTarget, $personId);
                $progress = $this->normalizeToPercent($rawValue, $detail->tipe_target, $detail->nilai_target);
                $progresses[] = $progress;
            }

            if (!empty($progresses)) {
                $divisionAverages[] = round(array_sum($progresses) / count($progresses), 1);
            }
        }

        return empty($divisionAverages) ? 0 : round(array_sum($divisionAverages) / count($divisionAverages), 1);
    }

    protected function normalizeToPercent($rawValue, $tipeTarget, $nilaiTarget): float
    {
        $raw = (float) $rawValue;
        $target = (float) $nilaiTarget;
        if (in_array($tipeTarget, ['rupiah', 'angka']) && $target > 0) {
            $raw = ($raw / $target) * 100;
        }

        return max(0, min(100, $raw));
    }

    public function calculatePerformaKPIDepartemenDetail($itemDetail, $personId = null)
    {
        $routeCalculators = [
            'pemasukan kotor' => fn ($t, $p) => $this->calculatePemasukanKotor($t, $p),
            'target penjualan project tahunan' => fn ($t, $p) => $this->calculateTargetPenjualanProjectTahunan($t, $p),
            'meningkatkan revenue perusahaan' => fn ($t, $p) => app(SPVSalesKPIService::class)->calculateMeningkatkanRevenuePerusahaan($t, $p),
            'pendapatan penjualan project' => fn ($t, $p) => app(SPVSalesKPIService::class)->calculatePendapatanPenjualanProject($t, $p),
        ];

        $allTargets = targetKPI::with(['detailTargetKPI.dataTarget'])->whereYear('created_at', now()->year)->get();
        $targetsByDivisi = [];

        foreach ($allTargets as $target) {
            $details = $target->detailTargetKPI;
            if (!$details || $details->isEmpty()) {
                continue;
            }

            foreach ($details->pluck('divisi')->unique()->filter() as $divisi) {
                $targetsByDivisi[$divisi][] = $target;
            }
        }

        $divisionAverages = [];
        $divisionBreakdown = [];
        $allProgress = [];

        foreach ($targetsByDivisi as $divisi => $items) {
            $progresses = [];
            foreach ($items as $itemTarget) {
                $detail = $itemTarget->detailTargetKPI->firstWhere('divisi', $divisi);
                if (!$detail) {
                    continue;
                }

                $route = strtolower($detail->dataTarget?->asistant_route ?? '');
                if ($route === 'performa kpi departemen') {
                    continue;
                }

                $calculator = $routeCalculators[$route] ?? null;
                if (!$calculator) {
                    continue;
                }

                $rawValue = $calculator($itemTarget, $personId);
                $progress = $this->normalizeToPercent($rawValue, $detail->tipe_target, $detail->nilai_target);
                $progresses[] = $progress;
                $allProgress[] = $progress;
            }

            if (!empty($progresses)) {
                $avg = array_sum($progresses) / count($progresses);
                $divisionAverages[$divisi] = round($avg, 1);
                $divisionBreakdown[$divisi] = round($avg, 1);
            }
        }

        $progress = !empty($divisionAverages) ? round(array_sum($divisionAverages) / count($divisionAverages), 1) : 0;
        $nilaiTarget = (float) ($itemDetail->nilai_target ?? $itemDetail->dataTarget?->nilai_target ?? 100);

        if ($nilaiTarget <= 0) {
            $nilaiTarget = 100;
        }

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');
        if ($gap === '-0') {
            $gap = '0';
        }

        $above = round(max(0, $progress), 1);
        $below = round(max(0, 100 - $progress), 1);

        arsort($divisionAverages);
        $topDivisionName = key($divisionAverages);
        $topDivisionValue = current($divisionAverages);

        asort($divisionAverages);
        $lowestDivisionName = key($divisionAverages);
        $lowestDivisionValue = current($divisionAverages);

        $mean = count($allProgress) ? array_sum($allProgress) / count($allProgress) : 0;
        $variance = 0;

        foreach ($allProgress as $val) {
            $variance += pow($val - $mean, 2);
        }

        $variance = count($allProgress) ? $variance / count($allProgress) : 0;
        $stdDev = sqrt($variance);
        $consistency = $stdDev < 10 ? 'stable' : 'fluctuating';

        $targetStatus = 'behind';
        if ($progress >= $nilaiTarget) {
            $targetStatus = 'on_track';
        } elseif ($gapRaw >= -5) {
            $targetStatus = 'at_risk';
        }

        $riskDivisions = [];
        foreach ($divisionBreakdown as $div => $val) {
            if ($val < 70) {
                $riskDivisions[] = ['name' => $div, 'value' => $val];
            }
        }

        $insight = "Performa KPI departemen stable dengan rata-rata {$progress}%. ";
        $insight .= "Divisi terbaik {$topDivisionName} ({$topDivisionValue}%), ";
        $insight .= "terendah {$lowestDivisionName} ({$lowestDivisionValue}%). ";
        $insight .= "Status target: {$targetStatus}, konsistensi {$consistency}.";

        return array_merge($this->getDefaultDetailResponse(), [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'division_breakdown' => $divisionBreakdown,
            'top_division' => ['name' => $topDivisionName, 'value' => $topDivisionValue],
            'lowest_division' => ['name' => $lowestDivisionName, 'value' => $lowestDivisionValue],
            'trend' => 'stable',
            'trend_value' => 0,
            'consistency' => $consistency,
            'target_status' => $targetStatus,
            'total_kpi' => $allTargets->count(),
            'total_division' => count($divisionBreakdown),
            'risk_divisions' => $riskDivisions,
            'insight' => $insight,
        ]);
    }
}
