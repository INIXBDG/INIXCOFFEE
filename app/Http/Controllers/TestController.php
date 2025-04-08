<?php

namespace App\Http\Controllers;

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
