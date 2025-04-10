<table>
    <thead>
        <tr>
            <th>Nama Karyawan</th>
            <th>Jenis Tunjangan</th>
            <th>Nominal</th>
        </tr>
    </thead>
    <tbody>
        @php
            $grandTotalTunjangan = 0;
            $grandTotalPotongan = 0;
            $grandTotalPremiHadir = 0;
            $grandTotalTransaksi = 0;
        @endphp

        @foreach ($post as $index => $items)
            @php $totalPerKaryawan = 0; @endphp
            @foreach ($items as $item)
                <tr>
                    <td>{{ $index }}</td>
                    <td>{{ $item->jenistunjangan->nama_tunjangan }}</td>
                    <td>{{ $item->total }}</td>
                </tr>

                @php
                    $totalPerKaryawan += floatval($item->total);
                    $grandTotalTransaksi += floatval($item->total);
                    if ($item->jenistunjangan->tipe == 'Potongan') {
                        $grandTotalPotongan += floatval($item->total);
                    } elseif ($item->jenistunjangan->nama_tunjangan == 'Absensi') {
                        $grandTotalPremiHadir += floatval($item->total);
                    } else {
                        $grandTotalTunjangan += floatval($item->total);
                    }
                @endphp
            @endforeach
        @endforeach

        <tr><td colspan="2">Total Tunjangan</td><td>{{ $grandTotalTunjangan }}</td></tr>
        <tr><td colspan="2">Total Potongan</td><td>{{ $grandTotalPotongan }}</td></tr>
        <tr><td colspan="2">Total Premi Hadir</td><td>{{ $grandTotalPremiHadir }}</td></tr>
        <tr><td colspan="2">Total Transaksi</td><td>{{ $grandTotalTransaksi }}</td></tr>
    </tbody>
</table>
