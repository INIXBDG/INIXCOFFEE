<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityInstruktur;
use App\Models\dbklien;
use App\Models\Inventaris;
use App\Models\jabatan;
use App\Models\Materi;
use App\Models\Nilaifeedback;
use App\Models\Perusahaan;
use App\Models\Peserta;
use App\Models\Registrasi;
use App\Models\RekomendasiLanjutan;
use App\Models\RKM;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class apiController extends Controller
{
    public function getFeedbacks()
    {
        $feedbacks = Nilaifeedback::with('rkm')->whereYear('created_at', '2026')->get();

        // $groupedFeedbacks = $feedbacks->groupBy('id_rkm');
        $groupedFeedbacks = $feedbacks->groupBy(function ($feedback) {
            return
                $feedback->rkm->materi->nama_materi . '/' .
                $feedback->rkm->tanggal_awal . '/' .
                $feedback->rkm->instruktur_key;
        });

        // return $groupedFeedbacks;
        $averageFeedbacks = [];

        foreach ($groupedFeedbacks as $materi_key => $feedbackGroup) {

            $first = $feedbackGroup->first();

            $materi_key     = $first->rkm->materi_key;
            $nama_materi    = $first->rkm->materi->nama_materi;
            $instruktur_key = $first->rkm->instruktur_key;
            $sales_key      = $first->rkm->sales_key;
            $created_at     = $first->created_at;

            $tanggal_awal = Carbon::parse($first->rkm->tanggal_awal)->format('Y-m-d');
            $tanggal_akhir = $first->rkm->tanggal_akhir;

            $totalFeedbacks = $feedbackGroup->count();

            // =========================
            // FIELD GROUP DEFINISI
            // =========================
            $groups = [
                'M'   => ['M1','M2','M3','M4'],
                'P'   => ['P1','P2','P3','P4','P5','P6','P7','P8'], 
                'F'   => ['F1','F2','F3','F4','F5'],
                'I'   => ['I1','I2','I3','I4','I5','I6','I7','I8'],
                'Ib'  => ['I1b','I2b','I3b','I4b','I5b','I6b','I7b','I8b'],
                'Ias' => ['I1as','I2as','I3as','I4as','I5as','I6as','I7as','I8as'],
            ];

            $result = [];

            foreach ($groups as $groupName => $fields) {

                $totalGroupScore = 0;
                $activeFieldCount = 0;

                foreach ($fields as $field) {

                    // cek apakah kolom ada & ada nilainya
                    if ($feedbackGroup->whereNotNull($field)->count() > 0) {

                        $fieldTotal = $feedbackGroup->sum(function ($item) use ($field) {
                            return $item->$field ?? 0;
                        });

                        $result["average{$field}"] = $totalFeedbacks > 0
                            ? $fieldTotal / $totalFeedbacks
                            : 0;

                        $totalGroupScore += $fieldTotal;
                        $activeFieldCount++;
                    }
                }

                // Hitung rata-rata group (M, P, F, dst)
                $result["average{$groupName}"] =
                    ($totalFeedbacks > 0 && $activeFieldCount > 0)
                    ? round($totalGroupScore / ($totalFeedbacks * $activeFieldCount), 1)
                    : 0;
            }

            $averageFeedbacks[] = array_merge([
                'nama_materi'    => $nama_materi,
                'materi_key'     => $materi_key,
                'instruktur_key' => $instruktur_key,
                'sales_key'      => $sales_key,
                'tanggal_awal'   => $tanggal_awal,
                'tanggal_akhir'  => $tanggal_akhir,
                'created_at'     => $created_at,
            ], $result);
        }

        // Urutkan hasil berdasarkan tanggal_awal secara descending
        $sortedFeedbacks = collect($averageFeedbacks)->sortByDesc('created_at')->values()->all();

        // return response()->json(['feedbacks' => $sortedFeedbacks]);
        return response()->json([
            'success' => true,
            'message' => 'List Feedbacks',
            'data' => $sortedFeedbacks
            // 'data' => $groupedFeedbacks
        ]);

    }

    public function getMateri()
    {
        $materi = Materi::all();

        return response()->json([
            'success' => true,
            'message' => 'List Materi',
            'data' => $materi
        ]);
    }

    public function getMateriInix()
    {
        $materi = Materi::whereIn('tipe_materi', ['Normal', 'Webinar/Workshop'])->get();

        $groupMateri = $materi->groupBy(function ($item) {
            return $item->kategori_materi;
        })->map(function ($group) {
            return $group->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_materi' => $item->nama_materi,
                    'kategori_materi' => $item->kategori_materi,
                    'kode_materi' => $item->kode_materi ? $item->kode_materi : '-',
                    'vendor' => $item->vendor,
                    'durasi' => $item->durasi,
                    'tipe_materi' => $item->tipe_materi,
                    'status' => $item->status ? $item->status : 'Nonaktif',
                    'deskripsi' => 'test',
                    'harga' => '5000000',
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            });
        });
        return response()->json([
            'success' => true,
            'message' => 'List Materi',
            'data' => $groupMateri
        ]);
    }
    public function getMateriInixByID($id)
    {
        $materi = Materi::findOrFail($id);

        $materiData = [
            'id' => $materi->id,
            'nama_materi' => $materi->nama_materi,
            'kategori_materi' => $materi->kategori_materi,
            'kode_materi' => $materi->kode_materi ? $materi->kode_materi : '-',
            'vendor' => $materi->vendor,
            'durasi' => $materi->durasi,
            'status' => $materi->status ? $materi->status : 'Nonaktif',
            'deskripsi' => 'test',
            'harga' => '5000000',
            'created_at' => $materi->created_at,
            'updated_at' => $materi->updated_at,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Detail Materi',
            'data' => $materiData
        ]);
    }


    public function getMateris()
    {
        $perusahaans = Materi::where('nama_materi', 'LIKE', '%' . request('q') . '%')->where('status', 'Aktif')->paginate(20);
        return response()->json($perusahaans);
    }



    public function getJabatan()
    {
        $materi = jabatan::all();

        return response()->json([
            'success' => true,
            'message' => 'List Jabatan',
            'data' => $materi
        ]);
    }

    public function getPerusahaanall()
    {
        $perusahaan = Perusahaan::with('karyawan')->get();

        return response()->json([
            'success' => true,
            'message' => 'List perusahaan',
            'data' => $perusahaan
        ]);
    }


    public function getUserall()
    {
        // $registrasi = Registrasi::with('rkm', 'peserta.perusahaan', 'materi')->get();
        $user = User::with('karyawan')->where('status_akun', '1')->get();

        return response()->json([
            'success' => true,
            'message' => 'List perusahaan',
            'data' => $user,
        ]);
    }
    public function getRegistrasi(Request $request)
    {
        $id_rkm = $request->id_rkm;
        // $registrasi = Registrasi::with('rkm', 'peserta.perusahaan', 'materi')->get();
        $user = Registrasi::with('peserta')->where('id_rkm', $id_rkm)->get();

        return response()->json([
            'data' => $user
        ]);

    }
    public function UpcomingRKM(Request $request)
    {
        $today = Carbon::now();
        $startDate = $today->copy()->startOfMonth()->toDateString();
        $endDate = $today->copy()->addMonths(4)->endOfMonth()->toDateString();

        // Ambil data RKM beserta relasi materi
        $rows = RKM::with('materi')
            ->whereBetween('tanggal_awal', [$startDate, $endDate])
            ->get();

        // Kelompokkan berdasarkan nama materi dan tanggal_awal
        $grouped = $rows->groupBy(function ($item) {
            return $item->materi->nama_materi . '|' . $item->tanggal_awal;
        });

        // Format hasil akhir
        $result = $grouped->map(function ($items, $key) {
            [$nama_materi, $tanggal_awal] = explode('|', $key);
            $tanggal_akhir = $items->first()->tanggal_akhir; // Ambil tanggal_akhir dari item pertama

            return [
                'nama_materi' => $nama_materi,
                'tanggal_awal' => $tanggal_awal,
                'tanggal_akhir' => $tanggal_akhir,
                'jadwals' => $items, // Seluruh entri RKM dalam grup ini
            ];
        })->values();

        return response()->json([
            'success' => true,
            'message' => 'Upcoming RKM',
            'data' => $result,
        ]);

    }
    public function jadwalRKM(Request $request)
    {
        $today = Carbon::now();
        $startDate = $today->copy()->startOfMonth()->toDateString();
        $endDate = $today->copy()->addMonths(4)->endOfMonth()->toDateString();

        // Ambil data RKM beserta relasi materi
        $rows = RKM::with('materi')
            ->whereBetween('tanggal_awal', [$startDate, $endDate])
            ->get();

        // Kelompokkan berdasarkan nama materi dan tanggal_awal
        $grouped = $rows->groupBy(function ($item) {
            return $item->materi->nama_materi . '|' . $item->tanggal_awal;
        });

        // Format hasil akhir
        $result = $grouped->map(function ($items, $key) {
            [$nama_materi, $tanggal_awal] = explode('|', $key);
            $tanggal_akhir = $items->first()->tanggal_akhir;

            // Tambahkan bulan sebagai informasi tambahan untuk pengelompokan
            $bulan = Carbon::parse($tanggal_awal)->format('Y-m');

            return [
                'nama_materi' => $nama_materi,
                'tanggal_awal' => $tanggal_awal,
                'tanggal_akhir' => $tanggal_akhir,
                'bulan' => $bulan,
                'jadwals' => $items,
            ];
        });

        // Kelompokkan berdasarkan bulan
        $groupedByMonth = $result->groupBy('bulan')->sortKeys();


        return response()->json([
            'success' => true,
            'message' => 'Upcoming RKM',
            'data' => $groupedByMonth,
        ]);
    }


    public function getInventaris(Request $request)
    {
        $data = Inventaris::all();

        return response()->json([
            'success' => true,
            'message' => 'List Inventaris Inixindo',
            'data' => $data
        ]);
    }

    public function CSATinstruktur(Request $request)
    {
        $response = $this->getFeedbacks();

        // Ambil data dari JsonResponse
        $data = collect($response->getData(true)['data']);

        $total = $data->sum('averageI');
        $count = $data->whereNotNull('averageI')->count();

        $rataRata = $count > 0 ? round($total / $count, 2) : 0;

        return response()->json([
            'success' => true,
            'message' => 'CSAT Instruktur',
            'total' => $total,
            'rata_rata' => $rataRata
        ]);
    }

   public function AktivitasInstruktur(Request $request)
    {
        $tahun = Carbon::now()->year;
        $sharingKnowledge = ActivityInstruktur::where('activity_type', 'Sharing Knowledge')
            ->whereYear('created_at', $tahun)
            ->count();

        $pembuatanMateri = ActivityInstruktur::where('activity_type', 'Pembuatan Materi')
            ->whereYear('created_at', $tahun)
            ->count();

        $pembuatanSilabus = ActivityInstruktur::where('activity_type', 'Pembuatan Silabus')
            ->whereYear('created_at', $tahun)
            ->count();

        return response()->json([
            'success' => true,
            'sharingKnowledge' => $sharingKnowledge,
            'pembuatanMateri' => $pembuatanMateri,
            'pembuatanSilabus' => $pembuatanSilabus,
        ]);
    }

    public function RekomendasiMateri(Request $request)
    {
        $tahun = Carbon::now()->year;

        // total rekomendasi berdasarkan RKM tahun ini
        $total = RekomendasiLanjutan::whereHas('rkm', function ($q) use ($tahun) {
            $q->whereYear('tanggal_awal', $tahun);
        })->count();

        // filled = rekomendasi yang keterangannya terisi (bukan null / kosong)
        $filled = RekomendasiLanjutan::whereHas('rkm', function ($q) use ($tahun) {
            $q->whereYear('tanggal_awal', $tahun);
        })
        // ->whereNotNull('keterangan')
        // ->where('keterangan', '!=', '')
        ->count();

        $persen = $total > 0 ? round(($filled / $total) * 100) : 0;

        return response()->json([
            'success' => true,
            'persen' => $persen,
            'total' => $total,
            'filled' => $filled
        ]);
    }

    public function getDBKlien()
{
    $data1 = DB::table('dbkliens')
        ->leftJoin(
            'perusahaans',
            'dbkliens.nama_perusahaan',
            '=',
            'perusahaans.id'
        )
        ->select(
            'dbkliens.nama',
            'dbkliens.jenis_kelamin',
            'dbkliens.email',
            'dbkliens.no_hp',
            'dbkliens.tanggal_lahir',
            'dbkliens.nama_perusahaan',
            'perusahaans.lokasi',
            'dbkliens.sales_key',
            'dbkliens.nama_materi',
            'dbkliens.created_at'
        );

    // ================= DATA 2
    $data2 = DB::table('registrasis')
        ->join('pesertas','registrasis.id_peserta','=','pesertas.id')
        ->join('materis','registrasis.id_materi','=','materis.id')
        ->leftJoin('perusahaans','pesertas.perusahaan_key','=','perusahaans.id')
        ->select(
            'pesertas.nama',
            'pesertas.jenis_kelamin',
            'pesertas.email',
            'pesertas.no_hp',
            'pesertas.tanggal_lahir',
            'perusahaans.nama_perusahaan',
            'perusahaans.lokasi',
            'perusahaans.sales_key',
            'materis.nama_materi',
            'registrasis.created_at'
        );

    // ================= UNION
    $union = $data1->unionAll($data2);

    // ================= GROUP PESERTA
    $data = DB::query()
        ->fromSub($union, 'x')
        ->get()
        ->groupBy(function($item){
            return
                strtolower($item->nama).'|'.
                strtolower($item->email).'|'.
                strtolower($item->jenis_kelamin);
        })
        ->map(function($rows){

            $first = $rows->first();

            // FORMAT NAMA
            $first->nama_formatted =
                \App\Models\Peserta::formatNama(
                    $first->nama
                );

            // USIA
            $first->usia =
                $first->tanggal_lahir
                ? Carbon::parse(
                    $first->tanggal_lahir
                  )->age
                : '';

            // LIST MATERI
            $first->materi_list =
                $rows->pluck('nama_materi')
                    ->map(function($m){
                        return $m ?? '-'; // ganti null jadi "-"
                    })
                    ->unique()
                    ->values();


            return $first;
        })
        ->values();

    return response()->json([
        'data' => $data
    ]);
}












}
