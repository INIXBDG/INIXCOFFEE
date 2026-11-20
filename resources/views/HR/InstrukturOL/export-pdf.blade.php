<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h3 { margin: 0 0 4px; }
        h4 { margin: 16px 0 6px; }
        .muted { color: #666; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { border: 1px solid #999; padding: 4px; text-align: left; vertical-align: top; }
        th { background: #eee; }
        .footer-row td { background: #f5f5f5; font-weight: bold; text-align: right; }
    </style>
</head>
<body>
    <h3>{{ $judul }}</h3>
    <div class="muted">Dicetak: {{ now()->format('d-m-Y H:i') }}</div>
    <table>
        <thead>
            <tr>
                @foreach ($headings as $h)
                    <th>{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $r)
                <tr>
                    @foreach ($r as $c)
                        <td>{{ $c }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($headings) }}">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if (!empty($sections))
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
                            @foreach ($r as $c)
                                <td>{{ $c }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($sec['headings']) }}">Tidak ada data.</td></tr>
                    @endforelse
                    @if (!empty($sec['footer']) && count($sec['rows']) > 0)
                        <tr class="footer-row">
                            <td colspan="{{ count($sec['headings']) }}">{{ $sec['footer'] }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        @endforeach
    @endif
</body>
</html>