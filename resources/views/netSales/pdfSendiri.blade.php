<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan RKM</title>
    <style>
        @page {
            margin: 15mm;
            size: A4 portrait;
        }

        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 10pt;
            color: #2c3e50;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        .page {
            page-break-after: always;
        }

        .page:last-child {
            page-break-after: auto;
        }

        .header {
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 10px;
            margin-bottom: 15px;
            text-align: center;
        }

        .title {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .section-title {
            background: #f1f5f9;
            padding: 6px 10px;
            font-weight: bold;
            margin-top: 15px;
            border-left: 4px solid #2c3e50;
            font-size: 10.5pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        table td,
        table th {
            padding: 6px 8px;
            border: 1px solid #dee2e6;
            vertical-align: top;
        }

        .label {
            background: #f8f9fa;
            font-weight: 600;
            width: 30%;
        }

        .amount {
            text-align: right;
            font-family: 'Courier New', Courier, monospace;
        }

        .status-badge {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 9pt;
            font-weight: bold;
        }

        .status-hijau {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-merah {
            background: #ffebee;
            color: #c62828;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            color: #94a3b8;
        }

        .table-netsales th {
            background: #f8f9fa;
            font-size: 9pt;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="header">
            <div class="title">Laporan Payment Advance {{ $rkm->id }} </div>
        </div>

        <div class="section-title">Informasi Utama</div>
        <table>
            <tr>
                <td class="label">Nama Materi</td>
                <td><strong>{{ $rkm->materi->nama_materi ?? '-' }}</strong></td>
            </tr>
            <tr>
                <td class="label">Jadwal Pelaksanaan</td>
                <td>
                    {{ \Carbon\Carbon::parse($rkm->tanggal_awal)->translatedFormat('d M Y') }}
                    s/d
                    {{ \Carbon\Carbon::parse($rkm->tanggal_akhir)->translatedFormat('d M Y') }}
                </td>
            </tr>
            <tr>
                <td class="label">Pax</td>
                <td>{{ $rkm->pax }}</td>
            </tr>
            <tr>
                <td class="label">Harga Jual</td>
                <td>{{ number_format($rkm->harga_jual ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Total Jual</td>
                <td>{{ number_format($rkm->pax * $rkm->harga_jual ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Sales</td>
                <td>{{ $rkm->sales_key }}</td>
            </tr>
        </table>

        <div class="section-title">Detail Perusahaan</div>
        <table>
            <tr>
                <td class="label">Nama Perusahaan</td>
                <td>{{ $rkm->perusahaan->nama_perusahaan ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Kategori / Lokasi</td>
                <td>
                    {{ $rkm->perusahaan->kategori_perusahaan ?? '-' }} / {{ $rkm->perusahaan->lokasi ?? '-' }}
                </td>
            </tr>
            <tr>
                <td class="label">Email</td>
                <td>{{ $rkm->perusahaan->email ?? '-' }}</td>
            </tr>
        </table>

        @php
            // Ambil data pertama dari array perhitungan_net_sales
            $pa = collect($rkm->perhitunganNetSales)->first();
        @endphp

        <div class="section-title">Biaya Operasional (Advance)</div>
        @if ($pa)
            <table>
                <tr>
                    <td class="label">Transportasi</td>
                    <td class="amount">Rp {{ number_format($pa['transportasi'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Akomodasi Peserta</td>
                    <td class="amount">Rp {{ number_format($pa['akomodasi_peserta'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Akomodasi Tim</td>
                    <td class="amount">Rp {{ number_format($pa['akomodasi_tim'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Cashback</td>
                    <td class="amount">Rp {{ number_format($pa['cashback'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Fresh Money</td>
                    <td class="amount">Rp {{ number_format($pa['fresh_money'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Sewa Laptop</td>
                    <td class="amount">Rp {{ number_format($pa['sewa_laptop'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Souvenir</td>
                    <td class="amount">Rp {{ number_format($pa['souvenir'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Entertaint</td>
                    <td class="amount">Rp {{ number_format($pa['entertaint'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr style="background: #f1f5f9; font-weight: bold;">
                    <td class="label">Total Advance</td>
                    <td class="amount">
                        @php
                            $totalAdvance =
                                ($pa['transportasi'] ?? 0) +
                                ($pa['akomodasi_peserta'] ?? 0) +
                                ($pa['akomodasi_tim'] ?? 0) +
                                ($pa['cashback'] ?? 0) +
                                ($pa['fresh_money'] ?? 0) +
                                ($pa['sewa_laptop'] ?? 0) +
                                ($pa['souvenir'] ?? 0) +
                                ($pa['entertaint'] ?? 0);
                        @endphp
                        Rp {{ number_format($totalAdvance, 0, ',', '.') }}
                    </td>
                </tr>
            </table>

            <div style="margin-top: 15px;">
                <strong>Keterangan / Catatan:</strong><br>
                <p style="border: 1px solid #dee2e6; padding: 8px; background: #fff;">
                    {{ $pa['deskripsi_tambahan'] ?? '-' }}
                </p>
            </div>
        @else
            <p style="text-align: center; font-style: italic; color: #999;">Data Payment Advance tidak tersedia.</p>
        @endif

        <div class="footer">
            RKM ID {{ $rkm->id }} | Dicetak: {{ date('d/m/Y H:i') }}
        </div>
    </div>
</body>

</html>
