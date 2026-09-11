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
use App\Models\LeadProject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SPVSalesKPIService
{
    use KPIDefaultResponseTrait;

    public function calculateMeningkatkanRevenuePerusahaan($item, $personId)
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

        $progress = (float) (ApprovalPendapatan::whereYear('created_at', $tahun)->sum('total_penjualan_bersih') ?? 0);

        return round($progress);
    }

    public function calculateMeningkatkanRevenuePerusahaanDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        $emptyResponse = [
            'progress' => 0,
            'gap' => 0,
            'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
        ];

        if (!$detail || !$detail->detail_jangka) {
            return $emptyResponse;
        }

        $tahun = (int) $detail->detail_jangka;
        $nilaiTarget = (float) $detail->nilai_target;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return $emptyResponse;
        }

        $approvals = ApprovalPendapatan::select('created_at', 'total_penjualan_bersih')
            ->whereYear('created_at', $tahun)
            ->get();

        $progress = 0;
        $dailyBreakdownPerMonth = [];

        foreach ($approvals as $approval) {
            $bersih = (float) $approval->total_penjualan_bersih;
            $progress += $bersih;

            $date = Carbon::parse($approval->created_at);
            $monthKey = $date->format('Y-m');
            $dayKey   = $date->format('Y-m-d');

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
            }

            if (!isset($dailyBreakdownPerMonth[$monthKey][$dayKey])) {
                $dailyBreakdownPerMonth[$monthKey][$dayKey] = 0;
            }

            $dailyBreakdownPerMonth[$monthKey][$dayKey] += $bersih;
        }

        ksort($dailyBreakdownPerMonth);

        $monthlyData = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            $totalMonth = array_sum($days);
            $monthlyData[$month] = $totalMonth;

            $monthlyProgress[$month] = $nilaiTarget > 0 ? ($totalMonth / $nilaiTarget) * 100 : 0;

            foreach ($days as $day => $value) {
                $dailyProgressPerMonth[$month][$day] = $nilaiTarget > 0 ? ($value / $nilaiTarget) * 100 : 0;
            }
        }

        $gap = $progress - $nilaiTarget;

        return [
            'progress' => round($progress),
            'gap' => $gap,
            'pie_chart' => [
                'above' => max($gap, 0),
                'below' => abs(min($gap, 0)),
            ],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    public function calculateCustomerAcquisitionCost($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("calculateCustomerAcquisitionCost: Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        $target = $detail->nilai_target;
        $start = Carbon::create($tahun, 1, 1)->startOfDay();
        $end = Carbon::create($tahun, Carbon::now()->month, Carbon::now()->daysInMonth)->endOfDay();

        $data = ApprovalPendapatanSales::select('id', 'tanggal_mulai', 'total_pa', 'oleh_oleh', 'entertainment', 'total_cashback', 'total_uang_saku', 'total_akomodasi', 'biaya_transport', 'harga_net', 'pax')
            ->with(['pendapatan:pax,harga_net'])
            ->whereBetween('tanggal_mulai', [$start, $end])
            ->get();

        if ($data->isEmpty()) return 0;

        $totalDataAkuisisi = 0;
        $dataAkuisisiTidakTerdata = 0;
        $achieve = 0;

        foreach ($data as $row) {
            $hargaNet = ($row->pendapatan?->pax ?? 0) * ($row->pendapatan?->harga_net ?? 0);
            $biayaPenjualan = (float) ($row->total_pa + $row->oleh_oleh + $row->entertainment + $row->total_cashback + $row->total_uang_saku + $row->total_akomodasi + $row->biaya_transport);
            $selisihBiayaUtama = (float) (($row->harga_net * $row->pax) - $hargaNet);
            $selisihBiaya = $biayaPenjualan > $selisihBiayaUtama ? ($biayaPenjualan - $selisihBiayaUtama) : 0;

            if ($selisihBiaya <= 0) {
                $dataAkuisisiTidakTerdata++;
                continue;
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

        return $progress;
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

        $data = ApprovalPendapatanSales::select('id', 'tanggal_mulai', 'total_pa', 'oleh_oleh', 'entertainment', 'total_cashback', 'total_uang_saku', 'total_akomodasi', 'biaya_transport', 'harga_net', 'pax')
            ->with(['pendapatan:pax,harga_net'])
            ->whereBetween('tanggal_mulai', [$start, $end])
            ->get();

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
            $selisihBiaya = $biayaPenjualan > $selisihBiayaUtama ? ($biayaPenjualan - $selisihBiayaUtama) : 0;

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

        $gapRaw = ($progress > $nilaiTarget) ? 0 : ($progress - $nilaiTarget);
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        return array_merge($this->getDefaultDetailResponse(), [
            'progress' => round($progress, 1),
            'gap' => $gap,
            'pie_chart' => [
                'above' => $achieve + $dataAkuisisiTidakTerdata,
                'below' => max(0, $totalCount - ($achieve + $dataAkuisisiTidakTerdata))
            ],
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

        $Saless = karyawan::select('id', 'kode_karyawan')
            ->where('status_aktif', '1')
            ->whereNot('jabatan', 'Outsource')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->whereNotNull('nip')
            ->whereNot('divisi', 'Direksi')
            ->where('jabatan', 'Sales')
            ->get();

        if ($Saless->isEmpty()) {
            return 0;
        }

        $startDate = Carbon::create($tahun, 1, 1);
        $endDate = min(Carbon::create($tahun, 12, 31), now());

        if ($startDate > $endDate) {
            return 0;
        }

        $period = CarbonPeriod::create($startDate, $endDate);

        $activities = Aktivitas::select('id_sales', 'created_at')
            ->whereYear('created_at', $tahun)
            ->whereIn('id_sales', $Saless->pluck('kode_karyawan'))
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

            foreach ($Saless as $sales) {
                $key = $sales->kode_karyawan . '_' . $dateKey;
                if (isset($activities[$key])) {
                    $totalAktif++;
                }
            }
        }

        $totalKemungkinan = $totalHariKerja * $Saless->count();

        if ($totalKemungkinan == 0) {
            return 0;
        }

        $progress = ($totalAktif / $totalKemungkinan) * 100;

        return round($progress, 2);
    }

    public function calculateEvaluasiKinerjaSalesDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        $emptyResponse = [
            'progress' => 0,
            'gap' => 0,
            'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
        ];

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $emptyResponse;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $emptyResponse;
        }

        $Saless = karyawan::select('id', 'kode_karyawan')
            ->where('Divisi', '!=', 'Direksi')
            ->where('status_aktif', '1')
            ->whereNot('jabatan', 'Outsource')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->whereNotNull('nip')
            ->where('jabatan', 'Sales')
            ->get();

        if ($Saless->isEmpty()) {
            return $emptyResponse;
        }

        $startDate = Carbon::create($tahun, 1, 1);
        $endDate = min(Carbon::create($tahun, 12, 31), now());

        if ($startDate > $endDate) {
            return $emptyResponse;
        }

        $period = CarbonPeriod::create($startDate, $endDate);

        $activities = Aktivitas::select('user_id', 'created_at')
            ->whereYear('created_at', $tahun)
            ->get()
            ->groupBy(function ($item) {
                return $item->user_id . '_' . Carbon::parse($item->created_at)->format('Y-m-d');
            });

        $totalHariKerja = 0;
        $totalAktif = 0;
        $dailyValues = [];

        foreach ($period as $date) {
            if ($date->isWeekend()) {
                continue;
            }

            $totalHariKerja++;
            $dateKey = $date->format('Y-m-d');
            $aktifHariIni = 0;

            foreach ($Saless as $sales) {
                $key = $sales->kode_karyawan . '_' . $dateKey;

                if (isset($activities[$key])) {
                    $totalAktif++;
                    $aktifHariIni++;
                }
            }

            $dailyValues[$dateKey] = $aktifHariIni;
        }

        $totalKemungkinan = $totalHariKerja * $Saless->count();

        if ($totalKemungkinan == 0) {
            return $emptyResponse;
        }

        $persentase = ($totalAktif / $totalKemungkinan) * 100;
        $progress = round($persentase, 2);

        $gapRaw = $progress - 100;
        $gap = rtrim(rtrim(sprintf('%.2f', $gapRaw), '0'), '.');

        $above = $totalAktif;
        $below = max(0, $totalKemungkinan - $totalAktif);

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];

        foreach ($dailyValues as $dateStr => $total) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [];
            }
            $monthlyData[$monthKey][] = $total;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $total;
        }

        $monthlyAverages = [];
        foreach ($monthlyData as $month => $dailyVals) {
            $monthlyAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 2);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);

        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($monthlyAverages as $month => $value) {
            $monthlyProgress[$month] = 100 > 0 ? round(($value / 100) * 100, 1) : 0;
        }

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $day => $value) {
                if (!isset($dailyProgressPerMonth[$month])) {
                    $dailyProgressPerMonth[$month] = [];
                }
                $dailyProgressPerMonth[$month][$day] = 100 > 0 ? round(($value / 100) * 100, 1) : 0;
            }
        }

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $above,
                'below' => $below
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    public function calculatePendapatanPenjualanProject($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            Log::warning("calculatePendapatanPenjualanProject: Detail tidak valid untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("calculatePendapatanPenjualanProject: Nilai target atau tahun tidak valid. Tahun: {$tahun}, Target: {$nilaiTarget}");
            return 0;
        }

        $kodeKaryawan = null;

        if ($personId !== null && $personId !== 'null' && $personId !== '') {
            $karyawanData = karyawan::find($personId);
            $kodeKaryawan = $karyawanData ? $karyawanData->kode_karyawan : null;
        }

        $query = LeadProject::where('status', 'won')->where('tahun_periode', $tahun);

        if ($kodeKaryawan) {
            $query->where('lead_projects.sales_id', $kodeKaryawan);
        }

        $totalSales = (float) ($query->select(DB::raw('SUM(lead_projects.estimasi_nilai) as total_sales'))->value('total_sales') ?? 0);

        return $totalSales;
    }

    public function calculatePendapatanPenjualanProjectDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return array_merge($this->getDefaultDetailResponse(), [
                'triwulan_data' => [],
                'sales_performance' => null,
            ]);
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return array_merge($this->getDefaultDetailResponse(), [
                'triwulan_data' => [],
                'sales_performance' => null,
            ]);
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
        for ($i = 1; $i <= 4; $i++) {
            $triwulanData['Triwulan_' . $i] = (float) number_format($triwulanDataTemp[$i], 1, '.', '');
        }

        $progressRupiah = (float) $totalSales;
        $targetGlobal = $nilaiTarget;
        $progressGlobal = $progressRupiah;
        $gap = $progressGlobal - $nilaiTarget;

        $above = $totalSales >= $targetGlobal ? 1 : 0;
        $below = 1 - $above;

        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($monthlyData as $month => $value) {
            $monthlyProgress[$month] = $targetGlobal > 0 ? (float) number_format(((float)$value / $targetGlobal) * 100, 1, '.', '') : 0;
        }

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $day => $value) {
                if (!isset($dailyProgressPerMonth[$month])) {
                    $dailyProgressPerMonth[$month] = [];
                }
                $dailyProgressPerMonth[$month][$day] = $targetGlobal > 0 ? (float) number_format(((float)$value / $targetGlobal) * 100, 1, '.', '') : 0;
            }
        }

        $salesPerformance = null;

        if ($personId === null) {
            $allSalesData = [];

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

            $salesKeys = $allKaryawan->pluck('kode_karyawan')->filter();
            
            $salesRevenues = LeadProject::where('status', 'won')
                ->where('tahun_periode', $tahun)
                ->whereIn('sales_id', $salesKeys)
                ->select('sales_id', DB::raw('SUM(estimasi_nilai) as total'))
                ->groupBy('sales_id')
                ->pluck('total', 'sales_id');

            $detailPersons = detailPersonKPI::where('id_target', $itemDetail->id)
                ->whereIn('id_karyawan', $allKaryawan->pluck('id'))
                ->get()
                ->keyBy('id_karyawan');

            foreach ($allKaryawan as $karyawanItem) {
                $salesKey = $karyawanItem->kode_karyawan;
                if (!$salesKey) continue;

                $salesRevenue = (float) ($salesRevenues[$salesKey] ?? 0);
                $detailPerson = $detailPersons->get($karyawanItem->id);
                $presentaseKemampuan = (float) ($detailPerson?->presentase_kemampuan ?? 0);
                $percentage = $presentaseKemampuan > 0 ? ($salesRevenue / $presentaseKemampuan) * 100 : 0;

                $allSalesData[] = [
                    'kode_karyawan' => (string) $salesKey,
                    'nama' => (string) ($karyawanItem->nama_lengkap ?? $karyawanItem->nama ?? $salesKey),
                    'revenue' => (float) number_format($salesRevenue, 1, '.', ''),
                    'id_detailPerson' => $detailPerson?->id,
                    'presentase_kemampuan' => (float) number_format($presentaseKemampuan, 1, '.', ''),
                    'percentage' => (float) number_format($percentage, 1, '.', ''),
                    'status' => $salesRevenue >= $presentaseKemampuan ? 'achieved' : 'pending'
                ];
            }

            $salesPerformance = [
                'type' => 'all',
                'data' => $allSalesData
            ];
        } else {
            $detailPerson = detailPersonKPI::where('id_target', $itemDetail->id)->where('id_karyawan', $personId)->first();
            $presentaseKemampuan = (float) ($detailPerson?->presentase_kemampuan ?? 0);
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
                    'status' => $totalSales >= $presentaseKemampuan ? 'achieved' : 'pending'
                ]
            ];
        }

        return [
            'progress' => round($progressGlobal, 1),
            'gap' => round($gap, 1),
            'dataManual' => ['manual_document' => $detail->manual_document ?? null],
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
            'triwulan_data' => $triwulanData,
            'sales_performance' => $salesPerformance,
        ];
    }

    public function calculateLeadsProject($item, $personId)
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

        $totalLead = LeadProject::where('tahun_periode', $tahun)->count();

        return round($totalLead);
    }

    public function calculateLeadsProjectDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!isset($detail) || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return array_merge($this->getDefaultDetailResponse(), [
                'triwulan_data' => [],
            ]);
        }

        $tahun = (int) $detail->detail_jangka;
        $targetTahunan = (int) $detail->nilai_target;

        if ($targetTahunan <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return array_merge($this->getDefaultDetailResponse(), [
                'triwulan_data' => [],
            ]);
        }

        $leads = LeadProject::where('tahun_periode', $tahun)
            ->selectRaw('DATE(tahun_periode) as tanggal, COUNT(*) as total')
            ->groupByRaw('DATE(tahun_periode)')
            ->get();

        $totalLead = 0;
        $monthlyDataTemp = [];
        $dailyBreakdownPerMonth = [];
        $triwulanDataTemp = [1 => 0, 2 => 0, 3 => 0, 4 => 0];

        foreach ($leads as $row) {
            $date = Carbon::parse($row->tanggal);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');
            $jumlah = (int) $row->total;

            $totalLead += $jumlah;
            $monthlyDataTemp[$monthKey] = ($monthlyDataTemp[$monthKey] ?? 0) + $jumlah;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $jumlah;

            $triwulan = (int) ceil($date->month / 3);
            $triwulanDataTemp[$triwulan] += $jumlah;
        }

        ksort($monthlyDataTemp);
        ksort($dailyBreakdownPerMonth);

        $monthlyProgress = [];
        foreach ($monthlyDataTemp as $month => $value) {
            $monthlyProgress[$month] = round(($value / $targetTahunan) * 100, 1);
        }

        $dailyProgressPerMonth = [];
        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $day => $value) {
                $dailyProgressPerMonth[$month][$day] = round(($value / $targetTahunan) * 100, 1);
            }
        }

        $triwulanData = [];
        for ($i = 1; $i <= 4; $i++) {
            $triwulanData["Triwulan_$i"] = $triwulanDataTemp[$i];
        }

        $gap = $totalLead - $targetTahunan;

        return [
            'progress' => round($totalLead),
            'gap' => $gap,
            'dataManual' => ['manual_document' => $detail->manual_document ?? null],
            'pie_chart' => [
                'above' => $totalLead >= $targetTahunan ? 1 : 0,
                'below' => $totalLead >= $targetTahunan ? 0 : 1,
            ],
            'monthly_data' => $monthlyDataTemp,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
            'triwulan_data' => $triwulanData,
        ];
    }
}