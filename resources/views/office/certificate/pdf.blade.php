<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat - {{ $certificate->nomor_sertifikat ?? 'Preview' }}</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            margin: 0;
            size: A4 landscape;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            width: 297mm;
            /* A4 landscape */
            height: 210mm;
            position: relative;
            background: #fff;
        }

        .certificate-container {
            width: 100%;
            height: 100%;
            position: relative;
            background-image: url('{{ public_path('assets/img/Cert BG.png') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-color: #ffffff;
            /* fallback kalau gambar gagal */
        }

        .certificate-number {
            position: absolute;
            top: 88px;
            right: 125px;
            font-size: 15px;
            color: black;
            font-style: italic;
            font-weight: 600;
        }

        .participant-name {
            position: absolute;
            top: 290px;
            left: 53%;
            transform: translateX(-50%);
            width: 90%;
            text-align: center;
            font-size: 52px;
            font-weight: bold;
            color: black;
            line-height: 1.2;
        }

        /* Nama Pelatihan / Materi */
        .course-name {
            position: absolute;
            top: 420px;
            left: 52.8%;
            transform: translateX(-50%);
            width: 85%;
            text-align: center;
            font-size: 30px;
            font-weight: bold;
            color: black;
        }

        .period-section {
            position: absolute;
            top: 440px;
            left: 120px;
            font-size: 25px;
            color: #333;
            font-style: italic;
        }

        .signature-section {
            margin-top: 25px;
            position: absolute;
            bottom: 28px;
            left: 135px;
            text-align: center;
        }

        .signature-image {
            width: auto;
            height: 140px;
            object-fit: contain;
            margin-bottom: 8px;
        }

        .signature-line {
            width: 300px;
            border-top: 2px solid #000;
            margin-top: 5px;
        }

        .signature-name {
            margin-top: 8px;
            font-weight: bold;
            font-size: 18px;
            color: black;
        }

        .signature-title {
            font-size: 16px;
            color: #444;
        }
    </style>
</head>

<body>
    <div class="certificate-container">

        <div class="certificate-number">
            No. {{ $certificate->nomor_sertifikat }}
        </div>

        <div class="participant-name">
            {{ $certificate->nama_peserta }}
        </div>

        <div class="course-name">
            {{ $certificate->nama_materi }}
        </div>

        <div class="period-section" style="margin-top: 6%">
            Period :
            @php
                $dates = explode(' - ', $certificate->tanggal_pelatihan ?? '');
                $awal = $dates[0] ?? null;
                $akhir = $dates[1] ?? null;

                $dates2 = explode(' - ', $certificate->tanggal_pelatihan2 ?? '');
                $awal2 = $dates2[0] ?? null;
                $akhir2 = $dates2[1] ?? null;
            @endphp
            @if ($awal && $akhir)
                {{ $awal ? \Carbon\Carbon::parse($awal)->format('F d, Y') : '' }}
                -
                {{ $akhir ? \Carbon\Carbon::parse($akhir)->format('F d, Y') : '' }}
            @endif
            @if ($awal2 && $akhir2)
                <br>
                <span style="display:inline-block; margin-left: 90px;">
                    {{ \Carbon\Carbon::parse($awal2)->format('F d, Y') }}
                    -
                    {{ \Carbon\Carbon::parse($akhir2)->format('F d, Y') }}
                </span>
            @endif
        </div>

        <div class="signature-section">
            @if (!empty($penandatangan->ttd) && Storage::exists('public/ttd/' . $penandatangan->ttd))
                <img src="{{ public_path('storage/ttd/' . $penandatangan->ttd) }}" class="signature-image">
            @else
                <div style="height: 75px;"></div>
            @endif

            <div class="signature-name">
                {{ $penandatangan->nama_lengkap ?? '____________________' }}
            </div>
            <div class="signature-line"></div>
            <div class="signature-title">
                {{ $penandatangan->jabatan ?? 'Penandatangan' }}
            </div>
        </div>

    </div>
</body>

</html>
