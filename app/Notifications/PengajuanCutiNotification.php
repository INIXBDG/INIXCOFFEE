<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Notifications\Messages\BroadcastMessage;

class PengajuanCutiNotification extends Notification implements ShouldBroadcast
{
    use Queueable, InteractsWithSockets;

    protected $data;
    protected $path;
    protected $type;
    protected $receiverId;

    public function __construct($data, $path, $type, $receiverId)
    {
        $this->data       = $data;
        $this->path       = $path;
        $this->type       = $type;
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
                'tipe'          => $this->type,
                'nama_lengkap'  => $this->data['nama_lengkap'] ?? auth()->user()?->karyawan?->nama_lengkap,
                'tanggal_awal'  => $this->data['tanggal_awal'],
                'tanggal_akhir' => $this->data['tanggal_akhir'],
                'jenis_cuti'    => $this->data['tipe'],
                'durasi'        => $this->data['durasi'] . ' hari',
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
                'tipe'          => $this->type,
                'nama_lengkap'  => $this->data['nama_lengkap'] ?? auth()->user()?->karyawan?->nama_lengkap,
                'tanggal_awal'  => $this->data['tanggal_awal'],
                'tanggal_akhir' => $this->data['tanggal_akhir'],
                'jenis_cuti'    => $this->data['tipe'],
                'durasi'        => $this->data['durasi'] . ' hari',
            ],
            'path'   => $this->path ?? '#',
            'status' => 'unread',
        ];
    }
}
