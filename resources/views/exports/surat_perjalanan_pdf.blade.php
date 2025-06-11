<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Surat Perjalanan PDF</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 8px;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
        }

        th {
            background-color: #f0f0f0;
        }
    </style>
</head>

<body>
    <h2>Data Surat Perjalanan - Bulan {{ \Carbon\Carbon::create()->month($data->first()->created_at->format('m'))->format('F') }}</h2>

    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Divisi</th>
                <th>Jabatan</th>
                <th>Jenis Dinas</th>
                <th>Tipe</th>
                <th>Tujuan</th>
                <th>Alasan</th>
                <th>Tanggal Berangkat</th>
                <th>Tanggal Pulang</th>
                <th>Durasi</th>
                <th>Rate Makan</th>
                <th>Rate SPJ</th>
                <th>Rate Taksi</th>
                <th>Total</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $item)
            <tr>
                <td>{{ $item->karyawan->nama_lengkap ?? '-' }}</td>
                <td>{{ $item->karyawan->divisi ?? '-' }}</td>
                <td>{{ $item->karyawan->jabatan ?? '-' }}</td>
                <td>{{ $item->jenis_dinas ?? '-' }}</td>
                <td>{{ $item->tipe ?? '-' }}</td>
                <td>{{ $item->alasan ?? '-' }}</td>
                <td>{{ $item->tujuan ?? '-' }}</td>
                <td>{{ $item->tanggal_berangkat ? $item->tanggal_berangkat->format('Y-m-d') : '-' }}</td>
                <td>{{ $item->tanggal_pulang ? $item->tanggal_pulang->format('Y-m-d') : '-' }}</td>
                <td>{{ $item->durasi ? $item->durasi . ' Hari' : '0 Hari' }}</td>
                <td>{{ 'Rp. ' . number_format($item->ratemakan, 0, ',', '.') }}</td>
                <td>{{ 'Rp. ' . number_format($item->ratespj, 0, ',', '.') }}</td>
                <td>{{ 'Rp. ' . number_format($item->ratetaksi, 0, ',', '.') }}</td>
                <td>{{ 'Rp. ' . number_format($item->total, 0, ',', '.') }}</td>
                <td>
                    {{ $item->approval_direksi === null ? '-' : (
                        $item->approval_direksi === 0 ? 'Belum Disetujui' : (
                            $item->approval_direksi === 1 ? 'Telah Disetujui' : 'Ditolak'
                        )
                    ) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>