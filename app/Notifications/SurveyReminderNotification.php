<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SurveyReminderNotification extends Notification
{
    use Queueable;

    protected $data;
    protected $path;
    protected $type;

    public function __construct($data = [], $path = null, $type = 'survey_reminder')
    {
        $this->data = $data;
        $this->path = $path ?? route('surveyKepuasan.create');
        $this->type = $type;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'user' => auth()->check() ? auth()->user()->username : 'System',
            'message' => [
                'tipe' => $this->type,
                'judul' => 'Survey Kepuasan ITSM!',
                'deskripsi' => 'dimohon untuk anda dapat mengisi survey kepuasan pelayanan ITSM.',
            ],
            'path' => $this->path,
            'status' => 'unread',
        ];
    }
}
