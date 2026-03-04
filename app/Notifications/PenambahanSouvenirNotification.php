<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Notifications\Messages\BroadcastMessage;

class PenambahanSouvenirNotification extends Notification implements ShouldBroadcast
{
    use Queueable, InteractsWithSockets;

    protected $data;
    protected $path;
    protected $type;
    protected $receiverId;

    /**
     * Constructor dengan Receiver ID untuk Broadcast Channel unik
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
        // Channel privat spesifik per user
        return new PrivateChannel('notifikasi.' . $this->receiverId);
    }

    public function broadcastAs(): string
    {
        return 'notifikasi-event';
    }

    /**
     * Struktur Data untuk Realtime Broadcast (Pusher/WebSocket)
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'user' => auth()->user()?->username ?? 'System',
            'message' => $this->buildMessagePayload(),
            'path'   => $this->path,
            'status' => 'unread',
        ]);
    }

    /**
     * Struktur Data untuk Database Storage
     */
    public function toArray($notifiable): array
    {
        return [
            'user' => auth()->user()?->username ?? 'System',
            'message' => $this->buildMessagePayload(),
            'path'   => $this->path,
            'status' => 'unread',
        ];
    }

    /**
     * Helper untuk menyusun payload pesan agar konsisten di toArray dan toBroadcast
     */
    private function buildMessagePayload()
    {
        return [
            'tipe'              => $this->type,
            'tipe_barang'       => $this->data['tipe'] ?? 'Souvenir',
            'id_karyawan'       => $this->data['id_karyawan'] ?? null,
            'tanggal_pengajuan' => $this->data['tanggal_pengajuan'] ?? now(),

            // Data RKM
            'nama_rkm'          => $this->data['nama_rkm'] ?? '-',
            'rkm_start'         => $this->data['rkm_start'] ?? null,
            'rkm_end'           => $this->data['rkm_end'] ?? null,

            // Data Tambahan (Penerima & Barang)
            'penerima_nama'     => $this->data['penerima_nama'] ?? '-',
            'penerima_jabatan'  => $this->data['penerima_jabatan'] ?? '-',
            'detail_barang'     => $this->data['detail_barang'] ?? [],
        ];
    }
}
