<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Notifications\Messages\BroadcastMessage;

class RKMUpdateNotification extends Notification implements ShouldBroadcast
{
    use Queueable, InteractsWithSockets;

    protected $data;
    protected $path;
    protected $receiverId;

    public function __construct($data, $path, $receiverId)
    {
        $this->data = $data;
        $this->path = $path;
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
        $user = $this->data['user_update'] ?? 'System';

        $statusMap = [1 => 'Biru', 2 => 'Hitam', 0 => 'Merah'];
        $perubahanText = [];
        foreach ($this->data['perubahan'] ?? [] as $field => $values) {
            $old = $values['old'] ?? '-';
            $new = $values['new'] ?? '-';
            if ($field === 'status') {
                $old = $statusMap[$old] ?? $old;
                $new = $statusMap[$new] ?? $new;
            }
            $perubahanText[] = "$field: $old → $new";
        }

        $pesan = $user . ' telah mengubah ' . implode(', ', $perubahanText);
        return new BroadcastMessage([
            'user' => $user,
            'message' => [
                'tipe'            => 'RKM Update',
                'nama_materi'     => $this->data['nama_materi'],
                'nama_perusahaan' => $this->data['nama_perusahaan'],
                'detail'          => $pesan,
            ],
            'path'   => $this->path,
            'status' => 'unread',
        ]);
    }

    public function toArray($notifiable): array
    {
        $user = $this->data['user_update'] ?? 'System';

        $statusMap = [1 => 'Biru', 2 => 'Hitam', 0 => 'Merah'];
        $perubahanText = [];
        foreach ($this->data['perubahan'] ?? [] as $field => $values) {
            $old = $values['old'] ?? '-';
            $new = $values['new'] ?? '-';
            if ($field === 'status') {
                $old = $statusMap[$old] ?? $old;
                $new = $statusMap[$new] ?? $new;
            }
            $perubahanText[] = "$field: $old → $new";
        }

        $pesan = $user . ' telah mengubah ' . implode(', ', $perubahanText);

        return [
            'user' => $user,
            'message' => [
                'tipe'            => 'RKM Update',
                'nama_materi'     => $this->data['nama_materi'],
                'nama_perusahaan' => $this->data['nama_perusahaan'],
                'detail'          => $pesan,
            ],
            'path'   => $this->path,
            'status' => 'unread',
        ];
    }
}
