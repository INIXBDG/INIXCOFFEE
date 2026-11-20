<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class RencanaPembelianHrExport implements FromArray, WithEvents, WithTitle
{
    private array $rows = [];
    private array $meta = [];
    private array $colWidths = [];
    private int $maxCols = 1;

    private const CENTER_HEADERS = [
        'No',
        'Tanggal',
        'Tenggat',
        'No. KK',
        'Kategori',
        'Qty',
        'Status',
    ];

    private const RIGHT_HEADERS = [
        'Harga Satuan',
        'Subtotal',
    ];

    public function __construct(
        private string $judul,
        private string $periodeLabel,
        private array $sections
    ) {
        $this->buildSheetData();
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function title(): string
    {
        return 'Rencana Pembelian';
    }

    private function buildSheetData(): void
    {
        foreach ($this->sections as $sec) {
            $this->maxCols = max($this->maxCols, count($sec['headings'] ?? []));
        }

        // Row 1: Judul Utama
        $this->rows[] = [$this->judul];
        $this->meta['titleRow'] = count($this->rows);

        // Row 2: Subtitle periode & waktu cetak
        $sub = ($this->periodeLabel ? "Periode: {$this->periodeLabel} | " : '') . 'Dicetak: ' . now()->format('d-m-Y H:i');
        $this->rows[] = [$sub];
        $this->meta['subtitleRow'] = count($this->rows);

        // Row 3: Spacer
        $this->rows[] = [''];
        $this->meta['tables'] = [];

        foreach ($this->sections as $idx => $sec) {
            if ($idx > 0) {
                $this->rows[] = [''];
            }

            $headings = $sec['headings'] ?? [];
            $dataRows = $sec['rows'] ?? [];
            $colCount = max(1, count($headings));

            $tableMeta = [
                'colCount'     => $colCount,
                'headings'     => $headings,
                'sectionRow'   => null,
                'headerRow'    => null,
                'dataStartRow' => null,
                'dataEndRow'   => null,
                'emptyRow'     => null,
                'footerRow'    => null,
            ];

            if (!empty($sec['judul'])) {
                $this->rows[] = [$sec['judul']];
                $tableMeta['sectionRow'] = count($this->rows);
            }

            $this->rows[] = $headings;
            $tableMeta['headerRow'] = count($this->rows);
            $this->trackWidths($headings, true);

            if (empty($dataRows)) {
                $this->rows[] = ['Tidak ada data.'];
                $tableMeta['emptyRow'] = count($this->rows);
            } else {
                $tableMeta['dataStartRow'] = count($this->rows) + 1;
                foreach ($dataRows as $r) {
                    $vals = array_values($r);
                    $this->rows[] = $vals;
                    $this->trackWidths($vals, false);
                }
                $tableMeta['dataEndRow'] = count($this->rows);

                if (!empty($sec['footer'])) {
                    $this->rows[] = [$sec['footer']];
                    $tableMeta['footerRow'] = count($this->rows);
                }
            }

            $this->meta['tables'][] = $tableMeta;
        }
    }

    private function trackWidths(array $row, bool $isHeader): void
    {
        foreach (array_values($row) as $i => $val) {
            $colIdx = $i + 1;
            $len    = mb_strlen((string) ($val ?? ''));
            if ($isHeader) {
                $len += 2;
            }
            if (!isset($this->colWidths[$colIdx]) || $len > $this->colWidths[$colIdx]) {
                $this->colWidths[$colIdx] = $len;
            }
        }
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet        = $event->sheet->getDelegate();
                $maxColLetter = Coordinate::stringFromColumnIndex($this->maxCols);

                $sheet->getParent()->getDefaultStyle()->getFont()
                    ->setName('Calibri')
                    ->setSize(10);

                // Judul utama
                $titleRow = $this->meta['titleRow'];
                $sheet->mergeCells("A{$titleRow}:{$maxColLetter}{$titleRow}");
                $sheet->getStyle("A{$titleRow}:{$maxColLetter}{$titleRow}")->applyFromArray([
                    'font' => [
                        'bold'  => true,
                        'size'  => 14,
                        'color' => ['argb' => 'FF1E1B4B'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension($titleRow)->setRowHeight(26);

                // Subtitle
                $subRow = $this->meta['subtitleRow'];
                $sheet->mergeCells("A{$subRow}:{$maxColLetter}{$subRow}");
                $sheet->getStyle("A{$subRow}:{$maxColLetter}{$subRow}")->applyFromArray([
                    'font' => [
                        'italic' => true,
                        'size'   => 9,
                        'color'  => ['argb' => 'FF6B7280'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension($subRow)->setRowHeight(18);
                $sheet->getRowDimension($subRow + 1)->setRowHeight(10);

                // Styling tiap section tabel
                foreach ($this->meta['tables'] as $t) {
                    $endCol = Coordinate::stringFromColumnIndex($t['colCount']);

                    if ($t['sectionRow']) {
                        $sr = $t['sectionRow'];
                        $sheet->mergeCells("A{$sr}:{$endCol}{$sr}");
                        $sheet->getStyle("A{$sr}:{$endCol}{$sr}")->applyFromArray([
                            'font' => [
                                'bold'  => true,
                                'size'  => 11,
                                'color' => ['argb' => 'FF1E1B4B'],
                            ],
                            'fill' => [
                                'fillType'   => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFEEF2FF'],
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_LEFT,
                                'vertical'   => Alignment::VERTICAL_CENTER,
                            ],
                            'borders' => [
                                'outline' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color'       => ['argb' => 'FFC7D2FE'],
                                ],
                            ],
                        ]);
                        $sheet->getRowDimension($sr)->setRowHeight(24);
                    }

                    // Header tabel
                    $hr = $t['headerRow'];
                    $sheet->getStyle("A{$hr}:{$endCol}{$hr}")->applyFromArray([
                        'font' => [
                            'bold'  => true,
                            'size'  => 10,
                            'color' => ['argb' => 'FFFFFFFF'],
                        ],
                        'fill' => [
                            'fillType'   => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FF312E81'],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical'   => Alignment::VERTICAL_CENTER,
                            'wrapText'   => true,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color'       => ['argb' => 'FF4338CA'],
                            ],
                        ],
                    ]);
                    $sheet->getRowDimension($hr)->setRowHeight(25);

                    $tableBottomRow = $hr;

                    if ($t['emptyRow']) {
                        $er = $t['emptyRow'];
                        $tableBottomRow = $er;
                        $sheet->mergeCells("A{$er}:{$endCol}{$er}");
                        $sheet->getStyle("A{$er}:{$endCol}{$er}")->applyFromArray([
                            'font' => [
                                'italic' => true,
                                'color'  => ['argb' => 'FF6B7280'],
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical'   => Alignment::VERTICAL_CENTER,
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color'       => ['argb' => 'FFCBD5E1'],
                                ],
                            ],
                        ]);
                        $sheet->getRowDimension($er)->setRowHeight(22);
                    } elseif ($t['dataStartRow'] && $t['dataEndRow']) {
                        $ds = $t['dataStartRow'];
                        $de = $t['dataEndRow'];
                        $tableBottomRow = $de;

                        $sheet->getStyle("A{$ds}:{$endCol}{$de}")->applyFromArray([
                            'font' => [
                                'size'  => 10,
                                'color' => ['argb' => 'FF111827'],
                            ],
                            'alignment' => [
                                'vertical' => Alignment::VERTICAL_CENTER,
                                'wrapText' => true,
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color'       => ['argb' => 'FFCBD5E1'],
                                ],
                            ],
                        ]);

                        foreach ($t['headings'] as $i => $headingText) {
                            $colLetter = Coordinate::stringFromColumnIndex($i + 1);
                            $hAlign    = Alignment::HORIZONTAL_LEFT;

                            if (in_array($headingText, self::CENTER_HEADERS, true)) {
                                $hAlign = Alignment::HORIZONTAL_CENTER;
                            } elseif (in_array($headingText, self::RIGHT_HEADERS, true)) {
                                $hAlign = Alignment::HORIZONTAL_RIGHT;
                            }

                            $sheet->getStyle("{$colLetter}{$ds}:{$colLetter}{$de}")
                                ->getAlignment()
                                ->setHorizontal($hAlign);
                        }

                        for ($r = $ds; $r <= $de; $r++) {
                            if (($r - $ds) % 2 === 1) {
                                $sheet->getStyle("A{$r}:{$endCol}{$r}")->getFill()
                                    ->setFillType(Fill::FILL_SOLID)
                                    ->getStartColor()->setARGB('FFF8FAFC');
                            }
                            $sheet->getRowDimension($r)->setRowHeight(20);
                        }

                        if ($t['footerRow']) {
                            $fr = $t['footerRow'];
                            $tableBottomRow = $fr;
                            $sheet->mergeCells("A{$fr}:{$endCol}{$fr}");
                            $sheet->getStyle("A{$fr}:{$endCol}{$fr}")->applyFromArray([
                                'font' => [
                                    'bold'  => true,
                                    'size'  => 10,
                                    'color' => ['argb' => 'FF1E1B4B'],
                                ],
                                'fill' => [
                                    'fillType'   => Fill::FILL_SOLID,
                                    'startColor' => ['argb' => 'FFEEF2FF'],
                                ],
                                'alignment' => [
                                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                                    'vertical'   => Alignment::VERTICAL_CENTER,
                                ],
                                'borders' => [
                                    'allBorders' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                        'color'       => ['argb' => 'FF94A3B8'],
                                    ],
                                ],
                            ]);
                            $sheet->getRowDimension($fr)->setRowHeight(23);
                        }
                    }

                    $sheet->getStyle("A{$hr}:{$endCol}{$tableBottomRow}")->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' => Border::BORDER_MEDIUM,
                                'color'       => ['argb' => 'FF312E81'],
                            ],
                        ],
                    ]);
                }

                // Lebar kolom proporsional
                for ($c = 1; $c <= $this->maxCols; $c++) {
                    $colLetter = Coordinate::stringFromColumnIndex($c);
                    $rawLen    = $this->colWidths[$c] ?? 10;

                    if ($c === 1) {
                        $width = max(6, min(10, $rawLen + 3));
                    } else {
                        $width = max(12, min(40, $rawLen + 4));
                    }

                    $sheet->getColumnDimension($colLetter)->setWidth($width);
                }

                $pageSetup = $sheet->getPageSetup();
                $pageSetup->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                $pageSetup->setFitToWidth(1);
                $pageSetup->setFitToHeight(0);
                $sheet->setShowGridlines(true);
            },
        ];
    }
}
