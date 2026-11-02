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
use App\Models\BpjsChangeLog;
use App\Models\JenisTunjangan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Models\Pph21Bulanan;
use App\Models\Pph21Laporan;
use Illuminate\Support\Facades\Storage;

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

            $payrollData = $this->getPayrollData($bulan, $tahun, $search);
            $payrollList = $payrollData['payrollList'];
            $totalEligible = count($payrollData['eligibleIds']);
            $totalRecords = $payrollList->count();
            $paginated = $payrollList->forPage($page, $perPage);
            $lastPage = (int) ceil($totalRecords / $perPage);

            $salaryRanges = $this->calculateSalaryRanges($payrollList);
            $allowanceByDivisi = $this->calculateAllowanceByDivisi($payrollList);
            $monthlyTrend = $this->calculateMonthlyTrend($tahun);
            $topDeductions = $this->calculateTopDeductions($payrollList, $bulan, $tahun);

            $totalBasicSalary = $payrollList->sum('basic_salary');

            $summary = [
                'total_karyawan' => $totalEligible,
                'sudah_dihitung' => $payrollList->where('status', 'Sudah Dihitung')->count(),
                'belum_dihitung' => $payrollList->where('status', 'Belum Dihitung')->count(),
                'total_gaji_pokok' => $payrollList->sum('gaji_pokok'),
                'total_salary' => $payrollList->sum('basic_salary'),
                'total_tunjangan' => $payrollList->sum('tunjangan_bersih'),
                'total_potongan' => $payrollList->sum('total_potongan'),
                'total_potongan_bpjs_tk' => $payrollList->sum('potongan_bpjs_tk'),
                'total_potongan_bpjs_kes' => $payrollList->sum('potongan_bpjs_kes'),
                'total_potongan_pph21' => $payrollList->sum('potongan_pph21'),
                'total_basic_salary' => $totalBasicSalary,
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
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data payroll: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function exportPayrollExcel(Request $request)
    {
        $bulan = (int) $request->input('month', now()->month);
        $tahun = (int) $request->input('year', now()->year);
        $search = $request->input('search', '');

        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $payrollData = $this->getPayrollData($bulan, $tahun, $search);
        $payrollList = $payrollData['payrollList'];

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

        $bulanNama = Carbon::createFromDate($tahun, $bulan, 1)->format('F');
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
        $sheet->getStyle("A{$totalRow}:E{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $summaryRow = $totalRow + 2;
        $sheet->mergeCells("A{$summaryRow}:C{$summaryRow}");
        $sheet->setCellValue("A{$summaryRow}", 'Ringkasan:');
        $sheet->getStyle("A{$summaryRow}")->getFont()->setBold(true);

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

        $payrollData = $this->getPayrollData($bulan, $tahun, $search);
        $payrollList = $payrollData['payrollList'];
        $totalEligible = count($payrollData['eligibleIds']);

        $summary = [
            'total_karyawan' => $totalEligible,
            'sudah_dihitung' => $payrollList->where('status', 'Sudah Dihitung')->count(),
            'belum_dihitung' => $payrollList->where('status', 'Belum Dihitung')->count(),
            'total_gaji_pokok' => $payrollList->sum('gaji_pokok'),
            'total_tunjangan' => $payrollList->sum('tunjangan_bersih'),
            'total_tunjangan_kotor' => $payrollList->sum('total_tunjangan'),
            'total_potongan' => $payrollList->sum('total_potongan'),
            'total_potongan_bpjs_tk' => $payrollList->sum('potongan_bpjs_tk'),
            'total_potongan_bpjs_kes' => $payrollList->sum('potongan_bpjs_kes'),
            'total_potongan_pph21' => $payrollList->sum('potongan_pph21'),
            'total_basic_salary' => $payrollList->sum('basic_salary'),
            'total_gaji_bersih' => $payrollList->sum('gaji_bersih'),
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

        $periodDisplay = Carbon::createFromDate($tahun, $bulan, 1)->format('F Y');

        $pdf = Pdf::loadView('office.HR.exports.payroll_pdf', [
            'data' => $payrollList->values(),
            'summary' => $summary,
            'charts' => $charts,
            'period' => $periodDisplay,
            'generated_at' => now()->format('d M Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download("laporan_payroll_{$bulan}_{$tahun}.pdf");
    }

    private function getPayrollData($bulan, $tahun, $search = '')
    {
        $periodStart = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
        $periodEnd = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();

        $karyawanIdsFromTunjangan = TunjanganKaryawan::where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->pluck('id_karyawan')
            ->unique()
            ->toArray();

        $baseQuery = $this->baseKaryawanQuery();

        if (!empty($search)) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('kode_karyawan', 'like', "%{$search}%")
                    ->orWhere('divisi', 'like', "%{$search}%")
                    ->orWhere('jabatan', 'like', "%{$search}%");
            });
        }

        $allKaryawan = $baseQuery->get();

        $eligibleByDate = $allKaryawan->filter(function ($emp) use ($periodStart, $periodEnd) {
            return $this->isEmployeeEligibleForPeriod($emp, $periodStart, $periodEnd);
        });

        $eligibleIdsByDate = $eligibleByDate->pluck('id')->toArray();

        $finalIds = array_unique(array_merge($karyawanIdsFromTunjangan, $eligibleIdsByDate));

        $finalKaryawan = karyawan::whereIn('id', $finalIds)->get();

        if (!empty($search)) {
            $finalKaryawan = $finalKaryawan->filter(function ($emp) use ($search) {
                return str_contains(strtolower($emp->nama_lengkap), strtolower($search))
                    || str_contains(strtolower($emp->kode_karyawan ?? ''), strtolower($search))
                    || str_contains(strtolower($emp->divisi ?? ''), strtolower($search))
                    || str_contains(strtolower($emp->jabatan ?? ''), strtolower($search));
            })->values();
        }

        $eligibleIds = $finalKaryawan->pluck('id')->toArray();

        $tunjanganData = TunjanganKaryawan::with('jenistunjangan:id,nama_tunjangan,tipe')
            ->whereIn('id_karyawan', $eligibleIds)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->get()
            ->groupBy('id_karyawan');

        $payrollList = $finalKaryawan->map(function ($emp) use ($tunjanganData, $periodStart, $periodEnd) {
            $gajiPokok = (float) ($emp->gaji ?? 0);
            $tunjanganJabatan = (float) ($emp->tunjangan_jabatan ?? 0);

            $items = $tunjanganData->get($emp->id, collect());
            $itemsPotongan = $items->filter(fn($i) => $this->isPotonganItem($i));
            $itemsTunjangan = $items->reject(fn($i) => $this->isPotonganItem($i));

            $totalTunjangan = (float) $itemsTunjangan->sum('total');
            $totalPotongan = abs((float) $itemsPotongan->sum('total'));

            $potonganBpjsTk = 0.0;
            $potonganBpjsKes = 0.0;
            $potonganPph21 = 0.0;

            foreach ($itemsPotongan as $item) {
                $kategori = $this->categorizePotongan(optional($item->jenistunjangan)->nama_tunjangan);
                $nilai = abs((float) $item->total);
                if ($kategori === 'bpjs_tk') {
                    $potonganBpjsTk += $nilai;
                } elseif ($kategori === 'bpjs_kes') {
                    $potonganBpjsKes += $nilai;
                } elseif ($kategori === 'pph21') {
                    $potonganPph21 += $nilai;
                }
            }

            $tunjanganBersih = $totalTunjangan - $totalPotongan;
            $basicSalary = $gajiPokok + $tunjanganJabatan;
            $gajiBersih = $basicSalary + $totalTunjangan - $totalPotongan;
            $status = $this->determineEmployeeStatusForPeriod($emp, $periodStart, $periodEnd);

            return [
                'id' => $emp->id,
                'nama' => $emp->nama_lengkap,
                'kode' => $emp->kode_karyawan,
                'divisi' => $emp->divisi ?? '-',
                'jabatan' => $emp->jabatan ?? '-',
                'gaji_pokok' => $gajiPokok,
                'tunjangan_jabatan' => $tunjanganJabatan,
                'basic_salary' => $basicSalary,
                'total_tunjangan' => $totalTunjangan,
                'tunjangan_bersih' => $tunjanganBersih,
                'total_potongan' => $totalPotongan,
                'potongan_bpjs_tk' => $potonganBpjsTk,
                'potongan_bpjs_kes' => $potonganBpjsKes,
                'potongan_pph21' => $potonganPph21,
                'gaji_bersih' => $gajiBersih,
                'status' => $status,
                'details' => $items->map(fn($i) => [
                    'nama' => optional($i->jenistunjangan)->nama_tunjangan,
                    'tipe' => $this->isPotonganItem($i) ? 'Potongan' : 'Tunjangan',
                    'keterangan' => $i->keterangan,
                    'nilai' => $this->isPotonganItem($i) ? -abs((float) $i->total) : (float) $i->total,
                ])->values(),
            ];
        });

        return [
            'payrollList' => $payrollList,
            'eligibleIds' => $eligibleIds,
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
        ];
    }

    private function baseKaryawanQuery()
    {
        return karyawan::query()
            ->whereNotIn('jabatan', ['Komisaris', 'Direktur'])
            ->whereNotIn('id', [1, 3])
            ->where('kode_karyawan', 'not like', '%OL%');
    }

    private function isPotonganItem($item): bool
    {
        $ket = strtolower(trim((string) $item->keterangan));
        $tipe = strtolower(trim((string) optional($item->jenistunjangan)->tipe));
        return $ket === 'potongan' || $tipe === 'potongan' || (float) $item->total < 0;
    }

    private function getSalaryComponentsFromLogs($employeeLogs, $targetBulan, $targetTahun)
    {
        if ($employeeLogs->isEmpty()) {
            return ['gaji' => 0.0, 'tunjangan_jabatan' => 0.0];
        }

        $targetKey = ($targetTahun * 12) + $targetBulan;

        $pastOrCurrentLogs = $employeeLogs->filter(function ($log) use ($targetKey) {
            return (($log->tahun * 12) + $log->bulan) <= $targetKey;
        });

        if ($pastOrCurrentLogs->isNotEmpty()) {
            $selectedLog = $pastOrCurrentLogs->sortByDesc(function ($log) {
                return ($log->tahun * 12) + $log->bulan;
            })->first();
        } else {
            $selectedLog = $employeeLogs->sortBy(function ($log) {
                return ($log->tahun * 12) + $log->bulan;
            })->first();
        }

        return [
            'gaji' => (float) ($selectedLog->gaji ?? 0),
            'tunjangan_jabatan' => (float) ($selectedLog->tunjangan_jabatan ?? 0),
        ];
    }

    private function getGajiPokokFromLogs($employeeLogs, $targetBulan, $targetTahun)
    {
        $c = $this->getSalaryComponentsFromLogs($employeeLogs, $targetBulan, $targetTahun);
        return $c['gaji'] + $c['tunjangan_jabatan'];
    }

    private function categorizePotongan(?string $nama): string
    {
        $n = strtolower(trim($nama ?? ''));
        if ($n === '') {
            return 'lainnya';
        }

        if (str_contains($n, 'kesehatan') || str_contains($n, 'bpjs kes') || str_contains($n, 'bpjskes')) {
            return 'bpjs_kes';
        }

        if (
            str_contains($n, 'tenaga kerja') ||
            str_contains($n, 'bpjs tk') ||
            str_contains($n, 'bpjstk') ||
            str_contains($n, 'jht') ||
            str_contains($n, 'jkk') ||
            str_contains($n, 'jkm')
        ) {
            return 'bpjs_tk';
        }

        if (str_contains($n, 'pph') || str_contains($n, 'pajak')) {
            return 'pph21';
        }

        return 'lainnya';
    }

    private function isEmployeeEligibleForPeriod($emp, $periodStart, $periodEnd)
    {
        if (str_starts_with($emp->kode_karyawan ?? '', 'OL') ||
            in_array($emp->jabatan, ['Outsource', 'Pilih Jabatan'])) {
            return false;
        }

        $startDateRaw = $emp->awal_probation ?? $emp->awal_kontrak;
        if ($startDateRaw) {
            $startDate = Carbon::parse($startDateRaw)->startOfDay();
            if ($startDate->gt($periodEnd)) {
                return false;
            }
        }

        if (!empty($emp->resigned_at)) {
            $resignedAt = Carbon::parse($emp->resigned_at)->startOfDay();
            if ($resignedAt->lt($periodStart)) {
                return false;
            }
        }

        return true;
    }

    private function determineEmployeeStatusForPeriod($emp, $periodStart, $periodEnd)
    {
        $resignedAt = !empty($emp->resigned_at) ? Carbon::parse($emp->resigned_at) : null;
        $startDateRaw = $emp->awal_probation ?? $emp->awal_kontrak;
        $startDate = $startDateRaw ? Carbon::parse($startDateRaw) : null;

        if ($resignedAt && $resignedAt->between($periodStart, $periodEnd)) {
            return 'Resign';
        }

        if ($startDate && $startDate->between($periodStart, $periodEnd)) {
            return 'New Hire';
        }

        return 'Active';
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
            $divisi[$d]['total_salary'] += $emp['gaji_bersih'] + $emp['total_potongan'];
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
        $allKaryawan = $this->baseKaryawanQuery()->get();

        $eligibleIds = $allKaryawan->pluck('id')->toArray();
        $allLogGaji = LogGaji::whereIn('id_karyawan', $eligibleIds)->get()->groupBy('id_karyawan');

        $trend = [];
        for ($m = 1; $m <= 12; $m++) {
            $periodStart = Carbon::createFromDate($tahun, $m, 1)->startOfMonth();
            $periodEnd = Carbon::createFromDate($tahun, $m, 1)->endOfMonth();

            $monthlyEligible = $allKaryawan->filter(function ($emp) use ($periodStart, $periodEnd) {
                return $this->isEmployeeEligibleForPeriod($emp, $periodStart, $periodEnd);
            });

            $newHires = 0;
            $actives = 0;
            $resigns = 0;
            $totalGaji = 0;

            foreach ($monthlyEligible as $emp) {
                $status = $this->determineEmployeeStatusForPeriod($emp, $periodStart, $periodEnd);
                if ($status === 'New Hire') {
                    $newHires++;
                } elseif ($status === 'Resign') {
                    $resigns++;
                } else {
                    $actives++;
                }

                $employeeLogs = $allLogGaji->get($emp->id, collect());
                $totalGaji += $this->getGajiPokokFromLogs($employeeLogs, $m, $tahun);
            }

            $trend[] = [
                'month' => Carbon::createFromDate($tahun, $m, 1)->format('M'),
                'new_hires' => $newHires,
                'actives' => $actives,
                'resigns' => $resigns,
                'total_gaji' => $totalGaji,
            ];
        }

        return $trend;
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

    public function indexPerhitungan(Request $request)
    {
        $karyawans = karyawan::with('divisi')
            ->where('status_aktif', '1')
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->whereNot('jabatan', 'Outsource')
            ->whereNot('divisi', 'Pilih Divisi')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNotNull('nip')
            ->orderBy('nama_lengkap')
            ->get();

        $divisiList = $karyawans
            ->map(fn($k) => $this->divisiName($k))
            ->filter(fn($d) => $d && $d !== '-')
            ->unique()
            ->sort()
            ->values();

        $payrollKaryawanIds = PerhitunganTunjanganHR::pluck('karyawan_id')->unique();

        $totalKaryawan = $karyawans->count();
        $sudahPayroll = $karyawans->pluck('id')->intersect($payrollKaryawanIds)->count();
        $belumPayroll = $totalKaryawan - $sudahPayroll;

        $bulanSekarang = (int) date('n');
        $tahunSekarang = (int) date('Y');

        $allLogGaji = LogGaji::whereIn('id_karyawan', $karyawans->pluck('id'))->get()->groupBy('id_karyawan');

        $totalGaji = 0;
        foreach ($karyawans as $emp) {
            $employeeLogs = $allLogGaji->get($emp->id, collect());
            $totalGaji += $this->getGajiPokokFromLogs($employeeLogs, $bulanSekarang, $tahunSekarang);
        }

        return view('HR.payroll.indexPerhitungan', compact(
            'karyawans', 'divisiList', 'totalKaryawan',
            'sudahPayroll', 'belumPayroll', 'totalGaji'
        ));
    }

    public function getKaryawanDataPerhitungan(Request $request)
    {
        $request->validate([
            'karyawan_id' => 'required|exists:karyawans,id',
            'bulan' => 'nullable|integer|between:1,12',
            'tahun' => 'nullable|integer',
        ]);

        [$bulan, $tahun] = $this->currentPeriod($request);

        $karyawan = karyawan::with('divisi')->findOrFail($request->karyawan_id);
        $pph21Data = Pph21Karyawan::where('karyawan_id', $karyawan->id)->first();
        $employeeLogs = LogGaji::where('id_karyawan', $karyawan->id)->get();
        $gajiDasar = (int) $this->getGajiPokokFromLogs($employeeLogs, $bulan, $tahun);

        $pph21Setup = 0;
        if ($pph21Data && $pph21Data->ptkp) {
            $pph21Setup = $this->hitungPph21Bulanan($gajiDasar, $pph21Data->ptkp);
        }

        $existing = $this->latestPayrollOf($karyawan->id);

        $tunjanganRows = $this->latestTunjanganByKaryawan([$karyawan->id], $bulan, $tahun)->get($karyawan->id, collect());
        $tunjanganDetail = $this->tunjanganDetailFromRows($tunjanganRows);
        $tunjanganSource = empty($tunjanganDetail) ? 'kosong' : 'tunjangan';

        if ($existing && !empty($existing->tunjangan_detail)) {
            $tunjanganDetail = $existing->tunjangan_detail;
            $tunjanganSource = 'payroll';
        }

        $defaultGajiPokok = (int) ($existing->gaji_pokok ?? $gajiDasar);
        $defaultUmk = (int) (($existing->umk_bandung ?? 0) ?: 2100000);
        $defaultSalaryBpjstk = (int) (($existing->salary_bpjstk ?? 0) ?: $defaultGajiPokok);

        $pphSource = 'kosong';
        $pphValue = 0;
        if ($pph21Data && (int) $pph21Data->total_pph > 0) {
            $pphValue = (int) $pph21Data->total_pph;
            $pphSource = 'pph21';
        } elseif ($pph21Setup > 0) {
            $pphValue = $pph21Setup;
            $pphSource = 'setup';
        }

        $keepBpjs = (bool) $existing;
        $bpjs = $existing ? [
            'jht_perusahaan' => (int) $existing->jht_perusahaan,
            'jkm_perusahaan' => (int) $existing->jkm_perusahaan,
            'jkk_perusahaan' => (int) $existing->jkk_perusahaan,
            'jp_perusahaan' => (int) $existing->jp_perusahaan,
            'bpjs_kes_perusahaan' => (int) $existing->bpjs_kes_perusahaan,
            'jht_karyawan' => (int) $existing->jht_karyawan,
            'jp_karyawan' => (int) $existing->jp_karyawan,
            'bpjs_kes_karyawan' => (int) $existing->bpjs_kes_karyawan,
            'biaya_tanggungan' => (int) $existing->biaya_tanggungan,
        ] : null;

        $info = $existing
            ? 'Data payroll karyawan ini sudah ada dan dipakai apa adanya. Ubah hanya bagian yang berubah, lalu update.'
            : 'Data payroll belum ada. Nilai awal diambil dari data Tunjangan dan perhitungan BPJS otomatis. Cukup disimpan sekali.';

        return response()->json([
            'success' => true,
            'mode' => $existing ? 'update' : 'create',
            'info' => $info,
            'keep_bpjs' => $keepBpjs,
            'bpjs' => $bpjs,
            'karyawan' => [
                'id' => $karyawan->id,
                'nip' => $karyawan->nip ?? '-',
                'nama' => $karyawan->nama_lengkap,
                'jabatan' => $karyawan->jabatan ?? '-',
                'divisi' => $this->divisiName($karyawan),
                'gaji_pokok' => $defaultGajiPokok,
                'salary_bpjstk' => $defaultSalaryBpjstk,
                'umk_bandung' => $defaultUmk,
                'pph21_bulanan' => $pphValue,
                'pph21_source' => $pphSource,
                'has_pph21' => (bool) $pph21Data,
            ],
            'tunjangan_detail' => $tunjanganDetail,
            'tunjangan_source' => $tunjanganSource,
            'has_tunjangan' => !empty($tunjanganDetail),
            'existing_payroll' => $existing ? [
                'id' => $existing->id,
                'status' => $existing->status,
            ] : null,
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
            'bulan' => 'nullable|integer|between:1,12',
            'tahun' => 'nullable|integer',
            'gaji_pokok' => 'required|numeric|min:0',
            'salary_bpjstk' => 'required|numeric|min:0',
            'umk_bandung' => 'required|numeric|min:0',
            'biaya_tanggungan' => 'nullable|numeric|min:0',
            'tunjangan' => 'nullable|array',
            'tunjangan.*.jenis_tunjangan_id' => 'nullable',
            'tunjangan.*.nama' => 'nullable|string',
            'tunjangan.*.total' => 'nullable|numeric',
        ]);

        $karyawan = karyawan::find($request->karyawan_id);
        if (!$karyawan) {
            return $this->respondError('Karyawan tidak ditemukan!');
        }
        if ($karyawan->jabatan === 'Outsource' || $karyawan->jabatan === 'Pilih Jabatan') {
            return $this->respondError('Karyawan dengan jabatan ini tidak masuk perhitungan BPJS!');
        }
        if (str_starts_with($karyawan->kode_karyawan ?? '', 'OL')) {
            return $this->respondError('Karyawan outsource (OL) tidak masuk perhitungan BPJS!');
        }
        if (empty($karyawan->nip)) {
            return $this->respondError('Karyawan tanpa NIP tidak masuk perhitungan BPJS!');
        }

        $existing = $this->latestPayrollOf($request->karyawan_id);
        if ($existing) {
            return $this->updatePerhitungan($request, $existing->id);
        }

        [$bulan, $tahun] = $this->currentPeriod($request);

        DB::beginTransaction();
        try {
            $attrs = $this->buildPayrollAttributes($request, $karyawan);

            $perhitungan = PerhitunganTunjanganHR::create(array_merge($attrs, [
                'karyawan_id' => $request->karyawan_id,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'status' => 'calculated',
                'created_by' => Auth::id(),
            ]));

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

    public function getAllLogsPerhitungan()
    {
        $logs = BpjsChangeLog::with(['karyawan', 'changedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        $grouped = [];
        foreach ($logs as $log) {
            $karyawanId = $log->karyawan_id;
            if (!isset($grouped[$karyawanId])) {
                $grouped[$karyawanId] = [
                    'karyawan_id' => $karyawanId,
                    'karyawan_nama' => $log->karyawan->nama_lengkap ?? 'Unknown',
                    'logs' => []
                ];
            }
            $grouped[$karyawanId]['logs'][] = [
                'field_name' => $log->field_name,
                'old_value' => $log->old_value,
                'new_value' => $log->new_value,
                'description' => $log->description,
                'changed_by' => $log->changedBy?->name ?? 'System',
                'changed_at' => $log->created_at?->format('d/m/Y H:i'),
                'bulan' => $log->bulan,
                'tahun' => $log->tahun,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => array_values($grouped)
        ]);
    }

    public function showPerhitungan($id)
    {
        $perhitungan = PerhitunganTunjanganHR::with(['karyawan.divisi', 'createdBy', 'updatedBy'])->findOrFail($id);

        $tunjanganList = TunjanganKaryawan::where('id_karyawan', $perhitungan->karyawan_id)
            ->where('bulan', $perhitungan->bulan)
            ->where('tahun', $perhitungan->tahun)
            ->with('jenistunjangan')
            ->get();

        $logs = BpjsChangeLog::where('karyawan_id', $perhitungan->karyawan_id)
            ->with('changedBy')
            ->orderBy('created_at', 'desc')
            ->get();

        $groupedLogs = [];
        foreach ($logs as $log) {
            $dateKey = $log->created_at?->format('Y-m-d H:i') ?? 'Unknown';
            $bulanName = \Carbon\Carbon::create()->month($log->bulan)->translatedFormat('F');
            $groupKey = $dateKey . '_' . $log->tahun . '_' . str_pad($log->bulan, 2, '0', STR_PAD_LEFT);

            if (!isset($groupedLogs[$groupKey])) {
                $groupedLogs[$groupKey] = [
                    'waktu' => $log->created_at?->format('d/m/Y H:i'),
                    'bulan' => $bulanName,
                    'tahun' => $log->tahun,
                    'changed_by' => $log->changedBy?->name ?? 'System',
                    'details' => []
                ];
            }
            $groupedLogs[$groupKey]['details'][] = [
                'field_name' => $log->field_name,
                'old_value' => $log->old_value,
                'new_value' => $log->new_value,
                'description' => $log->description,
            ];
        }
        $changeLogs = array_values($groupedLogs);

        return response()->json([
            'success' => true,
            'data' => $perhitungan,
            'tunjangan' => $tunjanganList,
            'change_logs' => $changeLogs,
        ]);
    }

    public function updatePerhitungan(Request $request, $id)
    {
        $perhitungan = PerhitunganTunjanganHR::findOrFail($id);

        $request->validate([
            'gaji_pokok' => 'required|numeric|min:0',
            'salary_bpjstk' => 'required|numeric|min:0',
            'umk_bandung' => 'required|numeric|min:0',
            'biaya_tanggungan' => 'nullable|numeric|min:0',
            'tunjangan' => 'nullable|array',
            'tunjangan.*.jenis_tunjangan_id' => 'nullable',
            'tunjangan.*.nama' => 'nullable|string',
            'tunjangan.*.total' => 'nullable|numeric',
        ]);

        DB::beginTransaction();
        try {
            $oldData = $perhitungan->toArray();

            $attrs = $this->buildPayrollAttributes($request, $perhitungan->karyawan, $perhitungan->umk_bandung);
            $attrs['updated_by'] = Auth::id();

            $perhitungan->update($attrs);

            $this->logManualChanges($oldData, $perhitungan->fresh(), $perhitungan->karyawan_id);

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

    public function destroyPerhitungan($id)
    {
        $perhitungan = PerhitunganTunjanganHR::findOrFail($id);

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

        $karyawans = karyawan::where('status_aktif', '1')
            ->whereNot('jabatan', 'Outsource')
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->where('kode_karyawan', 'not like', 'OL%')
            ->whereNotNull('nip')
            ->with('divisi')
            ->orderBy('nama_lengkap')
            ->get();

        $payrollQuery = PerhitunganTunjanganHR::with(['createdBy'])
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->orderByDesc('id');
        if ($bulan) {
            $payrollQuery->where('bulan', $bulan);
        }
        if ($tahun) {
            $payrollQuery->where('tahun', $tahun);
        }

        $payrollByKaryawan = $payrollQuery->get()->unique('karyawan_id')->keyBy('karyawan_id');

        $targetBulan = $bulan ?: (int) date('n');
        $targetTahun = $tahun ?: (int) date('Y');

        $allLogGaji = LogGaji::whereIn('id_karyawan', $karyawans->pluck('id'))->get()->groupBy('id_karyawan');

        $noPayrollIds = $karyawans->pluck('id')->diff($payrollByKaryawan->keys())->values()->all();
        $tunjanganTerbaru = $this->latestTunjanganByKaryawan($noPayrollIds, $targetBulan, $targetTahun);

        $rows = [];

        foreach ($karyawans as $emp) {
            if ($divisi && $this->divisiName($emp) !== $divisi) {
                continue;
            }

            $payroll = $payrollByKaryawan->get($emp->id);

            if ($status === 'sudah' && !$payroll) {
                continue;
            }
            if ($status === 'belum' && $payroll) {
                continue;
            }

            $tunjanganData = null;
            if ($payroll) {
                $detail = $payroll->tunjangan_detail ?? [];
                $tunjanganData = [
                    'total' => (int) ($payroll->total_tunjangan ?? array_sum(array_column($detail, 'total'))),
                    'source' => 'payroll',
                    'detail' => $detail,
                ];
            } else {
                $detail = $this->tunjanganDetailFromRows($tunjanganTerbaru->get($emp->id, collect()));
                if (!empty($detail)) {
                    $tunjanganData = [
                        'total' => array_sum(array_column($detail, 'total')),
                        'source' => 'tunjangan_karyawan',
                        'detail' => $detail,
                    ];
                }
            }

            $employeeLogs = $allLogGaji->get($emp->id, collect());
            $gajiPokok = $payroll ? (int) $payroll->gaji_pokok : (int) $this->getGajiPokokFromLogs($employeeLogs, $targetBulan, $targetTahun);

            $rows[] = [
                'karyawan_id' => $emp->id,
                'nip' => $emp->nip ?? '-',
                'nama' => $emp->nama_lengkap,
                'jabatan' => $emp->jabatan ?? '-',
                'divisi' => $this->divisiName($emp),
                'status_aktif' => $emp->status_aktif == '1' ? 'Aktif' : 'Nonaktif',
                'gaji_pokok' => $gajiPokok,
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

                        'jht_perusahaan' => $payroll->jht_perusahaan ?? 0,
                        'jkm_perusahaan' => $payroll->jkm_perusahaan ?? 0,
                        'jkk_perusahaan' => $payroll->jkk_perusahaan ?? 0,
                        'jp_perusahaan' => $payroll->jp_perusahaan ?? 0,
                        'bpjs_kes_perusahaan' => $payroll->bpjs_kes_perusahaan ?? 0,

                        'jht_karyawan' => $payroll->jht_karyawan ?? 0,
                        'jp_karyawan' => $payroll->jp_karyawan ?? 0,
                        'bpjs_kes_karyawan' => $payroll->bpjs_kes_karyawan ?? 0,
                        'biaya_tanggungan' => $payroll->biaya_tanggungan ?? 0,

                        'potongan_pph21' => $payroll->potongan_pph21 ?? 0,
                        'potongan_kasbon' => $payroll->potongan_kasbon ?? 0,
                        'potongan_denda' => $payroll->potongan_denda ?? 0,
                        'potongan_lain' => $payroll->potongan_lain ?? 0,
                        'gaji_pokok' => $payroll->gaji_pokok ?? 0,
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

        $payrolls = $query->orderByDesc('tahun')->orderByDesc('bulan')->orderByDesc('id')->get();
        if (!$bulan && !$tahun) {
            $payrolls = $payrolls->unique('karyawan_id')->values();
        }

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
        $totalBPJSKesKaryawan = $payrolls->sum('bpjs_kes_karyawan') + $payrolls->sum('biaya_tanggungan');
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
            'biaya_tanggungan' => 0,
            'total_bpjs_perusahaan' => $totalBPJSPerusahaan,
            'total_bpjs_karyawan' => $totalBPJSKaryawan,
        ];
    }

    private function logBpjsChanges($oldPayroll, $newPayroll, $karyawanId)
    {
        $fieldsToCompare = [
            'gaji_pokok' => 'Gaji Pokok',
            'salary_bpjstk' => 'Salary BPJSTK',
            'umk_bandung' => 'UMK Bandung',
            'jht_perusahaan' => 'JHT Perusahaan (3.7%)',
            'jkm_perusahaan' => 'JKM Perusahaan (0.3%)',
            'jkk_perusahaan' => 'JKK Perusahaan (0.24%)',
            'jp_perusahaan' => 'JP Perusahaan (2%)',
            'total_bpjstk_perusahaan' => 'Total BPJSTK Perusahaan',
            'jht_karyawan' => 'JHT Karyawan (2%)',
            'jp_karyawan' => 'JP Karyawan (1%)',
            'total_bpjstk_karyawan' => 'Total BPJSTK Karyawan',
            'bpjs_kes_perusahaan' => 'BPJS Kesehatan Perusahaan (4%)',
            'bpjs_kes_karyawan' => 'BPJS Kesehatan Karyawan (1%)',
            'biaya_tanggungan' => 'Biaya Tanggungan (kesehatan)',
            'total_bpjs_perusahaan' => 'Total BPJS Perusahaan',
            'total_bpjs_karyawan' => 'Total BPJS Karyawan',
            'potongan_pph21' => 'Potongan PPh 21',
            'total_potongan_lain' => 'Total Potongan Lain',
            'thp_kotor' => 'THP Kotor',
            'thp_bersih' => 'THP Bersih',
            'total_biaya_perusahaan' => 'Total Biaya Perusahaan',
        ];

        foreach ($fieldsToCompare as $field => $label) {
            $oldVal = $oldPayroll->{$field} ?? 0;
            $newVal = $newPayroll->{$field} ?? 0;

            if ((float) $oldVal !== (float) $newVal) {
                BpjsChangeLog::create([
                    'perhitungan_id' => $newPayroll->id,
                    'karyawan_id' => $karyawanId,
                    'bulan' => $newPayroll->bulan,
                    'tahun' => $newPayroll->tahun,
                    'field_name' => $label,
                    'old_value' => (string) $oldVal,
                    'new_value' => (string) $newVal,
                    'description' => "Perubahan {$label}: dari Rp " . number_format($oldVal) . " menjadi Rp " . number_format($newVal) . " (Sebelumnya dari periode bulan {$oldPayroll->bulan}/{$oldPayroll->tahun})",
                    'changed_by' => Auth::id(),
                ]);
            }
        }
    }

    private function logManualChanges($oldData, $newPayroll, $karyawanId)
    {
        $fieldsToCompare = [
            'gaji_pokok' => 'Gaji Pokok',
            'salary_bpjstk' => 'Salary BPJSTK',
            'umk_bandung' => 'UMK Bandung',
            'jht_perusahaan' => 'JHT Perusahaan',
            'jkm_perusahaan' => 'JKM Perusahaan',
            'jkk_perusahaan' => 'JKK Perusahaan',
            'jp_perusahaan' => 'JP Perusahaan',
            'bpjs_kes_perusahaan' => 'BPJS Kes Perusahaan',
            'jht_karyawan' => 'JHT Karyawan',
            'jp_karyawan' => 'JP Karyawan',
            'bpjs_kes_karyawan' => 'BPJS Kes Karyawan',
            'biaya_tanggungan' => 'Tanggungan Lainnya (kesehatan)',
            'potongan_pph21' => 'PPh 21',
            'thp_kotor' => 'THP Kotor',
            'thp_bersih' => 'THP Bersih',
        ];

        foreach ($fieldsToCompare as $field => $label) {
            $oldVal = $oldData[$field] ?? 0;
            $newVal = $newPayroll->{$field} ?? 0;

            if ((float) $oldVal !== (float) $newVal) {
                BpjsChangeLog::create([
                    'perhitungan_id' => $newPayroll->id,
                    'karyawan_id' => $karyawanId,
                    'bulan' => $newPayroll->bulan,
                    'tahun' => $newPayroll->tahun,
                    'field_name' => $label,
                    'old_value' => (string) $oldVal,
                    'new_value' => (string) $newVal,
                    'description' => "Perubahan {$label}: dari Rp " . number_format($oldVal) . " menjadi Rp " . number_format($newVal),
                    'changed_by' => Auth::id(),
                ]);
            }
        }
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
        $lastCol = 23;

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
        $sheet->mergeCellsByColumnAndRow(1, $row, $lastCol, $row);
        $sheet->getStyleByColumnAndRow($col, $row)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyleByColumnAndRow($col, $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;

        $sheet->setCellValueByColumnAndRow($col, $row, 'Periode: ' . $data['periode_label']);
        $sheet->mergeCellsByColumnAndRow(1, $row, $lastCol, $row);
        $sheet->getStyleByColumnAndRow($col, $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;

        $sheet->setCellValueByColumnAndRow($col, $row, 'Dicetak: ' . now()->format('d M Y H:i'));
        $sheet->mergeCellsByColumnAndRow(1, $row, $lastCol, $row);
        $row++;
        $row++;

        $headers = ['No', 'Nama', 'Status', 'Salary Bulan', 'Salary BPJSTK', 'Tunjangan', 'THP', 'UMK Bandung', 'JHT Per (3.70%)', 'JKM Per (0.30%)', 'JKK Per (0.24%)', 'JP Per (2.00%)', 'Total BPJS Per', 'JHT Kar (2.00%)', 'JP Kar (1.00%)', 'Total BPJS Kar', 'Total Per+Kar', 'BPJS Kes Per (4%)', 'BPJS Kes Kar (1%)', 'Biaya Tanggungan', 'Ditanggung Per', 'Ditanggung Kar', 'Salary THP Kar'];

        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($col, $row, $header);
            $col++;
        }

        $sheet->getStyleByColumnAndRow(1, $row, $lastCol, $row)->getFont()->setBold(true);
        $sheet->getStyleByColumnAndRow(1, $row, $lastCol, $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyleByColumnAndRow(1, $row, $lastCol, $row)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyleByColumnAndRow(1, $row, $lastCol, $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
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
            $sheet->setCellValueByColumnAndRow($col++, $row, $row_data['biaya_tanggungan']);

            $ditanggung_per = $row_data['total_bpjs_perusahaan'];
            $ditanggung_kar = $row_data['total_bpjs_karyawan'] + $row_data['biaya_tanggungan'];

            $sheet->setCellValueByColumnAndRow($col++, $row, $ditanggung_per);
            $sheet->setCellValueByColumnAndRow($col++, $row, $ditanggung_kar);
            $sheet->setCellValueByColumnAndRow($col++, $row, $row_data['thp_bersih']);

            $sheet->getStyleByColumnAndRow(1, $row, $lastCol, $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyleByColumnAndRow(4, $row, $lastCol, $row)->getNumberFormat()->setFormatCode('#,##0.##');
            $sheet->getStyleByColumnAndRow(4, $row, $lastCol, $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

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
        $sheet->getStyleByColumnAndRow(1, $row, $lastCol, $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

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

        foreach (range('A', 'W') as $col) {
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

        $payrolls = $query->orderByDesc('tahun')->orderByDesc('bulan')->orderByDesc('id')->get();
        if (!$bulan && !$tahun) {
            $payrolls = $payrolls->unique('karyawan_id')->values();
        }

        if ($divisi) {
            $payrolls = $payrolls->filter(fn($p) => ($p->karyawan->divisi->nama_divisi ?? '-') === $divisi);
        }

        $rows = [];
        foreach ($payrolls as $p) {
            $totalTunjangan = (int) ($p->total_tunjangan ?? 0);
            $potonganLain = (int) ($p->potongan_pph21 ?? 0);

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
                'biaya_tanggungan' => (int) ($p->biaya_tanggungan ?? 0),
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
            $periodeLabel = 'Data Terkini';
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

    public function deleteAll(Request $request)
    {
        try {
            $deleted = PerhitunganTunjanganHR::query()->delete();

            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus {$deleted} data perhitungan.",
                'stats' => [
                    'sudah' => 0,
                    'belum' => karyawan::query()
                        ->where('status_aktif', '1')
                        ->count(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function indexPph()
    {
        return view('HR.payroll.indexPPH');
    }

    private function pphKaryawanQuery()
    {
        return karyawan::with('pph21')
            ->where('status_aktif', '1')
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->whereNot('jabatan', 'Outsource')
            ->whereNot('divisi', 'Pilih Divisi')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNotNull('nip');
    }

    private function pphTerkunci(int $bulan, int $tahun): bool
    {
        return Pph21Laporan::where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->whereNotNull('lapor_kortex_at')
            ->exists();
    }

    private function pphContext(int $bulan, int $tahun): array
    {
        $key = ($tahun * 12) + $bulan;

        $records = Pph21Bulanan::where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->get()
            ->keyBy('karyawan_id');

        $previous = Pph21Bulanan::whereRaw('(tahun * 12 + bulan) < ?', [$key])
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->get()
            ->unique('karyawan_id')
            ->keyBy('karyawan_id');

        return [
            'records' => $records,
            'previous' => $previous,
            'awal' => $this->tunjanganPph21Awal($bulan, $tahun),
        ];
    }

    private function resolvePph($k, array $ctx): ?array
    {
        $record = $ctx['records']->get($k->id);
        if ($record) {
            return [
                'nilai' => (int) $record->total_pph,
                'sumber' => 'tersimpan',
                'dari' => null,
                'tersimpan' => true,
            ];
        }

        $awal = $ctx['awal'][$k->id] ?? null;
        if ($awal) {
            return [
                'nilai' => (int) $awal['total'],
                'sumber' => 'tunjangan',
                'dari' => null,
                'tersimpan' => false,
            ];
        }

        $prev = $ctx['previous']->get($k->id);
        if ($prev) {
            return [
                'nilai' => (int) $prev->total_pph,
                'sumber' => 'bulan_lalu',
                'dari' => str_pad((string) $prev->bulan, 2, '0', STR_PAD_LEFT) . '/' . $prev->tahun,
                'tersimpan' => false,
            ];
        }

        if ($k->pph21 && $k->pph21->ptkp) {
            return [
                'nilai' => $this->hitungPph21((int) ($k->gaji ?? 0), $k->pph21->ptkp)['bulanan'],
                'sumber' => 'hitung',
                'dari' => null,
                'tersimpan' => false,
            ];
        }

        return null;
    }

    private function syncPphProfileTotal(int $karyawanId): void
    {
        $latest = Pph21Bulanan::where('karyawan_id', $karyawanId)
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->first();

        if ($latest) {
            Pph21Karyawan::where('karyawan_id', $karyawanId)->update(['total_pph' => $latest->total_pph]);
        }
    }

    private function laporanPayload(?Pph21Laporan $l, int $bulan, int $tahun): array
    {
        if ($l) {
            $l->loadMissing(['kortexBy', 'kodeBillingBy', 'buktiBayarBy']);
        }

        $fmt = fn($d) => $d ? $d->format('d/m/Y H:i') : null;
        $base = url('HR-dashboard/perhitungan-pph/file');
        $q = "?bulan={$bulan}&tahun={$tahun}";

        return [
            'kortex' => [
                'sudah' => (bool) ($l && $l->lapor_kortex_at),
                'at' => $fmt($l?->lapor_kortex_at),
                'by' => $l?->kortexBy?->name,
            ],
            'kode_billing' => [
                'sudah' => (bool) ($l && $l->kode_billing_at),
                'nomor' => $l?->kode_billing_nomor,
                'file' => $l && $l->kode_billing_file ? basename($l->kode_billing_file) : null,
                'url' => $l && $l->kode_billing_file ? $base . '/kode-billing' . $q : null,
                'at' => $fmt($l?->kode_billing_at),
                'by' => $l?->kodeBillingBy?->name,
            ],
            'bukti_bayar' => [
                'sudah' => (bool) ($l && $l->bukti_bayar_at),
                'file' => $l && $l->bukti_bayar_file ? basename($l->bukti_bayar_file) : null,
                'url' => $l && $l->bukti_bayar_file ? $base . '/bukti-bayar' . $q : null,
                'at' => $fmt($l?->bukti_bayar_at),
                'by' => $l?->buktiBayarBy?->name,
            ],
        ];
    }

    public function getPphData(Request $request)
    {
        [$bulan, $tahun] = $this->currentPeriod($request);
        $ctx = $this->pphContext($bulan, $tahun);

        $data = $this->pphKaryawanQuery()
            ->get()
            ->map(function ($k) use ($ctx) {
                $awal = $ctx['awal'][$k->id] ?? null;

                return [
                    'id' => $k->id,
                    'nip' => $k->nip ?? '-',
                    'nama' => $k->nama_lengkap ?? '-',
                    'jabatan' => $k->jabatan ?? '-',
                    'divisi' => $this->divisiName($k),
                    'gaji' => (int) ($k->gaji ?? 0),
                    'tunjangan_jabatan' => (int) ($k->tunjangan_jabatan ?? 0),
                    'pph_tunjangan' => $awal ? (int) $awal['total'] : null,
                    'pph_bulan' => $this->resolvePph($k, $ctx),
                    'pph21' => $k->pph21 ? [
                        'id' => $k->pph21->id,
                        'ptkp' => $k->pph21->ptkp,
                        'menikah' => (int) $k->pph21->status_menikah,
                        'anak' => $this->decodeAnak($k->pph21->anak),
                        'total_pph' => (int) $k->pph21->total_pph,
                    ] : null,
                ];
            })
            ->values();

        $laporan = Pph21Laporan::where('bulan', $bulan)->where('tahun', $tahun)->first();

        return response()->json([
            'success' => true,
            'data' => $data,
            'laporan' => $this->laporanPayload($laporan, $bulan, $tahun),
            'meta' => [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'terkunci' => (bool) ($laporan && $laporan->lapor_kortex_at),
            ],
        ]);
    }

    public function storePph(Request $request)
    {
        $request->validate([
            'karyawan_id' => 'required|exists:karyawans,id',
            'ptkp' => 'required|in:TK/0,TK/1,TK/2,TK/3,K/0,K/1,K/2,K/3',
            'status_menikah' => 'required|in:0,1',
            'bulan' => 'nullable|integer|between:1,12',
            'tahun' => 'nullable|integer|min:2000|max:2100',
        ]);

        [$bulan, $tahun] = $this->currentPeriod($request);

        if ($this->pphTerkunci($bulan, $tahun)) {
            return $this->respondError('Periode ini sudah dilaporkan ke Kortex. Batalkan lapor Kortex terlebih dahulu untuk mengubah data.');
        }

        $anak = collect($request->anak ?? [])
            ->filter(fn($a) => !empty(trim($a['nama'] ?? '')))
            ->values()
            ->map(fn($a) => ['nama' => trim($a['nama'])])
            ->toArray();

        $karyawan = karyawan::findOrFail($request->karyawan_id);
        $awal = $this->tunjanganPph21Awal($bulan, $tahun, (int) $karyawan->id)[$karyawan->id] ?? null;

        $total = $awal
            ? (int) $awal['total']
            : $this->hitungPph21((int) ($karyawan->gaji ?? 0), $request->ptkp)['bulanan'];

        DB::beginTransaction();
        try {
            $pph = Pph21Karyawan::where('karyawan_id', $karyawan->id)->first();
            $mode = $pph ? 'update' : 'create';

            $payload = [
                'ptkp' => $request->ptkp,
                'status_menikah' => (int) $request->status_menikah,
                'anak' => $anak,
                'total_pph' => $total,
            ];

            if ($pph) {
                $pph->update($payload);
            } else {
                $pph = Pph21Karyawan::create(array_merge(['karyawan_id' => $karyawan->id], $payload));
            }

            Pph21Bulanan::updateOrCreate(
                ['karyawan_id' => $karyawan->id, 'bulan' => $bulan, 'tahun' => $tahun],
                [
                    'total_pph' => $total,
                    'sumber' => $awal ? 'tunjangan' : 'hitung',
                    'created_by' => Auth::id(),
                ]
            );

            $this->syncPphProfileTotal((int) $karyawan->id);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respondError('Gagal menyimpan: ' . $e->getMessage(), 500);
        }

        $pph->refresh();

        return response()->json([
            'success' => true,
            'mode' => $mode,
            'message' => $mode === 'create'
                ? 'Data PPH 21 berhasil disimpan. Bulan berikutnya cukup memakai data ini.'
                : 'Data PPH 21 periode ' . str_pad((string) $bulan, 2, '0', STR_PAD_LEFT) . '/' . $tahun . ' berhasil disimpan.',
            'data' => [
                'id' => $pph->id,
                'ptkp' => $pph->ptkp,
                'menikah' => (int) $pph->status_menikah,
                'anak' => $this->decodeAnak($pph->anak),
                'total_pph' => $total,
            ],
        ]);
    }

    public function isiOtomatisPph(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer|min:2000|max:2100',
        ]);

        [$bulan, $tahun] = $this->currentPeriod($request);

        if ($this->pphTerkunci($bulan, $tahun)) {
            return $this->respondError('Periode ini sudah dilaporkan ke Kortex. Batalkan lapor Kortex terlebih dahulu untuk mengubah data.');
        }

        $ctx = $this->pphContext($bulan, $tahun);
        $dibuat = 0;
        $tanpaPtkp = 0;

        DB::beginTransaction();
        try {
            foreach ($this->pphKaryawanQuery()->get() as $k) {
                if ($ctx['records']->has($k->id)) {
                    continue;
                }

                if (!$k->pph21) {
                    $tanpaPtkp++;
                    continue;
                }

                $r = $this->resolvePph($k, $ctx);
                if (!$r) {
                    continue;
                }

                Pph21Bulanan::create([
                    'karyawan_id' => $k->id,
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'total_pph' => $r['nilai'],
                    'sumber' => $r['sumber'],
                    'created_by' => Auth::id(),
                ]);

                $this->syncPphProfileTotal((int) $k->id);
                $dibuat++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respondError('Gagal mengisi otomatis: ' . $e->getMessage(), 500);
        }

        if ($dibuat === 0) {
            return $this->respondError('Tidak ada data yang perlu diisi. Semua karyawan yang punya status PTKP sudah terisi' . ($tanpaPtkp ? ", dan {$tanpaPtkp} karyawan belum punya status PTKP." : '.'));
        }

        return response()->json([
            'success' => true,
            'message' => "{$dibuat} karyawan berhasil diisi otomatis." . ($tanpaPtkp ? " {$tanpaPtkp} karyawan dilewati karena belum punya status PTKP." : ''),
            'dibuat' => $dibuat,
        ]);
    }

    public function deletePph($id)
    {
        return response()->json([
            'success' => false,
            'message' => 'Data PPH 21 tidak bisa dihapus. Silakan update data yang sudah ada.',
        ], 422);
    }

    public function laporKortexPph(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer|min:2000|max:2100',
        ]);

        [$bulan, $tahun] = $this->currentPeriod($request);

        if (!Pph21Bulanan::where('bulan', $bulan)->where('tahun', $tahun)->exists()) {
            return $this->respondError('Belum ada data PPH 21 yang tersimpan pada periode ini.');
        }

        $laporan = Pph21Laporan::firstOrCreate(['bulan' => $bulan, 'tahun' => $tahun]);
        $laporan->update([
            'lapor_kortex_at' => now(),
            'lapor_kortex_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lapor Kortex berhasil dicatat untuk seluruh karyawan.',
            'laporan' => $this->laporanPayload($laporan->fresh(), $bulan, $tahun),
        ]);
    }

    public function batalKortexPph(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer|min:2000|max:2100',
        ]);

        [$bulan, $tahun] = $this->currentPeriod($request);

        $laporan = Pph21Laporan::where('bulan', $bulan)->where('tahun', $tahun)->first();
        if (!$laporan || !$laporan->lapor_kortex_at) {
            return $this->respondError('Periode ini belum dilaporkan ke Kortex.');
        }

        $laporan->update([
            'lapor_kortex_at' => null,
            'lapor_kortex_by' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lapor Kortex dibatalkan. Data periode ini bisa diubah kembali.',
            'laporan' => $this->laporanPayload($laporan->fresh(), $bulan, $tahun),
        ]);
    }

    public function uploadBuktiBayarPph(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer|min:2000|max:2100',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        return $this->simpanBerkasPph($request, 'bukti_bayar');
    }

    public function uploadKodeBillingPph(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer|min:2000|max:2100',
            'nomor' => 'nullable|string|max:100',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if (!$request->hasFile('file') && !filled($request->nomor)) {
            return $this->respondError('Isi nomor kode billing atau unggah berkasnya.');
        }

        return $this->simpanBerkasPph($request, 'kode_billing');
    }

    private function simpanBerkasPph(Request $request, string $jenis)
    {
        [$bulan, $tahun] = $this->currentPeriod($request);

        $laporan = Pph21Laporan::firstOrCreate(['bulan' => $bulan, 'tahun' => $tahun]);

        $update = [
            $jenis . '_at' => now(),
            $jenis . '_by' => Auth::id(),
        ];

        if ($request->hasFile('file')) {
            $lama = $laporan->{$jenis . '_file'};
            if ($lama) {
                Storage::disk('local')->delete($lama);
            }

            $folder = 'pph21/' . $tahun . '-' . str_pad((string) $bulan, 2, '0', STR_PAD_LEFT) . '/' . $jenis;
            $update[$jenis . '_file'] = $request->file('file')->store($folder, 'local');
        }

        if ($jenis === 'kode_billing' && $request->filled('nomor')) {
            $update['kode_billing_nomor'] = trim($request->nomor);
        }

        $laporan->update($update);

        $label = $jenis === 'kode_billing' ? 'Kode billing' : 'Bukti bayar';

        return response()->json([
            'success' => true,
            'message' => $label . ' berhasil disimpan untuk seluruh karyawan.',
            'laporan' => $this->laporanPayload($laporan->fresh(), $bulan, $tahun),
        ]);
    }

    public function deleteAllPph(Request $request)
    {
        try {
            DB::beginTransaction();

            $deletedBulanan = Pph21Bulanan::query()->delete();

            $deletedKaryawan = Pph21Karyawan::query()->delete();

            $deletedLaporan = Pph21Laporan::query()->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus semua data PPH 21 "
                    . "({$deletedBulanan} data bulanan, "
                    . "{$deletedKaryawan} data PTKP karyawan, "
                    . "{$deletedLaporan} laporan).",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respondError('Gagal menghapus data: ' . $e->getMessage(), 500);
        }
    }

    public function downloadBerkasPph(Request $request, string $jenis)
    {
        $map = [
            'kode-billing' => 'kode_billing_file',
            'bukti-bayar' => 'bukti_bayar_file',
        ];

        abort_unless(isset($map[$jenis]), 404);

        [$bulan, $tahun] = $this->currentPeriod($request);

        $laporan = Pph21Laporan::where('bulan', $bulan)->where('tahun', $tahun)->first();
        $path = $laporan?->{$map[$jenis]};

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path));
    }

    private function tunjanganPph21Awal(int $bulan, int $tahun, ?int $karyawanId = null): array
    {
        $jenisIds = JenisTunjangan::query()
            ->where(function ($q) {
                $q->whereRaw("LOWER(REPLACE(REPLACE(nama_tunjangan, ' ', ''), '-', '')) LIKE ?", ['%pph21%'])
                    ->orWhereRaw('LOWER(nama_tunjangan) LIKE ?', ['%pasal 21%']);
            })
            ->pluck('id');

        if ($jenisIds->isEmpty()) {
            return [];
        }

        $query = TunjanganKaryawan::with('jenistunjangan:id,nama_tunjangan')
            ->whereIn('jenis_tunjangan', $jenisIds)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun);

        if ($karyawanId) {
            $query->where('id_karyawan', $karyawanId);
        }

        return $query->get()
            ->groupBy('id_karyawan')
            ->map(function ($items) use ($bulan, $tahun) {
                return [
                    'total' => (int) round(abs((float) $items->sum('total'))),
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'nama' => optional($items->first()->jenistunjangan)->nama_tunjangan ?? 'PPH 21',
                ];
            })
            ->all();
    }

    private const PTKP_BULANAN = [
        'TK/0' => 4500000, 'TK/1' => 4875000, 'TK/2' => 5250000, 'TK/3' => 5625000,
        'K/0' => 4875000, 'K/1' => 5250000, 'K/2' => 5625000, 'K/3' => 6000000,
    ];

    private function hitungPph21(int $gajiBulanan, ?string $ptkpCode): array
    {
        $gajiTahunan = $gajiBulanan * 12;
        $ptkp = (self::PTKP_BULANAN[$ptkpCode] ?? 0) * 12;
        $pkp = max(0, $gajiTahunan - $ptkp);

        $layers = [
            [60000000, 0.05],
            [250000000, 0.15],
            [500000000, 0.25],
            [5000000000, 0.30],
            [INF, 0.35],
        ];

        $sisa = $pkp;
        $prev = 0;
        $pphTahunan = 0;

        foreach ($layers as [$limit, $rate]) {
            if ($sisa <= 0) break;
            $kena = min($sisa, $limit - $prev);
            $pphTahunan += $kena * $rate;
            $sisa -= $kena;
            $prev = $limit;
        }

        return [
            'tahunan' => (int) round($pphTahunan),
            'bulanan' => (int) round($pphTahunan / 12),
        ];
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

    private function currentPeriod(Request $request): array
    {
        $bulan = (int) ($request->input('bulan') ?: now()->month);
        $tahun = (int) ($request->input('tahun') ?: now()->year);
        return [$bulan, $tahun];
    }

    private function latestPayrollOf($karyawanId)
    {
        return PerhitunganTunjanganHR::where('karyawan_id', $karyawanId)
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->orderByDesc('id')
            ->first();
    }

    private function divisiName($emp): string
    {
        $d = $emp->divisi;
        if (is_object($d)) {
            return $d->nama_divisi ?? '-';
        }
        return $d ?: '-';
    }

    private function latestTunjanganByKaryawan(array $ids, $maxBulan = null, $maxTahun = null)
    {
        if (empty($ids)) {
            return collect();
        }

        $q = TunjanganKaryawan::with('jenistunjangan')->whereIn('id_karyawan', $ids);

        if ($maxBulan && $maxTahun) {
            $q->where(function ($w) use ($maxBulan, $maxTahun) {
                $w->where('tahun', '<', $maxTahun)
                    ->orWhere(function ($x) use ($maxBulan, $maxTahun) {
                        $x->where('tahun', $maxTahun)->where('bulan', '<=', $maxBulan);
                    });
            });
        }

        return $q->orderByDesc('tahun')->orderByDesc('bulan')->get()
            ->groupBy('id_karyawan')
            ->map(function ($items) {
                $f = $items->first();
                return $items->filter(fn($i) => (int) $i->tahun === (int) $f->tahun && (int) $i->bulan === (int) $f->bulan)->values();
            });
    }

    private function tunjanganDetailFromRows($rows): array
    {
        $isPph = function ($t) {
            $nama = strtolower($t->jenistunjangan->nama_tunjangan ?? '');
            $padat = str_replace([' ', '-'], '', $nama);
            return str_contains($padat, 'pph21') || str_contains($nama, 'pasal 21');
        };

        return $rows
            ->reject($isPph)
            ->filter(function ($t) {
                $tipe = strtolower($t->jenistunjangan->tipe ?? $t->keterangan ?? '');
                return $tipe === 'tunjangan';
            })
            ->map(fn($t) => [
                'id' => $t->id,
                'jenis_tunjangan_id' => $t->jenis_tunjangan,
                'nama' => $t->jenistunjangan?->nama_tunjangan ?? ($t->keterangan ?? 'Lainnya'),
                'total' => (int) $t->total,
            ])
            ->values()
            ->toArray();
    }

    private function resolveBpjs(Request $request, $salaryBPJSTK, $umkBandung): array
    {
        $b = $this->calculateBPJSPerhitungan($salaryBPJSTK, $umkBandung);

        $fields = [
            'jht_perusahaan', 'jkm_perusahaan', 'jkk_perusahaan', 'jp_perusahaan', 'bpjs_kes_perusahaan',
            'jht_karyawan', 'jp_karyawan', 'bpjs_kes_karyawan', 'biaya_tanggungan'
        ];
        foreach ($fields as $f) {
            if ($request->has($f) && is_numeric($request->input($f))) {
                $b[$f] = max(0, (int) $request->input($f));
            }
        }

        $b['total_bpjstk_perusahaan'] = $b['jht_perusahaan'] + $b['jkm_perusahaan'] + $b['jkk_perusahaan'] + $b['jp_perusahaan'];
        $b['total_bpjstk_karyawan'] = $b['jht_karyawan'] + $b['jp_karyawan'];
        $b['total_bpjs_perusahaan'] = $b['total_bpjstk_perusahaan'] + $b['bpjs_kes_perusahaan'];
        $b['total_bpjs_karyawan'] = $b['total_bpjstk_karyawan'] + $b['bpjs_kes_karyawan'];

        return $b;
    }

    private function zeroBpjs(): array
    {
        return [
            'jht_perusahaan' => 0, 'jkm_perusahaan' => 0, 'jkk_perusahaan' => 0, 'jp_perusahaan' => 0,
            'total_bpjstk_perusahaan' => 0,
            'jht_karyawan' => 0, 'jp_karyawan' => 0, 'total_bpjstk_karyawan' => 0,
            'bpjs_kes_perusahaan' => 0, 'bpjs_kes_karyawan' => 0, 'biaya_tanggungan' => 0,
            'total_bpjs_perusahaan' => 0, 'total_bpjs_karyawan' => 0,
        ];
    }

    private function buildPayrollAttributes(Request $request, $karyawan, $umkFallback = 2100000): array
    {
        $umkBandung = $request->umk_bandung ?: $umkFallback;
        $salaryBPJSTK = $request->salary_bpjstk;
        $bpjs = $this->resolveBpjs($request, $salaryBPJSTK, $umkBandung);

        if ($karyawan && (
            $karyawan->jabatan === 'Outsource' ||
            $karyawan->jabatan === 'Pilih Jabatan' ||
            str_starts_with($karyawan->kode_karyawan ?? '', 'OL') ||
            empty($karyawan->nip)
        )) {
            $salaryBPJSTK = 0;
            $bpjs = $this->zeroBpjs();
        }

        $tunjanganDetail = $this->normalizeTunjangan($request->tunjangan ?? []);
        $totalTunjangan = array_sum(array_column($tunjanganDetail, 'total'));

        $potongan = $request->potongan ?? [];
        $pph21Tersimpan = $karyawan
            ? (int) Pph21Karyawan::where('karyawan_id', $karyawan->id)->value('total_pph')
            : 0;

        $potonganPph = $pph21Tersimpan > 0
            ? $pph21Tersimpan
            : (int) ($potongan['pph21'] ?? 0);
        $potonganKasbon = 0;
        $potonganDenda = 0;
        $potonganLain = 0;
        $totalPotLain = $potonganPph;

        $thpKotor = $request->gaji_pokok + $totalTunjangan;
        $thpBersih = $thpKotor - $bpjs['total_bpjs_karyawan'] - $bpjs['biaya_tanggungan'] - $totalPotLain;
        $totalBiayaPerusahaan = $thpKotor + $bpjs['total_bpjs_perusahaan'];

        return [
            'gaji_pokok' => $request->gaji_pokok,
            'salary_bpjstk' => $salaryBPJSTK,
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
            'biaya_tanggungan' => $bpjs['biaya_tanggungan'],
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
        ];
    }
}