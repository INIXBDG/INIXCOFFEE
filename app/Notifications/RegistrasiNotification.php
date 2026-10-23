<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class RegistrasiNotification extends Notification
{
    use Queueable;

    protected $data;
    protected $type;
    public $receiverId;

    public function __construct($data, $type, $receiverId)
    {
        $this->data = $data;
        $this->type = $type;
        $this->receiverId = $receiverId;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    protected function buildPesan(string $tanggalFormat): string
    {
        $nf = 'tidak ditemukan';

        return "Peserta " . ($this->data['nama_peserta'] ?? $nf)
            . " (" . ($this->data['email_peserta'] ?? $nf) . ")"
            . " telah berhasil melakukan registrasi untuk kelas "
            . ($this->data['kelas'] ?? $nf)
            . " periode " . ($this->data['periode'] ?? $nf)
            . " dari perusahaan " . ($this->data['perusahaan'] ?? $nf)
            . ", pada tanggal " . now()->translatedFormat($tanggalFormat)
            . " pukul " . now()->format('H:i')
            . ".\nInstruktur: " . ($this->data['instruktur'] ?? $nf)
            . " | Sales: " . ($this->data['sales'] ?? $nf) . ".";
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        $nf = 'tidak ditemukan';

        return new BroadcastMessage([
            'user' => 'System',
            'message' => [
                'tipe'          => $this->type,
                'pesan'         => $this->buildPesan('d F Y'),
                'nama_peserta'  => $this->data['nama_peserta'] ?? $nf,
                'email_peserta' => $this->data['email_peserta'] ?? $nf,
                'kelas'         => $this->data['kelas'] ?? $nf,
                'perusahaan'    => $this->data['perusahaan'] ?? $nf,
                'periode'       => $this->data['periode'] ?? $nf,
                'instruktur'    => $this->data['instruktur'] ?? $nf,
                'sales'         => $this->data['sales'] ?? $nf,
            ],
            'status'    => 'unread',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function toArray($notifiable): array
    {
        return [
            'user' => 'System',
            'message' => [
                'tipe'  => $this->type,
                'pesan' => $this->buildPesan('d-m-Y'),
            ],
            'status' => 'unread',
            'type'   => 'Info Registrasi Peserta',
        ];
    }
}