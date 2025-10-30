<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\Nilaifeedback;
use App\Models\RKM;
use App\Models\souvenir;
use App\Models\souvenirpeserta;
use App\Models\souvenirinhouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use App\Models\Registrasi;
class feedbackController extends Controller
{
    public function index()
    {
        $materi = Feedback::where('kategori_feedback', 'Materi')->get();
        $pelayanan = Feedback::where('kategori_feedback', 'Pelayanan')->get();
        $fasilitas = Feedback::where('kategori_feedback', 'Fasilitas Laboratium')->get();
        $instruktur = Feedback::where('kategori_feedback', 'Instruktur')->get();
        $jmlInstruktur = RKM::with('instruktur');
        $umum = Feedback::where('kategori_feedback', 'Umum')->get();
        $souvenir = souvenir::get();
        return view('feedback.index', compact('materi', 'pelayanan','fasilitas','instruktur','umum', 'souvenir'));
    }

    public function cekFeedback(Request $request)
    {
        $id_regist = $request->input('id_regist');
        $id_rkm = $request->input('id_rkm');
        $post = Nilaifeedback::where('id_regist',$id_regist)->first();
        if($post){
            return response()->json([
                'status' => 'error',
                'title' => 'Mohon Maaf',
                'text' => 'Anda sudah mengisi feedback ini!',
            ], 500);
        }else{
            return response()->json([
                'status' => 'success',
                'title' => 'Selamat!',
                'text' => 'Lanjut Feedback!',
            ], 200);
        }

    }

    public function cekFeedbackRKM(Request $request)
    {
        $startDate = Carbon::now()->startOfWeek()->toDateString();
        $endDate = Carbon::now()->endOfWeek()->toDateString(); // Tambahkan 1 minggu ke endDate
        $id = $request->input('id');
        $peserta = Registrasi::with('peserta', 'rkm', 'materi')
            ->where('id_peserta', $id)
            ->whereHas('rkm', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal_awal', [$startDate, $endDate]);
            })
            ->get();
        return response()->json(['rkm' => $peserta]);
    }


    public function store(Request $request)
    {
        $data = $request->all();

        if (is_null($data['I1b'])) {
            foreach (['I1b', 'I2b', 'I3b', 'I4b', 'I5b', 'I6b', 'I7b', 'I8b'] as $key) {
                $data[$key] = null;
            }
        } else {
            foreach (['I2b', 'I3b', 'I4b', 'I5b', 'I6b', 'I7b', 'I8b'] as $key) {
                if (is_null($data[$key])) {
                    $data[$key] = '4';
                }
            }
        }

        if (is_null($data['I1as'])) {
            foreach (['I1as', 'I2as', 'I3as', 'I4as', 'I5as', 'I6as', 'I7as', 'I8as'] as $key) {
                $data[$key] = null;
            }
        } else {
            foreach (['I2as', 'I3as', 'I4as', 'I5as', 'I6as', 'I7as', 'I8as'] as $key) {
                if (is_null($data[$key])) {
                    $data[$key] = '4';
                }
            }
        }

        // Check and assign default values for other fields
        foreach ($data as $key => $value) {
            if (is_null($value)) {
                if (in_array($key, ['U1', 'U2'])) {
                    $data[$key] = '-';
                } else if (!in_array($key, [
                    'I1b', 'I2b', 'I3b', 'I4b', 'I5b', 'I6b', 'I7b', 'I8b',
                    'I1as', 'I2as', 'I3as', 'I4as', 'I5as', 'I6as', 'I7as', 'I8as',
                ])) {
                    $data[$key] = '4';
                }
            }
        }
        // return $data;

        // Check if feedback already exists
        $nilaiFeedback = Nilaifeedback::where('id_regist', $data['id_regist'])
            ->where('id_rkm', $data['id_rkm'])
            ->first();

        if($nilaiFeedback){
            return response()->json([
                'status' => 'error',
                'title' => 'Mohon Maaf',
                'text' => 'Anda sudah mengisi feedback!',
            ], 500);
        }
            // Handle full feedback creation with all inputs
            Nilaifeedback::create($data);

        return response()->json([
            'status' => 'success',
            'title' => 'Selamat!',
            'text' => 'Terimakasih sudah mengisi feedback!',
        ], 200);
    }


    public function storeSouvenir(Request $request)
    {
        // return $request->all();

        $id_regist = $request->input('id_regist');
        $id_rkm = $request->input('id_rkm');
        $souveniran = $request->input('souvenir');
        if(!$souveniran){
            return response()->json([
                'status' => 'error',
                'title' => 'Mohon Maaf!',
                'text' => 'Anda belum mengisi souvenir, silahkan coba kembali!',
            ], 200);
        }
        $souvenir = Souvenir::where('nama_souvenir', $souveniran)->first();
        // dd($souvenir);
        $souvenir->decrement('stok');

        souvenirpeserta::create([
            'id_souvenir' => $souvenir->id,
            'id_rkm' => $id_rkm,
            'id_regist' => $id_regist
        ]);
        return response()->json([
                    'status' => 'success',
                    'title' => 'Selamat!',
                    'text' => 'Terimakasih sudah mengisi Feedback ini, Silahkan ke halaman utama!',
                ], 200);
    }
    public function cekSouvenir($id, $ids)
    {
        $sopenir = souvenirpeserta::where('id_rkm', $id)->where('id_regist', $ids)->first();

        if ($sopenir) {
            return response()->json([
                'status' => 'error',
                'title' => 'Mohon Maaf',
                'text' => 'Anda sudah mengisi feedback/souvenir ini, silahkan ke halaman utama!',
            ], 500);
        } else {
            // Mendapatkan nilai harga_jual dari model RKM
            $rkm = RKM::findOrFail($id);
            $harga_jual = intval($rkm->harga_jual);
            // return $rkm->metode_kelas;

            if ($rkm->metode_kelas == "Inhouse Bandung" || $rkm->metode_kelas == "Inhouse Luar Bandung") {
                // dd('INHOUSE KESINI');
                $s = souvenirinhouse::where('id_rkm', $rkm->id)->first();
                if (!$s) {
                    // return response()->json([
                    //     'status' => 'error',
                    //     'title' => 'Mohon Maaf!',
                    //     'text' => 'Souvenir belum ditentukan, silahkan hubungi Customer Care!',
                    // ], 404);
                    return response()->json([
                        'status' => 'skipped',
                        'title' => 'Selamat!',
                        'text' => 'Terimakasih sudah mengisi Feedback ini, Silahkan ke halaman utama!',
                    ], 200);
                } else {
                    $sid = $s->nama_souvenir;
                    if ($sid === 'All Item') {
                        $souvenirs = Souvenir::where('stok', '>', 0)
                            ->orderByDesc('stok')
                            ->get();
                    } else {
                        $souvenirs = Souvenir::where('nama_souvenir', 'LIKE', "%{$sid}%")
                            ->where('stok', '>', 0)
                            ->get();
                    }
                        if ($souvenirs->isEmpty()) {
                            return response()->json([
                                'status' => 'error',
                                'title' => 'Mohon Maaf',
                                'text' => 'Data tidak sesuai atau tidak ada. Silahkan Hubungi Administrator!',
                            ], 500);
                        }
                }
            } elseif ($rkm->metode_kelas == "Virtual") {
                // dd('ONLOINE KESINI');
                $s = souvenirinhouse::where('id_rkm', $rkm->id)->first();
                if (!$s) {
                    $souvenirs = Souvenir::where('min_harga_pelatihan', '<=', $harga_jual)
                        ->where('max_harga_pelatihan', '>=', $harga_jual)
                        ->where('stok', '>', 0) // Filter to exclude souvenirs with stock of 0
                        ->orderBy('min_harga_pelatihan', 'asc')
                        ->get();
                
                        if ($souvenirs->isEmpty()) {
                            return response()->json([
                                'status' => 'error',
                                'title' => 'Mohon Maaf',
                                'text' => 'Data tidak sesuai atau tidak ada. Silahkan Hubungi Administrator!',
                            ], 500);
                        }
                } else {
                    $sid = $s->nama_souvenir;
                    $souvenirs = Souvenir::where('nama_souvenir', 'LIKE', '%' . $sid . '%')
                        ->where('stok', '>', 0) // Filter to exclude souvenirs with stock of 0
                        ->get();
                        if ($souvenirs->isEmpty()) {
                            return response()->json([
                                'status' => 'error',
                                'title' => 'Mohon Maaf',
                                'text' => 'Data tidak sesuai atau tidak ada. Silahkan Hubungi Administrator!',
                            ], 500);
                        }
                }
            } elseif ($rkm->metode_kelas == "Offline") {
                $souvenirs = Souvenir::where('min_harga_pelatihan', '<=', $harga_jual)
                ->where('max_harga_pelatihan', '>=', $harga_jual)
                ->where('stok', '>', 0)
                ->orderBy('min_harga_pelatihan', 'asc')
                ->get();
                if ($souvenirs->isEmpty()) {
                    $souvenirs = Souvenir::where('min_harga_pelatihan', '<=', '6000000')
                                        ->where('max_harga_pelatihan', '>=', '15000000')
                                        ->where('stok', '>', 0)
                                        ->orderBy('min_harga_pelatihan', 'asc')
                                        ->get();
                }
                // dd($souvenirs);
            }
             else {
                return response()->json([
                    'status' => 'error',
                    'title' => 'Mohon Maaf',
                    'text' => 'Data tidak sesuai. Silahkan Hubungi Administrator!',
                ], 500);
            }
            
            $groupedSouvenirs = [];

            foreach ($souvenirs as $souvenir) {
                $namaBase = explode(' - ', $souvenir->nama_souvenir);
                $baseName = $namaBase[0];
                $size = isset($namaBase[1]) ? trim($namaBase[1]) : null;
                if (!isset($groupedSouvenirs[$baseName])) {
                    $groupedSouvenirs[$baseName] = [
                        'nama_souvenir' => $baseName,
                        'harga' => $souvenir->harga,
                        'min_harga_pelatihan' => $souvenir->min_harga_pelatihan,
                        'max_harga_pelatihan' => $souvenir->max_harga_pelatihan,
                        'stok' => $souvenir->stok,
                        'foto' => base64_encode($souvenir->blob_foto),
                        'sizes' => []
                    ];
                }
        
                if ($size) {
                    // Tambahkan size ke array sizes
                    // Bisa juga tambahkan stok per size jika perlu
                    array_push($groupedSouvenirs[$baseName]['sizes'], [
                        'size' =>  $size,
                        'stok' =>  (int)$souvenir->stok
                    ]);
                }
            }
        
            return response()->json(array_values($groupedSouvenirs));

        }
    }
}
