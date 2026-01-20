<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KegiatanNotification extends Notification
{
    use Queueable;
    protected $data;
    protected $path;

    public function __construct(array $data, string $path)
    {
        $this->data = $data;
        $this->path = $path;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function broadcastAs(): string
    {
        return 'notifikasi-event';
    }


    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'message' => [
                'tipe'    => 'Notif Kegiatan',
                'kegiatan' => $this->data['nama_kegiatan'] ?? '-',
                'lama_kegiatan'    => $this->data['lama_kegiatan'] ?? '-',
                'waktu_kegiatan' => $this->data['waktu_kegiatan'] ?? '-',
                'pic' => $this->data['pic'] ?? '-',
            ],
            'path'   => $this->path,
            'status' => 'unread',
        ]);
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => [
                'tipe'    => 'Notif Kegiatan',
                'kegiatan' => $this->data['nama_kegiatan'] ?? '-',
                'lama_kegiatan'    => $this->data['lama_kegiatan'] ?? '-',
                'waktu_kegiatan' => $this->data['waktu_kegiatan'] ?? '-',
                'pic' => $this->data['pic'] ?? '-',
            ],
            'path'   => $this->path,
            'status' => 'unread',
        ];
    }
}
