<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1e293b;
        }

        h2 {
            margin: 0 0 2px;
        }

        h3 {
            margin: 16px 0 6px;
        }

        .sub {
            color: #64748b;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #1f4e79;
            color: #fff;
            padding: 5px;
            text-align: left;
        }

        td {
            border-bottom: 1px solid #e2e8f0;
            padding: 4px 5px;
            vertical-align: top;
        }

        .r {
            text-align: right;
        }

        tfoot td {
            font-weight: bold;
            background: #f1f5f9;
        }
    </style>
</head>

<body>
    <h2>Laporan Maintenance {{ $tahun }}</h2>
    <div class="sub">Dicetak {{ now()->translatedFormat('d F Y H:i') }}</div>

    @foreach (['Maintenance Kendaraan' => $kendaraan, 'Maintenance Kantor' => $kantor] as $judul => $rows)
        <h3>{{ $judul }}</h3>
        <table>
            <thead>
                <tr>
                    <th width="4%">No</th>
                    <th width="9%">Tanggal</th>
                    <th width="9%">No. KK</th>
                    <th width="22%">Jenis</th>
                    <th>Keterangan</th>
                    <th class="r" width="13%">Biaya</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $i => $x)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $x->tanggal->format('d-m-Y') }}</td>
                        <td>{{ $x->no_kk ?: '-' }}</td>
                        <td>{{ $x->jenis_maintenance }}</td>
                        <td>{{ $x->keterangan ?: '-' }}</td>
                        <td class="r">Rp {{ number_format($x->biaya, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center;color:#64748b">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="r">TOTAL</td>
                    <td class="r">Rp {{ number_format($rows->sum('biaya'), 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    @endforeach

    <h3>Ringkasan</h3>
    <table style="width:50%">
        <tr>
            <td>Total Kendaraan</td>
            <td class="r">Rp {{ number_format($kendaraan->sum('biaya'), 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Total Kantor</td>
            <td class="r">Rp {{ number_format($kantor->sum('biaya'), 0, ',', '.') }}</td>
        </tr>
        <tfoot>
            <tr>
                <td>Grand total</td>
                <td class="r">Rp
                    {{ number_format($kendaraan->sum('biaya') + $kantor->sum('biaya'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>

</html>
