<?php

namespace App\Http\Controllers;

use App\Models\karyawan;
use App\Models\Tickets;
use App\Models\User;
use App\Notifications\TicketNotification;
use Carbon\Carbon;
use DateTime;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as NotificationFacade;

// use Google_Service_Sheets_ValueRange;
class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['handleInternalUpdate', 'getOpenTickets']);
    }

    private function normalizeTimestamp($timestamp)
    {
        // Jika sudah objek DateTime
        if ($timestamp instanceof \DateTime) {
            return $timestamp->format('n/j/Y H:i:s');
        }

        // Jika string, coba deteksi format terlebih dahulu
        if (is_string($timestamp)) {
            // Cek format dengan regex atau coba parse dulu
            $date = \DateTime::createFromFormat('m/d/Y H:i:s', $timestamp);
            if ($date !== false) {
                return $date->format('n/j/Y H:i:s');
            }
            $date = \DateTime::createFromFormat('Y-m-d H:i:s', $timestamp);
            if ($date !== false) {
                return $date->format('n/j/Y H:i:s');
            }

            // Jika tidak cocok format di atas, coba pakai strtotime
            $time = strtotime($timestamp);
            if ($time !== false) {
                return date('n/j/Y H:i:s', $time);
            }
        }

        // Jika timestamp tidak dikenali, return apa adanya (atau bisa dikasih default)
        return (string) $timestamp;
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'nama_karyawan' => 'required|string',
            'divisi' => 'required|string',
            'kategori' => 'required|string',
            'keperluan' => 'required|string',
            'detail_kendala' => 'required|string',
        ]);

        $ticket = Tickets::create([
            'nama_karyawan' => $request->nama_karyawan,
            'divisi' => $request->divisi,
            'kategori' => $request->kategori,
            'keperluan' => $request->keperluan,
            'detail_kendala' => $request->detail_kendala,
            'timestamp' => $request->datetime,
            // nilai default lainnya tetap
        ]);

        $todayCount = Tickets::whereDate('created_at', today())->count();
        $char = chr(96 + $todayCount); // a untuk tiket pertama, b untuk kedua, dst.
        $ticketId = 'NIX'.now()->format('ymd').$char;
        $ticket->ticket_id = $ticketId;
        $ticket->save();

        $message = "Ada Ticketing Masuk:\n"
            ."ID Tiket: *{$ticket->ticket_id}*\n\n"
            ."Nama Karyawan: {$ticket->nama_karyawan}\n"
            ."Divisi: {$ticket->divisi}\n"
            ."Kategori: {$ticket->kategori}\n"
            ."Keperluan: {$ticket->keperluan}\n"
            ."Detail Kendala: {$ticket->detail_kendala}\n\n"
            ."Balas dengan format:\n"
            ."`/terima {$ticket->ticket_id}` untuk memproses.";

        $timestamp = $this->normalizeTimestamp($ticket->timestamp);
        $detail_kendala_ts = '';
        $detail_kendala_pr = '';
        $detail_kendala_td = '';

        if ($ticket->keperluan == 'Technical Support') {
            $detail_kendala_ts = $ticket->detail_kendala;
        } elseif ($ticket->keperluan == 'Programming') {
            $detail_kendala_pr = $ticket->detail_kendala;
        } else {
            $detail_kendala_td = $ticket->detail_kendala;
        }
        $values = [
            [
                $timestamp,
                $ticket->nama_karyawan,
                $ticket->divisi,
                $ticket->kategori,
                $detail_kendala_ts,
                $ticket->keperluan,
                $detail_kendala_pr,
                $detail_kendala_td,
            ],
        ];

        $itsm = karyawan::where('divisi', 'IT Service Management')->get();

        // Ambil array kode_karyawan
        $kodeKaryawanList = $itsm->pluck('kode_karyawan')->toArray();

        // Filter dan olah nilai yang '-' jadi null (atau sesuai logika Anda)
        $users = array_map(function ($user) {
            return $user === '-' ? null : $user;
        }, $kodeKaryawanList);

        // Ambil data User yang terkait dengan kode_karyawan setelah difilter
        $users = User::whereHas('karyawan', function ($query) use ($users) {
            $query->whereIn('kode_karyawan', array_filter($users)); // pastikan tidak ada null
        })->get();

        $path = '/tickets';
        $status = 'Ticketing Baru';

        foreach ($users as $user) {
            $receiverId = $user->id;
            NotificationFacade::send($user, new TicketNotification($ticket, $path, $status, $receiverId));
        }

        // =========================================================================
        // PENGIRIMAN LANGSUNG KE TELEGRAM DARI INIXCOFFEE
        // =========================================================================
        try {
            $botToken = config('services.telegram.bot_token');
            $groupId = config('services.telegram.group_id');

            $telegramMessage = "🔔 *Ada Ticketing Masuk:*\n\n"
                ."ID Tiket: `{$ticket->ticket_id}`\n"
                ."Nama: {$ticket->nama_karyawan}\n"
                ."Divisi: {$ticket->divisi}\n"
                ."Kategori: {$ticket->kategori}\n"
                ."Keperluan: {$ticket->keperluan}\n"
                ."Kendala: {$ticket->detail_kendala}\n\n"
                ."Silahkan klik tombol di bawah atau balas dengan `/terima {$ticket->ticket_id}`";

            $response = Http::withOptions([
                'curl' => [CURLOPT_RESOLVE => ['api.telegram.org:443:149.154.166.110'], CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]
            ])->timeout(10)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $groupId,
                'text' => $telegramMessage,
                'parse_mode' => 'Markdown',
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [
                            ['text' => '✅ Terima', 'callback_data' => "accept:{$ticket->ticket_id}"],
                            ['text' => '❌ Tolak', 'callback_data' => "reject:{$ticket->ticket_id}"],
                        ],
                        [
                            ['text' => '🏁 Selesai', 'callback_data' => "finish:{$ticket->ticket_id}"],
                        ],
                    ],
                ]),
            ]);

            if ($response->successful()) {
                $resData = $response->json();
                $messageId = $resData['result']['message_id'] ?? null;
                if ($messageId) {
                    $ticket->telegram_message_id = $messageId;
                    $ticket->save();
                }
            }
        } catch (\Exception $e) {
            Log::error('Gagal mengirim tiket baru ke Telegram: '.$e->getMessage());
        }

        return redirect()->route('tickets.index')->with('success', 'Tiket berhasil dibuat, akan segera diprovide. Terimakasih!');
    }

    private function notifyTelegram($action, $ticket, $pic = null, $keterangan = null)
    {
        // =========================================================================
        // PENGIRIMAN UPDATE STATUS LANGSUNG KE TELEGRAM DARI INIXCOFFEE
        // =========================================================================
        try {
            $botToken = config('services.telegram.bot_token');
            $groupId = config('services.telegram.group_id');

            $picName = $pic ?? ($ticket->pic ?? '-');
            $infoKeterangan = $keterangan ?? '-';

            if ($action === 'accepted') {
                $text = "✅ *TICKET DITERIMA*\n\n"
                      ."ID: `{$ticket->ticket_id}`\n"
                      ."PIC: {$picName}\n"
                      .'Status: Sedang Diproses';
            } elseif ($action === 'finished') {
                $text = "🏁 *TICKET SELESAI*\n\n"
                      ."ID: `{$ticket->ticket_id}`\n"
                      ."PIC: {$picName}\n"
                      ."Keterangan: {$infoKeterangan}\n"
                      .'Status: Selesai';
            } else {
                $text = "🚫 *TICKET TERKENDALA / DITOLAK*\n\n"
                      ."ID: `{$ticket->ticket_id}`\n"
                      ."Keterangan: {$infoKeterangan}\n"
                      .'Status: Dibatalkan / Terkendala';
            }

            Http::withOptions([
                'curl' => [CURLOPT_RESOLVE => ['api.telegram.org:443:149.154.166.110'], CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]
            ])->timeout(10)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $groupId,
                'text' => $text,
                'parse_mode' => 'Markdown',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal kirim update ke Telegram: '.$e->getMessage());
        }
    }

    public function index()
    {
        return view('ticket.index');
    }

    public function getTickets()
    {
        $user = auth()->user()->karyawan_id;
        $karyawan = karyawan::findOrFail($user);
        $jabatan = $karyawan->jabatan;
        $divisi = $karyawan->divisi;

        $lembur = collect();

        if ($divisi == 'IT Service Management') {
            $lembur = Tickets::with('karyawan')->latest()->get();
        } else {
            $lembur = Tickets::with('karyawan')->whereHas('karyawan', function ($query) use ($user) {
                $query->where('id', $user);
            })->latest()->get();
        }

        return response()->json([
            'success' => true,
            'message' => 'List Tickets Karyawan',
            'data' => $lembur,
        ]);
    }

    public function create()
    {
        $karyawan = karyawan::get();

        return view('ticket.create', compact('karyawan'));
    }

    public function accept(Request $request, Tickets $ticket)
    {
        $tanggal_response = $request->tanggal_response;
        $jam_response = $request->jam_response;
        // dd($request->all());
        $ticket->update([
            'penanganan' => 'Sedang Diperbaiki',
            'status' => 'Di Proses',
            'tanggal_response' => $tanggal_response,
            'jam_response' => $jam_response,
            'pic' => $request->pic,
        ]);

        $spreadsheetId = '1k_NRI52B-alnGVeLTGB8cecL3f1G-C7_WCVGnQQGe9Y';
        $range = 'Form Responses 1!I'.$ticket->row.':M'.$ticket->row;
        $values = [
            [
                $tanggal_response,
                $jam_response,
                $request->pic,
                $ticket->penanganan,
                $ticket->status,
            ],
        ];

        $message = "Ticket Sedang Ditangani Oleh:\n"
            ."Nama Karyawan: {$request->pic}\n"
            ."Divisi: IT Service Management\n"
            ."Penanganan: Sedang Diperbaiki/Dicek\n"
            ."Waktu Response: {$tanggal_response} {$jam_response}\n"
            .'Mohon tunggu. Terimakasih!';

        $this->notifyTelegram('accepted', $ticket, $request->pic);
        if ($request->from_internal || $request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 'success',
                'message' => 'Ticket processed via API',
            ], 200);
        }

        return redirect()->route('tickets.index')->with('success', 'Tiket diterima.');
    }

    public function finish(Request $request, Tickets $ticket)
    {
        // dd($request->all());
        $tanggal_selesai = $request->tanggal_selesai;
        $jam_selesai = $request->jam_selesai;

        $ticket->update([
            'status' => 'Selesai',
            'penanganan' => $request->penanganan,
            'keterangan' => $request->keterangan,
            'tanggal_selesai' => $tanggal_selesai,
            'jam_selesai' => $jam_selesai,
            'tingkat_kesulitan' => $request->kesulitan,
        ]);

        $values = [
            [
                $ticket->penanganan,
                $ticket->status,
                $tanggal_selesai,
                $jam_selesai,
                $request->keterangan,
                '',
                '',
                $ticket->tingkat_kesulitan,
            ],
        ];
        $message = "Ticket Sudah Selesai:\n"
            ."Nama Karyawan: {$ticket->nama_karyawan}\n"
            ."Divisi: {$ticket->divisi}\n"
            ."Detail Kendala: {$ticket->detail_kendala}\n"
            ."Waktu Selesai: {$tanggal_selesai} {$jam_selesai}\n"
            .'Terimakasih!';

        $pembuatTiket = User::whereHas('karyawan', function ($query) use ($ticket) {
            $query->whereRaw('LOWER(TRIM(nama_lengkap)) = ?', [strtolower(trim($ticket->nama_karyawan))]);
        })->first();

        // Pengiriman notifikasi sistem jika pengguna ditemukan
        if ($pembuatTiket) {
            NotificationFacade::send($pembuatTiket, new \App\Notifications\SurveyReminderNotification($ticket));
        }
        $this->notifyTelegram('finished', $ticket, $ticket->pic, $request->keterangan);
        if ($request->from_internal || $request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 'success',
                'message' => 'Ticket Finished via API',
            ], 200);
        }

        return redirect()->route('tickets.index')->with('success', 'Tiket selesai.');
    }

    public function block(Request $request, Tickets $ticket)
    {
        // dd($request->all());
        $tanggal_selesai = $request->tanggal_selesai;
        $jam_selesai = $request->jam_selesai;

        $ticket->update([
            'status' => 'Terkendala',
            'keterangan' => $request->keterangan,
            'penanganan' => $request->penanganan,
            'tanggal_selesai' => $tanggal_selesai,
            'jam_selesai' => $jam_selesai,
        ]);

        $values = [
            [
                $ticket->penanganan,
                $ticket->status,
                $tanggal_selesai,
                $jam_selesai,
                $request->keterangan,
                '',
                '',
                $ticket->kesulitan,
            ],
        ];
        $message = "Ticket Terkendala:\n"
            ."Nama Karyawan: {$ticket->nama_karyawan}\n"
            ."Divisi: {$ticket->divisi}\n"
            ."Detail Kendala: {$ticket->detail_kendala}\n"
            ."Keterangan: {$request->keterangan}\n"
            ."Waktu Selesai: {$tanggal_selesai} {$jam_selesai}\n"
            .'Terimakasih!';

        $this->notifyTelegram('rejected', $ticket, null, $request->keterangan);

        if ($request->from_internal || $request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 'success',
                'message' => 'Ticket processed via API',
            ], 200);
        }

        return redirect()->route('tickets.index')->with('success', 'Tiket ditandai sebagai terkendala.');
    }

    public function destroy(Tickets $ticket)
    {
        $user = auth()->user();
        $isItsmCoordinator = $user
            && $user->jabatan === 'Koordinator ITSM'
            && optional($user->karyawan)->divisi === 'IT Service Management';

        abort_unless($isItsmCoordinator, 403);

        $deleted = Tickets::whereKey($ticket->getKey())->delete();
        abort_unless($deleted === 1, 404);

        return redirect()->route('tickets.index')->with('success', 'Tiket berhasil dihapus.');
    }

    /**
     * Edit ticket oleh User (hanya bisa jika status belum diproses)
     */
    public function editUser(Request $request, Tickets $ticket)
    {
        $karyawan = auth()->user()->karyawan;
        $karyawanNama = trim($karyawan->nama_lengkap ?? '');
        $divisi = $karyawan->divisi ?? null;

        // ITSM boleh edit semua, user biasa hanya bisa edit tiket miliknya sendiri
        if ($divisi !== 'IT Service Management') {
            $isOwner = (strcasecmp(trim($ticket->nama_karyawan), $karyawanNama) === 0);
            if (!$isOwner && $ticket->karyawan && $ticket->karyawan->id == auth()->user()->karyawan_id) {
                $isOwner = true;
            }
            abort_unless($isOwner, 403, 'Anda tidak memiliki hak untuk mengedit tiket ini.');
        }

        // Hanya bisa diedit jika belum diproses (bukan Di Proses, Selesai, atau Terkendala)
        abort_if(in_array($ticket->status, ['Di Proses', 'Selesai', 'Terkendala']), 403, 'Tiket yang sudah diproses tidak dapat diedit.');

        $request->validate([
            'keperluan'      => 'required|string',
            'kategori'       => 'required|string',
            'detail_kendala' => 'required|string',
        ]);

        $ticket->update([
            'keperluan'      => $request->keperluan,
            'kategori'       => $request->kategori,
            'detail_kendala' => $request->detail_kendala,
        ]);

        // Perbarui pesan di Telegram
        $this->updateTelegramMessage($ticket);

        return redirect()->route('tickets.index')->with('success', 'Tiket berhasil diperbarui.');
    }

    /**
     * Update pesan tiket di Telegram saat diedit
     */
    private function updateTelegramMessage(Tickets $ticket)
    {
        try {
            $botToken = config('services.telegram.bot_token');
            $groupId = config('services.telegram.group_id');

            if (!$botToken || !$groupId) {
                return;
            }

            $telegramMessage = "🔔 *Ada Ticketing Masuk:*\n\n"
                ."ID Tiket: `{$ticket->ticket_id}`\n"
                ."Nama: {$ticket->nama_karyawan}\n"
                ."Divisi: {$ticket->divisi}\n"
                ."Kategori: {$ticket->kategori}\n"
                ."Keperluan: {$ticket->keperluan}\n"
                ."Kendala: {$ticket->detail_kendala}\n\n"
                ."Silahkan klik tombol di bawah atau balas dengan `/terima {$ticket->ticket_id}`";

            $replyMarkup = [
                'inline_keyboard' => [
                    [
                        ['text' => '✅ Terima', 'callback_data' => "accept:{$ticket->ticket_id}"],
                        ['text' => '❌ Tolak', 'callback_data' => "reject:{$ticket->ticket_id}"],
                    ],
                    [
                        ['text' => '🏁 Selesai', 'callback_data' => "finish:{$ticket->ticket_id}"],
                    ],
                ],
            ];

            // 1. Jika ada telegram_message_id, edit pesan aslinya secara langsung
            if ($ticket->telegram_message_id) {
                $response = Http::withOptions([
                    'curl' => [CURLOPT_RESOLVE => ['api.telegram.org:443:149.154.166.110'], CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]
                ])->timeout(10)->post("https://api.telegram.org/bot{$botToken}/editMessageText", [
                    'chat_id' => $groupId,
                    'message_id' => (int) $ticket->telegram_message_id,
                    'text' => $telegramMessage,
                    'parse_mode' => 'Markdown',
                    'reply_markup' => json_encode($replyMarkup),
                ]);

                if ($response->successful()) {
                    return;
                }

                Log::warning('Gagal edit pesan Telegram asli, mengirim notifikasi pembaruan: '.$response->body());
            }

            // 2. Fallback: jika belum ada telegram_message_id (tiket lama) atau gagal edit (>48 jam), kirim pesan baru
            $updateNotification = $telegramMessage;

            $newMsgResponse = Http::withOptions([
                'curl' => [CURLOPT_RESOLVE => ['api.telegram.org:443:149.154.166.110'], CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]
            ])->timeout(10)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $groupId,
                'text' => $updateNotification,
                'parse_mode' => 'Markdown',
                'reply_markup' => json_encode($replyMarkup),
            ]);

            if ($newMsgResponse->successful()) {
                $newResData = $newMsgResponse->json();
                $newMsgId = $newResData['result']['message_id'] ?? null;
                if ($newMsgId && !$ticket->telegram_message_id) {
                    $ticket->telegram_message_id = $newMsgId;
                    $ticket->save();
                }
            }
        } catch (\Exception $e) {
            Log::error('Gagal update tiket ke Telegram: '.$e->getMessage());
        }
    }

    /**
     * Edit waktu terima & waktu selesai oleh ITSM
     */
    public function editItsm(Request $request, Tickets $ticket)
    {
        $divisi = auth()->user()->karyawan->divisi ?? null;
        abort_unless($divisi === 'IT Service Management', 403);

        $request->validate([
            'tanggal_response' => 'nullable|date',
            'jam_response'     => 'nullable|string',
            'tanggal_selesai'  => 'nullable|date',
            'jam_selesai'      => 'nullable|string',
            'pic'              => 'nullable|string',
        ]);

        $ticket->update([
            'tanggal_response' => $request->tanggal_response,
            'jam_response'     => $request->jam_response,
            'tanggal_selesai'  => $request->tanggal_selesai,
            'jam_selesai'      => $request->jam_selesai,
            'pic'              => $request->pic ?? $ticket->pic,
        ]);

        return redirect()->route('tickets.index')->with('success', 'Data waktu tiket berhasil diperbarui.');
    }

    public function show(Tickets $ticket)
    {
        return view('ticket.detail', compact('ticket'));
    }

    private function expectedInternalToken(): string
    {
        $configured = (string) config('services.internal_api.ticket_token', '');
        if ($configured !== '') {
            return $configured;
        }

        return hash_hmac('sha256', 'internal-ticket-token', (string) config('app.key'));
    }

    private function hasValidInternalToken(Request $request): bool
    {
        $provided = (string) $request->header('X-Internal-Token', '');
        $expected = $this->expectedInternalToken();

        return $expected !== '' && hash_equals($expected, $provided);
    }

    public function handleInternalUpdate(Request $request)
    {
        $request->headers->set('Accept', 'application/json');
        // Validasi Token Rahasia agar aman
        if (!$this->hasValidInternalToken($request)) {
            return response()->json(['message' => 'Unauthorized Access'], 401);
        }
        Log::info('Laravel B: Internal Update Request Received', [
            'ip_pengirim' => $request->ip(),
            'ticket_id' => $request->ticket_id,
            'action' => $request->action,
            'pic_name' => $request->pic_name,
            'full_payload' => $request->all(),
        ]);

        $ticket = Tickets::where('ticket_id', $request->ticket_id)->first();
        if (!$ticket) {
            return response()->json(['message' => 'Tiket tidak ditemukan'], 404);
        }

        $action = $request->action;

        // --- LOGIC ACCEPT ---
        if ($action === 'accept') {
            $fakeRequest = new Request([
                'pic' => $request->pic_name,
                'tanggal_response' => now()->format('Y-m-d'),
                'jam_response' => now()->format('H:i:s'),
                'from_internal' => true,
            ]);

            return $this->accept($fakeRequest, $ticket);
        }

        // --- LOGIC FINISH ---
        if ($action === 'finish') {
            $fakeRequest = new Request([
                'penanganan' => 'Selesai via Telegram',
                'keterangan' => $request->keterangan ?? 'Selesai',
                'kesulitan' => 'Normal',
                'tanggal_selesai' => now()->format('Y-m-d'),
                'jam_selesai' => now()->format('H:i:s'),
                'from_internal' => true,
            ]);

            return $this->finish($fakeRequest, $ticket);
        }

        // --- LOGIC REJECT (BLOCK) ---
        if ($action === 'reject') {
            $fakeRequest = new Request([
                'penanganan' => 'Terkendala/Ditolak via Telegram',
                'keterangan' => 'Dibatalkan oleh '.($request->pic_name ?? 'IT'),
                'tanggal_selesai' => now()->format('Y-m-d'),
                'jam_selesai' => now()->format('H:i:s'),
                'from_internal' => true,
            ]);

            return $this->block($fakeRequest, $ticket);
        }

        return response()->json(['message' => 'Action tidak dikenali'], 400);
    }

    public function getOpenTickets(Request $request)
    {
        // Validasi Token
        if (!$this->hasValidInternalToken($request)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Ambil tiket yang statusnya bukan 'Selesai' (SARGable range tahun berjalan)
        $tickets = Tickets::where('status', '!=', 'Selesai')
            ->select('ticket_id', 'nama_karyawan', 'detail_kendala', 'created_at')
            ->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json($tickets);
    }

    public function rekap()
    {
        return view('ticket.rekap');
    }

    public function rekapData(Request $request)
    {
        $request->validate([
            'periode'  => 'nullable|in:harian,mingguan,bulanan,tahunan,custom',
            'tanggal'  => 'nullable|date',
            'tahun'    => 'nullable|integer|min:2000|max:2100',
            'dari'     => 'nullable|date',
            'sampai'   => 'nullable|date|after_or_equal:dari',
            'divisi'   => 'nullable|string|max:100',
            'kategori' => 'nullable|string|max:100',
            'pic'      => 'nullable|string|max:100',
        ]);

        // ===== Konfigurasi (sesuaikan dengan aturan kantor) =====
        $statusDone  = 'Selesai';           // nilai kolom status untuk tiket selesai
        $nonIncident = ['Minor', 'Normal']; // selain ini masuk log insiden mingguan

        // ===== Rentang tanggal =====
        $periode = $request->input('periode', 'bulanan');
        $anchor  = Carbon::parse($request->input('tanggal', now()->toDateString()));
        $tahun   = (int) $request->input('tahun', now()->year);

        [$start, $end] = match ($periode) {
            'harian'   => [$anchor->copy()->startOfDay(), $anchor->copy()->endOfDay()],
            'mingguan' => [
                $anchor->copy()->startOfWeek(Carbon::MONDAY)->startOfDay(),
                $anchor->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay(),
            ],
            'tahunan'  => [
                Carbon::create($tahun, 1, 1)->startOfYear(),
                Carbon::create($tahun, 1, 1)->endOfYear(),
            ],
            'custom'   => [
                Carbon::parse($request->input('dari', $anchor))->startOfDay(),
                Carbon::parse($request->input('sampai', $anchor))->endOfDay(),
            ],
            default    => [$anchor->copy()->startOfMonth(), $anchor->copy()->endOfMonth()],
        };

        // Filter global (divisi, kategori, pic) - hanya yang terisi
        $filters = array_filter($request->only(['divisi', 'kategori', 'pic']), fn ($v) => $v !== null && $v !== '');

        $tickets = Tickets::where($filters)
            ->whereBetween('timestamp', [$start, $end])
            ->get([
                'ticket_id', 'timestamp', 'kategori', 'detail_kendala', 'pic', 'penanganan',
                'status', 'keterangan', 'tingkat_kesulitan',
                'tanggal_response', 'jam_response', 'tanggal_selesai', 'jam_selesai',
            ]);

        // ===== Satu kali loop untuk semua agregasi =====
        $nowTs     = now()->getTimestamp();
        $status    = [];
        $kategori  = [];
        $perPic    = [];
        $daily     = [];
        $open      = [];
        $incidents = [];

        foreach ($tickets as $t) {
            $created = Carbon::parse($t->timestamp);
            $resp    = ($t->tanggal_response && $t->jam_response)
                ? Carbon::parse($t->tanggal_response . ' ' . $t->jam_response) : null;
            $fin     = ($t->tanggal_selesai && $t->jam_selesai)
                ? Carbon::parse($t->tanggal_selesai . ' ' . $t->jam_selesai) : null;
            $done    = strcasecmp(trim((string) $t->status), $statusDone) === 0;

            $respMin = $resp ? max(0, ($resp->getTimestamp() - $created->getTimestamp()) / 60) : null;
            $resMin  = ($done && $fin) ? max(0, ($fin->getTimestamp() - $created->getTimestamp()) / 60) : null;

            // status
            $statusKey = $t->status ?: 'Tanpa status';
            $status[$statusKey] = ($status[$statusKey] ?? 0) + 1;

            // tren waktu: per hari, atau per bulan kalau periode tahunan
            $bucket = $periode === 'tahunan' ? $created->format('Y-m') : $created->toDateString();
            $daily[$bucket] ??= ['total' => 0, 'resp' => [], 'res' => []];
            $daily[$bucket]['total']++;
            if ($respMin !== null) {
                $daily[$bucket]['resp'][] = $respMin;
            }
            if ($resMin !== null) {
                $daily[$bucket]['res'][] = $resMin;
            }

            // kategori
            $katKey = $t->kategori ?: 'Lainnya';
            $kategori[$katKey] = ($kategori[$katKey] ?? 0) + 1;

            // PIC
            $picKey = $t->pic ?: 'Belum ada PIC';
            $perPic[$picKey] ??= ['pic' => $picKey, 'total' => 0, 'selesai' => 0, 'res' => []];
            $perPic[$picKey]['total']++;
            if ($done) {
                $perPic[$picKey]['selesai']++;
            }
            if ($resMin !== null) {
                $perPic[$picKey]['res'][] = $resMin;
            }

            // tiket belum selesai
            if (! $done) {
                $open[] = [
                    'ticket_id' => $t->ticket_id,
                    'kategori'  => $t->kategori,
                    'pic'       => $t->pic,
                    'status'    => $t->status,
                    'age_min'   => round(($nowTs - $created->getTimestamp()) / 60),
                    'alasan'    => $t->keterangan ?: $t->penanganan,
                ];
            }

            // log insiden (khusus mingguan)
            if ($periode === 'mingguan' && $t->tingkat_kesulitan
                && ! in_array($t->tingkat_kesulitan, $nonIncident, true)) {
                $incidents[] = [
                    'ticket_id'  => $t->ticket_id,
                    'tanggal'    => $created->format('d/m/Y H:i'),
                    'tingkat'    => $t->tingkat_kesulitan,
                    'masalah'    => $t->detail_kendala,
                    'tindakan'   => $t->penanganan,
                    'keterangan' => $t->keterangan,
                    'status'     => $t->status,
                ];
            }
        }

        // ===== Rapikan hasil agregasi =====
        $statusOut = [];
        foreach ($status as $label => $total) {
            $statusOut[] = ['label' => $label, 'total' => $total];
        }

        usort($open, fn ($a, $b) => $b['age_min'] <=> $a['age_min']);

        ksort($daily);
        $dailyOut = [];
        foreach ($daily as $key => $v) {
            $dailyOut[] = [
                'date'           => $key,
                'total'          => $v['total'],
                'avg_response'   => count($v['resp']) ? round(array_sum($v['resp']) / count($v['resp']), 1) : null,
                'avg_resolution' => count($v['res']) ? round(array_sum($v['res']) / count($v['res']), 1) : null,
            ];
        }

        arsort($kategori);
        $kategoriOut = [];
        foreach (array_slice($kategori, 0, 10, true) as $label => $total) {
            $kategoriOut[] = ['label' => $label, 'total' => $total];
        }

        $picOut = [];
        foreach ($perPic as $v) {
            $picOut[] = [
                'pic'            => $v['pic'],
                'total'          => $v['total'],
                'selesai'        => $v['selesai'],
                'avg_resolution' => count($v['res']) ? round(array_sum($v['res']) / count($v['res']), 1) : null,
            ];
        }
        usort($picOut, fn ($a, $b) => $b['total'] <=> $a['total']);

        // ===== Volume per bulan: 12 bulan untuk tahunan, selain itu 6 bulan terakhir =====
        // (ikut filter divisi/kategori/pic, bukan filter tanggal)
        $lastMonth  = $end->copy()->startOfMonth();
        $trendStart = $lastMonth->copy()->subMonths($periode === 'tahunan' ? 11 : 5);
        $trendRaw   = Tickets::where($filters)
            ->whereBetween('timestamp', [$trendStart, $end->copy()->endOfMonth()])
            ->selectRaw("DATE_FORMAT(`timestamp`, '%Y-%m') as bulan, COUNT(*) as total, SUM(status = ?) as selesai", [$statusDone])
            ->groupBy('bulan')
            ->get()
            ->keyBy('bulan');

        $trend = [];
        for ($m = $trendStart->copy(); $m <= $lastMonth; $m->addMonth()) {
            $row = $trendRaw->get($m->format('Y-m'));
            $trend[] = [
                'label'   => $m->translatedFormat('M Y'),
                'total'   => (int) ($row->total ?? 0),
                'selesai' => (int) ($row->selesai ?? 0),
            ];
        }

        return response()->json([
            'range' => [
                'periode' => $periode,
                'label'   => $periode === 'tahunan'
                    ? 'Tahun ' . $tahun
                    : $start->translatedFormat('d M Y') . ' - ' . $end->translatedFormat('d M Y'),
                'total'   => $tickets->count(),
            ],
            'filters' => [
                'divisi'   => Tickets::whereNotNull('divisi')->distinct()->orderBy('divisi')->pluck('divisi'),
                'kategori' => Tickets::whereNotNull('kategori')->distinct()->orderBy('kategori')->pluck('kategori'),
                'pic'      => Tickets::whereNotNull('pic')->distinct()->orderBy('pic')->pluck('pic'),
            ],
            'status'       => $statusOut,
            'open_tickets' => array_slice($open, 0, 20),
            'daily'        => $dailyOut,
            'per_kategori' => $kategoriOut,
            'per_pic'      => $picOut,
            'trend'        => $trend,
            'incidents'    => $periode === 'mingguan' ? $incidents : null,
        ]);
    }
}