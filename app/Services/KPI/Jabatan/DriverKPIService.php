<?php

namespace App\Services\KPI\Jabatan;

use App\Models\perbaikanKendaraan;
use App\Models\pickupDriver;
use App\Models\KondisiKendaraan;
use App\Models\HariLibur;
use App\Models\Nilaifeedback;
use App\Traits\KPIDefaultResponseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DriverKPIService
{
    use KPIDefaultResponseTrait;

    private function getHolidays($tahun)
    {
        return HariLibur::where('year', $tahun)->pluck('tanggal')->map(fn($d) => Carbon::parse($d)->toDateString())->toArray();
    }

    private function formatChartData($progress, $nilaiTarget, $above, $below, $rawDailyData)
    {
        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

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

        $query = perbaikanKendaraan::whereBetween('created_at', [$start, $end]);

        if ($personId !== null) {
            $query->where('id_user', $personId);
        }

        $totalData = (clone $query)->count();
        if ($totalData === 0) {
            return 0;
        }

        $dataDiperbaiki = (clone $query)->where('status', 'Selesai')->count();

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

        $query = perbaikanKendaraan::whereBetween('created_at', [$start, $end])->select('created_at', 'status');

        if ($personId !== null) {
            $query->where('id_user', $personId);
        }

        $allRepairs = $query->get();
        $totalData = $allRepairs->count();

        if ($totalData === 0) {
            return $this->getDefaultDetailResponse();
        }

        $dataDiperbaiki = $allRepairs->where('status', 'Selesai')->count();
        $progress = round(($dataDiperbaiki / $totalData) * 100, 1);

        $rawDailyData = [];
        foreach ($allRepairs as $repair) {
            $dateKey = Carbon::parse($repair->created_at)->format('Y-m-d');
            $nilaiItem = $repair->status === 'Selesai' ? 100 : 0;
            $rawDailyData[$dateKey][] = $nilaiItem;
        }

        return $this->formatChartData($progress, $nilaiTarget, $dataDiperbaiki, $totalData - $dataDiperbaiki, $rawDailyData);
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

        $query = pickupDriver::whereBetween('created_at', [$start, $end])->whereNotNull('budget');

        if ($personId !== null) {
            $query->where('id_karyawan', $personId);
        }

        $DataPickup = (clone $query)->withSum('biayaTransportasi', 'harga')->get();
        $totalData = $DataPickup->count();

        if ($totalData === 0) {
            return 0;
        }

        $countAman = $DataPickup
            ->filter(function ($data) {
                $totalBiaya = $data->biaya_transportasi_sum_harga ?? 0;
                return $totalBiaya <= $data->budget;
            })
            ->count();

        return round(($countAman / $totalData) * 100, 1);
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

        $query = pickupDriver::whereBetween('created_at', [$start, $end])
            ->whereNotNull('budget')
            ->select('id', 'created_at', 'budget')
            ->withSum('biayaTransportasi', 'harga');

        if ($personId !== null) {
            $query->where('id_karyawan', $personId);
        }

        $DataPickup = $query->get();
        $totalData = $DataPickup->count();

        if ($totalData === 0) {
            return $this->getDefaultDetailResponse();
        }

        $countAman = 0;
        $rawDailyData = [];

        foreach ($DataPickup as $data) {
            $totalBiaya = $data->biaya_transportasi_sum_harga ?? 0;
            $isAman = $totalBiaya <= $data->budget ? 1 : 0;
            if ($isAman) {
                $countAman++;
            }

            $dateKey = Carbon::parse($data->created_at)->format('Y-m-d');
            $rawDailyData[$dateKey][] = $isAman * 100;
        }

        $progress = round(($countAman / $totalData) * 100, 1);

        return $this->formatChartData($progress, $nilaiTarget, $countAman, $totalData - $countAman, $rawDailyData);
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

        $hariLibur = $this->getHolidays($tahun);

        $startPeriode = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $endPeriode = Carbon::createFromDate($tahun, 12, 31)->endOfDay();
        $hariIni = now()->startOfDay();

        if ($hariIni > $endPeriode) {
            $hariIni = $endPeriode;
        }

        $query = KondisiKendaraan::whereBetween('tanggal_pemeriksaan', [$startPeriode, $hariIni])->whereNotNull('tanggal_pemeriksaan');

        if ($personId !== null) {
            $query->where('user_id', $personId);
        }

        $reports = (clone $query)->pluck('tanggal_pemeriksaan');

        if ($reports->isEmpty()) {
            return 0;
        }

        $validDates = $reports
            ->filter(function ($d) use ($hariLibur) {
                return !in_array(Carbon::parse($d)->toDateString(), $hariLibur);
            })
            ->map(function ($d) {
                return Carbon::parse($d);
            });

        if ($validDates->isEmpty()) {
            return 0;
        }

        $firstReportDate = $validDates->sortBy('timestamp')->first();
        $startMinggu = $firstReportDate->copy()->startOfWeek(Carbon::MONDAY);

        $today = Carbon::now();
        $dayOfWeek = $today->dayOfWeek;

        if ($dayOfWeek < 6) {
            $checkUntil = $today->copy()->subWeek()->endOfWeek(Carbon::SUNDAY);
        } else {
            $checkUntil = $today->endOfDay();
        }

        if ($checkUntil > $endPeriode) {
            $checkUntil = $endPeriode;
        }

        $totalMinggu = ceil($startMinggu->diffInDays($checkUntil) / 7);
        if ($totalMinggu < 1) {
            $totalMinggu = 1;
        }

        $weeksInRange = [];
        for ($i = 0; $i < $totalMinggu; $i++) {
            $weekStart = $startMinggu->copy()->addWeeks($i)->startOfWeek(Carbon::MONDAY);
            $weeksInRange[] = $weekStart->format('o-W');
        }

        $weeklyReports = $validDates->groupBy(function ($date) {
            return $date->format('o-W');
        });

        $jumlahReportTepat = 0;
        foreach ($weeksInRange as $weekStr) {
            if ($weeklyReports->has($weekStr)) {
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

        $hariLibur = $this->getHolidays($tahun);

        $startPeriode = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $endPeriode = Carbon::createFromDate($tahun, 12, 31)->endOfDay();
        $hariIni = now()->startOfDay();

        if ($hariIni > $endPeriode) {
            $hariIni = $endPeriode;
        }

        $query = KondisiKendaraan::whereBetween('tanggal_pemeriksaan', [$startPeriode, $hariIni])->whereNotNull('tanggal_pemeriksaan');

        if ($personId !== null) {
            $query->where('user_id', $personId);
        }

        $reports = (clone $query)->pluck('tanggal_pemeriksaan');

        if ($reports->isEmpty()) {
            return $this->getDefaultDetailResponse();
        }

        $validDates = $reports
            ->filter(function ($d) use ($hariLibur) {
                return !in_array(Carbon::parse($d)->toDateString(), $hariLibur);
            })
            ->map(function ($d) {
                return Carbon::parse($d);
            });

        if ($validDates->isEmpty()) {
            return $this->getDefaultDetailResponse();
        }

        $firstReportDate = $validDates->sortBy('timestamp')->first();
        $startMinggu = $firstReportDate->copy()->startOfWeek(Carbon::MONDAY);

        $today = Carbon::now();
        $dayOfWeek = $today->dayOfWeek;

        if ($dayOfWeek < 6) {
            $checkUntil = $today->copy()->subWeek()->endOfWeek(Carbon::SUNDAY);
        } else {
            $checkUntil = $today->endOfDay();
        }

        if ($checkUntil > $endPeriode) {
            $checkUntil = $endPeriode;
        }

        $totalMinggu = ceil($startMinggu->diffInDays($checkUntil) / 7);
        if ($totalMinggu < 1) {
            $totalMinggu = 1;
        }

        $weeksInRange = [];
        for ($i = 0; $i < $totalMinggu; $i++) {
            $weekStart = $startMinggu->copy()->addWeeks($i)->startOfWeek(Carbon::MONDAY);
            $weeksInRange[] = $weekStart->format('o-W');
        }

        $weeklyReports = $validDates->groupBy(function ($date) {
            return $date->format('o-W');
        });

        $jumlahReportTepat = 0;
        $jumlahReportTidakTepat = 0;

        foreach ($weeksInRange as $weekStr) {
            if ($weeklyReports->has($weekStr)) {
                $jumlahReportTepat++;
            } else {
                $jumlahReportTidakTepat++;
            }
        }

        $progress = round(($jumlahReportTepat / $totalMinggu) * 100, 1);

        // Logika Daily Breakdown yang Sebenarnya (Bukan artifisial salinan mingguan)
        $reportDays = $validDates->map(fn($d) => $d->format('Y-m-d'))->unique()->toArray();
        $rawDailyData = [];

        $currentDate = $startMinggu->copy();
        while ($currentDate <= $checkUntil) {
            $dateKey = $currentDate->format('Y-m-d');
            $isHoliday = in_array($dateKey, $hariLibur);

            if (!$isHoliday) {
                $hasReport = in_array($dateKey, $reportDays);
                $rawDailyData[$dateKey][] = $hasReport ? 100 : 0;
            }
            $currentDate->addDay();
        }

        return $this->formatChartData($progress, $nilaiTarget, $jumlahReportTepat, $jumlahReportTidakTepat, $rawDailyData);
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
        $end = $tahun == now()->year ? now()->endOfDay() : Carbon::create($tahun, 12, 31)->endOfDay();

        $query = Nilaifeedback::whereBetween('created_at', [$start, $end])
            ->whereNotNull('P8')
            ->where('P8', '<>', '');

        $feedbacks = $query->get();

        $totalResponden = 0;
        $respondenPuas = 0;

        foreach ($feedbacks as $fb) {
            if (!is_numeric($fb->P8)) {
                continue;
            }

            $skor = min(4, max(1, (float) $fb->P8));
            $totalResponden++;

            if ($skor >= 3.5) {
                $respondenPuas++;
            }
        }

        if ($totalResponden == 0) {
            return 0;
        }

        $progress = ($respondenPuas / $totalResponden) * 100;
        return round($progress, 1);
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
        $end = $tahun == now()->year ? now()->endOfDay() : Carbon::create($tahun, 12, 31)->endOfDay();

        $query = Nilaifeedback::whereBetween('created_at', [$start, $end])
            ->whereNotNull('P8')
            ->where('P8', '<>', '');

        $feedbacks = $query->get();

        if ($feedbacks->isEmpty()) {
            return $this->getDefaultDetailResponse();
        }

        $totalResponden = 0;
        $respondenPuas = 0;
        $rawDailyData = [];

        foreach ($feedbacks as $fb) {
            if (!is_numeric($fb->P8)) {
                continue;
            }

            $score = min(4, max(1, (float) $fb->P8));
            $isPuas = $score >= 3.5 ? 1 : 0;

            $totalResponden++;
            if ($isPuas) {
                $respondenPuas++;
            }

            $dateKey = Carbon::parse($fb->created_at)->format('Y-m-d');
            $rawDailyData[$dateKey][] = $isPuas * 100;
        }

        if ($totalResponden == 0) {
            return $this->getDefaultDetailResponse();
        }

        $progress = round(($respondenPuas / $totalResponden) * 100, 1);

        return $this->formatChartData($progress, $nilaiTarget, $respondenPuas, $totalResponden - $respondenPuas, $rawDailyData);
    }
}
