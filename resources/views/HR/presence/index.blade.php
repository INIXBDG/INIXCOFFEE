@extends('layout_HR.app')
@section('content_HR')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
            --c-hadir: #059669;
            --c-telat: #dc2626;
            --c-cuti: #0284c7;
            --c-sakit: #7c3aed;
            --c-izin: #0d9488;
            --c-pending: #f97316;
            --c-tidak_hadir: #374151;
            --bg-hadir: #d1fae5;
            --bg-telat: #fee2e2;
            --bg-cuti: #e0f2fe;
            --bg-sakit: #ede9fe;
            --bg-izin: #ccfbf1;
            --bg-pending: #ffedd5;
            --bg-tidak_hadir: #e5e7eb;
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
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
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

        .stat-trend {
            font-size: .72rem;
            font-weight: 600;
            margin-top: .3rem;
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            color: var(--gray-400);
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

        .btn-success-custom {
            background: var(--success);
            border: none;
            color: #fff;
            font-weight: 600;
            padding: .5rem 1.25rem;
            border-radius: 8px;
            transition: all .25s;
            font-size: .85rem;
        }

        .btn-success-custom:hover {
            background: #047857;
            transform: translateY(-1px);
            color: #fff;
        }

        .btn-danger-custom {
            background: var(--danger);
            border: none;
            color: #fff;
            font-weight: 600;
            padding: .5rem 1.25rem;
            border-radius: 8px;
            transition: all .25s;
            font-size: .85rem;
        }

        .btn-danger-custom:hover {
            background: #b91c1c;
            transform: translateY(-1px);
            color: #fff;
        }

        .chart-wrap {
            position: relative;
            height: 280px;
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

        .filter-hint {
            font-size: .72rem;
            color: var(--gray-400);
            margin-top: .2rem;
            max-width: 420px;
        }

        .filter-field {
            display: flex;
            flex-direction: column;
        }

        .filter-field .form-label {
            margin-bottom: .2rem;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: .5rem;
        }

        .calendar-header {
            text-align: center;
            font-weight: 700;
            font-size: .78rem;
            color: var(--gray-600);
            padding: .6rem 0;
            text-transform: uppercase;
            letter-spacing: .5px;
            background: var(--gray-100);
            border-radius: 6px;
        }

        .calendar-day {
            min-height: 118px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border-radius: 6px;
            font-size: .85rem;
            cursor: pointer;
            transition: all .15s;
            border: 1px solid var(--gray-200);
            background: #fff;
            padding: .5rem .55rem;
            position: relative;
        }

        .calendar-day:hover {
            border-color: var(--pri);
            box-shadow: 0 0 0 2px rgba(79, 70, 229, .25);
            z-index: 2;
        }

        .calendar-day .day-num {
            font-weight: 800;
            font-size: 1.05rem;
            color: var(--gray-900);
            line-height: 1;
        }

        .calendar-day .holiday-name {
            font-size: .64rem;
            line-height: 1.2;
            color: #92400e;
            font-weight: 600;
            margin-top: .25rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .calendar-day .dots {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            margin-top: .4rem;
        }

        .calendar-day.holiday {
            background: #fef3c7;
            border-color: #fcd34d;
        }

        .calendar-day.weekend {
            background: var(--gray-100);
        }

        .calendar-day.weekend .day-num {
            color: var(--gray-400);
        }

        .calendar-day.future {
            opacity: .55;
        }

        .calendar-day.today {
            border: 2px solid var(--pri);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, .15);
        }

        .calendar-day.today .day-num {
            color: var(--pri);
        }

        .calendar-day.skeleton-day {
            cursor: default;
            background: #fff;
        }

        .calendar-day.skeleton-day:hover {
            border-color: var(--gray-200);
            box-shadow: none;
        }

        .calendar-empty {
            min-height: 118px;
        }

        .dot {
            min-width: 30px;
            height: 26px;
            padding: 0 7px;
            border-radius: 5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .82rem;
            font-weight: 800;
            color: #fff;
            line-height: 1;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .15);
        }

        .dot-telat {
            background: var(--c-telat);
        }

        .dot-cuti {
            background: var(--c-cuti);
        }

        .dot-sakit {
            background: var(--c-sakit);
        }

        .dot-izin {
            background: var(--c-izin);
        }

        .dot-pending {
            background: var(--c-pending);
        }

        .dot-tidak_hadir {
            background: var(--c-tidak_hadir);
        }

        .legend-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .55rem .75rem;
            border-radius: 8px;
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            margin-bottom: .5rem;
            font-size: .82rem;
            color: var(--gray-700);
            font-weight: 600;
        }

        .legend-row .left {
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .legend-swatch {
            width: 18px;
            height: 18px;
            border-radius: 4px;
            display: inline-block;
            flex-shrink: 0;
        }

        .legend-count {
            font-weight: 700;
            color: var(--gray-900);
        }

        .div-late-list {
            height: 100%;
            overflow-y: auto;
            padding-right: .25rem;
        }

        .div-late-item {
            padding: .7rem .85rem;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            margin-bottom: .5rem;
            background: #fff;
        }

        .div-late-item .progress {
            height: 7px;
            border-radius: 6px;
            background: var(--gray-100);
        }

        .div-late-rank {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: var(--gray-100);
            color: var(--gray-600);
            font-size: .7rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .div-late-item.top .div-late-rank {
            background: var(--danger);
            color: #fff;
        }

        .opp-card {
            border-radius: 10px;
            padding: 1.1rem 1.25rem;
            margin-bottom: .75rem;
            border: 1px solid var(--gray-200);
            background: #fff;
            transition: all .2s;
            border-left: 4px solid var(--gray-400);
        }

        .opp-card:hover {
            transform: translateX(3px);
            box-shadow: var(--shadow-sm);
        }

        .opp-card.high {
            border-left-color: var(--danger);
            background: linear-gradient(90deg, rgba(220, 38, 38, .03), transparent);
        }

        .opp-card.medium {
            border-left-color: var(--warning);
            background: linear-gradient(90deg, rgba(217, 119, 6, .03), transparent);
        }

        .opp-card.low {
            border-left-color: var(--success);
            background: linear-gradient(90deg, rgba(5, 150, 105, .03), transparent);
        }

        .opp-title {
            font-weight: 700;
            color: var(--gray-900);
            font-size: .9rem;
            margin-bottom: .35rem;
        }

        .opp-desc {
            font-size: .82rem;
            color: var(--gray-600);
            line-height: 1.5;
            margin-bottom: .6rem;
        }

        .opp-meta {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            font-size: .72rem;
            color: var(--gray-400);
            font-weight: 600;
        }

        .opp-meta span {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
        }

        .pred-card {
            background: linear-gradient(135deg, var(--pri-light) 0%, #fff 100%);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius);
            padding: 1.5rem;
            text-align: center;
            transition: all .25s;
        }

        .pred-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow);
            border-color: var(--pri);
        }

        .pred-label {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--gray-400);
            font-weight: 700;
            margin-bottom: .5rem;
        }

        .pred-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--pri);
            margin: .3rem 0;
            line-height: 1;
        }

        .pred-conf {
            font-size: .78rem;
            color: var(--gray-400);
            font-weight: 500;
        }

        .milestone-item {
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            padding: 1rem 1.25rem;
            margin-bottom: .6rem;
            transition: all .2s;
        }

        .milestone-item:hover {
            background: #fff;
            border-color: var(--pri);
            box-shadow: var(--shadow-sm);
        }

        .milestone-target {
            font-weight: 700;
            color: var(--gray-900);
            font-size: .95rem;
        }

        .milestone-timeline {
            background: var(--pri-light);
            color: var(--pri);
            font-size: .7rem;
            font-weight: 700;
            padding: .25rem .65rem;
            border-radius: 10px;
            text-transform: uppercase;
            letter-spacing: .3px;
        }

        .milestone-actions {
            font-size: .8rem;
            color: var(--gray-600);
            margin-top: .4rem;
            line-height: 1.5;
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
            font-size: .72rem;
            margin-bottom: .4rem;
            text-transform: uppercase;
            letter-spacing: .3px;
        }

        .empty-state {
            text-align: center;
            padding: 2.5rem 1rem;
            color: var(--gray-400);
        }

        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            opacity: .4;
            display: block;
        }

        .empty-state p {
            font-size: .88rem;
            margin: 0;
            font-weight: 500;
        }

        .empty-state small {
            font-size: .78rem;
        }

        .risk-badge {
            padding: .3rem .7rem;
            border-radius: 20px;
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .risk-high {
            background: var(--danger-light);
            color: var(--danger);
        }

        .risk-medium {
            background: var(--warning-light);
            color: var(--warning);
        }

        .risk-low {
            background: var(--success-light);
            color: var(--success);
        }

        .risk-none {
            background: var(--gray-100);
            color: var(--gray-600);
        }

        .employee-card {
            display: flex;
            align-items: center;
            padding: .75rem;
            border-radius: 8px;
            margin-bottom: .5rem;
            background: #fff;
            border: 1px solid var(--gray-200);
            transition: all .2s;
        }

        .employee-card:hover {
            background: var(--gray-50);
            border-color: var(--pri);
            box-shadow: var(--shadow-sm);
        }

        .employee-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: .75rem;
            flex-shrink: 0;
            overflow: hidden;
        }

        .employee-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .employee-avatar i {
            font-size: 1.2rem;
            color: var(--gray-400);
        }

        .employee-info {
            flex: 1;
            min-width: 0;
        }

        .employee-name {
            font-weight: 600;
            color: var(--gray-900);
            font-size: .875rem;
            margin-bottom: .15rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .employee-position {
            font-size: .72rem;
            color: var(--gray-400);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .employee-meta {
            text-align: right;
            flex-shrink: 0;
            max-width: 45%;
        }

        .employee-time {
            font-size: .78rem;
            font-weight: 600;
            color: var(--gray-700);
        }

        .employee-badge {
            font-size: .65rem;
            padding: .2rem .5rem;
            border-radius: 10px;
            font-weight: 700;
            text-transform: uppercase;
            display: inline-block;
        }

        .daily-stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: .75rem;
        }

        .daily-stat {
            border-radius: 10px;
            padding: 1rem .75rem;
            text-align: center;
        }

        .daily-stat .lbl {
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        .daily-stat .num {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--gray-900);
            margin: .3rem 0;
            line-height: 1;
        }

        .daily-stat .pct {
            font-size: .72rem;
            color: var(--gray-600);
        }

        .recap-table th {
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .3px;
            color: var(--gray-600);
            white-space: nowrap;
        }

        .recap-table td {
            font-size: .85rem;
            vertical-align: middle;
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
            .calendar-grid {
                gap: .25rem;
            }

            .calendar-day {
                font-size: .72rem;
                padding: .3rem;
                min-height: 78px;
            }

            .calendar-empty {
                min-height: 78px;
            }

            .calendar-day .day-num {
                font-size: .85rem;
            }

            .calendar-day .holiday-name {
                display: none;
            }

            .dot {
                min-width: 22px;
                height: 20px;
                font-size: .68rem;
                padding: 0 4px;
            }

            .stat-value {
                font-size: 1.3rem;
            }

            .pred-value {
                font-size: 1.6rem;
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

        @keyframes skeleton-pulse {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        #page-skeleton,
        #page-skeleton * {
            pointer-events: none;
        }

        #guideModal {
            --pri: #4f46e5;
            --pri-light: #eef2ff;
            --pri-dark: #3730a3;
            --success: #059669;
            --warning: #d97706;
            --danger: #dc2626;
            --danger-light: #fee2e2;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-400: #9ca3af;
            --gray-700: #374151;
            --gray-900: #111827;
        }

        #guideModal .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .1), 0 4px 10px rgba(0, 0, 0, .07);
        }

        #guideModal .modal-header-custom {
            background: linear-gradient(135deg, var(--pri) 0%, var(--pri-dark) 100%);
            color: #fff;
            border-radius: 12px 12px 0 0;
            padding: 1.1rem 1.5rem;
        }

        #guideModal .modal-header-custom .modal-title {
            font-weight: 700;
            font-size: 1rem;
        }

        #guideModal .modal-header-custom .btn-close {
            filter: brightness(0) invert(1);
        }

        #guideModal .nav-tabs-custom {
            border-bottom: 2px solid var(--gray-200);
        }

        #guideModal .nav-tabs-custom .nav-link {
            border: none;
            color: var(--gray-400);
            font-weight: 600;
            padding: .85rem 1.25rem;
            font-size: .875rem;
            transition: color .2s;
            display: flex;
            align-items: center;
            gap: .5rem;
            background: transparent;
        }

        #guideModal .nav-tabs-custom .nav-link:hover {
            color: var(--pri);
        }

        #guideModal .nav-tabs-custom .nav-link.active {
            color: var(--pri);
            border-bottom: 3px solid var(--pri);
            background: transparent;
        }

        #guideModal .guide-section {
            margin-bottom: 2rem;
        }

        #guideModal .guide-section h5 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--gray-900);
            border-left: 4px solid var(--pri);
            padding-left: .75rem;
            margin-bottom: .75rem;
        }

        #guideModal .guide-section ol,
        #guideModal .guide-section ul {
            padding-left: 1.25rem;
            margin-bottom: .75rem;
        }

        #guideModal .guide-section li {
            margin-bottom: .4rem;
            font-size: .9rem;
            color: var(--gray-700);
            line-height: 1.55;
        }

        #guideModal .guide-section p {
            font-size: .9rem;
            color: var(--gray-700);
            line-height: 1.55;
        }

        #guideModal .guide-note {
            background: var(--pri-light);
            border-radius: 8px;
            padding: .75rem 1rem;
            font-size: .85rem;
            color: var(--gray-700);
        }

        #guideModal .guide-example {
            background: var(--gray-50);
            border: 1px dashed var(--gray-400);
            border-radius: 8px;
            padding: 1rem;
            font-size: .85rem;
        }

        #guideModal .guide-itsm {
            background: var(--danger-light);
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 1.25rem;
        }

        #guideModal .guide-qa {
            margin-bottom: 1rem;
        }

        #guideModal .guide-qa strong {
            display: block;
            font-size: .9rem;
            color: var(--gray-900);
            margin-bottom: .2rem;
        }

        #guideModal .guide-qa span {
            font-size: .88rem;
            color: var(--gray-700);
            line-height: 1.55;
        }
    </style>

    @php
        $dailyCards = [
            ['present', 'Hadir', 'hadir'],
            ['late', 'Terlambat', 'telat'],
            ['leave', 'Cuti', 'cuti'],
            ['sick', 'Sakit', 'sakit'],
            ['izin', 'Izin', 'izin'],
            ['pending', 'Menunggu', 'pending'],
            ['absent', 'Tanpa Keterangan', 'tidak_hadir'],
        ];
        $dailyLists = [
            ['present', 'Hadir Tepat Waktu', 'fa-circle-check', 'hadir', 6],
            ['late', 'Terlambat', 'fa-clock', 'telat', 6],
            ['leave', 'Cuti', 'fa-umbrella-beach', 'cuti', 6],
            ['sick', 'Sakit', 'fa-briefcase-medical', 'sakit', 6],
            ['izin', 'Izin 3 Jam', 'fa-person-walking-arrow-right', 'izin', 6],
            ['pending', 'Menunggu Persetujuan Manager', 'fa-hourglass-half', 'pending', 6],
            ['absent', 'Tidak Hadir (Belum Ada Keterangan)', 'fa-user-xmark', 'tidak_hadir', 12],
        ];
    @endphp

    <div class="container-fluid px-4 py-4">

        <div id="page-skeleton">
            <div class="d-sm-flex align-items-center justify-content-between page-header">
                <div>
                    <span class="skel-line" style="width:280px;height:26px;margin-bottom:8px"></span>
                    <span class="skel-line" style="width:340px;height:14px"></span>
                </div>
                <div class="d-flex gap-2 mt-2 mt-sm-0">
                    <span class="skel-line" style="width:100px;height:38px;border-radius:8px"></span>
                    <span class="skel-line" style="width:90px;height:38px;border-radius:8px"></span>
                    <span class="skel-line" style="width:80px;height:38px;border-radius:8px"></span>
                </div>
            </div>
            <div class="card card-shell mb-4">
                <div class="card-body py-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <span class="skel-line" style="width:220px;height:30px"></span>
                        <div class="d-flex gap-2">
                            <span class="skel-line" style="width:120px;height:34px;border-radius:8px"></span>
                            <span class="skel-line" style="width:90px;height:34px;border-radius:8px"></span>
                            <span class="skel-line" style="width:110px;height:34px;border-radius:8px"></span>
                            <span class="skel-line" style="width:130px;height:34px;border-radius:8px"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-3 mb-4">
                @for ($i = 0; $i < 3; $i++)
                    <div class="col-xl-4 col-md-6">
                        <div class="card stat-card h-100">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div style="flex:1">
                                    <span class="skel-line" style="width:130px;height:10px;margin-bottom:10px"></span>
                                    <span class="skel-line" style="width:70px;height:24px;margin-bottom:8px"></span>
                                    <span class="skel-line" style="width:150px;height:11px"></span>
                                </div>
                                <span class="skel-line"
                                    style="width:52px;height:52px;border-radius:12px;flex-shrink:0"></span>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>
            <div class="d-flex gap-3 mb-4 pb-3" style="border-bottom:2px solid var(--gray-200)">
                <span class="skel-line" style="width:130px;height:20px"></span>
                <span class="skel-line" style="width:190px;height:20px"></span>
                <span class="skel-line" style="width:170px;height:20px"></span>
            </div>
            <div class="row g-4">
                @for ($c = 0; $c < 4; $c++)
                    <div class="col-lg-6">
                        <div class="card card-shell">
                            <div class="card-body">
                                <span class="skel-line" style="width:200px;height:16px;margin-bottom:16px"></span>
                                <span class="skel-line" style="height:280px;border-radius:8px"></span>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>

        <div id="page-real-content" class="d-none">

            <div class="d-sm-flex align-items-center justify-content-between page-header">
                <div>
                    <h1 class="page-title"><i class="fa-solid fa-user-check me-2" style="color:var(--pri)"></i>Attendance
                        Intelligence</h1>
                    <p class="page-sub mb-0">Monitor dan analisis kehadiran karyawan secara real-time</p>
                </div>
                <div class="d-flex gap-2 mt-2 mt-sm-0">
                    <button class="btn btn-outline-sec" id="btnRefresh"><i
                            class="fa-solid fa-arrows-rotate me-1"></i>Refresh</button>
                    <button class="btn btn-success-custom" id="btnExportCsv"><i
                            class="fa-solid fa-file-excel me-1"></i>Excel</button>
                    <button class="btn btn-danger-custom" id="btnExportPdf"><i
                            class="fa-solid fa-file-pdf me-1"></i>PDF</button>
                    <button class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#guideModal">
                        <i class="fa-solid fa-circle-question me-1"></i>Panduan
                    </button>
                </div>
            </div>

            <div class="card card-shell mb-4">
                <div class="card-body py-3">
                    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3">
                        <div>
                            <span class="fw-bold" style="font-size:.875rem;color:var(--pri)"><i
                                    class="fa-solid fa-filter me-1"></i>Filter Periode</span>
                            <div class="filter-hint">Pilih yang dibutuhkan saja. Bulan saja = tahun ini, tahun saja =
                                setahun penuh, triwulan menggantikan bulan.</div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap align-items-end">
                            <div class="filter-field"><label class="form-label">Bulan</label><select id="filterBulan"
                                    class="form-select form-select-sm" style="width:130px"></select></div>
                            <div class="filter-field"><label class="form-label">Tahun</label><select id="filterTahun"
                                    class="form-select form-select-sm" style="width:100px"></select></div>
                            <div class="filter-field"><label class="form-label">Triwulan</label><select
                                    id="filterTriwulan" class="form-select form-select-sm" style="width:130px"></select>
                            </div>
                            <div class="filter-field"><label class="form-label">Divisi</label><select id="filterDivisi"
                                    class="form-select form-select-sm" style="width:160px">
                                    <option value="all">Semua Divisi</option>
                                </select></div>
                            <button class="btn btn-pri btn-sm" id="btnApplyFilter"><i
                                    class="fa-solid fa-check me-1"></i>Terapkan</button>
                            <button class="btn btn-outline-sec btn-sm" id="btnResetFilter">Reset</button>
                        </div>
                    </div>
                    <div class="mt-2" style="font-size:.78rem;color:var(--gray-600)">Periode ditampilkan: <strong
                            id="periodLabel">-</strong></div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-xl-4 col-md-6">
                    <div class="card stat-card h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="stat-label">Total Telat (Periode)</p>
                                <h3 class="stat-value" id="metricTotalLate" style="color:var(--danger)">-</h3>
                                <div class="stat-trend" id="trendLate">Akumulasi keterlambatan</div>
                            </div>
                            <div class="stat-icon" style="background:linear-gradient(135deg,#dc2626,#ef4444)"><i
                                    class="fa-solid fa-clock"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="card stat-card h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="stat-label">Cuti / Sakit / Izin</p>
                                <h3 class="stat-value" id="metricTotalLeave" style="color:var(--info)">-</h3>
                                <div class="stat-trend">
                                    <a href="#" id="btnViewLeaveDetails"
                                        style="color:var(--pri);text-decoration:none;font-weight:600;font-size:.75rem">
                                        <i class="fa-solid fa-eye me-1"></i>Lihat Rekap per Orang
                                    </a>
                                </div>
                            </div>
                            <div class="stat-icon" style="background:linear-gradient(135deg,#0284c7,#38bdf8)"><i
                                    class="fa-solid fa-umbrella-beach"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="card stat-card h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="stat-label">Attendance Rate</p>
                                <h3 class="stat-value" id="metricAttendanceRate" style="color:var(--success)">-</h3>
                                <div class="stat-trend" id="trendAttendance">Persentase kehadiran</div>
                            </div>
                            <div class="stat-icon" style="background:linear-gradient(135deg,#059669,#10b981)"><i
                                    class="fa-solid fa-user-check"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <ul class="nav nav-tabs nav-tabs-custom mb-4" id="mainTabs">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab"
                        data-bs-target="#tabAnalytics"><i class="fa-solid fa-chart-line me-2"></i>Analytics</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabCalendar"><i
                            class="fa-solid fa-calendar-days me-2"></i>Kalender Kehadiran</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabPrediction"><i
                            class="fa-solid fa-wand-magic-sparkles me-2"></i>Prediksi & Target</button></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="tabAnalytics">
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="card card-shell">
                                <div class="card-body">
                                    <div class="chart-title"><i class="fa-solid fa-ranking-star"></i>Divisi dengan
                                        Keterlambatan Tertinggi</div>
                                    <div id="lateDivision-skel" class="chart-wrap"><span class="skel-line"
                                            style="height:100%;border-radius:8px"></span></div>
                                    <div id="lateDivision-wrap" class="chart-wrap d-none">
                                        <div class="div-late-list" id="lateDivisionList"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card card-shell">
                                <div class="card-body">
                                    <div class="chart-title"><i class="fa-solid fa-chart-bar"></i>Rekap Hadir, Cuti, Sakit
                                        & Izin 3 Jam</div>
                                    <div id="leaveChart-skel" class="chart-wrap"><span class="skel-line"
                                            style="height:100%;border-radius:8px"></span></div>
                                    <div id="leaveChart-wrap" class="chart-wrap d-none"><canvas id="leaveChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card card-shell">
                                <div class="card-body">
                                    <div class="chart-title"><i class="fa-solid fa-chart-column"></i>Persentase Kehadiran
                                        per Divisi</div>
                                    <div id="divAttChart-skel" class="chart-wrap"><span class="skel-line"
                                            style="height:100%;border-radius:8px"></span></div>
                                    <div id="divAttChart-wrap" class="chart-wrap d-none"><canvas
                                            id="divAttChart"></canvas></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card card-shell">
                                <div class="card-body">
                                    <div class="chart-title justify-content-between">
                                        <span><i class="fa-solid fa-bell me-2"></i>Pemberitahuan Keterlambatan</span>
                                        <span class="risk-badge risk-none" id="notifyBadge">-</span>
                                    </div>
                                    <div id="notifyChart-skel" class="chart-wrap"><span class="skel-line"
                                            style="height:100%;border-radius:8px"></span></div>
                                    <div id="notifyChart-wrap" class="d-none">
                                        <div class="chart-wrap" style="height:230px">
                                            <div class="div-late-list" id="notifyDivisionList"></div>
                                        </div>
                                        <div class="text-center mt-2" style="font-size:.78rem;color:var(--gray-600)"
                                            id="notifyText">-</div>
                                        <div class="text-center" style="font-size:.7rem;color:var(--gray-400)">
                                            Keterlambatan di atas 50% = Teguran Peringatan · di atas 70% = Peringatan Berat
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mt-2">
                        <div class="col-12">
                            <div class="card card-shell">
                                <div class="card-body">
                                    <div class="chart-title"><i class="fa-solid fa-lightbulb"
                                            style="color:var(--warning)"></i>Peluang & Rekomendasi</div>
                                    <div id="opportunitiesContainer"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tabCalendar">
                    <div class="row g-4">

                        <div class="col-xl-9 col-lg-8">
                            <div class="card card-shell">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <button class="btn btn-outline-sec btn-sm" id="btnPrevMonth"><i
                                                class="fa-solid fa-chevron-left"></i></button>
                                        <h5 class="mb-0 fw-bold" id="calendarMonthLabel" style="color:var(--gray-900)">-
                                        </h5>
                                        <button class="btn btn-outline-sec btn-sm" id="btnNextMonth"><i
                                                class="fa-solid fa-chevron-right"></i></button>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                        <div style="font-size:.75rem;color:var(--gray-400)">Angka pada kotak berwarna =
                                            jumlah karyawan. Kehadiran tepat waktu tidak ditandai.</div>
                                        <select id="calendarEmployee" class="form-select form-select-sm"
                                            style="width:210px">
                                            <option value="">Semua Karyawan</option>
                                        </select>
                                        <button class="btn btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#modalTambahHariLibur">
                                            Tambah Hari Libur
                                        </button>
                                    </div>
                                    <div id="attendanceCalendar" class="calendar-grid"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-4">
                            <div class="card card-shell h-100">
                                <div class="card-body">
                                    <div class="chart-title"><i class="fa-solid fa-circle-info"
                                            style="color:var(--info)"></i>Keterangan & Total Bulan Ini</div>
                                    <div id="calendarSummary"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div
                            class="card border-0 shadow-lg h-100 rounded-4 overflow-hidden d-flex flex-column glass-force">
                            <div class="card-header border-bottom-0 pb-0">
                                <h5 class="mb-0 fw-semibold text-dark d-flex align-items-center">
                                    <i class="bx bx-calendar-check text-success me-2" style="font-size: 1.5rem;"></i>
                                    Hari Libur Bulan Ini
                                </h5>
                            </div>
                            <div class="card-body p-4 flex-grow-1 d-flex flex-column" style="height: auto; gap: 12px;">
                                <div id="holiday-detail" class="pt-2">
                                    <div class="text-center py-3">
                                        <i class="bx bx-info-circle text-muted"
                                            style="font-size: 2.5rem; opacity: 0.5;"></i>
                                        <p class="text-muted small mt-2 mb-0">Klik tanggal libur di kalender</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tabPrediction">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="pred-card h-100">
                                <div class="pred-label"><i class="fa-solid fa-calendar-day me-1"></i>Bulan Depan</div>
                                <div class="pred-value" id="predNextMonth">-</div>
                                <div class="pred-conf" id="predNextMonthConf">-</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="pred-card h-100">
                                <div class="pred-label"><i class="fa-solid fa-calendar-week me-1"></i>Kuartal Depan</div>
                                <div class="pred-value" id="predNextQuarter">-</div>
                                <div class="pred-conf" id="predNextQuarterConf">-</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="pred-card h-100">
                                <div class="pred-label"><i class="fa-solid fa-calendar me-1"></i>Tahun Depan</div>
                                <div class="pred-value" id="predNextYear">-</div>
                                <div class="pred-conf" id="predNextYearConf">-</div>
                            </div>
                        </div>
                    </div>
                    <div class="card card-shell">
                        <div class="card-body">
                            <div class="chart-title"><i class="fa-solid fa-flag-checkered"
                                    style="color:var(--pri)"></i>Milestone Target</div>
                            <div id="milestonesContainer"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTambahHariLibur" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h1 class="modal-title fs-5">Tambah Hari Libur</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <form id="formTambahHariLibur">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nama Hari Libur <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" required autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipe</label>
                            <select name="type" class="form-select">
                                <option value="nasional">Nasional</option>
                                <option value="cuti_bersama">Cuti Bersama</option>
                                <option value="khusus">Khusus</option>
                            </select>
                        </div>
                        <div class="modal-footer px-0 pb-0">
                            <button type="button" class="btn btn-outline-secondary"
                                data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary" id="btnSimpanLibur">
                                <i class="fa-solid fa-save me-1"></i>Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="leaveDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:var(--radius);border:none;box-shadow:var(--shadow-lg)">
                <div class="modal-header"
                    style="border-bottom:2px solid var(--gray-200);padding:1.25rem 1.5rem;background:var(--info-light)">
                    <div>
                        <h5 class="modal-title fw-bold mb-0" style="color:var(--gray-900)"><i
                                class="fa-solid fa-list-ul me-2" style="color:var(--info)"></i>Rekap Cuti / Sakit / Izin
                            per Orang</h5>
                        <div style="font-size:.8rem;color:var(--gray-600)" id="leaveRecapPeriod">-</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding:1.25rem">
                    <input type="text" id="leaveRecapSearch" class="form-control form-control-sm mb-3"
                        placeholder="Cari nama atau divisi..." style="max-width:280px">
                    <div class="table-responsive">
                        <table class="table table-hover recap-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Karyawan</th>
                                    <th>Divisi</th>
                                    <th class="text-center">Cuti</th>
                                    <th class="text-center">Sakit</th>
                                    <th class="text-center">Izin</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Menunggu</th>
                                </tr>
                            </thead>
                            <tbody id="leaveRecapBody"></tbody>
                        </table>
                    </div>
                    <div style="font-size:.72rem;color:var(--gray-400)" class="mt-2">Klik nama karyawan untuk melihat
                        rincian pengajuannya. Cuti dan sakit dihitung per hari kerja, izin per pengajuan 3 jam. Menunggu =
                        belum disetujui manager (tidak masuk total).</div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="dailyDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:var(--radius);border:none;box-shadow:var(--shadow-lg)">
                <div class="modal-header"
                    style="border-bottom:2px solid var(--gray-200);padding:1.25rem 1.5rem;background:linear-gradient(135deg,var(--pri-light) 0%,#fff 100%)">
                    <div>
                        <h5 class="modal-title fw-bold mb-1" id="modalDateLabel" style="color:var(--gray-900)">-</h5>
                        <p class="mb-0" style="font-size:.85rem;color:var(--gray-600)" id="modalDayName">-</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body" style="padding:1.5rem">
                    <div id="modalLoading">
                        <div class="daily-stat-grid mb-4">
                            @for ($i = 0; $i < 7; $i++)
                                <div class="daily-stat" style="background:var(--gray-50)">
                                    <span class="skel-line" style="width:60%;height:10px;margin:0 auto 10px"></span>
                                    <span class="skel-line" style="width:40%;height:28px;margin:0 auto 8px"></span>
                                    <span class="skel-line" style="width:30%;height:10px;margin:0 auto"></span>
                                </div>
                            @endfor
                        </div>
                        <div class="card mb-4" style="border:none;border-radius:10px;box-shadow:var(--shadow)">
                            <div class="card-body">
                                <span class="skel-line" style="width:200px;height:15px;margin-bottom:16px"></span>
                                <span class="skel-line" style="height:250px;border-radius:8px"></span>
                            </div>
                        </div>
                        <div class="row g-3">
                            @for ($j = 0; $j < 4; $j++)
                                <div class="col-md-6">
                                    <div class="card" style="border:none;border-radius:10px;box-shadow:var(--shadow)">
                                        <div class="card-body">
                                            <span class="skel-line"
                                                style="width:160px;height:14px;margin-bottom:14px"></span>
                                            <span class="skel-line"
                                                style="height:56px;border-radius:8px;margin-bottom:8px"></span>
                                            <span class="skel-line" style="height:56px;border-radius:8px"></span>
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <div id="modalContent" style="display:none">
                        <div id="holidayAlert" class="alert mb-4" style="display:none;border-radius:8px">
                            <div class="d-flex align-items-center">
                                <i class="fa-solid fa-circle-info me-2" style="font-size:1.2rem"></i>
                                <div>
                                    <strong id="holidayAlertTitle">-</strong>
                                    <p class="mb-0 mt-1" style="font-size:.85rem" id="holidayAlertDesc">-</p>
                                </div>
                            </div>
                        </div>

                        <div class="daily-stat-grid mb-4">
                            @foreach ($dailyCards as [$id, $label, $color])
                                <div class="daily-stat" style="background:var(--bg-{{ $color }})">
                                    <div class="lbl" style="color:var(--c-{{ $color }})">{{ $label }}
                                    </div>
                                    <div class="num" id="stat-{{ $id }}">0</div>
                                    <div class="pct" id="stat-{{ $id }}-pct">0%</div>
                                </div>
                            @endforeach
                        </div>

                        <div class="card mb-4" style="border:none;border-radius:10px;box-shadow:var(--shadow)">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3" style="color:var(--gray-700)"><i
                                        class="fa-solid fa-chart-pie me-2" style="color:var(--pri)"></i>Distribusi
                                    Kehadiran</h6>
                                <div style="position:relative;height:250px"><canvas id="dailyStatusChart"></canvas></div>
                            </div>
                        </div>

                        <div class="row g-3">
                            @foreach ($dailyLists as [$id, $title, $icon, $color, $col])
                                <div class="col-md-{{ $col }}">
                                    <div class="card" style="border:none;border-radius:10px;box-shadow:var(--shadow)">
                                        <div class="card-body">
                                            <h6 class="fw-bold mb-3" style="color:var(--c-{{ $color }})">
                                                <i class="fa-solid {{ $icon }} me-2"></i>{{ $title }} (<span
                                                    id="{{ $id }}Count">0</span>)
                                            </h6>
                                            <div id="{{ $id }}List" style="max-height:280px;overflow-y:auto">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="guideModal" tabindex="-1" aria-labelledby="guideModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header modal-header-custom border-0">
                    <h5 class="modal-title" id="guideModalLabel"><i class="fa-solid fa-book-open me-2"></i>Panduan
                        Attendance Intelligence</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <ul class="nav nav-tabs nav-tabs-custom mb-4">
                        <li class="nav-item">
                            <button class="nav-link active" id="guide-tab1" data-bs-toggle="tab"
                                data-bs-target="#guide-content-1" type="button">
                                <i class="fa-solid fa-list-ol"></i>Cara Penggunaan (Step by Step)
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="guide-tab2" data-bs-toggle="tab"
                                data-bs-target="#guide-content-2" type="button">
                                <i class="fa-solid fa-circle-info"></i>Arti Status, Contoh & Bantuan
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="guide-content-1">

                            <div class="guide-section">
                                <h5>1. Untuk apa halaman ini?</h5>
                                <p>Halaman ini menunjukkan kehadiran karyawan: siapa yang hadir, telat, cuti, sakit, izin,
                                    atau tidak hadir. Anda bisa melihat ringkasannya, mencari tahu siapa orangnya, lalu
                                    mengunduh laporan.</p>
                                <div class="guide-note">Cara tercepat: pilih <strong>Bulan dan Tahun</strong>, pilih
                                    <strong>Divisi</strong>, lalu klik <strong>Terapkan</strong>. Data akan tampil dalam
                                    beberapa detik.
                                </div>
                            </div>

                            <div class="guide-section">
                                <h5>2. Mengatur Filter</h5>
                                <ol>
                                    <li>Di bagian atas halaman, pilih <strong>Bulan</strong> dan <strong>Tahun</strong> yang
                                        ingin dilihat.</li>
                                    <li>Jika ingin melihat 3 bulan sekaligus, pilih <strong>Triwulan</strong>. Pilihan
                                        <strong>Bulan</strong> akan otomatis tidak bisa dipakai, karena data dihitung per 3
                                        bulan.
                                    </li>
                                    <li>Pilih <strong>Divisi</strong>. Biarkan "Semua Divisi" untuk melihat seluruh
                                        perusahaan, atau pilih satu divisi saja.</li>
                                    <li>Klik <strong>Terapkan</strong>. Seluruh angka dan kalender akan mengikuti pilihan
                                        Anda.</li>
                                    <li>Untuk kembali ke awal, klik <strong>Reset</strong>. Filter akan kembali ke bulan dan
                                        tahun yang sedang berjalan.</li>
                                </ol>
                            </div>

                            <div class="guide-section">
                                <h5>3. Membaca Peringatan Keterlambatan</h5>
                                <ol>
                                    <li>Cari kartu <strong>Pemberitahuan Keterlambatan</strong>.</li>
                                    <li>Jika muncul label <span class="badge bg-warning text-dark">Teguran</span>, artinya
                                        lebih dari 50% hari kerja di divisi tersebut ada keterlambatan.</li>
                                    <li>Jika muncul label <span class="badge bg-danger">Peringatan Berat</span>, artinya
                                        lebih dari 70% hari kerja ada keterlambatan.</li>
                                    <li>Jika tidak ada label, keterlambatan masih dalam batas wajar.</li>
                                </ol>
                            </div>

                            <div class="guide-section">
                                <h5>4. Melihat Siapa Saja Orangnya (Rekap per Orang)</h5>
                                <ol>
                                    <li>Pada kartu angka, klik tulisan <strong>"Lihat Rekap per Orang"</strong>.</li>
                                    <li>Muncul tabel berisi nama setiap karyawan dan jumlah pengajuan atau kejadiannya.</li>
                                    <li>Gunakan tabel ini untuk mengetahui siapa yang perlu ditindaklanjuti.</li>
                                </ol>
                            </div>

                            <div class="guide-section">
                                <h5>5. Membaca Peluang dan Rekomendasi</h5>
                                <ol>
                                    <li>Gulir ke bagian <strong>Peluang & Rekomendasi</strong>.</li>
                                    <li>Bagian ini berisi saran tindakan berdasarkan data yang tampil. Contohnya, jika
                                        tingkat kehadiran di bawah 90%, akan disarankan mengoptimalkan pengingat absen.</li>
                                    <li>Saran ini hanya bantuan. Keputusan akhir tetap ada pada HRD.</li>
                                </ol>
                            </div>

                            <div class="guide-section">
                                <h5>6. Memakai Kalender Kehadiran</h5>
                                <ol>
                                    <li>Kalender menampilkan setiap tanggal dalam satu bulan. Angka di dalam kotak
                                        menunjukkan jumlah karyawan dengan status tertentu pada hari itu.</li>
                                    <li>Tanggal berlabel <span class="badge"
                                            style="background:#fef3c7;color:#92400e">Libur</span> adalah hari libur, dan
                                        <span class="badge bg-secondary">Akhir Pekan</span> adalah Sabtu/Minggu.
                                    </li>
                                    <li>Klik satu kotak tanggal. Akan terbuka jendela <strong>Detail Harian</strong> berisi
                                        nama karyawan, dikelompokkan per status (hadir, telat, cuti, dan seterusnya).</li>
                                    <li>Untuk melihat satu orang saja, pilih namanya di dropdown <strong>"Semua
                                            Karyawan"</strong> di atas kalender. Kalender akan menampilkan pola kehadiran
                                        orang itu saja.</li>
                                    <li>Tutup jendela detail dengan tombol X, lalu klik tanggal lain bila perlu.</li>
                                </ol>
                            </div>

                            <div class="guide-section">
                                <h5>7. Mengunduh Laporan</h5>
                                <ol>
                                    <li>Atur dulu filter sesuai kebutuhan, lalu klik <strong>Terapkan</strong>.</li>
                                    <li>Klik tombol <strong>Excel</strong> untuk tabel kehadiran harian. Baris adalah
                                        karyawan, kolom adalah tanggal 1 sampai 31, dan setiap sel berisi kode huruf
                                        berwarna (H, L, C, S, I, P, T).</li>
                                    <li>Klik tombol <strong>PDF</strong> untuk laporan ringkas yang rapi dan siap dicetak,
                                        berisi grafik dan tabel rekap.</li>
                                    <li>Isi laporan selalu sama dengan filter yang sedang aktif di layar.</li>
                                </ol>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="guide-content-2">

                            <div class="guide-section">
                                <h5>A. Data ini dari mana?</h5>
                                <p>Data berasal dari catatan absen masuk karyawan dan pengajuan cuti, sakit, atau izin yang
                                    sudah tercatat di sistem. halaman hanya menampilkan dan merangkum, tidak mengubah data
                                    apa pun.</p>
                                <ul>
                                    <li>Jika absen tidak tercatat, kemungkinan karyawan lupa absen atau absennya belum masuk
                                        ke sistem.</li>
                                    <li>Pengajuan cuti yang belum disetujui atasan tampil sebagai <strong>Pending</strong>.
                                    </li>
                                </ul>
                            </div>

                            <div class="guide-section">
                                <h5>B. Arti Setiap Status</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle" style="font-size:.85rem">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:150px">Status</th>
                                                <th>Artinya</th>
                                                <th style="width:230px">Contoh</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><span class="badge bg-success">Hadir</span></td>
                                                <td>Karyawan absen masuk tepat waktu atau lebih awal.</td>
                                                <td>Masuk pukul 07:55</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-danger">Telat</span></td>
                                                <td>Karyawan absen masuk melewati batas waktu yang diizinkan.</td>
                                                <td>Masuk pukul 08:20 (telat 20 menit)</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-info">Cuti / Sakit</span></td>
                                                <td>Karyawan mengajukan cuti atau sakit dan sudah disetujui atasan.</td>
                                                <td>Cuti tahunan, sakit dengan surat dokter</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-warning text-dark">Izin 3 Jam</span></td>
                                                <td>Karyawan mengajukan izin keluar beberapa jam dan sudah disetujui.</td>
                                                <td>Urusan keluarga, pulang lebih cepat</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-secondary">Pending</span></td>
                                                <td>Pengajuan sudah dibuat, tetapi belum disetujui atasan.</td>
                                                <td>Mengajukan cuti, menunggu persetujuan</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-dark">Tidak Hadir</span></td>
                                                <td>Tidak ada absen dan tidak ada pengajuan yang sah pada hari kerja.</td>
                                                <td>Alpha, atau lupa absen tanpa konfirmasi</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="guide-note">Hari libur dan akhir pekan tidak dihitung sebagai Tidak Hadir.
                                </div>
                            </div>

                            <div class="guide-section">
                                <h5>C. Contoh Penggunaan</h5>
                                <div class="guide-example">
                                    <p class="mb-2"><strong>Kasus:</strong> HRD ingin menindaklanjuti tingginya
                                        keterlambatan di Divisi Logistik pada Triwulan 2 tahun 2026.</p>
                                    <ol class="mb-3">
                                        <li>Pilih <strong>Triwulan 2</strong>, tahun <strong>2026</strong>, divisi
                                            <strong>Logistik</strong>, lalu klik <strong>Terapkan</strong>.
                                        </li>
                                        <li>Lihat kartu <strong>Pemberitahuan Keterlambatan</strong>. Jika muncul <span
                                                class="badge bg-danger">Peringatan Berat</span>, berarti lebih dari 70%
                                            hari kerja ada keterlambatan.</li>
                                        <li>Buka <strong>Kalender Kehadiran</strong> dan cari tanggal dengan jumlah telat
                                            paling banyak, lalu klik tanggal itu.</li>
                                        <li>Catat nama karyawan yang telat pada jendela Detail Harian, lalu klik
                                            <strong>"Lihat Rekap per Orang"</strong> untuk melihat siapa yang paling sering
                                            terlambat.
                                        </li>
                                        <li>Unduh <strong>PDF</strong> sebagai bahan laporan, lalu hubungi karyawan terkait.
                                        </li>
                                    </ol>
                                    <p class="mb-2"><strong>Contoh isi jendela Detail Harian</strong> saat Senin, 16
                                        Maret 2026 diklik:</p>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered bg-white mb-3">
                                            <thead>
                                                <tr>
                                                    <th>Total</th>
                                                    <th>Hadir</th>
                                                    <th>Telat</th>
                                                    <th>Sakit</th>
                                                    <th>Tidak Hadir</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>50</td>
                                                    <td>40</td>
                                                    <td>3</td>
                                                    <td>2</td>
                                                    <td>5</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <p class="mb-2">Di bawahnya, daftar nama per kelompok. Misalnya pada kelompok Telat:
                                    </p>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered bg-white mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Nama</th>
                                                    <th>Jabatan</th>
                                                    <th>Divisi</th>
                                                    <th>Jam Masuk</th>
                                                    <th>Telat</th>
                                                    <th>Catatan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Budi Santoso</td>
                                                    <td>Staff Gudang</td>
                                                    <td>Logistik</td>
                                                    <td>08:25</td>
                                                    <td>25 menit</td>
                                                    <td>Ban motor bocor</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="guide-section">
                                <h5>D. Pertanyaan yang Sering Muncul</h5>
                                <div class="guide-qa">
                                    <strong>Mengapa data pada tanggal tertentu kosong atau tertulis "-"?</strong>
                                    <span>Biasanya tanggal itu hari libur, akhir pekan, atau karyawan tersebut memang tidak
                                        dijadwalkan bekerja.</span>
                                </div>
                                <div class="guide-qa">
                                    <strong>Bagaimana cara mengembalikan filter ke awal?</strong>
                                    <span>Klik tombol <strong>Reset</strong> di sebelah tombol Terapkan. Filter akan kembali
                                        ke bulan dan tahun yang sedang berjalan.</span>
                                </div>
                                <div class="guide-qa">
                                    <strong>Mengapa pilihan Bulan tidak bisa diklik?</strong>
                                    <span>Karena Triwulan sedang dipilih. Kosongkan pilihan Triwulan untuk memilih bulan
                                        lagi.</span>
                                </div>
                            </div>

                            <div class="guide-section">
                                <h5>E. Permintaan Perubahan atau Laporan Error</h5>
                                <div class="guide-itsm">
                                    <p class="mb-2"><strong>Jika ada permintaan perubahan fitur/tampilan, atau Anda
                                            menemukan error atau data yang tidak sesuai, silakan hubungi Tim ITSM.</strong>
                                    </p>
                                    <ol class="mb-3">
                                        <li>Klik tombol <strong>Hubungi Tim ITSM</strong> di bawah.</li>
                                        <li>Pilih jenis permintaan (laporan error, fitur baru, perbaikan, atau masalah
                                            data).</li>
                                        <li>Tuliskan apa yang terjadi, di bagian mana, filter apa yang dipakai, dan hasil
                                            yang Anda harapkan.</li>
                                        <li>Lampirkan tangkapan layar bila ada, lalu kirim.</li>
                                    </ol>
                                    <a href="{{ route('tickets.index') }}" class="btn btn-danger btn-sm"
                                        target="_blank">
                                        <i class="fa-solid fa-ticket me-1"></i>Hubungi Tim ITSM
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm"
                        data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalBuatNotifikasiLibur" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:12px;border:none;box-shadow:var(--shadow-lg)">
                <div class="modal-header" style="background:linear-gradient(135deg,var(--pri) 0%,var(--pri-dark) 100%);color:#fff;border-radius:12px 12px 0 0">
                    <h5 class="modal-title fw-bold mb-0">
                        <i class="fa-regular fa-bell me-2"></i>Buat Notifikasi Hari Libur
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formBuatNotifikasiLibur">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label">Tipe Notifikasi</label>
                            <select name="tipe_notifikasi" id="notif_tipe" class="form-select" required>
                                <option value="Libur" selected>Libur</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Isi Notifikasi</label>
                            <textarea name="isi_notifikasi" id="notif_isi" class="form-control" rows="3" required
                                placeholder="Masukkan isi notifikasi"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" name="tanggal_awal" id="notif_tanggal_awal" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" name="tanggal_akhir" id="notif_tanggal_akhir" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-pri" id="btnSimpanNotifikasi">
                            <i class="fa-solid fa-paper-plane me-1"></i> Kirim Notifikasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const CATS = [{
                key: 'telat',
                label: 'Terlambat',
                color: '#dc2626'
            },
            {
                key: 'cuti',
                label: 'Cuti',
                color: '#0284c7'
            },
            {
                key: 'sakit',
                label: 'Sakit',
                color: '#7c3aed'
            },
            {
                key: 'izin',
                label: 'Izin',
                color: '#0d9488'
            },
            {
                key: 'pending',
                label: 'Menunggu Persetujuan',
                color: '#f97316'
            },
            {
                key: 'tidak_hadir',
                label: 'Tidak Hadir',
                color: '#374151'
            }
        ];
        const DAY_NAMES = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        const chartFont = {
            family: "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto",
            size: 11
        };
        const gridColor = 'rgba(0,0,0,0.05)';

        let leaveChart, divAttChart, notifyChart, dailyStatusChart;
        let currentCalMonth = null,
            currentCalYear = null;
        let filtersLoaded = false;
        let leaveRecapData = [];

        const esc = v => $('<div>').text(v ?? '').html();
        const pct = (n, t) => t > 0 ? ((n / t) * 100).toFixed(1) + '%' : '0%';

        $(document).ready(function() {
            initFilters();
            const [m, y] = calendarTargetFromFilter();

            $.when(loadAnalytics(), loadCalendar(m, y)).always(function() {
                $('#page-skeleton').fadeOut(200, function() {
                    $(this).remove();
                });
                $('#page-real-content').removeClass('d-none').hide().fadeIn(250);
            });

            initCalendarControls();

            $('#btnApplyFilter, #btnRefresh').on('click', applyFilter);
            $('#btnResetFilter').on('click', function() {
                setDefaultFilters();
                $('#filterDivisi').val('all');
                applyFilter();
            });
            $('#filterTriwulan').on('change', function() {
                $('#filterBulan').prop('disabled', !!$(this).val());
            });
            $('#btnExportCsv').on('click', () => exportReport('csv'));
            $('#btnExportPdf').on('click', () => exportReport('pdf'));
            $('#calendarEmployee').on('change', () => loadCalendar(currentCalMonth, currentCalYear));

            $('#btnViewLeaveDetails').on('click', function(e) {
                e.preventDefault();
                bootstrap.Modal.getOrCreateInstance(document.getElementById('leaveDetailsModal')).show();
            });
            $('#leaveRecapSearch').on('input', function() {
                renderLeaveRecap(this.value);
            });
        });

        function initFilters() {
            const nowYear = new Date().getFullYear();
            $('#filterBulan').append('<option value="">Semua</option>');
            for (let i = 1; i <= 12; i++) {
                const name = new Date(2000, i - 1).toLocaleString('id-ID', {
                    month: 'long'
                });
                $('#filterBulan').append(`<option value="${i}">${name}</option>`);
            }
            $('#filterTahun').append('<option value="">Semua</option>');
            for (let y = 2023; y <= nowYear + 1; y++) $('#filterTahun').append(`<option value="${y}">${y}</option>`);
            $('#filterTriwulan').append('<option value="">Tidak dipakai</option>');
            for (let q = 1; q <= 4; q++) $('#filterTriwulan').append(`<option value="${q}">Triwulan ${q}</option>`);
            setDefaultFilters();
        }

        function setDefaultFilters() {
            $('#filterBulan').val(String(new Date().getMonth() + 1)).prop('disabled', false);
            $('#filterTahun').val(String(new Date().getFullYear()));
            $('#filterTriwulan').val('');
        }

        function getFilterParams() {
            return {
                month: $('#filterBulan').val(),
                year: $('#filterTahun').val(),
                quarter: $('#filterTriwulan').val(),
                divisi: $('#filterDivisi').val() || 'all'
            };
        }

        function calendarTargetFromFilter() {
            const now = new Date();
            const f = getFilterParams();
            const y = f.year ? parseInt(f.year) : now.getFullYear();
            let m;
            if (f.quarter) m = (parseInt(f.quarter) - 1) * 3 + 1;
            else if (f.month) m = parseInt(f.month);
            else m = (y === now.getFullYear()) ? now.getMonth() + 1 : 1;
            return [m, y];
        }

        function applyFilter() {
            loadAnalytics();
            const [m, y] = calendarTargetFromFilter();
            loadCalendar(m, y);
        }

        function populateFilterOptions(filters) {
            if (filtersLoaded || !filters) return;
            filtersLoaded = true;
            (filters.divisions || []).forEach(d => $('#filterDivisi').append(
                `<option value="${esc(d)}">${esc(d)}</option>`));
            (filters.employees || []).forEach(e => $('#calendarEmployee').append(
                `<option value="${e.id}">${esc(e.nama)}</option>`));
        }

        function showAnalyticsSkeleton() {
            ['#metricTotalLate', '#metricTotalLeave', '#metricAttendanceRate'].forEach(id => {
                $(id).html('<span class="skel-line" style="width:70px;height:22px;display:inline-block"></span>');
            });
            ['lateDivision', 'leaveChart', 'divAttChart', 'notifyChart'].forEach(id => {
                $(`#${id}-wrap`).addClass('d-none');
                $(`#${id}-skel`).removeClass('d-none');
            });
            const oppSkel = $('#opportunitiesContainer').empty();
            for (let i = 0; i < 3; i++) oppSkel.append(
                '<span class="skel-line" style="height:90px;border-radius:10px;margin-bottom:12px"></span>');
            ['#predNextMonth', '#predNextQuarter', '#predNextYear'].forEach(id => $(id).html(
                '<span class="skel-line" style="width:60px;height:26px;margin:0 auto"></span>'));
            ['#predNextMonthConf', '#predNextQuarterConf', '#predNextYearConf'].forEach(id => $(id).html(
                '<span class="skel-line" style="width:100px;height:11px;margin:6px auto 0"></span>'));
            const msSkel = $('#milestonesContainer').empty();
            for (let i = 0; i < 2; i++) msSkel.append(
                '<span class="skel-line" style="height:60px;border-radius:8px;margin-bottom:10px"></span>');
        }

        function loadAnalytics() {
            showAnalyticsSkeleton();
            return $.get("{{ route('HR.absensi.analytics') }}", getFilterParams(), function(res) {
                if (!res.success) {
                    alert(res.message);
                    return;
                }
                populateFilterOptions(res.filters);
                $('#periodLabel').text(res.period.display);
                $('#leaveRecapPeriod').text(res.period.display);
                renderSummary(res.summary);
                renderCharts(res.charts);
                renderOpportunities(res.opportunities);
                renderPredictions(res.predictions);
            }).fail(() => alert('Gagal memuat data analytics'));
        }

        function renderSummary(s) {
            $('#metricTotalLate').text(s.total_late_minutes + ' menit');
            $('#trendLate').text(`${s.telat} kali · rata-rata ${s.avg_late_minutes} menit`);
            $('#metricTotalLeave').text(s.total_cuti_sakit_izin + ' orang');
            $('#metricAttendanceRate').text(s.attendance_rate + '%');
            $('#trendAttendance').text(`Tanpa keterangan: ${s.tidak_hadir} hari · Menunggu: ${s.pending} hari`);
            leaveRecapData = s.leave_recap || [];
            $('#leaveRecapSearch').val('');
            renderLeaveRecap('');
        }

        function renderLeaveRecap(keyword) {
            const body = $('#leaveRecapBody').empty();
            const kw = (keyword || '').toLowerCase();
            const data = leaveRecapData.filter(r => !kw || (r.nama || '').toLowerCase().includes(kw) || (r.divisi || '')
                .toLowerCase().includes(kw));
            if (!data.length) {
                body.html(
                    '<tr><td colspan="7" class="text-center py-4" style="color:var(--gray-400)">Tidak ada data pada periode ini.</td></tr>'
                );
                return;
            }

            const cell = (v, color) =>
                `<td class="text-center">${v > 0 ? `<span class="employee-badge" style="background:var(--bg-${color});color:var(--c-${color})">${v}</span>` : '<span style="color:var(--gray-400)">-</span>'}</td>`;
            const fmtDate = s => new Date(s + 'T00:00:00').toLocaleDateString('id-ID', {
                weekday: 'short',
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });

            data.forEach((r, idx) => {
                const avatar = r.foto ? `<img src="/storage/${esc(r.foto)}" alt="">` :
                    '<i class="fa-solid fa-user"></i>';

                const detailHtml = (r.details || []).map(d => `
                    <tr>
                        <td style="width:170px;white-space:nowrap">${fmtDate(d.tanggal)}</td>
                        <td style="width:260px"><span class="employee-badge" style="background:var(--bg-${d.key});color:var(--c-${d.key})">${esc(d.jenis)}</span></td>
                        <td style="color:var(--gray-600)">${esc(d.alasan)}</td>
                    </tr>`).join('');

                body.append(`
                    <tr class="recap-main" data-idx="${idx}" style="cursor:pointer">
                        <td><div class="d-flex align-items-center">
                            <i class="fa-solid fa-chevron-right me-2 recap-chevron" style="font-size:.7rem;color:var(--gray-400);transition:transform .2s"></i>
                            <div class="employee-avatar" style="width:32px;height:32px">${avatar}</div>
                            <div><div class="fw-semibold">${esc(r.nama)}</div><div style="font-size:.72rem;color:var(--gray-400)">${esc(r.jabatan || '-')}</div></div>
                        </div></td>
                        <td>${esc(r.divisi || '-')}</td>
                        ${cell(r.cuti, 'cuti')}
                        ${cell(r.sakit, 'sakit')}
                        ${cell(r.izin, 'izin')}
                        <td class="text-center fw-bold">${r.total}</td>
                        ${cell(r.pending, 'pending')}
                    </tr>
                    <tr class="recap-detail d-none" data-idx="${idx}">
                        <td colspan="7" style="background:var(--gray-50);padding:.75rem 1.25rem">
                            <div style="font-size:.72rem;font-weight:700;color:var(--gray-600);text-transform:uppercase;letter-spacing:.3px;margin-bottom:.4rem">Rincian Pengajuan</div>
                            <table class="table table-sm mb-0" style="font-size:.82rem"><tbody>${detailHtml}</tbody></table>
                        </td>
                    </tr>`);
            });

            body.find('.recap-main').on('click', function() {
                const idx = $(this).data('idx');
                body.find(`.recap-detail[data-idx="${idx}"]`).toggleClass('d-none');
                $(this).find('.recap-chevron').css('transform', $(this).find('.recap-chevron').css('transform') ===
                    'none' ? 'rotate(90deg)' : 'none');
            });
        }

        function destroyCharts() {
            [leaveChart, divAttChart, notifyChart].forEach(c => c?.destroy());
        }

        function renderCharts(c) {
            destroyCharts();

            renderLateDivision(c.late_division);
            $('#lateDivision-skel').addClass('d-none');
            $('#lateDivision-wrap').removeClass('d-none');

            const lb = c.leave_breakdown;
            const lbColors = {
                hadir: '#059669',
                cuti: '#0284c7',
                sakit: '#7c3aed',
                izin: '#0d9488'
            };
            $('#leaveChart-skel').addClass('d-none');
            $('#leaveChart-wrap').removeClass('d-none');
            leaveChart = new Chart(document.getElementById('leaveChart'), {
                type: 'bar',
                data: {
                    labels: lb.items.map(i => i.label),
                    datasets: [{
                        data: lb.items.map(i => i.percentage),
                        backgroundColor: lb.items.map(i => lbColors[i.key]),
                        borderRadius: 4
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => `${ctx.parsed.x}% dari 100%`
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            max: 100,
                            grid: {
                                color: gridColor
                            },
                            ticks: {
                                font: chartFont,
                                callback: v => v + '%'
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: chartFont
                            }
                        }
                    }
                }
            });

            const da = c.division_attendance;
            $('#divAttChart-skel').addClass('d-none');
            $('#divAttChart-wrap').removeClass('d-none');
            divAttChart = new Chart(document.getElementById('divAttChart'), {
                type: 'bar',
                data: {
                    labels: da.map(d => d.divisi),
                    datasets: [{
                            label: 'Hadir',
                            data: da.map(d => d.hadir),
                            backgroundColor: '#059669',
                            borderRadius: 4
                        },
                        {
                            label: 'Terlambat',
                            data: da.map(d => d.terlambat),
                            backgroundColor: '#dc2626',
                            borderRadius: 4
                        },
                        {
                            label: 'Izin',
                            data: da.map(d => d.izin),
                            backgroundColor: '#0d9488',
                            borderRadius: 4
                        },
                        {
                            label: 'Sakit',
                            data: da.map(d => d.sakit),
                            backgroundColor: '#7c3aed',
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: chartFont,
                                usePointStyle: true,
                                padding: 15
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => `${ctx.dataset.label}: ${ctx.parsed.y}%`
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            grid: {
                                color: gridColor
                            },
                            ticks: {
                                font: chartFont,
                                callback: v => v + '%'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: chartFont
                            }
                        }
                    }
                }
            });

            renderNotification(c.late_notification);
        }

        function renderLateDivision(list) {
            const wrap = $('#lateDivisionList').empty();
            if (!list || !list.length) {
                wrap.html(
                    '<div class="empty-state"><i class="fa-solid fa-ranking-star"></i><p>Belum ada data kehadiran</p></div>'
                );
                return;
            }
            list.forEach((d, i) => {
                wrap.append(`
                    <div class="div-late-item ${i === 0 && d.late_count > 0 ? 'top' : ''}">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2" style="min-width:0">
                                <span class="div-late-rank">${i + 1}</span>
                                <span class="fw-semibold text-truncate" style="font-size:.85rem;color:var(--gray-900)">${esc(d.divisi)}</span>
                            </div>
                            <span class="fw-bold" style="font-size:.95rem;color:var(--danger)">${d.late_percentage}%</span>
                        </div>
                        <div class="progress mb-2"><div class="progress-bar" style="width:${Math.min(d.late_percentage, 100)}%;background:var(--danger)"></div></div>
                        <div class="d-flex justify-content-between" style="font-size:.72rem;color:var(--gray-600)">
                            <span><i class="fa-solid fa-stopwatch me-1"></i>Rata-rata ${d.avg_late_minutes} menit</span>
                            <span>${d.late_count} terlambat dari ${d.attended} kehadiran</span>
                        </div>
                    </div>`);
            });
        }

        function renderNotification(n) {
            const badge = $('#notifyBadge');
            const map = {
                danger: ['risk-high', 'Peringatan Berat'],
                warning: ['risk-medium', 'Teguran'],
                safe: ['risk-low', 'Aman'],
                none: ['risk-none', 'Tidak ada data']
            };
            const [cls, label] = map[n.status] || map.none;
            badge.attr('class', 'risk-badge ' + cls).text(label);

            const divs = n.divisions || [];
            const berat = divs.filter(d => d.status === 'danger').length;
            const teguran = divs.filter(d => d.status === 'warning').length;
            $('#notifyText').text(n.total_late > 0 ?
                `${berat} divisi peringatan berat · ${teguran} divisi teguran peringatan` :
                'Tidak ada keterlambatan pada periode ini');

            $('#notifyChart-skel').addClass('d-none');
            $('#notifyChart-wrap').removeClass('d-none');

            const list = $('#notifyDivisionList').empty();
            if (!divs.length) {
                list.html(
                    '<div class="empty-state"><i class="fa-solid fa-circle-check"></i><p>Tidak ada notifikasi</p><small>Belum ada divisi dengan keterlambatan di atas 50%</small></div>'
                );
                return;
            }

            divs.slice().sort((a, b) => (a.status === 'danger' ? 0 : 1) - (b.status === 'danger' ? 0 : 1)).forEach(d => {
                const isDanger = d.status === 'danger';
                const color = isDanger ? 'var(--danger)' : 'var(--warning)';
                const bg = isDanger ? 'var(--danger-light)' : 'var(--warning-light)';
                const icon = isDanger ? 'fa-triangle-exclamation' : 'fa-bell';
                const batas = isDanger ? 70 : 50;
                const jenis = isDanger ? 'Peringatan Berat' : 'Teguran Peringatan';

                list.append(`
                    <div class="d-flex gap-3 align-items-start mb-2 p-3" style="background:#fff;border:1px solid var(--gray-200);border-left:4px solid ${color};border-radius:6px">
                        <span style="width:30px;height:30px;border-radius:6px;background:${bg};color:${color};display:inline-flex;align-items:center;justify-content:center;flex-shrink:0">
                            <i class="fa-solid ${icon}" style="font-size:.9rem"></i>
                        </span>
                        <div style="min-width:0">
                            <div style="font-size:.82rem;font-weight:600;color:var(--gray-900);line-height:1.4">
                                Divisi ${esc(d.divisi)} telah melewati batas ${batas}% keterlambatan, mohon memberikan ${jenis}.
                            </div>
                            <div style="font-size:.72rem;color:var(--gray-600);margin-top:.2rem">
                                Rata-rata keterlambatan ${d.percentage}% (${d.late_count} kali terlambat).
                            </div>
                        </div>
                    </div>`);
            });
        }

        function renderOpportunities(opp) {
            const container = $('#opportunitiesContainer').empty();
            if (!opp || opp.length === 0) {
                container.html(
                    '<div class="empty-state"><i class="fa-solid fa-lightbulb"></i><p>Tidak ada rekomendasi saat ini</p><small>Semua metrik berada dalam kondisi optimal</small></div>'
                );
                return;
            }
            opp.forEach(o => {
                const priorityClass = o.priority === 'high' ? 'high' : (o.priority === 'medium' ? 'medium' : 'low');
                const badgeClass = o.priority === 'high' ? 'risk-high' : (o.priority === 'medium' ? 'risk-medium' :
                    'risk-low');
                container.append(`
                    <div class="opp-card ${priorityClass}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="opp-title">${esc(o.title)}</div>
                            <span class="risk-badge ${badgeClass}">${esc(o.priority)}</span>
                        </div>
                        <div class="opp-desc">${esc(o.description)}</div>
                        <div class="opp-meta">
                            <span><i class="fa-solid fa-chart-line"></i>${esc(o.impact)}</span>
                            <span><i class="fa-solid fa-clock"></i>${esc(o.timeline)}</span>
                            <span><i class="fa-solid fa-gauge-high"></i>${esc(o.effort)}</span>
                        </div>
                    </div>`);
            });
        }

        function renderPredictions(p) {
            if (!p || !p.projections) return;
            $('#predNextMonth').text(p.projections.next_month.estimated_rate + '%');
            $('#predNextMonthConf').text(`Confidence: ${p.projections.next_month.confidence}`);
            $('#predNextQuarter').text(p.projections.next_quarter.estimated_rate + '%');
            $('#predNextQuarterConf').text(`Confidence: ${p.projections.next_quarter.confidence}`);
            $('#predNextYear').text(p.projections.next_year.estimated_rate + '%');
            $('#predNextYearConf').text(`Confidence: ${p.projections.next_year.confidence}`);

            const ms = $('#milestonesContainer').empty();
            if (!p.milestones || p.milestones.length === 0) {
                ms.html('<div class="empty-state"><i class="fa-solid fa-flag"></i><p>Belum ada milestone target</p></div>');
                return;
            }
            p.milestones.forEach(m => {
                ms.append(`
                    <div class="milestone-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="milestone-target"><i class="fa-solid fa-bullseye me-2" style="color:var(--pri)"></i>Target ${m.target}%</div>
                            <span class="milestone-timeline">${esc(m.timeline)}</span>
                        </div>
                        <div class="milestone-actions">${m.actions.map(esc).join(' • ')}</div>
                    </div>`);
            });
        }

        function exportReport(format) {
            const params = new URLSearchParams(getFilterParams());
            params.append('format', format);
            window.location.href = `{{ route('HR.absensi.export') }}?${params.toString()}`;
        }

        function renderCalendarSkeleton() {
            const container = $('#attendanceCalendar').empty();
            DAY_NAMES.forEach(d => container.append(`<div class="calendar-header">${d}</div>`));
            for (let i = 0; i < 35; i++) {
                container.append(
                    '<div class="calendar-day skeleton-day"><span class="skel-line" style="width:40%;height:12px;margin-bottom:6px"></span><span class="skel-line" style="width:70%;height:14px"></span></div>'
                );
            }
        }

        function loadCalendar(month, year) {
            currentCalMonth = parseInt(month);
            currentCalYear = parseInt(year);

            const monthName = new Date(currentCalYear, currentCalMonth - 1)
                .toLocaleString('id-ID', { month: 'long', year: 'numeric' });
            $('#calendarMonthLabel').text(monthName.charAt(0).toUpperCase() + monthName.slice(1));

            renderCalendarSkeleton();
            loadHolidayList();                 
            tampilkanDetailHariLibur(null);

            return $.get("{{ route('HR.absensi.calendar') }}", {
                month: currentCalMonth,
                year: currentCalYear,
                id_karyawan: $('#calendarEmployee').val(),
                divisi: $('#filterDivisi').val() || 'all'
            }, function (res) {
                if (!res.success) return;

                renderCalendar(res.calendar, res.month, res.year);
                renderCalendarSummary(res.totals);

                renderHolidayList(res.calendar);
            });
        }

        function renderCalendar(days, month, year) {
            const container = $('#attendanceCalendar').empty();
            const todayDate = new Date();
            const today = `${todayDate.getFullYear()}-${String(todayDate.getMonth() + 1).padStart(2, '0')}-${String(todayDate.getDate()).padStart(2, '0')}`;

            DAY_NAMES.forEach(d => container.append(`<div class="calendar-header">${d}</div>`));

            const firstDay = new Date(year, month - 1, 1).getDay();
            for (let i = 0; i < firstDay; i++) {
                container.append('<div class="calendar-empty"></div>');
            }

            days.forEach(d => {
                const classes = ['calendar-day'];
                if (d.is_holiday) classes.push('holiday');
                else if (d.is_weekend) classes.push('weekend');
                if (d.is_future) classes.push('future');
                if (d.date === today) classes.push('today');

                let dots = '';
                const tips = [];
                if (d.holiday_name) tips.push(d.holiday_name);

                CATS.forEach(c => {
                    const n = d.counts?.[c.key] || 0;
                    if (n > 0) {
                        dots += `<span class="dot dot-${c.key}">${n}</span>`;
                        tips.push(`${c.label}: ${n}`);
                    }
                });

                const el = $(`
                    <div class="${classes.join(' ')}" data-date="${d.date}"
                        title="${esc(tips.join(' | ') || 'Klik untuk lihat detail')}">
                        <div>
                            <span class="day-num">${d.day}</span>
                            ${d.holiday_name ? `<div class="holiday-name">${esc(d.holiday_name)}</div>` : ''}
                        </div>
                        <div class="dots">${dots}</div>
                    </div>
                `);

                el.on('click', function () {
                    if (d.is_holiday) {
                        tampilkanDetailHariLibur({
                            tanggal: d.date,
                            nama: d.holiday_name,
                            type: d.holiday_type || 'nasional'
                        });
                    } else {
                        showDailyDetails(d.date);
                    }
                });

                container.append(el);
            });
        }

        function renderCalendar(days, month, year) {
            const container = $('#attendanceCalendar').empty();
            const todayDate = new Date();
            const today =
                `${todayDate.getFullYear()}-${String(todayDate.getMonth() + 1).padStart(2, '0')}-${String(todayDate.getDate()).padStart(2, '0')}`;

            DAY_NAMES.forEach(d => container.append(`<div class="calendar-header">${d}</div>`));

            const firstDay = new Date(year, month - 1, 1).getDay();
            for (let i = 0; i < firstDay; i++) container.append('<div class="calendar-empty"></div>');

            days.forEach(d => {
                const classes = ['calendar-day'];
                if (d.is_holiday) classes.push('holiday');
                else if (d.is_weekend) classes.push('weekend');
                if (d.is_future) classes.push('future');
                if (d.date === today) classes.push('today');

                let dots = '';
                const tips = [];
                if (d.holiday_name) tips.push(d.holiday_name);

                CATS.forEach(c => {
                    const n = d.counts[c.key] || 0;
                    if (n > 0) {
                        dots += `<span class="dot dot-${c.key}">${n}</span>`;
                        tips.push(`${c.label}: ${n}`);
                    }
                });

                const el = $(`
                    <div class="${classes.join(' ')}" data-date="${d.date}" 
                        title="${esc(tips.join(' | ') || 'Klik untuk lihat detail')}">
                        <div>
                            <span class="day-num">${d.day}</span>
                            ${d.holiday_name ? `<div class="holiday-name">${esc(d.holiday_name)}</div>` : ''}
                        </div>
                        <div class="dots">${dots}</div>
                    </div>
                `);

                el.on('click', function() {
                    const date = $(this).data('date');

                    if (d.is_holiday) {
                        tampilkanDetailHariLibur({
                            tanggal: d.date,
                            date: d.date,
                            nama: d.holiday_name,
                            description: d.holiday_name,
                            type: d.holiday_type || 'nasional'
                        });
                    } else {
                        showDailyDetails(date);
                    }
                });

                container.append(el);
            });
        }

        function renderCalendarSummary(t) {
            const box = $('#calendarSummary').empty();
            CATS.forEach(c => {
                box.append(`
                    <div class="legend-row">
                        <span class="left"><span class="legend-swatch" style="background:${c.color}"></span>${c.label}</span>
                        <span class="legend-count">${t[c.key] || 0}</span>
                    </div>`);
            });
            box.append(
                `
                <div class="legend-row" style="background:#fef3c7;border-color:#fcd34d">
                    <span class="left"><span class="legend-swatch" style="background:#fcd34d"></span>Libur Nasional (hari)</span>
                    <span class="legend-count">${t.libur || 0}</span>
                </div>
                <div class="legend-row">
                    <span class="left"><span class="legend-swatch" style="background:var(--gray-200)"></span>Akhir Pekan (hari)</span>
                    <span class="legend-count">${t.weekend || 0}</span>
                </div>
                <div style="font-size:.72rem;color:var(--gray-400)" class="mt-2">Angka = jumlah karyawan-hari pada bulan ini. Izin yang menutup keterlambatan dihitung sebagai izin, bukan terlambat.</div>`
            );
        }

        function showDailyDetails(date) {
            const modalEl = document.getElementById('dailyDetailsModal');
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();

            $('#modalLoading').show();
            $('#modalContent').hide();

            $.get("{{ route('HR.absensi.daily-details') }}", {
                date
            }, function(res) {
                if (!res.success) {
                    alert(res.message);
                    modal.hide();
                    return;
                }

                $('#modalDateLabel').text(res.date);
                $('#modalDayName').text(res.day_name);

                const box = $('#holidayAlert');
                if (res.is_holiday) {
                    box.show().removeClass('alert-secondary alert-info').addClass('alert-warning');
                    $('#holidayAlertTitle').text('Hari Libur Nasional');
                    $('#holidayAlertDesc').text(res.holiday_names.join(', '));
                } else if (res.is_weekend) {
                    box.show().removeClass('alert-warning alert-info').addClass('alert-secondary');
                    $('#holidayAlertTitle').text('Akhir Pekan');
                    $('#holidayAlertDesc').text(
                        'Hanya karyawan yang bekerja di hari Sabtu atau yang tetap masuk yang tercantum.');
                } else {
                    box.hide();
                }

                const s = res.statistics;
                const map = {
                    present: s.present,
                    late: s.late,
                    leave: s.on_leave,
                    sick: s.sick,
                    izin: s.izin,
                    pending: s.pending,
                    absent: s.absent
                };
                Object.keys(map).forEach(k => {
                    $(`#stat-${k}`).text(map[k]);
                    $(`#stat-${k}-pct`).text(pct(map[k], s.total));
                });

                renderDailyStatusChart(map);

                renderEmployeeList('present', res.employees.present);
                renderEmployeeList('late', res.employees.late);
                renderEmployeeList('leave', res.employees.on_leave);
                renderEmployeeList('sick', res.employees.sick);
                renderEmployeeList('izin', res.employees.izin);
                renderEmployeeList('pending', res.employees.pending);
                renderEmployeeList('absent', res.employees.absent);

                $('#modalLoading').hide();
                $('#modalContent').show();
            }).fail(() => {
                alert('Gagal memuat detail kehadiran');
                modal.hide();
            });
        }

        function renderDailyStatusChart(m) {
            if (dailyStatusChart) dailyStatusChart.destroy();
            const total = Object.values(m).reduce((a, b) => a + b, 0);
            dailyStatusChart = new Chart(document.getElementById('dailyStatusChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Hadir', 'Terlambat', 'Cuti', 'Sakit', 'Izin', 'Menunggu', 'Tanpa Keterangan'],
                    datasets: [{
                        data: total > 0 ? [m.present, m.late, m.leave, m.sick, m.izin, m.pending, m
                            .absent
                        ] : [1],
                        backgroundColor: total > 0 ? ['#059669', '#dc2626', '#0284c7', '#7c3aed', '#0d9488',
                            '#f97316', '#374151'
                        ] : ['#e5e7eb'],
                        borderWidth: 3,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: chartFont,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            enabled: total > 0,
                            callbacks: {
                                label: ctx => `${ctx.label}: ${ctx.parsed} karyawan (${pct(ctx.parsed, total)})`
                            }
                        }
                    }
                }
            });
        }

        function renderEmployeeList(type, employees) {
            const container = $(`#${type}List`).empty();
            $(`#${type}Count`).text(employees.length);

            if (employees.length === 0) {
                container.html(
                    '<div class="text-center py-3" style="color:var(--gray-400)"><i class="fa-solid fa-inbox" style="font-size:1.5rem;opacity:.3"></i><p class="mb-0 mt-2" style="font-size:.82rem">Tidak ada karyawan</p></div>'
                );
                return;
            }

            const badge = (key, text) =>
                `<span class="employee-badge" style="background:var(--bg-${key});color:var(--c-${key})">${esc(text)}</span>`;

            employees.forEach(emp => {
                let meta = '';
                let note = '';
                if (type === 'present') {
                    meta =
                        `<div class="employee-time">${esc(emp.jam_masuk || '-')}</div>${badge('hadir', 'Tepat Waktu')}`;
                } else if (type === 'late') {
                    meta =
                        `<div class="employee-time">${esc(emp.jam_masuk || '-')}</div>${badge('telat', '+' + emp.late_minutes + ' mnt')}`;
                } else if (type === 'leave') {
                    meta = badge('cuti', emp.label);
                } else if (type === 'sick') {
                    meta = badge('sakit', emp.label);
                } else if (type === 'izin') {
                    meta = (emp.jam_masuk ? `<div class="employee-time">${esc(emp.jam_masuk)}</div>` : '') + badge(
                        'izin', emp.label);
                } else if (type === 'pending') {
                    meta = badge('pending', emp.label);
                } else {
                    meta = badge('tidak_hadir', emp.label);
                }
                if (emp.catatan && !['present', 'late', 'absent'].includes(type)) {
                    note =
                        ` • ${esc(String(emp.catatan).substring(0, 40))}${String(emp.catatan).length > 40 ? '...' : ''}`;
                }

                const avatar = emp.foto ? `<img src="/storage/${esc(emp.foto)}" alt="">` :
                    '<i class="fa-solid fa-user"></i>';

                container.append(`
                    <div class="employee-card">
                        <div class="employee-avatar">${avatar}</div>
                        <div class="employee-info">
                            <div class="employee-name">${esc(emp.nama)}</div>
                            <div class="employee-position">${esc(emp.jabatan)} • ${esc(emp.divisi)}${note}</div>
                        </div>
                        <div class="employee-meta">${meta}</div>
                    </div>`);
            });
        }

        function initCalendarControls() {
            $('#btnPrevMonth').on('click', () => {
                let m = currentCalMonth - 1,
                    y = currentCalYear;
                if (m < 1) {
                    m = 12;
                    y--;
                }
                loadCalendar(m, y);
            });
            $('#btnNextMonth').on('click', () => {
                let m = currentCalMonth + 1,
                    y = currentCalYear;
                if (m > 12) {
                    m = 1;
                    y++;
                }
                loadCalendar(m, y);
            });
        }

        const typeClassMap = {
            nasional: {
                badge: 'bg-warning-subtle text-warning',
                border: 'border-warning'
            },
            cuti_bersama: {
                badge: 'bg-info-subtle text-info',
                border: 'border-info'
            },
            khusus: {
                badge: 'bg-primary-subtle text-primary',
                border: 'border-primary'
            },
            default: {
                badge: 'bg-secondary-subtle text-secondary',
                border: 'border-secondary'
            }
        };

        function loadHolidayList() {
            $('#holiday-list').html(`
                <div class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2 mb-0 small">Memuat data libur...</p>
                </div>
            `);
        }

        function loadCalendar(month, year) {
            currentCalMonth = parseInt(month);
            currentCalYear = parseInt(year);
            const monthName = new Date(currentCalYear, currentCalMonth - 1).toLocaleString('id-ID', {
                month: 'long',
                year: 'numeric'
            });
            $('#calendarMonthLabel').text(monthName.charAt(0).toUpperCase() + monthName.slice(1));
            renderCalendarSkeleton();

            loadHolidayList(currentCalMonth, currentCalYear);
            tampilkanDetailHariLibur(null);

            return $.get("{{ route('HR.absensi.calendar') }}", {
                month: currentCalMonth,
                year: currentCalYear,
                id_karyawan: $('#calendarEmployee').val(),
                divisi: $('#filterDivisi').val() || 'all'
            }, function(res) {
                if (!res.success) return;
                renderCalendar(res.calendar, res.month, res.year);
                renderCalendarSummary(res.totals);
            });
        }

        function renderHolidayList(days) {
            const $list = $('#holiday-list');
            
            const holidays = (days || []).filter(d => d.is_holiday && d.holiday_name);

            if (holidays.length === 0) {
                $list.html(`
                    <div class="text-center py-4">
                        <i class="fa-solid fa-calendar-xmark text-muted" style="font-size:2rem;opacity:.4"></i>
                        <p class="text-muted small mt-2 mb-0">Tidak ada hari libur di bulan ini</p>
                    </div>
                `);
                return;
            }

            let html = '';
            holidays.forEach(h => {
                const dateObj = new Date(h.date + 'T00:00:00');
                const dayName = dateObj.toLocaleDateString('id-ID', { weekday: 'short' });
                const dayNum = dateObj.getDate();

                html += `
                    <button type="button" class="btn btn-info text-white w-100 text-start" id="btnBuatPengumumanLibur" data-tanggal="${esc(data.tanggal)}" data-nama="${esc(data.nama)}">
                        <div class="holiday-item d-flex align-items-center gap-3 p-2 mb-2 rounded-3 border"
                            style="cursor:pointer;background:#fff;transition:all .15s"
                            data-date="${h.date}"
                            data-nama="${esc(h.holiday_name)}"
                            data-type="${h.holiday_type || 'nasional'}">
                            <div class="badge bg-warning-subtle text-warning d-flex align-items-center justify-content-center"
                                style="width:42px;height:42px;font-size:1rem;font-weight:700;flex-shrink:0">
                                ${dayNum}
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-semibold text-dark text-truncate" style="font-size:.85rem">
                                    ${esc(h.holiday_name)}
                                </div>
                                <small class="text-muted">${dayName}</small>
                            </div>
                        </div>
                    </button>
                `;
            });

            $list.html(html);

            $list.find('.holiday-item').on('click', function () {
                tampilkanDetailHariLibur({
                    tanggal: $(this).data('date'),
                    nama: $(this).data('nama'),
                    type: $(this).data('type')
                });
            });
        }

        function tampilkanDetailHariLibur(data) {
            if (!data) {
                $('#holiday-detail').html(`
                    <div class="text-center py-3">
                        <i class="bx bx-info-circle text-muted" style="font-size:2.5rem;opacity:0.5"></i>
                        <p class="text-muted small mt-2 mb-0">Klik tanggal libur di kalender</p>
                    </div>
                `);
                return;
            }

            const dateObj = new Date(data.tanggal + 'T00:00:00');
            const dayName = dateObj.toLocaleDateString('id-ID', { weekday: 'long' });
            const formattedDate = dateObj.toLocaleDateString('id-ID', {
                year: 'numeric', month: 'long', day: 'numeric'
            });

            $('#holiday-detail').html(`
                <div class="holiday-detail-card">
                    <div class="d-flex align-items-center mb-3">
                        <div class="badge bg-warning-subtle text-warning px-3 py-2 d-flex align-items-center justify-content-center"
                            style="min-width:50px;font-size:1.2rem;height:50px;font-weight:700">
                            ${dateObj.getDate()}
                        </div>

                        <div class="ms-3">
                            <small class="text-muted d-block text-capitalize">Hari Libur</small>
                            <small class="text-muted fw-medium">${dayName}</small>
                        </div>
                    </div>

                    <div class="bg-light rounded-3 p-3 border-start border-4 border-warning mb-3">
                        <h6 class="mb-1 fw-bold text-dark">${esc(data.nama)}</h6>
                        <small class="text-muted d-block">${formattedDate}</small>

                        <div class="text-end mt-3">
                            <button type="button"
                                class="btn btn-info text-white"
                                id="btnBuatPengumumanLibur"
                                data-tanggal="${esc(data.tanggal)}"
                                data-nama="${esc(data.nama)}">
                                <i class="fa-regular fa-bell me-1"></i>
                                Buat Pengumuman
                            </button>
                        </div>
                    </div>
                </div>
            `);

            // Buka modal saat tombol diklik
            $('#btnBuatPengumumanLibur').off('click').on('click', function () {
                const tanggal = $(this).data('tanggal');
                const nama = $(this).data('nama');

                // Prefill form
                $('#notif_tipe').val('Libur');
                $('#notif_isi').val(`Hari Libur: ${nama}`);
                $('#notif_tanggal_awal').val(tanggal);
                $('#notif_tanggal_akhir').val(tanggal);

                // Tampilkan modal
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalBuatNotifikasiLibur')).show();
            });
        }

        $('#formBuatNotifikasiLibur').on('submit', function (e) {
            e.preventDefault();

            const $btn = $('#btnSimpanNotifikasi');
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

            $.ajax({
                url: "{{ route('notif.store') }}",
                method: 'POST',
                data: $(this).serialize(),
                success: function (res) {
                    if (res.success) {
                        bootstrap.Modal.getInstance(document.getElementById('modalBuatNotifikasiLibur')).hide();
                        $('#formBuatNotifikasiLibur')[0].reset();
                        alert(res.message || 'Notifikasi berhasil dikirim');
                    } else {
                        alert(res.message || 'Gagal menyimpan notifikasi');
                    }
                },
                error: function (xhr) {
                    let msg = 'Terjadi kesalahan';
                    if (xhr.responseJSON?.message) {
                        msg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON?.errors) {
                        msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    }
                    alert(msg);
                },
                complete: function () {
                    $btn.prop('disabled', false).html('<i class="fa-solid fa-paper-plane me-1"></i> Kirim Notifikasi');
                }
            });
        });

        $('#formTambahHariLibur').on('submit', function (e) {
            e.preventDefault();

            const $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

            $.ajax({
                url: "{{ route('storeHariLibur') }}",
                method: 'POST',
                data: $(this).serialize(),
                success: function (res) {
                    if (res.success !== false) {
                        $('#formTambahHariLibur')[0].reset();

                        loadCalendar(currentCalMonth, currentCalYear);
                    } else {
                        alert(res.message || 'Gagal menyimpan');
                    }
                },
                error: function (xhr) {
                    alert(xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan');
                },
                complete: function () {
                    $btn.prop('disabled', false).html('Simpan');
                }
            });
        });
    </script>
@endsection
