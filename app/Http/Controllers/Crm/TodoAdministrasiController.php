<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\TodoAdministrasi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TodoAdministrasiController extends Controller
{
    public function index(Request $request)
    {
        $query = TodoAdministrasi::query();
        
        $filterType = $request->get('filter_type', 'semua');
        $tahun = $request->get('tahun');
        $bulan = $request->get('bulan');
        $triwulan = $request->get('triwulan');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $today = Carbon::today();
        
        // Apply filters berdasarkan created_at
        if ($filterType === 'tahun' && $tahun && $tahun !== 'default') {
            // Filter by year
            $query->whereBetween('created_at', [
                Carbon::create($tahun, 1, 1)->startOfDay(),
                Carbon::create($tahun, 12, 31)->endOfDay()
            ]);
        } elseif ($filterType === 'bulan' && $bulan && $bulan !== 'default') {
            // Filter by month (current year)
            $query->whereMonth('created_at', $bulan)
                  ->whereYear('created_at', $today->year);
        } elseif ($filterType === 'triwulan' && $triwulan && $triwulan !== 'default') {
            // Filter by quarter (current year)
            $startMonth = ($triwulan - 1) * 3 + 1;
            $endMonth = $triwulan * 3;
            $filterStart = Carbon::create($today->year, $startMonth, 1)->startOfDay();
            $filterEnd = Carbon::create($today->year, $endMonth, 1)->endOfMonth()->endOfDay();
            $query->whereBetween('created_at', [$filterStart, $filterEnd]);
        } elseif ($filterType === 'custom' && $startDate && $endDate) {
            // Filter by custom date range
            $filterStart = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
            $filterEnd = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();
            $query->whereBetween('created_at', [$filterStart, $filterEnd]);
        }
        
        $todos = $query->orderByDesc('created_at')->paginate(10);
        
        return view('crm.todoAdministrasi.index', compact('todos', 'filterType', 'tahun', 'bulan', 'triwulan', 'startDate', 'endDate'));
    }

    public function store(Request $request)
    {
        try {
            $todo = $request->validate([
                'case' => 'required|string',
                'catatan' => 'nullable|string',
            ]);

            TodoAdministrasi::create($todo);

            return redirect()->route('todo-administrasi.index')->with('success', 'Todo berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->route('todo-administrasi.index')->with('error', 'Gagal menambahkan todo. Silakan coba lagi.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Validate input
            $todo = $request->validate([
                'case' => 'required|string',
                'solusi' => 'nullable|string',
                'status' => 'required|in:progres,selesai,gagal',
                'catatan' => 'nullable|string',
                'tanggal_selesai' => 'nullable|date',
                'dokumen' => 'nullable|file|max:10240',
            ]);

            // Find todo item
            $todoItem = TodoAdministrasi::findOrFail($id);

            // Handle dokumen file upload
            if ($request->hasFile('dokumen') && $request->file('dokumen')->isValid()) {
                try {
                    // Delete old file if exists
                    if ($todoItem->dokumen && Storage::exists($todoItem->dokumen)) {
                        Storage::delete($todoItem->dokumen);
                    }

                    // Store new file
                    $file = $request->file('dokumen');
                    $path = $file->store('todo-administrasi', 'public');
                    $todo['dokumen'] = $path;
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', 'Gagal mengunggah dokumen. Silakan coba lagi.')->withInput();
                }
            }

            // Update todo
            $todoItem->update($todo);

            return redirect()->route('todo-administrasi.index')->with('success', 'Todo berhasil diupdate.');
        } catch (\Exception $e) {
            return redirect()->route('todo-administrasi.index')->with('error', 'Gagal mengupdate todo. Silakan coba lagi.');
        }
    }
    public function destroy($id)
    {
        $todoItem = TodoAdministrasi::findOrFail($id);
        $todoItem->delete();

        return redirect()->route('todo-administrasi.index')->with('success', 'Todo berhasil dihapus.');
    }}
