<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Label Invoice</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            line-height: 1.4;
            color: #000000;
        }
        /* Kontainer utama dengan border statis */
        .container-box {
            border: 2px solid #000000;
            padding: 15px;
            width: 50%; /* Skala proporsional relatif terhadap ukuran A4 */
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .address-text {
            margin-bottom: 15px;
            text-align: justify;
        }
        .pic-section {
            margin-bottom: 15px;
        }
        .pic-name {
            font-weight: bold;
            font-size: 15px;
        }
        .subject-section {
            margin-top: 10px;
        }
        .subject-label {
            font-size: 14px;
        }
        .subject-value {
            font-weight: bold;
            font-size: 16px;
        }
    </style>
</head>
<body>

    <div class="container-box">
        <!-- Seksi Nama Perusahaan -->
        <div class="company-name">
            {{ $data->perusahaan ? $data->perusahaan->nama_perusahaan : '-' }}
        </div>

        <!-- Seksi Alamat -->
        <div class="address-text">
            {{ $data->alamat ? $data->alamat : '-' }}
        </div>

        <!-- Seksi PIC dan Telepon -->
        <div class="pic-section">
            <div class="pic-name">Up. {{ $data->pic ? $data->pic : '-' }}</div>
            <div>telepon: {{ $data->telepon ? $data->telepon : '-' }}</div>
        </div>

        <!-- Seksi Subjek -->
        <div class="subject-section">
            <div class="subject-label">Subject:</div>
            <div class="subject-value">Invoice</div>
        </div>
    </div>

</body>
</html>
