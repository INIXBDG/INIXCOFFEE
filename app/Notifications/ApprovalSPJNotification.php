<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ApprovalSPJNotification extends Notification implements ShouldBroadcast
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

    /**
     * @var \App\Models\User $notifiable
     */
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
        $type = $this->data['approval_manager'] == 1 ? 'Menyetujui SPJ' : 'Menolak SPJ';

        return new BroadcastMessage([
            'user' => auth()->user()?->username ?? 'System',
            'message' => [
                'tipe'             => $type,
                'nama_lengkap'     => $this->to,
                'tanggal_berangkat' => $this->data['tanggal_berangkat'],
                'tanggal_pulang'   => $this->data['tanggal_pulang'],
                'alasan'           => $this->data['alasan'],
                'durasi'           => $this->data['durasi'],
                'tujuan'           => $this->data['tujuan'],
            ],
            'path'   => $this->path,
            'status' => 'unread',
        ]);
    }

    public function toArray($notifiable): array
    {
        $type = $this->data['approval_manager'] == 1 ? 'Menyetujui SPJ' : 'Menolak SPJ';

        return [
            'user' => auth()->user()?->username ?? 'System',
            'message' => [
                'tipe'             => $type,
                'nama_lengkap'     => $this->to,
                'tanggal_berangkat' => $this->data['tanggal_berangkat'],
                'tanggal_pulang'   => $this->data['tanggal_pulang'],
                'alasan'           => $this->data['alasan'],
                'durasi'           => $this->data['durasi'],
                'tujuan'           => $this->data['tujuan'],
            ],
            'path'   => $this->path,
            'status' => 'unread',
        ];
    }
}
