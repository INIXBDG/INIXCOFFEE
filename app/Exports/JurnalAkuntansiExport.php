<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class JurnalAkuntansiExport implements FromView, ShouldAutoSize
{
    protected $data;
    protected $periode;

    public function __construct($data, $periode)
    {
        $this->data = $data;
        $this->periode = $periode;
    }

    public function view(): View
    {
        return view('jurnalakuntansi.export_template', [
            'data' => $this->data,
            'periode' => $this->periode
        ]);
    }
}