<!DOCTYPE html>
<html lang="id">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Rekap Feedback Instruktur</title>
    <style>
        @page {
            margin: 24px;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            color: #222;
        }

        h2 {
            margin: 0 0 4px;
            font-size: 17px;
            text-align: center;
        }

        p {
            margin: 0 0 12px;
            font-size: 10px;
            text-align: center;
        }

        h3 {
            margin: 16px 0 6px;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            page-break-inside: auto;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        thead {
            display: table-header-group;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 6px;
            font-size: 10px;
        }

        th {
            background-color: #f2f2f2;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .summary td {
            width: 33.33%;
            text-align: center;
            font-weight: bold;
        }

        .total-row {
            font-weight: bold;
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <h2>Rekapitulasi Feedback Instruktur</h2>
    <p>Periode: {{ $periodLabel }} | Tanggal Cetak: {{ $printedAt }}</p>

    <h3>Ringkasan Rata-rata Feedback</h3>
    <table class="summary">
        <tbody>
            <tr>
                <td>Instruktur: {{ number_format($report['average_feedback']['instruktur'] ?? 0, 1, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Total data feedback: {{ number_format($report['total_feedback'] ?? 0, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <h3>Rata-rata Feedback Per Instruktur</h3>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Kode Karyawan</th>
                <th>Nama Instruktur</th>
                @foreach ($months as $month)
                    <th>{{ \Illuminate\Support\Carbon::createFromDate($report['tahun'], $month, 1)->translatedFormat('F') }}</th>
                @endforeach
                <th>Grand Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $rows = collect($report['per_instruktur'] ?? [])
                    ->filter(fn ($item) =>
                        !empty($item['kode_karyawan']) &&
                        trim($item['kode_karyawan']) !== '-' &&
                        $item['total_feedback'] > 0 &&
                        $item['total_penilaian'] > 0
                    )
                    ->values();
            @endphp

            @forelse ($rows as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item['kode_karyawan'] }}</td>
                    <td>{{ $item['nama'] }}</td>
                    @foreach ($months as $month)
                        <td class="text-center">{{ isset($item['rata_rata_per_bulan'][$month]) ? number_format($item['rata_rata_per_bulan'][$month], 1, ',', '.') : '-' }}</td>
                    @endforeach
                    <td class="text-center">{{ number_format($item['rata_rata'], 1, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 4 + count($months) }}" class="text-center">Tidak ada data feedback instruktur pada periode ini.</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="3">Grand Total</td>
                @foreach ($months as $month)
                    <td class="text-center">{{ isset($report['average_feedback_per_bulan'][$month]) ? number_format($report['average_feedback_per_bulan'][$month], 1, ',', '.') : '-' }}</td>
                @endforeach
                <td class="text-center">{{ number_format($report['average_feedback']['instruktur'] ?? 0, 1, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>
