<?php

namespace App\Exports\KPI;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend as ChartLegend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title as ChartTitle;

class MonitoringKPIExport
{
    private $data;
    private $namaKaryawan;
    private $jabatan;
    private $tahun;
    
    private $monthlyProgress = [];
    private $dailyProgressPerMonth = [];
    private $statistics = [];
    private $regression = [];

    public function __construct(array $data, string $namaKaryawan, string $jabatan, int $tahun)
    {
        $this->data = $data;
        $this->namaKaryawan = $namaKaryawan;
        $this->jabatan = $jabatan;
        $this->tahun = $tahun;
        
        $this->computeMonthlyProgress();
        $this->computeStatistics();
        $this->computeRegression();
    }

    // ==========================================
    // ANALYTICS ENGINE
    // ==========================================

    private function computeMonthlyProgress()
    {
        $this->monthlyProgress = array_fill(1, 12, null);

        $monthlyData = $this->data['monthly_progress'] ?? $this->data['monthly_data'] ?? null;
        
        if ($monthlyData) {
            if ($monthlyData instanceof \Illuminate\Support\Collection) {
                $monthlyData = $monthlyData->toArray();
            }
            
            if (is_array($monthlyData)) {
                foreach ($monthlyData as $key => $item) {
                    if (is_numeric($key) && $key >= 1 && $key <= 12) {
                        $monthKey = (int) $key;
                        $itemArray = is_array($item) ? $item : (array) $item;
                        $val = $itemArray['value'] ?? $itemArray['avg'] ?? (is_scalar($item) ? $item : 0);
                        $this->monthlyProgress[$monthKey] = $val !== null ? (float) $val : null;
                    } 
                    elseif (is_array($item) || is_object($item)) {
                        $itemArray = (array) $item;
                        $m = $itemArray['month'] ?? $itemArray['bulan'] ?? null;
                        $v = $itemArray['value'] ?? $itemArray['avg'] ?? $itemArray['progress'] ?? null;
                        
                        if ($m !== null && $v !== null) {
                            $monthKey = is_numeric($m) ? (int) $m : (int) substr($m, 5, 2);
                            if ($monthKey >= 1 && $monthKey <= 12) {
                                $this->monthlyProgress[$monthKey] = (float) $v;
                            }
                        }
                    }
                }
            }
        }

        $hasData = false;
        foreach ($this->monthlyProgress as $val) {
            if ($val !== null) { $hasData = true; break; }
        }

        if (!$hasData) {
            $monthly = array_fill(1, 12, []);
            $targets = $this->data['daftar_target_pribadi'] ?? [];

            if ($targets instanceof \Illuminate\Support\Collection) {
                $targets = $targets->toArray();
            }

            foreach ($targets as $target) {
                $dateStr = $target['updated_at'] ?? $target['created_at'] ?? null;
                if (!$dateStr) continue;

                try {
                    $date = \Carbon\Carbon::parse($dateStr);
                    if ($date->year == $this->tahun) {
                        $monthly[$date->month][] = (float)($target['progress_percent'] ?? 0);
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            foreach ($monthly as $month => $values) {
                $this->monthlyProgress[$month] = !empty($values) 
                    ? round(array_sum($values) / count($values), 2) 
                    : null;
            }
        }

        $dailyData = $this->data['daily_progress_per_month'] ?? $this->data['daily_breakdown_per_month'] ?? null;
        if ($dailyData) {
            if ($dailyData instanceof \Illuminate\Support\Collection) {
                $dailyData = $dailyData->toArray();
            }
            $this->dailyProgressPerMonth = $dailyData;
        }
    }

    private function computeStatistics()
    {
        $targets = $this->data['daftar_target_pribadi'] ?? [];
        
        if ($targets instanceof \Illuminate\Support\Collection) {
            $targets = $targets->toArray();
        }

        $percents = array_map(fn($t) => (float)($t['progress_percent'] ?? 0), $targets);
        
        if (empty($percents)) {
            $this->statistics = [
                'mean' => 0, 'median' => 0, 'std_dev' => 0,
                'min' => 0, 'max' => 0, 'count' => 0, 'variance' => 0, 'range' => 0
            ];
            return;
        }

        sort($percents);
        $count = count($percents);
        $sum = array_sum($percents);
        $mean = $sum / $count;
        
        $median = $count % 2 === 0 
            ? ($percents[$count/2 - 1] + $percents[$count/2]) / 2 
            : $percents[(int)floor($count/2)];
        
        $variance = array_sum(array_map(fn($x) => pow($x - $mean, 2), $percents)) / $count;
        $stdDev = sqrt($variance);

        $this->statistics = [
            'mean' => round($mean, 2),
            'median' => round($median, 2),
            'std_dev' => round($stdDev, 2),
            'variance' => round($variance, 2),
            'min' => min($percents),
            'max' => max($percents),
            'count' => $count,
            'range' => max($percents) - min($percents)
        ];
    }

    private function computeRegression()
    {
        $points = [];
        foreach ($this->monthlyProgress as $month => $value) {
            if ($value !== null) {
                $points[] = ['x' => $month, 'y' => $value];
            }
        }

        $n = count($points);
        if ($n < 2) {
            $this->regression = [
                'slope' => 0, 
                'intercept' => $points[0]['y'] ?? 0,
                'r_squared' => 0, 
                'trend' => 'Data Belum Cukup',
                'forecast' => [], 
                'probability_100' => 0,
                'forecast_month12' => 0
            ];
            return;
        }

        $sumX = $sumY = $sumXY = $sumX2 = $sumY2 = 0;
        foreach ($points as $p) {
            $sumX += $p['x'];
            $sumY += $p['y'];
            $sumXY += $p['x'] * $p['y'];
            $sumX2 += $p['x'] ** 2;
            $sumY2 += $p['y'] ** 2;
        }

        $denom = $n * $sumX2 - $sumX ** 2;
        $slope = $denom != 0 ? ($n * $sumXY - $sumX * $sumY) / $denom : 0;
        $intercept = ($sumY - $slope * $sumX) / $n;

        $meanY = $sumY / $n;
        $ssTot = $sumY2 - $n * $meanY ** 2;
        $ssRes = 0;
        foreach ($points as $p) {
            $predicted = $slope * $p['x'] + $intercept;
            $ssRes += ($p['y'] - $predicted) ** 2;
        }
        $rSquared = $ssTot > 0 ? max(0, 1 - $ssRes / $ssTot) : 0;

        $trend = 'Stabil';
        if ($slope > 2) $trend = 'Meningkat Tajam';
        elseif ($slope > 0.5) $trend = 'Meningkat';
        elseif ($slope < -2) $trend = 'Menurun Tajam';
        elseif ($slope < -0.5) $trend = 'Menurun';

        $lastMonth = max(array_column($points, 'x'));
        $forecast = [];
        for ($m = $lastMonth + 1; $m <= min(12, $lastMonth + 3); $m++) {
            $forecast[$m] = max(0, min(100, round($slope * $m + $intercept, 2)));
        }

        $forecastMonth12 = $slope * 12 + $intercept;
        $stdDev = $this->statistics['std_dev'];
        $probability = 0;
        if ($stdDev > 0) {
            $z = (100 - $forecastMonth12) / $stdDev;
            $probability = round((1 - $this->normalCdf($z)) * 100, 1);
        } elseif ($forecastMonth12 >= 100) {
            $probability = 100;
        }
        $probability = max(0, min(100, $probability));

        $this->regression = [
            'slope' => round($slope, 3),
            'intercept' => round($intercept, 2),
            'r_squared' => round($rSquared, 3),
            'trend' => $trend,
            'forecast' => $forecast,
            'probability_100' => $probability,
            'forecast_month12' => round($forecastMonth12, 2)
        ];
    }

    private function normalCdf($x)
    {
        $t = 1 / (1 + 0.2316419 * abs($x));
        $d = 0.3989423 * exp(-$x * $x / 2);
        $p = $d * $t * (0.3193815 + $t * (-0.3565638 + $t * (1.781478 + $t * (-1.821256 + $t * 1.330274))));
        return $x > 0 ? 1 - $p : $p;
    }

    // ==========================================
    // MAIN GENERATOR
    // ==========================================

    public function generate()
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(10);

        $C = [
            'HDR' => '1E3A8A', 'SUB' => '3B82F6', 'ACC' => '8B5CF6',
            'ODD' => 'EFF6FF', 'GRN' => '059669', 'RED' => 'DC2626',
            'AMB' => 'D97706', 'GRY' => '6B7280', 'WH' => 'FFFFFF',
            'BG' => 'F8FAFC', 'BDR' => 'CBD5E1', 'IND' => '4F46E5'
        ];

        $this->buildSheet1ExecutiveDashboard($spreadsheet, $C);
        $this->buildSheet2AnalyticsTrend($spreadsheet, $C);
        $this->buildSheet3PredictionStrategy($spreadsheet, $C);

        $spreadsheet->setActiveSheetIndex(0);
        $tmpPath = tempnam(sys_get_temp_dir(), 'kpi_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->setIncludeCharts(true);
        $writer->save($tmpPath);

        return $tmpPath;
    }

    // ==========================================
    // SHEET 1: EXECUTIVE DASHBOARD
    // ==========================================
    private function buildSheet1ExecutiveDashboard($spreadsheet, $C)
    {
        $s = $spreadsheet->getActiveSheet()->setTitle('Executive Dashboard');
        $row = 1;

        $s->setCellValue('A' . $row, 'LAPORAN EKSEKUTIF KPI PRIBADI');
        $s->mergeCells('A' . $row . ':H' . $row);
        $s->getStyle('A' . $row)->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => $C['HDR']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        $s->getRowDimension($row)->setRowHeight(28);
        $row++;

        $s->setCellValue('A' . $row, "{$this->namaKaryawan}  •  {$this->jabatan}  •  {$this->data['user_info']['divisi']}  •  Tahun {$this->tahun}");
        $s->mergeCells('A' . $row . ':H' . $row);
        $s->getStyle('A' . $row)->applyFromArray([
            'font' => ['size' => 10, 'italic' => true, 'color' => ['rgb' => $C['GRY']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        $row += 2;

        // KPI Cards
        $cards = [
            ['A', 'Total Target', $this->data['total_target'] ?? 0, $C['SUB']],
            ['C', 'Rata-rata Progress', ($this->data['rata_rata_progress'] ?? 0) . '%', $C['ACC']],
            ['E', 'KPI Aktif', $this->data['kpi_aktif'] ?? 0, $C['AMB']],
            ['G', 'KPI Selesai', $this->data['kpi_selesai'] ?? 0, $C['GRN']],
        ];

        foreach ($cards as [$col, $label, $value, $color]) {
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
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['outline' => ['style' => Border::BORDER_THIN, 'color' => ['rgb' => $C['BDR']]]]
            ]);
            $s->getRowDimension($row + 2)->setRowHeight(36);
        }
        $row += 4;

        // Trend Badge
        $trend = $this->regression['trend'];
        $trendColor = str_contains($trend, 'Meningkat') ? $C['GRN'] : (str_contains($trend, 'Menurun') ? $C['RED'] : $C['AMB']);
        $s->setCellValue('A' . $row, "TREND PERKEMBANGAN:");
        $s->setCellValue('C' . $row, strtoupper($trend));
        $s->mergeCells('C' . $row . ':D' . $row);
        $s->getStyle('A' . $row)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($C['HDR']));
        $s->getStyle('C' . $row)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => $C['WH']]],
            'fill' => ['type' => Fill::FILL_SOLID, 'color' => ['rgb' => $trendColor]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        $row += 2;

        // Distribusi Status
        $s->setCellValue('A' . $row, 'DISTRIBUSI STATUS KPI');
        $s->getStyle('A' . $row)->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($C['HDR']));
        $s->mergeCells('A' . $row . ':D' . $row);
        $row++;

        $headers = ['Status', 'Jumlah', 'Persentase', 'Visual'];
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

        $distribusi = $this->data['distribusi_status'] ?? [];
        $total = max(1, $this->data['total_target'] ?? 1);
        $statusColors = [
            'Selesai' => $C['GRN'], 'Sedang Berjalan' => $C['AMB'],
            'Gagal' => $C['RED'], 'Belum Mulai' => $C['GRY']
        ];

        $distribusiStartRow = $row;
        foreach ($distribusi as $status => $count) {
            $percent = round(($count / $total) * 100, 1);
            $s->setCellValue("A{$row}", $status);
            $s->setCellValue("B{$row}", $count);
            $s->setCellValue("C{$row}", $percent . '%');
            $blocks = max(0, min(20, (int)($percent / 5)));
            $s->setCellValue("D{$row}", str_repeat('█', $blocks) . str_repeat('░', 20 - $blocks));

            $s->getStyle("A{$row}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($statusColors[$status] ?? $C['GRY']));
            $s->getStyle("B{$row}:C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $s->getStyle("D{$row}")->getFont()->getColor()->setRGB($statusColors[$status] ?? $C['GRY']);

            if (($row - $distribusiStartRow) % 2 === 0) {
                $s->getStyle("A{$row}:D{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($C['ODD']);
            }
            $row++;
        }
        $distribusiEndRow = $row - 1;

        // PIE CHART Distribusi
        if (!empty($distribusi)) {
            $seriesLabels = [new DataSeriesValues('String', "'Executive Dashboard'!\$A\$" . $distribusiStartRow . ":\$A\$" . $distribusiEndRow, null, count($distribusi))];
            $seriesValues = [new DataSeriesValues('Number', "'Executive Dashboard'!\$B\$" . $distribusiStartRow . ":\$B\$" . $distribusiEndRow, null, count($distribusi))];
            
            $series = new DataSeries(
                DataSeries::TYPE_PIECHART, 
                DataSeries::GROUPING_STANDARD, 
                range(0, count($seriesValues) - 1), 
                $seriesLabels, 
                [], 
                $seriesValues
            );
            $plotArea = new PlotArea(null, [$series]);
            $legend = new ChartLegend(ChartLegend::POSITION_RIGHT, null, false);
            $title = new ChartTitle('Komposisi Status KPI');
            $chart = new Chart('chart_status', $title, $legend, $plotArea);
            $chart->setTopLeftPosition('F' . $distribusiStartRow);
            $chart->setBottomRightPosition('H' . ($distribusiEndRow + 5));
            $s->addChart($chart);
        }

        $row = max($row, $distribusiEndRow + 7);

        // Top 3 & Bottom 3
        $targets = $this->data['daftar_target_pribadi'] ?? [];
        if ($targets instanceof \Illuminate\Support\Collection) $targets = $targets->toArray();
        
        usort($targets, fn($a, $b) => ($b['progress_percent'] ?? 0) <=> ($a['progress_percent'] ?? 0));

        $s->setCellValue("A{$row}", '🏆 TOP 3 TARGET TERBAIK');
        $s->getStyle("A{$row}")->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($C['GRN']));
        $s->mergeCells("A{$row}:D{$row}");
        $row++;

        foreach (array_slice($targets, 0, 3) as $i => $t) {
            $s->setCellValue("A{$row}", ($i + 1) . '.');
            $s->setCellValue("B{$row}", $t['judul'] ?? '-');
            $s->mergeCells("B{$row}:C{$row}");
            $s->setCellValue("D{$row}", ($t['progress_percent'] ?? 0) . '%');
            $s->getStyle("A{$row}:D{$row}")->getFont()->getColor()->setRGB($C['GRN']);
            $s->getStyle("D{$row}")->getFont()->setBold(true);
            $row++;
        }

        $row += 1;
        $s->setCellValue("A{$row}", '⚠️ TOP 3 TARGET PERLU PERHATIAN');
        $s->getStyle("A{$row}")->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($C['RED']));
        $s->mergeCells("A{$row}:D{$row}");
        $row++;

        $bottom = array_slice($targets, -3);
        $bottom = array_reverse($bottom);
        foreach ($bottom as $i => $t) {
            $s->setCellValue("A{$row}", ($i + 1) . '.');
            $s->setCellValue("B{$row}", $t['judul'] ?? '-');
            $s->mergeCells("B{$row}:C{$row}");
            $s->setCellValue("D{$row}", ($t['progress_percent'] ?? 0) . '%');
            $s->getStyle("A{$row}:D{$row}")->getFont()->getColor()->setRGB($C['RED']);
            $s->getStyle("D{$row}")->getFont()->setBold(true);
            $row++;
        }

        // Detail Target
        $row += 2;
        $s->setCellValue('A' . $row, 'DAFTAR LENGKAP TARGET KPI');
        $s->mergeCells('A' . $row . ':I' . $row);
        $s->getStyle('A' . $row)->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => $C['HDR']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        $row++;

        $detailHeaders = ['No', 'Judul Target', 'Periode', 'Tipe', 'Target', 'Progress', 'Progress (%)', 'Status', 'Tanggal'];
        $detailCols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];

        foreach ($detailHeaders as $i => $h) {
            $s->setCellValue($detailCols[$i] . $row, $h);
            $s->getStyle($detailCols[$i] . $row)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => $C['WH']]],
                'fill' => ['type' => Fill::FILL_SOLID, 'color' => ['rgb' => $C['HDR']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
            ]);
        }
        $s->getRowDimension($row)->setRowHeight(24);
        $row++;

        $statusColorsDetail = [
            'selesai' => $C['GRN'], 'gagal' => $C['RED'],
            'berjalan' => $C['AMB'], 'mulai' => $C['GRY']
        ];

        foreach ($targets as $i => $t) {
            $s->setCellValue("A{$row}", $i + 1);
            $s->setCellValue("B{$row}", $t['judul'] ?? '-');
            $s->setCellValue("C{$row}", $t['periode'] ?? '-');
            $s->setCellValue("D{$row}", ucfirst($t['tipe_target'] ?? '-'));
            $s->setCellValue("E{$row}", $t['target'] ?? 0);
            $s->setCellValue("F{$row}", $t['progress_display'] ?? '0');
            $s->setCellValue("G{$row}", ($t['progress_percent'] ?? 0) . '%');
            $s->setCellValue("H{$row}", $t['status'] ?? '-');
            $s->setCellValue("I{$row}", $t['created_at'] ?? '-');

            $status = strtolower($t['status'] ?? '');
            $sColor = $C['GRY'];
            foreach ($statusColorsDetail as $key => $color) {
                if (str_contains($status, $key)) { $sColor = $color; break; }
            }
            $s->getStyle("H{$row}")->getFont()->setBold(true)->getColor()->setRGB($sColor);

            $range = "A{$row}:I{$row}";
            $s->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $s->getStyle("B{$row}")->getAlignment()->setWrapText(true);
            $s->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            if ($i % 2 === 0) {
                $s->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($C['ODD']);
            }
            $row++;
        }

        $s->getColumnDimension('A')->setWidth(6);
        $s->getColumnDimension('B')->setWidth(40);
        $s->getColumnDimension('C')->setWidth(18);
        $s->getColumnDimension('D')->setWidth(12);
        $s->getColumnDimension('E')->setWidth(14);
        $s->getColumnDimension('F')->setWidth(18);
        $s->getColumnDimension('G')->setWidth(14);
        $s->getColumnDimension('H')->setWidth(18);
        $s->getColumnDimension('I')->setWidth(14);
    }

    // ==========================================
    // SHEET 2: ANALYTICS & TREND CHART
    // ==========================================
    private function buildSheet2AnalyticsTrend($spreadsheet, $C)
    {
        $s = $spreadsheet->createSheet()->setTitle('Analytics & Trend');
        $row = 1;

        $s->setCellValue('A' . $row, 'ANALISIS MENDALAM & TREN PERKEMBANGAN KPI');
        $s->mergeCells('A' . $row . ':N' . $row);
        $s->getStyle('A' . $row)->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => $C['HDR']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        $s->getRowDimension($row)->setRowHeight(26);
        $row++;

        $s->setCellValue('A' . $row, "Tahun {$this->tahun}  •  Realitas vs Prediksi Berbasis Regresi Linear");
        $s->mergeCells('A' . $row . ':N' . $row);
        $s->getStyle('A' . $row)->applyFromArray([
            'font' => ['size' => 10, 'italic' => true, 'color' => ['rgb' => $C['GRY']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        $row += 2;

        // === TABEL DATA UNTUK CHART ===
        $monthNames = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $s->setCellValue('A' . $row, 'METRIK');
        $s->getStyle('A' . $row)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => $C['WH']]],
            'fill' => ['type' => Fill::FILL_SOLID, 'color' => ['rgb' => $C['HDR']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        for ($m = 1; $m <= 12; $m++) {
            $col = $this->numToCol($m + 1);
            $s->setCellValue("{$col}{$row}", $monthNames[$m - 1]);
            $s->getStyle("{$col}{$row}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => $C['WH']]],
                'fill' => ['type' => Fill::FILL_SOLID, 'color' => ['rgb' => $C['SUB']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
        }
        $row++;

        // Baris 1: Progress Aktual
        $s->setCellValue('A' . $row, 'Progress Aktual (%)');
        $s->getStyle('A' . $row)->getFont()->setBold(true);
        $s->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($C['ODD']);

        $chartDataRowActual = $row;
        for ($m = 1; $m <= 12; $m++) {
            $col = $this->numToCol($m + 1);
            $val = $this->monthlyProgress[$m] ?? null;
            $s->setCellValue("{$col}{$row}", $val !== null ? $val : '');
            $s->getStyle("{$col}{$row}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'font' => ['bold' => true]
            ]);
            if ($val !== null) {
                $color = $val >= 80 ? $C['GRN'] : ($val >= 50 ? $C['AMB'] : $C['RED']);
                $s->getStyle("{$col}{$row}")->getFont()->getColor()->setRGB($color);
            }
        }
        $row++;

        // Baris 2: Progress Prediksi (Shadow Line)
        $s->setCellValue('A' . $row, 'Prediksi (%)');
        $s->getStyle('A' . $row)->getFont()->setBold(true)->setItalic(true);
        $s->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($C['ODD']);

        $chartDataRowPredict = $row;
        $slope = $this->regression['slope'];
        $intercept = $this->regression['intercept'];
        $currentMonth = (int)date('m');

        for ($m = 1; $m <= 12; $m++) {
            $col = $this->numToCol($m + 1);
            $actual = $this->monthlyProgress[$m] ?? null;
            // Prediksi untuk semua bulan (shadow line)
            $predicted = max(0, min(100, round($slope * $m + $intercept, 2)));
            $s->setCellValue("{$col}{$row}", $predicted);
            $s->getStyle("{$col}{$row}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
            
            // Highlight bulan prediksi (setelah current month)
            if ($m > $currentMonth) {
                $s->getStyle("{$col}{$row}")->getFont()->getColor()->setRGB($C['ACC']);
                $s->getStyle("{$col}{$row}")->getFont()->setBold(true);
            } else {
                $s->getStyle("{$col}{$row}")->getFont()->getColor()->setRGB($C['GRY']);
            }
        }
        $row++;

        // Baris 3: Momentum Delta
        $s->setCellValue('A' . $row, 'Momentum (Δ)');
        $s->getStyle('A' . $row)->getFont()->setBold(true);
        $s->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($C['ODD']);

        $prevVal = null;
        for ($m = 1; $m <= 12; $m++) {
            $col = $this->numToCol($m + 1);
            $val = $this->monthlyProgress[$m] ?? null;
            if ($val === null || $prevVal === null) {
                $s->setCellValue("{$col}{$row}", '-');
            } else {
                $delta = $val - $prevVal;
                $sign = $delta >= 0 ? '+' : '';
                $s->setCellValue("{$col}{$row}", $sign . round($delta, 1));
                $color = $delta > 0 ? $C['GRN'] : ($delta < 0 ? $C['RED'] : $C['GRY']);
                $s->getStyle("{$col}{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => $color]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);
            }
            if ($val !== null) $prevVal = $val;
        }
        $row += 2;
        $chartDataEndRow = $chartDataRowPredict;

        // === LINE CHART: AKTUAL + PREDIKSI (SHADOW) ===
        $xAxisTickValues = [new DataSeriesValues('String', "'Analytics & Trend'!\$B\$" . ($chartDataRowActual - 1) . ":\$M\$" . ($chartDataRowActual - 1), null, 12)];
        
        $seriesActual = new DataSeriesValues('Number', "'Analytics & Trend'!\$B\$" . $chartDataRowActual . ":\$M\$" . $chartDataRowActual, null, 12);
        $seriesPredict = new DataSeriesValues('Number', "'Analytics & Trend'!\$B\$" . $chartDataRowPredict . ":\$M\$" . $chartDataRowPredict, null, 12);

        $series = new DataSeries(
            DataSeries::TYPE_LINECHART,
            DataSeries::GROUPING_STANDARD,
            range(0, 1),
            [
                new DataSeriesValues('String', null, null, 1, ['Aktual (Realitas)']),
                new DataSeriesValues('String', null, null, 1, ['Prediksi (Shadow)'])
            ],
            $xAxisTickValues,
            [$seriesActual, $seriesPredict]
        );
        $series->setSmoothLine(true);

        $plotArea = new PlotArea(null, [$series]);
        $legend = new ChartLegend(ChartLegend::POSITION_BOTTOM, null, false);
        $title = new ChartTitle('Tren Progress: Realitas vs Prediksi (Shadow Line)');

        $chart = new Chart('trend_chart', $title, $legend, $plotArea);
        $chart->setTopLeftPosition('A' . $row);
        $chart->setBottomRightPosition('N' . ($row + 16));
        $s->addChart($chart);
        $row += 18;

        // === STATISTIK DESKRIPTIF ===
        $s->setCellValue('A' . $row, 'STATISTIK DESKRIPTIF & VOLATILITAS');
        $s->mergeCells('A' . $row . ':D' . $row);
        $s->getStyle('A' . $row)->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($C['HDR']));
        $row++;

        $stats = [
            ['Jumlah Target', $this->statistics['count']],
            ['Rata-rata (Mean)', $this->statistics['mean'] . '%'],
            ['Median', $this->statistics['median'] . '%'],
            ['Standar Deviasi (σ)', $this->statistics['std_dev'] . '%'],
            ['Variansi', $this->statistics['variance']],
            ['Nilai Minimum', $this->statistics['min'] . '%'],
            ['Nilai Maksimum', $this->statistics['max'] . '%'],
            ['Rentang (Range)', $this->statistics['range'] . '%'],
        ];

        $s->setCellValue('A' . $row, 'Metrik Statistik');
        $s->setCellValue('B' . $row, 'Nilai');
        $s->getStyle('A' . $row . ':B' . $row)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => $C['WH']]],
            'fill' => ['type' => Fill::FILL_SOLID, 'color' => ['rgb' => $C['HDR']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        $row++;

        foreach ($stats as $i => [$label, $value]) {
            $s->setCellValue("A{$row}", $label);
            $s->setCellValue("B{$row}", $value);
            $s->getStyle("A{$row}")->getFont()->setBold(true);
            $s->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $s->getStyle("A{$row}:B{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            if ($i % 2 === 0) {
                $s->getStyle("A{$row}:B{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($C['ODD']);
            }
            $row++;
        }

        // === REGRESI DI SEBELAH STATISTIK ===
        $regStartRow = $row - count($stats) - 2;
        $s->setCellValue('D' . $regStartRow, 'ANALISIS REGRESI LINEAR');
        $s->mergeCells('D' . $regStartRow . ':F' . $regStartRow);
        $s->getStyle('D' . $regStartRow)->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($C['HDR']));
        $regStartRow++;

        $regHeaders = ['Parameter', 'Nilai', 'Makna'];
        $regCols = ['D', 'E', 'F'];
        foreach ($regHeaders as $i => $h) {
            $s->setCellValue($regCols[$i] . $regStartRow, $h);
            $s->getStyle($regCols[$i] . $regStartRow)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => $C['WH']]],
                'fill' => ['type' => Fill::FILL_SOLID, 'color' => ['rgb' => $C['SUB']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
        }
        $regStartRow++;

        $regData = [
            ['Slope', $this->regression['slope'], 'Perubahan per bulan'],
            ['Intercept', $this->regression['intercept'], 'Progress awal'],
            ['R²', $this->regression['r_squared'], 'Konsistensi pola'],
            ['Trend', $this->regression['trend'], 'Arah performa'],
        ];

        foreach ($regData as $i => [$param, $val, $makna]) {
            $s->setCellValue("D{$regStartRow}", $param);
            $s->setCellValue("E{$regStartRow}", $val);
            $s->setCellValue("F{$regStartRow}", $makna);
            $s->getStyle("D{$regStartRow}")->getFont()->setBold(true);
            $s->getStyle("E{$regStartRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $s->getStyle("D{$regStartRow}:F{$regStartRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            if ($i % 2 === 0) {
                $s->getStyle("D{$regStartRow}:F{$regStartRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($C['ODD']);
            }
            $regStartRow++;
        }

        $row += 2;

        // === INTERPRETASI DINAMIS ===
        $s->setCellValue('A' . $row, 'INTERPRETASI PERKEMBANGAN');
        $s->mergeCells('A' . $row . ':F' . $row);
        $s->getStyle('A' . $row)->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($C['HDR']));
        $row++;

        $interpretations = $this->generateDevelopmentInterpretation();
        foreach ($interpretations as $interp) {
            $s->setCellValue("A{$row}", '▸');
            $s->setCellValue("B{$row}", $interp['label']);
            $s->setCellValue("C{$row}", $interp['value']);
            $s->mergeCells("C{$row}:F{$row}");
            $s->getStyle("A{$row}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($interp['color']));
            $s->getStyle("B{$row}")->getFont()->setBold(true);
            $s->getStyle("C{$row}")->getAlignment()->setWrapText(true);
            $s->getRowDimension($row)->setRowHeight(32);
            $row++;
        }

        $row += 1;
        // === INSIGHT ===
        $s->setCellValue('A' . $row, 'INSIGHT PERKEMBANGAN');
        $s->mergeCells('A' . $row . ':F' . $row);
        $s->getStyle('A' . $row)->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($C['HDR']));
        $row++;

        $insights = $this->generateMonthlyInsights();
        foreach ($insights as $insight) {
            $s->setCellValue("A{$row}", '•');
            $s->setCellValue("B{$row}", $insight);
            $s->mergeCells("B{$row}:F{$row}");
            $s->getStyle("B{$row}")->getAlignment()->setWrapText(true);
            $s->getRowDimension($row)->setRowHeight(20);
            $row++;
        }

        // Column Widths
        $s->getColumnDimension('A')->setWidth(22);
        $s->getColumnDimension('B')->setWidth(18);
        $s->getColumnDimension('C')->setWidth(30);
        $s->getColumnDimension('D')->setWidth(18);
        $s->getColumnDimension('E')->setWidth(15);
        $s->getColumnDimension('F')->setWidth(25);
        for ($m = 1; $m <= 12; $m++) {
            $s->getColumnDimension($this->numToCol($m + 1))->setWidth(9);
        }
        foreach (range('G', 'N') as $col) $s->getColumnDimension($col)->setWidth(10);
    }

    // ==========================================
    // SHEET 3: PREDICTION & STRATEGIC
    // ==========================================
    private function buildSheet3PredictionStrategy($spreadsheet, $C)
    {
        $s = $spreadsheet->createSheet()->setTitle('Prediction & Strategy');
        $row = 1;

        $s->setCellValue('A' . $row, 'PREDIKSI, POTENSI & STRATEGI PENCAPAIAN TARGET');
        $s->mergeCells('A' . $row . ':F' . $row);
        $s->getStyle('A' . $row)->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => $C['HDR']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        $s->getRowDimension($row)->setRowHeight(26);
        $row++;

        $s->setCellValue('A' . $row, "Forecast Berbasis Regresi Linear & Analisis Probabilitas");
        $s->mergeCells('A' . $row . ':F' . $row);
        $s->getStyle('A' . $row)->applyFromArray([
            'font' => ['size' => 10, 'italic' => true, 'color' => ['rgb' => $C['GRY']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        $row += 2;

        // === NARASI PREDIKSI & POTENSI GRANULAR ===
        $avgProg = $this->data['rata_rata_progress'] ?? 0;
        $slope = $this->regression['slope'];
        $r2 = $this->regression['r_squared'];
        $prob = $this->regression['probability_100'] ?? 0;
        $forecast12 = $this->regression['forecast_month12'] ?? 0;
        $stdDev = $this->statistics['std_dev'];
        $kpiGagal = $this->data['kpi_gagal'] ?? 0;
        $kpiAktif = $this->data['kpi_aktif'] ?? 0;
        $totalTarget = max(1, $this->data['total_target'] ?? 1);
        $failRate = round(($kpiGagal / $totalTarget) * 100, 1);

        // === PREDICTION TEXT GRANULAR ===
        $predictionText = $this->generatePredictionNarrative($avgProg, $slope, $forecast12, $r2, $prob, $stdDev);
        $potentialText = $this->generatePotentialNarrative($avgProg, $slope, $r2, $prob, $forecast12);
        $riskText = $this->generateRiskNarrative($kpiGagal, $totalTarget, $failRate, $stdDev, $slope);
        $strategyText = $this->generateStrategyNarrative($avgProg, $slope, $r2, $kpiGagal, $prob);

        // RENDER NARASI
        $narratives = [
            ['title' => '📊 PREDICTION NARRATIVE (Prediksi Kinerja)', 'text' => $predictionText, 'color' => $C['SUB'], 'bg' => 'F0F9FF'],
            ['title' => '🚀 POTENTIAL NARRATIVE (Potensi Pencapaian)', 'text' => $potentialText, 'color' => $C['GRN'], 'bg' => 'F0FDF4'],
            ['title' => '⚠️ RISK NARRATIVE (Analisis Risiko)', 'text' => $riskText, 'color' => $C['RED'], 'bg' => 'FEF2F2'],
            ['title' => '🔭 STRATEGY NARRATIVE (Rekomendasi Strategis)', 'text' => $strategyText, 'color' => $C['IND'], 'bg' => 'EEF2FF'],
        ];

        foreach ($narratives as $n) {
            $s->setCellValue('A' . $row, $n['title']);
            $s->mergeCells('A' . $row . ':F' . $row);
            $s->getStyle('A' . $row)->applyFromArray([
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => $n['color']]],
                'fill' => ['type' => Fill::FILL_SOLID, 'color' => ['rgb' => $n['bg']]]
            ]);
            $row++;

            $s->setCellValue('A' . $row, $n['text']);
            $s->mergeCells('A' . $row . ':F' . $row);
            $s->getStyle('A' . $row)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
            $s->getRowDimension($row)->setRowHeight(80);
            $s->getStyle('A' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $s->getStyle('A' . $row)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THICK)->getColor()->setRGB($n['color']);
            $row += 2;
        }

        // === METRIK PREDIKSI KUANTITATIF ===
        $s->setCellValue('A' . $row, 'METRIK PREDIKSI KUANTITATIF');
        $s->mergeCells('A' . $row . ':B' . $row);
        $s->getStyle('A' . $row)->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($C['HDR']));
        $row++;

        $probabilitas = $prob >= 85 ? 'SANGAT TINGGI' : ($prob >= 70 ? 'TINGGI' : ($prob >= 50 ? 'SEDANG' : ($prob >= 30 ? 'RENDAH' : 'SANGAT RENDAH')));
        $predData = [
            ['Rata-rata Progress Saat Ini', $avgProg . '%'],
            ['Estimasi Pencapaian Akhir Tahun', $forecast12 . '%'],
            ['Probabilitas Mencapai 100%', $prob . '% (' . $probabilitas . ')'],
            ['Tingkat Risiko Kegagalan', $kpiGagal . ' Target (' . $failRate . '%)'],
            ['Keandalan Model (R²)', $r2 . ' (' . ($r2 >= 0.7 ? 'Reliable' : ($r2 >= 0.4 ? 'Moderate' : 'Weak')) . ')'],
        ];

        foreach ($predData as $p) {
            $s->setCellValue("A{$row}", $p[0]);
            $s->setCellValue("B{$row}", $p[1]);
            $s->getStyle("A{$row}")->getFont()->setBold(true);
            $s->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $s->getStyle("A{$row}:B{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $row++;
        }
        $row += 1;

        // === GAUGE VISUAL ===
        $filled = max(0, min(20, (int)($prob / 5)));
        $empty = 20 - $filled;
        $gauge = str_repeat('█', $filled) . str_repeat('░', $empty);
        $s->setCellValue("A{$row}", 'Probabilitas Gauge:');
        $s->setCellValue("B{$row}", $gauge);
        $s->mergeCells("B{$row}:F{$row}");
        $s->getStyle("B{$row}")->getFont()->setBold(true)->setSize(12);
        $gaugeColor = $prob >= 70 ? $C['GRN'] : ($prob >= 40 ? $C['AMB'] : $C['RED']);
        $s->getStyle("B{$row}")->getFont()->getColor()->setRGB($gaugeColor);
        $row += 2;

        // === FORECAST TABLE 12 BULAN ===
        $s->setCellValue('A' . $row, 'FORECAST BULANAN (Regresi Linear)');
        $s->mergeCells('A' . $row . ':D' . $row);
        $s->getStyle('A' . $row)->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($C['HDR']));
        $row++;

        $fHeaders = ['Bulan', 'Progress Aktual', 'Progress Prediksi', 'Selisih'];
        $fCols = ['A', 'B', 'C', 'D'];
        foreach ($fHeaders as $i => $h) {
            $s->setCellValue($fCols[$i] . $row, $h);
            $s->getStyle($fCols[$i] . $row)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => $C['WH']]],
                'fill' => ['type' => Fill::FILL_SOLID, 'color' => ['rgb' => $C['SUB']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
        }
        $row++;

        $slope = $this->regression['slope'];
        $intercept = $this->regression['intercept'];
        $currentMonth = (int)date('m');
        $monthNames = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

        for ($m = 1; $m <= 12; $m++) {
            $s->setCellValue("A{$row}", $monthNames[$m - 1]);
            $actual = $this->monthlyProgress[$m] ?? null;
            $predicted = max(0, min(100, round($slope * $m + $intercept, 2)));
            
            $s->setCellValue("B{$row}", $actual !== null ? $actual . '%' : '-');
            $s->setCellValue("C{$row}", $predicted . '%');
            
            if ($actual !== null) {
                $diff = $actual - $predicted;
                $sign = $diff >= 0 ? '+' : '';
                $s->setCellValue("D{$row}", $sign . round($diff, 1) . '%');
                $color = $diff >= 0 ? $C['GRN'] : $C['RED'];
                $s->getStyle("D{$row}")->getFont()->setBold(true)->getColor()->setRGB($color);
            } else {
                $s->setCellValue("D{$row}", '-');
            }

            if ($m > $currentMonth) {
                $s->getStyle("A{$row}:D{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEF3C7');
                $s->getStyle("C{$row}")->getFont()->setBold(true)->getColor()->setRGB($C['ACC']);
            }

            $s->getStyle("A{$row}:D{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            foreach (['B', 'C', 'D'] as $col) {
                $s->getStyle("{$col}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
            $row++;
        }

        $row += 2;
        // === REKOMENDASI STRATEGIS ===
        $s->setCellValue('A' . $row, 'REKOMENDASI STRATEGIS');
        $s->mergeCells('A' . $row . ':F' . $row);
        $s->getStyle('A' . $row)->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($C['HDR']));
        $row++;

        $recommendations = $this->generateRecommendations();
        foreach ($recommendations as $rec) {
            $s->setCellValue("A{$row}", $rec['icon']);
            $s->setCellValue("B{$row}", $rec['title']);
            $s->setCellValue("C{$row}", $rec['desc']);
            $s->mergeCells("C{$row}:F{$row}");
            $s->getStyle("A{$row}")->getFont()->setSize(14);
            $s->getStyle("B{$row}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($rec['color']));
            $s->getStyle("C{$row}")->getAlignment()->setWrapText(true);
            $s->getRowDimension($row)->setRowHeight(40);
            $row++;
        }

        // Column Widths
        $s->getColumnDimension('A')->setWidth(25);
        $s->getColumnDimension('B')->setWidth(25);
        $s->getColumnDimension('C')->setWidth(50);
        foreach (range('D', 'F') as $col) $s->getColumnDimension($col)->setWidth(15);
    }

    // ==========================================
    // NARASI DINAMIS GRANULAR
    // ==========================================
    // ==========================================
    // NARASI DINAMIS GRANULAR (UPGRADED)
    // ==========================================

    private function generatePredictionNarrative($avgProg, $slope, $forecast12, $r2, $prob, $stdDev)
    {
        $base = "Berdasarkan analisis regresi linear (R²={$r2}) dengan tren {$slope}/bulan, ";
        $p = $avgProg;

        if ($p < 40) {
            $base .= "Anda berada dalam KONDISI DARURAT ({$p}%). Proyeksi akhir tahun hanya {$forecast12}% (probabilitas {$prob}%). Diperlukan tindakan darurat berupa eskalasi ke manajemen, review total target, dan intervensi harian untuk menyelamatkan sisa periode.";
        } elseif ($p >= 40 && $p < 43) {
            $base .= "Anda berada di ZONA BAHAYA KRITIS ({$p}%). Proyeksi akhir tahun {$forecast12}% (probabilitas {$prob}%). Fokus total minggu ini hanya pada penyelamatan 1 target utama yang paling berdampak. Tunda semua tugas non-esensial.";
        } elseif ($p >= 43 && $p < 46) {
            $base .= "Anda berada di ZONA BAHAYA ({$p}%). Proyeksi akhir tahun {$forecast12}% (probabilitas {$prob}%). Eskalasi hambatan operasional ke atasan segera. Diperlukan akselerasi minimal 5-7% bulan ini untuk keluar dari zona merah.";
        } elseif ($p >= 46 && $p < 49) {
            $base .= "Anda berada di ZONA MERAH AWAL ({$p}%). Proyeksi akhir tahun {$forecast12}% (probabilitas {$prob}%). Susun ulang prioritas kerja harian. Alokasikan 80% waktu produktif minggu ini khusus untuk 2 target dengan progress terendah.";
        } elseif ($p >= 49 && $p < 52) {
            $base .= "Anda berada di ZONA PERINGATAN KERAS ({$p}%). Proyeksi akhir tahun {$forecast12}% (probabilitas {$prob}%). Terapkan rutinitas daily check-in dengan supervisor untuk memastikan akuntabilitas dan mencegah penurunan lebih lanjut.";
        } elseif ($p >= 52 && $p < 55) {
            $base .= "Anda berada di ZONA KUNING TUA ({$p}%). Proyeksi akhir tahun {$forecast12}% (probabilitas {$prob}%). Fokus pada 'quick wins'. Selesaikan target-target kecil yang mendekati finish untuk membangun momentum psikologis positif.";
        } elseif ($p >= 55 && $p < 58) {
            $base .= "Anda berada di ZONA TRANSISI ({$p}%). Proyeksi akhir tahun {$forecast12}% (probabilitas {$prob}%). Performa mulai membaik. Terapkan time-blocking untuk tugas berat dan hindari multitasking yang mengurangi kualitas output.";
        } elseif ($p >= 58 && $p < 61) {
            $base .= "Anda berada di ZONA AMBANG BATAS ({$p}%). Proyeksi akhir tahun {$forecast12}% (probabilitas {$prob}%). Anda hampir mencapai titik aman (60%). Dorong 1-2 target kecil agar segera 'Selesai' untuk memberikan ruang gerak yang lebih luas.";
        } elseif ($p >= 61 && $p < 64) {
            $base .= "Anda berada di ZONA AMAN AWAL ({$p}%). Proyeksi akhir tahun {$forecast12}% (probabilitas {$prob}%). Selamat, Anda telah melewati batas kritis. Evaluasi target yang masih stagnan dan cari tahu hambatan spesifik yang menahannya.";
        } elseif ($p >= 64 && $p < 67) {
            $base .= "Anda berada di ZONA STABIL ({$p}%). Proyeksi akhir tahun {$forecast12}% (probabilitas {$prob}%). Performa mulai menunjukkan pola yang baik. Tingkatkan intensitas pada target bernilai tinggi (high-impact) untuk mendongkrak skor.";
        } elseif ($p >= 67 && $p < 70) {
            $base .= "Anda berada di ZONA PERTUMBUHAN ({$p}%). Proyeksi akhir tahun {$forecast12}% (probabilitas {$prob}%). Anda berada di jalur yang sangat tepat. Fokus pada penyelesaian target-target besar di kuartal ini untuk masuk ke zona 70%+.";
        } elseif ($p >= 70 && $p < 73) {
            $base .= "Anda berada di ZONA BAIK ({$p}%). Proyeksi akhir tahun {$forecast12}% (probabilitas {$prob}%). Momentum positif telah terbentuk. Mulailah mendokumentasikan progress dan tantangan sebagai bahan evaluasi mid-year yang konstruktif.";
        } elseif ($p >= 73 && $p < 76) {
            $base .= "Anda berada di ZONA SANGAT BAIK ({$p}%). Proyeksi akhir tahun {$forecast12}% (probabilitas {$prob}%). Akselerasi lebih lanjut sangat mungkin. Identifikasi 1 target 'stretch' yang bisa diselesaikan lebih awal untuk nilai tambah.";
        } elseif ($p >= 76 && $p < 79) {
            $base .= "Anda berada di ZONA OPTIMAL ({$p}%). Proyeksi akhir tahun {$forecast12}% (probabilitas {$prob}%). Performa Anda sangat solid. Mulai bagikan best practice atau tips efisiensi yang Anda temukan kepada rekan tim.";
        } elseif ($p >= 79 && $p < 82) {
            $base .= "Anda berada di ZONA EXCELLENT ({$p}%). Proyeksi akhir tahun {$forecast12}% (probabilitas {$prob}%). Anda mendekati garis finish dengan sangat baik. Pastikan tidak ada target yang terabaikan dan lakukan validasi data dengan stakeholder.";
        } elseif ($p >= 82 && $p < 85) {
            $base .= "Anda berada di ZONA PRESTASI TINGGI ({$p}%). Proyeksi akhir tahun {$forecast12}% (probabilitas {$prob}%). Pertahankan standar kualitas ini. Mulailah menyusun rencana pengembangan diri atau target baru yang lebih menantang.";
        } elseif ($p >= 85 && $p < 88) {
            $base .= "Anda berada di ZONA TOP PERFORMER ({$p}%). Proyeksi akhir tahun {$forecast12}% (probabilitas {$prob}%). Konsistensi Anda luar biasa. Anda berada di posisi ideal untuk mempertimbangkan peran mentoring bagi anggota tim lain.";
        } elseif ($p >= 88 && $p < 91) {
            $base .= "Anda berada di ZONA MASTERY ({$p}%). Proyeksi akhir tahun {$forecast12}% (probabilitas {$prob}%). Anda telah menjadi benchmark kinerja. Fokus selanjutnya adalah pada inovasi proses dan efisiensi kerja, bukan hanya angka target.";
        } elseif ($p >= 91 && $p < 94) {
            $base .= "Anda berada di ZONA SEMPURNA MENDEKATI ({$p}%). Proyeksi akhir tahun {$forecast12}% (probabilitas {$prob}%). Pastikan semua administrasi, bukti pendukung, dan dokumentasi target sudah 100% lengkap untuk validasi akhir.";
        } elseif ($p >= 94 && $p < 97) {
            $base .= "Anda berada di ZONA FINALISASI ({$p}%). Proyeksi akhir tahun {$forecast12}% (probabilitas {$prob}%). Selesaikan detail-detail kecil yang tersisa. Pastikan komunikasi mengenai status final target sudah jelas dan disetujui.";
        } else {
            $base .= "Anda berada di ZONA EXCELLENCE ({$p}%). Proyeksi akhir tahun {$forecast12}% (probabilitas {$prob}%). Selamat! Target tercapai dengan sangat baik. Fokus beralih pada evaluasi pasca-proyek, lessons learned, dan perencanaan strategis.";
        }
        
        return $base;
    }

    private function generatePotentialNarrative($avgProg, $slope, $r2, $prob, $forecast12)
    {
        $p = $avgProg;
        
        if ($p >= 90 && $slope >= 1.5) {
            return "POTENSI LUAR BIASA: Dengan progress {$p}% dan tren naik yang kuat (slope {$slope}), Anda memiliki peluang emas menjadi top performer organisasi. Potensi untuk mengambil tanggung jawab lebih besar, memimpin inisiatif, dan mentoring tim sangat tinggi. Pertimbangkan untuk mengajukan target 'stretch' di kuartal berikutnya.";
        } elseif ($p >= 80 && $slope >= 0.5) {
            return "POTENSI TINGGI: Progress {$p}% dengan tren positif (slope {$slope}) menunjukkan kapasitas berkembang lebih jauh. Anda berpotensi menjadi benchmark bagi rekan sejawat. Dokumentasikan best practice dan bagikan knowledge untuk memperkuat posisi Anda sebagai thought leader di divisi.";
        } elseif ($p >= 70 && $slope >= 0) {
            return "POTENSI MODERAT-POSITIF: Progress {$p}% dengan slope {$slope}/bulan masih memiliki ruang pertumbuhan 20-30%. Dengan fokus pada konsistensi dan eliminasi hambatan kecil, Anda dapat dengan mudah menembus threshold 85% di akhir tahun. Quick wins adalah kunci saat ini.";
        } elseif ($p >= 60 && $slope > -0.5) {
            return "POTENSI TERBATAS NAMUN BISA DIPULIHKAN: Progress {$p}% dengan slope {$slope}/bulan memerlukan dorongan ekstra. Potensi pertumbuhan masih ada namun memerlukan komitmen penuh dan realokasi prioritas. Fokus pada 1-2 target kritis dapat membangun momentum positif yang hilang.";
        } elseif ($p >= 50 && $slope > -1.5) {
            return "POTENSI MENIPIS: Progress {$p}% dengan slope {$slope}/bulan menunjukkan tren stagnan atau sedikit menurun. Potensi untuk mencapai target 100% semakin kecil kecuali ada perubahan strategi yang signifikan, dukungan tambahan, atau penyesuaian scope target.";
        } elseif ($slope < -1.5) {
            return "POTENSI NEGATIF (TURNAROUND REQUIRED): Slope {$slope}/bulan menunjukkan penurunan tajam. Potensi untuk recovery sangat bergantung pada intervensi segera dan identifikasi akar masalah (root cause). Tanpa tindakan korektif fundamental, performa akan terus memburuk.";
        } else {
            return "POTENSI KURANG OPTIMAL: Progress {$p}% dengan pola yang tidak konsisten (R²={$r2}) membuat prediksi menjadi sulit. Potensi pertumbuhan masih mungkin dicapai namun memerlukan evaluasi mendalam terhadap faktor eksternal dan internal yang mempengaruhi performa harian.";
        }
    }

    private function generateRiskNarrative($kpiGagal, $totalTarget, $failRate, $stdDev, $slope)
    {
        $base = "Analisis risiko menunjukkan: ";
        
        // Analisis Kegagalan
        if ($kpiGagal > ($totalTarget * 0.3)) {
            $base .= "RISIKO KRITIS - {$kpiGagal} target ({$failRate}%) berstatus gagal. Ini mengindikasikan masalah sistemik dalam perencanaan atau eksekusi. Risiko demotivasi dan dampak pada evaluasi tahunan sangat tinggi. Diperlukan audit menyeluruh terhadap proses penetapan target.";
        } elseif ($kpiGagal > ($totalTarget * 0.2)) {
            $base .= "RISIKO TINGGI - {$kpiGagal} target ({$failRate}%) gagal. Pattern kegagalan ini perlu dianalisis untuk menemukan common cause. Risiko efek domino pada target lain yang masih berjalan cukup signifikan dan memerlukan mitigasi proaktif.";
        } elseif ($kpiGagal > ($totalTarget * 0.1)) {
            $base .= "RISIKO MODERAT - {$kpiGagal} target ({$failRate}%) gagal, masih dalam batas yang dapat ditoleransi. Namun, monitoring ketat diperlukan untuk mencegah eskalasi ke target-target kritis lainnya.";
        } elseif ($kpiGagal > 0) {
            $base .= "RISIKO RENDAH - {$kpiGagal} target gagal ({$failRate}%), manajemen risiko berjalan baik. Tetap lakukan monitoring preventif untuk memastikan tidak ada target baru yang tergeser ke status gagal.";
        } else {
            $base .= "TIDAK ADA RISIKO KEGAGALAN - Semua target masih berjalan atau selesai. Performa sangat solid dan manajemen risiko berjalan efektif.";
        }
        
        // Analisis Volatilitas
        if ($stdDev > 25) {
            $base .= " | Volatilitas SANGAT TINGGI (σ={$stdDev}%) menunjukkan performa yang sangat fluktuatif dan tidak konsisten. Ini menambah risiko operasional dan memerlukan pembuatan SOP pribadi yang lebih ketat.";
        } elseif ($stdDev > 15) {
            $base .= " | Volatilitas TINGGI (σ={$stdDev}%) - fluktuasi perlu distabilkan. Disarankan untuk membuat rutinitas harian yang konsisten agar tidak terjadi penurunan progress mendadak di akhir periode.";
        } elseif ($stdDev > 8) {
            $base .= " | Volatilitas SEDANG (σ={$stdDev}%) - fluktuasi wajar namun perlu monitoring berkala untuk menjaga stabilitas momentum.";
        } else {
            $base .= " | Volatilitas RENDAH (σ={$stdDev}%) - performa sangat stabil, konsisten, dan dapat diprediksi dengan akurasi tinggi.";
        }
        
        return $base;
    }

    private function generateStrategyNarrative($avgProg, $slope, $r2, $kpiGagal, $prob)
    {
        $p = $avgProg;
        $base = "Rekomendasi strategis berdasarkan kondisi saat ini: ";
        
        if ($p >= 85 && $slope >= 1) {
            $base .= "STRATEGI EKSPANSI & KEPEMIMPINAN. Anda siap untuk mengambil tantangan lebih. (1) Ajukan target 'stretch' untuk kuartal depan, (2) Jadilah mentor bagi junior, (3) Dokumentasikan best practice dan share ke tim, (4) Eksplorasi inisiatif inovasi yang dapat meningkatkan efisiensi divisi.";
        } elseif ($p >= 70 && $slope >= 0) {
            $base .= "STRATEGI AKSELERASI TERFOKUS. Manfaatkan momentum positif. (1) Identifikasi 2-3 quick wins untuk membangun confidence, (2) Lakukan weekly review terhadap target kritis, (3) Optimalkan alokasi waktu untuk high-impact activities, (4) Delegasikan low-value tasks jika memungkinkan.";
        } elseif ($p >= 60 && $slope >= -0.5) {
            $base .= "STRATEGI STABILISASI & FOKUS. Perkuat fondasi sebelum akselerasi. (1) Review ulang prioritas target secara drastis, (2) Susun daily action plan yang spesifik dan measurable, (3) Lakukan coaching session dengan atasan untuk mendapat guidance, (4) Fokus pada proses, bukan hanya hasil akhir.";
        } elseif ($p >= 50) {
            $base .= "STRATEGI TURNAROUND. Diperlukan perubahan signifikan. (1) Lakukan root cause analysis mendalam terhadap target yang tertinggal, (2) Prioritaskan 20% target yang memberikan 80% impact (Prinsip Pareto), (3) Daily check-in dengan supervisor, (4) Pertimbangkan penyesuaian target yang tidak realistis.";
        } else {
            $base .= "STRATEGI DARURAT & INTERVENSI. Tindakan level tinggi diperlukan segera. (1) Eskalasi ke manajemen untuk mendapat dukungan penuh dan realokasi sumber daya, (2) Review total terhadap target dan timeline, (3) Coaching harian intensif, (4) Kemungkinan re-assignment atau restrukturisasi tanggung jawab.";
        }
        
        return $base;
    }

        // ==========================================
    // HELPER GENERATORS (UPGRADED & GRANULAR)
    // ==========================================

    private function generateMonthlyInsights()
    {
        $insights = [];
        $validMonths = array_filter($this->monthlyProgress, fn($v) => $v !== null);
        
        if (empty($validMonths)) {
            return ['📊 Belum ada data bulanan yang cukup untuk dianalisis secara mendalam.'];
        }

        $slope = $this->regression['slope'];
        $r2 = $this->regression['r_squared'];
        $trend = $this->regression['trend'];
        $avgProg = $this->data['rata_rata_progress'] ?? 0;

        // 1. Trend Analysis
        $trendDir = $slope >= 0 ? 'positif' : 'negatif';
        $insights[] = "📈 Analisis Tren: Secara keseluruhan, performa menunjukkan arah {$trendDir} dengan kemiringan (slope) " . ($slope >= 0 ? '+' : '') . "{$slope} per bulan. Ini mengindikasikan bahwa {$trend}.";

        // 2. Consistency (R²)
        if ($r2 > 0.7) {
            $insights[] = "✅ Konsistensi Tinggi: Pola perkembangan sangat stabil dan dapat diprediksi (R² = {$r2}). Ini adalah fondasi yang excellent untuk perencanaan jangka panjang.";
        } elseif ($r2 > 0.4) {
            $insights[] = "⚠️ Konsistensi Moderat: Pola perkembangan cukup konsisten (R² = {$r2}), namun terdapat beberapa fluktuasi wajar yang perlu diwaspadai agar tidak menjadi tren negatif.";
        } else {
            $insights[] = "🚨 Konsistensi Rendah: Pola perkembangan sangat fluktuatif (R² = {$r2}). Sangat disarankan untuk melakukan evaluasi mendalam terhadap manajemen waktu atau faktor eksternal yang menyebabkan inkonsistensi ini.";
        }

        // 3. Peak Performance
        $maxMonth = array_search(max($validMonths), $validMonths);
        $minMonth = array_search(min($validMonths), $validMonths);
        $monthNames = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        
        $insights[] = "🏆 Puncak Performa: Pencapaian tertinggi terjadi pada bulan {$monthNames[$maxMonth - 1]} (" . max($validMonths) . "%). Analisis kondisi kerja pada bulan ini dapat dijadikan best practice untuk bulan-bulan mendatang.";
        $insights[] = "📉 Titik Terendah: Pencapaian terendah tercatat pada bulan {$monthNames[$minMonth - 1]} (" . min($validMonths) . "%). Perlu investigasi apakah ada hambatan spesifik (cuti, beban kerja, dll) pada periode tersebut.";

        // 4. Daily Granularity
        if (!empty($this->dailyProgressPerMonth)) {
            $activeMonthsCount = count($this->dailyProgressPerMonth);
            if ($activeMonthsCount > 8) {
                $insights[] = "📅 Disiplin Harian: Analisis granular menunjukkan pola update progres yang rutin dan disiplin ({$activeMonthsCount} bulan dengan data harian), mencerminkan komitmen kerja yang sangat baik.";
            } else {
                $insights[] = "⏳ Perlu Peningkatan Frekuensi: Data harian masih terbatas ({$activeMonthsCount} bulan). Disarankan untuk meningkatkan frekuensi update progres harian agar monitoring lebih real-time dan akurat.";
            }
        }

        // 5. Recent Momentum (Last 3 months)
        $lastThree = array_slice($validMonths, -3, 3, true);
        if (count($lastThree) >= 2) {
            $values = array_values($lastThree);
            $increasing = true;
            for ($i = 1; $i < count($values); $i++) {
                if ($values[$i] <= $values[$i - 1]) { $increasing = false; break; }
            }
            
            $latestMonthKey = array_key_last($validMonths);
            $latestMonthName = $monthNames[$latestMonthKey - 1];

            if ($increasing) {
                $insights[] = "🔥 Momentum Positif Terkini: Progress 3 bulan terakhir (hingga {$latestMonthName}) terus MENINGKAT secara bertahap. Pertahankan ritme akselerasi ini untuk menutup tahun dengan hasil maksimal!";
            } else {
                $insights[] = "⚠️ Melambat di Akhir Periode: Progress 3 bulan terakhir (hingga {$latestMonthName}) menunjukkan tanda-tanda perlambatan atau stagnasi. Segera lakukan penyesuaian strategi agar tidak kehilangan momentum yang sudah dibangun.";
            }
        }

        return $insights;
    }

    private function generateDevelopmentInterpretation()
    {
        $interps = [];
        $stdDev = $this->statistics['std_dev'];
        $slope = $this->regression['slope'];
        $r2 = $this->regression['r_squared'];

        // 1. Volatility Interpretation
        if ($stdDev < 10) {
            $interps[] = [
                'label' => '🟢 Volatilitas Rendah (Stabil)', 
                'value' => "Performa sangat stabil (σ = {$stdDev}%). Anda memiliki ritme kerja yang konsisten dan predictable. Ini adalah aset berharga untuk target jangka panjang. Pertahankan rutinitas ini.", 
                'color' => '059669'
            ];
        } elseif ($stdDev < 20) {
            $interps[] = [
                'label' => '🟡 Volatilitas Sedang (Wajar)', 
                'value' => "Performa cukup stabil dengan fluktuasi yang masih dalam batas wajar (σ = {$stdDev}%). Disarankan untuk membuat micro-goals harian agar ritme kerja lebih terjaga dan tidak terjadi 'boom and bust' cycle.", 
                'color' => 'D97706'
            ];
        } else {
            $interps[] = [
                'label' => '🔴 Volatilitas Tinggi (Tidak Stabil)', 
                'value' => "Performa sangat fluktuatif (σ = {$stdDev}%). Ini adalah red flag yang memerlukan investigasi segera. Identifikasi apakah penyebabnya adalah manajemen waktu atau beban kerja, lalu buat SOP pribadi untuk menstabilkan output.", 
                'color' => 'DC2626'
            ];
        }

        // 2. Growth Potential Interpretation
        if ($slope > 2) {
            $interps[] = [
                'label' => '🚀 Pertumbuhan Eksponensial', 
                'value' => "Kemiringan positif yang sangat kuat (+{$slope}/bulan). Anda menunjukkan perkembangan akseleratif yang luar biasa. Manfaatkan momentum ini untuk mengambil inisiatif atau target tambahan yang lebih menantang.", 
                'color' => '059669'
            ];
        } elseif ($slope > 0.5) {
            $interps[] = [
                'label' => '📈 Pertumbuhan Positif Stabil', 
                'value' => "Kemiringan positif yang sehat (+{$slope}/bulan). Anda berkembang secara bertahap dan konsisten. Ini adalah pola pertumbuhan yang paling sustainable untuk karir jangka panjang.", 
                'color' => '059669'
            ];
        } elseif ($slope > -0.5) {
            $interps[] = [
                'label' => '⚠️ Stagnan / Plateau', 
                'value' => "Kemiringan hampir nol ({$slope}/bulan). Performa cenderung datar. Anda mungkin telah mencapai 'comfort zone'. Butuh stimulus baru, seperti mempelajari skill baru atau meminta feedback konstruktif, untuk memecah kebuntuan ini.", 
                'color' => 'D97706'
            ];
        } else {
            $interps[] = [
                'label' => '🚨 Perlu Intervensi Segera', 
                'value' => "Kemiringan negatif ({$slope}/bulan). Performa menunjukkan tren penurunan yang mengkhawatirkan. Coaching segera dan identifikasi hambatan (blockers) sangat diperlukan sebelum situasi menjadi lebih sulit dipulihkan.", 
                'color' => 'DC2626'
            ];
        }

        // 3. Predictability Interpretation
        if ($r2 > 0.7) {
            $interps[] = [
                'label' => '🎯 Sangat Dapat Diprediksi', 
                'value' => "R² = {$r2}. Pola kerja Anda sangat konsisten. Manajemen dapat mengandalkan prediksi ini untuk perencanaan sumber daya dan target jangka panjang dengan tingkat kepercayaan yang tinggi.", 
                'color' => '059669'
            ];
        } elseif ($r2 > 0.4) {
            $interps[] = [
                'label' => '⚖️ Cukup Dapat Diprediksi', 
                'value' => "R² = {$r2}. Pola dapat diprediksi dengan akurasi sedang. Ada beberapa variabel eksternal atau internal yang mempengaruhi performa, namun tren dasarnya masih dapat dibaca.", 
                'color' => 'D97706'
            ];
        } else {
            $interps[] = [
                'label' => '❓ Sulit Diprediksi', 
                'value' => "R² = {$r2}. Banyak faktor yang mempengaruhi performa secara acak, membuat prediksi menjadi sulit. Fokus harus dialihkan dari perencanaan jangka panjang ke manajemen krisis dan perbaikan proses harian.", 
                'color' => 'DC2626'
            ];
        }

        return $interps;
    }

    private function generateRecommendations()
    {
        $recs = [];
        $prob = $this->regression['probability_100'];
        $slope = $this->regression['slope'];
        $stdDev = $this->statistics['std_dev'];
        $avgProg = $this->data['rata_rata_progress'] ?? 0;

        // 1. Primary Recommendation based on Probability & Avg Progress
        if ($prob >= 80 && $avgProg >= 75) {
            $recs[] = [
                'icon' => '🏆', 
                'title' => 'Pertahankan & Ekspansi', 
                'desc' => "Probabilitas sangat tinggi ({$prob}%) untuk mencapai target. Anda berada di jalur yang tepat. Fokus sekarang adalah pada konsistensi, dokumentasi best practice, dan pertimbangkan untuk mengajukan target 'stretch' di periode berikutnya.", 
                'color' => '059669'
            ];
        } elseif ($prob >= 60 && $avgProg >= 60) {
            $recs[] = [
                'icon' => '🚀', 
                'title' => 'Akselerasi Terfokus', 
                'desc' => "Probabilitas baik ({$prob}%). Anda berada di jalur yang aman namun perlu dorongan ekstra. Tingkatkan intensitas pada target dengan progress di bawah 70%. Susun weekly milestone yang spesifik dan lakukan review rutin.", 
                'color' => '059669'
            ];
        } elseif ($prob >= 40 || $avgProg >= 50) {
            $recs[] = [
                'icon' => '⚡', 
                'title' => 'Strategi Quick Wins', 
                'desc' => "Probabilitas sedang ({$prob}%). Fokus pada eliminasi hambatan dan selesaikan target-target kecil yang mendekati finish untuk membangun momentum. Coaching mingguan dengan atasan sangat direkomendasikan untuk menjaga akuntabilitas.", 
                'color' => 'D97706'
            ];
        } else {
            $recs[] = [
                'icon' => '🚨', 
                'title' => 'Intervensi & Turnaround', 
                'desc' => "Probabilitas rendah ({$prob}%). Situasi memerlukan perhatian serius. Lakukan root cause analysis mendalam, evaluasi ulang target yang tidak realistis, dan jadwalkan daily check-in dengan supervisor untuk memulihkan performa.", 
                'color' => 'DC2626'
            ];
        }

        // 2. Volatility Recommendation
        if ($stdDev > 20) {
            $recs[] = [
                'icon' => '📊', 
                'title' => 'Stabilkan Ritme Kerja', 
                'desc' => "Volatilitas sangat tinggi (σ={$stdDev}%). Performa naik-turun drastis menambah risiko. Identifikasi faktor penyebab fluktuasi, buat SOP pribadi yang lebih ketat, dan bangun rutinitas harian yang konsisten.", 
                'color' => 'D97706'
            ];
        } elseif ($stdDev > 10) {
            $recs[] = [
                'icon' => '⚖️', 
                'title' => 'Jaga Konsistensi', 
                'desc' => "Volatilitas sedang (σ={$stdDev}%). Performa cukup baik namun perlu dijaga. Monitoring rutin diperlukan untuk menjaga ritme kerja yang stabil dan menghindari penurunan performa mendadak di akhir periode.", 
                'color' => 'D97706'
            ];
        }

        // 3. Trend (Slope) Recommendation
        if ($slope < -1) {
            $recs[] = [
                'icon' => '📉', 
                'title' => 'Reverse the Trend', 
                'desc' => "Trend menurun tajam (slope={$slope}). Jangan abaikan sinyal ini. Lakukan root cause analysis mendalam segera dan buat action plan perbaikan mingguan dengan KPI yang sangat spesifik dan terukur.", 
                'color' => 'DC2626'
            ];
        } elseif ($slope < 0) {
            $recs[] = [
                'icon' => '⚠️', 
                'title' => 'Cegah Penurunan Lebih Lanjut', 
                'desc' => "Trend sedikit menurun (slope={$slope}). Identifikasi early warning signs dan lakukan intervensi proaktif (seperti penyesuaian beban kerja) sebelum situasi memburuk.", 
                'color' => 'D97706'
            ];
        } elseif ($slope > 2) {
            $recs[] = [
                'icon' => '🌟', 
                'title' => 'Scale Up & Recognition', 
                'desc' => "Trend meningkat sangat tajam (slope={$slope}). Luar biasa! Pertimbangkan untuk mengajukan penugasan pada proyek yang lebih menantang dan pastikan pencapaian ini mendapatkan apresiasi yang layak.", 
                'color' => '059669'
            ];
        } elseif ($slope > 0.5) {
            $recs[] = [
                'icon' => '📈', 
                'title' => 'Lanjutkan Momentum Positif', 
                'desc' => "Trend positif yang sehat (slope={$slope}). Pertahankan ritme ini. Anda berada di jalur yang tepat untuk mencapai atau bahkan melampaui target yang ditetapkan.", 
                'color' => '059669'
            ];
        }

        return $recs;
    }

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