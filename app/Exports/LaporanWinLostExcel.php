<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Models\YourWinModel; // Ganti dengan model Anda
use App\Models\YourLostModel; // Ganti dengan model Anda

class LaporanWinLostExcel implements FromCollection, WithHeadings, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $results = collect();

        foreach ($this->data as $item) {
            $status = $item->grandtotal > 0 ? 'Win' : 'Lost'; // ✅ Cek kondisi benar

            $results->push([
                'ID' => $item->id,
                'Sales' => $item->sales_key,
                'Materi' => $item->nama_materi,
                'Perusahaan' => $item->nama_perusahaan,
                'Pax' => $item->pax,
                'Harga' => $item->harga,
                'Exam' => $item->total_exam,
                'Total PA' => $item->netsales,
                'Netsales' => $item->grandtotal,
                'Tanggal Awal' => $item->tanggal_awal,
                'Tanggal Akhir' => $item->tanggal_akhir,
                'Status' => $status, // ✅ Sudah benar: bisa Win atau Lost
            ]);
        }

        return $results;
    }


    public function headings(): array
    {
        return [
            'ID', 'Sales', 'Materi', 'Perusahaan', 'Pax', 'Harga', 'Exam', 'Total PA', 'Netsales', 'Tanggal Awal', 'Tanggal Akhir', 'Status'
        ];
    }

    public function title(): string
    {
        return 'Laporan Win & Lost';
    }
}
