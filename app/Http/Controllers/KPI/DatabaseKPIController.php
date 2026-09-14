<?php

namespace App\Http\Controllers\KPI;

use App\Http\Controllers\Controller;
use App\Models\activityLog;
use App\Models\formPenilaian;
use App\Models\karyawan;
use App\Models\kategoriKPI;
use App\Models\nilaiKPI;
use App\Models\pengajuancuti;
use App\Models\RKM;
use App\Models\shareForm;
use App\Models\tipeKategoriTabel;
use App\Models\User;
use App\Notifications\CommentNotification;
use App\Notifications\penilaianExcangheNotifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Mail\mailPenilaian;
use App\Models\AbsensiKaryawan;
use App\Models\AdministrasiKaryawan;
use App\Models\izinTigaJam;
use App\Models\Nilaifeedback;
use App\Models\PengajuanBarang;
use App\Models\SuratPerjalanan;
use App\Models\targetKPI;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DatabaseKPIController extends Controller
{
   public function __construct()
    {
        $this->middleware('auth');
    }

    public function activityLog()
    {
        $id_karyawan = Auth::user()->id;

        // OPTIMASI: Menambahkan select() untuk mengurangi beban memori
        $dataAuth = \Illuminate\Support\Facades\Cache::remember("activity_log_auth_{$id_karyawan}", 3600, function() use ($id_karyawan) {
            return activityLog::select('id', 'user_id', 'status', 'created_at')
                ->with('karyawan:id,nama_lengkap')
                ->where('user_id', $id_karyawan)
                ->whereIn('status', ['Login', 'Logout'])
                ->orderBy('created_at', 'desc')
                ->get();
        });

        $dataVisit = \Illuminate\Support\Facades\Cache::remember("activity_log_visit_{$id_karyawan}", 3600, function() use ($id_karyawan) {
            return activityLog::select('id', 'user_id', 'status', 'created_at')
                ->with('karyawan:id,nama_lengkap')
                ->where('user_id', $id_karyawan)
                ->whereNotIn('status', ['Login', 'Logout', 'Absen Masuk', 'Absen Keluar'])
                ->orderBy('created_at', 'desc')
                ->get();
        });

        $dataAbsen = \Illuminate\Support\Facades\Cache::remember("activity_log_absen_{$id_karyawan}", 3600, function() use ($id_karyawan) {
            return activityLog::select('id', 'user_id', 'status', 'created_at')
                ->with('karyawan:id,nama_lengkap')
                ->where('user_id', $id_karyawan)
                ->whereIn('status', ['Absen Masuk', 'Absen Keluar'])
                ->orderBy('created_at', 'desc')
                ->get();
        });

        $dataUptimeInformasional = \Illuminate\Support\Facades\Cache::remember("uptime_log_informasional", 3600, function() {
            return activityLog::select('id', 'status', 'created_at')
                ->whereBetween('status', [100, 199])
                ->orderBy('created_at', 'desc')
                ->get();
        });

        $dataUptimeSuccess = \Illuminate\Support\Facades\Cache::remember("uptime_log_success", 3600, function() {
            return activityLog::select('id', 'status', 'created_at')
                ->whereBetween('status', [200, 299])
                ->orderBy('created_at', 'desc')
                ->get();
        });

        $dataUptimeRedirect = \Illuminate\Support\Facades\Cache::remember("uptime_log_redirect", 3600, function() {
            return activityLog::select('id', 'status', 'created_at')
                ->whereBetween('status', [300, 399])
                ->orderBy('created_at', 'desc')
                ->get();
        });

        $dataUptimeClientError = \Illuminate\Support\Facades\Cache::remember("uptime_log_client_error", 3600, function() {
            return activityLog::select('id', 'status', 'created_at')
                ->whereBetween('status', [400, 499])
                ->orderBy('created_at', 'desc')
                ->get();
        });

        $dataUptimeServerError = \Illuminate\Support\Facades\Cache::remember("uptime_log_server_error", 3600, function() {
            return activityLog::select('id', 'status', 'created_at')
                ->whereBetween('status', [500, 599])
                ->orderBy('created_at', 'desc')
                ->get();
        });

        return view('databasekpi.activityLog', compact(
            'dataAuth', 'dataVisit', 'dataAbsen', 'dataUptimeInformasional',
            'dataUptimeSuccess', 'dataUptimeRedirect', 'dataUptimeClientError', 'dataUptimeServerError'
        ));
    }

    public function UptimePresentase()
    {
        $now = Carbon::now();
        $weekStart = $now->copy()->startOfWeek();
        $weekEnd = $now->copy()->endOfWeek();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        try {
            $response = Http::timeout(10)->get('http://192.168.95.173:8000/uptime.php', [
                'password' => env('UPTIME_PASSWORD')
            ]);

            if ($response->failed() || $response->body() === 'FILE_NOT_FOUND') {
                return response()->json(['error' => 'Tidak bisa mengambil file dari Server CCTV'], 404);
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json(['error' => 'Koneksi ke Server CCTV timeout atau terputus'], 503);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat menghubungi Server CCTV'], 500);
        }

        $content = $response->body();
        $lines   = array_filter(explode("\n", $content));

        $records = [];
        foreach ($lines as $index => $line) {
            if ($index === 0) continue;
            if (preg_match('/^(.*?)\s*,\s*(.*?)\s*,\s*(.*?)\s*,\s*(.*?)\s*,\s*(.*)$/', $line, $matches)) {
                $records[] = [
                    'timestamp' => $matches[1],
                    'server'    => $matches[2],
                    'ip'        => $matches[3],
                    'status'    => strtoupper(trim($matches[4])),
                    'downtime'  => trim($matches[5]),
                ];
            }
        }

        $apkRecords = array_filter($records, fn($r) => stripos($r['server'], 'APK') !== false);

        $filterByRange = function($arr, $start, $end) {
            return array_filter($arr, function($r) use ($start, $end) {
                $ts = Carbon::parse($r['timestamp']);
                return $ts >= $start && $ts <= $end;
            });
        };

        $apkWeek = $filterByRange($apkRecords, $weekStart, $weekEnd);
        $apkWeekDowntime = array_sum(array_map(function($r) {
            if ($r['status'] === 'RECOVERY' && preg_match('/(\d+):(\d+):(\d+)/', $r['downtime'], $m)) {
                return ((int)$m[1]) * 60 + (int)$m[2] + ((int)$m[3] > 0 ? 1 : 0);
            }
            return 0;
        }, $apkWeek));
        $totalWeekMinutes = $weekStart->diffInMinutes($weekEnd) + 1;
        $apkWeekPercent = $totalWeekMinutes > 0 ? (($totalWeekMinutes - $apkWeekDowntime) / $totalWeekMinutes) * 100 : 0;

        $apkMonth = $filterByRange($apkRecords, $monthStart, $monthEnd);
        $apkMonthDowntime = array_sum(array_map(function($r) {
            if ($r['status'] === 'RECOVERY' && preg_match('/(\d+):(\d+):(\d+)/', $r['downtime'], $m)) {
                return ((int)$m[1]) * 60 + (int)$m[2] + ((int)$m[3] > 0 ? 1 : 0);
            }
            return 0;
        }, $apkMonth));
        $totalMonthMinutes = $monthStart->diffInMinutes($monthEnd) + 1;
        $apkMonthPercent = $totalMonthMinutes > 0 ? (($totalMonthMinutes - $apkMonthDowntime) / $totalMonthMinutes) * 100 : 0;

        // OPTIMASI: Menggabungkan query menjadi 2 query saja menggunakan conditional aggregation
        $latteStats = activityLog::selectRaw("
            COUNT(CASE WHEN created_at BETWEEN ? AND ? THEN 1 END) as week_total,
            SUM(CASE WHEN status = '200' AND created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as week_up,
            COUNT(CASE WHEN created_at BETWEEN ? AND ? THEN 1 END) as month_total,
            SUM(CASE WHEN status = '200' AND created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as month_up
        ", [
            $weekStart, $weekEnd, $weekStart, $weekEnd,
            $monthStart, $monthEnd, $monthStart, $monthEnd
        ])->where('url', 'https://192.168.95.60:8002/')->first();

        $latteWeekTotal = $latteStats->week_total ?? 0;
        $latteWeekUp = $latteStats->week_up ?? 0;
        $latteWeekPercent = $latteWeekTotal > 0 ? ($latteWeekUp / $latteWeekTotal) * 100 : 0;

        $latteMonthTotal = $latteStats->month_total ?? 0;
        $latteMonthUp = $latteStats->month_up ?? 0;
        $latteMonthPercent = $latteMonthTotal > 0 ? ($latteMonthUp / $latteMonthTotal) * 100 : 0;

        return response()->json([
            'coffee_week' => round($apkWeekPercent, 2),
            'coffee_week_downtime' => $apkWeekDowntime,
            'coffee_month' => round($apkMonthPercent, 2),
            'coffee_month_downtime' => $apkMonthDowntime,
            'latte_week' => round($latteWeekPercent, 2),
            'latte_month' => round($latteMonthPercent, 2),
        ]);
    }

    public function getActivityChart()
    {
        try {
            $rawUrls = config('uptime.urls');
            if (empty($rawUrls)) {
                return response()->json(['error' => 'UPTIME_URLS not configured'], 500);
            }

            $urls = is_array($rawUrls) ? $rawUrls : array_filter(array_map('trim', explode(',', $rawUrls)));

            // OPTIMASI: Mengambil semua data sekaligus, bukan query di dalam loop
            $allChecks = activityLog::whereIn('url', $urls)
                ->where('status', 'uptime')
                ->orderBy('created_at', 'desc')
                ->limit(100 * count($urls))
                ->get()
                ->groupBy('url');

            $result = [];
            foreach ($urls as $url) {
                $checks = $allChecks->get($url, collect())->take(100);
                
                $result[$url] = [
                    'labels' => $checks->map(fn($log) => $log->created_at->format('d M H:i'))->values(),
                    'response_times' => $checks->map(fn($log) => $log->response_time_ms ?? 0)->values(),
                    'statuses' => $checks->map(fn($log) => (bool) $log->is_up)->values(),
                ];
            }

            return response()->json($result, 200, [], JSON_UNESCAPED_SLASHES);
        } catch (\Throwable $e) {
            Log::error('getActivityChart failed: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    public function downloadDivisi(Request $request)
    {
        $request->validate([
            'divisi'  => 'required|string',
            'tahun'   => 'required|integer'
        ]);

        $tahun  = $request->input('tahun');
        $divisi = $request->input('divisi');

        // OPTIMASI: Eager load dan ambil data sekaligus
        $formPenilaians = formPenilaian::with('karyawan:id,nama_lengkap,divisi')
            ->whereHas('karyawan', function ($q) use ($divisi) {
                $q->where('divisi', $divisi);
            })
            ->where('tahun', $tahun)
            ->where('jenis_form', 'Rutin')
            ->get();

        if ($formPenilaians->isEmpty()) {
            return back()->with('error', 'Tidak ada data penilaian untuk divisi & periode tersebut');
        }

        $finalData = [];
        $kodeKategoris = $formPenilaians->pluck('kode_kategori')->unique();
        $kodeForms = $formPenilaians->pluck('kode_form')->unique();
        $idKaryawans = $formPenilaians->pluck('id_karyawan')->unique();

        // Bulk fetch untuk menghindari N+1
        $allKategoriKPIs = kategoriKPI::whereIn('kode_kategori', $kodeKategoris)->get()->groupBy('kode_kategori');
        $allTipeKategori = tipeKategoriTabel::whereIn('id_kategori', $allKategoriKPIs->flatten()->pluck('id'))->get()->groupBy('id_kategori');
        
        $allShareForms = shareForm::with('evaluator:id,nama_lengkap,divisi')
            ->whereIn('id_evaluated', $idKaryawans)
            ->whereIn('kode_form', $kodeForms)
            ->get();
            
        $allNilaiKPI = nilaiKPI::whereIn('id_evaluated', $idKaryawans)
            ->whereIn('kode_form', $kodeForms)
            ->get();

        foreach ($formPenilaians->groupBy('id_karyawan') as $id_karyawan => $forms) {
            $form = $forms->first();

            $evaluated = [
                'nama'        => optional($form->karyawan)->nama_lengkap . ' - ' . optional($form->karyawan)->divisi ?? '-',
                'tahun'       => $form->tahun,
                'id_karyawan' => $form->id_karyawan,
                'catatan'     => $form->catatan,
            ];

            $dataAbsen = $this->getDataAbsen($form->id_karyawan, $tahun);

            $uniqueKodeKategori = $forms->pluck('kode_kategori')->unique();
            $kategoriKPIsForUser = $uniqueKodeKategori->flatMap(fn($kk) => $allKategoriKPIs->get($kk, collect()))->unique('judul_kategori')->values();

            $evaluatorList = [];
            $evaluatorDataForUser = $allShareForms->where('id_evaluated', $form->id_karyawan);

            foreach ($evaluatorDataForUser as $evaluatorItem) {
                $nilaiCollection = $allNilaiKPI->where('id_evaluator', $evaluatorItem->id_evaluator)
                    ->where('id_evaluated', $evaluatorItem->id_evaluated)
                    ->where('kode_form', $evaluatorItem->kode_form)
                    ->where('jenis_penilaian', $evaluatorItem->jenis_penilaian);

                $listNilaiEvaluator = [];
                foreach ($kategoriKPIsForUser as $kategori) {
                    $item = $nilaiCollection->first(fn($item) => 
                        $item->id_evaluator === $evaluatorItem->id_evaluator &&
                        $item->name_variabel === $kategori->judul_kategori
                    );

                    $listNilaiEvaluator[] = [
                        'pesan' => $item->pesan ?? '-',
                        'nilai' => $item->nilai ?? '-'
                    ];
                }

                $evaluatorList[] = [
                    'nama'            => optional($evaluatorItem->evaluator)->nama_lengkap . ' - ' . optional($evaluatorItem->evaluator)->divisi ?? '-',
                    'jenis_penilaian' => $evaluatorItem->jenis_penilaian ?? '-',
                    'nilai'           => $listNilaiEvaluator
                ];
            }

            $dataKriteria = $forms->map(function ($form) use ($allKategoriKPIs, $allTipeKategori) {
                $kategoris = $allKategoriKPIs->get($form->kode_kategori, collect());
                
                $detailKriteria = $kategoris->map(function ($kategori) use ($allTipeKategori) {
                    $tipeDetails = $allTipeKategori->get($kategori->id, collect());
                    return [
                        'sub_kriteria' => $kategori->judul_kategori,
                        'bobot' => $kategori->bobot,
                        'detailTipeSubKriteria' => $tipeDetails->map(fn($tipe) => [
                            'ket_sub_tipe' => $tipe->ket_tipe,
                            'nilai_ket_sub_tipe' => $tipe->nilai_ket_sub_tipe
                        ])->toArray()
                    ];
                });

                return [
                    'kriteria' => $form->nama_penilaian,
                    'detailKriteria' => $detailKriteria
                ];
            })->toArray();

            $evaluatorList = collect($evaluatorList)->unique(fn($item) => $item['nama'] . $item['jenis_penilaian'])->values();

            $finalData[] = [
                'evaluated' => $evaluated,
                'dataAbsen' => $dataAbsen,
                'data' => [
                    'evaluator' => $evaluatorList,
                    'dataKriteria' => $dataKriteria
                ]
            ];
        }

        return view('pdf.rekapPenilaianDivisi', ['data' => $finalData]);
    }

    public function downloadPDF(Request $request)
    {
        $request->validate([
            'id_karyawan' => 'required',
            'kodeForm'    => 'required|string',
            'tipe'        => 'required'
        ]);

        $tipe_button = $request->input('tipe');
        $id_karyawan = $request->input('id_karyawan');
        $kodeForm = $request->input('kodeForm');

        $karyawan = karyawan::find($id_karyawan);
        if (!$karyawan) {
            return back()->with('error', 'Data karyawan tidak ditemukan');
        }

        $formPenilaians = formPenilaian::with('karyawan:id,nama_lengkap,divisi')
            ->where('id_karyawan', $id_karyawan)
            ->where('kode_form', $kodeForm)
            ->get();

        if ($formPenilaians->isEmpty()) {
            return back()->with('error', 'Data form penilaian tidak ditemukan');
        }

        $year = now()->year;
        $formPenilaiansTahun = formPenilaian::where('id_karyawan', $id_karyawan)
            ->where('kode_form', $kodeForm)
            ->where('tahun', $year)
            ->pluck('id_karyawan');

        $shareFormTahun = shareForm::whereIn('id_evaluated', $formPenilaiansTahun)
            ->where('kode_form', $kodeForm)
            ->get();

        $dataNilaiTahun = nilaiKPI::whereIn('id_evaluated', $shareFormTahun->pluck('id_evaluated'))
            ->where('kode_form', $kodeForm)
            ->get();

        $getNilaiFinal = fn($data) => collect($data)
            ->filter(fn($item) => is_numeric($item?->nilai))
            ->map(function ($item) use ($formPenilaians) {
                // OPTIMASI: Ambil bobot dari collection yang sudah di-load jika memungkinkan, atau query tetap aman
                $bobot = kategoriKPI::where('kode_kategori', $item?->kode_kategori)->value('bobot') ?? 0;
                return (($item?->nilai ?? 0) * $bobot) / 100;
            })->sum();

        $dataNilaiTahunCount = $getNilaiFinal($dataNilaiTahun);
        $form = $formPenilaians->first();

        $evaluated = [
            'nama'        => optional($form->karyawan)->nama_lengkap . ' - ' . optional($form->karyawan)->divisi ?? '-',
            'quartal'     => $form->quartal,
            'tahun'       => $form->tahun,
            'id_karyawan' => $form->id_karyawan,
            'catatan'     => $form->catatan,
        ];

        $dataAbsen = $this->getDataAbsen($form->id_karyawan, $year);

        $kodeKategoris = $formPenilaians->pluck('kode_kategori')->unique();
        $allKategoriKPIs = kategoriKPI::whereIn('kode_kategori', $kodeKategoris)->get()->unique('judul_kategori')->values();
        $allTipeKategori = tipeKategoriTabel::whereIn('id_kategori', $allKategoriKPIs->pluck('id'))->get()->groupBy('id_kategori');

        $allEvaluatorData = shareForm::with('evaluator:id,nama_lengkap,divisi')
            ->where('id_evaluated', $form->id_karyawan)
            ->where('kode_form', $kodeForm)
            ->get();

        $evaluatorList = [];
        foreach ($allEvaluatorData as $evaluatorItem) {
            $nilaiCollection = nilaiKPI::where('id_evaluator', $evaluatorItem->id_evaluator)
                ->where('id_evaluated', $evaluatorItem->id_evaluated)
                ->where('kode_form', $kodeForm)
                ->where('jenis_penilaian', $evaluatorItem->jenis_penilaian)
                ->get();

            $listNilaiEvaluator = [];
            foreach ($allKategoriKPIs as $kategori) {
                $item = $nilaiCollection->first(fn($i) => 
                    $i->id_evaluator === $evaluatorItem->id_evaluator &&
                    $i->name_variabel === $kategori->judul_kategori
                );

                $listNilaiEvaluator[] = [
                    'pesan' => $item?->pesan ?? '-',
                    'nilai' => $item?->nilai ?? 0,
                ];
            }

            $evaluatorList[] = [
                'nama'            => optional($evaluatorItem->evaluator)->nama_lengkap . ' - ' . optional($evaluatorItem->evaluator)->divisi ?? '-',
                'jenis_penilaian' => $evaluatorItem->jenis_penilaian ?? '-',
                'nilai'           => $listNilaiEvaluator
            ];
        }

        $dataKriteria = $formPenilaians->map(function ($form) use ($allKategoriKPIs, $allTipeKategori) {
            $kategoris = $allKategoriKPIs->where('kode_kategori', $form->kode_kategori);
            $detailKriteria = $kategoris->map(function ($kategori) use ($allTipeKategori) {
                $tipeDetails = $allTipeKategori->get($kategori->id, collect());
                return [
                    'sub_kriteria' => $kategori->judul_kategori,
                    'tipe_kriteria' => $kategori->tipe_kategori,
                    'bobot' => $kategori->bobot,
                    'detailTipeSubKriteria' => $tipeDetails->map(fn($tipe) => [
                        'ket_sub_tipe' => $tipe->ket_tipe,
                        'nilai_ket_sub_tipe' => $tipe->nilai_ket_sub_tipe
                    ])->toArray()
                ];
            });

            return [
                'kriteria' => $form->nama_penilaian,
                'detailKriteria' => $detailKriteria
            ];
        })->toArray();

        $evaluatorList = collect($evaluatorList)->unique(fn($item) => $item['nama'] . $item['jenis_penilaian'])->values();

        $data = [
            'data' => [[
                'evaluated' => $evaluated,
                'dataAbsen' => $dataAbsen,
                'data' => [
                    'evaluator' => $evaluatorList,
                    'dataKriteria' => $dataKriteria
                ],
                'tipe_pdf' => $tipe_button
            ]]
        ];

        return view('pdf.rekapPenilaian', $data);
    }

    public function indexKategori(Request $request)
    {
        $divisi = karyawan::select('divisi')->distinct()->pluck('divisi');
        $tipe = $request->query('tipe', 'rutin');
        return view('databasekpi.indexKategori', compact('divisi', 'tipe'));
    }

    public function kirimEmailData(Request $request)
    {
        $request->validate([
            'id_karyawan' => 'required',
            'kodeForm'    => 'required|string'
        ]);

        $emailKaryawan = karyawan::find($request->input('id_karyawan'));
        if (!$emailKaryawan) {
            return response()->json(['error' => 'Data karyawan tidak ditemukan'], 404);
        }
        if (empty($emailKaryawan->email)) {
            return response()->json(['error' => 'Email karyawan belum tersedia'], 400);
        }

        try {
            $id_karyawan = $request->input('id_karyawan');
            $kodeForm = $request->input('kodeForm');

            $formPenilaians = formPenilaian::with('karyawan:id,nama_lengkap,divisi')
                ->where('id_karyawan', $id_karyawan)
                ->where('kode_form', $kodeForm)
                ->get();

            if ($formPenilaians->isEmpty()) {
                return response()->json(['message' => 'Data tidak ditemukan'], 404);
            }

            $form = $formPenilaians->first();
            $evaluated = [
                'nama'        => optional($form->karyawan)->nama_lengkap . ' - ' . optional($form->karyawan)->divisi ?? '-',
                'id_karyawan' => $form->id_karyawan,
                'quartal'     => $form->quartal,
                'tahun'       => $form->tahun,
                'catatan'     => $form->catatan,
            ];

            $dataAbsen = $this->getDataAbsen($form->id_karyawan, now()->year);

            $kodeKategoris = $formPenilaians->pluck('kode_kategori')->unique();
            $allKategoriKPIs = kategoriKPI::whereIn('kode_kategori', $kodeKategoris)->get()->unique('judul_kategori')->values();
            $allTipeKategori = tipeKategoriTabel::whereIn('id_kategori', $allKategoriKPIs->pluck('id'))->get()->groupBy('id_kategori');

            $allEvaluatorData = shareForm::with('evaluator:id,nama_lengkap,divisi')
                ->where('id_evaluated', $form->id_karyawan)
                ->where('kode_form', $kodeForm)
                ->get();

            $evaluatorList = [];
            foreach ($allEvaluatorData as $evaluatorItem) {
                $nilaiCollection = nilaiKPI::where('id_evaluator', $evaluatorItem->id_evaluator)
                    ->where('id_evaluated', $evaluatorItem->id_evaluated)
                    ->where('kode_form', $kodeForm)
                    ->where('jenis_penilaian', $evaluatorItem->jenis_penilaian)
                    ->get();

                $listNilaiEvaluator = [];
                foreach ($allKategoriKPIs as $kategori) {
                    $item = $nilaiCollection->first(fn($item) => 
                        $item->id_evaluator === $evaluatorItem->id_evaluator &&
                        $item->name_variabel === $kategori->judul_kategori
                    );

                    $listNilaiEvaluator[] = [
                        'pesan' => $item->pesan ?? '-',
                        'nilai' => $item->nilai ?? '-'
                    ];
                }

                $evaluatorList[] = [
                    'nama'            => optional($evaluatorItem->evaluator)->nama_lengkap . ' - ' . optional($evaluatorItem->evaluator)->divisi ?? '-',
                    'jenis_penilaian' => $evaluatorItem->jenis_penilaian ?? '-',
                    'nilai'           => $listNilaiEvaluator,
                ];
            }

            $dataKriteria = $formPenilaians->groupBy(fn($item) => $item->kode_form . '|' . $item->nama_penilaian)
                ->map(function ($groupedForms, $combinedKey) use ($allKategoriKPIs, $allTipeKategori) {
                    [$kodeForm, $namaPenilaian] = explode('|', $combinedKey);
                    $kategoris = $allKategoriKPIs->whereIn('kode_kategori', $groupedForms->pluck('kode_kategori'))->unique('judul_kategori')->values();

                    $detailKriteria = $kategoris->map(function ($kategori) use ($allTipeKategori) {
                        $tipeDetails = $allTipeKategori->get($kategori->id, collect());
                        return [
                            'sub_kriteria' => $kategori->judul_kategori,
                            'bobot' => $kategori->bobot,
                            'detailTipeSubKriteria' => $tipeDetails->map(fn($tipe) => [
                                'ket_sub_tipe' => $tipe->ket_tipe,
                                'nilai_ket_sub_tipe' => $tipe->nilai_ket_sub_tipe
                            ])->toArray()
                        ];
                    });

                    return [
                        'kriteria' => $namaPenilaian,
                        'kodeForm' => $kodeForm,
                        'detailKriteria' => $detailKriteria
                    ];
                })->values()->toArray();

            $evaluatorList = collect($evaluatorList)->unique(fn($item) => $item['nama'] . $item['jenis_penilaian'])->values();

            Mail::to($emailKaryawan->email)->send(new mailPenilaian([
                'evaluated'    => $evaluated,
                'dataAbsen'    => $dataAbsen,
                'evaluator'    => $evaluatorList,
                'dataKriteria' => $dataKriteria,
            ]));

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Gagal mengirim email penilaian', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Gagal mengirim email penilaian',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function sendCatatan(Request $request)
    {
        $request->validate([
            'id_karyawan' => 'required',
            'tahun'       => 'required',
            'kode_form'   => 'required',
            'catatan'     => 'required|string'
        ]);

        $affectedRows = formPenilaian::where('id_karyawan', $request->id_karyawan)
            ->where('kode_form', $request->kode_form)
            ->where('tahun', $request->tahun)
            ->update(['catatan' => $request->catatan]);

        if ($affectedRows > 0) {
            return back()->with('success', 'berhasil memberikan catatan');
        } else {
            return back()->with('error', 'Tidak ada data yang ditemukan untuk diperbarui.');
        }
    }

    public function getDetailPenilaian(Request $request)
    {
        $request->validate([
            'id_karyawan' => 'required',
            'kodeForm'    => 'required|string',
            'jenis_form'  => 'required|string',
        ]);

        $id_karyawan = $request->input('id_karyawan');
        $kodeForm = $request->input('kodeForm');
        $jenis_form = $request->input('jenis_form');

        $formPenilaians = formPenilaian::with('karyawan:id,nama_lengkap,divisi')
            ->where('id_karyawan', $id_karyawan)
            ->where('kode_form', $kodeForm)
            ->where('jenis_form', $jenis_form)
            ->get();

        if ($formPenilaians->isEmpty()) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $form = $formPenilaians->first();
        $evaluated = [
            'nama'        => optional($form->karyawan)->nama_lengkap . ' - ' . (optional($form->karyawan)->divisi ?? '-'),
            'id_karyawan' => $form->id_karyawan,
            'tahun'       => $form->tahun,
            'catatan'     => $form->catatan,
            'kode_form'   => $form->kode_form
        ];

        $dataAbsen = $this->getDataAbsen($form->id_karyawan, $form->tahun);

        $kodeKategoris = $formPenilaians->pluck('kode_kategori')->unique();
        $allKategoriKPIs = kategoriKPI::whereIn('kode_kategori', $kodeKategoris)->get()->unique('judul_kategori')->values();
        $allTipeKategori = tipeKategoriTabel::whereIn('id_kategori', $allKategoriKPIs->pluck('id'))->get()->groupBy('id_kategori');

        $allEvaluatorData = shareForm::with('evaluator:id,nama_lengkap,divisi')
            ->where('id_evaluated', $form->id_karyawan)
            ->where('kode_form', $form->kode_form)
            ->get();

        $evaluatorList = [];
        foreach ($allEvaluatorData as $evaluatorItem) {
            $jenis_penilaian = $evaluatorItem->jenis_penilaian;
            $id_evaluator = $evaluatorItem->id_evaluator;

            $nilaiKPIByEvaluator = nilaiKPI::where('id_evaluated', $id_karyawan)
                ->where('kode_form', $kodeForm)
                ->where('id_evaluator', $id_evaluator)
                ->where('jenis_penilaian', $jenis_penilaian)
                ->get()
                ->groupBy('name_variabel');

            $listNilaiEvaluator = [];
            foreach ($allKategoriKPIs as $kategori) {
                $nilaiItem = $nilaiKPIByEvaluator->get($kategori->judul_kategori);
                if ($nilaiItem && $nilaiItem->count() > 0) {
                    $firstItem = $nilaiItem->first();
                    $listNilaiEvaluator[] = [
                        'pesan' => $firstItem->pesan ?? '-',
                        'nilai' => $firstItem->nilai ?? '-'
                    ];
                } else {
                    $listNilaiEvaluator[] = ['pesan' => '-', 'nilai' => '-'];
                }
            }

            $evaluatorList[] = [
                'nama'            => optional($evaluatorItem->evaluator)->nama_lengkap . ' - ' . (optional($evaluatorItem->evaluator)->divisi ?? '-'),
                'jenis_penilaian' => $evaluatorItem->jenis_penilaian ?? '-',
                'nilai'           => $listNilaiEvaluator
            ];
        }

        $evaluatorList = collect($evaluatorList)->unique(fn($item) => $item['nama'] . $item['jenis_penilaian'])->values();

        $dataKriteria = $formPenilaians->groupBy(fn($item) => $item->kode_form . '|' . $item->nama_penilaian)
            ->map(function ($groupedForms, $combinedKey) use ($allKategoriKPIs, $allTipeKategori) {
                [$kodeForm, $namaPenilaian] = explode('|', $combinedKey);
                $kategoris = $allKategoriKPIs->whereIn('kode_kategori', $groupedForms->pluck('kode_kategori'))->unique('judul_kategori')->values();

                $detailKriteria = $kategoris->map(function ($kategori) use ($allTipeKategori) {
                    $tipeDetails = $allTipeKategori->get($kategori->id, collect());
                    return [
                        'sub_kriteria' => $kategori->judul_kategori,
                        'bobot'        => $kategori->bobot,
                        'tipe_input'   => $kategori->tipe_kategori,
                        'detailTipeSubKriteria' => $tipeDetails->map(fn($tipe) => [
                            'ket_sub_tipe'       => $tipe->ket_tipe,
                            'nilai_ket_sub_tipe' => $tipe->nilai_ket_sub_tipe
                        ])->toArray()
                    ];
                });

                return [
                    'kriteria'       => $namaPenilaian,
                    'kodeForm'       => $kodeForm,
                    'detailKriteria' => $detailKriteria
                ];
            })->values()->toArray();

        return response()->json([
            'data' => [[
                'evaluated' => $evaluated,
                'dataAbsen' => $dataAbsen,
                'data'      => [
                    'evaluator'    => $evaluatorList,
                    'dataKriteria' => $dataKriteria,
                ],
            ]]
        ]);
    }

    public function getDetailChartPenilaian(Request $request)
    {
        $request->validate(['id_karyawan' => 'required|integer']);
        $id_karyawan = $request->input('id_karyawan');

        $allFormPenilaians = formPenilaian::where('id_karyawan', $id_karyawan)
            ->where('jenis_form', 'Rutin')
            ->get();

        if ($allFormPenilaians->isEmpty()) {
            return response()->json(['chartTahunan' => []]);
        }

        $uniqueFormGroups = $allFormPenilaians->unique(fn($item) => $item->tahun . '|' . $item->quartal . '|' . $item->kode_form)->values();
        $persentaseJenis = [
            'General Manager' => 35,
            'Manager/SPV/Team Leader (Atasan Langsung)' => 30,
            'Rekan Kerja (Satu Divisi)' => 20,
            'Pekerja (Beda Divisi)' => 10,
            'Self Apprisial' => 5,
        ];

        $skorPerTahun = [];
        $kodeForms = $uniqueFormGroups->pluck('kode_form')->unique();
        $allKategori = kategoriKPI::whereIn('kode_kategori', $allFormPenilaians->pluck('kode_kategori')->unique())
            ->where('tipe_kategori', '!=', 'textarea')
            ->get()
            ->keyBy('judul_kategori');

        $evaluators = shareForm::where('id_evaluated', $id_karyawan)
            ->whereIn('kode_form', $kodeForms)
            ->get()
            ->groupBy(fn($e) => $e->kode_form . '|' . $e->jenis_penilaian);

        $nilaiAll = nilaiKPI::where('id_evaluated', $id_karyawan)
            ->whereIn('kode_form', $kodeForms)
            ->where('status', '1')
            ->get()
            ->groupBy(fn($n) => $n->kode_form . '|' . $n->id_evaluator . '|' . $n->jenis_penilaian . '|' . $n->name_variabel);

        foreach ($uniqueFormGroups as $group) {
            $tahun    = (int) $group->tahun;
            $quartal  = $group->quartal;
            $kodeForm = $group->kode_form;

            $kategoriKpis = $allFormPenilaians->where('tahun', $tahun)
                ->where('quartal', $quartal)
                ->where('kode_form', $kodeForm)
                ->pluck('kode_kategori')
                ->unique();

            $filteredKategori = $allKategori->filter(fn($k) => $kategoriKpis->contains($k->kode_kategori));
            $evaluatorGroupForForm = $evaluators->get($kodeForm, collect())->groupBy('jenis_penilaian');

            $totalSkorAkhirKaryawan = 0;

            foreach ($evaluatorGroupForForm as $jenisPenilaian => $evaluatorItems) {
                $bobotJenis = $persentaseJenis[$jenisPenilaian] ?? 0;
                if ($bobotJenis === 0) continue;

                $skorJenisPenilaian = 0;
                $groupKeyBase = $kodeForm . '|' . $evaluatorItems->first()->id_evaluator . '|' . $jenisPenilaian;

                foreach ($filteredKategori as $judulKategori => $kategori) {
                    $groupKey = $groupKeyBase . '|' . $judulKategori;
                    $nilaiItems = $nilaiAll->get($groupKey, collect());
                    
                    $nilaiPerEvaluator = $nilaiItems->filter(fn($it) => is_numeric($it->nilai))->pluck('nilai');
                    $avgNilaiSub = $nilaiPerEvaluator->isNotEmpty() ? $nilaiPerEvaluator->avg() : 0;

                    $skorJenisPenilaian += $avgNilaiSub * ((float) $kategori->bobot / 100);
                }

                $totalSkorAkhirKaryawan += ($skorJenisPenilaian * $bobotJenis) / 100;
            }

            $skorPerTahun[$tahun][] = $totalSkorAkhirKaryawan;
        }

        $chartTahunan = [];
        foreach ($skorPerTahun as $tahun => $skorList) {
            $chartTahunan[$tahun] = number_format(array_sum($skorList) / count($skorList), 2, '.', '');
        }
        ksort($chartTahunan);

        return response()->json(['chartTahunan' => $chartTahunan]);
    }

    public function indexBerandaKpi()
    {
        $user = auth()->user();
        $year = date('Y');

        $criticalStats = Cache::remember("dashboard_critical_{$user->id}_{$year}", 60, function () use ($user, $year) {
            $isExecutive = in_array($user->jabatan, ['HRD', 'GM', 'Direktur Utama', 'Direktur']);
            $totalKaryawan = karyawan::where('status_aktif', '1')->whereNot('divisi', 'Direksi')->count();

            return [
                'karyawan_aktif' => $totalKaryawan,
            ];
        });

        return view('databasekpi.dashboard', compact('criticalStats'));
    }

    public function penilaianReview(Request $request)
    {
        $request->validate([
            'id_nilai.*' => 'required|integer',
            'nilai.*'    => 'required|integer',
        ]);

        // OPTIMASI: Bulk update lebih efisien daripada loop save()
        foreach ($request->id_nilai as $index => $id) {
            nilaiKPI::where('id', $id)->update([
                'nilai' => $request->nilai[$index],
                'status' => '1'
            ]);
        }

        return redirect()->back()->with('success', 'Review penilaian berhasil dikirim.');
    }

    public function detailPenilaian($kodeForm, $id_karyawan, $tipe)
    {
        return view('databasekpi.detailPenilaian', compact('kodeForm', 'id_karyawan', 'tipe'));
    }

    public function penilaianEvaluator(Request $request)
    {
        $id_evaluator = Auth::user()->karyawan->id;
        $kode_form = $request->input('kode_form');
        $id_evaluated = $request->input('id_evaluated');
        $jenis_penilaian = $request->input('jenis_penilaian');
        $tahun = $request->input('tahun');

        $sharedForm = shareForm::where('kode_form', $kode_form)
            ->where('id_evaluator', $id_evaluator)
            ->where('id_evaluated', $id_evaluated)
            ->where('jenis_penilaian', $jenis_penilaian)
            ->first();

        if (!$sharedForm) {
            return redirect()->back()->with('error', 'Data penilaian tidak valid.');
        }

        $formInfo = formPenilaian::where('kode_form', $kode_form)
            ->where('id_karyawan', $sharedForm->id_evaluated)
            ->where('tahun', $tahun)
            ->pluck('kode_kategori')
            ->toArray();

        if (empty($formInfo)) {
            return redirect()->back()->with('error', 'Data penilaian tidak valid.');
        }

        $isAlreadyRated = nilaiKPI::where('kode_form', $kode_form)
            ->where('id_evaluator', $id_evaluator)
            ->where('id_evaluated', $id_evaluated)
            ->where('jenis_penilaian', $jenis_penilaian)
            ->where(function ($q) {
                $q->whereNotNull('pesan')->orWhereNotNull('nilai');
            })
            ->exists();

        if ($isAlreadyRated) {
            return redirect()->back()->with('error', 'Anda sudah menilai form ini sebelumnya.');
        }

        $allFields = collect($request->all())->filter(function ($_, $key) {
            return Str::startsWith($key, 'field_') || Str::startsWith($key, 'teks_field_') || Str::startsWith($key, 'pesan_field_');
        });

        if ($allFields->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data yang dikirim.');
        }

        DB::beginTransaction();
        try {
            // OPTIMASI: Ambil semua record yang relevan SEKALI SAJA sebelum loop
            $existingNilaiRecords = nilaiKPI::where('kode_form', $kode_form)
                ->where('id_evaluator', $id_evaluator)
                ->where('id_evaluated', $id_evaluated)
                ->whereIn('kode_kategori', $formInfo)
                ->where('jenis_penilaian', $jenis_penilaian)
                ->get()
                ->groupBy('name_variabel');

            foreach ($allFields as $fieldKey => $fieldGroup) {
                foreach ($fieldGroup as $label => $value) {
                    $labelReadable = str_replace('_', ' ', $label);
                    $nilai = null;
                    $valueToSave = null;

                    if (Str::startsWith($fieldKey, 'teks_field_')) {
                        $valueToSave = $value;
                        $nilaiKey = str_replace('teks_field_', 'nilai_field_', $fieldKey);
                        if ($request->has($nilaiKey) && isset($request->input($nilaiKey)[$label])) {
                            $nilai = (int) $request->input($nilaiKey)[$label];
                        }
                    } elseif (Str::startsWith($fieldKey, 'pesan_field_')) {
                        $valueToSave = $value;
                        $nilai = null;
                    } else {
                        if (is_string($value) && (str_starts_with($value, '[') || str_starts_with($value, '{'))) {
                            $decoded = json_decode($value, true);
                            if (is_array($decoded)) {
                                $value = $decoded;
                            }
                        }

                        if (is_array($value) && collect($value)->every(fn($v) => is_numeric($v))) {
                            $nilai = array_sum(array_map('intval', $value));
                        } elseif (is_numeric($value)) {
                            $nilai = (int) $value;
                        }

                        $valueToSave = is_array($value) ? json_encode($value) : $value;
                    }

                    // OPTIMASI: Ambil dari collection yang sudah di-load, bukan query DB
                    $records = $existingNilaiRecords->get($labelReadable, collect());

                    foreach ($records as $record) {
                        $record->pesan = $valueToSave;
                        $record->nilai = $nilai;
                        $record->status = '1';
                        $record->finished_at = now();
                        $record->save();
                    }
                }
            }

            DB::commit();

            $allForms = shareForm::where('kode_form', $kode_form)
                ->where('id_evaluator', $id_evaluator)
                ->where('id_evaluated', $id_evaluated)
                ->orderBy('id')
                ->get();

            $currentIndex = $allForms->search(fn($item) => $item->jenis_penilaian === $jenis_penilaian);

            $completedCount = nilaiKPI::where('kode_form', $kode_form)
                ->where('id_evaluator', $id_evaluator)
                ->where('id_evaluated', $id_evaluated)
                ->whereNotNull('finished_at')
                ->distinct('jenis_penilaian')
                ->count();

            $evaluatedEmployee = Karyawan::find($id_evaluated);
            $evaluatedName = $evaluatedEmployee ? $evaluatedEmployee->nama_lengkap : 'Pegawai';

            if ($completedCount >= $allForms->count()) {
                return redirect()->route('penilaian.shareUser', ['id_evaluator' => $id_evaluator])
                    ->with('completed_all', true)
                    ->with('evaluated_name', $evaluatedName);
            } else {
                $nextIndex = min($currentIndex + 1, $allForms->count() - 1);
                return redirect()->route('penilaian.shareUser', [
                    'id_evaluator' => $id_evaluator,
                    'kode_form' => $kode_form,
                    'id_evaluated' => $id_evaluated,
                    'status' => 'lanjut',
                    'active_tab' => $nextIndex,
                ])->with('success', "Penilaian untuk {$evaluatedName} berhasil disimpan. Terima kasih atas penilaian Anda!");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage());
        }
    }

    public function getAveragePenilaian($kode_form, $id_evaluated)
    {
        return nilaiKPI::select('name_variabel', DB::raw('AVG(nilai) as average'))
            ->where('kode_form', $kode_form)
            ->where('id_evaluated', $id_evaluated)
            ->whereNotNull('nilai')
            ->groupBy('name_variabel')
            ->get();
    }

    public function reviewPenilaian($kodeForm, $evaluatorId, $jenis_penilaian, $idKaryawan)
    {
        $id_evaluator = $evaluatorId;
        $id_karyawan = $idKaryawan;

        $jenisMap = [
            'J01P' => 'General Manager',
            'J02P' => 'Manager/SPV/Team Leader (Atasan Langsung)',
            'J03P' => 'Rekan Kerja (Satu Divisi)',
            'J04P' => 'Pekerja (Beda Divisi)',
            'J05P' => 'Self Apprisial',
        ];
        $jenis_penilaian = $jenisMap[$jenis_penilaian] ?? 'not_found';

        $dataEvaluator = shareForm::where('kode_form', $kodeForm)
            ->where('id_evaluator', $id_evaluator)
            ->where('jenis_penilaian', $jenis_penilaian)
            ->first();

        if (!$dataEvaluator) {
            return response()->json(['status' => false, 'message' => 'Data evaluator tidak ditemukan.'], 404);
        }

        $dataForm = formPenilaian::where('kode_form', $kodeForm)
            ->where('id_karyawan', $id_karyawan)
            ->get();

        if ($dataForm->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'Data form penilaian tidak ditemukan.'], 404);
        }

        // OPTIMASI: Bulk fetch untuk menghindari N+1 di dalam loop
        $kodeKategoris = $dataForm->pluck('kode_kategori')->unique();
        $allKategori = kategoriKPI::whereIn('kode_kategori', $kodeKategoris)->get()->keyBy('id');
        $allTipeKategori = tipeKategoriTabel::whereIn('id_kategori', $allKategori->keys())->get()->groupBy('id_kategori');
        
        $allNilai = nilaiKPI::where('kode_form', $kodeForm)
            ->where('id_evaluator', $id_evaluator)
            ->where('id_evaluated', $id_karyawan)
            ->where('jenis_penilaian', $jenis_penilaian)
            ->get()
            ->keyBy(fn($n) => $n->kode_kategori . '|' . $n->name_variabel);

        $result = [];
        $statusForm = false;
        $statusPenilaian = false;

        foreach ($dataForm as $form) {
            $kategoriList = $allKategori->filter(fn($k) => $k->kode_kategori === $form->kode_kategori);

            foreach ($kategoriList as $kategori) {
                $tipeKategori = $allTipeKategori->get($kategori->id, collect());
                $nilaiKey = $kategori->kode_kategori . '|' . $kategori->judul_kategori;
                $nilai = $allNilai->get($nilaiKey);

                $statusPenilaian = is_null($nilai?->finished_at);
                $statusForm = $nilai !== null;

                $result[$form->nama_penilaian]['kriteria'] = $form->nama_penilaian;
                $result[$form->nama_penilaian]['items'][] = [
                    'judul'       => $kategori->judul_kategori,
                    'tipe'        => $kategori->tipe_kategori,
                    'bobot'       => $kategori->bobot,
                    'level'       => $kategori->level,
                    'opsi'        => $tipeKategori,
                    'id_nilaiKPI' => $nilai->id ?? null,
                    'pesan'       => $nilai->pesan ?? '-',
                    'nilai'       => $nilai->nilai ?? '-',
                ];
            }
        }

        $evaluator = Karyawan::find($id_evaluator);
        $evaluated = Karyawan::find($id_karyawan);

        return view('databasekpi.reviewPenilaian', [
            'statusPenilaian' => $statusPenilaian,
            'jenis_penilaian' => $jenis_penilaian,
            'status'          => $statusForm,
            'kode_form'       => $kodeForm,
            'evaluator'       => $evaluator,
            'evaluated'       => $evaluated,
            'penilaian'       => $result,
        ]);
    }

    public function shareForm(Request $request)
    {
        $request->validate([
            'id_karyawan'       => 'required|array',
            'id_karyawan.*'     => 'integer',
            'divisi'            => 'nullable|array',
            'divisi.*'          => 'string',
            'kode_form'         => 'required|string',
            'id_evaluated'      => 'required|integer',
            'jenis_penilaian'   => 'required|string',
            'jenis_form'        => 'required|string',
        ]);

        $id_evaluator_array = $request->input('id_karyawan');
        $divisi_array       = $request->input('divisi', []);
        $kode_form          = $request->input('kode_form');
        $id_evaluated       = $request->input('id_evaluated');
        $jenis_penilaian    = $request->input('jenis_penilaian');
        $jenis_form         = $request->input('jenis_form');

        $karyawanEvaluated = Karyawan::find($id_evaluated);
        $dataFormKategori = formPenilaian::where('kode_form', $kode_form)
            ->where('id_karyawan', $id_evaluated)
            ->where('jenis_form', $jenis_form)
            ->get();
        $dataKategori = kategoriKPI::whereIn('kode_kategori', $dataFormKategori->pluck('kode_kategori'))->get();

        $month = now()->month;
        $quarterLabel = match (true) {
            $month >= 1 && $month <= 3 => 'Q1',
            $month >= 4 && $month <= 6 => 'Q2',
            $month >= 7 && $month <= 9 => 'Q3',
            default => 'Q4',
        };

        // OPTIMASI: Ambil data existing SEKALI di awal untuk menghindari query di dalam loop
        $existingShares = shareForm::whereIn('id_evaluator', $id_evaluator_array)
            ->where('id_evaluated', $id_evaluated)
            ->where('kode_form', $kode_form)
            ->where('jenis_penilaian', $jenis_penilaian)
            ->whereMonth('created_at', $month)
            ->get()
            ->keyBy(fn($s) => $s->id_evaluator . '-' . $s->divisi_evaluator);

        $existingNilai = nilaiKPI::whereIn('id_evaluator', $id_evaluator_array)
            ->where('id_evaluated', $id_evaluated)
            ->where('kode_form', $kode_form)
            ->where('jenis_penilaian', $jenis_penilaian)
            ->whereMonth('created_at', $month)
            ->get()
            ->keyBy('id_evaluator');

        $processedPairs = [];

        foreach ($id_evaluator_array as $id_evaluator) {
            $karyawan = Karyawan::find($id_evaluator);
            if (!$karyawan) continue;

            $isGM = strtoupper($karyawan->jabatan) === 'GM' || $jenis_penilaian === 'General Manager';
            $divisiEvaluators = $isGM ? [$karyawan->divisi] : $divisi_array;

            if (empty($divisiEvaluators)) continue;

            foreach ($divisiEvaluators as $divisi) {
                $pairKey = $id_evaluator . '-' . $divisi;
                if (in_array($pairKey, $processedPairs)) continue;
                $processedPairs[] = $pairKey;

                $alreadyShared = $existingShares->has($pairKey);

                if ($alreadyShared) {
                    if (!(strtoupper($karyawan->jabatan) === 'GM' && in_array($jenis_penilaian, ['General Manager', 'Manager/SPV/Team Leader (Atasan Langsung)']))) {
                        continue;
                    }
                }

                shareForm::firstOrCreate([
                    'id_evaluator'     => $id_evaluator,
                    'divisi_evaluator' => $divisi,
                    'kode_form'        => $kode_form,
                    'id_evaluated'     => $id_evaluated,
                    'jenis_penilaian'  => $jenis_penilaian,
                ]);

                if (!$existingNilai->has($id_evaluator)) {
                    $insertData = [];
                    foreach ($dataKategori as $kategori) {
                        $insertData[] = [
                            'id_evaluator'    => $id_evaluator,
                            'id_evaluated'    => $id_evaluated,
                            'kode_form'       => $kode_form,
                            'kode_kategori'   => $kategori->kode_kategori,
                            'name_variabel'   => $kategori->judul_kategori,
                            'jenis_penilaian' => $jenis_penilaian,
                            'status'          => '0',
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ];
                    }
                    // OPTIMASI: Bulk insert
                    if (!empty($insertData)) {
                        nilaiKPI::insert($insertData);
                    }
                }

                $users = User::whereHas('karyawan', fn($q) => $q->where('karyawan_id', $karyawan->id))->get();
                foreach ($users as $user) {
                    $dummyComment = (object)[
                        'karyawan_key' => $karyawan->karyawan_id,
                        'content'      => $karyawan->nama_lengkap . ' dapat mengisi formulir PENILAIAN KINERJA 360 ' . strtoupper($karyawanEvaluated->nama_lengkap) . ' untuk ' . $quarterLabel,
                    ];
                    $url = url('getFormPenilaian/' . $kode_form . '/' . $id_evaluated);
                    Notification::send($user, new penilaianExcangheNotifikasi($dummyComment, $url, $user->id));
                }
            }
        }

        return redirect()->back()->with('success', 'Berhasil mengirim form, jangan lupa untuk review nantinya');
    }

    public function getEvaluatorsByForm(Request $request)
    {
        $request->validate(['kode_form' => 'required|string']);
        $kodeForm = $request->kode_form;

        // OPTIMASI: Eager load 'evaluated' juga untuk menghindari N+1
        $evaluators = shareForm::with(['evaluator:id,nama_lengkap,jabatan', 'evaluated:id,nama_lengkap'])
            ->where('kode_form', $kodeForm)
            ->get()
            ->map(function ($item) {
                return [
                    'id'                => $item->id,
                    'id_evaluator'      => $item->id_evaluator,
                    'nama_evaluator'    => optional($item->evaluator)->nama_lengkap ?? '-',
                    'jabatan'           => optional($item->evaluator)->jabatan ?? '-',
                    'divisi_evaluator'  => $item->divisi_evaluator,
                    'jenis_penilaian'   => $item->jenis_penilaian,
                    'id_evaluated'      => $item->id_evaluated,
                    'nama_evaluated'    => optional($item->evaluated)->nama_lengkap ?? '-',
                ];
            });

        $listDivisi = karyawan::select('divisi')
            ->whereNotNull('divisi')
            ->where('divisi', '!=', '')
            ->distinct()
            ->orderBy('divisi')
            ->pluck('divisi');

        return response()->json(['evaluators' => $evaluators, 'list_divisi' => $listDivisi]);
    }

    public function updateDivisiEvaluator(Request $request)
    {
        $request->validate([
            'id'               => 'required|integer|exists:share_forms,id',
            'divisi_evaluator' => 'required|string|max:100',
        ]);

        $share = shareForm::findOrFail($request->id);
        $share->divisi_evaluator = $request->divisi_evaluator;
        $share->save();

        return response()->json(['success' => true, 'message' => 'Divisi evaluator berhasil diperbarui']);
    }

    public function kategoriStore(Request $request)
    {
        $input = $request->all();

        if (!empty($input['kriteria']) && is_array($input['kriteria'])) {
            foreach ($input['kriteria'] as $i => $kriteria) {
                if (!empty($kriteria['sub_kriteria']) && is_array($kriteria['sub_kriteria'])) {
                    $cleanSub = [];
                    foreach ($kriteria['sub_kriteria'] as $sub) {
                        if (!empty($sub['judul_kategori']) && trim($sub['judul_kategori']) !== '') {
                            if (empty($sub['level'])) $sub['level'] = 'required';
                            $cleanSub[] = $sub;
                        }
                    }
                    if (!empty($cleanSub)) {
                        $input['kriteria'][$i]['sub_kriteria'] = $cleanSub;
                    } else {
                        unset($input['kriteria'][$i]);
                    }
                }
            }
        }

        $request->merge($input);

        $request->validate([
            'id_karyawan'                                 => 'required|array|min:1',
            'kriteria'                                    => 'required|array|min:1',
            'kriteria.*.nama_penilaian'                   => 'required|string|max:250',
            'kriteria.*.sub_kriteria'                     => 'required|array|min:1',
            'kriteria.*.sub_kriteria.*.judul_kategori'   => 'required|string|max:250',
            'kriteria.*.sub_kriteria.*.tipe_kategori'    => 'required|in:text,radio,checkbox,number,range,textarea,select',
            'kriteria.*.sub_kriteria.*.level'            => 'required|in:required,null',
            'kriteria.*.sub_kriteria.*.bobot'            => 'required|numeric|min:0',
            'kriteria.*.sub_kriteria.*.ket_tipe'         => 'nullable|array',
            'kriteria.*.sub_kriteria.*.ket_tipe.*'       => 'nullable|string|max:250',
            'kriteria.*.sub_kriteria.*.nilai_ket_tipe'   => 'nullable|array',
            'kriteria.*.sub_kriteria.*.nilai_ket_tipe.*' => 'nullable|string|max:250',
            'jenis_form'                                  => 'required|string|in:Rutin,Kontrak,Probation',
        ], [
            'kriteria.*.sub_kriteria.*.judul_kategori.required' => 'Sub kriteria harus diisi',
            'kriteria.*.sub_kriteria.*.bobot.numeric' => 'Bobot harus berupa angka',
        ]);

        $id_karyawan_array = $request->input('id_karyawan');
        $all_kriteria_data = $request->input('kriteria');
        $jenis_form = $request->input('jenis_form');

        $currentDate = now('Asia/Jakarta');
        $month = (int) $currentDate->month;
        $year = (int) $currentDate->year;

        $quarterLabel = match (true) {
            $month >= 1 && $month <= 3 => 'Q1',
            $month >= 4 && $month <= 6 => 'Q2',
            $month >= 7 && $month <= 9 => 'Q3',
            $month >= 10 && $month <= 12 => 'Q4',
            default => 'Q1',
        };

        do {
            $kodeFormPenilaian = 'FORM-' . now('Asia/Jakarta')->format('YmdHis') . '-' . Str::upper(Str::random(6));
        } while (formPenilaian::where('kode_form', $kodeFormPenilaian)->exists());

        DB::beginTransaction();
        try {
            foreach ($id_karyawan_array as $id_karyawan) {
                foreach ($all_kriteria_data as $kriteriaData) {
                    do {
                        $kodeKategori = 'KTG-' . now('Asia/Jakarta')->format('YmdHis') . '-' . Str::upper(Str::random(5));
                    } while (formPenilaian::where('kode_kategori', $kodeKategori)->exists());

                    $nama_penilaian_utama = $kriteriaData['nama_penilaian'];

                    $form = new formPenilaian();
                    $form->id_karyawan = $id_karyawan;
                    $form->kode_form = $kodeFormPenilaian;
                    $form->kode_kategori = $kodeKategori;
                    $form->nama_penilaian = $nama_penilaian_utama;
                    $form->quartal = $quarterLabel;
                    $form->tahun = $year;
                    $form->jenis_form = $jenis_form;
                    $form->save();

                    foreach ($kriteriaData['sub_kriteria'] as $subKriteriaData) {
                        $kategori = new kategoriKPI();
                        $kategori->judul_kategori = $subKriteriaData['judul_kategori'];
                        $kategori->tipe_kategori = $subKriteriaData['tipe_kategori'];
                        $kategori->level = $subKriteriaData['level'];
                        $kategori->bobot = $subKriteriaData['bobot'];
                        $kategori->kode_kategori = $kodeKategori;
                        $kategori->save();

                        if (in_array($subKriteriaData['tipe_kategori'], ['radio', 'select', 'checkbox'])) {
                            $ket_tipe_for_sub = $subKriteriaData['ket_tipe'] ?? [];
                            $nilai_ket_tipe_for_sub = $subKriteriaData['nilai_ket_tipe'] ?? [];

                            $insertTipe = [];
                            foreach ($ket_tipe_for_sub as $j => $ket) {
                                if (!is_null($ket) && $ket !== '' && isset($nilai_ket_tipe_for_sub[$j])) {
                                    $insertTipe[] = [
                                        'id_kategori' => $kategori->id,
                                        'ket_tipe' => $ket,
                                        'nilai_ket_tipe' => $nilai_ket_tipe_for_sub[$j],
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ];
                                }
                            }
                            if (!empty($insertTipe)) {
                                tipeKategoriTabel::insert($insertTipe);
                            }
                        }
                    }
                }
            }

            DB::commit();

            $queryAdminis = AdministrasiKaryawan::whereYear('created_at', $year)
                ->whereNotIn('status', ['selesai', 'terlambat'])
                ->orderBy('created_at', 'desc');
                
            if ($request->jenis_form === 'Rutin') {
                if (in_array($quarterLabel, ['Q1', 'Q2'])) {
                    $queryAdminis->where('nama_administrasi', 'like', '%Penilaian 360 Rutin Semester 1%');
                } else {
                    $queryAdminis->where('nama_administrasi', 'like', '%Penilaian 360 Rutin Semester 2%');
                }
            }

            $administrasi = $queryAdminis->first();
            if ($administrasi) {
                $administrasi->update([
                    'status' => 'selesai',
                    'tanggal_selesai' => now(),
                    'updated_at' => now()
                ]);
            }

            return back()->with('success', 'Berhasil disimpan');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error save form: ' . $e->getMessage());
            return back()->with('error', 'Gagal: ' . $e->getMessage())->withInput();
        }
    }

    public function getTemplateList()
    {
        $templates = formPenilaian::with('karyawan:id,nama_lengkap')
            ->select('kode_form', 'nama_penilaian', 'jenis_form', 'quartal', 'tahun', 'id_karyawan', 'created_at')
            ->distinct()
            ->orderBy('id_karyawan')
            ->orderBy('tahun', 'desc')
            ->orderBy('quartal', 'desc')
            ->get();

        $grouped = $templates->groupBy(function ($item) {
            return $item->karyawan ? $item->karyawan->nama_lengkap : 'Karyawan Tidak Ditemukan';
        })->map->values();

        return response()->json($grouped);
    }

    public function loadTemplate($kodeForm)
    {
        $forms = formPenilaian::where('kode_form', $kodeForm)->get();
        if ($forms->isEmpty()) {
            return response()->json(['error' => 'Template tidak ditemukan'], 404);
        }

        $firstForm = $forms->first();
        $kodeKategoriList = $forms->pluck('kode_kategori')->unique()->values();

        // OPTIMASI: Eager load relasi
        $subKriterias = kategoriKPI::whereIn('kode_kategori', $kodeKategoriList)
            ->with('tipeKategoriTabels')
            ->get();

        $data = [
            'jenis_form' => $firstForm->jenis_form,
            'quartal' => $firstForm->quartal,
            'tahun' => $firstForm->tahun,
            'nama_penilaian' => $firstForm->nama_penilaian,
            'nama_evaluator' => $firstForm->nama_evaluator ?? '',
            'tanggal' => $firstForm->tanggal ?? '',
            'catatan' => $firstForm->catatan ?? '',
            'kriteria' => []
        ];

        // OPTIMASI: Gunakan keyBy untuk O(1) lookup, bukan array_search di dalam loop
        $kriteriaMap = [];
        foreach ($subKriterias as $sub) {
            if (!isset($kriteriaMap[$sub->kode_kategori])) {
                $formWithCategory = $forms->firstWhere('kode_kategori', $sub->kode_kategori);
                $kriteriaMap[$sub->kode_kategori] = [
                    'kode_kategori' => $sub->kode_kategori,
                    'nama_penilaian' => $formWithCategory ? $formWithCategory->nama_penilaian : $firstForm->nama_penilaian,
                    'sub_kriteria' => []
                ];
            }

            $tipes = $sub->tipeKategoriTabels;
            $kriteriaMap[$sub->kode_kategori]['sub_kriteria'][] = [
                'id_kategori' => $sub->id,
                'judul_kategori' => $sub->judul_kategori,
                'tipe_kategori' => $sub->tipe_kategori,
                'bobot' => $sub->bobot,
                'level' => $sub->level,
                'ket_tipe' => $tipes->pluck('ket_tipe')->toArray(),
                'nilai_ket_tipe' => $tipes->pluck('nilai_ket_tipe')->toArray()
            ];
        }

        $data['kriteria'] = array_values($kriteriaMap);
        return response()->json($data);
    }

    public function getFromPenilaian(Request $request, $kode_form, $id_karyawan)
    {
        $evaluatedEmployee = Karyawan::find($id_karyawan);
        if (!$evaluatedEmployee) return redirect()->back();

        $id_evaluator = Auth::user()->karyawan->id;

        $sharedForms = shareForm::where('kode_form', $kode_form)
            ->where('id_evaluated', $id_karyawan)
            ->where('id_evaluator', $id_evaluator)
            ->get();

        if ($sharedForms->isEmpty()) {
            return view('databasekpi.formPenilaian', ['outputData' => [], 'evaluatedEmployee' => $evaluatedEmployee, 'isEvaluator' => false]);
        }

        $formPenilaians = formPenilaian::where('kode_form', $kode_form)
            ->where('id_karyawan', $id_karyawan)
            ->get();

        if ($formPenilaians->isEmpty()) {
            return view('databasekpi.formPenilaian', ['outputData' => [], 'evaluatedEmployee' => $evaluatedEmployee, 'isEvaluator' => false]);
        }

        // OPTIMASI: Ambil semua nilaiKPI yang relevan SEKALI di awal
        $allNilaiKPI = nilaiKPI::where('kode_form', $kode_form)
            ->where('id_evaluator', $id_evaluator)
            ->where('id_evaluated', $evaluatedEmployee->id)
            ->get()
            ->groupBy(fn($n) => $n->jenis_penilaian);

        $outputData = [];
        foreach ($sharedForms as $shared) {
            $pendingKategori = $allNilaiKPI->get($shared->jenis_penilaian, collect())
                ->where('status', 0)
                ->pluck('kode_kategori')
                ->unique();

            if ($pendingKategori->isEmpty()) {
                $existingData = $allNilaiKPI->get($shared->jenis_penilaian, collect())->isNotEmpty();
                if ($existingData) continue;
            }

            $temp = [
                'form_penilaian_id' => $formPenilaians->first()->id,
                'kode_form_global'  => $kode_form,
                'evaluator'         => Auth::user()->karyawan->nama_lengkap,
                'evaluated'         => $evaluatedEmployee->nama_lengkap,
                'id_karyawan'       => $id_karyawan,
                'jenis_penilaian'   => $shared->jenis_penilaian,
                'tahun'             => $formPenilaians->first()->tahun,
                'detail_kategori'   => [],
            ];

            foreach ($formPenilaians as $form) {
                if ($pendingKategori->isNotEmpty() && !$pendingKategori->contains($form->kode_kategori)) {
                    continue;
                }

                $kategoriKPIs = kategoriKPI::where('kode_kategori', $form->kode_kategori)
                    ->with('tipeKategoriTabels')
                    ->get();

                $isiKriteria = $kategoriKPIs->map(function ($kategori) {
                    return [
                        'sub_kriteria_id'    => $kategori->id,
                        'sub_kriteria_judul' => $kategori->judul_kategori,
                        'tipe_kategori'      => $kategori->tipe_kategori,
                        'bobot'              => $kategori->bobot,
                        'level'              => $kategori->level,
                        'keterangan_tipe'    => $kategori->tipeKategoriTabels->map(fn($tipe) => [
                            'id' => $tipe->id, 'ket' => $tipe->ket_tipe, 'nilai' => $tipe->nilai_ket_tipe
                        ])->toArray(),
                    ];
                })->toArray();

                $temp['detail_kategori'][] = [
                    'kriteria_utama'     => $form->nama_penilaian,
                    'isi_kriteria'       => $isiKriteria,
                    'kode_kategori_form' => $form->kode_kategori,
                ];
            }

            if (!empty($temp['detail_kategori'])) {
                $outputData[] = $temp;
            }
        }

        return view('databasekpi.formPenilaian', [
            'outputData'        => $outputData,
            'evaluatedEmployee' => $evaluatedEmployee,
            'isEvaluator'       => true
        ]);
    }

    public function getFromPenilaianUser(Request $request, $id_evaluator)
    {
        $evaluatorEmploye = Karyawan::find($id_evaluator);

        if (!$evaluatorEmploye) {
            return redirect()->back();
        }

        $currentYear = now('Asia/Jakarta')->year;

        $sharedForms = shareForm::where('id_evaluator', $id_evaluator)
            ->whereYear('created_at', $currentYear)
            ->get();

        $grouped = [];

        foreach ($sharedForms as $share) {
            $formPenilaians = formPenilaian::where('kode_form', $share->kode_form)
                ->where('id_karyawan', $share->id_evaluated)
                ->where('tahun', $currentYear)
                ->get();

            if ($formPenilaians->isEmpty()) {
                continue;
            }


            $evaluatedEmployee = Karyawan::find($share->id_evaluated);

            if (!$evaluatedEmployee) {
                continue;
            }

            $pendingKategori = nilaiKPI::where('kode_form', $share->kode_form)
                ->where('id_evaluator', $id_evaluator)
                ->where('id_evaluated', $evaluatedEmployee->id)
                ->where('jenis_penilaian', $share->jenis_penilaian)
                ->where('status', 0)
                ->pluck('kode_kategori')
                ->unique();

            if ($pendingKategori->isEmpty()) {
                $existingData = nilaiKPI::where('kode_form', $share->kode_form)
                    ->where('id_evaluator', $id_evaluator)
                    ->where('id_evaluated', $evaluatedEmployee->id)
                    ->where('jenis_penilaian', $share->jenis_penilaian)
                    ->exists();

                if ($existingData) {
                    continue;
                }
            }

            $key = $share->kode_form . '_' .
                $evaluatedEmployee->id . '_' .
                $id_evaluator . '_' .
                $share->jenis_penilaian . '_' .
                $currentYear;

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'form_penilaian_id' => $formPenilaians->first()->id,
                    'kode_form_global' => $share->kode_form,
                    'evaluator' => $evaluatorEmploye->nama_lengkap,
                    'evaluated' => $evaluatedEmployee->nama_lengkap,
                    'id_karyawan' => $evaluatedEmployee->id,
                    'jenis_penilaian' => $share->jenis_penilaian,
                    'tahun' => $currentYear,
                    'detail_kategori' => [],
                ];
            }

            foreach ($formPenilaians as $formItem) {
                if ($pendingKategori->isNotEmpty() && !$pendingKategori->contains($formItem->kode_kategori)) {
                    continue;
                }

                $kategoriKPIs = kategoriKPI::where('kode_kategori', $formItem->kode_kategori)
                    ->with('tipeKategoriTabels')
                    ->get();

                $isiKriteria = $kategoriKPIs->map(function ($kategori) {
                    return [
                        'sub_kriteria_id' => $kategori->id,
                        'sub_kriteria_judul' => $kategori->judul_kategori,
                        'tipe_kategori' => $kategori->tipe_kategori,
                        'bobot' => $kategori->bobot,
                        'level' => $kategori->level,
                        'keterangan_tipe' => $kategori->tipeKategoriTabels->map(function ($tipe) {
                            return [
                                'id' => $tipe->id,
                                'ket' => $tipe->ket_tipe,
                                'nilai' => $tipe->nilai_ket_tipe
                            ];
                        })->toArray(),
                    ];
                })->toArray();

                $exists = collect($grouped[$key]['detail_kategori'])
                    ->contains(fn($item) => $item['kode_kategori_form'] === $formItem->kode_kategori);

                if (!$exists) {
                    $grouped[$key]['detail_kategori'][] = [
                        'kriteria_utama' => $formItem->nama_penilaian,
                        'isi_kriteria' => $isiKriteria,
                        'kode_kategori_form' => $formItem->kode_kategori,
                        'jenis_penilaian' => $share->jenis_penilaian,
                    ];
                }
            }
        }

        return view('databasekpi.formPenilaian', [
            'outputData' => array_values($grouped),
            'evaluatorEmploye' => $evaluatorEmploye,
            'isEvaluator' => true
        ]);
    }

    public function createKategori()
    {
        $data = karyawan::all();
        return view('databasekpi.formKateori', compact('data'));
    }

    public function getData()
    {
        $dataKaryawan = karyawan::all();
        $jumlah = $dataKaryawan->count();

        $data = $dataKaryawan->map(function ($karyawan) {
            $status = 'Tidak Diketahui';
            if ($karyawan->status_aktif === '1') $status = 'Karyawan Aktif';
            elseif ($karyawan->status_aktif === '0') $status = 'Karyawan Non Aktif';

            return [
                'nama_lengkap' => $karyawan->nama_lengkap ?? '-',
                'nip'          => $karyawan->nip ?? '-',
                'divisi'       => $karyawan->divisi ?? '-',
                'jabatan'      => $karyawan->jabatan ?? '-',
                'status'       => $status,
            ];
        });

        return response()->json(['jumlah' => $jumlah, 'data' => $data]);
    }

    public function getDataPenilaian()
    {
        $user_id = Auth::user()->id;
        $filterTahun = request()->get('tahun');
        $filterDivisi = request()->get('divisi');
        $jenisForm = request()->get('jenis_form');

        $dataFormPenilaianCollection = formPenilaian::with('karyawan:id,nama_lengkap,divisi')
            ->when($filterTahun, fn($q) => $q->where('tahun', $filterTahun))
            ->where('jenis_form', $jenisForm)
            ->get();

        $allKodeForms = $dataFormPenilaianCollection->pluck('kode_form')->unique()->values();
        $kodeFormMapping = [];
        foreach ($allKodeForms as $index => $kode) {
            $kodeFormMapping[$kode] = 'PK-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
        }

        $dataKaryawan = karyawan::where('status_aktif', '1')->get();
        $groupedOutputData = [];

        // OPTIMASI: Bulk fetch semua data relasi untuk menghindari N+1 masif
        $kodeKategoris = $dataFormPenilaianCollection->pluck('kode_kategori')->unique();
        $allKategoriKPIs = kategoriKPI::whereIn('kode_kategori', $kodeKategoris)->with('tipeKategoriTabels')->get()->groupBy('kode_kategori');
        
        $allShareForms = shareForm::with('evaluator:id,nama_lengkap')
            ->whereIn('id_evaluated', $dataFormPenilaianCollection->pluck('id_karyawan')->unique())
            ->whereIn('kode_form', $allKodeForms)
            ->get();
            
        $allNilaiKPI = nilaiKPI::whereIn('id_evaluated', $dataFormPenilaianCollection->pluck('id_karyawan')->unique())
            ->whereIn('kode_form', $allKodeForms)
            ->get();

        foreach ($dataFormPenilaianCollection as $formPenilaian) {
            if ($filterDivisi && $formPenilaian->karyawan->divisi !== $filterDivisi) continue;

            $evaluatedName = $formPenilaian->karyawan->nama_lengkap;
            $evaluatedDivisi = $formPenilaian->karyawan->divisi;
            $tahun = $formPenilaian->tahun;
            $kriteriaNama = $formPenilaian->nama_penilaian;
            $kodeFormGlobal = $formPenilaian->kode_form;

            $dataEvaluator = $allShareForms->where('id_evaluated', $formPenilaian->id_karyawan)
                ->where('kode_form', $kodeFormGlobal);

            $evaluatorNamesList = [];
            $evaluatorGroupedByJenis = [];
            $evaluatorIds = [];

            foreach ($dataEvaluator as $evaluatorRow) {
                $evaluatorId = $evaluatorRow->id_evaluator;
                $evaluatorName = optional($evaluatorRow->evaluator)->nama_lengkap ?? '-';
                $jenisPenilaian = $evaluatorRow->jenis_penilaian;

                $nilai = $allNilaiKPI->first(fn($n) => 
                    $n->kode_form === $kodeFormGlobal &&
                    $n->id_evaluated === $formPenilaian->id_karyawan &&
                    $n->id_evaluator === $evaluatorId &&
                    $n->jenis_penilaian === $jenisPenilaian
                );

                $isRed = $nilai && ($nilai->finished_at == null || $nilai->status == 0);
                $evaluatorData = ['id' => $evaluatorId, 'name' => $evaluatorName, 'is_red' => $isRed];

                $evaluatorNamesList[] = $evaluatorData;
                $evaluatorIds[] = $evaluatorId;

                if ($jenisPenilaian) {
                    if (!isset($evaluatorGroupedByJenis[$jenisPenilaian])) {
                        $evaluatorGroupedByJenis[$jenisPenilaian] = [];
                    }
                    $evaluatorGroupedByJenis[$jenisPenilaian][] = $evaluatorData;
                }
            }

            $evaluatorIds = array_unique($evaluatorIds);
            $jenisPenilaianList = array_keys($evaluatorGroupedByJenis);

            $groupKey = $kodeFormGlobal . '_' . $evaluatedName;
            $records = $allNilaiKPI->where('kode_form', $kodeFormGlobal)->where('id_evaluated', $formPenilaian->id_karyawan);

            $status = $records->isNotEmpty() && $records->every(fn($record) => is_null($record->pesan));

            if (!isset($groupedOutputData[$groupKey])) {
                $groupedOutputData[$groupKey] = [
                    'form_penilaian_id'  => $formPenilaian->id,
                    'kode_form'          => $kodeFormGlobal,
                    'kode_form_label'    => $kodeFormMapping[$formPenilaian->kode_form] ?? $formPenilaian->kode_form,
                    'id_karyawan'        => $formPenilaian->id_karyawan,
                    'evaluated'          => $evaluatedName,
                    'evaluatedDivisi'    => $evaluatedDivisi,
                    'tanggal'            => $formPenilaian->created_at->translatedFormat('l, d F Y'),
                    'tahun'              => $tahun,
                    'jenis_penilaian'    => $jenisPenilaianList,
                    'evaluator'          => $evaluatorNamesList,
                    'evaluator_by_jenis' => $evaluatorGroupedByJenis,
                    'id_evaluator'       => $evaluatorIds,
                    'detail_kategori'    => [],
                    'status'             => $status,
                ];
            }

            $kategoriKPIs = $allKategoriKPIs->get($formPenilaian->kode_kategori, collect());
            $isiKriteria = [];

            foreach ($kategoriKPIs as $kategori) {
                $nilaiRecords = $allNilaiKPI->where('kode_form', $kodeFormGlobal)
                    ->where('kode_kategori', $kategori->kode_kategori)
                    ->where('name_variabel', $kategori->judul_kategori)
                    ->where('id_evaluated', $formPenilaian->id_karyawan)
                    ->whereIn('id_evaluator', $evaluatorIds);

                $filteredRecords = $nilaiRecords->filter(fn($record) => !is_null($record->nilai));
                $totalNilai = $filteredRecords->sum('nilai');
                $nilaiFinal = $filteredRecords->isNotEmpty() ? $totalNilai : '-';
                $nilai_akhir = $filteredRecords->isNotEmpty() ? round(($totalNilai * ((float) $kategori->bobot)) / 100, 2) : '-';

                $isiKriteria[] = [
                    'sub_kriteria_id'    => $kategori->id,
                    'sub_kriteria_judul' => $kategori->judul_kategori,
                    'tipe_kategori'      => $kategori->tipe_kategori,
                    'bobot'              => $kategori->bobot,
                    'skor'               => $nilaiFinal,
                    'nilai_akhir'        => $nilai_akhir,
                    'tanggal'            => $kategori->created_at->translatedFormat('l, d F Y'),
                    'keterangan_tipe'    => $kategori->tipeKategoriTabels->map(fn($tipe) => [
                        'id' => $tipe->id, 'ket' => $tipe->ket_tipe, 'nilai' => $tipe->nilai_ket_tipe
                    ])->toArray(),
                ];
            }

            $groupedOutputData[$groupKey]['detail_kategori'][] = [
                'kriteria_utama'     => $kriteriaNama,
                'isi_kriteria'       => $isiKriteria,
                'kode_kategori_form' => $formPenilaian->kode_kategori,
            ];
        }

        return response()->json([
            'data'     => array_values($groupedOutputData),
            'karyawan' => $dataKaryawan
        ]);
    }

    public function index360($id_karyawan)
    {
        return view('databasekpi.penilaian360', compact('id_karyawan'));
    }

    public function get360($id_karyawan, Request $request)
    {
        $selectedTahun = (int) $request->query('tahun', now()->year);

        $allForms = formPenilaian::with('karyawan:id,nama_lengkap')
            ->where('id_karyawan', $id_karyawan)
            ->get();

        if ($allForms->isEmpty()) {
            return response()->json(['message' => 'Kosong']);
        }

        $groupedByTahun = $allForms->groupBy('tahun')->sortKeysDesc();
        $listPeriode = $groupedByTahun->map(fn($items, $tahun) => [
            'tahun' => $tahun,
            'label' => 'Periode Tahun ' . $tahun
        ])->values();

        if (!$selectedTahun || !$groupedByTahun->has($selectedTahun)) {
            $selectedTahun = $groupedByTahun->keys()->first();
        }

        $formPenilaian = $groupedByTahun[$selectedTahun];
        $catatan = $formPenilaian->pluck('catatan')->unique();
        $dataAbsen = $this->getDataAbsen($id_karyawan, $selectedTahun);

        $kodeFormList = $formPenilaian->pluck('kode_form');
        $kodeKategoriList = $formPenilaian->pluck('kode_kategori');

        // OPTIMASI: Bulk fetch
        $dataKriteria = kategoriKPI::whereIn('kode_kategori', $kodeKategoriList)->get()->groupBy('kode_kategori');
        $allShareForm = shareForm::with('evaluator:id,nama_lengkap')
            ->where('id_evaluated', $id_karyawan)
            ->whereIn('kode_form', $kodeFormList)
            ->get()
            ->groupBy('jenis_penilaian');
            
        $allNilaiKPI = nilaiKPI::where('id_evaluated', $id_karyawan)
            ->whereIn('kode_form', $kodeFormList)
            ->get()
            ->groupBy(fn($n) => $n->id_evaluator . '|' . $n->kode_form . '|' . $n->kode_kategori . '|' . $n->name_variabel);

        $allJenisPenilaian = [];
        foreach ($allShareForm as $jenis => $evaluators) {
            $dataEvaluators = [];
            foreach ($evaluators as $evaluator) {
                $dataKriteriaArray = [];
                $groupedKriteriaForForm = $dataKriteria->filter(fn($k, $kk) => 
                    $formPenilaian->contains('kode_kategori', $kk)
                );

                foreach ($groupedKriteriaForForm as $kode_kategori => $subKriterias) {
                    $kriteriaNama = $formPenilaian->firstWhere('kode_kategori', $kode_kategori)?->nama_penilaian ?? '-';
                    $subKriteriaArray = [];
                    
                    foreach ($subKriterias as $kriteria) {
                        $nilaiKey = $evaluator->id_evaluator . '|' . $evaluator->kode_form . '|' . $kode_kategori . '|' . $kriteria->judul_kategori;
                        $nilai = $allNilaiKPI->get($nilaiKey)?->first();

                        $subKriteriaArray[] = [
                            'subKriteria' => $kriteria->judul_kategori,
                            'bobot' => $kriteria->bobot,
                            'deskripsi' => $nilai->pesan ?? null,
                            'nilai' => $nilai->nilai ?? null
                        ];
                    }

                    $dataKriteriaArray[] = [
                        'kriteria' => $kriteriaNama,
                        'subKriteria' => $subKriteriaArray
                    ];
                }

                $dataEvaluators[] = [
                    'nama_evaluator' => $evaluator->evaluator->nama_lengkap ?? 'Tidak ditemukan',
                    'kriteria' => $dataKriteriaArray
                ];
            }

            $allJenisPenilaian[] = [
                'jenis_penilaian' => $jenis,
                'evaluator' => $dataEvaluators
            ];
        }

        return response()->json([
            'nama_evaluated' => $formPenilaian->pluck('karyawan.nama_lengkap')->unique()->values(),
            'tahun' => $selectedTahun,
            'data' => $allJenisPenilaian,
            'dataAbsen' => $dataAbsen,
            'catatan' => $catatan,
            'listPeriode' => $listPeriode
        ]);
    }

    public function clean(Request $request)
    {
        $kode_form = $request->input('kode_form');
        $id_karyawan = $request->input('id_karyawan');

        $data_evaluated = formPenilaian::where('kode_form', $kode_form)
            ->where('id_karyawan', $id_karyawan)
            ->select('kode_kategori', 'kode_form')
            ->get();

        if ($data_evaluated->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'Data penilaian tidak ditemukan.']);
        }

        $kodeKategoriList = $data_evaluated->pluck('kode_kategori')->unique();
        $kodeFormList = $data_evaluated->pluck('kode_form')->unique();

        $idEvaluators = shareForm::where('id_evaluated', $id_karyawan)
            ->whereIn('kode_form', $kodeFormList)
            ->pluck('id_evaluator');

        if ($idEvaluators->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'Gagal membersihkan, evaluator tidak ditemukan.']);
        }

        // OPTIMASI: Langsung delete tanpa get() terlebih dahulu
        $deletedNilai = nilaiKPI::whereIn('id_evaluator', $idEvaluators)
            ->where('id_evaluated', $id_karyawan)
            ->whereIn('kode_form', $kodeFormList)
            ->whereIn('kode_kategori', $kodeKategoriList)
            ->delete();

        $deletedShare = shareForm::whereIn('id_evaluator', $idEvaluators)
            ->where('id_evaluated', $id_karyawan)
            ->whereIn('kode_form', $kodeFormList)
            ->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil membersihkan penilaian!',
            'deleted_nilai' => $deletedNilai,
            'deleted_share' => $deletedShare
        ]);
    }

    public function getFormPenilaianData(Request $request)
    {
        $tahun = $request->get('tahun');
        $query = formPenilaian::with('karyawan:id,nama_lengkap,divisi');

        if ($tahun) {
            $query->where('tahun', $tahun);
        }

        $formPenilaian = $query->get();
        if ($formPenilaian->isEmpty()) {
            return response()->json(['data' => []]);
        }

        $uniqueKodeForms = $formPenilaian->pluck('kode_form')->unique()->values();
        $kodeFormMapping = [];
        foreach ($uniqueKodeForms as $i => $kode) {
            $kodeFormMapping[$kode] = 'PK-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT);
        }

        $grouped = $formPenilaian->groupBy('kode_form')->map(function ($items, $kodeForm) use ($kodeFormMapping) {
            $first = $items->first();
            $evaluated = $items->map(function ($form) {
                return [
                    'id_karyawan' => $form->id_karyawan,
                    'nama'        => optional($form->karyawan)->nama_lengkap . ' - ' . (optional($form->karyawan)->divisi ?? '-'),
                    'tahun'       => $form->tahun,
                    'catatan'     => $form->catatan,
                ];
            })->unique('id_karyawan')->values();

            return [
                'kode_form'       => $kodeForm,
                'label_kode_form' => $kodeFormMapping[$kodeForm] ?? $kodeForm,
                'tahun'           => $first->tahun,
                'evaluated'       => $evaluated
            ];
        })->values();

        return response()->json(['data' => $grouped]);
    }

    public function formPenilaianUpdate(Request $request)
    {
        $validated = $request->validate([
            'kode_form'                                   => 'required|string|max:50',
            'jenis_form'                                  => 'nullable|string|max:50',
            'kriteria'                                    => 'required|array|min:1',
            'kriteria.*.id_nama_penilaian'                => 'nullable|integer|exists:form_penilaians,id',
            'kriteria.*.nama_penilaian'                   => 'required|string',
            'kriteria.*.sub_kriteria'                     => 'nullable|array|min:1',
            'kriteria.*.sub_kriteria.*.id_judul_kategori' => 'nullable|integer|exists:kategori_k_p_i_s,id',
            'kriteria.*.sub_kriteria.*.judul_kategori'    => 'required|string',
            'kriteria.*.sub_kriteria.*.tipe_kategori'     => 'required|string',
            'kriteria.*.sub_kriteria.*.level'             => 'required|string',
            'kriteria.*.sub_kriteria.*.bobot'             => 'required|numeric',
            'kriteria.*.sub_kriteria.*.ket_tipe'          => 'nullable|array',
            'kriteria.*.sub_kriteria.*.ket_tipe.*'        => 'nullable|string',
            'kriteria.*.sub_kriteria.*.nilai_ket_tipe'    => 'nullable|array',
            'kriteria.*.sub_kriteria.*.nilai_ket_tipe.*'  => 'nullable|string',
        ]);

        $kodeForm = $validated['kode_form'];
        $jenis_form = $validated['jenis_form'];
        
        if (shareForm::where('kode_form', $kodeForm)->exists()) {
            return redirect()->back()->with('error', 'Jangan lupa dibersihkan terlebih dahulu');
        }

        DB::beginTransaction();
        try {
            $idKaryawanList = formPenilaian::where('kode_form', $kodeForm)->pluck('id_karyawan')->unique();
            $dataQuartalDanTahun = formPenilaian::where('kode_form', $kodeForm)->select('quartal', 'tahun', 'jenis_form')->first();
            $existingForms = formPenilaian::where('kode_form', $kodeForm)->get();
            
            $inputFormIds = collect($validated['kriteria'])->pluck('id_nama_penilaian')->filter()->toArray();
            $formsToDelete = $existingForms->filter(fn($form) => !in_array($form->id, $inputFormIds));

            foreach ($formsToDelete as $form) {
                $kategoriIds = kategoriKPI::where('kode_kategori', $form->kode_kategori)->pluck('id')->toArray();
                tipeKategoriTabel::whereIn('id_kategori', $kategoriIds)->delete();
                kategoriKPI::whereIn('id', $kategoriIds)->delete();
                $form->delete();
            }

            foreach ($validated['kriteria'] as $krit) {
                $namaPenilaian = $krit['nama_penilaian'];
                $subKriteriaInput = $krit['sub_kriteria'] ?? [];

                if (!empty($krit['id_nama_penilaian'])) {
                    $formRef = formPenilaian::find($krit['id_nama_penilaian']);
                    if ($formRef) {
                        $formsToUpdate = formPenilaian::where('nama_penilaian', $formRef->nama_penilaian)
                            ->where('kode_form', $kodeForm)
                            ->get();

                        foreach ($formsToUpdate as $form) {
                            $form->update(['nama_penilaian' => $namaPenilaian, 'jenis_form' => $jenis_form]);

                            $existingKategoriIds = kategoriKPI::where('kode_kategori', $form->kode_kategori)->pluck('id')->toArray();
                            $requestKategoriIds = [];

                            foreach ($subKriteriaInput as $sub) {
                                if (!empty($sub['id_judul_kategori'])) {
                                    $requestKategoriIds[] = $sub['id_judul_kategori'];
                                    $kategori = kategoriKPI::find($sub['id_judul_kategori']);
                                    if ($kategori) {
                                        $kategori->update([
                                            'judul_kategori' => $sub['judul_kategori'],
                                            'tipe_kategori'  => $sub['tipe_kategori'],
                                            'bobot'          => $sub['bobot'],
                                            'level'          => $sub['level'],
                                        ]);
                                    }
                                } else {
                                    $kategori = kategoriKPI::create([
                                        'judul_kategori' => $sub['judul_kategori'],
                                        'tipe_kategori'  => $sub['tipe_kategori'],
                                        'bobot'          => $sub['bobot'],
                                        'level'          => $sub['level'],
                                        'kode_kategori'  => $form->kode_kategori,
                                    ]);
                                    $requestKategoriIds[] = $kategori->id;
                                }

                                if (in_array($sub['tipe_kategori'], ['radio', 'checkbox', 'select'])) {
                                    $ket_tipe_list = $sub['ket_tipe'] ?? [];
                                    $nilai_list    = $sub['nilai_ket_tipe'] ?? [];
                                    tipeKategoriTabel::where('id_kategori', $kategori->id)->delete();
                                    
                                    $insertTipe = [];
                                    foreach ($ket_tipe_list as $i => $ket) {
                                        if (!is_null($ket) && $ket !== '') {
                                            $insertTipe[] = [
                                                'id_kategori'    => $kategori->id,
                                                'ket_tipe'       => $ket,
                                                'nilai_ket_tipe' => $nilai_list[$i] ?? null,
                                                'created_at'     => now(),
                                                'updated_at'     => now(),
                                            ];
                                        }
                                    }
                                    if (!empty($insertTipe)) {
                                        tipeKategoriTabel::insert($insertTipe);
                                    }
                                }
                            }

                            $toDelete = array_diff($existingKategoriIds, $requestKategoriIds);
                            if (!empty($toDelete)) {
                                tipeKategoriTabel::whereIn('id_kategori', $toDelete)->delete();
                                kategoriKPI::whereIn('id', $toDelete)->delete();
                            }
                        }
                    }
                } else {
                    foreach ($idKaryawanList as $idKaryawan) {
                        $kodeKategori = Str::random(15);
                        $form = formPenilaian::create([
                            'id_karyawan'    => $idKaryawan,
                            'kode_form'      => $kodeForm,
                            'jenis_form'     => $jenis_form,
                            'kode_kategori'  => $kodeKategori,
                            'nama_penilaian' => $namaPenilaian,
                            'quartal'        => $dataQuartalDanTahun ? $dataQuartalDanTahun->quartal : 'Q1',
                            'tahun'          => $dataQuartalDanTahun ? $dataQuartalDanTahun->tahun : date('Y'),
                            'catatan'        => null,
                        ]);

                        foreach ($subKriteriaInput as $sub) {
                            $kategori = kategoriKPI::create([
                                'judul_kategori' => $sub['judul_kategori'],
                                'tipe_kategori'  => $sub['tipe_kategori'],
                                'bobot'          => $sub['bobot'],
                                'level'          => $sub['level'],
                                'kode_kategori'  => $kodeKategori,
                            ]);

                            if (in_array($sub['tipe_kategori'], ['radio', 'checkbox', 'select'])) {
                                $ket_tipe_list = $sub['ket_tipe'] ?? [];
                                $nilai_list    = $sub['nilai_ket_tipe'] ?? [];
                                
                                $insertTipe = [];
                                foreach ($ket_tipe_list as $i => $ket) {
                                    if (!is_null($ket) && $ket !== '') {
                                        $insertTipe[] = [
                                            'id_kategori'    => $kategori->id,
                                            'ket_tipe'       => $ket,
                                            'nilai_ket_tipe' => $nilai_list[$i] ?? null,
                                            'created_at'     => now(),
                                            'updated_at'     => now(),
                                        ];
                                    }
                                }
                                if (!empty($insertTipe)) {
                                    tipeKategoriTabel::insert($insertTipe);
                                }
                            }
                        }
                    }
                }
            }

            DB::commit();
            return back()->with('success', 'Berhasil update data.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal update  ' . $e->getMessage());
        }
    }

    public function formPenilaianData()
    {
        return view('databasekpi.dataFormPenilaian');
    }

    public function formPenilaianEdit($kode_form)
    {
        if (shareForm::where('kode_form', $kode_form)->exists()) {
            return back()->with('error', 'Jangan lupa dibersihkan terlebih dahulu');
        }

        $allFormIds = formPenilaian::where('kode_form', $kode_form)->pluck('id')->toArray();
        $jenis_form = formPenilaian::where('kode_form', $kode_form)->value('jenis_form');

        $formPenilaianUnique = formPenilaian::where('kode_form', $kode_form)
            ->get()
            ->unique('nama_penilaian')
            ->values();

        // OPTIMASI: Bulk fetch
        $kodeKategoris = $formPenilaianUnique->pluck('kode_kategori')->unique();
        $allKategori = kategoriKPI::whereIn('kode_kategori', $kodeKategoris)->get()->keyBy('id');
        $allTipe = tipeKategoriTabel::whereIn('id_kategori', $allKategori->keys())->get()->groupBy('id_kategori');

        $result = [];
        foreach ($formPenilaianUnique as $data) {
            $kategori = $allKategori->filter(fn($k) => $k->kode_kategori === $data->kode_kategori);
            $kategoriArr = [];
            
            foreach ($kategori as $itemSub) {
                $dataTipeKategori = $allTipe->get($itemSub->id, collect());
                $tipeKategoriAll = $dataTipeKategori->map(fn($item) => [
                    'id'              => $item->id,
                    'keterangan_tipe' => $item->ket_tipe,
                    'nilai_ket_tipe'  => $item->nilai_ket_tipe,
                ])->toArray();

                $kategoriArr[] = [
                    'id_kategori'         => $itemSub->id,
                    'judul_kategori'      => $itemSub->judul_kategori,
                    'tipe_kategori'       => $itemSub->tipe_kategori,
                    'bobot'               => $itemSub->bobot,
                    'level'               => $itemSub->level,
                    'dataTipeKeterangan'  => $tipeKategoriAll
                ];
            }

            $result[] = [
                'id_formPenilaian' => $data->id,
                'nama_penilaian'   => $data->nama_penilaian,
                'kategori'         => $kategoriArr
            ];
        }

        // ===== PERBAIKAN DI SINI =====
        $data = [
            'result'      => $result,
            'jenis_form'  => $jenis_form,
            'kode_form'   => $kode_form,
            'allFormIds'  => $allFormIds,
        ];

        return view('databasekpi.formEditPenilaian', compact('data'));
    }

    public function hapus(Request $request)
    {
        $kode_form = $request->input('kode_form');
        $id_karyawan = $request->input('id_karyawan');

        $data_evaluated = formPenilaian::where('kode_form', $kode_form)
            ->where('id_karyawan', $id_karyawan)
            ->select('kode_kategori', 'kode_form')
            ->get();

        if ($data_evaluated->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'Data penilaian tidak ditemukan.']);
        }

        $kodeKategoriList = $data_evaluated->pluck('kode_kategori')->unique();
        $kodeFormList = $data_evaluated->pluck('kode_form')->unique();

        $idEvaluators = shareForm::where('id_evaluated', $id_karyawan)
            ->whereIn('kode_form', $kodeFormList)
            ->pluck('id_evaluator');

        $deletedNilai = 0;
        $deletedShare = 0;

        if ($idEvaluators->isNotEmpty()) {
            $deletedNilai = nilaiKPI::whereIn('id_evaluator', $idEvaluators)
                ->where('id_evaluated', $id_karyawan)
                ->whereIn('kode_form', $kodeFormList)
                ->whereIn('kode_kategori', $kodeKategoriList)
                ->delete();

            $deletedShare = shareForm::whereIn('id_evaluator', $idEvaluators)
                ->where('id_evaluated', $id_karyawan)
                ->whereIn('kode_form', $kodeFormList)
                ->delete();
        }

        formPenilaian::where('kode_form', $kode_form)->where('id_karyawan', $id_karyawan)->delete();

        foreach ($kodeKategoriList as $kodeKategori) {
            $masihDipakai = formPenilaian::where('kode_kategori', $kodeKategori)->exists();
            if (!$masihDipakai) {
                $kategori = kategoriKPI::where('kode_kategori', $kodeKategori)->first();
                if ($kategori) {
                    tipeKategoriTabel::where('id_kategori', $kategori->id)->delete();
                    $kategori->delete();
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil menghapus penilaian!',
            'deleted_nilai' => $deletedNilai,
            'deleted_share' => $deletedShare
        ]);
    }

    public function hapusEvaluator($kodeJenis, $id_evaluator, $kodeFormGlobal)
    {
        $jenisMap = [
            'JP01' => 'General Manager',
            'JP02' => 'Manager/SPV/Team Leader (Atasan Langsung)',
            'JP03' => 'Rekan Kerja (Satu Divisi)',
            'JP04' => 'Pekerja (Beda Divisi)',
            'JP05' => 'Self Apprisial',
        ];

        $jenisPenilaian = $jenisMap[$kodeJenis] ?? null;
        if (!$jenisPenilaian) {
            return response()->json(['status' => 'error', 'message' => 'Kode jenis penilaian tidak valid: ' . $kodeJenis], 400);
        }

        $deletedShare = shareForm::where('kode_form', $kodeFormGlobal)
            ->where('jenis_penilaian', $jenisPenilaian)
            ->where('id_evaluator', $id_evaluator)
            ->delete();

        $deletedNilai = nilaiKPI::where('kode_form', $kodeFormGlobal)
            ->where('id_evaluator', $id_evaluator)
            ->where('jenis_penilaian', $jenisPenilaian)
            ->delete();

        return response()->json([
            'status'        => 'success',
            'message'       => 'Berhasil menghapus evaluator!',
            'deleted_nilai' => $deletedNilai,
            'deleted_share' => $deletedShare
        ]);
    }

    public function contentDashboard(\App\Services\KPI\Dashboard\OverviewDashboardService $overviewService)
    {
        $year = date('Y');
        $user = auth()->user();

        $cacheKey = "dashboard_content_{$user->id}_{$year}";
        $payload = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($overviewService, $year, $user) {

            $isExecutive = in_array($user->jabatan, ['HRD', 'GM', 'Direktur Utama', 'Koordinator ITSM']);

            $totalKaryawan = karyawan::where('status_aktif', '1')->whereNot('divisi', 'Direksi')->count();

            $applyUserFilter = function ($query) use ($isExecutive, $user) {
                if (!$isExecutive) $query->where('id_karyawan', $user->id);
                return $query;
            };

            $AbsenCuti = $applyUserFilter(pengajuancuti::with('karyawan')
                ->where('tipe', 'Cuti')->whereYear('created_at', $year)->where('approval_manager', '1'))->get();
            $AbsenSakit = $applyUserFilter(pengajuancuti::with('karyawan')
                ->where('tipe', 'Sakit')->whereYear('created_at', $year)->where('approval_manager', '1'))->get();
            $AbsenIzin = $applyUserFilter(izinTigaJam::with('karyawan')
                ->whereYear('created_at', $year))->get();

            $dataCard_utama = [
                'karyawan_aktif' => $totalKaryawan,
                'dataSakit' => [
                    'totalAbsenSakit' => $AbsenSakit->pluck('id_karyawan')->unique()->count(),
                    'dataSakit' => $AbsenSakit->map(fn($s) => [
                        'namaKaryawan' => $s->karyawan->nama_lengkap ?? '-', 'divisi' => $s->karyawan->divisi ?? '-',
                        'alasan' => $s->alasan ?? '-', 'tanggalAwal' => $s->tanggal_awal ?? '-', 'tanggalAkhir' => $s->tanggal_akhir ?? '-'
                    ])
                ],
                'dataCuti' => [
                    'totalAbsenCuti' => $AbsenCuti->pluck('id_karyawan')->unique()->count(),
                    'dataCuti' => $AbsenCuti->map(fn($c) => [
                        'namaKaryawan' => $c->karyawan->nama_lengkap ?? '-', 'divisi' => $c->karyawan->divisi ?? '-',
                        'alasan' => $c->alasan ?? '-', 'tanggalAwal' => $c->tanggal_awal ?? '-', 'tanggalAkhir' => $c->tanggal_akhir ?? '-'
                    ])
                ],
                'dataIzin' => [
                    'totalAbsenIzin' => $AbsenIzin->pluck('id_karyawan')->unique()->count(),
                    'dataIzin' => $AbsenIzin->map(fn($i) => [
                        'namaKaryawan' => $i->karyawan->nama_lengkap ?? '-', 'divisi' => $i->karyawan->divisi ?? '-',
                        'alasan' => $i->alasan ?? '-', 'tanggalPengajuan' => $i->tanggal_pengajuan ?? '-'
                    ])
                ]
            ];

            $totalSemua = shareForm::whereYear('created_at', $year)->count();
            $totalDilaksanakan = nilaiKPI::whereYear('created_at', $year)->where('status', '1')
                ->select(\Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT CONCAT(id_evaluator, "-", id_evaluated, "-", kode_form, "-", jenis_penilaian)) as count'))
                ->value('count') ?? 0;
            $totalBelumDilaksanakan = max(0, $totalSemua - $totalDilaksanakan);

            $dataFormulir = [
                'totalFormulir' => formPenilaian::whereYear('created_at', $year)->count(),
                'totalRutin' => formPenilaian::whereYear('created_at', $year)->where('jenis_form', 'Rutin')->count(),
                'totalProbation' => formPenilaian::whereYear('created_at', $year)->where('jenis_form', 'Probation')->count(),
                'totalKontrak' => formPenilaian::whereYear('created_at', $year)->where('jenis_form', 'Kontrak')->count()
            ];

            $totalTargets = targetKPI::whereYear('created_at', $year)->count();
            $activeTargets = targetKPI::whereYear('created_at', $year)->where('status', '0')->count();
            $achievedTargets = targetKPI::whereYear('created_at', $year)->where('status', '1')->count();

            $divisions = karyawan::whereNotNull('divisi')
                ->where('divisi', '!=', '')
                ->where('divisi', '!=', 'Direksi')
                ->where('divisi', '!=', 'Pilih Divisi')
                ->where('divisi', 'NOT LIKE', 'Pilih%')
                ->distinct()
                ->pluck('divisi');

            $divisiOverview = [];
            $heatmapDataByDivisi = [];

            foreach ($divisions as $divisiName) {
                $deptData = $overviewService->getDepartmentOverviewData($divisiName, $year);

                if ($deptData['total_target'] > 0 || !empty($deptData['karyawan_departemen'])) {
                    $divisiOverview[] = [
                        'divisi' => $divisiName,
                        'total_kpi' => $deptData['total_target'],
                        'total_karyawan' => count($deptData['karyawan_departemen']),
                        'avg_progress' => $deptData['rata_rata_progress'],
                        'avg_prediction' => $deptData['rata_rata_progress'],
                        'health' => [
                            'on_track' => $deptData['kpi_selesai'],
                            'at_risk' => $deptData['kpi_aktif'],
                            'behind' => $deptData['kpi_gagal'],
                        ],
                        'total_achieved' => 0,
                        'total_target' => 0,
                        'total_achieved_display' => '0',
                        'total_target_display' => '0',
                    ];

                    $idKaryawanDivisi = karyawan::where('divisi', $divisiName)
                        ->where('status_aktif', '1')
                        ->pluck('id');
                    $totalKaryawanDivisi = max(1, $idKaryawanDivisi->count());

                    $monthlyProgress = $deptData['monthly_progress'] ?? [];
                    $progressArray = [];
                    for ($m = 1; $m <= 12; $m++) {
                        $progressArray[] = round($monthlyProgress[$m] ?? 0, 2);
                    }

                    $nilaiPerBulan = nilaiKPI::whereYear('created_at', $year)
                        ->where(function ($q) use ($idKaryawanDivisi) {
                            $q->whereIn('id_evaluated', $idKaryawanDivisi)
                            ->orWhereIn('id_evaluator', $idKaryawanDivisi);
                        })
                        ->selectRaw('MONTH(created_at) as bulan, status, COUNT(*) as jumlah')
                        ->groupBy('bulan', 'status')
                        ->get();

                    $completionArray = [];
                    for ($m = 1; $m <= 12; $m++) {
                        $rows = $nilaiPerBulan->where('bulan', $m);
                        $total = $rows->sum('jumlah');
                        $selesai = $rows->where('status', '1')->sum('jumlah');
                        $completionArray[] = $total > 0 ? round(($selesai / $total) * 100, 2) : 0;
                    }

                    $engagementPerBulan = activityLog::whereYear('created_at', $year)
                        ->whereIn('user_id', $idKaryawanDivisi)
                        ->selectRaw('MONTH(created_at) as bulan, COUNT(DISTINCT user_id) as aktif')
                        ->groupBy('bulan')
                        ->pluck('aktif', 'bulan');

                    $engagementArray = [];
                    for ($m = 1; $m <= 12; $m++) {
                        $aktif = $engagementPerBulan[$m] ?? 0;
                        $engagementArray[] = round(($aktif / $totalKaryawanDivisi) * 100, 2);
                    }

                    $heatmapDataByDivisi[$divisiName] = [
                        'progress' => $progressArray,
                        'completion' => $completionArray,
                        'engagement' => $engagementArray
                    ];
                }
            }

            usort($divisiOverview, fn($a, $b) => $b['avg_progress'] <=> $a['avg_progress']);

            $alertTargets = targetKPI::with('detailTargetKPI')
                ->whereYear('created_at', $year)->where('status', '0')
                ->orderBy('created_at', 'desc')->limit(5)->get()
                ->map(function($target) {
                    return [
                        'type' => 'at-risk',
                        'title' => $target->judul ?: 'Target KPI',
                        'desc' => 'Target untuk ' . ($target->detailTargetKPI->first()?->jabatan ?? 'Umum'),
                        'priority' => 'Medium',
                        'time' => $target->created_at->diffForHumans()
                    ];
                });

                $slaHariPenilaian = 14;

                $pendingAssessments = nilaiKPI::where('status', '0')
                    ->whereYear('created_at', $year)
                    ->select('kode_form', 'id_evaluator', 'id_evaluated', 'jenis_penilaian', DB::raw('MIN(created_at) as assigned_at'))
                    ->groupBy('kode_form', 'id_evaluator', 'id_evaluated', 'jenis_penilaian')
                    ->get();

                $idEvaluatedList = $pendingAssessments->pluck('id_evaluated')->unique();
                $karyawanMapDeadline = karyawan::whereIn('id', $idEvaluatedList)->get()->keyBy('id');

                $deadlineForms = $pendingAssessments->map(function ($item) use ($slaHariPenilaian, $karyawanMapDeadline) {
                        $assignedAt = Carbon::parse($item->assigned_at);
                        $deadlineDate = $assignedAt->copy()->addDays($slaHariPenilaian);
                        $daysLeft = now()->diffInDays($deadlineDate, false);
                        $priority = $daysLeft < 0 ? 'Overdue' : ($daysLeft <= 3 ? 'High' : ($daysLeft <= 7 ? 'Medium' : 'Low'));
                        $karyawan = $karyawanMapDeadline->get($item->id_evaluated);

                        return [
                            'day' => $deadlineDate->format('d'),
                            'month' => $deadlineDate->format('M'),
                            'title' => 'Penilaian ' . $item->jenis_penilaian . ' - ' . ($karyawan->nama_lengkap ?? 'Karyawan'),
                            'assignee' => $karyawan->divisi ?? 'Umum',
                            'priority' => $priority,
                            'days_left' => $daysLeft,
                            'kode_form' => $item->kode_form,
                            'id_evaluator' => $item->id_evaluator,
                            'id_evaluated' => $item->id_evaluated,
                            'jenis_penilaian' => $item->jenis_penilaian,
                        ];
                    })
                    ->sortBy('days_left')->take(5)->values();

                $activityTimeline = activityLog::with('karyawan:id,nama_lengkap,jabatan')
                    ->whereYear('created_at', $year)
                    ->whereNotIn('status', ['Login', 'Logout', 'Absen Masuk', 'Absen Keluar'])
                    ->orderBy('created_at', 'desc')->limit(8)->get()
                    ->map(function($log) {
                        $type = 'info';
                        $statusLower = strtolower($log->status ?: '');
                        if (str_contains($statusLower, 'selesai') || str_contains($statusLower, 'success')) $type = 'success';
                        elseif (str_contains($statusLower, 'error') || str_contains($statusLower, 'gagal')) $type = 'danger';
                        elseif (str_contains($statusLower, 'warning') || str_contains($statusLower, 'telat')) $type = 'warning';

                        return [
                            'id' => $log->id,
                            'type' => $type,
                            'title' => $log->status ?: 'Aktivitas Sistem',
                            'desc' => $log->karyawan ? $log->karyawan->nama_lengkap . ' - ' . $log->karyawan->jabatan : 'Sistem',
                            'time' => $log->created_at->diffForHumans()
                        ];
                    });

                $achievedTargetsList = targetKPI::with('detailTargetKPI')
                    ->whereYear('updated_at', $year)->where('status', '1')
                    ->orderBy('updated_at', 'desc')->limit(3)->get()
                    ->map(function($target) {
                        return [
                            'achievement_type' => 'target',
                            'ref_id' => $target->id,
                            'icon' => 'fa-bullseye',
                            'title' => 'Target Tercapai',
                            'desc' => ($target->judul ?: 'Target KPI') . ' - ' . ($target->detailTargetKPI->first()?->jabatan ?? 'Umum'),
                            'time' => $target->updated_at->diffForHumans(),
                            'sort_at' => $target->updated_at,
                        ];
                    });

                $currentMonth = now()->month;
                $topPerformers = nilaiKPI::whereYear('created_at', $year)->whereMonth('created_at', $currentMonth)
                    ->where('status', '1')->whereNotNull('nilai')
                    ->select('id_evaluated', DB::raw('AVG(nilai) as rata_rata'))
                    ->groupBy('id_evaluated')->havingRaw('AVG(nilai) >= 85')
                    ->orderByDesc('rata_rata')->limit(2)->get();$karyawanTopMap = karyawan::whereIn('id', $topPerformers->pluck('id_evaluated'))->get()->keyBy('id');

                $achievementsFromScore = $topPerformers->map(function($item) use ($karyawanTopMap) {
                    $k = $karyawanTopMap->get($item->id_evaluated);
                    return [
                        'achievement_type' => 'performer',
                        'ref_id' => $item->id_evaluated,
                        'icon' => 'fa-star',
                        'title' => 'Top Performer Bulan Ini',
                        'desc' => ($k->nama_lengkap ?? 'Karyawan') . ' - rata-rata nilai ' . round($item->rata_rata, 1),
                        'time' => 'Bulan ini',
                        'sort_at' => now(),
                    ];
                });

                $achievements = $achievedTargetsList->concat($achievementsFromScore)
                    ->sortByDesc('sort_at')->take(4)->values()
                    ->map(fn($a) => collect($a)->except('sort_at')->toArray());

                // News: tambahkan type + id
                $newsTargets = targetKPI::whereYear('created_at', $year)->orderBy('created_at', 'desc')->limit(3)->get()
                    ->map(function($target) {
                        return [
                            'news_type' => 'target',
                            'ref_id' => $target->id,
                            'title' => 'Target Baru: ' . ($target->judul ?: 'KPI'),
                            'desc' => 'Dibuat untuk periode ' . $target->created_at->format('Y'),
                            'time' => $target->created_at->diffForHumans(),
                            'tag' => 'Target',
                            'sort_at' => $target->created_at,
                        ];
                    });

                $newsAdministrasi = AdministrasiKaryawan::whereYear('created_at', $year)->where('status', 'selesai')
                    ->orderBy('tanggal_selesai', 'desc')->limit(2)->get()
                    ->map(function($item) {
                        $waktu = $item->tanggal_selesai ? Carbon::parse($item->tanggal_selesai) : $item->updated_at;
                        return [
                            'news_type' => 'administrasi',
                            'ref_id' => $item->id,
                            'title' => $item->nama_administrasi,
                            'desc' => 'Proses administrasi HR telah diselesaikan',
                            'time' => $waktu->diffForHumans(),
                            'tag' => 'Administrasi',
                            'sort_at' => $waktu,
                        ];
                    });

                $news = $newsTargets->concat($newsAdministrasi)->sortByDesc('sort_at')->take(4)->values()
                    ->map(fn($n) => collect($n)->except('sort_at')->toArray());

            return [
                'tahun' => $year,
                'dataCard_first' => $dataCard_utama,
                'dataChartPenilaian' => [
                    'totalSemua' => $totalSemua, 
                    'totalDilaksanakan' => $totalDilaksanakan, 
                    'totalBelumDilaksanakan' => $totalBelumDilaksanakan
                ],
                'dataFormulir' => $dataFormulir,
                'dataDivisi' => $divisiOverview,
                'dataRangking' => [], 
                'quick_stats' => [
                    'projects' => $activeTargets,
                    'projects_trend' => 0,
                    'achieved' => $achievedTargets,
                    'total_targets' => $totalTargets,
                    'deadlines' => $deadlineForms->count(),
                    'engagement' => $totalSemua > 0 ? round(($totalDilaksanakan / $totalSemua) * 100) : 0
                ],
                'heatmap_data' => $heatmapDataByDivisi,
                'divisi_list' => $divisions->toArray(),
                'kpi_alerts' => $alertTargets,
                'upcoming_deadlines' => $deadlineForms,
                'activity_timeline' => $activityTimeline,
                'achievements' => $achievements,
                'news' => $news
            ];
        });
        return response()->json($payload);
    }
    
    public function getDataProfile(Request $request)
    {
        $user = auth()->user()->karyawan_id;
        $karyawan = karyawan::where('id', $user)->first();
        return response()->json(['data' => $karyawan]);
    }

    private function getDataAbsen($id_karyawan, $tahun)
    {
        $sakit = pengajuancuti::where('id_karyawan', $id_karyawan)
            ->where('tipe', 'Sakit')
            ->where('approval_manager', '1')
            ->whereYear('tanggal_awal', $tahun)
            ->count();

        $izin = izinTigaJam::where('id_karyawan', $id_karyawan)
            ->whereYear('created_at', $tahun)
            ->count();

        $telat = AbsensiKaryawan::where('id_karyawan', $id_karyawan)
            ->whereYear('created_at', $tahun)
            ->where('keterangan', 'Telat')
            ->count();

        return ['sakit' => $sakit, 'telat' => $telat, 'izin' => $izin];
    }

    public function getDeadlineDetail(Request $request)
    {
        $request->validate([
            'kode_form' => 'required|string',
            'id_evaluator' => 'required|integer',
            'id_evaluated' => 'required|integer',
            'jenis_penilaian' => 'required|string',
        ]);

        $evaluator = karyawan::find($request->id_evaluator);
        $evaluated = karyawan::find($request->id_evaluated);

        $items = nilaiKPI::where('kode_form', $request->kode_form)
            ->where('id_evaluator', $request->id_evaluator)
            ->where('id_evaluated', $request->id_evaluated)
            ->where('jenis_penilaian', $request->jenis_penilaian)
            ->get();

        $assignedAt = $items->min('created_at');
        $totalKategori = $items->count();
        $totalSelesai = $items->where('status', '1')->count();

        return response()->json([
            'evaluator' => $evaluator->nama_lengkap ?? '-',
            'evaluated' => $evaluated->nama_lengkap ?? '-',
            'divisi' => $evaluated->divisi ?? '-',
            'jenis_penilaian' => $request->jenis_penilaian,
            'assigned_at' => $assignedAt ? Carbon::parse($assignedAt)->translatedFormat('l, d F Y') : '-',
            'total_kategori' => $totalKategori,
            'total_selesai' => $totalSelesai,
            'progress_percent' => $totalKategori > 0 ? round(($totalSelesai / $totalKategori) * 100) : 0,
            'kategori_list' => $items->map(fn($n) => [
                'nama' => $n->name_variabel,
                'status' => $n->status === '1' ? 'Selesai' : 'Belum Dinilai',
            ]),
        ]);
    }

    public function getActivityDetail(Request $request)
    {
        $request->validate(['id' => 'required|integer']);

        $log = activityLog::with('karyawan:id,nama_lengkap,jabatan,divisi')->find($request->id);
        if (!$log) return response()->json(['error' => 'Data tidak ditemukan'], 404);

        // riwayat aktivitas lain dari karyawan yang sama, hari yang sama
        $related = activityLog::where('user_id', $log->user_id)
            ->whereDate('created_at', $log->created_at->toDateString())
            ->whereNotIn('status', ['Login', 'Logout', 'Absen Masuk', 'Absen Keluar'])
            ->orderBy('created_at', 'desc')
            ->limit(10)->get();

        return response()->json([
            'title' => $log->status,
            'karyawan' => $log->karyawan->nama_lengkap ?? 'Sistem',
            'jabatan' => $log->karyawan->jabatan ?? '-',
            'divisi' => $log->karyawan->divisi ?? '-',
            'waktu' => $log->created_at->translatedFormat('l, d F Y H:i'),
            'riwayat_hari_ini' => $related->map(fn($r) => [
                'status' => $r->status,
                'time' => $r->created_at->format('H:i'),
            ]),
        ]);
    }

    public function getAchievementDetail(Request $request)
    {
        $request->validate([
            'type' => 'required|in:target,performer',
            'ref_id' => 'required|integer',
        ]);

        if ($request->type === 'target') {
            $target = targetKPI::with('detailTargetKPI')->find($request->ref_id);
            if (!$target) return response()->json(['error' => 'Target tidak ditemukan'], 404);

            return response()->json([
                'type' => 'target',
                'judul' => $target->judul ?: 'Target KPI',
                'status' => 'Tercapai',
                'dibuat' => $target->created_at->translatedFormat('l, d F Y'),
                'selesai' => $target->updated_at->translatedFormat('l, d F Y'),
                'detail' => $target->detailTargetKPI->map(fn($d) => [
                    'jabatan' => $d->jabatan ?? '-',
                    'keterangan' => $d->keterangan ?? '-',
                ]),
            ]);
        }

        // performer
        $karyawan = karyawan::find($request->ref_id);
        if (!$karyawan) return response()->json(['error' => 'Karyawan tidak ditemukan'], 404);

        $currentMonth = now()->month;
        $currentYear = now()->year;

        $nilaiDetail = nilaiKPI::where('id_evaluated', $karyawan->id)
            ->whereYear('created_at', $currentYear)->whereMonth('created_at', $currentMonth)
            ->where('status', '1')->whereNotNull('nilai')
            ->select('name_variabel', DB::raw('AVG(nilai) as rata_rata'))
            ->groupBy('name_variabel')->get();

        return response()->json([
            'type' => 'performer',
            'nama' => $karyawan->nama_lengkap,
            'divisi' => $karyawan->divisi,
            'jabatan' => $karyawan->jabatan,
            'rata_rata_keseluruhan' => round($nilaiDetail->avg('rata_rata'), 1),
            'breakdown' => $nilaiDetail->map(fn($n) => [
                'kategori' => $n->name_variabel,
                'nilai' => round($n->rata_rata, 1),
            ]),
        ]);
    }

    public function getNewsDetail(Request $request)
    {
        $request->validate([
            'type' => 'required|in:target,administrasi',
            'ref_id' => 'required|integer',
        ]);

        if ($request->type === 'target') {
            $target = targetKPI::with('detailTargetKPI')->find($request->ref_id);
            if (!$target) return response()->json(['error' => 'Data tidak ditemukan'], 404);

            return response()->json([
                'title' => 'Target Baru: ' . ($target->judul ?: 'KPI'),
                'dibuat' => $target->created_at->translatedFormat('l, d F Y H:i'),
                'status' => $target->status === '1' ? 'Tercapai' : 'Berjalan',
                'detail' => $target->detailTargetKPI->map(fn($d) => [
                    'jabatan' => $d->jabatan ?? '-',
                    'keterangan' => $d->keterangan ?? '-',
                ]),
            ]);
        }

        $item = AdministrasiKaryawan::find($request->ref_id);
        if (!$item) return response()->json(['error' => 'Data tidak ditemukan'], 404);

        return response()->json([
            'title' => $item->nama_administrasi,
            'status' => $item->status,
            'dibuat' => $item->created_at->translatedFormat('l, d F Y'),
            'selesai' => $item->tanggal_selesai ? Carbon::parse($item->tanggal_selesai)->translatedFormat('l, d F Y') : '-',
        ]);
    }

    public function divisiDrilldown(Request $request)
    {
        $divisi = $request->query('divisi');
        $currentYear = now()->year;
        if (!$divisi) return response()->json(['error' => 'Divisi wajib diisi'], 422);
        
        $data = app(\App\Services\KPI\Dashboard\OverviewDashboardService::class)->getDivisiDrilldownData($divisi, $currentYear);
        return response()->json($data);
    }
}