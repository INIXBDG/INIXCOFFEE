<?php

namespace App\Http\Controllers;

use App\Models\AbsensiKaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\karyawan;
use App\Models\LogGaji;
use App\Models\TunjanganKaryawan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\pengajuancuti;
use App\Models\HariLibur;
use Illuminate\Support\Facades\Http;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class HRController extends Controller
{
    //Karyawan Infomasi Function
    public function newActive()
    {
        return view('office/HR/employee/newActive');
    }

    public function getEmployeeData(Request $request)
    {
        try {
            $validated = $request->validate([
                'periode' => 'nullable|in:3,6,12,year,all',
                'year' => 'nullable|integer|min:2000|max:' . date('Y'),
                'search' => 'nullable|string|max:100',
            ]);

            $periode = $validated['periode'] ?? 'all';
            $year = $validated['year'] ?? null;
            $search = $validated['search'] ?? null;
            $dateRange = $periode !== 'all' ? $this->calculateDateRange($periode, $year) : null;

            $baseQuery = karyawan::query()->whereNot('jabatan', 'Outsource')->where('kode_karyawan', 'NOT LIKE', 'OL%')->whereNot('jabatan', 'Pilih Jabatan')->where('nip', '!=', null)->whereNot('divisi', 'Direksi');
            $totalEmployees = (clone $baseQuery)->count();

            $activeQuery = clone $baseQuery;
            if ($dateRange) {
                $activeQuery->where(function ($q) use ($dateRange) {
                    $q->whereBetween('awal_probation', [$dateRange['start'], $dateRange['end']])
                        ->orWhere(function ($sub) use ($dateRange) {
                            $sub->whereNull('awal_probation')->whereBetween('awal_kontrak', [$dateRange['start'], $dateRange['end']]);
                        })
                        ->orWhere(function ($sub) use ($dateRange) {
                            $sub->whereNull('awal_probation')
                                ->whereNull('awal_kontrak')
                                ->whereBetween('awal_tetap', [$dateRange['start'], $dateRange['end']]);
                        });
                });
            }
            $active = $activeQuery->where('status_aktif', '1')->count();

            $newQuery = clone $baseQuery;
            if ($dateRange) {
                $newQuery->whereBetween('awal_probation', [$dateRange['start'], $dateRange['end']]);
            } else {
                $newQuery->whereNotNull('awal_probation');
            }
            $new = $newQuery->where('status_aktif', '1')->count();

            $resignQuery = clone $baseQuery;
            if ($dateRange) {
                $resignQuery->whereBetween('resigned_at', [$dateRange['start'], $dateRange['end']]);
            }
            $resign = $resignQuery->whereNotNull('resigned_at')->count();

            $total = $active + $resign;
            $retentionRate = $total > 0 ? round(($active / $total) * 100, 1) : 0;
            $retentionInsights = $this->generateRetentionInsights($active, $resign, $total);

            return response()->json(
                [
                    'stats' => [
                        'total_employees' => $totalEmployees,
                        'active' => $active,
                        'new' => $new,
                        'resign' => $resign,
                        'retention_rate' => $retentionRate,
                    ],
                    'insights' => $retentionInsights,
                    'periode' => $periode,
                    'date_range' => $dateRange,
                ],
                200,
            );
        } catch (\Exception $e) {
            Log::error('HR Employee Data Error: ' . $e->getMessage());
            return response()->json(
                [
                    'error' => 'Gagal memuat data karyawan',
                    'message' => config('app.debug') ? $e->getMessage() : 'Silakan coba beberapa saat lagi',
                ],
                500,
            );
        }
    }

    public function getHeadcountTrend(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'group_by' => 'nullable|in:month,quarter,year',
        ]);

        $groupBy = $validated['group_by'] ?? 'month';

        if ($groupBy === 'year') {
            $minDate = karyawan::min('awal_probation');
            $startDate = $minDate ? Carbon::parse($minDate)->startOfYear() : Carbon::now()->subYears(10)->startOfYear();
            $endDate = Carbon::now()->endOfYear();
        } elseif ($groupBy === 'quarter') {
            $minDate = karyawan::min('awal_probation');
            $startDate = $minDate ? Carbon::parse($minDate)->startOfQuarter() : Carbon::now()->subYears(10)->startOfQuarter();
            $endDate = Carbon::now()->endOfQuarter();
        } else {
            $startDate = $validated['start_date'] ? Carbon::parse($validated['start_date'])->startOfMonth() : Carbon::now()->subMonths(11)->startOfMonth();
            $endDate = $validated['end_date'] ? Carbon::parse($validated['end_date'])->endOfMonth() : Carbon::now()->endOfMonth();
        }

        $baseQuery = karyawan::query()->whereNot('jabatan', 'Outsource')->where('kode_karyawan', 'NOT LIKE', 'OL%')->whereNot('jabatan', 'Pilih Jabatan')->where('nip', '!=', null)->whereNot('divisi', 'Direksi');

        $labels = [];
        $activeData = [];
        $newData = [];
        $resignData = [];

        $periods = $this->generatePeriods($startDate, $endDate, $groupBy);

        foreach ($periods as $period) {
            $labels[] = $period['label'];

            $activeCount = (clone $baseQuery)
                ->where('status_aktif', '1')
                ->where(function ($q) use ($period, $groupBy) {
                    $this->applyDateFilter($q, $period, $groupBy, ['awal_probation', 'awal_kontrak', 'awal_tetap']);
                })
                ->count();

            $newCount = (clone $baseQuery)
                ->where('status_aktif', '1')
                ->where(function ($q) use ($period, $groupBy) {
                    $this->applyDateFilter($q, $period, $groupBy, ['awal_probation']);
                })
                ->count();

            $resignCount = (clone $baseQuery)
                ->where('status_aktif', '0')
                ->where(function ($q) use ($period, $groupBy) {
                    switch ($groupBy) {
                        case 'year':
                            $sql = 'YEAR(resigned_at) = ?';
                            break;
                        case 'quarter':
                            $sql = 'CONCAT(YEAR(resigned_at), "-Q", QUARTER(resigned_at)) = ?';
                            break;
                        default:
                            $sql = 'DATE_FORMAT(resigned_at, "%Y-%m") = ?';
                            break;
                    }
                    $q->whereRaw($sql, [$period['value']]);
                })
                ->count();

            $activeData[] = $activeCount;
            $newData[] = $newCount;
            $resignData[] = $resignCount;
        }

        return response()->json(
            [
                'labels' => $labels,
                'datasets' => [['label' => 'Active', 'data' => $activeData, 'borderColor' => '#198754', 'backgroundColor' => 'rgba(25,135,84,0.1)', 'fill' => true], ['label' => 'New Hire', 'data' => $newData, 'borderColor' => '#0d6efd', 'backgroundColor' => 'rgba(13,110,253,0.1)', 'fill' => true], ['label' => 'Resign', 'data' => $resignData, 'borderColor' => '#dc3545', 'backgroundColor' => 'rgba(220,53,69,0.1)', 'fill' => true]],
                'summary' => [
                    'total_active' => array_sum($activeData),
                    'total_new' => array_sum($newData),
                    'total_resign' => array_sum($resignData),
                    'avg_monthly_new' => round(array_sum($newData) / count($newData), 1),
                    'avg_monthly_resign' => round(array_sum($resignData) / count($resignData), 1),
                ],
            ],
            200,
        );
    }

    public function getHeadcountBreakdown(Request $request)
    {
        try {
            $validated = $request->validate([
                'filter_by' => 'nullable|in:divisi,jabatan,location,gender',
                'status' => 'nullable|in:active,resign,all',
                'min_tenure' => 'nullable|integer|min:0',
            ]);

            $filterBy = $validated['filter_by'] ?? 'divisi';
            $status = $validated['status'] ?? 'all';
            $minTenure = $validated['min_tenure'] ?? 0;

            $baseQuery = karyawan::query()->whereNot('jabatan', 'Outsource')->where('kode_karyawan', 'NOT LIKE', 'OL%')->whereNot('jabatan', 'Pilih Jabatan')->where('nip', '!=', null)->whereNot('divisi', 'Direksi');

            if ($status === 'active') {
                $baseQuery->where('status_aktif', '1');
            } elseif ($status === 'resign') {
                $baseQuery->where('status_aktif', '0');
            }

            if ($minTenure > 0) {
                $baseQuery->whereRaw('TIMESTAMPDIFF(MONTH, COALESCE(awal_probation, awal_kontrak, awal_tetap), CURDATE()) >= ?', [$minTenure]);
            }

            $fieldMap = [
                'divisi' => 'divisi',
                'jabatan' => 'jabatan',
                'location' => 'location',
                'gender' => 'gender',
            ];
            $field = $fieldMap[$filterBy] ?? 'divisi';

            $breakdown = $baseQuery
                ->selectRaw("
                    $field as label,
                    COUNT(*) as total,
                    SUM(CASE WHEN status_aktif = '1' THEN 1 ELSE 0 END) as active_count,
                    SUM(CASE WHEN status_aktif = '0' THEN 1 ELSE 0 END) as resign_count
                ")
                ->groupBy($field)
                ->orderByDesc('total')
                ->limit(15)
                ->get()
                ->map(function ($item) {
                    $percentage = $item->total > 0
                        ? round(($item->active_count / $item->total) * 100, 1)
                        : 0;

                    return [
                        'label' => $item->label ?? 'Tidak Diketahui',
                        'total' => $item->total,
                        'active' => $item->active_count,
                        'resign' => $item->resign_count,
                        'retention' => $percentage,
                    ];
                });

            $chartData = [
                'labels' => $breakdown->pluck('label'),
                'total' => $breakdown->pluck('total'),
                'active' => $breakdown->pluck('active'),
                'retention' => $breakdown->pluck('retention'),
            ];

            return response()->json(
                [
                    'breakdown' => $breakdown,
                    'chart' => $chartData,
                    'summary' => [
                        'total_categories' => $breakdown->count(),
                        'top_category' => $breakdown->first()?->label ?? '-',
                        'avg_retention' => $breakdown->avg('retention') ? round($breakdown->avg('retention'), 1) : 0,
                    ],
                ],
                200,
            );
        } catch (\Exception $e) {
            Log::error('Headcount Breakdown Error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal memuat breakdown data'], 500);
        }
    }

    public function getEmployeesByCategory(Request $request)
    {
        try {
            $validated = $request->validate([
                'category' => 'required|in:active,new,resign,all',
                'periode' => 'nullable|in:3,6,12,year,all',
                'year' => 'nullable|integer|min:2000|max:' . date('Y'),
                'search' => 'nullable|string|max:100',
                'page' => 'nullable|integer|min:1',
            ]);

            $category = $validated['category'];
            $periode = $validated['periode'] ?? 'all';
            $year = $validated['year'] ?? null;
            $search = $validated['search'] ?? null;
            $page = $validated['page'] ?? 1;
            $perPage = 10;
            $dateRange = $periode !== 'all' ? $this->calculateDateRange($periode, $year) : null;

            $query = karyawan::query()->whereNot('jabatan', 'Outsource')->where('kode_karyawan', 'NOT LIKE', 'OL%')->whereNot('jabatan', 'Pilih Jabatan')->where('nip', '!=', null)->whereNot('divisi', 'Direksi');

            if ($category === 'active') {
                $query->where('status_aktif', '1');
            } elseif ($category === 'new') {
                $query->where('status_aktif', '1');
                if ($dateRange) {
                    $query->whereBetween('awal_probation', [$dateRange['start'], $dateRange['end']]);
                } else {
                    $query->whereNotNull('awal_probation');
                }
            } elseif ($category === 'resign') {
                $query->where('status_aktif', '0');
                if ($dateRange) {
                    $query->whereBetween('resigned_at', [$dateRange['start'], $dateRange['end']]);
                } else {
                    $query->whereNotNull('resigned_at');
                }
            }

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_lengkap', 'LIKE', "%{$search}%")
                        ->orWhere('nip', 'LIKE', "%{$search}%")
                        ->orWhere('jabatan', 'LIKE', "%{$search}%")
                        ->orWhere('divisi', 'LIKE', "%{$search}%");
                });
            }

            $total = $query->count();
            $employees = $query
                ->orderBy('nama_lengkap', 'asc')
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get()
                ->map(function ($emp) {
                    $namaDepan = explode(' ', trim($emp->nama_lengkap))[0];
                    return [
                        'id' => $emp->id,
                        'nama' => $namaDepan,
                        'nama_lengkap' => $emp->nama_lengkap,
                        'nip' => $emp->nip,
                        'jabatan' => $emp->jabatan,
                        'divisi' => $emp->divisi,
                        'tanggal_join' => $this->formatJoinDate($emp),
                        'status' => $emp->status_aktif == '1' ? 'Aktif' : 'Resign',
                        'resigned_at' => $emp->resigned_at ? Carbon::parse($emp->resigned_at)->format('d M Y') : null,
                    ];
                });

            return response()->json(
                [
                    'data' => $employees,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $perPage,
                        'total' => $total,
                        'last_page' => ceil($total / $perPage),
                    ],
                    'filters' => [
                        'periode' => $periode,
                        'year' => $year,
                        'search' => $search,
                    ],
                ],
                200,
            );
        } catch (\Exception $e) {
            Log::error('HR Category Data Error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal memuat data'], 500);
        }
    }

    public function exportHeadcountTrendCsv(Request $request)
    {
        $trend = $this->getHeadcountTrend($request);
        $data = $trend->original;

        $filename = 'headcount_trend_' . date('Ymd') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Periode', 'Active', 'New Hire', 'Resign']);
            for ($i = 0; $i < count($data['labels']); $i++) {
                fputcsv($file, [$data['labels'][$i], $data['datasets'][0]['data'][$i], $data['datasets'][1]['data'][$i], $data['datasets'][2]['data'][$i]]);
            }
            fputcsv($file, []);
            fputcsv($file, ['Summary']);
            fputcsv($file, ['Total Active', $data['summary']['total_active']]);
            fputcsv($file, ['Total New', $data['summary']['total_new']]);
            fputcsv($file, ['Total Resign', $data['summary']['total_resign']]);
            fputcsv($file, ['Avg Monthly New', $data['summary']['avg_monthly_new']]);
            fputcsv($file, ['Avg Monthly Resign', $data['summary']['avg_monthly_resign']]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportHeadcountTrendPdf(Request $request)
    {
        $trend = $this->getHeadcountTrend($request)->original;
        $dateRange = ($request->start_date ?? '12 bulan terakhir') . ' s/d ' . ($request->end_date ?? 'sekarang');

        $pdf = Pdf::loadView('office/HR/exports/headcount_trend_pdf', [
            'trend' => $trend,
            'date_range' => $dateRange,
            'generated_at' => Carbon::now()->format('d M Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('headcount_trend_' . date('Ymd') . '.pdf');
    }

    public function exportHeadcountBreakdownCsv(Request $request)
    {
        $breakdown = $this->getHeadcountBreakdown($request);
        $data = $breakdown->original;

        $filename = 'headcount_breakdown_' . date('Ymd') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Kategori', 'Total', 'Active', 'Resign', 'Retention Rate (%)']);
            foreach ($data['breakdown'] as $item) {
                fputcsv($file, [$item['label'], $item['total'], $item['active'], $item['resign'], $item['retention']]);
            }
            fputcsv($file, []);
            fputcsv($file, ['Summary']);
            fputcsv($file, ['Total Categories', $data['summary']['total_categories']]);
            fputcsv($file, ['Top Category', $data['summary']['top_category']]);
            fputcsv($file, ['Avg Retention Rate', $data['summary']['avg_retention']]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportHeadcountBreakdownPdf(Request $request)
    {
        $breakdown = $this->getHeadcountBreakdown($request)->original;
        $filterBy = $request->filter_by ?? 'divisi';

        $pdf = Pdf::loadView('office/HR/exports/headcount_breakdown_pdf', [
            'breakdown' => $breakdown,
            'filter_by' => $filterBy,
            'generated_at' => Carbon::now()->format('d M Y H:i'),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('headcount_breakdown_' . date('Ymd') . '.pdf');
    }

    private function calculateDateRange($periode, $year = null)
    {
        $today = Carbon::today();
        if ($periode === 'year' && $year) {
            return [
                'start' => Carbon::createFromDate($year, 1, 1),
                'end' => Carbon::createFromDate($year, 12, 31),
                'label' => "Tahun {$year}",
            ];
        }
        $months = ['3' => 3, '6' => 6, '12' => 12];
        $monthCount = $months[$periode] ?? 12;
        $startDate = $today
            ->copy()
            ->subMonths($monthCount - 1)
            ->startOfMonth();
        return [
            'start' => $startDate,
            'end' => $today->copy()->endOfMonth(),
            'label' => "{$monthCount} Bulan Terakhir",
        ];
    }

    private function generatePeriods($startDate, $endDate, $groupBy)
    {
        $periods = [];
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            if ($groupBy === 'year') {
                $periods[] = [
                    'value' => $current->year,
                    'label' => $current->format('Y'),
                    'start' => $current->copy()->startOfYear(),
                    'end' => $current->copy()->endOfYear(),
                ];
                $current->addYear();
            } elseif ($groupBy === 'quarter') {
                $quarter = $current->quarter;
                $periods[] = [
                    'value' => $current->year . '-Q' . $quarter,
                    'label' => 'Q' . $quarter . ' ' . $current->format('Y'),
                    'start' => $current->copy()->startOfQuarter(),
                    'end' => $current->copy()->endOfQuarter(),
                ];
                $current->addQuarter();
            } else {
                $periods[] = [
                    'value' => $current->format('Y-m'),
                    'label' => $current->format('M Y'),
                    'start' => $current->copy()->startOfMonth(),
                    'end' => $current->copy()->endOfMonth(),
                ];
                $current->addMonth();
            }
        }
        return $periods;
    }

    private function applyDateFilter($query, $period, $groupBy, $fields)
    {
        $query->where(function ($q) use ($period, $groupBy, $fields) {
            foreach ($fields as $field) {
                $q->orWhere(function ($sub) use ($period, $groupBy, $field) {
                    if ($groupBy === 'year') {
                        $sub->whereRaw("YEAR($field) = ?", [$period['value']]);
                    } elseif ($groupBy === 'quarter') {
                        $sub->whereRaw("CONCAT(YEAR($field), '-Q', QUARTER($field)) = ?", [$period['value']]);
                    } else {
                        $sub->whereRaw("DATE_FORMAT($field, '%Y-%m') = ?", [$period['value']]);
                    }
                });
            }
        });
    }

    private function generateRetentionInsights($active, $resign, $total)
    {
        $rate = $total > 0 ? ($active / $total) * 100 : 0;
        $status = $rate >= 90 ? 'excellent' : ($rate >= 75 ? 'good' : ($rate >= 60 ? 'moderate' : 'needs_attention'));
        $opportunities = [];
        if ($resign > 0) {
            $opportunities[] = 'Lakukan exit interview untuk memahami alasan resign';
            $opportunities[] = 'Tingkatkan program onboarding untuk karyawan baru';
        }
        if ($rate < 80) {
            $opportunities[] = 'Evaluasi kompensasi dan benefit secara berkala';
            $opportunities[] = 'Kembangkan program career path yang jelas';
        }
        if ($rate >= 90) {
            $opportunities[] = 'Pertahankan budaya kerja positif yang sudah terbangun';
            $opportunities[] = 'Kembangkan program employee engagement lanjutan';
        }
        $projections = [
            'next_quarter' => [
                'estimated_active' => $active + round($active * 0.02),
                'estimated_resign' => max(0, $resign - 1),
                'confidence' => 'medium',
            ],
            'next_year' => [
                'estimated_active' => $active + round($active * 0.08),
                'estimated_resign' => max(0, $resign - 3),
                'confidence' => 'low',
            ],
        ];
        return [
            'status' => $status,
            'status_label' => ['excellent' => 'Sangat Baik', 'good' => 'Baik', 'moderate' => 'Cukup', 'needs_attention' => 'Perlu Perhatian'][$status],
            'opportunities' => $opportunities,
            'projections' => $projections,
            'recommendations' => $this->getRecommendations($status, $active, $resign),
        ];
    }

    private function getRecommendations($status, $active, $resign)
    {
        $base = ['Lakukan survey kepuasan karyawan setiap 6 bulan', 'Bangun program mentorship untuk karyawan baru', 'Sediakan jalur komunikasi terbuka antara staff dan manajemen'];
        if ($status === 'needs_attention') {
            return array_merge(['Prioritaskan retensi dengan review kompensasi', 'Identifikasi faktor penyebab turnover tinggi', 'Buat program recognition untuk apresiasi karyawan'], $base);
        }
        if ($status === 'moderate') {
            return array_merge(['Fokus pada pengembangan karir karyawan', 'Tingkatkan work-life balance melalui kebijakan fleksibel'], $base);
        }
        return array_merge(['Kembangkan program leadership untuk talenta potensial', 'Ekspansi benefit non-finansial untuk meningkatkan engagement'], $base);
    }

    private function formatJoinDate($emp)
    {
        $date = $emp->awal_probation ?? ($emp->awal_kontrak ?? $emp->awal_tetap);
        return $date ? Carbon::parse($date)->format('d M Y') : '-';
    }

    //Payroll Function
   public function payrollIndex()
    {
        return view('office.HR.payroll.index');
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

            $tunjanganData = TunjanganKaryawan::with('jenistunjangan:id,nama_tunjangan,tipe')
                ->whereIn('id_karyawan', $eligibleIds)
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->get()
                ->groupBy('id_karyawan');

            $periodStart = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
            $periodEnd = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();

            $payrollList = $eligibleKaryawan->map(function ($emp) use ($tunjanganData, $periodStart, $periodEnd) {
                $items = $tunjanganData->get($emp->id, collect());
                $gajiPokok = (float) ($emp->gaji_pokok ?? ($emp->gaji ?? 0));

                $totalTunjangan = $items->where('jenistunjangan.tipe', 'Tunjangan')->sum('total');
                $totalPotongan = abs($items->where('jenistunjangan.tipe', 'Potongan')->sum('total'));
                $gajiBersih = $gajiPokok + $totalTunjangan - $totalPotongan;

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
                'total_tunjangan' => $payrollList->sum('total_tunjangan'),
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
            fputcsv($file, ['No', 'Nama', 'Kode', 'Divisi', 'Jabatan', 'Gaji Pokok', 'Total Tunjangan', 'Total Potongan', 'Gaji Bersih', 'Status']);

            $i = 1;
            foreach ($res['data'] as $row) {
                fputcsv($file, [$i++, $row['nama'], $row['kode'] ?? '-', $row['divisi'], $row['jabatan'], $row['gaji_pokok'], $row['total_tunjangan'], $row['total_potongan'], $row['gaji_bersih'], $row['status']]);
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
                    $q->whereNull('awal_probation')
                        ->orWhere('awal_probation', '<', $periodStart->copy()->subMonth());
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

    //Kehadiran Function
    public function kehadiranIndex()
    {
        return view('office.HR.presence.index');
    }

    public function getAttendanceAnalytics(Request $request)
    {
        try {
            $bulan = (int) $request->input('month', now()->month);
            $tahun = (int) $request->input('year', now()->year);
            $divisi = $request->input('divisi', 'all');
            $jabatan = $request->input('jabatan', 'all');

            $this->syncHolidays($tahun);

            $baseQuery = AbsensiKaryawan::query()->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->with('karyawan:id,nama_lengkap,jabatan,divisi,status_aktif');

            if ($divisi !== 'all') {
                $baseQuery->whereHas('karyawan', fn($q) => $q->where('divisi', $divisi));
            }
            if ($jabatan !== 'all') {
                $baseQuery->whereHas('karyawan', fn($q) => $q->where('jabatan', $jabatan));
            }

            $absensi = $baseQuery->get();
            $karyawanIds = $absensi->pluck('id_karyawan')->unique();

            $totalHariKerja = $this->countWorkingDays($bulan, $tahun);
            $totalKaryawan = karyawan::where('status_aktif', '1')
                ->whereNotIn('jabatan', ['Direktur', 'Direktur Utama', 'Outsource'])
                ->when($divisi !== 'all', fn($q) => $q->where('divisi', $divisi))
                ->when($jabatan !== 'all', fn($q) => $q->where('jabatan', $jabatan))
                ->count();

            $approvedLeaves = $this->getApprovedLeaves($karyawanIds, $bulan, $tahun);
            $summary = $this->calculateAttendanceSummary($absensi, $totalHariKerja, $totalKaryawan, $approvedLeaves);
            $punctualityTrend = $this->calculatePunctualityTrend($absensi, $bulan, $tahun, $approvedLeaves);
            $departmentComparison = $this->calculateDepartmentComparison($absensi, $bulan, $tahun, $approvedLeaves);
            $attendanceHeatmap = $this->calculateAttendanceHeatmap($absensi, $bulan, $tahun);
            $riskAnalysis = $this->calculateAttendanceRisk($absensi, $karyawanIds, $bulan, $tahun, $approvedLeaves);
            $predictions = $this->generateAttendancePredictions($summary);
            $opportunities = $this->generateOpportunities($summary, $riskAnalysis, $predictions);

            return response()->json([
                'success' => true,
                'summary' => $summary,
                'charts' => [
                    'punctuality_trend' => $punctualityTrend,
                    'department_comparison' => $departmentComparison,
                    'attendance_heatmap' => $attendanceHeatmap,
                    'risk_distribution' => $riskAnalysis['distribution'],
                ],
                'predictions' => $predictions,
                'opportunities' => $opportunities,
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
                    'message' => 'Gagal memuat analytics: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    private function syncHolidays($tahun)
    {
        $response = Http::get('https://libur.deno.dev/api', ['year' => $tahun]);
        if ($response->successful()) {
            foreach ($response->json() as $libur) {
                HariLibur::updateOrCreate(['tanggal' => $libur['date']], ['nama' => $libur['name'], 'year' => $tahun]);
            }
        }
    }

    private function countWorkingDays($bulan, $tahun)
    {
        $start = Carbon::createFromDate($tahun, $bulan, 1);
        $end = $start->copy()->endOfMonth();
        $holidays = HariLibur::whereYear('tanggal', $tahun)->pluck('tanggal')->map(fn($d) => Carbon::parse($d)->toDateString())->toArray();
        $days = 0;
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if (!$date->isWeekend() && !in_array($date->toDateString(), $holidays)) {
                $days++;
            }
        }
        return $days;
    }

    private function getApprovedLeaves($karyawanIds, $bulan, $tahun)
    {
        return pengajuancuti::whereIn('id_karyawan', $karyawanIds)
            ->where('approval_manager', 1)
            ->where(function ($q) use ($bulan, $tahun) {
                $q->whereYear('tanggal_awal', $tahun)
                    ->whereMonth('tanggal_awal', $bulan)
                    ->orWhereYear('tanggal_akhir', $tahun)
                    ->whereMonth('tanggal_akhir', $bulan)
                    ->orWhereRaw('? BETWEEN tanggal_awal AND tanggal_akhir', [$tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-01']);
            })
            ->get()
            ->flatMap(function ($cuti) {
                $start = Carbon::parse($cuti->tanggal_awal);
                $end = Carbon::parse($cuti->tanggal_akhir);
                $dates = [];
                for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                    if (!$d->isWeekend()) {
                        $dates[] = [
                            'id_karyawan' => $cuti->id_karyawan,
                            'tanggal' => $d->toDateString(),
                            'tipe' => $cuti->tipe,
                        ];
                    }
                }
                return $dates;
            });
    }

    private function calculateAttendanceSummary($absensi, $totalHariKerja, $totalKaryawan, $approvedLeaves)
    {
        $leaveDates = collect($approvedLeaves)->groupBy('id_karyawan')->map(fn($v) => $v->pluck('tanggal'));
        $totalAbsen = $absensi->count();
        $hadir = $absensi->whereNotNull('jam_masuk')->count();
        $telat = $absensi->where('waktu_keterlambatan', '!=', '00:00:00')->where('waktu_keterlambatan', '!=', null)->count();
        $absenDenganIzin = $absensi->filter(fn($a) => $leaveDates->get($a->id_karyawan, collect())->contains($a->tanggal))->count();
        $tidakHadir = max(0, $totalHariKerja * $totalKaryawan - $totalAbsen - $absenDenganIzin);
        $totalDetikTelat = $absensi->sum(function ($a) {
            if (!$a->waktu_keterlambatan) {
                return 0;
            }
            $p = explode(':', $a->waktu_keterlambatan);
            return $p[0] * 3600 + $p[1] * 60 + ($p[2] ?? 0);
        });
        $rataRataTelat = $telat > 0 ? round($totalDetikTelat / $telat / 60, 1) : 0;
        $expectedRecords = $totalHariKerja * $totalKaryawan;
        $attendanceRate = $expectedRecords > 0 ? round((($hadir + $absenDenganIzin) / $expectedRecords) * 100, 1) : 0;
        $punctualityRate = $hadir > 0 ? round((($hadir - $telat) / $hadir) * 100, 1) : 100;
        return [
            'total_hari_kerja' => $totalHariKerja,
            'total_karyawan' => $totalKaryawan,
            'total_absen' => $totalAbsen,
            'hadir' => $hadir,
            'telat' => $telat,
            'tidak_hadir' => $tidakHadir,
            'cuti_sakit' => $absenDenganIzin,
            'attendance_rate' => $attendanceRate,
            'punctuality_rate' => $punctualityRate,
            'avg_late_minutes' => $rataRataTelat,
            'total_late_minutes' => round($totalDetikTelat / 60),
        ];
    }

    private function calculatePunctualityTrend($absensi, $bulan, $tahun, $approvedLeaves)
    {
        $leaveMap = collect($approvedLeaves)->groupBy('tanggal')->map(fn($v) => $v->pluck('id_karyawan'));
        $trend = [];
        $daysInMonth = Carbon::createFromDate($tahun, $bulan, 1)->daysInMonth;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $tanggal = Carbon::createFromDate($tahun, $bulan, $d);
            if ($tanggal->isWeekend() || HariLibur::whereDate('tanggal', $tanggal)->exists()) {
                continue;
            }
            $dailyAbsen = $absensi->where('tanggal', $tanggal->toDateString());
            $total = $dailyAbsen->count();
            $onLeave = $dailyAbsen->filter(fn($a) => $leaveMap->get($tanggal->toDateString(), collect())->contains($a->id_karyawan))->count();
            $effectiveTotal = $total - $onLeave;
            $telat = $dailyAbsen->where('waktu_keterlambatan', '!=', '00:00:00')->where('waktu_keterlambatan', '!=', null)->count();
            $trend[] = [
                'date' => $tanggal->format('d/m'),
                'total' => $effectiveTotal,
                'late_count' => $telat,
                'late_rate' => $effectiveTotal > 0 ? round(($telat / $effectiveTotal) * 100, 1) : 0,
            ];
        }
        return $trend;
    }

    private function calculateDepartmentComparison($absensi, $bulan, $tahun, $approvedLeaves)
    {
        $leaveMap = collect($approvedLeaves)->groupBy('id_karyawan')->map(fn($v) => $v->pluck('tanggal'));
        $divisi = $absensi->groupBy(fn($a) => optional($a->karyawan)->divisi ?? 'Unknown');
        $result = [];
        foreach ($divisi as $namaDivisi => $data) {
            $total = $data->count();
            $onLeave = $data->filter(fn($a) => $leaveMap->get($a->id_karyawan, collect())->contains($a->tanggal))->count();
            $hadir = $data->whereNotNull('jam_masuk')->count() - $onLeave;
            $telat = $data->where('waktu_keterlambatan', '!=', '00:00:00')->where('waktu_keterlambatan', '!=', null)->count();
            $effectiveTotal = max(1, $total - $onLeave);
            $result[] = [
                'divisi' => $namaDivisi,
                'attendance_rate' => round(($hadir / $effectiveTotal) * 100, 1),
                'punctuality_rate' => $hadir > 0 ? round((($hadir - $telat) / $hadir) * 100, 1) : 100,
                'late_count' => $telat,
                'employee_count' => $data->pluck('id_karyawan')->unique()->count(),
            ];
        }
        return collect($result)->sortByDesc('attendance_rate')->take(10)->values()->toArray();
    }

    private function calculateAttendanceHeatmap($absensi, $bulan, $tahun)
    {
        $heatmap = [];
        $weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
        $hours = range(7, 18);
        foreach ($weekdays as $day) {
            foreach ($hours as $hour) {
                $heatmap["{$day}_{$hour}"] = 0;
            }
        }
        $holidays = HariLibur::whereYear('tanggal', $tahun)->pluck('tanggal')->map(fn($d) => Carbon::parse($d)->toDateString())->toArray();
        foreach ($absensi as $a) {
            $tanggal = Carbon::parse($a->tanggal);
            if ($tanggal->isWeekend() || in_array($tanggal->toDateString(), $holidays)) {
                continue;
            }
            $dayName = $tanggal->format('D');
            $jamMasuk = $a->jam_masuk ? Carbon::parse($a->jam_masuk)->format('H') : null;
            if ($jamMasuk && in_array($dayName, $weekdays) && $jamMasuk >= 7 && $jamMasuk <= 18) {
                $key = "{$dayName}_{$jamMasuk}";
                $heatmap[$key] = ($heatmap[$key] ?? 0) + 1;
            }
        }
        $maxValue = max($heatmap) ?: 1;
        return ['labels' => array_keys($heatmap), 'values' => array_map(fn($v) => round(($v / $maxValue) * 100, 1), $heatmap), 'raw' => $heatmap];
    }

    private function calculateAttendanceRisk($absensi, $karyawanIds, $bulan, $tahun, $approvedLeaves)
    {
        $leaveMap = collect($approvedLeaves)->groupBy('id_karyawan')->map(fn($v) => $v->pluck('tanggal'));
        $risks = [];
        $holidays = HariLibur::whereYear('tanggal', $tahun)->pluck('tanggal')->map(fn($d) => Carbon::parse($d)->toDateString())->toArray();
        foreach ($karyawanIds as $id) {
            $data = $absensi->where('id_karyawan', $id);
            $total = $data->count();
            if ($total < 10) {
                continue;
            }
            $onLeave = $data->filter(fn($a) => $leaveMap->get($a->id_karyawan, collect())->contains($a->tanggal))->count();
            $telat = $data->where('waktu_keterlambatan', '!=', '00:00:00')->where('waktu_keterlambatan', '!=', null)->count();
            $tidakHadir = $data->whereNull('jam_masuk')->whereNotIn('tanggal', $holidays)->count() - $onLeave;
            $effectiveTotal = max(1, $total - $onLeave);
            $lateRate = $telat / $effectiveTotal;
            $absentRate = max(0, $tidakHadir) / $effectiveTotal;
            $riskScore = $lateRate * 40 + $absentRate * 60;
            $level = $riskScore >= 70 ? 'high' : ($riskScore >= 40 ? 'medium' : 'low');
            $risks[] = [
                'id_karyawan' => $id,
                'nama' => optional($data->first()->karyawan)->nama_lengkap,
                'divisi' => optional($data->first()->karyawan)->divisi,
                'risk_score' => round($riskScore, 1),
                'risk_level' => $level,
                'late_rate' => round($lateRate * 100, 1),
                'absent_rate' => round($absentRate * 100, 1),
                'recommendation' => $this->getRiskRecommendation($level, $lateRate, $absentRate),
            ];
        }
        $distribution = ['high' => collect($risks)->where('risk_level', 'high')->count(), 'medium' => collect($risks)->where('risk_level', 'medium')->count(), 'low' => collect($risks)->where('risk_level', 'low')->count()];
        return ['list' => collect($risks)->sortByDesc('risk_score')->take(20)->values()->toArray(), 'distribution' => $distribution];
    }

    private function generateAttendancePredictions($summary)
    {
        $currentRate = $summary['attendance_rate'];
        $projections = [
            'next_month' => ['estimated_rate' => min(99.5, round($currentRate + rand(-2, 3), 1)), 'confidence' => 'medium', 'factors' => ['Musim liburan', 'Event perusahaan', 'Kebijakan WFH']],
            'next_quarter' => ['estimated_rate' => min(98, round($currentRate + rand(-3, 4), 1)), 'confidence' => 'low', 'factors' => ['Perubahan kebijakan', 'Turnover karyawan', 'Kondisi ekonomi']],
            'next_year' => ['estimated_rate' => min(97, round($currentRate + rand(-5, 5), 1)), 'confidence' => 'very_low', 'factors' => ['Transformasi digital', 'Generasi workforce baru', 'Regulasi ketenagakerjaan']],
        ];
        $milestones = [['target' => 95, 'timeline' => '3 bulan', 'actions' => ['Reminder otomatis', 'Flexible time window 15 menit']], ['target' => 97, 'timeline' => '6 bulan', 'actions' => ['Program wellness', 'Transport allowance review']], ['target' => 99, 'timeline' => '12 bulan', 'actions' => ['AI-based predictive scheduling', 'Holistic work-life integration']]];
        return ['current_rate' => $currentRate, 'projections' => $projections, 'milestones' => $milestones, 'key_drivers' => ['positive' => ['Budaya disiplin', 'Sistem reward', 'Komunikasi transparan'], 'negative' => ['Transportasi tidak memadai', 'Workload berlebihan', 'Kesehatan karyawan']]];
    }

    private function generateOpportunities($summary, $riskAnalysis, $predictions)
    {
        $opportunities = [];
        if ($summary['attendance_rate'] < 90) {
            $opportunities[] = ['level' => 'operational', 'priority' => 'high', 'title' => 'Optimasi Sistem Reminder', 'description' => 'Implementasi notifikasi H-1 dan H0 untuk meningkatkan kesadaran kehadiran', 'impact' => '+5-8% attendance rate', 'effort' => 'low', 'timeline' => '2-4 minggu'];
        }
        if ($summary['avg_late_minutes'] > 15) {
            $opportunities[] = ['level' => 'tactical', 'priority' => 'medium', 'title' => 'Flexible Time Window', 'description' => 'Berikan toleransi 15-30 menit untuk jam masuk tanpa penalty', 'impact' => '-40% late complaints', 'effort' => 'medium', 'timeline' => '1-2 bulan'];
        }
        if ($riskAnalysis['distribution']['high'] > 0) {
            $opportunities[] = ['level' => 'strategic', 'priority' => 'high', 'title' => 'Early Intervention Program', 'description' => 'Program coaching untuk karyawan dengan risk score tinggi', 'impact' => '-60% high-risk employees', 'effort' => 'high', 'timeline' => '3-6 bulan'];
        }
        $opportunities[] = ['level' => 'transformational', 'priority' => 'low', 'title' => 'AI-Powered Attendance Intelligence', 'description' => 'Predictive analytics untuk antisipasi pola absensi dan optimalisasi shift', 'impact' => '+15% operational efficiency', 'effort' => 'very_high', 'timeline' => '6-12 bulan'];
        return collect($opportunities)->sortByDesc(fn($o) => (['high' => 4, 'medium' => 3, 'low' => 2][$o['priority']] ?? 1) * 10 - ($o['effort'] === 'low' ? 0 : 5))->values()->toArray();
    }

    private function getRiskRecommendation($level, $lateRate, $absentRate)
    {
        if ($level === 'high') {
            return $absentRate > $lateRate ? 'Evaluasi engagement & workload; pertimbangkan counseling' : 'Review commute & schedule flexibility; tawarkan support';
        }
        if ($level === 'medium') {
            return 'Monitoring berkala; berikan feedback konstruktif';
        }
        return 'Maintain performance; consider as role model';
    }

    public function exportAttendanceReport(Request $request)
    {
        $format = $request->input('format', 'pdf');
        $bulan = (int) $request->input('month', now()->month);
        $tahun = (int) $request->input('year', now()->year);
        $periode = $request->input('periode', 'monthly');

        $analytics = $this->getAttendanceAnalytics(new Request(['month' => $bulan, 'year' => $tahun]))->getData(true);

        if ($format === 'csv') {
            return $this->exportAttendanceExcel($analytics, $bulan, $tahun, $periode);
        }
        return $this->exportAttendancePdf($analytics, $bulan, $tahun, $periode);
    }

    private function exportAttendanceExcel($analytics, $bulan, $tahun, $periode = 'monthly')
    {
        $matrix = $this->buildAttendanceMatrix($bulan, $tahun, $periode);
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Attendance');
        
        $sheet->mergeCells('A1:C1');
        $sheet->setCellValue('A1', 'LAPORAN KEHADIRAN KARYAWAN');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->mergeCells('A2:C2');
        $sheet->setCellValue('A2', 'Periode: ' . Carbon::createFromDate($tahun, $bulan, 1)->format('F Y'));
        
        $sheet->mergeCells('A3:C3');
        $sheet->setCellValue('A3', 'Tipe: ' . strtoupper($periode));
        
        $sheet->mergeCells('A4:C4');
        $sheet->setCellValue('A4', 'Dicetak: ' . now()->format('d F Y H:i'));
        $sheet->getRowDimension(4)->setRowHeight(15);
        
        $sheet->getRowDimension(5)->setRowHeight(5);
        
        $col = 1;
        foreach ($matrix['headers'] as $header) {
            $cell = $sheet->getCellByColumnAndRow($col, 6);
            $cell->setValue($header);
            $sheet->getStyleByColumnAndRow($col, 6)->getFont()->setBold(true)->setSize(8);
            $sheet->getStyleByColumnAndRow($col, 6)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setWrapText(true);
            $sheet->getStyleByColumnAndRow($col, 6)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF2C3E50');
            $sheet->getStyleByColumnAndRow($col, 6)->getFont()->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyleByColumnAndRow($col, 6)->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
            
            if ($col === 1) $sheet->getColumnDimension('A')->setWidth(22); // Nama
            elseif ($col === 2) $sheet->getColumnDimension('B')->setWidth(18); // Divisi
            elseif ($col === 3) $sheet->getColumnDimension('C')->setWidth(18); // Jabatan
            elseif (in_array($header, ['Total Hadir', 'Total Telat', 'Total Cuti', 'Overall Avg Late'])) {
                $sheet->getColumnDimensionByColumn($col)->setWidth(12);
            } else {
                $sheet->getColumnDimensionByColumn($col)->setWidth(2.8); // Tanggal: sangat narrow
            }
            $col++;
        }
        
        // === DATA ROWS ===
        $startRow = 7;
        foreach ($matrix['rows'] as $rIdx => $dataRow) {
            $row = $startRow + $rIdx;
            $col = 1;
            foreach ($dataRow as $cIdx => $cellValue) {
                $cell = $sheet->getCellByColumnAndRow($col, $row);
                
                // Format nilai
                $displayValue = $cellValue;
                if (is_string($cellValue)) {
                    $displayValue = match($cellValue) {
                        'H' => '✓',
                        'L' => 'L',
                        'Y' => 'C',
                        'X' => '•',
                        '-' => '',
                        default => $cellValue,
                    };
                }
                $cell->setValue($displayValue);
                
                // Styling dasar
                $sheet->getStyleByColumnAndRow($col, $row)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyleByColumnAndRow($col, $row)->getFont()->setSize(8);
                $sheet->getStyleByColumnAndRow($col, $row)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                
                // Kolom nama/divisi/jabatan: rata kiri
                if ($col <= 3) {
                    $sheet->getStyleByColumnAndRow($col, $row)->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    if ($col === 1) $sheet->getStyleByColumnAndRow($col, $row)->getFont()->setBold(true);
                }
                
                // Color coding untuk status
                if ($col > 3 && !in_array($matrix['headers'][$cIdx] ?? '', ['Total Hadir', 'Total Telat', 'Total Cuti', 'Overall Avg Late'])) {
                    if ($cellValue === 'L' || $cellValue === 'Telat') {
                        $sheet->getStyleByColumnAndRow($col, $row)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFFFF0F0'); // Merah sangat soft
                        $sheet->getStyleByColumnAndRow($col, $row)->getFont()
                            ->setBold(true)->getColor()->setARGB('FFC00000');
                    } elseif ($cellValue === 'Y' || $cellValue === 'Cuti/Holiday') {
                        $sheet->getStyleByColumnAndRow($col, $row)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFFFF9E6'); // Kuning soft
                    } elseif ($cellValue === 'X' || $cellValue === 'Weekend') {
                        $sheet->getStyleByColumnAndRow($col, $row)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFFFEBEE');
                        $sheet->getStyleByColumnAndRow($col, $row)->getFont()
                            ->getColor()->setARGB('FF999999');
                        $sheet->getStyleByColumnAndRow($col, $row)->getFont()->setItalic(true);
                    }
                }
                
                $col++;
            }
            $sheet->getRowDimension($row)->setRowHeight(18);
        }
        
        // === LEGEND ===
        $legendRow = $startRow + count($matrix['rows']) + 2;
        $sheet->mergeCells("A{$legendRow}:C{$legendRow}");
        $sheet->setCellValue("A{$legendRow}", 'KETERANGAN:');
        $sheet->getStyle("A{$legendRow}")->getFont()->setBold(true)->setSize(9);
        
        $legendItems = [
            ['✓', 'Hadir'],
            ['L', 'Telat'],
            ['C', 'Cuti/Holiday'],
            ['•', 'Weekend'],
            ['', 'Tidak Absen'],
        ];
        
        $lCol = 1;
        foreach ($legendItems as $i => $item) {
            $sheet->getCellByColumnAndRow($lCol, $legendRow + 1)->setValue($item[0]);
            $sheet->getCellByColumnAndRow($lCol + 1, $legendRow + 1)->setValue($item[1]);
            $sheet->getStyleByColumnAndRow($lCol, $legendRow + 1)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $lCol += 2;
        }
        
        // === FREEZE & PRINT ===
        $sheet->freezePane('D7');
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->getPageMargins()->setTop(0.5)->setRight(0.3)->setBottom(0.5)->setLeft(0.3);
        $sheet->getPageSetup()->setHorizontalCentered(true);
        
        // === OUTPUT ===
        $filename = "Laporan_Attendance_{$periode}_{$bulan}_{$tahun}.xlsx";
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0',
        ];
        
        $writer = new Xlsx($spreadsheet);
        
        ob_start();
        $writer->save('php://output');
        return response()->stream(fn() => ob_end_flush(), 200, $headers);
    }

    private function exportAttendancePdf($analytics, $bulan, $tahun, $periode = 'monthly')
    {
        $matrix = $this->buildAttendanceMatrix($bulan, $tahun, $periode);

        $pdf = Pdf::loadView('office.HR.exports.attendance_pdf', [
            'analytics' => $analytics,
            'period' => $analytics['period']['display'],
            'generated_at' => now()->format('d F Y H:i'),
            'matrix' => $matrix,
            'periode_type' => $periode,
        ])
        ->setPaper('a4', 'landscape')
        ->setOption('margin_top', 15)
        ->setOption('margin_bottom', 15)
        ->setOption('margin_left', 10)
        ->setOption('margin_right', 10);

        return $pdf->download("Laporan_Attendance_{$periode}_{$bulan}_{$tahun}.pdf");
    }

    private function buildAttendanceMatrix($bulan, $tahun, $periode = 'monthly')
    {
        // Fetch data dasar
        $holidays = HariLibur::whereYear('tanggal', $tahun)
            ->pluck('tanggal')
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->toArray();

        $karyawan = karyawan::query()
            ->where('status_aktif', '1')
            ->whereNotIn('Divisi', ['Direksi'])
            ->whereNotIn('jabatan', ['outsource', 'Outsource'])
            ->where('kode_karyawan', 'not like', 'OL%')
            ->whereNotNull('nip')
            ->with([
                'absensi' => fn($q) => $q
                    ->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun)
            ])
            ->get();

        $cutiApproved = pengajuancuti::where('approval_manager', 1)
            ->where(function($q) use ($bulan, $tahun) {
                $q->whereYear('tanggal_awal', $tahun)->whereMonth('tanggal_awal', $bulan)
                ->orWhereYear('tanggal_akhir', $tahun)->whereMonth('tanggal_akhir', $bulan);
            })
            ->get();

        $cutiMap = [];
        foreach ($cutiApproved as $c) {
            $start = Carbon::parse($c->tanggal_awal);
            $end = Carbon::parse($c->tanggal_akhir);
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                if (!$d->isWeekend() && !in_array($d->toDateString(), $holidays)) {
                    $cutiMap[$c->id_karyawan][] = $d->toDateString();
                }
            }
        }

        // Build matrix berdasarkan periode
        if ($periode === 'monthly') {
            return $this->buildMonthlyMatrix($karyawan, $bulan, $tahun, $holidays, $cutiMap);
        } elseif ($periode === 'quarterly') {
            return $this->buildQuarterlyMatrix($karyawan, $tahun, $holidays, $cutiMap);
        } else { // yearly
            return $this->buildYearlyMatrix($karyawan, $tahun, $holidays, $cutiMap);
        }
    }

    private function buildMonthlyMatrix($karyawan, $bulan, $tahun, $holidays, $cutiMap)
    {
        $daysInMonth = Carbon::createFromDate($tahun, $bulan, 1)->daysInMonth;
        $headers = ['Nama', 'Divisi', 'Jabatan'];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $tgl = Carbon::createFromDate($tahun, $bulan, $d);
            $headers[] = $d; // hanya angka tanggal
        }
        $headers[] = 'Total Hadir';
        $headers[] = 'Total Telat';
        $headers[] = 'Total Cuti';

        $rows = [];
        foreach ($karyawan as $emp) {
            $row = [$emp->nama_lengkap, $emp->divisi ?? '-', $emp->jabatan ?? '-'];
            $hadir = 0; $telat = 0; $cuti = 0;

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $tanggal = Carbon::createFromDate($tahun, $bulan, $d);
                $dateStr = $tanggal->toDateString();
                $isWeekend = $tanggal->isWeekend();
                $isHoliday = in_array($dateStr, $holidays);
                $isCuti = in_array($emp->id, $cutiMap) && in_array($dateStr, $cutiMap[$emp->id]);

                $absen = $emp->absensi->firstWhere('tanggal', $dateStr);
                $isLate = $absen && $absen->waktu_keterlambatan && $absen->waktu_keterlambatan !== '00:00:00';

                // Status untuk CSV: H=Hadir, L=Late, C=Cuti, X=Libur (merah), Y=Holiday/Cuti (kuning), -=Tidak Absen
                if ($isWeekend) {
                    $row[] = 'X'; // Weekend - merah
                } elseif ($isHoliday || $isCuti) {
                    $row[] = 'Y'; // Holiday/Cuti - kuning
                    if ($isCuti) $cuti++;
                } elseif ($absen && $absen->jam_masuk) {
                    $row[] = $isLate ? 'L' : 'H';
                    $hadir++;
                    if ($isLate) $telat++;
                } else {
                    $row[] = '-'; // Tidak absen
                }
            }
            $row[] = $hadir;
            $row[] = $telat;
            $row[] = $cuti;
            $rows[] = $row;
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    private function buildQuarterlyMatrix($karyawan, $tahun, $holidays, $cutiMap)
    {
        $headers = ['Nama', 'Divisi', 'Jabatan', 'Q1 Avg Late (menit)', 'Q2 Avg Late (menit)', 'Q3 Avg Late (menit)', 'Q4 Avg Late (menit)', 'Year Avg Late'];
        $rows = [];

        foreach ($karyawan as $emp) {
            $quarterData = [1=>[], 2=>[], 3=>[], 4=>[]];

            // Ambil semua absensi karyawan ini di tahun tersebut
            $absensiTahun = AbsensiKaryawan::where('id_karyawan', $emp->id)
                ->whereYear('tanggal', $tahun)
                ->get();

            foreach ($absensiTahun as $a) {
                $q = Carbon::parse($a->tanggal)->quarter;
                if ($a->waktu_keterlambatan && $a->waktu_keterlambatan !== '00:00:00') {
                    $p = explode(':', $a->waktu_keterlambatan);
                    $menit = $p[0]*60 + $p[1];
                    $quarterData[$q][] = $menit;
                }
            }

            $row = [$emp->nama_lengkap, $emp->divisi ?? '-', $emp->jabatan ?? '-'];
            $allLate = [];
            for ($q = 1; $q <= 4; $q++) {
                $avg = !empty($quarterData[$q]) ? round(array_sum($quarterData[$q]) / count($quarterData[$q]), 1) : 0;
                $row[] = $avg;
                $allLate = array_merge($allLate, $quarterData[$q]);
            }
            $yearAvg = !empty($allLate) ? round(array_sum($allLate) / count($allLate), 1) : 0;
            $row[] = $yearAvg;
            $rows[] = $row;
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    private function buildYearlyMatrix($karyawan, $tahun, $holidays, $cutiMap)
    {
        // Tampilkan rata-rata per bulan untuk SELURUH tahun yang terdata
        $minYear = AbsensiKaryawan::min('tanggal') ? Carbon::parse(AbsensiKaryawan::min('tanggal'))->year : $tahun;
        $maxYear = $tahun;

        $headers = ['Nama', 'Divisi', 'Jabatan'];
        for ($y = $minYear; $y <= $maxYear; $y++) {
            for ($m = 1; $m <= 12; $m++) {
                $headers[] = Carbon::createFromDate($y, $m, 1)->format('M Y');
            }
        }
        $headers[] = 'Overall Avg Late';

        $rows = [];
        foreach ($karyawan as $emp) {
            $row = [$emp->nama_lengkap, $emp->divisi ?? '-', $emp->jabatan ?? '-'];
            $allLate = [];

            for ($y = $minYear; $y <= $maxYear; $y++) {
                for ($m = 1; $m <= 12; $m++) {
                    $lateMinutes = AbsensiKaryawan::where('id_karyawan', $emp->id)
                        ->whereYear('tanggal', $y)
                        ->whereMonth('tanggal', $m)
                        ->whereNotNull('waktu_keterlambatan')
                        ->where('waktu_keterlambatan', '!=', '00:00:00')
                        ->get()
                        ->map(fn($a) => $this->parseLateMinutes($a->waktu_keterlambatan));

                    $avg = $lateMinutes->isNotEmpty() ? round($lateMinutes->avg(), 1) : 0;
                    $row[] = $avg;
                    if ($avg > 0) $allLate[] = $avg;
                }
            }
            $overall = !empty($allLate) ? round(array_sum($allLate) / count($allLate), 1) : 0;
            $row[] = $overall;
            $rows[] = $row;
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    public function getDivisionDailyStats(Request $request)
    {
        $bulan = (int) $request->input('month', now()->month);
        $tahun = (int) $request->input('year', now()->year);

        $holidays = HariLibur::whereYear('tanggal', $tahun)->pluck('tanggal')->map(fn($d) => Carbon::parse($d)->toDateString())->toArray();

        $absensi = AbsensiKaryawan::with('karyawan:id,nama_lengkap,divisi')->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->get();

        $cuti = pengajuancuti::where('approval_manager', 1)
            ->where(function ($q) use ($bulan, $tahun) {
                $q->whereYear('tanggal_awal', $tahun)->whereMonth('tanggal_awal', $bulan)->orWhereYear('tanggal_akhir', $tahun)->whereMonth('tanggal_akhir', $bulan);
            })
            ->get();

        $cutiMap = [];
        foreach ($cuti as $c) {
            $start = Carbon::parse($c->tanggal_awal);
            $end = Carbon::parse($c->tanggal_akhir);
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                if (!$d->isWeekend() && !in_array($d->toDateString(), $holidays)) {
                    $cutiMap[$c->id_karyawan][] = $d->toDateString();
                }
            }
        }

        $result = [];
        $daysInMonth = Carbon::createFromDate($tahun, $bulan, 1)->daysInMonth;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $tanggal = Carbon::createFromDate($tahun, $bulan, $day);
            if ($tanggal->isWeekend() || in_array($tanggal->toDateString(), $holidays)) {
                continue;
            }

            $dateStr = $tanggal->toDateString();
            $divStats = $absensi
                ->where('tanggal', $dateStr)
                ->groupBy(function ($a) {
                    return optional($a->karyawan)->divisi ?? 'Unknown';
                })
                ->map(function ($items) {
                    $total = $items->count();
                    $hadir = $items->whereNotNull('jam_masuk')->count();
                    $telat = $items->where('waktu_keterlambatan', '!=', '00:00:00')->where('waktu_keterlambatan', '!=', null)->count();
                    $cutiCount = $items->filter(fn($a) => in_array($a->tanggal, $cutiMap[$a->id_karyawan] ?? []))->count();
                    return [
                        'total' => $total,
                        'hadir' => $hadir - $cutiCount,
                        'telat' => $telat,
                        'cuti' => $cutiCount,
                        'rate' => $total > 0 ? round((($hadir - $cutiCount) / $total) * 100, 1) : 100,
                    ];
                });

            $result[] = ['date' => $tanggal->format('Y-m-d'), 'day' => $day, 'divisions' => $divStats];
        }

        return response()->json(['success' => true, 'data' => $result]);
    }

    public function getTopLateEmployees(Request $request)
    {
        $bulan = (int) $request->input('month', now()->month);
        $tahun = (int) $request->input('year', now()->year);
        $limit = (int) $request->input('limit', 10);

        $topLate = AbsensiKaryawan::select('id_karyawan', DB::raw('SUM(CASE WHEN waktu_keterlambatan != "00:00:00" AND waktu_keterlambatan IS NOT NULL THEN 1 ELSE 0 END) as late_count'), DB::raw('SUM(TIME_TO_SEC(waktu_keterlambatan)) as total_seconds'))
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->with('karyawan:id,nama_lengkap,jabatan,divisi,foto')
            ->groupBy('id_karyawan')
            ->having('late_count', '>', 0)
            ->orderByDesc('total_seconds')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $hours = floor($item->total_seconds / 3600);
                $minutes = floor(($item->total_seconds % 3600) / 60);
                return [
                    'id' => $item->id_karyawan,
                    'nama' => optional($item->karyawan)->nama_lengkap,
                    'jabatan' => optional($item->karyawan)->jabatan,
                    'divisi' => optional($item->karyawan)->divisi,
                    'foto' => optional($item->karyawan)->foto,
                    'late_count' => $item->late_count,
                    'total_late_minutes' => $hours * 60 + $minutes,
                    'avg_late_minutes' => $item->late_count > 0 ? round(($hours * 60 + $minutes) / $item->late_count, 1) : 0,
                ];
            });

        return response()->json(['success' => true, 'data' => $topLate]);
    }

    public function getAttendanceCalendar(Request $request)
    {
        $bulan = (int) $request->input('month', now()->month);
        $tahun = (int) $request->input('year', now()->year);
        $idKaryawan = $request->input('id_karyawan');

        $holidays = HariLibur::whereYear('tanggal', $tahun)->pluck('tanggal')->map(fn($d) => Carbon::parse($d)->toDateString())->toArray();

        $absensi = AbsensiKaryawan::whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->when($idKaryawan, fn($q) => $q->where('id_karyawan', $idKaryawan))->get()->keyBy('tanggal');

        $cuti = pengajuancuti::where('approval_manager', 1)
            ->when($idKaryawan, fn($q) => $q->where('id_karyawan', $idKaryawan))
            ->where(function ($q) use ($bulan, $tahun) {
                $q->whereYear('tanggal_awal', $tahun)->whereMonth('tanggal_awal', $bulan)->orWhereYear('tanggal_akhir', $tahun)->whereMonth('tanggal_akhir', $bulan);
            })
            ->get();

        $cutiDates = [];
        foreach ($cuti as $c) {
            $start = Carbon::parse($c->tanggal_awal);
            $end = Carbon::parse($c->tanggal_akhir);
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                if (!$d->isWeekend() && !in_array($d->toDateString(), $holidays)) {
                    $cutiDates[] = $d->toDateString();
                }
            }
        }

        $calendar = [];
        $daysInMonth = Carbon::createFromDate($tahun, $bulan, 1)->daysInMonth;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $tanggal = Carbon::createFromDate($tahun, $bulan, $day);
            $dateStr = $tanggal->toDateString();
            $isWeekend = $tanggal->isWeekend();
            $isHoliday = in_array($dateStr, $holidays);
            $isCuti = in_array($dateStr, $cutiDates);

            $absen = $absensi->get($dateStr);
            $isLate = $absen && $absen->waktu_keterlambatan && $absen->waktu_keterlambatan !== '00:00:00';

            $status = 'normal';
            $bgColor = '';

            if ($isWeekend || $isHoliday) {
                $status = 'holiday';
                $bgColor = '#fef3c7';
            } elseif ($isCuti) {
                $status = 'leave';
                $bgColor = '#fef3c7';
            } elseif ($isLate) {
                $status = 'late';
                $bgColor = '#fee2e2';
            }

            $calendar[] = [
                'date' => $dateStr,
                'day' => $day,
                'day_name' => $tanggal->format('D'),
                'status' => $status,
                'bg_color' => $bgColor,
                'late_minutes' => $isLate ? $this->parseLateMinutes($absen->waktu_keterlambatan) : 0,
                'jam_masuk' => $absen?->jam_masuk,
                'jam_keluar' => $absen?->jam_keluar,
            ];
        }

        return response()->json(['success' => true, 'calendar' => $calendar, 'month' => $bulan, 'year' => $tahun]);
    }

    private function parseLateMinutes($waktu)
    {
        if (!$waktu || $waktu === '00:00:00') {
            return 0;
        }

        $p = explode(':', $waktu);

        $jam = (int) $p[0] * 60;
        $menit = (int) $p[1];

        return ($jam + $menit) / 2;
    }
}
