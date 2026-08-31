<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\RKM;
use App\Models\ChecklistRKM;
use App\Models\karyawan;
use App\Models\Perusahaan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class checklistRKMController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:View CRM Checklist RKM', ['only' => ['index', 'getData']]);
    }

    public function index(): View
    {
        return view('crm.checklistRKM.index');
    }

    public function getData(Request $request): JsonResponse
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'bulan' => 'nullable|integer|between:1,12',
            'tahun' => 'nullable|integer|digits:4',
            'minggu' => 'nullable|integer|between:1,4',
            'page' => 'nullable|integer|min:1',
        ]);

        if (!$request->filled('bulan') || !$request->filled('tahun')) {
            $request->merge([
                'bulan' => date('n'),
                'tahun' => date('Y')
            ]);
        }

        $inner = DB::table('r_k_m_s')
            ->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
            ->leftJoin('checklist_r_k_m_s', 'checklist_r_k_m_s.id_rkm', '=', 'r_k_m_s.id')
            ->whereNull('r_k_m_s.deleted_at')
            ->select(
                DB::raw('GROUP_CONCAT(r_k_m_s.id SEPARATOR ",") AS id_all'),
                DB::raw('MIN(r_k_m_s.id) AS id'),
                'r_k_m_s.materi_key',
                'materis.nama_materi',
                DB::raw('GROUP_CONCAT(DISTINCT r_k_m_s.perusahaan_key SEPARATOR ",") AS perusahaan_all'),
                DB::raw('GROUP_CONCAT(DISTINCT r_k_m_s.sales_key SEPARATOR ",") AS sales_all'),
                DB::raw('GROUP_CONCAT(DISTINCT r_k_m_s.instruktur_key SEPARATOR ",") AS instruktur_all'),
                'r_k_m_s.tanggal_awal',
                DB::raw('MAX(r_k_m_s.tanggal_akhir) AS tanggal_akhir'),
                DB::raw('CASE WHEN SUM(r_k_m_s.status = 0) > 0 THEN 0 ELSE MIN(r_k_m_s.status) END AS status_all'),
                DB::raw('SUM(COALESCE(checklist_r_k_m_s.registrasi_form, 0)) AS registrasi_form'),
                DB::raw('SUM(COALESCE(checklist_r_k_m_s.surat_kontrak, 0)) AS surat_kontrak'),
                DB::raw('SUM(COALESCE(checklist_r_k_m_s.PA, 0)) AS PA'),
                DB::raw('SUM(COALESCE(checklist_r_k_m_s.PO, 0)) AS PO')
            )
            ->groupBy(
                'r_k_m_s.materi_key',
                'materis.nama_materi',
                'r_k_m_s.tanggal_awal'
            );

        if ($request->filled('search')) {
            $search = $request->search;
            $inner->where(function ($q) use ($search) {
                $q->where('materis.nama_materi', 'like', "%{$search}%");
            });
        }

        if ($request->filled('bulan') && $request->filled('tahun')) {
            $startDate = Carbon::create($request->tahun, $request->bulan, 1)->startOfMonth();
            $endDate = Carbon::create($request->tahun, $request->bulan, 1)->endOfMonth();
            $inner->where(function ($q) use ($startDate, $endDate) {
                $q->where('r_k_m_s.tanggal_awal', '<=', $endDate->format('Y-m-d'))
                ->where('r_k_m_s.tanggal_akhir', '>=', $startDate->format('Y-m-d'));
            });
        } elseif ($request->filled('tahun')) {
            $yearStart = Carbon::create($request->tahun, 1, 1)->startOfYear();
            $yearEnd = Carbon::create($request->tahun, 12, 31)->endOfYear();
            $inner->where(function ($q) use ($yearStart, $yearEnd) {
                $q->where('r_k_m_s.tanggal_awal', '<=', $yearEnd->format('Y-m-d'))
                ->where('r_k_m_s.tanggal_akhir', '>=', $yearStart->format('Y-m-d'));
            });
        }

        if ($request->filled('minggu') && $request->filled('bulan') && $request->filled('tahun')) {
            $minggu = (int) $request->minggu;
            $tahun = (int) $request->tahun;
            $bulan = (int) $request->bulan;

            $startDay = ($minggu - 1) * 7 + 1;
            $endDay = min($minggu * 7, Carbon::create($tahun, $bulan, 1)->daysInMonth);

            $firstBusinessDay = null;
            $lastBusinessDay = null;

            for ($day = $startDay; $day <= $endDay; $day++) {
                $date = Carbon::create($tahun, $bulan, $day);
                if ($date->isWeekday()) {
                    if (!$firstBusinessDay) {
                        $firstBusinessDay = $date->copy();
                    }
                    $lastBusinessDay = $date->copy();
                }
            }

            if ($firstBusinessDay && $lastBusinessDay) {
                $inner->where(function ($q) use ($firstBusinessDay, $lastBusinessDay) {
                    $q->where('r_k_m_s.tanggal_awal', '<=', $lastBusinessDay->format('Y-m-d'))
                    ->where('r_k_m_s.tanggal_akhir', '>=', $firstBusinessDay->format('Y-m-d'));
                });
            } else {
                $inner->whereRaw('1 = 0');
            }
        }

        $perPage = $request->input('per_page', 20);

        $dataRKM = DB::query()
            ->fromSub($inner, 'grouped')
            ->orderByRaw('
                CASE status_all
                    WHEN 0 THEN 1
                    WHEN 1 THEN 2
                    ELSE 3
                END ASC
            ')
            ->orderBy('tanggal_awal', 'asc')
            ->paginate($perPage);

        $taskMap = [
            'Registratsi Form' => 'registrasi_form',
            'Surat Kontrak' => 'surat_kontrak',
            'PA' => 'PA',
            'PO' => 'PO',
        ];

        $allSalesIds = [];
        $allInstrukturIds = [];
        $allPerusahaanIds = [];
        foreach ($dataRKM as $row) {
            $allSalesIds = array_merge($allSalesIds, array_filter(explode(',', $row->sales_all ?? '')));
            $allInstrukturIds = array_merge($allInstrukturIds, array_filter(explode(',', $row->instruktur_all ?? '')));
            $allPerusahaanIds = array_merge($allPerusahaanIds, array_filter(explode(',', $row->perusahaan_all ?? '')));
        }

        $karyawanMap = karyawan::whereIn('kode_karyawan', array_unique(array_merge($allSalesIds, $allInstrukturIds)))
            ->get()->keyBy('kode_karyawan');
        $perusahaanMap = Perusahaan::whereIn('id', array_unique($allPerusahaanIds))->get()->keyBy('id');

        $transformed = $dataRKM->map(function ($item) use ($taskMap, $karyawanMap, $perusahaanMap) {
            $checkboxes = [];
            
            $totalPerusahaan = count(array_filter(explode(',', $item->perusahaan_all ?? '')));
            
            foreach ($taskMap as $label => $field) {
                $checkboxes[$field] = [
                    'checked' => (bool) $item->$field,
                    'label' => $label,
                    'completed' => (int) $item->$field,
                    'total' => $totalPerusahaan,
                    'progress' => $totalPerusahaan > 0 ? round((($item->$field ?? 0) / $totalPerusahaan) * 100) : 0,
                ];
            }

            $salesNames = collect(explode(',', $item->sales_all ?? ''))
                ->filter()->map(fn($k) => $karyawanMap->get($k)?->nama_lengkap)->filter()->implode(', ');

            $instrukturNames = collect(explode(',', $item->instruktur_all ?? ''))
                ->filter()->map(fn($k) => $karyawanMap->get($k)?->nama_lengkap)->filter()->implode(', ');

            $perusahaanNames = collect(explode(',', $item->perusahaan_all ?? ''))
                ->filter()->map(fn($k) => $perusahaanMap->get($k)?->nama_perusahaan)->filter()->implode(', ');

            return [
                'id' => $item->id,
                'id_all' => $item->id_all,
                'status' => (string) $item->status_all,
                'materi' => $item->nama_materi ?? '-',
                'perusahaan' => $perusahaanNames ?: '-',
                'instruktur' => $instrukturNames ?: '-',
                'sales' => $salesNames ?: '-',
                'tanggal_training' => $item->tanggal_awal
                    ? Carbon::parse($item->tanggal_awal)->translatedFormat('d F Y') . ' s/d ' . Carbon::parse($item->tanggal_akhir)->translatedFormat('d F Y')
                    : '-',
                'checkboxes' => $checkboxes,
                'total_perusahaan' => $totalPerusahaan,
            ];
        });

        return response()->json([
            'data' => $transformed->values(),
            'pagination' => [
                'current_page' => $dataRKM->currentPage(),
                'last_page' => $dataRKM->lastPage(),
                'total' => $dataRKM->total(),
                'per_page' => $dataRKM->perPage(),
            ],
            'filters' => $request->only(['search', 'bulan', 'tahun', 'minggu']),
        ]);
    }

    public function updateChecklist(Request $request, $id): JsonResponse
    {
        $request->validate([
            'field' => 'required|in:registrasi_form,surat_kontrak,PA,PO',
            'checked' => 'required|boolean',
        ]);

        DB::beginTransaction();

        try {
            $field = $request->field;
            $checked = (bool) $request->checked;

            $checklist = ChecklistRKM::firstOrCreate(
                ['id_rkm' => $id],
                [
                    'registrasi_form' => false,
                    'surat_kontrak' => false,
                    'PA' => false,
                    'PO' => false,
                ],
            );

            $checklist->update([$field => $checked]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Checklist berhasil diupdate',
                'data' => [
                    'field' => $field,
                    'checked' => $checked,
                    'timestamp' => now()->format('Y-m-d H:i:s'),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error updating checklist RKM', [
                'id_rkm' => $id,
                'field' => $request->field,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Gagal update: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function getDetailData($id): JsonResponse
    {
        try {
            $baseRkm = RKM::select('materi_key', 'tanggal_awal')->findOrFail($id);

            $rkmItems = RKM::where('materi_key', $baseRkm->materi_key)
                ->where('tanggal_awal', $baseRkm->tanggal_awal)
                ->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
                ->leftJoin('checklist_r_k_m_s', 'checklist_r_k_m_s.id_rkm', '=', 'r_k_m_s.id')
                ->leftJoin('karyawans as sales', 'r_k_m_s.sales_key', '=', 'sales.kode_karyawan')
                ->leftJoin('perusahaans', 'r_k_m_s.perusahaan_key', '=', 'perusahaans.id')
                ->select(
                    'r_k_m_s.id',
                    'materis.nama_materi',
                    'r_k_m_s.perusahaan_key',
                    'perusahaans.nama_perusahaan',
                    'r_k_m_s.sales_key',
                    'sales.nama_lengkap as sales_name',
                    'checklist_r_k_m_s.registrasi_form',
                    'checklist_r_k_m_s.surat_kontrak',
                    'checklist_r_k_m_s.PA',
                    'checklist_r_k_m_s.PO'
                )
                ->orderBy('perusahaans.nama_perusahaan', 'asc')
                ->orderBy('sales.nama_lengkap', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $rkmItems
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data detail: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateDetailChecklist(Request $request, $id): JsonResponse
    {
        $request->validate([
            'rkm_id' => 'required|exists:r_k_m_s,id',
            'field' => 'required|in:registrasi_form,surat_kontrak,PA,PO',
            'checked' => 'required|boolean',
        ]);

        DB::beginTransaction();

        try {
            $field = $request->field;
            $checked = (bool) $request->checked;

            $checklist = ChecklistRKM::firstOrCreate(
                ['id_rkm' => $request->rkm_id],
                [
                    'registrasi_form' => false,
                    'surat_kontrak' => false,
                    'PA' => false,
                    'PO' => false,
                ]
            );

            $checklist->update([$field => $checked]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Checklist berhasil diupdate',
                'data' => [
                    'rkm_id' => $request->rkm_id,
                    'field' => $field,
                    'checked' => $checked,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Gagal update: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function updateMultiple(Request $request, $id): JsonResponse
    {
        $request->validate([
            'checklists' => 'required|array',
            'checklists.*.field' => 'required|in:registrasi_form,surat_kontrak,PA,PO',
            'checklists.*.checked' => 'required|boolean',
        ]);

        DB::beginTransaction();

        try {
            $checklist = ChecklistRKM::firstOrNew(['id_rkm' => $id]);
            $changes = [];

            foreach ($request->checklists as $item) {
                $field = $item['field'];
                $oldValue = $checklist->$field ?? false;
                $newValue = (bool) $item['checked'];

                if ($oldValue !== $newValue) {
                    $changes[] = [
                        'field' => $field,
                        'old' => $oldValue,
                        'new' => $newValue,
                    ];
                }

                $checklist->$field = $newValue;
            }

            $checklist->updated_at = now();
            $checklist->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Checklist berhasil diupdate',
                'changes' => $changes,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error updating multiple checklist RKM', [
                'id_rkm' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Gagal update: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }
}