<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use App\Models\RKM;
use App\Models\karyawan;
use Illuminate\Support\Carbon;
use App\Models\Perusahaan;
use App\Http\Resources\PostResource;
use App\Models\AbsensiPDF;
use App\Models\Nilaifeedback;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RKMExport;
use App\Exports\RKMExcelAdmsales;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Storage;

class RKMController extends Controller
{
    public function index() {}

    public function showMonth($year, $month)
    {
        $bulan = $month + 1;
        $startDate = CarbonImmutable::create($year, $month, 1);
        $endDate = CarbonImmutable::create($year, $month, 1)->endOfMonth();
        $now = CarbonImmutable::now()->locale('id_ID');

        $monthRanges = [];
        $date = $startDate;

        while ($date->month <= $endDate->month && $date->year <= $endDate->year) {
            $startOfMonth = $date->startOfMonth();
            $endOfMonth = $date->addMonth()->endOfMonth();

            $weekRanges = [];
            $startOfWeek = $startOfMonth->startOfWeek();
            while ($startOfWeek->lte($endOfMonth)) {
                $endOfWeek = $startOfWeek->copy()->endOfWeek();
                $start = $startOfWeek->format('Y-m-d');
                $end = $endOfWeek->format('Y-m-d');
                $startOfWeek = $startOfWeek->addWeek();
                $rows = RKM::with(['materi', 'peluang'])
                    ->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
                    ->whereBetween('r_k_m_s.tanggal_awal', [$start, $end])
                    ->whereDoesntHave('peluang', function ($query) {
                        $query->where('tentatif', 1); // Exclude RKM records where peluang.tentatif = 1
                    })
                    ->select(
                        DB::raw('GROUP_CONCAT(r_k_m_s.id SEPARATOR ", ") AS id'), // Gabungkan semua id
                        'r_k_m_s.materi_key',
                        'r_k_m_s.ruang',
                        'r_k_m_s.metode_kelas',
                        'r_k_m_s.event',
                        DB::raw('GROUP_CONCAT(r_k_m_s.exam SEPARATOR ", ") AS exam'), // Gabungkan semua exam
                        DB::raw('GROUP_CONCAT(r_k_m_s.makanan SEPARATOR ", ") AS makanan'), // Gabungkan semua makanan
                        DB::raw('GROUP_CONCAT(r_k_m_s.instruktur_key SEPARATOR ", ") AS instruktur_all'),
                        DB::raw('GROUP_CONCAT(r_k_m_s.perusahaan_key SEPARATOR ", ") AS perusahaan_all'),
                        DB::raw('GROUP_CONCAT(r_k_m_s.sales_key SEPARATOR ", ") AS sales_all'),
                        DB::raw('CASE WHEN SUM(r_k_m_s.status = 0) > 0 THEN 0 ELSE MIN(r_k_m_s.status) END AS status_all'),
                        DB::raw('SUM(r_k_m_s.pax) AS total_pax'),
                        'r_k_m_s.tanggal_awal',
                        DB::raw('MAX(r_k_m_s.tanggal_akhir) AS tanggal_akhir')
                    )
                    ->groupBy(
                        'r_k_m_s.materi_key',
                        'r_k_m_s.ruang',
                        'r_k_m_s.metode_kelas',
                        'r_k_m_s.event',
                        'r_k_m_s.tanggal_awal'
                    )
                    ->orderBy('status_all', 'asc')
                    ->orderBy('r_k_m_s.tanggal_awal', 'asc')
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

                $weekRanges[] = ['start' => $start, 'end' => $end, 'data' => $rows];
            }

            $monthRanges[] = ['month' => $startOfMonth->translatedFormat('F-Y'), 'weeksData' => $weekRanges];

            $date = $date->addMonth();
        }

        $json = $monthRanges;
        return new PostResource(true, 'List Detail Bulan RKM', $json);
    }

    public function exportExcel(Request $request)
    {
        $data = $request->input('data');
        $tahun = $request->input('tahun');
        $bulan = $request->input('bulan');

        $filename = "RKM_Bulan_{$bulan}_Tahun_{$tahun}_" . date('Ymd_His') . ".xlsx";

        $path = 'exports/' . $filename;

        Excel::store(new RKMExport($data, $bulan), $path, 'local');

        return response()->json(['filename' => $path]);
    }
    public function RKMAPIabsensi($year, $month)
    {
        $bulan = $month + 1;
        $startDate = CarbonImmutable::create($year, $month, 1);
        $endDate = CarbonImmutable::create($year, $month, 1)->endOfMonth();
        $now = CarbonImmutable::now()->locale('id_ID');

        $monthRanges = [];
        $date = $startDate;

        while ($date->month <= $endDate->month && $date->year <= $endDate->year) {
            $startOfMonth = $date->startOfMonth();
            $endOfMonth = $date->addMonth()->endOfMonth();

            $weekRanges = [];
            $startOfWeek = $startOfMonth->startOfWeek();
            while ($startOfWeek->lte($endOfMonth)) {
                $endOfWeek = $startOfWeek->copy()->endOfWeek();
                $start = $startOfWeek->format('Y-m-d');
                $end = $endOfWeek->format('Y-m-d');
                $startOfWeek = $startOfWeek->addWeek();
                $rows = RKM::with(['materi', 'peluang'])
                    ->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
                    ->whereBetween('r_k_m_s.tanggal_awal', [$start, $end])
                    ->whereDoesntHave('peluang', function ($query) {
                        $query->where('tentatif', 1);
                    })
                    ->select(
                        DB::raw('GROUP_CONCAT(r_k_m_s.id SEPARATOR ", ") AS id'),
                        'r_k_m_s.materi_key',
                        'r_k_m_s.ruang',
                        'r_k_m_s.metode_kelas',
                        'r_k_m_s.event',
                        DB::raw('GROUP_CONCAT(CASE 
                WHEN r_k_m_s.exam = "0" THEN "Tidak"
                WHEN r_k_m_s.exam = "1" THEN "Ya"
                ELSE COALESCE(r_k_m_s.exam, "Tidak")
            END SEPARATOR ", ") AS exam'),
                        DB::raw('GROUP_CONCAT(CASE 
                WHEN r_k_m_s.makanan = "0" THEN "Tidak Ada"
                WHEN r_k_m_s.makanan = "1" THEN "Nasi Box"
                WHEN r_k_m_s.makanan = "2" THEN "Prasmanan"
                ELSE COALESCE(r_k_m_s.makanan, "Tidak Ada")
            END SEPARATOR ", ") AS makanan'),
                        DB::raw('GROUP_CONCAT(r_k_m_s.instruktur_key SEPARATOR ", ") AS instruktur_all'),
                        DB::raw('GROUP_CONCAT(r_k_m_s.perusahaan_key SEPARATOR ", ") AS perusahaan_all'),
                        DB::raw('GROUP_CONCAT(r_k_m_s.sales_key SEPARATOR ", ") AS sales_all'),
                        DB::raw('CASE WHEN SUM(r_k_m_s.status = 0) > 0 THEN 0 ELSE MIN(r_k_m_s.status) END AS status_all'),
                        DB::raw('SUM(r_k_m_s.pax) AS total_pax'),
                        'r_k_m_s.tanggal_awal',
                        DB::raw('MAX(r_k_m_s.tanggal_akhir) AS tanggal_akhir')
                    )
                    ->groupBy(
                        'r_k_m_s.materi_key',
                        'r_k_m_s.ruang',
                        'r_k_m_s.metode_kelas',
                        'r_k_m_s.event',
                        'r_k_m_s.tanggal_awal'
                    )
                    ->orderBy('status_all', 'asc')
                    ->orderBy('r_k_m_s.tanggal_awal', 'asc')
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
                    $absensiExists = AbsensiPDF::where('id_rkm', $row->id)->exists();
                    $row->absensi_status = $absensiExists ? 'green' : 'red';
                }

                // return $rows;

                $weekRanges[] = ['start' => $start, 'end' =>  $end, 'data' => $rows];
            }

            $monthRanges[] = ['month' => $startOfMonth->translatedFormat('F-Y'), 'weeksData' => $weekRanges];

            $date = $date->addMonth();
        }

        $json = $monthRanges;
        return new PostResource(true, 'List Detail Bulan RKM', $json);
    }



    public function getRKMRegist()
    {
        $today = Carbon::now();
        $startDate = $today->startOfWeek()->toDateString(); // Tanggal awal minggu ini
        $endDate = $today->endOfWeek()->toDateString(); // Tanggal akhir minggu ini

        $rows = RKM::with(['materi:id,nama_materi'])
            ->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
            ->join('perusahaans', 'r_k_m_s.perusahaan_key', '=', 'perusahaans.id')
            // ->whereBetween('r_k_m_s.tanggal_awal', [$startDate, $endDate])
            // ->whereBetween('r_k_m_s.tanggal_akhir', [$startDate, $endDate])
            ->where('materis.nama_materi', 'LIKE', '%' . request('q') . '%')
            ->select('r_k_m_s.*', 'perusahaans.nama_perusahaan')
            ->paginate(10);
        return response()->json($rows);


        // $perusahaans = Perusahaan::where('nama_perusahaan', 'LIKE', '%'.request('q').'%')->paginate(10);
    }

    public function getRKMDetail(Request $request)
    {
        $idRkm = $request->id_rkm;
        $rkm = RKM::with('materi', 'instruktur', 'instruktur2', 'asisten', 'nilaifeedback')
            ->where('id', $idRkm)
            ->first();

        if ($rkm) {
            return response()->json(['rkm' => $rkm]);
        } else {
            return response()->json(['rkm' => null]);
        }
    }

    public function getRKMSouvenir(Request $request)
    {
        $rkm = RKM::with(['sales', 'materi', 'instruktur', 'perusahaan', 'instruktur2', 'asisten', 'souvenirpeserta.regist.peserta', 'souvenirpeserta.souvenir'])
            ->whereHas('souvenirpeserta')
            ->latest()
            ->get();

        if ($rkm->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No RKM found for the current month', 'data' => null]);
        } else {
            return response()->json(['success' => true, 'message' => 'List RKM', 'data' => $rkm]);
        }
    }

    public function getRKMDetailGroup(Request $request)
    {
        $idRkm = $request->id_rkm;
        $rkm = RKM::with('materi', 'instruktur', 'instruktur2', 'asisten', 'nilaifeedback')
            ->where('id', $idRkm)
            ->first();
		//dd($rkm);
        $materi_key = $rkm->materi_key;
        $start = $rkm->tanggal_awal;
        $end = $rkm->tanggal_akhir;
		$instruktur_key = $rkm->instruktur_key;
        $rows = RKM::with('materi', 'instruktur', 'instruktur2', 'asisten', 'nilaifeedback')
            ->where('materi_key', $materi_key)
			->where('instruktur_key', $instruktur_key)
            ->where('tanggal_awal', $start)
            ->whereBetween('tanggal_akhir', [$start, $end])
            ->get();

        $mergedData = [];

        foreach ($rows as $row) {
            // Buat kunci unik berdasarkan materi_key, tanggal_awal, dan tanggal_akhir
            $key = $row->materi_key . '|' . $row->tanggal_awal . '|' . $row->tanggal_akhir;

            if (!isset($mergedData[$key])) {
                // Jika kunci belum ada, tambahkan data baru
                $mergedData[$key] = $row->toArray();
                $mergedData[$key]['sales_key'] = [$row->sales_key]; // Simpan sales_key dalam array
                $mergedData[$key]['perusahaan_key'] = [$row->perusahaan_key]; // Simpan perusahaan_key dalam array
                $mergedData[$key]['pax'] = $row->pax; // Ambil pax
                $mergedData[$key]['id_rkm'] = [$row->id];
            } else {
                // Jika kunci sudah ada, gabungkan data
                $mergedData[$key]['sales_key'][] = $row->sales_key; // Tambahkan sales_key
                $mergedData[$key]['perusahaan_key'][] = $row->perusahaan_key; // Tambahkan perusahaan_key
                $mergedData[$key]['pax'] += $row->pax; // Jumlahkan pax
                $mergedData[$key]['id_rkm'][] = $row->id;
            }
        }

        // Format hasil akhir
        foreach ($mergedData as $data) {
            $data['sales_key'] = implode(', ', $data['sales_key']); // Gabungkan sales_key
            $data['perusahaan_key'] = implode(', ', $data['perusahaan_key']); // Gabungkan perusahaan_key
            $data['id_rkm'] = implode(', ', $data['id_rkm']);
            $result = $data;
        }

        // Kembalikan hasil
        // return response()->json($result);

        if ($rkm) {
            return response()->json($result);
        } else {
            return response()->json(['rkm' => null]);
        }
    }
}
