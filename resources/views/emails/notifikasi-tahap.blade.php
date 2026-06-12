@extends('emails.layouts.master')

@section('content')
    <p style="margin: 0 0 20px; color: #1f2937; font-size: 16px;">
        Halo <strong>{{ $pelamar->nama_lengkap }}</strong>,
    </p>

    <p style="margin: 0 0 20px; color: #4b5563; font-size: 15px; line-height: 1.6;">
        Kami ingin menginformasikan bahwa lamaran Anda untuk posisi
        <strong style="color: #0062ff;">{{ $pelamar->jabatan }}</strong>
        di divisi <strong>{{ $pelamar->divisi }}</strong> telah berlanjut ke tahap berikutnya.
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
        style="background-color: #e8f0ff; border-radius: 8px; margin: 25px 0;">
        <tr>
            <td style="padding: 20px;">
                <p
                    style="margin: 0 0 8px; color: #6b7280; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                    Tahap Saat Ini
                </p>
                <p style="margin: 0; color: #0062ff; font-size: 20px; font-weight: 700;">
                    {{ $tahapLabel }}
                </p>
            </td>
        </tr>
    </table>

    @if (!empty($keterangan))
        <div
            style="background-color: #f9fafb; border-left: 4px solid #0062ff; padding: 15px 20px; margin: 25px 0; border-radius: 4px;">
            <p style="margin: 0 0 5px; color: #6b7280; font-size: 12px; font-weight: 600; text-transform: uppercase;">
                Catatan dari Tim Inixindo Bandung
            </p>
            <p style="margin: 0; color: #1f2937; font-size: 14px; line-height: 1.6;">
                {{ $keterangan }}
            </p>
        </div>
    @endif

    <p style="margin: 25px 0 0; color: #4b5563; font-size: 14px; line-height: 1.6;">
        Tim kami akan segera menghubungi Anda untuk informasi lebih lanjut mengenai tahap selanjutnya.
        Mohon pastikan kontak Anda selalu aktif.
    </p>

    <p style="margin: 25px 0 0; color: #1f2937; font-size: 14px;">
        Salam,<br>
        <strong>Tim Inixindo Bandung</strong>
    </p>
@endsection
