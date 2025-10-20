<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Kanbans\TaskKanban;
use Illuminate\Support\Facades\Validator;

class KanbanController extends Controller
{
    /**
     * 🗂️ Tampilkan halaman Kanban.
     */
    public function index()
    {
        $kanban = new TaskKanban();
        return view('kanban.index', compact('kanban'));
    }

    /**
     * 🆕 Tambahkan task baru.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'state' => 'required|string|in:todo,inprogress,done',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $task = Task::create([
                'title' => $request->title,
                'description' => $request->description,
                'state' => $request->state,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Task berhasil ditambahkan',
                'data' => $task,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menambahkan task',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🔄 Update status (state) task setelah drag & drop.
     */
    public function updateState(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:tasks,id',
            'state' => 'required|string|in:todo,inprogress,done',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $task = Task::findOrFail($request->id);
            $task->update(['state' => $request->state]);

            return response()->json([
                'success' => true,
                'message' => 'Status task berhasil diperbarui',
                'data' => $task,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
