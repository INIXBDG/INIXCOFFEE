<?php

namespace App\Http\Controllers;

use App\Models\AbsensiKaryawan;
use App\Models\JenisTunjangan;
use App\Models\karyawan;
use App\Models\lembur;
use App\Models\pengajuancuti;
use App\Models\Tunjangan;
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
        // return $year;
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
            $bulan = 12; // Desember
            $tahun = $year - 1; // Tahun sebelumnya
        } else {
            $bulan = $month - 1;
            $tahun = $year; // Untuk bulan lain, tetap bulan yang diminta
        }

        // Ambil data tunjangan berdasarkan ID karyawan, bulan, dan tahun
        $tunjangan = TunjanganKaryawan::where('id_karyawan', $id)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->with('karyawan', 'jenistunjangan') // Mengambil data karyawan terkait
            ->get();

        // Format data yang akan dikirimkan
        return response()->json([
            'success' => true,
            'message' => 'List Tunjangan Saya pada bulan ' . $bulan . '-' . $tahun,
            'data' => $tunjangan
        ]);
    }

    public function getTunjanganSayaGenerate($id, $month, $year)
    {
        $tunjangan = TunjanganKaryawan::where('id_karyawan', $id)
            ->where('bulan', $month)
            ->where('tahun', $year)
            ->with('karyawan', 'jenistunjangan') // Mengambil data karyawan terkait
            ->get();

        // Format data yang akan dikirimkan
        return response()->json([
            'success' => true,
            'message' => 'List Tunjangan Saya pada bulan ' . $month . '-' . $year,
            'data' => $tunjangan
        ]);
    }

    public function generateTunjanganPDF($id, $month, $year)
    {
        if ($month == 1) {
            $bulan = 12; // Desember
            $tahun = $year - 1; // Tahun sebelumnya
        } else {
            $bulan = $month - 1;
            $tahun = $year; // Untuk bulan lain, tetap bulan yang diminta
        }
        // dd($bulan, $tahun);
        // Ambil data tunjangan berdasarkan ID karyawan, bulan, dan tahun
        $post = TunjanganKaryawan::where('id_karyawan', $id)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->with('karyawan', 'jenistunjangan') // Mengambil data karyawan terkait
            ->get();
        // dd($post);
         // Hitung total tunjangan, potongan, dan total bersih
        $totalTunjangan = 0;
        $totalPotongan = 0;
        $dataluar = $this->AbsensiKaryawanController->jumlahAbsensi($id, $bulan, $tahun);
         // Check if the response is a JsonResponse
        if ($dataluar instanceof \Illuminate\Http\JsonResponse) {
            $absensiData = $dataluar->getData(); // Get the data from the JsonResponse
        } else {
            // Handle the case where the response is not a JsonResponse
            $absensiData = $dataluar; // Assuming it's already an array or object
        }
        $jumlahAbsensi = $absensiData->data->jumlah_absensi;
        // Iterasi melalui data untuk menghitung total berdasarkan keterangan
        foreach ($post as $item) {
            if ($item->keterangan == 'Tunjangan') {
                $totalTunjangan += $item->total; // Tambahkan total tunjangan
            } else if ($item->keterangan == 'Potongan') {
                $totalPotongan += $item->total; // Tambahkan total potongan (nilai negatif)
            }
        }

        // Hitung total bersih (total tunjangan - total potongan)
        $totalBersih = $totalTunjangan + $totalPotongan; // Potongan sudah negatif, jadi cukup tambahkan

        $hrd = karyawan::where('jabatan', 'Koordinator Office')->first();
        $direktur = karyawan::where('jabatan', 'Direktur Utama')->first();
        $me = karyawan::where('id', $id)->first();

        // Menyusun data untuk PDF
        $data = [
            'absensi' => $jumlahAbsensi,
            'tunjangan' => $post,
            'month' => \Carbon\Carbon::createFromFormat('m', $bulan)->format('F Y'), // Format bulan dan tahun
            'hrd' => $hrd,
            'direktur' => $direktur,
            'me' => $me,
            'totalTunjangan' => $totalTunjangan,
            'totalPotongan' => $totalPotongan,
            'totalBersih' => $totalBersih,
        ];
        // return $data;
        return view('tunjangan.pdf', $data);
        $pdf = pdf::loadView('tunjangan.pdf', $data);

        // Menyimpan atau langsung mengirimkan file PDF
        return $pdf->download('Tunjangan_'.$id.'_'.$bulan.'_'.$tahun.'.pdf');
    }

    public function tunjanganExportPDF($month, $year)
    {
        if ($month == 1) {
            $bulan = 12; // Desember
            $tahun = $year - 1; // Tahun sebelumnya
        } else {
            $bulan = $month - 1;
            $tahun = $year; // Untuk bulan lain, tetap bulan yang diminta
        }

        $post = TunjanganKaryawan::where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->with('karyawan', 'jenistunjangan') // Mengambil data karyawan terkait
            ->get()
            ->groupBy('karyawan.nama_lengkap') // Mengelompokkan berdasarkan nama karyawan
            ->sortBy(function($group) {
                return $group->first()->karyawan->divisi; // Mengurutkan berdasarkan divisi
            });


        // return $post;

        return view('tunjangan.exportpdf', compact('post', 'bulan', 'tahun'));
    }

    public function create()
    {
        return view('tunjangan.create');
    }

    public function store(Request $request)
    {
        // return $request->all();
        // Validate the request data
        $this->validate($request, [
            'nama_tunjangan' => 'required',
            'tipe' => 'required',
            'nilai' => 'required',
            'divisi' => 'required',
            'hitung' => 'required',

        ]);

        // Check if a record with the same 'nama_tunjangan', 'tipe', and 'nilai' already exists
        $existingdivisi = JenisTunjangan::where('nama_tunjangan', $request->nama_tunjangan)
            ->where('tipe', $request->tipe)
            ->where('nilai', $request->nilai)
            ->first();

        if ($existingdivisi) {
            // Redirect back with an error message if a duplicate is found
            return redirect()->back()->withErrors(['duplicate' => 'Data ini sudah ada!'])->withInput();
        }
        $nilai = $request->nilai;
        if ($request->tipe === 'potongan') {
            $nilai = '-' . abs($nilai); // Add minus sign before the value
        }
        // Create a new record if no duplicate exists
        JenisTunjangan::create([
            'nama_tunjangan' => $request->nama_tunjangan,
            'tipe' => $request->tipe,
            'nilai' => $nilai,
            'divisi' => $request->divisi,
            'hitung' => $request->hitung,
        ]);

        // Redirect to the index route with a success message
        return redirect()->back()->with(['success' => 'Data Berhasil Disimpan!']);
    }

    public function edit($id)
    {
        $tunjangan = JenisTunjangan::findOrFail($id);
        // return $tunjangan;
        return view('tunjangan.edit', compact('tunjangan'));
    }

    public function update(Request $request, $id)
    {
        // Validate the request data
        $this->validate($request, [
            'nama_tunjangan' => 'required',
            'tipe' => 'required',
            'nilai' => 'required',
            'divisi' => 'required',
            'hitung' => 'required',
        ]);
        $tunjangan = JenisTunjangan::findOrFail($id);

        // Create a new record if no duplicate exists
       $tunjangan->update([
            'nama_tunjangan' => $request->nama_tunjangan,
            'tipe' => $request->tipe,
            'nilai' => $request->nilai,
            'divisi' => $request->divisi,
            'hitung' => $request->hitung,
        ]);

        // Redirect to the index route with a success message
        return redirect()->back()->with(['success' => 'Data Berhasil Diupdate!']);
    }

    public function penghitunganTunjangan()
    {
        // Ambil bulan dan tahun saat ini
        $month = now()->month;
        $year = now()->year;

        if ($month == 1) {
            $bulan = 12; // Desember
            $tahun = $year - 1; // Tahun sebelumnya
        } else {
            $bulan = $month - 1;
            $tahun = $year; // Untuk bulan lain, tetap bulan yang diminta
        }

        // Ambil semua karyawan yang aktif dan termasuk dalam divisi yang relevan
        $karyawanList = Karyawan::whereNotIn('jabatan', ['Komisaris', 'Direktur'])
                        ->whereNotIn('id', [1, 3])
                        ->where('kode_karyawan', 'not like', '%OL%')
                        ->where('status_aktif', '1')
                        ->get();

        // return $karyawanList;
        foreach ($karyawanList as $karyawan) {
            $karyawanId = $karyawan->id;

            // Periksa apakah perhitungan sudah dilakukan untuk karyawan ini
            $existingCalculation = TunjanganKaryawan::where('id_karyawan', $karyawanId)
                                    ->where('bulan', $bulan)
                                    ->where('tahun', $tahun)
                                    ->first();

            if ($existingCalculation) {
                // return redirect()->route('tunjangangenerate.index')->with(['error' => 'Tunjangan sudah dihitung otomatis untuk bulan dan tahun yang sama']);

                // return response()->json([
                //     'success' => false,
                //     'message' => 'Tunjangan untuk karyawan ini sudah dihitung untuk bulan dan tahun yang sama.'
                // ], 400);
            }

            // Ambil absensi karyawan untuk bulan tertentu
            $absensiKaryawan = AbsensiKaryawan::whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->where('id_karyawan', $karyawanId)
                ->get();

            // Hitung jumlah absensi dan keterlambatan
            $jumlahAbsensi = $absensiKaryawan->count();

            $totalSeconds = $absensiKaryawan->sum(function ($item) {
                if (!empty($item->waktu_keterlambatan) && strpos($item->waktu_keterlambatan, ':') !== false) {
                    list($hours, $minutes, $seconds) = explode(':', $item->waktu_keterlambatan);
                    return $hours * 3600 + $minutes * 60 + $seconds;
                }
                return 0;
            });

            // $excludedTunjangan = ['PPH', 'BPJS Keluarga', 'BPJS', 'BPJS Ketenagakerjaan'];
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


            // Ambil jenis tunjangan yang sesuai
            $jenisTunjangan = JenisTunjangan::where('divisi', 'All')
                ->whereIn('nama_tunjangan', $includeTunjangan) // Menggunakan whereIn untuk menyertakan tunjangan
                ->get();

            // return $jenisTunjangan;
            $cuti = pengajuancuti::where('id_karyawan', $karyawanId)
                ->whereYear('tanggal_awal', $tahun)
                ->whereMonth('tanggal_awal', $bulan)
                ->get();

            // Cek apakah ada data cuti
            if ($cuti->isNotEmpty()) {
                // Ambil durasi cuti karyawan
                $cutikaryawan = $cuti->sum('durasi');  // Menghitung total durasi cuti

                if ($cutikaryawan >= 3) {
                    $jumlahcuti = $cutikaryawan - 3;
                    $jumlahAbsensi = $jumlahAbsensi - $jumlahcuti;  // Kurangi jumlah absensi dengan sisa cuti lebih dari 3 hari
                }
            }
            // Variabel untuk total tunjangan dan potongan
            $totalTunjangan = 0;
            $totalPotongan = 0;

            foreach ($jenisTunjangan as $tunjangan) {
                if($tunjangan->nama_tunjangan == 'Lembur'){
                    $lembur = lembur::with('karyawan', 'hitunglembur')
                                ->where('id_karyawan', $karyawanId)
                                ->whereMonth('tanggal_spl', $bulan)
                                ->whereYear('tanggal_spl', $tahun)
                                ->get();
                    // dd($lembur);
                    $totalLemburan = 0;
                    // $log = [];

                    foreach($lembur as $data){
                        if($data->id_hitung_lembur == null || $data->id_hitung_lembur == ''){
                            // $log[] = 'Lewat karena id_hitung_lembur kosong/null';
                            continue;
                        }

                        if ($data->hitunglembur === null) {
                            // $log[] = 'Lewat karena relasi hitunglembur null';
                            continue;
                        }

                        $nilaiLembur = $data->hitunglembur->nilai_lembur;
                        $jamLembur = (strtotime($data->jam_selesai) - strtotime($data->jam_mulai)) / 3600;
                        $subtotal = $jamLembur * $nilaiLembur;
                        $totalLemburan += $subtotal;

                    }
                    // if($totalLembur > 0){
                        $jenisTunjangans = JenisTunjangan::where('nama_tunjangan', 'Lembur')->first();
                        $tunjanganKaryawan = new TunjanganKaryawan();
                        $tunjanganKaryawan->id_karyawan = $karyawanId;
                        $tunjanganKaryawan->bulan = $bulan;
                        $tunjanganKaryawan->tahun = $tahun;
                        $tunjanganKaryawan->jenis_tunjangan = $jenisTunjangans->id;
                        $tunjanganKaryawan->keterangan = $tunjangan->tipe;
                        // $tunjanganKaryawan->jumlah_absensi = '1';
                        $tunjanganKaryawan->total = $totalLemburan; // Pastikan nilai adalah integer (casting)
                        $tunjanganKaryawan->save();
                    // }else{

                    // }


                }else if ($tunjangan->hitung == 'Perhari' && $tunjangan->tipe == 'Tunjangan') {
                    if ($karyawan->jabatan == 'Direktur Utama') {
                        $jumlahAbsensi = AbsensiKaryawan::whereMonth('tanggal', $bulan)  // Ganti dengan bulan yang ingin dihitung
                            ->whereYear('tanggal', $tahun)  // Ganti dengan tahun yang sesuai
                            ->whereRaw('DAYOFWEEK(tanggal) NOT IN (1, 7)')  // Mengecualikan Minggu (1) dan Sabtu (7)
                            ->distinct()
                            ->count('tanggal');
                        // return $jumlahAbsensi;

                        $sebelumtigaratus = (float)$tunjangan->nilai * $jumlahAbsensi;
                        $jumlahTunjangan = $sebelumtigaratus + 300000;
                    }else {
                        // Hitung untuk karyawan lain tanpa tambahan
                        $jumlahTunjangan = $tunjangan->nilai * $jumlahAbsensi;
                    }

                    $tunjanganKaryawan = new TunjanganKaryawan();
                    $tunjanganKaryawan->id_karyawan = $karyawanId;
                    $tunjanganKaryawan->bulan = $bulan;
                    $tunjanganKaryawan->tahun = $tahun;
                    $tunjanganKaryawan->jenis_tunjangan = $tunjangan->id;
                    $tunjanganKaryawan->keterangan = $tunjangan->tipe;
                    // $tunjanganKaryawan->jumlah_absensi = $jumlahAbsensi;
                    $tunjanganKaryawan->total = (float) $jumlahTunjangan; // Pastikan nilai adalah integer (casting)
                    $tunjanganKaryawan->save();
                }



                // Jika jenis tunjangan dihitung sekali per bulan
                else if ($tunjangan->hitung == 'Perbulan' && $tunjangan->tipe == 'Tunjangan') {
                    $tunjanganKaryawan = new TunjanganKaryawan();
                    $tunjanganKaryawan->id_karyawan = $karyawanId;
                    $tunjanganKaryawan->bulan = $bulan;
                    $tunjanganKaryawan->tahun = $tahun;
                    $tunjanganKaryawan->jenis_tunjangan = $tunjangan->id;
                    $tunjanganKaryawan->keterangan = $tunjangan->tipe;
                    // $tunjanganKaryawan->jumlah_absensi = '1';
                    $tunjanganKaryawan->total = (float) $tunjangan->nilai; // Pastikan nilai adalah integer (casting)
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
        // Debugging untuk melihat semua data yang diterima
        // dd($request->all());

        // Validasi input
        $request->validate([
            'id_tunjangan' => 'required|exists:jenis_tunjangans,id',  // Memastikan id_tunjangan valid
            'karyawan_id' => 'required|array',              // Memastikan karyawan_id adalah array
            'karyawan_id.*' => 'required|numeric',           // Memastikan setiap karyawan_id adalah angka
            'nilai' => 'nullable|string|max:255',         // Memastikan nilai adalah string dan opsional
            'kelipatan' => 'nullable|string|max:255',         // Memastikan kelipatan adalah string dan opsional
            'hitung' => 'nullable|string|max:255',         // Memastikan hitung adalah string dan opsional
        ]);

        // Menentukan bulan dan tahun berdasarkan waktu saat ini
        $month = now()->month;
        $year = now()->year;

        // Jika bulan adalah Januari, set bulan Desember tahun lalu
        if ($month == 1) {
            $bulan = 12; // Desember
            $tahun = $year - 1; // Tahun sebelumnya
        } else {
            $bulan = $month - 1;
            $tahun = $year; // Untuk bulan lain, tetap bulan yang diminta
        }

        // Loop melalui setiap karyawan_id
        foreach ($request->karyawan_id as $karyawanId) {
            $jenisTunjangan = JenisTunjangan::findOrFail($request->id_tunjangan);

            // Hitung nilai berdasarkan jenis tunjangan
            if ($jenisTunjangan->nama_tunjangan == 'BPJS Keluarga') {
                $nilai = $request->nilai * $request->kelipatan; // Menghitung nilai untuk BPJS Keluarga
            } else {
                $nilai = $request->nilai; // Menggunakan nilai langsung untuk jenis tunjangan lain
            }

            // Hitung total berdasarkan metode perhitungan
            if ($request->hitung == 'Perhari') {
                $absensiKaryawan = AbsensiKaryawan::whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun)
                    ->where('id_karyawan', $karyawanId) // Menggunakan $karyawanId langsung
                    ->get();

                // Hitung jumlah absensi
                $jumlahAbsensi = $absensiKaryawan->count();
                $nilaitotal = $nilai * $jumlahAbsensi; // Total berdasarkan jumlah absensi
            } else {
                $nilaitotal = $nilai; // Total untuk perbulan
            }

            // Simpan data tunjangan karyawan
            $tunjanganKaryawan = new TunjanganKaryawan();
            $tunjanganKaryawan->id_karyawan = $karyawanId; // Menggunakan $karyawanId langsung
            $tunjanganKaryawan->bulan = $bulan;
            $tunjanganKaryawan->tahun = $tahun;
            $tunjanganKaryawan->jenis_tunjangan = $jenisTunjangan->id;
            $tunjanganKaryawan->keterangan = $jenisTunjangan->tipe;
            $tunjanganKaryawan->total = (float) $nilaitotal; // Pastikan nilai adalah float
            $tunjanganKaryawan->save(); // Simpan ke database
        }
        return redirect()->route('tunjangangenerate.index')->with(['success' => 'Data Berhasil Disimpan!']);

    }

    public function storeManual(Request $request)
    {
        // dd($request->all());
        $bulan = $request->input('bulan_tunjangan');
        $tahun = $request->input('tahun_tunjangan');
        $karyawanId = $request->input('karyawan_id');
        $dataTunjangan = $request->input('dataTunjangan');
        $deletedata = $request->input('deletedata');

        DB::beginTransaction(); // Mulai transaksi

        try {
            if($deletedata){
                foreach ($deletedata as $index => $nama_tunjangan) {
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
            if($dataTunjangan){
                foreach ($dataTunjangan as $namaTunjangan => $nilai) {
                    // Menghilangkan karakter '_' dari namaTunjangan
                    $namaTunjanganId = str_replace('_', ' ', $namaTunjangan);

                    $jenisTunjangan = JenisTunjangan::where('nama_tunjangan', $namaTunjanganId)->first();

                    if (!$jenisTunjangan) {
                        continue;
                    }

                    // Tentukan keterangan berdasarkan nilai (positif = Tunjangan, negatif = Potongan)
                    $keterangan = $nilai < 0 ? 'Potongan' : 'Tunjangan';

                    // Cek apakah tunjangan sudah ada
                    $tunjanganKaryawan = TunjanganKaryawan::where('id_karyawan', $karyawanId)
                        ->where('bulan', $bulan)
                        ->where('tahun', $tahun)
                        ->where('jenis_tunjangan', $jenisTunjangan->id)
                        ->first();

                    if ($tunjanganKaryawan) {
                        // Update data jika sudah ada
                        $tunjanganKaryawan->total = (float) $nilai;
                        $tunjanganKaryawan->save();
                    } else {
                        // Buat data baru jika belum ada
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

            // Redirect ke halaman index dengan pesan sukses
            return redirect()->route('tunjangangenerate.index')->with(['success' => 'Data Berhasil Disimpan!']);
        } catch (\Exception $e) {
            // Rollback transaksi jika ada error
            DB::rollback();
            Log::error('Failed : ' . $e->getMessage());
            // Kembalikan error
            return redirect()->route('tunjangangenerate.index')->with(['error' => 'Terjadi kesalahan, coba lagi.']);
        }
    }



    public function tunjanganExportExcel($month, $year)
    {
        if ($month == 1) {
            $bulan = 12; // Desember
            $tahun = $year - 1; // Tahun sebelumnya
        } else {
            $bulan = $month - 1;
            $tahun = $year; // Untuk bulan lain, tetap bulan yang diminta
        }

        $post = TunjanganKaryawan::where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->with('karyawan', 'jenistunjangan') // Mengambil data karyawan terkait
            ->get()
            ->groupBy('karyawan.nama_lengkap') // Mengelompokkan berdasarkan nama karyawan
            ->sortBy(function($group) {
                return $group->first()->karyawan->divisi; // Mengurutkan berdasarkan divisi
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
