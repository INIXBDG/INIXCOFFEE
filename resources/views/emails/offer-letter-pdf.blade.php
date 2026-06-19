<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Surat Penawaran Kerja - {{ $pelamar->nama_lengkap }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1e293b;
            line-height: 1.6;
            margin: 0;
            padding: 40px;
            position: relative;
            background-color: #fff;
        }

        /* WATERMARK PROTECTION */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 55px;
            color: rgba(220, 38, 38, 0.07);
            font-weight: 900;
            text-transform: uppercase;
            z-index: 0;
            white-space: nowrap;
            pointer-events: none;
            text-align: center;
            width: 100%;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #0062ff;
            padding-bottom: 20px;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
        }

        .header h1 {
            color: #0062ff;
            margin: 0;
            font-size: 24px;
            letter-spacing: 1px;
        }

        .header p {
            margin: 5px 0 0;
            color: #64748b;
            font-size: 14px;
        }

        .info-box {
            background-color: #f8fafc;
            border-left: 4px solid #0062ff;
            padding: 15px 20px;
            margin: 25px 0;
            border-radius: 4px;
        }

        table.details {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
        }

        table.details th,
        table.details td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }

        table.details th {
            width: 45%;
            color: #475569;
            font-weight: 600;
            background-color: #f1f5f9;
        }

        .total-row {
            background-color: #eff6ff;
        }

        .total-row th,
        .total-row td {
            color: #0062ff;
            font-weight: 700;
            font-size: 15px;
            border-bottom: 2px solid #cbd5e1;
        }

        .contact-box {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 20px;
            margin-top: 40px;
            border-radius: 8px;
            font-size: 13px;
            color: #334155;
            position: relative;
            z-index: 1;
        }

        .contact-box h4 {
            margin: 0 0 10px 0;
            color: #0f172a;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .contact-box p {
            margin: 4px 0;
            line-height: 1.5;
        }

        .footer {
            margin-top: 40px;
            font-size: 13px;
            color: #64748b;
            text-align: right;
            padding-right: 10px;
            position: relative;
            z-index: 1;
        }

        .signature-space {
            height: 60px;
        }

        .confidential-notice {
            font-size: 11px;
            color: #94a3b8;
            text-align: center;
            margin-top: 50px;
            font-style: italic;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
            position: relative;
            z-index: 1;
        }
    </style>
</head>

<body>

    {{-- Dynamic Watermark untuk Proteksi Visual --}}
    <div class="watermark">
        RAHASIA - {{ strtoupper($pelamar->nama_lengkap) }}
    </div>

    <div class="header">
        <h1>SURAT PENAWARAN KERJA</h1>
        <p>(Offer Letter)</p>
        <p>Nomor: OFFER/{{ date('Y') }}/{{ str_pad($pelamar->id, 4, '0', STR_PAD_LEFT) }}</p>
    </div>

    <p style="position: relative; z-index: 1;">
        Kepada Yth,<br>
        <strong>{{ $pelamar->nama_lengkap }}</strong><br>
        {{ $pelamar->email }} | {{ $pelamar->no_telepon }}
    </p>

    <p style="position: relative; z-index: 1;">Dengan hormat,</p>
    <p style="position: relative; z-index: 1;">
        Berdasarkan hasil proses seleksi dan serangkaian wawancara yang telah Anda ikuti, kami dari <strong>HR Inixindo
            Bandung</strong> dengan senang hati menyatakan bahwa Anda <strong>DITERIMA</strong> untuk bergabung dengan
        perusahaan kami.
    </p>
    <p style="position: relative; z-index: 1;">Berikut adalah rincian penawaran kerja resmi yang kami ajukan:</p>

    <div class="info-box" style="position: relative; z-index: 1;">
        <strong>Posisi Dilamar:</strong> {{ $pelamar->jabatan }}<br>
        <strong>Divisi:</strong> {{ $pelamar->divisi }}<br>
        <strong>Jenis Kepegawaian:</strong> {{ $statusKepegawaian }}<br>
        <strong>Tanggal Mulai Kerja:</strong> {{ \Carbon\Carbon::parse($tanggalMulai)->translatedFormat('d F Y') }}
    </div>

    <table class="details" style="position: relative; z-index: 1;">
        <tr>
            <th>Gaji Pokok (Bulanan)</th>
            <td>Rp {{ number_format($gaji, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Tunjangan Makan (Harian)</th>
            <td>Rp {{ number_format($tunjanganMakan, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Tunjangan Transportasi (Harian)</th>
            <td>Rp {{ number_format($tunjanganTransport, 0, ',', '.') }}</td>
        </tr>
        <tr class="total-row">
            <th>Estimasi Total Pendapatan per Bulan*</th>
            <td>Rp {{ number_format($estimasiBulanan, 0, ',', '.') }}</td>
        </tr>
    </table>
    <p style="font-size: 11px; color: #64748b; margin-top: -15px; position: relative; z-index: 1;">
        *Estimasi dihitung berdasarkan 22 hari kerja efektif per bulan. Potongan pajak (PPh 21) dan iuran BPJS akan
        disesuaikan dengan peraturan yang berlaku.
    </p>

    @if (!empty($benefitLainnya))
        <p style="position: relative; z-index: 1;"><strong>Benefit & Fasilitas
                Tambahan:</strong><br>{{ nl2br(e($benefitLainnya)) }}</p>
    @endif

    @if (!empty($pesanTambahan))
        <div class="info-box"
            style="border-left-color: #f59e0b; background-color: #fffbeb; position: relative; z-index: 1;">
            <strong>Catatan Khusus:</strong><br>{{ nl2br(e($pesanTambahan)) }}
        </div>
    @endif

    <p style="position: relative; z-index: 1;">
        Surat penawaran ini berlaku hingga
        <strong>{{ \Carbon\Carbon::parse($tanggalMulai)->subDays(7)->translatedFormat('d F Y') }}</strong>.
        Jika Anda menerima penawaran ini, mohon untuk menandatangani dan mengembalikan salinan surat ini paling lambat
        pada tanggal tersebut sebagai tanda persetujuan Anda.
    </p>

    <div class="contact-box">
        <h4>📞 Informasi Kontak & HR Department</h4>
        <p>Apabila terdapat pertanyaan atau membutuhkan klarifikasi lebih lanjut mengenai penawaran ini, silakan
            hubungi:</p>
        <p>
            <strong>Departemen HRD - Inixindo Bandung</strong><br>
            📧 Email: hr@inixindo.co.id | hr.inixindo@gmail.com<br>
            📞 Telepon: (022) 201-3131 / 0812-XXXX-XXXX<br>
            🏢 Alamat: Jl. Rajawali No. 38, Lebak Gede, Kec. Coblong, Kota Bandung, Jawa Barat 40132
        </p>
    </div>

    <div class="footer">
        <p>Bandung, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
        <p>Hormat kami,</p>
        <div class="signature-space"></div>
        <p><strong>Manajer HRD</strong><br>Inixindo Bandung</p>
    </div>

    <div class="confidential-notice">
        DOKUMEN INI BERSIFAT RAHASIA DAN HANYA UNTUK PENERIMA YANG BERSANGKUTAN.<br>
        Dilarang memperbanyak atau menyebarkan dokumen ini tanpa izin tertulis dari HR Inixindo Bandung.
    </div>
</body>

</html>
