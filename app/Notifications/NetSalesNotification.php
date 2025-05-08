<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NetSalesNotification extends Notification
{
    use Queueable;
    protected $comment;
    protected $url;
    protected $path;

    public function __construct($comment, $url, $path)
    {
        $this->comment = $comment;
        $this->url = $url;
        $this->path = $path;
    }
    public function via($notifiable)
    {
        return ['database']; // Menggunakan 'database' dan 'broadcast'
    }

    public function toArray($notifiable)
    {
        return [
            'user' => auth()->user()->username,
            'message' => [
                'status' => $this->comment->status,
                'tipe' => $this->comment->tipe,
                'nama_karyawan' => $this->comment->nama_karyawan,
                'alasan' => $this->comment->alasan,
            ],
            'path' => $this->path,
            'status' => 'unread',
        ];
    }
}
