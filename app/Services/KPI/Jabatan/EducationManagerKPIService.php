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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EducationManagerKPIService
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

    public function calculatePengembanganKurikulumPelatihan($item, $personId = null)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return 0.0;
        }

        $tahun = (int) $detail->detail_jangka;
        $nilaiTarget = (float) $detail->nilai_target;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return 0.0;
        }

        $bulanYangAdaMateri = Materi::whereYear('created_at', $tahun)
            ->selectRaw('COUNT(DISTINCT MONTH(created_at)) as count')
            ->value('count') ?? 0;

        return $nilaiTarget > 0 ? round(($bulanYangAdaMateri / $nilaiTarget) * 100, 1) : 0.0;
    }

    public function calculatePengembanganKurikulumPelatihanDetail($itemDetail, $personId = null)
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

        $bulanYangAdaMateri = Materi::whereYear('created_at', $tahun)
            ->selectRaw('COUNT(DISTINCT MONTH(created_at)) as count')
            ->value('count') ?? 0;

        $progress = $nilaiTarget > 0 ? round(($bulanYangAdaMateri / $nilaiTarget) * 100, 1) : 0.0;
        $gap = $this->calculateAndFormatGap($progress, $nilaiTarget);

        $monthlyData = [];
        $monthlyProgress = [];
        $dailyBreakdownPerMonth = [];
        $dailyProgressPerMonth = [];

        for ($m = 1; $m <= 12; $m++) {
            $monthKey = "{$tahun}-" . str_pad($m, 2, '0', STR_PAD_LEFT);
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
            'pie_chart' => [
                'above' => $bulanYangAdaMateri,
                'below' => max(0, 12 - $bulanYangAdaMateri),
            ],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    public function calculatePeningkatanKnowledgeSharing($item, $personId = null)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return 0.0;
        }

        $tahun = (int) $detail->detail_jangka;
        $nilaiTarget = (float) $detail->nilai_target;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return 0.0;
        }

        $jumlahMingguTerisi = ActivityInstruktur::whereYear('activity_date', $tahun)
            ->where('activity_type', 'Sharing Knowledge')
            ->selectRaw('COUNT(DISTINCT WEEK(activity_date)) as count')
            ->value('count') ?? 0;

        return $nilaiTarget > 0 ? round(($jumlahMingguTerisi / $nilaiTarget) * 100, 1) : 0.0;
    }

    public function calculatePeningkatanKnowledgeSharingDetail($itemDetail, $personId = null)
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

        $totalMingguDalamTahun = Carbon::create($tahun, 1, 1)->weeksInYear;

        $activities = ActivityInstruktur::select('activity_date')
            ->whereYear('activity_date', $tahun)
            ->where('activity_type', 'Sharing Knowledge')
            ->get();

        if ($activities->isEmpty()) {
            return array_merge($this->getDefaultDetailResponse(), [
                'pie_chart' => ['above' => 0, 'below' => $totalMingguDalamTahun],
            ]);
        }

        $mingguYangSudahJalan = [];
        $monthlyData = [];
        $dailyValues = [];

        foreach ($activities as $activity) {
            $tanggal = Carbon::parse($activity->activity_date);
            $mingguYangSudahJalan[$tanggal->week] = true;
            
            $monthKey = $tanggal->format('Y-m');
            $dayKey = $tanggal->format('Y-m-d');
            
            $dailyValues[$dayKey] = ($dailyValues[$dayKey] ?? 0) + 1;
            $monthlyData[$monthKey] = ($monthlyData[$monthKey] ?? 0) + 1;
        }

        $jumlahMingguTerisi = count($mingguYangSudahJalan);
        $progress = $nilaiTarget > 0 ? round(($jumlahMingguTerisi / $nilaiTarget) * 100, 1) : 0.0;
        $gap = $this->calculateAndFormatGap($progress, $nilaiTarget);

        $monthlyProgress = [];
        $dailyProgressPerMonth = [];
        $dailyBreakdownPerMonth = [];

        foreach ($dailyValues as $dateStr => $totalSesiHariIni) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            
            $dailyBreakdownPerMonth[$monthKey][$dateStr] = $totalSesiHariIni;
            $dailyProgressPerMonth[$monthKey][$dateStr] = $nilaiTarget > 0 ? round(($totalSesiHariIni / $nilaiTarget) * 100, 1) : 0.0;
        }

        foreach ($monthlyData as $month => $totalSesiBulanIni) {
            $monthlyProgress[$month] = $nilaiTarget > 0 ? round(($totalSesiBulanIni / $nilaiTarget) * 100, 1) : 0.0;
        }

        ksort($monthlyData);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgress);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $jumlahMingguTerisi,
                'below' => max(0, $totalMingguDalamTahun - $jumlahMingguTerisi),
            ],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    public function calculatePeningkatanKontribusiPelatihan($item, $personId = null)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return 0.0;
        }

        $tahun = (int) $detail->detail_jangka;
        $nilaiTarget = (float) $detail->nilai_target;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return 0.0;
        }

        $startDate = Carbon::create($tahun, 1, 1)->startOfDay();
        $endDate = Carbon::create($tahun, 12, 31)->endOfDay();

        $totalKelasInternal = RKM::whereBetween('tanggal_awal', [$startDate, $endDate])
            ->whereNotNull('instruktur_key')
            ->where('instruktur_key', '!=', '-')
            ->where(function ($q) {
                $q->where('instruktur_key', '!=', 'OL')
                  ->where('instruktur_key2', '!=', 'OL')
                  ->where('asisten_key', '!=', 'OL');
            })
            ->when($personId, fn($q) => $q->where('instruktur_key', $personId)) 
            ->count();

        return $nilaiTarget > 0 ? round(($totalKelasInternal / $nilaiTarget) * 100, 2) : 0.0;
    }

    public function calculatePeningkatanKontribusiPelatihanDetail($itemDetail, $personId = null)
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

        $startDate = Carbon::create($tahun, 1, 1)->startOfDay();
        $endDate = Carbon::create($tahun, 12, 31)->endOfDay();

        $totalKelasInternal = 0;
        $totalKelasFreelance = 0;
        $dailyValues = [];

        $rkms = RKM::select('tanggal_awal', 'instruktur_key', 'instruktur_key2', 'asisten_key')
            ->where('tanggal_awal', '<=', $endDate)
            ->where('tanggal_akhir', '>=', $startDate)
            ->whereNotNull('instruktur_key')
            ->where('instruktur_key', '!=', '-')
            ->when($personId, fn($q) => $q->where('instruktur_key', $personId))
            ->cursor();

        foreach ($rkms as $rkm) {
            $classDate = Carbon::parse($rkm->tanggal_awal);
            if ($classDate > $endDate) continue;

            $dateKey = $classDate->format('Y-m-d');
            $isFreelance = ($rkm->instruktur_key === 'OL' || $rkm->instruktur_key2 === 'OL' || $rkm->asisten_key === 'OL');

            if ($isFreelance) {
                $totalKelasFreelance++;
            } else {
                $totalKelasInternal++;
                $dailyValues[$dateKey] = ($dailyValues[$dateKey] ?? 0) + 1;
            }
        }

        $progress = $nilaiTarget > 0 ? round(($totalKelasInternal / $nilaiTarget) * 100, 2) : 0.0;
        $gap = $this->calculateAndFormatGap($progress, $nilaiTarget);

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
            $monthlyProgress[$month] = $nilaiTarget > 0 ? round(($totalBulanIni / $nilaiTarget) * 100, 2) : 0.0;
        }

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $day => $totalHariIni) {
                $dailyProgressPerMonth[$month][$day] = $nilaiTarget > 0 ? round(($totalHariIni / $nilaiTarget) * 100, 2) : 0.0;
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
            'pie_chart' => [
                'above' => $totalKelasInternal,
                'below' => max(0, ceil($nilaiTarget) - $totalKelasInternal),
            ],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
            'class_breakdown' => [
                'internal' => $totalKelasInternal,
                'freelance' => $totalKelasFreelance
            ],
        ];
    }

    public function calculateEvaluasiKinerjaInstruktur($item, $personId = null)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return 0.0;
        }

        $tahun = (int) $detail->detail_jangka;
        $nilaiTarget = (float) $detail->nilai_target;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return 0.0;
        }

        $countInstrukturs = karyawan::where('Divisi', '!=', 'Direksi')
            ->where('status_aktif', '1')
            ->whereIn('jabatan', ['Instruktur', 'Education Manager'])
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNotNull('nip')
            ->when($personId, fn($q) => $q->where('id', $personId))
            ->count();

        if ($countInstrukturs === 0) return 0.0;

        $startDate = Carbon::create($tahun, 1, 1);
        $endDate = Carbon::create($tahun, 12, 31);

        $liburNasional = HariLibur::whereYear('tanggal', $tahun)->pluck('tanggal')
            ->map(fn($tanggal) => Carbon::parse($tanggal)->toDateString())
            ->toArray();

        $totalHariKerja = 0;
        $workingDays = [];
        $period = CarbonPeriod::create($startDate, $endDate);
        
        foreach ($period as $date) {
            if (!$date->isWeekend() && !in_array($date->toDateString(), $liburNasional)) {
                $totalHariKerja++;
                $workingDays[] = $date->toDateString();
            }
        }

        $totalKemungkinan = $totalHariKerja * $countInstrukturs;
        if ($totalKemungkinan == 0) return 0.0;

        $totalAktif = ActivityInstruktur::whereIn('activity_date', $workingDays)
            ->when($personId, fn($q) => $q->where('user_id', $personId))
            ->count();

        return round(($totalAktif / $totalKemungkinan) * 100, 2);
    }

    public function calculateEvaluasiKinerjaInstrukturDetail($itemDetail, $personId = null)
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

        $instrukturIds = karyawan::select('id')
            ->where('Divisi', '!=', 'Direksi')
            ->where('status_aktif', '1')
            ->whereIn('jabatan', ['Instruktur', 'Education Manager'])
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNotNull('nip')
            ->when($personId, fn($q) => $q->where('id', $personId))
            ->pluck('id')->toArray();

        $countInstrukturs = count($instrukturIds);
        if ($countInstrukturs === 0) return $this->getDefaultDetailResponse();

        $startDate = Carbon::create($tahun, 1, 1);
        $endDate = min(Carbon::create($tahun, 12, 31), now());

        if ($startDate > $endDate) return $this->getDefaultDetailResponse();

        $liburNasional = HariLibur::whereBetween('tanggal', [$startDate, $endDate])
            ->pluck('tanggal')
            ->map(fn($tanggal) => Carbon::parse($tanggal)->toDateString())
            ->toArray();

        $activitiesByDate = ActivityInstruktur::select('activity_date')
            ->whereYear('activity_date', $tahun)
            ->whereIn('user_id', $instrukturIds)
            ->get()
            ->map(fn($a) => Carbon::parse($a->activity_date)->toDateString())
            ->countBy();

        $monthlyData = [];
        $monthlyProgress = [];
        $dailyBreakdownPerMonth = [];
        $dailyProgressPerMonth = [];
        $totalAktif = 0;

        $workingDaysByMonth = [];
        $period = CarbonPeriod::create($startDate, $endDate);
        foreach ($period as $date) {
            if (!$date->isWeekend() && !in_array($date->toDateString(), $liburNasional)) {
                $monthKey = $date->format('Y-m');
                $workingDaysByMonth[$monthKey][] = $date->toDateString();
            }
        }

        foreach ($workingDaysByMonth as $month => $days) {
            $totalWorkingDaysInMonth = count($days);
            $totalAktifInMonth = 0;

            foreach ($days as $day) {
                $aktifHariIni = $activitiesByDate[$day] ?? 0;
                $totalAktifInMonth += $aktifHariIni;
                $totalAktif += $aktifHariIni;
                
                $dailyBreakdownPerMonth[$month][$day] = $aktifHariIni;
                $dailyProgressPerMonth[$month][$day] = $countInstrukturs > 0 
                    ? round(($aktifHariIni / $countInstrukturs) * 100, 2) 
                    : 0.0;
            }

            $monthlyData[$month] = $totalAktifInMonth; 
            $monthlyProgress[$month] = ($totalWorkingDaysInMonth * $countInstrukturs) > 0 
                ? round(($totalAktifInMonth / ($totalWorkingDaysInMonth * $countInstrukturs)) * 100, 2) 
                : 0.0;
        }

        $totalKemungkinan = 0;
        foreach ($workingDaysByMonth as $days) {
            $totalKemungkinan += count($days) * $countInstrukturs;
        }

        $progress = $totalKemungkinan > 0 ? round(($totalAktif / $totalKemungkinan) * 100, 2) : 0.0;
        $gap = $this->calculateAndFormatGap($progress, $nilaiTarget);

        foreach ($dailyBreakdownPerMonth as $month => $days) {
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
            'pie_chart' => [
                'above' => $totalAktif,
                'below' => max(0, $totalKemungkinan - $totalAktif),
            ],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    public function calculatePembuatanArtikel($item, $personId = null)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return 0.0;
        }

        $tahun = (int) $detail->detail_jangka;
        $nilaiTarget = (float) $detail->nilai_target;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return 0.0;
        }

        $startDate = Carbon::create($tahun, 1, 1)->toDateString();
        $endDate = Carbon::create($tahun, 12, 31)->toDateString();

        $cacheKey = "articles_count_{$tahun}" . ($personId ? "_{$personId}" : "");
        
        $totalData = Cache::remember($cacheKey, 3600, function () use ($tahun, $startDate, $endDate, $personId) {
            $response = Http::get('https://inixindobdg.co.id/api/articles', [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            if (!$response->successful()) {
                return 0;
            }

            $apiData = $response->json()['data'] ?? [];
            
            $filteredData = collect($apiData)->filter(function ($article) use ($startDate, $endDate) {
                $tanggal = Carbon::parse($article['tanggal']);
                return $tanggal->between($startDate, $endDate);
            });

            if ($filteredData->isEmpty()) {
                return 0;
            }

            $pembuatNames = $filteredData->pluck('pembuat')->filter()->unique()->toArray();

            if (empty($pembuatNames)) {
                return 0;
            }

            $users = User::with('karyawan')
                ->whereHas('karyawan', function ($query) use ($pembuatNames) {
                    $query->whereIn('nama_lengkap', $pembuatNames)
                          ->where('divisi', 'Education');
                })
                ->whereNotNull('id_instruktur')
                ->get()
                ->keyBy(function ($user) {
                    return $user->karyawan->nama_lengkap ?? '';
                });

            $validArticlesCount = 0;
            foreach ($filteredData as $item) {
                $pembuat = $item['pembuat'] ?? '';
                if (isset($users[$pembuat])) {
                    $validArticlesCount++;
                }
            }

            return $validArticlesCount;
        });

        return $nilaiTarget > 0 ? round(($totalData / $nilaiTarget) * 100, 2) : 0.0;
    }

    public function calculatePembuatanArtikelDetail($itemDetail, $personId = null)
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

        $startDate = Carbon::create($tahun, 1, 1);
        $endDate = Carbon::create($tahun, 12, 31);

        $response = $this->getFilteredArticlesOptimized($tahun, $personId);
        $getData = collect($response['data'] ?? []);

        $totalData = $getData->count();

        if ($totalData === 0) {
            return array_merge($this->getDefaultDetailResponse(), [
                'pie_chart' => ['above' => 0, 'below' => ceil($nilaiTarget)],
            ]);
        }

        $progress = round(($totalData / $nilaiTarget) * 100, 2);
        $gap = $this->calculateAndFormatGap($progress, $nilaiTarget);

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
            $monthlyProgress[$month] = round(($count / $nilaiTarget) * 100, 2);
            foreach ($dailyBreakdownPerMonth[$month] as $day => $dailyCount) {
                $dailyProgressPerMonth[$month][$day] = round(($dailyCount / $nilaiTarget) * 100, 2);
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
                'below' => max(0, ceil($nilaiTarget) - $totalData),
            ],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function getFilteredArticlesOptimized($tahun, $personId = null)
    {
        $cacheKey = "articles_data_{$tahun}" . ($personId ? "_{$personId}" : "");
        
        return Cache::remember($cacheKey, 3600, function () use ($tahun, $personId) {
            $startDate = Carbon::create($tahun, 1, 1)->toDateString();
            $endDate = Carbon::create($tahun, 12, 31)->toDateString();

            $response = Http::get('https://inixindobdg.co.id/api/articles', [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            $articles = collect();

            if ($response->successful()) {
                $apiData = $response->json()['data'] ?? [];

                $filteredData = collect($apiData)->filter(function ($article) use ($startDate, $endDate) {
                    $tanggal = Carbon::parse($article['tanggal']);
                    return $tanggal->between($startDate, $endDate);
                });

                if ($filteredData->isEmpty()) {
                    return ['status' => 'success', 'message' => 'Data artikel berhasil diproses', 'data' => $articles];
                }

                $pembuatNames = $filteredData->pluck('pembuat')->filter()->unique()->toArray();

                if (!empty($pembuatNames)) {
                    $users = User::with('karyawan')
                        ->whereHas('karyawan', function ($query) use ($pembuatNames) {
                            $query->whereIn('nama_lengkap', $pembuatNames)
                                  ->where('divisi', 'Education');
                        })
                        ->whereNotNull('id_instruktur')
                        ->get()
                        ->keyBy(function ($user) {
                            return $user->karyawan->nama_lengkap ?? '';
                        });

                    foreach ($filteredData as $item) {
                        $pembuat = $item['pembuat'] ?? '';
                        if (isset($users[$pembuat])) {
                            $item['nama_lengkap_pembuat'] = $users[$pembuat]->karyawan->nama_lengkap ?? null;
                            $articles->push($item);
                        }
                    }
                }
            }

            return [
                'status'  => 'success',
                'message' => 'Data artikel berhasil diproses',
                'data'    => $articles
            ];
        });
    }
}