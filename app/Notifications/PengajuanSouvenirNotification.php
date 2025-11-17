<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PengajuanSouvenirNotification extends Notification
{
    use Queueable;

    protected $data;
    protected $path;
    protected $type;

    /**
     * Create a new notification instance.
     */
    public function __construct($data, $path, $type)
    {
        $this->data = $data;
        $this->path = $path;
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast']; // Menggunakan 'database' dan 'broadcast'
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'user' => auth()->user()->username,
            'message' => [
                'tipe' => $this->type, // cth: 'Mengajukan Permintaan Souvenir'
                'tipe_barang' => $this->data['tipe'], // cth: 'Souvenir'
                'id_karyawan' => $this->data['id_karyawan'],
                'tanggal_pengajuan' => $this->data['tanggal_pengajuan'],
            ],
            'path' => $this->path, // cth: '/pengajuansouvenir'
            'status' => 'unread',
        ];
    }
}
