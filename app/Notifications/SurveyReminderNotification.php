<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Notifications\Messages\BroadcastMessage;
use App\Models\Tickets;

class SurveyReminderNotification extends Notification implements ShouldBroadcast
{
    use Queueable, InteractsWithSockets;

    protected $ticket;
    protected $path;
    protected $type;

    public function __construct(Tickets $ticket)
    {
        $this->ticket = $ticket;
        // Penyesuaian path agar mengarah langsung ke survei dengan parameter ticket_id
        $this->path = route('surveykepuasan.index', ['ticket_id' => $ticket->ticket_id]);
        $this->type = 'survey_reminder';
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
                'judul'      => 'Tiket Selesai - ' . $this->ticket->ticket_id,
                'deskripsi'  => 'Tiket Anda telah selesai ditangani. Harap mengisi survei kepuasan terhadap pelayanan ticketing.',
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
                'judul'      => 'Tiket Selesai - ' . $this->ticket->ticket_id,
                'deskripsi'  => 'Tiket Anda telah selesai ditangani. Harap mengisi survei kepuasan terhadap pelayanan ticketing.',
            ],
            'path'   => $this->path,
            'status' => 'unread',
        ];
    }
}