<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\DataPassword;
use App\Models\Legalitas;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class LegalitasController extends Controller
{
    private const SORTABLE = [
        'nama_dokumen'     => 'nama_dokumen',
        'nomor_dokumen'    => 'nomor_dokumen',
        'tanggal_terbit'   => 'tanggal_terbit',
        'masa_berlaku'     => 'masa_berlaku',
        'tanggal_berakhir' => 'tanggal_berakhir',
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('HR.legalitas.index');
    }

    public function getData(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        if ($blocked = $this->guard()) {
            return $blocked;
        }

        $query = Legalitas::query();
        $recordsTotal = Legalitas::count();

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('nama_dokumen', 'like', "%{$search}%")
                  ->orWhere('nomor_dokumen', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        if ($request->filled('filter_status')) {
            $query->where('status', $request->filter_status);
        }

        if ($request->filled('filter_satuan')) {
            $query->where('satuan_masa_berlaku', $request->filter_satuan);
        }

        if ($request->filled('filter_tgl_mulai')) {
            $query->whereDate('tanggal_terbit', '>=', $request->filter_tgl_mulai);
        }

        if ($request->filled('filter_tgl_selesai')) {
            $query->whereDate('tanggal_terbit', '<=', $request->filter_tgl_selesai);
        }

        $recordsFiltered = (clone $query)->count();

        $orderIdx  = $request->input('order.0.column');
        $orderName = $request->input("columns.{$orderIdx}.name");

        if ($orderIdx !== null && isset(self::SORTABLE[$orderName])) {
            $dir = $request->input('order.0.dir') === 'asc' ? 'asc' : 'desc';
            $query->orderBy(self::SORTABLE[$orderName], $dir)->orderByDesc('id');
        } else {
            $query->orderByDesc('id');
        }

        $start  = max((int) $request->input('start', 0), 0);
        $length = (int) $request->input('length', 10);
        $length = ($length < 1 || $length > 100) ? 10 : $length;

        $rows = $query->skip($start)->take($length)->get();

        $data = $rows->values()->map(function ($row, $i) use ($start) {
            $berakhir = $row->tanggal_berakhir ? Carbon::parse($row->tanggal_berakhir) : null;
            $meta     = $this->fileMeta($row);

            return [
                'DT_RowIndex'          => $start + $i + 1,
                'id'                   => $row->id,
                'nama_dokumen'         => $row->nama_dokumen,
                'nomor_dokumen'        => $row->nomor_dokumen,
                'tanggal_terbit'       => Carbon::parse($row->tanggal_terbit)->format('d M Y'),
                'masa_berlaku_label'   => $row->masa_berlaku . ' ' . ucfirst($row->satuan_masa_berlaku),
                'tanggal_berakhir'     => $berakhir ? $berakhir->format('d M Y') : '-',
                'status'               => $row->status,
                'masa_berlaku'         => $row->masa_berlaku,
                'satuan_masa_berlaku'  => $row->satuan_masa_berlaku,
                'keterangan'           => $row->keterangan,
                'tanggal_terbit_raw'   => Carbon::parse($row->tanggal_terbit)->format('Y-m-d'),
                'tanggal_berakhir_raw' => $berakhir ? $berakhir->format('Y-m-d') : null,

                'sisa_hari'            => $berakhir
                    ? (int) now()->startOfDay()->diffInDays($berakhir->copy()->startOfDay(), false)
                    : null,
                'dibuat'               => $row->created_at ? $row->created_at->format('d M Y H:i') : '-',
                'diperbarui'           => $row->updated_at ? $row->updated_at->format('d M Y H:i') : '-',
                'file_name'            => $meta['file_name'],
                'file_ext'             => $meta['file_ext'],
                'file_size'            => $meta['file_size'],
                'file_url'             => $meta['file_ada'] ? route('HR.legalitas.file', $row->id) : null,
                'file_download_url'    => $meta['file_ada'] ? route('HR.legalitas.file', ['id' => $row->id, 'download' => 1]) : null,
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
        if ($blocked = $this->guard()) {
            return $blocked;
        }

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
        if ($blocked = $this->guard()) {
            return $blocked;
        }

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
        if ($blocked = $this->guard()) {
            return $blocked;
        }

        Legalitas::findOrFail($request->id)->delete();

        return response()->json(['message' => 'Legalitas berhasil dihapus'], 200);
    }

    private function guard(): ?JsonResponse
    {
        $hr = app(\App\Http\Controllers\HR\HRController::class);

        if (!$hr->unlocked()) {
            return response()->json(['message' => 'Terkunci.'], 423);
        }

        return null;
    }

    public function file(Request $request, int $id)
    {
        if ($blocked = $this->guard()) {
            return $blocked;
        }

        $row  = Legalitas::findOrFail($id);
        $disk = Storage::disk('public');

        abort_unless($row->path_dokumen && $disk->exists($row->path_dokumen), 404);

        $path = $disk->path($row->path_dokumen);
        $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $name = Str::slug($row->nama_dokumen) . '.' . $ext;

        if ($request->boolean('download')) {
            return response()->download($path, $name);
        }

        return response()->file($path, [
            'Content-Disposition' => 'inline; filename="' . $name . '"',
        ]);
    }

    private function fileMeta(Legalitas $row): array
    {
        $disk = Storage::disk('public');

        if (!$row->path_dokumen || !$disk->exists($row->path_dokumen)) {
            return ['file_ada' => false, 'file_name' => null, 'file_ext' => null, 'file_size' => null];
        }

        $bytes = $disk->size($row->path_dokumen);
        $size  = $bytes >= 1048576
            ? round($bytes / 1048576, 2) . ' MB'
            : round($bytes / 1024, 1) . ' KB';

        return [
            'file_ada'  => true,
            'file_name' => basename($row->path_dokumen),
            'file_ext'  => strtolower(pathinfo($row->path_dokumen, PATHINFO_EXTENSION)),
            'file_size' => $size,
        ];
    }
}