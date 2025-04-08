<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PesertaPerSalesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
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
            'No', 'Nama', 'Email', 'Jenis Kelamin', 'Nomor Handphone', 'Alamat', 'Perusahaan', 'Sales', 'Tanggal Lahir' 
        ];
    }

    public function map($row): array
    {
        return [
            $row['No'],
            $row['Nama'],
            $row['Email'],
            $row['Jenis Kelamin'],
            $row['Nomor Handphone'],
            $row['Alamat'],
            $row['Perusahaan'],
            $row['Sales'],
            $row['Tanggal Lahir']
        ];
    }
}
