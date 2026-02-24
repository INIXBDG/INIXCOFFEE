<?php

namespace App\Http\Controllers\office;

use App\Models\DetailPickupDriver;
use App\Models\karyawan;
use App\Models\pickupDriver;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\activityLog;
use App\Models\BiayaTransportasiDriver;
use App\Models\PerbaikanKendaraan;
use App\Models\TrackingPickupDriver;
use App\Models\User;
use App\Notifications\KoordinasiDriverNotifcation;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Support\Facades\Auth;

class pickupDriverController extends Controller
{
    public function index()
    {
        $latestPerKendaraan = PerbaikanKendaraan::select('kendaraan')->selectRaw('MAX(id) as max_id')->groupBy('kendaraan');

        $kendaraan = PerbaikanKendaraan::joinSub($latestPerKendaraan, 'latest', function ($join) {
            $join->on('perbaikan_kendaraans.id', '=', 'latest.max_id');
        })
            ->where(function ($query) {
                $query->where('type_condition', '!=', 'Kecelakaan')->orWhere('status', 'Selesai');
            })
            ->where(function ($query) {
                $query->where('type_vehicle_condition', '!=', ['Kerusakan Berat', 'Kerusakan Total'])->orWhere('status', 'Selesai');
            })
            ->pluck('perbaikan_kendaraans.kendaraan');

        if ($kendaraan->isEmpty()) {
            $kendaraan = collect(['H1', 'Innova']);
        }

        if ($kendaraan === "Innova") {
            $kendaraan === "Inova";
        }

        $dataDriver = karyawan::where('jabatan', 'Driver')->get();

        return view('office.pickupdriver.index', compact('dataDriver', 'kendaraan'));
    }

    public function create()
    {
        $dataDriver = karyawan::where('jabatan', 'Driver')
            ->where(function ($query) {
                $query->whereDoesntHave('pickupDriver')->orWhereHas('pickupDriver', function ($q) {
                    $q->whereIn('status_driver', ['Selesai, Driver Ready']);
                });
            })
            ->get();

        return view('office.pickupdriver.create', compact('dataDriver'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_driver' => 'required|integer',
            'budget' => 'nullable|integer',
            'tipe' => 'required|array',
            'tipe.*' => 'required|string',

            'lokasi' => 'required|array',
            'lokasi.*' => 'required|string',

            'tanggal' => 'required|array',
            'tanggal.*' => 'required|date',

            'waktu' => 'required|array',
            'waktu.*' => 'required',

            'detail' => 'nullable|array',
            'detail.*' => 'nullable|string',
        ]);

        $budget = empty($request->budget) || $request->budget == 0 ? null : $request->budget;

        $send = pickupDriver::create([
            'id_karyawan' => $request->id_driver,
            'id_pembuat' => auth()->user()->id,
            'status_apply' => 0,
            'status_driver' => 'Ready',
            'budget' => $budget,
        ]);

        if (!$send) {
            return back()->with('error', 'Gagal membuat koordinasi driver. Silakan coba lagi.');
        }

        foreach ($request->tipe as $index => $tipe) {
            DetailPickupDriver::create([
                'pickup_driver_id' => $send->id,
                'tipe' => $tipe,
                'lokasi' => $request->lokasi[$index],
                'tanggal_keberangkatan' => $request->tanggal[$index],
                'waktu_keberangkatan' => $request->waktu[$index],
                'detail' => $request->detail[$index],
            ]);
        }

        TrackingPickupDriver::create([
            'pickup_driver_id' => $send->id,
            'status' => auth()->user()->username . ' telah membuat koordinasi baru',
            'diubah_oleh' => auth()->user()->id,
        ]);

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
            'tipe' => $request->tipe,
            'tanggal_pembuatan' => now(),
            'id_pengajuan' => $send->id,
        ];
        $type = 'Koordinasi Driver';
        $path = '/office/pickup-driver/index';

        foreach ($users as $user) {
            $receiverId = $user->id;
            NotificationFacade::send($user, new KoordinasiDriverNotifcation($data, $path, $type, $receiverId));
        }

        return redirect()->route('office.pickupDriver.index')->with('success', 'Koordinasi pickup driver berhasil dibuat.');
    }

    public function get()
    {
        $data = PickupDriver::with(['karyawan', 'detailPickupDriver', 'pembuat', 'Tracking'])
            ->orderByRaw('status_apply = 0 DESC')
            ->orderBy('created_at', 'DESC')
            ->get();

        $data->transform(function ($item) {
            $uangKepakai = BiayaTransportasiDriver::where('id_pickup_driver', $item->id)->sum('harga');

            $item->uang_kepakai = $uangKepakai;
            $item->sisa_budget = $item->budget - $uangKepakai;

            $driver = $item->karyawan;

            return $item;
        });

        return response()->json($data);
    }

    public function getDriverStatus()
    {
        $drivers = Karyawan::where('jabatan', 'Driver')->select('id', 'nama_lengkap', 'jabatan')->get();

        if ($drivers->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Driver tidak ditemukan',
                'data' => [],
            ]);
        }

        $driverStatus = $drivers->map(function ($driver) {
            $lastLog = ActivityLog::where('user_id', $driver->id)
                ->whereIn('status', ['login', 'logout'])
                ->orderBy('created_at', 'desc')
                ->first();

            $status = 'offline';
            if ($lastLog && $lastLog->status === 'login') {
                $status = 'online';
            }

            return [
                'driver_id' => $driver->id,
                'nama' => $driver->nama_lengkap,
                'status' => $status,
                'foto' => $driver->foto,
            ];
        });

        $onlineCount = $driverStatus->where('status', 'online')->count();
        $offlineCount = $driverStatus->where('status', 'offline')->count();

        return response()->json([
            'status' => 'success',
            'total_drivers' => $drivers->count(),
            'online' => $onlineCount,
            'offline' => $offlineCount,
            'data' => $driverStatus,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'vehicle' => 'required|string',
        ]);

        $pickupDriver = pickupDriver::with('detailPickupDriver')->findOrFail($id);

        $detail = $pickupDriver->detailPickupDriver->first();

        if (!$detail) {
            return redirect()->back()->with('error', 'Detail pickup tidak ditemukan');
        }
        $statusDriver = null;

        $pickupDriver->status_apply = 1;
        if ($pickupDriver->status_driver === 'menggungu') {
        }
        if ($pickupDriver->detailPickupDriver->first()->tipe === 'Penjemputan') {
            $pickupDriver->status_driver = 'Sedang Menjemput';
            $statusDriver = 'Sedang Menjemput';
        } elseif ($pickupDriver->detailPickupDriver->first()->tipe === 'Pengantaran') {
            $pickupDriver->status_driver = 'Sedang Mengantarkan';
            $statusDriver = 'Sedang Mengantarkan';
        }
        $pickupDriver->kendaraan = $request->input('vehicle');
        $pickupDriver->save();

        $user = Auth()->user()->username;

        TrackingPickupDriver::create([
            'pickup_driver_id' => $pickupDriver->id,
            'status' => $user . ' telah menerima, status menjadi ' . $statusDriver . ' menggunakan ' . $pickupDriver->kendaraan,
            'diubah_oleh' => auth()->user()->id,
        ]);

        $creator = auth()->user();
        $creatorKaryawan = $creator->karyawan;
        $creatorJabatan = $creatorKaryawan->jabatan;

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

        $users = User::whereHas('karyawan', function ($query) use ($recipients) {
            $query->whereIn('kode_karyawan', $recipients);
        })->get();

        $detailTipe = $pickupDriver->detailPickupDriver->pluck('tipe')->toArray();

        $data = [
            'id_karyawan' => $pickupDriver->id_karyawan,
            'tipe' => $detailTipe,
            'tanggal_pembuatan' => now(),
            'id_pengajuan' => $pickupDriver->id,
        ];
        $type = 'Update Koordinasi Driver';
        $path = '/office/pickup-driver/index';

        foreach ($users as $user) {
            $receiverId = $user->id;
            NotificationFacade::send($user, new KoordinasiDriverNotifcation($data, $path, $type, $receiverId));
        }

        return redirect()->route('office.pickupDriver.index')->with('success', 'Status driver berhasil diperbarui menjadi Diterima.');
    }

    public function updateKepulangan(Request $request)
    {
        $request->validate([
            'pickup_driver_id' => 'required|integer',
            'waktu_kepulangan' => 'required',
        ]);

        $pickupDriver = pickupDriver::findOrFail($request->pickup_driver_id);
        $pickupDriver->waktu_kepulangan = $request->waktu_kepulangan;
        $pickupDriver->status_apply = 2;
        $pickupDriver->status_driver = 'Selesai, Driver Ready';
        $pickupDriver->save();

        $user = Auth()->user()->username;

        TrackingPickupDriver::create([
            'pickup_driver_id' => $pickupDriver->id,
            'status' => $user . ' telah selesai mengantarkan/menjemput ',
            'diubah_oleh' => auth()->user()->id,
        ]);

        $creator = auth()->user();
        $creatorKaryawan = $creator->karyawan;
        $creatorJabatan = $creatorKaryawan->jabatan;

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

        $users = User::whereHas('karyawan', function ($query) use ($recipients) {
            $query->whereIn('kode_karyawan', $recipients);
        })->get();

        $detailTipe = $pickupDriver->detailPickupDriver()->pluck('tipe')->toArray();

        $data = [
            'id_karyawan' => $pickupDriver->id_karyawan,
            'tipe' => $detailTipe,
            'tanggal_pembuatan' => now(),
            'id_pengajuan' => $pickupDriver->id,
        ];
        $type = 'Update Koordinasi Driver';
        $path = '/office/pickup-driver/index';

        foreach ($users as $user) {
            $receiverId = $user->id;
            NotificationFacade::send($user, new KoordinasiDriverNotifcation($data, $path, $type, $receiverId));
        }

        return redirect()->route('office.pickupDriver.index')->with('success', 'Waktu kepulangan driver berhasil diperbarui.');
    }

    public function delete($id)
    {
        $data = pickupDriver::findOrFail($id);
        $driverId = $data->id_karyawan;
        $detailTipe = $data->detailPickupDriver()->pluck('tipe')->toArray();

        DetailPickupDriver::where('pickup_driver_id', $data->id)->delete();
        TrackingPickupDriver::where('pickup_driver_id', $data->id)->delete();

        $user = Auth()->user()->username;
        TrackingPickupDriver::create([
            'pickup_driver_id' => $data->id,
            'status' => $user . ' telah menghapus data koordinasi pickup driver',
            'diubah_oleh' => auth()->user()->id,
        ]);

        $creator = auth()->user();
        $creatorKaryawan = $creator->karyawan;
        $creatorJabatan = $creatorKaryawan->jabatan;

        $driver = karyawan::findOrFail($driverId);

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

        $dataNotif = [
            'id_karyawan' => $driverId,
            'tipe' => $detailTipe,
            'tanggal_pembuatan' => now(),
            'id_pengajuan' => $data->id,
        ];
        $type = 'Update Koordinasi Driver';
        $path = '/office/pickup-driver/index';

        foreach ($users as $user) {
            $receiverId = $user->id;
            NotificationFacade::send($user, new KoordinasiDriverNotifcation($dataNotif, $path, $type, $receiverId));
        }

        $data->delete();

        return response()->json(['message' => 'Data berhasil dihapus']);
    }

    public function updateKoordinasi(Request $request)
    {
        $request->validate([
            'pickup_driver_id' => 'required|exists:pickup_drivers,id',
            'id_driver' => 'required|exists:karyawans,id',
            'kendaraan' => 'nullable|in:Inova,H1',
            'details' => 'required|array|min:1',
            'details.*.tipe' => 'required|in:Penjemputan,Pengantaran',
            'details.*.lokasi' => 'required|string',
            'details.*.tanggal' => 'required|date',
            'details.*.waktu' => 'required',
        ]);

        $pickup = pickupDriver::with('detailPickupDriver', 'karyawan')->findOrFail($request->pickup_driver_id);
        $oldDetails = $pickup->detailPickupDriver
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'tipe' => $item->tipe,
                    'lokasi' => $item->lokasi,
                    'tanggal' => $item->tanggal_keberangkatan,
                    'waktu' => substr($item->waktu_keberangkatan, 0, 5),
                ];
            })
            ->keyBy('id');

        $changes = [];
        if ($pickup->id_karyawan != $request->id_driver) {
            $oldDriver = $pickup->karyawan->nama_lengkap ?? 'Tidak diketahui';
            $newDriver = karyawan::where('id', $request->id_driver)->first()?->nama_lengkap ?? 'Tidak diketahui';
            $changes[] = "Driver diubah dari {$oldDriver} menjadi {$newDriver}";
        }

        if ($pickup->kendaraan != ($request->kendaraan ?? null)) {
            $oldVehicle = $pickup->kendaraan ?: 'tidak ada';
            $newVehicle = $request->kendaraan ?: 'tidak ada';
            $changes[] = "Kendaraan diubah dari {$oldVehicle} menjadi {$newVehicle}";
        }

        $newDetailsRaw = collect($request->details)->map(function ($detail) {
            return [
                'tipe' => $detail['tipe'],
                'lokasi' => $detail['lokasi'],
                'tanggal' => $detail['tanggal'],
                'waktu' => $detail['waktu'],
            ];
        });

        $deleted = [];
        foreach ($oldDetails as $id => $old) {
            $found = false;
            foreach ($newDetailsRaw as $new) {
                if ($old['tipe'] === $new['tipe'] && $old['lokasi'] === $new['lokasi'] && $old['tanggal'] == $new['tanggal'] && $old['waktu'] === $new['waktu']) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $deleted[] = "Detail dihapus: {$old['tipe']} ke {$old['lokasi']} pada {$old['tanggal']} pukul {$old['waktu']}";
            }
        }

        $added = [];
        foreach ($newDetailsRaw as $new) {
            $found = false;
            foreach ($oldDetails as $old) {
                if ($old['tipe'] === $new['tipe'] && $old['lokasi'] === $new['lokasi'] && $old['tanggal'] == $new['tanggal'] && $old['waktu'] === $new['waktu']) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $added[] = "Detail ditambahkan: {$new['tipe']} ke {$new['lokasi']} pada {$new['tanggal']} pukul {$new['waktu']}";
            }
        }

        if (empty($changes) && empty($deleted) && empty($added)) {
            return response()->json(['success' => true, 'message' => 'Tidak ada perubahan yang disimpan.']);
        }

        $pickup->id_karyawan = $request->id_driver;
        $pickup->kendaraan = $request->filled('kendaraan') ? $request->kendaraan : null;
        $pickup->save();

        DetailPickupDriver::where('pickup_driver_id', $pickup->id)->delete();
        foreach ($request->details as $detail) {
            DetailPickupDriver::create([
                'pickup_driver_id' => $pickup->id,
                'tipe' => $detail['tipe'],
                'lokasi' => $detail['lokasi'],
                'tanggal_keberangkatan' => $detail['tanggal'],
                'waktu_keberangkatan' => $detail['waktu'] . ':00',
            ]);
        }

        $user = auth()->user()->username;
        $logMessages = array_merge($changes, $deleted, $added);
        $fullLog = $user . ' memperbarui koordinasi: ' . implode('; ', $logMessages);

        TrackingPickupDriver::create([
            'pickup_driver_id' => $pickup->id,
            'status' => $fullLog,
            'diubah_oleh' => auth()->user()->id,
        ]);

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

        $detailTipe = collect($request->details)->pluck('tipe')->toArray();

        $data = [
            'id_karyawan' => $request->id_driver,
            'tipe' => $detailTipe,
            'tanggal_pembuatan' => now(),
            'id_pengajuan' => $pickup->id,
        ];
        $type = 'Update Koordinasi Driver';
        $path = '/office/pickup-driver/index';

        foreach ($users as $user) {
            $receiverId = $user->id;
            NotificationFacade::send($user, new KoordinasiDriverNotifcation($data, $path, $type, $receiverId));
        }

        return response()->json(['success' => true, 'message' => 'Koordinasi berhasil diperbarui dan dilacak.']);
    }
}
