<?php

namespace App\Http\Controllers\office;

use App\Http\Controllers\Controller;
use App\Models\karyawan;
use App\Models\Materi;
use App\Models\Modul;
use App\Models\NomorModul;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\ModulNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification as FacadesNotification;
use Illuminate\Validation\Rule; // Add this import

class ModulController extends Controller
{
    public function index()
    {
        $materis = Materi::all();
        $moduls = NomorModul::with('moduls')->orderBy('id', 'desc')->get();
        $now = Carbon::now();

        $romawiBulan = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ];

        $bulanRomawi = $romawiBulan[$now->month];
        $tahun2Digit = $now->format('y');

        $nomorPrefix = "M/BDG/000/{$bulanRomawi}/{$tahun2Digit}";

        return view('office.modul.index', compact('materis', 'moduls', 'nomorPrefix'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomor' => 'required|string',
            'tipe' => 'required|string|in:Regular,Authorize',
        ]);

        $exists = NomorModul::where('nomor', $request->nomor)->exists();

        if ($exists) {
            return back()->with('error', 'Nomor ini sudah terdaftar, silahkan gunakan nomor lain.');
        }

        $nomor = new NomorModul();
        $nomor->nomor = $request->nomor;
        $nomor->tipe = $request->tipe;
        $nomor->save();

        return back()->with('success', 'Nomor berhasil didaftarkan, silahkan masukkan data terkait dalam detail.');
    }


    public function deleteNomor($id)
    {
        $nomor = NomorModul::findOrFail($id);
        $modul = Modul::where('nomor', $nomor->nomor)->get();
        foreach ($modul as $key => $item) {
            $item->delete();
        }
        $nomor->delete();

        return back()->with('success', 'Data terkait berhasil dihapus');
    }
}
