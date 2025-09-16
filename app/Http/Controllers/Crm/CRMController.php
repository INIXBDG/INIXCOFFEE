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
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CRMController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $allowedUser = ['Adm Sales', 'HRD', 'Finance & Accounting', 'GM', 'Sales', 'Direktur Utama', 'Direktur'];

        if (in_array($user->jabatan, $allowedUser)) {

            // 1. Kategori perusahaan chart
            $data = Perusahaan::select('kategori_perusahaan', DB::raw('count(*) as total'))
                ->groupBy('kategori_perusahaan')
                ->get();

            $total = $data->sum('total') ?: 1; // Prevent division by zero

            $chartData = $data->map(function ($item) use ($total) {
                return [
                    'kategori' => $item->kategori_perusahaan ?? 'Tidak Ada Kategori',
                    'persen' => round(($item->total / $total) * 100, 2),
                ];
            });

            // 2. Target dan aktivitas
            $target = TargetActivity::all()->keyBy('id_sales');

            $aktivitas = Aktivitas::whereMonth('waktu_aktivitas', Carbon::now()->month)
                ->whereYear('waktu_aktivitas', Carbon::now()->year)
                ->get();

            $sales = User::where('jabatan', 'Sales')->where('status_akun', '1')->pluck('id_sales')->toArray();
            $activitysales = [];

            foreach ($sales as $id_sales) {
                $userAktivitas = $aktivitas->where('id_sales', $id_sales);

                $actualContact = $userAktivitas->where('aktivitas', 'Contact')->count();
                $actualCall = $userAktivitas->where('aktivitas', 'Call')->count();
                $actualEmail = $userAktivitas->where('aktivitas', 'Email')->count();
                $actualVisit = $userAktivitas->where('aktivitas', 'Visit')->count();
                $actualMeet = $userAktivitas->where('aktivitas', 'Meet')->count();
                $actualIncharge = $userAktivitas->where('aktivitas', 'Incharge')->count();

                $salesTarget = $target[$id_sales] ?? null;

                $contactbaru = Contact::where('sales_key', $id_sales)
                    ->where('status', '1')
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->count();

                $activitysales[] = [
                    'id_sales' => $id_sales,
                    'contact' => $contactbaru,
                    'call' => $actualCall,
                    'email' => $actualEmail,
                    'visit' => $actualVisit,
                    'meet' => $actualMeet,
                    'incharge' => $actualIncharge,
                    'target_contact' => $salesTarget->Contact ?? 0,
                    'target_call' => $salesTarget->Call ?? 0,
                    'target_email' => $salesTarget->Email ?? 0,
                    'target_visit' => $salesTarget->Visit ?? 0,
                    'target_meet' => $salesTarget->Meet ?? 0,
                    'target_incharge' => $salesTarget->Incharge ?? 0,
                ];
            }


            // 3. Top 5 produk paling banyak terjual
            $best = RKM::with('materi')
                ->select('materi_key', DB::raw('SUM(pax) as total_pax'))
                ->where('status', '0')
                ->groupBy('materi_key')
                ->orderByDesc('total_pax')
                ->limit(5)
                ->get();

            // 4. Top 5 produk paling menguntungkan
            $profit = RKM::with('materi')
                ->select('materi_key', DB::raw('SUM(COALESCE(harga_jual, 0) * COALESCE(pax, 0)) as total_revenue'))
                ->where('status', '0')
                ->groupBy('materi_key')
                ->orderByDesc('total_revenue')
                ->limit(5)
                ->get();

            // 5. Total Win
            $tahunDipilih = $request->query('tahun', now()->year);

            $dataRingkasanWin = Peluang::whereNotNull('merah')
                ->whereYear('merah', $tahunDipilih)
                ->select(
                    'id_sales',
                    DB::raw('CASE
                WHEN MONTH(merah) BETWEEN 1 AND 3 THEN "TR1"
                WHEN MONTH(merah) BETWEEN 4 AND 6 THEN "TR2"
                WHEN MONTH(merah) BETWEEN 7 AND 9 THEN "TR3"
                WHEN MONTH(merah) BETWEEN 10 AND 12 THEN "TR4"
            END as triwulan'),
                    DB::raw('SUM(final) as total_jumlah')
                )
                ->groupBy('id_sales', 'triwulan')
                ->get()
                ->groupBy('id_sales')
                ->map(function ($grup) {
                    return $grup->pluck('total_jumlah', 'triwulan')->toArray();
                })->toArray();

            // 6. Total Lost
            $dataRingkasanLost = Peluang::whereNotNull('lost')
                ->whereYear('lost', $tahunDipilih)
                ->select(
                    'id_sales',
                    DB::raw('CASE
                WHEN MONTH(lost) BETWEEN 1 AND 3 THEN "TR1"
                WHEN MONTH(lost) BETWEEN 4 AND 6 THEN "TR2"
                WHEN MONTH(lost) BETWEEN 7 AND 9 THEN "TR3"
                WHEN MONTH(lost) BETWEEN 10 AND 12 THEN "TR4"
            END as triwulan'),
                    DB::raw('SUM(COALESCE(harga, 0) * COALESCE(pax, 0)) as total_jumlah')
                )
                ->groupBy('id_sales', 'triwulan')
                ->get()
                ->groupBy('id_sales')
                ->map(function ($grup) {
                    return $grup->pluck('total_jumlah', 'triwulan')->toArray();
                })->toArray();

            $pengguna = User::where('status_akun', '1')
                ->select('id_sales', 'username')
                ->get()
                ->values()
                ->toArray();

            // Ensure all sales users are included for both win and lost
            $triwulanList = ['TR1', 'TR2', 'TR3', 'TR4'];
            $totalWin = [];
            $totalLost = [];

            foreach ($sales as $id_sales) {
                $totalWin[$id_sales] = [
                    'username' => $pengguna[$id_sales]['username'] ?? $id_sales,
                    'TR1' => $dataRingkasanWin[$id_sales]['TR1'] ?? 0,
                    'TR2' => $dataRingkasanWin[$id_sales]['TR2'] ?? 0,
                    'TR3' => $dataRingkasanWin[$id_sales]['TR3'] ?? 0,
                    'TR4' => $dataRingkasanWin[$id_sales]['TR4'] ?? 0,
                ];
                $totalLost[$id_sales] = [
                    'username' => $pengguna[$id_sales]['username'] ?? $id_sales,
                    'TR1' => $dataRingkasanLost[$id_sales]['TR1'] ?? 0,
                    'TR2' => $dataRingkasanLost[$id_sales]['TR2'] ?? 0,
                    'TR3' => $dataRingkasanLost[$id_sales]['TR3'] ?? 0,
                    'TR4' => $dataRingkasanLost[$id_sales]['TR4'] ?? 0,
                ];
            }

            // 7. Status Perusahaan per sales
            $totalStatus = Perusahaan::select('status', 'sales_key', DB::raw('count(*) as total'))->groupBy('status', 'sales_key')->get();

            // 8. Segmentasi Daerah per sales
            $lokasi = Perusahaan::select('sales_key', 'lokasi', DB::raw('count(*) as total'))
                ->whereNotNull('sales_key')
                ->whereNotNull('lokasi')
                ->groupBy('sales_key', 'lokasi')
                ->get();

            // Fetch unique sales_key values (no totaling)
            $salesKeys = Perusahaan::select('sales_key')
                ->whereNotNull('sales_key')
                ->distinct()
                ->pluck('sales_key');

            $salesTotals = Perusahaan::select('sales_key', DB::raw('count(*) as total'))
                ->whereNotNull('sales_key')
                ->groupBy('sales_key')
                ->pluck('total', 'sales_key')
                ->toArray();

            $totalDaerah = [];
            foreach ($lokasi as $row) {
                if (empty($row->sales_key) || empty($row->lokasi)) {
                    continue;
                }
                $totalSales = $salesTotals[$row->sales_key] ?? 0;
                $persen = $totalSales > 0 ? round(($row->total / $totalSales) * 100, 2) : 0;
                $totalDaerah[$row->sales_key][] = [
                    'lokasi' => $row->lokasi,
                    'total' => $row->total,
                    'persen' => $persen,
                ];
            }

            // Pass sales_keys as a Collection
            $sales = $salesKeys;

            // 10. Prospek terbuat minggu ini
            $prospek = Peluang::with('materiRelation')->whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ])->get();

            // 11. Map Perusahaan
            $map = DB::table('lokasis')
                ->leftJoin('perusahaans', 'lokasis.lokasi', '=', 'perusahaans.lokasi')
                ->select(
                    'lokasis.lokasi',
                    'lokasis.latitude',
                    'lokasis.longitude',
                    DB::raw('COUNT(perusahaans.id) as company_count')
                )
                ->groupBy('lokasis.id', 'lokasis.lokasi', 'lokasis.latitude', 'lokasis.longitude')
                ->get();

            return view('crm.dashboard', compact(
                'chartData',
                'activitysales',
                'best',
                'profit',
                'totalWin',
                'totalLost',
                'tahunDipilih',
                'totalStatus',
                'totalDaerah',
                'sales',
                'prospek',
                'map',
            ));
            
        } else {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }
    }


    public function getProfile()
    {
        $user = auth()->user();
        // Pastikan relasi karyawan sudah didefinisikan di model User
        $profile = $user->load('karyawan');

        // Bisa return data sebagai JSON jika untuk API, atau return view jika untuk halaman
        return response()->json([
            'id' => $user->id,
            'username' => $user->name, // contoh field
            'role' => $profile->jabatan, // contoh field
            'nama_lengkap' => $profile->karyawan->nama_lengkap ?? null,
            'jabatan' => $profile->karyawan->jabatan ?? null,
            'foto' => $profile->karyawan->foto ? asset('storage/posts/' . $profile->karyawan->foto) : null,
            'ttd' => $profile->karyawan->ttd ? asset('storage/ttd/' . $profile->karyawan->ttd) : null,
        ]);
    }
}
