<?php

namespace App\Http\Controllers;

use App\Exports\RekapitulasiAbsenperBulanExport;
use App\Models\AbsensiKaryawan;
use App\Models\karyawan;
use App\Models\Peserta;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RekapitulasiAbsenperKaryawanExport;
use App\Exports\RekapitulasiWaktuKeterlambatanExport;
use App\Models\pengajuancuti;
use App\Models\SuratPerjalanan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RekapitulasiAbsenController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        $auth = auth()->user();
        $jabatan = $auth->jabatan;
        if($jabatan == "HRD" || $jabatan == "Koordinator Office" ){
            $peserta = karyawan::all();
            return view('absensi.index', compact('peserta'));
        }else{
            abort(404);
        }
        
    }

    public function getAbsen(Request $request)
    {
        $id_karyawan = $request->input('id_karyawan');
        $tahun = $request->input('tahun');
        $bulan = $request->input('bulan');

        // Get the absences sorted by 'tanggal' in descending order
        $absensi = AbsensiKaryawan::where('id_karyawan', $id_karyawan)
                                ->whereYear('tanggal', $tahun)
                                ->whereMonth('tanggal', $bulan)
                                ->orderBy('tanggal', 'asc') // Sort by date (tanggal) in descending order
                                ->get();

        // Sum all the waktu_keterlambatan in seconds
        $totalSeconds = $absensi->sum(function ($item) {
            list($hours, $minutes, $seconds) = explode(':', $item->waktu_keterlambatan);
            return $hours * 3600 + $minutes * 60 + $seconds;
        });

        // Convert the total seconds back to HH:MM:SS format
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;

        // Format the time in HH:MM:SS
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

        return response()->json([
            'success' => true,
            'message' => 'Data Absensi Karyawan',
            'data' => $absensi,
            'total_keterlambatan' => $formattedTime,
        ]);
    }


    public function exportperKaryawan(Request $request, $year, $month)
    {
        $id_karyawan = $request->input('id_karyawan');
        $tahun = $request->input('tahun');
        $bulan = $request->input('bulan');

        // Mengambil data absensi
        $absensi = AbsensiKaryawan::with('karyawan')->where('id_karyawan', $id_karyawan)
                                    ->whereYear('tanggal', $year)
                                    ->whereMonth('tanggal', $month)
                                    ->get();

        // Mengambil data cuti
        $cuti = pengajuancuti::where('id_karyawan', $id_karyawan)
                                ->whereYear('tanggal_awal', $year)
                                ->whereMonth('tanggal_awal', $month)
                                ->where('approval_manager', '1')
                                ->get();

        $spj = SuratPerjalanan::with('karyawan')
                                ->where('id_karyawan', $id_karyawan)
                                ->whereYear('tanggal_berangkat', $tahun)
                                ->whereMonth('tanggal_berangkat', $bulan)
                                ->where('approval_hrd', '1')
                                ->get();

        // Menghitung total keterlambatan (jika ada)
        $totalSeconds = $absensi->sum(function ($item) {
            list($hours, $minutes, $seconds) = explode(':', $item->waktu_keterlambatan);
            return $hours * 3600 + $minutes * 60 + $seconds;
        });

        // Konversi total detik menjadi format HH:MM:SS
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;

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

        // Memformat data absensi
        $absensiData = $absensi->map(function ($item) use ($formattedTime) {
            return [
                'nama_karyawan' => $item->karyawan->nama_lengkap ?? null,
                'tanggal' => $item->tanggal,
                'jam_masuk' => $item->jam_masuk,
                'jam_keluar' => $item->jam_keluar,
                'keterangan' => $item->keterangan,
                'keterangan_pulang' => $item->keterangan_pulang,
                'waktu_keterlambatan' => $item->waktu_keterlambatan,
            ];
        })->toArray();

        $cutiData = $cuti->map(function ($item) {
            // Menggunakan Carbon untuk tanggal awal dan tanggal akhir
            $tanggalAwal = \Carbon\Carbon::parse($item->tanggal_awal);
            $tanggalAkhir = \Carbon\Carbon::parse($item->tanggal_akhir);
            
            // Membuat array untuk menyimpan rentang tanggal
            $cutiRange = [];
        
            // Loop dari tanggal_awal sampai tanggal_akhir
            for ($date = $tanggalAwal; $date->lte($tanggalAkhir); $date->addDay()) {
                // Cek apakah hari bukan Sabtu (6) atau Minggu (0)
                if (!$date->isWeekend()) {
                    // Setiap tanggal selain Sabtu atau Minggu dimasukkan ke array
                    $cutiRange[] = [
                        'nama_karyawan' => $item->karyawan->nama_lengkap ?? null,
                        'tanggal' => $date->toDateString(), // Menyimpan tanggal saat ini dalam iterasi
                        'jam_masuk' => null,
                        'jam_keluar' => null,
                        'keterangan' => $item->tipe,  // Keterangan Cuti
                        'waktu_keterlambatan' => null,
                    ];
                }
            }
        
            return $cutiRange; // Mengembalikan rentang tanggal untuk setiap cuti tanpa Sabtu dan Minggu
        })->flatten(1)->toArray(); // Flatten array agar tidak bersarang terlalu dalam
        
        $spjData = $spj->map(function ($item) {
            $tanggalAwal = \Carbon\Carbon::parse($item->tanggal_berangkat);
            $tanggalAkhir = \Carbon\Carbon::parse($item->tanggal_pulang);
            $spjRange = [];
            for ($date = $tanggalAwal->copy(); $date->lte($tanggalAkhir); $date->addDay()) {
                $spjRange[] = [
                    'nama_karyawan' => $item->karyawan->nama_lengkap ?? null,
                    'tanggal' => $date->toDateString(), // Menyimpan tanggal saat ini dalam iterasi
                    'jam_masuk' => null,
                    'jam_keluar' => null,
                    'keterangan' => 'SPJ (' . $item->tipe . ')',  // Keterangan Cuti
                    'keterangan_pulang' => 'SPJ (' . $item->keterangan_pulang . ')',
                    'waktu_keterlambatan' => null,
                ];
            }
            return $spjRange; // Mengembalikan rentang tanggal untuk setiap cuti tanpa Sabtu dan Minggu

        })->flatten(1)->toArray();
         // Gabungkan data dan prioritaskan SPJ
        $gabungan = [];
        $tanggal_absensi = [];
        foreach ($absensiData as $item) {
            $tanggal_absensi[] = $item['tanggal'];
        }
        foreach ($spjData as $spjItem) {
            $gabungan[$spjItem['tanggal']] = $spjItem;
        }
        foreach ($cutiData as $cutiItem) {
            $gabungan[$cutiItem['tanggal']] = $cutiItem;
        }
        foreach ($absensiData as $absensiItem) {
            if (!isset($gabungan[$absensiItem['tanggal']])) {
                $gabungan[$absensiItem['tanggal']] = $absensiItem;
            }
        }

        // Urutkan data berdasarkan tanggal
        ksort($gabungan);
        $dataGabungan = array_values($gabungan);

        // Membuat nama file sesuai nama karyawan dan bulan
        $monthName = \Carbon\Carbon::create()->locale('id')->month($bulan)->translatedFormat('F');
        $filename = 'Absen_' . ($dataGabungan[0]['nama_karyawan'] ?? 'Unknown') . '_Bulan_' . $monthName . '_Tahun_' . $tahun . '.xlsx';

        // Mengirim data ke export ke Excel
        return Excel::download(new RekapitulasiAbsenperKaryawanExport($dataGabungan), $filename);
    }


    public function exportperBulan(Request $request, $year, $month)
    {
        Carbon::setLocale('id');
        $absensi = AbsensiKaryawan::with('karyawan')
                                    ->whereYear('tanggal', $year)
                                    ->whereMonth('tanggal', $month)
                                    ->get();

        // Mengambil data cuti untuk semua karyawan dalam bulan tersebut
        $cuti = pengajuancuti::with('karyawan')
                                ->whereYear('tanggal_awal', $year)
                                ->whereMonth('tanggal_awal', $month)
                                ->get();

        // Mengambil data SPJ
        $spj = SuratPerjalanan::with('karyawan')
                                ->whereYear('tanggal_berangkat', $year)
                                ->whereMonth('tanggal_berangkat', $month)
                                ->get();

        // Buat rentang tanggal untuk seluruh bulan
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->locale('id');
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $dates = [];
        for ($date = $startOfMonth; $date->lte($endOfMonth); $date->addDay()) {
            $dates[] = $date->copy();
        }

        $employeeData = [];
        foreach ($absensi->groupBy('id_karyawan') as $id => $items) {
            $employee = $items->first()->karyawan;
            $name = $employee->nama_lengkap ?? 'Unknown';

            // Buat baris dengan nama karyawan dan placeholder untuk setiap tanggal
            $row = array_fill(0, count($dates), '-');
            $totalLate = 0;
            $lateCount = 0;  // Menambahkan penghitung keterlambatan

            // Jam masuk standar (misalnya jam 08:00)
            $standardTime = Carbon::createFromTime(8, 0, 0);
            
            // Tandai data absensi
            foreach ($items as $item) {
                $tanggal = Carbon::parse($item->tanggal)->locale('id');
                $dateIndex = $tanggal->day - 1;
                $row[$dateIndex] = $item->waktu_keterlambatan ?? '00:00:00';

                // Tentukan waktu masuk yang diharapkan (misalnya jam 08:01:00)
                $expectedTime = Carbon::parse($tanggal->toDateString() . ' 08:01:00');

                if ($item->waktu_keterlambatan !== '00:00:00') {
                    $actualLateTime = Carbon::parse($item->waktu_keterlambatan);  // Waktu keterlambatan aktual
                    
                    // Jika waktu keterlambatan lebih dari waktu yang diharapkan
                    if ($actualLateTime->gt($expectedTime)) {
                        // Hitung keterlambatan dalam menit
                        $lateDuration = $actualLateTime->diffInMinutes($expectedTime);
                        
                        // Tambahkan ke total keterlambatan
                        $totalLate += $lateDuration;
                        
                        // Increment jumlah keterlambatan
                        $lateCount++;
                    }
                }
            }

            // Tandai data cuti
            $employeeCuti = $cuti->where('id_karyawan', $id);
            foreach ($employeeCuti as $cutiItem) {
                if ($cutiItem->approval_manager == '1') { // Gunakan operator perbandingan ==
                    $tanggalAwal = Carbon::parse($cutiItem->tanggal_awal);
                    $tanggalAkhir = Carbon::parse($cutiItem->tanggal_akhir);
            
                    // Loop setiap hari dalam rentang cuti dan tandai pada array jika bukan hari libur
                    for ($date = $tanggalAwal; $date->lte($tanggalAkhir); $date->addDay()) {
                        if (!$date->isWeekend() && $date->month == $month) { // Pastikan tanggal dalam bulan yang diminta
                            $dateIndex = $date->day - 1;
                            $row[$dateIndex] = $cutiItem->tipe;
                        }
                    }
                }
                
            }

            // Tandai data SPJ
            foreach ($spj as $spjItem) {
                // Pastikan id_karyawan dari spjItem sama dengan id karyawan yang sedang diproses
                if ($spjItem->id_karyawan == $id) {
                    if($spjItem->approval_hrd == '1'){
                        $tanggalAwal = Carbon::parse($spjItem->tanggal_berangkat);
                        $tanggalAkhir = Carbon::parse($spjItem->tanggal_pulang);
                        for ($date = $tanggalAwal; $date->lte($tanggalAkhir); $date->addDay()) {
                            if ($tanggalAwal->month == $month && $tanggalAwal->year == $year) {
                                $dateIndex = $date->day - 1;
                                $row[$dateIndex] = 'SPJ'; // Ganti nilai dengan 'SPJ'
                            }
                        }
                    }
                }
            }


            // Gabungkan nama dan data ke dalam employeeData
            $employeeData[] = array_merge([$name], $row, [$lateCount]);
        }

        // Urutkan data berdasarkan nama karyawan
        usort($employeeData, function ($a, $b) {
            return strcmp($a[0], $b[0]);
        });

        // Persiapan nama file dan tanggal untuk header Excel
        $monthName = Carbon::createFromDate($year, $month, 1)->locale('id')->translatedFormat('F');
        $filename = 'Absen Karyawan Bulan ' . $monthName . ' Tahun ' . $year . '.xlsx';

        return Excel::download(new RekapitulasiAbsenperBulanExport($employeeData, $dates), $filename);
    }

    public function exportKeterlambatan(Request $request, $year)
    {
        $tahun = $year;

        // Ambil semua absensi karyawan untuk tahun tertentu
        $absensi = AbsensiKaryawan::with('karyawan')
                                    ->whereYear('tanggal', $tahun)
                                    ->get();

        $aggregatedData = [];
        $bulanArray = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        // Kelompokkan data absensi per karyawan
        $absensi->groupBy('id_karyawan')->each(function ($items, $id_karyawan) use (&$aggregatedData, $bulanArray) {
            
            // Inisialisasi data keterlambatan per bulan untuk setiap karyawan
            $karyawanData = array_fill_keys($bulanArray, 'Tidak ada data');
            $nama_karyawan = $items->first()->karyawan->nama_lengkap ?? 'Tidak diketahui';

            // Kelompokkan data absensi per bulan
            $items->groupBy(function ($item) {
                return \Carbon\Carbon::parse($item->tanggal)->translatedFormat('F'); // Ambil nama bulan dalam bahasa Indonesia
            })->each(function ($monthlyItems, $monthName) use (&$karyawanData) {
                
                // Hitung total keterlambatan dalam detik untuk setiap bulan
                $totalSeconds = $monthlyItems->sum(function ($item) {
                    if (!empty($item->waktu_keterlambatan) && strpos($item->waktu_keterlambatan, ':') !== false) {
                        list($hours, $minutes, $seconds) = explode(':', $item->waktu_keterlambatan);
                        return $hours * 3600 + $minutes * 60 + $seconds;
                    }
                    return 0;
                });

                // Tentukan keterangan berdasarkan total keterlambatan
                if ($totalSeconds > 0) {
                    if ($totalSeconds > 3600) {
                        $jam = floor($totalSeconds / 3600);
                        $menit = floor(($totalSeconds % 3600) / 60);
                        $keterangan = "Terlambat " . $jam . " jam " . $menit . " menit";
                    } elseif ($totalSeconds > 900) {
                        $keterangan = "Terlambat > 15 menit";
                    } else {
                        $keterangan = "Terlambat " . floor($totalSeconds / 60) . " menit";
                    }
                } else {
                    $keterangan = "Tidak pernah terlambat";
                }

                // Masukkan keterangan ke data karyawan sesuai bulan
                $karyawanData[$monthName] = $keterangan;
            });

            // Tambahkan data karyawan ke dalam array hasil
            $aggregatedData[] = array_merge(['nama_karyawan' => $nama_karyawan], $karyawanData);
        });

        // Urutkan hasil berdasarkan nama karyawan
        usort($aggregatedData, function ($a, $b) {
            return strcmp($a['nama_karyawan'], $b['nama_karyawan']);
        });
        // return $aggregatedData;
        // Siapkan nama file
        $filename = 'Waktu Keterlambatan Karyawan Tahun ' . $tahun . '.xlsx';

        // Unduh file Excel
        return Excel::download(new RekapitulasiWaktuKeterlambatanExport($aggregatedData), $filename);
    }



    public function edit(string $id)
    {
        $post = AbsensiKaryawan::with('karyawan')->findOrFail($id);
        return view('absensi.edit', compact('post'));
    }

    public function update(string $id,  Request $request)
    {
        // dd($request->all());
        $this->validate($request, [
            'id_karyawan'     => 'required',
            'jam_masuk'   => 'nullable',
            'jam_keluar'   => 'nullable',
            'keterangan'   => 'required',
            'keterangan_pulang'   => 'nullable',
            'waktu_keterlambatan'   => 'required'
        ]);

        $post = AbsensiKaryawan::findOrFail($id);

            $post->update([
                'id_karyawan'     => $request->id_karyawan,
                'jam_masuk'     => $request->jam_masuk,
                'jam_keluar'   => $request->jam_keluar,
                'keterangan'   => $request->keterangan,
                'keterangan_pulang'   => $request->keterangan_pulang,
                'waktu_keterlambatan'   => $request->waktu_keterlambatan,
            ]);

        return redirect()->route('rekapitulasiabsen.index')->with(['success' => 'Data Berhasil Diubah!']);
    }

}
