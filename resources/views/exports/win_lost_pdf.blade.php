<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #2c3e50; }
        .header p { margin: 5px 0; color: #7f8c8d; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { border: 1px solid #ccc; padding: 10px; text-align: center; }
        .table th { background-color: #f2f2f2; }
        .footer { margin-top: 30px; text-align: center; color: #95a5a6; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Dibuat pada: {{ now() }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Sales</th>
                <th>Materi</th>
                <th>Perusahaan</th>
                <th>Pax</th>
                <th>Harga</th>
                <th>Tanggal</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->sales_key }}</td>
                <td>{{ $item->nama_materi }}</td>
                <td>{{ $item->nama_perusahaan }}</td>
                <td>{{ $item->pax }}</td>
                <td>{{ number_format($item->harga, 0, ',', '.') }}</td>
                <td>{{ \Carbon\Carbon::parse($item->tanggal_awal)->format('d F Y') }} {{ \Carbon\Carbon::parse($item->tanggal_akhir)->format('d F Y') }}</td>
                <td>{{ $item->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        © 2025 INIXCOFFEE - {{ now() }}
    </div>
</body>
</html>
