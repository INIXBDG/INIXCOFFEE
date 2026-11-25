<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Notifications\Messages\BroadcastMessage;

class WebinarAdmSalesNotification extends Notification implements ShouldBroadcast
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

    private function payload(): array
    {
        return [
            'user' => auth()->user()?->username ?? 'System',
            'message' => [
                'tipe'            => 'Reminder Webinar Adm Sales',
                'year_mapping_id' => $this->data['year_mapping_id'],
                'nama_webinar'    => $this->data['nama_webinar'],
                'tema'            => $this->data['tema'],
                'tanggal'         => $this->data['tanggal'],
            ],
            'path'   => $this->path,
            'status' => 'unread',
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->payload());
    }

    public function toArray($notifiable): array
    {
        return $this->payload();
    }
}