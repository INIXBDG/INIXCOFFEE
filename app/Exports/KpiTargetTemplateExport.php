<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use App\Models\DataTarget;

class KpiTargetTemplateExport implements WithHeadings, WithStyles, WithColumnFormatting
{
    protected $groupedReferences;

    public function __construct()
    {
        $this->groupedReferences = $this->buildGroupedReferences();
    }

    private function buildGroupedReferences()
    {
        $routeMapping = $this->getRouteMapping();
        $dataTargets = DataTarget::all();
        
        $grouped = [];
        
        foreach ($dataTargets as $target) {
            $tipe = $target->tipe_target ?? 'Lainnya';
            $route = strtolower($target->asistant_route);
            
            $jabatans = [];
            foreach ($routeMapping as $jabatan => $routes) {
                if (in_array($route, array_map('strtolower', $routes))) {
                    $jabatans[] = $jabatan;
                }
            }
            
            if (empty($jabatans)) {
                $jabatans = ['Umum'];
            }
            
            foreach ($jabatans as $jabatan) {
                $grouped[$tipe][$jabatan][] = $target->asistant_route;
            }
        }
        
        ksort($grouped);
        foreach ($grouped as &$tipeData) {
            ksort($tipeData);
            foreach ($tipeData as &$routes) {
                sort($routes);
            }
        }
        
        return $grouped;
    }

    private function getRouteMapping(): array
    {
        return [
            'gm' => [
                'pemasukan kotor', 'pemasukan bersih', 'kepuasan pelanggan',
                'rasio biaya operasional terhadap revenue', 'performa kpi departemen'
            ],
            'customer care' => [
                'peserta puas dengan pelayanan dan fasilitas training',
                'dorong inovasi pelayanan', 'penanganan komplain perseta',
                'report persiapan kelas'
            ],
            'finance & accounting' => [
                'outstanding', 'inisiatif efisiensi keuangan',
                'mengurangi manual work dan error', 'laporan analisis keuangan',
                'pencairan biaya operasional', 'penyelesaian tagihan perusahaan',
                'akurasi pencatatan masuk'
            ],
            'hrd' => [
                'pelaksanaan kegiatan karyawan', 'pengeluaran biaya karyawan',
                'administrasi karyawan'
            ],
            'driver' => [
                'perbaikan kendaraan', 'report kondisi kendaraan',
                'kontrol pengeluaran transportasi', 'feedback kenyamanan berkendaran'
            ],
            'office boy' => [
                'feedback kebersihan dan kenyamanan', 'penyelesaian tugas harian'
            ],
            'koordinator itsm' => [
                'meningkatkan kepuasan dan loyalitas peserta/client',
                'availability sistem internal kritis'
            ],
            'programmer' => [
                'ketepatan waktu penyelesaian fitur',
                'mengukur kualitas aplikasi agar minim bug'
            ],
            'tim digital' => [
                'konsistensi campaign digital', 'efektifitas digital marketing'
            ],
            'technical support' => [
                'keberhasilan support memenuhi sla', 'kualitas layanan exam'
            ],
            'instruktur' => [
                'presentase kinerja instruktur', 'kepuasan peserta pelatihan',
                'upseling lanjutan materi', 'sertifikasi kompetensi internal',
                'pelatihan kompetensi eksternal'
            ],
            'education manager' => [
                'pengembangan kurikulum pelatihan', 'peningkatan knowledge sharing',
                'peningkatan kontribusi pelatihan', 'evaluasi kinerja instruktur'
            ],
            'sales' => [
                'target penjualan tahunan', 'biaya akuisisi perclient'
            ],
            'spv sales' => [
                'meningkatkan revenue perusahaan', 'customer acquisition cost',
                'evaluasi kinerja sales'
            ],
            'adm sales' => [
                'laporan mom', 'akurasi kelengkapan data penjualan',
                'todo administrasi'
            ],
            'admin holding' => [
                'ketepatan waktu po', 'kualitas dokumentasi support dan proctor'
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'Judul KPI*',
            'Deskripsi',
            'Jabatan*',
            'Karyawan (Opsional)',
            'Assistant Route*',
            'Detail Jangka (Jika Tahunan)*',
            '',
            'REFERENSI ASSISTANT ROUTE',
            '',
            '',
            '',
            ''
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F81BD']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $exampleRow = [
            'Target Q1 2024',
            'Target penjualan untuk kuartal pertama',
            'Sales',
            'Budi Santoso',
            'target penjualan tahunan',
            '2024',
            '', '', '', '', '', ''
        ];
        $sheet->fromArray([$exampleRow], null, 'A2');

        $sheet->getStyle('A2:F2')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '666666']],
        ]);

        $this->buildReferenceSection($sheet);

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getColumnDimension('G')->setWidth(2);
        foreach (range('H', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function buildReferenceSection(Worksheet $sheet)
    {
        $startRow = 1;
        $startCol = 'H';
        
        $sheet->mergeCells("{$startCol}{$startRow}:L{$startRow}");
        $sheet->getStyle("{$startCol}{$startRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '2E5984']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E7ECEF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['bottom' => ['borderStyle' => 'thin', 'color' => ['rgb' => '2E5984']]],
        ]);
        $sheet->getCell("{$startCol}{$startRow}")->setValue('REFERENSI ASSISTANT ROUTE');
        
        $currentRow = $startRow + 2;
        
        foreach ($this->groupedReferences as $tipe => $jabatans) {
            $sheet->mergeCells("{$startCol}{$currentRow}:L{$currentRow}");
            $sheet->getStyle("{$startCol}{$currentRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1B4D72']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D6EAF8']],
            ]);
            $sheet->getCell("{$startCol}{$currentRow}")->setValue("📊 TIPE: " . strtoupper($tipe));
            $currentRow++;
            
            foreach ($jabatans as $jabatan => $routes) {
                $sheet->mergeCells("{$startCol}{$currentRow}:L{$currentRow}");
                $sheet->getStyle("{$startCol}{$currentRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '2874A6']],
                ]);
                $sheet->getCell("{$startCol}{$currentRow}")->setValue("   👔 Jabatan: {$jabatan}");
                $currentRow++;
                
                foreach ($routes as $route) {
                    $sheet->mergeCells("{$startCol}{$currentRow}:L{$currentRow}");
                    $sheet->getStyle("{$startCol}{$currentRow}")->applyFromArray([
                        'font' => ['size' => 9, 'color' => ['rgb' => '555555']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                    ]);
                    $sheet->getCell("{$startCol}{$currentRow}")->setValue("      • {$route}");
                    $currentRow++;
                }
                
                $currentRow++;
            }
            
            $currentRow++;
        }
        
        $infoRow = $currentRow + 1;
        $sheet->mergeCells("{$startCol}{$infoRow}:L{$infoRow}");
        $sheet->getStyle("{$startCol}{$infoRow}")->applyFromArray([
            'font' => ['italic' => true, 'size' => 8, 'color' => ['rgb' => '999999']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);
        $sheet->getCell("{$startCol}{$infoRow}")->setValue("💡 Tips: Copy-paste nilai Assistant Route dari referensi di samping");
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_TEXT,
        ];
    }
}