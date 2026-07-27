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

    public function calculateLaporanMOM($item, $personId = null)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return 0.0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) return 0.0;

        $momCount = LaporanHarianSales::whereYear('created_at', $tahun)
            ->when($personId, fn($q) => $q->where('person_id', $personId))->count();
            
        $paCount = checklistRKM::whereYear('created_at', $tahun)->where('PA', '1')
            ->when($personId, fn($q) => $q->where('person_id', $personId))->count();
            
        $suratCount = checklistRKM::whereYear('created_at', $tahun)->where('surat_kontrak', '1')
            ->when($personId, fn($q) => $q->where('person_id', $personId))->count();

        $eregistStats = RKM::selectRaw("COUNT(*) as total, SUM(CASE WHEN registrasi_form IS NOT NULL THEN 1 ELSE 0 END) as above")
            ->whereYear('tanggal_awal', $tahun)
            ->when($personId, fn($q) => $q->where('person_id', $personId))
            ->first();

        $totalERegist = $eregistStats->total ?? 0;
        $aboveERegist = $eregistStats->above ?? 0;

        $scoreMom = ($momCount > 0) ? 25.0 : 0.0;
        $scorePa = ($paCount > 0) ? 25.0 : 0.0;
        $scoreSurat = ($suratCount > 0) ? 25.0 : 0.0;
        $scoreERegist = ($totalERegist > 0) ? ($aboveERegist / $totalERegist) * 25.0 : 0.0;

        return round($scoreMom + $scorePa + $scoreSurat + $scoreERegist, 1);
    }

    public function calculateLaporanMOMDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $this->getDefaultDetailResponse();
        }

        $tahun = (int) $detail->detail_jangka;
        $nilaiTarget = (float) $detail->nilai_target;

        if ($tahun < 2000 || $tahun > now()->year + 5 || $nilaiTarget <= 0) {
            return $this->getDefaultDetailResponse();
        }

        $monthlyMom = LaporanHarianSales::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
            ->whereYear('created_at', $tahun)
            ->when($personId, fn($q) => $q->where('person_id', $personId))
            ->groupBy('month')->pluck('count', 'month');
            
        $monthlyPa = checklistRKM::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
            ->whereYear('created_at', $tahun)->where('PA', '1')
            ->when($personId, fn($q) => $q->where('person_id', $personId))
            ->groupBy('month')->pluck('count', 'month');
            
        $monthlySurat = checklistRKM::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
            ->whereYear('created_at', $tahun)->where('surat_kontrak', '1')
            ->when($personId, fn($q) => $q->where('person_id', $personId))
            ->groupBy('month')->pluck('count', 'month');

        $monthlyTotalERegist = RKM::selectRaw("DATE_FORMAT(tanggal_awal, '%Y-%m') as month, COUNT(*) as count")
            ->whereYear('tanggal_awal', $tahun)
            ->when($personId, fn($q) => $q->where('person_id', $personId))
            ->groupBy('month')->pluck('count', 'month');
            
        $monthlyAboveERegist = RKM::selectRaw("DATE_FORMAT(tanggal_awal, '%Y-%m') as month, COUNT(*) as count")
            ->whereYear('tanggal_awal', $tahun)->whereNotNull('registrasi_form')
            ->when($personId, fn($q) => $q->where('person_id', $personId))
            ->groupBy('month')->pluck('count', 'month');

        $momCount = array_sum($monthlyMom->toArray());
        $paCount = array_sum($monthlyPa->toArray());
        $suratCount = array_sum($monthlySurat->toArray());
        $totalERegist = array_sum($monthlyTotalERegist->toArray());
        $aboveERegist = array_sum($monthlyAboveERegist->toArray());

        $useBinaryScoring = true; 

        $scoreMom = $useBinaryScoring ? ($momCount > 0 ? 25.0 : 0.0) : min(25.0, ($momCount / max(1, $momCount)) * 25.0);
        $scorePa = $useBinaryScoring ? ($paCount > 0 ? 25.0 : 0.0) : 25.0;
        $scoreSurat = $useBinaryScoring ? ($suratCount > 0 ? 25.0 : 0.0) : 25.0;
        $scoreERegist = ($totalERegist > 0) ? ($aboveERegist / $totalERegist) * 25.0 : 0.0;

        $progress = round($scoreMom + $scorePa + $scoreSurat + $scoreERegist, 1);
        $gap = $this->calculateAndFormatGap($progress, $nilaiTarget);

        $allMonths = collect($monthlyMom->keys())
            ->merge($monthlyPa->keys())
            ->merge($monthlySurat->keys())
            ->merge($monthlyTotalERegist->keys())
            ->unique()->sort()->values();
        
        $monthlyData = [];
        $monthlyProgress = [];
        $dailyBreakdownPerMonth = [];
        $dailyProgressPerMonth = [];

        $dailyLaporan = LaporanHarianSales::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, DATE_FORMAT(created_at, '%Y-%m-%d') as day, COUNT(*) as count")
            ->whereYear('created_at', $tahun)
            ->when($personId, fn($q) => $q->where('person_id', $personId))
            ->groupBy('month', 'day')->get();

        foreach ($dailyLaporan as $row) {
            $dailyBreakdownPerMonth[$row->month][$row->day] = (int) $row->count;
            $dailyProgressPerMonth[$row->month][$row->day] = $nilaiTarget > 0 ? round(((int) $row->count / $nilaiTarget) * 100, 1) : 0.0;
        }

        $aboveTargetMonths = 0;
        $belowTargetMonths = 0;

        foreach ($allMonths as $month) {
            $mMom = $monthlyMom[$month] ?? 0;
            $mPa = $monthlyPa[$month] ?? 0;
            $mSurat = $monthlySurat[$month] ?? 0;
            $mTotalE = $monthlyTotalERegist[$month] ?? 0;
            $mAboveE = $monthlyAboveERegist[$month] ?? 0;

            $mScoreMom = $useBinaryScoring ? ($mMom > 0 ? 25.0 : 0.0) : 0.0;
            $mScorePa = $useBinaryScoring ? ($mPa > 0 ? 25.0 : 0.0) : 0.0;
            $mScoreSurat = $useBinaryScoring ? ($mSurat > 0 ? 25.0 : 0.0) : 0.0;
            $mScoreE = ($mTotalE > 0) ? ($mAboveE / $mTotalE) * 25.0 : 0.0;

            $monthProgress = round($mScoreMom + $mScorePa + $mScoreSurat + $mScoreE, 1);
            
            $monthlyData[$month] = $monthProgress;
            $monthlyProgress[$month] = $monthProgress;

            if ($monthProgress >= $nilaiTarget) {
                $aboveTargetMonths++;
            } else {
                $belowTargetMonths++;
            }
        }

        ksort($monthlyData);
        ksort($monthlyProgress);
        ksort($dailyBreakdownPerMonth);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $aboveTargetMonths,
                'below' => $belowTargetMonths,
            ],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    public function calculateAkurasiKelengkapanDataPenjualan($item, $personId = null)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return 0.0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) return 0.0;

        $startDate = Carbon::create($tahun, 1, 1)->startOfDay();
        $endDate = ($tahun == now()->year) ? now()->endOfDay() : Carbon::create($tahun, 12, 31)->endOfDay();

        $sumKomponenSQL = "COALESCE(transportasi,0) + COALESCE(akomodasi_peserta,0) + COALESCE(akomodasi_tim,0) + COALESCE(fresh_money,0) + COALESCE(entertaint,0) + COALESCE(souvenir,0) + COALESCE(cashback,0) + COALESCE(sewa_laptop,0)";

        $totalWithPerhitungan = RKM::whereBetween('tanggal_awal', [$startDate, $endDate])
            ->where('status', '0')
            ->whereNull('deleted_at')
            ->whereHas('peluang', fn($q) => $q->where('tentatif', 0))
            ->whereHas('perhitunganNetSales')
            ->when($personId, fn($q) => $q->where('person_id', $personId))
            ->count();

        if ($totalWithPerhitungan === 0) return 0.0;

        $totalAkurat = RKM::whereBetween('rkms.tanggal_awal', [$startDate, $endDate])
            ->where('rkms.status', '0')
            ->whereNull('rkms.deleted_at')
            ->whereHas('peluang', fn($q) => $q->where('tentatif', 0))
            ->whereHas('perhitunganNetSales')
            ->whereHas('outstanding')
            ->when($personId, fn($q) => $q->where('rkms.person_id', $personId))
            ->whereRaw("
                (SELECT SUM({$sumKomponenSQL}) FROM perhitungan_net_sales WHERE rkm_id = rkms.id) > 0
                AND
                (SELECT SUM({$sumKomponenSQL}) FROM perhitungan_net_sales WHERE rkm_id = rkms.id) = 
                (SELECT COALESCE(SUM(net_sales), 0) FROM outstandings WHERE rkm_id = rkms.id)
            ")
            ->count();

        return round(($totalAkurat / $totalWithPerhitungan) * 100, 1);
    }

    public function calculateAkurasiKelengkapanDataPenjualanDetail($itemDetail, $personId = null)
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

        $startDate = Carbon::create($tahun, 1, 1)->startOfDay();
        $endDate = ($tahun == now()->year) ? now()->endOfDay() : Carbon::create($tahun, 12, 31)->endOfDay();

        $totalRkmDenganPerhitungan = 0;
        $totalRkmAkurat = 0;

        $monthlyTotal = [];
        $monthlyAccurate = [];
        $dailyTotal = [];
        $dailyAccurate = [];
        $dailyBreakdownPerMonth = [];

        $rkms = RKM::with(['perhitunganNetSales', 'outstanding'])
            ->whereBetween('tanggal_awal', [$startDate, $endDate])
            ->where('status', '0')
            ->whereNull('deleted_at')
            ->whereHas('peluang', fn($q) => $q->where('tentatif', 0))
            ->when($personId, fn($q) => $q->where('person_id', $personId))
            ->orderBy('tanggal_awal')
            ->cursor();

        foreach ($rkms as $rkm) {
            if ($rkm->perhitunganNetSales->isEmpty()) {
                continue;
            }

            $totalRkmDenganPerhitungan++;
            $date = Carbon::parse($rkm->tanggal_awal);
            $monthKey = $date->format('Y-m');
            $dateKey = $date->format('Y-m-d');

            $monthlyTotal[$monthKey] = ($monthlyTotal[$monthKey] ?? 0) + 1;
            $dailyTotal[$monthKey][$dateKey] = ($dailyTotal[$monthKey][$dateKey] ?? 0) + 1;

            if ($rkm->outstanding instanceof \Illuminate\Database\Eloquent\Collection) {
                if ($rkm->outstanding->isEmpty()) continue;
                $sumOutstanding = $rkm->outstanding->sum('net_sales');
            } else {
                if (!$rkm->outstanding) continue;
                $sumOutstanding = (int)($rkm->outstanding->net_sales ?? 0);
            }

            $sumKomponen = $rkm->perhitunganNetSales->sum(fn($p) => 
                (int)($p->transportasi ?? 0) + (int)($p->akomodasi_peserta ?? 0) +
                (int)($p->akomodasi_tim ?? 0) + (int)($p->fresh_money ?? 0) +
                (int)($p->entertaint ?? 0) + (int)($p->souvenir ?? 0) +
                (int)($p->cashback ?? 0) + (int)($p->sewa_laptop ?? 0)
            );

            if ($sumKomponen > 0 && $sumKomponen === $sumOutstanding) {
                $totalRkmAkurat++;
                $monthlyAccurate[$monthKey] = ($monthlyAccurate[$monthKey] ?? 0) + 1;
                $dailyAccurate[$monthKey][$dateKey] = ($dailyAccurate[$monthKey][$dateKey] ?? 0) + 1;
                $dailyBreakdownPerMonth[$monthKey][$dateKey] = ($dailyBreakdownPerMonth[$monthKey][$dateKey] ?? 0) + 1;
            }
        }

        $progress = $totalRkmDenganPerhitungan > 0 ? round(($totalRkmAkurat / $totalRkmDenganPerhitungan) * 100, 1) : 0.0;
        $gap = $this->calculateAndFormatGap($progress, $nilaiTarget);

        $monthlyProgress = [];
        foreach ($monthlyTotal as $month => $total) {
            $accurate = $monthlyAccurate[$month] ?? 0;
            $monthlyProgress[$month] = $total > 0 ? round(($accurate / $total) * 100, 1) : 0.0;
        }

        $dailyProgressPerMonth = [];
        foreach ($dailyTotal as $month => $days) {
            foreach ($days as $date => $total) {
                $accurate = $dailyAccurate[$month][$date] ?? 0;
                $dailyProgressPerMonth[$month][$date] = $total > 0 ? round(($accurate / $total) * 100, 1) : 0.0;
            }
        }

        ksort($monthlyProgress);
        ksort($dailyBreakdownPerMonth);
        ksort($dailyProgressPerMonth);

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

    public function calculateTodoAdministrasi($item, $personId = null)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return 0.0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) return 0.0;

        $stats = TodoAdministrasi::selectRaw("
            COUNT(*) as total, 
            SUM(CASE WHEN status = 'selesai' AND solusi IS NOT NULL THEN 1 ELSE 0 END) as done
        ")
            ->whereYear('created_at', $tahun)
            ->when($personId, fn($q) => $q->where('person_id', $personId))
            ->first();

        $totalData = $stats->total ?? 0;
        $totalDone = $stats->done ?? 0;

        if ($totalData === 0) return 0.0;

        return round(($totalDone / $totalData) * 100, 1);
    }

    public function calculateTodoAdministrasiDetail($itemDetail, $personId = null)
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

        $monthlyStats = TodoAdministrasi::selectRaw(
            "DATE_FORMAT(created_at, '%Y-%m') as month, 
             COUNT(*) as total, 
             SUM(CASE WHEN status = 'selesai' AND solusi IS NOT NULL THEN 1 ELSE 0 END) as done"
        )
            ->whereYear('created_at', $tahun)
            ->when($personId, fn($q) => $q->where('person_id', $personId))
            ->groupBy('month')
            ->get();

        if ($monthlyStats->isEmpty()) {
            return $this->getDefaultDetailResponse();
        }

        $totalData = $monthlyStats->sum('total');
        $totalDone = $monthlyStats->sum('done');
        $totalNotDone = $totalData - $totalDone;

        $progress = $totalData > 0 ? round(($totalDone / $totalData) * 100, 1) : 0.0;
        $gap = $this->calculateAndFormatGap($progress, $nilaiTarget);

        $dailyStatsAll = TodoAdministrasi::selectRaw(
            "DATE_FORMAT(created_at, '%Y-%m') as month, 
             DATE_FORMAT(created_at, '%Y-%m-%d') as day, 
             COUNT(*) as total, 
             SUM(CASE WHEN status = 'selesai' AND solusi IS NOT NULL THEN 1 ELSE 0 END) as done"
        )
            ->whereYear('created_at', $tahun)
            ->when($personId, fn($q) => $q->where('person_id', $personId))
            ->groupBy('month', 'day')
            ->get();

        $monthlyData = [];
        $monthlyProgress = [];
        $dailyBreakdownPerMonth = [];
        $dailyProgressPerMonth = [];

        foreach ($monthlyStats as $stat) {
            $month = $stat->month;
            $monthlyData[$month] = (int) $stat->done;
            $monthlyProgress[$month] = $stat->total > 0 ? round(($stat->done / $stat->total) * 100, 1) : 0.0;
            
            $dailyBreakdownPerMonth[$month] = [];
            $dailyProgressPerMonth[$month] = [];
        }

        foreach ($dailyStatsAll as $dStat) {
            $month = $dStat->month;
            $day = $dStat->day;
            
            $dailyBreakdownPerMonth[$month][$day] = (int) $dStat->done;
            $dailyProgressPerMonth[$month][$day] = $dStat->total > 0 
                ? round(($dStat->done / $dStat->total) * 100, 1) 
                : 0.0;
        }

        foreach ($dailyBreakdownPerMonth as $month => $days) {
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
                'above' => $totalDone,
                'below' => max(0, $totalNotDone),
            ],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }
}