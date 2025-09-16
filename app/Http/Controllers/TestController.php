<?php

namespace App\Http\Controllers;

use App\Models\AbsensiKaryawan;
use Illuminate\Http\Request;
use Carbon\CarbonImmutable;
use App\Models\RKM;
use Illuminate\Support\Facades\DB;
use App\Models\karyawan;
use App\Models\Materi;
use App\Models\Perusahaan;

class TestController extends Controller
{
    public function index()
    {
        $month = now()->month;
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
        return view('layouts.test', compact('leaderboard', 'totalketerlambatan', 'topKaryawan', 'remainingLeaderboard',));
    }

    public function exerkaemlama()
    {
        $start = '2024-03-04';
        $end = '2024-03-08';
        $rows = RKM::with(['materi', 'perusahaan'])
            ->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
            ->whereBetween('r_k_m_s.tanggal_awal', [$start, $end])
            ->whereBetween('r_k_m_s.tanggal_akhir', [$start, $end])
            ->select(
                'r_k_m_s.id',
                'r_k_m_s.materi_key',
                'r_k_m_s.ruang',
                'r_k_m_s.metode_kelas',
                'r_k_m_s.event',
                'r_k_m_s.tanggal_awal',
                DB::raw('GROUP_CONCAT(r_k_m_s.instruktur_key SEPARATOR ", ") AS instruktur_all'),
                DB::raw('GROUP_CONCAT(r_k_m_s.perusahaan_key SEPARATOR ", ") AS perusahaan_all'),
                DB::raw('GROUP_CONCAT(r_k_m_s.sales_key SEPARATOR ", ") AS sales_all'),
                DB::raw('GROUP_CONCAT(DISTINCT r_k_m_s.status ORDER BY r_k_m_s.status SEPARATOR ", ") AS status_all'),
                DB::raw('SUM(r_k_m_s.pax) AS total_pax')
            )
            ->groupBy('r_k_m_s.id', 'r_k_m_s.materi_key', 'r_k_m_s.ruang', 'r_k_m_s.metode_kelas', 'r_k_m_s.event', 'r_k_m_s.tanggal_awal')
            ->get();

        // Manipulasi hasil query
        $result = [];
        foreach ($rows as $row) {
            $materiKey = $row->materi_key;
            if (!isset($result[$materiKey])) {
                $result[$materiKey] = [
                    "id" => $row->id,
                    "materi_key" => $materiKey,
                    "nama_materi" => $row->materi->nama_materi,
                    "ruang" => $row->ruang,
                    "metode_kelas" => $row->metode_kelas,
                    "event" => $row->event,
                    "tanggal_awal" => $row->tanggal_awal,
                    "instruktur_all" => $row->instruktur_all,
                    "perusahaan_all" => $row->perusahaan->nama_perusahaan,
                    "sales_all" => $row->sales_all,
                    "status_all" => $row->status_all,
                    "total_pax" => $row->total_pax
                ];
            } else {
                // Jika sudah ada, tambahkan ke data yang sudah ada
                $result[$materiKey]['instruktur_all'] .= ', ' . $row->instruktur_all;
                $result[$materiKey]['perusahaan_all'] .= ', ' . $row->perusahaan->pluck('nama_perusahaan')->implode(', '); // Ambil nama perusahaan dari relasi
                $result[$materiKey]['sales_all'] .= ', ' . $row->sales_all;
                // Anda mungkin perlu melakukan manipulasi lain sesuai kebutuhan
            }
        }

        // Ubah array asosiatif ke array numerik
        $result = array_values($result);

        return response()->json($result);
    }

    public function exrkmlama(){
        $startDate = CarbonImmutable::create($year, $month, 1);
        $endDate = CarbonImmutable::create($year, $month, 1)->endOfMonth();
        $now = CarbonImmutable::now()->locale('id_ID');

        $monthRanges = [];
        $date = $startDate;

        while ($date->month <= $endDate->month && $date->year <= $endDate->year) {
            $startOfMonth = $date->startOfMonth();
            $endOfMonth = $date->endOfMonth();

            $weekRanges = [];
            $startOfWeek = $startOfMonth->startOfWeek();
            while ($startOfWeek->lte($endOfMonth)) {
                $endOfWeek = $startOfWeek->copy()->endOfWeek();
                $start = $startOfWeek->format('Y-m-d');
                $end = $endOfWeek->format('Y-m-d');
                $startOfWeek = $startOfWeek->addWeek();
                $rows = RKM::with(['materi'])
                    ->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
                    ->whereBetween('tanggal_awal', [$start, $end])
                    ->whereBetween('tanggal_akhir', [$start, $end])
                    ->select('r_k_m_s.materi_key', 'r_k_m_s.ruang', 'r_k_m_s.metode_kelas', 'r_k_m_s.event', 'r_k_m_s.tanggal_awal',
                        DB::raw('GROUP_CONCAT(r_k_m_s.instruktur_key SEPARATOR ", ") AS instruktur_all'),
                        DB::raw('GROUP_CONCAT(r_k_m_s.perusahaan_key SEPARATOR ", ") AS perusahaan_all'),
                        DB::raw('GROUP_CONCAT(r_k_m_s.sales_key SEPARATOR ", ") AS sales_all'),
                        DB::raw('GROUP_CONCAT(DISTINCT r_k_m_s.status ORDER BY r_k_m_s.status SEPARATOR ", ") AS status_all'),
                        DB::raw('SUM(r_k_m_s.pax) AS total_pax'))
                    ->groupBy('r_k_m_s.materi_key', 'r_k_m_s.ruang', 'r_k_m_s.metode_kelas', 'r_k_m_s.event', 'r_k_m_s.tanggal_awal')
                    ->get();

                foreach ($rows as $row) {
                    if ($row->instruktur_all == null) {
                        $sales_ids = explode(', ', $row->sales_all);
                        $perusahaan_ids = explode(', ', $row->perusahaan_all);
                        $row->sales = Karyawan::whereIn('kode_karyawan', $sales_ids)->get();
                        $row->perusahaan = Perusahaan::whereIn('id', $perusahaan_ids)->get();
                    } else {
                        $sales_ids = explode(', ', $row->sales_all);
                        $perusahaan_ids = explode(', ', $row->perusahaan_all);
                        $instruktur_ids = explode(', ', $row->instruktur_all);
                        $row->instruktur = Karyawan::whereIn('kode_karyawan', $instruktur_ids)->get();
                        $row->sales = Karyawan::whereIn('kode_karyawan', $sales_ids)->get();
                        $row->perusahaan = Perusahaan::whereIn('id', $perusahaan_ids)->get();
                    }
                }
                $weekRanges[] = ['start' => $start, 'end' =>  $end, 'data' => $rows];

            }

            $monthRanges[] = ['month' => $startOfMonth->translatedFormat('F-Y'), 'weeksData' => $weekRanges];

            $date = $date->addMonth();
        }

        $json = $monthRanges;
        return new PostResource(true, 'List Detail Bulan RKM', $json);
    }
}
