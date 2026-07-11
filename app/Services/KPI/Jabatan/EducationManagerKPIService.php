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

    public function calculatePengembanganKurikulumPelatihanDetail($itemDetail)
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

    // PERBAIKAN: Terapkan filter personId jika ada (untuk dashboard personal)
    $queryMateri = ActivityInstruktur::whereYear('activity_date', $tahun)
        ->where('activity_type', 'Sharing Knowledge');

    if ($personId !== null) {
        $queryMateri->where('user_id', $personId);
    }

    $dataMateri = $queryMateri->get();
    $totalMingguDalamTahun = Carbon::create($tahun, 1, 1)->weeksInYear;
    $mingguYangSudahJalan = [];

    foreach ($dataMateri as $activity) {
        $nomorMinggu = Carbon::parse($activity->activity_date)->week;
        $mingguYangSudahJalan[$nomorMinggu] = true;
    }

    $jumlahMingguTerisi = count($mingguYangSudahJalan);

    // Kembalikan angka mentah jumlah minggunya, jangan dijadikan persentase
    return $jumlahMingguTerisi; 
}
//     if ($totalMingguDalamTahun == 0) {
//         return 0;
//     }

//     // PERBAIKAN: Rumus persentase yang benar ( (Terisi / Total Target) * 100 )
//     $progress = ($jumlahMingguTerisi / $totalMingguDalamTahun) * 100;
    
//     // Opsional: Jika Anda menggunakan nilai target custom dari input user
//     // $nilaiTarget = (float) $detail->nilai_target;
//     // $progress = $nilaiTarget > 0 ? ($jumlahMingguTerisi / $nilaiTarget) * 100 : 0;

//     return round(min($progress, 100), 1);
// }

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

    // PERBAIKAN: Terapkan filter personId agar sinkron dengan Primer
    $queryMateri = ActivityInstruktur::whereYear('activity_date', $tahun)
        ->where('activity_type', 'Sharing Knowledge');

    if ($personId !== null) {
        $queryMateri->where('user_id', $personId);
    }

    $dataMateri = $queryMateri->get();
    $totalMingguDalamTahun = Carbon::create($tahun, 1, 1)->weeksInYear;

    if ($dataMateri->isEmpty()) {
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

    foreach ($dataMateri as $activity) {
        $tanggal = Carbon::parse($activity->activity_date);
        
        // Catat untuk perhitungan utama (Berdasarkan Minggu)
        $nomorMinggu = $tanggal->week;
        $mingguYangSudahJalan[$nomorMinggu] = true;

        // Catat untuk breakdown Chart Harian dan Bulanan (Akumulasi sesi, bukan rata-rata)
        $dateKey = $tanggal->format('Y-m-d');
        $monthKey = $tanggal->format('Y-m');
        
        $dailyValues[$dateKey] = ($dailyValues[$dateKey] ?? 0) + 1;
        $monthlyData[$monthKey] = ($monthlyData[$monthKey] ?? 0) + 1;
    }

    $jumlahMingguTerisi = count($mingguYangSudahJalan);
    
    // Perhitungan progress menggunakan max weeks (atau bisa diganti menggunakan $nilaiTarget)
    $progressRaw = $totalMingguDalamTahun > 0 ? ($jumlahMingguTerisi / $totalMingguDalamTahun) * 100 : 0;
    $progress = $jumlahMingguTerisi;

    // Hitung gap terhadap persentase yang dimasukkan (asumsi input user adalah persen)
    $gapRaw = $progress - $nilaiTarget; 
    $gap = $progress - $nilaiTarget;

    // Pie chart berbasis jumlah minggu (bukan jumlah sesi)
    $above = $jumlahMingguTerisi;
    $below = max(0, $totalMingguDalamTahun - $jumlahMingguTerisi);

    $dailyBreakdownPerMonth = [];
    $dailyProgressPerMonth = [];
    $monthlyProgress = [];

    // Format ulang array untuk output chart
    foreach ($dailyValues as $dateStr => $totalSesiHariIni) {
        $date = Carbon::parse($dateStr);
        $monthKey = $date->format('Y-m');
        
        $dailyBreakdownPerMonth[$monthKey][$dateStr] = $totalSesiHariIni;
        // Progress chart harian & bulanan dibiarkan nol atau diset total absolut agar bar chart tidak pecah
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
        'pie_chart' => ['above' => $above, 'below' => $below],
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

        // PERBAIKAN: Beri toleransi untuk kolom NULL pada instruktur_key2 dan asisten_key
        $totalKelasInternal = RKM::where('tanggal_awal', '<=', $endDate)
            ->where('tanggal_akhir', '>=', $startDate)
            ->whereNotNull('instruktur_key')
            ->where('instruktur_key', '!=', '-')
            ->where('instruktur_key', '!=', 'OL')
            ->where(function ($query) {
                $query->where('instruktur_key2', '!=', 'OL')
                    ->orWhereNull('instruktur_key2');
            })
            ->where(function ($query) {
                $query->where('asisten_key', '!=', 'OL')
                    ->orWhereNull('asisten_key');
            })
            ->count(); 

        if ($targetKelas <= 0) {
            return 0.0;
        }

        $progress = ($totalKelasInternal / $targetKelas) * 100;
        
        return round($progress, 2);
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

        // PERUBAHAN UTAMA: $endDate disamakan persis dengan fungsi primer (Full 1 Tahun)
        // Dulu: $endDate = min(Carbon::create($tahun, 12, 31)->endOfDay(), now());
        $startDate = Carbon::create($tahun, 1, 1)->startOfDay();
        $endDate = Carbon::create($tahun, 12, 31)->endOfDay(); 

        if ($startDate > $endDate) {
            return $emptyResponse;
        }

        // Ambil data RKM dalam setahun
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
            
            // Lewati jika tanggal kelas di luar batas akhir
            if ($classDate > $endDate) continue;

            $dateKey = $classDate->format('Y-m-d');
            
            // Pengecekan Instruktur Freelance (Orang Lain)
            $isFreelance = ($rkm->instruktur_key === 'OL' || $rkm->instruktur_key2 === 'OL' || $rkm->asisten_key === 'OL');

            if ($isFreelance) {
                $totalKelasFreelance++;
            } else {
                // Hanya kelas Internal yang dihitung sebagai progress (sama seperti Primer)
                $totalKelasInternal++; 
                $dailyValues[$dateKey] = ($dailyValues[$dateKey] ?? 0) + 1;
            }
        }

        // Kalkulasi Progress menggunakan totalKelasInternal (Sinkron dengan Primer)
        $progress = round(($totalKelasInternal / $targetKelas) * 100, 2);
        
        // Kalkulasi Gap
        $gapRaw = $progress - 100;
        $gap = rtrim(rtrim(sprintf('%.2f', $gapRaw), '0'), '.');

        // Kalkulasi Pie Chart
        $above = $totalKelasInternal;
        $below = max(0, $targetKelas - $totalKelasInternal);

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];

        // Grouping Akumulasi per Bulan (Hanya untuk kelas Internal)
        foreach ($dailyValues as $dateStr => $total) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $monthlyData[$monthKey] = ($monthlyData[$monthKey] ?? 0) + $total;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $total;
        }

        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        // Progress Bulanan & Harian
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

        // Sorting agar berurutan dari Januari -> Desember
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
            'class_breakdown' => [
                'internal' => $totalKelasInternal,
                'freelance' => $totalKelasFreelance
            ],
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

        // PENYAMAAN FILTER KARYAWAN (Harus 100% sama dengan Detail)
        $instruktursQuery = karyawan::where('Divisi', '!=', 'Direksi')
            ->where('status_aktif', '1')
            ->where('jabatan', 'Instruktur')
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

        $activities = ActivityInstruktur::whereYear('activity_date', $tahun)
            ->whereIn('user_id', $instrukturs->pluck('id'))
            ->get()
            ->groupBy(function ($act) {
                return $act->user_id . '_' . Carbon::parse($act->activity_date)->format('Y-m-d');
            });

        $totalHariKerja = 0;
        $totalAktif = 0;

        foreach ($period as $date) {
            $dateKey = $date->toDateString();

            if ($date->isWeekend() || in_array($dateKey, $liburNasional)) {
                continue;
            }

            $totalHariKerja++;

            foreach ($instrukturs as $instruktur) {
                $key = $instruktur->id . '_' . $dateKey;
                if (isset($activities[$key])) {
                    $totalAktif++;
                }
            }
        }

        $totalKemungkinan = $totalHariKerja * $instrukturs->count();

        if ($totalKemungkinan == 0) {
            return 0;
        }

        $progress = ($totalAktif / $totalKemungkinan) * 100;

        return round($progress, 2);
    }

    // PERBAIKAN: Tambahkan parameter $personId di fungsi detail
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

        // PERBAIKAN: Sinkronisasi filter query karyawan dengan Primer
        $instruktursQuery = karyawan::where('Divisi', '!=', 'Direksi')
            ->where('status_aktif', '1')
            ->where('jabatan', 'Instruktur')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%') // Pastikan konsisten dengan rule OL jika perlu
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

        if ($startDate > $endDate) return $emptyResponse;

        $period = CarbonPeriod::create($startDate, $endDate);
        $liburNasional = HariLibur::whereBetween('tanggal', [$startDate, $endDate])
            ->pluck('tanggal')
            ->map(fn($tanggal) => Carbon::parse($tanggal)->toDateString())
            ->toArray();

        // PERBAIKAN: Tambahkan whereIn agar tidak membebani memori server
        $activities = ActivityInstruktur::whereYear('activity_date', $tahun)
            ->whereIn('user_id', $instrukturs->pluck('id'))
            ->get()
            ->groupBy(function ($act) {
                return $act->user_id . '_' . Carbon::parse($act->activity_date)->format('Y-m-d');
            });

        $totalHariKerja = 0;
        $totalAktif = 0;
        $dailyValues = [];
        
        // Simpan jumlah instruktur ke variabel agar tidak dipanggil berulang kali di dalam loop
        $countInstrukturs = $instrukturs->count(); 

        foreach ($period as $date) {
            $dateKey = $date->toDateString();

            if ($date->isWeekend() || in_array($dateKey, $liburNasional)) {
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

        $totalKemungkinan = $totalHariKerja * $countInstrukturs;
        if ($totalKemungkinan == 0) return $emptyResponse;

        $progress = round(($totalAktif / $totalKemungkinan) * 100, 2);
        $gapRaw = $progress - 100;
        $gap = rtrim(rtrim(sprintf('%.2f', $gapRaw), '0'), '.');

        $above = $totalAktif;
        $below = max(0, $totalKemungkinan - $totalAktif);

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
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    public function calculatePembuatanArtikel($item, $personId) {
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

    public function calculatePembuatanArtikelDetail($itemDetail) {
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
