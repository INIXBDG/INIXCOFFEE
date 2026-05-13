<!DOCTYPE html>
<html>

<head>
    <title>Laporan Gaji</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }
    </style>
</head>

<body>

    <h2>Laporan Gaji</h2>
    <p>Periode: {{ $bulan }}/{{ $tahun }}</p>

    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Kode</th>
                <th>Divisi</th>
                <th>Jabatan</th>
                <th>Gaji</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $item)
                <tr>
                    <td>{{ $item->karyawan->nama_lengkap ?? '-' }}</td>
                    <td>{{ $item->karyawan->kode_karyawan ?? '-' }}</td>
                    <td>{{ $item->karyawan->divisi ?? '-' }}</td>
                    <td>{{ $item->karyawan->jabatan ?? '-' }}</td>
                    <td>Rp {{ number_format($item->gaji, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>
        Total Gaji:
        Rp {{ number_format($totalGaji, 0, ',', '.') }}
    </h3>

</body>

</html>
