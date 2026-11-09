<?php

namespace App\Http\Controllers;

use App\Models\PengajuanBarang;
use App\Models\karyawan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RekapPembelianItsmController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Memeriksa otorisasi khusus Koordinator ITSM (user ardhan).
     */
    private function authorizeKoordinatorItsm()
    {
        $user = auth()->user();
        if (!$user) {
            abort(403, 'Akses ditolak.');
        }

        $jabatan = $user->karyawan->jabatan ?? $user->jabatan ?? '';
        $username = strtolower($user->username ?? $user->name ?? '');

        $isKoordinatorItsm = (
            $jabatan === 'Koordinator ITSM' ||
            $username === 'ardhan' ||
            (method_exists($user, 'hasRole') && $user->hasRole('Koordinator ITSM'))
        );

        if (!$isKoordinatorItsm) {
            abort(403, 'Akses terbatas. Halaman ini hanya dapat diakses oleh Koordinator ITSM.');
        }
    }

    /**
     * Menampilkan halaman Rekap Pembelian ITSM.
     */
    public function index(Request $request)
    {
        $this->authorizeKoordinatorItsm();

        $user = auth()->user();
        $karyawanId = $user->karyawan_id ?? ($user->karyawan ? $user->karyawan->id : $user->id);

        $selectedYear = (int) $request->get('year', now()->year);
        $selectedMonth = $request->get('month', 'Semua');

        // Mengambil daftar tahun yang tersedia
        $availableYears = PengajuanBarang::selectRaw('YEAR(created_at) as year')
            ->whereNotNull('created_at')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->filter()
            ->values()
            ->toArray();

        if (empty($availableYears) || !in_array(now()->year, $availableYears)) {
            array_unshift($availableYears, now()->year);
            $availableYears = array_unique($availableYears);
            rsort($availableYears);
        }

        // Helper filter bulan
        $applyMonthFilter = function ($query) use ($selectedMonth) {
            if ($selectedMonth !== 'Semua' && is_numeric($selectedMonth)) {
                $query->whereMonth('created_at', (int)$selectedMonth);
            }
        };

        // 1. Data Pengajuan Pribadi bertipe Operasional (Koordinator ITSM / Ardhan)
        $queryPribadi = PengajuanBarang::with(['karyawan', 'detail', 'tracking'])
            ->where(function ($q) use ($karyawanId, $user) {
                $q->where('id_karyawan', $karyawanId);
                if ($user->karyawan) {
                    $q->orWhere('id_karyawan', $user->karyawan->id);
                }
            })
            ->where(function ($q) {
                $q->where('tipe', 'Operasional')
                  ->orWhere('tipe', 'operasional')
                  ->orWhere('tipe', 'oprasional');
            })
            ->whereYear('created_at', $selectedYear);

        $applyMonthFilter($queryPribadi);
        $pengajuanPribadi = $queryPribadi->latest('created_at')->get();

        // 2. Data Pengajuan bertipe Elektronik dari seluruh divisi ITSM
        $queryDivisi = PengajuanBarang::with(['karyawan', 'detail', 'tracking'])
            ->whereHas('karyawan', function ($q) {
                $q->where('divisi', 'IT Service Management')
                  ->orWhere('divisi', 'like', '%ITSM%');
            })
            ->where(function ($q) {
                $q->where('tipe', 'Elektronik')
                  ->orWhere('tipe', 'elektronik');
            })
            ->whereYear('created_at', $selectedYear);

        $applyMonthFilter($queryDivisi);
        $pengajuanDivisi = $queryDivisi->latest('created_at')->get();

        // Hitung total nominal per baris dan format data
        $pengajuanPribadi->each(function ($item) {
            $item->total_nominal = $item->detail->sum(function ($d) {
                $qty = (float) ($d->qty ?? 0);
                $harga = (float) ($d->harga ?? 0);
                return $qty * $harga;
            });
            $item->total_item = $item->detail->sum(function ($d) {
                return (int) ($d->qty ?? 0);
            });
            $item->nama_barang_list = $item->detail->pluck('nama_barang')->filter()->implode(', ');
            $item->status_tracking = $item->tracking ? $item->tracking->tracking : 'Menunggu Approval';
        });

        $pengajuanDivisi->each(function ($item) {
            $item->total_nominal = $item->detail->sum(function ($d) {
                $qty = (float) ($d->qty ?? 0);
                $harga = (float) ($d->harga ?? 0);
                return $qty * $harga;
            });
            $item->total_item = $item->detail->sum(function ($d) {
                return (int) ($d->qty ?? 0);
            });
            $item->nama_barang_list = $item->detail->pluck('nama_barang')->filter()->implode(', ');
            $item->status_tracking = $item->tracking ? $item->tracking->tracking : 'Menunggu Approval';
        });

        // Summary metrics
        $totalPribadiNominal = $pengajuanPribadi->sum('total_nominal');
        $totalPribadiCount   = $pengajuanPribadi->count();

        $totalDivisiNominal  = $pengajuanDivisi->sum('total_nominal');
        $totalDivisiCount    = $pengajuanDivisi->count();

        $grandTotalNominal   = $totalPribadiNominal + $totalDivisiNominal;
        $grandTotalCount     = $totalPribadiCount + $totalDivisiCount;

        // ========================================================
        // AGREGASI DATA UNTUK GRAFIK ANALITIK
        // ========================================================

        // 1. Analisis Operasional Pribadi
        $pribadiDetails = collect();
        $pribadiStatusCounts = [];
        $pribadiMonthly = array_values(array_fill(0, 12, 0));

        foreach ($pengajuanPribadi as $p) {
            $st = $p->status_tracking ?: 'Menunggu Approval';
            $pribadiStatusCounts[$st] = ($pribadiStatusCounts[$st] ?? 0) + 1;
            $m = $p->created_at ? (int)$p->created_at->format('n') - 1 : 0;
            $m = max(0, min(11, $m));

            foreach ($p->detail as $d) {
                $qty = (float)($d->qty ?? 0);
                $harga = (float)($d->harga ?? 0);
                $subtotal = $qty * $harga;
                $name = trim($d->nama_barang);
                if (!empty($name)) {
                    $pribadiDetails->push([
                        'nama' => $name,
                        'qty' => $qty,
                        'subtotal' => $subtotal,
                    ]);
                    $pribadiMonthly[$m] += $subtotal;
                }
            }
        }

        $pribadiTopQty = $pribadiDetails->groupBy(fn($i) => strtolower($i['nama']))->map(fn($g) => [
            'nama' => $g->first()['nama'],
            'qty' => $g->sum('qty'),
            'nominal' => $g->sum('subtotal')
        ])->sortByDesc('qty')->take(5)->values();

        $pribadiTopNominal = $pribadiDetails->groupBy(fn($i) => strtolower($i['nama']))->map(fn($g) => [
            'nama' => $g->first()['nama'],
            'nominal' => $g->sum('subtotal'),
            'qty' => $g->sum('qty')
        ])->sortByDesc('nominal')->take(5)->values();

        // 2. Analisis Elektronik Divisi ITSM
        $divisiDetails = collect();
        $divisiStatusCounts = [];
        $divisiMonthly = array_values(array_fill(0, 12, 0));
        $divisiByEmployee = [];

        foreach ($pengajuanDivisi as $p) {
            $st = $p->status_tracking ?: 'Menunggu Approval';
            $divisiStatusCounts[$st] = ($divisiStatusCounts[$st] ?? 0) + 1;
            $m = $p->created_at ? (int)$p->created_at->format('n') - 1 : 0;
            $m = max(0, min(11, $m));
            $empName = $p->karyawan ? $p->karyawan->nama_lengkap : 'Lainnya';

            foreach ($p->detail as $d) {
                $qty = (float)($d->qty ?? 0);
                $harga = (float)($d->harga ?? 0);
                $subtotal = $qty * $harga;
                $name = trim($d->nama_barang);
                if (!empty($name)) {
                    $divisiDetails->push([
                        'nama' => $name,
                        'qty' => $qty,
                        'subtotal' => $subtotal,
                    ]);
                    $divisiMonthly[$m] += $subtotal;
                    $divisiByEmployee[$empName] = ($divisiByEmployee[$empName] ?? 0) + $subtotal;
                }
            }
        }

        $divisiTopQty = $divisiDetails->groupBy(fn($i) => strtolower($i['nama']))->map(fn($g) => [
            'nama' => $g->first()['nama'],
            'qty' => $g->sum('qty'),
            'nominal' => $g->sum('subtotal')
        ])->sortByDesc('qty')->take(5)->values();

        $divisiTopNominal = $divisiDetails->groupBy(fn($i) => strtolower($i['nama']))->map(fn($g) => [
            'nama' => $g->first()['nama'],
            'nominal' => $g->sum('subtotal'),
            'qty' => $g->sum('qty')
        ])->sortByDesc('nominal')->take(5)->values();

        // Sort employee spending descending
        arsort($divisiByEmployee);

        // 3. Gabungan / Overview
        $monthlyTrendPribadi = $pribadiMonthly;
        $monthlyTrendDivisi  = $divisiMonthly;

        return view('itsm.rekap_pembelian.index', compact(
            'pengajuanPribadi',
            'pengajuanDivisi',
            'selectedYear',
            'selectedMonth',
            'availableYears',
            'totalPribadiNominal',
            'totalPribadiCount',
            'totalDivisiNominal',
            'totalDivisiCount',
            'grandTotalNominal',
            'grandTotalCount',
            'pribadiTopQty',
            'pribadiTopNominal',
            'pribadiMonthly',
            'pribadiStatusCounts',
            'divisiTopQty',
            'divisiTopNominal',
            'divisiMonthly',
            'divisiByEmployee',
            'divisiStatusCounts',
            'monthlyTrendPribadi',
            'monthlyTrendDivisi'
        ));
    }

    /**
     * Mengambil detail pengajuan barang beserta item dan tracking (AJAX / Modal).
     */
    public function getDetail($id)
    {
        $this->authorizeKoordinatorItsm();

        $pengajuan = PengajuanBarang::with(['karyawan', 'detail', 'tracking'])->findOrFail($id);

        $totalNominal = $pengajuan->detail->sum(function ($d) {
            $qty = (float) ($d->qty ?? 0);
            $harga = (float) ($d->harga ?? 0);
            return $qty * $harga;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $pengajuan->id,
                'tipe' => $pengajuan->tipe,
                'created_at' => $pengajuan->created_at ? $pengajuan->created_at->format('d M Y H:i') : '-',
                'pemohon' => [
                    'nama' => $pengajuan->karyawan->nama_lengkap ?? '-',
                    'jabatan' => $pengajuan->karyawan->jabatan ?? '-',
                    'divisi' => $pengajuan->karyawan->divisi ?? '-',
                ],
                'status_tracking' => $pengajuan->tracking ? $pengajuan->tracking->tracking : 'Menunggu Approval',
                'invoice' => $pengajuan->invoice ? asset('storage/' . $pengajuan->invoice) : null,
                'bukti' => $pengajuan->bukti ? asset('storage/' . $pengajuan->bukti) : null,
                'total_nominal' => $totalNominal,
                'items' => $pengajuan->detail->map(function ($item) {
                    $qty = (float) ($item->qty ?? 0);
                    $harga = (float) ($item->harga ?? 0);
                    return [
                        'nama_barang' => $item->nama_barang,
                        'qty' => $item->qty,
                        'harga' => $harga,
                        'subtotal' => $qty * $harga,
                        'keterangan' => $item->keterangan ?? '-',
                    ];
                }),
            ]
        ]);
    }
}
