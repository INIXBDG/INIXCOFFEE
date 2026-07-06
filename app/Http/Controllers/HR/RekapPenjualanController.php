<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RKM;
use App\Models\kelasanalisis;
use App\Models\analisisrkmmingguan;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

class RekapPenjualanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        Carbon::setLocale('id');
        $currentYear = date('Y');

        return view('HR.rekapPenjualan.index', compact('currentYear'));
    }

    public function getData($year, $monthStart = 1, $monthEnd = 12)
    {
        Carbon::setLocale('id');

        $rkms = RKM::with(['materi', 'analisisrkm'])
            ->where('status', '0')
            ->whereYear('tanggal_awal', $year)
            ->whereMonth('tanggal_awal', '>=', $monthStart)
            ->whereMonth('tanggal_awal', '<=', $monthEnd)
            ->get();

        $bulanNama = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $rekap = [];
        $grandTotal = [
            'total_kelas' => 0,
            'total_pax' => 0,
            'total_harga_jual' => 0,
            'total_nett' => 0,
            'total_lengkap' => 0,
        ];

        for ($m = $monthStart; $m <= $monthEnd; $m++) {
            $monthData = $rkms->filter(fn($r) => Carbon::parse($r->tanggal_awal)->month == $m);

            $totalKelas = $monthData->count();
            $totalPax = $monthData->sum('pax');
            $totalHargaJual = $monthData->sum(fn($r) => floatval($r->harga_jual) * intval($r->pax));
            $totalNett = $monthData->sum(fn($r) => $r->analisisrkm ? floatval($r->analisisrkm->nett_penjualan) : 0);
            $totalLengkap = $monthData->filter(fn($r) => $r->analisisrkm !== null)->count();

            $rekap[] = [
                'bulan' => $bulanNama[$m],
                'bulan_angka' => $m,
                'total_kelas' => $totalKelas,
                'total_pax' => $totalPax,
                'total_harga_jual' => $totalHargaJual,
                'total_nett' => $totalNett,
                'total_lengkap' => $totalLengkap,
                'total_belum' => $totalKelas - $totalLengkap,
                'rata_rata' => $totalKelas > 0 ? $totalNett / $totalKelas : 0,
            ];

            $grandTotal['total_kelas'] += $totalKelas;
            $grandTotal['total_pax'] += $totalPax;
            $grandTotal['total_harga_jual'] += $totalHargaJual;
            $grandTotal['total_nett'] += $totalNett;
            $grandTotal['total_lengkap'] += $totalLengkap;
        }

        return response()->json([
            'success' => true,
            'data' => $rekap,
            'grand_total' => $grandTotal,
        ]);
    }

    public function getMateriData($year)
    {
        Carbon::setLocale('id');

        $rkms = RKM::with(['materi', 'analisisrkm'])
            ->where('status', '0')
            ->whereYear('tanggal_awal', $year)
            ->get();

        $grouped = $rkms
            ->groupBy('materi_id')
            ->map(function ($items, $materiId) {
                $materiName = $items->first()->materi->nama_materi ?? 'Materi Tidak Diketahui';
                $totalKelas = $items->count();
                $totalPax = $items->sum('pax');
                $totalHargaJual = $items->sum(fn($r) => floatval($r->harga_jual) * intval($r->pax));
                $totalNett = $items->sum(fn($r) => $r->analisisrkm ? floatval($r->analisisrkm->nett_penjualan) : 0);
                $totalLengkap = $items->filter(fn($r) => $r->analisisrkm !== null)->count();

                return [
                    'materi_id' => $materiId,
                    'nama_materi' => $materiName,
                    'total_kelas' => $totalKelas,
                    'total_pax' => $totalPax,
                    'total_harga_jual' => $totalHargaJual,
                    'total_nett' => $totalNett,
                    'total_lengkap' => $totalLengkap,
                    'rata_rata_pax' => $totalKelas > 0 ? round($totalPax / $totalKelas, 1) : 0,
                ];
            })
            ->sortByDesc('total_nett')
            ->values();

        return response()->json([
            'success' => true,
            'data' => $grouped,
        ]);
    }

    public function getMingguanData($year, $month)
    {
        Carbon::setLocale('id');

        $startOfMonth = CarbonImmutable::create($year, $month, 1);
        $endOfMonth = $startOfMonth->endOfMonth();

        $rkms = RKM::with(['materi', 'analisisrkm'])
            ->where('status', '0')
            ->whereYear('tanggal_awal', $year)
            ->whereMonth('tanggal_awal', $month)
            ->get();

        $weeks = [];
        $startOfWeek = $startOfMonth->startOfWeek(Carbon::MONDAY);
        $weekNumber = 1;

        while ($startOfWeek->lte($endOfMonth)) {
            $endOfWeek = $startOfWeek->copy()->endOfWeek(Carbon::SUNDAY);

            if ($startOfWeek->month != $month) {
                $startOfWeek = $startOfWeek->addWeek();
                $weekNumber++;
                continue;
            }

            $weekData = $rkms->filter(function ($item) use ($startOfWeek, $endOfWeek) {
                $tgl = Carbon::parse($item->tanggal_awal);
                return $tgl->between($startOfWeek, $endOfWeek);
            });

            $totalKelas = $weekData->count();
            $totalPax = $weekData->sum('pax');
            $totalHargaJual = $weekData->sum(fn($r) => floatval($r->harga_jual) * intval($r->pax));
            $totalNett = $weekData->sum(fn($r) => $r->analisisrkm ? floatval($r->analisisrkm->nett_penjualan) : 0);

            $weeks[] = [
                'minggu' => $weekNumber,
                'tanggal_awal' => $startOfWeek->translatedFormat('d M'),
                'tanggal_akhir' => $endOfWeek->translatedFormat('d M Y'),
                'total_kelas' => $totalKelas,
                'total_pax' => $totalPax,
                'total_harga_jual' => $totalHargaJual,
                'total_nett' => $totalNett,
            ];

            $startOfWeek = $startOfWeek->addWeek();
            $weekNumber++;
        }

        return response()->json([
            'success' => true,
            'bulan' => $startOfMonth->translatedFormat('F'),
            'data' => $weeks,
        ]);
    }

    public function getProfitabilitas($year)
    {
        Carbon::setLocale('id');

        $bulanNama = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $rkms = RKM::with(['analisisrkm.analisisrkmmingguan'])
            ->where('status', '0')
            ->whereYear('tanggal_awal', $year)
            ->get();

        $profitData = [];
        $totalProfit = 0;

        for ($m = 1; $m <= 12; $m++) {
            $monthData = $rkms->filter(fn($r) => Carbon::parse($r->tanggal_awal)->month == $m);

            $totalNett = 0;
            $totalFixCost = 0;
            $fixCostFound = false;

            foreach ($monthData as $rkm) {
                if ($rkm->analisisrkm && $rkm->analisisrkm->analisisrkmmingguan) {
                    foreach ($rkm->analisisrkm->analisisrkmmingguan as $mingguan) {
                        $totalNett += floatval($mingguan->nett_penjualan ?? 0);
                        if (!$fixCostFound && $mingguan->fixcost !== null) {
                            $totalFixCost += floatval($mingguan->fixcost);
                            $fixCostFound = true;
                        }
                    }
                }
            }

            $profit = $totalNett - $totalFixCost;
            $totalProfit += $profit;

            $profitData[] = [
                'bulan' => $bulanNama[$m],
                'bulan_angka' => $m,
                'total_nett' => $totalNett,
                'total_fixcost' => $totalFixCost,
                'profit' => $profit,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $profitData,
            'total_profit' => $totalProfit,
        ]);
    }
}
