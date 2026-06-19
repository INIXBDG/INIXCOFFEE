<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\OrgStructure;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StructureInixindoController extends Controller
{
    public function index()
    {
        return view('HR.structure.index');
    }

    public function apiTree()
    {
        $allNodes = OrgStructure::get()->keyBy('id');
        $roots = $allNodes->filter(fn($n) => $n->parent_id === null);

        $buildTree = function ($parentId = null) use (&$buildTree, &$allNodes) {
            return $allNodes
                ->filter(fn($node) => $node->parent_id == $parentId)
                ->sortBy('sort_order')
                ->map(function ($node) use (&$buildTree, &$allNodes) {
                    $karyawans = collect();
                    if (!empty($node->karyawan_ids)) {
                        $karyawans = Karyawan::whereIn('id', $node->karyawan_ids)->get()->map(
                            fn($k) => [
                                'id' => $k->id,
                                'nama_lengkap' => $k->nama_lengkap,
                                'nip' => $k->nip,
                                'foto' => $k->foto,
                                'status_aktif' => $k->status_aktif,
                                'email' => $k->email,
                                'whatsapp' => $k->whatsapp,
                                'divisi' => $k->divisi,
                            ]
                        );
                    }

                    $children = $buildTree($node->id);

                    return [
                        'id' => $node->id,
                        'jabatan' => $node->jabatan,
                        'divisi' => $node->divisi,
                        'parent_id' => $node->parent_id,
                        'sort_order' => $node->sort_order,
                        'karyawans' => $karyawans,
                        'children' => $children,
                        'additional_parents' => $node->additional_parents ?? [],
                    ];
                })
                ->values();
        };

        $tree = $buildTree();
        
        return response()->json(['tree' => $tree]);
    }

    public function getKaryawans()
    {
        $karyawans = Karyawan::select('id', 'nama_lengkap', 'nip')->where('status_aktif', '1')->orderBy('nama_lengkap')->get();
        return response()->json($karyawans);
    }

    public function sync()
    {
        $uniqueJabatans = Karyawan::query()
            ->where(function ($query) {
                // Direktur & Direktur Utama (diperbolehkan meski divisi Direksi)
                $query->whereIn('jabatan', ['Direktur', 'Direktur Utama'])
                    ->where('status_aktif', '1');
            })
            ->orWhere(function ($query) {
                // Jabatan lain dengan filter ketat
                $query->where('status_aktif', '1')
                    ->whereNotIn('jabatan', ['Outsource', 'Pilih Jabatan'])
                    ->where('kode_karyawan', 'not like', 'OL%')
                    ->where('divisi', '!=', 'Direksi')           // ← Perbaikan utama
                    ->whereNotNull('nip');
            })
            ->select('jabatan', 'divisi')
            ->distinct()
            ->get();

        $existing = OrgStructure::pluck('jabatan')->toArray();

        $added = 0;

        foreach ($uniqueJabatans as $item) {
            if (in_array($item->jabatan, $existing)) {
                continue;
            }

            // Query untuk mengambil karyawan_ids
            $karyawanQuery = Karyawan::where('jabatan', $item->jabatan)
                ->where('status_aktif', '1');

            // Filter tambahan hanya untuk jabatan selain Direktur & Direktur Utama
            if (!in_array($item->jabatan, ['Direktur', 'Direktur Utama'])) {
                $karyawanQuery->whereNotIn('jabatan', ['Outsource', 'Pilih Jabatan'])
                            ->where('kode_karyawan', 'not like', 'OL%')
                            ->where('divisi', '!=', 'Direksi')   // ← Konsisten dengan query atas
                            ->whereNotNull('nip');
            }

            $karyawanIds = $karyawanQuery->pluck('id')->toArray();

            OrgStructure::create([
                'jabatan'      => $item->jabatan,
                'divisi'       => $item->divisi,
                'sort_order'   => 0,
                'karyawan_ids' => $karyawanIds,
            ]);

            $added++;
        }

        return response()->json([
            'success' => true,
            'message' => $added > 0
                ? "{$added} jabatan baru berhasil ditambahkan"
                : 'Semua jabatan sudah tersinkronisasi',
        ]);
    }
    
    public function setMultiParent(Request $request)
    {
        $validated = $request->validate([
            'position_id' => 'required|exists:org_structures,id',
            'additional_parents' => 'nullable|array',
            'additional_parents.*' => 'exists:org_structures,id',
        ]);

        try {
            $structure = OrgStructure::findOrFail($validated['position_id']);
            
            $structure->additional_parents = $validated['additional_parents'] ?? [];
            $structure->save();

            return response()->json([
                'success' => true,
                'message' => 'Bawahan bersama berhasil disimpan',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jabatan' => 'required|string|max:255|unique:org_structures,jabatan',
            'divisi' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:org_structures,id',
            'karyawan_ids' => 'nullable|array',
            'karyawan_ids.*' => 'exists:karyawans,id',
        ]);

        try {
            $structure = new OrgStructure();
            $structure->jabatan = $validated['jabatan'];
            $structure->divisi = $validated['divisi'] ?? null;
            $structure->parent_id = $validated['parent_id'] ?? null;
            $structure->sort_order = 0;
            $structure->karyawan_ids = $validated['karyawan_ids'] ?? [];
            $structure->save();

            return response()->json([
                'success' => true,
                'message' => 'Jabatan berhasil ditambahkan',
                'data' => $structure,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menambahkan jabatan: ' . $e->getMessage()], 500);
        }
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required',
            'items.*.parent_id' => 'nullable|exists:org_structures,id',
            'items.*.sort_order' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['items'] as $item) {
                $id = str_contains($item['id'], '_dup_') ? explode('_dup_', $item['id'])[0] : $item['id'];
                $structure = OrgStructure::find($id);
                if ($structure) {
                    $structure->parent_id = $item['parent_id'] ?? null;
                    $structure->sort_order = $item['sort_order'];
                    $structure->save();
                }
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Hierarki berhasil disimpan']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan hierarki: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $id = str_contains($id, '_dup_') ? explode('_dup_', $id)[0] : $id;
        $structure = OrgStructure::find($id);

        if (!$structure) {
            return response()->json(['success' => false, 'message' => 'Jabatan tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'jabatan' => 'required|string|max:255|unique:org_structures,jabatan,id,' . $id,
            'divisi' => 'nullable|string|max:255',
            'karyawan_ids' => 'nullable|array',
            'karyawan_ids.*' => 'exists:karyawans,id',
        ]);

        try {
            $structure->jabatan = $validated['jabatan'];
            $structure->divisi = $validated['divisi'] ?? $structure->divisi;
            $structure->karyawan_ids = $validated['karyawan_ids'] ?? [];
            $structure->save();

            return response()->json([
                'success' => true,
                'message' => 'Jabatan berhasil diperbarui',
                'data' => $structure->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $id = str_contains($id, '_dup_') ? explode('_dup_', $id)[0] : $id;
        $structure = OrgStructure::find($id);

        if (!$structure) {
            return response()->json(['success' => false, 'message' => 'Jabatan tidak ditemukan'], 404);
        }

        if ($structure->children()->count() > 0) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Tidak bisa menghapus jabatan yang masih memiliki sub-jabatan.',
                ],
                422,
            );
        }

        try {
            $namaJabatan = $structure->jabatan;
            $structure->delete();

            return response()->json([
                'success' => true,
                'message' => "Jabatan \"{$namaJabatan}\" berhasil dihapus",
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
        }
    }
}
