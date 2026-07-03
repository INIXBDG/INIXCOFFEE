<?php

namespace App\Http\Controllers;

use App\Models\ApprovalPendapatan;
use App\Models\Invoice;
use App\Models\Materi;
use App\Models\outstanding;
use App\Models\Perusahaan;
use App\Models\PicPenagihanInvoice;
use App\Models\RKM;
use App\Models\trackingOutstanding;
use Carbon\CarbonImmutable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ApprovalPendapatanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $dataMateri = Materi::get();
        $dataPerusahaan = Perusahaan::get();

        return view('office.approvalPendapatan.index', compact('dataMateri', 'dataPerusahaan'));
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

            // Format tanggal untuk query
            $start = $startOfWeek->format('Y-m-d');
            $end = $endOfWeek->format('Y-m-d');

            // Query data untuk minggu ini
            $rows = Invoice::with(['rkm', 'rkm.materi', 'rkm.perusahaan', 'rkm.sales', 'rkm.instruktur', 'rkm.outstanding', 'rkm.eksam', 'rkm.outstanding.tracking_outstanding'])
                ->whereHas('rkm', function ($query) use ($start, $end, $hariIni) {
                    $query->whereBetween('tanggal_awal', [$start, $end])
                        ->whereDate('tanggal_awal', '<=', $hariIni);
                })
                ->get();

            $dataValid = ApprovalPendapatan::with(['dataMateri', 'dataPerusahaan'])
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

                $hargaNet = (float) ($valid?->harga_net ?? ($item->harga_jual ?? 0));
                $pax = (int) ($valid?->pax ?? ($item->pax ?? 0));
                $total = (float) ($valid?->total_penjualan_kotor ?? $hargaNet * $pax);

                $weekData[] = [
                    'id_rkm' => $rkm->id,
                    'no_faktur' => $valid?->no_faktur ?? 'belum ada',
                    'no_invoice' => $valid?->no_invoice ?? 'belum ada',
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
                    'total_penjualan_sales' => (float) ($valid?->total_penjualan_bersih ?? 0),
                    'PPN' => (float) ($valid?->PPN ?? 0),
                    'PPH' => (float) ($valid?->PPH ?? 0),
                    'jumlah_pembayaran' => (float) ($valid?->jumlah_pembayaran ?? 0),
                    'tanggal_pembayaran' => $valid?->tanggal_pembayaran ?? null,
                    'biaya_admin' => (float) ($valid?->biaya_admin ?? 0),
                    'total_piutang' => (float) ($rkm->outstanding?->net_sales ?? 0),
                    'jenis_transport' => $valid?->jenis_transport ?? '-',
                    'biaya_transport' => (float) ($valid?->biaya_transport ?? 0),
                    'valid' => $valid?->status ?? 'belum tervalidasi',
                    'materi_id' => $valid?->materi ?? $rkm->materi_key,
                    'tanggal_mulai' => $valid?->tanggal_mulai ? Carbon::parse($valid->tanggal_mulai)->format('Y-m-d') : Carbon::parse($rkm->tanggal_awal)->format('Y-m-d'),
                    'tanggal_selesai' => $valid?->tanggal_selesai ? Carbon::parse($valid->tanggal_selesai)->format('Y-m-d') : Carbon::parse($rkm->tanggal_akhir)->format('Y-m-d'),
                    'perusahaan_id' => $valid?->perusahaan ?? $rkm->perusahaan_key,
                    'exam' => ($valid?->exam ?? $rkm->eksam?->total) ? 'Rp ' . number_format((float) ($valid?->exam ?? $rkm->eksam?->total), 0, ',', '.') : '-',

                    'exam_value' => (float) ($valid?->exam ?? $rkm->eksam?->total ?? 0),
                    'tracking' => $rkm->outstanding?->tracking_outstanding ? [
                        'invoice' => (bool)$rkm->outstanding->tracking_outstanding->invoice,
                        'faktur_pajak' => (bool)$rkm->outstanding->tracking_outstanding->faktur_pajak,
                        'dokumen_tambahan' => (bool)$rkm->outstanding->tracking_outstanding->dokumen_tambahan,
                        'konfir_cs' => (bool)$rkm->outstanding->tracking_outstanding->konfir_cs,
                        'tracking_dokumen' => (bool)$rkm->outstanding->tracking_outstanding->tracking_dokumen,
                        'no_resi' => (bool)$rkm->outstanding->tracking_outstanding->no_resi,
                        'konfir_pic' => (bool)$rkm->outstanding->tracking_outstanding->konfir_pic,
                        'pembayaran' => (bool)$rkm->outstanding->tracking_outstanding->pembayaran,
                        'status_resi' => $rkm->outstanding->tracking_outstanding->status_resi,
                        'status_pic' => $rkm->outstanding->tracking_outstanding->status_pic ?? '-',
                    ] : null,
                ];
            }

            $weekRanges[] = [
                'start' => $start,
                'end' => $end,
                'data' => $weekData,
                'week_number' => $weekNumber,
            ];

            // Pindah ke minggu berikutnya
            $startOfWeek = $startOfWeek->addWeek();
            $weekNumber++;
        }

        $monthRanges[] = [
            'month' => $startDate->translatedFormat('F-Y'),
            'weeksData' => $weekRanges,
        ];

        $footerBulanan = ApprovalPendapatan::whereYear('tanggal_mulai', $tahun)
            ->whereMonth('tanggal_mulai', $bulan)
            ->where('status', 'valid')
            ->selectRaw(
                "
                SUM(CAST(total_penjualan_kotor AS UNSIGNED)) as total_penjualan,
                SUM(CAST(total_diskon AS UNSIGNED)) as total_diskon,
                SUM(CAST(total_pa AS UNSIGNED)) as total_pa,
                SUM(CAST(total_cashback AS UNSIGNED)) as total_cashback,
                SUM(CAST(total_uang_saku AS UNSIGNED)) as total_uang_saku,
                SUM(CAST(total_akomodasi AS UNSIGNED)) as total_akomodasi,
                SUM(CAST(oleh_oleh AS UNSIGNED)) as oleh_oleh,
                SUM(CAST(total_penjualan_bersih AS UNSIGNED)) as total_penjualan_sales,
                SUM(CAST(PPN AS UNSIGNED)) as total_ppn,
                SUM(CAST(PPH AS UNSIGNED)) as total_pph,
                SUM(CAST(jumlah_pembayaran AS UNSIGNED)) as jumlah_pembayaran,
                SUM(CAST(biaya_admin AS UNSIGNED)) as biaya_admin,
                SUM(CAST(biaya_transport AS UNSIGNED)) as biaya_transport
            ",
            )
            ->first();

        $examBulanan = ApprovalPendapatan::whereYear('tanggal_mulai', $tahun)
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
                'total_penjualan_sales' => 0,
                'total_ppn' => 0,
                'total_pph' => 0,
                'jumlah_pembayaran' => 0,
                'biaya_admin' => 0,
                'biaya_transport' => 0,
                'total_exam' => $examBulanan,
            ];
        }

        $footerTahunan = ApprovalPendapatan::whereYear('tanggal_mulai', $tahun)
            ->where('status', 'valid')
            ->selectRaw(
                "
                SUM(CAST(total_penjualan_kotor AS UNSIGNED)) as total_penjualan,
                SUM(CAST(total_diskon AS UNSIGNED)) as total_diskon,
                SUM(CAST(total_pa AS UNSIGNED)) as total_pa,
                SUM(CAST(total_cashback AS UNSIGNED)) as total_cashback,
                SUM(CAST(total_uang_saku AS UNSIGNED)) as total_uang_saku,
                SUM(CAST(total_akomodasi AS UNSIGNED)) as total_akomodasi,
                SUM(CAST(oleh_oleh AS UNSIGNED)) as oleh_oleh,
                SUM(CAST(total_penjualan_bersih AS UNSIGNED)) as total_penjualan_sales,
                SUM(CAST(PPN AS UNSIGNED)) as total_ppn,
                SUM(CAST(PPH AS UNSIGNED)) as total_pph,
                SUM(CAST(jumlah_pembayaran AS UNSIGNED)) as jumlah_pembayaran,
                SUM(CAST(biaya_admin AS UNSIGNED)) as biaya_admin,
                SUM(CAST(biaya_transport AS UNSIGNED)) as biaya_transport
            ",
            )
            ->first();

        $examTahunan = ApprovalPendapatan::whereYear('tanggal_mulai', $tahun)
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
                'total_penjualan_sales' => 0,
                'total_ppn' => 0,
                'total_pph' => 0,
                'jumlah_pembayaran' => 0,
                'biaya_admin' => 0,
                'biaya_transport' => 0,
                'total_exam' => $examTahunan,
            ];
        }

        return response()->json([
            'data' => $monthRanges,
            'footer_bulanan' => $footerBulanan,
            'footer_tahunan' => $footerTahunan,
        ]);
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
            'exam' => 'nullable|numeric|min:0',
            'total_penjualan_sales' => 'nullable|numeric|min:0',
            'PPN' => 'nullable|numeric',
            'PPH' => 'nullable|numeric',
            'materi' => 'nullable',
            'perusahaan' => 'nullable',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'jumlah_pembayaran' => 'nullable|numeric',
            'tanggal_pembayaran' => 'nullable',
            'biaya_admin' => 'nullable|numeric',
            'pic' => 'nullable',
            'regist' => 'nullable',
            'faktur_pajak' => 'nullable|boolean',
            'dokumen_tambahan' => 'nullable|boolean',
            'konfir_cs' => 'nullable|boolean',
            'tracking_dokumen' => 'nullable|boolean',
            'no_resi' => 'nullable|boolean',
            'konfir_pic' => 'nullable|boolean',
            'pembayaran' => 'nullable|boolean',
            'status_pic' => 'nullable',
            'total_penjualan_kotor' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $approval = ApprovalPendapatan::firstOrNew([
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
                'exam' => (float) ($validated['exam'] ?? 0),
                'total_penjualan_bersih' => (float) ($validated['total_penjualan_sales'] ?? 0),
                'PPN' => (float) ($validated['PPN'] ?? 0),
                'PPH' => (float) ($validated['PPH'] ?? 0),
                'status' => 'valid',
                'materi' => $validated['materi'] ?? null,
                'tanggal_mulai' => $validated['tanggal_mulai'] ?? null,
                'tanggal_selesai' => $validated['tanggal_selesai'] ?? null,
                'perusahaan' => $validated['perusahaan'] ?? null,
                'jumlah_pembayaran' => (float) ($validated['jumlah_pembayaran'] ?? 0),
                'tanggal_pembayaran' => $validated['tanggal_pembayaran'] ?? null,
                'biaya_admin' => (float) ($validated['biaya_admin'] ?? 0),
                'total_penjualan_kotor' => (float) ($validated['total_penjualan_kotor'] ?? 0),
            ]);

            $approval->save();

            $statusPembayaran = ($approval->jumlah_pembayaran > 0) ? '1' : '0';

            $picPenagihan = PicPenagihanInvoice::where('id_rkm', $id)->first();
            $picValue = $picPenagihan?->pic ?? null;

            $potonganData = [];

            if ($approval->PPN) {
                $potonganData[] = ['jenis' => 'PPN', 'jumlah' => $approval->PPN];
            }

            if ($approval->PPH) {
                $potonganData[] = ['jenis' => 'PPH', 'jumlah' => $approval->PPH];
            }

            if ($approval->biaya_admin) {
                $potonganData[] = ['jenis' => 'biaya_admin', 'jumlah' => $approval->biaya_admin];
            }

            Outstanding::updateOrCreate(
                ['id_rkm' => $approval->id_rkm],
                [
                    'net_sales' => $approval->harga_net ?? $approval->harga_net,
                    'jumlah_pembayaran' => $approval->jumlah_pembayaran,
                    'due_date' => $approval->invoice?->due_date ?? null,
                    'pic' => $validated['pic'] ?? null,
                    'tanggal_bayar' => $approval->tanggal_pembayaran,
                    'status_pembayaran' => $statusPembayaran,
                    'ppn' => $approval->PPN,
                    'sales_key' => $approval->rkm?->sales_key ?? null,
                    'no_invoice' => $approval->no_invoice ?? null,
                    'no_regist' => $validated['regist'] ?? null,
                    'pph' => $approval->PPH,
                    'biaya_admin' => $approval->biaya_admin,
                    'jumlah_potongan' => !empty($potonganData) ? $potonganData : null,
                    'jenis_potongan' => !empty($potonganData) ? array_column($potonganData, 'jenis') : null,
                    'pic' => $picValue,
                ]
            );

            $outstanding = outstanding::where('id_rkm', $approval->id_rkm)->first();
            if ($outstanding) {
                trackingOutstanding::updateOrCreate(
                    ['id_outstanding' => $outstanding->id],
                    [
                        'invoice' => '1',
                        'faktur_pajak' => $request->boolean('faktur_pajak') ? '1' : '0',
                        'dokumen_tambahan' => $request->boolean('dokumen_tambahan') ? '1' : '0',
                        'konfir_cs' => $request->boolean('konfir_cs') ? '1' : '0',
                        'tracking_dokumen' => $request->boolean('tracking_dokumen') ? '1' : '0',
                        'no_resi' => $request->boolean('konfir_pic') ? '1' : '0',
                        'konfir_pic' => $request->boolean('konfir_pic') ? '1' : '0',
                        'pembayaran' => $request->boolean('pembayaran') ? '1' : '0',
                        'status_resi' => $request->boolean('status_resi') ? '1' : '0',
                        'status_pic' => $validated['status_pic'] ?? '-',
                    ]
                );
            }

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
