<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <style>
        @page {
            margin: 150px 55px 90px 60px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111827;
            line-height: 1.55;
        }

        .bg {
            position: fixed;
            top: -150px;
            left: -60px;
            width: 210mm;
            height: 297mm;
            z-index: -1000;
        }

        .bg img {
            width: 100%;
            height: 100%;
        }

        .kop {
            position: fixed;
            top: -135px;
            left: 0;
            width: 100%;
        }

        table.kop-tbl {
            width: 100%;
            border-collapse: collapse;
        }

        table.kop-tbl td {
            vertical-align: middle;
            padding: 0 0 8px 0;
            border-bottom: 2.5px solid #1f4e79;
        }

        .kop-logo {
            width: 90px;
        }

        .kop-logo img {
            height: 64px;
        }

        .kop-text {
            text-align: center;
            padding-right: 90px !important;
        }

        .kop-nama {
            font-size: 18px;
            font-weight: bold;
            color: #1f4e79;
            letter-spacing: 2px;
            margin: 0;
        }

        .kop-unit {
            font-size: 10px;
            font-weight: bold;
            color: #374151;
            margin: 1px 0 0;
        }

        .kop-alamat {
            font-size: 8.5px;
            color: #4b5563;
            margin: 1px 0 0;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .justify {
            text-align: justify;
        }

        table.meta {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 12px;
        }

        table.meta td {
            padding: 1px 0;
            vertical-align: top;
        }

        table.meta td.lbl {
            width: 62px;
        }

        table.meta td.sep {
            width: 10px;
        }

        table.meta td.tgl {
            text-align: right;
            width: 38%;
        }

        .tujuan {
            margin-bottom: 12px;
            line-height: 1.5;
        }

        .judul {
            text-align: center;
            margin: 4px 0 12px;
        }

        .judul h2 {
            font-size: 12px;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-decoration: underline;
        }

        .judul p {
            margin: 2px 0 0;
            font-size: 9px;
            color: #4b5563;
        }

        p.para {
            margin: 0 0 8px;
            text-align: justify;
        }

        table.detail {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0 8px;
        }

        table.detail td {
            padding: 4px 8px;
            border: 1px solid #9ca3af;
            vertical-align: top;
            font-size: 9.5px;
        }

        table.detail td.k {
            width: 30%;
            background: #f3f4f6;
            font-weight: bold;
        }

        table.detail tr.nominal td {
            background: #eef2ff;
            font-size: 11px;
            font-weight: bold;
            border: 1.5px solid #1f4e79;
        }

        .terbilang {
            font-style: italic;
            margin-bottom: 8px;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        table.data thead {
            display: table-header-group;
        }

        table.data th {
            background: #1f4e79;
            color: #ffffff;
            border: 1px solid #000000;
            padding: 5px 3px;
            font-size: 8px;
            text-transform: uppercase;
        }

        table.data td {
            border: 1px solid #9ca3af;
            padding: 4px 4px;
            font-size: 8.5px;
            vertical-align: middle;
        }

        table.data tr {
            page-break-inside: avoid;
        }

        table.data tr.total td {
            background: #e7e6e6;
            font-weight: bold;
            border: 1.5px solid #000000;
        }

        .sub {
            font-size: 7.5px;
            color: #6b7280;
        }

        .ttd-wrap {
            margin-top: 18px;
            page-break-inside: avoid;
        }

        .ttd-tanggal {
            text-align: right;
            margin-bottom: 8px;
        }

        table.ttd {
            width: 100%;
            border-collapse: collapse;
        }

        table.ttd td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 0 8px;
        }

        .ttd-role {
            font-weight: bold;
        }

        .ttd-space {
            height: 62px;
        }

        .ttd-line {
            border-top: 1px solid #000000;
            margin: 0 14px;
            padding-top: 3px;
            font-size: 9px;
            color: #4b5563;
        }
    </style>
</head>

<body>
    @if (!$polos && $background)
        <div class="bg"><img src="{{ $background }}" alt=""></div>
    @endif

    @if (!$polos)
        <div class="kop">
            <table class="kop-tbl">
                <tr>
                    <td class="kop-logo">
                        @if ($logo)
                            <img src="{{ $logo }}" alt="Logo">
                        @endif
                    </td>
                    <td class="kop-text">
                        <p class="kop-nama">{{ $profil['nama'] }}</p>
                        <p class="kop-unit">{{ $profil['unit'] }}</p>
                        <p class="kop-alamat">{{ $profil['alamat'] }}</p>
                        <p class="kop-alamat">{{ $profil['kontak'] }}</p>
                    </td>
                </tr>
            </table>
        </div>
    @endif

    @yield('content')
</body>

</html>
