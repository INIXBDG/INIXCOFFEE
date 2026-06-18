<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ApprovalSPJNotification extends Notification implements ShouldBroadcast
{
    use Queueable, InteractsWithSockets;

    protected $data;
    protected $path;
    protected $to;
    protected $receiverId;
    protected $action;
    protected $isDireksiApproval;

    // Tambahkan parameter $action dan $isDireksiApproval
    public function __construct($data, $path, $to, $receiverId, $action = 'Menyetujui SPJ', $isDireksiApproval = false)
    {
        $this->data = $data;
        $this->path = $path;
        $this->to   = $to;
        $this->receiverId = $receiverId;
        $this->action = $action;
        $this->isDireksiApproval = $isDireksiApproval;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function broadcastOn()
    {
        return new PrivateChannel('notifikasi.' . $this->receiverId);
    }

    public function broadcastAs()
    {
        return 'notifikasi-event';
    }

    public function toBroadcast($notifiable)
    {
        $messageData = [
            'user' => auth()->user()?->username ?? 'System',
            'message' => [
                'tipe'             => $this->action,
                'spj_id'           => $this->data->id,
                'nama_lengkap'     => $this->to,
                'tanggal_berangkat' => $this->data['tanggal_berangkat'],
                'tanggal_pulang'   => $this->data['tanggal_pulang'],
                'alasan'           => $this->data['alasan'],
                'durasi'           => $this->data['durasi'],
                'tujuan'           => $this->data['tujuan'],
            ],
            'path'   => $this->path,
            'status' => 'unread',
        ];

        // Sisipkan URL Approve/Reject jika ini notifikasi untuk Direksi
        if ($this->isDireksiApproval) {
            // Gunakan ->id karena $this->data adalah object Model Eloquent
            $spjId = $this->data->id;
            $messageData['approve_url'] = url('/suratperjalanan/' . $spjId . '/approve-direksi/1');
            $messageData['reject_url']  = url('/suratperjalanan/' . $spjId . '/approve-direksi/2');
        }

        return new BroadcastMessage($messageData);
    }

    public function toArray($notifiable): array
    {
        $messageData = [
            'user' => auth()->user()?->username ?? 'System',
            'message' => [
                'tipe'             => $this->action,
                'spj_id'           => $this->data->id,
                'nama_lengkap'     => $this->to,
                'tanggal_berangkat' => $this->data['tanggal_berangkat'],
                'tanggal_pulang'   => $this->data['tanggal_pulang'],
                'alasan'           => $this->data['alasan'],
                'durasi'           => $this->data['durasi'],
                'tujuan'           => $this->data['tujuan'],
            ],
            'path'   => $this->path,
            'status' => 'unread',
        ];

        if ($this->isDireksiApproval) {
            $messageData['approve_url'] = url('/suratperjalanan/' . $this->data['id'] . '/approve-direksi/1');
            $messageData['reject_url']  = url('/suratperjalanan/' . $this->data['id'] . '/approve-direksi/2');
        } elseif ($this->action === 'Menunggu Verifikasi Finance') {
            $messageData['finance_approval_url'] = url('/suratperjalanan?finance_approval_id=' . $this->data->id);
        }


        return $messageData;
    }
}
