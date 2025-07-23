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

class DatabaseKPIController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:View DatabaseKPI', ['only' => ['index']]);
    }

    public function indexKategori()
    {
        $divisi = karyawan::select('divisi')->distinct()->get()->pluck('divisi');
        return view('databasekpi.indexKategori', compact('divisi'));
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
            'tahun'   => $form->tahun
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

        // Data kriteria per form
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

        return response()->json([
            'data' => [
                [
                    'evaluated' => $evaluated,
                    'data' => [
                        'evaluator' => $evaluatorList,
                        'dataKriteria' => $dataKriteria
                    ]
                ]
            ]
        ]);
    }


    public function detailPenilaian($kodeForm, $id_karyawan)
    {
        return view('databasekpi.detailPenilaian', compact('kodeForm', 'id_karyawan'));
    }

    public function penilaianEvaluator(Request $request)
    {
        $validated = $request->input('field', []);
        $kode_form = $request->input('kode_form');
        $id_evaluator = Auth::user()->karyawan->id;

        $id_evaluated = nilaiKPI::where('kode_form', $kode_form)
            ->where('id_evaluator', $id_evaluator)
            ->value('id_evaluated');

        DB::beginTransaction();
        try {
            foreach ($validated as $field => $value) {
                $originalField = str_replace('_', ' ', $field);

                if (is_string($value) && (str_starts_with($value, '[') || str_starts_with($value, '{'))) {
                    $decoded = json_decode($value, true);
                    if (is_array($decoded)) {
                        $value = $decoded;
                    }
                }

                $records = nilaiKPI::where('kode_form', $kode_form)
                    ->where('id_evaluator', $id_evaluator)
                    ->where('id_evaluated', $id_evaluated)
                    ->where('name_variabel', $originalField)
                    ->get();

                foreach ($records as $record) {
                    if (!is_null($record->pesan)) {
                        return redirect()->back()->with('error', "Penilaian sudah pernah dilakukan oleh Anda.");
                    }

                    $kategori = kategoriKPI::where('kode_kategori', $record->kode_kategori)->first();
                    $nilai = null;
                    $valueToSave = null;

                    if ($kategori) {
                        $tipe = $kategori->tipe_kategori;

                        if (is_array($value)) {
                            if (collect($value)->every(fn($v) => is_numeric($v))) {
                                $nilai = array_sum(array_map('intval', $value));
                                $valueToSave = $nilai;
                            }
                        } else {
                            if (is_numeric($value)) {
                                $nilai = (int) $value;
                                $valueToSave = $nilai;
                            } else {
                                $valueToSave = $value;
                            }
                        }
                    }

                    $record->pesan = $valueToSave;
                    $record->nilai = $nilai;
                    $record->finished_at = now();
                    $record->save();
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Terima kasih telah menilai, penilaian anda akan di-review oleh HRD.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
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


    public function reviewPenilaian($kodeForm, $evaluatorId)
    {
        $kode_form = $kodeForm;
        $id_evaluator = $evaluatorId;

        $dataEvaluator = shareForm::where('kode_form', $kode_form)
            ->where('id_evaluator', $id_evaluator)
            ->first();

        if (!$dataEvaluator) {
            return response()->json([
                'status'  => false,
                'message' => 'Data evaluator tidak ditemukan.'
            ], 404);
        }

        $id_evaluated = $dataEvaluator->id_evaluated;

        $dataForm = formPenilaian::where('kode_form', $kode_form)
            ->where('id_karyawan', $id_evaluated)
            ->get();

        if ($dataForm->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'Data form penilaian tidak ditemukan.'
            ], 404);
        }

        $result = [];

        foreach ($dataForm as $form) {
            $kategoriList = kategoriKPI::where('kode_kategori', $form->kode_kategori)->get();

            foreach ($kategoriList as $kategori) {
                $tipeKategori = tipeKategoriTabel::where('id_kategori', $kategori->id)->get();

                $nilai = nilaiKPI::where('kode_form', $kode_form)
                    ->where('id_evaluator', $id_evaluator)
                    ->where('id_evaluated', $id_evaluated)
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
        $evaluated = Karyawan::find($id_evaluated);

        return view('databasekpi.reviewPenilaian', [
            'statusPenilaian' => $statusPenilaian,
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

        foreach ($id_evaluator_array as $id_evaluator) {
            $karyawan = Karyawan::find($id_evaluator);
            if (!$karyawan) continue;

            $isGM = strtoupper($karyawan->jabatan) === 'GM';
            $divisiEvaluators = $isGM ? [$karyawan->divisi] : $divisi_array;

            foreach ($divisiEvaluators as $divisi) {

                $alreadyShared = shareForm::where('id_evaluator', $id_evaluator)
                    ->where('id_evaluated', $id_evaluated)
                    ->where('kode_form', $kode_form)
                    ->whereMonth('created_at', $quarter)
                    ->exists();

                if ($alreadyShared) {
                    continue;
                }

                foreach ($dataKategori as $kategori) {
                    shareForm::firstOrCreate([
                        'id_evaluator'     => $id_evaluator,
                        'divisi_evaluator' => $divisi,
                        'kode_form'        => $kode_form,
                        'id_evaluated'     => $id_evaluated,
                        'jenis_penilaian'  => $jenis_penilaian,
                    ]);
                }

                $nilaiExists = NilaiKPI::where('id_evaluator', $id_evaluator)
                    ->where('id_evaluated', $id_evaluated)
                    ->where('kode_form', $kode_form)
                    ->whereMonth('created_at', $quarter)
                    ->exists();

                if (!$nilaiExists) {
                    foreach ($dataKategori as $kategori) {
                        NilaiKPI::create([
                            'id_evaluator'  => $id_evaluator,
                            'id_evaluated'  => $id_evaluated,
                            'kode_form'     => $kode_form,
                            'kode_kategori' => $kategori->kode_kategori,
                            'name_variabel' => $kategori->judul_kategori,
                            'status'        => '0',
                        ]);
                    }
                }

                if ($karyawan->kode_karyawan) {
                    $users = User::whereHas('karyawan', function ($q) use ($karyawan) {
                        $q->where('kode_karyawan', $karyawan->kode_karyawan);
                    })->get();

                    $quarterLabel = match (true) {
                        $month >= 1 && $month <= 3 => 'Q1',
                        $month >= 4 && $month <= 6 => 'Q2',
                        $month >= 7 && $month <= 9 => 'Q3',
                        default => 'Q4',
                    };

                    foreach ($users as $user) {
                        $dummyComment = (object) [
                            'karyawan_key' => $karyawan->kode_karyawan,
                            'content'      => $karyawan->nama_lengkap . ' dapat mengisi formulir PENILAIAN KINERJA ' . strtoupper($karyawanEvaluated->nama_lengkap) . ' untuk ' . $quarterLabel,
                            'materi_key'   => null,
                            'rkm_key'      => null,
                        ];

                        $url = url('getFormPenilaian/' . $kode_form . '/' . $id_evaluated);
                        Notification::send($user, new penilaianExcangheNotifikasi($dummyComment, $url, $url));
                    }
                }
            }
        }

        return redirect()->back()->with('success', 'Berhasil mengirim form, jangan lupa untuk review nantinya');
    }


    public function kategoriStore(Request $request)
    {
        $request->validate([
            'id_karyawan'         => 'required|array|min:1',
            'kriteria'            => 'required|array|min:1',
            'kriteria.*.nama_penilaian' => 'required|string',
            'kriteria.*.sub_kriteria' => 'required|array|min:1',
            'kriteria.*.sub_kriteria.*.judul_kategori' => 'required|string',
            'kriteria.*.sub_kriteria.*.tipe_kategori'  => 'required|string',
            'kriteria.*.sub_kriteria.*.level'          => 'required|string',
            'kriteria.*.sub_kriteria.*.bobot'          => 'required|numeric',
            'kriteria.*.sub_kriteria.*.ket_tipe'      => 'nullable|array',
            'kriteria.*.sub_kriteria.*.ket_tipe.*'    => 'nullable|string',
            'kriteria.*.sub_kriteria.*.nilai_ket_tipe'      => 'nullable|array',
            'kriteria.*.sub_kriteria.*.nilai_ket_tipe.*'    => 'nullable|string',
        ]);

        $id_karyawan_array  = $request->input('id_karyawan');
        $all_kriteria_data = $request->input('kriteria');

        $kodeFormPenilaian = Str::random(20);

        $month = now()->month;
        $year = now()->year;
        // $quarter = match (true) {
        //     $month >= 1 && $month <= 3 => [1, 2, 3],
        //     $month >= 4 && $month <= 6 => [4, 5, 6],
        //     $month >= 7 && $month <= 9 => [7, 8, 9],
        //     default => [10, 11, 12],
        // };

        $quarterLabel = match (true) {
            $month >= 1 && $month <= 3 => 'Q1',
            $month >= 4 && $month <= 6 => 'Q2',
            $month >= 7 && $month <= 9 => 'Q3',
            $month >= 10 && $month <= 12 => 'Q4'
        };

        if (!in_array($quarterLabel, ['Q1', 'Q2', 'Q3', 'Q4'])) {
            return redirect()->back()->with('error', 'Quarter tidak terdeteksi');
        }

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
                            $tipe = new tipeKategoriTabel();
                            $tipe->id_kategori    = $kategori->id;
                            $tipe->ket_tipe       = $ket;
                            $tipe->nilai_ket_tipe = $nilai_ket_tipe_for_sub[$j] ?? null;
                            $tipe->save();
                        }
                    }
                }
            }
        }

        return back()->with('success', 'Berhasil disimpan.');
    }

    public function getFromPenilaian(Request $request, $kode_form, $id_karyawan)
    {
        $formCode = $kode_form;
        $evaluatedEmployeeId = $id_karyawan;

        $evaluatedEmployee = Karyawan::find($evaluatedEmployeeId);
        if (!$evaluatedEmployee) {
            return redirect()->back()->with('error', 'Karyawan yang dievaluasi tidak ditemukan.');
        }

        $dataFormPenilaianCollection = formPenilaian::where('id_karyawan', $evaluatedEmployeeId)
            ->where('kode_form', $formCode)
            ->with('karyawan')
            ->get();

        if ($dataFormPenilaianCollection->isEmpty()) {
            return view('databasekpi.formPenilaian', ['data' => [], 'evaluatedEmployeeName' => $evaluatedEmployee->nama_lengkap]);
        }

        $groupedOutputData = [];

        foreach ($dataFormPenilaianCollection as $formPenilaian) {
            $evaluatorName = $formPenilaian->karyawan->nama_lengkap ?? 'N/A';
            $kriteriaNama = $formPenilaian->nama_penilaian;
            $kodeFormGlobal = $formPenilaian->kode_form;

            $groupKey = $kodeFormGlobal . '_' . $evaluatorName . '_' . $evaluatedEmployeeId;

            if (!isset($groupedOutputData[$groupKey])) {
                $groupedOutputData[$groupKey] = [
                    'form_penilaian_id'  => $formPenilaian->id,
                    'kode_form_global'   => $kodeFormGlobal,
                    'evaluator'          => $evaluatorName,
                    'evaluated'          => $evaluatedEmployee->nama_lengkap,
                    'id_karyawan'        => $evaluatedEmployeeId,
                    'detail_kategori'    => [],
                ];
            }

            $kategoriKPIs = kategoriKPI::where('kode_kategori', $formPenilaian->kode_kategori)
                ->with(['tipeKategoriTabels'])
                ->get();

            $isiKriteria = [];
            foreach ($kategoriKPIs as $kategori) {

                $bobotNumerik = (float) $kategori->bobot;

                $isiKriteria[] = [
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
            }

            $kode_form    = $formCode;
            $id_evaluator = Auth::user()->karyawan->id;

            $id_evaluated = nilaiKPI::where('kode_form', $kode_form)
                ->where('id_evaluator', $id_evaluator)
                ->value('id_evaluated');

            $records = nilaiKPI::where('kode_form', $kode_form)
                ->where('id_evaluator', $id_evaluator)
                ->where('id_evaluated', $id_evaluated)
                ->get();

            $status = $records->every(function ($record) {
                return is_null($record->pesan);
            });


            $groupedOutputData[$groupKey]['detail_kategori'][] = [
                'kriteria_utama'     => $kriteriaNama,
                'isi_kriteria'       => $isiKriteria,
                'kode_kategori_form' => $formPenilaian->kode_kategori,
            ];
        }

        $outputData = array_values($groupedOutputData);

        return view('databasekpi.formPenilaian', compact('outputData', 'evaluatedEmployee', 'status'));
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

            foreach ($dataEvaluator as $evaluatorRow) {
                $evaluatorId = $evaluatorRow->id_evaluator;
                $evaluatorName = optional($evaluatorRow->evaluator)->nama_lengkap ?? '-';

                $unfinished = nilaiKPI::where('kode_form', $kodeFormGlobal)
                    ->where('id_evaluated', $formPenilaian->id_karyawan)
                    ->where('id_evaluator', $evaluatorId)
                    ->whereNull('finished_at')
                    ->exists();

                $evaluatorNamesList[] = [
                    'id'     => $evaluatorId,
                    'name'   => $evaluatorName,
                    'is_red' => $unfinished,
                ];
            }

            $evaluatorIds = $dataEvaluator->pluck('id_evaluator')->unique()->values()->all();

            $groupKey = $kodeFormGlobal . '_' . $evaluatedName;

            $records = nilaiKPI::where('kode_form', $kodeFormGlobal)
                ->where('id_evaluated', $formPenilaian->id_karyawan)
                ->get();

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
                    'evaluator'          => $evaluatorNamesList,
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

    public function store(Request $request) {}

    public function show() {}

    public function create() {}

    public function detail() {}


    public function dataDetail(Request $request) {}

    public function edit() {}
}
