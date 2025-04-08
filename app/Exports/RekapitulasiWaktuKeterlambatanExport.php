<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Sheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapitulasiWaktuKeterlambatanExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    use Exportable;

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function headings(): array{
        return array_merge(['Nama Karyawan'], [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ]);
    }

    public function array(): array
    {
        return $this->data;
    }

    public function styles(Worksheet $sheet){
        $sheet->getStyle('1')->getFont()->setBold(true);
        $rowCount = count($this->data) + 1;

                // Apply conditional formatting
                for ($row = 2; $row <= $rowCount; $row++) {
                    foreach (range('B', 'M') as $col) {
                        $keterangan = $sheet->getCell("{$col}{$row}")->getValue();

                        if (strpos($keterangan, 'Tidak pernah terlambat') !== false) {
                            $sheet->getStyle("{$col}{$row}")
                                ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLUE);
                        } elseif (strpos($keterangan, 'Terlambat > 15 menit') !== false) {
                            $sheet->getStyle("{$col}{$row}")
                                ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
                        }
                    }
                }
    }
}
