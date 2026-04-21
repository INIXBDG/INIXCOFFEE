<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 7px;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 8px;
        }

        .header h1 {
            font-size: 12px;
        }

        .header-info {
            font-size: 7px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 2px;
            font-size: 6px;
            word-wrap: break-word;
            overflow: hidden;
        }

        th {
            background: #2c3e50;
            color: #fff;
            text-align: center;
        }

        td.text-right {
            text-align: right;
        }

        td.text-center {
            text-align: center;
        }

        .summary {
            margin-top: 10px;
            font-size: 8px;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 6px;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="period-badge">{{ $periodLabel }}</div>
        <div class="header-info">Diexport: {{ $generatedAt }}</div>
        <div class="header-info">Oleh: {{ $user }}</div>
    </div>

    @php $dataCollection = $data ?? collect(); @endphp

    @if ($dataCollection->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Perusahaan</th>
                    <th>Kelas</th>
                    <th>Sales</th>
                    <th>Tanggal</th>
                    <th>Tagihan</th>
                    <th>Jatuh Tempo</th>
                    <th>Tanggal Bayar</th>
                    <th>Nominal Pembayaran</th>
                    @foreach ($potonganTypes as $type)
                        <th>{{ $type }}</th>
                    @endforeach
                    <th>Uang Diterima</th>
                    <th>Status</th>
                    <th>Info</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($dataCollection as $index => $item)
                    @php
                        $rkm = $item->rkm;
                        $tanggalAkhir = $rkm?->tanggal_akhir;
                        $jatuhTempo = $tanggalAkhir
                            ? \Carbon\Carbon::parse($tanggalAkhir)->addMonths(6)->format('d M Y')
                            : '-';

                        $decodeJson = function ($value) {
                            if (empty($value)) {
                                return null;
                            }
                            $decoded = is_string($value) ? json_decode($value, true) : $value;
                            return is_string($decoded) ? json_decode($decoded, true) : $decoded;
                        };

                        $potonganData = [];
                        $decoded = $decodeJson($item->jumlah_potongan ?? null);

                        if (is_array($decoded)) {
                            foreach ($decoded as $p) {
                                if (!empty($p['jenis'])) {
                                    $potonganData[trim($p['jenis'])] = $p['jumlah'] ?? 0;
                                }
                            }
                        }
                    @endphp

                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ optional($rkm?->perusahaan)->nama_perusahaan ?? '-' }}</td>
                        <td>{{ optional($rkm?->materi)->nama_materi ?? '-' }}</td>
                        <td>{{ optional($rkm?->sales)->nama_lengkap ?? '-' }}</td>
                        <td>{{ $tanggalAkhir ? \Carbon\Carbon::parse($tanggalAkhir)->format('d M Y') : '-' }}</td>
                        <td class="text-right">{{ number_format(optional($rkm?->invoice)->amount ?? 0, 0, ',', '.') }}
                        </td>
                        <td>{{ $jatuhTempo }}</td>
                        <td>{{ $item->tanggal_bayar ? \Carbon\Carbon::parse($item->tanggal_bayar)->format('d M Y H:i') : '-' }}
                        </td>
                        <td class="text-right">{{ number_format(optional($rkm?->invoice)->amount ?? 0, 0, ',', '.') }}
                        </td>
                        @foreach ($potonganTypes as $type)
                            <td class="text-right">
                                {{ isset($potonganData[$type]) && $potonganData[$type] > 0
                                    ? number_format($potonganData[$type], 0, ',', '.')
                                    : '0' }}
                            </td>
                        @endforeach
                        <td class="text-right">{{ number_format($item->jumlah_pembayaran ?? 0, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $item->tanggal_bayar ? 'LUNAS' : 'BELUM BAYAR' }}</td>
                        <td class="text-right">
                            {{ number_format(optional($rkm?->invoice)->amount ?? 0, 0, ',', '.') ? 'Sesuai' : ' ' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            Total Data: {{ $dataCollection->count() }}<br>
            Total Uang Diterima: Rp {{ number_format($dataCollection->sum('jumlah_pembayaran'), 0, ',', '.') }}
        </div>
    @endif

    <div class="footer">
        Halaman {PAGENO} dari {nb}
    </div>

</body>

</html>
