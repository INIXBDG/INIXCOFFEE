<?php

namespace App\Http\Controllers;

use App\Models\JurnalAkuntansi;
use Illuminate\Http\Request;
use App\Models\PengajuanBarang;

class JurnalAkuntansiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Menampilkan halaman indeks jurnal akuntansi.
     */
    public function index()
    {
        return view('jurnalakuntansi.index');
    }

    /**
     * Mengembalikan data JSON untuk DataTables.
     */
    public function getData(Request $request)
    {
        $query = JurnalAkuntansi::query();

        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('tanggal_transaksi', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('tanggal_transaksi', '<=', $request->end_date);
        }

        $data = $query->latest()->get();

        return response()->json([
            'data' => $data
        ]);
    }

    /**
     * Mengembalikan data JSON pengajuan barang yang belum memiliki jurnal.
     */
    public function getBelumJurnal()
    {
        // Mengambil data pengajuan yang statusnya sudah selesai namun tidak ada di tabel jurnal_akuntansis
        $data = PengajuanBarang::with(['karyawan', 'detail', 'tracking'])
            ->whereDoesntHave('jurnalAkuntansi')
            ->whereHas('tracking', function($query) {
                $query->whereIn('tracking', ['Selesai', 'Pencairan Sudah Selesai']);
            })
            ->get();
        
        $formattedData = $data->map(function($item) {
            $totalHarga = 0;
            foreach($item->detail as $det) {
                $qtyValue = (int) $det->qty;
                $harga = explode('.', $det->harga);
                $hargaValue = (float) $harga[0];
                $totalHarga += ($qtyValue * $hargaValue);
            }

            return [
                'id' => $item->id,
                'nama_karyawan' => $item->karyawan->nama_lengkap ?? '-',
                'tipe' => $item->tipe,
                'tanggal' => $item->created_at->format('Y-m-d'),
                'total' => $totalHarga,
                'tracking' => $item->tracking,
                'detail_pengajuan_barang' => $item->detail,
            ];
        });

        return response()->json(['data' => $formattedData]);
    }

    /**
     * Menyimpan jurnal akuntansi secara manual dari pengajuan barang.
     */
    public function storeManual($id)
    {
        $pengajuan = PengajuanBarang::with('detail')->findOrFail($id);
        
        $jurnalExist = JurnalAkuntansi::where('id_pengajuan_barang', $id)->first();
        if ($jurnalExist) {
            return response()->json(['success' => false, 'message' => 'Jurnal untuk pengajuan ini sudah ada!']);
        }

        $totalPengeluaran = 0;
        foreach ($pengajuan->detail as $item) {
            $qtyValue = (int) $item->qty;
            $harga = explode('.', $item->harga);
            $hargaValue = (float) $harga[0];
            $totalPengeluaran += ($qtyValue * $hargaValue);
        }

        JurnalAkuntansi::create([
            'id_pengajuan_barang' => $id,
            'tanggal_transaksi' => now(),
            'keterangan' => 'Pengeluaran untuk Pengajuan Barang ID: ' . $id . ' (' . $pengajuan->tipe . ')',
            'debit' => $totalPengeluaran,
            'kredit' => 0,
        ]);

        return response()->json(['success' => true, 'message' => 'Jurnal berhasil dibuat!']);
    }

    /**
     * Mengambil data spesifik jurnal akuntansi untuk form edit.
     */
    /**
     * Mengambil data spesifik jurnal akuntansi untuk form edit beserta identifikasi jenis.
     */
    public function edit($id)
    {
        $jurnal = JurnalAkuntansi::findOrFail($id);
        $is_petty_cash = is_null($jurnal->id_pengajuan_barang);
        
        return response()->json([
            'success' => true,
            'data' => $jurnal,
            'is_petty_cash' => $is_petty_cash
        ]);
    }

    /**
     * Memperbarui data jurnal akuntansi di dalam database berdasarkan jenis jurnal.
     */
    public function update(Request $request, $id)
    {
        $jurnal = JurnalAkuntansi::findOrFail($id);
        $is_petty_cash = is_null($jurnal->id_pengajuan_barang);

        if ($is_petty_cash) {
            $request->validate([
                'tanggal_transaksi' => 'required|date',
                'keterangan' => 'required|string',
                'tipe_transaksi' => 'required|in:debit,kredit',
                'nominal' => 'required|numeric|min:0',
            ]);

            $debit = $request->tipe_transaksi === 'debit' ? $request->nominal : 0;
            $kredit = $request->tipe_transaksi === 'kredit' ? $request->nominal : 0;

            $jurnal->update([
                'tanggal_transaksi' => $request->tanggal_transaksi,
                'keterangan' => $request->keterangan,
                'debit' => $debit,
                'kredit' => $kredit,
            ]);
        } else {
            $request->validate([
                'tanggal_transaksi' => 'required|date',
                'keterangan' => 'required|string',
                'kredit' => 'required|numeric|min:0',
            ]);

            $jurnal->update([
                'tanggal_transaksi' => $request->tanggal_transaksi,
                'keterangan' => $request->keterangan,
                'kredit' => $request->kredit,
            ]);
        }

        return response()->json([
            'success' => true, 
            'message' => 'Data Jurnal Akuntansi berhasil diperbarui.'
        ]);
    }

    /**
     * Menyimpan data jurnal akuntansi manual (Kas Kecil).
     */
    public function storePettyCash(Request $request)
    {
        $request->validate([
            'tanggal_transaksi' => 'required|date',
            'keterangan' => 'required|string',
            'tipe_transaksi' => 'required|in:debit,kredit',
            'nominal' => 'required|numeric|min:1',
        ]);

        $debit = $request->tipe_transaksi === 'debit' ? $request->nominal : 0;
        $kredit = $request->tipe_transaksi === 'kredit' ? $request->nominal : 0;

        JurnalAkuntansi::create([
            'id_pengajuan_barang' => null, // Kosong karena tidak merujuk ke pengajuan
            'tanggal_transaksi' => $request->tanggal_transaksi,
            'keterangan' => '[Kas Kecil] ' . $request->keterangan,
            'debit' => $debit,
            'kredit' => $kredit,
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'Data Kas Kecil berhasil ditambahkan.'
        ]);
    }
}