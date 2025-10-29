<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class KaryawanMail extends Mailable
{
    use Queueable, SerializesModels;

    public $karyawan;

    public function __construct($karyawan)
    {
        $this->karyawan = $karyawan;
    }

    public function build()
    {
        return $this->subject('Tes Email dari Scheduler')
            ->view('emails.scheduleTest');
    }
}
