<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Broadcasting\PrivateChannel;

class KoordinasiDriverNotifcation extends Notification
{
    use Queueable;

    protected $data;
    protected $path;
    protected $type;
    public $receiverId;

    public function __construct($data, $path, $type, $receiverId)
    {
        $this->data = $data;
        $this->path = $path;
        $this->type = $type;
        $this->receiverId = $receiverId;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast', 'webpush'];
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('notifikasi.' . $this->receiverId);
    }

    public function broadcastAs(): string
    {
        return 'notifikasi-event';
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        // Handle array tipe
        $tipeKoordinasi = is_array($this->data['tipe'] ?? []) ? implode(', ', $this->data['tipe']) : $this->data['tipe'] ?? 'Unknown';

        return new BroadcastMessage([
            'user' => auth()->user()?->username ?? 'System',
            'message' => [
                'tipe' => $this->type,
                'tipe_koordinasi' => $tipeKoordinasi,
                'id_karyawan' => $this->data['id_karyawan'] ?? null,
                'tanggal_pembuatan' => $this->data['tanggal_pembuatan'] ?? now()->format('Y-m-d H:i'),
            ],
            'path' => $this->path ?? '#',
            'status' => 'unread',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function toWebPush($notifiable)
    {
        $pengaju = \App\Models\karyawan::find($this->data['id_karyawan'] ?? null);
        $namaPengaju = $pengaju ? $pengaju->nama_lengkap : 'Sistem';

        $tipeKoordinasi = is_array($this->data['tipe'] ?? []) ? implode(', ', $this->data['tipe']) : $this->data['tipe'] ?? 'Barang';

        $tanggal = now()->format('d/m/Y');

        $payload = [
            'title' => $this->type,
            'body' => "Dari: {$namaPengaju}\nTipe: {$tipeKoordinasi}\nTanggal: {$tanggal}",
            'icon' => '/icons/icon-512x512.png',
            'badge' => '/icons/badge-96x96.png',

            'data' => [
                'path' => $this->path ?? '/office/pickup-driver/index',
                'id' => $this->data['id_pengajuan'] ?? null,
            ],

            'actions' => [['action' => 'view', 'title' => 'Lihat Detail'], ['action' => 'close', 'title' => 'Tutup']],

            'requireInteraction' => false,
            'silent' => false,
            'vibrate' => [100, 50, 100],
            'renotify' => true,
            'tag' => 'koordinasi-' . ($this->data['id_pengajuan'] ?? time()),
        ];

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('JSON encode error: ' . json_last_error_msg());
        }
        return $json;

        return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function toArray($notifiable): array
    {
        $tipeKoordinasi = is_array($this->data['tipe'] ?? []) ? implode(', ', $this->data['tipe']) : $this->data['tipe'] ?? 'Unknown';

        return [
            'user' => auth()->user()?->username ?? 'System',
            'message' => [
                'tipe' => $this->type,
                'tipe_koordinasi' => $tipeKoordinasi,
                'id_karyawan' => $this->data['id_karyawan'] ?? null,
                'tanggal_pembuatan' => $this->data['tanggal_pembuatan'] ?? now()->format('Y-m-d H:i'),
            ],
            'path' => $this->path,
            'status' => 'unread',
            'type' => 'koordinasi_driver',
        ];
    }
}
