<?php

namespace App\Http\Controllers\Webinar;

use App\Http\Controllers\Controller;
use App\Models\EventTodo;
use App\Models\Todo;
use App\Models\YearMapping;
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

                // 1. Ambil data pemetaan event untuk tahu Tahun & Kuartalnya
                $mapping = YearMapping::findOrFail($mappingId);

                // 2. Inisialisasi Query Todo Master
                $todoQuery = Todo::where('is_active', true);

                // 3. Logika Filter Berdasarkan Periode Waktu
                if ($mapping->year > 2026 || ($mapping->year == 2026 && $mapping->quarter >= 4)) {
                    // KONDISI BARU (Mulai Q4 2026 ke atas)
                    // Abaikan kategori lama 'Perintilan'
                    $todoQuery->where('category', '!=', 'Perintilan');
                } else {
                    // KONDISI LAMA (Sebelum Q4 2026)
                    // Abaikan task/kategori baru agar history kuartal sebelumnya tidak error
                    $todoQuery->where('category', '!=', 'Aktivitas Setelah Webinar')
                              ->where('task_name', '!=', 'Voucher webinar');
                }

                $masterTodos = $todoQuery->orderBy('sort_order')->get();

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
