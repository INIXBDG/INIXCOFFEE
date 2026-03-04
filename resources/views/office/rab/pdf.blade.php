<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>PDF {{ $kegiatan->nama_kegiatan }}</title>
    <style>
        /* --- STYLE CSS SAMA SEPERTI SEBELUMNYA --- */
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }

        .card {
            border: 1px solid #dee2e6;
            border-radius: 0;
            margin-bottom: 20px;
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #dee2e6;
            padding: 15px;
        }

        .card-body {
            padding: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        td {
            vertical-align: top;
        }

        .title {
            font-size: 14px;
            font-weight: bold;
            color: #0d6efd;
            text-transform: uppercase;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            font-size: 9px;
            font-weight: bold;
            color: #198754;
            background-color: #d1e7dd;
            border: 1px solid #badbcc;
            border-radius: 4px;
            text-transform: uppercase;
        }

        .badge-info {
            color: #055160;
            background-color: #cff4fc;
            border-color: #b6effb;
        }

        .label {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: #6c757d;
            display: block;
            margin-bottom: 3px;
        }

        .value {
            font-size: 11px;
            font-weight: bold;
            color: #212529;
        }

        .sub-value {
            font-size: 10px;
            color: #0d6efd;
        }

        .border-top {
            border-top: 1px solid #dee2e6;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #212529;
            text-transform: uppercase;
            border-bottom: 2px solid #f8f9fa;
            padding-bottom: 5px;
        }

        .info-cell {
            padding-right: 10px;
            border-right: 1px solid #dee2e6;
        }

        .info-cell:last-child {
            border-right: none;
        }

        /* Table Bordered (Dipakai untuk Barang & Peserta) */
        .table-bordered {
            font-size: 10px;
            margin-top: 5px;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #dee2e6;
            padding: 6px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .table-bordered th {
            background-color: #f8f9fa;
            text-align: left;
            text-transform: uppercase;
        }

        .item-row {
            border-bottom: 1px dashed #e9ecef;
            padding-bottom: 3px;
            margin-bottom: 3px;
        }

        .item-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-warning {
            color: #d68c06;
        }

        .text-success {
            color: #198754;
        }

        .text-info {
            color: #0dcaf0;
        }

        .text-muted {
            color: #6c757d;
        }

        .fw-bold {
            font-weight: bold;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>

<body>

    <div class="card">
        {{-- 1. Header --}}
        <div class="card-header">
            <table>
                <tr>
                    <td style="width: 70%;">
                        <div class="title">{{ $kegiatan->nama_kegiatan }}</div>
                    </td>
                    <td style="width: 30%; text-align: right;">
                        <span class="badge">
                            {{ $kegiatan->status }} || {{ $kegiatan->tipe }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        {{-- 2. Informasi Utama --}}
        <div class="card-body">
            <table>
                <tr>
                    <td class="info-cell" style="width: 25%;">
                        <span class="label">PIC</span>
                        <div class="value">{{ $kegiatan->pic ?? '-' }}</div>
                    </td>

                    @if ($kegiatan->tipe != 'pembelian')
                        <td class="info-cell" style="width: 25%;">
                            <span class="label">DURASI</span>
                            <div class="value">{{ $kegiatan->lama_kegiatan ?? '-' }}</div>
                        </td>
                        <td class="info-cell" style="width: 25%;">
                            <span class="label">WAKTU KEGIATAN</span>
                            <div class="value">
                                {{ $kegiatan->waktu_kegiatan ? \Carbon\Carbon::parse($kegiatan->waktu_kegiatan)->translatedFormat('d F Y') : '-' }}
                            </div>
                            <div class="sub-value">
                                {{ $kegiatan->waktu_kegiatan ? \Carbon\Carbon::parse($kegiatan->waktu_kegiatan)->format('H:i') . ' WIB' : '' }}
                            </div>
                        </td>
                        {{-- Hapus Info Peserta ringkas disini jika sudah ada tabel di bawah --}}
                        <td class="info-cell" style="width: 25%;">
                            <span class="label">TOTAL PESERTA</span>
                            <div class="value">{{ $karyawan->count() }} Orang</div>
                        </td>
                    @else
                        {{-- Layout khusus Pembelian --}}
                        <td class="info-cell" style="width: 75%; border: none;">
                            <span class="label">WAKTU PEMBELIAN</span>
                            <div class="value">
                                {{ $kegiatan->waktu_kegiatan ? \Carbon\Carbon::parse($kegiatan->waktu_kegiatan)->translatedFormat('d F Y') : '-' }}
                            </div>
                        </td>
                    @endif
                </tr>
            </table>
        </div>

        {{-- 3. Tracking Status --}}
        <div class="card-body border-top">
            <div class="section-title">Tracking Status Kegiatan</div>
            <table>
                <tr>
                    <td class="info-cell" style="width: 20%;">
                        <span class="label">DIAJUKAN</span>
                        <div class="value">
                            {{ $kegiatan->created_at ? \Carbon\Carbon::parse($kegiatan->created_at)->translatedFormat('d M Y H:i') : '-' }}
                        </div>
                    </td>
                    <td class="info-cell" style="width: 20%;">
                        <span class="label">MENUNGGU</span>
                        <div class="value {{ $kegiatan->menunggu ? 'text-warning' : 'text-muted' }}">
                            {{ $kegiatan->menunggu ? \Carbon\Carbon::parse($kegiatan->menunggu)->translatedFormat('d M Y H:i') : '-' }}
                        </div>
                    </td>
                    <td class="info-cell" style="width: 20%;">
                        <span class="label">APPROVED</span>
                        <div class="value {{ $kegiatan->approved ? 'text-success' : 'text-muted' }}">
                            {{ $kegiatan->approved ? \Carbon\Carbon::parse($kegiatan->approved)->translatedFormat('d M Y H:i') : '-' }}
                        </div>
                    </td>
                    <td class="info-cell" style="width: 20%;">
                        <span class="label">PENCAIRAN</span>
                        <div class="value {{ $kegiatan->pencairan ? 'text-info' : 'text-muted' }}">
                            {{ $kegiatan->pencairan ? \Carbon\Carbon::parse($kegiatan->pencairan)->translatedFormat('d M Y H:i') : '-' }}
                        </div>
                    </td>
                    <td class="info-cell" style="width: 20%; border-right: none;">
                        <span class="label">SELESAI</span>
                        <div class="value {{ $kegiatan->selesai ? 'text-success' : 'text-muted' }}">
                            {{ $kegiatan->selesai ? \Carbon\Carbon::parse($kegiatan->selesai)->translatedFormat('d M Y H:i') : '-' }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        {{-- 4. Rincian Barang (Core Content) --}}
        <div class="card-body border-top">
            <div class="section-title">Rincian Pengajuan Barang</div>
            <table class="table-bordered">
                <thead>
                    <tr>
                        <th style="width: 5%;" class="text-center">No</th>
                        <th style="width: 20%;">Tanggal & Pemohon</th>
                        <th style="width: 10%;">Tipe</th>
                        <th style="width: 35%;">Rincian Barang</th>
                        <th style="width: 15%;" class="text-end">Total (IDR)</th>
                        <th style="width: 15%;">Status Tracking</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dataPengajuanBarang as $pengajuan)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>
                                <div class="fw-bold">
                                    {{ \Carbon\Carbon::parse($pengajuan->created_at)->translatedFormat('d M Y') }}
                                </div>
                                <div class="text-muted" style="margin-top: 2px;">
                                    {{ $pengajuan->karyawan->nama_lengkap ?? '-' }}</div>
                                <div class="text-info" style="font-size: 9px;">
                                    {{ $pengajuan->karyawan->divisi ?? '-' }}</div>
                            </td>
                            <td><span class="badge badge-info">{{ $pengajuan->tipe }}</span></td>
                            <td>
                                @php $subTotalPengajuan = 0; @endphp
                                @foreach ($pengajuan->detail as $item)
                                    @php
                                        $totalItem = $item->harga * $item->qty;
                                        $subTotalPengajuan += $totalItem;
                                    @endphp
                                    <div class="item-row">
                                        <div class="fw-bold">{{ $item->nama_barang }}</div>
                                        <div class="text-muted">{{ $item->qty }} x Rp
                                            {{ number_format($item->harga, 0, ',', '.') }}
                                            ({{ $item->keterangan ?? '-' }})
                                        </div>
                                    </div>
                                @endforeach
                            </td>
                            <td class="text-end fw-bold">{{ number_format($subTotalPengajuan, 0, ',', '.') }}</td>
                            <td>
                                @if ($pengajuan->tracking)
                                    <div class="text-warning fw-bold" style="font-size: 9px;">
                                        {{ $pengajuan->tracking->tracking }}</div>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted" style="padding: 10px;">Belum ada data
                                pengajuan barang.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($kegiatan->tipe != 'pembelian')
            <div class="page-break"></div>

            <div class="card-body border-top">
                <div class="section-title">Data Peserta Kegiatan</div>

                @php
                    $pakaiPesertaManual = isset($peserta) && $peserta->count() > 0;
                    $pakaiDataAbsensi = isset($karyawan) && $karyawan->count() > 0;
                @endphp

                @if ($pakaiPesertaManual)
                    {{-- PRIORITAS 1: Render dari variabel $peserta --}}
                    <table class="table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 5%;" class="text-center">No</th>
                                <th style="width: 45%;">Nama Lengkap</th>
                                <th style="width: 50%;">Jabatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($peserta as $index => $p)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td class="fw-bold">
                                        {{ $p->nama_lengkap ?? ($p->nama ?? '-') }}
                                    </td>
                                    <td>
                                        {{ $p->jabatan ?? '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @elseif ($pakaiDataAbsensi)
                    {{-- PRIORITAS 2: Render dari variabel $karyawan (Absensi) --}}
                    <table class="table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 5%;" class="text-center">No</th>
                                <th style="width: 35%;">Nama Lengkap</th>
                                <th style="width: 30%;">Jabatan</th>
                                <th style="width: 30%;">Waktu Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($karyawan as $index => $absen)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td class="fw-bold">
                                        {{ $absen->karyawan->nama_lengkap ?? '-' }}
                                    </td>
                                    <td>
                                        {{ $absen->karyawan->jabatan ?? '-' }}
                                    </td>
                                    <td>
                                        {{ $absen->jam_masuk ?? '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    {{-- EMPTY STATE --}}
                    <div class="text-center text-muted"
                        style="padding: 15px; border: 1px dashed #dee2e6; margin-top: 5px;">
                        Tidak ada peserta kegiatan yang tercatat.
                    </div>
                @endif
            </div>
        @endif
</body>

</html>
