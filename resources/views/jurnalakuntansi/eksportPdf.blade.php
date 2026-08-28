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
        padding: 3px 5px;
    }

    .signature-box .signature-row td {
        height: 42px;
        vertical-align: middle;
        padding: 2px 5px;
    }

    .signature-box .space-row td {
        height: 30px;
        vertical-align: middle;
        padding: 2px 5px;
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
                {{ $terbilang }}
            </td>
            <td width="55%">
                <table class="signature-box">
                    <tr class="title-row">
                        <td>Dibukukan</td>
                        <td>Mengetahui</td>
                        <td>Yang Membayar</td>
                        <td>Yang Menerima</td>
                    </tr>

                    {{-- Row tanda tangan --}}
                    <tr class="signature-row">
                        <td>
                            @if($ttd_accounting)
                                <img src="{{ public_path('storage/ttd/' . $ttd_accounting->ttd) }}" style="height: 60px; width: auto;">
                            @endif
                        </td>
                        <td>
                            @if($ttd_gm)
                                <img src="{{ public_path('storage/ttd/' . $ttd_gm->ttd) }}" style="height: 60px; width: auto;">
                            @endif
                        </td>
                        <td>
                            @if($ttd_keuangan)
                                <img src="{{ public_path('storage/ttd/' . $ttd_keuangan->ttd) }}" style="height: 60px; width: auto;">
                            @endif
                        </td>
                        <td>
                            @if($penerima)
                                <img src="{{ public_path('storage/ttd/' . $penerima->ttd) }}" style="height: 60px; width: auto;">
                            @endif
                        </td>
                    </tr>

                    <tr class="space-row">
                        <td>Accounting</td>
                        <td>Ka. Div</td>
                        <td>Keuangan</td>
                        @if ($penerima)
                            <td>{{ $penerima->nama_lengkap ?? '' }}</td>
                        @else
                            <td>{{ $orangluar ?? ''}}</td>
                        @endif
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</div>

<!-- Batas Halaman Pertama -->
<div class="page-break"></div>


<!-- ================= PAGE 2: FORM PERMINTAAN BARANG (GABUNGAN) ================= -->
@php
    $isNetSales = empty($jurnalAkuntansi->id_pengajuan_barang);
    $isPengajuanBarang = empty($jurnalAkuntansi->id_perhitungan_net_sales);
@endphp

@if($isPengajuanBarang)
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
                        $subtotal = ($item->qty ?? 1) * ($item->harga_barang ?? $item->harga ?? 0);
                        $totalGabungan += $subtotal;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $item->qty ?? 1 }}</td>
                        <td>{{ $item->nama_barang ?? '-' }}</td>
                        <td class="text-right">{{ formatRupiah($item->harga_barang ?? $item->harga ?? 0) }}</td>
                        <td>{{ $item->keterangan ?? '-' }}</td>
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
@endif

@if($isNetSales && isset($netSales))
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

        <h3 class="text-center" style="font-size: 14px; margin-top: 10px;">
            Form Net Sales
        </h3>

        <div style="display: flex; flex-wrap: wrap; gap: 20px; margin-top: 15px;">
            
            <div style="flex: 1; min-width: 200px; padding: 10px; border-radius: 5px;">
                <p style="margin: 0 0 8px 0;">
                    <strong style="display: inline-block; width: 120px;">Hari / Tanggal</strong>
                    : {{ \Carbon\Carbon::parse($netSales->tgl_pa)->translatedFormat('l, d F Y') }}
                </p>
                
                @if(isset($netSales->rkm->perusahaan->nama_perusahaan))
                <p style="margin: 0 0 8px 0;">
                    <strong style="display: inline-block; width: 120px;">Perusahaan</strong>
                    : {{ $netSales->rkm->perusahaan->nama_perusahaan }}
                </p>
                @endif
                
                @if(isset($netSales->rkm->materi->nama_materi))
                <p style="margin: 0 0 8px 0;">
                    <strong style="display: inline-block; width: 120px;">Materi</strong>
                    : {{ $netSales->rkm->materi->nama_materi }}
                </p>
                @endif

                @if(isset($netSales->rkm->tanggal_awal) && isset($netSales->rkm->tanggal_akhir))
                <p style="margin: 0 0 8px 0;">
                    <strong style="display: inline-block; width: 120px;">Tgl Pelatihan</strong>
                   : {{ \Carbon\Carbon::parse($netSales->rkm->tanggal_awal)->translatedFormat('d F Y') }} 
                    - 
                    {{ \Carbon\Carbon::parse($netSales->rkm->tanggal_akhir)->translatedFormat('d F Y') }}
                </p>
                @endif
            </div>
            
            <div style="flex: 2; min-width: 250px;">
                <table class="table table-bordered" width="100%" cellspacing="0" cellpadding="5">
                    {{-- Tipe Pembayaran --}}
                    @if($netSales->tipe_pembayaran)
                    <tr>
                        <th width="35%" style="background-color: #f2f2f2;">Tipe Pembayaran</th>
                            <td>{{ strtoupper($netSales->tipe_pembayaran) }}</td>
                        </tr>
                    @endif

                    {{-- Transportasi --}}
                    @if($netSales->transportasi)
                    <tr>
                        <th style="background-color: #f2f2f2;">Transportasi</th>
                        <td>{{ formatRupiah($netSales->transportasi) }}</td>
                    </tr>
                    @endif

                    {{-- Jenis Transportasi --}}
                    @if($netSales->jenis_transportasi)
                    <tr>
                        <th style="background-color: #f2f2f2;">Jenis Transportasi</th>
                        <td>{{ $netSales->jenis_transportasi }}</td>
                    </tr>
                    @endif

                    {{-- Akomodasi Peserta --}}
                    @if($netSales->akomodasi_peserta)
                    <tr>
                        <th style="background-color: #f2f2f2;">Akomodasi Peserta</th>
                        <td>{{ formatRupiah($netSales->akomodasi_peserta) }}</td>
                    </tr>
                    @endif

                    {{-- Akomodasi Tim --}}
                    @if($netSales->akomodasi_tim)
                    <tr>
                        <th style="background-color: #f2f2f2;">Akomodasi Tim</th>
                        <td>{{ formatRupiah($netSales->akomodasi_tim) }}</td>
                    </tr>
                    @endif

                    {{-- Keterangan Akomodasi Tim --}}
                    @if($netSales->keterangan_akomodasi_tim)
                    <tr>
                        <th style="background-color: #f2f2f2;">Keterangan Akomodasi Tim</th>
                        <td>{{ $netSales->keterangan_akomodasi_tim }}</td>
                    </tr>
                    @endif

                    {{-- Fresh Money --}}
                    @if($netSales->fresh_money)
                    <tr>
                        <th style="background-color: #f2f2f2;">Fresh Money</th>
                        <td>{{ formatRupiah($netSales->fresh_money) }}</td>
                    </tr>
                    @endif

                    {{-- Entertaint --}}
                    @if($netSales->entertaint)
                    <tr>
                        <th style="background-color: #f2f2f2;">Entertaint</th>
                        <td>{{ formatRupiah($netSales->entertaint) }}</td>
                    </tr>
                    @endif

                    {{-- Keterangan Entertaint --}}
                    @if($netSales->keterangan_entertaint)
                    <tr>
                        <th style="background-color: #f2f2f2;">Keterangan Entertaint</th>
                        <td>{{ $netSales->keterangan_entertaint }}</td>
                    </tr>
                    @endif

                    {{-- Souvenir --}}
                    @if($netSales->souvenir)
                    <tr>
                        <th style="background-color: #f2f2f2;">Souvenir</th>
                        <td>{{ formatRupiah($netSales->souvenir) }}</td>
                    </tr>
                    @endif

                    {{-- Cashback --}}
                    @if($netSales->cashback)
                    <tr>
                        <th style="background-color: #f2f2f2;">Cashback</th>
                        <td>{{ formatRupiah($netSales->cashback) }}</td>
                    </tr>
                    @endif

                    {{-- Sewa Laptop --}}
                    @if($netSales->sewa_laptop)
                    <tr>
                        <th style="background-color: #f2f2f2;">Sewa Laptop</th>
                        <td>{{ formatRupiah($netSales->sewa_laptop) }}</td>
                    </tr>
                    @endif
                </table>
                @php
                    $approvals = [
                        ['label' => 'Requested by:', 'person' => $sales ?? null],
                        ['label' => 'Manager:', 'person' => $manager ?? null],
                        ['label' => 'General Manager:', 'person' => $gm ?? null],
                        ['label' => 'Director:', 'person' => $dirut ?? null],
                    ];
                @endphp

                <table class="no-border" style="margin-top: 40px; width: 100%;">
                    <tr>
                        <td class="no-border" colspan="3"><strong>APPROVALS</strong></td>
                    </tr>
                    <tr>
                        <td class="no-border" style="width: 35%; vertical-align: top;">
                            <table style="width: 55%; text-align: left;" cellspacing="0" cellpadding="5">
                                @foreach($approvals as $approval)
                                <tr>
                                    <th style="text-align: left; max-height: 40px; max-width: 120px; border-right: none;">
                                        {{ $approval['label'] }}
                                    </th>
                                    <th style="border-left: none;">
                                        @if($approval['person'] && $approval['person']->ttd)
                                            <img src="{{ public_path('storage/ttd/' . $approval['person']->ttd) }}" style="max-width: 45px; max-height: 40px; margin-top: 5px;">
                                        @endif
                                    </th>
                                </tr>
                                @endforeach
                            </table>
                        </td>
                        <td class="no-border" style="width: 15%;"></td>
                        <td class="no-border" style="width: 50%; vertical-align: top;">
                            <table style="width: 60%; text-align: left;" cellspacing="0" cellpadding="5">
                                <tr>
                                    <th style="text-align: left; max-height: 40px; border-right: 1px solid black;" colspan="2">
                                        For Finance & Administration only
                                    </th>
                                </tr>
                                <tr>
                                    <th style="text-align: left; max-height: 40px; border-right: 1px solid black;" colspan="2">
                                        Status: Lengkap
                                    </th>
                                </tr>
                                <tr>
                                    <th style="text-align: left; max-height: 40px; border-right: none;">
                                        Verified by:
                                    </th>
                                    <th style="border-left: none;">
                                        @if($finance && $finance->ttd)
                                            <img src="{{ public_path('storage/ttd/' . $approval['person']->ttd) }}" style="max-width: 45px; max-height: 40px; margin-top: 5px;">
                                        @endif
                                    </th>                                
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        @if($netSales->deskripsi_tambahan)
            <div style="margin-top: 20px; padding: 10px; border: 1px solid #000; border-radius: 5px;">
                <strong>Catatan Tambahan:</strong><br>
                {{ $netSales->deskripsi_tambahan }}
            </div>
        @endif

        <br>
    </div>
@endif

<!-- ================= SECTION INVOICE/BUKTI (PALING BAWAH) ================= -->
@if($isNetSales && isset($netSales))
    @if($netSales->bukti && file_exists(public_path('storage/' . $netSales->bukti)))
        <div class="page-break"></div>
        <div class="page-body">
            <h4 class="text-center" style="margin-bottom: 15px;">Lampiran Bukti</h4>
            <div class="text-center">
                <img src="{{ public_path('storage/' . $netSales->bukti) }}" class="invoice-img">
            </div>
        </div>
    @endif
@elseif($isPengajuanBarang && isset($listPengajuan))
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
        @if ($invoiceData->bukti && file_exists(public_path('storage/' . $invoiceData->bukti)))
            <div class="page-break"></div>
            <div class="page-body">
                <h4 class="text-center" style="margin-bottom: 15px;">Lampiran Bukti (ID Pengajuan: #{{ $invoiceData->id }})</h4>
                <div class="text-center">
                    <img src="{{ public_path('storage/' . $invoiceData->bukti) }}" class="invoice-img">
                </div>
            </div>
        @endif
    @endforeach
@endif

</body>
</html>