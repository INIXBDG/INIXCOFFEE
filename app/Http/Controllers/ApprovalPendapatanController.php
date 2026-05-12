<?php

namespace App\Http\Controllers;

use App\Models\ApprovalPendapatan;
use App\Models\Materi;
use App\Models\outstanding;
use App\Models\Perusahaan;
use App\Models\PicPenagihanInvoice;
use App\Models\RKM;
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

        $hariIni = now();

        $rkm = RKM::with(['materi', 'perusahaan', 'sales', 'instruktur', 'outstanding', 'eksam'])
            ->whereYear('tanggal_akhir', $tahun)
            ->whereMonth('tanggal_akhir', $bulan)
            ->whereDate('tanggal_akhir', '<=', $hariIni)
            ->orderBy('tanggal_akhir')
            ->get();

        $dataValid = ApprovalPendapatan::with(['dataMateri', 'dataPerusahaan'])
            ->whereIn('id_rkm', $rkm->pluck('id'))
            ->get()
            ->keyBy('id_rkm');

        $groupedData = [];

        foreach ($rkm as $item) {
            $valid = $dataValid->get($item->id);

            $tanggalAkhir = Carbon::parse($item->tanggal_akhir);
            $weekNumber = ceil($tanggalAkhir->day / 7);

            if (!isset($groupedData[$weekNumber])) {
                $startDate = ($weekNumber - 1) * 7 + 1;
                $endDate = min($weekNumber * 7, $tanggalAkhir->daysInMonth);

                $groupedData[$weekNumber] = [
                    'range' => $startDate . ' ' . $tanggalAkhir->translatedFormat('F') . ' - ' . $endDate . ' ' . $tanggalAkhir->translatedFormat('F') . ' ' . $tahun,
                    'data' => [],
                ];
            }

            $hargaNet = (float) ($valid?->harga_net ?? ($item->harga_jual ?? 0));
            $pax = (int) ($valid?->pax ?? ($item->pax ?? 0));

            $total = $hargaNet * $pax;

            $groupedData[$weekNumber]['data'][] = [
                'id_rkm' => $item->id,
                'no_faktur' => $valid?->no_faktur ?? 'belum ada',
                'no_invoice' => $valid?->no_invoice ?? 'belum ada',
                'materi' => $valid?->dataMateri?->nama_materi ?? ($item->materi?->nama_materi ?? 'materi kosong'),
                'tanggal_training' => Carbon::parse($item->tanggal_awal)->translatedFormat('d F Y') . ' s/d ' . Carbon::parse($item->tanggal_akhir)->translatedFormat('d F Y'),
                'perusahaan' => $valid?->dataPerusahaan?->nama_perusahaan ?? ($item->perusahaan?->nama_perusahaan ?? 'perusahaan kosong'),
                'nama_sales' => $item->sales?->nama_lengkap ?? 'sales kosong',
                'instruktur' => $item->instruktur?->nama_lengkap ?? 'instruktur kosong',
                'harga' => $hargaNet,
                'pax' => $pax,
                'total' => $total,
                'diskon' => (float) ($valid?->diskon ?? 0),
                'total_diskon' => (float) ($valid?->total_diskon ?? 0),
                'total_pa' => (float) ($valid?->total_pa ?? 0),
                'total_cashback' => (float) ($valid?->total_cashback ?? 0),
                'total_uang_saku' => (float) ($valid?->total_uang_saku ?? 0),
                'total_akomodasi' => (float) ($valid?->total_akomodasi ?? 0),
                'oleh_oleh' => (float) ($valid?->oleh_oleh ?? 0),
                'total_penjualan_sales' => (float) ($valid?->total_penjualan_sales ?? 0),
                'PPN' => (float) ($valid?->PPN ?? 0),
                'PPH' => (float) ($valid?->PPH ?? 0),
                'jumlah_pembayaran' => (float) ($valid?->jumlah_pembayaran ?? 0),
                'tanggal_pembayaran' => (float) ($valid?->tanggal_pembayaran ?? 0),
                'biaya_admin' => (float) ($valid?->biaya_admin ?? 0),
                'total_piutang' => (float) ($item->outstanding?->net_sales ?? 0),
                'jenis_transport' => $valid?->jenis_transport ?? '-',
                'biaya_transport' => (float) ($valid?->biaya_transport ?? 0),
                'valid' => $valid?->status ?? 'belum tervalidasi',
                'materi_id' => $valid?->materi ?? $item->materi_key,
                'tanggal_mulai' => $valid?->tanggal_mulai ? Carbon::parse($valid->tanggal_mulai)->format('Y-m-d') : Carbon::parse($item->tanggal_awal)->format('Y-m-d'),
                'tanggal_selesai' => $valid?->tanggal_selesai ? Carbon::parse($valid->tanggal_selesai)->format('Y-m-d') : Carbon::parse($item->tanggal_akhir)->format('Y-m-d'),
                'perusahaan_id' => $valid?->perusahaan ?? $item->perusahaan_key,
                'exam' => $item->eksam 
                    ? 'Rp ' . number_format((float) $item->eksam->total, 0, ',', '.') 
                    : '-',
                'exam_value' => $item->eksam ? (float) $item->eksam->total : 0,
            ];
        }

        $footerBulanan = ApprovalPendapatan::whereYear('tanggal_selesai', $tahun)
            ->whereMonth('tanggal_selesai', $bulan)
            ->where('status', 'valid')
            ->selectRaw("
                SUM(CAST(harga_net AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total_penjualan,
                SUM(CAST(total_diskon AS UNSIGNED)) as total_diskon,
                SUM(CAST(total_pa AS UNSIGNED)) as total_pa,
                SUM(CAST(total_cashback AS UNSIGNED)) as total_cashback,
                SUM(CAST(total_uang_saku AS UNSIGNED)) as total_uang_saku,
                SUM(CAST(total_akomodasi AS UNSIGNED)) as total_akomodasi,
                SUM(CAST(oleh_oleh AS UNSIGNED)) as oleh_oleh,
                SUM(CAST(total_penjualan_sales AS UNSIGNED)) as total_penjualan_sales,
                SUM(CAST(PPN AS UNSIGNED)) as total_ppn,
                SUM(CAST(PPH AS UNSIGNED)) as total_pph,
                SUM(CAST(jumlah_pembayaran AS UNSIGNED)) as jumlah_pembayaran,
                SUM(CAST(biaya_admin AS UNSIGNED)) as biaya_admin,
                SUM(CAST(biaya_transport AS UNSIGNED)) as biaya_transport
            ")
            ->first();

        $examBulanan = RKM::with('eksam')
            ->whereYear('tanggal_akhir', $tahun)
            ->whereMonth('tanggal_akhir', $bulan)
            ->get()
            ->sum(function ($item) {
                return (float) ($item->eksam?->total ?? 0);
            });

        $footerBulanan->total_exam = $examBulanan;

        $footerTahunan = ApprovalPendapatan::whereYear('tanggal_selesai', $tahun)
            ->where('status', 'valid')
            ->selectRaw("
                SUM(CAST(harga_net AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total_penjualan,
                SUM(CAST(total_diskon AS UNSIGNED)) as total_diskon,
                SUM(CAST(total_pa AS UNSIGNED)) as total_pa,
                SUM(CAST(total_cashback AS UNSIGNED)) as total_cashback,
                SUM(CAST(total_uang_saku AS UNSIGNED)) as total_uang_saku,
                SUM(CAST(total_akomodasi AS UNSIGNED)) as total_akomodasi,
                SUM(CAST(oleh_oleh AS UNSIGNED)) as oleh_oleh,
                SUM(CAST(total_penjualan_sales AS UNSIGNED)) as total_penjualan_sales,
                SUM(CAST(PPN AS UNSIGNED)) as total_ppn,
                SUM(CAST(PPH AS UNSIGNED)) as total_pph,
                SUM(CAST(jumlah_pembayaran AS UNSIGNED)) as jumlah_pembayaran,
                SUM(CAST(biaya_admin AS UNSIGNED)) as biaya_admin,
                SUM(CAST(biaya_transport AS UNSIGNED)) as biaya_transport
            ")
            ->first();

        $examTahunan = RKM::with('eksam')
            ->whereYear('tanggal_akhir', $tahun)
            ->get()
            ->sum(function ($item) {
                return (float) ($item->eksam?->total ?? 0);
            });

        $footerTahunan->total_exam = $examTahunan;

        return response()->json([
            'groupedData' => array_values($groupedData),
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
                'total_penjualan_sales' => (float) ($validated['total_penjualan_sales'] ?? 0),
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
            ]);

            $approval->save();

            $outstanding = outstanding::where('id_rkm', $id)->first();

            if ($outstanding) {
                $netSales = $approval->total_penjualan_sales ?? $approval->harga_net;
                
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

                $picPenagihan = PicPenagihanInvoice::where('id_rkm', $id)->first();
                $picValue = $picPenagihan ? $picPenagihan->pic : $outstanding->pic;

                $outstanding->update([
                    'net_sales' => $netSales,
                    'jumlah_pembayaran' => $approval->jumlah_pembayaran,
                    'tanggal_bayar' => $approval->tanggal_pembayaran,
                    'ppn' => $approval->PPN,
                    'pph' => $approval->PPH,
                    'biaya_admin' => $approval->biaya_admin,
                    'jumlah_potongan' => !empty($potonganData) ? $potonganData : null,
                    'jenis_potongan' => !empty($potonganData) ? array_column($potonganData, 'jenis') : null,
                    'pic' => $picValue,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diupdate',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Gagal menyimpan ' . $e->getMessage(),
                ],
                500,
            );
        }
    }
}
