<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovalCutiNotification extends Notification
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
            $type = 'Menyetujui';
        } elseif ($this->data['approval_manager'] == 2) {
            $type = 'Menolak';
        }

        return [
            'user' => auth()->user()->username,
            'message' => [
                'tipe' => $type,  // Use the dynamically determined $type
                'nama_lengkap' => $this->to,
                'tanggal_awal' => $this->data['tanggal_awal'],
                'tanggal_akhir' => $this->data['tanggal_akhir'],
                'jenis_cuti' => $this->data['tipe'],
                'durasi' => $this->data['durasi'],
                'approval_manager' => $this->data['approval_manager'],
                'alasan_manager' => $this->data['alasan_manager'],
            ],
            'path' => $this->path,
            'status' => 'unread',
        ];
    }
}
