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

        $jabatanList = array_map('strtolower', $jabatanList);

        $routeMapping = [
            'gm' => ['pemasukan kotor', 'pemasukan bersih', 'target penjualan project tahunan', 'kepuasan pelanggan', 'rasio biaya operasional terhadap revenue', 'performa kpi departemen'],
            'customer care' => ['peserta puas dengan pelayanan dan fasilitas training', 'dorong inovasi pelayanan', 'penanganan komplain perseta', 'report persiapan kelas'],
            'finance & accounting' => ['outstanding', 'inisiatif efisiensi keuangan', 'mengurangi manual work dan error', 'laporan analisis keuangan', 'pencairan biaya operasional', 'penyelesaian tagihan perusahaan', 'akurasi pencatatan masuk'],
            'hrd' => ['pelaksanaan kegiatan karyawan', 'pengeluaran biaya karyawan', 'administrasi karyawan'],
            'driver' => ['perbaikan kendaraan', 'report kondisi kendaraan', 'kontrol pengeluaran transportasi', 'feedback kenyamanan berkendaran'],
            'office boy' => ['feedback kebersihan dan kenyamanan', 'penyelesaian tugas harian'],
            'koordinator itsm' => ['meningkatkan kepuasan dan loyalitas peserta/client', 'availability sistem internal kritis', 'persentase gap kompetensi tim terhadap standar skill'],
            'programmer' => ['ketepatan waktu penyelesaian fitur', 'mengukur kualitas aplikasi agar minim bug'],
            'tim digital' => ['konsistensi campaign digital', 'efektifitas digital marketing'],
            'project administrator & business support' => ['pendapatan penjualan project', 'leads project'],
            'technical support' => ['keberhasilan support memenuhi sla', 'kualitas layanan exam'],
            'instruktur' => ['presentase kinerja instruktur', 'kepuasan peserta pelatihan', 'upseling lanjutan materi', 'sertifikasi kompetensi internal', 'pelatihan kompetensi eksternal'],
            'education manager' => ['pengembangan kurikulum pelatihan', 'peningkatan knowledge sharing', 'peningkatan kontribusi pelatihan', 'evaluasi kinerja instruktur', 'pembuatan artikel'],
            'sales' => ['target penjualan tahunan', 'biaya akuisisi perclient', 'peningkatan kemampuan kompetensi sales'],
            'spv sales' => ['meningkatkan revenue perusahaan', 'customer acquisition cost', 'evaluasi kinerja sales'],
            'adm sales' => ['laporan mom', 'akurasi kelengkapan data penjualan', 'todo administrasi'],
            'admin holding' => ['ketepatan waktu po', 'kualitas dokumentasi support dan proctor'],
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
                'deskripsi'      => $validated['deskripsi_kpi'],
                'status'         => '0',
            ]);

            foreach ($jabatans as $jabatan) {
                $dataDivisi = karyawan::where('jabatan', $jabatan)->where('divisi', '!=', 'Direksi')->value('divisi');

                $detail_jangka_value = ($dataTarget->jangka_target === 'Tahunan' && !empty($validated['detail_jangka'])) ? $validated['detail_jangka'] : null;

                $detailStore = DetailTargetKPI::create([
                    'id_targetKPI'   => $createTarget->id,
                    'jabatan'        => $jabatan,
                    'divisi'         => $dataDivisi,
                    'id_data_target' => $dataTarget->id,
                    'jangka_target'  => $dataTarget->jangka_target,
                    'detail_jangka'  => $detail_jangka_value,
                    'tipe_target'    => $dataTarget->tipe_target,
                    'nilai_target'   => $dataTarget->nilai_target,
                ]);

                $karyawanQuery = karyawan::where('jabatan', $jabatan)
                    ->where('status_aktif', '1')
                    ->where('jabatan', '!=', 'Outsource')
                    ->where('kode_karyawan', 'NOT LIKE', 'OL%')
                    ->where('jabatan', '!=', 'Pilih Jabatan')
                    ->whereNotNull('nip')
                    ->where('divisi', '!=', 'Direksi')
                    ->distinct();

                if (!empty($validated['karyawan'])) {
                    $karyawanQuery->whereIn('id', $validated['karyawan']);
                }

                $karyawanIds = $karyawanQuery->pluck('id')->toArray();

                foreach ($karyawanIds as $karyawanId) {
                    detailPersonKPI::create([
                        'id_target'       => $createTarget->id,
                        'detailTargetKey' => $detailStore->id,
                        'id_karyawan'     => $karyawanId,
                    ]);
                }
            }

            return response()->json(['message' => 'Target berhasil dibuat', 'data' => ['id_target' => $createTarget->id]], 201);
        });
    }

    public function updateGapKompetensi(Request $request)
    {
        $data = $request->input('data');
        if (empty($data)) return response()->json(['status' => false, 'message' => 'Data kosong'], 400);

        foreach ($data as $item) {
            $id = $item['id'] ?? null;
            if (!$id) continue;

            $detail = detailPersonKPI::find($id);
            if (!$detail) continue;

            $detail->presentase_kemampuan = $item['kemampuan'] ?? 0;
            $detail->presentase_standar = $item['standar'] ?? 0;
            $detail->save();
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
        $user = auth()->user();
        $data = $overviewService->getProgressDashboardData($user, $request->idUser, $request->typeGet);
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
        $data = $overviewService->getPersonalOverviewData(
            $request->id_karyawan ?? auth()->id(),
            $request->tahun ?? now()->year
        );

        return isset($data['error']) ? response()->json(['success' => false, 'message' => $data['error']], $data['code']) : response()->json($data);
    }

    public function kpiOverview()
    {
        $userKaryawan = karyawan::where('id', Auth::id())->first();
        $departments = karyawan::where('divisi', '!=', 'Direksi')->whereNotNull('divisi')->distinct()->pluck('divisi')->values();
        return view('KPIdata.TargetDivisi.overview', [
            'departments' => $departments,
            'divisi' => $userKaryawan->divisi ?? null,
            'jabatan' => $userKaryawan->jabatan ?? null
        ]);
    }

    public function getDataOverview(Request $request, OverviewDashboardService $overviewService)
    {
        if (!$request->divisi || !$request->tahun) return response()->json(['message' => 'Divisi dan tahun harus diisi'], 400);

        $data = $overviewService->getDepartmentOverviewData($request->divisi, $request->tahun);
        return response()->json($data);
    }

    public function getDataTarget(Request $request)
    {
        $user = auth()->user();
        $idUser = $request->idUser;
        $typeGet = $request->typeGet;

        $targetUser = (filled($idUser) && filled($typeGet)) ? karyawan::find($idUser) : karyawan::find($user->id);
        if (!$targetUser) return response()->json(['message' => 'Karyawan tidak ditemukan'], 404);

        $divisiUser = $targetUser->divisi;
        $superRoles = ['GM', 'HRD', 'Direktur Utama'];

        $dataJabatan = in_array($user->jabatan, $superRoles)
            ? karyawan::whereNotIn('jabatan', ['Direktur Utama', 'Direktur'])->distinct()->pluck('jabatan')
            : karyawan::where('divisi', $divisiUser)->whereNotIn('jabatan', ['Direktur Utama', 'Direktur'])->distinct()->pluck('jabatan');

        $query = targetKPI::with(['karyawan', 'detailTargetKPI.dataTarget', 'detailTargetKPI.detailPersonKPI'])
            ->whereYear('created_at', now()->year);

        if (filled($idUser) && filled($typeGet)) {
            $query->whereHas('detailTargetKPI.detailPersonKPI', fn($q) => $q->where('id_karyawan', $idUser));
        } elseif (!in_array($user->jabatan, $superRoles)) {
            $query->whereHas('detailTargetKPI', fn($q) => $q->where('divisi', $divisiUser));
        }

        $detailList = $query->get();

        $data = [
            'detail' => $detailList->map(function ($item) use ($idUser) {
                $detail = $item->detailTargetKPI->first();
                if (!$detail) return null;

                $personId = !empty($idUser) ? (int) $idUser : null;
                $progress = $this->resolveProgress($item, $personId);

                return [
                    'id' => $item->id, 'pembuat' => $item->karyawan->nama_lengkap ?? null, 'id_pembuat' => $item->id_pembuat,
                    'judul' => $item->judul, 'deskripsi' => $item->deskripsi,
                    'jabatan' => $item->detailTargetKPI->pluck('jabatan')->unique()->values(),
                    'divisi' => $item->detailTargetKPI->pluck('divisi')->unique()->values(),
                    'asistant_route' => $detail->dataTarget?->asistant_route,
                    'jangka_target' => $detail->dataTarget?->jangka_target,
                    'detail_jangka' => $detail->detail_jangka,
                    'tipe_target' => $detail->dataTarget?->tipe_target,
                    'nilai_target' => $detail->dataTarget?->nilai_target,
                    'manual_value' => $detail->manual_value,
                    'status' => $item->status, 'created_at' => $item->created_at,
                    'tenggat_waktu' => $this->formatTenggatWaktuExport($detail->dataTarget?->jangka_target ?? '', $detail->detail_jangka ?? ''),
                    'progress' => $progress,
                ];
            })->filter()->values(),
            'jabatan_list' => $dataJabatan,
            'routes' => DataTarget::select('asistant_route', 'jangka_target', 'tipe_target', 'nilai_target')->get(),
        ];

        return response()->json($data);
    }

    public function detailData(Request $request)
    {
        $idTarget = $request->id;
        $personId = $request->idUser ?? null;

        $query = targetKPI::with(['karyawan', 'detailTargetKPI.detailPersonKPI.karyawan', 'detailTargetKPI.dataTarget'])->where('id', $idTarget);
        if ($personId !== null) {
            $query->whereHas('detailTargetKPI.detailPersonKPI', fn($q) => $q->where('id_karyawan', $personId));
        }

        $detailList = $query->get();

        $data = [
            'detail' => $detailList->map(function ($itemDetail) use ($personId) {
                $detail = $itemDetail->detailTargetKPI->first();
                if (!$detail) return null;

                $dataOutput = [
                    'pembuat' => $itemDetail->karyawan->nama_lengkap ?? null, 'judul' => $itemDetail->judul,
                    'condition' => $detail->dataTarget?->asistant_route, 'deskripsi' => $itemDetail->deskripsi,
                    'jabatan_kpi' => $detail->jabatan, 'divisi_kpi' => $detail->divisi,
                    'karyawan' => $itemDetail->detailTargetKPI->flatMap(function ($detailItem) {
                        return $detailItem->detailPersonKPI->map(fn($person) => [
                            'id' => $person->id, 'nama_lengkap' => $person->karyawan->nama_lengkap ?? null,
                            'jabatan' => $person->karyawan->jabatan ?? null, 'presentase_kemampuan' => $person->presentase_kemampuan ?? 0,
                            'presentase_standar' => $person->presentase_standar ?? 100,
                        ]);
                    })->values(),
                    'jangka_target' => $detail->jangka_target, 'detail_jangka' => $detail->detail_jangka,
                    'tipe_target' => $detail->tipe_target, 'nilai_target' => $detail->nilai_target,
                    'tenggat_waktu' => $this->formatTenggatWaktuExport($detail->jangka_target ?? '', $detail->detail_jangka ?? ''),
                    'data_detail' => $this->getCalculationByRoute($itemDetail, $personId),
                ];

                return ['data' => $dataOutput];
            })->filter()->values(),
        ];

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
        $routeMapping = ['gm' => ['pemasukan kotor', 'pemasukan bersih', 'kepuasan pelanggan', 'target penjualan project tahunan', 'rasio biaya operasional terhadap revenue', 'performa kpi departemen'], 'customer care' => ['peserta puas dengan pelayanan dan fasilitas training', 'dorong inovasi pelayanan', 'penanganan komplain perseta', 'report persiapan kelas'], 'finance & accounting' => ['outstanding', 'inisiatif efisiensi keuangan', 'mengurangi manual work dan error', 'laporan analisis keuangan', 'pencairan biaya operasional', 'penyelesaian tagihan perusahaan', 'akurasi pencatatan masuk'], 'hrd' => ['pelaksanaan kegiatan karyawan', 'pengeluaran biaya karyawan', 'administrasi karyawan'], 'driver' => ['perbaikan kendaraan', 'report kondisi kendaraan', 'kontrol pengeluaran transportasi', 'feedback kenyamanan berkendaran'], 'office boy' => ['feedback kebersihan dan kenyamanan', 'penyelesaian tugas harian'], 'koordinator itsm' => ['meningkatkan kepuasan dan loyalitas peserta/client', 'availability sistem internal kritis', 'persentase gap kompetensi tim terhadap standar skill'], 'programmer' => ['ketepatan waktu penyelesaian fitur', 'mengukur kualitas aplikasi agar minim bug'], 'tim digital' => ['konsistensi campaign digital', 'efektifitas digital marketing'], 'project administrator & business support' => ['pendapatan penjualan project', 'leads project'], 'technical support' => ['keberhasilan support memenuhi sla', 'kualitas layanan exam'], 'instruktur' => ['presentase kinerja instruktur', 'kepuasan peserta pelatihan', 'upseling lanjutan materi', 'sertifikasi kompetensi internal', 'pelatihan kompetensi eksternal'], 'education manager' => ['pengembangan kurikulum pelatihan', 'peningkatan knowledge sharing', 'peningkatan kontribusi pelatihan', 'evaluasi kinerja instruktur', 'pembuatan artikel'], 'sales' => ['target penjualan tahunan', 'biaya akuisisi perclient', 'peningkatan kemampuan kompetensi sales'], 'spv sales' => ['meningkatkan revenue perusahaan', 'customer acquisition cost', 'evaluasi kinerja sales'], 'adm sales' => ['laporan mom', 'akurasi kelengkapan data penjualan', 'todo administrasi'], 'admin holding' => ['ketepatan waktu po', 'kualitas dokumentasi support dan proctor']];
        return Excel::download(new KpiTargetTemplateExport($allRoutes, $routeMapping), 'template_import_kpi_' . date('Y-m-d') . '.xlsx');
    }

    public function exportMonitoringExcel(Request $request, OverviewDashboardService $overviewService)
    {
        try {
            $karyawanId = (int)($request->id_karyawan ?? Auth::id());
            $tahun      = (int)($request->tahun ?? now()->year);
            $filters    = ['periode' => $request->query('periode', 'all'), 'quarter' => $request->query('quarter'), 'tahun_filter' => $request->query('tahun_filter')];

            // Manfaatkan fungsi overview untuk mem-build data export tanpa nulis ulang logikanya
            // Anda bisa memindahkan logika map target export ke service khusus nanti, tapi kita pakai class Export yang dibuat
            $karyawan = karyawan::find($karyawanId);
            $data = $overviewService->getPersonalOverviewData($karyawanId, $tahun);

            $exportService = new MonitoringKPIExport($data, $karyawan->nama_lengkap ?? '-', $karyawan->jabatan ?? '-', $tahun);
            $tmpPath = $exportService->generate();

            return response()->download($tmpPath, 'KPI_' . str_replace(' ', '_', $karyawan->nama_lengkap ?? '-') . '_' . $tahun . '.xlsx')->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->withErrors(['export' => 'Gagal export Excel: ' . $e->getMessage()]);
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
            if (!$divisi) return back()->withErrors(['export' => 'Departemen belum dipilih.']);

            $data = $overviewService->getDepartmentOverviewData($divisi, $tahun);
            $pdf = Pdf::loadView('KPIdata.export.export_dept_pdf', $data)->setPaper('a4', 'landscape');
            return $pdf->download('KPI_Dept_' . str_replace(' ', '_', $divisi) . '_' . $tahun . '.pdf');
        } catch (\Exception $e) {
            return back()->withErrors(['export' => 'Terjadi kesalahan saat export: ' . $e->getMessage()]);
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
}
