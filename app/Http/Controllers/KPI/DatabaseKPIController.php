<?php

namespace App\Http\Controllers\KPI;

use App\Http\Controllers\Controller;
use App\Models\activityLog;
use App\Models\formPenilaian;
use App\Models\karyawan;
use App\Models\kategoriKPI;
use App\Models\NilaiKPI;
use App\Models\nilaiKPI as ModelsNilaiKPI;
use App\Models\pengajuancuti;
use App\Models\RKM;
use App\Models\shareForm;
use App\Models\tipeKategoriTabel;
use App\Models\User;
use App\Notifications\CommentNotification;
use App\Notifications\penilaianExcangheNotifikasi;
use Google\Service\AnalyticsReporting\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Contracts\Service\Attribute\Required;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;
use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Sum;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Unique;
use App\Mail\mailPenilaian;
use App\Models\AbsensiKaryawan;
use App\Models\izinTigaJam;
use App\Models\Nilaifeedback;
use App\Models\PengajuanBarang;
use App\Models\SuratPerjalanan;
use App\Models\targetKPI;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use function Laravel\Prompts\error;

class DatabaseKPIController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:View DatabaseKPI', ['only' => ['index']]);
    }


    public function activityLog()
    {
        $id_karyawan = Auth::user()->id;

        $dataAuth = activityLog::with('karyawan')
            ->where('user_id', $id_karyawan)
            ->whereIn('status', ['Login', 'Logout'])
            ->orderBy('created_at', 'desc')
            ->get();

        $dataVisit = activityLog::with('karyawan')
            ->where('user_id', $id_karyawan)
            ->whereNotIn('status', ['Login', 'Logout'])
            ->whereNotIn('status', ['Absen Masuk', 'Absen Keluar'])
            ->orderBy('created_at', 'desc')
            ->get();

        $dataAbsen = activityLog::with('karyawan')
            ->where('user_id', $id_karyawan)
            ->whereIn('status', ['Absen Masuk', 'Absen Keluar'])
            ->orderBy('created_at', 'desc')
            ->get();

        $dataUptimeInformasional = activityLog::whereBetween('status', [100, 199])
            ->orderBy('created_at', 'desc')
            ->get();

        $dataUptimeSuccess = activityLog::whereBetween('status', [200, 299])
            ->orderBy('created_at', 'desc')
            ->get();

        $dataUptimeRedirect = activityLog::whereBetween('status', [300, 399])
            ->orderBy('created_at', 'desc')
            ->get();

        $dataUptimeClientError = activityLog::whereBetween('status', [400, 499])
            ->orderBy('created_at', 'desc')
            ->get();

        $dataUptimeServerError = activityLog::whereBetween('status', [500, 599])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('databasekpi.activityLog', compact(
            'dataAuth',
            'dataVisit',
            'dataAbsen',
            'dataUptimeInformasional',
            'dataUptimeSuccess',
            'dataUptimeRedirect',
            'dataUptimeClientError',
            'dataUptimeServerError'
        ));
    }

    public function UptimePresentase()
    {
        $now = Carbon::now();
        
        $weekStart = $now->copy()->startOfWeek();
        $weekEnd = $now->copy()->endOfWeek();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        // Pengambilan data dari server cctv
        $response = Http::get('http://localhost:8001/uptime.php', [
            'password' => env('UPTIME_PASSWORD')
        ]);

        if ($response->failed() || $response->body() === 'FILE_NOT_FOUND') {
            return response()->json(['error' => 'Tidak bisa mengambil file dari Server CCTV'], 404);
        }
        
        $content = $response->body();
        $lines   = array_filter(explode("\n", $content));

        // Trim dulu data yg didapat agar terbaca
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

        // Kondisi hanya untuk "Server APK"
        $apkRecords = array_filter($records, function($r) {
            return stripos($r['server'], 'APK') !== false;
        });

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

        
        $latteWeekTotal = activityLog::where('url', 'https://192.168.95.60:8002/')
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->count();
        $latteWeekUp = activityLog::where('status', '200')
            ->where('url', 'https://192.168.95.60:8002/')
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->count();
        $latteWeekPercent = $latteWeekTotal > 0 ? ($latteWeekUp / $latteWeekTotal) * 100 : 0;

        $latteMonthTotal = activityLog::where('url', 'https://192.168.95.60:8002/')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->count();
        $latteMonthUp = activityLog::where('status', '200')
            ->where('url', 'https://192.168.95.60:8002/')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->count();
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

            $result = [];

            foreach ($urls as $url) {
                $checks = ActivityLog::where('url', $url)
                    ->where('status', 'uptime')
                    ->orderBy('created_at', 'desc')
                    ->limit(100)
                    ->get();

                $labels = $checks->map(fn($log) => $log->created_at->format('d M H:i'))->values();
                $responseTimes = $checks->map(fn($log) => $log->response_time_ms ?? 0)->values();
                $statuses = $checks->map(fn($log) => (bool) $log->is_up)->values();

                $result[$url] = [
                    'labels' => $labels,
                    'response_times' => $responseTimes,
                    'statuses' => $statuses,
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
            'quartal' => 'required|string',
            'tahun'   => 'required|integer'
        ]);

        $quartal = $request->input('quartal');
        $tahun   = $request->input('tahun');
        $divisi  = $request->input('divisi');

        $formPenilaians = formPenilaian::with('karyawan')
            ->whereHas('karyawan', function ($q) use ($divisi) {
                $q->where('divisi', $divisi);
            })
            ->where('quartal', $quartal)
            ->where('tahun', $tahun)
            ->where('jenis_form', 'Rutin')
            ->get();

        if ($formPenilaians->isEmpty()) {
            return back()->with('error', 'Tidak ada data penilaian untuk divisi & periode tersebut');
        }

        $finalData = [];

        foreach ($formPenilaians->groupBy('id_karyawan') as $id_karyawan => $forms) {
            $form = $forms->first();

            $evaluated = [
                'nama'    => optional($form->karyawan)->nama_lengkap . ' - ' . optional($form->karyawan)->divisi ?? '-',
                'quartal' => $form->quartal,
                'tahun'   => $form->tahun,
                'id_karyawan' => $form->id_karyawan,
                'catatan' => $form->catatan,
            ];

            // === Data Absensi ===
            $dataAbsensi = AbsensiKaryawan::where('id_karyawan', $form->id_karyawan)
                ->whereMonth('created_at', $quartal) // asumsi quartal = angka bulan
                ->whereYear('created_at', $tahun)
                ->get();

            $telat = $dataAbsensi->where('keterangan', 'Telat')->count();
            $izin  = $dataAbsensi->where('keterangan', 'Izin')->count();
            $sakit = $dataAbsensi->where('keterangan', 'Sakit')->count();

            $dataAbsen = [
                'sakit' => $sakit,
                'telat' => $telat,
                'izin'  => $izin
            ];

            // === Ambil Kategori KPI ===
            $allKategoriKPIs = $forms->flatMap(function ($form) {
                return kategoriKPI::where('kode_kategori', $form->kode_kategori)->get();
            })->unique('judul_kategori')->values();

            // === Evaluator Data ===
            $allEvaluatorData = $forms->flatMap(function ($form) {
                return shareForm::with('evaluator')
                    ->where('id_evaluated', $form->id_karyawan)
                    ->where('kode_form', $form->kode_form)
                    ->get();
            });

            $evaluatorList = [];

            foreach ($allEvaluatorData as $evaluatorItem) {
                $nilaiCollection = nilaiKPI::where('id_evaluator', $evaluatorItem->id_evaluator)
                    ->where('id_evaluated', $evaluatorItem->id_evaluated)
                    ->where('kode_form', $form->kode_form)
                    ->where('jenis_penilaian', $evaluatorItem->jenis_penilaian)
                    ->get();

                $listNilaiEvaluator = [];

                foreach ($allKategoriKPIs as $kategori) {
                    $item = $nilaiCollection->first(
                        fn($item) =>
                        $item->id_evaluator === $evaluatorItem->id_evaluator &&
                            $item->name_variabel === $kategori->judul_kategori
                    );

                    $listNilaiEvaluator[] = [
                        'pesan' => $item->pesan ?? '-',
                        'nilai' => $item->nilai ?? '-'
                    ];
                }

                $evaluatorList[] = [
                    'nama' => optional($evaluatorItem->evaluator)->nama_lengkap . ' - ' . optional($evaluatorItem->evaluator)->divisi ?? '-',
                    'jenis_penilaian' => $evaluatorItem->jenis_penilaian ?? '-',
                    'nilai' => $listNilaiEvaluator
                ];
            }

            // === Data Kriteria ===
            $dataKriteria = $forms->map(function ($form) {
                $kategoriKPIs = kategoriKPI::where('kode_kategori', $form->kode_kategori)->get();

                $detailKriteria = $kategoriKPIs->map(function ($kategori) {
                    $tipeDetails = tipeKategoriTabel::where('id_kategori', $kategori->id)->get();

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

            $evaluatorList = collect($evaluatorList)
                ->unique(fn($item) => $item['nama'] . $item['jenis_penilaian'])
                ->values();

            // === Push Data ===
            $finalData[] = [
                'evaluated' => $evaluated,
                'dataAbsen' => $dataAbsen,
                'data' => [
                    'evaluator' => $evaluatorList,
                    'dataKriteria' => $dataKriteria
                ]
            ];
        }

        $data = ['data' => $finalData];

        return view('pdf.rekapPenilaianDivisi', $data);

        // $pdf = Pdf::loadView('pdf.rekapPenilaianDivisi', $data);

        // $filename = "Rekap_Penilaian_Divisi_{$divisi}_{$quartal}_{$tahun}.pdf";

        // return $pdf->download($filename);
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

        $karyawan = karyawan::where('id', $id_karyawan)->first();

        if (!$karyawan) {
            return back()->with('error', 'Data karyawan tidak ditemukan');
        }

        $formPenilaians = formPenilaian::with('karyawan')
            ->where('id_karyawan', $id_karyawan)
            ->where('kode_form', $kodeForm)
            ->get();

        if ($formPenilaians->isEmpty()) {
            return back()->with('error', 'Data form penilaian tidak ditemukan');
        }

        $month = now()->month;
        $year = now()->year;
        $quarter = match (true) {
            $month >= 1 && $month <= 3 => [1, 2, 3],
            $month >= 4 && $month <= 6 => [4, 5, 6],
            $month >= 7 && $month <= 9 => [7, 8, 9],
            default => [10, 11, 12],
        };

        $formPenilaiansTahun = formPenilaian::where('id_karyawan', $id_karyawan)
            ->where('kode_form', $kodeForm)
            ->where('tahun', $year)
            ->pluck('id_karyawan');

        $formPenilaiansQuartal = formPenilaian::where('id_karyawan', $id_karyawan)
            ->where('kode_form', $kodeForm)
            ->where('tahun', $year)
            ->whereIn('quartal', $quarter)
            ->pluck('id_karyawan');

        $shareFormTahun = shareForm::whereIn('id_evaluated', $formPenilaiansTahun)
            ->where('kode_form', $kodeForm)
            ->get();

        $shareFormQuartal = shareForm::whereIn('id_evaluated', $formPenilaiansQuartal)
            ->where('kode_form', $kodeForm)
            ->get();

        $dataNilaiTahun = nilaiKPI::whereIn('id_evaluated', $shareFormTahun->pluck('id_evaluated'))
            ->where('kode_form', $kodeForm)
            ->get();

        $dataNilaiQuartal = nilaiKPI::whereIn('id_evaluated', $shareFormQuartal->pluck('id_evaluated'))
            ->where('kode_form', $kodeForm)
            ->get();

        $getNilaiFinal = fn($data) => collect($data)
            ->filter(fn($item) => is_numeric($item?->nilai))
            ->map(function ($item) {
                $bobot = kategoriKPI::where('kode_kategori', $item?->kode_kategori)->value('bobot') ?? 0;
                return (($item?->nilai ?? 0) * $bobot) / 100;
            })->sum();

        $dataNilaiTahunCount = $getNilaiFinal($dataNilaiTahun);
        $dataNilaiQuartalCount = $getNilaiFinal($dataNilaiQuartal);

        $form = $formPenilaians->first();
        $evaluated = [
            'nama'    => optional($form->karyawan)->nama_lengkap . ' - ' . optional($form->karyawan)->divisi ?? '-',
            'quartal' => $form->quartal,
            'tahun'   => $form->tahun,
            'id_karyawan' => $form->id_karyawan,
            'catatan' => $form->catatan,
        ];

        $currentMonth = now()->month;
        $currentQuartal = match (true) {
            $currentMonth >= 1 && $currentMonth <= 3 => [1, 2, 3],
            $currentMonth >= 4 && $currentMonth <= 6 => [4, 5, 6],
            $currentMonth >= 7 && $currentMonth <= 9 => [7, 8, 9],
            default => [10, 11, 12],
        };

        $dataAbsensi = AbsensiKaryawan::where('id_karyawan', $form->id_karyawan)
            ->whereIn(DB::raw('MONTH(created_at)'), $currentQuartal)
            ->get();

        $telat = $dataAbsensi->where('keterangan', 'Telat')->count();
        $izin  = $dataAbsensi->where('keterangan', 'Izin')->count();
        $sakit = $dataAbsensi->where('keterangan', 'Sakit')->count();

        $dataAbsen = [
            'sakit' => $sakit,
            'telat' => $telat,
            'izin'  => $izin
        ];

        $allKategoriKPIs = $formPenilaians->flatMap(function ($form) {
            return kategoriKPI::where('kode_kategori', $form->kode_kategori)->get();
        })->unique('judul_kategori')->values();

        $allEvaluatorData = $formPenilaians->flatMap(function ($form) {
            return shareForm::with('evaluator')
                ->where('id_evaluated', $form->id_karyawan)
                ->where('kode_form', $form->kode_form)
                ->get();
        });

        $evaluatorList = [];

        foreach ($allEvaluatorData as $evaluatorItem) {
            $nilaiCollection = nilaiKPI::where('id_evaluator', $evaluatorItem->id_evaluator)
                ->where('id_evaluated', $evaluatorItem->id_evaluated)
                ->where('kode_form', $kodeForm)
                ->where('jenis_penilaian', $evaluatorItem->jenis_penilaian)
                ->get();

            $listNilaiEvaluator = [];

            foreach ($allKategoriKPIs as $kategori) {
                $item = $nilaiCollection->first(
                    fn($i) =>
                    $i->id_evaluator === $evaluatorItem->id_evaluator &&
                        $i->name_variabel === $kategori->judul_kategori
                );

                $listNilaiEvaluator[] = [
                    'pesan' => $item?->pesan ?? '-',
                    'nilai' => $item?->nilai ?? 0,
                ];
            }

            $evaluatorList[] = [
                'nama' => optional($evaluatorItem->evaluator)->nama_lengkap . ' - ' . optional($evaluatorItem->evaluator)->divisi ?? '-',
                'jenis_penilaian' => $evaluatorItem->jenis_penilaian ?? '-',
                'nilai' => $listNilaiEvaluator
            ];
        }

        $dataKriteria = $formPenilaians->map(function ($form) {
            $kategoriKPIs = kategoriKPI::where('kode_kategori', $form->kode_kategori)->get();

            $detailKriteria = $kategoriKPIs->map(function ($kategori) {
                $tipeDetails = tipeKategoriTabel::where('id_kategori', $kategori->id)->get();

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

        $evaluatorList = collect($evaluatorList)
            ->unique(fn($item) => $item['nama'] . $item['jenis_penilaian'])
            ->values();

        $data = [
            'data' => [
                [
                    'evaluated' => $evaluated,
                    'dataAbsen' => $dataAbsen,
                    'data' => [
                        'evaluator' => $evaluatorList,
                        'dataKriteria' => $dataKriteria
                    ],
                    'tipe_pdf' => $tipe_button
                ]
            ]
        ];

        return view('pdf.rekapPenilaian', $data);
    }

    public function indexKategori(Request $request)
    {
        $divisi = karyawan::select('divisi')->distinct()->pluck('divisi');
        $tipe = $request->query('tipe', 'rutin');
        return view('databasekpi.indexKategori', compact(['divisi', 'tipe']));
    }

    public function kirimEmailData(Request $request)
    {
        $request->validate([
            'id_karyawan' => 'required',
            'kodeForm'    => 'required|string'
        ]);

        $email = karyawan::find($request->input('id_karyawan'));

        if (!$email) {
            return response()->json(['error' => 'Data karyawan tidak ditemukan'], 404);
        }

        if (empty($email->email)) {
            return response()->json(['error' => 'Email karyawan belum tersedia'], 400);
        }

        try {
            $id_karyawan = $request->input('id_karyawan');
            $kodeForm = $request->input('kodeForm');

            $formPenilaians = formPenilaian::with('karyawan')
                ->where('id_karyawan', $id_karyawan)
                ->where('kode_form', $kodeForm)
                ->get();

            if ($formPenilaians->isEmpty()) {
                return response()->json(['message' => 'Data tidak ditemukan'], 404);
            }

            $form = $formPenilaians->first();
            $evaluated = [
                'nama'        => optional($form->karyawan)->nama_lengkap . ' - ' . optional($form->karyawan)->divisi ?? '-',
                'id_karyawan' => optional($form)->id_karyawan,
                'quartal'     => $form->quartal,
                'tahun'       => $form->tahun,
                'catatan'     => $form->catatan,
            ];

            $currentMonth = now()->month;
            $currentQuartal = match (true) {
                $currentMonth >= 1 && $currentMonth <= 3 => [1, 2, 3],
                $currentMonth >= 4 && $currentMonth <= 6 => [4, 5, 6],
                $currentMonth >= 7 && $currentMonth <= 9 => [7, 8, 9],
                default => [10, 11, 12],
            };

            $dataAbsensi = AbsensiKaryawan::where('id_karyawan', $form->id_karyawan)
                ->whereIn(DB::raw('MONTH(created_at)'), $currentQuartal)
                ->get();

            $telat = $dataAbsensi->where('keterangan', 'Telat')->count();
            $izin  = $dataAbsensi->where('keterangan', 'Izin')->count();
            $sakit = $dataAbsensi->where('keterangan', 'Sakit')->count();

            $dataAbsen = [
                'sakit' => $sakit,
                'telat' => $telat,
                'izin'  => $izin
            ];


            $allKategoriKPIs = $formPenilaians->flatMap(function ($form) {
                return kategoriKPI::where('kode_kategori', $form->kode_kategori)->get();
            })->unique('judul_kategori')->values();

            $allEvaluatorData = $formPenilaians->flatMap(function ($form) {
                return shareForm::with('evaluator')
                    ->where('id_evaluated', $form->id_karyawan)
                    ->where('kode_form', $form->kode_form)
                    ->get();
            });

            $evaluatorList = [];

            foreach ($allEvaluatorData as $evaluatorItem) {
                $nilaiCollection = nilaiKPI::where('id_evaluator', $evaluatorItem->id_evaluator)
                    ->where('id_evaluated', $evaluatorItem->id_evaluated)
                    ->where('kode_form', $kodeForm)
                    ->where('jenis_penilaian', $evaluatorItem->jenis_penilaian) // << Tambahan penting
                    ->get();

                $listNilaiEvaluator = [];

                foreach ($allKategoriKPIs as $kategori) {
                    $item = $nilaiCollection->first(
                        fn($item) =>
                        $item->id_evaluator === $evaluatorItem->id_evaluator &&
                            $item->name_variabel === $kategori->judul_kategori
                    );

                    $listNilaiEvaluator[] = [
                        'pesan' => $item->pesan ?? '-',
                        'nilai' => $item->nilai ?? '-'
                    ];
                }

                $evaluatorList[] = [
                    'nama' => optional($evaluatorItem->evaluator)->nama_lengkap . ' - ' . optional($evaluatorItem->evaluator)->divisi ?? '-',
                    'jenis_penilaian' => $evaluatorItem->jenis_penilaian ?? '-',
                    'nilai' => $listNilaiEvaluator,
                ];
            }

            $dataKriteria = $formPenilaians
                ->groupBy(fn($item) => $item->kode_form . '|' . $item->nama_penilaian)
                ->map(function ($groupedForms, $combinedKey) {
                    [$kodeForm, $namaPenilaian] = explode('|', $combinedKey);

                    $kategoriKPIs = $groupedForms->flatMap(function ($form) {
                        return kategoriKPI::where('kode_kategori', $form->kode_kategori)->get();
                    })->unique('judul_kategori')->values();

                    $detailKriteria = $kategoriKPIs->map(function ($kategori) {
                        $tipeDetails = tipeKategoriTabel::where('id_kategori', $kategori->id)->get();

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
                })
                ->values()
                ->toArray();

            $evaluatorList = collect($evaluatorList)
                ->unique(fn($item) => $item['nama'] . $item['jenis_penilaian'])
                ->values();

            Mail::to($email->email)->send(new mailPenilaian([
                'evaluated'     => $evaluated,
                'dataAbsen'     => $dataAbsen,
                'evaluator'     => $evaluatorList,
                'dataKriteria'  => $dataKriteria,
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
            'quartal'     => 'required|in:S1,S2,Q1,Q2,Q3,Q4',
            'tahun'       => 'required',
            'kode_form'   => 'required',
            'catatan'     => 'required|string'
        ]);

        $id_karyawan = $request->input('id_karyawan');
        $quartal     = $request->input('quartal');
        $tahun       = $request->input('tahun');
        $kode_form   = $request->input('kode_form');
        $catatan     = $request->input('catatan');

        $query = formPenilaian::where('id_karyawan', $id_karyawan)
            ->where('kode_form', $kode_form)
            ->where('tahun', $tahun);

        if ($quartal === 'S1') {
            $query->whereIn('quartal', ['Q1', 'Q2']);
        } elseif ($quartal === 'S2') {
            $query->whereIn('quartal', ['Q3', 'Q4']);
        } else {
            $query->where('quartal', $quartal);
        }

        $affectedRows = $query->update([
            'catatan' => $catatan
        ]);

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

        $formPenilaians = formPenilaian::with('karyawan')
            ->where('id_karyawan', $id_karyawan)
            ->where('kode_form', $kodeForm)
            ->where('jenis_form', $jenis_form)
            ->get();

        if ($formPenilaians->isEmpty()) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $form = $formPenilaians->first();

        $quartal = $form->quartal;
        $semesterLabel = '';

        if (in_array($quartal, ['Q1', 'Q2'])) {
            $semesterLabel = 'S1';
        } elseif (in_array($quartal, ['Q3', 'Q4'])) {
            $semesterLabel = 'S2';
        } else {
            $semesterLabel = $quartal;
        }

        $evaluated = [
            'nama'        => optional($form->karyawan)->nama_lengkap . ' - ' . (optional($form->karyawan)->divisi ?? '-'),
            'id_karyawan' => $form->id_karyawan,
            'quartal'     => $semesterLabel,
            'tahun'       => $form->tahun,
            'catatan'     => $form->catatan,
            'kode_form'   => $form->kode_form
        ];

        $currentMonth = now()->month;
        $currentQuartal = match (true) {
            $currentMonth >= 1 && $currentMonth <= 3 => [1, 2, 3],
            $currentMonth >= 4 && $currentMonth <= 6 => [4, 5, 6],
            $currentMonth >= 7 && $currentMonth <= 9 => [7, 8, 9],
            default => [10, 11, 12],
        };

        $dataAbsensi = AbsensiKaryawan::where('id_karyawan', $form->id_karyawan)
            ->whereIn(DB::raw('MONTH(created_at)'), $currentQuartal)
            ->get();

        $telat = $dataAbsensi->where('keterangan', 'Telat')->count();
        $izin  = $dataAbsensi->where('keterangan', 'Izin')->count();
        $sakit = $dataAbsensi->where('keterangan', 'Sakit')->count();

        $dataAbsen = [
            'sakit' => $sakit,
            'telat' => $telat,
            'izin'  => $izin
        ];

        $allKategoriKPIs = $formPenilaians->flatMap(function ($form) {
            return kategoriKPI::where('kode_kategori', $form->kode_kategori)->get();
        })->unique('judul_kategori')->values();

        $allEvaluatorData = $formPenilaians->flatMap(function ($form) {
            return shareForm::with('evaluator')
                ->where('id_evaluated', $form->id_karyawan)
                ->where('kode_form', $form->kode_form)
                ->get();
        });

        $evaluatorList = [];

        foreach ($allEvaluatorData as $evaluatorItem) {
            $jenis_penilaian = $evaluatorItem->jenis_penilaian;
            $id_evaluator = $evaluatorItem->id_evaluator;

            $listNilaiEvaluator = [];

            $nilaiKPIByEvaluator = nilaiKPI::where('id_evaluated', $id_karyawan)
                ->where('kode_form', $kodeForm)
                ->where('id_evaluator', $id_evaluator)
                ->where('jenis_penilaian', $jenis_penilaian)
                ->get();

            $groupedByKategori = $nilaiKPIByEvaluator->groupBy('name_variabel');

            foreach ($allKategoriKPIs as $kategori) {
                $judul_kategori = $kategori->judul_kategori;
                $nilaiItem = $groupedByKategori->get($judul_kategori);

                if ($nilaiItem && $nilaiItem->count() > 0) {
                    $firstItem = $nilaiItem->first();
                    $listNilaiEvaluator[] = [
                        'pesan' => $firstItem->pesan ?? '-',
                        'nilai' => $firstItem->nilai ?? '-'
                    ];
                } else {
                    $listNilaiEvaluator[] = [
                        'pesan' => '-',
                        'nilai' => '-'
                    ];
                }
            }

            $evaluatorList[] = [
                'nama'            => optional($evaluatorItem->evaluator)->nama_lengkap . ' - ' . (optional($evaluatorItem->evaluator)->divisi ?? '-'),
                'jenis_penilaian' => $evaluatorItem->jenis_penilaian ?? '-',
                'nilai'           => $listNilaiEvaluator
            ];
        }

        $evaluatorList = collect($evaluatorList)
            ->unique(fn($item) => $item['nama'] . $item['jenis_penilaian'])
            ->values();

        $dataKriteria = $formPenilaians
            ->groupBy(fn($item) => $item->kode_form . '|' . $item->nama_penilaian)
            ->map(function ($groupedForms, $combinedKey) {
                [$kodeForm, $namaPenilaian] = explode('|', $combinedKey);

                $kategoriKPIs = $groupedForms->flatMap(function ($form) {
                    return kategoriKPI::where('kode_kategori', $form->kode_kategori)->get();
                })->unique('judul_kategori')->values();

                $detailKriteria = $kategoriKPIs->map(function ($kategori) {
                    $tipeDetails = tipeKategoriTabel::where('id_kategori', $kategori->id)->get();

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
            })
            ->values()
            ->toArray();

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
        $request->validate([
            'id_karyawan' => 'required|integer',
            'tahun'       => 'nullable',
        ]);

        $id_karyawan = $request->input('id_karyawan');
        $tahunFilter = (int) $request->input('tahun', now()->year);

        $allFormPenilaians = formPenilaian::where('id_karyawan', $id_karyawan)
            ->where('jenis_form', 'Rutin')
            ->get();

        if ($allFormPenilaians->isEmpty()) {
            return response()->json(['chartQuartal' => [], 'chartAllYears' => []]);
        }

        $uniqueFormGroups = $allFormPenilaians->unique(fn($item) => $item->tahun . '|' . $item->quartal . '|' . $item->kode_form)
            ->values();

        $persentaseJenis = [
            'General Manager' => 35,
            'Manager/SPV/Team Leader (Atasan Langsung)' => 30,
            'Rekan Kerja (Satu Divisi)' => 20,
            'Pekerja (Beda Divisi)' => 10,
            'Self Apprisial' => 5,
        ];

        $allResults = [];

        foreach ($uniqueFormGroups as $group) {
            $tahun    = (int) $group->tahun;
            $quartal  = $group->quartal;
            $kodeForm = $group->kode_form;

            $kategoriKpis = $allFormPenilaians->where('tahun', $tahun)
                ->where('quartal', $quartal)
                ->where('kode_form', $kodeForm)
                ->pluck('kode_kategori')
                ->unique();

            $allKategori = kategoriKPI::whereIn('kode_kategori', $kategoriKpis)
                ->where('tipe_kategori', '!=', 'textarea')
                ->get()
                ->keyBy('judul_kategori');

            $evaluators = shareForm::where('id_evaluated', $id_karyawan)
                ->where('kode_form', $kodeForm)
                ->get()
                ->groupBy('jenis_penilaian');

            $nilaiAll = NilaiKPI::where('id_evaluated', $id_karyawan)
                ->where('kode_form', $kodeForm)
                ->where('status', '1')
                ->get();

            $totalSkorAkhirKaryawan = 0;

            foreach ($evaluators as $jenisPenilaian => $evaluatorGroup) {
                $bobotJenis = $persentaseJenis[$jenisPenilaian] ?? 0;
                if ($bobotJenis === 0) continue;

                $skorJenisPenilaian = 0;

                foreach ($allKategori as $judulKategori => $kategori) {
                    $nilaiPerEvaluator = [];

                    foreach ($evaluatorGroup as $evaluatorItem) {
                        $itemNilai = $nilaiAll->first(function ($it) use ($evaluatorItem, $judulKategori, $jenisPenilaian) {
                            return $it->id_evaluator == $evaluatorItem->id_evaluator
                                && $it->jenis_penilaian == $jenisPenilaian
                                && $it->name_variabel == $judulKategori
                                && is_numeric($it->nilai);
                        });

                        if ($itemNilai) {
                            $nilaiPerEvaluator[] = (float) $itemNilai->nilai;
                        }
                    }

                    $avgNilaiSub = count($nilaiPerEvaluator) > 0
                        ? array_sum($nilaiPerEvaluator) / count($nilaiPerEvaluator)
                        : 0;

                    $bobotKategori = (float) $kategori->bobot;
                    $skorJenisPenilaian += $avgNilaiSub * ($bobotKategori / 100);
                }

                $totalSkorAkhirKaryawan += ($skorJenisPenilaian * $bobotJenis) / 100;
            }

            $allResults[$tahun][$quartal][$kodeForm] = number_format($totalSkorAkhirKaryawan, 2, '.', '');
        }

        $chartQuartal = [];
        $chartAllYears = $allResults;

        if (isset($allResults[$tahunFilter])) {
            $chartQuartal = $allResults[$tahunFilter];
        }

        if (empty($chartQuartal) && !empty($allResults)) {
            $latestYear = max(array_keys($allResults));
            $chartQuartal = $allResults[$latestYear];
        }

        $finalChartQuartal = [];
        foreach (['Q1', 'Q2', 'Q3', 'Q4'] as $q) {
            if (isset($chartQuartal[$q])) {
                $finalChartQuartal[$q] = $chartQuartal[$q];
            }
        }

        return response()->json([
            'chartQuartal'  => $finalChartQuartal,
            'chartAllYears' => $chartAllYears,
        ]);
    }


    public function indexBerandaKpi()
    {
        return view('databasekpi.dashboard');
    }

    public function penilaianReview(Request $request)
    {
        $request->validate([
            'id_nilai.*' => 'required|integer',
            'nilai.*'    => 'required|integer',
        ]);

        foreach ($request->id_nilai as $index => $id) {
            $nilaiModel = NilaiKPI::find($id);
            if ($nilaiModel) {
                $nilaiModel->nilai = $request->nilai[$index];
                $nilaiModel->status = '1';
                $nilaiModel->save();
            }
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
        $id_evaluator = Auth::user()->karyawan->id;
        $jenis_penilaian = $request->input('jenis_penilaian');
        $quartal = $request->input('quartal');
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
            ->where('quartal', $quartal)
            ->where('tahun', $tahun)
            ->select('kode_kategori')
            ->get();

        if ($formInfo->isEmpty()) {
            return redirect()->back()->with('error', 'Data penilaian tidak valid.');
        }

        $kategoriYangDigunakan = $formInfo->pluck('kode_kategori')->toArray();

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

                    $records = nilaiKPI::where('kode_form', $kode_form)
                        ->where('id_evaluator', $id_evaluator)
                        ->where('id_evaluated', $id_evaluated)
                        ->whereIn('kode_kategori', $kategoriYangDigunakan)
                        ->where('jenis_penilaian', $jenis_penilaian)
                        ->where('name_variabel', $labelReadable)
                        ->get();

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

            $currentIndex = $allForms->search(function ($item) use ($jenis_penilaian) {
                return $item->jenis_penilaian === $jenis_penilaian;
            });

            $completedCount = nilaiKPI::where('kode_form', $kode_form)
                ->where('id_evaluator', $id_evaluator)
                ->where('id_evaluated', $id_evaluated)
                ->whereNotNull('finished_at')
                ->distinct('jenis_penilaian')
                ->count();

            $evaluatedEmployee = Karyawan::find($id_evaluated);
            $evaluatedName = $evaluatedEmployee ? $evaluatedEmployee->nama_lengkap : 'Pegawai';

            if ($completedCount >= $allForms->count()) {
                return redirect()->route('penilaian.shareUser', [
                    'id_evaluator' => $id_evaluator,
                ])->with('completed_all', true)
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
        $result = nilaiKPI::select('name_variabel', DB::raw('AVG(nilai) as average'))
            ->where('kode_form', $kode_form)
            ->where('id_evaluated', $id_evaluated)
            ->whereNotNull('nilai')
            ->groupBy('name_variabel')
            ->get();

        return $result;
    }

    public function reviewPenilaian($kodeForm, $evaluatorId, $jenis_penilaian, $idKaryawan)
    {
        $kode_form = $kodeForm;
        $id_evaluator = $evaluatorId;
        $id_karyawan = $idKaryawan;

        if ($jenis_penilaian === 'J01P') {
            $jenis_penilaian = 'General Manager';
        } else if ($jenis_penilaian === 'J02P') {
            $jenis_penilaian = 'Manager/SPV/Team Leader (Atasan Langsung)';
        } else if ($jenis_penilaian === 'J03P') {
            $jenis_penilaian = 'Rekan Kerja (Satu Divisi)';
        } else if ($jenis_penilaian === 'J04P') {
            $jenis_penilaian = 'Pekerja (Beda Divisi)';
        } else if ($jenis_penilaian === 'J05P') {
            $jenis_penilaian = 'Self Apprisial';
        } else {
            $jenis_penilaian = 'not_found';
        }


        $dataEvaluator = shareForm::where('kode_form', $kode_form)
            ->where('id_evaluator', $id_evaluator)
            ->where('jenis_penilaian', $jenis_penilaian)
            ->first();

        if (!$dataEvaluator) {
            return response()->json([
                'status'  => false,
                'message' => 'Data evaluator tidak ditemukan.'
            ], 404);
        }

        $dataForm = formPenilaian::where('kode_form', $kode_form)
            ->where('id_karyawan', $id_karyawan)
            ->get();

        if ($dataForm->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'Data form penilaian tidak ditemukan.'
            ], 404);
        }

        $result = [];
        $statusForm = false;
        $statusPenilaian = false;

        foreach ($dataForm as $form) {
            $kategoriList = kategoriKPI::where('kode_kategori', $form->kode_kategori)->get();

            foreach ($kategoriList as $kategori) {
                $tipeKategori = tipeKategoriTabel::where('id_kategori', $kategori->id)->get();

                $nilai = nilaiKPI::where('kode_form', $kode_form)
                    ->where('id_evaluator', $id_evaluator)
                    ->where('id_evaluated', $id_karyawan)
                    ->where('jenis_penilaian', $jenis_penilaian)
                    ->where('kode_kategori', $kategori->kode_kategori)
                    ->where('name_variabel', $kategori->judul_kategori)
                    ->first();

                $statusPenilaian = is_null($nilai?->finished_at);
                $statusForm = $nilai !== null ? true : false;

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
            'kode_form'       => $kode_form,
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
            'divisi'            => 'required|array',
            'divisi.*'          => 'string',
            'kode_form'         => 'required|string',
            'id_evaluated'      => 'required|integer',
            'jenis_penilaian'   => 'required|string',
            'jenis_form'        => 'required|string',
        ]);

        $id_evaluator_array = $request->input('id_karyawan');
        $divisi_array       = $request->input('divisi');
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
        $quarter = match (true) {
            $month >= 1 && $month <= 3 => [1, 2, 3],
            $month >= 4 && $month <= 6 => [4, 5, 6],
            $month >= 7 && $month <= 9 => [7, 8, 9],
            default => [10, 11, 12],
        };

        $processedPairs = [];

        foreach ($id_evaluator_array as $id_evaluator) {
            $karyawan = Karyawan::find($id_evaluator);
            if (!$karyawan) continue;

            $isGM = strtoupper($karyawan->jabatan) === 'GM';
            $divisiEvaluators = $isGM ? [$karyawan->divisi] : $divisi_array;

            foreach ($divisiEvaluators as $divisi) {
                $pairKey = $id_evaluator . '-' . $divisi;

                if (in_array($pairKey, $processedPairs)) continue;
                $processedPairs[] = $pairKey;

                $alreadyShared = shareForm::where('id_evaluator', $id_evaluator)
                    ->where('id_evaluated', $id_evaluated)
                    ->where('kode_form', $kode_form)
                    ->where('jenis_penilaian', $jenis_penilaian)
                    ->whereMonth('created_at', $month)
                    ->exists();

                if ($alreadyShared) {
                    if (
                        !(strtoupper($karyawan->jabatan) === 'GM' &&
                            in_array($jenis_penilaian, [
                                'General Manager',
                                'Manager/SPV/Team Leader (Atasan Langsung)'
                            ])
                        )
                    ) {
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

                $nilaiExists = NilaiKPI::where('id_evaluator', $id_evaluator)
                    ->where('id_evaluated', $id_evaluated)
                    ->where('kode_form', $kode_form)
                    ->where('jenis_penilaian', $jenis_penilaian)
                    ->whereMonth('created_at', $month)
                    ->exists();

                if (!$nilaiExists) {
                    foreach ($dataKategori as $kategori) {
                        NilaiKPI::create([
                            'id_evaluator'    => $id_evaluator,
                            'id_evaluated'    => $id_evaluated,
                            'kode_form'       => $kode_form,
                            'kode_kategori'   => $kategori->kode_kategori,
                            'name_variabel'   => $kategori->judul_kategori,
                            'jenis_penilaian' => $jenis_penilaian,
                            'status'          => '0',
                        ]);
                    }
                }

                $users = User::whereHas('karyawan', function ($q) use ($karyawan) {
                    $q->where('karyawan_id', $karyawan->id);
                })->get();

                $quarterLabel = match (true) {
                    $month >= 1 && $month <= 3 => 'Q1',
                    $month >= 4 && $month <= 6 => 'Q2',
                    $month >= 7 && $month <= 9 => 'Q3',
                    default => 'Q4',
                };

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

    public function kategoriStore(Request $request)
    {
        $input = $request->all();

        foreach ($input['kriteria'] ?? [] as $i => $kriteria) {
            foreach ($kriteria['sub_kriteria'] ?? [] as $j => $sub) {
                if (empty($input['kriteria'][$i]['sub_kriteria'][$j]['level'])) {
                    $input['kriteria'][$i]['sub_kriteria'][$j]['level'] = '1';
                }
            }
        }

        $request->merge($input);

        $request->validate([
            'id_karyawan'                                 => 'required|array|min:1',
            'kriteria'                                    => 'required|array|min:1',
            'kriteria.*.nama_penilaian'                   => 'required|string',
            'kriteria.*.sub_kriteria'                     => 'required|array|min:1',
            'kriteria.*.sub_kriteria.*.judul_kategori'    => 'required|string',
            'kriteria.*.sub_kriteria.*.tipe_kategori'     => 'required|string',
            'kriteria.*.sub_kriteria.*.level'             => 'required|string',
            'kriteria.*.sub_kriteria.*.bobot'             => 'required|numeric',
            'kriteria.*.sub_kriteria.*.ket_tipe'          => 'nullable|array',
            'kriteria.*.sub_kriteria.*.ket_tipe.*'        => 'nullable|string',
            'kriteria.*.sub_kriteria.*.nilai_ket_tipe'    => 'nullable|array',
            'kriteria.*.sub_kriteria.*.nilai_ket_tipe.*'  => 'nullable|string',
            'jenis_form'                                  => 'required|string',
        ]);

        $id_karyawan_array   = $request->input('id_karyawan');
        $all_kriteria_data   = $request->input('kriteria');
        $kodeFormPenilaian   = Str::random(20);
        $month               = now()->month;
        $year                = now()->year;
        $jenis_form          = $request->input('jenis_form');

        $quarterLabel = match (true) {
            $month >= 1 && $month <= 3 => 'Q1',
            $month >= 4 && $month <= 6 => 'Q2',
            $month >= 7 && $month <= 9 => 'Q3',
            $month >= 10 && $month <= 12 => 'Q4',
            default => null
        };

        if (!$quarterLabel) {
            return redirect()->back()->with('error', 'Quarter tidak terdeteksi');
        }

        DB::beginTransaction();
        try {
            foreach ($id_karyawan_array as $id_karyawan) {
                foreach ($all_kriteria_data as $kriteriaData) {
                    $kodeKategori = Str::random(15);
                    $nama_penilaian_utama = $kriteriaData['nama_penilaian'];

                    $form = new formPenilaian();
                    $form->id_karyawan    = $id_karyawan;
                    $form->kode_form      = $kodeFormPenilaian;
                    $form->kode_kategori  = $kodeKategori;
                    $form->nama_penilaian = $nama_penilaian_utama;
                    $form->quartal        = $quarterLabel;
                    $form->tahun          = $year;
                    $form->jenis_form     = $jenis_form;
                    $form->save();

                    foreach ($kriteriaData['sub_kriteria'] as $subKriteriaData) {
                        $kategori = new kategoriKPI();
                        $kategori->judul_kategori = $subKriteriaData['judul_kategori'];
                        $kategori->tipe_kategori  = $subKriteriaData['tipe_kategori'];
                        $kategori->level          = $subKriteriaData['level'];
                        $kategori->bobot          = $subKriteriaData['bobot'];
                        $kategori->kode_kategori  = $kodeKategori;
                        $kategori->save();

                        if (in_array($subKriteriaData['tipe_kategori'], ['radio', 'select', 'checkbox'])) {
                            $ket_tipe_for_sub = $subKriteriaData['ket_tipe'] ?? [];
                            $nilai_ket_tipe_for_sub = $subKriteriaData['nilai_ket_tipe'] ?? [];

                            foreach ($ket_tipe_for_sub as $j => $ket) {
                                if (!is_null($ket) && $ket !== '') {
                                    $tipe = new tipeKategoriTabel();
                                    $tipe->id_kategori    = $kategori->id;
                                    $tipe->ket_tipe       = $ket;
                                    $tipe->nilai_ket_tipe = array_key_exists($j, $nilai_ket_tipe_for_sub) ? $nilai_ket_tipe_for_sub[$j] : null;
                                    $tipe->save();
                                }
                            }
                        }
                    }
                }
            }

            DB::commit();
            return back()->with('success', 'Berhasil disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

        public function getFromPenilaian(Request $request, $kode_form, $id_karyawan)
        {
            $evaluatedEmployee = Karyawan::find($id_karyawan);
            if (!$evaluatedEmployee) {
                return redirect()->back();
            }

            $id_evaluator = Auth::user()->karyawan->id;

            $sharedForms = shareForm::where('kode_form', $kode_form)
                ->where('id_evaluated', $id_karyawan)
                ->where('id_evaluator', $id_evaluator)
                ->get();

            if ($sharedForms->isEmpty()) {
                return view('databasekpi.formPenilaian', [
                    'outputData' => [],
                    'evaluatedEmployee' => $evaluatedEmployee,
                    'isEvaluator' => false
                ]);
            }

            $formPenilaians = formPenilaian::where('kode_form', $kode_form)
                ->where('id_karyawan', $id_karyawan)
                ->get();

            if ($formPenilaians->isEmpty()) {
                return view('databasekpi.formPenilaian', [
                    'outputData' => [],
                    'evaluatedEmployee' => $evaluatedEmployee,
                    'isEvaluator' => false
                ]);
            }

            $outputData = [];

            foreach ($sharedForms as $shared) {

                $formFiltered = $formPenilaians->filter(function ($item) use ($shared) {
                    return $item->jenis_penilaian === $shared->jenis_penilaian;
                });

                $totalKriteria = kategoriKPI::whereIn('kode_kategori', $formFiltered->pluck('kode_kategori'))->count();

                $kategoriIds = $formPenilaians->pluck('kode_kategori');

                $filledKategori = nilaiKPI::where('kode_form', $shared->kode_form)
                    ->where('id_evaluator', $id_evaluator)
                    ->where('id_evaluated', $evaluatedEmployee->id)
                    ->where('jenis_penilaian', $shared->jenis_penilaian)
                    ->whereIn('kode_kategori', $kategoriIds)
                    ->pluck('kode_kategori')
                    ->unique();

                dd($filledKategori);

                $remaining = $kategoriIds->diff($filledKategori);

                if ($remaining->isEmpty()) {
                    continue;
                }
                $temp = [
                    'form_penilaian_id' => $formPenilaians->first()->id,
                    'kode_form_global' => $kode_form,
                    'evaluator' => Auth::user()->karyawan->nama_lengkap,
                    'evaluated' => $evaluatedEmployee->nama_lengkap,
                    'id_karyawan' => $id_karyawan,
                    'jenis_penilaian' => $shared->jenis_penilaian,
                    'quartal' => $formPenilaians->first()->quartal,
                    'tahun' => $formPenilaians->first()->tahun,
                    'detail_kategori' => [],
                ];

                foreach ($formPenilaians as $form) {

                    $kategoriKPIs = kategoriKPI::where('kode_kategori', $form->kode_kategori)
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

                    $temp['detail_kategori'][] = [
                        'kriteria_utama' => $form->nama_penilaian,
                        'isi_kriteria' => $isiKriteria,
                        'kode_kategori_form' => $form->kode_kategori,
                    ];
                }

                $outputData[] = $temp;
            }

            return view('databasekpi.formPenilaian', [
                'outputData' => $outputData,
                'evaluatedEmployee' => $evaluatedEmployee,
                'isEvaluator' => true
            ]);
        }

        public function getFromPenilaianUser(Request $request, $id_evaluator)
        {
            $evaluatorEmploye = Karyawan::find($id_evaluator);
            if (!$evaluatorEmploye) {
                return redirect()->back();
            }

            $currentMonth = now()->month;
            $currentYear = now()->year;

            $currentQuartal = match (true) {
                $currentMonth <= 3 => 'Q1',
                $currentMonth <= 6 => 'Q2',
                $currentMonth <= 9 => 'Q3',
                default => 'Q4',
            };

            $sharedForms = shareForm::where('id_evaluator', $id_evaluator)->get();

            if ($sharedForms->isEmpty()) {
                return view('databasekpi.formPenilaian', [
                    'outputData' => [],
                    'evaluatorEmploye' => $evaluatorEmploye,
                    'isEvaluator' => false,
                ]);
            }

            $grouped = [];

            foreach ($sharedForms as $share) {

                $formPenilaians = formPenilaian::where('kode_form', $share->kode_form)
                    ->where('id_karyawan', $share->id_evaluated)
                    ->where('quartal', $currentQuartal)
                    ->where('tahun', $currentYear)
                    ->get();

                if ($formPenilaians->isEmpty()) {
                    continue;
                }

                $evaluatedEmployee = Karyawan::find($share->id_evaluated);
                $totalKriteria = kategoriKPI::whereIn('kode_kategori', $formPenilaians->pluck('kode_kategori'))->count();
                $formFiltered = $formPenilaians->filter(function ($item) use ($share) {
                    return $item->jenis_penilaian === $share->jenis_penilaian;
                });

                $totalKriteria = kategoriKPI::whereIn('kode_kategori', $formFiltered->pluck('kode_kategori'))->count();

                $kategoriIds = $formPenilaians->pluck('kode_kategori');

                $filledKategori = nilaiKPI::where('kode_form', $share->kode_form)
                    ->where('id_evaluator', $id_evaluator)
                    ->where('id_evaluated', $evaluatedEmployee->id)
                    ->where('jenis_penilaian', $share->jenis_penilaian)
                    ->whereIn('kode_kategori', $kategoriIds)
                    ->pluck('kode_kategori')
                    ->unique();

                $remaining = $kategoriIds->diff($filledKategori);

                if ($remaining->isEmpty()) {
                    continue;
                }
                $key = $share->kode_form . '_' . $evaluatedEmployee->id . '_' . $id_evaluator . '_' . $share->jenis_penilaian . '_' . $currentQuartal . '_' . $currentYear;

                if (!isset($grouped[$key])) {
                    $grouped[$key] = [
                        'form_penilaian_id' => $formPenilaians->first()->id,
                        'kode_form_global' => $share->kode_form,
                        'evaluator' => $evaluatorEmploye->nama_lengkap,
                        'evaluated' => $evaluatedEmployee->nama_lengkap,
                        'id_karyawan' => $evaluatedEmployee->id,
                        'jenis_penilaian' => $share->jenis_penilaian,
                        'quartal' => $currentQuartal,
                        'tahun' => $currentYear,
                        'detail_kategori' => [],
                    ];
                }

                foreach ($formPenilaians as $formItem) {

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

                    $exists = collect($grouped[$key]['detail_kategori'] ?? [])
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
            if ($karyawan->status_aktif === '1') {
                $status = 'Karyawan Aktif';
            } else if ($karyawan->status_aktif === '0') {
                $status = 'Karyawan Non Aktif';
            }

            return [
                'nama_lengkap' => $karyawan->nama_lengkap ?? '-',
                'nip'          => $karyawan->nip ?? '-',
                'divisi'       => $karyawan->divisi ?? '-',
                'jabatan'      => $karyawan->jabatan ?? '-',
                'status'       => $status ?? '-',
            ];
        });

        return response()->json([
            'jumlah' => $jumlah,
            'data' => $data
        ]);
    }

    public function getDataPenilaian()
    {
        $user_id = Auth::user()->id;

        $exchangeSemester = '';
        if (request()->get('quartal') === 'S1') {
            $exchangeSemester = ['Q1', 'Q2'];
        } else if (request()->get('quartal') === 'S2') {
            $exchangeSemester = ['Q3', 'Q4'];
        }

        $filterQuartal = $exchangeSemester;
        $filterTahun = request()->get('tahun');
        $filterDivisi = request()->get('divisi');
        $jenisForm = request()->get('jenis_form');

        $dataFormPenilaianCollection = formPenilaian::with('karyawan')
            ->when($filterQuartal, fn($q) => $q->whereIn('quartal', $exchangeSemester))
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

        foreach ($dataFormPenilaianCollection as $formPenilaian) {
            if ($filterDivisi && $formPenilaian->karyawan->divisi !== $filterDivisi) {
                continue;
            }

            $evaluatedName = $formPenilaian->karyawan->nama_lengkap;
            $evaluatedDivisi = $formPenilaian->karyawan->divisi;
            $exchangeQuartal = '';
            if ($formPenilaian->quartal === 'Q1' || $formPenilaian->quartal === 'Q2') {
                $exchangeQuartal = 'S1';
            }
            if ($formPenilaian->quartal === 'Q3' || $formPenilaian->quartal === 'Q4') {
                $exchangeQuartal = 'S2';
            }
            $quartal = $exchangeQuartal;
            $tahun = $formPenilaian->tahun;
            $kriteriaNama = $formPenilaian->nama_penilaian;
            $kodeFormGlobal = $formPenilaian->kode_form;

            $dataEvaluator = shareForm::with('evaluator')
                ->where('id_evaluated', $formPenilaian->id_karyawan)
                ->where('kode_form', $kodeFormGlobal)
                ->get();

            $evaluatorNamesList = [];
            $evaluatorGroupedByJenis = [];

            foreach ($dataEvaluator as $evaluatorRow) {
                $evaluatorId = $evaluatorRow->id_evaluator;
                $evaluatorName = optional($evaluatorRow->evaluator)->nama_lengkap ?? '-';
                $jenisPenilaian = $evaluatorRow->jenis_penilaian;

                $nilai = nilaiKPI::where('kode_form', $kodeFormGlobal)
                    ->where('id_evaluated', $formPenilaian->id_karyawan)
                    ->where('id_evaluator', $evaluatorId)
                    ->where('jenis_penilaian', $jenisPenilaian)
                    ->first();

                $isRed = $nilai && ($nilai->finished_at == null || $nilai->status == 0);

                $evaluatorData = [
                    'id'     => $evaluatorId,
                    'name'   => $evaluatorName,
                    'is_red' => $isRed,
                ];

                $evaluatorNamesList[] = $evaluatorData;

                if ($jenisPenilaian) {
                    if (!isset($evaluatorGroupedByJenis[$jenisPenilaian])) {
                        $evaluatorGroupedByJenis[$jenisPenilaian] = [];
                    }
                    $evaluatorGroupedByJenis[$jenisPenilaian][] = $evaluatorData;
                }
            }

            $evaluatorIds = collect($evaluatorNamesList)->pluck('id')->unique()->values()->all();
            $jenisPenilaianList = array_keys($evaluatorGroupedByJenis);

            $groupKey = $kodeFormGlobal . '_' . $evaluatedName;

            $records = nilaiKPI::where('kode_form', $kodeFormGlobal)
                ->where('id_evaluated', $formPenilaian->id_karyawan)
                ->get();

            $jenisPenilaianList = $dataEvaluator
                ->pluck('jenis_penilaian')
                ->unique()
                ->filter()
                ->values()
                ->all();

            $status = $records->isNotEmpty() && $records->every(function ($record) {
                return is_null($record->pesan);
            });

            if (!isset($groupedOutputData[$groupKey])) {
                $groupedOutputData[$groupKey] = [
                    'form_penilaian_id'  => $formPenilaian->id,
                    'kode_form'          => $kodeFormGlobal,
                    'kode_form_label'    => $kodeFormMapping[$formPenilaian->kode_form] ?? $formPenilaian->kode_form,
                    'id_karyawan'        => $formPenilaian->id_karyawan,
                    'evaluated'          => $evaluatedName,
                    'evaluatedDivisi'    => $evaluatedDivisi,
                    'tanggal'            => $formPenilaian->created_at->translatedFormat('l, d F Y'),
                    'quartal'            => $quartal,
                    'tahun'              => $tahun,
                    'jenis_penilaian'    => $jenisPenilaianList,
                    'evaluator'          => $evaluatorNamesList,
                    'evaluator_by_jenis' => $evaluatorGroupedByJenis,
                    'id_evaluator'       => $evaluatorIds,
                    'detail_kategori'    => [],
                    'status'             => $status,
                ];
            }

            $kategoriKPIs = kategoriKPI::where('kode_kategori', $formPenilaian->kode_kategori)
                ->with(['tipeKategoriTabels'])
                ->get();

            $isiKriteria = [];

            foreach ($kategoriKPIs as $kategori) {
                $nilaiRecords = nilaiKPI::where('kode_form', $kodeFormGlobal)
                    ->where('kode_kategori', $kategori->kode_kategori)
                    ->where('name_variabel', $kategori->judul_kategori)
                    ->where('id_evaluated', $formPenilaian->id_karyawan)
                    ->whereIn('id_evaluator', $evaluatorIds)
                    ->get();

                $filteredRecords = $nilaiRecords->filter(function ($record) {
                    return !is_null($record->nilai);
                });

                $totalNilai = $filteredRecords->sum('nilai');
                $nilaiFinal = $filteredRecords->isNotEmpty() ? $totalNilai : '-';

                $nilai_akhir = $filteredRecords->isNotEmpty()
                    ? round(($totalNilai * ((float) $kategori->bobot)) / 100, 2)
                    : '-';

                $isiKriteria[] = [
                    'sub_kriteria_id'    => $kategori->id,
                    'sub_kriteria_judul' => $kategori->judul_kategori,
                    'tipe_kategori'      => $kategori->tipe_kategori,
                    'bobot'              => $kategori->bobot,
                    'skor'               => $nilaiFinal,
                    'nilai_akhir'        => $nilai_akhir,
                    'tanggal'            => $kategori->created_at->translatedFormat('l, d F Y'),
                    'keterangan_tipe'    => $kategori->tipeKategoriTabels->map(function ($tipe) {
                        return [
                            'id'    => $tipe->id,
                            'ket'   => $tipe->ket_tipe,
                            'nilai' => $tipe->nilai_ket_tipe
                        ];
                    })->toArray(),
                ];
            }

            $groupedOutputData[$groupKey]['detail_kategori'][] = [
                'kriteria_utama'       => $kriteriaNama,
                'isi_kriteria'         => $isiKriteria,
                'kode_kategori_form'   => $formPenilaian->kode_kategori,
            ];
        }

        $outputData = array_values($groupedOutputData);

        return response()->json([
            'data'     => $outputData,
            'karyawan' => $dataKaryawan
        ]);
    }

    public function index360($id_karyawan)
    {
        return view('databasekpi.penilaian360', compact('id_karyawan'));
    }

    public function get360($id_karyawan)
	{
		$currentTahun = now()->year;

		$formPenilaian = formPenilaian::with('karyawan')
			->where('id_karyawan', $id_karyawan)
			->where('tahun', $currentTahun)
			->get();

		$catatan = $formPenilaian->pluck('catatan')->unique();

		$dataAbsensi = AbsensiKaryawan::where('id_karyawan', $id_karyawan)
			->whereYear('created_at', $currentTahun)
			->get();

		$telat = $dataAbsensi->where('keterangan', 'Telat')->count();
		$izin  = $dataAbsensi->where('keterangan', 'Izin')->count();
		$sakit = $dataAbsensi->where('keterangan', 'Sakit')->count();

		$dataAbsen = [
			'sakit' => $sakit,
			'telat' => $telat,
			'izin'  => $izin
		];

		if ($formPenilaian->isEmpty()) {
			return response()->json(['message' => '!formPenilaian']);
		}

		$allJenisPenilaian = [];
		$kodeFormList = $formPenilaian->pluck('kode_form');
		$kodeKategoriList = $formPenilaian->pluck('kode_kategori');

		$dataKriteria = kategoriKPI::whereIn('kode_kategori', $kodeKategoriList)->get();
		$groupedKriteria = $dataKriteria->groupBy('kode_kategori');

		$allShareForm = shareForm::with('evaluator')
			->where('id_evaluated', $id_karyawan)
			->whereIn('kode_form', $kodeFormList)
			->get()
			->groupBy('jenis_penilaian');

		foreach ($allShareForm as $jenis => $evaluators) {

			$dataEvaluators = [];

			foreach ($evaluators as $evaluator) {

				$dataKriteriaArray = [];

				foreach ($groupedKriteria as $kode_kategori => $subKriterias) {

					$kriteriaNama = $formPenilaian
						->firstWhere('kode_kategori', $kode_kategori)?->nama_penilaian ?? '-';

					$subKriteriaArray = [];

					foreach ($subKriterias as $kriteria) {

						$nilai = NilaiKPI::where('id_evaluator', $evaluator->id_evaluator)
							->where('id_evaluated', $id_karyawan)
							->where('kode_form', $evaluator->kode_form)
							->where('kode_kategori', $kode_kategori)
							->where('name_variabel', $kriteria->judul_kategori)
							->first();

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

		$dataForm = [
			'nama_evaluated' => $formPenilaian->pluck('karyawan.nama_lengkap')->unique()->values(),
			'quartal' => $formPenilaian->pluck('quartal')->unique()->values(),
			'tahun' => $formPenilaian->pluck('tahun')->unique()->values(),
			'data' => $allJenisPenilaian,
			'dataAbsen' => $dataAbsen,
			'catatan' => $catatan
		];

		return response()->json($dataForm);
	}

    public function clean(Request $request)
    {
        $kode_form = $request->input('kode_form');
        $id_karyawan = $request->input('id_karyawan');
        $jenis_form = $request->input('jenis_form');

        $data_evaluated = formPenilaian::where('kode_form', $kode_form)
            ->where('id_karyawan', $id_karyawan)
            ->get();

        if ($data_evaluated->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data penilaian tidak ditemukan.'
            ]);
        }

        $kodeKategoriList = $data_evaluated->pluck('kode_kategori')->unique();
        $kodeFormList = $data_evaluated->pluck('kode_form')->unique();

        $idEvaluators = shareForm::where('id_evaluated', $id_karyawan)
            ->whereIn('kode_form', $kodeFormList)
            ->pluck('id_evaluator');

        if ($idEvaluators->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membersihkan, evaluator tidak ditemukan.'
            ]);
        }

        $dataNilai = NilaiKPI::whereIn('id_evaluator', $idEvaluators)
            ->where('id_evaluated', $id_karyawan)
            ->whereIn('kode_form', $kodeFormList)
            ->whereIn('kode_kategori', $kodeKategoriList)
            ->get();

        $dataShare = shareForm::whereIn('id_evaluator', $idEvaluators)
            ->where('id_evaluated', $id_karyawan)
            ->whereIn('kode_form', $kodeFormList)
            ->get();

        if ($dataNilai->isEmpty() && $dataShare->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak ada data yang cocok untuk dihapus.'
            ]);
        }

        $deletedNilai = NilaiKPI::whereIn('id_evaluator', $idEvaluators)
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
        $quartal = $request->get('quartal');
        $tahun   = $request->get('tahun');

        $query = formPenilaian::with('karyawan');

        if ($quartal) {
            $query->where('quartal', $quartal);
        }

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
                    'quartal'     => $form->quartal,
                    'tahun'       => $form->tahun,
                    'catatan'     => $form->catatan,
                ];
            })->unique('id_karyawan')->values();

            return [
                'kode_form'       => $kodeForm,
                'label_kode_form' => $kodeFormMapping[$kodeForm] ?? $kodeForm,
                'quartal'         => $first->quartal,
                'tahun'           => $first->tahun,
                'evaluated'       => $evaluated
            ];
        })->values();

        return response()->json([
            'data' => $grouped
        ]);
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
            $idKaryawanList = formPenilaian::where('kode_form', $kodeForm)
                ->pluck('id_karyawan')
                ->unique();

            $dataQuartalDanTahun = formPenilaian::where('kode_form', $kodeForm)
                ->select('quartal', 'tahun', 'jenis_form')
                ->first();

            $existingForms = formPenilaian::where('kode_form', $kodeForm)->get();
            $inputFormIds = collect($validated['kriteria'])->pluck('id_nama_penilaian')->filter()->toArray();
            $formsToDelete = $existingForms->filter(function ($form) use ($inputFormIds) {
                return !in_array($form->id, $inputFormIds);
            });

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
                            $form->update([
                                'nama_penilaian' => $namaPenilaian,
                                'jenis_form'     => $jenis_form,
                            ]);

                            $existingKategoriIds = kategoriKPI::where('kode_kategori', $form->kode_kategori)
                                ->pluck('id')
                                ->toArray();
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
                                    foreach ($ket_tipe_list as $i => $ket) {
                                        if (!is_null($ket) && $ket !== '') {
                                            tipeKategoriTabel::create([
                                                'id_kategori'    => $kategori->id,
                                                'ket_tipe'       => $ket,
                                                'nilai_ket_tipe' => $nilai_list[$i] ?? null,
                                            ]);
                                        }
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
                                foreach ($ket_tipe_list as $i => $ket) {
                                    if (!is_null($ket) && $ket !== '') {
                                        tipeKategoriTabel::create([
                                            'id_kategori'    => $kategori->id,
                                            'ket_tipe'       => $ket,
                                            'nilai_ket_tipe' => $nilai_list[$i] ?? null,
                                        ]);
                                    }
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
        $evaluatorCheck = shareForm::where('kode_form', $kode_form)->exists();

        if ($evaluatorCheck) {
            return back()->with('error', 'Jangan lupa dibersihkan terlebih dahulu');
        }

        $allFormIds = formPenilaian::where('kode_form', $kode_form)
            ->pluck('id')
            ->toArray();

        $jenis_form = formPenilaian::where('kode_form', $kode_form)->value('jenis_form');

        $formPenilaianUnique = formPenilaian::where('kode_form', $kode_form)
            ->get()
            ->unique('nama_penilaian')
            ->values();

        $result = [];

        foreach ($formPenilaianUnique as $data) {
            $kategori = kategoriKPI::where('kode_kategori', $data->kode_kategori)->get();

            $kategoriArr = [];
            foreach ($kategori as $itemSub) {
                $dataTipeKategori = tipeKategoriTabel::where('id_kategori', $itemSub->id)->get();

                $tipeKategoriAll = [];
                foreach ($dataTipeKategori as $item) {
                    $tipeKategoriAll[] = [
                        'id'              => $item->id,
                        'keterangan_tipe' => $item->ket_tipe,
                        'nilai_ket_tipe'  => $item->nilai_ket_tipe,
                    ];
                }

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

        $data = [
            'jenis_form' => $jenis_form,
            'kode_form' => $kode_form,
            'result'    => $result,
            'allFormIds' => $allFormIds
        ];

        return view('databasekpi.formEditPenilaian', compact('data'));
    }

    public function hapus(Request $request)
    {
        $kode_form = $request->input('kode_form');
        $id_karyawan = $request->input('id_karyawan');
        $jenis_penilaian = $request->input('jenis_penilaian');
        $quartal = $request->input('quartal');
        $tahun = $request->input('tahun');
        $jenis_form = $request->input('jenis_form');

        $data_evaluated = formPenilaian::where('kode_form', $kode_form)
            ->where('id_karyawan', $id_karyawan)
            ->get();

        if ($data_evaluated->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data penilaian tidak ditemukan.'
            ]);
        }

        $kodeKategoriList = $data_evaluated->pluck('kode_kategori')->unique();
        $kodeFormList = $data_evaluated->pluck('kode_form')->unique();

        $idEvaluators = shareForm::where('id_evaluated', $id_karyawan)
            ->whereIn('kode_form', $kodeFormList)
            ->pluck('id_evaluator');

        $deletedNilai = 0;
        $deletedShare = 0;

        if ($idEvaluators->isNotEmpty()) {
            $deletedNilai = NilaiKPI::whereIn('id_evaluator', $idEvaluators)
                ->where('id_evaluated', $id_karyawan)
                ->whereIn('kode_form', $kodeFormList)
                ->whereIn('kode_kategori', $kodeKategoriList)
                ->delete();

            $deletedShare = shareForm::whereIn('id_evaluator', $idEvaluators)
                ->where('id_evaluated', $id_karyawan)
                ->whereIn('kode_form', $kodeFormList)
                ->delete();
        }

        formPenilaian::where('kode_form', $kode_form)
            ->where('id_karyawan', $id_karyawan)
            ->delete();

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
        switch ($kodeJenis) {
            case 'JP01':
                $jenisPenilaian = 'General Manager';
                break;
            case 'JP02':
                $jenisPenilaian = 'Manager/SPV/Team Leader (Atasan Langsung)';
                break;
            case 'JP03':
                $jenisPenilaian = 'Rekan Kerja (Satu Divisi)';
                break;
            case 'JP04':
                $jenisPenilaian = 'Pekerja (Beda Divisi)'; // ❗ Perbaiki ini!
                break;
            case 'JP05':
                $jenisPenilaian = 'Self Apprisial';
                break;
            default:
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kode jenis penilaian tidak valid: ' . $kodeJenis
                ], 400);
        }

        $deletedShare = shareForm::where('kode_form', $kodeFormGlobal)
            ->where('jenis_penilaian', $jenisPenilaian)
            ->where('id_evaluator', $id_evaluator)
            ->delete();

        $deletedNilai = NilaiKPI::where('kode_form', $kodeFormGlobal)
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

    public function contentDashboard()
    {
        $year = date('Y');
        $month = date('m');

        if ($month >= 1 && $month <= 6) {
            $startMonth = 1;
            $endMonth = 6;
            $semesterLabel = 'S1';
        } else {
            $startMonth = 7;
            $endMonth = 12;
            $semesterLabel = 'S2';
        }

        $startDate = "$year-$startMonth-01 00:00:00";
        $endDate   = "$year-$endMonth-" . date("t", strtotime("$year-$endMonth-01")) . " 23:59:59";

        $jabatanUserLogin = auth()->user()->jabatan;
        $idUserLogin = auth()->user()->id;
        $isPrivileged = in_array($jabatanUserLogin, ['HRD', 'GM', 'Direktur Utama']);

        $totalKaryawan = Karyawan::where('status_aktif', '1')
            ->whereNot('divisi', 'Direksi')
            ->count();

        $applyUserFilter = function ($query) use ($isPrivileged, $idUserLogin) {
            if (!$isPrivileged) {
                $query->where('id_karyawan', $idUserLogin);
            }
            return $query;
        };

        $AbsenCuti = $applyUserFilter(
            pengajuancuti::with('karyawan')
                ->where('tipe', 'Cuti')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('approval_manager', '1')
        )->get();

        $totalAbsensiCuti = $AbsenCuti->pluck('id_karyawan')->unique()->count();
        $arrayAbsenCuti = $AbsenCuti->map(fn($cuti) => [
            'id_karyawan'  => $cuti->id_karyawan,
            'namaKaryawan' => $cuti->karyawan->nama_lengkap,
            'divisi'       => $cuti->karyawan->divisi,
            'alasan'       => $cuti->alasan,
            'tanggalAwal'  => $cuti->tanggal_awal,
            'tanggalAkhir' => $cuti->tanggal_akhir
        ]);

        $dataAbsensiCuti = [
            'totalAbsenCuti' => $totalAbsensiCuti,
            'dataCuti'       => $arrayAbsenCuti
        ];

        $AbsenSakit = $applyUserFilter(
            pengajuancuti::with('karyawan')
                ->where('tipe', 'Sakit')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('approval_manager', '1')
        )->get();

        $totalAbsensiSakit = $AbsenSakit->pluck('id_karyawan')->unique()->count();
        $arrayAbsensiSakit = $AbsenSakit->map(fn($sakit) => [
            'id_karyawan'  => $sakit->id_karyawan,
            'namaKaryawan' => $sakit->karyawan->nama_lengkap,
            'divisi'       => $sakit->karyawan->divisi,
            'alasan'       => $sakit->alasan,
            'tanggalAwal'  => $sakit->tanggal_awal,
            'tanggalAkhir' => $sakit->tanggal_akhir
        ]);

        $dataAbsensiSakit = [
            'totalAbsenSakit' => $totalAbsensiSakit,
            'dataSakit'       => $arrayAbsensiSakit
        ];

        $AbsenIzin = $applyUserFilter(
            izinTigaJam::with('karyawan')
                ->whereBetween('created_at', [$startDate, $endDate])
        )->get();

        $totalAbsenIzin = $AbsenIzin->pluck('id_karyawan')->unique()->count();
        $arrayAbsensiIzin = $AbsenIzin->map(fn($izin) => [
            'id_karyawan'      => $izin->id_karyawan,
            'namaKaryawan'     => $izin->karyawan->nama_lengkap,
            'divisi'           => $izin->karyawan->divisi,
            'alasan'           => $izin->alasan,
            'tanggalPengajuan' => $izin->tanggal_pengajuan,
        ]);

        $dataAbsensiIzin = [
            'totalAbsenIzin' => $totalAbsenIzin,
            'dataIzin'       => $arrayAbsensiIzin
        ];

        $dataCard_utama = [
            'karyawan_aktif' => $totalKaryawan,
            'dataSakit'      => $dataAbsensiSakit,
            'dataCuti'       => $dataAbsensiCuti,
            'dataIzin'       => $dataAbsensiIzin
        ];

        $totalSemua = shareForm::whereBetween('created_at', [$startDate, $endDate]);
        if (!$isPrivileged) $totalSemua->where('id_evaluated', $idUserLogin);
        $totalSemua = $totalSemua->count();

        $totalDilaksanakan = NilaiKPI::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '1')
            ->selectRaw('COUNT(*) as jumlah')
            ->groupBy('id_evaluator', 'id_evaluated', 'kode_form', 'jenis_penilaian');
        if (!$isPrivileged) $totalDilaksanakan->where('id_evaluated', $idUserLogin);
        $totalDilaksanakan = $totalDilaksanakan->get()->count();

        $totalBelumDilaksanakan = NilaiKPI::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '0')
            ->selectRaw('COUNT(*) as jumlah')
            ->groupBy('id_evaluator', 'id_evaluated', 'kode_form', 'jenis_penilaian');
        if (!$isPrivileged) $totalBelumDilaksanakan->where('id_evaluated', $idUserLogin);
        $totalBelumDilaksanakan = $totalBelumDilaksanakan->get()->count();

        $dataChartJumlahPenilaianBerjalan = [
            'totalSemua'          => $totalSemua,
            'totalDilaksanakan'   => $totalDilaksanakan,
            'totalBelumDilaksanakan' => $totalBelumDilaksanakan
        ];

        $buildFormQuery = function ($jenis = null) use ($startDate, $endDate, $isPrivileged, $idUserLogin) {
            $query = formPenilaian::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('COUNT(*) as jumlah')
                ->groupBy('kode_form', 'id_karyawan');

            if ($jenis) {
                $query->where('jenis_form', $jenis);
            }

            if (!$isPrivileged) {
                $query->where('id_karyawan', $idUserLogin);
            }

            return $query->get()->count();
        };

        $dataTotalFormulir    = $buildFormQuery();
        $dataFormulirRutin    = $buildFormQuery('Rutin');
        $dataFormulirProbation = $buildFormQuery('Probation');
        $dataFormulirKontrak  = $buildFormQuery('Kontrak');

        $dataFormulir = [
            'totalFormulir'   => $dataTotalFormulir,
            'totalRutin'      => $dataFormulirRutin,
            'totalProbation'  => $dataFormulirProbation,
            'totalKontrak'    => $dataFormulirKontrak
        ];

        $dataDivisi = Karyawan::whereNot('divisi', 'Direksi')->select('divisi')->distinct();
        if (!$isPrivileged) {
            $dataDivisi->where('id', $idUserLogin);
        }
        $dataDivisi = $dataDivisi->get();

        $quartalList = $semesterLabel === 'S1' ? ['Q1', 'Q2'] : ['Q3', 'Q4'];

        $formPenilaian = formPenilaian::with('karyawan')
            ->where(function ($query) use ($quartalList) {
                foreach ($quartalList as $q) {
                    $query->orWhere('quartal', $q);
                }
            })
            ->where('tahun', $year)
            ->where('jenis_form', 'Rutin')
            ->select('id_karyawan', 'kode_form', 'quartal', 'tahun')
            ->groupBy('id_karyawan', 'kode_form', 'quartal', 'tahun')
            ->get();

        $persentaseJenis = [
            'General Manager' => 35,
            'Manager/SPV/Team Leader (Atasan Langsung)' => 30,
            'Rekan Kerja (Satu Divisi)' => 20,
            'Pekerja (Beda Divisi)' => 10,
            'Self Apprisial' => 5
        ];

        $hasilPenilaian = [];

        foreach ($formPenilaian as $form) {
            $evaluatedId = $form->id_karyawan;
            $kodeForm = $form->kode_form;

            $formsForEvaluated = formPenilaian::where('id_karyawan', $evaluatedId)
                ->where('kode_form', $kodeForm)
                ->where('quartal', $form->quartal)
                ->where('tahun', $form->tahun)
                ->get();

            $allKategori = $formsForEvaluated->flatMap(
                fn($f) =>
                kategoriKPI::where('kode_kategori', $f->kode_kategori)->get()
            )->unique('judul_kategori')->values();

            $evaluators = shareForm::where('id_evaluated', $evaluatedId)
                ->where('kode_form', $kodeForm)
                ->get();

            $skorJenis = [];

            foreach ($evaluators as $eval) {
                $nilaiCollection = NilaiKPI::where('id_evaluator', $eval->id_evaluator)
                    ->where('id_evaluated', $evaluatedId)
                    ->where('kode_form', $kodeForm)
                    ->where('jenis_penilaian', $eval->jenis_penilaian)
                    ->get();

                $totalNilaiEvaluator = 0;

                foreach ($allKategori as $kategori) {
                    $item = $nilaiCollection->first(
                        fn($it) =>
                        isset($it->name_variabel) && trim($it->name_variabel) === trim($kategori->judul_kategori)
                    );

                    if ($item && is_numeric($item->nilai) && is_numeric($kategori->bobot)) {
                        $totalNilaiEvaluator += ((float)$item->nilai) * (((float)$kategori->bobot) / 100);
                    }
                }

                $skorJenis[$eval->jenis_penilaian][] = $totalNilaiEvaluator;
            }

            $totalNilaiEvaluated = 0;

            foreach ($skorJenis as $jenis => $listSkor) {
                $rataRataJenis = count($listSkor) ? array_sum($listSkor) / count($listSkor) : 0;
                $bobotJenis = $persentaseJenis[$jenis] ?? 0;

                if ($bobotJenis == 0) {
                    $jenisNorm = preg_replace('/\s+/', '', strtolower($jenis));
                    foreach ($persentaseJenis as $k => $v) {
                        $kNorm = preg_replace('/\s+/', '', strtolower($k));
                        similar_text($kNorm, $jenisNorm, $percent);
                        if ($kNorm === $jenisNorm || $percent >= 80) {
                            $bobotJenis = $v;
                            break;
                        }
                    }
                }

                $totalNilaiEvaluated += ($rataRataJenis * $bobotJenis) / 100;
            }

            $hasilPenilaian[] = [
                'nama_karyawan' => $form->karyawan->nama_lengkap ?? '',
                'foto'          => $form->karyawan->foto ?? null,
                'divisi'        => $form->karyawan->divisi ?? '',
                'total_nilai'   => round($totalNilaiEvaluated, 2)
            ];
        }

        return response()->json([
            'semester'             => $semesterLabel,
            'dataCard_first'       => $dataCard_utama,
            'dataChartPenilaian'   => $dataChartJumlahPenilaianBerjalan,
            'dataDivisi'           => $dataDivisi,
            'dataRangking'         => $hasilPenilaian,
            'dataFormulir'         => $dataFormulir
        ]);
    }

    public function getDataProfile(Request $request)
    {
        $user = auth()->user()->karyawan_id;

        $karyawan = karyawan::where('id', $user)->first();

        return response()->json([
            'data' => $karyawan
        ]);
    }

    public function indexProject()
    {
        return view('KPIproject.index');
    }

    public function controlProject()
    {
        $idUser = auth()->user()->id;

        $dataUser = user::where('id', $idUser)->first();
        return view('KPIproject.createTugas', compact('dataUser'));
    }

    public function kpiIndex()
    {
        $daftarKaryawan = Karyawan::all();

        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];
        $targets = [10000000, 12000000, 11000000, 13000000, 12500000, 14000000];
        $realisasi = [9500000, 11000000, 9000000, 12500000, 10000000, 13500000];

        $karyawanPerluPerhatian = collect([
            (object)['nama' => 'Andi', 'target' => 10000000, 'pencapaian' => 7500000],
            (object)['nama' => 'Budi', 'target' => 12000000, 'pencapaian' => 9000000],
        ]);

        $karyawanGagalTarget = collect([
            (object)['nama' => 'Cici', 'target' => 10000000, 'pencapaian' => 5000000],
        ]);

        return view('KPIdata.index', compact(
            'daftarKaryawan',
            'karyawanPerluPerhatian',
            'karyawanGagalTarget'
        ))->with('chartData', [
            'labels' => $labels,
            'targets' => $targets,
            'realisasi' => $realisasi
        ]);
    }

    public function kpiOverview()
    {
        return view('kpidata.overview');
    }

    public function createTarget(Request $request)
    {
        $validated = $request->validate([
            'id_pembuat'        => 'required',
            'judul_kpi'         => 'required',
            'jabatan'           => 'required|string',
            'jangka_target'     => 'required|string',
            'detail_jangka'     => 'nullable',
            'tipe_target'       => 'required|string',
            'nilai_target'      => 'required',
            'assistant_route'   => 'required'
        ]);

        $dataDivisi = karyawan::where('jabatan', $validated['jabatan'])->first();

        targetKPI::create([
            'id_assistant'   => null,
            'id_pembuat'     => $validated['id_pembuat'],
            'judul'          => $validated['judul_kpi'],
            'deskripsi'      => $request->input('deskripsi_kpi'),
            'assistant_route' => $validated['assistant_route'],
            'jabatan'        => $validated['jabatan'],
            'divisi'         => $dataDivisi->divisi,
            'jangka_target'  => $validated['jangka_target'],
            'detail_jangka'  => $validated['detail_jangka'],
            'tipe_target'    => $validated['tipe_target'],
            'nilai_target'   => $validated['nilai_target'],
            'status'         => '0',
        ]);

        return response()->json([
            'message' => 'Target berhasil dibuat',
        ], 201);
    }

    public function hapusTarget($id)
    {
        $hapusTarget = targetKPI::where('id', $id)->first();
        $hapusTarget->delete();

        return response()->json(['message' => 'berhasil menghapus target!']);
    }

    public function updateTarget(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer',
            'judul_kpi' => 'required|string|max:255',
            'deskripsi_kpi' => 'nullable|string',
            'tipe_target' => 'required',
            'jangka_target' => 'required|string|in:Tahunan,Quartal,Bulanan,Mingguan',
            'detail_jangka' => 'required|string',
            'nilai_target' => 'required|string',
        ]);

        $cleanNilai = preg_replace('/[^0-9]/', '', $validated['nilai_target']);
        $cleanNilai = $cleanNilai === '' ? 0 : (int) $cleanNilai;

        $data = targetKPI::where('id', $validated['id'])->first();
        $data->update([
            'judul' => $validated['judul_kpi'],
            'deskripsi' => $validated['deskripsi_kpi'],
            'tipe_target' => $validated['tipe_target'],
            'jangka_target' => $validated['jangka_target'],
            'detail_jangka' => $validated['detail_jangka'],
            'nilai_target' => $cleanNilai,
        ]);

        return response()->json([
            'message' => 'Target berhasil diperbarui!',
        ]);
    }

    public function getDataTarget()
    {
        $user = auth()->user();
        $id_pembuat = $user->id;
        $jabatan_pembuat = $user->jabatan;
        $karyawan = karyawan::find($id_pembuat);
        if (!$karyawan) {
            return response()->json(['message' => 'Karyawan tidak ditemukan'], 404);
        }
        $divisi = $karyawan->divisi;
        $dataJabatan = $jabatan_pembuat === 'HRD'
            ? karyawan::whereNotIn('jabatan', ['Direktur Utama', 'Direktur'])->distinct()->pluck('jabatan')
            : karyawan::where('divisi', $divisi)->distinct()->pluck('jabatan');

        $detailList = targetKPI::with('karyawan')
            ->where('id_pembuat', $id_pembuat)
            ->whereYear('created_at', now()->year)
            ->get();

        $data = [
            'detail' => $detailList->map(function ($item) {
                $tenggat_waktu = null;
                switch (strtolower($item->jangka_target)) {
                    case 'tahunan':
                        $year = (int) $item->detail_jangka;
                        $tenggat_waktu = date('Y-m-d', strtotime("last day of December $year"));
                        break;
                    case 'bulanan':
                        [$bulan, $tahun] = array_map('trim', explode('-', str_replace(' ', '', $item->detail_jangka)));
                        $tenggat_waktu = date('Y-m-d', strtotime("last day of $tahun-$bulan"));
                        break;
                    case 'quartal':
                        if (preg_match('/Q\s*(\d)\s*-\s*(\d{4})/i', trim($item->detail_jangka), $matches)) {
                            $quartal = (int) $matches[1];
                            $tahun = (int) $matches[2];
                            $bulan_akhir = match ($quartal) {
                                1 => 3,
                                2 => 6,
                                3 => 9,
                                4 => 12,
                                default => 12
                            };
                            $tenggat_waktu = date('Y-m-t', strtotime("$tahun-$bulan_akhir-01"));
                        }
                        break;
                    case 'mingguan':
                        if (preg_match('/(\d{2})-(\d{2})\s*-\s*(\d{2})-(\d{2})\s*-\s*(\d{4})/', str_replace(' ', '', $item->detail_jangka), $matches)) {
                            $tgl = $matches[3];
                            $bulan = $matches[4];
                            $tahun = $matches[5];
                            $tenggat_waktu = sprintf('%04d-%02d-%02d', $tahun, $bulan, $tgl);
                        }
                        break;
                }

                $progress = null;

                if ($item->assistant_route === 'Kepuasan Pelanggan') {
                    $jangkaFilter = $item->jangka_target;
                    $detailJangka = $item->detail_jangka;
                    $feedbacks = collect();

                    if ($jangkaFilter === 'Tahunan') {
                        $tahun = (int) $detailJangka;
                        $start = "$tahun-01-01";
                        $end = "$tahun-12-31";
                        $feedbacks = Nilaifeedback::with('rkm')->whereBetween('created_at', [$start, $end])->get();
                    } elseif ($jangkaFilter === 'Bulanan') {
                        if (preg_match('/(\d{2})\s*-\s*(\d{4})/', $detailJangka, $matches)) {
                            $bulan = (int) $matches[1];
                            $tahun = (int) $matches[2];
                            $start = date('Y-m-01', strtotime("$tahun-$bulan-01"));
                            $end = date('Y-m-t', strtotime("$tahun-$bulan-01"));
                            $feedbacks = Nilaifeedback::with('rkm')->whereBetween('created_at', [$start, $end])->get();
                        }
                    } elseif (in_array($jangkaFilter, ['Kuartal', 'Quartal'])) {
                        if (preg_match('/Q(\d)\s*-\s*(\d{4})/i', $detailJangka, $matches)) {
                            $kuartal = (int) $matches[1];
                            $tahun = (int) $matches[2];
                            $bulanAwal = ($kuartal - 1) * 3 + 1;
                            $bulanAkhir = $kuartal * 3;
                            $start = date('Y-m-01', strtotime("$tahun-$bulanAwal-01"));
                            $end = date('Y-m-t', strtotime("$tahun-$bulanAkhir-01"));
                            $feedbacks = Nilaifeedback::with('rkm')->whereBetween('created_at', [$start, $end])->get();
                        }
                    } elseif ($jangkaFilter === 'Mingguan') {
                        if (preg_match('/(\d{2})-(\d{2})\s*-\s*(\d{2})-(\d{2})\s*-\s*(\d{4})/', $detailJangka, $matches)) {
                            $start = date('Y-m-d', strtotime("{$matches[5]}-{$matches[2]}-{$matches[1]}"));
                            $end = date('Y-m-d', strtotime("{$matches[5]}-{$matches[4]}-{$matches[3]}"));
                            $feedbacks = Nilaifeedback::with('rkm')->whereBetween('created_at', [$start, $end])->get();
                        }
                    }

                    $groupedFeedbacks = $feedbacks->groupBy(function ($feedback) {
                        return $feedback->rkm->materi->nama_materi . '/' . $feedback->rkm->tanggal_awal;
                    });

                    $averageFeedbacks = [];

                    foreach ($groupedFeedbacks as $group) {
                        $totalFeedbacks = $group->count();

                        $totalM1 = $group->sum('M1');
                        $totalM2 = $group->sum('M2');
                        $totalM3 = $group->sum('M3');
                        $totalM4 = $group->sum('M4');
                        $totalP1 = $group->sum('P1');
                        $totalP2 = $group->sum('P2');
                        $totalP3 = $group->sum('P3');
                        $totalP4 = $group->sum('P4');
                        $totalP5 = $group->sum('P5');
                        $totalP6 = $group->sum('P6');
                        $totalP7 = $group->sum('P7');
                        $totalF1 = $group->sum('F1');
                        $totalF2 = $group->sum('F2');
                        $totalF3 = $group->sum('F3');
                        $totalF4 = $group->sum('F4');
                        $totalF5 = $group->sum('F5');
                        $totalI1 = $group->sum('I1');
                        $totalI2 = $group->sum('I2');
                        $totalI3 = $group->sum('I3');
                        $totalI4 = $group->sum('I4');
                        $totalI5 = $group->sum('I5');
                        $totalI6 = $group->sum('I6');
                        $totalI7 = $group->sum('I7');
                        $totalI8 = $group->sum('I8');
                        $totalI1b = $group->sum('I1b');
                        $totalI2b = $group->sum('I2b');
                        $totalI3b = $group->sum('I3b');
                        $totalI4b = $group->sum('I4b');
                        $totalI5b = $group->sum('I5b');
                        $totalI6b = $group->sum('I6b');
                        $totalI7b = $group->sum('I7b');
                        $totalI8b = $group->sum('I8b');
                        $totalI1as = $group->sum('I1as');
                        $totalI2as = $group->sum('I2as');
                        $totalI3as = $group->sum('I3as');
                        $totalI4as = $group->sum('I4as');
                        $totalI5as = $group->sum('I5as');
                        $totalI6as = $group->sum('I6as');
                        $totalI7as = $group->sum('I7as');
                        $totalI8as = $group->sum('I8as');

                        $averageM = round(($totalM1 + $totalM2 + $totalM3 + $totalM4) / ($totalFeedbacks * 4), 1);
                        $averageP = round(($totalP1 + $totalP2 + $totalP3 + $totalP4 + $totalP5 + $totalP6 + $totalP7) / ($totalFeedbacks * 7), 1);
                        $averageF = round(($totalF1 + $totalF2 + $totalF3 + $totalF4 + $totalF5) / ($totalFeedbacks * 5), 1);
                        $averageI = round(($totalI1 + $totalI2 + $totalI3 + $totalI4 + $totalI5 + $totalI6 + $totalI7 + $totalI8) / ($totalFeedbacks * 8), 1);
                        $averageIb = round(($totalI1b + $totalI2b + $totalI3b + $totalI4b + $totalI5b + $totalI6b + $totalI7b + $totalI8b) / ($totalFeedbacks * 8), 1);
                        $averageIas = round(($totalI1as + $totalI2as + $totalI3as + $totalI4as + $totalI5as + $totalI6as + $totalI7as + $totalI8as) / ($totalFeedbacks * 8), 1);

                        $averageValues = [$averageM, $averageP, $averageF, $averageI];
                        if ($averageIb > 0) $averageValues[] = $averageIb;
                        if ($averageIas > 0) $averageValues[] = $averageIas;
                        $averageTotal = round(array_sum($averageValues) / count($averageValues), 1);

                        $averageFeedbacks[] = $averageTotal;
                    }

                    $total = count($averageFeedbacks);
                    if ($total > 0) {
                        $above = count(array_filter($averageFeedbacks, fn($v) => $v >= 3.5));
                        $progress = round(($above / $total) * 100, 1);
                    } else {
                        $progress = 0;
                    }
                }

                return [
                    'id' => $item->id,
                    'pembuat' => $item->karyawan->nama_lengkap ?? null,
                    'id_pembuat' => $item->id_pembuat,
                    'judul' => $item->judul,
                    'deskripsi' => $item->deskripsi,
                    'jabatan' => $item->jabatan,
                    'divisi' => $item->divisi,
                    'assistant_route' => $item->assistant_route,
                    'jangka_target' => $item->jangka_target,
                    'detail_jangka' => $item->detail_jangka,
                    'tipe_target' => $item->tipe_target,
                    'nilai_target' => $item->nilai_target,
                    'status' => $item->status,
                    'created_at' => $item->created_at,
                    'tenggat_waktu' => $tenggat_waktu,
                    'progress' => $progress,
                ];
            }),
            'jabatan_list' => $dataJabatan,
        ];

        return response()->json($data);
    }

    public function detailData(Request $request)
    {
        $id = $request->input('id');

        $dataFindTarget = targetKPI::where('id', $id)->first();

        if ($dataFindTarget->assistant_route === 'Kepuasan Pelanggan') {
            $jangkaFilter = $dataFindTarget->jangka_target;
            $detailJangka = $dataFindTarget->detail_jangka;

            if ($jangkaFilter === 'Tahunan') {
                $tahun = (int) $detailJangka;
                $start = "$tahun-01-01";
                $end = "$tahun-12-31";

                $feedbacks = Nilaifeedback::with('rkm')
                    ->whereBetween('created_at', [$start, $end])
                    ->get();
            } elseif ($jangkaFilter === 'Bulanan') {
                if (preg_match('/(\d{2})\s*-\s*(\d{4})/', $detailJangka, $matches)) {
                    $bulan = (int) $matches[1];
                    $tahun = (int) $matches[2];
                    $start = date('Y-m-01', strtotime("$tahun-$bulan-01"));
                    $end = date('Y-m-t', strtotime("$tahun-$bulan-01"));
                }

                $feedbacks = Nilaifeedback::with('rkm')
                    ->whereBetween('created_at', [$start, $end])
                    ->get();
            } elseif ($jangkaFilter === 'Kuartal' || $jangkaFilter === 'Quartal') {
                if (preg_match('/Q(\d)\s*-\s*(\d{4})/i', $detailJangka, $matches)) {
                    $kuartal = (int) $matches[1];
                    $tahun = (int) $matches[2];

                    $bulanAwal = ($kuartal - 1) * 3 + 1;
                    $bulanAkhir = $kuartal * 3;

                    $start = date('Y-m-01', strtotime("$tahun-$bulanAwal-01"));
                    $end = date('Y-m-t', strtotime("$tahun-$bulanAkhir-01"));
                }

                $feedbacks = Nilaifeedback::with('rkm')
                    ->whereBetween('created_at', [$start, $end])
                    ->get();
            } elseif ($jangkaFilter === 'Mingguan') {
                if (preg_match('/(\d{2})-(\d{2})\s*-\s*(\d{2})-(\d{2})\s*-\s*(\d{4})/', $detailJangka, $matches)) {
                    $start = date('Y-m-d', strtotime("{$matches[5]}-{$matches[2]}-{$matches[1]}"));
                    $end = date('Y-m-d', strtotime("{$matches[5]}-{$matches[4]}-{$matches[3]}"));
                }

                $feedbacks = Nilaifeedback::with('rkm')
                    ->whereBetween('created_at', [$start, $end])
                    ->get();
            }


            $groupedFeedbacks = $feedbacks->groupBy(function ($feedback) {
                return $feedback->rkm->materi->nama_materi . '/' . $feedback->rkm->tanggal_awal;
            });

            $averageFeedbacks = [];

            foreach ($groupedFeedbacks as $materi_key => $feedbackGroup) {
                $materi_key = $feedbackGroup->first()->rkm->materi_key;
                $nama_materi = $feedbackGroup->first()->rkm->materi->nama_materi;
                $instruktur_key = $feedbackGroup->first()->rkm->instruktur_key;
                $sales_key = $feedbackGroup->first()->rkm->sales_key;
                $created_at = $feedbackGroup->first()->created_at;
                $tanggal_awal = Carbon::parse($feedbackGroup->first()->rkm->tanggal_awal)->format('Y-m-d');
                $tanggal_akhir = $feedbackGroup->first()->rkm->tanggal_akhir;
                $totalFeedbacks = $feedbackGroup->count();

                $totalM1 = $feedbackGroup->sum('M1');
                $totalM2 = $feedbackGroup->sum('M2');
                $totalM3 = $feedbackGroup->sum('M3');
                $totalM4 = $feedbackGroup->sum('M4');
                $totalP1 = $feedbackGroup->sum('P1');
                $totalP2 = $feedbackGroup->sum('P2');
                $totalP3 = $feedbackGroup->sum('P3');
                $totalP4 = $feedbackGroup->sum('P4');
                $totalP5 = $feedbackGroup->sum('P5');
                $totalP6 = $feedbackGroup->sum('P6');
                $totalP7 = $feedbackGroup->sum('P7');
                $totalF1 = $feedbackGroup->sum('F1');
                $totalF2 = $feedbackGroup->sum('F2');
                $totalF3 = $feedbackGroup->sum('F3');
                $totalF4 = $feedbackGroup->sum('F4');
                $totalF5 = $feedbackGroup->sum('F5');
                $totalI1 = $feedbackGroup->sum('I1');
                $totalI2 = $feedbackGroup->sum('I2');
                $totalI3 = $feedbackGroup->sum('I3');
                $totalI4 = $feedbackGroup->sum('I4');
                $totalI5 = $feedbackGroup->sum('I5');
                $totalI6 = $feedbackGroup->sum('I6');
                $totalI7 = $feedbackGroup->sum('I7');
                $totalI8 = $feedbackGroup->sum('I8');
                $totalI1b = $feedbackGroup->sum('I1b');
                $totalI2b = $feedbackGroup->sum('I2b');
                $totalI3b = $feedbackGroup->sum('I3b');
                $totalI4b = $feedbackGroup->sum('I4b');
                $totalI5b = $feedbackGroup->sum('I5b');
                $totalI6b = $feedbackGroup->sum('I6b');
                $totalI7b = $feedbackGroup->sum('I7b');
                $totalI8b = $feedbackGroup->sum('I8b');
                $totalI1as = $feedbackGroup->sum('I1as');
                $totalI2as = $feedbackGroup->sum('I2as');
                $totalI3as = $feedbackGroup->sum('I3as');
                $totalI4as = $feedbackGroup->sum('I4as');
                $totalI5as = $feedbackGroup->sum('I5as');
                $totalI6as = $feedbackGroup->sum('I6as');
                $totalI7as = $feedbackGroup->sum('I7as');
                $totalI8as = $feedbackGroup->sum('I8as');

                $averageM = round(($totalM1 + $totalM2 + $totalM3 + $totalM4) / ($totalFeedbacks * 4), 1);
                $averageP = round(($totalP1 + $totalP2 + $totalP3 + $totalP4 + $totalP5 + $totalP6 + $totalP7) / ($totalFeedbacks * 7), 1);
                $averageF = round(($totalF1 + $totalF2 + $totalF3 + $totalF4 + $totalF5) / ($totalFeedbacks * 5), 1);
                $averageI = round(($totalI1 + $totalI2 + $totalI3 + $totalI4 + $totalI5 + $totalI6 + $totalI7 + $totalI8) / ($totalFeedbacks * 8), 1);
                $averageIb = round(($totalI1b + $totalI2b + $totalI3b + $totalI4b + $totalI5b + $totalI6b + $totalI7b + $totalI8b) / ($totalFeedbacks * 8), 1);
                $averageIas = round(($totalI1as + $totalI2as + $totalI3as + $totalI4as + $totalI5as + $totalI6as + $totalI7as + $totalI8as) / ($totalFeedbacks * 8), 1);

                $averageValues = [$averageM, $averageP, $averageF, $averageI];
                if ($averageIb > 0) $averageValues[] = $averageIb;
                if ($averageIas > 0) $averageValues[] = $averageIas;
                $averageTotal = round(array_sum($averageValues) / count($averageValues), 1);

                $averageFeedbacks[] = [
                    'nama_materi' => $nama_materi,
                    'materi_key' => $materi_key,
                    'instruktur_key' => $instruktur_key,
                    'sales_key' => $sales_key,
                    'tanggal_awal' => $tanggal_awal,
                    'tanggal_akhir' => $tanggal_akhir,
                    'created_at' => $created_at,
                    'averageTotal' => $averageTotal,
                ];
            }

            $sortedFeedbacks = collect($averageFeedbacks)->sortByDesc('created_at')->values()->all();

            $totalAllFeedbacks = count($sortedFeedbacks);
            $totalBelow35 = collect($sortedFeedbacks)->where('averageTotal', '<', 3.5)->count();
            $totalAbove35 = collect($sortedFeedbacks)->where('averageTotal', '>=', 3.5)->count();

            $data = [
                'detailData' => $sortedFeedbacks,
                'totalFeedback' => $totalAllFeedbacks,
                'totalKurang' => $totalBelow35,
                'totalLebih' => $totalAbove35,
                'nilaiTarget' => $dataFindTarget->nilai_target,
            ];

            return response()->json($data);
        } else if ($dataFindTarget->assistant_route === 'Pemasukan Kotor') {
        } else if ($dataFindTarget->assistant_route === 'Pemasukan Bersih') {
        } else if ($dataFindTarget->assistant_route === 'Rasio Biaya Operasional') {
        } else if ($dataFindTarget->assistant_route === 'Rata Rata Pencapaian Departement') {
        }
    }
}
