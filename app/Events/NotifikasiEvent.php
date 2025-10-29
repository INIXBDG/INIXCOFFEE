<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class NotifikasiEvent implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $user;
    public $message;

    public function __construct($user, $message)
    {
        $this->user = $user;
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('notifikasi.' . $this->user->id);
    }

    public function broadcastAs()
    {
        return 'notifikasi-event';
    }
}
