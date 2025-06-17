<?php

namespace App\Exports;


use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class pengajuanIzinExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    protected $post;

    public function __construct(array $post)
    {
        $this->post = $post;
    }
    public function collection()
    {
        return collect($this->post);
    }

    public function headings(): array
    {
        return [
            'tipe',
            'Nama Karyawan',
            'Jam Mulai',
            'Jam Selesai',
            'Durasi',
            'Alasan',
            'Alasan Approval',
            'Approval',
        ];
    }
}
