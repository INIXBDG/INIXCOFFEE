<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 7px;
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
            padding: 2px 3px;
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

        .neg {
            color: #c00;
        }
    </style>
</head>

<body>
    @php
        $rp = fn($n) => number_format((int) $n, 0, ',', '.');
        $bln = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    @endphp

    <h3>LAPORAN PERHITUNGAN PPH 21 TAHUNAN</h3>
    <div class="sub">Tahun Pajak: {{ $tahun }}</div>
    <div class="sub">{{ $thr_label }}</div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Gaji Pokok</th>
                <th>Total Gaji</th>
                @foreach ($bln as $b)
                    <th>PPh {{ $b }}</th>
                @endforeach
                <th>THR</th>
                <th>Total Setahun</th>
                <th>Biaya Jabatan</th>
                <th>Neto</th>
                <th>PTKP</th>
                <th>PKP</th>
                <th>Pajak Setahun</th>
                <th>Sudah Dipotong</th>
                <th>Terhutang</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $r)
                <tr>
                    <td class="c">{{ $r['no'] }}</td>
                    <td>{{ $r['nama'] }}<br><span style="color:#666">{{ $r['nip'] }} - {{ $r['divisi'] }}</span>
                    </td>
                    <td class="r">{{ $rp($r['gaji_pokok']) }}</td>
                    <td class="r">{{ $rp($r['total_gaji']) }}</td>
                    @foreach ($r['pph_bulan'] as $v)
                        <td class="r">{{ $rp($v) }}</td>
                    @endforeach
                    <td class="r">{{ $rp($r['thr']) }}</td>
                    <td class="r">{{ $rp($r['bruto']) }}</td>
                    <td class="r">{{ $rp($r['biaya_jabatan']) }}</td>
                    <td class="r">{{ $rp($r['neto']) }}</td>
                    <td class="r">{{ $rp($r['ptkp']) }}<br>{{ $r['ptkp_kode'] ?? '-' }}</td>
                    <td class="r">{{ $rp($r['pkp']) }}</td>
                    <td class="r">{{ $rp($r['pajak_setahun']) }}</td>
                    <td class="r">{{ $rp($r['sudah_dipotong']) }}</td>
                    <td class="r {{ $r['terhutang'] < 0 ? 'neg' : '' }}"><strong>{{ $rp($r['terhutang']) }}</strong>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="25" class="c">Tidak ada data sesuai filter</td>
                </tr>
            @endforelse
        </tbody>
        @if (count($rows))
            <tfoot>
                <tr>
                    <td colspan="3" class="r">TOTAL</td>
                    <td class="r">{{ $rp($total['total_gaji']) }}</td>
                    <td colspan="12"></td>
                    <td class="r">{{ $rp($total['thr']) }}</td>
                    <td class="r">{{ $rp($total['bruto']) }}</td>
                    <td class="r">{{ $rp($total['biaya_jabatan']) }}</td>
                    <td class="r">{{ $rp($total['neto']) }}</td>
                    <td></td>
                    <td class="r">{{ $rp($total['pkp']) }}</td>
                    <td class="r">{{ $rp($total['pajak_setahun']) }}</td>
                    <td class="r">{{ $rp($total['sudah_dipotong']) }}</td>
                    <td class="r">{{ $rp($total['terhutang']) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>

</html>
