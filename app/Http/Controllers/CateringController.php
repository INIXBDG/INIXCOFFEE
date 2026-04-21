<?php

namespace App\Http\Controllers;

use App\Models\Catering;
use App\Models\DetailCatering;
use App\Models\karyawan;
use App\Models\RKM;
use App\Models\TrackingCatering;
use App\Models\User;
use App\Models\vendor;
use App\Models\vendorCoffeeBreak;
use App\Models\vendorMakansiang;
use App\Notifications\cateringNotification;
use App\Notifications\updateCateringNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Support\Facades\DB;

class CateringController extends Controller
{
    public function index()
    {
        return view('catering.index');
    }

    public function getData(Request $request)
    {
        $type = $request->get('type', 'catering');

        $query = Catering::with([
            'karyawan',
            'DetailCatering',
            'TrackingCatering' => function ($q) {
                $q->latest();
            },
        ]);

        if ($type === 'rencana') {
            $query->whereNotNull('status_pembelian')->whereNotNull('tanggal_pembelian');
        } else {
            $query->where(function ($q) {
                $q->whereNull('status_pembelian')->orWhereNull('tanggal_pembelian');
            });
        }

        $dataCatering = $query->get();

        $data = $dataCatering->map(function ($item) use ($type) {
            $latestTracking = $item->TrackingCatering->first();
            Carbon::setLocale('id');

            $totalHarga = $item->DetailCatering->sum(function ($detail) {
                return $detail->jumlah * $detail->harga;
            });

            return [
                'id' => $item->id,
                'tanggal_pengajuan' => $item->created_at->translatedFormat('l, d F Y'),
                'nama_karyawan' => $item->karyawan->nama_lengkap ?? '-',
                'divisi' => $item->karyawan->divisi ?? '-',
                'jabatan' => $item->karyawan->jabatan ?? '-',
                'tipe' => $item->tipe,
                'tracking' => $latestTracking->tracking ?? '-',
                'status_pembelian' => $item->status_pembelian,
                'tanggal_pembelian' => $item->tanggal_pembelian ? Carbon::parse($item->tanggal_pembelian)->translatedFormat('l, d F Y') : '-',
                'detail' => $item->DetailCatering->map(function ($detail) {
                    $vendor = null;
                    if ($detail->tipe_detail === 'Coffee Break') {
                        $vendor = vendorCoffeeBreak::where('id', $detail->id_vendor)->where('is_active', '1')->first();
                    } elseif ($detail->tipe_detail === 'Makan Siang') {
                        $vendor = vendorMakansiang::where('id', $detail->id_vendor)->where('is_active', '1')->first();
                    }
                    $namaMakanan = is_string($detail->nama_makanan) && json_decode($detail->nama_makanan, true) !== null ? json_decode($detail->nama_makanan, true) : [$detail->nama_makanan];
                    return [
                        'id' => $detail->id,
                        'nama_makanan' => $namaMakanan,
                        'jumlah' => $detail->jumlah,
                        'harga' => $detail->harga,
                        'keterangan' => $detail->keterangan,
                        'id_vendor' => $detail->id_vendor,
                        'vendor' => $vendor ? $vendor->nama : 'Vendor Tidak Ditemukan',
                        'tipe_detail' => $detail->tipe_detail,
                    ];
                }),
                'total_harga' => $totalHarga,
                'invoice' => $item->invoice,
                'is_rencana' => $type === 'rencana',
            ];
        });

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_karyawan' => 'required|exists:karyawans,id',
            'tipe' => 'required|string|max:255',
            'barang.vendor.*' => 'required|integer|exists:vendors,id',
            'barang.tipe_detail.*' => 'required|in:Coffee Break,Makan Siang',
            'barang.nama_makanan.*' => 'required|array|min:1',
            'barang.nama_makanan.*.*' => 'required|string|max:255',
            'barang.qty.*' => 'required|integer|min:1',
            'barang.harga.*' => 'required',
            'barang.keterangan.*' => 'nullable|string|max:500',
            'status_pembelian' => 'nullable|string|max:255',
            'tanggal_pembelian' => 'nullable|date|after_or_equal:today',
        ]);

        if ($request->filled('status_pembelian') && $request->filled('tanggal_pembelian')) {
            $tanggalPembelian = Carbon::parse($request->tanggal_pembelian);
            $startOfWeek = Carbon::now()->startOfWeek();
            $endOfWeek = Carbon::now()->endOfWeek();
        }

        $catering = Catering::create([
            'id_karyawan' => $request->id_karyawan,
            'tipe' => $request->tipe,
            'status_pembelian' => $request->filled('status_pembelian') ? $request->status_pembelian : null,
            'tanggal_pembelian' => $request->filled('tanggal_pembelian') ? $request->tanggal_pembelian : null,
        ]);

        $detailData = [];

        foreach ($request->barang['nama_makanan'] as $index => $namaArray) {
            $rawHarga = $request->barang['harga'][$index];
            $cleanHarga = preg_replace('/[^0-9]/', '', $rawHarga);

            $detailData[] = [
                'id_catering' => $catering->id,
                'id_vendor' => $request->barang['vendor'][$index],
                'tipe_detail' => $request->barang['tipe_detail'][$index],
                'nama_makanan' => json_encode($namaArray, JSON_UNESCAPED_UNICODE),
                'jumlah' => $request->barang['qty'][$index],
                'harga' => (int) $cleanHarga,
                'keterangan' => $request->barang['keterangan'][$index] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($detailData)) {
            DetailCatering::insert($detailData);
        }

        TrackingCatering::create([
            'id_catering' => $catering->id,
            'tracking' => 'Telah Diajukan',
            'tanggal' => now(),
        ]);

        $karyawanPengaju = Karyawan::findOrFail($request->id_karyawan);
        $userPengaju = $karyawanPengaju->user ?? null;

        $spvSales = Karyawan::where('jabatan', 'SPV Sales')->first();
        $finance = Karyawan::where('jabatan', 'Finance & Accounting')->first();

        $penerimaUsers = collect();

        if ($spvSales?->user) {
            $penerimaUsers->push($spvSales->user);
        }

        if ($finance?->user) {
            $penerimaUsers->push($finance->user);
        }

        if ($userPengaju) {
            $penerimaUsers->push($userPengaju);
        }

        $penerimaUsers = $penerimaUsers->unique('id')->values();

        $notifData = [
            'id_karyawan' => $request->id_karyawan,
            'tipe' => $request->tipe,
            'tanggal_pengajuan' => now()->format('d-m-Y H:i'),
            'nama_lengkap' => $karyawanPengaju->nama_lengkap,
        ];

        $currentUser = auth()->user();
        $senderUsername = $currentUser?->username ?? 'System';
        $senderNama = $currentUser?->karyawan?->nama_lengkap ?? 'Sistem';

        foreach ($penerimaUsers as $user) {
            NotificationFacade::send($user, new CateringNotification($notifData, '/catering/index', 'Pengajuan catering', $user->id, $senderUsername, $senderNama));
        }

        return response()->json(['success' => 'Pengajuan berhasil dikirim!', 'id' => $catering->id]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_detail_catering.*' => 'nullable|exists:detail_caterings,id',
            'vendor.*' => 'required|integer|exists:vendors,id',
            'tipe_detail.*' => 'required|in:Coffee Break,Makan Siang',
            'nama_makanan.*' => 'required|array|min:1',
            'nama_makanan.*.*' => 'required|string|max:255',
            'qty.*' => 'required|integer|min:1',
            'harga.*' => 'required',
            'keterangan.*' => 'nullable|string|max:500',
            'deleted_ids' => 'nullable|string',
        ]);

        $catering = Catering::findOrFail($id);

        if ($request->filled('deleted_ids')) {
            $deletedIds = array_filter(explode(',', $request->deleted_ids));
            DetailCatering::whereIn('id', $deletedIds)->delete();
        }

        if (!empty($request->nama_makanan)) {
            foreach ($request->nama_makanan as $i => $namaArray) {

                $detailId = $request->id_detail_catering[$i] ?? null;

                $rawHarga = $request->harga[$i] ?? '0';

                $cleanHarga = preg_replace('/[^0-9.,]/', '', $rawHarga);

                if (substr_count($cleanHarga, '.') === 1 && strlen(explode('.', $cleanHarga)[1]) === 2) {
                    $cleanHarga = explode('.', $cleanHarga)[0];
                } else {
                    $cleanHarga = str_replace('.', '', $cleanHarga);
                }

                $cleanHarga = str_replace(',', '', $cleanHarga);

                $data = [
                    'id_vendor' => $request->vendor[$i] ?? null,
                    'tipe_detail' => $request->tipe_detail[$i] ?? null,
                    'nama_makanan' => json_encode($namaArray, JSON_UNESCAPED_UNICODE),
                    'jumlah' => $request->qty[$i] ?? 0,
                    'harga' => (int) $cleanHarga,
                    'keterangan' => $request->keterangan[$i] ?? null,
                ];

                if ($detailId) {
                    $detail = DetailCatering::find($detailId);
                    if ($detail) {
                        $detail->update($data);
                    }
                } else {
                    $data['id_catering'] = $id;
                    DetailCatering::create($data);
                }
            }
        }

        TrackingCatering::create([
            'id_catering' => $id,
            'tracking' => 'Update data catering',
            'tanggal' => now(),
        ]);

        return response()->json(['success' => 'Data catering berhasil diperbarui.']);
    }
        
    public function upgradeToCatering(Request $request, $id)
    {
        $catering = Catering::findOrFail($id);

        if (!$catering->status_pembelian || !$catering->tanggal_pembelian) {
            return response()->json(['error' => 'Data ini bukan Rencana Pembelian'], 422);
        }

        $catering->update([
            'status_pembelian' => null,
            'tanggal_pembelian' => null,
        ]);

        TrackingCatering::create([
            'id_catering' => $catering->id,
            'tracking' => 'Ditingkatkan dari Rencana Pembelian menjadi Catering',
            'tanggal' => now(),
        ]);

        return response()->json(['success' => 'Rencana Pembelian berhasil ditingkatkan menjadi Catering']);
    }

    private function getVendorName($vendorId, $tipeDetail)
    {
        if (!$vendorId) {
            return '(kosong)';
        }

        if ($tipeDetail === 'Coffee Break') {
            $vendor = VendorCoffeeBreak::find($vendorId);
            return $vendor?->nama ?? "Vendor CB #{$vendorId}";
        } elseif ($tipeDetail === 'Makan Siang') {
            $vendor = VendorMakanSiang::find($vendorId);
            return $vendor?->nama ?? "Vendor MS #{$vendorId}";
        }

        return "Vendor #{$vendorId}";
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
            'nama_pengaju' => $cateringData->karyawan->nama_lengkap,
            'ttd' => $cateringData->karyawan->ttd,
            'jabatan' => $cateringData->karyawan->jabatan,
            'detail' => $cateringData->DetailCatering->map(function ($detail) {
                $vendor = null;

                if ($detail->tipe_detail === 'Coffee Break') {
                    $vendor = vendorCoffeeBreak::where('id', $detail->id_vendor)->where('is_active', '1')->first();
                } elseif ($detail->tipe_detail === 'Makan Siang') {
                    $vendor = vendorMakansiang::where('id', $detail->id_vendor)->where('is_active', '1')->first();
                }

                $namaMakanan = is_string($detail->nama_makanan) && json_decode($detail->nama_makanan, true) !== null ? json_decode($detail->nama_makanan, true) : [$detail->nama_makanan];

                return [
                    'nama_makanan' => $namaMakanan,
                    'jumlah' => $detail->jumlah,
                    'harga' => 'Rp ' . number_format($detail->harga, 0, ',', '.'),
                    'vendor' => $vendor ? $vendor->nama : 'Vendor Tidak Ditemukan',
                    'keterangan' => $detail->keterangan,
                    'tipe_detail' => $detail->tipe_detail,
                ];
            }),
            'total_harga' =>
                'Rp ' .
                number_format(
                    $cateringData->DetailCatering->sum(function ($detail) {
                        return $detail->jumlah * $detail->harga;
                    }),
                    0,
                    ',',
                    '.',
                ),
            'status_pembelian' => $cateringData->status_pembelian,
            'tanggal_pembelian' => $cateringData->tanggal_pembelian ? Carbon::parse($cateringData->tanggal_pembelian)->translatedFormat('l, d F Y') : null,
        ];

        $trackingTerbaru = $cateringData->TrackingCatering->sortByDesc('created_at')->first();

        $finance = karyawan::where('id', $trackingTerbaru->id_karyawan)->latest()->first();

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
            return response()->json(['error' => 'Pengajuan ini sudah selesai dan tidak bisa disetujui lagi.'], 422);
        }

        if ($statusFinance && $userJabatan !== 'Finance & Accounting') {
            return response()->json(['error' => 'Aksi tidak diizinkan.'], 422);
        }

        $trackingMessage = '';
        if ($statusInput === '1') {
            $trackingMessage = $statusFinance ?? 'Status diperbarui oleh Finance.';
        } else {
            $trackingMessage = 'Pengajuan anda tidak disetujui.';
        }

        TrackingCatering::create([
            'id_catering' => $catering->id,
            'id_karyawan' => auth()->user()->id,
            'tracking' => $trackingMessage,
            'tanggal' => now(),
            'keterangan' => $statusInput === '0' ? $keterangan : null,
        ]);

        if ($statusInput === '1' && $statusFinance) {
            $catering->update(['status_finance' => $statusFinance]);
        }

        $karyawanPemohon = $catering->karyawan;
        $userPemohon = $karyawanPemohon->user ?? null;

        $spvSales = Karyawan::where('jabatan', 'SPV Sales')->first();
        $finance = Karyawan::where('jabatan', 'Finance & Accounting')->first();

        $usersToNotify = collect();

        if ($userPemohon) {
            $usersToNotify->push($userPemohon);
        }

        if ($spvSales?->user) {
            $usersToNotify->push($spvSales->user);
        }

        if ($finance?->user) {
            $usersToNotify->push($finance->user);
        }

        $usersToNotify = $usersToNotify->unique('id')->values();

        $data = [
            'id_karyawan' => $karyawanPemohon->id,
            'tipe' => $catering->tipe,
            'id_catering' => $catering->id,
            'tanggal_pengajuan' => $catering->created_at->translatedFormat('l, d F Y'),
            'nama_lengkap' => $karyawanPemohon->nama_lengkap,
        ];

        $currentUser = auth()->user();
        $senderUsername = $currentUser?->username ?? 'System';
        $senderNama = $currentUser?->karyawan?->nama_lengkap ?? 'Sistem';

        foreach ($usersToNotify as $user) {
            if ($user) {
                NotificationFacade::send($user, new CateringNotification($data, '/catering/index', 'Pengajuan catering', $user->id, $senderUsername, $senderNama));
            }
        }

        return response()->json(['success' => 'Status pengajuan berhasil diperbarui.']);
    }

    public function destroy($id)
    {
        $dataCatering = Catering::find($id);

        if (!$dataCatering) {
            return response()->json(['error' => 'Data tidak ditemukan!'], 404);
        }

        DetailCatering::where('id_catering', $dataCatering->id)->delete();
        TrackingCatering::where('id_catering', $dataCatering->id)->delete();

        $dataCatering->delete();

        return response()->json(['success' => 'Berhasil menghapus data!']);
    }

    public function updateInvoice(Request $request, $id)
    {
        $post = Catering::with('TrackingCatering')->findOrFail($id);

        if ($request->file('invoice') && $request->file('invoice')->isValid()) {

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
                TrackingCatering::create([
                    'id_catering' => $id,
                    'tracking' => $status,
                    'tanggal' => now(),
                ]);

                $post->update([
                    'invoice' => $path,
                ]);
            } else {
                $post->update([
                    'invoice' => $path,
                ]);
            }

            return response()->json([
                'success' => 'Invoice berhasil disimpan.',
                'invoice_path' => $path
            ]);
        }

        return response()->json(['error' => 'Invoice gagal diupload.'], 422);
    }

    public function getVendors(Request $request)
    {
        $tipe = $request->get('tipe');

        if ($tipe === 'Coffee Break') {
            $vendors = VendorCoffeeBreak::where('is_active', '1')->get();
        } elseif ($tipe === 'Makan Siang') {
            $vendors = VendorMakanSiang::where('is_active', '1')->get();
        } else {
            return response()->json([]);
        }

        return response()->json($vendors);
    }

    public function getDetail($id)
    {
        $catering = Catering::with([
            'karyawan',
            'DetailCatering',
            'TrackingCatering' => function ($q) {
                $q->latest();
            },
        ])->findOrFail($id);

        Carbon::setLocale('id');
        $totalHarga = $catering->DetailCatering->sum(fn($d) => $d->jumlah * $d->harga);

        $data = [
            'id' => $catering->id,
            'tanggal_pengajuan' => $catering->created_at->translatedFormat('l, d F Y'),
            'nama_karyawan' => $catering->karyawan->nama_lengkap ?? '-',
            'divisi' => $catering->karyawan->divisi ?? '-',
            'jabatan' => $catering->karyawan->jabatan ?? '-',
            'tipe' => $catering->tipe,
            'tracking' => $catering->TrackingCatering->first()?->tracking ?? '-',
            'status_pembelian' => $catering->status_pembelian,
            'tanggal_pembelian' => $catering->tanggal_pembelian ? Carbon::parse($catering->tanggal_pembelian)->translatedFormat('l, d F Y') : '-',
            'invoice' => $catering->invoice,
            'total_harga' => $totalHarga,
            'detail' => $catering->DetailCatering->map(function ($detail) {
                $vendor = null;
                if ($detail->tipe_detail === 'Coffee Break') {
                    $vendor = vendorCoffeeBreak::where('id', $detail->id_vendor)->where('is_active', '1')->first();
                } elseif ($detail->tipe_detail === 'Makan Siang') {
                    $vendor = vendorMakansiang::where('id', $detail->id_vendor)->where('is_active', '1')->first();
                }
                $namaMakanan = is_string($detail->nama_makanan) && json_decode($detail->nama_makanan, true) !== null ? json_decode($detail->nama_makanan, true) : [$detail->nama_makanan];
                return [
                    'id' => $detail->id,
                    'nama_makanan' => $namaMakanan,
                    'jumlah' => $detail->jumlah,
                    'harga' => $detail->harga,
                    'keterangan' => $detail->keterangan,
                    'vendor' => $vendor ? $vendor->nama : 'Vendor Tidak Ditemukan',
                    'tipe_detail' => $detail->tipe_detail,
                ];
            }),
            'tracking_history' => $catering->TrackingCatering->map(function ($t) {
                return [
                    'tanggal' => Carbon::parse($t->tanggal)->translatedFormat('l, d F Y'),
                    'tracking' => $t->tracking,
                    'keterangan' => $t->keterangan ?? '-',
                ];
            }),
        ];

        return response()->json($data);
    }
}
