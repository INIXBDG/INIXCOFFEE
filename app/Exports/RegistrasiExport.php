<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
class RegistrasiExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            'No', 'Nama Peserta', 'Perusahaan', 'Materi Pelatihan', 'Periode Pelatihan', 'Instruktur', 'Sales', 'Souvenir'
        ];
    }

    public function map($row): array
    {
        return [
            $row['No'],
            $row['Nama Peserta'],
            $row['Perusahaan'],
            $row['Materi Pelatihan'],
            $row['Periode Pelatihan'],
            $row['Instruktur'],
            $row['Sales'],
            $row['Souvenir']
        ];
    }
}
