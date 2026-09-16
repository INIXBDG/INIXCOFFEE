<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif !important;
            font-size: 12px;
        }

        h3, p {
            margin: 0 0 10px 0 !important;
        }

        table {
            border-collapse: collapse !important;
            width: 100% !important;
            margin-bottom: 40px !important;
            page-break-inside: auto !important;
        }

        tr {
            page-break-inside: avoid !important;
            page-break-after: auto !important;
        }

        th, td {
            border: 1px solid #ccc !important;
            padding: 6px 10px !important;
            text-align: left !important;
            vertical-align: top !important;
        }

        th {
            background-color: #f0f0f0 !important;
        }

        .highlight {
            background-color: #d1dffb !important;
            font-weight: bold !important;
        }

        .total-row {
            background-color: #e3e6f0 !important;
            font-weight: bold !important;
        }

        .summary-row {
            background: #393E46 !important;
            color: white !important;
            font-weight: bold !important;
        }

        .section {
            margin-bottom: 50px !important;
            page-break-after: always !important;
        }

        .section:last-child {
            page-break-after: auto !important;
        }

        .text-center {
            text-align: center !important;
        }

        .text-right {
            text-align: right !important;
        }

        @media print {
            @page {
                size: A4 !important;
                margin: 0 !important;
            }

            body {
                margin: 20px !important;
                padding: 10px !important;
                margin-top: 40px !important;
            }

            .no-print {
                display: none !important;
            }

            #button {
                display: none !important;
            }
        }

        @media screen {
            body {
                margin: 50px 200px !important;
            }
        }

        .button {
            border-radius: 3px !important;
            padding: 10px 25px !important;
            margin: 15px !important;
            background: linear-gradient(to right, #84d9d2, #07cdae) !important;
            border: 0 !important;
            font-size: 18px !important;
            font-weight: normal !important;
            color: white !important;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <button id="button" class="button" onclick="window.print();">Print</button>

    @foreach ($data as $formGroup)
    <div class="section">
        <h3>Rekap Penilaian - {{ $formGroup['evaluated']['nama'] ?? '-' }}</h3>
        
        @php
            $quartal = $formGroup['evaluated']['quartal'] ?? '-';
            $quartalLabel = in_array($quartal, ['Q1', 'Q2']) ? 'S1' : (in_array($quartal, ['Q3', 'Q4']) ? 'S2' : $quartal);
            
            $persentaseJenis = [
                'General Manager' => 35,
                'Manager/SPV/Team Leader (Atasan Langsung)' => 30,
                'Rekan Kerja (Satu Divisi)' => 20,
                'Pekerja (Beda Divisi)' => 10,
                'Self Apprisial' => 5
            ];

            $groupNilai = [];
            $groupPesan = [];
            foreach ($formGroup['data']['evaluator'] ?? [] as $ev) {
                $nilaiIndex = 0;
                foreach ($formGroup['data']['dataKriteria'] ?? [] as $kriteria) {
                    foreach ($kriteria['detailKriteria'] as $sub) {
                        $nilaiData = $ev['nilai'][$nilaiIndex++] ?? ['nilai' => null, 'pesan' => '-'];
                        $jenis = $ev['jenis_penilaian'];
                        $kriteriaNama = $kriteria['kriteria'];
                        $subNama = $sub['sub_kriteria'];

                        if (is_numeric($nilaiData['nilai'])) {
                            $groupNilai[$jenis][$kriteriaNama][$subNama][] = floatval($nilaiData['nilai']);
                        }

                        $pesan = trim($nilaiData['pesan'] ?? '');
                        if (!empty($pesan) && $pesan !== '-') {
                            if (!isset($groupPesan[$jenis][$kriteriaNama][$subNama])) {
                                $groupPesan[$jenis][$kriteriaNama][$subNama] = $pesan;
                            }
                        }
                    }
                }
            }

            $rata2Hasil = [];
            foreach ($groupNilai as $jenis => $kriteriaArr) {
                foreach ($kriteriaArr as $kriteria => $subArr) {
                    foreach ($subArr as $sub => $arrNilai) {
                        $rata2Hasil[$jenis][$kriteria][$sub] = array_sum($arrNilai) / count($arrNilai);
                    }
                }
            }

            $bobotMap = [];
            foreach ($formGroup['data']['dataKriteria'] ?? [] as $kriteria) {
                foreach ($kriteria['detailKriteria'] as $sub) {
                    $bobotMap[$kriteria['kriteria']][$sub['sub_kriteria']] = $sub['bobot'] ?? 0;
                }
            }

            $jenisTotalRaw = [];
            $mode = $formGroup['tipe_pdf'] ?? ($formGroup['evaluated']['mode'] ?? 'office');
        @endphp

        @if ($mode === 'office')
            @foreach ($formGroup['data']['evaluator'] ?? [] as $ev)
                @php
                    $totalSkorEvaluator = 0;
                @endphp

                <table>
                    <thead>
                        <tr>
                            <th colspan="5" style="background-color: #e0e0e0;">
                                Evaluator: {{ $ev['nama'] ?? '-' }} - Penilaian {{ $ev['jenis_penilaian'] }}
                            </th>
                        </tr>
                        <tr>
                            <th style="width: 20%;">Kriteria</th>
                            <th style="width: 40%;">Sub Kriteria</th>
                            <th style="width: 10%;">Bobot</th>
                            <th style="width: 15%;">Nilai</th>
                            <th style="width: 15%;">Skor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($formGroup['data']['dataKriteria'] ?? [] as $kriteria)
                            @php 
                                $jumlahSub = max(1, count($kriteria['detailKriteria'] ?? [])); 
                            @endphp
                            
                            @foreach ($kriteria['detailKriteria'] ?? [] as $iDetail => $sub)
                                @php
                                    $nilaiData = collect($ev['nilai'])->firstWhere('sub_kriteria', $sub['sub_kriteria']) ?? ['nilai' => null, 'pesan' => '-'];
                                    
                                    $nilaiRaw = $nilaiData['nilai'];
                                    $nilai = is_numeric($nilaiRaw) ? floatval($nilaiRaw) : 0;
                                    $skor = is_numeric($nilaiRaw) ? ($nilai * $sub['bobot']) / 100 : 0;
                                    
                                    if (is_numeric($nilaiRaw)) {
                                        $totalSkorEvaluator += $skor;
                                    }
                                    $tipeKategori = $sub['tipe_kriteria'] ?? 'number';
                                @endphp
                                
                                <tr>
                                    @if ($loop->first)
                                        <td rowspan="{{ $jumlahSub }}">{{ $kriteria['kriteria'] }}</td>
                                    @endif
                                    <td>{{ $sub['sub_kriteria'] }}</td>
                                    
                                    @if ($tipeKategori === 'textarea')
                                        <td colspan="3" class="text-center">{{ $nilaiData['pesan'] }}</td>
                                    @else
                                        <td>{{ $sub['bobot'] }}%</td>
                                        <td>{{ is_numeric($nilaiRaw) ? number_format($nilai, 2, ',', '.') : '-' }}</td>
                                        <td>{{ is_numeric($nilaiRaw) ? number_format($skor, 2, ',', '.') : '-' }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        @endforeach

                        <tr class="summary-row">
                            <td colspan="4" class="text-right">Total {{ $ev['nama'] }}</td>
                            <td class="text-center">{{ number_format($totalSkorEvaluator, 2, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>

                @php
                    $jenis = $ev['jenis_penilaian'];
                    if (!isset($jenisTotalRaw[$jenis])) $jenisTotalRaw[$jenis] = 0;
                    $jenisTotalRaw[$jenis] = max($jenisTotalRaw[$jenis], $totalSkorEvaluator);
                @endphp
            @endforeach
        @else
            @foreach ($rata2Hasil as $jenis => $kriteriaArr)
                @php $totalSkorJenis = 0; @endphp
                <table>
                    <thead>
                        <tr>
                            <th colspan="5" style="background-color: #e0e0e0;">Penilaian {{ $jenis }}</th>
                        </tr>
                        <tr>
                            <th>Kriteria</th>
                            <th>Sub Kriteria</th>
                            <th>Bobot</th>
                            <th>Nilai</th>
                            <th>Skor</th>
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
                                    $pesanTampil = $groupPesan[$jenis][$kriteria][$sub] ?? '-';
                                    
                                    $tipeKategori = 'number';
                                    foreach ($formGroup['data']['dataKriteria'] ?? [] as $k) {
                                        if ($k['kriteria'] === $kriteria) {
                                            foreach ($k['detailKriteria'] as $s) {
                                                if ($s['sub_kriteria'] === $sub) {
                                                    $tipeKategori = $s['tipe_kriteria'];
                                                    break 2;
                                                }
                                            }
                                        }
                                    }
                                @endphp
                                <tr>
                                    @if ($loop->first)
                                        <td rowspan="{{ $jumlahSub }}">{{ $kriteria }}</td>
                                    @endif
                                    <td>{{ $sub }}</td>
                                    @if ($tipeKategori === 'textarea')
                                        <td colspan="3" class="text-center">{{ $pesanTampil }}</td>
                                    @else
                                        <td>{{ $bobot }}%</td>
                                        <td>{{ number_format($rata, 2, ',', '.') }}</td>
                                        <td>{{ number_format($skor, 2, ',', '.') }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        @endforeach
                        <tr class="summary-row">
                            <td colspan="4" class="text-right">Total Penilaian {{ $jenis }}</td>
                            <td class="text-center">{{ number_format($totalSkorJenis, 2, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            @endforeach
        @endif

        @php
            $jenisTotalPost = [];
            $totalSemuaSkor = 0;
            foreach ($jenisTotalRaw as $jenis => $total) {
                $bobot = $persentaseJenis[$jenis] ?? 0;
                $jenisTotalPost[$jenis] = ($total * $bobot) / 100;
                $totalSemuaSkor += $jenisTotalPost[$jenis];
            }

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

        <table style="width: 50%;">
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
                    <td class="text-center">{{ number_format($final, 2, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr class="summary-row">
                    <td class="text-right">Total Semua Skor</td>
                    <td class="text-center">{{ number_format($totalSemuaSkor, 2, ',', '.') }}</td>
                </tr>
                <tr class="summary-row">
                    <td class="text-right">Keterangan</td>
                    <td class="text-center">{{ $keterangan }}</td>
                </tr>
                <tr class="summary-row">
                    <td class="text-right">Grade</td>
                    <td class="text-center">{{ $grade }}</td>
                </tr>
            </tbody>
        </table>

        <div class="title" style="margin-top: 20px;">
            <strong>Data Jumlah Absen</strong><br>
            Telat: {{ $formGroup['dataAbsen']['telat'] ?? 0 }} | 
            Sakit: {{ $formGroup['dataAbsen']['sakit'] ?? 0 }} | 
            Izin: {{ $formGroup['dataAbsen']['izin'] ?? 0 }}
            <br><br>
            <strong>Catatan:</strong><br>
            <span style="width: 200px; display: inline-block;">{{ $formGroup['evaluated']['catatan'] ?? '-' }}</span>
        </div>
    </div>
    @endforeach
</body>
</html>