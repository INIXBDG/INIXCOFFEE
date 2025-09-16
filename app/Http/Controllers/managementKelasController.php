<?php

namespace App\Http\Controllers;

use App\Models\RKM;
use Illuminate\Http\Request;
use Symfony\Contracts\Service\Attribute\Required;

class managementKelasController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:View Management Kelas', ['only' => ['index']]);
    }
    public function index()
    {
        return view('managementKelas.index');
    }

    public function get(Request $request)
    {
        $ruang = $request->filled('ruang') ? $request->input('ruang') : null;
        $tanggal_ruang = $request->filled('tanggal_ruang') ? $request->input('tanggal_ruang') : null;
        $filter_utama = $request->filled('filter_utama') ? $request->input('filter_utama') : null;

        $query = RKM::with(['sales', 'materi', 'instruktur', 'instruktur2', 'asisten', ''])
            ->where('metode_kelas', 'Offline');
        if ($ruang && $tanggal_ruang) {
            // $tanggalRuang = \Carbon\Carbon::parse($tanggal_ruang)->format('Y-m-d');
            $tanggalRuang = $tanggal_ruang;
            $query->where('ruang', $ruang)
                ->whereDate('tanggal_awal', $tanggalRuang);
        } elseif ($filter_utama) {
            // $filterUtama = \Carbon\Carbon::parse($filter_utama)->format('Y-m-d');
            $filterUtama = $filter_utama;
            $query->whereDate('tanggal_awal', $filterUtama);
        }

        $kelas = $query->get();

        return response()->json($kelas);
    }


    public function create($id) {}

    public function store(Request $request) {}
    public function update(Request $request, $id) {}
}
