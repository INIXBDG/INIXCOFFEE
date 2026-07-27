<?php

namespace App\Services\KPI\Jabatan;

use App\Models\ApprovalPendapatanSales;
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

        $query = RKM::where('status', '0')
            ->whereYear('tanggal_awal', $tahun);

        if ($personId !== null) {
            $karyawan = karyawan::find($personId);
            if ($karyawan && $karyawan->kode_karyawan) {
                $query->where('sales_key', $karyawan->kode_karyawan);
            } else {
                return 0;
            }
        }

        // PERBAIKAN: Agregasi langsung di level database (O(1) memory)
        $totalSales = (float) ($query->select(DB::raw('SUM(CAST(harga_jual AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total_sales'))->value('total_sales') ?? 0);

        $dataTarget = targetKPI::with('detailTargetKPI')
            ->whereHas('dataTarget', fn($q) => $q->where('asistant_route', 'pemasukan kotor'))
            ->first();

        $targetGM = ModelsTarget::where('quartal', 'All')->first() ?? null;
        $target = $dataTarget->detailTargetKPI->first()->nilai_target ?? $targetGM->target ?? 0;

        $progress = $target > 0 ? ($totalSales / $target) * 100 : 0;

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

        $query = RKM::where('status', '0')
            ->whereYear('tanggal_awal', $tahun);

        $kodeKaryawan = null;
        if ($personId !== null) {
            $karyawanData = karyawan::find($personId);
            $kodeKaryawan = $karyawanData ? $karyawanData->kode_karyawan : null;
            if ($kodeKaryawan) {
                $query->where('sales_key', $kodeKaryawan);
            }
        }

        $sales = $query->select('tanggal_awal', DB::raw('SUM(CAST(harga_jual AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total'))
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
            // PERBAIKAN: Simpan sebagai float murni, hindari number_format untuk operasi matematika
            $dailyBreakdownPerMonth[$monthKey][$dateKey] = $total;

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
            $monthlyData[$month] = round($total, 1);
        }

        ksort($monthlyData);
        ksort($dailyBreakdownPerMonth);

        $triwulanData = [];
        for ($i = 1; $i <= 4; $i++) {
            $triwulanData['Triwulan_' . $i] = round($triwulanDataTemp[$i], 1);
        }

        $dataTarget = targetKPI::with(['detailTargetKPI', 'dataTarget'])
            ->whereHas('dataTarget', function ($q) {
                $q->where('asistant_route', 'Pemasukan Kotor');
            })
            ->first();

        $targetGM = ModelsTarget::where('quartal', 'All')->first() ?? null;
        $targetGlobal = (float) ($dataTarget->detailTargetKPI->first()->nilai_target ?? $targetGM->target ?? 0);

        $progressGlobal = $targetGlobal > 0 ? ($totalSales / $targetGlobal) * 100 : 0;
        $gap = $progressGlobal - $nilaiTarget;

        $above = $totalSales >= $targetGlobal ? 1 : 0;
        $below = 1 - $above;

        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($monthlyData as $month => $value) {
            $monthlyProgress[$month] = $targetGlobal > 0 ? round(($value / $targetGlobal) * 100, 1) : 0;
        }

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $day => $value) {
                if (!isset($dailyProgressPerMonth[$month])) {
                    $dailyProgressPerMonth[$month] = [];
                }
                $dailyProgressPerMonth[$month][$day] = $targetGlobal > 0 ? round(($value / $targetGlobal) * 100, 1) : 0;
            }
        }

        $salesPerformance = null;

        if ($personId === null) {
            $allKaryawan = karyawan::where(function ($q) {
                $q->where('status_aktif', '1')
                  ->whereNot('jabatan', 'Outsource')
                  ->where('kode_karyawan', 'NOT LIKE', 'OL%')
                  ->whereNot('jabatan', 'Pilih Jabatan')
                  ->whereNotNull('nip')
                  ->whereNot('divisi', 'Direksi');
            })->where(function ($q) {
                $q->whereIn('jabatan', ['Sales', 'Sales Executive', 'Account Manager'])
                  ->orWhereNull('jabatan');
            })->get(['id', 'kode_karyawan', 'nama_lengkap', 'nama']);

            // PERBAIKAN: Eliminasi N+1 Query dengan agregasi database tunggal
            $revenues = RKM::where('status', '0')
                ->whereYear('tanggal_awal', $tahun)
                ->select('sales_key', DB::raw('SUM(CAST(harga_jual AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total'))
                ->groupBy('sales_key')
                ->get()
                ->pluck('total', 'sales_key');

            $karyawanIds = $allKaryawan->pluck('id');
            $detailPersons = detailPersonKPI::where('id_target', $itemDetail->id)
                ->whereIn('id_karyawan', $karyawanIds)
                ->get()
                ->keyBy('id_karyawan');

            $allSalesData = [];
            foreach ($allKaryawan as $karyawanItem) {
                $salesKey = $karyawanItem->kode_karyawan;
                if (!$salesKey) continue;

                $salesRevenue = (float) ($revenues[$salesKey] ?? 0);
                $detailPerson = $detailPersons[$karyawanItem->id] ?? null;
                $presentaseKemampuan = (float) ($detailPerson->presentase_kemampuan ?? 0);
                $percentage = $presentaseKemampuan > 0 ? ($salesRevenue / $presentaseKemampuan) * 100 : 0;

                $allSalesData[] = [
                    'kode_karyawan' => (string) $salesKey,
                    'nama' => (string) ($karyawanItem->nama_lengkap ?? $karyawanItem->nama ?? $salesKey),
                    'revenue' => round($salesRevenue, 1),
                    'id_detailPerson' => $detailPerson ? $detailPerson->id : null,
                    'presentase_kemampuan' => round($presentaseKemampuan, 1),
                    'percentage' => round($percentage, 1),
                    'status' => $salesRevenue >= $presentaseKemampuan ? 'achieved' : 'pending'
                ];
            }

            $salesPerformance = ['type' => 'all', 'data' => $allSalesData];
        } else {
            $karyawanData = karyawan::find($personId);
            $detailPerson = detailPersonKPI::where('id_target', $itemDetail->id)
                ->where('id_karyawan', $personId)
                ->first();

            $presentaseKemampuan = (float) ($detailPerson->presentase_kemampuan ?? 0);
            $percentage = $presentaseKemampuan > 0 ? ($totalSales / $presentaseKemampuan) * 100 : 0;
            $karyawanName = $karyawanData ? ($karyawanData->nama_lengkap ?? $karyawanData->nama ?? '') : '';

            $salesPerformance = [
                'type' => 'individual',
                'data' => [
                    'kode_karyawan' => (string) ($karyawanData->kode_karyawan ?? ''),
                    'nama' => (string) $karyawanName,
                    'revenue' => round($totalSales, 1),
                    'id_detailPerson' => $detailPerson ? $detailPerson->id : null,
                    'presentase_kemampuan' => round($presentaseKemampuan, 1),
                    'percentage' => round($percentage, 1),
                    'status' => $totalSales >= $presentaseKemampuan ? 'achieved' : 'pending'
                ]
            ];
        }

        return [
            'progress' => round($progressGlobal, 1),
            'gap' => round($gap, 1),
            'dataManual' => ['manual_document' => $detail->manual_document ?? null],
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
        
        // PERBAIKAN: Samakan rentang tanggal dengan fungsi Detail (akhir tahun target)
        $start = Carbon::create($tahun, 1, 1)->startOfDay();
        $end = Carbon::create($tahun, 12, 31)->endOfDay();

        $nilaiTarget = (float) $detail->nilai_target;

        // PERBAIKAN: Tambahkan select spesifik untuk menghindari Memory Exhaustion
        $query = ApprovalPendapatanSales::whereBetween('tanggal_mulai', [$start, $end])
            ->select('id', 'tanggal_mulai', 'total_pa', 'oleh_oleh', 'entertainment', 'total_cashback', 'total_uang_saku', 'total_akomodasi', 'biaya_transport', 'harga_net', 'pax');

        if ($personId !== null) {
            $karyawan = karyawan::find($personId);
            if ($karyawan && $karyawan->kode_karyawan) {
                $query->whereHas('rkm', function ($q) use ($karyawan) {
                    $q->where('sales_key', $karyawan->kode_karyawan);
                });
            } else {
                return 0;
            }
        }

        $data = $query->with(['pendapatan' => function($q) {
            $q->select('id', 'pax', 'harga_net');
        }])->get();

        if ($data->isEmpty()) return 0;

        $totalDataAkuisisi = 0;
        $dataAkuisisiTidakTerdata = 0;
        $achieve = 0;

        foreach ($data as $row) {
            $hargaNet = ($row->pendapatan?->pax ?? 0) * ($row->pendapatan?->harga_net ?? 0);
            $biayaPenjualan = (float) ($row->total_pa + $row->oleh_oleh + $row->entertainment + $row->total_cashback + $row->total_uang_saku + $row->total_akomodasi + $row->biaya_transport);
            $selisihBiayaUtama = (float) (($row->harga_net * $row->pax) - $hargaNet);
            $selisihBiaya = ($biayaPenjualan > $selisihBiayaUtama) ? ($biayaPenjualan - $selisihBiayaUtama) : 0;

            if ($selisihBiaya <= 0) {
                $dataAkuisisiTidakTerdata++;
            } else {
                $totalDataAkuisisi++;
                if ($hargaNet > 0) {
                    $persentase = ($selisihBiaya / $hargaNet) * 100;
                    if ($persentase <= 10) {
                        $achieve++;
                    }
                }
            }
        }

        $totalCount = $data->count();
        $progress = $totalCount > 0 ? round((($achieve + $dataAkuisisiTidakTerdata) / $totalCount) * 100, 2) : 0;

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

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return $this->getDefaultDetailResponse();
        }

        $start = Carbon::create($tahun, 1, 1)->startOfDay();
        $end = Carbon::create($tahun, 12, 31)->endOfDay();

        // PERBAIKAN: Tambahkan select spesifik untuk menghindari Memory Exhaustion
        $query = ApprovalPendapatanSales::whereBetween('tanggal_mulai', [$start, $end])
            ->select('id', 'tanggal_mulai', 'total_pa', 'oleh_oleh', 'entertainment', 'total_cashback', 'total_uang_saku', 'total_akomodasi', 'biaya_transport', 'harga_net', 'pax');

        if ($personId !== null) {
            $karyawan = karyawan::find($personId);
            if ($karyawan && $karyawan->kode_karyawan) {
                $query->whereHas('rkm', function ($q) use ($karyawan) {
                    $q->where('sales_key', $karyawan->kode_karyawan);
                });
            } else {
                return $this->getDefaultDetailResponse();
            }
        }

        $data = $query->with(['pendapatan' => function($q) {
            $q->select('id', 'pax', 'harga_net');
        }])->get();

        if ($data->isEmpty()) {
            return $this->getDefaultDetailResponse();
        }

        $totalDataAkuisisi = 0;
        $dataAkuisisiTidakTerdata = 0;
        $achieve = 0;

        $totalDataPerMonth = [];
        $achievedDataPerMonth = [];
        $totalDataPerDay = [];
        $achievedDataPerDay = [];

        foreach ($data as $row) {
            $date = Carbon::parse($row->tanggal_mulai);
            $dateKey = $date->format('Y-m-d');
            $monthKey = $date->format('Y-m');

            $totalDataPerMonth[$monthKey] = ($totalDataPerMonth[$monthKey] ?? 0) + 1;
            $totalDataPerDay[$monthKey][$dateKey] = ($totalDataPerDay[$monthKey][$dateKey] ?? 0) + 1;

            $achievedDataPerMonth[$monthKey] = $achievedDataPerMonth[$monthKey] ?? 0;
            $achievedDataPerDay[$monthKey][$dateKey] = $achievedDataPerDay[$monthKey][$dateKey] ?? 0;

            $hargaNet = ($row->pendapatan?->pax ?? 0) * ($row->pendapatan?->harga_net ?? 0);
            $biayaPenjualan = (float) ($row->total_pa + $row->oleh_oleh + $row->entertainment + $row->total_cashback + $row->total_uang_saku + $row->total_akomodasi + $row->biaya_transport);
            $selisihBiayaUtama = (float) (($row->harga_net * $row->pax) - $hargaNet);
            $selisihBiaya = ($biayaPenjualan > $selisihBiayaUtama) ? ($biayaPenjualan - $selisihBiayaUtama) : 0;

            $isRowAchieved = false;

            if ($selisihBiaya <= 0) {
                $dataAkuisisiTidakTerdata++;
                $isRowAchieved = true;
            } else {
                $totalDataAkuisisi++;
                if ($hargaNet > 0) {
                    $persentase = ($selisihBiaya / $hargaNet) * 100;
                    if ($persentase <= 10) {
                        $achieve++;
                        $isRowAchieved = true;
                    }
                }
            }

            if ($isRowAchieved) {
                $achievedDataPerMonth[$monthKey]++;
                $achievedDataPerDay[$monthKey][$dateKey]++;
            }
        }

        $totalCount = $data->count();
        $progress = $totalCount > 0 ? round((($achieve + $dataAkuisisiTidakTerdata) / $totalCount) * 100, 2) : 0;

        $monthlyData = [];
        $monthlyProgress = [];
        $dailyBreakdownPerMonth = [];
        $dailyProgressPerMonth = [];

        foreach ($totalDataPerMonth as $month => $total) {
            $capaianBulanan = $achievedDataPerMonth[$month];
            $monthlyData[$month] = $capaianBulanan;
            $monthlyProgress[$month] = $total > 0 ? round(($capaianBulanan / $total) * 100, 2) : 0;
        }

        foreach ($totalDataPerDay as $month => $days) {
            foreach ($days as $date => $total) {
                $capaianHarian = $achievedDataPerDay[$month][$date];
                $dailyBreakdownPerMonth[$month][$date] = $capaianHarian;
                $dailyProgressPerMonth[$month][$date] = $total > 0 ? round(($capaianHarian / $total) * 100, 2) : 0;
            }
        }

        $gapRaw = ($progress > $nilaiTarget) ? 0 : ($progress - $nilaiTarget);
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        return array_merge($this->getDefaultDetailResponse(), [
            'progress' => round($progress, 1),
            'gap' => $gap,
            'pie_chart' => [
                'above' => $progress >= $nilaiTarget ? 1 : 0,
                'below' => $progress < $nilaiTarget ? 1 : 0
            ],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ]);
    }

    public function calculatePeningkatanKemampuanKompetensiSales($item, $personId)
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

        $nilaiUkur = 90;
        $jabatanSales = ['Sales', 'SPV Sales', 'Adm Sales'];

        $allowedUsernames = User::whereIn('jabatan', $jabatanSales)
            ->pluck('username')
            ->filter()
            ->map(fn($username) => strtolower(trim($username)))
            ->toArray();

        $response = Http::get('https://coffee.inixindobdg.co.id/api/moodle-grades-sharingknowledge');
        if (!$response->successful()) {
            return 0;
        }

        $dataKnowledge = json_decode($response->body(), true);
        if (json_last_error() !== JSON_ERROR_NONE || !isset($dataKnowledge['data']['data'])) {
            return 0;
        }

        $collection = collect($dataKnowledge['data']['data'])->map(function ($row) {
            if (isset($row['username'])) {
                $row['username'] = strtolower(trim($row['username']));
            }
            return $row;
        });

        // PERBAIKAN: Filter tahun agar sinkron dengan fungsi Detail
        $collection = $collection->filter(function ($row) use ($tahun) {
            $dateString = $row['activity_submitted_at'] ?? $row['activity_created_at'] ?? null;
            if (!$dateString) return false;
            try {
                return Carbon::parse($dateString)->year == $tahun;
            } catch (\Exception $e) {
                return false;
            }
        });

        if ($personId !== null) {
            $userLogin = User::find($personId);
            if (!$userLogin || empty($userLogin->username)) {
                return 0;
            }

            $loginUsername = strtolower(trim($userLogin->username));
            if (!in_array($loginUsername, $allowedUsernames)) {
                return 0;
            }

            $filteredData = $collection->where('username', $loginUsername);
        } else {
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

        $jabatanSales = ['Sales', 'SPV Sales', 'Adm Sales'];
        $allowedUsernames = User::whereIn('jabatan', $jabatanSales)
            ->pluck('username')
            ->filter()
            ->map(fn($username) => strtolower(trim($username)))
            ->toArray();

        if (empty($allowedUsernames)) {
            return $this->getDefaultDetailResponse();
        }

        $loginUsername = null;
        if ($personId !== null) {
            $userLogin = User::find($personId);
            if (!$userLogin || empty($userLogin->username)) {
                return $this->getDefaultDetailResponse();
            }
            $loginUsername = strtolower(trim($userLogin->username));
            if (!in_array($loginUsername, $allowedUsernames)) {
                return $this->getDefaultDetailResponse();
            }
        }

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

        $totalPenilaian = 0;
        $totalMelebihiNilaiUkur = 0;

        $monthlyTotalPenilaian = [];
        $monthlyMelebihiNilaiUkur = [];
        $dailyTotalPenilaian = [];
        $dailyMelebihiNilaiUkur = [];

        foreach ($moodleData as $data) {
            if (empty($data['username'])) {
                continue;
            }

            $usernameData = strtolower(trim($data['username']));
            $dateString = $data['activity_submitted_at'] ?? $data['activity_created_at'] ?? null;

            $isValidUser = false;
            if ($personId !== null) {
                if ($usernameData === $loginUsername) {
                    $isValidUser = true;
                }
            } else {
                if (in_array($usernameData, $allowedUsernames)) {
                    $isValidUser = true;
                }
            }

            if ($isValidUser && $dateString) {
                try {
                    $date = Carbon::parse($dateString);
                    if ($date->year === $tahun) {
                        $totalPenilaian++;
                        $score = (float) ($data['score'] ?? 0);

                        $dateKey = $date->format('Y-m-d');
                        $monthKey = $date->format('Y-m');

                        $monthlyTotalPenilaian[$monthKey] = ($monthlyTotalPenilaian[$monthKey] ?? 0) + 1;
                        if (!isset($dailyTotalPenilaian[$monthKey][$dateKey])) {
                            $dailyTotalPenilaian[$monthKey][$dateKey] = 0;
                        }
                        $dailyTotalPenilaian[$monthKey][$dateKey]++;

                        if ($score > $nilaiUkur) {
                            $totalMelebihiNilaiUkur++;
                            $monthlyMelebihiNilaiUkur[$monthKey] = ($monthlyMelebihiNilaiUkur[$monthKey] ?? 0) + 1;
                            if (!isset($dailyMelebihiNilaiUkur[$monthKey][$dateKey])) {
                                $dailyMelebihiNilaiUkur[$monthKey][$dateKey] = 0;
                            }
                            $dailyMelebihiNilaiUkur[$monthKey][$dateKey]++;
                        }
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        if ($totalPenilaian === 0) {
            return $this->getDefaultDetailResponse();
        }

        $progress = round(($totalMelebihiNilaiUkur / $totalPenilaian) * 100, 2);

        $gap = 0;
        if ($progress <= $nilaiTarget) {
            $gapRaw = $progress - $nilaiTarget;
            $gap = rtrim(rtrim(sprintf('%.2f', $gapRaw), '0'), '.');
        }

        $monthlyData = [];
        $monthlyProgress = [];
        $dailyBreakdownPerMonth = [];
        $dailyProgressPerMonth = [];

        // PERBAIKAN: Perhitungan progres bulanan yang benar (persentase dari total penilaian bulan itu)
        foreach ($monthlyTotalPenilaian as $month => $total) {
            $melebihi = $monthlyMelebihiNilaiUkur[$month] ?? 0;
            $monthlyData[$month] = $melebihi;
            $monthlyProgress[$month] = $total > 0 ? round(($melebihi / $total) * 100, 2) : 0;
        }

        foreach ($dailyTotalPenilaian as $month => $days) {
            foreach ($days as $date => $total) {
                $melebihi = $dailyMelebihiNilaiUkur[$month][$date] ?? 0;
                $dailyBreakdownPerMonth[$month][$date] = $melebihi;
                $dailyProgressPerMonth[$month][$date] = $total > 0 ? round(($melebihi / $total) * 100, 2) : 0;
            }
            ksort($dailyBreakdownPerMonth[$month]);
            ksort($dailyProgressPerMonth[$month]);
        }

        ksort($monthlyData);
        ksort($monthlyProgress);

        return array_merge($this->getDefaultDetailResponse(), [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $totalMelebihiNilaiUkur,
                'below' => $totalPenilaian - $totalMelebihiNilaiUkur
            ],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ]);
    }
}