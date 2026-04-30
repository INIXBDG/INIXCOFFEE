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
        return ['database', 'broadcast'];
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
