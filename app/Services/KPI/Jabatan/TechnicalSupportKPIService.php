<?php

namespace App\Services\KPI\Jabatan;

use App\Models\karyawan;
use App\Models\detailPersonKPI;
use App\Models\PenilaianExam;
use App\Traits\KPIDefaultResponseTrait;
use App\Traits\TimeCalculationTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TechnicalSupportKPIService
{
    use KPIDefaultResponseTrait, TimeCalculationTrait;

    /**
     * Fungsi Helper Terpusat untuk memformat data Chart/Visualisasi.
     * Menjamin konsistensi format di seluruh fungsi Detail dan mencegah duplikasi kode (DRY).
     */
    private function formatChartData($progress, $nilaiTarget, $above, $below, $rawDailyData)
    {
        // PERBAIKAN: Standarisasi perhitungan Gap ($progress - $nilaiTarget), biarkan negatif jika di bawah target
        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');
        if ($gap === '' || $gap === '-') {
            $gap = '0';
        }

        $dailyAverages = [];
        foreach ($rawDailyData as $dateStr => $values) {
            if (count($values) > 0) {
                $dailyAverages[$dateStr] = round(array_sum($values) / count($values), 1);
            }
        }

        $monthlyData = [];
        $monthlyProgress = [];
        $dailyBreakdownPerMonth = [];
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
            if (count($dailyVals) > 0) {
                $monthlyAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
            }
        }
        foreach ($monthlyProgress as $month => $dailyVals) {
            if (count($dailyVals) > 0) {
                $monthlyProgressAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
            }
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAverages);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => max(0, $above), 'below' => max(0, $below)],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    public function calculateTingkatKeberhasilanSupportMemenuhiSLA($item, $personId)
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

        $start = Carbon::create($tahun, 1, 1, 0, 0, 0, 'Asia/Jakarta');
        $end = Carbon::create($tahun, 12, 31, 23, 59, 59, 'Asia/Jakarta');

        $idKaryawans = detailPersonKPI::where('detailTargetKey', $detail->id)
            ->when($personId !== null, fn($q) => $q->where('id_karyawan', $personId))
            ->pluck('id_karyawan')
            ->unique()
            ->toArray();

        if (empty($idKaryawans)) {
            return 0;
        }

        $namaDepans = karyawan::whereIn('id', $idKaryawans)->pluck('nama_lengkap')->filter()->map(fn($nama) => explode(' ', trim($nama))[0])->unique()->toArray();

        if (empty($namaDepans)) {
            return 0;
        }

        $jabatanList = karyawan::whereIn('id', $idKaryawans)->pluck('jabatan')->filter()->unique()->map(fn($n) => strtolower($n))->toArray();
        $keperluanPatterns = [];

        foreach ($jabatanList as $jabatan) {
            if (str_contains($jabatan, 'programmer') || str_contains($jabatan, 'koordinator itsm')) {
                $keperluanPatterns[] = 'Programming';
            } elseif (str_contains($jabatan, 'technical support') || str_contains($jabatan, 'tech support')) {
                $keperluanPatterns[] = 'Technical Support';
            }
        }

        $keperluanPatterns = array_unique($keperluanPatterns);
        if (empty($keperluanPatterns)) {
            return 0;
        }

        $ticketQuery = DB::table('tickets')
            ->select('created_at', 'tanggal_response', 'jam_response', 'tanggal_selesai', 'jam_selesai')
            ->whereIn('keperluan', $keperluanPatterns)
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('tanggal_selesai')
            ->whereIn('pic', $namaDepans);

        $tickets = $ticketQuery->get();

        if ($tickets->isEmpty()) {
            return 0;
        }

        $total = 0;
        $met = 0;

        foreach ($tickets as $ticket) {
            try {
                $createdAt = Carbon::parse($ticket->created_at, 'Asia/Jakarta');
                $resolvedAt = Carbon::parse(strlen($ticket->tanggal_selesai) > 10 ? $ticket->tanggal_selesai : $ticket->tanggal_selesai . ' ' . ($ticket->jam_selesai ?? '23:59:59'), 'Asia/Jakarta');

                $startAt = $createdAt;
                if (!empty($ticket->tanggal_response) && !empty($ticket->jam_response)) {
                    $startAt = Carbon::createFromFormat('Y-m-d H:i:s', $ticket->tanggal_response . ' ' . $ticket->jam_response, 'Asia/Jakarta');
                }

                $hours = $this->hitungJamKerja($startAt, $resolvedAt);
                $total++;

                if ($hours <= 8) {
                    $met++;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        if ($total === 0) {
            return 0;
        }

        return round(($met / $total) * 100, 1);
    }

    public function calculateTingkatKeberhasilanSupportMemenuhiSLADetail($itemDetail, $personId)
    {
        $details = $itemDetail->detailTargetKPI;
        if ($details->isEmpty()) {
            return $this->getDefaultDetailResponse();
        }

        $firstDetail = $details->first();
        $nilaiTarget = (float) $firstDetail->nilai_target;
        $tahun = (int) $firstDetail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $this->getDefaultDetailResponse();
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $idKaryawans = detailPersonKPI::where('detailTargetKey', $firstDetail->id)
            ->when($personId !== null, fn($q) => $q->where('id_karyawan', $personId))
            ->pluck('id_karyawan')
            ->unique()
            ->toArray();

        if (empty($idKaryawans)) {
            return $this->getDefaultDetailResponse();
        }

        $namaDepans = karyawan::whereIn('id', $idKaryawans)->pluck('nama_lengkap')->filter()->map(fn($nama) => explode(' ', trim($nama))[0])->unique()->toArray();

        if (empty($namaDepans)) {
            return $this->getDefaultDetailResponse();
        }

        $jabatanList = karyawan::whereIn('id', $idKaryawans)->pluck('jabatan')->filter()->unique()->map(fn($n) => strtolower($n))->toArray();
        $keperluanPatterns = [];

        foreach ($jabatanList as $jabatan) {
            if (str_contains($jabatan, 'programmer') || str_contains($jabatan, 'koordinator itsm')) {
                $keperluanPatterns[] = 'Programming';
            } elseif (str_contains($jabatan, 'technical support') || str_contains($jabatan, 'tech support')) {
                $keperluanPatterns[] = 'Technical Support';
            }
        }

        $keperluanPatterns = array_unique($keperluanPatterns);
        if (empty($keperluanPatterns)) {
            return $this->getDefaultDetailResponse();
        }

        $rawTickets = DB::table('tickets')
            ->select('created_at', 'tanggal_response', 'jam_response', 'tanggal_selesai', 'jam_selesai')
            ->whereIn('keperluan', $keperluanPatterns)
            ->whereIn('pic', $namaDepans)
            ->whereNotNull('tanggal_selesai')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        if ($rawTickets->isEmpty()) {
            return $this->getDefaultDetailResponse();
        }

        $totalTickets = 0;
        $resolutionMet = 0;
        $rawDailyData = [];

        foreach ($rawTickets as $ticket) {
            try {
                $createdAt = Carbon::parse($ticket->created_at, 'Asia/Jakarta');
                $resolvedAt = Carbon::parse(strlen($ticket->tanggal_selesai) > 10 ? $ticket->tanggal_selesai : $ticket->tanggal_selesai . ' ' . ($ticket->jam_selesai ?? '23:59:59'), 'Asia/Jakarta');

                $startResolution = $createdAt;
                if (!empty($ticket->tanggal_response) && !empty($ticket->jam_response)) {
                    $startResolution = Carbon::createFromFormat('Y-m-d H:i:s', $ticket->tanggal_response . ' ' . $ticket->jam_response, 'Asia/Jakarta');
                }

                $actualResolutionHours = $this->hitungJamKerja($startResolution, $resolvedAt);
                $metSLA = $actualResolutionHours <= 8;

                $totalTickets++;
                if ($metSLA) {
                    $resolutionMet++;
                }

                $dateKey = $resolvedAt->format('Y-m-d');
                if (!isset($rawDailyData[$dateKey])) {
                    $rawDailyData[$dateKey] = [];
                }
                $rawDailyData[$dateKey][] = $metSLA ? 100 : 0;
            } catch (\Exception $e) {
                continue;
            }
        }

        if ($totalTickets === 0) {
            return $this->getDefaultDetailResponse();
        }

        $progress = round(($resolutionMet / $totalTickets) * 100, 1);
        $progress = min($progress, 100);

        return $this->formatChartData($progress, $nilaiTarget, $resolutionMet, $totalTickets - $resolutionMet, $rawDailyData);
    }

    public function calculateKualitasLayananExam($item, $personId)
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

        $query = PenilaianExam::selectRaw('id_rkm, AVG(nilai_emote) as nilai')
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('id_rkm');

        $data = $query->groupBy('id_rkm')->get();
        $totalPenilaian = $data->count();

        if ($totalPenilaian == 0) {
            return 0.0;
        }

        $qualifiedPenilaian = $data
            ->filter(function ($item) {
                return $item->nilai >= 3.5;
            })
            ->count();

        $progress = ($qualifiedPenilaian / $totalPenilaian) * 100;
        return round($progress, 1);
    }

    public function calculateKualitasLayananExamDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (is_null($detail) || is_null($detail->nilai_target) || is_null($detail->detail_jangka)) {
            return $this->getDefaultDetailResponse();
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $this->getDefaultDetailResponse();
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $queryKPI = PenilaianExam::selectRaw('id_rkm, AVG(nilai_emote) as nilai, MIN(created_at) as created_at')
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('id_rkm');
            
        $groupedExams = $queryKPI->groupBy('id_rkm')->get();
        $totalPenilaian = $groupedExams->count();

        if ($totalPenilaian == 0) {
            return $this->getDefaultDetailResponse();
        }

        $qualifiedPenilaian = 0;
        $rawDailyData = [];

        foreach ($groupedExams as $exam) {
            $isQualified = $exam->nilai >= 3.5;
            if ($isQualified) {
                $qualifiedPenilaian++;
            }

            $dateKey = Carbon::parse($exam->created_at)->format('Y-m-d');
            if (!isset($rawDailyData[$dateKey])) {
                $rawDailyData[$dateKey] = [];
            }
            $rawDailyData[$dateKey][] = $isQualified ? 100 : 0;
        }

        $progress = round(($qualifiedPenilaian / $totalPenilaian) * 100, 1);

        return $this->formatChartData($progress, $nilaiTarget, $qualifiedPenilaian, $totalPenilaian - $qualifiedPenilaian, $rawDailyData);
    }
}
