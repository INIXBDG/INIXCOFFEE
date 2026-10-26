<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FeedbackInstrukturExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    public function __construct(
        private array $rows,
        private array $monthHeadings
    ) {
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Karyawan',
            'Nama Instruktur',
            ...$this->monthHeadings,
            'Grand Total',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
