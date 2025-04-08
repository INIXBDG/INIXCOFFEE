<?php

namespace App\Http\Controllers;

use App\Models\karyawan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\jabatan;
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
        $users = karyawan::findOrFail($id);
        $jabatan = jabatan::all();
        return view('user.edit', compact('users', 'jabatan'));
    }

    public function updateData(Request $request, $id)
    {
        // dd($request->all());
        $karyawan = Karyawan::findOrFail($id);
        $user = User::findOrFail($id);

        $data = $request->validate([
            'nama_lengkap' => ['required'],
            'nip' => ['nullable', 'numeric'],
            'jabatan' => ['nullable'],
            'divisi' => ['nullable'],
            'status_aktif' => ['required'],
        ]);

        $karyawan->jabatan = $data['jabatan'];
        $karyawan->update($request->all());
        $id_instruktur = null;
        $id_sales = null;

        if ($request->jabatan == 'Instruktur' || $request->jabatan == 'Technical Support') {
            $id_instruktur = $request->kode_karyawan;
        }

        if ($request->jabatan == 'SPV Sales' || $request->jabatan == 'Sales' || $request->jabatan == 'Adm Sales') {
            $id_sales = $request->kode_karyawan;
        }
        $user->jabatan = $data['jabatan'];
        $user->status_akun = $data['status_aktif'];
        $user->id_instruktur = $id_instruktur;
        $user->id_sales = $id_sales;
        $user->save();

        if (Auth::user()->role == "Admin") {
            return redirect('/user')->with('success', 'Data Berhasil Diubah');
        } elseif (Auth::user()->role == "Admin" && Auth::user()->id == $user->id) {
            return redirect('/profile/' . $id)->with('success', 'Data Berhasil Diubah');
        } else {
            return redirect('/profile/' . $id)->with('success', 'Data Berhasil Diubah');
        }
    }


    public function updateFoto(Request $request, $id): RedirectResponse
    {
        //validate form
        // dd($request->all());

        $this->validate($request, [
            'foto'     => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'ttd'     => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);
        $post = karyawan::findOrFail($id);

        if ($request->hasFile('foto')) {

            //upload new image
            $image = $request->file('foto');
            $image->storeAs('public/posts', $image->hashName());

            //delete old image
            Storage::delete('public/posts/'.$post->image);

            //update post with new image
            $post->update([
                'foto'     => $image->hashName(),
            ]);

        } elseif ($request->hasFile('ttd')) {
            $image = $request->file('ttd');
            $image->storeAs('public/ttd', $image->hashName());

            //delete old image
            Storage::delete('public/ttd/'.$post->image);

            //update post with new image
            $post->update([
                'ttd'     => $image->hashName(),
            ]);
        }else{
            return redirect()->route('user.show', $id)->with(['error' => 'Foto Tidak Disimpan!']);
        }

        //redirect to index
        return redirect()->route('user.show', $id)->with(['success' => 'Foto Berhasil Disimpan!']);
    }
}
