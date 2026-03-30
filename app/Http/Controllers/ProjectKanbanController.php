<?php

namespace App\Http\Controllers;

use App\Models\ProjectTask;
use Illuminate\Http\Request;

class ProjectKanbanController extends Controller
{
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
}