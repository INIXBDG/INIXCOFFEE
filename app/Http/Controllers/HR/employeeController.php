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

            $baseQuery = karyawan::query()
                ->whereNot('jabatan', 'Outsource')
                ->where('kode_karyawan', 'NOT LIKE', 'OL%')
                ->whereNot('jabatan', 'Pilih Jabatan')
                ->whereNotNull('nip')
                ->whereNot('divisi', 'Direksi');
                
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

    public function getResignedEmployees(Request $request)
    {
        $totalStart = microtime(true);
        
        $draw = $request->input('draw', 1);
        $search = $request->input('search.value') ?? $request->input('search');
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        $baseQuery = karyawan::query()
            ->whereNot('jabatan', 'Outsource')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->whereNotNull('nip')
            ->whereNot('divisi', 'Direksi')
            ->where(function ($q) {
                $q->where('status_aktif', '0')->orWhereNotNull('resigned_at');
            });

        $recordsTotal = (clone $baseQuery)->count();
        $recordsFiltered = $recordsTotal;
        $query = clone $baseQuery;

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('nama_lengkap', 'LIKE', "%{$search}%")
                  ->orWhere('nip', 'LIKE', "%{$search}%")
                  ->orWhere('jabatan', 'LIKE', "%{$search}%")
                  ->orWhere('divisi', 'LIKE', "%{$search}%");
            });
            $recordsFiltered = (clone $query)->count();
        }

        $employees = $query->orderByDesc('resigned_at')
            ->offset($start)
            ->limit($length)
            ->get();

        $data = $employees->map(function ($emp) {
            return [
                'id' => $emp->id,
                'nama_lengkap' => $emp->nama_lengkap ?? $emp->nama ?? 'Tidak Diketahui',
                'nip' => $emp->nip ?? '-',
                'jabatan' => $emp->jabatan ?? '-',
                'divisi' => $emp->divisi ?? '-',
                'resigned_at' => $emp->resigned_at ? Carbon::parse($emp->resigned_at)->format('d M Y') : '-',
                'resigned_at_raw' => $emp->resigned_at ? Carbon::parse($emp->resigned_at)->format('Y-m-d') : '',
                'alasan_resign' => $emp->alasan_resign ?: '-',
            ];
        })->toArray();

        Log::info('Waktu Proses DataTables Resigned: ' . (microtime(true) - $totalStart) . 's');

        return response()->json([
            'draw' => intval($draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
            'total_resign' => $recordsTotal,
        ], 200);
    }

    public function updateResignData(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'resigned_at' => 'required|date',
                'alasan_resign' => 'nullable|string|max:500',
            ]);

            $employee = karyawan::findOrFail($id);
            $employee->resigned_at = $validated['resigned_at'];
            $employee->alasan_resign = $validated['alasan_resign'] ?? null;
            $employee->save();

            return response()->json([
                'message' => 'Data resign berhasil diperbarui',
                'data' => [
                    'id' => $employee->id,
                    'resigned_at' => Carbon::parse($employee->resigned_at)->format('d M Y'),
                    'alasan_resign' => $employee->alasan_resign ?: '-',
                ],
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Karyawan tidak ditemukan'], 404);
        } catch (\Exception $e) {
            Log::error('HR Update Resign Data Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal memperbarui data',
                'message' => config('app.debug') ? $e->getMessage() : 'Silakan coba beberapa saat lagi',
            ], 500);
        }
    }

    public function moveToResign(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'resigned_at' => 'required|date',
                'alasan_resign' => 'nullable|string|max:500',
            ]);

            $employee = karyawan::findOrFail($id);
            $employee->status_aktif = '0';
            $employee->resigned_at = $validated['resigned_at'];
            $employee->alasan_resign = $validated['alasan_resign'] ?? null;
            $employee->save();

            return response()->json([
                'message' => 'Karyawan berhasil dipindahkan ke status resign',
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Karyawan tidak ditemukan'], 404);
        } catch (\Exception $e) {
            Log::error('HR Move To Resign Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal memindahkan data',
                'message' => config('app.debug') ? $e->getMessage() : 'Silakan coba beberapa saat lagi',
            ], 500);
        }
    }

    public function restoreEmployee($id)
    {
        try {
            $employee = karyawan::findOrFail($id);
            $employee->status_aktif = '1';
            $employee->resigned_at = null;
            $employee->alasan_resign = null;
            $employee->save();

            return response()->json([
                'message' => 'Karyawan berhasil dipulihkan menjadi aktif',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Karyawan tidak ditemukan'], 404);
        } catch (\Exception $e) {
            Log::error('HR Restore Employee Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal memulihkan data',
                'message' => config('app.debug') ? $e->getMessage() : 'Silakan coba beberapa saat lagi',
            ], 500);
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

        $baseQuery = karyawan::query()
            ->whereNot('jabatan', 'Outsource')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->whereNotNull('nip')
            ->whereNot('divisi', 'Direksi');

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
                'datasets' => [
                    ['label' => 'Active', 'data' => $activeData, 'borderColor' => '#198754', 'backgroundColor' => 'rgba(25,135,84,0.1)', 'fill' => true], 
                    ['label' => 'New Hire', 'data' => $newData, 'borderColor' => '#0d6efd', 'backgroundColor' => 'rgba(13,110,253,0.1)', 'fill' => true], 
                    ['label' => 'Resign', 'data' => $resignData, 'borderColor' => '#dc3545', 'backgroundColor' => 'rgba(220,53,69,0.1)', 'fill' => true]
                ],
                'summary' => [
                    'total_active' => array_sum($activeData),
                    'total_new' => array_sum($newData),
                    'total_resign' => array_sum($resignData),
                    'avg_monthly_new' => count($newData) > 0 ? round(array_sum($newData) / count($newData), 1) : 0,
                    'avg_monthly_resign' => count($resignData) > 0 ? round(array_sum($resignData) / count($resignData), 1) : 0,
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

            $baseQuery = karyawan::query()
                ->whereNot('jabatan', 'Outsource')
                ->where('kode_karyawan', 'NOT LIKE', 'OL%')
                ->whereNot('jabatan', 'Pilih Jabatan')
                ->whereNotNull('nip')
                ->whereNot('divisi', 'Direksi');

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
                    "$field as label,
                    COUNT(*) as total,
                    SUM(CASE WHEN status_aktif = '1' THEN 1 ELSE 0 END) as active_count,
                    SUM(CASE WHEN status_aktif = '0' THEN 1 ELSE 0 END) as resign_count"
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
        $totalStart = microtime(true);
        
        $draw = $request->input('draw', 1);
        $category = $request->input('category', 'all');
        $search = $request->input('search.value') ?? $request->input('search');
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $periode = $request->input('periode', 'all');
        $year = $request->input('year');
        
        $dateRange = $periode !== 'all' ? $this->calculateDateRange($periode, $year) : null;

        $baseQuery = karyawan::query()
            ->whereNot('jabatan', 'Outsource')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->whereNotNull('nip')
            ->whereNot('divisi', 'Direksi');

        if ($category === 'active') {
            $baseQuery->where('status_aktif', '1');
        } elseif ($category === 'new') {
            $baseQuery->where('status_aktif', '1');
            if ($dateRange) {
                $baseQuery->whereBetween('awal_probation', [$dateRange['start'], $dateRange['end']]);
            } else {
                $baseQuery->whereNotNull('awal_probation');
            }
        } elseif ($category === 'resign') {
            $baseQuery->where('status_aktif', '0');
            if ($dateRange) {
                $baseQuery->whereBetween('resigned_at', [$dateRange['start'], $dateRange['end']]);
            } else {
                $baseQuery->whereNotNull('resigned_at');
            }
        }

        $recordsTotal = (clone $baseQuery)->count();
        $recordsFiltered = $recordsTotal;
        $query = clone $baseQuery;

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'LIKE', "%{$search}%")
                  ->orWhere('nama', 'LIKE', "%{$search}%")
                  ->orWhere('nip', 'LIKE', "%{$search}%")
                  ->orWhere('jabatan', 'LIKE', "%{$search}%")
                  ->orWhere('divisi', 'LIKE', "%{$search}%");
            });
            $recordsFiltered = (clone $query)->count();
        }

        $employees = $query->orderBy('nama_lengkap', 'asc')
            ->offset($start)
            ->limit($length)
            ->get();

        $data = $employees->map(function ($emp) {
            $namaDepan = explode(' ', trim($emp->nama_lengkap ?? $emp->nama ?? ''))[0] ?? 'Tidak Diketahui';
            return [
                'id' => $emp->id,
                'nama' => $namaDepan,
                'nama_lengkap' => $emp->nama_lengkap ?? $emp->nama ?? '-',
                'nip' => $emp->nip ?? '-',
                'jabatan' => $emp->jabatan ?? '-',
                'divisi' => $emp->divisi ?? '-',
                'tanggal_join' => $this->formatJoinDate($emp),
                'status' => $emp->status_aktif == '1' ? 'Aktif' : 'Resign',
                'resigned_at' => $emp->resigned_at ? Carbon::parse($emp->resigned_at)->format('d M Y') : null,
            ];
        })->toArray();

        Log::info('Waktu Proses DataTables Category: ' . (microtime(true) - $totalStart) . 's');

        return response()->json(
            [
                'draw' => intval($draw),
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data,
                'pagination' => [
                    'current_page' => floor($start / $length) + 1,
                    'per_page' => $length,
                    'total' => $recordsFiltered,
                    'last_page' => ceil($recordsFiltered / $length),
                ],
                'filters' => [
                    'periode' => $periode,
                    'year' => $year,
                    'search' => $search,
                ],
            ],
            200,
        );
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
        $rate = $total > 0 ? round(($active / $total) * 100, 1) : 100.0;
        $turnoverRate = round(100 - $rate, 1);
        
        // Penentuan Status & Level Risiko
        if ($rate >= 90) {
            $status = 'excellent';
            $riskLevel = 'low';
        } elseif ($rate >= 75) {
            $status = 'good';
            $riskLevel = 'moderate';
        } elseif ($rate >= 60) {
            $status = 'moderate';
            $riskLevel = 'high';
        } else {
            $status = 'needs_attention';
            $riskLevel = 'critical';
        }

        // 1. Analisis Peluang (Dinamis berdasarkan kondisi data)
        $opportunities = [];
        
        if ($resign > 0) {
            $opportunities[] = [
                'icon' => 'fa-magnifying-glass-chart',
                'title' => 'Analisis Akar Penyebab (Root Cause)',
                'desc' => 'Lakukan exit interview terstruktur dan kategorikan alasan resign (kompensasi, atasan, karir) untuk menemukan pola.'
            ];
            $opportunities[] = [
                'icon' => 'fa-user-clock',
                'title' => 'Evaluasi Masa Kritis Karyawan Baru',
                'desc' => 'Cek apakah resign terjadi di bawah 6 bulan. Jika ya, perbaiki proses onboarding dan ekspektasi pekerjaan.'
            ];
        }

        if ($turnoverRate > 15) {
            $opportunities[] = [
                'icon' => 'fa-scale-unbalanced',
                'title' => 'Audit Kompensasi & Benefit',
                'desc' => 'Bandingkan struktur gaji dan benefit dengan rata-rata industri. Ketertinggalan kompetitif adalah pemicu utama turnover.'
            ];
            $opportunities[] = [
                'icon' => 'fa-route',
                'title' => 'Pemetaan Jalur Karir (Career Pathing)',
                'desc' => 'Karyawan sering resign karena merasa stagnan. Buat peta karir yang jelas untuk 1-3 tahun ke depan.'
            ];
        }

        if ($rate >= 90) {
            $opportunities[] = [
                'icon' => 'fa-shield-halved',
                'title' => 'Pencegahan Komplaisensi (Complacency)',
                'desc' => 'Pertahankan momentum dengan program employee engagement lanjutan dan identifikasi "High Potential Talent" untuk dipertahankan.'
            ];
        }

        // 2. Rekomendasi Strategis (Dikelompokkan agar masif dan terstruktur)
        $recommendations = $this->getStrategicRecommendations($status, $active, $resign, $total);

        // 3. Proyeksi Cerdas (Memperhitungkan volume data untuk confidence level)
        $dataConfidence = $total >= 50 ? 'high' : ($total >= 20 ? 'medium' : 'low');
        $turnoverTrend = $turnoverRate > 15 ? 1.05 : 0.98; // Faktor penyesuaian berdasarkan risiko

        $projections = [
            'next_quarter' => [
                'period' => 'Kuartal Depan',
                'estimated_active' => max(0, $active - round($resign * 0.25 * $turnoverTrend)), 
                'estimated_resign' => max(0, round($resign * 0.25 * $turnoverTrend)),
                'confidence' => $dataConfidence,
                'action_required' => $turnoverRate > 15 ? 'Intervensi Segera' : 'Monitoring Rutin'
            ],
            'next_year' => [
                'period' => 'Tahun Depan',
                'estimated_active' => max(0, round($active * 0.95)), 
                'estimated_resign' => max(0, round($total * 0.05 * $turnoverTrend)),
                'confidence' => $dataConfidence === 'high' ? 'medium' : 'low',
                'action_required' => $turnoverRate > 15 ? 'Restrukturisasi Strategi HR' : 'Pemeliharaan Budaya Kerja'
            ]
        ];

        return [
            'metrics' => [
                'retention_rate' => $rate,
                'turnover_rate' => $turnoverRate,
                'risk_level' => $riskLevel,
            ],
            'status' => $status,
            'status_label' => [
                'excellent' => 'Sangat Baik (Stabil)', 
                'good' => 'Baik (Perlu Pemeliharaan)', 
                'moderate' => 'Cukup (Waspada)', 
                'needs_attention' => 'Kritis (Perlu Intervensi Segera)'
            ][$status],
            'opportunities' => $opportunities,
            'recommendations' => $recommendations,
            'projections' => $projections,
        ];
    }

    private function getStrategicRecommendations($status, $active, $resign, $total)
    {
        // Struktur array multidimensi untuk tampilan UI yang lebih kaya (misal: Accordion/Tabs)
        $recommendations = [
            'retensi_kompensasi' => [
                'title' => 'Retensi & Kompensasi',
                'icon' => 'fa-coins',
                'color' => 'var(--warning)',
                'items' => []
            ],
            'budaya_engagement' => [
                'title' => 'Budaya & Employee Engagement',
                'icon' => 'fa-people-group',
                'color' => 'var(--pri)',
                'items' => []
            ],
            'pengembangan_karir' => [
                'title' => 'Pengembangan & Karir',
                'icon' => 'fa-chart-line',
                'color' => 'var(--success)',
                'items' => []
            ],
            'manajemen_kepemimpinan' => [
                'title' => 'Manajemen & Kepemimpinan',
                'icon' => 'fa-user-tie',
                'color' => 'var(--info)',
                'items' => []
            ]
        ];

        // Logika pengisian rekomendasi berdasarkan status
        if ($status === 'needs_attention') {
            $recommendations['retensi_kompensasi']['items'][] = 'Lakukan audit gaji mendesak (salary benchmarking) terhadap pasar industri.';
            $recommendations['retensi_kompensasi']['items'][] = 'Evaluasi ulang paket benefit non-tunai (asuransi, bonus, cuti).';
            $recommendations['budaya_engagement']['items'][] = 'Adakan "Stay Interview" dengan karyawan kunci (key persons) sebelum mereka memutuskan resign.';
            $recommendations['pengembangan_karir']['items'][] = 'Identifikasi 10-20% karyawan berkinerja tinggi dan buat rencana retensi khusus.';
            $recommendations['manajemen_kepemimpinan']['items'][] = 'Evaluasi gaya manajemen di divisi dengan tingkat resign tertinggi (toxic leadership check).';
        } 
        elseif ($status === 'moderate') {
            $recommendations['retensi_kompensasi']['items'][] = 'Tinjau ulang struktur kenaikan gaji tahunan agar lebih kompetitif.';
            $recommendations['budaya_engagement']['items'][] = 'Implementasikan program pengakuan karyawan (Employee Recognition Program) bulanan.';
            $recommendations['pengembangan_karir']['items'][] = 'Buat program mentoring formal antara senior dan karyawan baru (0-1 tahun).';
            $recommendations['manajemen_kepemimpinan']['items'][] = 'Berikan pelatihan "People Management" untuk para Supervisor/Manager.';
        } 
        else { // excellent atau good
            $recommendations['retensi_kompensasi']['items'][] = 'Pertahankan daya saing kompensasi dengan review pasar tahunan.';
            $recommendations['budaya_engagement']['items'][] = 'Kembangkan program Employer Branding untuk menarik talenta terbaik dari luar.';
            $recommendations['pengembangan_karir']['items'][] = 'Bangun program "Succession Planning" untuk posisi-posisi kritis.';
            $recommendations['manajemen_kepemimpinan']['items'][] = 'Dorong inovasi dan otonomi kerja untuk menjaga motivasi karyawan senior.';
        }

        $recommendations['budaya_engagement']['items'][] = 'Pastikan saluran komunikasi dua arah (feedback) antara staf dan manajemen tetap terbuka.';
        $recommendations['pengembangan_karir']['items'][] = 'Sediakan akses pelatihan/kursus online untuk peningkatan skill (upskilling).';

        return $recommendations;
    }

    private function formatJoinDate($emp)
    {
        $date = $emp->awal_probation ?? ($emp->awal_kontrak ?? $emp->awal_tetap);
        return $date ? Carbon::parse($date)->format('d M Y') : '-';
    }
}