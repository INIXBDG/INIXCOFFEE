<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class PreorderNotification extends Notification implements ShouldQueue
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
            'user'    => $this->data['pembuat'] ?? 'System',
            'message' => [
                'tipe'    => 'Preorder Modul',
                'noModul' => $this->data['no_modul'] ?? '-',
                'type'    => $this->data['type'] ?? '-',
                'pembuat' => $this->data['pembuat'] ?? '-',
            ],
            'path'   => $this->path,
            'status' => 'unread',
        ]);
    }

    public function toArray($notifiable): array
    {
        return [
            'user'    => $this->data['pembuat'] ?? 'System',
            'message' => [
                'tipe'    => 'Preorder Modul',
                'noModul' => $this->data['no_modul'] ?? '-',
                'type'    => $this->data['type'] ?? '-',
                'pembuat' => $this->data['pembuat'] ?? '-',
            ],
            'path'   => $this->path,
            'status' => 'unread',
        ];
    }
}
