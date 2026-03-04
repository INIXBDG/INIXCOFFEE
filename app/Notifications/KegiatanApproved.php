<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KegiatanApproved extends Notification
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
                'tipe'    => 'Status Approved',
                'status' => $this->data['status'] ?? '-',
                'kegiatan' => $this->data['kegiatan'] ?? '-',
            ],
            'path'   => $this->path,
            'status' => 'unread',
        ]);
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => [
                'tipe'    => 'Status Approved',
                'status' => $this->data['status'] ?? '-',
                'kegiatan' => $this->data['kegiatan'] ?? '-',
            ],
            'path'   => $this->path,
            'status' => 'unread',
        ];
    }
}
