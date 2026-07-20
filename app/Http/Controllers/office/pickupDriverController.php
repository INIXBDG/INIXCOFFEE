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
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use App\Exports\PickupDriverReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\outstanding;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class pickupDriverController extends Controller
{
    private const BOT_TOKEN = '8619211414:AAHnpchtKmY_FEKrOnj1VQTUsYKqp3Smuhw';
    private const CHAT_ID = '-1003758833562';

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
            $kendaraan = collect(['H1', 'Innova', 'Mobil Direksi']);
        }

        if ($kendaraan->contains('Innova')) {
            $kendaraan = $kendaraan->map(function ($item) {
                return $item === 'Innova' ? 'Inova' : $item;
            });
        }

        $dataDriver = karyawan::where('jabatan', 'Driver')->get();

        $extends = 'layouts_office.app';
        $section = 'office_contents';

        return view('office.pickupdriver.index', compact('dataDriver', 'kendaraan', 'extends', 'section'));
    }

    public function create()
    {
        $dataDriver = karyawan::where('jabatan', 'Driver')
            ->where('status_aktif', '1')
            ->where(function ($query) {
                $query->whereDoesntHave('pickupDriver')->orWhereHas('pickupDriver', function ($q) {
                    $q->where('status_driver', 'Selesai, Driver Ready');
                });
            })
            ->get();

        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $kendaraanSedangDipakai = pickupDriver::where('status_apply', 1)->whereNotNull('kendaraan')->where('kendaraan', '!=', '')->pluck('kendaraan')->unique();

        $allKendaraan = collect(['H1', 'Innova', 'Mobil Direksi']);

        $kendaraanTersedia = $allKendaraan->diff($kendaraanSedangDipakai);

        if ($kendaraanTersedia->isEmpty()) {
            $kendaraanTersedia = $allKendaraan;
        }

        if ($kendaraanTersedia->contains('Innova')) {
            $kendaraanTersedia = $kendaraanTersedia->map(function ($item) {
                return $item === 'Innova' ? 'Inova' : $item;
            });
        }

        $kendaraan = $kendaraanTersedia->values()->all();

        $extends = 'layouts_office.app';
        $section = 'office_contents';

        return view('office.pickupdriver.create', compact('dataDriver', 'kendaraan', 'extends', 'section'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_driver' => 'required|integer',
            'kendaraan' => 'required|string',
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
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $tipePerjalananUtama = in_array('Operasional Kantor', $request->tipe) ? 'Operasional Kantor' : $request->tipe[0];

        $send = pickupDriver::create([
            'id_karyawan' => $request->id_driver,
            'id_pembuat' => auth()->user()->id,
            'status_apply' => 0,
            'status_driver' => 'Ready',
            'kendaraan' => $request->kendaraan,
            'budget' => $budget,
            'tipe_perjalanan' => $tipePerjalananUtama,
        ]);

        if (!$send) {
            return response()->json(['success' => false, 'message' => 'Gagal membuat koordinasi driver. Silakan coba lagi.'], 500);
        }

        foreach ($request->tipe as $index => $tipe) {
            DetailPickupDriver::create([
                'pickup_driver_id' => $send->id,
                'tipe' => $tipe,
                'lokasi' => $request->lokasi[$index],
                'tanggal_keberangkatan' => $request->tanggal[$index],
                'waktu_keberangkatan' => $request->waktu[$index],
                'detail' => $request->detail[$index] ?? null,
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
            NotificationFacade::send($user, new KoordinasiDriverNotifcation($data, $path, $type, $user->id));
        }

        $telegramData = [
            'title' => '🆕 Koordinasi Driver Baru',
            'id_pengajuan' => $send->id,
            'creator_name' => $creatorKaryawan->nama_lengkap ?? $creator->nama_lengkap,
            'driver_name' => $driver->nama ?? ($driver->nama_lengkap ?? '-'),
            'budget' => $budget,
            'tanggal_pembuatan' => now(),
            'status_text' => 'Menunggu Konfirmasi',
            'status_apply' => 0,
            'tipe' => $request->tipe ?? [],
            'lokasi' => $request->lokasi ?? [],
            'tanggal' => $request->tanggal ?? [],
            'waktu' => $request->waktu ?? [],
            'detail' => $request->detail ?? [],
            'log_text' => null,
        ];

        $this->sendTelegramNotification($telegramData);

        return response()->json([
            'success' => true,
            'message' => 'Koordinasi pickup driver berhasil dibuat.',
            'data' => $send->load(['karyawan', 'detailPickupDriver', 'pembuat']),
        ]);
    }

    public function get()
    {
        $data = pickupDriver::with(['karyawan', 'detailPickupDriver', 'pembuat', 'Tracking'])
            ->orderByRaw('status_apply = 0 DESC')
            ->orderBy('created_at', 'DESC')
            ->get();

        $data->transform(function ($item) {
            $uangKepakai = BiayaTransportasiDriver::where('id_pickup_driver', $item->id)->sum('harga');
            $item->uang_kepakai = $uangKepakai;
            $item->sisa_budget = $item->budget - $uangKepakai;
            return $item;
        });

        return response()->json($data);
    }

    public function getDriverStatus()
    {
        $drivers = karyawan::where('jabatan', 'Driver')->where('status_aktif', '1')->select('id', 'nama_lengkap', 'jabatan', 'foto')->get();

        if ($drivers->isEmpty()) {
            return response()->json(['status' => 'success', 'message' => 'Driver tidak ditemukan', 'data' => []]);
        }

        $driverStatus = $drivers->map(function ($driver) {
            $lastLog = activityLog::where('user_id', $driver->id)
                ->whereIn('status', ['login', 'logout'])
                ->orderBy('created_at', 'desc')
                ->first();
            $status = $lastLog && $lastLog->status === 'login' ? 'online' : 'offline';
            return [
                'driver_id' => $driver->id,
                'nama' => $driver->nama_lengkap,
                'status' => $status,
                'foto' => $driver->foto,
            ];
        });

        return response()->json([
            'status' => 'success',
            'total_drivers' => $drivers->count(),
            'online' => $driverStatus->where('status', 'online')->count(),
            'offline' => $driverStatus->where('status', 'offline')->count(),
            'data' => $driverStatus,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $pickupDriver = pickupDriver::with('detailPickupDriver')->findOrFail($id);
        $detail = $pickupDriver->detailPickupDriver->first();

        if (!$detail) {
            return response()->json(['success' => false, 'message' => 'Detail pickup tidak ditemukan'], 404);
        }

        $pickupDriver->status_apply = 1;

        if ($detail->tipe === 'Penjemputan') {
            $pickupDriver->status_driver = 'Sedang Menjemput';
            $statusDriver = 'Sedang Menjemput';
        } elseif ($detail->tipe === 'Pengantaran') {
            $pickupDriver->status_driver = 'Sedang Mengantarkan';
            $statusDriver = 'Sedang Mengantarkan';
        } else {
            $statusDriver = 'Diterima';
        }

        $pickupDriver->save();

        $user = Auth()->user()->username;
        TrackingPickupDriver::create([
            'pickup_driver_id' => $pickupDriver->id,
            'status' => $user . ' telah menerima koordinasi, status menjadi ' . $statusDriver . ' dengan kendaraan ' . ($pickupDriver->kendaraan ?? '-'),
            'diubah_oleh' => auth()->user()->id,
        ]);

        $creator = auth()->user();
        $creatorKaryawan = $creator->karyawan;
        $driver = karyawan::findOrFail($pickupDriver->id_karyawan);
        $recipients = [];

        if ($creatorKaryawan->jabatan == 'HRD') {
            $CS = karyawan::where('jabatan', 'Customer Care')->first();
            if ($CS) {
                $recipients[] = $CS->kode_karyawan;
            }
            $recipients[] = $driver->kode_karyawan;
        } elseif ($creatorKaryawan->jabatan == 'Customer Care') {
            $HRD = karyawan::where('jabatan', 'HRD')->first();
            if ($HRD) {
                $recipients[] = $HRD->kode_karyawan;
            }
            $recipients[] = $driver->kode_karyawan;
        }

        $users = User::whereHas('karyawan', fn($q) => $q->whereIn('kode_karyawan', $recipients))->get();
        $detailTipe = $pickupDriver->detailPickupDriver->pluck('tipe')->toArray();

        foreach ($users as $user) {
            NotificationFacade::send(
                $user,
                new KoordinasiDriverNotifcation(
                    [
                        'id_karyawan' => $pickupDriver->id_karyawan,
                        'tipe' => $detailTipe,
                        'tanggal_pembuatan' => now(),
                        'id_pengajuan' => $pickupDriver->id,
                    ],
                    '/office/pickup-driver/index',
                    'Update Koordinasi Driver',
                    $user->id,
                ),
            );
        }

        $telegramPayload = [
            'title' => '🔄 Status Diperbarui',
            'id_pengajuan' => $pickupDriver->id,
            'creator_name' => $creatorKaryawan->nama_lengkap,
            'driver_name' => $driver->nama ?? ($driver->nama_lengkap ?? '-'),
            'budget' => $pickupDriver->budget,
            'tanggal_pembuatan' => now(),
            'status_text' => $statusDriver,
            'status_apply' => $pickupDriver->status_apply,
            'tipe' => $detailTipe,
            'lokasi' => [],
            'tanggal' => [],
            'waktu' => [],
            'detail' => [],
            'log_text' => null,
        ];

        $this->sendTelegramNotification($telegramPayload);

        return response()->json([
            'success' => true,
            'message' => 'Koordinasi diterima. Selamat bertugas!',
            'data' => $pickupDriver->load(['karyawan', 'detailPickupDriver']),
        ]);
    }

    public function updateKepulangan(Request $request)
    {
        $request->validate([
            'pickup_driver_id' => 'required|integer|exists:pickup_drivers,id',
            'waktu_kepulangan' => 'required',
            'KM_awal' => 'nullable',
            'KM_akhir' => 'nullable',
            'total_pemakaian' => 'nullable',
        ]);

        $pickupDriver = pickupDriver::findOrFail($request->pickup_driver_id);

        $pickupDriver->waktu_kepulangan = $request->waktu_kepulangan;
        $pickupDriver->status_apply = 2;
        $pickupDriver->status_driver = 'Selesai, Driver Ready';

        if ($request->filled('KM_awal')) {
            $pickupDriver->KM_awal = $request->KM_awal;
        }
        if ($request->filled('KM_akhir')) {
            $pickupDriver->KM_akhir = $request->KM_akhir;
        }

        $pickupDriver->save();

        $user = Auth()->user()->username;
        TrackingPickupDriver::create([
            'pickup_driver_id' => $pickupDriver->id,
            'status' => $user . ' telah menyelesaikan perjalanan. KM: ' . ($request->KM_awal ?? '-') . ' → ' . ($request->KM_akhir ?? '-') . ($request->filled('total_pemakaian') ? ', Budget terpakai: Rp ' . number_format($request->total_pemakaian, 0, ',', '.') : ''),
            'diubah_oleh' => auth()->user()->id,
        ]);

        $creator = auth()->user();
        $creatorKaryawan = $creator->karyawan;
        $driver = karyawan::findOrFail($pickupDriver->id_karyawan);
        $recipients = [];

        if ($creatorKaryawan->jabatan == 'HRD') {
            $CS = karyawan::where('jabatan', 'Customer Care')->first();
            if ($CS) {
                $recipients[] = $CS->kode_karyawan;
            }
            $recipients[] = $driver->kode_karyawan;
        } elseif ($creatorKaryawan->jabatan == 'Customer Care') {
            $HRD = karyawan::where('jabatan', 'HRD')->first();
            if ($HRD) {
                $recipients[] = $HRD->kode_karyawan;
            }
            $recipients[] = $driver->kode_karyawan;
        }

        $users = User::whereHas('karyawan', fn($q) => $q->whereIn('kode_karyawan', $recipients))->get();
        $detailTipe = $pickupDriver->detailPickupDriver()->pluck('tipe')->toArray();

        foreach ($users as $user) {
            NotificationFacade::send(
                $user,
                new KoordinasiDriverNotifcation(
                    [
                        'id_karyawan' => auth()->user()->id,
                        'tipe' => $detailTipe,
                        'tanggal_pembuatan' => now(),
                        'id_pengajuan' => $pickupDriver->id,
                    ],
                    '/office/pickup-driver/index',
                    'Update Koordinasi Driver',
                    $user->id,
                ),
            );
        }

        $telegramPayload = [
            'title' => '✅ Koordinasi Selesai',
            'id_pengajuan' => $pickupDriver->id,
            'creator_name' => $creatorKaryawan->nama_lengkap,
            'driver_name' => $driver->nama ?? ($driver->nama_lengkap ?? '-'),
            'budget' => $pickupDriver->budget,
            'tanggal_pembuatan' => now(),
            'status_text' => 'Selesai, Driver Ready',
            'status_apply' => $pickupDriver->status_apply,
            'tipe' => $detailTipe,
            'lokasi' => [],
            'tanggal' => [],
            'waktu' => [],
            'detail' => [],
            'log_text' => 'KM: ' . ($request->KM_awal ?? '-') . ' KM → ' . ($request->KM_akhir ?? '-') . ' KM',
        ];

        $this->sendTelegramNotification($telegramPayload);

        return response()->json([
            'success' => true,
            'message' => 'Waktu kepulangan dan data KM berhasil disimpan.',
            'data' => $pickupDriver->load(['karyawan', 'detailPickupDriver']),
        ]);
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
        $driver = karyawan::findOrFail($driverId);
        $recipients = [];

        if ($creatorKaryawan->jabatan == 'HRD') {
            $CS = karyawan::where('jabatan', 'Customer Care')->first();
            if ($CS) {
                $recipients[] = $CS->kode_karyawan;
            }
            $recipients[] = $driver->kode_karyawan;
        } elseif ($creatorKaryawan->jabatan == 'Customer Care') {
            $HRD = karyawan::where('jabatan', 'HRD')->first();
            if ($HRD) {
                $recipients[] = $HRD->kode_karyawan;
            }
            $recipients[] = $driver->kode_karyawan;
        }

        $users = User::whereHas('karyawan', fn($q) => $q->whereIn('kode_karyawan', $recipients))->get();

        foreach ($users as $user) {
            NotificationFacade::send(
                $user,
                new KoordinasiDriverNotifcation(
                    [
                        'id_karyawan' => $driverId,
                        'tipe' => $detailTipe,
                        'tanggal_pembuatan' => now(),
                        'id_pengajuan' => $data->id,
                    ],
                    '/office/pickup-driver/index',
                    'Update Koordinasi Driver',
                    $user->id,
                ),
            );
        }

        $telegramPayload = [
            'title' => '🗑️ Data Dihapus',
            'id_pengajuan' => $data->id,
            'creator_name' => $creatorKaryawan->nama_lengkap,
            'driver_name' => $driver->nama ?? ($driver->nama_lengkap ?? '-'),
            'budget' => $data->budget,
            'tanggal_pembuatan' => now(),
            'status_text' => 'Dihapus dari Sistem',
            'status_apply' => $data->status_apply,
            'tipe' => $detailTipe,
            'lokasi' => [],
            'tanggal' => [],
            'waktu' => [],
            'detail' => [],
            'log_text' => null,
            'state' => 'delete',
        ];

        $this->sendTelegramNotification($telegramPayload);

        $data->delete();
        return response()->json(['message' => 'Data berhasil dihapus']);
    }

    public function updateKoordinasi(Request $request)
    {
        $request->validate([
            'pickup_driver_id' => 'required|exists:pickup_drivers,id',
            'id_driver' => 'required|exists:karyawans,id',
            'kendaraan' => 'nullable|string|max:255',
            'budget_value' => 'nullable|numeric|min:0',
            'details' => 'required|array|min:1',
            'details.*.tipe' => 'required|in:Penjemputan,Pengantaran',
            'details.*.lokasi' => 'required|string|max:255',
            'details.*.tanggal' => 'required|date',
            'details.*.waktu' => 'required|date_format:H:i',
        ]);

        $pickup = pickupDriver::with('detailPickupDriver', 'karyawan')->findOrFail($request->pickup_driver_id);

        $oldData = [
            'id_karyawan' => $pickup->id_karyawan,
            'kendaraan' => $pickup->kendaraan,
            'budget' => $pickup->budget,
            'details' => $pickup->detailPickupDriver
                ->map(
                    fn($d) => [
                        'tipe' => $d->tipe,
                        'lokasi' => $d->lokasi,
                        'tanggal' => $d->tanggal_keberangkatan,
                        'waktu' => substr($d->waktu_keberangkatan, 0, 5),
                    ],
                )
                ->all(),
        ];

        $changes = [];

        if ($pickup->id_karyawan != $request->id_driver) {
            $oldDriver = $pickup->karyawan?->nama_lengkap ?? 'Tidak diketahui';
            $newDriver = karyawan::find($request->id_driver)?->nama_lengkap ?? 'Tidak diketahui';
            $changes[] = "Driver: {$oldDriver} → {$newDriver}";
        }

        $oldKendaraan = $pickup->kendaraan ?? 'tidak ada';
        $newKendaraan = $request->filled('kendaraan') ? $request->kendaraan : null;
        $newKendaraanDisplay = $newKendaraan ?? 'tidak ada';

        if ($oldKendaraan !== $newKendaraan) {
            $changes[] = "Kendaraan: {$oldKendaraan} → {$newKendaraanDisplay}";
        }

        $newBudget = $request->filled('budget_value') ? (float) $request->budget_value : null;
        $oldBudgetDisplay = $pickup->budget ? 'Rp ' . number_format($pickup->budget, 0, ',', '.') : 'tidak ada';
        $newBudgetDisplay = $newBudget ? 'Rp ' . number_format($newBudget, 0, ',', '.') : 'tidak ada';

        if ($pickup->budget !== $newBudget) {
            $changes[] = "Budget: {$oldBudgetDisplay} → {$newBudgetDisplay}";
        }

        $newDetailsKeyed = collect($request->details)->mapWithKeys(
            fn($d, $i) => [
                $i => $d['tipe'] . '|' . $d['lokasi'] . '|' . $d['tanggal'] . '|' . $d['waktu'],
            ],
        );

        $oldDetailsKeyed = collect($oldData['details'])->mapWithKeys(
            fn($d, $i) => [
                $i => $d['tipe'] . '|' . $d['lokasi'] . '|' . $d['tanggal'] . '|' . $d['waktu'],
            ],
        );

        $deleted = $oldDetailsKeyed->diff($newDetailsKeyed)->map(fn($v, $k) => "Dihapus: {$oldData['details'][$k]['tipe']} ke {$oldData['details'][$k]['lokasi']} ({$oldData['details'][$k]['tanggal']} {$oldData['details'][$k]['waktu']})")->all();

        $added = $newDetailsKeyed->diff($oldDetailsKeyed)->map(fn($v, $k) => "Ditambah: {$request->details[$k]['tipe']} ke {$request->details[$k]['lokasi']} ({$request->details[$k]['tanggal']} {$request->details[$k]['waktu']})")->all();

        if (empty($changes) && empty($deleted) && empty($added)) {
            return response()->json(['success' => true, 'message' => 'Tidak ada perubahan.']);
        }

        $pickup->id_karyawan = $request->id_driver;
        $pickup->budget = $newBudget;
        $pickup->kendaraan = $newKendaraan;
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
        $logParts = array_merge($changes, $deleted, $added);

        if (!empty($logParts)) {
            TrackingPickupDriver::create([
                'pickup_driver_id' => $pickup->id,
                'status' => $user . ' memperbarui: ' . implode('; ', $logParts),
                'diubah_oleh' => auth()->user()->id,
            ]);
        }

        $creator = auth()->user();
        $creatorKaryawan = $creator->karyawan;
        $driver = karyawan::findOrFail($request->id_driver);
        $recipients = [];

        if ($creatorKaryawan?->jabatan == 'HRD') {
            $CS = karyawan::where('jabatan', 'Customer Care')->first();
            if ($CS) {
                $recipients[] = $CS->kode_karyawan;
            }
            $recipients[] = $driver->kode_karyawan;
        } elseif ($creatorKaryawan?->jabatan == 'Customer Care') {
            $HRD = karyawan::where('jabatan', 'HRD')->first();
            if ($HRD) {
                $recipients[] = $HRD->kode_karyawan;
            }
            $recipients[] = $driver->kode_karyawan;
        }

        $users = User::whereHas('karyawan', fn($q) => $q->whereIn('kode_karyawan', $recipients))->get();
        $detailTipe = collect($request->details)->pluck('tipe')->toArray();

        foreach ($users as $user) {
            NotificationFacade::send(
                $user,
                new KoordinasiDriverNotifcation(
                    [
                        'id_karyawan' => $request->id_driver,
                        'tipe' => $detailTipe,
                        'tanggal_pembuatan' => now(),
                        'id_pengajuan' => $pickup->id,
                    ],
                    '/office/pickup-driver/index',
                    'Update Koordinasi Driver',
                    $user->id,
                ),
            );
        }

        $telegramPayload = [
            'title' => '✏️ Koordinasi Diperbarui',
            'id_pengajuan' => $pickup->id,
            'creator_name' => $creatorKaryawan?->nama_lengkap ?? '-',
            'driver_name' => $driver->nama ?? ($driver->nama_lengkap ?? '-'),
            'budget' => $newBudget,
            'tanggal_pembuatan' => now(),
            'status_text' => 'Diperbarui',
            'status_apply' => $pickup->status_apply,
            'tipe' => $detailTipe,
            'lokasi' => collect($request->details)->pluck('lokasi')->toArray(),
            'tanggal' => collect($request->details)->pluck('tanggal')->toArray(),
            'waktu' => collect($request->details)->pluck('waktu')->toArray(),
            'detail' => collect($request->details)->pluck('detail')->toArray(),
            'log_text' => implode("\n", $logParts),
        ];

        $this->sendTelegramNotification($telegramPayload);

        return response()->json(['success' => true, 'message' => 'Koordinasi berhasil diperbarui.']);
    }

    private function processTerimaFromTelegram(int $id): array
    {
        $pickupDriver = pickupDriver::with('detailPickupDriver')->findOrFail($id);

        if ($pickupDriver->status_apply != 0) {
            return ['success' => false, 'message' => '⚠️ Status koordinasi sudah berubah.'];
        }

        $detail = $pickupDriver->detailPickupDriver->first();

        if (!$detail) {
            return ['success' => false, 'message' => 'Detail pickup tidak ditemukan.'];
        }

        if ($detail->tipe === 'Penjemputan') {
            $pickupDriver->status_driver = 'Sedang Menjemput';
            $statusDriver = 'Sedang Menjemput';
        } elseif ($detail->tipe === 'Pengantaran') {
            $pickupDriver->status_driver = 'Sedang Mengantarkan';
            $statusDriver = 'Sedang Mengantarkan';
        } else {
            $pickupDriver->status_driver = 'Diterima';
            $statusDriver = 'Diterima';
        }

        $pickupDriver->status_apply = 1;
        $pickupDriver->save();

        $driver = karyawan::find($pickupDriver->id_karyawan);
        $detailTipe = $pickupDriver->detailPickupDriver->pluck('tipe')->toArray();

        TrackingPickupDriver::create([
            'pickup_driver_id' => $pickupDriver->id,
            'status' => 'Koordinasi diterima melalui Telegram, status menjadi ' . $statusDriver . ' dengan kendaraan ' . ($pickupDriver->kendaraan ?? '-'),
            'diubah_oleh' => $driver->id,
        ]);

        $this->sendTelegramNotification([
            'title' => '🔄 Status Diperbarui',
            'id_pengajuan' => $pickupDriver->id,
            'creator_name' => 'Telegram Bot',
            'driver_name' => $driver->nama_lengkap ?? '-',
            'budget' => $pickupDriver->budget,
            'tanggal_pembuatan' => now(),
            'status_text' => $statusDriver,
            'status_apply' => $pickupDriver->status_apply,
            'tipe' => $detailTipe,
            'lokasi' => [],
            'tanggal' => [],
            'waktu' => [],
            'detail' => [],
            'log_text' => null,
        ]);

        return ['success' => true, 'message' => '✅ Koordinasi berhasil diterima!'];
    }

    private function getSummaryMessage(int $id): array
    {
        $pickupDriver = pickupDriver::with(['karyawan', 'pembuat', 'detailPickupDriver'])->findOrFail($id);
        
        $coordinationData = [
            'id_pengajuan' => $pickupDriver->id,
            'creator_name' => $pickupDriver->pembuat?->nama_lengkap ?? '-',
            'driver_name' => $pickupDriver->karyawan?->nama_lengkap ?? '-',
            'budget' => $pickupDriver->budget,
            'tanggal_pembuatan' => $pickupDriver->created_at,
            'status_text' => $pickupDriver->status_driver ?? 'Menunggu',
            'status_apply' => $pickupDriver->status_apply,
            'tipe' => $pickupDriver->detailPickupDriver->pluck('tipe')->toArray(),
            'lokasi' => $pickupDriver->detailPickupDriver->pluck('lokasi')->toArray(),
            'tanggal' => $pickupDriver->detailPickupDriver->pluck('tanggal_keberangkatan')->toArray(),
            'waktu' => $pickupDriver->detailPickupDriver->pluck('waktu_keberangkatan')->toArray(),
        ];

        $formatted = $this->formatCoordinationMessage($coordinationData);
        $formatted['id'] = $id;
        
        return $formatted;
    }

    private function getSummaryTextAndButtons(int $id): array
    {
        $pickupDriver = pickupDriver::with(['karyawan', 'pembuat', 'detailPickupDriver'])->findOrFail($id);

        $budgetText = $pickupDriver->budget ? 'Rp ' . number_format($pickupDriver->budget, 0, ',', '.') : 'Tidak Ada Budget';
        $time = \Carbon\Carbon::parse($pickupDriver->created_at)->format('d M Y, H:i');
        $status = $pickupDriver->status_driver ?? 'Menunggu';

        $detailsText = '';
        $tipes = $pickupDriver->detailPickupDriver->pluck('tipe')->toArray();
        $lokasis = $pickupDriver->detailPickupDriver->pluck('lokasi')->toArray();
        $tanggals = $pickupDriver->detailPickupDriver->pluck('tanggal_keberangkatan')->map(fn($t) => \Carbon\Carbon::parse($t)->format('d M Y'))->toArray();
        $waktus = $pickupDriver->detailPickupDriver->pluck('waktu_keberangkatan')->map(fn($t) => substr($t, 0, 5))->toArray();

        if (count($tipes) > 0) {
            foreach ($tipes as $i => $tipe) {
                $lokasi = $lokasis[$i] ?? '-';
                $tanggal = $tanggals[$i] ?? '-';
                $waktu = $waktus[$i] ?? '-';
                $detailsText .= "• <b>{$tipe}</b>\n  {$lokasi}\n  {$tanggal} | {$waktu}\n\n";
            }
        }

        $messageBody = "ID: <code>#{$pickupDriver->id}</code>\n" .
                       "Dibuat: " . ($pickupDriver->pembuat?->nama_lengkap ?? '-') . "\n" .
                       "Driver: " . ($pickupDriver->karyawan?->nama_lengkap ?? '-') . "\n" .
                       "Budget: {$budgetText}\n" .
                       "Waktu: {$time}\n" .
                       "Status: {$status}\n" .
                       "──────────────\n" .
                       ($detailsText ? "<b>Rincian Perjalanan:</b>\n{$detailsText}" : '');

        $buttons = [
            [['text' => '🔍 Lihat Detail', 'callback_data' => "detail_{$pickupDriver->id}"]]
        ];

        if ($pickupDriver->status_apply == 0) {
            $buttons[] = [['text' => '✅ Terima', 'callback_data' => "terima_{$pickupDriver->id}"]];
        } else {
            $buttons[] = [['text' => '✅ Sudah Diterima', 'callback_data' => "sudah_terima_{$pickupDriver->id}"]];
        }

        return [
            'text' => "<b>🆕 Koordinasi Driver</b>\n──────────────\n" . $messageBody,
            'buttons' => $buttons
        ];
    }
    private function getDetailMessage(int $id): string
    {
        $pickupDriver = pickupDriver::with([
            'karyawan', 'pembuat', 'detailPickupDriver', 'biayaTransportasi', 'Tracking'
        ])->findOrFail($id);

        $uangKepakai = $pickupDriver->biayaTransportasi->sum('harga') ?? 0;
        $sisaBudget = ($pickupDriver->budget ?? 0) - $uangKepakai;

        $lines = [];
        $lines[] = "<b>DETAIL LENGKAP #{$pickupDriver->id}</b>";
        $lines[] = "──────────────";
        $lines[] = "<b>Driver:</b> " . ($pickupDriver->karyawan?->nama_lengkap ?? '-');
        $lines[] = "<b>Pembuat:</b> " . ($pickupDriver->pembuat?->nama_lengkap ?? '-');
        $lines[] = "<b>Kendaraan:</b> " . ($pickupDriver->kendaraan ?? 'Belum dipilih');
        $lines[] = "<b>Status:</b> " . ($pickupDriver->status_driver ?? '-');
        $lines[] = "<b>Budget:</b> " . ($pickupDriver->budget ? 'Rp ' . number_format($pickupDriver->budget, 0, ',', '.') : 'Tidak Ada');
        $lines[] = "<b>Terpakai:</b> " . ($uangKepakai > 0 ? 'Rp ' . number_format($uangKepakai, 0, ',', '.') : 'Rp 0');
        
        if ($pickupDriver->budget) {
            $sisaText = $sisaBudget < 0 ? '<b>Rp ' . number_format($sisaBudget, 0, ',', '.') . '</b>' : 'Rp ' . number_format($sisaBudget, 0, ',', '.');
            $lines[] = "<b>Sisa:</b> " . $sisaText;
        }

        if ($pickupDriver->KM_awal || $pickupDriver->KM_akhir) {
            $lines[] = "<b>KM:</b> " . ($pickupDriver->KM_awal ?? '-') . " → " . ($pickupDriver->KM_akhir ?? '-');
        }

        $lines[] = "──────────────";
        $lines[] = "<b>Rincian Rute:</b>";
        
        if ($pickupDriver->detailPickupDriver->isNotEmpty()) {
            foreach ($pickupDriver->detailPickupDriver as $index => $d) {
                $tanggal = \Carbon\Carbon::parse($d->tanggal_keberangkatan)->format('d M Y');
                $waktu = substr($d->waktu_keberangkatan, 0, 5);
                $lines[] = ($index + 1) . ". <b>{$d->tipe}</b>: {$d->lokasi}";
                $lines[] = "   {$tanggal} | {$waktu}";
            }
        } else {
            $lines[] = "   <i>Tidak ada detail rute.</i>";
        }

        return implode("\n", $lines);
    }

    private function sendTelegramNotification(array $coordinationData): void
    {
        $formatted = $this->formatCoordinationMessage($coordinationData);
        $this->sendTelegramMessage($formatted);
    }

    private function formatCoordinationMessage(array $coordinationData): array
    {
        $id = $coordinationData['id_pengajuan'] ?? '-';
        $creator = $coordinationData['creator_name'] ?? '-';
        $driver = $coordinationData['driver_name'] ?? '-';
        $budget = isset($coordinationData['budget']) && $coordinationData['budget'] ? 'Rp ' . number_format($coordinationData['budget'], 0, ',', '.') : 'Tidak Ada Budget';
        $time = isset($coordinationData['tanggal_pembuatan']) ? \Carbon\Carbon::parse($coordinationData['tanggal_pembuatan'])->format('d M Y, H:i') : '-';
        $status = $coordinationData['status_text'] ?? '-';
        $statusApply = $coordinationData['status_apply'] ?? 0;
        $logText = $coordinationData['log_text'] ?? null;
        $state = $coordinationData['state'] ?? null;

        $detailsText = '';
        if ($logText) {
            $detailsText = $logText;
        } else {
            $tipes = $coordinationData['tipe'] ?? [];
            $lokasis = $coordinationData['lokasi'] ?? [];
            $tanggals = $coordinationData['tanggal'] ?? [];
            $waktus = $coordinationData['waktu'] ?? [];
            $details = $coordinationData['detail'] ?? [];

            if (is_array($tipes) && count($tipes) > 0) {
                foreach ($tipes as $i => $tipe) {
                    $lokasi = $lokasis[$i] ?? '-';
                    $tanggal = $tanggals[$i] ?? '-';
                    $waktu = $waktus[$i] ?? '-';
                    $info = $details[$i] ?? '-';
                    $detailsText .= "• <b>{$tipe}</b>\n  {$lokasi}\n  {$tanggal} | {$waktu}\n  {$info}\n\n";
                }
            }
        }

        $message = "ID: <code>#{$id}</code>\n" . "Dibuat: {$creator}\n" . "Driver: {$driver}\n" . "Budget: {$budget}\n" . "Waktu: {$time}\n" . "Status: {$status}\n" . "──────────────\n" . ($detailsText ? "<b>Rincian Perjalanan:</b>\n{$detailsText}" : '');

        $buttons = [['text' => '🔍 Lihat Detail', 'callback_data' => "detail_{$id}"]];

        if ($statusApply == 0 && !$state) {
            $buttons[] = ['text' => '✅ Terima', 'callback_data' => "terima_{$id}"];
        }

        if ($statusApply == 1) {
            $buttons[] = ['text' => '🏁 Selesaikan', 'callback_data' => "selesaikan_{$id}"];
        }

        return [
            'title' => $coordinationData['title'] ?? '🆕 Koordinasi Driver',
            'message' => $message,
            'buttons' => $buttons,
            'id' => $id,
            'status_apply' => $statusApply,
        ];
    }

    private function startKepulanganFlow(int $userId, int $pickupId, int $chatId): void
    {
        Cache::put("telegram_kepulangan_{$userId}", [
            'step' => 'km_awal',
            'pickup_id' => $pickupId,
            'chat_id' => $chatId,
        ], now()->addMinutes(10));

        $this->sendTelegramMessageToChat($chatId, [
            'title' => '🏁 Input Kepulangan',
            'message' => "Silakan <b>reply pesan ini</b> dan ketik <b>KM Awal</b> kendaraan.\n\n<i>Contoh: 12000</i>",
            'buttons' => [['text' => '❌ Batalkan', 'callback_data' => "batal_kepulangan"]]
        ]);
    }

    private function processKepulanganInput(int $userId, string $text, int $chatId): void
    {
        $state = Cache::get("telegram_kepulangan_{$userId}");
        
        if (!$state) {
            return;
        }

        if (!is_numeric($text)) {
            $this->sendTelegramMessageToChat($chatId, [
                'title' => '⚠️ Input Tidak Valid',
                'message' => "Mohon <b>reply pesan ini</b> dengan <b>angka saja</b>.\n\n<i>Contoh: 12000</i>",
            ]);
            return;
        }

        $kmInput = (int) $text;
        $pickupId = $state['pickup_id'];

        if ($state['step'] === 'km_awal') {
            Cache::put("telegram_kepulangan_{$userId}", [
                'step' => 'km_akhir',
                'pickup_id' => $pickupId,
                'chat_id' => $chatId,
                'km_awal' => $kmInput,
            ], now()->addMinutes(10));

            $this->sendTelegramMessageToChat($chatId, [
                'title' => '🏁 Input KM Akhir',
                'message' => "KM Awal tersimpan: <b>{$kmInput}</b>\n\nSekarang <b>reply pesan ini</b> dan ketik <b>KM Akhir</b>.\n\n<i>Contoh: 12050</i>",
                'buttons' => [['text' => '❌ Batalkan', 'callback_data' => "batal_kepulangan"]]
            ]);

        } elseif ($state['step'] === 'km_akhir') {
            $kmAwal = $state['km_awal'];
            $kmAkhir = $kmInput;

            if ($kmAkhir < $kmAwal) {
                $this->sendTelegramMessageToChat($chatId, [
                    'title' => '⚠️ Validasi Gagal',
                    'message' => "KM Akhir (<b>{$kmAkhir}</b>) tidak boleh lebih kecil dari KM Awal (<b>{$kmAwal}</b>).\n\nSilakan <b>reply pesan ini</b> dan ketik ulang KM Akhir.",
                ]);
                return;
            }

            $pickup = pickupDriver::findOrFail($pickupId);
            $jarak = $kmAkhir - $kmAwal;
            
            $vehicleConfig = [
                'Innova' => ['fuelPrice' => 14500, 'kmPerLiter' => 8],
                'H1' => ['fuelPrice' => 12500, 'kmPerLiter' => 5],
            ];
            
            $vehicleType = $pickup->kendaraan ?? 'Innova';
            $config = $vehicleConfig[$vehicleType] ?? $vehicleConfig['Innova'];
            $ratePerKm = $config['fuelPrice'] / $config['kmPerLiter'];

            Cache::put("telegram_kepulangan_{$userId}", [
                'step' => 'konfirmasi',
                'pickup_id' => $pickupId,
                'chat_id' => $chatId,
                'km_awal' => $kmAwal,
                'km_akhir' => $kmAkhir,
            ], now()->addMinutes(10));

            $this->sendTelegramMessageToChat($chatId, [
                'title' => '✅ Konfirmasi Kepulangan',
                'message' => "KM Awal: <b>{$kmAwal}</b>\n" .
                             "KM Akhir: <b>{$kmAkhir}</b>\n" .
                             "Jarak: <b>{$jarak} KM</b>\n" .
                             "Apakah data sudah benar?",
                'buttons' => [
                    ['text' => '✅ Ya, Simpan', 'callback_data' => "konfirmasi_kepulangan"],
                    ['text' => '❌ Batalkan', 'callback_data' => "batal_kepulangan"]
                ]
            ]);
        }
    }

    private function saveKepulangan(int $userId, int $chatId): void
    {
        $state = Cache::get("telegram_kepulangan_{$userId}");

        if (!$state || $state['step'] !== 'konfirmasi') {
            return;
        }

        $pickup = pickupDriver::findOrFail($state['pickup_id']);

        $waktuKepulangan = now()->format('H:i:s');

        $pickup->waktu_kepulangan = $waktuKepulangan;
        $pickup->status_apply = 2;
        $pickup->status_driver = 'Selesai, Driver Ready';
        $pickup->KM_awal = $state['km_awal'];
        $pickup->KM_akhir = $state['km_akhir'];
        $pickup->save();

        Cache::forget("telegram_kepulangan_{$userId}");

        $this->sendTelegramMessageToChat($chatId, [
            'title' => '✅ Koordinasi Selesai',
            'message' => "Data kepulangan berhasil disimpan:\n\n" .
                "KM Awal : <b>{$pickup->KM_awal}</b>\n" .
                "KM Akhir : <b>{$pickup->KM_akhir}</b>\n" .
                "Waktu Diterima : <b>{$pickup->created_at}</b>\n" .
                "Waktu Kepulangan : <b>{$pickup->waktu_kepulangan}</b>\n",
        ]);
    }

    private function sendTelegramMessageToChat(int $chatId, array $data): bool
    {
        try {
            $title = $data['title'] ?? 'Notifikasi';
            $message = $data['message'] ?? '';
            $buttons = $data['buttons'] ?? [];

            $text = "<b>{$title}</b>\n";
            $text .= "──────────────\n";
            $text .= $message;

            $payload = [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ];

            if (!empty($buttons)) {
                $keyboard = array_map(function ($btn) {
                    return ['text' => $btn['text'], 'callback_data' => $btn['callback_data'] ?? ''];
                }, $buttons);

                $payload['reply_markup'] = json_encode([
                    'inline_keyboard' => [$keyboard],
                ]);
            }

            $response = Http::timeout(10)->post('https://api.telegram.org/bot' . self::BOT_TOKEN . '/sendMessage', $payload);

            if ($response->successful()) {
                Log::info('Telegram message sent to chat', ['chat_id' => $chatId]);
                return true;
            }

            Log::error('Gagal mengirim Telegram message', [
                'response' => $response->body(),
                'payload' => $payload,
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Telegram send error: ' . $e->getMessage());
            return false;
        }
    }

    private function sendTelegramMessage(array $data): bool
    {
        try {
            $title = $data['title'] ?? 'Notifikasi';
            $message = $data['message'] ?? '';
            $buttons = $data['buttons'] ?? [];

            $text = "<b>{$title}</b>\n";
            $text .= "──────────────\n";
            $text .= strip_tags($message);

            $payload = [
                'chat_id' => self::CHAT_ID,
                'text' => $text,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ];

            if (!empty($buttons)) {
                $keyboard = array_map(function ($btn) {
                    $button = [
                        'text' => $btn['text'],
                    ];

                    if (isset($btn['url'])) {
                        $button['url'] = $btn['url'];
                    }

                    if (isset($btn['callback_data'])) {
                        $button['callback_data'] = $btn['callback_data'];
                    }

                    return $button;
                }, $buttons);

                $payload['reply_markup'] = json_encode([
                    'inline_keyboard' => [$keyboard],
                ]);
            }

            $response = Http::timeout(10)->post('https://api.telegram.org/bot' . self::BOT_TOKEN . '/sendMessage', $payload);

            if ($response->successful()) {
                Log::info('Telegram Pickup Driver terkirim', ['chat_id' => self::CHAT_ID]);
                return true;
            }

            Log::error('Gagal mengirim Telegram Pickup Driver', [
                'response' => $response->body(),
                'payload' => $payload,
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Telegram Pickup Driver send error: ' . $e->getMessage());
            return false;
        }
    }

    public function webhook(Request $request)
    {
        Log::info('Webhook Pickup Driver Incoming Request:', $request->all());

        if (isset($request['message']['text'])) {
            $message = $request['message'];
            $chatId = $message['chat']['id'];
            $userId = $message['from']['id'];
            $text = trim($message['text']);
            
            $isReplyToBot = isset($message['reply_to_message']['from']['id']) && 
                           $message['reply_to_message']['from']['id'] == $request['message']['from']['id'];
            
            $isReplyToKmRequest = isset($message['reply_to_message']['text']) && 
                                  (str_contains($message['reply_to_message']['text'], 'KM Awal') || 
                                   str_contains($message['reply_to_message']['text'], 'KM Akhir'));

            if (Cache::has("telegram_kepulangan_{$userId}") && $isReplyToKmRequest) {
                $this->processKepulanganInput($userId, $text, $chatId);
                return response()->json(['success' => true]);
            }
        }

        // Handle callback query (tombol)
        if (!isset($request['callback_query'])) {
            return response()->json(['success' => true]);
        }

        $callback = $request['callback_query'];
        $callbackId = $callback['id'];
        $data = $callback['data'];
        $userId = $callback['from']['id'];
        
        $messageId = $callback['message']['message_id'] ?? null;
        $chatId = $callback['message']['chat']['id'] ?? self::CHAT_ID;

        try {
            if (str_starts_with($data, 'terima_')) {
                $id = (int) str_replace('terima_', '', $data);
                $result = $this->processTerimaFromTelegram($id);
                
                Http::post('https://api.telegram.org/bot' . self::BOT_TOKEN . '/answerCallbackQuery', [
                    'callback_query_id' => $callbackId,
                    'text' => $result['message'],
                ]);

            } elseif (str_starts_with($data, 'detail_')) {
                $id = (int) str_replace('detail_', '', $data);
                
                Http::post('https://api.telegram.org/bot' . self::BOT_TOKEN . '/answerCallbackQuery', [
                    'callback_query_id' => $callbackId,
                ]);

                $detailText = $this->getDetailMessage($id);
                
                Http::post('https://api.telegram.org/bot' . self::BOT_TOKEN . '/editMessageText', [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'text' => $detailText,
                    'parse_mode' => 'HTML',
                    'reply_markup' => json_encode([
                        'inline_keyboard' => [
                            [['text' => '⬅️ Kembali ke Ringkasan', 'callback_data' => "back_{$id}"]]
                        ]
                    ])
                ]);

            } elseif (str_starts_with($data, 'back_')) {
                $id = (int) str_replace('back_', '', $data);
                
                Http::post('https://api.telegram.org/bot' . self::BOT_TOKEN . '/answerCallbackQuery', [
                    'callback_query_id' => $callbackId,
                ]);

                $summary = $this->getSummaryTextAndButtons($id);

                Http::post('https://api.telegram.org/bot' . self::BOT_TOKEN . '/editMessageText', [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'text' => $summary['text'],
                    'parse_mode' => 'HTML',
                    'reply_markup' => json_encode([
                        'inline_keyboard' => $summary['buttons']
                    ])
                ]);

            } elseif (str_starts_with($data, 'selesaikan_')) {
                $id = (int) str_replace('selesaikan_', '', $data);
                
                Http::post('https://api.telegram.org/bot' . self::BOT_TOKEN . '/answerCallbackQuery', [
                    'callback_query_id' => $callbackId,
                ]);

                $this->startKepulanganFlow($userId, $id, $chatId);

            } elseif ($data === 'konfirmasi_kepulangan') {
                Http::post('https://api.telegram.org/bot' . self::BOT_TOKEN . '/answerCallbackQuery', [
                    'callback_query_id' => $callbackId,
                    'text' => '✅ Data disimpan!',
                ]);

                $this->saveKepulangan($userId, $chatId);

            } elseif ($data === 'batal_kepulangan') {
                Cache::forget("telegram_kepulangan_{$userId}");
                
                Http::post('https://api.telegram.org/bot' . self::BOT_TOKEN . '/answerCallbackQuery', [
                    'callback_query_id' => $callbackId,
                    'text' => '❌ Dibatalkan',
                ]);
            }

        } catch (\Throwable $e) {
            Log::error('Gagal memproses callback Pickup Driver', ['message' => $e->getMessage()]);
            
            Http::post('https://api.telegram.org/bot' . self::BOT_TOKEN . '/answerCallbackQuery', [
                'callback_query_id' => $callbackId,
                'text' => '❌ Gagal memproses aksi.',
                'show_alert' => true,
            ]);
        }

        return response()->json(['success' => true]);
    }
    private function telegramResponse($message)
    {
        if (request()->has('from_telegram') || str_contains(request()->header('User-Agent') ?? '', 'Telegram')) {
            return response($message, 200, ['Content-Type' => 'text/plain']);
        }

        return redirect()->route('office.pickupDriver.index')->with('success', $message);
    }

    public function exportExcel(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'kendaraan' => 'nullable|string',
            'status' => 'nullable|integer|in:0,1,2',
        ]);

        $filename = 'Laporan_PickupDriver_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new PickupDriverReportExport($request->start_date, $request->end_date, $request->kendaraan, $request->status), $filename);
    }

    public function exportPdf(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'kendaraan' => 'nullable|string',
            'status' => 'nullable|integer|in:0,1,2',
        ]);

        $query = pickupDriver::with([
            'karyawan',
            'pembuat',
            'detailPickupDriver',
            'Tracking',
            'biayaTransportasi' => function ($q) {
                $q->with('karyawan');
            },
        ]);

        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->kendaraan) {
            $query->where('kendaraan', $request->kendaraan);
        }

        if ($request->status !== null) {
            $query->where('status_apply', $request->status);
        }

        $data = $query->orderBy('created_at', 'desc')->get();

        $filterStatusText = match ($request->status) {
            0 => 'Menunggu',
            1 => 'Diterima',
            2 => 'Selesai',
            default => 'Semua',
        };

        $user = Auth::check() ? optional(Auth::user()->karyawan)->nama_lengkap ?? Auth::user()->username : 'System';

        $pdf = Pdf::loadView('office.reports.pickup_driver_pdf', [
            'data' => $data,
            'startDate' => $request->start_date,
            'endDate' => $request->end_date,
            'filterKendaraan' => $request->kendaraan,
            'filterStatusText' => $filterStatusText,
            'user' => $user,
            'generatedAt' => Carbon::now()->format('d M Y H:i:s'),
        ]);

        $pdf->setPaper('A4', 'landscape')->setOption('defaultFont', 'DejaVu Sans')->setOption('isHtml5ParserEnabled', true)->setOption('isRemoteEnabled', true);

        return $pdf->stream('Laporan_PickupDriver_' . date('Y-m-d_His') . '.pdf');
    }
}
