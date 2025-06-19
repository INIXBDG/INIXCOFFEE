<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class noRecordExchangeNotification extends Notification
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
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'user'    => auth()->user()->username,
            'message' => $this->data,
            'path'    => $this->path ?? '#',
            'status'  => 'unread',
        ];
    }
}
