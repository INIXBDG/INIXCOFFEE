<?php

namespace App\Http\Controllers;

use App\Models\PicPenagihanInvoice;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PicPenagihanController extends Controller
{
    public function index()
    {
        return view('office.picpenagihan.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_rkm' => 'required',
            'perusahaan_id' => 'required',
            'alamat' => 'nullable|string',
            'category' => 'required|string',
            'pic' => 'required|string',
            'telepon' => 'required|string',
        ]);

        PicPenagihanInvoice::create([
            'id_rkm' => $request->id_rkm,
            'perusahaan_id' => $request->perusahaan_id,
            'alamat' => $request->alamat,
            'category' => $request->category,
            'pic' => $request->pic,
            'telepon' => $request->telepon,
            'status' => '0',
        ]);

        return redirect()->route('picpenagihan.index')->with('success', 'Data PIC Penagihan berhasil disimpan.');
    }

    public function getData(Request $request)
    {
        $query = PicPenagihanInvoice::with([
            'perusahaan',
            'rkm.materi',
            'rkm.outstanding'
        ]);

        if ($request->has('filter_tahun') && !empty($request->filter_tahun)) {
            $query->whereYear('created_at', $request->filter_tahun);
        }

        if ($request->has('filter_bulan') && !empty($request->filter_bulan)) {
            $query->whereMonth('created_at', $request->filter_bulan);
        }

        $data = $query->get();

        return response()->json([
            'data' => $data
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'alamat' => 'nullable|string',
            'category' => 'required|string',
            'pic' => 'required|string',
            'telepon' => 'required|string',
        ]);

        $picPenagihan = PicPenagihanInvoice::findOrFail($id);
        $picPenagihan->update([
            'alamat' => $request->alamat,
            'category' => $request->category,
            'pic' => $request->pic,
            'telepon' => $request->telepon,
        ]);

        return back()->with('success', 'Data PIC Penagihan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $picPenagihan = PicPenagihanInvoice::findOrFail($id);
        $picPenagihan->delete();

        return back()->with('success', 'Data PIC Penagihan berhasil dihapus.');
    }

    public function exportPdf($id)
    {
        $data = PicPenagihanInvoice::with(['perusahaan'])->findOrFail($id);
        $pdf = Pdf::loadView('office.picpenagihan.pdf', compact('data'));
        $pdf->setPaper('A4', 'portrait');
        $namaPerusahaan = $data->perusahaan ? $data->perusahaan->nama_perusahaan : 'Unknown';
        $namaFile = 'Invoice_' . str_replace(' ', '_', $namaPerusahaan) . '.pdf';

        return $pdf->download($namaFile);
    }

}
