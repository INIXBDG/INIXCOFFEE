<?php

namespace App\Services\KPI\Jabatan;

use App\Models\Tickets;
use App\Models\karyawan;
use App\Models\detailPersonKPI;
use App\Traits\KPIDefaultResponseTrait;
use App\Traits\TimeCalculationTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProgrammerKPIService
{
    use KPIDefaultResponseTrait, TimeCalculationTrait;

    private function formatChartData($progress, $nilaiTarget, $above, $below, $rawDailyData)
    {
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
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    public function calculateMengukurKualitasAplikasiAgarMinimBug($item, $personId)
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

        $namaDepans = karyawan::whereIn('id', $idKaryawans)
            ->pluck('nama_lengkap')
            ->filter()
            ->map(fn($nama) => explode(' ', trim($nama))[0])
            ->unique()
            ->toArray();

        if (empty($namaDepans)) {
            return 0;
        }

        $errorQuery = Tickets::whereBetween('created_at', [$start, $end])
            ->where('kategori', 'Error (Aplikasi)')
            ->where('keperluan', 'Programming')
            ->whereNotNull('tanggal_selesai')
            ->whereIn('pic', $namaDepans);

        $requestQuery = Tickets::whereBetween('created_at', [$start, $end])
            ->where('kategori', 'Request')
            ->whereIn('pic', $namaDepans);

        $jumlahError = (clone $errorQuery)->count();
        $jumlahRequest = $requestQuery->count();
        $totalTicket = $jumlahError + $jumlahRequest;

        if ($totalTicket === 0) {
            return 0;
        }

        $skorRasio = ($jumlahRequest / $totalTicket) * 100;

        if ($jumlahError === 0) {
            $rataSkorError = 100;
        } else {
            $totalSkorError = 0;

            $ticketsError = $errorQuery->select('id', 'created_at', 'tanggal_selesai', 'jam_selesai', 'tingkat_kesulitan')->get();

            foreach ($ticketsError as $ticket) {
                try {
                    $startAt = Carbon::parse($ticket->created_at, 'Asia/Jakarta');
                    $endAt = strlen($ticket->tanggal_selesai) > 10 ? Carbon::parse($ticket->tanggal_selesai, 'Asia/Jakarta') : Carbon::parse($ticket->tanggal_selesai . ' ' . ($ticket->jam_selesai ?? '23:59:59'), 'Asia/Jakarta');

                    $durasiJam = $this->hitungJamKerja($startAt, $endAt);

                    $skorDurasi = match (true) {
                        $durasiJam <= 4 => 100,
                        $durasiJam <= 8 => 80,
                        $durasiJam <= 24 => 60,
                        default => 30,
                    };

                    $bobot = match ($ticket->tingkat_kesulitan) {
                        'Major' => 1.5,
                        'Moderate' => 1.2,
                        default => 1.0,
                    };

                    $totalSkorError += min(100, $skorDurasi * $bobot);
                } catch (\Exception $e) {
                    continue;
                }
            }

            $rataSkorError = $jumlahError > 0 ? $totalSkorError / $jumlahError : 0;
        }

        $skorKualitas = $skorRasio * 0.5 + $rataSkorError * 0.5;
        $progress = $nilaiTarget > 0 ? ($skorKualitas / $nilaiTarget) * 100 : 0;

        return min(100, round($progress, 1));
    }

    public function calculateMengukurKualitasAplikasiAgarMinimBugDetail($itemDetail, $personId = null)
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

        $namaDepans = karyawan::whereIn('id', $idKaryawans)
            ->pluck('nama_lengkap')
            ->filter()
            ->map(fn($nama) => explode(' ', trim($nama))[0])
            ->unique()
            ->toArray();

        if (empty($namaDepans)) {
            return $this->getDefaultDetailResponse();
        }

        $errorQuery = Tickets::whereBetween('created_at', [$start, $end])
            ->where('kategori', 'Error (Aplikasi)')
            ->where('keperluan', 'Programming')
            ->whereNotNull('tanggal_selesai')
            ->whereIn('pic', $namaDepans)
            ->select('id', 'created_at', 'tanggal_selesai', 'jam_selesai', 'tingkat_kesulitan');

        $requestQuery = Tickets::whereBetween('created_at', [$start, $end])
            ->where('kategori', 'Request')
            ->whereIn('pic', $namaDepans);

        $jumlahRequest = $requestQuery->count();
        $ticketsError = $errorQuery->get();
        $jumlahError = $ticketsError->count();

        $totalTicket = $jumlahRequest + $jumlahError;

        if ($totalTicket === 0) {
            return $this->getDefaultDetailResponse();
        }

        $skorRasio = ($jumlahRequest / $totalTicket) * 100;

        if ($jumlahError === 0) {
            $rataSkorError = 100;
            $above = 0;
            $below = 0;
            $rawDailyData = [];
        } else {
            $totalSkorError = 0;
            $ticketScores = [];
            $above = 0;
            $below = 0;

            foreach ($ticketsError as $ticket) {
                try {
                    $startAt = Carbon::parse($ticket->created_at, 'Asia/Jakarta');
                    $endAt = strlen($ticket->tanggal_selesai) > 10 ? Carbon::parse($ticket->tanggal_selesai, 'Asia/Jakarta') : Carbon::parse($ticket->tanggal_selesai . ' ' . ($ticket->jam_selesai ?? '23:59:59'), 'Asia/Jakarta');

                    $durasiJam = $this->hitungJamKerja($startAt, $endAt);

                    $skorDurasi = match (true) {
                        $durasiJam <= 4 => 100,
                        $durasiJam <= 8 => 80,
                        $durasiJam <= 24 => 60,
                        default => 30,
                    };

                    $bobot = match ($ticket->tingkat_kesulitan) {
                        'Major' => 1.5,
                        'Moderate' => 1.2,
                        default => 1.0,
                    };

                    $skorError = min(100, $skorDurasi * $bobot);
                    $totalSkorError += $skorError;

                    if ($skorError >= 70) {
                        $above++;
                    } else {
                        $below++;
                    }

                    $dateKey = $endAt->format('Y-m-d');
                    if (!isset($ticketScores[$dateKey])) {
                        $ticketScores[$dateKey] = [];
                    }
                    $ticketScores[$dateKey][] = $skorError;
                } catch (\Exception $e) {
                    continue;
                }
            }

            $rataSkorError = $totalSkorError / $jumlahError;
            $rawDailyData = $ticketScores;
        }

        $skorKualitas = $skorRasio * 0.5 + $rataSkorError * 0.5;
        $progress = $nilaiTarget > 0 ? ($skorKualitas / $nilaiTarget) * 100 : 0;
        $progress = round(min($progress, 100), 1);

        return $this->formatChartData($progress, $nilaiTarget, $above, $below, $rawDailyData);
    }

    public function calculateProgressKetepatanWaktuPenyelesaianFitur($item, $personId)
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

        $namaDepans = karyawan::whereIn('id', $idKaryawans)
            ->pluck('nama_lengkap')
            ->filter()
            ->map(fn($nama) => explode(' ', trim($nama))[0])
            ->unique()
            ->toArray();

        if (empty($namaDepans)) {
            return 0;
        }

        $jabatanFilter = karyawan::whereIn('id', $idKaryawans)
            ->pluck('jabatan')
            ->filter()
            ->unique()
            ->map(
                fn($j) => match (strtolower(trim($j))) {
                    'programmer', 'koordinator itsm' => 'Programming',
                    'technical support' => 'Technical Support',
                    'tim digital' => 'Tim Digital',
                    default => trim($j),
                },
            )
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (empty($jabatanFilter)) {
            return 0;
        }

        $ticketQuery = Tickets::whereIn('keperluan', $jabatanFilter)
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('tanggal_selesai')
            ->whereIn('pic', $namaDepans);

        $tickets = $ticketQuery->select('id', 'created_at', 'tanggal_selesai', 'jam_selesai', 'tingkat_kesulitan', 'kategori')->get();

        if ($tickets->isEmpty()) {
            return 0;
        }

        $metCount = 0;
        $total = $tickets->count();

        foreach ($tickets as $ticket) {
            try {
                $priority = 'Low';
                if (in_array(strtolower($ticket->tingkat_kesulitan), ['major', 'moderate'])) {
                    $priority = 'High';
                } elseif ($ticket->kategori === 'Error (Aplikasi)') {
                    $priority = 'Medium';
                }

                $startAt = Carbon::parse($ticket->created_at, 'Asia/Jakarta');
                $endAt = strlen($ticket->tanggal_selesai) > 10 ? Carbon::parse($ticket->tanggal_selesai, 'Asia/Jakarta') : Carbon::parse($ticket->tanggal_selesai . ' ' . ($ticket->jam_selesai ?? '23:59:59'), 'Asia/Jakarta');

                $actualHours = $this->hitungJamKerja($startAt, $endAt);

                $slaMet = match ($priority) {
                    'High' => $actualHours <= 24,
                    'Medium' => $actualHours <= 40,
                    default => true,
                };

                if ($slaMet) {
                    $metCount++;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        $realisasiPersen = ($metCount / $total) * 100;
        $progress = $nilaiTarget > 0 ? ($realisasiPersen / $nilaiTarget) * 100 : 0;

        return min(100, round($progress, 1));
    }

    public function calculateProgressKetepatanWaktuPenyelesaianFiturDetail($itemDetail, $personId = null)
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

        $namaDepans = karyawan::whereIn('id', $idKaryawans)
            ->pluck('nama_lengkap')
            ->filter()
            ->map(fn($nama) => explode(' ', trim($nama))[0])
            ->unique()
            ->toArray();

        if (empty($namaDepans)) {
            return $this->getDefaultDetailResponse();
        }

        $jabatanFilter = karyawan::whereIn('id', $idKaryawans)
            ->pluck('jabatan')
            ->filter()
            ->unique()
            ->map(
                fn($j) => match (strtolower(trim($j))) {
                    'programmer', 'koordinator itsm' => 'Programming',
                    'technical support' => 'Technical Support',
                    'tim digital' => 'Tim Digital',
                    default => trim($j),
                },
            )
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (empty($jabatanFilter)) {
            return $this->getDefaultDetailResponse();
        }

        $tickets = Tickets::whereIn('keperluan', $jabatanFilter)
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('pic', $namaDepans)
            ->whereNotNull('tanggal_selesai')
            ->select('id', 'created_at', 'tanggal_selesai', 'jam_selesai', 'tingkat_kesulitan', 'kategori')
            ->get();

        if ($tickets->isEmpty()) {
            return $this->getDefaultDetailResponse();
        }

        $metCount = 0;
        $total = $tickets->count();
        $totalHours = 0;
        $fastest = null;
        $slowest = 0;

        $rawDailyData = [];

        foreach ($tickets as $t) {
            try {
                $priority = 'Low';
                if (in_array(strtolower($t->tingkat_kesulitan), ['major', 'moderate'])) {
                    $priority = 'High';
                } elseif (in_array(strtolower($t->tingkat_kesulitan), ['minor', 'normal']) && $t->kategori === 'Error (Aplikasi)') {
                    $priority = 'Medium';
                }

                $startAt = Carbon::parse($t->created_at, 'Asia/Jakarta');
                $endAt = strlen($t->tanggal_selesai) > 10 ? Carbon::parse($t->tanggal_selesai, 'Asia/Jakarta') : Carbon::parse($t->tanggal_selesai . ' ' . ($t->jam_selesai ?? '23:59:59'), 'Asia/Jakarta');

                $hours = $this->hitungJamKerja($startAt, $endAt);

                $slaMet = match ($priority) {
                    'High' => $hours <= 24,
                    'Medium' => $hours <= 40,
                    default => true,
                };

                if ($slaMet) {
                    $metCount++;
                }

                $totalHours += $hours;
                $fastest = is_null($fastest) ? $hours : min($fastest, $hours);
                $slowest = max($slowest, $hours);

                $dayKey = $endAt->format('Y-m-d');
                $val = $slaMet ? 100 : 0;

                if (!isset($rawDailyData[$dayKey])) {
                    $rawDailyData[$dayKey] = [];
                }
                $rawDailyData[$dayKey][] = $val;
            } catch (\Exception $e) {
                continue;
            }
        }

        $realisasi = ($metCount / $total) * 100;
        $progress = min(round(($realisasi / $nilaiTarget) * 100, 1), 100);

        $baseResponse = $this->formatChartData($progress, $nilaiTarget, $metCount, $total - $metCount, $rawDailyData);

        return array_merge($baseResponse, [
            'realisasi_persen' => round($realisasi, 1),
            'total_ticket' => $total,
            'sla_met_count' => $metCount,
            'average_resolution_hours' => $total > 0 ? round($totalHours / $total, 1) : 0,
            'fastest_resolution' => $fastest ?? 0,
            'slowest_resolution' => $slowest ?? 0,
        ]);
    }
}
