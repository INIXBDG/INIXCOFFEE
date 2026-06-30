<?php

namespace App\Exports;

use App\Models\ObTools;
use App\Models\KondisiTools;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Log;

class KondisiToolsEksport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $reportType;
    protected $filters;

    public function __construct($reportType, $filters = [])
    {
        $this->reportType = $reportType;
        $this->filters = $filters;
    }

    public function collection()
    {
        if ($this->reportType === 'alat') {
            return $this->getAlatCollection();
        } elseif ($this->reportType === 'pemeriksaan') {
            return $this->getPemeriksaanCollection();
        }
        
        return collect([]);
    }

    private function getAlatCollection()
    {
        $query = ObTools::query();
        Log::info($this->filters['kategori']);

        if (!empty($this->filters['kategori'])) {
            $query->where('kategori', $this->filters['kategori']);
        }

        if (!empty($this->filters['search'])) {
            $query->where('nama_alat', 'like', '%' . $this->filters['search'] . '%');
        }

        return $query->orderBy('kategori')->orderBy('nama_alat')->get();
    }

    private function getPemeriksaanCollection()
    {
        $query = KondisiTools::with('alat');

        if (!empty($this->filters['tanggal_mulai'])) {
            $query->whereDate('tanggal_pemeriksaan', '>=', $this->filters['tanggal_mulai']);
        }

        if (!empty($this->filters['tanggal_selesai'])) {
            $query->whereDate('tanggal_pemeriksaan', '<=', $this->filters['tanggal_selesai']);
        }

        if (!empty($this->filters['kondisi'])) {
            $query->where('kondisi', $this->filters['kondisi']);
        }

        if (!empty($this->filters['id_alat'])) {
            $query->where('id_alat', $this->filters['id_alat']);
        }

        return $query->orderBy('tanggal_pemeriksaan', 'desc')->get();
    }

    public function headings(): array
    {
        if ($this->reportType === 'alat') {
            return [
                'No',
                'Nama Alat',
                'Kategori',
                'Jumlah (Qty)'
            ];
        } elseif ($this->reportType === 'pemeriksaan') {
            return [
                'No',
                'Tanggal Pemeriksaan',
                'Nama Alat',
                'Kategori Alat',
                'Kondisi',
                'Catatan',
            ];
        }

        return [];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        if ($this->reportType === 'alat') {
            return [
                $no,
                $row->nama_alat,
                $row->kategori,
                $row->qty
            ];
        } elseif ($this->reportType === 'pemeriksaan') {
            return [
                $no,
                $row->tanggal_pemeriksaan ? date('d/m/Y', strtotime($row->tanggal_pemeriksaan)) : '-',
                $row->alat->nama_alat ?? '-',
                $row->alat->kategori ?? '-',
                $row->kondisi,
                $row->catatan ?? '-',
            ];
        }

        return [];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle("A1:{$sheet->getHighestColumn()}{$highestRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        return [];
    }
}