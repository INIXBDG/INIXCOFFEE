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
    <h3>Data Registrasi</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Peserta</th>
                <th>Perusahaan</th>
                <th>Materi Pelatihan</th>
                <th>Periode Pelatihan</th>
                <th>Instruktur</th>
                <th>Sales</th>
                <th>Souvenir</th>
            </tr>
        </thead>
        <tbody>
            @foreach($registrasi as $index => $data)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $data->peserta->nama }}</td>
                <td>{{ $data->peserta->perusahaan->nama_perusahaan }}</td>
                <td>{{ $data->materi->nama_materi }}</td>
                <td>{{ $data->rkm->tanggal_awal . 's/d' . $data->rkm->tanggal_akhir }}</td>
                <td>{{ $data->karyawan->kode_karyawan }}</td>
                <td>{{ $data->sales->kode_karyawan }}</td>
                <td>{{ optional($data->souvenirpeserta->first())->souvenir->nama_souvenir ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
