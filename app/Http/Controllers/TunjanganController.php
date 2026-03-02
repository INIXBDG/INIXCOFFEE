<?php

namespace App\Http\Controllers;

use App\Models\AbsensiKaryawan;
use App\Models\JenisTunjangan;
use App\Models\Karyawan;
use App\Models\Lembur;
use App\Models\PengajuanCuti;
use App\Models\TunjanganKaryawan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exports\TunjanganExport;
use Maatwebsite\Excel\Facades\Excel;

class TunjanganController extends Controller
{
    protected $AbsensiKaryawanController;
    protected $overtimeController;

    public function __construct(AbsensiKaryawanController $AbsensiKaryawanController, OvertimeController $overtimeController)
    {
        $this->middleware('auth');
        $this->AbsensiKaryawanController = $AbsensiKaryawanController;
        $this->overtimeController = $overtimeController;
    }

    public function index()
    {
        $month = Carbon::now()->format('m');
        $year = Carbon::now()->format('Y');
        return view('tunjangan.index', compact('month', 'year'));
    }

    public function getJenisTunjanganOffice()
    {
        $tunjangan = JenisTunjangan::whereIn('divisi', ['Office', 'All'])->get();

        return response()->json([
            'success' => true,
            'message' => 'List Tunjangan',
            'data' => $tunjangan,
        ]);
    }

    public function getJenisTunjanganEdu()
    {
        $tunjangan = JenisTunjangan::whereIn('divisi', ['Education', 'All'])->get();

        return response()->json([
            'success' => true,
            'message' => 'List Tunjangan',
            'data' => $tunjangan,
        ]);
    }

    public function getJenisTunjanganSales()
    {
        $tunjangan = JenisTunjangan::whereIn('divisi', ['Sales', 'All'])->get();

        return response()->json([
            'success' => true,
            'message' => 'List Tunjangan',
            'data' => $tunjangan,
        ]);
    }

    public function getJenisTunjanganUmum()
    {
        $tunjangan = JenisTunjangan::whereIn('nama_tunjangan', ['Absensi', 'Makan', 'Transport', 'Lembur'])->get();

        return response()->json([
            'success' => true,
            'message' => 'List Tunjangan',
            'data' => $tunjangan,
        ]);
    }

    public function indexGenerate()
    {
        $tunjangan = JenisTunjangan::all();
        return view('tunjangan.generate', compact('tunjangan'));
    }

    public function getJenisTunjanganIndex()
    {
        $post = JenisTunjangan::where('divisi', 'All')->get();

        return response()->json([
            'success' => true,
            'message' => 'List Tunjangan',
            'data' => $post,
        ]);
    }

    public function getTunjanganSaya($id, $month, $year)
    {
        if ($month == 1) {
            $bulan = 12;
            $tahun = $year - 1;
        } else {
            $bulan = $month - 1;
            $tahun = $year;
        }

        // Hanya ambil tunjangan yang sudah di-approve
        $tunjangan = TunjanganKaryawan::where('id_karyawan', $id)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
          //  ->approved() // Gunakan scope approved
            ->with('karyawan', 'jenistunjangan')
            ->get();

        $gaji = Karyawan::findOrFail($id)->gaji;

        return response()->json([
            'success' => true,
            'message' => 'List Tunjangan Saya pada bulan ' . $bulan . '-' . $tahun,
            'data' => $tunjangan,
            'gaji' => $gaji
        ]);
    }

    // Halaman approval untuk GM
    public function indexApproval()
    {
        $month = Carbon::now()->format('m');
        $year = Carbon::now()->format('Y');
        return view('tunjangan.approval', compact('month', 'year'));
    }

    public function getTunjanganPendingApproval($month, $year)
    {
        try {
            if ($month == 1) {
                $bulan = 12;
                $tahun = $year - 1;
            } else {
                $bulan = $month - 1;
                $tahun = $year;
            }

            $tunjangan = TunjanganKaryawan::where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->where('status_approval', 'pending')
                ->with(['karyawan', 'jenistunjangan'])
                ->get();

            if ($tunjangan->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tidak ada tunjangan pending untuk bulan ' . $bulan . '-' . $tahun,
                    'data' => []
                ]);
            }

            $grouped = $tunjangan->groupBy('id_karyawan');
            $formattedData = [];

            foreach ($grouped as $karyawanId => $items) {
                $totalTunjangan = 0;
                $totalPotongan = 0;
                $details = [];

                foreach ($items as $item) {
                    if (!$item->jenistunjangan) {
                        \Log::warning("Jenis tunjangan null untuk item ID: " . $item->id);
                        continue;
                    }

                    $details[] = [
                        'id' => $item->id,
                        'nama_tunjangan' => $item->jenistunjangan->nama_tunjangan,
                        'keterangan' => $item->keterangan,
                        'total' => (float) $item->total,
                    ];

                    if ($item->keterangan == 'Tunjangan') {
                        $totalTunjangan += (float) $item->total;
                    } else {
                        $totalPotongan += (float) $item->total;
                    }
                }

                $firstItem = $items->first();
                
                if (!$firstItem || !$firstItem->karyawan) {
                    \Log::warning("Karyawan null untuk id_karyawan: " . $karyawanId);
                    continue;
                }

                $formattedData[] = [
                    'id_karyawan' => (int) $karyawanId,
                    'nama_karyawan' => $firstItem->karyawan->nama_lengkap ?? 'Unknown',
                    'divisi' => $firstItem->karyawan->divisi ?? '-',
                    'bulan' => (int) $bulan,
                    'tahun' => (int) $tahun,
                    'total_tunjangan' => (float) $totalTunjangan,
                    'total_potongan' => (float) $totalPotongan,
                    'total_bersih' => (float) ($totalTunjangan + $totalPotongan),
                    'details' => $details,
                    'jumlah_item' => count($items)
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'List Tunjangan Pending Approval',
                'data' => $formattedData
            ]);
        } catch (\Exception $e) {
            \Log::error('=== ERROR getTunjanganPendingApproval ===');
            \Log::error('Message: ' . $e->getMessage());
            \Log::error('File: ' . $e->getFile() . ':' . $e->getLine());
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function approveTunjangan(Request $request)
    {
        $request->validate([
            'id_karyawan' => 'required',
            'bulan' => 'required',
            'tahun' => 'required',
            'type' => 'required|in:all,selected',
            'item_ids' => 'required_if:type,selected|array'
        ]);

        try {
            DB::beginTransaction();

            if ($request->type == 'all') {
                TunjanganKaryawan::where('id_karyawan', $request->id_karyawan)
                    ->where('bulan', $request->bulan)
                    ->where('tahun', $request->tahun)
                    ->where('status_approval', 'pending')
                    ->update([
                        'status_approval' => 'approved',
                        'approved_by' => auth()->id(),
                        'approved_at' => now()
                    ]);
            } else {
                TunjanganKaryawan::whereIn('id', $request->item_ids)
                    ->where('status_approval', 'pending')
                    ->update([
                        'status_approval' => 'approved',
                        'approved_by' => auth()->id(),
                        'approved_at' => now()
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tunjangan berhasil di-approve'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error approving tunjangan: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat approve tunjangan'
            ], 500);
        }
    }

    public function rejectTunjangan(Request $request)
    {
        $request->validate([
            'id_karyawan' => 'required',
            'bulan' => 'required',
            'tahun' => 'required',
            'rejection_note' => 'required|string',
            'type' => 'required|in:all,selected',
            'item_ids' => 'required_if:type,selected|array'
        ]);

        try {
            DB::beginTransaction();

            if ($request->type == 'all') {
                TunjanganKaryawan::where('id_karyawan', $request->id_karyawan)
                    ->where('bulan', $request->bulan)
                    ->where('tahun', $request->tahun)
                    ->where('status_approval', 'pending')
                    ->update([
                        'status_approval' => 'rejected',
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                        'rejection_note' => $request->rejection_note
                    ]);
            } else {
                TunjanganKaryawan::whereIn('id', $request->item_ids)
                    ->where('status_approval', 'pending')
                    ->update([
                        'status_approval' => 'rejected',
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                        'rejection_note' => $request->rejection_note
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tunjangan berhasil di-reject'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error rejecting tunjangan: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat reject tunjangan'
            ], 500);
        }
    }

    public function bulkApproveTunjangan(Request $request)
    {
        $request->validate([
            'bulan' => 'required',
            'tahun' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $updated = TunjanganKaryawan::where('bulan', $request->bulan)
                ->where('tahun', $request->tahun)
                ->where('status_approval', 'pending')
                ->update([
                    'status_approval' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now()
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil approve {$updated} tunjangan"
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error bulk approving: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat bulk approve'
            ], 500);
        }
    }

    public function getApprovalHistory($month, $year)
    {
        if ($month == 1) {
            $bulan = 12;
            $tahun = $year - 1;
        } else {
            $bulan = $month - 1;
            $tahun = $year;
        }

        $tunjangan = TunjanganKaryawan::where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->whereIn('status_approval', ['approved', 'rejected'])
            ->with(['karyawan', 'jenistunjangan', 'approvedBy'])
            ->orderBy('approved_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tunjangan
        ]);
    }

    public function getTunjanganSayaGenerate($id, $month, $year)
    {
        $tunjangan = TunjanganKaryawan::where('id_karyawan', $id)
            ->where('bulan', $month)
            ->where('tahun', $year)
            ->with('karyawan', 'jenistunjangan')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'List Tunjangan Saya pada bulan ' . $month . '-' . $year,
            'data' => $tunjangan
        ]);
    }

    public function generateTunjanganPDF($id, $month, $year)
    {
        if ($month == 1) {
            $bulan = 12;
            $tahun = $year - 1;
        } else {
            $bulan = $month - 1;
            $tahun = $year;
        }

        $post = TunjanganKaryawan::where('id_karyawan', $id)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->with('karyawan', 'jenistunjangan')
            ->get();

        $totalTunjangan = 0;
        $totalPotongan = 0;
        $dataluar = $this->AbsensiKaryawanController->jumlahAbsensi($id, $bulan, $tahun);

        if ($dataluar instanceof \Illuminate\Http\JsonResponse) {
            $absensiData = $dataluar->getData();
        } else {
            $absensiData = $dataluar;
        }
        $jumlahAbsensi = $absensiData->data->jumlah_absensi;

        foreach ($post as $item) {
            if ($item->keterangan == 'Tunjangan') {
                $totalTunjangan += $item->total;
            } else if ($item->keterangan == 'Potongan') {
                $totalPotongan += $item->total;
            }
        }

        $totalBersih = $totalTunjangan + $totalPotongan;

        $hrd = Karyawan::where('jabatan', 'Koordinator Office')->first();
        $direktur = Karyawan::where('jabatan', 'Direktur Utama')->first();
        $me = Karyawan::where('id', $id)->first();

        $data = [
            'absensi' => $jumlahAbsensi,
            'tunjangan' => $post,
            'month' => \Carbon\Carbon::createFromFormat('m', $bulan)->format('F Y'),
            'hrd' => $hrd,
            'direktur' => $direktur,
            'me' => $me,
            'totalTunjangan' => $totalTunjangan,
            'totalPotongan' => $totalPotongan,
            'totalBersih' => $totalBersih,
        ];

        $pdf = Pdf::loadView('tunjangan.pdf', $data);
        return $pdf->download('Tunjangan_' . $id . '_' . $bulan . '_' . $tahun . '.pdf');
    }

    public function tunjanganExportPDF($month, $year)
    {
        if ($month == 1) {
            $bulan = 12;
            $tahun = $year - 1;
        } else {
            $bulan = $month - 1;
            $tahun = $year;
        }

        $post = TunjanganKaryawan::where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->with('karyawan', 'jenistunjangan')
            ->get()
            ->groupBy('karyawan.nama_lengkap')
            ->sortBy(function ($group) {
                return $group->first()->karyawan->divisi;
            });

        return view('tunjangan.exportpdf', compact('post', 'bulan', 'tahun'));
    }

    public function create()
    {
        return view('tunjangan.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'nama_tunjangan' => 'required',
            'tipe' => 'required',
            'nilai' => 'required',
            'divisi' => 'required',
            'hitung' => 'required',
        ]);

        $existingdivisi = JenisTunjangan::where('nama_tunjangan', $request->nama_tunjangan)
            ->where('tipe', $request->tipe)
            ->where('nilai', $request->nilai)
            ->first();

        if ($existingdivisi) {
            return redirect()->back()->withErrors(['duplicate' => 'Data ini sudah ada!'])->withInput();
        }

        $nilai = $request->nilai;
        if ($request->tipe === 'potongan') {
            $nilai = '-' . abs($nilai);
        }

        JenisTunjangan::create([
            'nama_tunjangan' => $request->nama_tunjangan,
            'tipe' => $request->tipe,
            'nilai' => $nilai,
            'divisi' => $request->divisi,
            'hitung' => $request->hitung,
        ]);

        return redirect()->back()->with(['success' => 'Data Berhasil Disimpan!']);
    }

    public function edit($id)
    {
        $tunjangan = JenisTunjangan::findOrFail($id);
        return view('tunjangan.edit', compact('tunjangan'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'nama_tunjangan' => 'required',
            'tipe' => 'required',
            'nilai' => 'required',
            'divisi' => 'required',
            'hitung' => 'required',
        ]);

        $tunjangan = JenisTunjangan::findOrFail($id);

        $tunjangan->update([
            'nama_tunjangan' => $request->nama_tunjangan,
            'tipe' => $request->tipe,
            'nilai' => $request->nilai,
            'divisi' => $request->divisi,
            'hitung' => $request->hitung,
        ]);

        return redirect()->back()->with(['success' => 'Data Berhasil Diupdate!']);
    }

    public function penghitunganTunjangan()
    {
        $month = now()->month;
        $year = now()->year;

        if ($month == 1) {
            $bulan = 12;
            $tahun = $year - 1;
        } else {
            $bulan = $month - 1;
            $tahun = $year;
        }

        $karyawanList = Karyawan::whereNotIn('jabatan', ['Komisaris', 'Direktur'])
            ->whereNotIn('id', [1, 3])
            ->where('kode_karyawan', 'not like', '%OL%')
            ->where('status_aktif', '1')
            ->get();

        foreach ($karyawanList as $karyawan) {
            $karyawanId = $karyawan->id;

            $existingCalculation = TunjanganKaryawan::where('id_karyawan', $karyawanId)
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->first();

            if ($existingCalculation) {
                continue; // Skip if calculation already exists
            }

            $absensiKaryawan = AbsensiKaryawan::whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->where('id_karyawan', $karyawanId)
                ->get();

            $absen_pulang = AbsensiKaryawan::whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->where('jam_keluar', '=', null)
                ->where('id_karyawan', $karyawanId)
                ->get();

            $jumlahAbsen = $absensiKaryawan->count();
            $jumlahAbsensiPulang = $absen_pulang->count();
            $jumlahAbsensi = $jumlahAbsen - $jumlahAbsensiPulang;

            $totalSeconds = $absensiKaryawan->sum(function ($item) {
                if (!empty($item->waktu_keterlambatan) && strpos($item->waktu_keterlambatan, ':') !== false) {
                    list($hours, $minutes, $seconds) = explode(':', $item->waktu_keterlambatan);
                    return $hours * 3600 + $minutes * 60 + $seconds;
                }
                return 0;
            });

            $includeTunjangan = ['Makan', 'Transport', 'Lembur'];
            if ($totalSeconds > 0) {
                if ($totalSeconds > 900) {
                    $keterangan = "Terlambat > 15 menit";
                } else {
                    $keterangan = "Terlambat " . floor($totalSeconds / 60) . " menit";
                    $includeTunjangan[] = 'Absensi';
                }
            } else {
                $keterangan = "Tidak pernah terlambat";
                $includeTunjangan[] = 'Absensi';
            }

            $jenisTunjangan = JenisTunjangan::where('divisi', 'All')
                ->whereIn('nama_tunjangan', $includeTunjangan)
                ->get();

            $cuti = PengajuanCuti::where('id_karyawan', $karyawanId)
                ->whereYear('tanggal_awal', $tahun)
                ->whereMonth('tanggal_awal', $bulan)
                ->get();

            if ($cuti->isNotEmpty()) {
                $cutikaryawan = $cuti->sum('durasi');
                if ($cutikaryawan >= 3) {
                    $jumlahcuti = $cutikaryawan - 3;
                    $jumlahAbsensi = $jumlahAbsensi - $jumlahcuti;
                }
            }

            foreach ($jenisTunjangan as $tunjangan) {
                if ($tunjangan->nama_tunjangan == 'Lembur') {
                    $lembur = Lembur::with('karyawan', 'hitunglembur')
                        ->where('id_karyawan', $karyawanId)
                        ->whereMonth('tanggal_spl', $bulan)
                        ->whereYear('tanggal_spl', $tahun)
                        ->get();

                    $totalLemburan = 0;

                    foreach ($lembur as $data) {
                        if ($data->id_hitung_lembur == null || $data->id_hitung_lembur == '') {
                            continue;
                        }

                        if ($data->hitunglembur === null) {
                            continue;
                        }

                        $nilaiLembur = $data->hitunglembur->nilai_lembur;
                        $jamLembur = (strtotime($data->jam_selesai) - strtotime($data->jam_mulai)) / 3600;
                        $subtotal = $jamLembur * $nilaiLembur;
                        $totalLemburan += $subtotal;
                    }

                    if ($totalLemburan > 0) {
                        $jenisTunjangans = JenisTunjangan::where('nama_tunjangan', 'Lembur')->first();
                        $tunjanganKaryawan = new TunjanganKaryawan();
                        $tunjanganKaryawan->id_karyawan = $karyawanId;
                        $tunjanganKaryawan->bulan = $bulan;
                        $tunjanganKaryawan->tahun = $tahun;
                        $tunjanganKaryawan->jenis_tunjangan = $jenisTunjangans->id;
                        $tunjanganKaryawan->keterangan = $tunjangan->tipe;
                        $tunjanganKaryawan->total = $totalLemburan;
                        $tunjanganKaryawan->save();
                    }
                } elseif ($tunjangan->hitung == 'Perhari' && $tunjangan->tipe == 'Tunjangan') {
                    if ($karyawan->jabatan == 'Direktur Utama') {
                        $jumlahAbsensi = AbsensiKaryawan::whereMonth('tanggal', $bulan)
                            ->whereYear('tanggal', $tahun)
                            ->whereRaw('DAYOFWEEK(tanggal) NOT IN (1, 7)')
                            ->distinct()
                            ->count('tanggal');

                        $sebelumtigaratus = (float) $tunjangan->nilai * $jumlahAbsensi;
                        $jumlahTunjangan = $sebelumtigaratus + 300000;
                    } else {
                        $jumlahTunjangan = $tunjangan->nilai * $jumlahAbsensi;
                    }

                    $tunjanganKaryawan = new TunjanganKaryawan();
                    $tunjanganKaryawan->id_karyawan = $karyawanId;
                    $tunjanganKaryawan->bulan = $bulan;
                    $tunjanganKaryawan->tahun = $tahun;
                    $tunjanganKaryawan->jenis_tunjangan = $tunjangan->id;
                    $tunjanganKaryawan->keterangan = $tunjangan->tipe;
                    $tunjanganKaryawan->total = (float) $jumlahTunjangan;
                    $tunjanganKaryawan->save();
                } elseif ($tunjangan->hitung == 'Perbulan' && $tunjangan->tipe == 'Tunjangan') {
                    $tunjanganKaryawan = new TunjanganKaryawan();
                    $tunjanganKaryawan->id_karyawan = $karyawanId;
                    $tunjanganKaryawan->bulan = $bulan;
                    $tunjanganKaryawan->tahun = $tahun;
                    $tunjanganKaryawan->jenis_tunjangan = $tunjangan->id;
                    $tunjanganKaryawan->keterangan = $tunjangan->tipe;
                    $tunjanganKaryawan->total = (float) $tunjangan->nilai;
                    $tunjanganKaryawan->save();
                }
            }
        }

        return redirect()->route('tunjangangenerate.index')->with(['success' => 'Penghitungan tunjangan berhasil!']);
    }

    public function createManual()
    {
        $month = now()->month;
        $year = now()->year;

        if ($month == 1) {
            $bulan = 12;
            $tahun = $year - 1;
        } else {
            $bulan = $month - 1;
            $tahun = $year;
        }

        $karyawan = Karyawan::where('status_aktif', '1')->get();
        $tunjangan = JenisTunjangan::all();

        return view('tunjangan.createManual', compact('karyawan', 'tunjangan', 'bulan', 'tahun'));
    }

    public function storeManualTunjangan(Request $request)
    {
        $request->validate([
            'id_tunjangan' => 'required|exists:jenis_tunjangans,id',
            'karyawan_id' => 'required|array',
            'karyawan_id.*' => 'required|numeric',
            'nilai' => 'nullable|string|max:255',
            'kelipatan' => 'nullable|string|max:255',
            'hitung' => 'nullable|string|max:255',
        ]);

        $month = now()->month;
        $year = now()->year;

        if ($month == 1) {
            $bulan = 12;
            $tahun = $year - 1;
        } else {
            $bulan = $month - 1;
            $tahun = $year;
        }

        foreach ($request->karyawan_id as $karyawanId) {
            $jenisTunjangan = JenisTunjangan::findOrFail($request->id_tunjangan);

            if ($jenisTunjangan->nama_tunjangan == 'BPJS Keluarga') {
                $nilai = $request->nilai * $request->kelipatan;
            } else {
                $nilai = $request->nilai;
            }

            if ($request->hitung == 'Perhari') {
                $absensiKaryawan = AbsensiKaryawan::whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun)
                    ->where('id_karyawan', $karyawanId)
                    ->get();

                $jumlahAbsensi = $absensiKaryawan->count();
                $nilaitotal = $nilai * $jumlahAbsensi;
            } else {
                $nilaitotal = $nilai;
            }

            $tunjanganKaryawan = new TunjanganKaryawan();
            $tunjanganKaryawan->id_karyawan = $karyawanId;
            $tunjanganKaryawan->bulan = $bulan;
            $tunjanganKaryawan->tahun = $tahun;
            $tunjanganKaryawan->jenis_tunjangan = $jenisTunjangan->id;
            $tunjanganKaryawan->keterangan = $jenisTunjangan->tipe;
            $tunjanganKaryawan->total = (float) $nilaitotal;
            $tunjanganKaryawan->save();
        }

        return redirect()->route('tunjangangenerate.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }

    public function storeManual(Request $request)
    {
        $bulan = $request->input('bulan_tunjangan');
        $tahun = $request->input('tahun_tunjangan');
        $karyawanId = $request->input('karyawan_id');
        $dataTunjangan = $request->input('dataTunjangan');
        $deletedata = $request->input('deletedata');

        DB::beginTransaction();

        try {
            if ($deletedata) {
                foreach ($deletedata as $nama_tunjangan) {
                    $jenisTunjangan = JenisTunjangan::where('nama_tunjangan', $nama_tunjangan)->first();
                    $tunjanganKaryawan = TunjanganKaryawan::where('id_karyawan', $karyawanId)
                        ->where('bulan', $bulan)
                        ->where('tahun', $tahun)
                        ->where('jenis_tunjangan', $jenisTunjangan->id)
                        ->first();
                    if ($tunjanganKaryawan) {
                        $tunjanganKaryawan->delete();
                    }
                }
            }

            if ($dataTunjangan) {
                foreach ($dataTunjangan as $namaTunjangan => $nilai) {
                    $namaTunjanganId = str_replace('_', ' ', $namaTunjangan);

                    $jenisTunjangan = JenisTunjangan::where('nama_tunjangan', $namaTunjanganId)->first();

                    if (!$jenisTunjangan) {
                        continue;
                    }

                    $keterangan = $nilai < 0 ? 'Potongan' : 'Tunjangan';

                    $tunjanganKaryawan = TunjanganKaryawan::where('id_karyawan', $karyawanId)
                        ->where('bulan', $bulan)
                        ->where('tahun', $tahun)
                        ->where('jenis_tunjangan', $jenisTunjangan->id)
                        ->first();

                    if ($tunjanganKaryawan) {
                        $tunjanganKaryawan->total = (float) $nilai;
                        $tunjanganKaryawan->save();
                    } else {
                        $tunjanganKaryawan = new TunjanganKaryawan();
                        $tunjanganKaryawan->id_karyawan = $karyawanId;
                        $tunjanganKaryawan->bulan = $bulan;
                        $tunjanganKaryawan->tahun = $tahun;
                        $tunjanganKaryawan->jenis_tunjangan = $jenisTunjangan->id;
                        $tunjanganKaryawan->keterangan = $keterangan;
                        $tunjanganKaryawan->total = (float) $nilai;
                        $tunjanganKaryawan->save();
                    }
                }
            }

            DB::commit();

            return redirect()->route('tunjangangenerate.index')->with(['success' => 'Data Berhasil Disimpan!']);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed : ' . $e->getMessage());
            return redirect()->route('tunjangangenerate.index')->with(['error' => 'Terjadi kesalahan, coba lagi.']);
        }
    }

    public function tunjanganExportExcel($month, $year)
    {
        if ($month == 1) {
            $bulan = 12;
            $tahun = $year - 1;
        } else {
            $bulan = $month - 1;
            $tahun = $year;
        }

        $post = TunjanganKaryawan::where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->with('karyawan', 'jenistunjangan')
            ->get()
            ->groupBy('karyawan.nama_lengkap')
            ->sortBy(function ($group) {
                return $group->first()->karyawan->divisi;
            });

        $nama_tunjangan = JenisTunjangan::get();

        return Excel::download(new TunjanganExport($post, $nama_tunjangan), 'rekap_tunjangan.xlsx');
    }
    
}

// foreach ($dataTunjangan as $tunjangan) {

        // $jenisTunjangan = JenisTunjangan::where('nama_tunjangan', $tunjangan)->first();
        // $nama_tunjangan = $jenisTunjangan->nama_tunjangan;
        // // Data tunjangan yang akan ditambahkan
        // $dataTunjangan = [];

        // // Cek jika tipe tunjangan adalah 'Perbulan' dan jenis tunjangan adalah 'Tunjangan'
        // if ($request->input('hitung') == 'Perbulan' && $jenisTunjangan->tipe == 'Tunjangan') {
        //     $tunjangan->total_tunjangan += $request->input('nilai');
        //         $dataTunjangan[] = [
        //             'nama_tunjangan' => $jenisTunjangan->nama_tunjangan,
        //             'tipe' => $jenisTunjangan->tipe,
        //             'jumlah' => $request->input('nilai'),
        //             'total' => $request->input('nilai'),
        //         ];
        // }

        // if ($request->input('hitung') == 'Perhari' && $jenisTunjangan->tipe == 'Tunjangan') {
        //     $jumlahTunjangan = $request->input('nilai') * $jumlahAbsensi;
        //     $tunjangan->total_tunjangan += $jumlahTunjangan;
        //     $dataTunjangan[] = [
        //         'nama_tunjangan' => $tunjangan->nama_tunjangan,
        //         'tipe' => $tunjangan->tipe,
        //         'jumlah' => $tunjangan->nilai,
        //         'jumlah_absensi' => $jumlahAbsensi,
        //         'total' => $jumlahTunjangan
        //     ];
        // }

        // if ($request->input('hitung') == 'Perbulan' && $jenisTunjangan->tipe == 'Potongan' && $nama_tunjangan == 'BPJS Keluarga') {
        //     $totalkelipatan = $request->input('nilai') * $request->input('kelipatan');
        //     $tunjangan->total_potongan += $totalkelipatan;
        //     $dataTunjangan[] = [
        //         'nama_tunjangan' => $jenisTunjangan->nama_tunjangan,
        //         'tipe' => $jenisTunjangan->tipe,
        //         'jumlah' => -$request->input('nilai'),
        //         'total' => $totalkelipatan,
        //     ];
        // } elseif ($jenisTunjangan->tipe == 'Potongan' && $nama_tunjangan != 'BPJS Keluarga') {
        //     // Hanya untuk tunjangan potongan lainnya, bukan BPJS Keluarga
        //     $tunjangan->total_potongan += $request->input('nilai');
        //     $dataTunjangan[] = [
        //         'nama_tunjangan' => $jenisTunjangan->nama_tunjangan,
        //         'tipe' => $jenisTunjangan->tipe,
        //         'jumlah' => -$request->input('nilai'),
        //         'total' => -$request->input('nilai'),
        //     ];
        // }

        // $tunjangansebelumnya = array_merge($tunjangansebelumnya, $dataTunjangan);
        // // dd($tunjangansebelumnya);
        // $tunjangan->data_tunjangan = json_encode($tunjangansebelumnya);
        // $totalBersih = $tunjangan->total_tunjangan - $tunjangan->total_potongan;
        // $tunjangan->total_bersih = $totalBersih;
        // $tunjangan->save();
        // }
