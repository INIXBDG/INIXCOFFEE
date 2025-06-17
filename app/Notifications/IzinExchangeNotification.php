<?php

namespace App\Notifications;

use App\Models\karyawan;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IzinExchangeNotification extends Notification
{
    use Queueable;

    protected $data;
    protected $path;
    protected $to;
    protected $type;

    public function __construct($data, $path, $to, $type)
    {
        $this->data = $data;
        $this->path = $path;
        $this->to   = $to;
        $this->type = $type;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        $data_koordinator = ['Office Manager', 'Education Manager', 'SPV Sales', 'Koordinator Office', 'Koordinator ITSM'];

        $koordinator = karyawan::where('divisi', auth()->user()->divisi)
            ->whereIn('jabatan', $data_koordinator)
            ->first();

        $approval = $this->data['approval'] ?? 0;
        $statusMessage = '';

        if ($approval == 1) {
            $statusMessage = auth()->user()->jabatan . ' telah menyetujui permintaan';
        } elseif ($approval == 2) {
            $statusMessage = 'HRD telah menyetujui permintaan';
        } elseif ($approval == 4) {
            $statusMessage = $this->data['alasan_approval'] ?? 'Permintaan ditolak';
        } else {
            $statusMessage = 'Menunggu persetujuan ' . ($koordinator ? $koordinator->jabatan : 'Koordinator');
        }

        return [
            'user' => auth()->user()->username,
            'message' => [
                'tipe' => $this->type,
                'nama_lengkap' => $this->to,
                'jam_mulai' => $this->data['jam_mulai'],
                'jam_selesai' => $this->data['jam_selesai'],
                'durasi' => $this->data['durasi'],
                'status' => $statusMessage,
                'approval' => $approval,
                'alasan_approval' => $this->data['alasan_approval'] ?? null,
            ],
            'path' => $this->path ?? '#',
            'status' => 'unread',
        ];
    }
}
