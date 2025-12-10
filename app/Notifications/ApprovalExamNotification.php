<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ApprovalExamNotification extends Notification implements ShouldBroadcast
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
                'tipe'              => 'Menyetujui Pengajuan Exam',
                'nama_lengkap'      => $this->data['nama_lengkap'] ?? auth()->user()?->karyawan?->nama_lengkap,
                'invoice'           => $this->data['invoice'],
                'perusahaan'        => $this->data['perusahaan'],
                'tanggal_pengajuan' => $this->data['tanggal_pengajuan'],
                'materi'            => $this->data['materi'],
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
                'tipe'              => 'Menyetujui Pengajuan Exam',
                'nama_lengkap'      => $this->data['nama_lengkap'] ?? auth()->user()?->karyawan?->nama_lengkap,
                'invoice'           => $this->data['invoice'],
                'perusahaan'        => $this->data['perusahaan'],
                'tanggal_pengajuan' => $this->data['tanggal_pengajuan'],
                'materi'            => $this->data['materi'],
            ],
            'path'   => $this->path ?? '#',
            'status' => 'unread',
        ];
    }
}