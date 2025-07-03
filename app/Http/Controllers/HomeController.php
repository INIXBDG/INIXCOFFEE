<?php

namespace App\Http\Controllers;

use App\Models\AbsensiKaryawan;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\RKM;
use App\Models\Registrasi;
use App\Models\notif;
use App\Models\target;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
            $now = Carbon::now();
            $startOfWeek = $now->startOfWeek()->toDateString(); // Mengambil tanggal awal minggu ini
            $endOfWeek = $now->endOfWeek()->toDateString();
            $idInstruktur = auth()->user()->id_instruktur;
            $totalkaryawan = User::count();
            $kelasmingguini = RKM::where('instruktur_key', $idInstruktur)
            ->whereBetween('tanggal_awal', [$startOfWeek, $endOfWeek])
            ->count();
            $jumlahmengajar1 = RKM::where('instruktur_key', $idInstruktur)->count();
            $jumlahmengajar2 = RKM::where('instruktur_key2', $idInstruktur)->count();
            $jumlahmengajar = $jumlahmengajar1 + $jumlahmengajar2;
            $pesertaanda = Registrasi::with('rkm', 'peserta.perusahaan', 'materi')
            ->whereHas('rkm', function ($query) use ($idInstruktur) {
                $query->where('instruktur_key', $idInstruktur);
            })
            ->count();
            $kelasmingguini1 = RKM::where('instruktur_key', $idInstruktur)->whereBetween('tanggal_awal', [$startOfWeek, $endOfWeek])
            ->count();
            $kelasmingguini2 = RKM::where('instruktur_key', $idInstruktur)->whereBetween('tanggal_awal', [$startOfWeek, $endOfWeek])
            ->count();
            $kelasmingguini = $kelasmingguini1 + $kelasmingguini2;
            $runningclass = RKM::whereBetween('tanggal_awal', [$startOfWeek, $endOfWeek])
            ->count();
            $karyawanaktif = User::where('status_akun', '1')->count();
            $pesertaaktif = Registrasi::all()->count();

           // Mengambil tanggal awal dan akhir minggu ini
            $startDate = Carbon::now()->startOfWeek();
            $endDate = Carbon::now()->endOfWeek();
            $sekarang = Carbon::now()->toDateString();
            // Mengambil notifikasi yang berada di antara tanggal awal dan akhir minggu ini
            $notifikasi = Notif::with('users')
                ->where(function($query) use ($startDate, $endDate) {
                    $query->whereBetween('tanggal_awal', [$startDate, $endDate])
                        ->orWhereBetween('tanggal_akhir', [$startDate, $endDate]);
                })
            ->get();
            $id_karyawan = auth()->user()->karyawan->id;
            $absenHariIni = AbsensiKaryawan::where('id_karyawan', $id_karyawan)
                            ->where('tanggal', $sekarang)
                            ->first();
            // return $absenHariIni;
            return view('layouts.menus', compact('notifikasi', 'absenHariIni'));

    }
    private function getTotalSales($year)
    {
        return DB::table('r_k_m_s')
            ->where('status', '0') // Hanya data dengan status 0
            ->whereYear('tanggal_awal', $year) // Tambahkan kondisi berdasarkan tahun
            ->select(DB::raw('SUM(CAST(harga_jual AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total_sales'))
            ->value('total_sales');
    }    

    public function getYearSales($year) {
        $tahun = $year;
        $targetdatabase = target::where('quartal', 'All')->where('tahun', $tahun)->first();
        $target = $targetdatabase ? (int) $targetdatabase->target : 0;

        $totalSales = $this->getTotalSales($tahun); // Ambil total sales berdasarkan tahun

        // Check if $target exists and has a non-zero target value
        if ($target) {
            $progress = ($totalSales / $target) * 100;
        } else {
            // Handle the case where there's no target or the target is zero
            $progress = 0; // Or set it to null, or any other default value
            $target = 0; // Or set it to null, or any other default value
            $totalSales = 0; // Or set it to null, or any other default value
        }

        // Now $progress is safe to use

        // $totalSales = 10000000000; // Fetch total sales from the database
        $formatTarget = function($value) {
            if ($value >= 1000000000) {
                // If the value is in billions, display in 'M' (for millions)
                return ($value / 1000000000) . ' M';
            } elseif ($value >= 100000000) {
                // If the value is in hundreds of millions, display in 'JT'
                return ($value / 1000000) . ' JT';
            } elseif ($value >= 10000000) {
                // If the value is in tens of millions, display in 'JT'
                return ($value / 1000000) . ' JT';
            } elseif ($value >= 1000000) {
                // If the value is in millions, display in 'JT'
                return ($value / 1000000) . ' JT';
            } else {
                // If the value is smaller than one million, use regular number format
                return number_format($value);
            }
        };
        
        // Divide the target into 8 sections, using the helper function to format each one
        $targetLabels = [];
        for ($i = 0; $i <= 8; $i++) {
            $targetLabels[] = $formatTarget(($target / 8) * $i);
        }
        return response()->json([
            'target' => $target,
            'progress' => $progress,
            'targetLabels' => $targetLabels,
            'totalSales' => $totalSales,
        ]);
    }



}
