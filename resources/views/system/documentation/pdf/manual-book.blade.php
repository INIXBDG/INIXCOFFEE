<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ $feature->document_title ?? 'Manual Book - ' . $feature->name }}</title>

    <style>
        @page {
            margin: 30px 35px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #333;
            font-size: 11px;
            line-height: 1.6;
            background: #fff;
        }

        * {
            box-sizing: border-box;
        }

        h1,
        h2,
        h3,
        h4,
        p,
        ul,
        li,
        table {
            margin: 0;
            padding: 0;
        }

        /* ================= WATERMARK ================= */
        .watermark {
            position: fixed;
            top: 40%;
            left: 10%;
            font-size: 80px;
            opacity: 0.04;
            transform: rotate(-30deg);
            color: #0F4C81;
            font-weight: bold;
            z-index: 0;
        }

        /* ================= COVER PAGE ================= */
        .cover-page {
            page-break-after: always;
            position: relative;
            height: 100%;
        }

        .cover-container {
            text-align: center;
            padding-top: 120px;
        }

        .cover-logo {
            width: 120px;
            height: 120px;
            background: #0F4C81;
            border-radius: 50%;
            margin: 0 auto 40px auto;
            line-height: 120px;
            color: white;
            font-size: 40px;
            font-weight: bold;
            overflow: hidden;
        }

        .cover-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cover-title {
            font-size: 36px;
            color: #0F4C81;
            font-weight: bold;
            margin-bottom: 10px;
            letter-spacing: 2px;
        }

        .cover-subtitle {
            font-size: 12px;
            color: #666;
            margin-bottom: 60px;
        }

        .cover-feature-name {
            font-size: 28px;
            color: #2D6AA6;
            font-weight: bold;
            margin-bottom: 80px;
            padding: 20px;
            border-top: 3px solid #0F4C81;
            border-bottom: 3px solid #0F4C81;
            display: inline-block;
        }

        .cover-info {
            margin-top: 60px;
        }

        .cover-info-row {
            display: inline-block;
            margin: 0 30px;
            text-align: center;
            vertical-align: top;
        }

        .cover-info-label {
            font-size: 10px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .cover-info-value {
            font-size: 14px;
            color: #0F4C81;
            font-weight: bold;
            margin-top: 4px;
        }

        .cover-bottom {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: #0F4C81;
        }

        .cover-bottom-secondary {
            position: absolute;
            bottom: 8px;
            left: 0;
            right: 0;
            height: 3px;
            background: #2D6AA6;
        }

        /* ================= IDENTITY PAGE ================= */
        .identity-page {
            page-break-after: always;
        }

        .identity-header {
            background: #0F4C81;
            color: white;
            padding: 20px 25px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .identity-header h2 {
            font-size: 20px;
            margin-bottom: 5px;
        }

        .identity-header p {
            color: #dbeafe;
            font-size: 12px;
        }

        .metadata-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px;
            margin-bottom: 30px;
        }

        .metadata-card {
            background: #F8FAFC;
            border: 1px solid #D6E4F0;
            border-radius: 8px;
            padding: 18px;
            vertical-align: top;
            width: 50%;
        }

        .metadata-label {
            font-size: 10px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }

        .metadata-value {
            font-size: 15px;
            color: #163A5F;
            font-weight: bold;
            word-break: break-word;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }

        .status-active {
            background: #D1FAE5;
            color: #0E9F6E;
        }

        .status-inactive {
            background: #FEE2E2;
            color: #DC2626;
        }

        .status-maintenance {
            background: #FEF3C7;
            color: #F59E0B;
        }

        .status-development {
            background: #E0E7FF;
            color: #4338CA;
        }

        .status-draft {
            background: #F3F4F6;
            color: #374151;
        }

        /* ================= TABLE OF CONTENTS ================= */
        .toc-page {
            page-break-after: always;
        }

        .toc-title {
            font-size: 22px;
            color: #0F4C81;
            font-weight: bold;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 3px solid #0F4C81;
        }

        .toc-item {
            padding: 12px 0;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }

        .toc-number {
            display: inline-block;
            width: 30px;
            height: 30px;
            background: #0F4C81;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 30px;
            font-weight: bold;
            margin-right: 15px;
            font-size: 12px;
        }

        .toc-text {
            color: #333;
            font-weight: 500;
        }

        .toc-empty {
            text-align: center;
            padding: 40px;
            color: #888;
            font-style: italic;
        }

        /* ================= SECTION STYLES ================= */
        .section-page {
            page-break-before: always;
        }

        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .section-title {
            background: #0F4C81;
            color: white;
            padding: 14px 20px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 8px 8px 0 0;
        }

        .section-title-icon {
            display: inline-block;
            width: 28px;
            height: 28px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            text-align: center;
            line-height: 28px;
            margin-right: 10px;
            font-size: 13px;
            font-weight: bold;
        }

        .section-content {
            border: 1px solid #D6E4F0;
            border-top: none;
            padding: 20px;
            background: white;
            border-radius: 0 0 8px 8px;
            text-align: justify;
        }

        .section-content p {
            margin-bottom: 10px;
        }

        .section-content p:last-child {
            margin-bottom: 0;
        }

        /* ================= FLOW DIAGRAM ================= */
        .flow-container {
            text-align: center;
            padding: 15px 0;
        }

        .flow-step {
            display: inline-block;
            background: #F8FAFC;
            border: 2px solid #0F4C81;
            border-radius: 8px;
            padding: 12px 25px;
            margin: 5px 0;
            font-weight: bold;
            color: #0F4C81;
            font-size: 12px;
            min-width: 180px;
        }

        .flow-arrow {
            display: block;
            text-align: center;
            font-size: 20px;
            color: #0F4C81;
            margin: 3px 0;
        }

        /* ================= USER ACCESS TABLE ================= */
        .access-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .access-table th {
            background: #0F4C81;
            color: white;
            padding: 12px 15px;
            text-align: left;
            font-size: 12px;
        }

        .access-table td {
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
            font-size: 11px;
        }

        .access-table tr:nth-child(even) {
            background: #F8FAFC;
        }

        .role-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
        }

        .role-admin {
            background: #DBEAFE;
            color: #1E40AF;
        }

        .role-staff {
            background: #D1FAE5;
            color: #065F46;
        }

        .role-user {
            background: #FEF3C7;
            color: #92400E;
        }

        .role-supervisor {
            background: #FCE7F3;
            color: #9D174D;
        }

        .role-manager {
            background: #E0E7FF;
            color: #4338CA;
        }

        .role-default {
            background: #F3F4F6;
            color: #374151;
        }

        /* ================= TIMELINE ================= */
        .timeline {
            padding: 10px 0;
        }

        .timeline-item {
            padding: 12px 15px 12px 50px;
            position: relative;
            border-left: 3px solid #D6E4F0;
            margin-left: 20px;
            margin-bottom: 10px;
        }

        .timeline-number {
            position: absolute;
            left: -15px;
            top: 10px;
            width: 28px;
            height: 28px;
            background: #0F4C81;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 28px;
            font-weight: bold;
            font-size: 12px;
        }

        .timeline-title {
            font-weight: bold;
            color: #163A5F;
            font-size: 12px;
            margin-bottom: 4px;
        }

        .timeline-desc {
            color: #666;
            font-size: 11px;
        }

        /* ================= INFO / WARNING / DANGER BOXES ================= */
        .info-box {
            background: #EFF6FF;
            border-left: 6px solid #2563EB;
            padding: 15px 18px;
            margin: 15px 0;
            border-radius: 0 6px 6px 0;
        }

        .info-box-title {
            font-weight: bold;
            color: #1E40AF;
            font-size: 12px;
            margin-bottom: 6px;
        }

        .info-box-content {
            color: #333;
            font-size: 11px;
        }

        .warning-box {
            background: #FFF8E1;
            border-left: 6px solid #F59E0B;
            padding: 15px 18px;
            margin: 15px 0;
            border-radius: 0 6px 6px 0;
        }

        .warning-box-title {
            font-weight: bold;
            color: #92400E;
            font-size: 12px;
            margin-bottom: 6px;
        }

        .warning-box-content {
            color: #333;
            font-size: 11px;
        }

        .danger-box {
            background: #FEF2F2;
            border-left: 6px solid #DC2626;
            padding: 15px 18px;
            margin: 15px 0;
            border-radius: 0 6px 6px 0;
        }

        .danger-box-title {
            font-weight: bold;
            color: #991B1B;
            font-size: 12px;
            margin-bottom: 6px;
        }

        .danger-box-content {
            color: #333;
            font-size: 11px;
        }

        /* ================= CHECKLIST ================= */
        .checklist {
            list-style: none;
            padding: 0;
        }

        .checklist li {
            padding: 8px 0 8px 30px;
            position: relative;
            border-bottom: 1px solid #f0f0f0;
            font-size: 11px;
        }

        .checklist li:before {
            content: "✓";
            position: absolute;
            left: 0;
            top: 8px;
            width: 20px;
            height: 20px;
            background: #0E9F6E;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 20px;
            font-size: 11px;
            font-weight: bold;
        }

        /* ================= SCREENSHOT ================= */
        .screenshot-box {
            border: 1px solid #D6E4F0;
            padding: 10px;
            background: #FAFAFA;
            border-radius: 8px;
            margin: 15px 0;
            text-align: center;
        }

        .screenshot-box img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
        }

        .screenshot-caption {
            text-align: center;
            font-size: 10px;
            color: #888;
            margin-top: 8px;
            font-style: italic;
        }

        /* ================= CONCLUSION ================= */
        .conclusion-box {
            background: linear-gradient(135deg, #F8FAFC 0%, #EFF6FF 100%);
            border: 2px solid #0F4C81;
            border-radius: 8px;
            padding: 25px;
            margin-top: 20px;
        }

        .conclusion-title {
            font-size: 16px;
            color: #0F4C81;
            font-weight: bold;
            margin-bottom: 15px;
            text-align: center;
        }

        .conclusion-content {
            text-align: justify;
            font-size: 12px;
            margin-bottom: 15px;
        }

        .target-users {
            margin-top: 15px;
        }

        .target-users-title {
            font-weight: bold;
            color: #163A5F;
            margin-bottom: 10px;
            font-size: 12px;
        }

        /* ================= FOOTER ================= */
        footer {
            position: fixed;
            bottom: -15px;
            left: 0;
            right: 0;
            text-align: center;
            color: #888;
            font-size: 10px;
            border-top: 2px solid #0F4C81;
            padding-top: 10px;
            background: white;
        }

        .footer-brand {
            font-weight: bold;
            color: #0F4C81;
        }

        .page-number:after {
            content: counter(page);
        }

        /* ================= UTILITY ================= */
        .text-center {
            text-align: center;
        }

        .mt-10 {
            margin-top: 10px;
        }

        .mt-20 {
            margin-top: 20px;
        }

        .mb-10 {
            margin-bottom: 10px;
        }

        .mb-20 {
            margin-bottom: 20px;
        }
    </style>

</head>

<body>

    @php
        $appName = $feature->app_name ?? config('app.name', 'Application');
        $companyName = $feature->company_name ?? config('app.company', $appName);
        $docTitle = $feature->document_title ?? 'PANDUAN PENGGUNAAN';
        $docSubtitle = $feature->document_subtitle ?? 'Dokumen resmi yang menjelaskan cara mengoperasikan, mengelola, dan memahami fungsi-fungsi dalam fitur. Dokumen ini diterbitkan langsung oleh tim IT Service Management INIXINDO BANDUNG pada tanggal ' . now()->format('d F Y');        $classification = $feature->classification ?? 'CONFIDENTIAL';
        $environment = $feature->environment ?? 'Production';
        $version = $feature->version ?? '1.0';
        $developer = $feature->developer ?? 'IT Service Management';

        $renderText = function ($data) {
            if (empty($data)) {
                return '';
            }
            return nl2br(e($data));
        };

        $parseSteps = function ($data) {
            if (empty($data)) {
                return [];
            }
            if (is_array($data)) {
                return array_values($data);
            }
            if (is_string($data)) {
                $decoded = json_decode($data, true);
                if (is_array($decoded)) {
                    return array_values($decoded);
                }
                return array_values(array_filter(array_map('trim', explode("\n", $data)), fn($v) => $v !== ''));
            }
            return [];
        };

        // ----- Helper: parse user_access ke array terstruktur -----
        $parseAccess = function ($data) {
            if (empty($data)) {
                return [];
            }
            if (is_array($data)) {
                // Array of objects/arrays
                if (isset($data[0]) && is_array($data[0])) {
                    return $data;
                }
                // Associative: role => permission
                $result = [];
                foreach ($data as $role => $perm) {
                    $result[] = [
                        'role' => is_string($role) ? $role : 'User',
                        'permission' => is_string($perm) ? $perm : (is_array($perm) ? implode(', ', $perm) : ''),
                        'description' => '',
                    ];
                }
                return $result;
            }
            if (is_string($data)) {
                $decoded = json_decode($data, true);
                if (is_array($decoded)) {
                    if (isset($decoded[0]) && is_array($decoded[0])) {
                        return $decoded;
                    }
                    $result = [];
                    foreach ($decoded as $role => $perm) {
                        $result[] = [
                            'role' => is_string($role) ? $role : 'User',
                            'permission' => is_string($perm) ? $perm : '',
                            'description' => '',
                        ];
                    }
                    return $result;
                }
            }
            return [];
        };

        // ----- Helper: role badge class -----
        $roleBadgeClass = function ($role) {
            $r = strtolower((string) $role);
            if (strpos($r, 'admin') !== false) {
                return 'role-admin';
            }
            if (strpos($r, 'staff') !== false || strpos($r, 'operator') !== false) {
                return 'role-staff';
            }
            if (strpos($r, 'supervisor') !== false || strpos($r, 'spv') !== false) {
                return 'role-supervisor';
            }
            if (strpos($r, 'manager') !== false || strpos($r, 'mgr') !== false) {
                return 'role-manager';
            }
            if (strpos($r, 'user') !== false) {
                return 'role-user';
            }
            return 'role-default';
        };

        // ----- Parse data dari DB -----
        $parsedBusinessFlow = $parseSteps($feature->business_flow ?? null);
        $parsedUserFlow = $parseSteps($feature->user_flow ?? null);
        $parsedAccess = $parseAccess($feature->user_access ?? null);
        $parsedHowItWorks = $parseSteps($feature->how_it_works ?? null);
        $parsedTargetUsers = $parseSteps($feature->target_users ?? null);
        $parsedScreenshots = $parseSteps($feature->screenshots ?? null);
        $parsedNotes = $parseSteps($feature->notes ?? null);
        $parsedWarnings = $parseSteps($feature->warnings ?? null);

        // ----- Logo: gunakan gambar dari DB, atau inisial dari nama company -----
        $logoInitial = mb_strtoupper(mb_substr($companyName, 0, 1));

        // ----- Build daftar section dinamis (hanya yang datanya ada) -----
        $sections = [];
        $num = 1;

        if (!empty($feature->purpose)) {
            $sections[] = [
                'num' => $num++,
                'key' => 'purpose',
                'title' => $feature->purpose_title ?? 'TUJUAN PEMBUATAN',
            ];
        }
        if (!empty($feature->background)) {
            $sections[] = [
                'num' => $num++,
                'key' => 'background',
                'title' => $feature->background_title ?? 'LATAR BELAKANG',
            ];
        }
        if (!empty($feature->problem_solved)) {
            $sections[] = [
                'num' => $num++,
                'key' => 'problem_solved',
                'title' => $feature->problem_solved_title ?? 'MASALAH YANG DISELESAIKAN',
            ];
        }
        if (count($parsedBusinessFlow) > 0) {
            $sections[] = [
                'num' => $num++,
                'key' => 'business_flow',
                'title' => $feature->business_flow_title ?? 'BUSINESS FLOW',
            ];
        }
        if (count($parsedUserFlow) > 0) {
            $sections[] = ['num' => $num++, 'key' => 'user_flow', 'title' => $feature->user_flow_title ?? 'USER FLOW'];
        }
        if (count($parsedAccess) > 0 || !empty($feature->user_access_notes)) {
            $sections[] = [
                'num' => $num++,
                'key' => 'user_access',
                'title' => $feature->user_access_title ?? 'HAK AKSES PENGGUNA',
            ];
        }
        if (count($parsedHowItWorks) > 0) {
            $sections[] = [
                'num' => $num++,
                'key' => 'how_it_works',
                'title' => $feature->how_it_works_title ?? 'CARA PENGGUNAAN',
            ];
        }
        if (count($parsedScreenshots) > 0) {
            $sections[] = [
                'num' => $num++,
                'key' => 'screenshots',
                'title' => $feature->screenshots_title ?? 'SCREENSHOT',
            ];
        }
        if (count($parsedNotes) > 0 || count($parsedWarnings) > 0) {
            $sections[] = ['num' => $num++, 'key' => 'notes', 'title' => $feature->notes_title ?? 'CATATAN PENTING'];
        }
        // Kesimpulan: tampilkan jika ada data conclusion ATAU target_users
        if (!empty($feature->conclusion) || count($parsedTargetUsers) > 0) {
            $sections[] = [
                'num' => $num++,
                'key' => 'conclusion',
                'title' => $feature->conclusion_title ?? 'KESIMPULAN',
            ];
        }
    @endphp

    <div class="watermark">{{ $classification }}</div>

    <!-- ================= COVER PAGE ================= -->
    <div class="cover-page">
        <div class="cover-container">
            <div class="cover-logo">
                <img src="{{ public_path('assets/img/bgsigns.png') }}" alt="">
            </div>
            <div class="cover-title">{{ $docTitle }}</div>
            <div class="cover-subtitle">{{ $docSubtitle }}</div>
            <div class="cover-feature-name">{{ $feature->name }}</div>
        </div>
        <div class="cover-bottom-secondary"></div>
        <div class="cover-bottom"></div>
    </div>

    <div class="identity-page">
        <div class="identity-header">
            <h2>{{ $feature->identity_title ?? 'IDENTITAS DOKUMEN' }}</h2>
            <p>{{ $feature->identity_subtitle ?? 'Informasi lengkap mengenai fitur dan dokumentasi' }}</p>
        </div>

        <table class="metadata-grid">
            <tr>
                <td class="metadata-card">
                    <div class="metadata-label">{{ $feature->label_name ?? 'Nama Fitur' }}</div>
                    <div class="metadata-value">{{ $feature->name }}</div>
                </td>
                <td class="metadata-card">
                    <div class="metadata-label">{{ $feature->label_status ?? 'Status' }}</div>
                    <div class="metadata-value">
                        <span class="status-badge status-{{ strtolower($feature->status ?? 'active') }}">
                            {{ ucfirst($feature->status ?? 'Active') }}
                        </span>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="metadata-card">
                    <div class="metadata-label">{{ $feature->label_category ?? 'Kategori' }}</div>
                    <div class="metadata-value">{{ $feature->category ?? '—' }}</div>
                </td>
                <td class="metadata-card">
                    <div class="metadata-label">{{ $feature->label_version ?? 'Versi Dokumen' }}</div>
                    <div class="metadata-value">{{ $version }}</div>
                </td>
            </tr>
            <tr>
                <td class="metadata-card">
                    <div class="metadata-label">{{ $feature->label_export_date ?? 'Tanggal Export' }}</div>
                    <div class="metadata-value">{{ now()->format('d F Y') }}</div>
                </td>
                <td class="metadata-card">
                    <div class="metadata-label">{{ $feature->label_developer ?? 'Developer' }}</div>
                    <div class="metadata-value">{{ $developer }}</div>
                </td>
            </tr>
        </table>

        @if (!empty($feature->short_description))
            <div class="section">
                <div class="section-title">
                    <span class="section-title-icon">📋</span>
                    {{ $feature->summary_title ?? 'RINGKASAN FITUR' }}
                </div>
                <div class="section-content">
                    <p>{{ $feature->short_description }}</p>
                </div>
            </div>
        @endif
    </div>

    <!-- ================= TABLE OF CONTENTS ================= -->
    <div class="toc-page">
        <div class="toc-title">{{ $feature->toc_title ?? 'DAFTAR ISI' }}</div>

        @if (count($sections) > 0)
            @foreach ($sections as $section)
                <div class="toc-item">
                    <span class="toc-number">{{ $section['num'] }}</span>
                    <span class="toc-text">{{ $section['title'] }}</span>
                </div>
            @endforeach
        @else
            <div class="toc-empty">
                {{ $feature->toc_empty_message ?? 'Belum ada konten yang tersedia untuk fitur ini.' }}
            </div>
        @endif
    </div>

    <!-- ================= MAIN CONTENT (DYNAMIC PER SECTION) ================= -->

    @foreach ($sections as $section)
        <div class="section-page">
            <div class="section">

                @switch($section['key'])
                    {{-- ============ TUJUAN PEMBUATAN ============ --}}
                    @case('purpose')
                        <div class="section-title">
                            <span class="section-title-icon">{{ $section['num'] }}</span>
                            {{ $section['title'] }}
                        </div>
                        <div class="section-content">
                            <p>{!! $renderText($feature->purpose) !!}</p>
                        </div>
                    @break

                    {{-- ============ LATAR BELAKANG ============ --}}
                    @case('background')
                        <div class="section-title">
                            <span class="section-title-icon">{{ $section['num'] }}</span>
                            {{ $section['title'] }}
                        </div>
                        <div class="section-content">
                            <p>{!! $renderText($feature->background) !!}</p>
                        </div>
                    @break

                    {{-- ============ MASALAH YANG DISELESAIKAN ============ --}}
                    @case('problem_solved')
                        <div class="section-title">
                            <span class="section-title-icon">{{ $section['num'] }}</span>
                            {{ $section['title'] }}
                        </div>
                        <div class="section-content">
                            <p>{!! $renderText($feature->problem_solved) !!}</p>
                        </div>
                    @break

                    {{-- ============ BUSINESS FLOW ============ --}}
                    @case('business_flow')
                        <div class="section-title">
                            <span class="section-title-icon">{{ $section['num'] }}</span>
                            {{ $section['title'] }}
                        </div>
                        <div class="section-content">
                            <div class="flow-container">
                                @foreach ($parsedBusinessFlow as $step)
                                    <div class="flow-step">{{ $step }}</div>
                                    @if (!$loop->last)
                                        <div class="flow-arrow">↓</div>
                                    @endif
                                @endforeach
                            </div>

                            @if (!empty($feature->business_flow_info))
                                <div class="info-box mt-20">
                                    <div class="info-box-title">ℹ {{ $feature->business_flow_info_title ?? 'INFORMASI' }}</div>
                                    <div class="info-box-content">{!! $renderText($feature->business_flow_info) !!}</div>
                                </div>
                            @endif
                        </div>
                    @break

                    {{-- ============ USER FLOW ============ --}}
                    @case('user_flow')
                        <div class="section-title">
                            <span class="section-title-icon">{{ $section['num'] }}</span>
                            {{ $section['title'] }}
                        </div>
                        <div class="section-content">
                            <div class="flow-container">
                                @foreach ($parsedUserFlow as $step)
                                    <div class="flow-step">{{ $step }}</div>
                                    @if (!$loop->last)
                                        <div class="flow-arrow">↓</div>
                                    @endif
                                @endforeach
                            </div>

                            @if (!empty($feature->user_flow_info))
                                <div class="info-box mt-20">
                                    <div class="info-box-title">ℹ {{ $feature->user_flow_info_title ?? 'INFORMASI' }}</div>
                                    <div class="info-box-content">{!! $renderText($feature->user_flow_info) !!}</div>
                                </div>
                            @endif
                        </div>
                    @break

                    {{-- ============ HAK AKSES PENGGUNA ============ --}}
                    @case('user_access')
                        <div class="section-title">
                            <span class="section-title-icon">{{ $section['num'] }}</span>
                            {{ $section['title'] }}
                        </div>
                        <div class="section-content">
                            @if (count($parsedAccess) > 0)
                                <table class="access-table">
                                    <thead>
                                        <tr>
                                            <th>{{ $feature->access_col_no ?? 'No' }}</th>
                                            <th>{{ $feature->access_col_role ?? 'Role' }}</th>
                                            <th>{{ $feature->access_col_permission ?? 'Permission' }}</th>
                                            @if (collect($parsedAccess)->pluck('description')->filter()->isNotEmpty())
                                                <th>{{ $feature->access_col_description ?? 'Keterangan' }}</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($parsedAccess as $idx => $access)
                                            <tr>
                                                <td>{{ $idx + 1 }}</td>
                                                <td>
                                                    <span class="role-badge {{ $roleBadgeClass($access['role'] ?? '') }}">
                                                        {{ $access['role'] ?? '—' }}
                                                    </span>
                                                </td>
                                                <td>{{ $access['permission'] ?? '—' }}</td>
                                                @if (collect($parsedAccess)->pluck('description')->filter()->isNotEmpty())
                                                    <td>{{ $access['description'] ?? '—' }}</td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif

                            @if (!empty($feature->user_access_notes))
                                <div class="info-box mt-20">
                                    <div class="info-box-title">ℹ
                                        {{ $feature->user_access_notes_title ?? 'INFORMASI TAMBAHAN' }}</div>
                                    <div class="info-box-content">{!! $renderText($feature->user_access_notes) !!}</div>
                                </div>
                            @endif
                        </div>
                    @break

                    {{-- ============ CARA PENGGUNAAN ============ --}}
                    @case('how_it_works')
                        <div class="section-title">
                            <span class="section-title-icon">{{ $section['num'] }}</span>
                            {{ $section['title'] }}
                        </div>
                        <div class="section-content">
                            <div class="timeline">
                                @foreach ($parsedHowItWorks as $idx => $step)
                                    @php
                                        $parts = is_string($step) ? explode('|', $step, 2) : [$step, ''];
                                        $title = trim($parts[0]);
                                        $desc = isset($parts[1]) ? trim($parts[1]) : '';
                                    @endphp
                                    <div class="timeline-item">
                                        <div class="timeline-number">{{ $idx + 1 }}</div>
                                        <div class="timeline-title">{{ $title }}</div>
                                        @if ($desc)
                                            <div class="timeline-desc">{{ $desc }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            @if (!empty($feature->how_it_works_notes))
                                <div class="warning-box mt-20">
                                    <div class="warning-box-title">⚠ {{ $feature->how_it_works_notes_title ?? 'PERHATIAN' }}
                                    </div>
                                    <div class="warning-box-content">{!! $renderText($feature->how_it_works_notes) !!}</div>
                                </div>
                            @endif
                        </div>
                    @break

                    {{-- ============ SCREENSHOT ============ --}}
                    @case('screenshots')
                        <div class="section-title">
                            <span class="section-title-icon">{{ $section['num'] }}</span>
                            {{ $section['title'] }}
                        </div>
                        <div class="section-content">
                            @foreach ($parsedScreenshots as $idx => $img)
                                <div class="screenshot-box">
                                    <img src="{{ $img }}" alt="Screenshot {{ $idx + 1 }}">
                                </div>
                                <div class="screenshot-caption">
                                    {{ $feature->screenshot_caption_prefix ?? 'Gambar' }} {{ $idx + 1 }}:
                                    {{ $feature->name }}
                                </div>
                            @endforeach
                        </div>
                    @break

                    {{-- ============ CATATAN PENTING ============ --}}
                    @case('notes')
                        <div class="section-title">
                            <span class="section-title-icon">{{ $section['num'] }}</span>
                            {{ $section['title'] }}
                        </div>
                        <div class="section-content">
                            @if (count($parsedNotes) > 0)
                                @if (count($parsedNotes) === 1)
                                    <div class="info-box">
                                        <div class="info-box-title">ℹ {{ $feature->notes_info_title ?? 'INFORMASI' }}</div>
                                        <div class="info-box-content">{!! $renderText($parsedNotes[0]) !!}</div>
                                    </div>
                                @else
                                    <ul class="checklist">
                                        @foreach ($parsedNotes as $note)
                                            <li>{!! $renderText($note) !!}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            @endif

                            @if (count($parsedWarnings) > 0)
                                <div class="danger-box mt-20">
                                    <div class="danger-box-title">⚠ {{ $feature->warnings_title ?? 'PERINGATAN' }}</div>
                                    <div class="danger-box-content">
                                        @foreach ($parsedWarnings as $warn)
                                            {!! $renderText($warn) !!}
                                            @if (!$loop->last)
                                                <br>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @break

                    {{-- ============ KESIMPULAN ============ --}}
                    @case('conclusion')
                        <div class="section-title">
                            <span class="section-title-icon">{{ $section['num'] }}</span>
                            {{ $section['title'] }}
                        </div>
                        <div class="section-content">
                            <div class="conclusion-box">
                                <div class="conclusion-title">{{ $section['title'] }}</div>

                                @if (!empty($feature->conclusion))
                                    <div class="conclusion-content">
                                        {!! $renderText($feature->conclusion) !!}
                                    </div>
                                @endif

                                @if (count($parsedTargetUsers) > 0)
                                    <div class="target-users">
                                        <div class="target-users-title">
                                            {{ $feature->target_users_title ?? 'Target Pengguna:' }}
                                        </div>
                                        <ul class="checklist">
                                            @foreach ($parsedTargetUsers as $user)
                                                <li>{{ $user }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @break
                @endswitch

            </div>
        </div>
    @endforeach

    <footer>
        <span class="footer-brand">INIXCOFFEE</span> |
        {{ $feature->name }} |
        {{ $version }} |
        {{ $feature->footer_generated_by ?? 'Generated by Documentation System' }} |
        {{ $feature->footer_page_label ?? 'Halaman' }} <span class="page-number"></span>
    </footer>

</body>

</html>
