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
        $totalPenilaian = 0;
        $totalMelebihiNilaiUkur = 0;

        $karyawanJabatan = karyawan::where('divisi', 'Sales & Marketing')
            ->where('status_aktif', '1')
            ->where('jabatan', '!=', 'Tim Digital')
            ->where('jabatan', '!=', 'GM')
            ->pluck('jabatan')
            ->map(fn($jabatan) => strtolower(trim($jabatan)))
            ->unique()
            ->toArray();

        $userQuery = User::whereHas('karyawan', function ($query) use ($personId, $karyawanJabatan) {
            $query->where('divisi', 'Sales & Marketing')
                ->whereIn('jabatan', $karyawanJabatan);

            if ($personId !== null) {
                $query->where('id', $personId);
            }
        });

        $salesUsernames = $userQuery->pluck('username')
            ->filter()
            ->map(fn($username) => strtolower(trim($username)))
            ->toArray();

        if (empty($salesUsernames)) {
            return 0;
        }

        try {
            // Uncomment API logic if needed
            // $apiUrl = env('MOODLE_API_URL');
            // $apiUsername = env('MOODLE_API_USERNAME');
            // $apiPassword = env('MOODLE_API_PASSWORD');

            // $response = Http::withBasicAuth($apiUsername, $apiPassword)
            //     ->timeout(15)
            //     ->get($apiUrl);

            // if (!$response->successful()) {
            //     return 0;
            // }
            // $moodleData = $response->json();

            // Mock Data if API is commented out
            $moodleData = [];
        } catch (\Exception $e) {
            return 0;
        }

        if (empty($moodleData) || !is_array($moodleData) || !isset($moodleData['data'])) {
            return 0;
        }

        $moodleDataValid = array_values($moodleData['data']);
        $moodleDataCount = count($moodleDataValid);

        for ($i = 0; $i < $moodleDataCount; $i++) {
            if (!isset($moodleDataValid[$i]) || !is_array($moodleDataValid[$i])) {
                continue;
            }

            $data = $moodleDataValid[$i];
            $moodleUsername = strtolower(trim($data['username'] ?? ''));

            if (in_array($moodleUsername, $salesUsernames)) {
                $totalPenilaian++;
                $score = (float) ($data['score'] ?? 0);

                if ($score > $nilaiUkur) {
                    $totalMelebihiNilaiUkur++;
                }
            }
        }

        if ($totalPenilaian === 0) {
            return 0;
        }

        $progress = ($totalMelebihiNilaiUkur / $totalPenilaian) * 100;

        return round($progress, 1);
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

        $karyawanJabatan = karyawan::where('divisi', 'Sales & Marketing')
            ->where('status_aktif', '1')
            ->whereNotIn('jabatan', ['Tim Digital', 'GM'])
            ->pluck('jabatan')
            ->map(fn($jabatan) => strtolower(trim($jabatan)))
            ->unique()
            ->toArray();

        $userQuery = User::whereHas('karyawan', function ($query) use ($personId, $karyawanJabatan) {
            $query->where('divisi', 'Sales & Marketing')
                ->whereIn('jabatan', $karyawanJabatan);

            if ($personId !== null) {
                $query->where('id', $personId);
            }
        });

        $salesUsernames = $userQuery->pluck('username')
            ->filter()
            ->map(fn($username) => strtolower(trim($username)))
            ->toArray();

        if (empty($salesUsernames)) {
            return $this->getDefaultDetailResponse();
        }

        try {
            // Uncomment API logic if needed
            // $apiUrl = env('MOODLE_API_URL');
            // $apiUsername = env('MOODLE_API_USERNAME');
            // $apiPassword = env('MOODLE_API_PASSWORD');

            // $response = Http::withBasicAuth($apiUsername, $apiPassword)
            //     ->timeout(15)
            //     ->get($apiUrl);
            // $moodleRaw = $response->successful() ? $response->json() : [];

            // Mock Data
            $moodleRaw = [];
        } catch (\Exception $e) {
            $moodleRaw = [];
        }

        if (empty($moodleRaw['data']) || !is_array($moodleRaw['data'])) {
            return $this->getDefaultDetailResponse();
        }

        $totalPenilaian = 0;
        $totalMelebihiNilaiUkur = 0;
        $dailyBreakdownPerMonth = [];
        $monthlyDataTemp = [];

        $moodleDataValid = array_values($moodleRaw['data']);
        $moodleDataCount = count($moodleDataValid);

        for ($i = 0; $i < $moodleDataCount; $i++) {
            $data = $moodleDataValid[$i];
            if (!isset($data['username'])) {
                continue;
            }

            $moodleUsername = strtolower(trim($data['username']));
            $dateString = $data['activity_submitted_at'] ?? $data['activity_created_at'] ?? null;

            if (in_array($moodleUsername, $salesUsernames) && $dateString) {
                $date = Carbon::parse($dateString);

                if ($date->year === $tahun) {
                    $totalPenilaian++;
                    $score = (float) ($data['score'] ?? 0);

                    $dateKey = $date->format('Y-m-d');
                    $monthKey = $date->format('Y-m');

                    if ($score > $nilaiUkur) {
                        $totalMelebihiNilaiUkur++;

                        if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                            $dailyBreakdownPerMonth[$monthKey] = [];
                        }
                        $dailyBreakdownPerMonth[$monthKey][$dateKey] = ($dailyBreakdownPerMonth[$monthKey][$dateKey] ?? 0) + 1;
                        $monthlyDataTemp[$monthKey] = ($monthlyDataTemp[$monthKey] ?? 0) + 1;
                    }
                }
            }
        }

        $progress = 0;
        if ($totalPenilaian > 0) {
            $progress = round(($totalMelebihiNilaiUkur / $totalPenilaian) * 100, 1);
        }

        $monthlyData = [];
        foreach ($monthlyDataTemp as $month => $total) {
            $monthlyData[$month] = round($total, 1);
        }

        ksort($monthlyData);
        ksort($dailyBreakdownPerMonth);

        $monthlyProgress = [];
        foreach ($monthlyData as $month => $value) {
            $monthlyProgress[$month] = $nilaiTarget > 0
                ? round(($value / $nilaiTarget) * 100, 1)
                : 0;
        }

        $dailyProgressPerMonth = [];
        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $date => $value) {
                $dailyProgressPerMonth[$month][$date] = $nilaiTarget > 0
                    ? round(($value / $nilaiTarget) * 100, 1)
                    : 0;
            }
        }

        $gap = 0;
        if ($progress <= $nilaiTarget) {
            $gapRaw = $progress - $nilaiTarget;
            $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');
        }

        $countAbove = $totalMelebihiNilaiUkur;
        $countBelow = $totalPenilaian - $totalMelebihiNilaiUkur;

        return [
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
        ];
    }
}
