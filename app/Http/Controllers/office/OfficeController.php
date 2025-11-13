<?php

namespace App\Http\Controllers\office;

use App\Http\Controllers\Controller;
use App\Models\AbsensiKaryawan;
use App\Models\karyawan;
use App\Models\RKM;
use App\Models\Tickets;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OfficeController extends Controller
{
    public function dashboard()
    {
        // 1. Total Karyawan & Divisi Stats
        $total_karyawan = Karyawan::where('status_aktif', '1')
            ->where('divisi', '!=', 'Direksi')
            ->where('jabatan', '!=', 'GM')
            ->where('id', '!=',  ['36', '38', '45', '46', '47', '48', '49', '52', '53', '54'])
            ->count();

        $karyawan = Karyawan::where('status_aktif', '1')
            ->where('divisi', '!=', 'Direksi')
            ->where('jabatan', '!=', 'GM')
            ->where('id', '!=', ['36', '38', '45', '46', '47', '48', '49', '52', '53', '54'])
            ->get();

        $statsFromDB = $karyawan->groupBy('divisi')->map(function ($items) {
            return [
                'total' => $items->count(),
                'data'  => $items
            ];
        });

        $divisiConfig = [
            'Office' => ['color' => 'primary', 'icon' => 'bx bx-building-house'],
            'Education' => ['color' => 'success', 'icon' => 'bx bx-book'],
            'Sales & Marketing' => ['color' => 'warning', 'icon' => 'bx bx-line-chart'],
            'IT Service Management' => ['color' => 'info', 'icon' => 'bx bx-cog'],
        ];

        $divisiStats = [];
        foreach ($divisiConfig as $namaDivisi => $config) {
            $divisiStats[] = [
                'nama'  => $namaDivisi,
                'total' => $statsFromDB[$namaDivisi]['total'] ?? 0,
                'color' => $config['color'],
                'icon'  => $config['icon'],
                'data'  => $statsFromDB[$namaDivisi]['data'] ?? collect([]),
            ];
        }

        // 2. Grafik Kehadiran + Karyawan Tidak Hadir
        $today = Carbon::today();
        $sevenDaysAgo = Carbon::today()->subDays(7);

        // Ambil data absensi 7 hari terakhir
        $absensi7Hari = AbsensiKaryawan::whereBetween('tanggal', [$sevenDaysAgo, $today])
            ->whereIn('id_karyawan', $karyawan->pluck('id'))
            ->get()
            ->groupBy('tanggal');

        // Hitung hadir per hari
        $kehadiranData = [];
        $labels = [];
        $tidakHadirList = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateStr = $date->format('Y-m-d');
            $label = $date->translatedFormat('D, d M');

            $labels[] = $label;

            $hadirHariIni = $absensi7Hari->get($dateStr, collect())->count();
            $totalKaryawan = $karyawan->count();
            $kehadiranData[] = $hadirHariIni;

            // Cek siapa yang tidak hadir HARI INI
            if ($i == 0) {
                $karyawanHadir = $absensi7Hari->get($dateStr, collect())->pluck('id_karyawan');
                $tidakHadir = $karyawan->whereNotIn('id', $karyawanHadir);

                $tidakHadirList = $tidakHadir->map(function ($k) {
                    return [
                        'nama' => $k->nama_lengkap,
                        'divisi' => $k->divisi,
                    ];
                })->values();
            }
        }

        $kehadiranChart = [
            'labels' => $labels,
            'data' => $kehadiranData,
        ];

        // 3. Laporan Ticketing
        $ticket = Tickets::where('status', '!=', 'Selesai')
        ->latest()
        ->take(7)
        ->get();

        // 4. RKM
        $rkm = RKM::with('materi', 'perusahaan', 'peluang')
            ->where('tanggal_awal', '<=', Carbon::now())
            ->where('tanggal_akhir', '>=', Carbon::now())
            ->where('status', '0')
            ->get();

        return view('office.dashboard', compact(
            'total_karyawan',
            'divisiStats',
            'kehadiranChart',
            'tidakHadirList',
            'ticket',
            'rkm',
        ));
    }
}
