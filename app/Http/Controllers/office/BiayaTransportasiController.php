<?php

namespace App\Http\Controllers\office;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\BiayaTransportasiDriver;
use App\Models\pickupDriver;
use App\Models\PengajuanBarang;
use App\Models\detailPengajuanBarang;
use App\Models\tracking_pengajuan_barang;
use App\Models\karyawan;
use App\Models\User;
use App\Notifications\PengajuanbarangNotification;
use Carbon\Carbon;

class BiayaTransportasiController extends Controller
{
    public function index()
    {
        $dataPickup = pickupDriver::with(['karyawan', 'detailPickupDriver'])
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->latest()
            ->get();

        return view('office.biayaTransportasi.index', compact('dataPickup'));
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'id_pickup_driver' => 'required|exists:pickup_drivers,id',
            'biaya' => 'required|array|min:1',
            'biaya.*.tipe' => 'required|in:BBM,TOL,Parkir,Lainnya,Budget Lebih',
            'biaya.*.harga' => 'required|numeric|min:500',
            'biaya.*.bukti' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'biaya.*.keterangan' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $pengajuan = PengajuanBarang::create([
                'id_karyawan' => Auth::id(),
                'tipe' => 'Reimbursement',
                'invoice' => null,
                'keterangan' => $validated['biaya'][0]['keterangan'] ?? null,
            ]);

            foreach ($validated['biaya'] as $index => $item) {
                $path = $request->file("biaya.$index.bukti")?->store('biaya_transportasi_driver', 'public');

                $biaya = BiayaTransportasiDriver::create([
                    'id_karyawan' => Auth::id(),
                    'id_pickup_driver' => $validated['id_pickup_driver'],
                    'id_pengajuan_barang' => $pengajuan->id,
                    'tipe' => $item['tipe'],
                    'harga' => $item['harga'],
                    'bukti' => $path,
                    'keterangan' => $item['keterangan'] ?? null,
                ]);

                if (!$pengajuan->invoice && $path) {
                    $pengajuan->update(['invoice' => $path]);
                }

                detailPengajuanBarang::create([
                    'id_pengajuan_barang' => $pengajuan->id,
                    'nama_barang' => $item['tipe'],
                    'qty' => 1,
                    'harga' => $item['harga'],
                    'keterangan' => $item['keterangan'] ?? "Mengajukan reimbursement biaya {$item['tipe']}",
                ]);
            }

            $karyawan = karyawan::findOrFail(Auth::id());

            $trackingText = match ($karyawan->divisi) {
                'Education' => 'Diajukan dan Sedang Ditinjau oleh Education Manager',
                'Office' => 'Diajukan dan Sedang Ditinjau oleh Finance',
                'Sales & Marketing' => 'Diajukan dan Sedang Ditinjau oleh SPV Sales',
                default => 'Diajukan dan Sedang Ditinjau oleh General Manager',
            };

            $tracking = tracking_pengajuan_barang::create([
                'id_pengajuan_barang' => $pengajuan->id,
                'tracking' => $trackingText,
                'tanggal' => now(),
            ]);

            $pengajuan->update(['id_tracking' => $tracking->id]);

            $gm = karyawan::where('jabatan', 'GM')->first();
            if ($gm) {
                $users = User::whereHas('karyawan', fn($q) => $q->where('kode_karyawan', $gm->kode_karyawan))->get();

                foreach ($users as $user) {
                    $user->notify(
                        new PengajuanbarangNotification(
                            [
                                'id_karyawan' => Auth::id(),
                                'tipe' => 'Biaya Transportasi',
                                'tanggal_pengajuan' => now(),
                            ],
                            '/pengajuanbarang',
                            'Pengajuan Reimbursement',
                            $user->id,
                        ),
                    );
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Biaya transportasi berhasil diajukan.',
        ]);
    }

    public function get()
    {
        $data = BiayaTransportasiDriver::with(['pengajuanBarang.tracking', 'karyawan', 'pickupDriver.karyawan', 'pickupDriver.detailPickupDriver', 'pickupDriver'])
            ->orderBy('id_pickup_driver')
            ->orderBy('created_at', 'desc')
            ->get();

        $transformed = $data->map(function ($item) {
            return [
                'id' => $item->id,
                'id_karyawan' => $item->id_karyawan,
                'id_pickup_driver' => $item->id_pickup_driver,
                'id_pengajuan_barang' => $item->id_pengajuan_barang,
                'tipe' => $item->tipe,
                'harga' => $item->harga,
                'bukti' => $item->bukti,
                'keterangan' => $item->keterangan,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'pickupDriver' => $item->pickupDriver,
                'pengajuan_barang' => $item->pengajuanBarang
                    ? [
                        'id' => $item->pengajuanBarang->id,
                        'tracking' => $item->pengajuanBarang->tracking
                            ? [
                                'tracking' => $item->pengajuanBarang->tracking->tracking,
                            ]
                            : null,
                    ]
                    : null,

                'pickup_driver' => $item->pickupDriver
                    ? [
                        'id' => $item->pickupDriver->id,
                        'karyawan' => $item->pickupDriver->karyawan
                            ? [
                                'nama_lengkap' => $item->pickupDriver->karyawan->nama_lengkap,
                            ]
                            : null,
                        'detail_pickup_driver' => $item->pickupDriver->detailPickupDriver ? $item->pickupDriver->detailPickupDriver->map(fn($d) => ['lokasi' => $d->lokasi]) : [],
                    ]
                    : null,
            ];
        });

        return response()->json(['data' => $transformed]);
    }

    public function update(Request $request, $id_pickup_driver)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.tipe' => 'required|in:BBM,TOL,Parkir,Lainnya,Budget Lebih',
            'items.*.harga' => 'required|numeric|min:500',
            'items.*.keterangan' => 'nullable|string|max:255',
            'items.*.bukti' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $existingItems = BiayaTransportasiDriver::where('id_pickup_driver', $id_pickup_driver)->where('id_karyawan', Auth::id())->get();

        abort_if($existingItems->isEmpty(), 403, 'Data tidak ditemukan atau tidak memiliki akses.');

        DB::transaction(function () use ($existingItems, $validated, $request, $id_pickup_driver) {
            $itemIdsToKeep = [];

            foreach ($validated['items'] as $index => $itemData) {
                $itemId = $request->input("items.$index.id");

                if ($itemId) {
                    $biaya = $existingItems->firstWhere('id', $itemId);

                    if ($biaya) {
                        $updateData = [
                            'tipe' => $itemData['tipe'],
                            'harga' => $itemData['harga'],
                            'keterangan' => $itemData['keterangan'] ?? null,
                        ];

                        if ($request->hasFile("items.$index.bukti")) {
                            if ($biaya->bukti) {
                                Storage::disk('public')->delete($biaya->bukti);
                            }
                            $updateData['bukti'] = $request->file("items.$index.bukti")->store('biaya_transportasi_driver', 'public');
                        }

                        $biaya->update($updateData);
                        $itemIdsToKeep[] = $biaya->id;

                        if ($biaya->pengajuanBarang) {
                            $detail = $biaya->pengajuanBarang->detail()->where('nama_barang', $biaya->getOriginal('tipe'))->first();

                            if ($detail) {
                                $detail->update([
                                    'nama_barang' => $itemData['tipe'],
                                    'harga' => $itemData['harga'],
                                    'keterangan' => $itemData['keterangan'] ?? "Mengajukan reimbursement biaya {$itemData['tipe']}",
                                ]);
                            }
                        }
                    }
                } else {
                    $newBiayaData = [
                        'id_karyawan' => Auth::id(),
                        'id_pickup_driver' => $id_pickup_driver,
                        'tipe' => $itemData['tipe'],
                        'harga' => $itemData['harga'],
                        'keterangan' => $itemData['keterangan'] ?? null,
                    ];

                    if ($request->hasFile("items.$index.bukti")) {
                        $newBiayaData['bukti'] = $request->file("items.$index.bukti")->store('biaya_transportasi_driver', 'public');
                    }

                    $biaya = BiayaTransportasiDriver::create($newBiayaData);
                    $itemIdsToKeep[] = $biaya->id;

                    $pengajuan = $existingItems->first()?->pengajuanBarang;

                    if ($pengajuan) {
                        detailPengajuanBarang::create([
                            'id_pengajuan_barang' => $pengajuan->id,
                            'nama_barang' => $itemData['tipe'],
                            'qty' => 1,
                            'harga' => $itemData['harga'],
                            'keterangan' => $itemData['keterangan'] ?? "Mengajukan reimbursement biaya {$itemData['tipe']}",
                        ]);
                    }
                }
            }

            $itemsToDelete = $existingItems->whereNotIn('id', $itemIdsToKeep);

            foreach ($itemsToDelete as $item) {
                if ($item->bukti) {
                    Storage::disk('public')->delete($item->bukti);
                }
                $item->delete();

                if ($item->pengajuanBarang) {
                    $detail = $item->pengajuanBarang->detail()->where('nama_barang', $item->tipe)->first();
                    if ($detail) {
                        $detail->delete();
                    }
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diperbarui.',
        ]);
    }

    public function destroy($id_pickup_driver)
    {
        $items = BiayaTransportasiDriver::where('id_pickup_driver', $id_pickup_driver)->with('pengajuanBarang')->get();

        DB::transaction(function () use ($items) {
            $pengajuanIds = [];

            foreach ($items as $item) {
                if ($item->bukti) {
                    Storage::disk('public')->delete($item->bukti);
                }
                $item->delete();

                if ($item->pengajuanBarang) {
                    $pengajuanIds[] = $item->pengajuanBarang->id;
                }
            }

            $pengajuanIds = array_unique($pengajuanIds);

            foreach ($pengajuanIds as $pengajuanId) {
                $count = BiayaTransportasiDriver::where('id_pengajuan_barang', $pengajuanId)->count();

                if ($count === 0) {
                    $pengajuan = PengajuanBarang::find($pengajuanId);
                    if ($pengajuan) {
                        $pengajuan->detail()->delete();
                        $pengajuan->tracking()->delete();
                        $pengajuan->delete();
                    }
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus.',
        ]);
    }
}
