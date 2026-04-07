<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

class TelegramKoordinasiDriverNotification extends Notification
{
    use Queueable;

    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function via($notifiable): array
    {
        return [TelegramChannel::class];
    }

    public function toTelegram($notifiable): TelegramMessage
    {
        $title = $this->data['title'] ?? 'Notifikasi Koordinasi Driver';
        $id = $this->data['id_pengajuan'] ?? '-';
        $creator = $this->data['creator_name'] ?? '-';
        $driver = $this->data['driver_name'] ?? '-';
        $budget = isset($this->data['budget']) && $this->data['budget'] ? 'Rp ' . number_format($this->data['budget'], 0, ',', '.') : 'Tidak Ada Budget';
        $time = isset($this->data['tanggal_pembuatan']) ? \Carbon\Carbon::parse($this->data['tanggal_pembuatan'])->format('d M Y, H:i') : '-';
        $status = $this->data['status_text'] ?? '-';
        $statusApply = $this->data['status_apply'] ?? 0;
        $logText = $this->data['log_text'] ?? null;

        $detailsText = '';
        if ($logText) {
            $detailsText = $logText;
        } else {
            $tipes = $this->data['tipe'] ?? [];
            $lokasis = $this->data['lokasi'] ?? [];
            $tanggals = $this->data['tanggal'] ?? [];
            $waktus = $this->data['waktu'] ?? [];
            $details = $this->data['detail'] ?? [];

            if (is_array($tipes) && count($tipes) > 0) {
                foreach ($tipes as $i => $tipe) {
                    $lokasi = $lokasis[$i] ?? '-';
                    $tanggal = $tanggals[$i] ?? '-';
                    $waktu = $waktus[$i] ?? '-';
                    $info = $details[$i] ?? '-';
                    $detailsText .= "• <b>{$tipe}</b>\n  {$lokasi}\n  {$tanggal} | {$waktu}\n  {$info}\n\n";
                }
            }
        }

        $messageText = "<b>{$title}</b>\n" . "──────────────\n" . "ID: <code>#{$id}</code>\n" . "Dibuat: {$creator}\n" . "Driver: {$driver}\n" . "Budget: {$budget}\n" . "Waktu: {$time}\n" . "Status: {$status}\n" . "──────────────\n" . ($detailsText ? "<b>Rincian Perjalanan:</b>\n{$detailsText}" : '');

        $msg = TelegramMessage::create();
        $msg->content($messageText)->parseMode('HTML');

        $baseUrl = url('/office/pickup-driver');
        $detailUrl = "{$baseUrl}/{$id}";

        $msg->button('🔍 Lihat Detail', $detailUrl);

        if ($statusApply == 0) {
            $terimaUrl = "{$baseUrl}/action/terima/{$id}";
            $msg->button('✅ Terima', $terimaUrl);
        } elseif ($statusApply == 1) {
            $selesaiUrl = "{$baseUrl}/action/selesaikan/{$id}";
            $msg->button('🏁 Selesaikan', $selesaiUrl);
        }
        return $msg;
    }
}
