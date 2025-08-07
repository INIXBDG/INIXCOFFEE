<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Peluang;
use App\Models\Perusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CRMController extends Controller
{
    public function index()
    {
        $data = Perusahaan::select('kategori_perusahaan', DB::raw('count(*) as total'))
            ->groupBy('kategori_perusahaan')
            ->get();

        $total = $data->sum('total');

        $chartData = $data->map(function ($item) use ($total) {
            return [
                'kategori' => $item->kategori_perusahaan ?? 'Tidak Ada Kategori',
                'persen' => round(($item->total / $total) * 100, 2),
            ];
        });

        
        return view('crm.dashboard', compact('chartData'));
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
