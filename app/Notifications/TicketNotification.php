<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketNotification extends Notification
{
    use Queueable;

    protected $data;
    protected $path;
    protected $status;

    public function __construct($data, $path, $status)
    {
        $this->data = $data;
        $this->path = $path;
        $this->status = $status;
    }

    public function via(object $notifiable): array
    {
         return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        // dd($this->data);
        // Akses variabel menggunakan $this->
        if($this->status == 'Ticketing Baru'){
            return [
                'user' => $this->data['nama_karyawan'],
                'message' => [
                    'tipe' => $this->status,
                    'nama_karyawan' => $this->data['nama_karyawan'] ?? null,
                    'divisi' => $this->data['divisi'] ?? null,
                    'keperluan' => $this->data['keperluan'] ?? null,
                    'kategori' => $this->data['kategori'] ?? null,
                ],
                'path' => $this->path,
                'status' => 'unread',
            ];
        }elseif($this->status == 'Ticketing diterima'){
            return [
                'user' => auth()->user()->username,
                'message' => [
                    'tipe' => $this->status,
                    'pic' => $this->data['pic'] ?? null,
                    'tanggal_response' => $this->data['tanggal_response'] ?? null,
                    'jam_response' => $this->data['jam_response'] ?? null,
                    'status' => $this->data['status'] ?? null,
                ],
                'path' => $this->path,
                'status' => 'unread',
            ];
        }
        elseif($this->status == 'Ticketing Selesai'){
            return [
                'user' => auth()->user()->username,
                'message' => [
                    'tipe' => $this->status,
                    'pic' => $this->data['pic'] ?? null,
                    'tanggal_selesai' => $this->data['tanggal_selesai'] ?? null,
                    'jam_selesai' => $this->data['jam_selesai'] ?? null,
                    'keterangan' => $this->data['keterangan'] ?? null,
                    'status' => $this->data['status'] ?? null,
                ],
                'path' => $this->path,
                'status' => 'unread',
            ];
        }
        elseif($this->status == 'Ticketing Terkendala'){
            return [
                'user' => auth()->user()->username,
                'message' => [
                    'tipe' => $this->status,
                    'pic' => $this->data['pic'] ?? null,
                    'tanggal_selesai' => $this->data['tanggal_selesai'] ?? null,
                    'jam_selesai' => $this->data['jam_selesai'] ?? null,
                    'keterangan' => $this->data['keterangan'] ?? null,
                    'status' => $this->data['status'] ?? null,
                ],
                'path' => $this->path,
                'status' => 'unread',
            ];
        }

        // return default jika status tidak cocok (opsional)
        return [];
    }
}

