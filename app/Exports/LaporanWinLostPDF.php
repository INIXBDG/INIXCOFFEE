<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView; // Jika ingin integrasi dengan Excel, tapi kita pakai DomPDF
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class LaporanWinLostPdf
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Generate PDF dan return download response
     */
    public function download($filename = 'Laporan_Win_Lost.pdf')
    {
        // Hitung total Win dan Lost untuk header
        $winCount = $this->data->where('status', 'Win')->count();
        $lostCount = $this->data->where('status', 'Lost')->count();
        $totalData = $this->data->count();
        
        // Load view dengan data
        $pdf = Pdf::loadView('exports.win_lost_pdf', [
            'data' => $this->data,
            'title' => 'Laporan Penjualan',
            'winCount' => $winCount,
            'lostCount' => $lostCount,
            'totalData' => $totalData,
        ]);

        // Set paper size dan orientasi (optional: landscape untuk tabel lebar)
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download($filename);
    }
}
