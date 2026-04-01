<?php

namespace App\Http\Controllers;

use App\Models\ProjectTask;
use Illuminate\Http\Request;
use App\Models\Project;

class ProjectKanbanController extends Controller
{
    public function index()
    {
        // Hanya mengambil proyek yang telah melewati tahap administrasi dan berstatus teknis
        $projects = Project::where('phase', 'teknis')->get();
        
        return view('kanbanproject.index', compact('projects'));
    }

    public function updateStatus(Request $request, ProjectTask $task)
    {
        $request->validate([
            'status' => 'required|in:backlog,to_do,in_progress,testing,validate,deploy,evaluasi'
        ]);

        $newStatus = $request->status;

        // Validasi otorisasi berdasarkan target status
        if ($newStatus === 'validate') {
            $this->authorize('validateTask', $task);
        } elseif ($newStatus === 'evaluasi') {
            $this->authorize('evaluateTask', $task);
        } else {
            $this->authorize('updateProgress', $task);
        }

        // Logika pengulangan (loop back) berdasarkan diagram
        // Jika Validate = no, kembali ke Backlog
        if ($task->status === 'validate' && $newStatus === 'backlog') {
             $this->authorize('validateTask', $task);
        }
        
        // Jika Evaluasi (Good?) = no, kembali ke Backlog
        if ($task->status === 'evaluasi' && $newStatus === 'backlog') {
             $this->authorize('evaluateTask', $task);
        }

        $task->update([
            'status' => $newStatus
        ]);

        return response()->json(['success' => true, 'message' => 'Status task berhasil diperbarui.']);
    }

    /**
     * Mengambil daftar karyawan yang saat ini dialokasikan ke proyek.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getTeamMembers($id): JsonResponse
    {
        $project = Project::with('teamMembers')->findOrFail($id);
        
        $members = $project->teamMembers->map(function($karyawan) {
            return [
                'id' => $karyawan->kode_karyawan,
                'text' => $karyawan->nama_lengkap
            ];
        });

        return response()->json(['success' => true, 'data' => $members]);
    }

    /**
     * Memperbarui alokasi karyawan pada proyek.
     * Mencegah pencabutan karyawan jika mereka sudah memiliki tugas di proyek ini.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function assignTeam(Request $request, $id): JsonResponse
    {
        $project = Project::findOrFail($id);
        $request->validate([
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'exists:karyawans,kode_karyawan'
        ]);

        $newEmployeeIds = $request->employee_ids ?? [];
        $currentEmployeeIds = $project->teamMembers()->pluck('karyawans.kode_karyawan')->toArray();
        
        // Identifikasi karyawan yang akan dihapus dari proyek
        $removedEmployeeIds = array_diff($currentEmployeeIds, $newEmployeeIds);

        // Validasi: Cegah penghapusan jika karyawan memiliki tugas di proyek ini
        if (!empty($removedEmployeeIds)) {
            $tasksForRemovedEmployees = ProjectTask::where('project_id', $project->id)
                ->whereHas('user.karyawan', function($query) use ($removedEmployeeIds) {
                    $query->whereIn('kode_karyawan', $removedEmployeeIds);
                })->exists();

            if ($tasksForRemovedEmployees) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus karyawan yang sudah memiliki tugas pada proyek ini.'
                ], 422);
            }
        }

        // Sinkronisasi data pada tabel pivot (project_karyawan)
        $project->teamMembers()->sync($newEmployeeIds);

        return response()->json([
            'success' => true, 
            'message' => 'Alokasi tim berhasil diperbarui.'
        ]);
    }
}