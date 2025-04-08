<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Peserta;
use App\Models\Registrasi;

class PesertaController extends Controller
{
    public function cekPeserta(Request $request)
    {
        $email = $request->input('email');
        $peserta = Peserta::with('perusahaan')->where('email', $email)->first();
        $post = Peserta::with('perusahaan')->paginate(25);

        return response()->json(['peserta' => $peserta]);
    }

    public function listMateri($id_peserta)
    {
        $registrasi = Registrasi::with('materi')->where('id_peserta', $id_peserta)->with('rkm')->get();

        // // Mengakses nama_materi dari setiap objek Registrasi
        // $listMateri = $registrasi->map(function ($item) {
        //     return $item->rkm->materi->nama_materi;
        // });

        return response()->json(['list' => $registrasi]);
    }

}
