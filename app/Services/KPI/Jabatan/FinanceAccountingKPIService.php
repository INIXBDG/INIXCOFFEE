<?php

namespace App\Services\KPI\Jabatan;

use App\Models\outstanding;
use App\Models\PengajuanBarang;
use App\Models\AnalysisReport;
use App\Models\ApprovalPendapatan;
use App\Models\trackingTagihanPerusahaan;
use App\Traits\KPIDefaultResponseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinanceAccountingKPIService
{
    use KPIDefaultResponseTrait;

    private function isTepatTenggat($data)
    {
        if ($data->status_pembayaran != 1 || empty($data->tanggal_bayar) || empty($data->due_date)) {
            return false;
        }
        return \Carbon\Carbon::parse($data->tanggal_bayar)->lte(\Carbon\Carbon::parse($data->due_date));
    }

    public function calculateOutstanding($item, $personId)
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

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return 0;
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $outstandings = outstanding::whereBetween('created_at', [$start, $end])->get();

        if ($outstandings->isEmpty()) {
            return 0;
        }

        $totalData = $outstandings->count();
        $tepatTenggat = $outstandings->filter(fn($d) => $this->isTepatTenggat($d))->count();
        $presentase = ($tepatTenggat / $totalData) * 100;

        return round($presentase, 1);
    }

    public function calculateOutstandingDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->nilai_target) || !is_numeric($detail->detail_jangka)) {
            return $this->getDefaultDetailResponse();
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $this->getDefaultDetailResponse();
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $outstandings = outstanding::whereBetween('created_at', [$start, $end])->get();

        if ($outstandings->isEmpty()) {
            return $this->getDefaultDetailResponse();
        }

        $totalData = $outstandings->count();
        $above = $outstandings->filter(fn($d) => $this->isTepatTenggat($d))->count();
        $below = $totalData - $above;

        $progress = $totalData > 0 ? ($above / $totalData) * 100 : 0;
        $progress = round($progress, 1);

        if ($progress < $nilaiTarget) {
            $gapRaw = abs($progress - $nilaiTarget);
            $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');
            if ($gap === '') {
                $gap = '0';
            }
        } else {
            $gap = 0;
        }

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($outstandings as $data) {
            $date = Carbon::parse($data->created_at);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $isTepat = $this->isTepatTenggat($data) ? 1 : 0;
            $pct = $isTepat * 100;

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [];
                $monthlyProgress[$monthKey] = [];
            }
            $monthlyData[$monthKey][] = $pct;
            $monthlyProgress[$monthKey][] = $pct;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
                $dailyProgressPerMonth[$monthKey] = [];
            }
            if (!isset($dailyBreakdownPerMonth[$monthKey][$dayKey])) {
                $dailyBreakdownPerMonth[$monthKey][$dayKey] = [];
                $dailyProgressPerMonth[$monthKey][$dayKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dayKey][] = $pct;
            $dailyProgressPerMonth[$monthKey][$dayKey][] = $pct;
        }

        $monthlyAverages = [];
        $monthlyProgressAverages = [];
        foreach ($monthlyData as $month => $values) {
            $monthlyAverages[$month] = round(array_sum($values) / count($values), 1);
        }
        foreach ($monthlyProgress as $month => $values) {
            $monthlyProgressAverages[$month] = round(array_sum($values) / count($values), 1);
        }

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $day => $values) {
                $dailyBreakdownPerMonth[$month][$day] = round(array_sum($values) / count($values), 1);
            }
        }
        foreach ($dailyProgressPerMonth as $month => $days) {
            foreach ($days as $day => $values) {
                $dailyProgressPerMonth[$month][$day] = round(array_sum($values) / count($values), 1);
            }
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAverages);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $above,
                'below' => $below,
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    public function calculateInisiatifEfisiensiKeuangan($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $progress = 0;
        $manualValue = (float) $detail->manual_value;
        $targetValue = (float) $detail->nilai_target;

        if ($targetValue == null) {
            return 0;
        }

        if ($manualValue > 0) {
            $progress = $manualValue;
        }

        return round($progress, 1);
    }

    public function calculateInisiatifEfisiensiKeuanganDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $this->getDefaultDetailResponse();
        }

        if (is_null($detail) || is_null($detail->manual_value)) {
            return array_merge($this->getDefaultDetailResponse(), [
                'dataManual' => ['manual_document' => $detail->manual_document ?? null],
            ]);
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $manualValue = (float) $detail->manual_value;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return array_merge($this->getDefaultDetailResponse(), [
                'dataManual' => ['manual_document' => $detail->manual_document ?? null],
            ]);
        }

        $progress = 0;
        if ($manualValue > 0) {
            $progress = $manualValue;
        }
        $progress = round($progress, 1);

        if ($progress < $nilaiTarget) {
            $gapRaw = abs($progress - $nilaiTarget);
            $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');
            if ($gap === '') {
                $gap = '0';
            }
        } else {
            $gap = 0;
        }

        return array_merge($this->getDefaultDetailResponse(), [
            'progress' => $progress,
            'gap' => $gap,
            'dataManual' => [
                'manual_document' => $detail->manual_document,
            ],
        ]);
    }

    public function calculateMengurangiManualWorkDanError($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $progress = 0;
        $manualValue = (float) $detail->manual_value;
        $targetValue = (float) $detail->nilai_target;

        if ($targetValue == null) {
            return 0;
        }

        if ($manualValue > 0) {
            $progress = $manualValue;
        }

        return round($progress, 1);
    }

    public function calculateMengurangiManualWorkDanErrorDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $this->getDefaultDetailResponse();
        }

        if (is_null($detail) || is_null($detail->manual_value)) {
            return array_merge($this->getDefaultDetailResponse(), [
                'dataManual' => ['manual_document' => $detail->manual_document ?? null],
            ]);
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $manualValue = (float) $detail->manual_value;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return array_merge($this->getDefaultDetailResponse(), [
                'dataManual' => ['manual_document' => $detail->manual_document ?? null],
            ]);
        }

        $progress = 0;
        if ($manualValue > 0) {
            $progress = $manualValue;
        }
        $progress = round($progress, 1);

        if ($progress < $nilaiTarget) {
            $gapRaw = abs($progress - $nilaiTarget);
            $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');
            if ($gap === '') {
                $gap = '0';
            }
        } else {
            $gap = 0;
        }

        return array_merge($this->getDefaultDetailResponse(), [
            'progress' => $progress,
            'gap' => $gap,
            'dataManual' => [
                'manual_document' => $detail->manual_document,
            ],
        ]);
    }

    public function calculateLaporanAnalisisKeuangan($item, $personId)
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

        $analisisData = AnalysisReport::where('year', $tahun)->count();
        $progress = 0;

        if ($analisisData > 0) {
            $progress = $analisisData;
        }

        return round($progress, 1);
    }

    public function calculateLaporanAnalisisKeuanganDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        $emptyResponse = array_merge($this->getDefaultDetailResponse(), [
            'analisa_data' => [],
        ]);

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $emptyResponse;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $emptyResponse;
        }

        $GetanalisisData = AnalysisReport::where('year', $tahun);
        $analisisData = $GetanalisisData->count();
        $analisaData = $GetanalisisData->get();

        $above = $analisisData;
        $below = max(0, $nilaiTarget - $analisisData);

        $progress = 0;
        if ($analisisData > 0) {
            $progress = $analisisData;
        }
        $progress = round($progress, 1);

        if ($progress < $nilaiTarget) {
            $gapRaw = abs($progress - $nilaiTarget);
            $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');
            if ($gap === '') {
                $gap = '0';
            }
        } else {
            $gap = 0;
        }

        return array_merge($this->getDefaultDetailResponse(), [
            'progress' => $progress,
            'gap' => $gap,
            'dataManual' => [
                'manual_document' => $detail->manual_document ?? null,
            ],
            'pie_chart' => ['above' => $above, 'below' => $below],
            'analisa_data' => $analisaData,
        ]);
    }

    public function calculatePencairanBiayaOperasional($item, $personId)
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

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $dataPengajuan = PengajuanBarang::with('tracking', 'detail')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $totalPengajuan = 0;
        $jumlahSesuai = 0;

        $completedStatuses = ['Selesai', 'Pencairan Sudah Selesai'];
        $excludedStatuses = ['Sedang Dikonfirmasi oleh Bagian Finance kepada Direksi', 'Finance Menunggu Approve Direksi', 'Membuat Permintaan Ke Direktur Utama', 'Diajukan dan Sedang Ditinjau oleh Education Manager', 'Diajukan dan Sedang Ditinjau oleh Koordinator IT Service Management', 'Diajukan dan Sedang Ditinjau oleh SPV Sales', 'Diajukan dan Sedang Ditinjau oleh General Manager'];

        foreach ($dataPengajuan as $pengajuan) {
            $trackingStatus = optional($pengajuan->tracking)->tracking;

            if (in_array($trackingStatus, $excludedStatuses)) {
                continue;
            }

            $totalPengajuan++;

            $isCompleted = in_array($trackingStatus, $completedStatuses);
            $score = 0;

            if ($isCompleted) {
                $tanggalTerimaFinance = Carbon::parse($pengajuan->tanggal_terima_finance ?? null);
                $tanggalSelesai = Carbon::parse($pengajuan->tanggal_selesai ?? null);

                if ($tanggalTerimaFinance && $tanggalSelesai && $tanggalTerimaFinance->addDays(7)->isBefore($tanggalSelesai)) {
                    $score = 0;
                } else {
                    $score = 1;
                }
            } else {
                $ageInDays = Carbon::parse($pengajuan->created_at)->diffInDays(now());
                if ($ageInDays <= 2) {
                    $score = 1;
                } elseif ($ageInDays <= 21) {
                    $decayDays = $ageInDays - 2;
                    $score = exp(-0.05 * $decayDays);
                    $score = max(0, min(1, $score));
                } else {
                    $score = 0;
                }
            }

            $jumlahSesuai += $score;
        }

        if ($totalPengajuan == 0) {
            return 0;
        }

        $progress = ($jumlahSesuai / $totalPengajuan) * 100;

        return round($progress, 1);
    }

    public function calculatePencairanBiayaOperasionalDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $this->getDefaultDetailResponse();
        }

        $tahun = (int) $detail->detail_jangka;
        $nilaiTarget = (float) $detail->nilai_target;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return $this->getDefaultDetailResponse();
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $dataPengajuan = PengajuanBarang::with('tracking', 'detail')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $totalPengajuan = 0;
        $jumlahSesuai = 0;

        $completedStatuses = ['Selesai', 'Pencairan Sudah Selesai'];
        $excludedStatuses = ['Sedang Dikonfirmasi oleh Bagian Finance kepada Direksi', 'Finance Menunggu Approve Direksi', 'Membuat Permintaan Ke Direktur Utama', 'Diajukan dan Sedang Ditinjau oleh Education Manager', 'Diajukan dan Sedang Ditinjau oleh Koordinator IT Service Management', 'Diajukan dan Sedang Ditinjau oleh SPV Sales', 'Diajukan dan Sedang Ditinjau oleh General Manager'];

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($dataPengajuan as $pengajuan) {
            $trackingStatus = optional($pengajuan->tracking)->tracking;

            if (in_array($trackingStatus, $excludedStatuses)) {
                continue;
            }

            $totalPengajuan++;

            $isCompleted = in_array($trackingStatus, $completedStatuses);
            $score = 0;

            if ($isCompleted) {
                $tanggalTerimaFinance = Carbon::parse($pengajuan->tanggal_terima_finance ?? null);
                $tanggalSelesai = Carbon::parse($pengajuan->tanggal_selesai ?? null);

                if ($tanggalTerimaFinance && $tanggalSelesai && $tanggalTerimaFinance->addDays(7)->isBefore($tanggalSelesai)) {
                    $score = 0;
                } else {
                    $score = 1;
                }
            } else {
                $ageInDays = Carbon::parse($pengajuan->created_at)->diffInDays(now());
                if ($ageInDays <= 2) {
                    $score = 1;
                } elseif ($ageInDays <= 21) {
                    $decayDays = $ageInDays - 2;
                    $score = exp(-0.05 * $decayDays);
                    $score = max(0, min(1, $score));
                } else {
                    $score = 0;
                }
            }

            $jumlahSesuai += $score;

            $date = Carbon::parse($pengajuan->created_at);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = ['total' => 0, 'scored' => 0];
                $monthlyProgress[$monthKey] = ['total' => 0, 'scored' => 0];
            }
            $monthlyData[$monthKey]['total']++;
            $monthlyData[$monthKey]['scored'] += $score;
            $monthlyProgress[$monthKey]['total']++;
            $monthlyProgress[$monthKey]['scored'] += $score * 100;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
                $dailyProgressPerMonth[$monthKey] = [];
            }
            if (!isset($dailyBreakdownPerMonth[$monthKey][$dayKey])) {
                $dailyBreakdownPerMonth[$monthKey][$dayKey] = ['total' => 0, 'scored' => 0];
                $dailyProgressPerMonth[$monthKey][$dayKey] = ['total' => 0, 'scored' => 0];
            }
            $dailyBreakdownPerMonth[$monthKey][$dayKey]['total']++;
            $dailyBreakdownPerMonth[$monthKey][$dayKey]['scored'] += $score;
            $dailyProgressPerMonth[$monthKey][$dayKey]['total']++;
            $dailyProgressPerMonth[$monthKey][$dayKey]['scored'] += $score * 100;
        }

        $progress = $totalPengajuan > 0 ? round(($jumlahSesuai / $totalPengajuan) * 100, 1) : 0;

        if ($progress < $nilaiTarget) {
            $gapRaw = abs($progress - $nilaiTarget);
            $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');
            if ($gap === '') {
                $gap = '0';
            }
        } else {
            $gap = 0;
        }

        $above = round($jumlahSesuai, 1);
        $below = round(max(0, $totalPengajuan - $jumlahSesuai), 1);

        $monthlyAverages = [];
        $monthlyProgressAverages = [];
        foreach ($monthlyData as $month => $data) {
            $monthlyAverages[$month] = $data['total'] > 0 ? round(($data['scored'] / $data['total']) * 100, 1) : 0;
        }
        foreach ($monthlyProgress as $month => $data) {
            $monthlyProgressAverages[$month] = $data['total'] > 0 ? round($data['scored'] / $data['total'], 1) : 0;
        }

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $day => $data) {
                $dailyBreakdownPerMonth[$month][$day] = $data['total'] > 0 ? round(($data['scored'] / $data['total']) * 100, 1) : 0;
            }
        }
        foreach ($dailyProgressPerMonth as $month => $days) {
            foreach ($days as $day => $data) {
                $dailyProgressPerMonth[$month][$day] = $data['total'] > 0 ? round($data['scored'] / $data['total'], 1) : 0;
            }
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAverages);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $above,
                'below' => $below,
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    public function calculatePenyelesaianTagihanPerusahaan($item, $personId = null)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return 0;
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $latestTagihan = trackingTagihanPerusahaan::whereBetween('tanggal_perkiraan_mulai', [$start, $end])
            ->orderBy('id', 'desc')
            ->get()
            ->unique('id_tagihan_perusahaan');

        $totalTagihan = $latestTagihan->count();
        if ($totalTagihan <= 0) {
            return 0;
        }

        $tagihanSelesai = $latestTagihan
            ->filter(function ($row) {
                return strtolower(trim($row->status)) === 'selesai' && strtolower(trim($row->tracking)) === 'selesai';
            })
            ->count();

        $progress = ($tagihanSelesai / $totalTagihan) * 100;

        return round($progress, 1);
    }

    public function calculatePenyelesaianTagihanPerusahaanDetail($itemDetail, $personId = null)
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

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $emptyResponse;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return $emptyResponse;
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $latestTagihan = trackingTagihanPerusahaan::whereBetween('tanggal_perkiraan_mulai', [$start, $end])
            ->orderBy('id', 'desc')
            ->get()
            ->unique('id_tagihan_perusahaan');

        $totalTagihan = $latestTagihan->count();

        if ($totalTagihan <= 0) {
            return $emptyResponse;
        }

        $tagihanSelesai = $latestTagihan
            ->filter(function ($row) {
                return strtolower(trim($row->status)) === 'selesai';
            })
            ->count();

        $progress = round(($tagihanSelesai / $totalTagihan) * 100, 1);
        $nilaiTarget = (float) $detail->nilai_target;

        if ($progress < $nilaiTarget) {
            $gapRaw = abs($progress - $nilaiTarget);
            $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');
            if ($gap === '') {
                $gap = '0';
            }
        } else {
            $gap = 0;
        }

        $above = $tagihanSelesai;
        $below = max(0, $totalTagihan - $tagihanSelesai);

        $monthlyDataRaw = [];
        $dailyDataRaw = [];

        foreach ($latestTagihan as $tagihan) {
            if (!$tagihan->tanggal_perkiraan_mulai) {
                continue;
            }

            $date = Carbon::parse($tagihan->tanggal_perkiraan_mulai);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $isSelesai = strtolower(trim($tagihan->status)) === 'selesai' && strtolower(trim($tagihan->tracking)) === 'selesai' ? 1 : 0;

            $monthlyDataRaw[$monthKey]['total'] = ($monthlyDataRaw[$monthKey]['total'] ?? 0) + 1;
            $monthlyDataRaw[$monthKey]['selesai'] = ($monthlyDataRaw[$monthKey]['selesai'] ?? 0) + $isSelesai;

            $dailyDataRaw[$monthKey][$dayKey]['total'] = ($dailyDataRaw[$monthKey][$dayKey]['total'] ?? 0) + 1;
            $dailyDataRaw[$monthKey][$dayKey]['selesai'] = ($dailyDataRaw[$monthKey][$dayKey]['selesai'] ?? 0) + $isSelesai;
        }

        $monthlyAverages = [];
        $monthlyProgress = [];
        $dailyBreakdownPerMonth = [];
        $dailyProgressPerMonth = [];

        foreach ($monthlyDataRaw as $month => $data) {
            $total = $data['total'];
            $selesai = $data['selesai'];
            $rate = $total > 0 ? round(($selesai / $total) * 100, 1) : 0;

            $monthlyAverages[$month] = $rate;
            $monthlyProgress[$month] = $rate;
        }

        foreach ($dailyDataRaw as $month => $days) {
            foreach ($days as $day => $data) {
                $total = $data['total'];
                $selesai = $data['selesai'];
                $rate = $total > 0 ? round(($selesai / $total) * 100, 1) : 0;

                $dailyBreakdownPerMonth[$month][$day] = $rate;
                $dailyProgressPerMonth[$month][$day] = $rate;
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

    public function calculateAkurasiPencatatanMasuk($item, $personId)
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
        $endDate = Carbon::create($tahun, Carbon::now()->month, Carbon::now()->daysInMonth)->endOfDay();
        $data = ApprovalPendapatan::whereBetween('tanggal_mulai', [$startOfYear, $endDate])->get();

        $total = $data->count();

        $sesuai = $data
            ->filter(function ($row) {
                $pembayaran = (float) $row->jumlah_pembayaran;
                $ppn = (float) $row->PPN;
                $pph = (float) $row->PPH;
                $kotor = (float) $row->total_pemasukan_kotor;

                if ($pembayaran === 0.0) {
                    $totalDenganPajak = $pembayaran + $ppn + $pph;
                    return $totalDenganPajak === $kotor || $pembayaran === $kotor;
                }

                return !is_null($row->total_pemasukan_kotor);
            })
            ->count();

        $progress = $total > 0 ? (($sesuai / $total) * 100) : 0;
        return round($progress, 1);
    }

    public function calculateAkurasiPencatatanMasukDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (is_null($detail) || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $this->getDefaultDetailResponse();
        }

        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return $this->getDefaultDetailResponse();
        }

        $startOfYear = Carbon::create($tahun, 1, 1)->startOfDay();
        $endDate = Carbon::create($tahun, Carbon::now()->month, Carbon::now()->daysInMonth)->endOfDay();

        $data = ApprovalPendapatan::whereBetween('tanggal_mulai', [$startOfYear, $endDate])->get();

        $total = $data->count();
        $totalAkurat = 0;

        $dailyResult = [];
        $accurateCount = 0;
        $notAccurateCount = 0;

        foreach ($data as $row) {
            $pembayaran = (float) $row->jumlah_pembayaran;
            $ppn = (float) $row->PPN;
            $pph = (float) $row->PPH;
            $kotor = (float) $row->total_pemasukan_kotor;

            $isAkurat = false;

            if ($pembayaran === 0.0) {
                $totalDenganPajak = $pembayaran + $ppn + $pph;
                $isAkurat = $totalDenganPajak === $kotor || $pembayaran === $kotor;
            } else {
                $isAkurat = !is_null($row->total_pemasukan_kotor);
            }

            $tanggal = Carbon::parse($row->tanggal_mulai);
            $tanggalKey = $tanggal->format('Y-m-d');

            $dailyResult[$tanggalKey][] = $isAkurat ? 1 : 0;

            if ($isAkurat) {
                $totalAkurat++;
                $accurateCount++;
            } else {
                $notAccurateCount++;
            }
        }

        if ($total == 0) {
            return $this->getDefaultDetailResponse();
        }

        $progress = ($totalAkurat / $total) * 100;
        $progress = round($progress, 1);
        $nilaiTarget = (float) $detail->nilai_target;

        if ($progress < $nilaiTarget) {
            $gapRaw = abs($progress - $nilaiTarget);
            $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');
            if ($gap === '') {
                $gap = '0';
            }
        } else {
            $gap = 0;
        }

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($dailyResult as $dateStr => $values) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');

            $avg = (array_sum($values) / count($values)) * 100;
            $avg = round($avg, 1);

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
            $dailyBreakdownPerMonth[$monthKey][$dateStr] = $avg;
            $dailyProgressPerMonth[$monthKey][$dateStr] = $avg;
        }

        $monthlyAverages = [];
        $monthlyProgressAverages = [];
        foreach ($monthlyData as $month => $values) {
            $monthlyAverages[$month] = round(array_sum($values) / count($values), 1);
        }
        foreach ($monthlyProgress as $month => $values) {
            $monthlyProgressAverages[$month] = round(array_sum($values) / count($values), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAverages);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $accurateCount,
                'below' => $notAccurateCount,
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }
}
