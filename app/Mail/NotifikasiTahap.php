<?php

namespace App\Mail;

use App\Models\Pelamar;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotifikasiTahap extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Pelamar $pelamar, public string $tahapLabel, public ?string $keterangan = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Update Lamaran - ' . $this->pelamar->jabatan);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.notifikasi-tahap',
            with: [
                'headerTitle' => 'Update Status Lamaran',
                'headerSubtitle' => 'Recruitment Progress',
            ],
        );
    }
}
