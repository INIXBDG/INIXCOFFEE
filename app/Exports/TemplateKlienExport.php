<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TemplateKlienExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'nama',
            'jenis_kelamin',
            'email',
            'no_hp',
            'alamat',
            'nama_perusahaan',
            'tanggal_lahir',
            'nama_materi',
            'sales_key',
            'dibuat_pada', // Kolom custom created_at
        ];
    }

    public function array(): array
    {
        return []; // Kosongkan karena kita hanya butuh headernya saja
    }
}
