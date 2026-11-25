<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\karyawan;
use App\Models\OrgChart;
use App\Models\OrgStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class StructureInixindoController extends Controller
{
    private const HR_MENU_PERMISSION = 'View Struktur Organisasi';

    private const PROTECTED_ROLES = ['HRD'];

    private const EXECUTIVE_TITLES = ['Direktur', 'Direktur Utama'];

    private const EXCLUDED_TITLES = ['Outsource', 'Pilih Jabatan'];

    public function index()
    {
        if (OrgChart::count() === 0) {
            OrgChart::create(['name' => 'Struktur Utama']);
        }

        $charts = OrgChart::orderBy('id')->get(['id', 'name']);

        $menuEnabled = Role::whereNotIn('name', self::PROTECTED_ROLES)->exists()
            && Role::whereNotIn('name', self::PROTECTED_ROLES)
                ->whereDoesntHave('permissions', fn ($q) => $q->where('name', self::HR_MENU_PERMISSION))
                ->doesntExist();

        return view('HR.structure.index', compact('charts', 'menuEnabled'));
    }

    public function toggleMenus(Request $request)
    {
        $permission = Permission::where('name', self::HR_MENU_PERMISSION)->firstOrFail();
        $enabled = $request->boolean('enabled');

        DB::transaction(function () use ($permission, $enabled) {
            Role::whereNotIn('name', self::PROTECTED_ROLES)->get()->each(function ($role) use ($permission, $enabled) {
                $enabled
                    ? $role->givePermissionTo($permission)
                    : $role->revokePermissionTo($permission);
            });
        });

        return redirect()->back()->with('status', 'Akses menu Struktur Organisasi ' . ($enabled ? 'diaktifkan' : 'dinonaktifkan') . ' untuk semua karyawan');
    }

    public function charts()
    {
        return response()->json(OrgChart::orderBy('id')->get(['id', 'name']));
    }

    public function storeChart(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $chart = OrgChart::create(['name' => trim($validated['name'])]);

        return response()->json([
            'success' => true,
            'message' => 'Struktur berhasil dibuat',
            'data' => $chart->only(['id', 'name']),
        ]);
    }

    public function updateChart(Request $request, OrgChart $chart)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $chart->update(['name' => trim($validated['name'])]);

        return response()->json([
            'success' => true,
            'message' => 'Nama struktur berhasil diperbarui',
            'data' => $chart->only(['id', 'name']),
        ]);
    }

    public function destroyChart(OrgChart $chart)
    {
        if (OrgChart::count() <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Minimal harus ada satu struktur.',
            ], 422);
        }

        DB::transaction(function () use ($chart) {
            OrgStructure::where('chart_id', $chart->id)->update(['parent_id' => null]);
            OrgStructure::where('chart_id', $chart->id)->delete();
            $chart->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Struktur berhasil dihapus',
        ]);
    }

    public function apiTree(OrgChart $chart)
    {
        $nodes = OrgStructure::where('chart_id', $chart->id)->get();

        $employeeIds = $nodes->pluck('karyawan_ids')->flatten()->filter()->unique()->values();

        $employees = $employeeIds->isEmpty()
            ? collect()
            : karyawan::whereIn('id', $employeeIds)->get()->keyBy('id');

        $byParent = $nodes->groupBy(fn ($node) => $node->parent_id ?? 0);

        $build = function ($parentId) use (&$build, $byParent, $employees) {
            return ($byParent->get($parentId ?? 0) ?? collect())
                ->sortBy([['sort_order', 'asc'], ['id', 'asc']])
                ->values()
                ->map(function ($node) use (&$build, $employees) {
                    $members = collect($node->karyawan_ids ?? [])
                        ->map(fn ($id) => $employees->get($id))
                        ->filter()
                        ->map(fn ($k) => [
                            'id' => $k->id,
                            'nama_lengkap' => $k->nama_lengkap,
                            'nip' => $k->nip,
                            'foto' => $k->foto,
                            'status_aktif' => $k->status_aktif,
                            'email' => $k->email,
                            'whatsapp' => $k->whatsapp,
                            'divisi' => $k->divisi,
                        ])
                        ->values();

                    return [
                        'id' => $node->id,
                        'jabatan' => $node->jabatan,
                        'divisi' => $node->divisi,
                        'parent_id' => $node->parent_id,
                        'sort_order' => $node->sort_order,
                        'karyawans' => $members,
                        'additional_parents' => array_map('intval', $node->additional_parents ?? []),
                        'children' => $build($node->id),
                        'pos_x' => $node->pos_x,
                        'pos_y' => $node->pos_y,
                    ];
                })
                ->values();
        };

        return response()->json([
            'chart' => $chart->only(['id', 'name']),
            'tree' => $build(null),
        ]);
    }

    public function savePositions(Request $request)
    {
        $validated = $request->validate([
            'positions' => 'required|array|min:1|max:500',
            'positions.*.id' => 'required|integer|exists:org_structures,id',
            'positions.*.pos_x' => 'nullable|numeric',
            'positions.*.pos_y' => 'nullable|numeric',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['positions'] as $row) {
                OrgStructure::whereKey($row['id'])->update([
                    'pos_x' => isset($row['pos_x']) ? (int) round($row['pos_x']) : null,
                    'pos_y' => isset($row['pos_y']) ? (int) round($row['pos_y']) : null,
                ]);
            }
        });

        return response()->json(['success' => true, 'message' => 'Posisi disimpan']);
    }

    public function resetPositions(OrgChart $chart)
    {
        OrgStructure::where('chart_id', $chart->id)->update(['pos_x' => null, 'pos_y' => null]);

        return response()->json(['success' => true, 'message' => 'Posisi dikembalikan ke otomatis']);
    }

    public function getKaryawans()
    {
        $karyawans = karyawan::select('id', 'nama_lengkap', 'nip', 'jabatan')
            ->where('status_aktif', '1')
            ->orderBy('nama_lengkap')
            ->get();

        return response()->json($karyawans);
    }

    public function getJabatans()
    {
        $fromEmployees = karyawan::query()
            ->where('status_aktif', '1')
            ->whereNotNull('jabatan')
            ->where('jabatan', '!=', '')
            ->whereNotIn('jabatan', self::EXCLUDED_TITLES)
            ->where('kode_karyawan', 'not like', 'OL%')
            ->where(function ($q) {
                $q->whereIn('jabatan', self::EXECUTIVE_TITLES)
                    ->orWhere(function ($w) {
                        $w->whereNotNull('nip')
                            ->where(fn ($d) => $d->whereNull('divisi')->orWhere('divisi', '!=', 'Direksi'));
                    });
            })
            ->get(['id', 'jabatan', 'divisi'])
            ->groupBy('jabatan')
            ->map(fn ($rows, $jabatan) => [
                'jabatan' => $jabatan,
                'divisi' => $rows->pluck('divisi')->filter()->first(),
                'karyawan_ids' => $rows->pluck('id')->values()->all(),
            ]);

        $fromStructures = OrgStructure::query()
            ->whereNotIn('jabatan', $fromEmployees->keys()->all())
            ->orderBy('id')
            ->get(['jabatan', 'divisi', 'karyawan_ids'])
            ->unique('jabatan')
            ->map(fn ($node) => [
                'jabatan' => $node->jabatan,
                'divisi' => $node->divisi,
                'karyawan_ids' => array_values($node->karyawan_ids ?? []),
            ]);

        $result = $fromEmployees->values()
            ->concat($fromStructures->values())
            ->sortBy('jabatan', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        return response()->json($result);
    }

    public function store(Request $request, OrgChart $chart)
    {
        $validated = $request->validate([
            'jabatan' => [
                'required',
                'string',
                'max:255',
                Rule::unique('org_structures', 'jabatan')->where('chart_id', $chart->id),
            ],
            'divisi' => 'nullable|string|max:255',
            'parent_id' => [
                'nullable',
                Rule::exists('org_structures', 'id')->where('chart_id', $chart->id),
            ],
            'karyawan_ids' => 'nullable|array',
            'karyawan_ids.*' => 'exists:karyawans,id',
        ]);

        $parentId = $validated['parent_id'] ?? null;

        $sortOrder = (int) OrgStructure::where('chart_id', $chart->id)
            ->where('parent_id', $parentId)
            ->max('sort_order') + 1;

        $node = OrgStructure::create([
            'chart_id' => $chart->id,
            'jabatan' => trim($validated['jabatan']),
            'divisi' => $validated['divisi'] ?? null,
            'parent_id' => $parentId,
            'sort_order' => $sortOrder,
            'karyawan_ids' => array_map('intval', $validated['karyawan_ids'] ?? []),
            'additional_parents' => [],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Jabatan berhasil ditambahkan',
            'data' => $node,
        ]);
    }

    public function update(Request $request, $id)
    {
        $node = OrgStructure::findOrFail($id);

        $validated = $request->validate([
            'jabatan' => [
                'required',
                'string',
                'max:255',
                Rule::unique('org_structures', 'jabatan')
                    ->where('chart_id', $node->chart_id)
                    ->ignore($node->id),
            ],
            'divisi' => 'nullable|string|max:255',
            'karyawan_ids' => 'nullable|array',
            'karyawan_ids.*' => 'exists:karyawans,id',
        ]);

        $node->update([
            'jabatan' => trim($validated['jabatan']),
            'divisi' => $validated['divisi'] ?? null,
            'karyawan_ids' => array_map('intval', $validated['karyawan_ids'] ?? []),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Jabatan berhasil diperbarui',
            'data' => $node->fresh(),
        ]);
    }

    public function destroy($id)
    {
        $node = OrgStructure::findOrFail($id);

        DB::transaction(function () use ($node) {
            $children = OrgStructure::where('parent_id', $node->id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            $siblings = OrgStructure::where('chart_id', $node->chart_id)
                ->where('parent_id', $node->parent_id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            $ordered = collect();

            foreach ($siblings as $sibling) {
                if ($sibling->id === $node->id) {
                    $ordered = $ordered->concat($children);
                } else {
                    $ordered->push($sibling);
                }
            }

            foreach ($ordered->values() as $index => $item) {
                OrgStructure::whereKey($item->id)->update([
                    'parent_id' => $node->parent_id,
                    'sort_order' => $index,
                ]);
            }

            OrgStructure::where('chart_id', $node->chart_id)
                ->whereNotNull('additional_parents')
                ->get()
                ->each(function ($other) use ($node) {
                    $current = $other->additional_parents ?? [];
                    $filtered = collect($current)
                        ->reject(fn ($parentId) => (int) $parentId === (int) $node->id)
                        ->values()
                        ->all();

                    if (count($filtered) !== count($current)) {
                        $other->update(['additional_parents' => $filtered]);
                    }
                });

            $node->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Jabatan "' . $node->jabatan . '" berhasil dihapus',
        ]);
    }

    public function move(Request $request, $id)
    {
        $validated = $request->validate([
            'position' => ['required', Rule::in(['child', 'before', 'after', 'above', 'link', 'root'])],
            'target_id' => 'nullable|integer',
        ]);

        $node = OrgStructure::findOrFail($id);
        $position = $validated['position'];
        $target = null;

        if ($position !== 'root') {
            $target = OrgStructure::where('chart_id', $node->chart_id)
                ->find($validated['target_id'] ?? 0);

            if (!$target) {
                return response()->json(['success' => false, 'message' => 'Jabatan tujuan tidak ditemukan'], 404);
            }

            if ($target->id === $node->id) {
                return response()->json(['success' => false, 'message' => 'Tujuan tidak valid'], 422);
            }

            $cursor = $target;
            $guard = 0;

            while ($cursor && $guard < 1000) {
                if ($cursor->id === $node->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak bisa memindahkan jabatan ke dalam bawahannya sendiri',
                    ], 422);
                }

                $cursor = $cursor->parent_id ? OrgStructure::find($cursor->parent_id) : null;
                $guard++;
            }
        }

        if ($position === 'link') {
            if ($node->parent_id === null) {
                $position = 'child';
            } else {
                if ((int) $target->id !== (int) $node->parent_id) {
                    $linked = collect($node->additional_parents ?? [])
                        ->push($target->id)
                        ->map(fn ($value) => (int) $value)
                        ->unique()
                        ->values()
                        ->all();

                    $node->update(['additional_parents' => $linked]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Atasan tambahan berhasil disimpan',
                ]);
            }
        }

        DB::transaction(function () use ($node, $target, $position) {
            $newParentId = match ($position) {
                'root' => null,
                'child' => $target->id,
                default => $target->parent_id,
            };

            $siblings = OrgStructure::where('chart_id', $node->chart_id)
                ->where('parent_id', $newParentId)
                ->where('id', '!=', $node->id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->values();

            $index = match ($position) {
                'before', 'above' => $siblings->search(fn ($s) => $s->id === $target->id),
                'after' => $siblings->search(fn ($s) => $s->id === $target->id) + 1,
                default => $siblings->count(),
            };

            if ($position === 'above') {
                $siblings = $siblings->reject(fn ($s) => $s->id === $target->id)->values();
            }

            if ($index === false) {
                $index = $siblings->count();
            }

            $ordered = $siblings->all();
            array_splice($ordered, $index, 0, [$node]);

            foreach ($ordered as $i => $item) {
                OrgStructure::whereKey($item->id)->update([
                    'parent_id' => $newParentId,
                    'sort_order' => $i,
                ]);
            }

            if ($position === 'above') {
                $childOrder = (int) OrgStructure::where('parent_id', $node->id)->max('sort_order') + 1;
                $targetExtra = collect($target->additional_parents ?? [])
                    ->reject(fn ($value) => (int) $value === (int) $node->id)
                    ->values()
                    ->all();

                $target->update([
                    'parent_id' => $node->id,
                    'sort_order' => $childOrder,
                    'additional_parents' => $targetExtra,
                ]);
            }

            $extra = collect($node->additional_parents ?? [])
                ->reject(fn ($parentId) => $newParentId !== null && (int) $parentId === (int) $newParentId)
                ->values()
                ->all();

            $node->update(['additional_parents' => $extra, 'pos_x' => null, 'pos_y' => null]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Hierarki berhasil disimpan',
        ]);
    }

    public function setAdditionalParents(Request $request, $id)
    {
        $node = OrgStructure::findOrFail($id);

        $validated = $request->validate([
            'additional_parents' => 'nullable|array',
            'additional_parents.*' => [
                'integer',
                Rule::exists('org_structures', 'id')->where('chart_id', $node->chart_id),
            ],
        ]);

        $ids = collect($validated['additional_parents'] ?? [])
            ->map(fn ($value) => (int) $value)
            ->reject(fn ($value) => $value === (int) $node->id || $value === (int) $node->parent_id)
            ->unique()
            ->values()
            ->all();

        $node->update(['additional_parents' => $ids]);

        return response()->json([
            'success' => true,
            'message' => 'Atasan tambahan berhasil disimpan',
        ]);
    }
}