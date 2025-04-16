<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RKM;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use App\Http\Resources\PostResource;
use App\Models\analisisrkmmingguan;
use App\Models\eksam;
use App\Models\netSales;

class netSalesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:View AnalisisRKM', ['only' => ['index']]);
    }
    public function index()
    {
        return view('netSales.index');
    }

    public function store(Request $request)
    {
        // return $request->all();
        //validate form
        $request->merge([
            'sebelumNetSales'   => str_replace('.', '', $request->sebelumNetSales),
            'pajak'             => str_replace('.', '', $request->pajak),
            'cashback'          => str_replace('.', '', $request->cashback),
            'biaya_akomodasi'   => str_replace('.', '', $request->biaya_akomodasi),
            'entertaint'        => str_replace('.', '', $request->entertaint),
        ]);

        $data = $request->validate([
            'id_rkm'          => 'required',
            'sebelumNetSales' => 'required',
            'pajak'           => 'required',
            'cashback'        => 'required',
            'biaya_akomodasi' => 'required',
            'entertaint'      => 'required',
        ]);

        netSales::create($data);

        return redirect()->route('netsales.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }

    public function getRkmDataPerBulanPerMinggu($year)
    {
        Carbon::setLocale('id');
        $startDate = CarbonImmutable::create($year, 1, 1);
        $endDate = CarbonImmutable::create($year, 12, 1)->endOfMonth();

        $monthRanges = [];
        $date = $startDate;

        while ($date->month <= $endDate->month && $date->year <= $endDate->year) {
            $startOfMonth = $date->startOfMonth();
            $endOfMonth = $date->endOfMonth();
            $monthName = $startOfMonth->translatedFormat('F');

            $weekRanges = [];
            $startOfWeek = $startOfMonth->startOfWeek();
            $weekNumber = 1;

            while ($startOfWeek->lte($endOfMonth)) {
                $endOfWeek = $startOfWeek->copy()->endOfWeek();

                // Skip minggu jika tidak dalam bulan yang sedang diproses
                if ($startOfWeek->month != $date->month) {
                    $startOfWeek = $startOfWeek->addWeek();
                    $weekNumber++;
                    continue;
                }

                $start = $startOfWeek->format('Y-m-d');
                $end = $endOfWeek->format('Y-m-d');

                $rkm = RKM::with(['materi', 'analisisrkm', 'netSales', 'analisisrkm.analisisrkmmingguan'])
                    ->where('status', '0')
                    ->whereYear('tanggal_awal', $year)
                    ->whereBetween('tanggal_awal', [$start, $end])
                    ->get();

                $formattedItems = $rkm->map(function ($item) {
                    $status = optional($item->netsales)->sebelumNetSales !== null ? 'Hijau' : 'Merah';
                    $tanggalAwal = Carbon::parse($item->tanggal_awal);
                    $tanggalAkhir = Carbon::parse($item->tanggal_akhir);
                    $total_harga_jual = floatval($item->harga_jual) * intval($item->pax);

                    $netSalesData = $item->netsales ? $item->netsales->toArray() : null;

                    $totalPerhitunganNetSales = $item->total_harga_jual - $item->sebelumNetSales - $item->pajak - $item->cashback - $item->biaya_akomodasi - $item->entertaint;

                    return [
                        'id'              => $item->id,
                        'nama_materi'     => $item->materi->nama_materi,
                        'pax'             => $item->pax,
                        'harga_jual'      => $item->harga_jual,
                        'total_harga_jual' => $total_harga_jual,
                        'tanggal_awal'    => $tanggalAwal->translatedFormat('d F Y'),
                        'tanggal_akhir'   => $tanggalAkhir->translatedFormat('d F Y'),
                        'durasi'          => $tanggalAwal->diffInDays($tanggalAkhir) + 1,
                        'status'          => $status,
                        'analisisrkm'     => $netSalesData,
                        'sebelumNetSales' => optional($item->netSales)->sebelumNetSales,
                        'pajak'           => optional($item->netSales)->pajak,
                        'cashback'        => optional($item->netSales)->cashback,
                        'biaya_akomodasi' => optional($item->netSales)->biaya_akomodasi,
                        'entertaint'      => optional($item->netSales)->entertaint,
                        'total'           => $totalPerhitunganNetSales
                    ];
                });

                // status mingguan
                $rkmfull = 'no data';
                if ($formattedItems->isNotEmpty()) {
                    $rkmfull = $formattedItems->every(fn($item) => $item['status'] === 'Hijau') ? 'ok' : 'pending';
                }

                $weekRanges[] = [
                    'rkmfull'              => $rkmfull,
                    'tahun'                => $year,
                    'bulan'                => $monthName,
                    'minggu'               => $weekNumber,
                    'tanggal_awal_minggu'  => $startOfWeek->translatedFormat('d F Y'),
                    'tanggal_akhir_minggu' => $endOfWeek->translatedFormat('d F Y'),
                    'data'                 => $formattedItems->isEmpty() ? null : $formattedItems,
                ];

                $startOfWeek = $startOfWeek->addWeek();
                $weekNumber++;
            }

            $monthRanges[] = [
                'month' => $monthName,
                'weeksData' => $weekRanges
            ];

            $date = $date->addMonth();
        }

        return new PostResource(true, 'List Detail Bulan RKM', $monthRanges);
    }


    public function create($id)
    {
        $rkm = RKM::with('perusahaan', 'materi')->findOrFail($id);
        $exam = eksam::where('id_rkm', $rkm->id)->first();
        if (!$exam) {
            $exam = null;
        } else {
            $exam = $exam->total;
            $exam = round($exam, 0);
        }
        $tanggalAwal = Carbon::parse($rkm->tanggal_awal);
        $tanggalAkhir = Carbon::parse($rkm->tanggal_akhir);
        // return $exam;
        $durasi = $tanggalAwal->diffInDays($tanggalAkhir);
        return view('netSales.create', compact('rkm', 'durasi', 'exam'));
    }
}
