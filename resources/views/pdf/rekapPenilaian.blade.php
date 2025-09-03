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
        $jenisTotalTemp = [];
        $jenisEvaluatorCount = [];
        $rekapGlobal = [];
        $totalKeseluruhanGlobal = 0;
        @endphp

        @foreach ($formGroup['data']['evaluator'] as $indexEvaluator => $evaluatorItem)
        @php
        $jenis = $evaluatorItem['jenis_penilaian'];
        $jenisCounter[$jenis] = ($jenisCounter[$jenis] ?? 0) + 1;
        $nomor = $jenisCounter[$jenis];
        $totalPerEvaluator = 0;
        $nilaiList = $evaluatorItem['nilai'];
        $nilaiIndex = 0;
        @endphp

        <table border="1" cellspacing="0" cellpadding="6" width="100%">
            <thead>
                <tr style="background-color: #e0e0e0;">
                    <th colspan="6">Evaluator
                        @if ($formGroup['tipe_pdf'] === 'office')
                        {{ $evaluatorItem['nama'] }}
                        @else
                        {{ $nomor }}
                        @endif
                        - Penilaian {{ $jenis }}
                    </th>
                </tr>
                <tr style="background-color: #f5f5f5;">
                    <th>Kriteria</th>
                    <th>Sub Kriteria</th>
                    <th>Bobot</th>
                    <th>Nilai</th>
                    <th>Pesan</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($formGroup['data']['dataKriteria'] as $kriteria)
                @php $jumlahSub = count($kriteria['detailKriteria']); @endphp
                @foreach ($kriteria['detailKriteria'] as $iDetail => $detail)
                @php
                $nilaiData = $nilaiList[$nilaiIndex] ?? ['nilai' => 0, 'pesan' => '-'];
                $nilai = is_numeric($nilaiData['nilai']) ? $nilaiData['nilai'] : 0;
                $pesan = $nilaiData['pesan'] ?? '-';
                $total = ($nilai * $detail['bobot']) / 100;
                $totalPerEvaluator += $total;
                $nilaiIndex++;
                @endphp
                <tr>
                    @if ($iDetail === 0)
                    <td rowspan="{{ $jumlahSub }}">{{ $kriteria['kriteria'] }}</td>
                    @endif
                    <td>{{ $detail['sub_kriteria'] }}</td>
                    <td>{{ $detail['bobot'] }}%</td>
                    <td>{{ $nilai }}</td>
                    <td>{{ $pesan }}</td>
                    <td>{{ number_format($total, 2) }}</td>
                </tr>
                @endforeach
                @endforeach

                <tr style="background-color: #d9edf7; font-weight: bold;">
                    <td colspan="5" class="text-right"><em>Jumlah (Evaluator {{ $nomor }} - Penilaian {{ $jenis }})</em></td>
                    <td>{{ number_format($totalPerEvaluator, 2) }}</td>
                </tr>
            </tbody>
        </table>
        <br>

        @php
        $jenisTotalTemp[$jenis] = ($jenisTotalTemp[$jenis] ?? 0) + $totalPerEvaluator;
        $jenisEvaluatorCount[$jenis] = ($jenisEvaluatorCount[$jenis] ?? 0) + 1;
        @endphp
        @endforeach

        @foreach ($jenisTotalTemp as $jenis => $totalJenis)
        @php
        $jumlahEvaluator = $jenisEvaluatorCount[$jenis] ?? 1;
        $rataRataJenis = $totalJenis / $jumlahEvaluator;

        $bobotJenis = match ($jenis) {
        'General Manager' => 35,
        'Manager/Team Leader/SPV', 'Manager/SPV/Team Leader (Atasan Langsung)' => 30,
        'Rekan Kerja (Satu Divisi)' => 20,
        'Pekerja (Beda Divisi)' => 10,
        'Self Apprisial' => 5,
        default => 0,
        };

        $finalJenis = ($rataRataJenis * $bobotJenis) / 100;
        $totalKeseluruhanGlobal += $finalJenis;

        if ($totalKeseluruhanGlobal >= 90) {
        $grade = 'A'; $keterangan = 'Sangat Baik';
        } elseif ($totalKeseluruhanGlobal >= 80) {
        $grade = 'B'; $keterangan = 'Baik';
        } elseif ($totalKeseluruhanGlobal >= 70) {
        $grade = 'C'; $keterangan = 'Cukup';
        } elseif ($totalKeseluruhanGlobal >= 60) {
        $grade = 'D'; $keterangan = 'Kurang';
        } else {
        $grade = 'E'; $keterangan = 'Sangat Kurang';
        }

        $rekapGlobal[] = [
        'jenis' => $jenis,
        'rata' => $rataRataJenis,
        'final' => $finalJenis,
        'keterangan' => $keterangan,
        'grade' => $grade,
        ];
        @endphp
        @endforeach

        <table border="1" cellspacing="0" cellpadding="6" width="100%" style="margin-bottom:10px; border-collapse: collapse;">
            <thead style="background-color: #f0f0f0;">
                <tr>
                    <th>Jenis Penilaian</th>
                    <th>Rata-rata</th>
                    <th>Total Setelah Bobot</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rekapGlobal as $row)
                <tr>
                    <td class="text-center">{{ $row['jenis'] }}</td>
                    <td class="text-center">{{ number_format($row['rata'], 2) }}</td>
                    <td class="text-center">{{ number_format($row['final'], 2) }}</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="3" class="text-right"><strong>Total Keseluruhan : {{ number_format($totalKeseluruhanGlobal, 2) }}</strong></td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right"><strong>Kriteria : {{ $keterangan }}</strong></td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right"><strong>Grade : {{ $grade }}</strong></td>
                </tr>
            </tbody>
        </table>

        <div class="title" style="margin-top: 20px;">Data Jumlah Absen</div>
        <table border="1" cellspacing="0" cellpadding="6" width="50%" style="margin-bottom:20px;">
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

        <label for="catatan">Catatan :
            <br><span style="width: 200px;">{{ $formGroup['evaluated']['catatan'] }}</span></label>
    </div>
    @endforeach
</body>

</html>