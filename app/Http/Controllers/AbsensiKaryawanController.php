<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\AbsensiKaryawan;
use App\Models\karyawan;
use App\Models\pengajuancuti;
use App\Models\User;
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
        $sekarang = \Carbon\Carbon::now('Asia/Jakarta');
        $jabatan = $request->input('jabatan') ?? auth()->user()->jabatan;

        // Validasi duplikasi absen
        if ($this->checkDuplicateAbsen($request->id_karyawan, $sekarang->toDateString())) {
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

        if (!$waktu->between($config['jamAwal'], $config['jamAkhir'])) {
            return [
                'valid' => false,
                'message' => 'Waktu absen tidak sesuai shift. Jam kerja: ' .
                    $config['jamAwal']->format('H:i') . ' - ' .
                    $config['jamAkhir']->format('H:i')
            ];
        }

        // Hitung keterlambatan
        $keterlambatan = '00:00:00';
        $keterangan = 'Masuk';

        if ($waktu->greaterThan($config['jamMulaiShift'])) {
            $diffMinutes = $waktu->diffInMinutes($config['jamMulaiShift']);
            $hours = intdiv($diffMinutes, 60);
            $minutes = ($diffMinutes % 60);
            $keterlambatan = sprintf('%02d:%02d:00', $hours, $minutes);
            $keterangan = 'Telat';
        }

        return [
            'valid' => true,
            'keterangan' => $keterangan,
            'keterlambatan' => $keterlambatan
        ];
    }

    private function getShiftConfig($dayOfWeek, $jabatan, $shift)
    {
        $isWeekend = ($dayOfWeek == \Carbon\Carbon::SATURDAY || $dayOfWeek == \Carbon\Carbon::SUNDAY);

        // Konfigurasi default
        $config = [
            'jamAwal' => \Carbon\Carbon::createFromTimeString('00:00:00'),
            'jamAkhir' => \Carbon\Carbon::createFromTimeString('23:59:59'),
            'jamMulaiShift' => \Carbon\Carbon::createFromTimeString('08:00:00')
        ];

        // Penyesuaian berdasarkan jabatan dan shift
        switch ($jabatan) {
            case 'Office Boy':
                if ($isWeekend) {
                    $config['jamMulaiShift'] = $shift == 1
                        ? \Carbon\Carbon::createFromTimeString('05:00:00')
                        : \Carbon\Carbon::createFromTimeString('11:00:00');
                } else {
                    $config['jamMulaiShift'] = $shift == 1
                        ? \Carbon\Carbon::createFromTimeString('05:00:00')
                        : \Carbon\Carbon::createFromTimeString('16:00:00');
                }
                break;

            case 'Technical Support':
                $config['jamMulaiShift'] = $isWeekend
                    ? \Carbon\Carbon::createFromTimeString('09:00:00')
                    : \Carbon\Carbon::createFromTimeString('08:00:00');
                $config['jamAkhir'] = $isWeekend
                    ? \Carbon\Carbon::createFromTimeString('16:00:00')
                    : \Carbon\Carbon::createFromTimeString('17:00:00');
                break;
        }

        return $config;
    }

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
        // Validasi input
        $validator = Validator::make($request->all(), [
            'shift' => 'required|in:1,2',
            'keterangan_pulang' => 'required|string',
            'id_karyawan' => 'required|integer',
            'jabatan' => 'required|string', // Tambahkan validasi untuk jabatan
            'client_time' => 'sometimes|date' // Untuk logging
        ], [
            'shift.required' => 'Shift harus diisi',
            'shift.in' => 'Shift hanya boleh 1 atau 2',
            'keterangan_pulang.required' => 'Keterangan pulang harus diisi',
            'jabatan.required' => 'Jabatan harus diisi'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Log perbedaan waktu client-server
        if ($request->client_time) {
            Log::channel('absensi')->info('Time check', [
                'server' => now('Asia/Jakarta')->toDateTimeString(),
                'client' => $request->client_time,
                'diff' => now('Asia/Jakarta')->diffInSeconds($request->client_time)
            ]);
        }

        // Waktu sekarang dengan timezone
        $sekarang = \Carbon\Carbon::now('Asia/Jakarta');
        $tanggal = $sekarang->toDateString();
        $jamKeluar = $sekarang;

        // Konfigurasi jam kerja
        $jadwal = $this->getJadwalKerja($sekarang->isWeekend(), $request->shift);

        // Cek absensi masuk untuk hari ini
        $absensi = $this->getAbsensiMasuk($request->id_karyawan, $request->shift, $tanggal);

        // Jika absen masuk tidak ditemukan dan jabatan adalah Office Boy
        if (!$absensi && $request->jabatan === 'Office Boy') {
            // Cek absensi hari sebelumnya
            $tanggalSebelumnya = $sekarang->copy()->subDay()->toDateString();
            $absensiSebelumnya = $this->getAbsensiMasuk($request->id_karyawan, $request->shift, $tanggalSebelumnya);

            // Jika absensi hari sebelumnya ada dan jam_masuk kosong
            if ($absensiSebelumnya && is_null($absensiSebelumnya->jam_masuk)) {
                // Update absensi hari sebelumnya dengan jam masuk (misalnya dianggap masuk sore hari sebelumnya)
                $absensiSebelumnya->update([
                    'jam_masuk' => $sekarang->copy()->subDay()->setTime(16, 0, 0)->toTimeString(), // Contoh: masuk jam 16:00
                    'keterangan_masuk' => 'Masuk (Shift Malam)'
                ]);

                // Buat absensi baru untuk hari ini hanya dengan jam_keluar
                $absensi = AbsensiKaryawan::create([
                    'id_karyawan' => $request->id_karyawan,
                    'tanggal' => $tanggal,
                    'shift' => $request->shift,
                    'jam_keluar' => $jamKeluar->toTimeString(),
                    'keterangan_pulang' => 'Pulang (' . $request->keterangan_pulang . ')'
                ]);
            } else {
                // Jika tidak ada absensi sebelumnya atau jam_masuk tidak kosong
                return response()->json(['error' => 'Absen masuk tidak ditemukan'], 404);
            }
        } elseif (!$absensi) {
            // Jika bukan Office Boy dan absen masuk tidak ditemukan
            return response()->json(['error' => 'Absen masuk tidak ditemukan'], 404);
        }

        // Validasi waktu
        if (!$this->validateWaktuAbsen($jamKeluar, $jadwal)) {
            return response()->json([
                'error' => 'Waktu absen tidak valid. Jam yang diperbolehkan: ' .
                    $jadwal['awal']->format('H:i') . ' - ' .
                    $jadwal['akhir']->format('H:i')
            ], 400);
        }

        // Simpan absensi
        $absensi->update([
            'jam_keluar' => $jamKeluar->toTimeString(),
            'keterangan_pulang' => 'Pulang (' . $request->keterangan_pulang . ')'
        ]);

        return response()->json([
            'success' => 'Absen pulang berhasil',
            'data' => [
                'jam_keluar' => $jamKeluar->format('H:i:s'),
                'tanggal' => $tanggal
            ]
        ]);
    }

    private function getJadwalKerja($isWeekend, $shift)
    {
        if ($shift == 1) {
            return [
                'awal' => \Carbon\Carbon::createFromTimeString($isWeekend ? '11:00:00' : '14:00:00'),
                'akhir' => \Carbon\Carbon::createFromTimeString($isWeekend ? '23:00:00' : '23:59:59')
            ];
        }

        return [
            'awal' => \Carbon\Carbon::createFromTimeString($isWeekend ? '00:00:00' : '00:00:00'),
            'akhir' => \Carbon\Carbon::createFromTimeString($isWeekend ? '10:00:00' : '09:00:00')
        ];
    }

    private function getAbsensiMasuk($idKaryawan, $shift, $tanggal)
    {
        $query = AbsensiKaryawan::where('id_karyawan', $idKaryawan);

        if ($shift == 2) {
            $tanggal = \Carbon\Carbon::parse($tanggal)->subDay()->toDateString();
        }

        return $query->where('tanggal', $tanggal)
            ->whereNull('jam_keluar')
            ->first();
    }

    private function validateWaktuAbsen($waktu, $jadwal)
    {
        return $waktu->between($jadwal['awal'], $jadwal['akhir']);
    }

    public function jumlahAbsensi($karyawanId, $bulan, $tahun)
    {
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

    public function noRecord()
    {
        $user = auth()->user()->karyawan_id;
        $karyawan = karyawan::findOrFail($user);
        $karyawanall = karyawan::where('divisi', '!=', 'Direksi')->where('divisi', $karyawan->divisi)->get();
        return view('absensi.klaim', compact('karyawan', 'karyawanall'));
    }

    public function getNoRecord()
    {
        $user = auth()->user()->karyawan_id;
    }
}
