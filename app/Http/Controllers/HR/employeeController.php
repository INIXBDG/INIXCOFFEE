<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\karyawan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class employeeController extends Controller
{
    //Karyawan Infomasi Function
    public function index()
    {
        return view('HR/employee/newActive');
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
                ->selectRaw(
                    "
                    $field as label,
                    COUNT(*) as total,
                    SUM(CASE WHEN status_aktif = '1' THEN 1 ELSE 0 END) as active_count,
                    SUM(CASE WHEN status_aktif = '0' THEN 1 ELSE 0 END) as resign_count
                ",
                )
                ->groupBy($field)
                ->orderByDesc('total')
                ->limit(15)
                ->get()
                ->map(function ($item) {
                    $percentage = $item->total > 0 ? round(($item->active_count / $item->total) * 100, 1) : 0;

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

        $pdf = Pdf::loadView('HR/exports/headcount_trend_pdf', [
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

        $pdf = Pdf::loadView('HR/exports/headcount_breakdown_pdf', [
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
}
