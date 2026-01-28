<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class RKMExcelAdmsales implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $data;
    protected $rowsWithStatus = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $rows = [];
        $rowIndex = 2;

        foreach ($this->data as $item) {
            $idList = array_filter(array_map('trim', explode(', ', $item['id_all'] ?? '')));

            $perusahaanAll = array_filter(array_map('trim', explode(', ', $item['perusahaan_all'] ?? '')));
            $salesAll      = array_filter(array_map('trim', explode(', ', $item['sales_all'] ?? '')));

            // Instruktur utama (hanya 1)
            $instruktur = trim($item['instruktur_nama'] ?? '');

            // Instruktur kedua
            $instruktur2 = trim($item['instruktur2_nama'] ?? '');

            // Asisten
            $asisten = trim($item['asisten_nama'] ?? '');

            $exam    = trim($item['exam'] ?? '');
            $makanan = trim($item['makanan'] ?? '');
            $status  = $item['status'] ?? '';

            $this->rowsWithStatus[$rowIndex] = $status;

            $rows[] = [
                'Materi'        => $item['nama_materi'] ?? '',
                'Perusahaan'    => implode(', ', $perusahaanAll),
                'Tanggal Awal'  => Carbon::parse($item['tanggal_awal'])->format('d/m/Y'),
                'Tanggal Akhir' => Carbon::parse($item['tanggal_akhir'])->format('d/m/Y'),
                'Exam'          => $exam === '1' ? 'Ya' : 'Tidak',
                'Metode Kelas'  => $item['metode_kelas'] ?? '',
                'Event'         => $item['event'] ?: 'Belum Ditentukan',
                'Ruang'         => $item['ruang'] ?: 'Belum Ditentukan',
                'Pax'           => $item['pax'] ?? 0,
                'Sales'         => implode(', ', $salesAll),
                'Instruktur'    => $instruktur,
                'Instruktur 2'  => $instruktur2,
                'Asisten'       => $asisten,
            ];

            $rowIndex++;
        }

        return new Collection($rows);
    }

    public function headings(): array
    {
        return [
            'Materi',
            'Perusahaan',
            'Tanggal Awal',
            'Tanggal Akhir',
            'Exam',
            'Metode Kelas',
            'Event',
            'Ruang',
            'Pax',
            'Sales',
            'Instruktur',
            'Instruktur 2',
            'Asisten',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastColumn = 'M'; // sekarang M karena ada 13 kolom

        // Warna background berdasarkan status
        foreach ($this->rowsWithStatus as $rowIndex => $status) {
            if ($status == '0') {
                $sheet->getStyle("A{$rowIndex}:{$lastColumn}{$rowIndex}")
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('C5172E');

                $sheet->getStyle("A{$rowIndex}:{$lastColumn}{$rowIndex}")
                    ->getFont()->getColor()->setARGB('FFFFFFFF');
            } elseif ($status == '1') {
                $sheet->getStyle("A{$rowIndex}:{$lastColumn}{$rowIndex}")
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('578FCA');

                $sheet->getStyle("A{$rowIndex}:{$lastColumn}{$rowIndex}")
                    ->getFont()->getColor()->setARGB('FFFFFFFF');
            } elseif ($status == '3') {
                $sheet->getStyle("A{$rowIndex}:{$lastColumn}{$rowIndex}")
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('2E8B57');

                $sheet->getStyle("A{$rowIndex}:{$lastColumn}{$rowIndex}")
                    ->getFont()->getColor()->setARGB('FFFFFFFF');
            } else {
                $sheet->getStyle("A{$rowIndex}:{$lastColumn}{$rowIndex}")
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF666666');

                $sheet->getStyle("A{$rowIndex}:{$lastColumn}{$rowIndex}")
                    ->getFont()->getColor()->setARGB('FFFFFFFF');
            }
        }

        // Tambah margin/padding di semua cell (data + header)
        $sheet->getStyle('A1:' . $lastColumn . $sheet->getHighestRow())
            ->getAlignment()
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT)
            ->setWrapText(true);

        $sheet->getStyle('A1:' . $lastColumn . $sheet->getHighestRow())
            ->getAlignment()
            ->setIndent(1); // indentasi 1 level = ~1 spasi

        // Padding tambahan dengan set custom padding (dalam pt)
        $styleArray = [
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'wrapText' => true,
            ],
        ];
        $sheet->getStyle('A1:' . $lastColumn . $sheet->getHighestRow())
            ->applyFromArray($styleArray);

        // Header tetap bold + background abu-abu
        $sheet->getStyle('A1:' . $lastColumn . '1')
            ->getFont()->setBold(true);

        $sheet->getStyle('A1:' . $lastColumn . '1')
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('D3D3D3');

        return [];
    }
}
