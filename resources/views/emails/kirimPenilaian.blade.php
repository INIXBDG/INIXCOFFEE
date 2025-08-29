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

        {{-- Loop tabel per evaluator --}}
        @foreach ($evaluator as $indexEvaluator => $evaluatorItem)
        @php
        $jenis = $evaluatorItem['jenis_penilaian'];
        $jenisCounter[$jenis] = ($jenisCounter[$jenis] ?? 0) + 1;
        $nomor = $jenisCounter[$jenis];
        $totalPerEvaluator = 0;
        $nilaiList = $evaluatorItem['nilai'];
        $nilaiIndex = 0;
        @endphp

        <table border="1" cellspacing="0" cellpadding="6" width="100%" style="margin-bottom:15px;">
            <thead>
                <tr style="background:#f0f0f0;">
                    <th colspan="6">Evaluator {{ $nomor }} - Penilaian {{ $jenis }}</th>
                </tr>
                <tr style="background:#f9f9f9;">
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

                <tr style="font-weight:bold;background:#fefefe;">
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

        {{-- REKAP TOTAL HASIL PENILAIAN --}}
        @php
        $totalSemuaSkor = 0;
        @endphp

        <h4 style="margin-top:20px;">Rekap Total Hasil Penilaian</h4>
        <table border="1" cellspacing="0" cellpadding="6" width="100%" style="margin-bottom:20px;">
            <thead>
                <tr style="background:#f0f0f0;">
                    <th>Jenis Penilaian</th>
                    <th>Rata-rata</th>
                    <th>Bobot</th>
                    <th>Final</th>
                    <th>Kriteria</th>
                    <th>Grade</th>
                </tr>
            </thead>
            <tbody>
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
                <tr>
                    <td>{{ $jenis }}</td>
                    <td class="text-center">{{ number_format($rataRataJenis, 2) }}</td>
                    <td class="text-center">{{ $bobotJenis }}%</td>
                    <td class="text-center">{{ number_format($finalJenis, 2) }}</td>
                    <td class="text-center">{{ $keterangan }}</td>
                    <td class="text-center">{{ $grade }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="font-weight:bold;background:#f9f9f9;">
                    <td colspan="3" class="text-right">Total Semua Skor</td>
                    <td class="text-center">{{ number_format($totalSemuaSkor, 2) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- TABEL ABSEN --}}
    <div class="title">Data Jumlah Absen</div>
    <table border="1" cellspacing="0" cellpadding="6" width="50%" style="margin-bottom: 20px;">
        <tr style="background:#f0f0f0;">
            <th>Telat</th>
            <th>Sakit</th>
            <th>Izin</th>
        </tr>
        <tr>
            <td>{{ $dataAbsen['telat'] }}</td>
            <td>{{ $dataAbsen['sakit'] }}</td>
            <td>{{ $dataAbsen['izin'] }}</td>
        </tr>
    </table>

    {{-- CATATAN --}}
    <label for="catatan"><strong>Catatan :</strong></label>
    <div id="catatan" style="margin-top:5px;">
        {{ $evaluated['catatan'] }}
    </div>
</body>


</html>