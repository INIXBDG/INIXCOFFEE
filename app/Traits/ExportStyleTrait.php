<?php

namespace App\Traits;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

trait ExportStyleTrait
{
    public function xlStyle(Worksheet $sheet, string $range, string $bgRgb, string $fontRgb = '1F2937', int $fontSize = 10, bool $bold = false, string $hAlign = 'left'): void
    {
        $style = $sheet->getStyle($range);
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB("FF{$bgRgb}");
        $style->getFont()->setBold($bold)->setSize($fontSize)->setName('Calibri')->getColor()->setARGB("FF{$fontRgb}");

        $hMap = [
            'center' => Alignment::HORIZONTAL_CENTER,
            'right' => Alignment::HORIZONTAL_RIGHT,
            'left' => Alignment::HORIZONTAL_LEFT
        ];

        $style->getAlignment()
            ->setHorizontal($hMap[$hAlign] ?? Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        $style->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCCCCCC');
    }

    public function xlHide(Worksheet $sheet, string $cell): void
    {
        $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFFFF');
    }
}
