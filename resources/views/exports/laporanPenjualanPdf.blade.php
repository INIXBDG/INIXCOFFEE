<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .container {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            padding: 15px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #2F80ED;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #2F80ED;
            font-size: 20px;
        }
        .summary {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            border-left: 4px solid {{ $isWin ? '#10B981' : '#EF4444' }};
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            text-align: center;
        }
        .summary-item strong {
            display: block;
            font-size: 11px;
            color: #666;
        }
        .summary-value {
            font-weight: bold;
            font-size: 14px;
            margin-top: 4px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table th {
            background-color: {{ $isWin ? '#10B981' : '#EF4444' }};
            color: white;
            padding: 8px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #ddd;
        }
        .table td {
            padding: 7px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .text-end {
            text-align: right !important;
        }
        .text-start {
            text-align: left !important;
        }
        .text-center {
            text-align: center !important;
        }
        .page-break {
            page-break-after: always;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #777;
        }
    </style>
</style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $title }}</h1>
            <p>Dicetak pada: {{ now()->format('d F Y H:i') }}</p>
        </div>

        <!-- Ringkasan -->
        <div class="summary">
            <div class="summary-grid">
                <div class="summary-item">
                    <strong>Total Harga Jual</strong>
                    <div class="summary-value">{{ 'Rp ' . number_format($summary->total_harga_jual, 0, ',', '.') }}</div>
                </div>
                <div class="summary-item">
                    <strong>Total PA</strong>
                    <div class="summary-value">{{ 'Rp ' . number_format($summary->total_netsales, 0, ',', '.') }}</div>
                </div>
                <div class="summary-item">
                    <strong>Total Exam</strong>
                    <div class="summary-value">{{ 'Rp ' . number_format($summary->total_exam, 0, ',', '.') }}</div>
                </div>
                <div class="summary-item">
                    <strong>Net Sales</strong>
                    <div class="summary-value">{{ 'Rp ' . number_format($summary->total_grand, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        <!-- Tabel Data -->
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Sales</th>
                    <th>Materi</th>
                    <th>Perusahaan</th>
                    <th>Pax</th>
                    <th>Harga</th>
                    <th>Exam</th>
                    <th>Total PA</th>
                    <th>Net Sales</th>
                    <th>Tgl Awal</th>
                    <th>Tgl Akhir</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                    <tr>
                        <td class="text-center">{{ $row['id'] }}</td>
                        <td class="text-center">{{ $row['sales_key'] }}</td>
                        <td class="text-start">{{ $row['nama_materi'] }}</td>
                        <td class="text-start">{{ $row['nama_perusahaan'] }}</td>
                        <td class="text-center">{{ $row['pax'] }}</td>
                        <td class="text-end">{{ 'Rp ' . number_format($row['harga'], 0, ',', '.') }}</td>
                        <td class="text-end">{{ 'Rp ' . number_format($row['total_exam'], 0, ',', '.') }}</td>
                        <td class="text-end">{{ 'Rp ' . number_format($row['netsales'], 0, ',', '.') }}</td>
                        <td class="text-end" style="font-weight: bold; {{ $isWin ? 'color: #10B981;' : 'color: #EF4444;' }}">
                            {{ 'Rp ' . number_format($row['grandtotal'], 0, ',', '.') }}
                        </td>
                        <td class="text-center">{{ $row['tanggal_awal'] ? \Carbon\Carbon::parse($row['tanggal_awal'])->format('d/m/Y') : '-' }}</td>
                        <td class="text-center">{{ $row['tanggal_akhir'] ? \Carbon\Carbon::parse($row['tanggal_akhir'])->format('d/m/Y') : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            Laporan ini dihasilkan secara otomatis dari sistem CRM.
        </div>
    </div>
</body>
</html>