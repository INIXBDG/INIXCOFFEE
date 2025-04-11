<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\AbsensiKaryawan;
use App\Models\karyawan;
use App\Models\pengajuancuti;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AbsensiKaryawanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function cekip(){
        // dd(request()->ip());
        $ip = request()->ip();
        $ipaddr = explode(".",$ip);
        // dd($ipaddr);
        if($ipaddr[0] == '192' && $ipaddr[1] == '168'){
            return response()->json(['success' => 'Absen Normal']);
        }else{
            return response()->json(['success' => 'Absen Luar']);
        }
    }
    public function create()
    {
        $user = User::with('karyawan')->where('status_akun', '1')->get();
        return view('absensi.create', compact('user'));
    }

    public function storeAbsensi(Request $request)
    {
        $shift = $request->shift;
        $sekarang = \Carbon\Carbon::now();
        $mytime = $sekarang->format('Y-m-d');
        // return $request->all();
        // Cek keterangan absen
        if($request->keterangan == '-' || $request->keterangan == null){
            return response()->json(['error' => 'Anda belum Memilih Jenis Absen.'], 400);
        }

        // Dapatkan jabatan dari input atau user yang sedang login
        $jabatan = $request->input('jabatan') ?? auth()->user()->jabatan;

        // Validasi jabatan yang diterima
        if (!$jabatan) {
            return response()->json(['error' => 'Jabatan tidak ditemukan.'], 400);
        }

        // Cek apakah sudah absen hari ini
        $existingRecord = AbsensiKaryawan::where('id_karyawan', $request->input('id_karyawan'))
                                        ->where('tanggal', $mytime)
                                        ->first();
        
        if ($existingRecord) {
            return response()->json(['error' => 'Anda telah Absen sebelumnya.'], 400);
        }

        // Proses penyimpanan foto
        $imageData = $request->input('foto');
        
        // Validasi apakah foto yang dikirim sesuai format yang diharapkan
        if (!$imageData || strpos($imageData, 'data:image/jpeg;base64,') === false) {
            return response()->json(['error' => 'Foto tidak valid. Harus berupa base64 image.'], 400);
        }

        // Proses simpan gambar
        $image = str_replace('data:image/jpeg;base64,', '', $imageData);
        $image = str_replace(' ', '+', $image);
        $imageName = time() . '.jpeg';
        $filePath = 'absensi/' . $imageName;
        
        // Coba simpan gambar, tangani error jika gagal
        try {
            Storage::put('public/' . $filePath, base64_decode($image));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal menyimpan foto.'], 500);
        }

        // Mendapatkan waktu jam masuk
        try {
            $jamMasuk = \Carbon\Carbon::createFromFormat('H:i:s', $sekarang->toTimeString());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Format jam masuk tidak valid. Harus dalam format H:i:s.'], 400);
        }

        $this->validate($request, [
            'foto' => 'required',
            'shift' => 'required',
            'keterangan' => 'required',
            'id_karyawan' => 'required|integer',
            'jam_masuk' => 'required|date_format:H:i:s',
        ]);

        $hari = \Carbon\Carbon::now()->dayOfWeek;
        $waktuKeterlambatanFormatted = '00:00:00';
        $keterangan = 'Masuk';

        // Konfigurasi shift berdasarkan jabatan
        switch ($jabatan) {
            
            case 'Office Boy':
                if ($hari == 6 || $hari == 7) {
                    // Shift times for Saturday and Sunday
                    $jamAwal = \Carbon\Carbon::createFromTimeString('00:00:01');
                    $jamBatasShift1Awal = \Carbon\Carbon::createFromTimeString('05:01:00'); // Shift 1 starts at 5:00 AM
                    $jamBatasShift1Akhir = \Carbon\Carbon::createFromTimeString('08:00:00');
                    $jamBatasShift2Awal = \Carbon\Carbon::createFromTimeString('11:01:00'); // Shift 2 starts at 10:00 AM
                    $jamBatasShift2Akhir = \Carbon\Carbon::createFromTimeString('23:50:59');
                } else {
                    // Shift times for Monday to Friday
                    $jamAwal = \Carbon\Carbon::createFromTimeString('00:00:01');
                    $jamBatasShift1Awal = \Carbon\Carbon::createFromTimeString('05:01:00');
                    $jamBatasShift1Akhir = \Carbon\Carbon::createFromTimeString('10:00:00');
                    $jamBatasShift2Awal = \Carbon\Carbon::createFromTimeString('16:01:00');
                    $jamBatasShift2Akhir = \Carbon\Carbon::createFromTimeString('23:50:59');
                }
                
                if ($jamMasuk->between($jamAwal, $jamBatasShift1Akhir) && $shift == '1') {
                    if ($jamMasuk->greaterThan($jamBatasShift1Awal)) {
                        $waktuKeterlambatan = $jamMasuk->diffInMinutes($jamBatasShift1Awal);
                        $hours = intdiv($waktuKeterlambatan, 60);
                        $minutes = ($waktuKeterlambatan % 60) + 1 ;
                        $waktuKeterlambatanFormatted = sprintf('%02d:%02d:00', $hours, $minutes);
                        $keterangan = 'Telat (' . $request->keterangan . ')';
                    } else {
                        $keterangan = 'Masuk (' . $request->keterangan . ')';
                    }
                } elseif ($jamMasuk->between($jamBatasShift1Awal, $jamBatasShift2Akhir) && $shift == '2') {
                    if ($jamMasuk->greaterThan($jamBatasShift2Awal)) {
                        $waktuKeterlambatan = $jamMasuk->diffInMinutes($jamBatasShift2Awal);
                        $hours = intdiv($waktuKeterlambatan, 60);
                        $minutes = ($waktuKeterlambatan % 60) + 1 ;
                        $waktuKeterlambatanFormatted = sprintf('%02d:%02d:00', $hours, $minutes);
                        $keterangan = 'Telat (' . $request->keterangan . ')';
                    } else {
                        $keterangan = 'Masuk (' . $request->keterangan . ')';
                    }
                } else {
                    return response()->json(['error' => 'Shift tidak sesuai untuk Office Boy.'], 400);
                }
            break;
            
            case 'Technical Support':
                if ($hari >= 1 && $hari <= 5) { // Senin - Jumat
                    $jamBatasAwal = \Carbon\Carbon::createFromTimeString('08:01:00');
                    $jamBatasAkhir = \Carbon\Carbon::createFromTimeString('17:00:00');
                } elseif ($hari == 6 || $hari == 7) { // Sabtu dan Minggu
                    $jamBatasAwal = \Carbon\Carbon::createFromTimeString('09:01:00');
                    $jamBatasAkhir = \Carbon\Carbon::createFromTimeString('16:00:00');
                }
                $jamAwal = \Carbon\Carbon::createFromTimeString('00:00:01');
            
                if ($jamMasuk->between($jamAwal, $jamBatasAkhir)) {
                    if ($jamMasuk->greaterThan($jamBatasAwal)) {
                        $waktuKeterlambatan = $jamMasuk->diffInMinutes($jamBatasAwal);
                        $hours = intdiv($waktuKeterlambatan, 60);
                        $minutes = ($waktuKeterlambatan % 60) + 1 ;
                        $waktuKeterlambatanFormatted = sprintf('%02d:%02d:00', $hours, $minutes);
                        $keterangan = 'Telat (' . $request->keterangan . ')';
                    } else {
                        $keterangan = 'Masuk (' . $request->keterangan . ')';
                    }
                } else {
                    return response()->json(['error' => 'Jam masuk tidak sesuai shift.'], 400);
                }
                break;
            
            default: 
                $jamAwal = \Carbon\Carbon::createFromTimeString('00:00:01');
                $jamBatasAwal = \Carbon\Carbon::createFromTimeString('08:01:00');
                $jamBatasAkhir = \Carbon\Carbon::createFromTimeString('16:00:00');

                if ($jamMasuk->between($jamAwal, $jamBatasAkhir)) {
                    if ($jamMasuk->greaterThan($jamBatasAwal)) {
                        $waktuKeterlambatan = $jamMasuk->diffInMinutes($jamBatasAwal);
                        $hours = intdiv($waktuKeterlambatan, 60);
                        $minutes = ($waktuKeterlambatan % 60) + 1 ;
                        $waktuKeterlambatanFormatted = sprintf('%02d:%02d:00', $hours, $minutes);
                        $keterangan = 'Telat (' . $request->keterangan . ')';
                    } else {
                        $keterangan = 'Masuk (' . $request->keterangan . ')';
                    }
                } else {
                    return response()->json(['error' => 'Jam masuk tidak sesuai shift.'], 400);
                }
            break;
        }

        // Simpan absensi
        $absensi = new AbsensiKaryawan();
        $absensi->id_karyawan = $request->input('id_karyawan');
        $absensi->tanggal = $mytime;
        $absensi->jam_masuk = $jamMasuk;
        $absensi->foto = $filePath;
        $absensi->keterangan = $keterangan;
        $absensi->waktu_keterlambatan = $waktuKeterlambatanFormatted;
        $absensi->save();

        return response()->json(['success' => 'Terimakasih Absen anda berhasil disimpan. Selamat Bekerja!']);
    }

    public function absenManual(Request $request)
    {
        // return $request->all();
        // Cek keterangan absen
        if($request->keterangan == '-' || $request->keterangan == null){
            return response()->json(['error' => 'Anda belum Memilih Jenis Absen.'], 400);
        }

        // Dapatkan jabatan dari input atau user yang sedang login
        $jabatan = $request->input('jabatan') ?? auth()->user()->jabatan;

        // Validasi jabatan yang diterima
        if (!$jabatan) {
            return response()->json(['error' => 'Jabatan tidak ditemukan.'], 400);
        }
        
        // Gunakan tanggal saat ini
        $sekarang = \Carbon\Carbon::now();
        $mytime = $sekarang->format('Y-m-d');

        // Cek apakah sudah absen hari ini
        $existingRecord = AbsensiKaryawan::where('id_karyawan', $request->input('id_karyawan'))
                                        ->where('tanggal', $request->input('tanggal'))
                                        ->first();
        
        if ($existingRecord) {
            return response()->json(['error' => 'Anda telah Absen sebelumnya.'], 400);
        }

        // Proses penyimpanan foto
        $imageData = $request->input('foto');
        
        // Validasi apakah foto yang dikirim sesuai format yang diharapkan
        if (!$imageData || strpos($imageData, 'data:image/jpeg;base64,') === false) {
            return response()->json(['error' => 'Foto tidak valid. Harus berupa base64 image.'], 400);
        }

        // Proses simpan gambar
        $image = str_replace('data:image/jpeg;base64,', '', $imageData);
        $image = str_replace(' ', '+', $image);
        $imageName = time() . '.jpeg';
        $filePath = 'absensi/' . $imageName;
        
        // Coba simpan gambar, tangani error jika gagal
        try {
            Storage::put('public/' . $filePath, base64_decode($image));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal menyimpan foto.'], 500);
        }

        $this->validate($request, [
            'foto' => 'required',
            'shift' => 'required',
            'keterangan' => 'required',
            'id_karyawan' => 'required|integer',
            'jam_masuk' => 'required|date_format:H:i:s',
        ]);

        // Simpan absensi
        $absensi = new AbsensiKaryawan();
        $absensi->id_karyawan = $request->input('id_karyawan');
        $absensi->tanggal = $request->input('tanggal');
        $absensi->jam_masuk = $request->input('jam_masuk');
        $absensi->foto = $filePath;
        $absensi->keterangan = $request->input('keterangan');
        $absensi->waktu_keterlambatan = $request->input('waktu_keterlambatan');
        $absensi->save();

        return view('absensi.index')->with(['success' => 'Terimakasih Absen anda berhasil disimpan. Selamat Bekerja!']);
    }

    public function index()
    {
        return view('suratperjalanan.index');
    }

    public function absensiKaryawan()
    {
        $id_karyawan = auth()->user()->karyawan_id;
        $month = now()->month; // Mendapatkan bulan saat ini
        $absen = AbsensiKaryawan::where('id_karyawan', $id_karyawan)
                    ->whereMonth('tanggal', $month)
                    ->orderBy('tanggal', 'asc') // Urutkan berdasarkan tanggal secara ascending (terkecil ke terbesar)
                    ->get();
        // Ambil leaderboard karyawan berdasarkan waktu keterlambatan
        $leaderboard = AbsensiKaryawan::select(
                    'id_karyawan',
                    DB::raw('SUM(TIME_TO_SEC(waktu_keterlambatan)) as total_keterlambatan'),
                    DB::raw('MAX(TIME_TO_SEC(waktu_keterlambatan)) as highest_keterlambatan'), // Fetch max lateness
                    DB::raw('MIN(foto) as foto')
                )
                ->with('karyawan') // Load karyawan relationship to get employee details like name if needed
                ->whereMonth('tanggal', $month)
                ->whereHas('karyawan', function($query) {
                    $query->whereNotIn('jabatan', ['Office boy', 'Driver']);
                })
                ->groupBy('id_karyawan') // Group by id_karyawan only, to aggregate multiple records per employee
                ->orderBy('total_keterlambatan', 'desc')
                ->limit(10) // Limit results to top 10 employees
                ->get();

                $leaderboard->each(function ($item) {
                    // Ambil record yang memiliki highest_keterlambatan untuk karyawan ini
                    $recordWithHighestLateness = AbsensiKaryawan::where('id_karyawan', $item->id_karyawan)
                        ->where(DB::raw('TIME_TO_SEC(waktu_keterlambatan)'), $item->highest_keterlambatan)
                        ->orderBy('tanggal', 'asc') // Jika ada beberapa record dengan highest keterlambatan, ambil yang paling awal
                        ->first();
                
                    // Set foto dari record tersebut ke dalam item leaderboard
                    $item->foto = $recordWithHighestLateness->foto ?? null;
                });

        // Convert total and highest lateness times to HH:MM:SS format and filter out employees with zero lateness
        $leaderboard = $leaderboard->filter(function ($item) {
        // Convert total_keterlambatan to HH:MM:SS
        $hoursketerlambatan = floor($item->total_keterlambatan / 3600);
        $minutesketerlambatan = floor(($item->total_keterlambatan % 3600) / 60);
        $secondsketerlambatan = $item->total_keterlambatan % 60;
        $item->total_keterlambatan = sprintf('%02d:%02d:%02d', $hoursketerlambatan, $minutesketerlambatan, $secondsketerlambatan);

        // Convert highest_keterlambatan to HH:MM:SS
        $hourstinggi = floor($item->highest_keterlambatan / 3600);
        $minutestinggi = floor(($item->highest_keterlambatan % 3600) / 60);
        $secondstinggi = $item->highest_keterlambatan % 60;
        $item->highest_keterlambatan = sprintf('%02d:%02d:%02d', $hourstinggi, $minutestinggi, $secondstinggi);

        // Only include if either total_keterlambatan or highest_keterlambatan is not 00:00:00
        return $item->total_keterlambatan !== '00:00:00' || $item->highest_keterlambatan !== '00:00:00';
        })->values(); // Reset keys on the filtered collection

    
        $topKaryawan = $leaderboard->take(3);
        $remainingLeaderboard = $leaderboard->slice(3)->values();


        $totalketerlambatan = AbsensiKaryawan::select('id_karyawan', DB::raw('SUM(TIME_TO_SEC(waktu_keterlambatan)) as total_keterlambatan'))        
                ->whereMonth('tanggal', $month)
                ->where('id_karyawan', auth()->user()->karyawan_id)
                ->groupBy('id_karyawan')
                ->orderBy('total_keterlambatan', 'desc')
                ->with('karyawan')
                ->first();
                if ($totalketerlambatan) {
                    $totalSeconds = $totalketerlambatan->total_keterlambatan;
                
                    // Menghitung jam, menit, dan detik
                    $hours = floor($totalSeconds / 3600);
                    $minutes = floor(($totalSeconds % 3600) / 60);
                    $seconds = $totalSeconds % 60;
                
                    // Format ke dalam string manusiawi
                    $formattedTime = '';
                    if ($hours > 0) {
                        $formattedTime .= $hours . ' jam ';
                    }
                    if ($minutes > 0) {
                        $formattedTime .= $minutes . ' menit ';
                    }
                    if ($seconds > 0) {
                        $formattedTime .= $seconds . ' detik';
                    }
                    
                    // Set formatted time ke dalam objek
                    $totalketerlambatan->total_keterlambatan = $formattedTime;
                }
            // return $totalketerlambatan;
            // return $leaderboard;

        return view('absensi.absensi', compact('absen', 'leaderboard', 'totalketerlambatan', 'topKaryawan', 'remainingLeaderboard'));
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'shift' => 'required',
            'keterangan_pulang' => 'required',
            'id_karyawan' => 'required|integer',
        ], [
            'shift.required' => json_encode(['error' => 'Shift harus diisi.']),
            'keterangan_pulang.required' => json_encode(['error' => 'Keterangan pulang harus diisi.']),
            // 'id_karyawan.required' => json_encode(['error' => 'ID karyawan harus diisi.']),
            // 'id_karyawan.integer' => json_encode(['error' => 'ID karyawan harus berupa angka.']),
        ]);
        

        // dd($request->all());

        // Ambil waktu sekarang
        $sekarang = \Carbon\Carbon::now();
        $tanggal = $sekarang->toDateString();  // Mengambil bagian tanggal
        $jamKeluar = $sekarang; // Gunakan Carbon instance untuk jam keluar

        // Batasan waktu absen pulang untuk hari biasa
        $jamBatasAwal = \Carbon\Carbon::createFromTimeString('14:00:00');
        $jamBatasAkhir = \Carbon\Carbon::createFromTimeString('23:59:59');
        $jamBatasKeesokanHari = \Carbon\Carbon::createFromTimeString('00:00:00');
        $jamBatasAkhirKeesokanHari = \Carbon\Carbon::createFromTimeString('09:00:00');

        // Batasan waktu absen pulang khusus untuk Sabtu dan Minggu
        $jamBatasAwalAkhirPekan = \Carbon\Carbon::createFromTimeString('11:00:00');
        $jamBatasAkhirAkhirPekan = \Carbon\Carbon::createFromTimeString('23:00:00');
        $jamBatasKeesokanHariAkhirPekan = \Carbon\Carbon::createFromTimeString('00:00:00');
        $jamBatasAkhirKeesokanHariAkhirPekan = \Carbon\Carbon::createFromTimeString('10:00:00'); // Batas waktu keesokan hari khusus akhir pekan

        $shift = $request->shift;

        if($request->keterangan_pulang == ''){
            return response()->json(['error' => 'Pilih dahulu tipa absensi nya.'], 400);
        }
        // Validasi shift
        if (!in_array($shift, ['1', '2'])) {
            return response()->json(['error' => 'Shift tidak valid.'], 400);
        }

        // Cari absensi berdasarkan shift
        $absensi = null;
        if ($shift == '2') {
            // Jika shift 2, cek absensi kemarin
            $kemarin = \Carbon\Carbon::yesterday()->format('Y-m-d');
            $absensi = AbsensiKaryawan::where('id_karyawan', $request->input('id_karyawan'))
                ->where('tanggal', $kemarin)
                ->first();
        } elseif ($shift == '1') {
            $absensi = AbsensiKaryawan::where('id_karyawan', $request->input('id_karyawan'))
                ->where('tanggal', $tanggal)
                ->first();
        }

        if (!$absensi) {
            return response()->json(['error' => 'Absen masuk tidak ditemukan atau anda belum absen hari ini.'], 404);
        }

        // Cek apakah jam_keluar sudah diisi
        if ($absensi->jam_keluar) {
            return response()->json(['error' => 'Anda sudah mengisi absen pulang!'], 400);
        }

        // Logika absensi pulang
        $isAllowedTime = false;
        $isWeekend = $sekarang->isWeekend(); // Cek apakah hari ini adalah Sabtu atau Minggu

        // Logika untuk shift 1 (absen pulang pada hari yang sama)
        if ($shift == '1') {
            if ($isWeekend) {
                // Jika Sabtu atau Minggu, gunakan batasan waktu khusus akhir pekan
                $isAllowedTime = $jamKeluar->between($jamBatasAwalAkhirPekan, $jamBatasAkhirAkhirPekan);
            } else {
                // Hari biasa
                $isAllowedTime = $jamKeluar->between($jamBatasAwal, $jamBatasAkhir);
            }
        }

        // Logika untuk shift 2 (absen pulang di hari berikutnya)
        if ($shift == '2') {
            if ($isWeekend) {
                // Jika Sabtu atau Minggu, gunakan batasan waktu khusus akhir pekan untuk hari berikutnya
                $isAllowedTime = $jamKeluar->between($jamBatasKeesokanHariAkhirPekan, $jamBatasAkhirKeesokanHariAkhirPekan);
            } else {
                // Hari biasa
                $isAllowedTime = $jamKeluar->between($jamBatasKeesokanHari, $jamBatasAkhirKeesokanHari);
            }
        }

        // Cek jika absen sebelum waktu yang diizinkan untuk shift 1
        if ($shift == '1' && !$isAllowedTime && $jamKeluar->lessThan($isWeekend ? $jamBatasAwalAkhirPekan : $jamBatasAwal)) {
            return response()->json(['error' => 'Absen tidak sesuai. Anda tidak dapat absen sebelum jam ' . ($isWeekend ? $jamBatasAwalAkhirPekan : $jamBatasAwal)->format('H:i') . '.'], 400);
        }

        // Cek jika absen sebelum waktu yang diizinkan untuk shift 2
        if ($shift == '2' && !$isAllowedTime && $jamKeluar->lessThan($isWeekend ? $jamBatasKeesokanHariAkhirPekan : $jamBatasKeesokanHari)) {
            return response()->json(['error' => 'Absen tidak sesuai. Anda tidak dapat absen sebelum jam ' . ($isWeekend ? $jamBatasKeesokanHariAkhirPekan : $jamBatasKeesokanHari)->format('H:i') . '.'], 400);
        }

        // Jika waktu diizinkan, simpan absensi pulang
        if ($isAllowedTime) {
            $keterangan_pulang = 'Pulang ('.$request->keterangan_pulang.')';
            $absensi->keterangan_pulang = $keterangan_pulang;
            $absensi->jam_keluar = $jamKeluar->toTimeString(); // Gunakan waktu sekarang
            // dd($absensi);
            $absensi->save();
            return response()->json(['success' => 'Terimakasih telah bekerja hari ini! Hati-hati di jalan.']);
        }

        // Pesan error jika belum waktunya absen pulang
        return response()->json(['error' => 'Belum bisa absen pulang. Waktu absen pulang minimal jam ' . ($isWeekend ? $jamBatasAwalAkhirPekan : $jamBatasAwal)->format('H:i') . '.'], 400);
    }

    public function jumlahAbsensi($karyawanId, $bulan, $tahun) {  
        // Mengambil data absensi karyawan berdasarkan bulan dan tahun  
        $absensiKaryawan = AbsensiKaryawan::whereMonth('tanggal', $bulan)  
            ->whereYear('tanggal', $tahun)  
            ->where('id_karyawan', $karyawanId)  
            ->get();  
        $cutis = pengajuancuti::where('id_karyawan', $karyawanId)  
            ->whereYear('tanggal_awal', $tahun)  
            ->whereMonth('tanggal_awal', $bulan)  
            ->get();  
  
       // Inisialisasi jumlahAbsensi
        if ($karyawanId == '2') {
            // Menggunakan distinct count untuk employee ID '2'
            $jumlahAbsensi = AbsensiKaryawan::whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->whereRaw('DAYOFWEEK(tanggal) NOT IN (1, 7)') // Mengecualikan Minggu (1) dan Sabtu (7)
                ->distinct('tanggal')
                ->count();

                // dd($jumlahAbsensi);
        } else {
            $jumlahAbsensi = $absensiKaryawan->count();
        }

        $karyawan = karyawan::findOrFail($karyawanId);  
          
        // Inisialisasi total durasi cuti dan izin    
        $totalCuti = 0;    
        $totalizin = 0;    
        
        if ($cutis->isNotEmpty()) {          
            foreach ($cutis as $cuti) {          
                if ($cuti->tipe == 'Izin') {          
                    $totalizin += $cuti->durasi; // Menjumlahkan durasi izin      
                } else {    
                    $totalCuti += $cuti->durasi; // Menjumlahkan durasi cuti          
                }      
            }        
          
            // Tambahkan totalCuti ke jumlahAbsensi terlebih dahulu  
            $jumlahAbsensi += $totalCuti; 
            // dd($jumlahAbsensi);

            // Kemudian kurangi totalizin dari jumlahAbsensi jika totalizin >= 1  
            if ($totalizin >= 1) {        
                $jumlahAbsensi -= $totalizin; // Kurangi totalizin dari jumlahAbsensi        
            }          
        }   
        
         
        // Hitung total keterlambatan dalam detik jika ada data absensi  
        $totalSeconds = $absensiKaryawan->sum(function ($item) {  
            if (!empty($item->waktu_keterlambatan) && strpos($item->waktu_keterlambatan, ':') !== false) {  
                list($hours, $minutes, $seconds) = explode(':', $item->waktu_keterlambatan);  
                return $hours * 3600 + $minutes * 60 + $seconds;  
            }  
            return 0;  
        });  
      
        // Kondisi jika total keterlambatan lebih dari 0  
        if ($totalSeconds > 0) {  
            if ($totalSeconds > 900) {  
                $keterangan = "Terlambat > 15 menit";  
            } else {  
                $keterangan = "Terlambat " . floor($totalSeconds / 60) . " menit";  
            }  
        } else {  
            $keterangan = "Tidak pernah terlambat";  
        }  
      
        return response()->json([  
            'success' => true,  
            'message' => 'Jumlah Absen ' . $bulan . '-' . $tahun,  
            'data' => [  
                'jumlah_absensi' => $jumlahAbsensi ?? 0, // Menangani kondisi null  
                'keterangan' => $keterangan,  
                'cutikaryawan' => $cutis ?? [], // Jika tidak ada data cuti, kirimkan array kosong  
                'karyawan' => $karyawan ?? [], // Jika tidak ada data cuti, kirimkan array kosong  
            ],  
        ]);  
    }  
    
    
    

}
