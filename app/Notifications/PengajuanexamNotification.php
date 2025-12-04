<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Notifications\Messages\BroadcastMessage;

class PengajuanexamNotification extends Notification implements ShouldBroadcast
{
    use Queueable, InteractsWithSockets;

    protected $data;
    protected $path;
    protected $receiverId;

    public function __construct($data, $path, $receiverId)
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
        return new PrivateChannel('private-notifikasi.' . $this->receiverId);
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
                'tipe'            => 'Pengajuan Exam',
                'nama_lengkap'    => $this->data['nama_lengkap'] ?? auth()->user()?->karyawan?->nama_lengkap,
                'nama_materi'     => $this->data['nama_materi'],
                'nama_perusahaan' => $this->data['nama_perusahaan'],
                'invoice'         => $this->data['invoice'] ?? '-',
                'tanggal'         => $this->data['tanggal_pengajuan'] ?? now()->format('d M Y'),
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
                'tipe'            => 'Pengajuan Exam',
                'nama_lengkap'    => $this->data['nama_lengkap'] ?? auth()->user()?->karyawan?->nama_lengkap,
                'nama_materi'     => $this->data['nama_materi'],
                'nama_perusahaan' => $this->data['nama_perusahaan'],
                'invoice'         => $this->data['invoice'] ?? '-',
                'tanggal'         => $this->data['tanggal_pengajuan'] ?? now()->format('d M Y'),
            ],
            'path'   => $this->path ?? '#',
            'status' => 'unread',
        ];
    }
}
