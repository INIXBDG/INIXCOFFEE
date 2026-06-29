<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

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
            ->select('id', 'id_instruktur', 'jabatan', 'karyawan_id')
            ->get();

        return response()->json([
            'data' => $users
        ]);
    }
}
