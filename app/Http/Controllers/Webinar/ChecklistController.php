<?php

namespace App\Http\Controllers\Webinar;

use App\Http\Controllers\Controller;
use App\Models\EventTodo;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Tambahkan facade Auth

class ChecklistController extends Controller
{
    /**
     * AMBIL LIST CHECKLIST PER EVENT
     */
    public function index($mappingId)
    {
        $count = EventTodo::where('year_mapping_id', $mappingId)->count();
        $user = Auth::user(); // Ambil user dengan aman

        if ($count === 0) {
            // Cek: User ada DAN jabatannya Tim Digital
            if ($user && $user->jabatan === 'Tim Digital') {
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

    /**
     * TOGGLE STATUS (CENTANG / UNCENTANG)
     */
    public function toggle($id)
    {
        $user = Auth::user();


        if (!$user || $user->jabatan !== 'Tim Digital') {
            return response()->json(['message' => 'Akses Ditolak.'], 403);
        }

        $checklist = EventTodo::findOrFail($id);
        $checklist->update([
            'is_checked' => !$checklist->is_checked
        ]);

        return response()->json(['success' => true, 'is_checked' => $checklist->is_checked]);
    }

    /**
     * UPDATE DETAIL (PJ & CATATAN)
     */
    public function updateDetail(Request $request, $id)
    {
        $user = Auth::user();

        // FIX ERROR DISINI JUGA:
        if (!$user || $user->jabatan !== 'Tim Digital') {
            return response()->json(['message' => 'Akses Ditolak.'], 403);
        }

        $checklist = EventTodo::findOrFail($id);
        $checklist->update([
            'pic' => $request->pic,
            'notes' => $request->notes
        ]);

        return response()->json(['success' => true]);
    }
}
