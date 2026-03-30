<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\ProjectAdministration;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProjectAdministrationController extends Controller
{
    public function index()
    {
        return view('administrasi_projek.index');
    }
    public function getAdministrasi(Request $request): JsonResponse
    {
        if ($request->ajax()) {
            $data = ProjectAdministration::with('dataproject', 'dataproject.tasks', 'dataproject.client')->get();

            return response()->json([
                'data' => $data
            ], 200);
        }

        return response()->json(['message' => 'Permintaan tidak valid'], 400);
    }

    /**
     * Menyimpan data Project dan ProjectAdministration baru ke basis data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        if ($request->ajax()) {
            $request->validate([
                'nama_projek' => 'required|string|max:255',
                'deskripsi' => 'nullable|string',
                'perusahaan_key' => 'required|exists:perusahaans,id', // Sesuaikan nama tabel referensi perusahaan Anda
            ]);

            DB::beginTransaction();
            try {
                // 1. Pembuatan entitas Project
                $project = Project::create([
                    'name' => $request->nama_projek,
                    'description' => $request->deskripsi,
                    'perusahaan_key' => $request->perusahaan_key, // Menyimpan relasi klien
                    'phase' => 'administrasi',
                ]);

                // 2. Pembuatan entitas ProjectAdministration terkait
                ProjectAdministration::create([
                    'project_id' => $project->id,
                    'current_stage' => 'kak',
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Data Proyek dan Administrasi berhasil dibuat.'
                ], 201);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem saat menyimpan data: ' . $e->getMessage()
                ], 500);
            }
        }

        return response()->json(['message' => 'Permintaan tidak valid'], 400);
    }

    public function updateStage(Request $request, Project $project)
    {
        $this->authorize('manageAdministration', $project);

        $request->validate([
            'current_stage' => 'required|in:kak,penganggaran,legal,dokumen_klien,pembayaran,assign_tim',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048',
        ]);

        $administration = $project->administration()->firstOrCreate(
            ['project_id' => $project->id]
        );

        $administration->current_stage = $request->current_stage;

        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('administrasi_projects', 'public');
            
            // Pemetaan nama kolom berdasarkan tahap
            $columnMap = [
                'kak' => 'kak_file',
                'penganggaran' => 'budget_file',
                'legal' => 'legal_file',
                'dokumen_klien' => 'client_doc_file',
                'pembayaran' => 'payment_doc_file',
            ];

            if (array_key_exists($request->current_stage, $columnMap)) {
                // Hapus berkas lama jika ada
                if ($administration->{$columnMap[$request->current_stage]}) {
                    Storage::disk('public')->delete($administration->{$columnMap[$request->current_stage]});
                }
                $administration->{$columnMap[$request->current_stage]} = $filePath;
            }
        }

        // Jika tahap adalah assign_tim, simpan pm_id (kode_karyawan)
        if ($request->current_stage === 'assign_tim') {
            $this->authorize('assignTeam', $project);
            $request->validate(['pm_id' => 'required|string|exists:karyawans,kode_karyawan']);
            $administration->pm_id = $request->pm_id;
            $project->update(['phase' => 'teknis']); // Pindah ke fase Kanban
        }

        $administration->save();

        return redirect()->back()->with('success', 'Tahap administrasi berhasil diperbarui.');
    }
}