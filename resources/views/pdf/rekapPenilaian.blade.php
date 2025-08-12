<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        h3,
        p {
            margin: 0 0 10px 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 40px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px 10px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #f0f0f0;
        }

        .highlight {
            background-color: #d1dffb;
            font-weight: bold;
        }

        .total-row {
            background-color: #e3e6f0;
            font-weight: bold;
        }

        .section {
            margin-bottom: 50px;
        }
    </style>
</head>

<body>
    @foreach ($data as $formGroup)
    <div class="section" style="margin-bottom: 40px;">
        <h3>Rekap Penilaian - {{ $formGroup['evaluated']['nama'] }}</h3>
        <p>Quartal: {{ $formGroup['evaluated']['quartal'] }} | Tahun: {{ $formGroup['evaluated']['tahun'] }}</p>

        @php
        $jenisCounter = [];
        $totalSemuaSkor = 0;
        @endphp

        @foreach ($formGroup['data']['evaluator'] as $evaluator)
        @php
        $jenis = $evaluator['jenis_penilaian'];
        $jenisCounter[$jenis] = ($jenisCounter[$jenis] ?? 0) + 1;
        $nomor = $jenisCounter[$jenis];
        $totalPerEvaluator = 0;
        $nilaiIndex = 0;
        @endphp

        <table border="1" cellspacing="0" cellpadding="6" width="100%">
            <thead>
                <tr style="background-color: #e0e0e0;">
                    <th colspan="5">Evaluator {{ $nomor }} - {{ $jenis }}</th>
                </tr>
                <tr style="background-color: #f5f5f5;">
                    <th>Kriteria</th>
                    <th>Sub Kriteria</th>
                    <th>Bobot</th>
                    <th>Nilai</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($formGroup['data']['dataKriteria'] as $kriteria)
                @php
                $jumlahSub = count($kriteria['detailKriteria']);
                @endphp

                @foreach ($kriteria['detailKriteria'] as $index => $detail)
                @php
                $nilaiObj = $evaluator['nilai'][$nilaiIndex] ?? ['nilai' => 0, 'pesan' => '-'];
                $nilai = is_numeric($nilaiObj['nilai']) ? $nilaiObj['nilai'] : 0;
                $total = ($nilai * $detail['bobot']) / 100;
                $totalPerEvaluator += $total;
                $nilaiIndex++;
                @endphp
                <tr>
                    @if ($index === 0)
                    <td rowspan="{{ $jumlahSub }}">{{ $kriteria['kriteria'] }}</td>
                    @endif
                    <td>{{ $detail['sub_kriteria'] }}</td>
                    <td>{{ $detail['bobot'] }}%</td>
                    <td>{{ $nilai }}</td>
                    <td>{{ number_format($total, 2) }}</td>
                </tr>
                @endforeach
                @endforeach

                @php
                $bobotJenis = match($jenis) {
                'General Manager' => 35,
                'Manager/Team Leader/SPV' => 30,
                'Rekan Kerja' => 20,
                'Pekerja (Beda Divisi)' => 10,
                'Self Appraisal' => 5,
                default => 0
                };
                $finalScore = ($totalPerEvaluator * $bobotJenis) / 100;
                $totalSemuaSkor += $finalScore;
                @endphp

                <tr style="background-color: #d9edf7; font-weight: bold;">
                    <td colspan="4">Total (Evaluator {{ $nomor }} - Penilaian {{ $jenis }})</td>
                    <td>{{ number_format($finalScore, 2) }}</td>
                </tr>
            </tbody>
        </table>
        <br>
        @endforeach

        @php
        if ($totalSemuaSkor >= 90) {
        $grade = 'A';
        $keterangan = 'Sangat Baik';
        } elseif ($totalSemuaSkor >= 80) {
        $grade = 'B';
        $keterangan = 'Baik';
        } elseif ($totalSemuaSkor >= 70) {
        $grade = 'C';
        $keterangan = 'Cukup';
        } elseif ($totalSemuaSkor >= 60) {
        $grade = 'D';
        $keterangan = 'Kurang';
        } else {
        $grade = 'E';
        $keterangan = 'Sangat Kurang';
        }
        @endphp

        <table border="1" cellspacing="0" cellpadding="6" width="100%" style="margin-top: 20px;">
            <tr style="background: #393E46; color: white;">
                <td colspan="3" style="text-align: right;">Total Semua Nilai</td>
                <td colspan="2" style="text-align: center;">{{ number_format($totalSemuaSkor, 2) }}</td>
            </tr>
            <tr style="background: #393E46; color: white;">
                <td colspan="3" style="text-align: right;">Kriteria</td>
                <td colspan="2" style="text-align: center;">{{ $keterangan }}</td>
            </tr>
            <tr style="background: #393E46; color: white;">
                <td colspan="3" style="text-align: right;">Grade</td>
                <td colspan="2" style="text-align: center;">{{ $grade }}</td>
            </tr>
        </table>
    </div>

    <div class="title" style="margin-top: 20px;">Data Jumlah Absen</div>
    <table border="1" cellspacing="0" cellpadding="6" width="50%" style="margin-bottom : 20px;">
        <tr>
            <td>Telat</td>
            <td>Sakit</td>
            <td>Izin</td>
        </tr>
        <tr>
            <td>{{ $formGroup['dataAbsen']['telat'] }}</td>
            <td>{{ $formGroup['dataAbsen']['sakit'] }}</td>
            <td>{{ $formGroup['dataAbsen']['izin'] }}</td>
        </tr>
    </table>

    <label for="catatan">Catatan :</label>
    <div style="width: 75%; padding: 10px;">
        {{ $formGroup['evaluated']['catatan'] }}
    </div>
    @endforeach
</body>

</html>