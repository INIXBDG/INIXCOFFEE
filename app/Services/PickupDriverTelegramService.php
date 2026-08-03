<?php

namespace App\Services;

use App\Models\karyawan;
use App\Models\pickupDriver;
use App\Models\TrackingPickupDriver;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class PickupDriverTelegramService
{
    private string $botToken;
    private string $chatId;

    public function __construct()
    {
        $this->botToken = config('services.telegram_pickup_driver.bot_token');
        $this->chatId = config('services.telegram_pickup_driver.chat_id');
    }

    public function sendTelegramNotification(array $coordinationData): void
    {
        $formatted = $this->formatCoordinationMessage($coordinationData);
        $this->sendTelegramMessage($formatted);
    }

    public function processTerimaFromTelegram(int $id): array
    {
        $pickupDriver = pickupDriver::with(['detailPickupDriver', 'pembuat', 'karyawan'])->findOrFail($id);

        if ($pickupDriver->status_apply != 0) {
            return ['success' => false, 'message' => '⚠️ Status koordinasi sudah berubah.'];
        }

        $detail = $pickupDriver->detailPickupDriver->first();
        if (!$detail) {
            return ['success' => false, 'message' => 'Detail pickup tidak ditemukan.'];
        }

        $statusDriver = match ($detail->tipe) {
            'Penjemputan' => 'Sedang Menjemput',
            'Pengantaran' => 'Sedang Mengantarkan',
            default => 'Diterima',
        };

        $pickupDriver->status_driver = $statusDriver;
        $pickupDriver->status_apply = 1;
        $pickupDriver->save();

        $driver = $pickupDriver->karyawan;
        $detailTipe = $pickupDriver->detailPickupDriver->pluck('tipe')->toArray();

        $lokasis = $pickupDriver->detailPickupDriver->pluck('lokasi')->toArray();
        $tanggals = $pickupDriver->detailPickupDriver->pluck('tanggal_keberangkatan')->map(fn($t) => Carbon::parse($t)->format('d M Y'))->toArray();
        $waktus = $pickupDriver->detailPickupDriver->pluck('waktu_keberangkatan')->map(fn($t) => substr($t, 0, 5))->toArray();
        
        $detailsArr = $pickupDriver->detailPickupDriver->pluck('detail')->toArray(); 

        TrackingPickupDriver::create([
            'pickup_driver_id' => $pickupDriver->id,
            'status' => 'Koordinasi diterima melalui Telegram, status menjadi ' . $statusDriver . ' dengan kendaraan ' . ($pickupDriver->kendaraan ?? '-'),
            'diubah_oleh' => $driver->id ?? null,
        ]);

        $this->sendTelegramNotification([
            'title' => '🔄 Status Diperbarui',
            'id_pengajuan' => $pickupDriver->id,
            'creator_name' => $pickupDriver->pembuat?->nama_lengkap ?? '-', 
            'driver_name' => $driver->nama_lengkap ?? '-',
            'budget' => $pickupDriver->budget,
            'tanggal_pembuatan' => $pickupDriver->created_at, 
            'status_text' => $statusDriver,
            'status_apply' => $pickupDriver->status_apply,
            'tipe' => $detailTipe,
            'lokasi' => $lokasis,
            'tanggal' => $tanggals,
            'waktu' => $waktus,
            'detail' => $detailsArr,
            'log_text' => null,
        ]);

        return ['success' => true, 'message' => '✅ Koordinasi berhasil diterima!'];
    }

    public function getSummaryTextAndButtons(int $id): array
    {
        $pickupDriver = pickupDriver::with(['karyawan', 'pembuat', 'detailPickupDriver'])->findOrFail($id);

        $budgetText = $pickupDriver->budget ? 'Rp ' . number_format($pickupDriver->budget, 0, ',', '.') : 'Tidak Ada Budget';
        $time = Carbon::parse($pickupDriver->created_at)->format('d M Y, H:i');
        $status = $pickupDriver->status_driver ?? 'Menunggu';

        $detailsText = '';
        $tipes = $pickupDriver->detailPickupDriver->pluck('tipe')->toArray();
        $lokasis = $pickupDriver->detailPickupDriver->pluck('lokasi')->toArray();
        $tanggals = $pickupDriver->detailPickupDriver->pluck('tanggal_keberangkatan')->map(fn($t) => Carbon::parse($t)->format('d M Y'))->toArray();
        $waktus = $pickupDriver->detailPickupDriver->pluck('waktu_keberangkatan')->map(fn($t) => substr($t, 0, 5))->toArray();

        foreach ($tipes as $i => $tipe) {
            $lokasi = $lokasis[$i] ?? '-';
            $tanggal = $tanggals[$i] ?? '-';
            $waktu = $waktus[$i] ?? '-';
            $detailsText .= "• <b>{$tipe}</b>\n  {$lokasi}\n  {$tanggal} | {$waktu}\n\n";
        }

        $messageBody = "ID: <code>#{$pickupDriver->id}</code>\n" . 'Dibuat: ' . ($pickupDriver->pembuat?->nama_lengkap ?? '-') . "\n" . 'Driver: ' . ($pickupDriver->karyawan?->nama_lengkap ?? '-') . "\n" . "Budget: {$budgetText}\n" . "Waktu: {$time}\n" . "Status: {$status}\n" . "──────────────\n" . ($detailsText ? "<b>Rincian Perjalanan:</b>\n{$detailsText}" : '');

        $buttons = [[['text' => '🔍 Lihat Detail', 'callback_data' => "detail_{$pickupDriver->id}"]]];
        $buttons[] = $pickupDriver->status_apply == 0 ? [['text' => '✅ Terima', 'callback_data' => "terima_{$pickupDriver->id}"]] : [['text' => '✅ Sudah Diterima', 'callback_data' => "sudah_terima_{$pickupDriver->id}"]];

        return [
            'text' => "<b>🆕 Koordinasi Driver</b>\n──────────────\n" . $messageBody,
            'buttons' => $buttons,
        ];
    }

    public function getDetailMessage(int $id): string
    {
        $pickupDriver = pickupDriver::with(['karyawan', 'pembuat', 'detailPickupDriver', 'biayaTransportasi', 'Tracking'])->findOrFail($id);

        $uangKepakai = $pickupDriver->biayaTransportasi->sum('harga') ?? 0;
        $sisaBudget = ($pickupDriver->budget ?? 0) - $uangKepakai;

        $lines = ["<b>DETAIL LENGKAP #{$pickupDriver->id}</b>", '──────────────', '<b>Driver:</b> ' . ($pickupDriver->karyawan?->nama_lengkap ?? '-'), '<b>Pembuat:</b> ' . ($pickupDriver->pembuat?->nama_lengkap ?? '-'), '<b>Kendaraan:</b> ' . ($pickupDriver->kendaraan ?? 'Belum dipilih'), '<b>Status:</b> ' . ($pickupDriver->status_driver ?? '-'), '<b>Budget:</b> ' . ($pickupDriver->budget ? 'Rp ' . number_format($pickupDriver->budget, 0, ',', '.') : 'Tidak Ada'), '<b>Terpakai:</b> ' . ($uangKepakai > 0 ? 'Rp ' . number_format($uangKepakai, 0, ',', '.') : 'Rp 0')];

        if ($pickupDriver->budget) {
            $sisaText = $sisaBudget < 0 ? '<b>Rp ' . number_format($sisaBudget, 0, ',', '.') . '</b>' : 'Rp ' . number_format($sisaBudget, 0, ',', '.');
            $lines[] = '<b>Sisa:</b> ' . $sisaText;
        }

        if ($pickupDriver->KM_awal || $pickupDriver->KM_akhir) {
            $lines[] = '<b>KM:</b> ' . ($pickupDriver->KM_awal ?? '-') . ' → ' . ($pickupDriver->KM_akhir ?? '-');
        }

        $lines[] = '──────────────';
        $lines[] = '<b>Rincian Rute:</b>';

        if ($pickupDriver->detailPickupDriver->isNotEmpty()) {
            foreach ($pickupDriver->detailPickupDriver as $index => $d) {
                $tanggal = Carbon::parse($d->tanggal_keberangkatan)->format('d M Y');
                $waktu = substr($d->waktu_keberangkatan, 0, 5);
                $lines[] = $index + 1 . ". <b>{$d->tipe}</b>: {$d->lokasi}";
                $lines[] = "   {$tanggal} | {$waktu}";
            }
        } else {
            $lines[] = '   <i>Tidak ada detail rute.</i>';
        }

        return implode("\n", $lines);
    }

    public function startKepulanganInput(int $pickupId, int $chatId): void
    {
        Cache::put("telegram_kepulangan_{$chatId}_{$pickupId}", $pickupId, now()->addMinutes(15));

        Http::timeout(10)->post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
            'chat_id' => $chatId,
            'text' => "<b>🏁 Input Kepulangan #{$pickupId}</b>\n──────────────\n" .
                "Silakan <b>reply pesan ini</b> dengan format:\n\n" .
                "<code>KM Awal-KM Akhir</code>\n\n" .
                "Contoh: <code>12000-12050</code>",
            'parse_mode' => 'HTML',
        ]);
    }

    public function processKepulanganReply(int $chatId, string $text, string $repliedText): void
    {
        if (!preg_match('/Input Kepulangan #(\d+)/', $repliedText, $match)) {
            return;
        }
        $pickupId = (int) $match[1];

        if (!Cache::has("telegram_kepulangan_{$chatId}_{$pickupId}")) {
            $this->sendPlainMessage($chatId, "⚠️ Sesi input sudah kedaluwarsa. Silakan klik tombol Selesaikan lagi.");
            return;
        }

        if (!preg_match('/^\s*(\d+)\s*-\s*(\d+)\s*$/', $text, $m)) {
            $this->sendPlainMessage($chatId, "⚠️ Format salah. Mohon reply dengan format:\n<code>KM Awal-KM Akhir</code>\nContoh: <code>12000-12050</code>");
            return;
        }

        $kmAwal = (int) $m[1];
        $kmAkhir = (int) $m[2];

        if ($kmAkhir < $kmAwal) {
            $this->sendPlainMessage($chatId, "⚠️ KM Akhir tidak boleh lebih kecil dari KM Awal. Silakan reply ulang.");
            return;
        }

        $pickup = pickupDriver::findOrFail($pickupId);
        $pickup->waktu_kepulangan = now()->format('H:i:s');
        $pickup->status_apply = 2;
        $pickup->status_driver = 'Selesai, Driver Ready';
        $pickup->KM_awal = $kmAwal;
        $pickup->KM_akhir = $kmAkhir;
        $pickup->save();

        TrackingPickupDriver::create([
            'pickup_driver_id' => $pickup->id,
            'status' => 'Kepulangan diinput melalui Telegram. KM: ' . $kmAwal . ' → ' . $kmAkhir,
            'diubah_oleh' => $pickup->id_karyawan,
        ]);

        Cache::forget("telegram_kepulangan_{$chatId}_{$pickupId}");

        $this->sendPlainMessage($chatId,
            "🎉 <b>Koordinasi Telah Selesai!</b>\n" .
            "──────────────\n" .
            "Hai! 👋 Saya <b>Inixindo Koordinasi Bot</b>.\n" .
            "Terima kasih sudah menyelesaikan tugas hari ini. Kerja bagus! 💪\n" .
            "Semoga aktivitas hari ini lancar, sampai ketemu di koordinasi berikutnya. 😊\n" .
            "──────────────\n" .
            "KM Awal : <b>{$kmAwal}</b>\n" .
            "KM Akhir : <b>{$kmAkhir}</b>\n" .
            "Jarak : <b>" . ($kmAkhir - $kmAwal) . " KM</b>"
        );
    }

    private function sendPlainMessage(int $chatId, string $text): void
    {
        Http::timeout(10)->post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ]);
    }

    public function answerCallback(string $callbackId, string $text = '', bool $showAlert = false): void
    {
        Http::post(
            "https://api.telegram.org/bot{$this->botToken}/answerCallbackQuery",
            array_filter([
                'callback_query_id' => $callbackId,
                'text' => $text ?: null,
                'show_alert' => $showAlert ?: null,
            ]),
        );
    }

    public function editMessage(int $chatId, int $messageId, string $text, array $inlineKeyboard = []): void
    {
        Http::post("https://api.telegram.org/bot{$this->botToken}/editMessageText", [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['inline_keyboard' => $inlineKeyboard]),
        ]);
    }

    private function formatCoordinationMessage(array $d): array
    {
        $id = $d['id_pengajuan'] ?? '-';
        $budget = !empty($d['budget']) ? 'Rp ' . number_format($d['budget'], 0, ',', '.') : 'Tidak Ada Budget';
        $time = isset($d['tanggal_pembuatan']) ? Carbon::parse($d['tanggal_pembuatan'])->format('d M Y, H:i') : '-';
        $statusApply = $d['status_apply'] ?? 0;
        $state = $d['state'] ?? null;

        $detailsText = $d['log_text'] ?? '';
        if (!$detailsText && !empty($d['tipe'])) {
            foreach ($d['tipe'] as $i => $tipe) {
                $lokasi = $d['lokasi'][$i] ?? '-';
                $tanggal = $d['tanggal'][$i] ?? '-';
                $waktu = $d['waktu'][$i] ?? '-';
                $info = $d['detail'][$i] ?? '-';
                $detailsText .= "• <b>{$tipe}</b>\n  {$lokasi}\n  {$tanggal} | {$waktu}\n  {$info}\n\n";
            }
        }

        $message = "ID: <code>#{$id}</code>\n" . 'Dibuat: ' . ($d['creator_name'] ?? '-') . "\n" . 'Driver: ' . ($d['driver_name'] ?? '-') . "\n" . "Budget: {$budget}\nWaktu: {$time}\nStatus: " . ($d['status_text'] ?? '-') . "\n" . "──────────────\n" . ($detailsText ? "<b>Rincian Perjalanan:</b>\n{$detailsText}" : '');

        $buttons = [['text' => '🔍 Lihat Detail', 'callback_data' => "detail_{$id}"]];
        if ($statusApply == 0 && !$state) {
            $buttons[] = ['text' => '✅ Terima', 'callback_data' => "terima_{$id}"];
        }
        if ($statusApply == 1) {
            $buttons[] = ['text' => '🏁 Selesaikan', 'callback_data' => "selesaikan_{$id}"];
        }

        return [
            'title' => $d['title'] ?? '🆕 Koordinasi Driver',
            'message' => $message,
            'buttons' => $buttons,
        ];
    }

    private function sendTelegramMessage(array $data): bool
    {
        return $this->postMessage($this->chatId, $data);
    }

    private function sendTelegramMessageToChat(int $chatId, array $data): bool
    {
        return $this->postMessage((string) $chatId, $data, useHtmlOnly: true);
    }

    private function postMessage(string $chatId, array $data, bool $useHtmlOnly = false): bool
    {
        try {
            $text = "<b>{$data['title']}</b>\n──────────────\n" . ($useHtmlOnly ? $data['message'] ?? '' : strip_tags($data['message'] ?? ''));

            $payload = [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ];

            if (!empty($data['buttons'])) {
                $keyboard = array_map(
                    fn($btn) => array_filter([
                        'text' => $btn['text'],
                        'url' => $btn['url'] ?? null,
                        'callback_data' => $btn['callback_data'] ?? null,
                    ]),
                    $data['buttons'],
                );

                $payload['reply_markup'] = json_encode(['inline_keyboard' => [$keyboard]]);
            }

            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$this->botToken}/sendMessage", $payload);

            if ($response->successful()) {
                Log::info('Telegram Pickup Driver terkirim', ['chat_id' => $chatId]);
                return true;
            }

            Log::error('Gagal mengirim Telegram Pickup Driver', ['response' => $response->body(), 'payload' => $payload]);
            return false;
        } catch (\Exception $e) {
            Log::error('Telegram Pickup Driver send error: ' . $e->getMessage());
            return false;
        }
    }
}
