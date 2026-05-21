<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Payroll {{ $period }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
        }

        h2 {
            text-align: center;
            margin-bottom: 5px;
        }

        .meta {
            text-align: center;
            color: #666;
            margin-bottom: 15px;
        }

        .summary {
            background: #f9f9f9;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }

        th {
            background: #f5f5f5;
        }

        .text-right {
            text-align: right;
        }

        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
        }

        .done {
            background: #d1e7dd;
            color: #0f5132;
        }

        .pending {
            background: #fff3cd;
            color: #664d03;
        }
    </style>
</head>

<body>
    <h2>Laporan Payroll & Tunjangan</h2>
    <div class="meta">Periode: {{ $period }} | Generated: {{ $generated_at }}</div>

    <div class="summary">
        <strong>Ringkasan:</strong>
        Total: {{ $summary['total_karyawan'] }} |
        Sudah: {{ $summary['sudah_dihitung'] }} |
        Belum: {{ $summary['belum_dihitung'] }} |
        Rata-rata: {{ number_format($summary['avg_gaji_bersih'], 0, ',', '.') }} IDR |
        Median: {{ number_format($summary['median_gaji_bersih'], 0, ',', '.') }} IDR |
        Total Bersih: {{ number_format($summary['total_gaji_bersih'], 0, ',', '.') }} IDR
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Kode</th>
                <th>Divisi</th>
                <th>Jabatan</th>
                <th class="text-right">Pokok</th>
                <th class="text-right">Tunjangan</th>
                <th class="text-right">Potongan</th>
                <th class="text-right">Bersih</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $row['nama'] }}</td>
                    <td>{{ $row['kode'] }}</td>
                    <td>{{ $row['divisi'] }}</td>
                    <td>{{ $row['jabatan'] }}</td>
                    <td class="text-right">{{ number_format($row['gaji_pokok'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['total_tunjangan'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['total_potongan'], 0, ',', '.') }}</td>
                    <td class="text-right"><strong>{{ number_format($row['gaji_bersih'], 0, ',', '.') }}</strong></td>
                    <td><span
                            class="badge {{ $row['status'] == 'Sudah Dihitung' ? 'done' : 'pending' }}">{{ $row['status'] }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
