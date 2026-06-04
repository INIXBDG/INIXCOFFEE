<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Executive Report - {{ $filters['tahun'] ?? date('Y') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10pt;
            color: #333;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 18pt;
        }

        .header p {
            margin: 5px 0 0;
            font-size: 9pt;
            color: #666;
        }

        .filters {
            margin-bottom: 20px;
            font-size: 9pt;
        }

        .metrics {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .metric {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 10px;
            border: 1px solid #ddd;
        }

        .metric-value {
            font-size: 16pt;
            font-weight: bold;
        }

        .metric-label {
            font-size: 8pt;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }

        th {
            background: #f5f5f5;
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }

        td {
            padding: 6px 8px;
            border: 1px solid #ddd;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            color: #999;
            border-top: 1px solid #ddd;
            padding: 10px 0;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Executive Analytics Report</h1>
        <p>{{ config('app.name') }} • Generated: {{ $generated_at->format('d M Y H:i') }}</p>
        <p>By: {{ $user->nama_lengkap }} ({{ $user->jabatan }})</p>
    </div>

    <div class="filters">
        <strong>Filters:</strong>
        Tahun: {{ $filters['tahun'] ?? 'All' }} |
        Divisi: {{ $filters['divisi'] ?? 'All' }} |
        Jabatan: {{ $filters['jabatan'] ?? 'All' }}
    </div>

    <div class="metrics">
        <div class="metric">
            <div class="metric-value">-</div>
            <div class="metric-label">Avg Progress</div>
        </div>
        <div class="metric">
            <div class="metric-value">-</div>
            <div class="metric-label">Total Targets</div>
        </div>
        <div class="metric">
            <div class="metric-value">-</div>
            <div class="metric-label">High Potential</div>
        </div>
        <div class="metric">
            <div class="metric-value">-</div>
            <div class="metric-label">Completion Rate</div>
        </div>
    </div>

    <h3 style="margin: 20px 0 10px;">Top Performers</h3>
    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Jabatan</th>
                <th>Divisi</th>
                <th>Performance</th>
                <th>Growth</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="5" style="text-align: center; color: #999;">Data akan diisi oleh sistem</td>
            </tr>
        </tbody>
    </table>

    <h3 style="margin: 20px 0 10px;">Recommendations</h3>
    <ul>
        <li>Review quarterly untuk karyawan di kategori "Needs Support"</li>
        <li>Program pengembangan untuk "Emerging" talent</li>
        <li>Retention strategy untuk "High Potential" employees</li>
    </ul>

    <div class="footer">
        Page
        <script type="text/php">if (isset($pdf)) { echo $PAGE_NUM; }</script> of
        <script type="text/php">if (isset($pdf)) { echo $PAGE_COUNT; }</script>
        • Confidential - Internal Use Only
    </div>
</body>

</html>
