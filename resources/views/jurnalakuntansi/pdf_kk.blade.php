<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Kas Kecil - {{ $periode_label }}</title>
    <style>
        @page {
            margin: 25px 30px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #000;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-bold {
            font-weight: bold;
        }

        .header-title {
            font-size: 13px;
            font-weight: bold;
        }

        .header-sub {
            font-size: 11px;
            font-weight: bold;
            margin-top: 2px;
        }

        .header-periode {
            font-size: 10px;
            margin-top: 2px;
            margin-bottom: 10px;
        }

        table.main-table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: auto;
        }

        table.main-table thead {
            display: table-header-group;
        }

        /* Setiap <tr> di sini mewakili SATU record penuh (baris utama + semua
           rinciannya, dibungkus tabel bersarang). Ini yang membuat DomPDF
           menganggapnya 1 unit yang tidak boleh terbelah antar halaman. */
        table.main-table > tbody > tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        table.main-table > tbody > tr > td.record-cell {
            padding: 0;
            border: none;
        }

        table.record-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.main-table th,
        table.record-table td {
            border: 1px solid #000;
            padding: 3px 5px;
            vertical-align: top;
        }

        table.main-table thead th {
            background-color: #e9e9e9;
            text-align: center;
            font-weight: bold;
        }

        .col-tanggal {
            width: 8%;
        }

        .col-kk {
            width: 10%;
        }

        .col-ket {
            width: 32%;
        }

        .col-cat {
            width: 8%;
            text-align: center;
        }

        .col-debit {
            width: 13%;
            text-align: right;
        }

        .col-kredit {
            width: 13%;
            text-align: right;
        }

        .col-saldo {
            width: 18%;
            text-align: right;
        }

        .rincian-item {
            font-size: 9px;
            color: #333;
            padding-left: 12px;
        }

        .rincian-header {
            font-size: 9px;
            font-weight: bold;
            padding-left: 6px;
            color: #444;
        }

        .rincian-header.ns {
            color: #0d6efd;
        }

        .rincian-header.sp {
            color: #dc3545;
        }

        .rincian-header.pb {
            color: #198754;
        }

        table.record-table tr.no-border td {
            border-top: none;
            border-bottom: none;
        }

        /* Baris terakhir dari record TERAKHIR wajib punya border bawah,
           walau baris itu baris rincian (.no-border) */
        table.record-table tr.force-border-bottom td {
            border-bottom: 1px solid #000 !important;
        }

        table.summary-table {
            width: 45%;
            margin-top: 10px;
            margin-left: 55%;
            border-collapse: collapse;
        }

        table.summary-table td {
            border: 1px solid #000;
            padding: 3px 6px;
        }

        table.summary-table td.label {
            width: 60%;
        }

        table.summary-table td.value {
            width: 40%;
            text-align: right;
        }

        table.ttd-table {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
        }

        table.ttd-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
        }

        .ttd-space {
            height: 45px;
        }

        .ttd-nama {
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="text-center header-title">PT INIXINDO AMIETE MANDIRI</div>
    <div class="text-center header-sub">KAS KECIL ( PETTY CASH )</div>
    <div class="text-center header-periode">{{ $periode_label }}</div>

    @php
        $saldoBerjalan = $saldo_awal;
        $totalDebit = 0;
        $totalKredit = 0;

        $currencyKeys = [
            'transportasi', 'akomodasi_peserta', 'akomodasi_tim', 'fresh_money',
            'entertaint', 'souvenir', 'cashback', 'sewa_laptop',
        ];
    @endphp

    <table class="main-table">
        <thead>
            <tr>
                <th class="col-tanggal">Tanggal</th>
                <th class="col-kk">KK</th>
                <th class="col-ket">Keterangan</th>
                <th class="col-cat">Cat.</th>
                <th class="col-debit">Debit (Rp)</th>
                <th class="col-kredit">Kredit (Rp)</th>
                <th class="col-saldo">Saldo (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $index => $row)
                @php
                    $debit = (float) $row['debit'];
                    $kredit = (float) $row['kredit'];
                    $saldoBerjalan = $saldoBerjalan + $debit - $kredit;
                    $totalDebit += $debit;
                    $totalKredit += $kredit;
                    $tglFormatted = \Carbon\Carbon::parse($row['tanggal_transaksi'])->translatedFormat('d-M');
                    $hasRincian = !empty($row['list_pengajuan']) || !empty($row['net_sales']) || !empty($row['surat_perjalanan']);
                @endphp

                <tr>
                    <td class="record-cell" colspan="7">
                        <table class="record-table">
                            <tr class="{{ !$hasRincian ? 'force-border-bottom' : '' }}">
                                <td class="col-tanggal">{{ $tglFormatted }}</td>
                                <td class="col-kk">{{ $row['nomor_kk'] ?? '-' }}</td>
                                <td class="col-ket">{{ $row['keterangan'] }}</td>
                                <td class="col-cat">{{ $row['no_akun'] ?? '-' }}</td>
                                <td class="col-debit">{{ $debit > 0 ? number_format($debit, 2, ',', '.') : '-' }}</td>
                                <td class="col-kredit">{{ $kredit > 0 ? number_format($kredit, 2, ',', '.') : '-' }}</td>
                                <td class="col-saldo">{{ number_format($saldoBerjalan, 2, ',', '.') }}</td>
                            </tr>

                            {{-- Rincian: Pengajuan Barang (bisa lebih dari satu per jurnal) --}}
                            @if (!empty($row['list_pengajuan']))
                                @foreach ($row['list_pengajuan'] as $pIndex => $pengajuan)
                                    <tr class="no-border">
                                        <td style="width: 8%;"></td>
                                        <td colspan="6" class="rincian-header pb">
                                            Pengajuan Barang — {{ $pengajuan['karyawan']['nama_lengkap'] ?? '-' }}
                                            ({{ $pengajuan['tipe'] ?? '-' }})
                                        </td>
                                    </tr>
                                    @php $detailList = $pengajuan['detail'] ?? []; @endphp
                                    @foreach ($detailList as $dIndex => $item)
                                        @php
                                            $isLastDetailLine = $pIndex === count($row['list_pengajuan']) - 1
                                                && $dIndex === count($detailList) - 1
                                                && empty($row['net_sales'])
                                                && empty($row['surat_perjalanan']);
                                        @endphp
                                        <tr class="no-border {{ $isLastDetailLine ? 'force-border-bottom' : '' }}">
                                            <td></td>
                                            <td colspan="6" class="rincian-item">
                                                {{ $item['qty'] }} x {{ $item['nama_barang'] }}
                                                @if (!empty($item['keterangan']))
                                                    ({{ $item['keterangan'] }})
                                                @endif
                                                — Rp {{ number_format($item['harga'], 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @endif

                            {{-- Rincian: Net Sales --}}
                            @if (!empty($row['net_sales']))
                                @php
                                    $ns = $row['net_sales'];
                                    $nsActiveKeys = array_values(array_filter($currencyKeys, fn($k) => !empty($ns[$k]) && $ns[$k] > 0));
                                @endphp
                                <tr class="no-border">
                                    <td></td>
                                    <td colspan="6" class="rincian-header ns">
                                        Perhitungan Net Sales
                                        @if (!empty($ns['rkm']['materi']['nama_materi']))
                                            — {{ $ns['rkm']['materi']['nama_materi'] }}
                                        @endif
                                        @if (!empty($ns['rkm']['perusahaan']['nama_perusahaan']))
                                            ({{ $ns['rkm']['perusahaan']['nama_perusahaan'] }})
                                        @endif
                                    </td>
                                </tr>
                                @foreach ($nsActiveKeys as $kIndex => $key)
                                    @php
                                        $isLastDetailLine = $kIndex === count($nsActiveKeys) - 1
                                            && empty($row['surat_perjalanan']);
                                    @endphp
                                    <tr class="no-border {{ $isLastDetailLine ? 'force-border-bottom' : '' }}">
                                        <td></td>
                                        <td colspan="6" class="rincian-item">
                                            {{ ucwords(str_replace('_', ' ', $key)) }}
                                            — Rp {{ number_format($ns[$key], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            @endif

                            {{-- Rincian: Surat Perjalanan --}}
                            @if (!empty($row['surat_perjalanan']))
                                @php $sp = $row['surat_perjalanan']; @endphp
                                <tr class="no-border">
                                    <td></td>
                                    <td colspan="6" class="rincian-header sp">
                                        Surat Perjalanan — {{ $sp['karyawan']['nama_lengkap'] ?? '-' }}
                                        ({{ $sp['karyawan']['divisi'] ?? '-' }})
                                    </td>
                                </tr>
                                <tr class="no-border">
                                    <td></td>
                                    <td colspan="6" class="rincian-item">
                                        {{ $sp['tipe'] ?? '-' }} — Tujuan: {{ $sp['tujuan'] ?? '-' }}
                                        @if (!empty($sp['alasan']))
                                            ({{ $sp['alasan'] }})
                                        @endif
                                    </td>
                                </tr>
                                <tr class="no-border force-border-bottom">
                                    <td></td>
                                    <td colspan="6" class="rincian-item">
                                        Berangkat: {{ !empty($sp['tanggal_berangkat']) ? \Carbon\Carbon::parse($sp['tanggal_berangkat'])->translatedFormat('d F Y') : '-' }}
                                        &nbsp;|&nbsp;
                                        Pulang: {{ !empty($sp['tanggal_pulang']) ? \Carbon\Carbon::parse($sp['tanggal_pulang'])->translatedFormat('d F Y') : '-' }}
                                        &nbsp;|&nbsp;
                                        Total: Rp {{ number_format($sp['total'] ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endif
                        </table>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @php
        $saldoAkhirOtomatis = $saldo_awal + $totalDebit - $totalKredit;
        $saldoAkhirTampil = ($saldo_akhir_manual ?? null) !== null ? $saldo_akhir_manual : $saldoAkhirOtomatis;
        $kasMasukTampil = ($kas_masuk_manual ?? 0) > 0 ? $kas_masuk_manual : $totalDebit;
        $kasKeluarTampil = ($kas_keluar_manual ?? 0) > 0 ? $kas_keluar_manual : $totalKredit;
    @endphp

    <table class="summary-table">
        <tr>
            <td class="label">Saldo Awal</td>
            <td class="value">{{ number_format($saldo_awal, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Kas Masuk (Debit)</td>
            <td class="value">{{ number_format($kasMasukTampil, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Kas Keluar (Kredit)</td>
            <td class="value">{{ number_format($kasKeluarTampil, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label text-bold">Saldo Akhir</td>
            <td class="value text-bold">{{ number_format($saldoAkhirTampil, 2, ',', '.') }}</td>
        </tr>
    </table>

    <table class="ttd-table">
        <tr>
            @foreach ($penandatangan as $ttd)
                <td>{{ $ttd['label'] }}</td>
            @endforeach
        </tr>
        <tr>
            @foreach ($penandatangan as $ttd)
                <td class="ttd-space">
                    @php
                        $ttdPath = !empty($ttd['ttd']) ? public_path('storage/ttd/' . $ttd['ttd']) : null;
                    @endphp
                    @if ($ttdPath && file_exists($ttdPath))
                        <img src="{{ $ttdPath }}" alt="{{ $ttd['nama'] }}" style="width: 75px; height: auto; margin: 4%">
                    @endif
                </td>
            @endforeach
        </tr>
        <tr>
            @foreach ($penandatangan as $ttd)
                <td class="ttd-nama">{{ $ttd['nama'] }}</td>
            @endforeach
        </tr>
        <tr>
            @foreach ($penandatangan as $ttd)
                <td>{{ $ttd['jabatan'] }}</td>
            @endforeach
        </tr>
    </table>

</body>

</html>