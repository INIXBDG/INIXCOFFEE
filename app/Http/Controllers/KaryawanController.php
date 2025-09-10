<?php

namespace App\Http\Controllers;

use App\Models\karyawan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\jabatan;
use Vinkla\Hashids\Facades\Hashids;
use Carbon\Carbon;

class KaryawanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function gantiFoto($id)
    {
        $users = karyawan::findOrFail($id);
        return view('karyawan.gantifoto', compact('users'));
    }

    public function edit($id)
    {
        $decoded = Hashids::decode($id);
        if (empty($decoded)) abort(404);

        $realId = $decoded[0];
        $users = Karyawan::findOrFail($realId);
        $user = User::where('karyawan_id', $users->id)->firstOrFail();

        // Batasi akses ke user sendiri atau admin
        if (auth()->id() !== $user->id && auth()->user()->role !== 'Admin') {
            abort(403);
        }

        $jabatan = Jabatan::all();
        return view('user.edit', compact('users', 'jabatan'));
    }


public function updateData(Request $request, $id)
{
    $decoded = Hashids::decode($id);
    if (empty($decoded)) abort(404);

    $realId = $decoded[0];

    $karyawan = Karyawan::findOrFail($realId);
    $user = User::where('karyawan_id', $karyawan->id)->firstOrFail();

    // Batasi akses ke user sendiri atau admin
    if (auth()->id() !== $user->id && auth()->user()->role !== 'Admin') {
        abort(403);
    }

    $data = $request->validate([
        'nama_lengkap' => ['required'],
        'nip' => ['nullable', 'numeric'],
        'jabatan' => ['nullable'],
        'divisi' => ['nullable'],
        'status_aktif' => ['required'],
        'email' => ['nullable', 'email'],
        'telepon' => ['nullable', 'string', 'max:20'],
        'whatsapp' => ['nullable', 'string', 'max:20'],
    ]);

    // Update data karyawan
    $karyawan->nama_lengkap = $data['nama_lengkap'];
    $karyawan->nip = $data['nip'] ?? $karyawan->nip;
    $karyawan->jabatan = $data['jabatan'];
    $karyawan->divisi = $data['divisi'] ?? $karyawan->divisi;
    $karyawan->status_aktif = $data['status_aktif'];

    // Tambahan update jika ada
    if (!empty($data['email'])) {
        $karyawan->email = $data['email'];
    }
    if (!empty($data['telepon'])) {
        $karyawan->telepon = $data['telepon'];
    }
    if (!empty($data['whatsapp'])) {
        $karyawan->whatsapp = $data['whatsapp'];
    }

    $karyawan->save();

    // Tentukan id_instruktur / id_sales
    $id_instruktur = null;
    $id_sales = null;

    if (in_array($request->jabatan, ['Instruktur', 'Technical Support'])) {
        $id_instruktur = $request->kode_karyawan;
    }

    if (in_array($request->jabatan, ['SPV Sales', 'Sales', 'Adm Sales'])) {
        $id_sales = $request->kode_karyawan;
    }

    // Update data user
    $user->jabatan = $data['jabatan'];
    $user->status_akun = $data['status_aktif'];
    $user->id_instruktur = $id_instruktur;
    $user->id_sales = $id_sales;
    $user->save();

    if (auth()->user()->role == "Admin") {
        return redirect('/user')->with('success', 'Data Berhasil Diubah');
    }

    return redirect()->route('user.show', ['hashid' => $user->hashids])
        ->with('success', 'Data Berhasil Diubah');
}

    public function updateFoto(Request $request, $id): RedirectResponse
    {
        $this->validate($request, [
            'foto' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'ttd'  => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        $post = Karyawan::findOrFail($id);

        // Proses foto
        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $foto->storeAs('public/posts', $foto->hashName());

            // Hapus foto lama jika ada
            if ($post->foto) {
                Storage::delete('public/posts/' . $post->foto);
            }

            $post->foto = $foto->hashName();
        }

        // Proses ttd
        if ($request->hasFile('ttd')) {
            $ttd = $request->file('ttd');
            $ttd->storeAs('public/ttd', $ttd->hashName());

            // Hapus ttd lama jika ada
            if ($post->ttd) {
                Storage::delete('public/ttd/' . $post->ttd);
            }

            $post->ttd = $ttd->hashName();
        }

        $post->save();
        //Encode hashing untuk update foto
        return redirect()->route('user.show', Hashids::encode($post->id))->with([
            'success' => 'Foto dan/atau TTD berhasil diperbarui!'
        ]);
    }
}
