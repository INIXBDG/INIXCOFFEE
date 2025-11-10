<?php

namespace App\Http\Controllers;

use App\Models\rekapMengajarInstruktur;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\rekapInstrukturController;
use App\Models\RKM;
use App\Exports\tunjanganEduExportExcel;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class tunjanganEducationController extends Controller
{
    protected $rekapInstrukturController;

    public function __construct(rekapInstrukturController $rekapInstrukturController)
    {
        $this->middleware('auth');
        $this->rekapInstrukturController = $rekapInstrukturController;
    }
    public function index()
    {
        $month = Carbon::now()->format('m');
        $year = Carbon::now()->format('Y');
        // return $year;
        return view('tunjanganeducation.index', compact('month', 'year'));
    }

    public function getListRekapInstruktur($bulan, $tahun)
{
    $caseMonth = "
        CASE
            WHEN MONTH(rekap_mengajar_instrukturs.tanggal_awal) <> MONTH(rekap_mengajar_instrukturs.tanggal_akhir)
                THEN MONTH(rekap_mengajar_instrukturs.tanggal_akhir)
            ELSE MONTH(rekap_mengajar_instrukturs.tanggal_awal)
        END
    ";

    $caseYear = "
        CASE
            WHEN MONTH(rekap_mengajar_instrukturs.tanggal_awal) <> MONTH(rekap_mengajar_instrukturs.tanggal_akhir)
                THEN YEAR(rekap_mengajar_instrukturs.tanggal_akhir)
            ELSE YEAR(rekap_mengajar_instrukturs.tanggal_awal)
        END
    ";

    // Pakai DB::raw hanya dalam select (atau selectRaw)
    $collection = rekapMengajarInstruktur::with('instruktur')
        ->select('rekap_mengajar_instrukturs.*', DB::raw("($caseMonth) as bulan_berlaku"), DB::raw("($caseYear) as tahun_berlaku"))
        // untuk filtering gunakan whereRaw dengan string CASE asli (bukan objek)
        ->whereRaw("($caseMonth) = ?", [$bulan])
        ->whereRaw("($caseYear) = ?", [$tahun])
        ->get();

    // Kumpulkan firstIdRkm untuk menghindari N+1
    $firstIds = $collection->map(function ($item) {
        $ids = array_filter(array_map('trim', explode(',', $item->id_rkm ?? '')));
        return $ids[0] ?? null;
    })->filter()->unique()->values()->all();

    // Ambil RKM sekaligus (eager with materi)
    $rkmMap = [];
    if (!empty($firstIds)) {
        $rkms = Rkm::with('materi')->whereIn('id', $firstIds)->get()->keyBy('id');
        $rkmMap = $rkms->all();
    }

    // Map hasil akhir
    $data = $collection->map(function ($item) use ($rkmMap) {
        $ids = array_filter(array_map('trim', explode(',', $item->id_rkm ?? '')));
        $firstId = $ids[0] ?? null;
        $rkmData = $firstId && isset($rkmMap[$firstId]) ? $rkmMap[$firstId] : null;

        return [
            'id' => $item->id,
            'id_rkm' => $item->id_rkm,
            'id_instruktur' => $item->id_instruktur,
            'feedback' => $item->feedback,
            'pax' => $item->pax,
            'level' => $item->level,
            'durasi' => $item->durasi,
            'tanggal_awal' => $item->tanggal_awal,
            'tanggal_akhir' => $item->tanggal_akhir,
            'bulan' => $item->bulan,
            'tahun' => $item->tahun,
            'poin_durasi' => $item->poin_durasi,
            'poin_pax' => $item->poin_pax,
            'tunjangan_feedback' => $item->tunjangan_feedback,
            'total_tunjangan' => $item->total_tunjangan,
            'status' => $item->status,
            'keterangan' => $item->keterangan,
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
            // kolom tambahan dari CASE
            'bulan_berlaku' => $item->bulan_berlaku,
            'tahun_berlaku' => $item->tahun_berlaku,
            'rkm' => $rkmData,
            'instruktur' => $item->instruktur,
        ];
    });

    return response()->json([
        'success' => true,
        'message' => 'List data',
        'data' => $data,
    ]);
}

    public function update($id, Request $request)
    {
        // $dataluar = $this->rekapInstrukturController->sinkronData();
        $data = rekapMengajarInstruktur::with('rkm', 'rkm.materi', 'instruktur')
                   ->findOrFail($id);
        // dd($data, $request->all());
        // return $data;    
        if ($request->approval == '99') {
            if ($data->level !== $request->level) {
                $level = $data->level;
                if($level === '1'){
                    $level_inst =  1;
                }else if($level === '2'){
                    $level_inst =  1.5;
                }else if($level === '3'){
                    $level_inst =  2;
                }
            } else {
                $level = $request->level;
            }
        
            if ($data->durasi !== $request->durasi) {
                $durasi = $data->durasi;
                if($data->metode_kelas == 'Inhouse Luar Bandung'){
                    $durasi_inst = $durasi * 5 * 1.3;
                }else{
                    $durasi_inst = $durasi * 5;
                }
                $poin_durasi = $durasi_inst * $level_inst;
            } else {
                $durasi = $request->durasi; 
                $poin_durasi = $request->poin_durasi;
            }
        
            if ($data->pax !== $request->pax) {
                $pax = $data->pax;
                $poin_pax = $pax * $level;
            } else {
                $pax = $request->pax; 
                $poin_pax = $request->poin_pax;
            }
            if ($data->feedback !== $request->feedback) {
                $feedback = $data->feedback;
                if($feedback >= '3.30'){
                    if($level === '1'){
                        $tunjangan_feedback = 80000;
                    }else if($level === '2'){
                        $tunjangan_feedback = 100000;
                    }else if($level === '3'){
                        $tunjangan_feedback = 125000;
                    }
                }else{
                    $tunjangan_feedback = 0;
                }
            } else {
                $feedback = $data->feedback;
                $tunjangan_feedback = $request->tunjangan_feedback;
            }

            $tunjangan_durasi = $poin_durasi * 15000;
            $tunjangan_pax = $poin_pax * 15000;
            $total_tunjangan = $tunjangan_durasi + $tunjangan_pax + $tunjangan_feedback;
            // dd($request->all());

            $data->update([
                'status' => 'Diajukan',
                'poin_durasi' => $poin_durasi,
                'poin_pax' => $poin_pax,
                'tunjangan_feedback' => $tunjangan_feedback,
                'total_tunjangan' => $total_tunjangan,
            ]); 
        }
        if ($request->approval == '1') {
            $data->update([
                'status' => 'Approve',
            ]); 
        }
        if ($request->approval == '2') {
            $data->update([
                'status' => 'Revisi',
            ]); 
        }
        return redirect()->route('tunjanganEducation.index')->with(['success' => 'Data Berhasil dihitung!']);

    }

    public function tunjanganEduExportExcel($month, $year)
    {
        $posts = rekapMengajarInstruktur::where('bulan', $month)
            ->where('tahun', $year)
            ->with('instruktur', 'rkm')
            ->get();

        // Mengubah data menjadi array sesuai kebutuhan export
        $exportData = $posts->map(function ($item) {
            return [
                'nama_karyawan' => $item->instruktur->nama_lengkap ?? '',
                'materi' => $item->rkm->materi->nama_materi ?? '',
                'perusahaan' => $item->rkm->perusahaan->nama_perusahaan ?? '',
                'feedback' => $item->rkm->feedback ?? '',
                'pax' => $item->pax ?? '',
                'level' => $item->level ?? '',
                'durasi' => $item->durasi ?? '',
                'tanggal_awal' => $item->tanggal_awal ?? '',
                'tanggal_akhir' => $item->tanggal_akhir ?? '',
                'bulan' => $item->bulan ?? '',
                'tahun' => $item->tahun ?? '',
                'poin_durasi' => $item->poin_durasi ?? '',
                'poin_pax' => $item->poin_pax ?? '',
                'tunjangan_feedback' => $item->tunjangan_feedback ?? '',
                'tunjangan_total' => $item->total_tunjangan ?? '',
                'status' => $item->status ?? '',
                'keterangan' => $item->keterangan ?? '',
            ];
        })->toArray();

        $fileName = "tunjangan_edu_{$month}_{$year}.xlsx";
        return Excel::download(new tunjanganEduExportExcel($exportData), $fileName);
    }

}
