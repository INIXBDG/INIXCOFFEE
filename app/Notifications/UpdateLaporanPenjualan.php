<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class UpdateLaporanPenjualan extends Notification implements ShouldQueue
{
    use Queueable;

    protected $data;
    protected $path;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $data, string $path)
    {
        $this->data = $data;
        $this->path = $path;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Kirim via database (untuk notifikasi di sistem)
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'user' => auth()->user()->username,
            'message' => [
                'tipe' => 'Perubahan Payment Advance',
                'karyawan' => $this->data['karyawan'] ?? 'Tidak diketahui',
                'id_rkm' => $this->data['id_rkm'] ?? null,
                'rkm' => $this->data['rkm'] ?? '-',
                'waktu' => $this->data['waktu'] ?? '-',
                'milik' => $this->data['milik'] ?? '-',
                'waktu_perubahan' => $this->data['waktu_perubahan'] ?? now()->toDateTimeString(),
                'perubahan' => $this->data['perubahan'] ?? [],
            ],
            'path' => $this->path,
            'status' => 'unread',
        ];
    }
}
