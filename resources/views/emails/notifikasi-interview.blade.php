@extends('emails.layouts.master')

@section('content')
    <p style="margin: 0 0 20px; color: #1f2937; font-size: 16px;">
        Hai <strong>{{ $pelamar->nama_lengkap }}</strong>,
    </p>

    <p style="margin: 0 0 20px; color: #4b5563; font-size: 15px; line-height: 1.6;">
        Terima kasih sudah ikut serta dalam proses rekrutmen di <strong>PT Inixindo Amiete Mandiri</strong>.
    </p>

    <p style="margin: 0 0 20px; color: #4b5563; font-size: 15px; line-height: 1.6;">
        @php
            $tahap = '1';

            if ($tahapInterview === 'Interview HR') {
                $tahap = '1';
            } elseif ($tahapInterview === 'Interview Manager/SPV/Koordinator') {
                $tahap = '2';
            } elseif ($tahapInterview === 'Technical Test') {
                $tahap = 'Technical Test';
            } elseif ($tahapInterview === 'Interview Direksi') {
                $tahap = '3';
            }
        @endphp
        Kami mengundang kamu untuk lanjut ke tahap <strong style="color: #0062ff;">Interview {{ $tahap }}</strong> seleksi
        untuk posisi <strong style="color: #0062ff;">{{ $pelamar->jabatan }}</strong>.
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
        style="margin: 20px 0; font-size: 15px; color: #1f2937; line-height: 1.8;">
        <tr>
            <td style="width: 130px; vertical-align: top; font-weight: 600;">Hari/Tanggal</td>
            <td>: {{ \Carbon\Carbon::parse($jadwal)->translatedFormat('l, d F Y') }}</td>
        </tr>
        <tr>
            <td style="vertical-align: top; font-weight: 600;">Waktu</td>
            <td>: {{ \Carbon\Carbon::parse($jadwal)->format('H.i') }} WIB</td>
        </tr>
        <tr>
            <td style="vertical-align: top; font-weight: 600;">Tempat</td>
            <td>: {{ $metode }}</td>
        </tr>
    </table>

    @if ($metode === 'Online (Zoom / Google Meet)' && !empty($linkMeeting))
        <p style="margin: 15px 0; color: #4b5563; font-size: 15px; line-height: 1.6;">
            <strong>Link Meeting:</strong> <a href="{{ $linkMeeting }}"
                style="color: #0062ff; word-break: break-all;">{{ $linkMeeting }}</a>
        </p>
    @elseif($metode === 'Offline (Datang ke Kantor)' && !empty($lokasi))
        <p style="margin: 15px 0; color: #4b5563; font-size: 15px; line-height: 1.6;">
            <strong>Lokasi:</strong> {{ $lokasi }}
        </p>
    @endif

    @php
        $tahapKey = $pelamar->tahap_interview ?? '';
        $pesanTambahan = '';

        if ($tahapKey === 'hr') {
            $pesanTambahan =
                'Mohon untuk dapat mengirimkan berkas pendukung seperti CV, ijazah, FC KTP, FC SIM, portofolio (jika ada) pada alamat email yang tertera.';
        } elseif ($tahapKey === 'technical') {
            $pesanTambahan =
                'Sebagai bagian dari proses seleksi, kami mengundang Anda untuk melakukan presentasi dengan durasi maksimal 15 menit dengan topik seputar Data Analyst/Data Science. Silakan pilih materi yang paling Anda kuasai, dan pastikan presentasi mencakup teori serta demo/praktik penggunaan tools yang relevan.';
        } elseif (in_array($tahapKey, ['user', 'direksi'])) {
            $pesanTambahan = 'Mohon untuk dapat memberikan konfirmasi kehadiran dan menggunakan pakaian rapi formal.';
        } else {
            $pesanTambahan = $catatan ?? 'Mohon untuk dapat memberikan konfirmasi kehadiran.';
        }
    @endphp

    <p style="margin: 25px 0; color: #4b5563; font-size: 15px; line-height: 1.6;">
        {{ $pesanTambahan }}
    </p>

    @if (!empty($catatan) && !in_array($tahapKey, ['hr', 'user', 'technical', 'direksi']))
        <div
            style="background-color: #f9fafb; border-left: 4px solid #ff9800; padding: 15px 20px; margin: 25px 0; border-radius: 4px;">
            <p style="margin: 0 0 5px; color: #6b7280; font-size: 12px; font-weight: 600; text-transform: uppercase;">
                Catatan Tambahan
            </p>
            <p style="margin: 0; color: #1f2937; font-size: 14px; line-height: 1.6;">
                {{ $catatan }}
            </p>
        </div>
    @endif

    <p style="margin: 30px 0 5px; color: #1f2937; font-size: 15px;">
        Salam,
    </p>
    <p style="margin: 0; color: #1f2937; font-size: 15px; font-weight: 600;">
        HRD
    </p>
    <p style="margin: 0; color: #1f2937; font-size: 15px;">
        PT Inixindo Amiete Mandiri
    </p>
@endsection
