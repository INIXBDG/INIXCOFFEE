<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Laporan Eksekutif KPI Departemen</title>
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

        .header .dept-name {
            font-size: 14pt;
            font-weight: bold;
            color: #3B82F6;
            margin-top: 6px;
            letter-spacing: 1.5px;
        }

        .header p {
            margin: 8px 0 0;
            color: #6B7280;
            font-size: 8.5pt;
        }

        .header .report-id {
            display: inline-block;
            margin-top: 6px;
            padding: 2px 12px;
            background-color: #EFF6FF;
            border: 1px solid #BFDBFE;
            border-radius: 12px;
            font-size: 7.5pt;
            color: #1E40AF;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        /* ==================== SECTION TITLES ==================== */
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            color: #FFFFFF;
            margin-top: 20px;
            margin-bottom: 12px;
            background: linear-gradient(135deg, #1E3A8A 0%, #3B82F6 100%);
            padding: 8px 14px;
            border-radius: 4px;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            box-shadow: 0 2px 4px rgba(30, 58, 138, 0.2);
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

        /* ==================== TABLES ==================== */
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
            background: linear-gradient(180deg, #1E3A8A 0%, #1E40AF 100%);
            color: #FFFFFF;
            padding: 8px 6px;
            text-align: center;
            font-size: 8pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            border-bottom: 2px solid #1E3A8A;
        }

        table.main td {
            padding: 7px 6px;
            border-bottom: 1px solid #E2E8F0;
            text-align: center;
            vertical-align: middle;
        }

        table.main tr:nth-child(even) {
            background-color: #F8FAFC;
        }

        table.main tr:hover {
            background-color: #EFF6FF;
        }

        table.main tr:last-child td {
            border-bottom: none;
        }

        /* ==================== TEXT UTILITIES ==================== */
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

        .text-gray {
            color: #6B7280;
        }

        .text-sm {
            font-size: 7.5pt;
        }

        .text-xs {
            font-size: 7pt;
        }

        /* ==================== PROGRESS BAR ==================== */
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
            background: linear-gradient(90deg, currentColor 0%, currentColor 100%);
            transition: width 0.3s ease;
        }

        .bar-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 50%;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.3) 0%, transparent 100%);
            border-radius: 6px 6px 0 0;
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

        .metric-card .card-title {
            font-size: 7pt;
            color: #FFFFFF;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 3px 6px;
            border-radius: 3px;
            display: inline-block;
            margin-bottom: 8px;
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

        .metric-card.blue {
            background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%);
            border: 1px solid #93C5FD;
        }

        .metric-card.blue .card-title {
            background-color: #3B82F6;
        }

        .metric-card.indigo {
            background: linear-gradient(135deg, #EEF2FF 0%, #E0E7FF 100%);
            border: 1px solid #A5B4FC;
        }

        .metric-card.indigo .card-title {
            background-color: #4F46E5;
        }

        .metric-card.green {
            background: linear-gradient(135deg, #F0FDF4 0%, #DCFCE7 100%);
            border: 1px solid #86EFAC;
        }

        .metric-card.green .card-title {
            background-color: #059669;
        }

        .metric-card.red {
            background: linear-gradient(135deg, #FEF2F2 0%, #FEE2E2 100%);
            border: 1px solid #FCA5A5;
        }

        .metric-card.red .card-title {
            background-color: #DC2626;
        }

        .metric-card.amber {
            background: linear-gradient(135deg, #FFFBEB 0%, #FEF3C7 100%);
            border: 1px solid #FCD34D;
        }

        .metric-card.amber .card-title {
            background-color: #D97706;
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

        /* ==================== PREDICTION BLOCK ==================== */
        .prediction-block {
            background-color: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-left: 5px solid #8B5CF6;
            padding: 14px 16px;
            margin-bottom: 12px;
            font-size: 8.5pt;
            line-height: 1.6;
            text-align: justify;
            border-radius: 0 6px 6px 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .prediction-block .pred-title {
            font-weight: bold;
            color: #8B5CF6;
            font-size: 9pt;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding-bottom: 6px;
            border-bottom: 1px solid #EDE9FE;
        }

        .prediction-block.risk {
            border-left-color: #DC2626;
        }

        .prediction-block.risk .pred-title {
            color: #DC2626;
            border-bottom-color: #FEE2E2;
        }

        .prediction-block.potential {
            border-left-color: #059669;
        }

        .prediction-block.potential .pred-title {
            color: #059669;
            border-bottom-color: #D1FAE5;
        }

        .prediction-block.outlook {
            border-left-color: #2563EB;
        }

        .prediction-block.outlook .pred-title {
            color: #2563EB;
            border-bottom-color: #DBEAFE;
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

        .gauge-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 50%;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.25) 0%, transparent 100%);
        }

        /* ==================== STATUS BADGE ==================== */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 7pt;
            font-weight: bold;
            color: #FFFFFF;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .badge-success {
            background: linear-gradient(135deg, #059669 0%, #10B981 100%);
        }

        .badge-warning {
            background: linear-gradient(135deg, #D97706 0%, #F59E0B 100%);
        }

        .badge-danger {
            background: linear-gradient(135deg, #DC2626 0%, #EF4444 100%);
        }

        .badge-info {
            background: linear-gradient(135deg, #2563EB 0%, #3B82F6 100%);
        }

        /* ==================== PAGE BREAK ==================== */
        .page-break {
            page-break-before: always;
        }

        /* ==================== SIGN-OFF ==================== */
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

        /* ==================== RANK BADGE ==================== */
        .rank-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            font-size: 8pt;
            font-weight: bold;
            color: #FFFFFF;
        }

        .rank-1 {
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
        }

        .rank-2 {
            background: linear-gradient(135deg, #9CA3AF 0%, #6B7280 100%);
        }

        .rank-3 {
            background: linear-gradient(135deg, #D97706 0%, #92400E 100%);
        }

        .rank-other {
            background-color: #E2E8F0;
            color: #374151;
        }

        /* ==================== MOMENTUM ARROWS ==================== */
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

        /* ==================== PAGE NUMBER ==================== */
        .page-number {
            text-align: right;
            font-size: 7pt;
            color: #9CA3AF;
            margin-bottom: 5px;
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
        // --- 1. PREPARE DATA DASAR ---
        $totalTarget = $data['total_target'] ?? 0;
        $avgProgress = round($data['rata_rata_progress'] ?? 0, 1);
        $kpiSelesai = $data['kpi_selesai'] ?? 0;
        $kpiGagal = $data['kpi_gagal'] ?? 0;
        $kpiAktif = $data['kpi_aktif'] ?? 0;
        $distribusi = $data['distribusi_nilai'] ?? [];
        $karyawan = collect($data['karyawan_departemen'] ?? [])
            ->sortByDesc('rata_rata_progress')
            ->values()
            ->toArray();
        $targets = $data['daftar_target_kpi'] ?? [];
        if ($targets instanceof \Illuminate\Support\Collection) {
            $targets = $targets->toArray();
        }
        $monthlyData = $data['monthly_progress'] ?? [];
        $tahun = $data['tahun'] ?? now()->year;
        $namaDivisi = $data['nama_divisi'] ?? 'Divisi';

        // Hitung statistik tambahan
        $totalKaryawan = count($karyawan);
        $successRate = $totalTarget > 0 ? round(($kpiSelesai / $totalTarget) * 100, 1) : 0;
        $failRate = $totalTarget > 0 ? round(($kpiGagal / $totalTarget) * 100, 1) : 0;

        // Estimasi akhir tahun (proyeksi sederhana)
        $currentMonth = (int) date('m');
        $remainingMonths = max(1, 12 - $currentMonth);
        $growthFactor = $avgProgress >= 80 ? 1.15 : ($avgProgress >= 60 ? 1.1 : 1.05);
        $estimasiAkhir = min(100, round($avgProgress * $growthFactor, 1));
        $probabilitas =
            $avgProgress >= 85
                ? 'SANGAT TINGGI'
                : ($avgProgress >= 70
                    ? 'TINGGI'
                    : ($avgProgress >= 50
                        ? 'SEDANG'
                        : 'RENDAH'));
        $probColor =
            $avgProgress >= 85
                ? '#059669'
                : ($avgProgress >= 70
                    ? '#3B82F6'
                    : ($avgProgress >= 50
                        ? '#D97706'
                        : '#DC2626'));

        // --- 2. LOGIKA ZONA (Interval 20%) ---
        $p = $avgProgress;
        if ($p < 30) {
            $zona = 'ZONA BAHAYA KRITIS';
        } elseif ($p < 50) {
            $zona = 'ZONA WASPADA';
        } elseif ($p < 70) {
            $zona = 'ZONA TRANSISI';
        } elseif ($p < 90) {
            $zona = 'ZONA PERTUMBUHAN';
        } else {
            $zona = 'ZONA EXCELLENCE';
        }

        // --- 3. LOGIKA NARASI GRANULAR ---
        $msg = '';
        if ($p < 12) {
            $msg = 'memerlukan intervensi darurat dan evaluasi total terhadap kelayakan target. Fokus absolut minggu ini hanya pada penyelamatan 1 target paling kritis.';
        } elseif ($p < 14) {
            $msg = 'memerlukan eskalasi segera ke level manajemen. Identifikasi akar masalah utama dan ajukan realokasi sumber daya.';
        } elseif ($p < 16) {
            $msg = 'menunjukkan kinerja yang sangat jauh di bawah ekspektasi. Lakukan daily check-in dengan atasan dan susun action plan harian.';
        } elseif ($p < 18) {
            $msg = 'berisiko tinggi mengalami kegagalan total. Prioritaskan hanya target dengan dampak bisnis tertinggi.';
        } elseif ($p < 20) {
            $msg = 'membutuhkan turnaround strategy yang radikal. Evaluasi ulang apakah target yang ditetapkan realistis.';
        } elseif ($p < 22) {
            $msg = 'masih sangat rentan terhadap kegagalan. Tingkatkan intensitas kerja minimal 20% dan manfaatkan dukungan tim.';
        } elseif ($p < 24) {
            $msg = 'memerlukan akselerasi minimal 5-7% per bulan. Identifikasi 2 target quick wins untuk membangun momentum.';
        } elseif ($p < 26) {
            $msg = 'menunjukkan progress yang lambat. Terapkan teknik time-blocking dan hilangkan semua distraksi.';
        } elseif ($p < 28) {
            $msg = 'berada di ambang batas bawah yang mengkhawatirkan. Minta feedback konstruktif dari atasan mengenai area perbaikan.';
        } elseif ($p < 30) {
            $msg = 'mulai menunjukkan sedikit perbaikan namun masih jauh dari zona aman. Pertahankan ritme percepatan ini.';
        } elseif ($p < 32) {
            $msg = 'memerlukan perhatian serius agar tidak tergelincir kembali. Fokus pada konsistensi harian tanpa hari tanpa progress.';
        } elseif ($p < 34) {
            $msg = 'menunjukkan tanda-tanda stabilisasi namun akselerasi masih kurang. Tantang diri menyelesaikan 1 target tertunda.';
        } elseif ($p < 36) {
            $msg = 'berada di fase kritis menuju zona transisi. Optimalkan penggunaan tools untuk mempercepat tugas repetitif.';
        } elseif ($p < 38) {
            $msg = 'membutuhkan dorongan ekstra untuk menembus batas 40%. Lakukan review mingguan yang ketat.';
        } elseif ($p < 40) {
            $msg = 'sudah mulai menunjukkan pola kerja yang lebih terarah. Mulai delegasikan tugas-tugas minor.';
        } elseif ($p < 42) {
            $msg = 'mendekati titik tengah, namun masih memerlukan kewaspadaan tinggi. Susun strategi catch-up untuk 2 bulan ke depan.';
        } elseif ($p < 44) {
            $msg = 'menunjukkan konsistensi yang mulai terbentuk. Dokumentasikan setiap keberhasilan kecil sebagai motivasi.';
        } elseif ($p < 46) {
            $msg = 'hampir mencapai zona transisi yang lebih aman. Tingkatkan kolaborasi dengan rekan tim yang progressnya lebih baik.';
        } elseif ($p < 48) {
            $msg = 'berada di ujung zona peringatan, siap untuk lompatan performa. Fokus pada penyelesaian total 1-2 target.';
        } elseif ($p < 50) {
            $msg = 'telah mencapai titik tengah perjalanan. Momentum mulai terbentuk. Rayakan pencapaian ini lalu reset fokus.';
        } elseif ($p < 52) {
            $msg = 'memasuki fase yang lebih stabil dengan risiko kegagalan yang mulai menurun. Pertahankan disiplin kerja.';
        } elseif ($p < 54) {
            $msg = 'menunjukkan perbaikan yang nyata dan dapat diandalkan. Mulai antisipasi hambatan di 2 target berikutnya.';
        } elseif ($p < 56) {
            $msg = 'berada di jalur yang tepat untuk mencapai target akhir tahun. Tingkatkan kualitas output, bukan hanya kecepatan.';
        } elseif ($p < 58) {
            $msg = 'mendekati zona aman dengan performa yang semakin solid. Mulai berbagi tips efisiensi kepada rekan tim.';
        } elseif ($p < 60) {
            $msg = 'hampir mencapai batas aman. Dorong diri untuk menyelesaikan 1 target besar minggu ini sebagai bukti kapabilitas.';
        } elseif ($p < 62) {
            $msg = 'telah memasuki fase performa yang baik. Fokus pada optimalisasi proses untuk mencapai target dengan usaha lebih sedikit.';
        } elseif ($p < 64) {
            $msg = 'menunjukkan konsistensi tinggi dan manajemen waktu yang efektif. Ajukan diri untuk membantu tim lain.';
        } elseif ($p < 66) {
            $msg = 'berada di jalur yang sangat sehat. Lakukan self-assessment: skill apa yang perlu ditingkatkan untuk mempercepat progress?';
        } elseif ($p < 68) {
            $msg = 'menunjukkan potensi untuk menjadi top performer. Dokumentasikan workflow Anda yang sukses untuk bahan mentoring.';
        } elseif ($p < 70) {
            $msg = 'mendekati zona sangat baik dengan momentum positif yang kuat. Jangan puas, tantang diri menembus batas 75%.';
        } elseif ($p < 72) {
            $msg = 'telah membuktikan kapabilitas dengan hasil yang konsisten. Mulai bangun networking internal untuk memahami best practice.';
        } elseif ($p < 74) {
            $msg = 'berada di posisi yang sangat menguntungkan. Fokus pada penyelesaian target yang memiliki bobot nilai terbesar.';
        } elseif ($p < 76) {
            $msg = 'menunjukkan kematangan dalam mengelola beban kerja. Berikan apresiasi kepada diri sendiri dan tim atas kerja keras.';
        } elseif ($p < 78) {
            $msg = 'hampir mencapai zona excellent. Siapkan presentasi atau laporan ringkas tentang keberhasilan Anda untuk review kinerja.';
        } elseif ($p < 80) {
            $msg = 'berada di ambang batas performa tinggi. Identifikasi 1 stretch goal yang bisa Anda selesaikan di luar target wajib.';
        } elseif ($p < 82) {
            $msg = 'telah memasuki kategori performa tinggi yang menjadi benchmark divisi. Mulai pikirkan tentang pengembangan karir.';
        } elseif ($p < 84) {
            $msg = 'menunjukkan penguasaan (mastery) yang sangat baik. Jadilah mentor informal bagi rekan yang sedang kesulitan.';
        } elseif ($p < 86) {
            $msg = 'berada di jalur yang sangat mulus menuju 90%+. Eksplorasi inovasi atau otomatisasi untuk meningkatkan efisiensi tim.';
        } elseif ($p < 88) {
            $msg = 'menunjukkan konsistensi tingkat atas yang sangat jarang ditemukan. Pastikan semua dokumentasi sudah rapi.';
        } elseif ($p < 90) {
            $msg = 'hampir mencapai kesempurnaan dalam eksekusi. Mulai susun rencana strategis untuk periode berikutnya.';
        } elseif ($p < 92) {
            $msg = 'berada di puncak performa dengan proyeksi akhir tahun yang sangat cerah. Fokus pada penyempurnaan detail terakhir.';
        } elseif ($p < 94) {
            $msg = 'menunjukkan dedikasi dan eksekusi yang luar biasa. Komunikasikan pencapaian ini kepada stakeholder terkait.';
        } elseif ($p < 96) {
            $msg = 'hampir mencapai garis finish dengan hasil yang fantastis. Pastikan validasi data dengan atasan berjalan lancar.';
        } elseif ($p < 98) {
            $msg = 'berada di langkah terakhir menuju pencapaian sempurna. Selesaikan semua outstanding item sekecil apapun.';
        } else {
            $msg = 'telah mencapai atau melampaui ekspektasi dengan hasil yang luar biasa. Fokus beralih pada evaluasi pasca-proyek dan lessons learned.';
        }

        // --- 4. ASSEMBLE NARASI DINAMIS ---
        $validMonths = array_filter($monthlyData, fn($v) => $v !== null);
        $bestMonth = !empty($validMonths) ? array_search(max($validMonths), $validMonths) : null;
        $worstMonth = !empty($validMonths) ? array_search(min($validMonths), $validMonths) : null;
        $monthNames = [
            '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];

        $fluktuasiText = '';
        if ($bestMonth && $worstMonth) {
            $fluktuasiText =
                " Performa puncak tercatat di bulan {$monthNames[$bestMonth]} (" . round($validMonths[$bestMonth], 1) . "%), sementara titik terendah ada di {$monthNames[$worstMonth]} (" . round($validMonths[$worstMonth], 1) . "%), menunjukkan rentang fluktuasi sebesar " .
                round(max($validMonths) - min($validMonths), 1) . '%.';
        }

        // Narasi 1: Trajectory
        $trajectoryText = "Berdasarkan analisis tren performa, divisi <strong>{$namaDivisi}</strong> berada di <strong>{$zona}</strong> ({$avgProgress}%). Proyeksi akhir tahun diperkirakan mencapai <strong>{$estimasiAkhir}%</strong>. Kondisi ini {$msg}{$fluktuasiText}";

        // Narasi 2: Potential
        $potentialText = "Divisi ini memiliki potensi pertumbuhan yang signifikan dengan fondasi yang ada. Akselerasi menuju target 100% sangat mungkin dicapai jika strategi yang tepat dieksekusi dengan disiplin tinggi dan konsistensi penuh.";

        // Narasi 3: Risk
        $riskText = "Analisis risiko menunjukkan bahwa divisi berada di <strong>{$zona}</strong> ({$avgProgress}%). {$msg} Tingkat kegagalan saat ini adalah <strong>{$failRate}%</strong>, yang memerlukan mitigasi proaktif dan tindakan korektif segera.";

        // Narasi 4: Strategy
        $outlookText = "Rekomendasi strategis utama: {$msg} Selain itu, pastikan komunikasi dengan stakeholder tetap transparan dan lakukan penyesuaian sumber daya jika diperlukan untuk optimalisasi hasil.";

        // --- 5. INSIGHTS TAMBAHAN ---
        $insights = [];
        $insightType = 'info';

        if ($avgProgress >= 80) {
            $insights[] = "Divisi <strong>{$namaDivisi}</strong> menunjukkan performa <strong>SANGAT BAIK</strong> dengan rata-rata progress <strong>{$avgProgress}%</strong>.";
            $insightType = 'success';
        } elseif ($avgProgress >= 60) {
            $insights[] = "Divisi <strong>{$namaDivisi}</strong> berada dalam kondisi <strong>CUKUP</strong> dengan rata-rata progress <strong>{$avgProgress}%</strong>.";
            $insightType = 'warning';
        } else {
            $insights[] = "Divisi <strong>{$namaDivisi}</strong> membutuhkan <strong>INTERVENSI SEGERA</strong> dengan rata-rata progress hanya <strong>{$avgProgress}%</strong>.";
            $insightType = 'danger';
        }

        if ($kpiGagal > 0) {
            $insights[] = "Terdapat <strong>{$kpiGagal} target berstatus GAGAL</strong> ({$failRate}% dari total) yang memerlukan evaluasi mendalam dan tindakan korektif.";
        }

        if ($kpiSelesai > 0) {
            $insights[] = "Telah berhasil menyelesaikan <strong>{$kpiSelesai} target</strong> ({$successRate}% success rate) yang menunjukkan kapabilitas tim.";
        }

        if ($totalKaryawan > 0) {
            $topPerformer = $karyawan[0] ?? null;
            $bottomPerformer = end($karyawan) ?: null;
            if ($topPerformer && $bottomPerformer) {
                $insights[] = "Top performer: <strong>{$topPerformer['nama']}</strong> (" . round($topPerformer['rata_rata_progress'], 1) . "%).";
                if ($topPerformer['id_karyawan'] !== ($bottomPerformer['id_karyawan'] ?? null)) {
                    $insights[] = "Memerlukan pembinaan: <strong>{$bottomPerformer['nama']}</strong> (" . round($bottomPerformer['rata_rata_progress'], 1) . "%).";
                }
            }
        }
    @endphp

    <!-- ========================================== -->
    <!-- HALAMAN 1: EXECUTIVE SUMMARY -->
    <!-- ========================================== -->

    <div class="page-number">Halaman 1 dari 5</div>

    <!-- HEADER -->
    <div class="header">
        <div class="company-label">Sistem Monitoring KPI Perusahaan</div>
        <h1>Laporan Eksekutif KPI</h1>
        <div class="dept-name">{{ strtoupper($namaDivisi) }}</div>
        <p>Periode Evaluasi: Tahun Anggaran {{ $tahun }}</p>
        <div class="report-id">RPT-KPI-{{ strtoupper(substr($namaDivisi, 0, 3)) }}-{{ $tahun }}-{{ date('md') }}</div>
    </div>

    <!-- METRIC CARDS -->
    <table style="width: 100%; border-collapse: separate; border-spacing: 6px; margin-bottom: 16px;">
        <tr>
            <td style="width: 20%;">
                <div class="metric-card blue">
                    <div class="card-title">Total Target</div>
                    <div class="card-value">{{ $totalTarget }}</div>
                    <div class="card-sub">Seluruh KPI Divisi</div>
                </div>
            </td>
            <td style="width: 20%;">
                <div class="metric-card indigo">
                    <div class="card-title">Rata-rata Progress</div>
                    <div class="card-value">{{ number_format($avgProgress, 1) }}%</div>
                    <div class="card-sub">Keseluruhan Tim</div>
                </div>
            </td>
            <td style="width: 20%;">
                <div class="metric-card green">
                    <div class="card-title">Target Selesai</div>
                    <div class="card-value">{{ $kpiSelesai }}</div>
                    <div class="card-sub">{{ number_format($successRate, 1) }}% Success Rate</div>
                </div>
            </td>
            <td style="width: 20%;">
                <div class="metric-card red">
                    <div class="card-title">Target Gagal</div>
                    <div class="card-value">{{ $kpiGagal }}</div>
                    <div class="card-sub">{{ number_format($failRate, 1) }}% Failure Rate</div>
                </div>
            </td>
            <td style="width: 20%;">
                <div class="metric-card amber">
                    <div class="card-title">Target Aktif</div>
                    <div class="card-value">{{ $kpiAktif }}</div>
                    <div class="card-sub">Sedang Berjalan</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- INSIGHT BOX -->
    <div class="insight-box {{ $insightType }}">
        <strong>Executive Insight</strong>
        <ul>
            @foreach ($insights as $insight)
                <li>{!! $insight !!}</li>
            @endforeach
        </ul>
    </div>

    <!-- DISTRIBUSI GRADE -->
    <div class="section-title">Distribusi Grade Kinerja Target</div>
    <table class="main">
        <thead>
            <tr>
                <th style="width: 22%;">Grade</th>
                <th style="width: 12%;">Jumlah</th>
                <th style="width: 12%;">Persentase</th>
                <th style="width: 54%;">Visualisasi</th>
            </tr>
        </thead>
        <tbody>
            @php
                $gradeColors = [
                    'Sangat Baik' => '#059669',
                    'Baik' => '#3B82F6',
                    'Cukup' => '#D97706',
                    'Kurang' => '#DC2626',
                    'Sangat Kurang' => '#6B7280',
                ];
            @endphp
            @forelse ($distribusi as $grade => $count)
                @php
                    $pct = $totalTarget > 0 ? round(($count / $totalTarget) * 100, 1) : 0;
                    $color = $gradeColors[$grade] ?? '#6B7280';
                @endphp
                <tr>
                    <td class="text-left" style="color: {{ $color }}; font-weight: bold;">
                        ● {{ $grade }}
                    </td>
                    <td><strong>{{ $count }}</strong></td>
                    <td>{{ number_format($pct, 1) }}%</td>
                    <td style="text-align: left; padding: 8px 12px;">
                        <div class="bar-container">
                            <div class="bar-fill"
                                style="width: {{ $pct }}%; background-color: {{ $color }};"></div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="color: #6B7280; padding: 12px;">Tidak ada data distribusi grade.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- ========================================== -->
    <!-- HALAMAN 2: TREN BULANAN & PREDIKSI -->
    <!-- ========================================== -->
    <div class="page-break"></div>

    <div class="page-number">Halaman 2 dari 5</div>

    <div class="section-title">Tren Perjalanan Bulanan Divisi</div>
    <p style="font-size: 8pt; color: #6B7280; margin-top: -6px; margin-bottom: 12px; font-style: italic;">
        Rata-rata progress seluruh anggota divisi per bulan, dilengkapi analisis momentum (Δ) antar periode.
    </p>

    <table class="main">
        <thead>
            <tr>
                <th style="width: 14%;">Bulan</th>
                <th style="width: 14%;">Progress (%)</th>
                <th style="width: 14%;">Status</th>
                <th style="width: 14%;">Momentum (Δ)</th>
                <th style="width: 44%;">Visualisasi</th>
            </tr>
        </thead>
        <tbody>
            @php $prevVal = null; @endphp
            @for ($m = 1; $m <= 12; $m++)
                @php
                    $val = isset($monthlyData[$m]) ? round($monthlyData[$m], 1) : null;
                    $color = $val !== null ? ($val >= 80 ? '#059669' : ($val >= 60 ? '#D97706' : '#DC2626')) : '#CBD5E1';
                    $width = $val !== null ? $val . '%' : '0%';
                    $displayVal = $val !== null ? number_format($val, 1) . '%' : '—';
                    $status = $val === null ? '—' : ($val >= 80 ? 'Sangat Baik' : ($val >= 60 ? 'Cukup' : 'Perhatian'));
                    $sColor = $val === null ? '#CBD5E1' : ($val >= 80 ? '#059669' : ($val >= 60 ? '#D97706' : '#DC2626'));

                    if ($val === null || $prevVal === null) {
                        $deltaDisplay = '—';
                        $dColor = '#CBD5E1';
                        $momentumClass = 'momentum-flat';
                    } else {
                        $delta = round($val - $prevVal, 1);
                        $sign = $delta >= 0 ? '+' : '';
                        $deltaDisplay = $sign . number_format($delta, 1) . '%';
                        $dColor = $delta > 0 ? '#059669' : ($delta < 0 ? '#DC2626' : '#6B7280');
                        $momentumClass = $delta > 0 ? 'momentum-up' : ($delta < 0 ? 'momentum-down' : 'momentum-flat');
                    }
                    if ($val !== null) {
                        $prevVal = $val;
                    }
                @endphp
                <tr>
                    <td style="font-weight: bold; background-color: #EFF6FF;">{{ $monthNames[$m] }}</td>
                    <td style="font-weight: bold; color: {{ $color }};">{{ $displayVal }}</td>
                    <td>
                        @if ($val !== null)
                            <span class="badge {{ $val >= 80 ? 'badge-success' : ($val >= 60 ? 'badge-warning' : 'badge-danger') }}">
                                {{ $status }}
                            </span>
                        @else
                            <span class="text-gray text-sm">—</span>
                        @endif
                    </td>
                    <td class="{{ $momentumClass }}">
                        @if ($val !== null && $prevVal !== null && $m > 1)
                            @if ($delta > 0)▲@elseif ($delta < 0)▼@else●@endif {{ $deltaDisplay }}
                        @else
                            —
                        @endif
                    </td>
                    <td style="text-align: left; padding: 8px 12px;">
                        <div class="bar-container">
                            <div class="bar-fill"
                                style="width: {{ $width }}; background-color: {{ $color }};"></div>
                        </div>
                    </td>
                </tr>
            @endfor
        </tbody>
    </table>

    <!-- INSIGHT TREN BULANAN -->
    @if (!empty($validMonths))
        <div class="insight-box success">
            <strong>Analisis Tren Bulanan</strong>
            <ul>
                <li>Performa <strong>tertinggi</strong> dicapai pada bulan <strong>{{ $monthNames[$bestMonth] }}</strong> dengan progress <strong>{{ number_format(round(max($validMonths), 1), 1) }}%</strong>.</li>
                <li>Performa <strong>terendah</strong> tercatat pada bulan <strong>{{ $monthNames[$worstMonth] }}</strong> dengan progress <strong>{{ number_format(round(min($validMonths), 1), 1) }}%</strong>.</li>
                <li>Rentang fluktuasi performa: <strong>{{ number_format(round(max($validMonths) - min($validMonths), 1), 1) }}%</strong>
                    ({{ max($validMonths) - min($validMonths) > 20 ? 'Fluktuasi Tinggi — Perlu Stabilisasi' : 'Fluktuasi Wajar' }}).
                </li>
            </ul>
        </div>
    @endif

    <!-- PREDIKSI & POTENSI -->
    <div class="section-title">Analisis Prediksi & Potensi Strategis</div>

    <div class="prediction-block">
        <div class="pred-title">Trajectory Prediction</div>
        {!! $trajectoryText !!}
    </div>

    <div class="prediction-block potential">
        <div class="pred-title">Growth Potential</div>
        {!! $potentialText !!}
    </div>

    <div class="prediction-block risk">
        <div class="pred-title">Risk Forecast</div>
        {!! $riskText !!}
    </div>

    <div class="prediction-block outlook">
        <div class="pred-title">Strategic Outlook</div>
        {!! $outlookText !!}
    </div>

    <!-- METRIK PREDIKSI -->
    <table style="width: 100%; border-collapse: separate; border-spacing: 8px 0; margin-top: 16px;">
        <tr>
            <td style="width: 50%; vertical-align: top; padding-right: 4px;">
                <table class="main">
                    <thead>
                        <tr>
                            <th colspan="2">Metrik Prediksi Kuantitatif</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-left" style="font-weight: bold;">Progress Saat Ini</td>
                            <td style="text-align: right;"><strong>{{ number_format($avgProgress, 1) }}%</strong></td>
                        </tr>
                        <tr>
                            <td class="text-left" style="font-weight: bold;">Estimasi Akhir Tahun</td>
                            <td style="text-align: right; color: #2563EB;"><strong>{{ number_format($estimasiAkhir, 1) }}%</strong></td>
                        </tr>
                        <tr>
                            <td class="text-left" style="font-weight: bold;">Bulan Tersisa</td>
                            <td style="text-align: right;"><strong>{{ $remainingMonths }} Bulan</strong></td>
                        </tr>
                        <tr>
                            <td class="text-left" style="font-weight: bold;">Probabilitas Sukses</td>
                            <td style="text-align: right; color: {{ $probColor }};"><strong>{{ $probabilitas }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </td>
            <td style="width: 50%; vertical-align: top; padding-left: 4px;">
                <div style="padding: 14px; background: linear-gradient(135deg, #F8FAFC 0%, #EFF6FF 100%); border: 1px solid #BFDBFE; border-radius: 6px;">
                    <div style="font-weight: bold; color: #1E3A8A; margin-bottom: 10px; font-size: 9pt; text-transform: uppercase; letter-spacing: 0.5px;">🎛️ Gauge Prediksi</div>
                    <div class="gauge-container">
                        <div class="gauge-fill"
                            style="width: {{ max(15, $estimasiAkhir) }}%; background: linear-gradient(90deg, {{ $probColor }} 0%, {{ $probColor }}CC 100%);">
                            {{ number_format($estimasiAkhir, 1) }}%
                        </div>
                    </div>
                    <div style="font-size: 7.5pt; color: #6B7280; margin-top: 8px; text-align: center;">
                        Target Divisi: 100% | Gap: {{ number_format(max(0, 100 - $estimasiAkhir), 1) }}%
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- ========================================== -->
    <!-- HALAMAN 3: PERFORMA TIM -->
    <!-- ========================================== -->
    <div class="page-break"></div>

    <div class="page-number">Halaman 3 dari 5</div>

    <div class="section-title">Analisis Performa Anggota Divisi</div>
    <p style="font-size: 8pt; color: #6B7280; margin-top: -6px; margin-bottom: 12px; font-style: italic;">
        Ranking performa seluruh anggota divisi berdasarkan rata-rata progress KPI. Data diurutkan dari performa tertinggi ke terendah.
    </p>

    <table class="main">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 23%;">Nama Karyawan</th>
                <th style="width: 15%;">Jabatan</th>
                <th style="width: 9%;">Total KPI</th>
                <th style="width: 11%;">Progress</th>
                <th style="width: 8%;">Selesai</th>
                <th style="width: 8%;">Gagal</th>
                <th style="width: 21%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($karyawan as $index => $k)
                @php
                    $prog = round($k['rata_rata_progress'], 1);
                    $pColor = $prog >= 80 ? 'text-green' : ($prog >= 60 ? 'text-amber' : 'text-red');
                    $statusText = $prog >= 80 ? 'On Track' : ($prog >= 60 ? 'Perlu Dorongan' : 'Berbahaya');
                    $badgeClass = $prog >= 80 ? 'badge-success' : ($prog >= 60 ? 'badge-warning' : 'badge-danger');
                    $rankClass = $index < 3 ? 'rank-' . ($index + 1) : 'rank-other';
                    
                    // Logika Warna Baris
                    $isTop = $index < 3;
                    $isBottom = ($totalKaryawan - 1 - $index) < 3 && $index >= 3;

                    if ($isTop) {
                        $rowClass = 'row-top';
                    } elseif ($isBottom) {
                        $rowClass = 'row-bottom';
                    } else {
                        $rowClass = 'row-safe';
                    }
                @endphp
                <tr class="{{ $rowClass }}">
                    <td><span class="rank-badge {{ $rankClass }}">{{ $index + 1 }}</span></td>
                    <td class="text-left">
                        <strong>{{ $k['nama'] }}</strong>
                    </td>
                    <td class="text-sm">{{ $k['jabatan'] }}</td>
                    <td>{{ $k['jumlah_target'] }}</td>
                    <td class="{{ $pColor }}"><strong>{{ number_format($prog, 1) }}%</strong></td>
                    <td class="text-green">{{ $k['total_target_selesai'] }}</td>
                    <td class="text-red">{{ $k['total_target_gagal'] }}</td>
                    <td><span class="badge {{ $badgeClass }}">{{ $statusText }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="color: #6B7280; padding: 16px;">Belum ada data karyawan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- STATISTIK TIM -->
    @php
        $onTrackCount = collect($karyawan)->where('rata_rata_progress', '>=', 80)->count();
        $needPushCount = collect($karyawan)
            ->filter(fn($k) => $k['rata_rata_progress'] >= 60 && $k['rata_rata_progress'] < 80)
            ->count();
        $dangerCount = collect($karyawan)->where('rata_rata_progress', '<', 60)->count();
    @endphp

    <div class="insight-box" style="margin-top: 16px;">
        <strong>Komposisi Performa Tim</strong>
        <ul>
            <li><span class="text-green">{{ $onTrackCount }} karyawan</span> berada dalam kondisi <strong>On Track</strong> (≥80%)</li>
            <li><span class="text-amber">{{ $needPushCount }} karyawan</span> membutuhkan <strong>dorongan tambahan</strong> (60-79%)</li>
            <li><span class="text-red">{{ $dangerCount }} karyawan</span> dalam kondisi <strong>berbahaya</strong> (&lt;60%)</li>
        </ul>
    </div>

    <!-- ========================================== -->
    <!-- HALAMAN 4: DETAIL TARGET -->
    <!-- ========================================== -->
    <div class="page-break"></div>

    <div class="page-number">Halaman 4 dari 5</div>

    <div class="section-title">Daftar Lengkap Target KPI Divisi</div>
    <p style="font-size: 8pt; color: #6B7280; margin-top: -6px; margin-bottom: 12px; font-style: italic;">
        Seluruh target KPI yang menjadi tanggung jawab divisi beserta rata-rata progress pencapaian.
    </p>

    <table class="main">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 35%;">Judul Target</th>
                <th style="width: 13%;">Periode</th>
                <th style="width: 14%;">Progress</th>
                <th style="width: 16%;">Status</th>
                <th style="width: 17%;">Kategori</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($targets as $index => $t)
                @php
                    $prog = round($t['progress'] ?? 0, 1);
                    $pColor = $prog >= 80 ? 'text-green' : ($prog >= 60 ? 'text-amber' : 'text-red');
                    $s = strtolower($t['status'] ?? '');
                    $badgeClass = str_contains($s, 'selesai')
                        ? 'badge-success'
                        : (str_contains($s, 'gagal')
                            ? 'badge-danger'
                            : 'badge-warning');
                    $kategori =
                        $prog >= 100
                            ? 'Sangat Baik'
                            : ($prog >= 80
                                ? 'Baik'
                                : ($prog >= 70
                                    ? 'Cukup'
                                    : ($prog >= 60
                                        ? 'Kurang'
                                        : 'Sangat Kurang')));
                    $katColor = $prog >= 80 ? '#059669' : ($prog >= 70 ? '#3B82F6' : ($prog >= 60 ? '#D97706' : '#DC2626'));
                @endphp
                <tr>
                    <td><strong>{{ $index + 1 }}</strong></td>
                    <td class="text-left"><strong>{{ $t['judul'] }}</strong></td>
                    <td class="text-sm">{{ $t['periode'] }}</td>
                    <td class="{{ $pColor }}"><strong>{{ number_format($prog, 1) }}%</strong></td>
                    <td><span class="badge {{ $badgeClass }}">{{ $t['status'] }}</span></td>
                    <td style="color: {{ $katColor }}; font-weight: bold; font-size: 8pt;">{{ $kategori }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="color: #6B7280; padding: 16px;">Belum ada target KPI terdaftar.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- ========================================== -->
    <!-- HALAMAN 5: SIGN-OFF & FOOTER -->
    <!-- ========================================== -->
    <div class="page-break"></div>

    <div class="page-number">Halaman 5 dari 5</div>

    <div class="section-title">Pengesahan Laporan</div>
    <p style="font-size: 8pt; color: #6B7280; margin-top: -6px; margin-bottom: 20px; font-style: italic;">
        Laporan ini telah disusun berdasarkan data aktual sistem KPI dan siap untuk ditinjau serta disahkan oleh pihak berwenang.
    </p>

    <div class="sign-off-container">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td class="sign-cell">
                    <div class="sign-title">Disiapkan oleh</div>
                    <div class="sign-role">Manager {{ $namaDivisi }}</div>
                    <div class="sign-line"></div>
                    <div class="sign-name">( ..................................... )</div>
                    <div class="sign-date">Tanggal: {{ now()->format('d M Y') }}</div>
                </td>
                <td class="sign-cell">
                    <div class="sign-title">Disetujui oleh</div>
                    <div class="sign-role">HRD / General Manager</div>
                    <div class="sign-line"></div>
                    <div class="sign-name">( ..................................... )</div>
                    <div class="sign-date">Tanggal: ...........................</div>
                </td>
            </tr>
        </tr>
    </table>
    </div>

    <!-- NOTES -->
    <div class="notes-box">
        <strong>📌 Catatan Penting:</strong> Laporan ini digenerate secara otomatis oleh Sistem Monitoring KPI.
        Data yang tertera bersumber dari input aktual karyawan dan perhitungan sistem pada saat laporan dibuat.
        Setiap perubahan data setelah laporan ini digenerate tidak akan tercermin di dokumen ini.
    </div>

    <!-- FOOTER -->
    <div class="footer">
        <strong>LAPORAN EKSEKUTIF KPI — {{ strtoupper($namaDivisi) }}</strong><br>
        Tahun Anggaran {{ $tahun }} &bull; Digenerate: {{ now()->format('d M Y, H:i') }} WIB<br>
        <em>Dokumen ini bersifat rahasia dan hanya untuk kepentingan internal perusahaan.</em>
    </div>

</body>

</html>