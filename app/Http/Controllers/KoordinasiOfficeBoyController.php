<?php

namespace App\Http\Controllers;

use App\Models\karyawan;
use App\Models\KoordinasiOfficeBoy;
use App\Models\TrackingKoordinasiOfficeBoy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KoordinasiOfficeBoyController extends Controller
{
    private $botToken;
    private $groupId;

    public function __construct()
    {
        $this->botToken = '8637052174:AAFSALsROZZSHz-fr2PM0IWe-EsYatdYXvI';
        $this->groupId = '-5410138806';

        $this->middleware('auth')->only([
            'index', 'getData', 'store', 'update', 'delete'
        ]);
    }

    public function index()
    {
        $officeBoy = karyawan::where('jabatan', 'Office Boy')->get();
        return view('office.koordinasiOfficeBoy.index', compact('officeBoy'));
    }

    public function getData()
    {
        $koordinasis = KoordinasiOfficeBoy::with('tracking', 'pembuat', 'karyawan')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Data koordinasi office boy',
            'data' => $koordinasis,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_tugas' => 'required|string',
            'karyawan' => 'required|exists:karyawans,id',
            'deadline' => 'required|date',
            'catatan' => 'nullable',
        ]);

        if (!in_array(Auth()->user()->jabatan, ['HRD', 'GM', 'Office Boy', 'Customer Care'])) {
            abort(401);
        }

        try {
            $createdBy = Auth()->user()->id;

            $koordinasi = KoordinasiOfficeBoy::create([
                'nama_tugas' => $request->nama_tugas,
                'karyawan'   => $request->karyawan,
                'deadline'   => $request->deadline,
                'catatan'    => $request->catatan,
                'created_by' => $createdBy,
                'status'     => 'Menunggu Konfirmasi',
            ]);
            $koordinasi->refresh();

            TrackingKoordinasiOfficeBoy::create([
                'koordinasi_id' => $koordinasi->id,
                'status'        => 'Koordinasi OB dibuat',
                'updated_by'    => $createdBy
            ]);

            $this->sendCreateNotification($koordinasi);

            return back()->with('success', 'Koordinasi berhasil dibuat');

        } catch (\Exception $e) {
            Log::error('Error Koordinasi OB:', [
                'message' => $e->getMessage()
            ]);

            return back()->with('error', 'Terjadi kesalahan');
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'id'         => 'required|exists:koordinasi_office_boys,id',
            'nama_tugas' => 'required|string',
            'karyawan'   => 'required|exists:karyawans,id',
            'deadline'   => 'required|date',
            'catatan'    => 'nullable',
        ]);

        try {
            $koordinasi = KoordinasiOfficeBoy::findOrFail($request->id);
            $koordinasi->update([
                'nama_tugas' => $request->nama_tugas,
                'karyawan'   => $request->karyawan,
                'deadline'   => $request->deadline,
                'catatan'    => $request->catatan,
            ]);

            TrackingKoordinasiOfficeBoy::create([
                'koordinasi_id' => $koordinasi->id,
                'status'        => 'Koordinasi OB diupdate',
                'updated_by'    => Auth()->user()->id
            ]);

            Log::info('Koordinasi berhasil diupdate dari website', ['id' => $koordinasi->id]);

            return back()->with('success', 'Koordinasi berhasil di Update');
        } catch (\Exception $e) {
            Log::error('Error saat mengupdate koordinasi:', ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan');
        }
    }

    public function delete(string $id)
    {
        try {
            $koordinasi = KoordinasiOfficeBoy::findOrFail($id);

            TrackingKoordinasiOfficeBoy::create([
                'koordinasi_id' => $koordinasi->id,
                'status'        => 'Koordinasi OB dihapus',
                'updated_by'    => Auth()->user()->id
            ]);

            $this->sendDeleteNotification($koordinasi);
            $koordinasi->delete();

            Log::info('Koordinasi berhasil dihapus', ['id' => $id]);

            return response()->json(['message' => 'Data berhasil dihapus']);
        } catch (\Exception $e) {
            Log::error('Error saat menghapus koordinasi:', ['message' => $e->getMessage()]);
            return response()->json(['message' => 'Terjadi kesalahan'], 500);
        }
    }

    public function updateStatus($action, $id)
    {
        try {
            $this->processStatusUpdate($action, $id, Auth()->user()->id);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error update status dari website:', ['message' => $e->getMessage()]);
            return response()->json(['success' => false], 500);
        }
    }

    public function updateFromTelegram(Request $request)
    {
        try {
            $action = $request->action;
            $id     = $request->id;

            Log::info('Webhook Telegram menerima request update', [
                'action' => $action,
                'id'     => $id
            ]);

            $this->processStatusUpdate($action, $id, null);

            Log::info('Update status dari Telegram berhasil diproses', [
                'action' => $action,
                'id'     => $id
            ]);

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('Error saat memproses update dari Telegram', [
                'message' => $e->getMessage(),
                'action'  => $request->action ?? null,
                'id'      => $request->id ?? null
            ]);
            return response()->json(['success' => false], 500);
        }
    }

    private function processStatusUpdate(string $action, int $id, $userId = null)
    {
        $koordinasi = KoordinasiOfficeBoy::findOrFail($id);
        $namaOb = karyawan::findOrFail($koordinasi->karyawan);

        $updatedBy = $userId ?? $koordinasi->karyawan;

        $telegramPayload = [];

        if ($action === 'terima' && $koordinasi->status === 'Menunggu Konfirmasi') {
            $koordinasi->update(['status' => 'Dikerjakan']);

            TrackingKoordinasiOfficeBoy::create([
                'koordinasi_id' => $koordinasi->id,
                'status'        => 'Tugas sedang dikerjakan',
                'updated_by'    => $updatedBy
            ]);

            $telegramPayload = [
                'title'        => '✅ Tugas Diterima',
                'id_pengajuan' => $koordinasi->id,
                'ob_name'      => $namaOb->nama_lengkap,
                'status'       => 'Dikerjakan'
            ];

        } elseif ($action === 'selesai' && $koordinasi->status !== 'Selesai') {
            $koordinasi->update(['status' => 'Selesai']);

            TrackingKoordinasiOfficeBoy::create([
                'koordinasi_id' => $koordinasi->id,
                'status'        => 'Tugas selesai',
                'updated_by'    => $updatedBy
            ]);

            $telegramPayload = [
                'title'        => '🏁 Tugas Selesai',
                'id_pengajuan' => $koordinasi->id,
                'ob_name'      => $namaOb->nama_lengkap,
                'status'       => 'Selesai'
            ];
        }

        if (!empty($telegramPayload)) {
            $this->sendStatusUpdateTelegram($telegramPayload);
        }
    }

    private function sendCreateNotification(KoordinasiOfficeBoy $koordinasi)
    {
        try {
            $koordinasi->load(['pembuat', 'karyawan']);

            $payload = [
                'title'        => '🔔 Koordinasi OB Baru',
                'id_pengajuan' => $koordinasi->id,
                'nama_tugas'   => $koordinasi->nama_tugas,
                'creator_name' => $koordinasi->pembuat?->nama_lengkap ?? 'System',
                'ob_name'      => $koordinasi->karyawan?->nama_lengkap ?? 'Unknown',
                'deadline'     => $koordinasi->deadline,
                'status'       => $koordinasi->status,
                'catatan'      => $koordinasi->catatan ?? '-',
                'show_action_buttons' => true
            ];

            $this->sendToTelegram($payload);

        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi Telegram (create)', [
                'id' => $koordinasi->id ?? null,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function sendDeleteNotification(KoordinasiOfficeBoy $koordinasi)
    {
        $namaOb = karyawan::findOrFail($koordinasi->karyawan);
        $payload = [
            'title'        => '🗑️ Tugas ' . $koordinasi->nama_tugas . ' dihapus',
            'id_pengajuan' => $koordinasi->id,
            'ob_name'      => $namaOb->nama_lengkap,
            'status'       => 'Dihapus'
        ];

        $this->sendStatusUpdateTelegram($payload);
    }

    private function sendToTelegram(array $data)
    {
        $time = isset($data['deadline'])
            ? \Carbon\Carbon::parse($data['deadline'])->format('d M Y, H:i')
            : '-';

        $message = "*{$this->escapeMarkdownV2($data['title'])}*\n\n" .
            "ID: `#{$this->escapeMarkdownV2($data['id_pengajuan'])}`\n" .
            "Dibuat: {$this->escapeMarkdownV2($data['creator_name'])}\n" .
            "Office Boy: {$this->escapeMarkdownV2($data['ob_name'])}\n" .
            "Tugas: {$this->escapeMarkdownV2($data['nama_tugas'])}\n" .
            "Deadline: {$this->escapeMarkdownV2($time)}\n" .
            "Status: {$this->escapeMarkdownV2($data['status'])}\n" .
            "──────────────────────\n" .
            "Catatan: {$this->escapeMarkdownV2($data['catatan'] ?? '-')}\n\n" .
            "Silahkan buka detail di aplikasi\\.";

        $detailUrl = 'https://coffee.inixindobdg.co.id//office/koordinasi-ob/detail/' . $data['id_pengajuan'];

        $inlineKeyboard = [
            [
                [
                    'text' => '✅ Terima',
                    'callback_data' => "terima:{$data['id_pengajuan']}"
                ],
                [
                    'text' => '🏁 Selesai',
                    'callback_data' => "selesai:{$data['id_pengajuan']}"
                ],
            ],
            [
                [
                    'text' => '🔍 Lihat Detail',
                    'callback_data' => "detail:{$data['id_pengajuan']}"
                ]
            ]
        ];

        $response = Http::timeout(10)->post(
            "https://api.telegram.org/bot{$this->botToken}/sendMessage",
            [
                'chat_id' => $this->groupId,
                'text' => $message,
                'parse_mode' => 'MarkdownV2',
                'reply_markup' => [
                    'inline_keyboard' => $inlineKeyboard
                ]
            ]
        );

        $result = $response->json();

        if (!$response->successful() || !($result['ok'] ?? false)) {
            Log::error('Telegram API Error', [
                'id' => $data['id_pengajuan'],
                'status' => $response->status(),
                'body' => $result,
                'message_preview' => substr($message, 0, 200)
            ]);
        } else {
            Log::info('✅ Telegram message berhasil dikirim', [
                'id' => $data['id_pengajuan']
            ]);
        }
    }

    private function escapeMarkdownV2($text)
    {
        return str_replace(
            ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'],
            ['\\_', '\\*', '\\[', '\\]', '\\(', '\\)', '\\~', '\\`', '\\>', '\\#', '\\+', '\\-', '\\=', '\\|', '\\{', '\\}', '\\.', '\\!'],
            $text
        );
    }

    private function sendStatusUpdateTelegram(array $data)
    {
        $message = "*{$this->escapeMarkdownV2($data['title'])}*\n\n" .
                "ID: `#{$data['id_pengajuan']}`\n" .
                "Office Boy: {$this->escapeMarkdownV2($data['ob_name'])}\n" .
                "Status: {$this->escapeMarkdownV2($data['status'])}\n\n";

        $detailUrl = 'https://coffee.inixindobdg.co.id//office/koordinasi-ob/detail/' . $data['id_pengajuan'];

        if ($data['status'] === 'Dikerjakan') {
            $replyMarkup = [
                'inline_keyboard' => [
                    [
                        [
                            'text' => '🔍 Lihat Detail',
                            'callback_data' => "detail:{$data['id_pengajuan']}"
                        ]
                    ],
                    [
                        ['text' => '🏁 Selesai', 'callback_data' => "selesai:{$data['id_pengajuan']}"]
                    ]
                ]
            ];
        } else {
            $replyMarkup = [
                'inline_keyboard' => [
                    [
                        [
                            'text' => '🔍 Lihat Detail',
                            'callback_data' => "detail:{$data['id_pengajuan']}"
                        ]
                    ]
                ]
            ];
        }

        $response = Http::timeout(10)->post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
            'chat_id'      => $this->groupId,
            'text'         => $message,
            'parse_mode'   => 'MarkdownV2',
            'reply_markup' => $replyMarkup
        ]);

        $result = $response->json();

        if (!$response->successful() || !($result['ok'] ?? false)) {
            Log::error('Gagal mengirim status update Telegram', [
                'id' => $data['id_pengajuan'],
                'status' => $response->status(),
                'body' => $result,
            ]);
        } else {
            Log::info('✅ Status update Telegram berhasil dikirim', [
                'id' => $data['id_pengajuan'],
                'title' => $data['title']
            ]);
        }
    }

    public function telegramDetail($id)
    {
        $koordinasi = KoordinasiOfficeBoy::with('tracking', 'karyawan', 'pembuat')
                        ->findOrFail($id);

        return view('office.koordinasiOfficeBoy.detail', compact('koordinasi'));
    }

    private function sendDetailToTelegram(int $id)
    {
        $koordinasi = KoordinasiOfficeBoy::with(['tracking', 'karyawan', 'pembuat'])
            ->findOrFail($id);

        $deadline = \Carbon\Carbon::parse($koordinasi->deadline)->format('d M Y, H:i');

        $message = "*📋 Detail Tugas \\#{$koordinasi->id}*\n\n" .
            "Tugas: {$this->escapeMarkdownV2($koordinasi->nama_tugas)}\n" .
            "Dibuat: {$this->escapeMarkdownV2($koordinasi->pembuat?->nama_lengkap ?? 'System')}\n" .
            "Office Boy: {$this->escapeMarkdownV2($koordinasi->karyawan?->nama_lengkap ?? 'Unknown')}\n" .
            "Deadline: {$this->escapeMarkdownV2($deadline)}\n" .
            "Status: {$this->escapeMarkdownV2($koordinasi->status)}\n" .
            "Catatan: {$this->escapeMarkdownV2($koordinasi->catatan ?? '-')}\n\n" .
            "*Riwayat:*\n";

        foreach ($koordinasi->tracking as $track) {
            $waktu = $track->created_at->format('d M H:i');
            $message .= "• {$this->escapeMarkdownV2($track->status)} — {$this->escapeMarkdownV2($waktu)}\n";
        }

        $response = Http::timeout(10)->post(
            "https://api.telegram.org/bot{$this->botToken}/sendMessage",
            [
                'chat_id' => $this->groupId,
                'text' => $message,
                'parse_mode' => 'MarkdownV2',
            ]
        );

        if (!$response->successful()) {
            Log::error('Gagal mengirim detail Telegram', [
                'id' => $id,
                'body' => $response->json(),
            ]);
        }
    }
    
    public function webhook(Request $request)
    {
        $update = $request->all();

        if (!isset($update['callback_query']['data'])) {
            return response()->json(['ok' => true]);
        }

        try {
            $callbackQueryId = $update['callback_query']['id'];
            $data = $update['callback_query']['data'];
            [$action, $id] = explode(':', $data);

            Log::info("Callback dari Telegram diterima", ['action' => $action, 'id' => $id]);

            if ($action === 'detail') {
                $koordinasi = KoordinasiOfficeBoy::findOrFail((int)$id);
                $namaOb = karyawan::findOrFail($koordinasi->karyawan);

                $deadline = \Carbon\Carbon::parse($koordinasi->deadline)->format('d M Y, H:i');

                $popupText = "📋 {$koordinasi->nama_tugas}\n\n" .
                    "OB: {$namaOb->nama_lengkap}\n" .
                    "Deadline: {$deadline}\n" .
                    "Status: {$koordinasi->status}\n" .
                    "Catatan: " . ($koordinasi->catatan ?? '-');

                Http::post("https://api.telegram.org/bot{$this->botToken}/answerCallbackQuery", [
                    'callback_query_id' => $callbackQueryId,
                    'text' => $popupText,
                    'show_alert' => true,
                ]);

                return response()->json(['ok' => true]);
            } else {
                $this->processStatusUpdate($action, (int)$id, null);
            }

            // Wajib jawab callback biar tombol gak "loading" terus
            Http::post("https://api.telegram.org/bot{$this->botToken}/answerCallbackQuery", [
                'callback_query_id' => $callbackQueryId,
            ]);

            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            Log::error('Error di webhook Telegram', ['message' => $e->getMessage()]);
            return response()->json(['ok' => true]);
        }
    }
}