<table border="1" cellspacing="0" cellpadding="4">
    <thead>
        <tr>
            <th>No.</th>
            <th>Nama Karyawan</th>
            <th>Divisi</th>
            @if (isset($rows[0]) && $rows[0]->jenis_PK === 'No Record')
            <th>Kendala</th>
            @elseif (isset($rows[0]) && $rows[0]->jenis_PK === 'Scheme Work')
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
            @if($row->jenis_PK === 'No Record')
            <td>{{ $row->kendala ?? '-' }}</td>
            @elseif ($row->jenis_PK === 'Scheme Work')
            @endif
            <td>
                {{ $row->absensiKaryawan->tanggal 
                    ? \Carbon\Carbon::parse($row->absensiKaryawan->tanggal_absen)->format('d M Y') : '-' 
                }}
            </td>
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