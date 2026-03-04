<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Data Peserta Pelatihan</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        .header {
            background-color: #667eea;
            color: white;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 20px;
            font-weight: bold;
            margin: 0;
        }

        .content {
            padding: 0 15px;
        }

        .info-section {
            background-color: #f8f9fa;
            border-left: 3px solid #667eea;
            padding: 15px;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #667eea;
        }

        .info-grid {
            width: 100%;
        }

        .info-row {
            width: 100%;
            margin-bottom: 10px;
        }

        .info-row::after {
            content: "";
            display: table;
            clear: both;
        }

        .info-item {
            float: left;
            width: 48%;
            margin-right: 2%;
            margin-bottom: 8px;
        }

        .info-item:nth-child(even) {
            margin-right: 0;
        }

        .info-label {
            font-size: 9px;
            text-transform: uppercase;
            color: #666;
            font-weight: bold;
            display: block;
            margin-bottom: 3px;
        }

        .info-value {
            font-size: 11px;
            color: #333;
            font-weight: normal;
            display: block;
        }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
            background-color: #e8f5e9;
            color: #388e3c;
        }

        .badge.online {
            background-color: #e8f5e9;
            color: #388e3c;
        }

        .badge.offline {
            background-color: #fff3e0;
            color: #f57c00;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        table thead {
            background-color: #667eea;
        }

        table th {
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            border: 1px solid #5568d3;
        }

        table td {
            padding: 8px;
            border: 1px solid #dee2e6;
            font-size: 10px;
            vertical-align: top;
        }

        table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .footer {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            font-size: 9px;
            color: #666;
            text-align: right;
        }

        /* Ensure proper page breaks */
        .page-break {
            page-break-after: always;
        }

        .no-break {
            page-break-inside: avoid;
        }

        /* Remove unsupported properties for DomPDF */
        .clear {
            clear: both;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Data Peserta Pelatihan</h1>
        </div>

        <div class="content">
            <div class="info-section no-break">
                <div class="section-title">Informasi Pelatihan</div>
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-item">
                            <span class="info-label">Nama Pelatihan</span>
                            <span class="info-value">{{ $rkm->materi->nama_materi }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Tanggal Pelaksanaan</span>
                            <span class="info-value">
                                {{ \Carbon\Carbon::parse($rkm->tanggal_awal)->translatedFormat('d M Y') }}
                                s/d
                                {{ \Carbon\Carbon::parse($rkm->tanggal_akhir)->translatedFormat('d M Y') }}
                            </span>
                        </div>
                    </div>
                    <div class="clear"></div>

                    <div class="info-row">
                        <div class="info-item">
                            <span class="info-label">Perusahaan</span>
                            <span class="info-value">{{ $rkm->perusahaan->nama_perusahaan }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Metode Kelas</span>
                            <span class="info-value">
                                <span class="badge {{ strtolower($rkm->metode_kelas) }}">{{ $rkm->metode_kelas }}</span>
                            </span>
                        </div>
                    </div>
                    <div class="clear"></div>

                    <div class="info-row">
                        <div class="info-item">
                            <span class="info-label">Instruktur</span>
                            <span class="info-value">{{ $rkm->instruktur->nama_lengkap }}</span>
                        </div>
                        @if ($rkm->instruktur2)
                            <div class="info-item">
                                <span class="info-label">Instruktur 2</span>
                                <span class="info-value">{{ $rkm->instruktur2->nama_lengkap }}</span>
                            </div>
                        @else
                            <div class="info-item">
                                <span class="info-label">Pax</span>
                                <span class="info-value">{{ $rkm->pax }} Peserta</span>
                            </div>
                        @endif
                    </div>
                    <div class="clear"></div>

                    @if ($rkm->instruktur2 || $rkm->asisten)
                        <div class="info-row">
                            @if ($rkm->asisten)
                                <div class="info-item">
                                    <span class="info-label">Asisten Instruktur</span>
                                    <span class="info-value">{{ $rkm->asisten->nama_lengkap }}</span>
                                </div>
                            @endif
                            @if ($rkm->instruktur2)
                                <div class="info-item">
                                    <span class="info-label">Pax</span>
                                    <span class="info-value">{{ $rkm->pax }} Peserta</span>
                                </div>
                            @endif
                        </div>
                        <div class="clear"></div>
                    @endif
                </div>
            </div>

            <div class="section-title">Daftar Peserta</div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 30%;">Nama Peserta</th>
                        <th style="width: 12%;">Jenis Kelamin</th>
                        <th style="width: 28%;">Email</th>
                        <th style="width: 25%;">Telepon</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($peserta as $item)
                        <tr>
                            <td style="text-align: center;">{{ $loop->iteration }}</td>
                            <td>{{ $item->peserta->nama }}</td>
                            <td>{{ $item->peserta->jenis_kelamin }}</td>
                            <td>{{ $item->peserta->email }}</td>
                            <td>{{ $item->peserta->no_hp }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="footer">
                <div>Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB</div>
            </div>
        </div>
    </div>
</body>

</html>
