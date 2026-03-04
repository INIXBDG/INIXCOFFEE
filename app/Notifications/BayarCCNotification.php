<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Notifications\Messages\BroadcastMessage;

class BayarCCNotification extends Notification implements ShouldBroadcast
{
    use Queueable, InteractsWithSockets;

    protected $data;
    protected $path;
    protected $receiverId;

    public function __construct($data, $path, $receiverId)
    {
        $this->data = $data;
        $this->data['total_harga_rupiah'] = $this->data['harga'] * $this->data['kurs'];

        $this->data['mata_uang_symbol'] = match ($this->data['mata_uang'] ?? '') {
            'Rupiah'        => 'Rp.',
            'Dollar'        => '$',
            'Poundsterling' => '£',
            'Swiss Franc'   => 'CHF',
            'Euro'          => '€',
            default         => '',
        };

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

    public function broadcastAs()
    {
        return 'notifikasi-event';
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'user' => auth()->user()?->username,
            'message' => [
                'tipe'            => 'Bayar CC',
                'invoice'         => $this->data['invoice'],
                'perusahaan'      => $this->data['perusahaan'],
                'tanggal_pengajuan' => $this->data['tanggal_pengajuan'],
                'materi'          => $this->data['materi'],
                'mata_uang'       => $this->data['mata_uang_symbol'],
                'pax'             => $this->data['pax'],
                'harga_dollar'    => $this->data['harga'],
                'harga_rupiah'    => $this->data['total_harga_rupiah'],
                'cc'              => $this->data['cc'],
            ],
            'path'   => $this->path,
            'status' => 'unread',
        ]);
    }

    public function toArray($notifiable): array
    {
        return [
            'user' => auth()->user()?->username,
            'message' => [
                'tipe'            => 'Bayar CC',
                'invoice'         => $this->data['invoice'],
                'perusahaan'      => $this->data['perusahaan'],
                'tanggal_pengajuan' => $this->data['tanggal_pengajuan'],
                'materi'          => $this->data['materi'],
                'mata_uang'       => $this->data['mata_uang_symbol'],
                'pax'             => $this->data['pax'],
                'harga_dollar'    => $this->data['harga'],
                'harga_rupiah'    => $this->data['total_harga_rupiah'],
                'cc'              => $this->data['cc'],
            ],
            'path'   => $this->path,
            'status' => 'unread',
        ];
    }
}
