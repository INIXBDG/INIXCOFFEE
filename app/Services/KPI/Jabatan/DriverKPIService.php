<?php

namespace App\Services\KPI\Jabatan;

use App\Models\PerbaikanKendaraan;
use App\Models\pickupDriver;
use App\Models\KondisiKendaraan;
use App\Models\HariLibur;
use App\Models\Nilaifeedback;
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

        $nilaiTarget = (float) $detail->nilai_target;

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $totalQuery = PerbaikanKendaraan::whereBetween('created_at', [$start, $end]);
        $selesaiQuery = PerbaikanKendaraan::whereBetween('created_at', [$start, $end])
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

        $presentase = ($dataDiperbaiki / $totalData) * 100;

        return round($presentase, 1);
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

        if ($personId !== null) {
            $allRepairs = PerbaikanKendaraan::whereBetween('created_at', [$start, $end])
                ->where('id_user', $personId)
                ->get();
        } else {
            $allRepairs = PerbaikanKendaraan::whereBetween('created_at', [$start, $end])->get();
        }

        $totalData = $allRepairs->count();

        if ($totalData == 0) {
            return $this->getDefaultDetailResponse();
        }

        $dataDiperbaiki = $allRepairs->where('status', 'Selesai')->count();
        $dataBelumDiperbaiki = $totalData - $dataDiperbaiki;

        if ($totalData <= 0) {
            return $this->getDefaultDetailResponse();
        }

        $presentase = ($dataDiperbaiki / $totalData) * 100;
        $progress = round($presentase, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $dataDiperbaiki;
        $below = $dataBelumDiperbaiki;

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
            'pie_chart' => ['above' => $above, 'below' => $below],
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

        $query = pickupDriver::whereBetween('created_at', [$start, $end])
            ->whereNotNull('budget');

        if ($personId !== null) {
            $query->where('id_karyawan', $personId);
        }

        $DataPickup = $query->with(['biayaTransportasi'])->get();

        $totalData = $DataPickup->count();
        if ($totalData === 0) {
            return 0;
        }

        $countAman = 0;

        foreach ($DataPickup as $data) {
            $totalBiaya = $data->biayaTransportasi->sum('harga') ?? 0;

            if ($totalBiaya <= $data->budget) {
                $countAman++;
            }
        }

        $presentase = ($countAman / $totalData) * 100;

        return round($presentase, 1);
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
            ->whereNotNull('budget');

        if ($personId !== null) {
            $query->where('id_karyawan', $personId);
        }

        $DataPickup = $query->with(['biayaTransportasi'])->get();

        $totalData = $DataPickup->count();

        if ($totalData === 0) {
            return $this->getDefaultDetailResponse();
        }

        $countAman = 0;
        $dailyValues = [];

        foreach ($DataPickup as $data) {
            $totalBiaya = $data->biayaTransportasi->sum('harga') ?? 0;

            $isAman = $totalBiaya <= $data->budget ? 1 : 0;
            if ($isAman) {
                $countAman++;
            }

            $tanggal = Carbon::parse($data->created_at);
            $dateKey = $tanggal->format('Y-m-d');

            if (!isset($dailyValues[$dateKey])) {
                $dailyValues[$dateKey] = [];
            }
            $dailyValues[$dateKey][] = $isAman * 100;
        }

        $presentase = ($countAman / $totalData) * 100;
        $progress = round($presentase, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $countAman;
        $below = $totalData - $countAman;

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
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
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

        $response = Http::get("https://libur.deno.dev/api", ['year' => $tahun]);

        if ($response->successful()) {
            foreach ($response->json() as $libur) {
                HariLibur::updateOrCreate(
                    ['tanggal' => $libur['date']],
                    ['nama' => $libur['name'], 'year' => $tahun]
                );
            }
        }

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

        if ($personId !== null) {
            $firstReport = KondisiKendaraan::whereBetween('tanggal_pemeriksaan', [$startPeriode, $hariIni])
                ->where('user_id', $personId)
                ->whereNotNull('tanggal_pemeriksaan')
                ->get()
                ->filter(function ($item) use ($hariLibur) {
                    return !in_array(Carbon::parse($item->tanggal_pemeriksaan)->toDateString(), $hariLibur);
                })
                ->sortBy('tanggal_pemeriksaan')
                ->first();
        } else {
            $firstReport = KondisiKendaraan::whereBetween('tanggal_pemeriksaan', [$startPeriode, $hariIni])
                ->whereNotNull('tanggal_pemeriksaan')
                ->get()
                ->filter(function ($item) use ($hariLibur) {
                    return !in_array(Carbon::parse($item->tanggal_pemeriksaan)->toDateString(), $hariLibur);
                })
                ->sortBy('tanggal_pemeriksaan')
                ->first();
        }

        if (!$firstReport) {
            return 0;
        }

        $startMinggu = Carbon::parse($firstReport->tanggal_pemeriksaan)->startOfWeek(Carbon::MONDAY);

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

        $jumlahReportTepat = 0;

        for ($i = 0; $i < $totalMinggu; $i++) {
            $weekStart = $startMinggu->copy()->addWeeks($i)->startOfWeek(Carbon::MONDAY);
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

            if ($weekEnd > $checkUntil) {
                $weekEnd = $checkUntil;
            }

            if ($personId !== null) {
                $hasReport = KondisiKendaraan::whereBetween('tanggal_pemeriksaan', [$weekStart, $weekEnd])
                    ->where('user_id', $personId)
                    ->whereNotNull('tanggal_pemeriksaan')
                    ->get()
                    ->filter(function ($item) use ($hariLibur) {
                        return !in_array(Carbon::parse($item->tanggal_pemeriksaan)->toDateString(), $hariLibur);
                    })
                    ->isNotEmpty();
            } else {
                $hasReport = KondisiKendaraan::whereBetween('tanggal_pemeriksaan', [$weekStart, $weekEnd])
                    ->whereNotNull('tanggal_pemeriksaan')
                    ->get()
                    ->filter(function ($item) use ($hariLibur) {
                        return !in_array(Carbon::parse($item->tanggal_pemeriksaan)->toDateString(), $hariLibur);
                    })
                    ->isNotEmpty();
            }

            if ($hasReport) {
                $jumlahReportTepat++;
            }
        }

        $presentase = ($jumlahReportTepat / $totalMinggu) * 100;
        return round($presentase, 1);
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

        $response = Http::get("https://libur.deno.dev/api", ['year' => $tahun]);

        if ($response->successful()) {
            foreach ($response->json() as $libur) {
                HariLibur::updateOrCreate(
                    ['tanggal' => $libur['date']],
                    ['nama' => $libur['name'], 'year' => $tahun]
                );
            }
        }

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

        if ($personId !== null) {
            $firstReport = KondisiKendaraan::whereBetween('tanggal_pemeriksaan', [$startPeriode, $hariIni])
                ->where('user_id', $personId)
                ->whereNotNull('tanggal_pemeriksaan')
                ->get()
                ->filter(function ($item) use ($hariLibur) {
                    return !in_array(Carbon::parse($item->tanggal_pemeriksaan)->toDateString(), $hariLibur);
                })
                ->sortBy('tanggal_pemeriksaan')
                ->first();
        } else {
            $firstReport = KondisiKendaraan::whereBetween('tanggal_pemeriksaan', [$startPeriode, $hariIni])
                ->whereNotNull('tanggal_pemeriksaan')
                ->get()
                ->filter(function ($item) use ($hariLibur) {
                    return !in_array(Carbon::parse($item->tanggal_pemeriksaan)->toDateString(), $hariLibur);
                })
                ->sortBy('tanggal_pemeriksaan')
                ->first();
        }

        if (!$firstReport) {
            return $this->getDefaultDetailResponse();
        }

        $startMinggu = Carbon::parse($firstReport->tanggal_pemeriksaan)->startOfWeek(Carbon::MONDAY);

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

        $jumlahReportTepat = 0;
        $jumlahReportTidakTepat = 0;
        $weeklyData = [];

        for ($i = 0; $i < $totalMinggu; $i++) {
            $weekStart = $startMinggu->copy()->addWeeks($i)->startOfWeek(Carbon::MONDAY);
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

            if ($weekEnd > $checkUntil) {
                $weekEnd = $checkUntil;
            }

            if ($personId !== null) {
                $hasReport = KondisiKendaraan::whereBetween('tanggal_pemeriksaan', [$weekStart, $weekEnd])
                    ->where('user_id', $personId)
                    ->whereNotNull('tanggal_pemeriksaan')
                    ->get()
                    ->filter(function ($item) use ($hariLibur) {
                        return !in_array(Carbon::parse($item->tanggal_pemeriksaan)->toDateString(), $hariLibur);
                    })
                    ->isNotEmpty();
            } else {
                $hasReport = KondisiKendaraan::whereBetween('tanggal_pemeriksaan', [$weekStart, $weekEnd])
                    ->whereNotNull('tanggal_pemeriksaan')
                    ->get()
                    ->filter(function ($item) use ($hariLibur) {
                        return !in_array(Carbon::parse($item->tanggal_pemeriksaan)->toDateString(), $hariLibur);
                    })
                    ->isNotEmpty();
            }

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

        $presentase = ($jumlahReportTepat / $totalMinggu) * 100;
        $progress = round($presentase, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $jumlahReportTepat;
        $below = $jumlahReportTidakTepat;

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
            'pie_chart' => ['above' => $above, 'below' => $below],
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

        $query = Nilaifeedback::whereBetween('created_at', [$start, $end])
            ->whereNotNull('P8')
            ->where('P8', '<>', '');

        // PERBAIKAN: Aktifkan jika butuh filter khusus driver / person tertentu
        // if ($personId) {
        //     $query->where('driver_id', $personId); // Sesuaikan dengan foreign key Anda
        // }

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
        $end = ($tahun == now()->year) ? now()->endOfDay() : Carbon::create($tahun, 12, 31)->endOfDay();

        $query = Nilaifeedback::whereBetween('created_at', [$start, $end])
            ->whereNotNull('P8')
            ->where('P8', '<>', '');

            
        // if ($personId) {
        //     $query->where('driver_id', $personId);
        // }

        $feedbacks = $query->get();
            // dd($feedbacks);


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

            // Kumpulkan Data Bulanan
            if (!isset($monthlyDataRaw[$monthKey])) {
                $monthlyDataRaw[$monthKey] = ['total' => 0, 'puas' => 0, 'scores' => []];
            }
            $monthlyDataRaw[$monthKey]['total']++;
            $monthlyDataRaw[$monthKey]['puas'] += $isPuas;
            $monthlyDataRaw[$monthKey]['scores'][] = $score;

            // PERBAIKAN BUG: Kumpulkan Data Harian (Menghindari Data Tertimpa)
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
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $monthlyAverages = [];
        $monthlyProgressAverages = [];
        $dailyBreakdownPerMonth = [];
        $dailyProgressPerMonth = [];

        // Kalkulasi Rata-rata Bulanan
        foreach ($monthlyDataRaw as $month => $data) {
            $monthlyAverages[$month] = round(array_sum($data['scores']) / count($data['scores']), 1);
            $monthlyProgressAverages[$month] = round(($data['puas'] / $data['total']) * 100, 1);
        }

        // Kalkulasi Rata-rata Harian
        foreach ($dailyDataRaw as $month => $days) {
            foreach ($days as $day => $data) {
                // Rata-rata skor hari itu
                $dailyBreakdownPerMonth[$month][$day] = round(array_sum($data['scores']) / count($data['scores']), 1);
                // Persentase yang puas di hari itu
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
            'monthly_data' => $monthlyAverages, // Rata-rata skor per bulan (skala 1-4)
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth, // Rata-rata skor per hari (skala 1-4)
            'monthly_progress' => $monthlyProgressAverages, // Persentase yang puas per bulan (0-100%)
            'daily_progress_per_month' => $dailyProgressPerMonth, // Persentase yang puas per hari (0-100%)
        ];
    }
}
