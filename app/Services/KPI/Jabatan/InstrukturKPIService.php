<?php

namespace App\Services\KPI\Jabatan;

use App\Models\ActivityInstruktur;
use App\Models\detailPersonKPI;
use App\Models\HariLibur;
use App\Models\karyawan;
use App\Models\Nilaifeedback;
use App\Models\Pelatihan;
use App\Models\pengajuancuti;
use App\Models\RekomendasiLanjutan;
use App\Models\RKM;
use App\Models\Sertifikasi;
use App\Traits\KPIDefaultResponseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class InstrukturKPIService
{
    use KPIDefaultResponseTrait;

    public function calculatePresentaseKinerjaInstruktur($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0.0;
        }

        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0.0;
            }

            $jamKerjaPerHari = 9;

        $today = Carbon::today();

        $startDate = Carbon::create($tahun, 1, 1)->startOfYear();

        /*
        |--------------------------------------------------------------------------
        | Jika KPI tahun sekarang -> hitung sampai hari ini
        | Jika KPI tahun lalu -> hitung sampai akhir tahun
        |--------------------------------------------------------------------------
        */
        $endDate = ($tahun == $today->year)
            ? $today
            : Carbon::create($tahun, 12, 31)->endOfYear();

        $liburNasional = HariLibur::pluck('tanggal')
            ->map(fn($t) => Carbon::parse($t)->toDateString())
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Hitung hari kerja dalam periode
        |--------------------------------------------------------------------------
        */
        $hariKerjaPeriode = 0;

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {

            if (
                !$date->isWeekend() &&
                !in_array($date->toDateString(), $liburNasional)
            ) {
                $hariKerjaPeriode++;
            }
        }

        $targetJamPerOrang = $hariKerjaPeriode * $jamKerjaPerHari;

        $totalJamMengajar = 0;

        if ($personId !== null) {
            $instrukturList = karyawan::where('id', $personId)->get();
        } else {
            $instrukturList = karyawan::where('status_aktif', '1')
                ->whereNot('jabatan', 'Outsource')
                ->where('kode_karyawan', 'NOT LIKE', 'OL%')
                ->whereNot('jabatan', 'Pilih Jabatan')
                ->whereNotNull('nip')
                ->whereNot('divisi', 'Direksi')
                ->where('jabatan', 'instruktur')
                ->get();
        }

        foreach ($instrukturList as $instruktur) {
            $kode = $instruktur->kode_karyawan;
            $idInstruktur = $instruktur->id;

            $activityDates = ActivityInstruktur::whereNull('id_rkm')
                ->whereBetween('activity_date', [$startDate, $endDate])
                ->whereHas('user', function ($q) use ($idInstruktur) {
                    $q->where('user_id', $idInstruktur);
                })
                ->pluck('activity_date')
                ->map(fn($date) => Carbon::parse($date)->toDateString())
                ->unique()
                ->toArray();

            $rkms = RKM::where('tanggal_awal', '<=', $endDate)
                ->where('tanggal_akhir', '>=', $startDate)
                ->where(function ($q) use ($kode) {
                    $q->where('instruktur_key', $kode)
                        ->orWhere('instruktur_key2', $kode)
                        ->orWhere('asisten_key', $kode);
                })->get();

            $rkmDates = [];
            foreach ($rkms as $rkm) {
                $rkmStart = Carbon::parse($rkm->tanggal_awal);
                $rkmEnd   = Carbon::parse($rkm->tanggal_akhir);

                $effectiveStart = $rkmStart->greaterThan($startDate) ? $rkmStart : $startDate;
                $effectiveEnd   = $rkmEnd->lessThan($endDate) ? $rkmEnd : $endDate;

                for ($date = $effectiveStart->copy(); $date->lte($effectiveEnd); $date->addDay()) {
                    $rkmDates[] = $date->toDateString();
                }
            }

            $allWorkingDays = array_unique(array_merge($activityDates, $rkmDates));

            $cutiDates = [];

            $cutis = pengajuancuti::where('id_karyawan', $instruktur->id)
                ->where('tanggal_awal', '<=', $endDate)
                ->where('tanggal_akhir', '>=', $startDate)
                ->get();

            foreach ($cutis as $cuti) {
                $cutiStart = Carbon::parse($cuti->tanggal_awal);
                $cutiEnd   = Carbon::parse($cuti->tanggal_akhir);

                $effectiveStart = $cutiStart->greaterThan($startDate) ? $cutiStart : $startDate;
                $effectiveEnd   = $cutiEnd->lessThan($endDate) ? $cutiEnd : $endDate;

                for ($date = $effectiveStart->copy(); $date->lte($effectiveEnd); $date->addDay()) {
                    $cutiDates[] = $date->toDateString();
                }
            }

            $cutiDates = array_unique($cutiDates);

            $allWorkingDays = array_diff($allWorkingDays, $cutiDates);

            $totalJamMengajar += count($allWorkingDays) * $jamKerjaPerHari;
        }

        $jumlahInstruktur = $instrukturList->count();

        $avgFactor = ($personId !== null || $jumlahInstruktur == 0) ? 1 : $jumlahInstruktur;

        $totalJamMengajarRataRata = $totalJamMengajar / $avgFactor;

        $targetJam = $targetJamPerOrang;

        if ($targetJam <= 0) {
            return 0.0;
        }

        $persentase = ($totalJamMengajarRataRata / $targetJam) * 100;

        return round($persentase, 2);
    }

    public function calculatePresentaseKinerjaInstrukturDetail($itemDetail, $personId)
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
            'instruktur_details' => [],
            'hari_libur_nasional' => ['jumlah' => 0, 'daftar' => []],
        ];

        if (!$detail || !$detail->nilai_target || !$detail->detail_jangka) {
            return $emptyResponse;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $emptyResponse;
        }

        $jamKerjaPerHari = 9;

        $today = Carbon::today();

        $startDate = Carbon::create($tahun, 1, 1)->startOfYear();

        /*
        |--------------------------------------------------------------------------
        | Jika KPI tahun sekarang -> hitung sampai hari ini
        | Jika KPI tahun lalu -> hitung sampai akhir tahun
        |--------------------------------------------------------------------------
        */
        $endDate = ($tahun == $today->year)
            ? $today
            : Carbon::create($tahun, 12, 31)->endOfYear();

        $liburNasional = HariLibur::pluck('tanggal')
            ->map(fn($t) => Carbon::parse($t)->toDateString())
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Hitung hari kerja dalam periode
        |--------------------------------------------------------------------------
        */
        $hariKerjaPeriode = 0;

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {

            if (
                !$date->isWeekend() &&
                !in_array($date->toDateString(), $liburNasional)
            ) {
                $hariKerjaPeriode++;
            }
        }

        $targetJamPerOrang = $hariKerjaPeriode * $jamKerjaPerHari;

        $totalJamMengajar = 0;
        $dailyValues = [];
        $instrukturDetails = [];

        $hariLiburNasionalList = HariLibur::whereBetween('tanggal', [$startDate, $endDate])
            ->get()
            ->mapWithKeys(function ($libur) {
                return [Carbon::parse($libur->tanggal)->toDateString() => $libur->keterangan ?? 'Hari Libur Nasional'];
            })
            ->toArray();

        if ($personId !== null) {
            $instrukturList = karyawan::where('id', $personId)->get();
        } else {
            $instrukturList = karyawan::where('status_aktif', '1')
                ->whereNot('jabatan', 'Outsource')
                ->where('kode_karyawan', 'NOT LIKE', 'OL%')
                ->whereNot('jabatan', 'Pilih Jabatan')
                ->whereNotNull('nip')
                ->whereNot('divisi', 'Direksi')
                ->where('jabatan', 'instruktur')
                ->get();
        }

        foreach ($instrukturList as $instruktur) {
            $kode = $instruktur->kode_karyawan;
            $idInstruktur = $instruktur->id;

            $activityDates = ActivityInstruktur::whereNull('id_rkm')
                ->whereBetween('activity_date', [$startDate, $endDate])
                ->whereHas('user', function ($q) use ($idInstruktur) {
                    $q->where('user_id', $idInstruktur);
                })
                ->pluck('activity_date')
                ->map(fn($date) => Carbon::parse($date)->toDateString())
                ->unique()
                ->toArray();

            $rkms = RKM::where('tanggal_awal', '<=', $endDate)
                ->where('tanggal_akhir', '>=', $startDate)
                ->where(function ($q) use ($kode) {
                    $q->where('instruktur_key', $kode)
                        ->orWhere('instruktur_key2', $kode)
                        ->orWhere('asisten_key', $kode);
                })->get();

            $rkmDates = [];
            foreach ($rkms as $rkm) {
                $rkmStart = Carbon::parse($rkm->tanggal_awal);
                $rkmEnd   = Carbon::parse($rkm->tanggal_akhir);

                $effectiveStart = $rkmStart->greaterThan($startDate) ? $rkmStart : $startDate;
                $effectiveEnd   = $rkmEnd->lessThan($endDate) ? $rkmEnd : $endDate;

                for ($date = $effectiveStart->copy(); $date->lte($effectiveEnd); $date->addDay()) {
                    $rkmDates[] = $date->toDateString();
                }
            }

            $allWorkingDays = array_values(array_unique(array_merge($activityDates, $rkmDates)));

            $cutiDates = [];
            $cutiDetailList = [];
            $cutis = pengajuancuti::where('id_karyawan', $instruktur->id)
                ->where('tanggal_awal', '<=', $endDate)
                ->where('tanggal_akhir', '>=', $startDate)
                ->get();

            foreach ($cutis as $cuti) {
                $cutiStart = Carbon::parse($cuti->tanggal_awal);
                $cutiEnd   = Carbon::parse($cuti->tanggal_akhir);

                $effectiveStart = $cutiStart->greaterThan($startDate) ? $cutiStart : $startDate;
                $effectiveEnd   = $cutiEnd->lessThan($endDate) ? $cutiEnd : $endDate;

                for ($date = $effectiveStart->copy(); $date->lte($effectiveEnd); $date->addDay()) {
                    $dateStr = $date->toDateString();
                    $cutiDates[] = $dateStr;
                    $cutiDetailList[$dateStr] = [
                        'alasan' => $cuti->alasan ?? 'Cuti',
                        'tipe' => $cuti->tipe ?? 'Cuti',
                        'tanggal_awal' => $cuti->tanggal_awal,
                        'tanggal_akhir' => $cuti->tanggal_akhir,
                    ];
                }
            }
            $cutiDates = array_values(array_unique($cutiDates));

            $allWorkingDays = array_values(array_diff($allWorkingDays, $cutiDates));

            $jamAktifInstruktur = count($allWorkingDays) * $jamKerjaPerHari;
            $totalJamMengajar += $jamAktifInstruktur;

            foreach ($allWorkingDays as $dateStr) {
                $dailyValues[$dateStr] = ($dailyValues[$dateStr] ?? 0) + $jamKerjaPerHari;
            }

            $persentaseInstruktur = $targetJamPerOrang > 0
                ? round(($jamAktifInstruktur / $targetJamPerOrang) * 100, 1)
                : 0;

            $daftarLiburPerInstruktur = [];
            foreach ($hariLiburNasionalList as $tgl => $ket) {
                if (Carbon::parse($tgl)->between($startDate, $endDate)) {
                    $daftarLiburPerInstruktur[$tgl] = $ket;
                }
            }

            $kalenderData = [];
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                $dateStr = $date->toDateString();
                $month = $date->format('Y-m');
                $day = $date->day;

                if ($date->isWeekend()) {
                    $status = 'weekend';
                    $keterangan = $date->isSaturday() ? 'Sabtu' : 'Minggu';
                } elseif (isset($hariLiburNasionalList[$dateStr])) {
                    $status = 'libur';
                    $keterangan = $hariLiburNasionalList[$dateStr];
                } elseif (in_array($dateStr, $cutiDates)) {
                    $status = 'cuti';
                    $keterangan = $cutiDetailList[$dateStr]['alasan'] ?? 'Cuti';
                } elseif (in_array($dateStr, $allWorkingDays)) {
                    $status = 'working';
                    $keterangan = 'Aktif (' . $jamKerjaPerHari . ' jam)';
                } else {
                    $status = 'empty';
                    $keterangan = 'Tidak ada aktivitas';
                }

                $kalenderData[$month][$day] = [
                    'status' => $status,
                    'keterangan' => $keterangan,
                    'tanggal' => $dateStr,
                ];
            }

            $instrukturDetails[] = [
                'id' => $instruktur->id,
                'nama' => $instruktur->nama_lengkap ?? '-',
                'kode_karyawan' => $kode,
                'jabatan' => $instruktur->jabatan ?? '-',
                'target_jam' => $targetJamPerOrang,
                'jam_aktif' => $jamAktifInstruktur,
                'persentase' => $persentaseInstruktur,
                'total_hari_kerja' => count($allWorkingDays),
                'total_hari_libur' => count($daftarLiburPerInstruktur),
                'total_hari_cuti' => count($cutiDates),
                'daftar_libur' => $daftarLiburPerInstruktur,
                'daftar_cuti' => $cutiDetailList,
                'kalender' => $kalenderData,
            ];
        }

        $jumlahInstruktur = $instrukturList->count();

        // Faktor pembagi: Jika personId tidak null, bagi dengan 1 (data utuh). Jika null, bagi dengan jumlah instruktur.
        $avgFactor = ($personId !== null || $jumlahInstruktur == 0) ? 1 : $jumlahInstruktur;

        $totalJamMengajarRataRata = $totalJamMengajar / $avgFactor;

        // Target jam untuk perbandingan persentase adalah target per orang
        $targetJam = $targetJamPerOrang;

        if ($targetJam <= 0) {
            return $emptyResponse;
        }

        $progress = round(min(100, ($totalJamMengajarRataRata / $targetJam) * 100), 1);
        $gap = round($progress - 100, 1);

        $above = $totalJamMengajarRataRata;
        $below = $personId ? 0 : max(0, $targetJam - $totalJamMengajarRataRata);

        $monthly = [];
        $dailyPerMonth = [];
        $monthlyProgress = [];
        $dailyProgress = [];

        foreach ($dailyValues as $dateStr => $jam) {
            $date = Carbon::parse($dateStr);
            $m = $date->format('Y-m');

            // Bagi dengan avgFactor untuk mendapatkan rata-rata jam per instruktur
            $jamRataRata = $jam / $avgFactor;

            $monthly[$m] = ($monthly[$m] ?? 0) + $jamRataRata;
            $dailyPerMonth[$m][$dateStr] = $jamRataRata;
        }

        foreach ($monthly as $month => $totalJam) {
            $monthlyProgress[$month] = $targetJam > 0
                ? round(($totalJam / $targetJam) * 100, 1)
                : 0;
        }

        foreach ($dailyPerMonth as $month => $days) {
            foreach ($days as $d => $val) {
                $dailyProgress[$month][$d] = $targetJam > 0
                    ? round(($val / $targetJam) * 100, 1)
                    : 0;
            }
        }

        ksort($monthly);
        ksort($dailyPerMonth);
        ksort($monthlyProgress);
        ksort($dailyProgress);

        if ($personId === null && $jumlahInstruktur > 0) {
            $avgHariKerja = array_sum(array_column($instrukturDetails, 'total_hari_kerja')) / $jumlahInstruktur;
            $avgHariCuti = array_sum(array_column($instrukturDetails, 'total_hari_cuti')) / $jumlahInstruktur;

            $instrukturDetails = [
                [
                    'id' => 0,
                    'nama' => 'Rata-rata Seluruh Instruktur',
                    'kode_karyawan' => '-',
                    'jabatan' => '-',
                    'target_jam' => $targetJamPerOrang,
                    'jam_aktif' => round($totalJamMengajarRataRata, 1),
                    'persentase' => $progress,
                    'total_hari_kerja' => round($avgHariKerja, 1),
                    'total_hari_libur' => count($hariLiburNasionalList),
                    'total_hari_cuti' => round($avgHariCuti, 1),
                    'daftar_libur' => $hariLiburNasionalList,
                    'daftar_cuti' => [],
                    'kalender' => [],
                ]
            ];
        }

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => round($above, 1),
                'below' => round($below, 1)
            ],
            'monthly_data' => $monthly,
            'daily_breakdown_per_month' => $dailyPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgress,
            'instruktur_details' => $instrukturDetails,
            'hari_libur_nasional' => [
                'jumlah' => count($hariLiburNasionalList),
                'daftar' => $hariLiburNasionalList,
            ],
        ];
    }

    public function calculateKepuasanPesertaPelatihan($item, $personId)
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

        $allScores = [];

        if ($personId !== null) {
            $kodeKaryawan = karyawan::where('id', $personId)->first();

            if ($kodeKaryawan) {
                    $rkmList = RKM::whereYear('tanggal_awal', $tahun)
                        ->where(function ($query) use ($kodeKaryawan) {
                            $query->where('instruktur_key', $kodeKaryawan->kode_karyawan)
                                ->orWhere('instruktur_key2', $kodeKaryawan->kode_karyawan)
                                ->orWhere('asisten_key', $kodeKaryawan->kode_karyawan);
                        })
                        ->get();

                    if ($rkmList->isNotEmpty()) {
                    $rkmIds = $rkmList->pluck('id')->filter()->toArray();

                    $feedbacks = Nilaifeedback::whereBetween('created_at', [$start, $end])
                        ->whereIn('id_rkm', $rkmIds)
                        ->get();

                    foreach ($feedbacks as $fb) {
                        // KOREKSI: Mengubah 'id_rkm' menjadi 'id' sebagai argumen pertama
                        $rkm = $rkmList->firstWhere('id', $fb->id_rkm);

                        if (!$rkm) {
                            continue;
                        }

                        $avg = 0;

                        if ($rkm->instruktur_key == $kodeKaryawan->kode_karyawan) {
                            $scores = [(float) ($fb->I1 ?? 0), (float) ($fb->I2 ?? 0), (float) ($fb->I3 ?? 0), (float) ($fb->I4 ?? 0), (float) ($fb->I5 ?? 0), (float) ($fb->I6 ?? 0), (float) ($fb->I7 ?? 0), (float) ($fb->I8 ?? 0)];
                            $avg = array_sum($scores) / 8;
                        } elseif ($rkm->instruktur_key2 == $kodeKaryawan->kode_karyawan) {
                            $scores = [(float) ($fb->I1b ?? 0), (float) ($fb->I2b ?? 0), (float) ($fb->I3b ?? 0), (float) ($fb->I4b ?? 0), (float) ($fb->I5b ?? 0), (float) ($fb->I6b ?? 0), (float) ($fb->I7b ?? 0), (float) ($fb->I8b ?? 0)];
                            $avg = array_sum($scores) / 8;
                        } elseif ($rkm->asisten_key == $kodeKaryawan->kode_karyawan) {
                            $scores = [(float) ($fb->I1as ?? 0), (float) ($fb->I2as ?? 0), (float) ($fb->I3as ?? 0), (float) ($fb->I4as ?? 0), (float) ($fb->I5as ?? 0), (float) ($fb->I6as ?? 0), (float) ($fb->I7as ?? 0), (float) ($fb->I8as ?? 0)];
                            $avg = array_sum($scores) / 8;
                        }

                        $avg = min(4, max(1, $avg));
                        $allScores[] = $avg;
                    }
                }
            }
        } else {
            $feedbacks = Nilaifeedback::whereBetween('created_at', [$start, $end])->get();

            foreach ($feedbacks as $fb) {
                $i1 = (float) ($fb->I1 ?? 0);
                $i2 = (float) ($fb->I2 ?? 0);
                $i3 = (float) ($fb->I3 ?? 0);
                $i4 = (float) ($fb->I4 ?? 0);
                $i5 = (float) ($fb->I5 ?? 0);
                $i6 = (float) ($fb->I6 ?? 0);
                $i7 = (float) ($fb->I7 ?? 0);
                $i8 = (float) ($fb->I8 ?? 0);
                $sumBase = $i1 + $i2 + $i3 + $i4 + $i5 + $i6 + $i7 + $i8;

                $i1b = (float) ($fb->I1b ?? 0);
                $i2b = (float) ($fb->I2b ?? 0);
                $i3b = (float) ($fb->I3b ?? 0);
                $i4b = (float) ($fb->I4b ?? 0);
                $i5b = (float) ($fb->I5b ?? 0);
                $i6b = (float) ($fb->I6b ?? 0);
                $i7b = (float) ($fb->I7b ?? 0);
                $i8b = (float) ($fb->I8b ?? 0);
                $sumB = $i1b + $i2b + $i3b + $i4b + $i5b + $i6b + $i7b + $i8b;

                if ($sumB > 0) {
                    $totalSum = $sumBase + $sumB;
                    $totalItem = 16;
                } else {
                    $totalSum = $sumBase;
                    $totalItem = 8;
                }

                if ($totalItem > 0) {
                    $avg = $totalSum / $totalItem;
                    $avg = min(4, max(1, $avg));
                    $allScores[] = $avg;
                }
            }
        }

        if (empty($allScores)) {
            return 0;
        }

        $totalResponden = count($allScores);
        $respondenPuas = 0;

        foreach ($allScores as $skor) {
            if ($skor >= 3.5) {
                $respondenPuas++;
            }
        }

        $progress = ($respondenPuas / $totalResponden) * 100;

        return round($progress, 1);
    }

    public function calculateKepuasanPesertaPelatihanDetail($itemDetail, $personId)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
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

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $allScores = [];
        $scoreDatePairs = [];

        if ($personId !== null) {
            $kodeKaryawan = karyawan::where('id', $personId)->first();

            if ($kodeKaryawan) {
                $rkmList = RKM::where('instruktur_key', $kodeKaryawan->kode_karyawan)
                    ->orWhere('instruktur_key2', $kodeKaryawan->kode_karyawan)
                    ->orWhere('asisten_key', $kodeKaryawan->kode_karyawan)
                    ->get();

                if (!$rkmList->isEmpty()) {
                    $rkmIds = $rkmList->pluck('id_rkm')->filter()->toArray();

                    $feedbacks = Nilaifeedback::whereBetween('created_at', [$start, $end])
                        ->whereIn('id_rkm', $rkmIds)
                        ->get();

                    foreach ($feedbacks as $fb) {
                        $rkm = $rkmList->firstWhere('id_rkm', $fb->id_rkm);
                        if (!$rkm) continue;

                        $avg = 0;

                        if ($rkm->instruktur_key == $kodeKaryawan->kode_karyawan) {
                            $scores = [(float)($fb->I1 ?? 0), (float)($fb->I2 ?? 0), (float)($fb->I3 ?? 0), (float)($fb->I4 ?? 0), (float)($fb->I5 ?? 0), (float)($fb->I6 ?? 0), (float)($fb->I7 ?? 0), (float)($fb->I8 ?? 0)];
                            $avg = array_sum($scores) / 8;
                        } elseif ($rkm->instruktur_key2 == $kodeKaryawan->kode_karyawan) {
                            $scores = [(float)($fb->I1b ?? 0), (float)($fb->I2b ?? 0), (float)($fb->I3b ?? 0), (float)($fb->I4b ?? 0), (float)($fb->I5b ?? 0), (float)($fb->I6b ?? 0), (float)($fb->I7b ?? 0), (float)($fb->I8b ?? 0)];
                            $avg = array_sum($scores) / 8;
                        } elseif ($rkm->asisten_key == $kodeKaryawan->kode_karyawan) {
                            $scores = [(float)($fb->I1as ?? 0), (float)($fb->I2as ?? 0), (float)($fb->I3as ?? 0), (float)($fb->I4as ?? 0), (float)($fb->I5as ?? 0), (float)($fb->I6as ?? 0), (float)($fb->I7as ?? 0), (float)($fb->I8as ?? 0)];
                            $avg = array_sum($scores) / 8;
                        }

                        $avg = min(4, max(1, $avg));

                        $allScores[] = $avg;
                        $scoreDatePairs[] = [
                            'score' => $avg,
                            'date' => $fb->created_at->format('Y-m-d'),
                        ];
                    }
                }
            }
        } else {
            $feedbacks = Nilaifeedback::whereBetween('created_at', [$start, $end])->get();

            foreach ($feedbacks as $fb) {
                $i1 = (float)($fb->I1 ?? 0);
                $i2 = (float)($fb->I2 ?? 0);
                $i3 = (float)($fb->I3 ?? 0);
                $i4 = (float)($fb->I4 ?? 0);
                $i5 = (float)($fb->I5 ?? 0);
                $i6 = (float)($fb->I6 ?? 0);
                $i7 = (float)($fb->I7 ?? 0);
                $i8 = (float)($fb->I8 ?? 0);
                $sumBase = $i1 + $i2 + $i3 + $i4 + $i5 + $i6 + $i7 + $i8;

                $i1b = (float)($fb->I1b ?? 0);
                $i2b = (float)($fb->I2b ?? 0);
                $i3b = (float)($fb->I3b ?? 0);
                $i4b = (float)($fb->I4b ?? 0);
                $i5b = (float)($fb->I5b ?? 0);
                $i6b = (float)($fb->I6b ?? 0);
                $i7b = (float)($fb->I7b ?? 0);
                $i8b = (float)($fb->I8b ?? 0);
                $sumB = $i1b + $i2b + $i3b + $i4b + $i5b + $i6b + $i7b + $i8b;

                if ($sumB > 0) {
                    $totalSum = $sumBase + $sumB;
                    $totalItem = 16;
                } else {
                    $totalSum = $sumBase;
                    $totalItem = 8;
                }

                if ($totalItem > 0) {
                    $avg = $totalSum / $totalItem;
                    $avg = min(4, max(1, $avg));

                    $allScores[] = $avg;
                    $scoreDatePairs[] = [
                        'score' => $avg,
                        'date' => $fb->created_at->format('Y-m-d'),
                    ];
                }
            }
        }

        if (empty($allScores)) {
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

        $totalResponden = count($allScores);
        $respondenPuas = 0;

        foreach ($allScores as $skor) {
            if ($skor >= 3.5) {
                $respondenPuas++;
            }
        }

        $progress = ($respondenPuas / $totalResponden) * 100;
        $progress = round($progress, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgressRaw = [];
        $dailyProgressPerMonthRaw = [];

        foreach ($scoreDatePairs as $pair) {
            $date = Carbon::parse($pair['date']);
            $monthKey = $date->format('Y-m');
            $dayKey = $pair['date'];
            $score = $pair['score'];

            $monthlyData[$monthKey][] = $score;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $score;

            $monthlyProgressRaw[$monthKey][] = $score;
            $dailyProgressPerMonthRaw[$monthKey][$dayKey][] = $score;
        }

        $monthlyAverages = [];
        foreach ($monthlyData as $month => $vals) {
            $monthlyAverages[$month] = round(array_sum($vals) / count($vals), 1);
        }

        $monthlyProgress = [];
        foreach ($monthlyProgressRaw as $month => $vals) {
            $total = count($vals);
            $puas = collect($vals)->filter(fn($v) => $v >= 3.5)->count();
            $monthlyProgress[$month] = $total > 0 ? round(($puas / $total) * 100, 1) : 0;
        }

        $dailyProgressPerMonth = [];
        foreach ($dailyProgressPerMonthRaw as $month => $days) {
            foreach ($days as $day => $vals) {
                $total = count($vals);
                $puas = collect($vals)->filter(fn($v) => $v >= 3.5)->count();
                $dailyProgressPerMonth[$month][$day] = $total > 0 ? round(($puas / $total) * 100, 1) : 0;
            }
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgress);
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
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    public function calculateUpselingLanjutanMateri($item, $personId): float
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
        if ($personId !== null) {
            $kodeKaryawan = karyawan::where('id', $personId)->first();

            $rkmQuery = RKM::with(['materi', 'peluang', 'rekomendasilanjutan'])
                        ->whereBetween('tanggal_awal', [$start, $end])->where('tanggal_akhir', '<', now())
                        ->where('status', '0')
                        ->whereNull('r_k_m_s.deleted_at')
                        ->whereHas('peluang', function ($query) {
                            $query->where('tentatif', 0);
                        })
                        ->orderBy('status', 'asc')
                        ->orderBy('tanggal_awal', 'asc')
                        ->get();
        } else {
            $rkmQuery = RKM::with(['materi', 'peluang', 'rekomendasilanjutan'])
                        ->whereBetween('tanggal_awal', [$start, $end])->where('tanggal_akhir', '<', now())
                        ->where('status', '0')
                        ->whereNull('r_k_m_s.deleted_at')
                        ->whereHas('peluang', function ($query) {
                            $query->where('tentatif', 0);
                        })
                        ->orderBy('status', 'asc')
                        ->orderBy('tanggal_awal', 'asc')
                        ->get();
        }

        $totalData = $rkmQuery->count();

        if ($totalData === 0) {
            return 0.0;
        }

        $rkmIds = $rkmQuery->pluck('id');

        $totalRekomendasi = RekomendasiLanjutan::whereIn('id_rkm', $rkmIds)->count();

        $presentase = ($totalRekomendasi / $totalData) * 100;

        return round($presentase, 1);
    }

    public function calculateUpselingLanjutanMateriDetail($itemDetail, $personId): array
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail) {
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

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        if ($personId !== null) {
            $kodeKaryawan = karyawan::where('id', $personId)->first();

            if (!$kodeKaryawan) {
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

            $rkmQuery = RKM::whereBetween('created_at', [$start, $end])
                ->where('instruktur_key', $kodeKaryawan->kode_karyawan)
                ->where('tanggal_akhir', '<', now());
        } else {
            $rkmQuery = RKM::whereBetween('created_at', [$start, $end])
                ->where('tanggal_akhir', '<', now());
        }

        $rkms = $rkmQuery->get(['id', 'created_at']);

        if ($rkms->isEmpty()) {
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

        $rkmIds = $rkms->pluck('id');
        $rekomendasiRkmIds = RekomendasiLanjutan::whereIn('id_rkm', $rkmIds)->pluck('id_rkm');
        $hasRekomendasiMap = $rekomendasiRkmIds->flip();

        $totalData = $rkms->count();
        $totalRekomendasi = 0;

        $dailyData = [];
        $monthlyDataRaw = [];

        foreach ($rkms as $rkm) {
            $hasRekom = $hasRekomendasiMap->has($rkm->id);
            if ($hasRekom) {
                $totalRekomendasi++;
            }

            $dateObj = Carbon::parse($rkm->created_at);
            $dayKey = $dateObj->format('Y-m-d');
            $monthKey = $dateObj->format('Y-m');

            $dailyData[$dayKey]['total'] = ($dailyData[$dayKey]['total'] ?? 0) + 1;
            if ($hasRekom) {
                $dailyData[$dayKey]['rekom'] = ($dailyData[$dayKey]['rekom'] ?? 0) + 1;
            }

            $monthlyDataRaw[$monthKey]['total'] = ($monthlyDataRaw[$monthKey]['total'] ?? 0) + 1;
            if ($hasRekom) {
                $monthlyDataRaw[$monthKey]['rekom'] = ($monthlyDataRaw[$monthKey]['rekom'] ?? 0) + 1;
            }
        }

        $progress = $totalData > 0 ? round(($totalRekomendasi / $totalData) * 100, 1) : 0;

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $totalRekomendasi;
        $below = $totalData - $totalRekomendasi;

        $monthlyAverages = [];
        $monthlyProgress = [];
        foreach ($monthlyDataRaw as $month => $data) {
            $rekom = $data['rekom'] ?? 0;

            $rate = $data['total'] > 0
                ? ($rekom / $data['total']) * 100
                : 0;

            $monthlyAverages[$month] = round($rate, 1);
            $monthlyProgress[$month] = round($rate, 1);
        }
        ksort($monthlyAverages);
        ksort($monthlyProgress);

        $dailyBreakdownPerMonth = [];
        $dailyProgressPerMonth = [];

        foreach ($dailyData as $dayKey => $data) {
            $dateObj = Carbon::parse($dayKey);
            $monthKey = $dateObj->format('Y-m');

            $rekom = $data['rekom'] ?? 0;

            $rate = $data['total'] > 0
                ? ($rekom / $data['total']) * 100
                : 0;

            $roundedRate = round($rate, 1);

            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $roundedRate;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $roundedRate;
        }

        ksort($dailyBreakdownPerMonth);
        ksort($dailyProgressPerMonth);

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            ksort($dailyBreakdownPerMonth[$month]);
            ksort($dailyProgressPerMonth[$month]);
        }

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

    public function calculateSertifikasiKompetensiInternal($item, $personId)
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
        $startYear = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $endYear = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $query = detailPersonKPI::where('detailTargetKey', $detail->id);

        if ($personId !== null) {
            $query->where('id_karyawan', $personId);
        }

        $detailPersons = $query->get();
        $totalData = $detailPersons->count();

        if ($totalData === 0) {
            return 0.0;
        }

        $countAchieved = 0;

        foreach ($detailPersons as $personItem) {
            $validSertifikasi = Sertifikasi::where('user_id', $personItem->id_karyawan)
                ->where('tanggal_berlaku_dari', '<=', $endYear)
                ->where(function ($q) use ($startYear) {
                    $q->where('tanggal_berlaku_sampai', '>=', $startYear)->orWhereNull('tanggal_berlaku_sampai');
                })
                ->count();

            if ($personId !== null) {
                $countAchieved += $validSertifikasi;
            } else {
                if ($validSertifikasi > 0) {
                    $countAchieved += 1;
                }
            }
        }

        if ($personId !== null) {
            $progress = max(100, $countAchieved);
        } else {
            $progress = $countAchieved;
        }

        return round($progress);
    }

    public function calculateSertifikasiKompetensiInternalDetail($itemDetail, $personId)
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

        if (is_null($detail) || is_null($detail->nilai_target) || is_null($detail->detail_jangka)) {
            return $emptyResponse;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $emptyResponse;
        }

        $startYear = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $endYear = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $query = detailPersonKPI::where('detailTargetKey', $detail->id);

        if ($personId !== null) {
            $query->where('id_karyawan', $personId);
        }

        $detailPersons = $query->get();
        $totalData = $detailPersons->count();

        if ($totalData === 0) {
            return $emptyResponse;
        }

        $countAchieved = 0;
        $dailyValues = [];

        foreach ($detailPersons as $personItem) {
            $validSertifikasis = Sertifikasi::where('user_id', $personItem->id_karyawan)
                ->where('tanggal_berlaku_dari', '<=', $endYear)
                ->where(function ($q) use ($startYear) {
                    $q->where('tanggal_berlaku_sampai', '>=', $startYear)
                        ->orWhereNull('tanggal_berlaku_sampai');
                })
                ->get();

            $validSertifikasi = $validSertifikasis->count();

            if ($personId !== null) {
                $countAchieved += $validSertifikasi;

                foreach ($validSertifikasis as $cert) {
                    $tanggal = Carbon::parse($cert->tanggal_berlaku_dari);
                    if ($tanggal < $startYear) {
                        $tanggal = $startYear;
                    }

                    if ($tanggal >= $startYear && $tanggal <= $endYear) {
                        $dateKey = $tanggal->format('Y-m-d');
                        $dailyValues[$dateKey][] = 1;
                    }
                }
            } else {
                if ($validSertifikasi > 0) {
                    $countAchieved += 1;

                    if ($validSertifikasis->isNotEmpty()) {
                        $firstCert = $validSertifikasis->sortBy('tanggal_berlaku_dari')->first();
                        $tanggal = Carbon::parse($firstCert->tanggal_berlaku_dari);

                        if ($tanggal < $startYear) {
                            $tanggal = $startYear;
                        }

                        if ($tanggal >= $startYear && $tanggal <= $endYear) {
                            $dateKey = $tanggal->format('Y-m-d');
                            $dailyValues[$dateKey][] = 1;
                        }
                    }
                }
            }
        }

        if ($personId !== null) {
            $progress = max(100, $countAchieved);
        } else {
            $progress = $countAchieved;
        }
        $progress = round($progress);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        if ($personId !== null) {
            $above = $countAchieved;
            $below = 0;
        } else {
            $above = $countAchieved;
            $below = $totalData - $countAchieved;
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

    public function calculatePelatihanKompetensiEksternal($item, $personId)
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
        $startYear = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $endYear = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $query = detailPersonKPI::where('detailTargetKey', $detail->id);

        if ($personId !== null) {
            $query->where('id_karyawan', $personId);
        }

        $detailPersons = $query->get();
        $totalData = $detailPersons->count();

        if ($totalData === 0) {
            return 0.0;
        }

        $countAchieved = 0;

        foreach ($detailPersons as $personItem) {
            $validSertifikasi = Pelatihan::where('user_id', $personItem->id_karyawan)
                ->whereBetween('tanggal_selesai', [$startYear, $endYear])
                ->count();

            if ($personId !== null) {
                $countAchieved += $validSertifikasi;
            } else {
                if ($validSertifikasi > 0) {
                    $countAchieved += 1;
                }
            }
        }

        if ($personId !== null) {
            $progress = max(100, $countAchieved);
        } else {
            $progress = $countAchieved;
        }

        return round($progress);
    }

    public function calculatePelatihanKompetensiEksternalDetail($itemDetail, $personId)
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

        if (is_null($detail) || is_null($detail->nilai_target) || is_null($detail->detail_jangka)) {
            return $emptyResponse;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $emptyResponse;
        }

        $startYear = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $endYear = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $query = detailPersonKPI::where('detailTargetKey', $detail->id);

        if ($personId !== null) {
            $query->where('id_karyawan', $personId);
        }

        $detailPersons = $query->get();
        $totalData = $detailPersons->count();

        if ($totalData === 0) {
            return $emptyResponse;
        }

        $countAchieved = 0;
        $dailyValues = [];

        foreach ($detailPersons as $personItem) {
            $validSertifikasis = Pelatihan::where('user_id', $personItem->id_karyawan)
                ->whereBetween('tanggal_selesai', [$startYear, $endYear])
                ->get();

            $validSertifikasi = $validSertifikasis->count();

            if ($personId !== null) {
                $countAchieved += $validSertifikasi;

                foreach ($validSertifikasis as $cert) {
                    $tanggal = Carbon::parse($cert->tanggal_selesai);
                    if ($tanggal < $startYear) {
                        $tanggal = $startYear;
                    }

                    if ($tanggal >= $startYear && $tanggal <= $endYear) {
                        $dateKey = $tanggal->format('Y-m-d');
                        $dailyValues[$dateKey][] = 1;
                    }
                }
            } else {
                if ($validSertifikasi > 0) {
                    $countAchieved += 1;

                    if ($validSertifikasis->isNotEmpty()) {
                        $firstCert = $validSertifikasis->sortBy('tanggal_selesai')->first();
                        $tanggal = Carbon::parse($firstCert->tanggal_selesai);

                        if ($tanggal < $startYear) {
                            $tanggal = $startYear;
                        }

                        if ($tanggal >= $startYear && $tanggal <= $endYear) {
                            $dateKey = $tanggal->format('Y-m-d');
                            $dailyValues[$dateKey][] = 1;
                        }
                    }
                }
            }
        }

        if ($personId !== null) {
            $progress = max(100, $countAchieved);
        } else {
            $progress = $countAchieved;
        }
        $progress = round($progress);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        if ($personId !== null) {
            $above = $countAchieved;
            $below = 0;
        } else {
            $above = $countAchieved;
            $below = $totalData - $countAchieved;
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
}