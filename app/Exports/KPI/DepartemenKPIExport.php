<?php

namespace App\Exports\KPI;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class DepartemenKPIExport
{
    private $data;
    private $namaDivisi;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->namaDivisi = $data['nama_divisi'] ?? 'Divisi';
    }

    public function generate()
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(10);

        $C = [
            'HDR' => '1E3A8A', 'SUB' => '3B82F6', 'GRN' => '059669', 
            'RED' => 'DC2626', 'YEL' => 'D97706', 'GRY' => '6B7280', 
            'WH'  => 'FFFFFF', 'ODD' => 'EFF6FF', 'BDR' => 'CBD5E1', 'IND' => '4F46E5'
        ];

        $this->buildSheet1ExecutiveDashboard($spreadsheet, $C);
        $this->buildSheet2TeamStatsAndPerformance($spreadsheet, $C);
        $this->buildSheet3PredictionAndDetails($spreadsheet, $C);

        $spreadsheet->setActiveSheetIndex(0);
        
        $tmpPath = tempnam(sys_get_temp_dir(), 'kpi_dept_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->setIncludeCharts(false);
        $writer->save($tmpPath);

        return $tmpPath;
    }

    // ==========================================
    // SHEET 1: DASHBOARD EKSEKUTIF DIVISI
    // ==========================================
    private function buildSheet1ExecutiveDashboard($spreadsheet, $C)
    {
        $s = $spreadsheet->getActiveSheet()->setTitle('Dashboard Divisi');
        $row = 1;

        // 1. Header
        $s->setCellValue('A' . $row, 'DASHBOARD EKSEKUTIF PERFORMA DIVISI');
        $s->mergeCells('A' . $row . ':E' . $row);
        $s->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14)->setColor(new Color($C['HDR']));
        $s->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $s->getRowDimension($row)->setRowHeight(30);
        $row++;

        $s->setCellValue('A' . $row, 'Ringkasan Kinerja Keseluruhan Departemen');
        $s->mergeCells('A' . $row . ':E' . $row);
        $s->getStyle('A' . $row)->getFont()->setItalic(true)->setColor(new Color($C['GRY']));
        $s->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row += 2;

        // 2. Metric Cards
        $metrics = [
            ['A', 'Total Target', $this->data['total_target'] ?? 0, $C['SUB']],
            ['C', 'Rata-rata Progress', ($this->data['rata_rata_progress'] ?? 0) . '%', $C['HDR']],
            ['E', 'Target Selesai', $this->data['kpi_selesai'] ?? 0, $C['GRN']],
        ];
        // Note: Adjusted to 5 columns (A, C, E) to make room for vertical monthly trend later

        foreach ($metrics as [$col, $label, $value, $color]) {
            $s->setCellValue($col . $row, $label);
            $s->mergeCells("{$col}{$row}:{$col}" . ($row + 1));
            $s->getStyle("{$col}{$row}")->applyFromArray([
                'font' => ['size' => 9, 'color' => ['rgb' => $C['WH']], 'bold' => true],
                'fill' => ['type' => Fill::FILL_SOLID, 'color' => ['rgb' => $color]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
            ]);
            $s->getStyle("{$col}" . ($row + 1))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($color);
            $s->setCellValue($col . ($row + 2), $value);
            $s->getStyle($col . ($row + 2))->applyFromArray([
                'font' => ['bold' => true, 'size' => 18, 'color' => ['rgb' => $C['HDR']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['outline' => ['style' => Border::BORDER_THIN, 'color' => ['rgb' => $C['BDR']]]]
            ]);
            $s->getRowDimension($row + 2)->setRowHeight(36);
        }
        $row += 4;

        // 3. Distribusi Grade
        $s->setCellValue('A' . $row, 'DISTRIBUSI GRADE KINERJA TARGET (Rata-rata)');
        $s->mergeCells('A' . $row . ':D' . $row);
        $s->getStyle('A' . $row)->getFont()->setBold(true)->setSize(11)->setColor(new Color($C['HDR']));
        $row++;

        $headers = ['Grade', 'Jumlah Target', 'Persentase', 'Visualisasi'];
        $cols = ['A', 'B', 'C', 'D'];
        foreach ($headers as $i => $h) {
            $s->setCellValue($cols[$i] . $row, $h);
            $s->getStyle($cols[$i] . $row)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => $C['WH']]],
                'fill' => ['type' => Fill::FILL_SOLID, 'color' => ['rgb' => $C['HDR']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
        }
        $row++;

        $totalTargets = max(1, $this->data['total_target'] ?? 1);
        $gradeColors = ['Sangat Baik' => $C['GRN'], 'Baik' => $C['SUB'], 'Cukup' => $C['YEL'], 'Kurang' => $C['RED'], 'Sangat Kurang' => $C['GRY']];
        $distribusi = $this->data['distribusi_nilai'] ?? [];
        $startRow = $row;

        foreach ($distribusi as $grade => $count) {
            $pct = round(($count / $totalTargets) * 100, 1);
            $s->setCellValue("A{$row}", $grade);
            $s->setCellValue("B{$row}", $count);
            $s->setCellValue("C{$row}", $pct . '%');
            $blocks = max(0, min(20, (int)($pct / 5)));
            $s->setCellValue("D{$row}", str_repeat('█', $blocks) . str_repeat('░', 20 - $blocks));

            $s->getStyle("A{$row}")->getFont()->setBold(true)->setColor(new Color($gradeColors[$grade] ?? $C['GRY']));
            $s->getStyle("B{$row}:C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $s->getStyle("D{$row}")->getFont()->getColor()->setRGB($gradeColors[$grade] ?? $C['GRY']);

            if (($row - $startRow) % 2 === 0) {
                $s->getStyle("A{$row}:D{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($C['ODD']);
            }
            $row++;
        }

        // 4. Tren Perjalanan Bulanan DIVISI (VERTIKAL - FIX UTAMA)
        $row += 2;
        $s->setCellValue('A' . $row, 'TREN PERJALANAN BULANAN DIVISI (Rata-rata Progress Tim)');
        $s->mergeCells('A' . $row . ':E' . $row);
        $s->getStyle('A' . $row)->getFont()->setBold(true)->setSize(11)->setColor(new Color($C['HDR']));
        $row++;

        $mHeaders = ['Bulan', 'Rata-rata Progress (%)', 'Status', 'Momentum (Δ)', 'Visualisasi'];
        $mCols = ['A', 'B', 'C', 'D', 'E'];
        foreach ($mHeaders as $i => $h) {
            $s->setCellValue($mCols[$i] . $row, $h);
            $s->getStyle($mCols[$i] . $row)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => $C['WH']]],
                'fill' => ['type' => Fill::FILL_SOLID, 'color' => ['rgb' => $C['SUB']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
        }
        $row++;

        $monthlyData = $this->data['monthly_progress'] ?? [];
        $monthNames = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $prevVal = null;

        for ($m = 1; $m <= 12; $m++) {
            $val = $monthlyData[$m] ?? null;
            $color = $val >= 80 ? $C['GRN'] : ($val >= 60 ? $C['YEL'] : ($val !== null ? $C['RED'] : $C['GRY']));
            $width = $val !== null ? $val : 0;
            $displayVal = $val !== null ? $val . '%' : '-';
            
            $status = $val === null ? '-' : ($val >= 80 ? 'Sangat Baik' : ($val >= 60 ? 'Cukup' : 'Perhatian'));
            $sColor = $val === null ? $C['GRY'] : ($val >= 80 ? $C['GRN'] : ($val >= 60 ? $C['YEL'] : $C['RED']));

            if ($val === null || $prevVal === null) {
                $deltaDisplay = '-';
                $dColor = $C['GRY'];
            } else {
                $delta = $val - $prevVal;
                $sign = $delta >= 0 ? '+' : '';
                $deltaDisplay = $sign . round($delta, 1) . '%';
                $dColor = $delta > 0 ? $C['GRN'] : ($delta < 0 ? $C['RED'] : $C['GRY']);
            }
            if ($val !== null) $prevVal = $val;

            $s->setCellValue("A{$row}", $monthNames[$m]);
            $s->setCellValue("B{$row}", $displayVal);
            $s->setCellValue("C{$row}", $status);
            $s->setCellValue("D{$row}", $deltaDisplay);
            $blocks = max(0, min(20, (int)($width / 5)));
            $s->setCellValue("E{$row}", str_repeat('█', $blocks) . str_repeat('░', 20 - $blocks));

            $s->getStyle("A{$row}")->getFont()->setBold(true);
            $s->getStyle("B{$row}")->getFont()->setBold(true)->getColor()->setRGB($color);
            $s->getStyle("C{$row}")->getFont()->getColor()->setRGB($sColor);
            $s->getStyle("C{$row}")->getFont()->setItalic(true);
            $s->getStyle("D{$row}")->getFont()->setBold(true)->getColor()->setRGB($dColor);
            $s->getStyle("E{$row}")->getFont()->getColor()->setRGB($color);

            $range = "A{$row}:E{$row}";
            $s->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            if ($m % 2 === 0) {
                $s->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($C['ODD']);
            }
            $row++;
        }

        // Column Widths Sheet 1
        $s->getColumnDimension('A')->setWidth(15);
        $s->getColumnDimension('B')->setWidth(18);
        $s->getColumnDimension('C')->setWidth(15);
        $s->getColumnDimension('D')->setWidth(15);
        $s->getColumnDimension('E')->setWidth(30);
    }

    // ==========================================
    // SHEET 2: STATISTIK & PERFORMA TIM
    // ==========================================
    private function buildSheet2TeamStatsAndPerformance($spreadsheet, $C)
    {
        $s = $spreadsheet->createSheet()->setTitle('Statistik & Performa Tim');
        $row = 1;

        $s->setCellValue('A' . $row, 'ANALISIS MENDALAM PERFORMA ANGGOTA DIVISI');
        $s->mergeCells('A' . $row . ':H' . $row);
        $s->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14)->setColor(new Color($C['HDR']));
        $s->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row += 2;

        // Statistik Komposisi Tim (BARU - Sangat berguna untuk rapat)
        $karyawan = collect($this->data['karyawan_departemen'] ?? [])->sortByDesc('rata_rata_progress')->values()->toArray();
        $totalKaryawan = count($karyawan);
        $onTrack = collect($karyawan)->where('rata_rata_progress', '>=', 80)->count();
        $needPush = collect($karyawan)->filter(fn($k) => $k['rata_rata_progress'] >= 60 && $k['rata_rata_progress'] < 80)->count();
        $danger = collect($karyawan)->where('rata_rata_progress', '<', 60)->count();

        $s->setCellValue('A' . $row, 'KOMPOSISI PERFORMA TIM (BERDASARKAN RATA-RATA)');
        $s->mergeCells('A' . $row . ':D' . $row);
        $s->getStyle('A' . $row)->getFont()->setBold(true)->setSize(11)->setColor(new Color($C['HDR']));
        $row++;

        $compData = [
            ['On Track (≥ 80%)', $onTrack, $totalKaryawan > 0 ? round(($onTrack/$totalKaryawan)*100, 1) : 0, $C['GRN']],
            ['Perlu Dorongan (60-79%)', $needPush, $totalKaryawan > 0 ? round(($needPush/$totalKaryawan)*100, 1) : 0, $C['YEL']],
            ['Berbahaya (< 60%)', $danger, $totalKaryawan > 0 ? round(($danger/$totalKaryawan)*100, 1) : 0, $C['RED']],
        ];

        $cHeaders = ['Kategori', 'Jumlah Karyawan', 'Persentase', 'Indikator'];
        $cCols = ['A', 'B', 'C', 'D'];
        foreach ($cHeaders as $i => $h) {
            $s->setCellValue($cCols[$i] . $row, $h);
            $s->getStyle($cCols[$i] . $row)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => $C['WH']]],
                'fill' => ['type' => Fill::FILL_SOLID, 'color' => ['rgb' => $C['HDR']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
        }
        $row++;

        foreach ($compData as $i => $c) {
            $s->setCellValue("A{$row}", $c[0]);
            $s->setCellValue("B{$row}", $c[1] . " Orang");
            $s->setCellValue("C{$row}", $c[2] . "%");
            $blocks = max(0, min(20, (int)($c[2] / 5)));
            $s->setCellValue("D{$row}", str_repeat('█', $blocks) . str_repeat('░', 20 - $blocks));
            
            $s->getStyle("A{$row}")->getFont()->setBold(true)->setColor(new Color($c[3]));
            $s->getStyle("B{$row}:C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $s->getStyle("D{$row}")->getFont()->getColor()->setRGB($c[3]);
            
            $range = "A{$row}:D{$row}";
            $s->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            if ($i % 2 === 0) $s->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($C['ODD']);
            $row++;
        }

        $row += 2;
        // Tabel Performa Individu
        $s->setCellValue('A' . $row, 'RANKING PERFORMA INDIVIDU');
        $s->mergeCells('A' . $row . ':H' . $row);
        $s->getStyle('A' . $row)->getFont()->setBold(true)->setSize(11)->setColor(new Color($C['HDR']));
        $row++;

        $tHeaders = ['Rank', 'Nama Karyawan', 'Jabatan', 'Total KPI', 'Rata-rata Progress', 'Selesai', 'Gagal', 'Status'];
        $tCols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        foreach ($tHeaders as $i => $h) {
            $s->setCellValue($tCols[$i] . $row, $h);
            $s->getStyle($tCols[$i] . $row)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => $C['WH']]],
                'fill' => ['type' => Fill::FILL_SOLID, 'color' => ['rgb' => $C['SUB']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
        }
        $row++;

        foreach ($karyawan as $i => $k) {
            $prog = $k['rata_rata_progress'];
            $pColor = $prog >= 80 ? $C['GRN'] : ($prog >= 60 ? $C['YEL'] : $C['RED']);
            $statusText = $prog >= 80 ? 'On Track' : ($prog >= 60 ? 'Perlu Dorongan' : 'Berbahaya');

            $s->setCellValue("A{$row}", $i + 1);
            $s->setCellValue("B{$row}", $k['nama']);
            $s->setCellValue("C{$row}", $k['jabatan']);
            $s->setCellValue("D{$row}", $k['jumlah_target']);
            $s->setCellValue("E{$row}", $prog . '%');
            $s->setCellValue("F{$row}", $k['total_target_selesai']);
            $s->setCellValue("G{$row}", $k['total_target_gagal']);
            $s->setCellValue("H{$row}", $statusText);

            $range = "A{$row}:H{$row}";
            $s->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            if ($i % 2 === 0) $s->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($C['ODD']);

            $s->getStyle("E{$row}")->getFont()->setBold(true)->getColor()->setRGB($pColor);
            $s->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $s->getStyle("H{$row}")->getFont()->setBold(true)->getColor()->setRGB($pColor);
            $s->getStyle("H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        }

        // Column Widths Sheet 2
        $s->getColumnDimension('A')->setWidth(6);
        $s->getColumnDimension('B')->setWidth(25);
        $s->getColumnDimension('C')->setWidth(20);
        $s->getColumnDimension('D')->setWidth(10);
        $s->getColumnDimension('E')->setWidth(18);
        $s->getColumnDimension('F')->setWidth(10);
        $s->getColumnDimension('G')->setWidth(10);
        $s->getColumnDimension('H')->setWidth(18);
    }

    // ==========================================
    // SHEET 3: PREDIKSI, POTENSI & DETAIL TARGET
    // ==========================================
    private function buildSheet3PredictionAndDetails($spreadsheet, $C)
    {
        $s = $spreadsheet->createSheet()->setTitle('Prediksi & Detail Target');
        $row = 1;

        $s->setCellValue('A' . $row, 'ANALISIS PREDIKSI, POTENSI & DETAIL TARGET DIVISI');
        $s->mergeCells('A' . $row . ':F' . $row);
        $s->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14)->setColor(new Color($C['HDR']));
        $s->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row += 2;

        // --- PREPARE DATA ---
        $avgProg = $this->data['rata_rata_progress'] ?? 0;
        $kpiGagal = $this->data['kpi_gagal'] ?? 0;
        $totalTarget = max(1, $this->data['total_target'] ?? 1);
        $failRate = round(($kpiGagal / $totalTarget) * 100, 1);
        $estimasiAkhir = min(100, round($avgProg * ($avgProg >= 80 ? 1.15 : ($avgProg >= 60 ? 1.10 : 1.05)), 1));

        // --- GENERATE DYNAMIC NARRATIVE USING GRANULAR HELPER ---
        $trajText = $this->getGranularNarrative($avgProg, 'trajectory', $this->namaDivisi, $estimasiAkhir, $failRate);
        $potText  = $this->getGranularNarrative($avgProg, 'potential', $this->namaDivisi, $estimasiAkhir, $failRate);
        $riskText = $this->getGranularNarrative($avgProg, 'risk', $this->namaDivisi, $estimasiAkhir, $failRate);
        $outText  = $this->getGranularNarrative($avgProg, 'strategy', $this->namaDivisi, $estimasiAkhir, $failRate);

        // --- RENDER NARRATIVE BLOCKS ---
        $narratives = [
            ['title' => '📊 TRAJECTORY PREDICTION (PREDIKSI LINTASAN)', 'text' => $trajText, 'color' => $C['SUB'], 'bg' => 'F0F9FF'],
            ['title' => '🚀 GROWTH POTENTIAL (POTENSI PERTUMBUHAN)', 'text' => $potText, 'color' => $C['GRN'], 'bg' => 'F0FDF4'],
            ['title' => '⚠️ RISK FORECAST (PRAKIRAAN RISIKO)', 'text' => $riskText, 'color' => $C['RED'], 'bg' => 'FEF2F2'],
            ['title' => '🔭 STRATEGIC OUTLOOK (PANDANGAN STRATEGIS)', 'text' => $outText, 'color' => $C['IND'], 'bg' => 'EEF2FF'],
        ];

        foreach ($narratives as $n) {
            $s->setCellValue('A' . $row, $n['title']);
            $s->mergeCells('A' . $row . ':F' . $row);
            $s->getStyle('A' . $row)->getFont()->setBold(true)->setSize(10)->setColor(new Color($n['color']));
            $s->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($n['bg']);
            $row++;

            $s->setCellValue('A' . $row, $n['text']);
            $s->mergeCells('A' . $row . ':F' . $row);
            $s->getStyle('A' . $row)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
            $s->getRowDimension($row)->setRowHeight(60); 
            $s->getStyle('A' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $s->getStyle('A' . $row)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THICK)->getColor()->setRGB($n['color']);
            $row += 2;
        }

        // --- METRIK PREDIKSI KUANTITATIF ---
        $row -= 1; 
        $s->setCellValue('A' . $row, 'METRIK PREDIKSI KUANTITATIF');
        $s->mergeCells('A' . $row . ':B' . $row);
        $s->getStyle('A' . $row)->getFont()->setBold(true)->setSize(11)->setColor(new Color($C['HDR']));
        $row++;

        $probabilitas = $avgProg >= 85 ? 'SANGAT TINGGI' : ($avgProg >= 70 ? 'TINGGI' : ($avgProg >= 50 ? 'SEDANG' : 'RENDAH'));
        $predData = [
            ['Rata-rata Progress Saat Ini', $avgProg . '%'],
            ['Estimasi Pencapaian Akhir Tahun', $estimasiAkhir . '%'],
            ['Tingkat Risiko Kegagalan', $kpiGagal . ' Target (' . $failRate . '%)'],
            ['Probabilitas Mencapai Target Divisi', $probabilitas],
        ];

        foreach ($predData as $p) {
            $s->setCellValue("A{$row}", $p[0]);
            $s->setCellValue("B{$row}", $p[1]);
            $s->getStyle("A{$row}")->getFont()->setBold(true);
            $s->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $s->getStyle("A{$row}:B{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $row++;
        }
        $row += 2;

        // --- DETAIL TARGET DIVISI ---
        $s->setCellValue('A' . $row, 'DAFTAR LENGKAP TARGET KPI DIVISI (RATA-RATA)');
        $s->mergeCells('A' . $row . ':F' . $row);
        $s->getStyle('A' . $row)->getFont()->setBold(true)->setSize(11)->setColor(new Color($C['HDR']));
        $row++;

        $tHeaders = ['No', 'Judul Target', 'Periode', 'Rata-rata Progress', 'Status', 'Kategori'];
        $tCols = ['A', 'B', 'C', 'D', 'E', 'F'];
        foreach ($tHeaders as $i => $h) {
            $s->setCellValue($tCols[$i] . $row, $h);
            $s->getStyle($tCols[$i] . $row)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => $C['WH']]],
                'fill' => ['type' => Fill::FILL_SOLID, 'color' => ['rgb' => $C['SUB']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
        }
        $row++;

        $targets = $this->data['daftar_target_kpi'] ?? [];
        foreach ($targets as $i => $t) {
            $prog = $t['progress'] ?? 0;
            $pColor = $prog >= 80 ? $C['GRN'] : ($prog >= 60 ? $C['YEL'] : $C['RED']);
            $sStatus = strtolower($t['status'] ?? '');
            $sColor = str_contains($sStatus, 'selesai') ? $C['GRN'] : (str_contains($sStatus, 'gagal') ? $C['RED'] : $C['YEL']);
            $kategori = $prog >= 100 ? 'Sangat Baik' : ($prog >= 80 ? 'Baik' : ($prog >= 70 ? 'Cukup' : ($prog >= 60 ? 'Kurang' : 'Sangat Kurang')));

            $s->setCellValue("A{$row}", $i + 1);
            $s->setCellValue("B{$row}", $t['judul']);
            $s->setCellValue("C{$row}", $t['periode']);
            $s->setCellValue("D{$row}", $prog . '%');
            $s->setCellValue("E{$row}", $t['status']);
            $s->setCellValue("F{$row}", $kategori);

            $range = "A{$row}:F{$row}";
            $s->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            if ($i % 2 === 0) $s->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($C['ODD']);

            $s->getStyle("B{$row}")->getAlignment()->setWrapText(true);
            $s->getStyle("D{$row}")->getFont()->setBold(true)->getColor()->setRGB($pColor);
            $s->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $s->getStyle("E{$row}")->getFont()->setBold(true)->getColor()->setRGB($sColor);
            $s->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $s->getStyle("F{$row}")->getFont()->setBold(true)->getColor()->setRGB($pColor);
            $s->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        }

        // Column Widths Sheet 3
        $s->getColumnDimension('A')->setWidth(5);
        $s->getColumnDimension('B')->setWidth(45);
        $s->getColumnDimension('C')->setWidth(15);
        $s->getColumnDimension('D')->setWidth(18);
        $s->getColumnDimension('E')->setWidth(15);
        $s->getColumnDimension('F')->setWidth(15);
    }

    // ==========================================
    // HELPER: GRANULAR NARRATIVE GENERATOR (2% Interval, 20% Zone)
    // ==========================================
    private function getGranularNarrative($p, $type, $namaDivisi, $estimasiAkhir, $failRate)
    {
        // 1. Tentukan Zona (Interval 20%)
        if ($p < 30) $zona = "ZONA BAHAYA KRITIS";
        elseif ($p < 50) $zona = "ZONA WASPADA";
        elseif ($p < 70) $zona = "ZONA TRANSISI";
        elseif ($p < 90) $zona = "ZONA PERTUMBUHAN";
        else $zona = "ZONA EXCELLENCE";

        // 2. Tentukan Narasi Granular (Interval 2% mulai dari 10%)
        $msg = "";
        if ($p < 12) $msg = "memerlukan intervensi darurat dan evaluasi total terhadap kelayakan target. Fokus absolut minggu ini hanya pada penyelamatan 1 target paling kritis.";
        elseif ($p < 14) $msg = "memerlukan eskalasi segera ke level manajemen. Identifikasi akar masalah utama dan ajukan realokasi sumber daya.";
        elseif ($p < 16) $msg = "menunjukkan kinerja yang sangat jauh di bawah ekspektasi. Lakukan daily check-in dengan atasan dan susun action plan harian.";
        elseif ($p < 18) $msg = "berisiko tinggi mengalami kegagalan total. Prioritaskan hanya target dengan dampak bisnis tertinggi.";
        elseif ($p < 20) $msg = "membutuhkan turnaround strategy yang radikal. Evaluasi ulang apakah target yang ditetapkan realistis.";
        elseif ($p < 22) $msg = "masih sangat rentan terhadap kegagalan. Tingkatkan intensitas kerja minimal 20% dan manfaatkan dukungan tim.";
        elseif ($p < 24) $msg = "memerlukan akselerasi minimal 5-7% per bulan. Identifikasi 2 target quick wins untuk membangun momentum.";
        elseif ($p < 26) $msg = "menunjukkan progress yang lambat. Terapkan teknik time-blocking dan hilangkan semua distraksi.";
        elseif ($p < 28) $msg = "berada di ambang batas bawah yang mengkhawatirkan. Minta feedback konstruktif dari atasan mengenai area perbaikan.";
        elseif ($p < 30) $msg = "mulai menunjukkan sedikit perbaikan namun masih jauh dari zona aman. Pertahankan ritme percepatan ini.";
        elseif ($p < 32) $msg = "memerlukan perhatian serius agar tidak tergelincir kembali. Fokus pada konsistensi harian tanpa hari tanpa progress.";
        elseif ($p < 34) $msg = "menunjukkan tanda-tanda stabilisasi namun akselerasi masih kurang. Tantang diri menyelesaikan 1 target tertunda.";
        elseif ($p < 36) $msg = "berada di fase kritis menuju zona transisi. Optimalkan penggunaan tools untuk mempercepat tugas repetitif.";
        elseif ($p < 38) $msg = "membutuhkan dorongan ekstra untuk menembus batas 40%. Lakukan review mingguan yang ketat.";
        elseif ($p < 40) $msg = "sudah mulai menunjukkan pola kerja yang lebih terarah. Mulai delegasikan tugas-tugas minor.";
        elseif ($p < 42) $msg = "mendekati titik tengah, namun masih memerlukan kewaspadaan tinggi. Susun strategi catch-up untuk 2 bulan ke depan.";
        elseif ($p < 44) $msg = "menunjukkan konsistensi yang mulai terbentuk. Dokumentasikan setiap keberhasilan kecil sebagai motivasi.";
        elseif ($p < 46) $msg = "hampir mencapai zona transisi yang lebih aman. Tingkatkan kolaborasi dengan rekan tim yang progressnya lebih baik.";
        elseif ($p < 48) $msg = "berada di ujung zona peringatan, siap untuk lompatan performa. Fokus pada penyelesaian total 1-2 target.";
        elseif ($p < 50) $msg = "telah mencapai titik tengah perjalanan. Momentum mulai terbentuk. Rayakan pencapaian ini lalu reset fokus.";
        elseif ($p < 52) $msg = "memasuki fase yang lebih stabil dengan risiko kegagalan yang mulai menurun. Pertahankan disiplin kerja.";
        elseif ($p < 54) $msg = "menunjukkan perbaikan yang nyata dan dapat diandalkan. Mulai antisipasi hambatan di 2 target berikutnya.";
        elseif ($p < 56) $msg = "berada di jalur yang tepat untuk mencapai target akhir tahun. Tingkatkan kualitas output, bukan hanya kecepatan.";
        elseif ($p < 58) $msg = "mendekati zona aman dengan performa yang semakin solid. Mulai berbagi tips efisiensi kepada rekan tim.";
        elseif ($p < 60) $msg = "hampir mencapai batas aman. Dorong diri untuk menyelesaikan 1 target besar minggu ini sebagai bukti kapabilitas.";
        elseif ($p < 62) $msg = "telah memasuki fase performa yang baik. Fokus pada optimalisasi proses untuk mencapai target dengan usaha lebih sedikit.";
        elseif ($p < 64) $msg = "menunjukkan konsistensi tinggi dan manajemen waktu yang efektif. Ajukan diri untuk membantu tim lain.";
        elseif ($p < 66) $msg = "berada di jalur yang sangat sehat. Lakukan self-assessment: skill apa yang perlu ditingkatkan untuk mempercepat progress?";
        elseif ($p < 68) $msg = "menunjukkan potensi untuk menjadi top performer. Dokumentasikan workflow Anda yang sukses untuk bahan mentoring.";
        elseif ($p < 70) $msg = "mendekati zona sangat baik dengan momentum positif yang kuat. Jangan puas, tantang diri menembus batas 75%.";
        elseif ($p < 72) $msg = "telah membuktikan kapabilitas dengan hasil yang konsisten. Mulai bangun networking internal untuk memahami best practice.";
        elseif ($p < 74) $msg = "berada di posisi yang sangat menguntungkan. Fokus pada penyelesaian target yang memiliki bobot nilai terbesar.";
        elseif ($p < 76) $msg = "menunjukkan kematangan dalam mengelola beban kerja. Berikan apresiasi kepada diri sendiri dan tim atas kerja keras.";
        elseif ($p < 78) $msg = "hampir mencapai zona excellent. Siapkan presentasi atau laporan ringkas tentang keberhasilan Anda untuk review kinerja.";
        elseif ($p < 80) $msg = "berada di ambang batas performa tinggi. Identifikasi 1 stretch goal yang bisa Anda selesaikan di luar target wajib.";
        elseif ($p < 82) $msg = "telah memasuki kategori performa tinggi yang menjadi benchmark divisi. Mulai pikirkan tentang pengembangan karir.";
        elseif ($p < 84) $msg = "menunjukkan penguasaan (mastery) yang sangat baik. Jadilah mentor informal bagi rekan yang sedang kesulitan.";
        elseif ($p < 86) $msg = "berada di jalur yang sangat mulus menuju 90%+. Eksplorasi inovasi atau otomatisasi untuk meningkatkan efisiensi tim.";
        elseif ($p < 88) $msg = "menunjukkan konsistensi tingkat atas yang sangat jarang ditemukan. Pastikan semua dokumentasi sudah rapi.";
        elseif ($p < 90) $msg = "hampir mencapai kesempurnaan dalam eksekusi. Mulai susun rencana strategis untuk periode berikutnya.";
        elseif ($p < 92) $msg = "berada di puncak performa dengan proyeksi akhir tahun yang sangat cerah. Fokus pada penyempurnaan detail terakhir.";
        elseif ($p < 94) $msg = "menunjukkan dedikasi dan eksekusi yang luar biasa. Komunikasikan pencapaian ini kepada stakeholder terkait.";
        elseif ($p < 96) $msg = "hampir mencapai garis finish dengan hasil yang fantastis. Pastikan validasi data dengan atasan berjalan lancar.";
        elseif ($p < 98) $msg = "berada di langkah terakhir menuju pencapaian sempurna. Selesaikan semua outstanding item sekecil apapun.";
        else $msg = "telah mencapai atau melampaui ekspektasi dengan hasil yang luar biasa. Fokus beralih pada evaluasi pasca-proyek dan lessons learned.";

        // 3. Assemble berdasarkan tipe narasi
        if ($type === 'trajectory') {
            return "Berdasarkan analisis tren performa, divisi {$namaDivisi} berada di {$zona} ({$p}%). Proyeksi akhir tahun diperkirakan mencapai {$estimasiAkhir}%. Kondisi ini {$msg}";
        } elseif ($type === 'potential') {
            return "Divisi ini memiliki potensi pertumbuhan yang {$msg} Dengan fondasi yang ada, akselerasi menuju target 100% sangat mungkin jika strategi ini dieksekusi dengan disiplin tinggi.";
        } elseif ($type === 'risk') {
            return "Analisis risiko menunjukkan bahwa berada di {$zona} ({$p}%) {$msg} Tingkat kegagalan saat ini adalah {$failRate}%, yang memerlukan mitigasi proaktif sesuai pesan di atas.";
        } elseif ($type === 'strategy') {
            return "Rekomendasi strategis utama: {$msg} Selain itu, pastikan komunikasi dengan stakeholder tetap transparan dan lakukan penyesuaian sumber daya jika diperlukan.";
        }
        return "";
    }
    // ==========================================
    // HELPER
    // ==========================================
    private function numToCol($num)
    {
        $col = '';
        while ($num > 0) {
            $mod = ($num - 1) % 26;
            $col = chr(65 + $mod) . $col;
            $num = (int)(($num - 1) / 26);
        }
        return $col;
    }
}