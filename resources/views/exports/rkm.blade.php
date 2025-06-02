<table border="1" cellspacing="0" cellpadding="4">
    <thead>
        <tr>
            <th>Materi</th>
            <th>Ruang</th>
            <th>Metode Kelas</th>
            <th>Event</th>
            <th>Instruktur</th>
            <th>Sales</th>
            <th>Perusahaan</th>
            <th>Pax</th>
            <th>Tanggal Mulai</th>
            <th>Tanggal Selesai</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $row)
            @if (isset($row->is_separator) && $row->is_separator)
                <tr>
                    <td colspan="2"></td>
                    <td colspan="8" style="border-top: 2px solid #000;"></td>
                </tr>
            @else
                <tr>
                    <td>{{ $row->materi->nama_materi ?? '' }}</td>
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
            @endif
        @endforeach
    </tbody>
</table>
