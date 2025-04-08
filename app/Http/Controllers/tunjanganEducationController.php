<?php

namespace App\Http\Controllers;

use App\Models\rekapMengajarInstruktur;
use App\Models\tunjanganEducation;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Http\Controllers\rekapInstrukturController;
use App\Models\RKM;

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
        $data = rekapMengajarInstruktur::with('instruktur')
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->get()
            ->map(function ($item) {
                // Split the id_rkm string and get the first ID
                $idRkmArray = explode(',', $item->id_rkm);
                $firstIdRkm = $idRkmArray[0] ?? null; // Get the first ID or null if not available
                
                // Fetch the corresponding rkm data
                $rkmData = null;
                if ($firstIdRkm) {
                    $rkmData = Rkm::with('materi')->find($firstIdRkm); // Assuming Rkm is the model for the rkm table
                }

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
                    'rkm' => $rkmData, // Include the fetched rkm data
                    'instruktur' => $item->instruktur // Include the fetched rkm data
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
                if($feedback >= '3.3'){
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
            $data->update([
                'status' => 'Diajukan',
                'poin_durasi' => $poin_durasi,
                'poin_pax' => $poin_pax,
                'tunjangan_feedback' => $tunjangan_feedback,
                'total_tunjangan' => $request->total_tunjangan,
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
}
