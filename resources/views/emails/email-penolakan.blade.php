@extends('emails.layouts.master')

@section('content')
    <p style="margin: 0 0 20px; color: #1f2937; font-size: 16px;">
        Halo <strong>{{ $pelamar->nama_lengkap }}</strong>,
    </p>

    <p style="margin: 0 0 20px; color: #4b5563; font-size: 15px; line-height: 1.6;">
        Terima kasih banyak atas minat Anda untuk bergabung dengan perusahaan kami dan atas waktu yang telah Anda luangkan
        untuk mengikuti proses rekrutmen posisi <strong>{{ $pelamar->jabatan }}</strong>.
    </p>

    <p style="margin: 0 0 20px; color: #4b5563; font-size: 15px; line-height: 1.6;">
        Setelah melalui pertimbangan yang matang, dengan berat hati kami informasikan bahwa saat ini kami belum dapat
        melanjutkan lamaran Anda ke tahap berikutnya. Keputusan ini bukan mencerminkan kemampuan atau potensi Anda,
        melainkan karena kami menemukan kandidat lain yang lebih sesuai dengan kebutuhan spesifik posisi tersebut.
    </p>

    @if (!empty($alasanPenolakan))
        <div
            style="background-color: #fee2e2; border-left: 4px solid #f44336; padding: 15px 20px; margin: 25px 0; border-radius: 4px;">
            <p style="margin: 0 0 5px; color: #991b1b; font-size: 12px; font-weight: 600; text-transform: uppercase;">
                Feedback
            </p>
            <p style="margin: 0; color: #7f1d1d; font-size: 14px; line-height: 1.6;">
                {{ $alasanPenolakan }}
            </p>
        </div>
    @endif

    <div style="background-color: #e8f0ff; border-radius: 8px; padding: 20px; margin: 25px 0;">
        <p style="margin: 0 0 8px; color: #1e40af; font-size: 14px; font-weight: 600;">
            💪 Tetap Semangat!
        </p>
        <p style="margin: 0; color: #1e3a8a; font-size: 13px; line-height: 1.6;">
            Kami sangat mengapresiasi antusiasme Anda. Profil Anda akan kami simpan dalam talent pool kami,
            dan kami akan menghubungi Anda kembali jika ada posisi yang lebih sesuai di masa depan.
        </p>
    </div>

    <p style="margin: 25px 0 0; color: #4b5563; font-size: 14px; line-height: 1.6;">
        Kami mendoakan yang terbaik untuk karier Anda ke depan dan semoga sukses selalu.
    </p>

    <p style="margin: 25px 0 0; color: #1f2937; font-size: 14px;">
        Salam,<br>
        <strong>Tim Inixindo Bandung</strong>
    </p>
@endsection
