<?php

namespace App\Services\KPI\Jabatan;

use App\Models\Aktivitas;
use App\Models\ApprovalPendapatan;
use App\Models\detailPersonKPI;
use App\Models\karyawan;
use App\Models\perhitunganNetSales;
use App\Traits\KPIDefaultResponseTrait;
use App\Models\ApprovalPendapatanSales;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Log;

class SPVSalesKPIService
{
    use KPIDefaultResponseTrait;

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
            'pie_chart' => ['above' => max(0, $above), 'below' => max(0, $below)],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    public function calculateMeningkatkanRevenuePerusahaan($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        $nilaiTarget = (float) $detail->nilai_target;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

    $query = ApprovalPendapatan::with('rkm.sales')->whereYear('created_at', $tahun);

    if ($personId !== null) {
        $karyawan = Karyawan::find($personId);
        if ($karyawan) {
            $query->whereHas('rkm.sales', function ($q) use ($karyawan) {
                $q->where('kode_karyawan', $karyawan->kode_karyawan);
            });
        }
    }

        $totalRevenue = (float) ($query->sum('total_penjualan_bersih') ?? 0);

        $progress = $totalRevenue;

        return round($progress, 1);
    }

    public function calculateMeningkatkanRevenuePerusahaanDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $this->getDefaultDetailResponse();
        }

        $tahun = (int) $detail->detail_jangka;
        $nilaiTarget = (float) $detail->nilai_target;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return $this->getDefaultDetailResponse();
        }

        $query = ApprovalPendapatan::with('rkm.sales')->whereYear('created_at', $tahun);

        if ($personId !== null) {
            $karyawan = Karyawan::find($personId);

            if ($karyawan) {
                $query->whereHas('rkm.sales', function ($q) use ($karyawan) {
                    $q->where('kode_karyawan', $karyawan->kode_karyawan);
                });
            }
        }

        $approvals = $query->get();

        if ($approvals->isEmpty()) {
            return $this->getDefaultDetailResponse();
        }

        $totalRevenue = 0;
        $rawDailyData = [];

        foreach ($approvals as $approval) {
            $bersih = (float) $approval->total_penjualan_bersih;
            $totalRevenue += $bersih;

            $dateKey = Carbon::parse($approval->created_at)->format('Y-m-d');
            if (!isset($rawDailyData[$dateKey])) {
                $rawDailyData[$dateKey] = [];
            }
            $rawDailyData[$dateKey][] = $bersih;
        }

        $progress = $totalRevenue;
        $above = $totalRevenue >= $nilaiTarget ? 1 : 0;
        $below = 1 - $above;

        return $this->formatChartData($progress, $nilaiTarget, $above, $below, $rawDailyData);
    }

    public function calculateCustomerAcquisitionCost($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("calculateCustomerAcquisitionCost: Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $start = Carbon::create($tahun, 1, 1)->startOfDay();
        $end = Carbon::create($tahun, 12, 31)->endOfDay();

        $query = ApprovalPendapatanSales::whereBetween('tanggal_mulai', [$start, $end])
            ->select('id', 'tanggal_mulai', 'total_pa', 'oleh_oleh', 'entertainment', 'total_cashback', 'total_uang_saku', 'total_akomodasi', 'biaya_transport', 'harga_net', 'pax')
            ->with(['pendapatan' => function ($q) {
                $q->select('id', 'pax', 'harga_net');
            }]);

        if ($personId !== null) {
            $karyawan = karyawan::find($personId);
            if ($karyawan && $karyawan->kode_karyawan) {
                $query->whereHas('rkm', function ($q) use ($karyawan) {
                    $q->where('sales_key', $karyawan->kode_karyawan);
                });
            } else {
                return 0;
            }
        }

        $data = $query->get();

        if ($data->isEmpty()) return 0;

        $totalDataAkuisisi = 0;
        $dataAkuisisiTidakTerdata = 0;
        $achieve = 0;

        foreach ($data as $row) {
            $hargaNet = ($row->pendapatan?->pax ?? 0) * ($row->pendapatan?->harga_net ?? 0);
            $biayaPenjualan = (float) ($row->total_pa + $row->oleh_oleh + $row->entertainment + $row->total_cashback + $row->total_uang_saku + $row->total_akomodasi + $row->biaya_transport);
            $selisihBiayaUtama = (float) (($row->harga_net * $row->pax) - $hargaNet);
            $selisihBiaya = ($biayaPenjualan > $selisihBiayaUtama) ? ($biayaPenjualan - $selisihBiayaUtama) : 0;

            if ($selisihBiaya <= 0) {
                $dataAkuisisiTidakTerdata++;
            } else {
                $totalDataAkuisisi++;
                if ($hargaNet > 0) {
                    $persentase = ($selisihBiaya / $hargaNet) * 100;
                    if ($persentase <= 10) {
                        $achieve++;
                    }
                }
            }
        }

        $totalCount = $data->count();
        $progress = $totalCount > 0 ? round((($achieve + $dataAkuisisiTidakTerdata) / $totalCount) * 100, 2) : 0;

        return round($progress, 2);
    }

    public function calculateCustomerAcquisitionCostDetail($itemDetail, $personId = null)
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

        $start = Carbon::create($tahun, 1, 1)->startOfDay();
        $end = Carbon::create($tahun, 12, 31)->endOfDay();

        $query = ApprovalPendapatanSales::whereBetween('tanggal_mulai', [$start, $end])
            ->select('id', 'tanggal_mulai', 'total_pa', 'oleh_oleh', 'entertainment', 'total_cashback', 'total_uang_saku', 'total_akomodasi', 'biaya_transport', 'harga_net', 'pax')
            ->with(['pendapatan' => function ($q) {
                $q->select('id', 'pax', 'harga_net');
            }]);

        if ($personId !== null) {
            $karyawan = karyawan::find($personId);
            if ($karyawan && $karyawan->kode_karyawan) {
                $query->whereHas('rkm', function ($q) use ($karyawan) {
                    $q->where('sales_key', $karyawan->kode_karyawan);
                });
            } else {
                return $this->getDefaultDetailResponse();
            }
        }

        $data = $query->get();

        if ($data->isEmpty()) {
            return $this->getDefaultDetailResponse();
        }

        $totalDataAkuisisi = 0;
        $dataAkuisisiTidakTerdata = 0;
        $achieve = 0;

        $totalDataPerMonth = [];
        $achievedDataPerMonth = [];
        $totalDataPerDay = [];
        $achievedDataPerDay = [];

        foreach ($data as $row) {
            $date = Carbon::parse($row->tanggal_mulai);
            $dateKey = $date->format('Y-m-d');
            $monthKey = $date->format('Y-m');

            $totalDataPerMonth[$monthKey] = ($totalDataPerMonth[$monthKey] ?? 0) + 1;
            $totalDataPerDay[$monthKey][$dateKey] = ($totalDataPerDay[$monthKey][$dateKey] ?? 0) + 1;

            $achievedDataPerMonth[$monthKey] = $achievedDataPerMonth[$monthKey] ?? 0;
            $achievedDataPerDay[$monthKey][$dateKey] = $achievedDataPerDay[$monthKey][$dateKey] ?? 0;

            $hargaNet = ($row->pendapatan?->pax ?? 0) * ($row->pendapatan?->harga_net ?? 0);
            $biayaPenjualan = (float) ($row->total_pa + $row->oleh_oleh + $row->entertainment + $row->total_cashback + $row->total_uang_saku + $row->total_akomodasi + $row->biaya_transport);
            $selisihBiayaUtama = (float) (($row->harga_net * $row->pax) - $hargaNet);
            $selisihBiaya = ($biayaPenjualan > $selisihBiayaUtama) ? ($biayaPenjualan - $selisihBiayaUtama) : 0;

            $isRowAchieved = false;

            if ($selisihBiaya <= 0) {
                $dataAkuisisiTidakTerdata++;
                $isRowAchieved = true;
            } else {
                $totalDataAkuisisi++;
                if ($hargaNet > 0) {
                    $persentase = ($selisihBiaya / $hargaNet) * 100;
                    if ($persentase <= 10) {
                        $achieve++;
                        $isRowAchieved = true;
                    }
                }
            }

            if ($isRowAchieved) {
                $achievedDataPerMonth[$monthKey]++;
                $achievedDataPerDay[$monthKey][$dateKey]++;
            }
        }

        $totalCount = $data->count();
        $progress = $totalCount > 0 ? round((($achieve + $dataAkuisisiTidakTerdata) / $totalCount) * 100, 2) : 0;

        $monthlyData = [];
        $monthlyProgress = [];
        $dailyBreakdownPerMonth = [];
        $dailyProgressPerMonth = [];

        foreach ($totalDataPerMonth as $month => $total) {
            $capaianBulanan = $achievedDataPerMonth[$month];
            $monthlyData[$month] = $capaianBulanan;
            $monthlyProgress[$month] = $total > 0 ? round(($capaianBulanan / $total) * 100, 2) : 0;
        }

        foreach ($totalDataPerDay as $month => $days) {
            foreach ($days as $date => $total) {
                $capaianHarian = $achievedDataPerDay[$month][$date];
                $dailyBreakdownPerMonth[$month][$date] = $capaianHarian;
                $dailyProgressPerMonth[$month][$date] = $total > 0 ? round(($capaianHarian / $total) * 100, 2) : 0;
            }
        }

        $achievedCount = $achieve + $dataAkuisisiTidakTerdata;
        $pieAbove = $achievedCount;
        $pieBelow = max(0, $totalCount - $achievedCount);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        return array_merge($this->getDefaultDetailResponse(), [
            'progress' => round($progress, 1),
            'gap' => $gap,
            'pie_chart' => ['above' => $pieAbove, 'below' => $pieBelow],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ]);
    }

    public function calculateEvaluasiKinerjaSales($item, $personId)
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

        $queryKaryawan = karyawan::where('status_aktif', '1')
            ->whereNot('jabatan', 'Outsource')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->whereNotNull('nip')
            ->whereNot('divisi', 'Direksi')
            ->where('jabatan', 'Sales');

        if ($personId !== null) {
            $queryKaryawan->where('id', $personId);
        }

        $Saless = $queryKaryawan->get(['id']);

        if ($Saless->isEmpty()) {
            return 0;
        }

        $startDate = Carbon::create($tahun, 1, 1);
        $endDate = min(Carbon::create($tahun, 12, 31), now());

        if ($startDate > $endDate) {
            return 0;
        }

        $period = CarbonPeriod::create($startDate, $endDate);
        $salesIds = $Saless->pluck('kode_karyawan');

        $activities = Aktivitas::whereYear('created_at', $tahun)
            ->whereIn('id_sales', $salesIds)
            ->select('id_sales', 'created_at')
            ->get()
            ->groupBy(function ($item) {
                return $item->id_sales . '_' . Carbon::parse($item->created_at)->format('Y-m-d');
            });

        $totalHariKerja = 0;
        $totalAktif = 0;

        foreach ($period as $date) {
            if ($date->isWeekend()) {
                continue;
            }

            $totalHariKerja++;
            $dateKey = $date->format('Y-m-d');

            foreach ($salesIds as $salesId) {
                $key = $salesId . '_' . $dateKey;
                if (isset($activities[$key])) {
                    $totalAktif++;
                }
            }
        }

        $totalKemungkinan = $totalHariKerja * $salesIds->count();

        if ($totalKemungkinan == 0) {
            return 0;
        }

        $progress = ($totalAktif / $totalKemungkinan) * 100;

        return round($progress, 2);
    }

    public function calculateEvaluasiKinerjaSalesDetail($itemDetail, $personId = null)
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

        $queryKaryawan = karyawan::where('status_aktif', '1')
            ->whereNot('jabatan', 'Outsource')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->whereNotNull('nip')
            ->whereNot('divisi', 'Direksi')
            ->where('jabatan', 'Sales');

        if ($personId !== null) {
            $queryKaryawan->where('id', $personId);
        }

        $Saless = $queryKaryawan->get(['id']);

        if ($Saless->isEmpty()) {
            return $this->getDefaultDetailResponse();
        }

        $startDate = Carbon::create($tahun, 1, 1);
        $endDate = min(Carbon::create($tahun, 12, 31), now());

        if ($startDate > $endDate) {
            return $this->getDefaultDetailResponse();
        }

        $period = CarbonPeriod::create($startDate, $endDate);
        $salesIds = $Saless->pluck('kode_karyawan');

        $activities = Aktivitas::whereYear('created_at', $tahun)
            ->whereIn('id_sales', $salesIds)
            ->select('id_sales', 'created_at')
            ->get()
            ->groupBy(function ($item) {
                return $item->id_sales . '_' . Carbon::parse($item->created_at)->format('Y-m-d');
            });

        $totalHariKerja = 0;
        $totalAktif = 0;
        $dailyValues = [];
        $monthlyAktif = [];
        $monthlyKemungkinan = [];

        foreach ($period as $date) {
            if ($date->isWeekend()) {
                continue;
            }

            $totalHariKerja++;
            $dateKey = $date->format('Y-m-d');
            $monthKey = $date->format('Y-m');
            $aktifHariIni = 0;

            if (!isset($monthlyKemungkinan[$monthKey])) {
                $monthlyKemungkinan[$monthKey] = 0;
            }
            $monthlyKemungkinan[$monthKey] += $salesIds->count();

            foreach ($salesIds as $salesId) {
                $key = $salesId . '_' . $dateKey;
                if (isset($activities[$key])) {
                    $totalAktif++;
                    $aktifHariIni++;
                }
            }

            $dailyValues[$dateKey] = $aktifHariIni;
            
            if (!isset($monthlyAktif[$monthKey])) {
                $monthlyAktif[$monthKey] = 0;
            }
            $monthlyAktif[$monthKey] += $aktifHariIni;
        }

        $totalKemungkinan = $totalHariKerja * $salesIds->count();

        if ($totalKemungkinan == 0) {
            return $this->getDefaultDetailResponse();
        }

        $progress = round(($totalAktif / $totalKemungkinan) * 100, 2);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $totalAktif;
        $below = max(0, $totalKemungkinan - $totalAktif);

        $rawDailyData = [];
        $monthlyProgress = [];

        foreach ($dailyValues as $dateStr => $total) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            
            if (!isset($rawDailyData[$dateStr])) {
                $rawDailyData[$dateStr] = [];
            }
            $dailyKemungkinan = $salesIds->count();
            $dailyProgress = $dailyKemungkinan > 0 ? ($total / $dailyKemungkinan) * 100 : 0;
            $rawDailyData[$dateStr][] = $dailyProgress;
        }

        foreach ($monthlyAktif as $month => $aktif) {
            $kemungkinanBulanIni = $monthlyKemungkinan[$month] ?? 1;
            $monthlyProgress[$month] = round(($aktif / $kemungkinanBulanIni) * 100, 1);
        }

        $baseResponse = $this->formatChartData($progress, $nilaiTarget, $above, $below, $rawDailyData);
        $baseResponse['monthly_progress'] = $monthlyProgress;

        return $baseResponse;
    }
}