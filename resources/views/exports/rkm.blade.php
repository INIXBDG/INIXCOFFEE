<table>
    <thead>
        <tr>
            <th>Materi</th>
            <th>Ruang</th>
            <th>Metode</th>
            <th>Event</th>
            <th>Instruktur</th>
            <th>Sales</th>
            <th>Perusahaan</th>
            <th>Total Pax</th>
            <th>Tanggal Awal</th>
            <th>Tanggal Akhir</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
            <tr>
                <td>{{ $row->materi->nama_materi }}</td>
                <td>{{ $row->ruang }}</td>
                <td>{{ $row->metode_kelas }}</td>
                <td>{{ $row->event }}</td>
                <td>
                    @foreach($row->instruktur ?? [] as $i)
                        {{ $i->nama_lengkap }}<br>
                    @endforeach
                </td>
                <td>
                    @foreach($row->sales ?? [] as $s)
                        {{ $s->nama_lengkap }}<br>
                    @endforeach
                </td>
                <td>
                    @foreach($row->perusahaan ?? [] as $p)
                        {{ $p->nama_perusahaan }}<br>
                    @endforeach
                </td>
                <td>{{ $row->total_pax }}</td>
                <td>{{ $row->tanggal_awal }}</td>
                <td>{{ $row->tanggal_akhir }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
