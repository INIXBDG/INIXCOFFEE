<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\karyawan;
use App\Models\AbsensiKaryawan;
use App\Models\SuratPerjalanan;
use App\Models\Pelamar;
use App\Models\pengajuancuti;
use App\Models\izinTigaJam;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HRController extends Controller
{
    public function index()
    {
        return view('HR.dashboard');
    }

    public function getDashboardData(Request $request)
    {
        try {
            Carbon::setLocale('id');
            $tahun = $request->input('tahun', date('Y'));
            $divisi = $request->input('divisi', 'all');

            $employeeComposition = $this->getEmployeeComposition($divisi);
            $attendanceSnapshot = $this->getAttendanceSnapshot($divisi);
            $recruitmentFunnel = $this->getRecruitmentFunnel();
            $financialOverview = $this->getFinancialOverview($tahun, $divisi);
            $divisionDistribution = $this->getDivisionDistribution($divisi);
            $todayDigest = $this->getTodayDigest($divisi);
            $recentHires = $this->getRecentHires($divisi, 5);
            $recentResigns = $this->getRecentResigns($divisi, 5);
            $topSPJDivisions = $this->getTopSPJDivisions($tahun, $divisi);
            $monthlyAttendanceTrend = $this->getMonthlyAttendanceTrend($tahun, $divisi);
            $upcomingEvents = $this->getUpcomingEvents();
            $insights = $this->generateInsights($tahun, $divisi, $employeeComposition, $attendanceSnapshot, $financialOverview);

            return response()->json([
                'success' => true,
                'header' => [
                    'greeting' => $this->getGreeting(),
                    'today' => Carbon::now()->translatedFormat('l, d F Y'),
                    'time' => Carbon::now()->format('H:i'),
                    'year' => $tahun,
                ],
                'composition' => $employeeComposition,
                'attendance' => $attendanceSnapshot,
                'recruitment' => $recruitmentFunnel,
                'financial' => $financialOverview,
                'divisions' => $divisionDistribution,
                'today' => $todayDigest,
                'recent_hires' => $recentHires,
                'recent_resigns' => $recentResigns,
                'top_spj' => $topSPJDivisions,
                'attendance_trend' => $monthlyAttendanceTrend,
                'upcoming' => $upcomingEvents,
                'insights' => $insights,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function getGreeting()
    {
        $hour = now()->hour;
        if ($hour < 11) return ['text' => 'Selamat Pagi', 'emoji' => '☀️', 'sub' => 'Mari mulai hari dengan produktif'];
        if ($hour < 15) return ['text' => 'Selamat Siang', 'emoji' => '🌤️', 'sub' => 'Waktunya review progress'];
        if ($hour < 18) return ['text' => 'Selamat Sore', 'emoji' => '🌅', 'sub' => 'Saatnya wrap-up harian'];
        return ['text' => 'Selamat Malam', 'emoji' => '🌙', 'sub' => 'Ringkasan hari ini siap dilihat'];
    }

    private function baseKaryawanQuery($divisi, $activeOnly = true)
    {
        return karyawan::query()
            ->when($activeOnly, fn($q) => $q->where('status_aktif', '1'))
            ->whereNot('jabatan', 'Outsource')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->whereNotNull('nip')
            ->whereNot('divisi', 'Direksi')
            ->when($divisi !== 'all', fn($q) => $q->where('divisi', $divisi));
    }

    private function getEmployeeComposition($divisi)
    {
        $total = $this->baseKaryawanQuery($divisi)->count();

        $tetap = (clone $this->baseKaryawanQuery($divisi))->whereNotNull('awal_tetap')->count();
        $kontrak = (clone $this->baseKaryawanQuery($divisi))->whereNotNull('awal_kontrak')->whereNull('awal_tetap')->count();
        $probation = (clone $this->baseKaryawanQuery($divisi))->whereNotNull('awal_probation')->whereNull('awal_kontrak')->whereNull('awal_tetap')->count();

        $tahunIni = now()->year;
        $newThisYear = (clone $this->baseKaryawanQuery($divisi))->whereYear('awal_probation', $tahunIni)->count();
        $resignThisYear = karyawan::where('status_aktif', '0')
            ->when($divisi !== 'all', fn($q) => $q->where('divisi', $divisi))
            ->whereYear('resigned_at', $tahunIni)
            ->count();

        $retentionRate = ($total + $resignThisYear) > 0
            ? round(($total / ($total + $resignThisYear)) * 100, 1)
            : 100;

        $avgTenure = $this->baseKaryawanQuery($divisi)->get(['awal_probation', 'awal_kontrak', 'awal_tetap'])
            ->map(function ($e) {
                $start = $e->awal_probation ?? $e->awal_kontrak ?? $e->awal_tetap;
                return $start ? Carbon::parse($start)->diffInMonths(now()) : 0;
            });
        $avgTenureMonths = $avgTenure->count() > 0 ? round($avgTenure->avg(), 1) : 0;

        return [
            'total' => $total,
            'tetap' => $tetap,
            'kontrak' => $kontrak,
            'probation' => $probation,
            'new_this_year' => $newThisYear,
            'resign_this_year' => $resignThisYear,
            'retention_rate' => $retentionRate,
            'avg_tenure_months' => $avgTenureMonths,
            'composition_chart' => [
                'labels' => ['Tetap', 'Kontrak', 'Probation'],
                'data' => [$tetap, $kontrak, $probation],
                'colors' => ['#059669', '#0284c7', '#f59e0b'],
            ],
        ];
    }

    private function getAttendanceSnapshot($divisi)
    {
        $bulanIni = now()->month;
        $tahun = now()->year;

        $absensi = AbsensiKaryawan::whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahun)
            ->when($divisi !== 'all', fn($q) => $q->whereHas('karyawan', fn($s) => $s->where('divisi', $divisi)))
            ->with('karyawan:id,nama_lengkap')
            ->get();

        $totalKaryawan = $this->baseKaryawanQuery($divisi)->count();
        $hariKerja = $this->countWorkingDays($bulanIni, $tahun);
        $expected = $totalKaryawan * $hariKerja;

        $isLate = fn($a) => str_starts_with(trim((string) ($a->keterangan ?? '')), 'Telat');
        $isPresent = fn($a) => $a->jam_masuk || str_starts_with(strtolower(trim((string) ($a->keterangan ?? ''))), 'masuk');

        $hadir = $absensi->filter(fn($a) => $isPresent($a) && !$isLate($a))->count();
        $telat = $absensi->filter(fn($a) => $isLate($a))->count();
        $totalHadir = $hadir + $telat;

        $attendanceRate = $expected > 0 ? round(($totalHadir / $expected) * 100, 1) : 0;
        $punctualityRate = $totalHadir > 0 ? round(($hadir / $totalHadir) * 100, 1) : 100;

        $totalDetikTelat = $absensi->filter(fn($a) => $isLate($a))->sum(function ($a) {
            if (!$a->waktu_keterlambatan) return 0;
            $p = explode(':', $a->waktu_keterlambatan);
            return ($p[0] ?? 0) * 3600 + ($p[1] ?? 0) * 60 + ($p[2] ?? 0);
        });
        $avgLateMinutes = $telat > 0 ? round($totalDetikTelat / $telat / 60, 1) : 0;

        $bulanLalu = $bulanIni == 1 ? 12 : $bulanIni - 1;
        $tahunLalu = $bulanIni == 1 ? $tahun - 1 : $tahun;
        $absensiLalu = AbsensiKaryawan::whereMonth('tanggal', $bulanLalu)
            ->whereYear('tanggal', $tahunLalu)
            ->when($divisi !== 'all', fn($q) => $q->whereHas('karyawan', fn($s) => $s->where('divisi', $divisi)))
            ->count();

        return [
            'total_hadir' => $totalHadir,
            'hadir_tepat' => $hadir,
            'telat' => $telat,
            'attendance_rate' => $attendanceRate,
            'punctuality_rate' => $punctualityRate,
            'avg_late_minutes' => $avgLateMinutes,
            'total_late_minutes' => round($totalDetikTelat / 60),
            'total_records' => $absensi->count(),
            'expected_records' => $expected,
            'month_name' => Carbon::createFromDate($tahun, $bulanIni, 1)->translatedFormat('F'),
            'vs_last_month' => $absensiLalu > 0 ? round((($absensi->count() - $absensiLalu) / $absensiLalu) * 100, 1) : 0,
        ];
    }

    private function getRecruitmentFunnel()
    {
        $stages = [
            'applied' => ['label' => 'Melamar', 'color' => '#3b82f6', 'icon' => 'fa-inbox'],
            'screening' => ['label' => 'Screening', 'color' => '#8b5cf6', 'icon' => 'fa-magnifying-glass'],
            'interview' => ['label' => 'Interview', 'color' => '#f59e0b', 'icon' => 'fa-calendar-check'],
            'offer' => ['label' => 'Offer', 'color' => '#ec4899', 'icon' => 'fa-file-signature'],
            'hired' => ['label' => 'Diterima', 'color' => '#10b981', 'icon' => 'fa-check-circle'],
        ];

        $data = [];
        $total = Pelamar::where('status_aktif', true)->count();
        $totalAktif = Pelamar::where('status_aktif', true)->whereNotIn('tahap_rekrutmen', ['hired', 'rejected'])->count();

        foreach ($stages as $key => $stage) {
            $count = Pelamar::where('tahap_rekrutmen', $key)->where('status_aktif', true)->count();
            $data[] = [
                'stage' => $key,
                'label' => $stage['label'],
                'count' => $count,
                'color' => $stage['color'],
                'icon' => $stage['icon'],
                'percentage' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
            ];
        }

        $rejected = Pelamar::where('tahap_rekrutmen', 'rejected')->where('status_aktif', true)->count();
        $conversionRate = $total > 0 ? round(($data[4]['count'] / $total) * 100, 1) : 0;

        $thisMonth = Pelamar::where('status_aktif', true)->whereMonth('tanggal_melamar', now()->month)->whereYear('tanggal_melamar', now()->year)->count();
        $lastMonth = now()->month == 1 ? 12 : now()->month - 1;
        $lastYear = now()->month == 1 ? now()->year - 1 : now()->year;
        $lastMonthCount = Pelamar::where('status_aktif', true)->whereMonth('tanggal_melamar', $lastMonth)->whereYear('tanggal_melamar', $lastYear)->count();

        return [
            'stages' => $data,
            'total' => $total,
            'aktif' => $totalAktif,
            'rejected' => $rejected,
            'conversion_rate' => $conversionRate,
            'this_month' => $thisMonth,
            'last_month' => $lastMonthCount,
            'trend_percent' => $lastMonthCount > 0 ? round((($thisMonth - $lastMonthCount) / $lastMonthCount) * 100, 1) : 0,
        ];
    }

    private function getFinancialOverview($tahun, $divisi)
    {
        $parseTotal = function ($item) {
            $val = (string) ($item->total ?? 0);
            if (substr_count($val, '.') > 1) $val = str_replace('.', '', $val);
            return (float) str_replace(',', '.', $val);
        };

        $spjTahunIni = SuratPerjalanan::whereYear('tanggal_berangkat', $tahun)
            ->when($divisi !== 'all', fn($q) => $q->whereHas('karyawan', fn($s) => $s->where('divisi', $divisi)))
            ->get();

        $totalTahun = $spjTahunIni->sum($parseTotal);
        $jumlahSPJ = $spjTahunIni->count();
        $avgPerSPJ = $jumlahSPJ > 0 ? round($totalTahun / $jumlahSPJ) : 0;

        $bulanIni = now()->month;
        $bulanLalu = $bulanIni == 1 ? 12 : $bulanIni - 1;
        $tahunLalu = $bulanIni == 1 ? $tahun - 1 : $tahun;

        $spjBulanIni = $spjTahunIni->filter(fn($s) => Carbon::parse($s->tanggal_berangkat)->month == $bulanIni);
        $totalBulanIni = $spjBulanIni->sum($parseTotal);

        $spjBulanLalu = SuratPerjalanan::whereMonth('tanggal_berangkat', $bulanLalu)
            ->whereYear('tanggal_berangkat', $tahunLalu)
            ->when($divisi !== 'all', fn($q) => $q->whereHas('karyawan', fn($s) => $s->where('divisi', $divisi)))
            ->get();
        $totalBulanLalu = $spjBulanLalu->sum($parseTotal);

        $monthChange = $totalBulanLalu > 0 ? round((($totalBulanIni - $totalBulanLalu) / $totalBulanLalu) * 100, 1) : 0;

        $monthlyData = collect(range(1, 12))->map(function ($m) use ($spjTahunIni, $parseTotal) {
            return $spjTahunIni->filter(fn($s) => Carbon::parse($s->tanggal_berangkat)->month == $m)->sum($parseTotal);
        })->toArray();

        return [
            'total_year' => $totalTahun,
            'total_month' => $totalBulanIni,
            'last_month' => $totalBulanLalu,
            'month_change' => $monthChange,
            'count_year' => $jumlahSPJ,
            'count_month' => $spjBulanIni->count(),
            'avg_per_spj' => $avgPerSPJ,
            'monthly_chart' => [
                'labels' => collect(range(1, 12))->map(fn($m) => Carbon::createFromDate($tahun, $m, 1)->translatedFormat('M'))->toArray(),
                'data' => $monthlyData,
            ],
            'month_name' => Carbon::createFromDate($tahun, $bulanIni, 1)->translatedFormat('F'),
        ];
    }

    private function getDivisionDistribution($divisi)
    {
        $data = $this->baseKaryawanQuery($divisi)
            ->select('divisi', DB::raw('COUNT(*) as total'))
            ->groupBy('divisi')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $colors = ['#4f46e5', '#059669', '#0284c7', '#d97706', '#dc2626', '#8b5cf6', '#ec4899', '#14b8a6'];

        return [
            'labels' => $data->pluck('divisi')->toArray(),
            'data' => $data->pluck('total')->toArray(),
            'colors' => array_slice($colors, 0, $data->count()),
            'total_categories' => $data->count(),
        ];
    }

    private function getTodayDigest($divisi)
    {
        $today = now()->toDateString();
        $isWeekend = now()->isWeekend();
        $isHoliday = \App\Models\HariLibur::whereDate('tanggal', $today)->exists();

        $absensi = AbsensiKaryawan::whereDate('tanggal', $today)
            ->when($divisi !== 'all', fn($q) => $q->whereHas('karyawan', fn($s) => $s->where('divisi', $divisi)))
            ->with('karyawan:id,nama_lengkap,jabatan,divisi,foto')
            ->get();

        $isLate = fn($a) => str_starts_with(trim((string) ($a->keterangan ?? '')), 'Telat');
        $isPresent = fn($a) => $a->jam_masuk || str_starts_with(strtolower(trim((string) ($a->keterangan ?? ''))), 'masuk');

        $hadir = $absensi->filter(fn($a) => $isPresent($a) && !$isLate($a));
        $telat = $absensi->filter(fn($a) => $isLate($a));

        $cutiHariIni = pengajuancuti::where('approval_manager', 1)
            ->whereDate('tanggal_awal', '<=', $today)
            ->whereDate('tanggal_akhir', '>=', $today)
            ->when($divisi !== 'all', fn($q) => $q->whereHas('karyawan', fn($s) => $s->where('divisi', $divisi)))
            ->with('karyawan:id,nama_lengkap,jabatan,divisi,foto')
            ->get();

        $izinHariIni = izinTigaJam::whereDate('tanggal_pengajuan', $today)
            ->when($divisi !== 'all', fn($q) => $q->whereHas('karyawan', fn($s) => $s->where('divisi', $divisi)))
            ->with('karyawan:id,nama_lengkap,jabatan,divisi,foto')
            ->get();

        $interviewHariIni = Pelamar::whereDate('jadwal_interview', $today)
            ->where('status_aktif', true)
            ->orderBy('jadwal_interview')
            ->get();

        $formatEmp = fn($e) => [
            'nama' => optional($e->karyawan)->nama_lengkap ?? 'Unknown',
            'jabatan' => optional($e->karyawan)->jabatan ?? '-',
            'foto' => optional($e->karyawan)->foto,
        ];

        return [
            'date' => now()->translatedFormat('d F Y'),
            'day_name' => now()->translatedFormat('l'),
            'is_weekend' => $isWeekend,
            'is_holiday' => $isHoliday,
            'total_hadir' => $hadir->count(),
            'total_telat' => $telat->count(),
            'total_cuti' => $cutiHariIni->count(),
            'total_izin' => $izinHariIni->count(),
            'total_interview' => $interviewHariIni->count(),
            'telat_list' => $telat->take(5)->map(function ($a) {
                $menit = 0;
                if ($a->waktu_keterlambatan) {
                    $p = explode(':', $a->waktu_keterlambatan);
                    $menit = ($p[0] ?? 0) * 60 + ($p[1] ?? 0);
                }
                return array_merge($this->formatEmp($a), ['late_minutes' => $menit, 'jam_masuk' => $a->jam_masuk]);
            })->values()->toArray(),
            'cuti_list' => $cutiHariIni->take(5)->map(fn($c) => array_merge($this->formatEmp($c), ['tipe' => $c->tipe]))->values()->toArray(),
            'interview_list' => $interviewHariIni->take(5)->map(fn($p) => [
                'nama' => $p->nama_lengkap,
                'jabatan' => $p->jabatan,
                'waktu' => Carbon::parse($p->jadwal_interview)->format('H:i'),
                'metode' => $p->metode_interview ?? '-',
            ])->values()->toArray(),
        ];
    }

    private function formatEmp($e)
    {
        return [
            'nama' => optional($e->karyawan)->nama_lengkap ?? 'Unknown',
            'jabatan' => optional($e->karyawan)->jabatan ?? '-',
            'foto' => optional($e->karyawan)->foto,
        ];
    }

    private function getRecentHires($divisi, $limit)
    {
        return $this->baseKaryawanQuery($divisi)
            ->whereNotNull('awal_probation')
            ->orderByDesc('awal_probation')
            ->limit($limit)
            ->get()
            ->map(fn($e) => [
                'nama' => $e->nama_lengkap,
                'jabatan' => $e->jabatan,
                'divisi' => $e->divisi,
                'foto' => $e->foto,
                'tanggal' => Carbon::parse($e->awal_probation)->translatedFormat('d M Y'),
                'days_ago' => Carbon::parse($e->awal_probation)->diffInDays(now()),
            ])->toArray();
    }

    private function getRecentResigns($divisi, $limit)
    {
        return karyawan::where('status_aktif', '0')
            ->when($divisi !== 'all', fn($q) => $q->where('divisi', $divisi))
            ->whereNotNull('resigned_at')
            ->orderByDesc('resigned_at')
            ->limit($limit)
            ->get()
            ->map(fn($e) => [
                'nama' => $e->nama_lengkap,
                'jabatan' => $e->jabatan,
                'divisi' => $e->divisi,
                'foto' => $e->foto,
                'tanggal' => Carbon::parse($e->resigned_at)->translatedFormat('d M Y'),
                'alasan' => $e->alasan_resign ?? '-',
                'days_ago' => Carbon::parse($e->resigned_at)->diffInDays(now()),
            ])->toArray();
    }

    private function getTopSPJDivisions($tahun, $divisi)
    {
        $parseTotal = function ($item) {
            $val = (string) ($item->total ?? 0);
            if (substr_count($val, '.') > 1) $val = str_replace('.', '', $val);
            return (float) str_replace(',', '.', $val);
        };

        $spj = SuratPerjalanan::with('karyawan:id,divisi')
            ->whereYear('tanggal_berangkat', $tahun)
            ->when($divisi !== 'all', fn($q) => $q->whereHas('karyawan', fn($s) => $s->where('divisi', $divisi)))
            ->get();

        return $spj->groupBy(fn($s) => optional($s->karyawan)->divisi ?? 'Lainnya')
            ->map(fn($items, $d) => [
                'divisi' => $d,
                'total' => $items->sum($parseTotal),
                'count' => $items->count(),
            ])
            ->sortByDesc('total')
            ->take(5)
            ->values()
            ->toArray();
    }

    private function getMonthlyAttendanceTrend($tahun, $divisi)
    {
        $data = collect(range(1, 12))->map(function ($m) use ($tahun, $divisi) {
            $absensi = AbsensiKaryawan::whereMonth('tanggal', $m)
                ->whereYear('tanggal', $tahun)
                ->when($divisi !== 'all', fn($q) => $q->whereHas('karyawan', fn($s) => $s->where('divisi', $divisi)))
                ->get();

            $isLate = fn($a) => str_starts_with(trim((string) ($a->keterangan ?? '')), 'Telat');
            $hadir = $absensi->filter(fn($a) => $a->jam_masuk && !$isLate($a))->count();
            $telat = $absensi->filter($isLate)->count();
            $total = $hadir + $telat;

            return [
                'month' => Carbon::createFromDate($tahun, $m, 1)->translatedFormat('M'),
                'hadir' => $hadir,
                'telat' => $telat,
                'rate' => $total > 0 ? round(($hadir / $total) * 100, 1) : 0,
            ];
        })->toArray();

        return $data;
    }

    private function getUpcomingEvents()
    {
        $events = collect();

        $interviews = Pelamar::where('status_aktif', true)
            ->whereNotNull('jadwal_interview')
            ->where('jadwal_interview', '>=', now())
            ->where('jadwal_interview', '<=', now()->addDays(14))
            ->orderBy('jadwal_interview')
            ->limit(5)
            ->get()
            ->map(fn($p) => [
                'type' => 'interview',
                'title' => "Interview {$p->nama_lengkap}",
                'subtitle' => $p->jabatan,
                'datetime' => Carbon::parse($p->jadwal_interview)->translatedFormat('d M, H:i'),
                'icon' => 'fa-calendar-check',
                'color' => '#0284c7',
            ]);

        $probationEnd = karyawan::where('status_aktif', '1')
            ->whereNotNull('akhir_probation')
            ->where('akhir_probation', '>=', now())
            ->where('akhir_probation', '<=', now()->addDays(30))
            ->orderBy('akhir_probation')
            ->limit(3)
            ->get()
            ->map(fn($e) => [
                'type' => 'probation',
                'title' => "Probation berakhir: {$e->nama_lengkap}",
                'subtitle' => $e->jabatan,
                'datetime' => Carbon::parse($e->akhir_probation)->translatedFormat('d M Y'),
                'icon' => 'fa-hourglass-half',
                'color' => '#f59e0b',
            ]);

        $kontrakEnd = karyawan::where('status_aktif', '1')
            ->whereNotNull('akhir_kontrak')
            ->where('akhir_kontrak', '>=', now())
            ->where('akhir_kontrak', '<=', now()->addDays(60))
            ->orderBy('akhir_kontrak')
            ->limit(3)
            ->get()
            ->map(fn($e) => [
                'type' => 'kontrak',
                'title' => "Kontrak berakhir: {$e->nama_lengkap}",
                'subtitle' => $e->jabatan,
                'datetime' => Carbon::parse($e->akhir_kontrak)->translatedFormat('d M Y'),
                'icon' => 'fa-file-contract',
                'color' => '#8b5cf6',
            ]);

        return $events->merge($interviews)
            ->merge($probationEnd)
            ->merge($kontrakEnd)
            ->sortBy('datetime')
            ->take(8)
            ->values()
            ->toArray();
    }

    private function generateInsights($tahun, $divisi, $composition, $attendance, $financial)
    {
        $insights = [];

        if ($composition['retention_rate'] < 80) {
            $insights[] = [
                'type' => 'danger',
                'icon' => 'fa-triangle-exclamation',
                'title' => 'Retensi Perlu Perhatian',
                'message' => "Retention rate {$composition['retention_rate']}% di bawah standar (80%). Tahun ini {$composition['resign_this_year']} karyawan resign.",
                'metric' => $composition['retention_rate'] . '%',
            ];
        } elseif ($composition['retention_rate'] >= 90) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'fa-shield-halved',
                'title' => 'Retensi Sangat Baik',
                'message' => "Retention rate {$composition['retention_rate']}%. Budaya kerja positif terpelihara.",
                'metric' => $composition['retention_rate'] . '%',
            ];
        }

        if ($attendance['punctuality_rate'] < 85) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'fa-clock',
                'title' => 'Ketepatan Waktu Menurun',
                'message' => "Punctuality {$attendance['punctuality_rate']}% dengan {$attendance['telat']} keterlambatan bulan ini. Rata-rata {$attendance['avg_late_minutes']} menit.",
                'metric' => $attendance['punctuality_rate'] . '%',
            ];
        }

        if ($financial['month_change'] > 30) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'fa-arrow-trend-up',
                'title' => 'Pengeluaran SPJ Naik',
                'message' => "Naik {$financial['month_change']}% dari bulan lalu. Total bulan ini Rp " . number_format($financial['total_month'], 0, ',', '.') . ".",
                'metric' => '+' . $financial['month_change'] . '%',
            ];
        } elseif ($financial['month_change'] < -20) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'fa-arrow-trend-down',
                'title' => 'Efisiensi SPJ Tercapai',
                'message' => "Turun " . abs($financial['month_change']) . "% dari bulan lalu. Penghematan efektif.",
                'metric' => $financial['month_change'] . '%',
            ];
        }

        if ($composition['probation'] > 0 && $composition['total'] > 0) {
            $probPercent = round(($composition['probation'] / $composition['total']) * 100, 1);
            if ($probPercent > 25) {
                $insights[] = [
                    'type' => 'info',
                    'icon' => 'fa-users-gear',
                    'title' => 'Banyak Karyawan Probation',
                    'message' => "{$composition['probation']} karyawan probation ({$probPercent}%). Perlu monitoring onboarding ketat.",
                    'metric' => $composition['probation'] . ' org',
                ];
            }
        }

        $interviewComing = Pelamar::where('status_aktif', true)
            ->whereNotNull('jadwal_interview')
            ->where('jadwal_interview', '>=', now())
            ->where('jadwal_interview', '<=', now()->addDays(7))
            ->count();

        if ($interviewComing > 3) {
            $insights[] = [
                'type' => 'info',
                'icon' => 'fa-calendar-week',
                'title' => 'Minggu Sibuk Interview',
                'message' => "{$interviewComing} interview terjadwal dalam 7 hari ke depan. Siapkan interviewer dan ruangan.",
                'metric' => $interviewComing . ' jadwal',
            ];
        }

        if ($composition['avg_tenure_months'] > 0) {
            $tahun = floor($composition['avg_tenure_months'] / 12);
            $bulan = $composition['avg_tenure_months'] % 12;
            $tenureStr = $tahun > 0 ? "{$tahun} thn {$bulan} bln" : "{$bulan} bln";
            $insights[] = [
                'type' => 'neutral',
                'icon' => 'fa-hourglass',
                'title' => 'Rata-rata Masa Kerja',
                'message' => "Karyawan bertahan rata-rata {$tenureStr}. " . ($composition['avg_tenure_months'] >= 24 ? 'Loyalitas tinggi.' : 'Perlu program retensi.'),
                'metric' => $tenureStr,
            ];
        }

        return array_slice($insights, 0, 5);
    }

    private function countWorkingDays($bulan, $tahun)
    {
        $start = Carbon::createFromDate($tahun, $bulan, 1);
        $end = $start->copy()->endOfMonth();
        $holidays = \App\Models\HariLibur::whereYear('tanggal', $tahun)
            ->pluck('tanggal')
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->toArray();
        $days = 0;
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if (!$date->isWeekend() && !in_array($date->toDateString(), $holidays)) {
                $days++;
            }
        }
        return $days;
    }

    public function getDivisions()
    {
        return response()->json(
            karyawan::whereNotNull('divisi')
                ->whereNot('divisi', 'Direksi')
                ->distinct()
                ->pluck('divisi')
        );
    }
}