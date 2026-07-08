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

        $query = LeadProject::where('status', 'won')
            ->where('tahun_periode', $tahun);

        $totalSales = (float) ($query
            ->select(DB::raw('SUM(lead_projects.estimasi_nilai) as total_sales'))
            ->value('total_sales') ?? 0);

        return round($totalSales);
    }

    public function calculatePendapatanPenjualanProjectDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        // Menggunakan Trait dan menggabungkan dengan key spesifik metode ini
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

        // Perbaikan: Menambahkan logika inisialisasi $kodeKaryawan berdasarkan $personId
        if ($personId !== null) {
            $karyawanData = karyawan::find($personId);
            $kodeKaryawan = $karyawanData ? $karyawanData->kode_karyawan : null;
        }

        $query = LeadProject::where('status', 'won')
            ->where('tahun_periode', $tahun);

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

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dateKey] = (float) number_format($total, 1, '.', '');

            if (!isset($monthlyDataTemp[$monthKey])) {
                $monthlyDataTemp[$monthKey] = 0;
            }
            $monthlyDataTemp[$monthKey] += $total;

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
            $monthlyProgress[$month] = $targetGlobal > 0
                ? (float) number_format(((float)$value / $targetGlobal) * 100, 1, '.', '')
                : 0;
        }

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $day => $value) {
                if (!isset($dailyProgressPerMonth[$month])) {
                    $dailyProgressPerMonth[$month] = [];
                }
                $dailyProgressPerMonth[$month][$day] = $targetGlobal > 0
                    ? (float) number_format(((float)$value / $targetGlobal) * 100, 1, '.', '')
                    : 0;
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
            })
            ->get();

            foreach ($allKaryawan as $karyawanItem) {
                $salesKey = $karyawanItem->kode_karyawan;
                if (!$salesKey) continue;

                $salesRevenue = LeadProject::where('status', 'won')
                    ->where('tahun_periode', $tahun)
                    ->where('sales_id', $salesKey)
                    ->select(DB::raw('SUM(estimasi_nilai) as total'))
                    ->value('total');

                $salesRevenue = (float) ($salesRevenue ?? 0);

                $detailPerson = detailPersonKPI::where('id_target', $itemDetail->id)
                    ->where('id_karyawan', $karyawanItem->id)
                    ->first();

                $presentaseKemampuan = (float) ($detailPerson->presentase_kemampuan ?? 0);
                $idDetailPerson = $detailPerson->id ?? null;

                $percentage = $presentaseKemampuan > 0 ? ($salesRevenue / $presentaseKemampuan) * 100 : 0;

                $allSalesData[] = [
                    'kode_karyawan' => (string) $salesKey,
                    'nama' => (string) ($karyawanItem->nama_lengkap ?? $karyawanItem->nama ?? $salesKey),
                    'revenue' => (float) number_format($salesRevenue, 1, '.', ''),
                    'id_detailPerson' => $idDetailPerson,
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
            $detailPerson = detailPersonKPI::where('id_target', $itemDetail->id)
                ->where('id_karyawan', $personId)
                ->first();

            $presentaseKemampuan = (float) ($detailPerson->presentase_kemampuan ?? 0);
            $idDetailPerson = $detailPerson->id ?? null;

            $percentage = $presentaseKemampuan > 0 ? ($totalSales / $presentaseKemampuan) * 100 : 0;

            $karyawanName = $karyawanData ? ($karyawanData->nama_lengkap ?? $karyawanData->nama ?? '') : '';

            $salesPerformance = [
                'type' => 'individual',
                'data' => [
                    'kode_karyawan' => (string) $kodeKaryawan,
                    'nama' => (string) $karyawanName,
                    'revenue' => (float) number_format($totalSales, 1, '.', ''),
                    'id_detailPerson' => $idDetailPerson,
                    'presentase_kemampuan' => (float) number_format($presentaseKemampuan, 1, '.', ''),
                    'percentage' => (float) number_format($percentage, 1, '.', ''),
                    'status' => $totalSales >= $presentaseKemampuan ? 'achieved' : 'pending'
                ]
            ];
        }

        return [
            'progress' => round($progressGlobal, 1),
            'gap' => round($gap, 1),
            'dataManual' => [
                'manual_document' => $detail->manual_document,
            ],
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
        $target = (int) $detail->nilai_target;

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

            if (!isset($monthlyDataTemp[$monthKey])) {
                $monthlyDataTemp[$monthKey] = 0;
            }
            $monthlyDataTemp[$monthKey] += $jumlah;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
            }
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
            'dataManual' => [
                'manual_document' => $detail->manual_document ?? null,
            ],
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
