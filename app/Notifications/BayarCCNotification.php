<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BayarCCNotification extends Notification
{
    use Queueable;

    protected $data;
    protected $path;

    public function __construct($data, $path)
    {
        $this->data = $data;
        $this->data['total_harga_rupiah'] = $this->data['harga'] * $this->data['kurs'];
        // Tentukan simbol mata uang berdasarkan jenis mata uang
        switch ($this->data['mata_uang']) {
            case 'Rupiah':
                $this->data['mata_uang_symbol'] = 'Rp.';
                break;
            case 'Dollar':
                $this->data['mata_uang_symbol'] = '$';
                break;
            case 'Poundsterling':
                $this->data['mata_uang_symbol'] = '£';
                break;
            case 'Swiss Franc':
                $this->data['mata_uang_symbol'] = 'CHF';
                break;
            case 'Euro':
                $this->data['mata_uang_symbol'] = '€';
                break;
            default:
                $this->data['mata_uang_symbol'] = ''; // Simbol default jika mata uang tidak diketahui
                break;
        }
        $this->path = $path;
    }

    public function via($notifiable)
    {
        return ['database']; // Menggunakan 'database' dan 'broadcast'
    }

    public function toArray($notifiable)
    {
        return [
            'user' => auth()->user()->username,
            'message' => [
                'tipe' => 'Bayar CC',
                'invoice' => $this->data['invoice'],
                'perusahaan' => $this->data['perusahaan'],
                'tanggal_pengajuan' => $this->data['tanggal_pengajuan'],
                'materi' => $this->data['materi'],
                'mata_uang' => $this->data['mata_uang_symbol'],
                'pax' => $this->data['pax'],
                'harga_dollar' => $this->data['harga'],
                'harga_rupiah' => $this->data['total_harga_rupiah'],
                'cc' => $this->data['cc'],
            ],
            'path' => $this->path,
            'status' => 'unread',
        ];
    }
}
