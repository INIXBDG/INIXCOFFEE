<?php

namespace App\Http\Controllers\office;

use App\Http\Controllers\Controller;
use App\Models\tagihanPerusahaan;
use App\Models\trackingTagihanPerusahaan;
use Illuminate\Http\Request;

class TagihanPerusahaanController extends Controller
{
    public function index()
    {
        $trackingTagihanPerusahaans = trackingTagihanPerusahaan::orderBy('tanggal_perkiraan_mulai', 'desc')->get();

        return view('office.tagihanPerusahaan.index', compact('trackingTagihanPerusahaans'));
    }
    
    public function storeTagihanPerusahaan(Request $request) 
    {
        $request->validate([
            'kegiatan' => 'required|string',
            'nominal' => 'nullable|numeric|min:0',
            'tipe' => 'required|in:bulanan,tahunan',
            'tanggal_perkiraan_mulai' => 'required|date',
            'tanggal_perkiraan_selesai' => 'nullable|date|after_or_equal:tanggal_perkiraan_mulai',
            'keterangan' => 'nullable|string',
        ]);
        
        $tagihan = tagihanPerusahaan::create([
            'kegiatan' => $request->kegiatan,
            'tipe' => $request->tipe,
            'nominal' => $request->nominal ?? null,
            'tanggal_perkiraan_mulai' => $request->tanggal_perkiraan_mulai,
            'tanggal_perkiraan_selesai' => $request->tanggal_perkiraan_selesai ?? $request->tanggal_perkiraan_mulai,
            'last_generate' => now()
        ]);

        trackingTagihanPerusahaan::create([
            'id_tagihan_perusahaan' => $tagihan->id,
            'nominal' => $request->nominal ?? null,
            'keterangan' => $request->keterangan ?? null,
            'tracking' => 'Diajukan dan Sedang Ditinjau oleh Finance',
            'tanggal_perkiraan_mulai' => $request->tanggal_perkiraan_mulai,
            'tanggal_perkiraan_selesai' => $request->tanggal_perkiraan_selesai ?? $request->tanggal_perkiraan_mulai,
        ]);

        return back()->with('success_tagihan', 'Tagihan perusahaan berhasil dibuat.');
    }

    public function detailTagihanPerusahaan($id)
    {
        $tagihan = trackingTagihanPerusahaan::with('tagihanPerusahaan')->findOrFail($id);

        return view('office.tagihanPerusahaan.detail', compact('tagihan'));
    }

    public function hapusTagihanPerusahaan($id) 
    {
        trackingTagihanPerusahaan::findOrFail($id)->delete();

        return back()->with('success_tagihan', 'Tagihan perusahaan berhasil dihapus.');
    }

    public function updateTagihanPerusahaan(Request $request, $id)
    {
        $tracking = trackingTagihanPerusahaan::findOrFail($id);
        $tagihan = tagihanPerusahaan::where('id', $tracking->id_tagihan_perusahaan)->first();

        $tagihan->update([
            'kegiatan' => $request->kegiatan ?? $tagihan->kegiatan,
            'tipe' => $request->tipe ?? $tagihan->tipe,
            'nominal' => $request->nominal ?? $tagihan->nominal,
            'tanggal_perkiraan_mulai' => $request->tanggal_perkiraan_mulai ?? $tagihan->tanggal_perkiraan_mulai,
            'tanggal_perkiraan_selesai' => $request->tanggal_perkiraan_selesai ?? $tagihan->tanggal_perkiraan_selesai,
        ]);

        $tracking->update([
            'nominal' => $request->nominal ?? $tracking->nominal,
            'status' => $request->status ?? $tracking->status,
            'keterangan' => $request->keterangan ?? $tracking->keterangan,
            'tracking' => $request->tracking ?? $tracking->tracking,
            'tanggal_selesai' => $request->tanggal_selesai ?? $tracking->tanggal_selesai,
            'tanggal_perkiraan_mulai' => $tagihan->tanggal_perkiraan_mulai,
            'tanggal_perkiraan_selesai' => $tagihan->tanggal_perkiraan_selesai ?? $tagihan->tanggal_perkiraan_selesai,
        ]);

        return back()->with('success_tagihan', 'Tagihan berhasil diperbaharui.');
    }

    public function dataTagihan($id) 
    {
        $tagihan = trackingTagihanPerusahaan::with('tagihanPerusahaan')->findOrFail($id);

        $data = [
            'id_tagihan' => $tagihan->id,
            'data' => [
                'kegiatan' => $tagihan->tagihanPerusahaan->kegiatan,
                'tipe' => $tagihan->tagihanPerusahaan->tipe,
                'nominal' => $tagihan->nominal,
                'status' => $tagihan->status,
                'keterangan' => $tagihan->keterangan,
                'tracking' => $tagihan->tracking,
                'tanggal_selesai' => $tagihan->tanggal_selesai,
                'tanggal_perkiraan_mulai' => $tagihan->tanggal_perkiraan_mulai,
                'tanggal_perkiraan_selesai' => $tagihan->tanggal_perkiraan_selesai,
            ]
        ];

        return response()->json($data);
    }
}
