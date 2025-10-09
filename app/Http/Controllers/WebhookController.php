<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tickets;
use App\Models\WhatsappUser;
use App\Models\Karyawan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('Webhook received:', $request->all());
        if ($request->isMethod('get')) {
            return response()->json(['status' => 'ok', 'message' => 'Webhook is alive and listening!']);
        }
        // GANTI DENGAN TOKEN DARI FONNTE ANDA
        $fonnteToken = env('FONNTE_API_KEY'); 
        
        if ($request->token !== $fonnteToken) {
            return response()->json(['status' => 'error', 'message' => 'Invalid token'], 401);
        }

        $sender = $request->sender;
        $message = strtolower(trim($request->message));
        
        $whatsappUser = karyawan::where('whatsapp', $sender)->first();
        if (!$whatsappUser) {
            return response()->json(['status' => 'ok', 'message' => 'User not registered']);
        }
        $pic = $whatsappUser;

        if (strpos($message, '/') !== 0) {
             return response()->json(['status' => 'ok', 'message' => 'Not a command']);
        }

        $parts = explode(' ', $message, 3);
        $command = ltrim($parts[0], '/');
        $ticketId = $parts[1] ?? null;
        $extraInfo = $parts[2] ?? '';

        if (!$ticketId) {
            return $this->replyToGroup("Perintah tidak valid. Format: `/{perintah} {id_tiket}`");
        }

        $ticket = Tickets::where('ticket_id', strtoupper($ticketId))->first();
        if (!$ticket) {
            return $this->replyToGroup("Tiket dengan ID `{$ticketId}` tidak ditemukan.");
        }

        $ticketController = new TicketController();
        $responseMessage = '';

        switch ($command) {
            case 'terima':
            case 'open':
                if ($ticket->status !== 'Baru') {
                    $responseMessage = "Tiket `{$ticketId}` sudah diproses oleh {$ticket->pic}.";
                    break;
                }
                $ticketController->accept($ticket, $pic);
                $responseMessage = "✅ Tiket `{$ticketId}` telah diterima dan ditangani oleh *{$pic->nama}*.";
                break;

            case 'selesai':
            case 'close':
                // Implementasi logika finish, panggil method dari TicketController jika sudah di-refactor
                $ticket->update(['status' => 'Selesai', 'keterangan' => $extraInfo ?: 'Selesai via WhatsApp']);
                $responseMessage = "✅ Tiket `{$ticketId}` telah diselesaikan oleh *{$pic->nama}*.";
                break;

            default:
                $responseMessage = "Perintah `/{$command}` tidak dikenali.";
                break;
        }
        
        $this->replyToGroup($responseMessage);
        return response()->json(['status' => 'ok']);
    }

    private function replyToGroup($message)
    {
        Http::withHeaders([
            'Authorization' => env('FONNTE_API_TOKEN'), // Ambil dari .env
        ])->post('https://api.fonnte.com/send', [
            'target'  => '120363418574215044@g.us', // Ambil dari .env
            'message' => $message,
        ]);
    }
}
