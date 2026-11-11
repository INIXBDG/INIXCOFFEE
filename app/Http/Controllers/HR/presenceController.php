<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\AbsensiKaryawan;
use App\Models\HariLibur;
use App\Models\izinTigaJam;
use App\Models\karyawan;
use App\Models\pengajuancuti;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class presenceController extends Controller
{
    public function index()
    {
        return view('HR.presence.index');
    }

    public function getSaturdayWorkingJabatan()
    {
        return ['office boy', 'technical support'];
    }

    public function isSaturdayWorker($jabatan)
    {
        return in_array(strtolower(trim((string) $jabatan)), $this->getSaturdayWorkingJabatan());
    }

    public function isExpectedToWork($tanggal, $jabatan)
    {
        if ($tanggal->isSunday()) {
            return false;
        }
        if ($tanggal->isSaturday()) {
            return $this->isSaturdayWorker($jabatan);
        }
        return true;
    }

    public function classifyKeterangan($record)
    {
        if (!$record) {
            return null;
        }
        $t = strtolower(trim((string) ($record->keterangan ?? '')));
        if ($t === '') {
            return null;
        }

        if (str_contains($t, 'cuti')) {
            return 'cuti';
        }
        if (str_contains($t, 'sakit')) {
            return 'sakit';
        }
        if (str_contains($t, 'telat') || str_contains($t, 'terlambat')) {
            return 'telat'; 
        }
        if (str_contains($t, 'izin') || str_contains($t, 'ijin')) {
            return 'izin';
        }
        if (str_contains($t, 'tidak masuk') || str_contains($t, 'alpa')) {
            return null;
        }
        if (str_contains($t, 'masuk')) {
            return 'masuk';
        }
        return null;
    }

    public function isLateRecord($absen)
    {
        if (!$absen) {
            return false;
        }
        if ($this->classifyKeterangan($absen) === 'telat') {
            return true;
        }
        return $this->parseLateMinutes($absen->waktu_keterlambatan ?? null) > 0;
    }

    public function averageLatePerEmployee($rows)
    {
        $perEmployee = $rows->groupBy('id')->map(function ($items) {
            $late = $items->where('status', 'telat');
            $n = $late->count();
            return $n > 0 ? $late->sum('late_minutes') / $n : 0;
        });

        return $perEmployee->isEmpty() ? 0 : round($perEmployee->avg(), 1);
    }

    public function isPresentRecord($absen)
    {
        if (!$absen) {
            return false;
        }
        if ($absen->jam_masuk) {
            return true;
        }
        return in_array($this->classifyKeterangan($absen), ['masuk', 'telat'], true);
    }

    public function approvalState($value)
    {
        $v = strtolower(trim((string) $value));
        if (in_array($v, ['1', 'approved', 'disetujui', 'setuju', 'acc', 'true', 'ya'], true)) {
            return 'approved';
        }
        if (in_array($v, ['', '0', 'pending', 'menunggu', 'null'], true)) {
            return 'pending';
        }
        return 'rejected';
    }

    public function isIzinCounted($value)
    {
        return in_array((string) $value, ['1', '2'], true);
    }

    public function isCutiMenikah($record)
    {
        foreach (['jenis_cuti', 'kategori', 'jenis', 'alasan', 'keterangan'] as $field) {
            $value = strtolower((string) ($record->{$field} ?? ''));
            if ($value !== '' && str_contains($value, 'menikah')) {
                return true;
            }
        }
        return false;
    }

    public function getActiveKaryawanQuery()
    {
        return karyawan::where('status_aktif', '1')->whereNot('jabatan', 'Outsource')->where('kode_karyawan', 'NOT LIKE', 'OL%')->whereNot('jabatan', 'Pilih Jabatan')->whereNotNull('nip')->whereNot('divisi', 'Direksi')->whereNot('jabatan', 'GM');
    }

    public function parseLateMinutes($waktu)
    {
        if (!$waktu || $waktu === '00:00:00') {
            return 0;
        }
        $p = explode(':', $waktu);
        return round(((int) $p[0]) * 60 + (int) ($p[1] ?? 0) + ((int) ($p[2] ?? 0)) / 60, 1);
    }

    public function startDateOf($emp)
    {
        if (!$emp->awal_probation) {
            return null;
        }
        try {
            return Carbon::parse($emp->awal_probation)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function resolvePeriod(Request $request)
    {
        $now = now();
        $year = $request->filled('year') ? (int) $request->input('year') : null;
        $month = $request->filled('month') ? (int) $request->input('month') : null;
        $quarter = $request->filled('quarter') ? (int) $request->input('quarter') : null;

        if ($year === null && $month === null && $quarter === null) {
            $year = $now->year;
            $month = $now->month;
        }

        $yearUsed = $year ?? $now->year;

        if ($quarter !== null && $quarter >= 1 && $quarter <= 4) {
            $start = Carbon::create($yearUsed, ($quarter - 1) * 3 + 1, 1)->startOfDay();
            $end = $start->copy()->addMonths(2)->endOfMonth()->startOfDay();
            $label = "Triwulan {$quarter} {$yearUsed}";
            $type = 'quarter';
            $month = null;
        } elseif ($month !== null && $month >= 1 && $month <= 12) {
            $start = Carbon::create($yearUsed, $month, 1)->startOfDay();
            $end = $start->copy()->endOfMonth()->startOfDay();
            $label = $start->translatedFormat('F Y');
            $type = 'month';
            $quarter = null;
        } else {
            $start = Carbon::create($yearUsed, 1, 1)->startOfDay();
            $end = Carbon::create($yearUsed, 12, 31)->startOfDay();
            $label = "Tahun {$yearUsed}";
            $type = 'year';
            $month = null;
            $quarter = null;
        }

        return [
            'start' => $start,
            'end' => $end,
            'label' => $label,
            'type' => $type,
            'year' => $yearUsed,
            'month' => $month,
            'quarter' => $quarter,
        ];
    }

    public function syncHolidays($tahun)
    {
        try {
            $response = Http::timeout(8)->get('https://libur.deno.dev/api', ['year' => $tahun]);
            if ($response->successful()) {
                foreach ($response->json() as $libur) {
                    HariLibur::updateOrCreate(['tanggal' => $libur['date']], ['nama' => $libur['name'], 'year' => $tahun]);
                }
            }
        } catch (\Throwable $e) {
            return false;
        }
        return true;
    }

    public function ensureHolidays(Carbon $start, Carbon $end)
    {
        for ($y = $start->year; $y <= $end->year; $y++) {
            if (!HariLibur::whereYear('tanggal', $y)->exists()) {
                $this->syncHolidays($y);
            }
        }
    }

    public function getHolidayMap(Carbon $start, Carbon $end)
    {
        return HariLibur::whereDate('tanggal', '>=', $start->toDateString())->whereDate('tanggal', '<=', $end->toDateString())->get()->mapWithKeys(fn($h) => [Carbon::parse($h->tanggal)->toDateString() => $h->nama])->toArray();
    }

    public function buildLeaveMap($ids, Carbon $start, Carbon $end)
    {
        $map = ['approved' => [], 'pending' => []];

        $records = pengajuancuti::whereIn('id_karyawan', $ids)->whereDate('tanggal_awal', '<=', $end->toDateString())->whereDate('tanggal_akhir', '>=', $start->toDateString())->get();

        foreach ($records as $c) {
            $state = $this->approvalState($c->approval_manager);
            if ($state === 'rejected') {
                continue;
            }

            $from = Carbon::parse($c->tanggal_awal)->startOfDay();
            $to = Carbon::parse($c->tanggal_akhir)->startOfDay();
            if ($from->lt($start)) {
                $from = $start->copy();
            }
            if ($to->gt($end)) {
                $to = $end->copy();
            }

            $tipe = strtolower((string) $c->tipe) === 'sakit' ? 'sakit' : 'cuti';
            $entry = [
                'tipe' => $tipe,
                'menikah' => $tipe === 'cuti' && $this->isCutiMenikah($c),
                'alasan' => $c->alasan ?? ($c->keterangan ?? '-'),
            ];

            for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
                $key = $d->toDateString();
                if ($state === 'approved') {
                    $map['approved'][$c->id_karyawan][$key] = $entry;
                } elseif (!isset($map['pending'][$c->id_karyawan][$key])) {
                    $map['pending'][$c->id_karyawan][$key] = $entry;
                }
            }
        }

        return $map;
    }

    public function buildIzinMap($ids, Carbon $start, Carbon $end)
    {
        $map = [];

        $records = izinTigaJam::whereIn('id_karyawan', $ids)
            ->whereDate('tanggal', '>=', $start->toDateString())
            ->whereDate('tanggal', '<=', $end->toDateString())
            ->whereIn('approval', [1, 2])
            ->get();

        foreach ($records as $i) {
            if (!$this->isIzinCounted($i->approval)) {
                continue;
            }
            $map[$i->id_karyawan][Carbon::parse($i->tanggal)->toDateString()] = $i->alasan ?: '-';
        }

        return $map;
    }

    public function buildDailyStatus(Carbon $start, Carbon $end, $divisi = null, $idKaryawan = null)
    {
        $employees = $this->getActiveKaryawanQuery()
            ->when($divisi && $divisi !== 'all', fn($q) => $q->where('divisi', $divisi))
            ->when($idKaryawan, fn($q) => $q->where('id', $idKaryawan))
            ->get(['id', 'nama_lengkap', 'jabatan', 'divisi', 'foto', 'awal_probation']);

        $holidays = $this->getHolidayMap($start, $end);
        $today = now()->startOfDay();
        $last = $end->lt($today) ? $end->copy() : $today->copy();

        if ($start->gt($last) || $employees->isEmpty()) {
            return ['rows' => collect(), 'employees' => $employees, 'holidays' => $holidays];
        }

        $ids = $employees->pluck('id')->all();

        $attMap = [];
        $attendance = AbsensiKaryawan::whereIn('id_karyawan', $ids)->whereDate('tanggal', '>=', $start->toDateString())->whereDate('tanggal', '<=', $last->toDateString())->get();
        foreach ($attendance as $a) {
            $attMap[$a->id_karyawan][Carbon::parse($a->tanggal)->toDateString()] = $a;
        }

        $leave = $this->buildLeaveMap($ids, $start, $last);
        $izin = $this->buildIzinMap($ids, $start, $last);
        $todayStr = $today->toDateString();
        $afterWorkHours = now()->hour >= 17;

        $rows = [];

        foreach ($employees as $emp) {
            $from = $start->copy();
            $join = $this->startDateOf($emp);
            if ($join && $join->gt($from)) {
                $from = $join;
            }

            for ($d = $from->copy(); $d->lte($last); $d->addDay()) {
                $date = $d->toDateString();
                $absen = $attMap[$emp->id][$date] ?? null;
                $present = $this->isPresentRecord($absen);

                if (!$present && (isset($holidays[$date]) || !$this->isExpectedToWork($d, $emp->jabatan))) {
                    continue;
                }

                $ket = $this->classifyKeterangan($absen);

                $izinAlasan = $izin[$emp->id][$date] ?? null;
                if ($izinAlasan === null && $ket === 'izin') {
                    $izinAlasan = trim((string) $absen->keterangan) ?: '-';
                }
                $hasIzin = $izinAlasan !== null;
                $approved = $leave['approved'][$emp->id][$date] ?? null;
                $pending = $leave['pending'][$emp->id][$date] ?? null;

                $status = 'tidak_hadir';
                $sub = null;
                $alasan = null;
                $lateMinutes = 0;
                $lateNotified = false;

                if ($present) {
                    if ($this->isLateRecord($absen)) {
                        if ($hasIzin) {
                            $status = 'izin';
                            $alasan = $izinAlasan;
                            $lateNotified = true;
                        } else {
                            $status = 'telat';
                            $lateMinutes = $this->parseLateMinutes($absen->waktu_keterlambatan);
                        }
                    } else {
                        $status = 'hadir';
                    }
                } elseif ($approved) {
                    $status = $approved['tipe'];
                    $sub = $approved['tipe'] === 'cuti' ? ($approved['menikah'] ? 'menikah' : 'lainnya') : null;
                    $alasan = $approved['alasan'];
                } elseif ($ket === 'cuti' || $ket === 'sakit') {
                    $status = $ket;
                    $isMenikah = str_contains(strtolower((string) $absen->keterangan), 'menikah');
                    $sub = $ket === 'cuti' ? ($isMenikah ? 'menikah' : 'lainnya') : null;
                    $alasan = trim((string) $absen->keterangan);
                } elseif ($hasIzin) {
                    $status = 'izin';
                    $alasan = $izinAlasan;
                } elseif ($pending) {
                    $status = 'pending';
                    $sub = $pending['tipe'];
                    $alasan = $pending['alasan'];
                }

                if ($date === $todayStr && !$afterWorkHours && in_array($status, ['tidak_hadir', 'pending'])) {
                    continue;
                }

                $rows[] = [
                    'id' => $emp->id,
                    'divisi' => $emp->divisi ?: 'Tanpa Divisi',
                    'tanggal' => $date,
                    'status' => $status,
                    'sub' => $sub,
                    'alasan' => $alasan,
                    'has_izin' => $hasIzin,
                    'late_minutes' => $lateMinutes,
                    'late_notified' => $lateNotified,
                    'jam_masuk' => $absen?->jam_masuk,
                    'jam_keluar' => $absen?->jam_keluar,
                    'izin_alasan' => $izinAlasan,
                ];
            }
        }

        return ['rows' => collect($rows), 'employees' => $employees, 'holidays' => $holidays];
    }

    public function calculateLeaveRecap($rows, $employees)
    {
        $emp = $employees->keyBy('id');
        $recap = [];

        foreach ($rows->groupBy('id') as $id => $items) {
            $menikah = $items->where('status', 'cuti')->where('sub', 'menikah')->count();
            $lainnya = $items->where('status', 'cuti')->where('sub', 'lainnya')->count();
            $sakit = $items->where('status', 'sakit')->count();
            $izin = $items->where('has_izin', true)->count();
            $pending = $items->where('status', 'pending')->count();
            $total = $menikah + $lainnya + $sakit + $izin;

            if ($total + $pending === 0) {
                continue;
            }

            $details = [];
            foreach ($items->sortBy('tanggal') as $r) {
                if ($r['status'] === 'cuti') {
                    $details[] = ['tanggal' => $r['tanggal'], 'key' => 'cuti', 'jenis' => $r['sub'] === 'menikah' ? 'Cuti Menikah' : 'Cuti Lainnya', 'alasan' => $r['alasan'] ?: '-'];
                } elseif ($r['status'] === 'sakit') {
                    $details[] = ['tanggal' => $r['tanggal'], 'key' => 'sakit', 'jenis' => 'Sakit', 'alasan' => $r['alasan'] ?: '-'];
                } elseif ($r['status'] === 'pending') {
                    $details[] = ['tanggal' => $r['tanggal'], 'key' => 'pending', 'jenis' => ($r['sub'] === 'sakit' ? 'Sakit' : 'Cuti') . ' (Menunggu Persetujuan)', 'alasan' => $r['alasan'] ?: '-'];
                }
                if ($r['has_izin']) {
                    $details[] = ['tanggal' => $r['tanggal'], 'key' => 'izin', 'jenis' => 'Izin 3 Jam', 'alasan' => ($r['izin_alasan'] ?? $r['alasan']) ?: '-'];
                }
            }

            $e = $emp->get($id);
            $recap[] = [
                'id' => $id,
                'nama' => optional($e)->nama_lengkap,
                'divisi' => optional($e)->divisi,
                'jabatan' => optional($e)->jabatan,
                'foto' => optional($e)->foto,
                'cuti' => $menikah + $lainnya,
                'sakit' => $sakit,
                'izin' => $izin,
                'total' => $total,
                'pending' => $pending,
                'details' => $details,
            ];
        }

        return collect($recap)->sortByDesc('total')->values()->toArray();
    }
    public function calculateAttendanceSummary($rows, $employees, $recap)
    {
        $count = fn($s) => $rows->where('status', $s)->count();

        $hadir = $count('hadir');
        $telat = $count('telat');
        $cuti = $count('cuti');
        $sakit = $count('sakit');
        $izin = $count('izin');
        $pending = $count('pending');
        $tidakHadir = $count('tidak_hadir');

        $total = $rows->count();
        $denominator = $total - $cuti - $sakit;
        $attended = $hadir + $telat + $izin;
        $totalLate = $rows->where('status', '%LIKE%', ['telat', 'Telat'])->sum('late_minutes');
        $avgLate = $this->averageLatePerEmployee($rows);

        return [
            'total_hari_kerja' => $rows->pluck('tanggal')->unique()->count(),
            'total_karyawan' => $employees->count(),
            'total_absen' => $hadir + $telat,
            'hadir' => $hadir,
            'telat' => $telat,
            'cuti' => $cuti,
            'sakit' => $sakit,
            'izin' => $izin,
            'pending' => $pending,
            'tidak_hadir' => $tidakHadir,
            'cuti_sakit' => $cuti + $sakit,
            'attendance_rate' => $denominator > 0 ? round(($attended / $denominator) * 100, 1) : 0,
            'punctuality_rate' => $hadir + $telat > 0 ? round(($hadir / ($hadir + $telat)) * 100, 1) : 100,
            'avg_late_minutes' => $telat > 0 ? round($totalLate / $telat, 1) : 0,
            'total_late_minutes' => $avgLate,
            'total_cuti_sakit_izin' => collect($recap)->where('total', '>', 0)->count(),
        ];
    }

    public function calculateDivisionLateness($rows)
    {
        return $rows->groupBy('divisi')->map(function ($items, $divisi) {
            $telat = $items->where('status', 'telat')->count();
            $attended = $items->whereIn('status', ['hadir', 'telat'])->count();
            return [
                'divisi' => $divisi,
                'late_count' => $telat,
                'attended' => $attended,
                'late_percentage' => $attended > 0 ? round($telat / $attended * 100, 1) : 0,
                'avg_late_minutes' => $this->averageLatePerEmployee($items),
            ];
        })->filter(fn($d) => $d['attended'] > 0)->sortByDesc('late_percentage')->values()->toArray();
    }

    public function calculateLeaveBreakdown($rows)
    {
        $labels = [
            'hadir' => 'Hadir',
            'cuti' => 'Cuti',
            'sakit' => 'Sakit',
            'izin' => 'Izin 3 Jam',
        ];
        $leaveKeys = ['cuti', 'sakit', 'izin'];

        $sumPct = array_fill_keys(array_keys($labels), 0);
        $dayCount = array_fill_keys(array_keys($labels), 0);
        $sumHead = array_fill_keys(array_keys($labels), 0);
        $totalDays = 0;

        foreach ($rows->groupBy('tanggal') as $items) {
            $total = $items->count();
            $totalDays++;

            $c = [
                'hadir' => $items->whereIn('status', ['hadir', 'telat'])->count(), // telat = hadir
                'cuti' => $items->where('status', 'cuti')->count(),
                'sakit' => $items->where('status', 'sakit')->count(),
                'izin' => $items->where('status', 'izin')->count(),
            ];
            $leaveTotal = $c['cuti'] + $c['sakit'] + $c['izin'];

            foreach ($labels as $k => $_) {
                // Cuti/sakit/izin: dikurangi dua kategori cuti lainnya
                // Hadir: dikurangi seluruh cuti, sakit, dan izin
                $excluded = in_array($k, $leaveKeys, true) ? $leaveTotal - $c[$k] : $leaveTotal;
                $den = $total - $excluded;

                $sumHead[$k] += $c[$k];
                if ($den > 0) {
                    $sumPct[$k] += $c[$k] / $den * 100;
                    $dayCount[$k]++;
                }
            }
        }

        $items = [];
        foreach ($labels as $k => $label) {
            $items[] = [
                'key' => $k,
                'label' => $label,
                'count' => $sumHead[$k],
                'avg_per_day' => $totalDays > 0 ? round($sumHead[$k] / $totalDays, 1) : 0,
                'percentage' => $dayCount[$k] > 0 ? round($sumPct[$k] / $dayCount[$k], 1) : 0,
            ];
        }

        return [
            'total' => $rows->count(),
            'total_days' => $totalDays,
            'items' => $items,
        ];
    }

    public function calculateDivisionAttendance($rows)
    {
        return $rows
            ->groupBy('divisi')
            ->map(function ($items, $divisi) {
                $total = max(1, $items->count());
                $pct = fn($n) => round(($n / $total) * 100, 1);
                return [
                    'divisi' => $divisi,
                    'hadir' => $pct($items->where('status', 'hadir')->count()),
                    'terlambat' => $pct($items->where('status', 'telat')->count()),
                    'izin' => $pct($items->where('has_izin', true)->count()),
                    'sakit' => $pct($items->where('status', 'sakit')->count()),
                ];
            })
            ->sortBy('divisi')
            ->values()
            ->toArray();
    }

    public function calculateLateNotification($rows)
    {
        $classify = function ($pct) {
            if ($pct === null) return 'none';
            if ($pct > 70) return 'warning';
            if ($pct > 50) return 'warning';
            return 'safe';
        };

        $latePct = function ($items) {
            $sum = 0;
            $days = 0;
            foreach ($items->groupBy('tanggal') as $day) {
                $den = $day->count() - $day->whereIn('status', ['cuti', 'sakit', 'izin'])->count();
                if ($den > 0) {
                    $sum += $day->where('status', 'telat')->count() / $den * 100;
                    $days++;
                }
            }
            return $days > 0 ? round($sum / $days, 1) : null;
        };

        $level = function ($pct) {
            if ($pct === null) return 'none';
            if ($pct > 70) return 'danger';
            if ($pct > 50) return 'warning
            ';
            return 'safe';
        };

        $overallPct = $latePct($rows);

        $divisions = $rows->groupBy('divisi')
            ->map(function ($items, $divisi) use ($latePct, $level) {
                $pct = $latePct($items);
                return [
                    'divisi' => $divisi,
                    'percentage' => $pct,
                    'status' => $level($pct),
                    'late_count' => $items->where('status', 'telat')->count(),
                ];
            })
            ->filter(fn($d) => in_array($d['status'], ['danger', 'warning'], true))
            ->sortByDesc('percentage')
            ->values()
            ->toArray();

        return [
            'percentage' => $overallPct,
            'status' => $level($overallPct),
            'total_late' => $rows->where('status', 'telat')->count(),
            'divisions' => $divisions,
            'thresholds' => ['danger' => 70, 'warning' => 50],
        ];
    }
    
    public function generateAttendancePredictions($summary)
    {
        $currentRate = $summary['attendance_rate'];
        $projections = [
            'next_month' => ['estimated_rate' => min(99.5, round($currentRate + rand(-2, 3), 1)), 'confidence' => 'medium', 'factors' => ['Musim liburan', 'Event perusahaan', 'Kebijakan WFH']],
            'next_quarter' => ['estimated_rate' => min(98, round($currentRate + rand(-3, 4), 1)), 'confidence' => 'low', 'factors' => ['Perubahan kebijakan', 'Turnover karyawan', 'Kondisi ekonomi']],
            'next_year' => ['estimated_rate' => min(97, round($currentRate + rand(-5, 5), 1)), 'confidence' => 'very_low', 'factors' => ['Transformasi digital', 'Generasi workforce baru', 'Regulasi ketenagakerjaan']],
        ];
        $milestones = [['target' => 95, 'timeline' => '3 bulan', 'actions' => ['Reminder otomatis', 'Flexible time window 15 menit']], ['target' => 97, 'timeline' => '6 bulan', 'actions' => ['Program wellness', 'Transport allowance review']], ['target' => 99, 'timeline' => '12 bulan', 'actions' => ['AI-based predictive scheduling', 'Holistic work-life integration']]];
        return [
            'current_rate' => $currentRate,
            'projections' => $projections,
            'milestones' => $milestones,
            'key_drivers' => [
                'positive' => ['Budaya disiplin', 'Sistem reward', 'Komunikasi transparan'],
                'negative' => ['Transportasi tidak memadai', 'Workload berlebihan', 'Kesehatan karyawan'],
            ],
        ];
    }

    public function generateOpportunities($summary, $notification)
    {
        $opportunities = [];

        if ($summary['attendance_rate'] < 90) {
            $opportunities[] = ['level' => 'operational', 'priority' => 'high', 'title' => 'Optimasi Sistem Reminder', 'description' => 'Implementasi notifikasi H-1 dan H0 untuk meningkatkan kesadaran kehadiran', 'impact' => '+5-8% attendance rate', 'effort' => 'low', 'timeline' => '2-4 minggu'];
        }
        if ($summary['tidak_hadir'] > 0) {
            $opportunities[] = ['level' => 'operational', 'priority' => 'high', 'title' => 'Konfirmasi Ketidakhadiran Tanpa Keterangan', 'description' => $summary['tidak_hadir'] . ' hari kerja belum memiliki cuti, sakit, atau izin. Minta karyawan atau atasan melengkapi pengajuan.', 'impact' => 'Data kehadiran lebih akurat', 'effort' => 'low', 'timeline' => '1 minggu'];
        }
        if ($summary['pending'] > 0) {
            $opportunities[] = ['level' => 'tactical', 'priority' => 'medium', 'title' => 'Percepat Persetujuan Cuti', 'description' => $summary['pending'] . ' hari kerja masih menunggu persetujuan manager.', 'impact' => 'Tidak ada pengajuan menggantung', 'effort' => 'low', 'timeline' => '1 minggu'];
        }
        if ($summary['avg_late_minutes'] > 15) {
            $opportunities[] = ['level' => 'tactical', 'priority' => 'medium', 'title' => 'Flexible Time Window', 'description' => 'Berikan toleransi 15-30 menit untuk jam masuk tanpa penalty', 'impact' => '-40% late complaints', 'effort' => 'medium', 'timeline' => '1-2 bulan'];
        }
        if (in_array($notification['status'], ['danger', 'warning'])) {
            $opportunities[] = ['level' => 'strategic', 'priority' => $notification['status'] === 'danger' ? 'high' : 'medium', 'title' => 'Disiplin Pemberitahuan Keterlambatan', 'description' => 'Hanya ' . $notification['percentage'] . '% keterlambatan yang diberitahukan lewat izin. Dorong karyawan mengajukan izin sebelum terlambat.', 'impact' => 'Keterlambatan terkomunikasi', 'effort' => 'medium', 'timeline' => '1-2 bulan'];
        }
        $opportunities[] = ['level' => 'transformational', 'priority' => 'low', 'title' => 'AI-Powered Attendance Intelligence', 'description' => 'Predictive analytics untuk antisipasi pola absensi dan optimalisasi shift', 'impact' => '+15% operational efficiency', 'effort' => 'very_high', 'timeline' => '6-12 bulan'];

        return collect($opportunities)->sortByDesc(fn($o) => (['high' => 4, 'medium' => 3, 'low' => 2][$o['priority']] ?? 1) * 10 - ($o['effort'] === 'low' ? 0 : 5))->values()->toArray();
    }

    public function buildAnalytics(Request $request)
    {
        $period = $this->resolvePeriod($request);
        $divisi = $request->input('divisi', 'all');

        $this->ensureHolidays($period['start'], $period['end']);

        $data = $this->buildDailyStatus($period['start'], $period['end'], $divisi);
        $rows = $data['rows'];

        $recap = $this->calculateLeaveRecap($rows, $data['employees']);
        $summary = $this->calculateAttendanceSummary($rows, $data['employees'], $recap);
        $notification = $this->calculateLateNotification($rows);

        $summary['leave_recap'] = $recap;

        $activeEmployees = $this->getActiveKaryawanQuery()
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap', 'divisi']);

        return [
            'success' => true,
            'summary' => $summary,
            'charts' => [
                'late_division' => $this->calculateDivisionLateness($rows),
                'leave_breakdown' => $this->calculateLeaveBreakdown($rows),
                'division_attendance' => $this->calculateDivisionAttendance($rows),
                'late_notification' => $notification,
            ],
            'predictions' => $this->generateAttendancePredictions($summary),
            'opportunities' => $this->generateOpportunities($summary, $notification),
            'period' => [
                'type' => $period['type'],
                'month' => $period['month'],
                'year' => $period['year'],
                'quarter' => $period['quarter'],
                'display' => $period['label'],
                'start' => $period['start']->toDateString(),
                'end' => $period['end']->toDateString(),
            ],
            'filters' => [
                'divisions' => $activeEmployees->pluck('divisi')->filter()->unique()->sort()->values()->toArray(),
                'employees' => $activeEmployees->map(fn($e) => ['id' => $e->id, 'nama' => $e->nama_lengkap])->values()->toArray(),
            ],
        ];
    }

    public function getAttendanceAnalytics(Request $request)
    {
        try {
            return response()->json($this->buildAnalytics($request));
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Gagal memuat analytics: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function getAttendanceCalendar(Request $request)
    {
        try {
            $bulan = (int) $request->input('month', now()->month);
            $tahun = (int) $request->input('year', now()->year);
            $divisi = $request->input('divisi', 'all');
            $idKaryawan = $request->input('id_karyawan');

            $start = Carbon::create($tahun, $bulan, 1)->startOfDay();
            $end = $start->copy()->endOfMonth()->startOfDay();

            $this->ensureHolidays($start, $end);

            $data = $this->buildDailyStatus($start, $end, $divisi, $idKaryawan);
            $byDate = $data['rows']->groupBy('tanggal');
            $holidays = $data['holidays'];
            $today = now()->startOfDay();

            $calendar = [];
            $totals = ['telat' => 0, 'cuti' => 0, 'sakit' => 0, 'izin' => 0, 'pending' => 0, 'tidak_hadir' => 0, 'libur' => 0, 'weekend' => 0];

            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $dateStr = $d->toDateString();
                $items = $byDate->get($dateStr, collect());
                $isHoliday = isset($holidays[$dateStr]);
                $isWeekend = $d->isWeekend();

                $counts = [
                    'telat' => $items->where('status', 'telat')->count(),
                    'cuti' => $items->where('status', 'cuti')->count(),
                    'sakit' => $items->where('status', 'sakit')->count(),
                    'izin' => $items->where('has_izin', true)->count(),
                    'pending' => $items->where('status', 'pending')->count(),
                    'tidak_hadir' => $items->where('status', 'tidak_hadir')->count(),
                ];

                foreach ($counts as $k => $v) {
                    $totals[$k] += $v;
                }
                if ($isHoliday) {
                    $totals['libur']++;
                } elseif ($isWeekend) {
                    $totals['weekend']++;
                }

                $calendar[] = [
                    'date' => $dateStr,
                    'day' => $d->day,
                    'is_weekend' => $isWeekend,
                    'is_holiday' => $isHoliday,
                    'holiday_name' => $holidays[$dateStr] ?? null,
                    'is_future' => $d->gt($today),
                    'counts' => $counts,
                ];
            }

            return response()->json([
                'success' => true,
                'calendar' => $calendar,
                'totals' => $totals,
                'month' => $bulan,
                'year' => $tahun,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Gagal memuat kalender: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function getDailyAttendanceDetails(Request $request)
    {
        try {
            $tanggal = $request->input('date');

            if (!$tanggal) {
                return response()->json(['success' => false, 'message' => 'Tanggal tidak valid'], 400);
            }

            $date = Carbon::parse($tanggal)->startOfDay();
            $this->ensureHolidays($date, $date);

            $data = $this->buildDailyStatus($date, $date);
            $employees = $data['employees']->keyBy('id');
            $holidayNames = array_values($data['holidays']);

            $lists = ['present' => [], 'late' => [], 'on_leave' => [], 'sick' => [], 'izin' => [], 'pending' => [], 'absent' => []];

            foreach ($data['rows'] as $r) {
                $e = $employees->get($r['id']);
                if (!$e) {
                    continue;
                }

                $item = [
                    'id' => $e->id,
                    'nama' => $e->nama_lengkap,
                    'jabatan' => $e->jabatan,
                    'divisi' => $e->divisi,
                    'foto' => $e->foto,
                    'jam_masuk' => $r['jam_masuk'],
                    'late_minutes' => $r['late_minutes'],
                    'label' => '',
                    'catatan' => $r['alasan'] ?? '',
                ];

                switch ($r['status']) {
                    case 'hadir':
                        $lists['present'][] = $item;
                        break;
                    case 'telat':
                        $lists['late'][] = $item;
                        break;
                    case 'cuti':
                        $item['label'] = $r['sub'] === 'menikah' ? 'Cuti Menikah' : 'Cuti Lainnya';
                        $lists['on_leave'][] = $item;
                        break;
                    case 'sakit':
                        $item['label'] = 'Sakit';
                        $lists['sick'][] = $item;
                        break;
                    case 'izin':
                        $item['label'] = 'Izin 3 Jam';
                        if ($r['late_notified']) {
                            $item['catatan'] = 'Terlambat, sudah ada izin. ' . $item['catatan'];
                        }
                        $lists['izin'][] = $item;
                        break;
                    case 'pending':
                        $item['label'] = ($r['sub'] === 'sakit' ? 'Sakit' : 'Cuti') . ' menunggu persetujuan';
                        $lists['pending'][] = $item;
                        break;
                    default:
                        $item['label'] = 'Belum ada keterangan';
                        $lists['absent'][] = $item;
                }
            }

            $total = $data['rows']->count();
            $present = count($lists['present']);
            $late = count($lists['late']);

            $statistics = [
                'total' => $total,
                'present' => $present,
                'late' => $late,
                'on_leave' => count($lists['on_leave']),
                'sick' => count($lists['sick']),
                'izin' => count($lists['izin']),
                'pending' => count($lists['pending']),
                'absent' => count($lists['absent']),
            ];

            return response()->json([
                'success' => true,
                'date' => $date->translatedFormat('d F Y'),
                'day_name' => $date->translatedFormat('l'),
                'is_weekend' => $date->isWeekend(),
                'is_holiday' => count($holidayNames) > 0,
                'holiday_names' => $holidayNames,
                'statistics' => $statistics,
                'employees' => $lists,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Gagal memuat detail: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function getDivisionDailyStats(Request $request)
    {
        $period = $this->resolvePeriod($request);
        $this->ensureHolidays($period['start'], $period['end']);

        $data = $this->buildDailyStatus($period['start'], $period['end'], $request->input('divisi', 'all'));

        $result = $data['rows']
            ->groupBy('tanggal')
            ->map(function ($items, $date) {
                $divisions = $items->groupBy('divisi')->map(function ($g) {
                    $total = $g->count();
                    $attended = $g->whereIn('status', ['hadir', 'telat', 'izin'])->count();
                    $onLeave = $g->whereIn('status', ['cuti', 'sakit'])->count();
                    return [
                        'total' => $total,
                        'hadir' => $g->where('status', 'hadir')->count(),
                        'telat' => $g->where('status', 'telat')->count(),
                        'cuti' => $g->where('status', 'cuti')->count(),
                        'sakit' => $g->where('status', 'sakit')->count(),
                        'izin' => $g->where('has_izin', true)->count(),
                        'pending' => $g->where('status', 'pending')->count(),
                        'tidak_hadir' => $g->where('status', 'tidak_hadir')->count(),
                        'rate' => $total - $onLeave > 0 ? round(($attended / ($total - $onLeave)) * 100, 1) : 100,
                    ];
                });
                return ['date' => $date, 'day' => (int) substr($date, 8, 2), 'divisions' => $divisions];
            })
            ->sortKeys()
            ->values();

        return response()->json(['success' => true, 'data' => $result]);
    }

    public function getTopLateEmployees(Request $request)
    {
        $period = $this->resolvePeriod($request);
        $limit = (int) $request->input('limit', 10);

        $this->ensureHolidays($period['start'], $period['end']);

        $data = $this->buildDailyStatus($period['start'], $period['end'], $request->input('divisi', 'all'));
        $employees = $data['employees']->keyBy('id');

        $top = $data['rows']
            ->where('status', 'telat')
            ->groupBy('id')
            ->map(function ($items, $id) use ($employees) {
                $e = $employees->get($id);
                $minutes = $items->sum('late_minutes');
                $count = $items->count();
                return [
                    'id' => $id,
                    'nama' => optional($e)->nama_lengkap,
                    'jabatan' => optional($e)->jabatan,
                    'divisi' => optional($e)->divisi,
                    'foto' => optional($e)->foto,
                    'late_count' => $count,
                    'total_late_minutes' => round($minutes),
                    'avg_late_minutes' => $count > 0 ? round($minutes / $count, 1) : 0,
                ];
            })
            ->sortByDesc('total_late_minutes')
            ->take($limit)
            ->values();

        return response()->json(['success' => true, 'data' => $top]);
    }

    public function buildDailyMatrix($data, $period)
    {
        $daysInMonth = $period['start']->daysInMonth;
        $headers = ['Nama', 'Divisi', 'Jabatan'];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $headers[] = $d;
        }
        array_push($headers, 'Total Hadir', 'Total Telat', 'Total Cuti', 'Total Sakit', 'Total Izin', 'Total Menunggu', 'Total Tanpa Keterangan');

        $codes = ['hadir' => 'H', 'telat' => 'L', 'cuti' => 'C', 'sakit' => 'S', 'izin' => 'I', 'pending' => 'P', 'tidak_hadir' => 'T'];
        $byEmp = $data['rows']->groupBy('id')->map(fn($g) => $g->keyBy('tanggal'));

        $rows = [];
        foreach ($data['employees'] as $emp) {
            $mine = $byEmp->get($emp->id, collect());
            $row = [$emp->nama_lengkap, $emp->divisi ?? '-', $emp->jabatan ?? '-'];

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $tanggal = $period['start']->copy()->day($d);
                $dateStr = $tanggal->toDateString();
                $r = $mine->get($dateStr);

                if ($r) {
                    $row[] = $codes[$r['status']];
                } elseif (isset($data['holidays'][$dateStr])) {
                    $row[] = 'B';
                } elseif (!$this->isExpectedToWork($tanggal, $emp->jabatan)) {
                    $row[] = 'X';
                } else {
                    $row[] = '-';
                }
            }

            $row[] = $mine->where('status', 'hadir')->count();
            $row[] = $mine->where('status', 'telat')->count();
            $row[] = $mine->where('status', 'cuti')->count();
            $row[] = $mine->where('status', 'sakit')->count();
            $row[] = $mine->where('has_izin', true)->count();
            $row[] = $mine->where('status', 'pending')->count();
            $row[] = $mine->where('status', 'tidak_hadir')->count();
            $rows[] = $row;
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    public function buildRecapMatrix($data)
    {
        $headers = ['Nama', 'Divisi', 'Jabatan', 'Hadir', 'Telat', 'Rata-rata Telat (menit)', 'Cuti Menikah', 'Cuti Lainnya', 'Sakit', 'Izin', 'Menunggu Persetujuan', 'Tanpa Keterangan'];
        $byEmp = $data['rows']->groupBy('id');

        $rows = [];
        foreach ($data['employees'] as $emp) {
            $mine = $byEmp->get($emp->id, collect());
            $late = $mine->where('status', 'telat');
            $telat = $late->count();
            $rows[] = [$emp->nama_lengkap, $emp->divisi ?? '-', $emp->jabatan ?? '-', $mine->where('status', 'hadir')->count(), $telat, $telat > 0 ? round($late->sum('late_minutes') / $telat, 1) : 0, $mine->where('status', 'cuti')->where('sub', 'menikah')->count(), $mine->where('status', 'cuti')->where('sub', 'lainnya')->count(), $mine->where('status', 'sakit')->count(), $mine->where('has_izin', true)->count(), $mine->where('status', 'pending')->count(), $mine->where('status', 'tidak_hadir')->count()];
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    public function buildAttendanceMatrix(Request $request)
    {
        $period = $this->resolvePeriod($request);
        $this->ensureHolidays($period['start'], $period['end']);

        $data = $this->buildDailyStatus($period['start'], $period['end'], $request->input('divisi', 'all'));

        if ($period['type'] === 'month') {
            return $this->buildDailyMatrix($data, $period);
        }
        return $this->buildRecapMatrix($data);
    }

    public function exportAttendanceReport(Request $request)
    {
        $format = $request->input('format', 'pdf');
        $period = $this->resolvePeriod($request);
        $analytics = $this->buildAnalytics($request);
        $matrix = $this->buildAttendanceMatrix($request);

        if ($format === 'csv') {
            return $this->exportAttendanceExcel($analytics, $matrix, $period);
        }
        return $this->exportAttendancePdf($analytics, $matrix, $period);
    }

    public function exportAttendanceExcel($analytics, $matrix, $period)
    {
        $isDaily = $period['type'] === 'month';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Attendance');

        $sheet->mergeCells('A1:C1');
        $sheet->setCellValue('A1', 'LAPORAN KEHADIRAN KARYAWAN');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:C2');
        $sheet->setCellValue('A2', 'Periode: ' . $period['label']);

        $sheet->mergeCells('A3:C3');
        $sheet->setCellValue('A3', 'Tipe: ' . ($isDaily ? 'HARIAN' : 'REKAP'));

        $sheet->mergeCells('A4:C4');
        $sheet->setCellValue('A4', 'Dicetak: ' . now()->format('d F Y H:i'));
        $sheet->getRowDimension(4)->setRowHeight(15);
        $sheet->getRowDimension(5)->setRowHeight(5);

        $col = 1;
        foreach ($matrix['headers'] as $header) {
            $sheet->getCellByColumnAndRow($col, 6)->setValue($header);
            $style = $sheet->getStyleByColumnAndRow($col, 6);
            $style->getFont()->setBold(true)->setSize(8);
            $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF2C3E50');
            $style->getFont()->getColor()->setARGB('FFFFFFFF');
            $style->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            if ($col === 1) {
                $sheet->getColumnDimension('A')->setWidth(22);
            } elseif ($col === 2 || $col === 3) {
                $sheet->getColumnDimensionByColumn($col)->setWidth(18);
            } elseif (is_int($header)) {
                $sheet->getColumnDimensionByColumn($col)->setWidth(2.8);
            } else {
                $sheet->getColumnDimensionByColumn($col)->setWidth(13);
            }
            $col++;
        }
        $sheet->getRowDimension(6)->setRowHeight($isDaily ? 30 : 36);

        $display = ['H' => '✓', 'L' => 'L', 'C' => 'C', 'S' => 'S', 'I' => 'I', 'P' => 'P', 'T' => 'T', 'B' => 'B', 'X' => '•', '-' => ''];
        $fills = ['L' => 'FFFEE2E2', 'C' => 'FFE0F2FE', 'S' => 'FFEDE9FE', 'I' => 'FFCCFBF1', 'P' => 'FFFFEDD5', 'T' => 'FFE5E7EB', 'B' => 'FFFEF3C7', 'X' => 'FFF3F4F6'];
        $fontColors = ['L' => 'FFDC2626', 'C' => 'FF0284C7', 'S' => 'FF7C3AED', 'I' => 'FF0D9488', 'P' => 'FFF97316', 'T' => 'FF374151', 'B' => 'FFD97706', 'X' => 'FF999999'];

        $startRow = 7;
        foreach ($matrix['rows'] as $rIdx => $dataRow) {
            $row = $startRow + $rIdx;
            $col = 1;
            foreach ($dataRow as $cIdx => $cellValue) {
                $isDayCell = $isDaily && $col > 3 && is_int($matrix['headers'][$cIdx] ?? null);
                $shown = $isDayCell && is_string($cellValue) ? $display[$cellValue] ?? $cellValue : $cellValue;

                $sheet->getCellByColumnAndRow($col, $row)->setValue($shown);
                $style = $sheet->getStyleByColumnAndRow($col, $row);
                $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $style->getFont()->setSize(8);
                $style->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                if ($col <= 3) {
                    $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    if ($col === 1) {
                        $style->getFont()->setBold(true);
                    }
                }

                if ($isDayCell && isset($fills[$cellValue])) {
                    $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($fills[$cellValue]);
                    $style->getFont()->getColor()->setARGB($fontColors[$cellValue]);
                    if (in_array($cellValue, ['L', 'T'])) {
                        $style->getFont()->setBold(true);
                    }
                }

                $col++;
            }
            $sheet->getRowDimension($row)->setRowHeight(18);
        }

        if ($isDaily) {
            $legendRow = $startRow + count($matrix['rows']) + 2;
            $sheet->setCellValue("A{$legendRow}", 'KETERANGAN:');
            $sheet
                ->getStyle("A{$legendRow}")
                ->getFont()
                ->setBold(true)
                ->setSize(9);

            $legend = [['✓', 'Hadir tepat waktu'], ['L', 'Terlambat'], ['C', 'Cuti disetujui'], ['S', 'Sakit disetujui'], ['I', 'Izin 3 jam disetujui'], ['P', 'Cuti/sakit menunggu persetujuan'], ['T', 'Tidak hadir tanpa keterangan'], ['B', 'Libur nasional'], ['•', 'Akhir pekan']];
            foreach ($legend as $i => $item) {
                $sheet->getCellByColumnAndRow(1, $legendRow + 1 + $i)->setValue($item[0]);
                $sheet->getCellByColumnAndRow(2, $legendRow + 1 + $i)->setValue($item[1]);
                $sheet
                    ->getStyleByColumnAndRow(1, $legendRow + 1 + $i)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        }

        $sheet->freezePane('D7');
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->getPageMargins()->setTop(0.5)->setRight(0.3)->setBottom(0.5)->setLeft(0.3);
        $sheet->getPageSetup()->setHorizontalCentered(true);

        $filename = 'Laporan_Attendance_' . Str::slug($period['label'], '_') . '.xlsx';
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0',
        ];

        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        return response()->stream(fn() => ob_end_flush(), 200, $headers);
    }

    public function exportAttendancePdf($analytics, $matrix, $period)
    {
        $pdf = Pdf::loadView('office.HR.exports.attendance_pdf', [
            'analytics' => $analytics,
            'period' => $analytics['period']['display'],
            'generated_at' => now()->format('d F Y H:i'),
            'matrix' => $matrix,
            'periode_type' => $period['type'],
        ])
            ->setPaper('a4', 'landscape')
            ->setOption('margin_top', 15)
            ->setOption('margin_bottom', 15)
            ->setOption('margin_left', 10)
            ->setOption('margin_right', 10);

        return $pdf->download('Laporan_Attendance_' . Str::slug($period['label'], '_') . '.pdf');
    }
}
