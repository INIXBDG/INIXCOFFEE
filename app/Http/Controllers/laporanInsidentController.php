<?php

namespace App\Http\Controllers;

use App\Models\karyawan;
use App\Models\laporanInsiden;
use App\Models\trackingLaporanInsiden;
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
                'id'                => $item->id,
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
        $laporanInsiden->pelapor                = $request->id_pelapor;
        $laporanInsiden->kategori               = $request->kategori;
        $laporanInsiden->kejadian               = $request->kejadian;
        $laporanInsiden->deskripsi              = $request->deskripsi;
        $laporanInsiden->tanggal_kejadian       = $request->tanggal_kejadian;
        $laporanInsiden->waktu_kejadian         = $request->waktu_kejadian;
        $laporanInsiden->lampiran               = $lampiranPath;
        $laporanInsiden->status                 = 'Baru';
        $laporanInsiden->catatan                = $request->catatan;
        $laporanInsiden->save();

        $tracking = new trackingLaporanInsiden();
        $tracking->id_laporanInsiden            = $laporanInsiden->id;
        $tracking->tanggal_response             = now()->format('Y-m-d');
        $tracking->waktu_response               = now()->format('H:i:s');
        $tracking->status                       = 'Baru';
        $tracking->save();

        return redirect()->route('index.laporanInsiden')->with('success', 'Laporan berhasil disimpan');
    }

    public function respon(Request $request)
    {
        $request->validate([
            'id'     => 'required',
            'status' => 'required'
        ]);

        $user = auth()->user()->karyawan_id;

        $laporanUpdate = laporanInsiden::where('id', $request->id)->first();
        $laporanUpdate->status              = $request->status;
        $laporanUpdate->save();

        $tracking = new trackingLaporanInsiden();
        $tracking->id_laporanInsiden        = $request->id;
        $tracking->responder                = $user;
        $tracking->tanggal_response         = now()->format('Y-m-d');
        $tracking->waktu_response           = now()->format('H:i:s');
        $tracking->status                   = $request->status;
        if ($request->status === 'Tidak Ditangani' || $request->status === 'Selesai') {
            $tracking->keterangan           = $request->solusi;
        } else {
            $tracking->solusi               = $request->solusi;
        }
        $tracking->save();

        return redirect()->route('index.laporanInsiden')->with('success', 'Laporan berhasil disimpan');
    }

    public function detail($id)
    {
        $dataLaporan = laporanInsiden::with('Pelapor')->findOrFail($id);
        $dataTrackingLaporan = trackingLaporanInsiden::with('karyawan')
            ->where('id_laporanInsiden', $dataLaporan->id)
            ->get();

        return view('laporanInsiden.detail', compact('dataLaporan', 'dataTrackingLaporan'));
    }

    public function edit($id)
    {
        $dataLaporan = laporanInsiden::with('Pelapor')->findOrFail($id);

        return view('laporanInsiden.edit', compact('dataLaporan'));
    }

    public function update(Request $request)
    {
        if ($request->lampiran !== null) {
            $lampiranPath = null;
            if ($request->hasFile('lampiran')) {
                $lampiranPath = $request->file('lampiran')->store('lampiran', 'public');
            }
        }

        $laporanInsiden = laporanInsiden::where('id', $request->id_laporan)->first();
        $laporanInsiden->kategori               = $request->kategori;
        $laporanInsiden->kejadian               = $request->kejadian;
        $laporanInsiden->deskripsi              = $request->deskripsi;
        $laporanInsiden->tanggal_kejadian       = $request->tanggal_kejadian;
        $laporanInsiden->waktu_kejadian         = $request->waktu_kejadian;
        if ($request->lampiran !== null) {
            $laporanInsiden->lampiran           = $lampiranPath;
        }
        $laporanInsiden->catatan                = $request->catatan;
        $laporanInsiden->save();

        return redirect()->route('index.laporanInsiden')->with('success', 'Laporan berhasil disimpan');
    }

    public function hapus($id)
    {
        $dataLaporan = laporanInsiden::with('Pelapor')->findOrFail($id);
        $dataTrackingLaporan = trackingLaporanInsiden::where('id_laporanInsiden', $dataLaporan->id)->get();

        foreach ($dataTrackingLaporan as $tracking) {
            $tracking->delete();
        }
        $dataLaporan->delete();
        return redirect()->back()->with(['success', 'berhasil menghapus laporan!']);
    }
}
