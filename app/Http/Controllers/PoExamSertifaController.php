<?php

namespace App\Http\Controllers;

use App\Models\PoExamSertifa;
use App\Models\Materi;
use App\Models\RKM;
use App\Models\Perusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PoExamSertifaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $items = PoExamSertifa::with(['materi', 'rkm', 'perusahaan'])->latest()->get();
        $materis = Materi::orderBy('nama_materi')->get();
        $rkms = RKM::orderBy('id')->get();
        $perusahaans = Perusahaan::orderBy('nama_perusahaan')->get();

        return view('office.exam.po_exam_sertifa', compact('items', 'materis', 'rkms', 'perusahaans'));
    }

    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'id_materi' => ['nullable', 'integer'],
            'id_rkm' => ['nullable', 'integer'],
            'tanggal_exam' => ['nullable', 'date'],
            'id_perusahaan' => ['nullable', 'integer'],
            'pax' => ['nullable', 'integer'],
            'harga' => ['nullable', 'numeric'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('office.exam.po-exam-sertifa.index', ['t' => time()])
                        ->withErrors($validator)
                        ->withInput();
        }

        PoExamSertifa::create($validator->validated());

        return redirect()->route('office.exam.po-exam-sertifa.index', ['t' => time()])->with('success', 'Data PO Exam Sertifa berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $item = PoExamSertifa::findOrFail($id);

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'id_materi' => ['nullable', 'integer'],
            'id_rkm' => ['nullable', 'integer'],
            'tanggal_exam' => ['nullable', 'date'],
            'id_perusahaan' => ['nullable', 'integer'],
            'pax' => ['nullable', 'integer'],
            'harga' => ['nullable', 'numeric'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('office.exam.po-exam-sertifa.index', ['t' => time()])
                        ->withErrors($validator)
                        ->withInput();
        }

        $item->update($validator->validated());

        return redirect()->route('office.exam.po-exam-sertifa.index', ['t' => time()])->with('success', 'Data PO Exam Sertifa berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = PoExamSertifa::findOrFail($id);
        $item->delete();

        return redirect()->route('office.exam.po-exam-sertifa.index', ['t' => time()])->with('success', 'Data PO Exam Sertifa berhasil dihapus.');
    }

    public function data()
    {
        return response()->json(PoExamSertifa::with(['materi', 'rkm', 'perusahaan'])->latest()->get());
    }
}
