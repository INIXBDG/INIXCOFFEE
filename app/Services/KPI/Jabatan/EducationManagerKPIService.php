<?php

namespace App\Services\KPI\Jabatan;

use App\Models\ActivityInstruktur;
use App\Models\HariLibur;
use App\Models\karyawan;
use App\Models\Materi;
use App\Models\RKM;
use App\Traits\KPIDefaultResponseTrait;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EducationManagerKPIService
{
    use KPIDefaultResponseTrait;

    private function calculatePengembanganKurikulumPelatihan($item, $personId)
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

            $dataMateri = Materi::whereYear('created_at', $tahun)->get();

            $totalBulanDalamTahun = 12;

            $bulanYangAdaMateri = $dataMateri
                ->pluck('created_at')
                ->map(function ($date) {
                    return Carbon::parse($date)->month;
                })
                ->unique()
                ->count();

            if ($totalBulanDalamTahun == 0) {
                return 0;
            }

            $progress = $bulanYangAdaMateri;

            return round($progress);
    }

    private function calculatePengembanganKurikulumPelatihanDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (is_null($detail) || is_null($detail->detail_jangka)) {
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

        $nilaiTarget = isset($detail->nilai_target) ? (float) $detail->nilai_target : 0;
        $tahun = (int) $detail->detail_jangka;

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

        $dataMateri = Materi::whereYear('created_at', $tahun)->get();

        $bulanYangAdaMateriList = $dataMateri
            ->pluck('created_at')
            ->map(function ($date) {
                return Carbon::parse($date)->month;
            })
            ->unique()
            ->values()
            ->toArray();

        $bulanYangAdaMateri = count($bulanYangAdaMateriList);
        $totalBulanDalamTahun = 12;

        if ($totalBulanDalamTahun == 0) {
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

        $presentase = $bulanYangAdaMateri;
        $progress = round($presentase);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $bulanYangAdaMateri;
        $below = $totalBulanDalamTahun - $bulanYangAdaMateri;

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        for ($m = 1; $m <= 12; $m++) {
            $monthKey = "{$tahun}-" . str_pad($m, 2, '0', STR_PAD_LEFT);

            $hasMateri = in_array($m, $bulanYangAdaMateriList);
            $monthValue = $hasMateri ? 1.0 : 0.0;

            $monthlyData[$monthKey] = $monthValue;
            $monthlyProgress[$monthKey] = $monthValue * 100;

            $dailyBreakdownPerMonth[$monthKey] = [];
            $dailyProgressPerMonth[$monthKey] = [];
        }

        ksort($monthlyData);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgress);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function calculatePeningkatanKnowledgeSharing($item, $personId)
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

        $dataMateri = ActivityInstruktur::whereYear('activity_date', $tahun)->where('activity_type', 'Sharing Knowledge')->get();

        $totalMingguDalamTahun = Carbon::create($tahun, 1, 1)->weeksInYear;

        $mingguYangSudahJalan = [];

        foreach ($dataMateri as $activity) {
            $nomorMinggu = Carbon::parse($activity->activity_date)->week;

            $mingguYangSudahJalan[$nomorMinggu] = true;
        }

        $jumlahMingguTerisi = count($mingguYangSudahJalan);

        if ($totalMingguDalamTahun == 0) {
            $progress = 0;
        } else {
            $progress = $jumlahMingguTerisi;
        }

        if ($progress > 100) {
            $progress = 100;
        }

        return round($progress);
    }

    private function calculatePeningkatanKnowledgeSharingDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (is_null($detail) || is_null($detail->nilai_target) || is_null($detail->detail_jangka)) {
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

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
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

        $dataMateri = ActivityInstruktur::whereYear('activity_date', $tahun)
            ->where('activity_type', 'Sharing Knowledge')
            ->get();

        if ($dataMateri->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => rtrim(rtrim(sprintf('%.1f', 0 - $nilaiTarget), '0'), '.'),
                'pie_chart' => ['above' => 0, 'below' => Carbon::create($tahun, 1, 1)->weeksInYear],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $totalMingguDalamTahun = Carbon::create($tahun, 1, 1)->weeksInYear;

        $mingguYangSudahJalan = [];

        foreach ($dataMateri as $activity) {
            $nomorMinggu = Carbon::parse($activity->activity_date)->week;
            $mingguYangSudahJalan[$nomorMinggu] = true;
        }

        $jumlahMingguTerisi = count($mingguYangSudahJalan);

        $progress = $totalMingguDalamTahun == 0 ? 0 : $jumlahMingguTerisi;

        if ($progress > 100) {
            $progress = 100;
        }

        $progress = round($progress);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $jumlahMingguTerisi;
        $below = max(0, $totalMingguDalamTahun - $jumlahMingguTerisi);

        $dailyValues = [];

        foreach ($dataMateri as $activity) {
            $tanggal = Carbon::parse($activity->activity_date);
            $dateKey = $tanggal->format('Y-m-d');
            $dailyValues[$dateKey][] = 1;
        }

        $dailyAverages = [];
        foreach ($dailyValues as $dateStr => $values) {
            $dailyAverages[$dateStr] = round(array_sum($values) / count($values), 1);
        }

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgressRaw = [];
        $dailyProgressPerMonth = [];

        foreach ($dailyAverages as $dateStr => $avg) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $monthlyData[$monthKey][] = $avg;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $avg;

            $monthlyProgressRaw[$monthKey][] = $avg;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $avg * 100;
        }

        $monthlyAverages = [];
        $monthlyProgress = [];

        foreach ($monthlyData as $month => $dailyVals) {
            $avg = array_sum($dailyVals) / count($dailyVals);
            $monthlyAverages[$month] = round($avg, 1);
            $monthlyProgress[$month] = round($avg * 100, 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgress);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function calculatePeningkatanKontribusiPelatihan($item)
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

        $startDate = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $endDate = Carbon::createFromDate($tahun, 12, 31)->endOfDay();
        $targetKelas = 357;

        $totalKelas = 0;
        $totalKelasOL = 0;

        $rkmQuery = RKM::where('tanggal_awal', '<=', $endDate)
            ->where('tanggal_akhir', '>=', $startDate)
            ->whereNotNull('instruktur_key')
            ->where('instruktur_key', '!=', '-');

        $rkms = $rkmQuery->get();
        $processedRkmIds = [];

        foreach ($rkms as $rkm) {
            if (in_array($rkm->id, $processedRkmIds)) {
                continue;
            }
            $processedRkmIds[] = $rkm->id;

            $isOLClass = (
                $rkm->instruktur_key === 'OL' ||
                $rkm->instruktur_key2 === 'OL' ||
                $rkm->asisten_key === 'OL'
            );

            if ($isOLClass) {
                $totalKelasOL += 1;
            } else {
                $totalKelas += 1;
            }
        }

        $totalKelasValid = $totalKelas;

        if ($targetKelas <= 0) {
            return 0.0;
        }

        $persentase = ($totalKelasValid / $targetKelas) * 100;
        $progress = round($persentase, 2);

        return $progress;
    }

    private function calculatePeningkatanKontribusiPelatihanDetail($itemDetail)
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
            'class_breakdown' => ['offline' => 0, 'online' => 0],
        ];

        if (is_null($detail) || is_null($detail->nilai_target) || is_null($detail->detail_jangka)) {
            return $emptyResponse;
        }

        $targetKelas = 357;
        $tahun = (int) $detail->detail_jangka;

        if ($targetKelas <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $emptyResponse;
        }

        $startDate = Carbon::create($tahun, 1, 1);
        $endDate = min(Carbon::create($tahun, 12, 31), now());

        if ($startDate > $endDate) {
            return $emptyResponse;
        }

        $rkmQuery = RKM::where('tanggal_awal', '<=', $endDate)
            ->where('tanggal_akhir', '>=', $startDate)
            ->whereNotNull('instruktur_key')
            ->where('instruktur_key', '!=', '-');

        $rkms = $rkmQuery->get();
        $processedRkmIds = [];

        $totalKelas = 0;
        $dailyValues = [];

        foreach ($rkms as $rkm) {
            if (in_array($rkm->id, $processedRkmIds)) continue;
            $processedRkmIds[] = $rkm->id;

            $classDate = Carbon::parse($rkm->tanggal_awal);
            if ($classDate < $startDate || $classDate > $endDate) continue;

            $dateKey = $classDate->format('Y-m-d');
            $totalKelas += 1;
            $dailyValues[$dateKey] = ($dailyValues[$dateKey] ?? 0) + 1;
        }

        $progress = round(($totalKelas / $targetKelas) * 100, 2);
        $gapRaw = $progress - 100;
        $gap = rtrim(rtrim(sprintf('%.2f', $gapRaw), '0'), '.');

        $above = $totalKelas;
        $below = max(0, $targetKelas - $totalKelas);

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgressRaw = [];
        $dailyProgressPerMonth = [];

        foreach ($dailyValues as $dateStr => $total) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $monthlyData[$monthKey][] = $total;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $total;

            $monthlyProgressRaw[$monthKey][] = $total;
            $dailyProgressPerMonth[$monthKey][$dayKey] = ($total / $targetKelas) * 100;
        }

        $monthlyAverages = [];
        $monthlyProgress = [];

        foreach ($monthlyData as $month => $vals) {
            $avg = array_sum($vals) / count($vals);
            $monthlyAverages[$month] = round($avg, 2);
            $monthlyProgress[$month] = round(($avg / $targetKelas) * 100, 2);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgress);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
            'class_breakdown' => [],
        ];
    }

    private function calculateEvaluasiKinerjaInstruktur($item, $personId)
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

        $instrukturs = karyawan::where('Divisi', '!=', 'Direksi')
            ->where('status_aktif', '1')
            ->where('jabatan', 'Instruktur')
            ->get();

        if ($instrukturs->isEmpty()) {
            return 0;
        }

        $startDate = Carbon::create($tahun, 1, 1);
        $endDate = min(Carbon::create($tahun, 12, 31), now());

        if ($startDate > $endDate) {
            return 0;
        }

        $period = CarbonPeriod::create($startDate, $endDate);

        $liburNasional = HariLibur::whereBetween('tanggal', [$startDate, $endDate])
                ->pluck('tanggal')
                ->map(fn($tanggal) => Carbon::parse($tanggal)->toDateString())
                ->toArray();

        $activities = ActivityInstruktur::whereYear('activity_date', $tahun)
            ->whereIn('user_id', $instrukturs->pluck('id'))
            ->get()
            ->groupBy(function ($item) {
                return $item->user_id . '_' . Carbon::parse($item->activity_date)->format('Y-m-d');
            });

        $totalHariKerja = 0;
        $totalAktif = 0;

        foreach ($period as $date) {

            $dateKey = $date->toDateString();

            // Skip Sabtu, Minggu, dan Hari Libur Nasional
            if (
                $date->isWeekend() ||
                in_array($dateKey, $liburNasional)
            ) {
                continue;
            }

            $totalHariKerja++;

            $aktifHariIni = 0;

            foreach ($instrukturs as $instruktur) {

                $key = $instruktur->id . '_' . $dateKey;

                if (isset($activities[$key])) {
                    $totalAktif++;
                    $aktifHariIni++;
                }
            }

            $dailyValues[$dateKey] = $aktifHariIni;
        }

        $totalKemungkinan = $totalHariKerja * $instrukturs->count();

        if ($totalKemungkinan == 0) {
            return 0;
        }

        $progress = ($totalAktif / $totalKemungkinan) * 100;

        return round($progress, 2);
    }

    private function calculateEvaluasiKinerjaInstrukturDetail($itemDetail)
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

        $instrukturs = karyawan::where('Divisi', '!=', 'Direksi')
            ->where('status_aktif', '1')->whereNot('jabatan', 'Outsource')->where('kode_karyawan', 'NOT LIKE', 'OL%')->whereNot('jabatan', 'Pilih Jabatan')->where('nip', '!=', null)
            ->where('jabatan', 'Instruktur')
            ->get();

        if ($instrukturs->isEmpty()) {
            return $emptyResponse;
        }

        $startDate = Carbon::create($tahun, 1, 1);
        $endDate = min(Carbon::create($tahun, 12, 31), now());

        if ($startDate > $endDate) {
            return $emptyResponse;
        }

        $period = CarbonPeriod::create($startDate, $endDate);
        $liburNasional = HariLibur::whereBetween('tanggal', [$startDate, $endDate])
            ->pluck('tanggal')
            ->map(fn($tanggal) => Carbon::parse($tanggal)->toDateString())
            ->toArray();

        $activities = ActivityInstruktur::whereYear('activity_date', $tahun)
            ->get()
            ->groupBy(function ($item) {
                return $item->user_id . '_' . Carbon::parse($item->activity_date)->format('Y-m-d');
            });

        $totalHariKerja = 0;
        $totalAktif = 0;
        $dailyValues = [];

        foreach ($period as $date) {

            $dateKey = $date->toDateString();

            // Skip Sabtu, Minggu, dan Hari Libur Nasional
            if (
                $date->isWeekend() ||
                in_array($dateKey, $liburNasional)
            ) {
                continue;
            }

            $totalHariKerja++;

            $aktifHariIni = 0;

            foreach ($instrukturs as $instruktur) {

                $key = $instruktur->id . '_' . $dateKey;

                if (isset($activities[$key])) {
                    $totalAktif++;
                    $aktifHariIni++;
                }
            }

            $dailyValues[$dateKey] = $aktifHariIni;
        }

        $totalKemungkinan = $totalHariKerja * $instrukturs->count();
        if ($totalKemungkinan == 0) {
            return $emptyResponse;
        }

        $progress = round(($totalAktif / $totalKemungkinan) * 100, 2);
        $gapRaw = $progress - 100;
        $gap = rtrim(rtrim(sprintf('%.2f', $gapRaw), '0'), '.');

        $above = $totalAktif;
        $below = max(0, $totalKemungkinan - $totalAktif);

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgressRaw = [];
        $dailyProgressPerMonth = [];

        foreach ($dailyValues as $dateStr => $total) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $monthlyData[$monthKey][] = $total;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $total;

            $monthlyProgressRaw[$monthKey][] = $total;
            $dailyProgressPerMonth[$monthKey][$dayKey] = ($total / $instrukturs->count()) * 100;
        }

        $monthlyAverages = [];
        $monthlyProgress = [];

        foreach ($monthlyData as $month => $vals) {
            $avg = array_sum($vals) / count($vals);
            $monthlyAverages[$month] = round($avg, 2);
            $monthlyProgress[$month] = round(($avg / $instrukturs->count()) * 100, 2);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgress);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function calculatePembuatanArtikel($item, $personId) {
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

        $startDate = carbon::create($tahun, '01', '01');
        $endDate = carbon::create($tahun, '12', '31');
        $response = Http::get('http://202.138.248.36:8003/api/filtered-articles')->json();

        $apiArtikel = collect($response['data'] ?? []);

        $getData = $apiArtikel->filter(function ($item) use ($startDate, $endDate) {
            $tanggal = Carbon::parse($item['tanggal']);

            return $tanggal->between($startDate, $endDate);
        });

        $totalData = $getData->count();

        if ($totalData == 0) {
            return 0;
        }

        $progress = ($totalData / 24) * 100;

        return round($progress, 2);
    }

    private function calculatePembuatanArtikelDetail($itemDetail) {
        $detail = $itemDetail->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$itemDetail->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$itemDetail->id}");
            return 0;
        }

        $startDate = carbon::create($tahun, '01', '01');
        $endDate = carbon::create($tahun, '12', '31');
        $response = Http::get('http://202.138.248.36:8003/api/filtered-articles')->json();

        $apiArtikel = collect($response['data'] ?? []);

        $getData = $apiArtikel->filter(function ($item) use ($startDate, $endDate) {
            $tanggal = Carbon::parse($item['tanggal']);

            return $tanggal->between($startDate, $endDate);
        });

        $totalData = $getData->count();

        if ($totalData == 0) {
            return [
                'progress' => 0,
                'gap' => -24,
                'pie_chart' => [
                    'above' => 0,
                    'below' => 24,
                ],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $progress = round(($totalData / 24) * 100, 2);
        $gap = $totalData - 24;

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($getData as $item) {
            $tanggal = Carbon::parse($item['tanggal']);

            $monthKey = $tanggal->format('Y-m');
            $dayKey = $tanggal->format('Y-m-d');

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = 0;
            }

            if (!isset($dailyBreakdownPerMonth[$monthKey][$dayKey])) {
                $dailyBreakdownPerMonth[$monthKey][$dayKey] = 0;
            }

            $monthlyData[$monthKey]++;
            $dailyBreakdownPerMonth[$monthKey][$dayKey]++;
        }

        foreach ($monthlyData as $month => $count) {
            $monthlyProgress[$month] = round(($count / 24) * 100, 2);

            foreach ($dailyBreakdownPerMonth[$month] as $day => $dailyCount) {
                $dailyProgressPerMonth[$month][$day] = round(($dailyCount / 24) * 100, 2);
            }
        }

        ksort($monthlyData);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgress);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $totalData,
                'below' => max(0, 24 - $totalData),
            ],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }
}