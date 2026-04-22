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
        // if ($request->ajax()) {
            $data = ProjectAdministration::with('dataproject', 'dataproject.tasks', 'dataproject.client', 'project_handover')->get();

            return response()->json([
                'data' => $data
            ], 200);
        // }

        // return response()->json(['message' => 'Permintaan tidak valid'], 400);
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
                    'client_id' => $request->perusahaan_key, // Menyimpan relasi klien
                    'phase' => 'administrasi',
                ]);

                // 2. Pembuatan entitas ProjectAdministration terkait
                ProjectAdministration::create([
                    'project_id' => $project->id,
                    'current_stage' => 'kak',
                    'pm_id' => 'AD',
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

        // ✅ Validasi Seragam: Memaksa format Array untuk SEMUA stage
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

            // 1. Tentukan Direktori Penyimpanan
            $storageFolder = 'administrasi_projects';
            if ($stage === 'dokumen_klien') {
                $storageFolder = 'administrasi_projects/client_docs';
            } elseif (in_array($stage, ['bast', 'final_report'])) {
                $storageFolder = 'handover_projects';
            }

            // 2. Simpan Berkas Fisik ke Storage
            foreach ($files as $file) {
                $paths[] = $file->store($storageFolder, 'public');
            }

            // 3. Tentukan Model Target (Administrasi atau Handover)
            $targetModel = $administration;
            
            if (in_array($stage, ['bast', 'final_report'])) {
                $handover = $administration->project_handover;
                if (!$handover) {
                    $handover = \App\Models\ProjectHandover::create(['project_id' => $project->id]);
                    $administration->update(['project_handover_id' => $handover->id]);
                }
                $targetModel = $handover;
            }

            // 4. Penggabungan Data Aman (Kompatibilitas Mundur untuk Data Lama)
            $existingData = $targetModel->{$columnName};
            $existingArray = [];
            
            if (!empty($existingData)) {
                $decoded = json_decode($existingData, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $existingArray = $decoded; // Format JSON Array
                } else {
                    $existingArray = [$existingData]; // Format lawas (Single String)
                }
            }

            // 5. Gabungkan Berkas Lama dengan Baru lalu Simpan
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
}