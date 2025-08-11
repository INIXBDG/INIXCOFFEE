<?php

namespace App\Http\Controllers;

use App\Models\formPenilaian;
use App\Models\karyawan;
use App\Models\kategoriKPI;
use App\Models\NilaiKPI;
use App\Models\nilaiKPI as ModelsNilaiKPI;
use App\Models\shareForm;
use App\Models\tipeKategoriTabel;
use App\Models\User;
use App\Notifications\CommentNotification;
use App\Notifications\penilaianExcangheNotifikasi;
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
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

use function Laravel\Prompts\error;

class DatabaseKPIController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:View DatabaseKPI', ['only' => ['index']]);
    }

    public function downloadPDF(Request $request)
    {
        $request->validate([
            'id_karyawan' => 'required',
            'kodeForm'    => 'required|string'
        ]);

        $id_karyawan = $request->input('id_karyawan');
        $kodeForm = $request->input('kodeForm');

        $email = karyawan::where('id', $id_karyawan)->first();

        if (!$email) {
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
            $month >= 10 && $month <= 12 => [10, 11, 12],
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
            ->filter(fn($item) => is_numeric($item->nilai))
            ->map(function ($item) {
                $bobot = kategoriKPI::where('kode_kategori', $item->kode_kategori)->value('bobot') ?? 0;
                return ($item->nilai * $bobot) / 100;
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

        $dataKriteria = $formPenilaians->map(function ($form) {
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

        $data = [
            'data' => [
                [
                    'evaluated' => $evaluated,
                    'dataAbsen' => $dataAbsen,
                    'data' => [
                        'evaluator' => $evaluatorList,
                        'dataKriteria' => $dataKriteria
                    ]
                ]
            ]
        ];

        $pdf = Pdf::loadView('pdf.rekapPenilaian', $data);

        $evaluatedName = preg_replace('/[^a-zA-Z0-9_]/', '_', $evaluated['nama'] ?? 'nama');
        $quartal = $evaluated['quartal'] ?? 'Q';
        $tahun = $evaluated['tahun'] ?? 'tahun';

        $filename = "Rekap_Penilaian_{$evaluatedName}_{$quartal}_{$tahun}.pdf";

        return $pdf->download($filename);
    }

    public function indexKategori()
    {
        $divisi = karyawan::select('divisi')->distinct()->get()->pluck('divisi');
        return view('databasekpi.indexKategori', compact('divisi'));
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
            \Log::error('Gagal mengirim email penilaian', [
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
            'quartal'     => 'required',
            'tahun'       => 'required',
            'kode_form'   => 'required',
            'catatan'     => 'required|string'
        ]);

        $id_karyawan = $request->input('id_karyawan');
        $quartal     = $request->input('quartal');
        $tahun       = $request->input('tahun');
        $kode_form   = $request->input('kode_form');
        $catatan     = $request->input('catatan');

        formPenilaian::where('id_karyawan', $id_karyawan)
            ->where('kode_form', $kode_form)
            ->where('quartal', $quartal)
            ->where('tahun', $tahun)
            ->update([
                'catatan' => $catatan
            ]);

        return back()->with(['status', 'success']);
    }

    public function getDetailPenilaian(Request $request)
    {
        $request->validate([
            'id_karyawan' => 'required',
            'kodeForm'    => 'required|string'
        ]);

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
            'id_karyawan' => $form->id_karyawan,
            'quartal'     => $form->quartal,
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
            $nilaiCollection = nilaiKPI::where('id_evaluator', $evaluatorItem->id_evaluator)
                ->where('id_evaluated', $evaluatorItem->id_evaluated)
                ->where('kode_form', $kodeForm)
                ->where('jenis_penilaian', $evaluatorItem->jenis_penilaian)
                ->get();

            $listNilaiEvaluator = [];

            foreach ($allKategoriKPIs as $kategori) {
                $item = $nilaiCollection->first(function ($nilai) use ($evaluatorItem, $kategori) {
                    return $nilai->id_evaluator === $evaluatorItem->id_evaluator &&
                        $nilai->name_variabel === $kategori->judul_kategori;
                });

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

        return response()->json([
            'data' => [
                [
                    'evaluated' => $evaluated,
                    'dataAbsen' => $dataAbsen,
                    'data' => [
                        'evaluator' => $evaluatorList,
                        'dataKriteria' => $dataKriteria
                    ]
                ]
            ]
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

    public function detailPenilaian($kodeForm, $id_karyawan)
    {
        return view('databasekpi.detailPenilaian', compact('kodeForm', 'id_karyawan'));
    }

    public function penilaianEvaluator(Request $request)
    {
        $kode_form = $request->input('kode_form');
        $id_evaluated = $request->input('id_evaluated');
        $id_evaluator = Auth::user()->karyawan->id;

        $jenis_penilaian = shareForm::where('kode_form', $kode_form)
            ->where('id_evaluator', $id_evaluator)
            ->where('id_evaluated', $id_evaluated)
            ->value('jenis_penilaian');

        $formInfo = formPenilaian::where('kode_form', $kode_form)
            ->where('id_karyawan', $id_evaluated)
            ->select('kode_kategori')
            ->get();

        if (!$jenis_penilaian || $formInfo->isEmpty()) {
            return redirect()->back()->with('error', 'Data penilaian tidak valid.');
        }

        $kategoriYangDigunakan = $formInfo->pluck('kode_kategori')->toArray();

        // Cegah duplikat penilaian
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

        // Ambil semua input yang diawali dengan 'field_'
        $allFields = collect($request->all())->filter(function ($_, $key) {
            return Str::startsWith($key, 'field_');
        });

        if ($allFields->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data yang dikirim.');
        }

        DB::beginTransaction();
        try {
            foreach ($allFields as $fieldKey => $fieldGroup) {
                foreach ($fieldGroup as $label => $value) {
                    // Ubah nama label jadi readable (dari slug ke normal)
                    $labelReadable = str_replace('_', ' ', $label);

                    // Cari pasangan nilai jika ada
                    $nilaiKey = 'nilai_' . $fieldKey;
                    $nilai = null;

                    if ($request->has($nilaiKey)) {
                        $nilaiGroup = $request->input($nilaiKey);
                        if (isset($nilaiGroup[$label]) && is_numeric($nilaiGroup[$label])) {
                            $nilai = (int) $nilaiGroup[$label];
                        }
                    }

                    // Decode jika JSON string
                    if (is_string($value) && (str_starts_with($value, '[') || str_starts_with($value, '{'))) {
                        $decoded = json_decode($value, true);
                        if (is_array($decoded)) {
                            $value = $decoded;
                        }
                    }

                    // Hitung nilai dari array jika numeric
                    if ($nilai === null && is_array($value) && collect($value)->every(fn($v) => is_numeric($v))) {
                        $nilai = array_sum(array_map('intval', $value));
                    } elseif ($nilai === null && is_numeric($value)) {
                        $nilai = (int) $value;
                    }

                    $valueToSave = is_array($value) ? json_encode($value) : $value;

                    // Ambil semua record nilaiKPI untuk kriteria ini
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
            return redirect()->back()->with('success', 'Terima kasih telah menilai. Penilaian Anda berhasil disimpan.');
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

    public function reviewPenilaian($kodeForm, $evaluatorId, $jenis, $idKaryawan)
    {
        $kode_form = $kodeForm;
        $id_evaluator = $evaluatorId;
        $id_karyawan = $idKaryawan;
        $jenis_penilaian = $jenis;

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

                $statusPenilaian = is_null($nilai?->pesan);
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
        ]);

        $id_evaluator_array = $request->input('id_karyawan');
        $divisi_array       = $request->input('divisi');
        $kode_form          = $request->input('kode_form');
        $id_evaluated       = $request->input('id_evaluated');
        $jenis_penilaian    = $request->input('jenis_penilaian');

        $karyawanEvaluated = Karyawan::find($id_evaluated);

        $dataFormKategori = formPenilaian::where('kode_form', $kode_form)
            ->where('id_karyawan', $id_evaluated)
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
                        'content'      => $karyawan->nama_lengkap . ' dapat mengisi formulir PENILAIAN KINERJA ' . strtoupper($karyawanEvaluated->nama_lengkap) . ' untuk ' . $quarterLabel,
                    ];

                    $url = url('getFormPenilaian/' . $kode_form . '/' . $id_evaluated);
                    Notification::send($user, new penilaianExcangheNotifikasi($dummyComment, $url, $url));
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
        ]);

        $id_karyawan_array   = $request->input('id_karyawan');
        $all_kriteria_data   = $request->input('kriteria');
        $kodeFormPenilaian   = Str::random(20);
        $month               = now()->month;
        $year                = now()->year;

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
            return redirect()->back()->with('error', 'Karyawan tidak ditemukan.');
        }

        $id_evaluator = Auth::user()->karyawan->id;

        $shared = shareForm::where('kode_form', $kode_form)
            ->where('id_evaluated', $id_karyawan)
            ->where('id_evaluator', $id_evaluator)
            ->first();

        if (!$shared) {
            return redirect()->back()->with('error', 'Form tidak tersedia.');
        }

        // Tambahkan pengecekan: jika sudah dinilai (nilai atau pesan tidak null), redirect
        $isAlreadyRated = nilaiKPI::where(function ($query) use ($kode_form, $id_evaluator, $id_karyawan) {
            $query->where('kode_form', $kode_form)
                ->where('id_evaluator', $id_evaluator)
                ->where('id_evaluated', $id_karyawan)
                ->where(function ($q) {
                    $q->whereNotNull('nilai')
                        ->orWhereNotNull('pesan');
                });
        })->exists();

        if ($isAlreadyRated) {
            return redirect()->back()->with('error', 'Penilaian sudah dilakukan.');
        }

        $formPenilaians = formPenilaian::where('kode_form', $kode_form)
            ->where('id_karyawan', $id_karyawan)
            ->get();

        if ($formPenilaians->isEmpty()) {
            return view('databasekpi.formPenilaian', [
                'data' => [],
                'evaluatedEmployeeName' => $evaluatedEmployee->nama_lengkap,
                'status' => false
            ]);
        }

        $outputData = [
            'form_penilaian_id' => $formPenilaians->first()->id,
            'kode_form_global'  => $kode_form,
            'evaluator'         => Auth::user()->karyawan->nama_lengkap,
            'evaluated'         => $evaluatedEmployee->nama_lengkap,
            'id_karyawan'       => $id_karyawan,
            'jenis_penilaian'   => $shared->jenis_penilaian,
            'quartal'           => $formPenilaians->first()->quartal,
            'tahun'             => $formPenilaians->first()->tahun,
            'detail_kategori'   => [],
        ];

        foreach ($formPenilaians as $form) {
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
                    'keterangan_tipe'    => $kategori->tipeKategoriTabels->map(function ($tipe) {
                        return [
                            'id'    => $tipe->id,
                            'ket'   => $tipe->ket_tipe,
                            'nilai' => $tipe->nilai_ket_tipe
                        ];
                    })->toArray(),
                ];
            })->toArray();

            $outputData['detail_kategori'][] = [
                'kriteria_utama'     => $form->nama_penilaian,
                'isi_kriteria'       => $isiKriteria,
                'kode_kategori_form' => $form->kode_kategori,
            ];
        }

        return view('databasekpi.formPenilaian', [
            'outputData' => [$outputData], // tetap array agar compatible
            'evaluatedEmployee' => $evaluatedEmployee,
            'status' => true
        ]);
    }

    public function getFromPenilaianUser(Request $request, $id_evaluator)
    {
        $evaluatorEmploye = Karyawan::find($id_evaluator);
        if (!$evaluatorEmploye) {
            return redirect()->back()->with('error', 'Evaluator tidak ditemukan.');
        }

        $currentMonth = now()->month;
        $currentYear = now()->year;
        $currentQuartal = match (true) {
            $currentMonth >= 1 && $currentMonth <= 3 => 'Q1',
            $currentMonth >= 4 && $currentMonth <= 6 => 'Q2',
            $currentMonth >= 7 && $currentMonth <= 9 => 'Q3',
            default => 'Q4',
        };

        $sharedForms = shareForm::where('id_evaluator', $id_evaluator)->get();

        if ($sharedForms->isEmpty()) {
            return view('databasekpi.formPenilaian', [
                'data' => [],
                'evaluatorEmployeName' => $evaluatorEmploye->nama_lengkap,
                'status' => 'Belum Ditunjuk',
            ]);
        }

        $groupedOutputData = [];

        foreach ($sharedForms as $share) {
            $formPenilaians = formPenilaian::where('kode_form', $share->kode_form)
                ->where('quartal', $currentQuartal)
                ->where('tahun', $currentYear)
                ->where('id_karyawan', $share->id_evaluated)
                ->get();

            foreach ($formPenilaians as $formItem) {
                $evaluatedEmployee = Karyawan::find($formItem->id_karyawan);
                if (!$evaluatedEmployee) continue;

                $totalItems = nilaiKPI::where('kode_form', $share->kode_form)
                    ->where('id_evaluator', $id_evaluator)
                    ->where('id_evaluated', $evaluatedEmployee->id)
                    ->where('jenis_penilaian', $share->jenis_penilaian)
                    ->count();

                $filledItems = nilaiKPI::where('kode_form', $share->kode_form)
                    ->where('id_evaluator', $id_evaluator)
                    ->where('id_evaluated', $evaluatedEmployee->id)
                    ->where('jenis_penilaian', $share->jenis_penilaian)
                    ->whereNotNull('pesan')
                    ->count();

                if ($totalItems > 0 && $filledItems === $totalItems) continue;

                $groupKey = implode('_', [
                    $share->kode_form,
                    $evaluatedEmployee->id,
                    $id_evaluator,
                    $share->jenis_penilaian,
                    $formItem->quartal,
                    $formItem->tahun
                ]);

                if (!isset($groupedOutputData[$groupKey])) {
                    $groupedOutputData[$groupKey] = [
                        'form_penilaian_id'  => $formItem->id,
                        'kode_form_global'   => $share->kode_form,
                        'evaluator'          => $evaluatorEmploye->nama_lengkap,
                        'evaluated'          => $evaluatedEmployee->nama_lengkap,
                        'id_karyawan'        => $evaluatedEmployee->id,
                        'jenis_penilaian'    => $share->jenis_penilaian,
                        'quartal'            => $formItem->quartal,
                        'tahun'              => $formItem->tahun,
                        'detail_kategori'    => [],
                    ];
                }

                $kategoriKPIs = kategoriKPI::where('kode_kategori', $formItem->kode_kategori)
                    ->with('tipeKategoriTabels')
                    ->get();

                $isiKriteria = $kategoriKPIs->map(function ($kategori) {
                    return [
                        'sub_kriteria_id'    => $kategori->id,
                        'sub_kriteria_judul' => $kategori->judul_kategori,
                        'tipe_kategori'      => $kategori->tipe_kategori,
                        'bobot'              => $kategori->bobot,
                        'level'              => $kategori->level,
                        'keterangan_tipe'    => $kategori->tipeKategoriTabels->map(function ($tipe) {
                            return [
                                'id'    => $tipe->id,
                                'ket'   => $tipe->ket_tipe,
                                'nilai' => $tipe->nilai_ket_tipe
                            ];
                        })->toArray(),
                    ];
                })->toArray();

                $alreadyExists = collect($groupedOutputData[$groupKey]['detail_kategori'] ?? [])
                    ->contains(fn($item) => $item['kode_kategori_form'] === $formItem->kode_kategori);

                if (!$alreadyExists) {
                    $groupedOutputData[$groupKey]['detail_kategori'][] = [
                        'kriteria_utama'     => $formItem->nama_penilaian,
                        'isi_kriteria'       => $isiKriteria,
                        'kode_kategori_form' => $formItem->kode_kategori,
                    ];
                }
            }
        }

        $outputData = array_values($groupedOutputData);
        $status = count($outputData) > 0 ? true : false;

        return view('databasekpi.formPenilaian', compact('outputData', 'evaluatorEmploye', 'status'));
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

        $filterQuartal = request()->get('quartal');
        $filterTahun = request()->get('tahun');
        $filterDivisi = request()->get('divisi');

        $dataFormPenilaianCollection = formPenilaian::with('karyawan')
            ->when($filterQuartal, fn($q) => $q->where('quartal', $filterQuartal))
            ->when($filterTahun, fn($q) => $q->where('tahun', $filterTahun))
            ->get();

        $dataKaryawan = karyawan::where('status_aktif', '1')->get();
        $groupedOutputData = [];

        foreach ($dataFormPenilaianCollection as $formPenilaian) {
            if ($filterDivisi && $formPenilaian->karyawan->divisi !== $filterDivisi) {
                continue;
            }

            $evaluatedName = $formPenilaian->karyawan->nama_lengkap;
            $evaluatedDivisi = $formPenilaian->karyawan->divisi;
            $quartal = $formPenilaian->quartal;
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
                    'id_karyawan'        => $formPenilaian->id_karyawan,
                    'evaluated'          => $evaluatedName,
                    'evaluatedDivisi'    => $evaluatedDivisi,
                    'quartal'            => $quartal,
                    'tahun'              => $tahun,
                    'jenis_penilaian'    => $jenisPenilaianList,
                    'evaluator'          => $evaluatorNamesList,
                    'evaluator_by_jenis' => $evaluatorGroupedByJenis,
                    'id_evaluator'       => $evaluatorIds,
                    'detail_kategori'    => [],
                    'status'             => $status
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
        $currentMonth = now()->month;

        if ($currentMonth >= 1 && $currentMonth <= 3) {
            $currentQuartal = 'Q1';
        } elseif ($currentMonth >= 4 && $currentMonth <= 6) {
            $currentQuartal = 'Q2';
        } elseif ($currentMonth >= 7 && $currentMonth <= 9) {
            $currentQuartal = 'Q3';
        } else {
            $currentQuartal = 'Q4';
        }

        $formPenilaian = formPenilaian::with('karyawan')
            ->where('id_karyawan', $id_karyawan)
            ->where('quartal', $currentQuartal)
            ->where('tahun', $currentTahun)
            ->get();

        $catatan = $formPenilaian->pluck('catatan')->unique();

        $quartalToMonths = [
            'Q1' => [1, 2, 3],
            'Q2' => [4, 5, 6],
            'Q3' => [7, 8, 9],
            'Q4' => [10, 11, 12],
        ];

        $bulanDalamQuartal = $quartalToMonths[$currentQuartal] ?? [];

        $dataAbsensi = AbsensiKaryawan::where('id_karyawan', $id_karyawan)
            ->whereIn(DB::raw('MONTH(created_at)'), $bulanDalamQuartal)
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
                    $kriteriaNama = $formPenilaian->firstWhere('kode_kategori', $kode_kategori)?->nama_penilaian ?? '-';
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
}
