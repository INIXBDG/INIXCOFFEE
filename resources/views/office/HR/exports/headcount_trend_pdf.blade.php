{{-- resources/views/office/HR/exports/headcount_trend_pdf.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Headcount Trend Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .meta {
            margin-bottom: 15px;
            font-size: 10px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background: #f5f5f5;
            font-weight: 600;
        }

        .summary {
            background: #f9f9f9;
            padding: 10px;
            border-radius: 4px;
        }

        .summary-item {
            display: inline-block;
            margin-right: 20px;
        }

        .chart-placeholder {
            text-align: center;
            padding: 40px;
            background: #f5f5f5;
            border-radius: 4px;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <h2>Laporan Trend Headcount</h2>
    <div class="meta">
        <div>Periode: {{ $date_range }}</div>
        <div>Generated: {{ $generated_at }}</div>
    </div>

    <div class="summary">
        <strong>Summary:</strong>
        <span class="summary-item">Total Active: {{ $trend['summary']['total_active'] }}</span>
        <span class="summary-item">Total New: {{ $trend['summary']['total_new'] }}</span>
        <span class="summary-item">Total Resign: {{ $trend['summary']['total_resign'] }}</span>
        <span class="summary-item">Avg New/Bulan: {{ $trend['summary']['avg_monthly_new'] }}</span>
    </div>

    <div class="chart-placeholder">
        <strong>📊 Grafik Trend Headcount</strong><br>
        <small>(Visualisasi grafik tersedia pada versi interaktif web)</small>
    </div>

    <table>
        <thead>
            <tr>
                <th>Periode</th>
                <th>Active</th>
                <th>New Hire</th>
                <th>Resign</th>
            </tr>
        </thead>
        <tbody>
            @for ($i = 0; $i < count($trend['labels']); $i++)
                <tr>
                    <td>{{ $trend['labels'][$i] }}</td>
                    <td>{{ $trend['datasets'][0]['data'][$i] }}</td>
                    <td>{{ $trend['datasets'][1]['data'][$i] }}</td>
                    <td>{{ $trend['datasets'][2]['data'][$i] }}</td>
                </tr>
            @endfor
        </tbody>
    </table>
</body>

</html>
