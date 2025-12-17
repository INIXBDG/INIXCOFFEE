<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>PO Modul - {{ $modul->first()->no_modul ?? '' }}</title>
    <style>
        /* 1. SETUP HALAMAN */
        @page {
            size: A4 landscape;
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

        /* 4. LOGO KANAN ATAS */
        .logo-text {
            font-size: 26px;
            font-weight: bold;
            color: #333;
            text-transform: lowercase;
        }

        /* 5. JUDUL DOKUMEN */
        .doc-title {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
            display: inline-block;
        }

        /* 6. INFO SECTION (NESTED TABLE) */
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            border: none;
        }

        .info-table>tbody>tr>td {
            vertical-align: top;
            padding: 2px 0;
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

        /* 7. MAIN DATA TABLE */
        .data-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .data-table tbody td {
            height: 40px;
            max-height: 40px;
            vertical-align: middle;
            overflow: hidden;
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
        }

        /* 8. FOOTER BOX */
        .footer-box {
            width: 40%;
            border-collapse: collapse;
            border: none;
            margin-top: 20px;
            table-layout: fixed;
        }

        .footer-box td {
            vertical-align: top;
            padding: 10px;
            border: none;
            overflow: hidden;
        }

        /* 9. NOTE TEXT WRAPPING */
        .note-text {
            font-size: 10px;
            width: 100%;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-all;
            white-space: normal;
        }

        .sign-area {
            text-align: center;
            min-height: 100px;
            display: block;
        }

        .sign-name {
            font-weight: bold;
            margin-top: 5px;
            font-size: 10px;
            position: relative;
            z-index: 2;
        }

        .signature-image {
            height: 60px;
            max-width: 100%;
            width: auto;
            object-fit: contain;
            display: inline-block;
            position: relative;
            z-index: 2;
        }
    </style>
</head>

<body>

    <table class="w-100">
        <tr>
            <td class="valign-top" width="60%">
                <table class="meta-table">
                    <tr>
                        <td class="meta-label">To</td>
                        <td> Mba Yana</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Subject</td>
                        <td> Materi</td>
                    </tr>
                    <tr>
                        <td class="meta-label">From</td>
                        <td>{{ explode(' ', $ttd->nama_lengkap)[0] }} Inixindo Bandung</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="w-100" style="margin-top: 20px; margin-bottom: 20px;">
        <tr>
            <td width="20%" class="valign-mid text-left">
                <img src="{{ public_path('assets/img/logo.png') }}" style="height: 90px; width: 145%;" alt="Logo">
            </td>

            <td width="60%" class="valign-mid text-center">
                <div class="doc-title">FORMULIR PEMESANAN MATERI</div>
            </td>

            <td width="20%"></td>
        </tr>
    </table>

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
                        <td class="label-col">Dan/UP</td>
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

    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" width="3%">NO</th>
                <th rowspan="2" width="8%">KODE MATERI</th>
                <th rowspan="2" width="20%">MATERI TRAINING</th>
                <th rowspan="2" width="10%">PERIODE TRAINING</th>
                <th rowspan="2" width="7%">PESERTA</th>
                <th rowspan="2" width="7%">TELP/EMAIL</th>
                <th colspan="3" width="9%">NAMA MATERI</th>
                <th colspan="2" width="12%">HARGA SATUAN (Rp)</th>
                <th colspan="2" width="12%">SUB TOTAL (Rp)</th>
                <th colspan="2" width="12%">TOTAL (Rp)</th>
            </tr>
            <tr>
                <th>ASLI</th>
                <th>COPY</th>
                <th>JML</th>

                <th width="6%"></th>
                <th width="6%"></th>

                <th width="6%"></th>
                <th width="6%"></th>

                <th width="6%"></th>
                <th width="6%"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($modul as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->kode_materi }}</td>
                    <td>{{ $item->nama_materi }}</td>
                    <td class="text-center">
                        {{ date('d', strtotime($item->awal_training)) }}-{{ date('d M Y', strtotime($item->akhir_training)) }}
                    </td>
                    <td></td>
                    <td></td>

                    <td class="text-center">{{ $item->jumlah }}</td>
                    <td></td>
                    <td class="text-center">{{ $item->jumlah }}</td>

                    <td></td>
                    <td class="text-right">{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                    <td></td>
                    <td class="text-right">{{ number_format($item->total, 0, ',', '.') }}</td>
                    <td></td>
                    <td class="text-right">{{ number_format($item->total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="14" class="text-right bold">TOTAL</td>

                <td class="text-right bold">
                    Rp {{ number_format($grandTotal, 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    <table class="footer-box">
        <tr>
            <td colspan="2" style="padding: 0; border: 1px solid #000;">
                <table class="w-100" style="border-collapse: collapse;">
                    <tr>
                        <td width="80%"
                            style="height: 50px; vertical-align: top; border-right: 1px solid #000; padding: 5px;">
                            <div style="font-weight: bold; margin-bottom: 2px;"></div>
                        </td>

                        <td width="20%" style="height: 50px; vertical-align: top; padding: 5px;">
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr style="height: 15px;">
            <td colspan="2" style="border: none;"></td>
        </tr>

        <tr>
            <td width="50%" style="height: 100px; border: 1px solid #000;">
                <div style="font-weight: bold; margin-bottom: 5px;">NOTE:</div>
                <div class="note-text">
                    {{ $no->note_modul }}
                </div>
            </td>

            <td width="50%" style="vertical-align: bottom; text-align: center; border: 1px solid #000;">
                <div class="sign-area"
                    style="background-image: url('{{ public_path('assets/img/bg-sign.png') }}');
                           background-position: 15% 2%;
                           background-repeat: no-repeat;
                           background-size: 50% auto;
                           position: relative;
                           opacity: 65%">

                    @if (!empty($ttd->ttd) && Storage::exists('public/ttd/' . $ttd->ttd))
                        <img src="{{ public_path('storage/ttd/' . $ttd->ttd) }}" class="signature-image">
                    @else
                        <div style="height: 60px;"></div>
                    @endif

                    <div class="sign-name">{{ $ttd->nama_lengkap ?? 'Nama Penandatangan' }}</div>
                </div>
            </td>
        </tr>
    </table>
</body>

</html>
