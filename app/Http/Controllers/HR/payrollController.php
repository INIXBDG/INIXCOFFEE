<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\karyawan;
use App\Models\LogGaji;
use App\Models\TunjanganKaryawan;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

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

            $excludedJabatan = ['Direktur', 'Direktur Utama'];

            $baseQuery = Karyawan::query()
                ->whereNot('jabatan', 'Outsource')
                ->where('kode_karyawan', 'NOT LIKE', 'OL%')
                ->whereNot('jabatan', 'Pilih Jabatan')
                ->whereNotNull('nip')
                ->whereNot('divisi', 'Direksi')
                ->where('status_aktif', '1')
                ->whereNotIn('jabatan', $excludedJabatan)
                ->whereRaw('LOWER(jabatan) != ?', ['outsource'])
                ->where(function ($q) {
                    $q->whereNull('kode_karyawan')->orWhere('kode_karyawan', 'not like', '%OL%');
                });

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
                $gajiPokok = (float) ($emp->gaji_pokok ?? ($emp->gaji ?? 0));

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
                    'tunjangan_bersih' => $tunjanganBersih, // === PERUBAHAN: Menambahkan field baru ===
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
                'total_tunjangan' => $payrollList->filter(function($emp) {
                    return $emp['tunjangan_bersih'] > 0;
                })->sum('tunjangan_bersih'),
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

    public function exportPayrollCsv(Request $request)
    {
        $bulan = (int) $request->input('month', now()->month);
        $tahun = (int) $request->input('year', now()->year);
        $search = $request->input('search', '');

        $res = $this->getPayrollDashboard(
            new Request([
                'month' => $bulan,
                'year' => $tahun,
                'search' => $search,
                'page' => 1,
            ]),
        )->getData(true);

        $filename = "payroll_{$bulan}_{$tahun}.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($res) {
            $file = fopen('php://output', 'w');
            // === PERUBAHAN: Menambahkan kolom Tunjangan Bersih ===
            fputcsv($file, ['No', 'Nama', 'Kode', 'Divisi', 'Jabatan', 'Gaji Pokok', 'Total Tunjangan', 'Tunjangan Bersih', 'Total Potongan', 'Gaji Bersih', 'Status']);

            $i = 1;
            foreach ($res['data'] as $row) {
                fputcsv($file, [
                    $i++,
                    $row['nama'],
                    $row['kode'] ?? '-',
                    $row['divisi'],
                    $row['jabatan'],
                    $row['gaji_pokok'],
                    $row['total_tunjangan'],
                    $row['tunjangan_bersih'], // === PERUBAHAN: Memasukkan nilai tunjangan_bersih ===
                    $row['total_potongan'],
                    $row['gaji_bersih'],
                    $row['status'],
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPayrollPdf(Request $request)
    {
        $bulan = (int) $request->input('month', now()->month);
        $tahun = (int) $request->input('year', now()->year);

        $res = $this->getPayrollDashboard(
            new Request([
                'month' => $bulan,
                'year' => $tahun,
                'page' => 1,
            ]),
        )->getData(true);

        $pdf = Pdf::loadView('office.HR.exports.payroll_pdf', [
            'data' => $res['data'],
            'summary' => $res['summary'],
            'charts' => $res['charts'],
            'period' => $res['period']['display'],
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
                'total_gaji' => (float) (LogGaji::where('bulan', $m)->where('tahun', $tahun)->sum('gaji') ?? 0),
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
}
