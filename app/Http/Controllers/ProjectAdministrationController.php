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
        // Mulai query dengan relasi yang dibutuhkan
        $query = ProjectAdministration::with([
            'dataproject', 
            'dataproject.tasks', 
            'dataproject.client', 
            'project_handover'
        ]);

        // ✅ FILTER TAHUN: Jika parameter 'year' dikirim dari frontend
        if ($request->filled('year')) {
            $year = $request->year;
            $query->whereHas('dataproject', function ($q) use ($year) {
                // Filter berdasarkan tahun pembuatan atau tahun tanggal awal proyek
                $q->whereYear('created_at', $year)
                  ->orWhereYear('tanggal_awal', $year);
            });
        }

        // Urutkan dari yang terbaru agar UX lebih baik
        $query->orderBy('created_at', 'desc');

        $data = $query->get();

        return response()->json([
            'data' => $data
        ], 200);
    }

    /**
     * Menyimpan data Project dan ProjectAdministration baru ke basis data.
     */
    public function store(Request $request): JsonResponse
    {
        if ($request->ajax()) {
            $request->validate([
                'nama_projek' => 'required|string|max:255',
                'deskripsi' => 'nullable|string',
                'perusahaan_key' => 'required|exists:perusahaans,id',
            ]);

            DB::beginTransaction();
            try {
                $project = Project::create([
                    'name' => $request->nama_projek,
                    'description' => $request->deskripsi,
                    'client_id' => $request->perusahaan_key,
                    'phase' => 'administrasi',
                ]);

                ProjectAdministration::create([
                    'project_id' => $project->id,
                    'current_stage' => 'kak',
                    'pm_id' => 'AD', // Pertimbangkan auth()->user()->kode_karyawan di masa depan
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

    public function destroy($id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $project = Project::withTrashed()->findOrFail($id);

            $administration = ProjectAdministration::withTrashed()
                ->where('project_id', $project->id)
                ->first();
            if ($administration) {
                $administration->delete();
            }

            $handover = \App\Models\ProjectHandover::withTrashed()
                ->where('project_id', $project->id)
                ->first();
            if ($handover) {
                $handover->delete();
            }

            $project->delete();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Proyek dan administrasi terkait berhasil dihapus.'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus proyek: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStage(Request $request, $id): JsonResponse
    {
        if (!$request->ajax()) {
            return response()->json(['message' => 'Permintaan tidak valid'], 400);
        }

        $project = Project::findOrFail($id);
        $administration = ProjectAdministration::where('project_id', $project->id)->firstOrFail();

        // ✅ Penanganan Keputusan Akhir
        if ($request->filled('final_decision')) {
            $request->validate([
                'final_decision' => 'in:lanjut,gagal'
            ]);

            if ($request->final_decision === 'lanjut') {
                $project->update(['phase' => 'teknis']);
            } elseif ($request->final_decision === 'gagal') {
                $project->update(['phase' => 'gagal']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Keputusan akhir proyek berhasil ditetapkan.'
            ], 200);
        }

        // ✅ Peta Kolom Seluruh Tahapan
        $columnMap = [
            'kak' => 'kak_file',
            'proposal' => 'proposal_file',
            'penganggaran' => 'budget_file',
            'surat_pekerjaan_dimulai' => 'surat_pekerjaan_dimulai_file',
            'dokumen_klien' => 'client_doc_file',
            'pembayaran' => 'payment_doc_file',
            'bast' => 'bast_file',
            'final_report' => 'final_report_file',
        ];

        $stage = $request->current_stage;

        if (!array_key_exists($stage, $columnMap)) {
            return response()->json(['success' => false, 'message' => 'Stage tidak valid.'], 422);
        }

        if (!$request->hasFile('file')) {
            return response()->json(['success' => false, 'message' => 'File tidak ditemukan.'], 422);
        }

        $validStages = implode(',', array_keys($columnMap));
        $request->validate([
            'current_stage' => 'required|in:' . $validStages,
            'file'   => 'required|array',
            'file.*' => 'file|mimes:pdf,doc,docx,jpg,png|max:5120',
        ]);

        try {
            $files = $request->file('file');
            $paths = [];
            $columnName = $columnMap[$stage];

            $storageFolder = 'administrasi_projects';
            if ($stage === 'dokumen_klien') {
                $storageFolder = 'administrasi_projects/client_docs';
            } elseif (in_array($stage, ['bast', 'final_report'])) {
                $storageFolder = 'handover_projects';
            }

            foreach ($files as $file) {
                $paths[] = $file->store($storageFolder, 'public');
            }

            $targetModel = $administration;
            if (in_array($stage, ['bast', 'final_report'])) {
                $handover = $administration->project_handover;
                if (!$handover) {
                    $handover = \App\Models\ProjectHandover::create(['project_id' => $project->id]);
                    $administration->update(['project_handover_id' => $handover->id]);
                }
                $targetModel = $handover;
            }

            $existingData = $targetModel->{$columnName};
            $existingArray = [];
            
            if (!empty($existingData)) {
                $decoded = json_decode($existingData, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $existingArray = $decoded;
                } else {
                    $existingArray = [$existingData];
                }
            }

            $allFiles = array_merge($existingArray, $paths);
            $targetModel->{$columnName} = json_encode($allFiles);
            $targetModel->save();

            return response()->json([
                'success' => true,
                'message' => 'Dokumen tahap ' . strtoupper($stage) . ' berhasil diunggah (' . count($paths) . ' berkas ditambahkan).',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal upload file. ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Memperbarui informasi dasar dan tanggal proyek.
     */
    public function updateProjectInfo(Request $request, $id)
    {
        $request->validate([
            'nama_projek' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tanggal_awal' => 'nullable|date',
            'tanggal_akhir' => 'nullable|date|after_or_equal:tanggal_awal', // ✅ DIPERBAIKI: tanggal_akhir
        ]);

        try {
            $project = \App\Models\Project::findOrFail($id);
            $project->update([
                'name' => $request->nama_projek,
                'description' => $request->deskripsi,
                'tanggal_awal' => $request->tanggal_awal,
                'tanggal_akhir' => $request->tanggal_akhir, // ✅ DIPERBAIKI: tanggal_akhir
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data Proyek berhasil diperbarui.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data proyek.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}