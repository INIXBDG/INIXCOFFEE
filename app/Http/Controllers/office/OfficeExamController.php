<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\RKM;
use App\Models\Karyawan;
use App\Models\Perusahaan;
use App\Models\Eksam;
use App\Models\BundlingExam;
use App\Http\Resources\PostResource;
use App\Models\DokumentasiExam;
use App\Models\eksam as ModelsEksam;

class OfficeExamController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:View RekapExam', ['only' => ['indexRekap', 'rekapJson']]);
    }

    public function indexOffice()
    {
        return view('office.exam.index');
    }

    public function showExamMonth($year, $month)
    {
        $cacheKey = "office_exam_month_{$year}_{$month}";
        $monthRanges = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($year, $month) {
            $startDate = CarbonImmutable::create($year, $month, 1);
            $endDate = CarbonImmutable::create($year, $month, 1)->endOfMonth();

            $monthRangesResult = [];
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

                    $rows = RKM::with(['materi', 'peluang', 'rekomendasilanjutan', 'eksam'])
                        ->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
                        ->whereBetween('r_k_m_s.tanggal_awal', [$start, $end])
                        ->where('r_k_m_s.exam', '1')
                        ->whereDoesntHave('peluang', function ($query) {
                            $query->where('tentatif', 1);
                        })
                        ->where(function ($query) {
                            $query
                                ->whereHas('exam.approvalexam', function ($q) {
                                    $q->where('technical_support', 1);
                                })
                                ->orWhereDoesntHave('exam.approvalexam');
                        })
                        ->select(DB::raw('GROUP_CONCAT(r_k_m_s.id SEPARATOR ", ") AS id'), DB::raw('GROUP_CONCAT(r_k_m_s.id SEPARATOR ", ") AS id_all'), DB::raw('GROUP_CONCAT(r_k_m_s.registrasi_form SEPARATOR ", ") AS registrasi_form'), 'r_k_m_s.materi_key', 'r_k_m_s.ruang', 'r_k_m_s.metode_kelas', 'r_k_m_s.event', DB::raw('GROUP_CONCAT(r_k_m_s.exam SEPARATOR ", ") AS exam'), DB::raw('GROUP_CONCAT(r_k_m_s.makanan SEPARATOR ", ") AS makanan'), DB::raw('GROUP_CONCAT(r_k_m_s.instruktur_key SEPARATOR ", ") AS instruktur_all'), DB::raw('GROUP_CONCAT(r_k_m_s.perusahaan_key SEPARATOR ", ") AS perusahaan_all'), DB::raw('GROUP_CONCAT(r_k_m_s.sales_key SEPARATOR ", ") AS sales_all'), DB::raw('CASE WHEN SUM(r_k_m_s.status = 0) > 0 THEN 0 ELSE MIN(r_k_m_s.status) END AS status_all'), DB::raw('SUM(r_k_m_s.pax) AS total_pax'), 'r_k_m_s.tanggal_awal', DB::raw('MAX(r_k_m_s.tanggal_akhir) AS tanggal_akhir'))
                        ->groupBy('r_k_m_s.materi_key', 'r_k_m_s.ruang', 'r_k_m_s.metode_kelas', 'r_k_m_s.event', 'r_k_m_s.tanggal_awal')
                        ->orderBy('status_all', 'asc')
                        ->orderBy('r_k_m_s.tanggal_awal', 'asc')
                        ->get();

                    $allSalesIds = $rows->flatMap(fn($row) => array_filter(explode(', ', $row->sales_all ?? '')))->unique();
                    $allCompanyIds = $rows->flatMap(fn($row) => array_filter(explode(', ', $row->perusahaan_all ?? '')))->unique();
                    $allInstructorIds = $rows->flatMap(fn($row) => array_filter(explode(', ', $row->instruktur_all ?? '')))->unique();
                    $allRkmIds = $rows->flatMap(fn($row) => array_filter(explode(', ', $row->id ?? '')))->unique();

                    $salesByCode = Karyawan::whereIn('kode_karyawan', $allSalesIds)->get()->keyBy('kode_karyawan');
                    $instructorsByCode = Karyawan::whereIn('kode_karyawan', $allInstructorIds)->get()->keyBy('kode_karyawan');
                    $companiesById = Perusahaan::whereIn('id', $allCompanyIds)->get()->keyBy('id');
                    $bundlingByRkm = BundlingExam::whereIn('id_rkm', $allRkmIds)->get()->groupBy('id_rkm');
                    $examByRkm = Eksam::whereIn('id_rkm', $allRkmIds)->get()->groupBy('id_rkm');

                    foreach ($rows as $row) {
                        $sales_ids = array_filter(explode(', ', $row->sales_all ?? ''));
                        $perusahaan_ids = array_filter(explode(', ', $row->perusahaan_all ?? ''));

                        $row->sales = collect($sales_ids)
                            ->map(fn($id) => $salesByCode->get($id))
                            ->filter()
                            ->values();
                        $row->perusahaan = collect($perusahaan_ids)
                            ->map(fn($id) => $companiesById->get($id))
                            ->filter()
                            ->values();

                        if ($row->instruktur_all) {
                            $instruktur_ids = array_filter(explode(', ', $row->instruktur_all));
                            $row->instruktur = collect($instruktur_ids)
                                ->map(fn($id) => $instructorsByCode->get($id))
                                ->filter()
                                ->values();
                        }

                        $rkmIds = array_filter(explode(', ', $row->id ?? ''));

                        $bundlingData = collect($rkmIds)
                            ->map(fn($id) => $bundlingByRkm->get($id, collect())->first())
                            ->filter()
                            ->first();
                        $bundlingVal = $bundlingData ? $bundlingData->bundling : null;

                        if ($bundlingVal === null || $bundlingVal == 0) {
                            $row->bundling_status = 0;
                        } elseif ($bundlingVal == 1) {
                            $row->bundling_status = 1;
                        } elseif ($bundlingVal == 2) {
                            $row->bundling_status = 2;
                        } else {
                            $row->bundling_status = 0;
                        }

                        $eksamData = collect($rkmIds)
                            ->map(fn($id) => $examByRkm->get($id, collect())->first())
                            ->filter()
                            ->first();
                        if ($eksamData) {
                            $row->exam_status = 'sudah_rekomendasi';
                        } else {
                            $row->exam_status = 'belum_pengajuan';
                        }
                    }

                    $weekRanges[] = ['start' => $start, 'end' => $end, 'data' => $rows];
                }

                $monthRangesResult[] = [
                    'month' => $startOfMonth->translatedFormat('F-Y'),
                    'weeksData' => $weekRanges,
                ];

                $date = $date->addMonth();
            }
            
            return $monthRangesResult;
        });

        return new PostResource(true, 'List Kelas dengan Exam', $monthRanges);
    }

    public function updateBundling(Request $request)
    {
        $ids = explode(', ', $request->input('id'));
        $status = $request->input('status');

        $dataRkm = RKM::with('eksam')->whereIn('id', $ids)->get();

        foreach ($dataRkm as $rkm) {
            $updateData = ['bundling' => $status];

            if ($rkm->eksam) {
                $updateData['id_exam'] = $rkm->eksam->id;
            }

            BundlingExam::updateOrCreate(['id_rkm' => $rkm->id], $updateData);
        }
        
        // Invalidate cache
        $tanggal_awal = count($dataRkm) > 0 ? CarbonImmutable::parse($dataRkm[0]->tanggal_awal) : now();
        Cache::forget("office_exam_month_{$tanggal_awal->year}_{$tanggal_awal->month}");

        return response()->json(['success' => true]);
    }

    public function showDetailExam($id)
    {
        $ids = explode(', ', $id);
        
        $rkms = DB::table('r_k_m_s')
            ->leftJoin('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
            ->leftJoin('eksams', 'r_k_m_s.id', '=', 'eksams.id_rkm')
            ->whereIn('r_k_m_s.id', $ids)
            ->select(
                'r_k_m_s.*',
                'materis.nama_materi',
                'eksams.invoice as eksam_invoice',
                'eksams.file_invoice as eksam_file_invoice',
                'eksams.tanggal_pengajuan as eksam_tanggal_pengajuan',
                'eksams.tanggal_mulai as eksam_tanggal_mulai',
                'eksams.tanggal_selesai as eksam_tanggal_selesai',
                'eksams.materi as eksam_materi',
                'eksams.perusahaan as eksam_perusahaan',
                'eksams.mata_uang as eksam_mata_uang',
                'eksams.harga as eksam_harga',
                'eksams.biaya_admin as eksam_biaya_admin',
                'eksams.harga_rupiah as eksam_harga_rupiah',
                'eksams.kurs as eksam_kurs',
                'eksams.kurs_dollar as eksam_kurs_dollar',
                'eksams.pax as eksam_pax',
                'eksams.total as eksam_total',
                'eksams.kode_exam as eksam_kode_exam',
                'eksams.total_pax as eksam_total_pax',
                'eksams.keterangan as eksam_keterangan',
                'eksams.status as eksam_status',
                'eksams.kode_karyawan as eksam_kode_karyawan'
            )
            ->get();
        
        $formatDate = function($value) {
            if (empty($value) || $value === '0000-00-00' || $value === '0000-00-00 00:00:00' || $value === '0') {
                return '-';
            }
            if (is_numeric($value) && $value < 1000000000) {
                return '-';
            }
            try {
                return \Carbon\CarbonImmutable::parse($value)->format('d M Y');
            } catch (\Exception $e) {
                return '-';
            }
        };
        
        // Pre-fetch all necessary IDs to avoid N+1 inside the loop
        $allPerusahaanIds = [];
        $allKaryawanIds = [];
        foreach ($rkms as $rkm) {
            $allPerusahaanIds = array_merge($allPerusahaanIds, array_filter(explode(', ', $rkm->perusahaan_key ?? '')));
            $allKaryawanIds = array_merge(
                $allKaryawanIds, 
                array_filter(explode(', ', $rkm->sales_key ?? '')),
                array_filter(explode(', ', $rkm->instruktur_key ?? '')),
                array_filter(explode(', ', $rkm->instruktur_key2 ?? '')),
                array_filter(explode(', ', $rkm->asisten_key ?? ''))
            );
        }
        
        $perusahaanMap = Perusahaan::whereIn('id', array_unique($allPerusahaanIds))->pluck('nama_perusahaan', 'id');
        $karyawanMap = Karyawan::whereIn('kode_karyawan', array_unique($allKaryawanIds))->pluck('nama_lengkap', 'kode_karyawan');
        
        $details = [];
        foreach ($rkms as $rkm) {
            $perusahaan_ids = array_filter(explode(', ', $rkm->perusahaan_key ?? ''));
            $perusahaan = collect($perusahaan_ids)->map(fn($id) => $perusahaanMap[$id] ?? null)->filter()->implode(', ') ?: '-';
            
            $sales_ids = array_filter(explode(', ', $rkm->sales_key ?? ''));
            $sales = collect($sales_ids)->map(fn($id) => $karyawanMap[$id] ?? null)->filter()->implode(', ') ?: '-';
            
            $instruktur_ids = array_filter(explode(', ', $rkm->instruktur_key ?? ''));
            $instruktur = collect($instruktur_ids)->map(fn($id) => $karyawanMap[$id] ?? null)->filter()->implode(', ') ?: '-';

            $instruktur2_ids = array_filter(explode(', ', $rkm->instruktur_key2 ?? ''));
            $instruktur2 = collect($instruktur2_ids)->map(fn($id) => $karyawanMap[$id] ?? null)->filter()->implode(', ') ?: '-';

            $asisten_ids = array_filter(explode(', ', $rkm->asisten_key ?? ''));
            $asisten = collect($asisten_ids)->map(fn($id) => $karyawanMap[$id] ?? null)->filter()->implode(', ') ?: '-';

            $tglAwal = $formatDate($rkm->tanggal_awal);
            $tglAkhir = $formatDate($rkm->tanggal_akhir);

            $tanggalTraining = '-';
            if ($tglAwal !== '-' && $tglAkhir !== '-') {
                $tanggalTraining = ($tglAwal === $tglAkhir) ? $tglAwal : $tglAwal . ' s/d ' . $tglAkhir;
            } elseif ($tglAwal !== '-') {
                $tanggalTraining = $tglAwal;
            } elseif ($tglAkhir !== '-') {
                $tanggalTraining = $tglAkhir;
            }

            $eksamData = null;
            if ($rkm->eksam_invoice || $rkm->eksam_materi) {
                $eksamData = [
                    'invoice' => $rkm->eksam_invoice ?? '-',
                    'file_invoice' => $rkm->eksam_file_invoice,
                    'tanggal_pengajuan' => $formatDate($rkm->eksam_tanggal_pengajuan),
                    'tanggal_mulai'     => $formatDate($rkm->eksam_tanggal_mulai),
                    'tanggal_selesai'   => $formatDate($rkm->eksam_tanggal_selesai),
                    'materi'            => $rkm->eksam_materi ?? '-',
                    'perusahaan'        => $rkm->eksam_perusahaan ?? '-',
                    'mata_uang'         => $rkm->eksam_mata_uang ?? '-',
                    'harga'             => $rkm->eksam_harga ?? 0,
                    'biaya_admin'       => $rkm->eksam_biaya_admin ?? 0,
                    'harga_rupiah'      => $rkm->eksam_harga_rupiah ?? 0,
                    'kurs'              => $rkm->eksam_kurs ?? 0,
                    'kurs_dollar'       => $rkm->eksam_kurs_dollar ?? 0,
                    'pax'               => $rkm->eksam_pax ?? 0,
                    'total'             => $rkm->eksam_total ?? 0,
                    'kode_exam'         => $rkm->eksam_kode_exam ?? '-',
                    'total_pax'         => $rkm->eksam_total_pax ?? 0,
                    'keterangan'        => $rkm->eksam_keterangan ?? '-',
                    'status'            => $rkm->eksam_status ?? '-',
                    'kode_karyawan'     => $rkm->eksam_kode_karyawan ?? '-',
                ];
            }

            $details[] = [
                'rkm' => [
                    'id' => $rkm->id,
                    'materi' => $rkm->nama_materi ?? '-',
                    'perusahaan' => $perusahaan,
                    'sales' => $sales,
                    'harga_jual' => $rkm->harga_jual ?? 0,
                    'pax' => $rkm->pax ?? 0,
                    'isi_pax' => $rkm->isi_pax ?? '-',
                    'tanggal' => $tanggalTraining,
                    'metode_kelas' => $rkm->metode_kelas ?? '-',
                    'event' => $rkm->event ?? '-',
                    'ruang' => $rkm->ruang ?? '-',
                    'instruktur' => $instruktur,
                    'instruktur2' => $instruktur2,
                    'asisten' => $asisten,
                    'status' => $rkm->status == 0 ? 'Tidak' : ($rkm->status == 1 ? 'Tidak' : 'Ya'),
                    'exam' => $rkm->exam == 1 ? 'Ya' : 'Tidak',
                    'authorize' => $rkm->authorize ?? '-',
                    'registrasi_form' => $rkm->registrasi_form ?? '-',
                    'quartal' => $rkm->quartal ?? '-',
                    'bulan' => $rkm->bulan ?? '-',
                    'tahun' => $rkm->tahun ?? '-',
                    'makanan' => $rkm->makanan ?? '-',
                    'pdf_peserta' => $rkm->pdf_peserta,
                ],
                'eksam' => $eksamData
            ];
        }
        
        return response()->json(['success' => true, 'data' => $details]);
    }

    public function indexRekap(){
        return view('office.exam.rekapExam');
    }

    public function rekapJson(Request $request)
    {
        $query = ModelsEksam::with(
            'registexam.dokumentasiExam',
            'rkm.instruktur',
            'rkm.materi',
            'approvalexam'
        )->whereHas('approvalexam', function ($q) {
            $q->where('technical_support', 1)
            ->orWhere('office_manager', 1);
        });

        // ── Filter waktu ──────────────────────────────────────────
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal_pengajuan', $request->tahun);
        }

        if ($request->filled('triwulan')) {
            $startMonth = ((int) $request->triwulan - 1) * 3 + 1;
            $endMonth   = $startMonth + 2;
            $query->whereMonth('tanggal_pengajuan', '>=', $startMonth)
                ->whereMonth('tanggal_pengajuan', '<=', $endMonth);
        } elseif ($request->filled('bulan')) {
            $query->whereMonth('tanggal_pengajuan', $request->bulan);
        }

        $exams = $query->get();

        // ── Helper ────────────────────────────────────────────────
        $lulus = fn ($registexam) => $registexam
            ->filter(fn ($r) => strtolower(optional($r->dokumentasiExam)->keterangan_lulus ?? '') === 'lulus')
            ->count();

        $tidakExam = fn ($registexam) => $registexam
            ->filter(fn ($r) => $r->dokumentasiExam === null)
            ->count();

        $tidakLulus = fn ($registexam) => $registexam->count() - $lulus($registexam) - $tidakExam($registexam);

        $ringkasan = fn ($group) => [
            'total_exam'        => $group->count(),
            'total_peserta'     => $group->sum(fn ($e) => $e->registexam->count()),
            'total_lulus'       => $group->sum(fn ($e) => $lulus($e->registexam)),
            'total_tidak_lulus' => $group->sum(fn ($e) => $tidakLulus($e->registexam)),
            'total_tidak_exam'  => $group->sum(fn ($e) => $tidakExam($e->registexam)),
        ];

        // ── Grand total ───────────────────────────────────────────
        $totalExam       = $exams->count();
        $totalPeserta    = $exams->sum(fn ($e) => $e->registexam->count());
        $totalLulus      = $exams->sum(fn ($e) => $lulus($e->registexam));
        $totalTidakLulus = $exams->sum(fn ($e) => $tidakLulus($e->registexam));
        $totalTidakExam  = $exams->sum(fn ($e) => $tidakExam($e->registexam));

        // ── Group by materi ───────────────────────────────────────
        $materiExam = $exams
            ->groupBy(fn ($e) => ($e->rkm?->materi?->alias_exam ?? $e->materi) . " | " . ($e->rkm?->materi?->kategori_exam ?? '#'))
            ->map($ringkasan);

        // ── Group by perusahaan ───────────────────────────────────
        $instansi = $exams
            ->groupBy(fn ($e) => $e->perusahaan ?? 'Unknown')
            ->map($ringkasan);

        // ── Group by instruktur ───────────────────────────────────
        $keberhasilanMengajar = $exams
            ->groupBy(fn ($e) => optional($e->rkm?->instruktur)->nama_lengkap ?? 'Unknown')
            ->map($ringkasan);

        // ── Group by kategori exam (total count per kategori) ─────
        $kategori = $exams
            ->groupBy(fn ($e) => $e->rkm?->materi?->kategori_exam ?? 'Unknown')
            ->map(fn ($group) => $group->count());

        $kategoriData = $exams
            ->groupBy(fn ($e) => $e->rkm?->materi?->kategori_exam ?? 'Unknown')
            ->map(function ($group) {
                return $group->values()->map(function ($e) {
                    return [
                        'tanggal_pengajuan' => $e->tanggal_pengajuan?->format('d M Y'),
                        'tanggal_mulai'     => $e->tanggal_mulai?->format('d M Y'),
                        'tanggal_selesai'   => $e->tanggal_selesai?->format('d M Y'),
                        'materi'            => $e->materi ?? '-',
                        'perusahaan'        => $e->perusahaan ?? '-',
                        'instruktur'        => $e->rkm?->instruktur?->nama_lengkap ?? '-',
                    ];
                });
            });

        return response()->json([
            'filter' => [
                'tahun'    => $request->tahun,
                'triwulan' => $request->triwulan,
                'bulan'    => $request->bulan,
            ],
            'total_exam'        => $totalExam,
            'total_peserta'     => $totalPeserta,
            'total_lulus'       => $totalLulus,
            'total_tidak_lulus' => $totalTidakLulus,
            'total_tidak_exam'  => $totalTidakExam,
            'materi_exam'       => $materiExam,
            'instansi'          => $instansi,
            'instruktur'        => $keberhasilanMengajar,
            'kategori'          => $kategori,
            'kategori_data'     => $kategoriData,
        ]);
    }
}
