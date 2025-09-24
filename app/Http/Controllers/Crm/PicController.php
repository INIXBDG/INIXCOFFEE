<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\Perusahaan;
use App\Models\Peserta;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class PicController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $allowedJabatan = ['Adm Sales', 'SPV Sales', 'HRD', 'Finance & Accounting', 'GM', 'Sales', 'Direktur Utama', 'Direktur'];
        // dd($user);
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

    public function indexJson(Request $request)
    {
        try {
            $user = Auth::user();
            $allowedJabatan = ['Adm Sales', 'SPV Sales', 'HRD', 'Finance & Accounting', 'GM', 'Sales', 'Direktur Utama', 'Direktur'];

            Log::debug('User Jabatan: ' . ($user->jabatan ?? 'unknown'));

            // Validasi akses jabatan
            if ($user->jabatan === 'Sales') {
                $salesFilter = $user->id_sales;
            } elseif (!in_array($user->jabatan, $allowedJabatan)) {
                return response()->json([
                    'error' => 'Unauthorized access.',
                ], 403);
            }

            $pesertaQuery = DB::table('pesertas as p')
                ->selectRaw('
                    p.id AS peserta_id,
                    p.nama,
                    p.email,
                    p.no_hp AS cp,
                    p.perusahaan_key,
                    NULL AS divisi,
                    pr.nama_perusahaan,
                    pr.sales_key,
                    p.updated_at AS created_at,
                    NULL AS contact_status,
                    "Peserta Regist" AS status_text
                ')
                ->join('perusahaans as pr', 'p.perusahaan_key', '=', 'pr.id');

            if ($user->jabatan === 'Sales') {
                $pesertaQuery->where('pr.sales_key', $salesFilter);
            }

            $contactQuery = DB::table('contacts as c')
                ->selectRaw('
                    c.id AS contact_id,
                    c.nama,
                    c.email,
                    c.cp,
                    c.id_perusahaan AS perusahaan_key,
                    c.divisi,
                    pr.nama_perusahaan,
                    pr.sales_key,
                    c.updated_at AS created_at,
                    c.status AS contact_status,
                    CASE
                        WHEN c.status = "1" THEN "Contact Baru"
                        WHEN c.status = "0" THEN "Contact"
                        ELSE "Unknown"
                    END AS status_text
                ')
                ->join('perusahaans as pr', 'c.id_perusahaan', '=', 'pr.id');

            if ($user->jabatan === 'Sales') {
                $contactQuery->where('pr.sales_key', $salesFilter);
            }

            $pesertaSql = $pesertaQuery->toSql();
            $pesertaBindings = $pesertaQuery->getBindings();

            $contactSql = $contactQuery->toSql();
            $contactBindings = $contactQuery->getBindings();

            $unionSql = "({$pesertaSql}) UNION ALL ({$contactSql})";
            $unionBindings = array_merge($pesertaBindings, $contactBindings);

            Log::debug('Manual Union SQL', [
                'sql' => $unionSql,
                'bindings' => $unionBindings,
            ]);

            $masterQuery = DB::table(DB::raw("({$unionSql}) as master"))
                ->setBindings($unionBindings);

            $draw = $request->get('draw', 1);
            $start = $request->get('start', 0);
            $length = $request->get('length', 10);
            $searchValue = $request->get('search')['value'] ?? '';
            $orderColumnIndex = $request->get('order')[0]['column'] ?? 0;
            $orderDirection = $request->get('order')[0]['dir'] ?? 'desc';

            $orderColumns = [
                'nama',
                'email',
                'cp',
                'perusahaan_key',
                'divisi',
                'contact_status',
                'nama_perusahaan',
                'sales_key',
                'created_at'
            ];
            $orderColumn = $orderColumns[$orderColumnIndex] ?? 'created_at';

            if (!empty($searchValue)) {
                $masterQuery->where(function ($q) use ($searchValue) {
                    $searchValueLower = strtolower($searchValue);

                    // Cek pencarian berdasarkan nama, email, cp, dan perusahaan
                    $q->whereRaw('LOWER(nama) LIKE ?', ["%{$searchValueLower}%"])
                        ->orWhereRaw('LOWER(email) LIKE ?', ["%{$searchValueLower}%"])
                        ->orWhereRaw('LOWER(cp) LIKE ?', ["%{$searchValueLower}%"])
                        ->orWhereRaw('LOWER(nama_perusahaan) LIKE ?', ["%{$searchValueLower}%"])
                        ->orWhereRaw('LOWER(sales_key) LIKE ?', ["%{$searchValueLower}%"])
                        ->orWhereRaw('LOWER(status_text) LIKE ?', ["%{$searchValueLower}%"]);
                });
            }


            $totalFiltered = $masterQuery->count();
            Log::debug('TotalFiltered: ' . $totalFiltered);

            $rawData = $masterQuery
                ->orderBy($orderColumn, $orderDirection)
                ->offset($start)
                ->limit($length)
                ->get();

            $totalRecords = DB::table(DB::raw("({$unionSql}) as master"))
                ->setBindings($unionBindings)
                ->count();

            $data = $rawData->map(function ($item) {
                if ($item->contact_status === '1' || $item->contact_status === 1) {
                    $statusText = 'Contact Baru';
                } elseif ($item->contact_status === '0' || $item->contact_status === 0) {
                    $statusText = 'Contact';
                } else {
                    $statusText = 'Peserta Regist';
                }

                return [
                    'nama' => $item->nama ?? '-',
                    'perusahaan' => $item->nama_perusahaan ?? '-',
                    'sales_key' => $item->sales_key ?? '-',
                    'status' => $statusText,
                    'email' => $item->email ?? '-',
                    'cp' => $item->cp ?? '-',
                    'divisi' => $item->divisi ?? '-',
                ];
            });

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalFiltered,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('PesertaController@indexJson Error: ' . $e->getMessage(), [
                'user_id' => Auth::id() ?? 'unknown',
                'user_jabatan' => $user->jabatan ?? 'unknown',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Terjadi kesalahan pada server. Silakan hubungi administrator.',
                'message' => config('app.debug') ? $e->getMessage() : 'Internal Server Error',
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
