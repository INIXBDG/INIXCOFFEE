<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Status Karyawan</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #5b73e8;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            color: #5b73e8;
            font-size: 16px;
        }

        .header p {
            margin: 5px 0 0;
            color: #666;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #f8f9fa;
            font-weight: 600;
            text-align: center;
            font-size: 10px;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            color: #fff;
        }

        .bg-primary {
            background-color: #5b73e8;
        }

        .bg-success {
            background-color: #28a745;
        }

        .bg-warning {
            background-color: #ffc107;
            color: #000;
        }

        .bg-danger {
            background-color: #dc3545;
        }

        .section-title {
            font-size: 13px;
            color: #5b73e8;
            margin: 20px 0 8px;
            border-left: 4px solid #5b73e8;
            padding-left: 8px;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 9px;
            color: #888;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .no-data {
            text-align: center;
            color: #888;
            padding: 20px 0;
            font-style: italic;
        }

        @page {
            margin: 15mm 10mm 15mm 10mm;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Laporan Status Karyawan</h1>
        <p>Periode:
            @if ($data['bulan'])
                {{ \Carbon\Carbon::createFromDate($data['tahun'], $data['bulan'], 1)->translatedFormat('F Y') }}
            @else
                Tahun {{ $data['tahun'] }}
            @endif
            | Dicetak: {{ now()->format('d/m/Y H:i') }}
        </p>
    </div>

    @php
        $cKontrak = count($data['kontrak']);
        $cTetap = count($data['tetap']);
        $cProb = count($data['probation']);
        $cResign = count($data['resign']);
        $totalAktif = $cKontrak + $cTetap + $cProb;
        $totalAll = $totalAktif + $cResign;
    @endphp

    <h2 class="section-title" style="margin-top: 0;">Ringkasan Distribusi</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 30%;">Status Karyawan</th>
                <th style="width: 15%;">Jumlah</th>
                <th style="width: 25%;">Persentase (Aktif)</th>
                <th style="width: 30%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Karyawan Kontrak</td>
                <td class="text-center">{{ $cKontrak }}</td>
                <td class="text-center">{{ $totalAktif > 0 ? round(($cKontrak / $totalAktif) * 100, 1) : 0 }}%</td>
                <td class="text-center"><span class="badge bg-primary">Aktif</span></td>
            </tr>
            <tr>
                <td>Karyawan Tetap</td>
                <td class="text-center">{{ $cTetap }}</td>
                <td class="text-center">{{ $totalAktif > 0 ? round(($cTetap / $totalAktif) * 100, 1) : 0 }}%</td>
                <td class="text-center"><span class="badge bg-success">Aktif</span></td>
            </tr>
            <tr>
                <td>Karyawan Probation</td>
                <td class="text-center">{{ $cProb }}</td>
                <td class="text-center">{{ $totalAktif > 0 ? round(($cProb / $totalAktif) * 100, 1) : 0 }}%</td>
                <td class="text-center"><span class="badge bg-warning">Aktif</span></td>
            </tr>
            <tr>
                <td>Karyawan Resign</td>
                <td class="text-center">{{ $cResign }}</td>
                <td class="text-center">{{ $totalAll > 0 ? round(($cResign / $totalAll) * 100, 1) : 0 }}%</td>
                <td class="text-center"><span class="badge bg-danger">Non-Aktif</span></td>
            </tr>
            <tr style="font-weight: bold; background-color: #f1f5f9;">
                <td>Total Keseluruhan</td>
                <td class="text-center">{{ $totalAll }}</td>
                <td colspan="2" class="text-center">100%</td>
            </tr>
        </tbody>
    </table>

    {{-- DETAIL PER STATUS --}}
    @foreach ([
        'kontrak' => ['label' => 'Kontrak', 'data' => $data['kontrak']],
        'tetap' => ['label' => 'Tetap', 'data' => $data['tetap']],
        'probation' => ['label' => 'Probation', 'data' => $data['probation']],
        'resign' => ['label' => 'Resign', 'data' => $data['resign']],
    ] as $key => $group)
        <h2 class="section-title">Detail Karyawan {{ $group['label'] }} ({{ count($group['data']) }} Data)</h2>
        @if (count($group['data']) > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 10%;">NIP</th>
                        <th style="width: 25%;">Nama Lengkap</th>
                        <th style="width: 15%;">Divisi</th>
                        <th style="width: 15%;">Jabatan</th>
                        <th style="width: 15%;">Tgl Masuk/Periode</th>
                        <th style="width: 15%;">{{ $key === 'resign' ? 'Tgl Resign' : 'Tgl Berakhir' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($group['data'] as $idx => $row)
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td><code>{{ $row->nip ?? '-' }}</code></td>
                            <td>{{ $row->nama_lengkap }}</td>
                            <td>{{ $row->divisi }}</td>
                            <td>{{ $row->jabatan }}</td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($row->tgl_periode)->format('d M Y') }}
                            </td>
                            <td class="text-center">
                                {{ $row->tgl_akhir ? \Carbon\Carbon::parse($row->tgl_akhir)->format('d M Y') : '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">Tidak ada data karyawan {{ $group['label'] }} pada periode ini.</div>
        @endif
    @endforeach

    <div class="footer">
        <p>Dokumen ini dibuat secara otomatis oleh Sistem HRIS. Tidak memerlukan tanda tangan basah.</p>
    </div>
</body>

</html>
