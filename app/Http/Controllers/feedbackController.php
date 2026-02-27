<?php

namespace App\Http\Controllers;

use App\Models\RKM;
use App\Models\Feedback;
use App\Models\souvenir;
use App\Models\Registrasi;
use Illuminate\Http\Request;
use App\Models\Nilaifeedback;
use Illuminate\Support\Carbon;
use App\Models\souvenirinhouse;
use App\Models\souvenirpeserta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Exports\FeedbackSalesExport;
use App\Exports\NilaifeedbackExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;

class feedbackController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $sales = Feedback::where('kategori_feedback', 'Sales')->get();
        $materi = Feedback::where('kategori_feedback', 'Materi')->get();
        $pelayanan = Feedback::where('kategori_feedback', 'Pelayanan')->get();
        $fasilitas = Feedback::where('kategori_feedback', 'Fasilitas Laboratium')->get();
        $instruktur = Feedback::where('kategori_feedback', 'Instruktur')->get();
        $jmlInstruktur = RKM::with('instruktur');
        $umum = Feedback::where('kategori_feedback', 'Umum')->get();
        $souvenir = souvenir::get();
        return view('feedback.index', compact('materi', 'pelayanan', 'fasilitas', 'instruktur', 'umum', 'souvenir', 'sales'));
    }

    public function cekFeedback(Request $request)
    {
        $id_regist = $request->input('id_regist');
        $id_rkm = $request->input('id_rkm');
        $post = Nilaifeedback::where('id_regist', $id_regist)->first();
        if ($post) {
            return response()->json([
                'status' => 'error',
                'title' => 'Mohon Maaf',
                'text' => 'Anda sudah mengisi feedback ini!',
            ], 500);
        } else {
            return response()->json([
                'status' => 'success',
                'title' => 'Selamat!',
                'text' => 'Lanjut Feedback!',
            ], 200);
        }
    }
    public function cekFeedbackRKM(Request $request)
    {
        $startDate = Carbon::now()->startOfMonth()->toDateString();

        // Mengatur $endDate menjadi hari terakhir bulan ini (akhir bulan)
        $endDate = Carbon::now()->endOfMonth()->toDateString();
        $id = $request->input('id');
        $peserta = Registrasi::with('peserta', 'rkm', 'materi')
            ->where('id_peserta', $id)
            ->whereHas('rkm', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal_awal', [$startDate, $endDate]);
            })
            ->get();
        return response()->json(['rkm' => $peserta]);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        if (is_null($data['I1b'])) {
            foreach (['I1b', 'I2b', 'I3b', 'I4b', 'I5b', 'I6b', 'I7b', 'I8b'] as $key) {
                $data[$key] = null;
            }
        } else {
            foreach (['I2b', 'I3b', 'I4b', 'I5b', 'I6b', 'I7b', 'I8b'] as $key) {
                if (is_null($data[$key])) {
                    $data[$key] = '4';
                }
            }
        }

        if (is_null($data['I1as'])) {
            foreach (['I1as', 'I2as', 'I3as', 'I4as', 'I5as', 'I6as', 'I7as', 'I8as'] as $key) {
                $data[$key] = null;
            }
        } else {
            foreach (['I2as', 'I3as', 'I4as', 'I5as', 'I6as', 'I7as', 'I8as'] as $key) {
                if (is_null($data[$key])) {
                    $data[$key] = '4';
                }
            }
        }

        // Check and assign default values for other fields
        foreach ($data as $key => $value) {
            if (is_null($value)) {
                if (in_array($key, ['U1', 'U2'])) {
                    $data[$key] = '-';
                } else if (
                    !in_array($key, [
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
                    ])
                ) {
                    $data[$key] = '4';
                }
            }
        }
        // return $data;

        // Check if feedback already exists
        $nilaiFeedback = Nilaifeedback::where('id_regist', $data['id_regist'])
            ->where('id_rkm', $data['id_rkm'])
            ->first();

        if ($nilaiFeedback) {
            return response()->json([
                'status' => 'error',
                'title' => 'Mohon Maaf',
                'text' => 'Anda sudah mengisi feedback!',
            ], 500);
        }
        // Handle full feedback creation with all inputs
        Nilaifeedback::create($data);

        return response()->json([
            'status' => 'success',
            'title' => 'Selamat!',
            'text' => 'Terimakasih sudah mengisi feedback!',
        ], 200);
    }

    public function show(string $id)
    {
        // Split the incoming ID to extract the necessary parts
        $array = explode('ixb', $id);
        $materiKey = $array[0];
        $bulan = $array[1];
        $tahun = $array[2];
        $hari = $array[3];
        $tanggal_awal = $tahun . '-' . $bulan . '-' . $hari;

        // Query the feedbacks with the given criteria and eager load relationships
        $feedbacks = Nilaifeedback::with('rkm', 'regist')
            ->whereHas('rkm', function ($query) use ($materiKey, $tanggal_awal) {
                $query->where('materi_key', $materiKey)
                    ->where('tanggal_awal', $tanggal_awal);
            })
            ->get();


        // Transform the feedback data
        $transformedFeedbacks = $feedbacks->map(function ($feedback) {
            $materi = isset($feedback->M) ? intval($feedback->M) : round(($feedback->M1 + $feedback->M2 + $feedback->M3 + $feedback->M4) / 4, 1);
            $pelayanan = isset($feedback->P) ? intval($feedback->P) : round(($feedback->P1 + $feedback->P2 + $feedback->P3 + $feedback->P4 + $feedback->P5 + $feedback->P6 + $feedback->P7) / 7, 1);
            $fasilitas = isset($feedback->F) ? intval($feedback->F) : round(($feedback->F1 + $feedback->F2 + $feedback->F3 + $feedback->F4 + $feedback->F5) / 5, 1);
            $instruktur = isset($feedback->I) ? intval($feedback->I) : round(($feedback->I1 + $feedback->I2 + $feedback->I3 + $feedback->I4 + $feedback->I5 + $feedback->I6 + $feedback->I7 + $feedback->I8) / 8, 1);
            $instruktur2 = isset($feedback->IB) ? intval($feedback->IB) : round(($feedback->I1b + $feedback->I2b + $feedback->I3b + $feedback->I4b + $feedback->I5b + $feedback->I6b + $feedback->I7b + $feedback->I8b) / 8, 1);
            $asisten = isset($feedback->IAS) ? intval($feedback->IAS) : round(($feedback->I1as + $feedback->I2as + $feedback->I3as + $feedback->I4as + $feedback->I5as + $feedback->I6as + $feedback->I7as + $feedback->I8as) / 8, 1);
            $sales = isset($feedback->S) ? intval($feedback->S) : round(($feedback->S1 + $feedback->S2 + $feedback->S3 + $feedback->S4 + $feedback->S5 + $feedback->S6 + $feedback->S7) / 7, 1);
            if($sales == '0' || $sales == 0){
                $total_feedback = round(($materi + $pelayanan + $fasilitas + $instruktur) / 4, 2);
            }else{
                $total_feedback = round(($materi + $pelayanan + $fasilitas + $instruktur + $sales) / 5, 2);
            }
            return [
                'id_regist' => $feedback->id_regist,
                'id_rkm' => $feedback->id_rkm,
                'nama_materi' => $feedback->rkm->materi->nama_materi,
                'sales_key' => $feedback->rkm->sales_key,
                'instruktur_key' => $feedback->rkm->instruktur_key,
                'instruktur_key2' => $feedback->rkm->instruktur_key2,
                'asisten_key' => $feedback->rkm->asisten_key,
                'tanggal_awal' => $feedback->rkm->tanggal_awal,
                'tanggal_akhir' => $feedback->rkm->tanggal_akhir,
                'email' => $feedback->email,
                'nama_perusahaan' => $feedback->rkm->perusahaan->nama_perusahaan,
                'materi' => $materi,
                'pelayanan' => $pelayanan,
                'fasilitas' => $fasilitas,
                'instruktur' => $instruktur,
                'instruktur2' => $instruktur2,
                'asisten' => $asisten,
                'sales' => $sales,
                'umum1' => $feedback->U1,
                'umum2' => $feedback->U2,
                'textM' => $feedback->TextM,
                'textP' => $feedback->TextP,
                'textF' => $feedback->TextF,
                'textI' => $feedback->TextI,
                'textIB' => $feedback->TextIB,
                'textIAS' => $feedback->TextIAS,
                'total_feedback' => $total_feedback,
                'datafeedbacks' => $feedback,
            ];
        });
        $instruktur =  $transformedFeedbacks->avg('instruktur');
        $instruktur2 = $transformedFeedbacks->avg('instruktur2');
        $asisten = $transformedFeedbacks->avg('asisten');
        if($instruktur2 == '0' || $instruktur == 0 && $asisten == '0' || $asisten == 0){
            $instrukturfix =  $transformedFeedbacks->avg('instruktur');
        }elseif($asisten == '0' || $asisten == 0){
            $instrukturfix =  round(($transformedFeedbacks->avg('instruktur') + $transformedFeedbacks->avg('instruktur2')) / 2, 2);
        }else{
            $instrukturfix =  round(($transformedFeedbacks->avg('instruktur') + $transformedFeedbacks->avg('instruktur2') + $transformedFeedbacks->avg('asisten')) / 3, 2);
        }
        $sales = $transformedFeedbacks->avg('sales');
        if($sales == '0' || $sales == 0){
            $all_avg_feedback = round((
                    $transformedFeedbacks->avg('materi') +
                    $transformedFeedbacks->avg('pelayanan') +
                    $transformedFeedbacks->avg('fasilitas') +
                    $instrukturfix) / 4, 2);
        }else{
            $all_avg_feedback = 
            round((
                    $transformedFeedbacks->avg('materi') +
                    $transformedFeedbacks->avg('pelayanan') +
                    $transformedFeedbacks->avg('fasilitas') +
                    $instrukturfix +
                    $transformedFeedbacks->avg('sales')
                ) / 5, 2);
        }
        
        // return $instruktur2;
        $post = [
                'materi' => round($transformedFeedbacks->avg('materi'), 2),
                'pelayanan' => round($transformedFeedbacks->avg('pelayanan'), 2),
                'fasilitas' => round($transformedFeedbacks->avg('fasilitas'), 2),
                'instruktur' => round($transformedFeedbacks->avg('instruktur'), 2),
                'instruktur2' => round($transformedFeedbacks->avg('instruktur2'), 2),
                'asisten' => round($transformedFeedbacks->avg('asisten'), 2),
                'sales' => round($transformedFeedbacks->avg('sales'), 2),
                'all_avg_feedback' => $all_avg_feedback,
                'data' => $transformedFeedbacks,
            ];


// return response()->json($post);

        $pertanyaan = Feedback::get();

        return view('feedback.show', compact('post', 'id', 'pertanyaan', 'feedbacks'));

    }

    public function storeSouvenir(Request $request)
    {
        $id_regist = $request->input('id_regist');
        $id_rkm = $request->input('id_rkm');
        $souveniran = $request->input('souvenir');
        if (!$souveniran) {
            return response()->json([
                'status' => 'error',
                'title' => 'Mohon Maaf!',
                'text' => 'Anda belum mengisi souvenir, silahkan coba kembali!',
            ], 200);
        }
        $souvenir = Souvenir::where('nama_souvenir', $souveniran)->first();
        $souvenir->decrement('stok');

        souvenirpeserta::create([
            'id_souvenir' => $souvenir->id,
            'id_rkm' => $id_rkm,
            'id_regist' => $id_regist
        ]);
        return response()->json([
            'status' => 'success',
            'title' => 'Selamat!',
            'text' => 'Terimakasih sudah mengisi Feedback ini, Silahkan ke halaman utama!',
        ], 200);
    }

    public function cekSouvenir($id, $ids)
    {
        $sopenir = souvenirpeserta::where('id_rkm', $id)->where('id_regist', $ids)->first();

        if ($sopenir) {
            return response()->json([
                'status' => 'error',
                'title' => 'Mohon Maaf',
                'text' => 'Anda sudah mengisi feedback/souvenir ini, silahkan ke halaman utama!',
            ], 500);
        } else {
            $rkm = RKM::findOrFail($id);
            $harga_jual = intval($rkm->harga_jual);

            // =======================================================
            // CASE: Inhouse Bandung / Luar Bandung
            // =======================================================
            if ($rkm->metode_kelas == "Inhouse Bandung" || $rkm->metode_kelas == "Inhouse Luar Bandung") {

                $s = souvenirinhouse::where('id_rkm', $rkm->id)->get();

                // Jika tidak ada souvenir inhouse
                if ($s->isEmpty()) {
                    return response()->json([
                        'status' => 'skipped',
                        'title' => 'Selamat!',
                        'text' => 'Terimakasih sudah mengisi Feedback ini, Silahkan ke halaman utama!',
                    ], 200);
                }

                // Ambil semua nama souvenir dari hasil query
                $souvenirNames = $s->pluck('nama_souvenir')->toArray();


                // Jika salah satu bernama "All Item"
                if (in_array('All Item', $souvenirNames)) {
                    $souvenirs = souvenir::where('stok', '>', 0)
                        ->orderByDesc('stok')
                        ->get();
                    // dd('all_item');
                } else {
                    $souvenirs = souvenir::where(function ($query) use ($souvenirNames) {
                        foreach ($souvenirNames as $name) {
                            $query->orWhere('nama_souvenir', 'LIKE', "%{$name}%");
                        }
                    })
                        ->where('stok', '>', 0)
                        ->orderByDesc('stok')
                        ->get();
                    // dd($souvenirs);
                }

                if ($souvenirs->isEmpty()) {
                    return response()->json([
                        'status' => 'error',
                        'title' => 'Mohon Maaf',
                        'text' => 'Data tidak sesuai atau tidak ada. Silahkan Hubungi Administrator!',
                    ], 500);
                }
            }

            // =======================================================
            // CASE: Virtual
            // =======================================================
            elseif ($rkm->metode_kelas == "Virtual") {
                $s = souvenirinhouse::where('id_rkm', $rkm->id)->first();

                if (!$s) {
                    $souvenirs = souvenir::where('min_harga_pelatihan', '<=', $harga_jual)
                        ->where('max_harga_pelatihan', '>=', $harga_jual)
                        ->where('stok', '>', 0)
                        ->orderBy('min_harga_pelatihan', 'asc')
                        ->get();
                } else {
                    $sid = $s->nama_souvenir;
                    $souvenirs = souvenir::where('nama_souvenir', 'LIKE', "%{$sid}%")
                        ->where('stok', '>', 0)
                        ->get();
                    //dd($souvenirs);
                }

                if ($souvenirs->isEmpty()) {
                    return response()->json([
                        'status' => 'error',
                        'title' => 'Mohon Maaf',
                        'text' => 'Data tidak sesuai atau tidak ada. Silahkan Hubungi Administrator!',
                    ], 500);
                }
            }

            // =======================================================
            // CASE: Offline
            // =======================================================
            elseif ($rkm->metode_kelas == "Offline") {
                $souvenirs = souvenir::where('min_harga_pelatihan', '<=', $harga_jual)
                    ->where('max_harga_pelatihan', '>=', $harga_jual)
                    ->where('stok', '>', 0)
                    ->orderBy('min_harga_pelatihan', 'asc')
                    ->get();

                if ($souvenirs->isEmpty()) {
                    $souvenirs = souvenir::whereBetween('min_harga_pelatihan', [6000000, 15000000])
                        ->where('stok', '>', 0)
                        ->orderBy('min_harga_pelatihan', 'asc')
                        ->get();
                }
            }

            // =======================================================
            // CASE: Tidak Sesuai
            // =======================================================
            else {
                return response()->json([
                    'status' => 'error',
                    'title' => 'Mohon Maaf',
                    'text' => 'Data tidak sesuai. Silahkan Hubungi Administrator!',
                ], 500);
            }

            // =======================================================
            // Grouping souvenir berdasarkan nama + size
            // =======================================================
            $groupedSouvenirs = [];

            foreach ($souvenirs as $souvenir) {
                $namaBase = explode(' - ', $souvenir->nama_souvenir);
                $baseName = $namaBase[0];
                $size = isset($namaBase[1]) ? trim($namaBase[1]) : null;

                if (!isset($groupedSouvenirs[$baseName])) {
                    $groupedSouvenirs[$baseName] = [
                        'nama_souvenir' => $baseName,
                        'harga' => $souvenir->harga,
                        'min_harga_pelatihan' => $souvenir->min_harga_pelatihan,
                        'max_harga_pelatihan' => $souvenir->max_harga_pelatihan,
                        'stok' => $souvenir->stok,
                        'foto' => base64_encode($souvenir->blob_foto),
                        'sizes' => []
                    ];
                }

                if ($size) {
                    $groupedSouvenirs[$baseName]['sizes'][] = [
                        'size' => $size,
                        'stok' => (int) $souvenir->stok
                    ];
                }
            }

            return response()->json(array_values($groupedSouvenirs));
        }
    }

    public function getFeedbacksByMonth($year, $month)
    {
        // Tentukan rentang tanggal untuk bulan dan tahun yang diberikan
        $date = CarbonImmutable::create($year, $month, 1);
        $startDate = $date->startOfMonth();
        $endDate = $date->endOfMonth();
        $startDateFormatted = $startDate->format('Y-m-d');
        $endDateFormatted = $endDate->format('Y-m-d');

        // Ambil Nilaifeedback yang terkait dengan rentang tanggal yang diberikan
        $feedbacks = Nilaifeedback::with(['rkm.materi'])
            ->whereHas('rkm', function ($query) use ($startDateFormatted, $endDateFormatted) {
                $query->whereBetween('tanggal_awal', [$startDateFormatted, $endDateFormatted]);
            })
            ->get();
        // return $feedbacks;

        // Kelompokkan feedback berdasarkan nama materi
        $groupedFeedbacks = $feedbacks->groupBy(function ($feedback) {
            return $feedback->rkm->materi->nama_materi;
        });

        $averageFeedbacks = [];

        foreach ($groupedFeedbacks as $nama_materi => $feedbackGroup) {
            $materi_key = $feedbackGroup->first()->rkm->materi_key;
            $instruktur_key = $feedbackGroup->first()->rkm->instruktur_key;
            $sales_key = $feedbackGroup->first()->rkm->sales_key;
            $created_at = $feedbackGroup->first()->created_at;
            $tanggal_awal = Carbon::parse($feedbackGroup->first()->rkm->tanggal_awal)->format('Y-m-d');
            $tanggal_akhir = $feedbackGroup->first()->rkm->tanggal_akhir;
            $totalFeedbacks = $feedbackGroup->count();

            $averageFeedbacks[] = [
                'nama_materi' => $nama_materi,
                'materi_key' => $materi_key,
                'instruktur_key' => $instruktur_key,
                'sales_key' => $sales_key,
                'tanggal_awal' => $tanggal_awal,
                'tanggal_akhir' => $tanggal_akhir,
                'created_at' => $created_at,
                'averageM1' => $feedbackGroup->avg('M1'),
                'averageM2' => $feedbackGroup->avg('M2'),
                'averageM3' => $feedbackGroup->avg('M3'),
                'averageM4' => $feedbackGroup->avg('M4'),
                'averageP1' => $feedbackGroup->avg('P1'),
                'averageP2' => $feedbackGroup->avg('P2'),
                'averageP3' => $feedbackGroup->avg('P3'),
                'averageP4' => $feedbackGroup->avg('P4'),
                'averageP5' => $feedbackGroup->avg('P5'),
                'averageP6' => $feedbackGroup->avg('P6'),
                'averageP7' => $feedbackGroup->avg('P7'),
                'averageF1' => $feedbackGroup->avg('F1'),
                'averageF2' => $feedbackGroup->avg('F2'),
                'averageF3' => $feedbackGroup->avg('F3'),
                'averageF4' => $feedbackGroup->avg('F4'),
                'averageF5' => $feedbackGroup->avg('F5'),
                'averageI1' => $feedbackGroup->avg('I1'),
                'averageI2' => $feedbackGroup->avg('I2'),
                'averageI3' => $feedbackGroup->avg('I3'),
                'averageI4' => $feedbackGroup->avg('I4'),
                'averageI5' => $feedbackGroup->avg('I5'),
                'averageI6' => $feedbackGroup->avg('I6'),
                'averageI7' => $feedbackGroup->avg('I7'),
                'averageI8' => $feedbackGroup->avg('I8'),
                'averageI1b' => $feedbackGroup->avg('I1b'),
                'averageI2b' => $feedbackGroup->avg('I2b'),
                'averageI3b' => $feedbackGroup->avg('I3b'),
                'averageI4b' => $feedbackGroup->avg('I4b'),
                'averageI5b' => $feedbackGroup->avg('I5b'),
                'averageI6b' => $feedbackGroup->avg('I6b'),
                'averageI7b' => $feedbackGroup->avg('I7b'),
                'averageI8b' => $feedbackGroup->avg('I8b'),
                'averageI1as' => $feedbackGroup->avg('I1as'),
                'averageI2as' => $feedbackGroup->avg('I2as'),
                'averageI3as' => $feedbackGroup->avg('I3as'),
                'averageI4as' => $feedbackGroup->avg('I4as'),
                'averageI5as' => $feedbackGroup->avg('I5as'),
                'averageI6as' => $feedbackGroup->avg('I6as'),
                'averageI7as' => $feedbackGroup->avg('I7as'),
                'averageI8as' => $feedbackGroup->avg('I8as'),
                'averageM' => round(($feedbackGroup->avg('M1') + $feedbackGroup->avg('M2') + $feedbackGroup->avg('M3') + $feedbackGroup->avg('M4')) / 4, 1),
                'averageP' => round(($feedbackGroup->avg('P1') + $feedbackGroup->avg('P2') + $feedbackGroup->avg('P3') + $feedbackGroup->avg('P4') + $feedbackGroup->avg('P5') + $feedbackGroup->avg('P6') + $feedbackGroup->avg('P7')) / 7, 1),
                'averageF' => round(($feedbackGroup->avg('F1') + $feedbackGroup->avg('F2') + $feedbackGroup->avg('F3') + $feedbackGroup->avg('F4') + $feedbackGroup->avg('F5')) / 5, 1),
                'averageI' => round(($feedbackGroup->avg('I1') + $feedbackGroup->avg('I2') + $feedbackGroup->avg('I3') + $feedbackGroup->avg('I4') + $feedbackGroup->avg('I5') + $feedbackGroup->avg('I6') + $feedbackGroup->avg('I7') + $feedbackGroup->avg('I8')) / 8, 1),
                'averageIb' => round(($feedbackGroup->avg('I1b') + $feedbackGroup->avg('I2b') + $feedbackGroup->avg('I3b') + $feedbackGroup->avg('I4b') + $feedbackGroup->avg('I5b') + $feedbackGroup->avg('I6b') + $feedbackGroup->avg('I7b') + $feedbackGroup->avg('I8b')) / 8, 1),
                'averageIas' => round(($feedbackGroup->avg('I1as') + $feedbackGroup->avg('I2as') + $feedbackGroup->avg('I3as') + $feedbackGroup->avg('I4as') + $feedbackGroup->avg('I5as') + $feedbackGroup->avg('I6as') + $feedbackGroup->avg('I7as') + $feedbackGroup->avg('I8as')) / 8, 1),
                'feedback' => $feedbackGroup,
            ];
        }

        // Urutkan hasil berdasarkan tanggal_awal
        $sortedFeedbacks = collect($averageFeedbacks)->sortBy('tanggal_awal')->values()->all();

        // return $sortedFeedbacks;
        return response()->json([
            'success' => true,
            'message' => 'List Feedbacks',
            'data' => $sortedFeedbacks
            // 'data' => $groupedFeedbacks
        ]);
    }

    public function exportExcelKhusus(string $id)
    {
        // Ambil data menggunakan metode getFeedbackData yang sudah ada
        $post = $this->getFeedbackData($id);

        // Konfigurasi header Excel
        $data = $post->flatMap(function ($item) {
            return $item['data']->map(function ($feedback) {
                return [
                    'Nama Perusahaan' => $feedback['nama_perusahaan'],
                    'ID Registrasi' => $feedback['id_regist'],
                    'ID RKM' => $feedback['id_rkm'],
                    'Nama Materi' => $feedback['nama_materi'],
                    'Sales Key' => $feedback['sales_key'],
                    'Instruktur Key' => $feedback['instruktur_key'],
                    'Instruktur Key 2' => $feedback['instruktur_key2'],
                    'Asisten Key' => $feedback['asisten_key'],
                    'Tanggal Awal' => $feedback['tanggal_awal'],
                    'Tanggal Akhir' => $feedback['tanggal_akhir'],
                    'Email' => $feedback['email'],
                    'Materi' => $feedback['materi'],
                    'Pelayanan' => $feedback['pelayanan'],
                    'Fasilitas' => $feedback['fasilitas'],
                    'Instruktur' => $feedback['instruktur'],
                    'Instruktur 2' => $feedback['instruktur2'],
                    'Asisten' => $feedback['asisten'],
                    'Umum 1' => $feedback['umum1'],
                    'Umum 2' => $feedback['umum2'],
                    // Access M1 and M2 directly from feedback
                    'Materi 1' => $feedback['datafeedbacks']->M1,
                    'Materi 2' => $feedback['datafeedbacks']->M2,
                    'Materi 3' => $feedback['datafeedbacks']->M3,
                    'Materi 4' => $feedback['datafeedbacks']->M4,
                    'Pelayanan 1' => $feedback['datafeedbacks']->P1,
                    'Pelayanan 2' => $feedback['datafeedbacks']->P2,
                    'Pelayanan 3' => $feedback['datafeedbacks']->P3,
                    'Pelayanan 4' => $feedback['datafeedbacks']->P4,
                    'Pelayanan 5' => $feedback['datafeedbacks']->P5,
                    'Pelayanan 6' => $feedback['datafeedbacks']->P6,
                    'Fasilitas 1' => $feedback['datafeedbacks']->F1,
                    'Fasilitas 2' => $feedback['datafeedbacks']->F2,
                    'Fasilitas 3' => $feedback['datafeedbacks']->F3,
                    'Fasilitas 4' => $feedback['datafeedbacks']->F4,
                    'Fasilitas 5' => $feedback['datafeedbacks']->F5,
                    'Instruktur 1' => $feedback['datafeedbacks']->I1,
                    'Instruktur 2' => $feedback['datafeedbacks']->I2,
                    'Instruktur 3' => $feedback['datafeedbacks']->I3,
                    'Instruktur 4' => $feedback['datafeedbacks']->I4,
                    'Instruktur 5' => $feedback['datafeedbacks']->I5,
                    'Instruktur 6' => $feedback['datafeedbacks']->I6,
                    'Instruktur 7' => $feedback['datafeedbacks']->I7,
                    'Instruktur 8' => $feedback['datafeedbacks']->I8,
                    'Instruktur#2 1' => $feedback['datafeedbacks']->I1b,
                    'Instruktur#2 2' => $feedback['datafeedbacks']->I2b,
                    'Instruktur#2 3' => $feedback['datafeedbacks']->I3b,
                    'Instruktur#2 4' => $feedback['datafeedbacks']->I4b,
                    'Instruktur#2 5' => $feedback['datafeedbacks']->I5b,
                    'Instruktur#2 6' => $feedback['datafeedbacks']->I6b,
                    'Instruktur#2 7' => $feedback['datafeedbacks']->I7b,
                    'Instruktur#2 8' => $feedback['datafeedbacks']->I8b,
                    'Asisten 1' => $feedback['datafeedbacks']->I1as,
                    'Asisten 2' => $feedback['datafeedbacks']->I2as,
                    'Asisten 3' => $feedback['datafeedbacks']->I3as,
                    'Asisten 4' => $feedback['datafeedbacks']->I4as,
                    'Asisten 5' => $feedback['datafeedbacks']->I5as,
                    'Asisten 6' => $feedback['datafeedbacks']->I6as,
                    'Asisten 7' => $feedback['datafeedbacks']->I7as,
                    'Asisten 8' => $feedback['datafeedbacks']->I8as,
                ];
            });
        });
        // return $data;
        // Ekspor ke Excel
        return Excel::download(new FeedbackSalesExport($data), 'Feedback_Data.xlsx');
    }

    public function exportPDFKhusus(string $id)
    {
        // Ambil data menggunakan metode show yang sudah ada
        $data = $this->getFeedbackData($id);
        // return $post;
        // Generate PDF dari tampilan dengan data yang diperoleh
        $pdf = PDF::loadView('exports.feedback-pdf', compact('data'));

        return $pdf->download('Feedback_Data.pdf');
    }

    private function getFeedbackData(string $id)
    {
        // Split the incoming ID to extract the necessary parts
        $array = explode('ixb', $id);
        $materiKey = $array[0];
        $bulan = $array[1];
        $tahun = $array[2];
        $hari = $array[3];
        $tanggal_awal = $tahun . '-' . $bulan . '-' . $hari;

        // Query the feedbacks with the given criteria and eager load relationships
        $feedbacks = Nilaifeedback::with('rkm', 'regist')
            ->whereHas('rkm', function ($query) use ($materiKey, $tanggal_awal) {
                $query->where('materi_key', $materiKey)
                    ->where('tanggal_awal', $tanggal_awal);
            })
            ->get();

        // Transform the feedback data
        $transformedFeedbacks = $feedbacks->map(function ($feedback) {
            return [
                'id_regist' => $feedback->id_regist,
                'id_rkm' => $feedback->id_rkm,
                'nama_materi' => $feedback->rkm->materi->nama_materi,
                'sales_key' => $feedback->rkm->sales_key,
                'instruktur_key' => $feedback->rkm->instruktur_key,
                'instruktur_key2' => $feedback->rkm->instruktur_key2,
                'asisten_key' => $feedback->rkm->asisten_key,
                'tanggal_awal' => $feedback->rkm->tanggal_awal,
                'tanggal_akhir' => $feedback->rkm->tanggal_akhir,
                'email' => $feedback->email,
                'nama_perusahaan' => $feedback->rkm->perusahaan->nama_perusahaan,
                'materi' => round(($feedback->M1 + $feedback->M2 + $feedback->M3 + $feedback->M4) / 4, 1),
                'pelayanan' => round(($feedback->P1 + $feedback->P2 + $feedback->P3 + $feedback->P4 + $feedback->P5 + $feedback->P6 + $feedback->P7) / 7, 1),
                'fasilitas' => round(($feedback->F1 + $feedback->F2 + $feedback->F3 + $feedback->F4 + $feedback->F5) / 5, 1),
                'instruktur' => round(($feedback->I1 + $feedback->I2 + $feedback->I3 + $feedback->I4 + $feedback->I5 + $feedback->I6 + $feedback->I7 + $feedback->I8) / 8, 1),
                'instruktur2' => round(($feedback->I1b + $feedback->I2b + $feedback->I3b + $feedback->I4b + $feedback->I5b + $feedback->I6b + $feedback->I7b + $feedback->I8b) / 8, 1),
                'asisten' => round(($feedback->I1as + $feedback->I2as + $feedback->I3as + $feedback->I4as + $feedback->I5as + $feedback->I6as + $feedback->I7as + $feedback->I8as) / 8, 1),
                'umum1' => $feedback->U1,
                'umum2' => $feedback->U2,
                'datafeedbacks' => $feedback,
            ];
        });

        return $transformedFeedbacks->groupBy('nama_perusahaan')->map(function ($groupedFeedbacks, $nama_perusahaan) {
            return [
                'nama_perusahaan' => $nama_perusahaan,
                'data' => $groupedFeedbacks
            ];
        })->values();
    }

    public function getNilaiFeedbackInstRKM(string $id)
    {
        $data = Nilaifeedback::where('id_rkm', $id)->get();

        $transformedFeedbacks = $data->map(function ($feedback) {
            return [
                'instruktur' => ($feedback->I1 + $feedback->I2 + $feedback->I3 + $feedback->I4 + $feedback->I5 + $feedback->I6 + $feedback->I7 + $feedback->I8) / 8,
                'instruktur2' => ($feedback->I1b + $feedback->I2b + $feedback->I3b + $feedback->I4b + $feedback->I5b + $feedback->I6b + $feedback->I7b + $feedback->I8b) / 8,
                'asisten' => ($feedback->I1as + $feedback->I2as + $feedback->I3as + $feedback->I4as + $feedback->I5as + $feedback->I6as + $feedback->I7as + $feedback->I8as) / 8
            ];
        });

        // Menghitung rata-rata dari semua feedback
        $averageFeedback = [
            'instruktur' => round($transformedFeedbacks->pluck('instruktur')->avg(), 1),
            'instruktur2' => round($transformedFeedbacks->pluck('instruktur2')->avg(), 1),
            'asisten' => round($transformedFeedbacks->pluck('asisten')->avg(), 1)
        ];

        return response()->json([
            'feedbacks' => $transformedFeedbacks,
            'average' => $averageFeedback
        ]);
    }
}

