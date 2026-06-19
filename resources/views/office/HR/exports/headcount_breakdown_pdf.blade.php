{{-- resources/views/office/HR/exports/headcount_breakdown_pdf.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Headcount Breakdown Report</title>
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
            margin-bottom: 20px;
        }

        .bar {
            display: inline-block;
            width: 100px;
            height: 12px;
            background: #e9ecef;
            border-radius: 2px;
            vertical-align: middle;
            margin: 0 10px;
        }

        .bar-fill {
            height: 100%;
            background: #198754;
            border-radius: 2px;
        }
    </style>
</head>

<body>
    <h2>Laporan Breakdown Headcount</h2>
    <div class="meta">
        <div>Filter By: {{ ucfirst($filter_by) }}</div>
        <div>Generated: {{ $generated_at }}</div>
    </div>

    <div class="summary">
        <strong>Summary:</strong>
        <span>Total Kategori: {{ $breakdown['summary']['total_categories'] }}</span> |
        <span>Top Kategori: {{ $breakdown['summary']['top_category'] }}</span> |
        <span>Avg Retention: {{ $breakdown['summary']['avg_retention'] }}%</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Kategori</th>
                <th>Total</th>
                <th>Active</th>
                <th>Resign</th>
                <th>Retention Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($breakdown['breakdown'] as $item)
                <tr>
                    <td>{{ $item['label'] }}</td>
                    <td>{{ $item['total'] }}</td>
                    <td>{{ $item['active'] }}</td>
                    <td>{{ $item['resign'] }}</td>
                    <td>
                        {{ $item['retention'] }}%
                        <span class="bar"><span class="bar-fill"
                                style="width: {{ min($item['retention'], 100) }}%"></span></span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
