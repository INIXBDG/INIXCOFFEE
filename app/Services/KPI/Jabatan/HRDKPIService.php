<?php

namespace App\Services\KPI\Jabatan;

use App\Models\AbsensiKaryawan;
use App\Models\AdministrasiKaryawan;
use App\Models\karyawan;
use App\Models\Kegiatan;
use App\Models\LogGaji;
use App\Models\Pelamar;
use App\Models\PerhitunganTunjanganHR;
use App\Traits\KPIDefaultResponseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class HRDKPIService
{
    use KPIDefaultResponseTrait;

    private const BOBOT_KOMPONEN = 25;

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

        $kegiatans = Kegiatan::select('id', 'id_peserta', 'waktu_kegiatan')->whereYear('created_at', $tahun)->get();

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

    public function calculatePelaksanaanKegiatanKaryawanDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (is_null($detail) || !$detail->detail_jangka) {
            return $this->getDefaultDetailResponse();
        }

        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return $this->getDefaultDetailResponse();
        }

        $startOfYear = Carbon::create($tahun, 1, 1)->startOfDay();
        $endOfYear = Carbon::create($tahun, 12, 31)->endOfDay();

        $kegiatans = Kegiatan::select('id', 'id_peserta', 'waktu_kegiatan', 'created_at')->whereBetween('created_at', [$startOfYear, $endOfYear])->get();

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

        $hasil = $this->hitungPengeluaran($tahun);

        return round($hasil['total'], 1);
    }

    public function calculatePengeluaranBiayaKaryawanDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();
        $nilaiTarget = (int) ($detail->nilai_target ?? 0);

        $defaultResponse = array_merge($this->getDefaultDetailResponse(), [
            'dataManual' => ['gaji' => 0, 'bpjs' => 0, 'rekrutmen' => 0, 'manual_document' => null]
        ]);

        if (!$detail || !is_numeric($detail->detail_jangka)) {
            return $defaultResponse;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return $defaultResponse;
        }

        $hasil = $this->hitungPengeluaran($tahun);

        $progress = round($hasil['total'], 1);
        $gap = ($progress <= $nilaiTarget) ? ($progress - $nilaiTarget) : 0;

        $penuh = 0;
        foreach (['gaji', 'bpjs', 'rekrutmen', 'kegiatan'] as $komponen) {
            if ($hasil[$komponen]['skor'] >= self::BOBOT_KOMPONEN - 0.0001) {
                $penuh++;
            }
        }

        return [
            'progress' => $progress,
            'gap' => $gap,
            'dataManual' => [
                'gaji' => round($hasil['gaji']['cakupan'], 1),
                'bpjs' => round($hasil['bpjs']['cakupan'], 1),
                'rekrutmen' => round($hasil['rekrutmen']['cakupan'], 1),
                'manual_document' => $detail->manual_document ?? null,
            ],
            'component_scores' => [
                'gaji' => round($hasil['gaji']['skor'], 1),
                'bpjs' => round($hasil['bpjs']['skor'], 1),
                'rekrutmen' => round($hasil['rekrutmen']['skor'], 1),
                'kegiatan' => round($hasil['kegiatan']['skor'], 1),
            ],
            'component_detail' => [
                'gaji' => [
                    'bulan_dinilai' => $hasil['gaji']['bulan_dinilai'],
                    'bulan_lengkap' => $hasil['gaji']['bulan_lengkap'],
                    'cakupan' => round($hasil['gaji']['cakupan'], 1),
                ],
                'bpjs' => [
                    'karyawan_wajib' => $hasil['bpjs']['total'],
                    'karyawan_terisi' => $hasil['bpjs']['terisi'],
                    'cakupan' => round($hasil['bpjs']['cakupan'], 1),
                ],
                'rekrutmen' => [
                    'posisi_dibuka' => $hasil['rekrutmen']['dibuka'],
                    'posisi_berjalan' => $hasil['rekrutmen']['berjalan'],
                    'cakupan' => round($hasil['rekrutmen']['cakupan'], 1),
                ],
                'kegiatan' => [
                    'budget' => $hasil['kegiatan']['budget'],
                    'realisasi' => $hasil['kegiatan']['realisasi'],
                ],
            ],
            'pie_chart' => [
                'above' => $penuh,
                'below' => 4 - $penuh
            ],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
        ];
    }

    private function hitungPengeluaran(int $tahun): array
    {
        $gaji = $this->skorGaji($tahun);
        $bpjs = $this->skorBpjs();
        $rekrutmen = $this->skorRekrutmen($tahun);
        $kegiatan = $this->skorKegiatan($tahun);

        return [
            'gaji' => $gaji,
            'bpjs' => $bpjs,
            'rekrutmen' => $rekrutmen,
            'kegiatan' => $kegiatan,
            'total' => $gaji['skor'] + $bpjs['skor'] + $rekrutmen['skor'] + $kegiatan['skor'],
        ];
    }

    private function skorGaji(int $tahun): array
    {
        $kosong = ['skor' => 0.0, 'cakupan' => 0.0, 'bulan_dinilai' => 0, 'bulan_lengkap' => 0];

        $sekarang = now();
        if ($tahun < $sekarang->year) {
            $bulanAkhir = 12;
        } elseif ($tahun === $sekarang->year) {
            $bulanAkhir = $sekarang->month - 1;
        } else {
            $bulanAkhir = 0;
        }

        if ($bulanAkhir < 1) {
            return $kosong;
        }

        $karyawans = karyawan::query()
            ->where('status_aktif', '1')
            ->whereNotNull('nip')
            ->whereNotIn('jabatan', ['Outsource', 'Pilih Jabatan', 'Komisaris', 'Direktur'])
            ->whereNotIn('id', [1, 3])
            ->where(function ($q) {
                $q->whereNull('kode_karyawan')->orWhere('kode_karyawan', 'NOT LIKE', 'OL%');
            })
            ->select(['id', 'awal_probation', 'awal_kontrak', 'resigned_at'])
            ->get();

        if ($karyawans->isEmpty()) {
            return $kosong;
        }

        $logPerBulan = LogGaji::where('tahun', $tahun)
            ->where('bulan', '<=', $bulanAkhir)
            ->whereIn('id_karyawan', $karyawans->pluck('id'))
            ->get(['id_karyawan', 'bulan'])
            ->groupBy(fn($l) => (int) $l->bulan)
            ->map(fn($g) => $g->pluck('id_karyawan')->map(fn($v) => (int) $v)->unique()->values()->all());

        $rasio = [];
        $lengkap = 0;

        for ($bulan = 1; $bulan <= $bulanAkhir; $bulan++) {
            $awal = Carbon::create($tahun, $bulan, 1)->startOfMonth();
            $akhir = $awal->copy()->endOfMonth();

            $wajib = $karyawans
                ->filter(fn($k) => $this->aktifPadaPeriode($k, $awal, $akhir))
                ->pluck('id')
                ->map(fn($v) => (int) $v)
                ->all();

            if (empty($wajib)) {
                continue;
            }

            $sudah = count(array_intersect($wajib, $logPerBulan->get($bulan, [])));
            $nilai = $sudah / count($wajib);
            $rasio[] = $nilai;

            if ($sudah === count($wajib)) {
                $lengkap++;
            }
        }

        if (empty($rasio)) {
            return $kosong;
        }

        $cakupan = (array_sum($rasio) / count($rasio)) * 100;

        return [
            'skor' => ($cakupan / 100) * self::BOBOT_KOMPONEN,
            'cakupan' => $cakupan,
            'bulan_dinilai' => count($rasio),
            'bulan_lengkap' => $lengkap,
        ];
    }

    private function aktifPadaPeriode($karyawan, Carbon $awal, Carbon $akhir): bool
    {
        $mulai = $karyawan->awal_probation ?: $karyawan->awal_kontrak;
        if ($mulai && Carbon::parse($mulai)->startOfDay()->gt($akhir)) {
            return false;
        }

        if (!empty($karyawan->resigned_at) && Carbon::parse($karyawan->resigned_at)->startOfDay()->lt($awal)) {
            return false;
        }

        return true;
    }

    private function skorBpjs(): array
    {
        $wajib = karyawan::query()
            ->where('status_aktif', '1')
            ->whereNotNull('nip')
            ->where(function ($q) {
                $q->where('id', 2)
                    ->orWhere(function ($q) {
                        $q->whereRaw("LOWER(COALESCE(jabatan, '')) NOT LIKE ?", ['%direksi%'])
                            ->whereRaw("LOWER(COALESCE(jabatan, '')) NOT LIKE ?", ['%direktur%'])
                            ->whereRaw("LOWER(COALESCE(jabatan, '')) NOT LIKE ?", ['%komisaris%'])
                            ->whereNotIn('jabatan', ['Outsource', 'Pilih Jabatan']);
                    });
            })
            ->whereNotIn('id', [1, 3])
            ->where(function ($q) {
                $q->whereNull('kode_karyawan')->orWhere('kode_karyawan', 'NOT LIKE', 'OL%');
            })
            ->where(function ($q) {
                $q->whereNull('bpjs_status')->orWhereIn('bpjs_status', ['', 'active']);
            })
            ->pluck('id')
            ->map(fn($v) => (int) $v)
            ->all();

        $total = count($wajib);
        if ($total === 0) {
            return ['skor' => 0.0, 'cakupan' => 0.0, 'total' => 0, 'terisi' => 0];
        }

        $adaData = PerhitunganTunjanganHR::whereIn('karyawan_id', $wajib)
            ->pluck('karyawan_id')
            ->map(fn($v) => (int) $v)
            ->unique()
            ->all();

        $terisi = count(array_intersect($wajib, $adaData));
        $cakupan = ($terisi / $total) * 100;

        return [
            'skor' => ($cakupan / 100) * self::BOBOT_KOMPONEN,
            'cakupan' => $cakupan,
            'total' => $total,
            'terisi' => $terisi,
        ];
    }

    private function skorRekrutmen(int $tahun): array
    {
        $pelamars = Pelamar::aktif()
            ->whereYear('tanggal_melamar', $tahun)
            ->get(['divisi', 'jabatan', 'tahap_rekrutmen', 'karyawan_id']);

        $posisi = $pelamars->groupBy(
            fn($p) => strtolower(trim((string) $p->divisi)) . '|' . strtolower(trim((string) $p->jabatan))
        );

        $dibuka = $posisi->count();
        if ($dibuka === 0) {
            return ['skor' => (float) self::BOBOT_KOMPONEN, 'cakupan' => 100.0, 'dibuka' => 0, 'berjalan' => 0];
        }

        $berjalan = $posisi->filter(
            fn($grup) => $grup->contains(
                fn($p) => $p->tahap_rekrutmen === 'hired' || !empty($p->karyawan_id)
            )
        )->count();

        $cakupan = ($berjalan / $dibuka) * 100;

        return [
            'skor' => ($cakupan / 100) * self::BOBOT_KOMPONEN,
            'cakupan' => $cakupan,
            'dibuka' => $dibuka,
            'berjalan' => $berjalan,
        ];
    }

    private function skorKegiatan(int $tahun): array
    {
        $startOfYear = Carbon::create($tahun, 1, 1)->startOfDay();
        $endOfYear = Carbon::create($tahun, 12, 31)->endOfDay();

        $kegiatanBudget = 0;
        $kegiatanRealisasi = 0;
        $kegiatans = Kegiatan::with('pengajuan_barang.detail')
            ->select('id', 'realisasi')
            ->whereBetween('created_at', [$startOfYear, $endOfYear])
            ->where('tipe', 'kegiatan')
            ->get();

        foreach ($kegiatans as $kegiatan) {
            $kegiatanRealisasi += (float) $kegiatan->realisasi;
            if ($kegiatan->pengajuan_barang) {
                foreach ($kegiatan->pengajuan_barang as $pengajuan) {
                    if ($pengajuan->detail) {
                        foreach ($pengajuan->detail as $d) {
                            $kegiatanBudget += (float) ($d->qty ?? 0) * (float) ($d->harga ?? 0);
                        }
                    }
                }
            }
        }

        $skor = 0.0;
        if ($kegiatanBudget > 0) {
            if ($kegiatanRealisasi <= $kegiatanBudget) {
                $skor = (float) self::BOBOT_KOMPONEN;
            } else {
                $overPercentage = (($kegiatanRealisasi - $kegiatanBudget) / $kegiatanBudget) * 100;
                $skor = max(100 - $overPercentage, 0) / 100 * self::BOBOT_KOMPONEN;
            }
        }

        return [
            'skor' => $skor,
            'budget' => $kegiatanBudget,
            'realisasi' => $kegiatanRealisasi,
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

        $dataAdministrasi = AdministrasiKaryawan::select('status', 'dateline', 'tanggal_selesai')->whereYear('created_at', $tahun)->get();
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

        $allData = AdministrasiKaryawan::select('created_at', 'status', 'dateline', 'tanggal_selesai')->whereYear('created_at', $tahun)->get();
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