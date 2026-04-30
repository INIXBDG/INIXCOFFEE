<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1f2937;
        }

        h1 {
            font-size: 16px;
            text-align: center;
            background-color: #1f2937;
            color: white;
            padding: 10px;
            margin: 0;
        }

        h2 {
            font-size: 12px;
            background-color: #2563eb;
            color: white;
            padding: 5px;
            margin-top: 20px;
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th {
            background-color: #374151;
            color: white;
            padding: 5px;
            text-align: center;
            border: 1px solid #ccc;
        }

        td {
            padding: 5px;
            border: 1px solid #ccc;
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .bg-light {
            background-color: #f3f4f6;
        }

        .bg-green {
            color: #10b981;
            font-weight: bold;
        }

        .bg-yellow {
            color: #f59e0b;
            font-weight: bold;
        }

        .bg-red {
            color: #ef4444;
            font-weight: bold;
        }

        .summary-box {
            background-color: #eff6ff;
            padding: 10px;
            border: 1px solid #bfdbfe;
            margin-bottom: 10px;
        }

        .insight-text {
            font-style: italic;
            white-space: pre-line;
        }
    </style>
</head>

<body>

    <h1>EXECUTIVE SUMMARY KPI DIVISI {{ strtoupper($divisi) }} - TAHUN {{ $tahun }}</h1>

    <table style="margin-top: 10px;">
        <tr class="bg-light">
            <td class="text-left" style="width: 20%;"><strong>Total Karyawan</strong></td>
            <td style="width: 30%;">: {{ $total_karyawan }}</td>
            <td class="text-left" style="width: 20%;"><strong>Rata-Rata KPI Divisi</strong></td>
            <td style="width: 30%;">: {{ $rata_rata_kpi_divisi }}%</td>
        </tr>
        <tr>
            <td class="text-left"><strong>Total Target Terdata</strong></td>
            <td>: {{ $total_target_terdata }}</td>
            <td class="text-left"><strong>Target Selesai</strong></td>
            <td>: {{ $target_selesai }} ({{ $target_selesai_persen }}%)</td>
        </tr>
    </table>

    <h2>INSIGHT OTOMATIS</h2>
    <div class="summary-box">
        <p class="insight-text">{{ $insight_text }}</p>
    </div>

    <h2>RANKING KARYAWAN</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">Rank</th>
                <th style="width: 25%;" class="text-left">Nama</th>
                <th style="width: 20%;" class="text-left">Jabatan</th>
                <th style="width: 10%;">Avg %</th>
                <th style="width: 10%;">Total Target</th>
                <th style="width: 15%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ranking_karyawan as $idx => $emp)
                <tr class="{{ $idx % 2 == 0 ? 'bg-light' : '' }}">
                    <td>{{ $idx + 1 }}</td>
                    <td class="text-left">{{ $emp['nama'] }}</td>
                    <td class="text-left">{{ $emp['jabatan'] }}</td>
                    <td
                        class="{{ $emp['avg_kpi'] >= 80 ? 'bg-green' : ($emp['avg_kpi'] >= 50 ? 'bg-yellow' : 'bg-red') }}">
                        {{ $emp['avg_kpi'] }}%
                    </td>
                    <td>{{ $emp['target_count'] }}</td>
                    <td
                        class="{{ $emp['avg_kpi'] >= 80 ? 'bg-green' : ($emp['avg_kpi'] >= 50 ? 'bg-yellow' : 'bg-red') }}">
                        {{ $emp['grade'] }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="page-break-before: always;"></div>

    <h2>DISTRIBUSI KINERJA & RISIKO</h2>
    <table>
        <thead>
            <tr>
                <th>Kategori</th>
                <th>Jumlah</th>
                <th>Persentase</th>
                <th>Risk Level</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-left">Top Performer (≥80%)</td>
                <td>{{ $risk_distribution['top']['count'] }}</td>
                <td>{{ $risk_distribution['top']['pct'] }}%</td>
                <td>Low Risk</td>
            </tr>
            <tr class="bg-light">
                <td class="text-left">Sedang (50-79%)</td>
                <td>{{ $risk_distribution['mid']['count'] }}</td>
                <td>{{ $risk_distribution['mid']['pct'] }}%</td>
                <td>Medium Risk</td>
            </tr>
            <tr>
                <td class="text-left">Perlu Perhatian (dibawah 50%)</td>
                <td>{{ $risk_distribution['low']['count'] }}</td>
                <td>{{ $risk_distribution['low']['pct'] }}%</td>
                <td>High Risk</td>
            </tr>
        </tbody>
    </table>

    <h2>TREND PERFORMA BULANAN</h2>
    <table>
        <thead>
            <tr>
                <th>Bulan</th>
                <th>Rata-Rata Dept (%)</th>
            </tr>
        </thead>
        <tbody>
            @for ($i = 1; $i <= 12; $i++)
                <tr class="{{ $i % 2 == 0 ? 'bg-light' : '' }}">
                    <td>{{ $nama_bulan[$i] }}</td>
                    <td>{{ $trend_bulanan[$i] }}%</td>
                </tr>
            @endfor
        </tbody>
    </table>

</body>

</html>
