<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8.5px;
            color: #1d2330;
        }

        h1 {
            font-size: 15px;
            margin: 0 0 4px;
        }

        h2 {
            font-size: 12px;
            margin: 0 0 6px;
        }

        h3 {
            font-size: 10px;
            margin: 12px 0 2px;
        }

        .muted {
            color: #6b7280;
        }

        .pb {
            page-break-before: always;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 6px;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 3px 5px;
            vertical-align: top;
            word-wrap: break-word;
        }

        th {
            background: #2563eb;
            color: #fff;
            text-align: left;
        }

        .ERROR {
            color: #dc2626;
            font-weight: bold;
        }

        .WARNING {
            color: #b45309;
            font-weight: bold;
        }

        .OK {
            color: #15803d;
            font-weight: bold;
        }

        .SKIP {
            color: #6b7280;
            font-weight: bold;
        }

        .chain {
            background: #f3f4f6;
            padding: 4px 6px;
            margin-top: 3px;
        }
    </style>
</head>

<body>
    @foreach ($sections as $s)
        <div class="{{ $loop->first ? '' : 'pb' }}">
            <h1>{{ $s['label'] }}</h1>
            <div class="muted">
                Dibuat: {{ $generatedAt }} · Terakhir disinkronkan: {{ $s['generated_at'] }} · {{ $s['summary'] }}
                · Filter: {{ $s['filters'] }} · Diekspor: {{ count($s['rows']) }} temuan
            </div>

            <table>
                <colgroup>
                    <col style="width: 7%">
                    <col style="width: 12%">
                    <col style="width: 16%">
                    <col style="width: 25%">
                    <col style="width: 40%">
                </colgroup>
                <thead>
                    <tr>
                        <th>Level</th>
                        <th>{{ $s['group_label'] }}</th>
                        <th>Jenis</th>
                        <th>File:Baris</th>
                        <th>Pesan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($s['rows'] as $r)
                        <tr>
                            <td class="{{ $r['level'] }}">{{ $r['level'] }}</td>
                            <td>{{ $r['group'] }}</td>
                            <td>{{ $r['type'] }}</td>
                            <td>{{ $r['location'] }}</td>
                            <td>{{ $r['message'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="muted">Tidak ada temuan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if (!empty($s['details']))
                <h2 class="pb">Detail Fitur</h2>
                @foreach ($s['details'] as $d)
                    <h3>{{ $d['feature'] }} · {{ $d['function'] }}</h3>
                    @if ($d['url'])
                        <div class="muted">{{ $d['url'] }}</div>
                    @endif
                    @if ($d['chain'])
                        <div class="chain">{{ $d['chain'] }}</div>
                    @endif
                    <table>
                        <colgroup>
                            <col style="width: 12%">
                            <col style="width: 9%">
                            <col style="width: 24%">
                            <col style="width: 32%">
                            <col style="width: 23%">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Lapisan</th>
                                <th>Status</th>
                                <th>Pemeriksaan</th>
                                <th>Detail</th>
                                <th>File:Baris</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($d['checks'] as $c)
                                <tr>
                                    <td>{{ $c['layer'] }}</td>
                                    <td class="{{ $c['level'] }}">{{ $c['status'] }}</td>
                                    <td>{{ $c['title'] }}</td>
                                    <td>{{ $c['detail'] }}</td>
                                    <td>{{ $c['location'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endforeach
            @endif
        </div>
    @endforeach
</body>

</html>
