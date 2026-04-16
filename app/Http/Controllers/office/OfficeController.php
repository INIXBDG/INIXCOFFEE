<?php

namespace App\Http\Controllers\office;

use App\Exports\ChecklistRkmExport;
use App\Http\Controllers\Controller;
use App\Models\AbsensiKaryawan;
use App\Models\AdministrasiKaryawan;
use App\Models\ChecklistKeperluan;
use App\Models\Feedback;
use App\Models\HariLibur;
use App\Models\karyawan;
use App\Models\Nilaifeedback;
use App\Models\outstanding;
use App\Models\pengajuancuti;
use App\Models\Perusahaan;
use App\Models\RKM;
use App\Models\tagihanPerusahaan;
use App\Models\Tickets;
use App\Models\trackingTagihanPerusahaan;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;

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

        $rkms = RKM::with([
            'materi',
            'peluang',
            'rekomendasilanjutan',
            'perusahaan',
            'instruktur',
            'sales'
        ])
        ->whereBetween('tanggal_awal', [$startDate, $endDate])
        ->whereDoesntHave('peluang', function ($query) {
            $query->where('tentatif', 1);
        })
        ->orderBy('status', 'asc')
        ->orderBy('tanggal_awal', 'asc')
        ->get()
        ->groupBy(function ($item) {
            return $item->materi_key . '|' .
                $item->ruang . '|' .
                $item->metode_kelas . '|' .
                $item->event . '|' .
                $item->tanggal_awal;
        })

        ->map(function ($items) {

            $first = $items->first();

            return (object) [
                'id' => $items->pluck('id')->implode(', '),
                'id_all' => $items->pluck('id')->implode(', '),
                'materi_key' => $first->materi_key,
                'ruang' => $first->ruang,
                'metode_kelas' => $first->metode_kelas,
                'event' => $first->event,
                'exam' => $items->pluck('exam')->implode(', '),
                'makanan' => $items->pluck('makanan')->implode(', '),
                'instruktur_all' => $items->pluck('instruktur_key')->implode(', '),
                'perusahaan_all' => $items->pluck('perusahaan_key')->implode(', '),
                'sales_all' => $items->pluck('sales_key')->implode(', '),
                'status_all' => $items->contains('status', 0)
                    ? 0
                    : $items->min('status'),
                'total_pax' => $items->sum('pax'),
                'tanggal_awal' => $first->tanggal_awal,
                'tanggal_akhir' => $items->max('tanggal_akhir'),
                'materi' => $first->materi,
                'peluang' => $first->peluang,
                'rekomendasilanjutan' => $first->rekomendasilanjutan,
                'perusahaan' => $items->pluck('perusahaan')
                    ->filter()
                    ->unique('id')
                    ->values(),
            ];
        })

        ->values(); 

        foreach ($rkms as $detail_rkm) {

            $singleId = trim(explode(',', $detail_rkm->id)[0]);

            $checklists = ChecklistKeperluan::where('id_rkm', $singleId)
                ->with('subChecklistKeperluans')
                ->whereNotNull('tanggal_keperluan')
                ->orderBy('tanggal_keperluan', 'asc')
                ->get()
                ->keyBy('tanggal_keperluan');

            $detail_rkm->checklists = $checklists;

            foreach ($checklists as $checklist => $item) {

                $progress = 0;

                if ($detail_rkm->metode_kelas === 'Offline') {
                    // ===== Materi =====
                    $materiChecked =
                        ($item->subChecklistKeperluans?->materi_module ? 1 : 0) +
                        ($item->subChecklistKeperluans?->materi_elearning ? 1 : 0);
    
                    $progress += ($materiChecked / 2) * 20;
    
                    // ===== Kelas =====
                    if ($item->kelas) {
                        $progress += 20;
                    }
    
                    // ===== CB =====
                    $cbChecked =
                        ($item->subChecklistKeperluans?->cb_instruktur ? 1 : 0) +
                        ($item->subChecklistKeperluans?->cb_peserta ? 1 : 0);
    
                    $progress += ($cbChecked / 2) * 20;
    
                    // ===== Maksi =====
                    $maksiChecked =
                        ($item->subChecklistKeperluans?->maksi_instruktur ? 1 : 0) +
                        ($item->subChecklistKeperluans?->maksi_peserta ? 1 : 0);
    
                    $progress += ($maksiChecked / 2) * 20;
    
                    // ===== Keperluan Kelas =====
                    $kelasChecked =
                        ($item->subChecklistKeperluans?->kelas_ac ? 1 : 0) +
                        ($item->subChecklistKeperluans?->kelas_jam ? 1 : 0) +
                        ($item->subChecklistKeperluans?->kelas_buku ? 1 : 0) +
                        ($item->subChecklistKeperluans?->kelas_pulpen ? 1 : 0) +
                        ($item->subChecklistKeperluans?->kelas_permen ? 1 : 0) +
                        ($item->subChecklistKeperluans?->kelas_camilan ? 1 : 0) +
                        ($item->subChecklistKeperluans?->kelas_minuman ? 1 : 0) +
                        ($item->subChecklistKeperluans?->kelas_lampu ? 1 : 0) +
                        ($item->subChecklistKeperluans?->kelas_kondisi_kebersihan ? 1 : 0);
    
                    $progress += ($kelasChecked / 9) * 20;

                    $item->progress = round($progress);
                } else {
                    $totalKategori = 3;
                    $kategoriSelesai = 0;

                    // ===== Materi =====
                    $totalMateri = 2;
                    $materiChecked =
                        ($item->subChecklistKeperluans?->materi_module ? 1 : 0) +
                        ($item->subChecklistKeperluans?->materi_elearning ? 1 : 0);
    
                    $kategoriSelesai += $materiChecked / $totalMateri;

                    // ===== CB =====
                    $kategoriSelesai += ($item->subChecklistKeperluans?->cb_instruktur ? 1 : 0);
    
                    // ===== Maksi =====
                    $kategoriSelesai += ($item->subChecklistKeperluans?->maksi_instruktur ? 1 : 0);

                    $item->progress = round(($kategoriSelesai / $totalKategori) * 100);
                }
            }
        }

        $endOfNextWeek = $now->copy()->addWeek()->endOfWeek();
        // Tagihan Perusaaan
        $trackingTagihanPerusahaans = trackingTagihanPerusahaan::with('tagihanPerusahaan')
            ->whereBetween('tanggal_perkiraan_selesai', [$startOfThisWeek, $endOfNextWeek])
            ->orderByDesc('created_at')
            ->get(); 
        
        $administrasis = AdministrasiKaryawan::orderBy('dateline', 'desc')
            ->where('status', '!=', 'selesai')
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
            'trackingTagihanPerusahaans',
            'administrasis'
        ));
    }

public function TableOutstanding(Request $request)
{
    $query = outstanding::with('rkm.perusahaan', 'rkm.materi', 'rkm.sales', 'rkm.invoice')
        ->whereYear('created_at', Carbon::now()->year)
        ->whereHas('rkm')
        ->latest();

    if ($search = $request->search) {
        $query->where(function ($q) use ($search) {
            $q->whereHas('rkm.materi', function ($m) use ($search) {
                $m->where('nama_materi', 'LIKE', "%{$search}%");
            })
            ->orWhereHas('rkm.perusahaan', function ($p) use ($search) {
                $p->where('nama_perusahaan', 'LIKE', "%{$search}%");
            })
            ->orWhere('sales_key', 'LIKE', "%{$search}%");
        });
    }

    $perPage = $request->get('length', 10);

    $outstanding = $query->paginate($perPage);

    $data = $outstanding->map(function ($item) {
        if ($item->status_pembayaran == 0 && is_null($item->tanggal_bayar)) {
            $status = "Belum Bayar";
        } elseif ($item->status_pembayaran == 1 && $item->tanggal_bayar) {
            if ($item->tanggal_bayar <= $item->due_date) {
                $status = "Tepat Waktu";
            } else {
                $status = "Terlambat";
            }
        } else {
            $status = "Belum Bayar";
        }

        if ($status === "Belum Bayar") {
            $info = '-';
        } elseif ($item->rkm->invoice?->amount !== null && (int) $item->rkm->invoice->amount) {
            $info = 'Sesuai';
        } else {
            $info = 'Tidak Sesuai';
        }

        $admin_transfer = 0;
        $nominal_pph23 = 0;
        $nominal_ppn = 0;
        $jumlah_potongan_as_array = [];

        $jenis_potongan_raw = $item->jenis_potongan;
        $jumlah_potongan_raw = $item->jumlah_potongan;

        if ($jenis_potongan_raw && $jenis_potongan_raw !== '-' && $jumlah_potongan_raw && $jumlah_potongan_raw !== '-') {
            if (is_string($jenis_potongan_raw)) {
                $jenis_arr = array_map('trim', explode(',', $jenis_potongan_raw));
            } elseif (is_array($jenis_potongan_raw)) {
                $jenis_arr = [];
                foreach ($jenis_potongan_raw as $jp) {
                    if (is_string($jp)) {
                        $jenis_arr[] = trim($jp);
                    } elseif (is_object($jp) && isset($jp->jenis)) {
                        $jenis_arr[] = trim($jp->jenis);
                    } elseif (is_array($jp) && isset($jp['jenis'])) {
                        $jenis_arr[] = trim($jp['jenis']);
                    }
                }
            } else {
                $jenis_arr = [];
            }

            if (is_string($jumlah_potongan_raw)) {
                $jumlah_arr_temp = array_map('trim', explode(',', $jumlah_potongan_raw));
                $jumlah_arr = array_map('intval', $jumlah_arr_temp);
            } elseif (is_array($jumlah_potongan_raw)) {
                $jumlah_arr = [];
                foreach ($jumlah_potongan_raw as $jp) {
                    if (is_numeric($jp)) {
                        $jumlah_arr[] = (int)$jp;
                    } elseif (is_object($jp) && isset($jp->jumlah)) {
                        $jumlah_arr[] = (int)$jp->jumlah;
                    } elseif (is_array($jp) && isset($jp['jumlah'])) {
                        $jumlah_arr[] = (int)$jp['jumlah'];
                    }
                }
            } else {
                $jumlah_arr = [];
            }

            $jumlah_potongan_as_array = $jumlah_arr; 

            foreach ($jenis_arr as $index => $jenis) {
                $jumlah = isset($jumlah_arr[$index]) ? $jumlah_arr[$index] : 0;

                if (stripos($jenis, 'Admin Transfer') !== false) {
                    $admin_transfer = $jumlah;
                } elseif (stripos($jenis, 'Nominal PPH23') !== false) {
                    $nominal_pph23 = $jumlah;
                } elseif (stripos($jenis, 'Nominal PPN') !== false) {
                    $nominal_ppn = $jumlah;
                }
            }
        }

        return [
            'perusahaan' => $item->rkm->perusahaan->nama_perusahaan ?? '-',
            'kelas' => $item->rkm->materi->nama_materi ?? '-',
            'sales' => $item->sales_key ?? '-',
            'tanggal' => $item->rkm->tanggal_akhir,
            'tagihan' => $item->rkm->invoice?->amount !== null ? (int) $item->rkm->invoice->amount : '-',
            'tenggat_waktu' => $item->due_date ?? '-',
            'tanggal_bayar' => $item->tanggal_bayar ?? '-',
            'nominal_pembayaran' => $item->rkm->invoice?->amount !== null ? (int) $item->rkm->invoice->amount : '-',
            'admin_transfer' => $admin_transfer,
            'nominal_pph23' => $nominal_pph23,
            'nominal_ppn' => $nominal_ppn,
            'jumlah_potongan' => !empty($jumlah_potongan_as_array) ? implode(', ', $jumlah_potongan_as_array) : '-',
            'uang_diterima' => (int) $item->jumlah_pembayaran ?? '-',
            // 'total' => $item->jumlah_pembayaran
            //     ? ($item->jumlah_pembayaran + array_sum($jumlah_potongan_as_array))
            //     : '-',
            'status' => $status,
            'info' => $info,
        ];
    });

    return response()->json([
        'data' => $data,
        'current_page' => $outstanding->currentPage(),
        'last_page' => $outstanding->lastPage(),
        'total' => $outstanding->total(),
    ]);
}

    public function GrafikOutstanding(Request $request)
    {
        $year = $request->year ?? Carbon::now()->year;

        $data = outstanding::whereYear('created_at', $year)->get();

        $belum_bayar = 0;
        $tepat_waktu = 0;
        $terlambat = 0;

        foreach ($data as $item) {

            if ($item->status_pembayaran == 0 && is_null($item->tanggal_bayar)) {
                $belum_bayar++;
            }
            elseif ($item->status_pembayaran == 1 && $item->tanggal_bayar) {

                if ($item->tanggal_bayar <= $item->due_date) {
                    $tepat_waktu++;
                } else {
                    $terlambat++;
                }
            }
        }

        return response()->json([
            'labels' => ['Belum Bayar', 'Tepat Waktu', 'Terlambat'],
            'data' => [$belum_bayar, $tepat_waktu, $terlambat],
            'total' => $belum_bayar + $tepat_waktu + $terlambat,
        ]);
    }

    public function GrafikKetepatanWaktu(Request $request)
    {
        $year = $request->year ?? Carbon::now()->year;

        $data = outstanding::with('rkm.invoice')
            ->whereYear('created_at', $year)
            ->get();

        $total = $data->count();

        $sesuai = $data->filter(function ($item) {
            return optional($item->rkm?->invoice)->amount !== null;
        })->count();

        $persen = $total > 0 ? round(($sesuai / $total) * 100, 2) : 0;

        return response()->json([
            'labels' => ['Sesuai', 'Tidak Sesuai'],
            'data' => [$sesuai, $total - $sesuai],
            'persen' => $persen,
        ]);
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

    // Function hari Libur

    public function dataHariLibur($year)
    {
        $response = HariLibur::where('year', $year)->get();

        return response()->json($response);
    }

    public function storeHariLibur(Request $request)
    {
        $request->validate([
            'nama' => 'required|string',
            'tanggal' => 'required|date',
        ]);

        HariLibur::create([
            'nama' => $request->nama,
            'tanggal' => $request->tanggal,
            'year' => Carbon::parse($request->tanggal)->year,
            'tipe' => 'perusahaan',
        ]);

        return redirect()->back()->with('success_libur', 'Hari libur berhasil ditambahkan.');
    }

    public function deleteHariLibur($id)
    {
        $hariLibur = HariLibur::findOrFail($id);
        $hariLibur->delete();

        return redirect()->back()->with('success_libur', 'Hari libur berhasil dihapus.');
    }

    public function editHariLibur($id)
    {
        $hariLibur = HariLibur::findOrFail($id);

        return response()->json($hariLibur);
    }

    public function updateHariLibur(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string',
            'tanggal' => 'required|date',
        ]);

        $hariLibur = HariLibur::findOrFail($id);
        $hariLibur->update([
            'nama' => $request->nama ?? $hariLibur->nama,
            'tanggal' => $request->tanggal ?? $hariLibur->tanggal,
            'year' => Carbon::parse($request->tanggal)->year ?? $hariLibur->year,
        ]);

        return redirect()->back()->with('success_libur', 'Hari libur berhasil diperbarui.');
    }

    // End hari libur

    // Export checklist
    public function exportChecklistPdf($id)
    {
        $rkm = RKM::with('materi', 'perusahaan', 'peluang')
            ->where('id', $id)
            ->firstOrFail();

        $pdf = Pdf::loadView('office.checklistRkmPdf', compact('rkm'));

        return $pdf->download("Checklist_Keperluan.pdf");
    }

    public function exportChecklistExcel($id)
    {
        return Excel::download(new ChecklistRkmExport($id), 'Checklist_Keperluan.xlsx');
    }
}
