<table border="1" cellspacing="0" cellpadding="4">
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Karyawan</th>
            <th>Divisi</th>
            <th>Tipe Cuti</th>
            <th>Tanggal Cuti</th>
            <th>Durasi Hari</th>
            <th>Alasan Cuti</th>
            <th>Alasan Pembatalan Cuti</th>
            <th>Kontak</th>
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
            <td>{{ $row->tipe ?? '-' }}</td>
            <td>
                {{ $row->tanggal_awal ? \Carbon\Carbon::parse($row->tanggal_awal)->format('d M Y') : '-' }} - {{ $row->tanggal_akhir ? \Carbon\Carbon::parse($row->tanggal_akhir)->format('d M Y') : '-' }}
            </td>
            <td>{{ $row->durasi ?? '-' }} Hari</td>
            <td>{{ $row->alasan ?? '-' }}</td>
            <td>{{ $row->kronologi ?? '-' }}</td>
            <td>{{ $row->kontak ?? '-' }}</td>
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