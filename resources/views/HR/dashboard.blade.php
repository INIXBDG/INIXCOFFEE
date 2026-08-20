@extends('layout_HR.app')

@section('content_HR')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --pri: #4f46e5;
            --pri-light: #eef2ff;
            --pri-dark: #3730a3;
            --success: #059669;
            --success-light: #d1fae5;
            --warning: #f59e0b;
            --warning-light: #fef3c7;
            --info: #0284c7;
            --info-light: #e0f2fe;
            --danger: #dc2626;
            --danger-light: #fee2e2;
            --purple: #8b5cf6;
            --purple-light: #ede9fe;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --radius: 14px;
            --shadow: 0 1px 3px rgba(0,0,0,.05), 0 1px 2px rgba(0,0,0,.03);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,.08), 0 2px 4px -2px rgba(0,0,0,.05);
            --shadow-lg: 0 10px 25px -5px rgba(0,0,0,.1);
        }

        body {
            background: #f5f7fb;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .dash-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #ec4899 100%);
            border-radius: var(--radius);
            padding: 2rem 2rem 5rem;
            color: white;
            position: relative;
            overflow: hidden;
            margin-bottom: -3.5rem;
        }

        .dash-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: rgba(255,255,255,.08);
            border-radius: 50%;
        }

        .dash-header::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: 20%;
            width: 250px;
            height: 250px;
            background: rgba(255,255,255,.05);
            border-radius: 50%;
        }

        .greeting-emoji {
            font-size: 2.5rem;
            display: inline-block;
            animation: wave 2.5s ease-in-out infinite;
        }

        @keyframes wave {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(15deg); }
            75% { transform: rotate(-15deg); }
        }

        .greeting-title {
            font-size: 2rem;
            font-weight: 700;
            margin: 0.5rem 0 0.25rem;
        }

        .greeting-sub {
            opacity: 0.9;
            font-size: 1rem;
        }

        .live-clock {
            background: rgba(255,255,255,.15);
            backdrop-filter: blur(10px);
            padding: 0.5rem 1rem;
            border-radius: 30px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .live-dot {
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.3); }
        }

        .card-panel {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 1.5rem;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .card-panel-title {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-panel-title i {
            color: var(--pri);
        }

        .big-stat {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .big-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--gray-900);
            line-height: 1;
        }

        .big-number .unit {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-500);
            margin-left: 0.25rem;
        }

        .trend-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.6rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .trend-badge.up { background: var(--success-light); color: var(--success); }
        .trend-badge.down { background: var(--danger-light); color: var(--danger); }
        .trend-badge.neutral { background: var(--gray-100); color: var(--gray-600); }

        .mini-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
            padding-top: 1rem;
            border-top: 1px solid var(--gray-200);
        }

        .mini-stat-item {
            text-align: center;
        }

        .mini-stat-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--gray-900);
            line-height: 1;
        }

        .mini-stat-label {
            font-size: 0.7rem;
            color: var(--gray-500);
            margin-top: 0.25rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .composition-bars {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .comp-bar {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .comp-bar-label {
            width: 70px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--gray-700);
        }

        .comp-bar-track {
            flex: 1;
            height: 10px;
            background: var(--gray-100);
            border-radius: 5px;
            overflow: hidden;
            position: relative;
        }

        .comp-bar-fill {
            height: 100%;
            border-radius: 5px;
            transition: width 1s ease;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 8px;
            font-size: 0.7rem;
            color: white;
            font-weight: 700;
        }

        .comp-bar-count {
            width: 40px;
            text-align: right;
            font-weight: 700;
            color: var(--gray-900);
            font-size: 0.9rem;
        }

        .funnel-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem;
            border-radius: 10px;
            margin-bottom: 0.5rem;
            background: var(--gray-50);
            transition: all 0.2s;
        }

        .funnel-item:hover {
            background: var(--pri-light);
            transform: translateX(3px);
        }

        .funnel-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
            font-size: 0.9rem;
        }

        .funnel-info {
            flex: 1;
            min-width: 0;
        }

        .funnel-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--gray-800);
        }

        .funnel-progress {
            height: 5px;
            background: var(--gray-200);
            border-radius: 3px;
            overflow: hidden;
            margin-top: 4px;
        }

        .funnel-progress-fill {
            height: 100%;
            border-radius: 3px;
            transition: width 0.8s ease;
        }

        .funnel-count {
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--gray-900);
        }

        .today-status-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
        }

        .today-status-item {
            background: var(--gray-50);
            border-radius: 10px;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-left: 4px solid;
        }

        .today-status-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
        }

        .today-status-count {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--gray-900);
            line-height: 1;
        }

        .today-status-label {
            font-size: 0.75rem;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-weight: 600;
        }

        .person-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            max-height: 320px;
            overflow-y: auto;
        }

        .person-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem;
            border-radius: 10px;
            transition: background 0.2s;
        }

        .person-item:hover {
            background: var(--gray-50);
        }

        .person-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #ec4899);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            flex-shrink: 0;
            overflow: hidden;
        }

        .person-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .person-info {
            flex: 1;
            min-width: 0;
        }

        .person-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-900);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .person-meta {
            font-size: 0.72rem;
            color: var(--gray-500);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .person-badge {
            padding: 0.2rem 0.6rem;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .insight-card {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 0.75rem;
            display: flex;
            gap: 0.85rem;
            align-items: flex-start;
            border-left: 4px solid;
        }

        .insight-card.success { background: var(--success-light); border-left-color: var(--success); }
        .insight-card.warning { background: var(--warning-light); border-left-color: var(--warning); }
        .insight-card.danger { background: var(--danger-light); border-left-color: var(--danger); }
        .insight-card.info { background: var(--info-light); border-left-color: var(--info); }
        .insight-card.neutral { background: var(--gray-100); border-left-color: var(--gray-400); }

        .insight-icon-wrap {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
        }

        .insight-content { flex: 1; min-width: 0; }

        .insight-title {
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .insight-metric {
            font-size: 0.8rem;
            font-weight: 800;
            padding: 0.15rem 0.5rem;
            border-radius: 10px;
            background: rgba(255,255,255,0.7);
        }

        .insight-message {
            font-size: 0.8rem;
            color: var(--gray-700);
            line-height: 1.5;
        }

        .event-item {
            display: flex;
            gap: 0.75rem;
            padding: 0.75rem;
            border-radius: 10px;
            margin-bottom: 0.5rem;
            background: var(--gray-50);
            transition: all 0.2s;
            border-left: 3px solid;
        }

        .event-item:hover {
            background: var(--pri-light);
            transform: translateX(3px);
        }

        .event-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
            font-size: 0.85rem;
        }

        .event-content { flex: 1; min-width: 0; }

        .event-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--gray-900);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .event-sub {
            font-size: 0.72rem;
            color: var(--gray-500);
        }

        .event-date {
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--gray-700);
            white-space: nowrap;
        }

        .empty-state {
            text-align: center;
            padding: 2rem 1rem;
            color: var(--gray-400);
        }

        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            opacity: 0.3;
        }

        .filter-inline {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-inline select {
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            padding: 0.5rem 0.85rem;
            font-size: 0.85rem;
            background: white;
            color: var(--gray-700);
            font-weight: 500;
        }

        .filter-inline select:focus {
            border-color: var(--pri);
            outline: none;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .filter-inline .filter-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--gray-600);
            margin-right: 0.25rem;
        }

        .btn-refresh {
            background: white;
            border: 1px solid var(--gray-200);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            color: var(--gray-700);
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.2s;
            cursor: pointer;
        }

        .btn-refresh:hover {
            background: var(--pri);
            color: white;
            border-color: var(--pri);
        }

        .divider-dashed {
            border-top: 1px dashed var(--gray-200);
            margin: 1rem 0;
        }

        .chart-container {
            position: relative;
            height: 220px;
        }

        .chart-container-sm {
            position: relative;
            height: 180px;
        }

        .quick-nav {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        .quick-nav-item {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: 12px;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--gray-700);
            transition: all 0.2s;
        }

        .quick-nav-item:hover {
            border-color: var(--pri);
            background: var(--pri-light);
            color: var(--pri);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .quick-nav-item i {
            font-size: 1.25rem;
        }

        .quick-nav-title {
            font-size: 0.85rem;
            font-weight: 600;
        }

        .quick-nav-sub {
            font-size: 0.7rem;
            color: var(--gray-500);
        }

        .loading-skeleton {
            background: linear-gradient(90deg, var(--gray-100) 25%, var(--gray-50) 50%, var(--gray-100) 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 8px;
        }

        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        .weekend-banner {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: white;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .big-number { font-size: 2rem; }
            .greeting-title { font-size: 1.5rem; }
            .today-status-grid { grid-template-columns: 1fr; }
            .mini-stats { grid-template-columns: repeat(3, 1fr); gap: 0.5rem; }
            .mini-stat-value { font-size: 1rem; }
        }

        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 3px; }
    </style>

    <div class="container-fluid px-3 px-md-4 py-3">
        <div class="dash-header">
            <div class="d-flex justify-content-between align-items-start position-relative" style="z-index:2">
                <div>
                    <div class="greeting-emoji" id="greetEmoji">☀️</div>
                    <h1 class="greeting-title" id="greetText">Selamat Pagi!</h1>
                    <p class="greeting-sub" id="greetSub">-</p>
                </div>
                <div class="text-end">
                    <div class="live-clock">
                        <span class="live-dot"></span>
                        <span id="liveClock">--:--</span>
                    </div>
                    <div style="margin-top:0.5rem;font-size:0.85rem;opacity:0.9" id="todayDate">-</div>
                </div>
            </div>
        </div>

        <div class="filter-inline" style="margin-top: 1rem;">
            <span class="filter-label"><i class="fa-solid fa-filter me-1"></i>Filter:</span>
            <select id="filterTahun">
                @for ($y = date('Y'); $y >= 2023; $y--)
                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <select id="filterDivisi">
                <option value="all">Semua Divisi</option>
            </select>
            <button class="btn-refresh" id="btnRefresh">
                <i class="fa-solid fa-arrows-rotate"></i> Refresh
            </button>
            <div style="margin-left:auto;font-size:0.75rem;color:var(--gray-500)">
                <i class="fa-regular fa-clock"></i> Update: <span id="lastUpdate">-</span>
            </div>
        </div>

        <div id="dashboardContent">
            <div class="row g-3">
                <div class="col-md-6 col-lg-3"><div class="card-panel"><div class="loading-skeleton" style="height:180px"></div></div></div>
                <div class="col-md-6 col-lg-3"><div class="card-panel"><div class="loading-skeleton" style="height:180px"></div></div></div>
                <div class="col-md-6 col-lg-3"><div class="card-panel"><div class="loading-skeleton" style="height:180px"></div></div></div>
                <div class="col-md-6 col-lg-3"><div class="card-panel"><div class="loading-skeleton" style="height:180px"></div></div></div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let charts = {};

        $(document).ready(function() {
            loadDivisions();
            loadDashboard();
            startClock();
            $('#btnRefresh').on('click', loadDashboard);
            $('#filterTahun, #filterDivisi').on('change', loadDashboard);
        });

        function startClock() {
            const update = () => {
                const now = new Date();
                $('#liveClock').text(now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }));
            };
            update();
            setInterval(update, 1000);
        }

        function loadDivisions() {
            $.get("{{ route('HR.dashboard.divisions') }}", function(data) {
                data.forEach(d => $('#filterDivisi').append(`<option value="${d}">${d}</option>`));
            });
        }

        function formatRp(n) {
            if (n >= 1000000000) return 'Rp ' + (n / 1000000000).toFixed(1) + ' M';
            if (n >= 1000000) return 'Rp ' + (n / 1000000).toFixed(1) + ' Jt';
            if (n >= 1000) return 'Rp ' + (n / 1000).toFixed(0) + ' Rb';
            return 'Rp ' + Number(n).toLocaleString('id-ID');
        }

        function getInitial(name) {
            if (!name) return '?';
            return name.split(' ').slice(0, 2).map(n => n[0] || '').join('').toUpperCase();
        }

        function renderAvatar(foto, nama) {
            if (foto) return `<img src="/storage/${foto}" alt="${nama}">`;
            return getInitial(nama);
        }

        function loadDashboard() {
            $('#btnRefresh i').addClass('fa-spin');
            $.get("{{ route('HR.dashboard.data') }}", {
                tahun: $('#filterTahun').val(),
                divisi: $('#filterDivisi').val()
            }, function(res) {
                if (!res.success) { alert(res.message); return; }
                renderDashboard(res);
                $('#lastUpdate').text(new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }));
            }).fail(() => alert('Gagal memuat dashboard')).always(() => {
                $('#btnRefresh i').removeClass('fa-spin');
            });
        }

        function renderDashboard(d) {
            $('#greetEmoji').text(d.header.greeting.emoji);
            $('#greetText').text(d.header.greeting.text + '!');
            $('#greetSub').text(d.header.greeting.sub);
            $('#todayDate').text(d.header.today);

            let html = '';
            html += renderMainStats(d);
            html += '<div class="row g-3 mt-3">';
            html += renderComposition(d.composition);
            html += renderRecruitment(d.recruitment);
            html += '</div>';
            html += '<div class="row g-3 mt-3">';
            html += renderTodayDigest(d.today);
            html += renderInsights(d.insights);
            html += '</div>';
            html += '<div class="row g-3 mt-3">';
            html += renderMonthlyAttendance(d.attendance_trend);
            html += renderDivisionChart(d.divisions);
            html += '</div>';
            html += '<div class="row g-3 mt-3">';
            html += renderRecentHires(d.recent_hires);
            html += renderRecentResigns(d.recent_resigns);
            html += renderUpcoming(d.upcoming);
            html += '</div>';
            html += renderQuickNav();

            $('#dashboardContent').html(html);
            setTimeout(() => initCharts(d), 100);
        }

        function renderMainStats(d) {
            const c = d.composition, a = d.attendance, f = d.financial, r = d.recruitment;

            const attTrendClass = a.vs_last_month >= 0 ? 'up' : 'down';
            const attTrendIcon = a.vs_last_month >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
            const finTrendClass = f.month_change >= 0 ? 'up' : 'down';
            const finTrendIcon = f.month_change >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';

            return `
                <div class="row g-3">
                    <div class="col-md-6 col-lg-3">
                        <div class="card-panel">
                            <div class="card-panel-title"><i class="fa-solid fa-users"></i> Total Karyawan</div>
                            <div class="big-stat">
                                <div>
                                    <div class="big-number">${c.total}<span class="unit">org</span></div>
                                    <div style="font-size:0.75rem;color:var(--gray-500);margin-top:0.25rem">
                                        <span style="color:var(--success)">+${c.new_this_year} baru</span> · 
                                        <span style="color:var(--danger)">${c.resign_this_year} resign</span>
                                    </div>
                                </div>
                                <span class="trend-badge ${c.retention_rate >= 85 ? 'up' : 'neutral'}">
                                    <i class="fa-solid fa-shield"></i> ${c.retention_rate}%
                                </span>
                            </div>
                            <div class="mini-stats">
                                <div class="mini-stat-item">
                                    <div class="mini-stat-value" style="color:var(--success)">${c.tetap}</div>
                                    <div class="mini-stat-label">Tetap</div>
                                </div>
                                <div class="mini-stat-item">
                                    <div class="mini-stat-value" style="color:var(--info)">${c.kontrak}</div>
                                    <div class="mini-stat-label">Kontrak</div>
                                </div>
                                <div class="mini-stat-item">
                                    <div class="mini-stat-value" style="color:var(--warning)">${c.probation}</div>
                                    <div class="mini-stat-label">Probation</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="card-panel">
                            <div class="card-panel-title"><i class="fa-solid fa-user-check"></i> Kehadiran ${a.month_name}</div>
                            <div class="big-stat">
                                <div>
                                    <div class="big-number">${a.attendance_rate}<span class="unit">%</span></div>
                                    <div style="font-size:0.75rem;color:var(--gray-500);margin-top:0.25rem">
                                        ${a.total_hadir} dari ${a.expected_records} shift kerja
                                    </div>
                                </div>
                                <span class="trend-badge ${attTrendClass}">
                                    <i class="fa-solid ${attTrendIcon}"></i> ${Math.abs(a.vs_last_month)}%
                                </span>
                            </div>
                            <div class="mini-stats">
                                <div class="mini-stat-item">
                                    <div class="mini-stat-value" style="color:var(--success)">${a.punctuality_rate}%</div>
                                    <div class="mini-stat-label">Tepat Waktu</div>
                                </div>
                                <div class="mini-stat-item">
                                    <div class="mini-stat-value" style="color:var(--warning)">${a.telat}</div>
                                    <div class="mini-stat-label">Telat</div>
                                </div>
                                <div class="mini-stat-item">
                                    <div class="mini-stat-value" style="color:var(--danger)">${a.avg_late_minutes}<span style="font-size:0.7rem">m</span></div>
                                    <div class="mini-stat-label">Rata² Telat</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="card-panel">
                            <div class="card-panel-title"><i class="fa-solid fa-file-invoice-dollar"></i> SPJ ${f.month_name}</div>
                            <div class="big-stat">
                                <div>
                                    <div class="big-number" style="font-size:1.8rem">${formatRp(f.total_month)}</div>
                                    <div style="font-size:0.75rem;color:var(--gray-500);margin-top:0.25rem">
                                        ${f.count_month} transaksi · Avg ${formatRp(f.avg_per_spj)}
                                    </div>
                                </div>
                                <span class="trend-badge ${finTrendClass}">
                                    <i class="fa-solid ${finTrendIcon}"></i> ${Math.abs(f.month_change)}%
                                </span>
                            </div>
                            <div class="mini-stats">
                                <div class="mini-stat-item">
                                    <div class="mini-stat-value" style="font-size:1rem">${formatRp(f.total_year)}</div>
                                    <div class="mini-stat-label">Total Tahun Ini</div>
                                </div>
                                <div class="mini-stat-item">
                                    <div class="mini-stat-value">${f.count_year}</div>
                                    <div class="mini-stat-label">Total SPJ</div>
                                </div>
                                <div class="mini-stat-item">
                                    <div class="mini-stat-value" style="font-size:1rem">${formatRp(f.last_month)}</div>
                                    <div class="mini-stat-label">Bulan Lalu</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="card-panel">
                            <div class="card-panel-title">
                                <i class="fa-solid fa-user-plus"></i> Recruitment Pipeline
                                <span class="trend-badge ${r.trend_percent >= 0 ? 'up' : 'down'}" style="margin-left:auto">
                                    <i class="fa-solid ${r.trend_percent >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'}"></i> ${Math.abs(r.trend_percent)}%
                                </span>
                            </div>
                            <div class="big-stat">
                                <div>
                                    <div class="big-number">${r.aktif}<span class="unit">aktif</span></div>
                                    <div style="font-size:0.75rem;color:var(--gray-500);margin-top:0.25rem">
                                        ${r.this_month} pelamar baru bulan ini
                                    </div>
                                </div>
                                <span class="trend-badge up" style="background:var(--success-light);color:var(--success)">
                                    <i class="fa-solid fa-bullseye"></i> ${r.conversion_rate}%
                                </span>
                            </div>
                            <div class="mini-stats">
                                <div class="mini-stat-item">
                                    <div class="mini-stat-value">${r.total}</div>
                                    <div class="mini-stat-label">Total</div>
                                </div>
                                <div class="mini-stat-item">
                                    <div class="mini-stat-value" style="color:var(--success)">${r.stages[4].count}</div>
                                    <div class="mini-stat-label">Hired</div>
                                </div>
                                <div class="mini-stat-item">
                                    <div class="mini-stat-value" style="color:var(--danger)">${r.rejected}</div>
                                    <div class="mini-stat-label">Ditolak</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function renderComposition(c) {
            const total = c.total || 1;
            const bars = [
                { label: 'Tetap', count: c.tetap, color: '#059669' },
                { label: 'Kontrak', count: c.kontrak, color: '#0284c7' },
                { label: 'Probation', count: c.probation, color: '#f59e0b' },
            ];

            const tenureYears = Math.floor(c.avg_tenure_months / 12);
            const tenureMonths = c.avg_tenure_months % 12;
            const tenureStr = tenureYears > 0 ? `${tenureYears} thn ${tenureMonths} bln` : `${tenureMonths} bln`;

            return `
                <div class="col-lg-5">
                    <div class="card-panel">
                        <div class="card-panel-title"><i class="fa-solid fa-chart-pie"></i> Komposisi Karyawan</div>
                        <div class="row align-items-center">
                            <div class="col-5">
                                <div class="chart-container-sm"><canvas id="chartComposition"></canvas></div>
                            </div>
                            <div class="col-7">
                                <div class="composition-bars">
                                    ${bars.map(b => {
                                        const pct = Math.round((b.count / total) * 100);
                                        return `
                                            <div class="comp-bar">
                                                <div class="comp-bar-label">${b.label}</div>
                                                <div class="comp-bar-track">
                                                    <div class="comp-bar-fill" style="width:${pct}%;background:${b.color}">${pct > 15 ? pct + '%' : ''}</div>
                                                </div>
                                                <div class="comp-bar-count">${b.count}</div>
                                            </div>
                                        `;
                                    }).join('')}
                                </div>
                                <div class="divider-dashed"></div>
                                <div style="display:flex;justify-content:space-between;font-size:0.8rem">
                                    <div>
                                        <div style="color:var(--gray-500);font-size:0.7rem;text-transform:uppercase">Avg Masa Kerja</div>
                                        <div style="font-weight:700;color:var(--gray-900)">${tenureStr}</div>
                                    </div>
                                    <div style="text-align:right">
                                        <div style="color:var(--gray-500);font-size:0.7rem;text-transform:uppercase">Retention</div>
                                        <div style="font-weight:700;color:var(--success)">${c.retention_rate}%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function renderRecruitment(r) {
            return `
                <div class="col-lg-7">
                    <div class="card-panel">
                        <div class="card-panel-title">
                            <i class="fa-solid fa-filter"></i> Recruitment Funnel
                            <span style="margin-left:auto;font-size:0.75rem;color:var(--gray-500)">Conversion: <strong style="color:var(--success)">${r.conversion_rate}%</strong></span>
                        </div>
                        ${r.stages.map(s => `
                            <div class="funnel-item">
                                <div class="funnel-icon" style="background:${s.color}">
                                    <i class="fa-solid ${s.icon}"></i>
                                </div>
                                <div class="funnel-info">
                                    <div class="funnel-label">${s.label}</div>
                                    <div class="funnel-progress">
                                        <div class="funnel-progress-fill" style="width:${s.percentage}%;background:${s.color}"></div>
                                    </div>
                                </div>
                                <div class="funnel-count">${s.count}</div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        function renderTodayDigest(t) {
            const banner = t.is_weekend ? `
                <div class="weekend-banner">
                    <i class="fa-solid fa-mug-hot" style="font-size:1.3rem"></i>
                    <div>Hari ini ${t.day_name} — Weekend. Data menunjukkan aktivitas minimal.</div>
                </div>
            ` : (t.is_holiday ? `
                <div class="weekend-banner" style="background:linear-gradient(135deg,#ef4444,#dc2626)">
                    <i class="fa-solid fa-calendar-xmark" style="font-size:1.3rem"></i>
                    <div>Hari ini libur nasional. Tidak ada aktivitas kerja.</div>
                </div>
            ` : '');

            const statusItems = [
                { label: 'Hadir', count: t.total_hadir, icon: 'fa-user-check', color: 'var(--success)', bg: 'var(--success-light)', border: 'var(--success)' },
                { label: 'Terlambat', count: t.total_telat, icon: 'fa-clock', color: 'var(--warning)', bg: 'var(--warning-light)', border: 'var(--warning)' },
                { label: 'Cuti / Sakit', count: t.total_cuti, icon: 'fa-umbrella-beach', color: 'var(--info)', bg: 'var(--info-light)', border: 'var(--info)' },
                { label: 'Izin 3 Jam', count: t.total_izin, icon: 'fa-hourglass-half', color: 'var(--purple)', bg: 'var(--purple-light)', border: 'var(--purple)' },
            ];

            const telatList = t.telat_list.length > 0 ? t.telat_list.map(e => `
                <div class="person-item">
                    <div class="person-avatar">${renderAvatar(e.foto, e.nama)}</div>
                    <div class="person-info">
                        <div class="person-name">${e.nama}</div>
                        <div class="person-meta">${e.jabatan} · Masuk ${e.jam_masuk || '-'}</div>
                    </div>
                    <span class="person-badge" style="background:var(--warning-light);color:var(--warning)">+${e.late_minutes}m</span>
                </div>
            `).join('') : '<div class="empty-state"><i class="fa-solid fa-check-circle"></i><p>Tidak ada keterlambatan hari ini</p></div>';

            const cutiList = t.cuti_list.length > 0 ? t.cuti_list.map(c => `
                <div class="person-item">
                    <div class="person-avatar">${renderAvatar(c.foto, c.nama)}</div>
                    <div class="person-info">
                        <div class="person-name">${c.nama}</div>
                        <div class="person-meta">${c.jabatan}</div>
                    </div>
                    <span class="person-badge" style="background:var(--info-light);color:var(--info)">${c.tipe}</span>
                </div>
            `).join('') : '<div class="empty-state"><i class="fa-regular fa-calendar"></i><p>Tidak ada cuti hari ini</p></div>';

            const interviewList = t.interview_list.length > 0 ? t.interview_list.map(i => `
                <div class="person-item">
                    <div class="person-avatar" style="background:linear-gradient(135deg,#0284c7,#3b82f6)"><i class="fa-solid fa-user-tie"></i></div>
                    <div class="person-info">
                        <div class="person-name">${i.nama}</div>
                        <div class="person-meta">${i.jabatan} · ${i.metode}</div>
                    </div>
                    <span class="person-badge" style="background:var(--info-light);color:var(--info)">${i.waktu}</span>
                </div>
            `).join('') : '<div class="empty-state"><i class="fa-regular fa-calendar-check"></i><p>Tidak ada interview</p></div>';

            return `
                <div class="col-lg-8">
                    <div class="card-panel">
                        <div class="card-panel-title">
                            <i class="fa-solid fa-calendar-day"></i> Ringkasan Hari Ini
                            <span style="margin-left:auto;font-size:0.8rem;color:var(--gray-500);font-weight:600">${t.day_name}, ${t.date}</span>
                        </div>
                        ${banner}
                        <div class="today-status-grid">
                            ${statusItems.map(s => `
                                <div class="today-status-item" style="border-left-color:${s.border}">
                                    <div class="today-status-icon" style="background:${s.color}"><i class="fa-solid ${s.icon}"></i></div>
                                    <div>
                                        <div class="today-status-count" style="color:${s.color}">${s.count}</div>
                                        <div class="today-status-label">${s.label}</div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                        <div class="divider-dashed"></div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div style="font-size:0.75rem;font-weight:700;color:var(--gray-600);text-transform:uppercase;margin-bottom:0.5rem">
                                    <i class="fa-solid fa-clock" style="color:var(--warning)"></i> Yang Telat
                                </div>
                                <div class="person-list">${telatList}</div>
                            </div>
                            <div class="col-md-4">
                                <div style="font-size:0.75rem;font-weight:700;color:var(--gray-600);text-transform:uppercase;margin-bottom:0.5rem">
                                    <i class="fa-solid fa-umbrella-beach" style="color:var(--info)"></i> Cuti Hari Ini
                                </div>
                                <div class="person-list">${cutiList}</div>
                            </div>
                            <div class="col-md-4">
                                <div style="font-size:0.75rem;font-weight:700;color:var(--gray-600);text-transform:uppercase;margin-bottom:0.5rem">
                                    <i class="fa-solid fa-calendar-check" style="color:var(--info)"></i> Interview Hari Ini
                                </div>
                                <div class="person-list">${interviewList}</div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function renderInsights(insights) {
            const colorMap = {
                success: { bg: 'var(--success)', light: 'var(--success-light)', text: 'var(--success)' },
                warning: { bg: 'var(--warning)', light: 'var(--warning-light)', text: 'var(--warning)' },
                danger: { bg: 'var(--danger)', light: 'var(--danger-light)', text: 'var(--danger)' },
                info: { bg: 'var(--info)', light: 'var(--info-light)', text: 'var(--info)' },
                neutral: { bg: 'var(--gray-500)', light: 'var(--gray-100)', text: 'var(--gray-700)' },
            };

            const body = insights.length > 0 ? insights.map(i => {
                const c = colorMap[i.type] || colorMap.neutral;
                return `
                    <div class="insight-card ${i.type}">
                        <div class="insight-icon-wrap" style="background:${c.bg}"><i class="fa-solid ${i.icon}"></i></div>
                        <div class="insight-content">
                            <div class="insight-title">
                                <span>${i.title}</span>
                                <span class="insight-metric" style="color:${c.text}">${i.metric}</span>
                            </div>
                            <div class="insight-message">${i.message}</div>
                        </div>
                    </div>
                `;
            }).join('') : '<div class="empty-state"><i class="fa-solid fa-sparkles"></i><p>Semua metrik terlihat baik!</p></div>';

            return `
                <div class="col-lg-4">
                    <div class="card-panel">
                        <div class="card-panel-title"><i class="fa-solid fa-lightbulb"></i> Insights & Rekomendasi</div>
                        ${body}
                    </div>
                </div>
            `;
        }

        function renderMonthlyAttendance(data) {
            return `
                <div class="col-lg-7">
                    <div class="card-panel">
                        <div class="card-panel-title"><i class="fa-solid fa-chart-line"></i> Tren Kehadiran 12 Bulan</div>
                        <div class="chart-container"><canvas id="chartAttendanceTrend"></canvas></div>
                    </div>
                </div>
            `;
        }

        function renderDivisionChart(d) {
            return `
                <div class="col-lg-5">
                    <div class="card-panel">
                        <div class="card-panel-title"><i class="fa-solid fa-building"></i> Distribusi per Divisi (${d.total_categories})</div>
                        <div class="chart-container"><canvas id="chartDivision"></canvas></div>
                    </div>
                </div>
            `;
        }

        function renderRecentHires(list) {
            const body = list.length > 0 ? list.map(e => `
                <div class="person-item">
                    <div class="person-avatar">${renderAvatar(e.foto, e.nama)}</div>
                    <div class="person-info">
                        <div class="person-name">${e.nama}</div>
                        <div class="person-meta">${e.jabatan} · ${e.divisi}</div>
                    </div>
                    <div style="text-align:right">
                        <div style="font-size:0.75rem;font-weight:700;color:var(--success)">${e.tanggal}</div>
                        <div style="font-size:0.68rem;color:var(--gray-500)">${e.days_ago} hari lalu</div>
                    </div>
                </div>
            `).join('') : '<div class="empty-state"><i class="fa-solid fa-user-plus"></i><p>Belum ada karyawan baru</p></div>';

            return `
                <div class="col-md-6 col-lg-4">
                    <div class="card-panel">
                        <div class="card-panel-title"><i class="fa-solid fa-user-plus" style="color:var(--success)"></i> Karyawan Baru Terbaru</div>
                        <div class="person-list">${body}</div>
                    </div>
                </div>
            `;
        }

        function renderRecentResigns(list) {
            const body = list.length > 0 ? list.map(e => `
                <div class="person-item">
                    <div class="person-avatar" style="background:linear-gradient(135deg,#dc2626,#991b1b)">${renderAvatar(e.foto, e.nama)}</div>
                    <div class="person-info">
                        <div class="person-name">${e.nama}</div>
                        <div class="person-meta">${e.jabatan} · ${e.alasan !== '-' ? e.alasan.substring(0, 30) : 'Tidak ada alasan'}</div>
                    </div>
                    <div style="text-align:right">
                        <div style="font-size:0.75rem;font-weight:700;color:var(--danger)">${e.tanggal}</div>
                        <div style="font-size:0.68rem;color:var(--gray-500)">${e.days_ago} hari lalu</div>
                    </div>
                </div>
            `).join('') : '<div class="empty-state"><i class="fa-solid fa-user-minus"></i><p>Tidak ada yang resign</p></div>';

            return `
                <div class="col-md-6 col-lg-4">
                    <div class="card-panel">
                        <div class="card-panel-title"><i class="fa-solid fa-user-minus" style="color:var(--danger)"></i> Karyawan Resign Terbaru</div>
                        <div class="person-list">${body}</div>
                    </div>
                </div>
            `;
        }

        function renderUpcoming(events) {
            const body = events.length > 0 ? events.map(e => `
                <div class="event-item" style="border-left-color:${e.color}">
                    <div class="event-icon" style="background:${e.color}"><i class="fa-solid ${e.icon}"></i></div>
                    <div class="event-content">
                        <div class="event-title">${e.title}</div>
                        <div class="event-sub">${e.subtitle}</div>
                    </div>
                    <div class="event-date">${e.datetime}</div>
                </div>
            `).join('') : '<div class="empty-state"><i class="fa-regular fa-calendar"></i><p>Tidak ada agenda mendatang</p></div>';

            return `
                <div class="col-md-12 col-lg-4">
                    <div class="card-panel">
                        <div class="card-panel-title"><i class="fa-solid fa-calendar-week" style="color:var(--purple)"></i> Agenda 14 Hari ke Depan</div>
                        <div class="person-list">${body}</div>
                    </div>
                </div>
            `;
        }

        function renderQuickNav() {
            const items = [
                { title: 'Data Karyawan', sub: 'Kelola data SDM', icon: 'fa-users', url: '/HR-dashboard/employee/index' },
                { title: 'Kehadiran', sub: 'Absensi & analitik', icon: 'fa-calendar-check', url: '/HR-dashboard/absensi' },
                { title: 'Recruitment', sub: 'Pipeline pelamar', icon: 'fa-user-plus', url: '/HR-dashboard/hire' },
                { title: 'Rekap SPJ', sub: 'Pengeluaran dinas', icon: 'fa-file-invoice-dollar', url: '/HR-dashboard/rekap-spj' },
            ];

            return `
                <div class="quick-nav">
                    ${items.map(i => `
                        <a href="${i.url}" class="quick-nav-item">
                            <i class="fa-solid ${i.icon}"></i>
                            <div>
                                <div class="quick-nav-title">${i.title}</div>
                                <div class="quick-nav-sub">${i.sub}</div>
                            </div>
                        </a>
                    `).join('')}
                </div>
            `;
        }

        function initCharts(d) {
            Object.values(charts).forEach(c => c?.destroy());
            charts = {};

            const chartFont = { family: "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto", size: 11 };

            if (document.getElementById('chartComposition')) {
                charts.composition = new Chart(document.getElementById('chartComposition'), {
                    type: 'doughnut',
                    data: {
                        labels: d.composition.composition_chart.labels,
                        datasets: [{
                            data: d.composition.composition_chart.data,
                            backgroundColor: d.composition.composition_chart.colors,
                            borderWidth: 3,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: ctx => {
                                        const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                        const pct = total > 0 ? ((ctx.raw / total) * 100).toFixed(1) : 0;
                                        return `${ctx.label}: ${ctx.raw} (${pct}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            if (document.getElementById('chartAttendanceTrend')) {
                charts.trend = new Chart(document.getElementById('chartAttendanceTrend'), {
                    type: 'line',
                    data: {
                        labels: d.attendance_trend.map(t => t.month),
                        datasets: [
                            {
                                label: 'Tepat Waktu',
                                data: d.attendance_trend.map(t => t.hadir),
                                borderColor: '#059669',
                                backgroundColor: 'rgba(5, 150, 105, 0.1)',
                                fill: true,
                                tension: 0.4,
                                borderWidth: 2.5,
                                pointRadius: 3
                            },
                            {
                                label: 'Terlambat',
                                data: d.attendance_trend.map(t => t.telat),
                                borderColor: '#dc2626',
                                backgroundColor: 'rgba(220, 38, 38, 0.1)',
                                fill: true,
                                tension: 0.4,
                                borderWidth: 2.5,
                                pointRadius: 3
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { font: chartFont, usePointStyle: true, padding: 15 } }
                        },
                        scales: {
                            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: chartFont } },
                            x: { grid: { display: false }, ticks: { font: chartFont } }
                        }
                    }
                });
            }

            if (document.getElementById('chartDivision')) {
                charts.division = new Chart(document.getElementById('chartDivision'), {
                    type: 'bar',
                    data: {
                        labels: d.divisions.labels,
                        datasets: [{
                            label: 'Jumlah Karyawan',
                            data: d.divisions.data,
                            backgroundColor: d.divisions.colors,
                            borderRadius: 8,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: chartFont } },
                            y: { grid: { display: false }, ticks: { font: { ...chartFont, weight: '600' } } }
                        }
                    }
                });
            }
        }
    </script>
@endsection