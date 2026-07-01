<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PembelianHrNotification extends Notification implements ShouldBroadcast
{
    use Queueable, InteractsWithSockets;

    protected $barang;
    protected $periode;
    protected $path;
    protected $receiverId;

    public function __construct($barang, $periode, $path, $receiverId)
    {
        $this->barang = $barang;
        $this->periode = $periode;
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

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'user' => 'System',
            'message' => [
                'tipe'              => 'Pengajuan rencana pembelian',
                'pesan'       => 'Periode '. $this->periode. ' segera berakhir, '.'Segera ajukan rencana pembelian '.$this->barang. ' ke pengajuan barang',
            ],
            'path'   => $this->path ?? '#',
            'status' => 'unread',
        ]);
    }

    public function toArray($notifiable): array
    {
        return [
            'user' => 'System',
            'message' => [
                'tipe'              => 'Pengajuan rencana pembelian',
                'pesan'       => 'Periode '. $this->periode. ' segera berakhir, '.'Segera ajukan rencana pembelian '.$this->barang. ' ke pengajuan barang',
            ],
            'path'   => $this->path ?? '#',
            'status' => 'unread',
        ];
    }
}
