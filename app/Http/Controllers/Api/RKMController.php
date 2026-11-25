<?php

namespace App\Http\Controllers\Api;

use App\Exports\RkmExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use App\Models\RKM;
use App\Models\karyawan;
use App\Models\User;
use Illuminate\Support\Carbon;
use App\Models\Perusahaan;
use App\Http\Resources\PostResource;
use App\Models\AbsensiPDF;
use App\Models\RekomendasiLanjutan;
use App\Models\eksam;
use App\Support\CacheRegistry;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class RKMController extends Controller
{
    public function index()
    {
    }

    public function showMonth($year, $month)
    {
        $yearInt = (int) $year;
        $monthInt = (int) $month;

        $monthRanges = CacheRegistry::rememberRkm("show_month:{$yearInt}:{$monthInt}", 300, function () use ($yearInt, $monthInt) {
            DB::statement("SET SESSION group_concat_max_len = 10000");

            $startDate = CarbonImmutable::create($yearInt, $monthInt, 1)->locale('id_ID');
            $startOfMonth = $startDate->startOfMonth();
            $endOfMonth = $startDate->addMonth()->endOfMonth();

            // 1. Susun daftar rentang minggu terlebih dahulu (1 query untuk seluruh rentang)
            $weekBuckets = [];
            $cursor = $startOfMonth->startOfWeek();
            while ($cursor->lte($endOfMonth)) {
                $endOfWeek = $cursor->copy()->endOfWeek();
                $weekBuckets[] = [
                    'start' => $cursor->format('Y-m-d'),
                    'end'   => $endOfWeek->format('Y-m-d'),
                ];
                $cursor = $cursor->addWeek();
            }

            if (empty($weekBuckets)) {
                return [['month' => $startOfMonth->translatedFormat('F-Y'), 'weeksData' => []]];
            }

            $overallStart = $weekBuckets[0]['start'];
            $overallEnd = $weekBuckets[count($weekBuckets) - 1]['end'];

            // 2. Satu query RKM teragregasi untuk seluruh rentang 2 bulan (menggantikan ~9 query loop mingguan)
            $allRows = RKM::with(['materi', 'peluang'])
                ->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
                ->whereBetween('r_k_m_s.tanggal_awal', [$overallStart, $overallEnd])
                ->whereDoesntHave('peluang', function ($query) {
                    $query->where('tentatif', 1)->orWhere('tahap', 'lost');
                })
                ->select(
                    DB::raw('MAX(r_k_m_s.souvenir) AS souvenir'),
                    DB::raw('GROUP_CONCAT(r_k_m_s.id SEPARATOR ", ") AS id'),
                    DB::raw('GROUP_CONCAT(r_k_m_s.id SEPARATOR ", ") AS id_all'),
                    DB::raw('GROUP_CONCAT(r_k_m_s.registrasi_form SEPARATOR ", ") AS registrasi_form'),
                    'r_k_m_s.materi_key',
                    'r_k_m_s.ruang',
                    'r_k_m_s.metode_kelas',
                    'r_k_m_s.event',
                    DB::raw('GROUP_CONCAT(r_k_m_s.exam SEPARATOR ", ") AS exam'),
                    DB::raw('GROUP_CONCAT(r_k_m_s.makanan SEPARATOR ", ") AS makanan'),
                    DB::raw('GROUP_CONCAT(r_k_m_s.instruktur_key SEPARATOR ", ") AS instruktur_all'),
                    DB::raw('GROUP_CONCAT(r_k_m_s.perusahaan_key SEPARATOR ", ") AS perusahaan_all'),
                    DB::raw('GROUP_CONCAT(r_k_m_s.sales_key SEPARATOR ", ") AS sales_all'),
                    DB::raw('CASE WHEN SUM(r_k_m_s.status = 0) > 0 THEN 0 ELSE MIN(r_k_m_s.status) END AS status_all'),
                    DB::raw('SUM(r_k_m_s.pax) AS total_pax'),
                    DB::raw('MAX(r_k_m_s.authorize) AS authorize'),
                    'r_k_m_s.tanggal_awal',
                    DB::raw('MAX(r_k_m_s.tanggal_akhir) AS tanggal_akhir')
                )
                ->groupBy([
                    'r_k_m_s.materi_key',
                    'r_k_m_s.ruang',
                    'r_k_m_s.metode_kelas',
                    'r_k_m_s.event',
                    'r_k_m_s.tanggal_awal',
                ])
                ->orderBy('status_all', 'asc')
                ->orderBy('r_k_m_s.tanggal_awal', 'asc')
                ->get();

            // 3. Ekstraksi ID kolektif sekali saja untuk seluruh rentang
            $allKaryawanCodes = [];
            $allPerusahaanIds = [];
            $allRkmIds = [];

            foreach ($allRows as $row) {
                foreach (explode(', ', $row->sales_all ?? '') as $code) {
                    $code = trim($code);
                    if ($code !== '') $allKaryawanCodes[$code] = true;
                }
                foreach (explode(', ', $row->instruktur_all ?? '') as $code) {
                    $code = trim($code);
                    if ($code !== '') $allKaryawanCodes[$code] = true;
                }
                foreach (explode(', ', $row->perusahaan_all ?? '') as $pid) {
                    $pid = trim($pid);
                    if ($pid !== '') $allPerusahaanIds[$pid] = true;
                }
                foreach (explode(',', $row->id_all ?? '') as $rid) {
                    $rid = trim($rid);
                    if ($rid !== '') $allRkmIds[$rid] = true;
                }
            }

            $uniqueKaryawanIds = array_keys($allKaryawanCodes);
            $uniquePerusahaanIds = array_keys($allPerusahaanIds);
            $uniqueRkmIds = array_keys($allRkmIds);

            // 4. Bulk fetch relasi sekali saja (total 5 query, bukan ~45 query per request)
            $karyawanMap = !empty($uniqueKaryawanIds)
                ? karyawan::whereIn('kode_karyawan', $uniqueKaryawanIds)->get()->keyBy('kode_karyawan')
                : collect();

            $karyawanPrimaryIds = $karyawanMap->pluck('id')->all();
            $userMap = !empty($karyawanPrimaryIds)
                ? User::whereIn('karyawan_id', $karyawanPrimaryIds)->get()->keyBy('karyawan_id')
                : collect();

            foreach ($karyawanMap as $karyawan) {
                $user = $userMap->get($karyawan->id);
                $karyawan->user_id_sales = $user ? $user->id_sales : null;
                $karyawan->user_id_instruktur = $user ? $user->id_instruktur : null;
            }

            $perusahaanMap = !empty($uniquePerusahaanIds)
                ? Perusahaan::whereIn('id', $uniquePerusahaanIds)->get()->keyBy('id')
                : collect();

            $rekomendasiMap = !empty($uniqueRkmIds)
                ? RekomendasiLanjutan::whereIn('id_rkm', $uniqueRkmIds)->get()->groupBy('id_rkm')
                : collect();

            $examedRkmIds = !empty($uniqueRkmIds)
                ? eksam::whereIn('id_rkm', $uniqueRkmIds)
                    ->whereNotNull('id_rkm')
                    ->pluck('id_rkm')
                    ->map(fn($v) => (string) $v)
                    ->flip()
                    ->toArray()
                : [];

            // 5. Pemetaan data dan pengelompokan ke bucket mingguan di memori
            foreach ($allRows as $row) {
                $salesCodes = array_values(array_filter(array_map('trim', explode(', ', $row->sales_all ?? ''))));
                $instrukturCodes = array_values(array_filter(array_map('trim', explode(', ', $row->instruktur_all ?? ''))));
                $perusahaanIds = array_values(array_filter(array_map('trim', explode(', ', $row->perusahaan_all ?? ''))));
                $rkmIds = array_values(array_filter(array_map('trim', explode(',', $row->id_all ?? ''))));

                $row->sales = collect($salesCodes)->map(fn($c) => $karyawanMap->get($c))->filter()->values();
                $row->instruktur = collect($instrukturCodes)->map(fn($c) => $karyawanMap->get($c))->filter()->values();
                $row->perusahaan = collect($perusahaanIds)->map(fn($id) => $perusahaanMap->get($id))->filter()->values();
                $row->rekomendasi_group = collect($rkmIds)->flatMap(fn($id) => $rekomendasiMap->get($id) ?? [])->filter()->values();
                $row->has_exam = collect($rkmIds)->contains(fn($id) => isset($examedRkmIds[(string) $id]));
                $row->nama_sales_list = collect($salesCodes)
                    ->map(fn($c) => optional($karyawanMap->get($c))->nama_lengkap)
                    ->filter()
                    ->implode(', ');
            }

            $weekRanges = [];
            foreach ($weekBuckets as $bucket) {
                $start = $bucket['start'];
                $end = $bucket['end'];
                $weekRows = $allRows->filter(function ($row) use ($start, $end) {
                    $tgl = substr((string) $row->tanggal_awal, 0, 10);
                    return $tgl >= $start && $tgl <= $end;
                })->values();

                $weekRanges[] = ['start' => $start, 'end' => $end, 'data' => $weekRows];
            }

            $result = [['month' => $startOfMonth->translatedFormat('F-Y'), 'weeksData' => $weekRanges]];

            // Simpan sebagai plain array agar payload Redis ringan & cepat di-unserialize
            return json_decode(json_encode($result), true);
        });

        return new PostResource(true, 'List Detail Bulan RKM', $monthRanges);
    }

    public function exportExcel(Request $request)
    {
        $data = $request->input('data');
        $tahun = $request->input('tahun');
        $bulan = $request->input('bulan');

        $filename = "RKM_Bulan_{$bulan}_Tahun_{$tahun}_".date('Ymd_His').'.xlsx';

        $path = 'exports/'.$filename;

        Excel::store(new RkmExport($data, $bulan), $path, 'local');

        return response()->json(['filename' => $path]);
    }

    public function RKMAPIabsensi($year, $month)
    {
        $yearInt = (int) $year;
        $monthInt = (int) $month;

        $json = CacheRegistry::rememberRkm("absensi:{$yearInt}:{$monthInt}", 300, function () use ($yearInt, $monthInt) {
            DB::statement("SET SESSION group_concat_max_len = 10000");

            $startDate = CarbonImmutable::create($yearInt, $monthInt, 1)->locale('id_ID');
            $startOfMonth = $startDate->startOfMonth();
            $endOfCurrentMonth = $startOfMonth->endOfMonth();
            $endOfTwoMonths = $startDate->addMonth()->endOfMonth();

            $weekBuckets = [];
            $cursor = $startOfMonth->startOfWeek();
            while ($cursor->lte($endOfTwoMonths)) {
                $endOfWeek = $cursor->copy()->endOfWeek();
                $weekBuckets[] = [
                    'start' => $cursor->format('Y-m-d'),
                    'end'   => $endOfWeek->format('Y-m-d'),
                ];
                $cursor = $cursor->addWeek();
            }

            // Query SARGable (menggantikan whereMonth + loop mingguan + N+1 per baris)
            $allRows = RKM::with(['materi', 'peluang', 'exam', 'exam.approvalexam'])
                ->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
                ->whereBetween('r_k_m_s.tanggal_awal', [
                    $startOfMonth->format('Y-m-d'),
                    $endOfCurrentMonth->format('Y-m-d'),
                ])
                ->where('r_k_m_s.status', '0')
                ->whereDoesntHave('peluang', function ($query) {
                    $query->where('tentatif', 1);
                })
                ->where(function ($query) {
                    $query->whereHas('exam.approvalexam', function ($q) {
                        $q->where('technical_support', 1);
                    })
                    ->orWhereDoesntHave('exam.approvalexam');
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
                    DB::raw('MAX(r_k_m_s.authorize) AS authorize'),
                    'r_k_m_s.tanggal_awal',
                    DB::raw('MAX(r_k_m_s.tanggal_akhir) AS tanggal_akhir')
                )
                ->groupBy([
                    'r_k_m_s.materi_key',
                    'r_k_m_s.ruang',
                    'r_k_m_s.metode_kelas',
                    'r_k_m_s.event',
                    'r_k_m_s.tanggal_awal',
                ])
                ->orderBy('status_all', 'asc')
                ->orderBy('r_k_m_s.tanggal_awal', 'asc')
                ->get();

            // Bulk fetch seluruh relasi sekali saja (menggantikan ~200+ query N+1)
            $allSalesCodes = [];
            $allInstrukturCodes = [];
            $allPerusahaanIds = [];
            $allCompositeRkmIds = [];

            foreach ($allRows as $row) {
                $allCompositeRkmIds[] = $row->id;
                foreach (explode(', ', $row->sales_all ?? '') as $c) {
                    $c = trim($c);
                    if ($c !== '') $allSalesCodes[$c] = true;
                }
                foreach (explode(', ', $row->instruktur_all ?? '') as $c) {
                    $c = trim($c);
                    if ($c !== '') $allInstrukturCodes[$c] = true;
                }
                foreach (explode(', ', $row->perusahaan_all ?? '') as $pid) {
                    $pid = trim($pid);
                    if ($pid !== '') $allPerusahaanIds[$pid] = true;
                }
            }

            $salesCodesList = array_keys($allSalesCodes);
            $instrukturCodesList = array_keys($allInstrukturCodes);
            $perusahaanIdsList = array_keys($allPerusahaanIds);

            $karyawanByKode = !empty($salesCodesList)
                ? karyawan::whereIn('kode_karyawan', $salesCodesList)->get()->keyBy('kode_karyawan')
                : collect();

            $karyawanBySalesUser = !empty($salesCodesList)
                ? karyawan::with('user')
                    ->whereHas('user', fn($q) => $q->whereIn('users.id_sales', $salesCodesList))
                    ->get()
                    ->groupBy(fn($k) => optional($k->user)->id_sales)
                : collect();

            $karyawanByInstrukturUser = !empty($instrukturCodesList)
                ? karyawan::with('user')
                    ->whereHas('user', fn($q) => $q->whereIn('users.id_instruktur', $instrukturCodesList))
                    ->get()
                    ->groupBy(fn($k) => optional($k->user)->id_instruktur)
                : collect();

            $perusahaanMap = !empty($perusahaanIdsList)
                ? Perusahaan::whereIn('id', $perusahaanIdsList)->get()->keyBy('id')
                : collect();

            $existingAbsensiIds = !empty($allCompositeRkmIds)
                ? AbsensiPDF::whereIn('id_rkm', array_unique($allCompositeRkmIds))
                    ->pluck('id_rkm')
                    ->flip()
                    ->toArray()
                : [];

            foreach ($allRows as $row) {
                $salesIds = array_values(array_filter(array_map('trim', explode(', ', $row->sales_all ?? ''))));
                $perusahaanIds = array_values(array_filter(array_map('trim', explode(', ', $row->perusahaan_all ?? ''))));

                if ($row->instruktur_all == null) {
                    $row->sales = collect($salesIds)->map(fn($c) => $karyawanByKode->get($c))->filter()->values();
                } else {
                    $instrukturIds = array_values(array_filter(array_map('trim', explode(', ', $row->instruktur_all ?? ''))));
                    $row->instruktur = collect($instrukturIds)
                        ->flatMap(fn($c) => $karyawanByInstrukturUser->get($c, collect()))
                        ->unique('id')
                        ->values();
                    $row->sales = collect($salesIds)
                        ->flatMap(fn($c) => $karyawanBySalesUser->get($c, collect()))
                        ->unique('id')
                        ->values();
                }

                $row->perusahaan = collect($perusahaanIds)->map(fn($id) => $perusahaanMap->get($id))->filter()->values();
                $row->absensi_status = isset($existingAbsensiIds[$row->id]) ? 'green' : 'red';
            }

            $weekRanges = [];
            foreach ($weekBuckets as $bucket) {
                $start = $bucket['start'];
                $end = $bucket['end'];
                $weekRows = $allRows->filter(function ($row) use ($start, $end) {
                    $tgl = substr((string) $row->tanggal_awal, 0, 10);
                    return $tgl >= $start && $tgl <= $end;
                })->values();

                $weekRanges[] = ['start' => $start, 'end' => $end, 'data' => $weekRows];
            }

            $result = [['month' => $startOfMonth->translatedFormat('F-Y'), 'weeksData' => $weekRanges]];

            return json_decode(json_encode($result), true);
        });

        return new PostResource(true, 'List Detail Bulan RKM', $json);
    }

    public function getRKMRegist()
    {
        $year = now()->year;
        $rows = RKM::with(['materi:id,nama_materi'])
            ->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
            ->join('perusahaans', 'r_k_m_s.perusahaan_key', '=', 'perusahaans.id')
            ->whereYear('r_k_m_s.tanggal_awal', $year)
            // ->whereBetween('r_k_m_s.tanggal_akhir', [$startDate, $endDate])
            ->where('materis.nama_materi', 'LIKE', '%'.request('q').'%')
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

        if (!$rkm) {
            return response()->json([
                'success' => false,
                'message' => 'RKM tidak ditemukan',
                'data' => null,
            ], 404);
        }

        $materi_key = $rkm->materi_key;
        $start = $rkm->tanggal_awal;
        $instruktur_key = $rkm->instruktur_key;

        $rows = RKM::with([
            'materi', 'instruktur', 'instruktur2', 'asisten',
            'nilaifeedback', 'peluang', 'exam', 'exam.approvalexam',
        ])
        ->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
        ->whereDate('r_k_m_s.tanggal_awal', $start)
        ->where('r_k_m_s.status', '0')
        ->whereNull('r_k_m_s.deleted_at')
        ->whereDoesntHave('peluang', fn($q) => $q->where('tentatif', 1))
        ->whereHas('peluang', fn($q) => $q->where('tentatif', 0))
        ->where('r_k_m_s.materi_key', $materi_key)
        ->where('r_k_m_s.instruktur_key', $instruktur_key)
        ->orderBy('r_k_m_s.tanggal_awal')
        ->orderBy('r_k_m_s.tanggal_akhir')
        ->select('r_k_m_s.*')
        ->get();

        if ($rows->isEmpty()) {
            Log::warning('RKM Group Detail: rows kosong', [
                'id_rkm' => $idRkm,
                'materi_key' => $materi_key,
                'instruktur_key' => $instruktur_key,
                'status' => $rkm->status,
                'deleted_at' => $rkm->deleted_at,
                'peluang_tentatif' => $rkm->peluang->tentatif ?? 'NO_PELUANG',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Data RKM tidak memenuhi kriteria rekap (cek status/peluang/instruktur/tentatif/materi)',
                'data' => [
                    'id_rkm' => $idRkm,
                    'materi_key' => $materi_key,
                    'instruktur_key' => $instruktur_key,
                    'status' => $rkm->status,
                    'deleted_at' => $rkm->deleted_at,
                    'peluang_tentatif' => $rkm->peluang->tentatif ?? 'NO_PELUANG',
                ],
            ], 200); // tetap 200 karena ini bukan error server, cuma data gak ada
        }

        $mergedData = [];
        foreach ($rows as $row) {
            $key = $row->materi_key.'|'.$row->tanggal_awal.'|'.$row->tanggal_akhir;
            if (!isset($mergedData[$key])) {
                $mergedData[$key] = $row->toArray();
                $mergedData[$key]['sales_key'] = [$row->sales_key];
                $mergedData[$key]['perusahaan_key'] = [$row->perusahaan_key];
                $mergedData[$key]['pax'] = $row->pax;
                $mergedData[$key]['id_rkm'] = [$row->id];
            } else {
                $mergedData[$key]['sales_key'][] = $row->sales_key;
                $mergedData[$key]['perusahaan_key'][] = $row->perusahaan_key;
                $mergedData[$key]['pax'] += $row->pax;
                $mergedData[$key]['id_rkm'][] = $row->id;
            }
        }

        $mergedResults = [];
        foreach ($mergedData as $data) {
            $data['sales_key'] = implode(', ', $data['sales_key']);
            $data['perusahaan_key'] = implode(', ', $data['perusahaan_key']);
            $data['id_rkm'] = implode(', ', $data['id_rkm']);
            $data['tanggal_awal'] = Carbon::parse($data['tanggal_awal'])->timezone('Asia/Jakarta')->format('Y-m-d');
            $data['tanggal_akhir'] = Carbon::parse($data['tanggal_akhir'])->timezone('Asia/Jakarta')->format('Y-m-d');
            $mergedResults[] = $data;
        }

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => end($mergedResults),
        ]);
    }

    public function jadwalRKM()
    {
        $today = Carbon::now();
        $startDate = $today->copy()->subMonths(4)->startOfMonth()->toDateString();
        $endDate = $today->copy()->endOfMonth()->toDateString();

        $result = CacheRegistry::rememberRkm("jadwal_api:{$startDate}:{$endDate}", 300, function () use ($startDate, $endDate) {
            $rows = RKM::with('materi')
                ->whereBetween('tanggal_awal', [$startDate, $endDate])
                ->where('status', '0')
                ->get();

            $grouped = $rows->groupBy(function ($item) {
                return ($item->materi->nama_materi ?? '-') . '|' . $item->tanggal_awal;
            });

            $mapped = $grouped->map(function ($items, $key) {
                [$nama_materi, $tanggal_awal] = explode('|', $key);
                $tanggal_akhir = $items->first()->tanggal_akhir;

                return [
                    'nama_materi'   => $nama_materi,
                    'tanggal_awal'  => $tanggal_awal,
                    'tanggal_akhir' => $tanggal_akhir,
                    'jadwals'       => $items,
                ];
            })->values();

            return json_decode(json_encode($mapped), true);
        });

        return response()->json([
            'success' => true,
            'message' => 'Upcoming RKM',
            'data' => $result,
        ]);
    }
}
