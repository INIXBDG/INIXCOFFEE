<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Rekap SPJ</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 15px;
        }

        h2 {
            margin-bottom: 2px;
            font-size: 16px;
        }

        h3 {
            margin-top: 15px;
            margin-bottom: 5px;
            font-size: 13px;
            color: #333;
        }

        p {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 11px;
        }

        hr {
            margin-bottom: 15px;
            border: 0;
            border-top: 1px solid #ccc;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            page-break-inside: auto;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        thead {
            display: table-header-group;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: left;
            font-size: 11px;
        }

        th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }

        .filter-info {
            text-align: center;
            font-size: 10px;
            color: #555;
        }
    </style>
</head>

<body>
    <h2 style="text-align: center;">Rekapitulasi Surat Perjalanan Dinas (SPD/SPJ)</h2>
    <p style="text-align: center;">Tanggal Cetak: {{ now()->translatedFormat('l, d F Y') }}</p>
    @isset($filter)
        <p class="filter-info">
            Tahun: {{ $filter['tahun'] }} &nbsp;|&nbsp;
            Periode: {{ $filter['periode'] }} &nbsp;|&nbsp;
            Jenis Dinas: {{ $filter['jenis_dinas'] }} &nbsp;|&nbsp;
            Divisi: {{ $filter['divisi'] }}
        </p>
    @endisset
    <hr>

    <h3>1. Rekap Per Divisi</h3>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Nama Divisi</th>
                <th width="15%">Jumlah SPJ</th>
                <th width="25%">Total Pengeluaran</th>
            </tr>
        </thead>
        <tbody>
            @php $total1 = 0; @endphp
            @forelse ($tab1 as $index => $item)
                @php $total1 += $item['total']; @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item['divisi'] }}</td>
                    <td class="text-center">{{ $item['jumlah_spj'] }}</td>
                    <td class="text-right">Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">Tidak ada data.</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="3" class="text-right">TOTAL KESELURUHAN:</td>
                <td class="text-right">Rp {{ number_format($total1, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <h3>2. Rekap Per Status SPJ</h3>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Status SPJ</th>
                <th width="12%">Jumlah SPJ</th>
                <th width="12%">Persentase</th>
                <th width="25%">Total Pengeluaran</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalStatus = 0;
                $countStatus = 0;
            @endphp
            @foreach ($tabStatus as $index => $item)
                @php
                    $totalStatus += $item['total'];
                    $countStatus += $item['jumlah_spj'];
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item['status'] }}</td>
                    <td class="text-center">{{ $item['jumlah_spj'] }}</td>
                    <td class="text-center">{{ $item['persentase'] }}%</td>
                    <td class="text-right">Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2" class="text-right">TOTAL KESELURUHAN:</td>
                <td class="text-center">{{ $countStatus }}</td>
                <td></td>
                <td class="text-right">Rp {{ number_format($totalStatus, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <h3>3. Statistik Pengeluaran</h3>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Kategori / Jenis SPJ</th>
                <th width="20%">Total Pengeluaran</th>
                <th width="15%">Persentase</th>
                <th width="15%">Gap vs Periode Lalu</th>
            </tr>
        </thead>
        <tbody>
            @php $total3 = 0; @endphp
            @foreach ($tab3 as $index => $item)
                @php $total3 += $item['total']; @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item['kategori'] }}</td>
                    <td class="text-right">Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ $item['persentase'] }}%</td>
                    <td class="text-center">{{ $item['gap'] }}%</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2" class="text-right">TOTAL:</td>
                <td class="text-right">Rp {{ number_format($total3, 0, ',', '.') }}</td>
                <td class="text-center">100%</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</body>

</html>