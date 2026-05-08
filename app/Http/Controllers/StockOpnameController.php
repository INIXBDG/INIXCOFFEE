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
            'stock_sekarang' => $request->stock_awal,
            'pic' => $request->pic,
        ]);

        return response()->json(['success' => true, 'message' => 'Barang berhasil ditambahkan']);
    }

    public function inlineUpdate(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:stock_opnames,id',
            'field' => 'required|in:nama_barang,stock_sekarang,kategori,satuan,notes',
            'value' => 'required',
        ]);

        $barang = StockOpname::findOrFail($request->id);
        $oldStock = (int) $barang->stock_sekarang;
        $barang->{$request->field} = $request->value;

        if ($request->field == 'stock_sekarang') {
            $newStock = (int) $request->value;

            if ($newStock < 0) {
                return response()->json(['error' => 'Stock tidak boleh negatif'], 400);
            }

            $selisih = $newStock - $oldStock;

            $karyawan = karyawan::where('id', Auth()->user()->id)->first();

            StockOpnameLog::create([
                'barang_id' => $barang->id,
                'tanggal' => now()->toDateString(),
                'stock_sebelumnya' => $oldStock,
                'stock_hari_ini' => $newStock,
                'selisih' => $selisih,
                'notes' => $barang->notes,
                'updated_by' => $karyawan->kode_karyawan,
            ]);

            $barang->stock_awal = $newStock;
        }

        $barang->save();
        return response()->json(['success' => true, 'new_baseline' => $barang->stock_awal]);
    }

    public function syncBaseline(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:stock_opnames,id',
            'stock_awal' => 'required|integer|min:0',
        ]);

        $barang = StockOpname::findOrFail($request->id);
        $barang->stock_awal = $request->stock_awal;
        $barang->save();

        return response()->json(['success' => true]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:stock_opnames,id',
            'nama_barang' => 'required',
            'stock_awal' => 'required|integer|min:0',
            'stock_sekarang' => 'required|integer|min:0',
            'kategori' => 'required',
            'satuan' => 'required',
        ]);

        $barang = StockOpname::findOrFail($request->id);
        $barang->update([
            'kode_barang' => $request->kode_barang,
            'nama_barang' => $request->nama_barang,
            'stock_awal' => $request->stock_awal,
            'stock_sekarang' => $request->stock_sekarang,
            'kategori' => $request->kategori,
            'satuan' => $request->satuan,
            'notes' => $request->notes,
            'pic' => $request->pic,
        ]);

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
