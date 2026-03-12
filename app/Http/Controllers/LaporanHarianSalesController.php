<?php

namespace App\Http\Controllers;

use App\Models\CatatanClientSales;
use App\Models\CatatanMeetingSales;
use App\Models\karyawan;
use App\Models\LaporanHarianSales;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LaporanHarianSalesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $laporans = LaporanHarianSales::with('catatanSales', 'catatanClient')->orderBy('created_at', 'desc')->get();

        return view('crm.laporanHarian.index', compact('laporans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sales = karyawan::where('divisi', 'Sales & Marketing')
            ->get();

        return view('crm.laporanHarian.create', compact('sales'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'tanggal_pelaksanaan' => 'required|date',
            'waktu_pelaksanaan' => 'required|regex:/^\d{2}:\d{2}$/',
            'tempat_or_media' => 'required|string',
            'jumlah_peserta_hadir' => 'required|numeric|min:1',
            'jumlah_peserta_tidak_hadir' => 'nullable|numeric',
            'alasan_peserta_tidak_hadir' => 'nullable',
            'jenis_meeting' => 'required|string',
            'pic' => 'required',
            'notulis' => 'nullable',
            'topic' => 'required',
            'catatan' => 'nullable',
        ]);

        $laporan = LaporanHarianSales::create([
            'tanggal_pelaksanaan' => $request->tanggal_pelaksanaan,
            'waktu_pelaksanaan' => trim($request->waktu_pelaksanaan),
            'tempat_or_media' => $request->tempat_or_media,
            'jumlah_peserta_hadir' => $request->jumlah_peserta_hadir,
            'jumlah_peserta_tidak_hadir' => $request->jumlah_peserta_tidak_hadir ?? null,
            'alasan_peserta_tidak_hadir' => $request->alasan_peserta_tidak_hadir ?? null,
            'jenis_meeting' => $request->jenis_meeting,
            'pic' => $request->pic,
            'notulis' => $request->notulis,
            'topic' => $request->topic,
            'catatan' => $request->catatan ?? null,
        ]);


        // Catanan untuk sales
        if ($request->sales) {

            $request->validate([
                'sales' => 'required',
                'catatan_sales' => 'required'
            ]);

            foreach ($request->sales as $index => $sales_id) {

                if (!$sales_id) {
                    continue;
                }

                CatatanMeetingSales::create([
                    'laporan_id' => $laporan->id,
                    'sales_id' => $sales_id,
                    'catatan' => $request->catatan_sales[$index] ?? null,
                ]);
            }

        }

        // Catatan Untuk Client
        if ($request->nama_perusahaan) {

            $request->validate([
                'nama_perusahaan' => 'required',
            ]);

            foreach ($request->nama_perusahaan as $index => $nama_perusahaan){

                if (!$nama_perusahaan) {
                    continue;
                }

                CatatanClientSales::create([
                    'laporan_id' => $laporan->id,
                    'nama_perusahaan' => $nama_perusahaan,
                    'kebutuhan' => $request->kebutuhan[$index] ?? null,
                    'rekomendasi_silabus' => $request->rekomendasi_silabus[$index] ?? null,
                    'catatan' => $request->catatan_client[$index] ?? null,
                ]);
            }
        }

        return redirect()->route('laporan.harian')->with('success', 'Laporan berhasil disimpan.');
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $laporan = LaporanHarianSales::with('catatanSales')->findOrFail($id);
        $sales = karyawan::where('divisi', 'Sales & Marketing')
            ->get();

        return view('crm.laporanHarian.detail', compact('laporan', 'sales'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // dd($request->all());
        $laporan = LaporanHarianSales::findOrFail($id);

        $request->validate([
            'tanggal_pelaksanaan' => 'required|date',
            'waktu_pelaksanaan' => 'required|regex:/^\d{2}:\d{2}$/',
            'tempat_or_media' => 'required|string',
            'jumlah_peserta_hadir' => 'required|numeric|min:1',
            'jumlah_peserta_tidak_hadir' => 'nullable|numeric',
            'alasan_peserta_tidak_hadir' => 'nullable',
            'jenis_meeting' => 'required|string',
            'pic' => 'required',
            'notulis' => 'nullable',
            'topic' => 'required',
            'catatan' => 'nullable',
        ]);

        $laporan->update([
            'tanggal_pelaksanaan' => $request->tanggal_pelaksanaan,
            'waktu_pelaksanaan' => trim($request->waktu_pelaksanaan),
            'tempat_or_media' => $request->tempat_or_media,
            'jumlah_peserta_hadir' => $request->jumlah_peserta_hadir,
            'jumlah_peserta_tidak_hadir' => $request->jumlah_peserta_tidak_hadir ?? null,
            'alasan_peserta_tidak_hadir' => $request->alasan_peserta_tidak_hadir ?? null,
            'jenis_meeting' => $request->jenis_meeting,
            'pic' => $request->pic,
            'notulis' => $request->notulis ?? null,
            'topic' => $request->topic,
            'catatan' => $request->catatan ?? null,
        ]);

        // Delete existing catatan sales
        CatatanMeetingSales::where('laporan_id', $laporan->id)->delete();

        // Create new catatan sales
        if ($request->sales) {

            $request->validate([
                'sales' => 'required',
                'catatan_sales' => 'required'
            ]);

            foreach ($request->sales as $index => $sales_id) {

                if (!$sales_id) {
                    continue;
                }

                CatatanMeetingSales::create([
                    'laporan_id' => $laporan->id,
                    'sales_id' => $sales_id,
                    'catatan' => $request->catatan_sales[$index] ?? null,
                ]);
            }

        }

        // Delete existing catatan sales
        CatatanClientSales::where('laporan_id', $laporan->id)->delete();

        // Create Catatan Untuk Client
        if ($request->nama_perusahaan) {

            $request->validate([
                'nama_perusahaan' => 'required',
            ]);

            foreach ($request->nama_perusahaan as $index => $nama_perusahaan){

                if (!$nama_perusahaan) {
                    continue;
                }

                CatatanClientSales::create([
                    'laporan_id' => $laporan->id,
                    'nama_perusahaan' => $nama_perusahaan,
                    'kebutuhan' => $request->kebutuhan[$index] ?? null,
                    'rekomendasi_silabus' => $request->rekomendasi_silabus[$index] ?? null,
                    'catatan' => $request->catatan_client[$index] ?? null,
                ]);
            }
        }

        return redirect()->route('laporan.harian')->with('success', 'Laporan berhasil diperbarui.');
    }

    /**
     * Delete the specified resource from storage.
     */
    public function delete(string $id)
    {
        $laporan = LaporanHarianSales::findOrFail($id);

        // Hapus catatan sales yang terkait
        CatatanMeetingSales::where('laporan_id', $laporan->id)->delete();
        
        // Hapus catatan client yang terkait
        CatatanClientSales::where('laporan_id', $laporan->id)->delete();

        // Hapus laporan harian
        $laporan->delete();

        return redirect()->route('laporan.harian')->with('success', 'Laporan berhasil dihapus.');
    }

    public function exportPdf($id, $type)
    {
        $laporan = LaporanHarianSales::findOrFail($id);
        $pdf = Pdf::loadView('crm.laporanHarian.exportPdf', compact('laporan', 'type'));

        return $pdf->download('laporan_mom.'.$laporan->tanggal_pelaksanaan.'.pdf');
    }
}
