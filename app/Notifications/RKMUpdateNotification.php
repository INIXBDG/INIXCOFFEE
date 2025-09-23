<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RKMUpdateNotification extends Notification
{
    use Queueable;

    protected $data;
    protected $path;

    public function __construct($data, $path)
    {
        $this->data = $data;
        $this->path = $path;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        $user = $this->data['user_update'];

        $statusMap = [
            1 => 'Biru',
            2 => 'Hitam',
            0 => 'Merah',
        ];

        $perubahanText = [];
        foreach ($this->data['perubahan'] as $field => $values) {
            $old = $values['old'] ?? '-';
            $new = $values['new'] ?? '-';

            if ($field === 'status') {
                $old = $statusMap[$old] ?? $old;
                $new = $statusMap[$new] ?? $new;
            }

            $perubahanText[] = "{$field}: {$old} → {$new}";
        }

        $pesan = $user . ' telah mengubah ' . implode(', ', $perubahanText);

        return [
            'user' => $user,
            'message' => [
                'tipe' => 'RKM Update',
                'nama_materi' => $this->data['nama_materi'],
                'nama_perusahaan' => $this->data['nama_perusahaan'],
                'detail' => $pesan,
            ],
            'path' => $this->path,
            'status' => 'unread',
        ];
    }
}
