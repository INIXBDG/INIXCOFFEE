<?php

namespace App\Http\Controllers\office;

use App\Http\Controllers\Controller;
use App\Models\PoExamSertifa;
use App\Models\Materi;
use App\Models\RKM;
use App\Models\Perusahaan;
use Illuminate\Http\Request;

class PoExamSertifaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:View PoSertifa', ['only' => ['index', 'getData']]);
        $this->middleware('permission:Store PoSertifa', ['only' => ['store']]);
        $this->middleware('permission:Update PoSertifa', ['only' => ['update']]);
        $this->middleware('permission:Delete PoSertifa', ['only' => ['destroy']]);
    }

    public function index()
    {
        $rkms = RKM::with(['materi', 'perusahaan'])
                ->where('exam', '1')
                ->whereBetween('tanggal_awal', [now()->subMonth(), now()->addMonth()])
                ->orderBy('id')
                ->get();

        $skemas = PoExamSertifa::whereNotNull('skema')
                    ->where('skema', '!=', '')
                    ->distinct()
                    ->pluck('skema');

        return view('office.exam.po_exam_sertifa', compact('rkms', 'skemas'));
    }

    public function getData()
    {
        $items = PoExamSertifa::with(['materi', 'rkm', 'perusahaan'])->latest()->get();
        $materis = Materi::orderBy('nama_materi')->get();
        $rkms = RKM::orderBy('id')->get();
        $perusahaans = Perusahaan::orderBy('nama_perusahaan')->get();

        return response()->json([
            'items' => $items,
            'materis' => $materis,
            'rkms' => $rkms,
            'perusahaans' => $perusahaans
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_rkm' => 'required|integer',
            'id_materi' => 'required|integer',
            'tanggal_exam' => 'nullable|date',
            'id_perusahaan' => 'nullable|integer',
            'pax' => 'nullable|integer',
            'harga' => 'nullable|numeric',
            'skema' => 'nullable|string',
        ]);

        PoExamSertifa::create($validatedData);

        return redirect()
            ->route('office.certifa.index')
            ->with('success', 'Data PO Exam Sertifa berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $item = PoExamSertifa::findOrFail($id);

        $validatedData = $request->validate([
            'id_rkm' => 'required|integer',
            'id_materi' => 'required|integer',
            'tanggal_exam' => 'nullable|date',
            'id_perusahaan' => 'nullable|integer',
            'pax' => 'nullable|integer',
            'harga' => 'nullable|numeric',
            'skema' => 'nullable|string',
        ]);

        $item->update($validatedData);

        return redirect()
            ->route('office.certifa.index')
            ->with('success', 'Data PO Exam Sertifa berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = PoExamSertifa::findOrFail($id);
        $item->delete();

        return redirect()
            ->route('office.certifa.index')
            ->with('success', 'Data PO Exam Sertifa berhasil dihapus.');
    }
}
