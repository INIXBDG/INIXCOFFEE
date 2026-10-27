@extends('layout_HR.app')

@section('content_HR')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --pri: #4f46e5;
            --pri-light: #eef2ff;
            --pri-dark: #3730a3;
            --success: #059669;
            --success-light: #d1fae5;
            --warning: #d97706;
            --warning-light: #fef3c7;
            --info: #0284c7;
            --info-light: #e0f2fe;
            --danger: #dc2626;
            --danger-light: #fee2e2;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-400: #9ca3af;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-900: #111827;
            --radius: 10px;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, .08), 0 1px 2px rgba(0, 0, 0, .05);
            --shadow: 0 4px 6px rgba(0, 0, 0, .07), 0 2px 4px rgba(0, 0, 0, .05);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, .1), 0 4px 10px rgba(0, 0, 0, .07);
        }

        body {
            background: #fafbfc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .page-header {
            margin-bottom: 1.5rem;
        }

        .page-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: .15rem;
        }

        .page-sub {
            color: var(--gray-400);
            font-size: .875rem;
        }

        .stat-card {
            border: none;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            transition: transform .25s, box-shadow .25s;
            background: #fff;
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--pri-light);
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: #fff;
        }

        .stat-value {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--gray-900);
            margin: .4rem 0 .15rem;
            line-height: 1.1;
        }

        .stat-label {
            color: var(--gray-400);
            font-size: .78rem;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: .5px;
            font-weight: 600;
        }

        .nav-tabs-custom {
            border-bottom: 2px solid var(--gray-200);
        }

        .nav-tabs-custom .nav-link {
            border: none;
            color: var(--gray-400);
            font-weight: 600;
            padding: .85rem 1.25rem;
            font-size: .875rem;
            transition: color .2s;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .nav-tabs-custom .nav-link:hover {
            color: var(--pri);
        }

        .nav-tabs-custom .nav-link.active {
            color: var(--pri);
            border-bottom: 3px solid var(--pri);
            background: transparent;
        }

        .nav-tabs-custom .nav-link .tab-count {
            background: var(--gray-100);
            color: var(--gray-600);
            font-size: .7rem;
            padding: 2px 8px;
            border-radius: 10px;
            font-weight: 700;
        }

        .nav-tabs-custom .nav-link.active .tab-count {
            background: var(--pri-light);
            color: var(--pri);
        }

        .card-shell {
            border: none;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            background: #fff;
        }

        .card-shell .card-body {
            padding: 1.5rem;
        }

        .btn-pri {
            background: var(--pri);
            border: none;
            color: #fff;
            font-weight: 600;
            padding: .5rem 1.25rem;
            border-radius: 8px;
            transition: all .25s;
            font-size: .85rem;
        }

        .btn-pri:hover {
            background: var(--pri-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, .35);
            color: #fff;
        }

        .btn-outline-sec {
            background: #fff;
            border: 1px solid var(--gray-200);
            color: var(--gray-600);
            font-weight: 500;
            padding: .4rem 1rem;
            border-radius: 8px;
            transition: all .2s;
            font-size: .85rem;
        }

        .btn-outline-sec:hover {
            background: var(--gray-50);
            border-color: var(--gray-400);
            color: var(--gray-900);
        }

        .chart-wrap {
            position: relative;
            height: 300px;
        }

        .chart-title {
            font-size: .875rem;
            font-weight: 700;
            color: var(--gray-700);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .chart-title i {
            color: var(--pri);
        }

        .filter-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: var(--pri-light);
            color: var(--pri);
            padding: .4rem .85rem;
            border-radius: 20px;
            font-size: .78rem;
            font-weight: 600;
        }

        .table-modern {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        .table-modern thead th {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--gray-600);
            background: var(--gray-50);
            border-bottom: 2px solid var(--gray-200) !important;
            border-top: none !important;
            padding: 0.85rem 1rem;
        }

        .table-modern tbody tr {
            transition: background .15s;
        }

        .table-modern tbody tr:hover {
            background: var(--pri-light) !important;
        }

        .table-modern tbody td {
            vertical-align: middle;
            font-size: .875rem;
            border-bottom: 1px solid var(--gray-100) !important;
            border-top: none !important;
            padding: 0.85rem 1rem;
            color: var(--gray-700);
        }

        .status-badge {
            padding: .35rem .75rem;
            border-radius: 20px;
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .status-active {
            background: var(--success-light);
            color: var(--success);
        }

        .status-resign {
            background: var(--gray-100);
            color: var(--gray-600);
        }

        .form-control,
        .form-select {
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            padding: .5rem .85rem;
            font-size: .875rem;
            color: var(--gray-700);
            transition: all .2s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--pri);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, .12);
            outline: none;
        }

        .form-label {
            font-weight: 600;
            color: var(--gray-700);
            font-size: .78rem;
            margin-bottom: .4rem;
            text-transform: uppercase;
            letter-spacing: .3px;
        }

        .pagination-custom {
            display: flex;
            gap: 0.25rem;
            flex-wrap: wrap;
        }

        .pagination-custom button {
            padding: 0.35rem 0.75rem;
            font-size: 0.8rem;
            border-radius: 6px;
            border: 1px solid var(--gray-200);
            background: #fff;
            color: var(--gray-600);
            cursor: pointer;
            transition: all 0.15s;
            font-weight: 600;
        }

        .pagination-custom button:hover {
            background: var(--pri-light);
            color: var(--pri);
            border-color: var(--pri);
        }

        .pagination-custom button.active {
            background: var(--pri);
            color: white;
            border-color: var(--pri);
        }

        .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
        }

        .modal-header-custom {
            background: linear-gradient(135deg, var(--pri) 0%, var(--pri-dark) 100%);
            color: #fff;
            border-radius: 12px 12px 0 0;
            padding: 1.1rem 1.5rem;
        }

        .modal-header-custom .modal-title {
            font-weight: 700;
            font-size: 1rem;
        }

        .modal-header-custom .btn-close {
            filter: brightness(0) invert(1);
        }

        .gauge-wrap {
            position: relative;
            width: 160px;
            height: 160px;
            margin: 0 auto 1rem;
        }

        .gauge-value {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }

        .gauge-value .num {
            font-size: 2rem;
            font-weight: 700;
            color: var(--gray-900);
            line-height: 1;
        }

        .gauge-value .unit {
            font-size: .9rem;
            color: var(--gray-400);
            font-weight: 600;
        }

        .emp-list-item {
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            padding: 1rem 1.15rem;
            margin-bottom: .6rem;
            background: #fff;
            transition: all .2s;
        }

        .emp-list-item:hover {
            border-color: var(--pri);
            background: var(--pri-light);
            transform: translateX(3px);
        }

        .emp-name {
            font-weight: 700;
            color: var(--gray-900);
            font-size: .9rem;
        }

        .emp-meta {
            color: var(--gray-400);
            font-size: .78rem;
        }

        .breakdown-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: .6rem 0;
            border-bottom: 1px dashed var(--gray-200);
        }

        .breakdown-item:last-child {
            border-bottom: none;
        }

        .breakdown-label {
            min-width: 140px;
            font-weight: 600;
            color: var(--gray-700);
            font-size: .85rem;
        }

        .breakdown-bar-wrap {
            flex: 1;
            height: 8px;
            background: var(--gray-100);
            border-radius: 4px;
            overflow: hidden;
        }

        .breakdown-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--pri), #7c3aed);
            border-radius: 4px;
            transition: width .6s ease;
        }

        .breakdown-value {
            min-width: 50px;
            text-align: right;
            font-weight: 700;
            color: var(--pri);
            font-size: .85rem;
        }

        .insight-card {
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            padding: .85rem 1rem;
            margin-bottom: .5rem;
            font-size: .85rem;
            color: var(--gray-700);
            display: flex;
            align-items: flex-start;
            gap: .6rem;
        }

        .insight-card i {
            color: var(--success);
            margin-top: 2px;
        }

        .insight-card.rec i {
            color: var(--pri);
        }

        .summary-mini {
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
        }

        .summary-mini .value {
            font-size: 1.4rem;
            font-weight: 700;
            line-height: 1.1;
        }

        .summary-mini .label {
            font-size: .72rem;
            color: var(--gray-400);
            text-transform: uppercase;
            letter-spacing: .5px;
            font-weight: 600;
            margin-top: .3rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--gray-400);
        }

        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            opacity: .4;
            display: block;
        }

        .empty-state p {
            font-size: .9rem;
            margin: 0;
            font-weight: 500;
        }

        .section-title {
            font-size: .85rem;
            font-weight: 700;
            color: var(--gray-700);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .section-title i {
            color: var(--pri);
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a1a1c1;
        }

        @media (max-width: 767px) {
            .stat-value {
                font-size: 1.3rem;
            }

            .chart-wrap {
                height: 240px;
            }
        }

        #dt-space-reserver {
            height: 50px;
            width: 100%;
            display: block;
        }

        .skeleton-row {
            height: 95px;
        }

        .skeleton-cell {
            background: linear-gradient(90deg, var(--gray-100) 25%, var(--gray-200) 50%, var(--gray-100) 75%);
            background-size: 200% 100%;
            animation: skeleton-pulse 1.5s infinite;
            border-radius: 6px;
            height: 18px;
            width: 100%;
            display: block;
        }

        .skeleton-cell.short {
            width: 60%;
        }

        .skeleton-cell.circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            margin: 0 auto;
            display: block;
        }

        @keyframes skeleton-pulse {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        .skel-line {
            background: linear-gradient(90deg, var(--gray-100) 25%, var(--gray-200) 50%, var(--gray-100) 75%);
            background-size: 200% 100%;
            animation: skeleton-pulse 1.5s infinite;
            border-radius: 6px;
            display: block;
            width: 100%;
        }

        .skel-line+.skel-line {
            margin-top: 6px;
        }

        #page-skeleton,
        #page-skeleton * {
            pointer-events: none;
        }

        .rem-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: .9rem 1.1rem;
            border: 1px solid var(--gray-200);
            border-left: 4px solid var(--gray-400);
            border-radius: 8px;
            background: #fff;
            margin-bottom: .6rem;
        }

        .rem-item.overdue {
            border-left-color: var(--danger);
        }

        .rem-item.urgent {
            border-left-color: var(--warning);
        }

        .rem-item.upcoming {
            border-left-color: var(--info);
        }

        .rem-days {
            min-width: 92px;
            text-align: center;
            border-radius: 8px;
            padding: .4rem .5rem;
            font-weight: 700;
            font-size: .8rem;
            line-height: 1.2;
        }

        .rem-item.overdue .rem-days {
            background: var(--danger-light);
            color: var(--danger);
        }

        .rem-item.urgent .rem-days {
            background: var(--warning-light);
            color: var(--warning);
        }

        .rem-item.upcoming .rem-days {
            background: var(--info-light);
            color: var(--info);
        }

        .rem-phase {
            font-size: .66rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: .2rem .5rem;
            border-radius: 10px;
            background: var(--pri-light);
            color: var(--pri);
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: .75rem 1.5rem;
        }

        .detail-grid .k {
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .3px;
            color: var(--gray-400);
        }

        .detail-grid .v {
            font-size: .88rem;
            color: var(--gray-900);
            font-weight: 500;
            word-break: break-word;
        }

        .timeline-row {
            display: flex;
            justify-content: space-between;
            padding: .55rem .8rem;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            margin-bottom: .4rem;
            font-size: .85rem;
        }

        .guide-section {
            margin-bottom: 2rem;
        }

        .guide-section h5 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--gray-900);
            border-left: 4px solid var(--pri);
            padding-left: .75rem;
            margin-bottom: .75rem;
        }

        .guide-section ol,
        .guide-section ul {
            padding-left: 1.25rem;
            margin-bottom: .75rem;
        }

        .guide-section li {
            margin-bottom: .4rem;
            font-size: .9rem;
            color: var(--gray-700);
            line-height: 1.55;
        }

        .guide-note {
            background: var(--pri-light);
            border-radius: 8px;
            padding: .75rem 1rem;
            font-size: .85rem;
            color: var(--gray-700);
        }

        .guide-example {
            background: var(--gray-50);
            border: 1px dashed var(--gray-400);
            border-radius: 8px;
            padding: 1rem;
            font-size: .85rem;
        }

        .guide-itsm {
            background: var(--danger-light);
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 1.25rem;
        }
    </style>

    <div class="container-fluid px-4 py-4">

        <div id="page-skeleton">
            <div class="d-sm-flex align-items-center justify-content-between page-header">
                <div>
                    <span class="skel-line" style="width:260px;height:26px"></span>
                    <span class="skel-line" style="width:200px;height:14px"></span>
                </div>
                <div class="text-end">
                    <span class="skel-line" style="width:110px;height:12px;margin-left:auto"></span>
                    <span class="skel-line" style="width:150px;height:16px;margin-left:auto"></span>
                </div>
            </div>

            <div class="card card-shell mb-4">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="skel-line" style="width:120px;height:16px"></span>
                        <span class="skel-line" style="width:80px;height:30px;border-radius:8px"></span>
                    </div>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3 col-sm-6">
                            <span class="skel-line" style="width:80px;height:11px;margin-bottom:8px"></span>
                            <span class="skel-line" style="height:34px;border-radius:8px"></span>
                        </div>
                        <div class="col-md-4 col-sm-12">
                            <span class="skel-line" style="width:80px;height:11px;margin-bottom:8px"></span>
                            <span class="skel-line" style="height:34px;border-radius:8px"></span>
                        </div>
                        <div class="col-md-3 col-sm-12">
                            <span class="skel-line" style="width:100px;height:11px;margin-bottom:8px"></span>
                            <span class="skel-line" style="height:30px;border-radius:20px"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                @for ($i = 0; $i < 4; $i++)
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card h-100" style="cursor:default">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div style="flex:1">
                                    <span class="skel-line" style="width:90px;height:10px;margin-bottom:10px"></span>
                                    <span class="skel-line" style="width:60px;height:22px"></span>
                                </div>
                                <span class="skel-line"
                                    style="width:52px;height:52px;border-radius:12px;flex-shrink:0"></span>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>

            <div class="d-flex gap-3 mb-4 pb-3" style="border-bottom:2px solid var(--gray-200)">
                <span class="skel-line" style="width:170px;height:20px"></span>
                <span class="skel-line" style="width:150px;height:20px"></span>
                <span class="skel-line" style="width:160px;height:20px"></span>
            </div>

            @for ($c = 0; $c < 2; $c++)
                <div class="card card-shell mb-4">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                            <span class="skel-line" style="width:180px;height:16px"></span>
                            <div class="d-flex gap-2">
                                <span class="skel-line" style="width:70px;height:30px;border-radius:8px"></span>
                                <span class="skel-line" style="width:70px;height:30px;border-radius:8px"></span>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <span class="skel-line" style="width:70%;height:11px;margin-bottom:8px"></span>
                                <span class="skel-line" style="height:32px;border-radius:8px"></span>
                            </div>
                            <div class="col-md-3">
                                <span class="skel-line" style="width:70%;height:11px;margin-bottom:8px"></span>
                                <span class="skel-line" style="height:32px;border-radius:8px"></span>
                            </div>
                            <div class="col-md-3">
                                <span class="skel-line" style="width:70%;height:11px;margin-bottom:8px"></span>
                                <span class="skel-line" style="height:32px;border-radius:8px"></span>
                            </div>
                            <div class="col-md-3">
                                <span class="skel-line" style="height:32px;border-radius:8px;margin-top:20px"></span>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            @for ($m = 0; $m < 4; $m++)
                                <div class="col-6 col-md-3">
                                    <span class="skel-line" style="height:60px;border-radius:8px"></span>
                                </div>
                            @endfor
                        </div>
                        <span class="skel-line" style="height:300px;border-radius:10px"></span>
                    </div>
                </div>
            @endfor
        </div>

        <div id="page-real-content" class="d-none">

            <div class="d-sm-flex align-items-center justify-content-between page-header">
                <div>
                    <h1 class="page-title"><i class="fa-solid fa-users me-2" style="color:var(--pri)"></i>Informasi Karyawan</h1>
                    <p class="page-sub mb-0">Kelola dan pantau data SDM perusahaan Anda</p>
                </div>
                <div class="text-end">
                    <button class="btn btn-outline-primary btn-sm" onclick="openUserGuide()">
                        <i class="fa-solid fa-circle-question me-1"></i>Panduan
                    </button>
                    <small class="text-muted d-block mt-2">Terakhir update:</small>
                    <div class="fw-semibold" style="font-size:.85rem;color:var(--gray-700)" id="last-update">
                        {{ now()->format('d M Y, H:i') }}
                    </div>
                </div>
            </div>

            <div class="card card-shell mb-4">
                <div class="card-body py-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                        <span class="fw-bold" style="font-size:.875rem;color:var(--pri)"><i
                                class="fa-solid fa-filter me-1"></i>Filter Data</span>
                        <button class="btn btn-outline-sec btn-sm" id="btn-reset-filter"><i
                                class="fa-solid fa-rotate me-1"></i>Reset</button>
                    </div>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3 col-sm-6">
                            <label class="form-label">Periode Data</label>
                            <select name="periode" id="periode" class="form-select form-select-sm">
                                <option value="all">Tanpa Filter</option>
                                <option value="12">12 Bulan Terakhir</option>
                                <option value="6">6 Bulan Terakhir</option>
                                <option value="3">3 Bulan Terakhir</option>
                                <option value="year">Pilih Tahun</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6 d-none" id="year-selector">
                            <label class="form-label">Tahun</label>
                            <select name="year" id="year" class="form-select form-select-sm">
                                @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4 col-sm-12">
                            <label class="form-label">Pencarian</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0"><i
                                        class="fa-solid fa-search text-muted"></i></span>
                                <input type="text" id="search-employee" class="form-control border-start-0"
                                    placeholder="Cari nama, NIP, jabatan...">
                                <button class="btn btn-pri" type="button" id="btn-search"><i
                                        class="fa-solid fa-magnifying-glass"></i></button>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-12">
                            <span class="filter-badge" id="active-filter-badge">
                                <i class="fa-solid fa-circle-info"></i>
                                <span id="filter-label">Menampilkan: Semua Data</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card h-100" data-modal="modal-active">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="stat-label">Karyawan Active</p>
                                <h3 class="stat-value" id="stat-active">-</h3>
                            </div>
                            <div class="stat-icon" style="background:linear-gradient(135deg,#4f46e5,#7c3aed)"><i
                                    class="fa-solid fa-user-check"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card h-100" data-modal="modal-new">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="stat-label">Karyawan Baru</p>
                                <h3 class="stat-value" id="stat-new" style="color:var(--success)">-</h3>
                            </div>
                            <div class="stat-icon" style="background:linear-gradient(135deg,#059669,#10b981)"><i
                                    class="fa-solid fa-user-plus"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card h-100" data-modal="modal-resign">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="stat-label">Karyawan Resign</p>
                                <h3 class="stat-value" id="stat-resign" style="color:var(--gray-600)">-</h3>
                            </div>
                            <div class="stat-icon" style="background:linear-gradient(135deg,#6b7280,#9ca3af)"><i
                                    class="fa-solid fa-user-minus"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card h-100" data-modal="modal-retention">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="stat-label">Tingkat Retensi</p>
                                <h3 class="stat-value" id="stat-retention" style="color:var(--info)">-<small
                                        style="font-size:.9rem">%</small></h3>
                            </div>
                            <div class="stat-icon" style="background:linear-gradient(135deg,#0284c7,#38bdf8)"><i
                                    class="fa-solid fa-chart-line"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <ul class="nav nav-tabs nav-tabs-custom mb-4" id="mainTabs">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabOverview">
                        <i class="fa-solid fa-chart-pie"></i>Overview & Analytics
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabData">
                        <i class="fa-solid fa-table"></i>Data Karyawan
                        <span class="tab-count" id="tabDataCount">0</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabResign">
                        <i class="fa-solid fa-user-minus"></i>Karyawan Resign
                        <span class="tab-count" id="tabResignCount">0</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabReminder">
                        <i class="fa-solid fa-bell"></i>Reminder
                        <span class="tab-count" id="tabReminderCount">0</span>
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="tabOverview">
                    <div class="card card-shell mb-4">
                        <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                                <div class="chart-title mb-0"><i class="fa-solid fa-chart-line"></i>Headcount Trend</div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-sec btn-sm" id="btn-export-trend-csv"><i
                                            class="fa-solid fa-file-excel me-1"></i>Excel</button>
                                    <button class="btn btn-outline-sec btn-sm" id="btn-export-trend-pdf"><i
                                            class="fa-solid fa-file-pdf me-1"></i>PDF</button>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <label class="form-label">Tanggal Mulai</label>
                                    <input type="date" id="trend-start-date" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tanggal Akhir</label>
                                    <input type="date" id="trend-end-date" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Group By</label>
                                    <select id="trend-group-by" class="form-select form-select-sm">
                                        <option value="month">Bulanan</option>
                                        <option value="quarter">Triwulan</option>
                                        <option value="year">Tahunan</option>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button class="btn btn-pri btn-sm w-100" id="btn-apply-trend-filter"><i
                                            class="fa-solid fa-check me-1"></i>Terapkan</button>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-6 col-md-3">
                                    <div class="summary-mini">
                                        <div class="value" style="color:var(--pri)" id="trend-total-active">-</div>
                                        <div class="label">Total Active</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="summary-mini">
                                        <div class="value" style="color:var(--success)" id="trend-total-new">-</div>
                                        <div class="label">Total New</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="summary-mini">
                                        <div class="value" style="color:var(--gray-600)" id="trend-total-resign">-</div>
                                        <div class="label">Total Resign</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="summary-mini">
                                        <div class="value" style="color:var(--info)" id="trend-avg-new">-</div>
                                        <div class="label">Avg New/Bulan</div>
                                    </div>
                                </div>
                            </div>

                            <div class="chart-wrap"><canvas id="trendChart"></canvas></div>
                        </div>
                    </div>

                    <div class="card card-shell mb-4">
                        <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                                <div class="chart-title mb-0"><i class="fa-solid fa-chart-column"></i>Headcount Breakdown
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-sec btn-sm" id="btn-export-breakdown-csv"><i
                                            class="fa-solid fa-file-excel me-1"></i>Excel</button>
                                    <button class="btn btn-outline-sec btn-sm" id="btn-export-breakdown-pdf"><i
                                            class="fa-solid fa-file-pdf me-1"></i>PDF</button>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <label class="form-label">Filter By</label>
                                    <select id="breakdown-filter-by" class="form-select form-select-sm">
                                        <option value="divisi">Divisi</option>
                                        <option value="jabatan">Jabatan</option>
                                        <option value="gender">Gender</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select id="breakdown-status" class="form-select form-select-sm">
                                        <option value="all">Semua</option>
                                        <option value="active">Active</option>
                                        <option value="resign">Resign</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Min. Masa Kerja (bln)</label>
                                    <input type="number" id="breakdown-min-tenure" class="form-control form-control-sm"
                                        value="0" min="0">
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button class="btn btn-pri btn-sm w-100" id="btn-apply-breakdown-filter"><i
                                            class="fa-solid fa-check me-1"></i>Terapkan</button>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <div class="summary-mini">
                                        <div class="value" style="color:var(--pri)" id="breakdown-total-cats">-</div>
                                        <div class="label">Kategori</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="summary-mini">
                                        <div class="value" style="color:var(--success)" id="breakdown-top-cat">-</div>
                                        <div class="label">Top Kategori</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="summary-mini">
                                        <div class="value" style="color:var(--info)" id="breakdown-avg-retention">-
                                        </div>
                                        <div class="label">Avg Retensi</div>
                                    </div>
                                </div>
                            </div>

                            <div class="chart-wrap"><canvas id="breakdownChart"></canvas></div>

                            <div class="mt-4">
                                <div class="section-title"><i class="fa-solid fa-list"></i>Detail Retensi per Kategori
                                </div>
                                <div id="breakdown-list"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tabData">
                    <div class="card card-shell">
                        <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                                <div class="section-title mb-0"><i class="fa-solid fa-users"></i>Daftar Karyawan</div>
                                <div class="d-flex gap-2 align-items-center">
                                    <select id="list-category" class="form-select form-select-sm" style="width:140px">
                                        <option value="all">Semua</option>
                                        <option value="active">Active</option>
                                        <option value="new">Baru</option>
                                        <option value="resign">Resign</option>
                                    </select>
                                    <button class="btn btn-outline-sec btn-sm" id="btn-export-employees-csv"
                                        title="Export Excel"><i class="fa-solid fa-file-excel"></i></button>
                                    <button class="btn btn-outline-sec btn-sm" id="btn-export-employees-pdf"
                                        title="Export PDF"><i class="fa-solid fa-file-pdf"></i></button>
                                    <button class="btn btn-pri btn-sm" id="btn-load-employees"><i
                                            class="fa-solid fa-rotate me-1"></i>Load Data</button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table-modern mb-0">
                                    <thead>
                                        <tr>
                                            <th>Nama</th>
                                            <th>NIP</th>
                                            <th>Jabatan</th>
                                            <th>Divisi</th>
                                            <th>Tanggal Join</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="employee-table-body"></tbody>
                                </table>
                            </div>
                            <div class="d-flex flex-wrap justify-content-between align-items-center mt-3">
                                <small class="text-muted" id="table-info">Menampilkan data karyawan</small>
                                <div class="pagination-custom mt-2 mt-md-0" id="employee-pagination"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tabResign">
                    <div class="card card-shell">
                        <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                                <div class="section-title mb-0"><i class="fa-solid fa-user-minus"></i>Daftar Karyawan
                                    Resign</div>
                                <div class="d-flex gap-2 align-items-center">
                                    <div class="input-group input-group-sm" style="width:220px">
                                        <input type="text" id="resign-search" class="form-control"
                                            placeholder="Cari nama, NIP...">
                                        <button class="btn btn-pri" id="btn-resign-search"><i
                                                class="fa-solid fa-magnifying-glass"></i></button>
                                    </div>
                                    <button class="btn btn-outline-sec btn-sm" id="btn-export-resign-csv"
                                        title="Export Excel"><i class="fa-solid fa-file-excel"></i></button>
                                    <button class="btn btn-outline-sec btn-sm" id="btn-export-resign-pdf"
                                        title="Export PDF"><i class="fa-solid fa-file-pdf"></i></button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table-modern mb-0">
                                    <thead>
                                        <tr>
                                            <th>Nama</th>
                                            <th>NIP</th>
                                            <th>Jabatan</th>
                                            <th>Divisi</th>
                                            <th>Tanggal Resign</th>
                                            <th>Alasan Resign</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="resign-table-body"></tbody>
                                </table>
                            </div>

                            <div class="d-flex flex-wrap justify-content-between align-items-center mt-3">
                                <small class="text-muted" id="resign-table-info">Menampilkan data karyawan resign</small>
                                <div class="pagination-custom mt-2 mt-md-0" id="resign-pagination"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tabReminder">
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="summary-mini">
                                <div class="value" style="color:var(--danger)" id="rem-overdue">-</div>
                                <div class="label">Terlewat</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="summary-mini">
                                <div class="value" style="color:var(--warning)" id="rem-urgent">-</div>
                                <div class="label">7 Hari Lagi</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="summary-mini">
                                <div class="value" style="color:var(--info)" id="rem-upcoming">-</div>
                                <div class="label">Akan Datang</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="summary-mini">
                                <div class="value" style="color:var(--pri)" id="rem-total">-</div>
                                <div class="label">Total Reminder</div>
                            </div>
                        </div>
                    </div>
                    <div class="card card-shell">
                        <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                                <div class="section-title mb-0"><i class="fa-solid fa-bell"></i>Penilaian yang Perlu
                                    Dibuat</div>
                                <div class="d-flex gap-2">
                                    <select id="rem-type" class="form-select form-select-sm" style="width:170px">
                                        <option value="all">Semua Fase</option>
                                        <option value="probation">Probation</option>
                                        <option value="kontrak">Kontrak</option>
                                        <option value="tetap">Tetap</option>
                                    </select>
                                    <select id="rem-window" class="form-select form-select-sm" style="width:150px">
                                        <option value="14">14 hari ke depan</option>
                                        <option value="30" selected>30 hari ke depan</option>
                                        <option value="60">60 hari ke depan</option>
                                        <option value="90">90 hari ke depan</option>
                                    </select>
                                </div>
                            </div>
                            <div id="reminder-list"></div>
                            <div style="font-size:.72rem;color:var(--gray-400)" class="mt-2">Penilaian probation,
                                kontrak, dan tetap harus dibuat paling lambat 1 bulan sebelum tanggal berakhir. Reminder
                                yang sudah terlewat lebih dari 1 bulan tidak ditampilkan lagi.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-edit-resign" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header modal-header-custom border-0">
                    <h5 class="modal-title"><i class="fa-solid fa-pen-to-square me-2"></i>Edit Data Resign</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <strong id="edit-resign-name" style="color:var(--gray-900)"></strong>
                        <div class="emp-meta" id="edit-resign-meta"></div>
                    </div>
                    <input type="hidden" id="edit-resign-id">
                    <div class="mb-3 mt-3">
                        <label class="form-label">Tanggal Resign</label>
                        <input type="date" id="edit-resign-date" class="form-control form-control-sm">
                        <div class="invalid-feedback" id="edit-resign-date-error"></div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Alasan Resign</label>
                        <textarea id="edit-resign-reason" class="form-control form-control-sm" rows="3"
                            placeholder="Masukkan alasan resign..."></textarea>
                        <div class="invalid-feedback" id="edit-resign-reason-error"></div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-sec btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-pri btn-sm" id="btn-save-resign"><i
                            class="fa-solid fa-check me-1"></i>Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="moveToResign" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header modal-header-custom border-0">
                    <h5 class="modal-title"><i class="fa-solid fa-user-minus me-2"></i>Pindahkan ke Resign</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <strong id="add-resign-name" style="color:var(--gray-900)"></strong>
                        <div class="emp-meta" id="add-resign-meta"></div>
                    </div>
                    <input type="hidden" id="add-resign-id">
                    <div class="mb-3 mt-3">
                        <label class="form-label">Tanggal Resign</label>
                        <input type="date" id="add-resign-date" class="form-control form-control-sm">
                        <div class="invalid-feedback" id="add-resign-date-error"></div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Alasan Resign</label>
                        <textarea id="add-resign-reason" class="form-control form-control-sm" rows="3"
                            placeholder="Masukkan alasan resign..."></textarea>
                        <div class="invalid-feedback" id="add-resign-reason-error"></div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-sec btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-pri btn-sm" id="btn-save-move-resign"><i
                            class="fa-solid fa-check me-1"></i>Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-active" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header modal-header-custom border-0">
                    <h5 class="modal-title"><i class="fa-solid fa-user-check me-2"></i>Daftar Karyawan Active</h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-sec btn-sm" onclick="exportStatModal('active', 'csv')"><i
                                class="fa-solid fa-file-excel"></i></button>
                        <button class="btn btn-outline-sec btn-sm" onclick="exportStatModal('active', 'pdf')"><i
                                class="fa-solid fa-file-pdf"></i></button>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                </div>
                <div class="modal-body">
                    <div id="list-active"></div>
                    <div class="pagination-custom mt-3 justify-content-center" id="pagination-active"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-new" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header modal-header-custom border-0">
                    <h5 class="modal-title"><i class="fa-solid fa-user-plus me-2"></i>Daftar Karyawan Baru</h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-sec btn-sm" onclick="exportStatModal('new', 'csv')"><i
                                class="fa-solid fa-file-excel"></i></button>
                        <button class="btn btn-outline-sec btn-sm" onclick="exportStatModal('new', 'pdf')"><i
                                class="fa-solid fa-file-pdf"></i></button>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                </div>
                <div class="modal-body">
                    <div id="list-new"></div>
                    <div class="pagination-custom mt-3 justify-content-center" id="pagination-new"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-resign" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header modal-header-custom border-0">
                    <h5 class="modal-title"><i class="fa-solid fa-user-minus me-2"></i>Daftar Karyawan Resign</h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-sec btn-sm" onclick="exportStatModal('resign', 'csv')"><i
                                class="fa-solid fa-file-excel"></i></button>
                        <button class="btn btn-outline-sec btn-sm" onclick="exportStatModal('resign', 'pdf')"><i
                                class="fa-solid fa-file-pdf"></i></button>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                </div>
                <div class="modal-body">
                    <div id="list-resign"></div>
                    <div class="pagination-custom mt-3 justify-content-center" id="pagination-resign"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-retention" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header modal-header-custom border-0">
                    <h5 class="modal-title"><i class="fa-solid fa-chart-line me-2"></i>Analisis Tingkat Retensi</h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-sec btn-sm" onclick="exportStatModal('all', 'csv')"><i
                                class="fa-solid fa-file-excel"></i></button>
                        <button class="btn btn-outline-sec btn-sm" onclick="exportStatModal('all', 'pdf')"><i
                                class="fa-solid fa-file-pdf"></i></button>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                </div>
                <div class="modal-body">

                    <div id="retention-skeleton">
                        <div class="row g-4">
                            <div class="col-md-4 text-center">
                                <span class="skel-line"
                                    style="width:160px;height:160px;border-radius:50%;margin:0 auto 1rem"></span>
                                <span class="skel-line"
                                    style="width:80px;height:22px;border-radius:20px;margin:0 auto"></span>
                            </div>
                            <div class="col-md-8">
                                <span class="skel-line" style="width:160px;height:14px;margin-bottom:12px"></span>
                                <div class="row g-2 mb-3">
                                    @for ($s = 0; $s < 4; $s++)
                                        <div class="col-6"><span class="skel-line"
                                                style="height:50px;border-radius:8px"></span></div>
                                    @endfor
                                </div>
                                <span class="skel-line" style="width:180px;height:14px;margin-bottom:10px"></span>
                                <span class="skel-line" style="height:50px;border-radius:8px;margin-bottom:8px"></span>
                                <span class="skel-line" style="height:50px;border-radius:8px;margin-bottom:16px"></span>
                                <span class="skel-line" style="width:150px;height:14px;margin-bottom:10px"></span>
                                <span class="skel-line" style="height:60px;border-radius:8px"></span>
                            </div>
                        </div>
                        <span class="skel-line" style="width:120px;height:14px;margin-top:24px;margin-bottom:12px"></span>
                        <span class="skel-line" style="height:140px;border-radius:8px"></span>
                    </div>

                    <div id="retention-real" class="d-none">
                        <div class="row g-4">
                            <div class="col-md-4 text-center">
                                <div class="gauge-wrap">
                                    <svg width="160" height="160" style="transform:rotate(-90deg)">
                                        <circle cx="80" cy="80" r="65" fill="none"
                                            stroke="var(--gray-100)" stroke-width="12"></circle>
                                        <circle class="progress-ring" cx="80" cy="80" r="65"
                                            fill="none" stroke="var(--pri)" stroke-width="12" stroke-linecap="round"
                                            stroke-dasharray="408.4" stroke-dashoffset="408.4"
                                            style="transition:stroke-dashoffset 0.6s ease"></circle>
                                    </svg>
                                    <div class="gauge-value">
                                        <div class="num"><span id="gauge-value">0</span></div>
                                        <div class="unit">%</div>
                                    </div>
                                </div>
                                <span class="status-badge status-active" id="retention-status">Baik</span>
                            </div>
                            <div class="col-md-8">
                                <div class="section-title"><i class="fa-solid fa-chart-simple"></i>Ringkasan Statistik
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <div class="summary-mini">
                                            <div class="value" style="color:var(--pri);font-size:1.1rem"
                                                id="summary-total">
                                                -</div>
                                            <div class="label">Total</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="summary-mini">
                                            <div class="value" style="color:var(--success);font-size:1.1rem"
                                                id="summary-active">-</div>
                                            <div class="label">Active</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="summary-mini">
                                            <div class="value" style="color:var(--gray-600);font-size:1.1rem"
                                                id="summary-resign">-</div>
                                            <div class="label">Resign</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="summary-mini">
                                            <div class="value" style="color:var(--info);font-size:1.1rem"
                                                id="summary-ratio">-</div>
                                            <div class="label">Rasio</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="section-title"><i class="fa-solid fa-lightbulb"
                                        style="color:var(--warning)"></i>Peluang Peningkatan</div>
                                <div id="opportunities-list"></div>

                                <div class="section-title mt-3"><i class="fa-solid fa-circle-check"></i>Rekomendasi</div>
                                <div id="recommendations-list"></div>
                            </div>
                        </div>

                        <div class="section-title mt-4"><i class="fa-solid fa-crystal-ball"></i>Proyeksi</div>
                        <div class="table-responsive">
                            <table class="table-modern">
                                <thead>
                                    <tr>
                                        <th>Periode</th>
                                        <th>Est. Active</th>
                                        <th>Est. Resign</th>
                                        <th class="text-center">Confidence</th>
                                    </tr>
                                </thead>
                                <tbody id="projections-body"></tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-detail" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header modal-header-custom border-0">
                    <h5 class="modal-title"><i class="fa-solid fa-id-card me-2"></i>Informasi Karyawan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detail-body"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-chart-detail" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header modal-header-custom border-0">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-list-check me-2"></i>
                        Detail: <span id="chart-detail-title" class="text-primary">-</span>
                    </h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-sec btn-sm" id="btn-export-chart-csv" title="Export Excel">
                            <i class="fa-solid fa-file-excel me-1"></i>Excel
                        </button>
                        <button class="btn btn-outline-sec btn-sm" id="btn-export-chart-pdf" title="Export PDF">
                            <i class="fa-solid fa-file-pdf me-1"></i>PDF
                        </button>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                </div>
                <div class="modal-body">
                    <div id="chart-detail-loading" class="text-center py-5 d-none">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Memuat data...</p>
                    </div>
                    <div class="table-responsive">
                        <table class="table-modern mb-0">
                            <thead>
                                <tr>
                                    <th>Nama Lengkap</th>
                                    <th>NIP</th>
                                    <th>Jabatan</th>
                                    <th>Divisi</th>
                                    <th>Tanggal</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody id="chart-detail-tbody"></tbody>
                        </table>
                    </div>
                    <div class="pagination-custom mt-3 justify-content-center" id="chart-detail-pagination"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalUserGuide" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header modal-header-custom border-0">
                    <h5 class="modal-title"><i class="fa-solid fa-book-open me-2"></i>Panduan Dashboard Informasi Karyawan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs nav-tabs-custom mb-4">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#guide-tab1">
                                <i class="fa-solid fa-list-ol"></i>Cara Penggunaan (Step by Step)
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#guide-tab2">
                                <i class="fa-solid fa-database"></i>Asal Data, Contoh & Bantuan
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="guide-tab1">

                            <div class="guide-section">
                                <h5>1. Membuka halaman dan membaca ringkasan</h5>
                                <ol>
                                    <li>Buka menu HR, lalu pilih <strong>Informasi Karyawan</strong>.</li>
                                    <li>Saat pertama dibuka, tampil kerangka abu-abu (loading). Tunggu sampai semua data siap dan halaman asli muncul.</li>
                                    <li>Lihat empat kartu di bagian atas: <strong>Karyawan Active</strong>, <strong>Karyawan Baru</strong>, <strong>Karyawan Resign</strong>, dan <strong>Tingkat Retensi</strong>.</li>
                                    <li>Waktu pembaruan terakhir ada di pojok kanan atas ("Terakhir update").</li>
                                </ol>
                            </div>

                            <div class="guide-section">
                                <h5>2. Memakai Filter Data</h5>
                                <ol>
                                    <li>Di kotak <strong>Filter Data</strong>, pilih <strong>Periode Data</strong>: Tanpa Filter, 3/6/12 Bulan Terakhir, atau Pilih Tahun.</li>
                                    <li>Jika memilih <em>Pilih Tahun</em>, dropdown <strong>Tahun</strong> akan muncul. Pilih tahunnya.</li>
                                    <li>Untuk mencari karyawan, ketik nama, NIP, jabatan, atau divisi di kolom <strong>Pencarian</strong>, lalu tekan <kbd>Enter</kbd> atau klik tombol kaca pembesar.</li>
                                    <li>Pastikan filter sudah aktif dengan membaca label <strong>"Menampilkan: ..."</strong> di sebelah kanan.</li>
                                    <li>Untuk mengembalikan semuanya, klik tombol <strong>Reset</strong>.</li>
                                </ol>
                                <div class="guide-note">Filter periode dan pencarian memengaruhi: 4 kartu statistik, daftar di dalam modal kartu, tab Data Karyawan, dan hasil export. Grafik di tab Overview memakai filter masing-masing (tanggal, Group By, Filter By, dan seterusnya).</div>
                            </div>

                            <div class="guide-section">
                                <h5>3. Kartu Statistik (klik untuk melihat detail)</h5>
                                <ol>
                                    <li>Klik salah satu kartu, misalnya <strong>Karyawan Baru</strong>.</li>
                                    <li>Muncul jendela berisi daftar karyawan: nama, NIP, jabatan, divisi, dan tanggal.</li>
                                    <li>Jika datanya lebih dari satu halaman, klik nomor halaman di bagian bawah.</li>
                                    <li>Klik tombol <strong>Excel</strong> atau <strong>PDF</strong> di pojok kanan atas jendela untuk mengunduh daftar yang sama.</li>
                                    <li>Khusus kartu <strong>Tingkat Retensi</strong>: jendela menampilkan lingkaran persentase, ringkasan statistik, peluang peningkatan, rekomendasi, dan proyeksi.</li>
                                    <li>Tutup dengan tombol X.</li>
                                </ol>
                            </div>

                            <div class="guide-section">
                                <h5>4. Headcount Trend (tab Overview &amp; Analytics)</h5>
                                <ol>
                                    <li>Isi <strong>Tanggal Mulai</strong> dan <strong>Tanggal Akhir</strong> (berlaku untuk tampilan Bulanan).</li>
                                    <li>Pilih <strong>Group By</strong>: Bulanan, Triwulan, atau Tahunan. Triwulan dan Tahunan otomatis mengambil seluruh rentang sejak data pertama.</li>
                                    <li>Klik <strong>Terapkan</strong>. Grafik dan empat angka ringkasan akan diperbarui.</li>
                                    <li>Baca tiga garis: <strong>Active</strong>, <strong>New Hire</strong>, dan <strong>Resign</strong>.</li>
                                    <li>Klik sebuah titik pada garis, misalnya titik <em>Sep 2026</em> pada garis <em>Resign</em>.</li>
                                    <li>Jendela detail menampilkan <strong>siapa saja</strong> karyawan yang membentuk angka pada titik itu. Jumlahnya selalu sama dengan angka di grafik.</li>
                                    <li>Gunakan tombol <strong>Excel</strong> atau <strong>PDF</strong> di jendela detail untuk mengunduh daftar tersebut, atau di kartu Trend untuk mengunduh tabel trend lengkap.</li>
                                </ol>
                            </div>

                            <div class="guide-section">
                                <h5>5. Headcount Breakdown</h5>
                                <ol>
                                    <li>Pilih <strong>Filter By</strong>: Divisi, Jabatan, atau Gender.</li>
                                    <li>Pilih <strong>Status</strong>: Semua, Active, atau Resign.</li>
                                    <li>Isi <strong>Min. Masa Kerja (bln)</strong> jika hanya ingin karyawan dengan masa kerja tertentu.</li>
                                    <li>Klik <strong>Terapkan</strong>.</li>
                                    <li>Klik sebuah batang pada grafik (batang ungu = Total, hijau = Active). Daftar karyawan di kategori itu akan muncul sesuai filter yang sedang dipakai.</li>
                                    <li>Di bawah grafik ada <strong>Detail Retensi per Kategori</strong>: persentase karyawan aktif dari total tiap kategori.</li>
                                </ol>
                            </div>

                            <div class="guide-section">
                                <h5>6. Tab Data Karyawan</h5>
                                <ol>
                                    <li>Klik tab <strong>Data Karyawan</strong>. Data dimuat otomatis.</li>
                                    <li>Pilih kategori: Semua, Active, Baru, atau Resign, lalu klik <strong>Load Data</strong>.</li>
                                    <li>Klik ikon mata untuk melihat detail lengkap satu karyawan (kontak, data pribadi, riwayat probation/kontrak/tetap, masa kerja).</li>
                                    <li>Untuk karyawan aktif, klik ikon merah <strong>Pindahkan ke Resign</strong>, isi tanggal dan alasan resign, lalu klik <strong>Simpan</strong>.</li>
                                    <li>Pindah halaman dengan nomor atau panah di bawah tabel.</li>
                                    <li>Klik <strong>Excel</strong> atau <strong>PDF</strong> untuk mengunduh daftar sesuai kategori dan filter yang aktif.</li>
                                </ol>
                            </div>

                            <div class="guide-section">
                                <h5>7. Tab Karyawan Resign</h5>
                                <ol>
                                    <li>Klik tab <strong>Karyawan Resign</strong>.</li>
                                    <li>Cari dengan nama atau NIP lalu klik tombol cari.</li>
                                    <li>Klik ikon pensil untuk memperbaiki tanggal atau alasan resign, lalu <strong>Simpan</strong>.</li>
                                    <li>Klik ikon panah putar untuk <strong>memulihkan</strong> karyawan menjadi aktif, lalu konfirmasi.</li>
                                    <li>Unduh daftar lewat tombol <strong>Excel</strong> atau <strong>PDF</strong>.</li>
                                </ol>
                            </div>

                            <div class="guide-section">
                                <h5>8. Tab Reminder Penilaian</h5>
                                <ol>
                                    <li>Klik tab <strong>Reminder</strong>.</li>
                                    <li>Baca empat angka: <strong>Terlewat</strong>, <strong>7 Hari Lagi</strong>, <strong>Akan Datang</strong>, dan <strong>Total Reminder</strong>.</li>
                                    <li>Pilih <strong>Fase</strong> (Probation, Kontrak, Tetap) dan jendela waktu (14/30/60/90 hari ke depan).</li>
                                    <li>Pada setiap baris tertera tindakan yang harus dilakukan, tanggal berakhir, dan kapan penilaian mulai dibuat.</li>
                                    <li>Klik <strong>Detail</strong> untuk membuka informasi karyawan tersebut.</li>
                                </ol>
                                <div class="guide-note">Penilaian harus dibuat paling lambat 1 bulan sebelum tanggal berakhir. Reminder yang sudah terlewat lebih dari 1 bulan tidak ditampilkan lagi.</div>
                            </div>

                            <div class="guide-section">
                                <h5>9. Export Excel</h5>
                                <ol>
                                    <li>Atur filter terlebih dahulu (periode, pencarian, kategori, atau klik titik/batang grafik).</li>
                                    <li>Klik tombol <strong>Excel</strong>. File berformat <code>.xls</code> akan terunduh.</li>
                                    <li>Buka file di Microsoft Excel. Jika muncul peringatan format, klik <strong>Ya</strong>. File aman dan hanya berisi tabel data.</li>
                                    <li>Isi file sama dengan yang tampil di layar pada saat tombol diklik.</li>
                                </ol>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="guide-tab2">

                            <div class="guide-section">
                                <h5>A. Dari mana data diambil?</h5>
                                <ul>
                                    <li>Seluruh data berasal dari <strong>tabel karyawan</strong> di database, yaitu data yang diinput HR pada master data karyawan.</li>
                                    <li>Kolom yang dipakai: <strong>status_aktif</strong> (aktif/tidak), <strong>awal_probation</strong>, <strong>awal_kontrak</strong>, <strong>awal_tetap</strong> (tanggal mulai tiap fase), <strong>resigned_at</strong> (tanggal resign), serta nama, NIP, jabatan, dan divisi.</li>
                                    <li>Data <strong>tidak dihitung</strong> bila: jabatan <em>Outsource</em> atau <em>Pilih Jabatan</em>, kode karyawan berawalan <em>OL</em> (instruktur orang luar), NIP kosong, atau divisi <em>Direksi</em>.</li>
                                    <li>Jika angka terasa kurang, periksa dulu apakah data karyawan tersebut sudah lengkap (NIP dan tanggal mulai).</li>
                                </ul>
                            </div>

                            <div class="guide-section">
                                <h5>B. Cara setiap angka dihitung</h5>
                                <ul>
                                    <li><strong>Karyawan Active:</strong> status_aktif = aktif. Jika filter periode dipakai, tanggal mulai pertama yang terisi (probation, jika kosong kontrak, jika kosong tetap) harus berada di periode itu.</li>
                                    <li><strong>Karyawan Baru:</strong> karyawan aktif yang memiliki tanggal <em>awal probation</em> (dan berada di periode filter bila dipakai).</li>
                                    <li><strong>Karyawan Resign:</strong> karyawan yang <em>resigned_at</em>-nya terisi (dan berada di periode filter bila dipakai).</li>
                                    <li><strong>Tingkat Retensi:</strong> Active ÷ (Active + Resign) × 100%.</li>
                                    <li><strong>Grafik Trend, per titik periode:</strong> Active = karyawan aktif yang salah satu tanggal mulainya (probation/kontrak/tetap) jatuh di periode itu; New Hire = karyawan aktif dengan awal probation di periode itu; Resign = karyawan resign dengan tanggal resign di periode itu.</li>
                                    <li><strong>Grafik Breakdown:</strong> Total = semua karyawan pada kategori (setelah filter Status dan Masa Kerja); Active = yang berstatus aktif.</li>
                                    <li><strong>Reminder:</strong> fase terkini karyawan (Tetap, jika tidak ada Kontrak, jika tidak ada Probation) dan tanggal akhir fase tersebut.</li>
                                </ul>
                            </div>

                            <div class="guide-section">
                                <h5>C. Contoh perhitungan</h5>
                                <div class="guide-example">
                                    <p class="mb-2">Misalkan ada 6 karyawan (filter: <em>Tanpa Filter</em>):</p>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered bg-white mb-3">
                                            <thead>
                                                <tr><th>Nama</th><th>Status</th><th>Awal Probation</th><th>Awal Kontrak</th><th>Awal Tetap</th><th>Resigned At</th></tr>
                                            </thead>
                                            <tbody>
                                                <tr><td>Andi</td><td>Aktif</td><td>05 Sep 2026</td><td>-</td><td>-</td><td>-</td></tr>
                                                <tr><td>Budi</td><td>Aktif</td><td>12 Okt 2026</td><td>-</td><td>-</td><td>-</td></tr>
                                                <tr><td>Citra</td><td>Aktif</td><td>-</td><td>01 Agu 2026</td><td>-</td><td>-</td></tr>
                                                <tr><td>Dewi</td><td>Aktif</td><td>-</td><td>-</td><td>10 Jul 2026</td><td>-</td></tr>
                                                <tr><td>Eko</td><td>Resign</td><td>-</td><td>-</td><td>-</td><td>15 Sep 2026</td></tr>
                                                <tr><td>Fani</td><td>Resign</td><td>-</td><td>-</td><td>-</td><td>20 Okt 2026</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <ul class="mb-3">
                                        <li><strong>Active = 4</strong> (Andi, Budi, Citra, Dewi). <strong>Baru = 2</strong> (Andi, Budi, karena punya awal probation). <strong>Resign = 2</strong> (Eko, Fani).</li>
                                        <li><strong>Retensi</strong> = 4 ÷ (4 + 2) × 100% = <strong>66,7%</strong>.</li>
                                        <li>Klik kartu <em>Karyawan Baru</em>: daftar berisi <strong>2 orang</strong> (Andi dan Budi), bukan 6.</li>
                                        <li>Grafik Trend Bulanan, titik <em>Sep 2026</em>: Active = 1 (Andi), New Hire = 1 (Andi), Resign = 1 (Eko).</li>
                                        <li>Klik titik <em>Sep 2026</em> pada garis <em>Resign</em>: detail hanya menampilkan <strong>Eko</strong> (1 orang), sama dengan angka di grafik.</li>
                                        <li>Breakdown per Divisi: jika divisi <em>Office</em> punya 5 karyawan dan 4 aktif, maka Total = 5, Active = 4, Retensi = 80%. Klik batang Total menampilkan 5 orang, klik batang Active menampilkan 4 orang.</li>
                                    </ul>
                                    <div class="guide-note">Jika detail yang tampil tidak sama dengan angka di layar, cek filter yang sedang aktif lalu laporkan ke Tim ITSM (lihat bagian D).</div>
                                </div>
                            </div>

                            <div class="guide-section">
                                <h5>D. Permintaan perubahan atau laporan error</h5>
                                <div class="guide-itsm">
                                    <p class="mb-2"><strong>Jika ada permintaan perubahan fitur/tampilan, atau Anda menemukan error atau data yang tidak sesuai, silakan hubungi Tim ITSM.</strong></p>
                                    <ol class="mb-3">
                                        <li>Klik tombol <strong>Hubungi Tim ITSM</strong> di bawah.</li>
                                        <li>Pilih jenis permintaan (laporan error, fitur baru, perbaikan, atau masalah data).</li>
                                        <li>Tuliskan judul dan penjelasan: apa yang terjadi, di tab/bagian mana, filter apa yang dipakai, dan angka yang Anda harapkan.</li>
                                        <li>Lampirkan tangkapan layar bila ada, lalu kirim.</li>
                                    </ol>
                                    <a href="{{ route('tickets.index') }}" class="btn btn-danger btn-sm" target="_blank">
                                        <i class="fa-solid fa-ticket me-1"></i>Hubungi Tim ITSM
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        function openUserGuide() {
            new bootstrap.Modal(document.getElementById('modalUserGuide')).show();
        }

        function exportStatModal(category, format) {
            const params = new URLSearchParams({
                category: category,
                periode: $('#periode').val(),
                year: $('#year').val(),
                search: $('#search-employee').val(),
                length: 10000
            }).toString();
            const route = format === 'csv'
                ? "{{ route('HR.employee.export.csv') }}"
                : "{{ route('HR.employee.export.pdf') }}";
            window.location = route + "?" + params;
        }

        $(document).ready(function() {
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            let trendChart, breakdownChart;
            let currentChartFilter = {};
            let employeeRowCache = {};
            let resignRowCache = {};
            let reminderData = [];
            let lastTrendParams = {};
            let lastBreakdownParams = {};

            $.when(loadStats(), loadTrendChart(), loadBreakdownChart(), loadReminders()).always(function() {
                $('#page-skeleton').fadeOut(200, function() { $(this).remove(); });
                $('#page-real-content').removeClass('d-none').hide().fadeIn(250);
            });

            $('button[data-bs-target="#tabData"]').on('shown.bs.tab', function() { loadEmployeeTable(1); });
            $('button[data-bs-target="#tabResign"]').on('shown.bs.tab', function() { loadResignTable(1); });

            $('.card[data-modal]').on('click', function() {
                const modalId = $(this).data('modal'),
                    category = modalId.replace('modal-', '');
                if (category === 'retention') {
                    $('#retention-real').addClass('d-none');
                    $('#retention-skeleton').removeClass('d-none');
                    loadRetentionAnalysis();
                } else {
                    loadEmployeeList(category, 1);
                }
                new bootstrap.Modal(document.getElementById(modalId)).show();
            });

            $('#periode, #year, #btn-search').on('change click', function() { updateFilterLabel(); loadStats(); });
            $('#search-employee').on('keypress', function(e) { if (e.which === 13) { updateFilterLabel(); loadStats(); } });
            $('#btn-reset-filter').on('click', function() {
                $('#periode').val('all'); $('#year').val($('#year option:first').val());
                $('#year-selector').addClass('d-none'); $('#search-employee').val('');
                updateFilterLabel(); loadStats();
            });
            $('#periode').on('change', function() { $('#year-selector').toggleClass('d-none', $(this).val() !== 'year'); });

            $('#btn-export-employees-csv').on('click', function() {
                window.location = "{{ route('HR.employee.export.csv') }}?" + $.param({ category: $('#list-category').val(), ...getFilterParams() });
            });
            $('#btn-export-employees-pdf').on('click', function() {
                window.location = "{{ route('HR.employee.export.pdf') }}?" + $.param({ category: $('#list-category').val(), ...getFilterParams() });
            });
            $('#btn-export-resign-csv').on('click', function() {
                window.location = "{{ route('HR.employee.resigned.export.csv') }}?" + $.param({ search: $('#resign-search').val() });
            });
            $('#btn-export-resign-pdf').on('click', function() {
                window.location = "{{ route('HR.employee.resigned.export.pdf') }}?" + $.param({ search: $('#resign-search').val() });
            });
            $('#btn-apply-trend-filter').on('click', loadTrendChart);
            $('#btn-export-trend-csv').on('click', function() { window.location = "{{ route('HR.employee.trend.export.csv') }}?" + $.param(getTrendParams()); });
            $('#btn-export-trend-pdf').on('click', function() { window.location = "{{ route('HR.employee.trend.export.pdf') }}?" + $.param(getTrendParams()); });
            $('#btn-apply-breakdown-filter').on('click', loadBreakdownChart);
            $('#btn-export-breakdown-csv').on('click', function() { window.location = "{{ route('HR.employee.breakdown.export.csv') }}?" + $.param(getBreakdownParams()); });
            $('#btn-export-breakdown-pdf').on('click', function() { window.location = "{{ route('HR.employee.breakdown.export.pdf') }}?" + $.param(getBreakdownParams()); });

            $('#btn-export-chart-csv').on('click', function() {
                const params = new URLSearchParams(currentChartFilter).toString();
                window.location = "{{ route('HR.employee.chart.detail.export.csv') }}?" + params;
            });
            $('#btn-export-chart-pdf').on('click', function() {
                const params = new URLSearchParams(currentChartFilter).toString();
                window.location = "{{ route('HR.employee.chart.detail.export.pdf') }}?" + params;
            });

            $('#btn-load-employees').on('click', function() { loadEmployeeTable(1); });
            $('#list-category').on('change', function() { loadEmployeeTable(1); });
            $('#btn-resign-search').on('click', function() { loadResignTable(1); });
            $('#resign-search').on('keypress', function(e) { if (e.which === 13) loadResignTable(1); });

            $(document).on('click', '.btn-detail', function() {
                const id = $(this).data('id');
                $('#detail-body').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Memuat detail karyawan...</p></div>');
                new bootstrap.Modal(document.getElementById('modal-detail')).show();
                $.get(`/HR-dashboard/employee/detail/${id}`, function(res) {
                    if (res.success) {
                        const d = res.data;
                        const fotoUrl = d.foto ? d.foto : `https://ui-avatars.com/api/?name=${encodeURIComponent(d.nama_lengkap)}&background=4f46e5&color=fff&size=128`;
                        let html = `
                            <div class="row g-4">
                                <div class="col-md-4 text-center">
                                    <img src="${fotoUrl}" class="rounded-circle mb-3 shadow-sm" style="width:120px;height:120px;object-fit:cover;border:4px solid #fff;outline:2px solid var(--pri)">
                                    <h5 class="mb-1 fw-bold">${d.nama_lengkap}</h5>
                                    <p class="text-muted mb-2">${d.jabatan} • ${d.divisi}</p>
                                    <span class="status-badge ${d.status === 'Aktif' ? 'status-active' : 'status-resign'}">${d.status}</span>
                                </div>
                                <div class="col-md-8">
                                    <div class="row g-3">
                                        <div class="col-md-6"><label class="text-muted small mb-1">NIP</label><div class="fw-semibold fs-6">${d.nip}</div></div>
                                        <div class="col-md-6"><label class="text-muted small mb-1">Kode Karyawan</label><div class="fw-semibold fs-6">${d.kode_karyawan}</div></div>
                                        <div class="col-md-6"><label class="text-muted small mb-1">Email</label><div class="fw-semibold fs-6">${d.email}</div></div>
                                        <div class="col-md-6"><label class="text-muted small mb-1">No. WhatsApp</label><div class="fw-semibold fs-6">${d.whatsapp}</div></div>
                                        <div class="col-md-6"><label class="text-muted small mb-1">Masa Kerja</label><div class="fw-semibold fs-6">${d.masa_kerja}</div></div>
                                        <div class="col-md-6"><label class="text-muted small mb-1">Fase Saat Ini</label><div class="fw-semibold fs-6">${d.fase_label}</div></div>
                                    </div>
                                    <hr class="my-4">
                                    <h6 class="mb-3 text-primary"><i class="fa-solid fa-calendar-days me-2"></i>Riwayat Status</h6>
                                    <div class="row g-3">
                                        <div class="col-md-4"><div class="p-3 bg-light rounded border"><div class="small text-muted mb-1">Probation</div><div class="fw-semibold">${d.awal_probation} <br><span class="text-muted small">s/d</span><br> ${d.akhir_probation}</div></div></div>
                                        <div class="col-md-4"><div class="p-3 bg-light rounded border"><div class="small text-muted mb-1">Kontrak</div><div class="fw-semibold">${d.awal_kontrak} <br><span class="text-muted small">s/d</span><br> ${d.akhir_kontrak}</div></div></div>
                                        <div class="col-md-4"><div class="p-3 bg-light rounded border"><div class="small text-muted mb-1">Tetap</div><div class="fw-semibold">${d.awal_tetap} <br><span class="text-muted small">s/d</span><br> ${d.akhir_tetap}</div></div></div>
                                    </div>
                                    ${d.status === 'Resign' ? `
                                    <hr class="my-4">
                                    <h6 class="mb-3 text-danger"><i class="fa-solid fa-user-xmark me-2"></i>Informasi Resign</h6>
                                    <div class="p-3 bg-danger bg-opacity-10 rounded border border-danger border-opacity-25">
                                        <div class="row g-3">
                                            <div class="col-md-6"><label class="text-muted small mb-1">Tanggal Resign</label><div class="fw-semibold text-danger">${d.resigned_at}</div></div>
                                            <div class="col-md-6"><label class="text-muted small mb-1">Alasan Resign</label><div class="fw-semibold">${d.alasan_resign}</div></div>
                                            ${d.keterangan !== '-' ? `<div class="col-12"><label class="text-muted small mb-1">Keterangan Tambahan</label><div class="fw-semibold">${d.keterangan}</div></div>` : ''}
                                        </div>
                                    </div>` : ''}
                                </div>
                            </div>`;
                        $('#detail-body').html(html);
                    } else {
                        $('#detail-body').html('<div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation me-2"></i>Gagal memuat detail.</div>');
                    }
                }).fail(function() {
                    $('#detail-body').html('<div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation me-2"></i>Terjadi kesalahan jaringan.</div>');
                });
            });

            $(document).on('click', '.btn-move-resign', function() {
                const id = $(this).data('id');
                const emp = employeeRowCache[id];
                if (!emp) return;
                $('#add-resign-id').val(emp.id);
                $('#add-resign-name').text(emp.nama);
                $('#add-resign-meta').text(`${emp.nip} • ${emp.jabatan} • ${emp.divisi}`);
                $('#add-resign-date').val(new Date().toISOString().split('T')[0]).removeClass('is-invalid');
                $('#add-resign-reason').val('').removeClass('is-invalid');
                new bootstrap.Modal(document.getElementById('moveToResign')).show();
            });

            $('#btn-save-move-resign').on('click', function() {
                const id = $('#add-resign-id').val();
                const btn = $(this);
                const payload = { _method: 'PUT', resigned_at: $('#add-resign-date').val(), alasan_resign: $('#add-resign-reason').val() };
                $('#add-resign-date, #add-resign-reason').removeClass('is-invalid');
                btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i>Menyimpan...');
                $.ajax({
                    url: "{{ route('HR.employee.moveToResign', ':id') }}".replace(':id', id),
                    method: 'POST', data: payload,
                    success: function(res) {
                        bootstrap.Modal.getInstance(document.getElementById('moveToResign')).hide();
                        loadEmployeeTable($('#employee-pagination .active').data('page') || 1);
                        loadStats();
                    },
                    error: function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;
                            if (errors.resigned_at) { $('#add-resign-date').addClass('is-invalid'); $('#add-resign-date-error').text(errors.resigned_at[0]); }
                            if (errors.alasan_resign) { $('#add-resign-reason').addClass('is-invalid'); $('#add-resign-reason-error').text(errors.alasan_resign[0]); }
                        } else { alert(xhr.responseJSON?.error || 'Gagal memindahkan data'); }
                    },
                    complete: function() { btn.prop('disabled', false).html('<i class="fa-solid fa-check me-1"></i>Simpan'); }
                });
            });

            $(document).on('click', '.btn-edit-resign', function() {
                const id = $(this).data('id');
                const emp = resignRowCache[id];
                if (!emp) return;
                $('#edit-resign-id').val(emp.id);
                $('#edit-resign-name').text(emp.nama_lengkap);
                $('#edit-resign-meta').text(`${emp.nip} • ${emp.jabatan} • ${emp.divisi}`);
                $('#edit-resign-date').val(emp.resigned_at_raw || '').removeClass('is-invalid');
                $('#edit-resign-reason').val(emp.alasan_resign === '-' ? '' : emp.alasan_resign).removeClass('is-invalid');
                new bootstrap.Modal(document.getElementById('modal-edit-resign')).show();
            });

            $('#btn-save-resign').on('click', function() {
                const id = $('#edit-resign-id').val();
                const btn = $(this);
                const payload = { _method: 'PUT', resigned_at: $('#edit-resign-date').val(), alasan_resign: $('#edit-resign-reason').val() };
                $('#edit-resign-date, #edit-resign-reason').removeClass('is-invalid');
                btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i>Menyimpan...');
                $.ajax({
                    url: `/HR-dashboard/employee/resigned/${id}`,
                    method: 'POST', data: payload,
                    success: function(res) {
                        bootstrap.Modal.getInstance(document.getElementById('modal-edit-resign')).hide();
                        loadResignTable($('#resign-pagination .active').data('page') || 1);
                    },
                    error: function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;
                            if (errors.resigned_at) { $('#edit-resign-date').addClass('is-invalid'); $('#edit-resign-date-error').text(errors.resigned_at[0]); }
                            if (errors.alasan_resign) { $('#edit-resign-reason').addClass('is-invalid'); $('#edit-resign-reason-error').text(errors.alasan_resign[0]); }
                        } else { alert(xhr.responseJSON?.error || 'Gagal memperbarui data'); }
                    },
                    complete: function() { btn.prop('disabled', false).html('<i class="fa-solid fa-check me-1"></i>Simpan'); }
                });
            });

            $(document).on('click', '.btn-restore-resign', function() {
                const id = $(this).data('id');
                const emp = resignRowCache[id];
                if (!emp) return;
                if (!confirm(`Pulihkan ${emp.nama_lengkap} menjadi karyawan aktif?`)) return;
                const btn = $(this);
                btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');
                $.ajax({
                    url: "{{ route('HR.employee.restore', ':id') }}".replace(':id', id),
                    method: 'POST', data: { _method: 'PUT' },
                    success: function() { loadResignTable($('#resign-pagination .active').data('page') || 1); loadStats(); },
                    error: function(xhr) { alert(xhr.responseJSON?.error || 'Gagal memulihkan data'); btn.prop('disabled', false).html('<i class="fa-solid fa-rotate-left"></i>'); }
                });
            });

            $('#rem-type').on('change', renderReminders);
            $('#rem-window').on('change', loadReminders);

            updateFilterLabel();

            function getFilterParams() {
                return { periode: $('#periode').val(), year: $('#year').val(), search: $('#search-employee').val() };
            }
            function getTrendParams() {
                return { start_date: $('#trend-start-date').val(), end_date: $('#trend-end-date').val(), group_by: $('#trend-group-by').val() };
            }
            function getBreakdownParams() {
                return { filter_by: $('#breakdown-filter-by').val(), status: $('#breakdown-status').val(), min_tenure: $('#breakdown-min-tenure').val() };
            }
            function updateFilterLabel() {
                const p = $('#periode').val(), y = $('#year').val(), s = $('#search-employee').val();
                let lbl = 'Menampilkan: ';
                if (p === 'all' && !s) lbl += 'Semua Data';
                else {
                    lbl += p === 'all' ? 'Semua Periode' : (p === 'year' ? `Tahun ${y}` : `${p} Bulan Terakhir`);
                    if (s) lbl += ` • Search: "${s}"`;
                }
                $('#filter-label').text(lbl);
            }
            function esc(v) { return $('<div>').text(v ?? '').html(); }

            function renderSkeleton(tbodyId) {
                const tbody = $(tbodyId);
                if (tbody.find('.skeleton-row').length > 0) return;
                let skeletonHtml = `<tr><td colspan="7"><div id="dt-space-reserver"></div></td></tr>`;
                for (let i = 0; i < 8; i++) {
                    skeletonHtml += `<tr class="skeleton-row"><td><span class="skeleton-cell"></span></td><td><span class="skeleton-cell short"></span></td><td><span class="skeleton-cell"></span></td><td><span class="skeleton-cell short"></span></td><td><span class="skeleton-cell short"></span></td><td class="text-center"><span class="skeleton-cell circle"></span></td><td class="text-center"><span class="skeleton-cell circle"></span></td></tr>`;
                }
                tbody.html(skeletonHtml);
            }

            function renderEmpListSkeleton(containerId, count = 4) {
                let html = '';
                for (let i = 0; i < count; i++) {
                    html += `<div class="emp-list-item"><div class="d-flex justify-content-between align-items-start"><div style="flex:1"><span class="skel-line" style="width:150px;height:14px;margin-bottom:8px"></span><span class="skel-line" style="width:200px;height:11px;margin-bottom:6px"></span><span class="skel-line" style="width:170px;height:11px"></span></div><span class="skel-line" style="width:60px;height:20px;border-radius:20px;flex-shrink:0"></span></div></div>`;
                }
                $(containerId).html(html);
            }

            function loadStats() {
                return $.get("{{ route('HR.employee.data') }}", getFilterParams(), function(res) {
                    if (res.stats) {
                        $('#stat-active').text(res.stats.active);
                        $('#stat-new').text(res.stats.new);
                        $('#stat-resign').text(res.stats.resign);
                        $('#stat-retention').html(res.stats.retention_rate + '<small style="font-size:.9rem">%</small>');
                        $('#tabDataCount').text(res.stats.active || 0);
                        $('#last-update').text(new Date().toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }));
                    }
                });
            }

            function loadTrendChart() {
                lastTrendParams = getTrendParams();
                return $.get("{{ route('HR.employee.trend') }}", lastTrendParams, function(res) {
                    $('#trend-total-active').text(res.summary.total_active);
                    $('#trend-total-new').text(res.summary.total_new);
                    $('#trend-total-resign').text(res.summary.total_resign);
                    $('#trend-avg-new').text(res.summary.avg_monthly_new);
                    if (trendChart) trendChart.destroy();
                    const chartFont = { family: "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto", size: 11 };
                    const colors = ['#4f46e5', '#059669', '#6b7280'];
                    trendChart = new Chart(document.getElementById('trendChart'), {
                        type: 'line',
                        data: {
                            labels: res.labels,
                            datasets: res.datasets.map((d, i) => ({
                                ...d,
                                borderColor: colors[i] || colors[0],
                                backgroundColor: colors[i] ? colors[i] + '20' : colors[0] + '20',
                                tension: 0.4, fill: false, borderWidth: 2.5,
                                pointRadius: 4, pointHoverRadius: 6,
                                pointBackgroundColor: '#fff',
                                pointBorderColor: colors[i] || colors[0],
                                pointBorderWidth: 2
                            }))
                        },
                        options: {
                            responsive: true, maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'bottom', labels: { font: chartFont, usePointStyle: true, padding: 15 } },
                                tooltip: { backgroundColor: 'rgba(17, 24, 39, 0.95)', titleFont: { size: 12, weight: 'bold' }, bodyFont: { size: 11 }, padding: 12, cornerRadius: 8 }
                            },
                            scales: {
                                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: chartFont } },
                                x: { grid: { display: false }, ticks: { font: chartFont, maxRotation: 45 } }
                            }
                        }
                    });
                    setupChartClickEvents();
                });
            }

            function loadBreakdownChart() {
                lastBreakdownParams = getBreakdownParams();
                return $.get("{{ route('HR.employee.breakdown') }}", lastBreakdownParams, function(res) {
                    $('#breakdown-total-cats').text(res.summary.total_categories);
                    $('#breakdown-top-cat').text(res.summary.top_category);
                    $('#breakdown-avg-retention').text(res.summary.avg_retention + '%');
                    if (breakdownChart) breakdownChart.destroy();
                    const chartFont = { family: "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto", size: 11 };
                    breakdownChart = new Chart(document.getElementById('breakdownChart'), {
                        type: 'bar',
                        data: {
                            labels: res.chart.labels,
                            datasets: [{
                                label: 'Total', data: res.chart.total,
                                backgroundColor: 'rgba(79, 70, 229, 0.85)', borderRadius: 6, borderSkipped: false
                            }, {
                                label: 'Active', data: res.chart.active,
                                backgroundColor: 'rgba(5, 150, 105, 0.85)', borderRadius: 6, borderSkipped: false
                            }]
                        },
                        options: {
                            responsive: true, maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'bottom', labels: { font: chartFont, usePointStyle: true, padding: 15 } },
                                tooltip: { backgroundColor: 'rgba(17, 24, 39, 0.95)', titleFont: { size: 12, weight: 'bold' }, bodyFont: { size: 11 }, padding: 12, cornerRadius: 8 }
                            },
                            scales: {
                                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: chartFont } },
                                x: { grid: { display: false }, ticks: { font: chartFont, maxRotation: 45, minRotation: 45 } }
                            }
                        }
                    });
                    const list = $('#breakdown-list').empty();
                    if (!res.breakdown || res.breakdown.length === 0) {
                        list.html('<div class="empty-state"><i class="fa-solid fa-chart-simple"></i><p>Belum ada data breakdown</p></div>');
                        return;
                    }
                    res.breakdown.forEach(item => {
                        list.append(`
                            <div class="breakdown-item">
                                <div class="breakdown-label">${item.label}</div>
                                <div class="breakdown-bar-wrap"><div class="breakdown-bar" style="width:${item.retention}%"></div></div>
                                <div class="breakdown-value">${item.retention}%</div>
                            </div>
                        `);
                    });
                    setupChartClickEvents();
                });
            }

            function loadEmployeeTable(page = 1) {
                const tbody = $('#employee-table-body'), pag = $('#employee-pagination');
                renderSkeleton('#employee-table-body'); pag.empty();
                $.get("{{ route('HR.employee.category') }}", {
                    category: $('#list-category').val(), ...getFilterParams(), page: page, length: 10
                }, function(res) {
                    tbody.find('.skeleton-row, #dt-space-reserver').remove();
                    employeeRowCache = {};
                    if (res.data.length === 0) {
                        tbody.append('<tr><td colspan="7"><div class="empty-state"><i class="fa-solid fa-users-slash"></i><p>Tidak ada data</p></div></td></tr>');
                    } else {
                        res.data.forEach(emp => {
                            employeeRowCache[emp.id] = emp;
                            const statusClass = emp.status === 'Aktif' ? 'status-active' : 'status-resign';
                            const actionBtn = `<button class="btn btn-outline-sec btn-sm btn-detail" data-id="${emp.id}" title="Detail"><i class="fa-solid fa-eye"></i></button>` +
                                (emp.status === 'Aktif' ? ` <button class="btn btn-outline-danger btn-sm btn-move-resign" data-id="${emp.id}" title="Pindahkan ke Resign"><i class="fa-solid fa-user-minus"></i></button>` : '');
                            tbody.append(`<tr>
                                <td><strong style="color:var(--gray-900)">${emp.nama}</strong><br><small class="text-muted">${emp.nama_lengkap}</small></td>
                                <td class="text-muted">${emp.nip}</td><td>${emp.jabatan}</td><td>${emp.divisi}</td><td>${emp.tanggal_join}</td>
                                <td class="text-center"><span class="status-badge ${statusClass}">${emp.status}</span></td>
                                <td class="text-center">${actionBtn}</td>
                            </tr>`);
                        });
                    }
                    $('#table-info').text(`Menampilkan ${res.data.length} dari ${res.pagination.total} data`);
                    if (res.pagination.last_page > 1) {
                        let pagHtml = '';
                        if (res.pagination.current_page > 1) pagHtml += `<button class="prev" data-page="${res.pagination.current_page - 1}"><i class="fa-solid fa-chevron-left"></i></button>`;
                        for (let i = 1; i <= res.pagination.last_page; i++) {
                            if (i === 1 || i === res.pagination.last_page || (i >= res.pagination.current_page - 2 && i <= res.pagination.current_page + 2)) {
                                pagHtml += `<button class="${i === res.pagination.current_page ? 'active' : ''}" data-page="${i}">${i}</button>`;
                            } else if (i === res.pagination.current_page - 3 || i === res.pagination.current_page + 3) {
                                pagHtml += `<span style="padding:0 8px;color:var(--gray-400)">...</span>`;
                            }
                        }
                        if (res.pagination.current_page < res.pagination.last_page) pagHtml += `<button class="next" data-page="${res.pagination.current_page + 1}"><i class="fa-solid fa-chevron-right"></i></button>`;
                        pag.html(pagHtml);
                        pag.off('click', 'button').on('click', 'button', function() {
                            if (!$(this).hasClass('disabled')) loadEmployeeTable($(this).data('page'));
                        });
                    }
                }).fail(function() {
                    tbody.find('.skeleton-row, #dt-space-reserver').remove();
                    tbody.append('<tr><td colspan="7"><div class="empty-state text-danger"><i class="fa-solid fa-triangle-exclamation"></i><p>Gagal memuat data</p></div></td></tr>');
                });
            }

            function loadResignTable(page) {
                const tbody = $('#resign-table-body');
                const pag = $('#resign-pagination');
                renderSkeleton('#resign-table-body');
                pag.empty();
                const params = { search: $('#resign-search').val(), page };
                $.get("{{ route('HR.employee.resigned') }}", params, function(res) {
                    tbody.find('.skeleton-row').remove();
                    tbody.find('#dt-space-reserver').remove();
                    $('#tabResignCount').text(res.total_resign || 0);
                    resignRowCache = {};
                    if (res.data.length === 0) {
                        tbody.append('<tr><td colspan="7"><div class="empty-state"><i class="fa-solid fa-users-slash"></i><p>Tidak ada data karyawan resign</p></div></td></tr>');
                    } else {
                        res.data.forEach(emp => {
                            resignRowCache[emp.id] = emp;
                            tbody.append(`
                                <tr>
                                    <td><strong style="color:var(--gray-900)">${emp.nama_lengkap}</strong></td>
                                    <td class="text-muted">${emp.nip}</td>
                                    <td>${emp.jabatan}</td>
                                    <td>${emp.divisi}</td>
                                    <td><span style="color:var(--danger)"><i class="fa-solid fa-calendar-xmark me-1"></i>${emp.resigned_at}</span></td>
                                    <td>${emp.alasan_resign}</td>
                                    <td class="text-center">
                                        <button class="btn btn-outline-sec btn-sm btn-edit-resign" data-id="${emp.id}" title="Edit"><i class="fa-solid fa-pen"></i></button>
                                        <button class="btn btn-outline-sec btn-sm btn-restore-resign" data-id="${emp.id}" title="Pulihkan ke Aktif"><i class="fa-solid fa-rotate-left"></i></button>
                                    </td>
                                </tr>
                            `);
                        });
                    }
                    $('#resign-table-info').text(`Menampilkan ${res.data.length} dari ${res.pagination.total} data`);
                    if (res.pagination.last_page > 1) {
                        for (let i = 1; i <= res.pagination.last_page; i++) {
                            const active = i === res.pagination.current_page ? 'active' : '';
                            pag.append(`<button class="${active}" data-page="${i}">${i}</button>`);
                        }
                        pag.off('click', 'button').on('click', 'button', function() { loadResignTable($(this).data('page')); });
                    }
                }).fail(function() {
                    tbody.find('.skeleton-row, #dt-space-reserver').remove();
                    tbody.append('<tr><td colspan="7"><div class="empty-state"><i class="fa-solid fa-triangle-exclamation" style="color:var(--danger)"></i><p>Gagal memuat data</p></div></td></tr>');
                });
            }

            function loadEmployeeList(category, page) {
                const listId = `#list-${category}`, paginationId = `#pagination-${category}`;
                renderEmpListSkeleton(listId);
                $(paginationId).empty();
                $.get("{{ route('HR.employee.category') }}", { ...getFilterParams(), category, page }, function(res) {
                    $(listId).empty();
                    if (res.data.length === 0) {
                        $(listId).html('<div class="empty-state"><i class="fa-solid fa-inbox"></i><p>Tidak ada data</p></div>');
                    } else {
                        res.data.forEach(emp => {
                            const dateDisplay = category === 'resign' && emp.resigned_at ?
                                `<br><small style="color:var(--danger)"><i class="fa-solid fa-calendar-xmark me-1"></i>Resign: ${emp.resigned_at}</small>` :
                                emp.tanggal_join !== '-' ? `<br><small class="emp-meta"><i class="fa-solid fa-calendar me-1"></i>${emp.tanggal_join}</small>` : '';
                            $(listId).append(`
                                <div class="emp-list-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="emp-name">${emp.nama}</div>
                                            <div class="emp-meta">${emp.nama_lengkap} • ${emp.nip}</div>
                                            <div class="emp-meta mt-1"><i class="fa-solid fa-briefcase me-1"></i>${emp.jabatan} • ${emp.divisi}</div>
                                            ${dateDisplay}
                                        </div>
                                        <span class="status-badge ${emp.status === 'Aktif' ? 'status-active' : 'status-resign'}">${emp.status}</span>
                                    </div>
                                </div>
                            `);
                        });
                    }
                    if (res.pagination.last_page > 1) {
                        let pagination = '';
                        for (let i = 1; i <= res.pagination.last_page; i++) {
                            const active = i === res.pagination.current_page ? 'active' : '';
                            pagination += `<button class="${active}" data-page="${i}">${i}</button>`;
                        }
                        $(paginationId).html(pagination).off('click', 'button').on('click', 'button', function() {
                            loadEmployeeList(category, $(this).data('page'));
                        });
                    }
                }).fail(function() {
                    $(listId).html('<div class="empty-state"><i class="fa-solid fa-triangle-exclamation" style="color:var(--danger)"></i><p>Gagal memuat data</p></div>');
                });
            }

            function loadRetentionAnalysis() {
                $.get("{{ route('HR.employee.data') }}", getFilterParams(), function(res) {
                    if (!res.stats || !res.insights) return;
                    $('#retention-skeleton').addClass('d-none');
                    $('#retention-real').removeClass('d-none');
                    const rate = res.stats.retention_rate;
                    const circumference = 2 * Math.PI * 65;
                    const offset = circumference - (rate / 100) * circumference;
                    $('#gauge-value').text(rate);
                    $('.progress-ring').css({ 'stroke-dasharray': circumference, 'stroke-dashoffset': offset });
                    let strokeColor, statusText, statusClass;
                    if (rate >= 90) { strokeColor = 'var(--success)'; statusText = 'Sangat Baik'; statusClass = 'status-active'; }
                    else if (rate >= 75) { strokeColor = 'var(--pri)'; statusText = 'Baik'; statusClass = 'status-active'; }
                    else if (rate >= 60) { strokeColor = 'var(--warning)'; statusText = 'Cukup'; statusClass = 'status-badge'; }
                    else { strokeColor = 'var(--danger)'; statusText = 'Kritis'; statusClass = 'status-resign'; }
                    $('.progress-ring').css('stroke', strokeColor);
                    $('#retention-status').text(statusText).attr('class', 'status-badge ' + statusClass);
                    $('#summary-total').text(res.stats.total_employees);
                    $('#summary-active').text(res.stats.active);
                    $('#summary-resign').text(res.stats.resign);
                    $('#summary-ratio').text(`${res.stats.active} : ${res.stats.resign}`);
                    const oppList = $('#opportunities-list').empty();
                    if (res.insights.opportunities && res.insights.opportunities.length > 0) {
                        let oppHtml = '';
                        res.insights.opportunities.forEach(opp => {
                            oppHtml += `<div class="insight-card mb-2"><i class="fa-solid ${opp.icon || 'fa-lightbulb'} mt-1" style="color: var(--warning);"></i><div><div class="fw-bold mb-1" style="color: var(--gray-900); font-size: 0.9rem;">${opp.title}</div><div style="font-size: 0.8rem; color: var(--gray-600); line-height: 1.4;">${opp.desc}</div></div></div>`;
                        });
                        oppList.html(oppHtml);
                    } else {
                        oppList.html('<div class="empty-state" style="padding:1rem"><small>Tidak ada peluang peningkatan saat ini</small></div>');
                    }
                    const recList = $('#recommendations-list').empty();
                    if (res.insights.recommendations && Object.keys(res.insights.recommendations).length > 0) {
                        let recHtml = '';
                        $.each(res.insights.recommendations, function(key, category) {
                            recHtml += `<div class="mb-3"><div class="section-title mb-2" style="color: ${category.color || 'var(--pri)'}"><i class="fa-solid ${category.icon} me-2"></i>${category.title}</div><ul class="list-unstyled mb-0">`;
                            category.items.forEach(item => {
                                recHtml += `<li class="mb-2 d-flex align-items-start gap-2" style="font-size: 0.85rem; color: var(--gray-700); line-height: 1.4;"><i class="fa-solid fa-check-circle mt-1" style="color: var(--success);"></i><span>${item}</span></li>`;
                            });
                            recHtml += `</ul></div>`;
                        });
                        recList.html(recHtml);
                    } else {
                        recList.html('<div class="empty-state" style="padding:1rem"><small>Tidak ada rekomendasi saat ini</small></div>');
                    }
                    const projBody = $('#projections-body').empty();
                    if (res.insights.projections) {
                        Object.values(res.insights.projections).forEach(data => {
                            let confClass = 'status-resign';
                            if (data.confidence === 'high') confClass = 'status-active';
                            else if (data.confidence === 'medium') confClass = 'status-badge';
                            let actionClass = data.action_required && data.action_required.includes('Segera') ? 'text-danger fw-bold' : 'text-muted';
                            projBody.append(`<tr><td><strong>${data.period}</strong></td><td class="fw-bold" style="color:var(--success)">${data.estimated_active}</td><td class="fw-bold" style="color:var(--danger)">${data.estimated_resign}</td><td class="text-center"><span class="status-badge ${confClass} mb-1 d-inline-block">${data.confidence.toUpperCase()}</span><br><small class="${actionClass}" style="font-size:0.7rem">${data.action_required || '-'}</small></td></tr>`);
                        });
                    }
                });
            }

            function loadReminders() {
                return $.get("{{ route('HR.employee.reminders') }}", { window: $('#rem-window').val() }, function(res) {
                    if (!res.success) return;
                    reminderData = res.data;
                    $('#rem-overdue').text(res.summary.overdue);
                    $('#rem-urgent').text(res.summary.urgent);
                    $('#rem-upcoming').text(res.summary.upcoming);
                    $('#rem-total').text(res.summary.total);
                    $('#tabReminderCount').text(res.summary.total);
                    renderReminders();
                });
            }

            function renderReminders() {
                const type = $('#rem-type').val();
                const list = $('#reminder-list').empty();
                const data = reminderData.filter(r => type === 'all' || r.fase === type);
                if (!data.length) {
                    list.html('<div class="empty-state"><i class="fa-solid fa-circle-check"></i><p>Tidak ada reminder</p><small>Belum ada penilaian yang perlu dibuat pada rentang ini</small></div>');
                    return;
                }
                data.forEach(r => {
                    const days = r.days_left < 0 ? `Terlewat<br>${Math.abs(r.days_left)} hari` : r.days_left === 0 ? 'Hari ini' : `${r.days_left} hari<br>lagi`;
                    list.append(`
                        <div class="rem-item ${r.level}">
                            <div class="rem-days">${days}</div>
                            <div style="flex:1;min-width:0">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="emp-name">${esc(r.nama)}</span>
                                    <span class="rem-phase">${esc(r.fase_label)}</span>
                                </div>
                                <div class="emp-meta">${esc(r.nip)} • ${esc(r.jabatan)} • ${esc(r.divisi)}</div>
                                <div style="font-size:.8rem;color:var(--gray-700);margin-top:.25rem"><i class="fa-solid fa-clipboard-check me-1" style="color:var(--pri)"></i>${esc(r.tindakan)}</div>
                                <div class="emp-meta mt-1">Berakhir: <strong>${esc(r.tanggal_berakhir)}</strong> • Penilaian mulai: ${esc(r.mulai_penilaian)}</div>
                            </div>
                            <button class="btn btn-outline-sec btn-sm btn-detail" data-id="${r.id}"><i class="fa-solid fa-eye me-1"></i>Detail</button>
                        </div>
                    `);
                });
            }

            function setupChartClickEvents() {
                if (trendChart) {
                    trendChart.options.onClick = (e, activeEls, chart) => {
                        if (!activeEls.length) return;
                        const el = activeEls[0];
                        openChartDetailModal('trend',
                            chart.data.labels[el.index],
                            chart.data.datasets[el.datasetIndex].label,
                            { group_by: lastTrendParams.group_by });
                    };
                    trendChart.update();
                }
                if (breakdownChart) {
                    breakdownChart.options.onClick = (e, activeEls, chart) => {
                        if (!activeEls.length) return;
                        const el = activeEls[0];
                        openChartDetailModal('breakdown',
                            chart.data.labels[el.index],
                            chart.data.datasets[el.datasetIndex].label,
                            {
                                filter_by: lastBreakdownParams.filter_by,
                                status: lastBreakdownParams.status,
                                min_tenure: lastBreakdownParams.min_tenure
                            });
                    };
                    breakdownChart.update();
                }
            }

            function openChartDetailModal(type, label, dataset, extra = {}) {
                currentChartFilter = { type, label, dataset, ...extra };

                $('#chart-detail-title').text(`${dataset} - ${label}`);
                $('#chart-detail-tbody').empty();
                $('#chart-detail-pagination').empty();
                $('#chart-detail-loading').removeClass('d-none');

                new bootstrap.Modal(document.getElementById('modal-chart-detail')).show();
                loadChartDetailData(1);
            }

            function loadChartDetailData(page = 1) {
                $('#chart-detail-loading').removeClass('d-none');
                $('#chart-detail-tbody').html('<tr><td colspan="6" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>');

                $.get("{{ route('HR.employee.chart.detail') }}", {
                    ...currentChartFilter,
                    page: page,
                    length: 10
                }, function(res) {
                    $('#chart-detail-loading').addClass('d-none');
                    const tbody = $('#chart-detail-tbody');
                    tbody.empty();

                    if (!res.data || res.data.length === 0) {
                        tbody.append('<tr><td colspan="6" class="text-center py-4 text-muted">Tidak ada data detail untuk filter ini.</td></tr>');
                    } else {
                        res.data.forEach(emp => {
                            const statusClass = emp.status === 'Aktif' ? 'status-active' : 'status-resign';
                            tbody.append(`
                            <tr>
                                <td><strong>${emp.nama}</strong></td>
                                <td class="text-muted">${emp.nip}</td>
                                <td>${emp.jabatan}</td>
                                <td>${emp.divisi}</td>
                                <td>${emp.tanggal}</td>
                                <td class="text-center"><span class="status-badge ${statusClass}">${emp.status}</span></td>
                            </tr>
                            `);
                        });
                    }

                    const pag = $('#chart-detail-pagination');
                    pag.empty();
                    const paginationInfo = res.pagination;
                    if (paginationInfo.last_page > 1) {
                        let pagHtml = `<small class="text-muted me-3">Total: ${paginationInfo.total} karyawan</small>`;
                        if (paginationInfo.current_page > 1) pagHtml += `<button class="prev" data-page="${paginationInfo.current_page - 1}"><i class="fa-solid fa-chevron-left"></i></button>`;

                        for (let i = 1; i <= paginationInfo.last_page; i++) {
                            if (i === 1 || i === paginationInfo.last_page || (i >= paginationInfo.current_page - 2 && i <= paginationInfo.current_page + 2)) {
                                const active = i === paginationInfo.current_page ? 'active' : '';
                                pagHtml += `<button class="${active}" data-page="${i}">${i}</button>`;
                            } else if (i === paginationInfo.current_page - 3 || i === paginationInfo.current_page + 3) {
                                pagHtml += `<span style="padding:0 8px;color:var(--gray-400)">...</span>`;
                            }
                        }
                        if (paginationInfo.current_page < paginationInfo.last_page) pagHtml += `<button class="next" data-page="${paginationInfo.current_page + 1}"><i class="fa-solid fa-chevron-right"></i></button>`;

                        pag.html(pagHtml);
                        pag.off('click', 'button').on('click', 'button', function() {
                            if (!$(this).hasClass('disabled')) loadChartDetailData($(this).data('page'));
                        });
                    } else {
                        pag.html(`<small class="text-muted">Menampilkan semua ${paginationInfo.total} data</small>`);
                    }
                }).fail(function() {
                    $('#chart-detail-loading').addClass('d-none');
                    $('#chart-detail-tbody').html('<tr><td colspan="6" class="text-center py-4 text-danger">Gagal memuat data.</td></tr>');
                });
            }
        });
    </script>
@endsection