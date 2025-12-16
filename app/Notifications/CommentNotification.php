<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Queue\SerializesModels;

class CommentNotification extends Notification implements ShouldBroadcast
{
    use Queueable, InteractsWithSockets, SerializesModels;

    protected $comment;
    protected $url;
    protected $path;
    protected $receiverId; 

    public function __construct($comment, $url, $path, $receiverId)
    {
        $this->comment = $comment;
        $this->url = $url;
        $this->path = $path;
        $this->receiverId = $receiverId; 
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'user' => auth()->user()->username ?? 'System',
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
            'user' => auth()->user()->username ?? 'System',
            'message' => [
                'karyawan_key' => $this->comment->karyawan_key,
                'tipe' => 'komentar',
                'content' => $this->comment->content,
                'materi_key' => $this->comment->materi_key,
                'rkm_key' => $this->comment->rkm_key,
            ],
            'path' => $this->url,
            'status' => 'unread',
        ]);
    }

    public function broadcastOn()
    {
        return new PrivateChannel('notifikasi.' . $this->receiverId);
    }

    public function broadcastAs()
    {
        return 'notifikasi-event';
    }
}
