<?php

namespace App\Exports\KPI;

use App\Traits\ExportStyleTrait;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;

class DepartemenKPIExport
{
    use ExportStyleTrait;

    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function generate()
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(10);

        // Konstanta Warna
        $C_HDR = '1F2937'; $C_SUB = '2563EB'; $C_WH = 'FFFFFF'; $C_ODD = 'F3F4F6';
        $C_GRN = '10B981'; $C_RED = 'EF4444'; $C_YEL = 'F59E0B'; $C_DRK = '111827';

        // 1. Sheet: Executive Summary
        $s1 = $spreadsheet->getActiveSheet()->setTitle('Executive Summary');
        // [Pindahkan seluruh logika dari baris pembuatan $s1 hingga perulangan ranking karyawan di sini]
        // Ganti variabel $data menjadi $this->data

        // 2. Sheet: Deep Analysis
        $s2 = $spreadsheet->createSheet()->setTitle('Deep Analysis');
        // [Pindahkan seluruh logika pembuatan $s2 dan LineChart trend performa di sini]

        // 3. Sheet: Visualisasi Grafik
        $s3 = $spreadsheet->createSheet()->setTitle('Visualisasi Grafik');
        // [Pindahkan seluruh logika pembuatan $s3 dan PieChart distribusi grade di sini]

        $spreadsheet->setActiveSheetIndex(0);
        $tmpPath = tempnam(sys_get_temp_dir(), 'kpi_dept_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->setIncludeCharts(true);
        $writer->save($tmpPath);

        return $tmpPath;
    }
}
