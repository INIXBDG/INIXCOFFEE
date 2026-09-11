<?php

namespace App\Services\KPI\Jabatan;

use App\Models\ActivityInstruktur;
use App\Models\HariLibur;
use App\Models\karyawan;
use App\Models\Materi;
use App\Models\RKM;
use App\Models\User;
use App\Traits\KPIDefaultResponseTrait;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EducationManagerKPIService
{
    use KPIDefaultResponseTrait;

    public function calculatePengembanganKurikulumPelatihan($item, $personId)
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

        $bulanYangAdaMateri = Materi::whereYear('created_at', $tahun)
            ->selectRaw('COUNT(DISTINCT MONTH(created_at)) as total_bulan')
            ->value('total_bulan') ?? 0;

        return (int) round($bulanYangAdaMateri);
    }

    public function calculatePengembanganKurikulumPelatihanDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (is_null($detail) || is_null($detail->detail_jangka)) {
            return [
                'progress' => 0, 'gap' => 0, 'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [], 'daily_breakdown_per_month' => [],
                'monthly_progress' => [], 'daily_progress_per_month' => [],
            ];
        }

        $nilaiTarget = (float) ($detail->nilai_target ?? 0);
        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0, 'gap' => 0, 'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [], 'daily_breakdown_per_month' => [],
                'monthly_progress' => [], 'daily_progress_per_month' => [],
            ];
        }

        $bulanYangAdaMateriList = Materi::whereYear('created_at', $tahun)
            ->selectRaw('MONTH(created_at) as bulan')
            ->distinct()
            ->pluck('bulan')
            ->toArray();

        $bulanYangAdaMateri = count($bulanYangAdaMateriList);
        $totalBulanDalamTahun = 12;

        $progress = (int) round($bulanYangAdaMateri);
        $gapRaw = $progress - $nilaiTarget;
        $gap = $progress > $nilaiTarget ? 0 : rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        for ($m = 1; $m <= 12; ++$m) {
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
            'pie_chart' => ['above' => $bulanYangAdaMateri, 'below' => $totalBulanDalamTahun - $bulanYangAdaMateri],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    public function calculatePeningkatanKnowledgeSharing($item, $personId = null)
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

        $jumlahMingguTerisi = ActivityInstruktur::whereYear('activity_date', $tahun)
            ->where('activity_type', 'Sharing Knowledge')
            ->when($personId !== null, fn($q) => $q->where('user_id', $personId))
            ->selectRaw('COUNT(DISTINCT WEEK(activity_date)) as total_minggu')
            ->value('total_minggu') ?? 0;

        return (int) $jumlahMingguTerisi;
    }

    public function calculatePeningkatanKnowledgeSharingDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();
        $emptyResponse = [
            'progress' => 0, 'gap' => 0, 'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [], 'daily_breakdown_per_month' => [],
            'monthly_progress' => [], 'daily_progress_per_month' => [],
        ];

        if (!$detail || !$detail->detail_jangka || !is_numeric($detail->nilai_target)) {
            return $emptyResponse;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $emptyResponse;
        }

        $totalMingguDalamTahun = Carbon::create($tahun, 1, 1)->weeksInYear;

        $activityData = ActivityInstruktur::select('activity_date')
            ->whereYear('activity_date', $tahun)
            ->where('activity_type', 'Sharing Knowledge')
            ->when($personId !== null, fn($q) => $q->where('user_id', $personId))
            ->get();

        if ($activityData->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => rtrim(rtrim(sprintf('%.1f', 0 - $nilaiTarget), '0'), '.'),
                'pie_chart' => ['above' => 0, 'below' => $totalMingguDalamTahun],
                'monthly_data' => [], 'daily_breakdown_per_month' => [],
                'monthly_progress' => [], 'daily_progress_per_month' => [],
            ];
        }

        $mingguYangSudahJalan = [];
        $dailyValues = [];
        $monthlyData = [];

        foreach ($activityData as $activity) {
            $tanggal = Carbon::parse($activity->activity_date);
            $mingguYangSudahJalan[$tanggal->week] = true;

            $dateKey = $tanggal->format('Y-m-d');
            $monthKey = $tanggal->format('Y-m');

            $dailyValues[$dateKey] = ($dailyValues[$dateKey] ?? 0) + 1;
            $monthlyData[$monthKey] = ($monthlyData[$monthKey] ?? 0) + 1;
        }

        $jumlahMingguTerisi = count($mingguYangSudahJalan);
        $progress = $jumlahMingguTerisi;
        $gap = $progress - $nilaiTarget;

        $dailyBreakdownPerMonth = [];
        $dailyProgressPerMonth = [];
        $monthlyProgress = [];

        foreach ($dailyValues as $dateStr => $totalSesiHariIni) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');

            $dailyBreakdownPerMonth[$monthKey][$dateStr] = $totalSesiHariIni;
            $dailyProgressPerMonth[$monthKey][$dateStr] = $totalSesiHariIni;
        }

        foreach ($monthlyData as $month => $totalSesiBulanIni) {
            $monthlyProgress[$month] = $totalSesiBulanIni;
        }

        ksort($monthlyData);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgress);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $jumlahMingguTerisi, 'below' => max(0, $totalMingguDalamTahun - $jumlahMingguTerisi)],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    public function calculatePeningkatanKontribusiPelatihan($item)
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

        $targetKelas = 357;
        $startDate = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $endDate = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $totalKelasInternal = RKM::where('tanggal_awal', '<=', $endDate)
            ->where('tanggal_akhir', '>=', $startDate)
            ->whereNotNull('instruktur_key')
            ->where('instruktur_key', '!=', '-')
            ->where('instruktur_key', '!=', 'OL')
            ->where(function ($query) {
                $query->where('instruktur_key2', '!=', 'OL')->orWhereNull('instruktur_key2');
            })
            ->where(function ($query) {
                $query->where('asisten_key', '!=', 'OL')->orWhereNull('asisten_key');
            })
            ->count();

        if ($targetKelas <= 0) {
            return 0.0;
        }

        return round(($totalKelasInternal / $targetKelas) * 100, 2);
    }

    public function calculatePeningkatanKontribusiPelatihanDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();
        $emptyResponse = [
            'progress' => 0, 'gap' => 0, 'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [], 'daily_breakdown_per_month' => [],
            'monthly_progress' => [], 'daily_progress_per_month' => [],
            'class_breakdown' => ['internal' => 0, 'freelance' => 0],
        ];

        if (!$detail || !$detail->detail_jangka) {
            return $emptyResponse;
        }

        $targetKelas = 357;
        $tahun = (int) $detail->detail_jangka;

        if ($targetKelas <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $emptyResponse;
        }

        $startDate = Carbon::create($tahun, 1, 1)->startOfDay();
        $endDate = Carbon::create($tahun, 12, 31)->endOfDay();

        if ($startDate > $endDate) {
            return $emptyResponse;
        }

        $rkms = RKM::select('id', 'tanggal_awal', 'instruktur_key', 'instruktur_key2', 'asisten_key')
            ->where('tanggal_awal', '<=', $endDate)
            ->where('tanggal_akhir', '>=', $startDate)
            ->whereNotNull('instruktur_key')
            ->where('instruktur_key', '!=', '-')
            ->get();

        $totalKelasInternal = 0;
        $totalKelasFreelance = 0;
        $dailyValues = [];

        foreach ($rkms as $rkm) {
            $classDate = Carbon::parse($rkm->tanggal_awal);
            if ($classDate > $endDate) {
                continue;
            }

            $dateKey = $classDate->format('Y-m-d');
            $isFreelance = ($rkm->instruktur_key === 'OL' || $rkm->instruktur_key2 === 'OL' || $rkm->asisten_key === 'OL');

            if ($isFreelance) {
                ++$totalKelasFreelance;
            } else {
                ++$totalKelasInternal;
                $dailyValues[$dateKey] = ($dailyValues[$dateKey] ?? 0) + 1;
            }
        }

        $progress = round(($totalKelasInternal / $targetKelas) * 100, 2);
        $gapRaw = $progress - 100;
        $gap = rtrim(rtrim(sprintf('%.2f', $gapRaw), '0'), '.');

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($dailyValues as $dateStr => $total) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $monthlyData[$monthKey] = ($monthlyData[$monthKey] ?? 0) + $total;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $total;
        }

        foreach ($monthlyData as $month => $totalBulanIni) {
            $monthlyProgress[$month] = round(($totalBulanIni / $targetKelas) * 100, 2);
        }

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $day => $totalHariIni) {
                $dailyProgressPerMonth[$month][$day] = round(($totalHariIni / $targetKelas) * 100, 2);
            }
            ksort($dailyBreakdownPerMonth[$month]);
            ksort($dailyProgressPerMonth[$month]);
        }

        ksort($monthlyData);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgress);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $totalKelasInternal, 'below' => max(0, $targetKelas - $totalKelasInternal)],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
            'class_breakdown' => ['internal' => $totalKelasInternal, 'freelance' => $totalKelasFreelance],
        ];
    }

    public function calculateEvaluasiKinerjaInstruktur($item, $personId = null)
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

        $instruktursQuery = karyawan::where('Divisi', '!=', 'Direksi')
            ->where('status_aktif', '1')
            ->whereIn('jabatan', ['Instruktur', 'Education Manager'])
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNotNull('nip');

        if ($personId !== null) {
            $instruktursQuery->where('id', $personId);
        }

        $instrukturs = $instruktursQuery->get();
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

        $instrukturIds = $instrukturs->pluck('id');
        
        $activitiesByDate = ActivityInstruktur::whereYear('activity_date', $tahun)
            ->whereIn('user_id', $instrukturIds)
            ->selectRaw('DATE(activity_date) as tanggal, COUNT(DISTINCT user_id) as aktif_count')
            ->groupBy('tanggal')
            ->pluck('aktif_count', 'tanggal')
            ->toArray();

        $totalHariKerja = 0;
        $totalAktif = 0;

        foreach ($period as $date) {
            $dateKey = $date->toDateString();

            if ($date->isWeekend() || in_array($dateKey, $liburNasional)) {
                continue;
            }

            ++$totalHariKerja;
            if (isset($activitiesByDate[$dateKey])) {
                $totalAktif += $activitiesByDate[$dateKey];
            }
        }

        $totalKemungkinan = $totalHariKerja * $instrukturs->count();

        if ($totalKemungkinan == 0) {
            return 0;
        }

        return round(($totalAktif / $totalKemungkinan) * 100, 2);
    }

    public function calculateEvaluasiKinerjaInstrukturDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();
        $emptyResponse = [
            'progress' => 0, 'gap' => 0, 'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [], 'daily_breakdown_per_month' => [],
            'monthly_progress' => [], 'daily_progress_per_month' => [],
        ];

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $emptyResponse;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $emptyResponse;
        }

        $instruktursQuery = karyawan::where('Divisi', '!=', 'Direksi')
            ->where('status_aktif', '1')
            ->whereIn('jabatan', ['Instruktur', 'Education Manager'])
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNotNull('nip');

        if ($personId !== null) {
            $instruktursQuery->where('id', $personId);
        }

        $instrukturs = $instruktursQuery->get();
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

        $instrukturIds = $instrukturs->pluck('id');
        $countInstrukturs = $instrukturs->count();

        $activitiesByDate = ActivityInstruktur::whereYear('activity_date', $tahun)
            ->whereIn('user_id', $instrukturIds)
            ->selectRaw('DATE(activity_date) as tanggal, COUNT(DISTINCT user_id) as aktif_count')
            ->groupBy('tanggal')
            ->pluck('aktif_count', 'tanggal')
            ->toArray();

        $totalHariKerja = 0;
        $totalAktif = 0;
        $dailyValues = [];

        foreach ($period as $date) {
            $dateKey = $date->toDateString();

            if ($date->isWeekend() || in_array($dateKey, $liburNasional)) {
                continue;
            }

            ++$totalHariKerja;
            $aktifHariIni = $activitiesByDate[$dateKey] ?? 0;
            $totalAktif += $aktifHariIni;
            $dailyValues[$dateKey] = $aktifHariIni;
        }

        $totalKemungkinan = $totalHariKerja * $countInstrukturs;
        if ($totalKemungkinan == 0) {
            return $emptyResponse;
        }

        $progress = round(($totalAktif / $totalKemungkinan) * 100, 2);
        $gapRaw = $progress - 100;
        $gap = rtrim(rtrim(sprintf('%.2f', $gapRaw), '0'), '.');

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];

        foreach ($dailyValues as $dateStr => $total) {
            $monthKey = Carbon::parse($dateStr)->format('Y-m');
            $monthlyData[$monthKey][] = $total;
            $dailyBreakdownPerMonth[$monthKey][$dateStr] = $total;
        }

        $monthlyAverages = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($monthlyData as $month => $vals) {
            $avg = array_sum($vals) / count($vals);
            $monthlyAverages[$month] = round($avg, 2);
            $monthlyProgress[$month] = round(($avg / $countInstrukturs) * 100, 2);
        }

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $day => $totalHariIni) {
                $dailyProgressPerMonth[$month][$day] = round(($totalHariIni / $countInstrukturs) * 100, 2);
            }
            ksort($dailyBreakdownPerMonth[$month]);
            ksort($dailyProgressPerMonth[$month]);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgress);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $totalAktif, 'below' => max(0, $totalKemungkinan - $totalAktif)],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function getFilteredArticles()
    {
        $apiUrl = 'https://inixindobdg.co.id/api/articles';
        $response = Http::get($apiUrl);
        $articles = collect();

        if ($response->successful()) {
            $apiData = $response->json()['data'] ?? [];

            $educationUsers = User::with('karyawan')
                ->whereHas('karyawan', fn($q) => $q->where('divisi', 'Education'))
                ->get();

            $userMap = [];
            foreach ($educationUsers as $user) {
                $name = strtolower(trim($user->karyawan->nama_lengkap ?? ''));
                if ($name && !is_null($user->id_instruktur)) {
                    $userMap[$name] = $user->karyawan->nama_lengkap;
                }
            }

            foreach ($apiData as $item) {
                $pembuat = strtolower(trim($item['pembuat'] ?? ''));
                if (isset($userMap[$pembuat])) {
                    $item['nama_lengkap_pembuat'] = $userMap[$pembuat];
                    $articles->push($item);
                }
            }
        }

        return [
            'status' => 'success',
            'message' => 'Data artikel berhasil diproses',
            'data' => $articles,
        ];
    }

    public function calculatePembuatanArtikel($item, $personId)
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

        $startDate = Carbon::create($tahun, 1, 1);
        $endDate = Carbon::create($tahun, 12, 31);

        $response = $this->getFilteredArticles();
        $apiArtikel = collect($response['data'] ?? []);

        $totalData = $apiArtikel->filter(function ($article) use ($startDate, $endDate) {
            $tanggal = Carbon::parse($article['tanggal']);
            return $tanggal->between($startDate, $endDate);
        })->count();

        if ($totalData == 0) {
            return 0;
        }

        return round(($totalData / 24) * 100, 2);
    }

    public function calculatePembuatanArtikelDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();
        $emptyResponse = [
            'progress' => 0, 'gap' => -24, 'pie_chart' => ['above' => 0, 'below' => 24],
            'monthly_data' => [], 'daily_breakdown_per_month' => [],
            'monthly_progress' => [], 'daily_progress_per_month' => [],
        ];

        if (!$detail || !$detail->detail_jangka) {
            return $emptyResponse;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return $emptyResponse;
        }

        $startDate = Carbon::create($tahun, 1, 1);
        $endDate = Carbon::create($tahun, 12, 31);

        $response = $this->getFilteredArticles();
        $apiArtikel = collect($response['data'] ?? []);

        $getData = $apiArtikel->filter(function ($article) use ($startDate, $endDate) {
            $tanggal = Carbon::parse($article['tanggal']);
            return $tanggal->between($startDate, $endDate);
        });

        $totalData = $getData->count();

        if ($totalData == 0) {
            return $emptyResponse;
        }

        $progress = round(($totalData / 24) * 100, 2);
        $gap = $totalData - 24;

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($getData as $article) {
            $tanggal = Carbon::parse($article['tanggal']);
            $monthKey = $tanggal->format('Y-m');
            $dayKey = $tanggal->format('Y-m-d');

            $monthlyData[$monthKey] = ($monthlyData[$monthKey] ?? 0) + 1;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = ($dailyBreakdownPerMonth[$monthKey][$dayKey] ?? 0) + 1;
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
            'pie_chart' => ['above' => $totalData, 'below' => max(0, 24 - $totalData)],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }
}