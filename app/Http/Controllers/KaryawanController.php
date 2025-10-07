<?php

namespace App\Http\Controllers;

use App\Models\karyawan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\jabatan;
use App\Models\TunjanganKaryawan;
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
        if (auth()->id() !== $user->id && auth()->user()->role !== 'Admin' && auth()->user()->jabatan !== 'HRD') {
            abort(403);
        }

        $jabatan = Jabatan::all();
        return view('user.edit', compact('users', 'jabatan'));
    }


public function updateData(Request $request, $id)
{
    $decoded = Hashids::decode($id);
    if (empty($decoded[0])) abort(404);

    $realId = $decoded[0];

    $karyawan = Karyawan::findOrFail($realId);
    $user = User::where('karyawan_id', $karyawan->id)->firstOrFail();

    // Batasi akses ke user sendiri atau admin
    if (auth()->id() !== $user->id && auth()->user()->role !== 'Admin' && auth()->user()->jabatan !== 'HRD') {
        abort(403);
    }

    $data = $request->validate([
        'nama_lengkap' => ['required'],
        'nip' => ['nullable', 'numeric'],
        'kode_karyawan' => ['nullable'],
        'jabatan' => ['nullable'],
        'divisi' => ['nullable'],
        'status_aktif' => ['required'],
        'rekening_maybank' => ['nullable'],
        'rekening_bca' => ['nullable'],
        'telepon' => ['nullable'],           // ✅ Tambahkan validasi
        'whatsapp' => ['nullable'],       // ✅ Tambahkan validasi
        'email' => ['nullable', 'email'],   // ✅ Tambahkan validasi
        'awal_probation' => ['nullable', 'date'],
        'akhir_probation' => ['nullable', 'date'],
        'awal_kontrak' => ['nullable', 'date'],
        'akhir_kontrak' => ['nullable', 'date'],
        'awal_tetap' => ['nullable', 'date'],
        'akhir_tetap' => ['nullable', 'date'],
        'keterangan' => ['nullable'],
        'cuti' => ['nullable', 'numeric'],
    ]);

    // ✅ Update karyawan dengan semua data yang divalidasi
    $karyawan->update($data);

    $id_instruktur = null;
    $id_sales = null;

    if (in_array($request->jabatan, ['Instruktur', 'Technical Support'])) {
        $id_instruktur = $request->kode_karyawan;
    }


    if (in_array($request->jabatan, ['SPV Sales', 'Sales', 'Adm Sales'])) {
        $id_sales = $request->kode_karyawan;
    }

    $user->jabatan = $data['jabatan'];
    $user->status_akun = $data['status_aktif'];
    $user->id_instruktur = $id_instruktur;
    $user->id_sales = $id_sales;
    $user->save();

    if (auth()->user()->jabatan == "HRD") {
        return redirect('/user')->with('success', 'Data Berhasil Diubah');
    }

    return back()->with('success', 'Data Berhasil Diubah');
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

    public function gajiIndex()
    {
        if (Auth::user()->jabatan !== "HRD") {
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        $karyawan = user::with('karyawan')->where('status_akun', "1")->get();
        return view('gaji.index', compact('karyawan'));
    }

    public function updateGaji(Request $request, $id)
    {
        $request->validate([
            'jumlah_gaji' => 'required|numeric|min:0',
        ]);

        $karyawan = Karyawan::findOrFail($id);
        $karyawan->update(['gaji' => $request->jumlah_gaji]);

        return redirect()->route('gaji.index')->with('success', 'Gaji berhasil diperbarui.');
    }

    public function destroyGaji($id)
    {
        $karyawan = Karyawan::findOrFail($id);
        $karyawan->update(['gaji' => null]); // Nullify salary instead of deleting record

        return redirect()->route('gaji.index')->with('success', 'Gaji berhasil dihapus.');
    }

    public function slip()
    {
        $HRD = User::with('karyawan')->find('55');
        $user = User::with('karyawan')->find(Auth::id());
        $tunjangan = TunjanganKaryawan::where('id_karyawan', Auth::id())
            ->with('karyawan', 'jenistunjangan')
            ->get();
        return view('tunjangan.slip', compact('user', 'tunjangan', 'HRD'));
    }
}
