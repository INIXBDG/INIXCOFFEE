{{-- resources/views/office/stockopname/pdf-log.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Log Stock Opname</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }

        th {
            background: #f8f9fa;
            font-weight: bold;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h3 {
            margin: 5px 0;
        }

        .meta {
            font-size: 9px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="header">
        <h3>LOG STOCK OPNAME</h3>
        <div class="meta">Dicetak: {{ now()->translatedFormat('l, d F Y H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="18%">Tanggal</th>
                <th width="20%">Barang</th>
                <th width="10%">Stock Awal</th>
                <th width="10%">Stock Akhir</th>
                <th width="8%">Selisih</th>
                <th width="20%">Notes</th>
                <th width="15%">PIC</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dataLog as $index => $log)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $log->updated_at?->translatedFormat('d M Y H:i') ?? '-' }}</td>
                    <td>{{ $log->barang?->nama_barang ?? '-' }}</td>
                    <td align="right">{{ number_format($log->stock_sebelumnya ?? 0) }}</td>
                    <td align="right">{{ number_format($log->stock_hari_ini ?? 0) }}</td>
                    <td align="right">{{ number_format($log->selisih ?? 0) }}</td>
                    <td>{{ $log->notes ?? '-' }}</td>
                    <td>{{ $log->karyawan?->nama_lengkap ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
