<?php

namespace App\Http\Controllers\office;

use Carbon\Carbon;
use App\Models\RKM;
use App\Models\Tickets;
use App\Models\Feedback;
use App\Models\karyawan;
use Illuminate\Http\Request;
use App\Models\Nilaifeedback;
use App\Models\AbsensiKaryawan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class OfficeController extends Controller
{
    public function dashboard(Request $request)
    {
        // 1. Total Karyawan & Divisi Stats
        $total_karyawan = Karyawan::where('status_aktif', '1')
            ->where('divisi', '!=', 'Direksi')
            ->where('jabatan', '!=', 'GM')
            ->where('id', '!=', ['36', '38', '45', '46', '47', '48', '49', '52', '53', '54'])
            ->count();

        $karyawan = Karyawan::where('status_aktif', '1')
            ->where('divisi', '!=', 'Direksi')
            ->where('jabatan', '!=', 'GM')
            ->where('id', '!=', ['36', '38', '45', '46', '47', '48', '49', '52', '53', '54'])
            ->get();

        $statsFromDB = $karyawan->groupBy('divisi')->map(function ($items) {
            return [
                'total' => $items->count(),
                'data' => $items
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
                'nama' => $namaDivisi,
                'total' => $statsFromDB[$namaDivisi]['total'] ?? 0,
                'color' => $config['color'],
                'icon' => $config['icon'],
                'data' => $statsFromDB[$namaDivisi]['data'] ?? collect([]),
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

    public function getNilaiInstruktur(Request $request)
    {
        $filter = $request->filter;
        $value = $request->value;
        $tahun = $request->tahun ?? now()->year;

        $query = Nilaifeedback::with('rkm.instruktur', 'rkm.instruktur2', 'rkm.asisten');

        if ($filter === 'tahun' && is_numeric($value)) {
            $query->whereYear('created_at', $value);
        } elseif ($filter === 'bulan' && is_numeric($value)) {
            $query->whereYear('created_at', $tahun)
                ->whereMonth('created_at', $value);
        } elseif ($filter === 'triwulan' && is_numeric($value)) {
            $bulanMulai = ($value - 1) * 3 + 1;
            $bulanSelesai = $bulanMulai + 2;

            $query->whereYear('created_at', $tahun)
                ->whereBetween(DB::raw('MONTH(created_at)'), [$bulanMulai, $bulanSelesai]);
        }

        $feedbacks = $query->get();

        if ($feedbacks->count() === 0) {
            return response()->json([]);
        }

        $groupByInstruktur = $feedbacks->groupBy(function ($item) {
            return $item->rkm->instruktur->id ?? null;
        });

        $result = [];

        foreach ($groupByInstruktur as $instrukturId => $items) {
            if (!$instrukturId)
                continue;

            $avgIU = collect(['I1', 'I2', 'I3', 'I4', 'I5', 'I6', 'I7', 'I8'])
                ->map(fn($i) => $items->avg($i))
                ->avg();

            $avgI2 = collect(['I1b', 'I2b', 'I3b', 'I4b', 'I5b', 'I6b', 'I7b', 'I8b'])
                ->map(fn($i) => $items->avg($i))
                ->avg();

            $avgIas = collect(['I1as', 'I2as', 'I3as', 'I4as', 'I5as', 'I6as', 'I7as', 'I8as'])
                ->map(fn($i) => $items->avg($i))
                ->avg();

            $nilaiAkhir = collect([$avgIU, $avgI2, $avgIas])->avg();

            $result[] = [
                'id_instruktur' => $instrukturId,
                'nama_instruktur' => $items->first()->rkm->instruktur->nama_lengkap,
                'nilai_instruktur' => round($nilaiAkhir, 2),
            ];
        }

        return response()->json($result);

    }

    public function exportPdf(Request $request)
    {
        $filter = $request->filter;
        $value = $request->value;
        $tahun = $request->tahun ?? now()->year;

        $query = Nilaifeedback::with('rkm.instruktur');

        // === FILTER ===
        if ($filter === 'tahun' && is_numeric($value)) {
            $query->whereYear('created_at', $value);
            $rentangWaktu = "Tahun $value";

        } elseif ($filter === 'bulan' && is_numeric($value)) {
            $query->whereYear('created_at', $tahun)
                ->whereMonth('created_at', $value);

            $rentangWaktu = \Carbon\Carbon::createFromDate($tahun, $value, 1)
                ->translatedFormat('F Y');

        } elseif ($filter === 'triwulan' && is_numeric($value)) {
            $bulanMulai = ($value - 1) * 3 + 1;
            $bulanSelesai = $bulanMulai + 2;

            $query->whereYear('created_at', $tahun)
                ->whereBetween(DB::raw('MONTH(created_at)'), [$bulanMulai, $bulanSelesai]);

            $rentangWaktu = "Triwulan $value Tahun $tahun";
        } else {
            $rentangWaktu = "Semua Data";
        }

        $feedbacks = $query->get();

        // === GROUP & HITUNG NILAI ===
        $data = $feedbacks
            ->filter(fn($f) => $f->rkm && $f->rkm->instruktur)
            ->groupBy(fn($f) => $f->rkm->instruktur->nama_lengkap)
            ->map(function ($items) {

                $instruktur = $items->first()->rkm->instruktur;

                return [
                    'nama' => $instruktur->nama_lengkap,
                    'nilai' => round(
                        $items->avg(function ($row) {
                            return collect([
                                $row->I1,
                                $row->I2,
                                $row->I3,
                                $row->I4,
                                $row->I5,
                                $row->I6,
                                $row->I7,
                                $row->I8
                            ])->avg();
                        }),
                        2
                    )
                ];
            })
            ->values();


        $pdf = Pdf::loadView('office.feedbackinstrukturpdf', [
            'data' => $data,
            'rentangWaktu' => $rentangWaktu
        ])->setPaper('A4', 'portrait');

        return $pdf->download('Laporan_Feedback_Instruktur.pdf');
    }

}
