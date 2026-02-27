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

class KegiatanController extends Controller
{
    protected $PengajuanBarangController;

    public function __construct(PengajuanBarangController $PengajuanBarangController)
    {
        $this->middleware('auth');
        $this->PengajuanBarangController = $PengajuanBarangController;
    }

    public function index()
    {
        $kegiatan = Kegiatan::all();
        $drivers = karyawan::where('jabatan', 'Driver')
            ->where(function ($query) {
                $query->whereDoesntHave('pickupDriver')->orWhereHas('pickupDriver', function ($q) {
                    $q->whereIn('status_driver', ['Selesai, Driver Ready']);
                });
            })
            ->get();

        return view('office.rab.index', compact('kegiatan', 'drivers'));
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
        return response()->json([
            'success' => true,
            'message' => 'List data pengajuan barang untuk kegiatan/pembelian',
            'data' => $dataPengajuanBarang,
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
            'tipe' => 'required|in:kegiatan,pembelian',
            'waktu_kegiatan' => 'nullable|date',
            'lama_kegiatan' => 'nullable|max:100',
            'pic' => 'nullable|string|max:255',
            'status' => 'nullable|in:Diajukan,Menunggu,Approved,Pencairan,Selesai',
        ]);

        $kegiatan = new Kegiatan();
        $kegiatan->nama_kegiatan = $validated['nama_kegiatan'];
        $kegiatan->tipe = $validated['tipe'];
        $kegiatan->waktu_kegiatan = $validated['waktu_kegiatan'];
        $kegiatan->lama_kegiatan = $validated['lama_kegiatan'];
        $kegiatan->pic = $validated['pic'] ?? null;
        $kegiatan->status = $validated['status'] ?? 'Diajukan';

        $kegiatan->save();

        if ($kegiatan->tipe === "kegiatan") {
            $pickupDriver = new pickupDriver();
            $pickupDriver->id_karyawan = $request->id_driver;
            $pickupDriver->id_pembuat = Auth()->user()->id;
            $pickupDriver->status_apply = 0;
            $pickupDriver->budget = $request->budget;
            $pickupDriver->save();

            if ($pickupDriver) {
                $detailPickupDriver = new DetailPickupDriver();
                $detailPickupDriver->pickup_driver_id = $pickupDriver->id;
                $detailPickupDriver->tipe = "Pengantaran";
                $detailPickupDriver->lokasi = $request->lokasi;

                $waktuKegiatan = Carbon::parse($validated['waktu_kegiatan']);
                $waktuBerangkat = $waktuKegiatan->copy()->subHour();

                $detailPickupDriver->tanggal_keberangkatan = $waktuBerangkat->format('Y-m-d');
                $detailPickupDriver->waktu_keberangkatan = $waktuBerangkat->format('H:i:s');
                $detailPickupDriver->detail = "-";
                $detailPickupDriver->save();
            }

            if ($pickupDriver) {
                $trackingPickupDriver = new TrackingPickupDriver();
                $trackingPickupDriver->pickup_driver_id = $pickupDriver->id;
                $trackingPickupDriver->status = auth()->user()->username . ' telah membuat koordinasi baru';
                $trackingPickupDriver->diubah_oleh = auth()->user()->id;
                $trackingPickupDriver->save();

                $creator = auth()->user();
                $creatorKaryawan = $creator->karyawan;
                $creatorJabatan = $creatorKaryawan->jabatan;

                $driver = karyawan::findOrFail($request->id_driver);

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

                $users = User::whereHas('karyawan', function ($query) use ($recipients) {
                    $query->whereIn('kode_karyawan', $recipients);
                })->get();

                $data = [
                    'id_karyawan' => $request->id_driver,
                    'tipe' => $detailPickupDriver->tipe,
                    'tanggal_pembuatan' => now(),
                    'id_pengajuan' => $pickupDriver->id,
                ];
                $type = 'Koordinasi Driver';
                $path = '/office/pickup-driver/index';

                foreach ($users as $user) {
                    $receiverId = $user->id;
                    NotificationFacade::send($user, new KoordinasiDriverNotifcation($data, $path, $type, $receiverId));
                }
            }
        }

        $penerima = User::where('jabatan', 'GM')->where('status_akun', '1')->first();
        $data = [
            'nama_kegiatan' => $validated['nama_kegiatan'],
            'tipe' => $validated['tipe'],
            'waktu_kegiatan' => $validated['waktu_kegiatan'],
            'lama_kegiatan' => $validated['lama_kegiatan'],
            'pic' => $validated['pic'],
        ];

        $path = '/office/kegiatan/show/' . $kegiatan->id;
        $type = 'Kegiatan Terbuat';

        Notification::send($penerima, new KegiatanNotification($data, $path, $type));

        return redirect()->back()->with('success', 'Kegiatan berhasil disimpan');
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

    // public function deleteRincian($id)
    // {
    //     RincianKegiatan::findOrFail($id)->delete();

    //     return redirect()->back()->with('success', 'Rincian berhasil didelete');
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
}
