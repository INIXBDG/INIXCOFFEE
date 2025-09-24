<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class PengajuanbarangNotification extends Notification
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
                'tipe_barang' => $this->data['tipe'],
                'id_karyawan' => $this->data['id_karyawan'],
                'tanggal_pengajuan' => $this->data['tanggal_pengajuan'],
            ],
            'path' => $this->path,
            'status' => 'unread',
        ];
    }
}
