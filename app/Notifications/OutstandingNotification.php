<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;


class OutstandingNotification extends Notification
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
        return ['database', 'broadcast']; // Menggunakan 'database' dan 'broadcast'
    }

    public function toArray($notifiable)
    {
        return [
            'user' => auth()->user()->username,
            'message' => [
                'nama_perusahaan' => $this->data['nama_perusahaan'],
                'nama_materi' => $this->data['nama_materi'],
                'net_sales' => $this->data['net_sales'],
                'due_date' => $this->data['due_date'],
                'status_pembayaran' => $this->data['status_pembayaran'],
                'tipe' => 'Outstanding',
            ],
            'path' => $this->path,
            'status' => 'unread',
        ];
    }

    protected function formatRupiah($amount)
    {
        return number_format($amount, 0, ',', '.');
    }


    
}
