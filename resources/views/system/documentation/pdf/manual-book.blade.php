<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ $feature->document_title ?? 'Manual Book - ' . $feature->name . ' (' . $feature->document_version . ')' }}
    </title>
    <style>
        @page {
            margin: 30px 35px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #333;
            font-size: 11px;
            line-height: 1.6;
        }

        .watermark {
            position: fixed;
            top: -30px;
            left: -35px;
            width: 210mm;
            height: 297mm;
            z-index: -1000;
            pointer-events: none;
        }

        .watermark img {
            width: 100%;
            height: 100%;
        }

        .cover-page {
            page-break-after: always;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .cover-logo {
            margin-bottom: 30px;
            text-align: center;
        }

        .cover-logo img {
            width: 120px;
            height: auto;
        }

        .cover-title {
            font-size: 34px;
            color: #0F4C81;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .cover-feature-name {
            font-size: 26px;
            color: #1E40AF;
            font-weight: bold;
            padding: 16px 40px;
            border-top: 3px solid #0F4C81;
            border-bottom: 3px solid #0F4C81;
        }

        .identity-header {
            background: #0F4C81;
            color: white;
            padding: 18px 24px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .metadata-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px;
        }

        .metadata-card {
            background: #F8FAFC;
            border: 1px solid #D6E4F0;
            border-radius: 8px;
            padding: 16px;
        }

        .metadata-label {
            font-size: 10px;
            color: #64748B;
            text-transform: uppercase;
        }

        .metadata-value {
            font-size: 14.5px;
            color: #1E3A8A;
            font-weight: 600;
        }

        .section {
            margin-bottom: 28px;
        }

        .section-title {
            background: #0F4C81;
            color: white;
            padding: 13px 22px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 8px 8px 0 0;
            margin-top: 20px;
        }

        /* --- PERBAIKAN UTAMA DI SINI --- */
        .section-content {
            border: 1px solid #D6E4F0;
            border-top: none;
            padding: 24px;
            border-radius: 0 0 8px 8px;
            background: #FAFBFC;

            /* Menjaga spasi, tab, dan enter (newline) persis seperti di database */
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        /* Styling list yang sangat minimalis, hanya fallback jika ada tag <ul>/<ol> */
        .section-content ul,
        .section-content ol {
            padding-left: 20px;
            margin: 10px 0;
        }

        .section-content li {
            margin-bottom: 8px;
            line-height: 1.6;
        }

        .section-content ul {
            list-style-type: disc;
            /* Bullet point standar */
        }

        .section-content ol {
            list-style-type: decimal;
            /* Angka standar (akan ditimpa oleh angka manual di database jika berbentuk plain text) */
        }

        /* -------------------------------- */

        .section-content img {
            max-width: 100%;
            height: auto;
            margin: 10px 0;
        }

        .section-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        .section-content table th,
        .section-content table td {
            border: 1px solid #D6E4F0;
            padding: 8px;
            text-align: left;
        }

        .section-content table th {
            background: #E0ECF8;
            color: #1E3A8A;
        }

        footer {
            position: fixed;
            bottom: -12px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9.5px;
            color: #64748B;
            border-top: 2px solid #0F4C81;
            padding-top: 8px;
        }
    </style>
</head>

<body>

    <div class="watermark">
        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('assets/img/backgrounds/kops.png'))) }}"
            alt="Watermark">
    </div>

    <div class="cover-page">
        <div class="cover-logo">
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('assets/img/inixs.png'))) }}"
                alt="logo inixindo">
        </div>
        <div class="cover-title">PANDUAN PENGGUNAAN</div>
        <div class="cover-feature-name">{{ $feature->name }}</div>
    </div>

    <div class="section">
        <div class="identity-header">
            <h3>IDENTITAS DOKUMEN</h3>
            <p>Informasi lengkap mengenai fitur dan dokumentasi</p>
        </div>

        <table class="metadata-grid">
            <tr>
                <td class="metadata-card">
                    <div class="metadata-label">VERSI DOKUMEN</div>
                    <div class="metadata-value">{{ $feature->document_version }}</div>
                </td>
                <td class="metadata-card">
                    <div class="metadata-label">STATUS</div>
                    <div class="metadata-value">{{ ucfirst($feature->status ?? 'Production') }}</div>
                </td>
            </tr>
            <tr>
                <td class="metadata-card">
                    <div class="metadata-label">KATEGORI</div>
                    <div class="metadata-value">{{ $feature->category }}</div>
                </td>
            </tr>
            <tr>
                <td class="metadata-card">
                    <div class="metadata-label">TANGGAL EXPORT</div>
                    <div class="metadata-value">{{ now()->format('d F Y') }}</div>
                </td>
                <td class="metadata-card">
                    <div class="metadata-label">DEVELOPER</div>
                    <div class="metadata-value">IT Service Management</div>
                </td>
            </tr>
        </table>
    </div>

    @if ($feature->short_description)
        <div class="section">
            <div class="section-title">RINGKASAN FITUR</div>
            <div class="section-content">{!! $feature->short_description !!}</div>
        </div>
    @endif

    @if ($feature->purpose)
        <div class="section">
            <div class="section-title">TUJUAN FITUR</div>
            <div class="section-content">{!! $feature->purpose !!}</div>
        </div>
    @endif

    @if ($feature->problem_solved)
        <div class="section">
            <div class="section-title">MASALAH YANG DISELESAIKAN</div>
            <div class="section-content">{!! $feature->problem_solved !!}</div>
        </div>
    @endif

    @if ($feature->how_it_works)
        <div class="section">
            <div class="section-title">CARA PENGGUNAAN / DESKRIPSI FITUR</div>
            <div class="section-content">{!! $feature->how_it_works !!}</div>
        </div>
    @endif

    @if ($feature->user_access)
        <div class="section">
            <div class="section-title">HAK AKSES PENGGUNA</div>
            <div class="section-content">{!! $feature->user_access !!}</div>
        </div>
    @endif

    @if ($feature->codeDocumentations && $feature->codeDocumentations->count() > 0)
        <div class="section">
            <div class="section-title">DOKUMENTASI KODE / API</div>
            <div class="section-content">
                <ul>
                    @foreach ($feature->codeDocumentations as $code)
                        <li>
                            <strong>{{ $code->name ?? ($code->endpoint ?? 'Endpoint') }}</strong>
                            @if ($code->description ?? false)
                                : {{ $code->description }}
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if ($feature->childrenRecursive && $feature->childrenRecursive->count() > 0)
        <div class="section" style="page-break-before: always;">
            <div class="section-title">DAFTAR SUB FITUR</div>
            <div class="section-content">
                Dokumen ini juga mencakup {{ $feature->childrenRecursive->count() }} sub fitur berikut:
                <ul style="margin-top: 10px;">
                    @foreach ($feature->childrenRecursive as $child)
                        <li>{{ $child->name }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

        @foreach ($feature->childrenRecursive as $child)
            @include('system.documentation.pdf.feature-section', ['feature' => $child])
        @endforeach
    @endif

    <footer>
        INIXCOFFEE | {{ $feature->name }} | {{ $feature->document_version }} | Generated by Documentation System |
        Halaman
        <script type="text/php">
            if (isset($pdf)) {
                echo $PAGE_NUM . " / " . $PAGE_COUNT;
            }
        </script>
    </footer>

</body>

</html>
