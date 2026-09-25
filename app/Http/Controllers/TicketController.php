<?php

namespace App\Http\Controllers;

use App\Models\karyawan;
use App\Models\Tickets;
use App\Models\User;
use App\Notifications\TicketNotification;
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
            $botToken = env('TELEGRAM_BOT_TOKEN');
            $groupId = env('TELEGRAM_GROUP_ID');

            $telegramMessage = "🔔 *Ada Ticketing Masuk:*\n\n"
                ."ID Tiket: `{$ticket->ticket_id}`\n"
                ."Nama: {$ticket->nama_karyawan}\n"
                ."Divisi: {$ticket->divisi}\n"
                ."Kategori: {$ticket->kategori}\n"
                ."Keperluan: {$ticket->keperluan}\n"
                ."Kendala: {$ticket->detail_kendala}\n\n"
                ."Silahkan klik tombol di bawah atau balas dengan `/terima {$ticket->ticket_id}`";

            Http::withoutVerifying()->timeout(10)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
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
            $botToken = env('TELEGRAM_BOT_TOKEN');
            $groupId = env('TELEGRAM_GROUP_ID');

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

            Http::withoutVerifying()->timeout(10)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
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
            $query->where('nama_lengkap', $ticket->nama_karyawan);
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

    public function show(Tickets $ticket)
    {
        return view('ticket.detail', compact('ticket'));
    }

    public function handleInternalUpdate(Request $request)
    {
        $request->headers->set('Accept', 'application/json');
        // Validasi Token Rahasia agar aman
        if ($request->header('X-Internal-Token') !== 'TOKEN_RAHASIA_KITA_123') {
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
        if ($request->header('X-Internal-Token') !== 'TOKEN_RAHASIA_KITA_123') {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Ambil tiket yang statusnya bukan 'Selesai'
        $tickets = Tickets::where('status', '!=', 'Selesai')
            ->select('ticket_id', 'nama_karyawan', 'detail_kendala', 'created_at')
            ->whereYear('created_at', now()->year) // Sebutkan kolom 'created_at'
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json($tickets);
    }
}
