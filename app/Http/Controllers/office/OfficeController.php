<?php

namespace App\Http\Controllers\office;

use Carbon\Carbon;
use App\Models\RKM;
use App\Models\Tickets;
use App\Models\karyawan;
use Illuminate\Http\Request;
use App\Models\pengajuancuti;
use App\Models\AbsensiKaryawan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use function PHPUnit\Framework\matches;

class OfficeController extends Controller
{
    public function dashboard()
    {
        // 1. Total Karyawan & Divisi Stats
        $total_karyawan = Karyawan::where('status_aktif', '1')
            ->where('divisi', '!=', 'Direksi')
            ->where('jabatan', '!=', 'GM')
            ->where('id', '!=',  ['36', '38', '45', '46', '47', '48', '49', '52', '53', '54'])
            ->count();

        $karyawan = Karyawan::where('status_aktif', '1')
            ->where('divisi', '!=', 'Direksi')
            ->where('jabatan', '!=', 'GM')
            ->where('id', '!=', ['36', '38', '45', '46', '47', '48', '49', '52', '53', '54'])
            ->get();

        $statsFromDB = $karyawan->groupBy('divisi')->map(function ($items) {
            return [
                'total' => $items->count(),
                'data'  => $items
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
                'nama'  => $namaDivisi,
                'total' => $statsFromDB[$namaDivisi]['total'] ?? 0,
                'color' => $config['color'],
                'icon'  => $config['icon'],
                'data'  => $statsFromDB[$namaDivisi]['data'] ?? collect([]),
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



        return view('office.dashboard', compact(
            'total_karyawan',
            'divisiStats',
            'kehadiranChart',
            'tidakHadirList',
            'ticket',
            'rkm',
            'jumlahPeserta',
            'jumlahInstruktur',
        ));
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
