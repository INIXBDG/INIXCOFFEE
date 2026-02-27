<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OutstandingPaNotification extends Notification
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
                'tipe'    => 'Outstanding Lunas dengan data PA',
                'perusahaan' => $this->data['perusahaan'] ?? '-',
                'materi'    => $this->data['materi'] ?? '-',
                'periode' => $this->data['periode'] ?? '-',
            ],
            'path'   => $this->path,
            'status' => 'unread',
        ]);
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => [
                'tipe'    => 'Outstanding Lunas dengan data PA',
                'perusahaan' => $this->data['perusahaan'] ?? '-',
                'materi'    => $this->data['materi'] ?? '-',
                'periode' => $this->data['periode'] ?? '-',
            ],
            'path'   => $this->path,
            'status' => 'unread',
        ];
    }
}
