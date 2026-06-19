<?php

namespace App\Mail;

use App\Models\Pelamar;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class EmailKustom extends Mailable
{
    use Queueable, SerializesModels;

    public array $lampiranPaths = [];

    public function __construct(public Pelamar $pelamar, public string $subjekCustom, public string $isiEmail, public array $lampiran = []) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjekCustom);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.email-kustom',
            with: [
                'headerTitle' => $this->subjekCustom,
                'headerSubtitle' => 'Pesan dari Tim HR',
            ],
        );
    }

    public function attachments(): array
    {
        $attachments = [];
        foreach ($this->lampiran as $path) {
            if (Storage::disk('public')->exists($path)) {
                $attachments[] = Attachment::fromStorageDisk('public', $path);
            }
        }
        return $attachments;
    }
}
