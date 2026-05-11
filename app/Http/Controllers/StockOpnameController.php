<?php

namespace App\Http\Controllers;

use App\Models\StockOpname;
use App\Models\karyawan;
use App\Models\StockOpnameLog;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StockOpnameExport;
use App\Exports\StockOpnameLogExport;
use Illuminate\Support\Facades\Auth;

class StockOpnameController extends Controller
{
    public function index()
    {
        $barang = StockOpname::latest()->get();
        $karyawan = Karyawan::whereIn('jabatan', ['HRD', 'Office Boy'])
            ->whereNotNull('NIP')
            ->where('status_aktif', '1')
            ->get();

        return view('office.stockopname.index', compact('barang', 'karyawan'));
    }

    public function getLog()
    {
        $dataLog = StockOpnameLog::with(['barang', 'karyawan'])
            ->latest()
            ->get();
        return response()->json($dataLog);
    }

    public function getData()
    {
        $barang = StockOpname::latest()->get();
        return response()->json($barang);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_barang' => 'required',
            'stock_awal' => 'required|integer|min:0',
            'kategori' => 'required',
            'satuan' => 'required',
        ]);

        StockOpname::create([
            'kode_barang' => 'BRG-' . time(),
            'nama_barang' => $request->nama_barang,
            'kategori' => $request->kategori,
            'satuan' => $request->satuan,
            'stock_awal' => $request->stock_awal,
            'stock_masuk' => 0,
            'stock_keluar' => 0,
            'stock_sekarang' => $request->stock_awal,
            'pic' => $request->pic,
        ]);

        return response()->json(['success' => true, 'message' => 'Barang berhasil ditambahkan']);
    }

    public function inlineUpdate(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:stock_opnames,id',
            'field' => 'required|in:nama_barang,stock_masuk,kategori,satuan,notes',
            'value' => 'required',
        ]);

        $barang = StockOpname::findOrFail($request->id);
        $oldValue = (int) $barang->{$request->field};
        $barang->{$request->field} = $request->value;

        if ($request->field == 'stock_masuk') {
            $newValue = (int) $request->value;

            if ($newValue < 0) {
                return response()->json(['error' => 'Stock masuk tidak boleh negatif'], 400);
            }

            $selisih = $newValue - $oldValue;

            $karyawan = karyawan::where('id', Auth()->user()->id)->first();

            StockOpnameLog::create([
                'barang_id' => $barang->id,
                'tanggal' => now()->toDateString(),
                'jenis_transaksi' => 'masuk',
                'stock_sebelumnya' => $oldValue,
                'stock_hari_ini' => $newValue,
                'selisih' => $selisih,
                'notes' => $barang->notes,
                'updated_by' => $karyawan->kode_karyawan,
            ]);
        }

        $barang->stock_sekarang = $barang->stock_awal + $barang->stock_masuk - $barang->stock_keluar;
        $barang->save();
        return response()->json(['success' => true, 'stock_sekarang' => $barang->stock_sekarang]);
    }

    public function storeKeluar(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:stock_opnames,id',
            'items.*.qty_keluar' => 'required|integer|min:1',
            'items.*.notes_keluar' => 'nullable|string|max:255',
        ]);

        $karyawan = karyawan::where('id', Auth()->user()->id)->first();
        $processed = 0;

        foreach ($request->items as $item) {
            $barang = StockOpname::findOrFail($item['barang_id']);
            $qtyKeluar = (int) $item['qty_keluar'];
            $currentKeluar = (int) $barang->stock_keluar;
            $newKeluar = $currentKeluar + $qtyKeluar;
            $stockSekarang = $barang->stock_awal + $barang->stock_masuk - $newKeluar;

            StockOpnameLog::create([
                'barang_id' => $barang->id,
                'tanggal' => now()->toDateString(),
                'jenis_transaksi' => 'keluar',
                'stock_sebelumnya' => $currentKeluar,
                'stock_hari_ini' => $newKeluar,
                'selisih' => $qtyKeluar,
                'notes' => $item['notes_keluar'],
                'updated_by' => $karyawan?->kode_karyawan,
            ]);

            $barang->update([
                'stock_keluar' => $newKeluar,
                'stock_sekarang' => $stockSekarang,
            ]);

            $processed++;
        }

        return response()->json([
            'success' => true, 
            'message' => "{$processed} barang berhasil dicatat",
            'processed' => $processed
        ]);
    }
    
    public function syncBaseline(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:stock_opnames,id',
            'stock_awal' => 'required|integer|min:0',
        ]);

        $barang = StockOpname::findOrFail($request->id);
        $barang->stock_awal = $request->stock_awal;
        $barang->stock_sekarang = $barang->stock_awal + $barang->stock_masuk - $barang->stock_keluar;
        $barang->save();

        return response()->json(['success' => true]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:stock_opnames,id',
            'nama_barang' => 'required',
            'stock_awal' => 'required|integer|min:0',
            'kategori' => 'required',
            'satuan' => 'required',
        ]);

        $barang = StockOpname::findOrFail($request->id);
        $barang->update([
            'kode_barang' => $request->kode_barang,
            'nama_barang' => $request->nama_barang,
            'stock_awal' => $request->stock_awal,
            'kategori' => $request->kategori,
            'satuan' => $request->satuan,
            'notes' => $request->notes,
            'pic' => $request->pic,
        ]);

        $barang->stock_sekarang = $barang->stock_awal + $barang->stock_masuk - $barang->stock_keluar;
        $barang->save();

        return response()->json(['success' => true, 'message' => 'Data berhasil diupdate']);
    }

    public function cleanLog(Request $request)
    {
        $query = StockOpnameLog::query();

        if ($request->tanggal) {
            $query->whereDate('created_at', $request->tanggal);
        }
        if ($request->bulan) {
            $query->whereMonth('created_at', $request->bulan);
        }
        if ($request->tahun) {
            $query->whereYear('created_at', $request->tahun);
        }

        $total = $query->count();
        $query->delete();

        return response()->json(['success' => true, 'message' => $total . ' log berhasil dihapus']);
    }

    public function delete($id)
    {
        StockOpname::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Barang berhasil dihapus']);
    }

    public function exportExcel()
    {
        return Excel::download(new StockOpnameExport(), 'stock-opname-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportPdf()
    {
        $barang = StockOpname::latest()->get();
        $pdf = Pdf::loadView('office.stockopname.pdf', compact('barang'))->setPaper('a4', 'landscape');
        return $pdf->download('stock-opname-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportLogExcel()
    {
        return Excel::download(new StockOpnameLogExport(), 'stock-opname-log-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportLogPdf()
    {
        $dataLog = StockOpnameLog::with(['barang', 'karyawan'])
            ->latest()
            ->get();
        $pdf = Pdf::loadView('office.stockopname.pdf-log', compact('dataLog'))->setPaper('a4', 'landscape');
        return $pdf->download('stock-opname-log-' . now()->format('Y-m-d') . '.pdf');
    }
}