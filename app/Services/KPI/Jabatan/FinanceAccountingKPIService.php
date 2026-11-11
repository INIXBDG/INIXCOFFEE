<?php

namespace App\Services\KPI\Jabatan;

use App\Models\outstanding;
use App\Models\PengajuanBarang;
use App\Models\AnalysisReport;
use App\Models\AnalysisFinanceInitiative;
use App\Models\ApprovalPendapatan;
use App\Models\trackingTagihanPerusahaan;
use App\Traits\KPIDefaultResponseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinanceAccountingKPIService
{
    use KPIDefaultResponseTrait;

    private const SLA_PENCAIRAN_HARI = 7;
    private const SLA_PENCAIRAN_MULAI = '2026-06-01';

    private function getStatus($data)
    {
        if ($data->status_pembayaran == 1 && !empty($data->tanggal_bayar) && !empty($data->due_date)) {
            return Carbon::parse($data->tanggal_bayar)->lte(Carbon::parse($data->due_date))
                ? 'tepat'
                : 'terlambat';
        }

        if ($data->status_pembayaran != 1 && !empty($data->due_date)
            && now()->lte(Carbon::parse($data->due_date)->addMonths(6))) {
            return 'on_track';
        }

        return 'terlambat';
    }

    private function slaPencairan($row): ?string
    {
        if (empty($row->no_kk) || empty($row->tanggal_pencairan)) {
            return null;
        }

        $cair = Carbon::parse($row->tanggal_pencairan);

        if ($cair->lt(Carbon::parse(self::SLA_PENCAIRAN_MULAI)->startOfDay())) {
            return null;
        }

        if (empty($row->tanggal_terima_finance)) {
            return null;
        }

        $finance = Carbon::parse($row->tanggal_terima_finance);
        $selisihHari = (int) (($cair->getTimestamp() - $finance->getTimestamp()) / 86400);

        return $selisihHari <= self::SLA_PENCAIRAN_HARI ? 'berhasil' : 'gagal';
    }

    private function evaluasiPencairan(int $tahun)
    {
        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        return PengajuanBarang::whereBetween('created_at', [$start, $end])
            ->whereNotNull('no_kk')
            ->whereNotNull('tanggal_pencairan')
            ->whereNotNull('tanggal_terima_finance')
            ->get()
            ->map(fn ($p) => [
                'date'   => Carbon::parse($p->created_at),
                'status' => $this->slaPencairan($p),
            ])
            ->filter(fn ($r) => $r['status'] !== null)
            ->values();
    }

    private function tagihanSelesai($row): bool
    {
        $status = strtolower(trim($row->status ?? ''));
        $tracking = strtolower(trim($row->tracking ?? ''));

        return $status === 'on_time' || ($status === 'selesai' && $tracking === 'selesai');
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

        if ((float) $detail->nilai_target <= 0) {
            return 0;
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $grouped = outstanding::whereBetween('created_at', [$start, $end])
            ->get()
            ->groupBy(fn($d) => $this->getStatus($d));

        $tepat = $grouped->get('tepat', collect())->count();
        $terlambat = $grouped->get('terlambat', collect())->count();

        $totalData = $tepat + $terlambat;
        if ($totalData === 0) {
            return 0;
        }

        return round(($tepat / $totalData) * 100, 1);
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

        $rows = outstanding::whereBetween('created_at', [$start, $end])
            ->get()
            ->map(fn($d) => [
                'date'   => Carbon::parse($d->created_at),
                'status' => $this->getStatus($d),
            ])
            ->reject(fn($r) => $r['status'] === 'on_track')
            ->map(fn($r) => $r + ['pct' => $r['status'] === 'tepat' ? 100 : 0])
            ->values();

        if ($rows->isEmpty()) {
            return $this->getDefaultDetailResponse();
        }

        $totalData = $rows->count();
        $above = $rows->where('status', 'tepat')->count();
        $below = $totalData - $above;

        $progress = round(($above / $totalData) * 100, 1);

        $gap = $progress < $nilaiTarget
            ? rtrim(rtrim(sprintf('%.1f', $nilaiTarget - $progress), '0'), '.')
            : 0;
        if ($gap === '') $gap = '0';

        $byMonth = $rows->groupBy(fn($r) => $r['date']->format('Y-m'));

        $monthlyAverages = $byMonth
            ->map(fn($g) => round($g->avg('pct'), 1))
            ->sortKeys()
            ->all();

        $dailyPerMonth = $byMonth
            ->map(fn($g) => $g
                ->groupBy(fn($r) => $r['date']->format('Y-m-d'))
                ->map(fn($d) => round($d->avg('pct'), 1))
                ->sortKeys()
                ->all()
            )
            ->sortKeys()
            ->all();

        return [
            'progress'                  => $progress,
            'gap'                       => $gap,
            'pie_chart'                 => ['above' => $above, 'below' => $below],
            'monthly_data'              => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyPerMonth,
            'monthly_progress'          => $monthlyAverages,
            'daily_progress_per_month'  => $dailyPerMonth,
        ];
    }

    public function calculateInisiatifEfisiensiKeuangan($item, $personId)
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

        $targetValue = (float) $detail->nilai_target;
        if ($targetValue == 0) return 0;

        $diterapkan = AnalysisFinanceInitiative::where('tahun', $tahun)
            ->where('is_implemented', true)
            ->count();

        return round((float) $diterapkan, 1);
    }

    public function calculateInisiatifEfisiensiKeuanganDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $this->getDefaultDetailResponse();
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return array_merge($this->getDefaultDetailResponse(), [
                'dataManual' => ['manual_document' => $detail->manual_document ?? null],
            ]);
        }

        $diterapkan = AnalysisFinanceInitiative::where('tahun', $tahun)
            ->where('is_implemented', true)
            ->count();

        $progress = round((float) $diterapkan, 1);

        $gap = $progress < $nilaiTarget ? rtrim(rtrim(sprintf('%.1f', abs($progress - $nilaiTarget)), '0'), '.') : 0;
        if ($gap === '') $gap = '0';

        return array_merge($this->getDefaultDetailResponse(), [
            'progress' => $progress,
            'gap' => $gap,
            'dataManual' => ['manual_document' => $detail->manual_document ?? null],
            'pie_chart' => ['above' => $diterapkan, 'below' => max(0, $nilaiTarget - $diterapkan)],
        ]);
    }

    public function calculateMengurangiManualWorkDanError($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $targetValue = (float) $detail->nilai_target;
        if ($targetValue == 0) return 0;

        $manualValue = (float) ($detail->manual_value ?? 0);
        return $manualValue > 0 ? round($manualValue, 1) : 0;
    }

    public function calculateMengurangiManualWorkDanErrorDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $this->getDefaultDetailResponse();
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return array_merge($this->getDefaultDetailResponse(), [
                'dataManual' => ['manual_document' => $detail->manual_document ?? null],
            ]);
        }

        $manualValue = (float) ($detail->manual_value ?? 0);
        $progress = $manualValue > 0 ? round($manualValue, 1) : 0;

        $gap = $progress < $nilaiTarget ? rtrim(rtrim(sprintf('%.1f', abs($progress - $nilaiTarget)), '0'), '.') : 0;
        if ($gap === '') $gap = '0';

        return array_merge($this->getDefaultDetailResponse(), [
            'progress' => $progress,
            'gap' => $gap,
            'dataManual' => ['manual_document' => $detail->manual_document ?? null],
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
        return $analisisData > 0 ? round((float) $analisisData, 1) : 0;
    }

    public function calculateLaporanAnalisisKeuanganDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();
        $emptyResponse = array_merge($this->getDefaultDetailResponse(), ['analisa_data' => []]);

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $emptyResponse;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $emptyResponse;
        }

        $analisaData = AnalysisReport::where('year', $tahun)->get();
        $analisisData = $analisaData->count();

        $above = $analisisData;
        $below = max(0, $nilaiTarget - $analisisData);
        $progress = $analisisData > 0 ? round((float) $analisisData, 1) : 0;

        $gap = $progress < $nilaiTarget ? rtrim(rtrim(sprintf('%.1f', abs($progress - $nilaiTarget)), '0'), '.') : 0;
        if ($gap === '') $gap = '0';

        return array_merge($this->getDefaultDetailResponse(), [
            'progress' => $progress,
            'gap' => $gap,
            'dataManual' => ['manual_document' => $detail->manual_document ?? null],
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

        $rows = $this->evaluasiPencairan($tahun);

        $total = $rows->count();
        if ($total === 0) {
            return 0;
        }

        $berhasil = $rows->where('status', 'berhasil')->count();

        return round(($berhasil / $total) * 100, 1);
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

        $rows = $this->evaluasiPencairan($tahun);
        $total = $rows->count();

        if ($total === 0) {
            $gapKosong = $nilaiTarget > 0 ? rtrim(rtrim(sprintf('%.1f', $nilaiTarget), '0'), '.') : 0;
            if ($gapKosong === '') $gapKosong = '0';

            return array_merge($this->getDefaultDetailResponse(), [
                'progress' => 0,
                'gap' => $gapKosong,
            ]);
        }

        $above = $rows->where('status', 'berhasil')->count();
        $below = $total - $above;

        $progress = round(($above / $total) * 100, 1);
        $gap = $progress < $nilaiTarget ? rtrim(rtrim(sprintf('%.1f', abs($progress - $nilaiTarget)), '0'), '.') : 0;
        if ($gap === '') $gap = '0';

        $byMonth = $rows->groupBy(fn ($r) => $r['date']->format('Y-m'));

        $monthlyAverages = $byMonth
            ->map(fn ($g) => round($g->where('status', 'berhasil')->count() / $g->count() * 100, 1))
            ->sortKeys()
            ->all();

        $dailyPerMonth = $byMonth
            ->map(fn ($g) => $g
                ->groupBy(fn ($r) => $r['date']->format('Y-m-d'))
                ->map(fn ($d) => round($d->where('status', 'berhasil')->count() / $d->count() * 100, 1))
                ->sortKeys()
                ->all()
            )
            ->sortKeys()
            ->all();

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyPerMonth,
            'monthly_progress' => $monthlyAverages,
            'daily_progress_per_month' => $dailyPerMonth,
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

        $tagihanSelesai = $latestTagihan->filter(fn ($row) => $this->tagihanSelesai($row))->count();

        return round(($tagihanSelesai / $totalTagihan) * 100, 1);
    }

    public function calculatePenyelesaianTagihanPerusahaanDetail($itemDetail, $personId = null)
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

        $tagihanSelesai = $latestTagihan->filter(fn ($row) => $this->tagihanSelesai($row))->count();

        $progress = round(($tagihanSelesai / $totalTagihan) * 100, 1);
        $nilaiTarget = (float) $detail->nilai_target;
        $gap = $progress < $nilaiTarget ? rtrim(rtrim(sprintf('%.1f', abs($progress - $nilaiTarget)), '0'), '.') : 0;
        if ($gap === '') $gap = '0';

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

            $isSelesai = $this->tagihanSelesai($tagihan) ? 1 : 0;

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
        $endDate = $tahun === now()->year
            ? now()->endOfMonth()
            : Carbon::create($tahun, 12, 31)->endOfDay();

        $data = ApprovalPendapatan::whereBetween('tanggal_mulai', [$startOfYear, $endDate])->get();

        $total = $data->count();
        if ($total === 0) {
            return 0;
        }

        $sesuai = $data->filter(function ($row) {
            $pembayaran = (float) ($row->jumlah_pembayaran ?? 0);
            $ppn        = (float) ($row->PPN ?? 0);
            $pph        = (float) ($row->PPH ?? 0);
            $kotor      = (float) ($row->total_pemasukan_kotor ?? 0);

            if ($pembayaran === 0.0) {
                $totalDenganPajak = $pembayaran + $ppn + $pph;

                return abs($totalDenganPajak - $kotor) < 0.01
                    || abs($pembayaran - $kotor) < 0.01;
            }

            return optional($row->total_pemasukan_kotor) !== null;
        })->count();

        return round(($sesuai / $total) * 100, 1);
    }

    public function calculateAkurasiPencatatanMasukDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $this->getDefaultDetailResponse();
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return $this->getDefaultDetailResponse();
        }

        $startOfYear = Carbon::create($tahun, 1, 1)->startOfDay();
        $endDate = $tahun === now()->year
            ? now()->endOfMonth()
            : Carbon::create($tahun, 12, 31)->endOfDay();

        $data = ApprovalPendapatan::whereBetween('tanggal_mulai', [$startOfYear, $endDate])->get();

        $total = $data->count();
        if ($total === 0) {
            return $this->getDefaultDetailResponse();
        }

        $accurateCount = 0;
        $notAccurateCount = 0;
        $dailyResult = [];

        foreach ($data as $row) {
            $pembayaran = (float) ($row->jumlah_pembayaran ?? 0);
            $ppn        = (float) ($row->PPN ?? 0);
            $pph        = (float) ($row->PPH ?? 0);
            $kotor      = (float) ($row->total_pemasukan_kotor ?? 0);

            if ($pembayaran === 0.0) {
                $totalDenganPajak = $pembayaran + $ppn + $pph;

                $isAkurat = abs($totalDenganPajak - $kotor) < 0.01
                    || abs($pembayaran - $kotor) < 0.01;
            } else {
                $isAkurat = optional($row->total_pemasukan_kotor) !== null;
            }

            $tanggalKey = Carbon::parse($row->tanggal_mulai)->format('Y-m-d');
            $dailyResult[$tanggalKey][] = $isAkurat ? 1 : 0;

            if ($isAkurat) {
                $accurateCount++;
            } else {
                $notAccurateCount++;
            }
        }

        $progress = round(($accurateCount / $total) * 100, 1);
        $nilaiTarget = (float) $detail->nilai_target;

        $gap = $progress < $nilaiTarget
            ? rtrim(rtrim(sprintf('%.1f', abs($progress - $nilaiTarget)), '0'), '.')
            : 0;
        if ($gap === '') $gap = '0';

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $dailyProgressPerMonth = [];

        foreach ($dailyResult as $dateStr => $values) {
            $monthKey = Carbon::parse($dateStr)->format('Y-m');
            $avg = round((array_sum($values) / count($values)) * 100, 1);

            $monthlyData[$monthKey][] = $avg;
            $dailyBreakdownPerMonth[$monthKey][$dateStr] = $avg;
            $dailyProgressPerMonth[$monthKey][$dateStr] = $avg;
        }

        $monthlyAverages = [];
        foreach ($monthlyData as $month => $values) {
            $monthlyAverages[$month] = round(array_sum($values) / count($values), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($dailyProgressPerMonth);

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            ksort($dailyBreakdownPerMonth[$month]);
        }
        foreach ($dailyProgressPerMonth as $month => $days) {
            ksort($dailyProgressPerMonth[$month]);
        }

        return [
            'progress'                  => $progress,
            'gap'                       => $gap,
            'pie_chart'                 => ['above' => $accurateCount, 'below' => $notAccurateCount],
            'monthly_data'              => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress'          => $monthlyAverages,
            'daily_progress_per_month'  => $dailyProgressPerMonth,
        ];
    }
}