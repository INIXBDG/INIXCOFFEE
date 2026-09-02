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
use Illuminate\Support\Facades\Cache;
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

        // 2. Filter Tanggal & Waktu Aktivitas
        $tahun = $request->input('tahun', Carbon::now()->year);
        $bulan = $request->input('bulan', Carbon::now()->month);
        $mingguKe = $request->input('minggu', null);
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate && $endDate) {
            $tanggalRange = Carbon::parse($startDate)->translatedFormat('d M Y') . ' - ' . Carbon::parse($endDate)->translatedFormat('d M Y');
            $bulanTahun = Carbon::parse($startDate)->translatedFormat('F Y');
            $startFilter = Carbon::parse($startDate)->startOfDay()->format('Y-m-d H:i:s');
            $endFilter = Carbon::parse($endDate)->endOfDay()->format('Y-m-d H:i:s');
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
            $startFilter = $startOfWeek->format('Y-m-d H:i:s');
            $endFilter = $endOfWeek->format('Y-m-d H:i:s');
        }

        // 1. Kategori perusahaan chart (Cached 1 Jam)
        $chartData = Cache::remember('chart_kategori_perusahaan', 3600, function () {
            $data = Perusahaan::select('kategori_perusahaan', DB::raw('COUNT(id) as total'))->groupBy('kategori_perusahaan')->get();
            $total = $data->sum('total') ?: 1;
            return $data->map(fn($item) => [
                'kategori' => $item->kategori_perusahaan ?? 'Tidak Ada Kategori',
                'persen' => round(($item->total / $total) * 100, 2),
            ]);
        });

        // 3. Ambil Target Sales Spesifik Kolom
        $target = TargetActivity::select('id_sales', 'Contact', 'Call', 'Email', 'Visit', 'Meet', 'Incharge', 'PA', 'PI', 'FormM', 'DB')
            ->get()->keyBy('id_sales');


        // 6. Top 5 Produk Chart Terjual & Menguntungkan (Digabung jadi 1 query)
        $rkmStats = Cache::remember('rkm_stats_top_5', 3600, function () {
            return RKM::with('materi:id,nama_materi')
                ->select(
                    'materi_key',
                    DB::raw('SUM(pax) as total_pax'),
                    DB::raw('SUM(COALESCE(harga_jual, 0) * COALESCE(pax, 0)) as total_revenue')
                )
                ->where('status', '0')
                ->groupBy('materi_key')
                ->get();
        });

        $best = $rkmStats->sortByDesc('total_pax')->take(5)->values();
        $profit = $rkmStats->sortByDesc('total_revenue')->take(5)->values();

        // 8. Segmentasi Daerah per Sales (Cached 1 Jam untuk meringankan beban TTFB)
        $dataSegmentasi = Cache::remember('segmentasi_daerah_sales', 3600, function () {
            $lokasiData = Perusahaan::select('sales_key', 'lokasi', DB::raw('COUNT(id) as total'))
                ->whereNotNull('sales_key')
                ->whereNotNull('lokasi')
                ->groupBy('sales_key', 'lokasi')
                ->get();

            $salesTotals = [];
            foreach ($lokasiData as $row) {
                $salesTotals[$row->sales_key] = ($salesTotals[$row->sales_key] ?? 0) + $row->total;
            }

            $totalDaerah = [];
            foreach ($lokasiData as $row) {
                $totalSales = $salesTotals[$row->sales_key] ?? 0;
                $persen = $totalSales > 0 ? round(($row->total / $totalSales) * 100, 2) : 0;
                $totalDaerah[$row->sales_key][] = [
                    'lokasi' => $row->lokasi,
                    'total' => $row->total,
                    'persen' => $persen,
                ];
            }

            return [
                'totalDaerah' => $totalDaerah,
                'sales' => collect(array_keys($salesTotals))
            ];
        });

        $totalDaerah = $dataSegmentasi['totalDaerah'];
        $sales = $dataSegmentasi['sales'];

        // 10. Map Perusahaan (Cached)
        $map = Cache::remember('map_perusahaan', 3600, function () {
            return DB::table('lokasis')
                ->leftJoin('perusahaans', 'lokasis.lokasi', '=', 'perusahaans.lokasi')
                ->select('lokasis.lokasi', 'lokasis.latitude', 'lokasis.longitude', DB::raw('COUNT(perusahaans.id) as company_count'))
                ->groupBy('lokasis.id', 'lokasis.lokasi', 'lokasis.latitude', 'lokasis.longitude')
                ->get();
        });

        // 11. Top Vendors & Kategori Materi Terjual & Segmen Spend (Cached)
        $topVendors = Cache::remember('top_vendors', 3600, function () {
            return DB::table('r_k_m_s')->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
                ->where('r_k_m_s.status', '0')->select('materis.vendor', DB::raw('COUNT(r_k_m_s.id) as total'))
                ->groupBy('materis.vendor')->orderByDesc('total')->get();
        });

        $topKategoriMateri = Cache::remember('top_kategori_materi', 3600, function () {
            return DB::table('r_k_m_s')->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
                ->where('r_k_m_s.status', '0')->select('materis.kategori_materi', DB::raw('COUNT(r_k_m_s.id) as total'))
                ->groupBy('materis.kategori_materi')->orderByDesc('total')->get();
        });

        $topSpendSeg = Cache::remember('top_spend_seg', 3600, function () {
            return DB::table('r_k_m_s')->join('perusahaans', 'r_k_m_s.perusahaan_key', '=', 'perusahaans.id')
                ->where('r_k_m_s.status', '0')->select('perusahaans.kategori_perusahaan', DB::raw('COUNT(r_k_m_s.id) as total'), DB::raw('SUM(r_k_m_s.harga_jual) as spend'))
                ->groupBy('perusahaans.kategori_perusahaan')->orderByDesc('total')->get();
        });

        // 13. Data Checklist Milik Adm Sales
        $query = RKM::with(['checklist', 'materi', 'perusahaan', 'instruktur', 'sales']);

        if ($request->search) {
            $query->whereHas('materi', function ($q) use ($request) {
                $q->where('nama_materi', 'like', $request->search . '%');
            });
        }

        if ($request->bulan && $request->tahun) {
            if ($request->minggu) {
                $startMonth = Carbon::create($request->tahun, $request->bulan, 1);
                $startWk = $startMonth->copy()->addWeeks($request->minggu - 1)->startOfWeek(Carbon::MONDAY);
                $endWk = $startWk->copy()->endOfWeek(Carbon::SUNDAY);

                $query->whereBetween('created_at', [
                    $startWk->format('Y-m-d 00:00:00'),
                    $endWk->format('Y-m-d 23:59:59')
                ]);
            } else {
                $startMonth = Carbon::create($request->tahun, $request->bulan, 1)->format('Y-m-d 00:00:00');
                $endMonth = Carbon::create($request->tahun, $request->bulan, 1)->endOfMonth()->format('Y-m-d 23:59:59');
                $query->whereBetween('created_at', [$startMonth, $endMonth]);
            }
        } elseif ($request->tahun) {
            $startYr = "{$request->tahun}-01-01 00:00:00";
            $endYr = "{$request->tahun}-12-31 23:59:59";
            $query->whereBetween('created_at', [$startYr, $endYr]);
        }

        $dataRKM = $query->simplePaginate(10);

        return view('crm.dashboard', compact(
            'chartData', 'best', 'profit', 
            'totalDaerah', 'sales', 'map',
            'tanggal', 'mingguKeBulan', 'tahun', 'bulan', 'mingguKe', 'bulanTahun',
            'tanggalRange', 'topSpendSeg', 'topKategoriMateri', 'topVendors', 'dataRKM'
        ));
    }

    public function updateChecklist(Request $request)
    {
        $checklist = checklistRKM::updateOrCreate(
            ['id_rkm' => $request->rkm_id],
            [
                'registrasi_form' => DB::raw('registrasi_form'), // Pertahankan nilai lama jika ada
                'surat_kontrak' => DB::raw('surat_kontrak'),
                'PA' => DB::raw('PA'),
                'PO' => DB::raw('PO'),
                $request->field => (bool) $request->value,
            ]
        );

        return response()->json([
            'success' => true,
            'updated_field' => $request->field,
            'value' => (bool) $request->value,
        ]);
    }

    public function detailAktivitasApi(Request $request)
    {
        $query = Aktivitas::select(
                'id', 'aktivitas', 'waktu_aktivitas', 'id_contact', 'id_peserta',
                'id_peluang', 'deskripsi', 'harga', 'pax', 'total', 'foto_lokasi', 'latitude', 'longitude'
            )
            ->with(['contact.perusahaan', 'peserta', 'perusahaanLangsung'])
            ->where('id_sales', $request->id_sales);

        $label = $request->aktivitas;
        $tipeArray = [$label];

        if ($label === 'Penawaran Awal') $tipeArray = ['PA'];
        if ($label === 'Leads') $tipeArray = ['PI', 'Leads'];
        if ($label === 'Regis Form') $tipeArray = ['Form_Masuk', 'Regis Form'];

        $query->whereIn('aktivitas', $tipeArray);

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('waktu_aktivitas', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        return response()->json($query->get());
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
        $query = Peluang::with(['materiRelation:id,nama_materi', 'perusahaan:id,nama_perusahaan'])
            ->where('id_sales', $id_sales)
            ->whereNotNull($dateColumn);

        // Optimasi Range Tanggal Tanpa Fungsi SQL di Kolom
        $ranges = [
            'TR1' => ["{$tahun}-01-01 00:00:00", "{$tahun}-03-31 23:59:59"],
            'TR2' => ["{$tahun}-04-01 00:00:00", "{$tahun}-06-30 23:59:59"],
            'TR3' => ["{$tahun}-07-01 00:00:00", "{$tahun}-09-30 23:59:59"],
            'TR4' => ["{$tahun}-10-01 00:00:00", "{$tahun}-12-31 23:59:59"],
        ];

        if (isset($ranges[$triwulan])) {
            $query->whereBetween($dateColumn, $ranges[$triwulan]);
        } else {
            $query->whereBetween($dateColumn, ["{$tahun}-01-01 00:00:00", "{$tahun}-12-31 23:59:59"]);
        }

        $data = $query->select('materi', 'perusahaan_id', 'id_contact', 'netsales', 'pax', DB::raw('(netsales * pax) as total'), $dateColumn)->get();

        return response()->json($data);
    }

    public function apiProspekMingguan()
    {
        $prospekStart = Carbon::now()->startOfWeek()->format('Y-m-d H:i:s');
        $prospekEnd = Carbon::now()->endOfWeek()->format('Y-m-d H:i:s');
        
        $prospek = Peluang::with('materiRelation:id,nama_materi')
            ->whereBetween('created_at', [$prospekStart, $prospekEnd])
            ->get();
            
        return response()->json($prospek);
    }

    public function apiIncompletePA(Request $request)
    {
        $PA = perhitunganNetSales::with([
                'rkm.materi', 
                'rkm.perusahaan', 
                'trackingNetSales', 
                'rkm.peluang'
            ])
            ->whereHas('trackingNetSales', function ($query) {
                $query->where('tracking', '!=', 'Selesai')->orWhereNull('tracking');
            })
            ->simplePaginate(10); // Menggunakan simplePaginate untuk meringankan kueri COUNT
            
        return response()->json($PA);
    }

    public function apiPivotStatus()
    {
        $totalStatus = Perusahaan::select('status', 'sales_key', DB::raw('COUNT(id) as total'))
            ->whereNotNull('status')
            ->whereNotNull('sales_key')
            ->groupBy('status', 'sales_key')
            ->get();

        $statuses = $totalStatus->pluck('status')->unique()->sort()->values();
        $pivotData = [];
        
        foreach ($totalStatus as $item) {
            $pivotData[$item->sales_key][$item->status] = $item->total;
        }

        return response()->json([
            'statuses' => $statuses,
            'data' => $pivotData
        ]);
    }

    public function apiTotalWinLost(Request $request)
    {
        $tahunDipilih = $request->query('tahun', now()->year);
        $startYear = "{$tahunDipilih}-01-01 00:00:00";
        $endYear = "{$tahunDipilih}-12-31 23:59:59";

        $dataRingkasanWinRaw = Peluang::whereBetween('merah', [$startYear, $endYear])
            ->select('id_sales', DB::raw('QUARTER(merah) as triwulan_angka'), DB::raw('SUM(netsales * pax) as total_jumlah'))
            ->groupBy('id_sales', 'triwulan_angka')
            ->get();

        $dataRingkasanLostRaw = Peluang::whereBetween('lost', [$startYear, $endYear])
            ->select('id_sales', DB::raw('QUARTER(lost) as triwulan_angka'), DB::raw('SUM(COALESCE(harga, 0) * COALESCE(pax, 0)) as total_jumlah'))
            ->groupBy('id_sales', 'triwulan_angka')
            ->get();

        $formatTriwulan = function($data) {
            $result = [];
            foreach ($data as $row) {
                $result[$row->id_sales]['TR' . $row->triwulan_angka] = $row->total_jumlah;
            }
            return $result;
        };

        $dataRingkasanWin = $formatTriwulan($dataRingkasanWinRaw);
        $dataRingkasanLost = $formatTriwulan($dataRingkasanLostRaw);

        $salesList = User::where('jabatan', 'Sales')->where('status_akun', '1')->pluck('id_sales')->toArray();
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

        return response()->json([
            'win' => $totalWin,
            'lost' => $totalLost
        ]);
    }

    public function apiTargetAktivitas(Request $request)
    {
        $today = Carbon::now()->locale('id');
        $tahun = $request->input('tahun', $today->year);
        $bulan = $request->input('bulan', $today->month);
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Kalkulasi Rentang Waktu
        if ($startDate && $endDate) {
            $tanggalRange = Carbon::parse($startDate)->translatedFormat('d M Y') . ' - ' . Carbon::parse($endDate)->translatedFormat('d M Y');
            $startFilter = Carbon::parse($startDate)->startOfDay()->format('Y-m-d H:i:s');
            $endFilter = Carbon::parse($endDate)->endOfDay()->format('Y-m-d H:i:s');
        } else {
            $startOfWeek = Carbon::create($tahun, $bulan, 1)->startOfMonth();
            $endOfWeek = (clone $startOfWeek)->endOfMonth();
            
            $tanggalRange = $startOfWeek->translatedFormat('d') . ' – ' . $endOfWeek->translatedFormat('d F Y');
            $startFilter = $startOfWeek->format('Y-m-d H:i:s');
            $endFilter = $endOfWeek->format('Y-m-d H:i:s');
        }

        // Ambil Target Sales Spesifik Kolom
        $target = TargetActivity::select('id_sales', 'Contact', 'Call', 'Email', 'Visit', 'Meet', 'Incharge', 'PA', 'PI', 'FormM', 'DB')
            ->get()->keyBy('id_sales');

        // Hitung Aktivitas Per Sales
        $aktivitasCountsRaw = Aktivitas::select(
                'id_sales',
                'aktivitas',
                DB::raw('COUNT(id) as hitungan'),
                DB::raw('SUM(total) as nilai_total')
            )
            ->whereBetween('waktu_aktivitas', [$startFilter, $endFilter])
            ->groupBy('id_sales', 'aktivitas')
            ->get();

        $aktivitasCounts = [];
        $aktivitasSums = [];
        foreach ($aktivitasCountsRaw as $ac) {
            $aktivitasCounts[$ac->id_sales][$ac->aktivitas] = $ac->hitungan;
            $aktivitasSums[$ac->id_sales][$ac->aktivitas] = $ac->nilai_total;
        }

        $salesList = User::where('jabatan', 'Sales')->where('status_akun', '1')->pluck('id_sales')->toArray();
        $activitysales = [];

        foreach ($salesList as $id_sales) {
            $salesTarget = $target[$id_sales] ?? null;

            $activitysales[] = [
                'id_sales'          => $id_sales,
                'DB'                => $aktivitasCounts[$id_sales]['DB'] ?? 0,
                'contact'           => $aktivitasCounts[$id_sales]['Contact'] ?? 0,
                'call'              => $aktivitasCounts[$id_sales]['Call'] ?? 0,
                'email'             => $aktivitasCounts[$id_sales]['Email'] ?? 0,
                'visit'             => $aktivitasCounts[$id_sales]['Visit'] ?? 0,
                'meet'              => $aktivitasCounts[$id_sales]['Meet'] ?? 0,
                'incharge'          => $aktivitasCounts[$id_sales]['Incharge'] ?? 0,
                'PA'                => $aktivitasCounts[$id_sales]['PA'] ?? 0,
                'Leads'             => ($aktivitasCounts[$id_sales]['PI'] ?? 0) + ($aktivitasCounts[$id_sales]['Leads'] ?? 0),
                'Regis_Form'        => ($aktivitasCounts[$id_sales]['Form_Masuk'] ?? 0) + ($aktivitasCounts[$id_sales]['Regis Form'] ?? 0),

                'total_PA'          => $aktivitasSums[$id_sales]['PA'] ?? 0,
                'total_Regis_Form'  => ($aktivitasSums[$id_sales]['Form_Masuk'] ?? 0) + ($aktivitasSums[$id_sales]['Regis Form'] ?? 0),

                'target_DB'         => $salesTarget->DB ?? 0,
                'target_contact'    => $salesTarget->Contact ?? 0,
                'target_call'       => $salesTarget->Call ?? 0,
                'target_email'      => $salesTarget->Email ?? 0,
                'target_visit'      => $salesTarget->Visit ?? 0,
                'target_meet'       => $salesTarget->Meet ?? 0,
                'target_incharge'   => $salesTarget->Incharge ?? 0,
                'target_PA'         => $salesTarget->PA ?? 0,
                'target_PI'         => $salesTarget->PI ?? 0,
                'target_Form_Masuk' => $salesTarget->FormM ?? 0,
            ];
        }

        return response()->json([
            'tanggalRange' => $tanggalRange,
            'activitysales' => $activitysales
        ]);
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
