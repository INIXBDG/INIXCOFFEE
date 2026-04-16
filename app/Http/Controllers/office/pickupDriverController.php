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
                $query->whereDoesntHave('pickupDriver')->orWhereHas('pickupDriver', function ($q) {
                    $q->where('status_driver', 'Selesai, Driver Ready');
                });
            })
            ->get();

        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $budgetPerKendaraan = pickupDriver::select('kendaraan')
            ->selectRaw('COALESCE(SUM(biaya_transportasi_drivers.harga), 0) as total_terpakai')
            ->join('biaya_transportasi_drivers', 'pickup_drivers.id', '=', 'biaya_transportasi_drivers.id_pickup_driver')
            ->where('pickup_drivers.tipe_perjalanan', 'Operasional Kantor')
            ->whereHas('detailPickupDriver', function ($q) use ($startOfWeek, $endOfWeek) {
                $q->whereBetween('tanggal_keberangkatan', [$startOfWeek, $endOfWeek]);
            })
            ->groupBy('pickup_drivers.kendaraan')
            ->get()
            ->mapWithKeys(function ($item) {
                $sisa = 1000000 - $item->total_terpakai;
                return [$item->kendaraan => max(0, $sisa)];
            });

        $kendaraanSedangDipakai = pickupDriver::where('status_apply', 1)->whereNotNull('kendaraan')->where('kendaraan', '!=', '')->pluck('kendaraan')->unique();

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

        return view('office.pickupdriver.create', compact('dataDriver', 'budgetPerKendaraan', 'kendaraan', 'extends', 'section'));
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
            $totalTerpakai = pickupDriver::where('kendaraan', $request->kendaraan)
                ->where('tipe_perjalanan', 'Operasional Kantor')
                ->whereHas('detailPickupDriver', function ($q) use ($startOfWeek, $endOfWeek) {
                    $q->whereBetween('tanggal_keberangkatan', [$startOfWeek, $endOfWeek]);
                })
                ->join('biaya_transportasi_drivers', 'pickup_drivers.id', '=', 'biaya_transportasi_drivers.id_pickup_driver')
                ->sum('biaya_transportasi_drivers.harga');

            $sisaBudget = 1000000 - $totalTerpakai;

            if ($budget > $sisaBudget) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => "Budget melebihi batas. Sisa budget untuk {$request->kendaraan} minggu ini: Rp " . number_format($sisaBudget, 0, ',', '.'),
                    ],
                    422,
                );
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
