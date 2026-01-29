<?php

namespace App\Http\Controllers;

use App\Models\karyawan;
use App\Models\Tickets;
use App\Models\User;
use App\Notifications\TicketNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;
use DateTime;
use Illuminate\Support\Facades\Notification as NotificationFacade;
// use Google_Service_Sheets_ValueRange;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function normalizeTimestamp($timestamp)
    {
        // Jika sudah objek DateTime
        if ($timestamp instanceof DateTime) {
            return $timestamp->format('n/j/Y H:i:s');
        }

        // Jika string, coba deteksi format terlebih dahulu
        if (is_string($timestamp)) {
            // Cek format dengan regex atau coba parse dulu
            $date = DateTime::createFromFormat('m/d/Y H:i:s', $timestamp);
            if ($date !== false) {
                return $date->format('n/j/Y H:i:s');
            }
            $date = DateTime::createFromFormat('Y-m-d H:i:s', $timestamp);
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
        return (string)$timestamp;
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
            'timestamp' => $request->datetime
            // nilai default lainnya tetap
        ]);

        $todayCount = Tickets::whereDate('created_at', today())->count();
        $char = chr(96 + $todayCount); // a untuk tiket pertama, b untuk kedua, dst.
        $ticketId = 'NIX' . now()->format('ymd') . $char;
        $ticket->ticket_id = $ticketId;
        $ticket->save();


        $message = "Ada Ticketing Masuk:\n"
            . "ID Tiket: *{$ticket->ticket_id}*\n\n"
            . "Nama Karyawan: {$ticket->nama_karyawan}\n"
            . "Divisi: {$ticket->divisi}\n"
            . "Kategori: {$ticket->kategori}\n"
            . "Keperluan: {$ticket->keperluan}\n"
            . "Detail Kendala: {$ticket->detail_kendala}\n\n"
            . "Balas dengan format:\n"
            . "`/terima {$ticket->ticket_id}` untuk memproses.";


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
            ]
        ];
        $ticket->update([
            'row' => $message
        ]);
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
        $status = "Ticketing Baru";

        foreach ($users as $user) {
            $receiverId = $user->id;
            NotificationFacade::send($user, new TicketNotification($ticket, $path, $status, $receiverId));
        }

        // KIRIM KE LARAVEL A VIA WEBHOOK
        try {
            Http::withHeaders([
                'Accept' => 'application/json',
                'X-Webhook-Secret' => 'RAHASIA_KITA' // Opsional: Untuk keamanan
            ])->post('https://inixindobdg.co.id/api/new-ticket-notification', [
                'ticket_id'      => $ticket->ticket_id,
                'nama_karyawan'  => $ticket->nama_karyawan,
                'divisi'         => $ticket->divisi,
                'kategori'       => $ticket->kategori,
                'keperluan'      => $ticket->keperluan,
                'detail_kendala' => $ticket->detail_kendala,
            ]);
            
        } catch (\Exception $e) {
            // Log error jika Laravel A down
            Log::error("Gagal mengirim webhook: " . $e->getMessage());
        }

        return redirect()->route('tickets.index')->with('success', 'Tiket berhasil dibuat, akan segera diprovide. Terimakasih!');
    }

    private function appendValues($spreadsheetId, $range, $values, $valueInputOption = 'RAW')
    {
        $client = new Client();
        $client->setAuthConfig(storage_path('app/google/chart-spreadsheet-api.json'));
        $client->addScope(Sheets::SPREADSHEETS);

        $service = new Sheets($client);

        try {
            $body = new \Google\Service\Sheets\ValueRange();
            $body->setValues($values);

            $params = ['valueInputOption' => $valueInputOption];

            $result = $service->spreadsheets_values->append(
                $spreadsheetId,
                $range,
                $body,
                $params
            );

            $updatedRange = $result->getUpdates()->getUpdatedRange();

            if (preg_match('/![A-Z]+(\d+):[A-Z]+\d+$/', $updatedRange, $matches)) {
                $startRow = intval($matches[1]);
                printf("%d.", $result->getUpdates()->getUpdatedCells(), $startRow);
                return $startRow;
            } else {
                printf("%d cells appended. Namun gagal mengambil baris ID.", $result->getUpdates()->getUpdatedCells());
                return 500;
            }
        } catch (\Exception $e) {
            echo 'Message: ' . $e->getMessage();
            return 500;
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

        $values = [
            [
                $tanggal_response,
                $jam_response,
                $request->pic,
                $ticket->penanganan,
                $ticket->status,
            ]
        ];
        
        $message = "Ticket Sedang Ditangani Oleh:\n"
            . "Nama Karyawan: {$request->pic}\n"
            . "Divisi: IT Service Management\n"
            . "Penanganan: Sedang Diperbaiki/Dicek\n"
            . "Waktu Response: {$tanggal_response} {$jam_response}\n"
            . "Mohon tunggu. Terimakasih!";

        
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
            ]
        ];
        $message = "Ticket Sudah Selesai:\n"
            . "Nama Karyawan: {$ticket->nama_karyawan}\n"
            . "Divisi: {$ticket->divisi}\n"
            . "Detail Kendala: {$ticket->detail_kendala}\n"
            . "Waktu Selesai: {$tanggal_selesai} {$jam_selesai}\n"
            . "Terimakasih!";

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
            ]
        ];
        $message = "Ticket Terkendala:\n"
            . "Nama Karyawan: {$ticket->nama_karyawan}\n"
            . "Divisi: {$ticket->divisi}\n"
            . "Detail Kendala: {$ticket->detail_kendala}\n"
            . "Keterangan: {$request->keterangan}\n"
            . "Waktu Selesai: {$tanggal_selesai} {$jam_selesai}\n"
            . "Terimakasih!";

      
        return redirect()->route('tickets.index')->with('success', 'Tiket ditandai sebagai terkendala.');
    }
    public function show(Tickets $ticket)
    {
        return view('ticket.detail', compact('ticket'));
    }

    private function updatedValues($spreadsheetId, $range, $values, $valueInputOption = 'RAW')
    {
        $client = new Client();
        $client->setAuthConfig(storage_path('app/google/chart-spreadsheet-api.json'));
        $client->addScope(Sheets::SPREADSHEETS);

        $service = new Sheets($client);

        try {
            // Gunakan nama class yang benar sesuai namespace
            $body = new \Google\Service\Sheets\ValueRange();
            $body->setValues($values);


            $params = ['valueInputOption' => $valueInputOption];

            $result = $service->spreadsheets_values->update(
                $spreadsheetId,
                $range,
                $body,
                $params
            );

            printf("%d cells updated.", $result->getUpdatedCells());
            return $result;
        } catch (\Exception $e) {
            echo 'Message: ' . $e->getMessage();
            return null;
        }
    }


    public function handleInternalUpdate(Request $request)
    {
        // 1. Log request masuk dari Laravel A
        Log::info('Laravel B: Internal Update Request Received', [
            'ip_pengirim' => $request->ip(),
            'ticket_id'   => $request->ticket_id,
            'action'      => $request->action,
            'pic_name'    => $request->pic_name,
            'full_payload'=> $request->all()
        ]);

        // Cari tiket berdasarkan ticket_id
        $ticket = Tickets::where('ticket_id', $request->ticket_id)->first();

        if (!$ticket) {
            Log::error("Laravel B: Ticket ID {$request->ticket_id} Not Found.");
            return response()->json(['message' => 'Tiket tidak ditemukan'], 404);
        }

        $action = $request->action;

        if ($action === 'accept') {
            Log::info("Laravel B: Processing ACCEPT for Ticket {$ticket->ticket_id} by {$request->pic_name}");
            
            $fakeRequest = new Request([
                'pic' => $request->pic_name,
                'tanggal_response' => now()->format('Y-m-d'),
                'jam_response' => now()->format('H:i:s'),
            ]);
            return $this->accept($fakeRequest, $ticket);
        }

        if ($action === 'finish') {
            Log::info("Laravel B: Processing FINISH for Ticket {$ticket->ticket_id}");

            $fakeRequest = new Request([
                'penanganan' => 'Selesai via Bot Telegram',
                'keterangan' => $request->keterangan ?? 'Selesai',
                'kesulitan' => 'Normal',
                'tanggal_selesai' => now()->format('Y-m-d'),
                'jam_selesai' => now()->format('H:i:s'),
            ]);
            return $this->finish($fakeRequest, $ticket);
        }

        if ($action === 'reject') {
            Log::info("Laravel B: Processing REJECT/BLOCK for Ticket {$ticket->ticket_id}");

            $fakeRequest = new Request([
                'penanganan' => 'Terkendala/Ditolak',
                'keterangan' => 'Dibatalkan/Ditolak via Telegram',
                'tanggal_selesai' => now()->format('Y-m-d'),
                'jam_selesai' => now()->format('H:i:s'),
            ]);
            return $this->block($fakeRequest, $ticket);
        }

        Log::warning("Laravel B: Unknown action '{$action}' received for Ticket {$request->ticket_id}");
        return response()->json(['message' => 'Aksi tidak dikenali'], 400);
    }
}
