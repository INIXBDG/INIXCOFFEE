<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CutiExchangeNotification extends Notification
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
                'tanggal_awal' => $this->data['tanggal_awal'],
                'tanggal_akhir' => $this->data['tanggal_akhir'],
                'jenis_cuti' => $this->data['tipe'],
                'durasi' => $this->data['durasi'],
            ],
            'path' => $this->path,
            'status' => 'unread',
        ];
    }
}
