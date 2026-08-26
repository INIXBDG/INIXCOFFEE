<?php

namespace App\Http\Controllers\office;

use App\Http\Controllers\Controller;
use App\Models\KondisiTools;
use App\Models\ObTools;
use App\Imports\KondisiToolsImport; 
use App\Exports\KondisiToolsEksport; 
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class KondisiToolsController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:View KondisiTools', ['only' => ['index', 'getTools', 'getPemeriksaan']]);
        $this->middleware('permission:Store KondisiTools', ['only' => ['store']]);
        $this->middleware('permission:Update KondisiTools', ['only' => ['update']]);
        $this->middleware('permission:Delete KondisiTools', ['only' => ['delete']]);
        }

    public function index()
    {
        $tools = ObTools::with([
            'kondisiTools' => function ($q) {
                $q->latest('tanggal_pemeriksaan');
            }
        ])->get();

        $kondisiTools = KondisiTools::with('alat')
            ->latest()
            ->get();

        $kategoriTools = ObTools::select('kategori')
            ->distinct()
            ->pluck('kategori');

        return view(
            'office.kondisiTools.index',
            compact('tools', 'kondisiTools', 'kategoriTools')
        );
    }

    public function getTools(Request $request){
        $query = ObTools::with([
            'kondisiTools' => function ($q) {
                $q->latest('tanggal_pemeriksaan');
            }
        ]);

        if ($request->has('search') && !empty($request->search['value'])) {
            $searchValue = $request->search['value'];
            $query->where(function($q) use ($searchValue) {
                $q->where('nama_alat', 'like', "%{$searchValue}%")
                  ->orWhere('kategori', 'like', "%{$searchValue}%");
            });
        }

        if ($request->has('order')) {
            $columnIndex = $request->order[0]['column'];
            $direction = $request->order[0]['dir'];
            
            $columns = ['id', 'nama_alat', 'kategori', 'qty'];
            if (isset($columns[$columnIndex])) {
                $query->orderBy($columns[$columnIndex], $direction);
            }
        }

        $totalRecords = ObTools::count();
        $filteredRecords = $query->count();
        
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        
        $tools = $query->skip($start)->take($length)->get();

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $tools
        ]);
    }

    public function getPemeriksaan(Request $request)
    {
        $query = KondisiTools::with('alat');

        // Ambil parameter filter
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');

        // DEFAULT: Jika tidak ada filter, tampilkan data HARI INI saja
        if (empty($tanggalMulai) && empty($tanggalSelesai)) {
            $today = now()->format('Y-m-d');
            $query->whereDate('tanggal_pemeriksaan', $today);
        } else {
            // Filter sesuai rentang tanggal
            if (!empty($tanggalMulai)) {
                $query->whereDate('tanggal_pemeriksaan', '>=', $tanggalMulai);
            }
            if (!empty($tanggalSelesai)) {
                $query->whereDate('tanggal_pemeriksaan', '<=', $tanggalSelesai);
            }
        }

        // Handle search dari DataTables
        if ($request->has('search') && !empty($request->search['value'])) {
            $searchValue = $request->search['value'];
            $query->where(function($q) use ($searchValue) {
                $q->whereHas('alat', function($subQ) use ($searchValue) {
                    $subQ->where('nama_alat', 'like', "%{$searchValue}%");
                })
                ->orWhere('kondisi', 'like', "%{$searchValue}%")
                ->orWhere('catatan', 'like', "%{$searchValue}%");
            });
        }

        // Handle ordering dari DataTables
        if ($request->has('order')) {
            $columnIndex = $request->order[0]['column'];
            $direction = $request->order[0]['dir'];
            
            $columns = [
                0 => null, // No (skip)
                1 => 'tanggal_pemeriksaan',
                2 => null, // nama_alat (relasi) - akan dihandle khusus
                3 => 'kondisi',
                4 => 'catatan',
            ];

            if ($columnIndex == 2) {
                // Sorting berdasarkan nama alat (relasi)
                $query->join('ob_tools', 'kondisi_tools.id_alat', '=', 'ob_tools.id')
                    ->orderBy('ob_tools.nama_alat', $direction)
                    ->select('kondisi_tools.*');
            } elseif (isset($columns[$columnIndex]) && $columns[$columnIndex] !== null) {
                $query->orderBy($columns[$columnIndex], $direction);
            }
        } else {
            // Default sort: tanggal terbaru
            $query->orderBy('tanggal_pemeriksaan', 'desc');
        }

        $totalRecords = KondisiTools::count();
        $filteredRecords = $query->count();

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        $kondisiTools = $query->skip($start)->take($length)->get();

        // Format data
        $formattedData = $kondisiTools->map(function($item) {
            return [
                'id' => $item->id,
                'id_alat' => $item->id_alat,
                'nama_alat' => $item->alat ? $item->alat->nama_alat : '-',
                'kondisi' => $item->kondisi,
                'tanggal_pemeriksaan' => $item->tanggal_pemeriksaan,
                'catatan' => $item->catatan,
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $formattedData
        ]);
    }

    public function store(Request $request)
    {
        try {
            if ($request->tipe === 'tool') {
                $validasi = $request->validate([
                    'nama_alat' => 'required|string|max:255',
                    'kategori' => 'required|string',
                    'kategori_lainnya' => 'nullable|string|max:50',
                    'qty' => 'required|integer|min:1',
                ]);

                $kategoriFinal = $validasi['kategori'];
                if ($validasi['kategori'] === 'Lainnya') {
                    if (empty($validasi['kategori_lainnya'])) {
                        return back()->with('error', 'Silakan isi nama kategori pada field kategori lainnya');
                    }
                    $kategoriFinal = trim($validasi['kategori_lainnya']);
                }

                $tool = ObTools::create([
                    'nama_alat' => $validasi['nama_alat'],
                    'kategori' => $kategoriFinal,
                    'qty' => $validasi['qty'],
                ]);

                return back()->with('success', 'Alat berhasil ditambahkan');
                
            } elseif ($request->tipe === 'pengecekan') {
                $validasi = $request->validate([
                    'id_alat' => 'required|exists:ob_tools,id',
                    'kondisi' => 'required',
                    'tanggal_pemeriksaan' => 'required|date',
                    'catatan' => 'nullable'
                ]);

                $pemeriksaan = KondisiTools::create($validasi);
                return back()->with('success', 'Pemeriksaan alat berhasil dibuat');
            }

        } catch (\Exception $e) {
            Log::info([
                'success' => false,
                'message' => 'gagal store kondisi tool : ' . $e->getMessage()
            ]);
            return back()->with('error', 'Terjadi kesalahan sistem');
        }
    }

    public function update(Request $request, $id)
    {
        try {

            if ($request->tipe === 'tool') {
                $request->validate([
                    'nama_alat' => 'required|string|max:255',
                    'kategori' => 'required|string',
                    'kategori_lainnya' => 'nullable|string|max:50',
                    'qty' => 'required|integer|min:1',
                ]);

                $tool = ObTools::findOrFail($id);

                $kategoriFinal = $request->kategori;
                if ($request->kategori === 'Lainnya') {
                    if (empty($request->kategori_lainnya)) {
                        return back()->with('error', 'Silakan isi nama kategori pada field kategori lainnya');
                    }
                    $kategoriFinal = trim($request->kategori_lainnya);
                }

                $tool->update([
                    'nama_alat' => $request->nama_alat ?? $tool->nama_alat,
                    'kategori' => $kategoriFinal,
                    'qty' => $request->qty ?? $tool->qty,
                ]);

                return back()->with('success', 'Alat berhasil diperbaharui');
                
            } elseif ($request->tipe === 'pengecekan') {
                $request->validate([
                    'kondisi' => 'required',
                    'tanggal_pemeriksaan' => 'required|date',
                    'catatan' => 'nullable'
                ]);

                $pemeriksaan = KondisiTools::findOrFail($id);

                $pemeriksaan->update([
                    'kondisi' => $request->kondisi ?? $pemeriksaan->kondisi,
                    'tanggal_pemeriksaan' => $request->tanggal_pemeriksaan ?? $pemeriksaan->tanggal_pemeriksaan,
                    'catatan' => $request->catatan ?? $pemeriksaan->catatan,
                ]);
                
                return back()->with('success', 'Pemeriksaan alat berhasil diperbaharui');
            }

        } catch (\Exception $e) {
            Log::info([
                'success' => false,
                'message' => 'gagal update kondisi tool : ' . $e->getMessage()
            ]);
            return back()->with('error', 'Terjadi kesalahan sistem');
        }
    }

    public function delete($id, Request $request)
    {
        try {
            if ($request->tipe === 'tool') {
                $tool = ObTools::findOrFail($id)->delete();

                return response()->json([
                    'success' => true, 
                    'message' => 'Alat berhasil dihapus'
                    ]);
            } elseif ($request->tipe === 'pengecekan') {
                $pemeriksaan = KondisiTools::findOrFail($id)->delete();
                
                return response()->json([
                    'success' => true, 
                    'message' => 'Pemeriksaan alat berhasil dihapus'
                    ]);
            }
        } catch (\Exception $e) {
            Log::info([
                'success' => false,
                'message' => 'gagal hapus kondisi tool : ' . $e->getMessage()
            ]);
            return response()->json([
                'success' => false, 
                'message' => 'Terjadi kesalahan sistem'
            ]);
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            $reportType = $request->input('report_type', 'alat');
            $filename = 'Laporan_' . ucfirst($reportType) . '_' . date('Y-m-d_His') . '.xlsx';

            $filters = [
                'tanggal_mulai' => $request->input('tanggal_mulai'),
                'tanggal_selesai' => $request->input('tanggal_selesai'),
                'kategori' => $request->input('kategori'),
                'kondisi' => $request->input('kondisi'),
                'id_alat' => $request->input('id_alat'),
            ];

            return Excel::download(
                new KondisiToolsEksport($reportType, $filters),
                $filename
            );
        } catch (\Exception $e) {
            Log::error('Error export excel: ' . $e->getMessage());
            return back()->with('error', 'Gagal export data: ' . $e->getMessage());
        }
    }
    
    public function importAlat(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'karyawan_id' => 'nullable|exists:karyawans,id',
        ]);

        try {
            $import = new KondisiToolsImport();
            Excel::import($import, $request->file('file'));

            return back()->with('success', 'Import data alat berhasil');
        } catch (\Exception $e) {
            Log::info([
                'success' => false,
                'message' => 'gagal import alat : ' . $e->getMessage()
            ]);
            return back()->with('error', 'Terjadi kesalahan sistem');
        }
    }

    public function exportPdf(Request $request)
    {
        try {
            $reportType = $request->input('report_type', 'alat');
            
            $filters = [
                'tanggal_mulai' => $request->input('tanggal_mulai'),
                'tanggal_selesai' => $request->input('tanggal_selesai'),
                'kategori' => $request->input('kategori'),
                'kondisi' => $request->input('kondisi'),
                'id_alat' => $request->input('id_alat'),
            ];

            if ($reportType === 'alat') {
                $data = $this->getFilteredAlatData($filters);
                $title = 'Laporan Data Alat';
                $totalQty = $data->sum('qty');
            } else {
                $data = $this->getFilteredPemeriksaanData($filters);
                $title = 'Laporan Riwayat Pemeriksaan';
                $totalQty = 0;
            }

            $hasFilter = !empty(array_filter($filters));

            if (!empty($filters['id_alat'])) {
                $alat = ObTools::find($filters['id_alat']);
                $filters['nama_alat'] = $alat ? $alat->nama_alat : null;
            }

            $pdf = Pdf::loadView('office.kondisiTools.pdf', [
                'data' => $data,
                'reportType' => $reportType,
                'filters' => $filters,
                'hasFilter' => $hasFilter,
                'title' => $title,
                'totalData' => $data->count(),
                'totalQty' => $totalQty,
            ]);

            $filename = 'Laporan_' . ucfirst($reportType) . '_' . date('Y-m-d_His') . '.pdf';
            
            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Error export PDF: ' . $e->getMessage());
            return back()->with('error', 'Gagal export PDF: ' . $e->getMessage());
        }
    }

    private function getFilteredAlatData($filters)
    {
        $query = ObTools::query();

        if (!empty($filters['kategori'])) {
            $query->where('kategori', $filters['kategori']);
        }

        return $query->orderBy('kategori')->orderBy('nama_alat')->get();
    }

    private function getFilteredPemeriksaanData($filters)
    {
        $query = KondisiTools::with('alat');

        if (!empty($filters['tanggal_mulai'])) {
            $query->whereDate('tanggal_pemeriksaan', '>=', $filters['tanggal_mulai']);
        }

        if (!empty($filters['tanggal_selesai'])) {
            $query->whereDate('tanggal_pemeriksaan', '<=', $filters['tanggal_selesai']);
        }

        if (!empty($filters['kondisi'])) {
            $query->where('kondisi', $filters['kondisi']);
        }

        if (!empty($filters['id_alat'])) {
            $query->where('id_alat', $filters['id_alat']);
        }

        return $query->orderBy('tanggal_pemeriksaan', 'desc')->get();
    }
}