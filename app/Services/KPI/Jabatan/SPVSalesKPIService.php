<?php

namespace App\Services\KPI\Jabatan;

use App\Models\Aktivitas;
use App\Models\ApprovalPendapatan;
use App\Models\detailPersonKPI;
use App\Models\karyawan;
use App\Models\Peluang;
use App\Models\perhitunganNetSales;
use App\Models\targetKPI;
use App\Traits\KPIDefaultResponseTrait;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Services\KPI\Jabatan\GMKPIService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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

        $progress = 0;

        $peluang = ApprovalPendapatan::whereYear('created_at', $tahun)
            ->get();

        foreach ($peluang as $p) {
            $bersih = $p->total_penjualan_bersih;

            $progress += $bersih;
        }

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

        $approvals = ApprovalPendapatan::whereYear('created_at', $tahun)->get();

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

            // jika ingin TOTAL per bulan
            $monthlyData[$month] = $totalMonth;

            $monthlyProgress[$month] = $nilaiTarget > 0
                ? ($totalMonth / $nilaiTarget) * 100
                : 0;

            foreach ($days as $day => $value) {
                $dailyProgressPerMonth[$month][$day] = $nilaiTarget > 0
                    ? ($value / $nilaiTarget) * 100
                    : 0;
            }
        }

        $gap = $progress - $nilaiTarget;

        return [
            // samakan dengan original
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
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $labaKotor = app(GMKPIService::class)->calculatePemasukanKotor($item, $personId);

        if ($labaKotor == 0) {
            return 0;
        }

        $karyawanIds = detailPersonKPI::where('detailTargetKey', $detail->id)->pluck('id_karyawan');
        $kodeKaryawanList = karyawan::whereIn('id', $karyawanIds)->pluck('kode_karyawan')->filter();

        if ($kodeKaryawanList->isEmpty()) {
            return 0;
        }

        $totalBiayaAkuisisi = perhitunganNetSales::whereHas('rkm', function ($query) use ($kodeKaryawanList, $tahun) {
            $query->whereIn('sales_key', $kodeKaryawanList)
                ->whereYear('tanggal_awal', $tahun);
        })
            ->whereBetween('tgl_pa', [$start, $end])
            ->get()
            ->sum(function ($record) {
                return ($record->transportasi ?? 0) +
                    ($record->akomodasi_peserta ?? 0) +
                    ($record->akomodasi_tim ?? 0) +
                    ($record->fresh_money ?? 0) +
                    ($record->entertaint ?? 0) +
                    ($record->souvenir ?? 0) +
                    ($record->cashback ?? 0) +
                    ($record->sewa_laptop ?? 0);
            });

        if ($totalBiayaAkuisisi > ($labaKotor * ($nilaiTarget / 100))) {
            $totalBiayaAkuisisi = $labaKotor * ($nilaiTarget / 100);
        }

        $progress = 0;

        if ($totalBiayaAkuisisi > 0) {
            $rasio = ($totalBiayaAkuisisi / $labaKotor) * 100;
            $batas = $nilaiTarget;
            $progress = ($batas / $rasio) * 100;
        }

        return round($progress, 1);
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

        $Saless = karyawan::where('status_aktif', '1')->whereNot('jabatan', 'Outsource')->where('kode_karyawan', 'NOT LIKE', 'OL%')->whereNot('jabatan', 'Pilih Jabatan')->where('nip', '!=', null)->whereNot('divisi', 'Direksi')
            ->where('jabatan', 'Sales')
            ->get();

        if ($Saless->isEmpty()) {
            return 0;
        }

        // ✅ PERBAIKAN 1: Hanya hitung dari awal tahun sampai hari ini
        $startDate = Carbon::create($tahun, 1, 1);
        $endDate = min(Carbon::create($tahun, 12, 31), now());

        // Jika tanggal mulai lebih besar dari tanggal akhir, return 0
        if ($startDate > $endDate) {
            return 0;
        }

        $period = CarbonPeriod::create($startDate, $endDate);

        // ✅ OPTIMASI 2: Load semua aktivitas sekali saja (hindari query di dalam loop)
        $activities = Aktivitas::whereYear('created_at', $tahun)
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
                // Cek di array yang sudah di-load, bukan query database
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

        $Saless = karyawan::where('Divisi', '!=', 'Direksi')
            ->where('status_aktif', '1')->whereNot('jabatan', 'Outsource')->where('kode_karyawan', 'NOT LIKE', 'OL%')->whereNot('jabatan', 'Pilih Jabatan')->where('nip', '!=', null)
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

        $activities = Aktivitas::whereYear('created_at', $tahun)
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

    public function calculateBiayaAkuisisiClient($item, $personId)
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

        $peluang = Peluang::with('rkm.perhitunganNetSales')
            ->whereYear('created_at', $tahun)
            ->get();

        $actualCAC = 0;
        $targetTahunan = $this->calculatePemasukanKotor($item, $personId);

        foreach ($peluang as $p) {
            if ($p->tahap === 'merah') {
                if ($p->rkm && $p->rkm->perhitunganNetSales) {
                    foreach ($p->rkm->perhitunganNetSales as $perhitungan) {
                        $actualCAC +=
                            ($perhitungan->transportasi ?? 0) +
                            ($perhitungan->akomodasi_peserta ?? 0) +
                            ($perhitungan->akomodasi_tim ?? 0) +
                            ($perhitungan->fresh_money ?? 0) +
                            ($perhitungan->entertaint ?? 0) +
                            ($perhitungan->souvenir ?? 0) +
                            ($perhitungan->cashback ?? 0) +
                            ($perhitungan->sewa_laptop ?? 0);
                    }
                }
            }
        }

        if ($actualCAC <= 0) {
            return 0.0;
        }

        $maxCAC = ($nilaiTarget / 100) * $targetTahunan;

        if ($maxCAC <= 0) {
            return 0.0;
        }

        $progress = min(($maxCAC / $actualCAC) * 100, 100);

        return round($progress, 2);
    }

    public function calculateBiayaAkuisisiClientDetail($itemDetail)
    {
        $details = $itemDetail->detailTargetKPI;
        $detail = $details->first();
        $nilaiTarget = (float) ($detail->nilai_target ?? 0);
        $item = $itemDetail;
        $personId = Auth::user()->id;

        if (!$detail || $nilaiTarget <= 0) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $tahun = (int) $detail->detail_jangka;
        $persentaseTarget = (float) $detail->nilai_target;
        $targetTahunanUnit = $this->calculatePemasukanKotor($item, $personId);

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $peluangs = Peluang::with('rkm.perhitunganNetSales')
            ->whereYear('created_at', $tahun)
            ->get();

        $actualCAC = 0;
        $dailyBreakdownPerMonth = [];

        $maxCAC = ($persentaseTarget / 100) * $targetTahunanUnit;

        foreach ($peluangs as $p) {
            if ($p->tahap !== 'merah') {
                continue;
            }

            $totalBiayaPeluang = 0;
            if ($p->rkm && $p->rkm->perhitunganNetSales) {
                foreach ($p->rkm->perhitunganNetSales as $perhitungan) {
                    $totalBiayaPeluang += ($perhitungan->transportasi ?? 0)
                        + ($perhitungan->akomodasi_peserta ?? 0)
                        + ($perhitungan->akomodasi_tim ?? 0)
                        + ($perhitungan->fresh_money ?? 0)
                        + ($perhitungan->entertaint ?? 0)
                        + ($perhitungan->souvenir ?? 0)
                        + ($perhitungan->cashback ?? 0)
                        + ($perhitungan->sewa_laptop ?? 0);
                }
            }

            $actualCAC += $totalBiayaPeluang;

            $date = \Carbon\Carbon::parse($p->created_at);
            $dateKey = $date->format('Y-m-d');
            $monthKey = $date->format('Y-m');

            if (!isset($dailyBreakdownPerMonth[$monthKey][$dateKey])) {
                $dailyBreakdownPerMonth[$monthKey][$dateKey] = 0;
            }
            $dailyBreakdownPerMonth[$monthKey][$dateKey] += $totalBiayaPeluang;
        }

        $progress = 0;
        if ($actualCAC > 0) {
            $progress = min(($maxCAC / $actualCAC) * 100, 100);
        }

        $monthlyData = [];
        foreach ($dailyBreakdownPerMonth as $month => $days) {
            $monthlyData[$month] = round(array_sum($days) / count($days), 1);
        }

        ksort($monthlyData);
        ksort($dailyBreakdownPerMonth);

        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($monthlyData as $month => $value) {
            $monthlyProgress[$month] = $maxCAC > 0 ? round(($value / $maxCAC) * 100, 1) : 0;
        }

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $day => $value) {
                if (!isset($dailyProgressPerMonth[$month])) {
                    $dailyProgressPerMonth[$month] = [];
                }
                $dailyProgressPerMonth[$month][$day] = $maxCAC > 0 ? round(($value / $maxCAC) * 100, 1) : 0;
            }
        }

        $gapRaw = $maxCAC - $actualCAC;
        if ($progress > $nilaiTarget) {
            $gapRaw = 0;
        }
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        return [
            'progress' => round($progress, 1),
            'actual_cac' => $actualCAC,
            'max_cac' => $maxCAC,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $actualCAC > $maxCAC ? 1 : 0,
                'below' => $actualCAC <= $maxCAC ? 1 : 0
            ],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
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

        $totalSales = ApprovalPendapatan::whereYear('tanggal_mulai', $tahun)->select(DB::raw('SUM(CAST(harga_net AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total_sales'))->value('total_sales');

        $totalSales = (float) ($totalSales ?? 0);

        if ($totalSales <= 0) {
            return 0;
        }

        $progress = $totalSales;

        return round($progress);
    }

    public function calculatePemasukanKotorDetail($itemDetail)
    {
        $details = $itemDetail->detailTargetKPI;
        $detail = $details->first();

        $defaultResponse = [
            'progress' => 0,
            'gap' => 0,
            'pie_chart' => [
                'above' => 0,
                'below' => 0
            ],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
            'triwulan_data' => [],
            'sales_performance' => null,
            'dataManual' => [
                'manual_document' => $detail->manual_document ?? null
            ],
        ];

        if (
            !$detail ||
            !is_numeric($detail->detail_jangka) ||
            !is_numeric($detail->nilai_target)
        ) {
            return $defaultResponse;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $defaultResponse;
        }

        $sales = ApprovalPendapatan::query()
            ->whereYear('tanggal_mulai', $tahun)
            ->selectRaw('
                tanggal_mulai,
                SUM(CAST(harga_net AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total
            ')
            ->groupBy('tanggal_mulai')
            ->get();

        $totalSales = 0;
        $dailyBreakdownPerMonth = [];
        $monthlyDataTemp = [];
        $triwulanDataTemp = [
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0
        ];

        foreach ($sales as $row) {
            $date = Carbon::parse($row->tanggal_mulai);

            $dateKey = $date->format('Y-m-d');
            $monthKey = $date->format('Y-m');
            $total = (int) round($row->total ?? 0);

            $totalSales += $total;

            $dailyBreakdownPerMonth[$monthKey][$dateKey] = $total;
            $monthlyDataTemp[$monthKey] =
                ($monthlyDataTemp[$monthKey] ?? 0) + $total;

            $triwulan = ceil($date->month / 3);

            $triwulanDataTemp[$triwulan] += $total;
        }

        $monthlyData = collect($monthlyDataTemp)
            ->sortKeys()
            ->map(fn($v) => (int) round($v))
            ->toArray();

        ksort($dailyBreakdownPerMonth);

        $triwulanData = collect($triwulanDataTemp)
            ->mapWithKeys(fn($value, $key) => [
                'Triwulan_' . $key => (int) round($value)
            ])
            ->toArray();

        $progressGlobal = (int) round($totalSales);
        $gap = (int) round($progressGlobal - $nilaiTarget);

        $above = $totalSales >= $nilaiTarget ? 1 : 0;
        $below = $above ? 0 : 1;

        $monthlyProgress = [];
        $runningMonth = 0;

        foreach ($monthlyData as $month => $value) {
            $runningMonth += $value;

            $monthlyProgress[$month] = $nilaiTarget > 0
                ? round(($runningMonth / $nilaiTarget) * 100)
                : 0;
        }

        $dailyProgressPerMonth = [];
        $runningDay = 0;

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $day => $value) {
                $runningDay += $value;

                $dailyProgressPerMonth[$month][$day] = $nilaiTarget > 0
                    ? round(($runningDay / $nilaiTarget) * 100)
                    : 0;
            }
        }

        $allKaryawan = Karyawan::query()
            ->where(function ($q) {
                $q->where('status_aktif', '1')
                    ->where('jabatan', '!=', 'Outsource')
                    ->where('kode_karyawan', 'NOT LIKE', 'OL%')
                    ->where('jabatan', '!=', 'Pilih Jabatan')
                    ->whereNotNull('nip')
                    ->where('divisi', '!=', 'Direksi')
                    ->orWhereNull('status_aktif');
            })
            ->where(function ($q) {
                $q->where('jabatan', 'Sales')
                    ->orWhereNull('jabatan');
            })
            ->get();

        $revenueBySalesKey = ApprovalPendapatan::with('rkm')
            ->whereYear('tanggal_mulai', $tahun)
            ->get()
            ->filter(function ($item) {
                return $item->rkm && $item->rkm->sales_key;
            })
            ->groupBy(function ($item) {
                return $item->rkm->sales_key;
            })
            ->map(function ($items) {
                return $items->sum('total_penjualan_sales');
            })
            ->toArray();

        $targetPenjualanTahunan = targetKPI::whereHas(
            'detailTargetKPI.dataTarget',
            function ($q) {
                $q->where(
                    'asistant_route',
                    'target penjualan tahunan'
                );
            }
        )->first();

        $idTargetToUse = $targetPenjualanTahunan
            ? $targetPenjualanTahunan->id
            : $itemDetail->id;

        $detailPersons = DetailPersonKPI::query()
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

            $salesRevenue = (int) round(
                $revenueBySalesKey[$salesKey] ?? 0
            );

            $detailPerson = $detailPersons->get($karyawan->id);

            $presentaseKemampuan = $detailPerson
                ? (int) round($detailPerson->presentase_kemampuan ?? 0)
                : 0;

            $percentage = $presentaseKemampuan > 0
                ? round(($salesRevenue / $presentaseKemampuan) * 100)
                : 0;

            $allSalesData[] = [
                'kode_karyawan' => $salesKey,
                'nama' => $karyawan->nama_lengkap
                    ?? $karyawan->nama
                    ?? $salesKey,
                'revenue' => $salesRevenue,
                'id_detailPerson' => $detailPerson?->id,
                'presentase_kemampuan' => $presentaseKemampuan,
                'percentage' => $percentage,
                'status' => $salesRevenue >= $presentaseKemampuan
                    ? 'achieved'
                    : 'pending'
            ];
        }

        return [
            'progress' => $progressGlobal,
            'gap' => $gap,
            'dataManual' => [
                'manual_document' => $detail->manual_document ?? null
            ],
            'pie_chart' => [
                'above' => $above,
                'below' => $below
            ],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
            'triwulan_data' => $triwulanData,
            'sales_performance' => [
                'type' => 'all',
                'data' => $allSalesData
            ]
        ];
    }
}