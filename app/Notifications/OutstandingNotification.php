<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Notifications\Messages\BroadcastMessage;

class OutstandingNotification extends Notification implements ShouldBroadcast
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
                'tipe'              => 'Outstanding Payment',
                'nama_lengkap'      => $this->data['nama_lengkap'] ?? auth()->user()?->karyawan?->nama_lengkap,
                'nama_perusahaan'   => $this->data['nama_perusahaan'],
                'nama_materi'       => $this->data['nama_materi'],
                'due_date'          => $this->data['due_date'],
                'status_pembayaran' => $this->data['status_pembayaran'],
                'nominal'           => $this->data['nominal'] ?? null,
                'invoice'           => $this->data['invoice'] ?? '-',
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
                'tipe'              => 'Outstanding Payment',
                'nama_lengkap'      => $this->data['nama_lengkap'] ?? auth()->user()?->karyawan?->nama_lengkap,
                'nama_perusahaan'   => $this->data['nama_perusahaan'],
                'nama_materi'       => $this->data['nama_materi'],
                'due_date'          => $this->data['due_date'],
                'status_pembayaran' => $this->data['status_pembayaran'],
                'nominal'           => $this->data['nominal'] ?? null,
                'invoice'           => $this->data['invoice'] ?? '-',
            ],
            'path'   => $this->path ?? '#',
            'status' => 'unread',
        ];
    }
}