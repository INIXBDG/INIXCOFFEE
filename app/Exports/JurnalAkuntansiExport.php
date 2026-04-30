<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class JurnalAkuntansiExport implements FromView, ShouldAutoSize
{
    protected $data, $periode, $format, $saldo_awal, $kas_masuk, $kas_keluar, $saldo_akhir;

    public function __construct($data, $periode, $format, $saldo_awal, $kas_masuk, $kas_keluar, $saldo_akhir)
    {
        $this->data = $data;
        $this->periode = $periode;
        $this->format = $format;
        $this->saldo_awal = $saldo_awal;
        $this->kas_masuk = $kas_masuk;
        $this->kas_keluar = $kas_keluar;
        $this->saldo_akhir = $saldo_akhir;
    }

    public function view(): View
    {
        return view('jurnalakuntansi.export_template', [
            'data' => $this->data,
            'periode' => $this->periode,
            'format' => $this->format,
            'saldo_awal' => $this->saldo_awal,
            'kas_masuk' => $this->kas_masuk,
            'kas_keluar' => $this->kas_keluar,
            'saldo_akhir' => $this->saldo_akhir,
        ]);
    }
}