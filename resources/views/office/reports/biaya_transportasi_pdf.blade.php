<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Biaya Transportasi</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .header h2 {
            margin: 0;
            font-size: 16px;
        }

        .info {
            margin-bottom: 15px;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            margin-top: 20px;
            font-size: 10px;
            color: #666;
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>LAPORAN BIAYA TRANSPORTASI DRIVER</h2>
    </div>

    <div class="info">
        <p><strong>Periode:</strong> {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d M Y') : 'Awal' }} s/d
            {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d M Y') : 'Sekarang' }}</p>
        @if ($filterTipe)
            <p><strong>Tipe:</strong> {{ $filterTipe }}</p>
        @endif
        @if ($filterStatus)
            <p><strong>Status:</strong> {{ $filterStatus }}</p>
        @endif
        <p><strong>Dicetak pada:</strong> {{ $generatedAt }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 8%;">Bulan</th>
                <th style="width: 8%;">Minggu</th>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 15%;">Driver</th>
                <th style="width: 20%;">Koordinasi / Tujuan</th>
                <th style="width: 10%;">Tipe</th>
                <th style="width: 12%;" class="text-right">Harga</th>
                <th style="width: 10%;">Operasional Kantor</th>
                <th style="width: 10%;">Sisa Budget Minggu</th>
                <th>Keterangan</th>
                <th style="width: 10%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $index => $item)
                @php
                    $driverName = '-';
                    $koordinasi = '-';

                    if (!empty($item->id_pengajuan_spj) && $item->SPJ) {
                        $driverName = $item->SPJ->karyawan?->nama_lengkap ?? '-';
                        $koordinasi = 'SPJ: ' . ($item->SPJ->tujuan ?? '-');
                    } elseif ($item->id_pickup_driver == 999999999) {
                        $koordinasi = 'Diluar Koordinasi Driver';
                    } elseif ($item->pickupDriver) {
                        $driverName = $item->pickupDriver->karyawan?->nama_lengkap ?? '-';
                        $lokasi = $item->pickupDriver->detailPickupDriver->first()->lokasi ?? '-';
                        $koordinasi = "{$driverName} | {$lokasi}";
                    }
                @endphp
                @php
                    $isOperasional = isset($budgetMap[$item->id]);
                    $sisaBudget = $budgetMap[$item->id]['sisa_budget'] ?? null;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('M') }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($item->created_at)->weekOfMonth }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d M Y') }}</td>
                    <td>{{ $driverName }}</td>
                    <td>{{ $koordinasi }}</td>
                    <td>{{ $item->tipe }}</td>
                    <td class="text-right">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                    <td>{{ $isOperasional ? 'Ya' : 'Tidak' }}</td>
                    <td class="text-right">{{ $isOperasional ? 'Rp ' . number_format($sisaBudget, 0, ',', '.') : '-' }}</td>
                    <td>{{ $item->keterangan ?? '-' }}</td>
                    <td>{{ $item->pengajuan_barang?->tracking?->tracking ?? 'Menunggu' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Total Data: {{ $data->count() }} record</p>
    </div>
</body>

</html>
