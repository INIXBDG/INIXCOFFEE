<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PengajuanSPJNotification extends Notification
{
    use Queueable;

    protected $data;
    protected $path;
    protected $type;

    public function __construct($data, $path, $type)
    {
        $this->data = $data;
        $this->path = $path;
        $this->type = $type;
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
                'tipe' => $this->type,
                'tanggal_berangkat' => $this->data['tanggal_berangkat'],
                'tanggal_pulang' => $this->data['tanggal_pulang'],
                'alasan' => $this->data['alasan'],
                'durasi' => $this->data['durasi'],
                'tujuan' => $this->data['tujuan'],
            ],
            'path' => $this->path,
            'status' => 'unread',
        ];
    }
}
