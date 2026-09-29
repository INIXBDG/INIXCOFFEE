<?php

namespace App\Http\Controllers\Webinar;

use App\Http\Controllers\Controller;
use App\Models\EventTodo;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Tambahkan facade Auth

class ChecklistController extends Controller
{
    public function index($mappingId)
    {
        $count = EventTodo::where('year_mapping_id', $mappingId)->count();
        $user = Auth::user();

        if ($count === 0) {
            // Cek permission langsung via database/Spatie
            if ($user && $user->can('update-checklist')) {
                $masterTodos = Todo::where('is_active', true)->orderBy('sort_order')->get();
                $newChecklists = [];
                foreach ($masterTodos as $todo) {
                    $newChecklists[] = [
                        'year_mapping_id' => $mappingId,
                        'todo_id' => $todo->id,
                        'is_checked' => false,
                        'pic' => null,
                        'notes' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                EventTodo::insert($newChecklists);
            }
        }

        $checklists = EventTodo::where('year_mapping_id', $mappingId)
            ->with('todo')
            ->get()
            ->sortBy(function($checklist) {
                return $checklist->todo->sort_order;
            })
            ->values();

        return response()->json($checklists);
    }

    public function toggle($id)
    {
        $user = Auth::user();

        // Cek permission update-checklist
        if (!$user || !$user->can('update-checklist')) {
            return response()->json(['message' => 'Akses Ditolak: Anda tidak memiliki izin mencentang checklist.'], 403);
        }

        $checklist = EventTodo::findOrFail($id);
        $checklist->update([
            'is_checked' => !$checklist->is_checked
        ]);

        return response()->json(['success' => true, 'is_checked' => $checklist->is_checked]);
    }

    public function updateDetail(Request $request, $id)
    {
        $user = Auth::user();

        // Cek permission update-checklist
        if (!$user || !$user->can('update-checklist')) {
            return response()->json(['message' => 'Akses Ditolak: Anda tidak memiliki izin mengubah PIC/Catatan.'], 403);
        }

        $checklist = EventTodo::findOrFail($id);
        $checklist->update([
            'pic' => $request->pic,
            'notes' => $request->notes
        ]);

        return response()->json(['success' => true]);
    }
}
