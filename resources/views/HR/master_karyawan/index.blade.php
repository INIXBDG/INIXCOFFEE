@extends('layout_HR.app')

@push('styles')
    <style>
        :root {
            --pri: #4f46e5;
            --pri-dark: #3730a3;
            --pri-light: #eef2ff;
            --success: #059669;
            --success-light: #d1fae5;
            --warning: #d97706;
            --warning-light: #fef3c7;
            --info: #0284c7;
            --info-light: #e0f2fe;
            --danger: #dc2626;
            --danger-light: #fee2e2;
            --purple: #7c3aed;
            --purple-light: #ede9fe;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-900: #111827;
            --radius: 10px;
            --shadow: 0 1px 3px rgba(0, 0, 0, .06), 0 1px 2px rgba(0, 0, 0, .04);
        }

        .mdk {
            color: var(--gray-900);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .modal-backdrop {
            --bs-backdrop-bg: #000;
            --bs-backdrop-opacity: .35;
        }

        .modal-backdrop.show {
            opacity: .35;
        }

        .mdk .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-900);
            margin: 0;
        }

        .mdk .page-sub {
            color: var(--gray-500);
            font-size: .875rem;
            margin: .15rem 0 0;
        }

        .mdk h2 {
            font-size: 1rem;
            font-weight: 700;
            margin: 0;
            color: var(--gray-900);
        }

        .mdk .sub {
            color: var(--gray-500);
            font-size: .85rem;
        }

        .mdk .panel {
            background: #fff;
            border: 0;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .mdk .panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: 1.1rem 1.4rem;
            border-bottom: 1px solid var(--gray-200);
            flex-wrap: wrap;
        }

        .mdk .panel-head h2 {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .95rem;
            color: var(--gray-700);
        }

        .mdk .panel-head h2 i {
            color: var(--pri);
        }

        .mdk .panel-body {
            padding: 1.25rem 1.4rem;
        }

        .mdk .btn {
            border-radius: 8px;
            font-weight: 600;
            font-size: .85rem;
        }

        .mdk .btn-brand {
            background: var(--pri);
            color: #fff;
            border: 1px solid var(--pri);
        }

        .mdk .btn-brand:hover,
        .mdk .btn-brand:focus {
            background: var(--pri-dark);
            border-color: var(--pri-dark);
            color: #fff;
        }

        .mdk .btn-line {
            background: #fff;
            border: 1px solid var(--gray-200);
            color: var(--gray-700);
        }

        .mdk .btn-line:hover {
            border-color: var(--pri);
            color: var(--pri);
            background: var(--pri-light);
        }

        .mdk .btn:focus-visible,
        .mdk .form-control:focus,
        .mdk .form-select:focus {
            box-shadow: 0 0 0 3px rgba(79, 70, 229, .15);
            border-color: var(--pri);
        }

        .mdk .form-control,
        .mdk .form-select {
            font-size: .875rem;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            color: var(--gray-700);
        }

        .mdk .form-label {
            font-size: .78rem;
            font-weight: 600;
            color: var(--gray-600);
            margin-bottom: .3rem;
        }

        .mdk .lock-pill {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .4rem .8rem;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            background: #fff;
            font-size: .85rem;
            font-weight: 600;
            color: var(--gray-700);
            font-variant-numeric: tabular-nums;
        }

        .mdk .lock-pill i {
            color: var(--pri);
        }

        .mdk .summary {
            display: grid;
            grid-template-columns: 1.4fr 1fr 1fr 1fr;
            gap: 1rem;
        }

        .mdk .stat {
            padding: 1.1rem 1.3rem;
            position: relative;
            overflow: hidden;
        }

        .mdk .stat::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--pri);
        }

        .mdk .stat.warn::before {
            background: var(--warning);
        }

        .mdk .stat.bad::before {
            background: var(--danger);
        }

        .mdk .stat.purple::before {
            background: var(--purple);
        }

        .mdk .stat-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: .75rem;
        }

        .mdk .stat .l {
            color: var(--gray-500);
            font-size: .8rem;
            font-weight: 600;
        }

        .mdk .stat .n {
            font-size: 1.9rem;
            font-weight: 700;
            line-height: 1.1;
            margin-top: .35rem;
            color: var(--gray-900);
        }

        .mdk .stat .foot {
            color: var(--gray-500);
            font-size: .78rem;
            margin-top: .35rem;
        }

        .mdk .stat-ico {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            font-size: 1.05rem;
            color: #fff;
            flex: none;
        }

        .mdk .bg-pri {
            background: linear-gradient(135deg, #6366f1, var(--pri));
        }

        .mdk .bg-warn {
            background: linear-gradient(135deg, #f59e0b, var(--warning));
        }

        .mdk .bg-bad {
            background: linear-gradient(135deg, #ef4444, var(--danger));
        }

        .mdk .bg-purple {
            background: linear-gradient(135deg, #8b5cf6, var(--purple));
        }

        @keyframes mdkShimmer {
            to {
                background-position: -200% 0;
            }
        }

        .mdk .summary.is-loading .n {
            color: transparent;
            width: 70px;
            border-radius: 6px;
            background: linear-gradient(90deg, var(--gray-100) 25%, var(--gray-200) 50%, var(--gray-100) 75%);
            background-size: 200% 100%;
            animation: mdkShimmer 1.5s infinite;
        }

        .mdk .split-bar {
            display: flex;
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
            margin: .75rem 0 .55rem;
            background: var(--gray-100);
        }

        .mdk .split-bar span:nth-child(1) {
            background: var(--warning);
        }

        .mdk .split-bar span:nth-child(2) {
            background: var(--info);
        }

        .mdk .split-bar span:nth-child(3) {
            background: var(--success);
        }

        .mdk .legend {
            display: flex;
            gap: 1rem;
            font-size: .78rem;
            color: var(--gray-500);
            flex-wrap: wrap;
        }

        .mdk .legend i {
            display: inline-block;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            margin-right: .35rem;
        }

        .mdk .tag {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            font-size: .72rem;
            font-weight: 700;
            padding: .22rem .6rem;
            border-radius: 6px;
            white-space: nowrap;
        }

        .mdk .tag-probation {
            background: var(--warning-light);
            color: var(--warning);
        }

        .mdk .tag-kontrak {
            background: var(--info-light);
            color: var(--info);
        }

        .mdk .tag-kartap {
            background: var(--success-light);
            color: var(--success);
        }

        .mdk .tag-konfirmasi {
            background: var(--purple-light);
            color: var(--purple);
        }

        .mdk .tag-done {
            background: var(--success-light);
            color: var(--success);
        }

        .mdk .tag-pending {
            background: var(--warning-light);
            color: var(--warning);
        }

        .mdk .tag-late {
            background: var(--danger-light);
            color: var(--danger);
        }

        .mdk .tag-muted {
            background: var(--gray-100);
            color: var(--gray-500);
        }

        .mdk .page-tabs {
            display: flex;
            gap: .25rem;
            border-bottom: 2px solid var(--gray-200);
            margin: 1.5rem 0 1.25rem;
            overflow-x: auto;
        }

        .mdk .page-tabs a {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .8rem 1.1rem;
            color: var(--gray-500);
            font-weight: 600;
            font-size: .875rem;
            text-decoration: none;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            white-space: nowrap;
            transition: color .15s;
        }

        .mdk .page-tabs a:hover {
            color: var(--pri);
        }

        .mdk .page-tabs a.active {
            color: var(--pri);
            border-bottom-color: var(--pri);
        }

        .mdk .page-tabs a:focus-visible {
            outline: 3px solid rgba(79, 70, 229, .25);
            outline-offset: -3px;
            border-radius: 6px;
        }

        .mdk .page-tabs .count {
            background: var(--gray-100);
            color: var(--gray-600);
            font-size: .72rem;
            padding: .1rem .55rem;
            border-radius: 20px;
        }

        .mdk .page-tabs a.active .count {
            background: var(--pri-light);
            color: var(--pri);
        }

        .mdk .filters {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: .75rem;
            padding: 1rem 1.4rem;
            border-bottom: 1px solid var(--gray-200);
            background: var(--gray-50);
        }

        .mdk .search-wrap {
            position: relative;
        }

        .mdk .search-wrap i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            font-size: .85rem;
        }

        .mdk .search-wrap .form-control {
            padding-left: 2.2rem;
        }

        .mdk table.dataTable thead th,
        .mdk table.table thead th {
            font-size: .7rem;
            color: var(--gray-500);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            background: var(--gray-50);
            white-space: nowrap;
            border-bottom: 1px solid var(--gray-200);
            padding: .85rem 1rem;
        }

        .mdk table.dataTable td {
            vertical-align: middle;
            font-size: .85rem;
            padding: .85rem 1rem;
            color: var(--gray-700);
        }

        .mdk table.dataTable tbody tr:hover {
            background: var(--gray-50);
        }

        .mdk table.dataTable tbody {
            transition: opacity .15s ease;
        }

        .mdk table.is-loading tbody {
            opacity: .4;
            pointer-events: none;
        }

        .mdk div.dataTables_processing {
            position: absolute;
            top: 50%;
            left: 50%;
            width: auto;
            margin: 0;
            padding: .6rem 1.1rem;
            transform: translate(-50%, -50%);
            background: #fff;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, .1);
            z-index: 5;
            font-size: .85rem;
            font-weight: 600;
            color: var(--pri);
            white-space: nowrap;
        }

        .mdk .dataTables_info,
        .mdk .dataTables_length label {
            font-size: .82rem;
            color: var(--gray-500);
        }

        .mdk .pagination .page-link {
            border: 1px solid var(--gray-200);
            color: var(--gray-600);
            font-size: .82rem;
            padding: .35rem .7rem;
            margin: 0 2px;
            border-radius: 6px;
        }

        .mdk .pagination .page-item.active .page-link {
            background: var(--pri);
            border-color: var(--pri);
            color: #fff;
        }

        .mdk .pagination .page-item.disabled .page-link {
            color: var(--gray-400);
        }

        .mdk .person {
            display: flex;
            align-items: center;
            gap: .7rem;
            min-width: 200px;
        }

        .mdk .avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--pri-light);
            color: var(--pri);
            display: grid;
            place-items: center;
            font-weight: 700;
            font-size: .8rem;
            flex: none;
            object-fit: cover;
        }

        .mdk .person .nm {
            font-weight: 600;
            line-height: 1.2;
            color: var(--gray-900);
        }

        .mdk .person .nip {
            color: var(--gray-500);
            font-size: .75rem;
        }

        .mdk .doc-meter {
            min-width: 130px;
        }

        .mdk .doc-meter .progress {
            height: 6px;
            background: var(--gray-100);
            border-radius: 3px;
        }

        .mdk .doc-meter .progress-bar {
            background: var(--success);
            border-radius: 3px;
        }

        .mdk .doc-meter .progress-bar.low {
            background: var(--warning);
        }

        .mdk .doc-meter small {
            color: var(--gray-500);
            font-size: .72rem;
        }

        .mdk .act .btn {
            padding: .3rem .6rem;
        }

        .mdk .reminder-filter {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .mdk .reminder-list {
            padding: 1.1rem 1.4rem;
            display: flex;
            flex-direction: column;
            gap: .6rem;
        }

        .mdk .reminder {
            display: grid;
            grid-template-columns: 38px 1fr auto;
            gap: .9rem;
            align-items: center;
            padding: .8rem 1rem;
            border: 1px solid var(--gray-200);
            border-left: 4px solid var(--warning);
            border-radius: 8px;
            background: #fff;
            transition: background .15s;
        }

        .mdk .reminder:hover {
            background: var(--gray-50);
        }

        .mdk .reminder.late {
            border-left-color: var(--danger);
        }

        .mdk .reminder.done {
            border-left-color: var(--success);
        }

        .mdk .reminder .r-ico {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            background: var(--warning-light);
            color: var(--warning);
        }

        .mdk .reminder.late .r-ico {
            background: var(--danger-light);
            color: var(--danger);
        }

        .mdk .reminder.done .r-ico {
            background: var(--success-light);
            color: var(--success);
        }

        .mdk .reminder .who {
            font-weight: 700;
            font-size: .88rem;
            color: var(--gray-900);
        }

        .mdk .reminder .what {
            color: var(--gray-500);
            font-size: .8rem;
            margin-top: .1rem;
        }

        .mdk .empty {
            padding: 2.2rem 1rem;
            text-align: center;
            color: var(--gray-400);
            font-size: .85rem;
        }

        .mdk .empty i.big {
            display: block;
            font-size: 1.8rem;
            opacity: .45;
            margin-bottom: .5rem;
        }

        .mdk .empty.loading {
            color: var(--pri);
            font-weight: 600;
        }

        .mdk-modal .modal-content {
            border: 0;
            border-radius: 12px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, .2);
            overflow: hidden;
        }

        .mdk-modal .profile-head {
            display: flex;
            gap: 1rem;
            align-items: center;
            padding: 1.4rem 1.5rem;
            background: linear-gradient(135deg, var(--pri) 0%, var(--pri-dark) 100%);
            color: #fff;
        }

        .mdk-modal .profile-head h2 {
            color: #fff;
            font-size: 1.2rem;
        }

        .mdk-modal .profile-head .sub {
            color: rgba(255, 255, 255, .85);
        }

        .mdk-modal .profile-head .avatar {
            width: 64px;
            height: 64px;
            font-size: 1.2rem;
            background: rgba(255, 255, 255, .95);
            color: var(--pri);
            border: 3px solid rgba(255, 255, 255, .45);
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-weight: 700;
            overflow: hidden;
            flex: none;
        }

        .mdk-modal .profile-head .btn-line {
            border: 0;
        }

        .mdk-modal .profile-head .btn-close {
            filter: brightness(0) invert(1);
            opacity: .8;
        }

        .mdk-modal .nav-tabs {
            border-bottom: 2px solid var(--gray-200);
            padding: 0 1.25rem;
            gap: .25rem;
            flex-wrap: nowrap;
            overflow-x: auto;
            background: #fff;
        }

        .mdk-modal .nav-tabs .nav-link {
            display: flex;
            align-items: center;
            gap: .5rem;
            border: 0;
            border-bottom: 3px solid transparent;
            color: var(--gray-500);
            font-weight: 600;
            font-size: .85rem;
            white-space: nowrap;
            padding: .85rem 1rem;
            margin-bottom: -2px;
            background: none;
        }

        .mdk-modal .nav-tabs .nav-link:hover {
            color: var(--pri);
        }

        .mdk-modal .nav-tabs .nav-link.active {
            color: var(--pri);
            border-bottom-color: var(--pri);
            background: none;
        }

        .mdk-modal .modal-body {
            background: #fff;
        }

        .mdk-modal .section-title,
        .mdk .section-title {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .78rem;
            color: var(--pri);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin: 1.6rem 0 .85rem;
        }

        .mdk .section-title:first-child {
            margin-top: 0;
        }

        .mdk .kv {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .75rem;
            margin: 0;
        }

        .mdk .kv>div {
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            padding: .65rem .9rem;
        }

        .mdk .kv dt {
            font-size: .7rem;
            color: var(--gray-500);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .3px;
            margin: 0 0 .15rem;
        }

        .mdk .kv dd {
            margin: 0;
            font-weight: 600;
            font-size: .88rem;
            color: var(--gray-900);
            word-break: break-word;
        }

        .mdk .note-box {
            background: var(--gray-50);
            border-left: 4px solid var(--pri);
            border-radius: 8px;
            padding: .8rem 1rem;
            font-size: .85rem;
            color: var(--gray-700);
            white-space: pre-line;
            margin: 0;
        }

        .mdk .auto-box {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: .75rem;
        }

        .mdk .auto-tile {
            display: flex;
            align-items: center;
            gap: .8rem;
            border-radius: 10px;
            padding: .9rem 1rem;
            border: 1px solid var(--gray-200);
            background: #fff;
        }

        .mdk .auto-tile .ic {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            flex: none;
        }

        .mdk .auto-tile.t1 .ic {
            background: var(--pri-light);
            color: var(--pri);
        }

        .mdk .auto-tile.t2 .ic {
            background: var(--info-light);
            color: var(--info);
        }

        .mdk .auto-tile.t3 .ic {
            background: var(--success-light);
            color: var(--success);
        }

        .mdk .auto-tile small {
            display: block;
            font-size: .72rem;
            color: var(--gray-500);
            font-weight: 600;
        }

        .mdk .auto-tile strong {
            font-size: 1rem;
            color: var(--gray-900);
        }

        .mdk .doc-summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.25rem;
            flex-wrap: wrap;
            padding: 1rem 1.2rem;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--pri-light) 0%, #f5f3ff 100%);
            border: 1px solid #e0e7ff;
            margin-bottom: 1.1rem;
        }

        .mdk .doc-summary-main {
            flex: 1;
            min-width: 220px;
        }

        .mdk .doc-summary-title {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: .5rem;
            font-weight: 700;
            font-size: .9rem;
            color: var(--gray-900);
            margin-bottom: .45rem;
        }

        .mdk .doc-summary-title span {
            font-size: .8rem;
            font-weight: 600;
            color: var(--pri);
        }

        .mdk .doc-summary .progress {
            height: 8px;
            background: #fff;
            border-radius: 4px;
        }

        .mdk .doc-summary .progress-bar {
            background: linear-gradient(90deg, #6366f1, var(--pri));
            border-radius: 4px;
            transition: width .4s ease;
        }

        .mdk .doc-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: .85rem;
            transition: opacity .15s;
        }

        .mdk .doc-grid.busy {
            opacity: .5;
            pointer-events: none;
        }

        .mdk .doc-card {
            display: flex;
            flex-direction: column;
            gap: .55rem;
            padding: 1rem 1.1rem;
            border: 1px solid var(--gray-200);
            border-radius: 10px;
            background: #fff;
            transition: box-shadow .15s, border-color .15s;
        }

        .mdk .doc-card.ada {
            border-left: 4px solid var(--success);
        }

        .mdk .doc-card.ada:hover {
            box-shadow: 0 4px 14px rgba(79, 70, 229, .12);
        }

        .mdk .doc-card.kosong {
            border: 2px dashed var(--gray-200);
            background: var(--gray-50);
            cursor: pointer;
        }

        .mdk .doc-card.kosong:hover,
        .mdk .doc-card.kosong:focus-visible {
            border-color: var(--pri);
            background: var(--pri-light);
            outline: none;
        }

        .mdk .doc-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
        }

        .mdk .doc-ico {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            background: var(--gray-100);
            color: var(--gray-500);
            font-size: 1rem;
        }

        .mdk .doc-card.ada .doc-ico {
            background: var(--success-light);
            color: var(--success);
        }

        .mdk .doc-card.kosong:hover .doc-ico {
            background: #fff;
            color: var(--pri);
        }

        .mdk .doc-card .nm {
            font-weight: 700;
            font-size: .92rem;
            color: var(--gray-900);
        }

        .mdk .doc-card .fl {
            display: flex;
            align-items: center;
            gap: .45rem;
            font-size: .78rem;
            color: var(--gray-500);
            min-width: 0;
        }

        .mdk .doc-card .fl span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .mdk .doc-card .fl .ext-pdf {
            color: var(--danger);
        }

        .mdk .doc-card .fl .ext-doc {
            color: var(--info);
        }

        .mdk .doc-card .fl .ext-img {
            color: var(--purple);
        }

        .mdk .doc-card .doc-act {
            display: flex;
            gap: .4rem;
            margin-top: .15rem;
        }

        .mdk .doc-card .doc-act .btn {
            padding: .3rem .65rem;
            font-size: .78rem;
        }

        .mdk .doc-card .dz {
            display: flex;
            align-items: center;
            gap: .6rem;
            color: var(--gray-500);
            font-size: .82rem;
            font-weight: 600;
        }

        .mdk .doc-card .dz i {
            color: var(--pri);
            font-size: 1.1rem;
        }

        .mdk .doc-card .dz small {
            display: block;
            font-weight: 500;
            color: var(--gray-400);
            font-size: .72rem;
        }

        .mdk .timeline {
            position: relative;
            margin: 0;
            padding: 0 0 0 1.5rem;
            list-style: none;
        }

        .mdk .timeline::before {
            content: "";
            position: absolute;
            left: 6px;
            top: 6px;
            bottom: 6px;
            width: 2px;
            background: var(--gray-200);
        }

        .mdk .timeline li {
            position: relative;
            padding: .8rem 1rem;
            margin-bottom: .7rem;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            background: #fff;
        }

        .mdk .timeline li::before {
            content: "";
            position: absolute;
            left: -1.5rem;
            top: 1.1rem;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #fff;
            border: 3px solid var(--pri);
        }

        .mdk .timeline .when {
            font-size: .75rem;
            color: var(--gray-500);
            font-weight: 600;
            margin-bottom: .25rem;
        }

        .mdk .timeline .amt {
            font-weight: 700;
            color: var(--gray-900);
        }

        .mdk .timeline .up {
            color: var(--success);
            font-size: .8rem;
            font-weight: 700;
        }

        .mdk .timeline .down {
            color: var(--danger);
            font-size: .8rem;
            font-weight: 700;
        }

        .mdk .thp-box {
            background: var(--pri-light);
            border: 1px solid #e0e7ff;
            border-radius: 10px;
            padding: .9rem 1.1rem;
            text-align: center;
        }

        .mdk .thp-label {
            font-size: .75rem;
            font-weight: 600;
            color: var(--gray-500);
            margin-bottom: .2rem;
        }

        .mdk .thp-value {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--pri);
        }

        .mdk .choice {
            display: flex;
            gap: .65rem;
            align-items: flex-start;
            padding: .8rem 1rem;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            cursor: pointer;
            transition: border-color .15s, background .15s;
        }

        .mdk .choice:hover,
        .mdk .choice:has(input:checked) {
            border-color: var(--pri);
            background: var(--pri-light);
        }

        .mdk .choice input {
            margin-top: .2rem;
        }

        .mdk .extend-box {
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: 10px;
            padding: 1rem;
        }

        .mdk .modal-header {
            background: linear-gradient(135deg, #f8faff, #eef4ff);
            border-bottom: 1px solid var(--gray-200);
            padding: 1.1rem 1.4rem;
        }

        .mdk .modal-header h2 {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: 1.05rem;
        }

        .mdk .modal-header h2 i {
            color: var(--pri);
        }

        .mdk .modal-footer {
            border-top: 1px solid var(--gray-200);
            background: var(--gray-50);
            padding: .9rem 1.4rem;
        }

        .modal-dialog-scrollable {
            max-height: calc(100vh - 1.5rem);
        }

        .modal-dialog-scrollable .modal-content {
            max-height: 100%;
            overflow: hidden;
        }

        .modal-dialog-scrollable .modal-body {
            overflow-y: auto;
            max-height: calc(100vh - 220px);
        }

        #modalReminder .modal-content,
        #modalGaji .modal-content {
            max-height: calc(100vh - 2rem);
            overflow-y: auto;
        }

        .mdk .table-responsive {
            -webkit-overflow-scrolling: touch;
        }

        @media (max-width: 991px) {
            .mdk .summary {
                grid-template-columns: 1fr 1fr;
            }

            .mdk .filters {
                grid-template-columns: 1fr 1fr;
            }

            .mdk .kv,
            .mdk .auto-box,
            .mdk .doc-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767.98px) {
            .mdk .summary {
                grid-template-columns: 1fr;
            }

            .mdk .filters {
                grid-template-columns: 1fr;
            }

            .mdk table.dataTable td,
            .mdk table.dataTable th {
                padding: .5rem .4rem;
                font-size: .8rem;
            }

            .mdk .person {
                min-width: 140px;
            }

            .mdk .reminder {
                grid-template-columns: 38px 1fr;
            }

            .mdk .reminder .btn {
                grid-column: 1 / -1;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            .mdk .summary.is-loading .n,
            .mdk .doc-summary .progress-bar {
                animation: none;
                transition: none;
            }
        }
    </style>
@endpush

@section('content_HR')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css"
        integrity="sha512-QeR2VH+lsBE5LSAe1Q5EnTBbe7XTBubt8dG93Y7gidSgdMCr8nVqKcfKAMyN96SV8KDbZVTDXChatu5G2KQGzg=="
        crossorigin="anonymous" referrerpolicy="no-referrer">


    <div class="container-fluid px-4 py-4">
        <div class="mdk">

            @php
                $tab = in_array(request('tab'), ['karyawan', 'reminder']) ? request('tab') : 'karyawan';
            @endphp

            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
                <div>
                    <h1 class="page-title"><i class="fa-solid fa-address-book me-2" style="color:var(--pri)"></i>Master Data
                        Karyawan</h1>
                    <p class="page-sub">Data pribadi, dokumen, masa kerja, dan reminder kontrak karyawan.</p>
                </div>
            </div>

            <div class="summary is-loading">
                <div class="panel stat">
                    <div class="stat-top">
                        <div>
                            <div class="l">Total karyawan aktif</div>
                            <div class="n" id="sumTotal">0</div>
                        </div>
                        <div class="stat-ico bg-pri"><i class="fa-solid fa-users"></i></div>
                    </div>
                    <div class="split-bar" aria-hidden="true">
                        <span id="barProbation" style="width:0"></span><span id="barKontrak" style="width:0"></span><span
                            id="barKartap" style="width:0"></span>
                    </div>
                    <div class="legend">
                        <span><i style="background:var(--warning)"></i>Probation <b id="sumProbation">0</b></span>
                        <span><i style="background:var(--info)"></i>Kontrak <b id="sumKontrak">0</b></span>
                        <span><i style="background:var(--success)"></i>Kartap <b id="sumKartap">0</b></span>
                    </div>
                </div>
                <div class="panel stat warn">
                    <div class="stat-top">
                        <div>
                            <div class="l">Reminder pending</div>
                            <div class="n" id="sumReminder">0</div>
                        </div>
                        <div class="stat-ico bg-warn"><i class="fa-regular fa-bell"></i></div>
                    </div>
                    <div class="foot"><b id="sumLewat">0</b> sudah lewat jatuh tempo</div>
                </div>
                <div class="panel stat bad">
                    <div class="stat-top">
                        <div>
                            <div class="l">Dokumen belum lengkap</div>
                            <div class="n" id="sumDokBelum">0</div>
                        </div>
                        <div class="stat-ico bg-bad"><i class="fa-solid fa-file-circle-exclamation"></i></div>
                    </div>
                    <div class="foot">dari <b id="sumDokTotal">0</b> karyawan</div>
                </div>
                <div class="panel stat purple">
                    <div class="stat-top">
                        <div>
                            <div class="l">Jatuh tempo 30 hari</div>
                            <div class="n" id="sumJatuh">0</div>
                        </div>
                        <div class="stat-ico bg-purple"><i class="fa-solid fa-calendar-xmark"></i></div>
                    </div>
                    <div class="foot">probation &amp; kontrak</div>
                </div>
            </div>

            <nav class="page-tabs" aria-label="Menu master karyawan">
                <a href="{{ url()->current() }}?tab=karyawan" class="{{ $tab === 'karyawan' ? 'active' : '' }}"
                    @if ($tab === 'karyawan') aria-current="page" @endif>
                    <i class="fa-solid fa-users"></i>Data karyawan
                </a>
                <a href="{{ url()->current() }}?tab=reminder" class="{{ $tab === 'reminder' ? 'active' : '' }}"
                    @if ($tab === 'reminder') aria-current="page" @endif>
                    <i class="fa-regular fa-bell"></i>Reminder <span class="count" id="tabReminderCount">0</span>
                </a>
            </nav>

            @if ($tab === 'karyawan')
                <div class="panel">
                    <div class="panel-head">
                        <h2><i class="fa-solid fa-table-list"></i>Daftar karyawan</h2>
                        <button type="button" class="btn btn-line btn-sm" id="btnResetFilter">
                            <i class="fa-solid fa-rotate-left me-1"></i>Reset filter
                        </button>
                    </div>
                    <div class="filters">
                        <div class="search-wrap">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="search" id="fSearch" class="form-control"
                                placeholder="Cari nama, NIP, atau NIK" aria-label="Cari karyawan">
                        </div>
                        <select id="fDivisi" class="form-select" aria-label="Filter divisi">
                            <option value="">Semua divisi</option>
                        </select>
                        <select id="fStatus" class="form-select" aria-label="Filter status karyawan">
                            <option value="">Semua status</option>
                            <option value="Probation">Probation</option>
                            <option value="Kontrak">Kontrak</option>
                            <option value="Kartap">Kartap</option>
                        </select>
                        <select id="fDokumen" class="form-select" aria-label="Filter dokumen">
                            <option value="">Semua dokumen</option>
                            <option value="lengkap">Lengkap</option>
                            <option value="belum">Belum lengkap</option>
                        </select>
                    </div>

                    <div class="panel-body">
                        <div class="alert alert-danger d-none" id="tabelError" role="alert"></div>
                        <div class="table-responsive">
                            <table id="tabelKaryawan" class="table table-hover align-middle w-100">
                                <thead>
                                    <tr>
                                        <th>Karyawan</th>
                                        <th>Divisi</th>
                                        <th>Jabatan</th>
                                        <th>Status</th>
                                        <th>Masa kerja</th>
                                        <th>Masa kontrak</th>
                                        <th>Basic salary</th>
                                        <th>Kelengkapan dokumen</th>
                                        <th>Reminder</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="panel">
                    <div class="panel-head">
                        <div>
                            <h2><i class="fa-regular fa-bell"></i>Daftar reminder</h2>
                            <div class="sub mt-1">Masa probation, masa kontrak, dan perubahan gaji yang perlu
                                ditindaklanjuti</div>
                        </div>
                        <form method="GET" action="{{ url()->current() }}" class="reminder-filter"
                            id="formFilterReminder">
                            <input type="hidden" name="tab" value="reminder">
                            <select name="jenis" class="form-select form-select-sm" style="width:auto"
                                aria-label="Filter jenis reminder">
                                <option value="">Semua jenis</option>
                                <option value="probation" @selected(request('jenis') === 'probation')>Masa probation</option>
                                <option value="kontrak" @selected(request('jenis') === 'kontrak')>Masa kontrak</option>
                                <option value="gaji" @selected(request('jenis') === 'gaji')>Kenaikan / perubahan gaji</option>
                            </select>
                            <select name="status" class="form-select form-select-sm" style="width:auto"
                                aria-label="Filter status reminder">
                                <option value="">Semua status</option>
                                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                                <option value="kartap" @selected(request('status') === 'kartap')>Masuk Kartap</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-brand"><i
                                    class="fa-solid fa-filter me-1"></i>Terapkan</button>
                        </form>
                    </div>
                    <div id="reminderList" class="reminder-list">
                        <div class="empty loading"><i class="fa-solid fa-spinner fa-spin me-2"></i>Memuat reminder...
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade mdk mdk-modal" id="modalDetail" tabindex="-1" aria-labelledby="dNama" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="profile-head">
                    <div class="avatar" id="dAvatar"></div>
                    <div class="flex-grow-1">
                        <h2 id="dNama"></h2>
                        <div class="sub" id="dSub"></div>
                        <div class="mt-2 d-flex gap-2 flex-wrap" id="dTags"></div>
                    </div>
                    <div class="d-flex gap-2 align-self-start">
                        <button type="button" class="btn btn-sm btn-line btn-edit" id="dEdit"><i
                                class="fa-solid fa-pen me-2"></i>Ubah</button>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                </div>

                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab"
                            data-bs-target="#tabPribadi" type="button" role="tab"><i
                                class="fa-regular fa-id-card"></i>Data pribadi</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabKerja"
                            type="button" role="tab"><i class="fa-solid fa-briefcase"></i>Kepegawaian</button>
                    </li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabDokumen"
                            type="button" role="tab"><i class="fa-regular fa-folder-open"></i>Dokumen</button>
                    </li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabGaji"
                            type="button" role="tab"><i class="fa-solid fa-money-bill-trend-up"></i>Riwayat
                            gaji</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabReminder"
                            type="button" role="tab"><i class="fa-regular fa-bell"></i>Reminder</button></li>
                </ul>

                <div class="modal-body p-4">
                    <div class="alert alert-danger d-none mdk-error" role="alert"></div>
                    <div class="tab-content">

                        <div class="tab-pane fade show active" id="tabPribadi" role="tabpanel">
                            <div class="section-title"><i class="fa-solid fa-user"></i>Biodata</div>
                            <dl class="kv">
                                <div>
                                    <dt>Nama lengkap</dt>
                                    <dd data-f="nama_lengkap"></dd>
                                </div>
                                <div>
                                    <dt>NIK</dt>
                                    <dd data-f="nik"></dd>
                                </div>
                                <div>
                                    <dt>Tanggal lahir</dt>
                                    <dd data-f="tanggal_lahir"></dd>
                                </div>
                                <div>
                                    <dt>Pendidikan terakhir</dt>
                                    <dd data-f="pendidikan_terakhir"></dd>
                                </div>
                                <div>
                                    <dt>Kejuruan</dt>
                                    <dd data-f="kejuruan"></dd>
                                </div>
                                <div>
                                    <dt>No HP</dt>
                                    <dd data-f="no_hp"></dd>
                                </div>
                            </dl>
                            <div class="section-title"><i class="fa-solid fa-location-dot"></i>Alamat</div>
                            <dl class="kv">
                                <div>
                                    <dt>Alamat KTP</dt>
                                    <dd data-f="alamat_ktp"></dd>
                                </div>
                                <div>
                                    <dt>Alamat domisili</dt>
                                    <dd data-f="alamat_domisili"></dd>
                                </div>
                            </dl>
                            <div class="section-title"><i class="fa-solid fa-building-columns"></i>Rekening</div>
                            <dl class="kv">
                                <div>
                                    <dt>Maybank</dt>
                                    <dd data-f="rekening_maybank"></dd>
                                </div>
                                <div>
                                    <dt>BCA</dt>
                                    <dd data-f="rekening_bca"></dd>
                                </div>
                            </dl>
                            <div class="section-title"><i class="fa-regular fa-note-sticky"></i>Keterangan</div>
                            <p class="note-box" data-f="keterangan"></p>
                        </div>

                        <div class="tab-pane fade" id="tabKerja" role="tabpanel">
                            <div class="section-title"><i class="fa-solid fa-calculator"></i>Dihitung otomatis
                            </div>
                            <div class="auto-box">
                                <div class="auto-tile t1">
                                    <span class="ic"><i class="fa-solid fa-hourglass-half"></i></span>
                                    <div><small>Masa kerja (sejak probation)</small><strong data-f="masa_kerja"></strong>
                                    </div>
                                </div>
                                <div class="auto-tile t2">
                                    <span class="ic"><i class="fa-solid fa-file-signature"></i></span>
                                    <div><small>Masa kontrak (sejak awal kontrak)</small><strong
                                            data-f="masa_kontrak"></strong></div>
                                </div>
                                <div class="auto-tile t3">
                                    <span class="ic"><i class="fa-solid fa-wallet"></i></span>
                                    <div><small>Basic salary (gaji pertama)</small><strong data-f="basic_salary"></strong>
                                    </div>
                                </div>
                            </div>
                            <div class="section-title"><i class="fa-regular fa-calendar"></i>Periode</div>
                            <dl class="kv">
                                <div>
                                    <dt>Awal probation</dt>
                                    <dd data-f="awal_probation"></dd>
                                </div>
                                <div>
                                    <dt>Akhir probation</dt>
                                    <dd data-f="akhir_probation"></dd>
                                </div>
                                <div>
                                    <dt>Awal kontrak</dt>
                                    <dd data-f="awal_kontrak"></dd>
                                </div>
                                <div>
                                    <dt>Akhir kontrak</dt>
                                    <dd data-f="akhir_kontrak"></dd>
                                </div>
                                <div>
                                    <dt>Awal karyawan tetap</dt>
                                    <dd data-f="awal_tetap"></dd>
                                </div>
                                <div>
                                    <dt>Akhir karyawan tetap</dt>
                                    <dd data-f="akhir_tetap"></dd>
                                </div>
                            </dl>
                        </div>

                        <div class="tab-pane fade" id="tabDokumen" role="tabpanel">
                            <div class="doc-summary">
                                <div class="doc-summary-main">
                                    <div class="doc-summary-title">Kelengkapan dokumen <span id="dokRingkas">0 dari
                                            0</span></div>
                                    <div class="progress" role="progressbar" aria-label="Kelengkapan dokumen">
                                        <div class="progress-bar" id="dokBar" style="width:0%"></div>
                                    </div>
                                </div>
                                <a href="#" id="btnDownloadSemua" class="btn btn-sm btn-brand d-none">
                                    <i class="fa-solid fa-file-zipper me-2"></i>Unduh semua (ZIP)
                                </a>
                            </div>

                            <input type="file" id="dokInput" class="d-none"
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <div class="doc-grid" id="dDokumen"></div>
                        </div>

                        <div class="tab-pane fade" id="tabGaji" role="tabpanel">
                            <div class="thp-box mb-2">
                                <div class="thp-label">Take Home Pay</div>
                                <div class="thp-value" id="gThp">-</div>
                            </div>

                            <div class="section-title"><i class="fa-solid fa-pen"></i>Ubah Data Gaji</div>
                            <form id="formGaji">
                                @csrf
                                <input type="hidden" name="id" id="g_id">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="gajiBaru">Gaji pokok</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input id="gajiBaru" name="gaji" type="number" min="0"
                                                step="1" class="form-control" placeholder="Nominal gaji pokok"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="gajiTunjangan">Tunjangan jabatan</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input id="gajiTunjangan" name="tunjangan_jabatan" type="number"
                                                min="0" step="1" class="form-control"
                                                placeholder="Nominal tunjangan">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="gajiBulan">Bulan</label>
                                        <select id="gajiBulan" name="bulan" class="form-select" required>
                                            <option value="1">Januari</option>
                                            <option value="2">Februari</option>
                                            <option value="3">Maret</option>
                                            <option value="4">April</option>
                                            <option value="5">Mei</option>
                                            <option value="6">Juni</option>
                                            <option value="7">Juli</option>
                                            <option value="8">Agustus</option>
                                            <option value="9">September</option>
                                            <option value="10">Oktober</option>
                                            <option value="11">November</option>
                                            <option value="12">Desember</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="gajiTahun">Tahun</label>
                                        <input id="gajiTahun" name="tahun" type="number" min="2000"
                                            max="2099" class="form-control" placeholder="Contoh: 2026" required>
                                    </div>
                                </div>
                                <div class="sub mt-2 mb-3">Nominal dalam Rupiah, tanpa titik atau koma.</div>
                                <button type="submit" class="btn btn-brand w-100">Simpan perubahan</button>
                            </form>

                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-4 mb-2">
                                <div class="section-title m-0"><i class="fa-solid fa-clock-rotate-left"></i>Riwayat gaji
                                </div>
                                <select id="fLogTahun" class="form-select form-select-sm" style="width:130px"
                                    aria-label="Filter tahun riwayat gaji">
                                    <option value="">Semua Tahun</option>
                                </select>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-center">No</th>
                                            <th class="text-center">Bulan</th>
                                            <th class="text-center">Tahun</th>
                                            <th>Gaji</th>
                                            <th>Tunjangan jabatan</th>
                                            <th class="text-center">Dicatat</th>
                                        </tr>
                                    </thead>
                                    <tbody id="logGajiBody">
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Tidak ada riwayat gaji.
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot id="logGajiFoot" style="display:none">
                                        <tr>
                                            <th class="text-end" colspan="3">Total</th>
                                            <th id="logTotalGaji">-</th>
                                            <th id="logTotalTunjangan">-</th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="sub mt-2">Basic salary diambil dari gaji pada periode paling awal di riwayat
                                ini.</div>
                        </div>

                        <div class="tab-pane fade" id="tabReminder" role="tabpanel">
                            <div class="reminder-list p-0" id="dReminder"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade mdk" id="modalReminder" tabindex="-1" aria-labelledby="reminderTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:12px">
                <form id="formReminder">
                    @csrf
                    <input type="hidden" name="id" id="r_id">
                    <div class="modal-header">
                        <h2 id="reminderTitle"><i class="fa-regular fa-bell"></i>Tindak lanjut reminder</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger d-none mdk-error" role="alert"></div>
                        <div class="mb-3">
                            <div class="fw-bold" id="rNama"></div>
                            <div class="sub" id="rJudul"></div>
                        </div>

                        <label class="form-label">Status</label>
                        <div class="d-grid gap-2 mb-3">
                            <label class="choice">
                                <input class="form-check-input" type="radio" name="status" value="done">
                                <span><strong>Sudah update</strong><br><span class="sub">Data kontrak dan gaji sudah
                                        diperbarui.</span></span>
                            </label>
                            <label class="choice">
                                <input class="form-check-input" type="radio" name="status" value="pending" checked>
                                <span><strong>Pending</strong><br><span class="sub">Belum diputuskan. Alasan wajib
                                        diisi.</span></span>
                            </label>
                            <label class="choice">
                                <input class="form-check-input" type="radio" name="status" value="kartap">
                                <span><strong>Masuk Kartap</strong><br><span class="sub">Diangkat menjadi karyawan
                                        tetap mulai hari ini.</span></span>
                            </label>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="alasanReminder">Alasan <span
                                    class="text-danger">*</span></label>
                            <textarea id="alasanReminder" name="alasan" class="form-control" rows="2"
                                placeholder="Contoh: menunggu evaluasi kinerja" required></textarea>
                        </div>

                        <div class="extend-box">
                            <div class="fw-bold mb-2">Perpanjangan probation / kontrak</div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label" for="jenisPerpanjang">Jenis</label>
                                    <select id="jenisPerpanjang" name="jenis_perpanjang" class="form-select">
                                        <option value="">Tidak diperpanjang</option>
                                        <option value="probation">Perpanjang probation</option>
                                        <option value="kontrak">Perpanjang kontrak</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label" for="lamaPerpanjang">Lama (bulan)</label>
                                    <input id="lamaPerpanjang" name="lama_perpanjang" type="number" min="1"
                                        class="form-control" placeholder="3">
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="alasanPerpanjang">Alasan perpanjangan</label>
                                    <textarea id="alasanPerpanjang" name="alasan_perpanjang" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-line" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-brand">Simpan status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade mdk" id="modalGaji" tabindex="-1" aria-labelledby="gajiTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:12px">
                <form id="formGaji">
                    @csrf
                    <input type="hidden" name="id" id="g_id">
                    <div class="modal-header">
                        <h2 id="gajiTitle"><i class="fa-solid fa-money-bill-trend-up"></i>Catat perubahan gaji
                        </h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger d-none mdk-error" role="alert"></div>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label" for="tglGaji">Tanggal</label>
                                <input id="tglGaji" name="tanggal" type="date" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label" for="gajiLama">Gaji terakhir</label>
                                <input id="gajiLama" type="text" class="form-control" readonly>
                            </div>
                            <div class="col-6">
                                <label class="form-label" for="gajiBaru">Gaji baru</label>
                                <input id="gajiBaru" name="gaji" type="number" min="0" class="form-control"
                                    placeholder="0" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label" for="gajiTunjangan">Tunjangan jabatan</label>
                                <input id="gajiTunjangan" name="tunjangan_jabatan" type="number" min="0"
                                    class="form-control" placeholder="Sama seperti sebelumnya">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-line" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-brand">Simpan perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade mdk" id="modalForm" tabindex="-1" aria-labelledby="formTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:12px">
                <form id="formKaryawan" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" id="f_id">
                    <div class="modal-header">
                        <h2 id="formTitle"><i class="fa-solid fa-user-pen"></i><span id="formTitleText">Tambah
                                karyawan</span></h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-danger d-none mdk-error" role="alert"></div>

                        <div class="section-title"><i class="fa-regular fa-id-card"></i>Identitas</div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label" for="f_foto">Pas foto</label>
                                <input id="f_foto" name="foto" type="file" accept="image/*"
                                    class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="f_nip">NIP</label>
                                <input id="f_nip" name="nip" type="text" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="f_nama">Nama lengkap</label>
                                <input id="f_nama" name="nama_lengkap" type="text" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="f_nik">NIK</label>
                                <input id="f_nik" name="nik" type="text" inputmode="numeric" maxlength="16"
                                    class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="f_lahir">Tanggal lahir</label>
                                <input id="f_lahir" name="tanggal_lahir" type="date" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="f_hp">No HP</label>
                                <input id="f_hp" name="whatsapp" type="tel" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="f_pend">Pendidikan terakhir</label>
                                <input id="f_pend" name="pendidikan_terakhir" type="text" list="listPendidikan"
                                    class="form-control">
                                <datalist id="listPendidikan">
                                    <option value="SD"></option>
                                    <option value="SMP"></option>
                                    <option value="SMA/SMK"></option>
                                    <option value="D3"></option>
                                    <option value="S1"></option>
                                    <option value="S2"></option>
                                </datalist>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="f_kej">Kejuruan</label>
                                <input id="f_kej" name="kejuruan" type="text" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="f_ktp">Alamat KTP</label>
                                <textarea id="f_ktp" name="alamat_ktp" rows="2" class="form-control"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="f_dom">Alamat domisili</label>
                                <textarea id="f_dom" name="alamat_lengkap" rows="2" class="form-control"></textarea>
                            </div>
                        </div>

                        <div class="section-title"><i class="fa-solid fa-briefcase"></i>Kepegawaian</div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label" for="f_div">Divisi</label>
                                <input id="f_div" name="divisi" type="text" list="listDivisi"
                                    class="form-control">
                                <datalist id="listDivisi"></datalist>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="f_jab">Jabatan</label>
                                <input id="f_jab" name="jabatan" type="text" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="f_status">Status karyawan</label>
                                <select id="f_status" name="status_karyawan" class="form-select">
                                    <option value="Probation">Probation</option>
                                    <option value="Kontrak">Kontrak</option>
                                    <option value="Kartap">Kartap</option>
                                </select>
                            </div>
                            <div class="col-md-3"><label class="form-label" for="f_ap">Awal
                                    probation</label><input id="f_ap" name="awal_probation" type="date"
                                    class="form-control"></div>
                            <div class="col-md-3"><label class="form-label" for="f_xp">Akhir
                                    probation</label><input id="f_xp" name="akhir_probation" type="date"
                                    class="form-control"></div>
                            <div class="col-md-3"><label class="form-label" for="f_ak">Awal kontrak</label><input
                                    id="f_ak" name="awal_kontrak" type="date" class="form-control"></div>
                            <div class="col-md-3"><label class="form-label" for="f_xk">Akhir
                                    kontrak</label><input id="f_xk" name="akhir_kontrak" type="date"
                                    class="form-control"></div>
                            <div class="col-md-3"><label class="form-label" for="f_at">Awal karyawan
                                    tetap</label><input id="f_at" name="awal_tetap" type="date"
                                    class="form-control"></div>
                            <div class="col-md-3" id="wrapGajiAwal">
                                <label class="form-label" for="f_gaji">Gaji pertama (basic salary)</label>
                                <input id="f_gaji" name="gaji_awal" type="number" min="0"
                                    class="form-control">
                            </div>
                            <div class="col-12">
                                <div class="note-box"><i class="fa-solid fa-circle-info me-1"
                                        style="color:var(--pri)"></i>Masa kerja dan masa kontrak dihitung otomatis.
                                    Basic salary diambil dari gaji pertama di log gaji.</div>
                            </div>
                        </div>

                        <div class="section-title"><i class="fa-solid fa-building-columns"></i>Rekening dan
                            keterangan</div>
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label" for="f_mb">Rekening
                                    Maybank</label><input id="f_mb" name="rekening_maybank" type="text"
                                    class="form-control"></div>
                            <div class="col-md-6"><label class="form-label" for="f_bca">Rekening BCA</label><input
                                    id="f_bca" name="rekening_bca" type="text" class="form-control"></div>
                            <div class="col-12"><label class="form-label" for="f_ket">Keterangan</label>
                                <textarea id="f_ket" name="keterangan" rows="2" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-line" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-brand">Simpan perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        var R = {
            data: "{{ route('HR.master_karyawan.data') }}",
            detail: "{{ route('HR.master_karyawan.detail', ['id' => '__ID__']) }}",
            reminder: "{{ route('HR.master_karyawan.reminder') }}",
            store: "{{ route('HR.master_karyawan.store') }}",
            update: "{{ route('HR.master_karyawan.update') }}",
            reminderUpdate: "{{ route('HR.master_karyawan.reminder_update') }}",
            gajiStore: "{{ route('HR.master_karyawan.gaji_store') }}",
            downloadDokumen: "{{ route('HR.master_karyawan.download_dokumen', ['id' => '__ID__']) }}"
        };

        var filterReminder = @json(request()->only('jenis', 'status'));
        var tabel = null;
        var idAktif = null;
        var detailTerakhir = null;
        var kembaliKeDetail = false;
        var kunciUpload = null;
        var PETA_STATUS = {
            'Probation': 'tag-probation',
            'Kontrak': 'tag-kontrak',
            'Kartap': 'tag-kartap'
        };

        var BULAN_NAMA = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus',
            'September', 'Oktober', 'November', 'Desember'
        ];
        var logGajiAktif = [];

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        });

        $.fn.dataTable.ext.errMode = 'none';

        function esc(s) {
            return String(s == null ? '' : s).replace(/[&<>"']/g, function(c) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [c];
            });
        }

        function notifGagal(pesan) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: pesan || 'Terjadi kesalahan, coba lagi.',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        }

        function setLoading(btn, loading, teks) {
            var $btn = $(btn);
            if (!$btn.length) return;
            if (loading) {
                $btn.data('original-html', $btn.html());
                $btn.prop('disabled', true).addClass('disabled')
                    .html('<i class="fa-solid fa-spinner fa-spin me-2"></i>' + (teks === undefined ? 'Memproses...' :
                        teks));
            } else {
                $btn.prop('disabled', false).removeClass('disabled')
                    .html($btn.data('original-html') || $btn.html());
            }
        }

        function statusTag(s) {
            return '<span class="tag ' + (PETA_STATUS[s] || 'tag-muted') + '">' + esc(s || '-') + '</span>';
        }

        function avatar(b) {
            return b.foto_url ?
                '<img class="avatar" src="' + esc(b.foto_url) + '" alt="" loading="lazy">' :
                '<span class="avatar">' + esc(b.initials || '?') + '</span>';
        }

        function dokIcon(label) {
            var l = String(label || '').toLowerCase();
            var peta = [
                [/kontrak/, 'fa-file-signature'],
                [/probation/, 'fa-hourglass-half'],
                [/gaji|skema/, 'fa-money-bill-wave'],
                [/ijazah/, 'fa-graduation-cap'],
                [/\bktp\b/, 'fa-id-card'],
                [/\bkk\b/, 'fa-people-roof'],
                [/npwp/, 'fa-file-invoice-dollar'],
                [/\bsim\b/, 'fa-car'],
                [/\bcv\b/, 'fa-file-lines']
            ];
            for (var i = 0; i < peta.length; i++) {
                if (peta[i][0].test(l)) return peta[i][1];
            }
            return 'fa-file';
        }

        function extInfo(nama) {
            var m = /\.([a-z0-9]+)$/i.exec(String(nama || ''));
            var e = m ? m[1].toLowerCase() : '';
            if (e === 'pdf') return {
                icon: 'fa-file-pdf',
                cls: 'ext-pdf'
            };
            if (e === 'doc' || e === 'docx') return {
                icon: 'fa-file-word',
                cls: 'ext-doc'
            };
            if (e === 'jpg' || e === 'jpeg' || e === 'png') return {
                icon: 'fa-file-image',
                cls: 'ext-img'
            };
            return {
                icon: 'fa-file',
                cls: ''
            };
        }

        function kartuDokumen(x) {
            var ico = dokIcon(x.label);

            if (!x.file) {
                return '<div class="doc-card kosong btn-upload" data-key="' + esc(x.key) +
                    '" role="button" tabindex="0" aria-label="Unggah ' + esc(x.label) + '">' +
                    '<div class="doc-card-top"><div class="doc-ico"><i class="fa-solid ' + ico + '"></i></div>' +
                    '<span class="tag tag-muted">Belum diunggah</span></div>' +
                    '<div class="nm">' + esc(x.label) + '</div>' +
                    '<div class="dz"><i class="fa-solid fa-cloud-arrow-up"></i><div>Klik untuk unggah' +
                    '<small>PDF, DOC, JPG, PNG</small></div></div></div>';
            }

            var ext = extInfo(x.file);

            return '<div class="doc-card ada">' +
                '<div class="doc-card-top"><div class="doc-ico"><i class="fa-solid ' + ico + '"></i></div>' +
                '<span class="tag tag-done"><i class="fa-solid fa-circle-check"></i>Tersedia</span></div>' +
                '<div class="nm">' + esc(x.label) + '</div>' +
                '<div class="fl"><i class="fa-solid ' + ext.icon + ' ' + ext.cls + '"></i><span title="' + esc(x.file) +
                '">' + esc(x.file) + '</span></div>' +
                '<div class="doc-act">' +
                '<a class="btn btn-sm btn-line" href="' + esc(x.url) +
                '" target="_blank" rel="noopener"><i class="fa-solid fa-eye me-1"></i>Lihat</a>' +
                '<a class="btn btn-sm btn-line" href="' + esc(x.url) +
                '" download><i class="fa-solid fa-download me-1"></i>Unduh</a>' +
                '<button type="button" class="btn btn-sm btn-line btn-upload" data-key="' + esc(x.key) +
                '"><i class="fa-solid fa-arrow-up-from-bracket me-1"></i>Ganti</button>' +
                '</div></div>';
        }

        function modalInstance(id) {
            return bootstrap.Modal.getOrCreateInstance(document.getElementById(id));
        }

        function bukaModal(id) {
            var terbuka = $('.modal.show').not('#' + id)[0];
            if (terbuka) {
                terbuka.addEventListener('hidden.bs.modal', function() {
                    modalInstance(id).show();
                }, {
                    once: true
                });
                bootstrap.Modal.getOrCreateInstance(terbuka).hide();
            } else {
                modalInstance(id).show();
            }
        }

        function tutupModal(id) {
            var el = document.getElementById(id);
            var m = bootstrap.Modal.getInstance(el);
            if (m) m.hide();
        }

        function bersihkanError(modal) {
            $(modal + ' .mdk-error').addClass('d-none').text('');
        }

        function tampilError(modal, xhr) {
            var j = xhr.responseJSON || {};
            var pesan = 'Terjadi kesalahan, coba lagi.';
            if (j.errors) {
                var semua = $.map(j.errors, function(v) {
                    return v;
                });
                pesan = semua[0];
            } else if (j.message) {
                pesan = j.message;
            }
            $(modal + ' .mdk-error').removeClass('d-none').text(pesan);
        }

        function kirim(form, url, modal, sukses) {
            bersihkanError(modal);

            var fd = new FormData(form);
            var $btn = $(form).find('button[type="submit"]');
            var $semua = $(form).closest('.modal').find('input, select, textarea, button').not(':disabled');

            setLoading($btn, true, 'Menyimpan...');
            $semua.prop('disabled', true);

            function selesai() {
                $semua.prop('disabled', false);
                setLoading($btn, false);
            }

            $.ajax({
                url: url,
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false
            }).done(function(res) {
                selesai();
                sukses(res);
            }).fail(function(xhr) {
                selesai();
                tampilError(modal, xhr);
            });
        }

        function muatRingkasan() {
            $('.summary').addClass('is-loading');

            return $.getJSON(R.data, {
                summary_only: 1
            }).done(function(res) {
                var s = res.summary;
                var total = s.total || 1;
                $('#sumTotal').text(s.total);
                $('#sumProbation').text(s.probation);
                $('#sumKontrak').text(s.kontrak);
                $('#sumKartap').text(s.kartap);
                $('#barProbation').css('width', (s.probation / total * 100) + '%');
                $('#barKontrak').css('width', (s.kontrak / total * 100) + '%');
                $('#barKartap').css('width', (s.kartap / total * 100) + '%');
                $('#sumReminder').text(s.reminder_pending);
                $('#sumLewat').text(s.lewat_tempo);
                $('#sumDokBelum').text(s.dokumen_belum_lengkap);
                $('#sumDokTotal').text(s.total);
                $('#sumJatuh').text(s.jatuh_tempo_30_hari);
                $('#tabReminderCount').text(s.reminder_pending);

                var opsi = $.map(s.divisi, function(d) {
                    return '<option value="' + esc(d) + '">' + esc(d) + '</option>';
                }).join('');
                var dipilih = $('#fDivisi').val();
                $('#fDivisi').html('<option value="">Semua divisi</option>' + opsi).val(dipilih);
                $('#listDivisi').html(opsi);
            }).fail(function() {
                notifGagal('Ringkasan gagal dimuat.');
            }).always(function() {
                $('.summary').removeClass('is-loading');
            });
        }

        function itemReminder(r) {
            var kelas = r.level === 'late' ? ' late' : (r.level === 'done' ? ' done' : '');
            var ikon = r.status === 'kartap' ? 'fa-circle-check' : (r.level === 'late' ? 'fa-triangle-exclamation' :
                'fa-hourglass-half');
            var tag = r.status === 'kartap' ?
                '<span class="tag tag-kartap ms-2">Masuk Kartap</span>' :
                '<span class="tag ' + (r.level === 'late' ? 'tag-late' : 'tag-pending') + ' ms-2">' + esc(r.label) +
                '</span>';
            var aksi = r.status === 'kartap' ?
                '<span class="tag tag-done"><i class="fa-solid fa-check"></i>Sudah update</span>' :
                '<button type="button" class="btn btn-sm btn-brand btn-reminder" data-id="' + r.id + '" data-nama="' + esc(r
                    .nama) + '" data-judul="' + esc(r.judul) + '">Tindak lanjut</button>';
            return '<div class="reminder' + kelas + '"><span class="r-ico"><i class="fa-solid ' + ikon +
                '"></i></span><div><div class="who">' + esc(r.nama) + tag +
                '</div><div class="what">' + esc(r.judul) + ' &middot; ' + esc(r.jabatan || '-') + ', ' + esc(r.divisi ||
                    '-') +
                '</div></div>' + aksi + '</div>';
        }

        function muatReminder() {
            var $list = $('#reminderList');
            $list.html(
                '<div class="empty loading"><i class="fa-solid fa-spinner fa-spin me-2"></i>Memuat reminder...</div>');

            return $.getJSON(R.reminder, filterReminder).done(function(res) {
                $list.html(res.data.length ? $.map(res.data, itemReminder).join('') :
                    '<div class="empty"><i class="fa-regular fa-bell-slash big"></i>Tidak ada reminder.</div>');
            }).fail(function() {
                $list.html('<div class="empty text-danger">Reminder gagal dimuat.</div>');
            });
        }

        function muatDetail(id) {
            idAktif = id;
            return $.getJSON(R.detail.replace('__ID__', id))
                .done(isiDetail)
                .fail(function() {
                    notifGagal('Data karyawan gagal dimuat.');
                });
        }

        function rp(n) {
            return 'Rp ' + (parseInt(n) || 0).toLocaleString('id-ID');
        }

        function renderLogGaji(log) {
            var $body = $('#logGajiBody');
            var $foot = $('#logGajiFoot');

            if (!log.length) {
                $body.html('<tr><td colspan="6" class="text-center text-muted">Tidak ada riwayat gaji.</td></tr>');
                $foot.hide();
                return;
            }

            var urut = log.slice().sort(function(a, b) {
                return b.tahun !== a.tahun ? b.tahun - a.tahun : b.bulan - a.bulan;
            });

            var totalGaji = 0;
            var totalTunjangan = 0;

            $body.html($.map(urut, function(r, i) {
                totalGaji += r.gaji;
                totalTunjangan += r.tunjangan_jabatan;

                var tgl = r.created_at ?
                    new Date(r.created_at).toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    }) : '-';

                return '<tr><td class="text-center">' + (i + 1) + '</td>' +
                    '<td class="text-center">' + esc(BULAN_NAMA[r.bulan] || r.bulan) + '</td>' +
                    '<td class="text-center">' + esc(r.tahun) + '</td>' +
                    '<td>' + rp(r.gaji) + '</td>' +
                    '<td>' + rp(r.tunjangan_jabatan) + '</td>' +
                    '<td class="text-center text-muted small">' + esc(tgl) + '</td></tr>';
            }).join(''));

            $('#logTotalGaji').text(rp(totalGaji));
            $('#logTotalTunjangan').text(rp(totalTunjangan));
            $foot.show();
        }

        function terapkanFilterLog() {
            var tahun = $('#fLogTahun').val();

            renderLogGaji($.grep(logGajiAktif, function(r) {
                return !tahun || String(r.tahun) === String(tahun);
            }));
        }

        function isiFilterTahunLog(log) {
            var dipilih = $('#fLogTahun').val();
            var tahunList = [];

            $.each(log, function(i, r) {
                var t = String(r.tahun);
                if (tahunList.indexOf(t) === -1) tahunList.push(t);
            });

            tahunList.sort(function(a, b) {
                return b - a;
            });

            $('#fLogTahun').html('<option value="">Semua Tahun</option>' + $.map(tahunList, function(t) {
                return '<option value="' + esc(t) + '">' + esc(t) + '</option>';
            }).join(''));

            if (tahunList.indexOf(dipilih) !== -1) $('#fLogTahun').val(dipilih);
        }

        function isiDetail(d) {
            var b = d.baris;
            detailTerakhir = d;

            $('#dAvatar').html(b.foto_url ?
                '<img src="' + esc(b.foto_url) +
                '" alt="" class="w-100 h-100 rounded-circle" style="object-fit:cover">' :
                esc(b.initials || '?'));
            $('#dNama').text(b.nama_lengkap);
            $('#dSub').text('NIP ' + (b.nip || '-') + ' \u00b7 ' + (b.jabatan || '-') + ' \u00b7 ' + (b.divisi || '-'));
            $('#dTags').html(statusTag(b.status_karyawan) + '<span class="tag ' +
                (b.dokumen.terisi === b.dokumen.total ? 'tag-done' : 'tag-pending') + '">Dokumen ' +
                b.dokumen.terisi + ' dari ' + b.dokumen.total + '</span>');
            $('#dEdit').attr('data-id', b.id);

            $('#modalDetail [data-f]').each(function() {
                $(this).text(d.fields[$(this).attr('data-f')] || '-');
            });

            var persenDok = b.dokumen.total ? Math.round(b.dokumen.terisi / b.dokumen.total * 100) : 0;
            $('#dokRingkas').text(b.dokumen.terisi + ' dari ' + b.dokumen.total + ' dokumen');
            $('#dokBar').css('width', persenDok + '%');

            $('#dDokumen').html($.map(d.dokumen, kartuDokumen).join(''));

            var adaDokumen = $.grep(d.dokumen, function(x) {
                return !!x.file;
            }).length > 0;
            $('#btnDownloadSemua').toggleClass('d-none', !adaDokumen).attr('href', R.downloadDokumen.replace('__ID__', b
                .id));

            var logs = $.grep($.map(String(d.fields.keterangan || '').split('\n'), function(l) {
                return $.trim(l);
            }), function(l) {
                return l.charAt(0) === '[';
            });

            var htmlReminder = d.reminder.length ? $.map(d.reminder, itemReminder).join('') :
                '<div class="empty"><i class="fa-regular fa-bell-slash big"></i>Tidak ada reminder aktif.</div>';

            if (logs.length) {
                htmlReminder += '<div class="section-title mt-3 mb-0"><i class="fa-solid fa-clock-rotate-left"></i>' +
                    'Riwayat tindakan reminder</div>' +
                    $.map(logs, function(l) {
                        return '<div class="reminder done"><span class="r-ico"><i class="fa-solid fa-check"></i></span>' +
                            '<div class="what">' + esc(l) + '</div><span></span></div>';
                    }).join('');
            }

            $('#dReminder').html(htmlReminder);

            var sekarang = new Date();

            $('#g_id').val(b.id);
            $('#gThp').text(rp(d.gaji_sekarang.gaji + d.gaji_sekarang.tunjangan));
            $('#gajiBaru').val(d.gaji_sekarang.gaji);
            $('#gajiTunjangan').val(d.gaji_sekarang.tunjangan);
            $('#gajiBulan').val(sekarang.getMonth() + 1);
            $('#gajiTahun').val(sekarang.getFullYear());

            logGajiAktif = d.log_gaji;
            isiFilterTahunLog(logGajiAktif);
            terapkanFilterLog();
        }

        function isiForm(raw, id) {
            $('#formKaryawan').trigger('reset');
            $('#f_id').val(id || '');
            $('#formTitleText').text(id ? 'Ubah data karyawan' : 'Tambah karyawan');
            $('#wrapGajiAwal').toggleClass('d-none', !!id);
            $('#formKaryawan [name]').not('[type=file], #f_id').each(function() {
                var nama = $(this).attr('name');
                $(this).val(raw && raw[nama] != null ? raw[nama] : '');
            });
            if (!id) $('#f_status').val('Probation');
            bersihkanError('#modalForm');
        }

        function segarkan() {
            muatRingkasan();
            if (tabel) tabel.ajax.reload(null, false);
            if ($('#reminderList').length) muatReminder();
        }

        function unduhSemua($link) {
            if ($link.data('busy')) return;
            $link.data('busy', true);
            setLoading($link, true, 'Menyiapkan ZIP...');

            $.ajax({
                url: $link.attr('href'),
                method: 'GET',
                xhrFields: {
                    responseType: 'blob'
                }
            }).done(function(blob, status, xhr) {
                var cd = xhr.getResponseHeader('Content-Disposition') || '';
                var m = /filename\*?=(?:UTF-8'')?"?([^";]+)"?/i.exec(cd);
                var nama = m ? decodeURIComponent(m[1]) : 'dokumen.zip';
                var url = window.URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url;
                a.download = nama;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                setTimeout(function() {
                    window.URL.revokeObjectURL(url);
                }, 1000);
            }).fail(function() {
                tampilError('#modalDetail', {
                    responseJSON: {
                        message: 'Dokumen gagal diunduh.'
                    }
                });
            }).always(function() {
                setLoading($link, false);
                $link.data('busy', false);
            });
        }

        $(function() {
            muatRingkasan();
            if ($('#reminderList').length) muatReminder();

            if ($('#tabelKaryawan').length) {
                var $tabel = $('#tabelKaryawan');

                $tabel.on('preXhr.dt', function() {
                    $('#tabelError').addClass('d-none').text('');
                    $tabel.addClass('is-loading');
                }).on('xhr.dt draw.dt', function() {
                    $tabel.removeClass('is-loading');
                }).on('error.dt', function(e, settings, techNote, message) {
                    $tabel.removeClass('is-loading');
                    $('#tabelError').removeClass('d-none').text(
                        'Data karyawan gagal dimuat. Coba muat ulang halaman.');
                });

                tabel = $tabel.DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: R.data,
                        type: 'GET',
                        data: function(d) {
                            d.q = $('#fSearch').val();
                            d.divisi = $('#fDivisi').val();
                            d.status = $('#fStatus').val();
                            d.dokumen = $('#fDokumen').val();
                        }
                    },
                    searching: false,
                    pageLength: 10,
                    order: [
                        [0, 'asc']
                    ],
                    columns: [{
                            data: 'nama_lengkap',
                            render: function(d, t, r) {
                                return t === 'display' ?
                                    '<div class="person">' + avatar(r) + '<div><div class="nm">' +
                                    esc(r.nama_lengkap) +
                                    '</div><div class="nip">NIP ' + esc(r.nip || '-') +
                                    '</div></div></div>' : d;
                            }
                        },
                        {
                            data: 'divisi',
                            defaultContent: '-'
                        },
                        {
                            data: 'jabatan',
                            defaultContent: '-'
                        },
                        {
                            data: 'status_karyawan',
                            render: function(d, t) {
                                return t === 'display' ? statusTag(d) : d;
                            }
                        },
                        {
                            data: 'masa_kerja',
                            orderable: false
                        },
                        {
                            data: 'masa_kontrak',
                            orderable: false
                        },
                        {
                            data: 'basic_salary',
                            orderable: false,
                            defaultContent: 0,
                            render: function(d, t, r) {
                                return t === 'display' ? esc(r.basic_salary_fmt) : (d || 0);
                            }
                        },
                        {
                            data: 'dokumen.persen',
                            orderable: false,
                            render: function(d, t, r) {
                                return t === 'display' ?
                                    '<div class="doc-meter"><div class="progress"><div class="progress-bar' +
                                    (d < 60 ? ' low' : '') +
                                    '" style="width:' + d + '%"></div></div><small>' + r.dokumen
                                    .terisi + ' dari ' + r.dokumen.total +
                                    ' dokumen</small></div>' : d;
                            }
                        },
                        {
                            data: 'reminder.label',
                            orderable: false,
                            defaultContent: '',
                            render: function(d, t, r) {
                                if (t !== 'display') return d || '';
                                if (!r.reminder.level)
                                    return '<span class="tag tag-muted">-</span>';
                                return '<span class="tag ' + (r.reminder.level === 'late' ?
                                        'tag-late' : 'tag-pending') + '">' +
                                    esc(r.reminder.label) + '</span>';
                            }
                        },
                        {
                            data: null,
                            orderable: false,
                            className: 'act text-end',
                            render: function(d, t, r) {
                                if (t !== 'display') return '';

                                return '<div class="d-flex justify-content-end gap-1">' +
                                    '<button type="button" class="btn btn-sm btn-line btn-detail" data-id="' +
                                    r.id + '" title="Detail">' +
                                    '<i class="fa-solid fa-eye"></i>' +
                                    '</button>' +
                                    '<button type="button" class="btn btn-sm btn-line btn-edit" data-id="' +
                                    r.id + '" title="Ubah">' +
                                    '<i class="fa-solid fa-pen"></i>' +
                                    '</button>' +
                                    '</div>';
                            }
                        },
                    ],
                    language: {
                        processing: '<i class="fa-solid fa-spinner fa-spin me-2"></i>Memuat data...',
                        lengthMenu: 'Tampilkan _MENU_ data',
                        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ karyawan',
                        infoEmpty: 'Tidak ada data',
                        emptyTable: 'Belum ada data karyawan.',
                        zeroRecords: 'Tidak ada karyawan yang cocok.',
                        paginate: {
                            previous: 'Sebelumnya',
                            next: 'Berikutnya'
                        }
                    }
                });

                var jeda = null;
                $('#fSearch').on('input', function() {
                    clearTimeout(jeda);
                    jeda = setTimeout(function() {
                        tabel.ajax.reload();
                    }, 400);
                });

                $('#fDivisi, #fStatus, #fDokumen').on('change', function() {
                    tabel.ajax.reload();
                });

                $('#btnResetFilter').on('click', function() {
                    $('#fSearch').val('');
                    $('#fDivisi, #fStatus, #fDokumen').val('');
                    tabel.ajax.reload();
                });
            }
        });

        $(document).on('click', '.btn-detail', function() {
            var $btn = $(this);
            setLoading($btn, true, '');
            $('#fLogTahun').html('<option value="">Semua Tahun</option>');
            muatDetail($btn.attr('data-id'))
                .done(function() {
                    bersihkanError('#modalDetail');
                    bootstrap.Tab.getOrCreateInstance($('#modalDetail .nav-link')[0]).show();
                    bukaModal('modalDetail');
                })
                .always(function() {
                    setLoading($btn, false);
                });
        });

        $(document).on('click', '.btn-edit', function() {
            var $btn = $(this);

            if ($btn.attr('id') === 'dEdit' && detailTerakhir) {
                isiForm(detailTerakhir.raw, detailTerakhir.baris.id);
                bukaModal('modalForm');
                return;
            }

            setLoading($btn, true, '');

            muatDetail($btn.attr('data-id'))
                .done(function(d) {
                    isiForm(d.raw, d.baris.id);
                    bukaModal('modalForm');
                })
                .always(function() {
                    setLoading($btn, false);
                });
        });

        $(document).on('click', '.btn-reminder', function() {
            kembaliKeDetail = $(this).closest('#modalDetail').length > 0;
            $('#formReminder').trigger('reset');
            $('#r_id').val($(this).attr('data-id'));
            $('#rNama').text($(this).attr('data-nama'));
            $('#rJudul').text($(this).attr('data-judul'));
            $('#alasanReminder').prop('required', true);
            bersihkanError('#modalReminder');
            bukaModal('modalReminder');
        });

        $('#formReminder input[name=status]').on('change', function() {
            $('#alasanReminder').prop('required', $(this).val() === 'pending');
        });

        $('#btnDownloadSemua').on('click', function(e) {
            e.preventDefault();
            unduhSemua($(this));
        });

        $(document).on('click', '.btn-upload', function() {
            kunciUpload = $(this).attr('data-key');
            $('#dokInput').val('').trigger('click');
        });

        $(document).on('keydown', '.doc-card.kosong', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                $(this).trigger('click');
            }
        });

        $('#dokInput').on('change', function() {
            var files = $(this).prop('files');
            if (!files || !files.length) return;

            var fd = new FormData();
            fd.append('id', idAktif);
            fd.append(kunciUpload, files[0]);

            bersihkanError('#modalDetail');
            $('#dDokumen').addClass('busy');

            $.ajax({
                url: R.update,
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false
            }).done(function() {
                muatDetail(idAktif);
                segarkan();
            }).fail(function(xhr) {
                tampilError('#modalDetail', xhr);
            }).always(function() {
                $('#dDokumen').removeClass('busy');
            });
        });

        $('#formKaryawan').on('submit', function(e) {
            e.preventDefault();
            kirim(this, $('#f_id').val() ? R.update : R.store, '#modalForm', function() {
                tutupModal('modalForm');
                segarkan();
            });
        });

        $('#formReminder').on('submit', function(e) {
            e.preventDefault();
            kirim(this, R.reminderUpdate, '#modalReminder', function() {
                segarkan();
                if (kembaliKeDetail) {
                    muatDetail($('#r_id').val()).done(function() {
                        bukaModal('modalDetail');
                    });
                } else {
                    tutupModal('modalReminder');
                }
            });
        });

        $('#fLogTahun').on('change', terapkanFilterLog);

        $('#formGaji').on('submit', function(e) {
            e.preventDefault();
            kirim(this, R.gajiStore, '#modalDetail', function(res) {
                segarkan();
                muatDetail($('#g_id').val());
                Swal.fire({
                    icon: 'success',
                    title: res.message || 'Gaji berhasil diperbarui.',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
            });
        });
    </script>
@endpush
