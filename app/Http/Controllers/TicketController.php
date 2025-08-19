<?php

namespace App\Http\Controllers;

use App\Models\karyawan;
use App\Models\Tickets;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;
use DateTime;
// use Google_Service_Sheets_ValueRange;
class TicketController extends Controller
{
    private function normalizeTimestamp($timestamp) {
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

        // Kirim notifikasi Telegram ke admin (ganti dengan chat id admin Anda)
        $adminChatId = 2021670238;
        $message = "Ticket baru dibuat:\n"
            . "ID: {$ticket->id}\n"
            . "Nama Karyawan: {$ticket->nama_karyawan}\n"
            . "Divisi: {$ticket->divisi}\n"
            . "Kategori: {$ticket->kategori}\n"
            . "Keperluan: {$ticket->keperluan}\n"
            . "Detail Kendala: {$ticket->detail_kendala}";

        Telegram::sendMessage([
            'chat_id' => $adminChatId,
            'text' => $message,
        ]);

        $spreadsheetId = '1k_NRI52B-alnGVeLTGB8cecL3f1G-C7_WCVGnQQGe9Y';
        $range = 'Form Responses 1!A:H';  // Pastikan nama sheet dan kolom sesuai di Spreadsheet Anda
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
       
        // Panggil method appendValues
        $message = $this->appendValues($spreadsheetId, $range, $values);
        $ticket->update([
            'row' => $message
        ]);
        // return $message;

        

        return redirect()->route('tickets.index')->with('success', 'Tiket berhasil dibuat, akan segera diprovide. Terimakasih!');

        // return response()->json(['message' => 'Tiket berhasil dibuat, notifikasi terkirim, dan data disimpan di Google Sheets.']);
        // return response()->json(['message' => 'Tiket berhasil dibuat, akan segera diprovide. Terimakasih']);
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
                return null;
            }
        } catch (\Exception $e) {
            echo 'Message: ' . $e->getMessage();
            return null;
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

        if($divisi == 'IT Service Management'){
            $lembur = Tickets::with('karyawan')->latest()->get();
        }
        else{
            $lembur = Tickets::with('karyawan')->whereHas('karyawan', function($query) use ($user) {
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
        $tanggal_response = \Carbon\Carbon::now()->format('Y-m-d');
        $jam_response = \Carbon\Carbon::now()->format('H:i:s');

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
            ]
        ];
        $message = $this->updatedValues($spreadsheetId, $range, $values);

        return redirect()->route('tickets.index')->with('success', 'Tiket diterima.');
    }
    public function finish(Request $request, Tickets $ticket)
    {
        $tanggal_selesai = \Carbon\Carbon::now()->format('Y-m-d');
        $jam_selesai = \Carbon\Carbon::now()->format('H:i:s');

        $ticket->update([
            'status' => 'Selesai',
            'keterangan' => $request->keterangan,
            'tanggal_selesai' => $tanggal_selesai,
            'jam_selesai' => $jam_selesai,
        ]);

        $spreadsheetId = '1k_NRI52B-alnGVeLTGB8cecL3f1G-C7_WCVGnQQGe9Y';
        $range = 'Form Responses 1!M'.$ticket->row.':S'.$ticket->row;
        $values = [
            [
                $ticket->status,
                $tanggal_selesai,
                $jam_selesai,
                $request->keterangan,
                '',
                '',
                $ticket->kesulitan,
            ]
        ];
        $message = $this->updatedValues($spreadsheetId, $range, $values);

        return redirect()->route('tickets.index')->with('success', 'Tiket selesai.');
    }

    public function block(Request $request, Tickets $ticket)
    {
        $tanggal_selesai = \Carbon\Carbon::now()->format('Y-m-d');
        $jam_selesai = \Carbon\Carbon::now()->format('H:i:s');

        $ticket->update([
            'status' => 'Terkendala',
            'keterangan' => $request->keterangan,
            'tanggal_selesai' => $tanggal_selesai,
            'jam_selesai' => $jam_selesai,
        ]);

        $spreadsheetId = '1k_NRI52B-alnGVeLTGB8cecL3f1G-C7_WCVGnQQGe9Y';
        $range = 'Form Responses 1!M'.$ticket->row.':S'.$ticket->row;
        $values = [
            [
                $ticket->status,
                $tanggal_selesai,
                $jam_selesai,
                $request->keterangan,
                '',
                '',
                $ticket->kesulitan,
            ]
        ];
        $message = $this->updatedValues($spreadsheetId, $range, $values);

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

}
