<?php

namespace App\Http\Controllers;

use App\Models\ProjectTask;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectActivity;
use App\Models\ProjectAdministration;
use Illuminate\Http\JsonResponse; // ✅ WAJIB

class ProjectKanbanController extends Controller
{
    public function index()
    {
        // Memuat proyek beserta relasi administrasi dan data Project Manager
        $projects = Project::with('administration.projectManager')->where('phase', 'teknis')->get();
        return view('kanbanproject.index', compact('projects'));
    }

    /**
     * Memperbarui status tugas dari antarmuka Kanban (Drag and Drop).
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'required|in:backlog,to_do,in_progress,testing,deploy,validate,evaluasi'
            ]);

            // Memuat tugas beserta struktur relasi ke Project Manager
            $task = ProjectTask::with('project.administration.projectManager')->findOrFail($id);
            $newStatus = $request->status;

            // Identifikasi pengguna saat ini
            $currentUser = auth()->user();
            $currentKaryawanId = $currentUser->karyawan->kode_karyawan ?? null;

            // Identifikasi pemegang hak akses (PM dan Assignee)
            $pmId = $task->project->administration->projectManager->kode_karyawan ?? null;
            $assigneeId = $task->assignee_id;

            $isPM = ($currentKaryawanId === $pmId);
            $isAssignee = ($currentKaryawanId === $assigneeId);

            // 1. Otorisasi Eksplisit berdasarkan jenis pergerakan status
            if (in_array($newStatus, ['validate', 'evaluasi'])) {
                // Tahap validasi dan evaluasi dikunci secara eksklusif untuk Project Manager
                if (!$isPM) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Hanya Project Manager yang memiliki otoritas untuk memvalidasi atau mengevaluasi tugas.'
                    ], 403);
                }
            } else {
                // Tahap perkembangan tugas (progress) dapat diakses oleh Pelaksana (Assignee) atau Project Manager
                if (!$isPM && !$isAssignee) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Hanya Karyawan yang ditugaskan atau Project Manager yang dapat memindahkan status tugas ini.'
                    ], 403);
                }
            }

            // 2. Logika Loop Back (Mencegah Assignee mengembalikan tugas yang sudah masuk fase validasi/evaluasi)
            if (in_array($task->status, ['validate', 'evaluasi']) && in_array($newStatus, ['backlog', 'to_do', 'in_progress', 'testing', 'deploy'])) {
                if (!$isPM) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tugas ini sedang dalam fase peninjauan. Hanya Project Manager yang dapat menurunkannya kembali.'
                    ], 403);
                }
            }

            // 3. Eksekusi pembaruan data
            $task->update([
                'status' => $newStatus
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status task berhasil diperbarui.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status task.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengambil daftar karyawan yang dialokasikan pada kolom assignee_id.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getTeamMembers($id): JsonResponse
    {
        try {
            $projectAdmin = ProjectAdministration::where('project_id', $id)->firstOrFail();
            $assigneeIds = $projectAdmin->assignee_id ?? [];

            // Memuat data karyawan berdasarkan array ID yang tersimpan
            $members = \App\Models\Karyawan::whereIn('kode_karyawan', $assigneeIds)
                ->get()
                ->map(function ($karyawan) use ($id) {
                    // PERBAIKAN: Cek langsung ke kolom assignee_id tanpa melalui relasi bersarang
                    $hasTasks = ProjectTask::where('project_id', $id)
                        ->where('assignee_id', $karyawan->kode_karyawan)
                        ->exists();

                    return [
                        'id' => $karyawan->kode_karyawan,
                        'text' => $karyawan->nama_lengkap,
                        'locked' => $hasTasks
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $members
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data tim',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Memperbarui kolom assignee_id dan memvalidasi penguncian karyawan.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function assignTeam(Request $request, $id): JsonResponse
    {
        try {
            $projectAdmin = ProjectAdministration::where('project_id', $id)->firstOrFail();

            $request->validate([
                'employee_ids' => 'nullable|array',
                'employee_ids.*' => 'exists:karyawans,kode_karyawan'
            ]);

            $newEmployeeIds = $request->employee_ids ?? [];
            $currentEmployeeIds = $projectAdmin->assignee_id ?? [];

            $removedEmployeeIds = array_diff($currentEmployeeIds, $newEmployeeIds);

            if (!empty($removedEmployeeIds)) {
                // PERBAIKAN: Cek langsung menggunakan whereIn ke kolom assignee_id
                $tasksExist = ProjectTask::where('project_id', $id)
                    ->whereIn('assignee_id', $removedEmployeeIds)
                    ->exists();

                if ($tasksExist) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak dapat menghapus karyawan yang sudah memiliki tugas pada proyek ini.'
                    ], 422);
                }
            }

            // Memperbarui array karyawan pada tabel project_administrations
            $projectAdmin->update([
                'assignee_id' => $newEmployeeIds
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Alokasi tim berhasil diperbarui.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal update tim',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getTasks(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'project_id' => 'required|exists:projects,id'
            ]);

            $currentUser = auth()->user();
            $currentKaryawanId = $currentUser->karyawan->kode_karyawan ?? null;

            $project = Project::with('administration.projectManager')
                ->findOrFail($request->project_id);
            $pmId = $project->administration->projectManager->kode_karyawan ?? null;

            $query = ProjectTask::with(['assignee'])
                ->where('project_id', $request->project_id);

            if ($currentKaryawanId !== $pmId) {
                $query->where('assignee_id', $currentKaryawanId);
            }

            $tasks = $query->get();

            return response()->json([
                'success' => true,
                'data' => $tasks,
                'user_role' => $currentKaryawanId === $pmId ? 'pm' : 'member'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil task',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Membuat tugas baru.
     * Hanya Project Manager yang diizinkan (ditangani via middleware atau policy).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function storeTask(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'project_id' => 'required|exists:projects,id',
                'title' => 'required|string|max:255',
                'status' => 'required|in:backlog,to_do,in_progress,testing,deploy,validate,evaluasi',
                'startdate' => 'nullable|date',
                'enddate' => 'nullable|date|after_or_equal:date_start',
                'assignee_id' => 'nullable|exists:karyawans,kode_karyawan',
            ]);

            $project = Project::with('administration.projectManager')->findOrFail($request->project_id);

            $currentUser = auth()->user();
            $currentKaryawanId = $currentUser->karyawan->kode_karyawan ?? null;

            $pmId = $project->administration->projectManager->kode_karyawan ?? null;

            // 🚨 VALIDASI: hanya PM boleh create task
            if ($currentKaryawanId !== $pmId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya Project Manager yang dapat membuat task.'
                ], 403);
            }

            $task = ProjectTask::create([
                'project_id' => $request->project_id,
                'title' => $request->title,
                'status' => $request->status,
                'startdate' => $request->startdate,
                'enddate' => $request->enddate,
                'assignee_id' => $request->assignee_id
            ]);

            $task->load('assignee');

            return response()->json([
                'success' => true,
                'message' => 'Task berhasil dibuat.',
                'data' => $task
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat task',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Memperbarui detail tugas.
     * Hanya Project Manager yang diizinkan (ditangani via middleware atau policy).
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateTask(Request $request, $id): JsonResponse
    {
        try {
            $task = ProjectTask::with('project.administration.projectManager')->findOrFail($id);

            $currentUser = auth()->user();
            $currentKaryawanId = $currentUser->karyawan->kode_karyawan ?? null;

            $pmId = $task->project->administration->projectManager->kode_karyawan ?? null;

            if ($currentKaryawanId !== $pmId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya PM yang dapat mengedit task.'
                ], 403);
            }

            $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'startdate' => 'nullable|date',
                'enddate' => 'nullable|date|after_or_equal:date_start',
                'assignee_id' => 'nullable|exists:karyawans,kode_karyawan',
            ]);

            $task->update($request->only([
                'title',
                'description',
                'startdate',
                'enddate',
                'assignee_id'
            ]));

            $task->load('assignee');

            return response()->json([
                'success' => true,
                'message' => 'Task berhasil diperbarui.',
                'data' => $task
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal update task',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menghapus tugas.
     * Hanya Project Manager yang diizinkan. Tidak bisa dihapus jika status deploy, validate, evaluasi.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function deleteTask($id): JsonResponse
    {
        try {
            $task = ProjectTask::findOrFail($id);

            // Draf otorisasi (Sesuaikan dengan implementasi Gate/Policy Anda)
            // $this->authorize('deleteTask', $task);

            $restrictedStatuses = ['deploy', 'validate', 'evaluasi'];

            if (in_array($task->status, $restrictedStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Task tidak dapat dihapus karena berada pada tahap ' . strtoupper($task->status) . '.'
                ], 403); // 403 Forbidden
            }

            $task->delete();

            return response()->json([
                'success' => true,
                'message' => 'Task berhasil dihapus.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus task',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeActivity(Request $request, $id): JsonResponse
    {
        try {
            $task = ProjectTask::findOrFail($id);

            $currentUser = auth()->user();
            $currentKaryawanId = $currentUser->karyawan->kode_karyawan ?? null;

            // 🚨 VALIDASI: hanya assignee yang boleh input aktivitas
            if ($task->assignee_id !== $currentKaryawanId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya karyawan yang ditugaskan yang dapat mengisi aktivitas.'
                ], 403);
            }

            $request->validate([
                'activity' => 'required|string',
                'status' => 'required|string',
                'doc' => 'nullable|file|mimes:pdf,jpg,png,jpeg,docx|max:5120',
            ]);

            $docPath = null;
            if ($request->hasFile('doc')) {
                $docPath = $request->file('doc')->store('task_activities', 'public');
            }

            ProjectActivity::create([
                'project_task_id' => $task->id,
                'user_id' => $currentKaryawanId, // PERBAIKAN: Menggunakan kode_karyawan
                'activity' => $request->activity,
                'status' => $request->status,
                'activity_date' => now(),
                'doc' => $docPath,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Aktivitas berhasil ditambahkan.'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan aktivitas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengambil daftar riwayat aktivitas harian dari sebuah tugas.
     *
     * @param int $id
     * @return JsonResponse
     */
    /**
     * Mengambil daftar riwayat aktivitas harian dari sebuah tugas.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getActivities($id): JsonResponse
    {
        try {
            // PERBAIKAN: Gunakan 'user' saja, karena relasi 'user' sudah merujuk ke model Karyawan
            $activities = ProjectActivity::with('user')
                ->where('project_task_id', $id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $activities
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil aktivitas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}