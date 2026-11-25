<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9.5px; color: #111827; }
        h3 { margin: 0 0 4px; font-size: 14px; color: #1e1b4b; }
        .muted { color: #6b7280; margin-bottom: 12px; font-size: 9px; }
        h4 {
            margin: 14px 0 6px;
            padding: 5px 8px;
            background: #eef2ff;
            border-left: 3px solid #312e81;
            color: #1e1b4b;
            font-size: 11px;
        }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { border: 1px solid #cbd5e1; padding: 5px 6px; vertical-align: top; }
        th { background: #312e81; color: #ffffff; font-weight: bold; text-align: center; }
        tbody tr:nth-child(even) td { background: #f8fafc; }
        .text-center { text-align: center; }
        .text-right { text-align: right; white-space: nowrap; }
        .footer-row td { background: #eef2ff !important; font-weight: bold; text-align: right; color: #1e1b4b; }
        .empty-row td { text-align: center; font-style: italic; color: #6b7280; padding: 10px; }
    </style>
</head>
<body>
    <h3>{{ $judul }}</h3>
    <div class="muted">
        @if (!empty($periodeLabel))
            Periode: {{ $periodeLabel }} |
        @endif
        Dicetak: {{ now()->format('d-m-Y H:i') }}
    </div>

    @php
        $centerHeaders = ['No', 'Tanggal', 'Tenggat', 'No. KK', 'Kategori', 'Qty', 'Status'];
        $rightHeaders = ['Harga Satuan', 'Subtotal'];
    @endphp

    @foreach ($sections as $sec)
        <h4>{{ $sec['judul'] }}</h4>
        <table>
            <thead>
                <tr>
                    @foreach ($sec['headings'] as $h)
                        <th>{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($sec['rows'] as $r)
                    <tr>
                        @foreach ($r as $idx => $c)
                            @php
                                $head = $sec['headings'][$idx] ?? '';
                                $cls = in_array($head, $centerHeaders, true)
                                    ? 'text-center'
                                    : (in_array($head, $rightHeaders, true) ? 'text-right' : '');
                            @endphp
                            <td class="{{ $cls }}">{{ $c }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="{{ count($sec['headings']) }}">Tidak ada data.</td>
                    </tr>
                @endforelse
                @if (!empty($sec['footer']) && count($sec['rows']) > 0)
                    <tr class="footer-row">
                        <td colspan="{{ count($sec['headings']) }}">{{ $sec['footer'] }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    @endforeach
</body>
</html>
