<?php

namespace App\Exports;

use App\Models\ThrPeriod;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ThrExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $period;
    private $rowNumber = 0;

    public function __construct(ThrPeriod $period)
    {
        $this->period = $period;
    }

    public function collection()
    {
        return $this->period->items()
            ->with(['karyawan:id,nip'])
            ->orderBy('nama_lengkap', 'asc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'NIP',
            'Nama Lengkap',
            'Jabatan',
            'Status Karyawan',
            'Rekening Maybank',
            'Tanggal Masuk',
            'Masa Kerja',
            'Basic Salary',
            'Nilai THR (Sistem)',
            'Nilai THR Final',
            'Diedit Manual',
            'Catatan',
        ];
    }

    public function map($item): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $item->karyawan->nip ?? '-',
            $item->nama_lengkap,
            $item->jabatan ?? '-',
            $item->status_karyawan ?? '-',
            $item->rekening_maybank ?? '-',
            $item->tanggal_join ? $item->tanggal_join->format('d/m/Y') : '-',
            $item->masa_kerja_teks ?? '-',
            $item->basic_salary,
            $item->nilai_thr_sistem,
            $item->nilai_thr_final,
            $item->is_manual ? 'Ya' : 'Tidak',
            $item->catatan ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF4F46E5'],
                ],
            ],
        ];
    }
}
