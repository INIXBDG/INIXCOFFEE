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

        .section {
            margin-bottom: 50px;
        }

        .table-title {
            background-color: #e0e0e0;
            font-weight: bold;
        }

        .table-header {
            background-color: #f5f5f5;
        }

        .final-row {
            background-color: #d9edf7;
            font-weight: bold;
        }

        .summary-row {
            background: #393E46;
            color: white;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="section">
        <h3>Rekap Penilaian - {{ $evaluated['nama'] }}</h3>
        <p>Quartal: {{ $evaluated['quartal'] }} | Tahun: {{ $evaluated['tahun'] }}</p>

        @php
        $jenisCounter = [];
        $jenisTotalTemp = [];
        $jenisEvaluatorCount = [];
        @endphp

        @foreach ($evaluator as $indexEvaluator => $evaluatorItem)
        @php
        $jenis = $evaluatorItem['jenis_penilaian'];
        $jenisCounter[$jenis] = ($jenisCounter[$jenis] ?? 0) + 1;
        $nomor = $jenisCounter[$jenis];
        $totalPerEvaluator = 0;
        $nilaiList = $evaluatorItem['nilai'];
        $nilaiIndex = 0;
        @endphp

        <table>
            <thead>
                <tr class="table-title">
                    <th colspan="6">Evaluator {{ $nomor }} - {{ $evaluatorItem['nama'] }} ({{ $jenis }})</th>
                </tr>
                <tr class="table-header">
                    <th>Kriteria</th>
                    <th>Sub Kriteria</th>
                    <th>Bobot</th>
                    <th>Nilai</th>
                    <th>Pesan</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($dataKriteria as $kriteria)
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

                <tr>
                    <td colspan="5" class="text-right"><em>Jumlah (Evaluator {{ $nomor }} - Penilaian {{ $jenis }})</em></td>
                    <td>{{ number_format($totalPerEvaluator, 2) }}</td>
                </tr>
            </tbody>
        </table>

        @php
        $jenisTotalTemp[$jenis] = ($jenisTotalTemp[$jenis] ?? 0) + $totalPerEvaluator;
        $jenisEvaluatorCount[$jenis] = ($jenisEvaluatorCount[$jenis] ?? 0) + 1;
        @endphp
        @endforeach

        @php
        $totalSemuaSkor = 0;
        @endphp
        @foreach ($jenisTotalTemp as $jenis => $totalJenis)
        @php
        $jumlahEvaluator = $jenisEvaluatorCount[$jenis] ?? 1;
        $rataRataJenis = $totalJenis / $jumlahEvaluator;

        $bobotJenis = match ($jenis) {
        'General Manager' => 35,
        'Manager/Team Leader/SPV', 'Manager/SPV/Team Leader (Atasan Langsung)' => 30,
        'Rekan Kerja' => 20,
        'Pekerja (Beda Divisi)' => 10,
        'Self Appraisal' => 5,
        default => 0,
        };

        $finalJenis = ($rataRataJenis * $bobotJenis) / 100;
        $totalSemuaSkor += $finalJenis;
        @endphp
        <table style="margin-bottom:10px;">
            <tr>
                <td colspan="3" class="text-right"><strong>Rata-rata</strong></td>
                <td class="text-center"><strong>{{ number_format($rataRataJenis, 2) }}</strong></td>
                <td class="text-right"><strong>Total {{ $jenis }} Setelah Bobot</strong></td>
                <td class="text-center"><strong>{{ number_format($finalJenis, 2) }}</strong></td>
            </tr>
            @php
            if ($finalJenis >= 90) {
            $grade = 'A';
            $keterangan = 'Sangat Baik';
            } elseif ($finalJenis >= 80) {
            $grade = 'B';
            $keterangan = 'Baik';
            } elseif ($finalJenis >= 70) {
            $grade = 'C';
            $keterangan = 'Cukup';
            } elseif ($finalJenis >= 60) {
            $grade = 'D';
            $keterangan = 'Kurang';
            } else {
            $grade = 'E';
            $keterangan = 'Sangat Kurang';
            }
            @endphp
            <tr class="summary-row">
                <td colspan="5" class="text-right">Kriteria</td>
                <td colspan="1" class="text-center">{{ $keterangan }}</td>
            </tr>
            <tr class="summary-row">
                <td colspan="5" class="text-right">Grade</td>
                <td colspan="1" class="text-center">{{ $grade }}</td>
            </tr>
        </table>
        @endforeach
    </div>

    <div class="title">Data Jumlah Absen</div>
    <table style="margin-bottom: 10px;">
        <tr>
            <td>Telat</td>
            <td>Sakit</td>
            <td>Izin</td>
        </tr>
        <tr>
            <td>{{ $dataAbsen['telat'] }}</td>
            <td>{{ $dataAbsen['sakit'] }}</td>
            <td>{{ $dataAbsen['izin'] }}</td>
        </tr>
    </table>

    <label for="catatan">Catatan :</label>
    <div id="catatan" style="width: 20%;">
        {{ $evaluated['catatan'] }}
    </div>
</body>

</html>