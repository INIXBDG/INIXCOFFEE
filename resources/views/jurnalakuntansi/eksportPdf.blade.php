<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Bukti Pengeluaran Kas</title>

<style>
    body {
        font-family: sans-serif;
        font-size: 12px;
    }

    table {
        border-collapse: collapse;
        width: 100%;
    }

    td, th {
        border: 1px solid #000;
        padding: 5px;
    }

    .no-border td {
        border: none;
    }

    .text-center { text-align: center; }
    .text-right { text-align: right; }

    .page {
        page-break-after: always;
    }

    .page:last-child {
        page-break-after: auto;
    }

    .signature-box {
        width: 100%;
        border-collapse: collapse;
        font-size: 10px;
    }

    .signature-box td {
        border: 1px solid #000;
        text-align: center;
    }

    /* Baris judul */
    .signature-box tr:first-child td {
        height: 18px;
        vertical-align: middle;
    }

    /* Area tanda tangan */
    .signature-box tr:last-child td {
        height: 60px;       /* tinggi kotak */
        vertical-align: bottom;
        padding-bottom: 5px;
    }

    .info-box td {
        border: 1px solid #000;
        padding: 3px;
    }

    .logo-wrapper { 
        width: 160px; 
        height: 60px; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        overflow: hidden; 
    } 
    
    .logo-wrapper img { 
        width: 230%; 
        height: 230%; 
        object-fit: cover; 
        object-position: center; 
    }
</style>
</head>

<body>

<!-- ================= PAGE 1 ================= -->
<div class="page">

    <!-- HEADER -->
    <table class="no-border">
        <tr>
            <td class="no-border" width="50%">
                <div class="logo-wrapper">
                    <img src="{{ asset('css/logo.png') }}"><br>
                </div>
                Jl. Cipaganti 95<br>
                Bandung
            </td>
            <td class="no-border text-center" width="50%">
                <h3>BUKTI PENGELUARAN KAS</h3>
            </td>
        </tr>
    </table>

    <br>

    <!-- INFO -->
    <table class="no-border" width="100%">
        <tr>
            <!-- KIRI KOSONG -->
            <td class="no-border" width="55%"></td>

            <!-- BOX INFO KANAN -->
            <td class="no-border" width="45%">
                <table class="info-box" style="font-size:11px;">
                    <tr>
                        <td width="40%">No</td>
                        <td width="60%">{{ $jurnalAkuntansi->nomor_kk }}</td>
                    </tr>
                    <tr>
                        <td>Tanggal</td>
                        <td>{{ $jurnalAkuntansi->tanggal_transaksi }}</td>
                    </tr>
                    <tr>
                        <td>Dibayarkan Kepada</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Alamat</td>
                        <td></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <br>

    <!-- TABLE MAIN -->
    <table>
        <thead>
            <tr class="text-center">
                <th width="5%">No</th>
                <th width="20%">Kode</th>
                <th>Keterangan</th>
                <th width="25%">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>&nbsp;</td>
                <td></td>
                <td>{{ $jurnalAkuntansi->keterangan }}</td>
                <td>
                    @if ($jurnalAkuntansi->kredit === '0.00')
                        Debit : {{ formatRupiah($jurnalAkuntansi->debit) }}
                    @elseif($jurnalAkuntansi->debit === '0.00')
                        Kredit : {{ formatRupiah($jurnalAkuntansi->kredit) }}
                    @else
                        Debit : {{ formatRupiah($jurnalAkuntansi->debit) }}<br>
                        Kredit : {{ formatRupiah($jurnalAkuntansi->kredit) }}
                    @endif
                </td>
            </tr>

            <tr>
                <td colspan="3" class="text-right"><strong>TOTAL</strong></td>
                <td>
                    @if ($jurnalAkuntansi->kredit === '0.00')
                        {{ formatRupiah($jurnalAkuntansi->debit) }}
                    @elseif($jurnalAkuntansi->debit === '0.00')
                        {{ formatRupiah($jurnalAkuntansi->kredit) }}
                    @else
                        Debit : {{ formatRupiah($jurnalAkuntansi->debit) }}<br>
                        Kredit : {{ formatRupiah($jurnalAkuntansi->kredit) }}
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <br>

    <!-- TERBILANG + SIGNATURE -->
    <table class="no-border">
        <tr>
            <td class="no-border" width="50%">
                Terbilang :<br><br>
                ___________________________<br>
                ___________________________
            </td>

            <td class="no-border" width="50%">
                <table class="signature-box">
                    <tr>
                        <td>Dibukukan</td>
                        <td>Mengetahui</td>
                        <td>Yang Membayar</td>
                        <td>Yang Menerima</td>
                    </tr>
                    <tr>
                        <td>Accounting</td>
                        <td>Ka. Div</td>
                        <td>Keuangan</td>
                        <td></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</div>


<!-- ================= PAGE 2 ================= -->
<div class="page">

    <table class="no-border">
        <tr>
            <td class="no-border" width="50%">
                <img src="{{ asset('css/logo.png') }}" width="100"><br>
                INIXINDO BANDUNG<br>
                <small>Jl. Cipaganti no.95 Bandung</small>
            </td>
        </tr>
    </table>

    <h3 class="text-center">Form Permintaan Barang</h3>

    <table>
        <tr>
            <td width="30%">Hari / Tanggal</td>
            <td>{{ \Carbon\Carbon::parse($data->created_at)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td>Divisi</td>
            <td>{{ $data->karyawan->divisi }}</td>
        </tr>
    </table>

    <br>

    <table>
        <thead>
            <tr>
                <th>Qty</th>
                <th>Nama Barang</th>
                <th>Harga</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp

            @foreach ($data->detail as $item)
                @php
                    $subtotal = $item->qty * $item->harga;
                    $total += $subtotal;
                @endphp
                <tr>
                    <td>{{ $item->qty }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>{{ formatRupiah($item->harga) }}</td>
                    <td>{{ $item->keterangan }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2"><strong>Total</strong></td>
                <td colspan="2">{{ formatRupiah($total) }}</td>
            </tr>
        </tfoot>
    </table>

    <br>

    <!-- TTD -->
    <table class="no-border text-center">
        <tr>
            <td>Yang Mengajukan</td>
            <td>Menyetujui</td>
            <td>Mengetahui</td>
        </tr>
        <tr>
            <td>
                @if ($data->karyawan->ttd)
                    <img src="{{ asset('storage/ttd/' . $data->karyawan->ttd) }}" style="width: 155px;height:auto">
                @endif
            </td>
            <td>
                @if ($finance->ttd)
                    <img src="{{ asset('storage/ttd/' . $finance->ttd) }}" style="width: 155px;height:auto">
                @endif
            </td>
            <td>
                @if ($gm->ttd)
                    <img src="{{ asset('storage/ttd/' . $gm->ttd) }}" style="width: 155px;height:auto">
                @endif
            </td>
        </tr>
        <tr>
            <td>{{ $data->karyawan->nama_lengkap }}</td>
            <td>{{ $finance->nama_lengkap }}</td>
            <td>{{ $gm->nama_lengkap }}</td>
        </tr>
    </table>

</div>


<!-- ================= PAGE 3 (INVOICE) ================= -->
@if ($data->invoice)
<div class="page">
    <img src="{{ asset('storage/' . $data->invoice) }}" style="width:100%;">
</div>
@endif

</body>
</html>