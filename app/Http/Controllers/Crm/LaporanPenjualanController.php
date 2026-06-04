<?php

namespace App\Http\Controllers\Crm;

use App\Exports\LaporanPenjualanExport;
use App\Http\Controllers\Controller;
use App\Models\HistoryNetSales;
use App\Models\karyawan;
use App\Models\Materi;
use Illuminate\Http\Request;
use App\Models\Peluang;
use App\Models\perhitunganNetSales;
use App\Models\RKM;
use App\Models\User;
use App\Notifications\UpdateLaporanPenjualan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanPenjualanController extends Controller
{
    public function index(Request $request)
    {
        $sales = karyawan::where('jabatan', 'Sales')->where('status_aktif', '1')->get();
        $materi = Materi::all();
        return view('crm.LaporanPenjualan.index', compact(
            'sales',
            'materi',
        ));
    }

    public function indexJson(Request $request)
    {
        $status = $request->query('status');
        $query = RKM::with(['exam', 'perhitunganNetSales', 'materi', 'perusahaan', 'invoice', 'peluang.regis'])
                    ->orderByDesc('tanggal_awal');

        // Filter Status
        if ($status === '0') {
            $query->whereNull('deleted_at')
                ->whereNull('deleted_by')
                ->where('status', '0')
                ->where(function ($q) {
                    $q->whereDoesntHave('peluang')
                        ->orWhereHas('peluang', function ($subQ) {
                            $subQ->where('tahap', '!=', 'lost');
                        });
                });
        } elseif ($status === '2') {
        $query->withTrashed()
            ->where(function ($q) {
                $q->whereNotNull('deleted_at')
                ->orWhere(function ($subQ) {
                    $subQ->whereNull('deleted_at')
                        ->whereHas('peluang', function ($p) {
                            $p->where('tahap', 'lost');
                        });
                });
            });
        }

        // Filter Sales & Materi
        if ($request->filled('sales_key')) {
            $query->where('sales_key', $request->sales_key);
        }

        if ($request->filled('materi_key')) {
            $query->where('materi_key', $request->materi_key);
        }

        // Filter Range Tanggal
        if ($request->filled('tanggal_awal_mulai') && $request->filled('tanggal_awal_akhir')) {
            $query->whereBetween('tanggal_awal', [
                $request->tanggal_awal_mulai,
                $request->tanggal_awal_akhir
            ]);
        } elseif ($request->filled('tanggal_awal_mulai')) {
            $query->whereDate('tanggal_awal', '>=', $request->tanggal_awal_mulai);
        } elseif ($request->filled('tanggal_awal_akhir')) {
            $query->whereDate('tanggal_awal', '<=', $request->tanggal_awal_akhir);
        }

        // Filter Triwulan/Bulan
        if ($request->filled('triwulan')) {
            $triwulanMapping = [
                '1' => [1, 2, 3],
                '2' => [4, 5, 6],
                '3' => [7, 8, 9],
                '4' => [10, 11, 12],
            ];
            $triwulan = $request->input('triwulan');
            if (array_key_exists($triwulan, $triwulanMapping)) {
                $query->whereIn(DB::raw('MONTH(tanggal_awal)'), $triwulanMapping[$triwulan]);
            }
        } elseif ($request->filled('bulan')) {
            $query->whereMonth('tanggal_awal', $request->bulan);
        }

        // Filter Minggu & Tahun
        if ($request->filled('minggu')) {
            $query->whereRaw('WEEK(tanggal_awal, 1) = ?', [$request->minggu]);
        }

        if ($request->filled('tahun')) {
            $query->whereYear('tanggal_awal', $request->tahun);
        }

        // Inisialisasi variabel untuk total keseluruhan
        $totalHargaJualKeseluruhan = 0;
        $totalNetSalesKeseluruhan = 0;
        $totalExamKeseluruhan = 0;
        $totalGrandKeseluruhan = 0;

        $data = $query->get()->map(function ($item) use (&$totalHargaJualKeseluruhan, &$totalNetSalesKeseluruhan, &$totalExamKeseluruhan, &$totalGrandKeseluruhan) {
            $exam = $item->exam;
            $netsales = $item->perhitunganNetSales;

            $examHarga = ($exam && isset($exam->harga_rupiah)) ? (float) $exam->harga_rupiah : 0.0;
            $pax = (float) ($item->pax ?? 0);
            $totalexam = $examHarga * $pax;

            if (is_null($netsales)) {
                $sum = [
                    'transportasi' => 0.0, 'akomodasi_peserta' => 0.0, 'akomodasi_tim' => 0.0,
                    'fresh_money' => 0.0, 'entertaint' => 0.0, 'souvenir' => 0.0,
                    'sewa_laptop' => 0.0, 'cashback' => 0.0, 'grand_total' => 0.0, 'pembayaran' => null,
                ];
                $netsales = collect();
            } else {
                $sum = [
                    'transportasi' => (float) $netsales->sum('transportasi'),
                    'akomodasi_peserta' => (float) $netsales->sum('akomodasi_peserta'),
                    'akomodasi_tim' => (float) $netsales->sum('akomodasi_tim'),
                    'fresh_money' => (float) $netsales->sum('fresh_money'),
                    'cashback' => (float) $netsales->sum('cashback'),
                    'entertaint' => (float) $netsales->sum('entertaint'),
                    'souvenir' => (float) $netsales->sum('souvenir'),
                    'sewa_laptop' => (float) $netsales->sum('sewa_laptop'),
                    'pembayaran' => $netsales->pluck('tipe_pembayaran')->first(),
                ];

                $sum['grand_total'] = array_sum([
                    $sum['transportasi'], $sum['akomodasi_peserta'], $sum['akomodasi_tim'],
                    $sum['fresh_money'], $sum['cashback'], $sum['entertaint'],
                    $sum['sewa_laptop'], $sum['souvenir']
                ]);
            }

            $hargaJual = (float) ($item->harga_jual ?? 0.0);
            $totalPenjualan = $hargaJual * $pax;
            $grandtotal = $item->peluang->netsales ?? 0.0;

            // Akumulasi total keseluruhan
            $totalHargaJualKeseluruhan += $totalPenjualan; // Total sales revenue
            $totalNetSalesKeseluruhan += $sum['grand_total']; // Total CAC costs
            $totalExamKeseluruhan += $totalexam;
            $totalGrandKeseluruhan += $grandtotal;
            $totalppn = 0.11 * $totalPenjualan;

            return [
                'id' => $item->id,
                'sales_key' => $item->sales_key,
                'materi_key' => $item->materi_key,
                'perusahaan_key' => $item->perusahaan_key,
                'pax' => $pax,
                'harga' => $hargaJual,
                'total_penjualan' => $totalPenjualan,
                'exam' => $examHarga,
                'total_exam' => $totalexam,
                'tanggal_awal' => $item->tanggal_awal,
                'tanggal_akhir' => $item->tanggal_akhir,
                'metode_kelas' => $item->metode_kelas,
                'netsales' => $sum['grand_total'],
                'grandtotal' => $grandtotal,
                'nama_materi' => $item->materi?->nama_materi ?? '-',
                'nama_perusahaan' => $item->perusahaan?->nama_perusahaan ?? '-',
                'perhitungannet' => $netsales,
                'invoice' => $item->invoice,
                'bukti' => $item->bukti ?? null,
                'path_regis' => $item->peluang->regis->path ?? '-',
                'total_ppn' => $totalppn,
            ];
        });

        // --- LOGIKA PERHITUNGAN TARGET CAC (10% DARI 9M) ---
        $targetTahunan9M = 9000000000;
        $targetCAC = 0.10 * $targetTahunan9M; // 900jt
        $targetPeriode = $targetCAC; // Default Tahunan

        if ($request->filled('triwulan')) {
            $targetPeriode = $targetCAC / 4;
        } elseif ($request->filled('bulan')) {
            $targetPeriode = $targetCAC / 12;
        } elseif ($request->filled('minggu')) {
            $targetPeriode = $targetCAC / 52;
        }

        $selisihBudget = $targetPeriode - $totalNetSalesKeseluruhan;

        return response()->json([
            'data' => $data,
            'summary' => [
                'total_harga_jual' => $totalHargaJualKeseluruhan,
                'total_netsales' => $totalNetSalesKeseluruhan,
                'total_exam' => $totalExamKeseluruhan,
                'total_grand' => $totalGrandKeseluruhan,

                'target_cac_periode' => $targetPeriode,
                'selisih_cac' => $selisihBudget,
                'is_overbudget' => ($selisihBudget < 0),
                'persentase_pemakaian' => ($targetPeriode > 0) ? round(($totalNetSalesKeseluruhan / $targetPeriode) * 100, 2) : 0
            ]
        ]);
    }

    public function detailRingkasan($id)
    {
        $data = Peluang::where('id_sales', $id)->where('tahap', 'merah')->with('aktivitas', 'materiRelation')->with('perusahaan')->get();
        return view('crm.closedwin.detail', compact('data'));
    }

    public function editPA($id)
    {
        $pa = perhitunganNetSales::with('peserta')->where('id_rkm', $id)->get();
        $netsales = perhitunganNetSales::with('trackingNetSales', 'approvedNetSales', 'peserta', 'rkm')
            ->where('id_rkm', $id)
            ->first();

        $peluang = Peluang::where('id_rkm', $id)->first();
        $totalPA = $peluang ? $peluang->netsales : 0;

        $historyNet = HistoryNetSales::with('user.karyawan')->where('id_rkm', $id)->get();

        return view('crm.LaporanPenjualan.editpa', compact('pa', 'netsales', 'totalPA', 'historyNet'));
    }

    public function updatePA(Request $request, $id)
    {
        $numericFields = [
            'transportasi',
            'akomodasi_peserta',
            'akomodasi_tim',
            'cashback',
            'fresh_money',
            'entertaint',
            'souvenir',
            'sewa_laptop',
        ];

        foreach ($numericFields as $field) {
            if ($request->has($field)) {
                $rawValue = $request->input($field);

                if (is_array($rawValue)) {
                    $rawValue = reset($rawValue) ?? 0;
                }

                $cleanValue = preg_replace('/[^\d]/', '', (string)$rawValue);

                $finalValue = ($cleanValue === '') ? 0 : $cleanValue;

                $request->merge([
                    $field => $finalValue
                ]);
            }
        }

        $validated = $request->validate([
            'transportasi'             => 'nullable|numeric|min:0',
            'jenis_transportasi'       => 'nullable',
            'akomodasi_peserta'        => 'nullable|numeric|min:0',
            'akomodasi_tim'            => 'nullable|numeric|min:0',
            'keterangan_akomodasi_tim' => 'nullable',
            'cashback'                 => 'nullable|numeric|min:0',
            'fresh_money'              => 'nullable|numeric|min:0',
            'souvenir'                 => 'nullable|numeric|min:0',
            'entertaint'               => 'nullable|numeric|min:0',
            'keterangan_entertaint'    => 'nullable',
            'sewa_laptop'              => 'nullable|numeric|min:0',
            'deskripsi_tambahan'       => 'nullable|string|max:500',
            'tgl_pa'                   => 'nullable|date',
            'tipe_pembayaran'          => 'nullable|string|in:cash,transfer,credit',
        ]);

        $pa = perhitunganNetSales::findOrFail($id);
        $oldData = $pa->getOriginal();

        $pa->update($validated);

        $peluang = Peluang::where('id_rkm', $pa->id_rkm)->first();
        if ($peluang) {
            $totalPenawaran = (float)$peluang->harga * (int)$peluang->pax;
            $potonganPajak = $totalPenawaran * (11 / 100);

            $allPA = perhitunganNetSales::where('id_rkm', $pa->id_rkm)->get();
            $totalPAInputs = $allPA->sum('transportasi') +
                             $allPA->sum('akomodasi_peserta') +
                             $allPA->sum('akomodasi_tim') +
                             $allPA->sum('fresh_money') +
                             $allPA->sum('entertaint') +
                             $allPA->sum('souvenir') +
                             $allPA->sum('cashback') +
                             $allPA->sum('sewa_laptop');

            $peluang->netsales = $totalPenawaran - $potonganPajak - $totalPAInputs;
            $peluang->save();
        }

        $changed = [];
        foreach ($validated as $key => $value) {
            $oldValue = $oldData[$key] ?? null;

            if ($oldValue != $value) {
                $changed[$key] = [
                    'before' => $oldValue,
                    'after' => $value,
                ];
            }
        }

        $historyNet = new HistoryNetSales();
        $historyNet->id_user = Auth::user()->id;
        $historyNet->id_rkm = $pa->id_rkm;
        $historyNet->data = $changed;
        $historyNet->save();

        $users = User::whereIn('jabatan', ['GM', 'Adm Sales', 'Finance & Accounting'])->get();

        $rkm = RKM::with('materi')->where('id', $pa->id_rkm)->first();

        Carbon::setLocale('id');
        date_default_timezone_set('Asia/Jakarta');

        $data = [
            'karyawan' => Auth::user()->karyawan->nama_lengkap ?? Auth::user()->username,
            'id_rkm' => $rkm->id ?? null,
            'rkm' => $rkm->materi?->nama_materi ?? 'Tidak diketahui',
            'waktu' => ($rkm->tanggal_awal && $rkm->tanggal_akhir)
                ? Carbon::parse($rkm->tanggal_awal)->translatedFormat('d F Y') . ' - ' . Carbon::parse($rkm->tanggal_akhir)->translatedFormat('d F Y')
                : 'Tanggal belum ditentukan',
            'milik' => $rkm->sales_key ?? 'Tidak diketahui',
            'waktu_perubahan' => Carbon::now()->format('Y-m-d H:i:s'),
            'perubahan' => $changed,
        ];

        $path = "/crm/edit/{$pa->id_rkm}/pa";

        $receiverId = $users->pluck('id')->toArray();
        Notification::send($users, new UpdateLaporanPenjualan($data, $path, $receiverId));

        return response()->json([
            'success' => true,
            'message' => 'Data penawaran acara berhasil diperbarui.',
            'data' => $pa
        ]);
    }

    private function buildBaseQuery(Request $request, $status)
    {
        $query = RKM::with([
            'exam',
            'perhitunganNetSales.peserta',
            'materi',
            'perusahaan',
            'invoice'
        ])->where('status', $status)->orderByDesc('tanggal_awal');

        if ($request->filled('sales_key')) {
            $query->where('sales_key', $request->sales_key);
        }

        if ($request->filled('materi_key')) {
            $query->where('materi_key', $request->materi_key);
        }

        if ($request->filled('tanggal_awal_mulai') && $request->filled('tanggal_awal_akhir')) {
            $query->whereBetween('tanggal_awal', [
                $request->tanggal_awal_mulai,
                $request->tanggal_awal_akhir
            ]);
        } elseif ($request->filled('tanggal_awal_mulai')) {
            $query->whereDate('tanggal_awal', '>=', $request->tanggal_awal_mulai);
        } elseif ($request->filled('tanggal_awal_akhir')) {
            $query->whereDate('tanggal_awal', '<=', $request->tanggal_awal_akhir);
        }

        if ($request->filled('triwulan')) {
            $triwulanMapping = [
                '1' => [1, 2, 3],
                '2' => [4, 5, 6],
                '3' => [7, 8, 9],
                '4' => [10, 11, 12],
            ];

            $triwulan = $request->input('triwulan');
            if (array_key_exists($triwulan, $triwulanMapping)) {
                $query->whereIn(DB::raw('MONTH(tanggal_awal)'), $triwulanMapping[$triwulan]);
            }
        } elseif ($request->filled('bulan')) {
            $query->whereMonth('tanggal_awal', $request->bulan);
        }

        if ($request->filled('minggu')) {
            $query->whereRaw('WEEK(tanggal_awal, 1) = ?', [$request->minggu]);
        }

        if ($request->filled('tahun')) {
            $query->whereYear('tanggal_awal', $request->tahun);
        }

        return $query;
    }

    // Helper: Proses perhitungan **sama persis seperti di indexJson / Blade**
    private function calculateReportItem($item)
    {
        $exam = $item->exam;
        $netsales = $item->perhitunganNetSales;

        // Harga Exam
        $examHarga = ($exam && isset($exam->harga_rupiah)) ? (float) $exam->harga_rupiah : 0.0;
        $pax = (float) ($item->pax ?? 0);
        $totalexam = $examHarga * $pax;

        // Total Penjualan = harga_jual * pax
        $hargaJual = (float) ($item->harga_jual ?? 0.0);
        $totalPenjualan = $hargaJual * $pax;

        if (is_null($netsales) || $netsales->isEmpty()) {
            $grandNet = 0.0;
        } else {
            $grandNet =
                (float) $netsales->sum('transportasi') +
                (float) $netsales->sum('penginapan') +
                (float) $netsales->sum('fresh_money') +
                (float) $netsales->sum('cashback') +
                (float) $netsales->sum('diskon') +
                (float) $netsales->sum('entertaint') +
                (float) $netsales->sum('souvenir');
        }

        $grandtotal = $totalPenjualan - ($grandNet + $totalexam);

        return [
            'id' => $item->id,
            'sales_key' => $item->sales_key,
            'nama_materi' => $item->materi?->nama_materi ?? '-',
            'nama_perusahaan' => $item->perusahaan?->nama_perusahaan ?? '-',
            'pax' => $pax,
            'harga' => $hargaJual,
            'total_penjualan' => $totalPenjualan,
            'exam' => $examHarga,
            'total_exam' => $totalexam,
            'netsales' => $grandNet,
            'grandtotal' => $grandtotal,
            'tanggal_awal' => $item->tanggal_awal,
            'tanggal_akhir' => $item->tanggal_akhir,
            'metode_kelas' => $item->metode_kelas,
            'invoice' => $item->invoice,
        ];
    }

    public function downloadWinExcel(Request $request)
    {
        $query = $this->buildBaseQuery($request, '0');
        $rawData = $query->get();

        $processedData = $rawData->map(function ($item) {
            return $this->calculateReportItem($item);
        });

        return Excel::download(
            new LaporanPenjualanExport($processedData),
            'laporan-win-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function downloadLostExcel(Request $request)
    {
        $query = $this->buildBaseQuery($request, '2');
        $rawData = $query->get();

        $processedData = $rawData->map(function ($item) {
            return $this->calculateReportItem($item);
        });

        return Excel::download(
            new LaporanPenjualanExport($processedData),
            'laporan-lost-' . now()->format('Y-m-d') . '.xlsx'
        );
    }


    private function fetchDataForReport(Request $request, $status)
    {
        $status = $status;
        $query = RKM::with(['exam', 'perhitunganNetSales.peserta', 'materi', 'perusahaan', 'invoice'])
            ->orderByDesc('tanggal_awal')
            ->where('status', $status);

        if ($request->filled('sales_key')) {
            $query->where('sales_key', $request->sales_key);
        }
        if ($request->filled('materi_key')) {
            $query->where('materi_key', $request->materi_key);
        }
        if ($request->filled('tanggal_awal_mulai') && $request->filled('tanggal_awal_akhir')) {
            $query->whereBetween('tanggal_awal', [$request->tanggal_awal_mulai, $request->tanggal_awal_akhir]);
        } elseif ($request->filled('tanggal_awal_mulai')) {
            $query->whereDate('tanggal_awal', '>=', $request->tanggal_awal_mulai);
        } elseif ($request->filled('tanggal_awal_akhir')) {
            $query->whereDate('tanggal_awal', '<=', $request->tanggal_awal_akhir);
        }
        if ($request->filled('triwulan')) {
            $triwulanMapping = ['1' => [1, 2, 3], '2' => [4, 5, 6], '3' => [7, 8, 9], '4' => [10, 11, 12]];
            if (array_key_exists($request->triwulan, $triwulanMapping)) {
                $query->whereIn(DB::raw('MONTH(tanggal_awal)'), $triwulanMapping[$request->triwulan]);
            }
        } elseif ($request->filled('bulan')) {
            $query->whereMonth('tanggal_awal', $request->bulan);
        }
        if ($request->filled('minggu')) {
            $query->whereRaw('WEEK(tanggal_awal, 1) = ?', [$request->minggu]);
        }
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal_awal', $request->tahun);
        }

        $totalHargaJualKeseluruhan = 0;
        $totalNetSalesKeseluruhan = 0;
        $totalExamKeseluruhan = 0;
        $totalGrandKeseluruhan = 0;

        $data = $query->get()->map(function ($item) use (&$totalHargaJualKeseluruhan, &$totalNetSalesKeseluruhan, &$totalExamKeseluruhan, &$totalGrandKeseluruhan) {
            // ... (sama seperti di indexJson Anda)
            $exam = $item->exam;
            $netsales = $item->perhitunganNetSales;
            $examHarga = ($exam && isset($exam->harga_rupiah)) ? (float) $exam->harga_rupiah : 0.0;
            $pax = (float) ($item->pax ?? 0);
            $totalexam = $examHarga * $pax;

            if (is_null($netsales)) {
                $sumGrand = 0.0;
            } else {
                $sumGrand =
                    (float) $netsales->sum('transportasi') +
                    (float) $netsales->sum('penginapan') +
                    (float) $netsales->sum('fresh_money') +
                    (float) $netsales->sum('cashback') +
                    (float) $netsales->sum('diskon') +
                    (float) $netsales->sum('entertaint') +
                    (float) $netsales->sum('souvenir');
            }

            $hargaJual = (float) ($item->harga_jual ?? 0.0);
            $totalPenjualan = $hargaJual * $pax;
            $grandtotal = $totalPenjualan - ($sumGrand + $totalexam);

            $totalHargaJualKeseluruhan += $hargaJual;
            $totalNetSalesKeseluruhan += $sumGrand;
            $totalExamKeseluruhan += $totalexam;
            $totalGrandKeseluruhan += $grandtotal;

            return [
                'id' => $item->id,
                'sales_key' => $item->sales_key,
                'nama_materi' => $item->materi?->nama_materi ?? '-',
                'nama_perusahaan' => $item->perusahaan?->nama_perusahaan ?? '-',
                'pax' => $pax,
                'harga' => $hargaJual,
                'total_penjualan' => $totalPenjualan,
                'exam' => $examHarga,
                'total_exam' => $totalexam,
                'netsales' => $sumGrand,
                'grandtotal' => $grandtotal,
                'tanggal_awal' => $item->tanggal_awal,
                'tanggal_akhir' => $item->tanggal_akhir,
            ];
        });

        $summary = (object) [
            'total_harga_jual' => $totalHargaJualKeseluruhan,
            'total_netsales' => $totalNetSalesKeseluruhan,
            'total_exam' => $totalExamKeseluruhan,
            'total_grand' => $totalGrandKeseluruhan,
        ];

        return [$data, $summary];
    }

    public function downloadPdfWin(Request $request)
    {
        [$data, $summary] = $this->fetchDataForReport($request, '0');
        $title = 'Laporan Penjualan Win';
        $isWin = true;

        $pdf = Pdf::loadView('exports.laporanPenjualanPdf', compact('data', 'summary', 'title', 'isWin'))
            ->setPaper('A4', 'landscape');

        return $pdf->download('laporan-win-' . now()->format('Y-m-d') . '.pdf');
    }

    public function downloadPdfLost(Request $request)
    {
        [$data, $summary] = $this->fetchDataForReport($request, '2');
        $title = 'Laporan Penjualan Lost';
        $isWin = false;

        $pdf = Pdf::loadView('exports.laporanPenjualanPdf', compact('data', 'summary', 'title', 'isWin'))
            ->setPaper('A4', 'landscape');

        return $pdf->download('laporan-lost-' . now()->format('Y-m-d') . '.pdf');
    }

    public function laporanForGm(Request $request)
    {
        // 1. Inisialisasi parameter filter tahun
        $tahun = $request->input('tahun', now()->year);

        // 2. Deklarasi referensi target statis
        $targetReferensi = [
            'rara'   => 450000000,
            'hera'   => 450000000,
            'reni'   => 300000000,
            'nabila' => 250000000,
            'luthfiahaffah' => 200000000,
            'Alfasyiani'   => 250000000,
        ];

        // 3. Ekstraksi entitas sales dengan validasi dan pemfilteran dinamis berdasarkan array referensi
        $kunciTarget = array_keys($targetReferensi);
        $users = User::where('status_akun', '1')
                     ->whereNotNull('id_sales')
                     ->where(function ($query) use ($kunciTarget) {
                         foreach ($kunciTarget as $kunci) {
                             $query->orWhere('username', 'LIKE', '%' . $kunci . '%');
                         }
                     })
                     ->get();

        // 4. Agregasi data penjualan dengan eksklusi status lost
        $peluangData = Peluang::selectRaw('id_sales, MONTH(periode_mulai) as bulan, SUM(netsales) as total_netsales')
            ->whereYear('periode_mulai', $tahun)
            ->where(function ($query) {
                $query->where('lost', 0)->orWhereNull('lost');
            })
            ->groupBy('id_sales', 'bulan')
            ->get()
            ->groupBy('id_sales');

        // 5. Konfigurasi struktur periode triwulan
        $strukturTriwulan = [
            'Triwulan I'   => ['bulan' => [1, 2, 3], 'nama_bulan' => ['January', 'February', 'March']],
            'Triwulan II'  => ['bulan' => [4, 5, 6], 'nama_bulan' => ['April', 'Mei', 'Juni']],
            'Triwulan III' => ['bulan' => [7, 8, 9], 'nama_bulan' => ['Juli', 'Agustus', 'September']],
            'Triwulan IV'  => ['bulan' => [10, 11, 12], 'nama_bulan' => ['Oktober', 'November', 'Desember']],
        ];

        $laporan = [];

        // 6. Eksekusi komputasi matriks data
        foreach ($strukturTriwulan as $namaTriwulan => $konfigurasi) {
            $dataSales = [];
            $totalKeseluruhan = [
                'bulan_1' => 0, 'bulan_2' => 0, 'bulan_3' => 0,
                'total' => 0, 'target' => 0, 'selisih' => 0
            ];

            foreach ($users as $user) {
                $nama = $user->username;
                $target = 0;

                // Modifikasi pencocokan target menggunakan logika LIKE (string position)
                foreach ($targetReferensi as $kunci => $nilaiTarget) {
                    if (stripos($nama, $kunci) !== false) {
                        $target = $nilaiTarget;
                        break; // Hentikan iterasi setelah kecocokan pertama ditemukan
                    }
                }

                $b1 = $konfigurasi['bulan'][0];
                $b2 = $konfigurasi['bulan'][1];
                $b3 = $konfigurasi['bulan'][2];

                $salesB1 = $this->kalkulasiSalesBulanan($peluangData, $user->id_sales, $b1);
                $salesB2 = $this->kalkulasiSalesBulanan($peluangData, $user->id_sales, $b2);
                $salesB3 = $this->kalkulasiSalesBulanan($peluangData, $user->id_sales, $b3);

                $totalSales = $salesB1 + $salesB2 + $salesB3;
                $selisih = $totalSales - $target;
                $persentase = $target > 0 ? round(($totalSales / $target) * 100) : 0;

                $dataSales[] = [
                    'nama_sales' => $nama,
                    'bulan_1'    => $salesB1,
                    'bulan_2'    => $salesB2,
                    'bulan_3'    => $salesB3,
                    'total'      => $totalSales,
                    'target'     => $target,
                    'selisih'    => $selisih,
                    'persentase' => $persentase,
                ];

                $totalKeseluruhan['bulan_1'] += $salesB1;
                $totalKeseluruhan['bulan_2'] += $salesB2;
                $totalKeseluruhan['bulan_3'] += $salesB3;
                $totalKeseluruhan['total']   += $totalSales;
                $totalKeseluruhan['target']  += $target;
                $totalKeseluruhan['selisih'] += $selisih;
            }

            $totalKeseluruhan['persentase'] = $totalKeseluruhan['target'] > 0
                ? round(($totalKeseluruhan['total'] / $totalKeseluruhan['target']) * 100)
                : 0;

            $laporan[$namaTriwulan] = [
                'nama_bulan'        => $konfigurasi['nama_bulan'],
                'data_sales'        => $dataSales,
                'total_keseluruhan' => $totalKeseluruhan,
            ];
        }

        // 7. Pengembalian data ke antarmuka pengguna (menghapus dd($user))
        return view('crm.LaporanPenjualan.laporan_for_gm', compact('laporan', 'tahun'));
    }

    private function kalkulasiSalesBulanan($peluangData, $idSales, $bulan)
    {
        if (!isset($peluangData[$idSales])) {
            return 0;
        }

        $dataBulan = $peluangData[$idSales]->firstWhere('bulan', $bulan);
        return $dataBulan ? $dataBulan->total_netsales : 0;
    }
}
