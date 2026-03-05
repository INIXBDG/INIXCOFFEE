<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Daftar Hadir Peserta Exam</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 12mm 10mm 12mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 11px;
            line-height: 1.15;
            color: #000;
        }

        .container {
            width: 100%;
            max-width: 190mm;
            /* ≈ A4 lebar efektif portrait */
            margin: 0 auto;
            padding: 0;
            box-sizing: border-box;
        }

        .logo-inixindo {
            display: block;
            margin: 0mm auto 4mm auto;
            width: 150px;
            height: auto;
        }

        .header {
            text-align: center;
            margin: 0 0 6mm 0;
        }

        .header p {
            margin: 2px 0;
            font-size: 13px;
            font-weight: bold;
        }

        table {
            width: 100%;
            max-width: 100%;
            margin: 0 auto 4mm auto;
            border-collapse: collapse;
            table-layout: fixed;
            page-break-inside: avoid;
        }

        table,
        th,
        td {
            border: 1px solid #000;
        }

        th,
        td {
            padding: 4px 5px;
            font-size: 10.5px;
            text-align: center;
            vertical-align: middle;
        }

        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .signature {
            height: 45px;
            /* ruang tanda tangan */
        }

        tr {
            height: 20px;
            /* baris lebih rapat */
        }

        .footer-section {
            margin-top: 6mm;
            text-align: center;
        }

        .vendor-logos {
            margin: 6mm 0 4mm 0;
        }

        .vendor-logos img {
            height: 60px;
            width: auto;
            margin: 3px 5px;
            vertical-align: middle;
        }

        .page-footer img {
            width: 100%;
            height: auto;
            display: block;
        }

        /* Memastikan konten tetap center secara horizontal */
        .center-wrapper {
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="container">

        <img src="{{ public_path('assets/img/inixs.png') }}" alt="Inixindo Logo" class="logo-inixindo" />

        <div class="header">
            <p>Daftar Hadir Peserta Exam</p>
            <p>Materi : {{ $exam->materi->nama_materi ?? 'N/A' }}</p>
            <p>Periode :
                {{ $tgl_exam ? \Carbon\Carbon::parse($tgl_exam)->locale('id')->translatedFormat('d F Y') : 'N/A' }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 6%">No</th>
                    <th style="width: 38%">Nama</th>
                    <th style="width: 35%">Perusahaan</th>
                    <th style="width: 21%">Tanda
                        Tangan<br>{{ $tgl_exam ? \Carbon\Carbon::parse($tgl_exam)->format('M d, y') : 'N/A' }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pesertas as $exam)
                    @foreach ($exam->registexam as $reg)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $reg->peserta?->nama ?? 'N/A' }}</td>
                            <td>{{ $reg->peserta?->perusahaan?->nama_perusahaan ?? 'N/A' }}</td>
                            <td class="signature"></td>
                        </tr>
                    @endforeach
                @endforeach
                <tr>
                    <td colspan="3" style="text-align: center; padding-top: 25px; padding-bottom: 25px;">Proctor :
                        Anggie Anggrainie</td>
                    <td style="padding-top: 25px; padding-bottom: 25px;"></td>
                </tr>
            </tbody>
        </table>

        <div class="footer-section">
            <div class="vendor-logos">
                <img src="{{ public_path('assets/img/vendor/bnsp.png') }}" alt="BNSP" />
                <img src="{{ public_path('assets/img/vendor/cisco.png') }}" alt="Cisco" />
                <img src="{{ public_path('assets/img/vendor/eccouncil.png') }}" alt="EC-Council" />
                <img src="{{ public_path('assets/img/vendor/epi.png') }}" alt="EPI" />
                <img src="{{ public_path('assets/img/vendor/isaca.jpg') }}" alt="ISACA" />
                <img src="{{ public_path('assets/img/vendor/itrain.png') }}" alt="iTrain" />
                <img src="{{ public_path('assets/img/vendor/mikrotik.png') }}" alt="MikroTik" />
                <img src="{{ public_path('assets/img/vendor/microsoft.png') }}" alt="Microsoft" />
                <img src="{{ public_path('assets/img/vendor/redhat.png') }}" alt="Red Hat" />
            </div>

            <div class="page-footer">
                <img src="{{ public_path('assets/img/footer.png') }}" alt="Footer" />
            </div>
        </div>

    </div>

</body>

</html>
