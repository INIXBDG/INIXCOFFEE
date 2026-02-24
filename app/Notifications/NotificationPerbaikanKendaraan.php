<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Broadcasting\PrivateChannel;

class NotificationPerbaikanKendaraan extends Notification
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
        return ['database', 'broadcast', 'App\Channels\WebPushChannel'];
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
        return new BroadcastMessage([
            'user' => auth()->user()?->username ?? 'System',
            'message' => [
                'tipe' => $this->type,
                'tipe_barang' => $this->data['tipe'] ?? 'Unknown',
                'id_karyawan' => $this->data['id_karyawan'] ?? null,
                'tanggal_pengajuan' => $this->data['tanggal_pengajuan'] ?? now()->format('Y-m-d H:i'),
            ],
            'path' => $this->path ?? '#',
            'status' => 'unread',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function toArray($notifiable): array
    {
        return [
            'user' => auth()->user()?->username ?? 'System',
            'message' => [
                'tipe' => $this->type,
                'tipe_barang' => $this->data['tipe'] ?? 'Unknown',
                'id_karyawan' => $this->data['id_karyawan'] ?? null,
                'tanggal_pengajuan' => $this->data['tanggal_pengajuan'] ?? now()->format('Y-m-d H:i'),
            ],
            'path' => $this->path,
            'status' => 'unread',
            'type' => 'pengajuan_barang',
        ];
    }
}
