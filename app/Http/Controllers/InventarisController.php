<?php

namespace App\Http\Controllers;

use App\Imports\InventarisImport;
use App\Models\checkbarang;
use App\Models\inventaris;
use App\Models\service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class InventarisController extends Controller
{
    public function index()
    {
        $usernames = DB::table('users')->pluck('username')->toArray();
        return view('inventaris.index', compact('usernames'));
    }

    public function inputinventaris(Request $request)
    {
        // Validasi role berdasarkan type
        $user = auth()->user();
        if ($user->jabatan === 'Technical Support' && $request->type !== 'E') {
            return response()->json(['message' => 'Hanya boleh menambahkan barang elektronik.'], 403);
        }
        if ($user->jabatan === 'Finance & Accounting' && $request->type !== 'NE') {
            return response()->json(['message' => 'Hanya boleh menambahkan barang non-elektronik.'], 403);
        }

        // Validasi input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'kodebarang' => 'required|string',
            'merk_kode_seri_hardware' => 'string',
            'qty' => 'integer',
            'satuan' => 'required|string|max:255',
            'type' => 'required|in:E,NE',
            'harga_beli' => 'required|numeric|min:0',
            'waktu_pembelian' => 'required|date',
            'pengguna' => 'nullable|string|max:255',
            'ruangan' => 'nullable|string|max:255',
            'kondisi' => 'required|in:baik,rusak/bermasalah,sedang diperbaiki',
            'deskripsi' => 'nullable|string',
        ]);

        $validated['total_harga'] = $validated['qty'] * $validated['harga_beli'];

        // Simpan data
        $inventaris = Inventaris::create($validated);

        return response()->json([
            'message' => 'Inventaris berhasil disimpan',
            'data' => $inventaris,
        ], 201);
    }

    public function editview($id)
    {
        $data = inventaris::where('id', $id)->first();
        $usernames = DB::table('users')->pluck('username')->toArray();
        return view('inventaris.edit', compact('data', 'usernames'));
    }

    public function addcheck($id, Request $request)
    {
        $validated = $request->validate([
            'tanggal_pemeriksaan' => 'required|date',
            'interval' => 'required|string|max:255',
            'kondisi' => 'required|string|max:255',
            'catatan' => 'string|max:255|nullable',
        ]);

        $inventaris = Inventaris::findOrFail($id);
        $validated['idbarang'] = $inventaris->idbarang;
        $validated['inspector'] = Auth::user()->username;

        Inventaris::where('id', $id)->update([
            'kondisi' => $validated['kondisi']
        ]);

        Checkbarang::create($validated);
        return back()->with('success', 'Berhasil menambahkan data pemeriksaan.');
    }

    public function addservice($id, Request $request)
    {
        $validated = $request->validate([
            'tanggal_service' => 'required|date',
            'deskripsi' => 'string|max:255|required',
            'harga' => 'numeric',
        ]);

        $inventaris = Inventaris::findOrFail($id);
        $validated['idbarang'] = $inventaris->idbarang;
        $validated['user'] = Auth::user()->username;

        Service::create($validated);
        return back()->with('success', 'Berhasil menambahkan data service.');
    }

    public function deletedata($id)
    {
        $inventaris = Inventaris::findOrFail($id); // Query by id
        $idbarang = $inventaris->idbarang;

        // Delete related records first
        Service::where('idbarang', $idbarang)->delete();
        Checkbarang::where('idbarang', $idbarang)->delete();

        $inventaris->delete();
        return back()->with('success', 'Berhasil menghapus semua data terkait');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        Excel::import(new InventarisImport, $request->file('file'));

        return back()->with('success', 'Data inventaris berhasil diimpor.');
    }

    public function user($id, Request $request)
    {
        $inventaris = inventaris::where('id', $id)->update([
            'pengguna' => $request->pengguna,
            'ruangan' => $request->ruangan
        ]);

        return back()->with('success', 'Berhasil merubah pengguna dan ruangan');
    }
}
