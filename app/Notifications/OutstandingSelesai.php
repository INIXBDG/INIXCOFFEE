<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Notifications\Messages\BroadcastMessage;

class OutstandingSelesai extends Notification implements ShouldBroadcast
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
                    'tipe'        => 'Pembayaran Outstanding Selesai',
                    'perusahaan'  => $this->data['perusahaan'],
                    'materi'      => $this->data['materi'],
                    'tgl_bayar'   => $this->data['tgl_bayar'],
                    'no_invoice'  => $this->data['no_invoice'],
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
                'tipe'        => 'Pembayaran Outstanding Selesai',
                'perusahaan'  => $this->data['perusahaan'],
                'materi'      => $this->data['materi'],
                'tgl_bayar'   => $this->data['tgl_bayar'],
                'no_invoice'  => $this->data['no_invoice'],
                'periode'     => $this->data['periode'],
            ],
            'status' => 'unread',
        ];
    }
}
