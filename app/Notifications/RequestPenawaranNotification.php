<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Broadcasting\PrivateChannel;

class RequestPenawaranNotification extends Notification implements ShouldQueue
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
        return ['database', 'broadcast']; // Menambahkan broadcast
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
                'nama' => $this->data['nama_lengkap'] ?? 'Unknown',
                'instansi' => $this->data['instansi'] ?? 'Unknown',
                'tanggal_masuk' => now()->format('Y-m-d H:i'),
                'nama_materi' => $this->data['nama_materi'] ?? '-',
                'kelas' => $this->data['kelas'] ?? '-',
                'email' => $this->data['email'] ?? '-',
                'no_telepon' => $this->data['no_telepon'] ?? '-',
                'pax' => $this->data['pax'] ?? '-',
                'status' => 'Request Penawaran'
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
                'nama' => $this->data['nama_lengkap'] ?? 'Unknown',
                'instansi' => $this->data['instansi'] ?? 'Unknown',
                'tanggal_masuk' => now()->format('Y-m-d H:i'),
                'nama_materi' => $this->data['nama_materi'] ?? '-',
                'kelas' => $this->data['kelas'] ?? '-',
                'email' => $this->data['email'] ?? '-',
                'no_telepon' => $this->data['no_telepon'] ?? '-',
                'pax' => $this->data['pax'] ?? '-',
                'status' => 'Request Penawaran'
            ],
            'path' => $this->path,
            'status' => 'unread',
            'type' => 'request_penawaran',
        ];
    }
}
