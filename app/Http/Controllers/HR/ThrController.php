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
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ThrController extends Controller
{
    private static bool $columnEnsured = false;

    private bool $tampilDesimal = true;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->ensureColumns();
            return $next($request);
        });
    }

    private function ensureColumns(): void
    {
        if (self::$columnEnsured) {
            return;
        }

        $table = (new ThrItem())->getTable();

        if (Schema::hasTable($table) && !Schema::hasColumn($table, 'is_excluded')) {
            Schema::table($table, function (Blueprint $t) {
                $t->boolean('is_excluded')->default(false);
            });
        }

        self::$columnEnsured = true;
    }

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

    private function hitungNilai(float $basic, float $bulan, float $full = 12): float
    {
        if ($bulan >= $full) {
            return round($basic, 2);
        }

        if ($bulan > 0) {
            return round(($basic * $bulan) / 12, 2);
        }

        return 0.0;
    }

    public function getPeriods()
    {
        try {
            $eligibleIds = $this->eligibleEmployeeQuery()->select('id');

            $periods = ThrPeriod::withCount([
                'items' => function ($q) use ($eligibleIds) {
                    $q->whereIn('karyawan_id', $eligibleIds)->where('is_excluded', false);
                },
            ])
                ->withSum(
                    [
                        'items as total_anggaran' => function ($q) use ($eligibleIds) {
                            $q->whereIn('karyawan_id', $eligibleIds)->where('is_excluded', false);
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
                        'total_dikecualikan' => 0,
                        'total_penerima' => 0,
                        'total_tersedia_gaji' => 0,
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
            $activeQuery = (clone $allBaseQuery)->where('is_excluded', false);

            $totalKaryawan = (clone $activeQuery)->count();
            $totalDikecualikan = (clone $allBaseQuery)->where('is_excluded', true)->count();
            $totalAnggaran = (clone $activeQuery)->sum('nilai_thr_final');
            $totalPenerima = (clone $activeQuery)->where('total_masa_kerja_bulan', '>', 0)->count();
            $totalTersediaGaji = (clone $activeQuery)->where('nilai_thr_final', '>', 0)->count();
            $totalFull = (clone $activeQuery)->where('total_masa_kerja_bulan', '>=', $period->full_masa_kerja_bulan)->count();
            $totalProrata = (clone $activeQuery)->where('total_masa_kerja_bulan', '>', 0)->where('total_masa_kerja_bulan', '<', $period->full_masa_kerja_bulan)->count();
            $totalManual = (clone $activeQuery)->where('is_manual', 1)->count();

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
                    'total_dikecualikan' => $totalDikecualikan,
                    'total_penerima' => $totalPenerima,
                    'total_tersedia_gaji' => $totalTersediaGaji,
                    'total_anggaran' => round((float) $totalAnggaran, 2),
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

            $basic = round((float) $validated['basic_salary'], 2);
            $bulan = (float) $validated['total_masa_kerja_bulan'];

            $sistem = $this->hitungNilai($basic, $bulan, $fullMonths);

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
            $item->nilai_thr_final = round((float) $validated['nilai_thr_final'], 2);
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

    public function exportExcel(Request $request, $periodId)
    {
        try {
            $period = ThrPeriod::findOrFail($periodId);
            $tampilDesimal = filter_var($request->input('desimal', true), FILTER_VALIDATE_BOOLEAN);
            $fileName = 'THR_' . str_replace(' ', '_', $period->nama_periode) . '_' . date('Ymd') . '.xlsx';

            return Excel::download(new ThrExport($period, $tampilDesimal), $fileName);
        } catch (\Exception $e) {
            Log::error('THR exportExcel error: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Gagal mengunduh Excel: ' . $e->getMessage());
        }
    }

    public function toggleInclude(Request $request)
    {
        $validated = $request->validate([
            'period_id' => 'required|integer',
            'ikut' => 'required|boolean',
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'integer',
        ]);

        try {
            $period = ThrPeriod::findOrFail($validated['period_id']);
            $ikut = $request->boolean('ikut');

            $count = ThrItem::where('thr_period_id', $period->id)
                ->whereIn('id', $validated['item_ids'])
                ->update(['is_excluded' => !$ikut]);

            return response()->json([
                'status' => 'success',
                'message' => $count . ' karyawan ' . ($ikut ? 'diikutsertakan dalam' : 'dikeluarkan dari') . ' perhitungan THR.',
            ]);
        } catch (\Exception $e) {
            Log::error('THR toggleInclude error: ' . $e->getMessage());
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Gagal menyimpan perubahan: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function exportPdf(Request $request)
    {
        $period = ThrPeriod::findOrFail($request->input('period_id'));
        $this->tampilDesimal = filter_var($request->input('desimal', true), FILTER_VALIDATE_BOOLEAN);

        $mode = $request->input('mode', 'rekap');
        if (!in_array($mode, ['rekap', 'per_orang', 'semua_surat'], true)) {
            $mode = 'rekap';
        }

        $polos = filter_var($request->input('polos', false), FILTER_VALIDATE_BOOLEAN);
        $tahun = (int) $period->tahun;
        $jatuhTempo = Carbon::parse($period->tanggal_jatuh_tempo);
        $fullMonths = (float) ($period->full_masa_kerja_bulan ?: 12);

        $nomorSurat = trim((string) $request->input('nomor_surat', ''));
        if ($nomorSurat === '') {
            $nomorSurat = '........../HRD-INIX/THR/' . $tahun;
        }

        $eligibleIds = $this->eligibleEmployeeQuery()->select('id');

        $query = ThrItem::where('thr_period_id', $period->id)
            ->whereIn('karyawan_id', $eligibleIds)
            ->where('is_excluded', false)
            ->where('total_masa_kerja_bulan', '>', 0)
            ->with('karyawan:id,nip,divisi');

        if ($mode === 'per_orang') {
            $query->where('id', (int) $request->input('item_id', 0));
        } else {
            $search = trim((string) $request->input('search', ''));
            $statusFilter = $request->input('status_karyawan');
            $kategoriFilter = $request->input('kategori_thr');

            if ($search !== '') {
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

            if ($kategoriFilter === 'full') {
                $query->where('total_masa_kerja_bulan', '>=', $fullMonths);
            } elseif ($kategoriFilter === 'prorata') {
                $query->where('total_masa_kerja_bulan', '<', $fullMonths);
            } elseif ($kategoriFilter === 'manual') {
                $query->where('is_manual', 1);
            }
        }

        $items = $query->orderBy('nama_lengkap')->get()->map(function ($it) use ($fullMonths) {
            $bulan = (float) $it->total_masa_kerja_bulan;
            $bulanInt = (int) round($bulan);
            $basic = (float) $it->basic_salary;
            $nilai = (float) $it->nilai_thr_final;
            $basicFmt = 'Rp ' . $this->rp($basic);

            if ($it->is_manual) {
                $kategori = 'Manual';
                $rumus = 'Nominal disesuaikan secara manual';
            } elseif ($bulan >= $fullMonths) {
                $kategori = '1x Gaji';
                $rumus = '1 × Basic Salary (' . $basicFmt . ')';
            } else {
                $kategori = 'Prorata';
                $rumus = "({$basicFmt} × {$bulanInt} bulan ÷ 12)";
            }

            return [
                'id' => $it->id,
                'nama_lengkap' => $it->nama_lengkap,
                'nip' => $it->karyawan && $it->karyawan->nip ? $it->karyawan->nip : '-',
                'divisi' => $it->karyawan && $it->karyawan->divisi ? $it->karyawan->divisi : '-',
                'jabatan' => $it->jabatan ?: '-',
                'status_karyawan' => $it->status_karyawan,
                'rekening_maybank' => $it->rekening_maybank ?: '-',
                'tanggal_join_teks' => $it->tanggal_join ? Carbon::parse($it->tanggal_join)->translatedFormat('d F Y') : '-',
                'bulan_kerja' => $bulanInt,
                'masa_kerja_teks' => $it->masa_kerja_teks ?: ($bulanInt . ' Bulan'),
                'basic_salary' => $basic,
                'nilai_thr' => $nilai,
                'basic_fmt' => $this->rp($basic),
                'nilai_fmt' => $this->rp($nilai),
                'terbilang' => $this->terbilangRupiah($nilai),
                'kategori' => $kategori,
                'rumus' => $rumus,
                'catatan' => $it->catatan,
                'is_manual' => (bool) $it->is_manual,
            ];
        })->values();

        if ($items->isEmpty()) {
            abort(404, 'Tidak ada data karyawan yang berhak untuk dicetak.');
        }

        $totalThr = round((float) $items->sum('nilai_thr'), 2);
        $totalBasic = round((float) $items->sum('basic_salary'), 2);

        $summary = [
            'total_karyawan' => $items->count(),
            'total_basic_fmt' => $this->rp($totalBasic),
            'total_thr_fmt' => $this->rp($totalThr),
            'total_terbilang' => $this->terbilangRupiah($totalThr),
        ];

        $data = [
            'items' => $items,
            'summary' => $summary,
            'period' => $period,
            'tahun' => $tahun,
            'tglJatuhTempo' => $jatuhTempo->translatedFormat('d F Y'),
            'bulanSurat' => $jatuhTempo->translatedFormat('F'),
            'polos' => $polos,
            'tampilDesimal' => $this->tampilDesimal,
            'nomorSurat' => $nomorSurat,
            'profil' => $this->companyProfile(),
            'ttd' => $this->resolveTandaTangan(),
            'logo' => $polos ? null : $this->assetDataUri('assets/img/inixs'),
            'background' => $polos ? null : $this->assetDataUri('assets/surat/backgrounds/kops'),
        ];

        $view = $mode === 'rekap' ? 'HR.thr.pdf_rekap' : 'HR.thr.pdf_surat';
        $pdf = Pdf::loadView($view, $data)->setPaper('a4', 'portrait');
        $suffix = $polos ? '_Polos' : '';
        $slug = Str::slug($period->nama_periode, '_');

        if ($mode === 'per_orang') {
            $nama = Str::slug($items->first()['nama_lengkap'], '_');
            return $pdf->download("Surat_THR_{$nama}_{$slug}{$suffix}.pdf");
        }

        if ($mode === 'semua_surat') {
            return $pdf->download("Surat_THR_Per_Karyawan_{$slug}{$suffix}.pdf");
        }

        return $pdf->download("Surat_Rekap_THR_{$slug}{$suffix}.pdf");
    }

    private function resolveTandaTangan(): array
    {
        $user = Auth::user();

        $namaPembuat = '';
        $jabatanPembuat = '';

        if ($user) {
            $namaPembuat = (string) ($user->nama_lengkap ?? $user->name ?? $user->nama ?? $user->username ?? $user->email ?? '');

            $karyawanUser = $user->karyawan ?? null;
            if (!$karyawanUser instanceof karyawan && $namaPembuat !== '') {
                $karyawanUser = karyawan::whereRaw('LOWER(TRIM(nama_lengkap)) = ?', [mb_strtolower(trim($namaPembuat))])->first();
            }

            if ($karyawanUser instanceof karyawan) {
                $namaPembuat = $karyawanUser->nama_lengkap ?: $namaPembuat;
                $jabatanPembuat = (string) ($karyawanUser->jabatan ?? '');
            }
        }

        $gm = $this->findKaryawanByJabatan(['gm', 'general manager']);
        $dirut = $this->findKaryawanByJabatan(['direktur utama']);

        return [
            [
                'peran' => 'Dibuat oleh,',
                'nama' => $namaPembuat,
                'jabatan' => $jabatanPembuat,
            ],
            [
                'peran' => 'Disetujui oleh,',
                'nama' => $gm?->nama_lengkap ?? '',
                'jabatan' => $gm?->jabatan ?? 'GM',
            ],
            [
                'peran' => 'Disetujui oleh,',
                'nama' => $dirut?->nama_lengkap ?? '',
                'jabatan' => $dirut?->jabatan ?? 'Direktur Utama',
            ],
        ];
    }

    private function findKaryawanByJabatan(array $jabatanList): ?karyawan
    {
        $placeholders = implode(',', array_fill(0, count($jabatanList), '?'));

        return karyawan::query()
            ->whereRaw("LOWER(TRIM(jabatan)) IN ({$placeholders})", $jabatanList)
            ->where('status_aktif', '1')
            ->orderBy('id')
            ->first();
    }

    private function companyProfile(): array
    {
        return [
            'nama' => 'INIXINDO',
            'unit' => 'Human Resources Department',
            'alamat' => 'Alamat lengkap perusahaan',
            'kontak' => 'Telp. (000) 0000000  |  Email: hrd@perusahaan.co.id',
            'kota' => '.....................',
        ];
    }

    private function assetDataUri(string $basePath): ?string
    {
        $basePath = preg_replace('/\.(png|jpe?g)$/i', '', $basePath);

        foreach (['png', 'jpg', 'jpeg'] as $ext) {
            $path = public_path("{$basePath}.{$ext}");
            if (is_file($path)) {
                $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';
                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
            }
        }

        return null;
    }

    private function terbilangRupiah(float $angka): string
    {
        $angka = round(abs($angka), 2);
        $rupiah = (int) floor($angka);
        $sen = $this->tampilDesimal ? (int) round(($angka - $rupiah) * 100) : 0;

        $teks = $rupiah === 0 ? 'Nol' : ucfirst(trim($this->terbilang($rupiah)));
        $teks .= ' rupiah';

        if ($sen > 0) {
            $teks .= ' ' . trim($this->terbilang($sen)) . ' sen';
        }

        return $teks;
    }

    private function terbilang(int $angka): string
    {
        $huruf = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        if ($angka < 12) {
            return $huruf[$angka];
        }
        if ($angka < 20) {
            return $this->terbilang($angka - 10) . ' belas';
        }
        if ($angka < 100) {
            return $this->terbilang(intdiv($angka, 10)) . ' puluh' . ($angka % 10 ? ' ' . $this->terbilang($angka % 10) : '');
        }
        if ($angka < 200) {
            return 'seratus' . ($angka - 100 ? ' ' . $this->terbilang($angka - 100) : '');
        }
        if ($angka < 1000) {
            return $this->terbilang(intdiv($angka, 100)) . ' ratus' . ($angka % 100 ? ' ' . $this->terbilang($angka % 100) : '');
        }
        if ($angka < 2000) {
            return 'seribu' . ($angka - 1000 ? ' ' . $this->terbilang($angka - 1000) : '');
        }
        if ($angka < 1000000) {
            return $this->terbilang(intdiv($angka, 1000)) . ' ribu' . ($angka % 1000 ? ' ' . $this->terbilang($angka % 1000) : '');
        }
        if ($angka < 1000000000) {
            return $this->terbilang(intdiv($angka, 1000000)) . ' juta' . ($angka % 1000000 ? ' ' . $this->terbilang($angka % 1000000) : '');
        }
        if ($angka < 1000000000000) {
            return $this->terbilang(intdiv($angka, 1000000000)) . ' miliar' . ($angka % 1000000000 ? ' ' . $this->terbilang($angka % 1000000000) : '');
        }

        return $this->terbilang(intdiv($angka, 1000000000000)) . ' triliun' . ($angka % 1000000000000 ? ' ' . $this->terbilang($angka % 1000000000000) : '');
    }

    private function potong(float $angka): float
    {
        $angka = round($angka, 2);
        return $angka >= 0 ? floor($angka) : ceil($angka);
    }

    private function rp(float $angka): string
    {
        if ($this->tampilDesimal) {
            return number_format(round($angka, 2), 2, ',', '.');
        }

        return number_format($this->potong($angka), 0, ',', '.');
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
            $calcMonths = 0;

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

            $nilaiThrSistem = $this->hitungNilai($basicSalary, (float) $calcMonths, 12.0);

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
                    'basic_salary' => round($basicSalary, 2),
                    'nilai_thr_sistem' => $nilaiThrSistem,
                    'nilai_thr_final' => $nilaiThrSistem,
                    'is_manual' => false,
                    'catatan' => null,
                ],
            );
        }
    }
}