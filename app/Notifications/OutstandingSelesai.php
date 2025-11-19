<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OutstandingSelesai extends Notification
{
    use Queueable;
    protected $data;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => [
                'tipe' => 'Pembayaran Outstanding Selesai',
                'perusahaan' => $this->data['perusahaan'],
                'materi' => $this->data['materi'],
                'tgl_bayar' => $this->data['tgl_bayar'],
                'no_invoice' => $this->data['no_invoice'],
                'periode' => $this->data['periode'],
            ],
            'status' => 'unread,'
        ];
    }
}
