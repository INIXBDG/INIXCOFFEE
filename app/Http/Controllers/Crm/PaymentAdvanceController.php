<?php

namespace App\Http\Controllers\crm;

use App\Http\Controllers\Controller;
use App\Models\karyawan;
use App\Models\RKM;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentAdvanceController extends Controller
{

    public function __construct(){
        $this->middleware('auth');
        $this->middleware('permission:View CRM PaymentAdvance', ['only' => ['index', 'getData']]);
        $this->middleware('permission:Create CRM PaymentAdvance', ['only' => ['form', 'printPdf']]);
    }

    public function index()
    {
        $sales = karyawan::where('jabatan', 'Sales')->where('status_aktif', '1')->get();

        // Daftar tahun untuk filter (selalu memuat tahun berjalan)
        $tahunList = RKM::selectRaw('YEAR(tanggal_awal) as y')
            ->whereNotNull('tanggal_awal')
            ->distinct()
            ->pluck('y')
            ->push((int) now()->year)
            ->unique()
            ->sortDesc()
            ->values();

        return view('crm.paymentAdvance.index', compact('sales', 'tahunList'));
    }

    public function getData(Request $request)
    {
        $tahun   = (int) $request->input('tahun', now()->year);
        $bulan   = (int) $request->input('bulan');     // 1-12, kosong = semua
        $quartal = (int) $request->input('quartal');   // 1-4, kosong = semua
        $sales   = $request->input('sales');

        // Rentang tanggal berdasarkan tanggal_awal
        if ($bulan >= 1 && $bulan <= 12) {
            $start = Carbon::create($tahun, $bulan, 1)->startOfMonth();
            $end   = $start->copy()->endOfMonth();
        } elseif ($quartal >= 1 && $quartal <= 4) {
            $start = Carbon::create($tahun, ($quartal - 1) * 3 + 1, 1)->startOfMonth();
            $end   = $start->copy()->addMonths(2)->endOfMonth();
        } else {
            $start = Carbon::create($tahun, 1, 1)->startOfYear();
            $end   = $start->copy()->endOfYear();
        }

        $rkms = RKM::with('perhitunganNetSales.approvedNetSales', 'perusahaan', 'materi', 'peluang')
            ->whereIn('status', ['3', '0'])
            ->whereBetween('tanggal_awal', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->when($sales, fn ($q) => $q->where('sales_key', $sales))
            ->orderBy('tanggal_awal', 'asc')
            ->get();

        // Field turunan dihitung di server dari tanggal_awal
        $rkms->each(function ($rkm) {
            $tgl = $rkm->tanggal_awal ? Carbon::parse($rkm->tanggal_awal) : null;

            $rkm->week_start = $tgl ? $tgl->copy()->startOfWeek()->toDateString() : null; // Senin
            $rkm->bulan_no   = $tgl ? $tgl->month : null;
            $rkm->quartal_no = $tgl ? (int) ceil($tgl->month / 3) : null;
            $rkm->tahun_no   = $tgl ? $tgl->year : null;
            $rkm->tgl_awal_ymd  = $tgl ? $tgl->toDateString() : null;
            $rkm->tgl_akhir_ymd = $rkm->tanggal_akhir ? Carbon::parse($rkm->tanggal_akhir)->toDateString() : null;
        });

        return response()->json($rkms);
    }

    public function form($idRkm)
    {
        $rkm = RKM::with('perhitunganNetSales.approvedNetSales', 'perusahaan', 'materi')->findOrFail($idRkm);

        // Penandatangan: ambil nama dan ttd sekaligus
        $requester = karyawan::where('kode_karyawan', $rkm->sales_key)->first(['nama_lengkap', 'ttd']);
        $manager   = karyawan::where('jabatan', 'SPV Sales')->where('status_aktif', '1')->first(['nama_lengkap', 'ttd']);
        $gm        = karyawan::where('jabatan', 'GM')->where('status_aktif', '1')->first(['nama_lengkap', 'ttd']);
        $director  = karyawan::where('jabatan', 'Direktur Utama')->where('status_aktif', '1')->first(['nama_lengkap', 'ttd']);
        $finance   = karyawan::where('jabatan', 'Finance & Accounting')->where('status_aktif', '1')->where('kode_karyawan', 'RF')->first(['nama_lengkap', 'ttd']);

        $salesName = $requester->nama_lengkap ?? $rkm->sales_key;

        $tglAwal  = $rkm->tanggal_awal ? Carbon::parse($rkm->tanggal_awal) : null;
        $tglAkhir = $rkm->tanggal_akhir ? Carbon::parse($rkm->tanggal_akhir) : null;
        $tglKelas = $tglAwal
            ? $tglAwal->format('d/m/Y') . ($tglAkhir ? ' - ' . $tglAkhir->format('d/m/Y') : '')
            : '';

        // Mendukung relasi hasOne maupun hasMany
        $rel  = $rkm->perhitunganNetSales;
        $list = $rel instanceof \Illuminate\Support\Collection ? $rel : collect($rel ? [$rel] : []);

        // Label biaya Payment Advance
        $labels = [
            'transportasi'      => 'Transportasi',
            'akomodasi_peserta' => 'Akomodasi Peserta',
            'akomodasi_tim'     => 'Akomodasi Tim',
            'fresh_money'       => 'Fresh Money',
            'entertaint'        => 'Entertaint',
            'souvenir'          => 'Souvenir',
            'cashback'          => 'Cashback',
            'sewa_laptop'       => 'Sewa Laptop',
        ];

        $items = [];
        foreach ($list as $pa) {
            foreach ($labels as $field => $label) {
                $nilai = (float) ($pa->$field ?? 0);
                if ($nilai <= 0) continue;

                $ket = match ($field) {
                    'transportasi'  => $pa->jenis_transportasi,
                    'akomodasi_tim' => $pa->keterangan_akomodasi_tim,
                    'entertaint'    => $pa->keterangan_entertaint,
                    default         => null,
                };

                $items[] = [
                    'description'   => $label . ($ket ? ' (' . $ket . ')' : ''),
                    'qty'           => 1,
                    'amount'        => $nilai,
                    'type'          => strtolower((string) $pa->tipe_pembayaran) === 'cash' ? 'Cash' : 'Transfer',
                    'date_required' => $pa->tgl_pa ? Carbon::parse($pa->tgl_pa)->toDateString() : '',
                ];
            }
        }

        // Baris Net otomatis di paling atas: qty = pax, amount = harga_jual x pax
        $pax = (int) $rkm->pax;
        array_unshift($items, [
            'description'   => 'Net',
            'qty'           => $pax,
            'amount'        => (float) $rkm->harga_jual * $pax,
            'type'          => 'Non',
            'date_required' => '',
        ]);

        // Semua approval dari setiap perhitungan Net Sales
        $approved = $list->flatMap(function ($pa) {
            $a = $pa->approvedNetSales;
            return $a instanceof \Illuminate\Support\Collection ? $a : collect($a ? [$a] : []);
        })->values();

        $firstPa = $list->first();

        return view('crm.paymentAdvance.form', [
            'rkm'       => $rkm,

            // Approval
            'approved'  => $approved,
            'salesName' => $salesName,
            'requester' => $requester,
            'manager'   => $manager,
            'gm'        => $gm,
            'director'  => $director,
            'finance'   => $finance,

            // Isi form
            'tglPa'     => $firstPa && $firstPa->tgl_pa ? Carbon::parse($firstPa->tgl_pa)->toDateString() : now()->toDateString(),
            'nama'      => $salesName,
            'divisi'    => 'Sales',
            'project'   => '',
            'materi'    => $rkm->materi->nama_materi ?? '',
            'tglKelas'  => $tglKelas,
            'instansi'  => $rkm->perusahaan->nama_perusahaan ?? '',
            'items'     => $items,
            'catatan'   => $list->pluck('deskripsi_tambahan')->filter()->implode("\n"),
        ]);
    }

    // Terima semua isi form, masukkan ke template PDF, lalu stream
    public function printPdf(Request $request)
    {
        $request->validate([
            'id_rkm'                => 'required|exists:r_k_m_s,id',
            'tgl_pa'                => 'nullable|date',
            'nama'                  => 'nullable|string|max:100',
            'divisi'                => 'nullable|string|max:100',
            'project'               => 'nullable|string|max:255',
            'materi'                => 'nullable|string|max:255',
            'tgl_pelatihan'         => 'nullable|string|max:100',
            'instansi'              => 'nullable|string|max:255',
            'catatan'               => 'nullable|string|max:2000',
            'ppn_persen'            => 'nullable|numeric|min:0|max:100',
            'pph_persen'            => 'nullable|numeric|min:0|max:100',
            'items'                 => 'required|array|min:1',
            'items.*.description'   => 'nullable|string|max:255',
            'items.*.qty'           => 'nullable|numeric|min:0',
            'items.*.amount'        => 'nullable|numeric|min:0',
            'items.*.type'          => 'nullable|in:Transfer,Cash,Non',
            'items.*.date_required' => 'nullable|date',
        ]);

        // Buang baris kosong, hitung ulang total di server
        $items = collect($request->input('items', []))
            ->filter(fn ($i) => trim($i['description'] ?? '') !== '' || (float) ($i['amount'] ?? 0) > 0)
            ->map(fn ($i) => [
                'description'   => $i['description'] ?? '',
                'qty'           => $i['qty'] ?? '',
                'amount'        => (float) ($i['amount'] ?? 0),
                'type'          => $i['type'] ?? 'Non',
                'date_required' => !empty($i['date_required'])
                    ? Carbon::parse($i['date_required'])->locale('id')->translatedFormat('d M Y')
                    : '',
            ])
            ->values();

        $total     = $items->sum('amount');
        $ppnPersen = (float) $request->input('ppn_persen', 0);
        $pphPersen = (float) $request->input('pph_persen', 0);   // kosong = 0, baris PPH tidak dicetak
        $ppn       = $total * $ppnPersen / 100;
        $pph       = $total * $pphPersen / 100;

        $totalKeseluruhan = $total + $ppn + $pph;

        // Approval tidak bisa diedit, selalu dihitung ulang dari data di server
        $rkm = RKM::with('perhitunganNetSales.approvedNetSales')->findOrFail($request->id_rkm);

        $rel  = $rkm->perhitunganNetSales;
        $list = $rel instanceof \Illuminate\Support\Collection ? $rel : collect($rel ? [$rel] : []);

        $approved = $list->flatMap(function ($pa) {
            $a = $pa->approvedNetSales;
            return $a instanceof \Illuminate\Support\Collection ? $a : collect($a ? [$a] : []);
        })->values();

        // Penandatangan: ambil nama dan ttd sekaligus
        $requester = karyawan::where('kode_karyawan', $rkm->sales_key)->first(['nama_lengkap', 'ttd']);
        $manager   = karyawan::where('jabatan', 'SPV Sales')->where('status_aktif', '1')->first(['nama_lengkap', 'ttd']);
        $gm        = karyawan::where('jabatan', 'GM')->where('status_aktif', '1')->first(['nama_lengkap', 'ttd']);
        $director  = karyawan::where('jabatan', 'Direktur Utama')->where('status_aktif', '1')->first(['nama_lengkap', 'ttd']);
        $finance   = karyawan::where('jabatan', 'Finance & Accounting')->where('status_aktif', '1')->where('kode_karyawan', 'RF')->first(['nama_lengkap', 'ttd']);

        $salesName = $requester->nama_lengkap ?? $rkm->sales_key;

        $data = [
            'logo'             => public_path('assets/img/inix.png'),

            // Isi form
            'tglPa'            => $request->tgl_pa ? Carbon::parse($request->tgl_pa)->format('d/m/Y') : '',
            'nama'             => $request->input('nama', ''),
            'divisi'           => $request->input('divisi', ''),
            'project'          => $request->input('project', ''),
            'materi'           => $request->input('materi', ''),
            'tglKelas'         => $request->input('tgl_pelatihan', ''),
            'instansi'         => $request->input('instansi', ''),
            'catatan'          => $request->input('catatan', ''),
            'items'            => $items,
            'total'            => $total,
            'ppnPersen'        => $ppnPersen,
            'ppn'              => $ppn,
            'pphPersen'        => $pphPersen,
            'pph'              => $pph,
            'totalKeseluruhan' => $totalKeseluruhan,

            // Approval
            'approved'         => $approved,
            'salesName'        => $salesName,
            'requester'        => $requester,
            'manager'          => $manager,
            'gm'               => $gm,
            'director'         => $director,
            'finance'          => $finance,
        ];

        $filename = 'payment-advance-' . Str::slug($data['nama'] ?: 'pa') . '.pdf';

        return Pdf::loadView('crm.paymentAdvance.pdf', $data)
            ->setPaper('a4', 'portrait')
            ->stream($filename);
    }
}