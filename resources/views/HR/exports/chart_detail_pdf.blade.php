<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Detail Data Karyawan</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f3f4f6; }
        .header { margin-bottom: 20px; }
        .meta { color: #666; font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Detail Data Karyawan: {{ strtoupper($meta['dataset']) }}</h2>
        <p class="meta">Filter: {{ ucfirst($meta['type']) }} - {{ $meta['label'] }} | Generated: {{ $generated_at }}</p>
    </div>
    <table>
        <thead>
            <tr>
                <th>Nama Lengkap</th>
                <th>NIP</th>
                <th>Jabatan</th>
                <th>Divisi</th>
                <th>Tanggal</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                <td>{{ $row['nama'] }}</td>
                <td>{{ $row['nip'] }}</td>
                <td>{{ $row['jabatan'] }}</td>
                <td>{{ $row['divisi'] }}</td>
                <td>{{ $row['tanggal'] }}</td>
                <td>{{ $row['status'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>