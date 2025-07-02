<!DOCTYPE html>
<html>

<head>
    <title>Rekap Pengajuan Klaim</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #eee;
        }
    </style>
</head>

<body>
    <h3 style="text-align: center;">Rekap Pengajuan Klaim ({{ $jenisPK }})</h3>
    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Nama Karyawan</th>
                <th>Divisi</th>
                @if ($jenisPK === 'No Record')
                <th>Kendala</th>
                @endif
                <th>Tanggal Absen</th>
                <th>Kronologi</th>
                <th>Alasan Ditolak</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach ($rows as $row)
            <tr>
                <td>{{ $no++ }}</td>
                <td>{{ $row->karyawan->nama_lengkap ?? '-' }}</td>
                <td>{{ $row->karyawan->divisi ?? '-' }}</td>
                @if ($jenisPK === 'No Record')
                <td>{{ $row->kendala ?? '-' }}</td>
                @endif
                <td>{{ $row->absensiKaryawan->tanggal ? \Carbon\Carbon::parse($row->absensiKaryawan->tanggal)->format('d M Y') : '-' }}</td>
                <td>{{ $row->kronologi ?? '-' }}</td>
                <td>{{ $row->alasan_approval ?? '-' }}</td>
                <td>
                    @if ($row->approval === 0)
                    Belum Disetujui
                    @elseif ($row->approval === 1)
                    Telah Disetujui
                    @elseif ($row->approval === 2)
                    Ditolak
                    @else
                    Tidak diketahui
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>