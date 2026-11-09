<?php

namespace App\Http\Controllers;

use App\Models\Perusahaan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PerpindahanDBController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:View CRM Perpindahan DB', ['only' => ['index', 'getData']]);
    }

    public function index()
    {
        return view('crm.perpindahandb');
    }

    public function getData(Request $request)
    {
        $query = Perusahaan::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_perusahaan', 'like', "%{$search}%")
                    ->orWhere('lokasi', 'like', "%{$search}%")
                    ->orWhere('sales_key', 'like', "%{$search}%");
            });
        }

        $data = $query->select([
            'id',
            'nama_perusahaan',
            'kategori_perusahaan',
            'lokasi',
            'sales_key',
            'status',
            'history_sales',
            'created_at'
        ])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $data
        ]);
    }

    public function getSalesList()
    {
        $sales = User::with('karyawan:id,kode_karyawan,nama_lengkap')
            ->where(function ($q) {
                $q->where('jabatan', 'Sales')
                    ->orWhere('role', 'sales');
            })
            ->get()
            ->map(function ($user) {
                return [
                    'kode_karyawan' => $user->karyawan->kode_karyawan ?? null,
                    'nama_lengkap' => $user->karyawan->nama_lengkap ?? '-'
                ];
            })
            ->filter(fn($s) => $s['kode_karyawan'] !== null) // penting
            ->values();

        return response()->json(['sales' => $sales]);
    }

    public function transfer(Request $request)
    {
        $request->validate([
            'perusahaan_id' => 'required|exists:perusahaans,id',
            'sales_baru' => 'required|string',
            'alasan' => 'nullable|string|max:500',
        ]);

        $perusahaan = Perusahaan::findOrFail($request->perusahaan_id);

        $salesLama = $perusahaan->sales_key;
        $salesBaru = $request->sales_baru;

        // ambil history lama
        $history = [];

        if (!empty($perusahaan->history_sales)) {
            $decoded = json_decode($perusahaan->history_sales, true);
            if (is_array($decoded)) {
                $history = $decoded;
            }
        }

        // tambah history baru
        $history[] = [
            'tanggal' => now()->format('Y-m-d H:i:s'),
            'dari' => $salesLama,
            'ke' => $salesBaru,
            'oleh' => Auth::user()->name,
            'alasan' => $request->alasan
        ];

        // update
        $perusahaan->update([
            'sales_key' => $salesBaru,
            'history_sales' => json_encode($history)
        ]);

        DB::beginTransaction();
        try {
            $perusahaan->update([
                'sales_key' => $salesBaru,
                'history_sales' => json_encode($history, JSON_UNESCAPED_UNICODE)
            ]);

            DB::commit();
            return back()->with('success', 'Transfer berhasil.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function exportHistory($id)
    {
        $perusahaan = Perusahaan::findOrFail($id);
        $history = json_decode($perusahaan->history_sales ?? '[]', true);

        return response()->json([
            'perusahaan' => $perusahaan->nama_perusahaan,
            'history' => is_array($history) ? $history : []
        ]);
    }
}