<?php

namespace App\Http\Controllers;

use App\Models\manajemenRuangan;
use App\Models\RKM;
use App\Models\eksam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Contracts\Service\Attribute\Required;

class managementKelasController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // $this->middleware('permission:View Management Kelas', ['only' => ['index']]);
    }
    
    public function index(Request $request)
    {
        // Check if this is for exam assignment
        $assignMode = $request->get('assign_mode');
        $examId = $request->get('exam_id');
        
        $examData = null;
        if ($assignMode === 'exam' && $examId) {
            $examData = session('exam_assign_data');
        }
        
        return view('managementKelas.index', compact('assignMode', 'examId', 'examData'));
    }

public function get(Request $request)
{
    $ruang = $request->input('ruang');
    $filter_utama = $request->input('filter_utama');
    $tanggal_ruang = $request->input('tanggal_ruang');

    // Query RKM - PERBAIKAN: Filter ruangan yang benar
    $queryRkm = RKM::with(['sales', 'materi', 'instruktur', 'instruktur2', 'asisten', 'perusahaan'])
        ->where('metode_kelas', 'Offline')
        ->whereNotNull('ruang')
        ->where('ruang', '!=', '');

    if ($ruang && $tanggal_ruang) {
        $tanggalRuang = collect((array) $tanggal_ruang)
            ->map(fn($tgl) => \Carbon\Carbon::parse($tgl)->format('Y-m-d'))
            ->toArray();

        // PERBAIKAN: Gunakan ruang langsung, bukan filter LIKE
        $queryRkm->where('ruang', $ruang)
            ->where(function($q) use ($tanggalRuang) {
                foreach($tanggalRuang as $tanggal) {
                    $q->orWhere(function($subQ) use ($tanggal) {
                        $subQ->whereDate('tanggal_awal', '<=', $tanggal)
                             ->whereDate('tanggal_akhir', '>=', $tanggal);
                    });
                }
            });
    } elseif ($filter_utama) {
        $filterUtama = \Carbon\Carbon::parse($filter_utama)->format('Y-m-d');
        
        // PERBAIKAN: Filter berdasarkan range tanggal yang benar
        $queryRkm->where(function($q) use ($filterUtama) {
            $q->whereDate('tanggal_awal', '<=', $filterUtama)
              ->whereDate('tanggal_akhir', '>=', $filterUtama);
        });
    }

    $kelasRkm = $queryRkm->get();

    // PERBAIKAN: Grouping berdasarkan ruang dan tanggal yang konsisten
    $groupedRkm = $kelasRkm->groupBy(function ($item) {
        return $item->ruang; // Grouping hanya berdasarkan ruang
    });

    $dataRKM = $groupedRkm->map(function ($items) {
        $first = $items->first();
        $awal  = \Carbon\Carbon::parse($first->tanggal_awal)->format('Y-m-d');
        $akhir = \Carbon\Carbon::parse($first->tanggal_akhir)->format('Y-m-d');

        return [
            "key"          => $first->ruang, // PERBAIKAN: Key hanya berdasarkan ruang
            "ruang"        => $first->ruang, // PERBAIKAN: Simpan ruang tanpa "Ruang" prefix
            "ruangan"      => "-", // PERBAIKAN: Set ke "-" untuk identifikasi RKM
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

    // Query Management Ruangan - PERBAIKAN: Filter yang konsisten
    $queryMR = DB::table('manajemen_ruangans');

    if ($ruang && $tanggal_ruang) {
        $tanggalRuang = collect((array) $tanggal_ruang)
            ->map(fn($tgl) => \Carbon\Carbon::parse($tgl)->format('Y-m-d'))
            ->toArray();
            
        // PERBAIKAN: Filter berdasarkan ruangan yang benar
        $queryMR->where('ruangan', $ruang)
            ->whereIn(DB::raw('DATE(tanggal)'), $tanggalRuang);
    } elseif ($filter_utama) {
        $filterUtama = \Carbon\Carbon::parse($filter_utama)->format('Y-m-d');
        $queryMR->whereDate('tanggal', $filterUtama);
    }

    $kelasMR = $queryMR->get();
    
    // PERBAIKAN: Key berdasarkan ruangan saja
    $dataMR = $kelasMR->groupBy('ruangan')->map(function ($items) {
        $grouped = $items->groupBy(function($item) {
            return \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d');
        });
        
        // Ambil data untuk tanggal yang paling relevan atau semua tanggal
        $first = $items->first();
        return [
            "key"         => $first->ruangan,
            "ruang"       => "-", // PERBAIKAN: Set ke "-" untuk identifikasi Management
            "ruangan"     => $first->ruangan,
            "tanggal"     => \Carbon\Carbon::parse($first->tanggal)->format("Y-m-d"),
            "jam_mulai"   => $first->jam_mulai,
            "jam_selesai" => $first->jam_selesai,
            "kebutuhan"   => $first->kebutuhan,
            "keterangan"  => $first->keterangan ?? "-",
            "id"          => $first->id, // BARU: Kirim ID untuk tombol batalkan
        ];
    })->toArray();

    // PERBAIKAN: Combine data berdasarkan ruangan
    $allRooms = ['1', '2', '3', '4', '5', '6', 'ADOC'];
    
    $final = collect($allRooms)->map(function ($room) use ($dataRKM, $dataMR) {
        $rkm = $dataRKM[$room] ?? null;
        $mr  = $dataMR[$room] ?? null;

        // Jika ada data RKM atau MR, return data tersebut
        if ($rkm) {
            return $rkm;
        } elseif ($mr) {
            return $mr;
        }
        
        // Jika tidak ada data, return null (akan difilter)
        return null;
    })->filter()->values(); // Remove null values

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

        // Check if this is for exam assignment
        $examId = session('exam_assign_id');
        
        try {
            DB::transaction(function () use ($request, $examId) {
                // Create management ruangan entry
                $managementRuang = new manajemenRuangan();
                $managementRuang->ruangan      = $request->input('ruang');
                $managementRuang->tanggal      = $request->input('tanggal');
                $managementRuang->jam_mulai    = $request->input('jam_mulai');
                $managementRuang->jam_selesai  = $request->input('jam_selesai');
                
                if ($examId) {
                    // This is for exam assignment
                    $exam = eksam::with(['rkm', 'materi', 'perusahaan'])->findOrFail($examId);
                    
                    $managementRuang->kebutuhan = 'Exam - ' . ($exam->materi->nama_materi ?? 'Unknown');
                    $managementRuang->keterangan = 'Exam untuk ' . ($exam->perusahaan->nama_perusahaan ?? 'Unknown') . ' (Pax: ' . $exam->pax . ')';
                    
                    // Update RKM
                    $exam->rkm->update([
                        'ruang' => $request->input('ruang'),
                        'tanggal_awal' => $request->input('tanggal'),
                        'tanggal_akhir' => $request->input('tanggal'),
                        'metode_kelas' => 'Offline'
                    ]);
                } else {
                    // Regular management ruangan
                    $managementRuang->kebutuhan = $request->input('kebutuhan');
                    $managementRuang->keterangan = $request->input('keterangan');
                }
                
                $managementRuang->save();
            });

            if ($examId) {
                // Clear session and redirect to exam index
                session()->forget(['exam_assign_id', 'exam_assign_data']);
                return redirect()->route('exam.index')->with('success', 'Ruangan berhasil di-assign untuk exam.');
            } else {
                return redirect()->back()->with('success', 'Ruangan berhasil dijadwalkan.');
            }
            
        } catch (\Exception $e) {
            if ($examId) {
                return redirect()->route('exam.index')->with('error', 'Gagal assign ruangan: ' . $e->getMessage());
            } else {
                return redirect()->back()->with('error', 'Gagal menjadwalkan ruangan: ' . $e->getMessage());
            }
        }
    }
    
    public function update(Request $request, $id) {}
    
    // BARU: Method untuk pembatalan jadwal manajemen ruangan
    public function batalkan(Request $request, $id)
    {
        try {
            $jadwal = manajemenRuangan::findOrFail($id);
            
            // Validasi: hanya boleh batalkan jadwal masa depan/hari ini
            $jadwalDate = \Carbon\Carbon::parse($jadwal->tanggal)->startOfDay();
            if ($jadwalDate < now()->startOfDay()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat membatalkan jadwal yang sudah lewat.'
                ], 400);
            }

            $jadwal->delete(); // Soft delete jika model pakai SoftDeletes, else hard delete
            
            return response()->json([
                'success' => true,
                'message' => 'Jadwal berhasil dibatalkan.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan: ' + $e->getMessage()
            ], 500);
        }
    }
}