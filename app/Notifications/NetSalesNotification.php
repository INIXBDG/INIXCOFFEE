<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Notifications\Messages\BroadcastMessage;

class NetSalesNotification extends Notification implements ShouldBroadcast
{
    use Queueable, InteractsWithSockets;

    protected $comment;
    protected $path;
    protected $receiverId;

    public function __construct($comment, $path, $receiverId)
    {
        $this->comment = $comment;
        $this->path    = $path;
        $this->receiverId = $receiverId;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function broadcastOn()
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
                'tipe'          => 'Net Sales',
                'nama_lengkap'  => $this->comment->nama_karyawan,
                'status'        => $this->comment->status,
                'alasan'        => $this->comment->alasan ?? '-',
            ],
            'path'   => $this->path ?? '#',
            'status' => 'unread',
        ]);
    }

    public function toArray($notifiable): array
    {
        return [
            'user' => auth()->user()?->username ?? 'System',
            'message' => [
                'tipe'          => 'Net Sales',
                'nama_lengkap'  => $this->comment->nama_karyawan,
                'status'        => $this->comment->status,
                'alasan'        => $this->comment->alasan ?? '-',
            ],
            'path'   => $this->path,
            'status' => 'unread',
        ];
    }
}
