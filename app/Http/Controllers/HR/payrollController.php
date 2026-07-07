<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\karyawan;
use App\Models\LogGaji;
use App\Models\TunjanganKaryawan;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PerhitunganTunjanganHR;
use App\Models\Pph21Karyawan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;

class payrollController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('HR.payroll.index');
    }

    public function getPayrollDashboard(Request $request)
    {
        try {
            $bulan = (int) $request->input('month', now()->month);
            $tahun = (int) $request->input('year', now()->year);
            $search = $request->input('search', '');
            $page = max(1, (int) $request->input('page', 1));
            $perPage = 15;

            $baseQuery = Karyawan::query()
                ->whereNot('jabatan', 'Pilih Jabatan')
                ->where('status_aktif', '1');

            if (!empty($search)) {
                $baseQuery->where(function ($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('kode_karyawan', 'like', "%{$search}%")
                        ->orWhere('divisi', 'like', "%{$search}%")
                        ->orWhere('jabatan', 'like', "%{$search}%");
                });
            }

            $eligibleKaryawan = $baseQuery->get();
            $eligibleIds = $eligibleKaryawan->pluck('id')->toArray();
            $totalEligible = count($eligibleIds);

            $tunjanganData = TunjanganKaryawan::with('jenistunjangan:id,nama_tunjangan,tipe')->whereIn('id_karyawan', $eligibleIds)->where('bulan', $bulan)->where('tahun', $tahun)->get()->groupBy('id_karyawan');

            $periodStart = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
            $periodEnd = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();

            $payrollList = $eligibleKaryawan->map(function ($emp) use ($tunjanganData, $periodStart, $periodEnd) {
                $items = $tunjanganData->get($emp->id, collect());
                $gajiPokok = (float) ($emp->gaji_pokok ?? ($emp->gaji + $emp->tunjangan_jabatan ?? 0));

                $totalTunjangan = $items->where('jenistunjangan.tipe', 'Tunjangan')->sum('total');
                $totalPotongan = abs($items->where('jenistunjangan.tipe', 'Potongan')->sum('total'));

                $tunjanganBersih = $totalTunjangan - $totalPotongan;

                if ($tunjanganBersih < 0) {
                    $tunjanganBersih = 0;
                }

                $gajiBersih = $gajiPokok + $tunjanganBersih;

                $awalProbation = $emp->awal_probation ? Carbon::parse($emp->awal_probation) : null;
                $resignedAt = $emp->resigned_at ? Carbon::parse($emp->resigned_at) : null;

                $status = $this->determineEmployeeStatusForPeriod($awalProbation, $resignedAt, $emp->status_aktif, $periodStart, $periodEnd);

                return [
                    'id' => $emp->id,
                    'nama' => $emp->nama_lengkap,
                    'kode' => $emp->kode_karyawan,
                    'divisi' => $emp->divisi ?? '-',
                    'jabatan' => $emp->jabatan ?? '-',
                    'gaji_pokok' => $gajiPokok,
                    'total_tunjangan' => $totalTunjangan,
                    'tunjangan_bersih' => $tunjanganBersih,
                    'total_potongan' => $totalPotongan,
                    'gaji_bersih' => $gajiBersih,
                    'status' => $status,
                    'details' => $items
                        ->map(
                            fn($i) => [
                                'nama' => optional($i->jenistunjangan)->nama_tunjangan,
                                'tipe' => optional($i->jenistunjangan)->tipe,
                                'keterangan' => $i->keterangan,
                                'nilai' => (float) $i->total,
                            ],
                        )
                        ->values(),
                ];
            });

            $totalRecords = $payrollList->count();
            $paginated = $payrollList->forPage($page, $perPage);
            $lastPage = ceil($totalRecords / $perPage);

            $salaryRanges = $this->calculateSalaryRanges($payrollList);
            $allowanceByDivisi = $this->calculateAllowanceByDivisi($payrollList);
            $monthlyTrend = $this->calculateMonthlyTrend($tahun);
            $topDeductions = $this->calculateTopDeductions($payrollList, $bulan, $tahun);

            $summary = [
                'total_karyawan' => $totalEligible,
                'sudah_dihitung' => $payrollList->where('status', 'Sudah Dihitung')->count(),
                'belum_dihitung' => $payrollList->where('status', 'Belum Dihitung')->count(),
                'total_gaji_pokok' => $payrollList->sum('gaji_pokok'),
                'total_tunjangan' => $payrollList
                    ->filter(function ($emp) {
                        return $emp['tunjangan_bersih'] > 0;
                    })
                    ->sum('tunjangan_bersih'),
                'total_potongan' => $payrollList->sum('total_potongan'),
                'total_gaji_bersih' => $payrollList->sum('gaji_bersih'),
                'avg_gaji_bersih' => $totalRecords > 0 ? round($payrollList->sum('gaji_bersih') / $totalRecords, 0) : 0,
                'median_gaji_bersih' => $this->calculateMedian($payrollList->pluck('gaji_bersih')->toArray()),
                'new_hire_count' => $payrollList->where('status', 'New Hire')->count(),
                'active_count' => $payrollList->where('status', 'Active')->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $paginated->values(),
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $totalRecords,
                    'last_page' => $lastPage,
                ],
                'summary' => $summary,
                'charts' => [
                    'salary_ranges' => $salaryRanges,
                    'allowance_by_divisi' => $allowanceByDivisi,
                    'monthly_trend' => $monthlyTrend,
                    'top_deductions' => $topDeductions,
                ],
                'period' => [
                    'month' => $bulan,
                    'year' => $tahun,
                    'display' => Carbon::createFromDate($tahun, $bulan, 1)->format('F Y'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Gagal memuat data payroll: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    private function determineEmployeeStatusForPeriod($awalProbation, $resignedAt, $statusAktif, $periodStart, $periodEnd)
    {
        if ($resignedAt && $resignedAt->between($periodStart, $periodEnd)) {
            return 'Resign';
        }

        if ($awalProbation && $awalProbation->between($periodStart, $periodEnd)) {
            return 'New Hire';
        }

        if ($awalProbation && $awalProbation->lt($periodStart->copy()->subMonth())) {
            return 'Active';
        }

        if ($statusAktif == 1) {
            return 'Active';
        }

        return 'Active';
    }

    private function calculateTopDeductions($payrollList, $bulan, $tahun)
    {
        $deductions = TunjanganKaryawan::with('jenistunjangan:id,nama_tunjangan,tipe')
            ->whereIn('id_karyawan', array_column($payrollList->toArray(), 'id'))
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->whereHas('jenistunjangan', function ($q) {
                $q->where('tipe', 'Potongan');
            })
            ->get()
            ->groupBy(function ($item) {
                return optional($item->jenistunjangan)->nama_tunjangan ?? 'Lainnya';
            })
            ->map(function ($items, $nama) {
                return [
                    'nama' => $nama,
                    'total_nilai' => abs($items->sum('total')),
                    'total_karyawan' => $items->count(),
                    'rata_rata' => round(abs($items->sum('total')) / $items->count(), 0),
                ];
            })
            ->sortByDesc('total_nilai')
            ->take(8)
            ->values();

        return [
            'labels' => $deductions->pluck('nama')->toArray(),
            'total_values' => $deductions->pluck('total_nilai')->toArray(),
            'employee_counts' => $deductions->pluck('total_karyawan')->toArray(),
            'averages' => $deductions->pluck('rata_rata')->toArray(),
        ];
    }

    public function exportPayrollExcel(Request $request)
    {
        $bulan = (int) $request->input('month', now()->month);
        $tahun = (int) $request->input('year', now()->year);
        $search = $request->input('search', '');

        ini_set('memory_limit', '512M');
        set_time_limit(300);


        $baseQuery = Karyawan::query()
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->where('status_aktif', '1');

        if (!empty($search)) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('kode_karyawan', 'like', "%{$search}%")
                    ->orWhere('divisi', 'like', "%{$search}%")
                    ->orWhere('jabatan', 'like', "%{$search}%");
            });
        }

        $eligibleKaryawan = $baseQuery->get();
        $eligibleIds = $eligibleKaryawan->pluck('id')->toArray();

        $tunjanganData = TunjanganKaryawan::with('jenistunjangan:id,nama_tunjangan,tipe')->whereIn('id_karyawan', $eligibleIds)->where('bulan', $bulan)->where('tahun', $tahun)->get()->groupBy('id_karyawan');

        $periodStart = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
        $periodEnd = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();

        $payrollList = $eligibleKaryawan->map(function ($emp) use ($tunjanganData, $periodStart, $periodEnd) {
            $items = $tunjanganData->get($emp->id, collect());
            $gajiPokok = (float) ($emp->gaji_pokok ?? ($emp->gaji + $emp->tunjangan_jabatan ?? 0));

            $totalTunjangan = $items->where('jenistunjangan.tipe', 'Tunjangan')->sum('total');
            $totalPotongan = abs($items->where('jenistunjangan.tipe', 'Potongan')->sum('total'));
            $tunjanganBersih = $totalTunjangan - $totalPotongan;

            if ($tunjanganBersih < 0) {
                $tunjanganBersih = 0;
            }

            $gajiBersih = $gajiPokok + $tunjanganBersih;

            $awalProbation = $emp->awal_probation ? Carbon::parse($emp->awal_probation) : null;
            $resignedAt = $emp->resigned_at ? Carbon::parse($emp->resigned_at) : null;

            $status = $this->determineEmployeeStatusForPeriod($awalProbation, $resignedAt, $emp->status_aktif, $periodStart, $periodEnd);

            return [
                'id' => $emp->id,
                'nama' => $emp->nama_lengkap,
                'kode' => $emp->kode_karyawan,
                'divisi' => $emp->divisi ?? '-',
                'jabatan' => $emp->jabatan ?? '-',
                'gaji_pokok' => $gajiPokok,
                'total_tunjangan' => $totalTunjangan,
                'tunjangan_bersih' => $tunjanganBersih,
                'total_potongan' => $totalPotongan,
                'gaji_bersih' => $gajiBersih,
                'status' => $status,
            ];
        });

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Payroll');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F4E79'],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '000000']]],
        ];

        $dataStyle = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ];

        $currencyStyle = [
            'numberFormat' => ['formatCode' => '#,##0'],
        ];

        $totalStyle = [
            'font' => ['bold' => true, 'size' => 11],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E7E6E6'],
            ],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '000000']]],
        ];

        $sheet->mergeCells('A1:K1');
        $sheet->setCellValue('A1', 'LAPORAN PAYROLL');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $bulanNama = date('F', mktime(0, 0, 0, $bulan, 10));
        $sheet->mergeCells('A2:K2');
        $sheet->setCellValue('A2', "Periode: {$bulanNama} {$tahun}");
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2')->getFont()->setItalic(true);
        $sheet->getRowDimension(2)->setRowHeight(20);

        $headers = ['No', 'Nama', 'Kode', 'Divisi', 'Jabatan', 'Gaji Pokok', 'Total Tunjangan', 'Tunjangan Bersih', 'Total Potongan', 'Gaji Bersih', 'Status'];
        $sheet->fromArray($headers, null, 'A3');
        $sheet->getStyle('A3:K3')->applyFromArray($headerStyle);
        $sheet->getRowDimension(3)->setRowHeight(25);

        $rowNum = 4;
        $i = 1;
        $totals = [
            'gaji_pokok' => 0,
            'total_tunjangan' => 0,
            'tunjangan_bersih' => 0,
            'total_potongan' => 0,
            'gaji_bersih' => 0,
        ];

        foreach ($payrollList as $row) {
            $sheet->setCellValue("A{$rowNum}", $i++);
            $sheet->setCellValue("B{$rowNum}", $row['nama']);
            $sheet->setCellValue("C{$rowNum}", $row['kode']);
            $sheet->setCellValue("D{$rowNum}", $row['divisi']);
            $sheet->setCellValue("E{$rowNum}", $row['jabatan']);
            $sheet->setCellValue("F{$rowNum}", $row['gaji_pokok']);
            $sheet->setCellValue("G{$rowNum}", $row['total_tunjangan']);
            $sheet->setCellValue("H{$rowNum}", $row['tunjangan_bersih']);
            $sheet->setCellValue("I{$rowNum}", $row['total_potongan']);
            $sheet->setCellValue("J{$rowNum}", $row['gaji_bersih']);
            $sheet->setCellValue("K{$rowNum}", $row['status']);

            $totals['gaji_pokok'] += (float) $row['gaji_pokok'];
            $totals['total_tunjangan'] += (float) $row['total_tunjangan'];
            $totals['tunjangan_bersih'] += (float) $row['tunjangan_bersih'];
            $totals['total_potongan'] += (float) $row['total_potongan'];
            $totals['gaji_bersih'] += (float) $row['gaji_bersih'];

            $rowNum++;
        }

        $lastRow = $rowNum - 1;
        if ($lastRow >= 4) {
            $sheet->getStyle("A4:K{$lastRow}")->applyFromArray($dataStyle);
            $sheet->getStyle("F4:J{$lastRow}")->applyFromArray($currencyStyle);

            foreach (range('A', 'K') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }

        $totalRow = $rowNum;
        $sheet->mergeCells("A{$totalRow}:E{$totalRow}");
        $sheet->setCellValue("A{$totalRow}", 'TOTAL');
        $sheet->setCellValue("F{$totalRow}", $totals['gaji_pokok']);
        $sheet->setCellValue("G{$totalRow}", $totals['total_tunjangan']);
        $sheet->setCellValue("H{$totalRow}", $totals['tunjangan_bersih']);
        $sheet->setCellValue("I{$totalRow}", $totals['total_potongan']);
        $sheet->setCellValue("J{$totalRow}", $totals['gaji_bersih']);

        $sheet->getStyle("A{$totalRow}:K{$totalRow}")->applyFromArray($totalStyle);
        $sheet->getStyle("F{$totalRow}:J{$totalRow}")->applyFromArray($currencyStyle);
        $sheet
            ->getStyle("A{$totalRow}:E{$totalRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $summaryRow = $totalRow + 2;
        $sheet->mergeCells("A{$summaryRow}:C{$summaryRow}");
        $sheet->setCellValue("A{$summaryRow}", 'Ringkasan:');
        $sheet
            ->getStyle("A{$summaryRow}")
            ->getFont()
            ->setBold(true);

        $summaryRow++;
        $sheet->setCellValue("A{$summaryRow}", 'Total Karyawan:');
        $sheet->setCellValue("B{$summaryRow}", count($payrollList));

        $summaryRow++;
        $sheet->setCellValue("A{$summaryRow}", 'Total Gaji Bersih:');
        $sheet->setCellValue("B{$summaryRow}", $totals['gaji_bersih']);
        $sheet->getStyle("B{$summaryRow}")->applyFromArray($currencyStyle);

        $sheet->getStyle("A{$summaryRow}:B{$summaryRow}")->applyFromArray($dataStyle);

        $filename = "Payroll_{$bulan}_{$tahun}.xlsx";

        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        $writer->save('php://output');
        exit();
    }

    public function exportPayrollPdf(Request $request)
    {
        $bulan = (int) $request->input('month', now()->month);
        $tahun = (int) $request->input('year', now()->year);
        $search = $request->input('search', '');


        $baseQuery = Karyawan::query()
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->where('status_aktif', '1');

        if (!empty($search)) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('kode_karyawan', 'like', "%{$search}%")
                    ->orWhere('divisi', 'like', "%{$search}%")
                    ->orWhere('jabatan', 'like', "%{$search}%");
            });
        }

        $eligibleKaryawan = $baseQuery->get();
        $eligibleIds = $eligibleKaryawan->pluck('id')->toArray();
        $totalEligible = count($eligibleIds);

        $tunjanganData = TunjanganKaryawan::with('jenistunjangan:id,nama_tunjangan,tipe')->whereIn('id_karyawan', $eligibleIds)->where('bulan', $bulan)->where('tahun', $tahun)->get()->groupBy('id_karyawan');

        $periodStart = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
        $periodEnd = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();

        $payrollList = $eligibleKaryawan->map(function ($emp) use ($tunjanganData, $periodStart, $periodEnd) {
            $items = $tunjanganData->get($emp->id, collect());
            $gajiPokok = (float) ($emp->gaji_pokok ?? ($emp->gaji + $emp->tunjangan_jabatan ?? 0));

            $totalTunjangan = $items->where('jenistunjangan.tipe', 'Tunjangan')->sum('total');
            $totalPotongan = abs($items->where('jenistunjangan.tipe', 'Potongan')->sum('total'));
            $tunjanganBersih = $totalTunjangan - $totalPotongan;

            if ($tunjanganBersih < 0) {
                $tunjanganBersih = 0;
            }

            $gajiBersih = $gajiPokok + $tunjanganBersih;

            $awalProbation = $emp->awal_probation ? Carbon::parse($emp->awal_probation) : null;
            $resignedAt = $emp->resigned_at ? Carbon::parse($emp->resigned_at) : null;

            $status = $this->determineEmployeeStatusForPeriod($awalProbation, $resignedAt, $emp->status_aktif, $periodStart, $periodEnd);

            return [
                'id' => $emp->id,
                'nama' => $emp->nama_lengkap,
                'kode' => $emp->kode_karyawan,
                'divisi' => $emp->divisi ?? '-',
                'jabatan' => $emp->jabatan ?? '-',
                'gaji_pokok' => $gajiPokok,
                'total_tunjangan' => $totalTunjangan,
                'tunjangan_bersih' => $tunjanganBersih,
                'total_potongan' => $totalPotongan,
                'gaji_bersih' => $gajiBersih,
                'status' => $status,
            ];
        });

        $summary = [
            'total_karyawan' => $totalEligible,
            'sudah_dihitung' => $payrollList->where('status', 'Sudah Dihitung')->count(),
            'belum_dihitung' => $payrollList->where('status', 'Belum Dihitung')->count(),
            'total_gaji_pokok' => $payrollList->sum('gaji_pokok'),
            'total_tunjangan' => $payrollList
                ->filter(function ($emp) {
                    return $emp['tunjangan_bersih'] > 0;
                })
                ->sum('tunjangan_bersih'),
            'total_potongan' => $payrollList->sum('total_potongan'),
            'total_gaji_bersih' => $payrollList->sum('gaji_bersih'),
            'avg_gaji_bersih' => $payrollList->count() > 0 ? round($payrollList->sum('gaji_bersih') / $payrollList->count(), 0) : 0,
            'median_gaji_bersih' => $this->calculateMedian($payrollList->pluck('gaji_bersih')->toArray()),
            'new_hire_count' => $payrollList->where('status', 'New Hire')->count(),
            'active_count' => $payrollList->where('status', 'Active')->count(),
        ];

        $salaryRanges = $this->calculateSalaryRanges($payrollList);
        $allowanceByDivisi = $this->calculateAllowanceByDivisi($payrollList);
        $monthlyTrend = $this->calculateMonthlyTrend($tahun);
        $topDeductions = $this->calculateTopDeductions($payrollList, $bulan, $tahun);

        $charts = [
            'salary_ranges' => $salaryRanges,
            'allowance_by_divisi' => $allowanceByDivisi,
            'monthly_trend' => $monthlyTrend,
            'top_deductions' => $topDeductions,
        ];

        $period = [
            'month' => $bulan,
            'year' => $tahun,
            'display' => Carbon::createFromDate($tahun, $bulan, 1)->format('F Y'),
        ];

        $pdf = Pdf::loadView('office.HR.exports.payroll_pdf', [
            'data' => $payrollList->values(),
            'summary' => $summary,
            'charts' => $charts,
            'period' => $period['display'],
            'generated_at' => now()->format('d M Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download("laporan_payroll_{$bulan}_{$tahun}.pdf");
    }

    private function calculateSalaryRanges($payrollList)
    {
        $ranges = [
            '< 5 Juta' => ['min' => 0, 'max' => 4999999, 'count' => 0, 'total' => 0],
            '5-10 Juta' => ['min' => 5000000, 'max' => 9999999, 'count' => 0, 'total' => 0],
            '10-20 Juta' => ['min' => 10000000, 'max' => 19999999, 'count' => 0, 'total' => 0],
            '20-50 Juta' => ['min' => 20000000, 'max' => 49999999, 'count' => 0, 'total' => 0],
            '> 50 Juta' => ['min' => 50000000, 'max' => PHP_INT_MAX, 'count' => 0, 'total' => 0],
        ];

        foreach ($payrollList as $emp) {
            $gaji = $emp['gaji_bersih'];
            foreach ($ranges as $label => $range) {
                if ($gaji >= $range['min'] && $gaji <= $range['max']) {
                    $ranges[$label]['count']++;
                    $ranges[$label]['total'] += $gaji;
                    break;
                }
            }
        }

        return [
            'labels' => array_keys($ranges),
            'counts' => array_values(array_map(fn($r) => $r['count'], $ranges)),
            'averages' => array_values(array_map(fn($r) => $r['count'] > 0 ? round($r['total'] / $r['count'], 0) : 0, $ranges)),
        ];
    }

    private function calculateAllowanceByDivisi($payrollList)
    {
        $divisi = [];
        foreach ($payrollList as $emp) {
            $d = $emp['divisi'];
            if (!isset($divisi[$d])) {
                $divisi[$d] = ['count' => 0, 'total_allowance' => 0, 'total_salary' => 0];
            }
            $divisi[$d]['count']++;
            $divisi[$d]['total_allowance'] += $emp['total_tunjangan'];
            $divisi[$d]['total_salary'] += $emp['gaji_bersih'];
        }

        $top = collect($divisi)->sortByDesc('total_allowance')->take(8);
        return [
            'labels' => $top->keys()->toArray(),
            'allowance' => $top->pluck('total_allowance')->values()->toArray(),
            'avg_salary' => $top->map(fn($v) => $v['count'] > 0 ? round($v['total_salary'] / $v['count'], 0) : 0)->values()->toArray(),
        ];
    }

    private function calculateMonthlyTrend($tahun)
    {
        $trend = [];
        for ($m = 1; $m <= 12; $m++) {
            $periodStart = Carbon::createFromDate($tahun, $m, 1)->startOfMonth();
            $periodEnd = Carbon::createFromDate($tahun, $m, 1)->endOfMonth();

            $newHires = Karyawan::whereNotNull('awal_probation')
                ->whereBetween('awal_probation', [$periodStart, $periodEnd])
                ->where('status_aktif', '1')
                ->count();

            $actives = Karyawan::where('status_aktif', '1')
                ->where(function ($q) use ($periodStart) {
                    $q->whereNull('awal_probation')->orWhere('awal_probation', '<', $periodStart->copy()->subMonth());
                })
                ->count();

            $resigns = Karyawan::where('status_aktif', '0')
                ->whereBetween('resigned_at', [$periodStart, $periodEnd])
                ->count();

            $trend[] = [
                'month' => Carbon::createFromDate($tahun, $m, 1)->format('M'),
                'new_hires' => $newHires,
                'actives' => $actives,
                'resigns' => $resigns,
                'total_gaji' => (float) (LogGaji::where('bulan', $m)->where('tahun', $tahun)->selectRaw('SUM(gaji + tunjangan_jabatan) as total')->value('total') ?? 0),
            ];
        }
        return $trend;
    }

    private function calculateMedian($values)
    {
        if (empty($values)) {
            return 0;
        }
        sort($values);
        $count = count($values);
        $mid = floor($count / 2);
        return $count % 2 === 0 ? round(($values[$mid - 1] + $values[$mid]) / 2, 0) : round($values[$mid], 0);
    }

    public function indexPerhitungan(Request $request)
    {
        $karyawans = Karyawan::with('divisi')
            ->where('status_aktif', '1')
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->orderBy('nama_lengkap')
            ->get();

        $payrollsCollection = PerhitunganTunjanganHR::with(['karyawan.divisi', 'createdBy'])
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get();

        $bulanSekarang = date('n');
        $tahunSekarang = date('Y');

        $payrollBulanIni = $payrollsCollection->where('bulan', $bulanSekarang)->where('tahun', $tahunSekarang);

        $totalKaryawan = $karyawans->count();
        $sudahPayroll = $payrollBulanIni->count();
        $belumPayroll = $totalKaryawan - $sudahPayroll;
        $totalGaji = $karyawans->sum('gaji') + $karyawans->sum('tunjangan_jabatan');

        return view('HR.payroll.indexPerhitungan', compact('karyawans', 'payrollsCollection', 'totalKaryawan', 'sudahPayroll', 'belumPayroll', 'totalGaji', 'bulanSekarang', 'tahunSekarang'));
    }

    public function getKaryawanDataPerhitungan(Request $request)
    {
        $request->validate([
            'karyawan_id' => 'required|exists:karyawans,id',
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer',
        ]);

        $karyawan = karyawan::with('divisi')->findOrFail($request->karyawan_id);

        $pph21Data = Pph21Karyawan::where('karyawan_id', $request->karyawan_id)->first();
        $gajiDasar = (int) ($karyawan->gaji_pokok ?? ($karyawan->gaji + $karyawan->tunjangan_jabatan ?? 0));
        $pph21Bulanan = 0;
        
        if ($pph21Data && $pph21Data->ptkp) {
            $pph21Bulanan = $this->hitungPph21Bulanan($gajiDasar, $pph21Data->ptkp);
        }

        $tunjanganList = TunjanganKaryawan::where('id_karyawan', $request->karyawan_id)
            ->where('bulan', $request->bulan)
            ->where('tahun', $request->tahun)
            ->whereHas('jenistunjangan', function ($query) {
                $query->where('tipe', 'Tunjangan');
            })
            ->with('jenistunjangan')
            ->get();

        $tunjanganDetail = $tunjanganList
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'jenis_tunjangan_id' => $t->jenis_tunjangan,
                    'nama' => $t->jenistunjangan?->nama_tunjangan ?? ($t->keterangan ?? 'Lainnya'),
                    'total' => (int) $t->total,
                ];
            })
            ->values()
            ->toArray();

        $existingPayroll = PerhitunganTunjanganHR::where('karyawan_id', $request->karyawan_id)
            ->where('bulan', $request->bulan)
            ->where('tahun', $request->tahun)
            ->first();

        $defaultUmk = $existingPayroll->umk_bandung ?? 2100000;
        $defaultGajiPokok = $existingPayroll->gaji_pokok ?? $gajiDasar;
        $defaultSalaryBpjstk = $existingPayroll->salary_bpjstk ?? $defaultGajiPokok;

        $defaultPph21 = $existingPayroll->potongan_pph21 ?? $pph21Bulanan;

        if ($existingPayroll && !empty($existingPayroll->tunjangan_detail)) {
            $tunjanganDetail = $existingPayroll->tunjangan_detail;
        }

        return response()->json([
            'success' => true,
            'karyawan' => [
                'id' => $karyawan->id,
                'nip' => $karyawan->nip ?? '-',
                'nama' => $karyawan->nama_lengkap,
                'jabatan' => $karyawan->jabatan ?? '-',
                'divisi' => $karyawan->divisi->nama_divisi ?? '-',
                'gaji_pokok' => $defaultGajiPokok,
                'salary_bpjstk' => $defaultSalaryBpjstk,
                'umk_bandung' => $defaultUmk,
                'pph21_bulanan' => $defaultPph21,
                'has_pph21' => $pph21Data ? true : false,
            ],
            'tunjangan_detail' => $tunjanganDetail,
            'has_tunjangan' => $tunjanganList->isNotEmpty() || ($existingPayroll && !empty($existingPayroll->tunjangan_detail)),
            'existing_payroll' => $existingPayroll ? ['id' => $existingPayroll->id, 'status' => $existingPayroll->status] : null,
        ]);
    }

    private function normalizeTunjangan($tunjangan)
    {
        $result = [];
        foreach ((array) $tunjangan as $t) {
            $total = (int) ($t['total'] ?? 0);
            $nama = trim($t['nama'] ?? '');
            if ($total <= 0 && $nama === '') {
                continue;
            }
            $result[] = [
                'jenis_tunjangan_id' => isset($t['jenis_tunjangan_id']) && $t['jenis_tunjangan_id'] !== '' ? (int) $t['jenis_tunjangan_id'] : null,
                'nama' => $nama !== '' ? $nama : 'Lainnya',
                'total' => $total,
            ];
        }
        return $result;
    }

    public function storePerhitungan(Request $request)
    {
        $request->validate([
            'karyawan_id' => 'required|exists:karyawans,id',
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer',
            'gaji_pokok' => 'required|numeric|min:0',
            'salary_bpjstk' => 'required|numeric|min:0',
            'umk_bandung' => 'required|numeric|min:0',
            'tunjangan' => 'nullable|array',
            'tunjangan.*.jenis_tunjangan_id' => 'nullable',
            'tunjangan.*.nama' => 'nullable|string',
            'tunjangan.*.total' => 'nullable|numeric',
        ]);

        $exists = PerhitunganTunjanganHR::where('karyawan_id', $request->karyawan_id)->where('bulan', $request->bulan)->where('tahun', $request->tahun)->first();

        $umkBandung = $request->umk_bandung ?: 2100000;

        DB::beginTransaction();
        try {
            $bpjs = $this->calculateBPJSPerhitungan($request->salary_bpjstk, $umkBandung);

            $tunjanganDetail = $this->normalizeTunjangan($request->tunjangan ?? []);
            $totalTunjangan = array_sum(array_column($tunjanganDetail, 'total'));

            $potongan = $request->potongan ?? [];
            $potonganPph = (int) ($potongan['pph21'] ?? 0);
            $potonganKasbon = (int) ($potongan['kasbon'] ?? 0);
            $potonganDenda = (int) ($potongan['denda'] ?? 0);
            $potonganLain = (int) ($potongan['lain'] ?? 0);
            $totalPotLain = $potonganPph + $potonganKasbon + $potonganDenda + $potonganLain;

            $thpKotor = $request->gaji_pokok + $totalTunjangan;
            $thpBersih = $thpKotor - $bpjs['total_bpjs_karyawan'] - $totalPotLain;
            $totalBiayaPerusahaan = $thpKotor + $bpjs['total_bpjs_perusahaan'];

            $perhitungan = PerhitunganTunjanganHR::create([
                'karyawan_id' => $request->karyawan_id,
                'bulan' => $request->bulan,
                'tahun' => $request->tahun,
                'gaji_pokok' => $request->gaji_pokok,
                'salary_bpjstk' => $request->salary_bpjstk,
                'umk_bandung' => $umkBandung,
                'tunjangan_detail' => $tunjanganDetail,
                'total_tunjangan' => $totalTunjangan,
                'jht_perusahaan' => $bpjs['jht_perusahaan'],
                'jkm_perusahaan' => $bpjs['jkm_perusahaan'],
                'jkk_perusahaan' => $bpjs['jkk_perusahaan'],
                'jp_perusahaan' => $bpjs['jp_perusahaan'],
                'total_bpjstk_perusahaan' => $bpjs['total_bpjstk_perusahaan'],
                'jht_karyawan' => $bpjs['jht_karyawan'],
                'jp_karyawan' => $bpjs['jp_karyawan'],
                'total_bpjstk_karyawan' => $bpjs['total_bpjstk_karyawan'],
                'bpjs_kes_perusahaan' => $bpjs['bpjs_kes_perusahaan'],
                'bpjs_kes_karyawan' => $bpjs['bpjs_kes_karyawan'],
                'total_bpjs_perusahaan' => $bpjs['total_bpjs_perusahaan'],
                'total_bpjs_karyawan' => $bpjs['total_bpjs_karyawan'],
                'potongan_pph21' => $potonganPph,
                'potongan_kasbon' => $potonganKasbon,
                'potongan_denda' => $potonganDenda,
                'potongan_lain' => $potonganLain,
                'total_potongan_lain' => $totalPotLain,
                'thp_kotor' => $thpKotor,
                'thp_bersih' => $thpBersih,
                'total_biaya_perusahaan' => $totalBiayaPerusahaan,
                'status' => 'calculated',
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payroll berhasil dibuat!',
                'data' => $perhitungan->load(['karyawan.divisi', 'createdBy']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respondError('Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function showPerhitungan($id)
    {
        $perhitungan = PerhitunganTunjanganHR::with(['karyawan.divisi', 'createdBy', 'approvedBy', 'updatedBy'])->findOrFail($id);

        $tunjanganList = TunjanganKaryawan::where('id_karyawan', $perhitungan->karyawan_id)->where('bulan', $perhitungan->bulan)->where('tahun', $perhitungan->tahun)->with('jenistunjangan')->get();

        return response()->json([
            'success' => true,
            'data' => $perhitungan,
            'tunjangan' => $tunjanganList,
        ]);
    }

    public function updatePerhitungan(Request $request, $id)
    {
        $perhitungan = PerhitunganTunjanganHR::findOrFail($id);

        if (!$perhitungan->canBeEdited()) {
            return $this->respondError('Payroll dengan status ' . $perhitungan->status . ' tidak dapat diedit!');
        }

        $request->validate([
            'gaji_pokok' => 'required|numeric|min:0',
            'salary_bpjstk' => 'required|numeric|min:0',
            'umk_bandung' => 'required|numeric|min:0',
            'tunjangan' => 'nullable|array',
            'tunjangan.*.jenis_tunjangan_id' => 'nullable',
            'tunjangan.*.nama' => 'nullable|string',
            'tunjangan.*.total' => 'nullable|numeric',
        ]);

        DB::beginTransaction();
        try {
            $umkBandung = $request->umk_bandung ?: $perhitungan->umk_bandung;
            $bpjs = $this->calculateBPJSPerhitungan($request->salary_bpjstk, $umkBandung);

            $tunjanganDetail = $this->normalizeTunjangan($request->tunjangan ?? []);
            $totalTunjangan = array_sum(array_column($tunjanganDetail, 'total'));

            $potongan = $request->potongan ?? [];
            $potonganPph = (int) ($potongan['pph21'] ?? 0);
            $potonganKasbon = (int) ($potongan['kasbon'] ?? 0);
            $potonganDenda = (int) ($potongan['denda'] ?? 0);
            $potonganLain = (int) ($potongan['lain'] ?? 0);
            $totalPotLain = $potonganPph + $potonganKasbon + $potonganDenda + $potonganLain;

            $thpKotor = $request->gaji_pokok + $totalTunjangan;
            $thpBersih = $thpKotor - $bpjs['total_bpjs_karyawan'] - $totalPotLain;
            $totalBiayaPerusahaan = $thpKotor + $bpjs['total_bpjs_perusahaan'];

            $perhitungan->update([
                'gaji_pokok' => $request->gaji_pokok,
                'salary_bpjstk' => $request->salary_bpjstk,
                'umk_bandung' => $umkBandung,
                'tunjangan_detail' => $tunjanganDetail,
                'total_tunjangan' => $totalTunjangan,
                'jht_perusahaan' => $bpjs['jht_perusahaan'],
                'jkm_perusahaan' => $bpjs['jkm_perusahaan'],
                'jkk_perusahaan' => $bpjs['jkk_perusahaan'],
                'jp_perusahaan' => $bpjs['jp_perusahaan'],
                'total_bpjstk_perusahaan' => $bpjs['total_bpjstk_perusahaan'],
                'jht_karyawan' => $bpjs['jht_karyawan'],
                'jp_karyawan' => $bpjs['jp_karyawan'],
                'total_bpjstk_karyawan' => $bpjs['total_bpjstk_karyawan'],
                'bpjs_kes_perusahaan' => $bpjs['bpjs_kes_perusahaan'],
                'bpjs_kes_karyawan' => $bpjs['bpjs_kes_karyawan'],
                'total_bpjs_perusahaan' => $bpjs['total_bpjs_perusahaan'],
                'total_bpjs_karyawan' => $bpjs['total_bpjs_karyawan'],
                'potongan_pph21' => $potonganPph,
                'potongan_kasbon' => $potonganKasbon,
                'potongan_denda' => $potonganDenda,
                'potongan_lain' => $potonganLain,
                'total_potongan_lain' => $totalPotLain,
                'thp_kotor' => $thpKotor,
                'thp_bersih' => $thpBersih,
                'total_biaya_perusahaan' => $totalBiayaPerusahaan,
                'updated_by' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payroll berhasil diupdate!',
                'data' => $perhitungan->fresh()->load(['karyawan.divisi', 'createdBy']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respondError('Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function approvePerhitungan($id)
    {
        $perhitungan = PerhitunganTunjanganHR::findOrFail($id);

        if (!in_array($perhitungan->status, ['calculated', 'draft'])) {
            return $this->respondError('Status payroll tidak valid untuk di-approve!');
        }

        $perhitungan->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Payroll berhasil disetujui!']);
    }

    public function destroyPerhitungan($id)
    {
        $perhitungan = PerhitunganTunjanganHR::findOrFail($id);

        if ($perhitungan->status === 'paid') {
            return $this->respondError('Payroll yang sudah dibayar tidak dapat dihapus!');
        }

        DB::beginTransaction();
        try {
            $perhitungan->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Payroll berhasil dihapus!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respondError('Gagal menghapus: ' . $e->getMessage());
        }
    }

    public function getPayrollDataPerhitungan(Request $request)
    {
        $bulan = $request->get('bulan');
        $tahun = $request->get('tahun');
        $divisi = $request->get('divisi');
        $status = $request->get('status');

        $karyawans = karyawan::where('status_aktif', '1')->with('divisi')->orderBy('nama_lengkap')->get();

        $payrollQuery = PerhitunganTunjanganHR::with(['karyawan.divisi', 'createdBy'])
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc');

        if ($bulan) {
            $payrollQuery->where('bulan', $bulan);
        }
        if ($tahun) {
            $payrollQuery->where('tahun', $tahun);
        }
        if ($status && !in_array($status, ['sudah', 'belum'])) {
            $payrollQuery->where('status', $status);
        }

        $payrolls = $payrollQuery->get();

        $tunjanganKaryawanQuery = TunjanganKaryawan::with('jenistunjangan');

        if ($bulan) {
            $tunjanganKaryawanQuery->where('bulan', $bulan);
        }
        if ($tahun) {
            $tunjanganKaryawanQuery->where('tahun', $tahun);
        }

        $tunjanganKaryawanAll = $tunjanganKaryawanQuery->get();
        $tunjanganByKaryawan = $tunjanganKaryawanAll->groupBy('id_karyawan');

        $rows = [];
        foreach ($karyawans as $emp) {
            if ($divisi && ($emp->divisi->nama_divisi ?? '-') !== $divisi) {
                continue;
            }

            $payroll = $payrolls->first(fn($p) => $p->karyawan_id === $emp->id && (!$bulan || $p->bulan == $bulan) && (!$tahun || $p->tahun == $tahun));

            if ($status === 'sudah' && !$payroll) {
                continue;
            }
            if ($status === 'belum' && $payroll) {
                continue;
            }

            $tunjanganData = null;

            if ($payroll) {
                $detail = $payroll->tunjangan_detail ?? [];
                $totalTunjangan = (int) ($payroll->total_tunjangan ?? array_sum(array_column($detail, 'total')));

                $tunjanganData = [
                    'total' => $totalTunjangan,
                    'source' => 'payroll',
                    'detail' => $detail,
                ];
            } else {
                $tunjanganList = $tunjanganByKaryawan->get($emp->id, collect());

                if ($tunjanganList->isNotEmpty()) {
                    $detail = $tunjanganList
                        ->map(function ($t) {
                            return [
                                'jenis_tunjangan_id' => $t->jenis_tunjangan,
                                'nama' => $t->jenistunjangan?->nama_tunjangan ?? ($t->keterangan ?? 'Lainnya'),
                                'total' => (int) $t->total,
                            ];
                        })
                        ->values()
                        ->toArray();

                    $tunjanganData = [
                        'total' => array_sum(array_column($detail, 'total')),
                        'source' => 'tunjangan_karyawan',
                        'detail' => $detail,
                    ];
                }
            }

            $rows[] = [
                'karyawan_id' => $emp->id,
                'nip' => $emp->nip ?? '-',
                'nama' => $emp->nama_lengkap,
                'jabatan' => $emp->jabatan ?? '-',
                'divisi' => $emp->divisi->nama_divisi ?? '-',
                'status_aktif' => $emp->status_aktif == '1' ? 'Aktif' : 'Nonaktif',
                'gaji_pokok' => (int) ($emp->gaji + $emp->tunjangan_jabatan ?? 0),
                'tunjangan_preview' => $tunjanganData,
                'payroll' => $payroll
                    ? [
                        'id' => $payroll->id,
                        'bulan' => $payroll->bulan,
                        'tahun' => $payroll->tahun,
                        'thp_bersih' => $payroll->thp_bersih,
                        'thp_kotor' => $payroll->thp_kotor,
                        'status' => $payroll->status,
                        'created_at' => $payroll->created_at?->format('d/m/Y H:i'),
                        'created_by' => $payroll->createdBy?->name ?? '-',
                        'total_bpjs_perusahaan' => $payroll->total_bpjs_perusahaan,
                        'total_bpjs_karyawan' => $payroll->total_bpjs_karyawan,
                        'tunjangan' => $tunjanganData,
                    ]
                    : null,
            ];
        }

        return response()->json(['success' => true, 'data' => $rows]);
    }

    public function getStatsPerhitungan(Request $request)
    {
        $bulan = $request->get('bulan');
        $tahun = $request->get('tahun');
        $divisi = $request->get('divisi');

        $query = PerhitunganTunjanganHR::with('karyawan.divisi');
        if ($bulan) {
            $query->where('bulan', $bulan);
        }
        if ($tahun) {
            $query->where('tahun', $tahun);
        }

        $payrolls = $query->get();

        if ($divisi) {
            $payrolls = $payrolls->filter(fn($p) => ($p->karyawan->divisi->nama_divisi ?? '-') === $divisi);
        }

        $totalGajiBulanan = $payrolls->sum('gaji_pokok');
        $totalBPJSTKPerusahaan = $payrolls->sum(function ($p) {
            return ($p->jht_perusahaan ?? 0) + ($p->jkm_perusahaan ?? 0) + ($p->jkk_perusahaan ?? 0) + ($p->jp_perusahaan ?? 0);
        });
        $totalBPJSKesPerusahaan = $payrolls->sum('bpjs_kes_perusahaan');
        $totalBPJSTKKaryawan = $payrolls->sum(function ($p) {
            return ($p->jht_karyawan ?? 0) + ($p->jp_karyawan ?? 0);
        });
        $totalBPJSKesKaryawan = $payrolls->sum('bpjs_kes_karyawan');

        $totalDitanggungPerusahaan = $totalBPJSTKPerusahaan + $totalBPJSKesPerusahaan;
        $totalDitanggungKaryawan = $totalBPJSTKKaryawan + $totalBPJSKesKaryawan;

        $totalGajiTahunan = $totalGajiBulanan * 12;
        $batasMaksimal = $totalGajiTahunan * 0.4;
        $totalBPJSTahunan = $totalDitanggungPerusahaan * 12;
        $persentase = $totalGajiBulanan > 0 ? ($totalDitanggungPerusahaan / $totalGajiBulanan) * 100 : 0;

        $divisiStats = $payrolls
            ->groupBy(fn($p) => $p->karyawan->divisi->nama_divisi ?? 'Lainnya')
            ->map(
                fn($group) => [
                    'count' => $group->count(),
                    'total_thp' => $group->sum('thp_bersih'),
                    'total_bpjs_perusahaan' => $group->sum('total_bpjs_perusahaan'),
                    'total_bpjs_karyawan' => $group->sum('total_bpjs_karyawan'),
                    'avg_thp' => $group->avg('thp_bersih'),
                ],
            )
            ->toArray();

        $trendQuery = PerhitunganTunjanganHR::selectRaw(
            '
                bulan,
                SUM(thp_kotor) as total_thp,
                SUM(total_bpjs_perusahaan) as total_bpjs_per,
                SUM(total_bpjs_karyawan) as total_bpjs_kar,
                COUNT(*) as jumlah
            ',
        )
            ->where('tahun', $tahun ?: date('Y'))
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        return response()->json([
            'success' => true,
            'total_payroll' => $payrolls->sum('thp_kotor'),
            'total_bpjs_perusahaan' => $payrolls->sum('total_bpjs_perusahaan'),
            'total_bpjs_karyawan' => $payrolls->sum('total_bpjs_karyawan'),
            'total_thp_bersih' => $payrolls->sum('thp_bersih'),
            'avg_thp' => $payrolls->avg('thp_bersih') ?? 0,
            'count' => $payrolls->count(),
            'divisi_stats' => $divisiStats,
            'trend' => $trendQuery,
            'bpjs_breakdown' => [
                'jht_perusahaan' => $payrolls->sum('jht_perusahaan'),
                'jkm_perusahaan' => $payrolls->sum('jkm_perusahaan'),
                'jkk_perusahaan' => $payrolls->sum('jkk_perusahaan'),
                'jp_perusahaan' => $payrolls->sum('jp_perusahaan'),
                'kes_perusahaan' => $payrolls->sum('bpjs_kes_perusahaan'),
                'jht_karyawan' => $payrolls->sum('jht_karyawan'),
                'jp_karyawan' => $payrolls->sum('jp_karyawan'),
                'kes_karyawan' => $payrolls->sum('bpjs_kes_karyawan'),
            ],
            'ringkasan_biaya' => [
                'total_gaji_bulanan' => $totalGajiBulanan,
                'total_gaji_tahunan' => $totalGajiTahunan,
                'batas_maksimal' => $batasMaksimal,
                'total_bpjs_tk_perusahaan' => $totalBPJSTKPerusahaan,
                'total_bpjs_kes_perusahaan' => $totalBPJSKesPerusahaan,
                'total_bpjs_tk_karyawan' => $totalBPJSTKKaryawan,
                'total_bpjs_kes_karyawan' => $totalBPJSKesKaryawan,
                'total_ditanggung_perusahaan' => $totalDitanggungPerusahaan,
                'total_ditanggung_karyawan' => $totalDitanggungKaryawan,
                'total_bpjs_tahunan' => $totalBPJSTahunan,
                'persentase' => $persentase,
            ],
        ]);
    }

    public function calculateBPJSPerhitungan($salaryBPJSTK, $umkBandung)
    {
        $salaryBPJSTK = (float) $salaryBPJSTK;
        $umkBandung = (float) $umkBandung;

        $jhtPerusahaan = (int) round(($salaryBPJSTK * 3.7) / 100);
        $jkmPerusahaan = (int) round(($salaryBPJSTK * 0.3) / 100);
        $jkkPerusahaan = (int) round(($salaryBPJSTK * 0.24) / 100);
        $jpPerusahaan = (int) round(($salaryBPJSTK * 2) / 100);
        $totalBPJSTKPerusahaan = $jhtPerusahaan + $jkmPerusahaan + $jkkPerusahaan + $jpPerusahaan;

        $jhtKaryawan = (int) round(($salaryBPJSTK * 2) / 100);
        $jpKaryawan = (int) round(($salaryBPJSTK * 1) / 100);
        $totalBPJSTKKaryawan = $jhtKaryawan + $jpKaryawan;

        $bpjsKesPerusahaan = (int) round(($umkBandung * 4) / 100);
        $bpjsKesKaryawan = (int) round(($umkBandung * 1) / 100);

        $totalBPJSPerusahaan = $totalBPJSTKPerusahaan + $bpjsKesPerusahaan;
        $totalBPJSKaryawan = $totalBPJSTKKaryawan + $bpjsKesKaryawan;

        return [
            'jht_perusahaan' => $jhtPerusahaan,
            'jkm_perusahaan' => $jkmPerusahaan,
            'jkk_perusahaan' => $jkkPerusahaan,
            'jp_perusahaan' => $jpPerusahaan,
            'total_bpjstk_perusahaan' => $totalBPJSTKPerusahaan,
            'jht_karyawan' => $jhtKaryawan,
            'jp_karyawan' => $jpKaryawan,
            'total_bpjstk_karyawan' => $totalBPJSTKKaryawan,
            'bpjs_kes_perusahaan' => $bpjsKesPerusahaan,
            'bpjs_kes_karyawan' => $bpjsKesKaryawan,
            'total_bpjs_perusahaan' => $totalBPJSPerusahaan,
            'total_bpjs_karyawan' => $totalBPJSKaryawan,
        ];
    }

    private function hitungPph21Bulanan($gajiDasar, $ptkpCode)
    {
        $ptkpBulanan = [
            'TK/0' => 4500000, 'TK/1' => 4875000, 'TK/2' => 5250000, 'TK/3' => 5625000,
            'K/0' => 4875000, 'K/1' => 5250000, 'K/2' => 5625000, 'K/3' => 6000000,
        ];

        if (!isset($ptkpBulanan[$ptkpCode]) || !$gajiDasar) {
            return 0;
        }

        $gajiTahunan = $gajiDasar * 12;
        $ptkpTahunan = $ptkpBulanan[$ptkpCode] * 12;
        $pkp = max(0, $gajiTahunan - $ptkpTahunan);

        $layers = [
            ['limit' => 60000000, 'rate' => 0.05],
            ['limit' => 250000000, 'rate' => 0.15],
            ['limit' => 500000000, 'rate' => 0.25],
            ['limit' => 5000000000, 'rate' => 0.30],
            ['limit' => PHP_INT_MAX, 'rate' => 0.35],
        ];

        $pphTahunan = 0;
        $prevLimit = 0;
        foreach ($layers as $layer) {
            if ($pkp <= 0) break;
            $taxable = min($pkp, $layer['limit'] - $prevLimit);
            $pphTahunan += $taxable * $layer['rate'];
            $pkp -= $taxable;
            $prevLimit = $layer['limit'];
        }

        return (int) round($pphTahunan / 12);
    }

    private function respondError($message, $code = 422)
    {
        return response()->json(['success' => false, 'message' => $message], $code);
    }

    public function exportExcelPerhitungan(Request $request)
    {
        $data = $this->buildExportData($request);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->getPageMargins()->setTop(0.5);
        $sheet->getPageMargins()->setRight(0.5);
        $sheet->getPageMargins()->setLeft(0.5);
        $sheet->getPageMargins()->setBottom(0.5);

        $spreadsheet->getActiveSheet()->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $spreadsheet->getActiveSheet()->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

        $row = 1;
        $col = 1;

        $sheet->setCellValueByColumnAndRow($col, $row, 'LAPORAN PERHITUNGAN PAYROLL DAN BPJS');
        $sheet->mergeCellsByColumnAndRow(1, $row, 22, $row);
        $sheet->getStyleByColumnAndRow($col, $row)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyleByColumnAndRow($col, $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;

        $sheet->setCellValueByColumnAndRow($col, $row, 'Periode: ' . $data['periode_label']);
        $sheet->mergeCellsByColumnAndRow(1, $row, 22, $row);
        $sheet->getStyleByColumnAndRow($col, $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;

        $sheet->setCellValueByColumnAndRow($col, $row, 'Dicetak: ' . now()->format('d M Y H:i'));
        $sheet->mergeCellsByColumnAndRow(1, $row, 22, $row);
        $row++;
        $row++;

        $headers = ['No', 'Nama', 'Status', 'Salary Bulan', 'Salary BPJSTK', 'Tunjangan', 'THP', 'UMK Bandung', 'JHT Per (3.70%)', 'JKM Per (0.30%)', 'JKK Per (0.24%)', 'JP Per (2.00%)', 'Total BPJS Per', 'JHT Kar (2.00%)', 'JP Kar (1.00%)', 'Total BPJS Kar', 'Total Per+Kar', 'BPJS Kes Per (4%)', 'BPJS Kes Kar (1%)', 'Ditanggung Per', 'Ditanggung Kar', 'Salary THP Kar'];

        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($col, $row, $header);
            $col++;
        }

        $sheet->getStyleByColumnAndRow(1, $row, 22, $row)->getFont()->setBold(true);
        $sheet->getStyleByColumnAndRow(1, $row, 22, $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyleByColumnAndRow(1, $row, 22, $row)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyleByColumnAndRow(1, $row, 22, $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $row++;

        $totalGaji = 0;
        $totalDitanggungPer = 0;
        $totalDitanggungKar = 0;
        $no = 1;

        foreach ($data['rows'] as $row_data) {
            $col = 1;
            $sheet->setCellValueByColumnAndRow($col++, $row, $no++);
            $sheet->setCellValueByColumnAndRow($col++, $row, $row_data['nama']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $row_data['status']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $row_data['gaji_pokok']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $row_data['salary_bpjstk']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $row_data['total_tunjangan']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $row_data['thp_bersih']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $row_data['umk_bandung']);

            $sheet->setCellValueByColumnAndRow($col++, $row, $row_data['jht_perusahaan']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $row_data['jkm_perusahaan']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $row_data['jkk_perusahaan']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $row_data['jp_perusahaan']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $row_data['total_bpjs_perusahaan']);

            $sheet->setCellValueByColumnAndRow($col++, $row, $row_data['jht_karyawan']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $row_data['jp_karyawan']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $row_data['total_bpjs_karyawan']);

            $total_bpjs = $row_data['total_bpjs_perusahaan'] + $row_data['total_bpjs_karyawan'];
            $sheet->setCellValueByColumnAndRow($col++, $row, $total_bpjs);

            $sheet->setCellValueByColumnAndRow($col++, $row, $row_data['bpjs_kes_perusahaan']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $row_data['bpjs_kes_karyawan']);

            $ditanggung_per = $row_data['total_bpjs_perusahaan'] + $row_data['bpjs_kes_perusahaan'];
            $ditanggung_kar = $row_data['total_bpjs_karyawan'] + $row_data['bpjs_kes_karyawan'];

            $sheet->setCellValueByColumnAndRow($col++, $row, $ditanggung_per);
            $sheet->setCellValueByColumnAndRow($col++, $row, $ditanggung_kar);
            $sheet->setCellValueByColumnAndRow($col++, $row, $row_data['thp_bersih']);

            $sheet->getStyleByColumnAndRow(1, $row, 22, $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyleByColumnAndRow(4, $row, 22, $row)->getNumberFormat()->setFormatCode('#,##0.##');
            $sheet->getStyleByColumnAndRow(4, $row, 22, $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $totalGaji += $row_data['gaji_pokok'];
            $totalDitanggungPer += $ditanggung_per;
            $totalDitanggungKar += $ditanggung_kar;
            $row++;
        }

        $col = 1;
        $sheet->setCellValueByColumnAndRow($col++, $row, 'TOTAL:');
        $sheet->mergeCellsByColumnAndRow(1, $row, 3, $row);
        $sheet->getStyleByColumnAndRow(1, $row, 3, $row)->getFont()->setBold(true);
        $sheet->setCellValueByColumnAndRow($col, $row, $totalGaji);
        $sheet->getStyleByColumnAndRow($col, $row)->getFont()->setBold(true);
        $sheet->getStyleByColumnAndRow($col, $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyleByColumnAndRow(1, $row, 22, $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $row += 3;

        $sheet->setCellValueByColumnAndRow(1, $row, 'RINGKASAN TOTAL BIAYA BPJS');
        $sheet->mergeCellsByColumnAndRow(1, $row, 2, $row);
        $sheet->getStyleByColumnAndRow(1, $row, 2, $row)->getFont()->setBold(true);
        $row++;

        $sheet->setCellValueByColumnAndRow(1, $row, 'Total Gaji Karyawan dalam 1 Tahun');
        $sheet->setCellValueByColumnAndRow(2, $row, $totalGaji * 12);
        $sheet->getStyleByColumnAndRow(2, $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyleByColumnAndRow(1, $row, 2, $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $row++;

        $sheet->setCellValueByColumnAndRow(1, $row, 'Batas Maksimal (maks 40%)');
        $sheet->setCellValueByColumnAndRow(2, $row, $totalGaji * 12 * 0.4);
        $sheet->getStyleByColumnAndRow(2, $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyleByColumnAndRow(1, $row, 2, $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $row++;

        $sheet->setCellValueByColumnAndRow(1, $row, 'Total BPJS TK & Kes ditanggung pers. Dalam 1 tahun');
        $sheet->setCellValueByColumnAndRow(2, $row, $totalDitanggungPer * 12);
        $sheet->getStyleByColumnAndRow(2, $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyleByColumnAndRow(1, $row, 2, $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $row++;

        $percentage = $totalGaji > 0 ? ($totalDitanggungPer / $totalGaji) * 100 : 0;

        $sheet->setCellValueByColumnAndRow(1, $row, 'Persentase');
        $sheet->setCellValueByColumnAndRow(2, $row, $percentage);
        $sheet->getStyleByColumnAndRow(2, $row)->getNumberFormat()->setFormatCode('0.00');

        $sheet->getStyleByColumnAndRow(1, $row, 2, $row)->getFont()->setBold(true);

        $sheet->getStyleByColumnAndRow(1, $row, 2, $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $style = $sheet->getStyleByColumnAndRow(2, $row);

        if ($percentage > 40) {
            $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF0000');

            $style->getFont()->getColor()->setARGB(Color::COLOR_WHITE);
        } else {
            $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF00B050');

            $style->getFont()->getColor()->setARGB(Color::COLOR_WHITE);
        }

        foreach (range('A', 'V') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Laporan_Payroll_' . ($data['periode_label'] ?? 'Semua') . '_' . date('Ymd') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }

    public function exportPdfPerhitungan(Request $request)
    {
        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Library dompdf belum terinstall',
                ],
                500,
            );
        }

        $data = $this->buildExportData($request);
        $filename = 'Laporan_Payroll_' . ($data['periode_label'] ?? 'Semua') . '_' . date('Ymd') . '.pdf';

        $pdf = Pdf::loadView('HR.exports.perhitungan_pdf', compact('data'))->setPaper('a4', 'landscape')->setOption('isHtml5ParserEnabled', true)->setOption('isRemoteEnabled', true);

        return $pdf->download($filename);
    }

    private function buildExportData(Request $request)
    {
        $bulan = $request->get('bulan');
        $tahun = $request->get('tahun');
        $divisi = $request->get('divisi');
        $status = $request->get('status');

        $bulanNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $query = PerhitunganTunjanganHR::with(['karyawan.divisi', 'createdBy']);

        if ($bulan) {
            $query->where('bulan', $bulan);
        }
        if ($tahun) {
            $query->where('tahun', $tahun);
        }
        if ($status && !in_array($status, ['sudah', 'belum'])) {
            $query->where('status', $status);
        }

        $payrolls = $query->orderBy('tahun', 'desc')->orderBy('bulan', 'desc')->get();

        if ($divisi) {
            $payrolls = $payrolls->filter(fn($p) => ($p->karyawan->divisi->nama_divisi ?? '-') === $divisi);
        }

        $rows = [];
        foreach ($payrolls as $p) {
            $totalTunjangan = (int) ($p->total_tunjangan ?? 0);
            $potonganLain = ($p->potongan_pph21 ?? 0) + ($p->potongan_kasbon ?? 0) + ($p->potongan_denda ?? 0) + ($p->potongan_lain ?? 0);

            $rows[] = [
                'nip' => $p->karyawan->nip ?? '-',
                'nama' => $p->karyawan->nama_lengkap ?? '-',
                'status' => $p->status ?? 'draft',
                'gaji_pokok' => (int) ($p->gaji_pokok ?? 0),
                'salary_bpjstk' => (int) ($p->salary_bpjstk ?? 0),
                'total_tunjangan' => $totalTunjangan,
                'thp_bersih' => (int) ($p->thp_bersih ?? 0),
                'umk_bandung' => (int) ($p->umk_bandung ?? 2100000),

                'jht_perusahaan' => (int) ($p->jht_perusahaan ?? 0),
                'jkm_perusahaan' => (int) ($p->jkm_perusahaan ?? 0),
                'jkk_perusahaan' => (int) ($p->jkk_perusahaan ?? 0),
                'jp_perusahaan' => (int) ($p->jp_perusahaan ?? 0),
                'total_bpjs_perusahaan' => (int) ($p->total_bpjs_perusahaan ?? 0),

                'jht_karyawan' => (int) ($p->jht_karyawan ?? 0),
                'jp_karyawan' => (int) ($p->jp_karyawan ?? 0),
                'total_bpjs_karyawan' => (int) ($p->total_bpjs_karyawan ?? 0),

                'bpjs_kes_perusahaan' => (int) ($p->bpjs_kes_perusahaan ?? 0),
                'bpjs_kes_karyawan' => (int) ($p->bpjs_kes_karyawan ?? 0),

                'potongan_lain' => $potonganLain,
                'created_by' => $p->createdBy->name ?? '-',
                'created_at' => $p->created_at ? $p->created_at->format('d/m/Y H:i') : '-',
            ];
        }
        if ($bulan && $tahun) {
            $periodeLabel = $bulanNames[(int) $bulan] . ' ' . $tahun;
        } elseif ($tahun) {
            $periodeLabel = 'Tahun ' . $tahun;
        } else {
            $periodeLabel = 'Semua Periode';
        }
        if ($divisi) {
            $periodeLabel .= ' - Divisi ' . $divisi;
        }

        return [
            'rows' => $rows,
            'periode_label' => $periodeLabel,
            'bulan_names' => $bulanNames,
            'filter' => [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'divisi' => $divisi,
                'status' => $status,
            ],
        ];
    }

    public function indexPph()
    {
        return view('HR.payroll.indexPPH');
    }

    public function getPphData()
    {
        $karyawans = Karyawan::with('pph21')
            ->where('status_aktif', '1')
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->get();

        $data = $karyawans->map(function($k) {
            return [
                'id'                => $k->id,
                'nip'               => $k->nip ?? '-',
                'nama'              => $k->nama_lengkap ?? '-',
                'jabatan'           => $k->jabatan ?? '-',
                'divisi'            => is_object($k->divisi) ? ($k->divisi->nama_divisi ?? '-') : ($k->divisi ?? '-'),
                'gaji'              => (int) ($k->gaji ?? 0),
                'tunjangan_jabatan' => (int) ($k->tunjangan_jabatan ?? 0),
                'pph21' => $k->pph21 ? [
                    'id'      => $k->pph21->id,
                    'ptkp'    => $k->pph21->ptkp,
                    'menikah' => (int) $k->pph21->status_menikah,
                    'anak'    => $this->decodeAnak($k->pph21->anak),
                ] : null,
            ];
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function storePph(Request $request)
    {
        $anak = collect($request->anak ?? [])
            ->filter(fn($a) => !empty(trim($a['nama'] ?? '')))
            ->values()
            ->map(fn($a) => ['nama' => trim($a['nama'])])
            ->toArray();

        $pph = Pph21Karyawan::updateOrCreate(
            ['karyawan_id' => $request->karyawan_id],
            [
                'ptkp'           => $request->ptkp,
                'status_menikah' => (int) $request->status_menikah,
                'anak'           => $anak, // akan di-cast ke JSON di model
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Data PPH 21 berhasil disimpan!',
            'data'    => [
                'id'      => $pph->id,
                'ptkp'    => $pph->ptkp,
                'menikah' => (int) $pph->status_menikah,
                'anak'    => $this->decodeAnak($pph->anak),
            ],
        ]);
    }

    public function deletePph($id)
    {
        $pph = Pph21Karyawan::find($id);
        if (!$pph) {
            return response()->json([
                'success' => false,
                'message' => 'Data PPH 21 tidak ditemukan.',
            ], 404);
        }

        $pph->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data PPH 21 berhasil dihapus!',
        ]);
    }

    private function decodeAnak($anak)
    {
        if (is_null($anak)) return [];
        if (is_array($anak)) return $anak;
        if (is_string($anak)) {
            $decoded = json_decode($anak, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }
}
