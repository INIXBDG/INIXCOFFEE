<?php

namespace App\Services\KPI\Jabatan;

use App\Models\LeadProject;
use App\Models\karyawan;
use App\Models\detailPersonKPI;
use App\Traits\KPIDefaultResponseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectAdminKPIService
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
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    public function calculatePendapatanPenjualanProject($item, $personId)
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

        $query = LeadProject::where('status', 'won')->where('tahun_periode', $tahun);

        if ($personId !== null) {
            $karyawan = karyawan::find($personId);
            if ($karyawan && $karyawan->kode_karyawan) {
                $query->where('sales_id', $karyawan->kode_karyawan);
            }
        }

        $totalSales = (float) ($query->sum('estimasi_nilai') ?? 0);
        $progress = $totalSales;

        return round($progress, 1);
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

        $query = LeadProject::where('status', 'won')->where('tahun_periode', $tahun);

        if ($personId !== null) {
            $karyawanData = karyawan::find($personId);
            if ($karyawanData && $karyawanData->kode_karyawan) {
                $query->where('sales_id', $karyawanData->kode_karyawan);
            }
        }

        $totalSales = (float) ($query->sum('estimasi_nilai') ?? 0);

        $chartQuery = LeadProject::where('status', 'won')->where('tahun_periode', $tahun)->selectRaw('DATE(created_at) as tanggal, SUM(estimasi_nilai) as total_nilai');

        if ($personId !== null) {
            $karyawanData = karyawan::find($personId);
            if ($karyawanData && $karyawanData->kode_karyawan) {
                $chartQuery->where('sales_id', $karyawanData->kode_karyawan);
            }
        }

        $chartData = $chartQuery->groupByRaw('DATE(created_at)')->get();

        $rawDailyData = [];
        $triwulanDataTemp = [1 => 0, 2 => 0, 3 => 0, 4 => 0];

        foreach ($chartData as $row) {
            $date = Carbon::parse($row->tanggal);
            $dateKey = $date->format('Y-m-d');
            $month = (int) $date->format('m');
            $triwulan = (int) ceil($month / 3);
            $total = (float) ($row->total_nilai ?? 0);

            if (!isset($rawDailyData[$dateKey])) {
                $rawDailyData[$dateKey] = [];
            }
            $rawDailyData[$dateKey][] = $total;

            if (isset($triwulanDataTemp[$triwulan])) {
                $triwulanDataTemp[$triwulan] += $total;
            }
        }

        $triwulanData = [];
        for ($i = 1; $i <= 4; $i++) {
            $triwulanData['Triwulan_' . $i] = (float) number_format($triwulanDataTemp[$i], 1, '.', '');
        }

        $progressPercentage = $totalSales;
        $above = $totalSales >= $nilaiTarget ? 1 : 0;
        $below = 1 - $above;

        $baseResponse = $this->formatChartData($progressPercentage, $nilaiTarget, $above, $below, $rawDailyData);

        $salesPerformance = null;

        if ($personId === null) {
            $allSalesData = [];

            $allKaryawan = karyawan::where(function ($q) {
                $q->where('status_aktif', '1')->whereNot('jabatan', 'Outsource')->where('kode_karyawan', 'NOT LIKE', 'OL%')->whereNot('jabatan', 'Pilih Jabatan')->whereNotNull('nip')->whereNot('divisi', 'Direksi');
            })
                ->where(function ($q) {
                    $q->whereIn('jabatan', ['Sales', 'Sales Executive', 'Account Manager'])->orWhereNull('jabatan');
                })
                ->get();

            $revenues = LeadProject::where('status', 'won')->where('tahun_periode', $tahun)->select('sales_id', DB::raw('SUM(estimasi_nilai) as total_revenue'))->groupBy('sales_id')->get()->pluck('total_revenue', 'sales_id');

            $karyawanIds = $allKaryawan->pluck('id');
            $detailPersons = detailPersonKPI::where('id_target', $itemDetail->id)->whereIn('id_karyawan', $karyawanIds)->get()->keyBy('id_karyawan');

            foreach ($allKaryawan as $k) {
                $salesKey = $k->kode_karyawan;
                if (!$salesKey) {
                    continue;
                }

                $salesRevenue = (float) ($revenues[$salesKey] ?? 0);
                $detailPerson = $detailPersons[$k->id] ?? null;
                $presentaseKemampuan = (float) ($detailPerson->presentase_kemampuan ?? 0);

                $percentage = $presentaseKemampuan > 0 ? ($salesRevenue / $presentaseKemampuan) * 100 : 0;

                $allSalesData[] = [
                    'kode_karyawan' => (string) $salesKey,
                    'nama' => (string) ($k->nama_lengkap ?? ($k->nama ?? $salesKey)),
                    'revenue' => (float) number_format($salesRevenue, 1, '.', ''),
                    'id_detailPerson' => $detailPerson ? $detailPerson->id : null,
                    'presentase_kemampuan' => (float) number_format($presentaseKemampuan, 1, '.', ''),
                    'percentage' => (float) number_format($percentage, 1, '.', ''),
                    'status' => $salesRevenue >= $presentaseKemampuan ? 'achieved' : 'pending',
                ];
            }

            $salesPerformance = [
                'type' => 'all',
                'data' => $allSalesData,
            ];
        } else {
            $karyawanData = karyawan::find($personId);
            $detailPerson = detailPersonKPI::where('id_target', $itemDetail->id)->where('id_karyawan', $personId)->first();

            $presentaseKemampuan = (float) ($detailPerson->presentase_kemampuan ?? 0);
            $percentage = $presentaseKemampuan > 0 ? ($totalSales / $presentaseKemampuan) * 100 : 0;
            $karyawanName = $karyawanData ? $karyawanData->nama_lengkap ?? ($karyawanData->nama ?? '') : '';

            $salesPerformance = [
                'type' => 'individual',
                'data' => [
                    'kode_karyawan' => (string) ($karyawanData->kode_karyawan ?? ''),
                    'nama' => (string) $karyawanName,
                    'revenue' => (float) number_format($totalSales, 1, '.', ''),
                    'id_detailPerson' => $detailPerson ? $detailPerson->id : null,
                    'presentase_kemampuan' => (float) number_format($presentaseKemampuan, 1, '.', ''),
                    'percentage' => (float) number_format($percentage, 1, '.', ''),
                    'status' => $totalSales >= $presentaseKemampuan ? 'achieved' : 'pending',
                ],
            ];
        }

        return array_merge($baseResponse, [
            'dataManual' => [
                'manual_document' => $detail->manual_document ?? null,
            ],
            'triwulan_data' => $triwulanData,
            'sales_performance' => $salesPerformance,
            'total_revenue_rupiah' => (float) number_format($totalSales, 1, '.', ''),
        ]);
    }

    public function calculateLeadsProject($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        $target = (float) $detail->nilai_target;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $query = LeadProject::where('tahun_periode', $tahun);

        if ($personId !== null) {
            $karyawan = karyawan::find($personId);
            if ($karyawan && $karyawan->kode_karyawan) {
                $query->where('sales_id', $karyawan->kode_karyawan);
            }
        }

        $totalLead = $query->count();
        $progress = $totalLead;

        return round($progress);
    }

    public function calculateLeadsProjectDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return array_merge($this->getDefaultDetailResponse(), [
                'triwulan_data' => [],
            ]);
        }

        $tahun = (int) $detail->detail_jangka;
        $targetTahunan = (float) $detail->nilai_target;

        if ($targetTahunan <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return array_merge($this->getDefaultDetailResponse(), [
                'triwulan_data' => [],
            ]);
        }

        $query = LeadProject::where('tahun_periode', $tahun);

        if ($personId !== null) {
            $karyawan = karyawan::find($personId);
            if ($karyawan && $karyawan->kode_karyawan) {
                $query->where('sales_id', $karyawan->kode_karyawan);
            }
        }

        $leads = $query->selectRaw('DATE(created_at) as tanggal, COUNT(*) as total')->groupByRaw('DATE(created_at)')->get();

        $totalLead = 0;
        $rawDailyData = [];
        $triwulanDataTemp = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
        $monthlyDataTemp = [];

        foreach ($leads as $row) {
            $date = Carbon::parse($row->tanggal);
            $dateKey = $date->format('Y-m-d');
            $monthKey = $date->format('Y-m');
            $jumlah = (int) $row->total;

            $totalLead += $jumlah;

            if (!isset($rawDailyData[$dateKey])) {
                $rawDailyData[$dateKey] = [];
            }
            $rawDailyData[$dateKey][] = $jumlah;

            if (!isset($monthlyDataTemp[$monthKey])) {
                $monthlyDataTemp[$monthKey] = 0;
            }
            $monthlyDataTemp[$monthKey] += $jumlah;

            $triwulan = (int) ceil($date->month / 3);
            $triwulanDataTemp[$triwulan] += $jumlah;
        }

        $triwulanData = [];
        for ($i = 1; $i <= 4; $i++) {
            $triwulanData["Triwulan_$i"] = $triwulanDataTemp[$i];
        }

        $progressAbsolute = (float) $totalLead;
        $above = $totalLead >= $targetTahunan ? 1 : 0;
        $below = 1 - $above;

        $baseResponse = $this->formatChartData($progressAbsolute, $targetTahunan, $above, $below, $rawDailyData);

        $correctedMonthlyProgress = [];
        $correctedDailyProgress = [];

        foreach ($monthlyDataTemp as $month => $value) {
            $correctedMonthlyProgress[$month] = (float) number_format($value, 1, '.', '');
        }

        foreach ($baseResponse['daily_breakdown_per_month'] as $month => $days) {
            foreach ($days as $day => $value) {
                $correctedDailyProgress[$month][$day] = (float) number_format($value, 1, '.', '');
            }
        }

        return array_merge($baseResponse, [
            'monthly_progress' => $correctedMonthlyProgress,
            'daily_progress_per_month' => $correctedDailyProgress,
            'dataManual' => [
                'manual_document' => $detail->manual_document ?? null,
            ],
            'triwulan_data' => $triwulanData,
            'total_leads_absolute' => (float) number_format($totalLead, 1, '.', ''),
        ]);
    }
}