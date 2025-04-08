<?php

namespace App\Http\Controllers;

use App\Exports\FeedbackSalesExport;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\Feedback;
use App\Models\Nilaifeedback;
use App\Models\RKM;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use App\Exports\NilaifeedbackExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class feedbackController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:View Feedback', ['only' => ['index']]);
        $this->middleware('permission:Create Feedback', ['only' => ['create','store']]);
        $this->middleware('permission:Detail Feedback Per Bulan', ['only' => ['detailfeedbacks']]);
       
    }
    public function index()
    {
        return view('feedback.index');
    }
    /**
     * create
     *
     * @return View
     */
    public function create(): View
    {
        return view('feedback.create');
    }
    /**
     * store
     *
     * @param  mixed $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $this->validate($request, [
            'kategori_feedback' => 'nullable',
            'pertanyaan' => 'nullable',
        ]);

        $kategori_feedback = $request->kategori_feedback;
        $huruf_pertama = substr($kategori_feedback, 0, 1);
        Feedback::create([
            'kategori_feedback' => $request->kategori_feedback,
            'pertanyaan' => $request->pertanyaan,
            'key' => $huruf_pertama,
        ]);

        return redirect()->route('feedback.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }
    /**
     * show
     *
     * @param  mixed $id
     * @return View
     */
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

        $groupedFeedbacks = $transformedFeedbacks->groupBy('nama_perusahaan')->map(function ($groupedFeedbacks, $nama_perusahaan) {
            return [
                'nama_perusahaan' => $nama_perusahaan,
                'data' => $groupedFeedbacks,
                // 'feedbacks' => $groupedFeedbacks->pluck('datafeedbacks')
            ];
        });

        $post = $groupedFeedbacks->values();

        // return response()->json(['post' => $post]);
        return view('feedback.show', compact('post', 'id'));

    }
    /**
     * edit
     *
     * @param  mixed $id
     * @return View
     */
    public function edit(string $id): View
    {
        //get post by ID
        $perusahaans = Feedback::findOrFail($id);

        //render view with post
        return view('feedback.edit', compact('perusahaans'));
    }
    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //validate form
        $this->validate($request, [
            'kategori_feedback' => 'required',
            'pertanyaan' => 'nullable',
        ]);

        $post = Feedback::findOrFail($id);

            $post->update([
                'kategori_feedback' => $request->kategori_feedback,
                'pertanyaan' => $request->pertanyaan,
            ]);

        return redirect()->route('feedback.index')->with(['success' => 'Data Berhasil Diubah!']);
    }
    /**
     * destroy
     *
     * @param  mixed $post
     * @return void
     */
    public function destroy($id): RedirectResponse
    {
        $post = Feedback::findOrFail($id);

        $post->delete();

        return redirect()->route('feedback.index')->with(['success' => 'Data Berhasil Dihapus!']);
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
            ->whereHas('rkm', function($query) use ($startDateFormatted, $endDateFormatted) {
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
    public function detailfeedbacks()
    {
        return view('feedback.feedbackperbulan');
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
        $post = $this->getFeedbackData($id);
        // return $post;
        // Generate PDF dari tampilan dengan data yang diperoleh
        $pdf = PDF::loadView('exports.feedback-pdf', compact('post'));

        return $pdf->download('Feedback_Data.pdf');
    }
    // Helper function untuk mendapatkan data yang sama seperti di metode show
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
