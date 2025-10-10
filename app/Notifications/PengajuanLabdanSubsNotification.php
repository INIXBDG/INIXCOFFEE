<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PengajuanLabdanSubsNotification extends Notification
{
    use Queueable;

    protected $data;
    protected $path;
    protected $type;

    public function __construct($data, $path, $type)
    {
        $this->data = $data;
        $this->path = $path;
        $this->type = $type;
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
                'tipe'              => $this->type,
                'id_karyawan'       => $this->data['id_karyawan'],
                'tanggal_pengajuan' => $this->data['tanggal_pengajuan'],
                'jenis_pengajuan'   => $this->data['jenis_pengajuan'] ?? null,
                'nama'              => $this->data['nama'] ?? null,
                'deskripsi'         => $this->data['deskripsi'] ?? null,
                'rkm'               => $this->data['rkm'] ?? null,
            ],
            'path' => $this->path,
            'status' => 'unread',
        ];
    }

    public function show($id)
    {
        $data = PengajuanLabSubs::with(['karyawan','lab','subs','tracking'])->findOrFail($id);

        $tracking = $data->tracking()->orderBy('tanggal','asc')->get();

        return view('pengajuanlabs.detail', compact('data','tracking'));
    }

}
