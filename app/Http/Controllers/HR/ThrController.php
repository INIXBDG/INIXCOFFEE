<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\karyawan;
use App\Models\LogGaji;
use App\Models\ThrPeriod;
use App\Models\ThrItem;
use App\Models\AdministrasiKaryawan;
use App\Models\HariLibur;
use App\Exports\ThrExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class ThrController extends Controller
{
    /**
     * Display THR main view.
     */
    public function index()
    {
        return view('HR.thr.index');
    }

    /**
     * Get list of all THR periods.
     */
    public function getPeriods()
    {
        try {
            $periods = ThrPeriod::withCount('items')
                ->withSum('items as total_anggaran', 'nilai_thr_final')
                ->orderByDesc('tahun')
                ->orderByDesc('id')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $periods,
            ]);
        } catch (\Exception $e) {
            Log::error('THR getPeriods error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memuat daftar periode THR: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Idul Fitri holiday date and list of holidays for a specific year from hari_liburs table.
     */
    public function getHolidayDate(Request $request)
    {
        try {
            $year = $request->input('year', date('Y'));

            // Query seluruh hari libur pada tahun tersebut (cek kolom year maupun tahun dari kolom tanggal)
            $holidays = HariLibur::where(function ($q) use ($year) {
                    $q->where('year', (string)$year)
                      ->orWhereYear('tanggal', $year);
                })
                ->orderBy('tanggal', 'asc')
                ->get()
                ->map(function ($item) {
                    $namaLower = strtolower($item->nama);
                    $isIdulfitri = str_contains($namaLower, 'idul fitri') 
                                || str_contains($namaLower, 'idulfitri') 
                                || str_contains($namaLower, 'lebaran');
                    $isCutiBersama = str_contains($namaLower, 'cuti');

                    return [
                        'id' => $item->id,
                        'nama' => $item->nama,
                        'tanggal' => Carbon::parse($item->tanggal)->format('Y-m-d'),
                        'tanggal_indo' => Carbon::parse($item->tanggal)->translatedFormat('l, d F Y'),
                        'tipe' => $item->tipe,
                        'is_idulfitri' => $isIdulfitri,
                        'is_cuti' => $isCutiBersama,
                    ];
                });

            // Cari rekomendasi Idul Fitri terbaik di tahun tersebut:
            // 1. Hari Raya Idul Fitri (bukan Cuti Bersama)
            $recommended = $holidays->first(function ($h) {
                return $h['is_idulfitri'] && !$h['is_cuti'];
            });

            // 2. Jika tidak ada, ambil hari libur manapun yang terkait Idul Fitri / Lebaran
            if (!$recommended) {
                $recommended = $holidays->first(function ($h) {
                    return $h['is_idulfitri'];
                });
            }

            // 3. Jika tetap belum ada libur Idul Fitri, ambil hari libur pertama di tahun tersebut (jika ada)
            if (!$recommended && $holidays->isNotEmpty()) {
                $recommended = $holidays->first();
            }

            return response()->json([
                'status' => 'success',
                'year' => (int)$year,
                'total_holidays' => $holidays->count(),
                'holidays' => $holidays,
                'recommended' => $recommended,
                'tanggal' => $recommended ? $recommended['tanggal'] : null,
                'nama' => $recommended ? $recommended['nama'] : null,
            ]);
        } catch (\Exception $e) {
            Log::error('THR getHolidayDate error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mencari data hari libur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get THR items and summary statistics for a given period.
     */
    public function getData(Request $request)
    {
        try {
            $periodId = $request->input('period_id');

            if ($periodId) {
                $period = ThrPeriod::find($periodId);
            } else {
                $period = ThrPeriod::orderByDesc('tahun')->orderByDesc('id')->first();
            }

            if (!$period) {
                return response()->json([
                    'status' => 'success',
                    'period' => null,
                    'stats' => [
                        'total_karyawan' => 0,
                        'total_penerima' => 0,
                        'total_anggaran' => 0,
                        'total_full' => 0,
                        'total_prorata' => 0,
                        'total_manual' => 0,
                    ],
                    'items' => [],
                ]);
            }

            $search = $request->input('search');
            $statusFilter = $request->input('status_karyawan');
            $kategoriFilter = $request->input('kategori_thr');

            // Query base for stats — berdasarkan masa kerja periode ini (tanpa batasan minimum masa kerja)
            $allBaseQuery = ThrItem::where('thr_period_id', $period->id);
            $totalKaryawan   = (clone $allBaseQuery)->count();
            $totalAnggaran   = (clone $allBaseQuery)->sum('nilai_thr_final');
            // Karyawan BERHAK = masa kerja > 0 (semua karyawan yang aktif berhak menerima THR)
            $totalPenerima   = (clone $allBaseQuery)
                ->where('total_masa_kerja_bulan', '>', 0)
                ->count();
            // Karyawan tersedia data gaji (untuk info anggaran)
            $totalTersediaGaji = (clone $allBaseQuery)->where('nilai_thr_final', '>', 0)->count();
            $totalFull = (clone $allBaseQuery)
                ->where('total_masa_kerja_bulan', '>=', $period->full_masa_kerja_bulan)
                ->count();
            $totalProrata = (clone $allBaseQuery)
                ->where('total_masa_kerja_bulan', '>', 0)
                ->where('total_masa_kerja_bulan', '<', $period->full_masa_kerja_bulan)
                ->count();
            $totalManual = (clone $allBaseQuery)->where('is_manual', 1)->count();

            // Item Query with zero N+1
            $query = ThrItem::where('thr_period_id', $period->id)
                ->with(['karyawan:id,nip,foto,divisi,kode_karyawan', 'period:id,full_masa_kerja_bulan,min_masa_kerja_bulan']);

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_lengkap', 'LIKE', "%{$search}%")
                      ->orWhere('jabatan', 'LIKE', "%{$search}%")
                      ->orWhere('rekening_maybank', 'LIKE', "%{$search}%")
                      ->orWhereHas('karyawan', function ($sub) use ($search) {
                          $sub->where('nip', 'LIKE', "%{$search}%")
                              ->orWhere('divisi', 'LIKE', "%{$search}%");
                      });
                });
            }

            if (!empty($statusFilter) && $statusFilter !== 'all') {
                $query->where('status_karyawan', $statusFilter);
            }

            if (!empty($kategoriFilter) && $kategoriFilter !== 'all') {
                if ($kategoriFilter === 'full') {
                    // Masa kerja >= threshold penuh (12 bulan atau full_masa_kerja_bulan)
                    $query->where('total_masa_kerja_bulan', '>=', $period->full_masa_kerja_bulan);
                } elseif ($kategoriFilter === 'prorata') {
                    // Prorata: masa kerja > 0 dan di bawah threshold penuh
                    $query->where('total_masa_kerja_bulan', '>', 0)
                          ->where('total_masa_kerja_bulan', '<', $period->full_masa_kerja_bulan);
                } elseif ($kategoriFilter === 'ineligible') {
                    // Masa kerja = 0 (masuk setelah tanggal Idulfitri)
                    $query->where('total_masa_kerja_bulan', '<=', 0);
                } elseif ($kategoriFilter === 'manual') {
                    $query->where('is_manual', 1);
                }
            }

            $items = $query->orderBy('nama_lengkap', 'asc')->get();

            return response()->json([
                'status' => 'success',
                'period' => $period,
                'stats' => [
                    'total_karyawan'     => $totalKaryawan,
                    'total_penerima'     => $totalPenerima,       // berhak berdasar masa kerja
                    'total_tersedia_gaji'=> $totalTersediaGaji,   // punya data gaji (nilai > 0)
                    'total_anggaran'     => (float) $totalAnggaran,
                    'total_full'         => $totalFull,
                    'total_prorata'      => $totalProrata,
                    'total_manual'       => $totalManual,
                ],
                'items' => $items,
            ]);
        } catch (\Exception $e) {
            Log::error('THR getData error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memuat data THR: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate or recalculate THR for active employees.
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'nama_periode' => 'required|string|max:150',
            'tahun' => 'required|integer|min:2000|max:2100',
            'tanggal_jatuh_tempo' => 'required|date',
            'min_masa_kerja_bulan' => 'required|integer|min:0|max:60',
            'full_masa_kerja_bulan' => 'required|integer|min:1|max:120',
            'catatan' => 'nullable|string|max:500',
            'force_reset_manual' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            // Find or create period
            $period = ThrPeriod::firstOrNew([
                'tahun' => $validated['tahun'],
                'nama_periode' => $validated['nama_periode'],
            ]);

            $period->tanggal_jatuh_tempo = $validated['tanggal_jatuh_tempo'];
            $period->min_masa_kerja_bulan = $validated['min_masa_kerja_bulan'];
            $period->full_masa_kerja_bulan = $validated['full_masa_kerja_bulan'];
            $period->catatan = $validated['catatan'] ?? null;
            $period->created_by = Auth::id() ?? 1;
            $period->save();

            $forceResetManual = $request->boolean('force_reset_manual', false);

            $this->calculateThrItems($period, $forceResetManual);

            // Buat otomatis satu Administrasi Karyawan dengan deadline pada hari Idulfitri
            // dan keterangan "Pembayaran tunjangan hari raya karyawan"
            try {
                AdministrasiKaryawan::create([
                    'nama_administrasi' => $period->nama_periode,
                    'dateline'          => Carbon::parse($period->tanggal_jatuh_tempo)->format('Y-m-d'),
                    'keterangan'        => 'Pembayaran tunjangan hari raya karyawan',
                    'status'            => 'proses',
                ]);
                Cache::forget('administrasi_karyawan_progress');
                Cache::forget('office_dashboard_administrasi');
            } catch (\Exception $admEx) {
                Log::warning('Auto generate administrasi karyawan THR notice: ' . $admEx->getMessage());
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Periode THR dan Administrasi Karyawan berhasil dibuat.',
                'period_id' => $period->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('THR generate error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghasilkan perhitungan THR: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update manual THR value for an individual employee.
     */
    public function updateItem(Request $request, $id)
    {
        $validated = $request->validate([
            'nilai_thr_final' => 'required|numeric|min:0',
            'basic_salary' => 'nullable|numeric|min:0',
            'catatan' => 'nullable|string|max:500',
            'save_to_master' => 'nullable|boolean',
        ]);

        try {
            $item = ThrItem::findOrFail($id);
            $item->nilai_thr_final = (float) $validated['nilai_thr_final'];
            if (isset($validated['basic_salary'])) {
                $newBasic = (float) $validated['basic_salary'];
                $item->basic_salary = $newBasic;

                // Simpan ke data master karyawan agar otomatis auto-fill di semua modul
                if ($request->boolean('save_to_master', true) && $newBasic > 0) {
                    $karyawan = karyawan::find($item->karyawan_id);
                    if ($karyawan) {
                        $karyawan->gaji = $newBasic;
                        $karyawan->save();
                    }
                }
            }
            $item->catatan = $validated['catatan'] ?? null;
            $item->is_manual = true;
            $item->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Nilai THR dan Basic Salary berhasil disimpan.',
                'item' => $item,
            ]);
        } catch (\Exception $e) {
            Log::error('THR updateItem error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui nilai THR: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update settings of a THR period (e.g. Cutoff Date, Minimum Work Period, Full Work Period).
     */
    public function updateSetting(Request $request, $periodId)
    {
        $validated = $request->validate([
            'nama_periode' => 'required|string|max:150',
            'tanggal_jatuh_tempo' => 'required|date',
            'min_masa_kerja_bulan' => 'required|integer|min:0|max:60',
            'full_masa_kerja_bulan' => 'required|integer|min:1|max:120',
            'catatan' => 'nullable|string|max:500',
            'recalculate' => 'nullable|boolean',
            'force_reset_manual' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $period = ThrPeriod::findOrFail($periodId);
            $period->nama_periode = $validated['nama_periode'];
            $period->tanggal_jatuh_tempo = $validated['tanggal_jatuh_tempo'];
            $period->min_masa_kerja_bulan = $validated['min_masa_kerja_bulan'];
            $period->full_masa_kerja_bulan = $validated['full_masa_kerja_bulan'];
            $period->catatan = $validated['catatan'] ?? null;
            $period->save();

            if ($request->boolean('recalculate', true)) {
                $forceReset = $request->boolean('force_reset_manual', false);
                $this->calculateThrItems($period, $forceReset);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Ketentuan THR berhasil diperbarui.',
                'period' => $period,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('THR updateSetting error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui ketentuan THR: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an entire THR period.
     */
    public function destroyPeriod($id)
    {
        try {
            $period = ThrPeriod::findOrFail($id);
            $period->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Periode THR berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            Log::error('THR destroyPeriod error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus periode THR: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export THR data to Excel.
     */
    public function exportExcel($periodId)
    {
        try {
            $period = ThrPeriod::findOrFail($periodId);
            $fileName = 'THR_' . str_replace(' ', '_', $period->nama_periode) . '_' . date('Ymd') . '.xlsx';

            return Excel::download(new ThrExport($period), $fileName);
        } catch (\Exception $e) {
            Log::error('THR exportExcel error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengunduh Excel: ' . $e->getMessage());
        }
    }

    /**
     * Core calculation engine for THR items.
     */
    private function calculateThrItems(ThrPeriod $period, bool $forceResetManual = false)
    {
        $cutoffDate = Carbon::parse($period->tanggal_jatuh_tempo)->endOfDay();
        $targetYear = (int) $cutoffDate->format('Y');
        $targetMonth = (int) $cutoffDate->format('m');

        // Fetch eligible active employees in one single query
        $employees = karyawan::query()
            ->whereNot('jabatan', 'Outsource')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->whereNotNull('nip')
            ->where('status_aktif', '1')
            ->get();

        $employeeIds = $employees->pluck('id')->toArray();

        // Eager load all LogGaji in ONE single query (no N+1)
        $allLogGaji = LogGaji::whereIn('id_karyawan', $employeeIds)
            ->get()
            ->groupBy('id_karyawan');

        // Existing THR items for this period (to preserve manual edits)
        $existingItems = ThrItem::where('thr_period_id', $period->id)
            ->get()
            ->keyBy('karyawan_id');

        $minMonths = (int) $period->min_masa_kerja_bulan;
        $fullMonths = (int) $period->full_masa_kerja_bulan;

        // End point adalah Idulfitri periode saat ini
        // Start point untuk karyawan lama adalah Idulfitri periode sebelumnya (atau 12 bulan sebelumnya jika periode pertama)
        $previousPeriod = ThrPeriod::where('tanggal_jatuh_tempo', '<', $period->tanggal_jatuh_tempo)
            ->orderByDesc('tanggal_jatuh_tempo')
            ->first();

        $defaultStartPoint = $previousPeriod 
            ? Carbon::parse($previousPeriod->tanggal_jatuh_tempo)->startOfDay()
            : $cutoffDate->copy()->subYear()->startOfDay();

        foreach ($employees as $emp) {
            // 1. Determine join date
            $joinDateRaw = $emp->awal_probation ?? $emp->awal_kontrak ?? $emp->awal_tetap;
            $joinDate = $joinDateRaw ? Carbon::parse($joinDateRaw)->startOfDay() : null;

            // 2. Determine Employment Status
            $statusKaryawan = 'Tetap';
            if (!empty($emp->awal_tetap)) {
                $statusKaryawan = 'Tetap';
            } elseif (!empty($emp->awal_kontrak)) {
                $statusKaryawan = 'Kontrak';
            } elseif (!empty($emp->awal_probation)) {
                $statusKaryawan = 'Probation';
            }

            // 3. Determine Basic Salary (gaji pokok + tunjangan jabatan) otomatis diambil langsung dari data (auto-fill)
            $logs = $allLogGaji->get($emp->id, collect());
            $targetKey = ($targetYear * 12) + $targetMonth;

            $pastLogs = $logs->filter(function ($l) use ($targetKey) {
                return (($l->tahun * 12) + $l->bulan) <= $targetKey && !empty($l->gaji) && (float)$l->gaji > 0;
            });

            $selectedLog = null;
            if ($pastLogs->isNotEmpty()) {
                $selectedLog = $pastLogs->sortByDesc(fn($l) => ($l->tahun * 12) + $l->bulan)->first();
            } else {
                $selectedLog = $logs->where('gaji', '>', 0)->sortByDesc(fn($l) => ($l->tahun * 12) + $l->bulan)->first();
            }

            $gajiPokok = $selectedLog ? (float) $selectedLog->gaji : (float) ($emp->gaji ?? 0);
            $tunjanganJabatan = $selectedLog ? (float) ($selectedLog->tunjangan_jabatan ?? 0) : (float) ($emp->tunjangan_jabatan ?? 0);
            $basicSalary = $gajiPokok + $tunjanganJabatan;

            $existing = $existingItems->get($emp->id);
            // If employee basic salary in DB is 0 but existing item had a custom entered basic_salary > 0, preserve it
            if ($basicSalary <= 0 && $existing && (float) $existing->basic_salary > 0) {
                $basicSalary = (float) $existing->basic_salary;
            }

            // 4. Calculate Tenure (Masa Kerja)
            // End point: tanggal Idulfitri periode ini ($cutoffDate)
            // Start point: jika karyawan baru masuk setelah Idulfitri sebelumnya -> dihitung dari join date ke Idulfitri saat ini.
            //              jika karyawan lama (masuk sebelum Idulfitri sebelumnya) -> dihitung dari Idulfitri sebelumnya ke Idulfitri saat ini (12 bulan penuh).
            $years = 0;
            $months = 0;
            $days = 0;
            $totalMonthsFloat = 0.0;
            $tenureText = '-';

            if ($joinDate) {
                if ($joinDate->gt($cutoffDate)) {
                    // Masuk setelah tanggal Idulfitri periode ini
                    $totalMonthsFloat = 0.0;
                    $tenureText = '0 Bulan';
                    $calcMonths = 0;
                } else {
                    // Tentukan tanggal mulai hitung untuk periode THR ini
                    $startDate = ($joinDate->gt($defaultStartPoint)) ? $joinDate : $defaultStartPoint;

                    $diff = $startDate->diff($cutoffDate);
                    $years = (int) $diff->y;
                    $months = (int) $diff->m;
                    $days = (int) $diff->d;

                    // Hitung total bulan dalam rentang periode ini (maksimal 12 bulan)
                    $totalCalculatedMonths = ($years * 12) + $months;
                    if ($days >= 15) {
                        $totalCalculatedMonths += 1; // pembulatan bulan jika sisa hari >= 15
                    }
                    $calcMonths = min(12, $totalCalculatedMonths);

                    // Teks display masa kerja untuk karyawan
                    // Jika karyawan sudah melewati Idulfitri sebelumnya, maka masa kerja THR adalah 12 Bulan (Penuh)
                    if ($joinDate->lte($defaultStartPoint)) {
                        $calcMonths = 12;
                        $totalMonthsFloat = 12.0;
                        $tenureText = '12 Bln (Penuh)';
                    } else {
                        $totalMonthsFloat = round($calcMonths, 2);
                        $parts = [];
                        if ($years > 0) $parts[] = $years . ' Thn';
                        if ($months > 0 || $years == 0) $parts[] = $months . ' Bln';
                        if ($days > 0 && $years == 0 && $months < 3) $parts[] = $days . ' Hr';
                        $tenureText = implode(' ', $parts) ?: ($calcMonths . ' Bln');
                    }
                }
            } else {
                // If join date is not set, treat as 12 months (full)
                $calcMonths = 12;
                $totalMonthsFloat = 12.0;
                $tenureText = '12 Bln (Penuh)';
            }

            // 5. Calculate THR based on revision:
            // Tidak ada minimal masa kerja.
            // Rumus: (Basic Salary / 12) * masa kerja
            // Jika masa kerja >= 12 bulan (atau karyawan lama), dapat 1x basic salary
            $nilaiThrSistem = 0.0;

            if ($calcMonths >= 12) {
                $nilaiThrSistem = $basicSalary;
            } elseif ($calcMonths > 0) {
                $nilaiThrSistem = round(($basicSalary / 12) * $calcMonths);
            } else {
                $nilaiThrSistem = 0.0;
            }

            // Check existing item
            $existing = $existingItems->get($emp->id);
            $isManual = false;
            $nilaiThrFinal = $nilaiThrSistem;
            $catatan = null;

            if ($existing) {
                if (!$forceResetManual && $existing->is_manual) {
                    // Preserve manual override!
                    $isManual = true;
                    $nilaiThrFinal = $existing->nilai_thr_final;
                    $catatan = $existing->catatan;
                }
            }

            // Update or create THR Item
            ThrItem::updateOrCreate(
                [
                    'thr_period_id' => $period->id,
                    'karyawan_id' => $emp->id,
                ],
                [
                    'nama_lengkap' => $emp->nama_lengkap,
                    'jabatan' => $emp->jabatan,
                    'status_karyawan' => $statusKaryawan,
                    'rekening_maybank' => $emp->rekening_maybank,
                    'tanggal_join' => $joinDate ? $joinDate->toDateString() : null,
                    'masa_kerja_tahun' => $years,
                    'masa_kerja_bulan' => $months,
                    'masa_kerja_hari' => $days,
                    'total_masa_kerja_bulan' => $totalMonthsFloat,
                    'masa_kerja_teks' => $tenureText,
                    'basic_salary' => $basicSalary,
                    'nilai_thr_sistem' => $nilaiThrSistem,
                    'nilai_thr_final' => $nilaiThrFinal,
                    'is_manual' => $isManual,
                    'catatan' => $catatan,
                ]
            );
        }
    }
}
