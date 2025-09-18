<?php

namespace App\Http\Controllers;

use App\Models\manajemenRuangan;
use App\Models\RKM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Contracts\Service\Attribute\Required;

class managementKelasController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:View Management Kelas', ['only' => ['index']]);
    }
    public function index()
    {
        return view('managementKelas.index');
    }

    public function get(Request $request)
    {
        $ruang = $request->input('ruang');
        $filter_utama = $request->input('filter_utama');
        $tanggal_ruang = $request->input('tanggal_ruang');

        $queryRkm = RKM::with(['sales', 'materi', 'instruktur', 'instruktur2', 'asisten', 'perusahaan'])
            ->where('metode_kelas', 'Offline');

        if ($ruang && $tanggal_ruang) {
            $tanggalRuang = collect((array) $tanggal_ruang)
                ->map(fn($tgl) => \Carbon\Carbon::parse($tgl)->format('Y-m-d'))
                ->toArray();

            $queryRkm->where('ruang', $ruang)
                ->whereIn(DB::raw('DATE(tanggal_awal)'), $tanggalRuang);
        } elseif ($filter_utama) {
            $filterUtama = collect((array) $filter_utama)
                ->map(fn($tgl) => \Carbon\Carbon::parse($tgl)->format('Y-m-d'))
                ->toArray();

            $queryRkm->whereIn(DB::raw('DATE(tanggal_awal)'), $filterUtama);
        }

        $kelasRkm = $queryRkm->get();

        $groupedRkm = $kelasRkm->groupBy(function ($item) {
            $awal = substr($item->tanggal_awal, 0, 10);
            return $item->ruang . '_' . $awal;
        });

        $dataRKM = $groupedRkm->map(function ($items) {
            $first = $items->first();
            $awal  = \Carbon\Carbon::parse($first->tanggal_awal)->format('Y-m-d');
            $akhir = \Carbon\Carbon::parse($first->tanggal_akhir)->format('Y-m-d');

            return [
                "key"          => $first->ruang . '_' . $awal,
                "ruang"        => "Ruang " . $first->ruang,
                "ruang_id"     => $first->ruang,
                "tanggal_awal" => $awal,
                "tanggal_akhir"=> $akhir,
                "materi"       => $items->pluck("materi.nama_materi")->filter()->implode(", "),
                "sales"        => $items->pluck("sales.nama_lengkap")->filter()->implode(", "),
                "instruktur"   => $items->pluck("instruktur.nama_lengkap")->filter()->implode(", "),
                "instruktur2"  => $items->pluck("instruktur2.nama_lengkap")->filter()->implode(", "),
                "asisten"      => $items->pluck("asisten.nama_lengkap")->filter()->implode(", "),
                "perusahaan"   => $items->pluck("perusahaan.nama_perusahaan")->filter()->implode(", "),
                "harga_jual"   => $first->harga_jual,
                "pax"          => $first->pax,
                "exam"         => $first->exam ? "Ya" : "Tidak",
                "authorize"    => $first->authorize ? "ya" : "Tidak",
            ];
        })->keyBy('key')->toArray();

        $queryMR = DB::table('manajemen_ruangans');

        if ($ruang && $tanggal_ruang) {
            $tanggalRuang = collect((array) $tanggal_ruang)
                ->map(fn($tgl) => \Carbon\Carbon::parse($tgl)->format('Y-m-d'))
                ->toArray();
            $queryMR->where('ruangan', $ruang)
                ->whereIn(DB::raw('DATE(tanggal)'), $tanggalRuang);
        } elseif ($filter_utama) {
            $filterUtama = collect((array) $filter_utama)
                ->map(fn($tgl) => \Carbon\Carbon::parse($tgl)->format('Y-m-d'))
                ->toArray();
            $queryMR->whereIn(DB::raw('DATE(tanggal)'), $filterUtama);
        }

        $kelasMR = $queryMR->get();
        $dataMR = $kelasMR->map(function ($item) {
            return [
                "key"         => $item->ruangan . '_' . \Carbon\Carbon::parse($item->tanggal)->format("Y-m-d"),
                "ruangan"     => "Ruang " . $item->ruangan,
                "ruang_id"    => $item->ruangan,
                "tanggal"     => \Carbon\Carbon::parse($item->tanggal)->format("Y-m-d"),
                "jam_mulai"   => $item->jam_mulai,
                "jam_selesai" => $item->jam_selesai,
                "kebutuhan"   => $item->kebutuhan,
                "keterangan"  => $item->keterangan ?? "-",
            ];
        })->keyBy('key')->toArray();

        $allKeys = collect(array_merge(array_keys($dataRKM), array_keys($dataMR)))->unique();

        $final = $allKeys->map(function ($key) use ($dataRKM, $dataMR) {
            $rkm = $dataRKM[$key] ?? null;
            $mr  = $dataMR[$key] ?? null;

            return [
                "ruang"        => $rkm['ruang'] ?? "-",
                "ruangan"      => $mr['ruangan'] ?? "-",
                "tanggal"      => $mr['tanggal'] ?? "-",
                "tanggal_awal" => $rkm['tanggal_awal'] ?? "-",
                "tanggal_akhir"=> $rkm['tanggal_akhir'] ?? "-",
                "materi"       => $rkm['materi'] ?? "-",
                "sales"        => $rkm['sales'] ?? "-",
                "instruktur"   => $rkm['instruktur'] ?? "-",
                "instruktur2"  => $rkm['instruktur2'] ?? "-",
                "asisten"      => $rkm['asisten'] ?? "-",
                "jam_mulai"    => $mr['jam_mulai'] ?? "-",
                "jam_selesai"  => $mr['jam_selesai'] ?? "-",
                "kebutuhan"    => $mr['kebutuhan'] ?? "-",
                "keterangan"   => $mr['keterangan'] ?? "-",
                "perusahaan"   => $rkm['perusahaan'] ?? "-",
                "pax"          => $rkm['pax'] ?? "-",
                "exam"         => $rkm['exam'] ?? "-",
                "authorize"    => $rkm['authorize'] ?? "-"
            ];
        })->values();

        return response()->json($final);
    }

    public function create($id) {}

    public function store(Request $request)
    {
        $request->validate([
            'ruang'             => 'required',
            'tanggal'           => 'required|date',
            'jam_mulai'         => 'required',
            'jam_selesai'       => 'required'
        ]);

        $managementRuang = new manajemenRuangan();
        $managementRuang->ruangan      = $request->input('ruang');
        $managementRuang->tanggal      = $request->input('tanggal');
        $managementRuang->jam_mulai    = $request->input('jam_mulai');
        $managementRuang->jam_selesai    = $request->input('jam_selesai');
        $managementRuang->kebutuhan    = $request->input('kebutuhan');
        $managementRuang->keterangan    = $request->input('keterangan');
        $managementRuang->save();

        return redirect()->back();
    }
    public function update(Request $request, $id) {}
}
