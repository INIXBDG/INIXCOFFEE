<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LemburNotification extends Notification
{
    use Queueable;

    protected $data;
    protected $path;
    // protected $to;
    protected $type;

    public function __construct($data, $path, $type)
    {
        $this->data = $data;
        $this->path = $path;
        // $this->to = $to;
        $this->type = $type;
    }

    public function via($notifiable)
    {
        return ['database']; // Menggunakan 'database' dan 'broadcast'
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // Determine the type based on approval statuses

        return [
            'user' => auth()->user()->username,
            'message' => [
                'tipe' => $this->type,  // Use the dynamically determined $type
                'id_karyawan' => $this->data['id_karyawan'],
                'tanggal_lembur' => $this->data['tanggal_lembur'],
                'waktu_lembur' => $this->data['waktu_lembur'],
                'uraian_tugas' => $this->data['uraian_tugas'],
            ],
            'path' => $this->path,
            'status' => 'unread',
        ];
    }
}
