<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\karyawan;
use App\Models\OrgChart;
use App\Models\OrgStructure;
use Illuminate\Http\Request;

class EmployeeStructureController extends Controller
{
    public function index()
    {
        return view('employee.structure.index');
    }

    public function apiTree(Request $request)
    {
        $charts = OrgChart::orderBy('id')->get(['id', 'name']);
        $chart = $charts->firstWhere('id', (int) $request->query('chart')) ?? $charts->first();

        if (!$chart) {
            return response()->json(['charts' => [], 'chart' => null, 'tree' => []]);
        }

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
                        'pos_x' => $node->pos_x,
                        'pos_y' => $node->pos_y,
                        'children' => $build($node->id),
                    ];
                })
                ->values();
        };

        return response()->json([
            'charts' => $charts,
            'chart' => $chart->only(['id', 'name']),
            'tree' => $build(null),
        ]);
    }
}