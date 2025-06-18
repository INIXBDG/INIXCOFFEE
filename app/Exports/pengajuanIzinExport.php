<?php

namespace App\Exports;


use App\Models\absensi_noRecord;
use App\Models\izinTigaJam;
use App\Models\pembatalanCuti;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class pengajuanIzinExport implements FromView
{

    public function __construct()
    {
        //
    }

    public function view(): View
    {
        $rows = izinTigaJam::with('karyawan')->get();

        return view('exports.pengajuanizinjam', [
            'rows' => $rows,
        ]);
    }
}
