<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Rekap Maintenance</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 0; padding: 15px; }
        h2 { margin-bottom: 2px; font-size: 16px; text-align: center; }
        h3 { margin-top: 15px; margin-bottom: 5px; font-size: 13px; color: #333; }
        p { margin-top: 0; margin-bottom: 10px; font-size: 11px; text-align: center; }
        hr { margin-bottom: 15px; border: 0; border-top: 1px solid #ccc; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; page-break-inside: auto; }
        tr { page-break-inside: avoid; page-break-after: auto; }
        thead { display: table-header-group; }
        th, td { border: 1px solid #000; padding: 4px 6px; text-align: left; font-size: 11px; }
        th { background-color: #f2f2f2; text-align: center; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { font-weight: bold; background-color: #f9f9f9; }
    </style>
</head>
<body>
    <h2>Rekapitulasi Data Maintenance Sistem & Aset</h2>
    <p>Tanggal Cetak: {{ now()->translatedFormat('l, d F Y') }}</p>
    <hr>
    
    <h3>Jadwal Mendatang</h3>
    <table>
        <thead>
            <tr>
                <th width="5%">NO</th>
                <th>ID SERVICE</th>
                <th>NAMA ASET/SISTEM</th>
                <th>TEKNISI</th>
                <th>KATEGORI</th>
                <th>TANGGAL MULAI</th>
                <th class="text-right">BIAYA</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mendatang as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="text-center">MNT-{{ str_pad($item->id, 4, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $item->nama_barang }}</td>
                <td>{{ $item->teknisi }}</td>
                <td>{{ $item->kategori }}</td>
                <td>{{ \Carbon\Carbon::parse($item->tanggal_mulai)->translatedFormat('d F Y') }}</td>
                <td class="text-right">Rp {{ number_format($item->biaya, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada jadwal mendatang.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6" class="text-right">TOTAL BIAYA:</td>
                <td class="text-right">Rp {{ number_format($mendatang->sum('biaya'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <h3>Sedang Dikerjakan (On Progress)</h3>
    <table>
        <thead>
            <tr>
                <th width="5%">NO</th>
                <th>ID SERVICE</th>
                <th>NAMA ASET/SISTEM</th>
                <th>TEKNISI</th>
                <th>KATEGORI</th>
                <th>TANGGAL MULAI</th>
                <th class="text-right">BIAYA</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sedangDikerjakan as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="text-center">MNT-{{ str_pad($item->id, 4, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $item->nama_barang }}</td>
                <td>{{ $item->teknisi }}</td>
                <td>{{ $item->kategori }}</td>
                <td>{{ \Carbon\Carbon::parse($item->tanggal_mulai)->translatedFormat('d F Y') }}</td>
                <td class="text-right">Rp {{ number_format($item->biaya, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada jadwal yang sedang dikerjakan.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6" class="text-right">TOTAL BIAYA:</td>
                <td class="text-right">Rp {{ number_format($sedangDikerjakan->sum('biaya'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <h3>Riwayat Maintenance (Selesai)</h3>
    <table>
        <thead>
            <tr>
                <th width="5%">NO</th>
                <th>ID SERVICE</th>
                <th>NAMA ASET/SISTEM</th>
                <th>TEKNISI</th>
                <th>KATEGORI</th>
                <th>TANGGAL SELESAI</th>
                <th class="text-right">BIAYA</th>
            </tr>
        </thead>
        <tbody>
            @forelse($riwayat as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="text-center">MNT-{{ str_pad($item->id, 4, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $item->nama_barang }}</td>
                <td>{{ $item->teknisi }}</td>
                <td>{{ $item->kategori }}</td>
                <td>{{ \Carbon\Carbon::parse($item->tanggal_selesai)->translatedFormat('d F Y') }}</td>
                <td class="text-right">Rp {{ number_format($item->biaya, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada riwayat maintenance.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6" class="text-right">TOTAL BIAYA:</td>
                <td class="text-right">Rp {{ number_format($riwayat->sum('biaya'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
