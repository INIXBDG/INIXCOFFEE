<?php

namespace App\Exports\KPI;

use App\Traits\ExportStyleTrait;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;

class MonitoringKPIExport
{
    use ExportStyleTrait;

    private $data;
    private $namaKaryawan;
    private $jabatan;
    private $tahun;

    public function __construct(array $data, string $namaKaryawan, string $jabatan, int $tahun)
    {
        $this->data = $data;
        $this->namaKaryawan = $namaKaryawan;
        $this->jabatan = $jabatan;
        $this->tahun = $tahun;
    }

    public function generate()
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(10);

        // Konstanta Warna
        $C_HDR = '2F5496'; $C_SUB = '8EA9DB'; $C_ODD = 'DCE6F1'; $C_WH = 'FFFFFF';
        $C_TOT = 'D9E1F2'; $C_GRN = '70AD47'; $C_YEL = 'FFC000'; $C_RED = 'FF4444';
        $C_AMB = 'D97706'; $C_DRK = '1F2937'; $C_GRY = '888888';

        // 1. Sheet: Daftar Target KPI
        $s1 = $spreadsheet->getActiveSheet()->setTitle('Daftar Target KPI');
        // [Pindahkan seluruh logika dari baris $colW1 = ... hingga $s1->addChart($chartBar); di sini]
        // Pastikan mengganti $data menjadi $this->data, $namaKaryawan menjadi $this->namaKaryawan, dan $tahun menjadi $this->tahun.

        // 2. Sheet: Rekap & Analisa
        $s2 = $spreadsheet->createSheet()->setTitle('Rekap & Analisa');
        // [Pindahkan seluruh logika pembuatan $s2 dan chartLine serta chartRupiah di sini]

        // 3. Sheet: Ringkasan Eksekutif
        $s3 = $spreadsheet->createSheet()->setTitle('Ringkasan Eksekutif');
        // [Pindahkan seluruh logika pembuatan $s3 dan chartPie di sini]

        $spreadsheet->setActiveSheetIndex(0);
        $tmpPath = tempnam(sys_get_temp_dir(), 'kpi_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->setIncludeCharts(true);
        $writer->save($tmpPath);

        return $tmpPath;
    }
}
