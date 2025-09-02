<?php

namespace App\Http\Controllers;

use App\Models\karyawan;
use App\Models\laporanInsiden;
use Illuminate\Http\Request;
use Carbon\Carbon;

class laporanInsidentController extends Controller
{
    public function index()
    {
        return view('laporanInsiden.index');
    }

    public function get()
    {
        $laporanInsiden = laporanInsiden::with('Pelapor')->get();

        $data = $laporanInsiden->map(function ($item) {
            return [
                'id_pelapor'        => $item->pelapor,
                'pelapor'           => $item->Pelapor->nama_lengkap,
                'kategori'          => $item->kategori,
                'kejadian'          => $item->kejadian,
                'deskripsi'         => $item->deskripsi,
                'tanggal'           => $item->tanggal_kejadian,
                'waktu'             => $item->waktu_kejadian,
                'status'            => $item->status,
                'waktu_pengajuan' => $item->created_at
                    ? $item->created_at->translatedFormat('D, d M Y H:i')
                    : '-',
            ];
        });

        return response()->json([
            'data' => $data
        ]);
    }


    public function create()
    {
        $user = auth()->user()->karyawan_id;
        $karyawan = karyawan::findOrFail($user);
        return view('laporanInsiden.form', compact('karyawan'));
    }

    public function store(Request $request)
    {
        $validate = $request->validate([
            'kategori'          => 'required|string',
            'tanggal_kejadian'  => 'required|date',
            'waktu_kejadian'    => 'required',
            'id_pelapor'        => 'required|integer',
            'kejadian'          => 'required',
            'deskripsi'         => 'required|string',
            'lampiran'          => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        ]);

        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {
            $lampiranPath = $request->file('lampiran')->store('lampiran', 'public');
        }

        $laporanInsiden = new laporanInsiden();
        $laporanInsiden->pelapor          = $request->id_pelapor;
        $laporanInsiden->kategori         = $request->kategori;
        $laporanInsiden->kejadian         = $request->kejadian;
        $laporanInsiden->deskripsi        = $request->deskripsi;
        $laporanInsiden->tanggal_kejadian = $request->tanggal_kejadian;
        $laporanInsiden->waktu_kejadian   = $request->waktu_kejadian;
        $laporanInsiden->lampiran         = $lampiranPath;
        $laporanInsiden->status           = 'Baru';
        $laporanInsiden->catatan          = $request->catatan;
        $laporanInsiden->save();

        return redirect()->route('index.laporanInsiden')->with('success', 'Laporan berhasil disimpan');
    }
}
