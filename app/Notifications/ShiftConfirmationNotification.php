<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ShiftConfirmationNotification extends Notification
{
    use Queueable;

    protected $shiftNumber;
    protected $date;

    public function __construct($shiftNumber, $date)
    {
        $this->shiftNumber = $shiftNumber;
        $this->date = $date;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'shift_confirmation',
            'shift' => $this->shiftNumber,
            'date' => $this->date,
            'message' => "Persetujuan pengambilan Shift {$this->shiftNumber} untuk tanggal {$this->date}.",
            'action_url' => url("/api/tasks/confirm-shift?shift={$this->shiftNumber}&date={$this->date}")
        ];
    }
}