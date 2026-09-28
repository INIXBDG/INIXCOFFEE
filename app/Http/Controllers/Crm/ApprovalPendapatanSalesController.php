<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\ApprovalPendapatan;
use App\Models\ApprovalPendapatanSales;
use App\Models\Materi;
use App\Models\perhitunganNetSales;
use App\Models\Perusahaan;
use App\Models\Rkm;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ApprovalPendapatanSalesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $dataMateri = Materi::get();
        $dataPerusahaan = Perusahaan::get();

        return view('crm.approvalPendapatan.index', compact('dataMateri', 'dataPerusahaan'));
    }

    public function get($tahun, $bulan)
    {
        $validate = Validator::make(
            [
                'tahun' => $tahun,
                'bulan' => $bulan,
            ],
            [
                'tahun' => 'required|integer|digits:4',
                'bulan' => 'required|integer|between:1,12',
            ],
        );

        if ($validate->fails()) {
            return response()->json(
                [
                    'error' => 'Parameter tahun dan bulan tidak valid',
                ],
                400,
            );
        }

        $isPeriodeFinance = ($tahun == 2026 && $bulan >= 1 && $bulan <= 9);

        if ($isPeriodeFinance) {
            $response = $this->getFromApprovalPendapatan($tahun, $bulan);
        } else {
            $response = $this->getFromPerhitunganNetSales($tahun, $bulan);
        }

        $data = $response->getData(true);
        $data['is_finance_period'] = $isPeriodeFinance;

        return response()->json($data);
    }

    private function getFromApprovalPendapatan($tahun, $bulan)
    {
        $startDate = CarbonImmutable::create($tahun, $bulan, 1);
        $endDate = CarbonImmutable::create($tahun, $bulan, 1)->endOfMonth();
        $hariIni = now();

        $monthRanges = [];
        $startOfWeek = $startDate->copy()->startOfWeek();
        $endOfMonth = $endDate->copy();

        $weekNumber = 1;
        $weekRanges = [];

        while ($startOfWeek->lte($endOfMonth)) {
            $endOfWeek = $startOfWeek->copy()->endOfWeek();

            $start = $startOfWeek->format('Y-m-d');
            $end = $endOfWeek->format('Y-m-d');

            $rows = ApprovalPendapatan::with(['rkm', 'rkm.materi', 'rkm.perusahaan', 'rkm.sales', 'rkm.instruktur'])
                ->whereHas('rkm', function ($query) use ($start, $end, $hariIni) {
                    $query->whereBetween('tanggal_awal', [$start, $end])
                        ->whereDate('tanggal_awal', '<=', $hariIni);
                })
                ->get();

            $dataValid = ApprovalPendapatanSales::with(['dataMateri', 'dataPerusahaan'])
                ->whereIn('id_rkm', $rows->pluck('id_rkm'))
                ->get()
                ->keyBy('id_rkm');

            $weekData = [];

            foreach ($rows as $item) {
                $rkm = $item->rkm;

                if (!$rkm) {
                    continue;
                }

                $valid = $dataValid->get($item->id_rkm);

                $hargaNet = (float) ($valid?->harga_net ?? ($item->harga_net ?? 0));
                $pax = (int) ($valid?->pax ?? ($item->pax ?? 0));
                $total = $hargaNet * $pax;

                $weekData[] = [
                    'id_rkm' => $rkm->id,
                    'no_faktur' => $valid?->no_faktur ?? $item->no_faktur ?? 'belum ada',
                    'no_invoice' => $valid?->no_invoice ?? $item->no_invoice ?? 'belum ada',
                    'materi' => $valid?->dataMateri?->nama_materi ?? ($rkm->materi?->nama_materi ?? 'materi kosong'),
                    'tanggal_training' => Carbon::parse($rkm->tanggal_awal)->translatedFormat('d F Y') . ' s/d ' . Carbon::parse($rkm->tanggal_akhir)->translatedFormat('d F Y'),
                    'perusahaan' => $valid?->dataPerusahaan?->nama_perusahaan ?? ($rkm->perusahaan?->nama_perusahaan ?? 'perusahaan kosong'),
                    'nama_sales' => $rkm->sales?->nama_lengkap ?? 'sales kosong',
                    'instruktur' => $rkm->instruktur?->nama_lengkap ?? 'instruktur kosong',
                    'harga' => $hargaNet,
                    'pax' => $pax,
                    'total' => $total,
                    'total_penjualan_kotor' => $total,
                    'diskon' => (float) ($valid?->diskon ?? 0),
                    'total_diskon' => (float) ($valid?->total_diskon ?? 0),
                    'total_pa' => (float) ($valid?->total_pa ?? 0),
                    'total_cashback' => (float) ($valid?->total_cashback ?? 0),
                    'total_uang_saku' => (float) ($valid?->total_uang_saku ?? 0),
                    'total_akomodasi' => (float) ($valid?->total_akomodasi ?? 0),
                    'oleh_oleh' => (float) ($valid?->oleh_oleh ?? 0),
                    'biaya_lain_lain' => (float) ($valid?->biaya_lain_lain ?? 0),
                    'entertainment' => (float) ($valid?->entertainment ?? 0),
                    'total_penjualan_sales' => (float) ($valid?->total_penjualan_sales ?? 0),
                    'jenis_transport' => $valid?->jenis_transport ?? '-',
                    'biaya_transport' => (float) ($valid?->biaya_transport ?? 0),
                    'pengurangan_phh' => (float) ($valid?->pengurangan_phh ?? 0),
                    'exam_value' => (float) ($valid?->exam ?? 0),
                    'exam' => ($valid?->exam ?? 0) ? 'Rp ' . number_format((float) $valid->exam, 0, ',', '.') : '-',
                    'valid' => $valid?->status ?? 'belum tervalidasi',
                    'materi_id' => $valid?->materi ?? $rkm->materi_key,
                    'tanggal_mulai' => $valid?->tanggal_mulai ? Carbon::parse($valid->tanggal_mulai)->format('Y-m-d') : Carbon::parse($rkm->tanggal_awal)->format('Y-m-d'),
                    'tanggal_selesai' => $valid?->tanggal_selesai ? Carbon::parse($valid->tanggal_selesai)->format('Y-m-d') : Carbon::parse($rkm->tanggal_akhir)->format('Y-m-d'),
                    'perusahaan_id' => $valid?->perusahaan ?? $rkm->perusahaan_key,
                    'manual' => false,
                ];
            }

            $weekRanges[] = [
                'start' => $start,
                'end' => $end,
                'data' => $weekData,
                'week_number' => $weekNumber,
            ];

            $startOfWeek = $startOfWeek->addWeek();
            $weekNumber++;
        }

        $monthRanges[] = [
            'month' => $startDate->translatedFormat('F-Y'),
            'weeksData' => $weekRanges,
        ];

        return $this->buildFooterResponse($monthRanges, $tahun, $bulan);
    }

    private function getFromPerhitunganNetSales($tahun, $bulan)
    {
        $startDate = CarbonImmutable::create($tahun, $bulan, 1);
        $endDate = CarbonImmutable::create($tahun, $bulan, 1)->endOfMonth();
        $hariIni = now();

        $monthRanges = [];
        $startOfWeek = $startDate->copy()->startOfWeek();
        $endOfMonth = $endDate->copy();

        $weekNumber = 1;
        $weekRanges = [];

        while ($startOfWeek->lte($endOfMonth)) {
            $endOfWeek = $startOfWeek->copy()->endOfWeek();

            $start = $startOfWeek->format('Y-m-d');
            $end = $endOfWeek->format('Y-m-d');

            $rows = perhitunganNetSales::with(['rkm', 'rkm.materi', 'rkm.perusahaan', 'rkm.sales', 'rkm.instruktur'])
                ->whereHas('rkm', function ($query) use ($start, $end, $hariIni) {
                    $query->whereBetween('tanggal_awal', [$start, $end])
                        ->whereDate('tanggal_awal', '<=', $hariIni);
                })
                ->get();

            $idRkmDariNetSales = $rows->pluck('id_rkm')->unique()->values();

            $allApprovals = ApprovalPendapatanSales::with(['dataMateri', 'dataPerusahaan'])
                ->where(function ($q) use ($start, $end) {
                    $q->whereBetween('tanggal_mulai', [$start, $end])
                        ->orWhereHas('rkm', function ($r) use ($start, $end) {
                            $r->whereBetween('tanggal_awal', [$start, $end]);
                        });
                })
                ->get()
                ->keyBy('id_rkm');

            $idRkmManualOnly = $allApprovals->keys()->diff($idRkmDariNetSales);

            $manualRkms = collect();
            if ($idRkmManualOnly->isNotEmpty()) {
                $manualRkms = Rkm::with(['materi', 'perusahaan', 'sales', 'instruktur'])
                    ->whereIn('id', $idRkmManualOnly)
                    ->get()
                    ->keyBy('id');
            }

            $weekData = [];
            $processedIds = [];

            foreach ($rows as $item) {
                $rkm = $item->rkm;
                if (!$rkm || in_array($rkm->id, $processedIds)) {
                    continue;
                }

                $valid = $allApprovals->get($rkm->id);

                $hargaNet = (float) ($valid?->harga_net ?? ($rkm->harga_jual ?? 0));
                $pax = (int) ($valid?->pax ?? ($rkm->pax ?? 0));
                $total = $hargaNet * $pax;

                $totalPA = ($item->transportasi ?? 0)
                    + ($item->akomodasi_peserta ?? 0)
                    + ($item->akomodasi_tim ?? 0)
                    + ($item->fresh_money ?? 0)
                    + ($item->entertaint ?? 0)
                    + ($item->souvenir ?? 0)
                    + ($item->cashback ?? 0)
                    + ($item->sewa_laptop ?? 0);

                $weekData[] = [
                    'id_rkm' => $rkm->id,
                    'materi' => $valid?->dataMateri?->nama_materi ?? ($rkm->materi?->nama_materi ?? 'materi kosong'),
                    'tanggal_training' => Carbon::parse($rkm->tanggal_awal)->translatedFormat('d F Y') . ' s/d ' . Carbon::parse($rkm->tanggal_akhir)->translatedFormat('d F Y'),
                    'perusahaan' => $valid?->dataPerusahaan?->nama_perusahaan ?? ($rkm->perusahaan?->nama_perusahaan ?? 'perusahaan kosong'),
                    'nama_sales' => $rkm->sales?->nama_lengkap ?? 'sales kosong',
                    'instruktur' => $rkm->instruktur?->nama_lengkap ?? 'instruktur kosong',
                    'harga' => $hargaNet,
                    'pax' => $pax,
                    'total' => $total,
                    'total_penjualan_kotor' => $total,
                    'diskon' => (float) ($valid?->diskon ?? 0),
                    'total_diskon' => (float) ($valid?->total_diskon ?? 0),
                    'total_pa' => (float) ($valid?->total_pa ?? $totalPA),
                    'total_cashback' => (float) ($valid?->total_cashback ?? ($item->cashback ?? 0)),
                    'total_uang_saku' => (float) ($valid?->total_uang_saku ?? ($item->fresh_money ?? 0)),
                    'total_akomodasi' => (float) ($valid?->total_akomodasi ?? (($item->akomodasi_peserta ?? 0) + ($item->akomodasi_tim ?? 0))),
                    'oleh_oleh' => (float) ($valid?->oleh_oleh ?? 0),
                    'biaya_lain_lain' => (float) ($valid?->biaya_lain_lain ?? 0),
                    'entertainment' => (float) ($valid?->entertainment ?? ($item->entertaint ?? 0)),
                    'total_penjualan_sales' => (float) ($valid?->total_penjualan_sales ?? 0),
                    'jenis_transport' => $valid?->jenis_transport ?? '-',
                    'biaya_transport' => (float) ($valid?->biaya_transport ?? ($item->transportasi ?? 0)),
                    'pengurangan_phh' => (float) ($valid?->pengurangan_phh ?? 0),
                    'exam_value' => (float) ($valid?->exam ?? 0),
                    'exam' => ($valid?->exam ?? 0) ? 'Rp ' . number_format((float) $valid->exam, 0, ',', '.') : '-',
                    'valid' => $valid?->status ?? 'belum tervalidasi',
                    'materi_id' => $valid?->materi ?? $rkm->materi_key,
                    'tanggal_mulai' => $valid?->tanggal_mulai ? Carbon::parse($valid->tanggal_mulai)->format('Y-m-d') : Carbon::parse($rkm->tanggal_awal)->format('Y-m-d'),
                    'tanggal_selesai' => $valid?->tanggal_selesai ? Carbon::parse($valid->tanggal_selesai)->format('Y-m-d') : Carbon::parse($rkm->tanggal_akhir)->format('Y-m-d'),
                    'perusahaan_id' => $valid?->perusahaan ?? $rkm->perusahaan_key,
                    'manual' => false,
                ];

                $processedIds[] = $rkm->id;
            }

            foreach ($idRkmManualOnly as $idRkm) {
                if (in_array($idRkm, $processedIds)) {
                    continue;
                }

                $valid = $allApprovals->get($idRkm);
                $rkm = $manualRkms->get($idRkm);

                if (!$valid || !$rkm) {
                    continue;
                }

                $rkmStart = Carbon::parse($rkm->tanggal_awal)->format('Y-m-d');
                if ($rkmStart < $start || $rkmStart > $end) {
                    continue;
                }

                $hargaNet = (float) ($valid->harga_net ?? 0);
                $pax = (int) ($valid->pax ?? 0);
                $total = $hargaNet * $pax;

                $weekData[] = [
                    'id_rkm' => $rkm->id,
                    'materi' => $valid->dataMateri?->nama_materi ?? ($rkm->materi?->nama_materi ?? 'materi kosong'),
                    'tanggal_training' => Carbon::parse($rkm->tanggal_awal)->translatedFormat('d F Y') . ' s/d ' . Carbon::parse($rkm->tanggal_akhir)->translatedFormat('d F Y'),
                    'perusahaan' => $valid->dataPerusahaan?->nama_perusahaan ?? ($rkm->perusahaan?->nama_perusahaan ?? 'perusahaan kosong'),
                    'nama_sales' => $rkm->sales?->nama_lengkap ?? 'sales kosong',
                    'instruktur' => $rkm->instruktur?->nama_lengkap ?? 'instruktur kosong',
                    'harga' => $hargaNet,
                    'pax' => $pax,
                    'total' => $total,
                    'total_penjualan_kotor' => $total,
                    'diskon' => (float) ($valid->diskon ?? 0),
                    'total_diskon' => (float) ($valid->total_diskon ?? 0),
                    'total_pa' => (float) ($valid->total_pa ?? 0),
                    'total_cashback' => (float) ($valid->total_cashback ?? 0),
                    'total_uang_saku' => (float) ($valid->total_uang_saku ?? 0),
                    'total_akomodasi' => (float) ($valid->total_akomodasi ?? 0),
                    'oleh_oleh' => (float) ($valid->oleh_oleh ?? 0),
                    'biaya_lain_lain' => (float) ($valid->biaya_lain_lain ?? 0),
                    'entertainment' => (float) ($valid->entertainment ?? 0),
                    'total_penjualan_sales' => (float) ($valid->total_penjualan_sales ?? 0),
                    'jenis_transport' => $valid->jenis_transport ?? '-',
                    'biaya_transport' => (float) ($valid->biaya_transport ?? 0),
                    'pengurangan_phh' => (float) ($valid?->pengurangan_phh ?? 0),
                    'exam_value' => (float) ($valid->exam ?? 0),
                    'exam' => ($valid->exam ?? 0) ? 'Rp ' . number_format((float) $valid->exam, 0, ',', '.') : '-',
                    'valid' => $valid->status ?? 'valid',
                    'materi_id' => $valid->materi ?? $rkm->materi_key,
                    'tanggal_mulai' => $valid->tanggal_mulai ? Carbon::parse($valid->tanggal_mulai)->format('Y-m-d') : Carbon::parse($rkm->tanggal_awal)->format('Y-m-d'),
                    'tanggal_selesai' => $valid->tanggal_selesai ? Carbon::parse($valid->tanggal_selesai)->format('Y-m-d') : Carbon::parse($rkm->tanggal_akhir)->format('Y-m-d'),
                    'perusahaan_id' => $valid->perusahaan ?? $rkm->perusahaan_key,
                    'manual' => true,
                ];

                $processedIds[] = $idRkm;
            }

            $weekRanges[] = [
                'start' => $start,
                'end' => $end,
                'data' => $weekData,
                'week_number' => $weekNumber,
            ];

            $startOfWeek = $startOfWeek->addWeek();
            $weekNumber++;
        }

        $monthRanges[] = [
            'month' => $startDate->translatedFormat('F-Y'),
            'weeksData' => $weekRanges,
        ];

        return $this->buildFooterResponse($monthRanges, $tahun, $bulan);
    }

    private function buildFooterResponse($monthRanges, $tahun, $bulan)
    {
        $footerBulanan = ApprovalPendapatanSales::whereYear('tanggal_mulai', $tahun)
            ->whereMonth('tanggal_mulai', $bulan)
            ->where('status', 'valid')
            ->selectRaw(
                "
                SUM(CAST(harga_net AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total_penjualan,
                SUM(CAST(total_diskon AS UNSIGNED)) as total_diskon,
                SUM(CAST(total_pa AS UNSIGNED)) as total_pa,
                SUM(CAST(total_cashback AS UNSIGNED)) as total_cashback,
                SUM(CAST(total_uang_saku AS UNSIGNED)) as total_uang_saku,
                SUM(CAST(total_akomodasi AS UNSIGNED)) as total_akomodasi,
                SUM(CAST(oleh_oleh AS UNSIGNED)) as oleh_oleh,
                SUM(CAST(biaya_lain_lain AS UNSIGNED)) as biaya_lain_lain,
                SUM(CAST(entertainment AS UNSIGNED)) as entertainment,
                SUM(CAST(total_penjualan_sales AS UNSIGNED)) as total_penjualan_sales,
                SUM(CAST(biaya_transport AS UNSIGNED)) as biaya_transport,
                SUM(CAST(pengurangan_phh AS UNSIGNED)) as pengurangan_phh
            ",
            )
            ->first();

        $examBulanan = ApprovalPendapatanSales::whereYear('tanggal_mulai', $tahun)
            ->whereMonth('tanggal_mulai', $bulan)
            ->where('status', 'valid')
            ->sum('exam');

        if ($footerBulanan) {
            $footerBulanan->total_exam = $examBulanan;
        } else {
            $footerBulanan = (object) [
                'total_penjualan' => 0,
                'total_diskon' => 0,
                'total_pa' => 0,
                'total_cashback' => 0,
                'total_uang_saku' => 0,
                'total_akomodasi' => 0,
                'oleh_oleh' => 0,
                'biaya_lain_lain' => 0,
                'total_penjualan_sales' => 0,
                'biaya_transport' => 0,
                'pengurangan_phh' => 0,        // <-- tambahkan
                'total_exam' => $examBulanan,
            ];
        }

        $footerTahunan = ApprovalPendapatanSales::whereYear('tanggal_mulai', $tahun)
            ->where('status', 'valid')
            ->selectRaw(
                "
                SUM(CAST(harga_net AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total_penjualan,
                SUM(CAST(total_diskon AS UNSIGNED)) as total_diskon,
                SUM(CAST(total_pa AS UNSIGNED)) as total_pa,
                SUM(CAST(total_cashback AS UNSIGNED)) as total_cashback,
                SUM(CAST(total_uang_saku AS UNSIGNED)) as total_uang_saku,
                SUM(CAST(oleh_oleh AS UNSIGNED)) as oleh_oleh,
                SUM(CAST(biaya_lain_lain AS UNSIGNED)) as biaya_lain_lain,
                SUM(CAST(entertainment AS UNSIGNED)) as entertainment,
                SUM(CAST(total_penjualan_sales AS UNSIGNED)) as total_penjualan_sales,
                SUM(CAST(biaya_transport AS UNSIGNED)) as biaya_transport,
                SUM(CAST(pengurangan_phh AS UNSIGNED)) as pengurangan_phh
            ",
            )
            ->first();

        $examTahunan = ApprovalPendapatanSales::whereYear('tanggal_mulai', $tahun)
            ->where('status', 'valid')
            ->sum('exam');

        if ($footerTahunan) {
            $footerTahunan->total_exam = $examTahunan;
        } else {
            $footerTahunan = (object) [
                'total_penjualan' => 0,
                'total_diskon' => 0,
                'total_pa' => 0,
                'total_cashback' => 0,
                'total_uang_saku' => 0,
                'total_akomodasi' => 0,
                'oleh_oleh' => 0,
                'biaya_lain_lain' => 0,
                'entertainment' => 0,
                'total_penjualan_sales' => 0,
                'biaya_transport' => 0,
                'pengurangan_phh' => 0,
                'total_exam' => $examTahunan,
            ];
        }

        return response()->json([
            'data' => $monthRanges,
            'footer_bulanan' => $footerBulanan,
            'footer_tahunan' => $footerTahunan,
        ]);
    }

    public function searchRkm(Request $request)
    {
        $start = $request->get('start'); 
        $end   = $request->get('end');   

        $usedIds = ApprovalPendapatanSales::pluck('id_rkm');

        $query = Rkm::with(['materi', 'perusahaan', 'sales', 'instruktur']) 
            ->whereNotIn('id', $usedIds);

        if ($start && $end) {
            $query->whereBetween('tanggal_awal', [$start, $end]);
        }

        $result = $query->orderBy('tanggal_awal')
            ->get()
            ->map(function ($rkm) {
                $tgl = Carbon::parse($rkm->tanggal_awal)->translatedFormat('d M Y');
                $tglAkhir = Carbon::parse($rkm->tanggal_akhir)->translatedFormat('d M Y');

                return [
                    'id'    => $rkm->id,
                    'label' => ($rkm->materi?->nama_materi ?? 'materi kosong')
                        . ' — ' . ($rkm->perusahaan?->nama_perusahaan ?? 'perusahaan kosong')
                        . ' — ' . ($rkm->sales?->nama_lengkap ?? 'sales kosong')
                        . ' (' . $tgl . ' s/d ' . $tglAkhir . ')',
                ];
            });

        return response()->json($result);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_rkm' => 'required|integer|exists:r_k_m_s,id',
        ]);

        $sudahAda = ApprovalPendapatanSales::where('id_rkm', $validated['id_rkm'])->exists();

        if ($sudahAda) {
            return response()->json([
                'success' => false,
                'message' => 'RKM ini sudah memiliki data penjualan',
            ], 422);
        }

        DB::beginTransaction();

        try {
            $rkm = Rkm::with(['materi', 'perusahaan', 'sales', 'instruktur'])->findOrFail($validated['id_rkm']);

            // Ambil data perhitungan net sales jika ada (untuk nilai default)
            $netSales = perhitunganNetSales::where('id_rkm', $rkm->id)->first();

            $hargaNet     = (float) ($rkm->harga_jual ?? 0);
            $pax          = (int) ($rkm->pax ?? 0);
            $totalKotor   = $hargaNet * $pax;

            // Nilai default dari perhitunganNetSales (jika ada)
            $totalPA          = 0;
            $totalCashback    = 0;
            $totalUangSaku    = 0;
            $totalAkomodasi   = 0;
            $entertainment    = 0;
            $biayaTransport   = 0;

            if ($netSales) {
                $totalPA        = (float) (
                    ($netSales->transportasi ?? 0) +
                    ($netSales->akomodasi_peserta ?? 0) +
                    ($netSales->akomodasi_tim ?? 0) +
                    ($netSales->fresh_money ?? 0) +
                    ($netSales->entertaint ?? 0) +
                    ($netSales->souvenir ?? 0) +
                    ($netSales->cashback ?? 0) +
                    ($netSales->sewa_laptop ?? 0)
                );
                $totalCashback  = (float) ($netSales->cashback ?? 0);
                $totalUangSaku  = (float) ($netSales->fresh_money ?? 0);
                $totalAkomodasi = (float) (($netSales->akomodasi_peserta ?? 0) + ($netSales->akomodasi_tim ?? 0));
                $entertainment  = (float) ($netSales->entertaint ?? 0);
                $biayaTransport = (float) ($netSales->transportasi ?? 0);
            }

            $approval = ApprovalPendapatanSales::create([
                'id_rkm'                => $rkm->id,
                'materi'                => $rkm->materi_key,
                'perusahaan'            => $rkm->perusahaan_key,
                'tanggal_mulai'         => $rkm->tanggal_awal,
                'tanggal_selesai'       => $rkm->tanggal_akhir,
                'harga_net'             => $hargaNet,
                'pax'                   => $pax,
                'diskon'                => 0,
                'total_diskon'          => 0,
                'total_pa'              => $totalPA,
                'total_cashback'        => $totalCashback,
                'total_uang_saku'       => $totalUangSaku,
                'total_akomodasi'       => $totalAkomodasi,
                'oleh_oleh'             => 0,
                'biaya_lain_lain'       => 0,
                'entertainment'         => $entertainment,
                'biaya_transport'       => $biayaTransport,
                'pengurangan_phh'       => 0,
                'exam'                  => 0,
                'total_penjualan_sales' => 0,
                'status'                => 'valid',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'id_rkm'                => $rkm->id,
                    'materi'                => $rkm->materi?->nama_materi ?? 'materi kosong',
                    'tanggal_training'      => Carbon::parse($rkm->tanggal_awal)->translatedFormat('d F Y') . ' s/d ' . Carbon::parse($rkm->tanggal_akhir)->translatedFormat('d F Y'),
                    'perusahaan'            => $rkm->perusahaan?->nama_perusahaan ?? 'perusahaan kosong',
                    'nama_sales'            => $rkm->sales?->nama_lengkap ?? 'sales kosong',
                    'instruktur'            => $rkm->instruktur?->nama_lengkap ?? 'instruktur kosong',
                    'harga'                 => $hargaNet,
                    'pax'                   => $pax,
                    'total'                 => $totalKotor,
                    'total_penjualan_kotor' => $totalKotor,
                    'diskon'                => 0,
                    'total_diskon'          => 0,
                    'total_pa'              => $totalPA,
                    'total_cashback'        => $totalCashback,
                    'total_uang_saku'       => $totalUangSaku,
                    'total_akomodasi'       => $totalAkomodasi,
                    'oleh_oleh'             => 0,
                    'biaya_lain_lain'       => 0,
                    'entertainment'         => $entertainment,
                    'total_penjualan_sales' => 0,
                    'jenis_transport'       => '-',
                    'biaya_transport'       => $biayaTransport,
                    'pengurangan_phh'       => 0,
                    'exam_value'            => 0,
                    'exam'                  => '-',
                    'valid'                 => 'valid',
                    'materi_id'             => $rkm->materi_key,
                    'perusahaan_id'         => $rkm->perusahaan_key,
                    'tanggal_mulai'         => Carbon::parse($rkm->tanggal_awal)->format('Y-m-d'),
                    'tanggal_selesai'       => Carbon::parse($rkm->tanggal_akhir)->format('Y-m-d'),
                    'manual'                => true,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat data baru: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'no_faktur' => 'nullable|string|max:255',
            'no_invoice' => 'nullable|string|max:255',
            'harga' => 'nullable|numeric|min:0',
            'pax' => 'nullable|integer|min:0',
            'diskon' => 'nullable|numeric',
            'total_diskon' => 'nullable|numeric',
            'total_pa' => 'nullable|numeric',
            'total_cashback' => 'nullable|numeric',
            'total_uang_saku' => 'nullable|numeric',
            'total_akomodasi' => 'nullable|numeric',
            'jenis_transport' => 'nullable|string|max:255',
            'biaya_transport' => 'nullable|numeric',
            'oleh_oleh' => 'nullable|numeric',
            'biaya_lain_lain' => 'nullable|numeric|min:0',
            'entertainment' => 'nullable|numeric',
            'pengurangan_phh' => 'nullable|numeric|min:0',
            'exam' => 'nullable|numeric|min:0',
            'total_penjualan_sales' => 'nullable|numeric|min:0',
            'materi' => 'nullable',
            'perusahaan' => 'nullable',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        DB::beginTransaction();

        try {
            $approval = ApprovalPendapatanSales::firstOrNew([
                'id_rkm' => $id,
            ]);

            $approval->fill([
                'no_faktur' => $validated['no_faktur'] ?? null,
                'no_invoice' => $validated['no_invoice'] ?? null,
                'harga_net' => (float) ($validated['harga'] ?? 0),
                'pax' => (int) ($validated['pax'] ?? 0),
                'diskon' => (float) ($validated['diskon'] ?? 0),
                'total_diskon' => (float) ($validated['total_diskon'] ?? 0),
                'total_pa' => (float) ($validated['total_pa'] ?? 0),
                'total_cashback' => (float) ($validated['total_cashback'] ?? 0),
                'total_uang_saku' => (float) ($validated['total_uang_saku'] ?? 0),
                'total_akomodasi' => (float) ($validated['total_akomodasi'] ?? 0),
                'jenis_transport' => $validated['jenis_transport'] ?? null,
                'biaya_transport' => (float) ($validated['biaya_transport'] ?? 0),
                'oleh_oleh' => (float) ($validated['oleh_oleh'] ?? 0),
                'biaya_lain_lain' => (float) ($validated['biaya_lain_lain'] ?? 0),
                'entertainment' => (float) ($validated['entertainment'] ?? 0),
                'pengurangan_phh' => (float) ($validated['pengurangan_phh'] ?? 0),
                'exam' => (float) ($validated['exam'] ?? 0),
                'total_penjualan_sales' => (float) ($validated['total_penjualan_sales'] ?? 0),
                'status' => 'valid',
                'materi' => $validated['materi'] ?? null,
                'perusahaan' => $validated['perusahaan'] ?? null,
                'tanggal_mulai' => $validated['tanggal_mulai'] ?? null,
                'tanggal_selesai' => $validated['tanggal_selesai'] ?? null,
            ]);

            $approval->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diupdate',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan ' . $e->getMessage(),
            ], 500);
        }
    }
}