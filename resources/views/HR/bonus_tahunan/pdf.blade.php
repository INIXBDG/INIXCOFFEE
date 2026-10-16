<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Bonus Tahunan Desember {{ $tahun }}</title>
    <style>
        @page {
            margin: 20px 25px;
            size: a4 landscape;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9pt;
            color: #222;
            line-height: 1.3;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #1F4E79;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 14pt;
            margin: 0;
            color: #1F4E79;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 4px 0 0;
            font-size: 9pt;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            vertical-align: middle;
        }
        th {
            background-color: #1F4E79;
            color: #ffffff;
            font-size: 8pt;
            text-transform: uppercase;
            text-align: center;
        }
        tr:nth-child(even) {
            background-color: #f9fbfd;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .total-row {
            background-color: #eaeff5 !important;
            font-weight: bold;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 7.5pt;
            font-weight: bold;
        }
        .badge-tetap { background: #d1fae5; color: #065f46; }
        .badge-kontrak { background: #e0f2fe; color: #0369a1; }
        .badge-probation { background: #fef3c7; color: #92400e; }
        .badge-resign { background: #fee2e2; color: #991b1b; }
        .footer {
            margin-top: 25px;
            font-size: 8pt;
            color: #777;
        }
        .signature-table {
            width: 100%;
            margin-top: 30px;
            border: none;
        }
        .signature-table td {
            border: none;
            padding: 0;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Daftar Pencairan Bonus Tahunan</h1>
        <p><strong>INIXINDO</strong> • Periode Pencairan: <strong>Desember {{ $tahun }}</strong> (Cut-off Masa Kerja: 31 Desember {{ $tahun }})</p>
    </div>

    <!-- Summary Box -->
    <table style="width: 50%; margin-bottom: 12px; font-size: 8.5pt;">
        <tr class="total-row">
            <td style="width: 50%;">Total Karyawan Penerima:</td>
            <td class="fw-bold" style="width: 50%;">{{ $summary['total_karyawan'] ?? count($items) }} Orang</td>
        </tr>
        <tr class="total-row">
            <td>Total Pengeluaran Bonus:</td>
            <td class="fw-bold" style="color: #1F4E79;">Rp {{ number_format($summary['total_nominal_bonus'] ?? 0, 0, ',', '.') }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 130px; text-align: left;">Jabatan</th>
                <th style="text-align: left;">Nama Lengkap</th>
                <th style="width: 95px; text-align: right;">Basic Salary</th>
                <th style="width: 105px;">Rekening Maybank</th>
                <th style="width: 100px;">Status Karyawan</th>
                <th style="width: 80px;">Masa Kerja</th>
                <th style="width: 80px;">Skema</th>
                <th style="width: 105px; text-align: right;">Nominal Bonus</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totBasic = 0;
                $totBonus = 0;
            @endphp
            @forelse ($items as $idx => $item)
                @php
                    $totBasic += (float) $item['basic_salary'];
                    $totBonus += (float) $item['nominal_bonus'];

                    $badgeClass = 'badge-tetap';
                    if ($item['status_karyawan'] === 'Karyawan Kontrak') $badgeClass = 'badge-kontrak';
                    elseif ($item['status_karyawan'] === 'Karyawan Probation') $badgeClass = 'badge-probation';
                    elseif ($item['status_karyawan'] === 'Resign') $badgeClass = 'badge-resign';
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $item['jabatan'] }}</strong><br>
                        <small style="color: #666;">{{ $item['divisi'] }}</small>
                    </td>
                    <td>
                        <strong>{{ $item['nama_lengkap'] }}</strong><br>
                        <small style="color: #777;">NIP: {{ $item['nip'] }}</small>
                    </td>
                    <td class="text-end">Rp {{ number_format($item['basic_salary'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ $item['rekening_maybank'] ?: '-' }}</td>
                    <td class="text-center">
                        <span class="badge {{ $badgeClass }}">{{ $item['status_karyawan'] }}</span>
                    </td>
                    <td class="text-center">{{ $item['masa_kerja'] }}</td>
                    <td class="text-center fw-bold" style="color: #1F4E79;">{{ strtoupper($item['skema_bonus']) }}</td>
                    <td class="text-end fw-bold">Rp {{ number_format($item['nominal_bonus'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px;">Tidak ada data untuk periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-end">TOTAL:</td>
                <td class="text-end">Rp {{ number_format($totBasic, 0, ',', '.') }}</td>
                <td colspan="4"></td>
                <td class="text-end" style="color: #1F4E79; font-size: 9.5pt;">Rp {{ number_format($totBonus, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="signature-table">
        <tr>
            <td style="width: 33%;">
                Dibuat Oleh,<br><br><br><br>
                <strong>HRD Department</strong>
            </td>
            <td style="width: 33%;">
                Diperiksa Oleh,<br><br><br><br>
                <strong>Finance & Accounting</strong>
            </td>
            <td style="width: 33%;">
                Disetujui Oleh,<br><br><br><br>
                <strong>General Manager</strong>
            </td>
        </tr>
    </table>

    <div class="footer">
        Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB • Sistem INIX HR
    </div>

</body>
</html>
