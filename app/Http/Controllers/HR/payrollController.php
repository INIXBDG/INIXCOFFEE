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
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use App\Models\ThrPeriod;
use App\Models\ThrItem;

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

    private function isBpjsEligible(karyawan $karyawan): bool
    {
        if ((int) $karyawan->id === 2) {
            return true;
        }

        if (in_array((int) $karyawan->id, [1, 3], true)) {
            return false;
        }

        $jabatan = strtolower(trim((string) $karyawan->jabatan));
        if (
            str_contains($jabatan, 'direksi')
            || str_contains($jabatan, 'direktur')
            || str_contains($jabatan, 'komisaris')
            || $jabatan === 'outsource'
            || $jabatan === 'pilih jabatan'
        ) {
            return false;
        }

        return !str_starts_with(strtoupper((string) $karyawan->kode_karyawan), 'OL')
            && !empty($karyawan->nip);
    }

    private function bpjsKaryawanQuery()
    {
        return karyawan::with('divisi')
            ->where('status_aktif', '1')
            ->whereNotNull('nip')
            ->where(function ($query) {
                $query->where('id', 2)
                    ->orWhere(function ($query) {
                        $query->whereRaw("LOWER(COALESCE(jabatan, '')) NOT LIKE ?", ['%direksi%'])
                            ->whereRaw("LOWER(COALESCE(jabatan, '')) NOT LIKE ?", ['%direktur%'])
                            ->whereRaw("LOWER(COALESCE(jabatan, '')) NOT LIKE ?", ['%komisaris%'])
                            ->whereNotIn('jabatan', ['Outsource', 'Pilih Jabatan']);
                    });
            })
            ->whereNotIn('id', [1, 3])
            ->where(function ($query) {
                $query->whereNull('kode_karyawan')
                    ->orWhere('kode_karyawan', 'NOT LIKE', 'OL%');
            });
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
        $karyawans = $this->bpjsKaryawanQuery()
            ->where('bpjs_status', '!=', 'permanent')
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->whereNot('jabatan', 'Outsource')
            ->whereNot('divisi', 'Pilih Divisi')
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
        if (!$this->isBpjsEligible($karyawan)) {
            return $this->respondError('Jabatan Direksi atau Direksi/komisaris tidak masuk perhitungan BPJS, kecuali Ray Manurung.');
        }
        if (in_array($karyawan->bpjs_status, ['suspended', 'permanent'], true)) {
            return $this->respondError('Status BPJS karyawan sedang nonaktif. Aktifkan kembali sebelum membuat atau mengubah perhitungan.');
        }
        $pph21Data = Pph21Karyawan::where('karyawan_id', $karyawan->id)->first();
        $employeeLogs = LogGaji::where('id_karyawan', $karyawan->id)->get();
        $gajiDasar = (int) $this->getGajiPokokFromLogs($employeeLogs, $bulan, $tahun);

        $gaji = (int) round($this->getSalaryComponentsFromLogs($employeeLogs, $bulan, $tahun)['gaji']);
        $pph21Setup = 0;
        if ($pph21Data && $pph21Data->ptkp) {
            $pph21Setup = $this->hitungPph21Bulanan($gaji, $pph21Data->ptkp);
        }
        $existing = $this->latestPayrollOf($karyawan->id, $bulan, $tahun);

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
            'bpjs_status' => $karyawan->bpjs_status ?: 'active',
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

        [$bulan, $tahun] = $this->currentPeriod($request);

        DB::beginTransaction();
        try {
            $karyawan = karyawan::whereKey($request->karyawan_id)->lockForUpdate()->first();
            if (!$karyawan) {
                DB::rollBack();
                return $this->respondError('Karyawan tidak ditemukan!');
            }
            if (!$this->isBpjsEligible($karyawan)) {
                DB::rollBack();
                return $this->respondError('Jabatan Direksi atau komisaris tidak masuk perhitungan BPJS, kecuali Ray Manurung.');
            }
            if ($karyawan->jabatan === 'Outsource' || $karyawan->jabatan === 'Pilih Jabatan') {
                DB::rollBack();
                return $this->respondError('Karyawan dengan jabatan ini tidak masuk perhitungan BPJS!');
            }
            if (str_starts_with($karyawan->kode_karyawan ?? '', 'OL')) {
                DB::rollBack();
                return $this->respondError('Karyawan outsource (OL) tidak masuk perhitungan BPJS!');
            }
            if (empty($karyawan->nip)) {
                DB::rollBack();
                return $this->respondError('Karyawan tanpa NIP tidak masuk perhitungan BPJS!');
            }
            if (in_array($karyawan->bpjs_status, ['suspended', 'permanent'], true)) {
                DB::rollBack();
                return $this->respondError('Status BPJS karyawan sedang nonaktif. Aktifkan kembali sebelum membuat perhitungan.');
            }

            $attrs = $this->buildPayrollAttributes($request, $karyawan);
            $existing = PerhitunganTunjanganHR::withTrashed()
                ->where('karyawan_id', $karyawan->id)
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                $oldData = $existing->toArray();
                if ($existing->trashed()) {
                    $existing->restore();
                }

                $attrs['updated_by'] = Auth::id();
                $existing->update($attrs);
                $this->logManualChanges($oldData, $existing->fresh(), $karyawan->id);
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Payroll berhasil diupdate!',
                    'data' => $existing->fresh()->load(['karyawan.divisi', 'createdBy']),
                ]);
            }

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
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            if ((int) $e->errorInfo[1] === 1062) {
                return $this->respondError('Data periode ini sudah tersimpan. Silakan muat ulang halaman, lalu gunakan tombol Update.');
            }
            return $this->respondError('Terjadi kesalahan: ' . $e->getMessage());
        } catch (\Throwable $e) {
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
        $bpjsStatus = $perhitungan->karyawan?->bpjs_status ?: 'active';
        if (!$perhitungan->karyawan || !$this->isBpjsEligible($perhitungan->karyawan)) {
            return $this->respondError('Karyawan ini tidak masuk perhitungan BPJS.');
        }
        if (in_array($bpjsStatus, ['suspended', 'permanent'], true)) {
            return $this->respondError('Status BPJS karyawan sedang nonaktif. Aktifkan kembali sebelum mengubah perhitungan.');
        }

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

        $karyawans = $this->bpjsKaryawanQuery()
            ->where('bpjs_status', '!=', 'permanent')
            ->whereNot('jabatan', 'Outsource')
            ->whereNot('jabatan', 'Pilih Jabatan')
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
                'bpjs_status' => $emp->bpjs_status ?: 'active',
                'bpjs_restore_until' => $emp->bpjs_restore_until?->format('d/m/Y H:i'),
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

        $payrolls = $payrolls->filter(fn($p) => $p->karyawan && $this->isBpjsEligible($p->karyawan))->values();

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

        $trendKaryawans = $this->bpjsKaryawanQuery()->get();
        if ($divisi) {
            $trendKaryawans = $trendKaryawans->filter(
                fn($employee) => $this->divisiName($employee) === $divisi
            );
        }
        $trendEligibleIds = $trendKaryawans->pluck('id');
        $trendTahun = $tahun ?: date('Y');

        $trendThp = LogGaji::selectRaw(
            '
                bulan,
                SUM(CAST(COALESCE(gaji, 0) AS DECIMAL(15,2)) + COALESCE(tunjangan_jabatan, 0)) as total_thp
            '
        )
            ->whereIn('id_karyawan', $trendEligibleIds)
            ->where('tahun', $trendTahun)
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get()
            ->keyBy(fn($row) => (int) $row->bulan);

        $trendBpjs = PerhitunganTunjanganHR::selectRaw(
            '
                bulan,
                SUM(total_bpjs_perusahaan) as total_bpjs_per,
                SUM(total_bpjs_karyawan) as total_bpjs_kar,
                COUNT(*) as jumlah
            '
        )
            ->whereIn('karyawan_id', $trendEligibleIds)
            ->where('tahun', $trendTahun)
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get()
            ->keyBy(fn($row) => (int) $row->bulan);

        $trendMonths = $trendThp->keys()
            ->merge($trendBpjs->keys())
            ->unique()
            ->sort()
            ->values();

        $trendQuery = $trendMonths->map(function ($bulan) use ($trendThp, $trendBpjs) {
            return [
                'bulan' => $bulan,
                'total_thp' => (float) ($trendThp->get($bulan)->total_thp ?? 0),
                'total_bpjs_per' => (float) ($trendBpjs->get($bulan)->total_bpjs_per ?? 0),
                'total_bpjs_kar' => (float) ($trendBpjs->get($bulan)->total_bpjs_kar ?? 0),
                'jumlah' => (int) ($trendBpjs->get($bulan)->jumlah ?? 0),
            ];
        })->values();

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
        return $this->hitungPph21((int) $gajiDasar, $ptkpCode)['bulanan'];
    }

    public function getPphAnalysis(Request $request)
    {
        [$bulan, $tahun] = $this->currentPeriod($request);
        $awalPeriode = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
        $prevDate = $awalPeriode->copy()->subMonth();
 
        $list = $this->pphKaryawanPeriode($bulan, $tahun);
        $ids = $list->pluck('id')->all();
        $ctx = $this->pphContext($bulan, $tahun, $ids);
        $laporan = Pph21Laporan::where('bulan', $bulan)->where('tahun', $tahun)->first();
 
        $anomali = [];
        $tambah = function (string $tipe, string $level, $k, string $pesan) use (&$anomali) {
            $anomali[] = [
                'tipe' => $tipe,
                'level' => $level,
                'karyawan_id' => $k->id,
                'nip' => $k->nip ?? '-',
                'nama' => $k->nama_lengkap ?? '-',
                'pesan' => $pesan,
            ];
        };
        $rp = fn($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');
 
        $rows = [];
        foreach ($list as $k) {
            $ptkp = $k->pph21->ptkp ?? null;
            $hit = $this->hitungPphKaryawan($k, $ctx, $ptkp);
            $res = $this->resolvePph($k, $ctx);
            $awal = $ctx['awal'][$k->id] ?? null;
            $rec = $ctx['records']->get($k->id);
 
            $rows[] = [
                'id' => $k->id,
                'nip' => $k->nip ?? '-',
                'nama' => $k->nama_lengkap ?? '-',
                'divisi' => $this->divisiName($k),
                'ptkp' => $ptkp,
                'kategori' => $hit['kategori'],
                'nilai' => $res ? (int) $res['nilai'] : null,
                'tersimpan' => (bool) ($res['tersimpan'] ?? false),
            ];
 
            // Nilai tersimpan tidak lagi sama dengan hasil hitung saat ini (PTKP / penghasilan berubah)
            if ($rec && $ptkp && ($rec->sumber ?? '') !== 'tunjangan' && abs((int) $rec->total_pph - $hit['nilai']) > 1000) {
                $ptkpDiubah = $k->pph21 && $k->pph21->updated_at && $rec->updated_at
                    && $k->pph21->updated_at->gt($rec->updated_at->copy()->addMinute());
                $tambah(
                    $ptkpDiubah ? 'ptkp_berubah' : 'nilai_tidak_sesuai',
                    'sedang',
                    $k,
                    ($ptkpDiubah ? 'Status PTKP diubah setelah nilai disimpan. ' : 'Gaji/penghasilan berubah setelah nilai disimpan. ')
                    . 'Tersimpan ' . $rp($rec->total_pph) . ', hasil hitung sekarang ' . $rp($hit['nilai']) . '.'
                );
            }
 
            // Nilai dari Tunjangan vs hasil hitung (TER / tahunan)
            if ($awal && $ptkp) {
                $selisih = abs((int) $awal['total'] - $hit['nilai']);
                $dasar = max((int) $awal['total'], $hit['nilai'], 1);
                if ($selisih >= 50000 && ($selisih / $dasar) >= 0.10) {
                    $tambah('selisih_tunjangan', 'sedang', $k,
                        'Nilai dari Tunjangan ' . $rp($awal['total']) . ' berbeda ' . $rp($selisih)
                        . ' dari hasil hitung ' . ($hit['masa_terakhir'] ? 'tahunan' : 'TER') . ' ' . $rp($hit['nilai']) . '.');
                }
            }
 
            foreach ($hit['peringatan'] as $p) {
                $tambah('masa_terakhir', 'sedang', $k, $p);
            }
        }
 
        // Karyawan resign tetapi status masih aktif (tidak ikut periode ini)
        $resign = $this->pphKaryawanQuery()
            ->whereNotNull('resigned_at')
            ->where('resigned_at', '<', $awalPeriode)
            ->get();
        foreach ($resign as $k) {
            $tambah('resign_masih_aktif', 'tinggi', $k,
                'Resign ' . Carbon::parse($k->resigned_at)->format('d/m/Y') . ' tetapi status karyawan masih aktif. Perbarui status aktif di data karyawan.');
        }
 
        // Ada nilai tersimpan untuk karyawan yang tidak eligible periode ini
        $yatim = Pph21Bulanan::where('bulan', $bulan)->where('tahun', $tahun)->whereNotIn('karyawan_id', $ids)->get();
        if ($yatim->isNotEmpty()) {
            $namaKaryawan = karyawan::whereIn('id', $yatim->pluck('karyawan_id'))->get()->keyBy('id');
            foreach ($yatim as $y) {
                $k = $namaKaryawan->get($y->karyawan_id);
                if (!$k) continue;
                $tambah('resign_masih_muncul', 'tinggi', $k,
                    'Ada nilai PPH 21 tersimpan (' . $rp($y->total_pph) . ') padahal karyawan tidak eligible pada periode ini (sudah resign / belum bergabung / dikecualikan).');
            }
        }
 
        $urut = ['tinggi' => 0, 'sedang' => 1, 'rendah' => 2];
        usort($anomali, fn($a, $b) => $urut[$a['level']] <=> $urut[$b['level']]);
        $jumlahTinggi = count(array_filter($anomali, fn($a) => $a['level'] === 'tinggi'));
 
        $koleksi = collect($rows);
        $total = $koleksi->count();
        $punyaPtkp = $koleksi->whereNotNull('ptkp')->count();
        $tersimpan = $koleksi->where('tersimpan', true)->count();
        $totalPph = (int) $koleksi->sum(fn($r) => $r['nilai'] ?? 0);
        $totalPrev = (int) Pph21Bulanan::where('bulan', $prevDate->month)->where('tahun', $prevDate->year)
            ->whereIn('karyawan_id', $ids)->sum('total_pph');
 
        $kesehatan = [
            'total_eligible' => $total,
            'punya_ptkp' => $punyaPtkp,
            'belum_ptkp' => $total - $punyaPtkp,
            'sudah_diisi' => $tersimpan,
            'belum_diisi' => $total - $tersimpan,
            'total_pph' => $totalPph,
            'total_pph_bulan_lalu' => $totalPrev,
            'selisih' => $totalPph - $totalPrev,
            'persen' => $totalPrev > 0 ? round((($totalPph - $totalPrev) / $totalPrev) * 100, 1) : null,
            'bulan_lalu_label' => str_pad((string) $prevDate->month, 2, '0', STR_PAD_LEFT) . '/' . $prevDate->year,
        ];
 
        $isiLengkap = $total > 0 && $tersimpan === $total;
        $steps = [
            ['kode' => 'ptkp', 'judul' => 'Setup PTKP', 'selesai' => $total > 0 && $punyaPtkp === $total, 'detail' => "{$punyaPtkp}/{$total} karyawan"],
            ['kode' => 'isi', 'judul' => 'Isi nilai bulan ini', 'selesai' => $isiLengkap, 'detail' => "{$tersimpan}/{$total} tersimpan"],
            ['kode' => 'review', 'judul' => 'Review', 'selesai' => $isiLengkap && $jumlahTinggi === 0, 'detail' => $isiLengkap ? ($jumlahTinggi ? "{$jumlahTinggi} anomali tinggi" : 'Tidak ada anomali tinggi') : 'Menunggu pengisian'],
            ['kode' => 'kortex', 'judul' => 'Lapor Kortex', 'selesai' => (bool) ($laporan && $laporan->lapor_kortex_at), 'detail' => $laporan && $laporan->lapor_kortex_at ? $laporan->lapor_kortex_at->format('d/m/Y H:i') : 'Belum'],
            ['kode' => 'billing', 'judul' => 'Kode Billing', 'selesai' => (bool) ($laporan && $laporan->kode_billing_at), 'detail' => $laporan && $laporan->kode_billing_at ? $laporan->kode_billing_at->format('d/m/Y H:i') : 'Belum'],
            ['kode' => 'bukti', 'judul' => 'Bukti Bayar', 'selesai' => (bool) ($laporan && $laporan->bukti_bayar_at), 'detail' => $laporan && $laporan->bukti_bayar_at ? $laporan->bukti_bayar_at->format('d/m/Y H:i') : 'Belum'],
        ];
        $aktif = collect($steps)->search(fn($s) => !$s['selesai']);
        $aktif = $aktif === false ? count($steps) : $aktif;
 
        $perKategori = ['A' => ['jumlah' => 0, 'total' => 0], 'B' => ['jumlah' => 0, 'total' => 0], 'C' => ['jumlah' => 0, 'total' => 0], 'Belum PTKP' => ['jumlah' => 0, 'total' => 0]];
        foreach ($rows as $r) {
            $key = $r['kategori'] ?? 'Belum PTKP';
            $perKategori[$key]['jumlah']++;
            $perKategori[$key]['total'] += (int) ($r['nilai'] ?? 0);
        }
 
        $rentang = [
            ['label' => 'Rp 0', 'min' => 0, 'max' => 0],
            ['label' => '1 - 500 rb', 'min' => 1, 'max' => 500000],
            ['label' => '500 rb - 1 jt', 'min' => 500001, 'max' => 1000000],
            ['label' => '1 - 2,5 jt', 'min' => 1000001, 'max' => 2500000],
            ['label' => '2,5 - 5 jt', 'min' => 2500001, 'max' => 5000000],
            ['label' => '5 - 10 jt', 'min' => 5000001, 'max' => 10000000],
            ['label' => '> 10 jt', 'min' => 10000001, 'max' => PHP_INT_MAX],
        ];
        foreach ($rentang as &$rg) {
            $rg['jumlah'] = $koleksi->filter(fn($r) => $r['nilai'] !== null && $r['nilai'] >= $rg['min'] && $r['nilai'] <= $rg['max'])->count();
        }
        unset($rg);
 
        $top = $koleksi->whereNotNull('nilai')->sortByDesc('nilai')->take(10)->values()
            ->map(fn($r) => ['nip' => $r['nip'], 'nama' => $r['nama'], 'divisi' => $r['divisi'], 'ptkp' => $r['ptkp'], 'nilai' => $r['nilai']]);
 
        $perDivisi = $koleksi->groupBy('divisi')->map(fn($g, $d) => [
            'divisi' => $d,
            'jumlah' => $g->count(),
            'total' => (int) $g->sum(fn($r) => $r['nilai'] ?? 0),
            'rata_rata' => $g->count() ? (int) round($g->sum(fn($r) => $r['nilai'] ?? 0) / $g->count()) : 0,
        ])->sortByDesc('total')->values();
 
        return response()->json([
            'success' => true,
            'meta' => ['bulan' => $bulan, 'tahun' => $tahun, 'terkunci' => (bool) ($laporan && $laporan->lapor_kortex_at)],
            'kesehatan' => $kesehatan,
            'workflow' => ['steps' => $steps, 'aktif' => $aktif],
            'distribusi' => [
                'kategori' => $perKategori,
                'rentang' => $rentang,
                'top' => $top,
                'divisi' => $perDivisi,
            ],
            'anomali' => array_slice($anomali, 0, 200),
            'anomali_total' => count($anomali),
        ]);
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

    private const PTKP_DESEMBER = [
        'TK/0' => 54000000,
        'TK/1' => 58500000,
        'TK/2' => 63000000,
        'TK/3' => 67500000,
        'K/0'  => 58500000,
        'K/1'  => 63000000,
        'K/2'  => 67500000,
        'K/3'  => 72000000,
    ];

    public function indexPph()
    {
        return view('HR.payroll.indexPPH');
    }

    private function pphKaryawanQuery(?int $bulan = null, ?int $tahun = null)
    {
        return karyawan::with('pph21')
            ->where(function ($w) use ($bulan, $tahun) {
                $w->where('status_aktif', '1');
                if ($bulan && $tahun) {
                    $awal = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
                    $w->orWhereBetween('resigned_at', [$awal, $awal->copy()->endOfMonth()]);
                }
            })
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
    private function pphKaryawanPeriode(int $bulan, int $tahun)
    {
        $awal = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
        $akhir = $awal->copy()->endOfMonth();
 
        return $this->pphKaryawanQuery($bulan, $tahun)->get()
            ->filter(fn($k) => $this->isEmployeeEligibleForPeriod($k, $awal, $akhir))
            ->values();
    }

    private function p2(int $n): string
    {
        return str_pad((string) $n, 2, '0', STR_PAD_LEFT);
    }

    private function ringkasBulan(array $bulan): string
    {
        sort($bulan);
        $out = [];
        $start = $prev = null;

        foreach ($bulan as $b) {
            if ($start === null) {
                $start = $prev = $b;
                continue;
            }
            if ($b === $prev + 1) {
                $prev = $b;
                continue;
            }
            $out[] = $start === $prev ? $this->p2($start) : $this->p2($start) . '-' . $this->p2($prev);
            $start = $prev = $b;
        }

        if ($start !== null) {
            $out[] = $start === $prev ? $this->p2($start) : $this->p2($start) . '-' . $this->p2($prev);
        }

        return implode(', ', $out);
    }

    private function periodeThrDesember(int $tahun): array
    {
        $p = ThrPeriod::where('tahun', $tahun)->orderByDesc('id')->first();
        $fallback = false;

        if (!$p) {
            $p = ThrPeriod::orderByDesc('id')->first();
            $fallback = (bool) $p;
        }

        return [
            'period' => $p,
            'info' => $p ? [
                'id' => $p->id,
                'nama' => $p->nama_periode,
                'tahun' => (int) $p->tahun,
                'fallback' => $fallback,
            ] : null,
        ];
    }
 
    private function pphContext(int $bulan, int $tahun, ?array $ids = null): array
    {
        $key = ($tahun * 12) + $bulan;
        $scope = fn($q, string $kolom = 'karyawan_id') => $ids !== null ? $q->whereIn($kolom, $ids) : $q;
 
        $records = $scope(Pph21Bulanan::where('bulan', $bulan)->where('tahun', $tahun))
            ->get()->keyBy('karyawan_id');
 
        $previous = $scope(Pph21Bulanan::whereRaw('(tahun * 12 + bulan) < ?', [$key]))
            ->orderByDesc('tahun')->orderByDesc('bulan')
            ->get()->unique('karyawan_id')->keyBy('karyawan_id');
 
        $tahunIni = $scope(Pph21Bulanan::where('tahun', $tahun)->where('bulan', '<', $bulan))
            ->get()->groupBy('karyawan_id');
 
        $iuran = $scope(PerhitunganTunjanganHR::where('tahun', $tahun)->where('bulan', '<=', $bulan))
            ->get()->groupBy('karyawan_id');
 
        $logs = $scope(LogGaji::where('tahun', '<=', $tahun), 'id_karyawan')
            ->get()->groupBy('id_karyawan');
 
        return [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'records' => $records,
            'previous' => $previous,
            'tahun_ini' => $tahunIni,
            'iuran' => $iuran,
            'logs' => $logs,
            'awal' => $this->tunjanganPph21Awal($bulan, $tahun, null),
        ];
    }

    private function resolvePph($k, array $ctx): ?array
    {
        $ptkp = $k->pph21->ptkp ?? null;
        $hit = $this->hitungPphKaryawan($k, $ctx, $ptkp);
 
        $base = [
            'dari' => null,
            'tersimpan' => false,
            'masa_terakhir' => $hit['masa_terakhir'],
            'peringatan' => $hit['peringatan'],
        ];
 
        $record = $ctx['records']->get($k->id);
        if ($record) {
            return array_merge($base, ['nilai' => (int) $record->total_pph, 'sumber' => 'tersimpan', 'tersimpan' => true]);
        }
 
        $awal = $ctx['awal'][$k->id] ?? null;
        if ($awal) {
            return array_merge($base, ['nilai' => (int) $awal['total'], 'sumber' => 'tunjangan']);
        }
 
        $prev = $ctx['previous']->get($k->id);
        if (
            $prev && !$hit['masa_terakhir']
            && (int) $prev->tahun === $ctx['tahun'] && (int) $prev->bulan < 12
            && $this->brutoBulan($k, (int) $prev->bulan, (int) $prev->tahun, $ctx) === $hit['bruto']
        ) {
            return [
                'nilai' => (int) $prev->total_pph,
                'sumber' => 'bulan_lalu',
                'dari' => str_pad((string) $prev->bulan, 2, '0', STR_PAD_LEFT) . '/' . $prev->tahun,
                'tersimpan' => false,
                'masa_terakhir' => false,
                'peringatan' => [],
            ];
        }
 
        if ($ptkp) {
            return array_merge($base, ['nilai' => $hit['nilai'], 'sumber' => 'hitung']);
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
        $list = $this->pphKaryawanPeriode($bulan, $tahun);
        $ctx = $this->pphContext($bulan, $tahun, $list->pluck('id')->all());
 
        $data = $list->map(function ($k) use ($ctx, $bulan, $tahun) {
            $awal = $ctx['awal'][$k->id] ?? null;
            $ptkp = $k->pph21->ptkp ?? null;
            $hit = $this->hitungPphKaryawan($k, $ctx, $ptkp);
 
            return [
                'id' => $k->id,
                'nip' => $k->nip ?? '-',
                'nama' => $k->nama_lengkap ?? '-',
                'jabatan' => $k->jabatan ?? '-',
                'divisi' => $this->divisiName($k),
                'gaji' => (int) ($k->gaji ?? 0),
                'tunjangan_jabatan' => (int) ($k->tunjangan_jabatan ?? 0),
                'bruto' => $hit['bruto'],
                'ter' => [
                    'kategori' => $hit['kategori'],
                    'tarif' => $hit['tarif'],
                    'nilai' => $hit['ter'],
                ],
                'masa_terakhir' => $hit['masa_terakhir'],
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
        })->values();
 
        $laporan = Pph21Laporan::where('bulan', $bulan)->where('tahun', $tahun)->first();
 
        return response()->json([
            'success' => true,
            'data' => $data,
            'laporan' => $this->laporanPayload($laporan, $bulan, $tahun),
            'meta' => [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'terkunci' => (bool) ($laporan && $laporan->lapor_kortex_at),
                'masa_terakhir' => $data->contains('masa_terakhir', true),
            ],
        ]);
    }

    private function pasal17Lapisan(int $pkp): array
    {
        $layers = [
            [0, 60000000, 0.05],
            [60000000, 250000000, 0.15],
            [250000000, 500000000, 0.25],
            [500000000, 5000000000, 0.30],
            [5000000000, INF, 0.35],
        ];

        $out = [];
        foreach ($layers as [$dari, $sampai, $rate]) {
            $kena = max(0, min($pkp, $sampai) - $dari);
            if ($kena <= 0) {
                break;
            }
            $out[] = [
                'dari' => $dari,
                'sampai' => is_infinite($sampai) ? null : $sampai,
                'tarif' => $rate * 100,
                'dasar' => (int) $kena,
                'pajak' => (int) round($kena * $rate),
            ];
        }

        return $out;
    }

    private function filterPphDesember(array $rows, ?string $search, ?string $status, ?string $divisi): array
    {
        $search = strtolower(trim((string) $search));

        $hasil = array_values(array_filter($rows, function ($r) use ($search, $status, $divisi) {
            if ($divisi && $r['divisi'] !== $divisi) {
                return false;
            }

            if ($search !== ''
                && !str_contains(strtolower($r['nama']), $search)
                && !str_contains(strtolower((string) $r['nip']), $search)) {
                return false;
            }

            switch ($status) {
                case 'kena':           return $r['pajak_setahun'] > 0;
                case 'tidak_kena':     return $r['pajak_setahun'] <= 0;
                case 'kurang_bayar':   return $r['terhutang'] > 0;
                case 'lebih_bayar':    return $r['terhutang'] < 0;
                case 'belum_ptkp':     return empty($r['ptkp_kode']);
                case 'ada_peringatan': return !empty($r['peringatan']);
                default:               return true;
            }
        }));

        foreach ($hasil as $i => &$r) {
            $r['no'] = $i + 1;
        }
        unset($r);

        return $hasil;
    }

    private function totalPphDesember(array $rows): array
    {
        $k = collect($rows);

        return [
            'total_gaji' => (int) $k->sum('total_gaji'),
            'thr' => (int) $k->sum('thr'),
            'bruto' => (int) $k->sum('bruto'),
            'biaya_jabatan' => (int) $k->sum('biaya_jabatan'),
            'neto' => (int) $k->sum('neto'),
            'pkp' => (int) $k->sum('pkp'),
            'pajak_setahun' => (int) $k->sum('pajak_setahun'),
            'sudah_dipotong' => (int) $k->sum('sudah_dipotong'),
            'terhutang' => (int) $k->sum('terhutang'),
        ];
    }

    private function labelStatusDesember(?string $status): string
    {
        return [
            'kena' => 'Kena PPh',
            'tidak_kena' => 'Tidak kena PPh',
            'kurang_bayar' => 'Kurang bayar',
            'lebih_bayar' => 'Lebih bayar (kelebihan potong)',
            'belum_ptkp' => 'Belum ada PTKP',
            'ada_peringatan' => 'Ada peringatan data',
        ][$status] ?? 'Semua';
    }

    /** Dipakai bersama oleh tabel, export Excel, dan export PDF agar filternya identik. */
    private function dataDesember(Request $request): array
    {
        $tahun = (int) ($request->input('tahun') ?: now()->year);
        $semua = $this->buildPphDesember($tahun);
        $rows = $this->filterPphDesember(
            $semua,
            $request->input('search'),
            $request->input('status'),
            $request->input('divisi')
        );

        $filterLabel = [];
        if ($request->filled('status')) {
            $filterLabel[] = 'Status: ' . $this->labelStatusDesember($request->input('status'));
        }
        if ($request->filled('divisi')) {
            $filterLabel[] = 'Divisi: ' . $request->input('divisi');
        }
        if ($request->filled('search')) {
            $filterLabel[] = 'Pencarian: "' . $request->input('search') . '"';
        }

        $thrInfo = $this->periodeThrDesember($tahun)['info'];
        $thrLabel = $thrInfo
            ? 'THR dari fitur THR: ' . $thrInfo['nama'] . ' (' . $thrInfo['tahun'] . ')'
                . ($thrInfo['fallback'] ? ' - periode tahun lain karena belum ada THR tahun ini' : '')
            : 'THR: belum ada periode THR (dihitung Rp 0)';

        return [
            'tahun' => $tahun,
            'semua' => $semua,
            'rows' => $rows,
            'total' => $this->totalPphDesember($rows),
            'filter_label' => $filterLabel ? implode(' | ', $filterLabel) : 'Semua data',
            'thr_info' => $thrInfo,
            'thr_label' => $thrLabel,
        ];
    }

    public function getPphDesember(Request $request)
    {
        $d = $this->dataDesember($request);

        return response()->json([
            'success' => true,
            'tahun' => $d['tahun'],
            'data' => $d['rows'],
            'total' => $d['total'],
            'jumlah' => count($d['rows']),
            'jumlah_semua' => count($d['semua']),
            'thr_info' => $d['thr_info'],
            'divisi_list' => collect($d['semua'])->pluck('divisi')->unique()->filter(fn($x) => $x && $x !== '-')->sort()->values(),
        ]);
    }

    private function buildPphDesember(int $tahun): array
    {
        $awalTahun = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $akhirTahun = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $list = karyawan::with('pph21')
            ->where(function ($w) use ($tahun) {
                $w->where('status_aktif', '1')->orWhereYear('resigned_at', $tahun);
            })
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->whereNot('jabatan', 'Outsource')
            ->whereNot('divisi', 'Pilih Divisi')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNotNull('nip')
            ->orderBy('nama_lengkap')
            ->get()
            ->filter(fn($k) => $this->isEmployeeEligibleForPeriod($k, $awalTahun, $akhirTahun))
            ->values();

        $ids = $list->pluck('id')->all();

        $ctx = [
            'logs' => LogGaji::where('tahun', '<=', $tahun)
                ->whereIn('id_karyawan', $ids)
                ->get()
                ->groupBy('id_karyawan'),
        ];

        $pphBulanan = Pph21Bulanan::where('tahun', $tahun)
            ->whereIn('karyawan_id', $ids)
            ->get()
            ->groupBy('karyawan_id')
            ->map(fn($g) => $g->keyBy('bulan'));

        // THR dari fitur THR (periode terbaru), hanya yang ikut dihitung
        $periodeThr = $this->periodeThrDesember($tahun)['period'];
        $thrData = $periodeThr
            ? ThrItem::where('thr_period_id', $periodeThr->id)
                ->whereIn('karyawan_id', $ids)
                ->where('is_excluded', false)
                ->get()
                ->keyBy('karyawan_id')
            : collect();

        $rows = [];
        $no = 1;

        foreach ($list as $k) {
            $ptkpCode = $k->pph21->ptkp ?? null;
            $mulai = $this->bulanMulaiKerja($k, $tahun);
            $akhir = 12;
            if (!empty($k->resigned_at)) {
                $r = Carbon::parse($k->resigned_at);
                if ($r->year === $tahun) {
                    $akhir = $r->month;
                }
            }

            // 1) Data PPh yang benar-benar tersimpan pada rentang bulan kerja
            $asli = [];
            for ($m = $mulai; $m <= $akhir; $m++) {
                $rec = $pphBulanan->get($k->id)?->get($m);
                if ($rec) {
                    $asli[$m] = (int) $rec->total_pph;
                }
            }
            $bulanAsli = array_keys($asli);

            // 2) Susun Jan-Des: bulan kosong memakai data tersimpan terdekat sebelumnya,
            //    bulan sebelum data pertama memakai data pertama
            $gajiBulan = [];
            $pphBulan = [];
            $pphSumber = [];
            $totalGaji = 0;
            $sudahDipotong = 0;

            for ($m = 1; $m <= 12; $m++) {
                if ($m < $mulai || $m > $akhir) {
                    $gajiBulan[$m] = 0;
                    $pphBulan[$m] = 0;
                    $pphSumber[$m] = 0;
                    continue;
                }

                $gajiBulan[$m] = $this->brutoBulan($k, $m, $tahun, $ctx);

                if (isset($asli[$m])) {
                    $pphBulan[$m] = $asli[$m];
                    $pphSumber[$m] = 0;
                } elseif ($bulanAsli) {
                    $sebelum = array_filter($bulanAsli, fn($x) => $x < $m);
                    $src = $sebelum ? max($sebelum) : min($bulanAsli);
                    $pphBulan[$m] = $asli[$src];
                    $pphSumber[$m] = $src;
                } else {
                    $pphBulan[$m] = 0;
                    $pphSumber[$m] = 0;
                }

                $totalGaji += $gajiBulan[$m];
                $sudahDipotong += $pphBulan[$m];
            }

            $catatanPph = [];
            $grup = [];
            foreach ($pphSumber as $m => $src) {
                if ($src > 0) {
                    $grup[$src][] = $m;
                }
            }
            ksort($grup);
            foreach ($grup as $src => $bulanList) {
                $catatanPph[] = 'PPh bulan ' . $this->ringkasBulan($bulanList) . ' memakai data bulan ' . $this->p2($src) . '.';
            }

            $thr = (int) round((float) ($thrData->get($k->id)->nilai_thr_final ?? 0));
            $bruto = $totalGaji + $thr;
            $jumlahBulan = max(1, $akhir - $mulai + 1);

            // 5% dari bruto, maksimal Rp6.000.000 setahun (Rp500.000 x jumlah bulan bekerja)
            $maksBiayaJabatan = 500000 * $jumlahBulan;
            $biayaJabatan = (int) min(round($bruto * 0.05), $maksBiayaJabatan);

            $neto = $bruto - $biayaJabatan;
            $ptkp = self::PTKP_DESEMBER[$ptkpCode] ?? 0;
            $pkp = max(0, $neto - $ptkp);
            $pkp = intdiv($pkp, 1000) * 1000;

            $lapisan = $this->pasal17Lapisan($pkp);
            $pajakSetahun = (int) array_sum(array_column($lapisan, 'pajak'));
            $terhutang = $pajakSetahun - $sudahDipotong;

            $peringatan = [];
            if (!$ptkpCode) {
                $peringatan[] = 'Status PTKP belum diisi, PTKP dihitung Rp 0.';
            }
            if (!$bulanAsli) {
                $peringatan[] = 'Belum ada data PPh 21 yang tersimpan pada tahun ini, PPh dihitung Rp 0.';
            }
            if ($terhutang < 0) {
                $peringatan[] = 'Kelebihan potong, harus dikembalikan ke karyawan.';
            }

            $rows[] = [
                'no' => $no++,
                'id' => $k->id,
                'nip' => $k->nip ?? '-',
                'nama' => $k->nama_lengkap ?? '-',
                'divisi' => $this->divisiName($k),
                'gaji_pokok' => (int) round((float) ($k->gaji ?? 0)),
                'gaji_bulan' => array_values($gajiBulan),
                'pph_bulan' => array_values($pphBulan),
                'pph_sumber' => array_values($pphSumber),
                'catatan_pph' => $catatanPph,
                'total_gaji' => $totalGaji,
                'thr' => $thr,
                'bruto' => $bruto,
                'biaya_jabatan' => $biayaJabatan,
                'maks_biaya_jabatan' => $maksBiayaJabatan,
                'neto' => $neto,
                'ptkp_kode' => $ptkpCode,
                'ptkp' => $ptkp,
                'pkp' => $pkp,
                'lapisan' => $lapisan,
                'pajak_setahun' => $pajakSetahun,
                'sudah_dipotong' => $sudahDipotong,
                'terhutang' => $terhutang,
                'peringatan' => $peringatan,
            ];
        }

        return $rows;
    }

    private function labelSumberPph(?array $res): string
    {
        if (!$res) {
            return 'Belum ada';
        }

        return match ($res['sumber']) {
            'tersimpan' => 'Tersimpan',
            'tunjangan' => 'Dari tunjangan (belum disimpan)',
            'bulan_lalu' => 'Dari bulan ' . ($res['dari'] ?? '-') . ' (belum disimpan)',
            default => !empty($res['masa_terakhir']) ? 'Hitung tahunan (belum disimpan)' : 'Hitung TER (belum disimpan)',
        };
    }

    private function dataPphBulanan(Request $request): array
    {
        [$bulan, $tahun] = $this->currentPeriod($request);
        $namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $list = $this->pphKaryawanPeriode($bulan, $tahun);
        $ctx = $this->pphContext($bulan, $tahun, $list->pluck('id')->all());

        $search = strtolower(trim((string) $request->input('search')));
        $divisi = $request->input('divisi');
        $ptkpFilter = $request->input('ptkp');

        $rows = [];
        foreach ($list as $k) {
            $ptkp = $k->pph21->ptkp ?? null;
            $divisiNama = $this->divisiName($k);

            if ($divisi && $divisiNama !== $divisi) {
                continue;
            }
            if ($ptkpFilter && $ptkp !== $ptkpFilter) {
                continue;
            }
            if ($search !== ''
                && !str_contains(strtolower($k->nama_lengkap ?? ''), $search)
                && !str_contains(strtolower((string) $k->nip), $search)) {
                continue;
            }

            $hit = $this->hitungPphKaryawan($k, $ctx, $ptkp);
            $res = $this->resolvePph($k, $ctx);

            $rows[] = [
                'no' => count($rows) + 1,
                'nip' => $k->nip ?? '-',
                'nama' => $k->nama_lengkap ?? '-',
                'jabatan' => $k->jabatan ?? '-',
                'divisi' => $divisiNama,
                'gaji_dasar' => (int) $hit['bruto'],
                'ptkp' => $ptkp ?? '-',
                'kategori' => $hit['kategori'] ?? '-',
                'menikah' => $k->pph21 ? ((int) $k->pph21->status_menikah ? 'Menikah' : 'Belum') : '-',
                'anak' => $k->pph21 ? count($this->decodeAnak($k->pph21->anak)) : 0,
                'pph' => $res ? (int) $res['nilai'] : 0,
                'sumber' => $this->labelSumberPph($res),
            ];
        }

        $laporan = Pph21Laporan::where('bulan', $bulan)->where('tahun', $tahun)->first();

        $filter = [];
        if ($divisi) $filter[] = 'Divisi: ' . $divisi;
        if ($ptkpFilter) $filter[] = 'PTKP: ' . $ptkpFilter;
        if ($search !== '') $filter[] = 'Pencarian: "' . $request->input('search') . '"';

        return [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'periode_label' => $namaBulan[$bulan] . ' ' . $tahun,
            'rows' => $rows,
            'total_pph' => (int) array_sum(array_column($rows, 'pph')),
            'filter_label' => $filter ? implode(' | ', $filter) : 'Semua data',
            'status_laporan' => [
                'Lapor Kortex' => (bool) ($laporan && $laporan->lapor_kortex_at),
                'Bukti Bayar' => (bool) ($laporan && $laporan->bukti_bayar_at),
                'Kode Billing' => (bool) ($laporan && $laporan->kode_billing_at),
            ],
        ];
    }

    public function exportPphDataExcel(Request $request)
    {
        $d = $this->dataPphBulanan($request);

        $headers = ['No', 'NIP', 'Nama', 'Jabatan', 'Divisi', 'Gaji Dasar', 'Status PTKP', 'Kategori TER',
                    'Menikah', 'Jml Anak', 'PPh 21/Bulan', 'Sumber Nilai'];
        $lastLetter = Coordinate::stringFromColumnIndex(count($headers));

        $ss = new Spreadsheet();
        $sheet = $ss->getActiveSheet();
        $sheet->setTitle('PPh21 Bulanan');

        $sheet->mergeCells("A1:{$lastLetter}1");
        $sheet->setCellValue('A1', 'LAPORAN PPH 21 KARYAWAN');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A2:{$lastLetter}2");
        $sheet->setCellValue('A2', 'Periode: ' . $d['periode_label'] . '  |  Filter: ' . $d['filter_label']);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $status = [];
        foreach ($d['status_laporan'] as $nama => $sudah) {
            $status[] = $nama . ': ' . ($sudah ? 'Sudah' : 'Belum');
        }
        $sheet->mergeCells("A3:{$lastLetter}3");
        $sheet->setCellValue('A3', implode('  |  ', $status));
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A3')->getFont()->setItalic(true);

        $h = 5;
        $sheet->fromArray($headers, null, "A{$h}");
        $sheet->getStyle("A{$h}:{$lastLetter}{$h}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E79']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $r = $h + 1;
        foreach ($d['rows'] as $row) {
            $sheet->fromArray([
                $row['no'], $row['nip'], $row['nama'], $row['jabatan'], $row['divisi'], $row['gaji_dasar'],
                $row['ptkp'], $row['kategori'], $row['menikah'], $row['anak'], $row['pph'], $row['sumber'],
            ], null, "A{$r}");
            $r++;
        }

        $sheet->setCellValue("A{$r}", 'TOTAL');
        $sheet->mergeCells("A{$r}:J{$r}");
        $sheet->setCellValue("K{$r}", $d['total_pph']);
        $sheet->getStyle("A{$r}:{$lastLetter}{$r}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E7E6E6']],
        ]);

        $sheet->getStyle("A{$h}:{$lastLetter}{$r}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("F" . ($h + 1) . ":F{$r}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("K" . ($h + 1) . ":K{$r}")->getNumberFormat()->setFormatCode('#,##0');

        for ($c = 1; $c <= count($headers); $c++) {
            $sheet->getColumnDimensionByColumn($c)->setAutoSize(true);
        }
        $sheet->freezePane('D' . ($h + 1));

        $filename = "PPh21_{$d['tahun']}_" . str_pad((string) $d['bulan'], 2, '0', STR_PAD_LEFT) . '_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($ss) {
            (new Xlsx($ss))->save('php://output');
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    public function exportPphDataPdf(Request $request)
    {
        $d = $this->dataPphBulanan($request);

        $pdf = Pdf::loadView('HR.payroll.export.pph_bulanan_pdf', $d + [
            'generated_at' => now()->format('d M Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download("PPh21_{$d['tahun']}_" . str_pad((string) $d['bulan'], 2, '0', STR_PAD_LEFT) . '_' . date('Ymd_His') . '.pdf');
    }

    public function exportPphDesemberExcel(Request $request)
    {
        $d = $this->dataDesember($request);
        $bln = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        $headers = array_merge(
            ['No', 'NIP', 'Nama', 'Divisi', 'Gaji Pokok'],
            array_map(fn($b) => "Gaji {$b}", $bln),
            array_map(fn($b) => "PPh {$b}", $bln),
            ['THR', 'Total Setahun (Gaji + THR)', 'Biaya Jabatan', 'Neto', 'Status PTKP', 'PTKP', 'PKP',
            'Pajak Setahun', 'PPh Sudah Dipotong', 'PPh Terhutang']
        );
        $lastCol = count($headers);
        $lastLetter = Coordinate::stringFromColumnIndex($lastCol);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('PPh21 Desember');

        $sheet->mergeCells("A1:{$lastLetter}1");
        $sheet->setCellValue('A1', 'LAPORAN PERHITUNGAN PPH 21 TAHUNAN');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A2:{$lastLetter}2");
        $sheet->setCellValue('A2', 'Tahun Pajak: ' . $d['tahun']);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A3:{$lastLetter}3");
        $sheet->setCellValue('A3', 'Filter: ' . $d['filter_label'] . '  |  Dicetak: ' . now()->format('d M Y H:i'));
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A3')->getFont()->setItalic(true);

        $sheet->mergeCells("A4:{$lastLetter}4");
        $sheet->setCellValue('A4', $d['thr_label']);
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A4')->getFont()->setItalic(true);

        $headerRow = 5;
        $sheet->fromArray($headers, null, "A{$headerRow}");
        $sheet->getStyle("A{$headerRow}:{$lastLetter}{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E79']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        $r = $headerRow + 1;
        foreach ($d['rows'] as $row) {
            $line = array_merge(
                [$row['no'], $row['nip'], $row['nama'], $row['divisi'], $row['gaji_pokok']],
                $row['gaji_bulan'],
                $row['pph_bulan'],
                [$row['thr'], $row['bruto'], $row['biaya_jabatan'], $row['neto'], $row['ptkp_kode'] ?? '-',
                $row['ptkp'], $row['pkp'], $row['pajak_setahun'], $row['sudah_dipotong'], $row['terhutang']]
            );
            $sheet->fromArray($line, null, "A{$r}");
            $r++;
        }

        $lastData = $r - 1;
        $t = $d['total'];
        $sheet->setCellValue("A{$r}", 'TOTAL');
        $sheet->mergeCells("A{$r}:D{$r}");

        // kolom: E = gaji pokok, F-Q gaji, R-AC pph, AD..AM sisanya
        $kolThr = 5 + 24 + 1;
        $totalMap = [
            $kolThr => $t['thr'],
            $kolThr + 1 => $t['bruto'],
            $kolThr + 2 => $t['biaya_jabatan'],
            $kolThr + 3 => $t['neto'],
            $kolThr + 6 => $t['pkp'],
            $kolThr + 7 => $t['pajak_setahun'],
            $kolThr + 8 => $t['sudah_dipotong'],
            $kolThr + 9 => $t['terhutang'],
        ];
        foreach ($totalMap as $col => $val) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . $r, $val);
        }
        $sheet->getStyle("A{$r}:{$lastLetter}{$r}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E7E6E6']],
        ]);

        $sheet->getStyle("A{$headerRow}:{$lastLetter}{$r}")
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // format angka: semua kolom nominal kecuali Status PTKP
        $kolPtkpKode = $kolThr + 4;
        $sheet->getStyle('E' . ($headerRow + 1) . ":{$lastLetter}{$r}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle(Coordinate::stringFromColumnIndex($kolPtkpKode) . ($headerRow + 1) . ':' .
            Coordinate::stringFromColumnIndex($kolPtkpKode) . $r)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        for ($c = 1; $c <= $lastCol; $c++) {
            $sheet->getColumnDimensionByColumn($c)->setAutoSize(true);
        }
        $sheet->freezePane('E' . ($headerRow + 1));

        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A3);

        $filename = "PPh21_Desember_{$d['tahun']}_" . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function exportPphDesemberPdf(Request $request)
    {
        $d = $this->dataDesember($request);

        $pdf = Pdf::loadView('HR.payroll.export.pph_pdf', [
            'rows' => $d['rows'],
            'total' => $d['total'],
            'tahun' => $d['tahun'],
            'filter_label' => $d['filter_label'],
            'thr_label' => $d['thr_label'],
            'generated_at' => now()->format('d M Y H:i'),
        ])->setPaper('a3', 'landscape');

        return $pdf->download("PPh21_Desember_{$d['tahun']}_" . date('Ymd_His') . '.pdf');
    }
    public function previewPph(Request $request)
    {
        $request->validate([
            'karyawan_id' => 'required|exists:karyawans,id',
            'ptkp' => 'required|in:TK/0,TK/1,TK/2,TK/3,K/0,K/1,K/2,K/3',
            'bulan' => 'nullable|integer|between:1,12',
            'tahun' => 'nullable|integer|min:2000|max:2100',
        ]);
 
        [$bulan, $tahun] = $this->currentPeriod($request);
        $k = karyawan::findOrFail($request->karyawan_id);
        $ctx = $this->pphContext($bulan, $tahun, [(int) $k->id]);
        $hit = $this->hitungPphKaryawan($k, $ctx, $request->ptkp);
 
        return response()->json(['success' => true, 'hasil' => $hit]);
    }
 
    public function storePph(Request $request)
    {
        $request->validate([
            'karyawan_id' => 'required|exists:karyawans,id',
            'ptkp' => 'required|in:TK/0,TK/1,TK/2,TK/3,K/0,K/1,K/2,K/3',
            'status_menikah' => 'required|in:0,1',
            'pakai_data' => 'nullable|boolean',
            'bulan' => 'nullable|integer|between:1,12',
            'tahun' => 'nullable|integer|min:2000|max:2100',
        ]);
 
        [$bulan, $tahun] = $this->currentPeriod($request);
 
        if ($this->pphTerkunci($bulan, $tahun)) {
            return $this->respondError('Periode ' . str_pad((string) $bulan, 2, '0', STR_PAD_LEFT) . '/' . $tahun . ' sudah dilaporkan ke Kortex sehingga data terkunci. Batalkan lapor Kortex terlebih dahulu untuk mengubah data.');
        }
 
        $anak = collect($request->anak ?? [])
            ->filter(fn($a) => !empty(trim($a['nama'] ?? '')))
            ->values()
            ->map(fn($a) => ['nama' => trim($a['nama'])])
            ->toArray();
 
        $karyawan = karyawan::findOrFail($request->karyawan_id);
        $pphLama = Pph21Karyawan::where('karyawan_id', $karyawan->id)->first();
        $ptkpBerubah = !$pphLama || $pphLama->ptkp !== $request->ptkp;
 
        // Tentukan nilai dengan urutan prioritas yang sama seperti tampilan.
        // Nilai "tersimpan" diabaikan (kita sedang menyimpan ulang); "bulan_lalu" hanya
        // dipakai untuk tombol "simpan" (pakai_data) dan hanya jika PTKP tidak berubah.
        $ctx = $this->pphContext($bulan, $tahun, [(int) $karyawan->id]);
        $ctx['records'] = collect();
        if (!$request->boolean('pakai_data') || $ptkpBerubah) {
            $ctx['previous'] = collect();
        }
        $karyawan->setRelation('pph21', new Pph21Karyawan(['ptkp' => $request->ptkp]));
        $resolved = $this->resolvePph($karyawan, $ctx);
 
        $total = (int) $resolved['nilai'];
        $sumber = $resolved['sumber'];
 
        DB::beginTransaction();
        try {
            $mode = $pphLama ? 'update' : 'create';
 
            $payload = [
                'ptkp' => $request->ptkp,
                'status_menikah' => (int) $request->status_menikah,
                'anak' => $anak,
                'total_pph' => $total,
            ];
 
            if ($pphLama) {
                $pphLama->update($payload);
                $pph = $pphLama;
            } else {
                $pph = Pph21Karyawan::create(array_merge(['karyawan_id' => $karyawan->id], $payload));
            }
 
            Pph21Bulanan::updateOrCreate(
                ['karyawan_id' => $karyawan->id, 'bulan' => $bulan, 'tahun' => $tahun],
                [
                    'total_pph' => $total,
                    'sumber' => $sumber,
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
            'sumber' => $sumber,
            'peringatan' => $resolved['peringatan'] ?? [],
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
            return $this->respondError('Periode ' . str_pad((string) $bulan, 2, '0', STR_PAD_LEFT) . '/' . $tahun . ' sudah dilaporkan ke Kortex sehingga data terkunci. Batalkan lapor Kortex terlebih dahulu.');
        }
 
        $list = $this->pphKaryawanPeriode($bulan, $tahun);
        $ctx = $this->pphContext($bulan, $tahun, $list->pluck('id')->all());
        $dibuat = 0;
        $tanpaPtkp = 0;
        $peringatan = [];
 
        DB::beginTransaction();
        try {
            foreach ($list as $k) {
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
 
                foreach ($r['peringatan'] ?? [] as $p) {
                    $peringatan[] = ($k->nama_lengkap ?? $k->nip) . ': ' . $p;
                }
 
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
            'message' => "{$dibuat} karyawan berhasil diisi otomatis." . ($tanpaPtkp ? " {$tanpaPtkp} karyawan dilewati karena belum punya status PTKP." : '')
                . ($peringatan ? "\n\nPerhatian:\n- " . implode("\n- ", array_slice($peringatan, 0, 8)) : ''),
            'dibuat' => $dibuat,
            'peringatan' => $peringatan,
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
 
        if ($this->pphTerkunci($bulan, $tahun)) {
            return $this->respondError('Periode ini sudah dilaporkan ke Kortex.');
        }
 
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

    public function setBpjsStatus(Request $request, $karyawanId)
    {
        $request->validate([
            'status' => 'required|in:active,suspended,permanent',
        ]);

        $karyawan = karyawan::findOrFail($karyawanId);
        if (!$this->isBpjsEligible($karyawan)) {
            return $this->respondError('Karyawan ini bukan penerima BPJS.');
        }
        $status = $request->status;
        $now = now();

        DB::beginTransaction();
        try {
            $update = [
                'bpjs_status' => $status,
                'bpjs_status_changed_at' => $now,
                'bpjs_deleted_at' => null,
                'bpjs_restore_until' => null,
                'bpjs_deleted_by' => null,
            ];

            $karyawan->update($update);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->respondError('Gagal mengubah status BPJS: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => $status === 'permanent'
                ? 'BPJS dinonaktifkan permanen. Data perhitungan tetap disimpan dan disembunyikan.'
                : ($status === 'suspended' ? 'BPJS dinonaktifkan sementara.' : 'BPJS berhasil diaktifkan kembali.'),
            'data' => [
                'status' => $status,
                'restore_until' => $karyawan->bpjs_restore_until?->toISOString(),
            ],
        ]);
    }

    public function getBpjsTrash()
    {
        $rows = karyawan::query()
            ->whereIn('bpjs_status', ['suspended', 'permanent'])
            ->orderByDesc('bpjs_deleted_at')
            ->get()
            ->map(fn($k) => [
                'id' => $k->id,
                'nip' => $k->nip,
                'nama' => $k->nama_lengkap,
                'divisi' => $this->divisiName($k),
                'status' => $k->bpjs_status,
                'deleted_at' => $k->bpjs_deleted_at?->format('d/m/Y H:i'),
                'restore_until' => $k->bpjs_restore_until?->format('d/m/Y H:i'),
            ]);

        return response()->json(['success' => true, 'data' => $rows]);
    }

    public function restoreBpjs($karyawanId)
    {
        $karyawan = karyawan::findOrFail($karyawanId);
        if (!in_array($karyawan->bpjs_status, ['suspended', 'permanent'], true)) {
            return $this->respondError('BPJS karyawan sudah aktif.');
        }

        DB::beginTransaction();
        try {
            $trashed = PerhitunganTunjanganHR::onlyTrashed()
                ->where('karyawan_id', $karyawan->id)
                ->get();
            foreach ($trashed as $payroll) {
                $payroll->restore();
            }

            $karyawan->update([
                'bpjs_status' => 'active',
                'bpjs_status_changed_at' => now(),
                'bpjs_deleted_at' => null,
                'bpjs_restore_until' => null,
                'bpjs_deleted_by' => null,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->respondError('Gagal memulihkan BPJS: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'BPJS berhasil dipulihkan.']);
    }

    public function isiOtomatisBpjs(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer|min:2000|max:2100',
        ]);

        [$bulan, $tahun] = $this->currentPeriod($request);
        $dibuat = 0;
        $dilewati = 0;

        $prevDate = Carbon::createFromDate($tahun, $bulan, 1)->subMonth();
        DB::beginTransaction();
        try {
            foreach ($this->bpjsKaryawanQuery()->where('bpjs_status', '!=', 'permanent')->get() as $karyawan) {
                if ($this->latestPayrollOf($karyawan->id, $bulan, $tahun)) {
                    continue;
                }

                if (in_array($karyawan->bpjs_status, ['suspended', 'permanent'], true)) {
                    $dilewati++;
                    continue;
                }

                $previous = $this->latestPayrollOf($karyawan->id, $prevDate->month, $prevDate->year);
                if (!$previous) {
                    $dilewati++;
                    continue;
                }

                $attributes = [
                    'gaji_pokok' => $previous->gaji_pokok,
                    'salary_bpjstk' => $previous->salary_bpjstk,
                    'umk_bandung' => $previous->umk_bandung,
                    'tunjangan_detail' => $previous->tunjangan_detail,
                    'total_tunjangan' => $previous->total_tunjangan,
                    'jht_perusahaan' => $previous->jht_perusahaan,
                    'jkm_perusahaan' => $previous->jkm_perusahaan,
                    'jkk_perusahaan' => $previous->jkk_perusahaan,
                    'jp_perusahaan' => $previous->jp_perusahaan,
                    'total_bpjstk_perusahaan' => $previous->total_bpjstk_perusahaan,
                    'jht_karyawan' => $previous->jht_karyawan,
                    'jp_karyawan' => $previous->jp_karyawan,
                    'total_bpjstk_karyawan' => $previous->total_bpjstk_karyawan,
                    'bpjs_kes_perusahaan' => $previous->bpjs_kes_perusahaan,
                    'bpjs_kes_karyawan' => $previous->bpjs_kes_karyawan,
                    'total_bpjs_perusahaan' => $previous->total_bpjs_perusahaan,
                    'total_bpjs_karyawan' => $previous->total_bpjs_karyawan,
                    'potongan_pph21' => $previous->potongan_pph21,
                    'thp_kotor' => $previous->thp_kotor,
                    'thp_bersih' => $previous->thp_bersih,
                    'total_biaya_perusahaan' => $previous->total_biaya_perusahaan,
                    'biaya_tanggungan' => $previous->biaya_tanggungan,
                    'status' => 'calculated',
                    'updated_by' => Auth::id(),
                ];

                $target = PerhitunganTunjanganHR::withTrashed()
                    ->where('karyawan_id', $karyawan->id)
                    ->where('bulan', $bulan)
                    ->where('tahun', $tahun)
                    ->lockForUpdate()
                    ->first();

                if ($target) {
                    if ($target->trashed()) {
                        $target->restore();
                    }
                    $target->update($attributes);
                } else {
                    PerhitunganTunjanganHR::create(array_merge($attributes, [
                        'karyawan_id' => $karyawan->id,
                        'bulan' => $bulan,
                        'tahun' => $tahun,
                        'created_by' => Auth::id(),
                    ]));
                }
                $dibuat++;
            }
            DB::commit();
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            if ((int) $e->errorInfo[1] === 1062) {
                return $this->respondError('Data periode BPJS sudah tersimpan. Silakan muat ulang daftar lalu coba lagi.');
            }
            return $this->respondError('Gagal mengisi otomatis BPJS: ' . $e->getMessage());
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->respondError('Gagal mengisi otomatis BPJS: ' . $e->getMessage());
        }

        if (!$dibuat) {
            return $this->respondError('Tidak ada data yang perlu diisi. Semua periode sudah terisi atau bulan sebelumnya belum tersedia.');
        }

        return response()->json([
            'success' => true,
            'message' => "{$dibuat} karyawan berhasil diisi otomatis BPJS.",
            'dibuat' => $dibuat,
            'dilewati' => $dilewati,
        ]);
    }

    public function deleteAllPph(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer|min:2000|max:2100',
        ]);
 
        [$bulan, $tahun] = $this->currentPeriod($request);
 
        if ($this->pphTerkunci($bulan, $tahun)) {
            return $this->respondError('Periode ' . str_pad((string) $bulan, 2, '0', STR_PAD_LEFT) . '/' . $tahun . ' sudah dilaporkan ke Kortex sehingga data terkunci. Batalkan lapor Kortex terlebih dahulu.');
        }
 
        try {
            DB::beginTransaction();
 
            $ids = Pph21Bulanan::where('bulan', $bulan)->where('tahun', $tahun)->pluck('karyawan_id')->all();
            $deleted = Pph21Bulanan::where('bulan', $bulan)->where('tahun', $tahun)->delete();
            foreach ($ids as $id) {
                $this->syncPphProfileTotal((int) $id);
            }
 
            DB::commit();
 
            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus {$deleted} data PPH 21 periode " . str_pad((string) $bulan, 2, '0', STR_PAD_LEFT) . "/{$tahun}. Status PTKP karyawan tidak dihapus.",
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

    private const PTKP_TAHUNAN = [
        'TK/0' => 54000000, 'TK/1' => 58500000, 'TK/2' => 63000000, 'TK/3' => 67500000,
        'K/0'  => 58500000, 'K/1'  => 63000000, 'K/2'  => 67500000, 'K/3'  => 72000000,
    ];

    private const TER_KATEGORI = [
        'TK/0' => 'A', 'TK/1' => 'A', 'K/0' => 'A',
        'TK/2' => 'B', 'TK/3' => 'B', 'K/1' => 'B', 'K/2' => 'B',
        'K/3'  => 'C',
    ];

    private const TER_TABEL = [
        'A' => [
            [5400000, 0], [5650000, 0.25], [5950000, 0.5], [6300000, 0.75], [6750000, 1],
            [7500000, 1.25], [8550000, 1.5], [9650000, 1.75], [10050000, 2], [10350000, 2.25],
            [10700000, 2.5], [11050000, 3], [11600000, 3.5], [12500000, 4], [13750000, 5],
            [15100000, 6], [16950000, 7], [19750000, 8], [24150000, 9], [26450000, 10],
            [28000000, 11], [30050000, 12], [32400000, 13], [35400000, 14], [39100000, 15],
            [43850000, 16], [47800000, 17], [51400000, 18], [56300000, 19], [62200000, 20],
            [68600000, 21], [77500000, 22], [89000000, 23], [103000000, 24], [125000000, 25],
            [157000000, 26], [206000000, 27], [337000000, 28], [454000000, 29], [550000000, 30],
            [695000000, 31], [910000000, 32], [1400000000, 33], [PHP_INT_MAX, 34],
        ],
        'B' => [
            [6200000, 0], [6500000, 0.25], [6850000, 0.5], [7300000, 0.75], [9200000, 1],
            [10750000, 1.5], [11250000, 2], [11600000, 2.5], [12600000, 3], [13600000, 4],
            [14950000, 5], [16400000, 6], [18450000, 7], [21850000, 8], [26000000, 9],
            [27700000, 10], [29350000, 11], [31450000, 12], [33950000, 13], [37100000, 14],
            [41100000, 15], [45800000, 16], [49500000, 17], [53800000, 18], [58500000, 19],
            [64000000, 20], [71000000, 21], [80000000, 22], [93000000, 23], [109000000, 24],
            [129000000, 25], [163000000, 26], [211000000, 27], [374000000, 28], [459000000, 29],
            [555000000, 30], [704000000, 31], [957000000, 32], [1405000000, 33],
            [PHP_INT_MAX, 34],
        ],
        'C' => [
            [6600000, 0], [6950000, 0.25], [7350000, 0.5], [7800000, 0.75], [8850000, 1],
            [9800000, 1.25], [10950000, 1.5], [11200000, 1.75], [12050000, 2], [12950000, 3],
            [14150000, 4], [15550000, 5], [17050000, 6], [19500000, 7], [22700000, 8],
            [26600000, 9], [28100000, 10], [30100000, 11], [32600000, 12], [35400000, 13],
            [38900000, 14], [43000000, 15], [47400000, 16], [51200000, 17], [55800000, 18],
            [60400000, 19], [66700000, 20], [74500000, 21], [83200000, 22], [95600000, 23],
            [110000000, 24], [134000000, 25], [169000000, 26], [221000000, 27], [390000000, 28],
            [463000000, 29], [561000000, 30], [709000000, 31], [965000000, 32], [1419000000, 33],
            [PHP_INT_MAX, 34],
        ],
    ];

    private function terKategori(?string $ptkp): ?string
    {
        return self::TER_KATEGORI[$ptkp] ?? null;
    }
 
    private function terTarif(int $bruto, string $kategori): float
    {
        foreach (self::TER_TABEL[$kategori] as [$batas, $tarif]) {
            if ($bruto <= $batas) {
                return (float) $tarif;
            }
        }
        return 34.0;
    }
 
    /** Tarif Pasal 17 ayat (1) huruf a UU PPh. */
    private function pasal17(int $pkp): int
    {
        $layers = [
            [60000000, 0.05], [250000000, 0.15], [500000000, 0.25],
            [5000000000, 0.30], [INF, 0.35],
        ];
 
        $sisa = $pkp;
        $prev = 0;
        $pph = 0.0;
        foreach ($layers as [$limit, $rate]) {
            if ($sisa <= 0) break;
            $kena = min($sisa, $limit - $prev);
            $pph += $kena * $rate;
            $sisa -= $kena;
            $prev = $limit;
        }
 
        return (int) round($pph);
    }

    private function hitungPph21(int $brutoBulanan, ?string $ptkpCode): array
    {
        $kategori = $this->terKategori($ptkpCode);
        if (!$kategori || $brutoBulanan <= 0) {
            return ['kategori' => $kategori, 'tarif' => 0.0, 'bulanan' => 0, 'tahunan' => 0, 'metode' => 'ter'];
        }
 
        $tarif = $this->terTarif($brutoBulanan, $kategori);
        $bulanan = (int) round($brutoBulanan * $tarif / 100);
 
        return [
            'kategori' => $kategori,
            'tarif' => $tarif,
            'bulanan' => $bulanan,
            'tahunan' => $bulanan * 12,
            'metode' => 'ter',
        ];
    }

    private function hitungPph21MasaTerakhir(int $brutoSetahun, int $jumlahBulan, int $iuranPengurang, ?string $ptkpCode, int $sudahDipotong): array
    {
        $biayaJabatan = (int) min(round($brutoSetahun * 0.05), 500000 * max(1, $jumlahBulan));
        $neto = $brutoSetahun - $biayaJabatan - $iuranPengurang;
        $ptkp = self::PTKP_TAHUNAN[$ptkpCode] ?? 0;
        $pkp = max(0, $neto - $ptkp);
        $pkp = intdiv($pkp, 1000) * 1000; // PKP dibulatkan ke bawah ribuan penuh
        $terutang = $this->pasal17($pkp);
 
        return [
            'bruto_setahun' => $brutoSetahun,
            'jumlah_bulan' => $jumlahBulan,
            'biaya_jabatan' => $biayaJabatan,
            'iuran_pengurang' => $iuranPengurang,
            'neto_setahun' => $neto,
            'ptkp_setahun' => $ptkp,
            'pkp' => $pkp,
            'pph_setahun' => $terutang,
            'sudah_dipotong' => $sudahDipotong,
            'selisih' => $terutang - $sudahDipotong,
        ];
    }
 
    private function masaTerakhir($k, int $bulan, int $tahun): bool
    {
        if ($bulan === 12) {
            return true;
        }
        if (!empty($k->resigned_at)) {
            $r = Carbon::parse($k->resigned_at);
            return $r->year === $tahun && $r->month === $bulan;
        }
        return false;
    }
 
    /** Bulan pertama bekerja dalam tahun pajak (karyawan baru di tengah tahun). */
    private function bulanMulaiKerja($k, int $tahun): int
    {
        $raw = $k->awal_probation ?? $k->awal_kontrak;
        if ($raw) {
            $d = Carbon::parse($raw);
            if ($d->year === $tahun) {
                return $d->month;
            }
        }
        return 1;
    }

    private function brutoBulan($k, int $bulan, int $tahun, array $ctx): int
    {
        $logs = $ctx['logs']->get($k->id, collect());
        if ($logs->isEmpty()) {
            return (int) round((float) ($k->gaji ?? 0));
        }
        $komponen = $this->getSalaryComponentsFromLogs($logs, $bulan, $tahun);
        return (int) round($komponen['gaji']);
    }

    private function hitungPphKaryawan($k, array $ctx, ?string $ptkp): array
    {
        $bulan = $ctx['bulan'];
        $tahun = $ctx['tahun'];
        $bruto = $this->brutoBulan($k, $bulan, $tahun, $ctx);
        $ter = $this->hitungPph21($bruto, $ptkp);
 
        $out = [
            'bruto' => $bruto,
            'kategori' => $ter['kategori'],
            'tarif' => $ter['tarif'],
            'ter' => $ter['bulanan'],
            'nilai' => $ter['bulanan'],
            'masa_terakhir' => false,
            'rincian' => null,
            'peringatan' => [],
        ];
 
        if (!$ptkp || !$this->masaTerakhir($k, $bulan, $tahun)) {
            return $out;
        }
 
        $mulai = $this->bulanMulaiKerja($k, $tahun);
        $jumlah = max(1, $bulan - $mulai + 1);
 
        $brutoSetahun = 0;
        for ($m = $mulai; $m <= $bulan; $m++) {
            $brutoSetahun += $this->brutoBulan($k, $m, $tahun, $ctx);
        }
 
        // JHT + JP porsi karyawan = pengurang penghasilan bruto (Tabel 8.1 buku PPh 21).
        $iuran = (int) $ctx['iuran']->get($k->id, collect())
            ->where('bulan', '>=', $mulai)
            ->sum(fn($p) => (int) ($p->jht_karyawan ?? 0) + (int) ($p->jp_karyawan ?? 0));
 
        $sebelumnya = $ctx['tahun_ini']->get($k->id, collect())->where('bulan', '>=', $mulai);
        $sudah = (int) $sebelumnya->sum('total_pph');
 
        $kosong = [];
        for ($m = $mulai; $m < $bulan; $m++) {
            if (!$sebelumnya->contains('bulan', $m)) {
                $kosong[] = str_pad((string) $m, 2, '0', STR_PAD_LEFT);
            }
        }
 
        $r = $this->hitungPph21MasaTerakhir($brutoSetahun, $jumlah, $iuran, $ptkp, $sudah);
 
        $out['masa_terakhir'] = true;
        $out['rincian'] = $r;
        $out['nilai'] = max(0, $r['selisih']);
 
        if ($r['selisih'] < 0) {
            $out['peringatan'][] = 'Kelebihan potong Rp ' . number_format(abs($r['selisih']), 0, ',', '.')
                . ' pada masa pajak terakhir. Nilai yang disimpan Rp 0; kelebihan harus dikembalikan ke karyawan.';
        }
        if ($kosong) {
            $out['peringatan'][] = 'PPh 21 bulan ' . implode(', ', $kosong) . '/' . $tahun
                . ' belum tersimpan, sehingga "sudah dipotong" mungkin terlalu kecil dan selisih terlalu besar.';
        }
 
        return $out;
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

    private function latestPayrollOf($karyawanId, $bulan = null, $tahun = null)
    {
        $query = PerhitunganTunjanganHR::where('karyawan_id', $karyawanId);
        if ($bulan && $tahun) {
            $query->where('bulan', $bulan)->where('tahun', $tahun);
        }

        return $query
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

        if ($karyawan && !$this->isBpjsEligible($karyawan)) {
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

    public function exportPphAnalisisExcel(Request $request)
    {
        [$bulan, $tahun] = $this->currentPeriod($request);
        $a = $this->getPphAnalysis($request)->getData(true);

        $ss = new Spreadsheet();
        $sheet = $ss->getActiveSheet();
        $sheet->setTitle('Analisis PPh21');
        $r = 1;

        $judul = function (string $teks) use (&$r, $sheet) {
            $sheet->setCellValue("A{$r}", $teks);
            $sheet->getStyle("A{$r}")->getFont()->setBold(true)->setSize(12);
            $r++;
        };

        $tabel = function (array $head, array $baris) use (&$r, $sheet) {
            $akhir = Coordinate::stringFromColumnIndex(count($head));
            $sheet->fromArray($head, null, "A{$r}");
            $sheet->getStyle("A{$r}:{$akhir}{$r}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E79']],
            ]);
            $r++;
            foreach ($baris as $b) {
                $sheet->fromArray($b, null, "A{$r}");
                $r++;
            }
            $r++;
        };

        $judul("ANALISIS PPH 21 - {$bulan}/{$tahun}");
        $r++;

        $h = $a['kesehatan'];
        $judul('Ringkasan');
        $tabel(['Keterangan', 'Nilai'], [
            ['Karyawan eligible', $h['total_eligible']],
            ['Sudah punya PTKP', $h['punya_ptkp']],
            ['Belum punya PTKP', $h['belum_ptkp']],
            ['Nilai bulan ini tersimpan', $h['sudah_diisi']],
            ['Belum tersimpan', $h['belum_diisi']],
            ['Total PPh 21', $h['total_pph']],
            ['Total PPh 21 bulan lalu (' . $h['bulan_lalu_label'] . ')', $h['total_pph_bulan_lalu']],
            ['Selisih', $h['selisih']],
        ]);

        $judul('Status Alur Kerja');
        $tabel(['Langkah', 'Status', 'Detail'], array_map(
            fn($s) => [$s['judul'], $s['selesai'] ? 'Selesai' : 'Belum', $s['detail']],
            $a['workflow']['steps']
        ));

        $judul('Kategori TER');
        $baris = [];
        foreach ($a['distribusi']['kategori'] as $k => $v) {
            $baris[] = [$k, $v['jumlah'], $v['total']];
        }
        $tabel(['Kategori', 'Jumlah', 'Total PPh 21'], $baris);

        $judul('Distribusi Nominal PPh 21');
        $tabel(['Rentang', 'Jumlah Karyawan'], array_map(
            fn($x) => [$x['label'], $x['jumlah']],
            $a['distribusi']['rentang']
        ));

        $judul('Total PPh 21 per Divisi');
        $tabel(['Divisi', 'Jumlah', 'Total', 'Rata-rata'], array_map(
            fn($x) => [$x['divisi'], $x['jumlah'], $x['total'], $x['rata_rata']],
            $a['distribusi']['divisi']->all ?? $a['distribusi']['divisi']
        ));

        $judul('10 PPh 21 Tertinggi');
        $tabel(['NIP', 'Nama', 'Divisi', 'PTKP', 'Nilai'], array_map(
            fn($x) => [$x['nip'], $x['nama'], $x['divisi'], $x['ptkp'] ?? '-', $x['nilai']],
            $a['distribusi']['top']
        ));

        $judul('Deteksi Anomali (' . $a['anomali_total'] . ')');
        $tabel(['Level', 'NIP', 'Nama', 'Pesan'], array_map(
            fn($x) => [ucfirst($x['level']), $x['nip'], $x['nama'], $x['pesan']],
            $a['anomali']
        ));

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = "Analisis_PPh21_{$tahun}_" . str_pad((string) $bulan, 2, '0', STR_PAD_LEFT) . '_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($ss) {
            (new Xlsx($ss))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function exportPphAnalisisPdf(Request $request)
    {
        [$bulan, $tahun] = $this->currentPeriod($request);
        $a = $this->getPphAnalysis($request)->getData(true);

        $pdf = Pdf::loadView('HR.payroll.export.pph_analisis_pdf', [
            'a' => $a,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'generated_at' => now()->format('d M Y H:i'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download("Analisis_PPh21_{$tahun}_" . str_pad((string) $bulan, 2, '0', STR_PAD_LEFT) . '_' . date('Ymd_His') . '.pdf');
    }
}