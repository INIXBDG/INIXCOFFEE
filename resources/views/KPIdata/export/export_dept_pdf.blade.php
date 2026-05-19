<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1f2937;
            line-height: 1.4;
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
            margin-top: 15px;
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

        .text-right {
            text-align: right;
        }

        .bg-light {
            background-color: #f3f4f6;
        }

        .text-green {
            color: #10b981;
            font-weight: bold;
        }

        .text-yellow {
            color: #f59e0b;
            font-weight: bold;
        }

        .text-blue {
            color: #2563eb;
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

        .page-break {
            page-break-before: always;
        }

        .footer {
            text-align: center;
            font-size: 9px;
            color: #6b7280;
            margin-top: 20px;
            border-top: 1px solid #e5e7eb;
            padding-top: 5px;
        }

        .bar-wrap {
            background: #e5e7eb;
            height: 8px;
            border-radius: 2px;
            overflow: hidden;
        }

        .bar-fill {
            background: #2563eb;
            height: 100%;
            border-radius: 2px;
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
                <th style="width: 15%;">Grade</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ranking_karyawan as $idx => $emp)
                <tr class="{{ $idx % 2 == 0 ? 'bg-light' : '' }}">
                    <td>{{ $idx + 1 }}</td>
                    <td class="text-left">{{ $emp['nama'] }}</td>
                    <td class="text-left">{{ $emp['jabatan'] }}</td>
                    <td
                        class="{{ $emp['avg_kpi'] >= 80 ? 'text-green' : ($emp['avg_kpi'] >= 50 ? 'text-yellow' : 'text-blue') }}">
                        {{ $emp['avg_kpi'] }}%
                    </td>
                    <td>{{ $emp['target_count'] }}</td>
                    <td>{{ $emp['grade'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="page-break"></div>

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
                <td class="text-green">Low Risk</td>
            </tr>
            <tr class="bg-light">
                <td class="text-left">Sedang (50-79%)</td>
                <td>{{ $risk_distribution['mid']['count'] }}</td>
                <td>{{ $risk_distribution['mid']['pct'] }}%</td>
                <td class="text-yellow">Medium Risk</td>
            </tr>
            <tr>
                <td class="text-left">Perlu Perhatian (&lt;50%)</td>
                <td>{{ $risk_distribution['low']['count'] }}</td>
                <td>{{ $risk_distribution['low']['pct'] }}%</td>
                <td class="text-blue">High Risk</td>
            </tr>
        </tbody>
    </table>

    <h2>TREND PERFORMA BULANAN</h2>
    <table>
        <thead>
            <tr>
                <th>Bulan</th>
                <th>Rata-Rata Dept (%)</th>
                <th>Visual</th>
            </tr>
        </thead>
        <tbody>
            @for ($i = 1; $i <= 12; $i++)
                @php
                    $val = $trend_bulanan[$i] ?? 0;
                    $barWidth = min($val, 100);
                @endphp
                <tr class="{{ $i % 2 == 0 ? 'bg-light' : '' }}">
                    <td>{{ $nama_bulan[$i] }}</td>
                    <td>{{ $val }}%</td>
                    <td>
                        <div class="bar-wrap">
                            <div class="bar-fill" style="width: {{ $barWidth }}%;"></div>
                        </div>
                    </td>
                </tr>
            @endfor
        </tbody>
    </table>

    <div class="page-break"></div>

    <h2>STATUS TARGET GLOBAL</h2>
    <table>
        <tbody>
            <tr class="bg-light">
                <td class="text-left" style="width: 30%;">Target Selesai</td>
                <td style="width: 20%;" class="text-green">{{ $status_target_global['selesai'] }}</td>
                <td class="text-left" style="width: 30%;">Dalam Progress</td>
                <td style="width: 20%;" class="text-yellow">{{ $status_target_global['progress'] }}</td>
            </tr>
            <tr>
                <td class="text-left">Target Gagal</td>
                <td class="text-blue">{{ $status_target_global['gagal'] }}</td>
                <td class="text-left">Belum Dimulai</td>
                <td>{{ $status_target_global['belum_mulai'] }}</td>
            </tr>
        </tbody>
    </table>

    <h2>DISTRIBUSI GRADE KARYAWAN</h2>
    <table>
        <thead>
            <tr>
                <th>Grade</th>
                <th>Range Nilai</th>
                <th>Jumlah Karyawan</th>
            </tr>
        </thead>
        <tbody>
            @php
                $gradeCounts = ['Sangat Baik' => 0, 'Baik' => 0, 'Cukup' => 0, 'Kurang' => 0, 'Sangat Kurang' => 0];
                foreach ($ranking_karyawan as $emp) {
                    if (isset($gradeCounts[$emp['grade']])) {
                        $gradeCounts[$emp['grade']]++;
                    }
                }
                $grades = [
                    ['Sangat Baik', '≥ 100%', 'text-green'],
                    ['Baik', '80% - 99%', 'text-green'],
                    ['Cukup', '70% - 79%', 'text-yellow'],
                    ['Kurang', '60% - 69%', 'text-blue'],
                    ['Sangat Kurang', '&lt; 60%', 'text-blue'],
                ];
            @endphp
            @foreach ($grades as $g)
                <tr class="{{ $loop->index % 2 == 0 ? 'bg-light' : '' }}">
                    <td class="text-left {{ $g[2] }}">{{ $g[0] }}</td>
                    <td>{{ $g[1] }}</td>
                    <td>{{ $gradeCounts[$g[0]] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Laporan KPI Divisi {{ $divisi }} - Tahun {{ $tahun }} | Dicetak pada {{ date('d M Y H:i') }}
    </div>

</body>

</html>
