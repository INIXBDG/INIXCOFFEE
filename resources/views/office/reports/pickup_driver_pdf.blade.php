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
                <th width="10%">KM Awal</th>
                <th width="10%">KM Akhir</th>
                <th width="8%">Status</th>
                <th width="20%">Detail Rute</th>
            </tr>
        </thead>
        <tbody>
            @php
                $runningBudget = [];
            @endphp

            @foreach ($data as $pickup)
                @php
                    $kendaraan = $pickup->kendaraan ?? 'UNKNOWN';
                    $startOfWeek = \Carbon\Carbon::parse($pickup->created_at)->startOfWeek();
                    $weekKey = $kendaraan . '_' . $startOfWeek->format('Y-m-d');

                    if (!isset($runningBudget[$weekKey])) {
                        $runningBudget[$weekKey] = 1000000;
                    }

                    $totalBiaya = $pickup->biayaTransportasi->sum('harga');

                    if ($pickup->tipe_perjalanan === 'Operasional Kantor') {
                        $budgetAwal = $runningBudget[$weekKey];
                        $runningBudget[$weekKey] -= $totalBiaya;
                        $sisaBudget = $runningBudget[$weekKey];
                    } else {
                        $budgetAwal = null;
                        $sisaBudget = null;
                    }
                @endphp

                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ \Carbon\Carbon::parse($pickup->created_at)->format('d/m/Y') }}</td>
                    <td>{{ $pickup->karyawan->nama_lengkap ?? '-' }}</td>
                    <td>{{ $kendaraan }}</td>
                    <td class="text-right">
                        {{ $budgetAwal ? 'Rp ' . number_format($budgetAwal, 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right">Rp {{ number_format($totalBiaya, 0, ',', '.') }}</td>
                    <td class="text-right">
                        {{ $sisaBudget !== null ? 'Rp ' . number_format($sisaBudget, 0, ',', '.') : '-' }}
                    </td>
                    <td>{{ $pickup->KM_awal ?? '-' }}</td>
                    <td>{{ $pickup->KM_akhir ?? '-' }}</td>
                    <td>{{ $pickup->status_apply }}</td>
                    <td>
                        {!! $pickup->detailPickupDriver->map(fn($d) => "• {$d->tipe}: {$d->lokasi}")->implode('<br>') !!}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
