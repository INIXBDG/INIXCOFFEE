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

        // ✅ Gunakan filled() supaya tidak ke-trigger kalau kosong
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

        // ✅ Validasi upload
       

        $columnMap = [
            'kak' => 'kak_file',
            'proposal' => 'proposal_file',
            'penganggaran' => 'budget_file',
            'surat_pekerjaan_dimulai' => 'surat_pekerjaan_dimulai_file',
            'dokumen_klien' => 'client_doc_file',
            'pembayaran' => 'payment_doc_file',

            // ✅ TAMBAHAN HANDOVER
            'bast' => 'bast_file',
            'final_report' => 'final_report_file',
        ];

        $stage = $request->current_stage;
        if ($stage === 'dokumen_klien') {
            $request->validate([
                'file' => 'required|array',
                'file.*' => 'file|mimes:pdf,doc,docx,jpg,png|max:5120',
            ]);
        } else {
            $request->validate([
                'current_stage' => 'required|in:kak,penganggaran,legal,dokumen_klien,pembayaran',
                'file' => 'required|file|mimes:pdf,doc,docx,jpg,png|max:5120',
            ]);
        }
        
        // ❗ Tambahkan validasi file benar-benar ada
        if (!$request->hasFile('file')) {
            return response()->json([
                'success' => false,
                'message' => 'File tidak ditemukan dalam request.'
            ], 422);
        }

        if (!array_key_exists($stage, $columnMap)) {
            return response()->json([
                'success' => false,
                'message' => 'Stage tidak valid.'
            ], 422);
        }

        try {
            if ($stage === 'dokumen_klien') {

                $files = $request->file('file');
                $paths = [];

                // ambil file lama (kalau ada)
                $existingFiles = $administration->client_doc_file 
                    ? json_decode($administration->client_doc_file, true) 
                    : [];

                foreach ($files as $file) {
                    $paths[] = $file->store('administrasi_projects/client_docs', 'public');
                }

                // gabungkan file lama + baru (optional, bisa juga replace total)
                $allFiles = array_merge($existingFiles, $paths);

                $administration->client_doc_file = json_encode($allFiles);
                $administration->save();

            }
            if (in_array($stage, ['bast', 'final_report'])) {

                $handover = $administration->project_handover;

                // Kalau belum ada, buat dulu
                if (!$handover) {
                    $handover = \App\Models\ProjectHandover::create([
                        'project_id' => $project->id,
                    ]);

                    $administration->update([
                        'project_handover_id' => $handover->id
                    ]);
                }

                // Hapus file lama kalau ada
                if ($handover->{$columnMap[$stage]}) {
                    \Storage::disk('public')->delete($handover->{$columnMap[$stage]});
                }

                $filePath = $request->file('file')->store('handover_projects', 'public');

                $handover->{$columnMap[$stage]} = $filePath;
                $handover->save();

            } else {
                // existing logic (administrasi)
                if ($administration->{$columnMap[$stage]}) {
                    \Storage::disk('public')->delete($administration->{$columnMap[$stage]});
                }

                $filePath = $request->file('file')->store('administrasi_projects', 'public');

                $administration->{$columnMap[$stage]} = $filePath;
                $administration->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Dokumen tahap ' . strtoupper($stage) . ' berhasil diunggah.',
                'path' => $filePath // optional debug
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal upload file.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}