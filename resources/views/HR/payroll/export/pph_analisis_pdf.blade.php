<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #111;
        }

        h3 {
            margin: 0;
            text-align: center;
            font-size: 14px;
        }

        h4 {
            margin: 14px 0 4px;
            font-size: 10px;
            color: #1F4E79;
        }

        .sub {
            text-align: center;
            margin: 2px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
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
    </style>
</head>

<body>
    @php
        $rp = fn($n) => number_format((int) $n, 0, ',', '.');
        $h = $a['kesehatan'];
    @endphp

    <h3>ANALISIS PPH 21</h3>
    <div class="sub">Periode {{ str_pad($bulan, 2, '0', STR_PAD_LEFT) }}/{{ $tahun }} | Dicetak:
        {{ $generated_at }}</div>

    <h4>Ringkasan</h4>
    <table>
        <tr>
            <td>Karyawan eligible</td>
            <td class="r">{{ $h['total_eligible'] }}</td>
        </tr>
        <tr>
            <td>Sudah punya PTKP</td>
            <td class="r">{{ $h['punya_ptkp'] }} / {{ $h['total_eligible'] }}</td>
        </tr>
        <tr>
            <td>Nilai bulan ini tersimpan</td>
            <td class="r">{{ $h['sudah_diisi'] }} / {{ $h['total_eligible'] }}</td>
        </tr>
        <tr>
            <td>Total PPh 21</td>
            <td class="r">Rp {{ $rp($h['total_pph']) }}</td>
        </tr>
        <tr>
            <td>Total bulan lalu ({{ $h['bulan_lalu_label'] }})</td>
            <td class="r">Rp {{ $rp($h['total_pph_bulan_lalu']) }}</td>
        </tr>
        <tr>
            <td>Selisih</td>
            <td class="r">Rp {{ $rp($h['selisih']) }}</td>
        </tr>
    </table>

    <h4>Status Alur Kerja</h4>
    <table>
        <tr>
            <th>Langkah</th>
            <th>Status</th>
            <th>Detail</th>
        </tr>
        @foreach ($a['workflow']['steps'] as $s)
            <tr>
                <td>{{ $s['judul'] }}</td>
                <td class="c">{{ $s['selesai'] ? 'Selesai' : 'Belum' }}</td>
                <td>{{ $s['detail'] }}</td>
            </tr>
        @endforeach
    </table>

    <h4>Kategori TER</h4>
    <table>
        <tr>
            <th>Kategori</th>
            <th>Jumlah</th>
            <th>Total PPh 21</th>
        </tr>
        @foreach ($a['distribusi']['kategori'] as $k => $v)
            <tr>
                <td>{{ $k }}</td>
                <td class="c">{{ $v['jumlah'] }}</td>
                <td class="r">{{ $rp($v['total']) }}</td>
            </tr>
        @endforeach
    </table>

    <h4>Total PPh 21 per Divisi</h4>
    <table>
        <tr>
            <th>Divisi</th>
            <th>Jumlah</th>
            <th>Total</th>
            <th>Rata-rata</th>
        </tr>
        @foreach ($a['distribusi']['divisi'] as $x)
            <tr>
                <td>{{ $x['divisi'] }}</td>
                <td class="c">{{ $x['jumlah'] }}</td>
                <td class="r">{{ $rp($x['total']) }}</td>
                <td class="r">{{ $rp($x['rata_rata']) }}</td>
            </tr>
        @endforeach
    </table>

    <h4>10 PPh 21 Tertinggi</h4>
    <table>
        <tr>
            <th>Nama</th>
            <th>Divisi</th>
            <th>PTKP</th>
            <th>Nilai</th>
        </tr>
        @foreach ($a['distribusi']['top'] as $x)
            <tr>
                <td>{{ $x['nama'] }} ({{ $x['nip'] }})</td>
                <td>{{ $x['divisi'] }}</td>
                <td class="c">{{ $x['ptkp'] ?? '-' }}</td>
                <td class="r">{{ $rp($x['nilai']) }}</td>
            </tr>
        @endforeach
    </table>

    <h4>Deteksi Anomali ({{ $a['anomali_total'] }})</h4>
    <table>
        <tr>
            <th>Level</th>
            <th>Karyawan</th>
            <th>Pesan</th>
        </tr>
        @forelse ($a['anomali'] as $x)
            <tr>
                <td class="c">{{ ucfirst($x['level']) }}</td>
                <td>{{ $x['nama'] }} ({{ $x['nip'] }})</td>
                <td>{{ $x['pesan'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="c">Tidak ada anomali terdeteksi</td>
            </tr>
        @endforelse
    </table>
</body>

</html>
