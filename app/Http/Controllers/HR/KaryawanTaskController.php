<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\JobDesk;
use App\Models\Karyawan;
use App\Models\OrgStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KaryawanTaskController extends Controller
{
    public function index(Request $request)
    {
        $jobDesks = JobDesk::with(['orgStructure.karyawans'])->get();

        $orgStructures = OrgStructure::with([
            'karyawans' => function ($q) {
                $q->with('jobProfile');
            },
        ])
            ->has('karyawans')
            ->orderBy('jabatan')
            ->get();

        return view('HR.job_desk.index', compact('jobDesks', 'orgStructures'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_org' => 'required|exists:org_structures,id|unique:job_desks,id_org',
                'fungsi_utama' => 'nullable|string',
                'tujuan_jabatan' => 'nullable|string',
                'kualifikasi_pendidikan' => 'nullable|string',
                'pengalaman_kerja' => 'nullable|string',
                'kompetensi' => 'nullable|array',
                'kompetensi.*' => 'nullable|string',
                'karakteristik_pribadi' => 'nullable|string',
                'tugas_tanggung_jawab' => 'nullable|array',
                'tugas_tanggung_jawab.*.name' => 'nullable|string',
                'tugas_tanggung_jawab.*.details' => 'nullable|array',
                'tugas_tanggung_jawab.*.details.*' => 'nullable|string',
                'wewenang' => 'nullable|array',
                'wewenang.*.name' => 'nullable|string',
                'wewenang.*.details' => 'nullable|array',
                'wewenang.*.details.*' => 'nullable|string',
            ]);

            $validated = $this->cleanHierarchicalData($validated);
            $validated['sop'] = []; // Default kosong, SOP dihandle terpisah

            DB::beginTransaction();
            JobDesk::create($validated);
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Job Desk berhasil ditambahkan']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error store job desk: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $jobDesk = JobDesk::findOrFail($id);

            $validated = $request->validate([
                'id_org' => 'required|exists:org_structures,id|unique:job_desks,id_org,' . $id,
                'fungsi_utama' => 'nullable|string',
                'tujuan_jabatan' => 'nullable|string',
                'kualifikasi_pendidikan' => 'nullable|string',
                'pengalaman_kerja' => 'nullable|string',
                'kompetensi' => 'nullable|array',
                'kompetensi.*' => 'nullable|string',
                'karakteristik_pribadi' => 'nullable|string',
                'tugas_tanggung_jawab' => 'nullable|array',
                'tugas_tanggung_jawab.*.name' => 'nullable|string',
                'tugas_tanggung_jawab.*.details' => 'nullable|array',
                'tugas_tanggung_jawab.*.details.*' => 'nullable|string',
                'wewenang' => 'nullable|array',
                'wewenang.*.name' => 'nullable|string',
                'wewenang.*.details' => 'nullable|array',
                'wewenang.*.details.*' => 'nullable|string',
            ]);

            $validated = $this->cleanHierarchicalData($validated);
            // Jangan update SOP dari sini, biarkan SOP controller yang handle

            DB::beginTransaction();
            $jobDesk->update($validated);
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Job Desk berhasil diperbarui']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update job desk: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $jobDesk = JobDesk::with('orgStructure.karyawans')->find($id);
        return response()->json($jobDesk);
    }

    public function getJobDesk($orgId)
    {
        $jobDesk = JobDesk::where('id_org', $orgId)->first();
        return response()->json($jobDesk);
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $jobDesk = JobDesk::findOrFail($id);
            $jobDesk->delete();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Job Desk berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error delete job desk: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menghapus data'], 500);
        }
    }

    private function cleanHierarchicalData($data)
    {
        if (isset($data['kompetensi'])) {
            $data['kompetensi'] = array_values(array_filter($data['kompetensi']));
        }

        $hierarchicalFields = ['tugas_tanggung_jawab', 'wewenang'];

        foreach ($hierarchicalFields as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $cleaned = [];
                foreach ($data[$field] as $item) {
                    if (empty(trim($item['name'] ?? ''))) {
                        continue;
                    }
                    $details = isset($item['details']) ? array_values(array_filter($item['details'])) : [];
                    $cleaned[] = [
                        'name' => trim($item['name']),
                        'details' => $details,
                    ];
                }
                $data[$field] = $cleaned;
            }
        }

        return $data;
    }
}
