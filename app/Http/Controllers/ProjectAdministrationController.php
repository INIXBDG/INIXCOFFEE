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
            $data = ProjectAdministration::with('dataproject', 'dataproject.tasks', 'dataproject.client')->get();

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
        $request->validate([
            'current_stage' => 'required|in:kak,penganggaran,legal,dokumen_klien,pembayaran',
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,png|max:5120',
        ]);

        $columnMap = [
            'kak' => 'kak_file',
            'penganggaran' => 'budget_file',
            'legal' => 'legal_file',
            'dokumen_klien' => 'client_doc_file',
            'pembayaran' => 'payment_doc_file',
        ];

        $stage = $request->current_stage;

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
            // Hapus file lama
            if ($administration->{$columnMap[$stage]}) {
                \Storage::disk('public')->delete($administration->{$columnMap[$stage]});
            }

            // Simpan file baru
            $filePath = $request->file('file')->store('administrasi_projects', 'public');

            $administration->{$columnMap[$stage]} = $filePath;
            $administration->save();

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