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

    private function calculateAndFormatGap(float $progress, float $target): string
    {
        $gapRaw = $progress - $target;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');
        return $gap === '' ? '0' : $gap;
    }

    private function getDefaultDetailResponse(array $extra = []): array
    {
        return array_merge([
            'progress' => 0.0,
            'gap' => '0',
            'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
        ], $extra);
    }

    public function calculatePresentaseKinerjaInstruktur($item, $personId)
    {
        $detailResult = $this->calculatePresentaseKinerjaInstrukturDetail($item, $personId);
        return $detailResult['progress'];
    }

    public function calculatePresentaseKinerjaInstrukturDetail($itemDetail, $personId)
    {
        $detail = $itemDetail->detailTargetKPI->first();
        $emptyResponse = $this->getDefaultDetailResponse([
            'instruktur_details' => [],
            'hari_libur_nasional' => ['jumlah' => 0, 'daftar' => []],
        ]);

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
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
        $endDate = ($tahun == $today->year) ? $today : Carbon::create($tahun, 12, 31)->endOfYear();

        $liburNasional = HariLibur::whereBetween('tanggal', [$startDate, $endDate])
            ->pluck('nama', 'tanggal')
            ->mapWithKeys(fn($nama, $tanggal) => [
                Carbon::parse($tanggal)->toDateString() => $nama ?? 'Hari Libur Nasional'
            ])
            ->toArray();

        $hariKerjaPeriode = 0;
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            if (!$date->isWeekend() && !array_key_exists($date->toDateString(), $liburNasional)) {
                $hariKerjaPeriode++;
            }
        }

        $targetJamPerOrang = $hariKerjaPeriode * $jamKerjaPerHari;

        $instrukturQuery = karyawan::select('id', 'kode_karyawan', 'nama_lengkap', 'jabatan')
            ->where('status_aktif', '1')
            ->whereNot('jabatan', 'Outsource')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->whereNotNull('nip')
            ->whereNot('divisi', 'Direksi')
            ->where('jabatan', 'instruktur');

        if ($personId !== null) {
            $instrukturQuery->where('id', $personId);
        }

        $instrukturList = $instrukturQuery->get();
        $instrukturIds = $instrukturList->pluck('id')->toArray();
        $kodeKaryawans = $instrukturList->pluck('kode_karyawan')->toArray();

        if (empty($instrukturIds)) {
            return $emptyResponse;
        }

        $activityDatesByUser = ActivityInstruktur::select('user_id', 'activity_date')
            ->whereNull('id_rkm')
            ->whereBetween('activity_date', [$startDate, $endDate])
            ->whereIn('user_id', $instrukturIds)
            ->get()
            ->groupBy('user_id')
            ->map(fn($items) => $items->pluck('activity_date')->map(fn($d) => Carbon::parse($d)->toDateString())->unique()->toArray());

        $cutiDatesByUser = [];
        $cutiDetailsByUser = [];
        pengajuancuti::select('id_karyawan', 'tanggal_awal', 'tanggal_akhir', 'alasan', 'tipe')
            ->whereIn('id_karyawan', $instrukturIds)
            ->where('tanggal_awal', '<=', $endDate)
            ->where('tanggal_akhir', '>=', $startDate)
            ->cursor()
            ->each(function ($cuti) use (&$cutiDatesByUser, &$cutiDetailsByUser, $startDate, $endDate) {
                $effectiveStart = $cuti->tanggal_awal > $startDate->toDateString() ? Carbon::parse($cuti->tanggal_awal) : $startDate->copy();
                $effectiveEnd = $cuti->tanggal_akhir < $endDate->toDateString() ? Carbon::parse($cuti->tanggal_akhir) : $endDate->copy();
                
                $currentDate = $effectiveStart->copy();
                while ($currentDate->lte($effectiveEnd)) {
                    $dateStr = $currentDate->toDateString();
                    $cutiDatesByUser[$cuti->id_karyawan][] = $dateStr;
                    $cutiDetailsByUser[$cuti->id_karyawan][$dateStr] = [
                        'alasan' => $cuti->alasan ?? 'Cuti',
                        'tipe' => $cuti->tipe ?? 'Cuti',
                        'tanggal_awal' => $cuti->tanggal_awal,
                        'tanggal_akhir' => $cuti->tanggal_akhir,
                    ];
                    $currentDate->addDay();
                }
            });

        $rkmDatesByKode = [];
        RKM::select('id', 'tanggal_awal', 'tanggal_akhir', 'instruktur_key', 'instruktur_key2', 'asisten_key')
            ->where('tanggal_awal', '<=', $endDate)
            ->where('tanggal_akhir', '>=', $startDate)
            ->where(function ($q) use ($kodeKaryawans) {
                $q->whereIn('instruktur_key', $kodeKaryawans)
                  ->orWhereIn('instruktur_key2', $kodeKaryawans)
                  ->orWhereIn('asisten_key', $kodeKaryawans);
            })
            ->cursor()
            ->each(function ($rkm) use (&$rkmDatesByKode, $startDate, $endDate) {
                $effectiveStart = $rkm->tanggal_awal > $startDate->toDateString() ? Carbon::parse($rkm->tanggal_awal) : $startDate->copy();
                $effectiveEnd = $rkm->tanggal_akhir < $endDate->toDateString() ? Carbon::parse($rkm->tanggal_akhir) : $endDate->copy();
                
                $kodes = array_filter([$rkm->instruktur_key, $rkm->instruktur_key2, $rkm->asisten_key]);
                $currentDate = $effectiveStart->copy();
                while ($currentDate->lte($effectiveEnd)) {
                    $dateStr = $currentDate->toDateString();
                    foreach ($kodes as $kode) {
                        if ($kode) {
                            $rkmDatesByKode[$kode][] = $dateStr;
                        }
                    }
                    $currentDate->addDay();
                }
            });

        $totalJamMengajar = 0;
        $dailyValues = [];
        $instrukturDetails = [];

        foreach ($instrukturList as $instruktur) {
            $kode = $instruktur->kode_karyawan;
            $idInstruktur = $instruktur->id;

            $activityDates = $activityDatesByUser[$idInstruktur] ?? [];
            $rkmDates = $rkmDatesByKode[$kode] ?? [];
            $cutiDates = $cutiDatesByUser[$idInstruktur] ?? [];
            $cutiDetailList = $cutiDetailsByUser[$idInstruktur] ?? [];

            $allWorkingDays = array_unique(array_merge($activityDates, $rkmDates));
            $allWorkingDays = array_values(array_diff($allWorkingDays, array_unique($cutiDates)));

            $jamAktifInstruktur = count($allWorkingDays) * $jamKerjaPerHari;
            $totalJamMengajar += $jamAktifInstruktur;

            foreach ($allWorkingDays as $dateStr) {
                $dailyValues[$dateStr] = ($dailyValues[$dateStr] ?? 0) + $jamKerjaPerHari;
            }

            $persentaseInstruktur = $targetJamPerOrang > 0 ? round(($jamAktifInstruktur / $targetJamPerOrang) * 100, 1) : 0;

            $daftarLiburPerInstruktur = [];
            foreach ($liburNasional as $tgl => $ket) {
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
                } elseif (isset($liburNasional[$dateStr])) {
                    $status = 'libur';
                    $keterangan = $liburNasional[$dateStr];
                } elseif (in_array($dateStr, array_unique($cutiDates))) {
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
                'total_hari_cuti' => count(array_unique($cutiDates)),
                'daftar_libur' => $daftarLiburPerInstruktur,
                'daftar_cuti' => $cutiDetailList,
                'kalender' => $kalenderData,
            ];
        }

        $jumlahInstruktur = $instrukturList->count();
        $avgFactor = ($personId !== null || $jumlahInstruktur == 0) ? 1 : $jumlahInstruktur;
        $totalJamMengajarRataRata = $totalJamMengajar / $avgFactor;
        $targetJam = $targetJamPerOrang;

        if ($targetJam <= 0) {
            return $emptyResponse;
        }

        $progress = round(min(100, ($totalJamMengajarRataRata / $targetJam) * 100), 1);
        $gap = $this->calculateAndFormatGap($progress, 100.0);

        $above = $totalJamMengajarRataRata;
        $below = $personId ? 0 : max(0, $targetJam - $totalJamMengajarRataRata);

        $monthly = [];
        $dailyPerMonth = [];
        $monthlyProgress = [];
        $dailyProgress = [];

        foreach ($dailyValues as $dateStr => $jam) {
            $date = Carbon::parse($dateStr);
            $m = $date->format('Y-m');
            $jamRataRata = $jam / $avgFactor;

            $monthly[$m] = ($monthly[$m] ?? 0) + $jamRataRata;
            $dailyPerMonth[$m][$dateStr] = $jamRataRata;
        }

        foreach ($monthly as $month => $totalJam) {
            $monthlyProgress[$month] = $targetJam > 0 ? round(($totalJam / $targetJam) * 100, 1) : 0;
        }

        foreach ($dailyPerMonth as $month => $days) {
            foreach ($days as $d => $val) {
                $dailyProgress[$month][$d] = $targetJam > 0 ? round(($val / $targetJam) * 100, 1) : 0;
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
                    'total_hari_libur' => count($liburNasional),
                    'total_hari_cuti' => round($avgHariCuti, 1),
                    'daftar_libur' => $liburNasional,
                    'daftar_cuti' => [],
                    'kalender' => [],
                ]
            ];
        }

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => round($above, 1), 'below' => round($below, 1)],
            'monthly_data' => $monthly,
            'daily_breakdown_per_month' => $dailyPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgress,
            'instruktur_details' => $instrukturDetails,
            'hari_libur_nasional' => [
                'jumlah' => count($liburNasional),
                'daftar' => $liburNasional,
            ],
        ];
    }

    public function calculateKepuasanPesertaPelatihan($item, $personId)
    {
        $detailResult = $this->calculateKepuasanPesertaPelatihanDetail($item, $personId);
        return $detailResult['progress'];
    }

    public function calculateKepuasanPesertaPelatihanDetail($itemDetail, $personId)
    {
        $detail = $itemDetail->detailTargetKPI->first();
        $emptyResponse = $this->getDefaultDetailResponse();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $emptyResponse;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $emptyResponse;
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $allScores = [];
        $scoreDatePairs = [];
        $kodeKaryawan = null;

        $query = Nilaifeedback::select('id_rkm', 'created_at', 'I1', 'I2', 'I3', 'I4', 'I5', 'I6', 'I7', 'I8', 'I1b', 'I2b', 'I3b', 'I4b', 'I5b', 'I6b', 'I7b', 'I8b', 'I1as', 'I2as', 'I3as', 'I4as', 'I5as', 'I6as', 'I7as', 'I8as')
            ->whereBetween('created_at', [$start, $end]);

        if ($personId !== null) {
            $kodeKaryawan = karyawan::where('id', $personId)->value('kode_karyawan');
            if (!$kodeKaryawan) {
                return $emptyResponse;
            }
            $rkmIds = RKM::whereYear('tanggal_awal', $tahun)
                ->where(function ($q) use ($kodeKaryawan) {
                    $q->where('instruktur_key', $kodeKaryawan)
                      ->orWhere('instruktur_key2', $kodeKaryawan)
                      ->orWhere('asisten_key', $kodeKaryawan);
                })->pluck('id')->toArray();
            
            if (!empty($rkmIds)) {
                $query->whereIn('id_rkm', $rkmIds);
            } else {
                return $emptyResponse;
            }
        }

        $rkmMap = collect();
        if ($personId !== null && !empty($rkmIds)) {
            $rkmMap = RKM::select('id', 'instruktur_key', 'instruktur_key2', 'asisten_key')
                ->whereIn('id', $rkmIds)
                ->get()
                ->keyBy('id');
        }

        $query->cursor()->each(function ($fb) use (&$allScores, &$scoreDatePairs, $personId, $kodeKaryawan, $rkmMap) {
            if ($personId !== null && $kodeKaryawan) {
                $rkm = $rkmMap->get($fb->id_rkm);
                if (!$rkm) return;

                $avg = 0;
                if ($rkm->instruktur_key == $kodeKaryawan) {
                    $scores = [(float)($fb->I1 ?? 0), (float)($fb->I2 ?? 0), (float)($fb->I3 ?? 0), (float)($fb->I4 ?? 0), (float)($fb->I5 ?? 0), (float)($fb->I6 ?? 0), (float)($fb->I7 ?? 0), (float)($fb->I8 ?? 0)];
                    $avg = array_sum($scores) / 8;
                } elseif ($rkm->instruktur_key2 == $kodeKaryawan) {
                    $scores = [(float)($fb->I1b ?? 0), (float)($fb->I2b ?? 0), (float)($fb->I3b ?? 0), (float)($fb->I4b ?? 0), (float)($fb->I5b ?? 0), (float)($fb->I6b ?? 0), (float)($fb->I7b ?? 0), (float)($fb->I8b ?? 0)];
                    $avg = array_sum($scores) / 8;
                } elseif ($rkm->asisten_key == $kodeKaryawan) {
                    $scores = [(float)($fb->I1as ?? 0), (float)($fb->I2as ?? 0), (float)($fb->I3as ?? 0), (float)($fb->I4as ?? 0), (float)($fb->I5as ?? 0), (float)($fb->I6as ?? 0), (float)($fb->I7as ?? 0), (float)($fb->I8as ?? 0)];
                    $avg = array_sum($scores) / 8;
                }
            } else {
                $sumBase = (float)($fb->I1 ?? 0) + (float)($fb->I2 ?? 0) + (float)($fb->I3 ?? 0) + (float)($fb->I4 ?? 0) + (float)($fb->I5 ?? 0) + (float)($fb->I6 ?? 0) + (float)($fb->I7 ?? 0) + (float)($fb->I8 ?? 0);
                $sumB = (float)($fb->I1b ?? 0) + (float)($fb->I2b ?? 0) + (float)($fb->I3b ?? 0) + (float)($fb->I4b ?? 0) + (float)($fb->I5b ?? 0) + (float)($fb->I6b ?? 0) + (float)($fb->I7b ?? 0) + (float)($fb->I8b ?? 0);

                $avg = $sumB > 0 ? ($sumBase + $sumB) / 16 : $sumBase / 8;
            }

            $avg = min(4, max(1, $avg));
            $allScores[] = $avg;
            $scoreDatePairs[] = [
                'score' => $avg,
                'date' => Carbon::parse($fb->created_at)->format('Y-m-d'),
            ];
        });

        if (empty($allScores)) {
            return $emptyResponse;
        }

        $totalResponden = count($allScores);
        $respondenPuas = count(array_filter($allScores, fn($skor) => $skor >= 3.5));
        $progress = round(($respondenPuas / $totalResponden) * 100, 1);
        $gap = $this->calculateAndFormatGap($progress, $nilaiTarget);

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
            $puas = count(array_filter($vals, fn($v) => $v >= 3.5));
            $monthlyProgress[$month] = $total > 0 ? round(($puas / $total) * 100, 1) : 0;
        }

        $dailyProgressPerMonth = [];
        foreach ($dailyProgressPerMonthRaw as $month => $days) {
            foreach ($days as $day => $vals) {
                $total = count($vals);
                $puas = count(array_filter($vals, fn($v) => $v >= 3.5));
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
            'pie_chart' => ['above' => $respondenPuas, 'below' => max(0, $totalResponden - $respondenPuas)],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    public function calculateUpselingLanjutanMateri($item, $personId): float
    {
        $detailResult = $this->calculateUpselingLanjutanMateriDetail($item, $personId);
        return $detailResult['progress'];
    }

    public function calculateUpselingLanjutanMateriDetail($itemDetail, $personId): array
    {
        $detail = $itemDetail->detailTargetKPI->first();
        $emptyResponse = $this->getDefaultDetailResponse();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $emptyResponse;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $emptyResponse;
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $rkmQuery = RKM::select('id', 'tanggal_awal')
            ->whereBetween('tanggal_awal', [$start, $end])
            ->where('tanggal_akhir', '<', now())
            ->where('status', '0')
            ->whereNull('r_k_m_s.deleted_at')
            ->whereHas('peluang', fn($q) => $q->where('tentatif', 0));

        if ($personId !== null) {
            $kodeKaryawan = karyawan::find($personId);
            if (!$kodeKaryawan) return $emptyResponse;
            $rkmQuery->where('instruktur_key', $kodeKaryawan->kode_karyawan);
        }

        $rkms = $rkmQuery->cursor();
        
        $totalData = 0;
        $totalRekomendasi = 0;
        $dailyData = [];
        $monthlyDataRaw = [];
        $rkmIds = [];
        $rkmDates = [];
        
        foreach ($rkms as $rkm) {
            $rkmIds[] = $rkm->id;
            $rkmDates[$rkm->id] = $rkm->tanggal_awal;
            $totalData++;
        }

        if ($totalData === 0) return $emptyResponse;

        $rekomendasiRkmIds = RekomendasiLanjutan::whereIn('id_rkm', $rkmIds)->pluck('id_rkm')->toArray();
        $hasRekomendasiMap = array_flip($rekomendasiRkmIds);

        foreach ($rkmIds as $id) {
            $hasRekom = isset($hasRekomendasiMap[$id]);
            if ($hasRekom) $totalRekomendasi++;

            $dateObj = Carbon::parse($rkmDates[$id]);
            $dayKey = $dateObj->format('Y-m-d');
            $monthKey = $dateObj->format('Y-m');

            $dailyData[$dayKey]['total'] = ($dailyData[$dayKey]['total'] ?? 0) + 1;
            $monthlyDataRaw[$monthKey]['total'] = ($monthlyDataRaw[$monthKey]['total'] ?? 0) + 1;
            
            if ($hasRekom) {
                $dailyData[$dayKey]['rekom'] = ($dailyData[$dayKey]['rekom'] ?? 0) + 1;
                $monthlyDataRaw[$monthKey]['rekom'] = ($monthlyDataRaw[$monthKey]['rekom'] ?? 0) + 1;
            }
        }

        $progress = $totalData > 0 ? round(($totalRekomendasi / $totalData) * 100, 1) : 0;
        $gap = $this->calculateAndFormatGap($progress, $nilaiTarget);

        $monthlyAverages = [];
        foreach ($monthlyDataRaw as $month => $data) {
            $rate = $data['total'] > 0 ? (($data['rekom'] ?? 0) / $data['total']) * 100 : 0;
            $monthlyAverages[$month] = round($rate, 1);
        }
        ksort($monthlyAverages);

        $dailyBreakdownPerMonth = [];
        foreach ($dailyData as $dayKey => $data) {
            $monthKey = Carbon::parse($dayKey)->format('Y-m');
            $rate = $data['total'] > 0 ? (($data['rekom'] ?? 0) / $data['total']) * 100 : 0;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = round($rate, 1);
        }
        
        foreach ($dailyBreakdownPerMonth as $month => $days) {
            ksort($dailyBreakdownPerMonth[$month]);
        }
        ksort($dailyBreakdownPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $totalRekomendasi, 'below' => max(0, $totalData - $totalRekomendasi)],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyAverages,
            'daily_progress_per_month' => $dailyBreakdownPerMonth,
        ];
    }

    public function calculateSertifikasiKompetensiInternal($item, $personId)
    {
        $detailResult = $this->calculateSertifikasiKompetensiInternalDetail($item, $personId);
        return $detailResult['progress'];
    }

    public function calculateSertifikasiKompetensiInternalDetail($itemDetail, $personId)
    {
        $detail = $itemDetail->detailTargetKPI->first();
        $emptyResponse = $this->getDefaultDetailResponse();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $emptyResponse;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $emptyResponse;
        }

        $startYear = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $endYear = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $query = detailPersonKPI::select('id_karyawan')->where('detailTargetKey', $detail->id);
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
            $validSertifikasis = Sertifikasi::select('tanggal_berlaku_dari')
                ->where('user_id', $personItem->id_karyawan)
                ->where('tanggal_berlaku_dari', '<=', $endYear)
                ->where(function ($q) use ($startYear) {
                    $q->where('tanggal_berlaku_sampai', '>=', $startYear)->orWhereNull('tanggal_berlaku_sampai');
                })
                ->cursor();

            $validCount = 0;
            $firstCertDate = null;

            foreach ($validSertifikasis as $cert) {
                $validCount++;
                $tanggal = Carbon::parse($cert->tanggal_berlaku_dari);
                
                if ($firstCertDate === null || $tanggal->lt($firstCertDate)) {
                    $firstCertDate = $tanggal;
                }

                if ($personId !== null) {
                    if ($tanggal < $startYear) $tanggal = $startYear;
                    if ($tanggal >= $startYear && $tanggal <= $endYear) {
                        $dailyValues[$tanggal->format('Y-m-d')][] = 1;
                    }
                }
            }

            if ($personId !== null) {
                $countAchieved += $validCount;
            } else {
                if ($validCount > 0) {
                    $countAchieved += 1;
                    
                    if ($firstCertDate) {
                        $tanggal = $firstCertDate;
                        if ($tanggal < $startYear) $tanggal = $startYear;
                        if ($tanggal >= $startYear && $tanggal <= $endYear) {
                            $dailyValues[$tanggal->format('Y-m-d')][] = 1;
                        }
                    }
                }
            }
        }

        $progress = $countAchieved;
        $gap = $this->calculateAndFormatGap($progress, $nilaiTarget);

        $above = $countAchieved;
        $below = $personId !== null ? 0 : max(0, $totalData - $countAchieved);

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
        $detailResult = $this->calculatePelatihanKompetensiEksternalDetail($item, $personId);
        return $detailResult['progress'];
    }

    public function calculatePelatihanKompetensiEksternalDetail($itemDetail, $personId)
    {
        $detail = $itemDetail->detailTargetKPI->first();
        $emptyResponse = $this->getDefaultDetailResponse();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $emptyResponse;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $emptyResponse;
        }

        $startYear = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $endYear = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $query = detailPersonKPI::select('id_karyawan')->where('detailTargetKey', $detail->id);
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
            $validSertifikasis = Pelatihan::select('tanggal_selesai')
                ->where('user_id', $personItem->id_karyawan)
                ->whereBetween('tanggal_selesai', [$startYear, $endYear])
                ->cursor();

            $validCount = 0;
            $firstCertDate = null;

            foreach ($validSertifikasis as $cert) {
                $validCount++;
                $tanggal = Carbon::parse($cert->tanggal_selesai);
                
                if ($firstCertDate === null || $tanggal->lt($firstCertDate)) {
                    $firstCertDate = $tanggal;
                }

                if ($personId !== null) {
                    if ($tanggal < $startYear) $tanggal = $startYear;
                    if ($tanggal >= $startYear && $tanggal <= $endYear) {
                        $dailyValues[$tanggal->format('Y-m-d')][] = 1;
                    }
                }
            }

            if ($personId !== null) {
                $countAchieved += $validCount;
            } else {
                if ($validCount > 0) {
                    $countAchieved += 1;
                    
                    if ($firstCertDate) {
                        $tanggal = $firstCertDate;
                        if ($tanggal < $startYear) $tanggal = $startYear;
                        if ($tanggal >= $startYear && $tanggal <= $endYear) {
                            $dailyValues[$tanggal->format('Y-m-d')][] = 1;
                        }
                    }
                }
            }
        }

        $progress = $nilaiTarget > 0 ? round(min(100, ($countAchieved / $nilaiTarget) * 100), 1) : 0;
        $gap = $this->calculateAndFormatGap($progress, $nilaiTarget);

        $above = $countAchieved;
        $below = $personId !== null ? 0 : max(0, $totalData - $countAchieved);

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