<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
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
        $query = RKM::with(['exam', 'perhitunganNetSales.peserta', 'materi', 'perusahaan','invoice'])
            ->orderByDesc('tanggal_awal');

        if ($status !== null) {
            $query->where('status', $status);
        } else {
            $query->whereIn('status', ['0', '2']);
        }

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

        // Inisialisasi variable untuk total keseluruhan
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
                    'transportasi' => 0.0,
                    'penginapan' => 0.0,
                    'fresh_money' => 0.0,
                    'cashback' => 0.0,
                    'diskon' => 0.0,
                    'entertaint' => 0.0,
                    'souvenir' => 0.0,
                    'pembayaran' => null,
                    'grand_total' => 0.0
                ];
                $netsales = collect();
            } else {
                $sum = [
                    'transportasi' => (float) $netsales->sum('transportasi'),
                    'penginapan' => (float) $netsales->sum('penginapan'),
                    'fresh_money' => (float) $netsales->sum('fresh_money'),
                    'cashback' => (float) $netsales->sum('cashback'),
                    'diskon' => (float) $netsales->sum('diskon'),
                    'entertaint' => (float) $netsales->sum('entertaint'),
                    'souvenir' => (float) $netsales->sum('souvenir'),
                    'pembayaran' => $netsales->pluck('tipe_pembayaran')->first(),
                ];

                $sum['grand_total'] =
                    $sum['transportasi'] +
                    $sum['penginapan'] +
                    $sum['fresh_money'] +
                    $sum['cashback'] +
                    $sum['diskon'] +
                    $sum['entertaint'] +
                    $sum['souvenir'];
            }

            $hargaJual = (float) ($item->harga_jual ?? 0.0);
            $totalPenjualan = $hargaJual * $pax;

            $grandtotal = $totalPenjualan - ($sum['grand_total'] + $totalexam);

            // Akumulasi total keseluruhan
            $totalHargaJualKeseluruhan += $hargaJual;
            $totalNetSalesKeseluruhan += $sum['grand_total'];
            $totalExamKeseluruhan += $totalexam;
            $totalGrandKeseluruhan += $grandtotal;

            return [
                'id' => $item->id,
                'sales_key' => $item->sales_key,
                'materi_key' => $item->materi_key,
                'perusahaan_key' => $item->perusahaan_key,
                'pax' => $pax,
                'harga' => $hargaJual,
                'total_penjualan' => $totalPenjualan, // Total Harga x Pax
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
            ];
        });

        return response()->json([
            'data' => $data,
            'summary' => [
                'total_harga_jual' => $totalHargaJualKeseluruhan,
                'total_netsales' => $totalNetSalesKeseluruhan,
                'total_exam' => $totalExamKeseluruhan,
                'total_grand' => $totalGrandKeseluruhan,
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
        $peserta = perhitunganNetSales::with('peserta')->where('id_rkm', $id)->count();
        $netsales = perhitunganNetSales::with('trackingNetSales', 'approvedNetSales', 'peserta')
            ->where('id_rkm', $id)
            ->get();

        return view('crm.LaporanPenjualan.editpa', compact('pa', 'netsales', 'peserta'));
    }

    public function updatePA(Request $request, $id)
    {
        $fields = [
            'transportasi',
            'penginapan',
            'cashback',
            'fresh_money',
            'entertaint',
            'souvenir',
            'harga_penawaran'
        ];

        // Bersihkan angka dari format rupiah
        foreach ($fields as $field) {
            if ($request->has($field)) {
                $request->merge([
                    $field => preg_replace('/[^\d]/', '', $request->input($field))
                ]);
            }
        }

        $validated = $request->validate([
            'transportasi'     => 'nullable|numeric|min:0',
            'penginapan'       => 'nullable|numeric|min:0',
            'cashback'         => 'nullable|numeric|min:0',
            'fresh_money'      => 'nullable|numeric|min:0',
            'entertaint'       => 'nullable|numeric|min:0',
            'souvenir'         => 'nullable|numeric|min:0',
            'desc'             => 'nullable|string|max:500',
            'harga_penawaran'  => 'nullable|numeric|min:0',
            'tgl_pa'           => 'nullable|date',
            'tipe_pembayaran'  => 'nullable|string|in:cash,transfer,credit',
        ]);

        // Ambil data lama sebelum update
        $pa = perhitunganNetSales::findOrFail($id);
        $oldData = $pa->only(array_keys($validated));

        // Update data baru
        $pa->update($validated);
        $newData = $pa->only(array_keys($validated));

        // Deteksi perubahan
        $changed = [];
        foreach ($newData as $key => $value) {
            if ($oldData[$key] != $value) {
                $changed[$key] = [
                    'before' => $oldData[$key],
                    'after' => $value,
                ];
            }
        }

        // Ambil user penerima notifikasi
        $users = User::whereIn('jabatan', ['GM', 'Adm Sales', 'Finance & Accounting'])->get();

        // Ambil data RKM
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

        Notification::send($users, new UpdateLaporanPenjualan($data, $path));

        return response()->json([
            'success' => true,
            'message' => 'Data penawaran acara berhasil diperbarui.',
            'data' => $pa
        ]);
    }
}
