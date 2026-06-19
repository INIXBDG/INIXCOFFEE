<?php

namespace App\Mail;

use App\Models\Pelamar;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class OfferLetter extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Pelamar $pelamar,
        public int $gaji,
        public int $tunjanganMakan,
        public int $tunjanganTransport,
        public int $estimasiBulanan,
        public string $tanggalMulai,
        public string $statusKepegawaian,
        public ?string $benefitLainnya = null,
        public ?string $pesanTambahan = null,
        public string $offerPassword,
        public ?string $offerPath = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Penawaran Kerja (Offer Letter) - ' . $this->pelamar->jabatan);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.offer-letter',
            with: [
                'headerTitle' => 'Penawaran Kerja',
                'headerSubtitle' => 'Silakan unduh dan baca dokumen terlampir',
            ],
        );
    }

    public function attachments(): array
    {
        if (!$this->offerPath) {
            return [];
        }

        $fullPath = storage_path('app/public/' . $this->offerPath);

        if (!file_exists($fullPath)) {
            return [];
        }

        $fileName = 'Offer_Letter_' . str_replace(' ', '_', $this->pelamar->nama_lengkap) . '.pdf';

        return [Attachment::fromPath($fullPath)->as($fileName)->withMime('application/pdf')];
    }
}
