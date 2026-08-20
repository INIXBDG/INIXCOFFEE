<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Broadcasting\PrivateChannel;

class KelengkapanDataKelasNotification extends Notification implements ShouldQueue
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
        return new BroadcastMessage([
            'user' => 'System',
            'message' => [
                'tipe' => $this->type,
                'kelas' => $this->data['kelas_list'] ?? [],
                'jumlah_kelas' => $this->data['jumlah_kelas'] ?? 0,
                'tanggal_terdekat' => $this->data['tanggal_terdekat'] ?? null,
            ],
            'path' => $this->path ?? '#',
            'status' => 'unread',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function toArray($notifiable): array
    {
        return [
            'user' => 'System',
            'message' => [
                'tipe' => $this->type,
                'kelas' => $this->data['kelas_list'] ?? [],
                'jumlah_kelas' => $this->data['jumlah_kelas'] ?? 0,
                'tanggal_terdekat' => $this->data['tanggal_terdekat'] ?? null,
            ],
            'path' => $this->path,
            'status' => 'unread',
            'type' => 'kelengkapan_data_kelas',
        ];
    }
}