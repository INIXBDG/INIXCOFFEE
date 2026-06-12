<?php

namespace App\Mail;

use App\Models\Pelamar;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotifikasiInterview extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Pelamar $pelamar,
        public string $jadwal,
        public string $metode,
        public ?string $linkMeeting = null,
        public ?string $lokasi = null,
        public string $interviewer = '',
        public string $tahapInterview = '',
        public ?string $catatan = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Undangan ' . $this->tahapInterview . ' – ' . $this->pelamar->jabatan . ' ' . $this->pelamar->detail_jabatan,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.notifikasi-interview',
            with: [
                'headerTitle' => 'Undangan Interview',
                'headerSubtitle' => 'Jadwal Interview Anda',
            ]
        );
    }
}