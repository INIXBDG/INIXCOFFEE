<?php

namespace App\Http\Controllers;

use App\Models\AbsensiKaryawan;
use App\Models\Nilaifeedback;
use App\Models\Peserta;
use App\Models\Registrasi;
use App\Models\RKM;
use App\Models\souvenirpeserta;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class ChartController extends Controller
{
    public function getPenjualanPerBulan($year) 
    {
        // Mengambil total harga_jual per bulan untuk tahun tertentu
        $data = RKM::selectRaw('MONTH(tanggal_awal) as bulan, SUM(CAST(harga_jual AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total_penjualan')
            ->whereYear('tanggal_awal', $year) // Filter berdasarkan tahun yang diberikan
            ->where('status', '0') // Menyertakan kondisi status
            ->groupBy('bulan') // Kelompokkan berdasarkan bulan
            ->orderBy('bulan')
            ->get();

        // Map angka bulan menjadi nama bulan
        $data = $data->map(function ($item) {
            $item->bulan = $this->getNamaBulan($item->bulan); // Konversi angka bulan ke nama bulan
            return $item;
        });

        return response()->json([
            'success' => true,
            'message' => 'List Penjualan per Bulan',
            'data' => $data,
        ]);
    }

    // Fungsi untuk mengonversi angka bulan menjadi nama bulan
    private function getNamaBulan($bulan) 
    {
        $namaBulan = [
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

        return $namaBulan[$bulan] ?? 'Tidak Diketahui';
    }

    public function getPerSalesPerTahun($year) 
    {
        // Mengambil total harga_jual per sales_key untuk tahun tertentu
        $data = RKM::selectRaw('sales_key, SUM(CAST(harga_jual AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total_penjualan')
            ->whereYear('tanggal_awal', $year) // Filter berdasarkan tahun yang diberikan
            ->where('status', '0') // Menyertakan kondisi status
            ->groupBy('sales_key') // Kelompokkan berdasarkan sales_key
            ->orderBy('sales_key') // Urutkan berdasarkan sales_key
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Total Penjualan per Sales untuk Tahun ' . $year,
            'data' => $data,
        ]);
    }
    public function getPerSalesPerQuartal($year) 
    {
        // Mengambil total harga_jual per sales_key per kuartal untuk tahun tertentu
        $data = RKM::selectRaw('sales_key, quartal, SUM(CAST(harga_jual AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total_penjualan')
            ->whereYear('tanggal_awal', $year) // Filter berdasarkan tahun yang diberikan
            ->where('status', '0') // Menyertakan kondisi status
            ->groupBy('sales_key', 'quartal') // Kelompokkan berdasarkan sales_key dan quartal
            ->orderBy('sales_key') // Urutkan berdasarkan sales_key
            ->orderBy('quartal') // Urutkan berdasarkan kuartal
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Total Penjualan per Sales per Triwulan untuk Tahun ' . $year,
            'data' => $data,
        ]);
    }

    public function getAnalisisMarginByYear($year)
    {
        Carbon::setLocale('id');

        // Ambil semua data RKM untuk tahun yang ditentukan
        $rkm = RKM::with(['materi', 'analisisrkm', 'analisisrkm.analisisrkmmingguan'])
            ->where('status', '0')
            ->get();

        // return $rkm;

        // Filter data berdasarkan tahun
        $rkmFiltered = $rkm->filter(function ($item) use ($year) {
            $tanggalAwal = Carbon::parse($item->tanggal_awal);
            return $tanggalAwal->year == $year;
        });

        // Grup data berdasarkan bulan dalam setahun
        $groupedByMonth = $rkmFiltered->groupBy(function ($item) {
            return Carbon::parse($item->tanggal_awal)->month;
        });

        // Inisialisasi akumulator untuk total profit tahunan
        $totalYearlyProfit = 0;
        $monthlyProfits = [];

        // Loop dari 1 hingga 12 untuk mencakup semua bulan
        foreach (range(1, 12) as $month) {
            // Cek apakah ada data untuk bulan ini
            $monthData = $groupedByMonth->get($month, collect());

            // Jika tidak ada data, beri nilai profit bulanan 0
            if ($monthData->isEmpty()) {
                $monthlyProfits[] = [
                    'bulan' => $month,
                    'weeklyProfits' => collect(), // Mingguan kosong
                    'totalMonthlyProfit' => 0
                ];
                continue;
            }

            // Jika ada data untuk bulan ini, hitung profit bulanan
            $firstDayOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $lastDayOfMonth = $firstDayOfMonth->copy()->endOfMonth();
            $totalWeeksInMonth = $lastDayOfMonth->weekOfMonth;

            // Inisialisasi profit bulanan
            $totalMonthlyProfit = 0;
            $firstFixCost = $monthData->pluck('analisisrkm.analisisrkmmingguan.*.fixcost')
                                        ->flatten()
                                        ->filter()
                                        ->first() ?? 0;

            // Grup data bulanan berdasarkan minggu dalam bulan
            $groupedByWeek = $monthData->groupBy(function ($item) {
                return Carbon::parse($item->tanggal_awal)->weekOfMonth;
            });

            // Loop setiap minggu dalam bulan dan hitung profit mingguan
            $weeklyProfits = collect(range(1, $totalWeeksInMonth))->mapWithKeys(function ($weekNumber) use ($groupedByWeek, &$totalMonthlyProfit, $firstFixCost) {
                $weekItems = $groupedByWeek->get($weekNumber, collect());

                // Inisialisasi total profit untuk minggu tersebut
                $profit = 0;

                if ($weekItems->isNotEmpty()) {
                    // Hitung total profit untuk setiap item dalam minggu tersebut
                    foreach ($weekItems as $item) {
                        // dd($item);
                        $analisisRkmmingguanData = $item->analisisrkm->analisisrkmmingguan ?? null;
                        if ($analisisRkmmingguanData) {
                            foreach ($analisisRkmmingguanData as $data) {
                                // dd($data);
                                $profit = (float)($data['profit'] ?? '0');
                                
                            }
                        }
                    }
                    
                } else {
                    // Gunakan nilai fixcost pertama dengan tanda negatif jika minggu kosong
                    $profit = -abs((float)$firstFixCost);
                }
                // Tambahkan profit mingguan ke profit bulanan
                $totalMonthlyProfit += $profit;

                return ['Minggu ' . $weekNumber => $profit];
            });

            // Simpan hasil profit mingguan dan bulanan untuk bulan ini
            $monthlyProfits[] = [
                'bulan' => $month,
                'weeklyProfits' => $weeklyProfits,
                'totalMonthlyProfit' => $totalMonthlyProfit
            ];

            // Tambahkan profit bulanan ke total profit tahunan
            $totalYearlyProfit += $totalMonthlyProfit;
        }

        return response()->json([
            'success' => true,
            'message' => "Data Profit Tahunan untuk Tahun $year",
            'data' => [
                'tahun' => $year,
                'monthlyProfits' => $monthlyProfits,
                'totalYearlyProfit' => $totalYearlyProfit,
            ]
        ]);
    }

    public function getAbsensiYearly($year)
    {
        $leaderboard = AbsensiKaryawan::select(
            'id_karyawan',
            DB::raw('SUM(TIME_TO_SEC(waktu_keterlambatan) / 60) as total_keterlambatan'),
        )
        ->with('karyawan') // Load karyawan relationship to get employee details like name if needed
        ->whereYear('tanggal', $year)
        ->groupBy('id_karyawan') // Group by id_karyawan only, to aggregate multiple records per employee
        ->orderBy('total_keterlambatan', 'desc')
        ->get();    
        $leaderboard = $leaderboard->filter(function ($item) {
            // Hanya sertakan jika `total_keterlambatan` bukan 0
            return $item->total_keterlambatan > 0;
        })->values();
        return response()->json([
            'success' => true,
            'message' => "Data Absen Tahunan (Dalam Menit) untuk Tahun $year ",
            'data' => $leaderboard
        ]);
    }

    public function getTabInix($year)
    {
        $total_kelas = RKM::whereYear('tanggal_awal', $year)->where('status', '0')->count();
        $offline = RKM::whereYear('tanggal_awal', $year)->where('metode_kelas', 'Offline')->where('status', '0')->count();
        $virtual = RKM::whereYear('tanggal_awal', $year)->where('metode_kelas', 'Virtual')->where('status', '0')->count();
        $jumlah_peserta = Registrasi::whereYear('created_at', $year)->count();
        $jumlah_peserta_perbulan = Registrasi::whereYear('created_at', $year)
            ->select(DB::raw('MONTH(created_at) as bulan'), DB::raw('COUNT(*) as jumlah_data'))
            ->groupBy('bulan')
            ->get()
            ->avg('jumlah_data') ?? 0;
    
        $ratarata_kelas_perbulan = RKM::whereYear('created_at', $year)
            ->select(DB::raw('MONTH(tanggal_awal) as bulan'), DB::raw('COUNT(*) as jumlah_data'))
            ->where('status', '0')
            ->groupBy(DB::raw('MONTH(tanggal_awal)'))
            ->get()
            ->avg('jumlah_data') ?? 0;
        
    
        $feedbacks = Nilaifeedback::with('rkm', 'rkm.instruktur')->whereYear('created_at', $year)->get();
        $totalFeedbacks = $feedbacks->count();
        $ratarata_feedback = [
            'materi' => $feedbacks->avg(fn($feedback) => ($feedback->M1 + $feedback->M2 + $feedback->M3 + $feedback->M4) / 4) ?? 0,
            'pelayanan' => $feedbacks->avg(fn($feedback) => ($feedback->P1 + $feedback->P2 + $feedback->P3 + $feedback->P4 + $feedback->P5 + $feedback->P6 + $feedback->P7) / 7) ?? 0,
            'fasilitas' => $feedbacks->avg(fn($feedback) => ($feedback->F1 + $feedback->F2 + $feedback->F3 + $feedback->F4 + $feedback->F5) / 5) ?? 0,
            'instruktur' => $feedbacks->avg(fn($feedback) => ($feedback->I1 + $feedback->I2 + $feedback->I3 + $feedback->I4 + $feedback->I5 + $feedback->I6 + $feedback->I7 + $feedback->I8) / 8) ?? 0,
        ];
    
        $ratarata_feedback = array_map(fn($value) => round($value, 1), $ratarata_feedback);
    
        $sales_terbaik = RKM::whereYear('tanggal_awal', $year)
            ->where('status', '0')
            ->whereHas('sales', fn($query) => $query->where('jabatan', '!=', 'SPV Sales'))
            ->with('sales')
            ->select('sales_key', DB::raw('SUM(harga_jual) as total_penjualan'))
            ->groupBy('sales_key')
            ->orderByDesc('total_penjualan')
            ->first() ?? [];
    
        $instruktur_terbaik = RKM::whereYear('tanggal_awal', $year)
            ->where('status', '0')
            ->whereHas('instruktur', fn($query) => $query->where('jabatan', '!=', 'Education Manager'))
            ->with('instruktur')
            ->select('instruktur_key', DB::raw('count(id) as total_mengajar'))
            ->groupBy('instruktur_key')
            ->orderByDesc('total_mengajar')
            ->get();
        // return $instruktur_terbaik;
        $feedbacks = Nilaifeedback::with(['rkm', 'rkm.instruktur'])
            ->whereYear('created_at', $year)
            ->whereHas('rkm.instruktur', fn($query) => $query->where('jabatan', '!=', 'Education Manager'))
            ->get();
        
        // Mengelompokkan feedback berdasarkan instruktur_key
        $inst_terbaik = $feedbacks->groupBy(fn($feedback) => $feedback->rkm->instruktur_key ?? 'unknown');
        
        // Menghitung rata-rata feedback untuk setiap instruktur
        $rata_rata_inst = $inst_terbaik->mapWithKeys(function ($group, $instruktur_key) {
            $avg = $group->avg(fn($feedback) => ($feedback->I1 + $feedback->I2 + $feedback->I3 + $feedback->I4 + $feedback->I5 + $feedback->I6 + $feedback->I7 + $feedback->I8) / 8) ?? 0;
            return [$instruktur_key => $avg];
        });
        
        // Mendapatkan instruktur dengan jumlah mengajar terbanyak terlebih dahulu
        $max_total_mengajar = $instruktur_terbaik->first()->total_mengajar ?? 0;
        $top_instruktur = $instruktur_terbaik->filter(fn($instruktur) => $instruktur->total_mengajar === $max_total_mengajar);
        
        // Jika ada lebih dari satu instruktur dengan jumlah mengajar tertinggi, pilih yang memiliki rata-rata feedback tertinggi
        $selected_instruktur = $top_instruktur->sortByDesc(fn($instruktur) => $rata_rata_inst[$instruktur->instruktur_key] ?? 0)->first();
        
        // Mengambil detail instruktur terbaik
        $in = $selected_instruktur->instruktur_key ?? 0;
        $instruktur_key = $selected_instruktur->instruktur_key ?? 'Tidak tersedia';
        $total_mengajar = $selected_instruktur->total_mengajar ?? 0;
        $feedback = $rata_rata_inst[$in] ?? 0;
        $nama_lengkap = $selected_instruktur->instruktur->nama_lengkap ?? 'Tidak tersedia';
        $foto = $selected_instruktur->instruktur->foto ?? 'Tidak tersedia';
        
        $leaderboard = AbsensiKaryawan::select(
                'id_karyawan',
                DB::raw('SUM(TIME_TO_SEC(waktu_keterlambatan)) as total_keterlambatan'),
                DB::raw('MAX(TIME_TO_SEC(waktu_keterlambatan)) as highest_keterlambatan'), // Fetch max lateness
                DB::raw('MIN(foto) as foto')
            )
            ->with('karyawan') // Load karyawan relationship to get employee details like name if needed
            ->whereYear('tanggal', $year)
            ->whereHas('karyawan', function($query) {
                $query->whereNotIn('jabatan', ['Office boy', 'Driver']);
            })
            ->groupBy('id_karyawan') // Group by id_karyawan only, to aggregate multiple records per employee
            ->orderBy('total_keterlambatan', 'desc')
            ->limit(3) // Limit results to top 10 employees
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
            // return $leaderboard;

                $itsm_nama   = 'Static ITSM';
    $itsm_foto   = 'itsm.jpg'; // simpan di storage/posts
    $office_nama = 'Static Office';
    $office_foto = 'office.jpg'; // simpan di storage/posts


    return response()->json([
        'success' => true,
        'message' => "Data Inixindo dalam angka Tahun $year",
        'data' => [
            'tahun' => $year,
            'total_kelas' => $total_kelas,
            'jumlah_peserta' => $jumlah_peserta,
            'offline' => $offline,
            'virtual' => $virtual,
            'ratarata_kelas_perbulan' => round($ratarata_kelas_perbulan, 1),
            'jumlah_peserta_perbulan' => round($jumlah_peserta_perbulan, 1),
            'ratarata_feedback' => $ratarata_feedback,
            'sales_terbaik' => $sales_terbaik ?: ['sales_key' => 'Tidak tersedia', 'total_penjualan' => 0],
            'instruktur_terbaik' => [
                'instruktur_key' => $instruktur_key,
                'total_mengajar' => $total_mengajar,
                'feedback' => round($feedback, 1),
                'instruktur' => [
                    'nama_lengkap' => $nama_lengkap,
                    'foto' => $foto,
                ],
            ],
            'itsm_terbaik' => [
                'itsm_key' => 'static',
                'total_mengajar' => 99,
                'feedback' => 4.9,
                'itsm' => [
                    'nama_lengkap' => $itsm_nama,
                    'foto' => $itsm_foto,
                ],
            ],
            'office_terbaik' => [
                'office_key' => 'static',
                'total_mengajar' => 88,
                'feedback' => 4.8,
                'office' => [
                    'nama_lengkap' => $office_nama,
                    'foto' => $office_foto,
                ],
            ],
            'keterlambatan' => $leaderboard,
        ]
    ]);
    }

    public function getSouvenirYearly($year) 
    {
        // Get data grouped by id_souvenir with count
        $data = souvenirpeserta::with('souvenir') // Ensure 'souvenir' is loaded
            ->whereYear('created_at', $year)
            ->get()
            ->groupBy('id_souvenir') // Group by id_souvenir
            ->map(function ($items) {
                // Get the first item from the group to access the souvenir
                $souvenir = $items->first()->souvenir; // Access the 'souvenir' relation from the first item
                $count = $items->count();

                if (!$souvenir) {
                    // Handle case where 'souvenir' is null
                    return [
                        'nama_souvenir' => 'Unknown', // Provide a fallback name
                        'count' => $count,
                    ];
                }

                return [
                    'nama_souvenir' => $souvenir->nama_souvenir,
                    'count' => $count,
                ];
            })
            ->values(); // Reset array keys for JSON output

        return response()->json([
            'success' => true,
            'message' => 'List Souvenir',
            'data' => $data
        ], 200);
    }


    public function getTotalFeedbackPerbulan($year, $month)
    {
        // Fetch feedback data for the specified year and month
        $data = Nilaifeedback::with('regist', 'rkm.instruktur') // Ensure relationships are loaded
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->get();

        // Initialize result array
        $result = [];

        // Process each feedback item
        foreach ($data as $item) {
            // Calculate averages for Instruktur 1
            $instruktur1 = collect([ 
                $item->I1, $item->I2, $item->I3, $item->I4,
                $item->I5, $item->I6, $item->I7, $item->I8
            ])->filter()->map(fn($value) => (float) $value);

            // Calculate averages for Instruktur 2
            $instruktur2 = collect([
                $item->I1b, $item->I2b, $item->I3b, $item->I4b,
                $item->I5b, $item->I6b, $item->I7b, $item->I8b
            ])->filter()->map(fn($value) => (float) $value);

            // Calculate averages for Asisten
            $asisten = collect([
                $item->I1as, $item->I2as, $item->I3as, $item->I4as,
                $item->I5as, $item->I6as, $item->I7as, $item->I8as
            ])->filter()->map(fn($value) => (float) $value);

            // Compute overall averages
            $instruktur1_avg = $instruktur1->isNotEmpty() ? $instruktur1->avg() : null;
            $instruktur2_avg = $instruktur2->isNotEmpty() ? $instruktur2->avg() : null;
            $asisten_avg = $asisten->isNotEmpty() ? $asisten->avg() : null;

            // Get the instructor's code
            $kode_karyawan = optional($item->rkm->instruktur)->kode_karyawan ?? 'Unknown';

            // If the instructor code is valid, calculate the combined average
            if ($kode_karyawan !== 'Unknown') {
                $combinedAverage = collect([$instruktur1_avg, $instruktur2_avg, $asisten_avg])
                    ->filter(fn($value) => !is_null($value))
                    ->avg();

                // If there's a valid combined average, update the result array
                if (!is_null($combinedAverage)) {
                    if (isset($result[$kode_karyawan])) {
                        // If the instructor already exists, add to the average (handle multiple feedback)
                        $result[$kode_karyawan]['nilairatarata'] += $combinedAverage;
                        $result[$kode_karyawan]['count'] += 1;
                    } else {
                        // Otherwise, create a new entry for the instructor
                        $result[$kode_karyawan] = [
                            'instruktur_key' => $kode_karyawan,
                            'nilairatarata' => $combinedAverage,
                            'count' => 1
                        ];
                    }
                }
            }
        }

        // After processing all feedback, calculate the average for each instructor
        foreach ($result as $instruktur_key => $data) {
            // Calculate the final average for each instructor
            $result[$instruktur_key]['nilairatarata'] = round($data['nilairatarata'] / $data['count'], 2);
            // Remove the 'count' field, as it's no longer needed
            unset($result[$instruktur_key]['count']);
        }

        // Format the result as required
        $formattedResult = array_values($result);
        
        // Return the JSON response
        return response()->json([
            'success' => true,
            'message' => 'List Feedback dengan rata-rata dan pengelompokan per bulan ' . $month,
            'data' => $formattedResult
        ], 200);
    }



    public function getTotalMengajarPerbulan($year, $month)
    {
        // Fetch feedback data for the specified year and month
        $data = RKM::whereYear('tanggal_awal', $year)
            ->where('status', '0')
            ->whereMonth('tanggal_awal', $month)
            ->select('instruktur_key', DB::raw('count(id) as total_mengajar'))
            ->groupBy('instruktur_key')
            ->orderByDesc('total_mengajar')
            ->get();
        // Check if no data was found
        if ($data->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data yang tersedia',
                'data' => []
            ], 200);
        }

        // Return the JSON response
        return response()->json([
            'success' => true,
            'message' => 'List Total Mengajar per bulan ' . $month,
            'data' => $data
        ], 200);
    }

    public function getTotalMateriPerbulan($year, $month)
    {
        // Initialize the query
        $query = RKM::whereYear('tanggal_awal', $year)
            ->where('r_k_m_s.status', '0') // Specify the table for status
            ->join('materis', 'materis.id', '=', 'r_k_m_s.materi_key') // Join with 'materis'
            ->select('r_k_m_s.instruktur_key', 'materis.kategori_materi') // Select specific columns
            ->groupBy('r_k_m_s.instruktur_key', 'materis.kategori_materi') // Group by the selected columns
            ->orderBy('total_mengajar', 'desc'); // Order by the count

        // Check if the month is 'All' or a specific month
        if ($month === 'All') {
            $data = $query->selectRaw('r_k_m_s.instruktur_key, materis.kategori_materi, count(r_k_m_s.id) as total_mengajar')
                ->get();
            $message = 'List Kategori Materi per tahun';
        } else {
            $data = $query->whereMonth('tanggal_awal', $month) // Add filter for month
                ->selectRaw('r_k_m_s.instruktur_key, materis.kategori_materi, count(r_k_m_s.id) as total_mengajar')
                ->get();
            $message = 'List Kategori Materi per bulan ' . $month;
        }

        // Check if no data was found
        if ($data->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data yang tersedia',
                'data' => []
            ], 200);
        }

        // Return the JSON response
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], 200);
    }



    public function getTotalMengajarPerJenisMateriPerTahun($year, $month)
    {
        if($month === 'All'){
            $data = RKM::whereYear('tanggal_awal', $year)
                ->where('r_k_m_s.status', '0') // Perbaikan: tentukan tabel untuk kolom 'status'
                ->join('materis', 'materis.id', '=', 'r_k_m_s.materi_key')
                ->select('r_k_m_s.instruktur_key', 'materis.kategori_materi', DB::raw('count(r_k_m_s.id) as total_mengajar'))
                ->groupBy('r_k_m_s.instruktur_key', 'materis.kategori_materi')
                ->orderByDesc('total_mengajar')
                ->get();

            $groupedData = $data->groupBy('kategori_materi')->map(function ($items) {
                return $items->map(function ($item) {
                    return [
                        'instruktur_key' => $item->instruktur_key,
                        'total_mengajar' => $item->total_mengajar
                    ];
                });
            });

            return response()->json([
                'success' => true,
                'message' => 'List Total Mengajar Per Jenis Materi per tahun',
                'data' => $groupedData
            ]);

        } else {
            $data = RKM::whereYear('tanggal_awal', $year)
                ->whereMonth('tanggal_awal', $month)
                ->where('r_k_m_s.status', '0') // Perbaikan: tentukan tabel untuk kolom 'status'
                ->join('materis', 'materis.id', '=', 'r_k_m_s.materi_key')
                ->select('r_k_m_s.instruktur_key', 'materis.kategori_materi', DB::raw('count(r_k_m_s.id) as total_mengajar'))
                ->groupBy('r_k_m_s.instruktur_key', 'materis.kategori_materi')
                ->orderByDesc('total_mengajar')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'List Kategori Materi Per bulan ' . $month,
                'data' => $data
            ], 200);
        }

        if ($data->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data yang tersedia',
                'data' => []
            ], 200);
        }
    }

    public function getAbsenPerbulan($year, $month)
    {
        if($month === 'All'){
            // Fetch data for the specified month and year
            $totalketerlambatan = AbsensiKaryawan::whereYear('tanggal', $year)
                ->with('karyawan')
                ->get();

            // Filter the records where 'waktu_keterlambatan' is greater than 1 minute
            $filteredData = $totalketerlambatan->filter(function ($item) {
                // Convert 'waktu_keterlambatan' from HH:MM:SS to total seconds
                $timeParts = explode(':', $item->waktu_keterlambatan);
                $seconds = ($timeParts[0] * 3600) + ($timeParts[1] * 60) + $timeParts[2];  // Convert to total seconds

                // Return true if the time is greater than 60 seconds
                return $seconds > 60;
            });

            // Group by 'id_karyawan' and count occurrences of lateness
            $latenessCount = $filteredData->groupBy('id_karyawan')->map(function ($items) {
                return [
                    'id_karyawan' => $items->first()->id_karyawan, // Take the 'id_karyawan' from the first record
                    'karyawan' => $items->first()->karyawan, // Take the 'id_karyawan' from the first record
                    'total_keterlambatan' => $items->count()         // Count the number of lateness entries for this 'id_karyawan'
                ];
            });

            // Convert the result to an array
            $latenessArray = $latenessCount->values()->all();
            if (empty($latenessArray)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data yang tersedia',
                ]);
            }
            return response()->json([
                'success' => true,
                'message' => 'Total Absen Keterlambatan Per Tahun',
                'data' => $latenessArray
            ]);
        }
        else{
            // Fetch data for the specified month and year
            $totalketerlambatan = AbsensiKaryawan::whereMonth('tanggal', $month)
                ->whereYear('tanggal', $year)
                ->with('karyawan')
                ->get();

            // Filter the records where 'waktu_keterlambatan' is greater than 1 minute
            $filteredData = $totalketerlambatan->filter(function ($item) {
                // Convert 'waktu_keterlambatan' from HH:MM:SS to total seconds
                $timeParts = explode(':', $item->waktu_keterlambatan);
                $seconds = ($timeParts[0] * 3600) + ($timeParts[1] * 60) + $timeParts[2];  // Convert to total seconds

                // Return true if the time is greater than 60 seconds
                return $seconds > 60;
            });

            // Group by 'id_karyawan' and count occurrences of lateness
            $latenessCount = $filteredData->groupBy('id_karyawan')->map(function ($items) {
                return [
                    'id_karyawan' => $items->first()->id_karyawan, // Take the 'id_karyawan' from the first record
                    'karyawan' => $items->first()->karyawan, // Take the 'id_karyawan' from the first record
                    'total_keterlambatan' => $items->count()         // Count the number of lateness entries for this 'id_karyawan'
                ];
            });

            // Convert the result to an array
            $latenessArray = $latenessCount->values()->all();
            if (empty($latenessArray)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data yang tersedia',
                ]);
            }
            return response()->json([
                'success' => true,
                'message' => 'Total Absen Keterlambatan Per bulan ' . $month,
                'data' => $latenessArray
            ], 200);
        }
        
        
    }

}
