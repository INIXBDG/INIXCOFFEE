<?php

namespace App\Exports;

use App\Models\Nilaifeedback;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithHeadings;

class NilaifeedbackExport implements FromArray, WithHeadings
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
            'No',
            'Nama Materi',
            'Instruktur',
            'Sales',
            'Tanggal Awal',
            'Tanggal Akhir',
            'Materi 1',
            'Materi 2',
            'Materi 3',
            'Materi 4',
            'Pelayanan 1',
            'Pelayanan 2',
            'Pelayanan 3',
            'Pelayanan 4',
            'Pelayanan 5',
            'Pelayanan 6',
            'Pelayanan 7',
            'Fasilitas laboratorium 1',
            'Fasilitas laboratorium 2',
            'Fasilitas laboratorium 3',
            'Fasilitas laboratorium 4',
            'Fasilitas laboratorium 5',
            'Instruktur 1',
            'Instruktur 2',
            'Instruktur 3',
            'Instruktur 4',
            'Instruktur 5',
            'Instruktur 6',
            'Instruktur 7',
            'Instruktur 8',
            'Instruktur #2 1',
            'Instruktur #2 2',
            'Instruktur #2 3',
            'Instruktur #2 4',
            'Instruktur #2 5',
            'Instruktur #2 6',
            'Instruktur #2 7',
            'Instruktur #2 8',
            'Asisten 1',
            'Asisten 2',
            'Asisten 3',
            'Asisten 4',
            'Asisten 5',
            'Asisten 6',
            'Asisten 7',
            'Asisten 8',
            'Pengalaman',
            'Saran',

        ];
    }

    public function array(): array{
        return $this->data;
    }

    // public function query()
    // {
    //     return Nilaifeedback::whereHas('rkm', function ($query) {
    //         $query->whereYear('tanggal_awal', $this->year)
    //               ->whereMonth('tanggal_awal', $this->month);
    //     })->with('rkm');
    // }



}
