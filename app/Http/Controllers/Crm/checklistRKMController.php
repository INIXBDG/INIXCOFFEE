<?php

namespace App\Http\Controllers\crm;

use App\Http\Controllers\Controller;
use App\Models\RKM;
use App\Models\ChecklistRKM;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class checklistRKMController extends Controller
{
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

        $query = RKM::with(['materi', 'perusahaan', 'sales', 'instruktur', 'checklist']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('materi', fn($qm) => $qm->where('nama_materi', 'like', "%{$search}%"))
                  ->orWhereHas('perusahaan', fn($qp) => $qp->where('nama_perusahaan', 'like', "%{$search}%"))
                  ->orWhereHas('sales', fn($qs) => $qs->where('nama_lengkap', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('bulan') && $request->filled('tahun')) {
            $startDate = Carbon::create($request->tahun, $request->bulan, 1)->startOfMonth();
            $endDate = Carbon::create($request->tahun, $request->bulan, 1)->endOfMonth();
            $query->whereBetween('tanggal_awal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
        } elseif ($request->filled('tahun')) {
            $yearStart = Carbon::create($request->tahun, 1, 1)->startOfYear();
            $yearEnd = Carbon::create($request->tahun, 12, 31)->endOfYear();
            $query->whereBetween('tanggal_awal', [$yearStart->format('Y-m-d'), $yearEnd->format('Y-m-d')]);
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
                $query->whereBetween('tanggal_awal', [$firstBusinessDay->format('Y-m-d'), $lastBusinessDay->format('Y-m-d')]);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $perPage = $request->input('per_page', 20);
        $dataRKM = $query->orderBy('tanggal_awal', 'asc')->paginate($perPage);

        $taskMap = [
            'Registratsi Form' => 'registrasi_form',
            'Surat Kontrak' => 'surat_kontrak',
            'PA' => 'PA',
            'PO' => 'PO',
        ];

        $transformed = $dataRKM->map(function ($item) use ($taskMap) {
            $checklist = $item->checklist ?? new ChecklistRKM();
            $checkboxes = [];
            foreach ($taskMap as $label => $field) {
                $checkboxes[$field] = [
                    'checked' => (bool) ($checklist->$field ?? false),
                    'label' => $label,
                ];
            }

            return [
                'id' => $item->id,
                'rkm_code' => $item->kode_rkm ?? 'RKM-' . $item->id,
                'materi' => $item->materi?->nama_materi ?? '-',
                'perusahaan' => $item->perusahaan?->nama_perusahaan ?? '-',
                'instruktur' => $item->instruktur?->nama_lengkap ?? '-',
                'instruktur_id' => $item->instruktur_id ?? null,
                'sales' => $item->sales?->nama_lengkap ?? '-',
                'tanggal_training' => $item->tanggal_awal ? Carbon::parse($item->tanggal_awal)->translatedFormat('d F Y') . ' s/d ' . Carbon::parse($item->tanggal_akhir)->translatedFormat('d F Y') : '-',
                'group_key' => $item->tanggal_awal . '|' . $item->tanggal_akhir . '|' . ($item->materi_id ?? '') . '|' . ($item->instruktur_id ?? ''),
                'checkboxes' => $checkboxes,
            ];
        });

        $grouped = $transformed->groupBy('group_key')->map(function ($items, $key) {
            return [
                'group_key' => $key,
                'items' => $items->values()->all(),
            ];
        })->values();

        $paginatedGroups = new \Illuminate\Pagination\LengthAwarePaginator(
            $grouped->forPage($dataRKM->currentPage(), $perPage),
            $grouped->count(),
            $perPage,
            $dataRKM->currentPage(),
            ['path' => $dataRKM->path(), 'query' => $request->query()]
        );

        return response()->json([
            'data' => $paginatedGroups->items(),
            'pagination' => [
                'current_page' => $paginatedGroups->currentPage(),
                'last_page' => $paginatedGroups->lastPage(),
                'total' => $paginatedGroups->total(),
                'per_page' => $paginatedGroups->perPage(),
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