<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Broadcasting\PrivateChannel;

class KondisiKendaraan extends Notification
{
    use Queueable;
    protected $data;
    protected $path;
    public $receiverId;

    public function __construct(array $data, string $path, $receiverId)
    {
        $this->data = $data;
        $this->path = $path;
        $this->receiverId = $receiverId;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function broadcastOn(): PrivateChannel
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
            'message' => [
                'tipe'    => 'Laporan Kondisi Kendaraan',
                'user' => $this->data['user'] ?? '-',
                'kendaraan' => $this->data['kendaraan'] ?? '-',
                'tanggal_pemeriksaan' => $this->data['tanggal_pemeriksaan'] ?? '-',
            ],
            'path'   => $this->path,
            'status' => 'unread',
        ]);
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => [
                'tipe'    => 'Laporan Kondisi Kendaraan',
                'user' => $this->data['user'] ?? '-',
                'kendaraan' => $this->data['kendaraan'] ?? '-',
                'tanggal_pemeriksaan' => $this->data['tanggal_pemeriksaan'] ?? '-',
            ],
            'path'   => $this->path,
            'status' => 'unread',
        ];
    }
}
