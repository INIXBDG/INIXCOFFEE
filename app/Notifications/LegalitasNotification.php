<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LegalitasNotification extends Notification
{
    use Queueable, InteractsWithSockets;

    protected $data;
    protected $receiverId;
    protected $sender;
    /**
     * Create a new notification instance.
     */
    public function __construct($data, $path, $receiverId, $sender = "System")
    {
        $this->data = $data;
        $this->receiverId = $receiverId;
        $this->sender = $sender;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function broadcastOn()
    {
        return new PrivateChannel('notifikasi.' . $this->receiverId);
    }

    public function broadcastAs(): string
    {
        return 'notifikasi-event';
    }

    public function toArray($notifiable): array
    {
        return [
            'user' => $this->sender,
            'message' => [
                'tipe' => "Legalitas Reminder",
                'legalitas' => $this->data['legalitas'],
                'kadaluarsa' => $this->data['tanggal_berkahir'],
                'sisa_hari' => $this->data['sisa_hari'],
            ],
            'status' => 'unread',
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}