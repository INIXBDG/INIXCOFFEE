<?php

namespace App\Http\Controllers\office;

use App\Http\Controllers\Controller;
use App\Models\AbsensiKaryawan;
use App\Models\ChecklistKeperluan;
use App\Models\Feedback;
use App\Models\karyawan;
use App\Models\Nilaifeedback;
use App\Models\pengajuancuti;
use App\Models\Perusahaan;
use App\Models\RKM;
use App\Models\tagihanPerusahaan;
use App\Models\trackingTagihanPerusahaan;
use App\Models\Tickets;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

use function PHPUnit\Framework\matches;

class OfficeController extends Controller
{
    public function dashboard(Request $request)
    {
        // 1. Total Karyawan & Divisi Stats
        $total_karyawan = karyawan::where('status_aktif', '1')
            ->where('divisi', '!=', 'Direksi')
            ->where('jabatan', '!=', 'GM')
            ->where('id', '!=', ['36', '38', '45', '46', '47', '48', '49', '52', '53', '54'])
            ->count();

        $karyawan = Karyawan::where('status_aktif', '1')
            ->where('divisi', '!=', 'Direksi')
            ->where('jabatan', '!=', 'GM')
            ->where('id', '!=', ['36', '38', '45', '46', '47', '48', '49', '52', '53', '54'])
            ->get();

        $statsFromDB = $karyawan->groupBy('divisi')->map(function ($items) {
            return [
                'total' => $items->count(),
                'data' => $items
            ];
        });

        $divisiConfig = [
            'Office' => ['color' => 'primary', 'icon' => 'bx bx-building-house'],
            'Education' => ['color' => 'success', 'icon' => 'bx bx-book'],
            'Sales & Marketing' => ['color' => 'warning', 'icon' => 'bx bx-line-chart'],
            'IT Service Management' => ['color' => 'info', 'icon' => 'bx bx-cog'],
        ];

        $divisiStats = [];
        foreach ($divisiConfig as $namaDivisi => $config) {
            $divisiStats[] = [
                'nama' => $namaDivisi,
                'total' => $statsFromDB[$namaDivisi]['total'] ?? 0,
                'color' => $config['color'],
                'icon' => $config['icon'],
                'data' => $statsFromDB[$namaDivisi]['data'] ?? collect([]),
            ];
        }

        // 2. Grafik Kehadiran + Karyawan Tidak Hadir
        $today = Carbon::today();
        $sevenDaysAgo = Carbon::today()->subDays(7);

        // Ambil data absensi 7 hari terakhir
        $absensi7Hari = AbsensiKaryawan::whereBetween('tanggal', [$sevenDaysAgo, $today])
            ->whereIn('id_karyawan', $karyawan->pluck('id'))
            ->get()
            ->groupBy('tanggal');

        // Hitung hadir per hari
        $kehadiranData = [];
        $labels = [];
        $tidakHadirList = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateStr = $date->format('Y-m-d');
            $label = $date->translatedFormat('D, d M');

            $labels[] = $label;

            $hadirHariIni = $absensi7Hari->get($dateStr, collect())->count();
            $totalKaryawan = $karyawan->count();
            $kehadiranData[] = $hadirHariIni;

            // Cek siapa yang tidak hadir HARI INI
            if ($i == 0) {
                $karyawanHadir = $absensi7Hari->get($dateStr, collect())->pluck('id_karyawan');
                $tidakHadir = $karyawan->whereNotIn('id', $karyawanHadir);

                $tidakHadirList = $tidakHadir->map(function ($k) {
                    return [
                        'nama' => $k->nama_lengkap,
                        'divisi' => $k->divisi,
                    ];
                })->values();
            }
        }

        $kehadiranChart = [
            'labels' => $labels,
            'data' => $kehadiranData,
        ];

        // 3. Laporan Ticketing
        $ticket = Tickets::where('status', '!=', 'Selesai')
            ->latest()
            ->take(7)
            ->get();

        // 4. RKM
        $rkm = RKM::with('materi', 'perusahaan', 'peluang')
            ->where('tanggal_awal', '<=', Carbon::now())
            ->where('tanggal_akhir', '>=', Carbon::now())
            ->where('status', '0')
            ->get();

        // 5. Jumlah Peserta
        $jumlahPeserta = RKM::where('tanggal_awal', '<=', Carbon::now())
            ->where('tanggal_akhir', '>=', Carbon::now())
            ->where('status', '0')
            ->sum('pax');

        // 6. Jumlah Instruktur
        $jumlahInstruktur = RKM::where('tanggal_awal', '<=', now())
            ->where('tanggal_akhir', '>=', now())
            ->where('status', '0')
            ->get()
            ->sum(
                fn($rkm) =>
                collect([
                    $rkm->instruktur_key,
                    $rkm->instruktur_key2,
                    $rkm->asisten_key,
                ])
                    ->filter(fn($v) => $v !== '-' && !is_null($v))
                    ->count()
            );


        // detail rkm
        $now = Carbon::now();
        $startOfThisWeek = $now->copy()->startOfWeek();
        $endOfThisWeek = $now->copy()->endOfWeek();
        $startOfLastWeek = $now->copy()->subWeek()->startOfWeek();
        $endOfLastWeek = $now->copy()->subWeek()->endOfWeek();
        
        $startDate = $startOfLastWeek;
        $endDate = $endOfThisWeek;

        $rkms = RKM::with(['materi', 'peluang', 'rekomendasilanjutan'])
            ->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
            ->whereBetween('r_k_m_s.tanggal_awal', [$startDate, $endDate])
            ->whereDoesntHave('peluang', function ($query) {
                $query->where('tentatif', 1); // Exclude RKM records where peluang.tentatif = 1
            })
            ->select(
                DB::raw('GROUP_CONCAT(r_k_m_s.id SEPARATOR ", ") AS id'), // Gabungkan semua id
                DB::raw('GROUP_CONCAT(r_k_m_s.id SEPARATOR ", ") AS id_all'), // Gabungkan semua id
                'r_k_m_s.materi_key',
                'r_k_m_s.ruang',
                'r_k_m_s.metode_kelas',
                'r_k_m_s.event',
                DB::raw('GROUP_CONCAT(r_k_m_s.exam SEPARATOR ", ") AS exam'), // Gabungkan semua exam
                DB::raw('GROUP_CONCAT(r_k_m_s.makanan SEPARATOR ", ") AS makanan'), // Gabungkan semua makanan
                DB::raw('GROUP_CONCAT(r_k_m_s.instruktur_key SEPARATOR ", ") AS instruktur_all'),
                DB::raw('GROUP_CONCAT(r_k_m_s.perusahaan_key SEPARATOR ", ") AS perusahaan_all'),
                DB::raw('GROUP_CONCAT(r_k_m_s.sales_key SEPARATOR ", ") AS sales_all'),
                DB::raw('CASE WHEN SUM(r_k_m_s.status = 0) > 0 THEN 0 ELSE MIN(r_k_m_s.status) END AS status_all'),
                DB::raw('SUM(r_k_m_s.pax) AS total_pax'),
                'r_k_m_s.tanggal_awal',
                DB::raw('MAX(r_k_m_s.tanggal_akhir) AS tanggal_akhir')
            )
            ->groupBy(
                'r_k_m_s.materi_key',
                'r_k_m_s.ruang',
                'r_k_m_s.metode_kelas',
                'r_k_m_s.event',
                'r_k_m_s.tanggal_awal'
            )
            ->orderBy('status_all', 'asc')
            ->orderBy('r_k_m_s.tanggal_awal', 'asc')
            ->get();
        $rkms->each(function ($row) {
            if ($row->perusahaan_all) {
                $perusahaan_ids = explode(', ', $row->perusahaan_all);

                $row->perusahaan = Perusahaan::whereIn('id', $perusahaan_ids)->get();
            } else {
                $row->perusahaan = collect();
            }
        });

        foreach ($rkms as $detail_rkm) {
            $singleId = explode(',', $detail_rkm->id)[0];
            $singleId = trim($singleId);
            
            $checklist = ChecklistKeperluan::where('id_rkm', $singleId)->first();
            $detail_rkm->checklist = $checklist;

            if ($checklist) {
                $totalItem = 5;

                $checked = 
                    ($checklist->materi ? 1 : 0) +
                    ($checklist->kelas ? 1 : 0) +
                    ($checklist->cb ? 1 : 0) +
                    ($checklist->maksi ? 1 : 0) +
                    ($checklist->keperluan_kelas ? 1 : 0);

                $detail_rkm->checklist_status = round(($checked / $totalItem) * 100);
            } else {
                $detail_rkm->checklist_status = 0;
            }
        }

        $endOfNextWeek = $now->copy()->addWeek()->endOfWeek();
        // Tagihan Perusaaan
        $trackingTagihanPerusahaans = trackingTagihanPerusahaan::with('tagihanPerusahaan')
            ->whereHas('tagihanPerusahaan', function ($q) use ($startOfThisWeek, $endOfNextWeek) {
                $q->whereBetween('tanggal_perkiraan_selesai', [$startOfThisWeek, $endOfNextWeek]);
            })
            ->orderByDesc('created_at')
            ->get(); 

        return view('office.dashboard', compact(
            'total_karyawan',
            'divisiStats',
            'kehadiranChart',
            'tidakHadirList',
            'ticket',
            'rkm',
            'jumlahPeserta',
            'jumlahInstruktur',
            'rkms',
            'trackingTagihanPerusahaans'
        ));
    }

    public function getNilaiInstruktur(Request $request)
    {
        $filter = $request->filter;
        $value = $request->value;
        $tahun = $request->tahun ?? now()->year;

        $query = Nilaifeedback::with('rkm.instruktur', 'rkm.instruktur2', 'rkm.asisten');

        if ($filter === 'tahun' && is_numeric($value)) {
            $query->whereYear('created_at', $value);
        } elseif ($filter === 'bulan' && is_numeric($value)) {
            $query->whereYear('created_at', $tahun)
                ->whereMonth('created_at', $value);
        } elseif ($filter === 'triwulan' && is_numeric($value)) {
            $bulanMulai = ($value - 1) * 3 + 1;
            $bulanSelesai = $bulanMulai + 2;

            $query->whereYear('created_at', $tahun)
                ->whereBetween(DB::raw('MONTH(created_at)'), [$bulanMulai, $bulanSelesai]);
        }

        $feedbacks = $query->get();

        if ($feedbacks->count() === 0) {
            return response()->json([]);
        }

        $groupByInstruktur = $feedbacks->groupBy(function ($item) {
            return $item->rkm->instruktur->id ?? null;
        });

        $result = [];

        foreach ($groupByInstruktur as $instrukturId => $items) {
            if (!$instrukturId)
                continue;

            $avgIU = collect(['I1', 'I2', 'I3', 'I4', 'I5', 'I6', 'I7', 'I8'])
                ->map(fn($i) => $items->avg($i))
                ->avg();

            $avgI2 = collect(['I1b', 'I2b', 'I3b', 'I4b', 'I5b', 'I6b', 'I7b', 'I8b'])
                ->map(fn($i) => $items->avg($i))
                ->avg();

            $avgIas = collect(['I1as', 'I2as', 'I3as', 'I4as', 'I5as', 'I6as', 'I7as', 'I8as'])
                ->map(fn($i) => $items->avg($i))
                ->avg();

            $nilaiAkhir = collect([$avgIU, $avgI2, $avgIas])->avg();

            $result[] = [
                'id_instruktur' => $instrukturId,
                'nama_instruktur' => $items->first()->rkm->instruktur->nama_lengkap,
                'nilai_instruktur' => round($nilaiAkhir, 2),
            ];
        }

        return response()->json($result);

    }

    public function exportPdf(Request $request)
    {
        $filter = $request->filter;
        $value = $request->value;
        $tahun = $request->tahun ?? now()->year;

        $query = Nilaifeedback::with('rkm.instruktur');

        // === FILTER ===
        if ($filter === 'tahun' && is_numeric($value)) {
            $query->whereYear('created_at', $value);
            $rentangWaktu = "Tahun $value";

        } elseif ($filter === 'bulan' && is_numeric($value)) {
            $query->whereYear('created_at', $tahun)
                ->whereMonth('created_at', $value);

            $rentangWaktu = \Carbon\Carbon::createFromDate($tahun, $value, 1)
                ->translatedFormat('F Y');

        } elseif ($filter === 'triwulan' && is_numeric($value)) {
            $bulanMulai = ($value - 1) * 3 + 1;
            $bulanSelesai = $bulanMulai + 2;

            $query->whereYear('created_at', $tahun)
                ->whereBetween(DB::raw('MONTH(created_at)'), [$bulanMulai, $bulanSelesai]);

            $rentangWaktu = "Triwulan $value Tahun $tahun";
        } else {
            $rentangWaktu = "Semua Data";
        }

        $feedbacks = $query->get();

        // === GROUP & HITUNG NILAI ===
        $data = $feedbacks
            ->filter(fn($f) => $f->rkm && $f->rkm->instruktur)
            ->groupBy(fn($f) => $f->rkm->instruktur->nama_lengkap)
            ->map(function ($items) {

                $instruktur = $items->first()->rkm->instruktur;

                return [
                    'nama' => $instruktur->nama_lengkap,
                    'nilai' => round(
                        $items->avg(function ($row) {
                            return collect([
                                $row->I1,
                                $row->I2,
                                $row->I3,
                                $row->I4,
                                $row->I5,
                                $row->I6,
                                $row->I7,
                                $row->I8
                            ])->avg();
                        }),
                        2
                    )
                ];
            })
            ->values();


        $pdf = Pdf::loadView('office.feedbackinstrukturpdf', [
            'data' => $data,
            'rentangWaktu' => $rentangWaktu
        ])->setPaper('A4', 'portrait');

        return $pdf->download('Laporan_Feedback_Instruktur.pdf');
    }

    public function dataCuti(Request $request)
    {
        Carbon::setLocale('id');

        $filter = $request->filter;
        $value  = $request->value;

        $tahun = is_numeric($request->tahun)
            ? (int) $request->tahun
            : now()->year;

        $query = pengajuancuti::join('karyawans', 'pengajuancutis.id_karyawan', '=', 'karyawans.id')
            ->where('pengajuancutis.approval_manager', '1');

        $rentangWaktu = '';

        if ($filter === 'tahun' && is_numeric($value)) {

            $query->whereYear('pengajuancutis.tanggal_awal', $value);
            $rentangWaktu = ' Tahun ' . $value;

        } elseif ($filter === 'bulan' && is_numeric($value)) {

            $query->whereYear('pengajuancutis.tanggal_awal', $tahun)
                ->whereMonth('pengajuancutis.tanggal_awal', $value);

            $rentangWaktu = Carbon::createFromDate($tahun, $value, 1)
                ->translatedFormat('F Y');

        } elseif ($filter === 'triwulan' && is_numeric($value)) {

            $bulanMulai   = ($value - 1) * 3 + 1;
            $bulanSelesai = $bulanMulai + 2;

            $query->whereYear('pengajuancutis.tanggal_awal', $tahun)
                ->whereBetween(
                    DB::raw('MONTH(pengajuancutis.tanggal_awal)'),
                    [$bulanMulai, $bulanSelesai]
                );

            $rentangWaktu = "Triwulan $value Tahun $tahun";
        }

        $dataCuti = $query->select(
                'karyawans.id',
                'karyawans.nama_lengkap',
                DB::raw('COUNT(*) as total_cuti')
            )
            ->groupBy('karyawans.id', 'karyawans.nama_lengkap')
            ->orderByDesc('total_cuti')
            ->get();

        if ($request->boolean('export')){
            $pdf = Pdf::loadView('office.daftarCutiPdf', compact('dataCuti', 'rentangWaktu'));
            return $pdf->download('Laporan_Cuti.pdf');
        }

        return response()->json([
            'labelCuti'    => $dataCuti->pluck('nama_lengkap'),
            'totalCuti'    => $dataCuti->pluck('total_cuti'),
            'rentangWaktu' => $rentangWaktu ?: 'Semua Data'
        ]);
    }

    public function dataMengajar(Request $request){

         Carbon::setLocale('id');

        $filter = $request->filter;
        $value  = $request->value;

        $tahun = is_numeric($request->tahun)
            ? (int) $request->tahun
            : now()->year;

        $query = DB::table('r_k_m_s')
            ->select('instruktur_key as kode_karyawan', 'tanggal_awal')
            ->whereNotNull('instruktur_key')
            ->unionAll(
                DB::table('r_k_m_s')
                    ->select('instruktur_key2 as kode_karyawan', 'tanggal_awal')
                    ->whereNotNull('instruktur_key2')
            )
            ->unionAll(
                DB::table('r_k_m_s')
                    ->select('asisten_key as kode_karyawan', 'tanggal_awal')
                    ->whereNotNull('asisten_key')
            );

        $dataMengajar = DB::table(DB::raw("({$query->toSql()}) as t"))
            ->mergeBindings($query)
            ->join('karyawans', 't.kode_karyawan', '=', 'karyawans.kode_karyawan')
            ->when($filter === 'tahun' && is_numeric($value), fn($query) => 
                $query->whereYear('t.tanggal_awal', $value))
            ->when($filter === 'bulan' && is_numeric($value), fn($query) => 
                $query->whereYear('t.tanggal_awal', $tahun)
                    ->whereMonth('t.tanggal_awal', $value))
            ->when($filter === 'triwulan' && is_numeric($value), function ($query) use ($value, $tahun) {
                $bulanMulai = ($value - 1) * 3 + 1;
                $query->whereYear('t.tanggal_awal', $tahun)
                    ->whereBetween( DB::raw('MONTH(t.tanggal_awal)'), [$bulanMulai, $bulanMulai + 2]);
            })
            ->select('t.kode_karyawan', 'karyawans.nama_lengkap', DB::raw('COUNT(*) as total_mengajar'))
            ->groupBy('t.kode_karyawan', 'karyawans.nama_lengkap')
            ->orderByDesc('total_mengajar')
            ->get()
            ->map(function ($item) {
            return [
                'namaKaryawan' => $item->nama_lengkap,
                'kodeKaryawan' => $item->kode_karyawan,
                'totalMengajar' => $item->total_mengajar,
            ];
        });

        $rentangWaktu = match ($filter) {
            'tahun' => "Tahun $value",
            'bulan' => Carbon::createFromDate($tahun, $value, 1)->translatedFormat('F Y'),
            'triwulan' => "Triwulan $value Tahun $tahun",
            default => ""
        };

        if ($request->boolean('exportTotalMengajar')){
            $pdf = Pdf::loadView('office.totalMengajarPdf', compact('dataMengajar', 'rentangWaktu'));
            return $pdf->download('Laporan_Total_Mengajar.pdf');
        }

        return response()->json([
            'dataMengajar' => $dataMengajar,
            'rentangWaktu' => $rentangWaktu
            ]);
    }
}
