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
            ->pluck('id_karyawan')
            ->unique()
            ->toArray();

        if (empty($idKaryawans)) {
            return 0;
        }

        $picNames = karyawan::whereIn('id', $idKaryawans)
            ->pluck('nama_lengkap')
            ->map(fn($nama) => explode(' ', trim($nama))[0] ?? '')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (empty($picNames)) {
            return 0;
        }

        $normalizedPicNames = array_map(function ($name) {
            return match ($name) {
                'Stepanus' => 'Stefan',
                'Jonathan' => 'Valen',
                default => $name,
            };
        }, $picNames);

        $errorQuery = Tickets::whereBetween('created_at', [$start, $end])
            ->where('kategori', 'Error (Aplikasi)')
            ->where('keperluan', 'Programming')
            ->whereNotNull('tanggal_selesai');

        $requestQuery = Tickets::whereBetween('created_at', [$start, $end])
            ->where('kategori', 'Request');

        if ($personId !== null) {
            $karyawanData = karyawan::find($personId);
            if (!$karyawanData) {
                return 0;
            }

            $firstName = explode(' ', trim($karyawanData->nama_lengkap))[0] ?? '';
            if (!$firstName) {
                return 0;
            }

            $firstName = match ($firstName) {
                'Stepanus' => 'Stefan',
                'Jonathan' => 'Valen',
                default => $firstName,
            };

            $errorQuery->where('pic', $firstName);
            $requestQuery->where('pic', $firstName);
        } else {
            $errorQuery->whereIn('pic', $normalizedPicNames);
            $requestQuery->whereIn('pic', $normalizedPicNames);
        }

        $ticketsError = $errorQuery->get();
        $ticketsRequest = $requestQuery->get();

        $jumlahError = $ticketsError->count();
        $jumlahRequest = $ticketsRequest->count();
        $totalTicket = $jumlahError + $jumlahRequest;

        if ($totalTicket === 0) {
            return 0;
        }

        $skorRasio = ($jumlahRequest / $totalTicket) * 100;

        if ($jumlahError === 0) {
            $rataSkorError = 100;
        } else {
            $totalSkorError = 0;

            foreach ($ticketsError as $ticket) {
                try {
                    $startAt = Carbon::parse($ticket->created_at, 'Asia/Jakarta');

                    $endAt = strlen($ticket->tanggal_selesai) > 10
                        ? Carbon::parse($ticket->tanggal_selesai, 'Asia/Jakarta')
                        : Carbon::parse($ticket->tanggal_selesai . ' ' . ($ticket->jam_selesai ?? '23:59:59'), 'Asia/Jakarta');

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

        $skorKualitas = ($skorRasio * 0.5) + ($rataSkorError * 0.5);

        $progress = $nilaiTarget > 0 ? ($skorKualitas / $nilaiTarget) * 100 : 0;

        return min(100, round($progress, 1));
    }

    public function calculateMengukurKualitasAplikasiAgarMinimBugDetail($itemDetail, $personId = null)
    {
        $details = $itemDetail->detailTargetKPI;

        if ($details->isEmpty()) {
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

        $firstDetail = $details->first();
        $nilaiTarget = (float) $firstDetail->nilai_target;
        $tahun = (int) $firstDetail->detail_jangka;

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

        $picNames = [];

        if ($personId !== null) {
            $idKaryawans = detailPersonKPI::where('detailTargetKey', $firstDetail->id)
                ->where('id_karyawan', $personId)
                ->pluck('id_karyawan')->unique()->toArray();
        } else {
            $idKaryawans = detailPersonKPI::where('detailTargetKey', $firstDetail->id)
                ->pluck('id_karyawan')->unique()->toArray();
        }

        if (!empty($idKaryawans)) {
            $namaLengkapList = karyawan::whereIn('id', $idKaryawans)->pluck('nama_lengkap')->toArray();
            $picNames = array_map(fn($nama) => explode(' ', trim($nama))[0] ?? '', $namaLengkapList);
        }

        $picNames = array_filter($picNames);

        if (empty($picNames)) {
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

        $normalizedPicNames = array_map(fn($name) => match ($name) {
            'Stepanus' => 'Stefan',
            'Jonathan' => 'Valen',
            default => $name
        }, $picNames);

        $ticketsError = Tickets::whereBetween('created_at', [$start, $end])
            ->where('kategori', 'Error (Aplikasi)')
            ->where('keperluan', 'Programming')
            ->whereIn('pic', $normalizedPicNames)
            ->whereNotNull('tanggal_selesai')
            ->get();

        $ticketsRequest = Tickets::whereBetween('created_at', [$start, $end])
            ->where('kategori', 'Request')
            ->whereIn('pic', $normalizedPicNames)
            ->get();

        $jumlahError = $ticketsError->count();
        $jumlahRequest = $ticketsRequest->count();
        $totalTicket = $jumlahRequest + $jumlahError;

        if ($totalTicket === 0) {
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

        $skorRasio = ($jumlahRequest / $totalTicket) * 100;

        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        if ($jumlahError === 0) {
            $rataSkorError = 100;
            $above = 0;
            $below = 0;
            $monthlyAverages = [];
            $dailyBreakdownPerMonth = [];
        } else {
            $totalSkorError = 0;
            $ticketScores = [];

            foreach ($ticketsError as $ticket) {
                $startAt = Carbon::parse($ticket->created_at, 'Asia/Jakarta');
                $endAt = strlen($ticket->tanggal_selesai) > 10
                    ? Carbon::parse($ticket->tanggal_selesai, 'Asia/Jakarta')
                    : Carbon::parse($ticket->tanggal_selesai . ' ' . ($ticket->jam_selesai ?? '23:59:59'), 'Asia/Jakarta');

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

                $dateKey = $endAt->format('Y-m-d');
                $ticketScores[$dateKey] = $skorError;
            }

            $rataSkorError = $totalSkorError / $jumlahError;

            $above = count(array_filter($ticketScores, fn($s) => $s >= 70));
            $below = $jumlahError - $above;

            $monthlyData = [];
            $dailyBreakdownPerMonth = [];

            foreach ($ticketScores as $dateStr => $score) {
                $date = Carbon::parse($dateStr);
                $monthKey = $date->format('Y-m');
                $dayKey = $dateStr;

                $dailyBreakdownPerMonth[$monthKey][$dayKey] = round($score, 1);
                $monthlyData[$monthKey][] = $score;

                $dailyProgressPerMonth[$monthKey][$dayKey] = round(min($score, 100), 1);
                $monthlyProgress[$monthKey][] = min($score, 100);
            }

            $monthlyAverages = [];
            $monthlyProgressAvg = [];

            foreach ($monthlyData as $month => $scores) {
                $monthlyAverages[$month] = round(array_sum($scores) / count($scores), 1);
            }

            foreach ($monthlyProgress as $month => $vals) {
                $monthlyProgressAvg[$month] = round(array_sum($vals) / count($vals), 1);
            }

            ksort($monthlyAverages);
            ksort($dailyBreakdownPerMonth);
            ksort($monthlyProgressAvg);
            ksort($dailyProgressPerMonth);
        }

        $skorKualitas = $skorRasio * 0.5 + $rataSkorError * 0.5;
        $progress = $nilaiTarget > 0 ? ($skorKualitas / $nilaiTarget) * 100 : 0;
        $progress = round(min($progress, 100), 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = $gapRaw < 0 ? abs($gapRaw) : 0;
        $gap = rtrim(rtrim(sprintf('%.1f', $gap), '0'), '.');

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyAverages ?? [],
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth ?? [],
            'monthly_progress' => $monthlyProgressAvg ?? [],
            'daily_progress_per_month' => $dailyProgressPerMonth ?? [],
        ];
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
            ->pluck('id_karyawan')
            ->unique()
            ->toArray();

        if (empty($idKaryawans)) {
            return 0;
        }

        $picNames = karyawan::whereIn('id', $idKaryawans)
            ->pluck('nama_lengkap')
            ->map(fn($nama) => explode(' ', trim($nama))[0] ?? '')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (empty($picNames)) {
            return 0;
        }

        $picJabatan = karyawan::whereIn('id', $idKaryawans)
            ->pluck('jabatan')
            ->filter()
            ->unique()
            ->map(fn($n) => ucwords(strtolower($n)))
            ->values()
            ->toArray();

        $jabatanFilter = array_map(function ($jabatan) {
            return match (strtolower($jabatan)) {
                'programmer', 'koordinator itsm' => 'Programming',
                'technical support' => 'Technical Support',
                'tim digital' => 'Tim Digital',
                default => $jabatan,
            };
        }, $picJabatan);

        $jabatanFilter = array_unique(array_filter($jabatanFilter));

        if (empty($jabatanFilter)) {
            return 0;
        }

        $ticketQuery = Tickets::whereIn('keperluan', $jabatanFilter)
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('tanggal_selesai');

        if ($personId !== null) {
            $karyawanData = karyawan::find($personId);
            if (!$karyawanData) {
                return 0;
            }

            $firstName = explode(' ', trim($karyawanData->nama_lengkap))[0] ?? '';

            $firstName = match ($firstName) {
                'Stepanus' => 'Stefan',
                'Jonathan' => 'Valen',
                default => $firstName,
            };

            $ticketQuery->where('pic', $firstName);
        } else {
            $ticketQuery->whereIn('pic', $picNames);
        }

        $tickets = $ticketQuery->get();

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

                $endAt = strlen($ticket->tanggal_selesai) > 10
                    ? Carbon::parse($ticket->tanggal_selesai, 'Asia/Jakarta')
                    : Carbon::parse($ticket->tanggal_selesai . ' ' . ($ticket->jam_selesai ?? '23:59:59'), 'Asia/Jakarta');

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

    public function calculateProgressKetepatanWaktuPenyelesaianFiturDetail($itemDetail, $personId)
    {
        $details = $itemDetail->detailTargetKPI;

        if ($details->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => 0,
                'realisasi_persen' => 0,
                'total_ticket' => 0,
                'sla_met_count' => 0,
                'average_resolution_hours' => 0,
                'fastest_resolution' => 0,
                'slowest_resolution' => 0,
                'sla_rate_per_priority' => [],
                'top_pic_performance' => [],
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'monthly_ticket_count' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $firstDetail = $details->first();
        $nilaiTarget = (float) $firstDetail->nilai_target;
        $tahun = (int) $firstDetail->detail_jangka;

        if ($nilaiTarget <= 0) {
            return [];
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $idKaryawans = detailPersonKPI::where('detailTargetKey', $firstDetail->id)
            ->when($personId, fn($q) => $q->where('id_karyawan', $personId))
            ->pluck('id_karyawan')->unique()->toArray();

        $namaLengkapList = karyawan::whereIn('id', $idKaryawans)->pluck('nama_lengkap')->toArray();
        $picNames = array_map(fn($n) => explode(' ', trim($n))[0] ?? '', $namaLengkapList);
        $picNames = array_filter($picNames);

        if (empty($picNames)) {
            return [];
        }

        $normalizedPicNames = array_map(fn($name) => match ($name) {
            'Stepanus' => 'Stefan',
            'Jonathan' => 'Valen',
            default => $name
        }, $picNames);

        $targetJabatanList = $details->pluck('jabatan')->unique()->toArray();

        $picJabatan = karyawan::whereIn('jabatan', $targetJabatanList)
            ->pluck('jabatan')->unique()->toArray();

        $jabatanFilter = array_map(fn($j) => match (strtolower($j)) {
            'programmer', 'koordinator itsm' => 'Programming',
            'technical support' => 'Technical Support',
            'tim digital' => 'Tim Digital',
            default => $j
        }, $picJabatan);

        $tickets = Tickets::whereIn('keperluan', $jabatanFilter)
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('pic', $normalizedPicNames)
            ->whereNotNull('tanggal_selesai')
            ->get();

        if ($tickets->isEmpty()) {
            return [];
        }

        $metCount = 0;
        $total = $tickets->count();
        $totalHours = 0;
        $fastest = null;
        $slowest = 0;

        $monthlyData = [];
        $dailyBreakdown = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($tickets as $t) {
            $priority = 'Low';

            if (in_array(strtolower($t->tingkat_kesulitan), ['major', 'moderate'])) {
                $priority = 'High';
            } elseif (in_array(strtolower($t->tingkat_kesulitan), ['minor', 'normal']) && $t->kategori === 'Error (Aplikasi)') {
                $priority = 'Medium';
            }

            $startAt = Carbon::parse($t->created_at);
            $endAt = strlen($t->tanggal_selesai) > 10
                ? Carbon::parse($t->tanggal_selesai)
                : Carbon::parse($t->tanggal_selesai . ' ' . ($t->jam_selesai ?? '23:59:59'));

            $hours = $this->hitungJamKerja($startAt, $endAt);

            $slaMet = match ($priority) {
                'High' => $hours <= 24,
                'Medium' => $hours <= 40,
                default => true
            };

            if ($slaMet) $metCount++;

            $totalHours += $hours;
            $fastest = is_null($fastest) ? $hours : min($fastest, $hours);
            $slowest = max($slowest, $hours);

            $month = $endAt->format('Y-m');
            $day = $endAt->format('Y-m-d');

            $val = $slaMet ? 1 : 0;

            $monthlyData[$month][] = $val;
            $dailyBreakdown[$month][$day] = $val;

            $progressVal = $val * 100;
            $monthlyProgress[$month][] = $progressVal;
            $dailyProgressPerMonth[$month][$day] = $progressVal;
        }

        $monthlyAvg = [];
        $monthlyProgressAvg = [];

        foreach ($monthlyData as $m => $vals) {
            $monthlyAvg[$m] = round((array_sum($vals) / count($vals)) * 100, 1);
        }

        foreach ($monthlyProgress as $m => $vals) {
            $monthlyProgressAvg[$m] = round(array_sum($vals) / count($vals), 1);
        }

        ksort($monthlyAvg);
        ksort($dailyBreakdown);
        ksort($monthlyProgressAvg);
        ksort($dailyProgressPerMonth);

        $realisasi = ($metCount / $total) * 100;
        $progress = min(round(($realisasi / $nilaiTarget) * 100, 1), 100);

        return [
            'progress' => $progress,
            'gap' => max(0, 100 - $progress),
            'realisasi_persen' => round($realisasi, 1),
            'total_ticket' => $total,
            'sla_met_count' => $metCount,
            'average_resolution_hours' => round($totalHours / $total, 1),
            'fastest_resolution' => $fastest,
            'slowest_resolution' => $slowest,
            'pie_chart' => [
                'above' => $metCount,
                'below' => $total - $metCount
            ],
            'monthly_data' => $monthlyAvg,
            'monthly_ticket_count' => [],
            'daily_breakdown_per_month' => $dailyBreakdown,
            'monthly_progress' => $monthlyProgressAvg,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }
}
