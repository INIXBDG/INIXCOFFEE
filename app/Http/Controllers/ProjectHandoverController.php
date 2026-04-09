<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectHandover;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProjectHandoverController extends Controller
{
    /**
     * Menampilkan antarmuka daftar Serah Terima Proyek.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('handover_project.index');
    }

    /**
     * Mengambil daftar proyek yang siap untuk diserahterimakan (AJAX).
     * Syarat: Berada pada fase 'teknis' dan seluruh/mayoritas tugasnya telah dievaluasi.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getHandovers(Request $request): JsonResponse
    {
        try {
            // Memuat proyek beserta relasi tugas, serah terima, klien, dan PM
            $projects = Project::with([
                'tasks',
                'handover',
                'client',
                'administration.projectManager'
            ])
                ->whereIn('phase', ['teknis', 'selesai']) // Mengambil fase yang relevan
                ->get()
                ->filter(function ($project) {
                    $totalTasks = $project->tasks->count();

                    // Abaikan proyek yang belum memiliki tugas sama sekali
                    if ($totalTasks === 0) {
                        return false;
                    }

                    // Menghitung tugas yang sudah selesai (validate atau evaluasi)
                    $completedTasks = $project->tasks->whereIn('status', ['validate', 'evaluasi'])->count();

                    // Logika: Proyek dianggap "Siap Serah Terima" jika 100% tugas telah dievaluasi/divalidasi.
                    // Anda dapat mengubah angka 1 menjadi 0.9 jika mendefinisikan "mayoritas" sebagai 90%.
                    $completionRate = $completedTasks / $totalTasks;

                    return $completionRate === 1;
                })
                ->values(); // Mengurutkan ulang indeks array untuk respons JSON

            return response()->json([
                'success' => true,
                'data' => $projects
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data serah terima proyek.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengunggah dokumen BAST dan memperbarui status serah terima.
     *
     * @param Request $request
     * @param int $projectId
     * @return JsonResponse
     */
    /**
     * Mengunggah dokumen BAST dan memperbarui status proyek.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    /**
     * Mengunggah dokumen BAST dan memperbarui status proyek.
     * Hanya dapat dilakukan oleh SPV Sales, GM, atau Project Manager terkait.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function uploadBast(Request $request, $id): JsonResponse
    {
        try {
            // 1. Ambil data proyek untuk mengecek siapa PM-nya
            $project = Project::with('administration.projectManager')->findOrFail($id);

            // 2. Identifikasi Pengguna yang sedang masuk (Login)
            $currentUser = auth()->user();
            $userRole = $currentUser->jabatan ?? '';
            $currentKaryawanId = $currentUser->karyawan->kode_karyawan ?? null;
            $pmId = $project->administration->projectManager->kode_karyawan ?? null;

            // 3. Validasi Hak Akses (Otorisasi)
            $isAuthorizedRole = in_array($userRole, ['SPV Sales', 'GM']);
            $isPM = ($currentKaryawanId === $pmId);

            if (!$isAuthorizedRole && !$isPM) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Hanya SPV Sales, GM, atau Project Manager yang diizinkan mengunggah dokumen BAST.'
                ], 403);
            }

            // 4. Validasi Dokumen
            $request->validate([
                'bast_file' => 'required|file|mimes:pdf,doc,docx,jpg,png|max:5120',
            ]);

            // 5. Eksekusi Penyimpanan
            $filePath = $request->file('bast_file')->store('project_handovers', 'public');

            ProjectHandover::updateOrCreate(
                ['project_id' => $id],
                [
                    'bast_file' => $filePath,
                    'handover_date' => now(),
                    'status' => 'selesai'
                ]
            );

            Project::where('id', $id)->update(['phase' => 'selesai']);

            return response()->json([
                'success' => true,
                'message' => 'Dokumen BAST berhasil diunggah. Proyek dinyatakan selesai.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunggah dokumen BAST.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}