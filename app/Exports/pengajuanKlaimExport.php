<?php

namespace App\Exports;

use App\Models\absensi_noRecord;
use App\Models\pembatalanCuti;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class pengajuanKlaimExport implements FromView
{
    protected $jenisPK;

    public function __construct($jenisPK)
    {
        $this->jenisPK = $jenisPK;
    }

    public function view(): View
    {
        if ($this->jenisPK === 'No Record' || $this->jenisPK === 'Scheme Work') {
            $rows = absensi_noRecord::with(['karyawan', 'absensiKaryawan'])
                ->where('jenis_PK', $this->jenisPK)
                ->get();
        } else {
            $rows = pembatalanCuti::with(['karyawan', 'pengajuancuti'])->get();
        }

        if ($this->jenisPK === 'No Record' || $this->jenisPK === 'Scheme Work') {
            return view('exports.noRecord', [
                'rows' => $rows,
            ]);
        } else {
            return view('exports.cancelLeave', [
                'rows' => $rows,
            ]);
        }
    }
}
