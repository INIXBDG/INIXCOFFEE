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
            margin-bottom: 30px;
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

        .summary-row {
            background: #393E46;
            color: white;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="section">
        <h3>Rekap Penilaian - {{ $evaluated['nama'] }}</h3>
        <p>
            Semester:
            {{ in_array($evaluated['quartal'], ['Q1', 'Q2']) ? 'S1' : (in_array($evaluated['quartal'], ['Q3', 'Q4']) ? 'S2' : $evaluated['quartal']) }}
            | Tahun: {{ $evaluated['tahun'] }}
        </p>

        @php
        $persentaseJenis = [
            'General Manager' => 35,
            'Manager/SPV/Team Leader (Atasan Langsung)' => 30,
            'Rekan Kerja (Satu Divisi)' => 20,
            'Pekerja (Beda Divisi)' => 10,
            'Self Apprisial' => 5
        ];
        $groupRata2 = [];
        foreach ($evaluator as $ev) {
            $nilaiIndex = 0;
            foreach ($dataKriteria as $kriteria) {
                foreach ($kriteria['detailKriteria'] as $sub) {
                    $nilaiData = $ev['nilai'][$nilaiIndex++] ?? ['nilai' => 0, 'pesan' => '-'];
                    $nilai = is_numeric($nilaiData['nilai']) ? floatval($nilaiData['nilai']) : 0;
                    $groupRata2[$ev['jenis_penilaian']][$kriteria['kriteria']][$sub['sub_kriteria']][] = $nilai;
                }
            }
        }

        $rata2Hasil = [];
        foreach ($groupRata2 as $jenis => $kriteriaArr) {
            foreach ($kriteriaArr as $kriteria => $subArr) {
                foreach ($subArr as $sub => $arrNilai) {
                    $rata2Hasil[$jenis][$kriteria][$sub] = array_sum($arrNilai) / count($arrNilai);
                }
            }
        }

        // buat map bobot
        $bobotMap = [];
        foreach ($dataKriteria as $kriteria) {
            foreach ($kriteria['detailKriteria'] as $sub) {
                $bobotMap[$kriteria['kriteria']][$sub['sub_kriteria']] = $sub['bobot'];
            }
        }

        $jenisTotalRaw = [];
        @endphp

        {{-- TAMPILAN NON-OFFICE --}}
        @foreach ($rata2Hasil as $jenis => $kriteriaArr)
        @php $totalSkorJenis = 0; @endphp
        <table>
            <thead>
                <tr>
                    <th colspan="4">Penilaian {{ $jenis }}</th>
                </tr>
                <tr>
                    <th>Kriteria</th>
                    <th>Sub Kriteria</th>
                    <th>Bobot</th>
                    <th>Rata-rata</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($kriteriaArr as $kriteria => $subArr)
                @php $jumlahSub = count($subArr); @endphp
                @foreach ($subArr as $sub => $rata)
                @php
                    $bobot = $bobotMap[$kriteria][$sub] ?? 0;
                    $skor = ($rata * $bobot) / 100;
                    $totalSkorJenis += $skor;
                @endphp
                <tr>
                    @if ($loop->first)
                    <td rowspan="{{ $jumlahSub }}">{{ $kriteria }}</td>
                    @endif
                    <td>{{ $sub }}</td>
                    <td>{{ $bobot }}%</td>
                    <td>{{ number_format($rata, 2, ',', '.') }}</td>
                </tr>
                @endforeach
                @endforeach

                <tr class="summary-row">
                    <td colspan="3" class="text-right">Total Penilaian {{ $jenis }}</td>
                    <td>{{ number_format($totalSkorJenis, 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
        @php
            if (!isset($jenisTotalRaw[$jenis])) $jenisTotalRaw[$jenis] = 0;
            $jenisTotalRaw[$jenis] = max($jenisTotalRaw[$jenis], $totalSkorJenis);
        @endphp
        @endforeach

        @php
        $jenisTotalPost = [];
        $totalSemuaSkor = 0;
        foreach ($jenisTotalRaw as $jenis => $total) {
            $bobot = $persentaseJenis[$jenis] ?? 0;
            $jenisTotalPost[$jenis] = ($total * $bobot)/100;
            $totalSemuaSkor += $jenisTotalPost[$jenis];
        }

        if ($totalSemuaSkor >= 90) { $grade='A'; $keterangan='Sangat Baik'; }
        elseif ($totalSemuaSkor >= 80) { $grade='B'; $keterangan='Baik'; }
        elseif ($totalSemuaSkor >= 70) { $grade='C'; $keterangan='Cukup'; }
        elseif ($totalSemuaSkor >= 60) { $grade='D'; $keterangan='Kurang'; }
        else { $grade='E'; $keterangan='Sangat Kurang'; }
        @endphp

        <h4>Rekap Total Hasil Penilaian</h4>
        <table>
            <thead>
                <tr>
                    <th>Jenis Penilaian</th>
                    <th>Skor Akhir</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($jenisTotalPost as $jenis => $final)
                <tr>
                    <td>{{ $jenis }}</td>
                    <td class="text-center">{{ number_format($final,2,',','.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="summary-row">
                    <td class="text-right">Total Semua Skor</td>
                    <td class="text-center">{{ number_format($totalSemuaSkor,2,',','.') }}</td>
                </tr>
                <tr class="summary-row">
                    <td class="text-right">Keterangan</td>
                    <td class="text-center">{{ $keterangan }}</td>
                </tr>
                <tr class="summary-row">
                    <td class="text-right">Grade</td>
                    <td class="text-center">{{ $grade }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="title">Data Jumlah Absen</div>
        <table width="50%">
            <tr>
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

        <label for="catatan"><strong>Catatan :</strong></label>
        <div id="catatan" style="margin-top:5px;"> {{ $evaluated['catatan'] }} </div>
    </div>
</body>

</html>
