<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;
class RekapitulasiAbsenperBulanExport implements FromArray, WithHeadings, ShouldAutoSize
{
    use Exportable;

    protected $data;
    protected $dates;

    public function __construct($data, $dates)
    {
        $this->data = $data;
        $this->dates = $dates;
    }

    public function headings(): array
    {
        // Set up the headers with dynamic dates
        $headers = ['Nama Karyawan'];
        foreach ($this->dates as $date) {
            $headers[] = $date->format('j F');
        }
        
        // Menambahkan header "Total Keterlambatan"
        $headers[] = 'Total Keterlambatan'; 
        
        return $headers;
    }

    public function array(): array
    {
        return $this->data;
    }
}