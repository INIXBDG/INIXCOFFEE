<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Aktivitas;
use App\Models\Contact;
use App\Models\Feedback;
use App\Models\Materi;
use App\Models\Nilaifeedback;
use App\Models\Peluang;
use App\Models\Perusahaan;
use App\Models\Peserta;
use App\Models\RKM;
use App\Models\TargetActivity;
use App\Models\User;
use App\Models\vendor;
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
        $allowedUser = ['Adm Sales', 'SPV Sales', 'HRD', 'Finance & Accounting', 'GM', 'Sales', 'Direktur Utama', 'Direktur', 'Programmer'];

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

            $aktivitas = Aktivitas::with('contact.perusahaan', 'peserta')
                ->whereMonth('waktu_aktivitas', Carbon::now()->month)
                ->whereYear('waktu_aktivitas', Carbon::now()->year)
                ->get();

            $sales = User::where('jabatan', 'Sales')
                ->where('status_akun', '1')
                ->pluck('id_sales')
                ->toArray();

            $activitysales = [];

            foreach ($sales as $id_sales) {
                $userAktivitas = $aktivitas->where('id_sales', $id_sales);

                // Ambil data berdasarkan jenis aktivitas
                $contactData = $userAktivitas->where('aktivitas', 'Contact');
                $callData = $userAktivitas->where('aktivitas', 'Call');
                $emailData = $userAktivitas->where('aktivitas', 'Email');
                $visitData = $userAktivitas->where('aktivitas', 'Visit');
                $meetData = $userAktivitas->where('aktivitas', 'Meet');
                $inchargeData = $userAktivitas->where('aktivitas', 'Incharge');
                $paData = $userAktivitas->where('aktivitas', 'PA');
                $piData = $userAktivitas->where('aktivitas', 'PI');
                $teleData = $userAktivitas->where('aktivitas', 'Telemarketing');
                $formMasukData = $userAktivitas->where('aktivitas', 'Form_Masuk');
                $formKeluarData = $userAktivitas->where('aktivitas', 'Form_Keluar');
                $dbData = $userAktivitas->where('aktivitas', 'DB');

                $salesTarget = $target[$id_sales] ?? null;

                $activitysales[] = [
                    'id_sales' => $id_sales,

                    // 📊 Jumlah aktivitas
                    'contact' => $contactData->count(),
                    'call' => $callData->count(),
                    'email' => $emailData->count(),
                    'visit' => $visitData->count(),
                    'meet' => $meetData->count(),
                    'incharge' => $inchargeData->count(),
                    'PA' => $paData->count(),
                    'PI' => $piData->count(),
                    'Telemarketing' => $teleData->count(),
                    'Form_Masuk' => $formMasukData->count(),
                    'Form_Keluar' => $formKeluarData->count(),
                    'DB' => $dbData->count(),

                    // 💰 Total nilai
                    'total_PA' => $paData->sum('total'),
                    'total_Form_Masuk' => $formMasukData->sum('total'),
                    'total_Form_Keluar' => $formKeluarData->sum('total'),

                    // 🎯 Target
                    'target_contact' => $salesTarget->Contact ?? 0,
                    'target_call' => $salesTarget->Call ?? 0,
                    'target_email' => $salesTarget->Email ?? 0,
                    'target_visit' => $salesTarget->Visit ?? 0,
                    'target_meet' => $salesTarget->Meet ?? 0,
                    'target_incharge' => $salesTarget->Incharge ?? 0,
                    'target_PA' => $salesTarget->PA ?? 0,
                    'target_PI' => $salesTarget->PI ?? 0,
                    'target_Telemarketing' => $salesTarget->Telemarketing ?? 0,
                    'target_Form_Masuk' => $salesTarget->FormM ?? 0,
                    'target_Form_Keluar' => $salesTarget->FormK ?? 0,
                    'target_DB' => $salesTarget->DB ?? 0,

                    // 🗂️ Data aktivitas (detail)
                    'data_contact' => $contactData->values(),
                    'data_call' => $callData->values(),
                    'data_email' => $emailData->values(),
                    'data_visit' => $visitData->values(),
                    'data_meet' => $meetData->values(),
                    'data_incharge' => $inchargeData->values(),
                    'data_PA' => $paData->values(),
                    'data_PI' => $piData->values(),
                    'data_Telemarketing' => $teleData->values(),
                    'data_Form_Masuk' => $formMasukData->values(),
                    'data_Form_Keluar' => $formKeluarData->values(),
                    'data_DB' => $dbData->values(),
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
                    DB::raw('SUM(netsales * pax) as total_jumlah')
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

            // 12. Total Pesert Terdaftar
            $TotalPeserta = Peserta::all()->count();

            // 13. Rata" Feedback Peserta
            $feedbacks = Nilaifeedback::all([
                'M1',
                'M2',
                'M3',
                'M4',
                'P1',
                'P2',
                'P3',
                'P4',
                'P5',
                'P6',
                'P7',
                'F1',
                'F2',
                'F3',
                'F4',
                'F5',
                'I1',
                'I2',
                'I3',
                'I4',
                'I5',
                'I6',
                'I7',
                'I8',
                'I1b',
                'I2b',
                'I3b',
                'I4b',
                'I5b',
                'I6b',
                'I7b',
                'I8b',
                'I1as',
                'I2as',
                'I3as',
                'I4as',
                'I5as',
                'I6as',
                'I7as',
                'I8as',
            ]);

            // Kumpulkan semua nilai dari setiap baris & kolom
            $allValues = collect();

            foreach ($feedbacks as $feedback) {
                $values = [
                    $feedback->M1,
                    $feedback->M2,
                    $feedback->M3,
                    $feedback->M4,
                    $feedback->P1,
                    $feedback->P2,
                    $feedback->P3,
                    $feedback->P4,
                    $feedback->P5,
                    $feedback->P6,
                    $feedback->P7,
                    $feedback->F1,
                    $feedback->F2,
                    $feedback->F3,
                    $feedback->F4,
                    $feedback->F5,
                    $feedback->I1,
                    $feedback->I2,
                    $feedback->I3,
                    $feedback->I4,
                    $feedback->I5,
                    $feedback->I6,
                    $feedback->I7,
                    $feedback->I8,
                    $feedback->I1b,
                    $feedback->I2b,
                    $feedback->I3b,
                    $feedback->I4b,
                    $feedback->I5b,
                    $feedback->I6b,
                    $feedback->I7b,
                    $feedback->I8b,
                    $feedback->I1as,
                    $feedback->I2as,
                    $feedback->I3as,
                    $feedback->I4as,
                    $feedback->I5as,
                    $feedback->I6as,
                    $feedback->I7as,
                    $feedback->I8as,
                ];

                // Masukkan semua nilai ke collection utama
                $allValues = $allValues->merge($values);
            }

            // Filter hanya angka yang valid
            $numericValues = $allValues->filter(fn($v) => is_numeric($v));

            // Hitung rata-rata keseluruhan
            $AvgFeedback = $numericValues->avg();


            // 14. Jumlah Materi
            $TotalMateri = Materi::all()->count();

            // 15. Jumlah Vendor
            $TotalVendor = vendor::all()->count();

            // 16. Top 5 Vendor Terjual
            $topVendors = DB::table('r_k_m_s')
                ->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
                ->where('r_k_m_s.status', '0')
                ->select('materis.vendor', DB::raw('count(*) as total'))
                ->groupBy('materis.vendor')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get();

            // 17. Top 5 Kategori Materi Terjual
            $topKategoriMateri = DB::table('r_k_m_s')
                ->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
                ->where('r_k_m_s.status', '0')
                ->select('materis.kategori_materi', DB::raw('count(*) as total'))
                ->groupBy('materis.kategori_materi')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get();

            // 18. Top 5 Spend Perusahaan per Segmentasi
            $topSpendSeg = DB::table('r_k_m_s')
                ->join('perusahaans', 'r_k_m_s.perusahaan_key', '=', 'perusahaans.id')
                ->where('r_k_m_s.status', '0')
                ->select(
                    'perusahaans.kategori_perusahaan',
                    DB::raw('COUNT(*) as total'),
                    DB::raw('SUM(r_k_m_s.harga_jual) as spend')
                )
                ->groupBy('perusahaans.kategori_perusahaan')
                ->orderByDesc('total')
                ->limit(5)
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
