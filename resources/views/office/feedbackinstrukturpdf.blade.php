<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Resume Feedback Instruktur</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            line-height: 1.3;
        }

        .header {
            background-color: #1976d2;
            color: white;
            padding: 12px;
            text-align: center;
            margin-bottom: 10px;
        }

        .header h1 {
            font-size: 14px;
            font-weight: bold;
        }

        .header h2 {
            font-size: 11px;
            font-weight: normal;
            margin-top: 3px;
        }

        .section-title {
            padding: 6px 8px;
            font-size: 10px;
            font-weight: bold;
            color: white;
            margin-top: 12px;
            margin-bottom: 8px;
        }

        .section-title.terendah {
            background-color: #d32f2f;
        }

        .section-title.tertinggi {
            background-color: #388e3c;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 9px;
        }

        table th {
            background-color: #f5f5f5;
            padding: 5px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #999;
        }

        table td {
            padding: 4px 5px;
            border: 1px solid #ddd;
        }

        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .two-col {
            width: 100%;
        }

        .col-left {
            float: left;
            width: 52%;
        }

        .col-right {
            float: right;
            width: 46%;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 8px;
        }

        .badge.red {
            background-color: #ffebee;
            color: #d32f2f;
        }

        .badge.green {
            background-color: #e8f5e9;
            color: #388e3c;
        }

        .box {
            border: 1px solid #ddd;
            margin-bottom: 8px;
        }

        .box-header {
            padding: 5px 8px;
            font-size: 9px;
            font-weight: bold;
            color: white;
        }

        .box-header.red {
            background-color: #d32f2f;
        }

        .box-header.green {
            background-color: #388e3c;
        }

        .stats {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            padding: 8px;
            margin-bottom: 10px;
        }

        .stats-row {
            margin-bottom: 5px;
        }

        .stats-item {
            display: inline-block;
            width: 32%;
            margin-right: 1%;
        }

        .stats-label {
            font-size: 8px;
            color: #666;
        }

        .stats-value {
            font-size: 10px;
            font-weight: bold;
            color: #1976d2;
        }

        .footer {
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #ddd;
            font-size: 8px;
            color: #666;
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>RESUME FEEDBACK INSTRUKTUR</h1>
        <h2>{{ $rentangWaktu }}</h2>
    </div>

    {{-- STATISTICS --}}
    <div class="stats">
        <div class="stats-row">
            <div class="stats-item">
                <span class="stats-label">Total Feedback</span><br>
                <span class="stats-value">{{ $stats['total_feedback'] }}</span>
            </div>
            <div class="stats-item">
                <span class="stats-label">Total Instruktur</span><br>
                <span class="stats-value">{{ $stats['total_instruktur'] }}</span>
            </div>
            <div class="stats-item">
                <span class="stats-label">Rata-rata Nilai</span><br>
                <span class="stats-value">{{ $stats['rata_rata'] }}</span>
            </div>
        </div>
        <div class="stats-row">
            <div class="stats-item">
                <span class="stats-label">Nilai Tertinggi</span><br>
                <span class="stats-value" style="color: #388e3c">{{ $stats['nilai_tertinggi'] }}</span>
            </div>
            <div class="stats-item">
                <span class="stats-label">Nilai Terendah</span><br>
                <span class="stats-value" style="color: #d32f2f">{{ $stats['nilai_terendah'] }}</span>
            </div>
            <div class="stats-item">
                <span class="stats-label">Feedback ≥ 4.0</span><br>
                <span class="stats-value" style="color: #388e3c">{{ $stats['total_tertinggi'] }}</span>
            </div>
        </div>
    </div>

    {{-- FEEDBACK TERENDAH --}}
    <div class="section-title terendah">FEEDBACK TERENDAH ≤ 3.3</div>

    <div class="two-col clearfix">
        <div class="col-left">
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%">No</th>
                        <th style="width: 50%">Nama</th>
                        <th style="width: 20%" class="text-center">Feedback</th>
                        <th style="width: 25%">Bulan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($feedbackTerendah as $item)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $item['nama'] }}</td>
                            <td class="text-center"><span class="badge red">{{ $item['feedback'] }}</span></td>
                            <td>{{ $item['bulan'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Tidak ada data</td>
                        </tr>
                    @endforelse
                    @if ($feedbackTerendah->count() > 0)
                        <tr style="background-color: #ffebee; font-weight: bold;">
                            <td colspan="2" class="text-right">Total:</td>
                            <td colspan="2" class="text-center">{{ $feedbackTerendah->count() }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="col-right">
            <div class="box">
                <div class="box-header red">Jumlah Feedback Terendah per Instruktur</div>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 10%">No</th>
                            <th style="width: 60%">Nama</th>
                            <th style="width: 30%" class="text-center">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($summaryInstrukturTerendah as $item)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $item['nama'] }}</td>
                                <td class="text-center">{{ $item['jumlah'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">Tidak ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="box">
                <div class="box-header red">Bulan dengan Feedback Terendah</div>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 60%">Bulan</th>
                            <th style="width: 40%" class="text-center">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($summaryBulanTerendah as $item)
                            <tr>
                                <td>{{ $item['bulan'] }}</td>
                                <td class="text-center">{{ $item['jumlah'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center">Tidak ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- FEEDBACK TERTINGGI --}}
    <div class="section-title tertinggi">FEEDBACK TERTINGGI ≥ 4.00</div>

    <div class="two-col clearfix">
        <div class="col-left">
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%">No</th>
                        <th style="width: 50%">Nama</th>
                        <th style="width: 20%" class="text-center">Feedback</th>
                        <th style="width: 25%">Bulan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($feedbackTertinggi as $item)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $item['nama'] }}</td>
                            <td class="text-center"><span class="badge green">{{ $item['feedback'] }}</span></td>
                            <td>{{ $item['bulan'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Tidak ada data</td>
                        </tr>
                    @endforelse
                    @if ($feedbackTertinggi->count() > 0)
                        <tr style="background-color: #e8f5e9; font-weight: bold;">
                            <td colspan="2" class="text-right">Total:</td>
                            <td colspan="2" class="text-center">{{ $feedbackTertinggi->count() }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="col-right">
            <div class="box">
                <div class="box-header green">Jumlah Feedback Tertinggi per Instruktur</div>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 10%">No</th>
                            <th style="width: 60%">Nama</th>
                            <th style="width: 30%" class="text-center">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($summaryInstrukturTertinggi as $item)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $item['nama'] }}</td>
                                <td class="text-center">{{ $item['jumlah'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">Tidak ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="box">
                <div class="box-header green">Bulan dengan Feedback Tertinggi</div>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 60%">Bulan</th>
                            <th style="width: 40%" class="text-center">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($summaryBulanTertinggi as $item)
                            <tr>
                                <td>{{ $item['bulan'] }}</td>
                                <td class="text-center">{{ $item['jumlah'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center">Tidak ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div style="page-break-before: always;"></div>

    <div class="header">
        <h1>DETAIL FEEDBACK INSTRUKTUR</h1>
        <h2>{{ $rentangWaktu }}</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width:5%">No</th>
                <th rowspan="2" style="width:25%">Instruktur</th>
                <th colspan="2" class="text-center" style="width:50%">Informasi Kelas</th>
                <th rowspan="2" style="width:20%" class="text-center">Feedback</th>
            </tr>
            <tr>
                <th style="width:20%">Bulan</th>
                <th style="width:30%">Materi</th>
            </tr>
        </thead>

        <tbody>
            @php $no = 1; @endphp

            @forelse($detailFeedback->groupBy('nama') as $nama => $groupNama)

                @php $firstNama = true; @endphp

                @foreach($groupNama->groupBy('bulan') as $bulan => $groupBulan)

                    @php $firstBulan = true; @endphp

                    @foreach($groupBulan as $item)
                        <tr>
                            <td class="text-center">
                                {{ $firstNama && $firstBulan ? $no : '' }}
                            </td>

                            <td>
                                {{ $firstNama ? $nama : '' }}
                            </td>

                            <td>
                                {{ $firstBulan ? $bulan : '' }}
                            </td>

                            <td>{{ $item['materi'] }}</td>

                            <td class="text-center">
                                @if($item['feedback'] >= 4)
                                    <span class="badge green">{{ $item['feedback'] }}</span>
                                @elseif($item['feedback'] <= 3.3)
                                    <span class="badge red">{{ $item['feedback'] }}</span>
                                @else
                                    {{ $item['feedback'] }}
                                @endif
                            </td>
                        </tr>

                        @php
                            $firstNama = false;
                            $firstBulan = false;
                        @endphp
                    @endforeach

                @endforeach

                @php $no++; @endphp

            @empty
                <tr>
                    <td colspan="5" class="text-center">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB
    </div>
</body>

</html>