<?php

namespace App\Notifications;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PoReminder extends Notification
{
    use Queueable, InteractsWithSockets;

    protected $data;


    public function __construct(array $data)
    {
        $this->data = $data;
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
        return new BroadcastMessage(
            [
                'user' => auth()->user()?->username ?? 'System',
                'message' => [
                    'tipe'        => 'PO Reminder',
                    'perusahaan'     => $this->data['perusahaan'],
                    'materi'     => $this->data['materi'],
                    'periode'     => $this->data['periode'],
                ],
                'status' => 'unread',
            ]
        );
    }
    public function toArray($notifiable): array
    {
        return [
            'user' => auth()->user()?->username ?? 'System',
            'message' => [
                'tipe'        => 'PO Reminder',
                'perusahaan'  => $this->data['perusahaan'],
                'materi'      => $this->data['materi'],
                'periode'     => $this->data['periode'],
            ],
            'status' => 'unread',
        ];
    }
}
