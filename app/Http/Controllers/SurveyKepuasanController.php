<?php

namespace App\Http\Controllers;

use App\Models\SurveyKepuasan;
use App\Models\Tickets;
use Illuminate\Http\Request;

class SurveyKepuasanController extends Controller
{
    public function index(Request $request)
    {
        // Menangkap parameter query string ?ticket_id=...
        $ticket_id = $request->query('ticket_id');

        // Memastikan parameter tersedia dan tiket ditemukan
        $ticket = Tickets::where('ticket_id', $ticket_id)->firstOrFail();

        return view('surveykepuasan.create', compact('ticket'));
    }

    public function store(Request $request)
    {
        $id_user = auth()->user()->id;

        $request->validate([
            'ticket_id' => 'required|exists:tickets,ticket_id',
            'q1' => 'required|integer',
            'q2' => 'required|string',
            'q3' => 'nullable|string',
            'q4' => 'required|integer',
            'q5' => 'nullable|string',
        ]);

        $surveyKepuasan = new SurveyKepuasan();
        $surveyKepuasan->id_user = $id_user;
        $surveyKepuasan->ticket_id = $request->ticket_id;
        $surveyKepuasan->q1 = $request->q1;
        $surveyKepuasan->q2 = $request->q2;
        $surveyKepuasan->q3 = $request->q3;
        $surveyKepuasan->q4 = $request->q4;
        $surveyKepuasan->q5 = $request->q5;
        $surveyKepuasan->save();

        // Pembaruan status tiket menjadi sudah disurvei
        Tickets::where('ticket_id', $request->ticket_id)->update([
            'is_surveyed' => true
        ]);

        return redirect()->route('tickets.index')->with('success', 'Survey berhasil dikirim! Terima kasih atas partisipasi Anda.');
    }

    public function indexTable()
    {
        $data = SurveyKepuasan::with('karyawan')->latest()->get();
        return view('surveykepuasan.index', compact('data'));
    }

    public function destroy($id)
    {
        $data = SurveyKepuasan::find($id);

        if (!$data) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $data->delete();

        return response()->json(['message' => 'Data berhasil dihapus'], 200);
    }
}