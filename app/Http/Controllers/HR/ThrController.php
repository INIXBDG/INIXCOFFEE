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
    public function index()
    {
        return view('HR.thr.index');
    }

    private function eligibleEmployeeQuery()
    {
        return karyawan::query()
            ->whereRaw('LOWER(TRIM(jabatan)) <> ?', ['outsource'])
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->whereNotNull('nip')
            ->where('status_aktif', '1');
    }

    public function getPeriods()
    {
        try {
            $eligibleIds = $this->eligibleEmployeeQuery()->select('id');

            $periods = ThrPeriod::withCount([
                'items' => function ($q) use ($eligibleIds) {
                    $q->whereIn('karyawan_id', $eligibleIds);
                },
            ])
                ->withSum(
                    [
                        'items as total_anggaran' => function ($q) use ($eligibleIds) {
                            $q->whereIn('karyawan_id', $eligibleIds);
                        },
                    ],
                    'nilai_thr_final',
                )
                ->orderByDesc('tahun')
                ->orderByDesc('id')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $periods,
            ]);
        } catch (\Exception $e) {
            Log::error('THR getPeriods error: ' . $e->getMessage());
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Gagal memuat daftar periode THR: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function getHolidayDate(Request $request)
    {
        try {
            $year = $request->input('year', date('Y'));

            $holidays = HariLibur::where(function ($q) use ($year) {
                $q->where('year', (string) $year)->orWhereYear('tanggal', $year);
            })
                ->orderBy('tanggal', 'asc')
                ->get()
                ->map(function ($item) {
                    $namaLower = strtolower($item->nama);
                    $isIdulfitri = str_contains($namaLower, 'idul fitri') || str_contains($namaLower, 'idulfitri') || str_contains($namaLower, 'lebaran');
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

            $recommended = $holidays->first(function ($h) {
                return $h['is_idulfitri'] && !$h['is_cuti'];
            });

            if (!$recommended) {
                $recommended = $holidays->first(function ($h) {
                    return $h['is_idulfitri'];
                });
            }

            if (!$recommended && $holidays->isNotEmpty()) {
                $recommended = $holidays->first();
            }

            return response()->json([
                'status' => 'success',
                'year' => (int) $year,
                'total_holidays' => $holidays->count(),
                'holidays' => $holidays,
                'recommended' => $recommended,
                'tanggal' => $recommended ? $recommended['tanggal'] : null,
                'nama' => $recommended ? $recommended['nama'] : null,
            ]);
        } catch (\Exception $e) {
            Log::error('THR getHolidayDate error: ' . $e->getMessage());
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Gagal mencari data hari libur: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

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

            $eligibleIds = $this->eligibleEmployeeQuery()->select('id');

            $allBaseQuery = ThrItem::where('thr_period_id', $period->id)->whereIn('karyawan_id', $eligibleIds);
            $totalKaryawan = (clone $allBaseQuery)->count();
            $totalAnggaran = (clone $allBaseQuery)->sum('nilai_thr_final');
            $totalPenerima = (clone $allBaseQuery)->where('total_masa_kerja_bulan', '>', 0)->count();
            $totalTersediaGaji = (clone $allBaseQuery)->where('nilai_thr_final', '>', 0)->count();
            $totalFull = (clone $allBaseQuery)->where('total_masa_kerja_bulan', '>=', $period->full_masa_kerja_bulan)->count();
            $totalProrata = (clone $allBaseQuery)->where('total_masa_kerja_bulan', '>', 0)->where('total_masa_kerja_bulan', '<', $period->full_masa_kerja_bulan)->count();
            $totalManual = (clone $allBaseQuery)->where('is_manual', 1)->count();

            $query = ThrItem::where('thr_period_id', $period->id)
                ->whereIn('karyawan_id', $eligibleIds)
                ->with(['karyawan:id,nip,foto,divisi,kode_karyawan', 'period:id,full_masa_kerja_bulan,min_masa_kerja_bulan']);

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_lengkap', 'LIKE', "%{$search}%")
                        ->orWhere('jabatan', 'LIKE', "%{$search}%")
                        ->orWhere('rekening_maybank', 'LIKE', "%{$search}%")
                        ->orWhereHas('karyawan', function ($sub) use ($search) {
                            $sub->where('nip', 'LIKE', "%{$search}%")->orWhere('divisi', 'LIKE', "%{$search}%");
                        });
                });
            }

            if (!empty($statusFilter) && $statusFilter !== 'all') {
                $query->where('status_karyawan', $statusFilter);
            }

            if (!empty($kategoriFilter) && $kategoriFilter !== 'all') {
                if ($kategoriFilter === 'full') {
                    $query->where('total_masa_kerja_bulan', '>=', $period->full_masa_kerja_bulan);
                } elseif ($kategoriFilter === 'prorata') {
                    $query->where('total_masa_kerja_bulan', '>', 0)->where('total_masa_kerja_bulan', '<', $period->full_masa_kerja_bulan);
                } elseif ($kategoriFilter === 'ineligible') {
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
                    'total_karyawan' => $totalKaryawan,
                    'total_penerima' => $totalPenerima,
                    'total_tersedia_gaji' => $totalTersediaGaji,
                    'total_anggaran' => (float) $totalAnggaran,
                    'total_full' => $totalFull,
                    'total_prorata' => $totalProrata,
                    'total_manual' => $totalManual,
                ],
                'items' => $items,
            ]);
        } catch (\Exception $e) {
            Log::error('THR getData error: ' . $e->getMessage());
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Gagal memuat data THR: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

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

            try {
                AdministrasiKaryawan::create([
                    'nama_administrasi' => $period->nama_periode,
                    'dateline' => Carbon::parse($period->tanggal_jatuh_tempo)->format('Y-m-d'),
                    'keterangan' => 'Pembayaran tunjangan hari raya karyawan',
                    'status' => 'proses',
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
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Gagal menghasilkan perhitungan THR: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function updateItem(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'status_karyawan' => 'required|in:Tetap,Kontrak,Probation',
            'rekening_maybank' => 'nullable|string|max:100',
            'tanggal_join' => 'nullable|date',
            'total_masa_kerja_bulan' => 'required|numeric|min:0|max:120',
            'masa_kerja_teks' => 'nullable|string|max:100',
            'basic_salary' => 'required|numeric|min:0',
            'nilai_thr_final' => 'required|numeric|min:0',
            'catatan' => 'nullable|string|max:500',
        ]);

        try {
            $item = ThrItem::findOrFail($id);
            $period = ThrPeriod::find($item->thr_period_id);
            $fullMonths = (float) ($period && $period->full_masa_kerja_bulan ? $period->full_masa_kerja_bulan : 12);

            $basic = (float) $validated['basic_salary'];
            $bulan = (float) $validated['total_masa_kerja_bulan'];

            if ($bulan >= $fullMonths) {
                $sistem = $basic;
            } elseif ($bulan > 0) {
                $sistem = round(($basic / 12) * $bulan);
            } else {
                $sistem = 0.0;
            }

            $teks = trim((string) ($validated['masa_kerja_teks'] ?? ''));
            if ($teks === '') {
                $teks = $bulan >= 12 ? '12 Bln (Penuh)' : ((int) $bulan) . ' Bln';
            }

            $item->nama_lengkap = $validated['nama_lengkap'];
            $item->jabatan = $validated['jabatan'] ?? null;
            $item->status_karyawan = $validated['status_karyawan'];
            $item->rekening_maybank = $validated['rekening_maybank'] ?? null;
            $item->tanggal_join = !empty($validated['tanggal_join']) ? $validated['tanggal_join'] : null;
            $item->total_masa_kerja_bulan = $bulan;
            $item->masa_kerja_tahun = intdiv((int) $bulan, 12);
            $item->masa_kerja_bulan = ((int) $bulan) % 12;
            $item->masa_kerja_hari = 0;
            $item->masa_kerja_teks = $teks;
            $item->basic_salary = $basic;
            $item->nilai_thr_sistem = $sistem;
            $item->nilai_thr_final = (float) $validated['nilai_thr_final'];
            $item->catatan = $validated['catatan'] ?? null;
            $item->is_manual = true;
            $item->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Data THR berhasil disimpan.',
                'item' => $item,
            ]);
        } catch (\Exception $e) {
            Log::error('THR updateItem error: ' . $e->getMessage());
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Gagal memperbarui data THR: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

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
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Gagal memperbarui ketentuan THR: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

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
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Gagal menghapus periode THR: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function exportExcel($periodId)
    {
        try {
            $period = ThrPeriod::findOrFail($periodId);
            $fileName = 'THR_' . str_replace(' ', '_', $period->nama_periode) . '_' . date('Ymd') . '.xlsx';

            return Excel::download(new ThrExport($period), $fileName);
        } catch (\Exception $e) {
            Log::error('THR exportExcel error: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Gagal mengunduh Excel: ' . $e->getMessage());
        }
    }

    private function calculateThrItems(ThrPeriod $period, bool $forceResetManual = false)
    {
        $cutoffDate = Carbon::parse($period->tanggal_jatuh_tempo)->endOfDay();
        $targetYear = (int) $cutoffDate->format('Y');
        $targetMonth = (int) $cutoffDate->format('m');

        $employees = $this->eligibleEmployeeQuery()->get();

        $employeeIds = $employees->pluck('id')->toArray();

        ThrItem::where('thr_period_id', $period->id)->whereNotIn('karyawan_id', $employeeIds)->delete();

        $allLogGaji = LogGaji::whereIn('id_karyawan', $employeeIds)->get()->groupBy('id_karyawan');

        $existingItems = ThrItem::where('thr_period_id', $period->id)->get()->keyBy('karyawan_id');

        $previousPeriod = ThrPeriod::where('tanggal_jatuh_tempo', '<', $period->tanggal_jatuh_tempo)->orderByDesc('tanggal_jatuh_tempo')->first();

        $defaultStartPoint = $previousPeriod ? Carbon::parse($previousPeriod->tanggal_jatuh_tempo)->startOfDay() : $cutoffDate->copy()->subYear()->startOfDay();

        foreach ($employees as $emp) {
            $existing = $existingItems->get($emp->id);

            if ($existing && $existing->is_manual && !$forceResetManual) {
                continue;
            }

            $joinDateRaw = $emp->awal_probation ?? ($emp->awal_kontrak ?? $emp->awal_tetap);
            $joinDate = $joinDateRaw ? Carbon::parse($joinDateRaw)->startOfDay() : null;

            $statusKaryawan = 'Tetap';
            if (!empty($emp->awal_tetap)) {
                $statusKaryawan = 'Tetap';
            } elseif (!empty($emp->awal_kontrak)) {
                $statusKaryawan = 'Kontrak';
            } elseif (!empty($emp->awal_probation)) {
                $statusKaryawan = 'Probation';
            }

            $logs = $allLogGaji->get($emp->id, collect());
            $targetKey = $targetYear * 12 + $targetMonth;

            $pastLogs = $logs->filter(function ($l) use ($targetKey) {
                return $l->tahun * 12 + $l->bulan <= $targetKey && !empty($l->gaji) && (float) $l->gaji > 0;
            });

            $selectedLog = null;
            if ($pastLogs->isNotEmpty()) {
                $selectedLog = $pastLogs->sortByDesc(fn($l) => $l->tahun * 12 + $l->bulan)->first();
            } else {
                $selectedLog = $logs->where('gaji', '>', 0)->sortByDesc(fn($l) => $l->tahun * 12 + $l->bulan)->first();
            }

            $basicSalary = $selectedLog ? (float) $selectedLog->gaji : (float) ($emp->gaji ?? 0);

            if ($basicSalary <= 0 && $existing && (float) $existing->basic_salary > 0) {
                $basicSalary = (float) $existing->basic_salary;
            }

            $years = 0;
            $months = 0;
            $days = 0;
            $totalMonthsFloat = 0.0;
            $tenureText = '-';

            if ($joinDate) {
                if ($joinDate->gt($cutoffDate)) {
                    $totalMonthsFloat = 0.0;
                    $tenureText = '0 Bulan';
                    $calcMonths = 0;
                } else {
                    $startDate = $joinDate->gt($defaultStartPoint) ? $joinDate : $defaultStartPoint;

                    $diff = $startDate->diff($cutoffDate);
                    $years = (int) $diff->y;
                    $months = (int) $diff->m;
                    $days = (int) $diff->d;

                    $totalCalculatedMonths = $years * 12 + $months;
                    if ($days >= 15) {
                        $totalCalculatedMonths += 1;
                    }
                    $calcMonths = min(12, $totalCalculatedMonths);

                    if ($joinDate->lte($defaultStartPoint)) {
                        $calcMonths = 12;
                        $totalMonthsFloat = 12.0;
                        $tenureText = '12 Bln (Penuh)';
                    } else {
                        $totalMonthsFloat = round($calcMonths, 2);
                        $parts = [];
                        if ($years > 0) {
                            $parts[] = $years . ' Thn';
                        }
                        if ($months > 0 || $years == 0) {
                            $parts[] = $months . ' Bln';
                        }
                        if ($days > 0 && $years == 0 && $months < 3) {
                            $parts[] = $days . ' Hr';
                        }
                        $tenureText = implode(' ', $parts) ?: $calcMonths . ' Bln';
                    }
                }
            } else {
                $calcMonths = 12;
                $totalMonthsFloat = 12.0;
                $tenureText = '12 Bln (Penuh)';
            }

            if ($calcMonths >= 12) {
                $nilaiThrSistem = $basicSalary;
            } elseif ($calcMonths > 0) {
                $nilaiThrSistem = round(($basicSalary / 12) * $calcMonths);
            } else {
                $nilaiThrSistem = 0.0;
            }

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
                    'nilai_thr_final' => $nilaiThrSistem,
                    'is_manual' => false,
                    'catatan' => null,
                ],
            );
        }
    }
}