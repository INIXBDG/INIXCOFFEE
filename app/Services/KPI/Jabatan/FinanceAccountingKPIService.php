<?php

namespace App\Services\KPI\Jabatan;

use App\Traits\KPIDefaultResponseTrait;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FinanceAccountingKPIService
{
    use KPIDefaultResponseTrait;

    private function calculateOutstanding($item, $personId)
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

        $outstandings = Outstanding::whereBetween('created_at', [$start, $end])->get();

        if ($outstandings->isEmpty()) {
            return 0;
        }

        $totalData = $outstandings->count();

        $tepatTenggat = $outstandings->filter(function ($data) {
            return $data->status_pembayaran == 1
                && $data->tanggal_bayar
                && $data->due_date
                && Carbon::parse($data->tanggal_bayar)->lt(Carbon::parse($data->due_date));
        })->count();

        $presentase = ($tepatTenggat / $totalData) * 100;

        return round($presentase, 1);
    }

    private function calculateOutstandingDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->nilai_target) || !is_numeric($detail->detail_jangka)) {
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

        $outstandings = Outstanding::whereBetween('created_at', [$start, $end])->get();

        if ($outstandings->isEmpty()) {
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

        $totalData = $outstandings->count();

        $tepatTenggat = $outstandings->where('due_date', '<', 'tanggal_bayar')->where('status_pembayaran', '1');

        $above = $tepatTenggat->count();
        $below = $totalData - $above;

        $progress = $totalData > 0 ? ($above / $totalData) * 100 : 0;

        $progress = round($progress, 1);

        $gapRaw = $progress - $nilaiTarget;

        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        $monthlyTarget = $nilaiTarget / 12;
        $dailyTarget = $nilaiTarget / 365;

        foreach ($outstandings as $data) {
            $date = Carbon::parse($data->created_at);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $isTepat = $data->status_pembayaran == '1' && $data->tanggal_bayar && $data->due_date && Carbon::parse($data->tanggal_bayar)->lt(Carbon::parse($data->due_date)) ? 1 : 0;
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

    private function calculateInisiatifEfisiensiKeuangan($item, $personId)
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

        return round($progress);
    }

    private function calculateInisiatifEfisiensiKeuanganDetail($itemDetail)
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

        $item = $itemDetail;
        $personId = 0;

        if (is_null($detail) || is_null($detail->manual_value)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'dataManual' => [
                    'manual_document' => $detail->manual_document,
                ],
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $manualValue = (float) $detail->manual_value;

        if ($manualValue == null) {
            return 0;
        }

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

        $progress = 0;

        if (!is_null($detail->manual_value)) {
            $manualValue = (float) $detail->manual_value;

            if ($manualValue > 0) {
                $progress = $manualValue;
            }
        }

        $progress = round($progress);
        $gapRaw = $progress - $nilaiTarget;
        $gap = $nilaiTarget - $manualValue;

        return [
            'progress' => $progress,
            'gap' => $gap,
            'dataManual' => [
                'manual_document' => $detail->manual_document,
            ],
            'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
        ];
    }

    private function calculateMengurangiManualWorkDanError($item, $personId)
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

        return round($progress);
    }

    private function calculateMengurangiManualWorkDanErrorDetail($itemDetail)
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

        $item = $itemDetail;
        $personId = 0;

        if (is_null($detail) || is_null($detail->manual_value)) {
           return [
                'progress' => 0,
                'gap' => 0,
                'dataManual' => [
                    'manual_document' => $detail->manual_document,
                ],
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $manualValue = (float) $detail->manual_value;

        if ($manualValue == null) {
            return 0;
        }

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

        $progress = 0;

        if (!is_null($detail->manual_value)) {
            $manualValue = (float) $detail->manual_value;

            if ($manualValue > 0) {
                $progress = $manualValue;
            }
        }

        $progress = round($progress);
        $gapRaw = $progress - $nilaiTarget;
        $gap = $nilaiTarget - $manualValue;

        return [
            'progress' => $progress,
            'gap' => $gap,
            'dataManual' => [
                'manual_document' => $detail->manual_document,
            ],
            'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
        ];
    }

    private function calculateLaporanAnalisisKeuangan($item, $personId)
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

        $analisisData = AnalysisReport::where('year', $tahun)->count();

        $progress = 0;

        if ($analisisData == 0) {
            return 0;
        }

        if ($analisisData > 0) {
            $progress = $analisisData;
        }

        return round($progress);
    }

    private function calculateLaporanAnalisisKeuanganDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'analisa_data' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        $GetanalisisData = AnalysisReport::where('year', $tahun);

        $analisisData = $GetanalisisData->count();

        $above = $analisisData;
        $bellow = $nilaiTarget - $analisisData;

        $analisaData = $GetanalisisData->get();

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'analisa_data' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $progress = 0;

        if ($analisisData == 0) {
            return 0;
        }

        if ($analisisData > 0) {
            $progress = $analisisData;
        }

        $progress = round($progress);
        $gapRaw = $analisisData - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        return [
            'progress' => $progress,
            'gap' => $gap,
            'dataManual' => [
                'manual_document' => $detail->manual_document,
            ],
            'pie_chart' => ['above' => $above, 'below' => $bellow],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'analisa_data' => $analisaData,
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
        ];
    }

    private function calculatePencairanBiayaOperasional($item, $personId)
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

        $dataPengajuan = PengajuanBarang::with('tracking', 'detail')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $totalPengajuan = 0;
        $jumlahSesuai = 0;

        $completedStatuses = ['Selesai', 'Pencairan Sudah Selesai'];
        $excludedStatuses = [
            'Sedang Dikonfirmasi oleh Bagian Finance kepada Direksi',
            'Finance Menunggu Approve Direksi',
            'Membuat Permintaan Ke Direktur Utama',
            'Diajukan dan Sedang Ditinjau oleh Education Manager',
            'Diajukan dan Sedang Ditinjau oleh Koordinator IT Service Management',
            'Diajukan dan Sedang Ditinjau oleh SPV Sales',
            'Diajukan dan Sedang Ditinjau oleh General Manager'
        ];

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
                $ageInDays = now()->diffInDays($pengajuan->created_at, false);
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

    private function calculatePencairanBiayaOperasionalDetail($itemDetail, $personId)
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

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $dataPengajuan = PengajuanBarang::with('tracking', 'detail')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $totalPengajuan = 0;
        $jumlahSesuai = 0;

        $completedStatuses = ['Selesai', 'Pencairan Sudah Selesai'];
        $excludedStatuses = [
            'Sedang Dikonfirmasi oleh Bagian Finance kepada Direksi',
            'Finance Menunggu Approve Direksi',
            'Membuat Permintaan Ke Direktur Utama',
            'Diajukan dan Sedang Ditinjau oleh Education Manager',
            'Diajukan dan Sedang Ditinjau oleh Koordinator IT Service Management',
            'Diajukan dan Sedang Ditinjau oleh SPV Sales',
            'Diajukan dan Sedang Ditinjau oleh General Manager'
        ];

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
                $ageInDays = now()->diffInDays($pengajuan->created_at, false);
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

        $nilaiTarget = (float) $detail->nilai_target;
        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = round($jumlahSesuai, 1);
        $below = round($totalPengajuan - $jumlahSesuai, 1);

        $monthlyAverages = [];
        $monthlyProgressAverages = [];
        foreach ($monthlyData as $month => $data) {
            $monthlyAverages[$month] = $data['total'] > 0
                ? round(($data['scored'] / $data['total']) * 100, 1)
                : 0;
        }
        foreach ($monthlyProgress as $month => $data) {
            $monthlyProgressAverages[$month] = $data['total'] > 0
                ? round(($data['scored'] / $data['total']), 1)
                : 0;
        }

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $day => $data) {
                $dailyBreakdownPerMonth[$month][$day] = $data['total'] > 0
                    ? round(($data['scored'] / $data['total']) * 100, 1)
                    : 0;
            }
        }
        foreach ($dailyProgressPerMonth as $month => $days) {
            foreach ($days as $day => $data) {
                $dailyProgressPerMonth[$month][$day] = $data['total'] > 0
                    ? round(($data['scored'] / $data['total']), 1)
                    : 0;
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
                'below' => $below
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function calculatePenyelesaianTagihanPerusahaan($item, $personId)
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

        $foreignKey = 'id_tagihan_perusahaan';

        $latestIds = trackingTagihanPerusahaan::whereBetween(
                'tanggal_perkiraan_mulai',
                [$start, $end]
            )
            ->selectRaw("{$foreignKey}, MAX(id) as latest_id")
            ->groupBy($foreignKey)
            ->pluck('latest_id')
            ->filter()
            ->unique()
            ->values();

        if ($latestIds->isEmpty()) {
            return 0;
        }

        $totalTagihan = $latestIds->count();

        if ($totalTagihan <= 0) {
            return 0;
        }

        $tagihanSelesai = trackingTagihanPerusahaan::whereIn('id', $latestIds)
            ->where('status', 'Selesai')
            ->where('tracking', 'Selesai')
            ->count();

        $progress = ($tagihanSelesai / max($totalTagihan, 1)) * 100;

        return round($progress, 1);
    }

    private function calculatePenyelesaianTagihanPerusahaanDetail($itemDetail, $personId)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => [
                    'above' => 0,
                    'below' => 0
                ],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => [
                    'above' => 0,
                    'below' => 0
                ],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $latestIds = trackingTagihanPerusahaan::whereBetween('tanggal_perkiraan_mulai', [$start, $end])
            ->selectRaw('id_tagihan_perusahaan, MAX(id) as latest_id')
            ->groupBy('id_tagihan_perusahaan')
            ->pluck('latest_id');

        if ($latestIds->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => [
                    'above' => 0,
                    'below' => 0
                ],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $dataTagihan = trackingTagihanPerusahaan::whereIn('id', $latestIds)->get();

        $totalTagihan = $dataTagihan->count();

        $tagihanSelesai = $dataTagihan->filter(function ($row) {
            return $row->status === 'selesai' && $row->tracking === 'Selesai';
        })->count();

        $progress = $totalTagihan > 0
            ? round(($tagihanSelesai / $totalTagihan) * 100, 1)
            : 0;

        $nilaiTarget = (float) $detail->nilai_target;
        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $tagihanSelesai;
        $below = $totalTagihan - $tagihanSelesai;

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($dataTagihan as $tagihan) {
            if (!$tagihan->tanggal_perkiraan_mulai) {
                continue;
            }

            $date = Carbon::parse($tagihan->tanggal_perkiraan_mulai);

            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $isSelesai = (
                $tagihan->status === 'selesai' &&
                $tagihan->tracking === 'Selesai'
            ) ? 1 : 0;

            $pct = $isSelesai * 100;

            $monthlyData[$monthKey]['total'] =
                ($monthlyData[$monthKey]['total'] ?? 0) + 1;

            $monthlyData[$monthKey]['selesai'] =
                ($monthlyData[$monthKey]['selesai'] ?? 0) + $isSelesai;

            $monthlyProgress[$monthKey]['total'] =
                ($monthlyProgress[$monthKey]['total'] ?? 0) + 1;

            $monthlyProgress[$monthKey]['selesai'] =
                ($monthlyProgress[$monthKey]['selesai'] ?? 0) + $pct;

            $dailyBreakdownPerMonth[$monthKey][$dayKey]['total'] =
                ($dailyBreakdownPerMonth[$monthKey][$dayKey]['total'] ?? 0) + 1;

            $dailyBreakdownPerMonth[$monthKey][$dayKey]['selesai'] =
                ($dailyBreakdownPerMonth[$monthKey][$dayKey]['selesai'] ?? 0) + $isSelesai;

            $dailyProgressPerMonth[$monthKey][$dayKey]['total'] =
                ($dailyProgressPerMonth[$monthKey][$dayKey]['total'] ?? 0) + 1;

            $dailyProgressPerMonth[$monthKey][$dayKey]['selesai'] =
                ($dailyProgressPerMonth[$monthKey][$dayKey]['selesai'] ?? 0) + $pct;
        }

        $monthlyAverages = [];
        $monthlyProgressAverages = [];

        foreach ($monthlyData as $month => $data) {
            $total = (int) ($data['total'] ?? 0);
            $selesai = (int) ($data['selesai'] ?? 0);

            $monthlyAverages[$month] = $total > 0
                ? round(($selesai / $total) * 100, 1)
                : 0;
        }

        foreach ($monthlyProgress as $month => $data) {
            $total = (int) ($data['total'] ?? 0);
            $selesai = (float) ($data['selesai'] ?? 0);

            $monthlyProgressAverages[$month] = $total > 0
                ? round(($selesai / $total), 1)
                : 0;
        }

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $day => $data) {
                $total = (int) ($data['total'] ?? 0);
                $selesai = (int) ($data['selesai'] ?? 0);

                $dailyBreakdownPerMonth[$month][$day] = $total > 0
                    ? round(($selesai / $total) * 100, 1)
                    : 0;
            }
        }

        foreach ($dailyProgressPerMonth as $month => $days) {
            foreach ($days as $day => $data) {
                $total = (int) ($data['total'] ?? 0);
                $selesai = (float) ($data['selesai'] ?? 0);

                $dailyProgressPerMonth[$month][$day] = $total > 0
                    ? round(($selesai / $total), 1)
                    : 0;
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
                'below' => $below
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function calculateAkurasiPencatatanMasuk($item, $personId)
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
        $start = Carbon::create($tahun, 1, 1)->startOfDay();

        $end = ($tahun == now()->year)
            ? now()->endOfDay()
            : Carbon::create($tahun, 12, 31)->endOfDay();

        $data = outstanding::whereBetween('created_at', [$start, $end])->get();

        $totalTagihan = $data->count();
        $totalAkurat = 0;

        foreach ($data as $row) {

            $netSales = (float) ($row->net_sales ?? 0);
            $jumlahBayar = (float) ($row->jumlah_pembayaran ?? 0);

            $data = ApprovalPendapatan::whereBetween('tanggal_mulai', [$startOfYear, $endDate])->get();

            // Ambil data potongan (boleh null, array, atau json)
            $potongan = $row->jumlah_potongan;

            if (!empty($potongan)) {

                if (!is_array($potongan)) {
                    $potongan = json_decode($potongan, true);
                }

                if (is_array($potongan)) {
                    foreach ($potongan as $item) {
                        $totalPotongan += (float) ($item['jumlah'] ?? 0);
                    }
                }
            }

            // Akurat apabila:
            // 1. Net Sales = Jumlah Pembayaran
            // 2. Net Sales = Jumlah Pembayaran + Total Potongan
            if (
                $netSales == $jumlahBayar ||
                $netSales == ($jumlahBayar + $totalPotongan)
            ) {
                $totalAkurat++;
            }

            return optional($item->total_pemasukan_kotor) !== null;
        })->count();

        $progress = $total > 0 ? round(($sesuai / $total) * 100, 2) : 0;
        return round($progress, 1);
    }

    private function calculateAkurasiPencatatanMasukDetail($itemDetail)
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
        $nilaiTarget = (float) $detail->nilai_target;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return $emptyResponse;
        }

        // Year To Date
        $start = Carbon::create($tahun, 1, 1)->startOfDay();

        $end = ($tahun == now()->year)
            ? now()->endOfDay()
            : Carbon::create($tahun, 12, 31)->endOfDay();

        $data = Outstanding::whereBetween('created_at', [$start, $end])->get();

        $total = 0;
        $totalAkurat = 0;

        $monthlyTotal = [];
        $monthlyAccurate = [];

        $dailyTotal = [];
        $dailyAccurate = [];

        $dailyBreakdownPerMonth = [];

        foreach ($data as $row) {

            // Jika Net Sales kosong tidak ikut dihitung
            if (is_null($row->net_sales)) {
                continue;
            }

            $total++;

            $date = Carbon::parse($row->created_at);
            $monthKey = $date->format('Y-m');
            $dateKey = $date->format('Y-m-d');

            // Total data per bulan
            $monthlyTotal[$monthKey] =
                ($monthlyTotal[$monthKey] ?? 0) + 1;

            // Total data per hari
            $dailyTotal[$monthKey][$dateKey] =
                ($dailyTotal[$monthKey][$dateKey] ?? 0) + 1;

            $netSales = (float) ($row->net_sales ?? 0);
            $jumlahBayar = (float) ($row->jumlah_pembayaran ?? 0);

            $totalPotongan = 0;

            $potongan = $row->jumlah_potongan;

            if (!empty($potongan)) {

                if (!is_array($potongan)) {
                    $potongan = json_decode($potongan, true);
                }

                if (is_array($potongan)) {
                    foreach ($potongan as $item) {
                        $totalPotongan += (float) ($item['jumlah'] ?? 0);
                    }
                }
            }

            $isAkurat =
                $netSales == $jumlahBayar ||
                $netSales == ($jumlahBayar + $totalPotongan);

            if ($isAkurat) {

                $totalAkurat++;

                // Akurat per bulan
                $monthlyAccurate[$monthKey] =
                    ($monthlyAccurate[$monthKey] ?? 0) + 1;

                // Akurat per hari
                $dailyAccurate[$monthKey][$dateKey] =
                    ($dailyAccurate[$monthKey][$dateKey] ?? 0) + 1;

                // Breakdown harian
                $dailyBreakdownPerMonth[$monthKey][$dateKey] =
                    ($dailyBreakdownPerMonth[$monthKey][$dateKey] ?? 0) + 1;
            }
        }

        if ($total == 0) {
            return $emptyResponse;
        }

        // Progress utama
        $progress = round(($totalAkurat / $total) * 100, 1);

        // Gap
        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');
        if ($gap === '') {
            $gap = '0';
        }

        // Progress bulanan
        $monthlyProgress = [];

        foreach ($monthlyTotal as $month => $jumlah) {

            $accurate = $monthlyAccurate[$month] ?? 0;

            $monthlyProgress[$month] = $jumlah > 0
                ? round(($accurate / $jumlah) * 100, 1)
                : 0;
        }

        // Progress harian
        $dailyProgressPerMonth = [];

        foreach ($dailyTotal as $month => $days) {

            foreach ($days as $date => $jumlah) {

                $accurate = $dailyAccurate[$month][$date] ?? 0;

                $dailyProgressPerMonth[$month][$date] = $jumlah > 0
                    ? round(($accurate / $jumlah) * 100, 1)
                    : 0;
            }
        }

        ksort($monthlyAccurate);
        ksort($monthlyProgress);
        ksort($dailyBreakdownPerMonth);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $totalAkurat,
                'below' => max(0, $total - $totalAkurat),
            ],

            // Jumlah data akurat per bulan
            'monthly_data' => $monthlyAccurate,

            // Jumlah data akurat per hari
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,

            // Persentase akurasi per bulan
            'monthly_progress' => $monthlyProgress,

            // Persentase akurasi per hari
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }
}