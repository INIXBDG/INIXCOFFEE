<?php

namespace App\Http\Controllers;

use App\Models\VisitProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VisitProjectController extends Controller
{
    public function index()
    {
        return view('visit_projects.index');
    }

    public function get(Request $request)
    {
        if ($request->has('draw')) {
            $query = VisitProject::query();

            // Total records before filtering
            $recordsTotal = $query->count();

            // Search filter
            if ($search = $request->input('search.value')) {
                $query->where(function($q) use ($search) {
                    $q->where('kegiatan', 'like', "%{$search}%")
                      ->orWhere('lokasi', 'like', "%{$search}%")
                      ->orWhere('pic_name', 'like', "%{$search}%")
                      ->orWhere('desc', 'like', "%{$search}%");
                });
            }

            // Total records after filtering
            $recordsFiltered = $query->count();

            // Order
            $orderColumnIndex = $request->input('order.0.column');
            $orderDir = $request->input('order.0.dir', 'desc');

            $columnsMap = [
                1 => 'kegiatan',
                2 => 'lokasi',
                3 => 'pic_name',
                4 => 'tanggal',
                5 => 'photo_path',
                6 => 'desc'
            ];

            if (isset($columnsMap[$orderColumnIndex])) {
                $query->orderBy($columnsMap[$orderColumnIndex], $orderDir);
            } else {
                $query->latest(); // Default order
            }

            // Paging
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);

            if ($length != -1) {
                $query->offset($start)->limit($length);
            }

            $visits = $query->get();

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $visits
            ]);
        }

        $visits = VisitProject::latest()->get();

        return response()->json([
            'status' => 'success',
            'data'   => $visits
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kegiatan'   => 'required|string|max:255',
            'lokasi'     => 'required|string|max:255',
            'pic_name'   => 'required|string|max:255',
            'tanggal'    => 'required|date',
            'photo_path' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'desc'       => 'required|string',
        ]);

        if ($request->hasFile('photo_path')) {
            $validated['photo_path'] = $request->file('photo_path')->store('visit-photos', 'public');
        }

        VisitProject::create($validated);

        return redirect()->route('visit-projects.index')->with('success', 'Data aktivitas visit berhasil disimpan.');
    }



    public function update(Request $request, VisitProject $visitProject)
    {
        $validated = $request->validate([
            'kegiatan'   => 'required|string|max:255',
            'lokasi'     => 'required|string|max:255',
            'pic_name'   => 'required|string|max:255',
            'tanggal'    => 'required|date',
            'photo_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'desc'       => 'required|string',
        ]);

        if ($request->hasFile('photo_path')) {
            if ($visitProject->photo_path && Storage::disk('public')->exists($visitProject->photo_path)) {
                Storage::disk('public')->delete($visitProject->photo_path);
            }
            $validated['photo_path'] = $request->file('photo_path')->store('visit-photos', 'public');
        }

        $visitProject->update($validated);

        return redirect()->route('visit-projects.index')->with('success', 'Data aktivitas visit berhasil diperbarui.');
    }

    public function destroy(VisitProject $visitProject)
    {
        if ($visitProject->photo_path && Storage::disk('public')->exists($visitProject->photo_path)) {
            Storage::disk('public')->delete($visitProject->photo_path);
        }

        $visitProject->delete();

        return redirect()->route('visit-projects.index')->with('success', 'Data aktivitas visit berhasil dihapus.');
    }
}
