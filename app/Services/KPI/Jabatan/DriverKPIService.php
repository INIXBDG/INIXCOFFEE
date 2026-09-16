<?php

namespace App\Services\KPI\Jabatan;

use App\Models\BiayaTransportasiDriver;
use App\Models\perbaikanKendaraan;
use App\Models\pickupDriver;
use App\Models\KondisiKendaraan;
use App\Models\HariLibur;
use App\Models\Nilaifeedback;
use App\Services\OperationalBudgetCalculator;
use App\Traits\KPIDefaultResponseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DriverKPIService
{
    use KPIDefaultResponseTrait;

    public function calculatePerbaikanKendaraan($item, $personId)
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

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $totalQuery = perbaikanKendaraan::whereBetween('created_at', [$start, $end]);
        $selesaiQuery = perbaikanKendaraan::whereBetween('created_at', [$start, $end])
            ->where('status', 'Selesai');

        if ($personId !== null) {
            $totalQuery->where('id_user', $personId);
            $selesaiQuery->where('id_user', $personId);
        }

        $totalData = $totalQuery->count();
        $dataDiperbaiki = $selesaiQuery->count();

        if ($totalData <= 0) {
            return 0;
        }

        return round(($dataDiperbaiki / $totalData) * 100, 1);
    }

    public function calculatePerbaikanKendaraanDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (is_null($detail) || is_null($detail->nilai_target) || is_null($detail->detail_jangka)) {
            return $this->getDefaultDetailResponse();
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $this->getDefaultDetailResponse();
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $query = perbaikanKendaraan::whereBetween('created_at', [$start, $end]);
        
        if ($personId !== null) {
            $query->where('id_user', $personId);
        }

        $allRepairs = $query->get();
        $totalData = $allRepairs->count();

        if ($totalData == 0) {
            return $this->getDefaultDetailResponse();
        }

        $dataDiperbaiki = $allRepairs->where('status', 'Selesai')->count();
        $dataBelumDiperbaiki = $totalData - $dataDiperbaiki;

        $progress = round(($dataDiperbaiki / $totalData) * 100, 1);
        $gapRaw = $progress - $nilaiTarget;
        $gap = $progress > $nilaiTarget ? 0 : rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $dailyValues = [];

        foreach ($allRepairs as $repair) {
            $tanggal = Carbon::parse($repair->created_at);
            $dateKey = $tanggal->format('Y-m-d');
            $nilaiItem = $repair->status === 'Selesai' ? 100 : 0;

            if (!isset($dailyValues[$dateKey])) {
                $dailyValues[$dateKey] = [];
            }
            $dailyValues[$dateKey][] = $nilaiItem;
        }

        $dailyAverages = [];
        foreach ($dailyValues as $dateStr => $values) {
            $dailyAverages[$dateStr] = round(array_sum($values) / count($values), 1);
        }

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($dailyAverages as $dateStr => $avg) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $monthlyData[$monthKey][] = $avg;
            $monthlyProgress[$monthKey][] = $avg;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $avg;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $avg;
        }

        $monthlyAverages = [];
        $monthlyProgressAverages = [];
        
        foreach ($monthlyData as $month => $dailyVals) {
            $monthlyAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
        }
        foreach ($monthlyProgress as $month => $dailyVals) {
            $monthlyProgressAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAverages);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $dataDiperbaiki, 'below' => $dataBelumDiperbaiki],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    public function calculateKontrolPengeluaranTransportasi($item, $personId)
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

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $query = BiayaTransportasiDriver::whereBetween('created_at', [$start, $end]);

        if ($personId !== null) {
            $query->where('id_karyawan', $personId);
        }

        $items = $query->with(['pickupDriver', 'SPJ'])->get();

        if ($items->isEmpty()) {
            return 100.0;
        }

        $weeklyGroups = $items->groupBy(function ($item) {
            return Carbon::parse($item->created_at)->startOfWeek()->format('Y-m-d');
        });

        $totalWeeks = $weeklyGroups->count();
        $countAman = 0;
        $sources = ['driver', 'spj', 'outside'];

        foreach ($weeklyGroups as $weekStart => $weekItems) {
            $summary = OperationalBudgetCalculator::weeklySummary($weekItems, $weekStart, $sources);
            
            if (($summary['sisa_budget'] ?? 0) >= 0) {
                $countAman++;
            }
        }

        return $totalWeeks > 0 ? round(($countAman / $totalWeeks) * 100, 1) : 100.0;
    }

    public function calculateKontrolPengeluaranTransportasiDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || is_null($detail->nilai_target) || is_null($detail->detail_jangka)) {
            return $this->getDefaultDetailResponse();
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $this->getDefaultDetailResponse();
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $query = BiayaTransportasiDriver::whereBetween('created_at', [$start, $end]);

        if ($personId !== null) {
            $query->where('id_karyawan', $personId);
        }

        $items = $query->with(['pickupDriver', 'SPJ'])->get();

        if ($items->isEmpty()) {
            return [
                'progress' => 100.0,
                'gap' => '0',
                'pie_chart' => ['above' => 1, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => []
            ];
        }

        $weeklyGroups = $items->groupBy(function ($item) {
            return Carbon::parse($item->created_at)->startOfWeek()->format('Y-m-d');
        });

        $sources = ['driver', 'spj', 'outside'];
        $totalWeeks = $weeklyGroups->count();
        $countAman = 0;
        
        $dailyProgress = [];

        foreach ($weeklyGroups as $weekStart => $weekItems) {
            $summary = OperationalBudgetCalculator::weeklySummary($weekItems, $weekStart, $sources);
            
            $sisaBudget = $summary['sisa_budget'] ?? 0;
            $totalBudget = ($summary['budget_awal'] ?? 0) + ($summary['total_tambahan'] ?? 0);
            $totalTerpakai = $summary['total_terpakai'] ?? 0;

            $weekProgress = ($totalTerpakai > 0 && $totalBudget > 0) 
                ? min(100.0, ($totalBudget / $totalTerpakai) * 100) 
                : 100.0;

            if ($sisaBudget >= 0) {
                $countAman++;
            }

            $currentDate = Carbon::parse($weekStart);
            $endDateOfWeek = $currentDate->copy()->endOfWeek();
            
            while ($currentDate->lte($endDateOfWeek)) {
                if ($currentDate->year == $tahun) {
                    $dateKey = $currentDate->format('Y-m-d');
                    $dailyProgress[$dateKey] = round($weekProgress, 1);
                }
                $currentDate->addDay();
            }
        }

        $progress = $totalWeeks > 0 ? round(($countAman / $totalWeeks) * 100, 1) : 100.0;
        $gapRaw = $progress - $nilaiTarget;
        $gap = $progress > $nilaiTarget ? 0 : rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        
        foreach ($dailyProgress as $dateStr => $prog) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $monthlyData[$monthKey][] = $prog;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $prog;
        }

        $monthlyAverages = [];
        foreach ($monthlyData as $month => $dailyVals) {
            $monthlyAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $countAman, 
                'below' => max(0, $totalWeeks - $countAman)
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyAverages,
            'daily_progress_per_month' => $dailyBreakdownPerMonth,
        ];
    }

    public function calculateReportKondisiKendaraan($item, $personId)
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

        // $response = Http::get("https://libur.deno.dev/api", ['year' => $tahun]);
        // if ($response->successful()) {
        //     foreach ($response->json() as $libur) {
        //         HariLibur::updateOrCreate(
        //             ['tanggal' => $libur['date']],
        //             ['nama' => $libur['name'], 'year' => $tahun]
        //         );
        //     }
        // }

        $startPeriode = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $endPeriode = Carbon::createFromDate($tahun, 12, 31)->endOfDay();
        $hariIni = now()->startOfDay();

        if ($hariIni > $endPeriode) {
            $hariIni = $endPeriode;
        }

        $hariLibur = HariLibur::where('year', $tahun)
            ->pluck('tanggal')
            ->map(function ($d) {
                return Carbon::parse($d)->toDateString();
            })
            ->toArray();

        $query = KondisiKendaraan::whereBetween('tanggal_pemeriksaan', [$startPeriode, $hariIni])
            ->whereNotNull('tanggal_pemeriksaan');

        if ($personId !== null) {
            $query->where('user_id', $personId);
        }

        $firstReport = $query->get()
            ->filter(function ($item) use ($hariLibur) {
                return !in_array(Carbon::parse($item->tanggal_pemeriksaan)->toDateString(), $hariLibur);
            })
            ->sortBy('tanggal_pemeriksaan')
            ->first();

        if (!$firstReport) {
            return 0;
        }

        $startMinggu = Carbon::parse($firstReport->tanggal_pemeriksaan)->startOfWeek(Carbon::MONDAY);
        $today = Carbon::now();
        $dayOfWeek = $today->dayOfWeek;

        $checkUntil = $dayOfWeek < 6 ? $today->copy()->subWeek()->endOfWeek(Carbon::SUNDAY) : $today->endOfDay();

        if ($checkUntil > $endPeriode) {
            $checkUntil = $endPeriode;
        }

        $totalMinggu = max(1, ceil($startMinggu->diffInDays($checkUntil) / 7));

        $allReportsQuery = KondisiKendaraan::whereBetween('tanggal_pemeriksaan', [$startMinggu, $checkUntil])
            ->whereNotNull('tanggal_pemeriksaan');

        if ($personId !== null) {
            $allReportsQuery->where('user_id', $personId);
        }

        $allReports = $allReportsQuery->get()
            ->filter(function ($item) use ($hariLibur) {
                return !in_array(Carbon::parse($item->tanggal_pemeriksaan)->toDateString(), $hariLibur);
            });

        $jumlahReportTepat = 0;

        for ($i = 0; $i < $totalMinggu; $i++) {
            $weekStart = $startMinggu->copy()->addWeeks($i)->startOfWeek(Carbon::MONDAY);
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

            if ($weekEnd > $checkUntil) {
                $weekEnd = $checkUntil;
            }

            $hasReport = $allReports->contains(function ($item) use ($weekStart, $weekEnd) {
                $tanggal = Carbon::parse($item->tanggal_pemeriksaan);
                return $tanggal->between($weekStart, $weekEnd);
            });

            if ($hasReport) {
                $jumlahReportTepat++;
            }
        }

        return round(($jumlahReportTepat / $totalMinggu) * 100, 1);
    }

    public function calculateReportKondisiKendaraanDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || is_null($detail->nilai_target) || is_null($detail->detail_jangka)) {
            return $this->getDefaultDetailResponse();
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $this->getDefaultDetailResponse();
        }

        // $response = Http::get("https://libur.deno.dev/api", ['year' => $tahun]);
        // if ($response->successful()) {
        //     foreach ($response->json() as $libur) {
        //         HariLibur::updateOrCreate(
        //             ['tanggal' => $libur['date']],
        //             ['nama' => $libur['name'], 'year' => $tahun]
        //         );
        //     }
        // }

        $startPeriode = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $endPeriode = Carbon::createFromDate($tahun, 12, 31)->endOfDay();
        $hariIni = now()->startOfDay();

        if ($hariIni > $endPeriode) {
            $hariIni = $endPeriode;
        }

        $hariLibur = HariLibur::where('year', $tahun)
            ->pluck('tanggal')
            ->map(function ($d) {
                return Carbon::parse($d)->toDateString();
            })
            ->toArray();

        $query = KondisiKendaraan::whereBetween('tanggal_pemeriksaan', [$startPeriode, $hariIni])
            ->whereNotNull('tanggal_pemeriksaan');

        if ($personId !== null) {
            $query->where('user_id', $personId);
        }

        $firstReport = $query->get()
            ->filter(function ($item) use ($hariLibur) {
                return !in_array(Carbon::parse($item->tanggal_pemeriksaan)->toDateString(), $hariLibur);
            })
            ->sortBy('tanggal_pemeriksaan')
            ->first();

        if (!$firstReport) {
            return $this->getDefaultDetailResponse();
        }

        $startMinggu = Carbon::parse($firstReport->tanggal_pemeriksaan)->startOfWeek(Carbon::MONDAY);
        $today = Carbon::now();
        $dayOfWeek = $today->dayOfWeek;

        $checkUntil = $dayOfWeek < 6 ? $today->copy()->subWeek()->endOfWeek(Carbon::SUNDAY) : $today->endOfDay();

        if ($checkUntil > $endPeriode) {
            $checkUntil = $endPeriode;
        }

        $totalMinggu = max(1, ceil($startMinggu->diffInDays($checkUntil) / 7));

        $allReportsQuery = KondisiKendaraan::whereBetween('tanggal_pemeriksaan', [$startMinggu, $checkUntil])
            ->whereNotNull('tanggal_pemeriksaan');

        if ($personId !== null) {
            $allReportsQuery->where('user_id', $personId);
        }

        $allReports = $allReportsQuery->get()
            ->filter(function ($item) use ($hariLibur) {
                return !in_array(Carbon::parse($item->tanggal_pemeriksaan)->toDateString(), $hariLibur);
            });

        $jumlahReportTepat = 0;
        $jumlahReportTidakTepat = 0;
        $weeklyData = [];

        for ($i = 0; $i < $totalMinggu; $i++) {
            $weekStart = $startMinggu->copy()->addWeeks($i)->startOfWeek(Carbon::MONDAY);
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

            if ($weekEnd > $checkUntil) {
                $weekEnd = $checkUntil;
            }

            $hasReport = $allReports->contains(function ($item) use ($weekStart, $weekEnd) {
                $tanggal = Carbon::parse($item->tanggal_pemeriksaan);
                return $tanggal->between($weekStart, $weekEnd);
            });

            if ($hasReport) {
                $jumlahReportTepat++;
                $weekValue = 100;
            } else {
                $jumlahReportTidakTepat++;
                $weekValue = 0;
            }

            $weeklyData[] = [
                'start' => $weekStart->copy(),
                'end' => $weekEnd->copy(),
                'value' => $weekValue,
            ];
        }

        $progress = round(($jumlahReportTepat / $totalMinggu) * 100, 1);
        $gapRaw = $progress - $nilaiTarget;
        $gap = $progress > $nilaiTarget ? 0 : rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $dailyValues = [];
        foreach ($weeklyData as $week) {
            $currentDate = $week['start']->copy();
            while ($currentDate <= $week['end']) {
                $dateKey = $currentDate->format('Y-m-d');
                if (!isset($dailyValues[$dateKey])) {
                    $dailyValues[$dateKey] = [];
                }
                $dailyValues[$dateKey][] = $week['value'];
                $currentDate->addDay();
            }
        }

        $dailyAverages = [];
        foreach ($dailyValues as $dateStr => $values) {
            $dailyAverages[$dateStr] = round(array_sum($values) / count($values), 1);
        }

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($dailyAverages as $dateStr => $avg) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $monthlyData[$monthKey][] = $avg;
            $monthlyProgress[$monthKey][] = $avg;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $avg;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $avg;
        }

        $monthlyAverages = [];
        $monthlyProgressAverages = [];
        
        foreach ($monthlyData as $month => $dailyVals) {
            $monthlyAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
        }
        foreach ($monthlyProgress as $month => $dailyVals) {
            $monthlyProgressAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAverages);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $jumlahReportTepat, 'below' => $jumlahReportTidakTepat],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    public function calculateFeedbackKenyamananBerkendara($item, $personId = null)
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

        $start = Carbon::create($tahun, 1, 1)->startOfDay();
        $end = ($tahun == now()->year) ? now()->endOfDay() : Carbon::create($tahun, 12, 31)->endOfDay();

        $query = Nilaifeedback::select('P8')->whereBetween('created_at', [$start, $end])
            ->whereNotNull('P8')
            ->where('P8', '<>', '');

        $feedbacks = $query->get();

        $totalResponden = 0;
        $respondenPuas = 0;

        foreach ($feedbacks as $fb) {
            if (!is_numeric($fb->P8)) continue;

            $skor = min(4, max(1, (float) $fb->P8));
            $totalResponden++;

            if ($skor >= 3.5) {
                $respondenPuas++;
            }
        }

        if ($totalResponden == 0) return 0;

        return round(($respondenPuas / $totalResponden) * 100, 1);
    }

    public function calculateFeedbackKenyamananBerkendaraDetail($itemDetail, $personId = null)
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

        $start = Carbon::create($tahun, 1, 1)->startOfDay();
        $end = ($tahun == now()->year) ? now()->endOfDay() : Carbon::create($tahun, 12, 31)->endOfDay();

        $query = Nilaifeedback::select('P8', 'created_at')->whereBetween('created_at', [$start, $end])
            ->whereNotNull('P8')
            ->where('P8', '<>', '');

        $feedbacks = $query->get();

        if ($feedbacks->isEmpty()) return $this->getDefaultDetailResponse();

        $totalResponden = 0;
        $respondenPuas = 0;
        
        $monthlyDataRaw = [];
        $dailyDataRaw = [];

        foreach ($feedbacks as $fb) {
            if (!is_numeric($fb->P8)) continue;

            $score = min(4, max(1, (float) $fb->P8));
            $isPuas = $score >= 3.5 ? 1 : 0;

            $totalResponden++;
            if ($isPuas) $respondenPuas++;

            $date = Carbon::parse($fb->created_at);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            if (!isset($monthlyDataRaw[$monthKey])) {
                $monthlyDataRaw[$monthKey] = ['total' => 0, 'puas' => 0, 'scores' => []];
            }
            $monthlyDataRaw[$monthKey]['total']++;
            $monthlyDataRaw[$monthKey]['puas'] += $isPuas;
            $monthlyDataRaw[$monthKey]['scores'][] = $score;

            if (!isset($dailyDataRaw[$monthKey][$dayKey])) {
                $dailyDataRaw[$monthKey][$dayKey] = ['total' => 0, 'puas' => 0, 'scores' => []];
            }
            $dailyDataRaw[$monthKey][$dayKey]['total']++;
            $dailyDataRaw[$monthKey][$dayKey]['puas'] += $isPuas;
            $dailyDataRaw[$monthKey][$dayKey]['scores'][] = $score;
        }

        if ($totalResponden == 0) return $this->getDefaultDetailResponse();

        $progress = round(($respondenPuas / $totalResponden) * 100, 1);
        $gapRaw = $progress - $nilaiTarget;
        $gap = $progress > $nilaiTarget ? 0 : rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $monthlyAverages = [];
        $monthlyProgressAverages = [];
        $dailyBreakdownPerMonth = [];
        $dailyProgressPerMonth = [];

        foreach ($monthlyDataRaw as $month => $data) {
            $monthlyAverages[$month] = round(array_sum($data['scores']) / count($data['scores']), 1);
            $monthlyProgressAverages[$month] = round(($data['puas'] / $data['total']) * 100, 1);
        }

        foreach ($dailyDataRaw as $month => $days) {
            foreach ($days as $day => $data) {
                $dailyBreakdownPerMonth[$month][$day] = round(array_sum($data['scores']) / count($data['scores']), 1);
                $dailyProgressPerMonth[$month][$day] = round(($data['puas'] / $data['total']) * 100, 1);
            }
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
                'below' => $totalResponden - $respondenPuas,
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }
}