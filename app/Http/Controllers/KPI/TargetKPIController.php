<?php

namespace App\Http\Controllers\KPI;

use App\Http\Controllers\Controller;
use App\Models\detailPersonKPI;
use App\Models\DetailTargetKPI;
use App\Models\formPenilaian;
use App\Models\karyawan;
use App\Models\nilaiKPI;
use App\Models\kategoriKPI;
use App\Models\targetKPI;
use App\Models\shareForm;
use App\Models\tipeKategoriTabel;
use App\Models\DataTarget;
use App\Models\AbsensiKaryawan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

// Service & Export (Single Responsibility)
use App\Exports\KpiTargetTemplateExport;
use App\Imports\KpiTargetImport;
use App\Exports\KPI\MonitoringKPIExport;
use App\Exports\KPI\DepartemenKPIExport;
use App\Services\KPI\Dashboard\ExecutiveAnalyticsService;
use App\Services\KPI\Dashboard\PerformanceDashboardService;
use App\Services\KPI\Dashboard\OverviewDashboardService;

// Trait
use App\Traits\KPIFormatTrait;
use App\Traits\KPIResolverTrait;

// support
use App\Support\KPI\PanduanData;

class TargetKPIController extends Controller
{
    use KPIFormatTrait, KPIResolverTrait;

    public function kpiIndex()
    {
        $daftarKaryawan = karyawan::where('status_aktif', '1')
            ->whereNot('jabatan', 'Outsource')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->whereNotNull('nip')
            ->whereNot('divisi', 'Direksi')
            ->get();

        return view('KPIdata.TargetDivisi.index', compact('daftarKaryawan'));
    }

    public function getKaryawanByJabatan(Request $request)
    {
        $jabatanList = is_array($request->input('jabatan', [])) ? $request->input('jabatan', []) : [$request->input('jabatan', [])];

        $karyawan = karyawan::whereIn('jabatan', $jabatanList)
            ->where('status_aktif', '1')->whereNot('jabatan', 'Outsource')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')->whereNot('jabatan', 'Pilih Jabatan')
            ->whereNotNull('nip')->whereNot('divisi', 'Direksi')
            ->select('id', 'nama_lengkap', 'jabatan')
            ->get()
            ->map(fn($k) => ['id' => $k->id, 'text' => $k->nama_lengkap . ' (' . $k->jabatan . ')']);

        return response()->json($karyawan);
    }

    public function getAssistantRoutesByJabatan(Request $request)
    {
        $jabatanList = is_array($request->input('jabatan', [])) ? $request->input('jabatan', []) : [$request->input('jabatan', [])];

        if (empty($jabatanList)) return response()->json([]);

        $jabatanList = array_map(fn($jabatan) => trim(strtolower($jabatan)), $jabatanList);

        $routeMapping = [
            'gm' => [
                'pemasukan kotor',
                'pemasukan bersih',
                'target penjualan project tahunan',
                'kepuasan pelanggan',
                'rasio biaya operasional terhadap revenue',
                'performa kpi departemen'
            ],

            'customer care' => [
                'peserta puas dengan pelayanan dan fasilitas training',
                'dorong inovasi pelayanan',
                'penanganan komplain perseta',
                'report persiapan kelas'
            ],

            'finance & accounting' => [
                'outstanding',
                'inisiatif efisiensi keuangan',
                'mengurangi manual work dan error',
                'laporan analisis keuangan',
                'pencairan biaya operasional',
                'penyelesaian tagihan perusahaan',
                'akurasi pencatatan masuk'
            ],

            'hrd' => [
                'pelaksanaan kegiatan karyawan',
                'pengeluaran biaya karyawan',
                'administrasi karyawan'
            ],

            'driver' => [
                'perbaikan kendaraan',
                'report kondisi kendaraan',
                'kontrol pengeluaran transportasi',
                'feedback kenyamanan berkendaran'
            ],

            'office boy' => [
                'feedback kebersihan dan kenyamanan',
                'penyelesaian tugas harian'
            ],

            'koordinator itsm' => [
                'meningkatkan kepuasan dan loyalitas peserta/client',
                'availability sistem internal kritis',
                'persentase gap kompetensi tim terhadap standar skill'
            ],

            'programmer' => [
                'ketepatan waktu penyelesaian fitur',
                'mengukur kualitas aplikasi agar minim bug'
            ],

            'tim digital' => [
                'konsistensi campaign digital',
            ],

            'project administrator & business support' => [
                'efektifitas digital marketing',
                'timeline webinar'
            ],

            'technical support' => [
                'keberhasilan support memenuhi sla',
                'kualitas layanan exam'
            ],

            'instruktur' => [
                'presentase kinerja instruktur',
                'kepuasan peserta pelatihan',
                'upseling lanjutan materi',
                'sertifikasi kompetensi internal',
                'pelatihan kompetensi eksternal'
            ],

            'education manager' => [
                'pengembangan kurikulum pelatihan',
                'peningkatan knowledge sharing',
                'peningkatan kontribusi pelatihan',
                'evaluasi kinerja instruktur',
                'pembuatan artikel'
            ],

            'sales' => [
                'target penjualan tahunan',
                'biaya akuisisi perclient',
                'peningkatan kemampuan kompetensi sales'
            ],

            'spv sales' => [
                'meningkatkan revenue perusahaan',
                'customer acquisition cost',
                'evaluasi kinerja sales',
                'pendapatan penjualan project',
                'leads project'
            ],

            'adm sales' => [
                'laporan mom',
                'akurasi kelengkapan data penjualan',
                'todo administrasi'
            ],

            'admin holding' => [
                'ketepatan waktu po',
                'kualitas dokumentasi support dan proctor'
            ],
        ];

        $kombinasiIT = ['programmer', 'tim digital', 'technical support'];
        $kombinasiSales = ['sales', 'spv sales', 'adm sales'];

        $availableRoutes = [];
        if (count(array_intersect($jabatanList, $kombinasiIT)) === 3) {
            $availableRoutes = ['kepuasan client itsm', 'inovation adaption rate', 'persentase gap kompetensi tim terhadap standar skill'];
        } elseif (count(array_intersect($jabatanList, $kombinasiSales)) === 3) {
            $availableRoutes = ['peningkatan kemampuan kompetensi sales'];
        } else {
            foreach ($jabatanList as $jabatan) {
                if (isset($routeMapping[$jabatan])) $availableRoutes = array_merge($availableRoutes, $routeMapping[$jabatan]);
            }
            $availableRoutes = array_unique($availableRoutes);
        }

        $dataTargets = DataTarget::whereIn(DB::raw('LOWER(asistant_route)'), $availableRoutes)
            ->get(['asistant_route', 'jangka_target', 'tipe_target', 'nilai_target']);

        return response()->json($dataTargets);
    }

    public function cleaningDatabase()
    {
        try {
            DB::beginTransaction();
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('target_k_p_i_s')->truncate();
            DB::table('detail_target_k_p_i_s')->truncate();
            DB::table('detail_person_k_p_i_s')->truncate();
            DB::table('data_targets')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            DB::commit();

            return redirect()->back()->with('success', 'Seluruh data database berhasil dibersihkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membersihkan database: ' . $e->getMessage());
        }
    }

    public function getDataTargetByRoute(Request $request)
    {
        $route = $request->query('route');
        if (!$route) return response()->json(['error' => 'Parameter route diperlukan'], 400);

        $dataTarget = DataTarget::where('asistant_route', $route)->first();
        if (!$dataTarget) return response()->json(['error' => 'Data target tidak ditemukan'], 404);

        return response()->json($dataTarget);
    }

    public function createTarget(Request $request)
    {
        $validated = $request->validate([
            'id_pembuat'       => 'required',
            'judul_kpi'        => 'required|string',
            'deskripsi_kpi'    => 'nullable|string',
            'jabatan'          => 'required|array',
            'jabatan.*'        => 'required|string|distinct',
            'karyawan'         => 'nullable|array',
            'karyawan.*'       => 'required|integer|distinct',
            'asistant_route'   => 'required|string',
            'detail_jangka'    => 'required',
        ]);

        $dataTarget = DataTarget::where('asistant_route', $validated['asistant_route'])->first();
        if (!$dataTarget) return response()->json(['message' => 'Konfigurasi target tidak ditemukan'], 404);

        $jabatans = array_unique($validated['jabatan']);
        return DB::transaction(function () use ($validated, $dataTarget, $jabatans) {
            $createTarget = targetKPI::create([
                'id_pembuat'     => $validated['id_pembuat'],
                'id_data_target' => $dataTarget->id,
                'judul'          => $validated['judul_kpi'],
                'deskripsi'      => $validated['deskripsi_kpi'] ?? null,
                'status'         => '0',
            ]);

            $detail_jangka_value = ($dataTarget->jangka_target === 'Tahunan' && !empty($validated['detail_jangka']))
                ? $validated['detail_jangka'] : null;

            foreach ($jabatans as $jabatan) {
                $query = $this->baseKaryawanAktif()->where('jabatan', $jabatan);

                // Jika karyawan dipilih manual, batasi ke yang dipilih
                if (!empty($validated['karyawan'])) {
                    $query->whereIn('id', $validated['karyawan']);
                }

                $karyawanList = $query->get(['id', 'divisi']);

                if ($karyawanList->isEmpty()) {
                    continue; // atau throw/log jika ingin dianggap error
                }

                // Kelompokkan per divisi supaya divisi dan karyawan selalu konsisten
                foreach ($karyawanList->groupBy('divisi') as $divisi => $anggota) {
                    $detailStore = DetailTargetKPI::create([
                        'id_targetKPI'   => $createTarget->id,
                        'jabatan'        => $jabatan,
                        'divisi'         => $divisi,
                        'id_data_target' => $dataTarget->id,
                        'jangka_target'  => $dataTarget->jangka_target,
                        'detail_jangka'  => $detail_jangka_value,
                        'tipe_target'    => $dataTarget->tipe_target,
                        'nilai_target'   => $dataTarget->nilai_target,
                    ]);

                    foreach ($anggota->pluck('id')->unique() as $karyawanId) {
                        detailPersonKPI::create([
                            'id_target'       => $createTarget->id,
                            'detailTargetKey' => $detailStore->id,
                            'id_karyawan'     => $karyawanId,
                        ]);
                    }
                }
            }

            return response()->json(['message' => 'Target berhasil dibuat', 'data' => ['id_target' => $createTarget->id]], 201);
        });
    }

    public function updateGapKompetensi(Request $request)
    {
        $data = $request->input('data');
        if (empty($data)) {
            return response()->json(['status' => false, 'message' => 'Data kosong'], 400);
        }

        foreach ($data as $item) {
            $idKaryawan = $item['id_karyawan'] ?? null;
            $detailTargetKey = $item['detailTargetKey'] ?? null;

            if (!$idKaryawan || !$detailTargetKey) {
                continue;
            }

            $detailTarget = \App\Models\DetailTargetKPI::find($detailTargetKey);
            $idTarget = $detailTarget ? $detailTarget->id_targetKPI : null;

            if (!$idTarget) {
                continue;
            }

            detailPersonKPI::updateOrCreate(
                [
                    'id_karyawan'     => $idKaryawan,
                    'detailTargetKey' => $detailTargetKey
                ],
                [
                    'presentase_kemampuan' => $item['kemampuan'] ?? 0,
                    'presentase_standar'   => $item['standar'] ?? 100,
                    'id_target'            => $idTarget
                ]
            );
        }

        return response()->json(['status' => true, 'message' => 'Data berhasil diupdate']);
    }

    public function hapusTarget($id)
    {
        $target = targetKPI::with('detailTargetKPI.detailPersonKPI')->find($id);
        if (!$target) return response()->json(['message' => 'Target tidak ditemukan'], 404);

        foreach ($target->detailTargetKPI as $detail) $detail->detailPersonKPI()->delete();
        $target->detailTargetKPI()->delete();
        $target->delete();

        return response()->json(['message' => 'Berhasil menghapus target']);
    }

    public function manualValue(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'manual_value' => 'nullable|string',
            'biaya_gaji_tahunan' => 'nullable|numeric',
            'biaya_bpjs_tahunan' => 'nullable|numeric',
            'biaya_rekrutmen_tahunan' => 'nullable|numeric',
            'manual_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $detail = DetailTargetKPI::where('id_targetKPI', $request->id)->first();
        if (!$detail) return response()->json(['success' => false, 'message' => 'Detail Target KPI tidak ditemukan'], 404);

        $existingDocument = $detail->manual_document;
        $manualValue = $request->manual_value;

        if (!is_null($request->biaya_gaji_tahunan) || !is_null($request->biaya_bpjs_tahunan) || !is_null($request->biaya_rekrutmen_tahunan)) {
            $manualValue = (int) ($request->biaya_gaji_tahunan ?? 0) . ',' . (int) ($request->biaya_bpjs_tahunan ?? 0) . ',' . (int) ($request->biaya_rekrutmen_tahunan ?? 0);
        }

        $updateData = ['manual_value' => $manualValue];

        if ($request->hasFile('manual_document')) {
            if ($existingDocument && Storage::disk('public')->exists($existingDocument)) {
                Storage::disk('public')->delete($existingDocument);
            }
            $updateData['manual_document'] = $request->file('manual_document')->store('manual_documents', 'public');
        }

        $detail->update($updateData);

        return response()->json([
            'success' => true, 'message' => 'Berhasil memasukkan data manual',
            'data' => ['id' => $detail->id, 'manual_value' => $detail->manual_value, 'manual_document' => $detail->manual_document]
        ]);
    }

    // ============================================
    // DASHBOARD OVERVIEW & ANALYTICS DELEGATION
    // ============================================

    public function getProgressDashboard(Request $request, OverviewDashboardService $overviewService)
    {
        $totalStart = microtime(true);

        $user = auth()->user();
        $cacheKey = "progress_dashboard_{$user->id}_{$request->idUser}_{$request->typeGet}_" . now()->year;

        $data = \Illuminate\Support\Facades\Cache::remember($cacheKey, 120, function () use ($overviewService, $user, $request) {
            return $overviewService->getProgressDashboardData($user, $request->idUser, $request->typeGet);
        });

        $executionTime = round((microtime(true) - $totalStart) * 1000, 2);
        Log::info("Waktu Proses getProgressDashboard: {$executionTime} ms | user_id: {$user->id}");

        $data['execution_time_ms'] = $executionTime;
        return response()->json($data);
    }

    public function getChartStatistics(Request $request, OverviewDashboardService $overviewService)
    {
        $user = auth()->user();
        $data = $overviewService->getChartStatisticsData(
            $user ? trim($user->jabatan) : null,
            $request->jabatan ? trim($request->jabatan) : null,
            $request->tahun ?? date('Y'),
            $request->id_target ?? null,
            $request->bulan ?? null
        );
        return response()->json($data);
    }

    public function personalIndex($id = null)
    {
        $targetId = $id ?? auth()->user()->id;
        return view('KPIdata.TargetSubDivisi.overviewKaryawan', compact('targetId'));
    }

    public function getDataOverviewPersonal(Request $request, OverviewDashboardService $overviewService)
    {
        $karyawanId = $request->id_karyawan ?? auth()->id(); 
        $tahunFilter = (int) ($request->tahun ?? now()->year);

        $data = $overviewService->getPersonalOverviewData($karyawanId, $tahunFilter);

        return isset($data['error'])
            ? response()->json(['success' => false, 'message' => $data['error']], $data['code'])
            : response()->json($data);
    }

    public function kpiOverview()
    {
        $userKaryawan = karyawan::where('id', Auth::id())->first();
        $departments = karyawan::where('divisi', '!=', 'Direksi')->whereNotNull('divisi')->distinct()->pluck('divisi')->values();
        
        return view('KPIdata.TargetDivisi.overview', [
            'departments' => $departments,
            'divisi' => $userKaryawan->divisi ?? null,
            'jabatan' => $userKaryawan->jabatan ?? null,
            'user_id' => Auth::id()
        ]);
    }

    public function getDataOverview(Request $request, OverviewDashboardService $overviewService)
    {
        if (!$request->divisi || !$request->tahun) {
            return response()->json(['message' => 'Divisi dan tahun harus diisi'], 400);
        }

        $personId = $request->id_karyawan ?? auth()->id();

        $data = $overviewService->getDepartmentOverviewData($request->divisi, $request->tahun, $personId);
        
        return response()->json($data);
    }

    public function getDataTarget(Request $request)
    {
        $totalStart = microtime(true);

        $user = auth()->user();
        $idUser = $request->idUser;
        $typeGet = $request->typeGet;

        $targetUser = (filled($idUser) && filled($typeGet)) ? karyawan::find($idUser) : $user;
        if (!$targetUser) {
            return response()->json(['message' => 'Karyawan tidak ditemukan'], 404);
        }

        $divisiUser = $targetUser->divisi;

        $superRoles = ['GM', 'HRD', 'Direktur Utama', 'Direktur'];
        $divisionHeadRoles = ['SPV Sales', 'Koordinator ITSM', 'Education Manager'];

        $isSuper = in_array($user->jabatan, $superRoles);
        $isDivisionHead = in_array($user->jabatan, $divisionHeadRoles);

        if ($isDivisionHead) {
            $divisiUser = karyawan::where('jabatan', $user->jabatan)
                ->whereNotNull('divisi')
                ->where('divisi', '!=', '')
                ->distinct()
                ->pluck('divisi')
                ->first();
                
            if (!$divisiUser) {
                return response()->json(['message' => 'Divisi tidak ditemukan untuk jabatan Anda'], 400);
            }
        }

        $query = targetKPI::with([
            'karyawan', 
            'detailTargetKPI.dataTarget', 
            'detailTargetKPI.detailPersonKPI'
        ])->whereYear('created_at', now()->year);

        if (filled($idUser) && filled($typeGet)) {
            $requestedUser = karyawan::find($idUser);

            if (!$requestedUser) {
                return response()->json(['message' => 'Karyawan tidak ditemukan'], 404);
            }

            if (!$isSuper && !$isDivisionHead) {
                if ($requestedUser->id != $user->id) {
                    return response()->json(['message' => 'Anda tidak memiliki akses untuk melihat data karyawan ini'], 403);
                }
            } elseif ($isDivisionHead && !$isSuper) {
                if ($requestedUser->divisi !== $divisiUser) {
                    return response()->json(['message' => 'Anda tidak memiliki akses untuk melihat data divisi lain'], 403);
                }
            }

            $query->whereHas('detailTargetKPI.detailPersonKPI', fn($q) => $q->where('id_karyawan', $idUser));

        } else {
            if ($isSuper) {
                // Super melihat semua
            } elseif ($isDivisionHead) {
                $query->whereHas('detailTargetKPI', fn($q) => $q->where('divisi', $divisiUser));
            } else {
                $query->whereHas('detailTargetKPI.detailPersonKPI', fn($q) => $q->where('id_karyawan', $user->id));
            }
        }

        $searchKeyword = $request->input('search.value') ?? $request->input('search');
        if (filled($searchKeyword)) {
            $query->where(function ($q) use ($searchKeyword) {
                $q->where('judul', 'like', "%{$searchKeyword}%")
                  ->orWhere('deskripsi', 'like', "%{$searchKeyword}%")
                  ->orWhereHas('karyawan', fn($sq) => $sq->where('nama_lengkap', 'like', "%{$searchKeyword}%"));
            });
        }

        $perPage = $request->input('per_page', 10);
        $paginatedList = $query->paginate($perPage);

        $mappedDetail = $paginatedList->getCollection()->map(function ($item) use ($idUser) {
            $detail = $item->detailTargetKPI->first();
            if (!$detail) return null;

            $personId = filled($idUser) ? (int) $idUser : null;
            $progress = $this->resolveProgress($item, $personId);

            $totalPeserta = $item->detailTargetKPI->flatMap(function ($detailItem) {
                return $detailItem->detailPersonKPI->pluck('id_karyawan');
            })->unique()->count();

            return [
                'id' => $item->id,
                'pembuat' => $item->karyawan?->nama_lengkap ?? 'Tidak Diketahui',
                'id_pembuat' => $item->id_pembuat,
                'judul' => $item->judul ?? '-',
                'deskripsi' => $item->deskripsi ?? '-',
                'jabatan' => $item->detailTargetKPI->pluck('jabatan')->unique()->values(),
                'divisi' => $item->detailTargetKPI->pluck('divisi')->unique()->values(),
                'asistant_route' => $detail->dataTarget?->asistant_route ?? 'Tidak Ada Route',
                'jangka_target' => $detail->dataTarget?->jangka_target ?? '-',
                'detail_jangka' => $detail->detail_jangka ?? '-',
                'tipe_target' => $detail->dataTarget?->tipe_target ?? '-',
                'nilai_target' => $detail->dataTarget?->nilai_target ?? 0,
                'manual_value' => $detail->manual_value ?? null,
                'status' => $item->status ?? '0',
                'created_at' => $item->created_at?->format('Y-m-d H:i:s') ?? '-',
                'tenggat_waktu' => $this->formatTenggatWaktuExport($detail->dataTarget?->jangka_target ?? '', $detail->detail_jangka ?? ''),
                'progress' => $progress,
                'total_peserta' => $totalPeserta,
            ];
        })->filter()->values();

        $paginatedList->setCollection($mappedDetail);

        $dataJabatan = $isSuper
            ? karyawan::whereNotIn('jabatan', ['Direktur Utama', 'Direktur'])->distinct()->pluck('jabatan')
            : karyawan::where('divisi', $divisiUser)->whereNotIn('jabatan', ['Direktur Utama', 'Direktur'])->distinct()->pluck('jabatan');

        $executionTime = round((microtime(true) - $totalStart) * 1000, 2);
        Log::info("Waktu Proses getDataTarget: {$executionTime} ms | Total: {$paginatedList->total()}");

        return response()->json([
            'detail' => $paginatedList->items(),
            'pagination' => [
                'current_page' => $paginatedList->currentPage(),
                'last_page'    => $paginatedList->lastPage(),
                'total'        => $paginatedList->total(),
                'per_page'     => $paginatedList->perPage(),
            ],
            'jabatan_list' => $dataJabatan,
            'routes' => DataTarget::select('asistant_route', 'jangka_target', 'tipe_target', 'nilai_target')->get(),
            'execution_time_ms' => $executionTime,
        ]);
    }

    public function detailData(Request $request)
    {
        $idTarget = $request->id;
        $personId = $request->idUser ?? null;

        $user = auth()->user();
        $userJabatan = strtolower($user->jabatan ?? '');
        $isUserKoordinatorItsm = str_contains($userJabatan, 'koordinator itsm') || str_contains($userJabatan, 'koordinator');

        $target = targetKPI::find($idTarget);
        $detailTarget = $target?->detailTargetKPI->first();
        $routeName = $detailTarget?->dataTarget?->asistant_route ?? '';
        $isGapKompetensiTarget = ($routeName === 'persentase gap kompetensi tim terhadap standar skill');

        $query = targetKPI::with(['karyawan', 'detailTargetKPI.detailPersonKPI.karyawan', 'detailTargetKPI.dataTarget'])->where('id', $idTarget);
        
        $shouldFilterByPerson = !($isUserKoordinatorItsm && $isGapKompetensiTarget);
        
        if ($personId !== null && $shouldFilterByPerson) {
            $query->whereHas('detailTargetKPI.detailPersonKPI', fn($q) => $q->where('id_karyawan', $personId));
        }

        $detailList = $query->get();

        $data = [
            'detail' => $detailList->map(function ($itemDetail) use ($personId, $isUserKoordinatorItsm, $isGapKompetensiTarget) {
                $detail = $itemDetail->detailTargetKPI->first();
                if (!$detail) return null;

                if ($isUserKoordinatorItsm && $isGapKompetensiTarget) {
                    $divisi = $detail->divisi;
                    
                    $karyawanDiDivisi = \App\Models\karyawan::where('divisi', $divisi)
                        ->where('status_aktif', '1')
                        ->whereNot('jabatan', 'Outsource')
                        ->where('kode_karyawan', 'NOT LIKE', 'OL%')
                        ->whereNot('jabatan', 'Pilih Jabatan')
                        ->whereNotNull('nip')
                        ->where('divisi', '!=', 'Direksi')
                        ->get();

                    $existingGaps = \App\Models\detailPersonKPI::where('detailTargetKey', $detail->id)
                        ->get()
                        ->keyBy('id_karyawan');

                    $karyawanCollection = $karyawanDiDivisi->map(function($k) use ($existingGaps, $detail) {
                        $existing = $existingGaps->get($k->id);
                        return [
                            'id' => $existing ? $existing->id : null, 
                            'id_karyawan' => $k->id,
                            'detailTargetKey' => $detail->id,
                            'nama_lengkap' => $k->nama_lengkap,
                            'jabatan' => $k->jabatan,
                            'presentase_kemampuan' => $existing ? (float)$existing->presentase_kemampuan : 0,
                            'presentase_standar' => $existing ? (float)$existing->presentase_standar : 100,
                        ];
                    })->values();
                } else {
                    $karyawanCollection = $itemDetail->detailTargetKPI->flatMap(function ($detailItem) use ($personId) {
                        return $detailItem->detailPersonKPI->where('id_karyawan', $personId);
                    })->map(fn($person) => [
                        'id' => $person->id, 
                        'id_karyawan' => $person->id_karyawan,
                        'nama_lengkap' => $person->karyawan->nama_lengkap ?? null,
                        'jabatan' => $person->karyawan->jabatan ?? null, 
                        'presentase_kemampuan' => $person->presentase_kemampuan ?? 0,
                        'presentase_standar' => $person->presentase_standar ?? 100,
                    ])->values();
                }

                $dataOutput = [
                    'pembuat' => $itemDetail->karyawan->nama_lengkap ?? null, 
                    'judul' => $itemDetail->judul,
                    'condition' => $detail->dataTarget?->asistant_route, 
                    'deskripsi' => $itemDetail->deskripsi,
                    'jabatan_kpi' => $detail->jabatan, 
                    'divisi_kpi' => $detail->divisi,
                    'karyawan' => $karyawanCollection,
                    'jangka_target' => $detail->jangka_target, 
                    'detail_jangka' => $detail->detail_jangka,
                    'tipe_target' => $detail->tipe_target, 
                    'nilai_target' => $detail->nilai_target,
                    'tenggat_waktu' => $this->formatTenggatWaktuExport($detail->jangka_target ?? '', $detail->detail_jangka ?? ''),
                    'data_detail' => $this->getCalculationByRoute($itemDetail, ($isUserKoordinatorItsm && $isGapKompetensiTarget) ? null : $personId),
                ];

                return ['data' => $dataOutput];
            })->filter()->values(),
        ];

        return response()->json($data);
    }

    // Controller
    public function divisiDrilldown(Request $request)
    {
        $divisi = $request->query('divisi');
        $currentYear = now()->year;

        if (!$divisi) {
            return response()->json(['error' => 'Divisi wajib diisi'], 422);
        }

        $data = app(OverviewDashboardService::class)->getDivisiDrilldownData($divisi, $currentYear);

        return response()->json($data);
    }

    // ============================================
    // EXPORT FILE EXCEL & PDF
    // ============================================

    public function importTarget(Request $request)
    {
        try {
            $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:10240', 'skip_duplicate' => 'nullable|boolean', 'dry_run' => 'nullable|boolean']);
            $file = $request->file('file');
            $options = ['skip_duplicate' => $request->boolean('skip_duplicate'), 'dry_run' => $request->boolean('dry_run')];

            if ($options['dry_run']) {
                $import = new KpiTargetImport($options);
                Excel::toCollection($import, $file);
                $summary = $import->getSummary();
                return empty($summary['errors'])
                    ? response()->json(['success' => 'Preview Valid', 'summary' => $summary])
                    : response()->json(['errors' => ['preview' => array_slice($summary['errors'], 0, 20)]], 422);
            }

            DB::beginTransaction();
            $import = new KpiTargetImport($options);
            Excel::import($import, $file);
            $summary = $import->getSummary();

            if (!empty($summary['errors'])) {
                DB::rollBack();
                return response()->json(['errors' => ['file' => array_slice($summary['errors'], 0, 50)]], 422);
            }

            DB::commit();
            return response()->json(['success' => 'Import selesai', 'summary' => $summary], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['errors' => ['file' => [$e->getMessage()]]], 500);
        }
    }

    public function downloadTemplate()
    {
        $allRoutes = DataTarget::pluck('asistant_route')->unique()->sort()->values();
        // Gunakan pemetaan langsung untuk memotong kode panjang map di controller
    $routeMapping = [
        'gm' => [
            'pemasukan kotor',
            'pemasukan bersih',
            'kepuasan pelanggan',
            'target penjualan project tahunan',
            'rasio biaya operasional terhadap revenue',
            'performa kpi departemen'
        ],
        'customer care' => [
            'peserta puas dengan pelayanan dan fasilitas training',
            'dorong inovasi pelayanan',
            'penanganan komplain perseta',
            'report persiapan kelas'
        ],
        'finance & accounting' => [
            'outstanding',
            'inisiatif efisiensi keuangan',
            'mengurangi manual work dan error',
            'laporan analisis keuangan',
            'pencairan biaya operasional',
            'penyelesaian tagihan perusahaan',
            'akurasi pencatatan masuk'
        ],
        'hrd' => [
            'pelaksanaan kegiatan karyawan',
            'pengeluaran biaya karyawan',
            'administrasi karyawan'
        ],
        'driver' => [
            'perbaikan kendaraan',
            'report kondisi kendaraan',
            'kontrol pengeluaran transportasi',
            'feedback kenyamanan berkendaran'
        ],
        'office boy' => [
            'feedback kebersihan dan kenyamanan',
            'penyelesaian tugas harian'
        ],
        'koordinator itsm' => [
            'meningkatkan kepuasan dan loyalitas peserta/client',
            'availability sistem internal kritis',
            'persentase gap kompetensi tim terhadap standar skill'
        ],
        'programmer' => [
            'ketepatan waktu penyelesaian fitur',
            'mengukur kualitas aplikasi agar minim bug'
        ],
        'tim digital' => [
            'konsistensi campaign digital',
        ],
        'project administrator & business support' => [
            'efektifitas digital marketing',
            'timeline webinar'
        ],
        'technical support' => [
            'keberhasilan support memenuhi sla',
            'kualitas layanan exam'
        ],
        'instruktur' => [
            'presentase kinerja instruktur',
            'kepuasan peserta pelatihan',
            'upseling lanjutan materi',
            'sertifikasi kompetensi internal',
            'pelatihan kompetensi eksternal'
        ],
        'education manager' => [
            'pengembangan kurikulum pelatihan',
            'peningkatan knowledge sharing',
            'peningkatan kontribusi pelatihan',
            'evaluasi kinerja instruktur',
            'pembuatan artikel'
        ],
        'sales' => [
            'target penjualan tahunan',
            'biaya akuisisi perclient',
            'peningkatan kemampuan kompetensi sales'
        ],
        'spv sales' => [
            'meningkatkan revenue perusahaan',
            'customer acquisition cost',
            'evaluasi kinerja sales',
            'pendapatan penjualan project',
            'leads project'
        ],
        'adm sales' => [
            'laporan mom',
            'akurasi kelengkapan data penjualan',
            'todo administrasi'
        ],
        'admin holding' => [
            'ketepatan waktu po',
            'kualitas dokumentasi support dan proctor'
        ]
    ];
        return Excel::download(new KpiTargetTemplateExport($allRoutes, $routeMapping), 'template_import_kpi_' . date('Y-m-d') . '.xlsx');
    }

    public function exportMonitoringExcel(Request $request, OverviewDashboardService $overviewService)
    {
        try {
            $karyawanId = (int)($request->id_karyawan ?? Auth::id());
            $tahun      = (int)($request->tahun ?? now()->year);

            $karyawan = karyawan::find($karyawanId);
            
            if (!$karyawan) {
                return back()->withErrors(['export' => 'Data karyawan tidak ditemukan.']);
            }

            $data = $overviewService->getPersonalOverviewData($karyawanId, $tahun);

            $exportService = new MonitoringKPIExport(
                $data, 
                $karyawan->nama_lengkap, 
                $karyawan->jabatan ?? '-', 
                $tahun
            );
            $tmpPath = $exportService->generate();

            $fileName = 'KPI_' . str_replace(' ', '_', $karyawan->nama_lengkap) . '_' . $tahun . '.xlsx';

            return response()->download($tmpPath, $fileName)->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            Log::error('Export KPI Excel Failed: ' . $e->getMessage());
            return back()->withErrors(['export' => 'Gagal export Excel: ' . $e->getMessage()]);
        }
    }

    public function exportMonitoringPdf(Request $request, OverviewDashboardService $overviewService)
    {
        try {
            $karyawanId = (int)($request->id_karyawan ?? Auth::id());
            $tahun      = (int)($request->tahun ?? now()->year);

            $karyawan = karyawan::find($karyawanId);
            
            if (!$karyawan) {
                return back()->withErrors(['export' => 'Data karyawan tidak ditemukan.']);
            }

            $data = $overviewService->getPersonalOverviewData($karyawanId, $tahun);

            $fileName = 'KPI_Report_' . str_replace(' ', '_', $karyawan->nama_lengkap) . '_' . $tahun . '.pdf';

            $pdf = Pdf::loadView('KPIdata.export.export_pdf', [
                'data'     => $data,
                'karyawan' => $karyawan,
                'tahun'    => $tahun
            ]);

            $pdf->setPaper('A4', 'portrait');
            $pdf->setOption('isHtml5ParserEnabled', true);
            $pdf->setOption('isRemoteEnabled', true);

            return $pdf->download($fileName);
            
        } catch (\Exception $e) {
            Log::error('Export KPI PDF Failed: ' . $e->getMessage());
            return back()->withErrors(['export' => 'Gagal export PDF: ' . $e->getMessage()]);
        }
    }

    public function exportDeptExcel(Request $request, OverviewDashboardService $overviewService)
    {
        try {
            $divisi = $request->query('divisi');
            $tahun  = (int)($request->query('tahun') ?? now()->year);
            if (!$divisi) return back()->withErrors(['export' => 'Departemen belum dipilih.']);

            $data = $overviewService->getDepartmentOverviewData($divisi, $tahun);
            $exportService = new DepartemenKPIExport($data);
            $tmpPath = $exportService->generate();

            return response()->download($tmpPath, 'KPI_Dept_' . str_replace(' ', '_', $divisi) . '_' . $tahun . '.xlsx')->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->withErrors(['export' => 'Terjadi kesalahan saat export: ' . $e->getMessage()]);
        }
    }

    public function exportDeptPdf(Request $request, OverviewDashboardService $overviewService)
    {
        try {
            $divisi = $request->query('divisi');
            $tahun  = (int)($request->query('tahun') ?? now()->year);
            
            if (!$divisi) {
                return back()->withErrors(['export' => 'Departemen belum dipilih.']);
            }

            // Ambil data dari service
            $data = $overviewService->getDepartmentOverviewData($divisi, $tahun);
            
            // Tambahkan konteks untuk header di Blade PDF
            $data['nama_divisi'] = $divisi;
            $data['tahun']       = $tahun;

            $fileName = 'KPI_Dept_' . str_replace(' ', '_', $divisi) . '_' . $tahun . '.pdf';

            // Render ke PDF
            $pdf = Pdf::loadView('KPIdata.export.export_dept_pdf', [
                'data' => $data
            ]);

            $pdf->setPaper('A4', 'portrait');
            $pdf->setOption('isHtml5ParserEnabled', true);
            $pdf->setOption('isRemoteEnabled', true);

            return $pdf->download($fileName);
            
        } catch (\Exception $e) {
            Log::error('Export Dept PDF Failed: ' . $e->getMessage());
            return back()->withErrors(['export' => 'Gagal export PDF: ' . $e->getMessage()]);
        }
    }

    // ============================================
    // EXECUTIVE DASHBOARD
    // ============================================

    public function executiveDashboard()
    {
        $user = auth()->user();
        if (!in_array($user->jabatan, ['GM', 'HRD', 'Direktur Utama'])) abort(403, 'Akses khusus executive required');

        $divisiList = karyawan::whereNotNull('divisi')->where('divisi', '!=', '')->where('divisi', '!=', 'Direksi')->distinct()->pluck('divisi')->filter()->values();
        $jabatanList = karyawan::whereNotNull('jabatan')->where('jabatan', '!=', '')->whereNotIn('jabatan', ['Direktur Utama', 'Direktur', 'Outsource', 'Pilih Jabatan'])->where('kode_karyawan', 'NOT LIKE', 'OL%')->whereNotNull('nip')->where('divisi', '!=', 'Direksi')->distinct()->pluck('jabatan')->filter()->values();
        return view('HR.executive.dashboard', compact('divisiList', 'jabatanList'));
    }

    public function exportExecutiveReport(Request $request, ExecutiveAnalyticsService $analyticsService)
    {
        $user = auth()->user();
        if (!in_array($user->jabatan, ['GM', 'HRD', 'Direktur Utama'])) abort(403, 'Akses khusus executive required');

        $filters = $request->only(['divisi', 'jabatan', 'tahun']);
        // Data dapat diambil dari analyticsService (Misalnya dari summary Trend Data)
        $dataTrend = $analyticsService->getTrendData($filters);

        $pdf = Pdf::loadView('HR.executive.report-pdf', [
            'filters' => $filters, 'generated_at' => now(), 'user' => $user, 'summary' => $dataTrend['summary']
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Executive_Report_' . date('Ymd_His') . '.pdf');
    }

    public function getExecutiveTrend(Request $request, ExecutiveAnalyticsService $analyticsService)
    {
        $user = auth()->user();
        if (!in_array($user->jabatan, ['GM', 'HRD', 'Direktur Utama'])) abort(403, 'Akses khusus executive required');

        $filters = $request->validate(['divisi' => 'nullable|string', 'jabatan' => 'nullable|string', 'id_karyawan' => 'nullable|integer', 'tahun' => 'nullable|integer', 'granularity' => 'nullable|string']);
        return response()->json($analyticsService->getTrendData($filters));
    }

    public function getPredictiveAnalysis(Request $request, ExecutiveAnalyticsService $analyticsService)
    {
        return response()->json($analyticsService->getPredictiveAnalysisData($request->only(['divisi', 'jabatan', 'tahun'])));
    }

    public function getPotentialMatrixUnified(Request $request, ExecutiveAnalyticsService $analyticsService)
    {
        return response()->json($analyticsService->getMatrixData($request->only(['divisi', 'jabatan', 'tahun'])));
    }

    // ============================================
    // PERFORMANCE DASHBOARD
    // ============================================

    public function performanceDashboard()
    {
        $divisiList = karyawan::whereNotNull('divisi')->where('divisi', '!=', '')->where('divisi', '!=', 'Direksi')->distinct()->pluck('divisi')->filter()->values();
        $jabatanList = karyawan::whereNotNull('jabatan')->where('jabatan', '!=', '')->whereNotIn('jabatan', ['Direktur Utama', 'Direktur', 'Outsource', 'Pilih Jabatan'])->where('kode_karyawan', 'NOT LIKE', 'OL%')->whereNotNull('nip')->where('divisi', '!=', 'Direksi')->distinct()->pluck('jabatan')->filter()->values();
        return view('HR.performance.dashboard', compact('divisiList', 'jabatanList'));
    }

    public function getPerformanceDashboardData(Request $request, PerformanceDashboardService $performanceService)
    {
        $filters = [
            'tahun' => $request->input('tahun', now()->year), 'divisi' => $request->input('divisi'),
            'jabatan' => $request->input('jabatan'), 'search' => $request->input('search'),
        ];
        return response()->json($performanceService->getPerformanceData($filters));
    }

    public function getAssessment360DetailTab(Request $request)
    {
        $request->validate(['id_karyawan' => 'required|integer', 'tahun' => 'required|integer']);
        $id_karyawan = $request->input('id_karyawan');
        $tahun       = $request->input('tahun');

        $karyawan = karyawan::find($id_karyawan);
        if (!$karyawan) return response()->json(['message' => 'Karyawan tidak ditemukan'], 404);

        $formPenilaians = formPenilaian::with('karyawan')->where('id_karyawan', $id_karyawan)->where('tahun', $tahun)->get();

        if ($formPenilaians->isEmpty()) {
            return response()->json(['success' => false, 'message' => "Belum ada data penilaian 360 untuk tahun {$tahun}"], 404);
        }

        $groupedByQuartal = $formPenilaians->groupBy('quartal')->sortKeysDesc();
        $firstForm = $formPenilaians->first();

        $evaluated = [
            'nama' => optional($firstForm->karyawan)->nama_lengkap . ' - ' . (optional($firstForm->karyawan)->divisi ?? '-'),
            'id_karyawan' => $firstForm->id_karyawan, 'quartal' => $firstForm->quartal, 'tahun' => $firstForm->tahun,
            'catatan' => $firstForm->catatan ?? '-', 'kode_form' => $firstForm->kode_form,
        ];

        $dataAbsensi = AbsensiKaryawan::where('id_karyawan', $id_karyawan)->whereYear('created_at', $tahun)->get();
        $dataAbsen = [
            'sakit' => $dataAbsensi->where('keterangan', 'Sakit')->count(),
            'telat' => $dataAbsensi->where('keterangan', 'Telat')->count(),
            'izin'  => $dataAbsensi->where('keterangan', 'Izin')->count(),
        ];

        $kodeKategoriList = $formPenilaians->pluck('kode_kategori')->unique();
        $allKategoriKPIs = kategoriKPI::whereIn('kode_kategori', $kodeKategoriList)->get();
        $kodeFormList = $formPenilaians->pluck('kode_form')->unique();

        $allEvaluatorData = shareForm::with('evaluator')->where('id_evaluated', $id_karyawan)->whereIn('kode_form', $kodeFormList)->get();

        $evaluatorList = [];
        foreach ($allEvaluatorData as $evaluatorItem) {
            $nilaiKPIByEvaluator = nilaiKPI::where('id_evaluated', $id_karyawan)->whereIn('kode_form', $kodeFormList)
                ->where('id_evaluator', $evaluatorItem->id_evaluator)->where('jenis_penilaian', $evaluatorItem->jenis_penilaian)->get();

            $groupedByKategori = $nilaiKPIByEvaluator->groupBy('name_variabel');
            $listNilaiEvaluator = [];
            foreach ($allKategoriKPIs as $kategori) {
                $nilaiItem = $groupedByKategori->get($kategori->judul_kategori);
                $listNilaiEvaluator[] = ($nilaiItem && $nilaiItem->count() > 0)
                    ? ['pesan' => $nilaiItem->first()->pesan ?? '-', 'nilai' => $nilaiItem->first()->nilai ?? '-']
                    : ['pesan' => '-', 'nilai' => '-'];
            }

            $evaluatorList[] = ['nama' => optional($evaluatorItem->evaluator)->nama_lengkap . ' - ' . (optional($evaluatorItem->evaluator)->divisi ?? '-'), 'jenis_penilaian' => $evaluatorItem->jenis_penilaian ?? '-', 'nilai' => $listNilaiEvaluator];
        }

        $evaluatorList = collect($evaluatorList)->unique(fn($item) => $item['nama'] . $item['jenis_penilaian'])->values();

        $dataKriteria = $formPenilaians->groupBy(fn($item) => $item->kode_form . '|' . $item->nama_penilaian)->map(function ($groupedForms, $combinedKey) {
            [$kodeForm, $namaPenilaian] = explode('|', $combinedKey);
            $kategoriKPIs = $groupedForms->flatMap(fn($form) => kategoriKPI::where('kode_kategori', $form->kode_kategori)->get())->unique('judul_kategori')->values();

            $detailKriteria = $kategoriKPIs->map(fn($kategori) => [
                'sub_kriteria' => $kategori->judul_kategori, 'bobot' => $kategori->bobot, 'tipe_input' => $kategori->tipe_kategori,
                'detailTipeSubKriteria' => tipeKategoriTabel::where('id_kategori', $kategori->id)->get()->map(fn($tipe) => ['ket_sub_tipe' => $tipe->ket_tipe, 'nilai_ket_sub_tipe' => $tipe->nilai_ket_sub_tipe])->toArray()
            ]);
            return ['kriteria' => $namaPenilaian, 'kodeForm' => $kodeForm, 'detailKriteria' => $detailKriteria];
        })->values()->toArray();

        $persentaseJenis = ['General Manager' => 35, 'Manager/SPV/Team Leader (Atasan Langsung)' => 30, 'Rekan Kerja (Satu Divisi)' => 20, 'Pekerja (Beda Divisi)' => 10, 'Self Apprisial' => 5];
        $chartData = [];

        foreach ($groupedByQuartal as $quartal => $forms) {
            $kodeForms = $forms->pluck('kode_form')->unique();
            $evaluators = shareForm::where('id_evaluated', $id_karyawan)->whereIn('kode_form', $kodeForms)->get()->groupBy('jenis_penilaian');
            $totalSkor = 0;

            foreach ($evaluators as $jenis => $evalGroup) {
                if (!isset($persentaseJenis[$jenis])) continue;
                $skorJenis = 0;
                foreach ($allKategoriKPIs as $kategori) {
                    $nilaiPerEvaluator = [];
                    foreach ($evalGroup as $eval) {
                        $itemNilai = nilaiKPI::where('id_evaluator', $eval->id_evaluator)->where('id_evaluated', $id_karyawan)->where('kode_form', $eval->kode_form)->where('jenis_penilaian', $jenis)->where('name_variabel', $kategori->judul_kategori)->whereNotNull('nilai')->first();
                        if ($itemNilai && is_numeric($itemNilai->nilai)) $nilaiPerEvaluator[] = (float) $itemNilai->nilai;
                    }
                    $skorJenis += (count($nilaiPerEvaluator) > 0 ? array_sum($nilaiPerEvaluator) / count($nilaiPerEvaluator) : 0) * ($kategori->bobot / 100);
                }
                $totalSkor += ($skorJenis * $persentaseJenis[$jenis]) / 100;
            }
            $chartData[$quartal] = number_format($totalSkor, 2, '.', '');
        }

        return response()->json([
            'success' => true,
            'data' => [['evaluated' => $evaluated, 'dataAbsen' => $dataAbsen, 'data' => ['evaluator' => $evaluatorList, 'dataKriteria' => $dataKriteria], 'chart' => ['quartal' => $chartData, 'all' => []]]],
            'karyawan' => ['nama' => $karyawan->nama_lengkap, 'jabatan' => $karyawan->jabatan, 'divisi' => $karyawan->divisi, 'foto' => $karyawan->foto ? asset('storage/' . $karyawan->foto) : asset('assets/img/avatars/1.png')]
        ]);
    }

    public function getPanduan($key)
    {
        $semua = PanduanData::all();

        if (!array_key_exists($key, $semua)) {
            return response()->json([
                'html' => '<div class="alert alert-warning">Panduan tidak ditemukan.</div>'
            ]);
        }

        $html = view('KPIdata.panduan.panduan_data', ['key' => $key])->render();

        return response()->json([
            'html'  => $html,
            'title' => $semua[$key]['judul'] ?? ucwords(str_replace('_', ' ', $key)),
        ]);
    }

    private function panduanJabatanMap(): array
    {
        return [
            'gm' => [
                'label' => 'GM',
                'keys' => [
                    'rasio_biaya_operasional_terhadap_revenue',
                ],
            ],
            'customer care' => [
                'label' => 'Customer Care',
                'keys' => [
                    'peserta_puas_dengan_pelayanan_dan_fasilitas_training',
                    'dorong_inovasi_pelayanan',
                    'penanganan_komplain_peserta',
                    'report_persiapan_kelas',
                ],
            ],
            'finance & accounting' => [
                'label' => 'Finance & Accounting',
                'keys' => [
                    'inisiatif_efisiensi_keuangan',
                    'mengurangi_manual_work_dan_error',
                ],
            ],
            'hrd' => [
                'label' => 'HRD',
                'keys' => [
                    'pelaksanaan_kegiatan_karyawan',
                    'pengeluaran_biaya_karyawan',
                    'administrasi_karyawan',
                ],
            ],
            'koordinator itsm' => [
                'label' => 'Koordinator ITSM',
                'keys' => [
                    'meningkatkan_kepuasan_dan_loyalitas_peserta',
                    'availability_sistem_internal_kritis',
                    'persentase_gap_kompetensi',
                ],
            ],
            'programmer' => [
                'label' => 'Programmer',
                'keys' => [
                    'ketepatan_waktu_penyelesaian_fitur',
                    'mengukur_kualitas_aplikasi_agar_minim_bug',
                ],
            ],
            'tim digital' => [
                'label' => 'Tim Digital',
                'keys' => [
                    'konsistensi_campaign_digital',
                ],
            ],
            'technical support' => [
                'label' => 'Technical Support',
                'keys' => [
                    'tingkat_keberhasilan_support_memenuhi_sla',
                    'kualitas_layanan_exam',
                ],
            ],
            'adm sales' => [
                'label' => 'Adm Sales',
                'keys' => [
                    'laporan_mom',
                    'akurasi_kelengkapan_data_penjualan',
                    'todo_administrasi',
                ],
            ],
            'admin holding' => [
                'label' => 'Admin Holding',
                'keys' => [
                    'ketepatan_waktu_po',
                    'kualitas_dokumentasi_support_dan_proctor',
                ],
            ],
            'IT Service Management' => [
                'label' => 'Divisi ITSM',
                'keys' => [
                    'kepuasan_client_itsm',
                    'inovation_adaption_rate',
                ],
            ],

            'driver' => [
                'label' => 'Driver',
                'keys' => [
                    'perbaikan_kendaraan',
                    'kontrol_pengeluaran_transportasi',
                    'report_kondisi_kendaraan',
                    'feedback_kenyamanan_berkendara',
                ],
            ],

            'education manager' => [
                'label' => 'Education Manager',
                'keys' => [
                    'pengembangan_kurikulum_pelatihan',
                    'peningkatan_knowledge_sharing',
                    'peningkatan_kontribusi_pelatihan',
                    'evaluasi_kinerja_instruktur',
                    'pembuatan_artikel',
                ],
            ],

            'finance & accounting' => [
                'label' => 'Finance & Accounting',
                'keys' => [
                    'outstanding',
                    'inisiatif_efisiensi_keuangan',
                    'mengurangi_manual_work_dan_error',
                    'laporan_analisis_keuangan',
                    'pencairan_biaya_operasional',
                    'penyelesaian_tagihan_perusahaan',
                    'akurasi_pencatatan_masuk',
                ],
            ],

            'gm' => [
                'label' => 'GM',
                'keys' => [
                    'pemasukan_kotor',
                    'pemasukan_bersih',
                    'target_penjualan_project_tahunan',
                    'kepuasan_pelanggan',
                    'rasio_biaya_operasional_terhadap_revenue',
                    'performa_kpi_departemen',
                ],
            ],

            'office boy' => [
                'label' => 'Office Boy',
                'keys' => ['feedback_kebersihan_dan_kenyamanan', 'penyelesaian_tugas_harian'],
            ],
            'project administrator & business support' => [
                'label' => 'Project Administrator & Business Support',
                'keys' => ['efektifitas_digital_marketing', 'timeline_webinar'],
            ],
            'instruktur' => [
                'label' => 'Instruktur',
                'keys' => [
                    'presentase_kinerja_instruktur',
                    'kepuasan_peserta_pelatihan',
                    'upseling_lanjutan_materi',
                    'sertifikasi_kompetensi_internal',
                    'pelatihan_kompetensi_eksternal',
                ],
            ],
            'sales' => [
                'label' => 'Sales',
                'keys' => ['target_penjualan_tahunan', 'biaya_akuisisi_perclient', 'peningkatan_kemampuan_kompetensi_sales'],
            ],
            'spv sales' => [
                'label' => 'SPV Sales',
                'keys' => [
                    'meningkatkan_revenue_perusahaan',
                    'customer_acquisition_cost',
                    'evaluasi_kinerja_sales',
                    'pendapatan_penjualan_project',
                    'leads_project',
                ],
            ],
        ];
    }

    public function getPanduanOpsi()
    {
        $semua = PanduanData::all();
        $grup = [];

        foreach ($this->panduanJabatanMap() as $slug => $cfg) {
            $total = count(array_filter($cfg['keys'], fn($k) => isset($semua[$k])));
            if ($total === 0) {
                continue;
            }

            $divisi = match ($slug) {
                'IT Service Management' => 'IT Service Management',
                default => karyawan::whereRaw('LOWER(jabatan) = ?', [$slug])
                    ->whereNotNull('divisi')
                    ->where('divisi', '!=', '')
                    ->where('divisi', '!=', 'Direksi')
                    ->value('divisi') ?? 'Lainnya',
            };

            $grup[$divisi][] = ['value' => $slug, 'label' => $cfg['label'], 'total' => $total];
        }

        ksort($grup);

        $hasil = [];
        foreach ($grup as $divisi => $items) {
            $hasil[] = ['divisi' => $divisi, 'items' => $items];
        }

        return response()->json(['groups' => $hasil]);
    }

    private function bersihkanPanduanPdf(array $panduan): array
    {
        foreach (['dari_mana_data', 'error_kosong', 'alur_bisnis'] as $field) {
            $panduan[$field] = preg_replace(
                '/[\x{1F000}-\x{1FFFF}\x{2600}-\x{27BF}\x{2B00}-\x{2BFF}\x{FE0F}]/u',
                '',
                $panduan[$field] ?? ''
            );
        }

        return $panduan;
    }

    public function downloadPanduanPdf(Request $request)
    {
        $request->validate([
            'jabatan'   => 'required|array|min:1',
            'jabatan.*' => 'required|string',
        ]);

        $semua = PanduanData::all();
        $dipilih = array_unique(array_map('strtolower', $request->input('jabatan')));

        $groups = [];
        $total = 0;

        foreach ($this->panduanJabatanMap() as $slug => $cfg) {
            if (!in_array($slug, $dipilih, true)) {
                continue;
            }

            $items = [];
            foreach ($cfg['keys'] as $key) {
                if (!isset($semua[$key])) {
                    continue;
                }
                $items[] = ['key' => $key, 'p' => $this->bersihkanPanduanPdf($semua[$key])];
            }

            if (empty($items)) {
                continue;
            }

            $total += count($items);
            $groups[] = ['label' => $cfg['label'], 'items' => $items];
        }

        if (empty($groups)) {
            abort(404, 'Panduan belum tersedia untuk jabatan yang dipilih.');
        }

        $pdf = Pdf::loadView('KPIdata.panduan.pdf', [
            'groups'  => $groups,
            'total'   => $total,
            'tanggal' => now()->locale('id')->translatedFormat('d F Y, H:i'),
        ]);

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->download('Panduan_Target_KPI_' . now()->format('Ymd_His') . '.pdf');
    }

    private function baseKaryawanAktif()
    {
        return karyawan::where('status_aktif', '1')
            ->where('jabatan', '!=', 'Outsource')
            ->where('jabatan', '!=', 'Pilih Jabatan')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNotNull('nip')
            ->whereNotNull('divisi')
            ->where('divisi', '!=', '')
            ->where('divisi', '!=', 'Direksi');
    }
}
