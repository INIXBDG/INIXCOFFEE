<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RKM;
use App\Models\Peluang;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use App\Http\Resources\PostResource;
use App\Models\analisisrkmmingguan;
use App\Models\approvedNetSales;
use App\Models\detailPengajuanBarang;
use App\Models\eksam;
use App\Models\HistoryNetSales;
use App\Models\karyawan;
use App\Models\Materi;
use App\Models\PengajuanBarang;
use App\Models\perhitunganNetSales;
use App\Models\Perusahaan;
use App\Models\tracking_pengajuan_barang;
use App\Models\trackingNetSales;
use App\Models\User;
use App\Notifications\CommentNotification;
use App\Notifications\UpdateLaporanPenjualan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class netSalesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:View PaymantAdvance', ['only' => ['index']]);
    }

    private const KOLOM_BIAYA = [
        'transportasi', 'akomodasi_peserta', 'akomodasi_tim', 'fresh_money',
        'entertaint', 'souvenir', 'cashback', 'oleh_oleh', 'biaya_lain_lain',
        'biaya_exam', 'sewa_laptop',
    ];

    private function totalBiaya($rows): float
    {
        $total = 0.0;
        foreach (self::KOLOM_BIAYA as $kolom) {
            $total += (float) $rows->sum($kolom);
        }
        return $total;
    }

    public function index()
    {
        return view('netSales.index');
    }

    public function store(Request $request)
    {
        foreach (self::KOLOM_BIAYA as $f) {
            $v = $request->input($f);
            $request->merge([$f => $v ? str_replace('.', '', $v) : null]);
        }

        $data = $request->validate([
            'id_rkm' => 'required',
            'transportasi' => 'nullable|numeric',
            'jenis_transportasi' => 'nullable',
            'akomodasi_peserta' => 'nullable|numeric',
            'akomodasi_tim' => 'nullable|numeric',
            'keterangan_akomodasi_tim' => 'nullable',
            'fresh_money' => 'nullable|numeric',
            'entertaint' => 'nullable|numeric',
            'keterangan_entertaint' => 'nullable',
            'souvenir' => 'nullable|numeric',
            'cashback' => 'nullable|numeric',
            'sewa_laptop' => 'nullable|numeric',
            'tgl_pa' => 'required',
            'tipe_pembayaran' => 'required|in:Cash,Transfer',
            'deskripsi_tambahan' => 'nullable',
            'oleh_oleh'             => 'nullable|numeric',
            'biaya_lain_lain'       => 'nullable|numeric',
            'keterangan_biaya_lain' => 'nullable|string|max:500',
            'biaya_exam'            => 'nullable|numeric',
        ]);

        $tracking = trackingNetSales::create([
            'id_rkm' => $data['id_rkm'],
        ]);

        $data['id_tracking'] = $tracking->id;

        $netSales = perhitunganNetSales::create($data);

        $spv = karyawan::where('jabatan', 'SPV Sales')->first();

        if ($spv) {
            $user = User::whereHas('karyawan', function ($q) use ($spv) {
                $q->where('kode_karyawan', $spv->kode_karyawan);
            })->first();

            if ($user) {
                $dummyComment = (object) [
                    'karyawan_key' => auth()->user()->karyawan->id ?? null,
                    'content' => 'Pengajuan Payment Advance baru oleh Sales, anda dimohon untuk melakukan persetujuan.',
                    'materi_key' => null,
                    'rkm_key' => $data['id_rkm'],
                ];

                $url = url('paymentAdvance.index');

                $path = request()->path();
                $receiverId = $user->id;
                Notification::send($user, new CommentNotification($dummyComment, $url, $path, $receiverId));
            }
        }

        return redirect()
            ->route('paymantAdvance.index')
            ->with(['success' => 'Data Berhasil Disimpan!']);
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

    public function getRkmDataPerBulanPerMinggu($year, $month)
    {
        if (!is_numeric($year) || !is_numeric($month)) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter tahun dan bulan harus berupa angka.',
                'data' => [],
            ]);
        }

        Carbon::setLocale('id');

        try {
            $startDate = CarbonImmutable::create($year, $month, 1);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Format bulan atau tahun tidak valid.',
                'data' => [],
            ]);
        }

        $endDate = CarbonImmutable::create($year, $month, 1)->endOfMonth();

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

                $rkm = RKM::with(['materi', 'analisisrkm', 'perhitunganNetSales.approvedNetSales', 'analisisrkm.analisisrkmmingguan', 'perusahaan', 'outstanding'])
                    ->where('status', '0')
                    ->whereYear('tanggal_awal', $year)
                    ->whereBetween('tanggal_awal', [$start, $end])
                    ->whereHas('outstanding', function ($query) {
                        $query->where('status_pembayaran', '1');
                    })
                    ->whereHas('perhitunganNetSales')
                    ->get();

                $formattedItems = $rkm->map(function ($item) {
                    $status = $item->perhitunganNetSales->isNotEmpty() ? 'Hijau' : 'Merah';
                    $tanggalAwal = Carbon::parse($item->tanggal_awal);
                    $tanggalAkhir = Carbon::parse($item->tanggal_akhir);
                    $total_harga_jual = floatval($item->harga_jual) * intval($item->pax);

                    $netSalesList = $item->perhitunganNetSales;

                    $transportasi = $netSalesList->sum('transportasi');
                    $akomodasi_peserta = $netSalesList->sum('akomodasi_peserta');
                    $akomodasi_tim = $netSalesList->sum('akomodasi_tim');
                    $fresh_money = $netSalesList->sum('fresh_money');
                    $entertaint = $netSalesList->sum('entertaint');
                    $souvenir = $netSalesList->sum('souvenir');
                    $cashback = $netSalesList->sum('cashback');
                    $sewa_laptop = $netSalesList->sum('sewa_laptop');

                    $oleh_oleh = $netSalesList->sum('oleh_oleh');
                    $biaya_lain_lain = $netSalesList->sum('biaya_lain_lain');
                    $biaya_exam = $netSalesList->sum('biaya_exam');

                    // Kalkulasi Total Penawaran
                    $totalPenawaran = floatval($item->pax * $item->harga_jual);

                    // Kalkulasi Potongan 11%
                    $potonganPajak = $totalPenawaran * (11 / 100);

                    // Kalkulasi Total Perhitungan Net Sales
                    $totalPerhitunganNetSales = floatval($totalPenawaran - $potonganPajak - $this->totalBiaya($netSalesList));
                    $latestApproved = $netSalesList->flatMap->approvedNetSales->sortByDesc('created_at')->first();

                    return [
                        'netsales' => $netSalesList,
                        'id' => $item->id,
                        'nama_materi' => $item->materi->nama_materi,
                        'pax' => $item->pax,
                        'sales_key' => $item->sales_key,
                        'harga_jual' => $item->harga_jual,
                        'total_harga_jual' => $total_harga_jual,
                        'tanggal_awal' => $tanggalAwal->translatedFormat('d F Y'),
                        'tanggal_akhir' => $tanggalAkhir->translatedFormat('d F Y'),
                        'durasi' => $tanggalAwal->diffInDays($tanggalAkhir) + 1,
                        'status' => $status,
                        'analisisRkm' => $netSalesList->toArray(),
                        'transportasi' => $transportasi,
                        'akomodasi_peserta' => $akomodasi_peserta,
                        'akomodasi_tim' => $akomodasi_tim,
                        'fresh_money' => $fresh_money,
                        'entertaint' => $entertaint,
                        'souvenir' => $souvenir,
                        'cashback' => $cashback,
                        'sewa_laptop' => $sewa_laptop,
                        'oleh_oleh' => $oleh_oleh,
                        'biaya_lain_lain' => $biaya_lain_lain,
                        'biaya_exam' => $biaya_exam,
                        'total' => $totalPerhitunganNetSales,
                        'level_status' => optional($latestApproved)->level_status ?? 'Belum Disetujui',
                        'keterangan' => optional($latestApproved)->keterangan ?? '-',
                        'perusahaan' => $item->perusahaan,
                    ];
                });

                $rkmfull = 'no data';
                if ($formattedItems->isNotEmpty()) {
                    $rkmfull = $formattedItems->every(fn($item) => $item['status'] === 'Hijau') ? 'ok' : 'pending';
                }

                $weekRanges[] = [
                    'rkmfull' => $rkmfull,
                    'tahun' => $year,
                    'bulan' => $monthName,
                    'minggu' => $weekNumber,
                    'tanggal_awal_minggu' => $startOfWeek->translatedFormat('d F Y'),
                    'tanggal_akhir_minggu' => $endOfWeek->translatedFormat('d F Y'),
                    'data' => $formattedItems->isEmpty() ? null : $formattedItems,
                ];

                $startOfWeek = $startOfWeek->addWeek();
                $weekNumber++;
            }

            $monthRanges[] = [
                'month' => $monthName,
                'weeksData' => $weekRanges,
            ];

            $date = $date->addMonth();
        }

        return new PostResource(true, 'List Detail Bulan RKM', $monthRanges);
    }

    public function detail($id)
    {
        $historyNet = HistoryNetSales::with('user')->where('id_rkm', $id)->get();
        return view('netSales.detail', compact('id', 'historyNet'));
    }

    public function dataDetail(Request $request)
    {
        $id = $request->input('value');

        $dataRKM = RKM::where('id', $id)->first();
        $dataPerusahaan = Perusahaan::where('id', $dataRKM->perusahaan_key)->first();
        $dataSales = karyawan::where('kode_karyawan', $dataRKM->sales_key)->first();
        $dataMateri = Materi::where('id', $dataRKM->materi_key)->first();
        $dataNetSales = perhitunganNetSales::with('peserta', 'rkm')->where('id_rkm', $dataRKM->id)->get();
        $dataTracking = trackingNetSales::where('id', $dataNetSales->first()->id_tracking)->first();

        $mulai = Carbon::parse($dataRKM->tanggal_awal)->timezone('Asia/Jakarta');
        $akhir = Carbon::parse($dataRKM->tanggal_akhir)->timezone('Asia/Jakarta');

        $arrayRKM = [
            'id' => $dataRKM->id,
            'nama_perusahaan' => $dataPerusahaan->nama_perusahaan,
            'materi' => $dataMateri->nama_materi,
            'sales' => $dataSales->nama_lengkap,
            'harga_jual' => $dataRKM->harga_jual,
            'pax' => $dataRKM->pax,
            'metode_kelas' => $dataRKM->metode_kelas,
            'durasi_kelas' => $mulai->startOfDay()->diffInDays($akhir->startOfDay()) + 1,
        ];

        $arrayNetSales = [];
        $grandtotal = 0;
        foreach ($dataNetSales as $netSale) {
            $dataApproved = approvedNetSales::where('id_rkm', $dataNetSales->first()->id_rkm)->get();

            // Kalkulasi Pemotongan PPN 11%
            $totalPenawaran = $netSale->rkm->pax * $netSale->rkm->harga_jual;
            $potonganPajak = $totalPenawaran * (11 / 100);

            $total = $totalPenawaran - $potonganPajak - $this->totalBiaya(collect([$netSale]));
            $totalPA = $this->totalBiaya(collect([$netSale])) - (float) $netSale->cashback;
            $netSaleData = [
                'id_netSales' => $netSale->id,
                'transportasi' => $netSale->transportasi,
                'jenis_transportasi' => $netSale->jenis_transportasi,
                'akomodasi_peserta' => $netSale->akomodasi_peserta,
                'akomodasi_tim' => $netSale->akomodasi_tim,
                'keterangan_akomodasi_tim' => $netSale->keterangan_akomodasi_tim,
                'fresh_money' => $netSale->fresh_money,
                'entertaint' => $netSale->entertaint,
                'keterangan_entertaint' => $netSale->keterangan_entertaint,
                'cashback' => $netSale->cashback,
                'souvenir' => $netSale->souvenir,
                'sewa_laptop' => $netSale->sewa_laptop,
                'oleh_oleh' => $netSale->oleh_oleh,
                'biaya_lain_lain' => $netSale->biaya_lain_lain,
                'keterangan_biaya_lain' => $netSale->keterangan_biaya_lain,
                'biaya_exam' => $netSale->biaya_exam,
                'tgl_pa' => $netSale->tgl_pa,
                'tipe_pembayaran' => $netSale->tipe_pembayaran,
                'bukti' => $netSale->bukti ?? null,
                'deskripsi_tambahan' => $netSale->deskripsi_tambahan,
                'ppn' => $potonganPajak, // 🔹 Variabel PPN diteruskan ke frontend
                'total' => $total,
                'totalPa' => $totalPA,
                'approved' => $dataApproved
                    ->map(function ($item) {
                        return [
                            'tanggal' => $item->created_at->format('Y-m-d H:i'),
                            'status' => $item->status,
                            'level_status' => $item->level_status,
                            'keterangan' => $item->keterangan,
                        ];
                    })
                    ->toArray(),
            ];
            $grandtotal += $total;
            $arrayNetSales[] = $netSaleData;
        }

        $arrayTracking = [
            'tanggal' => $dataTracking->created_at->format('d M Y H:i'),
            'status' => $dataTracking->tracking,
        ];

        return response()->json([
            'dataRKM' => $arrayRKM,
            'dataNetSales' => $arrayNetSales,
            'grandTotal' => $grandtotal,
            'dataTracking' => $arrayTracking,
        ]);
    }

    public function edit($id)
    {
        $dataNetSales = perhitunganNetSales::findOrFail($id);
        $rkm = RKM::with('perusahaan', 'materi')->findOrFail($dataNetSales->id_rkm);
        $exam = eksam::where('id_rkm', $rkm->id)->first();
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

    public function updateNetSales(Request $request)
    {
        $id = $request->input('id_netsales');
        $dataNetSales = perhitunganNetSales::findOrFail($id);

        $oldData = $dataNetSales->getOriginal();

        $dataNetSales->transportasi = str_replace(['.', ','], '', $request->input('transportasi'));
        $dataNetSales->jenis_transportasi = $request->input('jenis_transportasi');
        $dataNetSales->akomodasi_peserta = str_replace(['.', ','], '', $request->input('akomodasi_peserta'));
        $dataNetSales->akomodasi_tim = str_replace(['.', ','], '', $request->input('akomodasi_tim'));
        $dataNetSales->keterangan_akomodasi_tim = $request->input('keterangan_akomodasi_tim');
        $dataNetSales->fresh_money = str_replace(['.', ','], '', $request->input('fresh_money'));
        $dataNetSales->entertaint = str_replace(['.', ','], '', $request->input('entertaint'));
        $dataNetSales->keterangan_entertaint = $request->input('keterangan_entertaint');
        $dataNetSales->souvenir = str_replace(['.', ','], '', $request->input('souvenir'));
        $dataNetSales->cashback = str_replace(['.', ','], '', $request->input('cashback'));
        $dataNetSales->sewa_laptop = str_replace(['.', ','], '', $request->input('sewa_laptop'));
        foreach (['oleh_oleh', 'biaya_lain_lain', 'biaya_exam'] as $f) {
            if ($request->has($f)) {
                $v = preg_replace('/[^\d]/', '', (string) $request->input($f));
                $dataNetSales->{$f} = $v === '' ? null : $v;
            }
        }
        if ($request->has('keterangan_biaya_lain')) {
            $dataNetSales->keterangan_biaya_lain = $request->input('keterangan_biaya_lain');
        }

        $dataNetSales->tgl_pa = $request->input('tgl_pa');
        $dataNetSales->tipe_pembayaran = $request->input('tipe_pembayaran');
        $dataNetSales->deskripsi_tambahan = $request->input('deskripsi_tambahan');

        $dataNetSales->save();

        $peluang = Peluang::where('id_rkm', $dataNetSales->id_rkm)->first();
        if ($peluang) {
            $totalPenawaran = (float)$peluang->harga * (int)$peluang->pax;
            $potonganPajak = $totalPenawaran * (11 / 100);

            $allPA = perhitunganNetSales::where('id_rkm', $dataNetSales->id_rkm)->get();
            $totalPAInputs = $this->totalBiaya($allPA);

            // Menyimpan hasil akhir ke kolom netsales
            $peluang->netsales = $totalPenawaran - $potonganPajak - $totalPAInputs;
            $peluang->save();
        }

        $changed = [];
        $newData = $dataNetSales->getAttributes(); //Ambil yg udah ke save

        $fieldsToWatch = [
            'transportasi',
            'jenis_transportasi',
            'akomodasi_peserta',
            'akomodasi_tim',
            'keterangan_akomodasi_tim',
            'fresh_money',
            'entertaint',
            'souvenir',
            'cashback',
            'sewa_laptop',
            'tgl_pa',
            'tipe_pembayaran',
            'deskripsi_tambahan',
            'oleh_oleh', 
            'biaya_lain_lain', 
            'keterangan_biaya_lain', 
            'biaya_exam',
        ];

        foreach ($fieldsToWatch as $field) {
            $oldValue = $oldData[$field] ?? null;
            $newValue = $newData[$field] ?? null;

            if ($oldValue != $newValue) {
                $changed[$field] = [
                    'before' => $oldValue,
                    'after' => $newValue,
                ];
            }
        }

        if (!empty($changed)) {
            $historyNet = new HistoryNetSales();
            $historyNet->id_user = Auth::id();
            $historyNet->id_rkm = $dataNetSales->id_rkm;
            $historyNet->data = $changed;
            $historyNet->save();
        }

        $users = User::whereIn('jabatan', ['GM', 'Adm Sales', 'Finance & Accounting'])->get();
        $rkm = RKM::with('materi')->where('id', $dataNetSales->id_rkm)->first();

        Carbon::setLocale('id');
        date_default_timezone_set('Asia/Jakarta');

        $data = [
            'karyawan' => Auth::user()->karyawan->nama_lengkap ?? Auth::user()->username,
            'id_rkm' => $rkm->id ?? null,
            'rkm' => $rkm->materi?->nama_materi ?? 'Tidak diketahui',
            'waktu' => ($rkm->tanggal_awal && $rkm->tanggal_akhir)
                ? Carbon::parse($rkm->tanggal_awal)->translatedFormat('d F Y') . ' - ' . Carbon::parse($rkm->tanggal_akhir)->translatedFormat('d F Y')
                : 'Tanggal belum ditentukan',
            'milik' => $rkm->sales_key ?? 'Tidak diketahui',
            'waktu_perubahan' => Carbon::now()->format('Y-m-d H:i:s'),
            'perubahan' => $changed,
        ];

        $path = "/crm/edit/{$dataNetSales->id_rkm}/pa";
        $receiverId = $users->pluck('id')->toArray();
        Notification::send($users, new UpdateLaporanPenjualan($data, $path, $receiverId));

        return redirect()->back()->with('success', 'Data Net Sales berhasil diperbarui.');
    }

    public function DownloadPDF($year, $month)
    {
        if (!is_numeric($year) || !is_numeric($month)) {
            abort(400, 'Parameter tahun dan bulan harus berupa angka');
        }

        Carbon::setLocale('id');

        try {
            $startDate = CarbonImmutable::create($year, $month, 1);
        } catch (\Exception $e) {
            abort(400, 'Format bulan atau tahun tidak valid');
        }

        $endDate = $startDate->endOfMonth();

        $monthRanges = [];
        $date = $startDate;

        while ($date->month <= $endDate->month && $date->year <= $endDate->year) {
            $startOfMonth = $date->startOfMonth();
            $endOfMonth   = $date->endOfMonth();
            $monthName    = $startOfMonth->translatedFormat('F');

            $weekRanges  = [];
            $startOfWeek = $startOfMonth->startOfWeek();
            $weekNumber  = 1;

            while ($startOfWeek->lte($endOfMonth)) {
                $endOfWeek = $startOfWeek->copy()->endOfWeek();

                if ($startOfWeek->month != $date->month) {
                    $startOfWeek = $startOfWeek->addWeek();
                    $weekNumber++;
                    continue;
                }

                $start = $startOfWeek->format('Y-m-d');
                $end   = $endOfWeek->format('Y-m-d');

                $rkm = RKM::with([
                    'materi',
                    'analisisrkm',
                    'perhitunganNetSales.approvedNetSales',
                    'analisisrkm.analisisrkmmingguan',
                    'perusahaan'
                ])
                    ->where('status', '0')
                    ->whereYear('tanggal_awal', $year)
                    ->whereBetween('tanggal_awal', [$start, $end])
                    ->get();

                $formattedItems = $rkm->map(function ($item) {
                    $status = $item->perhitunganNetSales->isNotEmpty() ? 'Hijau' : 'Merah';

                    $tanggalAwal  = Carbon::parse($item->tanggal_awal);
                    $tanggalAkhir = Carbon::parse($item->tanggal_akhir);

                    $netSalesList = $item->perhitunganNetSales;

                    $transportasi       = $netSalesList->sum('transportasi');
                    $akomodasi_peserta  = $netSalesList->sum('akomodasi_peserta');
                    $akomodasi_tim      = $netSalesList->sum('akomodasi_tim');
                    $fresh_money        = $netSalesList->sum('fresh_money');
                    $entertaint         = $netSalesList->sum('entertaint');
                    $souvenir           = $netSalesList->sum('souvenir');
                    $cashback           = $netSalesList->sum('cashback');
                    $sewa_laptop        = $netSalesList->sum('sewa_laptop');
                    $oleh_oleh = $netSalesList->sum('oleh_oleh');
                    $biaya_lain_lain = $netSalesList->sum('biaya_lain_lain');
                    $biaya_exam = $netSalesList->sum('biaya_exam');
                    $deskripsi = $netSalesList->first()->deskripsi_tambahan ?? '-';

                    $totalPenawaran = $item->pax * $item->harga_jual;

                    $totalNetSales = $totalPenawaran;
                    // - $transportasi
                    // - $akomodasi_peserta
                    // - $akomodasi_tim
                    // - $fresh_money
                    // - $entertaint
                    // - $souvenir
                    // - $cashback
                    // - $sewa_laptop;

                    $latestApproved = $netSalesList
                        ->flatMap->approvedNetSales
                        ->sortByDesc('created_at')
                        ->first();

                    return [
                        'id' => $item->id,
                        'nama_materi' => $item->materi->nama_materi,
                        'pax' => $item->pax,
                        'sales_key' => $item->sales_key,
                        'harga_jual' => $item->harga_jual,
                        'total_harga_jual' => $item->pax * $item->harga_jual,
                        'tanggal_awal' => $tanggalAwal->translatedFormat('d F Y'),
                        'tanggal_akhir' => $tanggalAkhir->translatedFormat('d F Y'),
                        'durasi' => $tanggalAwal->diffInDays($tanggalAkhir) + 1,
                        'status' => $status,
                        'transportasi' => $transportasi,
                        'akomodasi_peserta' => $akomodasi_peserta,
                        'akomodasi_tim' => $akomodasi_tim,
                        'fresh_money' => $fresh_money,
                        'entertaint' => $entertaint,
                        'souvenir' => $souvenir,
                        'cashback' => $cashback,
                        'sewa_laptop' => $sewa_laptop,
                        'oleh_oleh' => $oleh_oleh,
                        'biaya_lain_lain' => $biaya_lain_lain,
                        'biaya_exam' => $biaya_exam,
                        'deskripsi' => $deskripsi ?? '-',
                        'total' => $totalNetSales,
                        'keterangan_biaya_lain' => $netSalesList->pluck('keterangan_biaya_lain')->filter()->implode('; '),
                        'level_status' => optional($latestApproved)->level_status ?? 'Belum Disetujui',
                        'keterangan' => optional($latestApproved)->keterangan ?? '-',
                        'perusahaan' => $item->perusahaan,
                    ];
                });

                $rkmfull = 'no data';
                if ($formattedItems->isNotEmpty()) {
                    $rkmfull = $formattedItems->every(fn($item) => $item['status'] === 'Hijau')
                        ? 'ok'
                        : 'pending';
                }

                $weekRanges[] = [
                    'rkmfull' => $rkmfull,
                    'minggu' => $weekNumber,
                    'tanggal_awal_minggu' => $startOfWeek->translatedFormat('d F Y'),
                    'tanggal_akhir_minggu' => $endOfWeek->translatedFormat('d F Y'),
                    'data' => $formattedItems,
                ];

                $startOfWeek = $startOfWeek->addWeek();
                $weekNumber++;
            }

            $monthRanges[] = [
                'tahun' => $year,
                'bulan' => $monthName,
                'weeksData' => $weekRanges,
            ];

            $date = $date->addMonth();
        }

        $pdf = Pdf::loadView('netSales.pdf', compact(
            'monthRanges',
            'year',
            'month'
        ));

        $pdf->setPaper('a4', 'portrait');


        return $pdf->download("RKM_{$month}_{$year}.pdf");
    }

    public function pdfSendiri($id)
    {

        $rkm = RKM::with(
            'materi',
            'analisisrkm',
            'perhitunganNetSales.approvedNetSales',
            'analisisrkm.analisisrkmmingguan',
            'perusahaan',
            'sales'
        )->where('id', $id)->first();

        $spv = karyawan::where('jabatan', 'SPV Sales')->where('status_aktif', '1')->select('ttd')->first();
        $gm = karyawan::where('jabatan', 'GM')->where('status_aktif', '1')->select('ttd')->first();
        $dirut = karyawan::where('jabatan', 'Direktur Utama')->where('status_aktif', '1')->first();

        $pdf = Pdf::loadView('netSales.pdfSendiri', compact(
            'rkm', 'gm', 'spv', 'dirut'
        ));

        $pdf->setPaper('a4', 'portrait');


        return $pdf->stream("RKM_{$id}.pdf");
    }

    public function detailNetSales($id)
    {
        $data = perhitunganNetSales::with([
            'rkm.materi',
            'rkm.perusahaan',
            'karyawan'
        ])->findOrFail($id);

        $total = $this->totalBiaya(collect([$data]));

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $total
        ]);
    }
}
