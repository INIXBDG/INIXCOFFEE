<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Laporan Eksekutif KPI - {{ $karyawan->nama_lengkap }}</title>
    <style>
        @page {
            margin: 12mm 15mm;
            size: A4;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', 'Helvetica Neue', Arial, sans-serif;
            font-size: 9.5pt;
            color: #1F2937;
            margin: 0;
            padding: 0;
            line-height: 1.5;
            background-color: #FFFFFF;
        }

        /* ==================== WATERMARK ==================== */
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

        /* ==================== PAGE NUMBER ==================== */
        .page-number {
            text-align: right;
            font-size: 7pt;
            color: #9CA3AF;
            margin-bottom: 5px;
        }

        /* ==================== HEADER ==================== */
        .header {
            text-align: center;
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 3px double #1E3A8A;
            position: relative;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #3B82F6, #1E3A8A, #3B82F6);
        }

        .header .company-label {
            font-size: 8pt;
            color: #6B7280;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .header h1 {
            margin: 0;
            color: #1E3A8A;
            font-size: 20pt;
            letter-spacing: 2px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .header .employee-name {
            font-size: 14pt;
            font-weight: bold;
            color: #3B82F6;
            margin-top: 6px;
            letter-spacing: 1.5px;
        }

        .header .employee-meta {
            font-size: 9pt;
            color: #6B7280;
            margin-top: 4px;
        }

        .header .report-id {
            display: inline-block;
            margin-top: 8px;
            padding: 2px 12px;
            background-color: #EFF6FF;
            border: 1px solid #BFDBFE;
            border-radius: 12px;
            font-size: 7.5pt;
            color: #1E40AF;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        /* ==================== SECTION TITLE ==================== */
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            color: #FFFFFF;
            margin-top: 20px;
            margin-bottom: 12px;
            background-color: #1E3A8A;
            background: linear-gradient(135deg, #1E3A8A 0%, #3B82F6 100%);
            padding: 8px 14px;
            border-radius: 4px;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            box-shadow: 0 2px 4px rgba(30, 58, 138, 0.2);
            border-left: 4px solid #3B82F6;
        }

        .sub-title {
            font-size: 9.5pt;
            font-weight: bold;
            color: #1E3A8A;
            margin-top: 14px;
            margin-bottom: 8px;
            padding-left: 8px;
            border-left: 3px solid #3B82F6;
        }

        /* ==================== TABLE ==================== */
        table.main {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 12px;
            font-size: 8.5pt;
            border-radius: 4px;
            overflow: hidden;
            border: 1px solid #E2E8F0;
        }

        table.main th {
            background-color: #1E3A8A;
            background: linear-gradient(180deg, #1E3A8A 0%, #1E40AF 100%);
            color: #FFFFFF !important;
            padding: 8px 6px;
            text-align: center;
            font-size: 8pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            border-bottom: 2px solid #1E3A8A;
            border: 1px solid #1E3A8A;
        }

        table.main td {
            padding: 7px 6px;
            border-bottom: 1px solid #E2E8F0;
            text-align: center;
            vertical-align: middle;
        }

        table.main tr:nth-child(even) td {
            background-color: #F8FAFC;
        }

        table.main tr:hover td {
            background-color: #EFF6FF;
        }

        table.main tr:last-child td {
            border-bottom: none;
        }

        /* ==================== TEXT UTILITY ==================== */
        .text-left {
            text-align: left !important;
        }

        .text-center {
            text-align: center !important;
        }

        .text-right {
            text-align: right !important;
        }

        .text-green {
            color: #059669;
            font-weight: bold;
        }

        .text-red {
            color: #DC2626;
            font-weight: bold;
        }

        .text-amber {
            color: #D97706;
            font-weight: bold;
        }

        .text-blue {
            color: #2563EB;
            font-weight: bold;
        }

        .text-purple {
            color: #8B5CF6;
            font-weight: bold;
        }

        .text-gray {
            color: #6B7280;
        }

        .text-sm {
            font-size: 7.5pt;
        }

        .text-xs {
            font-size: 7pt;
        }

        /* ==================== BAR CHART ==================== */
        .bar-container {
            width: 100%;
            background-color: #E2E8F0;
            height: 12px;
            border-radius: 6px;
            overflow: hidden;
            position: relative;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .bar-fill {
            height: 100%;
            border-radius: 6px;
            position: relative;
            transition: width 0.3s ease;
        }

        /* Dual Bar (Actual + Shadow) */
        .dual-bar-container {
            position: relative;
            height: 14px;
            background-color: #E2E8F0;
            border-radius: 7px;
            overflow: hidden;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .dual-bar-shadow {
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            background: repeating-linear-gradient(45deg,
                    rgba(139, 92, 246, 0.25),
                    rgba(139, 92, 246, 0.25) 4px,
                    rgba(139, 92, 246, 0.15) 4px,
                    rgba(139, 92, 246, 0.15) 8px);
            border-radius: 7px;
        }

        .dual-bar-actual {
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            border-radius: 7px;
            opacity: 0.95;
        }

        /* ==================== GAUGE ==================== */
        .gauge-container {
            background: linear-gradient(90deg, #E2E8F0 0%, #F1F5F9 100%);
            height: 24px;
            border-radius: 12px;
            overflow: hidden;
            position: relative;
            margin: 10px 0;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .gauge-fill {
            height: 100%;
            border-radius: 12px;
            text-align: center;
            color: #FFFFFF;
            font-size: 9pt;
            font-weight: bold;
            line-height: 24px;
            position: relative;
            overflow: hidden;
        }

        /* ==================== METRIC CARDS ==================== */
        .metric-card {
            text-align: center;
            padding: 12px 8px;
            border-radius: 6px;
            position: relative;
            overflow: hidden;
        }

        .metric-card .card-icon {
            font-size: 14pt;
            margin-bottom: 4px;
        }

        .metric-card .card-value {
            font-size: 18pt;
            font-weight: 800;
            color: #1E3A8A;
            line-height: 1;
        }

        .metric-card .card-sub {
            font-size: 7pt;
            color: #6B7280;
            margin-top: 4px;
        }

        .metric-card .card-title {
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 3px 8px;
            border-radius: 3px;
            display: block;
            margin: 0 auto 8px;
            width: fit-content;
            text-align: center;
        }

        .metric-card.blue .card-title {
            background-color: #3B82F6;
            color: #FFFFFF;
            border: 1px solid #2563EB;
        }

        .metric-card.purple .card-title {
            background-color: #8B5CF6;
            color: #FFFFFF;
            border: 1px solid #7C3AED;
        }

        .metric-card.amber .card-title {
            background-color: #D97706;
            color: #FFFFFF;
            border: 1px solid #B45309;
        }

        .metric-card.green .card-title {
            background-color: #059669;
            color: #FFFFFF;
            border: 1px solid #047857;
        }

        .metric-card.red .card-title {
            background-color: #DC2626;
            color: #FFFFFF;
            border: 1px solid #B91C1C;
        }

        /* ==================== NARRATIVE BOX ==================== */
        .narrative-box {
            background-color: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-left: 5px solid #3B82F6;
            padding: 14px 16px;
            margin-bottom: 12px;
            font-size: 8.5pt;
            line-height: 1.6;
            text-align: justify;
            border-radius: 0 6px 6px 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .narrative-box.risk {
            border-left-color: #DC2626;
            background-color: #FEF2F2;
        }

        .narrative-box.risk .pred-title {
            color: #DC2626;
            border-bottom-color: #FEE2E2;
        }

        .narrative-box.potential {
            border-left-color: #059669;
            background-color: #F0FDF4;
        }

        .narrative-box.potential .pred-title {
            color: #059669;
            border-bottom-color: #D1FAE5;
        }

        .narrative-box.warning {
            border-left-color: #D97706;
            background-color: #FFFBEB;
        }

        .narrative-box.warning .pred-title {
            color: #D97706;
            border-bottom-color: #FEF3C7;
        }

        .narrative-box.info {
            border-left-color: #2563EB;
            background-color: #F0F9FF;
        }

        .narrative-box.info .pred-title {
            color: #2563EB;
            border-bottom-color: #DBEAFE;
        }

        .pred-title {
            font-weight: bold;
            color: #3B82F6;
            font-size: 9pt;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding-bottom: 6px;
            border-bottom: 1px solid #DBEAFE;
        }

        /* ==================== INSIGHT BOX ==================== */
        .insight-box {
            background-color: #F0F9FF;
            border-left: 5px solid #3B82F6;
            padding: 12px 16px;
            margin: 14px 0;
            font-size: 8.5pt;
            border-radius: 0 6px 6px 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .insight-box.warning {
            background-color: #FFFBEB;
            border-left-color: #D97706;
        }

        .insight-box.danger {
            background-color: #FEF2F2;
            border-left-color: #DC2626;
        }

        .insight-box.success {
            background-color: #F0FDF4;
            border-left-color: #059669;
        }

        .insight-box strong {
            color: #1E3A8A;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .insight-box ul {
            margin: 6px 0;
            padding-left: 16px;
        }

        .insight-box li {
            margin-bottom: 4px;
            line-height: 1.5;
        }

        /* ==================== BADGE ==================== */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 7pt;
            font-weight: bold;
            color: #FFFFFF !important;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .badge-success {
            background-color: #059669;
            /* Solid fallback */
            background: linear-gradient(135deg, #059669 0%, #10B981 100%);
        }

        .badge-warning {
            background-color: #D97706;
            background: linear-gradient(135deg, #D97706 0%, #F59E0B 100%);
        }

        .badge-danger {
            background-color: #DC2626;
            background: linear-gradient(135deg, #DC2626 0%, #EF4444 100%);
        }

        .badge-info {
            background-color: #2563EB;
            background: linear-gradient(135deg, #2563EB 0%, #3B82F6 100%);
        }

        .badge-purple {
            background-color: #7C3AED;
            background: linear-gradient(135deg, #7C3AED 0%, #8B5CF6 100%);
        }

        /* ==================== ZONE INDICATOR ==================== */
        .zone-indicator {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 9pt;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        }

        .zone-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        /* ==================== PAGE BREAK ==================== */
        .page-break {
            page-break-before: always;
        }

        /* ==================== SIGN OFF ==================== */
        .sign-off-container {
            margin-top: 30px;
            padding: 20px;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            background-color: #FAFBFC;
        }

        .sign-cell {
            text-align: center;
            vertical-align: top;
            padding: 12px;
            width: 50%;
        }

        .sign-title {
            font-weight: bold;
            color: #1E3A8A;
            font-size: 9pt;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .sign-role {
            font-size: 8pt;
            color: #6B7280;
            margin-bottom: 50px;
        }

        .sign-line {
            border-bottom: 1.5px solid #1F2937;
            width: 80%;
            margin: 0 auto 5px;
        }

        .sign-name {
            font-size: 8.5pt;
            color: #374151;
            margin-top: 4px;
        }

        .sign-date {
            font-size: 7.5pt;
            color: #6B7280;
            margin-top: 4px;
        }

        /* ==================== FOOTER ==================== */
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 7.5pt;
            color: #6B7280;
            border-top: 1px solid #E2E8F0;
            padding-top: 10px;
            line-height: 1.6;
        }

        .footer strong {
            color: #1E3A8A;
        }

        /* ==================== NOTES BOX ==================== */
        .notes-box {
            margin-top: 20px;
            padding: 12px 16px;
            background-color: #F8FAFC;
            border: 1px dashed #CBD5E1;
            border-radius: 4px;
            font-size: 7.5pt;
            color: #6B7280;
            text-align: center;
            line-height: 1.6;
        }

        /* ==================== STAT CARD ==================== */
        .stat-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px dashed #E2E8F0;
            font-size: 8.5pt;
        }

        .stat-row:last-child {
            border-bottom: none;
        }

        .stat-label {
            color: #6B7280;
        }

        .stat-value {
            font-weight: bold;
            color: #1F2937;
        }

        /* ==================== MOMENTUM ==================== */
        .momentum-up {
            color: #059669;
            font-weight: bold;
        }

        .momentum-down {
            color: #DC2626;
            font-weight: bold;
        }

        .momentum-flat {
            color: #6B7280;
        }

        /* ==================== ROW HIGHLIGHTS ==================== */
        .row-top {
            background-color: #D1FAE5 !important;
        }

        .row-safe {
            background-color: #FEF9C3 !important;
        }

        .row-bottom {
            background-color: #FEE2E2 !important;
        }
    </style>
</head>

<body>

    <div class="watermark">
        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('assets/img/backgrounds/kops.png'))) }}"
            alt="Watermark">
    </div>

    @php
        // --- PREPARE DATA & GRANULAR LOGIC ---
        $avgProg = round($data['rata_rata_progress'] ?? 0, 1);
        $targets = $data['daftar_target_pribadi'] ?? [];
        if ($targets instanceof \Illuminate\Support\Collection) {
            $targets = $targets->toArray();
        }

        // Sort targets by progress percentage descending
        usort($targets, function($a, $b) {
            $progA = round($a['progress_percent'] ?? 0, 1);
            $progB = round($b['progress_percent'] ?? 0, 1);
            return $progB <=> $progA;
        });

        $percents = array_map(fn($t) => round((float) ($t['progress_percent'] ?? 0), 1), $targets);
        $count = count($percents);
        $mean = $count > 0 ? round(array_sum($percents) / $count, 1) : 0;
        $min = $count > 0 ? round(min($percents), 1) : 0;
        $max = $count > 0 ? round(max($percents), 1) : 0;

        $variance = $count > 0 ? array_sum(array_map(fn($x) => pow($x - $mean, 2), $percents)) / $count : 0;
        $stdDev = round(sqrt($variance), 1);

        // Median
        $median = 0;
        if ($count > 0) {
            $sorted = $percents;
            sort($sorted);
            $mid = floor($count / 2);
            $median = $count % 2 == 0 ? round(($sorted[$mid - 1] + $sorted[$mid]) / 2, 1) : round($sorted[$mid], 1);
        }

        $validMonths = array_filter($data['monthly_progress'] ?? [], fn($v) => $v !== null);
        $slope = 0;
        $r2 = 0;
        $prob = 0;
        $forecast12 = 0;

        // ==========================================
        // GRANULAR PREDICTION NARRATIVE (Interval 3%)
        // ==========================================
        $p = $avgProg;
        $predTitle = '';
        $predText = '';
        $predZoneColor = '#3B82F6';
        $predZoneIcon = '';

        if ($p < 40) {
            $predTitle = 'KONDISI KRITIS';
            $predText =
                'Progress di bawah 40% memerlukan intervensi darurat. Segera jadwalkan meeting dengan atasan untuk <em>root cause analysis</em>, evaluasi ulang kelayakan target, dan realokasi sumber daya secara total.';
            $predZoneColor = '#DC2626';
            $predZoneIcon = '';
        } elseif ($p < 43) {
            $predTitle = 'ZONA BAHAYA KRITIS';
            $predText =
                'Fokus total pada penyelamatan 1 target utama yang paling berdampak. Delegasikan atau tunda semua tugas administratif non-esensial minggu ini untuk memaksimalkan output.';
            $predZoneColor = '#DC2626';
            $predZoneIcon = '';
        } elseif ($p < 46) {
            $predTitle = 'ZONA BAHAYA';
            $predText =
                'Eskalasi hambatan operasional ke atasan segera. Jangan menunda penyelesaian tugas. Diperlukan akselerasi minimal 5-7% bulan ini untuk segera keluar dari zona merah.';
            $predZoneColor = '#DC2626';
            $predZoneIcon = '';
        } elseif ($p < 49) {
            $predTitle = 'ZONA MERAH AWAL';
            $predText =
                'Susun ulang prioritas kerja harian secara drastis. Identifikasi 2 target dengan progress terendah dan alokasikan 80% waktu produktif Anda minggu ini khusus untuk keduanya.';
            $predZoneColor = '#EF4444';
            $predZoneIcon = '';
        } elseif ($p < 52) {
            $predTitle = 'ZONA PERINGATAN KERAS';
            $predText =
                'Anda berada di ambang batas tengah. Terapkan rutinitas <em>daily check-in</em> dengan supervisor untuk memastikan akuntabilitas harian dan menjaga momentum tetap terjaga.';
            $predZoneColor = '#F97316';
            $predZoneIcon = '';
        } elseif ($p < 55) {
            $predTitle = 'ZONA KUNING TUA';
            $predText =
                'Fokus pada <em>quick wins</em>. Selesaikan target-target kecil yang mendekati finish terlebih dahulu untuk membangun momentum psikologis dan menaikkan rata-rata secara cepat.';
            $predZoneColor = '#EAB308';
            $predZoneIcon = '';
        } elseif ($p < 58) {
            $predTitle = 'ZONA TRANSISI';
            $predText =
                'Performa mulai membaik, namun belum stabil. Mulai terapkan <em>time-blocking</em> untuk tugas-tugas berat dan hindari multitasking yang dapat mengurangi kualitas output.';
            $predZoneColor = '#EAB308';
            $predZoneIcon = '';
        } elseif ($p < 61) {
            $predTitle = 'ZONA AMBANG BATAS';
            $predText =
                'Anda hampir mencapai titik aman (60%). Dorong 1-2 target kecil agar segera berstatus "Selesai" untuk memberikan napas lega dan ruang gerak yang lebih luas.';
            $predZoneColor = '#84CC16';
            $predZoneIcon = '';
        } elseif ($p < 64) {
            $predTitle = 'ZONA AMAN AWAL';
            $predText =
                'Selamat, Anda telah melewati batas kritis. Pertahankan konsistensi ini. Evaluasi target yang masih stagnan dan cari tahu hambatan spesifik yang menahannya.';
            $predZoneColor = '#84CC16';
            $predZoneIcon = '';
        } elseif ($p < 67) {
            $predTitle = 'ZONA STABIL';
            $predText =
                'Performa mulai menunjukkan pola yang baik. Tingkatkan intensitas dan alokasi waktu pada target bernilai tinggi (high-impact) untuk mendongkrak skor secara signifikan.';
            $predZoneColor = '#22C55E';
            $predZoneIcon = '';
        } elseif ($p < 70) {
            $predTitle = 'ZONA PERTUMBUHAN';
            $predText =
                'Anda berada di jalur yang sangat tepat. Fokus pada penyelesaian target-target besar di kuartal ini. Jangan puas, dorong sedikit lagi untuk masuk ke zona 70%+.';
            $predZoneColor = '#2563EB';
            $predZoneIcon = '';
        } elseif ($p < 73) {
            $predTitle = 'ZONA BAIK';
            $predText =
                'Momentum positif telah terbentuk. Mulailah mendokumentasikan progress dan tantangan yang dihadapi sebagai bahan evaluasi mid-year yang konstruktif dan berbasis data.';
            $predZoneColor = '#2563EB';
            $predZoneIcon = '';
        } elseif ($p < 76) {
            $predTitle = 'ZONA SANGAT BAIK';
            $predText =
                'Akselerasi lebih lanjut sangat mungkin. Identifikasi 1 target "stretch" (menantang) yang bisa Anda selesaikan lebih awal untuk memberikan nilai tambah bagi divisi.';
            $predZoneColor = '#2563EB';
            $predZoneIcon = '';
        } elseif ($p < 79) {
            $predTitle = 'ZONA OPTIMAL';
            $predText =
                'Performa Anda sangat solid dan dapat diandalkan. Mulai bagikan <em>best practice</em> atau tips efisiensi yang Anda temukan kepada rekan tim untuk memperkuat budaya kerja.';
            $predZoneColor = '#8B5CF6';
            $predZoneIcon = '';
        } elseif ($p < 82) {
            $predTitle = 'ZONA EXCELLENT';
            $predText =
                'Anda mendekati garis finish dengan sangat baik. Pastikan tidak ada target yang terabaikan di akhir periode. Lakukan validasi data dengan stakeholder terkait.';
            $predZoneColor = '#8B5CF6';
            $predZoneIcon = '';
        } elseif ($p < 85) {
            $predTitle = 'ZONA PRESTASI TINGGI';
            $predText =
                'Pertahankan standar kualitas yang luar biasa ini. Mulailah menyusun rencana pengembangan diri atau target baru yang lebih menantang untuk periode berikutnya.';
            $predZoneColor = '#8B5CF6';
            $predZoneIcon = '';
        } elseif ($p < 88) {
            $predTitle = 'ZONA TOP PERFORMER';
            $predText =
                'Konsistensi Anda luar biasa. Anda berada di posisi yang ideal untuk mempertimbangkan peran mentoring atau memimpin inisiatif kecil bagi anggota tim lain.';
            $predZoneColor = '#7C3AED';
            $predZoneIcon = '';
        } elseif ($p < 91) {
            $predTitle = 'ZONA MASTERY';
            $predText =
                'Anda telah menjadi benchmark kinerja di divisi. Fokus selanjutnya adalah pada inovasi proses dan efisiensi kerja, bukan hanya sekadar mencapai angka target.';
            $predZoneColor = '#7C3AED';
            $predZoneIcon = '';
        } elseif ($p < 94) {
            $predTitle = 'ZONA SEMPURNA MENDEKATI';
            $predText =
                'Pastikan semua administrasi, bukti pendukung, dan dokumentasi target sudah 100% lengkap dan rapi untuk memudahkan proses validasi akhir oleh manajemen.';
            $predZoneColor = '#F59E0B';
            $predZoneIcon = '';
        } elseif ($p < 97) {
            $predTitle = 'ZONA FINALISASI';
            $predText =
                'Selesaikan detail-detail kecil yang tersisa. Pastikan komunikasi dengan atasan/stakeholder mengenai status final target sudah jelas, transparan, dan disetujui.';
            $predZoneColor = '#F59E0B';
            $predZoneIcon = '';
        } else {
            $predTitle = 'ZONA EXCELLENCE';
            $predText =
                'Selamat! Target tercapai dengan sangat baik. Fokus sekarang beralih pada evaluasi pasca-proyek, pembelajaran (lessons learned), dan perencanaan strategis untuk tahun depan.';
            $predZoneColor = '#059669';
            $predZoneIcon = '';
        }

        // ==========================================
        // RISK NARRATIVE (Volatilitas)
        // ==========================================
        $riskText =
            'Volatilitas performa saat ini adalah <strong>' .
            number_format($stdDev, 1) .
            '%</strong> (Standar Deviasi). ';
        $riskType = 'info';
        if ($stdDev > 20) {
            $riskText .=
                'Nilai ini <strong>TINGGI</strong>, menunjukkan performa yang sangat fluktuatif dan tidak konsisten. Perlu investigasi penyebab inkonsistensi dan pembuatan SOP pribadi yang lebih ketat.';
            $riskType = 'danger';
        } elseif ($stdDev > 10) {
            $riskText .=
                'Nilai ini <strong>SEDANG</strong>, menunjukkan fluktuasi yang wajar namun perlu dimonitor agar tidak berkembang menjadi ketidakstabilan di akhir periode.';
            $riskType = 'warning';
        } else {
            $riskText .=
                'Nilai ini <strong>RENDAH</strong>, menunjukkan ritme kerja yang sangat stabil, konsisten, dan dapat diprediksi dengan akurasi tinggi.';
            $riskType = 'success';
        }

        // Hitung statistik tambahan
        $totalTarget = $data['total_target'] ?? 0;
        $kpiSelesai = $data['kpi_selesai'] ?? 0;
        $kpiGagal = $data['kpi_gagal'] ?? 0;
        $kpiAktif = $data['kpi_aktif'] ?? 0;
        $successRate = $totalTarget > 0 ? round(($kpiSelesai / $totalTarget) * 100, 1) : 0;
        $failRate = $totalTarget > 0 ? round(($kpiGagal / $totalTarget) * 100, 1) : 0;

        // Zone color untuk indicator
        if ($avgProg < 40) {
            $zoneBg = '#FEE2E2';
            $zoneColor = '#991B1B';
        } elseif ($avgProg < 60) {
            $zoneBg = '#FEF3C7';
            $zoneColor = '#92400E';
        } elseif ($avgProg < 75) {
            $zoneBg = '#DBEAFE';
            $zoneColor = '#1E40AF';
        } elseif ($avgProg < 90) {
            $zoneBg = '#EDE9FE';
            $zoneColor = '#5B21B6';
        } else {
            $zoneBg = '#D1FAE5';
            $zoneColor = '#065F46';
        }
    @endphp

    <!-- ========================================== -->
    <!-- HALAMAN 1: EXECUTIVE DASHBOARD -->
    <!-- ========================================== -->

    <div class="page-number">Halaman 1 dari 4</div>

    <!-- HEADER -->
    <div class="header">
        <div class="company-label">Sistem Monitoring KPI Perusahaan</div>
        <h1>Laporan Eksekutif KPI</h1>
        <div class="employee-name">{{ strtoupper($karyawan->nama_lengkap) }}</div>
        <div class="employee-meta">
            {{ $karyawan->jabatan ?? '-' }} &bull;
            {{ $data['user_info']['divisi'] ?? '-' }} &bull;
            Tahun {{ $tahun ?? now()->year }}
        </div>
        <div class="report-id">
            RPT-KPI-INDV-{{ strtoupper(substr($karyawan->nama_lengkap, 0, 3)) }}-{{ $tahun ?? now()->year }}-{{ date('md') }}
        </div>
    </div>

    <!-- METRIC CARDS -->
    <table width="100%" cellpadding="6" cellspacing="0"
        style="margin-bottom: 16px; border-collapse: separate; border-spacing: 6px;">
        <tr>
            <td width="25%"
                style="background-color: #EFF6FF; border: 1px solid #93C5FD; border-radius: 6px; text-align: center; padding: 12px 8px;">
                <div
                    style="background-color: #3B82F6; color: #FFFFFF; font-size: 7.5pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; padding: 3px 8px; border-radius: 3px; display: block; margin: 0 auto 8px; width: fit-content; border: 1px solid #2563EB;">
                    Total Target</div>
                <div style="font-size: 18pt; font-weight: 800; color: #1E3A8A; line-height: 1;">{{ $totalTarget }}
                </div>
                <div style="font-size: 7pt; color: #6B7280; margin-top: 4px;">Seluruh KPI</div>
            </td>
            <td width="25%"
                style="background-color: #F5F3FF; border: 1px solid #C4B5FD; border-radius: 6px; text-align: center; padding: 12px 8px;">
                <div
                    style="background-color: #8B5CF6; color: #FFFFFF; font-size: 7.5pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; padding: 3px 8px; border-radius: 3px; display: block; margin: 0 auto 8px; width: fit-content; border: 1px solid #7C3AED;">
                    Rata-rata Progress</div>
                <div style="font-size: 18pt; font-weight: 800; color: #1E3A8A; line-height: 1;">
                    {{ number_format($avgProg, 1) }}%</div>
                <div style="font-size: 7pt; color: #6B7280; margin-top: 4px;">Keseluruhan</div>
            </td>
            <td width="25%"
                style="background-color: #FFFBEB; border: 1px solid #FCD34D; border-radius: 6px; text-align: center; padding: 12px 8px;">
                <div
                    style="background-color: #D97706; color: #FFFFFF; font-size: 7.5pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; padding: 3px 8px; border-radius: 3px; display: block; margin: 0 auto 8px; width: fit-content; border: 1px solid #B45309;">
                    KPI Aktif</div>
                <div style="font-size: 18pt; font-weight: 800; color: #1E3A8A; line-height: 1;">{{ $kpiAktif }}
                </div>
                <div style="font-size: 7pt; color: #6B7280; margin-top: 4px;">Sedang Berjalan</div>
            </td>
            <td width="25%"
                style="background-color: #F0FDF4; border: 1px solid #86EFAC; border-radius: 6px; text-align: center; padding: 12px 8px;">
                <div
                    style="background-color: #059669; color: #FFFFFF; font-size: 7.5pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; padding: 3px 8px; border-radius: 3px; display: block; margin: 0 auto 8px; width: fit-content; border: 1px solid #047857;">
                    KPI Selesai</div>
                <div style="font-size: 18pt; font-weight: 800; color: #1E3A8A; line-height: 1;">{{ $kpiSelesai }}
                </div>
                <div style="font-size: 7pt; color: #6B7280; margin-top: 4px;">{{ number_format($successRate, 1) }}%
                    Success Rate</div>
            </td>
        </tr>
    </table>

    <!-- ZONE + PREDICTION -->
    <div style="text-align: center; margin-bottom: 16px;">
        <div class="zone-indicator" style="background-color: {{ $zoneBg }}; color: {{ $zoneColor }};">
            {{ $predZoneIcon }} {{ $predTitle }} &bull; {{ number_format($avgProg, 1) }}%
        </div>
    </div>

    <div class="narrative-box info">
        <div class="pred-title">{{ $predZoneIcon }} Prediksi Kinerja & Rekomendasi Strategis</div>
        {!! $predText !!}
    </div>

    <!-- INSIGHT BOX -->
    @php
        $insightType = 'info';
        $insights = [];
        if ($avgProg >= 80) {
            $insights[] =
                'Anda menunjukkan performa <strong>SANGAT BAIK</strong> dengan rata-rata progress <strong>' .
                number_format($avgProg, 1) .
                '%</strong>.';
            $insightType = 'success';
        } elseif ($avgProg >= 60) {
            $insights[] =
                'Performa Anda berada dalam kondisi <strong>CUKUP</strong> dengan rata-rata progress <strong>' .
                number_format($avgProg, 1) .
                '%</strong>.';
            $insightType = 'warning';
        } else {
            $insights[] =
                'Anda membutuhkan <strong>INTERVENSI SEGERA</strong> dengan rata-rata progress hanya <strong>' .
                number_format($avgProg, 1) .
                '%</strong>.';
            $insightType = 'danger';
        }
        if ($kpiSelesai > 0) {
            $insights[] = "Telah berhasil menyelesaikan <strong>{$kpiSelesai} target</strong> ({$successRate}% success rate).";
        }
        if ($kpiGagal > 0) {
            $insights[] = "Terdapat <strong>{$kpiGagal} target berstatus GAGAL</strong> ({$failRate}% dari total) yang memerlukan evaluasi mendalam.";
        }
    @endphp

    <div class="insight-box {{ $insightType }}">
        <strong>Personal Insight</strong>
        <ul>
            @foreach ($insights as $insight)
                <li>{!! $insight !!}</li>
            @endforeach
        </ul>
    </div>

    <!-- ========================================== -->
    <!-- HALAMAN 2: TREN BULANAN -->
    <!-- ========================================== -->
    <div class="page-break"></div>
    <div class="page-number">Halaman 2 dari 4</div>

    <div class="section-title">Tren Bulanan: Realitas vs Prediksi (Shadow)</div>
    <p style="font-size: 8pt; color: #6B7280; margin-top: -6px; margin-bottom: 12px; font-style: italic;">
        Perbandingan progress aktual dengan garis prediksi (shadow) untuk melihat potensi pencapaian bulan depan.
    </p>

    <table class="main">
        <thead style="color: black;">
            <tr>
                <th style="width: 13%; color: black;">Bulan</th>
                <th style="width: 15%; color: black;">Aktual (%)</th>
                <th style="width: 15%; color: black;">Shadow (%)</th>
                <th style="width: 12%; color: black;">Momentum (Δ)</th>
                <th style="width: 45%; color: black;">Visualisasi Dual-Layer</th>
            </tr>
        </thead>
        <tbody>
            @php
                $monthNames = [
                    '',
                    'Januari',
                    'Februari',
                    'Maret',
                    'April',
                    'Mei',
                    'Juni',
                    'Juli',
                    'Agustus',
                    'September',
                    'Oktober',
                    'November',
                    'Desember',
                ];
                $prevVal = null;
                $shadowSlope = ($avgProg / max(1, count($validMonths))) * 0.8;
            @endphp
            @for ($m = 1; $m <= 12; $m++)
                @php
                    $actual = isset($data['monthly_progress'][$m]) ? round($data['monthly_progress'][$m], 1) : null;
                    $shadow =
                        $actual !== null
                            ? $actual
                            : round(max(0, min(100, $avgProg + $shadowSlope * ($m - max(1, count($validMonths))))), 1);

                    $aColor =
                        $actual !== null
                            ? ($actual >= 80
                                ? '#059669'
                                : ($actual >= 60
                                    ? '#D97706'
                                    : '#DC2626'))
                            : '#CBD5E1';
                    $shadowColor = '#8B5CF6';

                    $delta = 0;
                    $deltaDisplay = '—';
                    $momentumClass = 'momentum-flat';
                    $momentumIcon = '';

                    if ($actual !== null && $prevVal !== null) {
                        $delta = round($actual - $prevVal, 1);
                        $sign = $delta >= 0 ? '+' : '';
                        $deltaDisplay = $sign . number_format($delta, 1) . '%';

                        if ($delta > 0) {
                            $momentumClass = 'momentum-up';
                            $momentumIcon = '▲';
                        } elseif ($delta < 0) {
                            $momentumClass = 'momentum-down';
                            $momentumIcon = '▼';
                        } else {
                            $momentumClass = 'momentum-flat';
                            $momentumIcon = '●';
                        }
                    }

                    if ($actual !== null) {
                        $prevVal = $actual;
                    }
                @endphp
                <tr>
                    <td style="font-weight: bold; background-color: #EFF6FF;">{{ $monthNames[$m] }}</td>
                    <td style="font-weight: bold; color: {{ $aColor }};">
                        {{ $actual !== null ? number_format($actual, 1) . '%' : '—' }}
                    </td>
                    <td style="font-weight: bold; color: {{ $shadowColor }}; font-style: italic;">
                        {{ number_format($shadow, 1) }}%
                    </td>
                    <td class="{{ $momentumClass }}">
                        @if ($actual !== null && $prevVal !== null && $momentumIcon !== '')
                            {{ $momentumIcon }} {{ $deltaDisplay }}
                        @else
                            —
                        @endif
                    </td>
                    <td style="text-align: left; padding: 8px 12px;">
                        <div class="dual-bar-container">
                            <div class="dual-bar-shadow" style="width: {{ $shadow }}%;"></div>
                            @if ($actual !== null)
                                <div class="dual-bar-actual"
                                    style="width: {{ $actual }}%; background-color: {{ $aColor }};"></div>
                            @endif
                        </div>
                    </td>
                </tr>
            @endfor
        </tbody>
    </table>

    <!-- INSIGHT TREN -->
    @if (!empty($validMonths))
        @php
            $maxM = array_search(max($validMonths), $validMonths);
            $minM = array_search(min($validMonths), $validMonths);
            $mNames = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            $fluktuasi = round(max($validMonths) - min($validMonths), 1);
        @endphp
        <div class="insight-box success">
            <strong>Analisis Tren Bulanan</strong>
            <ul>
                <li>Performa <strong>tertinggi</strong> dicapai pada bulan <strong>{{ $mNames[$maxM] }}</strong> dengan
                    progress <strong>{{ number_format(round(max($validMonths), 1), 1) }}%</strong>.</li>
                <li>Performa <strong>terendah</strong> tercatat pada bulan <strong>{{ $mNames[$minM] }}</strong> dengan
                    progress <strong>{{ number_format(round(min($validMonths), 1), 1) }}%</strong>.</li>
                <li>Rentang fluktuasi: <strong>{{ number_format($fluktuasi, 1) }}%</strong>
                    ({{ $fluktuasi > 20 ? 'Fluktuasi Tinggi — Perlu Stabilisasi' : 'Fluktuasi Wajar' }}).
                </li>
            </ul>
        </div>
    @else
        <div class="insight-box warning">
            <strong> Data Tidak Lengkap</strong>
            <p style="margin: 4px 0;">Belum ada data progres bulanan yang tercatat untuk dianalisis. Silakan update
                progress secara berkala.</p>
        </div>
    @endif

    <!-- ANALISIS STATISTIK -->
    <div class="section-title" style="color: black;">Analisis Statistik Lanjutan</div>

    <table style="width: 100%; border-collapse: separate; border-spacing: 8px 0; margin-top: 12px;">
        <tr>
            <td style="width: 50%; vertical-align: top; padding-right: 4px;">
                <div
                    style="padding: 14px; background: linear-gradient(135deg, #FFFFFF 0%, #F8FAFC 100%); border: 1px solid #E2E8F0; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <div
                        style="font-weight: bold; color: #1E3A8A; margin-bottom: 10px; font-size: 9pt; text-transform: uppercase; letter-spacing: 0.5px;">
                        📉 Statistik Deskriptif</div>

                    <div class="stat-row">
                        <span class="stat-label">Jumlah Target</span>
                        <span class="stat-value">{{ $count }}</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Rata-rata (Mean)</span>
                        <span class="stat-value">{{ number_format($mean, 1) }}%</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Median</span>
                        <span class="stat-value">{{ number_format($median, 1) }}%</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Standar Deviasi (σ)</span>
                        <span class="stat-value">{{ number_format($stdDev, 1) }}%</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Variansi</span>
                        <span class="stat-value">{{ number_format($variance, 2) }}</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Nilai Minimum</span>
                        <span class="stat-value text-red">{{ number_format($min, 1) }}%</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Nilai Maksimum</span>
                        <span class="stat-value text-green">{{ number_format($max, 1) }}%</span>
                    </div>
                </div>
            </td>
            <td style="width: 50%; vertical-align: top; padding-left: 4px;">
                <div
                    style="padding: 14px; background: linear-gradient(135deg, #FFFFFF 0%, #F8FAFC 100%); border: 1px solid #E2E8F0; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <div
                        style="font-weight: bold; color: #1E3A8A; margin-bottom: 10px; font-size: 9pt; text-transform: uppercase; letter-spacing: 0.5px;">
                        📊 Distribusi Status</div>

                    @php $hasStatus = false; @endphp
                    @foreach ($data['distribusi_status'] ?? [] as $status => $cnt)
                        @if ($cnt > 0)
                            @php
                                $hasStatus = true;
                                $cls =
                                    $status == 'Selesai'
                                        ? 'text-green'
                                        : ($status == 'Gagal'
                                            ? 'text-red'
                                            : ($status == 'Sedang Berjalan'
                                                ? 'text-amber'
                                                : 'text-gray'));
                            @endphp
                            <div class="stat-row">
                                <span class="stat-label {{ $cls }}">{{ $status }}</span>
                                <span class="stat-value">{{ $cnt }} Target</span>
                            </div>
                        @endif
                    @endforeach

                    @if (!$hasStatus)
                        <div style="color: #6B7280; font-size: 8pt; text-align: center; padding: 10px;">Tidak ada data
                            distribusi status.</div>
                    @endif
                </div>

                <!-- PROBABILITY GAUGE -->
                @php
                    $prob = round(max(0, min(100, $avgProg + 15)), 1);
                    $gColor = $prob >= 70 ? '#059669' : ($prob >= 40 ? '#D97706' : '#DC2626');
                @endphp
                <div
                    style="margin-top: 12px; padding: 14px; background: linear-gradient(135deg, #F8FAFC 0%, #EFF6FF 100%); border: 1px solid #BFDBFE; border-radius: 6px;">
                    <div
                        style="font-weight: bold; color: #1E3A8A; margin-bottom: 10px; font-size: 9pt; text-transform: uppercase; letter-spacing: 0.5px;">
                        🎛️ Probabilitas Pencapaian 100%</div>
                    <div class="gauge-container">
                        <div class="gauge-fill"
                            style="width: {{ max(15, $prob) }}%; background: linear-gradient(90deg, {{ $gColor }} 0%, {{ $gColor }}CC 100%);">
                            {{ number_format($prob, 1) }}%
                        </div>
                    </div>
                    <div style="font-size: 7.5pt; color: #6B7280; margin-top: 8px; text-align: center;">
                        Target Pribadi: 100% | Gap: {{ number_format(max(0, 100 - $prob), 1) }}%
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- RISK ANALYSIS -->
    <div class="narrative-box {{ $riskType }}" style="margin-top: 16px;">
        <div class="pred-title"> Analisis Risiko Volatilitas</div>
        {!! $riskText !!}
    </div>

    <!-- ========================================== -->
    <!-- HALAMAN 3: REKOMENDASI STRATEGIS -->
    <!-- ========================================== -->
    <div class="page-break"></div>
    <div class="page-number">Halaman 3 dari 4</div>

    <div class="section-title">Rekomendasi Strategis Personal</div>
    <p style="font-size: 8pt; color: #6B7280; margin-top: -6px; margin-bottom: 12px; font-style: italic;">
        Rekomendasi yang disesuaikan berdasarkan performa aktual dan volatilitas kerja Anda.
    </p>

    @php
        $recs = [];

        // Primary recommendation based on zone
        $recs[] = [
            'icon' => $predZoneIcon,
            'title' => $predTitle,
            'text' => $predText,
            'type' => $avgProg < 60 ? 'danger' : ($avgProg < 75 ? 'warning' : 'success'),
        ];

        // Volatility-based recommendations
        if ($stdDev > 20) {
            $recs[] = [
                'icon' => '',
                'title' => 'Peringatan Volatilitas Tinggi',
                'text' =>
                    'Fluktuasi performa Anda sangat tinggi (σ=' .
                    number_format($stdDev, 1) .
                    '%). Segera buat rutinitas harian yang konsisten dan SOP pribadi untuk mengurangi risiko penurunan progress mendadak.',
                'type' => 'danger',
            ];
        } elseif ($stdDev > 12) {
            $recs[] = [
                'icon' => '',
                'title' => 'Catatan Konsistensi',
                'text' =>
                    'Terdapat fluktuasi sedang (σ=' .
                    number_format($stdDev, 1) .
                    '%). Pertahankan ritme kerja yang stabil dan hindari penumpukan tugas di akhir periode (sistem kebut semalam).',
                'type' => 'warning',
            ];
        } else {
            $recs[] = [
                'icon' => '',
                'title' => 'Konsistensi Optimal',
                'text' =>
                    'Performa Anda sangat stabil (σ=' .
                    number_format($stdDev, 1) .
                    '%). Pertahankan ritme kerja ini dan pertimbangkan untuk mengambil tantangan tambahan.',
                'type' => 'success',
            ];
        }

        // Target count recommendations
        if ($count > 0) {
            $completionRatio = $count > 0 ? ($kpiSelesai / $count) * 100 : 0;
            if ($completionRatio >= 80) {
                $recs[] = [
                    'icon' => '',
                    'title' => 'Top Performer Mindset',
                    'text' =>
                        'Anda telah menyelesaikan ' .
                        number_format($completionRatio, 1) .
                        '% target. Saatnya menjadi mentor bagi rekan tim atau mengambil inisiatif strategis baru.',
                    'type' => 'success',
                ];
            } elseif ($completionRatio < 30 && $count >= 3) {
                $recs[] = [
                    'icon' => '',
                    'title' => 'Fokus Prioritas',
                    'text' =>
                        "Dengan {$count} target aktif dan completion rate hanya " .
                        number_format($completionRatio, 1) .
                        '%, segera lakukan reprioritisasi. Fokus pada 2-3 target paling kritis.',
                    'type' => 'danger',
                ];
            }
        }
    @endphp

    @foreach ($recs as $rec)
        <div class="narrative-box {{ $rec['type'] }}">
            <div class="pred-title">{{ $rec['icon'] }} {{ $rec['title'] }}</div>
            {!! $rec['text'] !!}
        </div>
    @endforeach

    <!-- QUICK ACTION CHECKLIST -->
    <div
        style="padding: 14px; background: linear-gradient(135deg, #FFFBEB 0%, #FEF3C7 100%); border: 1px solid #FCD34D; border-radius: 6px; margin-top: 16px;">
        <div
            style="font-weight: bold; color: #92400E; margin-bottom: 10px; font-size: 9pt; text-transform: uppercase; letter-spacing: 0.5px;">
            ✅ Quick Action Checklist (7 Hari ke Depan)</div>
        <ul style="margin: 0; padding-left: 18px; font-size: 8.5pt; line-height: 1.8;">
            @if ($avgProg < 60)
                <li>Jadwalkan 1-on-1 meeting dengan atasan untuk <em>root cause analysis</em>.</li>
                <li>Identifikasi 1-2 target <em>quick wins</em> untuk segera diselesaikan minggu ini.</li>
                <li>Susun ulang prioritas harian menggunakan matriks Eisenhower.</li>
                <li>Terapkan teknik Pomodoro (25 menit fokus, 5 menit istirahat).</li>
            @elseif ($avgProg < 80)
                <li>Review target dengan progress &lt;60% dan buat action plan spesifik.</li>
                <li>Konsultasi dengan top performer untuk mendapatkan tips efisiensi.</li>
                <li>Tetapkan milestone mingguan untuk setiap target aktif.</li>
                <li>Reward diri sendiri setelah menyelesaikan 1 target besar.</li>
            @else
                <li>Dokumentasikan <em>best practice</em> Anda untuk sharing dengan tim.</li>
                <li>Identifikasi 1 <em>stretch goal</em> untuk ditambahkan periode depan.</li>
                <li>Investasikan waktu untuk pengembangan skill baru yang relevan.</li>
                <li>Pertimbangkan peran mentoring untuk membantu rekan tim.</li>
            @endif
        </ul>
    </div>

    <!-- ========================================== -->
    <!-- HALAMAN 4: DETAIL TARGET & SIGN-OFF -->
    <!-- ========================================== -->
    <div class="page-break"></div>
    <div class="page-number">Halaman 4 dari 4</div>

    <div class="section-title">Daftar Lengkap Target KPI Pribadi</div>
    <p style="font-size: 8pt; color: #6B7280; margin-top: -6px; margin-bottom: 12px; font-style: italic;">
        Seluruh target KPI yang menjadi tanggung jawab Anda beserta progress pencapaian aktual.
    </p>

    <table class="main">
        <thead style="color: black;">
            <tr>
                <th style="width: 5%; color: black;">No</th>
                <th style="width: 32%; color: black;">Judul Target</th>
                <th style="width: 13%; color: black;">Periode</th>
                <th style="width: 10%; color: black;">Tipe</th>
                <th style="width: 15%; color: black;">Progress</th>
                <th style="width: 10%; color: black;">% Progress</th>
                <th style="width: 15%; color: black;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($targets as $index => $t)
                @php
                    $prog = round($t['progress_percent'] ?? 0, 1);
                    $pColor = $prog >= 80 ? 'text-green' : ($prog >= 60 ? 'text-amber' : 'text-red');
                    $s = strtolower($t['status'] ?? '');
                    $badgeClass = str_contains($s, 'selesai')
                        ? 'badge-success'
                        : (str_contains($s, 'gagal')
                            ? 'badge-danger'
                            : (str_contains($s, 'berjalan')
                                ? 'badge-warning'
                                : 'badge-info'));
                    
                    // Logika Warna Baris
                    $isTop = $index < 3;
                    $isBottom = ($count - 1 - $index) < 3 && $index >= 3;

                    if ($isTop) {
                        $rowClass = 'row-top';
                    } elseif ($isBottom) {
                        $rowClass = 'row-bottom';
                    } else {
                        $rowClass = 'row-safe';
                    }
                @endphp
                <tr class="{{ $rowClass }}">
                    <td><strong>{{ $index + 1 }}</strong></td>
                    <td class="text-left"><strong>{{ $t['judul'] ?? '-' }}</strong></td>
                    <td class="text-sm">{{ $t['periode'] ?? '-' }}</td>
                    <td class="text-sm">{{ ucfirst($t['tipe_target'] ?? '-') }}</td>
                    <td class="{{ $pColor }}">
                        <strong>{{ $t['progress_display'] ?? '0' }}</strong>
                    </td>
                    <td>
                        <div class="bar-container" style="margin-bottom: 3px;">
                            <div class="bar-fill"
                                style="width: {{ min(100, $prog) }}%; background-color: {{ $prog >= 80 ? '#059669' : ($prog >= 60 ? '#D97706' : '#DC2626') }};">
                            </div>
                        </div>
                        <span class="text-sm {{ $pColor }}">{{ number_format($prog, 1) }}%</span>
                    </td>
                    <td><span class="badge {{ $badgeClass }}" style="color: black;">{{ $t['status'] ?? '-' }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="color: #6B7280; padding: 16px;">Belum ada target KPI terdaftar.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- SUMMARY TABLE -->
    @if ($count > 0)
        <div
            style="margin-top: 12px; padding: 12px 16px; background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%); border: 1px solid #93C5FD; border-radius: 6px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 8.5pt;">
                <tr>
                    <td style="width: 33%; text-align: center; padding: 8px;">
                        <div style="font-size: 7pt; color: #6B7280; text-transform: uppercase; letter-spacing: 0.5px;">
                            Rata-rata Progress</div>
                        <div style="font-size: 16pt; font-weight: bold; color: #1E3A8A;">
                            {{ number_format($mean, 1) }}%</div>
                    </td>
                    <td
                        style="width: 33%; text-align: center; padding: 8px; border-left: 1px solid #BFDBFE; border-right: 1px solid #BFDBFE;">
                        <div style="font-size: 7pt; color: #6B7280; text-transform: uppercase; letter-spacing: 0.5px;">
                            Median</div>
                        <div style="font-size: 16pt; font-weight: bold; color: #1E3A8A;">
                            {{ number_format($median, 1) }}%</div>
                    </td>
                    <td style="width: 34%; text-align: center; padding: 8px;">
                        <div style="font-size: 7pt; color: #6B7280; text-transform: uppercase; letter-spacing: 0.5px;">
                            Range (Min-Max)</div>
                        <div style="font-size: 14pt; font-weight: bold; color: #1E3A8A;">
                            <span class="text-red">{{ number_format($min, 1) }}%</span>
                            <span style="color: #6B7280; font-size: 10pt;"> → </span>
                            <span class="text-green">{{ number_format($max, 1) }}%</span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    @endif

    <!-- SIGN-OFF -->
    <div class="sign-off-container">
        <div
            style="font-weight: bold; color: #1E3A8A; margin-bottom: 12px; font-size: 10pt; text-transform: uppercase; letter-spacing: 0.5px; text-align: center; border-bottom: 2px solid #E2E8F0; padding-bottom: 8px;">
            Pengesahan Laporan
        </div>
        <p style="font-size: 8pt; color: #6B7280; margin-bottom: 20px; text-align: center; font-style: italic;">
            Laporan ini telah disusun berdasarkan data aktual sistem KPI dan siap untuk ditinjau serta disahkan oleh
            pihak berwenang.
        </p>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td class="sign-cell">
                    <div class="sign-title">Dibuat oleh</div>
                    <div class="sign-role">Sistem Monitoring KPI (Otomatis)</div>
                    <div class="sign-line"></div>
                    <div class="sign-name">( Sistem )</div>
                    <div class="sign-date">Tanggal: {{ now()->format('d M Y') }}</div>
                </td>
                <td class="sign-cell">
                    <div class="sign-title">Disetujui oleh</div>
                    <div class="sign-role">Atasan Langsung / Manager</div>
                    <div class="sign-line"></div>
                    <div class="sign-name">( ..................................... )</div>
                    <div class="sign-date">Tanggal: ...........................</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- NOTES -->
    <div class="notes-box">
        <strong>Catatan Penting:</strong> Laporan ini digenerate secara otomatis oleh Sistem Monitoring KPI.
        Data yang tertera bersumber dari input aktual pada saat laporan dibuat.
        Setiap perubahan data setelah laporan ini digenerate tidak akan tercermin di dokumen ini.
    </div>

    <!-- FOOTER -->
    <div class="footer">
        <strong>LAPORAN EKSEKUTIF KPI PRIBADI</strong><br>
        {{ strtoupper($karyawan->nama_lengkap) }} &bull; Tahun {{ $tahun ?? now()->year }}<br>
        Digenerate: {{ now()->format('d M Y, H:i') }} WIB<br>
        <em>Dokumen ini bersifat rahasia dan hanya untuk kepentingan internal perusahaan.</em>
    </div>

</body>

</html>