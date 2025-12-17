<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Data Peserta - {{ $no->no_modul ?? '-' }}</title>
    <style>
        /* 1. SETUP HALAMAN */
        @page {
            size: A4 portrait;
            margin: 10mm 15mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
        }

        /* 2. HELPER UMUM */
        .w-100 {
            width: 100%;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .bold {
            font-weight: bold;
        }

        .valign-top {
            vertical-align: top;
        }

        .valign-mid {
            vertical-align: middle;
        }

        /* 3. META BOX */
        .meta-table {
            width: 320px;
            border-collapse: collapse;
        }

        .meta-table td {
            border: 1px solid #000;
            padding: 4px 8px;
            vertical-align: middle;
        }

        .meta-label {
            width: 60px;
            font-weight: normal;
        }

        /* 4. JUDUL DOKUMEN */
        .doc-title {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
            display: inline-block;
        }

        /* 5. INFO SECTION */
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            border: none;
        }

        .inner-info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .inner-info-table td {
            border: none !important;
            padding: 2px 0;
            vertical-align: top;
        }

        .label-col {
            width: 90px;
            font-weight: bold;
        }

        .sep-col {
            width: 10px;
            text-align: center;
            font-weight: bold;
        }

        /* 6. MAIN DATA TABLE */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #000;
            padding: 6px 4px;
            vertical-align: middle;
            font-size: 10px;
        }

        .data-table th {
            background-color: #e0e0e0;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* 7. FOOTER BOX (DIPERBAIKI) */
        .footer-box {
            width: 50%;
            border-collapse: collapse;
            margin-top: 20px;
            border: 1px solid #000;
            /* Hanya garis luar kotak utama */
        }

        .footer-box td {
            border: none;
            /* Hapus garis internal antar sel */
            padding: 10px;
            vertical-align: top;
        }

        .note-label {
            font-weight: bold;
            /* text-decoration: underline; (Opsional, matikan jika ingin persis gambar) */
            margin-bottom: 5px;
            font-size: 10px;
        }

        .note-text {
            font-size: 10px;
            white-space: pre-line;
            line-height: 1.4;
        }

        .sign-area {
            text-align: center;
            min-height: 80px;
            margin-top: 0;
            position: relative;
        }

        .sign-name {
            font-size: 9px;
            margin-top: 5px;
            position: relative;
            z-index: 2;
        }

        .signature-image {
            height: 60px;
            max-width: 100%;
            object-fit: contain;
            display: inline-block;
            position: relative;
            z-index: 1;
        }
    </style>
</head>

<body>

    {{-- HEADER: META INFO --}}
    <table class="w-100">
        <tr>
            <td class="valign-top" width="60%">
                <table class="meta-table">
                    <tr>
                        <td class="meta-label">To</td>
                        <td>: Mba Yana</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Subject</td>
                        <td>: Data Peserta</td>
                    </tr>
                    <tr>
                        <td class="meta-label">From</td>
                        <td>{{ explode(' ', $ttd->nama_lengkap)[0] }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- HEADER: LOGO & JUDUL --}}
    <table class="w-100" style="margin-top: 20px; margin-bottom: 20px;">
        <tr>
            <td width="20%" class="valign-mid text-left">
                <img src="{{ public_path('assets/img/logo.png') }}" style="height: 70px; width: 130px;" alt="Logo">
            </td>
            <td width="60%" class="valign-mid text-center">
                <div class="doc-title">FORMULIR DATA PESERTA</div>
            </td>
            <td width="20%"></td>
        </tr>
    </table>

    {{-- INFO SECTION --}}
    <table class="info-table">
        <tr>
            <td width="33%">
                <table class="inner-info-table">
                    <tr>
                        <td class="label-col">Kepada/UP</td>
                        <td class="sep-col">:</td>
                        <td>Holding</td>
                    </tr>
                    <tr>
                        <td class="label-col">Dari/UP</td>
                        <td class="sep-col">:</td>
                        <td>Inixindo Bandung</td>
                    </tr>
                </table>
            </td>
            <td width="33%">
                <table class="inner-info-table">
                    <tr>
                        <td class="label-col">Telp/email</td>
                        <td class="sep-col">:</td>
                        <td>021-57940868</td>
                    </tr>
                    <tr>
                        <td class="label-col">Telp/email</td>
                        <td class="sep-col">:</td>
                        <td>022-2032831</td>
                    </tr>
                </table>
            </td>
            <td width="34%">
                <table class="inner-info-table">
                    <tr>
                        <td class="label-col">Tanggal Pesan</td>
                        <td class="sep-col">:</td>
                        <td>{{ \Carbon\Carbon::parse($no->created_at)->translatedFormat('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Nomor PO</td>
                        <td class="sep-col">:</td>
                        <td>{{ $no->no_modul ?? '-' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- TABEL DATA PESERTA --}}
    <table class="data-table">
        <thead>
            <tr>
                <th width="4%">NO</th>
                <th width="20%">NAMA PESERTA</th>
                <th width="20%">MATERI TRAINING</th>
                <th width="15%">PERIODE TRAINING</th>
                <th width="15%">PERUSAHAAN</th>
                <th width="26%">TELP/EMAIL PESERTA</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($peserta as $p)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $p->nama_peserta }}</td>
                    <td>{{ $p->dataModul->nama_materi ?? '-' }}</td>
                    <td class="text-center">
                        @if ($p->awal_training && $p->akhir_training)
                            {{ \Carbon\Carbon::parse($p->awal_training)->format('d') }} -
                            {{ \Carbon\Carbon::parse($p->akhir_training)->translatedFormat('d M Y') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $p->perusahaan->nama_perusahaan ?? '-' }}</td>
                    <td>{{ $p->email ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px;">Tidak ada data peserta</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="footer-box">
        <tr>
            <td width="70%" class="valign-top text-left">
                <div class="note-label">NOTE:</div>
                <div class="note-text">
                    {{ $no->note_peserta ?? '' }}
                </div>
            </td>

            <td width="30%" class="valign-top text-center">
                <div style="height: 10px;"></div>

                <div class="sign-area"
                    style="background-image: url('{{ public_path('assets/img/bg-sign.png') }}');
                        background-repeat: no-repeat;
                        background-size: contain;
                        opacity: 0.7;">

                    @if (!empty($ttd->ttd) && Storage::exists('public/ttd/' . $ttd->ttd))
                        <img src="{{ public_path('storage/ttd/' . $ttd->ttd) }}" class="signature-image">
                    @else
                        <div style="height: 60px;"></div>
                    @endif

                    <div class="sign-name">{{ $ttd->nama_lengkap ?? 'Admin Inixindo' }}</div>
                </div>
            </td>
        </tr>
    </table>

</body>

</html>
