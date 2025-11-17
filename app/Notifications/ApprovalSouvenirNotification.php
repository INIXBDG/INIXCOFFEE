<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovalSouvenirNotification extends Notification
{
    use Queueable;

    protected $data;
    protected $path;
    protected $to;
    protected $type;

    /**
     * Create a new notification instance.
     */
    public function __construct($data, $path, $to, $type)
    {
        $this->data = $data;
        $this->path = $path;
        $this->to = $to;
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
                'tipe' => $this->type,  // cth: 'Pengajuan Souvenir Disetujui'
                'nama_lengkap' => $this->to, // cth: Nama Customer Care
                'tanggal' => $this->data['tanggal'],
                'status' => $this->data['status'], // cth: 'Disetujui GM, Menunggu Pencairan Finance'
            ],
            'path' => $this->path, // cth: '/pengajuansouvenir'
            'status' => 'unread',
        ];
    }
}
