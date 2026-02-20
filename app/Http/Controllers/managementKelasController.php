<?php

namespace App\Http\Controllers;

use App\Models\manajemenRuangan;
use App\Models\RKM;
use App\Models\eksam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        // Query RKM
        $queryRkm = RKM::with(['sales', 'materi', 'instruktur', 'instruktur2', 'asisten', 'perusahaan'])
            ->where('metode_kelas', 'Offline')
            ->whereNotNull('ruang')
            ->where('ruang', '!=', '');

        if ($ruang && $tanggal_ruang) {
            $tanggalRuang = collect((array) $tanggal_ruang)
                ->map(fn($tgl) => \Carbon\Carbon::parse($tgl)->format('Y-m-d'))
                ->toArray();

            $queryRkm->where('ruang', $ruang)
                ->where(function ($q) use ($tanggalRuang) {
                    foreach ($tanggalRuang as $tanggal) {
                        $q->orWhere(function ($subQ) use ($tanggal) {
                            $subQ->whereDate('tanggal_awal', '<=', $tanggal)
                                ->whereDate('tanggal_akhir', '>=', $tanggal);
                        });
                    }
                });
        } elseif ($filter_utama) {
            $filterUtama = \Carbon\Carbon::parse($filter_utama)->format('Y-m-d');
            $queryRkm->where(function ($q) use ($filterUtama) {
                $q->whereDate('tanggal_awal', '<=', $filterUtama)
                    ->whereDate('tanggal_akhir', '>=', $filterUtama);
            });
        }

        $kelasRkm = $queryRkm->get();
        $groupedRkm = $kelasRkm->groupBy('ruang');

        $dataRKM = $groupedRkm->map(function ($items) {
            $first = $items->first();
            $awal = \Carbon\Carbon::parse($first->tanggal_awal)->format('Y-m-d');
            $akhir = \Carbon\Carbon::parse($first->tanggal_akhir)->format('Y-m-d');

            return [
                "key" => $first->ruang,
                "ruang" => $first->ruang,
                "ruangan" => "-",
                "tanggal_awal" => $awal,
                "tanggal_akhir" => $akhir,
                "materi" => $items->pluck("materi.nama_materi")->filter()->implode(", "),
                "sales" => $items->pluck("sales.nama_lengkap")->filter()->implode(", "),
                "instruktur" => $items->pluck("instruktur.nama_lengkap")->filter()->implode(", "),
                "instruktur2" => $items->pluck("instruktur2.nama_lengkap")->filter()->implode(", "),
                "asisten" => $items->pluck("asisten.nama_lengkap")->filter()->implode(", "),
                "perusahaan" => $items->pluck("perusahaan.nama_perusahaan")->filter()->implode(", "),
                "harga_jual" => $first->harga_jual,
                "pax" => $first->pax,
                "exam" => $first->exam ? "Ya" : "Tidak",
                "authorize" => $first->authorize ? "ya" : "Tidak",
            ];
        })->keyBy('key')->toArray();

        // Query Management Ruangan
        $queryMR = DB::table('manajemen_ruangans');

        if ($ruang && $tanggal_ruang) {
            $tanggalRuang = collect((array) $tanggal_ruang)
                ->map(fn($tgl) => \Carbon\Carbon::parse($tgl)->format('Y-m-d'))
                ->toArray();
            $queryMR->where('ruangan', $ruang)
                ->whereIn(DB::raw('DATE(tanggal)'), $tanggalRuang);
        } elseif ($filter_utama) {
            $filterUtama = \Carbon\Carbon::parse($filter_utama)->format('Y-m-d');
            $queryMR->whereDate('tanggal', $filterUtama);
        }

        $kelasMR = $queryMR->get();

        $dataMR = $kelasMR->groupBy('ruangan')->map(function ($items) {
            $first = $items->first();
            return [
                "key" => $first->ruangan,
                "ruang" => "-",
                "ruangan" => $first->ruangan,
                "tanggal" => \Carbon\Carbon::parse($first->tanggal)->format("Y-m-d"),
                "jam_mulai" => $first->jam_mulai,
                "jam_selesai" => $first->jam_selesai,
                "kebutuhan" => $first->kebutuhan,
                "keterangan" => $first->keterangan ?? "-",
                "id" => $first->id,
            ];
        })->toArray();

        $allRooms = ['1', '2', '3', '4', '5', '6', 'ADOC'];

        $final = collect($allRooms)->map(function ($room) use ($dataRKM, $dataMR) {
            $rkm = $dataRKM[$room] ?? null;
            $mr = $dataMR[$room] ?? null;

            if ($rkm)
                return $rkm;
            elseif ($mr)
                return $mr;
            return null;
        })->filter()->values();

        return response()->json($final);
    }

    public function create($id)
    {
    }

    /**
     * Store a newly created resource in storage.
     * ✅ Return JSON untuk AJAX requests
     */
    public function store(Request $request)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'ruang' => 'required|string|max:50',
                'tanggal' => 'required|date',
                'jam_mulai' => 'required|date_format:H:i',
                'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
                'kebutuhan' => 'nullable|string',
                'keterangan' => 'nullable|string',
            ], [
                'jam_selesai.after' => 'Jam selesai harus lebih dari jam mulai',
            ]);

            $examId = session('exam_assign_id');
            $managementRuang = null;

            DB::transaction(function () use ($request, $examId, &$managementRuang) {
                $managementRuang = new manajemenRuangan();
                $managementRuang->ruangan = $request->input('ruang');
                $managementRuang->tanggal = $request->input('tanggal');
                $managementRuang->jam_mulai = $request->input('jam_mulai');
                $managementRuang->jam_selesai = $request->input('jam_selesai');

                if ($examId) {
                    // This is for exam assignment
                    $exam = eksam::with(['rkm', 'materi', 'perusahaan'])->findOrFail($examId);

                    $managementRuang->kebutuhan = 'Exam - ' . ($exam->materi->nama_materi ?? 'Unknown');
                    $managementRuang->keterangan = 'Exam untuk ' . ($exam->perusahaan->nama_perusahaan ?? 'Unknown') . ' (Pax: ' . $exam->pax . ')';

                    // Update RKM jika ada
                    if ($exam->rkm) {
                        $exam->rkm->update([
                            'ruang' => $request->input('ruang'),
                            'tanggal_awal' => $request->input('tanggal'),
                            'tanggal_akhir' => $request->input('tanggal'),
                            'metode_kelas' => 'Offline'
                        ]);
                    }
                } else {
                    // Regular management ruangan
                    $managementRuang->kebutuhan = $request->input('kebutuhan');
                    $managementRuang->keterangan = $request->input('keterangan');
                }

                $managementRuang->save();
            });

            // ✅ RETURN JSON (BUKAN REDIRECT!)
            return response()->json([
                'success' => true,
                'message' => 'Ruangan berhasil dijadwalkan.',
                'data' => $managementRuang
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation error - return 422
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            // Log error untuk debugging
            Log::error('ManagementKelas store error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     * ✅ Return JSON untuk AJAX requests
     */
    public function update(Request $request, $id)
    {
        try {
            $managementRuang = manajemenRuangan::findOrFail($id);

            $validated = $request->validate([
                'ruang' => 'required|string|max:50',
                'tanggal' => 'required|date',
                'jam_mulai' => 'required|date_format:H:i',
                'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
                'kebutuhan' => 'nullable|string',
                'keterangan' => 'nullable|string',
            ], [
                'jam_selesai.after' => 'Jam selesai harus lebih dari jam mulai',
            ]);

            $managementRuang->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diperbarui!',
                'data' => $managementRuang
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('ManagementKelas update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage (batalkan jadwal).
     * ✅ Return JSON untuk AJAX requests
     */
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

            $jadwal->delete(); // Soft delete jika model pakai SoftDeletes

            return response()->json([
                'success' => true,
                'message' => 'Jadwal berhasil dibatalkan.'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data jadwal tidak ditemukan.'
            ], 404);

        } catch (\Exception $e) {
            Log::error('ManagementKelas batalkan error: ' . $e->getMessage());
            // ✅ PERBAIKAN: Pakai titik (.) untuk concat string di PHP, BUKAN plus (+)
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan: ' . $e->getMessage()
            ], 500);
        }
    }
}