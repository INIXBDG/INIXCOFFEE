<?php

namespace App\Http\Controllers;

use App\Models\AbsensiKaryawan;
use App\Models\LeadProject;
use App\Models\notif;
use App\Models\target;
use Illuminate\Http\JsonResponse;
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
        $startDate = $now->copy()->startOfWeek();
        $endDate = $now->copy()->endOfWeek();
        $sekarang = $now->toDateString();

        // Mengambil notifikasi yang berada di antara tanggal awal dan akhir minggu ini
        $notifikasi = notif::with('users')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal_awal', [$startDate, $endDate])
                    ->orWhereBetween('tanggal_akhir', [$startDate, $endDate]);
            })
            ->get();

        $user = auth()->user();
        $id_karyawan = $user->karyawan_id ?? $user->karyawan?->id;
        $absenHariIni = $id_karyawan
            ? AbsensiKaryawan::where('id_karyawan', $id_karyawan)
                ->where('tanggal', $sekarang)
                ->first()
            : null;

        return view('layouts.menus', compact('notifikasi', 'absenHariIni'));
    }

    public function dashboardPartial()
    {
        return view('partials.dashboard');
    }

    public function keepAlive(): JsonResponse
    {
        return response()->json(['status' => 'alive']);
    }

    private function getTotalSales($year)
    {
        $yearInt = (int) $year;
        $yearStart = sprintf('%04d-01-01', $yearInt);
        $yearEnd = sprintf('%04d-12-31', $yearInt);

        if (in_array((string) $year, ['2023', '2024', '2025'], true)) {
            return DB::table('r_k_m_s')
                ->where('status', '0')
                ->whereBetween('tanggal_awal', [$yearStart, $yearEnd])
                ->select(DB::raw('SUM(CAST(harga_jual AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total_sales'))
                ->value('total_sales');
        } else {
            return DB::table('peluangs')
                ->whereNotNull('merah')
                ->whereBetween('periode_mulai', [$yearStart, $yearEnd])
                ->select(DB::raw('
                    ROUND(
                        SUM(
                            CASE
                                WHEN final IS NULL THEN
                                    (
                                        CAST(harga AS DECIMAL(15,2))
                                        - COALESCE(CAST(netsales AS DECIMAL(15,2)), 0)
                                    ) * COALESCE(CAST(pax AS UNSIGNED), 0)
                                ELSE
                                    CAST(final AS DECIMAL(15,2))
                            END
                        ),
                        0
                    ) as total_sales
                '))
                ->value('total_sales');
        }
    }

    public function getYearSales($year)
    {
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
        $formatTarget = function ($value) {
            if ($value >= 1000000000) {
                // If the value is in billions, display in 'M' (for millions)
                return ($value / 1000000000).' M';
            } elseif ($value >= 100000000) {
                // If the value is in hundreds of millions, display in 'JT'
                return ($value / 1000000).' JT';
            } elseif ($value >= 10000000) {
                // If the value is in tens of millions, display in 'JT'
                return ($value / 1000000).' JT';
            } elseif ($value >= 1000000) {
                // If the value is in millions, display in 'JT'
                return ($value / 1000000).' JT';
            } else {
                // If the value is smaller than one million, use regular number format
                return number_format($value);
            }
        };

        // Divide the target into 8 sections, using the helper function to format each one
        $targetLabels = [];
        for ($i = 0; $i <= 8; ++$i) {
            $targetLabels[] = $formatTarget(($target / 8) * $i);
        }

        return response()->json([
            'target' => $target,
            'progress' => $progress,
            'targetLabels' => $targetLabels,
            'totalSales' => $totalSales,
        ]);
    }

    public function getProjectTarget($year)
    {
        try {
            // Ambil target_project dari tabel target berdasarkan tahun
            $targetRow = target::where('tahun', $year)->first();
            $targetProject = $targetRow ? (float) $targetRow->target_project : 0;

            $completedSum = LeadProject::where('status', 'won')
                ->where('tahun_periode', $year)
                ->sum('estimasi_nilai');

            // Generate labels untuk ruler (9 titik: 0%, 12.5%, 25%, ... 100%)
            $targetLabels = [];
            if ($targetProject > 0) {
                for ($i = 0; $i <= 8; ++$i) {
                    $targetLabels[] = $this->formatTargetLabel(($i / 8) * $targetProject);
                }
            } else {
                $targetLabels = array_fill(0, 9, '0');
            }

            return response()->json([
                'success' => true,
                'completedSum' => (float) $completedSum,
                'target' => (float) $targetProject,
                'targetLabels' => $targetLabels,
            ]);
        } catch (\Throwable $e) {
            \Log::error('getProjectTarget error', [
                'msg' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    // Helper function (jika belum ada)
    private function formatTargetLabel($value)
    {
        if ($value >= 1000000000) {
            return round($value / 1000000000, 1).' M';
        }
        if ($value >= 1000000) {
            return round($value / 1000000, 1).' JT';
        }

        return number_format($value, 0, ',', '.');
    }
}
