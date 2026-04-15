<?php

namespace App\Http\Controllers;

use App\Models\AdministrasiKaryawan;
use Illuminate\Http\Request;

class AdministrasiKaryawanController extends Controller
{

    public function index()
    {
        $administrasis = AdministrasiKaryawan::orderBy('dateline', 'desc')->get();

        return view( 'office.administrasiKaryawan.index', compact('administrasis'));
    }

    public function store(Request $request)
    {
        $validasi = $request->validate([
            'nama_administrasi' => 'required|string',
            'dateline' => 'required|date',
            'keterangan' => 'nullable|string'
        ]);

        AdministrasiKaryawan::create($validasi);

        return back()->with('success_administrasi', 'Administrasi Karyawan berhasil dibuat.');
    }

    public function update(Request $request, string $id)
    {
        $administrasi = AdministrasiKaryawan::findOrFail($id);
        
        $updateData = [
            'nama_administrasi' => $request->nama_administrasi ?? $administrasi->nama_administrasi,
            'dateline' => $request->dateline ?? $administrasi->dateline,
            'keterangan' => $request->keterangan ?? $administrasi->keterangan,
            'status' => $request->status ?? $administrasi->status,
            'tanggal_selesai' => $request->tanggal_selesai ?? $administrasi->tanggal_selesai
        ];

        if ($request->hasFile('bukti_transfer')) {
            $path = $request->file('bukti_transfer')->store('bukti_transfer', 'public');
            $updateData['bukti_transfer'] = $path;
        }

        $administrasi->update($updateData);

        // set status
        if ($request->tanggal_selesai !== null && $administrasi->dateline < $request->tanggal_selesai && !in_array($request->status, ['selesai', 'pending']) && !in_array($administrasi->status, ['selesai', 'pending'])) {
            $administrasi->status = 'terlambat';
            $administrasi->save();
        } else if ($request->tanggal_selesai !== null && $administrasi->dateline >= $request->tanggal_selesai && $administrasi->status !== 'terlambat') {
            $administrasi->status = 'selesai';
            $administrasi->save();
        } else if ($request->tanggal_selesai !== null && in_array($administrasi->status, ['selesai', 'pending'])) {
            $administrasi->status = 'selesai';
            $administrasi->save();
        }

        return back()->with('success_administrasi', 'Administrasi Karyawan berhasil diperbaharui.');
    }

    public function destroy(string $id)
    {
        $administrasi = AdministrasiKaryawan::findOrFail($id)->delete();

        return back()->with('success_administrasi', 'Administrasi Karyawan berhasil dihapus.');
    }

    public function getData($id)
    {
        $data = AdministrasiKaryawan::findOrFail($id);

        return response()->json($data);
    }

    public function edit($id)
    {
        $administrasi = AdministrasiKaryawan::findOrFail($id);

        return view('office.administrasiKaryawan.detail', compact('administrasi'));
    }
}
