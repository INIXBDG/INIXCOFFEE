<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Notifications\Messages\BroadcastMessage;

class PenukaranSouvenirNotification extends Notification implements ShouldBroadcast
{
    use Queueable, InteractsWithSockets;

    protected $data;
    protected $path;
    protected $type;
    protected $receiverId;

    /**
     * @param array $data Data detail penukaran (nama peserta, barang, dll)
     * @param string $path URL tujuan saat notifikasi diklik
     * @param string $type Jenis notifikasi (misal: 'Penukaran Souvenir')
     * @param int $receiverId ID User penerima (untuk channel broadcast)
     */
    public function __construct($data, $path, $type, $receiverId)
    {
        $this->data = $data;
        $this->path = $path;
        $this->type = $type;
        $this->receiverId = $receiverId;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function broadcastOn()
    {
        // Channel privat spesifik untuk user penerima
        return new PrivateChannel('notifikasi.' . $this->receiverId);
    }

    public function broadcastAs(): string
    {
        return 'notifikasi-event';
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'user' => auth()->user()?->username ?? 'System',
            'message' => [
                'tipe'              => $this->type,
                'nama_peserta'      => $this->data['nama_peserta'],
                'souvenir_lama'     => $this->data['souvenir_lama'],
                'souvenir_baru'     => $this->data['souvenir_baru'],
                'tanggal_tukar'     => $this->data['tanggal_tukar'],
            ],
            'path'   => $this->path,
            'status' => 'unread',
        ]);
    }

    public function toArray($notifiable): array
    {
        return [
            'user' => auth()->user()?->username ?? 'System',
            'message' => [
                'tipe'              => $this->type,
                'nama_peserta'      => $this->data['nama_peserta'],
                'souvenir_lama'     => $this->data['souvenir_lama'],
                'souvenir_baru'     => $this->data['souvenir_baru'],
                'tanggal_tukar'     => $this->data['tanggal_tukar'],
            ],
            'path'   => $this->path,
            'status' => 'unread',
        ];
    }
}
