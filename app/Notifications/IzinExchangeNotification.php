<?php

namespace App\Notifications;

use App\Models\Karyawan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Notifications\Messages\BroadcastMessage;

class IzinExchangeNotification extends Notification implements ShouldBroadcast
{
    use Queueable, InteractsWithSockets;

    protected $data;
    protected $path;
    protected $to;
    protected $type;
    protected $receiverId; 


    public function __construct($data, $path, $to, $type, $receiverId)
    {
        $this->data = $data;
        $this->path = $path;
        $this->to   = $to;
        $this->type = $type;
        $this->receiverId   = $receiverId;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        $data_koordinator = ['Office Manager', 'Education Manager', 'SPV Sales', 'Koordinator Office', 'Koordinator ITSM'];
        $koordinator = Karyawan::where('divisi', auth()->user()->divisi)
            ->whereIn('jabatan', $data_koordinator)
            ->first();

        $approval = $this->data['approval'] ?? 0;
        $statusMessage = match ($approval) {
            1 => auth()->user()->jabatan . ' telah menyetujui permintaan',
            2 => 'HRD telah menyetujui permintaan',
            4 => $this->data['alasan_approval'] ?? 'Permintaan ditolak',
            default => 'Menunggu persetujuan ' . ($koordinator?->jabatan ?? 'Koordinator'),
        };

        return new BroadcastMessage([
            'user' => auth()->user()?->username,
            'message' => [
                'tipe'             => $this->type,
                'nama_lengkap'     => $this->to,
                'jam_mulai'        => $this->data['jam_mulai'],
                'jam_selesai'      => $this->data['jam_selesai'],
                'durasi'           => $this->data['durasi'],
                'status'           => $statusMessage,
                'approval'         => $approval,
                'alasan_approval'  => $this->data['alasan_approval'] ?? null,
            ],
            'path'   => $this->path ?? '#',
            'status' => 'unread',
        ]);
    }

    public function toArray($notifiable): array
    {
        $data_koordinator = ['Office Manager', 'Education Manager', 'SPV Sales', 'Koordinator Office', 'Koordinator ITSM'];
        $koordinator = Karyawan::where('divisi', auth()->user()->divisi)
            ->whereIn('jabatan', $data_koordinator)
            ->first();

        $approval = $this->data['approval'] ?? 0;
        $statusMessage = match ($approval) {
            1 => auth()->user()->jabatan . ' telah menyetujui permintaan',
            2 => 'HRD telah menyetujui permintaan',
            4 => $this->data['alasan_approval'] ?? 'Permintaan ditolak',
            default => 'Menunggu persetujuan ' . ($koordinator?->jabatan ?? 'Koordinator'),
        };

        return [
            'user' => auth()->user()?->username,
            'message' => [
                'tipe'             => $this->type,
                'nama_lengkap'     => $this->to,
                'jam_mulai'        => $this->data['jam_mulai'],
                'jam_selesai'      => $this->data['jam_selesai'],
                'durasi'           => $this->data['durasi'],
                'status'           => $statusMessage,
                'approval'         => $approval,
                'alasan_approval'  => $this->data['alasan_approval'] ?? null,
            ],
            'path'   => $this->path ?? '#',
            'status' => 'unread',
        ];
    }

    public function broadcastOn()
    {
        return new PrivateChannel('notifikasi.' . $this->receiverId);
    }

    public function broadcastAs(): string
    {
        return 'notifikasi-event';
    }
}
