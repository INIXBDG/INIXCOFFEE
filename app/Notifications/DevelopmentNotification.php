<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Notifications\Messages\BroadcastMessage;

class DevelopmentNotification extends Notification implements ShouldBroadcast
{
    use Queueable, InteractsWithSockets;

    protected $data;
    protected $path;
    protected $type;
    protected $receiverId;

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
        return new PrivateChannel('notifikasi.' . $this->receiverId);
    }

    public function broadcastAs(): string
    {
        return 'notifikasi-event';
    }

    /**
     * Helper private agar struktur data konsisten antara toBroadcast dan toArray
     */
    private function buildPayload()
    {
        return [
            'tipe'              => $this->type,
            'tipe_kategori'     => $this->data['tipe_kategori'] ?? 'Umum',
            'nama_item'         => $this->data['nama_item'] ?? '-',
            'status'            => $this->data['status'] ?? null,
            'id_user'           => $this->data['id_user'] ?? null,
            'tanggal_pengajuan' => $this->data['tanggal_pengajuan'] ?? now(),
            
            'harga'             => $this->data['harga'] ?? 0,
            
            // Sertifikasi
            'tanggal_ujian'     => $this->data['tanggal_ujian'] ?? null,
            'berlaku_dari'      => $this->data['berlaku_dari'] ?? null,
            'berlaku_sampai'    => $this->data['berlaku_sampai'] ?? null,

            // Pelatihan (UPDATE DISINI)
            'tanggal_mulai'     => $this->data['tanggal_mulai'] ?? null,
            'tanggal_selesai'   => $this->data['tanggal_selesai'] ?? null,
        ];
    }
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'user'    => auth()->user()?->username ?? 'System',
            'message' => $this->buildPayload(),
            'path'    => $this->path,
            'status'  => 'unread',
        ]);
    }

    public function toArray($notifiable): array
    {
        return [
            'user'    => auth()->user()?->username ?? 'System',
            'message' => $this->buildPayload(),
            'path'    => $this->path,
            'status'  => 'unread',
        ];
    }
}