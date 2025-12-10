<?php

namespace App\Http\Controllers;

use App\Models\SurveyKepuasan;
use Illuminate\Http\Request;

class SurveyKepuasanController extends Controller
{
    public function index()
    {
        return view('surveykepuasan.create');
    }

    public function store(Request $request)
    {
        $id_user = auth()->user()->id;

        $validatedData = $request->validate([
            'q1' => 'required|integer',
            'q2' => 'required|string',
            'q3' => 'nullable|string',
            'q4' => 'required|integer',
            'q5' => 'nullable|string',
        ]);

        $surveyKepuasan = new SurveyKepuasan();
        $surveyKepuasan->id_user = $id_user;
        $surveyKepuasan->q1 = $request->q1;
        $surveyKepuasan->q2 = $request->q2;
        $surveyKepuasan->q3 = $request->q3;
        $surveyKepuasan->q4 = $request->q4;
        $surveyKepuasan->q5 = $request->q5;
        $surveyKepuasan->save();

        return redirect()->back()->with('success', 'Survey berhasil dikirim! Terima kasih atas partisipasi Anda.');
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
