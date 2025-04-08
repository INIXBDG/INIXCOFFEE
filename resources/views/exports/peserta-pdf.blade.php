<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Include Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Data Peserta</title>
    <style>
        table {
            width: auto;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
            text-align: left;
        }
        th, td {
            padding: 8px;
        }
    </style>
</head>
<body>
    <h3>Data Peserta</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Jenis Kelamin</th>
                <th>Nomor Handphone</th>
                <th>Alamat</th>
                <th>Perusahaan</th>
                <th>Tanggal Lahir</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataPeserta as $index => $peserta)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $peserta->nama }}</td>
                <td>{{ $peserta->email }}</td>
                <td>{{ $peserta->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                <td>{{ $peserta->no_hp }}</td>
                <td>{{ $peserta->alamat }}</td>
                <td>{{ $peserta->perusahaan->nama_perusahaan }}</td>
                <td>{{ \Carbon\Carbon::parse($peserta->tanggal_lahir)->format('d F Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
