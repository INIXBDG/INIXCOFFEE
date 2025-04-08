<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;

class RekapitulasiAbsenperKaryawanExport implements FromArray, WithHeadings, ShouldAutoSize
{
    use Exportable;

    protected $data;
    // protected $month;

    public function __construct($data)
    {
        $this->data = $data;
        // $this->month = $month;
    }

    public function headings(): array{
        return [
            'Nama Karyawan',
            'Tanggal',
            'Jam Masuk',
            'Jam Pulang',
            'Keterangan Masuk',
            'Keterangan Pulang',
            'Waktu Keterlambatan',

        ];
    }

    public function array(): array
    {
        // Convert the Eloquent collection to an array
        // return $this->data->toArray();
        return $this->data;
    }
}
