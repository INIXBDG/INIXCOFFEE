<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\DailyActivity;
use App\Kanbans\TaskKanban;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class KanbanController extends Controller
{
    /**
     * Tampilkan halaman Kanban.
     */
    public function index()
    {
        $boards = [
            ['title' => 'To Do', 'key' => 'todo', 'color' => 'gray'],
            ['title' => 'In Progress', 'key' => 'inprogress', 'color' => 'blue'],
            ['title' => 'Done', 'key' => 'done', 'color' => 'green'],
        ];

        $currentUser = Auth::user();
        $karyawan = $currentUser->karyawan;

        $userDivisionName = null;
        $tasks = collect();

        if ($karyawan) {
            $userDivisionName = $karyawan->divisi;
        }

        if (!empty($userDivisionName)) {
            $tasksQuery = Task::with([
                                'user.karyawan',
                                'dailyActivities.user.karyawan'
                            ])
                            ->whereHas('user.karyawan', function ($query) use ($userDivisionName) {
                                $query->where('divisi', $userDivisionName);
                            });
            $tasks = $tasksQuery->get()->groupBy('state');

        } else {
        }

        // dd($tasks);
        return view('kanban.index', compact('boards', 'tasks', 'userDivisionName' ));
    }

    public function getTaskActivities(Task $task)
    {
        $activities = $task->dailyActivities()
                           ->with('user.karyawan') // Load user dan karyawan terkait aktivitas
                           ->latest('activity_date') // Urutkan tanggal terbaru
                           ->latest('created_at')    // Urutkan waktu input terbaru
                           ->get();

        return response()->json($activities);
    }

    /**
     * Tambahkan task baru.
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
                'user_id' => Auth::id(),
            ]);

            $task->load('user.karyawan');

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
     * Update status (state) task setelah drag & drop.
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

    /**
     * Hapus task dari database.
     */
    public function destroy(Task $task)
    {
        try {
            $task->dailyActivities()->delete();

            $task->delete();

            return response()->json(['success' => true, 'message' => 'Task berhasil dihapus.']);

        } catch (\Exception $e) {
            // Tangani jika ada error
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus task: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update task.
     */
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        // ✅ UBAH VALIDASI
        $validator = Validator::make($request->all(), [
            'title'       => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'date_start'  => 'sometimes|nullable|date', // Tambahkan ini
            'date_end'    => 'sometimes|nullable|date|after_or_equal:date_start', // Tambahkan ini
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $task->update($request->only('title', 'description', 'date_start', 'date_end'));

            $task->load('user.karyawan');

            return response()->json([
                'success' => true,
                'message' => 'Task berhasil diperbarui',
                'data' => $task,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui task',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

