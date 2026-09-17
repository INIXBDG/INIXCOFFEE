<?php

namespace App\Http\Controllers\office;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;


// Import semua model yang dibutuhkan
use App\Models\souvenir;
use App\Models\souvenirpeserta;
use App\Models\PengajuanSouvenir;
use App\Models\DetailPengajuanSouvenir; // Asumsi model ini ada berdasarkan relasi detail()
use App\Models\PenambahanSouvenir;
use App\Models\PenukaranSouvenir;

class DashboardSouvenirController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        // $this->middleware('permission:View DashboardSouvenir', ['only' => ['index']]);
    }

    public function index()
    {
        $tahunSekarang = date('Y');
        $cacheKey = "office_dashboard_souvenir_{$tahunSekarang}";

        $dashboardData = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($tahunSekarang) {
            $topPeserta = souvenirpeserta::select('id_souvenir', DB::raw('count(*) as total_pilih'))
                ->with('souvenir')
                ->groupBy('id_souvenir')
                ->orderByDesc('total_pilih')
                ->limit(5)
                ->get();

            $topOffice = DetailPengajuanSouvenir::select('id_souvenir', DB::raw('sum(pax) as total_beli'))
                ->with('souvenir')
                ->groupBy('id_souvenir')
                ->orderByDesc('total_beli')
                ->limit(5)
                ->get();

            $stockSouvenir = souvenir::select('id', 'nama_souvenir', 'stok')
                ->orderBy('stok', 'asc')
                ->get();

            $topPenambahan = PenambahanSouvenir::select('id_souvenir', DB::raw('sum(qty) as total_keluar'))
                ->with('souvenir')
                ->groupBy('id_souvenir')
                ->orderByDesc('total_keluar')
                ->limit(5)
                ->get();

            $totalPenukaran = PenukaranSouvenir::count();

            $chartPenukaran = PenukaranSouvenir::select(
                    DB::raw('MONTH(tanggal_tukar) as bulan'),
                    DB::raw('count(*) as total')
                )
                ->whereYear('tanggal_tukar', $tahunSekarang)
                ->groupBy('bulan')
                ->get();

            $analisaSelisih = souvenir::select('id', 'nama_souvenir')
                ->get()
                ->map(function($item) use ($tahunSekarang) {
                    $totalBeli = DetailPengajuanSouvenir::where('id_souvenir', $item->id)
                        ->whereHas('pengajuan', function($q) use ($tahunSekarang) {
                            $q->whereYear('created_at', $tahunSekarang);
                        })
                        ->sum('pax');

                    $totalPakai = souvenirpeserta::where('id_souvenir', $item->id)
                        ->whereHas('rkm', function($q) use ($tahunSekarang) {
                            $q->whereYear('tanggal_awal', $tahunSekarang);
                        })
                        ->count();

                    $selisih = $totalBeli - $totalPakai;

                    $item->total_masuk = $totalBeli;
                    $item->total_keluar = $totalPakai;
                    $item->selisih_flow = $selisih;

                    return $item;
                })
                ->filter(function($item) {
                    return $item->total_masuk > 0 || $item->total_keluar > 0;
                })
                ->sortBy('selisih_flow');

            return compact(
                'topPeserta',
                'topOffice',
                'stockSouvenir',
                'topPenambahan',
                'totalPenukaran',
                'chartPenukaran',
                'analisaSelisih'
            );
        });

        return view('office.dashboardsouveir.dashboard', $dashboardData);
    }



}
