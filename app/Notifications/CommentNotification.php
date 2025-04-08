<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class CommentNotification extends Notification
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
        return ['database', 'broadcast']; // Menggunakan 'database' dan 'broadcast'
    }

    public function toArray($notifiable)
    {
        return [
            'user' => auth()->user()->username,
            'message' => [
                'karyawan_key' => $this->comment->karyawan_key,
                'tipe' => 'komentar',
                'content' => $this->comment->content,
                'materi_key' => $this->comment->materi_key,
                'rkm_key' => $this->comment->rkm_key,
            ],
            'path' => $this->path,
            'status' => 'unread',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'user' => auth()->user()->username,
            'message' => [
                'karyawan_key' => $this->comment->karyawan_key,
                'content' => $this->comment->content,
                'materi_key' => $this->comment->materi_key,
                'rkm_key' => $this->comment->rkm_key,
            ],
            'path' => $this->url,
            'status' => 'unread',
        ]);
    }
}

