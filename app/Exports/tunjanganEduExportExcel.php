<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
// use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class tunjanganEduExportExcel implements FromCollection, ShouldAutoSize, WithHeadings
{
    protected $post;

    public function __construct(array $post)
    {
        $this->post = $post;
    }

    public function collection()
    {
        // Pastikan mengembalikan collection of arrays
        return collect($this->post);
    }

    public function headings(): array
    {
        return [
            'Nama Karyawan',
            'Materi',
            'Perusahaan',
            'Feedback',
            'Pax',
            'Level',
            'Durasi',
            'Tanggal Awal',
            'Tanggal Akhir',
            'Bulan',
            'Tahun',
            'Poin Durasi',
            'Poin Pax',
            'Tunjangan Feedback',
            'Tunjangan Total',
            'Status',
            'Keterangan',
        ];
    }
}
