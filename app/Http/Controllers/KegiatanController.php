<?php

namespace App\Http\Controllers;

use App\Models\AbsensiKaryawan;
use App\Models\DetailPickupDriver;
use App\Models\karyawan;
use App\Models\Kegiatan;
use App\Models\PengajuanBarang;
use App\Models\pickupDriver;
use App\Models\RincianKegiatan;
use App\Models\tracking_pengajuan_barang;
use App\Models\TrackingPickupDriver;
use App\Models\User;
use App\Notifications\KegiatanApproved;
use App\Notifications\KegiatanMenunggu;
use App\Notifications\KegiatanNotification;
use App\Notifications\KegiatanPencairan;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\KoordinasiDriverNotifcation;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Mockery\Expectation;
use App\Models\PembelianHr;
use Illuminate\Support\Facades\DB;

class KegiatanController extends Controller
{
    protected $PengajuanBarangController;

    public function __construct(PengajuanBarangController $PengajuanBarangController)
    {
        $this->middleware('auth');
        $this->PengajuanBarangController = $PengajuanBarangController;
        $this->middleware('permission:View RAB Kegiatan', ['only' => ['index', 'show']]);
        $this->middleware('permission:Store RAB Kegiatan', ['only' => ['storeKegiatan', 'storeDetail', 'storePeserta']]);
        $this->middleware('permission:Update RAB Kegiatan', ['only' => ['updateKegiatan', 'updateDetail']]);
        $this->middleware('permission:Delete RAB Kegiatan', ['only' => ['deleteKegiatan', 'deleteRincian']]);
    }

    public function index()
    {
        $kegiatan = Kegiatan::all();
        $karyawans = Karyawan::all();
        $drivers = karyawan::where('jabatan', 'Driver')
            ->where(function ($query) {
                $query->whereDoesntHave('pickupDriver')->orWhereHas('pickupDriver', function ($q) {
                    $q->whereIn('status_driver', ['Selesai, Driver Ready']);
                });
            })
            ->get();
        
        $dibatalkan = PembelianHr::with('tracking', 'tracking.karyawan', 'details')->where('status_pembelian', 'Dibatalkan')->paginate(10);
        $user = auth()->user();

        $rencanasRaw = PembelianHr::with([
            'details',
            'tracking.karyawan'
        ])
        ->where('status_pembelian', 'Rencana')
        ->when(!in_array($user->jabatan, ['HRD', 'GM']), function ($query) use ($user) {
            $query->where('id_karyawan', $user->id);
        })
        ->get()
        ->map(function ($item) {
            $item->source = 'pembelian_hr';
            $item->status = $item->status_pembelian;
            return $item;
        });

        $pembelianRaw = PembelianHr::with([
            'details',
            'tracking.karyawan'
        ])
        ->where('status_pembelian', 'Terlaksana')
        ->when(!in_array($user->jabatan, ['HRD', 'GM']), function ($query) use ($user) {
            $query->where('id_karyawan', $user->id);
        })
        ->get()
        ->map(function ($item) {
            $item->source = 'pembelian_hr';
            $item->status = $item->status_pembelian;
            return $item;
        });

        $kegiatanRencanaRaw = Kegiatan::with([
            'pengajuan_barang.detail',
            'rincian'
        ])
        ->where('tipe', 'pembelian')
        ->where('status','!=','selesai')
        ->get()
        ->map(function($item){

            $details = collect();

            foreach ($item->rincian as $rincian) {

                $details->push((object)[
                    'jenis' => 'rincian',
                    'nama_barang' => $rincian->hal,
                    'qty' => $rincian->qty,
                    'harga' => $rincian->harga_satuan,
                    'url' => null,
                    'keterangan' => $rincian->rincian,
                    'tanggal' => $rincian->tanggal
                ]);
            }

            foreach ($item->pengajuan_barang as $pengajuan) {

                foreach ($pengajuan->detail as $barang) {

                    $details->push((object)[
                        'jenis' => 'barang',
                        'nama_barang' => $barang->nama_barang,
                        'qty' => $barang->qty,
                        'harga' => $barang->harga,
                        'url' => null,
                        'keterangan' => $barang->keterangan,
                        'tanggal' => $pengajuan->tanggal_pencairan,
                    ]);
                }
            }

            return (object)[
                'id' => $item->id,
                'source' => 'kegiatan',
                'periode' => Carbon::parse($item->waktu_kegiatan)->translatedFormat('F Y'),
                'details' => $details,
                'tracking' => collect(),
                'kategori' => 'Kegiatan',
                'status_pembelian' => $item->status,
                'created_at' => $item->created_at,
                'menunggu' => $item->menunggu,
                'approved' => $item->approved,
                'pencairan' => $item->pencairan,
                'selesai' => $item->selesai,
                'invoice' => $item->pengajuan_barang->pluck('invoice')->filter()->implode(', '),
                'no_kk' => $item->pengajuan_barang->pluck('no_kk')->filter()->implode(', '),
            ];
        });

        $kegiatanPembelianRaw = Kegiatan::with([
            'pengajuan_barang.detail',
            'rincian'
        ])
        ->where('tipe', 'pembelian')
        ->where('status','selesai')
        ->get()
        ->map(function($item){

            $details = collect();

            foreach ($item->rincian as $rincian) {

                $details->push((object)[
                    'jenis' => 'rincian',
                    'nama_barang' => $rincian->hal,
                    'qty' => $rincian->qty,
                    'harga' => $rincian->harga_satuan,
                    'url' => null,
                    'keterangan' => $rincian->rincian,
                    'tanggal' => $rincian->tanggal
                ]);
            }

            foreach ($item->pengajuan_barang as $pengajuan) {

                foreach ($pengajuan->detail as $barang) {

                    $details->push((object)[
                        'jenis' => 'barang',
                        'nama_barang' => $barang->nama_barang,
                        'qty' => $barang->qty,
                        'harga' => $barang->harga,
                        'url' => null,
                        'keterangan' => $barang->keterangan,
                        'tanggal' => $pengajuan->tanggal_pencairan,
                    ]);
                }
            }

            return (object)[
                'id' => $item->id,
                'source' => 'kegiatan',
                'periode' => Carbon::parse($item->waktu_kegiatan)->translatedFormat('F Y'),
                'details' => $details,
                'tracking' => collect(),
                'kategori' => 'Kegiatan',
                'status_pembelian' => $item->status,
                'created_at' => $item->created_at,
                'menunggu' => $item->menunggu,
                'approved' => $item->approved,
                'pencairan' => $item->pencairan,
                'selesai' => $item->selesai,
                'invoice' => $item->pengajuan_barang->pluck('invoice')->filter()->implode(', '),
                'no_kk' => $item->pengajuan_barang->pluck('no_kk')->filter()->implode(', '),
            ];
        });

        if (in_array($user->jabatan, ['HRD', 'GM'])) {
            $rencanas = $rencanasRaw
                ->concat($kegiatanRencanaRaw)
                ->sortBy('periode')
                ->values();
    
            $pembelian = $pembelianRaw
                ->concat($kegiatanPembelianRaw)
                ->sortBy('periode')
                ->values();
        } else {
            $rencanas = $rencanasRaw
                ->sortBy('periode')
                ->values();
    
            $pembelian = $pembelianRaw
                ->sortBy('periode')
                ->values();
        }

        $extends = 'layouts_office.app';
        $section = 'office_contents';

        return view('office.rab.index', compact('kegiatan', 'drivers', 'pembelian', 'rencanas', 'dibatalkan', 'extends', 'section', 'karyawans'));
    }

    public function show($id)
    {
        $kegiatan = Kegiatan::with('rincian')->findOrFail($id);
        $totalRincian = $kegiatan->rincian->sum('total');

        $absensi = AbsensiKaryawan::with('karyawan')->whereDate('tanggal', $kegiatan->waktu_kegiatan)->get();

        $karyawan = karyawan::where('status_aktif', '1')->get();

        $idPeserta = $kegiatan->id_peserta ?? [];

        $peserta = Karyawan::whereIn('id', $idPeserta)->get();

        return view('office.rab.show', compact('kegiatan', 'totalRincian', 'absensi', 'karyawan', 'peserta'));
    }

    public function getPengajuanBarang($id)
    {
        $dataPengajuanBarang = PengajuanBarang::with('karyawan', 'tracking', 'detail')->where('id_kegiatan', $id)->get();
        $dataRincianKegiatan = RincianKegiatan::where('id_kegiatan', $id)->get()
            ->map(function ($item) {
                return [
                    'notPengajuan' => 'true',
                    'id' => $item->id,
                    'created_at' => $item->tanggal,
                    'karyawan' => [
                        'nama_lengkap' => $item->karyawan->nama_lengkap,
                        'divisi' => $item->karyawan->divisi,
                    ],
                    'tipe' => $item->tipe,
                    'tracking' => [
                        'tracking' => $item->status,
                    ],
                    'detail' => [
                        [
                            'harga' => $item->harga_satuan,
                            'qty' => $item->qty
                        ]
                    ]
                ];
            });

        $dataGabungan = collect($dataPengajuanBarang->toArray())
            ->merge($dataRincianKegiatan)
            ->values();
        return response()->json([
            'success' => true,
            'message' => 'List data pengajuan barang untuk kegiatan/pembelian',
            'data' => $dataGabungan,
        ]);
    }

    public function getDetailKegiatan($id) {
        $data = RincianKegiatan::findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'data detail kegiatan',
            'data' => $data
        ]);
    }

    public function downloadPDF($id)
    {
        $kegiatan = Kegiatan::with('rincian')->findOrFail($id);
        $totalRincian = $kegiatan->rincian->sum('total');

        $karyawan = AbsensiKaryawan::with('karyawan')->whereDate('tanggal', $kegiatan->waktu_kegiatan)->get();

        $idPeserta = $kegiatan->id_peserta ?? [];

        $peserta = Karyawan::whereIn('id', $idPeserta)->get();

        $dataPengajuanBarang = PengajuanBarang::with('karyawan', 'tracking', 'detail')->where('id_kegiatan', $id)->get();

        if ($kegiatan->tipe === 'kegiatan') {
            $filename = 'pdf-kegiatan.pdf';
        } elseif ($kegiatan->tipe === 'pembelian') {
            $filename = 'pdf-pembelian.pdf';
        } else {
            $filename = 'pdf-kegiatan.pdf';
        }

        $pdf = Pdf::loadView('office.rab.pdf', compact('kegiatan', 'totalRincian', 'karyawan', 'dataPengajuanBarang', 'peserta'))->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

    public function updateRealisasi(Request $request)
    {
        $dataKegiatan = Kegiatan::findOrFail($request->id);

        $dataKegiatan->realisasi = $request->input('realisasi');
        $dataKegiatan->save();

        return back();
    }

    public function storePeserta(Request $request, $id)
    {
        $kegiatan = Kegiatan::findOrFail($id);
        $kegiatan->id_peserta = array_map('intval', $request->peserta);
        $kegiatan->save();

        return back()->with('success', 'Berhasil menambahkan peserta kegiatan');
    }

    public function storeKegiatan(Request $request)
    {
        $validated = $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'tipe' => 'required|in:kegiatan,pembelian,rekrutmen',
            'waktu_kegiatan' => 'nullable|date',
            'lama_kegiatan' => 'nullable|max:100',
            'pic' => 'nullable|string|max:255',
            'status' => 'nullable|in:Diajukan,Menunggu,Approved,Pencairan,Selesai',

            'id_driver' => 'nullable|exists:karyawan,id',
            'budget' => 'nullable|numeric',
            'lokasi' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $kegiatan = new Kegiatan();
            $kegiatan->nama_kegiatan = $validated['nama_kegiatan'];
            $kegiatan->tipe = $validated['tipe'];
            $kegiatan->waktu_kegiatan = $validated['waktu_kegiatan'] ?? null;
            $kegiatan->lama_kegiatan = $validated['lama_kegiatan'] ?? null;
            $kegiatan->pic = $validated['pic'] ?? null;
            $kegiatan->status = $validated['status'] ?? 'Diajukan';
            $kegiatan->save();

            if ($kegiatan->tipe === 'kegiatan' && $request->filled('id_driver')) {
                $pickupDriver = new pickupDriver();
                $pickupDriver->id_karyawan = $request->input('id_driver');
                $pickupDriver->id_pembuat = auth()->id();
                $pickupDriver->status_apply = 0;
                $pickupDriver->budget = $request->input('budget');
                $pickupDriver->save();

                if ($pickupDriver->id_karyawan) {
                    $waktuKegiatan = filled($validated['waktu_kegiatan'] ?? null)
                        ? Carbon::parse($validated['waktu_kegiatan'])
                        : now();

                    $waktuBerangkat = $waktuKegiatan->copy()->subHour();

                    $detailPickupDriver = new DetailPickupDriver();
                    $detailPickupDriver->pickup_driver_id = $pickupDriver->id;
                    $detailPickupDriver->tipe = 'Pengantaran';
                    $detailPickupDriver->lokasi = $request->input('lokasi') ?: '-';
                    $detailPickupDriver->tanggal_keberangkatan = $waktuBerangkat->format('Y-m-d');
                    $detailPickupDriver->waktu_keberangkatan = $waktuBerangkat->format('H:i:s');
                    $detailPickupDriver->detail = '-';
                    $detailPickupDriver->save();

                    $trackingPickupDriver = new TrackingPickupDriver();
                    $trackingPickupDriver->pickup_driver_id = $pickupDriver->id;
                    $trackingPickupDriver->status = (auth()->user()->username ?? 'User') . ' telah membuat koordinasi baru';
                    $trackingPickupDriver->diubah_oleh = auth()->id();
                    $trackingPickupDriver->save();

                    $creatorJabatan = optional(auth()->user()->karyawan)->jabatan;

                    $driver = karyawan::findOrFail($pickupDriver->id_karyawan);

                    $recipients = [];

                    if ($creatorJabatan == 'HRD') {
                        $CS = karyawan::where('jabatan', 'Customer Care')->first();

                        if ($CS) {
                            $recipients[] = $CS->kode_karyawan;
                        }

                        $recipients[] = $driver->kode_karyawan;
                    } elseif ($creatorJabatan == 'Customer Care') {
                        $HRD = karyawan::where('jabatan', 'HRD')->first();

                        if ($HRD) {
                            $recipients[] = $HRD->kode_karyawan;
                        }

                        $recipients[] = $driver->kode_karyawan;
                    }

                    $recipients = array_values(array_unique(array_filter($recipients)));

                    if (!empty($recipients)) {
                        $users = User::whereHas('karyawan', function ($query) use ($recipients) {
                            $query->whereIn('kode_karyawan', $recipients);
                        })->get();

                        $data = [
                            'id_karyawan' => $pickupDriver->id_karyawan,
                            'tipe' => $detailPickupDriver->tipe,
                            'tanggal_pembuatan' => now(),
                            'id_pengajuan' => $pickupDriver->id,
                        ];

                        $type = 'Koordinasi Driver';
                        $path = '/office/pickup-driver/index';

                        foreach ($users as $user) {
                            Notification::send(
                                $user,
                                new KoordinasiDriverNotifcation(
                                    $data,
                                    $path,
                                    $type,
                                    $user->id
                                )
                            );
                        }
                    }
                }
            }

            $penerima = User::where('jabatan', 'GM')->where('status_akun', '1')->first();

            if ($penerima) {
                $data = [
                    'nama_kegiatan' => $kegiatan->nama_kegiatan,
                    'tipe' => $kegiatan->tipe,
                    'waktu_kegiatan' => $kegiatan->waktu_kegiatan,
                    'lama_kegiatan' => $kegiatan->lama_kegiatan,
                    'pic' => $kegiatan->pic,
                ];

                $path = '/office/kegiatan/show/' . $kegiatan->id;
                $type = 'Kegiatan Terbuat';

                Notification::send($penerima, new KegiatanNotification($data, $path, $type));
            }

            DB::commit();

            return redirect()->back()->with('success', 'Kegiatan berhasil disimpan');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Kegiatan gagal disimpan. Silakan periksa kembali data.');
        }
    }

    public function updateKegiatan(Request $request, $id)
    {
        $kegiatan = Kegiatan::findOrFail($id);

        $validated = $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'waktu_kegiatan' => 'nullable|date',
            'lama_kegiatan' => 'nullable|max:100',
            'pic' => 'nullable|string|max:255',
            'status' => 'nullable|in:Diajukan,Menunggu,Approved,Pencairan,Selesai',
        ]);

        $kegiatan->nama_kegiatan = $validated['nama_kegiatan'];
        $kegiatan->waktu_kegiatan = $validated['waktu_kegiatan'];
        $kegiatan->lama_kegiatan = $validated['lama_kegiatan'];
        $kegiatan->pic = $validated['pic'] ?? null;
        $kegiatan->status = $validated['status'] ?? 'Diajukan';

        $kegiatan->save();

        return redirect()->back()->with('success', 'Kegiatan berhasil diupdate');
    }

    public function deleteKegiatan($id)
    {
        RincianKegiatan::where('id_kegiatan', $id)->delete();
        Kegiatan::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Kegiatan berhasil dihapus');
    }

    // Tidak Terpakai, jadinya pakai dari PengajuanBarangController

    // public function storeRincian(Request $request, $id)
    // {
    //     $validated = $request->validate([
    //         'hal'          => 'required|string|max:255',
    //         'rincian'      => 'required|string|max:255',
    //         'qty'          => 'required|integer',
    //         'harga_satuan' => 'nullable|numeric|min:0',
    //         'tipe'         => 'nullable|in:ATK,Elektronik,Makanan,Souvenir,Operasional,Reimbursement,Training & Sertifikasi',
    //     ]);

    //     $rincian = new RincianKegiatan();
    //     $rincian->id_kegiatan  = $id;
    //     $rincian->hal          = $validated['hal'];
    //     $rincian->rincian      = $validated['rincian'];
    //     $rincian->qty          = $validated['qty'];
    //     $rincian->harga_satuan = $validated['harga_satuan'];
    //     $rincian->total        = $validated['harga_satuan'] * $validated['qty'];
    //     $rincian->save();

    //     // Gunakan construct untuk menggunakan function controller lain
    //     $user = Auth::user();
    //     $idKaryawan = $user->karyawan ? $user->karyawan->id : $user->id;

    //     $payloadPengajuan = [
    //         'id_karyawan' => (string) $idKaryawan,
    //         'tipe'        => (string) $validated['tipe'],
    //         'barang'      => [
    //             'nama_barang'  => [$validated['rincian']],
    //             'qty'          => [(string) $validated['qty']],
    //             'harga_barang' => [(string) $validated['harga_satuan']],
    //             'keterangan'   => [$validated['hal']],
    //         ],
    //     ];

    //     $newRequest = $request->duplicate();

    //     $newRequest->merge($payloadPengajuan);

    //     $this->PengajuanBarangController->store($newRequest);

    //     return redirect()->back()->with('success', 'Rincian dan Pengajuan Barang berhasil disimpan.');
    // }

    // public function updateRincian(Request $request, $id)
    // {
    //     $rincian = RincianKegiatan::findOrFail($id);
    //     $validated = $request->validate([
    //         'hal'   => 'required|string|max:255',
    //         'rincian'  => 'required|string|max:255',
    //         'qty'   => 'required|integer',
    //         'harga_satuan'  => 'nullable|numeric|min:0',
    //     ]);
    //     $total = $request->harga_satuan * $request->qty;

    //     $rincian->id_kegiatan = $rincian->id_kegiatan;
    //     $rincian->hal  = $validated['hal'];
    //     $rincian->rincian = $validated['rincian'];
    //     $rincian->qty  = $validated['qty'];
    //     $rincian->harga_satuan            = $validated['harga_satuan'];
    //     $rincian->total    = $total;

    //     $rincian->save();

    //     return redirect()->back()->with('success', 'Rincian berhasil diupdate');
    // }


    public function gm(Request $request, $id)
    {
        $kegiatan = Kegiatan::findOrFail($id);
        $kegiatan->status = $request->status;
        if ($request->status === 'Approved') {
            $kegiatan->approved = Carbon::now();
            $kegiatan->menunggu = Carbon::now();
        } elseif ($request->status === 'Menunggu') {
            $kegiatan->menunggu = Carbon::now();
        }
        $kegiatan->save();

        if ($request->status === 'Approved') {
            $penerima = User::whereIn('jabatan', ['Finance & Accounting', 'HRD'])
                ->where('status_akun', '1')
                ->get();

            $data = [
                'status' => $request->status,
                'kegiatan' => $kegiatan->nama_kegiatan,
            ];

            $path = '/office/kegiatan/show/' . $kegiatan->id;

            if ($penerima->isNotEmpty()) {
                Notification::send($penerima, new KegiatanApproved($data, $path));
            }
        }

        if ($request->status === 'Menunggu') {
            $user = User::where('jabatan', 'HRD')->where('status_akun', '1')->get();

            $data = [
                'status' => $request->status,
                'kegiatan' => $kegiatan->nama_kegiatan,
            ];

            $path = '/office/kegiatan/show/' . $kegiatan->id;

            Notification::send($user, new KegiatanMenunggu($data, $path));
        }

        return redirect()->back()->with('success', 'Status berhasil diupdate');
    }

    public function finance(Request $request, $id)
    {
        $kegiatan = Kegiatan::findOrFail($id);
        $kegiatan->status = $request->status;
        $kegiatan->pencairan = Carbon::now();
        $kegiatan->save();

        $penerima = User::where('jabatan', 'HRD')->where('status_akun', '1')->get();

        $data = [
            'status' => $request->status,
            'kegiatan' => $kegiatan->nama_kegiatan,
        ];

        $path = '/office/kegiatan/show/' . $kegiatan->id;

        Notification::send($penerima, new KegiatanPencairan($data, $path));
        return redirect()->back()->with('success', 'Status berhasil diupdate');
    }

    public function selesai(Request $request, $id)
    {
        $kegiatan = Kegiatan::findOrFail($id);
        $kegiatan->status = $request->status;
        $kegiatan->selesai = Carbon::now();
        $kegiatan->save();
        return redirect()->back()->with('success', 'Status berhasil diupdate');
    }

    public function getAvailablePengajuanBarang($id)
    {
        $kegiatan = Kegiatan::findOrFail($id);
        $userId = auth()->user()->id;

        $startDate = Carbon::parse($kegiatan->waktu_kegiatan)->subMonth();
        $endDate = Carbon::parse($kegiatan->kegiatan)->addMonth();

        $pengajuanBarang = PengajuanBarang::with(['karyawan', 'detail'])
            ->whereHas('karyawan', function ($q) use ($userId) {
                $q->where('id', $userId); 
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('id_kegiatan')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pengajuanBarang,
            'range' => [
                'start' => $startDate->format('d M Y'),
                'end' => $endDate->format('d M Y'),
            ],
        ]);
    }

    public function linkPengajuanBarang(Request $request, $id)
    {
        $updated = PengajuanBarang::whereIn('id', $request->pengajuan_ids)
            ->whereNull('id_kegiatan') 
            ->update(['id_kegiatan' => $id]);

        return redirect()
            ->back()
            ->with('success', "Berhasil menghubungkan {$updated} pengajuan barang ke kegiatan.");
    }

    public function storeDetail(Request $request) {
        $validation = $request->validate([
            'id_kegiatan' => 'required|exists:kegiatans,id',
            'hal' => 'required',
            'rincian' => 'nullable',
            'qty' => 'required',
            'harga_satuan' => 'required',
            'total' => 'required',
            'status' => 'required',
            'tipe' => 'required',
            'id_karyawan' => 'required',
            'tanggal' => 'required',
        ]);

        try {
            RincianKegiatan::create($validation);

            return back()->with('success', 'Detail barang berhasil dibuat');
        } catch (Expectation $e) {
            return back()->with('error', 'Terjadi kesalahan : '.$e);
        }
    }

    public function updateDetail(Request $request) {
        $request->validate([
            'id_detail' => 'required|exists:rincian_kegiatans,id',
            'hal' => 'required',
            'rincian' => 'nullable',
            'qty' => 'required',
            'harga_satuan' => 'required',
            'total' => 'required',
            'status' => 'required',
            'tipe' => 'required',
            'tanggal' => 'required',
        ]);

        try {
            $detail = RincianKegiatan::findOrFail($request->id_detail);

            $detail->update([
                'hal' => $request->hal ?? $detail->hal,
                'rincian' => $request->rincian ?? $detail->rincian,
                'qty' => $request->qty ?? $detail->qty,
                'harga_satuan' => $request->harga_satuan ?? $detail->harga_satuan,
                'total' => $request->total ?? $detail->total,
                'status' => $request->status ?? $detail->status,
                'tipe' => $request->tipe ?? $detail->tipe,
                'tanggal' => $request->tanggal ?? $detail->tanggal,
            ]);

            return back()->with('success', 'Detail barang berhasil diupdate');
        } catch (Expectation $e) {
            return back()->with('error', 'Terjadi kesalahan : '.$e);
        }
    }

    public function deleteRincian($id)
    {
        RincianKegiatan::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Rincian berhasil didelete');
    }
}
