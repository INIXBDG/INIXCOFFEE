<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Notifications\Messages\BroadcastMessage;

class CateringNotification extends Notification implements ShouldBroadcast
{
    use Queueable, InteractsWithSockets;

    protected $data;
    protected $path;
    protected $type;
    protected $receiverId;
    protected $senderUsername;
    protected $senderNamaLengkap;

    public function __construct($data, $path, $type, $receiverId, $senderUsername = null, $senderNamaLengkap = null)
    {
        $this->data = $data;
        $this->path = $path;
        $this->type = $type;
        $this->receiverId = $receiverId;
        $this->senderUsername = $senderUsername ?? 'System';
        $this->senderNamaLengkap = $senderNamaLengkap ?? 'Sistem';
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function broadcastOn()
    {
        return new PrivateChannel('notifikasi.' . $this->receiverId);
    }

    public function broadcastAs(): string
    {
        return 'notifikasi-event';
    }

    public function toArray($notifiable): array
    {
        return [
            'user' => $this->senderUsername,
            'message' => [
                'tipe' => $this->type,
                'nama_lengkap' => $this->data['nama_lengkap'] ?? $this->senderNamaLengkap,
                'tipe_barang' => $this->data['tipe'],
                'tanggal_pengajuan' => $this->data['tanggal_pengajuan'],
            ],
            'path' => $this->path ?? '#',
            'status' => 'unread',
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
