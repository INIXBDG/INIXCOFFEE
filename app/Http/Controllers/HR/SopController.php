<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\JobDesk;
use App\Models\OrgStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SopController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_org' => 'required|exists:org_structures,id',
                'sop' => 'nullable|array',
                'sop.*.name' => 'nullable|string',
                'sop.*.details' => 'nullable|array',
                'sop.*.details.*' => 'nullable|string',
            ]);

            $validated = $this->cleanSopData($validated);

            DB::beginTransaction();

            // Cari JobDesk berdasarkan id_org, jika belum ada buat baru
            $jobDesk = JobDesk::firstOrNew(['id_org' => $validated['id_org']]);
            $jobDesk->sop = $validated['sop'] ?? [];

            // Jika record baru, set default value untuk field lain agar tidak error
            if (!$jobDesk->exists) {
                $jobDesk->fungsi_utama = null;
                $jobDesk->tujuan_jabatan = null;
                $jobDesk->kualifikasi_pendidikan = null;
                $jobDesk->pengalaman_kerja = null;
                $jobDesk->kompetensi = [];
                $jobDesk->karakteristik_pribadi = null;
                $jobDesk->tugas_tanggung_jawab = [];
                $jobDesk->wewenang = [];
            }

            $jobDesk->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'SOP berhasil ditambahkan',
                'id' => $jobDesk->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error store SOP: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $jobDesk = JobDesk::findOrFail($id);

            $validated = $request->validate([
                'sop' => 'nullable|array',
                'sop.*.name' => 'nullable|string',
                'sop.*.details' => 'nullable|array',
                'sop.*.details.*' => 'nullable|string',
            ]);

            $validated = $this->cleanSopData($validated);

            DB::beginTransaction();
            $jobDesk->sop = $validated['sop'] ?? [];
            $jobDesk->save();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'SOP berhasil diperbarui']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update SOP: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $jobDesk = JobDesk::with('orgStructure.karyawans')->find($id);
        return response()->json([
            'id' => $jobDesk->id,
            'id_org' => $jobDesk->id_org,
            'sop' => $jobDesk->sop ?? [],
        ]);
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $jobDesk = JobDesk::findOrFail($id);

            // Hanya hapus field SOP, bukan seluruh record
            $jobDesk->sop = [];
            $jobDesk->save();

            // Jika semua field kosong, hapus recordnya
            $isEmpty = empty($jobDesk->fungsi_utama) && empty($jobDesk->tujuan_jabatan) && empty($jobDesk->kualifikasi_pendidikan) && empty($jobDesk->pengalaman_kerja) && empty($jobDesk->kompetensi) && empty($jobDesk->karakteristik_pribadi) && empty($jobDesk->tugas_tanggung_jawab) && empty($jobDesk->wewenang) && empty($jobDesk->sop);

            if ($isEmpty) {
                $jobDesk->delete();
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'SOP berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error delete SOP: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menghapus SOP'], 500);
        }
    }

    private function cleanSopData($data)
    {
        if (isset($data['sop']) && is_array($data['sop'])) {
            $cleaned = [];
            foreach ($data['sop'] as $item) {
                if (empty(trim($item['name'] ?? ''))) {
                    continue;
                }
                $details = isset($item['details']) ? array_values(array_filter($item['details'])) : [];
                $cleaned[] = [
                    'name' => trim($item['name']),
                    'details' => $details,
                ];
            }
            $data['sop'] = $cleaned;
        }
        return $data;
    }
}
