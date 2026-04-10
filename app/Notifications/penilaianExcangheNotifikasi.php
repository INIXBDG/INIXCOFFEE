<?php

namespace App\Notifications;

use App\Models\Karyawan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Notifications\Messages\BroadcastMessage;

class penilaianExcangheNotifikasi extends Notification implements ShouldBroadcast
{
    use Queueable, InteractsWithSockets;

    protected $comment;
    protected $path;
    protected $receiverId;

    public function __construct($comment, $path, $receiverId)
    {
        $this->comment = $comment;
        $this->path = $path;
        $this->receiverId = $receiverId;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        $user = auth()->user();

        return new BroadcastMessage([
            'user' => $user?->username ?? 'System',
            'message' => [
                'tipe' => 'Penilaian 360',
                'karyawan_key' => $this->comment->karyawan_key,
                'content' => $this->comment->content,
                'pengirim' => $user?->nama_lengkap ?? 'Sistem',
                'jabatan' => $user?->jabatan ?? null,
            ],
            'path' => $this->path ?? '#',
            'status' => 'unread',
        ]);
    }

    public function toArray($notifiable): array
    {
        $user = auth()->user();

        return [
            'user' => $user?->username ?? 'System',
            'message' => [
                'tipe' => 'Penilaian 360',
                'karyawan_key' => $this->comment->karyawan_key,
                'content' => $this->comment->content,
                'pengirim' => $user?->nama_lengkap ?? 'Sistem',
                'jabatan' => $user?->jabatan ?? null,
            ],
            'path' => $this->path ?? '#',
            'status' => 'unread',
        ];
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('notifikasi.' . $this->receiverId);
    }

    public function broadcastAs(): string
    {
        return 'notifikasi-event';
    }
}
