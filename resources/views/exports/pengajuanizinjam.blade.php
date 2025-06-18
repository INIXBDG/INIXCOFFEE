<table border="1" cellspacing="0" cellpadding="4">
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Karyawan</th>
            <th>Divisi</th>
            <th>Tanggal</th>
            <th>Jam</th>
            <th>Durasi Jam</th>
            <th>Alasan</th>
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
            <td>{{ $row->karyawan->divisi ?? '-'}}</td>
            <td>{{ $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('d M Y') : '-' }} </td>
            <td>
                {{ $row->jam_mulai ? \Carbon\Carbon::parse($row->jam_mulai)->format('H:i') : '-' }} - {{ $row->jam_selesai ? \Carbon\Carbon::parse($row->jam_selesai)->format('H:i') : '-' }}
            </td>
            <td>{{ $row->durasi ?? '-' }} Jam</td>
            <td>{{ $row->alasan ?? '-' }}</td>
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