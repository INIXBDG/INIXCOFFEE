<?php

namespace App\Services\KPI\Jabatan;

use App\Models\checklistRKM;
use App\Models\LaporanHarianSales;
use App\Models\RKM;
use App\Models\TodoAdministrasi;
use App\Traits\KPIDefaultResponseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ADMSalesKPIService
{
    use KPIDefaultResponseTrait;

    public function calculateLaporanMOM($item, $personId)
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

        $momCount = LaporanHarianSales::whereYear('created_at', $tahun)->count();

        $PACount = checklistRKM::whereYear('created_at', $tahun)->where('PA', '1')->count();
        $SuratKontrakCount = checklistRKM::whereYear('created_at', $tahun)->where('surat_kontrak', '1')->count();

        $rkmBase = RKM::whereYear('tanggal_awal', $tahun);

        $totalDataERegist = (clone $rkmBase)->count();

        $totalDataAboveERegist = (clone $rkmBase)
            ->whereNotNull('registrasi_form')
            ->count();

        $persenCalculationMom = $momCount == 0 ? 100 : 25;
        $persenCalculationERegist = $totalDataERegist == 0 ? 0 : 25;

        $progressMoM = $momCount > 0 ? ($momCount / $momCount) * $persenCalculationERegist : 0;
        $progressPA = $PACount > 0 ? ($PACount / $PACount) * $persenCalculationERegist : 0;
        $progressSuratKontrak = $SuratKontrakCount > 0 ? ($SuratKontrakCount / $SuratKontrakCount) * $persenCalculationERegist : 0;

        if ($progressMoM == 0) {
            $progressMoM = 0;
        }

        $progressERegist = $totalDataERegist > 0
            ? ($totalDataAboveERegist / $totalDataERegist) * $persenCalculationMom
            : 0;

        if ($progressERegist == 0) {
            $progressERegist = 0;
        }

        $progress = $progressMoM + $progressERegist + $progressPA + $progressSuratKontrak;

        if ($progress == 0) {
            return 0;
        }

        return round($progress, 1);
    }

    public function calculateLaporanMOMDetail($itemDetail, $personId = null)
    {
        $details = $itemDetail->detailTargetKPI;

        $firstDetail = $details->first();

        $tahun = (int) optional($firstDetail)->detail_jangka;
        $nilaiTarget = (float) optional($firstDetail)->nilai_target;

        if ($details->isEmpty() || $nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $this->getDefaultDetailResponse();
        }

        $momCount = LaporanHarianSales::whereYear('created_at', $tahun)->count();
        $PACount = checklistRKM::whereYear('created_at', $tahun)->where('PA', '1')->count();
        $SuratKontrakCount = checklistRKM::whereYear('created_at', $tahun)->where('surat_kontrak', '1')->count();

        $rkmBase = RKM::whereYear('tanggal_awal', $tahun);

        $totalDataERegist = (clone $rkmBase)->count();

        $totalDataAboveERegist = (clone $rkmBase)
            ->whereNotNull('registrasi_form')
            ->count();

        $persenCalculationMom = $momCount == 0 ? 100 : 25;
        $persenCalculationERegist = $totalDataERegist == 0 ? 0 : 25;

        $progressMoM = $momCount > 0
            ? ($momCount / $momCount) * $persenCalculationERegist
            : 0;

        $progressSuratKontrak = $SuratKontrakCount > 0
            ? ($SuratKontrakCount / $SuratKontrakCount) * $persenCalculationERegist
            : 0;

        $progressPA = $PACount > 0
            ? ($PACount / $PACount) * $persenCalculationERegist
            : 0;

        $progressERegist = $totalDataERegist > 0
            ? ($totalDataAboveERegist / $totalDataERegist) * $persenCalculationMom
            : 0;

        $progress = $progressMoM + $progressERegist + $progressPA + $progressSuratKontrak;

        $laporans = LaporanHarianSales::whereYear('created_at', $tahun)
            ->select(DB::raw('DATE(created_at) as tanggal, COUNT(*) as total'))
            ->groupBy('tanggal')
            ->get();

        $dailyBreakdownPerMonth = [];
        $monthlyDataTemp = [];

        foreach ($laporans as $row) {
            $date = Carbon::parse($row->tanggal);
            $dateKey = $date->format('Y-m-d');
            $monthKey = $date->format('Y-m');

            $total = (float) $row->total;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dateKey] = $total;

            if (!isset($monthlyDataTemp[$monthKey])) {
                $monthlyDataTemp[$monthKey] = [];
            }
            $monthlyDataTemp[$monthKey][] = $total;
        }

        $monthlyData = [];
        foreach ($monthlyDataTemp as $month => $totals) {
            $count = count($totals);

            $monthlyData[$month] = $count > 0
                ? round(array_sum($totals) / $count, 1)
                : 0;
        }

        ksort($monthlyData);
        ksort($dailyBreakdownPerMonth);

        $monthlyProgress = [];
        foreach ($monthlyData as $month => $value) {
            $monthlyProgress[$month] = $nilaiTarget > 0
                ? round(($value / $nilaiTarget) * 100, 1)
                : 0;
        }

        $dailyProgressPerMonth = [];
        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $date => $value) {
                $dailyProgressPerMonth[$month][$date] = $nilaiTarget > 0
                    ? round(($value / $nilaiTarget) * 100, 1)
                    : 0;
            }
        }

        $pieChart = [
            'above' => $totalDataAboveERegist,
            'below' => max(0, $totalDataERegist - $totalDataAboveERegist),
        ];

        $gap = 0;

        if ($progress > $nilaiTarget) {
            $gap = 0;
        } else {
            $gapRaw = $progress - $nilaiTarget;
            $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');
        }

        return [
            'progress' => round($progress, 1),
            'gap' => $gap,
            'pie_chart' => $pieChart,
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    public function calculateAkurasiKelengkapanDataPenjualan($item, $personId)
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

        $startDate = Carbon::create($tahun, 1, 1)->startOfDay();

        $endDate = ($tahun == now()->year)
            ? now()->endOfDay()
            : Carbon::create($tahun, 12, 31)->endOfDay();

        $rkms = RKM::with(['perhitunganNetSales', 'outstanding', 'peluang'])
                    ->whereBetween('tanggal_awal', [$startDate, $endDate])
                    ->where('status', '0')
                    ->whereNull('deleted_at')
                    ->whereHas('peluang', function ($query) {
                        $query->where('tentatif', 0);
                    })
                    ->orderBy('status', 'asc')
                    ->orderBy('tanggal_awal', 'asc')
                    ->get();

        $totalRkmDenganPerhitungan = 0;
        $totalRkmAkurat = 0;

        foreach ($rkms as $rkm) {
            $listPerhitungan = $rkm->perhitunganNetSales;

            if ($listPerhitungan->isEmpty()) {
                continue;
            }

            $totalRkmDenganPerhitungan++;

            $listOutstanding = $rkm->outstanding;

            if (blank($listOutstanding)) {
                continue;
            }

            $sumKomponen = 0;

            $itemsPerhitungan = $listPerhitungan instanceof \Illuminate\Database\Eloquent\Collection
                ? $listPerhitungan
                : [$listPerhitungan];

            foreach ($itemsPerhitungan as $p) {
                $sumKomponen +=
                    (int)($p->transportasi ?? 0) +
                    (int)($p->akomodasi_peserta ?? 0) +
                    (int)($p->akomodasi_tim ?? 0) +
                    (int)($p->fresh_money ?? 0) +
                    (int)($p->entertaint ?? 0) +
                    (int)($p->souvenir ?? 0) +
                    (int)($p->cashback ?? 0) +
                    (int)($p->sewa_laptop ?? 0);
            }

            $sumOutstanding = 0;
            $itemsOutstanding = $listOutstanding instanceof \Illuminate\Database\Eloquent\Collection
                ? $listOutstanding
                : [$listOutstanding];

            foreach ($itemsOutstanding as $o) {
                $sumOutstanding += (int)($o->net_sales ?? 0);
            }

            if ($sumKomponen === $sumOutstanding) {
                $totalRkmAkurat++;
            }
        }

        if ($totalRkmDenganPerhitungan == 0) {
            return 0.0;
        }

        $persentase = ($totalRkmAkurat / $totalRkmDenganPerhitungan) * 100;

        return round($persentase, 1);
    }

    public function calculateAkurasiKelengkapanDataPenjualanDetail($itemDetail, $personId = null)
    {
        $details = $itemDetail->detailTargetKPI;
        $detail = $details->first();

        $nilaiTarget = (float) optional($detail)->nilai_target;
        $tahun = (int) (optional($detail)->detail_jangka ?? now()->year);

        if ($details->isEmpty() || $tahun < 2000 || $tahun > now()->year + 5) {
            return $this->getDefaultDetailResponse();
        }

        $startDate = Carbon::create($tahun, 1, 1)->startOfDay();

        $endDate = ($tahun == now()->year)
            ? now()->endOfDay()
            : Carbon::create($tahun, 12, 31)->endOfDay();

        $rkms = RKM::with([
                'perhitunganNetSales',
                'outstanding',
                'peluang'
            ])
            ->whereBetween('tanggal_awal', [$startDate, $endDate])
            ->where('status', '0')
            ->whereNull('deleted_at')
            ->whereHas('peluang', function ($query) {
                $query->where('tentatif', 0);
            })
            ->orderBy('status')
            ->orderBy('tanggal_awal')
            ->get();

        $totalRkmDenganPerhitungan = 0;
        $totalRkmAkurat = 0;

        $monthlyTotal = [];
        $monthlyAccurate = [];

        $dailyTotal = [];
        $dailyAccurate = [];

        $dailyBreakdownPerMonth = [];

        foreach ($rkms as $rkm) {

            $listPerhitungan = $rkm->perhitunganNetSales;

            if ($listPerhitungan->isEmpty()) {
                continue;
            }

            $totalRkmDenganPerhitungan++;

            $date = Carbon::parse($rkm->tanggal_awal);
            $monthKey = $date->format('Y-m');
            $dateKey = $date->format('Y-m-d');

            $monthlyTotal[$monthKey] = ($monthlyTotal[$monthKey] ?? 0) + 1;
            $dailyTotal[$monthKey][$dateKey] = ($dailyTotal[$monthKey][$dateKey] ?? 0) + 1;

            $listOutstanding = $rkm->outstanding;

            if (blank($listOutstanding)) {
                continue;
            }

            $sumKomponen = 0;

            foreach ($listPerhitungan as $p) {
                $sumKomponen +=
                    (int) ($p->transportasi ?? 0) +
                    (int) ($p->akomodasi_peserta ?? 0) +
                    (int) ($p->akomodasi_tim ?? 0) +
                    (int) ($p->fresh_money ?? 0) +
                    (int) ($p->entertaint ?? 0) +
                    (int) ($p->souvenir ?? 0) +
                    (int) ($p->cashback ?? 0) +
                    (int) ($p->sewa_laptop ?? 0);
            }

            $sumOutstanding = 0;

            foreach ($listOutstanding as $o) {
                $sumOutstanding += (int) ($o->net_sales ?? 0);
            }

            if ($sumKomponen === $sumOutstanding) {

                $totalRkmAkurat++;

                $monthlyAccurate[$monthKey] = ($monthlyAccurate[$monthKey] ?? 0) + 1;
                $dailyAccurate[$monthKey][$dateKey] = ($dailyAccurate[$monthKey][$dateKey] ?? 0) + 1;
                $dailyBreakdownPerMonth[$monthKey][$dateKey] = ($dailyBreakdownPerMonth[$monthKey][$dateKey] ?? 0) + 1;
            }
        }

        $progress = $totalRkmDenganPerhitungan > 0
            ? round(($totalRkmAkurat / $totalRkmDenganPerhitungan) * 100, 1)
            : 0;

        $monthlyProgress = [];

        foreach ($monthlyTotal as $month => $total) {
            $accurate = $monthlyAccurate[$month] ?? 0;
            $monthlyProgress[$month] = $total > 0
                ? round(($accurate / $total) * 100, 1)
                : 0;
        }

        $dailyProgressPerMonth = [];

        foreach ($dailyTotal as $month => $days) {
            foreach ($days as $date => $total) {
                $accurate = $dailyAccurate[$month][$date] ?? 0;
                $dailyProgressPerMonth[$month][$date] = $total > 0
                    ? round(($accurate / $total) * 100, 1)
                    : 0;
            }
        }

        ksort($monthlyAccurate);
        ksort($monthlyProgress);
        ksort($dailyBreakdownPerMonth);
        ksort($dailyProgressPerMonth);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $totalRkmAkurat,
                'below' => max(0, $totalRkmDenganPerhitungan - $totalRkmAkurat),
            ],
            'monthly_data' => $monthlyAccurate,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    public function calculateTodoAdministrasi($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail) {
            return 0.0;
        }

        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return 0.0;
        }

        $momCount = TodoAdministrasi::whereYear('created_at', $tahun)->count();

        if ($momCount == 0) {
            return 0;
        }

        $momDone = TodoAdministrasi::whereYear('created_at', $tahun)
            ->where('status', 'selesai')
            ->whereNotNull('solusi')
            ->count();

        $progress = ($momDone / $momCount) * 100;

        return round($progress, 1);
    }

    public function calculateTodoAdministrasiDetail($itemDetail, $personId = null)
    {
        $details = $itemDetail->detailTargetKPI;

        $tahun = (int) optional($details->first())->detail_jangka;
        $nilaiTarget = (float) optional($details->first())->nilai_target;

        if ($details->isEmpty() || $tahun < 2000 || $tahun > now()->year + 5) {
            return $this->getDefaultDetailResponse();
        }

        $todos = TodoAdministrasi::whereYear('created_at', $tahun)->get();

        if ($todos->isEmpty()) {
            return $this->getDefaultDetailResponse();
        }

        $totalData = $todos->count();

        $totalDone = $todos->where('status', 'selesai')
            ->whereNotNull('solusi')
            ->count();

        $totalNotDone = $totalData - $totalDone;

        $progress = $totalData > 0 ? ($totalDone / $totalData) * 100 : 0;
        $progress = round($progress, 1);

        $dailyBreakdownPerMonth = [];
        $monthlyDataTemp = [];

        foreach ($todos as $todo) {
            $date = \Carbon\Carbon::parse($todo->created_at);
            $dateKey = $date->format('Y-m-d');
            $monthKey = $date->format('Y-m');

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
            }

            if (!isset($dailyBreakdownPerMonth[$monthKey][$dateKey])) {
                $dailyBreakdownPerMonth[$monthKey][$dateKey] = 0;
            }

            $dailyBreakdownPerMonth[$monthKey][$dateKey]++;

            if (!isset($monthlyDataTemp[$monthKey])) {
                $monthlyDataTemp[$monthKey] = [];
            }

            $monthlyDataTemp[$monthKey][] = $dailyBreakdownPerMonth[$monthKey][$dateKey];
        }

        $monthlyData = [];
        foreach ($monthlyDataTemp as $month => $values) {
            $monthlyData[$month] = round(array_sum($values) / count($values), 1);
        }

        ksort($monthlyData);
        ksort($dailyBreakdownPerMonth);

        $monthlyProgress = [];
        foreach ($monthlyData as $month => $value) {
            $monthlyProgress[$month] = $nilaiTarget > 0
                ? round(($value / $nilaiTarget) * 100, 1)
                : 0;
        }

        $dailyProgressPerMonth = [];
        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $date => $value) {
                $dailyProgressPerMonth[$month][$date] = $nilaiTarget > 0
                    ? round(($value / $nilaiTarget) * 100, 1)
                    : 0;
            }
        }

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $pieChart = [
            'above' => $totalDone,
            'below' => $totalNotDone,
        ];

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => $pieChart,
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }
}