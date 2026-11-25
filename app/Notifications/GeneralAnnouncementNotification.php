<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi umum omnichannel (portal + email kantor).
 * Dipakai ulang untuk pengumuman dan reminder (mis. approval tertunda).
 */
class GeneralAnnouncementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        protected string $judul,
        protected string $isi,
        protected string $path = '/',
        protected string $tipe = 'Pengumuman',
        protected ?string $pengirim = null
    ) {}

    public function via($notifiable): array
    {
        $channels = ['database'];

        // Kirim email hanya jika user punya email kantor yang valid.
        if (method_exists($notifiable, 'routeNotificationForMail')
            && $notifiable->routeNotificationForMail($this)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail($notifiable): MailMessage
    {
        $nama = $notifiable->karyawan->nama_lengkap ?? $notifiable->username ?? 'Rekan';

        return (new MailMessage)
            ->subject('['.$this->tipe.'] '.$this->judul)
            ->greeting('Halo, '.$nama)
            ->line($this->isi)
            ->action('Buka di Portal', url($this->path))
            ->line('Email ini dikirim otomatis oleh sistem. Mohon tidak membalas email ini.');
    }

    public function toArray($notifiable): array
    {
        return [
            'user' => $this->pengirim ?? auth()->user()?->username ?? 'System',
            'message' => [
                'tipe' => $this->tipe,
                'judul' => $this->judul,
                'isi' => $this->isi,
            ],
            'path' => $this->path,
            'status' => 'unread',
        ];
    }
}
