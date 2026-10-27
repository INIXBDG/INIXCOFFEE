<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\karyawan;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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

            $baseQuery = $this->baseKaryawanQuery();
            $this->applySearch($baseQuery, $search);

            $totalEmployees = (clone $baseQuery)->count();
            $active = $this->applyCategoryFilter(clone $baseQuery, 'active', $dateRange)->count();
            $new    = $this->applyCategoryFilter(clone $baseQuery, 'new', $dateRange)->count();
            $resign = $this->applyCategoryFilter(clone $baseQuery, 'resign', $dateRange)->count();

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
        $page   = max(1, (int) $request->input('page', 1));
        $length = max(1, (int) $request->input('length', 10));
        $start  = $request->has('start') ? (int) $request->input('start') : ($page - 1) * $length;

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

        $employees = $query->orderByDesc('resigned_at')->offset($start)->limit($length)->get();

        $data = $employees
            ->map(function ($emp) {
                return [
                    'id' => $emp->id,
                    'nama_lengkap' => $emp->nama_lengkap ?? ($emp->nama ?? 'Tidak Diketahui'),
                    'nip' => $emp->nip ?? '-',
                    'jabatan' => $emp->jabatan ?? '-',
                    'divisi' => $emp->divisi ?? '-',
                    'resigned_at' => $emp->resigned_at ? Carbon::parse($emp->resigned_at)->format('d M Y') : '-',
                    'resigned_at_raw' => $emp->resigned_at ? Carbon::parse($emp->resigned_at)->format('Y-m-d') : '',
                    'alasan_resign' => $emp->alasan_resign ?: '-',
                ];
            })
            ->toArray();

        return response()->json([
            'draw' => intval($draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
            'total_resign' => $recordsTotal,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $length,
                'total' => $recordsFiltered,
                'last_page' => (int) ceil($recordsFiltered / $length) ?: 1,
            ],
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

            return response()->json(
                [
                    'message' => 'Data resign berhasil diperbarui',
                    'data' => [
                        'id' => $employee->id,
                        'resigned_at' => Carbon::parse($employee->resigned_at)->format('d M Y'),
                        'alasan_resign' => $employee->alasan_resign ?: '-',
                    ],
                ],
                200,
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(
                [
                    'error' => 'Validasi gagal',
                    'errors' => $e->errors(),
                ],
                422,
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Karyawan tidak ditemukan'], 404);
        } catch (\Exception $e) {
            Log::error('HR Update Resign Data Error: ' . $e->getMessage());

            return response()->json(
                [
                    'error' => 'Gagal memperbarui data',
                    'message' => config('app.debug') ? $e->getMessage() : 'Silakan coba beberapa saat lagi',
                ],
                500,
            );
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
            Cache::forget('office_karyawan_aktif'); // Invalidate cache

            return response()->json(
                [
                    'message' => 'Karyawan berhasil dipindahkan ke status resign',
                ],
                200,
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(
                [
                    'error' => 'Validasi gagal',
                    'errors' => $e->errors(),
                ],
                422,
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Karyawan tidak ditemukan'], 404);
        } catch (\Exception $e) {
            Log::error('HR Move To Resign Error: ' . $e->getMessage());

            return response()->json(
                [
                    'error' => 'Gagal memindahkan data',
                    'message' => config('app.debug') ? $e->getMessage() : 'Silakan coba beberapa saat lagi',
                ],
                500,
            );
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
            Cache::forget('office_karyawan_aktif'); // Invalidate cache

            return response()->json(
                [
                    'message' => 'Karyawan berhasil dipulihkan menjadi aktif',
                ],
                200,
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Karyawan tidak ditemukan'], 404);
        } catch (\Exception $e) {
            Log::error('HR Restore Employee Error: ' . $e->getMessage());

            return response()->json(
                [
                    'error' => 'Gagal memulihkan data',
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

        $baseQuery = karyawan::query()->whereNot('jabatan', 'Outsource')->where('kode_karyawan', 'NOT LIKE', 'OL%')->whereNot('jabatan', 'Pilih Jabatan')->whereNotNull('nip')->whereNot('divisi', 'Direksi');

        $labels = [];
        $activeData = [];
        $newData = [];
        $resignData = [];

        $periods = $this->generatePeriods($startDate, $endDate, $groupBy);

        foreach ($periods as $period) {
            $labels[] = $period['label'];
            $activeData[] = $this->applyTrendDataset(clone $baseQuery, 'Active', $period, $groupBy)->count();
            $newData[]    = $this->applyTrendDataset(clone $baseQuery, 'New Hire', $period, $groupBy)->count();
            $resignData[] = $this->applyTrendDataset(clone $baseQuery, 'Resign', $period, $groupBy)->count();
        }

        return response()->json(
            [
                'labels' => $labels,
                'datasets' => [['label' => 'Active', 'data' => $activeData, 'borderColor' => '#198754', 'backgroundColor' => 'rgba(25,135,84,0.1)', 'fill' => true], ['label' => 'New Hire', 'data' => $newData, 'borderColor' => '#0d6efd', 'backgroundColor' => 'rgba(13,110,253,0.1)', 'fill' => true], ['label' => 'Resign', 'data' => $resignData, 'borderColor' => '#dc3545', 'backgroundColor' => 'rgba(220,53,69,0.1)', 'fill' => true]],
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
                'filter_by' => 'nullable|in:divisi,jabatan,gender', // Hapus location jika tidak ada di DB
                'status' => 'nullable|in:active,resign,all',
                'min_tenure' => 'nullable|integer|min:0',
            ]);

            $filterBy = $validated['filter_by'] ?? 'divisi';
            $status = $validated['status'] ?? 'all';
            $minTenure = $validated['min_tenure'] ?? 0;

            $baseQuery = karyawan::query()->whereNot('jabatan', 'Outsource')->where('kode_karyawan', 'NOT LIKE', 'OL%')->whereNot('jabatan', 'Pilih Jabatan')->whereNotNull('nip')->whereNot('divisi', 'Direksi');

            // Filter Status Global untuk Breakdown Chart
            $this->applyBreakdownFilters($baseQuery, $status, $minTenure);

            // Mapping field frontend ke database
            $fieldMap = [
                'divisi' => 'divisi',
                'jabatan' => 'jabatan',
                'gender' => 'gender',
            ];
            $field = $fieldMap[$filterBy] ?? 'divisi';

            // Ambil Data Grouping
            $breakdown = $baseQuery
                ->selectRaw(
                    "$field as label,
                    COUNT(*) as total,
                    SUM(CASE WHEN status_aktif = '1' THEN 1 ELSE 0 END) as active_count,
                    SUM(CASE WHEN status_aktif = '0' OR resigned_at IS NOT NULL THEN 1 ELSE 0 END) as resign_count",
                )
                ->groupBy($field)
                ->orderByDesc('total')
                ->limit(15) // Batasi agar chart tidak penuh
                ->get()
                ->map(function ($item) {
                    $percentage = $item->total > 0 ? round(($item->active_count / $item->total) * 100, 1) : 0;
                    return [
                        'label' => $item->label ?: 'Tidak Diketahui',
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
                        'top_category' => $breakdown->first()['label'] ?? '-',
                        'top_category' => $breakdown->first()?->label ?? '-',
                        'avg_retention' => $breakdown->avg('retention') ? round($breakdown->avg('retention'), 1) : 0,
                    ],
                ],
                200,
            );
        } catch (\Exception $e) {
            Log::error('Headcount Breakdown Error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal memuat breakdown data', 'message' => $e->getMessage()], 500);
        }
    }

    public function getChartDetailEmployees(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:trend,breakdown',
            'label' => 'required|string',
            'dataset' => 'required|string',
            'filter_by' => 'nullable|string',
            'group_by' => 'nullable|in:month,quarter,year',
            'status' => 'nullable|in:active,resign,all',
            'min_tenure' => 'nullable|integer|min:0',
            'page' => 'nullable|integer|min:1',
            'length' => 'nullable|integer|min:1|max:10000',
        ]);

        $type = $validated['type'];
        $label = $validated['label'];
        $dataset = $validated['dataset'];
        $page = $validated['page'] ?? 1;
        $length = $validated['length'] ?? 10;
        $start = ($page - 1) * $length;

        $query = $this->baseKaryawanQuery();
        $useResignDate = false;
        $groupBy = $validated['group_by'] ?? 'month';
        $col = in_array($validated['filter_by'] ?? '', ['divisi', 'jabatan', 'gender'], true) ? $validated['filter_by'] : 'divisi';

        if ($type === 'trend') {
            $period = $this->resolvePeriodFromLabel($label, $groupBy);
            if (!$period) {
                return response()->json(['error' => 'Format periode tidak valid'], 400);
            }
            $this->applyTrendDataset($query, $dataset, $period, $groupBy);
            $useResignDate = $dataset === 'Resign';
        } else {
            $this->applyBreakdownFilters($query, $validated['status'] ?? 'all', $validated['min_tenure'] ?? 0);

            if ($label === 'Tidak Diketahui') {
                $query->where(function ($q) use ($col) {
                    $q->whereNull($col)->orWhere($col, '');
                });
            } else {
                $query->where($col, $label);
            }

            if ($dataset === 'Active') {
                $query->where('status_aktif', '1');
            } elseif ($dataset === 'Resign') {
                $query->where(function ($q) {
                    $q->where('status_aktif', '0')->orWhereNotNull('resigned_at');
                });
            } // 'Total' = semua di kategori tsb (sesudah filter)
        }

        $total = (clone $query)->count();

        $data = $query->orderBy('nama_lengkap', 'asc')->offset($start)->limit($length)->get()
            ->map(function ($emp) use ($useResignDate) {
                $date = $useResignDate ? $emp->resigned_at : ($emp->awal_probation ?? $emp->awal_kontrak ?? $emp->awal_tetap);
                return [
                    'nama' => $emp->nama_lengkap ?? $emp->nama,
                    'nip' => $emp->nip,
                    'jabatan' => $emp->jabatan,
                    'divisi' => $emp->divisi,
                    'tanggal' => $date ? Carbon::parse($date)->format('d M Y') : '-',
                    'status' => $emp->status_aktif == '1' ? 'Aktif' : 'Resign',
                ];
            });

        return response()->json([
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'last_page' => (int) ceil($total / $length) ?: 1,
                'total' => $total,
                'per_page' => $length,
            ],
            'meta' => ['type' => $type, 'label' => $label, 'dataset' => $dataset, 'filter_by' => $col],
        ]);
    }

    public function exportChartDetailCsv(Request $request)
    {
        $request->merge(['page' => 1, 'length' => 10000]);
        $response = $this->getChartDetailEmployees($request);
        $payload = $response->original;
        if (!isset($payload['data'])) abort(400, 'Parameter tidak valid');

        $meta = $payload['meta'];
        $rows = collect($payload['data'])->map(fn ($r) => [$r['nama'], $r['nip'], $r['jabatan'], $r['divisi'], $r['tanggal'], $r['status']])->toArray();

        return $this->streamExcel("detail_{$meta['type']}_{$meta['label']}_{$meta['dataset']}_".date('Ymd'),
            ['Nama Lengkap', 'NIP', 'Jabatan', 'Divisi', 'Tanggal', 'Status'], $rows, [], "Detail {$meta['dataset']} - {$meta['label']}");
    }

    public function exportChartDetailPdf(Request $request)
    {
        $request->merge(['page' => 1, 'length' => 10000]);
        $response = $this->getChartDetailEmployees($request);
        $data = $response->original['data'];
        $meta = $response->original['meta'];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('HR/exports/chart_detail_pdf', [
            'data' => $data,
            'meta' => $meta,
            'generated_at' => \Carbon\Carbon::now()->format('d M Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream("detail_{$meta['type']}_{$meta['label']}_" . date('Ymd') . '.pdf');
    }

    public function getEmployeesByCategory(Request $request)
    {
        $category = $request->input('category', 'all');
        $search = $request->input('search');
        $page = $request->input('page', 1); // Terima parameter page
        $length = $request->input('length', 10);
        $periode = $request->input('periode', 'all');
        $year = $request->input('year');

        $start = ($page - 1) * $length; // Hitung offset dari page
        $dateRange = $periode !== 'all' ? $this->calculateDateRange($periode, $year) : null;

        $baseQuery = $this->baseKaryawanQuery();
        $this->applyCategoryFilter($baseQuery, $category, $dateRange);

        $recordsTotal = (clone $baseQuery)->count();
        $query = clone $baseQuery;
        $this->applySearch($query, $search);
        $recordsFiltered = (clone $query)->count();

        $employees = $query->orderBy('nama_lengkap', 'asc')->offset($start)->limit($length)->get();

        $data = $employees
            ->map(function ($emp) {
                $namaDepan = explode(' ', trim($emp->nama_lengkap ?? ($emp->nama ?? '')))[0] ?? 'Tidak Diketahui';
                return [
                    'id' => $emp->id,
                    'nama' => $namaDepan,
                    'nama_lengkap' => $emp->nama_lengkap ?? ($emp->nama ?? '-'),
                    'nip' => $emp->nip ?? '-',
                    'jabatan' => $emp->jabatan ?? '-',
                    'divisi' => $emp->divisi ?? '-',
                    'tanggal_join' => $this->formatJoinDate($emp),
                    'status' => $emp->status_aktif == '1' ? 'Aktif' : 'Resign',
                ];
            })
            ->toArray();

        $lastPage = ceil($recordsFiltered / $length) ?: 1;

        return response()->json(
            [
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $length,
                    'total' => $recordsFiltered,
                    'last_page' => $lastPage,
                ],
            ],
            200,
        );
    }

    public function exportEmployeesCsv(Request $request)
    {
        $rows = $this->getEmployeeQueryForExport($request)->get()->map(function ($emp) {
            $joinDate = $emp->awal_probation ?? ($emp->awal_kontrak ?? $emp->awal_tetap);
            return [
                $emp->nama_lengkap ?? $emp->nama, $emp->nip, $emp->jabatan, $emp->divisi,
                $joinDate ? Carbon::parse($joinDate)->format('d M Y') : '-',
                $emp->status_aktif == '1' ? 'Aktif' : 'Resign',
                $emp->resigned_at ? Carbon::parse($emp->resigned_at)->format('d M Y') : '-',
                $emp->alasan_resign ?: '-',
            ];
        })->toArray();

        return $this->streamExcel('data_karyawan_'.date('Ymd'),
            ['Nama Lengkap', 'NIP', 'Jabatan', 'Divisi', 'Tanggal Join', 'Status', 'Tanggal Resign', 'Alasan Resign'], $rows, [], 'Data Karyawan');
    }

    public function exportEmployeesPdf(Request $request)
    {
        $employees = $this->getEmployeeQueryForExport($request)
            ->get()
            ->map(function ($emp) {
                $joinDate = $emp->awal_probation ?? ($emp->awal_kontrak ?? $emp->awal_tetap);
                return [
                    'nama' => $emp->nama_lengkap ?? $emp->nama,
                    'nip' => $emp->nip,
                    'jabatan' => $emp->jabatan,
                    'divisi' => $emp->divisi,
                    'tanggal_join' => $joinDate ? \Carbon\Carbon::parse($joinDate)->format('d M Y') : '-',
                    'status' => $emp->status_aktif == '1' ? 'Aktif' : 'Resign',
                    'resigned_at' => $emp->resigned_at ? \Carbon\Carbon::parse($emp->resigned_at)->format('d M Y') : '-',
                    'alasan_resign' => $emp->alasan_resign ?: '-',
                ];
            });
        return \Barryvdh\DomPDF\Facade\Pdf::loadView('HR/exports/employees_pdf', ['employees' => $employees, 'generated_at' => \Carbon\Carbon::now()->format('d M Y H:i')])
            ->setPaper('a4', 'landscape')
            ->stream('data_karyawan_' . date('Ymd') . '.pdf');
    }

    public function exportResignedEmployeesCsv(Request $request)
    {
        $rows = $this->getResignedQueryForExport($request)->get()->map(function ($emp) {
            return [
                $emp->nama_lengkap ?? $emp->nama, $emp->nip, $emp->jabatan, $emp->divisi,
                $emp->resigned_at ? Carbon::parse($emp->resigned_at)->format('d M Y') : '-',
                $emp->alasan_resign ?: '-',
            ];
        })->toArray();

        return $this->streamExcel('data_karyawan_resign_'.date('Ymd'),
            ['Nama Lengkap', 'NIP', 'Jabatan', 'Divisi', 'Tanggal Resign', 'Alasan Resign'], $rows, [], 'Data Karyawan Resign');
    }

    public function exportResignedEmployeesPdf(Request $request)
    {
        $employees = $this->getResignedQueryForExport($request)
            ->get()
            ->map(function ($emp) {
                return [
                    'nama' => $emp->nama_lengkap ?? $emp->nama,
                    'nip' => $emp->nip,
                    'jabatan' => $emp->jabatan,
                    'divisi' => $emp->divisi,
                    'resigned_at' => $emp->resigned_at ? \Carbon\Carbon::parse($emp->resigned_at)->format('d M Y') : '-',
                    'alasan_resign' => $emp->alasan_resign ?: '-',
                ];
            });
        return \Barryvdh\DomPDF\Facade\Pdf::loadView('HR/exports/resigned_employees_pdf', ['employees' => $employees, 'generated_at' => \Carbon\Carbon::now()->format('d M Y H:i')])
            ->setPaper('a4', 'landscape')
            ->stream('data_karyawan_resign_' . date('Ymd') . '.pdf');
    }

    // Helper Queries untuk Export
    private function getEmployeeQueryForExport(Request $request)
    {
        $periode = $request->input('periode', 'all');
        $dateRange = $periode !== 'all' ? $this->calculateDateRange($periode, $request->input('year')) : null;

        $query = $this->baseKaryawanQuery();
        $this->applyCategoryFilter($query, $request->input('category', 'all'), $dateRange);
        $this->applySearch($query, $request->input('search'));

        return $query->orderBy('nama_lengkap', 'asc');
    }

    private function getResignedQueryForExport(Request $request)
    {
        $search = $request->input('search');
        $query = karyawan::query()
            ->whereNot('jabatan', 'Outsource')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->whereNotNull('nip')
            ->whereNot('divisi', 'Direksi')
            ->where(function ($q) {
                $q->where('status_aktif', '0')->orWhereNotNull('resigned_at');
            });

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'LIKE', "%{$search}%")
                    ->orWhere('nip', 'LIKE', "%{$search}%")
                    ->orWhere('jabatan', 'LIKE', "%{$search}%")
                    ->orWhere('divisi', 'LIKE', "%{$search}%");
            });
        }
        return $query->orderByDesc('resigned_at');
    }

    private function streamCsv($employees, $prefix, $headers, $callback)
    {
        $filename = $prefix . date('Ymd') . '.csv';
        return response()->stream(
            function () use ($employees, $headers, $callback) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $headers);
                foreach ($employees as $emp) {
                    fputcsv($file, $callback($emp));
                }
                fclose($file);
            },
            200,
            ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$filename\""],
        );
    }

    public function exportHeadcountTrendCsv(Request $request)
    {
        $data = $this->getHeadcountTrend($request)->original;
        $rows = [];
        foreach ($data['labels'] as $i => $label) {
            $rows[] = [$label, $data['datasets'][0]['data'][$i], $data['datasets'][1]['data'][$i], $data['datasets'][2]['data'][$i]];
        }
        $s = $data['summary'];

        return $this->streamExcel('headcount_trend_'.date('Ymd'), ['Periode', 'Active', 'New Hire', 'Resign'], $rows, [
            ['Total Active', $s['total_active']], ['Total New', $s['total_new']], ['Total Resign', $s['total_resign']],
            ['Avg Monthly New', $s['avg_monthly_new']], ['Avg Monthly Resign', $s['avg_monthly_resign']],
        ], 'Headcount Trend');
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
        $data = $this->getHeadcountBreakdown($request)->original;
        $rows = [];
        foreach ($data['breakdown'] as $item) {
            $rows[] = [$item['label'], $item['total'], $item['active'], $item['resign'], $item['retention']];
        }
        $s = $data['summary'];

        return $this->streamExcel('headcount_breakdown_'.date('Ymd'), ['Kategori', 'Total', 'Active', 'Resign', 'Retention Rate (%)'], $rows, [
            ['Total Categories', $s['total_categories']], ['Top Category', $s['top_category']], ['Avg Retention Rate', $s['avg_retention']],
        ], 'Headcount Breakdown');
    }

    public function exportHeadcountBreakdownPdf(Request $request)
    {
        $breakdownResponse = $this->getHeadcountBreakdown($request);
        $breakdownData = $breakdownResponse->original;

        $filterBy = $request->input('filter_by', 'divisi');

        $pdf = Pdf::loadView('HR/exports/headcount_breakdown_pdf', [
            'breakdown' => $breakdownData,
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
                if (!preg_match('/^[a-zA-Z0-9_\.]+$/', $field)) {
                    continue;
                }

                $q->orWhere(function ($sub) use ($period, $groupBy, $field) {
                    if ($groupBy === 'year') {
                        $sub->whereYear($field, $period['value']);
                    } elseif ($groupBy === 'quarter') {
                        // Perbaikan: Ganti {$field} dengan penggabungan string ( . $field . )
                        $sub->whereRaw('CONCAT(YEAR(' . $field . "), '-Q', QUARTER(" . $field . ')) = ?', [$period['value']]);
                    } else {
                        $dateParts = explode('-', $period['value']);

                        if (count($dateParts) === 2) {
                            $sub->whereYear($field, $dateParts[0])->whereMonth($field, $dateParts[1]);
                        } else {
                            // Perbaikan: Ganti {$field} dengan penggabungan string ( . $field . )
                            $sub->whereRaw('DATE_FORMAT(' . $field . ", '%Y-%m') = ?", [$period['value']]);
                        }
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
                'desc' => 'Lakukan exit interview terstruktur dan kategorikan alasan resign (kompensasi, atasan, karir) untuk menemukan pola.',
            ];
            $opportunities[] = [
                'icon' => 'fa-user-clock',
                'title' => 'Evaluasi Masa Kritis Karyawan Baru',
                'desc' => 'Cek apakah resign terjadi di bawah 6 bulan. Jika ya, perbaiki proses onboarding dan ekspektasi pekerjaan.',
            ];
        }

        if ($turnoverRate > 15) {
            $opportunities[] = [
                'icon' => 'fa-scale-unbalanced',
                'title' => 'Audit Kompensasi & Benefit',
                'desc' => 'Bandingkan struktur gaji dan benefit dengan rata-rata industri. Ketertinggalan kompetitif adalah pemicu utama turnover.',
            ];
            $opportunities[] = [
                'icon' => 'fa-route',
                'title' => 'Pemetaan Jalur Karir (Career Pathing)',
                'desc' => 'Karyawan sering resign karena merasa stagnan. Buat peta karir yang jelas untuk 1-3 tahun ke depan.',
            ];
        }

        if ($rate >= 90) {
            $opportunities[] = [
                'icon' => 'fa-shield-halved',
                'title' => 'Pencegahan Komplaisensi (Complacency)',
                'desc' => 'Pertahankan momentum dengan program employee engagement lanjutan dan identifikasi "High Potential Talent" untuk dipertahankan.',
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
                'action_required' => $turnoverRate > 15 ? 'Intervensi Segera' : 'Monitoring Rutin',
            ],
            'next_year' => [
                'period' => 'Tahun Depan',
                'estimated_active' => max(0, round($active * 0.95)),
                'estimated_resign' => max(0, round($total * 0.05 * $turnoverTrend)),
                'confidence' => $dataConfidence === 'high' ? 'medium' : 'low',
                'action_required' => $turnoverRate > 15 ? 'Restrukturisasi Strategi HR' : 'Pemeliharaan Budaya Kerja',
            ],
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
                'needs_attention' => 'Kritis (Perlu Intervensi Segera)',
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
                'items' => [],
            ],
            'budaya_engagement' => [
                'title' => 'Budaya & Employee Engagement',
                'icon' => 'fa-people-group',
                'color' => 'var(--pri)',
                'items' => [],
            ],
            'pengembangan_karir' => [
                'title' => 'Pengembangan & Karir',
                'icon' => 'fa-chart-line',
                'color' => 'var(--success)',
                'items' => [],
            ],
            'manajemen_kepemimpinan' => [
                'title' => 'Manajemen & Kepemimpinan',
                'icon' => 'fa-user-tie',
                'color' => 'var(--info)',
                'items' => [],
            ],
        ];

        // Logika pengisian rekomendasi berdasarkan status
        if ($status === 'needs_attention') {
            $recommendations['retensi_kompensasi']['items'][] = 'Lakukan audit gaji mendesak (salary benchmarking) terhadap pasar industri.';
            $recommendations['retensi_kompensasi']['items'][] = 'Evaluasi ulang paket benefit non-tunai (asuransi, bonus, cuti).';
            $recommendations['budaya_engagement']['items'][] = 'Adakan "Stay Interview" dengan karyawan kunci (key persons) sebelum mereka memutuskan resign.';
            $recommendations['pengembangan_karir']['items'][] = 'Identifikasi 10-20% karyawan berkinerja tinggi dan buat rencana retensi khusus.';
            $recommendations['manajemen_kepemimpinan']['items'][] = 'Evaluasi gaya manajemen di divisi dengan tingkat resign tertinggi (toxic leadership check).';
        } elseif ($status === 'moderate') {
            $recommendations['retensi_kompensasi']['items'][] = 'Tinjau ulang struktur kenaikan gaji tahunan agar lebih kompetitif.';
            $recommendations['budaya_engagement']['items'][] = 'Implementasikan program pengakuan karyawan (Employee Recognition Program) bulanan.';
            $recommendations['pengembangan_karir']['items'][] = 'Buat program mentoring formal antara senior dan karyawan baru (0-1 tahun).';
            $recommendations['manajemen_kepemimpinan']['items'][] = 'Berikan pelatihan "People Management" untuk para Supervisor/Manager.';
        } else {
            // excellent atau good
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

    private function baseKaryawanQuery()
    {
        return karyawan::query()->whereNot('jabatan', 'Outsource')->where('kode_karyawan', 'NOT LIKE', 'OL%')->whereNot('jabatan', 'Pilih Jabatan')->whereNotNull('nip')->whereNot('divisi', 'Direksi');
    }

    private function applySearch($query, $search)
    {
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'LIKE', "%{$search}%")
                    ->orWhere('nama', 'LIKE', "%{$search}%")
                    ->orWhere('nip', 'LIKE', "%{$search}%")
                    ->orWhere('jabatan', 'LIKE', "%{$search}%")
                    ->orWhere('divisi', 'LIKE', "%{$search}%");
            });
        }
        return $query;
    }

    /** Satu-satunya definisi Active / Baru / Resign (dipakai kartu, daftar, dan export) */
    private function applyCategoryFilter($query, $category, $dateRange)
    {
        if ($category === 'active') {
            $query->where('status_aktif', '1');
            if ($dateRange) {
                $query->where(function ($q) use ($dateRange) {
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
        } elseif ($category === 'new') {
            $query->where('status_aktif', '1');
            $dateRange ? $query->whereBetween('awal_probation', [$dateRange['start'], $dateRange['end']]) : $query->whereNotNull('awal_probation');
        } elseif ($category === 'resign') {
            $query->whereNotNull('resigned_at');
            if ($dateRange) {
                $query->whereBetween('resigned_at', [$dateRange['start'], $dateRange['end']]);
            }
        }
        return $query;
    }

    private function applyBreakdownFilters($query, $status, $minTenure)
    {
        if ($status === 'active') {
            $query->where('status_aktif', '1');
        } elseif ($status === 'resign') {
            $query->where(function ($q) {
                $q->where('status_aktif', '0')->orWhereNotNull('resigned_at');
            });
        }
        if ((int) $minTenure > 0) {
            $query->whereRaw('TIMESTAMPDIFF(MONTH, COALESCE(awal_probation, awal_kontrak, awal_tetap), CURDATE()) >= ?', [(int) $minTenure]);
        }
        return $query;
    }

    private function resignPeriodSql($groupBy)
    {
        return match ($groupBy) {
            'year' => 'YEAR(resigned_at) = ?',
            'quarter' => 'CONCAT(YEAR(resigned_at), "-Q", QUARTER(resigned_at)) = ?',
            default => 'DATE_FORMAT(resigned_at, "%Y-%m") = ?',
        };
    }

    /** Satu-satunya definisi titik di grafik trend (dipakai grafik DAN detail) */
    private function applyTrendDataset($query, $dataset, $period, $groupBy)
    {
        if ($dataset === 'Resign') {
            $query->where('status_aktif', '0')->whereRaw($this->resignPeriodSql($groupBy), [$period['value']]);
        } elseif ($dataset === 'New Hire') {
            $query->where('status_aktif', '1');
            $this->applyDateFilter($query, $period, $groupBy, ['awal_probation']);
        } else {
            // Active / Total
            $query->where('status_aktif', '1');
            $this->applyDateFilter($query, $period, $groupBy, ['awal_probation', 'awal_kontrak', 'awal_tetap']);
        }
        return $query;
    }

    /** Ubah label grafik ("Sep 2026", "Q3 2026", "2026") kembali jadi periode */
    private function resolvePeriodFromLabel($label, $groupBy)
    {
        if ($groupBy === 'year') {
            return preg_match('/^\d{4}$/', $label) ? ['value' => (int) $label] : null;
        }
        if ($groupBy === 'quarter') {
            return preg_match('/^Q([1-4]) (\d{4})$/', $label, $m) ? ['value' => $m[2] . '-Q' . $m[1]] : null;
        }
        try {
            return ['value' => Carbon::createFromFormat('!M Y', $label)->format('Y-m')];
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function streamExcel($filename, array $headers, $rows, array $summary = [], $title = null)
    {
        $filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $filename) . '.xls';

        return response()->stream(
            function () use ($headers, $rows, $summary, $title) {
                echo "\xEF\xBB\xBF";
                echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel"><head><meta charset="UTF-8"></head><body>';
                if ($title) {
                    echo '<h3>' . e($title) . '</h3>';
                }
                echo '<table border="1"><thead><tr>';
                foreach ($headers as $h) {
                    echo '<th style="background:#4f46e5;color:#ffffff">' . e($h) . '</th>';
                }
                echo '</tr></thead><tbody>';
                foreach ($rows as $row) {
                    echo '<tr>';
                    foreach ($row as $cell) {
                        $style = is_int($cell) || is_float($cell) ? '' : ' style="mso-number-format:\@"'; // NIP tetap teks
                        echo '<td' . $style . '>' . e($cell) . '</td>';
                    }
                    echo '</tr>';
                }
                echo '</tbody></table>';
                if ($summary) {
                    echo '<br><table border="1"><tr><th colspan="2">Summary</th></tr>';
                    foreach ($summary as $s) {
                        echo '<tr><td>' . e($s[0]) . '</td><td>' . e($s[1]) . '</td></tr>';
                    }
                    echo '</table>';
                }
                echo '</body></html>';
            },
            200,
            [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ],
        );
    }

    private function safeDate($value)
    {
        if (!$value) {
            return null;
        }
        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function currentPhase($emp)
    {
        if ($emp->awal_tetap) {
            return ['key' => 'tetap', 'label' => 'Karyawan Tetap', 'end' => $this->safeDate($emp->akhir_tetap), 'action' => 'Tinjau masa berlaku status karyawan tetap'];
        }
        if ($emp->awal_kontrak) {
            return ['key' => 'kontrak', 'label' => 'Kontrak', 'end' => $this->safeDate($emp->akhir_kontrak), 'action' => 'Buat penilaian kontrak (perpanjang kontrak atau pengangkatan karyawan tetap)'];
        }
        if ($emp->awal_probation) {
            return ['key' => 'probation', 'label' => 'Probation', 'end' => $this->safeDate($emp->akhir_probation), 'action' => 'Buat penilaian probation untuk keputusan kontrak'];
        }
        return null;
    }

    public function getEmployeeReminders(Request $request)
    {
        try {
            $window = (int) $request->input('window', 30);
            $window = in_array($window, [14, 30, 60, 90], true) ? $window : 30;
            $today = Carbon::today();

            $employees = $this->baseKaryawanQuery()->where('status_aktif', '1')->whereNull('resigned_at')->get();

            $items = [];
            foreach ($employees as $emp) {
                $phase = $this->currentPhase($emp);
                if (!$phase || !$phase['end']) {
                    continue;
                }

                $daysLeft = (int) $today->diffInDays($phase['end'], false);

                if ($daysLeft > $window) {
                    continue;
                }

                if ($phase['end']->copy()->addMonth()->lt($today)) {
                    continue;
                }

                $level = $daysLeft < 0 ? 'overdue' : ($daysLeft <= 7 ? 'urgent' : 'upcoming');
                $assessStart = $phase['end']->copy()->subMonth();

                $items[] = [
                    'id' => $emp->id,
                    'nama' => $emp->nama_lengkap,
                    'nip' => $emp->nip,
                    'jabatan' => $emp->jabatan ?? '-',
                    'divisi' => $emp->divisi ?? '-',
                    'foto' => $emp->foto,
                    'fase' => $phase['key'],
                    'fase_label' => $phase['label'],
                    'tanggal_berakhir' => $phase['end']->format('d M Y'),
                    'mulai_penilaian' => $assessStart->format('d M Y'),
                    'days_left' => $daysLeft,
                    'level' => $level,
                    'tindakan' => $phase['action'],
                ];
            }

            usort($items, fn($a, $b) => $a['days_left'] <=> $b['days_left']);
            $collection = collect($items);

            return response()->json([
                'success' => true,
                'window' => $window,
                'summary' => [
                    'total' => $collection->count(),
                    'overdue' => $collection->where('level', 'overdue')->count(),
                    'urgent' => $collection->where('level', 'urgent')->count(),
                    'upcoming' => $collection->where('level', 'upcoming')->count(),
                    'probation' => $collection->where('fase', 'probation')->count(),
                    'kontrak' => $collection->where('fase', 'kontrak')->count(),
                    'tetap' => $collection->where('fase', 'tetap')->count(),
                ],
                'data' => $items,
            ]);
        } catch (\Exception $e) {
            Log::error('HR Reminder Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memuat reminder'], 500);
        }
    }

    public function getEmployeeDetail($id)
    {
        try {
            $e = $this->baseKaryawanQuery()->findOrFail($id);
            $fmt = fn($v) => ($d = $this->safeDate($v)) ? $d->format('d M Y') : '-';
            $phase = $this->currentPhase($e);

            $firstDate = $this->safeDate($e->awal_probation ?? ($e->awal_kontrak ?? $e->awal_tetap));
            $tenure = '-';
            if ($firstDate) {
                $end = $this->safeDate($e->resigned_at) ?: Carbon::today();
                $diff = $firstDate->diff($end);
                $tenure = "{$diff->y} tahun {$diff->m} bulan";
            }

            $daysLeft = $phase && $phase['end'] ? (int) Carbon::today()->diffInDays($phase['end'], false) : null;

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $e->id,
                    'foto' => $e->foto,
                    'nama_lengkap' => $e->nama_lengkap,
                    'nip' => $e->nip ?? '-',
                    'kode_karyawan' => $e->kode_karyawan ?? '-',
                    'jabatan' => $e->jabatan ?? '-',
                    'divisi' => $e->divisi ?? '-',
                    'status' => $e->status_aktif == '1' ? 'Aktif' : 'Resign',
                    'fase_label' => $phase['label'] ?? '-',
                    'masa_kerja' => $tenure,
                    'email' => $e->email ?? '-',
                    'whatsapp' => $e->whatsapp ?? '-',
                    'telepon' => $e->Telepon ?? '-',
                    'gender' => $e->gender ?? '-',
                    'tempat_lahir' => $e->tempat_lahir ?? '-',
                    'tanggal_lahir' => $fmt($e->tanggal_lahir),
                    'religion' => $e->religion ?? '-',
                    'alamat' => $e->alamat_lengkap ?? '-',
                    'kota' => $e->kota ?? '-',
                    'provinsi' => $e->provinsi ?? '-',
                    'awal_probation' => $fmt($e->awal_probation),
                    'akhir_probation' => $fmt($e->akhir_probation),
                    'awal_kontrak' => $fmt($e->awal_kontrak),
                    'akhir_kontrak' => $fmt($e->akhir_kontrak),
                    'awal_tetap' => $fmt($e->awal_tetap),
                    'akhir_tetap' => $fmt($e->akhir_tetap),
                    'resigned_at' => $fmt($e->resigned_at),
                    'alasan_resign' => $e->alasan_resign ?: '-',
                    'keterangan' => $e->keterangan ?: '-',
                    'days_left' => $daysLeft,
                    'tindakan' => $phase['action'] ?? null,
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Karyawan tidak ditemukan'], 404);
        }
    }
}
