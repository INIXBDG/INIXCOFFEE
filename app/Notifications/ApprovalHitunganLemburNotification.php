<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ApprovalHitunganLemburNotification extends Notification implements ShouldBroadcast
{
    use Queueable, InteractsWithSockets;

    protected $data;
    protected $path;
    protected $type;
    protected $receiverId;

    public function __construct($data, $path, $type, $receiverId)
    {
        $this->data = $data;
        $this->path = $path;
        $this->type = $type;
        $this->receiverId = $receiverId;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function broadcastOn()
    {
        return new PrivateChannel('notifikasi.' . $this->receiverId);
    }

    public function broadcastAs()
    {
        return 'notifikasi-event';
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'user' => auth()->user()?->username ?? 'System',
            'message' => [
                'tipe'          => $this->type,
                'alasan'        => $this->data['alasan'],
                'nama_karyawan' => $this->data['nama_karyawan'],
            ],
            'path'   => $this->path,
            'status' => 'unread',
        ]);
    }

    public function toArray($notifiable)
    {
        return [
            'user' => auth()->user()?->username ?? 'System',
            'message' => [
                'tipe'          => $this->type,
                'alasan'        => $this->data['alasan'],
                'nama_karyawan' => $this->data['nama_karyawan'],
            ],
            'path'   => $this->path,
            'status' => 'unread',
        ];
    }
}
