<?php

namespace App\Http\Controllers\Crm;
use App\Http\Controllers\Controller;
use App\Models\Peluang;
use Illuminate\Http\Request;

class CRMController extends Controller
{
    public function index(){
        // $profit = Peluang::whereNotNull('close_win')->sum();
        return view('crm.dashboard');
    }

    public function getProfile()
    {
        $user = auth()->user();
        // Pastikan relasi karyawan sudah didefinisikan di model User
        $profile = $user->load('karyawan');

        // Bisa return data sebagai JSON jika untuk API, atau return view jika untuk halaman
        return response()->json([
            'id' => $user->id,
            'username' => $user->name, // contoh field
            'role' => $profile->jabatan, // contoh field
            'nama_lengkap' => $profile->karyawan->nama_lengkap ?? null,
            'jabatan' => $profile->karyawan->jabatan ?? null,
            'foto' => $profile->karyawan->foto ? asset('storage/posts/' . $profile->karyawan->foto) : null,
            'ttd' => $profile->karyawan->ttd ? asset('storage/ttd/' . $profile->karyawan->ttd) : null,
        ]);
    }

}

