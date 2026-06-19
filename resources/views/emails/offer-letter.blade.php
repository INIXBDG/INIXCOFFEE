@extends('emails.layouts.master')

@section('content')
    <p style="margin: 0 0 20px; color: #1f2937; font-size: 16px;">
        Halo <strong>{{ $pelamar->nama_lengkap }}</strong>,
    </p>

    <p style="margin: 0 0 20px; color: #4b5563; font-size: 15px; line-height: 1.6;">
        Selamat! Berdasarkan hasil evaluasi dan serangkaian wawancara yang telah Anda lalui, kami dengan senang hati
        menyampaikan penawaran kerja resmi untuk bergabung dengan <strong>PT. Inixindo Amiete Mandiri Bandung</strong>
        pada posisi <strong style="color: #0062ff;">{{ $pelamar->jabatan }}</strong>.
    </p>

    {{-- 🔐 BOX PASSWORD --}}
    <div
        style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px 20px; margin: 25px 0; border-radius: 4px;">
        <p style="margin: 0 0 10px; color: #92400e; font-size: 14px; font-weight: 700;">
            🔐 Password Dokumen Offer Letter
        </p>
        <p style="margin: 0 0 10px; color: #78350f; font-size: 14px; line-height: 1.5;">
            Untuk menjaga kerahasiaan, dokumen PDF yang terlampir telah kami kunci.
            Silakan gunakan password di bawah ini untuk membukanya di PDF Reader:
        </p>
        <p style="margin: 0; font-size: 14px; color: #78350f;">
            <strong>Password Anda:</strong>
            <span
                style="background: #fff; padding: 5px 12px; border-radius: 4px; font-family: monospace; font-size: 20px; letter-spacing: 4px; border: 1px dashed #f59e0b; font-weight: bold; color: #1f2937;">
                {{ $offerPassword }}
            </span>
        </p>
        <p style="margin: 10px 0 0; font-size: 12px; color: #92400e; font-style: italic;">
            💡 Simpan password ini dengan baik. Dokumen hanya dapat dibuka dengan password tersebut.
        </p>
    </div>

    {{-- 📎 LANGKAH SELANJUTNYA --}}
    <div
        style="background-color: #eff6ff; border-left: 4px solid #0062ff; padding: 15px 20px; margin: 25px 0; border-radius: 4px;">
        <p style="margin: 0 0 10px; color: #1e40af; font-size: 14px; font-weight: 700;">
            📎 Langkah Selanjutnya:
        </p>
        <ol style="margin: 0; padding-left: 20px; color: #1e3a8a; font-size: 14px; line-height: 1.6;">
            <li>Unduh dokumen <em>Offer Letter</em> yang terlampir.</li>
            <li>Buka file PDF dan masukkan <strong>Password</strong> di atas saat diminta.</li>
            <li>Baca dokumen dengan saksama, lalu tanda tangan pada kolom yang disediakan.</li>
            <li>Kirimkan kembali salinan dokumen yang sudah ditandatangani sebagai tanda persetujuan.</li>
        </ol>
    </div>

    {{-- ⚠️ PENGINGAT KERAHASIAAN --}}
    <p
        style="font-size: 12px; color: #9ca3af; margin-top: 20px; font-style: italic; border-top: 1px dashed #e5e7eb; padding-top: 15px;">
        ⚠️ <em>Dokumen ini bersifat rahasia dan hanya ditujukan untuk Anda. Mohon untuk tidak menyebarkan
            atau meneruskan email ini kepada pihak yang tidak berkepentingan.</em>
    </p>

    <p style="margin: 25px 0 0; color: #4b5563; font-size: 14px; line-height: 1.6;">
        Jika ada pertanyaan atau hal yang perlu didiskusikan lebih lanjut, jangan ragu untuk menghubungi kami
        melalui email ini.
    </p>

    <p style="margin: 30px 0 0; color: #1f2937; font-size: 14px;">
        Hormat kami,<br>
        <span style="color: #6b7280; font-size: 13px;">HRD - PT. Inixindo Amiete Mandiri Bandung</span>
    </p>
@endsection
