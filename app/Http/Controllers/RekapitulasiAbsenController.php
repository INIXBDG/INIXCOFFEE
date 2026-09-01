<?php

namespace App\Http\Controllers;

use App\Exports\RekapitulasiAbsenperBulanExport;
use App\Exports\RekapitulasiAbsenperKaryawanExport;
use App\Exports\RekapitulasiWaktuKeterlambatanExport;
use App\Models\AbsensiKaryawan;
use App\Models\Karyawan;
use App\Models\Peserta;
use App\Models\IzinTigaJam;
use App\Models\PengajuanCuti;
use App\Models\SuratPerjalanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
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
        if ($jabatan == "HRD" || $jabatan == "Koordinator Office") {
            $peserta = Karyawan::all();
            return view('absensi.index', compact('peserta'));
        } else {
            abort(404);
        }
    }

    public function getAbsen(Request $request)
    {
        $id_karyawan = $request->input('id_karyawan');
        $tahun = $request->input('tahun');
        $bulan = $request->input('bulan');

        $absensi = AbsensiKaryawan::where('id_karyawan', $id_karyawan)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->orderBy('tanggal', 'asc')
            ->get();

        // Optimasi N+1 Query: Ambil semua izin sekaligus, lalu keyBy berdasarkan tanggal
        $tanggalList = $absensi->pluck('tanggal')->unique();
        $izins = IzinTigaJam::where('id_karyawan', $id_karyawan)
            ->whereIn('tanggal_pengajuan', $tanggalList)
            ->where('approval', 2)
            ->get()
            ->keyBy('tanggal_pengajuan');

        // Loop tanpa query database di dalamnya (hanya lookup di memory)
        foreach ($absensi as $item) {
            $izin = $izins->get($item->tanggal);

            $item->izin = $izin
                ? [
                    'jam_mulai' => Carbon::parse($izin->jam_mulai)->format('H:i'),
                    'jam_selesai' => Carbon::parse($izin->jam_selesai)->format('H:i'),
                ]
                : null;
        }

        // Hitung total keterlambatan
        $totalSeconds = $absensi->sum(function ($item) {
            list($hours, $minutes, $seconds) = explode(':', $item->waktu_keterlambatan ?? '00:00:00');
            return $hours * 3600 + $minutes * 60 + $seconds;
        });

        $formattedTime = $this->formatSeconds($totalSeconds);

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
        $tahun = $year;
        $bulan = $month;

        // Ambil data absensi dengan eager loading
        $absensi = AbsensiKaryawan::with('karyawan')
            ->where('id_karyawan', $id_karyawan)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->get();

        // Ambil data cuti dengan eager loading untuk menghindari N+1
        $cuti = PengajuanCuti::with('karyawan')
            ->where('id_karyawan', $id_karyawan)
            ->whereYear('tanggal_awal', $tahun)
            ->whereMonth('tanggal_awal', $bulan)
            ->where('approval_manager', '1')
            ->get();

        // Ambil data SPJ dengan eager loading
        $spj = SuratPerjalanan::with('karyawan')
            ->where('id_karyawan', $id_karyawan)
            ->whereYear('tanggal_berangkat', $tahun)
            ->whereMonth('tanggal_berangkat', $bulan)
            ->where('approval_hrd', '1')
            ->get();

        // Hitung total keterlambatan
        $totalSeconds = $absensi->sum(function ($item) {
            list($hours, $minutes, $seconds) = explode(':', $item->waktu_keterlambatan ?? '00:00:00');
            return $hours * 3600 + $minutes * 60 + $seconds;
        });

        $formattedTime = $this->formatSeconds($totalSeconds);

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

        // Format data cuti menjadi array per tanggal (exclude weekend)
        $cutiData = $cuti->map(function ($item) {
            $tanggalAwal = Carbon::parse($item->tanggal_awal);
            $tanggalAkhir = Carbon::parse($item->tanggal_akhir);
            $cutiRange = [];

            for ($date = $tanggalAwal->copy(); $date->lte($tanggalAkhir); $date->addDay()) {
                if (!$date->isWeekend()) {
                    $cutiRange[] = [
                        'nama_karyawan' => $item->karyawan->nama_lengkap ?? null,
                        'tanggal' => $date->toDateString(),
                        'jam_masuk' => null,
                        'jam_keluar' => null,
                        'keterangan' => $item->tipe,
                        'waktu_keterlambatan' => null,
                    ];
                }
            }
            return $cutiRange;
        })->flatten(1)->toArray();

        // Format data SPJ menjadi array per tanggal
        $spjData = $spj->map(function ($item) {
            $tanggalAwal = Carbon::parse($item->tanggal_berangkat);
            $tanggalAkhir = Carbon::parse($item->tanggal_pulang);
            $spjRange = [];

            for ($date = $tanggalAwal->copy(); $date->lte($tanggalAkhir); $date->addDay()) {
                $spjRange[] = [
                    'nama_karyawan' => $item->karyawan->nama_lengkap ?? null,
                    'tanggal' => $date->toDateString(),
                    'jam_masuk' => null,
                    'jam_keluar' => null,
                    'keterangan' => 'SPJ (' . $item->tipe . ')',
                    'keterangan_pulang' => 'SPJ (' . $item->keterangan_pulang . ')',
                    'waktu_keterlambatan' => null,
                ];
            }
            return $spjRange;
        })->flatten(1)->toArray();

        // Gabungkan data: prioritas SPJ > Cuti > Absensi
        $gabungan = [];

        // Isi dengan SPJ terlebih dahulu (prioritas tertinggi)
        foreach ($spjData as $spjItem) {
            $gabungan[$spjItem['tanggal']] = $spjItem;
        }

        // Timpa dengan Cuti jika tanggal belum diisi SPJ
        foreach ($cutiData as $cutiItem) {
            if (!isset($gabungan[$cutiItem['tanggal']])) {
                $gabungan[$cutiItem['tanggal']] = $cutiItem;
            }
        }

        // Timpa dengan Absensi hanya jika belum ada SPJ atau Cuti
        foreach ($absensiData as $absensiItem) {
            if (!isset($gabungan[$absensiItem['tanggal']])) {
                $gabungan[$absensiItem['tanggal']] = $absensiItem;
            }
        }

        // Urutkan data berdasarkan tanggal
        ksort($gabungan);
        $dataGabungan = array_values($gabungan);

        // Membuat nama file sesuai nama karyawan dan bulan
        $monthName = Carbon::create()->locale('id')->month($bulan)->translatedFormat('F');
        $filename = 'Absen_' . ($dataGabungan[0]['nama_karyawan'] ?? 'Unknown') . '_Bulan_' . $monthName . '_Tahun_' . $tahun . '.xlsx';

        return Excel::download(new RekapitulasiAbsenperKaryawanExport($dataGabungan), $filename);
    }

    public function exportperBulan(Request $request, $year, $month)
    {
        Carbon::setLocale('id');
        $absensi = AbsensiKaryawan::with('karyawan')
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->get();

        $cuti = PengajuanCuti::with('karyawan')
            ->whereYear('tanggal_awal', $year)
            ->whereMonth('tanggal_awal', $month)
            ->get();

        $spj = SuratPerjalanan::with('karyawan')
            ->whereYear('tanggal_berangkat', $year)
            ->whereMonth('tanggal_berangkat', $month)
            ->get();

        // Optimasi: Kelompokkan data berdasarkan id_karyawan agar tidak perlu loop berulang
        $absensiGrouped = $absensi->groupBy('id_karyawan');
        $cutiGrouped = $cuti->groupBy('id_karyawan');
        $spjGrouped = $spj->groupBy('id_karyawan');

        // Buat rentang tanggal untuk seluruh bulan
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->locale('id');
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $dates = [];
        for ($date = $startOfMonth; $date->lte($endOfMonth); $date->addDay()) {
            $dates[] = $date->copy();
        }

        $employeeData = [];

        foreach ($absensiGrouped as $id => $items) {
            $employee = $items->first()->karyawan;
            $name = $employee->nama_lengkap ?? 'Unknown';

            // Buat baris dengan nama karyawan dan placeholder untuk setiap tanggal
            $row = array_fill(0, count($dates), '-');
            $totalLate = 0;
            $lateCount = 0;

            $expectedTime = Carbon::createFromTime(8, 1, 0);

            // Tandai data absensi
            foreach ($items as $item) {
                $tanggal = Carbon::parse($item->tanggal)->locale('id');
                $dateIndex = $tanggal->day - 1;
                $row[$dateIndex] = $item->waktu_keterlambatan ?? '00:00:00';

                if ($item->waktu_keterlambatan !== '00:00:00' && !empty($item->waktu_keterlambatan)) {
                    $actualLateTime = Carbon::parse($item->waktu_keterlambatan);
                    if ($actualLateTime->gt($expectedTime)) {
                        $lateDuration = $actualLateTime->diffInMinutes($expectedTime);
                        $totalLate += $lateDuration;
                        $lateCount++;
                    }
                }
            }

            // Tandai data cuti (ambil dari collection yang sudah di-group)
            $employeeCuti = $cutiGrouped->get($id, collect());
            foreach ($employeeCuti as $cutiItem) {
                if ($cutiItem->approval_manager == '1') {
                    $tanggalAwal = Carbon::parse($cutiItem->tanggal_awal);
                    $tanggalAkhir = Carbon::parse($cutiItem->tanggal_akhir);

                    for ($date = $tanggalAwal->copy(); $date->lte($tanggalAkhir); $date->addDay()) {
                        if (!$date->isWeekend() && $date->month == $month) {
                            $dateIndex = $date->day - 1;
                            $row[$dateIndex] = $cutiItem->tipe;
                        }
                    }
                }
            }

            // Tandai data SPJ (ambil dari collection yang sudah di-group)
            $employeeSpj = $spjGrouped->get($id, collect());
            foreach ($employeeSpj as $spjItem) {
                if ($spjItem->approval_hrd == '1') {
                    $tanggalAwal = Carbon::parse($spjItem->tanggal_berangkat);
                    $tanggalAkhir = Carbon::parse($spjItem->tanggal_pulang);
                    for ($date = $tanggalAwal->copy(); $date->lte($tanggalAkhir); $date->addDay()) {
                        if ($date->month == $month && $date->year == $year) {
                            $dateIndex = $date->day - 1;
                            $row[$dateIndex] = 'SPJ';
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

        $monthName = Carbon::createFromDate($year, $month, 1)->locale('id')->translatedFormat('F');
        $filename = 'Absen Karyawan Bulan ' . $monthName . ' Tahun ' . $year . '.xlsx';

        return Excel::download(new RekapitulasiAbsenperBulanExport($employeeData, $dates), $filename);
    }

    public function exportKeterlambatan(Request $request, $year)
    {
        $tahun = $year;

        $absensi = AbsensiKaryawan::with('karyawan')
            ->whereYear('tanggal', $tahun)
            ->get();

        $aggregatedData = [];
        $bulanArray = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        $absensi->groupBy('id_karyawan')->each(function ($items, $id_karyawan) use (&$aggregatedData, $bulanArray) {
            $karyawanData = array_fill_keys($bulanArray, 'Tidak ada data');
            $nama_karyawan = $items->first()->karyawan->nama_lengkap ?? 'Tidak diketahui';

            $items->groupBy(function ($item) {
                return Carbon::parse($item->tanggal)->translatedFormat('F');
            })->each(function ($monthlyItems, $monthName) use (&$karyawanData) {
                $totalSeconds = $monthlyItems->sum(function ($item) {
                    if (!empty($item->waktu_keterlambatan) && strpos($item->waktu_keterlambatan, ':') !== false) {
                        list($hours, $minutes, $seconds) = explode(':', $item->waktu_keterlambatan);
                        return $hours * 3600 + $minutes * 60 + $seconds;
                    }
                    return 0;
                });

                if ($totalSeconds > 0) {
                    if ($totalSeconds > 3600) {
                        $jam = floor($totalSeconds / 3600);
                        $menit = floor(($totalSeconds % 3600) / 60);
                        $keterangan = "Terlambat " . $jam . " jam " . $menit . " menit";
                    } else {
                        $keterangan = "Terlambat " . floor($totalSeconds / 60) . " menit";
                    }
                } else {
                    $keterangan = "Tidak pernah terlambat";
                }

                $karyawanData[$monthName] = $keterangan;
            });

            $aggregatedData[] = array_merge(['nama_karyawan' => $nama_karyawan], $karyawanData);
        });

        usort($aggregatedData, function ($a, $b) {
            return strcmp($a['nama_karyawan'], $b['nama_karyawan']);
        });

        $filename = 'Waktu Keterlambatan Karyawan Tahun ' . $tahun . '.xlsx';

        return Excel::download(new RekapitulasiWaktuKeterlambatanExport($aggregatedData), $filename);
    }

    public function edit(string $id)
    {
        $post = AbsensiKaryawan::with('karyawan')->findOrFail($id);
        return view('absensi.edit', compact('post'));
    }

    public function update(string $id, Request $request)
    {
        $this->validate($request, [
            'id_karyawan' => 'required',
            'jam_masuk' => 'nullable',
            'jam_keluar' => 'nullable',
            'keterangan' => 'required',
            'keterangan_pulang' => 'nullable',
            'waktu_keterlambatan' => 'required'
        ]);

        $post = AbsensiKaryawan::findOrFail($id);

        $post->update([
            'id_karyawan' => $request->id_karyawan,
            'jam_masuk' => $request->jam_masuk,
            'jam_keluar' => $request->jam_keluar,
            'keterangan' => $request->keterangan,
            'keterangan_pulang' => $request->keterangan_pulang,
            'waktu_keterlambatan' => $request->waktu_keterlambatan,
        ]);

        return redirect()->route('rekapitulasiabsen.index')->with(['success' => 'Data Berhasil Diubah!']);
    }

    /**
     * Helper method untuk memformat detik menjadi string jam/menit/detik
     */
    private function formatSeconds($totalSeconds)
    {
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

        return $formattedTime ?: '0 detik';
    }
}