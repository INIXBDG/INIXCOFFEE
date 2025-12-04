<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ApprovalCutiNotification extends Notification implements ShouldBroadcast
{
    use Queueable, InteractsWithSockets;

    protected $data;
    protected $path;
    protected $to;
    protected $receiverId;

    public function __construct($data, $path, $to, $receiverId)
    {
        $this->data = $data;
        $this->path = $path;
        $this->to   = $to;
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
        $type = $this->data['approval_manager'] == 1 ? 'Menyetujui Cuti' : 'Menolak Cuti';

        return new BroadcastMessage([
            'user' => auth()->user()?->username ?? 'System',
            'message' => [
                'tipe'             => $type,
                'nama_lengkap'     => $this->to,
                'tanggal_awal'     => $this->data['tanggal_awal'],
                'tanggal_akhir'    => $this->data['tanggal_akhir'],
                'jenis_cuti'       => $this->data['tipe'],
                'durasi'           => $this->data['durasi'] . ' hari',
                'alasan_manager'   => $this->data['alasan_manager'] ?? '-',
            ],
            'path'   => $this->path ?? '#',
            'status' => 'unread',
        ]);
    }

    public function toArray($notifiable): array
    {
        $type = $this->data['approval_manager'] == 1 ? 'Menyetujui Cuti' : 'Menolak Cuti';

        return [
            'user' => auth()->user()?->username ?? 'System',
            'message' => [
                'tipe'             => $type,
                'nama_lengkap'     => $this->to,
                'tanggal_awal'     => $this->data['tanggal_awal'],
                'tanggal_akhir'    => $this->data['tanggal_akhir'],
                'jenis_cuti'       => $this->data['tipe'],
                'durasi'           => $this->data['durasi'] . ' hari',
                'alasan_manager'   => $this->data['alasan_manager'] ?? '-',
            ],
            'path'   => $this->path ?? '#',
            'status' => 'unread',
        ];
    }
}
