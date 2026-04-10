<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Notifications\Messages\BroadcastMessage;

class IdeInovasiNotification extends Notification implements ShouldBroadcast
{
    use Queueable, InteractsWithSockets;

    protected $data;
    protected $path;
    protected $status;
    protected $receiverId;

    public function __construct($data, $path, $status, $receiverId)
    {
        $this->data = $data;
        $this->path = $path;
        $this->status = $status;
        $this->receiverId = $receiverId;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /** @var \App\Models\User $notifiable */
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
        return new BroadcastMessage(['message' => $this->toArray($notifiable)]);
    }

    public function toArray($notifiable): array
    {
        return match ($this->status) {
            'Ide Inovasi Baru' => [
                // Mengambil nama user yang sedang login (pembuat ide)
                'user' => auth()->user()?->username ?? auth()->user()?->name ?? 'System',
                'message' => [
                    'tipe'  => $this->status,
                    'judul' => $this->data['judul'] ?? null,
                ],
                'path'   => $this->path,
                'status' => 'unread',
            ],
            default => [
                'user' => 'System',
                'message' => [
                    'tipe' => 'Unknown',
                ],
                'path'   => $this->path,
                'status' => 'unread',
            ],
        };
    }
}
