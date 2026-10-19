<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Legalitas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LegalitasController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Kolom yang boleh dipakai untuk sorting (nama kolom DataTables => kolom DB).
     */
    private const SORTABLE = [
        'nama_dokumen'     => 'nama_dokumen',
        'nomor_dokumen'    => 'nomor_dokumen',
        'tanggal_terbit'   => 'tanggal_terbit',
        'masa_berlaku'     => 'masa_berlaku',
        'tanggal_berakhir' => 'tanggal_berakhir',
    ];

    public function index()
    {
        return view('HR.legalitas.index');
    }

    /**
     * Endpoint server-side DataTables (tanpa Yajra).
     */
    public function getData(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $query = Legalitas::query();
        $recordsTotal = Legalitas::count();

        // Pencarian global
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('nama_dokumen', 'like', "%{$search}%")
                  ->orWhere('nomor_dokumen', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        // Filter status & satuan
        if ($request->filled('filter_status')) {
            $query->where('status', $request->filter_status);
        }

        if ($request->filled('filter_satuan')) {
            $query->where('satuan_masa_berlaku', $request->filter_satuan);
        }

        // Filter rentang tanggal terbit (boleh salah satu saja)
        if ($request->filled('filter_tgl_mulai')) {
            $query->whereDate('tanggal_terbit', '>=', $request->filter_tgl_mulai);
        }

        if ($request->filled('filter_tgl_selesai')) {
            $query->whereDate('tanggal_terbit', '<=', $request->filter_tgl_selesai);
        }

        $recordsFiltered = (clone $query)->count();

        // Sorting (whitelist), default: terbaru di atas
        $orderIdx  = $request->input('order.0.column');
        $orderName = $request->input("columns.{$orderIdx}.name");

        if ($orderIdx !== null && isset(self::SORTABLE[$orderName])) {
            $dir = $request->input('order.0.dir') === 'asc' ? 'asc' : 'desc';
            $query->orderBy(self::SORTABLE[$orderName], $dir)
                  ->orderByDesc('id'); // tie-breaker agar urutan antar halaman stabil
        } else {
            $query->orderByDesc('id');
        }

        // Paging
        $start  = max((int) $request->input('start', 0), 0);
        $length = (int) $request->input('length', 10);
        $length = ($length < 1 || $length > 100) ? 10 : $length;

        $rows = $query->skip($start)->take($length)->get();

        $data = $rows->values()->map(function ($row, $i) use ($start) {
            return [
                'DT_RowIndex'        => $start + $i + 1,
                'id'                 => $row->id,
                'nama_dokumen'       => $row->nama_dokumen,
                'nomor_dokumen'      => $row->nomor_dokumen,
                'tanggal_terbit'     => Carbon::parse($row->tanggal_terbit)->format('d M Y'),
                'masa_berlaku_label' => $row->masa_berlaku . ' ' . ucfirst($row->satuan_masa_berlaku),
                'tanggal_berakhir'   => $row->tanggal_berakhir
                    ? Carbon::parse($row->tanggal_berakhir)->format('d M Y')
                    : '-',
                'status'             => $row->status,
                // data mentah untuk form edit
                'masa_berlaku'          => $row->masa_berlaku,
                'satuan_masa_berlaku'   => $row->satuan_masa_berlaku,
                'keterangan'            => $row->keterangan,
                'tanggal_terbit_raw'    => Carbon::parse($row->tanggal_terbit)->format('Y-m-d'),
                'tanggal_berakhir_raw'  => $row->tanggal_berakhir
                    ? Carbon::parse($row->tanggal_berakhir)->format('Y-m-d')
                    : null,
                'file_url'           => $row->path_dokumen
                    ? asset('storage/' . ltrim($row->path_dokumen, '/'))
                    : null,
            ];
        });

        return response()->json([
            'draw'            => (int) $request->input('draw'),
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_dokumen'        => 'required|string|max:255',
            'nomor_dokumen'       => 'required|string',
            'tanggal_terbit'      => 'required|date',
            'masa_berlaku'        => 'required|integer|min:1',
            'satuan_masa_berlaku' => 'required|in:hari,bulan,tahun',
            'tanggal_berakhir'    => 'nullable|date',
            'status'              => 'required|in:aktif,kadaluarsa',
            'keterangan'          => 'nullable|string',
            'path_dokumen'        => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:2048',
        ]);

        $validated['path_dokumen'] = $request->file('path_dokumen')->store('legalitas', 'public');

        Legalitas::create($validated);

        return response()->json(['message' => 'Legalitas berhasil ditambahkan'], 201);
    }

    public function update(Request $request)
    {
        $legalitas = Legalitas::findOrFail($request->id);

        $validated = $request->validate([
            'nama_dokumen'        => 'required|string|max:255',
            'nomor_dokumen'       => 'required|string',
            'tanggal_terbit'      => 'required|date',
            'masa_berlaku'        => 'required|integer|min:1',
            'satuan_masa_berlaku' => 'required|in:hari,bulan,tahun',
            'tanggal_berakhir'    => 'nullable|date',
            'status'              => 'required|in:aktif,kadaluarsa',
            'keterangan'          => 'nullable|string',
            'path_dokumen'        => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:2048',
        ]);

        if ($request->hasFile('path_dokumen')) {
            // Hapus file lama jika ada
            if ($legalitas->path_dokumen) {
                Storage::disk('public')->delete($legalitas->path_dokumen);
            }
            $validated['path_dokumen'] = $request->file('path_dokumen')->store('legalitas', 'public');
        }

        $legalitas->update($validated);

        return response()->json(['message' => 'Legalitas berhasil diperbarui'], 200);
    }

    public function delete(Request $request)
    {
        $legalitas = Legalitas::findOrFail($request->id);
        $legalitas->delete();

        return response()->json(['message' => 'Legalitas berhasil dihapus'], 200);
    }
}