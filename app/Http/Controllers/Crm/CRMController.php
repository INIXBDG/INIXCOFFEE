<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Aktivitas;
use App\Models\checklistRKM;
use App\Models\Contact;
use App\Models\Feedback;
use App\Models\karyawan;
use App\Models\Materi;
use App\Models\Nilaifeedback;
use App\Models\Peluang;
use App\Models\PerbaikanKendaraan;
use App\Models\perhitunganNetSales;
use App\Models\Perusahaan;
use App\Models\Peserta;
use App\Models\pickupDriver;
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

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:Dashboard CRM', ['only' => ['index']]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $allowedUser = ['Adm Sales', 'SPV Sales', 'HRD', 'Finance & Accounting', 'GM', 'Sales', 'Direktur Utama', 'Direktur', 'Programmer'];

        if (!in_array($user->jabatan, $allowedUser)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $today = Carbon::now()->locale('id');
        $today->settings(['formatFunction' => 'translatedFormat']);

        $tanggal = $today->translatedFormat('d F Y');
        $firstDayOfMonth = $today->copy()->startOfMonth();
        $mingguKeBulan = ceil(($today->day + $firstDayOfMonth->dayOfWeek) / 7);

        // 1. Kategori perusahaan chart
        $data = Perusahaan::select('kategori_perusahaan', DB::raw('count(*) as total'))->groupBy('kategori_perusahaan')->get();
        $total = $data->sum('total') ?: 1;

        $chartData = $data->map(function ($item) use ($total) {
            return [
                'kategori' => $item->kategori_perusahaan ?? 'Tidak Ada Kategori',
                'persen' => round(($item->total / $total) * 100, 2),
            ];
        });

        // 2. Filter Tanggal & Waktu Aktivitas
        $tahun = $request->input('tahun', Carbon::now()->year);
        $bulan = $request->input('bulan', Carbon::now()->month);
        $mingguKe = $request->input('minggu', null);
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate && $endDate) {
            $tanggalRange = \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') . ' - ' . \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y');
            $bulanTahun = \Carbon\Carbon::parse($startDate)->translatedFormat('F Y');
        } else {
            $monthStart = Carbon::create($tahun, $bulan, 1)->startOfMonth();
            $monthEnd = (clone $monthStart)->endOfMonth();

            if ($mingguKe) {
                $startOfWeek = (clone $monthStart)->addWeeks($mingguKe - 1)->startOfWeek(Carbon::MONDAY);
                $endOfWeek = (clone $startOfWeek)->endOfWeek(Carbon::SUNDAY);

                if ($startOfWeek->lt($monthStart)) $startOfWeek = $monthStart;
                if ($endOfWeek->gt($monthEnd)) $endOfWeek = $monthEnd;
            } else {
                $startOfWeek = $monthStart;
                $endOfWeek = $monthEnd;
            }
            $tanggalRange = $startOfWeek->translatedFormat('d') . ' – ' . $endOfWeek->translatedFormat('d F Y');
            $bulanTahun = $startOfWeek->translatedFormat('F Y');
        }

        // 3. Ambil Target Sales Spesifik Kolom
        $target = TargetActivity::select('id_sales', 'Contact', 'Call', 'Email', 'Visit', 'Meet', 'Incharge', 'PA', 'PI', 'FormM', 'DB')
            ->get()->keyBy('id_sales');

        // 4. Inisialisasi Eager Loading untuk Aktivitas
        $aktivitasQuery = Aktivitas::with([
            'contact.perusahaan',
            'peserta',
            'perusahaanLangsung'
        ]);

        if ($startDate && $endDate) {
            $aktivitasQuery->whereBetween('waktu_aktivitas', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59'
            ]);
        } else {
            $aktivitasQuery->whereBetween('waktu_aktivitas', [$startOfWeek, $endOfWeek]);
        }

        $aktivitas = $aktivitasQuery->get();

        // 5. Hitung Aktivitas Per Sales
        $salesList = User::where('jabatan', 'Sales')->where('status_akun', '1')->pluck('id_sales')->toArray();
        $activitysales = [];

        foreach ($salesList as $id_sales) {
            $userAktivitas = $aktivitas->where('id_sales', $id_sales);

            $contactData = $userAktivitas->where('aktivitas', 'Contact');
            $callData = $userAktivitas->where('aktivitas', 'Call');
            $emailData = $userAktivitas->where('aktivitas', 'Email');
            $visitData = $userAktivitas->where('aktivitas', 'Visit');
            $meetData = $userAktivitas->where('aktivitas', 'Meet');
            $inchargeData = $userAktivitas->where('aktivitas', 'Incharge');
            $paData = $userAktivitas->where('aktivitas', 'PA');
            $piData = $userAktivitas->whereIn('aktivitas', ['PI', 'Leads']);
            $formMasukData = $userAktivitas->whereIn('aktivitas', ['Form_Masuk', 'Regis Form']);
            $dbData = $userAktivitas->where('aktivitas', 'DB');

            $salesTarget = $target[$id_sales] ?? null;

            $activitysales[] = [
                'id_sales' => $id_sales,
                'contact'    => $contactData->count(),
                'call'       => $callData->count(),
                'email'      => $emailData->count(),
                'visit'      => $visitData->count(),
                'meet'       => $meetData->count(),
                'incharge'   => $inchargeData->count(),
                'PA'         => $paData->count(),
                'Leads'      => $piData->count(),
                'Regis_Form' => $formMasukData->count(),
                'DB'         => $dbData->count(),
                'total_PA'         => $paData->sum('total'),
                'total_Regis_Form' => $formMasukData->sum('total'),
                'target_contact'    => $salesTarget->Contact ?? 0,
                'target_call'       => $salesTarget->Call ?? 0,
                'target_email'      => $salesTarget->Email ?? 0,
                'target_visit'      => $salesTarget->Visit ?? 0,
                'target_meet'       => $salesTarget->Meet ?? 0,
                'target_incharge'   => $salesTarget->Incharge ?? 0,
                'target_PA'         => $salesTarget->PA ?? 0,
                'target_PI'         => $salesTarget->PI ?? 0,
                'target_Form_Masuk' => $salesTarget->FormM ?? 0,
                'target_DB'         => $salesTarget->DB ?? 0,
                'data_contact'    => $contactData->values(),
                'data_call'       => $callData->values(),
                'data_email'      => $emailData->values(),
                'data_visit'      => $visitData->values(),
                'data_meet'       => $meetData->values(),
                'data_incharge'   => $inchargeData->values(),
                'data_PA'         => $paData->values(),
                'data_Leads'      => $piData->values(),
                'data_Regis_Form' => $formMasukData->values(),
                'data_DB'         => $dbData->values(),
            ];
        }

        // 6. Top 5 Produk Chart Terjual & Menguntungkan (Optimasi Select Eager Load)
        $best = RKM::with('materi:id,nama_materi')
            ->select('materi_key', DB::raw('SUM(pax) as total_pax'))
            ->where('status', '0')
            ->groupBy('materi_key')
            ->orderByDesc('total_pax')->limit(5)->get();

        $profit = RKM::with('materi:id,nama_materi')
            ->select('materi_key', DB::raw('SUM(COALESCE(harga_jual, 0) * COALESCE(pax, 0)) as total_revenue'))
            ->where('status', '0')
            ->groupBy('materi_key')
            ->orderByDesc('total_revenue')->limit(5)->get();

        // 7. Total Win & Lost
        $tahunDipilih = $request->query('tahun', now()->year);

        $dataRingkasanWin = Peluang::whereNotNull('merah')
            ->whereYear('merah', $tahunDipilih)
            ->select(
                'id_sales',
                DB::raw('CASE WHEN MONTH(merah) BETWEEN 1 AND 3 THEN "TR1" WHEN MONTH(merah) BETWEEN 4 AND 6 THEN "TR2" WHEN MONTH(merah) BETWEEN 7 AND 9 THEN "TR3" WHEN MONTH(merah) BETWEEN 10 AND 12 THEN "TR4" END as triwulan'),
                DB::raw('SUM(netsales * pax) as total_jumlah')
            )
            ->groupBy('id_sales', 'triwulan')
            ->get()
            ->groupBy('id_sales')
            ->map(fn($grup) => $grup->pluck('total_jumlah', 'triwulan')->toArray())
            ->toArray();

        $dataRingkasanLost = Peluang::whereNotNull('lost')
            ->whereYear('lost', $tahunDipilih)
            ->select(
                'id_sales',
                DB::raw('CASE WHEN MONTH(lost) BETWEEN 1 AND 3 THEN "TR1" WHEN MONTH(lost) BETWEEN 4 AND 6 THEN "TR2" WHEN MONTH(lost) BETWEEN 7 AND 9 THEN "TR3" WHEN MONTH(lost) BETWEEN 10 AND 12 THEN "TR4" END as triwulan'),
                DB::raw('SUM(COALESCE(harga, 0) * COALESCE(pax, 0)) as total_jumlah')
            )
            ->groupBy('id_sales', 'triwulan')
            ->get()
            ->groupBy('id_sales')
            ->map(fn($grup) => $grup->pluck('total_jumlah', 'triwulan')->toArray())
            ->toArray();

        $pengguna = User::where('status_akun', '1')->select('id_sales', 'username')->get()->keyBy('id_sales');

        $totalWin = [];
        $totalLost = [];
        foreach ($salesList as $id_sales) {
            $username = $pengguna[$id_sales]->username ?? $id_sales;
            $totalWin[$id_sales] = [
                'username' => $username,
                'TR1' => $dataRingkasanWin[$id_sales]['TR1'] ?? 0,
                'TR2' => $dataRingkasanWin[$id_sales]['TR2'] ?? 0,
                'TR3' => $dataRingkasanWin[$id_sales]['TR3'] ?? 0,
                'TR4' => $dataRingkasanWin[$id_sales]['TR4'] ?? 0,
            ];
            $totalLost[$id_sales] = [
                'username' => $username,
                'TR1' => $dataRingkasanLost[$id_sales]['TR1'] ?? 0,
                'TR2' => $dataRingkasanLost[$id_sales]['TR2'] ?? 0,
                'TR3' => $dataRingkasanLost[$id_sales]['TR3'] ?? 0,
                'TR4' => $dataRingkasanLost[$id_sales]['TR4'] ?? 0,
            ];
        }

        // 8. Status Perusahaan & Segmentasi Daerah per Sales
        $totalStatus = Perusahaan::select('status', 'sales_key', DB::raw('count(*) as total'))
            ->groupBy('status', 'sales_key')->get();

        $lokasi = Perusahaan::select('sales_key', 'lokasi', DB::raw('count(*) as total'))
            ->whereNotNull('sales_key')->whereNotNull('lokasi')
            ->groupBy('sales_key', 'lokasi')->get();

        $salesKeys = Perusahaan::select('sales_key')->whereNotNull('sales_key')->distinct()->pluck('sales_key');
        $salesTotals = Perusahaan::select('sales_key', DB::raw('count(*) as total'))->whereNotNull('sales_key')->groupBy('sales_key')->pluck('total', 'sales_key')->toArray();

        $totalDaerah = [];
        foreach ($lokasi as $row) {
            $totalSales = $salesTotals[$row->sales_key] ?? 0;
            $persen = $totalSales > 0 ? round(($row->total / $totalSales) * 100, 2) : 0;
            $totalDaerah[$row->sales_key][] = [
                'lokasi' => $row->lokasi,
                'total' => $row->total,
                'persen' => $persen,
            ];
        }

        $sales = $salesKeys;

        // 9. Prospek terbuat minggu ini
        $prospek = Peluang::with('materiRelation')
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->get();

        // 10. Map Perusahaan
        $map = DB::table('lokasis')
            ->leftJoin('perusahaans', 'lokasis.lokasi', '=', 'perusahaans.lokasi')
            ->select('lokasis.lokasi', 'lokasis.latitude', 'lokasis.longitude', DB::raw('COUNT(perusahaans.id) as company_count'))
            ->groupBy('lokasis.id', 'lokasis.lokasi', 'lokasis.latitude', 'lokasis.longitude')->get();

        // 11. Top Vendors & Kategori Materi Terjual & Segmen Spend
        $topVendors = DB::table('r_k_m_s')->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
            ->where('r_k_m_s.status', '0')->select('materis.vendor', DB::raw('count(*) as total'))
            ->groupBy('materis.vendor')->orderByDesc('total')->get();

        $topKategoriMateri = DB::table('r_k_m_s')->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
            ->where('r_k_m_s.status', '0')->select('materis.kategori_materi', DB::raw('count(*) as total'))
            ->groupBy('materis.kategori_materi')->orderByDesc('total')->get();

        $topSpendSeg = DB::table('r_k_m_s')->join('perusahaans', 'r_k_m_s.perusahaan_key', '=', 'perusahaans.id')
            ->where('r_k_m_s.status', '0')->select('perusahaans.kategori_perusahaan', DB::raw('COUNT(*) as total'), DB::raw('SUM(r_k_m_s.harga_jual) as spend'))
            ->groupBy('perusahaans.kategori_perusahaan')->orderByDesc('total')->get();

        // 12. PA yg belum di approve
        $PA = perhitunganNetSales::with(['rkm.materi', 'rkm.perusahaan', 'trackingNetSales', 'rkm.peluang'])
            ->whereHas('trackingNetSales', function ($query) {
                $query->where('tracking', '!=', 'Selesai')->orWhereNull('tracking');
            })->paginate(10);

        // 13. Data Checklist Milik Adm Sales
        $query = RKM::with(['checklist', 'materi', 'perusahaan', 'instruktur', 'sales']);

        if ($request->search) {
            $query->whereHas('materi', function ($q) use ($request) {
                $q->where('nama_materi', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->bulan) $query->whereMonth('created_at', $request->bulan);
        if ($request->tahun) $query->whereYear('created_at', $request->tahun);
        if ($request->minggu) $query->whereRaw('CEIL(DAY(created_at)/7) = ?', [$request->minggu]);

        $dataRKM = $query->paginate(10);

        return view('crm.dashboard', compact(
            'chartData', 'activitysales', 'best', 'profit', 'totalWin', 'totalLost',
            'tahunDipilih', 'totalStatus', 'totalDaerah', 'sales', 'prospek', 'map',
            'tanggal', 'mingguKeBulan', 'tahun', 'bulan', 'mingguKe', 'bulanTahun',
            'tanggalRange', 'topSpendSeg', 'topKategoriMateri', 'topVendors', 'PA', 'dataRKM'
        ));
    }
    public function updateChecklist(Request $request)
    {
        $checklist = checklistRKM::where('id_rkm', $request->rkm_id)->first();

        if (!$checklist) {
            $checklist = checklistRKM::create([
                'id_rkm' => $request->rkm_id,
                'registrasi_form' => 0,
                'surat_kontrak' => 0,
                'PA' => 0,
                'PO' => 0,
            ]);
        }

        $checklist->update([
            $request->field => (bool) $request->value,
        ]);

        return response()->json([
            'success' => true,
            'updated_field' => $request->field,
            'value' => (bool) $request->value,
        ]);
    }

    public function chartRKM(Request $request)
    {
        $key = $request->input('key');
        $type = $request->input('type');

        $query = DB::table('r_k_m_s')->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')->join('perusahaans', 'r_k_m_s.perusahaan_key', '=', 'perusahaans.id')->where('r_k_m_s.status', '0');

        if ($type === 'vendor') {
            $query->where('materis.vendor', $key);
        } elseif ($type === 'materi') {
            $query->where('materis.kategori_materi', $key);
        } elseif ($type === 'spend') {
            $query->where('perusahaans.kategori_perusahaan', $key);
        }

        $data = $query->select('materis.nama_materi', 'perusahaans.nama_perusahaan', 'r_k_m_s.sales_key', 'r_k_m_s.harga_jual', 'r_k_m_s.created_at')->get();

        return response()->json($data);
    }

    public function chartPerusahaan(Request $request)
    {
        $key = $request->input('key');

        $query = DB::table('perusahaans')->where('kategori_perusahaan', $key);

        $data = $query->select('nama_perusahaan', 'sales_key', 'status')->get();

        return response()->json($data);
    }

    public function chartClosed(Request $request)
    {
        $id_sales = $request->id_sales;
        $triwulan = $request->triwulan;
        $tahun = $request->tahun ?? now()->year;
        $status = $request->status ?? 'win';

        $dateColumn = $status === 'lost' ? 'lost' : 'merah';

        $query = Peluang::with('materiRelation', 'perusahaan')->where('id_sales', $id_sales)->whereNotNull($dateColumn)->whereYear($dateColumn, $tahun);
        $range = [
            'TR1' => [1, 3],
            'TR2' => [4, 6],
            'TR3' => [7, 9],
            'TR4' => [10, 12],
        ];

        if (isset($range[$triwulan])) {
            $query->whereBetween(DB::raw("MONTH($dateColumn)"), $range[$triwulan]);
        }

        $data = $query->select('materi', 'id_contact', 'netsales', 'pax', DB::raw('(netsales * pax) as total'), 'merah')->get();

        return response()->json($data);
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

    public function indexKoordinasi()
    {
        $latestPerKendaraan = PerbaikanKendaraan::select('kendaraan')->selectRaw('MAX(id) as max_id')->groupBy('kendaraan');

        $kendaraan = PerbaikanKendaraan::joinSub($latestPerKendaraan, 'latest', function ($join) {
            $join->on('perbaikan_kendaraans.id', '=', 'latest.max_id');
        })
            ->where(function ($query) {
                $query->where('type_condition', '!=', 'Kecelakaan')->orWhere('status', 'Selesai');
            })
            ->where(function ($query) {
                $query->where('type_vehicle_condition', '!=', ['Kerusakan Berat', 'Kerusakan Total'])->orWhere('status', 'Selesai');
            })
            ->pluck('perbaikan_kendaraans.kendaraan');

        if ($kendaraan->isEmpty()) {
            $kendaraan = collect(['H1', 'Innova']);
        }

        if ($kendaraan->contains('Innova')) {
            $kendaraan = $kendaraan->map(function ($item) {
                return $item === 'Innova' ? 'Inova' : $item;
            });
        }

        $dataDriver = karyawan::where('jabatan', 'Driver')->get();

        $extends = 'layouts_crm.app';
        $section = 'crm_contents';

        return view('office.pickupdriver.index', compact('dataDriver', 'kendaraan', 'extends', 'section'));
    }

    public function createKoordinasi()
    {
         $dataDriver = karyawan::where('jabatan', 'Driver')
            ->where('status_aktif', '1')
            ->where(function ($query) {
                $query->whereDoesntHave('pickupDriver')
                    ->orWhereHas('pickupDriver', function ($q) {
                        $q->where('status_driver', 'Selesai, Driver Ready');
                    });
            })
            ->get();

        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $budgetPerjalanan = pickupDriver::select('kendaraan')
            ->selectRaw('COALESCE(SUM(pickup_drivers.budget), 0) as total_budget')
            ->where('tipe_perjalanan', 'Operasional Kantor')
            ->whereHas('detailPickupDriver', function ($q) use ($startOfWeek, $endOfWeek) {
                $q->whereBetween('tanggal_keberangkatan', [$startOfWeek, $endOfWeek]);
            })
            ->groupBy('kendaraan')
            ->get()
            ->map(function ($item) {
                $item->sisa_budget = 1000000 - $item->total_budget;
                return $item;
            });

        $kendaraanSedangDipakai = pickupDriver::where('status_apply', 1)
            ->whereNotNull('kendaraan')
            ->where('kendaraan', '!=', '')
            ->pluck('kendaraan')
            ->unique();

        $allKendaraan = collect(['H1', 'Innova']);

        $kendaraanTersedia = $allKendaraan->diff($kendaraanSedangDipakai);

        if ($kendaraanTersedia->isEmpty()) {
            $kendaraanTersedia = $allKendaraan;
        }

        if ($kendaraanTersedia->contains('Innova')) {
            $kendaraanTersedia = $kendaraanTersedia->map(function ($item) {
                return $item === 'Innova' ? 'Inova' : $item;
            });
        }

        $kendaraan = $kendaraanTersedia->values()->all();

        $extends = 'layouts_crm.app';
        $section = 'crm_contents';

        return view('office.pickupdriver.create', compact('dataDriver', 'budgetPerjalanan', 'kendaraan', 'extends', 'section'));
    }
}
