<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class ServerTimeUpdate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public $time;

    public function __construct()
    {
        $this->time = now()->format('H:i:s');
    }

    public function broadcastOn()
    {
        return new Channel('system');
    }

    public function broadcastAs()
    {
        return 'server.time';
    }
}