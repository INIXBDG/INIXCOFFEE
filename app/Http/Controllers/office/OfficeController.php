<?php

namespace App\Http\Controllers\office;

use App\Exports\ChecklistRkmExport;
use App\Http\Controllers\Controller;
use App\Models\AbsensiKaryawan;
use App\Models\AdministrasiKaryawan;
use App\Models\ChecklistEksam;
use App\Models\ChecklistKeperluan;
use App\Models\eksam;
use App\Models\Feedback;
use App\Models\HariLibur;
use App\Models\JenisTunjangan;
use App\Models\karyawan;
use App\Models\LogGaji;
use App\Models\Nilaifeedback;
use App\Models\outstanding;
use App\Models\pengajuancuti;
use App\Models\Perusahaan;
use App\Models\RKM;
use App\Models\tagihanPerusahaan;
use App\Models\Tickets;
use App\Models\trackingTagihanPerusahaan;
use App\Models\TunjanganKaryawan;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\JsonResponse;

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

        $karyawan = karyawan::where('status_aktif', '1')
            ->whereRaw("UPPER(kode_karyawan) NOT LIKE '%OL%'")
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
            ->where('status', '0')
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
                    'id_all' => $items->sortBy('id')->pluck('id')->implode(', '),
                    'materi_key' => $first->materi_key,
                    'ruang' => $first->ruang,
                    'metode_kelas' => $first->metode_kelas,
                    'event' => $first->event,
                    'harga_jual' => $first->harga_jual,
                    'pax' => $first->pax,
                    'exam' => $first->exam,
                    'instruktur_key' => $first->instruktur_key,
                    'instruktur_key2' => $first->instruktur_key2,
                    'asisten_key' => $first->asisten_key,
                    'makanan' => $items->pluck('makanan')->implode(', '),
                    'perusahaan_all' => $items->pluck('perusahaan_key')->implode(', '),
                    'sales_all' => $items->pluck('sales_key')->implode(', '),
                    'status' => $items->contains('status', 0) ? 0 : $items->min('status'),
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
            $rkmIds = collect(explode(',', $detail_rkm->id_all))
                ->map(fn($id) => trim($id))
                ->filter()
                ->values()
                ->toArray();

            $checklists = ChecklistKeperluan::whereIn('id_rkm', $rkmIds)
                ->with('subChecklistKeperluans')
                ->whereNotNull('tanggal_keperluan')
                ->orderBy('tanggal_keperluan', 'asc')
                ->get()
                ->groupBy('tanggal_keperluan')
                ->map(function ($items) {
                    return $items->first();
                });

            $detail_rkm->checklists = $checklists;

            foreach ($checklists as $checklist => $item) {
                $progress = 0;

                if ($detail_rkm->metode_kelas === 'Offline') {
                    $materiChecked =
                        ($item->subChecklistKeperluans?->materi_module ? 1 : 0) +
                        ($item->subChecklistKeperluans?->materi_elearning ? 1 : 0);

                    $progress += ($materiChecked / 2) * 20;

                    if ($item->kelas) {
                        $progress += 20;
                    }

                    $cbChecked =
                        ($item->subChecklistKeperluans?->cb_instruktur ? 1 : 0) +
                        ($item->subChecklistKeperluans?->cb_peserta ? 1 : 0);

                    $progress += ($cbChecked / 2) * 20;

                    $maksiChecked =
                        ($item->subChecklistKeperluans?->maksi_instruktur ? 1 : 0) +
                        ($item->subChecklistKeperluans?->maksi_peserta ? 1 : 0);

                    $progress += ($maksiChecked / 2) * 20;

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

                    $totalMateri = 2;
                    $materiChecked =
                        ($item->subChecklistKeperluans?->materi_module ? 1 : 0) +
                        ($item->subChecklistKeperluans?->materi_elearning ? 1 : 0);

                    $kategoriSelesai += $materiChecked / $totalMateri;
                    $kategoriSelesai += ($item->subChecklistKeperluans?->cb_instruktur ? 1 : 0);
                    $kategoriSelesai += ($item->subChecklistKeperluans?->maksi_instruktur ? 1 : 0);

                    $item->progress = round(($kategoriSelesai / $totalKategori) * 100);
                }
            }
        }

        // Jumlah Peserta
        $jumlahPeserta = $rkms
            ->where('status', '0')
            ->sum('pax');

        // Jumlah Instruktur
        $jumlahInstruktur = $rkms
            ->where('status', '0')
            ->sum(
                fn($rkms) =>
                collect([
                    $rkms->instruktur_key,
                    $rkms->instruktur_key2,
                    $rkms->asisten_key,
                ])
                    ->filter(fn($v) => $v !== '-' && !is_null($v))
                    ->count()
            );

        $endOfNextWeek = $now->copy()->addWeek()->endOfWeek();

        // Tagihan Perusaaan
        $trackingTagihanPerusahaans = trackingTagihanPerusahaan::with('tagihanPerusahaan')
            ->orderByDesc('created_at')
            ->get();
            // dd($trackingTagihanPerusahaans);

        $administrasis = AdministrasiKaryawan::orderBy('dateline', 'desc')
            ->get();

        
        // get data exam
        $exams = eksam::with([
            'materi',
            'perusahaan',
            'rkm.materi',
            'rkm.perusahaan',
            'approvalexam',
            'checklistEksam'
        ])
        ->whereHas('approvalexam', function ($q) {
            $q->where('office_manager', '1');
        })
        ->orderBy('created_at', 'desc')->get();

        return view('office.dashboard', compact(
            'total_karyawan',
            'divisiStats',
            'kehadiranChart',
            'tidakHadirList',
            'ticket',
            'jumlahPeserta',
            'jumlahInstruktur',
            'rkms',
            'trackingTagihanPerusahaans',
            'administrasis',
            'exams'
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
                            $jumlah_arr[] = (int) $jp;
                        } elseif (is_object($jp) && isset($jp->jumlah)) {
                            $jumlah_arr[] = (int) $jp->jumlah;
                        } elseif (is_array($jp) && isset($jp['jumlah'])) {
                            $jumlah_arr[] = (int) $jp['jumlah'];
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
            } elseif ($item->status_pembayaran == 1 && $item->tanggal_bayar) {

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
            $instruktur = $items->first()->rkm->instruktur;

            if (
                !$instrukturId ||
                !$instruktur ||
                $instruktur->status_aktif == 0 ||
                $instruktur->divisi != 'Education' ||
                str_contains(strtoupper($instruktur->kode_karyawan), 'OL')
            ) {
                continue;
            }

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
                'kode_karyawan' => $instruktur->kode_karyawan,
                'id_instruktur' => $instrukturId,
                'nama_instruktur' => $instruktur->nama_lengkap,
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

        $query = Nilaifeedback::with('rkm.instruktur', 'rkm.materi');

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

        $groupedFeedback = $feedbacks
            ->filter(fn($f) => $f->rkm && $f->rkm->instruktur)
            ->groupBy(function ($f) {
                return $f->rkm->instruktur->id . '_' .
                    \Carbon\Carbon::parse($f->created_at)->format('Y_m');
            })
            ->map(function ($items) {
                $first = $items->first();
                $avg = $items->avg(function ($row) {
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
                });

                return [
                    'instruktur_id' => $first->rkm->instruktur->id,
                    'nama' => $first->rkm->instruktur->nama_lengkap,
                    'feedback' => round($avg, 2),
                    'bulan' => \Carbon\Carbon::parse($first->created_at)->translatedFormat('F'),
                    'bulan_num' => \Carbon\Carbon::parse($first->created_at)->month,
                    'kelas' => $first->rkm->nama_rkm ?? '-',
                ];
            });

        $feedbackTerendah = $groupedFeedback
            ->filter(fn($f) => $f['feedback'] <= 3.3)
            ->sortBy('feedback')
            ->values();

        $feedbackTertinggi = $groupedFeedback
            ->filter(fn($f) => $f['feedback'] >= 4.0)
            ->sortByDesc('feedback')
            ->values();

        $summaryInstrukturTerendah = $feedbackTerendah
            ->groupBy('nama')
            ->map(fn($items) => [
                'nama' => $items->first()['nama'],
                'jumlah' => $items->count(),
                'kelas' => $items->first()['kelas']
            ])
            ->sortByDesc('jumlah')
            ->values();

        $summaryInstrukturTertinggi = $feedbackTertinggi
            ->groupBy('nama')
            ->map(fn($items) => [
                'nama' => $items->first()['nama'],
                'jumlah' => $items->count(),
                'kelas' => $items->first()['kelas']
            ])
            ->sortByDesc('jumlah')
            ->values();

        $summaryBulanTerendah = $feedbackTerendah
            ->groupBy('bulan_num')
            ->map(fn($items) => [
                'bulan' => $items->first()['bulan'],
                'bulan_num' => $items->first()['bulan_num'],
                'jumlah' => $items->count()
            ])
            ->sortBy('bulan_num')
            ->values();

        $summaryBulanTertinggi = $feedbackTertinggi
            ->groupBy('bulan_num')
            ->map(fn($items) => [
                'bulan' => $items->first()['bulan'],
                'bulan_num' => $items->first()['bulan_num'],
                'jumlah' => $items->count()
            ])
            ->sortBy('bulan_num')
            ->values();
        $allFeedbacks = $groupedFeedback->pluck('feedback');
        $stats = [
            'total_feedback' => $groupedFeedback->count(),
            'total_terendah' => $feedbackTerendah->count(),
            'total_tertinggi' => $feedbackTertinggi->count(),
            'rata_rata' => round($allFeedbacks->avg() ?? 0, 2),
            'nilai_tertinggi' => round($allFeedbacks->max() ?? 0, 2),
            'nilai_terendah' => round($allFeedbacks->min() ?? 0, 2),
            'total_instruktur' => $groupedFeedback->unique('nama')->count(),
        ];

        $detailFeedback = $feedbacks
            ->filter(fn($f) => $f->rkm && $f->rkm->instruktur)
            ->map(function ($item) {
                $nilai = collect([
                    $item->I1,
                    $item->I2,
                    $item->I3,
                    $item->I4,
                    $item->I5,
                    $item->I6,
                    $item->I7,
                    $item->I8
                ])->avg();

                return [
                    'nama' => $item->rkm->instruktur->nama_lengkap ?? '-',
                    'bulan' => \Carbon\Carbon::parse($item->created_at)->translatedFormat('F Y'),
                    'materi' => $item->rkm->materi->nama_materi ?? ($item->rkm->nama_rkm ?? '-'),
                    'feedback' => round($nilai, 2),
                ];
            })
            ->values();

        $pdf = Pdf::loadView('office.feedbackinstrukturpdf', [
            'feedbackTerendah' => $feedbackTerendah,
            'feedbackTertinggi' => $feedbackTertinggi,
            'summaryInstrukturTerendah' => $summaryInstrukturTerendah,
            'summaryInstrukturTertinggi' => $summaryInstrukturTertinggi,
            'summaryBulanTerendah' => $summaryBulanTerendah,
            'summaryBulanTertinggi' => $summaryBulanTertinggi,
            'stats' => $stats,
            'rentangWaktu' => $rentangWaktu,
            'detailFeedback' => $detailFeedback,
        ])->setPaper('A4', 'landscape');

        return $pdf->download('Laporan_Feedback_Instruktur.pdf');
    }

    public function dataCuti(Request $request)
    {
        Carbon::setLocale('id');

        $filter = $request->filter;
        $value = $request->value;

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

            $bulanMulai = ($value - 1) * 3 + 1;
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

        if ($request->boolean('export')) {
            $pdf = Pdf::loadView('office.daftarCutiPdf', compact('dataCuti', 'rentangWaktu'));
            return $pdf->download('Laporan_Cuti.pdf');
        }

        return response()->json([
            'labelCuti' => $dataCuti->pluck('nama_lengkap'),
            'totalCuti' => $dataCuti->pluck('total_cuti'),
            'rentangWaktu' => $rentangWaktu ?: 'Semua Data'
        ]);
    }

    public function dataMengajar(Request $request)
    {
        Carbon::setLocale('id');

        $filter = $request->filter;
        $value = $request->value;
        $tahun = is_numeric($request->tahun) ? (int) $request->tahun : now()->year;

        $baseQuery = DB::table('r_k_m_s')
            ->select('instruktur_key as kode_karyawan', 'tanggal_awal', 'materi_key', 'perusahaan_key', 'id as rkm_id', 'metode_kelas')
            ->whereNotNull('instruktur_key')
            ->unionAll(
                DB::table('r_k_m_s')
                    ->select('instruktur_key2 as kode_karyawan', 'tanggal_awal', 'materi_key', 'perusahaan_key', 'id as rkm_id', 'metode_kelas')
                    ->whereNotNull('instruktur_key2')
            )
            ->unionAll(
                DB::table('r_k_m_s')
                    ->select('asisten_key as kode_karyawan', 'tanggal_awal', 'materi_key', 'perusahaan_key', 'id as rkm_id', 'metode_kelas')
                    ->whereNotNull('asisten_key')
            );

        $query = DB::table(DB::raw("({$baseQuery->toSql()}) as t"))
            ->mergeBindings($baseQuery)
            ->join('karyawans', 't.kode_karyawan', '=', 'karyawans.kode_karyawan');

        $query->when($filter === 'tahun' && is_numeric($value), fn($q) =>
            $q->whereYear('t.tanggal_awal', $value))
            ->when($filter === 'bulan' && is_numeric($value), fn($q) =>
                $q->whereYear('t.tanggal_awal', $tahun)->whereMonth('t.tanggal_awal', $value))
            ->when($filter === 'triwulan' && is_numeric($value), function ($q) use ($value, $tahun) {
                $bulanMulai = ($value - 1) * 3 + 1;
                $q->whereYear('t.tanggal_awal', $tahun)
                    ->whereBetween(DB::raw('MONTH(t.tanggal_awal)'), [$bulanMulai, $bulanMulai + 2]);
            });

        $results = $query->select(
            't.kode_karyawan',
            'karyawans.nama_lengkap',
            't.tanggal_awal',
            't.rkm_id',
            't.materi_key',
            't.perusahaan_key',
            't.metode_kelas'
        )
            ->orderBy('t.tanggal_awal')
            ->get();

        $groupedByKaryawan = $results->groupBy('kode_karyawan');
        $finalData = [];

        foreach ($groupedByKaryawan as $kode => $sessions) {
            $namaKaryawan = $sessions->first()->nama_lengkap;

            if ($filter === 'triwulan' && is_numeric($value)) {
                $periodType = 'bulan';
            } elseif ($filter === 'bulan' && is_numeric($value)) {
                $periodType = 'minggu';
            } elseif ($filter === 'tahun' && is_numeric($value)) {
                $periodType = 'bulan';
            } else {
                $periodType = 'minggu';
            }

            $groupedByPeriod = $sessions->groupBy(function ($item) use ($periodType) {
                $date = Carbon::parse($item->tanggal_awal);

                if ($periodType === 'bulan') {
                    return $date->translatedFormat('F Y');
                } else {
                    return 'Minggu ke-' . $date->weekOfMonth . ' (' . $date->translatedFormat('d M Y') . ')';
                }
            });

            $periodsData = [];
            $totalFeedback = 0;
            $feedbackCount = 0;

            foreach ($groupedByPeriod as $periodLabel => $periodSessions) {
                $rkmIds = $periodSessions->pluck('rkm_id')->filter()->toArray();
                $feedbackAvg = 0;

                if (!empty($rkmIds)) {
                    $feedbacks = Nilaifeedback::whereIn('id_rkm', $rkmIds)->get();

                    if (!$feedbacks->isEmpty()) {
                        $allScores = [];

                        $fields = [
                            'I1',
                            'I2',
                            'I3',
                            'I4',
                            'I5',
                            'I6',
                            'I7',
                            'I8',
                            'I1b',
                            'I2b',
                            'I3b',
                            'I4b',
                            'I5b',
                            'I6b',
                            'I7b',
                            'I8b',
                            'I1as',
                            'I2as',
                            'I3as',
                            'I4as',
                            'I5as',
                            'I6as',
                            'I7as',
                            'I8as'
                        ];

                        foreach ($fields as $col) {
                            $values = $feedbacks->pluck($col)->filter(fn($v) => is_numeric($v))->toArray();
                            $allScores = array_merge($allScores, $values);
                        }

                        if (!empty($allScores)) {
                            $feedbackAvg = round(array_sum($allScores) / count($allScores), 2);
                            $totalFeedback += $feedbackAvg;
                            $feedbackCount++;
                        }
                    }
                }

                $periodsData[] = [
                    'periode' => $periodLabel,
                    'total_mengajar' => $periodSessions->count(),
                    'materi' => $periodSessions->unique('materi_key')->pluck('materi_key')->join(', '),
                    'metode' => $periodSessions->unique('metode')->filter()->pluck('metode')->join(', ') ?: '-',
                    'feedback_avg' => $feedbackAvg > 0 ? $feedbackAvg : '-'
                ];
            }

            $overallFeedback = $feedbackCount > 0 ? round($totalFeedback / $feedbackCount, 2) : '-';

            $finalData[] = [
                'namaKaryawan' => $namaKaryawan,
                'kodeKaryawan' => $kode,
                'totalMengajar' => $sessions->count(),
                'periods' => $periodsData,
                'overall_feedback' => $overallFeedback
            ];
        }

        usort($finalData, fn($a, $b) => $b['totalMengajar'] <=> $a['totalMengajar']);

        $rentangWaktu = match ($filter) {
            'tahun' => "Tahun $value",
            'bulan' => Carbon::createFromDate($tahun, $value, 1)->translatedFormat('F Y'),
            'triwulan' => "Triwulan $value Tahun $tahun",
            default => "Semua Data"
        };

        if ($request->boolean('exportTotalMengajar')) {
            $dataMengajar = $finalData;
            $pdf = Pdf::loadView('office.totalMengajarPdf', compact('dataMengajar', 'rentangWaktu', 'filter'));
            return $pdf->download('Laporan_Total_Mengajar.pdf');
        }

        return response()->json([
            'dataMengajar' => $finalData,
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

    // get data checklist exam
    public function getAllExam(Request $request)
    {
        $search = $request->search;

        $exams = eksam::with([
                'materi',
                'perusahaan',
                'rkm.materi',
                'rkm.perusahaan',
                'rkm.sales',
                'approvalexam',
                'checklistEksam'
            ])
            ->when($search, function ($q) use ($search) {

                $q->where('perusahaan', 'like', "%{$search}%")
                ->orWhereHas('rkm.materi', function ($qr) use ($search) {

                        $qr->where(
                            'nama_materi',
                            'like',
                            "%{$search}%"
                        );
                });
            })
            ->latest()
            
            ->paginate(10);

        return response()->json($exams);
    }

    public function getExam($id) {
        $exam = eksam::with([
            'materi',
            'perusahaan',
            'rkm.materi',
            'rkm.perusahaan',
            'rkm.sales',
            'approvalexam',
            'checklistEksam'
        ])
        ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'List Exam',
            'data' => $exam,
        ]);
    }

    // store Checklist exam
    public function storeChecklistExam(Request $request)
    {
        $request->validate([
            'id_exam' => 'required'
        ]);

        ChecklistEksam::create([
            'id_exam' => $request->id_exam,
            'status' => 1
        ]);

        return redirect()->back()->with('success_exam', 'Exam berhasil di selesaikan');
    }
  
    public function laporanStatusKaryawan(Request $request)
    {
        $tahun = $request->tahun ?? now()->year;
        $bulan = $request->bulan;

        $base = Karyawan::whereNot('jabatan', 'Outsource')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->whereNotNull('nip')
            ->whereNot('divisi', 'Direksi');

        $aktifBase = (clone $base)->where('status_aktif', '1');
        $resignBase = (clone $base)->where('status_aktif', '0')->whereNotNull('resigned_at');

        // Helper untuk apply filter tahun & bulan ke kolom spesifik
        $applyDateFilter = function($query, $column) use ($tahun, $bulan) {
            if ($tahun) $query->whereYear($column, $tahun);
            if ($bulan) $query->whereMonth($column, $bulan);
            return $query;
        };

        $kontrak = (clone $aktifBase)->whereNotNull(['awal_kontrak', 'akhir_kontrak'])->whereNull(['awal_tetap', 'akhir_tetap']);
        $kontrakCount = $applyDateFilter($kontrak, 'awal_kontrak')->count();

        $tetap = (clone $aktifBase)->whereNotNull(['awal_tetap', 'akhir_tetap']);
        $tetapCount = $applyDateFilter($tetap, 'awal_tetap')->count();

        $probation = (clone $aktifBase)->whereNotNull(['awal_probation', 'akhir_probation'])->whereNull(['awal_kontrak', 'akhir_kontrak']);
        $probationCount = $applyDateFilter($probation, 'awal_probation')->count();

        $resign = clone $resignBase;
        $resignCount = $applyDateFilter($resign, 'resigned_at')->count();

        return response()->json([
            'kontrak' => $kontrakCount,
            'tetap' => $tetapCount,
            'probation' => $probationCount,
            'resign' => $resignCount,
        ]);
    }

    public function detailKaryawanStatus(Request $request)
    {
        $status = $request->status;
        $tahun = $request->tahun ?? now()->year;
        $bulan = $request->bulan;
        $search = $request->search ?? '';

        $query = Karyawan::whereNot('jabatan', 'Outsource')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->whereNotNull('nip')
            ->whereNot('divisi', 'Direksi');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'LIKE', "%{$search}%")
                ->orWhere('nip', 'LIKE', "%{$search}%")
                ->orWhere('divisi', 'LIKE', "%{$search}%");
            });
        }

        $dateColumn = null;
        switch ($status) {
            case 'kontrak':
                $query->where('status_aktif', '1')->whereNotNull(['awal_kontrak', 'akhir_kontrak'])->whereNull(['awal_tetap', 'akhir_tetap']);
                $dateColumn = 'awal_kontrak';
                break;
            case 'tetap':
                $query->where('status_aktif', '1')->whereNotNull(['awal_tetap', 'akhir_tetap']);
                $dateColumn = 'awal_tetap';
                break;
            case 'probation':
                $query->where('status_aktif', '1')->whereNotNull(['awal_probation', 'akhir_probation'])->whereNull(['awal_kontrak', 'akhir_kontrak']);
                $dateColumn = 'awal_probation';
                break;
            case 'resign':
                $query->where('status_aktif', '0')->whereNotNull('resigned_at');
                $dateColumn = 'resigned_at';
                break;
        }

        // Apply filter tanggal sesuai status
        if ($dateColumn) {
            if ($tahun) $query->whereYear($dateColumn, $tahun);
            if ($bulan) $query->whereMonth($dateColumn, $bulan);
        }

        return response()->json($query->orderBy('nama_lengkap')->paginate(10));
    }

    public function laporanTrendKaryawan(Request $request)
    {
        $tahun = $request->tahun ?? now()->year;
        $labels = $kontrak = $tetap = $probation = [];

        // Base query yang sama untuk semua status aktif
        $base = Karyawan::whereNot('jabatan', 'Outsource')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->whereNotNull('nip')
            ->whereNot('divisi', 'Direksi')
            ->where('status_aktif', '1');

        // Gunakan copy() agar tidak merusak instance Carbon di iterasi berikutnya
        $baseDate = now()->year($tahun);

        for ($i = 5; $i >= 0; $i--) {
            $date = $baseDate->copy()->subMonths($i);
            $labels[] = $date->translatedFormat('M Y');
            
            $start = $date->copy()->startOfMonth();
            $end = $date->copy()->endOfMonth();

            // Filter berdasarkan kolom tanggal spesifik masing-masing status
            $kontrak[] = (clone $base)
                ->whereNotNull(['awal_kontrak', 'akhir_kontrak'])
                ->whereNull(['awal_tetap', 'akhir_tetap'])
                ->whereBetween('awal_kontrak', [$start, $end])
                ->count();

            $tetap[] = (clone $base)
                ->whereNotNull(['awal_tetap', 'akhir_tetap'])
                ->whereBetween('awal_tetap', [$start, $end])
                ->count();

            $probation[] = (clone $base)
                ->whereNotNull(['awal_probation', 'akhir_probation'])
                ->whereNull(['awal_kontrak', 'akhir_kontrak'])
                ->whereBetween('awal_probation', [$start, $end])
                ->count();
        }

        return response()->json([
            'labels' => $labels, 
            'kontrak' => $kontrak, 
            'tetap' => $tetap, 
            'probation' => $probation
        ]);
    }

    public function getChecklistRKM($tahun, $bulan) {
        $data = RKM::with('checklistKeperluan', 'materi', 'perusahaan')
                    ->whereYear('tanggal_awal', $tahun)
                    ->whereMonth('tanggal_awal', $bulan)
                    ->get();
    
        return response()->json([
            'message' => 'data checklist rkm',
            'data' => $data
        ]);
    }
}
