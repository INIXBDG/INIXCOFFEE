<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class pengajuanCutiExport implements FromCollection, ShouldAutoSize, WithHeadings
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
            'Tipe',
            'Tanggal Awal',
            'Tanggal Akhir',
            'Durasi',
            'Kontak',
            'Alasan',
            'Alasan Manager',
            'Surat Sakit',
            'Approval Manager',
        ];
    }
}
