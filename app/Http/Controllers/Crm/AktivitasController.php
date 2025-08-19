<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Aktivitas;
use App\Models\Perusahaan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AktivitasController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        $allowedJabatan = ['Adm Sales', 'HRD', 'Finance & Accounting', 'GM'];

        if ($user->jabatan === 'Sales') {
            $idSales = $user->id_sales;
            $data = Aktivitas::where('id_sales', $idSales)->get();
            $contact = Perusahaan::where('sales_key', $idSales)->get();
        } elseif (in_array($user->jabatan, $allowedJabatan)) {
            $data = Aktivitas::all();
            $contact = Perusahaan::all();
        } else {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return view('crm.aktivitas.index', compact('data', 'contact'));
    }


    public function indexJson()
    {
        try {
            $user = Auth::user();
            $allowedJabatan = ['Adm Sales', 'HRD', 'Finance & Accounting', 'GM'];

            $query = Aktivitas::with('perusahaan') // Eager load perusahaan to avoid N+1 queries
                ->select('id', 'id_sales', 'id_contact', 'aktivitas', 'subject', 'deskripsi', 'waktu_aktivitas');

            if ($user->jabatan === 'Sales') {
                $query->where('id_sales', $user->id_sales);
            } elseif (!in_array($user->jabatan, $allowedJabatan)) {
                return response()->json([
                    'error' => 'Unauthorized access.'
                ], 403);
            }

            // Server-side processing
            $draw = request()->get('draw', 1);
            $start = request()->get('start', 0);
            $length = request()->get('length', 10);
            $searchValue = request()->get('search')['value'] ?? '';
            $orderColumnIndex = request()->get('order')[0]['column'] ?? 0;
            $orderDirection = request()->get('order')[0]['dir'] ?? 'asc';

            $orderColumns = ['id', 'id_sales', 'id_contact', 'aktivitas', 'subject', 'deskripsi', 'waktu_aktivitas'];
            $orderColumn = $orderColumns[$orderColumnIndex] ?? 'id';

            $totalRecords = $query->count();

            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('aktivitas', 'like', "%{$searchValue}%")
                        ->orWhere('subject', 'like', "%{$searchValue}%")
                        ->orWhere('deskripsi', 'like', "%{$searchValue}%");
                });
            }

            $totalFiltered = $query->count();
            $data = $query->orderBy($orderColumn, $orderDirection)
                ->offset($start)
                ->limit($length)
                ->get()
                ->map(function ($item) {
                    // Safely handle perusahaan relationship
                $kontak = '-';
                if ($item->perusahaan instanceof \Illuminate\Database\Eloquent\Model) {
                    $kontak = $item->perusahaan->nama_perusahaan;
                    if ($item->perusahaan->cp) {
                        $kontak .= ' (' . $item->perusahaan->cp . ')';
                    }
                }
                    return [
                        'id' => $item->id,
                        'kontak' => $kontak,
                        'aktivitas' => ucfirst($item->aktivitas),
                        'subject' => $item->subject,
                        'deskripsi' => $item->deskripsi ?? '-',
                        'waktu_aktivitas' => \Carbon\Carbon::parse($item->waktu_aktivitas)->format('d/m/Y'),
                    ];
                });

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalFiltered,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('IndexJson Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_contact' => 'required|integer',
            'id_peluang' => 'required|integer',
            'aktivitas' => 'required|in:Call,Email,Visit,Meet',
            'subject' => 'required|string',
            'deskripsi' => 'required|string',
            'waktu_aktivitas' => 'required|date',
        ]);

        // hanya untuk test function di postman, setelah selesai tolong diubah -> auth()->user()->id_sales
        $validated['id_sales'] = $request->input('id_sales', auth()->user()->id_sales ?? null);
        $aktivitas = Aktivitas::create($validated);

        return back()->with([
            'message' => 'Aktivitas berhasil direcord.',
            'data' => $aktivitas,
        ]);
    }

    public function storeNew(Request $request)
    {
        $validated = $request->validate([
            'id_contact' => 'required|integer',
            'aktivitas' => 'required|in:Call,Email,Visit,Meet',
            'subject' => 'required|string',
            'deskripsi' => 'nullable|string',
            'waktu_aktivitas' => 'required|date',
        ]);

        // hanya untuk test function di postman, setelah selesai tolong diubah -> auth()->user()->id_sales
        $validated['id_sales'] = $request->input('id_sales', auth()->user()->id_sales ?? null);

        $aktivitas = Aktivitas::create($validated);

        return back()->with([
            'message' => 'Aktivitas berhasil direcord.',
            'data' => $aktivitas,
        ]);
    }


    public function delete($id)
    {
        try {
            $aktivitas = Aktivitas::find($id);

            if (!$aktivitas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aktivitas tidak ditemukan.',
                ], 404);
            }

            $aktivitas->delete();

            return response()->json([
                'success' => true,
                'message' => 'Aktivitas berhasil dihapus.',
                'id' => $id
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal menghapus aktivitas.',
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $aktivitas = Aktivitas::findOrFail($id);
        $aktivitas->update([
            'aktivitas' => $request->aktivitas,
            'subject' => $request->subject,
            'deskripsi' => $request->deskripsi,
            'waktu_aktivitas' => $request->waktu_aktivitas,
        ]);

        return response()->json(['message' => 'Aktivitas berhasil diperbarui.']);
    }
}
