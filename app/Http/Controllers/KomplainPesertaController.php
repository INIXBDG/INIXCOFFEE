<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\RKM;
use Illuminate\Http\Request;
use App\Models\Nilaifeedback;
use App\Models\KomplainPeserta;
use Illuminate\Support\Facades\DB;

class KomplainPesertaController extends Controller
{
    public function index()
    {
        return view('komplain_peserta.index');
    }

    public function dataKomplain()
    {
        $komplains = DB::table('komplain_pesertas')
            ->leftJoin('nilaifeedbacks', 'komplain_pesertas.nilaifeedback_id', '=', 'nilaifeedbacks.id')
            ->leftJoin('r_k_m_s', 'nilaifeedbacks.id_rkm', '=', 'r_k_m_s.id')
            ->leftJoin('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
            ->leftJoin('perusahaans', 'r_k_m_s.perusahaan_key', '=', 'perusahaans.id')
            ->select(
                'komplain_pesertas.nilaifeedback_id',
                DB::raw("GROUP_CONCAT(komplain_pesertas.komplain SEPARATOR '||') as komplain"),
                DB::raw("GROUP_CONCAT(komplain_pesertas.kategori_feedback SEPARATOR '||') as kategori"),
                DB::raw("GROUP_CONCAT(komplain_pesertas.tanggal_selesai SEPARATOR '||') as tanggal_selesai"),
                DB::raw("GROUP_CONCAT(komplain_pesertas.status SEPARATOR '||') as status"),
                DB::raw("MIN(komplain_pesertas.created_at) as created_at"),
                DB::raw("CONCAT(perusahaans.nama_perusahaan, ' || ', materis.nama_materi, ' || ', r_k_m_s.tanggal_akhir) AS detail_feedback")
            )
            ->groupBy(
                'komplain_pesertas.nilaifeedback_id',
                'perusahaans.nama_perusahaan',
                'materis.nama_materi'
            )
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $komplains->map(function ($item) {
            return [
                'id' => $item->nilaifeedback_id,
                'komplain' => explode('||', $item->komplain),
                'kategori_feedback' => explode('||', $item->kategori),
                'detail_feedback' => $item->detail_feedback,
                'created_at' => $item->created_at,
                'tanggal_selesai' => explode('||', $item->tanggal_selesai),
                'status' => explode('||', $item->status),
            ];
        });

        return response()->json([
            'data' => $data
        ]);
    }


    public function create()
    {
        $tanggalSekarang = Carbon::today();
        $satuMingguLalu = Carbon::today()->subDays(7);

        $feedbacks = DB::table('nilaifeedbacks')
            ->leftJoin('r_k_m_s', 'nilaifeedbacks.id_rkm', '=', 'r_k_m_s.id')
            ->leftJoin('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
            ->leftJoin('perusahaans', 'r_k_m_s.perusahaan_key', '=', 'perusahaans.id')
            // ->whereDate('r_k_m_s.tanggal_akhir', '>=', $satuMingguLalu)
            // ->whereDate('r_k_m_s.tanggal_akhir', '<=', $tanggalSekarang)
            ->orderBy('perusahaans.nama_perusahaan', 'asc')
            ->select(
                'nilaifeedbacks.id as nilaifeedback_id',
                'materis.nama_materi',
                'perusahaans.nama_perusahaan',
                'r_k_m_s.tanggal_akhir'            
                )
            ->get();

        return view('komplain_peserta.create', compact('feedbacks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'feedback' => 'required',
            'kategori.*' => 'required',
            'komplain.*' => 'required',
        ]);

        foreach ($request->kategori as $index => $kategori) {

            $kategoriFinal = $kategori;

            if ($kategori === 'lainnya') {
                if (empty($request->kategori_lainnya[$index])) {
                    return back()
                        ->withErrors(['kategori_lainnya.' . $index => 'Kategori lainnya wajib diisi'])
                        ->withInput();
                }

                $kategoriFinal = $request->kategori_lainnya[$index];
            }

            KomplainPeserta::create([
                'nilaifeedback_id' => $request->feedback,
                'kategori_feedback' => $kategoriFinal,
                'komplain' => $request->komplain[$index],
            ]);
        }

        return redirect()
            ->route('komplain-peserta')
            ->with('success', 'Komplain Berhasil Dibuat');
    }

    public function show(string $id)
    {

    }


    public function edit($nilaifeedback_id)
    {
        $komplains = KomplainPeserta::where('nilaifeedback_id', $nilaifeedback_id)->get();

        $feedback = DB::table('nilaifeedbacks')
            ->join('r_k_m_s', 'nilaifeedbacks.id_rkm', '=', 'r_k_m_s.id')
            ->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
            ->join('perusahaans', 'r_k_m_s.perusahaan_key', '=', 'perusahaans.id')
            ->where('nilaifeedbacks.id', $nilaifeedback_id)
            ->select(
                'nilaifeedbacks.id as nilaifeedback_id',
                'materis.nama_materi',
                'perusahaans.nama_perusahaan'
            )
            ->first();

        return view('komplain_peserta.edit', compact('komplains', 'feedback'));
    }


    public function update(Request $request, $nilaifeedback_id)
    {
        $request->validate([
            'komplain.*' => 'required',
            'status.*' => 'required',
            'tanggal_selesai.*' => 'nullable|date'
        ]);

        foreach ($request->komplain as $id => $isiKomplain) {
            KomplainPeserta::where('id', $id)->update([
                'komplain' => $isiKomplain,
                'status' => $request->status[$id] ?? null,
                'tanggal_selesai' => $request->tanggal_selesai[$id] ?? null,
            ]);
        }

        return redirect()
            ->route('komplain-peserta')
            ->with('success', 'Komplain berhasil diperbarui');
    }



    public function destroy(string $id)
    {
        KomplainPeserta::where('nilaifeedback_id', $id)->delete();

        return redirect()->route('komplain-peserta')->with('success', 'Komplain Berhasil Dihapus!');
    }

    public function dataNilaiPenilaian($id) {
        $feedback = Nilaifeedback::findOrFail($id);

        return response()->json([
            'materi' => round(($feedback->M1 + $feedback->M2 + $feedback->M3 + $feedback->M4) / 4, 1),
            'pelayanan' => round(($feedback->P1 + $feedback->P2 + $feedback->P3 + $feedback->P4 + $feedback->P5 + $feedback->P6 + $feedback->P7) / 7, 1),
            'fasilitas' => round(($feedback->F1 + $feedback->F2 + $feedback->F3 + $feedback->F4 + $feedback->F5) / 5, 1),
            'instruktur' => round(($feedback->I1 + $feedback->I2 + $feedback->I3 + $feedback->I4 + $feedback->I5 + $feedback->I6 + $feedback->I7 + $feedback->I8) / 8, 1),
            'instruktur2' => round(($feedback->I1b + $feedback->I2b + $feedback->I3b + $feedback->I4b + $feedback->I5b + $feedback->I6b + $feedback->I7b + $feedback->I8b) / 8, 1),
            'asisten' => round(($feedback->I1as + $feedback->I2as + $feedback->I3as + $feedback->I4as + $feedback->I5as + $feedback->I6as + $feedback->I7as + $feedback->I8as) / 8, 1),
            'umum1' => $feedback->U1,
            'umum2' => $feedback->U2,
        ]);
    }
}
