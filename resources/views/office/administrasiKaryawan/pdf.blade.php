<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Administrasi Karyawan</title>

    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
        }

        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .periode {
            text-align: center;
            font-size: 12px;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background-color: #4F81BD;
            color: white;
            padding: 6px;
            border: 1px solid black;
            text-align: center;
        }

        td {
            padding: 5px;
            border: 1px solid black;
        }

        tr:nth-child(even) {
            background-color: #f5f5f5;
        }

        .center {
            text-align: center;
        }

        .progress-green {
            background-color: #92D050;
            text-align: center;
            font-weight: bold;
        }

        .progress-orange {
            background-color: #ffa200;
            color: white;
            text-align: center;
            font-weight: bold;
        }

        .progress-red {
            background-color: #FF0000;
            color: white;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="title">Administrasi Karyawan</div>

    {{-- KETERANGAN PERIODE --}}
    <div class="periode">
        @php
            use Carbon\Carbon;

            $text = '';

            if ($request->start_date && $request->end_date) {
                $text = 'Periode: ' .
                    Carbon::parse($request->start_date)->format('d M Y') .
                    ' - ' .
                    Carbon::parse($request->end_date)->format('d M Y');
            } elseif ($request->tahun && $request->bulan) {
                $text = 'Periode: ' . Carbon::create()->month($request->bulan)->translatedFormat('F') . ' ' . $request->tahun;
            } elseif ($request->tahun && $request->quartal) {
                $text = 'Periode: Q' . $request->quartal . ' ' . $request->tahun;
            } elseif ($request->tahun) {
                $text = 'Periode: Tahun ' . $request->tahun;
            } else {
                $text = 'Semua Data';
            }
        @endphp

        {{ $text }}
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="25%">Administrasi</th>
                <th width="15%">Deadline</th>
                <th width="15%">Selesai</th>
                <th width="10%">Status</th>
                <th width="10%">Progress</th>
                <th width="20%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $index => $item)
                @php
                    if ($item->tanggal_selesai) {
                        $diff = \Carbon\Carbon::parse($item->dateline)
                            ->diffInDays(\Carbon\Carbon::parse($item->tanggal_selesai), false);

                        if ($diff <= 0 || $item->status === 'selesai') {
                            $progress = 100;
                            $color = 'progress-green';
                        } elseif ($diff <= 3) {
                            $progress = 80;
                            $color = 'progress-orange';
                        } elseif ($diff <= 7) {
                            $progress = 60;
                            $color = 'progress-orange';
                        } else {
                            $progress = 0;
                            $color = 'progress-red';
                        }
                    } else {
                        $progress = 0;
                        $color = 'progress-red';
                    }
                @endphp

                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $item->nama_administrasi ?? '-' }}</td>

                    <td class="center">
                        {{ $item->dateline ? \Carbon\Carbon::parse($item->dateline)->format('d-M-y') : '-' }}
                    </td>

                    <td class="center">
                        {{ $item->tanggal_selesai ? \Carbon\Carbon::parse($item->tanggal_selesai)->format('d-M-y') : '-' }}
                    </td>

                    <td class="center">{{ $item->status ?? '-' }}</td>

                    <td class="{{ $color }}">
                        {{ $progress }}%
                    </td>

                    <td>{{ $item->keterangan ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>