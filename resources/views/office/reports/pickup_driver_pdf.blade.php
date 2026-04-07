<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Koordinasi Driver & Biaya Transportasi</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            margin: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 5px 0;
        }

        .header p {
            margin: 3px 0;
            font-style: italic;
        }

        .meta {
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background: #f0f0f0;
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
            font-weight: bold;
        }

        td {
            padding: 6px 8px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .status-menunggu {
            background: #fff3cd;
            padding: 2px 6px;
            border-radius: 3px;
        }

        .status-diterima {
            background: #cce5ff;
            padding: 2px 6px;
            border-radius: 3px;
        }

        .status-selesai {
            background: #d4edda;
            padding: 2px 6px;
            border-radius: 3px;
        }

        .total-row {
            font-weight: bold;
            background: #f8f9fa;
        }

        .page-break {
            page-break-before: always;
        }

        .signature {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            text-align: center;
            width: 45%;
        }

        .signature-box .line {
            border-top: 1px solid #333;
            margin: 40px 0 5px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>LAPORAN KOORDINASI DRIVER & BIAYA TRANSPORTASI</h2>
        <p>Office Management System</p>
        <p>Periode: {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d F Y') : 'Awal' }} s/d
            {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d F Y') : 'Sekarang' }}</p>
        <p>Diexport: {{ \Carbon\Carbon::now()->format('d F Y H:i:s') }}</p>
    </div>

    <div class="meta">
        <strong>Filter:</strong>
        Kendaraan: {{ $filterKendaraan ?? 'Semua' }} |
        Status: {{ $filterStatusText ?? 'Semua' }}
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="12%">Tanggal</th>
                <th width="15%">Driver</th>
                <th width="10%">Kendaraan</th>
                <th width="10%">Budget</th>
                <th width="10%">Total Biaya</th>
                <th width="10%">Sisa</th>
                <th width="8%">Status</th>
                <th width="20%">Detail Rute</th>
            </tr>
        </thead>
        <tbody>
            @php
                $no = 1;
                $grandTotalBudget = 0;
                $grandTotalBiaya = 0;
            @endphp
            @foreach ($data as $pickup)
                @php
                    $totalBiaya = $pickup->biayaTransportasi->sum('harga');
                    $sisaBudget = $pickup->budget ? $pickup->budget - $totalBiaya : null;
                    $grandTotalBudget += $pickup->budget ?? 0;
                    $grandTotalBiaya += $totalBiaya;

                    $statusClass = match ($pickup->status_apply) {
                        0 => 'status-menunggu',
                        1 => 'status-diterima',
                        2 => 'status-selesai',
                        default => '',
                    };
                    $statusText = match ($pickup->status_apply) {
                        0 => 'Menunggu',
                        1 => 'Diterima',
                        2 => 'Selesai',
                        default => '-',
                    };

                    $detailRute = $pickup->detailPickupDriver
                        ->map(function ($d) {
                            return "• {$d->tipe}: {$d->lokasi}";
                        })
                        ->implode('<br>');
                @endphp
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td>{{ \Carbon\Carbon::parse($pickup->created_at)->format('d/m/Y') }}</td>
                    <td>{{ $pickup->karyawan->nama_lengkap ?? '-' }}</td>
                    <td>{{ $pickup->kendaraan ?? '-' }}</td>
                    <td class="text-right">{{ $pickup->budget ? 'Rp ' . number_format($pickup->budget, 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right">Rp {{ number_format($totalBiaya, 0, ',', '.') }}</td>
                    <td class="text-right">
                        {{ $sisaBudget !== null ? 'Rp ' . number_format($sisaBudget, 0, ',', '.') : '-' }}</td>
                    <td class="text-center"><span class="{{ $statusClass }}">{{ $statusText }}</span></td>
                    <td>{!! $detailRute !!}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="4" class="text-right">TOTAL</td>
                <td class="text-right">Rp {{ number_format($grandTotalBudget, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($grandTotalBiaya, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($grandTotalBudget - $grandTotalBiaya, 0, ',', '.') }}</td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>
</body>

</html>
