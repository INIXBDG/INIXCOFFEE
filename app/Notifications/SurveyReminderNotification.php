<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Notifications\Messages\BroadcastMessage;

class SurveyReminderNotification extends Notification implements ShouldBroadcast
{
    use Queueable, InteractsWithSockets;

    protected $data;
    protected $path;
    protected $type;

    public function __construct($data = [], $path = null, $type = 'survey_reminder')
    {
        $this->data = $data;
        $this->path = $path ?? route('surveyKepuasan.create');
        $this->type = $type;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /** @var \App\Models\User $notifiable */
    public function broadcastOn()
    {
        return new PrivateChannel('notifikasi.' . $this->id);
    }

    public function broadcastAs(): string
    {
        return 'notifikasi-event';
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'user' => auth()->user()?->username ?? 'System',
            'message' => [
                'tipe'       => $this->type,
                'judul'      => 'Survey Kepuasan ITSM!',
                'deskripsi'  => 'dimohon untuk anda dapat mengisi survey kepuasan pelayanan ITSM.',
            ],
            'path'   => $this->path,
            'status' => 'unread',
        ]);
    }

    public function toArray($notifiable): array
    {
        return [
            'user' => auth()->user()?->username ?? 'System',
            'message' => [
                'tipe'       => $this->type,
                'judul'      => 'Survey Kepuasan ITSM!',
                'deskripsi'  => 'dimohon untuk anda dapat mengisi survey kepuasan pelayanan ITSM.',
            ],
            'path'   => $this->path,
            'status' => 'unread',
        ];
    }
}
