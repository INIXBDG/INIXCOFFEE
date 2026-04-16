<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Biaya Transportasi</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
        }

        .info {
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            margin-top: 20px;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>LAPORAN BIAYA TRANSPORTASI DRIVER</h2>
    </div>

    <div class="info">
        <p>Periode: {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d M Y') : 'Awal' }} s/d
            {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d M Y') : 'Sekarang' }}</p>
        @if ($filterTipe)
            <p>Tipe: {{ $filterTipe }}</p>
        @endif
        @if ($filterStatus)
            <p>Status: {{ $filterStatus }}</p>
        @endif
        <p>Dicetak pada: {{ $generatedAt }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Bulan</th>
                <th>Minggu</th>
                <th>Tanggal</th>
                <th>Driver</th>
                <th>Koordinasi</th>
                <th>Tipe</th>
                <th class="text-right">Harga</th>
                <th>Keterangan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $index => $item)
                @php
                    $koordinasi = '-';
                    if ($item->id_pickup_driver == 999999999) {
                        $koordinasi = 'Diluar Koordinasi Driver';
                    } elseif ($item->pickupDriver) {
                        $driver = $item->pickupDriver->karyawan?->nama_lengkap ?? '-';
                        $lokasi = $item->pickupDriver->detailPickupDriver->first()->lokasi ?? '-';
                        $koordinasi = "{$driver} | {$lokasi}";
                    }
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('M') }}</td>
                    <td>{{\Carbon\Carbon::parse($item->created_at)->weekOfMonth}}</td>
                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d M Y') }}</td>
                    <td>{{ $item->pickupDriver?->karyawan?->nama_lengkap ?? '-' }}</td>
                    <td>{{ $koordinasi }}</td>
                    <td>{{ $item->tipe }}</td>
                    <td class="text-right">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                    <td>{{ $item->keterangan ?? '-' }}</td>
                    <td>{{ $item->pengajuan_barang?->tracking?->tracking ?? 'Menunggu' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Total Data: {{ $data->count() }}</p>
    </div>
</body>

</html>
