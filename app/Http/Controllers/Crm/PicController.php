<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\Perusahaan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PicController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $allowedJabatan = ['Adm Sales', 'HRD', 'Finance & Accounting', 'GM'];

        if ($user->jabatan === 'Sales') {
            $salesKey = $user->id_sales;
            $perusahaans = Perusahaan::where('sales_key', $salesKey)->get();
        } elseif (in_array($user->jabatan, $allowedJabatan)) {
            $perusahaans = Perusahaan::all();

        } else {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        // dd($perusahaans);
        return view('crm.pic.index', compact('perusahaans'));
    }


    public function indexJson()
    {
        try {
            $user = Auth::user();
            $allowedJabatan = ['Adm Sales', 'HRD', 'Finance & Accounting', 'GM'];

            $query = Contact::with('perusahaan')
                ->select(
                'id',
                'id_perusahaan',
                'sales_key',
                'nama',
                'status',
                'email',
                'cp',
                'divisi')->first();

            // Contoh pengecekan user, sesuaikan dengan kebutuhan
            if ($user->jabatan === 'Sales') {
                $query->where('sales_key', $user->sales_key);
            } elseif (!in_array($user->jabatan, $allowedJabatan)) {
                return response()->json([
                    'error' => 'Unauthorized access.'
                ], 403);
            }

            // Server-side processing datatables
            $draw = request()->get('draw', 1);
            $start = request()->get('start', 0);
            $length = request()->get('length', 10);
            $searchValue = request()->get('search')['value'] ?? '';
            $orderColumnIndex = request()->get('order')[0]['column'] ?? 0;
            $orderDirection = request()->get('order')[0]['dir'] ?? 'asc';

            $orderColumns = ['id', 'id_perusahaan', 'sales_key', 'nama', 'status', 'email', 'cp', 'divisi'];
            $orderColumn = $orderColumns[$orderColumnIndex] ?? 'id';

            $totalRecords = $query->count();

            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('nama', 'like', "%{$searchValue}%")
                      ->orWhere('email', 'like', "%{$searchValue}%")
                      ->orWhere('cp', 'like', "%{$searchValue}%")
                      ->orWhere('divisi', 'like', "%{$searchValue}%");
                });
            }

            $totalFiltered = $query->count();

            $data = $query->orderBy($orderColumn, $orderDirection)
                ->offset($start)
                ->limit($length)
                ->get()
                ->map(function ($item) {
                    // Format tampilan status misalnya
                    $statusLabel = $item->status == 1 ? 'Kontak Baru' : 'Aktif';

                    $namaPerusahaan = $item->perusahaan?->nama_perusahaan ?? '-';

                    return [
                        'nama' => $item->nama,
                        'perusahaan' => $namaPerusahaan,
                        'sales_key' => $item->sales_key,
                        'status' => $statusLabel,
                        'email' => $item->email,
                        'cp' => $item->cp,
                        'divisi' => $item->divisi,
                        'test' => 'test'
                    ];
                });

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalFiltered,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('PicController@indexJson Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_perusahaan' => 'required|exists:perusahaans,id',
            'nama'          => 'required|string',
            'email'         => 'nullable|email|unique:contacts,email',
            'cp'            => 'nullable|string',
            'divisi'        => 'nullable|string',
        ]);

        // Simpan contact baru
        $validated['status'] = '1';
        $validated['sales_key'] = $request->input('sales_key', auth()->user()->id_sales ?? null);
        Contact::create($validated);

    return redirect()->route('index.pic')->with('success', 'Contact berhasil ditambahkan.');
    }

    // public function update($id, Request $request) {

    //     $validated = $request->validate([
    //         'id_perusahaan' => 'required|exists:perusahaans,id',
    //         'nama'          => 'required|string',
    //         'email'         => 'nullable|email|unique:contacts,email',
    //         'cp'            => 'nullable|string',
    //         'divisi'        => 'nullable|string',
    //     ]);

    //     $contact = Contact::findOrFail($id);
    // }   


}
