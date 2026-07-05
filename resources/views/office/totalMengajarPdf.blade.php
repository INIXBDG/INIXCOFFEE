<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Data Total Mengajar Instruktur</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #333;
            line-height: 1.3;
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        .header {
            background-color: #667eea;
            color: white;
            padding: 12px;
            text-align: center;
            margin-bottom: 12px;
        }

        .header h1 {
            font-size: 14px;
            font-weight: bold;
            margin: 0;
        }

        .content {
            padding: 0 8px;
        }

        .info-section {
            background-color: #f8f9fa;
            border-left: 3px solid #667eea;
            padding: 8px;
            margin-bottom: 12px;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #333;
            margin-bottom: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        table thead {
            background-color: #667eea;
        }

        table th {
            color: white;
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
            font-size: 8px;
            border: 1px solid #5568d3;
        }

        table td {
            padding: 5px 4px;
            border: 1px solid #dee2e6;
            font-size: 8px;
            vertical-align: top;
        }

        table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .instructor-section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }

        .instructor-title {
            background-color: #e8eaf6;
            padding: 5px 8px;
            font-weight: bold;
            font-size: 10px;
            border-left: 3px solid #667eea;
            margin-bottom: 5px;
        }

        .footer {
            margin-top: 12px;
            padding-top: 8px;
            border-top: 1px solid #dee2e6;
            font-size: 8px;
            color: #666;
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Data Total Mengajar Instruktur</h1>
        </div>
        <div class="content">
            <div class="info-section">
                <div class="section-title">Periode: {{ $rentangWaktu ?? 'Semua Data' }}</div>
                <div style="font-size: 8px;">Total Instruktur:
                    {{ is_array($dataMengajar ?? []) ? count($dataMengajar) : 0 }}</div>
            </div>

            @php $noGlobal = 1; @endphp
            @foreach ($dataMengajar ?? [] as $item)
                <div class="instructor-section">
                    <div class="instructor-title">
                        {{ $noGlobal }}. {{ $item['namaKaryawan'] ?? '-' }} ({{ $item['kodeKaryawan'] ?? '-' }})
                        - Total: {{ $item['totalMengajar'] ?? 0 }} sesi
                        - Avg Feedback: {{ $item['overall_feedback'] ?? '-' }}
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th style="width: 5%;">No</th>
                                <th style="width: 25%;">Bulan/Periode</th>
                                <th style="width: 15%;">Instruktur</th>
                                <th style="width: 10%;">Feedback</th>
                                <th style="width: 15%;">Metode</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($item['periods'] ?? [] as $index => $period)
                                <tr>
                                    <td style="text-align: center;">{{ $index + 1 }}</td>
                                    <td>{{ $period['periode'] ?? '-' }}</td>
                                    <td>{{ $item['namaKaryawan'] ?? '-' }}</td>
                                    <td style="text-align: center;">{{ $period['feedback_avg'] ?? '-' }}</td>
                                    <td>{{ $period['metode'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @php $noGlobal++; @endphp
            @endforeach

            @if (empty($dataMengajar ?? []) || count($dataMengajar ?? []) === 0)
                <div style="text-align: center; padding: 30px; color: #666; font-size: 10px;">
                    Tidak ada data mengajar untuk periode ini.
                </div>
            @endif

            <div class="footer">
                <div>Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB</div>
            </div>
        </div>

        <div style="page-break-before: always; margin: 12px;">
            <div class="section-title" style="margin-top: 20px; margin-bottom: 10px;">Analisa Efektivitas</div>

            @php
                // Hitung total seluruh kelas dari semua instruktur untuk dasar persentase
                $grandTotalKelas = 0;
                foreach ($dataMengajar ?? [] as $d) {
                    $grandTotalKelas += $d['totalMengajar'] ?? 0;
                }
            @endphp

            <table>
                <thead>
                    <tr>
                        <th style="width: 25%; text-align: left;">Nama</th>
                        <th style="width: 15%; text-align: center;">Jumlah Kelas</th>
                        <th style="width: 20%; text-align: right;">Percentage of Contribution</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataMengajar ?? [] as $item)
                        @php
                            $totalKelas = $item['totalMengajar'] ?? 0;
                            $percentage = $grandTotalKelas > 0 ? ($totalKelas / $grandTotalKelas) * 100 : 0;
                            $percentageFormatted = number_format($percentage, 2, ',', '') . '%';
                        @endphp

                        <tr>
                            <td style="text-transform: capitalize;">{{ $item['namaKaryawan'] ?? '-' }}</td>
                            <td style="text-align: center;">{{ $totalKelas }}</td>
                            <td style="text-align: right;">{{ $percentageFormatted }}</td>
                        </tr>
                    @endforeach

                    <tr style="font-weight: bold; background-color: #f1f1f1;">
                        <td>TOTAL</td>
                        <td style="text-align: center;">{{ $grandTotalKelas }}</td>
                        <td style="text-align: right;">100,00%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
