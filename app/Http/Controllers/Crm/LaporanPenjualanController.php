<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Peluang;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LaporanPenjualanController extends Controller
{
    public function index(Request $request)
    {
        $tahunDipilih = $request->query('tahun', now()->year);

        // Ringkasan untuk MERAH (Closed Win)
        $ringkasanMerah = Peluang::whereNotNull('merah')
            ->whereYear('merah', $tahunDipilih)
            ->select(
                'id_sales',
                DB::raw('CASE
                    WHEN MONTH(merah) BETWEEN 1 AND 3 THEN "TR1"
                    WHEN MONTH(merah) BETWEEN 4 AND 6 THEN "TR2"
                    WHEN MONTH(merah) BETWEEN 7 AND 9 THEN "TR3"
                    WHEN MONTH(merah) BETWEEN 10 AND 12 THEN "TR4"
                END as triwulan'),
                DB::raw('SUM(netsales * pax) as total_jumlah')
            )
            ->groupBy('id_sales', 'triwulan')
            ->get()
            ->groupBy('id_sales')
            ->map(function ($grup) {
                return $grup->pluck('total_jumlah', 'triwulan')->toArray();
            })
            ->toArray();

        // Ringkasan untuk LOST (pakai harga * pax)
        $ringkasanLost = Peluang::whereNotNull('lost')
            ->whereYear('lost', $tahunDipilih)
            ->select(
                'id_sales',
                DB::raw('CASE
                    WHEN MONTH(lost) BETWEEN 1 AND 3 THEN "TR1"
                    WHEN MONTH(lost) BETWEEN 4 AND 6 THEN "TR2"
                    WHEN MONTH(lost) BETWEEN 7 AND 9 THEN "TR3"
                    WHEN MONTH(lost) BETWEEN 10 AND 12 THEN "TR4"
                END as triwulan'),
                DB::raw('SUM(harga * pax) as total_jumlah')
            )
            ->groupBy('id_sales', 'triwulan')
            ->get()
            ->groupBy('id_sales')
            ->map(function ($grup) {
                return $grup->pluck('total_jumlah', 'triwulan')->toArray();
            })
            ->toArray();

        // Ambil data pengguna
        $pengguna = User::select('id_sales', 'username')->get()->keyBy('id_sales')->toArray();
        return view('crm.LaporanPenjualan.index', compact(
            'ringkasanMerah',
            'ringkasanLost',
            'pengguna',
            'tahunDipilih'
        ));
    }

    public function detailRingkasan($id)
    {
        $data = Peluang::where('id_sales', $id)->where('tahap', 'merah')->with('aktivitas', 'materiRelation')->with('perusahaan')->get();
        return view('crm.closedwin.detail', compact('data'));
    }
}
