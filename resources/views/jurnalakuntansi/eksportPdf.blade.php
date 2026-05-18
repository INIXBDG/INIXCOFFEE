<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Bukti Pengeluaran Kas</title>

<style>
    @page {
        margin: 30px;
    }
    
    body {
        font-family: sans-serif;
        font-size: 11px; /* Dikecilkan sedikit agar muat di layout DomPDF */
        color: #000;
        line-height: 1.4;
    }

    table {
        border-collapse: collapse;
        width: 100%;
        margin-bottom: 10px;
    }

    td, th {
        border: 1px solid #000;
        padding: 5px;
        vertical-align: top;
    }

    /* Penanganan tabel tanpa border khusus DomPDF */
    table.no-border, table.no-border td {
        border: none !important;
    }

    .text-center { text-align: center; }
    .text-right { text-align: right; }

    /* Sintaks page break standar yang kompatibel penuh dengan DomPDF */
    .page-break {
        page-break-after: always;
    }

    .signature-box {
        width: 100%;
        border-collapse: collapse;
        font-size: 9px;
    }

    .signature-box td {
        border: 1px solid #000;
        text-align: center;
    }

    /* Tinggi komponen box tanda tangan */
    .signature-box .title-row td {
        height: 18px;
        vertical-align: middle;
    }

    .signature-box .space-row td {
        height: 105px;
        vertical-align: middle;
    }

    .info-box td {
        border: 1px solid #000;
        padding: 4px;
    }

    /* Pengganti Flexbox khusus untuk kompabilitas DomPDF */
    .logo-box {
        width: 120px;
        height: auto;
    }
    
    .invoice-img {
        max-width: 100%;
        max-height: 700px;
        display: block;
        margin: 0 auto;
    }
</style>
</head>

<body>

<!-- ================= PAGE 1: BUKTI PENGELUARAN KAS ================= -->
<div class="page-body">

    <!-- HEADER -->
    <table class="no-border">
        <tr>
            <td width="50%">
                @if(file_exists(public_path('css/logo.png')))
                    <img src="{{ public_path('css/logo.png') }}" class="logo-box"><br>
                @endif
                Jl. Cipaganti 95<br>
                Bandung
            </td>
            <td class="text-center" width="50%" style="vertical-align: middle;">
                <h3 style="margin: 0; font-size: 16px;">BUKTI PENGELUARAN KAS</h3>
            </td>
        </tr>
    </table>

    <br>

    <!-- INFO -->
    <table class="no-border">
        <tr>
            <td class="no-border" width="55%"></td>

            <!-- BOX INFO KANAN -->
            <td class="no-border" width="45%">
                <table class="info-box" style="font-size:11px;">
                {{-- <table class="info-box"> --}}
                    <tr>
                        <td width="40%">No</td>
                        <td width="60%">{{ $jurnalAkuntansi->nomor_kk }}</td>
                    </tr>
                    <tr>
                        <td>Tanggal</td>
                        <td>{{ \Carbon\Carbon::parse($jurnalAkuntansi->tanggal_transaksi)->translatedFormat('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td>Dibayarkan Kepada</td>
                        <td>{{ $listPengajuan->first()->karyawan->nama_lengkap ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Alamat</td>
                        <td>Bandung</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <br>

    <!-- TABLE MAIN -->
    <table>
        <thead>
            <tr class="text-center" style="background-color: #f2f2f2;">
                <th width="5%">No</th>
                <th width="20%">Kode</th>
                <th>Keterangan</th>
                <th width="25%">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td class="text-center">{{ $jurnalAkuntansi->no_accounting->no ?? $jurnalAkuntansi->no_akun }}</td>
                <td>{{ $jurnalAkuntansi->keterangan }}</td>
                <td class="text-right">
                    @if ($jurnalAkuntansi->kredit === '0.00' || $jurnalAkuntansi->kredit == 0)
                        {{ formatRupiah($jurnalAkuntansi->debit) }}
                    @else
                        {{ formatRupiah($jurnalAkuntansi->kredit) }}
                    @endif
                </td>
            </tr>
            <tr>
                <td colspan="3" class="text-right"><strong>TOTAL</strong></td>
                <td class="text-right">
                    <strong>
                    @if ($jurnalAkuntansi->kredit === '0.00' || $jurnalAkuntansi->kredit == 0)
                        {{ formatRupiah($jurnalAkuntansi->debit) }}
                    @else
                        {{ formatRupiah($jurnalAkuntansi->kredit) }}
                    @endif
                    </strong>
                </td>
            </tr>
        </tbody>
    </table>

    <br><br>

    <table class="no-border">
        <tr>
            <td width="45%" style="vertical-align: top;">
                Terbilang :<br><br>
                ___________________________________<br><br>
                ___________________________________
            </td>
            <td width="55%">
                <table class="signature-box">
                    <tr class="title-row">
                        <td>Dibukukan</td>
                        <td>Mengetahui</td>
                        <td>Yang Membayar</td>
                        <td>Yang Menerima</td>
                    </tr>
                    <tr class="space-row">
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

<!-- Batas Halaman Pertama -->
<div class="page-break"></div>


<!-- ================= PAGE 2: FORM PERMINTAAN BARANG (GABUNGAN) ================= -->
<div class="page-body">

    <table class="no-border">
        <tr>
            <td>
                @if(file_exists(public_path('css/logo.png')))
                    <img src="{{ public_path('css/logo.png') }}" class="logo-box"><br>
                @endif
                INIXINDO BANDUNG<br>
                <small>Jl. Cipaganti no.95 Bandung</small>
            </td>
        </tr>
    </table>

    <h3 class="text-center" style="font-size: 14px; margin-top: 10px;">Form Permintaan Barang</h3>

    <table class="no-border">
        <tr>
            <td width="25%">Hari / Tanggal</td>
            <td width="75%">: {{ \Carbon\Carbon::parse($jurnalAkuntansi->tanggal_transaksi)->translatedFormat('l, d F Y') }}</td>
        </tr>
        <tr>
            <td>Divisi</td>
            <td>: {{ $listPengajuan->first()->karyawan->divisi ?? '-' }}</td>
        </tr>
        <tr>
            <td>Ref. Pengajuan ID</td>
            <td>: 
                @foreach ($listPengajuan as $p)
                    #{{ $p->id }}{{ !$loop->last ? ', ' : '' }}
                @endforeach
            </td>
        </tr>
    </table>

    <br>

    <!-- TABEL BARANG GABUNGAN -->
    <table>
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th width="10%">Qty</th>
                <th>Nama Barang</th>
                <th width="25%">Harga</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php $totalGabungan = 0; @endphp
            @foreach ($listPengajuan as $pengajuan)
                @foreach ($pengajuan->detail as $item)
                    @php
                        $subtotal = $item->qty * $item->harga;
                        $totalGabungan += $subtotal;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $item->qty }}</td>
                        <td>{{ $item->nama_barang }}</td>
                        <td class="text-right">{{ formatRupiah($item->harga) }}</td>
                        <td>
                            {{ $item->keterangan ?? '-' }}
                        </td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #fafafa;">
                <td colspan="2" class="text-right"><strong>Total Keseluruhan</strong></td>
                <td colspan="2" class="text-right"><strong>{{ formatRupiah($totalGabungan) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <br><br>

    <!-- TTD FORM PERMINTAAN -->
    <table class="no-border text-center">
        <tr>
            <td width="33%">Yang Mengajukan</td>
            <td width="33%">Menyetujui</td>
            <td width="33%">Mengetahui</td>
        </tr>
        <tr>
            <!-- Menggunakan public_path() agar DomPDF mendeteksi letak ttd lokal -->
            <td height="70" style="vertical-align: middle;">
                @if ($listPengajuan->first()->karyawan && $listPengajuan->first()->karyawan->ttd && file_exists(public_path('storage/ttd/' . $listPengajuan->first()->karyawan->ttd)))
                    <img src="{{ public_path('storage/ttd/' . $listPengajuan->first()->karyawan->ttd) }}" style="width: 100px; height: auto;">
                @endif
            </td>
            <td style="vertical-align: middle;">
                @if ($finance && $finance->ttd && file_exists(public_path('storage/ttd/' . $finance->ttd)))
                    <img src="{{ public_path('storage/ttd/' . $finance->ttd) }}" style="width: 100px; height: auto;">
                @endif
            </td>
            <td style="vertical-align: middle;">
                @if ($gm && $gm->ttd && file_exists(public_path('storage/ttd/' . $gm->ttd)))
                    <img src="{{ public_path('storage/ttd/' . $gm->ttd) }}" style="width: 100px; height: auto;">
                @endif
            </td>
        </tr>
        <tr style="font-weight: bold;">
            <td>{{ $listPengajuan->first()->karyawan->nama_lengkap ?? '-' }}</td>
            <td>{{ $finance->nama_lengkap ?? '_________________' }}</td>
            <td>{{ $gm->nama_lengkap ?? '_________________' }}</td>
        </tr>
    </table>

</div>


<!-- ================= SECTION INVOICE (DI BAWAH) ================= -->
@foreach ($listPengajuan as $invoiceData)
    @if ($invoiceData->invoice && file_exists(public_path('storage/' . $invoiceData->invoice)))
        <div class="page-break"></div>
        <div class="page-body">
            <h4 class="text-center" style="margin-bottom: 15px;">Lampiran Berkas / Invoice (ID Pengajuan: #{{ $invoiceData->id }})</h4>
            <div class="text-center">
                <img src="{{ public_path('storage/' . $invoiceData->invoice) }}" class="invoice-img">
            </div>
        </div>
    @endif
@endforeach

</body>
</html>