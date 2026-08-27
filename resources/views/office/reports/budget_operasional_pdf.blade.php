<!DOCTYPE html>
<html>

<head>
    <title>Laporan Budget Operasional</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h3 {
            margin: 0;
            font-size: 16px;
        }

        .header p {
            margin: 5px 0 0;
            color: #666;
            font-size: 10px;
        }

        .card {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            page-break-inside: avoid;
        }

        .card-header {
            font-weight: bold;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            font-size: 12px;
        }

        .summary-box {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            text-align: center;
        }

        .summary-item {
            flex: 1;
            padding: 5px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            margin: 0 2px;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: left;
        }

        th {
            background-color: #f1f3f5;
            font-size: 10px;
        }

        td {
            font-size: 10px;
        }

        .text-end {
            text-align: right;
        }

        .text-danger {
            color: #dc3545;
        }

        .text-success {
            color: #198754;
        }
    </style>
</head>

<body>
    <div class="header">
        <h3>Laporan Budget Operasional Kantor</h3>
        <p>Periode: {{ $monthName }} {{ $weekText }}</p>
        <p>Dicetak pada: {{ $generatedAt }}</p>
    </div>

    @foreach ($weeksData as $w)
        <div class="card">
            <div class="card-header">
                <span>Minggu: {{ $w['week_start'] }} s/d {{ $w['week_end'] }}</span>
                <span style="color: {{ $w['sisa_budget'] < 0 ? '#dc3545' : '#198754' }}">
                    Sisa: Rp {{ number_format($w['sisa_budget'], 0, ',', '.') }}
                </span>
            </div>
            <div class="summary-box">
                <div class="summary-item">Budget Awal<br><b>Rp {{ number_format($w['budget_awal'], 0, ',', '.') }}</b>
                </div>
                <div class="summary-item">Tambahan<br><b>Rp {{ number_format($w['total_tambahan'], 0, ',', '.') }}</b>
                </div>
                <div class="summary-item">Terpakai<br><b>Rp {{ number_format($w['total_terpakai'], 0, ',', '.') }}</b>
                </div>
                <div class="summary-item">Sisa<br><b>Rp {{ number_format($w['sisa_budget'], 0, ',', '.') }}</b></div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th width="15%">Tanggal</th>
                        <th width="20%">Sumber</th>
                        <th width="25%">Tipe</th>
                        <th width="20%" class="text-end">Harga</th>
                        <th width="20%" class="text-end">Sisa Setelah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($w['items'] as $item)
                        <tr>
                            <td>{{ $item['tanggal'] }}</td>
                            <td>{{ $item['sumber'] }}</td>
                            <td>{{ $item['tipe'] }}</td>
                            <td class="text-end">Rp {{ number_format($item['harga'], 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($item['sisa_setelah'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
</body>

</html>
