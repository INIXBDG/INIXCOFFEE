<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovalLabSubsNotification extends Notification
{
    use Queueable;

    protected $data;
    protected $path;
    protected $to;
    protected $type;

    public function __construct($data, $path, $to, $type)
    {
        $this->data = $data;
        $this->path = $path;
        $this->to = $to;
        $this->type = $type;
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

        return [
            'user' => auth()->user()->username,
            'message' => [
                'tipe' => $this->type,
                'nama_lengkap' => $this->to,
                'tanggal' => $this->data['tanggal'],
                'status' => $this->data['status'],
            ],
            'path' => $this->path,
            'status' => 'unread',
        ];
    }
}
