<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            color: #111;
        }

        h3 {
            margin: 0;
            text-align: center;
            font-size: 13px;
        }

        .sub {
            text-align: center;
            margin: 2px 0;
            font-size: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 3px 4px;
        }

        th {
            background: #1F4E79;
            color: #fff;
            text-align: center;
        }

        .r {
            text-align: right;
        }

        .c {
            text-align: center;
        }

        tfoot td {
            font-weight: bold;
            background: #e7e6e6;
        }
    </style>
</head>

<body>
    @php $rp = fn($n) => number_format((int) $n, 0, ',', '.'); @endphp

    <h3>LAPORAN PPH 21 KARYAWAN</h3>
    <div class="sub">Periode: {{ $periode_label }}</div>
    <div class="sub">Filter: {{ $filter_label }} | Dicetak: {{ $generated_at }}</div>
    <div class="sub">
        @foreach ($status_laporan as $nama => $sudah)
            {{ $nama }}: {{ $sudah ? 'Sudah' : 'Belum' }}@if (!$loop->last)
                |
            @endif
        @endforeach
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>NIP</th>
                <th>Nama</th>
                <th>Divisi</th>
                <th>Gaji Dasar</th>
                <th>PTKP</th>
                <th>TER</th>
                <th>Menikah</th>
                <th>Anak</th>
                <th>PPh 21/Bulan</th>
                <th>Sumber Nilai</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $r)
                <tr>
                    <td class="c">{{ $r['no'] }}</td>
                    <td>{{ $r['nip'] }}</td>
                    <td>{{ $r['nama'] }}<br><span style="color:#666">{{ $r['jabatan'] }}</span></td>
                    <td>{{ $r['divisi'] }}</td>
                    <td class="r">{{ $rp($r['gaji_dasar']) }}</td>
                    <td class="c">{{ $r['ptkp'] }}</td>
                    <td class="c">{{ $r['kategori'] }}</td>
                    <td class="c">{{ $r['menikah'] }}</td>
                    <td class="c">{{ $r['anak'] }}</td>
                    <td class="r"><strong>{{ $rp($r['pph']) }}</strong></td>
                    <td>{{ $r['sumber'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="c">Tidak ada data sesuai filter</td>
                </tr>
            @endforelse
        </tbody>
        @if (count($rows))
            <tfoot>
                <tr>
                    <td colspan="9" class="r">TOTAL</td>
                    <td class="r">{{ $rp($total_pph) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>

</html>
