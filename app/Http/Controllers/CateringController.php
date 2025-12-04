<?php

namespace App\Http\Controllers;

use App\Models\Catering;
use App\Models\DetailCatering;
use App\Models\karyawan;
use App\Models\TrackingCatering;
use App\Models\User;
use App\Models\vendor;
use App\Models\vendorCoffeeBreak;
use App\Models\vendorMakansiang;
use App\Notifications\cateringNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class CateringController extends Controller
{
    public function index()
    {
        return view('catering.index');
    }

    public function get()
    {
        $dataCatering = Catering::with(['karyawan', 'DetailCatering', 'TrackingCatering' =>  function ($q) {
            $q->latest();
        }])->get();

        $data = $dataCatering->map(function ($item) {
            $latestTracking = $item->TrackingCatering->first();
            Carbon::setLocale('id');

            return [
                'id' => $item->id,
                'tanggal_pengajuan' => $item->created_at->translatedFormat('l, d F Y'),
                'nama_karyawan' => $item->karyawan->nama_lengkap ?? '-',
                'divisi' => $item->karyawan->divisi ?? '-',
                'tipe' => $item->tipe,
                'tracking' => $latestTracking->tracking ?? '-',
                'detail' => $item->DetailCatering->map(function ($detail) {
                    return [
                        'nama_makanan' => $detail->nama_makanan,
                        'jumlah' => $detail->jumlah,
                        'harga' => $detail->harga,
                        'keterangan' => $detail->keterangan,
                    ];
                }),
            ];
        });

        return response()->json($data);
    }

    public function create()
    {
        $id_user = auth()->user()->id;
        $karyawan = karyawan::where('id', $id_user)->first();

        $vendorCB = vendorCoffeeBreak::get();
        $vendorMS = vendorMakansiang::get();

        return view('catering.create', compact('karyawan', 'vendorCB', 'vendorMS'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_karyawan' => 'required|exists:karyawans,id',
            'tipe'        => 'required|string|max:255',
            'barang.vendor.*'       => 'required|integer|exists:vendors,id',
            'barang.tipe_detail.*'  => 'required|in:Coffee Break,Makan Siang',
            'barang.nama_makanan.*' => 'required|string|max:255',
            'barang.qty.*'          => 'required|integer|min:1',
            'barang.harga.*'        => 'required|integer|min:0',
            'barang.keterangan.*'   => 'nullable|string|max:500',
        ]);

        $catering = Catering::create([
            'id_karyawan' => $request->id_karyawan,
            'tipe'        => $request->tipe,
        ]);

        $detailData = [];

        foreach ($request->barang['nama_makanan'] as $index => $nama) {
            $detailData[] = [
                'id_catering'  => $catering->id,
                'id_vendor'    => $request->barang['vendor'][$index],
                'tipe_detail'  => $request->barang['tipe_detail'][$index],
                'nama_makanan' => $nama,
                'jumlah'       => $request->barang['qty'][$index],
                'harga'        => $request->barang['harga'][$index],
                'keterangan'   => $request->barang['keterangan'][$index] ?? null,
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }

        if (!empty($detailData)) {
            DetailCatering::insert($detailData);
        }

        TrackingCatering::create([
            'id_catering' => $catering->id,
            'tracking'    => 'Telah Diajukan',
            'tanggal'     => now(),
        ]);

        $karyawan = Karyawan::findOrFail($request->id_karyawan);
        $divisi   = $karyawan->divisi;
        $jabatan  = $karyawan->jabatan;

        $finance = Karyawan::where('jabatan', 'Finance & Accounting')->first();
        $penerimaKode = [];

        if (in_array($jabatan, ['SPV Sales', 'Finance & Accounting']) && $finance) {
            $penerimaKode[] = $finance->kode_karyawan;
        } elseif ($divisi === 'Office' && $finance) {
            $penerimaKode[] = $finance->kode_karyawan;
        }

        if (!empty($penerimaKode)) {
            $users = User::whereHas('karyawan', fn($q) => $q->whereIn('kode_karyawan', $penerimaKode))->get();

            $notifData = [
                'id_karyawan'      => $request->id_karyawan,
                'tipe'             => $request->tipe,
                'tanggal_pengajuan' => now()->format('d-m-Y H:i'),
            ];

            foreach ($users as $user) {
                $receiverId = $user->id;
                NotificationFacade::send($user, new cateringNotification(
                    $notifData,
                    '/catering/index',
                    'Pengajuan catering',
                    $receiverId
                ));
            }
        }

        return redirect()->route('catering.index')->with('success', 'Pengajuan berhasil dikirim!');
    }

    public function show($id)
    {
        $dataCatering = Catering::with(['karyawan', 'DetailCatering', 'TrackingCatering'])->where('id', $id)->first();

        if (!$dataCatering) {
            abort(404);
        }

        $dataTrackingTerbaru = $dataCatering->TrackingCatering->sortByDesc('tanggal')->first();

        $data = [
            'id' => $dataCatering->id,
            'tanggal_pengajuan' => Carbon::parse($dataCatering->created_at)->locale('id')->translatedFormat('l, d F Y'),
            'nama_karyawan' => $dataCatering->karyawan->nama_lengkap ?? '-',
            'divisi' => $dataCatering->karyawan->divisi ?? '-',
            'jabatan' => $dataCatering->karyawan->jabatan,
            'tipe' => $dataCatering->tipe,
            'status' => $dataTrackingTerbaru->tracking ?? '-',
            'invoice' => $dataCatering->invoice,
            'detail' => $dataCatering->DetailCatering->map(function ($detail) {
                $vendor = null;

                if ($detail->tipe_detail === 'Coffee Break') {
                    $vendor = vendorCoffeeBreak::where('id', $detail->id_vendor)->where('is_active', '1')->first();
                } else if ($detail->tipe_detail === 'Makan Siang') {
                    $vendor = vendorMakansiang::where('id', $detail->id_vendor)->where('is_active', '1')->first();
                }

                return [
                    'id' => $detail->id,
                    'nama_makanan' => $detail->nama_makanan,
                    'jumlah' => $detail->jumlah,
                    'harga' => $detail->harga,
                    'keterangan' => $detail->keterangan,
                    'id_vendor' => $detail->id_vendor,
                    'vendor' => $vendor ? $vendor->nama : 'Vendor Tidak Ditemukan',
                    'tipe_detail' => $detail->tipe_detail, // Tambahkan ini
                ];
            }),
            'tracking' => $dataCatering->TrackingCatering->map(function ($tracking) {
                return [
                    'tracking' => $tracking->tracking,
                    'tanggal' => Carbon::parse($tracking->tanggal)->locale('id')->translatedFormat('l, d F Y'),
                    'keterangan' => $tracking->keterangan ?? '-',
                ];
            }),
        ];

        $vendorCB = vendorCoffeeBreak::get();
        $vendorMS = vendorMakansiang::get();

        return view('catering.show', compact('data', 'vendorCB', 'vendorMS'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_detail_catering' => 'array',
            'id_detail_catering.*' => 'nullable|exists:detail_caterings,id',
            'nama_makanan' => 'array',
            'nama_makanan.*' => 'required|string|max:255',
            'qty' => 'array',
            'qty.*' => 'required|integer|min:1',
            'harga' => 'array',
            'harga.*' => 'required|integer|min:0',
            'vendor' => 'array',
            'vendor.*' => 'required|integer|exists:vendors,id',
            'tipe_detail' => 'array',
            'tipe_detail.*' => 'required|in:Coffee Break,Makan Siang',
            'keterangan' => 'array',
            'keterangan.*' => 'nullable|string',
            'deleted_ids' => 'nullable|string',
        ]);

        $catering = Catering::findOrFail($id);

        if ($request->filled('deleted_ids')) {
            $deletedIds = array_filter(explode(',', $request->deleted_ids));
            if (!empty($deletedIds)) {
                DetailCatering::whereIn('id', $deletedIds)->delete();
            }
        }

        foreach ($request->id_detail_catering as $index => $detailId) {
            $data = [
                'nama_makanan' => $request->nama_makanan[$index],
                'jumlah'       => $request->qty[$index],
                'harga'        => $request->harga[$index],
                'id_vendor'    => $request->vendor[$index],
                'tipe_detail'  => $request->tipe_detail[$index],
                'keterangan'   => $request->keterangan[$index] ?? null,
            ];

            if (is_null($detailId) || $detailId === '') {
                $data['id_catering'] = $id;
                DetailCatering::create($data);
            } else {
                DetailCatering::where('id', $detailId)->update($data);
            }
        }

        TrackingCatering::create([
            'id_catering' => $id,
            'tracking'    => 'Terjadi perubahan data Barang',
            'tanggal'     => now(),
        ]);

        return redirect()->route('catering.show', $id)->with('success', 'Data berhasil diperbarui.');
    }

    public function PDF(Request $request)
    {
        $id = $request->input('id');

        $cateringData = Catering::with(['karyawan', 'DetailCatering', 'TrackingCatering'])
            ->where('id', $id)
            ->first();

        if (!$cateringData) {
            abort(404, 'Data tidak ditemukan');
        }

        $data = [
            'tanggal_pengajuan' => Carbon::parse($cateringData->created_at)->locale('id')->translatedFormat('l, d F Y'),
            'nama_pengaju'      => $cateringData->karyawan->nama_lengkap,
            'ttd'               => $cateringData->karyawan->ttd,
            'jabatan'           => $cateringData->karyawan->jabatan,
            'detail'            => $cateringData->DetailCatering->map(function ($detail) {
                $vendor = null;

                if ($detail->tipe_detail === 'Coffee Break') {
                    $vendor = vendorCoffeeBreak::where('id', $detail->id_vendor)->where('is_active', '1')->first();
                } else if ($detail->tipe_detail === 'Makan Siang') {
                    $vendor = vendorMakansiang::where('id', $detail->id_vendor)->where('is_active', '1')->first();
                }

                return [
                    'nama_makanan'  => $detail->nama_makanan,
                    'jumlah'        => $detail->jumlah,
                    'harga'         => 'Rp ' . number_format($detail->harga, 0, ',', '.'),
                    'vendor'        => $vendor ? $vendor->nama : 'Vendor Tidak Ditemukan',
                    'keterangan'    => $detail->keterangan,
                    'tipe_detail'   => $detail->tipe_detail,
                ];
            }),
            'total_harga' => 'Rp ' . number_format(
                $cateringData->DetailCatering->sum(function ($detail) {
                    return $detail->jumlah * $detail->harga;
                }),
                0,
                ',',
                '.'
            ),
        ];

        $trackingTerbaru = $cateringData->TrackingCatering->sortByDesc('created_at')->first();

        $finance = karyawan::where('id', $trackingTerbaru->id_karyawan)
            ->latest()
            ->first();

        $gm = karyawan::where('jabatan', 'GM')->latest()->first();

        return view('exports.catering', compact('data', 'finance', 'gm'));
    }

    public function approved(Request $request)
    {
        $request->validate([
            'status_input' => 'required|in:0,1',
            'id_catering' => 'required|exists:caterings,id',
            'keterangan' => 'nullable|string|max:500',
            'status_finance' => 'nullable|string|max:255',
        ]);

        $userJabatan = auth()->user()->karyawan->jabatan;
        $statusInput = $request->input('status_input');
        $id = $request->input('id_catering');
        $keterangan = $request->input('keterangan');
        $statusFinance = $request->input('status_finance');

        $catering = Catering::with('karyawan.user')->findOrFail($id);

        if ($catering->status_finance === 'Selesai') {
            return redirect()->back()->with('error', 'Pengajuan ini sudah selesai dan tidak bisa disetujui lagi.');
        }

        if ($statusFinance && $userJabatan !== 'Finance & Accounting') {
            return redirect()->back()->with('error', 'Aksi tidak diizinkan.');
        }

        $trackingMessage = '';
        if ($statusInput === '1') {
            $trackingMessage = $statusFinance ?? 'Status diperbarui oleh Finance.';
        } else {
            $trackingMessage = "Pengajuan anda tidak disetujui.";
        }

        $tracking = new TrackingCatering();
        $tracking->id_catering = $catering->id;
        $tracking->id_karyawan = auth()->user()->id;
        $tracking->tracking = $trackingMessage;
        $tracking->tanggal = now();
        $tracking->keterangan = $statusInput === '0' ? $keterangan : null;
        $tracking->save();

        if ($statusInput === '1' && $statusFinance) {
            $catering->update(['status_finance' => $statusFinance]);
        }

        $type = 'Approved Catering';
        $path = '/catering/detail/' . $catering->id;
        $karyawanPemohon = $catering->karyawan;

        $data = [
            'id_karyawan' => $karyawanPemohon->id,
            'tipe' => $catering->tipe,
            'id_catering' => $catering->id,
            'tanggal_pengajuan' => $catering->created_at->translatedFormat('l, d F Y'),
        ];

        $usersToNotify = collect([$karyawanPemohon->user]);

        if ($statusInput === '1') {
            $finance = Karyawan::where('jabatan', 'Finance & Accounting')->first();
            if ($finance?->user) {
                $usersToNotify->push($finance->user);
            }
        }

        foreach ($usersToNotify as $user) {
            if ($user) {
                $receiverId = $user->id;
                NotificationFacade::send($user, new cateringNotification($data, $path, $type, $receiverId));
            }
        }

        return redirect()->back()->with('success', 'Status pengajuan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $dataCatering = Catering::find($id);

        if (!$dataCatering) {
            return redirect()->back()->with('error', 'Data tidak ditemukan!');
        }

        DetailCatering::where('id_catering', $dataCatering->id)->delete();
        TrackingCatering::where('id_catering', $dataCatering->id)->delete();

        $dataCatering->delete();

        return redirect()->back()->with('success', 'Berhasil menghapus data!');
    }

    public function invoice($id)
    {
        $catering = Catering::with('karyawan')->findOrFail($id);
        return view('catering.invoice', compact('catering'));
    }


    public function updateInvoice(Request $request, $id)
    {
        $post = Catering::with('TrackingCatering')->findOrFail($id);

        if ($request->hasFile('invoice')) {
            if ($post->invoice) {
                Storage::delete('public/' . $post->invoice);
            }

            $file = $request->file('invoice');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $directory = 'catering';
            $path = $file->storeAs($directory, $filename, 'public');

            $latestTracking = $post->TrackingCatering->sortByDesc('tanggal')->first();

            if ($latestTracking && $latestTracking->tracking == 'Pencairan Sudah Selesai') {
                $status = 'Selesai';
                trackingcatering::create([
                    'id_catering' => $id,
                    'tracking' => $status,
                    'tanggal' => now()
                ]);
                $post->update([
                    'invoice' => $path,
                ]);
            } else {
                $post->update([
                    'invoice' => $path,
                ]);
            }
        } else {
            return redirect()->route('catering.index')
                ->with('error', 'Invoice gagal diupload.');
        }

        return redirect()->route('catering.index')
            ->with('success', 'Invoice berhasil disimpan.');
    }
}
