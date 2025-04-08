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
use App\Models\kelasanalisis;

class KelasAnalisisController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:View AnalisisRKM', ['only' => ['index']]);
    }
    public function index()
    {
        return view('kelasanalisis.index'); 
    }
    public function getRkmDataPerBulanPerMinggu($year)
    {
        Carbon::setLocale('id'); // Set locale ke Bahasa Indonesia
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
                
                // Kondisi untuk menghindari data duplikat di antara dua bulan
                if ($startOfWeek->month != $date->month) {
                    $startOfWeek = $startOfWeek->addWeek(); 
                    $weekNumber++;
                    continue;
                }
    
                $start = $startOfWeek->format('Y-m-d');
                $end = $endOfWeek->format('Y-m-d');
    
                // Fetch RKM data only for the specified week
                $rkm = RKM::with(['materi', 'analisisrkm', 'analisisrkm.analisisrkmmingguan'])
                    ->where('status', '0')
                    ->whereYear('tanggal_awal', $year)
                    ->whereBetween('tanggal_awal', [$start, $end])
                    ->get();
    
                // Check if all items have status "Hijau"
                $allHijau = $rkm->isNotEmpty() && $rkm->every(function ($item) {
                    // dd($item->analisisrkm);
                    // return $item;
                    return $item->analisisrkm ? 'Hijau' : 'Merah' == 'Hijau';
                });
    
                $rkmfull = $allHijau ? 'ok' : 'pending';
    
                $formattedItems = $rkm->map(function ($item) {
                    $status = $item->analisisrkm ? 'Hijau' : 'Merah';
                    $tanggalAwal = Carbon::parse($item->tanggal_awal);
                    $tanggalAkhir = Carbon::parse($item->tanggal_akhir);
                    $total_harga_jual = floatval($item->harga_jual) * intval($item->pax);
    
                    $analisisRkmData = $item->analisisrkm ? $item->analisisrkm->toArray() : null;
    
                    return [
                        'id'              => $item->id,
                        'nama_materi'     => $item->materi->nama_materi,
                        'pax'             => $item->pax,
                        'harga_jual'      => $item->harga_jual,
                        'total_harga_jual'=> $total_harga_jual,
                        'tanggal_awal'    => $tanggalAwal->translatedFormat('d F Y'),
                        'tanggal_akhir'   => $tanggalAkhir->translatedFormat('d F Y'),
                        'durasi'          => $tanggalAwal->diffInDays($tanggalAkhir) + 1,
                        'status'          => $status,
                        'analisisrkm'     => $analisisRkmData
                    ];
                });
    
                $weekRanges[] = [
                    'rkmfull'              => $formattedItems->isEmpty() ? 'no data' : $rkmfull,
                    'tahun'                => $year,
                    'bulan'                => $monthName,
                    'minggu'               => $weekNumber,
                    'tanggal_awal_minggu'  => $startOfWeek->translatedFormat('d F Y'),
                    'tanggal_akhir_minggu' => $endOfWeek->translatedFormat('d F Y'),
                    'data'                 => $formattedItems->isEmpty() ? null : $formattedItems,
                ];
    
                // Move to the next week
                $startOfWeek = $startOfWeek->addWeek();
                $weekNumber++;
            }
    
            $monthRanges[] = [
                'month' => $monthName,
                'weeksData' => $weekRanges
            ];
    
            // Move to the next month
            $date = $date->addMonth();
        }
    
        $json = $monthRanges;
        return new PostResource(true, 'List Detail Bulan RKM', $json);
    }

    public function create($id)
    {
        $rkm = RKM::with('perusahaan', 'materi')->findOrFail($id);
        $exam = eksam::where('id_rkm', $rkm->id)->first();
        if (!$exam) {
            $exam = null;
        }else{
            $exam = $exam->total;
            $exam = round($exam, 0);
        }
        $tanggalAwal = Carbon::parse($rkm->tanggal_awal);
        $tanggalAkhir = Carbon::parse($rkm->tanggal_akhir);
        // return $exam;
        $durasi = $tanggalAwal->diffInDays($tanggalAkhir);
        return view('kelasanalisis.create', compact('rkm', 'durasi', 'exam'));
    }

    public function store(Request $request)
    {
        // return $request->all();
        //validate form
        $data = $this->validate($request, [
            'harga_modul_regular'     => 'nullable',
            'harga_modul_regular_dollar'     => 'nullable',
            'kurs_dollar'     => 'nullable',
            'biaya_modul_regular'     => 'nullable',
            'biaya_modul_regular_dollar'     => 'nullable',
            'makan_siang'     => 'nullable',
            'coffee_break'     => 'nullable',
            'konsumsi'     => 'nullable',
            'souvenir_satu'     => 'nullable',
            'souvenir'     => 'nullable',
            'pc_pax'     => 'nullable',
            'pc_instruktur'     => 'nullable',
            'konsumsi_instruktur'     => 'nullable',
            'pc'     => 'nullable',
            'alat'     => 'nullable',
            'pa_hotel_akomodasi'     => 'nullable',
            'pa_hotel'     => 'nullable',
            'total_fee_instruktur'     => 'nullable',
            'total_harga_jual'     => 'nullable',
            'fee_instruktur'     => 'nullable',
            'nett_penjualan'     => 'nullable',
            'exam'     => 'nullable',
            'pax'     => 'required',
            'durasi'     => 'required',
            'id_rkm'     => 'required',
            'komentar'     => 'nullable',
        ]); 

        kelasanalisis::create($data);

        return redirect()->route('kelasanalisis.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }
    public function edit($id)
    {
        $post = kelasanalisis::with('rkm.materi', 'rkm.perusahaan')->where('id_rkm', $id)->first();
        $post->total_harga_jual = (float) $post->total_harga_jual;
        $post->harga_modul_regular = (float) $post->harga_modul_regular;
        $post->harga_modul_regular_dollar = (float) $post->harga_modul_regular_dollar;
        $post->kurs_dollar = (float) $post->kurs_dollar;
        $post->biaya_modul_regular = (float) $post->biaya_modul_regular;
        $post->biaya_modul_regular_dollar = (float) $post->biaya_modul_regular_dollar;
        $post->makan_siang = (float) $post->makan_siang;
        $post->coffee_break = (float) $post->coffee_break;
        $post->konsumsi = (float) $post->konsumsi;
        $post->souvenir_satu = (float) $post->souvenir_satu;
        $post->souvenir = (float) $post->souvenir;
        $post->pa_hotel = (float) $post->pa_hotel;
        $post->exam = (float) $post->exam;
        $post->pc_pax = (float) $post->pc_pax;
        $post->pc_instruktur = (float) $post->pc_instruktur;
        $post->pc = (float) $post->pc;
        $post->alat = (float) $post->alat;
        $post->fee_instruktur = (float) $post->fee_instruktur;
        $post->total_fee_instruktur = (float) $post->total_fee_instruktur;
        $post->nett_penjualan = (float) $post->nett_penjualan;

        // return $post;
        return view('kelasanalisis.edit', compact('post'));
    }
    public function update(Request $request, $id)
    {
        // dd($request->all());
        $data = $this->validate($request, [
            'harga_modul_regular'     => 'nullable',
            'harga_modul_regular_dollar'     => 'nullable',
            'kurs_dollar'     => 'nullable',
            'biaya_modul_regular'     => 'nullable',
            'biaya_modul_regular_dollar'     => 'nullable',
            'makan_siang'     => 'nullable',
            'coffee_break'     => 'nullable',
            'konsumsi'     => 'nullable',
            'souvenir_satu'     => 'nullable',
            'souvenir'     => 'nullable',
            'pc_pax'     => 'nullable',
            'pc_instruktur'     => 'nullable',
            'pc'     => 'nullable',
            'alat'     => 'nullable',
            'pa_hotel_akomodasi'     => 'nullable',
            'pa_hotel'     => 'nullable',
            'total_fee_instruktur'     => 'nullable',
            'total_harga_jual'     => 'nullable',
            'fee_instruktur'     => 'nullable',
            'nett_penjualan'     => 'nullable',
            'exam'     => 'nullable',
            'pax'     => 'required',
            'durasi'     => 'required',
            'id_rkm'     => 'required',
            'komentar'     => 'nullable',
        ]); 

        $post = kelasanalisis::findOrFail($id);

            $post->update($data);

        return redirect()->route('kelasanalisis.index')->with(['success' => 'Data Berhasil Diubah!']);
    }

    public function getRkmDataByMonthAndWeek($year, $month, $week)
    {
        Carbon::setLocale('id');
    
        // Map Indonesian months to English months
        $indonesianToEnglishMonths = [
            'Januari' => 'January',
            'Februari' => 'February',
            'Maret' => 'March',
            'April' => 'April',
            'Mei' => 'May',
            'Juni' => 'June',
            'Juli' => 'July',
            'Agustus' => 'August',
            'September' => 'September',
            'Oktober' => 'October',
            'November' => 'November',
            'Desember' => 'December',
        ];
    
        // Convert the Indonesian month to English
        $englishMonth = $indonesianToEnglishMonths[$month] ?? null;
    
        if (!$englishMonth) {
            return new PostResource(false, "Bulan $month tidak valid.", null);
        }
    
        // Create date range for the specified month and year
        $startOfMonth = CarbonImmutable::create($year, Carbon::parse($englishMonth)->month, 1);
        $endOfMonth = $startOfMonth->endOfMonth();
    
        // Fetch RKM data for the specified year and status
        $rkm = RKM::with(['materi', 'analisisrkm', 'analisisrkm.analisisrkmmingguan'])
            ->where('status', '0')
            ->whereYear('tanggal_awal', $year)
            ->get();
    
        // Filter data based on the translated month
        $rkmFiltered = $rkm->filter(function ($item) use ($year, $englishMonth) {
            $tanggalAwal = Carbon::parse($item->tanggal_awal);
            return $tanggalAwal->year == $year && $tanggalAwal->format('F') == $englishMonth;
        });
    
        if ($rkmFiltered->isEmpty()) {
            return new PostResource(false, "Tidak ada data RKM untuk bulan $month", null);
        }
    
        // Initialize weekly data processing
        $startOfWeek = $startOfMonth->startOfWeek(Carbon::MONDAY);
        $weekNumber = 1;
    
        while ($startOfWeek->lte($endOfMonth)) {
            $endOfWeek = $startOfWeek->copy()->endOfWeek(Carbon::SUNDAY);
    
            if ($weekNumber == $week) {
                $start = $startOfWeek->format('Y-m-d');
                $end = $endOfWeek->format('Y-m-d');
    
                // Filter RKM data for the current week
                $weekData = $rkmFiltered->filter(function ($item) use ($start, $end) {
                    return Carbon::parse($item->tanggal_awal)->between($start, $end);
                });
    
                if ($weekData->isEmpty()) {
                    return new PostResource(false, "Tidak ada data RKM untuk $month Minggu $week", null);
                }
    
                $fixcost = [];
                $profit = [];
    
                $formattedWeekData = $weekData->map(function ($item) use (&$fixcost, &$profit) {
                    $status = $item->analisisrkm ? 'Hijau' : 'Merah';
                    $tanggalAwal = Carbon::parse($item->tanggal_awal);
                    $tanggalAkhir = Carbon::parse($item->tanggal_akhir);
                    $total_harga_jual = floatval($item->harga_jual) * intval($item->pax);
                
                    $analisisRkmData = $item->analisisrkm ? $item->analisisrkm->toArray() : null;
                    $analisisRkmmingguanData = $item->analisisrkm && $item->analisisrkm->analisisrkmmingguan ? $item->analisisrkm->analisisrkmmingguan : null;
                
                    if ($analisisRkmmingguanData) {
                        foreach ($analisisRkmmingguanData as $data) {
                            $fix = $data['fixcost'] ?? null;
                            $prof = $data['profit'] ?? null;
                
                            if ($fix !== null) {
                                $fixcost[] = $fix;
                            }
                
                            if ($prof !== null) {
                                $profit[] = $prof;
                            }
                        }
                    }
                
                    return [
                        'id'              => $item->id,
                        'nama_materi'     => $item->materi->nama_materi,
                        'pax'             => $item->pax,
                        'harga_jual'      => $item->harga_jual,
                        'total_harga_jual'=> $total_harga_jual,
                        'tanggal_awal'    => $tanggalAwal->translatedFormat('d F Y'),
                        'tanggal_akhir'   => $tanggalAkhir->translatedFormat('d F Y'),
                        'durasi'          => $tanggalAwal->diffInDays($tanggalAkhir) + 1,
                        'status'          => $status,
                        'analisisrkm'     => $analisisRkmData,
                        'analisisrkmmingguan' => [
                            'data' => $analisisRkmmingguanData,
                        ],
                    ];
                })->values();
    
                $allHijau = $weekData->every(function ($item) {
                    return $item->analisisrkm ? 'Hijau' : 'Merah' == 'Hijau';
                });
    
                $rkmfull = $allHijau ? 'ok' : 'pending';
    
                return new PostResource(true, "Data RKM untuk $month Minggu $week", [
                    'rkmfull' => $rkmfull,
                    'fixcost' => $fixcost,
                    'profit'  => $profit,
                    'data'    => $formattedWeekData,
                ]);
            }
    
            $startOfWeek = $startOfWeek->addWeek();
            $weekNumber++;
        }
    
        return new PostResource(false, "Tidak ada data RKM untuk $month Minggu $week", null);
    }
    

    public function postAnalisisMingguan(Request $request)
    {
        // return $request->all();
        $data = $request->all();
        // Convert fixcost and profit to decimal format
        $fixcost = str_replace(['.', ','], '', $data['fixcost']);
        $profit = str_replace(['.', ','], '', $data['profit']);
        // dd($profit);
        foreach ($data['id_kelasanalisis'] as $index => $id_kelasanalisis) {
            $kelasAnalisis = new analisisrkmmingguan();
            $kelasAnalisis->id_kelasanalisis = $id_kelasanalisis;
            $kelasAnalisis->tahun = $data['tahun'][$index];
            $kelasAnalisis->bulan = $data['bulan'][$index];
            $kelasAnalisis->minggu = $data['minggu'][$index];
            $kelasAnalisis->nama_materi = $data['nama_materi'][$index];
            $kelasAnalisis->nett_penjualan = $data['nett_penjualan'][$index];
            $kelasAnalisis->fixcost = $fixcost;
            $kelasAnalisis->profit = $profit;
            $kelasAnalisis->save();
        }


        return redirect()->route('kelasanalisis.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }

    public function updateAnalisisMingguan(Request $request)
    {
        $data = $request->all();

        // Convert fixcost and profit to numeric format
        $fixcost = isset($data['fixcost']) ? (float) str_replace(['.', ','], '', $data['fixcost']) : null;
        $profit = isset($data['profit']) ? (float) str_replace(['.', ','], '', $data['profit']) : null;

        foreach ($data['id_kelasanalisis'] as $index => $id_kelasanalisis) {
            // Retrieve existing record to update it
            $kelasAnalisis = analisisrkmmingguan::where('id_kelasanalisis', $id_kelasanalisis);

            // Update fields for the existing record
            $kelasAnalisis->update([
                'nett_penjualan' => (float) $request->nett_penjualan[$index],
                'fixcost' => $fixcost,
                'profit' => $profit,
            ]);
        }

        return redirect()->route('kelasanalisis.index')->with(['success' => 'Data Berhasil Diupdate!']);
    }


    public function getAnalisisMargin($year, $monthName)
    {
        Carbon::setLocale('id');

        // Map month names in Indonesian to numbers
        $months = [
            'Januari' => 1,
            'Februari' => 2,
            'Maret' => 3,
            'April' => 4,
            'Mei' => 5,
            'Juni' => 6,
            'Juli' => 7,
            'Agustus' => 8,
            'September' => 9,
            'Oktober' => 10,
            'November' => 11,
            'Desember' => 12,
        ];

        // Get the month number based on the name provided
        $month = $months[$monthName] ?? null;

        if (!$month) {
            return new PostResource(false, "Bulan tidak valid: $monthName", null);
        }

        // Get all RKM data for the specified year
        $rkm = RKM::with(['materi', 'analisisrkm', 'analisisrkm.analisisrkmmingguan'])
            ->where('status', '0')
            ->get();

        // Filter data by year
        $rkmFiltered = $rkm->filter(function ($item) use ($year) {
            $tanggalAwal = Carbon::parse($item->tanggal_awal);
            return $tanggalAwal->year == $year;
        });

        // Group data by month number
        $groupedByMonth = $rkmFiltered->groupBy(function ($item) {
            return Carbon::parse($item->tanggal_awal)->month;
        });

        // Retrieve data for the specified month number
        $monthData = $groupedByMonth->get($month);

        if (!$monthData) {
            return new PostResource(false, "Tidak ada data RKM untuk bulan $monthName", null);
        }

        // Get the first and last days of the month
        $firstDayOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $lastDayOfMonth = $firstDayOfMonth->copy()->endOfMonth();
        $totalWeeksInMonth = $lastDayOfMonth->weekOfMonth;

        // Initialize a total profit accumulator
        $totalProfit = 0;
        $firstFixCost = $monthData->pluck('analisisrkm.analisisrkmmingguan.*.fixcost')
                                    ->flatten()
                                    ->filter()
                                    ->first() ?? 0;
        // Group month data by week and calculate total profit for each week
        $groupedByWeek = $monthData->groupBy(function ($item) {
            return Carbon::parse($item->tanggal_awal)->weekOfMonth;
        });

        // Loop through each week in the month and calculate profit
        $weeklyProfits = collect(range(1, $totalWeeksInMonth))->mapWithKeys(function ($weekNumber) use ($groupedByWeek, &$totalProfit, $firstFixCost) {
            $weekItems = $groupedByWeek->get($weekNumber, collect());

            // Initialize total profit for the week
            $profit = 0;

            if ($weekItems->isNotEmpty()) {
                // Calculate the total profit for each item in the week
                foreach ($weekItems as $item) {
                    $analisisRkmmingguanData = $item->analisisrkm->analisisrkmmingguan ?? null;
                    if ($analisisRkmmingguanData) {
                        foreach ($analisisRkmmingguanData as $data) {
                            $profit = (float)($data['profit'] ?? '0');
                        }
                    }
                }
            } else {
                // Use the first fixcost value with a negative sign if the week is empty
                $profit = -abs((float)$firstFixCost);
            }

            // Accumulate the weekly profit into the total profit
            $totalProfit += $profit;

            return ['Minggu ' . $weekNumber => $profit];
        });

        // Include the total profit at the end of the response
        return new PostResource(true, "Data Profit Bulanan untuk $monthName Tahun $year", [
            'tahun'       => $year,
            'bulan'       => $monthName,
            'weeklyProfit' => $weeklyProfits,
            'totalProfit' => $totalProfit,  // Include the accumulated total profit
        ]);
    }


    public function sinkronDataKelasAnalisis(){
        $data = kelasanalisis::with('rkm')->get();
        foreach ($data as $value) {
            $rkm = RKM::findOrFail($value->id_rkm);
            $pax = $rkm->pax;
            $tanggalAwal = $rkm->tanggal_awal;
            $tanggalAkhir = $rkm->tanggal_akhir;
            $durasihari = Carbon::parse($tanggalAkhir)->diffInDays($tanggalAwal);
            $durasi = $durasihari + 1;
            $total_harga_jual = $rkm->harga_jual * $pax;
            $kelas = $rkm->metode_kelas;
            if($value->pax != $pax || $value->durasi != $durasi || $value->total_harga_jual != $total_harga_jual){
                $biaya_modul_regular = $value->harga_modul_regular * $pax;
                $biaya_dollar = $value->harga_modul_regular_dollar * $pax;
                $biaya_modul_regular_dollar = $biaya_dollar * $value->kurs_dollar;
                $souvenir = $value->souvenir_satu * $pax;
                $konsumsi_inst = $pax + $value->konsumsi_instruktur;
                $makan_siang = ($durasi * $konsumsi_inst) * $value->makan_siang;
                $coffee_break = ($durasi * $konsumsi_inst) * $value->coffee_break;
                $konsumsi = $makan_siang + $coffee_break;
                if($kelas == 'Virtual'){
                    $pc = $value->pc_pax * $durasi * $value->pc_instrukutur;
                }else{
                    $pc = $value->pc_pax * $durasi * ($pax + $value->pc_instrukutur);
                }
                if($value->fee_instruktur == '0.00'){
                    $total_fee_instruktur = $value->total_fee_instruktur;
                }else{
                    $total_fee_instruktur = $value->fee_instruktur * 5 * $durasi;
                }
                $nett_penjualan = $total_harga_jual - ($total_fee_instruktur + $pc + $souvenir + $konsumsi + $biaya_modul_regular + $biaya_modul_regular_dollar + $value->alat + $value->pa_hotel + $value->exam);

                $value->update([
                    'biaya_modul_regular' => $biaya_modul_regular,
                    'biaya_modul_regular_dollar' => $biaya_modul_regular_dollar,
                    'pax' => $pax,
                    'durasi' => $durasi,
                    'total_harga_jual' => $total_harga_jual,
                    'souvenir' => $souvenir,
                    'konsumsi' => $konsumsi,
                    'total_fee_instruktur' => $total_fee_instruktur,
                    'pc' => $pc,
                    'nett_penjualan' => $nett_penjualan,
                ]);
            }else{
                continue;
            }
            // dd($durasi + 1);
        }
        return response()->json([
            'success' => true,
            'message' => 'Data telah Disinkronkan!',
        ]);
    }
    
}

