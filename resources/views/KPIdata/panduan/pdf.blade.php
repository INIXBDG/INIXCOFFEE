<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Panduan Target KPI</title>
    <style>
        @page {
            margin: 50px 40px 45px 40px;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 12px;
            line-height: 1.55;
            color: #1a1a1a;
            background: #fff;
        }

        .header {
            position: fixed;
            top: -35px;
            left: 0;
            right: 0;
            font-size: 10px;
            color: #888;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 6px;
        }

        .footer {
            position: fixed;
            bottom: -30px;
            left: 0;
            right: 0;
            font-size: 10px;
            color: #888;
            text-align: center;
            border-top: 1px solid #e0e0e0;
            padding-top: 6px;
        }

        .pagenum:before {
            content: counter(page);
        }

        h1 {
            font-size: 22px;
            margin: 20px 0 8px 0;
            color: #111;
            font-weight: bold;
        }

        h2 {
            font-size: 15px;
            margin: 0 0 6px 0;
            color: #111;
            font-weight: bold;
        }

        h3 {
            font-size: 13px;
            margin: 16px 0 8px 0;
            padding-bottom: 4px;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }

        h6 {
            font-size: 12px;
            margin: 12px 0 6px 0;
            font-weight: bold;
            color: #333;
        }

        p {
            margin: 0 0 8px 0;
        }

        ul, ol {
            margin: 0 0 10px 0;
            padding-left: 20px;
        }

        li {
            margin-bottom: 4px;
        }

        .cover-sub {
            font-size: 12px;
            color: #555;
            margin-bottom: 14px;
        }

        .meta {
            font-size: 11px;
            color: #555;
            margin-bottom: 20px;
        }

        .toc-title {
            font-size: 14px;
            font-weight: bold;
            margin: 24px 0 10px 0;
            color: #111;
        }

        .toc-grup {
            font-weight: bold;
            margin-top: 12px;
            margin-bottom: 4px;
            color: #333;
            font-size: 12px;
        }

        .toc-grup + ol {
            margin-top: 2px;
            margin-bottom: 8px;
        }

        .grup {
            background: #f0f0f0;
            color: #222;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 14px;
            border-left: 4px solid #666;
        }

        .target {
            page-break-before: always;
        }

        .judul-sub {
            color: #666;
            font-size: 11px;
            margin-bottom: 10px;
        }

        .bab-1 { color: #1a1a1a; }
        .bab-2 { color: #1a1a1a; }
        .bab-3 { color: #1a1a1a; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0 12px 0;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            vertical-align: top;
            font-size: 11px;
            text-align: left;
        }

        thead th,
        .table-light th {
            background: #f5f5f5;
            font-weight: bold;
        }

        .table-responsive {
            display: block;
        }

        .alert {
            padding: 8px 12px;
            border: 1px solid #ddd;
            margin: 10px 0;
            background: #fafafa;
            font-size: 11px;
        }

        .alert-warning {
            background: #fafafa;
            border-color: #ccc;
        }

        .alert-info {
            background: #fafafa;
            border-color: #ccc;
        }

        .alert-secondary {
            background: #f5f5f5;
            border-color: #ccc;
        }

        .alert-light {
            background: #fafafa;
            border-color: #ddd;
        }

        .alert ul,
        .alert ol {
            margin-bottom: 0;
        }

        pre {
            background: #f7f7f7;
            border: 1px solid #ddd;
            padding: 8px 10px;
            font-family: "DejaVu Sans Mono", monospace;
            font-size: 10px;
            white-space: pre-wrap;
            margin: 8px 0 10px 0;
        }

        code {
            background: #f0f0f0;
            font-family: "DejaVu Sans Mono", monospace;
            font-size: 10px;
            padding: 1px 4px;
        }

        .badge {
            padding: 2px 6px;
            font-size: 10px;
            color: #333;
            background: #e8e8e8;
            border: 1px solid #ccc;
        }

        .bg-success,
        .bg-danger,
        .bg-warning,
        .bg-info,
        .bg-secondary {
            background: #e8e8e8;
            color: #333;
        }

        .text-dark { color: #1a1a1a; }
        .text-muted { color: #666; }
        .small { font-size: 10px; }
        .text-center { text-align: center; }
        .d-block { display: block; }
        .mb-0 { margin-bottom: 0; }
        .mb-2 { margin-bottom: 6px; }
        .mb-3 { margin-bottom: 10px; }
        .mt-3 { margin-top: 10px; }
        .mt-4 { margin-top: 14px; }
        .fw-semibold { font-weight: bold; }
        .fw-bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">Panduan Target KPI</div>
    <div class="footer">Dicetak {{ $tanggal }} &nbsp;|&nbsp; Halaman <span class="pagenum"></span></div>

    <h1>Panduan Target KPI</h1>
    <div class="cover-sub">Sumber data, cara perhitungan, penanganan data kosong, dan alur kerja setiap target</div>
    <div class="meta">
        Dicetak pada {{ $tanggal }}<br>
        Jumlah panduan: <strong>{{ $total }}</strong>
    </div>

    <div class="toc-title">Daftar Isi</div>
    @foreach ($groups as $g)
        <div class="toc-grup">{{ $g['label'] }}</div>
        <ol>
            @foreach ($g['items'] as $it)
                <li>{{ $it['p']['judul'] }}</li>
            @endforeach
        </ol>
    @endforeach

    @foreach ($groups as $g)
        @foreach ($g['items'] as $it)
            @php $p = $it['p']; @endphp
            <div class="target">
                @if ($loop->first)
                    <div class="grup">Jabatan: {{ $g['label'] }}</div>
                @endif

                <h2>{{ $p['judul'] }}</h2>
                <div class="judul-sub">{{ $g['label'] }} · Panduan lengkap cara kerja target ini</div>

                <h3 class="bab-1">1. Dari Mana Datanya &amp; Bagaimana Dihitung</h3>
                {!! $p['dari_mana_data'] !!}

                <h3 class="bab-2">2. Jika Muncul Error atau Data Kosong</h3>
                {!! $p['error_kosong'] !!}

                <h3 class="bab-3">3. Alur Kerja (Step by Step)</h3>
                {!! $p['alur_bisnis'] !!}
            </div>
        @endforeach
    @endforeach
</body>
</html>