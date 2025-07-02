<?php

namespace App\Http\Controllers;

use App\Models\karyawan;
use Illuminate\Http\Request;

class DatabaseKPIController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:View DatabaseKPI', ['only' => ['index']]);
    }
    public function index()
    {
        return view('databasekpi.index');
    }

    public function getData()
    {
        $dataKaryawan = karyawan::all();
        $jumlah = $dataKaryawan->count();

        $data = $dataKaryawan->map(function ($karyawan) {
            $status = 'Tidak Diketahui';
            if ($karyawan->status_aktif === '1') {
                $status = 'Karyawan Aktif';
            } else if ($karyawan->status_aktif === '0') {
                $status = 'Karyawan Non Aktif';
            }

            return [
                'nama_lengkap' => $karyawan->nama_lengkap ?? '-',
                'nip'          => $karyawan->nip ?? '-',
                'divisi'       => $karyawan->divisi ?? '-',
                'jabatan'      => $karyawan->jabatan ?? '-',
                'status'       => $status ?? '-',
            ];
        });

        return response()->json([
            'jumlah' => $jumlah,
            'data' => $data
        ]);
    }

    public function store(Request $request) {}

    public function show() {}

    public function create() {}

    public function detail() {}


    public function dataDetail(Request $request) {}

    public function edit() {}
}
