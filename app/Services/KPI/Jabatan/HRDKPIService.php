<?php

namespace App\Services\KPI\Jabatan;

use App\Models\AbsensiKaryawan;
use App\Models\AdministrasiKaryawan;
use App\Models\JenisTunjangan;
use App\Models\Kegiatan;
use App\Models\TunjanganKaryawan;
use App\Traits\KPIDefaultResponseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class HRDKPIService
{
    use KPIDefaultResponseTrait;

    public function calculatePelaksanaanKegiatanKaryawan($item, $personId = null)
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

        $kegiatans = Kegiatan::whereYear('created_at', $tahun)->get();

        $totalKegiatan = $kegiatans->count();
        if ($totalKegiatan == 0) {
            return 0;
        }
        $totalKehadiranValid = 0;

        foreach ($kegiatans as $kegiatan) {
            $pesertaIds = is_array($kegiatan->id_peserta) ? $kegiatan->id_peserta : json_decode($kegiatan->id_peserta, true);

            if (empty($pesertaIds)) {
                continue;
            }

            $jumlahPeserta = count($pesertaIds);

            $tanggalKegiatan = Carbon::parse($kegiatan->waktu_kegiatan);
            $startOfDay = $tanggalKegiatan->copy()->startOfDay();
            $endOfDay = $tanggalKegiatan->copy()->endOfDay();

            $jumlahHadir = AbsensiKaryawan::whereIn('id_karyawan', $pesertaIds)
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->where('keterangan', 'Masuk')
                ->count();

            $persentase = ($jumlahHadir / $jumlahPeserta) * 100;

            if ($persentase >= 80) {
                $totalKehadiranValid++;
            }
        }

        $progress = ($totalKehadiranValid / $totalKegiatan) * 100;

        return round($progress, 1);
    }

    public function calculatePelaksanaanKegiatanKaryawanDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (is_null($detail) || is_null($detail->manual_value)) {
            return $this->getDefaultDetailResponse();
        }

        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return $this->getDefaultDetailResponse();
        }

        $startOfYear = Carbon::create($tahun, 1, 1)->startOfDay();
        $endOfYear = Carbon::create($tahun, 12, 31)->endOfDay();

        $kegiatans = Kegiatan::whereBetween('created_at', [$startOfYear, $endOfYear])->get();

        $totalKegiatan = $kegiatans->count();
        $totalKehadiranValid = 0;

        $dailyAverages = [];
        $aboveCount = 0;
        $belowCount = 0;

        foreach ($kegiatans as $kegiatan) {
            $pesertaIds = is_array($kegiatan->id_peserta) ? $kegiatan->id_peserta : json_decode($kegiatan->id_peserta, true);

            if (empty($pesertaIds)) {
                continue;
            }

            $jumlahPeserta = count($pesertaIds);

            $tanggalKegiatan = Carbon::parse($kegiatan->waktu_kegiatan);
            $startOfDay = $tanggalKegiatan->copy()->startOfDay();
            $endOfDay = $tanggalKegiatan->copy()->endOfDay();

            $jumlahHadir = AbsensiKaryawan::whereIn('id_karyawan', $pesertaIds)
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->where('keterangan', 'Masuk')
                ->count();

            $persentase = ($jumlahHadir / $jumlahPeserta) * 100;

            $tanggalKey = $tanggalKegiatan->format('Y-m-d');
            $dailyAverages[$tanggalKey] = round($persentase, 1);

            if ($persentase >= 80) {
                $totalKehadiranValid++;
                $aboveCount++;
            } else {
                $belowCount++;
            }
        }

        if ($totalKegiatan == 0) {
            return $this->getDefaultDetailResponse();
        }

        $progress = ($totalKehadiranValid / $totalKegiatan) * 100;
        $progress = round($progress, 1);

        $nilaiTarget = $detail->nilai_target ?? 0;
        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

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
            'pie_chart' => ['above' => $aboveCount, 'below' => $belowCount],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    public function calculatePengeluaranBiayaKaryawan($item, $personId = null)
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

        $startOfYear = Carbon::create($tahun, 1, 1)->startOfDay();
        $endOfYear = Carbon::create($tahun, 12, 31)->endOfDay();

        $parts = explode(',', $detail->manual_value ?? '');

        $gaji = (float) ($parts[0] ?? 0);
        $bpjsManual = (float) ($parts[1] ?? 0);
        $rekrutmenManual = (float) ($parts[2] ?? 0);

        $bpjsIds = JenisTunjangan::whereIn('nama_tunjangan', [
            'BPJS Tenaga Kerja',
            'BPJS Kesehatan'
        ])->pluck('id');

        $bpjsBudget = TunjanganKaryawan::whereBetween('created_at', [$startOfYear, $endOfYear])
            ->whereIn('jenis_tunjangan', $bpjsIds)
            ->sum('total');

        $rekrutmenBudget = Kegiatan::whereBetween('created_at', [$startOfYear, $endOfYear])
            ->where('tipe', 'rekrutment')
            ->sum('realisasi');

        $kegiatanBudget = 0;
        $kegiatanRealisasi = 0;

        $kegiatans = Kegiatan::with('pengajuan_barang.detail')
            ->whereBetween('created_at', [$startOfYear, $endOfYear])
            ->where('tipe', 'kegiatan')
            ->get();

        foreach ($kegiatans as $kegiatan) {
            $kegiatanRealisasi += (float) $kegiatan->realisasi;

            if ($kegiatan->pengajuan_barang) {
                foreach ($kegiatan->pengajuan_barang as $pengajuan) {
                    if ($pengajuan->detail) {
                        foreach ($pengajuan->detail as $d) {
                            $qty = (float) ($d->qty ?? 0);
                            $harga = (float) ($d->harga ?? 0);

                            $kegiatanBudget += $qty * $harga;
                        }
                    }
                }
            }
        }

        $score = 0;

        if ($gaji > 0) {
            $score++;
        }

        if ($bpjsManual > 0 && $bpjsManual <= $bpjsBudget) {
            $score++;
        }

        if ($rekrutmenManual > 0 && $rekrutmenManual <= $rekrutmenBudget) {
            $score++;
        }

        if ($kegiatanRealisasi > 0 && $kegiatanRealisasi <= $kegiatanBudget) {
            $score++;
        }

        $progress = ($score / 4) * 100;

        return round($progress, 1);
    }

    public function calculatePengeluaranBiayaKaryawanDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        $defaultDataManual = [
            'gaji' => 0,
            'bpjs' => 0,
            'rekrutmen' => 0,
            'manual_document' => null,
        ];

        if (!$detail || !is_numeric($detail->detail_jangka)) {
            return array_merge($this->getDefaultDetailResponse(), [
                'dataManual' => $defaultDataManual
            ]);
        }

        $tahun = (int) $detail->detail_jangka;
        $nilaiTarget = (int) $detail->nilai_target;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return array_merge($this->getDefaultDetailResponse(), [
                'dataManual' => $defaultDataManual
            ]);
        }

        $startOfYear = Carbon::create($tahun, 1, 1)->startOfDay();
        $endOfYear = Carbon::create($tahun, 12, 31)->endOfDay();

        $parts = explode(',', $detail->manual_value ?? '');

        $gaji = (float) ($parts[0] ?? 0);
        $bpjsManual = (float) ($parts[1] ?? 0);
        $rekrutmenManual = (float) ($parts[2] ?? 0);

        $dataManual = [
            'gaji' => $gaji,
            'bpjs' => $bpjsManual,
            'rekrutmen' => $rekrutmenManual,
            'manual_document' => $detail->manual_document ?? null,
        ];

        $bpjsIds = JenisTunjangan::whereIn('nama_tunjangan', [
            'BPJS Tenaga Kerja',
            'BPJS Kesehatan'
        ])->pluck('id');

        $bpjsBudget = TunjanganKaryawan::whereBetween('created_at', [$startOfYear, $endOfYear])
            ->whereIn('jenis_tunjangan', $bpjsIds)
            ->sum('total');

        $rekrutmenBudget = Kegiatan::whereBetween('created_at', [$startOfYear, $endOfYear])
            ->where('tipe', 'rekrutment')
            ->sum('realisasi');

        $kegiatanBudget = 0;
        $kegiatanRealisasi = 0;

        $kegiatans = Kegiatan::with('pengajuan_barang.detail')
            ->whereBetween('created_at', [$startOfYear, $endOfYear])
            ->where('tipe', 'kegiatan')
            ->get();

        foreach ($kegiatans as $kegiatan) {
            $kegiatanRealisasi += (float) $kegiatan->realisasi;

            if ($kegiatan->pengajuan_barang) {
                foreach ($kegiatan->pengajuan_barang as $pengajuan) {
                    if ($pengajuan->detail) {
                        foreach ($pengajuan->detail as $d) {
                            $qty = (float) ($d->qty ?? 0);
                            $harga = (float) ($d->harga ?? 0);

                            $kegiatanBudget += $qty * $harga;
                        }
                    }
                }
            }
        }

        $score = 0;

        if ($gaji > 0) {
            $score++;
        }

        if ($bpjsManual > 0 && $bpjsManual <= $bpjsBudget) {
            $score++;
        }

        if ($rekrutmenManual > 0 && $rekrutmenManual <= $rekrutmenBudget) {
            $score++;
        }

        if ($kegiatanRealisasi > 0 && $kegiatanRealisasi <= $kegiatanBudget) {
            $score++;
        }

        $progress = round(($score / 4) * 100, 1);
        $gap = 0;
        if ($progress <= $nilaiTarget) {
            $gap = $progress - $nilaiTarget;
        }

        return [
            'progress' => $progress,
            'gap' => $gap,
            'dataManual' => $dataManual,
            'pie_chart' => [
                'above' => $score,
                'below' => 4 - $score
            ],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
        ];
    }

    public function calculateAdministrasiKaryawan($item, $personId = null)
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

        $dataAdministrasi = AdministrasiKaryawan::whereYear('created_at', $tahun)->get();
        $totalData = $dataAdministrasi->count();

        if ($totalData == 0) {
            return 0;
        }

        $totalSkor = 0;
        $penaltyPerDay = 0.1;
        $maxLateDays = 7;

        foreach ($dataAdministrasi as $data) {
            if ($data->status === 'selesai') {
                if ($data->dateline && $data->tanggal_selesai) {
                    $dateline = Carbon::parse($data->dateline);
                    $selesai  = Carbon::parse($data->tanggal_selesai);

                    $daysLate = $selesai->greaterThan($dateline) ? $selesai->diffInDays($dateline) : 0;

                    if ($daysLate >= $maxLateDays) {
                        $skor = 0;
                    } else {
                        $skor = max(0, 1 - ($daysLate * $penaltyPerDay));
                    }
                } else {
                    $skor = 0;
                }
            } else {
                $skor = 0;
            }

            $totalSkor += $skor;
        }

        $progress = ($totalSkor / $totalData) * 100;

        return round($progress, 2);
    }

    public function calculateAdministrasiKaryawanDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("detailTargetKPI atau detail_jangka tidak ditemukan untuk item ID: {$itemDetail->id}");
            return $this->getDefaultDetailResponse();
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk item ID: {$itemDetail->id}");
            return $this->getDefaultDetailResponse();
        }

        $allData = AdministrasiKaryawan::whereYear('created_at', $tahun)->get();
        $totalRecords = $allData->count();

        $groupedByMonth = $allData->groupBy(fn($d) => Carbon::parse($d->created_at)->format('Y-m'));

        $penaltyPerDay = 0.1;
        $maxLateDays   = 7;
        $totalSkor     = 0;
        $perfectCount  = 0;

        $monthlyData            = [];
        $monthlyProgress        = [];
        $dailyBreakdownPerMonth = [];
        $dailyProgressPerMonth  = [];

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $monthKey = sprintf('%04d-%02d', $tahun, $bulan);
            $monthRecords = $groupedByMonth->get($monthKey, collect());
            $monthTotal   = $monthRecords->count();

            $monthSkor = 0;
            $monthDailyBreakdown = [];
            $monthDailyProgress  = [];

            if ($monthTotal > 0) {
                foreach ($monthRecords as $data) {
                    $skor = 0;

                    if ($data->status === 'selesai' && $data->dateline && $data->tanggal_selesai) {
                        $dateline = Carbon::parse($data->dateline);
                        $selesai  = Carbon::parse($data->tanggal_selesai);

                        $daysLate = $selesai->greaterThan($dateline) ? $selesai->diffInDays($dateline) : 0;

                        if ($daysLate >= $maxLateDays) {
                            $skor = 0;
                        } else {
                            $skor = max(0, 1 - ($daysLate * $penaltyPerDay));
                        }
                    }

                    $monthSkor += $skor;
                    if ($skor == 1) $perfectCount++;

                    $dayKey = $data->tanggal_selesai
                        ? Carbon::parse($data->tanggal_selesai)->format('Y-m-d')
                        : sprintf('%04d-%02d-10', $tahun, $bulan);

                    $scorePercent = $skor * 100;
                    $monthDailyBreakdown[$dayKey] = $scorePercent;
                    $monthDailyProgress[$dayKey]  = $scorePercent;
                }

                $monthProgress = ($monthSkor / $monthTotal) * 100;
            } else {
                $monthProgress = 0;
                $dayKey = sprintf('%04d-%02d-10', $tahun, $bulan);
                $monthDailyBreakdown[$dayKey] = 0;
                $monthDailyProgress[$dayKey]  = 0;
            }

            $monthlyData[$monthKey]            = round($monthProgress, 1);
            $monthlyProgress[$monthKey]        = round($monthProgress, 1);
            $dailyBreakdownPerMonth[$monthKey] = $monthDailyBreakdown;
            $dailyProgressPerMonth[$monthKey]  = $monthDailyProgress;

            $totalSkor += $monthSkor;
        }

        $progress = $totalRecords > 0 ? round(($totalSkor / $totalRecords) * 100, 1) : 0;
        $nilaiTarget = $itemDetail->detailTargetKPI->pluck('nilai_target')->first() ?? 0;
        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $perfectCount;
        $below = $totalRecords - $perfectCount;

        ksort($monthlyData);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgress);
        ksort($dailyProgressPerMonth);

        return [
            'progress'               => $progress,
            'gap'                    => $gap,
            'pie_chart'              => ['above' => $above, 'below' => $below],
            'monthly_data'           => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress'       => $monthlyProgress,
            'daily_progress_per_month'  => $dailyProgressPerMonth,
        ];
    }
}
