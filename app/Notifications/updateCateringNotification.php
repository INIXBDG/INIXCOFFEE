<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateCateringNotification extends Notification implements ShouldBroadcast
{
    use Queueable, InteractsWithSockets, SerializesModels;

    protected $data;
    protected $path;
    protected $type;
    protected $receiverId;


    public function __construct(array $data, $path, $type, $receiverId)
    {
        $this->data = $data;
        $this->path = $path;
        $this->type = $type;
        $this->receiverId = $receiverId;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable): array
    {
        $pengubah = $this->data['pengubah'];
        $perubahan = $this->data['perubahan'] ?? [];
        $tipe = $this->data['tipe'];
        $nama = $this->data['nama_lengkap'];
        $tanggal = $this->data['tanggal_pengajuan'];

        if (empty($perubahan)) {
            $pesan = $pengubah . " telah mengubah pengajuan catering.";
        } else {
            $pesanLines = array_map(fn($item) => "• {$item}", $perubahan);
            $pesan = $pengubah . " telah mengubah pengajuan catering:\n" . implode("\n", $pesanLines);
        }

        return [
            'user' => auth()->user()->username ?? 'System',
            'message' => [
                'tipe' => $this->type,
                'nama_lengkap' => $nama,
                'tanggal_pengajuan' => $tanggal,
                'pesan' => $pesan,
                'tipe_barang' => $tipe,
            ],
            'path' => $this->path,
            'status' => 'unread',
        ];
    }
    public function toBroadcast($notifiable): BroadcastMessage
    {
        $pengubah = $this->data['pengubah'];
        $perubahan = $this->data['perubahan'] ?? [];
        $tipe = $this->data['tipe'];
        $nama = $this->data['nama_lengkap'];
        $tanggal = $this->data['tanggal_pengajuan'];

        if (empty($perubahan)) {
            $pesan = $pengubah . " telah mengubah pengajuan catering.";
        } else {
            $pesanLines = array_map(fn($item) => "• {$item}", $perubahan);
            $pesan = $pengubah . " telah mengubah pengajuan catering:\n" . implode("\n", $pesanLines);
        }
        
        return new BroadcastMessage([
            'user' => auth()->user()->username ?? 'System',
            'message' => [
                'tipe' => $this->type,
                'nama_lengkap' => $nama,
                'tanggal_pengajuan' => $tanggal,
                'pesan' => $pesan,
                'tipe_barang' => $tipe,
            ],
            'path' => $this->path,
            'status' => 'unread',
        ]);
    }


    public function broadcastOn()
    {
        return new PrivateChannel('notifikasi.' . $this->receiverId);
    }

    public function broadcastAs()
    {
        return 'notifikasi-event';
    }
}
