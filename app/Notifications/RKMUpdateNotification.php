<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
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
        return ['database', 'broadcast']; // Menggunakan 'database' dan 'broadcast'
    }

    public function toArray($notifiable)
    {
        return [
            'user' => auth()->user()->username,
            'message' => [
                'tipe' => 'RKM Update',
                'nama_materi' => $this->data['nama_materi'],
                'nama_perusahaan' => $this->data['nama_perusahaan'],
            ],
            'path' => $this->path,
            'status' => 'unread',
        ];
    }
}
