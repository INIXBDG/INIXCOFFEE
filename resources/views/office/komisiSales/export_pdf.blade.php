<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Komisi Sales</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 10mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header p {
            margin: 3px 0 0;
            font-size: 10px;
            color: #555;
        }

        .info-row {
            font-size: 9px;
            margin-bottom: 6px;
        }
        .info-row strong {
            display: inline-block;
            width: 80px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 8px;
        }

        table.data-table th,
        table.data-table td {
            border: 1px solid #666;
            padding: 2px 3px;
            font-size: 7px;
            vertical-align: top;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }

        table.data-table th {
            background-color: #e8e8e8;
            font-weight: bold;
            text-align: center;
            font-size: 6.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 3px 2px;
        }

        /* Lebar kolom - total ~257mm (pas landscape A4) */
        .col-no       { width: 6mm; }
        .col-materi   { width: 38mm; }
        .col-pax      { width: 10mm; }
        .col-num      { width: 16mm; }
        .col-num-sm   { width: 14mm; }
        .col-nett     { width: 18mm; }
        .col-perusahaan { width: 38mm; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }

        .total-row {
            background-color: #d9e6f2 !important;
            font-weight: bold;
            font-size: 7.5px;
        }
        .total-row td {
            border-top: 2px solid #444;
        }

        .commission-box {
            border: 1.5px solid #333;
            padding: 6px 10px;
            margin-bottom: 12px;
            background: #f5f8fc;
            font-size: 10px;
            display: inline-block;
            width: 100%;
        }
        .commission-box .komisi-value {
            font-size: 12px;
            font-weight: bold;
        }

        .ttd-date {
            font-size: 9px;
            margin-bottom: 5px;
        }

        table.signatures {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.signatures td {
            width: 25%;
            text-align: center;
            vertical-align: top;
            font-size: 9px;
            padding: 0 5px;
        }
        .sig-space {
            height: 45px;
        }
        .sig-name {
            font-weight: bold;
            text-decoration: underline;
            font-size: 9px;
        }
        .sig-role {
            font-size: 8px;
            color: #555;
        }
        .sig-label {
            font-size: 8px;
            color: #666;
            margin-bottom: 2px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>PT. INIXINDO BANDUNG</h2>
        <p>Komisi Marketing Periode {{ $bulan_range }} {{ $tahun }}</p>
        <p class="periode-label">
            @if($quartal == 0)
                Tahunan {{ $tahun }}
            @else
                TR {{ $quartal }} {{ $tahun }}
            @endif
        </p>
    </div>

    <div class="info-row">
        <strong>Nama Sales:</strong> {{ $nama_sales }}
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-materi">Materi</th>
                <th class="col-pax">Pax</th>
                <th class="col-num">Penjualan</th>
                <th class="col-num-sm">Discount</th>
                <th class="col-num-sm">PA</th>
                <th class="col-num-sm">Cashback</th>
                <th class="col-num-sm">Uang Saku</th>
                <th class="col-num">Akomodasi</th>
                <th class="col-num">Transport</th>
                <th class="col-num-sm">Oleh-oleh</th>
                <th class="col-num-sm">Entertainment</th>
                <th class="col-num-sm">Biaya Lain</th>
                <th class="col-num-sm">Pengurangan PPH</th>
                <th class="col-num-sm">Exam</th>
                <th class="col-nett">NETT</th>
                <th class="col-perusahaan">Perusahaan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="text-left">{{ $r['materi'] ?? '-' }}</td>
                    <td class="text-center">{{ $r['pax'] ?? 0 }}</td>
                    <td class="text-right">{{ number_format($r['penjualan'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($r['discount'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($r['pa'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($r['cashback'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($r['uang_saku'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($r['akomodasi'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($r['transport'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($r['oleh_oleh'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($r['entertainment'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($r['biaya_lainnya'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($r['pengurangan_PPH'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($r['exam'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right" style="font-weight:bold;">{{ number_format($r['nett'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-left">{{ $r['perusahaan'] ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="16" class="text-center">Tidak ada data</td>
                </tr>
            @endforelse

            {{-- Total --}}
            <tr class="total-row">
                <td class="text-center" colspan="2">TOTAL</td>
                <td class="text-center">{{ $totals['pax'] ?? 0 }}</td>
                <td class="text-right">{{ number_format($totals['penjualan'] ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totals['discount'] ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totals['pa'] ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totals['cashback'] ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totals['uang_saku'] ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totals['akomodasi'] ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totals['transport'] ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totals['oleh_oleh'] ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totals['entertainment'] ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totals['biaya_lainnya'] ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totals['pengurangan_PPH'] ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totals['exam'] ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totals['nett'] ?? 0, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="commission-box">
        <table style="width:100%; border:none;">
            <tr>
                <td style="border:none; width:33%; font-size:10px;">
                    <strong>Target Penjualan {{ $tahun }}:</strong><br>
                    @if($target > 0)
                        Rp {{ number_format($target, 0, ',', '.') }}<br>
                        <span style="font-size:9px; color:#555;">
                            Pencapaian: {{ number_format($pencapaian, 2, ',', '.') }}%
                        </span>
                    @else
                        <span style="font-size:9px; color:#999;">Belum ditentukan</span>
                    @endif
                </td>
                <td style="border:none; width:34%; font-size:10px;">
                    <strong>Komisi Sales (2%):</strong><br>
                    <span class="komisi-value">Rp {{ number_format($komisi, 0, ',', '.') }}</span>
                </td>
                <td style="border:none; width:33%; font-size:9px;">
                    <strong>Terbilang:</strong><br>
                    <em>{{ $terbilang }}</em>
                </td>
            </tr>
        </table>
    </div>

    <div class="ttd-date">{{ $tanggal_ttd }}</div>

    <table class="signatures">
        <tr>
            <td>
                <div class="sig-label">Dibuat oleh:</div>
                <div class="sig-space"></div>
                <div class="sig-name">{{ $finance->nama_lengkap ?? '-' }}</div>
                <div class="sig-role">Accounting</div>
            </td>
            <td>
                <div class="sig-label">Diketahui Oleh:</div>
                <div class="sig-space"></div>
                <div class="sig-name">{{ $spv_sales->nama_lengkap ?? '-' }}</div>
                <div class="sig-role">Sales Supervisor</div>
            </td>
            <td>
                <div class="sig-label">Disetujui oleh:</div>
                <div class="sig-space"></div>
                <div class="sig-name">{{ $gm->nama_lengkap ?? '-' }}</div>
                <div class="sig-role">General Manager</div>
            </td>
            <td>
                <div class="sig-label">Diterima Oleh:</div>
                <div class="sig-space"></div>
                <div class="sig-name">{{ $nama_sales }}</div>
                <div class="sig-role">Sales & Marketing</div>
            </td>
        </tr>
    </table>

</body>
</html>