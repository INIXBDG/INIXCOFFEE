<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Carbon\Carbon;

class LaporanPenjualanExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnFormatting, ShouldAutoSize
{
    protected $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function map($row): array
    {
        // Format tanggal agar Excel mengenalinya sebagai date
        $tanggalAwal = $row['tanggal_awal'] ? Carbon::parse($row['tanggal_awal'])->format('Y-m-d') : null;
        $tanggalAkhir = $row['tanggal_akhir'] ? Carbon::parse($row['tanggal_akhir'])->format('Y-m-d') : null;

        return [
            $row['id'],
            $row['sales_key'],
            $row['nama_materi'],
            $row['nama_perusahaan'],
            $row['pax'],
            $row['harga'], // akan diformat di columnFormats
            $row['total_penjualan'], // akan diformat di columnFormats
            $row['exam'], // akan diformat di columnFormats
            $row['total_exam'], // akan diformat di columnFormats
            $row['netsales'], // akan diformat di columnFormats
            $row['grandtotal'], // akan diformat di columnFormats
            $tanggalAwal,
            $tanggalAkhir,
            $row['metode_kelas'] ?? '-',
            $row['invoice'] ? $row['invoice']['invoice_number'] : 'Belum ada',
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Sales Key',
            'Materi',
            'Perusahaan',
            'Pax',
            'Harga per Pax',
            'Total Penjualan',
            'Harga Exam',
            'Total Exam',
            'Total PA',
            'Net Sales',
            'Tanggal Awal',
            'Tanggal Akhir',
            'Metode Kelas',
            'Nomor Invoice',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->data) + 1;

        // Header Style (baris 1)
        $sheet->getStyle('A1:O1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2F80ED'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        if ($lastRow >= 2) {
            $range = "A2:O{$lastRow}";

            $sheet->getStyle($range)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'D3D3D3'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            $sheet->getStyle("F2:K{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("L2:M{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Tanggal
        }

        $sheet->getColumnDimension('A')->setWidth(8);   // ID
        $sheet->getColumnDimension('B')->setWidth(10);  // Sales Key
        $sheet->getColumnDimension('C')->setWidth(25);  // Materi
        $sheet->getColumnDimension('D')->setWidth(25);  // Perusahaan
        $sheet->getColumnDimension('E')->setWidth(8);   // Pax
        $sheet->getColumnDimension('F')->setWidth(15);  // Harga per Pax
        $sheet->getColumnDimension('G')->setWidth(15);  // Total Penjualan
        $sheet->getColumnDimension('H')->setWidth(15);  // Harga Exam
        $sheet->getColumnDimension('I')->setWidth(15);  // Total Exam
        $sheet->getColumnDimension('J')->setWidth(15);  // NetSales
        $sheet->getColumnDimension('K')->setWidth(15);  // Grand Total
        $sheet->getColumnDimension('L')->setWidth(15);  // Tanggal Awal
        $sheet->getColumnDimension('M')->setWidth(15);  // Tanggal Akhir
        $sheet->getColumnDimension('N')->setWidth(15);  // Metode Kelas
        $sheet->getColumnDimension('O')->setWidth(20);  // Nomor Invoice

        return [];
    }

    public function columnFormats(): array
    {
        return [
            'F' => '_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"??_);_(@_)', // Harga per Pax
            'G' => '_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"??_);_(@_)', // Total Penjualan
            'H' => '_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"??_);_(@_)', // Harga Exam
            'I' => '_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"??_);_(@_)', // Total Exam
            'J' => '_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"??_);_(@_)', // NetSales
            'K' => '_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"??_);_(@_)', // Grand Total

            'L' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'M' => NumberFormat::FORMAT_DATE_DDMMYYYY, 
        ];
    }
}
