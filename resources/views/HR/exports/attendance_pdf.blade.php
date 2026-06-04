{{-- resources/views/office/HR/exports/attendance_pdf.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Attendance - {{ $period }}</title>
    <style>
        @page {
            margin: 12mm 8mm 12mm 8mm;
            size: A4 landscape;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 7.5pt;
            line-height: 1.2;
            color: #1a1a1a;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .header h1 {
            margin: 0 0 3px 0;
            font-size: 13pt;
            font-weight: bold;
            color: #1a1a1a;
            text-transform: uppercase;
        }

        .header .meta {
            font-size: 8pt;
            color: #666;
        }

        .summary-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 6px 10px;
            margin: 0 0 12px 0;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .summary-item strong {
            display: block;
            font-size: 7pt;
            color: #666;
            text-transform: uppercase;
        }

        .summary-item span {
            font-size: 11pt;
            font-weight: bold;
            color: #1a1a1a;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
            page-break-inside: avoid;
        }

        thead {
            background: #2c3e50;
            color: #fff;
        }

        th {
            padding: 4px 2px;
            text-align: center;
            font-weight: bold;
            font-size: 7pt;
            border: 0.5px solid #2c3e50;
            white-space: nowrap;
            vertical-align: middle;
        }

        th.date-col {
            width: 16px;
            padding: 3px 1px;
            font-weight: normal;
        }

        th.info-col {
            text-align: left;
            padding: 4px 6px;
            min-width: 80px;
        }

        th.info-col.nama {
            width: 18%;
        }

        th.info-col.divisi {
            width: 14%;
        }

        th.info-col.jabatan {
            width: 14%;
        }

        th.summary-col {
            background: #1a3a5c;
            width: 45px;
        }

        td {
            padding: 3px 2px;
            text-align: center;
            border: 0.5px solid #e0e0e0;
            font-size: 7.5pt;
            vertical-align: middle;
        }

        td.info-cell {
            text-align: left;
            padding: 4px 6px;
            font-size: 7.5pt;
        }

        td.nama {
            font-weight: 600;
        }

        td.divisi,
        td.jabatan {
            font-size: 7pt;
            color: #555;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Status - MINIMALIS */
        .hadir {
            background: #fff;
        }

        .late {
            background: #fff5f5;
            color: #c00;
            font-weight: 600;
        }

        .cuti {
            background: #fff9e6;
            color: #996600;
        }

        .weekend {
            background: #fff;
            color: #bbb;
            font-style: italic;
        }

        .absent {
            background: #fff;
            color: #eee;
        }

        .legend {
            margin: 10px 0 5px 0;
            padding: 5px 8px;
            background: #f8f9fa;
            border-left: 3px solid #2c3e50;
            font-size: 7pt;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .legend-box {
            width: 14px;
            height: 14px;
            border: 0.5px solid #999;
            display: inline-block;
        }

        .legend-box.late {
            background: #fff5f5;
            border-color: #c00;
        }

        .legend-box.cuti {
            background: #fff9e6;
            border-color: #b38f00;
        }

        .legend-box.weekend {
            color: #bbb;
            font-style: italic;
        }

        .signature {
            margin-top: 25px;
            page-break-inside: avoid;
            display: flex;
            justify-content: flex-end;
        }

        .signature-box {
            text-align: center;
            width: 200px;
        }

        .signature-line {
            border-top: 0.5px solid #333;
            margin: 25px 0 3px 0;
            padding-top: 2px;
        }

        .signature-name {
            font-weight: bold;
            font-size: 9pt;
        }

        .signature-title {
            font-size: 8pt;
            color: #555;
        }

        .page-number {
            position: fixed;
            bottom: 4mm;
            right: 8mm;
            font-size: 7pt;
            color: #999;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .muted {
            color: #999;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>Laporan Kehadiran Karyawan</h1>
        <div class="meta">
            Periode: <strong>{{ $period }}</strong> &nbsp;|&nbsp;
            Tipe: <strong>{{ strtoupper($periode_type) }}</strong> &nbsp;|&nbsp;
            Dicetak: {{ $generated_at }}
        </div>
    </div>

    @if (isset($analytics['summary']))
        <div class="summary-box">
            <div class="summary-item">
                <strong>Attendance</strong><span>{{ $analytics['summary']['attendance_rate'] }}%</span></div>
            <div class="summary-item">
                <strong>Punctuality</strong><span>{{ $analytics['summary']['punctuality_rate'] }}%</span></div>
            <div class="summary-item"><strong>Total Hadir</strong><span>{{ $analytics['summary']['hadir'] }}</span></div>
            <div class="summary-item"><strong>Total Telat</strong><span>{{ $analytics['summary']['telat'] }}</span></div>
            <div class="summary-item"><strong>Cuti</strong><span>{{ $analytics['summary']['cuti_sakit'] }}</span></div>
            <div class="summary-item"><strong>Avg
                    Telat</strong><span>{{ $analytics['summary']['avg_late_minutes'] }}'</span></div>
        </div>
    @endif

    <div class="legend">
        <div class="legend-item"><span class="legend-box"></span> Hadir</div>
        <div class="legend-item"><span class="legend-box late"></span> Telat</div>
        <div class="legend-item"><span class="legend-box cuti"></span> Cuti/Holiday</div>
        <div class="legend-item"><span class="legend-box weekend">•</span> Weekend</div>
        <div class="legend-item"><span class="legend-box absent">–</span> Tidak Absen</div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="info-col nama">Nama</th>
                <th class="info-col divisi">Divisi</th>
                <th class="info-col jabatan">Jabatan</th>
                @foreach ($matrix['headers'] as $header)
                    @if (!in_array($header, ['Nama', 'Divisi', 'Jabatan', 'Total Hadir', 'Total Telat', 'Total Cuti', 'Overall Avg Late']))
                        <th class="date-col">{{ $header }}</th>
                    @endif
                @endforeach
                <th class="summary-col">Hadir</th>
                <th class="summary-col">Telat</th>
                <th class="summary-col">Cuti</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($matrix['rows'] as $row)
                <tr>
                    @foreach ($row as $i => $cell)
                        @php
                            if ($i === 0) {
                                $class = 'info-cell nama';
                                $content = $cell;
                            } elseif ($i === 1) {
                                $class = 'info-cell divisi';
                                $content = $cell;
                            } elseif ($i === 2) {
                                $class = 'info-cell jabatan';
                                $content = $cell;
                            } else {
                                if (is_string($cell)) {
                                    switch ($cell) {
                                        case 'H':
                                            $class = 'hadir';
                                            $content = '✓';
                                            break;
                                        case 'L':
                                            $class = 'late';
                                            $content = 'L';
                                            break;
                                        case 'Y':
                                            $class = 'cuti';
                                            $content = 'C';
                                            break;
                                        case 'X':
                                            $class = 'weekend';
                                            $content = '•';
                                            break;
                                        case '-':
                                            $class = 'absent';
                                            $content = '';
                                            break;
                                        default:
                                            $class = 'hadir';
                                            $content = $cell;
                                    }
                                } else {
                                    $class = 'hadir bold';
                                    $content = $cell;
                                }
                            }
                        @endphp
                        <td class="{{ $class }}">{{ $content }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-name">[Nama Penanggung Jawab]</div>
            <div class="signature-title">HR Manager</div>
            <div style="font-size:7pt;color:#888;">{{ now()->format('d F Y') }}</div>
        </div>
    </div>

    <div class="page-number">
        <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_script('
                $font = $fontMetrics->get_font("DejaVu Sans", "normal");
                $size = 7;
                $pageText = $PAGE_NUM . " / " . $PAGE_COUNT;
                $y = 18;
                $x = $pdf->get_width() - 40;
                $pdf->text($x, $y, $pageText, $font, $size);
            ');
        }
    </script>
    </div>

</body>

</html>
