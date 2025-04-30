<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RKM;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use App\Http\Resources\PostResource;
use App\Models\analisisrkmmingguan;
use App\Models\aprovedNetSales;
use App\Models\detailPengajuanBarang;
use App\Models\eksam;
use App\Models\karyawan;
use App\Models\Materi;
use App\Models\PengajuanBarang;
use App\Models\perhitunganNetSales;
use App\Models\Perusahaan;
use App\Models\tracking_pengajuan_barang;
use App\Models\User;
use App\Notifications\CommentNotification;
use Illuminate\Support\Facades\Notification;

class netSalesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:View PaymantAdvance', ['only' => ['index']]);
    }
    public function index()
    {
        return view('netSales.index');
    }

    public function store(Request $request)
    {
        $request->merge([
            'harga_penawaran' => str_replace('.', '', $request->harga_penawaran),
            'transportasi'    => str_replace('.', '', $request->transportasi),
            'penginapan'      => str_replace('.', '', $request->penginapan),
            'fresh_money'     => str_replace('.', '', $request->fresh_money),
            'souvenir'        => str_replace('.', '', $request->souvenir),
            'entertaint'      => str_replace('.', '', $request->entertaint),
        ]);
    
        $data = $request->validate([
            'harga_penawaran' => 'required',
            'id_rkm'          => 'required',
            'transportasi'    => 'required',
            'penginapan'      => 'required',
            'fresh_money'     => 'required',
            'entertaint'      => 'required',
            'souvenir'        => 'required',
            'tgl_pa'          => 'required',
            'tipe_pembayaran' => 'required',
        ]);
    
        $netSales = perhitunganNetSales::create($data);
    
        $spv = karyawan::where('jabatan', 'SPV Sales')->first();
    
        if ($spv) {
            $user = User::whereHas('karyawan', function ($q) use ($spv) {
                $q->where('kode_karyawan', $spv->kode_karyawan);
            })->first();
    
            if ($user) {
                $dummyComment = (object) [
                    'karyawan_key' => auth()->user()->karyawan->id ?? null,
                    'content' => 'Pengajuan Paymant Advance baru oleh Sales, anda dimohon untuk melakukan persetujuan.',
                    'materi_key' => null,
                    'rkm_key' => $data['id_rkm'],
                ];
    
                $url = url('paymantAdvance.index'); 
                $path = request()->path();
    
                Notification::send($user, new CommentNotification($dummyComment, $url, $path));
            }
        }
    
        return redirect()->route('paymantAdvance.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }

    public function getRkmDataPerBulanPerMinggu($year)
    {
        Carbon::setLocale('id');
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
    
                if ($startOfWeek->month != $date->month) {
                    $startOfWeek = $startOfWeek->addWeek();
                    $weekNumber++;
                    continue;
                }
    
                $start = $startOfWeek->format('Y-m-d');
                $end = $endOfWeek->format('Y-m-d');
    
                $rkm = RKM::with([
                        'materi',
                        'analisisrkm',
                        'perhitunganNetSales.aprovedNetSales',
                        'analisisrkm.analisisrkmmingguan'
                    ])
                    ->where('status', '0')
                    ->whereYear('tanggal_awal', $year)
                    ->whereBetween('tanggal_awal', [$start, $end])
                    ->get();
    
                $formattedItems = $rkm->map(function ($item) {
                    $status = optional($item->perhitunganNetSales)->harga_penawaran !== null ? 'Hijau' : 'Merah';
                    $tanggalAwal = Carbon::parse($item->tanggal_awal);
                    $tanggalAkhir = Carbon::parse($item->tanggal_akhir);
                    $total_harga_jual = floatval($item->harga_jual) * intval($item->pax);
    
                    $netSales = $item->perhitunganNetSales;
                    $aprovedNetSales = $netSales 
                    ? $netSales->aprovedNetSales()->latest('created_at')->first()
                    : null;                
    
                    $totalPerhitunganNetSales = floatval(
                        floatval(optional($netSales)->harga_penawaran) -
                        floatval(optional($netSales)->transportasi) -
                        floatval(optional($netSales)->fresh_money) -
                        floatval(optional($netSales)->souvenir) -
                        floatval(optional($netSales)->entertaint) -
                        floatval(optional($netSales)->penginapan)
                    );
    
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
                        'analisisRkm'     => $netSales ? $netSales->toArray() : null,
                        'id_NetSales'     => optional($netSales)->id,
                        'harga_penawaran' => optional($netSales)->harga_penawaran,
                        'transportasi'    => optional($netSales)->transportasi,
                        'fresh_money'     => optional($netSales)->fresh_money,
                        'entertaint'      => optional($netSales)->entertaint,
                        'souvenir'        => optional($netSales)->souvenir,
                        'penginapan'      => optional($netSales)->penginapan,
                        'tgl_pa'          => optional($netSales)->tgl_pa,
                        'tipe_pembayaran' => optional($netSales)->tipe_pembayaran,
                        'total'           => $totalPerhitunganNetSales,
                        'level_status'    => optional($aprovedNetSales)->level_status ?? 'Belum disetujui',
                        'keterangan'      => optional($aprovedNetSales)->keterangan ?? '-',
                    ];
                });
    
                $rkmfull = 'no data';
                if ($formattedItems->isNotEmpty()) {
                    $rkmfull = $formattedItems->every(fn($item) => $item['status'] === 'Hijau') ? 'ok' : 'pending';
                }
    
                $weekRanges[] = [
                    'rkmfull'              => $rkmfull,
                    'tahun'                => $year,
                    'bulan'                => $monthName,
                    'minggu'               => $weekNumber,
                    'tanggal_awal_minggu'  => $startOfWeek->translatedFormat('d F Y'),
                    'tanggal_akhir_minggu' => $endOfWeek->translatedFormat('d F Y'),
                    'data'                 => $formattedItems->isEmpty() ? null : $formattedItems,
                ];
    
                $startOfWeek = $startOfWeek->addWeek();
                $weekNumber++;
            }
    
            $monthRanges[] = [
                'month' => $monthName,
                'weeksData' => $weekRanges
            ];
    
            $date = $date->addMonth();
        }
    
        return new PostResource(true, 'List Detail Bulan RKM', $monthRanges);
    }
    
    public function create($id)
    {
        $rkm = RKM::with('perusahaan', 'materi')->findOrFail($id);
        $exam = eksam::where('id_rkm', $rkm->id)->first();
        if (!$exam) {
            $exam = null;
        } else {
            $exam = $exam->total;
            $exam = round($exam, 0);
        }
        $tanggalAwal = Carbon::parse($rkm->tanggal_awal);
        $tanggalAkhir = Carbon::parse($rkm->tanggal_akhir);
        // return $exam;
        $durasi = $tanggalAwal->diffInDays($tanggalAkhir);
        return view('netSales.create', compact('rkm', 'durasi', 'exam'));
    }

    public function detail($id)
    {
        return view('netSales.detail', compact('id'));
    }
    

    public function dataDetail(Request $request) {
        $id = $request->input('value');
    
        $dataRKM = RKM::where('id', $id)->first();
        $dataPerusahaan = Perusahaan::where('id', $dataRKM->perusahaan_key)->first();
        $dataSales = karyawan::where('kode_karyawan', $dataRKM->sales_key)->first();
        $dataMateri = Materi::where('id', $dataRKM->materi_key)->first();
        $dataNetSales = perhitunganNetSales::where('id_rkm', $dataRKM->id)->first();
        $dataApproved = aprovedNetSales::where('id_NetSales', $dataNetSales->id)->get();
        $mulai = Carbon::parse($dataRKM->tanggal_awal)->timezone('Asia/Jakarta');
        $akhir = Carbon::parse($dataRKM->tanggal_akhir)->timezone('Asia/Jakarta');
    
        $arrayRKM = [
            'id'              => $dataRKM->id,
            'nama_perusahaan' => $dataPerusahaan->nama_perusahaan,
            'materi'          => $dataMateri->nama_materi,
            'sales'           => $dataSales->nama_lengkap,
            'harga_jual'      => $dataRKM->harga_jual,
            'pax'             => $dataRKM->pax,
            'metode_kelas'    => $dataRKM->metode_kelas,
            'durasi_kelas' => $mulai->startOfDay()->diffInDays($akhir->startOfDay()) + 1,
        ];
    
        $arrayNetSales = [
            'id_netSales'     => $dataNetSales->id,
            'transportasi'    => $dataNetSales->transportasi,
            'penginapan'      => $dataNetSales->penginapan,
            'fresh_money'     => $dataNetSales->fresh_money,
            'entertaint'      => $dataNetSales->entertaint,
            'souvenir'        => $dataNetSales->souvenir,
            'harga_penawaran' => $dataNetSales->harga_penawaran,
            'tgl_pa'          => $dataNetSales->tgl_pa,
            'tipe_pembayaran' => $dataNetSales->tipe_pembayaran,
            'total'           => $dataNetSales->harga_penawaran - 
                                 $dataNetSales->transportasi - 
                                 $dataNetSales->penginapan - 
                                 $dataNetSales->fresh_money -
                                 $dataNetSales->entertaint -
                                 $dataNetSales->souvenir,
        ];
    
        $arrayAproved = [];
        foreach ($dataApproved as $item) {
            $arrayAproved[] = [
                'tanggal'    => $item->created_at->format('Y-m-d H:i'),
                'status'     => $item->status,
                'keterangan' => $item->keterangan,
            ];
        }
    
        return response()->json([
            'dataRKM'      => $arrayRKM,
            'dataNetSales' => $arrayNetSales,
            'dataApproved' => $arrayAproved,
        ]);
    }
    
    public function edit($id) {
        $rkm = RKM::with('perusahaan', 'materi')->findOrFail($id);
        $exam = eksam::where('id_rkm', $rkm->id)->first();
        $dataNetSales = perhitunganNetSales::where('id_rkm', $rkm->id)->first();
        if (!$exam) {
            $exam = null;
        } else {
            $exam = $exam->total;
            $exam = round($exam, 0);
        }
        $tanggalAwal = Carbon::parse($rkm->tanggal_awal);
        $tanggalAkhir = Carbon::parse($rkm->tanggal_akhir);
        $durasi = $tanggalAwal->diffInDays($tanggalAkhir);
        return view('netSales.edit', compact('rkm', 'durasi', 'exam', 'dataNetSales'));    
    }

    public function updateNetSales(Request $request) {
        $id = $request->input('id_netsales');
    
        $dataNetSales = perhitunganNetSales::where('id', $id)->first();
        
        $dataNetSales->transportasi = str_replace(['.', ','], '', $request->input('transportasi'));
        $dataNetSales->penginapan = str_replace(['.', ','], '', $request->input('penginapan'));
        $dataNetSales->fresh_money = str_replace(['.', ','], '', $request->input('fresh_money'));
        $dataNetSales->entertaint = str_replace(['.', ','], '', $request->input('entertaint'));
        $dataNetSales->souvenir = str_replace(['.', ','], '', $request->input('souvenir'));
        $dataNetSales->harga_penawaran = str_replace(['.', ','], '', $request->input('harga_penawaran'));
    
        $dataNetSales->tgl_pa = $request->input('tgl_pa');
        $dataNetSales->tipe_pembayaran = $request->input('tipe_pembayaran');
    
        $dataNetSales->save();
    
        return redirect()->back()->with('success','berhasil');
    }    
}
