<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Notifications\Messages\BroadcastMessage;

class UpdateLaporanPenjualan extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable, InteractsWithSockets;

    protected $data;
    protected $path;
    protected $receiverId;

    public function __construct(array $data, string $path, $receiverId)
    {
        $this->data = $data;
        $this->path = $path;
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
                'tipe'               => 'Perubahan Payment Advance',
                'karyawan'           => $this->data['karyawan'] ?? 'Tidak diketahui',
                'id_rkm'             => $this->data['id_rkm'] ?? null,
                'rkm'                => $this->data['rkm'] ?? '-',
                'waktu'              => $this->data['waktu'] ?? '-',
                'milik'              => $this->data['milik'] ?? '-',
                'waktu_perubahan'    => $this->data['waktu_perubahan'] ?? now()->toDateTimeString(),
                'perubahan'          => $this->data['perubahan'] ?? [],
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
                'tipe'               => 'Perubahan Payment Advance',
                'karyawan'           => $this->data['karyawan'] ?? 'Tidak diketahui',
                'id_rkm'             => $this->data['id_rkm'] ?? null,
                'rkm'                => $this->data['rkm'] ?? '-',
                'waktu'              => $this->data['waktu'] ?? '-',
                'milik'              => $this->data['milik'] ?? '-',
                'waktu_perubahan'    => $this->data['waktu_perubahan'] ?? now()->toDateTimeString(),
                'perubahan'          => $this->data['perubahan'] ?? [],
            ],
            'path'   => $this->path,
            'status' => 'unread',
        ];
    }
}
