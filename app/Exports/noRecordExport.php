<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

class noRecordExport implements FromCollection
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
            'Kendala',
            'Tanggal',
            'Kronologi',
        ];
    }
}
