<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovalSPJNotification extends Notification
{
    use Queueable;

    protected $data;
    protected $path;
    protected $to;

    public function __construct($data, $path, $to)
    {
        $this->data = $data;
        $this->path = $path;
        $this->to = $to;
    }

    public function via($notifiable)
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
        // Determine the type based on approval statuses
        $type = '';

        if ($this->data['approval_manager'] == 1) {
            $type = 'Menyetujui SPJ';
        } elseif ($this->data['approval_manager'] == 2) {
            $type = 'Menolak SPJ';
        }

        return [
            'user' => auth()->user()->username,
            'message' => [
                'tipe' => $type,  // Use the dynamically determined $type
                'nama_lengkap' => $this->to,
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
