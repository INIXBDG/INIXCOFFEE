<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RegistryFeature;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;

class RegistryFeatureController extends Controller
{

    /**
     * Menampilkan daftar semua tugas (Read).
     */
    public function index()
    {
        $daftar_tugas = RegistryFeature::with([
            'pengerja.karyawan',
        ])->latest()->get();

        return view('registry.index', compact('daftar_tugas'));
    }

    /**
     * Menampilkan formulir untuk membuat tugas baru (Create Form).
     */
    public function create()
    {
        // Ambil user untuk dropdown 'Pengerja'.
        $users = User::with('karyawan')
                     ->get()
                     ->sortBy('name');

        // Ambil data 'features' dari tabel permissions
        $permissions = Permission::all();
        $unwantedWords = ['Create', 'Delete', 'View', 'Edit'];

        $features = $permissions->pluck('name')->map(function ($name) use ($unwantedWords) {
            $cleanedName = str_replace($unwantedWords, '', $name);
            return trim($cleanedName);
        })->filter()->unique()->sort()->values();

        // Kirim 'users' dan 'features' ke view
        return view('registry.create', compact('users', 'features'));
    }

    /**
     * Menyimpan data tugas baru ke database (Create Action).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tugas'       => 'required|string|max:255', // <-- [DIUBAH] Validasi sebagai teks
            'fitur'       => 'required|string|max:255', // <-- [BARU] Validasi untuk dropdown fitur
            'tipe'        => 'required|string|max:100',
            'pengerja_id' => 'nullable|exists:users,id',
            'catatan'     => 'nullable|string',
            'pemilik'     => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $validatedData = $validator->validated();

        $validatedData['status'] = 'Belum dimulai';
        RegistryFeature::create($validatedData);

        return redirect()->route('registry.index')
                         ->with('success', 'Tugas baru berhasil ditambahkan.');
    }
    /**
     * Menampilkan detail satu tugas (Read Single).
     */
    public function show(RegistryFeature $tugas)
    {
        // $tugas sudah otomatis diambil oleh Laravel (Route Model Binding)
        return view('registry.show', compact('tugas'));
    }

    /**
     * Menampilkan formulir untuk mengedit tugas (Update Form).
     */
    public function edit(RegistryFeature $tugas)
    {
        $users = User::with('karyawan')
                     ->get();

        return view('registry.edit', compact('tugas', 'users'));
    }

    /**
     * Memperbarui data tugas di database (Update Action).
     */
    public function update(Request $request, RegistryFeature $tugas)
    {
        $validator = Validator::make($request->all(), [
            'tugas'         => 'required|string|max:255',
            'tipe'          => 'required|string|max:100',
            // 'pemilik'     => 'required|string|max:100', // Sebaiknya dihapus
            'pengerja_id'   => 'nullable|exists:users,id',
            'status'        => 'required|string|max:100',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_akhir' => 'nullable|date|after_or_equal:tanggal_mulai',
            'catatan'       => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Ambil semua data yang divalidasi
        $validatedData = $validator->validated();
        $tugas->update($validatedData);

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('registry.index')
                         ->with('success', 'Tugas berhasil diperbarui.');
    }

    public function startTask(RegistryFeature $tugas)
    {
        // Cek agar tidak bisa dimulai jika statusnya bukan 'Belum dimulai'
        if ($tugas->status != 'Belum dimulai') {
            return redirect()->back()
                             ->with('error', 'Tugas ini tidak dapat dimulai (mungkin sudah berjalan atau selesai).');
        }

        // Update status dan tanggal mulai
        $tugas->update([
            'status' => 'Dalam proses',
            'tanggal_mulai' => now()
        ]);

        return redirect()->route('registry.index')
                         ->with('success', 'Tugas "' . $tugas->tugas . '" telah dimulai.');
    }

    public function finishTask(RegistryFeature $tugas)
    {
        // [PERBAIKAN] Cek berdasarkan tanggal_akhir, bukan status
        if (!is_null($tugas->tanggal_akhir)) {
            return redirect()->back()
                             ->with('error', 'Tugas ini sudah memiliki tanggal selesai.');
        }

        // Cek jika tugas belum dimulai (tidak punya tanggal_mulai)
        if (is_null($tugas->tanggal_mulai)) {
            return redirect()->back()
                             ->with('error', 'Tugas ini belum dimulai, tidak bisa ditandai selesai.');
        }

        // Update status dan tanggal akhir
        $tugas->update([
            'status' => 'Selesai', // [PERBAIKAN] Baris ini diaktifkan kembali
            'tanggal_akhir' => now()
        ]);

        return redirect()->route('registry.index')
                         ->with('success', 'Tugas "' . $tugas->tugas . '" telah ditandai Selesai.');
    }

    /**
     * Menghapus data tugas dari database (Delete).
     */
    public function destroy(RegistryFeature $tugas)
    {
        // Hapus data
        $tugas->delete();

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('registry.index')
                         ->with('success', 'Tugas berhasil dihapus.');
    }
}

