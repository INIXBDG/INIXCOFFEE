<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class FeedbackSalesExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $feedbackData;

    // Constructor to accept the data
    public function __construct(Collection $feedbackData)
    {
        $this->feedbackData = $feedbackData;
    }

    // Collection method to return the data for export
    public function collection()
    {
        return $this->feedbackData;
    }

    // Headings for the Excel sheet
    public function headings(): array
    {
        return [
            'Nama Perusahaan',
            'ID Registrasi',
            'ID RKM',
            'Nama Materi',
            'Sales Key',
            'Instruktur Key',
            'Instruktur Key 2',
            'Asisten Key',
            'Tanggal Awal',
            'Tanggal Akhir',
            'Email',
            'Materi',
            'Pelayanan',
            'Fasilitas',
            'Instruktur',
            'Instruktur 2',
            'Asisten',
            'Umum 1',
            'Umum 2',
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
                    'Fasilitas 1',
                    'Fasilitas 2',
                    'Fasilitas 3',
                    'Fasilitas 4',
                    'Fasilitas 5',
                    'Instruktur 1',
                    'Instruktur 2',
                    'Instruktur 3',
                    'Instruktur 4',
                    'Instruktur 5',
                    'Instruktur 6',
                    'Instruktur 7',
                    'Instruktur 8',
                    'Instruktur#2 1',
                    'Instruktur#2 2',
                    'Instruktur#2 3',
                    'Instruktur#2 4',
                    'Instruktur#2 5',
                    'Instruktur#2 6',
                    'Instruktur#2 7',
                    'Instruktur#2 8',
                    'Asisten 1',
                    'Asisten 2',
                    'Asisten 3',
                    'Asisten 4',
                    'Asisten 5',
                    'Asisten 6',
                    'Asisten 7',
                    'Asisten 8',
        ];
    }

    // Optional styles for the Excel sheet
    public function styles(Worksheet $sheet)
    {
        // Styling the heading row
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

