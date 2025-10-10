<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Aktivitas;
use App\Models\Contact;
use App\Models\Peluang;
use App\Models\Perusahaan;
use App\Models\RKM;
use App\Models\TargetActivity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class salesPribadiController extends Controller
{
    public function index()
    {

        $user = Auth::user();

        if ($user->jabatan === 'Sales') {

            $idSales = Auth::user()->id_sales;

            // 1. Kategori perusahaan chart
            $data = Perusahaan::where('sales_key', $idSales)->select('kategori_perusahaan', DB::raw('count(*) as total'))
                ->groupBy('kategori_perusahaan')
                ->get();

            $total = $data->sum('total') ?: 1; // Prevent division by zero

            $chartData = $data->map(function ($item) use ($total) {
                return [
                    'kategori' => $item->kategori_perusahaan ?? 'Tidak Ada Kategori',
                    'persen' => round(($item->total / $total) * 100, 2),
                    'jumlah' => $item->total,
                ];
            });

            // 2. Target dan aktivitas
            $target = TargetActivity::where('id_sales', $idSales)->first();

            $aktivitas = Aktivitas::where('id_sales', $idSales)
                ->whereMonth('waktu_aktivitas', Carbon::now()->month)
                ->whereYear('waktu_aktivitas', Carbon::now()->year)
                ->get();

            // hitung aktivitas
            $actualContact = $aktivitas->where('aktivitas', 'Contact')->count();
            $actualCall = $aktivitas->where('aktivitas', 'Call')->count();
            $actualEmail = $aktivitas->where('aktivitas', 'Email')->count();
            $actualVisit = $aktivitas->where('aktivitas', 'Visit')->count();
            $actualMeet = $aktivitas->where('aktivitas', 'Meet')->count();
            $actualIncharge = $aktivitas->where('aktivitas', 'Incharge')->count();
            $actualPA = $aktivitas->where('aktivitas', 'PA')->count();
            $actualPI = $aktivitas->where('aktivitas', 'PI')->count();
            $actualTelemarketing = $aktivitas->where('aktivitas', 'Telemarketing')->count();
            $actualForm_Masuk = $aktivitas->where('aktivitas', 'Form_Masuk')->count();
            $actualForm_Keluar = $aktivitas->where('aktivitas', 'Form_Keluar')->count();

            // hitung contact baru bulan ini
            $contactbaru = Contact::where('sales_key', $idSales)
                ->where('status', '1')
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count();

            $perusahaanBaru = Perusahaan::where('sales_key', $idSales)
                ->where('status', 'Database Baru')
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count();

            $activitysales = [
                'id_sales' => $idSales,
                'DB' => $perusahaanBaru,
                'contact' => $contactbaru,
                'call' => $actualCall,
                'email' => $actualEmail,
                'visit' => $actualVisit,
                'meet' => $actualMeet,
                'incharge' => $actualIncharge,
                'PA' => $actualPA,
                'PI' => $actualPI,
                'Telemarketing' => $actualTelemarketing,
                'Form_Masuk' => $actualForm_Masuk,
                'Form_Keluar' => $actualForm_Keluar,
                'target_DB' => $target->DB ?? 0,
                'target_contact' => $target->Contact ?? 0,
                'target_call' => $target->Call ?? 0,
                'target_email' => $target->Email ?? 0,
                'target_visit' => $target->Visit ?? 0,
                'target_meet' => $target->Meet ?? 0,
                'target_incharge' => $target->Incharge ?? 0,
                'target_PA' => $target->PA ?? 0,
                'target_PI' => $target->PI ?? 0,
                'target_Telemarketing' => $target->Telemarketing ?? 0,
                'target_Form_Masuk' => $target->FormM ?? 0,
                'target_Form_Keluar' => $target->FormK ?? 0,
            ];

            // 3. Top 5 produk paling banyak terjual
            $best = RKM::where('sales_key', $idSales)
                ->with('materi')
                ->select('materi_key', DB::raw('SUM(pax) as total_pax'))
                ->where('status', '0')
                ->groupBy('materi_key')
                ->orderByDesc('total_pax')
                ->limit(5)
                ->get();

            // 4. Top 5 produk paling menguntungkan
            $profit = RKM::where('sales_key', $idSales)
                ->with('materi')
                ->select('materi_key', DB::raw('SUM(COALESCE(harga_jual, 0) * COALESCE(pax, 0)) as total_revenue'))
                ->where('status', '0')
                ->groupBy('materi_key')
                ->orderByDesc('total_revenue')
                ->limit(5)
                ->get();


            // 5. Prospek terbuat minggu ini
            $prospek = Peluang::where('id_sales', $idSales)->with('materiRelation')->whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ])->get();


            // 6. Status Perusahaan per sales
            $totalStatus = Perusahaan::select('status', 'sales_key', DB::raw('count(*) as total'))
                ->where('sales_key', $idSales)
                ->groupBy('status', 'sales_key')
                ->get();



            // 7. Segmentasi Daerah untuk sales yang login
            $lokasi = Perusahaan::select('lokasi', DB::raw('count(*) as total'))
                ->where('sales_key', $idSales)
                ->whereNotNull('lokasi')
                ->groupBy('lokasi')
                ->get();

            // total perusahaan per sales (hanya login user)
            $totalSales = Perusahaan::where('sales_key', $idSales)->count();

            $totalDaerah = [];
            foreach ($lokasi as $row) {
                if (empty($row->lokasi)) {
                    continue;
                }
                $persen = $totalSales > 0 ? round(($row->total / $totalSales) * 100, 2) : 0;
                $totalDaerah[] = [
                    'lokasi' => $row->lokasi,
                    'total' => $row->total,
                    'persen' => $persen,
                ];
            }

            return view('crm.myDashboard', compact('chartData', 'activitysales', 'best', 'profit', 'prospek', 'totalStatus', 'totalDaerah'));
        } else {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }
    }
}
