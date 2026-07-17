<?php

namespace App\Services\KPI\Jabatan;

use App\Models\Peluang;
use App\Models\RKM;
use App\Models\karyawan;
use App\Models\detailPersonKPI;
use App\Models\targetKPI;
use App\Models\target as ModelsTarget;
use App\Models\User;
use App\Models\perhitunganNetSales;
use App\Traits\KPIDefaultResponseTrait;
use App\Services\KPI\Jabatan\GMKPIService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SalesKPIService
{
    use KPIDefaultResponseTrait;

    public function calculateTargetPenjualanTahunan($item, $personId)
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

        $totalSales = RKM::where('status', '0')
            ->whereYear('tanggal_awal', $tahun);

        if ($personId !== null) {
            $personId = detailPersonKPI::where('detailTargetKey', $detail->id)->first()?->id_karyawan;

            $kodeKaryawan = karyawan::where('id', $personId)->value('kode_karyawan');
            if (!$kodeKaryawan) {
                return 0;
            }

            $totalSales = $totalSales->where('sales_key', $kodeKaryawan)
                ->select(DB::raw('SUM(CAST(harga_jual AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total_sales'))
                ->value('total_sales');
        } else {
            $totalSales = $totalSales
                ->select(DB::raw('SUM(CAST(harga_jual AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total_sales'))
                ->value('total_sales');
        }

        $dataTarget = targetKPI::with('detailTargetKPI')
            ->whereHas(
                'dataTarget',
                fn($q) => $q->where('asistant_route', 'pemasukan kotor')
            )
            ->first();

        $targetGM = ModelsTarget::where('quartal', 'All')->first() ?? null;

        $target = $dataTarget->detailTargetKPI->first()->nilai_target
            ?? $targetGM->target
            ?? 0;

        $progressRupiah = (float) ($totalSales ?? 0);

        $progress = $target > 0 ? ($progressRupiah / $target) * 100 : 0;

        return round($progress, 1);
    }

    public function calculateTargetPenjualanTahunanDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return array_merge($this->getDefaultDetailResponse(), [
                'triwulan_data' => [],
                'sales_performance' => null,
            ]);
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return array_merge($this->getDefaultDetailResponse(), [
                'triwulan_data' => [],
                'sales_performance' => null,
            ]);
        }

        $kodeKaryawan = null;
        $karyawanData = null;

        if ($personId !== null) {
            $karyawanData = karyawan::find($personId);
            $kodeKaryawan = $karyawanData ? $karyawanData->kode_karyawan : null;
        }

        $query = RKM::where('status', '0')
            ->whereYear('tanggal_awal', $tahun);

        if ($kodeKaryawan) {
            $query->where('sales_key', $kodeKaryawan);
        }

        $sales = $query->select(DB::raw('tanggal_awal, SUM(CAST(harga_jual AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total'))
            ->groupBy('tanggal_awal')
            ->get();

        $totalSales = 0;
        $dailyBreakdownPerMonth = [];
        $monthlyDataTemp = [];
        $triwulanDataTemp = [1 => 0, 2 => 0, 3 => 0, 4 => 0];

        foreach ($sales as $row) {
            $date = Carbon::parse($row->tanggal_awal);
            $dateKey = $date->format('Y-m-d');
            $monthKey = $date->format('Y-m');
            $total = (float) ($row->total ?? 0);

            $totalSales += $total;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dateKey] = (float) number_format($total, 1, '.', '');

            if (!isset($monthlyDataTemp[$monthKey])) {
                $monthlyDataTemp[$monthKey] = 0;
            }
            $monthlyDataTemp[$monthKey] += $total;

            $month = (int) $date->format('m');
            $triwulan = (int) ceil($month / 3);
            if (isset($triwulanDataTemp[$triwulan])) {
                $triwulanDataTemp[$triwulan] += $total;
            }
        }

        $monthlyData = [];
        foreach ($monthlyDataTemp as $month => $total) {
            $monthlyData[$month] = (float) number_format($total, 1, '.', '');
        }

        ksort($monthlyData);
        ksort($dailyBreakdownPerMonth);

        $triwulanData = [];
        for ($i = 1; $i <= 4; $i++) {
            $triwulanData['Triwulan_' . $i] = (float) number_format($triwulanDataTemp[$i], 1, '.', '');
        }

        $progressRupiah = (float) number_format($totalSales, 1, '.', '');

        $dataTarget = targetKPI::with(['detailTargetKPI', 'dataTarget'])
            ->whereHas('dataTarget', function ($q) {
                $q->where('asistant_route', 'Pemasukan Kotor');
            })
            ->first();

        $targetGM = ModelsTarget::where('quartal', 'All')->first() ?? null;
        $targetGlobal = (float) ($dataTarget->detailTargetKPI->first()->nilai_target ?? $targetGM->target ?? 0);

        $progressGlobal = $targetGlobal > 0 ? ($progressRupiah / $targetGlobal) * 100 : 0;
        $gap = $progressGlobal - $nilaiTarget;

        $above = $totalSales >= $targetGlobal ? 1 : 0;
        $below = 1 - $above;

        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($monthlyData as $month => $value) {
            $monthlyProgress[$month] = $targetGlobal > 0
                ? (float) number_format(($value / $targetGlobal) * 100, 1, '.', '')
                : 0;
        }

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $day => $value) {
                if (!isset($dailyProgressPerMonth[$month])) {
                    $dailyProgressPerMonth[$month] = [];
                }
                $dailyProgressPerMonth[$month][$day] = $targetGlobal > 0
                    ? (float) number_format(($value / $targetGlobal) * 100, 1, '.', '')
                    : 0;
            }
        }

        $salesPerformance = null;

        if ($personId === null) {
            $allSalesData = [];

            $allKaryawan = karyawan::where(function ($q) {
                $q->where('status_aktif', '1')
                    ->whereNot('jabatan', 'Outsource')
                    ->where('kode_karyawan', 'NOT LIKE', 'OL%')
                    ->whereNot('jabatan', 'Pilih Jabatan')
                    ->whereNotNull('nip')
                    ->whereNot('divisi', 'Direksi')
                    ->orWhereNull('status_aktif');
            })
                ->where(function ($q) {
                    $q->where('jabatan', 'Sales')
                        ->orWhere('jabatan', 'Sales Executive')
                        ->orWhere('jabatan', 'Account Manager')
                        ->orWhereNull('jabatan')
                        ->where('status_aktif', '1');
                })
                ->get();

            foreach ($allKaryawan as $karyawanItem) {
                $salesKey = $karyawanItem->kode_karyawan;

                if (!$salesKey) {
                    continue;
                }

                $salesRevenue = RKM::where('status', '0')
                    ->whereYear('tanggal_awal', $tahun)
                    ->where('sales_key', $salesKey)
                    ->select(DB::raw('SUM(CAST(harga_jual AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total'))
                    ->value('total');

                $salesRevenue = (float) ($salesRevenue ?? 0);

                $detailPerson = detailPersonKPI::where('id_target', $itemDetail->id)
                    ->where('id_karyawan', $karyawanItem->id)
                    ->first();

                $presentaseKemampuan = (float) ($detailPerson->presentase_kemampuan ?? 0);
                $idDetailPerson = $detailPerson->id ?? null;

                $percentage = $presentaseKemampuan > 0 ? ($salesRevenue / $presentaseKemampuan) * 100 : 0;

                $allSalesData[] = [
                    'kode_karyawan' => (string) $salesKey,
                    'nama' => (string) ($karyawanItem->nama_lengkap ?? $karyawanItem->nama ?? $salesKey),
                    'revenue' => (float) number_format($salesRevenue, 1, '.', ''),
                    'id_detailPerson' => $idDetailPerson,
                    'presentase_kemampuan' => (float) number_format($presentaseKemampuan, 1, '.', ''),
                    'percentage' => (float) number_format($percentage, 1, '.', ''),
                    'status' => $salesRevenue >= $presentaseKemampuan ? 'achieved' : 'pending'
                ];
            }

            $salesPerformance = [
                'type' => 'all',
                'data' => $allSalesData
            ];
        } else {
            $detailPerson = detailPersonKPI::where('id_target', $itemDetail->id)
                ->where('id_karyawan', $personId)
                ->first();

            $presentaseKemampuan = (float) ($detailPerson->presentase_kemampuan ?? 0);
            $idDetailPerson = $detailPerson->id ?? null;

            $percentage = $presentaseKemampuan > 0 ? ($totalSales / $presentaseKemampuan) * 100 : 0;

            $karyawanName = $karyawanData ? ($karyawanData->nama_lengkap ?? $karyawanData->nama ?? '') : '';

            $salesPerformance = [
                'type' => 'individual',
                'data' => [
                    'kode_karyawan' => (string) $kodeKaryawan,
                    'nama' => (string) $karyawanName,
                    'revenue' => (float) number_format($totalSales, 1, '.', ''),
                    'id_detailPerson' => $idDetailPerson,
                    'presentase_kemampuan' => (float) number_format($presentaseKemampuan, 1, '.', ''),
                    'percentage' => (float) number_format($percentage, 1, '.', ''),
                    'status' => $totalSales >= $presentaseKemampuan ? 'achieved' : 'pending'
                ]
            ];
        }

        return [
            'progress' => (float) number_format($progressGlobal, 1, '.', ''),
            'gap' => (float) number_format($gap, 1, '.', ''),
            'dataManual' => [
                'manual_document' => $detail->manual_document ?? null,
            ],
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
            'triwulan_data' => $triwulanData,
            'sales_performance' => $salesPerformance,
        ];
    }

    public function calculateBiayaAkuisisiClient($item, $personId)
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

        $peluang = Peluang::with('rkm.perhitunganNetSales')
            ->whereYear('created_at', $tahun)
            ->get();

        $actualCAC = 0;

        // Memanggil metode calculatePemasukanKotor dari GMKPIService
        $targetTahunan = app(GMKPIService::class)->calculatePemasukanKotor($item, $personId);

        foreach ($peluang as $p) {
            if ($p->tahap === 'merah') {
                if ($p->rkm && $p->rkm->perhitunganNetSales) {
                    foreach ($p->rkm->perhitunganNetSales as $perhitungan) {
                        $actualCAC +=
                            ($perhitungan->transportasi ?? 0) +
                            ($perhitungan->akomodasi_peserta ?? 0) +
                            ($perhitungan->akomodasi_tim ?? 0) +
                            ($perhitungan->fresh_money ?? 0) +
                            ($perhitungan->entertaint ?? 0) +
                            ($perhitungan->souvenir ?? 0) +
                            ($perhitungan->cashback ?? 0) +
                            ($perhitungan->sewa_laptop ?? 0);
                    }
                }
            }
        }

        if ($actualCAC <= 0) {
            return 0.0;
        }

        $maxCAC = ($nilaiTarget / 100) * $targetTahunan;

        if ($maxCAC <= 0) {
            return 0.0;
        }

        $progress = min(($maxCAC / $actualCAC) * 100, 100);

        return round($progress, 2);
    }

    public function calculateBiayaAkuisisiClientDetail($itemDetail, $personId = null)
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

        // Memanggil metode calculatePemasukanKotor dari GMKPIService
        $targetTahunanUnit = app(GMKPIService::class)->calculatePemasukanKotor($itemDetail, $personId);

        $peluangs = Peluang::with('rkm.perhitunganNetSales')
            ->whereYear('created_at', $tahun)
            ->get();

        $actualCAC = 0;
        $dailyBreakdownPerMonth = [];

        $maxCAC = ($nilaiTarget / 100) * $targetTahunanUnit;

        foreach ($peluangs as $p) {
            if ($p->tahap !== 'merah') {
                continue;
            }

            $totalBiayaPeluang = 0;
            if ($p->rkm && $p->rkm->perhitunganNetSales) {
                foreach ($p->rkm->perhitunganNetSales as $perhitungan) {
                    $totalBiayaPeluang += ($perhitungan->transportasi ?? 0)
                        + ($perhitungan->akomodasi_peserta ?? 0)
                        + ($perhitungan->akomodasi_tim ?? 0)
                        + ($perhitungan->fresh_money ?? 0)
                        + ($perhitungan->entertaint ?? 0)
                        + ($perhitungan->souvenir ?? 0)
                        + ($perhitungan->cashback ?? 0)
                        + ($perhitungan->sewa_laptop ?? 0);
                }
            }

            $actualCAC += $totalBiayaPeluang;

            $date = Carbon::parse($p->created_at);
            $dateKey = $date->format('Y-m-d');
            $monthKey = $date->format('Y-m');

            if (!isset($dailyBreakdownPerMonth[$monthKey][$dateKey])) {
                $dailyBreakdownPerMonth[$monthKey][$dateKey] = 0;
            }
            $dailyBreakdownPerMonth[$monthKey][$dateKey] += $totalBiayaPeluang;
        }

        $progress = 0;
        if ($actualCAC > 0) {
            $progress = min(($maxCAC / $actualCAC) * 100, 100);
        }

        $monthlyData = [];
        foreach ($dailyBreakdownPerMonth as $month => $days) {
            $monthlyData[$month] = round(array_sum($days) / count($days), 1);
        }

        ksort($monthlyData);
        ksort($dailyBreakdownPerMonth);

        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($monthlyData as $month => $value) {
            $monthlyProgress[$month] = $maxCAC > 0 ? round(($value / $maxCAC) * 100, 1) : 0;
        }

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $day => $value) {
                if (!isset($dailyProgressPerMonth[$month])) {
                    $dailyProgressPerMonth[$month] = [];
                }
                $dailyProgressPerMonth[$month][$day] = $maxCAC > 0 ? round(($value / $maxCAC) * 100, 1) : 0;
            }
        }

        $gapRaw = $maxCAC - $actualCAC;
        if ($progress > $nilaiTarget) {
            $gapRaw = 0;
        }
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        return array_merge($this->getDefaultDetailResponse(), [
            'progress' => round($progress, 1),
            'actual_cac' => $actualCAC,
            'max_cac' => $maxCAC,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $actualCAC > $maxCAC ? 1 : 0,
                'below' => $actualCAC <= $maxCAC ? 1 : 0
            ],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ]);
    }

    public function calculatePeningkatanKemampuanKompetensiSales($item, $personId)
    {
        $nilaiUkur = 90;

        $jabatanSales = ['Sales', 'SPV Sales', 'Adm Sales'];

        // 1. Standarisasi daftar username dari database (lowercase & trim)
        $allowedUsernames = User::whereIn('jabatan', $jabatanSales)
            ->pluck('username')
            ->filter() // Mengabaikan nilai null
            ->map(fn($username) => strtolower(trim($username)))
            ->toArray();

        $response = Http::get('https://coffee.inixindobdg.co.id/api/moodle-grades-sharingknowledge');
        if (!$response->successful()) {
            return 0;
        }

        $dataKnowledge = json_decode($response->body(), true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($dataKnowledge['data'])) {
            return 0;
        }

        // 2. Standarisasi username dari API Moodle saat dibungkus ke dalam Collection
        $collection = collect($dataKnowledge['data']['data'] ?? [])->map(function ($row) {
            if (isset($row['username'])) {
                $row['username'] = strtolower(trim($row['username']));
            }
            return $row;
        });

        if ($personId !== null) {
            $userLogin = User::find($personId);

            if (!$userLogin || empty($userLogin->username)) {
                return 0;
            }

            // 3. Standarisasi username milik user yang dicek
            $loginUsername = strtolower(trim($userLogin->username));

            if (!in_array($loginUsername, $allowedUsernames)) {
                return 0;
            }

            // Pencarian kini 100% aman karena kedua sisi sudah lowercase & tanpa spasi
            $filteredData = $collection->where('username', $loginUsername);
        } else {
            // Pencarian global divisi kini 100% aman
            $filteredData = $collection->whereIn('username', $allowedUsernames);
        }

        $totalPenilaian = $filteredData->count();
        if ($totalPenilaian === 0) {
            return 0;
        }

        $totalMelebihiNilaiUkur = $filteredData->filter(function ($row) use ($nilaiUkur) {
            return (float) ($row['score'] ?? 0) > $nilaiUkur;
        })->count();

        $progress = ($totalMelebihiNilaiUkur / $totalPenilaian) * 100;

        return round($progress, 2);
    }

    public function calculatePeningkatanKemampuanKompetensiSalesDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $this->getDefaultDetailResponse();
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;
        $nilaiUkur = 90;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $this->getDefaultDetailResponse();
        }

        // 1. Standarisasi daftar username dari database (lowercase & trim)
        $jabatanSales = ['Sales', 'SPV Sales', 'Adm Sales']; // Diselaraskan
        $allowedUsernames = User::whereIn('jabatan', $jabatanSales)
            ->pluck('username')
            ->filter()
            ->map(fn($username) => strtolower(trim($username)))
            ->toArray();

        if (empty($allowedUsernames)) {
            return $this->getDefaultDetailResponse();
        }

        // 2. Inisialisasi Pencarian Individu DI LUAR LOOP (Mencegah Query N+1)
        $loginUsername = null;
        if ($personId !== null) {
            $userLogin = User::find($personId);

            if (!$userLogin || empty($userLogin->username)) {
                return $this->getDefaultDetailResponse();
            }

            // Standarisasi username karyawan yang sedang dicek
            $loginUsername = strtolower(trim($userLogin->username));

            // Jika username-nya bukan bagian dari tim sales, kembalikan default
            if (!in_array($loginUsername, $allowedUsernames)) {
                return $this->getDefaultDetailResponse();
            }
        }

        // 3. Integrasi Data API HTTP Client
        try {
            $response = Http::get('https://coffee.inixindobdg.co.id/api/moodle-grades-sharingknowledge');
            if (!$response->successful()) {
                return $this->getDefaultDetailResponse();
            }
            $dataKnowledge = json_decode($response->body(), true);
        } catch (\Exception $e) {
            return $this->getDefaultDetailResponse();
        }

        $moodleData = $dataKnowledge['data']['data'] ?? null;
        if (empty($moodleData) || !is_array($moodleData)) {
            return $this->getDefaultDetailResponse();
        }

        // 4. Inisialisasi Struktur Penampung Data
        $totalPenilaian = 0;
        $totalMelebihiNilaiUkur = 0;

        $monthlyDataTemp = [];
        $dailyBreakdownPerMonth = [];

        // 5. Pengolahan Data Menggunakan Foreach
        foreach ($moodleData as $data) {
            if (empty($data['username'])) {
                continue;
            }

            // Standarisasi username dari API
            $usernameData = strtolower(trim($data['username']));
            $dateString = $data['activity_submitted_at'] ?? $data['activity_created_at'] ?? null;

            // Kondisional Filter Username
            $isValidUser = false;
            if ($personId !== null) {
                // Mode individu: Cukup cek kecocokan 1 vs 1 (yang mana loginUsername sudah terfilter di allowedUsernames sebelumnya)
                if ($usernameData === $loginUsername) {
                    $isValidUser = true;
                }
            } else {
                // Mode global divisi: Cek ke dalam array whitelist
                if (in_array($usernameData, $allowedUsernames)) {
                    $isValidUser = true;
                }
            }

            // Jika User Valid dan memiliki tanggal
            if ($isValidUser && $dateString) {
                $date = Carbon::parse($dateString);

                // Filter berdasarkan jangka tahun target KPI
                if ($date->year === $tahun) {
                    $totalPenilaian++;
                    $score = (float) ($data['score'] ?? 0);

                    $dateKey = $date->format('Y-m-d');
                    $monthKey = $date->format('Y-m');

                    if ($score > $nilaiUkur) {
                        $totalMelebihiNilaiUkur++;

                        // Simpan jumlah data absolut yang memenuhi standar
                        $monthlyDataTemp[$monthKey] = ($monthlyDataTemp[$monthKey] ?? 0) + 1;

                        if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                            $dailyBreakdownPerMonth[$monthKey] = [];
                        }
                        $dailyBreakdownPerMonth[$monthKey][$dateKey] = ($dailyBreakdownPerMonth[$monthKey][$dateKey] ?? 0) + 1;
                    }
                }
            }
        }

        // Jika tidak ada total data penilaian sama sekali
        if ($totalPenilaian === 0) {
            return $this->getDefaultDetailResponse();
        }

        // 6. Kalkulasi Progress Utama & Gap
        $progress = round(($totalMelebihiNilaiUkur / $totalPenilaian) * 100, 2);

        $gap = 0;
        if ($progress <= $nilaiTarget) {
            $gapRaw = $progress - $nilaiTarget;
            $gap = rtrim(rtrim(sprintf('%.2f', $gapRaw), '0'), '.');
        }

        // 7. Mempertahankan Format Output Sesuai Spesifikasi Grafik Frontend
        $monthlyData = [];
        foreach ($monthlyDataTemp as $month => $total) {
            $monthlyData[$month] = round($total, 1);
        }

        ksort($monthlyData);
        ksort($dailyBreakdownPerMonth);

        $monthlyProgress = [];
        foreach ($monthlyData as $month => $value) {
            $monthlyProgress[$month] = $nilaiTarget > 0
                ? round(($value / $nilaiTarget) * 100, 2)
                : 0;
        }

        $dailyProgressPerMonth = [];
        foreach ($dailyBreakdownPerMonth as $month => $days) {
            ksort($dailyBreakdownPerMonth[$month]); // Urutkan tanggal harian secara kronologis
            foreach ($days as $date => $value) {
                $dailyProgressPerMonth[$month][$date] = $nilaiTarget > 0
                    ? round(($value / $nilaiTarget) * 100, 2)
                    : 0;
            }
        }

        $countAbove = $totalMelebihiNilaiUkur;
        $countBelow = $totalPenilaian - $totalMelebihiNilaiUkur;

        return array_merge($this->getDefaultDetailResponse(), [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $countAbove,
                'below' => $countBelow
            ],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ]);
    }
}
