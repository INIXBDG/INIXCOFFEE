<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\OrgStructure;
use App\Models\Karyawan;
use Illuminate\Http\Request;

class EmployeeStructureController extends Controller
{
    public function index()
    {
        return view('employee.structure.index');
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
                            ],
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
}
