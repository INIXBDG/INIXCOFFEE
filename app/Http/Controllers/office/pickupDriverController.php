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
use App\Http\Controllers\TelegramController;
use App\Models\outstanding;

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
                $query->whereDoesntHave('pickupDriver')
                    ->orWhereHas('pickupDriver', function ($q) {
                        $q->where('status_driver', 'Selesai, Driver Ready');
                    });
            })
            ->get();

        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $budgetPerjalanan = pickupDriver::select('kendaraan')
            ->selectRaw('COALESCE(SUM(pickup_drivers.budget), 0) as total_budget')
            ->where('tipe_perjalanan', 'Operasional Kantor')
            ->whereHas('detailPickupDriver', function ($q) use ($startOfWeek, $endOfWeek) {
                $q->whereBetween('tanggal_keberangkatan', [$startOfWeek, $endOfWeek]);
            })
            ->groupBy('kendaraan')
            ->get()
            ->map(function ($item) {
                $item->sisa_budget = 1000000 - $item->total_budget;
                return $item;
            });

        $kendaraanSedangDipakai = pickupDriver::where('status_apply', 1)
            ->whereNotNull('kendaraan')
            ->where('kendaraan', '!=', '')
            ->pluck('kendaraan')
            ->unique();

        $allKendaraan = collect(['H1', 'Innova']);

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

        return view('office.pickupdriver.create', compact(
            'dataDriver',
            'budgetPerjalanan',
            'kendaraan',
            'extends',
            'section'
        ));
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

        if (in_array('Operasional Kantor', $request->tipe) && $budget) {
            $total = pickupDriver::where('kendaraan', $request->kendaraan)
                ->where('tipe_perjalanan', 'Operasional Kantor')
                ->whereHas('detailPickupDriver', function ($q) use ($startOfWeek, $endOfWeek) {
                    $q->whereBetween('tanggal_keberangkatan', [$startOfWeek, $endOfWeek]);
                })
                ->sum('budget');

            if ($total + $budget > 1000000) {
                return response()->json(['success' => false, 'message' => 'Budget kendaraan minggu ini sudah melebihi batas'], 422);
            }
        }

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
            'path' => $path,
        ];

        $telegramCtrl = new TelegramController();
        $personalData = $telegramCtrl->formatPersonalCoordinationMessage($telegramData);
        $telegramCtrl->sendPersonalTelegramMessage($personalData);

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
            'path' => '/office/pickup-driver/index',
        ];

        $telegramCtrl = new TelegramController();
        $personalData = $telegramCtrl->formatPersonalCoordinationMessage($telegramPayload);
        $telegramCtrl->sendPersonalTelegramMessage($personalData);

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
            'total_pemakaian' => 'nullable|integer|min:0',
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
            'path' => '/office/pickup-driver/index',
        ];

        $telegramCtrl = new TelegramController();
        $personalData = $telegramCtrl->formatPersonalCoordinationMessage($telegramPayload);
        $telegramCtrl->sendPersonalTelegramMessage($personalData);

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
            'path' => '/office/pickup-driver/index',
        ];

        $telegramCtrl = new TelegramController();
        $personalData = $telegramCtrl->formatPersonalCoordinationMessage($telegramPayload);
        $telegramCtrl->sendPersonalTelegramMessage($personalData);

        $data->delete();
        return response()->json(['message' => 'Data berhasil dihapus']);
    }

    public function updateKoordinasi(Request $request)
    {
        $request->validate([
            'pickup_driver_id' => 'required|exists:pickup_drivers,id',
            'id_driver' => 'required|exists:karyawans,id',
            'kendaraan' => 'nullable',
            'details' => 'required|array|min:1',
            'details.*.tipe' => 'required|in:Penjemputan,Pengantaran',
            'details.*.lokasi' => 'required|string',
            'details.*.tanggal' => 'required|date',
            'details.*.waktu' => 'required',
        ]);

        $pickup = pickupDriver::with('detailPickupDriver', 'karyawan')->findOrFail($request->pickup_driver_id);
        $oldDetails = $pickup->detailPickupDriver
            ->map(
                fn($item) => [
                    'id' => $item->id,
                    'tipe' => $item->tipe,
                    'lokasi' => $item->lokasi,
                    'tanggal' => $item->tanggal_keberangkatan,
                    'waktu' => substr($item->waktu_keberangkatan, 0, 5),
                ],
            )
            ->keyBy('id');

        $changes = [];
        if ($pickup->id_karyawan != $request->id_driver) {
            $oldDriver = $pickup->karyawan->nama_lengkap ?? 'Tidak diketahui';
            $newDriver = karyawan::find($request->id_driver)?->nama_lengkap ?? 'Tidak diketahui';
            $changes[] = "Driver: {$oldDriver} → {$newDriver}";
        }
        if ($pickup->kendaraan != ($request->kendaraan ?? null)) {
            $changes[] = 'Kendaraan: ' . ($pickup->kendaraan ?: 'tidak ada') . ' → ' . ($request->kendaraan ?: 'tidak ada');
        }

        $newDetailsRaw = collect($request->details)->map(
            fn($d) => [
                'tipe' => $d['tipe'],
                'lokasi' => $d['lokasi'],
                'tanggal' => $d['tanggal'],
                'waktu' => $d['waktu'],
            ],
        );

        $deleted = [];
        foreach ($oldDetails as $id => $old) {
            if (!$newDetailsRaw->first(fn($n) => $old['tipe'] === $n['tipe'] && $old['lokasi'] === $n['lokasi'] && $old['tanggal'] == $n['tanggal'] && $old['waktu'] === $n['waktu'])) {
                $deleted[] = "Dihapus: {$old['tipe']} ke {$old['lokasi']} ({$old['tanggal']} {$old['waktu']})";
            }
        }
        $added = [];
        foreach ($newDetailsRaw as $new) {
            if (!$oldDetails->first(fn($o) => $o['tipe'] === $new['tipe'] && $o['lokasi'] === $new['lokasi'] && $o['tanggal'] == $new['tanggal'] && $o['waktu'] === $new['waktu'])) {
                $added[] = "Ditambah: {$new['tipe']} ke {$new['lokasi']} ({$new['tanggal']} {$new['waktu']})";
            }
        }

        if (empty($changes) && empty($deleted) && empty($added)) {
            return response()->json(['success' => true, 'message' => 'Tidak ada perubahan.']);
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
        $logMsg = $user . ' memperbarui: ' . implode('; ', array_merge($changes, $deleted, $added));
        TrackingPickupDriver::create([
            'pickup_driver_id' => $pickup->id,
            'status' => $logMsg,
            'diubah_oleh' => auth()->user()->id,
        ]);

        $creator = auth()->user();
        $creatorKaryawan = $creator->karyawan;
        $driver = karyawan::findOrFail($request->id_driver);
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
            'creator_name' => $creatorKaryawan->nama_lengkap,
            'driver_name' => $driver->nama ?? ($driver->nama_lengkap ?? '-'),
            'budget' => $pickup->budget,
            'tanggal_pembuatan' => now(),
            'status_text' => 'Diperbarui',
            'status_apply' => $pickup->status_apply,
            'tipe' => $detailTipe,
            'lokasi' => [],
            'tanggal' => [],
            'waktu' => [],
            'detail' => [],
            'log_text' => implode("\n", array_merge($changes, $deleted, $added)),
            'path' => '/office/pickup-driver/index',
        ];

        $telegramCtrl = new TelegramController();
        $personalData = $telegramCtrl->formatPersonalCoordinationMessage($telegramPayload);
        $telegramCtrl->sendPersonalTelegramMessage($personalData);

        return response()->json(['success' => true, 'message' => 'Koordinasi berhasil diperbarui.']);
    }

    public function actionTerimaFromTelegramToken(Request $request, $id, $token)
    {
        if (!Cache::has("telegram_action_{$id}_{$token}")) {
            return $this->telegramResponse('❌ Link sudah kadaluarsa atau tidak valid.');
        }

        $payload = Cache::get("telegram_action_{$id}_{$token}");

        Cache::forget("telegram_action_{$id}_{$token}");

        $pickupDriver = pickupDriver::with('detailPickupDriver')->findOrFail($id);

        if ($pickupDriver->status_apply != 0) {
            return $this->telegramResponse('⚠️ Status koordinasi sudah berubah.');
        }

        $detail = $pickupDriver->detailPickupDriver->first();
        $pickupDriver->status_apply = 1;
        $pickupDriver->status_driver = $detail?->tipe === 'Penjemputan' ? 'Sedang Menjemput' : 'Sedang Mengantarkan';
        $pickupDriver->save();

        return $this->telegramResponse('✅ Koordinasi berhasil diterima! Driver sedang bertugas.');
    }

    public function actionSelesaikanFromTelegramToken(Request $request, $id, $token)
    {
        if (!Cache::has("telegram_action_{$id}_{$token}")) {
            return $this->telegramResponse('❌ Link sudah kadaluarsa atau tidak valid.');
        }

        $payload = Cache::get("telegram_action_{$id}_{$token}");
        Cache::forget("telegram_action_{$id}_{$token}");

        $pickupDriver = pickupDriver::findOrFail($id);

        if ($pickupDriver->status_apply != 1) {
            return $this->telegramResponse('⚠️ Koordinasi belum dalam status "Diterima".');
        }

        $pickupDriver->status_apply = 2;
        $pickupDriver->status_driver = 'Selesai, Driver Ready';
        $pickupDriver->waktu_kepulangan = now();
        $pickupDriver->save();

        return $this->telegramResponse('🏁 Koordinasi berhasil diselesaikan! Driver sudah ready.');
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
            'tipe' => 'nullable|string|in:Outstanding PA,Lunas',
            'status' => 'nullable|string',
            'karyawan' => 'nullable|integer',
        ]);

        $query = outstanding::with(['rkm.perusahaan', 'rkm.materi', 'rkm.sales', 'tracking_outstanding']);

        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->tipe === 'Outstanding PA') {
            $query->where(function ($q) {
                $q->whereNotNull('net_sales')
                ->where('net_sales', '!=', 0)
                ->where('net_sales', '!=', '0.00');
            });
        } elseif ($request->tipe === 'Lunas') {
            $query->where('status_pembayaran', 1);
        }

        if ($request->karyawan) {
            $query->whereHas('rkm.sales', function ($q) use ($request) {
                $q->where('id', $request->karyawan);
            });
        }

        $data = $query->orderBy('due_date')
            ->orderBy('created_at', 'desc')
            ->get() ?? collect();

        $title = match ($request->tipe) {
            'Outstanding PA' => 'LAPORAN OUTSTANDING PA',
            'Lunas' => 'LAPORAN OUTSTANDING LUNAS',
            default => 'LAPORAN OUTSTANDING',
        };

        $user = Auth::check()
            ? (optional(Auth::user()->karyawan)->nama_lengkap ?? Auth::user()->username)
            : 'System';

        $pdf = Pdf::loadView('office.reports.outstanding_pdf', [
            'data' => $data ?? collect(),
            'title' => $title,
            'startDate' => $request->start_date,
            'endDate' => $request->end_date,
            'filterTipe' => $request->tipe,
            'user' => $user,
            'generatedAt' => Carbon::now()->format('d M Y H:i:s'),
        ]);

        $pdf->setPaper('A4', 'landscape')
            ->setOption('defaultFont', 'DejaVu Sans')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        return $pdf->stream('Laporan_Outstanding_' . date('Y-m-d_His') . '.pdf');
    }
}
