<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Nilaifeedback;
use App\Models\RKM;
use App\Models\souvenir;
use App\Models\souvenirpeserta;
use App\Models\souvenirinhouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use App\Models\Registrasi;


class FeedbackAPIController extends Controller
{
    public function index()
    {
        $sales = Feedback::where('kategori_feedback', 'Sales')->get();
        $materi = Feedback::where('kategori_feedback', 'Materi')->get();
        $pelayanan = Feedback::where('kategori_feedback', 'Pelayanan')->get();
        $fasilitas = Feedback::where('kategori_feedback', 'Fasilitas Laboratium')->get();
        $instruktur = Feedback::where('kategori_feedback', 'Instruktur')->get();
        $jmlInstruktur = RKM::with('instruktur');
        $umum = Feedback::where('kategori_feedback', 'Umum')->get();
        $souvenir = souvenir::get();

        return response()->json([
            'sales' => $sales,
            'materi' => $materi,
            'pelayanan' => $pelayanan,
            'fasilitas' => $fasilitas,
            'instruktur' => $instruktur,
            'umum' => $umum,
            'souvenir' => $souvenir,
        ]);
    }

    public function cekFeedback(Request $request)
    {
        $id_regist = $request->input('id_regist');
        $id_rkm = $request->input('id_rkm');
        $post = Nilaifeedback::where('id_regist', $id_regist)->first();
        if ($post) {
            return response()->json([
                'status' => 'error',
                'title' => 'Mohon Maaf',
                'text' => 'Anda sudah mengisi feedback ini!',
            ], 500);
        } else {
            return response()->json([
                'status' => 'success',
                'title' => 'Selamat!',
                'text' => 'Lanjut Feedback!',
            ], 200);
        }
    }
	public function cekFeedbackRKM(Request $request)
	{
		// 1 bulan sebelumnya dari hari ini
		$startDate = Carbon::now()->subMonth()->startOfMonth()->toDateString();

		// 1 bulan ke depan dari hari ini
		$endDate = Carbon::now()->addMonth()->endOfMonth()->toDateString();

		$id = $request->input('id');

		$peserta = Registrasi::with('peserta', 'rkm', 'materi')
			->where('id_peserta', $id)
			->whereHas('rkm', function ($query) use ($startDate, $endDate) {
				$query->whereBetween('tanggal_awal', [$startDate, $endDate]);
				$query->whereNull('deleted_at');
			})
			->get();

		return response()->json([
			'rkm' => $peserta,
			'start_date' => $startDate,
			'end_date' => $endDate
		]);
	}

    public function store(Request $request)
    {
        $data = $request->all();

        // =========================
        // HANDLE I*b
        // =========================
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

        // =========================
        // HANDLE I*as
        // =========================
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

        // =========================
        // HANDLE KELAS
        // =========================
        $isVirtual = strtolower($data['kelas']) === 'Virtual';

        foreach ($data as $key => $value) {

            if (is_null($value)) {

                // U1 & U2 selalu "-"
                if (in_array($key, ['U1', 'U2'])) {
                    $data[$key] = '-';
                    continue;
                }

                if (!$isVirtual) {
                    // OFFLINE

                    // field yang boleh tetap null
                    $allowedNull = [
                        'I1b','I2b','I3b','I4b','I5b','I6b','I7b','I8b',
                        'I1as','I2as','I3as','I4as','I5as','I6as','I7as','I8as',
                        'P7','P3','P8','F4','M4', // M4 Ditambahkan
                    ];

                    if (!in_array($key, $allowedNull)) {
                        $data[$key] = '4';
                    }
                }

                // kalau virtual → biarkan null
            }
        }

        // =========================
        // VALIDASI DUPLIKAT
        // =========================
        $nilaiFeedback = Nilaifeedback::where('id_regist', $data['id_regist'])
            ->where('id_rkm', $data['id_rkm'])
            ->first();

        if ($nilaiFeedback) {
            return response()->json([
                'status' => 'error',
                'title' => 'Mohon Maaf',
                'text' => 'Anda sudah mengisi feedback!',
            ], 500);
        }

        Nilaifeedback::create($data);

        return response()->json([
            'status' => 'success',
            'title' => 'Selamat!',
            'text' => 'Terimakasih sudah mengisi feedback!',
        ], 200);
    }

    public function storeSouvenir(Request $request)
    {
        $id_regist = $request->input('id_regist');
        $id_rkm = $request->input('id_rkm');
        $souveniran = $request->input('souvenir');

        if (!$souveniran) {
            return response()->json([
                'status' => 'error',
                'title' => 'Mohon Maaf!',
                'text' => 'Anda belum mengisi souvenir, silahkan coba kembali!',
            ], 200);
        }

        $result = DB::transaction(function () use ($souveniran, $id_rkm, $id_regist) {
            $souvenir = souvenir::where('nama_souvenir', $souveniran)
                ->lockForUpdate()
                ->first();

            if (!$souvenir || $souvenir->stok < 1) {
                return false;
            }

            $souvenir->decrement('stok');

            souvenirpeserta::create([
                'id_souvenir' => $souvenir->id,
                'id_rkm' => $id_rkm,
                'id_regist' => $id_regist,
            ]);

            return true;
        });

        if (!$result) {
            return response()->json([
                'status' => 'error',
                'title' => 'Mohon Maaf!',
                'text' => 'Stok souvenir habis, silahkan pilih souvenir lain!',
            ], 200);
        }

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
            $rkm = RKM::findOrFail($id);
            $harga_jual = intval($rkm->harga_jual);

            if($rkm->souvenir == false){
                return response()->json([
                    'status' => 'skipped',
                    'title' => 'Selamat!',
                    'text' => 'Terimakasih sudah mengisi Feedback ini, Silahkan ke halaman utama!',
                ], 500);
            }


            if($rkm){
                $souvenirs = souvenir::where('min_harga_pelatihan', '<=', $harga_jual)
                    ->where('max_harga_pelatihan', '>=', $harga_jual)
                    ->where('stok', '>', 0)
                    ->orderBy('min_harga_pelatihan', 'asc')
                    ->get();

                if ($souvenirs->isEmpty()) {
                    $souvenirs = souvenir::whereBetween('min_harga_pelatihan', [6000000, 15000000])
                        ->where('stok', '>', 0)
                        ->orderBy('min_harga_pelatihan', 'asc')
                        ->get();
                }
            }

            // =======================================================
            // CASE: Tidak Sesuai
            // =======================================================
            else {
                return response()->json([
                    'status' => 'error',
                    'title' => 'Mohon Maaf',
                    'text' => 'Data tidak sesuai. Silahkan Hubungi Administrator!',
                ], 500);
            }

            // =======================================================
            // Grouping souvenir berdasarkan nama + size
            // =======================================================
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
                    $groupedSouvenirs[$baseName]['sizes'][] = [
                        'size' => $size,
                        'stok' => (int) $souvenir->stok
                    ];
                }
            }

            return response()->json(array_values($groupedSouvenirs));
        }
    }
}
