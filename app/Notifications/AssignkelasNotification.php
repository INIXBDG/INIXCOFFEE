<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Notifications\Messages\BroadcastMessage;

class AssignkelasNotification extends Notification implements ShouldBroadcast
{
    use Queueable, InteractsWithSockets;

    protected $data;
    protected $path;
    protected $receiverId;

    public function __construct($data, $path, $receiverId)
    {
        $this->data = $data;
        $this->path = $path;
        $receiverId = $receiverId;
    }

    public function via($notifiable): array
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
                'tipe'            => 'Assign Kelas',
                'nama_materi'     => $this->data['nama_materi'],
                'nama_perusahaan' => $this->data['nama_perusahaan'],
                'role'            => $this->data['role'] ?? null,
            ],
            'path'   => $this->path,
            'status' => 'unread',
        ]);
    }

    public function toArray($notifiable): array
    {
        return [
            'user' => auth()->user()?->username ?? 'System',
            'message' => [
                'tipe'            => 'Assign Kelas',
                'nama_materi'     => $this->data['nama_materi'],
                'nama_perusahaan' => $this->data['nama_perusahaan'],
                'role'            => $this->data['role'] ?? null,
            ],
            'path'   => $this->path,
            'status' => 'unread',
        ];
    }
}
