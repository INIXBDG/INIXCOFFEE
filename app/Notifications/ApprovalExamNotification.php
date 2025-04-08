<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovalExamNotification extends Notification
{
    use Queueable;

    protected $data;
    protected $path;

    public function __construct($data, $path)
    {
        $this->data = $data;
        $this->path = $path;
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
                'tipe' => 'Menyetujui Pengajuan Exam',
                'invoice' => $this->data['invoice'],
                'perusahaan' => $this->data['perusahaan'],
                'tanggal_pengajuan' => $this->data['tanggal_pengajuan'],
                'materi' => $this->data['materi'],
            ],
            'path' => $this->path,
            'status' => 'unread',
        ];
    }
}
