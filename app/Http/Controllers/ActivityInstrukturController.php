<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ActivityInstrukturController extends Controller
{
    public function index()
    {
        $currentUser = Auth::user();

        $karyawan = $currentUser->karyawan;

        $divisionName = 'Tidak Terdaftar'; // Teks default jika user/karyawan tidak ada
        $activities = collect(); // Kumpulan data kosong by default

        if ($karyawan) {
            $userDivisionName = $karyawan->divisi;
            if (!empty($userDivisionName)) {
                $divisionName = $userDivisionName;
                $activities = ActivityInstruktur::with(['user.karyawan', 'task'])
                                        ->whereHas('user.karyawan', function ($query) use ($userDivisionName) {
                                            $query->where('divisi', $userDivisionName);

                                        })
                                        ->latest('activity_date') // Urutkan dari tanggal terbaru
                                        ->latest('created_at')    // Urutkan lagi by waktu pembuatan
                                        ->get();
            } else {
                $divisionName = 'Karyawan Tanpa Divisi';
            }

        }
        return view('activityinstruktur.index', compact('activities', 'divisionName'));
    }
}
