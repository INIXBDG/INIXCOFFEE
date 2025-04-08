<?php

namespace App\Http\Controllers;

use App\Models\RKM;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Feedback;
use App\Models\Nilaifeedback;
use App\Models\Registrasi;
use App\Exports\NilaifeedbackExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\CarbonImmutable;


class nilaifeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $month = Carbon::now()->month;
        // $post = RKM::whereMonth('tanggal_awal', $month)->get();
        $post = RKM::latest()->get();
        $materi = Feedback::where('kategori_feedback', 'Materi')->get();
        $pelayanan = Feedback::where('kategori_feedback', 'Pelayanan')->get();
        $fasilitas = Feedback::where('kategori_feedback', 'Fasilitas Laboratium')->get();
        $instruktur = Feedback::where('kategori_feedback', 'Instruktur')->get();
        // $jmlInstruktur = RKM::with('instruktur');
        $umum = Feedback::where('kategori_feedback', 'Umum')->get();
        return view('nilaifeedback.create', compact('post', 'materi', 'fasilitas', 'instruktur', 'umum', 'pelayanan'));

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $regist = Registrasi::with('peserta')->where('id', $request->id_regist)->first();
        $email = $regist->peserta->email;
        // return $email;
        $nilaiFeedback = Nilaifeedback::where('id_regist', $request->id_regist)
            ->where('id_rkm', $request->id_rkm)
            ->first();
        if($nilaiFeedback){
            return redirect()->route('feedback.index')->with(['error' => 'Mohon maaf anda sudah mengisi Feedback ini!']);
        } else {
            Nilaifeedback::create(array_merge($request->except('email'), ['email' => $email]));
            return redirect()->route('feedback.index')->with(['success' => 'Terimakasih sudah mengisi Feedback ini!']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $materi = Feedback::where('kategori_feedback', 'Materi')->get();
        $pelayanan = Feedback::where('kategori_feedback', 'Pelayanan')->get();
        $fasilitas = Feedback::where('kategori_feedback', 'Fasilitas Laboratium')->get();
        $instruktur = Feedback::where('kategori_feedback', 'Instruktur')->get();
        // $jmlInstruktur = RKM::with('instruktur');
        $umum = Feedback::where('kategori_feedback', 'Umum')->get();
        $nilaifeedback = Nilaifeedback::find($id);
        // return $nilaifeedback;
        return view('nilaifeedback.edit', compact('nilaifeedback', 'materi', 'pelayanan', 'fasilitas', 'instruktur', 'umum'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // dd($request->all());
        $nilaiFeedback = Nilaifeedback::find($id);
        $nilaiFeedback->update($request->all());
        return redirect()->route('feedback.index')->with(['success' => 'Data Berhasil Diupdate! ']);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function export($year, $month)
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

        $averageFeedbacks = [];
        $no = 1;
        foreach ($feedbacks as $feedbackGroup) {
            $averageFeedbacks[] = [
                'no' => $no,
                'nama_materi' => $feedbackGroup->rkm->materi->nama_materi,
                'instruktur_key' => $feedbackGroup->rkm->instruktur_key,
                'sales_key' => $feedbackGroup->rkm->sales_key,
                'tanggal_awal' => $feedbackGroup->rkm->tanggal_awal,
                'tanggal_akhir' => $feedbackGroup->rkm->tanggal_akhir,
                'M1' => $feedbackGroup->M1,
                'M2' => $feedbackGroup->M2,
                'M3' => $feedbackGroup->M3,
                'M4' => $feedbackGroup->M4,
                'P1' => $feedbackGroup->P1,
                'P2' => $feedbackGroup->P2,
                'P3' => $feedbackGroup->P3,
                'P4' => $feedbackGroup->P4,
                'P5' => $feedbackGroup->P5,
                'P6' => $feedbackGroup->P6,
                'P7' => $feedbackGroup->P7,
                'F1' => $feedbackGroup->F1,
                'F2' => $feedbackGroup->F2,
                'F3' => $feedbackGroup->F3,
                'F4' => $feedbackGroup->F4,
                'F5' => $feedbackGroup->F5,
                'I1' => $feedbackGroup->I1,
                'I2' => $feedbackGroup->I2,
                'I3' => $feedbackGroup->I3,
                'I4' => $feedbackGroup->I4,
                'I5' => $feedbackGroup->I5,
                'I6' => $feedbackGroup->I6,
                'I7' => $feedbackGroup->I7,
                'I8' => $feedbackGroup->I8,
                'I1b' => $feedbackGroup->I1b,
                'I2b' => $feedbackGroup->I2b,
                'I3b' => $feedbackGroup->I3b,
                'I4b' => $feedbackGroup->I4b,
                'I5b' => $feedbackGroup->I5b,
                'I6b' => $feedbackGroup->I6b,
                'I7b' => $feedbackGroup->I7b,
                'I8b' => $feedbackGroup->I8b,
                'I1as' => $feedbackGroup->I1as,
                'I2as' => $feedbackGroup->I2as,
                'I3as' => $feedbackGroup->I3as,
                'I4as' => $feedbackGroup->I4as,
                'I5as' => $feedbackGroup->I5as,
                'I6as' => $feedbackGroup->I6as,
                'I7as' => $feedbackGroup->I7as,
                'I8as' => $feedbackGroup->I8as,
                'U1' => $feedbackGroup->U1,
                'U2' => $feedbackGroup->U2,
            ];
            $no++;
        }
        // return $averageFeedbacks;


        // Urutkan hasil berdasarkan tanggal_awal
        $sortedFeedbacks = collect($averageFeedbacks)->sortBy('tanggal_awal')->values()->all();
        $data = $averageFeedbacks;
        $monthName = \Carbon\Carbon::create()->locale('id')->month($month)->translatedFormat('F');
        $filename = 'Feedback Bulan ' . $monthName . ' Tahun ' . $year . '.xlsx';
        
		return Excel::download(new NilaifeedbackExport($data), $filename);
	}
}
