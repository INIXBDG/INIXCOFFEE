<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "DejaVu Sans", "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .header-info {
            font-size: 9px;
            color: #555;
            margin: 2px 0;
        }

        .period-badge {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 9px;
        }

        th {
            background: #2c3e50;
            color: white;
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #1a252f;
        }

        td {
            padding: 5px 4px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        tr:hover {
            background: #f1f5f9;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .status-lunas {
            background: #27ae60;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
        }

        .status-belum {
            background: #e74c3c;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
        }

        .summary {
            margin-top: 20px;
            padding: 10px;
            background: #ecf0f1;
            border-left: 4px solid #3498db;
            font-size: 9px;
        }

        .summary-title {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .no-data {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
            font-style: italic;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #999;
            padding: 10px 0;
            border-top: 1px solid #eee;
        }

        @page {
            margin: 15mm 10mm;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="period-badge">{{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d M Y') : 'Awal' }} s/d
            {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d M Y') : 'Sekarang' }}</div>
        <div class="header-info">Diexport: {{ \Carbon\Carbon::now()->format('d M Y H:i:s') }}</div>
        <div class="header-info">Oleh: {{ $user }}</div>
        @if ($filterTipe)
            <div class="header-info">Filter: <strong>{{ $filterTipe }}</strong></div>
        @endif
    </div>

    @php
        $dataCollection = $data ?? collect();
    @endphp

    @if ($dataCollection->count() > 0)
        <table>
            <thead>
                <tr>
                    <th class="text-center" width="5%">No</th>
                    <th>Perusahaan</th>
                    <th>Materi</th>
                    <th class="text-center">Periode Latihan</th>
                    <th class="text-right">Net Sales</th>
                    <th>PIC</th>
                    <th>Sales</th>
                    @if ($filterTipe !== 'Outstanding PA')
                        <th class="text-center">Tenggat</th>
                        <th class="text-center">Status</th>
                    @endif
                    @if ($filterTipe === 'Lunas')
                        <th>Jenis Potongan</th>
                        <th class="text-right">Jml Potongan</th>
                        <th class="text-right">Jml Bayar</th>
                        <th class="text-center">Tgl Bayar</th>
                    @elseif($filterTipe === null)
                        <th>Invoice</th>
                        <th>Faktur</th>
                        <th>Dokumen</th>
                        <th>Konfir CS</th>
                        <th>No. Resi</th>
                        <th>Status Resi</th>
                        <th>Konfir PIC</th>
                        <th>Pembayaran</th>
                        <th>Keterangan</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($dataCollection as $index => $item)
                    @php
                        $rkm = $item->rkm;
                        $perusahaan = optional($rkm?->perusahaan)->nama_perusahaan ?? '-';
                        $materi = optional($rkm?->materi)->nama_materi ?? '-';
                        $sales = optional($rkm?->sales)->nama_lengkap ?? '-';
                        $periode =
                            $rkm?->tanggal_awal && $rkm?->tanggal_akhir
                                ? \Carbon\Carbon::parse($rkm->tanggal_awal)->format('d M Y') .
                                    ' s/d ' .
                                    \Carbon\Carbon::parse($rkm->tanggal_akhir)->format('d M Y')
                                : '-';
                        $netSales = number_format((float) ($item->net_sales ?? 0), 0, ',', '.');
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td><strong>{{ $perusahaan }}</strong></td>
                        <td>{{ $materi }}</td>
                        <td class="text-center">{{ $periode }}</td>
                        <td class="text-right"><strong>Rp {{ $netSales }}</strong></td>
                        <td>{{ $item->pic ?? '-' }}</td>
                        <td>{{ $sales }}</td>

                        @if ($filterTipe !== 'Outstanding PA')
                            <td class="text-center">
                                {{ $item->due_date ? \Carbon\Carbon::parse($item->due_date)->format('d M Y') : '-' }}
                            </td>
                            <td class="text-center">
                                @if ($item->status_pembayaran == '1')
                                    <span class="status-lunas">Lunas</span>
                                @else
                                    <span class="status-belum">Belum</span>
                                @endif
                            </td>
                        @endif

                        @if ($filterTipe === 'Lunas')
                            <td>{{ formatJsonPotongan($item->jenis_potongan, 'jenis') }}</td>
                            <td class="text-right">{{ formatJsonPotongan($item->jumlah_potongan, 'jumlah') }}</td>
                            <td class="text-right"><strong>Rp
                                    {{ number_format((float) ($item->jumlah_pembayaran ?? 0), 0, ',', '.') }}</strong>
                            </td>
                            <td class="text-center">
                                {{ $item->tanggal_bayar ? \Carbon\Carbon::parse($item->tanggal_bayar)->format('d M Y') : '-' }}
                            </td>
                        @elseif($filterTipe === null)
                            @php $tracking = $item->tracking_outstanding; @endphp
                            <td class="text-center">{{ optional($tracking)->invoice ? 'Ada' : '-' }}</td>
                            <td class="text-center">{{ optional($tracking)->faktur_pajak ? 'Ada' : '-' }}</td>
                            <td class="text-center">{{ optional($tracking)->dokumen_tambahan ? 'Ada' : '-' }}</td>
                            <td class="text-center">{{ optional($tracking)->konfir_cs ? 'Ada' : '-' }}</td>
                            <td class="text-center">{{ optional($tracking)->no_resi ? 'Ada' : '-' }}</td>
                            <td class="text-center">{{ optional($tracking)->status_resi ?? '-' }}</td>
                            <td class="text-center">{{ optional($tracking)->konfir_pic ? 'Ada' : '-' }}</td>
                            <td class="text-center">{{ optional($tracking)->pembayaran ? 'Ada' : '-' }}</td>
                            <td>{{ optional($tracking)->keterangan_pic ?? '-' }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <div class="summary-title">RINGKASAN DATA</div>
            <div>• Total Record: <strong>{{ $dataCollection->count() }}</strong></div>
            <div>• Tipe Filter: <strong>{{ $filterTipe ?? 'Semua' }}</strong></div>
            @if ($filterTipe === 'Lunas')
                <div>• Total Pembayaran: <strong>Rp
                        {{ number_format($dataCollection->sum('jumlah_pembayaran'), 0, ',', '.') }}</strong></div>
            @endif
            <div>• Generated: {{ \Carbon\Carbon::now()->format('d M Y H:i:s') }}</div>
        </div>
    @else
        <div class="no-data">
            <p>Tidak ada data yang ditemukan untuk kriteria ini.</p>
            <p>Silakan periksa kembali filter tanggal atau tipe laporan.</p>
        </div>
    @endif

    <div class="footer">
        Halaman {PAGENO} dari {nb} • Laporan Internal • {{ config('app.name') }}
    </div>

</body>

</html>
