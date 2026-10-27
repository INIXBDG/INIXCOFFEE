<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\karyawan;
use App\Models\Pelamar;
use App\Models\SuratPerjalanan;
use App\Models\pengajuancuti;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\InstrukturOl;
use App\Models\RKM;
use App\Models\Maintenance;

class HRController extends Controller
{
    const STATUS_LABEL = [
        'hadir' => 'Hadir tepat waktu',
        'telat' => 'Terlambat',
        'cuti' => 'Cuti',
        'sakit' => 'Sakit',
        'izin' => 'Izin 3 jam',
        'pending' => 'Menunggu persetujuan',
        'tidak_hadir' => 'Tanpa keterangan',
    ];

    const DAILY_COLUMNS = ['Tanggal', 'Nama', 'Divisi', 'Jabatan', 'Status', 'Jam masuk', 'Telat (menit)', 'Keterangan'];

    private presenceController $presence;

    private Carbon $pStart;
    private Carbon $pEnd;
    private Carbon $pEff;
    private int $pTahun;
    private string $pType = 'year';
    private string $pLabel = '';

    public function __construct()
    {
        $this->presence = app(presenceController::class);
    }

    public function index()
    {
        return view('HR.dashboard');
    }

    private function initPeriod(Request $request): void
    {
        $tahun = (int) $request->input('tahun', date('Y'));
        $periode = (string) $request->input('periode', 'all');
        $bulan = (int) $request->input('bulan', 0);

        if ($bulan >= 1 && $bulan <= 12) {
            $periode = 'm' . $bulan;
        }

        if (preg_match('/^q([1-4])$/', $periode, $m)) {
            $this->pType = 'quarter';
            $start = Carbon::create($tahun, ((int) $m[1] - 1) * 3 + 1, 1)->startOfDay();
            $end = $start->copy()->addMonthsNoOverflow(2)->endOfMonth()->startOfDay();
            $label = 'Triwulan ' . $m[1] . ' ' . $tahun;
        } elseif (preg_match('/^m(\d{1,2})$/', $periode, $m) && (int) $m[1] >= 1 && (int) $m[1] <= 12) {
            $this->pType = 'month';
            $start = Carbon::create($tahun, (int) $m[1], 1)->startOfDay();
            $end = $start->copy()->endOfMonth()->startOfDay();
            $label = $start->translatedFormat('F Y');
        } else {
            $this->pType = 'year';
            $start = Carbon::create($tahun, 1, 1)->startOfDay();
            $end = Carbon::create($tahun, 12, 31)->startOfDay();
            $label = 'Tahun ' . $tahun;
        }

        $today = Carbon::today();
        $eff = $end->gt($today) ? $today->copy() : $end->copy();
        if ($eff->lt($start)) {
            $eff = $start->copy();
        }

        $this->pTahun = $tahun;
        $this->pStart = $start;
        $this->pEnd = $end;
        $this->pEff = $eff;
        $this->pLabel = $label;
    }

    private function periodBounds(): array
    {
        return [$this->pStart->copy()->startOfDay(), $this->pEnd->copy()->endOfDay()];
    }

    private function prevPeriod(): array
    {
        if ($this->pType === 'month') {
            $s = $this->pStart->copy()->subMonthNoOverflow()->startOfMonth();
            $e = $s->copy()->endOfMonth()->startOfDay();
        } elseif ($this->pType === 'quarter') {
            $s = $this->pStart->copy()->subMonthsNoOverflow(3)->startOfMonth();
            $e = $s->copy()->addMonthsNoOverflow(2)->endOfMonth()->startOfDay();
        } else {
            $s = Carbon::create($this->pTahun - 1, 1, 1)->startOfDay();
            $e = Carbon::create($this->pTahun - 1, 12, 31)->startOfDay();
        }
        return [$s, $e];
    }

    private function headcountQuery($divisi)
    {
        $end = $this->pEnd->copy()->endOfDay();

        if ($end->gte(now())) {
            return $this->baseKaryawanQuery($divisi);
        }

        $d = $end->toDateString();
        return $this->baseKaryawanQuery($divisi, false)
            ->where(fn($q) => $q
                ->where(fn($x) => $x->where('status_aktif', '1')->whereNull('resigned_at'))
                ->orWhere('resigned_at', '>', $end))
            ->where(fn($q) => $q
                ->whereRaw('COALESCE(awal_probation, awal_kontrak, awal_tetap) IS NULL')
                ->orWhereRaw('COALESCE(awal_probation, awal_kontrak, awal_tetap) <= ?', [$d]));
    }

    public function getDashboardData(Request $request)
    {
        try {
            Carbon::setLocale('id');
            $this->initPeriod($request);
            $tahun = $this->pTahun;
            $divisi = $request->input('divisi', 'all');

            [$prevStart, $prevEnd] = $this->prevPeriod();

            $recent = $this->statusRange($this->pStart, $this->pEff, $divisi);
            $prevRows = $prevStart->year >= 2023
                ? $this->statusRange($prevStart, $prevEnd, $divisi)['rows']
                : collect();

            $composition = $this->getEmployeeComposition($divisi, $tahun);
            $attendance = $this->getAttendanceSnapshot($recent['rows'], $prevRows);
            $recruitment = $this->getRecruitmentFunnel($tahun, $divisi);
            $financial = $this->getFinancialOverview($tahun, $divisi);
            $upcoming = $this->getUpcoming($divisi, $tahun);

            return response()->json([
                'success' => true,
                'header' => [
                    'greeting' => $this->getGreeting(),
                    'today' => Carbon::today()->translatedFormat('l, d F Y'),
                ],
                'period' => ['label' => $this->pLabel, 'type' => $this->pType],
                'composition' => $composition,
                'attendance' => $attendance,
                'recruitment' => $recruitment,
                'financial' => $financial,
                'leave' => $this->getLeaveOverview($tahun, $divisi),
                'divisions' => $this->getDivisionDistribution($divisi),
                'today' => $this->getTodayDigest($recent, $this->pEff),
                'recent_hires' => $this->getRecentHires($divisi, $tahun, 5),
                'recent_resigns' => $this->getRecentResigns($divisi, $tahun, 5),
                'attendance_trend' => $this->getMonthlyAttendanceTrend($recent['rows'], $tahun),
                'upcoming' => $upcoming['events'],
                'insights' => $this->generateInsights($composition, $attendance, $financial, $recruitment, $upcoming['counts']),
                'instruktur_ol' => $this->getOlOverview($tahun),
                'sales_overview' => $this->getSalesOverview($tahun),
                'maintenance_overview' => $this->getMaintenanceOverview($divisi),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function refDate(int $tahun): Carbon
    {
        return $tahun == now()->year ? Carbon::today() : Carbon::create($tahun, 12, 31)->startOfDay();
    }

    private function getGreeting()
    {
        $hour = now()->hour;
        if ($hour < 11) return ['text' => 'Selamat pagi', 'sub' => 'Mari mulai hari dengan produktif'];
        if ($hour < 15) return ['text' => 'Selamat siang', 'sub' => 'Waktunya meninjau progres'];
        if ($hour < 18) return ['text' => 'Selamat sore', 'sub' => 'Saatnya merapikan pekerjaan hari ini'];
        return ['text' => 'Selamat malam', 'sub' => 'Ringkasan hari ini siap dilihat'];
    }

    private function getOlOverview(int $tahun): array
    {
        $total = InstrukturOl::count();

        $lspDist = InstrukturOl::select('core', DB::raw('COUNT(*) as total'))
            ->groupBy('core')
            ->orderByDesc('total')
            ->get()
            ->map(fn($item) => [
                'label' => $item->core ?: 'Tidak Diketahui',
                'value' => (int) $item->total
            ])
            ->toArray();

        $topCores = InstrukturOl::select(
                DB::raw('TRIM(core) as core_normalized'),
                DB::raw('COUNT(*) as total')
            )
            ->whereNotNull('core')
            ->where('core', '!=', '')
            ->groupBy('core_normalized')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'label' => trim($item->core_normalized),
                'value' => (int) $item->total
            ])
            ->toArray();

        $lengkapCv = InstrukturOl::whereNotNull('cv_path')->count();
        $lengkapKtp = InstrukturOl::whereNotNull('ktp_path')->count();
        $docRate = $total > 0 ? round((($lengkapCv + $lengkapKtp) / ($total * 2)) * 100, 1) : 0;

        return [
            'total' => $total,
            'doc_completion_rate' => $docRate,
            'lsp_distribution' => $lspDist,
            'top_cores' => $topCores,
        ];
    }

    private function getSalesOverview(int $tahun): array
    {
        $rkms = RKM::with(['materi', 'analisisrkm'])
            ->where('status', '0')
            ->whereBetween('tanggal_awal', $this->periodBounds())
            ->get();

        $totalKelas = $rkms->count();
        $totalPax = $rkms->sum('pax');
        $totalNett = $rkms->sum(fn($r) => floatval($r->analisisrkm?->nett_penjualan ?? 0));

        $topMateri = $rkms->groupBy('materi_key')->map(function($items) {
            return [
                'label' => $items->first()->materi->nama_materi ?? 'Materi Lainnya',
                'value' => $items->count()
            ];
        })->sortByDesc('value')->take(3)->values()->toArray();

        return compact('totalKelas', 'totalPax', 'totalNett', 'topMateri');
    }

    private function getMaintenanceOverview($divisi): array
    {
        $maintenances = Maintenance::whereBetween('tanggal', $this->periodBounds())
            ->when($divisi !== 'all', fn($q) => $q->where('divisi', $divisi))
            ->get();

        $totalTiket = $maintenances->count();
        $totalBiaya = $maintenances->sum('biaya');

        $today = now()->toDateString();
        $onProgress = $maintenances->where('status', 'On Progress')->where('tanggal', '<=', $today)->count();
        $mendatang = $maintenances->where('status', 'Mendatang')->count() +
                    $maintenances->where('status', '!=', 'Selesai')->where('tanggal', '>', $today)->count();

        $topKategori = Maintenance::select('jenis_maintenance', DB::raw('COUNT(id) as total'))
            ->whereBetween('tanggal', $this->periodBounds())
            ->when($divisi !== 'all', fn($q) => $q->where('divisi', $divisi))
            ->groupBy('jenis_maintenance')
            ->orderByDesc('total')
            ->limit(3)
            ->get()
            ->map(fn($item) => [
                'label' => $item->jenis_maintenance ?: 'Tidak Diketahui',
                'value' => (int) $item->total,
            ])->toArray();

        return [
            'total_tiket' => $totalTiket,
            'total_biaya' => $totalBiaya,
            'on_progress' => $onProgress,
            'mendatang' => $mendatang,
            'top_kategori' => $topKategori,
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Karyawan                                                           */
    /* ------------------------------------------------------------------ */
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

    private function yearRange(int $tahun): array   // nama dipertahankan, isinya sekarang = periode
    {
        return $this->periodBounds();
    }

    private function namaKaryawan($e): string
    {
        return $e->nama_lengkap ?? $e->nama ?? 'Tidak Diketahui';
    }

    private function getEmployeeComposition($divisi, $tahun)
    {
        [$start, $end] = $this->periodBounds();

        $active = $this->headcountQuery($divisi)->get(['id', 'awal_probation', 'awal_kontrak', 'awal_tetap']);

        $tetap = $kontrak = $probation = $belumDiisi = 0;
        $tenures = [];

        foreach ($active as $e) {
            $f = $this->fase($e);
            if ($f === 'Tetap') $tetap++;
            elseif ($f === 'Kontrak') $kontrak++;
            elseif ($f === 'Probation') $probation++;
            else $belumDiisi++;

            $first = $e->awal_probation ?? $e->awal_kontrak ?? $e->awal_tetap;
            if ($first && Carbon::parse($first)->lte($this->pEff)) {
                $tenures[] = max(0, (int) Carbon::parse($first)->diffInMonths($this->pEff));
            }
        }

        $total = $active->count();

        $newThisYear = $this->baseKaryawanQuery($divisi)
            ->whereBetween('awal_probation', [$start, $end])
            ->count();

        $resignThisYear = $this->baseKaryawanQuery($divisi, false)
            ->whereNotNull('resigned_at')
            ->whereBetween('resigned_at', [$start, $end])
            ->count();

        $retention = ($total + $resignThisYear) > 0 ? round($total / ($total + $resignThisYear) * 100, 1) : 100;

        $avgMonths = count($tenures) > 0 ? (int) round(array_sum($tenures) / count($tenures)) : 0;
        $years = intdiv($avgMonths, 12);
        $months = $avgMonths % 12;

        $labels = ['Tetap', 'Kontrak', 'Probation'];
        $data = [$tetap, $kontrak, $probation];
        $colors = ['#059669', '#0284c7', '#d97706'];
        if ($belumDiisi > 0) {
            $labels[] = 'Belum diisi';
            $data[] = $belumDiisi;
            $colors[] = '#9ca3af';
        }

        return [
            'total' => $total,
            'tetap' => $tetap,
            'kontrak' => $kontrak,
            'probation' => $probation,
            'belum_diisi' => $belumDiisi,
            'new_this_year' => $newThisYear,
            'resign_this_year' => $resignThisYear,
            'retention_rate' => $retention,
            'avg_tenure_months' => $avgMonths,
            'avg_tenure_label' => $years > 0 ? "{$years} thn {$months} bln" : "{$months} bln",
            'composition_chart' => ['labels' => $labels, 'data' => $data, 'colors' => $colors],
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

        return [
            'labels' => $data->map(fn($d) => $d->divisi ?: 'Tanpa divisi')->toArray(),
            'data' => $data->pluck('total')->map(fn($v) => (int) $v)->toArray(),
            'total_categories' => $data->count(),
        ];
    }

    private function getRecentHires($divisi, $tahun, $limit)
    {
        [$start, $end] = $this->yearRange((int) $tahun);

        return $this->baseKaryawanQuery($divisi)
            ->whereNotNull('awal_probation')
            ->whereBetween('awal_probation', [$start, $end])
            ->orderByDesc('awal_probation')
            ->limit($limit)
            ->get()
            ->map(fn($e) => [
                'nama' => $this->namaKaryawan($e),
                'jabatan' => $e->jabatan ?? '-',
                'divisi' => $e->divisi ?? '-',
                'foto' => $e->foto,
                'tanggal' => Carbon::parse($e->awal_probation)->translatedFormat('d M Y'),
                'days_ago' => max(0, (int) Carbon::parse($e->awal_probation)->startOfDay()->diffInDays(Carbon::today())),
            ])->toArray();
    }

    private function getRecentResigns($divisi, $tahun, $limit)
    {
        [$start, $end] = $this->yearRange((int) $tahun);

        return $this->baseKaryawanQuery($divisi, false)
            ->whereNotNull('resigned_at')
            ->whereBetween('resigned_at', [$start, $end])
            ->orderByDesc('resigned_at')
            ->limit($limit)
            ->get()
            ->map(fn($e) => [
                'nama' => $this->namaKaryawan($e),
                'jabatan' => $e->jabatan ?? '-',
                'divisi' => $e->divisi ?? '-',
                'foto' => $e->foto,
                'tanggal' => Carbon::parse($e->resigned_at)->translatedFormat('d M Y'),
                'alasan' => $e->alasan_resign ?: '-',
                'days_ago' => max(0, (int) Carbon::parse($e->resigned_at)->startOfDay()->diffInDays(Carbon::today())),
            ])->toArray();
    }
    /* ------------------------------------------------------------------ */
    /*  Kehadiran                                                          */
    /* ------------------------------------------------------------------ */

    private function statusRange(Carbon $start, Carbon $end, $divisi)
    {
        $this->presence->ensureHolidays($start, $end);
        return $this->presence->buildDailyStatus($start, $end, $divisi);
    }

    private function summarize($rows)
    {
        $p = $this->presence->calculateAttendanceSummary($rows, collect(), []);
        $present = $p['hadir'] + $p['telat'];

        return [
            'hadir' => $p['hadir'],
            'telat' => $p['telat'],
            'tidak_hadir' => $p['tidak_hadir'],
            'attended' => $p['hadir'] + $p['telat'] + $p['izin'],
            'expected' => $rows->count() - $p['cuti'] - $p['sakit'],
            'rate' => $p['attendance_rate'],
            'punctuality' => $p['punctuality_rate'],
            'late_rate' => $present > 0 ? round($p['telat'] / $present * 100, 1) : 0,
            'avg_late' => (int) round($this->presence->averageLatePerEmployee($rows)),
        ];
    }

    private function getAttendanceSnapshot($rows, $prevRows)
    {
        $cur = $this->summarize($rows);
        $prev = $prevRows->isEmpty() ? null : $this->summarize($prevRows);

        return [
            'month_name' => $this->pLabel,
            'attendance_rate' => $cur['rate'],
            'attended' => $cur['attended'],
            'expected' => $cur['expected'],
            'punctuality_rate' => $cur['punctuality'],
            'telat' => $cur['telat'],
            'tidak_hadir' => $cur['tidak_hadir'],
            'avg_late_minutes' => $cur['avg_late'],
            'vs_last_month' => $prev ? round($cur['rate'] - $prev['rate'], 1) : 0,
        ];
    }

    private function getMonthlyAttendanceTrend($yearRows, $tahun)
    {
        $byMonth = $yearRows->groupBy(fn($r) => (int) substr($r['tanggal'], 5, 2));

        return collect(range(1, 12))->map(function ($m) use ($byMonth, $tahun) {
            $rows = $byMonth->get($m, collect());
            $label = Carbon::createFromDate($tahun, $m, 1)->translatedFormat('M');

            if ($rows->isEmpty()) {
                return ['month' => $label, 'rate' => null, 'late_rate' => null];
            }

            $s = $this->summarize($rows);
            return ['month' => $label, 'rate' => $s['rate'], 'late_rate' => $s['late_rate']];
        })->toArray();
    }

    private function getTodayDigest($recent, Carbon $refDate)
    {
        $todayStr = $refDate->toDateString();
        $rows = $recent['rows']->where('tanggal', $todayStr);
        $emp = $recent['employees']->keyBy('id');
        $count = fn($s) => $rows->where('status', $s)->count();

        $person = function ($r) use ($emp) {
            $e = $emp->get($r['id']);
            return [
                'nama' => $e?->nama_lengkap ?? 'Tidak diketahui',
                'jabatan' => $e?->jabatan ?? '-',
                'foto' => $e?->foto,
            ];
        };

        $telatList = $rows->where('status', 'telat')
            ->sortByDesc('late_minutes')
            ->take(5)
            ->map(fn($r) => $person($r) + [
                'late_minutes' => (int) round($r['late_minutes']),
                'jam_masuk' => $r['jam_masuk'],
            ])->values()->toArray();

        $cutiRows = $rows->whereIn('status', ['cuti', 'sakit']);
        $cutiList = $cutiRows->take(5)->map(fn($r) => $person($r) + [
            'tipe' => $r['status'] === 'sakit' ? 'Sakit' : ($r['sub'] === 'menikah' ? 'Cuti menikah' : 'Cuti'),
        ])->values()->toArray();

        $interviews = Pelamar::where('status_aktif', true)
            ->whereDate('jadwal_interview', $todayStr)
            ->orderBy('jadwal_interview')
            ->get();

        return [
            'date' => $refDate->translatedFormat('d F Y'),
            'day_name' => $refDate->translatedFormat('l'),
            'is_weekend' => $refDate->isWeekend(),
            'is_holiday' => isset($recent['holidays'][$todayStr]),
            'total_hadir' => $count('hadir'),
            'total_telat' => $count('telat'),
            'total_cuti' => $cutiRows->count(),
            'total_izin' => $count('izin'),
            'total_pending' => $count('pending'),
            'total_tidak_hadir' => $count('tidak_hadir'),
            'total_interview' => $interviews->count(),
            'telat_list' => $telatList,
            'cuti_list' => $cutiList,
            'is_today' => $refDate->isToday(),
            'interview_list' => $interviews->take(5)->map(fn($p) => [
                'nama' => $p->nama_lengkap,
                'jabatan' => $p->jabatan,
                'waktu' => Carbon::parse($p->jadwal_interview)->format('H:i'),
                'metode' => $p->metode_interview ?? '-',
            ])->values()->toArray(),
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Rekrutmen                                                          */
    /* ------------------------------------------------------------------ */

    private function getRecruitmentFunnel($tahun, $divisi)
    {
        $bounds = $this->periodBounds();
        $base = fn() => Pelamar::where('status_aktif', true)
            ->when($divisi !== 'all', fn($q) => $q->where('divisi', $divisi));

        $counts = $base()->whereBetween('tanggal_melamar', $bounds)
            ->select('tahap_rekrutmen', DB::raw('COUNT(*) as c'))
            ->groupBy('tahap_rekrutmen')
            ->pluck('c', 'tahap_rekrutmen');

        $total = (int) $counts->sum();
        $hired = (int) ($counts['hired'] ?? 0);
        $rejected = (int) ($counts['rejected'] ?? 0);

        $stages = [
            'applied' => ['label' => 'Melamar', 'color' => '#3b82f6'],
            'screening' => ['label' => 'Screening', 'color' => '#8b5cf6'],
            'interview' => ['label' => 'Interview', 'color' => '#d97706'],
            'offer' => ['label' => 'Offer', 'color' => '#ec4899'],
            'hired' => ['label' => 'Diterima', 'color' => '#059669'],
        ];

        $data = [];
        foreach ($stages as $key => $stage) {
            $n = (int) ($counts[$key] ?? 0);
            $data[] = [
                'stage' => $key,
                'label' => $stage['label'],
                'count' => $n,
                'color' => $stage['color'],
                'percentage' => $total > 0 ? round($n / $total * 100, 1) : 0,
            ];
        }

        [$ps, $pe] = $this->prevPeriod();
        $prevCount = $base()->whereBetween('tanggal_melamar', [$ps->copy()->startOfDay(), $pe->copy()->endOfDay()])->count();

        return [
            'stages' => $data,
            'total' => $total,
            'aktif' => $total - $hired - $rejected,
            'hired' => $hired,
            'rejected' => $rejected,
            'conversion_rate' => $total > 0 ? round($hired / $total * 100, 1) : 0,
            'this_period' => $total,
            'last_period' => $prevCount,
            'trend_percent' => $prevCount > 0 ? round(($total - $prevCount) / $prevCount * 100, 1) : 0,
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  SPJ                                                                */
    /* ------------------------------------------------------------------ */

    private function spjTotal($item): float
    {
        $val = (string) ($item->total ?? 0);
        if (substr_count($val, '.') > 1) {
            $val = str_replace('.', '', $val);
        }
        return (float) str_replace(',', '.', $val);
    }

    private function getFinancialOverview($tahun, $divisi)
    {
        $byDivisi = fn($q) => $q->when($divisi !== 'all',
            fn($x) => $x->whereHas('karyawan', fn($s) => $s->where('divisi', $divisi)));

        [$ps, $pe] = $this->prevPeriod();

        $spj = $byDivisi(SuratPerjalanan::whereBetween('tanggal_berangkat', $this->periodBounds()))->get();
        $spjPrev = $byDivisi(SuratPerjalanan::whereBetween('tanggal_berangkat', [$ps->copy()->startOfDay(), $pe->copy()->endOfDay()]))->get();

        $total = $spj->sum(fn($s) => $this->spjTotal($s));
        $totalPrev = $spjPrev->sum(fn($s) => $this->spjTotal($s));
        $count = $spj->count();

        return [
            'total_period' => (int) round($total),
            'count_period' => $count,
            'prev_total' => (int) round($totalPrev),
            'change' => $totalPrev > 0 ? round(($total - $totalPrev) / $totalPrev * 100, 1) : 0,
            'avg_per_spj' => $count > 0 ? (int) round($total / $count) : 0,
            'label' => $this->pLabel,
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Cuti                                                               */
    /* ------------------------------------------------------------------ */

    private function leaveStatus($v): string
    {
        return match ($this->presence->approvalState($v)) {
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => 'Menunggu',
        };
    }

    private function leaveData(int $tahun, string $divisi): array
    {
        $list = pengajuancuti::with(['karyawan', 'karyawanpengganti1'])
            ->whereBetween('tanggal_awal', $this->periodBounds())      
            ->when($divisi !== 'all', fn($q) => $q->whereHas('karyawan', fn($s) => $s->where('divisi', $divisi)))
            ->orderByDesc('tanggal_awal')
            ->get();

        $aktif = $this->headcountQuery($divisi)->count();               

        $withStatus = $list->map(fn($p) => ['p' => $p, 'status' => $this->leaveStatus($p->approval_manager)]);
        $approved = $withStatus->where('status', 'Disetujui');

        $pegawaiCuti = $approved->map(fn($x) => $x['p']->id_karyawan)->unique()->count();
        $hariCuti = (int) round($approved->sum(fn($x) => (float) $x['p']->durasi));

        $rows = $withStatus->map(function ($x) {
            $p = $x['p'];
            $e = $p->karyawan;
            return [
                $e?->nama_lengkap ?? 'Tidak diketahui',
                $e?->divisi ?? '-',
                $e?->jabatan ?? '-',
                ucfirst((string) ($p->tipe ?: 'cuti')),
                $p->tanggal_awal ? Carbon::parse($p->tanggal_awal)->format('d/m/Y') : '-',
                $p->tanggal_akhir ? Carbon::parse($p->tanggal_akhir)->format('d/m/Y') : '-',
                (float) $p->durasi . ' hari',
                $p->alasan ?: '-',
                $x['status'],
                $p->alasan_manager ?: '-',
                $p->karyawanpengganti1?->nama_lengkap ?? '-',
            ];
        })->values()->all();

        return [
            'rows' => $rows,
            'aktif' => $aktif,
            'total' => $list->count(),
            'disetujui' => $approved->count(),
            'menunggu' => $withStatus->where('status', 'Menunggu')->count(),
            'ditolak' => $withStatus->where('status', 'Ditolak')->count(),
            'pegawai_cuti' => $pegawaiCuti,
            'hari_cuti' => $hariCuti,
            'rate_pegawai' => $aktif > 0 ? round($pegawaiCuti / $aktif * 100, 1) : 0,
            'rate_approval' => $list->count() > 0 ? round($approved->count() / $list->count() * 100, 1) : 0,
        ];
    }

    private function getLeaveOverview($tahun, $divisi)
    {
        $d = $this->leaveData((int) $tahun, $divisi);
        $d['recent'] = array_slice($d['rows'], 0, 5);
        unset($d['rows']);
        return $d;
    }

    /* ------------------------------------------------------------------ */
    /*  Agenda & reminder                                                  */
    /* ------------------------------------------------------------------ */

    private function getUpcoming($divisi, $tahun, $limit = 8)
    {
        $events = [];
        $today = Carbon::today();
        [$start, $end] = $this->periodBounds();

        $interviews = Pelamar::where('status_aktif', true)
            ->when($divisi !== 'all', fn($q) => $q->where('divisi', $divisi))
            ->whereNotNull('jadwal_interview')
            ->whereBetween('jadwal_interview', [$start, $end])
            ->orderBy('jadwal_interview')
            ->when($limit, fn($q) => $q->limit(5))
            ->get();

        foreach ($interviews as $p) {
            $events[] = [
                'sort' => Carbon::parse($p->jadwal_interview)->timestamp,
                'kategori' => 'Interview',
                'title' => "Interview {$p->nama_lengkap}",
                'subtitle' => $p->jabatan,
                'datetime' => Carbon::parse($p->jadwal_interview)->translatedFormat('d M, H:i'),
                'icon' => 'fa-calendar-check',
                'color' => '#0284c7',
            ];
        }

        // window reminder disesuaikan supaya akhir periode terjangkau
        $window = max(30, (int) ceil($today->diffInDays($end, false)));

        $res = json_decode(
            app(employeeController::class)->getEmployeeReminders(new Request(['window' => $window]))->getContent(),
            true
        );

        $counts = ['overdue' => 0, 'urgent' => 0, 'upcoming' => 0];
        $colors = ['overdue' => '#dc2626', 'urgent' => '#d97706', 'upcoming' => '#7c3aed'];

        foreach (($res['data'] ?? []) as $r) {
            if ($divisi !== 'all' && $r['divisi'] !== $divisi) {
                continue;
            }
            $d = (int) $r['days_left'];
            $date = $today->copy()->addDays($d);

            // hanya penilaian yang tanggal berakhirnya jatuh DI DALAM periode
            if ($date->lt($start) || $date->gt($end)) {
                continue;
            }

            $counts[$r['level']]++;

            $events[] = [
                'sort' => $date->timestamp,
                'kategori' => 'Penilaian',
                'title' => "Penilaian {$r['fase_label']}: {$r['nama']}",
                'subtitle' => "Berakhir {$r['tanggal_berakhir']} · {$r['jabatan']}",
                'datetime' => $d < 0 ? 'Terlewat ' . abs($d) . ' hari' : ($d === 0 ? 'Hari ini' : "{$d} hari lagi"),
                'icon' => 'fa-clipboard-check',
                'color' => $colors[$r['level']] ?? '#7c3aed',
            ];
        }

        usort($events, fn($a, $b) => $a['sort'] <=> $b['sort']);
        $slice = $limit ? array_slice($events, 0, $limit) : $events;

        return [
            'events' => array_map(fn($e) => array_diff_key($e, ['sort' => 1]), $slice),
            'counts' => $counts,
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Insight                                                            */
    /* ------------------------------------------------------------------ */

    private function generateInsights($composition, $attendance, $financial, $recruitment, $reminderCounts)
    {
        $insights = [];
        $add = function ($type, $icon, $title, $message, $metric, $detail = null) use (&$insights) {
            $insights[] = compact('type', 'icon', 'title', 'message', 'metric', 'detail');
        };

        if ($reminderCounts['overdue'] > 0) {
            $add('danger', 'fa-clipboard-check', 'Penilaian terlewat', "{$reminderCounts['overdue']} penilaian karyawan sudah melewati tanggal berakhir fase. Segera buat penilaiannya.", $reminderCounts['overdue'] . ' orang', ['agenda', '']);
        }
        if ($reminderCounts['urgent'] > 0) {
            $add('warning', 'fa-hourglass-half', 'Penilaian mendesak', "{$reminderCounts['urgent']} fase karyawan berakhir dalam 7 hari ke depan.", $reminderCounts['urgent'] . ' orang', ['agenda', '']);
        } elseif ($reminderCounts['upcoming'] > 0) {
            $add('info', 'fa-bell', 'Penilaian akan datang', "{$reminderCounts['upcoming']} fase karyawan berakhir dalam 30 hari ke depan.", $reminderCounts['upcoming'] . ' orang', ['agenda', '']);
        }

        $rate = $composition['retention_rate'];
        if ($rate < 60) {
            $add('danger', 'fa-triangle-exclamation', 'Retensi kritis', "Retensi {$rate}% di bawah 60%. Tahun ini {$composition['resign_this_year']} karyawan resign. Perlu intervensi segera.", $rate . '%', ['retensi', '']);
        } elseif ($rate < 75) {
            $add('warning', 'fa-triangle-exclamation', 'Retensi perlu diwaspadai', "Retensi {$rate}% di bawah 75%. Tahun ini {$composition['resign_this_year']} karyawan resign.", $rate . '%', ['retensi', '']);
        } elseif ($rate >= 90) {
            $add('success', 'fa-shield-halved', 'Retensi sangat baik', "Retensi {$rate}% tahun ini.", $rate . '%', ['retensi', '']);
        }

        if ($attendance['telat'] > 0 && $attendance['punctuality_rate'] < 85) {
            $add('warning', 'fa-clock', 'Ketepatan waktu menurun', "Ketepatan waktu {$attendance['punctuality_rate']}% dengan {$attendance['telat']} keterlambatan bulan ini. Rata-rata {$attendance['avg_late_minutes']} menit per orang.", $attendance['punctuality_rate'] . '%', ['ketepatan', '']);
        }
        if ($attendance['tidak_hadir'] > 0) {
            $add('warning', 'fa-user-xmark', 'Ada hari tanpa keterangan', "{$attendance['tidak_hadir']} hari kerja bulan ini belum memiliki cuti, sakit, atau izin.", $attendance['tidak_hadir'] . ' hari', ['kehadiran', 'tidak_hadir']);
        }

        if ($financial['change'] > 30) {
            $add('warning', 'fa-arrow-trend-up', 'Pengeluaran SPJ naik', "Naik {$financial['change']}% dari periode sebelumnya.", '+' . $financial['change'] . '%', ['spj', '']);
        } elseif ($financial['change'] < -20) {
            $add('success', 'fa-arrow-trend-down', 'Pengeluaran SPJ turun', 'Turun ' . abs($financial['change']) . '% dari periode sebelumnya.', $financial['change'] . '%', ['spj', '']);
        }

        if ($composition['probation'] > 0 && $composition['total'] > 0) {
            $pct = round($composition['probation'] / $composition['total'] * 100, 1);
            if ($pct > 25) {
                $add('info', 'fa-users-gear', 'Banyak karyawan probation', "{$composition['probation']} karyawan masih probation ({$pct}%).", $composition['probation'] . ' orang', ['karyawan', 'Probation']);
            }
        }

        $order = ['danger' => 0, 'warning' => 1, 'info' => 2, 'success' => 3, 'neutral' => 4];
        usort($insights, fn($a, $b) => $order[$a['type']] <=> $order[$b['type']]);

        return array_slice($insights, 0, 5);
    }

    public function getDivisions()
    {
        return response()->json(
            $this->baseKaryawanQuery('all', false)
                ->whereNotNull('divisi')
                ->where('divisi', '!=', '')
                ->distinct()
                ->orderBy('divisi')
                ->pluck('divisi')
        );
    }

    /* ------------------------------------------------------------------ */
    /*  Detail drill-down & Export                                         */
    /* ------------------------------------------------------------------ */

    public function getDetail(Request $request)
    {
        try {
            Carbon::setLocale('id');
            $this->initPeriod($request);     
            $d = $this->buildDetail(
                (string) $request->input('type'),
                $this->pTahun,
                (string) $request->input('divisi', 'all'),
                (string) $request->input('filter', ''),
                0
            );
            return response()->json(['success' => true] + $d);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memuat detail: ' . $e->getMessage()], 500);
        }
    }

    public function exportDetail(Request $request)
    {
        Carbon::setLocale('id');
        $this->initPeriod($request);
        $type = (string) $request->input('type');
        $tahun = $this->pTahun;
        $divisi = (string) $request->input('divisi', 'all');
        $filter = (string) $request->input('filter', '');
        $format = $request->input('format', 'excel');

        $d = $this->buildDetail($type, $tahun, $divisi, $filter, 0);
        $html = $this->exportHtml($d, $tahun, $divisi);
        $fname = 'hr-' . preg_replace('/[^a-z0-9_-]/i', '', $type) . "-{$tahun}-" . now()->format('Ymd_His');

        if ($format === 'pdf') {
            if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                return \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4', 'landscape')->download($fname . '.pdf');
            }
            return response($html . '<script>window.onload=function(){window.print()}</script>');
        }

        return response("\xEF\xBB\xBF" . $html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fname . '.xls"',
        ]);
    }

    private function exportHtml(array $d, int $tahun, string $divisi): string
    {
        $e = fn($v) => e((string) $v);
        $h = '<html><head><meta charset="UTF-8"><style>
            body{font-family:Arial,sans-serif;font-size:11px;color:#111}
            h2{margin:0 0 4px} .meta{color:#555;margin-bottom:8px}
            table{border-collapse:collapse;width:100%;margin-bottom:10px}
            th,td{border:1px solid #999;padding:4px 6px;text-align:left;vertical-align:top}
            th{background:#eef2ff} .box{background:#f3f4f6;padding:6px 8px;margin-bottom:8px}
        </style></head><body>';
        $h .= '<h2>' . $e($d['title']) . '</h2>';
        $h .= '<div class="meta">' . $e($this->pLabel) . ' | Divisi: ' . ($divisi === 'all' ? 'Semua' : $e($divisi)) . ' | Dicetak ' . now()->translatedFormat('d F Y H:i') . '</div>';
        $h .= '<div class="box"><b>Rumus:</b> ' . $e($d['formula']) . '<br><b>Penjelasan:</b> ' . $e($d['explanation']) . '</div>';

        if (!empty($d['summary'])) {
            $h .= '<table><tr><th>Ringkasan</th><th>Nilai</th><th>Keterangan</th></tr>';
            foreach ($d['summary'] as $s) {
                $h .= '<tr><td>' . $e($s['label']) . '</td><td>' . $e($s['value']) . '</td><td>' . $e($s['note'] ?? '') . '</td></tr>';
            }
            $h .= '</table>';
        }

        $h .= '<table><tr><th>No</th>';
        foreach ($d['columns'] as $c) {
            $h .= '<th>' . $e($c) . '</th>';
        }
        $h .= '</tr>';
        foreach ($d['rows'] as $i => $r) {
            $h .= '<tr><td>' . ($i + 1) . '</td>';
            foreach ($r as $cell) {
                $h .= '<td>' . $e($cell) . '</td>';
            }
            $h .= '</tr>';
        }
        if (empty($d['rows'])) {
            $h .= '<tr><td colspan="' . (count($d['columns']) + 1) . '">Tidak ada data</td></tr>';
        }
        $h .= '</table>';
        return $h . '</body></html>';
    }

    private function buildDetail(string $type, int $tahun, string $divisi, string $filter = '', int $bulan = 0): array
    {
        return match ($type) {
            'karyawan', 'komposisi' => $this->detailKaryawan($divisi, $filter),
            'retensi', 'resigns'    => $this->detailResign($divisi, $tahun),
            'hires'                 => $this->detailHires($divisi, $tahun),
            'kehadiran'             => $this->detailKehadiran($divisi, $tahun, $bulan, $filter),
            'ketepatan'             => $this->detailKetepatan($divisi, $tahun, $bulan),
            'tren'                  => $this->detailTren($divisi, $tahun),
            'hari_ini'              => $this->detailHariIni($divisi, $tahun, $filter),
            'cuti'                  => $this->detailCuti($divisi, $tahun, $filter),
            'spj'                   => $this->detailSpj($divisi, $tahun, $bulan),
            'rekrutmen'             => $this->detailRekrutmen($divisi, $tahun, $filter, $bulan),
            'divisi'                => $this->detailDivisi($divisi),
            'agenda'                => $this->detailAgenda($divisi, $tahun),
            'instruktur_ol'         => $this->detailInstrukturOl($divisi, $filter), 
            'penjualan'             => $this->detailPenjualan($divisi, $tahun),
            'maintenance'           => $this->detailMaintenance($divisi, $tahun, $filter),
            'penjualan_kelas'       => $this->detailPenjualanKelas($divisi, $tahun),
            'penjualan_peserta'     => $this->detailPenjualanPeserta($divisi, $tahun),
            'penjualan_nett'        => $this->detailPenjualanNett($divisi, $tahun),
            'penjualan_materi'      => $this->detailPenjualanMateri($divisi, $tahun, $filter),
            default => throw new \InvalidArgumentException('Tipe detail tidak dikenal: ' . $type),
        };
    }

    private function sum(string $label, $value, string $note = ''): array
    {
        return ['label' => $label, 'value' => (string) $value, 'note' => $note];
    }

    private function fase($e): string
    {
        $end = $this->pEff;
        $has = fn($d) => $d && Carbon::parse($d)->startOfDay()->lte($end);
        return $has($e->awal_tetap) ? 'Tetap'
            : ($has($e->awal_kontrak) ? 'Kontrak'
            : ($has($e->awal_probation) ? 'Probation' : 'Belum diisi'));
    }

    private function periodRange(int $tahun, int $bulan = 0): array
    {
        return [$this->pStart->copy(), $this->pEff->copy()];
    }

    private function statusSet(string $filter): array
    {
        if ($filter === 'cuti_sakit') {
            return ['cuti', 'sakit'];
        }
        return isset(self::STATUS_LABEL[$filter]) ? [$filter] : [];
    }

    private function detailInstrukturOl(string $divisi, string $filter): array
    {
        $query = InstrukturOl::query();
        $titleSuffix = '';

        if (str_starts_with($filter, 'lsp:')) {
            $lspName = substr($filter, 4);
            $query->where('core', 'like', "%{$lspName}%");
            $titleSuffix = " - Afiliasi: {$lspName}";
        } elseif (str_starts_with($filter, 'core:')) {
            $core = substr($filter, 5);
            $query->where('core', 'like', "%{$core}%");
            $titleSuffix = " - Keahlian: {$core}";
        }

        $items = $query->orderBy('nama')->get();
        $total = $items->count();

        $rows = $items->map(function ($i) {
            $kelengkapan = [];
            if ($i->cv_path) $kelengkapan[] = 'CV';
            if ($i->ktp_path) $kelengkapan[] = 'KTP';

            return [
                $i->kode_karyawan ?: '-',
                $i->nama,
                $i->core ?: '-',
                $i->no_hp ?: '-',
                $kelengkapan ? implode(', ', $kelengkapan) : 'Belum lengkap',
            ];
        })->values()->all();

        return [
            'title' => 'Data Instruktur OL' . $titleSuffix,
            'formula' => 'Total instruktur OL terdaftar, dikelompokkan berdasarkan core keahlian.',
            'explanation' => 'Data ini mencakup instruktur yang dimigrasi dari karyawan atau ditambahkan manual. Kelengkapan berkas dihitung berdasarkan keberadaan file CV dan KTP.',
            'summary' => [
                $this->sum('Total Instruktur', $total),
                $this->sum('Lengkap CV', $items->whereNotNull('cv_path')->count()),
                $this->sum('Lengkap KTP', $items->whereNotNull('ktp_path')->count()),
            ],
            'columns' => ['Kode', 'Nama', 'Core', 'No. HP', 'Kelengkapan'],
            'rows' => $rows,
        ];
    }

    private function detailPenjualan(string $divisi, int $tahun): array
    {
        $rkms = RKM::with(['materi', 'analisisrkm'])
            ->where('status', '0')
            ->whereBetween('tanggal_awal', $this->periodBounds())
            ->get();

        $bulanNama = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $rows = [];
        $totalKelas = 0; $totalPax = 0; $totalNett = 0;

        for ($m = 1; $m <= 12; $m++) {
            $monthData = $rkms->filter(fn($r) => Carbon::parse($r->tanggal_awal)->month == $m);
            $kelas = $monthData->count();
            $pax = $monthData->sum('pax');
            $nett = $monthData->sum(fn($r) => floatval($r->analisisrkm?->nett_penjualan ?? 0));

            if ($kelas > 0) {
                $rows[] = [
                    $bulanNama[$m - 1],
                    $kelas,
                    number_format($pax),
                    'Rp ' . number_format($nett, 0, ',', '.')
                ];
                $totalKelas += $kelas;
                $totalPax += $pax;
                $totalNett += $nett;
            }
        }

        return [
            'title' => 'Detail Rekap Penjualan Kelas Tahun ' . $tahun,
            'formula' => 'Total Nett = Sum(nett_penjualan) dari analisisrkm yang terkait.',
            'explanation' => 'Menampilkan rekapitulasi jumlah kelas, total peserta (pax), dan total nett penjualan per bulan.',
            'summary' => [
                $this->sum('Total Kelas', $totalKelas),
                $this->sum('Total Pax', $totalPax),
                $this->sum('Total Nett', 'Rp ' . number_format($totalNett, 0, ',', '.')),
            ],
            'columns' => ['Bulan', 'Jumlah Kelas', 'Total Pax', 'Total Nett'],
            'rows' => $rows,
        ];
    }

    private function detailPenjualanKelas(string $divisi, int $tahun): array
    {
        $rkms = RKM::with('materi')
            ->where('status', '0')
            ->whereBetween('tanggal_awal', $this->periodBounds())
            ->get();

        $bulanNama = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $rows = [];
        $total = 0;

        for ($m = 1; $m <= 12; $m++) {
            $count = $rkms->filter(fn($r) => Carbon::parse($r->tanggal_awal)->month == $m)->count();
            if ($count > 0) {
                $rows[] = [$bulanNama[$m - 1], $count];
                $total += $count;
            }
        }

        return [
            'title' => 'Detail Jumlah Kelas per Bulan - Tahun ' . $tahun,
            'formula' => 'Total Kelas = COUNT(id) dari RKM dengan status aktif per bulan.',
            'explanation' => 'Menampilkan distribusi jumlah kelas yang dilaksanakan setiap bulan sepanjang tahun.',
            'summary' => [$this->sum('Total Kelas Sepanjang Tahun', $total)],
            'columns' => ['Bulan', 'Jumlah Kelas'],
            'rows' => $rows,
        ];
    }

    private function detailPenjualanPeserta(string $divisi, int $tahun): array
    {
        $rkms = RKM::with('materi')
            ->where('status', '0')
            ->whereBetween('tanggal_awal', $this->periodBounds())
            ->get();

        $bulanNama = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $rows = [];
        $total = 0;

        for ($m = 1; $m <= 12; $m++) {
            $monthData = $rkms->filter(fn($r) => Carbon::parse($r->tanggal_awal)->month == $m);
            $pax = $monthData->sum('pax');
            $kelas = $monthData->count();
            if ($pax > 0) {
                $avg = $kelas > 0 ? round($pax / $kelas, 1) : 0;
                $rows[] = [$bulanNama[$m - 1], number_format($pax), $kelas, $avg];
                $total += $pax;
            }
        }

        return [
            'title' => 'Detail Jumlah Peserta (Pax) per Bulan - Tahun ' . $tahun,
            'formula' => 'Total Pax = SUM(pax) dari semua kelas per bulan. Rata-rata = Total Pax / Jumlah Kelas.',
            'explanation' => 'Menampilkan total peserta yang terdaftar setiap bulan beserta rata-rata peserta per kelas.',
            'summary' => [
                $this->sum('Total Peserta', number_format($total)),
                $this->sum('Rata-rata per Kelas', round($total / max($rkms->count(), 1), 1) . ' peserta'),
            ],
            'columns' => ['Bulan', 'Total Pax', 'Jumlah Kelas', 'Rata-rata Pax/Kelas'],
            'rows' => $rows,
        ];
    }

    private function detailPenjualanNett(string $divisi, int $tahun): array
    {
        $rkms = RKM::with(['materi', 'analisisrkm'])
            ->where('status', '0')
            ->whereBetween('tanggal_awal', $this->periodBounds())
            ->get();

        $bulanNama = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $rows = [];
        $total = 0;

        for ($m = 1; $m <= 12; $m++) {
            $monthData = $rkms->filter(fn($r) => Carbon::parse($r->tanggal_awal)->month == $m);
            $nett = $monthData->sum(fn($r) => floatval($r->analisisrkm?->nett_penjualan ?? 0));
            $kelas = $monthData->count();
            if ($nett > 0) {
                $avg = $kelas > 0 ? $nett / $kelas : 0;
                $rows[] = [
                    $bulanNama[$m - 1],
                    'Rp ' . number_format($nett, 0, ',', '.'),
                    $kelas,
                    'Rp ' . number_format($avg, 0, ',', '.')
                ];
                $total += $nett;
            }
        }

        return [
            'title' => 'Detail Nett Penjualan per Bulan - Tahun ' . $tahun,
            'formula' => 'Total Nett = SUM(nett_penjualan) dari analisisrkm. Rata-rata = Total Nett / Jumlah Kelas.',
            'explanation' => 'Menampilkan total pendapatan nett setiap bulan beserta rata-rata nett per kelas.',
            'summary' => [
                $this->sum('Total Nett Penjualan', 'Rp ' . number_format($total, 0, ',', '.')),
                $this->sum('Rata-rata per Kelas', 'Rp ' . number_format($total / max($rkms->count(), 1), 0, ',', '.')),
            ],
            'columns' => ['Bulan', 'Total Nett', 'Jumlah Kelas', 'Rata-rata Nett/Kelas'],
            'rows' => $rows,
        ];
    }

    private function detailPenjualanMateri(string $divisi, int $tahun, string $filter = ''): array
    {
        $query = RKM::with('materi')
            ->where('status', '0')
            ->whereBetween('tanggal_awal', $this->periodBounds());

        $materiFilter = '';
        if (str_starts_with($filter, 'materi:')) {
            $materiFilter = substr($filter, 7);
            $query->whereHas('materi', fn($q) => $q->where('nama_materi', 'like', "%{$materiFilter}%"));
        }

        $rkms = $query->get();

        $grouped = $rkms->groupBy('materi_key')->map(function($items) {
            $materiName = $items->first()->materi->nama_materi ?? 'Materi Lainnya';
            $kelas = $items->count();
            $pax = $items->sum('pax');
            $nett = $items->sum(fn($r) => floatval($r->analisisrkm?->nett_penjualan ?? 0));
            $avgPax = $kelas > 0 ? round($pax / $kelas, 1) : 0;
            $avgNett = $kelas > 0 ? $nett / $kelas : 0;

            return [
                'label' => $materiName,
                'kelas' => $kelas,
                'pax' => $pax,
                'nett' => $nett,
                'avg_pax' => $avgPax,
                'avg_nett' => $avgNett,
            ];
        })->sortByDesc('kelas')->values();

        $rows = $grouped->map(fn($m) => [
            $m['label'],
            $m['kelas'],
            number_format($m['pax']),
            $m['avg_pax'],
            'Rp ' . number_format($m['nett'], 0, ',', '.'),
            'Rp ' . number_format($m['avg_nett'], 0, ',', '.'),
        ])->values()->all();

        $totalKelas = $grouped->sum('kelas');
        $totalNett = $grouped->sum('nett');

        return [
            'title' => 'Detail Performa per Materi' . ($materiFilter ? ' - ' . $materiFilter : ''),
            'formula' => 'Grouping berdasarkan materi_key. Avg Nett = Total Nett Materi / Jumlah Kelas Materi.',
            'explanation' => 'Menampilkan performa setiap materi pelatihan berdasarkan jumlah kelas, total peserta, dan total nett penjualan.',
            'summary' => [
                $this->sum('Total Materi', $grouped->count()),
                $this->sum('Total Kelas', $totalKelas),
                $this->sum('Total Nett', 'Rp ' . number_format($totalNett, 0, ',', '.')),
            ],
            'columns' => ['Nama Materi', 'Jumlah Kelas', 'Total Pax', 'Avg Pax', 'Total Nett', 'Avg Nett/Kelas'],
            'rows' => $rows,
        ];
    }

    private function detailMaintenance(string $divisi, int $tahun, string $filter = ''): array
    {
        $query = Maintenance::query()
            ->whereBetween('tanggal', $this->periodBounds())
            ->when($divisi !== 'all', fn($q) => $q->where('divisi', $divisi))
            ->orderBy('tanggal', 'desc');

        if (str_starts_with($filter, 'kategori:')) {
            $query->where('jenis_maintenance', substr($filter, 9));
        }

        $items = $query->get();
        $totalBiaya = $items->sum('biaya');

        $rows = $items->map(fn($item) => [
            'MNT-' . str_pad($item->id, 4, '0', STR_PAD_LEFT),
            $item->jenis_maintenance,
            $item->divisi,
            $item->nama_barang,
            $item->teknisi ?? '-',
            Carbon::parse($item->tanggal)->format('d/m/Y'),
            $item->status,
            'Rp ' . number_format($item->biaya, 0, ',', '.')
        ])->values()->all();

        return [
            'title' => 'Detail Data Maintenance Tahun ' . $tahun,
            'formula' => 'Total Biaya = Sum(biaya) dari semua tiket maintenance.',
            'explanation' => 'Menampilkan daftar seluruh tiket maintenance (Mendatang, On Progress, Selesai) beserta rincian biayanya.',
            'summary' => [
                $this->sum('Total Tiket', $items->count()),
                $this->sum('Total Biaya', 'Rp ' . number_format($totalBiaya, 0, ',', '.')),
            ],
            'columns' => ['ID Service', 'Kategori', 'Divisi', 'Nama Barang', 'Teknisi', 'Tanggal', 'Status', 'Biaya'],
            'rows' => $rows,
        ];
    }

    private function statusFilterLabel(string $filter): string
    {
        return $filter === 'cuti_sakit' ? 'Cuti / Sakit' : (self::STATUS_LABEL[$filter] ?? '');
    }

    private function dailyRows($rows, $emp): array
    {
        return $rows->sortBy('tanggal')->map(function ($r) use ($emp) {
            $e = $emp->get($r['id']);
            $ket = trim((string) ($r['alasan'] ?? ''));
            if (!empty($r['late_notified'])) {
                $ket = trim('Terlambat, sudah ada izin. ' . $ket);
            }
            return [
                Carbon::parse($r['tanggal'])->format('d/m/Y'),
                $e?->nama_lengkap ?? 'Tidak diketahui',
                $e?->divisi ?? '-',
                $e?->jabatan ?? '-',
                self::STATUS_LABEL[$r['status']] ?? $r['status'],
                $r['jam_masuk'] ?? '-',
                (int) round($r['late_minutes'] ?? 0),
                $ket !== '' ? $ket : '-',
            ];
        })->values()->all();
    }

    private function detailKaryawan($divisi, string $filter): array
    {
        $all = $this->headcountQuery($divisi)->orderBy('divisi')->orderBy('nama_lengkap')->get();
        $list = $all;
        $suffix = '';

        if ($filter !== '') {
            if (str_starts_with($filter, 'divisi:')) {
                $dv = substr($filter, 7);
                $list = $all->filter(fn($e) => ($e->divisi ?: '') === $dv);
                $suffix = ' - Divisi ' . ($dv ?: 'Tanpa divisi');
            } else {
                $list = $all->filter(fn($e) => $this->fase($e) === $filter);
                $suffix = ' - ' . $filter;
            }
        }

        $count = fn($f) => $all->filter(fn($e) => $this->fase($e) === $f)->count();

        $rows = $list->map(function ($e) {
            $start = $e->awal_probation ?? $e->awal_kontrak ?? $e->awal_tetap;
            $mo = $start ? max(0, (int) Carbon::parse($start)->diffInMonths($this->pEff)) : null;
            return [
                $e->nip, $this->namaKaryawan($e), $e->divisi ?: '-', $e->jabatan ?: '-', $this->fase($e),
                $start ? Carbon::parse($start)->format('d/m/Y') : '-',
                $mo === null ? '-' : intdiv($mo, 12) . ' thn ' . ($mo % 12) . ' bln',
            ];
        })->values()->all();

        return [
            'title' => 'Total & Komposisi Karyawan (' . $this->pLabel . ')' . $suffix,
            'formula' => 'Total = jumlah karyawan aktif. Status: Tetap jika awal_tetap terisi, Kontrak jika awal_kontrak terisi, selain itu Probation.',
            'explanation' => 'Yang dihitung: status_aktif = 1, bukan Outsource, bukan kode OL%, jabatan bukan "Pilih Jabatan", NIP terisi, bukan divisi Direksi. Masa kerja dihitung dari tanggal awal fase paling awal sampai hari ini.',
            'requirements' => [
                'karyawan.status_aktif, nip, kode_karyawan, jabatan, divisi: filter siapa yang dihitung',
                'karyawan.awal_probation / awal_kontrak / awal_tetap: menentukan status dan masa kerja',
                'Jika "Belum diisi" muncul: ketiga tanggal awal fase kosong, lengkapi di data karyawan',
            ],
            'summary' => [
                $this->sum('Total aktif', $all->count()),
                $this->sum('Tetap', $count('Tetap')),
                $this->sum('Kontrak', $count('Kontrak')),
                $this->sum('Probation', $count('Probation')),
                $this->sum('Belum diisi', $count('Belum diisi'), 'Data tanggal fase kosong'),
                $this->sum('Ditampilkan', $list->count(), $suffix ? trim($suffix, ' -') : 'Semua'),
            ],
            'columns' => ['NIP', 'Nama', 'Divisi', 'Jabatan', 'Status', 'Mulai', 'Masa kerja'],
            'rows' => $rows,
        ];
    }

    private function detailResign($divisi, $tahun): array
    {
        [$start, $end] = $this->yearRange((int) $tahun);

        $aktif = $this->headcountQuery($divisi)->count();
        $list = $this->baseKaryawanQuery($divisi, false)
            ->whereNotNull('resigned_at')
            ->whereBetween('resigned_at', [$start, $end])
            ->orderByDesc('resigned_at')
            ->get();

        $n = $list->count();
        $rate = ($aktif + $n) > 0 ? round($aktif / ($aktif + $n) * 100, 1) : 100;

        return [
            'title' => 'Retensi & Karyawan Resign ' . $this->pLabel,
            'formula' => 'Retensi = Karyawan aktif / (Karyawan aktif + Resign tahun ini) x 100',
            'explanation' => "Contoh perhitungan saat ini: {$aktif} / ({$aktif} + {$n}) x 100 = {$rate}%. Resign dihitung dari tanggal resign pada tahun terpilih, apa pun status aktifnya. Ambang: 90% ke atas sangat baik, 75% baik, 60% cukup (waspada), di bawah 60% kritis.",
            'requirements' => [
                'karyawan.resigned_at: tanggal resign, wajib diisi saat karyawan keluar',
                'karyawan.alasan_resign: alasan keluar (opsional tapi disarankan)',
                'karyawan.status_aktif: harus 0 untuk yang sudah resign agar tidak dihitung aktif',
            ],
            'summary' => [
                $this->sum('Karyawan aktif', $aktif),
                $this->sum('Resign periode', $n),
                $this->sum('Retensi', $rate . '%'),
            ],
            'columns' => ['NIP', 'Nama', 'Divisi', 'Jabatan', 'Tanggal resign', 'Alasan'],
            'rows' => $list->map(fn($e) => [
                $e->nip ?? '-', $this->namaKaryawan($e), $e->divisi ?: '-', $e->jabatan ?: '-',
                Carbon::parse($e->resigned_at)->format('d/m/Y'), $e->alasan_resign ?: '-',
            ])->values()->all(),
        ];
    }

    private function detailHires($divisi, $tahun): array
    {
        [$start, $end] = $this->yearRange((int) $tahun);

        $list = $this->baseKaryawanQuery($divisi)
            ->whereNotNull('awal_probation')
            ->whereBetween('awal_probation', [$start, $end])
            ->orderByDesc('awal_probation')
            ->get();

        return [
            'title' => 'Karyawan Baru ' . $this->pLabel,
            'formula' => 'Karyawan baru = karyawan aktif dengan awal_probation di tahun terpilih',
            'explanation' => 'Tanggal awal probation dianggap sebagai tanggal bergabung. Yang sudah resign tidak dihitung sebagai karyawan baru aktif.',
            'requirements' => ['karyawan.awal_probation: tanggal bergabung, wajib diisi', 'karyawan.status_aktif = 1'],
            'summary' => [$this->sum('Total karyawan baru', $list->count())],
            'columns' => ['NIP', 'Nama', 'Divisi', 'Jabatan', 'Tanggal bergabung', 'Status sekarang'],
            'rows' => $list->map(fn($e) => [
                $e->nip ?? '-', $this->namaKaryawan($e), $e->divisi ?: '-', $e->jabatan ?: '-',
                Carbon::parse($e->awal_probation)->format('d/m/Y'), $this->fase($e),
            ])->values()->all(),
        ];
    }

    private function detailKehadiran($divisi, int $tahun, int $bulan, string $filter): array
    {
        [$start, $end] = $this->periodRange($tahun, $bulan);
        $res = $this->statusRange($start, $end, $divisi);
        $emp = $res['employees']->keyBy('id');
        $s = $this->summarize($res['rows']);
        $set = $this->statusSet($filter);
        $periode = $start->translatedFormat('F Y');

        $common = [
            'formula' => 'Kehadiran = (Hadir + Telat + Izin) / (Total hari kerja - Cuti - Sakit) x 100',
            'explanation' => "Contoh periode {$periode}: ({$s['hadir']} hadir + {$s['telat']} telat + izin) = {$s['attended']} hari hadir dari {$s['expected']} hari wajib = {$s['rate']}%. Cuti dan sakit dikeluarkan dari pembagi. Telat yang sudah punya izin 3 jam dihitung sebagai Izin, bukan Telat. Kolom Izin pada tabel per karyawan menghitung semua hari ber-izin 3 jam, termasuk yang tetap hadir.",
            'requirements' => [
                'Data presensi (jam masuk, waktu keterlambatan, keterangan) dari absensi karyawan',
                'Pengajuan cuti dan sakit yang disetujui (approval_manager)',
                'Izin 3 jam dengan approval 1 atau 2',
                'Kalender libur nasional dan aturan hari kerja (Sabtu hanya Office Boy dan Technical Support)',
                'Tanggal awal probation: karyawan baru dihitung sejak tanggal bergabung',
            ],
            'summary' => [
                $this->sum('Tingkat kehadiran', $s['rate'] . '%', "{$s['attended']} dari {$s['expected']} hari"),
                $this->sum('Hadir tepat waktu', $s['hadir']),
                $this->sum('Telat', $s['telat']),
                $this->sum('Tanpa keterangan', $s['tidak_hadir'], 'Hari kerja tanpa presensi, cuti, sakit, atau izin'),
            ],
        ];

        if ($set) {
            return $common + [
                'title' => "Kehadiran {$periode} - " . $this->statusFilterLabel($filter),
                'columns' => self::DAILY_COLUMNS,
                'rows' => $this->dailyRows($res['rows']->whereIn('status', $set), $emp),
            ];
        }

        $matrix = $this->presence->buildRecapMatrix($res);

        return $common + [
            'title' => 'Kehadiran ' . $periode,
            'columns' => $matrix['headers'],
            'rows' => $matrix['rows'],
        ];
    }

    private function detailKetepatan($divisi, int $tahun, int $bulan): array
    {
        [$start, $end] = $this->periodRange($tahun, $bulan);
        $res = $this->statusRange($start, $end, $divisi);
        $emp = $res['employees']->keyBy('id');
        $s = $this->summarize($res['rows']);

        $rows = $res['rows']->where('status', 'telat')->sortByDesc('late_minutes')->map(function ($r) use ($emp) {
            $e = $emp->get($r['id']);
            return [
                Carbon::parse($r['tanggal'])->format('d/m/Y'),
                $e?->nama_lengkap ?? 'Tidak diketahui',
                $e?->divisi ?? '-',
                $e?->jabatan ?? '-',
                $r['jam_masuk'] ?? '-',
                (int) round($r['late_minutes'] ?? 0),
            ];
        })->values()->all();

        return [
            'title' => 'Keterlambatan ' . $start->translatedFormat('F Y'),
            'formula' => 'Tepat waktu = Hadir tepat waktu / (Hadir + Telat) x 100. Rata-rata telat = rata-rata dari (total menit telat / jumlah hari telat) tiap karyawan yang pernah telat.',
            'explanation' => "Contoh saat ini: {$s['hadir']} / ({$s['hadir']} + {$s['telat']}) x 100 = {$s['punctuality']}%. Rata-rata telat {$s['avg_late']} menit per karyawan. Hari telat yang sudah punya izin 3 jam tidak dihitung sebagai telat.",
            'requirements' => [
                'absensi.jam_masuk dan waktu_keterlambatan: sumber menit keterlambatan',
                'absensi.keterangan: klasifikasi telat/izin/cuti/sakit',
                'Izin 3 jam yang disetujui: mengubah status telat menjadi izin',
            ],
            'summary' => [
                $this->sum('Tepat waktu', $s['punctuality'] . '%'),
                $this->sum('Jumlah telat', $s['telat'], 'kejadian'),
                $this->sum('Telat dengan izin', $res['rows']->where('late_notified', true)->count(), 'dihitung sebagai izin'),
                $this->sum('Rata-rata telat', $s['avg_late'] . ' menit', 'per karyawan'),
            ],
            'columns' => ['Tanggal', 'Nama', 'Divisi', 'Jabatan', 'Jam masuk', 'Telat (menit)'],
            'rows' => $rows,
        ];
    }

    private function detailTren($divisi, int $tahun): array
    {
        $rows = $this->statusRange($this->pStart, $this->pEff, $divisi)['rows'];

        $by = $rows->groupBy(fn($r) => (int) substr($r['tanggal'], 5, 2));
        $out = [];

        foreach (range(1, 12) as $m) {
            $g = $by->get($m, collect());
            $label = Carbon::createFromDate($tahun, $m, 1)->translatedFormat('F');
            if ($g->isEmpty()) {
                $out[] = [$label, '-', '-', '-', '-', '-', '-', '-', '-', '-'];
                continue;
            }
            $c = fn($st) => $g->where('status', $st)->count();
            $s = $this->summarize($g);
            $out[] = [$label, $s['expected'], $c('hadir'), $c('telat'), $c('izin'), $c('cuti'), $c('sakit'), $c('tidak_hadir'), $s['rate'] . '%', $s['late_rate'] . '%'];
        }

        return [
            'title' => 'Tren Kehadiran per Bulan - ' . $this->pLabel,
            'formula' => 'Kehadiran = (Hadir + Telat + Izin) / (Hari kerja - Cuti - Sakit) x 100. Keterlambatan = Telat / (Hadir + Telat) x 100.',
            'explanation' => 'Setiap baris adalah satu bulan. Klik titik bulan pada grafik untuk melihat rincian per karyawan.',
            'requirements' => [
                'Data presensi, cuti, sakit, dan izin seluruh bulan pada tahun terpilih',
                'Kalender libur nasional',
            ],
            'summary' => [],
            'columns' => ['Bulan', 'Hari wajib', 'Hadir', 'Telat', 'Izin', 'Cuti', 'Sakit', 'Tanpa ket.', 'Kehadiran', 'Keterlambatan'],
            'rows' => $out,
        ];
    }

    private function detailHariIni($divisi, int $tahun, string $filter): array
    {
        $ref = $this->pEff->copy();
        $res = $this->statusRange($ref->copy(), $ref->copy(), $divisi);
        $emp = $res['employees']->keyBy('id');
        $today = $res['rows']->where('tanggal', $ref->toDateString());
        $set = $this->statusSet($filter);
        $list = $set ? $today->whereIn('status', $set) : $today;

        return [
            'title' => 'Status Karyawan ' . $ref->translatedFormat('d F Y') . ($set ? ' - ' . $this->statusFilterLabel($filter) : ''),
            'formula' => 'Setiap karyawan dikelompokkan ke satu status hari ini',
            'explanation' => 'Pada hari libur atau akhir pekan, hanya karyawan yang tetap bekerja yang tercatat.',
            'requirements' => ['Presensi hari ini', 'Pengajuan cuti, sakit, dan izin 3 jam beserta status persetujuannya'],
            'summary' => collect(self::STATUS_LABEL)->map(fn($l, $k) => $this->sum($l, $today->where('status', $k)->count()))->values()->all(),
            'columns' => self::DAILY_COLUMNS,
            'rows' => $this->dailyRows($list, $emp),
        ];
    }

    private function detailCuti($divisi, int $tahun, string $filter): array
    {
        $l = $this->leaveData($tahun, $divisi);
        $rows = $filter !== ''
            ? array_values(array_filter($l['rows'], fn($r) => $r[8] === $filter))
            : $l['rows'];

        return [
            'title' => 'Pengajuan Cuti ' . $this->pLabel . ($filter !== '' ? ' - ' . $filter : ''),
            'formula' => '% karyawan cuti = Karyawan dengan cuti disetujui / Karyawan aktif x 100. % persetujuan = Pengajuan disetujui / Total pengajuan x 100.',
            'explanation' => "Contoh saat ini: {$l['pegawai_cuti']} karyawan / {$l['aktif']} karyawan aktif x 100 = {$l['rate_pegawai']}%. Dari {$l['total']} pengajuan, {$l['disetujui']} disetujui = {$l['rate_approval']}%. Satu karyawan yang cuti berkali-kali hanya dihitung satu kali pada persen karyawan.",
            'requirements' => [
                'pengajuancuti.id_karyawan, tanggal_awal, tanggal_akhir, durasi: siapa dan kapan',
                'pengajuancuti.approval_manager dan alasan_manager: status dan catatan atasan',
                'pengajuancuti.tipe, alasan, backup_karyawan1: jenis, alasan, dan pengganti',
                'Jumlah karyawan aktif sebagai pembagi',
            ],
            'summary' => [
                $this->sum('Total pengajuan', $l['total']),
                $this->sum('Disetujui', $l['disetujui']),
                $this->sum('Menunggu', $l['menunggu']),
                $this->sum('Ditolak', $l['ditolak']),
                $this->sum('Karyawan yang cuti', $l['rate_pegawai'] . '%', "{$l['pegawai_cuti']} dari {$l['aktif']} karyawan aktif"),
                $this->sum('Total hari cuti disetujui', $l['hari_cuti'], 'hari'),
            ],
            'columns' => ['Nama', 'Divisi', 'Jabatan', 'Tipe', 'Mulai', 'Selesai', 'Durasi', 'Alasan', 'Status', 'Catatan manager', 'Pengganti'],
            'rows' => $rows,
        ];
    }

    private function detailSpj($divisi, int $tahun, int $bulan): array
    {
        $list = SuratPerjalanan::with('karyawan')
            ->whereBetween('tanggal_berangkat', $this->periodBounds())
            ->when($divisi !== 'all', fn($q) => $q->whereHas('karyawan', fn($s) => $s->where('divisi', $divisi)))
            ->orderByDesc('tanggal_berangkat')->get();

        $fmt = fn($v) => 'Rp ' . number_format($v, 0, ',', '.');
        $total = $list->sum(fn($s) => $this->spjTotal($s));
        $n = $list->count();
        $avg = $n > 0 ? (int) round($total / $n) : 0;

        return [
            'title' => 'SPJ ' . $this->pLabel,
            'formula' => 'Rata-rata per SPJ = Total nilai SPJ / Jumlah SPJ. Perubahan = (Periode ini - Periode sebelumnya) / Periode sebelumnya x 100.',
            'explanation' => "Contoh saat ini: {$fmt($total)} / {$n} SPJ = {$fmt($avg)}. SPJ difilter berdasarkan tanggal berangkat dalam periode terpilih.",
            'requirements' => [
                'surat_perjalanans.tanggal_berangkat: menentukan periode',
                'surat_perjalanans.total: nilai SPJ',
                'surat_perjalanans.karyawan_id (relasi karyawan): untuk filter divisi dan nama',
            ],
            'summary' => [
                $this->sum('Total nilai', $fmt($total)),
                $this->sum('Jumlah SPJ', $n),
                $this->sum('Rata-rata', $fmt($avg)),
            ],
            'columns' => ['Nama', 'Divisi', 'Tanggal berangkat', 'Tujuan', 'Total'],
            'rows' => $list->map(fn($s) => [
                $s->karyawan?->nama_lengkap ?? '-', $s->karyawan?->divisi ?? '-',
                $s->tanggal_berangkat ? Carbon::parse($s->tanggal_berangkat)->format('d/m/Y') : '-',
                $s->tujuan ?? '-',
                $fmt($this->spjTotal($s)),
            ])->values()->all(),
        ];
    }

    private function detailRekrutmen($divisi, int $tahun, string $filter, int $bulan): array
    {
        $all = Pelamar::where('status_aktif', true)
            ->when($divisi !== 'all', fn($q) => $q->where('divisi', $divisi))
            ->whereBetween('tanggal_melamar', $this->periodBounds())
            ->orderByDesc('tanggal_melamar')->get();

        $list = $all;
        $suffix = ' - ' . $this->pLabel;

        if ($filter !== '') {
            $list = $all->where('tahap_rekrutmen', $filter);
            $suffix .= ' - ' . (Pelamar::TAHAP[$filter] ?? $filter);
        }

        $total = $all->count();
        $hired = $all->where('tahap_rekrutmen', 'hired')->count();
        $rej = $all->where('tahap_rekrutmen', 'rejected')->count();
        $conv = $total > 0 ? round($hired / $total * 100, 1) : 0;

        $summary = [
            $this->sum('Total pelamar aktif', $total),
            $this->sum('Dalam proses', $total - $hired - $rej),
        ];
        foreach (Pelamar::TAHAP as $k => $label) {
            $summary[] = $this->sum($label, $all->where('tahap_rekrutmen', $k)->count());
        }
        $summary[] = $this->sum('Konversi', $conv . '%');

        return [
            'title' => 'Pelamar & Tahapan Rekrutmen' . $suffix,
            'formula' => 'Konversi = Diterima / Total pelamar aktif x 100. Persen tiap tahap = Pelamar di tahap itu / Total pelamar aktif x 100.',
            'explanation' => "Contoh saat ini: {$hired} / {$total} x 100 = {$conv}%. Dalam proses = Total - Diterima - Ditolak = " . ($total - $hired - $rej) . '.',
            'requirements' => [
                'pelamars.tahap_rekrutmen: applied, screening, interview, offer, hired, rejected',
                'pelamars.tanggal_melamar: untuk pelamar baru per bulan',
                'pelamars.divisi: untuk filter divisi (jika kosong, filter divisi tidak akan akurat)',
                'pelamars.status_aktif = true',
            ],
            'summary' => $summary,
            'columns' => ['Nama', 'Posisi', 'Divisi', 'Tahap', 'Tanggal melamar', 'Sumber', 'Jadwal interview'],
            'rows' => $list->map(fn($p) => [
                $p->nama_lengkap, $p->jabatan ?: '-', $p->divisi ?: '-', $p->tahap_label,
                $p->tanggal_melamar?->format('d/m/Y') ?? '-', $p->sumber_lamaran ?: '-',
                $p->jadwal_interview?->format('d/m/Y H:i') ?? '-',
            ])->values()->all(),
        ];
    }

    private function detailDivisi($divisi): array
    {
        $data = $this->headcountQuery($divisi)->select('divisi', DB::raw('COUNT(*) as total'))
            ->groupBy('divisi')->orderByDesc('total')->get();
        $sum = (int) $data->sum('total');

        return [
            'title' => 'Karyawan per Divisi',
            'formula' => 'Persentase = Karyawan di divisi / Total karyawan aktif x 100',
            'explanation' => "Total {$sum} karyawan aktif tersebar di {$data->count()} divisi.",
            'requirements' => ['karyawan.divisi dan status_aktif'],
            'summary' => [$this->sum('Total karyawan', $sum), $this->sum('Jumlah divisi', $data->count())],
            'columns' => ['Divisi', 'Jumlah', 'Persentase'],
            'rows' => $data->map(fn($d) => [
                $d->divisi ?: 'Tanpa divisi', (int) $d->total,
                $sum > 0 ? round($d->total / $sum * 100, 1) . '%' : '0%',
            ])->values()->all(),
        ];
    }

    private function detailAgenda($divisi, int $tahun): array
    {
        $u = $this->getUpcoming($divisi, $tahun, null);
        $events = $u['events'];
        $c = $u['counts'];

        return [
            'title' => 'Agenda dan Reminder',
            'formula' => 'Interview 14 hari ke depan + penilaian karyawan yang fasenya berakhir dalam 30 hari (atau sudah terlewat)',
            'explanation' => 'Terlewat = tanggal berakhir fase sudah lewat. Mendesak = berakhir dalam 7 hari. Akan datang = berakhir dalam 30 hari.',
            'requirements' => [
                'pelamars.jadwal_interview: jadwal interview pelamar aktif',
                'karyawan.akhir_probation / akhir_kontrak: tanggal berakhir fase untuk reminder penilaian',
            ],
            'summary' => [
                $this->sum('Terlewat', $c['overdue']),
                $this->sum('Mendesak', $c['urgent']),
                $this->sum('Akan datang', $c['upcoming']),
                $this->sum('Total agenda', count($events)),
            ],
            'columns' => ['Kategori', 'Agenda', 'Keterangan', 'Waktu'],
            'rows' => array_map(fn($e) => [$e['kategori'], $e['title'], $e['subtitle'], $e['datetime']], $events),
        ];
    }
}