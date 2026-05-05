<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Data Perbaikan Kendaraan</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm 10mm 15mm 10mm;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 5px 0;
            font-size: 16px;
            color: #2c3e50;
        }

        .header p {
            margin: 3px 0;
            font-style: italic;
            color: #7f8c8d;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #bdc3c7;
            padding: 6px 4px;
            vertical-align: middle;
            text-align: left;
        }

        th {
            background-color: #ecf0f1;
            font-weight: bold;
            font-size: 9px;
            color: #2c3e50;
            text-align: center;
            padding: 8px 4px;
        }

        td {
            font-size: 8.5px;
            line-height: 1.3;
        }

        .text-right { 
            text-align: right !important; 
        }
        
        .text-center { 
            text-align: center !important; 
        }

        .status-menunggu { 
            background: #fef9e7; 
            padding: 2px 6px; 
            border-radius: 3px; 
            color: #d35400;
            font-weight: normal;
        }
        
        .status-diterima { 
            background: #eaf2f8; 
            padding: 2px 6px; 
            border-radius: 3px; 
            color: #2980b9;
            font-weight: normal;
        }
        
        .status-selesai { 
            background: #e8f5e9; 
            padding: 2px 6px; 
            border-radius: 3px; 
            color: #27ae60;
            font-weight: normal;
        }

        .total-row {
            font-weight: bold;
            background: #f8f9fa;
        }

        .signature {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            text-align: center;
            width: 45%;
        }

        .signature-box .line {
            border-top: 1px solid #333;
            margin: 40px 0 8px;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }

        .signature-box p {
            margin: 5px 0;
            font-size: 8px;
        }

        /* Lebar kolom responsif */
        .col-no { width: 3%; }
        .col-kendaraan { width: 8%; }
        .col-driver { width: 8%; }
        .col-jenis-kondisi { width: 8%; }
        .col-kondisi-kendaraan { width: 8%; }
        .col-jenis-perbaikan { width: 8%; }
        .col-deskripsi-kondisi { width: 10%; }
        .col-waktu { width: 7%; }
        .col-lokasi { width: 7%; }
        .col-estimasi { width: 6%; }
        .col-status { width: 6%; }
        .col-bukti { width: 5%; }
        .col-tanggal { width: 7%; }
        .col-selesai { width: 7%; }
        .col-detail { width: 8%; }
        .col-document { width: 6%; }
        .col-invoice { width: 5%; }
        .col-deskripsi-perbaikan { width: 10%; }
        .col-vendor { width: 8%; }

        /* Warna baris selang-seling */
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* Penyesuaian untuk konten panjang */
        .truncate {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Untuk header jika terjadi page break */
        thead { 
            display: table-header-group; 
        }
        
        /* Hindari page break di tengah baris */
        tbody tr {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>LAPORAN DATA PERBAIKAN KENDARAAN</h2>
        <p>Office Management System</p>
        <p>
            Periode:
            {{ $from ? \Carbon\Carbon::parse($from)->format('d-m-Y') : '-' }}
            s/d
            {{ $to ? \Carbon\Carbon::parse($to)->format('d-m-Y') : '-' }}
        </p>
        <p>Diexport: {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y, H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-kendaraan">Kendaraan</th>
                <th class="col-driver">Driver</th>
                <th class="col-jenis-kondisi">Jenis Kondisi</th>
                <th class="col-kondisi-kendaraan">Kondisi Kendaraan</th>
                <th class="col-jenis-perbaikan">Jenis Perbaikan</th>
                <th class="col-deskripsi-kondisi">Deskripsi Kondisi</th>
                <th class="col-waktu">Waktu Kejadian</th>
                <th class="col-lokasi">Lokasi</th>
                <th class="col-estimasi">Estimasi (Rp)</th>
                <th class="col-status">Status</th>
                <th class="col-bukti">Bukti</th>
                <th class="col-tanggal">Tgl Perbaikan</th>
                <th class="col-selesai">Selesai Perbaikan</th>
                <th class="col-detail">Detail Perbaikan</th>
                <th class="col-document">Document</th>
                <th class="col-invoice">Invoice</th>
                <th class="col-deskripsi-perbaikan">Deskripsi Perbaikan</th>
                <th class="col-vendor">Vendor</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $d)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $d->kendaraan ?? '-' }}</td>
                    <td>{{ $d->user->karyawan->nama_lengkap ?? '-' }}</td>
                    <td>{{ $d->type_condition ?? '-' }}</td>
                    <td>{{ $d->type_vehicle_condition ?? '-' }}</td>
                    <td>{{ $d->type_repair ?? '-' }}</td>
                    <td>{{ Str::limit($d->deskripsi_kondisi ?? '-', 60) }}</td>
                    <td>
                        @if($d->tanggal_kejadian)
                            {{ \Carbon\Carbon::parse($d->tanggal_kejadian)->format('d-m-Y') }}<br>
                            {{ \Carbon\Carbon::parse($d->waktu_kejadian)->format('H:i') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $d->lokasi ?? '-' }}</td>
                    <td class="text-right">{{ number_format($d->estimasi ?? 0, 0, ',', '.') }}</td>
                    <td>
                        <span class="status-{{ strtolower(str_replace(' ', '-', $d->status)) }}">
                            {{ $d->status ?? '-' }}
                        </span>
                    </td>
                    <td class="text-center">{{ $d->bukti ? 'Ada' : 'Tidak' }}</td>
                    <td>{{ $d->tanggal_perbaikan ? \Carbon\Carbon::parse($d->tanggal_perbaikan)->format('d-m-Y') : '-' }}</td>
                    <td>{{ $d->selesai_perbaikan ? \Carbon\Carbon::parse($d->selesai_perbaikan)->format('d-m-Y') : '-' }}</td>
                    <td>{{ Str::limit($d->detail_perbaikan ?? '-', 50) }}</td>
                    <td>{{ $d->document ?? '-' }}</td>
                    <td class="text-center">{{ $d->invoice ? 'Ada' : 'Tidak' }}</td>
                    <td>{{ Str::limit($d->deskripsi_perbaikan ?? '-', 60) }}</td>
                    <td>{{ $d->vendor->nama ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="19" class="text-center">Tidak ada data ditemukan</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>