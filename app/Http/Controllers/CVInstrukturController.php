<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class CVInstrukturController extends Controller
{
    public function index()
    {
        return view('cv-instruktur.index');
    }

    public function data(Request $request)
    {
        $users = User::with('karyawan')
            ->whereNotNull('id_instruktur')
            ->where('id_instruktur', '!=', '')
            ->where('status_akun', '1')
            ->where('username', 'not like', 'OL%')
            ->whereDoesntHave('karyawan', function ($query) {
                $query->where('kode_karyawan', 'like', 'OL%');
            })
            ->whereHas('karyawan', function ($query) {
                $query->where('divisi', 'Education');
            })
            ->select('id', 'username', 'id_instruktur', 'jabatan', 'karyawan_id')
            ->get();

        return response()->json([
            'data' => $users
        ]);
    }

    public function show($id)
    {
        $user = User::with([
            'karyawan.educations',
            'karyawan.specializations',
            'karyawan.rkmsInstruktur.materi',
            'karyawan.rkmsInstruktur.perusahaan',
            'sertifikasis'
        ])->findOrFail($id);

        return view('cv-instruktur.show', compact('user'));
    }

    public function downloadPdf($id)
    {
        $user = User::with([
            'karyawan.educations',
            'karyawan.specializations',
            'karyawan.rkmsInstruktur.materi',
            'karyawan.rkmsInstruktur.perusahaan',
            'sertifikasis'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('cv-instruktur.pdf', compact('user'))
                  ->setPaper('a4', 'portrait');

        $namaFile = 'CV_Instruktur_' . (optional($user->karyawan)->nama_lengkap ?? 'Unknown') . '.pdf';

        return $pdf->download($namaFile);
    }
}

