<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Notifications\Messages\BroadcastMessage;

class TicketNotification extends Notification implements ShouldBroadcast
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
            'Ticketing Baru' => [
                'user' => $this->data['nama_karyawan'] ?? 'System',
                'message' => [
                    'tipe'         => $this->status,
                    'nama_karyawan' => $this->data['nama_karyawan'] ?? null,
                    'divisi'       => $this->data['divisi'] ?? null,
                    'keperluan'    => $this->data['keperluan'] ?? null,
                    'kategori'     => $this->data['kategori'] ?? null,
                ],
                'path'   => $this->path,
                'status' => 'unread',
            ],
            default => [
                'user' => auth()->user()?->username ?? 'System',
                'message' => [
                    'tipe'             => $this->status,
                    'pic'              => $this->data['pic'] ?? null,
                    'tanggal_response' => $this->data['tanggal_response'] ?? $this->data['tanggal_selesai'] ?? null,
                    'jam_response'     => $this->data['jam_response'] ?? $this->data['jam_selesai'] ?? null,
                    'keterangan'       => $this->data['keterangan'] ?? null,
                    'status'           => $this->data['status'] ?? null,
                ],
                'path'   => $this->path,
                'status' => 'unread',
            ],
        };
    }
}
