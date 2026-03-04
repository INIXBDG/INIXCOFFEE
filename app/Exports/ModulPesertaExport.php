<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithDefaultStyles; // 1. Tambah Import Ini
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Style; // 2. Tambah Import Ini
use Maatwebsite\Excel\Concerns\WithDrawings; // <--- 1. Import ini
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

// 3. Tambahkan 'WithDefaultStyles' ke implements
class ModulPesertaExport implements FromView, WithStyles, WithColumnWidths, WithDefaultStyles, WithDrawings
{
    protected $no;
    protected $peserta;
    protected $ttd;

    public function __construct($no, $peserta, $ttd)
    {
        $this->no = $no;
        $this->peserta = $peserta;
        $this->ttd = $ttd;
    }

    public function view(): View
    {
        return view('office.modul.excelPeserta', [
            'no' => $this->no,
            'peserta' => $this->peserta,
            'ttd' => $this->ttd
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6.29,
            'B' => 30.57,
            'C' => 35.71,
            'D' => 18.43,
            'E' => 24.43,
            'F' => 35.71,
        ];
    }

    public function defaultStyles(Style $defaultStyle)
    {
        return [
            'font' => [
                'name' => 'Arial',
                'size' => 10,
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A:F')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A:F')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        return []; // Jangan kembalikan array 'font' di sini
    }

    public function drawings()
    {
        $drawings = [];

        /** ================= LOGO ================= */
        $logo = new Drawing();
        $logo->setName('Logo');
        $logo->setDescription('Logo Inixindo');
        $logo->setPath(public_path('assets/img/logo.png'));
        $logo->setHeight(70);
        $logo->setCoordinates('A5');
        $logo->setOffsetX(5);
        $logo->setOffsetY(0);
        $drawings[] = $logo;

        /** ================= HITUNG FOOTER ================= */
        $headerRow   = 10; // sesuaikan
        $dataCount   = $this->peserta->count();
        $footerStart = $headerRow + $dataCount + 3;

        /** ================= CAP (BACKGROUND) ================= */
        $cap = new Drawing();
        $cap->setName('Cap');
        $cap->setPath(public_path('assets/img/bg-signs.png'));
        $cap->setHeight(90);
        $cap->setCoordinates('C' . ($footerStart + 1));
        $cap->setOffsetX(55);
        $cap->setOffsetY(120);
        $drawings[] = $cap;

        /** ================= TTD ================= */
        if (!empty($this->ttd->ttd)) {
            $ttd = new Drawing();
            $ttd->setName('TTD');
            $ttd->setPath(public_path('storage/ttd/' . $this->ttd->ttd));
            $ttd->setHeight(110);
            $ttd->setCoordinates('C' . ($footerStart + 2));
            $ttd->setOffsetX(70);
            $ttd->setOffsetY(45);
            $drawings[] = $ttd;
        }

        return $drawings;
    }
}
