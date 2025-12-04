<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;


class PenambahanSouvenirNotification extends Notification
{
    use Queueable;

    protected $data;
    protected $path;
    protected $type;

    /**
     * Create a new notification instance.
     * * @param array $data Data detail notifikasi (id_karyawan, tipe, tanggal_pengajuan)
     * @param string $path URL redirect saat notifikasi diklik
     * @param string $type Judul atau tipe notifikasi
     */
    public function __construct($data, $path, $type)
    {
        $this->data = $data;
        $this->path = $path;
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     * Disimpan ke tabel 'notifications' kolom 'data'.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $senderName = auth()->user()->username ?? 'System';

        return [
            'user' => $senderName,
            'message' => [
                'tipe' => $this->type,
                // Gunakan operator ?? untuk mencegah error jika key tidak dikirim dari controller
                'tipe_barang' => $this->data['tipe'] ?? 'Souvenir',
                'id_karyawan' => $this->data['id_karyawan'] ?? null,
                'tanggal_pengajuan' => $this->data['tanggal_pengajuan'] ?? now(), // Fallback ke waktu sekarang

                // Mapping Data RKM Baru
                'nama_rkm' => $this->data['nama_rkm'] ?? '-',
                'rkm_start' => $this->data['rkm_start'] ?? null,
                'rkm_end' => $this->data['rkm_end'] ?? null,
            ],
            'path' => $this->path,
            'status' => 'unread',
        ];
    }

}
