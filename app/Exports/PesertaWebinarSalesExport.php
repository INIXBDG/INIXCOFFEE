<?php

namespace App\Exports;

use App\Models\PesertaWebinarSales;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PesertaWebinarSalesExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithTitle,
    WithStyles,
    WithColumnFormatting,
    ShouldAutoSize
{
    private int $no = 0;

    public function __construct(private int $yearMappingId)
    {
    }

    public function collection()
    {
        return PesertaWebinarSales::with(['perusahaan:id,nama_perusahaan,kategori_perusahaan', 'sales'])
            ->where('year_mapping_id', $this->yearMappingId)
            ->orderBy('nama_peserta')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'Email',
            'No HP',
            'Domisili',
            'Perusahaan',
            'Kategori Perusahaan',
            'Sales',
            'Info',
            'Paket',
        ];
    }

    public function map($p): array
    {
        $sales = $p->id_sales
            ? $p->id_sales . ($p->sales->nama_lengkap ?? null ? ' - ' . $p->sales->nama_lengkap : '')
            : '-';

        return [
            ++$this->no,
            $p->nama_peserta,
            $p->email_peserta,
            (string) $p->no_hp,
            $p->domisili ?: '-',
            $p->perusahaan->nama_perusahaan ?? '-',
            $p->perusahaan->kategori_perusahaan ?? '-',
            $sales,
            $p->info ?: '-',
            $p->paket ?: '-',
        ];
    }

    // Kolom No HP (D) sebagai teks supaya angka 0 di depan tidak hilang
    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);
        $sheet->getStyle('A1:J1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E2E8F0');

        $sheet->freezePane('A2');

        return [];
    }

    public function title(): string
    {
        return 'Peserta Webinar';
    }
}