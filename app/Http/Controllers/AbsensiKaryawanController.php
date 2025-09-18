<?php

namespace App\Http\Controllers;

use App\Models\absensi_noRecord;
use App\Notifications\noRecordExchangeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\AbsensiKaryawan;
use App\Models\izinTigaJam;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use App\Models\karyawan;
use App\Models\pembatalanCuti;
use App\Models\pengajuancuti;
use App\Models\User;
use App\Notifications\cancelLeaveExchangeNotification;
use App\Notifications\schemeWorkExchangeNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;


class AbsensiKaryawanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function cekip()
    {
        // dd(request()->ip());
        $ip = request()->ip();
        $ipaddr = explode(".", $ip);
        // dd($ipaddr);
        if ($ipaddr[0] == '192' && $ipaddr[1] == '168') {
            return response()->json(['success' => 'Absen Normal']);
        } else {
            return response()->json(['success' => 'Absen Luar']);
        }
    }
    public function create()
    {
        $user = User::with('karyawan')->where('status_akun', '1')->get();
        return view('absensi.create', compact('user'));
    }

<<<<<<< HEAD
=======
    public function storeAbsensi(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'shift' => 'required|in:1,2',
            'keterangan' => 'required|string',
            'id_karyawan' => 'required|integer',
            'foto' => 'required|string',
            'client_time' => 'sometimes|date'
        ], [
            'keterangan.required' => 'Jenis absen harus dipilih',
            'foto.required' => 'Foto absen wajib diambil'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Log waktu client-server
        $this->logTimeDiscrepancy($request);

        // Persiapan data
        $sekarang = Carbon::now('Asia/Jakarta');
        $jabatan = $request->input('jabatan') ?? auth()->user()->jabatan;

        // Validasi duplikasi absen
        if ($jabatan !== 'Office Boy' && $this->checkDuplicateAbsen($request->id_karyawan, $sekarang->toDateString())) {
            return response()->json(['error' => 'Anda sudah melakukan absen masuk hari ini'], 400);
        }

        // Proses foto
        $fotoPath = $this->processFoto($request->foto);
        if (!$fotoPath) {
            return response()->json(['error' => 'Gagal menyimpan foto absen'], 500);
        }

        // Validasi shift dan waktu
        $validationResult = $this->validateShiftWaktu($sekarang, $request->shift, $jabatan);
        if (!$validationResult['valid']) {
            return response()->json(['error' => $validationResult['message']], 400);
        }

        // Simpan data
        $absensi = AbsensiKaryawan::create([
            'id_karyawan' => $request->id_karyawan,
            'tanggal' => $sekarang->toDateString(),
            'jam_masuk' => $sekarang->toTimeString(),
            'foto' => $fotoPath,
            'keterangan' => $validationResult['keterangan'],
            'waktu_keterlambatan' => $validationResult['keterlambatan'],
            'shift' => $request->shift
        ]);

        return response()->json([
            'success' => 'Absen masuk berhasil',
            'data' => [
                'jam_masuk' => $sekarang->format('H:i:s'),
                'keterangan' => $validationResult['keterangan']
            ]
        ]);
    }

    private function logTimeDiscrepancy(Request $request)
    {
        if ($request->client_time) {
            Log::channel('absensi')->info('Time check masuk', [
                'server' => now('Asia/Jakarta')->toDateTimeString(),
                'client' => $request->client_time,
                'diff_seconds' => now('Asia/Jakarta')->diffInSeconds($request->client_time)
            ]);
        }
    }

    private function checkDuplicateAbsen($idKaryawan, $tanggal)
    {
        return AbsensiKaryawan::where('id_karyawan', $idKaryawan)
            ->where('tanggal', $tanggal)
            ->exists();
    }

    private function processFoto($imageData)
    {
        try {
            if (strpos($imageData, 'data:image/jpeg;base64,') === false) {
                return false;
            }

            $image = str_replace('data:image/jpeg;base64,', '', $imageData);
            $image = str_replace(' ', '+', $image);
            $imageName = 'absensi_' . time() . '.jpeg';
            $filePath = 'absensi/' . $imageName;

            Storage::put('public/' . $filePath, base64_decode($image));
            return $filePath;
        } catch (\Exception $e) {
            Log::error('Error proses foto: ' . $e->getMessage());
            return false;
        }
    }

    private function validateShiftWaktu($waktu, $shift, $jabatan)
    {
        $config = $this->getShiftConfig($waktu->dayOfWeek, $jabatan, $shift);
        $isWeekend = ($waktu->dayOfWeek == Carbon::SATURDAY || $waktu->dayOfWeek == Carbon::SUNDAY);


        $id_karyawan = auth()->user()->karyawan_id;

        $izinHariIni = izinTigaJam::where('id_karyawan', $id_karyawan)
            ->whereDate('tanggal_pengajuan', $waktu->toDateString())
            ->where('approval', 2)
            ->first();

        if (!$waktu->between($config['jamAwal'], $config['jamAkhir'])) {
            return [
                'valid' => false,
                'message' => 'Waktu absen tidak sesuai shift. Jam kerja: ' .
                    $config['jamAwal']->format('H:i') . ' - ' .
                    $config['jamAkhir']->format('H:i')
            ];
        }

    if ($isWeekend && !in_array($jabatan, ['Office Boy', 'Technical Support'])) {
        return [
            'valid' => true,
            'keterangan' => 'Masuk',
            'keterlambatan' => '00:00:00'
        ];
    }

        // Hitung keterlambatan untuk jabatan lain dan hari selain weekend
        $keterlambatan = '00:00:00';
        $keterangan = 'Masuk';
      if ($izinHariIni) {
    // Jam mulai izin dari database
    $izinMulai = Carbon::parse($izinHariIni->jam_mulai);
    $batasAwalMasuk = $izinMulai->copy()->addHour(); // 1 jam setelah izin
    $batasAkhirMasuk = $izinMulai->copy()->addHours(3); // 3 jam setelah izin

    if ($waktu->lessThan($batasAwalMasuk)) {
        // Terlalu cepat
        return [
            'valid' => false,
            'message' => 'Anda belum bisa absen. Minimal pukul ' . $batasAwalMasuk->format('H:i')
        ];
    } elseif ($waktu->greaterThan($batasAkhirMasuk)) {
        // Telat lebih dari 3 jam
        $diffMinutes = $waktu->diffInMinutes($batasAkhirMasuk);
        $hours = intdiv($diffMinutes, 60);
        $minutes = ($diffMinutes % 60);
        $keterlambatan = sprintf('%02d:%02d:00', $hours, $minutes);
        $keterangan = 'Telat (izin 3 Jam)';
    } else {
        // Masuk sesuai izin
        $keterangan = 'Masuk (Izin 3 Jam)';
        $keterlambatan = '00:00:00';
    }

} elseif ($waktu->greaterThan($config['jamMulaiShift'])) {
    // Telat normal
    $diffMinutes = $waktu->diffInMinutes($config['jamMulaiShift']);
    $hours = intdiv($diffMinutes, 60);
    $minutes = ($diffMinutes % 60);
    $keterlambatan = sprintf('%02d:%02d:00', $hours, $minutes);
    $keterangan = 'Telat';
} else {
    // Masuk tepat waktu
    $keterangan = 'Masuk';
    $keterlambatan = '00:00:00';
}

    return [
        'valid' => true,
        'keterangan' => $keterangan,
        'keterlambatan' => $keterlambatan
    ];
}



    private function getShiftConfig($dayOfWeek, $jabatan, $shift)
    {
        $isWeekend = ($dayOfWeek == Carbon::SATURDAY || $dayOfWeek == Carbon::SUNDAY);

        // Konfigurasi default
        $config = [
            'jamAwal' => Carbon::createFromTimeString('00:00:00'),
            'jamAkhir' => Carbon::createFromTimeString('23:59:59'),
            'jamMulaiShift' => Carbon::createFromTimeString('08:00:00')
        ];

        // Penyesuaian berdasarkan jabatan dan shift
        switch ($jabatan) {
            case 'Office Boy':
                if ($isWeekend) {
                    $config['jamMulaiShift'] = $shift == 1
                        ? Carbon::createFromTimeString('05:00:00')
                        : Carbon::createFromTimeString('11:00:00');
                } else {
                    $config['jamMulaiShift'] = $shift == 1
                        ? Carbon::createFromTimeString('05:00:00')
                        : Carbon::createFromTimeString('16:00:00');
                }
                break;

            case 'Technical Support':
                $config['jamMulaiShift'] = $isWeekend
                    ? Carbon::createFromTimeString('09:00:00')
                    : Carbon::createFromTimeString('08:00:00');
                $config['jamAkhir'] = $isWeekend
                    ? Carbon::createFromTimeString('16:00:00')
                    : Carbon::createFromTimeString('17:00:00');
                break;
        }

        return $config;
    }

>>>>>>> 1b16b0915417b914bb03ff291c20d9b95894c711
    public function absenManual(Request $request)
    {
        // return $request->all();
        // Cek keterangan absen
        if ($request->keterangan == '-' || $request->keterangan == null) {
            return response()->json(['error' => 'Anda belum Memilih Jenis Absen.'], 400);
        }

        // Dapatkan jabatan dari input atau user yang sedang login
        $jabatan = $request->input('jabatan') ?? auth()->user()->jabatan;

        // Validasi jabatan yang diterima
        if (!$jabatan) {
            return response()->json(['error' => 'Jabatan tidak ditemukan.'], 400);
        }

        // Gunakan tanggal saat ini
        $sekarang = Carbon::now();
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
        $month = now()->month;
<<<<<<< HEAD
        $year = now()->year;
=======
>>>>>>> 1b16b0915417b914bb03ff291c20d9b95894c711
        $absen = AbsensiKaryawan::where('id_karyawan', $id_karyawan)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->orderBy('tanggal', 'asc') // Urutkan berdasarkan tanggal secara ascending (terkecil ke terbesar)
            ->get();

        $karyawan = karyawan::where('id', $id_karyawan)->first();
        $noRecord = absensi_noRecord::where('id_karyawan', auth()->user()->karyawan_id)
            ->where('jenis_PK', 'No Record')
            // ->where('approval', 1)
            ->get();


        $schemeWork = absensi_noRecord::where('id_karyawan', $id_karyawan)
            ->where('jenis_PK', 'Scheme Work')
            ->whereHas('absensiKaryawan')
            ->with('absensiKaryawan')
            ->get();

        $cancelLeave = pembatalanCuti::where('id_karyawan', $id_karyawan)
            ->whereHas('pengajuancuti')
            ->with('pengajuancuti')
            ->get();

        $leaderboard = AbsensiKaryawan::select(
            'id_karyawan',
            DB::raw('SUM(TIME_TO_SEC(waktu_keterlambatan)) as total_keterlambatan'),
            DB::raw('MAX(TIME_TO_SEC(waktu_keterlambatan)) as highest_keterlambatan'), // Fetch max lateness
            DB::raw('MIN(foto) as foto')
        )
            ->with('karyawan') // Load karyawan relationship to get employee details like name if needed
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->whereHas('karyawan', function ($query) {
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
            ->whereYear('tanggal', $year)
            ->where('id_karyawan', auth()->user()->karyawan_id) // Menggunakan Auth::user()
            ->groupBy('id_karyawan')
            ->orderBy('total_keterlambatan', 'desc')
            ->first(); // Tidak perlu with('karyawan') jika hanya mengambil total_keterlambatan

        // Jika tidak ada data keterlambatan, atau totalnya 0
        if (!$totalketerlambatan || $totalketerlambatan->total_keterlambatan == '0') {
            $formattedTime = '0 menit'; // Set ke '0 menit'
        } else {
            $totalSeconds = (int)$totalketerlambatan->total_keterlambatan; // Pastikan ini integer

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

            // Hapus spasi ekstra di akhir jika ada
            $formattedTime = trim($formattedTime);
        }

        // return $leaderboard;

        // Tambahkan query izin tiga jam
        $izinTigaJam = izinTigaJam::where('id_karyawan', $id_karyawan)
            ->whereMonth('tanggal_pengajuan', $month)
            ->orderBy('tanggal_pengajuan', 'desc')
            ->get();

        return view('absensi.absensi', compact(
            'absen',
            'leaderboard',
            'totalketerlambatan',
            'topKaryawan',
            'remainingLeaderboard',
            'noRecord',
            'schemeWork',
            'cancelLeave',
            'izinTigaJam' // <-- tambahkan ini
        ));
    }

<<<<<<< HEAD
    
=======
    public function update(Request $request)
    {
        $this->validate($request, [
            'keterangan_pulang' => 'required',
            'id_karyawan' => 'required|integer',
        ]);

        // Ambil waktu sekarang
        $sekarang = \Carbon\Carbon::now();
        $jamKeluar = $sekarang->copy(); // Gunakan Carbon instance untuk jam keluar
        $jamKeluarTime = $jamKeluar->format('H:i:s');
        // return $jamKeluar;
        // Tentukan tanggal absensi: jika sebelum jam 08:00:00, gunakan tanggal kemarin
        $tanggal = ($jamKeluar->lt(\Carbon\Carbon::createFromTimeString('10:00:00')))
            ? $sekarang->copy()->subDay()->toDateString()
            : $sekarang->toDateString();
        // return $tanggal;
        // Ambil absensi berdasarkan tanggal yang dihitung
        $absensi = AbsensiKaryawan::where('id_karyawan', $request->input('id_karyawan'))
            ->where('tanggal', $tanggal)
            ->latest()
            ->first();

        if (!$absensi) {
            return response()->json(['error' => 'Absen masuk tidak ditemukan untuk tanggal: ' . $tanggal], 404);
        }

        // Cek apakah sudah absen pulang
        if ($absensi->jam_keluar) {
            return response()->json(['error' => 'Anda sudah mengisi absen pulang!'], 400);
        }

        // Simpan absen pulang
        $keterangan_pulang = 'Pulang (' . $request->keterangan_pulang . ')';
        $absensi->keterangan_pulang = $keterangan_pulang;
        $absensi->jam_keluar = $jamKeluarTime;
        $absensi->save();

        return response()->json(['success' => 'Terimakasih telah bekerja hari ini! Hati-hati di jalan.']);
    }
>>>>>>> 1b16b0915417b914bb03ff291c20d9b95894c711


    private function getAbsensiMasuk($idKaryawan, $tanggal)
    {
        return AbsensiKaryawan::where('id_karyawan', $idKaryawan)
            ->where('tanggal', $tanggal)
            ->whereNull('jam_keluar')
            ->first();
    }


    public function jumlahAbsensi($karyawanId, $bulan, $tahun)
    {
        // Mengambil data absensi karyawan berdasarkan bulan dan tahun  
        $absensiKaryawan = AbsensiKaryawan::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->Where('jam_keluar', '!=', '')
            ->where('id_karyawan', $karyawanId)
            ->get();
        $absen_pulang = AbsensiKaryawan::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->Where('jam_keluar', '=', null)
            ->where('id_karyawan', $karyawanId)
            ->get();
        $cutis = pengajuancuti::where('id_karyawan', $karyawanId)
            ->whereYear('tanggal_awal', $tahun)
            ->whereMonth('tanggal_awal', $bulan)
            ->get();
        // dd($absensiKaryawan);

        // Inisialisasi jumlahAbsensi
        if ($karyawanId == '2') {
            // Menggunakan distinct count untuk employee ID '2'
            $jumlahAbsensi = AbsensiKaryawan::whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->whereRaw('DAYOFWEEK(tanggal) NOT IN (1, 7)') // Mengecualikan Minggu (1) dan Sabtu (7)
                ->distinct('tanggal')
                ->count();
        } else {
            $jumlahAbsensi = $absensiKaryawan->count();
            $jumlahAbsensiPulang = $absen_pulang->count();
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
                'jumlah_tidak_absen_pulang' => $jumlahAbsensiPulang ?? 0,
                'cutikaryawan' => $cutis ?? [], // Jika tidak ada data cuti, kirimkan array kosong  
                'karyawan' => $karyawan ?? [], // Jika tidak ada data cuti, kirimkan array kosong  
            ],
        ]);
    }

    // -----------------------------------------------------------------
    // 1️⃣ ABsen Masuk
    // -----------------------------------------------------------------
    public function storeMasuk(Request $request)
    {
<<<<<<< HEAD
        $validator = Validator::make($request->all(), [
            'shift'      => 'required|in:1,2',
            'keterangan' => 'required|string',
            'id_karyawan'=> 'required|integer',
            'foto'       => 'required|string',
            'jabatan'    => 'required|string',
            'client_time'=> 'sometimes|date',
=======
        $user = auth()->user()->karyawan_id;
        $karyawan = karyawan::findOrFail($user);

        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $data_absen = AbsensiKaryawan::where('id_karyawan', $user)
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->get();

        $karyawanall = karyawan::where('divisi', '!=', 'Direksi')
            ->where('divisi', $karyawan->divisi)
            ->get();

        return view('absensi.klaim', compact('karyawan', 'karyawanall', 'data_absen'));
    }

    public function createNoRecord(Request $request)
    {
        $this->validate($request, [
            'id_karyawan'   => 'required|integer',
            'kendala'       => 'required|string|in:Human Error,System Error',
            'tanggal_absen' => 'required|date',
            'bukti_gambar'  => 'required|image',
            'kronologi'     => 'required|string',
>>>>>>> 1b16b0915417b914bb03ff291c20d9b95894c711
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $this->logTimeDiscrepancy($request);

        $now = Carbon::now('Asia/Jakarta');
        $jabatan = $request->input('jabatan');

        // Cek **masuk** sudah ada atau belum (hanya periksa jam_masuk)
        $exists = AbsensiKaryawan::where('id_karyawan', $request->id_karyawan)
            ->whereDate('tanggal', $now->toDateString())
            ->whereNotNull('jam_masuk')
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'Anda sudah absen masuk hari ini'], 400);
        }

        $fotoPath = $this->processFoto($request->foto);
        if (!$fotoPath) {
            return response()->json(['error' => 'Gagal menyimpan foto'], 500);
        }

<<<<<<< HEAD
        $shiftCheck = $this->validateShiftWaktu($now, $request->shift, $jabatan);
        if (!$shiftCheck['valid']) {
            return response()->json(['error' => $shiftCheck['message']], 400);
        }

        $absensi = AbsensiKaryawan::create([
            'id_karyawan' => $request->id_karyawan,
            'tanggal'     => $now->toDateString(),
            'jam_masuk'   => $now->toTimeString(),
            'foto'  => $fotoPath,
            'keterangan'  => $shiftCheck['keterangan'],
            'waktu_keterlambatan' => $shiftCheck['keterlambatan'],
            // 'shift'       => $request->shift,
            // 'jabatan'     => $jabatan,
=======
        absensi_noRecord::create([
            'id_karyawan'   => $request->id_karyawan,
            'jenis_PK'      => 'No Record',
            'kendala'       => $request->kendala,
            'id_absen'      => '0',
            'bukti_gambar'  => $fotoPath,
            'kronologi'     => $request->kronologi,
            'approval'      => '0',
>>>>>>> 1b16b0915417b914bb03ff291c20d9b95894c711
        ]);

        return response()->json([
            'success' => 'Absen masuk berhasil',
            'data'    => [
                'jam_masuk' => $now->format('H:i:s'),
                'keterangan'=> $shiftCheck['keterangan'],
                'foto'      => $fotoPath,
            ]
        ], 201);
    }

    // -----------------------------------------------------------------
    // 2️⃣ ABsen Keluar
    // -----------------------------------------------------------------
    public function storeKeluar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_karyawan'=> 'required|integer',
            'client_time'=> 'sometimes|date',
        ]);
        

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $this->logTimeDiscrepancy($request);

        $now = Carbon::now('Asia/Jakarta');
        $dayOfWeek = $now->dayOfWeek; // Ini mengembalikan 1 (Senin) sampai 7 (Minggu)

        // Cari record hari ini yang **sudah** absen masuk tapi belum keluar
        $absensi = AbsensiKaryawan::where('id_karyawan', $request->id_karyawan)
            ->whereDate('tanggal', $now->toDateString())
            ->whereNotNull('jam_masuk')
            ->whereNull('jam_keluar')
            ->first();

        if (!$absensi) {
            return response()->json(['error' => 'Tidak ada absen masuk untuk hari ini'], 404);
        }

        // Optional: validasi jam keluar berada dalam shift yang sama
        $shiftConfig = $this->getShiftConfig(
            $dayOfWeek,     // ✅ Hari ini (integer 1–7)
            auth()->user()->jabatan,
            $request->shift
        );

        // -----------------------------------------------------------------
        // 5️⃣ Cek apakah waktu keluar berada dalam “periode diizinkan”
        // -----------------------------------------------------------------
        // Misalnya, $isAllowedTime = true bila jam keluar berada di antara
        // jamAwal dan jamAkhir shift (bisa dipakai untuk logika tambahan)
        // $batas = Carbon::parse('14:00:00');          // ubah menjadi objek Carbon

        // // Jika konfigurasi shift memiliki jamAkhir, gunakan itu, bila tidak pakai $now
        // $jamAkhir = isset($shiftConfig['jamAkhir'])
        //     ? Carbon::parse($shiftConfig['jamAkhir'])
        //     : $now;                                   // fallback, tidak akan pernah false

        // $isAllowedTime = $now->between($batas, $jamAkhir);

        // // ---- Shift 1 ----------------------------------------------------
        // if ($request->shift == '1' && !$isAllowedTime) {
        //     return response()->json([
        //         'error' => 'Anda tidak dapat absen pulang sebelum jam '
        //                 . $batas->format('H:i') . '.',
        //     ], 400);
        // }


        // Jika jam keluar diluar jam akhir shift, beri peringatan atau set status "pulang terlambat"
        $keluarValid = $now->lessThanOrEqualTo($shiftConfig['jamAkhir']);
        if (!$keluarValid) {
            // Misalnya, tetap simpan tapi beri keterangan
            $keteranganKeluar = 'Pulang';
        } else {
            $keteranganKeluar = 'Pulang';
        }

        $absensi->update([
            'jam_keluar'   => $now->toTimeString(),
            'keterangan_pulang' => $keteranganKeluar,
        ]);

        return response()->json([
            'success' => 'Terimakasih telah bekerja hari ini! Hati-hati di jalan.',
            'data'    => [
                'jam_keluar' => $now->format('H:i:s'),
                'keterangan'=> $keteranganKeluar,
            ]
        ]);
    }

    // -----------------------------------------------------------------
    // 3️⃣ Helper‑helper yang sama (log, foto, shift, dll)
    // -----------------------------------------------------------------
    private function logTimeDiscrepancy(Request $request)
    {
        if ($request->client_time) {
            Log::channel('absensi')->info('Time check', [
                'server' => now('Asia/Jakarta')->toDateTimeString(),
                'client' => $request->client_time,
                'diff_s' => now('Asia/Jakarta')->diffInSeconds($request->client_time)
            ]);
        }
    }

    private function processFoto(string $imageData): ?string
    {
        try {
            if (!str_contains($imageData, 'data:image')) {
                return null;
            }

            // Ambil tipe dan data base64
            [$meta, $base64] = explode(',', $imageData, 2);
            $extension = match (true) {
                str_contains($meta, 'jpeg') => 'jpeg',
                str_contains($meta, 'png')  => 'png',
                default                     => 'jpg',
            };

            $fileName = 'absensi_' . uniqid() . '.' . $extension;
            $path = "absensi/{$fileName}";
            Storage::put('public/' . $path, base64_decode($base64));

            return $path;
        } catch (\Throwable $e) {
            Log::error('Proses foto gagal: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Mengembalikan konfigurasi shift (jam mulai, jam akhir, jam mulai shift)
     * Hasilnya **Carbon** yang sudah di‑set ke timezone Asia/Jakarta
     */
    private function getShiftConfig(int $dayOfWeek, string $jabatan, int $shift): array
    {
        $isWeekend = in_array($dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]);

        // Default standar kantor (Senin‑Jumat)
        $config = [
            'jamAwal'      => Carbon::createFromTimeString('08:00:00', 'Asia/Jakarta'),
            'jamAkhir'     => Carbon::createFromTimeString('17:00:00', 'Asia/Jakarta'),
            'jamMulaiShift'=> Carbon::createFromTimeString('08:00:00', 'Asia/Jakarta'),
        ];

        switch ($jabatan) {
            case 'Office Boy':
                if ($isWeekend) {
                    // Weekend shift Office Boy
                    $config['jamMulaiShift'] = $shift == 1
                        ? Carbon::createFromTimeString('05:00:00', 'Asia/Jakarta')
                        : Carbon::createFromTimeString('17:00:00', 'Asia/Jakarta');
                    $config['jamAwal'] = $config['jamMulaiShift'];
                    $config['jamAkhir'] = $config['jamMulaiShift']->copy()->addHours(12);
                } else {
                    // Weekday shift Office Boy
                    $config['jamMulaiShift'] = $shift == 1
                        ? Carbon::createFromTimeString('05:00:00', 'Asia/Jakarta')
                        : Carbon::createFromTimeString('17:00:00', 'Asia/Jakarta');
                    $config['jamAwal'] = $config['jamMulaiShift'];
                    $config['jamAkhir'] = $config['jamMulaiShift']->copy()->addHours(12);
                }
                break;

            case 'Driver':
                // Driver hanya satu shift (08‑17) semua hari
                $config['jamMulaiShift'] = Carbon::createFromTimeString('08:00:00', 'Asia/Jakarta');
                $config['jamAwal'] = $config['jamMulaiShift'];
                $config['jamAkhir'] = Carbon::createFromTimeString('17:00:00', 'Asia/Jakarta');
                break;

            case 'Technical Support':
                if ($isWeekend) {
                    // Weekend Technical Support 09‑16
                    $config['jamMulaiShift'] = Carbon::createFromTimeString('09:00:00', 'Asia/Jakarta');
                    $config['jamAwal'] = $config['jamMulaiShift'];
                    $config['jamAkhir'] = Carbon::createFromTimeString('16:00:00', 'Asia/Jakarta');
                } else {
                    // Weekday Technical Support 08‑17
                    $config['jamMulaiShift'] = Carbon::createFromTimeString('08:00:00', 'Asia/Jakarta');
                    $config['jamAwal'] = $config['jamMulaiShift'];
                    $config['jamAkhir'] = Carbon::createFromTimeString('17:00:00', 'Asia/Jakarta');
                }
                break;

            default:
                // Karyawan reguler (sen‑jum 08‑17)
                $config['jamMulaiShift'] = Carbon::createFromTimeString('08:00:00', 'Asia/Jakarta');
                $config['jamAwal'] = $config['jamMulaiShift'];
                $config['jamAkhir'] = Carbon::createFromTimeString('17:00:00', 'Asia/Jakarta');
                break;
        }

        return $config;
    }

    /**
     * Validasi jam masuk terhadap konfigurasi shift.
     * Mengembalikan array: valid|keterangan|keterlambatan|message
     */
    private function validateShiftWaktu($waktu, $shift, $jabatan)
    {
        $config = $this->getShiftConfig($waktu->dayOfWeek, $jabatan, $shift);
        $isWeekend = ($waktu->dayOfWeek == Carbon::SATURDAY || $waktu->dayOfWeek == Carbon::SUNDAY);


        $id_karyawan = auth()->user()->karyawan_id;

        $izinHariIni = izinTigaJam::where('id_karyawan', $id_karyawan)
            ->whereDate('tanggal_pengajuan', $waktu->toDateString())
            ->where('approval', 2)
            ->first();

        if (!$waktu->between($config['jamAwal'], $config['jamAkhir'])) {
            return [
                'valid' => false,
                'message' => 'Waktu absen tidak sesuai shift. Jam kerja: ' .
                    $config['jamAwal']->format('H:i') . ' - ' .
                    $config['jamAkhir']->format('H:i')
            ];
        }

        if ($isWeekend && !in_array($jabatan, ['Office Boy', 'Technical Support'])) {
            return [
                'valid' => true,
                'keterangan' => 'Masuk',
                'keterlambatan' => '00:00:00'
            ];
        }

        // Hitung keterlambatan untuk jabatan lain dan hari selain weekend
        $keterlambatan = '00:00:00';
        $keterangan = 'Masuk';
      if ($izinHariIni) {
        // Jam mulai izin dari database
        $izinMulai = Carbon::parse($izinHariIni->jam_mulai);
        $batasAwalMasuk = $izinMulai->copy()->addHour(); // 1 jam setelah izin
        $batasAkhirMasuk = $izinMulai->copy()->addHours(3); // 3 jam setelah izin

        if ($waktu->lessThan($batasAwalMasuk)) {
            // Terlalu cepat
            return [
                'valid' => false,
                'message' => 'Anda belum bisa absen. Minimal pukul ' . $batasAwalMasuk->format('H:i')
            ];
        } elseif ($waktu->greaterThan($batasAkhirMasuk)) {
            // Telat lebih dari 3 jam
            $diffMinutes = $waktu->diffInMinutes($batasAkhirMasuk);
            $hours = intdiv($diffMinutes, 60);
            $minutes = ($diffMinutes % 60);
            $keterlambatan = sprintf('%02d:%02d:00', $hours, $minutes);
            $keterangan = 'Telat (izin 3 Jam)';
        } else {
            // Masuk sesuai izin
            $keterangan = 'Masuk (Izin 3 Jam)';
            $keterlambatan = '00:00:00';
        }

        } elseif ($waktu->greaterThan($config['jamMulaiShift'])) {
            // Telat normal
            $diffMinutes = $waktu->diffInMinutes($config['jamMulaiShift']);
            $hours = intdiv($diffMinutes, 60);
            $minutes = ($diffMinutes % 60);
            $keterlambatan = sprintf('%02d:%02d:00', $hours, $minutes);
            $keterangan = 'Telat';
        } else {
            // Masuk tepat waktu
            $keterangan = 'Masuk';
            $keterlambatan = '00:00:00';
        }

            return [
                'valid' => true,
                'keterangan' => $keterangan,
                'keterlambatan' => $keterlambatan
            ];
    }
<<<<<<< HEAD
=======

    public function createCancelLeave(Request $request)
    {
        $this->validate($request, [
            'id_karyawan'   => 'required|integer',
            'tanggal_cuti' => 'required|integer',
            'bukti_gambar'  => 'required|image',
            'kronologi'     => 'required|string',
        ]);

        if ($request->kendala === 'Human Error') {
            $jumlahHE = absensi_noRecord::where('id_karyawan', $request->id_karyawan)
                ->where('kendala', 'Human Error')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            if ($jumlahHE >= 3) {
                return back()->withErrors(['kendala' => 'Pengajuan dengan kendala "Human Error" hanya diperbolehkan maksimal 3 kali dalam sebulan.'])->withInput();
            }
        }

        $file = $request->file('bukti_gambar');
        $ext = $file->getClientOriginalExtension();
        $filename = 'bukti_' . now()->format('Y_m_d_H_i_s') . '.' . $ext;
        $destinationPath = public_path('pengajuan_klaim');

        $file->move($destinationPath, $filename);

        $fotoPath = 'pengajuan_klaim/' . $filename;

        if (!$fotoPath) {
            return back()->withErrors(['bukti_gambar' => 'Tidak dapat melampirkan bukti'])->withInput();
        }

        $data_cuti = pengajuancuti::where('id', $request->tanggal_cuti)->first();

        pembatalanCuti::create([
            'id_karyawan'   => $request->id_karyawan,
            'id_cuti'       => $request->tanggal_cuti,
            'bukti_gambar'  => $fotoPath,
            'kronologi'     => $request->kronologi,
            'approval'      => '0',
            'tipe'          => $data_cuti->tipe,
            'tanggal_awal'  => $data_cuti->tanggal_awal,
            'tanggal_akhir' => $data_cuti->tanggal_akhir,
            'durasi'        => $data_cuti->durasi,
            'kontak'        => $data_cuti->kontak,
            'alasan'        => $data_cuti->alasan,
            'surat_sakit'   => $data_cuti->surat_sakit,
        ]);

        $karyawan = karyawan::find($request->id_karyawan);
        $hrd = karyawan::where('jabatan', 'HRD')->first();

        $kodePenerima = [];

        if ($hrd) {
            $kodePenerima[] = $hrd->kode_karyawan;
        }

        if ($karyawan) {
            $kodePenerima[] = $karyawan->kode_karyawan;
        }

        $users = User::whereHas('karyawan', function ($query) use ($kodePenerima) {
            $query->whereIn('kode_karyawan', $kodePenerima);
        })->get();

        $approval = 0;
        $statusMessage = "Menunggu Persetujuan HRD";

        if ($approval === 0) {
            $statusMessage = "Menunggu Persetujuan HRD";
        }

        $notificationData = [
            'tipe'            => 'cancel_leave',
            'nama_lengkap'    => $karyawan->nama_lengkap,
            'kronologi'       => $request->kronologi,
            'jenis'           => $data_cuti->tipe,
            'tanggal_awal'    => $data_cuti->tanggal_awal,
            'tanggal_akhir'   => $data_cuti->tanggal_akhir,
            'status'          => $statusMessage,
            'durasi'          => $data_cuti->durasi,
            'alasan'          => $data_cuti->alasan,
            'approval'        => 0,
            'alasan_approval' => null,
        ];

        $path = '/absensi/karyawan?page=cancel_leave';

        foreach ($users as $user) {
            NotificationFacade::send($user, new cancelLeaveExchangeNotification($notificationData, $path));
        }

        return redirect('/absensi/karyawan?page=cancel_leave')->with('success', 'Berhasil mengajukan');
    }

    public function approveCancelLeave(Request $request)
    {
        $this->validate($request, [
            'approval'       => 'required|integer|in:1,2',
            'id_CL'       => 'required|integer',
            'id_karyawan'    => 'required|integer',
        ]);

        $jenis_PK = pembatalanCuti::where('id_karyawan', $request->id_karyawan)
            ->where('id', $request->id_CL)
            ->first();

        if (!$jenis_PK) {
            return redirect()->back()->withErrors('Data tidak ditemukan.');
        }

        $jenis_PK->approval = $request->approval;
        if ($request->filled('alasan_approval')) {
            $jenis_PK->alasan_approval = $request->alasan_approval;
        }
        $jenis_PK->approval_date = now();
        $jenis_PK->save();

        if ($request->approval === 1) {
            $deletingData = pengajuancuti::where('id', $jenis_PK->id_cuti)->first();
            $deletingData->delete();
        }

        $absen = AbsensiKaryawan::where('id', $jenis_PK->id_karyawan)->first();

        $karyawan = karyawan::find($request->id_karyawan);
        $hrd = karyawan::where('jabatan', 'HRD')->first();

        $kodePenerima = [];

        if ($hrd) {
            $kodePenerima[] = $hrd->kode_karyawan;
        }

        if ($karyawan) {
            $kodePenerima[] = $karyawan->kode_karyawan;
        }

        $users = User::whereHas('karyawan', function ($query) use ($kodePenerima) {
            $query->whereIn('kode_karyawan', $kodePenerima);
        })->get();

        $data_cuti = pengajuancuti::where('id', $request->id_CL)->first();

        $statusMessage = $request->approval == 1 ? "Telah Disetujui Oleh HRD" : "Telah Ditolak Oleh HRD";

        $notificationData = [
            'tipe'            => 'cancel_leave',
            'nama_lengkap'    => $karyawan->nama_lengkap,
            'kronologi'       => $request->kronologi,
            'jenis'           => $data_cuti->tipe,
            'tanggal_awal'    => $data_cuti->tanggal_awal,
            'tanggal_akhir'   => $data_cuti->tanggal_akhir,
            'status'          => $statusMessage,
            'durasi'          => $data_cuti->durasi,
            'alasan'          => $data_cuti->alasan,
            'approval'        => 0,
            'alasan_approval' => null,
        ];

        $path = '/absensi/karyawan?page=cancel_leave';

        foreach ($users as $user) {
            NotificationFacade::send($user, new cancelLeaveExchangeNotification($notificationData, $path));
        }

        return redirect('/absensi/karyawan?page=cancel_leave')->with('success', 'Berhasil memproses data absensi.');
    }

    public function deleteCancelLeave(Request $request)
    {
        $this->validate($request, [
            'id_cancel_leave'       => 'required|integer',
        ]);
        $cancelLeave = pembatalanCuti::find($request->id_cancel_leave);
        $cancelLeave->delete();

        return redirect('/absensi/karyawan?page=cancel_leave')->with('success', 'Data Berhasil Dihapus');
    }
    public function cancelLeave()
    {
        $user = auth()->user()->karyawan_id;
        $karyawan = karyawan::findOrFail($user);

        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $data_cuti = pengajuancuti::where('id_karyawan', $user)
            ->where('approval_manager', '1')
            ->whereBetween('tanggal_awal', [$startOfMonth, $endOfMonth])
            ->get();

        $karyawanall = karyawan::where('divisi', '!=', 'Direksi')
            ->where('divisi', $karyawan->divisi)
            ->get();

        return view('absensi.pembatalancuti', compact('karyawan', 'karyawanall', 'data_cuti'));
    }
>>>>>>> 1b16b0915417b914bb03ff291c20d9b95894c711
}