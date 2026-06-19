<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RegistryFeature;
use App\Models\User;
use App\Models\DailyActivity;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use App\Models\Tickets;
use App\Models\karyawan;
use App\Notifications\RegistryFeatureNotification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class RegistryFeatureController extends Controller
{

    public function index()
    {
        return view('registry.index');
    }

    public function getRegistry()
    {
        $daftar_tugas = RegistryFeature::with([
            'pengerja.karyawan',
        ])->orderBy('created_at', 'desc')->get();

        return response()->json([
            'data' => $daftar_tugas
        ]);
    }

    public function create()
    {
        $users = User::with('karyawan')
                     ->get()
                     ->sortBy('name');

        $permissions = Permission::all();
        $unwantedWords = ['Create', 'Delete', 'View', 'Edit'];

        $permissionFeatures = $permissions->pluck('name')->map(function ($name) use ($unwantedWords) {
            $cleanedName = str_replace($unwantedWords, '', $name);
            return trim($cleanedName);
        })->filter()->unique();

        $registryFeatures = RegistryFeature::whereNotNull('fitur')->pluck('fitur')->unique();

        $features = $permissionFeatures->merge($registryFeatures)->filter()->unique()->sort()->values();

        $jabatans = User::whereNotNull('jabatan')->pluck('jabatan')->unique()->sort()->values();

        $assignedTickets = RegistryFeature::whereNotNull('ticket_id')->pluck('ticket_id');

        $tickets = Tickets::select('ticket_id', 'detail_kendala', 'divisi', 'kategori')
            ->where('keperluan', 'Programming')
            ->whereNotIn('ticket_id', $assignedTickets)
            ->whereBetween('created_at', [
                now()->subWeek()->startOfWeek(),
                now()->endOfWeek()
            ])
            ->get();

        return view('registry.create', compact('users', 'features', 'tickets', 'jabatans'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tugas'           => 'required|string|max:255',
            'fitur'           => 'required|string|max:255',
            'tipe'            => 'required|string|max:100',
            'pengerja_id'     => 'nullable|exists:users,id',
            'catatan'         => 'nullable|string',
            'pemilik'         => 'required|string',
            'fakta'           => 'required|string',
            'harapan'         => 'required|string',
            'waktu_perkiraan' => 'nullable|integer|min:1',
            'ticket_id'       => 'nullable|string|exists:tickets,ticket_id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validatedData = $validator->validated();
        $validatedData['status'] = 'Belum dimulai';

        $tugasBaru = RegistryFeature::create($validatedData);

        $dailyActivity = DailyActivity::create([
            'user_id'     => $tugasBaru->pengerja_id ?? auth()->id(),
            'id_task'     => $tugasBaru->id,
            'activity'    => $tugasBaru->tipe . ' - ' . $tugasBaru->fitur,
            'status'      => 'On Progres',
            'description' => $tugasBaru->tugas,
        ]);

        $tugasBaru->update([
            'daily_activity_id' => $dailyActivity->id
        ]);

        $koordinatorKaryawan = karyawan::where('jabatan', 'Koordinator ITSM')->first();
        if ($koordinatorKaryawan) {
            $userKoordinator = User::where('karyawan_id', $koordinatorKaryawan->id)->first();

            if ($userKoordinator) {
                $to = $koordinatorKaryawan->nama_lengkap;
                $path = route('registry.edit', $tugasBaru->id);
                $type = 'Mengajukan Tugas Baru (Registry Feature)';

                $notifData = [
                    'tanggal'   => now(),
                    'status'    => 'Menunggu Review Estimasi Waktu',
                    'ticket_id' => $tugasBaru->ticket_id ?? '-',
                    'tugas'     => $tugasBaru->tugas,
                    'fitur'     => $tugasBaru->fitur,
                    'tipe'      => $tugasBaru->tipe,
                    'pemilik'   => $tugasBaru->pemilik,
                    'fakta'     => $tugasBaru->fakta,
                    'harapan'   => $tugasBaru->harapan,
                ];

                NotificationFacade::send(
                    $userKoordinator,
                    new RegistryFeatureNotification($notifData, $path, $to, $type, $userKoordinator->id)
                );
            }
        }

        return redirect()->route('registry.index')
                         ->with('success', 'Tugas baru berhasil ditambahkan dan notifikasi review telah dikirim ke Koordinator ITSM.');
    }

    public function edit(RegistryFeature $tugas)
    {
        $users = User::with('karyawan')->get();

        $permissions = Permission::all();
        $unwantedWords = ['Create', 'Delete', 'View', 'Edit'];

        $features = $permissions->pluck('name')->map(function ($name) use ($unwantedWords) {
            $cleanedName = str_replace($unwantedWords, '', $name);
            return trim($cleanedName);
        })->filter()->unique()->sort()->values();

        return view('registry.edit', compact('tugas', 'users', 'features'));
    }

    public function update(Request $request, RegistryFeature $tugas)
    {
        $validator = Validator::make($request->all(), [
            'ticket_id'       => 'nullable|string',
            'tugas'           => 'required|string|max:255',
            'fitur'           => 'required|string|max:255',
            'tipe'            => 'required|string|max:100',
            'pemilik'         => 'required|string',
            'pengerja_id'     => 'nullable|exists:users,id',
            'fakta'           => 'required|string',
            'harapan'         => 'required|string',
            'waktu_perkiraan' => 'nullable|integer|min:1',
            'tanggal_mulai'   => 'nullable|date',
            'tanggal_akhir'   => 'nullable|date|after_or_equal:tanggal_mulai',
            'catatan'         => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validatedData = $validator->validated();
        $tugas->update($validatedData);

        $jabatanPengguna = optional(auth()->user()->karyawan)->jabatan;

        if ($jabatanPengguna === 'Koordinator ITSM' && $tugas->pengerja_id) {

            $to = $tugas->pengerja->karyawan->nama_lengkap ?? $tugas->pengerja->name;
            $path = route('registry.index');
            $type = 'Tugas Telah Direview Koordinator ITSM';

            $notifData = [
                'tanggal'         => now(),
                'status'          => 'Telah direview',
                'ticket_id'       => $tugas->ticket_id ?? '-',
                'tugas'           => $tugas->tugas,
                'fitur'           => $tugas->fitur,
                'tipe'            => $tugas->tipe,
                'pemilik'         => $tugas->pemilik,
                'fakta'           => $tugas->fakta,
                'harapan'         => $tugas->harapan,
                'waktu_perkiraan' => $tugas->waktu_perkiraan,
                'catatan'         => $tugas->catatan,
            ];

            NotificationFacade::send(
                $tugas->pengerja,
                new RegistryFeatureNotification($notifData, $path, $to, $type, $tugas->pengerja->id)
            );
        }

        return redirect()->route('registry.index')
                         ->with('success', 'Tugas berhasil diperbarui.');
    }

    public function startTask(Request $request, RegistryFeature $tugas)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date'
        ]);

        if ($tugas->status != 'Belum dimulai') {
            return redirect()->back()
                             ->with('error', 'Tugas ini tidak dapat dimulai (mungkin sudah berjalan atau selesai).');
        }

        $tugas->update([
            'status' => 'Dalam proses',
            'tanggal_mulai' => $request->tanggal_mulai
        ]);

        // Logika Pembaruan Start Date dan Status pada Daily Activity
        $dailyActivity = null;
        if ($tugas->daily_activity_id) {
            $dailyActivity = DailyActivity::find($tugas->daily_activity_id);
        } else {
            // Cadangan untuk data lama sebelum migrasi
            $dailyActivity = DailyActivity::where('id_task', $tugas->id)->first();
        }

        if ($dailyActivity) {
            $dailyActivity->start_date = $request->tanggal_mulai;
            $dailyActivity->updateStatus('On Progres'); // Memanggil fungsi kustom dari Model

            // Perbarui ID jika menggunakan mekanisme cadangan
            if (is_null($tugas->daily_activity_id)) {
                $tugas->update(['daily_activity_id' => $dailyActivity->id]);
            }
        }

        // Logika Pembaruan Status Ticket
        if ($tugas->ticket_id) {
            $ticket = Tickets::where('id', $tugas->ticket_id)
                                         ->orWhere('ticket_id', $tugas->ticket_id)
                                         ->first();
            if ($ticket) {
                $waktuMulai = \Carbon\Carbon::parse($request->tanggal_mulai);
                $picName = auth()->user()->karyawan->nama_lengkap ?? auth()->user()->name;

                $ticket->update([
                    'penanganan' => 'Sedang Diperbaiki',
                    'status' => 'Di Proses',
                    'tanggal_response' => $waktuMulai->toDateString(),
                    'jam_response' => $waktuMulai->toTimeString(),
                    'pic' => $picName,
                ]);
            }
        }

        return redirect()->route('registry.index')
                         ->with('success', 'Tugas "' . $tugas->tugas . '" telah dimulai dan tiket diterima.');
    }

    public function finishTask(Request $request, RegistryFeature $tugas)
    {
        $request->validate([
            'tanggal_akhir' => 'required|date',
            'kesulitan'     => 'nullable|string',
            'keterangan'    => 'nullable|string',
            'penanganan'    => 'nullable|string',
        ]);

        if (!is_null($tugas->tanggal_akhir)) {
            return redirect()->back()
                             ->with('error', 'Tugas ini sudah memiliki tanggal selesai.');
        }

        if (is_null($tugas->tanggal_mulai)) {
            return redirect()->back()
                             ->with('error', 'Tugas ini belum dimulai, tidak bisa ditandai selesai.');
        }

        $tugas->update([
            'status' => 'Selesai',
            'tanggal_akhir' => $request->tanggal_akhir
        ]);

        $dailyActivity = null;
        if ($tugas->daily_activity_id) {
            $dailyActivity = DailyActivity::find($tugas->daily_activity_id);
        } else {
            $dailyActivity = DailyActivity::where('id_task', $tugas->id)->first();
        }

        if ($dailyActivity) {
            $dailyActivity->end_date = $request->tanggal_akhir;
            $dailyActivity->updateStatus('Selesai');

            if (is_null($tugas->daily_activity_id)) {
                $tugas->update(['daily_activity_id' => $dailyActivity->id]);
            }
        }

        // Logika Pembaruan Ticket dan Webhook Telegram
        if ($tugas->ticket_id) {
            $ticket = Tickets::where('id', $tugas->ticket_id)
                                         ->orWhere('ticket_id', $tugas->ticket_id)
                                         ->first();
            if ($ticket) {
                $waktuAkhir = \Carbon\Carbon::parse($request->tanggal_akhir);

                $ticket->update([
                    'status' => 'Selesai',
                    'tanggal_selesai' => $waktuAkhir->toDateString(),
                    'jam_selesai' => $waktuAkhir->toTimeString(),
                    'tingkat_kesulitan' => $request->kesulitan,
                    'keterangan' => $request->keterangan,
                    'penanganan' => $request->penanganan,
                ]);

                $pembuatTiket = User::whereHas('karyawan', function ($query) use ($ticket) {
                    $query->where('nama_lengkap', $ticket->nama_karyawan);
                })->first();

                if ($pembuatTiket) {
                    \Illuminate\Support\Facades\Notification::send($pembuatTiket, new \App\Notifications\SurveyReminderNotification($ticket));
                }

                try {
                    $picName = $ticket->pic ?? auth()->user()->karyawan->nama_lengkap ?? auth()->user()->name;

                    \Illuminate\Support\Facades\Http::withHeaders([
                        'Accept' => 'application/json',
                        'X-Webhook-Secret' => 'RAHASIA_KITA'
                    ])->post('https://inixindobdg.co.id/api/ticket-status-update', [
                        'action'    => 'finished',
                        'ticket_id' => $ticket->ticket_id,
                        'pic'       => $picName,
                        'keterangan'=> $request->keterangan,
                        'status'    => $ticket->status
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Gagal kirim update ke Telegram dari Registry: " . $e->getMessage());
                }
            }
        }

        return redirect()->route('registry.index')
                         ->with('success', 'Tugas "' . $tugas->tugas . '" telah ditandai Selesai dan tiket diselesaikan.');
    }

    public function destroy(RegistryFeature $tugas)
    {
        $tugas->delete();

        return redirect()->route('registry.index')
                         ->with('success', 'Tugas berhasil dihapus.');
    }
}

