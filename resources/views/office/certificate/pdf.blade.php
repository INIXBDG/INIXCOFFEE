<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate - {{ $certificate->nomor_sertifikat }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            margin: 0;
        }

        body {
            font-family: 'Helvetica', Arial, sans-serif;
            width: 297mm;
            height: 210mm;
            position: relative;
            background: white;
            color: #333;
        }

        .certificate-container {
            width: 100%;
            height: 100%;
            position: relative;
            background-image: url('{{ asset('assets/img/Cert BG.png') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        /* Nomor Sertifikat - kanan atas */
        .certificate-number {
            position: absolute;
            top: 88px;
            right: 135px;
            font-size: 15px;
            color: #000;
            font-style: italic;
        }

        /* Nama Peserta - tengah */
        .participant-name {
            position: absolute;
            top: 280px;
            left: 52%;
            transform: translateX(-50%);
            text-align: center;
            font-size: 52px;
            font-weight: bold;
            color: #000;
            width: 85%;
        }

        /* Nama Materi/Course - tengah bawah nama */
        .course-name {
            position: absolute;
            top: 420px;
            left: 54%;
            transform: translateX(-50%);
            text-align: center;
            font-size: 36px;
            font-weight: bold;
            color: #000;
            width: 85%;
        }

        /* Period - kiri bawah (tanpa label) */
        .period-section {
            position: absolute;
            top: 495px;
            left: 240px;
            font-size: 25px;
            color: #333;
            font-style: italic;
        }

        /* Signature - kiri bawah */
        .signature-section {
            position: absolute;
            bottom: 68px;
            left: 135px;
        }

        .signature-image {
            width: 220px;
            height: 75px;
            object-fit: contain;
            margin-bottom: 0px;
        }

        .signature-line {
            border-top: 2px solid #000;
            width: 300px;
            margin-bottom: 0px;
            margin-top: 3px;
        }
    </style>
</head>

<body>
    <div class="certificate-container">
        <!-- Nomor Sertifikat -->
        <div class="certificate-number">No. {{ $certificate->nomor_sertifikat }}</div>

        <!-- Nama Peserta -->
        <div class="participant-name">{{ $certificate->nama_peserta }}</div>

        <!-- Nama Materi -->
        <div class="course-name">{{ $certificate->nama_materi }}</div>

        <!-- Period -->
        <div class="period-section">
            <span class="period-dates">
                @php
                    $dates = explode(' - ', $certificate->tanggal_pelatihan);
                    $awal = $dates[0] ?? null;
                    $akhir = $dates[1] ?? null;
                @endphp
                {{ $awal ? \Carbon\Carbon::createFromFormat('Y-m-d', $awal)->format('F d, Y') : '' }} -
                {{ $akhir ? \Carbon\Carbon::createFromFormat('Y-m-d', $akhir)->format('F d, Y') : '' }}
            </span>
        </div>

        <!-- Signature -->
        <div class="signature-section">
            @if (isset($penandatangan) && $penandatangan->ttd && Storage::exists('public/' . $penandatangan->ttd))
                <img src="{{ storage_path('app/public/' . $penandatangan->ttd) }}" class="signature-image">
            @else
                <div style="height: 75px;"></div>
            @endif
        </div>
    </div>
</body>

</html>
