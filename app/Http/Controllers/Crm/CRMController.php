<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Aktivitas;
use App\Models\Peluang;
use App\Models\Perusahaan;
use App\Models\RKM;
use App\Models\TargetActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CRMController extends Controller
{
    public function index()
    {
        // 1. Kategori perusahaan chart
        $data = Perusahaan::select('kategori_perusahaan', DB::raw('count(*) as total'))
            ->groupBy('kategori_perusahaan')
            ->get();

        $total = $data->sum('total');

        $chartData = $data->map(function ($item) use ($total) {
            return [
                'kategori' => $item->kategori_perusahaan ?? 'Tidak Ada Kategori',
                'persen' => round(($item->total / $total) * 100, 2),
            ];
        });

        // 2. Target dan aktivitas
        $target = TargetActivity::all()->keyBy('id_sales');
        $aktivitas = Aktivitas::all();
        $sales = User::where('jabatan', 'Sales')->pluck('id_sales')->toArray();

        $activitysales = [];

        foreach ($sales as $id_sales) {
            $userAktivitas = $aktivitas->where('id_sales', $id_sales);

            $actualCall = $userAktivitas->where('aktivitas', 'Call')->count();
            $actualEmail = $userAktivitas->where('aktivitas', 'Email')->count();
            $actualVisit = $userAktivitas->where('aktivitas', 'Visit')->count();

            $salesTarget = $target[$id_sales] ?? null;

            $activitysales[] = [
                'id_sales' => $id_sales,
                'call' => $actualCall,
                'email' => $actualEmail,
                'visit' => $actualVisit,
                'target_call' => $salesTarget->Call ?? 0,
                'target_email' => $salesTarget->Email ?? 0,
                'target_visit' => $salesTarget->Visit ?? 0,

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
            ->select('materi_key', DB::raw('SUM(harga_jual * pax) as total_revenue'))
            ->where('status', '0')
            ->groupBy('materi_key')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        return view('crm.dashboard', compact(
            'chartData',
            'activitysales',
            'best',
            'profit'
        ));
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
