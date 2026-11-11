@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .explorer-container {
            display: flex;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            min-height: 600px;
        }

        .sidebar {
            width: 280px;
            background: #f8f9fa;
            border-right: 1px solid #e5e7eb;
            padding: 20px 0;
            overflow-y: auto;
            flex-shrink: 0;
        }

        .sidebar-title {
            padding: 0 20px 15px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #6b7280;
            letter-spacing: 0.5px;
        }

        .folder-tree {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .folder-tree-item {
            position: relative;
        }

        .folder-tree-link {
            display: flex;
            align-items: center;
            padding: 8px 20px;
            cursor: pointer;
            transition: all 0.15s;
            color: #374151;
            font-size: 0.9rem;
            border-left: 3px solid transparent;
            text-decoration: none;
        }

        .folder-tree-link:hover {
            background: #e8f0ff;
            color: #0062ff;
        }

        .folder-tree-link.active {
            background: #dbeafe;
            color: #0062ff;
            border-left-color: #0062ff;
            font-weight: 600;
        }

        .folder-tree-link.pinned {
            font-weight: 600;
        }

        .folder-tree-link.dragging {
            opacity: 0.5;
            background: #e0e7ff;
        }

        .folder-tree-link.drag-over {
            background: #d4edda !important;
            border-left-color: #28a745 !important;
            color: #155724 !important;
        }

        .folder-tree-link.drag-over-invalid {
            background: #f8d7da !important;
            border-left-color: #dc3545 !important;
            color: #721c24 !important;
        }

        .folder-tree-link .folder-icon {
            margin-right: 10px;
            color: #ffc107;
            font-size: 1.1rem;
        }

        .folder-tree-link .folder-label {
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .folder-tree-link .folder-count-badge {
            background: #e5e7eb;
            color: #6b7280;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-left: 5px;
        }

        .folder-tree-link.active .folder-count-badge {
            background: #0062ff;
            color: white;
        }

        .folder-tree-link .pin-indicator {
            color: #ffc107;
            font-size: 0.7rem;
            margin-right: 5px;
        }

        .folder-tree-link .folder-actions-inline {
            display: none;
            gap: 3px;
            margin-left: 5px;
        }

        .folder-tree-link:hover .folder-actions-inline {
            display: flex;
        }

        .folder-tree-link:hover .folder-count-badge {
            display: none;
        }

        .folder-action-mini {
            width: 22px;
            height: 22px;
            border-radius: 4px;
            border: none;
            background: white;
            color: #6b7280;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            transition: all 0.15s;
        }

        .folder-action-mini:hover {
            background: #0062ff;
            color: white;
        }

        .folder-action-mini.danger:hover {
            background: #dc2626;
        }

        .folder-tree-nested {
            list-style: none;
            padding-left: 20px;
            margin: 0;
            border-left: 1px dashed #d1d5db;
            margin-left: 30px;
        }

        .folder-tree-toggle {
            width: 16px;
            height: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 5px;
            cursor: pointer;
            color: #9ca3af;
            font-size: 0.7rem;
            transition: transform 0.2s;
        }

        .folder-tree-toggle.expanded {
            transform: rotate(90deg);
        }

        .sidebar-divider {
            height: 1px;
            background: #e5e7eb;
            margin: 15px 0;
        }

        .inbox-item {
            background: #fef3c7 !important;
            color: #92400e !important;
        }

        .inbox-item:hover {
            background: #fde68a !important;
            color: #78350f !important;
        }

        .inbox-item.active {
            background: #fbbf24 !important;
            color: #78350f !important;
            border-left-color: #f59e0b !important;
        }

        .inbox-item .folder-icon {
            color: #f59e0b !important;
        }

        .main-content {
            flex: 1;
            padding: 25px 30px;
            overflow-y: auto;
            background: white;
        }

        .breadcrumb-nav {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            color: #6b7280;
            flex-wrap: wrap;
        }

        .breadcrumb-item {
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.15s;
        }

        .breadcrumb-item:hover {
            background: #f3f4f6;
            color: #0062ff;
        }

        .breadcrumb-item.active {
            color: #1f2937;
            font-weight: 600;
            cursor: default;
        }

        .breadcrumb-item.active:hover {
            background: transparent;
            color: #1f2937;
        }

        .breadcrumb-separator {
            color: #d1d5db;
        }

        .content-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
        }

        .content-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.3rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }

        .content-title i {
            color: #ffc107;
            font-size: 1.5rem;
        }

        .content-title.inbox-title i {
            color: #f59e0b;
        }

        .content-subtitle {
            color: #6b7280;
            font-size: 0.85rem;
            margin-top: 3px;
        }

        .main-content.drag-over {
            background: #f0fdf4;
            outline: 2px dashed #22c55e;
            outline-offset: -10px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 15px;
            opacity: 0.4;
        }

        .empty-state h5 {
            color: #6b7280;
            margin-bottom: 8px;
        }

        .content-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .content-table thead th {
            background: #f9fafb;
            padding: 12px 15px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #6b7280;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
            text-align: left;
        }

        .content-table tbody tr {
            transition: all 0.15s;
            cursor: move;
        }

        .content-table tbody tr:hover {
            background: #f9fafb;
        }

        .content-table tbody tr.dragging {
            opacity: 0.4;
        }

        .content-table tbody td {
            padding: 14px 15px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
            font-size: 0.9rem;
        }

        .rating-stars {
            color: #ffc107;
            font-size: 1rem;
            letter-spacing: 1px;
        }

        .pelamar-name-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .pelamar-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.85rem;
            flex-shrink: 0;
        }

        .pelamar-name-text {
            font-weight: 600;
            color: #1f2937;
        }

        .pelamar-email-text {
            font-size: 0.75rem;
            color: #9ca3af;
        }

        .drop-zone-hint {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(34, 197, 94, 0.9);
            color: white;
            padding: 15px 30px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            pointer-events: none;
            z-index: 10;
        }

        .main-content.drag-over .drop-zone-hint {
            display: block;
        }

        .cv-viewer-container {
            width: 100%;
            height: 70vh;
            background: #f3f4f6;
            border-radius: 8px;
            overflow: hidden;
        }

        .cv-viewer-container iframe,
        .cv-viewer-container embed,
        .cv-viewer-container object {
            width: 100%;
            height: 100%;
            border: none;
        }

        .cv-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 70vh;
            color: #9ca3af;
        }

        .cv-empty i {
            font-size: 4rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .skill-tag {
            display: inline-block;
            background: #e8f0ff;
            color: #0062ff;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 12px;
            margin: 2px;
        }

        .subfolder-card {
            cursor: pointer;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.2s;
        }

        .subfolder-card:hover {
            border-color: #0062ff;
            background: #f0f7ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 98, 255, 0.15);
        }

        .subfolders-section h6 {
            font-weight: 700;
        }

        .tg {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            font-size: .74rem;
            font-weight: 600;
            padding: .28rem .7rem;
            border-radius: 1rem;
            white-space: nowrap;
        }

        .tg::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }

        .tg-applied {
            background: #dbeafe;
            color: #2563eb;
        }

        .tg-screening {
            background: #e0e7ff;
            color: #4f46e5;
        }

        .tg-psikotest {
            background: #ccfbf1;
            color: #0f766e;
        }

        .tg-interview {
            background: #fef3c7;
            color: #d97706;
        }

        .tg-offer {
            background: #ede9fe;
            color: #7c3aed;
        }

        .tg-hired {
            background: #dcfce7;
            color: #16a34a;
        }

        .tg-rejected {
            background: #fee2e2;
            color: #dc2626;
        }

        .dt-dialog .modal-content {
            border: none;
            border-radius: 14px;
            overflow: hidden;
        }

        .dt-head {
            display: flex;
            gap: 1rem;
            align-items: center;
            padding: 1.4rem 3.5rem 1.2rem 1.6rem;
            border-bottom: 1px solid #eef0f3;
            position: relative;
        }

        .dt-ava {
            width: 64px;
            height: 64px;
            flex-shrink: 0;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            font-size: 1.4rem;
            font-weight: 700;
        }

        .dt-name {
            margin: 0 0 .1rem;
            font-size: 1.2rem;
            font-weight: 700;
            color: #111827;
        }

        .dt-sub {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .6rem;
            color: #6b7280;
            font-size: .88rem;
        }

        .dt-contact {
            display: flex;
            flex-wrap: wrap;
            gap: .3rem 1.1rem;
            margin-top: .5rem;
            font-size: .82rem;
            color: #4b5563;
        }

        .dt-contact i {
            width: 14px;
            margin-right: .35rem;
            text-align: center;
            color: #9ca3af;
        }

        .dt-close {
            position: absolute;
            top: 1.1rem;
            right: 1.2rem;
        }

        .dt-body {
            max-height: 62vh;
            overflow-y: auto;
            padding: 1.4rem 1.6rem;
        }

        .dt-facts {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: .75rem;
            margin-bottom: 1.6rem;
        }

        .dt-fact {
            padding: .8rem .95rem;
            border: 1px solid #eef0f3;
            border-radius: 10px;
            background: #f8fafc;
        }

        .dt-fact b {
            display: block;
            font-size: 1rem;
            color: #111827;
            line-height: 1.3;
        }

        .dt-fact span {
            font-size: .74rem;
            color: #6b7280;
        }

        .dt-sec {
            margin-bottom: 1.6rem;
        }

        .dt-sec:last-child {
            margin-bottom: 0;
        }

        .dt-sec h6 {
            margin: 0 0 .5rem;
            font-size: .92rem;
            font-weight: 700;
            color: #111827;
        }

        .dt-row {
            display: grid;
            grid-template-columns: 170px 1fr;
            gap: 1rem;
            padding: .55rem 0;
            border-bottom: 1px solid #f3f4f6;
            font-size: .86rem;
        }

        .dt-row:last-child {
            border-bottom: none;
        }

        .dt-row dt {
            margin: 0;
            font-weight: 500;
            color: #6b7280;
        }

        .dt-row dd {
            margin: 0;
            font-weight: 500;
            color: #1f2937;
            word-break: break-word;
        }

        .dt-empty {
            color: #c0c5cd;
            font-weight: 400;
        }

        .dt-chips {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .dt-chip {
            padding: .35rem .75rem;
            border-radius: 8px;
            border: 1px dashed #d1d5db;
            background: #fafbfc;
            color: #9ca3af;
            font-size: .8rem;
        }

        .dt-chip.on {
            border: 1px solid #99f6e4;
            background: #f0fdfa;
            color: #0f766e;
            font-weight: 600;
        }

        .dt-count {
            margin-left: .35rem;
            padding: 0 .5rem;
            border-radius: 10px;
            background: #eef2f7;
            color: #4b5563;
            font-size: .72rem;
            font-weight: 600;
            line-height: 20px;
        }

        .dt-pn-ringkas {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin-bottom: .75rem;
            font-size: .85rem;
            color: #6b7280;
        }

        .dt-pn-ringkas b {
            font-size: 1.4rem;
            color: #92400e;
        }

        .dt-badge-saya {
            margin-left: .4rem;
            padding: .1rem .5rem;
            border-radius: 8px;
            background: #dbeafe;
            color: #1d4ed8;
            font-size: .68rem;
            font-weight: 600;
        }

        .dt-foot {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            padding: .9rem 1.6rem;
            border-top: 1px solid #eef0f3;
            background: #fff;
        }

        .rk-sub {
            font-size: .85rem;
            color: #6b7280;
        }

        .rk-top {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 1.5rem;
            padding: 1rem 1.25rem;
            margin-bottom: 1rem;
            border: 1px solid #eef0f3;
            border-radius: 12px;
            background: #f8fafc;
        }

        .rk-avg {
            font-size: 2.1rem;
            font-weight: 700;
            line-height: 1;
        }

        .rk-lbl {
            margin-top: .25rem;
            font-size: .75rem;
            color: #6b7280;
        }

        .rk-stars {
            color: #f59e0b;
            font-size: 1.15rem;
            letter-spacing: 1px;
        }

        .rk-mini {
            display: flex;
            flex-wrap: wrap;
            gap: .4rem .9rem;
            margin-left: auto;
            font-size: .78rem;
            color: #4b5563;
        }

        .rk-filter {
            display: flex;
            flex-wrap: wrap;
            gap: .4rem;
            margin-bottom: 1.1rem;
        }

        .rk-chip {
            padding: .3rem .85rem;
            border: 1px solid #e5e7eb;
            border-radius: 2rem;
            background: #fff;
            color: #6b7280;
            font-size: .8rem;
            font-weight: 600;
            cursor: pointer;
        }

        .rk-chip.active {
            background: #0062ff;
            border-color: #0062ff;
            color: #fff;
        }

        .rk-stage {
            margin-bottom: 1.25rem;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
        }

        .rk-stage-head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .6rem;
            padding: .8rem 1.1rem;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
        }

        .rk-status {
            font-size: .76rem;
            color: #6b7280;
        }

        .rk-stage-avg {
            margin-left: auto;
            font-size: .8rem;
            color: #92400e;
            font-weight: 600;
        }

        .rk-stage-body {
            padding: 1rem 1.1rem;
        }

        .rk-label {
            margin: 1rem 0 .5rem;
            font-size: .78rem;
            font-weight: 700;
            color: #6b7280;
        }

        .rk-label:first-child {
            margin-top: 0;
        }

        .rk-item {
            padding: .85rem 1rem;
            margin-bottom: .6rem;
            border: 1px solid #eef0f3;
            border-radius: 10px;
            background: #fff;
        }

        .rk-item:last-child {
            margin-bottom: 0;
        }

        .rk-item.saya {
            border-color: #bfdbfe;
            background: #f5f9ff;
        }

        .rk-item-head {
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .rk-ava {
            width: 36px;
            height: 36px;
            flex-shrink: 0;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e8f0ff;
            color: #0062ff;
            font-size: .75rem;
            font-weight: 700;
        }

        .rk-who {
            flex: 1;
            min-width: 0;
        }

        .rk-who b {
            display: block;
            font-size: .88rem;
        }

        .rk-who span {
            font-size: .76rem;
            color: #6b7280;
        }

        .rk-note {
            margin-top: .6rem;
            padding: .6rem .8rem;
            border-left: 3px solid #0062ff;
            border-radius: 6px;
            background: #f8fafc;
            font-size: .84rem;
            line-height: 1.55;
            white-space: pre-line;
        }

        .rk-foot {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .5rem;
            margin-top: .6rem;
            font-size: .76rem;
            color: #6b7280;
        }

        .rk-foot .spacer {
            margin-right: auto;
        }

        .rk-tes {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: .6rem;
        }

        .rk-tes-card {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .8rem 1rem;
            border: 1px solid #eef0f3;
            border-radius: 10px;
        }

        .rk-tes-card.kosong {
            border-style: dashed;
            background: #fafbfc;
            color: #9ca3af;
        }

        .rk-tes-val {
            min-width: 54px;
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f766e;
        }

        .rk-tes-card b {
            display: block;
            font-size: .86rem;
        }

        .rk-tes-card span {
            font-size: .75rem;
            color: #6b7280;
        }

        .rk-empty {
            padding: .4rem 0;
            font-size: .85rem;
            color: #9ca3af;
            font-style: italic;
        }

        .rk-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .rk-link:hover small {
            color: #0062ff;
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .explorer-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                max-height: 250px;
                border-right: none;
                border-bottom: 1px solid #e5e7eb;
            }

            .dt-facts {
                grid-template-columns: repeat(2, 1fr);
            }

            .dt-row {
                grid-template-columns: 1fr;
                gap: .1rem;
            }

            .rk-tes {
                grid-template-columns: 1fr;
            }

            .rk-mini {
                margin-left: 0;
            }
        }
    </style>

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">
                <i class="fa fa-folder-open text-warning"></i> Data Pelamar
                @if ($title)
                    {{ $title }}
                @endif
            </h4>

            <div class="btn-group" role="group">
                <a href="{{ route('HR.arsip.index', ['layout' => 'app']) }}" class="btn btn-secondary me-3">
                    <i class="fa fa-box"></i> Arsip Data
                </a>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreateFolder">
                    <i class="fa fa-plus"></i> Buat Folder Baru
                </button>
            </div>
        </div>

        <div class="explorer-container">
            <div class="sidebar">
                <div class="sidebar-title">Navigasi</div>
                <ul class="folder-tree" id="folderTree">
                    <li class="folder-tree-item">
                        <a class="folder-tree-link inbox-item active" data-folder-id="inbox"
                            onclick="selectFolder('inbox')">
                            <i class="fa fa-inbox folder-icon"></i>
                            <span class="folder-label">Pelamar</span>
                            <span class="folder-count-badge" id="inboxCount">0</span>
                        </a>
                    </li>
                </ul>

                <div class="sidebar-divider"></div>

                <div class="sidebar-title">Folder</div>
                <ul class="folder-tree" id="folderTreeMain"></ul>
            </div>

            <div class="main-content" id="mainContent" style="position: relative;">
                <div class="drop-zone-hint">
                    <i class="fa fa-download"></i> Jatuhkan di sini untuk menambahkan pelamar
                </div>

                <div class="breadcrumb-nav" id="breadcrumb">
                    <span class="breadcrumb-item active" data-folder-id="inbox">
                        <i class="fa fa-home"></i> Pelamar
                    </span>
                </div>

                <div class="content-header">
                    <div>
                        <h3 class="content-title inbox-title" id="contentTitle">
                            <i class="fa fa-inbox"></i>
                            <span>Pelamar</span>
                        </h3>
                        <div class="content-subtitle" id="contentSubtitle">
                            Pelamar yang belum masuk folder manapun
                        </div>
                    </div>
                </div>

                <div id="contentTableContainer">
                    <div class="text-center text-muted py-5">
                        <i class="fa fa-spinner fa-spin fa-3x mb-3"></i>
                        <p>Memuat data...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCreateFolder" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-folder-plus"></i> Buat Folder Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formCreateFolder">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Folder</label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Parent Folder (Opsional)</label>
                            <select name="parent_id" id="parentFolderSelect" class="form-select">
                                <option value="">-- Root Folder --</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Buat Folder</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalRenameFolder" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-edit"></i> Ubah Nama Folder</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formRenameFolder">
                    <input type="hidden" name="folder_id" id="renameFolderId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Baru</label>
                            <input type="text" name="nama" id="renameFolderName" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPenilaian" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-star"></i> Penilaian Pelamar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formPenilaian" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="pelamar_id" id="penilaianPelamarId">
                    <input type="hidden" name="folder_id" id="penilaianFolderId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Pelamar</label>
                            <input type="text" id="penilaianNama" class="form-control" readonly>
                        </div>
                        <div class="alert alert-light border py-2 mb-3" id="penilaianTahapBox"
                            style="font-size:.85rem; display:none;">
                            Penilaian dicatat pada tahap <b id="penilaianTahap">-</b>.
                            <span id="penilaianHint"></span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Penilaian (1-4 Bintang)</label>
                            <div class="d-flex gap-2 fs-1" style="cursor: pointer;" id="starContainer">
                                <span class="star" data-value="1" style="color: #ddd;">☆</span>
                                <span class="star" data-value="2" style="color: #ddd;">☆</span>
                                <span class="star" data-value="3" style="color: #ddd;">☆</span>
                                <span class="star" data-value="4" style="color: #ddd;">☆</span>
                            </div>
                            <input type="hidden" name="rating" id="ratingInput" value="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Catatan</label>
                            <textarea name="catatan" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">File Penilaian (PDF/DOC)</label>
                            <input type="file" name="file_penilaian" class="form-control" accept=".pdf,.doc,.docx">
                            <div class="form-text" id="penilaianInfoFile"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Simpan Penilaian</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetailPelamar" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered dt-dialog">
            <div class="modal-content" id="detailContent"></div>
        </div>
    </div>

    <div class="modal fade" id="modalRekap" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0"><i class="fa fa-clipboard-list text-primary"></i> Rekap penilaian</h5>
                        <div class="rk-sub" id="rkNama">-</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="rkBody"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success me-auto" id="rkBtnNilai" style="display:none;">
                        <i class="fa fa-star"></i> Beri penilaian
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCVViewer" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">
                        <i class="fa fa-file-pdf me-2"></i>
                        <span id="cvTitle">CV Pelamar</span>
                    </h5>
                    <div class="d-flex gap-2">
                        <a href="#" id="cvDownloadBtn" class="btn btn-sm btn-outline-light" target="_blank">
                            <i class="fa fa-download"></i> Download
                        </a>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                </div>
                <div class="modal-body p-0">
                    <div id="cvViewerContainer" class="cv-viewer-container">
                        <div class="cv-empty">
                            <i class="fa fa-file-pdf"></i>
                            <h5>Memuat CV...</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let allFolders = [];
        let unassignedPelamar = [];
        let currentFolderId = 'inbox';
        let currentFolderName = 'Inbox';
        let draggedFolderId = null;
        let draggedPelamarId = null;
        let DETAIL_AKTIF = null;
        let DETAIL_KEMBALI = null;
        let PN_DARI_DETAIL = false;
        let REKAP = {
            id: null,
            nama: '',
            data: null,
            filter: 'all',
            kembali: false,
            reopen: false
        };

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        function esc(s) {
            return String(s ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            })[c]);
        }

        function bintang(n) {
            const r = Math.max(0, Math.min(4, Math.round(n)));
            return '★'.repeat(r) + '☆'.repeat(4 - r);
        }

        function tglIndo(s, panjang = true) {
            return s ? new Date(s).toLocaleDateString('id-ID', {
                day: 'numeric',
                month: panjang ? 'long' : 'short',
                year: 'numeric'
            }) : '';
        }

        function getInitial(name) {
            if (!name) return '?';
            const parts = name.trim().split(' ');
            return (parts[0][0] + (parts[1] ? parts[1][0] : '')).toUpperCase();
        }

        function bukaModal(id) {
            bootstrap.Modal.getOrCreateInstance(document.getElementById(id)).show();
        }

        function tutupModal(id) {
            const m = bootstrap.Modal.getInstance(document.getElementById(id));
            if (m) m.hide();
        }

        function gantiModal(dariId, aksi) {
            const el = document.getElementById(dariId);
            if (el && el.classList.contains('show')) {
                $(el).one('hidden.bs.modal', aksi);
                bootstrap.Modal.getOrCreateInstance(el).hide();
            } else {
                aksi();
            }
        }

        function gagal(xhr) {
            showToast((xhr.responseJSON && xhr.responseJSON.message) || 'Terjadi kesalahan pada server', 'danger');
        }

        function namaFolder(id) {
            const f = allFolders.find(x => x.id == id);
            return f ? f.nama : '';
        }

        $(document).ready(function() {
            loadAllData();
        });

        function loadAllData() {
            $.ajax({
                type: 'GET',
                url: "{{ route('HR.folders.data') }}",
                success: function(response) {
                    if (response.success) {
                        allFolders = response.data;
                        renderSidebar();
                        renderContent();
                    }
                },
                error: gagal
            });

            $.ajax({
                type: 'GET',
                url: "{{ route('HR.folders.pelamar.belum-folder') }}",
                success: function(response) {
                    if (response.success) {
                        unassignedPelamar = response.data;
                        $('#inboxCount').text(unassignedPelamar.length);
                        if (currentFolderId === 'inbox') {
                            renderContent();
                        }
                    }
                },
                error: gagal
            });
        }

        function renderSidebar() {
            const tree = $('#folderTreeMain');
            tree.empty();
            renderFolderTree(allFolders, null, tree);
            updateParentFolderSelect();

            if (currentFolderId !== 'inbox') {
                $(`.folder-tree-link[data-folder-id="${currentFolderId}"]`).addClass('active');
            }
        }

        function renderFolderTree(folders, parentId, container) {
            const filtered = folders.filter(f => {
                if (parentId == null) return f.parent_id == null || f.parent_id == 0 || f.parent_id === '';
                return f.parent_id == parentId;
            });

            filtered.sort((a, b) => {
                if (a.is_pinned && !b.is_pinned) return -1;
                if (!a.is_pinned && b.is_pinned) return 1;
                return a.nama.localeCompare(b.nama);
            });

            filtered.forEach(folder => {
                const hasChildren = folders.some(f => f.parent_id == folder.id);
                const count = folder.pelamars ? folder.pelamars.length : 0;

                const li = $(`
                    <li class="folder-tree-item">
                        <a class="folder-tree-link ${folder.is_pinned ? 'pinned' : ''}"
                           data-folder-id="${folder.id}"
                           data-parent-id="${folder.parent_id || ''}"
                           draggable="true"
                           onclick="selectFolder(${folder.id})">
                            ${hasChildren ? '<span class="folder-tree-toggle" onclick="event.stopPropagation(); toggleFolderChildren(this, ' + folder.id + ')"><i class="fa fa-chevron-right"></i></span>' : '<span style="width: 21px; display: inline-block;"></span>'}
                            <i class="fa fa-folder folder-icon"></i>
                            ${folder.is_pinned ? '<i class="fa fa-thumbtack pin-indicator"></i>' : ''}
                            <span class="folder-label" title="${esc(folder.nama)}">${esc(folder.nama)}</span>
                            <span class="folder-count-badge">${count}</span>
                            <span class="folder-actions-inline">
                                <button class="folder-action-mini" onclick="event.stopPropagation(); togglePin(${folder.id})" title="Pin/Unpin">
                                    <i class="fa fa-thumbtack"></i>
                                </button>
                                <button class="folder-action-mini" onclick="event.stopPropagation(); showRenameModal(${folder.id})" title="Rename">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button class="folder-action-mini" onclick="event.stopPropagation(); archiveFolder(${folder.id})" title="Archive">
                                    <i class="fa fa-archive"></i>
                                </button>
                                <button class="folder-action-mini danger" onclick="event.stopPropagation(); deleteFolder(${folder.id})" title="Delete">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </span>
                        </a>
                        ${hasChildren ? `<ul class="folder-tree-nested" id="folder-children-${folder.id}" style="display: none;"></ul>` : ''}
                    </li>
                `);

                container.append(li);

                if (hasChildren) {
                    renderFolderTree(folders, folder.id, $(`#folder-children-${folder.id}`));
                }
            });
        }

        function toggleFolderChildren(toggleEl, folderId) {
            $(`#folder-children-${folderId}`).slideToggle(200);
            $(toggleEl).toggleClass('expanded');
        }

        function updateParentFolderSelect() {
            const select = $('#parentFolderSelect');
            select.find('option:not(:first)').remove();

            function addOptions(parentId, level) {
                allFolders.filter(f => {
                    if (parentId == null) return f.parent_id == null || f.parent_id == 0 || f.parent_id === '';
                    return f.parent_id == parentId;
                }).forEach(folder => {
                    select.append($('<option>').val(folder.id).text('—'.repeat(level) + ' ' + folder.nama));
                    addOptions(folder.id, level + 1);
                });
            }

            addOptions(null, 0);
        }

        function selectFolder(folderId, folderName) {
            currentFolderId = folderId;
            currentFolderName = folderId === 'inbox' ? 'Inbox' : (folderName || namaFolder(folderId));

            $('.folder-tree-link').removeClass('active');
            $(`.folder-tree-link[data-folder-id="${folderId}"]`).addClass('active');

            expandParentFolders(folderId);
            renderContent();
            renderBreadcrumb(folderId);
        }

        function expandParentFolders(folderId) {
            if (folderId === 'inbox') return;

            const folder = allFolders.find(f => f.id == folderId);
            if (!folder || folder.parent_id == null || folder.parent_id == 0 || folder.parent_id === '') return;

            const nested = $(`#folder-children-${folder.parent_id}`);
            nested.show();
            nested.siblings('a').find('.folder-tree-toggle').addClass('expanded');

            expandParentFolders(folder.parent_id);
        }

        function renderBreadcrumb(folderId) {
            const breadcrumb = $('#breadcrumb');
            breadcrumb.empty();

            if (folderId === 'inbox') {
                breadcrumb.append('<span class="breadcrumb-item active"><i class="fa fa-home"></i> Pelamar</span>');
                return;
            }

            const path = [];
            let current = allFolders.find(f => f.id == folderId);

            while (current) {
                path.unshift(current);
                current = current.parent_id ? allFolders.find(f => f.id == current.parent_id) : null;
            }

            breadcrumb.append('<span class="breadcrumb-item" onclick="selectFolder(\'inbox\')"><i class="fa fa-home"></i> Pelamar</span>');

            path.forEach((folder, index) => {
                breadcrumb.append('<span class="breadcrumb-separator"><i class="fa fa-chevron-right"></i></span>');
                const isLast = index === path.length - 1;

                breadcrumb.append(`
                    <span class="breadcrumb-item ${isLast ? 'active' : ''}" ${isLast ? '' : `onclick="selectFolder(${folder.id})"`}>
                        <i class="fa fa-folder"></i> ${esc(folder.nama)}
                    </span>
                `);
            });
        }

        function renderContent() {
            const container = $('#contentTableContainer');
            const title = $('#contentTitle');
            const subtitle = $('#contentSubtitle');

            if (currentFolderId === 'inbox') {
                title.html('<i class="fa fa-inbox"></i> <span>Pelamar</span>').addClass('inbox-title');
                subtitle.text('Pelamar yang belum masuk folder manapun');
                renderInboxTable(container);
                return;
            }

            const folder = allFolders.find(f => f.id == currentFolderId);
            if (!folder) {
                container.html('<div class="empty-state"><i class="fa fa-folder-open"></i><h5>Folder tidak ditemukan</h5></div>');
                return;
            }

            title.html(`<i class="fa fa-folder"></i> <span>${esc(folder.nama)}</span>`).removeClass('inbox-title');
            subtitle.text(`${folder.pelamars.length} pelamar dalam folder ini`);
            renderFolderTable(container, folder);
        }

        function tombolAksi(p, folderId) {
            return `
                <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-outline-primary js-detail" data-id="${p.id}" title="Detail">
                        <i class="fa fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary js-rekap" data-id="${p.id}" data-nama="${esc(p.nama_lengkap)}" title="Semua penilaian">
                        <i class="fa fa-clipboard-list"></i>
                    </button>
                    ${p.cv_path ? `<button class="btn btn-sm btn-outline-danger js-cv" data-id="${p.id}" data-nama="${esc(p.nama_lengkap)}" title="Lihat CV"><i class="fa fa-file-pdf"></i></button>` : ''}
                    ${folderId ? `<button class="btn btn-sm btn-outline-success js-nilai-tabel" data-id="${p.id}" data-folder="${folderId}" data-nama="${esc(p.nama_lengkap)}" title="Nilai"><i class="fa fa-star"></i></button>` : ''}
                </div>`;
        }

        function renderInboxTable(container) {
            if (unassignedPelamar.length === 0) {
                container.html(`
                    <div class="empty-state">
                        <i class="fa fa-inbox"></i>
                        <h5>Pelamar Kosong</h5>
                        <p>Semua pelamar sudah masuk folder</p>
                    </div>
                `);
                return;
            }

            const rows = unassignedPelamar.map((p, index) => `
                <tr draggable="true" data-pelamar-id="${p.id}">
                    <td style="width: 50px;">${index + 1}</td>
                    <td>
                        <div class="pelamar-name-cell">
                            <div class="pelamar-avatar">${esc(getInitial(p.nama_lengkap))}</div>
                            <div>
                                <div class="pelamar-name-text">${esc(p.nama_lengkap)}</div>
                                <div class="pelamar-email-text">${esc(p.email || '-')}</div>
                            </div>
                        </div>
                    </td>
                    <td>${esc(p.jabatan || '-')}</td>
                    <td>${esc(p.divisi || '-')}</td>
                    <td><span class="badge bg-secondary">${esc(p.sumber_lamaran || '-')}</span></td>
                    <td>${esc(tglIndo(p.tanggal_melamar, false) || '-')}</td>
                    <td style="width: 180px;">${tombolAksi(p, null)}</td>
                </tr>
            `).join('');

            container.html(`
                <div class="table-responsive">
                    <table class="content-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Nama Lengkap</th>
                                <th>Posisi</th>
                                <th>Divisi</th>
                                <th>Sumber</th>
                                <th>Tanggal Melamar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            `);

            setupDragAndDrop();
        }

        function renderFolderTable(container, folder) {
            const hasSubfolders = folder.children && folder.children.length > 0;
            const hasPelamars = folder.pelamars && folder.pelamars.length > 0;

            if (!hasSubfolders && !hasPelamars) {
                container.html(`
                    <div class="empty-state">
                        <i class="fa fa-folder-open"></i>
                        <h5>Folder Kosong</h5>
                        <p>Drag & drop pelamar dari Pelamar ke folder ini</p>
                    </div>
                `);
                return;
            }

            let html = '';

            if (hasSubfolders) {
                html += `
                    <div class="subfolders-section mb-4">
                        <h6 class="text-muted mb-3" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fa fa-folder"></i> Subfolder (${folder.children.length})
                        </h6>
                        <div class="row">
                            ${folder.children.map(child => `
                                <div class="col-md-4 col-lg-3 mb-3">
                                    <div class="card subfolder-card h-100" onclick="selectFolder(${child.id})">
                                        <div class="card-body text-center py-4">
                                            <i class="fa fa-folder text-warning" style="font-size: 3rem; margin-bottom: 10px;"></i>
                                            <h6 class="card-title mb-2" style="font-size: 0.9rem;" title="${esc(child.nama)}">
                                                ${esc(child.nama.length > 20 ? child.nama.substring(0, 20) + '...' : child.nama)}
                                            </h6>
                                            ${child.is_pinned ? '<i class="fa fa-thumbtack text-warning ms-2" style="font-size: 0.7rem;"></i>' : ''}
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;

                if (hasPelamars) {
                    html += '<hr class="my-4">';
                }
            }

            if (hasPelamars) {
                const rows = folder.pelamars.map((pf, index) => {
                    const p = pf.pelamar;
                    const nilaiList = (p.penilaians || []).map(x => x.rating).filter(Boolean);
                    const rata = nilaiList.length ? Math.round(nilaiList.reduce((a, b) => a + b, 0) / nilaiList.length * 10) / 10 : null;
                    const ratingHtml = rata ?
                        `<a href="#" class="rk-link js-rekap" data-id="${p.id}" data-nama="${esc(p.nama_lengkap)}"><div class="rating-stars">${bintang(rata)}</div><small class="text-muted">${rata} · ${nilaiList.length} penilaian</small></a>` :
                        '<span class="text-muted">-</span>';

                    return `
                        <tr draggable="true" data-pelamar-id="${p.id}">
                            <td style="width: 50px;">${index + 1}</td>
                            <td>
                                <div class="pelamar-name-cell">
                                    <div class="pelamar-avatar">${esc(getInitial(p.nama_lengkap))}</div>
                                    <div>
                                        <div class="pelamar-name-text">${esc(p.nama_lengkap)}</div>
                                        <div class="pelamar-email-text">${esc(p.email || '-')}</div>
                                    </div>
                                </div>
                            </td>
                            <td>${esc(p.jabatan || '-')}</td>
                            <td>${esc(p.divisi || '-')}</td>
                            <td>${ratingHtml}</td>
                            <td style="width: 180px;">${tombolAksi(p, folder.id)}</td>
                        </tr>
                    `;
                }).join('');

                html += `
                    <h6 class="text-muted mb-3" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                        <i class="fa fa-users"></i> Pelamar (${folder.pelamars.length})
                    </h6>
                    <div class="table-responsive">
                        <table class="content-table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th>Nama Lengkap</th>
                                    <th>Posisi</th>
                                    <th>Divisi</th>
                                    <th>Rating</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>${rows}</tbody>
                        </table>
                    </div>
                `;
            }

            container.html(html);
            setupDragAndDrop();
        }

        function showDetailPelamar(pelamarId) {
            $('#detailContent').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Memuat detail pelamar...</p></div>');
            bukaModal('modalDetailPelamar');

            $.ajax({
                type: 'GET',
                url: `/HR-dashboard/folders/pelamar/${pelamarId}/detail`,
                success: res => res.success ?
                    renderDetailContent(res.data) :
                    $('#detailContent').html('<div class="text-center py-5 text-danger">Gagal memuat data</div>'),
                error: xhr => $('#detailContent').html(`<div class="text-center py-5 text-danger">${esc((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal memuat data')}</div>`)
            });
        }

        function dtPenilaian(n, nama) {
            const ini = (n.penilai_nama || '?').split(' ').slice(0, 2).map(x => x[0] || '').join('').toUpperCase();
            const catatan = n.catatan && String(n.catatan).trim() ?
                `<div class="rk-note">${esc(n.catatan)}</div>` :
                '<div class="rk-empty mt-2">Tidak ada catatan.</div>';

            let berkas = '';
            if (n.file_url) {
                const judul = `Penilaian ${n.penilai_nama} - ${nama}`;
                berkas = (n.file_tipe ?
                        `<button type="button" class="btn btn-sm btn-outline-secondary dt-file" data-judul="${esc(judul)}" data-url="${esc(n.file_url)}" data-tipe="${esc(n.file_tipe)}"><i class="fa fa-eye"></i> Pratinjau</button>` : '') +
                    `<a class="btn btn-sm btn-outline-secondary" href="${esc(n.file_url)}" download target="_blank"><i class="fa fa-download"></i> Unduh</a>`;
            }

            return `
                <div class="rk-item ${n.milik_saya ? 'saya' : ''}">
                    <div class="rk-item-head">
                        <div class="rk-ava">${esc(ini)}</div>
                        <div class="rk-who">
                            <b>${esc(n.penilai_nama)}${n.milik_saya ? '<span class="dt-badge-saya">Anda</span>' : ''}</b>
                            <span>${esc(n.penilai_jabatan)}</span>
                        </div>
                        <div class="text-end">
                            <div class="rk-stars">${bintang(n.rating)}</div>
                            <span class="tg tg-${esc(n.tahap || 'applied')}">${esc(n.tahap_label)}</span>
                        </div>
                    </div>
                    ${catatan}
                    <div class="rk-foot"><span class="spacer"><i class="fa fa-clock"></i> ${esc(n.tanggal_dinilai || '-')}</span>${berkas}</div>
                </div>`;
        }

        function renderDetailContent(data) {
            DETAIL_AKTIF = data;
            const p = data.pelamar;

            const baris = (label, nilai) =>
                `<div class="dt-row"><dt>${label}</dt><dd>${nilai ? nilai : '<span class="dt-empty">-</span>'}</dd></div>`;

            const jk = p.jenis_kelamin === 'L' ? 'Laki-laki' : (p.jenis_kelamin === 'P' ? 'Perempuan' : '');
            const lahir = p.tanggal_lahir ? tglIndo(p.tanggal_lahir) + (data.usia ? ` (${data.usia} tahun)` : '') : '';
            const pendidikan = [p.pendidikan_terakhir, p.jurusan].filter(Boolean).join(' - ');
            const keahlian = Array.isArray(p.keahlian) ? p.keahlian : [];

            const chipTes = data.psikotest.map(t =>
                `<span class="dt-chip ${t.ada ? 'on' : ''}">${esc(t.label)}${t.ada && t.nilai !== null ? ': ' + esc(t.nilai) : ''}</span>`
            ).join('');

            const bagianNilai = data.total_penilai > 0 ? `
                <div class="dt-pn-ringkas"><b>${data.avg_rating}</b><span class="rk-stars">${bintang(data.avg_rating)}</span><span>rata-rata dari ${data.total_penilai} penilaian</span></div>
                ${data.penilaians.map(n => dtPenilaian(n, p.nama_lengkap)).join('')}` :
                '<div class="rk-empty">Belum ada penilaian. Klik "Beri penilaian" untuk mengisi.</div>';

            $('#detailContent').html(`
                <div class="dt-head">
                    <div class="dt-ava">${esc(data.inisial)}</div>
                    <div>
                        <h5 class="dt-name">${esc(p.nama_lengkap)}</h5>
                        <div class="dt-sub">
                            <span>${esc(p.jabatan || '-')} · ${esc(p.divisi || '-')}</span>
                            <span class="tg tg-${esc(p.tahap_rekrutmen)}">${esc(data.tahap_label)}</span>
                        </div>
                        <div class="dt-contact">
                            <span><i class="fa fa-envelope"></i>${esc(p.email || '-')}</span>
                            <span><i class="fa fa-phone"></i>${esc(p.no_telepon || '-')}</span>
                            <span><i class="fa fa-map-marker-alt"></i>${esc(p.domisili || '-')}</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close dt-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="dt-body">
                    <div class="dt-facts">
                        <div class="dt-fact"><b>${p.pengalaman_tahun ? esc(p.pengalaman_tahun) + ' tahun' : '-'}</b><span>Pengalaman</span></div>
                        <div class="dt-fact"><b>${esc(p.ipk || '-')}</b><span>IPK</span></div>
                        <div class="dt-fact"><b>${esc(p.gaji_diharapkan_format || '-')}</b><span>Gaji diharapkan</span></div>
                        <div class="dt-fact"><b>${esc(tglIndo(p.tanggal_melamar, false) || '-')}</b><span>Tanggal melamar</span></div>
                    </div>

                    <section class="dt-sec">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                            <h6 class="mb-0">Penilaian<span class="dt-count">${data.total_penilai}</span></h6>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary js-rekap" data-id="${p.id}" data-nama="${esc(p.nama_lengkap)}">
                                    <i class="fa fa-clipboard-list"></i> Rekap per tahap
                                </button>
                                <button type="button" class="btn btn-sm btn-success js-nilai">
                                    <i class="fa fa-star"></i> ${data.penilaian_saya ? 'Ubah penilaian saya' : 'Beri penilaian'}
                                </button>
                            </div>
                        </div>
                        ${bagianNilai}
                    </section>

                    <section class="dt-sec">
                        <h6>Tes psikologi</h6>
                        <div class="dt-chips">${chipTes}</div>
                    </section>

                    <section class="dt-sec">
                        <h6>Lamaran</h6>
                        <dl class="mb-0">
                            ${baris('Posisi', esc(p.jabatan))}
                            ${baris('Divisi', esc(p.divisi))}
                            ${baris('Sumber lamaran', esc(p.sumber_lamaran))}
                            ${baris('Notice period', esc(p.notice_period))}
                        </dl>
                    </section>

                    <section class="dt-sec">
                        <h6>Pendidikan</h6>
                        <dl class="mb-0">
                            ${baris('Pendidikan terakhir', esc(pendidikan))}
                            ${baris('Institusi', esc(p.institusi))}
                        </dl>
                    </section>

                    <section class="dt-sec">
                        <h6>Data pribadi</h6>
                        <dl class="mb-0">
                            ${baris('Tanggal lahir', esc(lahir))}
                            ${baris('Jenis kelamin', jk)}
                            ${baris('Status pernikahan', esc(p.status_pernikahan))}
                        </dl>
                    </section>

                    <section class="dt-sec">
                        <h6>Keahlian</h6>
                        ${keahlian.length ? keahlian.map(k => `<span class="skill-tag">${esc(k)}</span>`).join('') : '<span class="dt-empty">-</span>'}
                    </section>
                </div>

                <div class="dt-foot">
                    ${p.cv_path ? `<button type="button" class="btn btn-outline-danger js-cv" data-id="${p.id}" data-nama="${esc(p.nama_lengkap)}"><i class="fa fa-file-pdf"></i> Lihat CV</button>` : ''}
                    ${data.portofolio_url ? `<a class="btn btn-outline-secondary" href="${esc(data.portofolio_url)}" target="_blank"><i class="fa fa-folder-open"></i> Portofolio</a>` : ''}
                    <button type="button" class="btn btn-secondary ms-auto" data-bs-dismiss="modal">Tutup</button>
                </div>
            `);
        }

        function showCV(pelamarId, nama) {
            $('#cvTitle').text('CV - ' + nama);
            $('#cvViewerContainer').html('<div class="cv-empty"><i class="fa fa-spinner fa-spin"></i><h5>Memuat CV...</h5></div>');
            bukaModal('modalCVViewer');

            $.ajax({
                type: 'GET',
                url: `/HR-dashboard/folders/pelamar/${pelamarId}/detail`,
                success: function(response) {
                    if (response.success && response.data.cv_url) {
                        const cvUrl = response.data.cv_url;
                        $('#cvDownloadBtn').attr('href', cvUrl);

                        if (cvUrl.toLowerCase().endsWith('.pdf')) {
                            $('#cvViewerContainer').html(`<iframe src="${esc(cvUrl)}" style="width: 100%; height: 70vh; border: none;"></iframe>`);
                        } else {
                            $('#cvViewerContainer').html(`
                                <div class="cv-empty">
                                    <i class="fa fa-file"></i>
                                    <h5>Preview tidak tersedia untuk format ini</h5>
                                    <p>Silakan download untuk melihat dokumen</p>
                                    <a href="${esc(cvUrl)}" class="btn btn-primary mt-3" target="_blank">
                                        <i class="fa fa-download"></i> Download Dokumen
                                    </a>
                                </div>
                            `);
                        }
                    } else {
                        $('#cvViewerContainer').html('<div class="cv-empty"><i class="fa fa-file-pdf"></i><h5>CV tidak ditemukan</h5><p>Pelamar belum mengupload CV</p></div>');
                    }
                },
                error: function() {
                    $('#cvViewerContainer').html('<div class="cv-empty text-danger"><i class="fa fa-exclamation-circle"></i><h5>Gagal memuat CV</h5></div>');
                }
            });
        }

        function bukaDokumen(judul, url, tipe) {
            $('#cvTitle').text(judul);
            $('#cvDownloadBtn').attr('href', url + (url.includes('/psikotest/') ? (url.includes('?') ? '&' : '?') + 'unduh=1' : ''));

            const box = $('#cvViewerContainer');
            if (tipe === 'pdf') {
                box.html(`<iframe src="${esc(url)}" style="width:100%;height:70vh;border:none;"></iframe>`);
            } else if (tipe === 'gambar') {
                box.html(`<div style="padding:1rem;text-align:center;height:70vh;overflow:auto;"><img src="${esc(url)}" style="max-width:100%;border-radius:8px;"></div>`);
            } else {
                box.html('<div class="cv-empty"><i class="fa fa-file"></i><h5>Pratinjau tidak tersedia untuk format ini</h5><p>Gunakan tombol Download di atas.</p></div>');
            }
            bukaModal('modalCVViewer');
        }

        function showRekap(id, nama) {
            REKAP = {
                id: id,
                nama: nama,
                data: null,
                filter: 'all',
                kembali: false,
                reopen: false
            };
            $('#rkNama').text(nama);
            $('#rkBtnNilai').hide();
            $('#rkBody').html('<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2"></div> Memuat rekap penilaian...</div>');

            gantiModal('modalDetailPelamar', () => bukaModal('modalRekap'));

            $.getJSON(`/HR-dashboard/folders/pelamar/${id}/rekap`)
                .done(res => {
                    REKAP.data = res.data;
                    renderRekap();
                })
                .fail(xhr => $('#rkBody').html(`<div class="text-center py-5 text-danger">${esc((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal memuat rekap')}</div>`));
        }

        function rkItem(n) {
            const ini = (n.penilai_nama || '?').split(' ').slice(0, 2).map(x => x[0] || '').join('').toUpperCase();
            const catatan = n.catatan && String(n.catatan).trim() ? `<div class="rk-note">${esc(n.catatan)}</div>` : '';
            const sub = [n.penilai_jabatan, n.tahap_interview_label].filter(x => x && x !== '-').map(esc).join(' · ');

            let berkas = '';
            if (n.file_url) {
                const judul = `Penilaian ${n.penilai_nama} - ${REKAP.nama}`;
                berkas = (n.file_tipe ?
                        `<button type="button" class="btn btn-sm btn-outline-secondary rk-file" data-judul="${esc(judul)}" data-url="${esc(n.file_url)}" data-tipe="${esc(n.file_tipe)}"><i class="fa fa-eye"></i> Pratinjau</button>` : '') +
                    `<a class="btn btn-sm btn-outline-secondary" href="${esc(n.file_url)}" download target="_blank"><i class="fa fa-download"></i> Unduh ${esc((n.file_ext || '').toUpperCase())}</a>`;
            }

            return `
                <div class="rk-item">
                    <div class="rk-item-head">
                        <div class="rk-ava">${esc(ini)}</div>
                        <div class="rk-who"><b>${esc(n.penilai_nama)}</b><span>${sub || '-'}</span></div>
                        <div class="text-end"><div class="rk-stars">${bintang(n.rating)}</div><small class="text-muted">${n.rating} dari 4</small></div>
                    </div>
                    ${catatan}
                    <div class="rk-foot"><span class="spacer"><i class="fa fa-clock"></i> ${esc(n.tanggal_dinilai || '-')} · folder ${esc(n.folder_nama)}</span>${berkas}</div>
                </div>`;
        }

        function rkTes(t) {
            const d = t.data;
            if (!d) {
                return `<div class="rk-tes-card kosong"><div><b>${esc(t.label)}</b><span>Belum ada hasil</span></div></div>`;
            }
            const nilai = d.hasil ? esc(d.hasil) : (d.skor !== null && d.skor !== undefined ? esc(d.skor) : '-');
            const ket = [d.level, d.tanggal_tes ? 'Tes ' + tglIndo(d.tanggal_tes, false) : ''].filter(Boolean).map(esc).join(' · ');
            const tombol = d.file_url ?
                `<button type="button" class="btn btn-sm btn-outline-secondary ms-auto rk-file" data-judul="${esc(t.label)} - ${esc(REKAP.nama)}" data-url="${esc(d.file_url)}" data-tipe="${esc(d.preview_tipe || '')}"><i class="fa fa-eye"></i></button>` : '';

            return `
                <div class="rk-tes-card">
                    <div class="rk-tes-val">${nilai}</div>
                    <div><b>${esc(t.label)}</b><span>${ket || '-'}</span>${d.catatan ? `<div class="mt-1" style="font-size:.78rem;color:#4b5563;">${esc(d.catatan)}</div>` : ''}</div>
                    ${tombol}
                </div>`;
        }

        function rkFeedback(f) {
            return `
                <div class="rk-item">
                    <div class="rk-item-head">
                        <div class="rk-who"><b>${esc(f.oleh)}</b><span>${esc(f.jenis)}${f.ke ? ' · lanjut ke ' + esc(f.ke) : ''}</span></div>
                        ${f.rating ? `<div class="rk-stars">${bintang(f.rating)}</div>` : ''}
                    </div>
                    ${f.keterangan ? `<div class="rk-note">${esc(f.keterangan)}</div>` : ''}
                    <div class="rk-foot"><span><i class="fa fa-clock"></i> ${esc(f.tanggal || '-')}</span></div>
                </div>`;
        }

        function rkJadwal(j) {
            const lokasi = j.link ? `<a href="${esc(j.link)}" target="_blank">${esc(j.link)}</a>` : esc(j.lokasi || '');
            return `
                <div class="rk-item">
                    <div class="rk-who"><b>${esc(j.jadwal || '-')}${j.tahap_interview ? ' · ' + esc(j.tahap_interview) : ''}</b>
                    <span>${esc(j.metode || '')}${j.interviewer ? ' · Interviewer: ' + esc(j.interviewer) : ''}</span></div>
                    ${lokasi ? `<div class="rk-foot"><span>${lokasi}</span></div>` : ''}
                </div>`;
        }

        function rkBlokTahap(t, status) {
            let isi = '';

            if (t.jadwal.length) isi += `<div class="rk-label">Jadwal interview</div>${t.jadwal.map(rkJadwal).join('')}`;
            if (t.psikotest.length) isi += `<div class="rk-label">Hasil tes</div><div class="rk-tes">${t.psikotest.map(rkTes).join('')}</div>`;
            if (t.penilaian.length) isi += `<div class="rk-label">Penilaian interviewer (${t.penilaian.length})</div>${t.penilaian.map(rkItem).join('')}`;
            if (t.feedback.length) isi += `<div class="rk-label">Catatan HR</div>${t.feedback.map(rkFeedback).join('')}`;

            if (!isi) isi = '<div class="rk-empty">Belum ada penilaian di tahap ini.</div>';

            return `
                <section class="rk-stage">
                    <div class="rk-stage-head">
                        <span class="tg tg-${esc(t.kode)}">${esc(t.label)}</span>
                        <span class="rk-status">${status}</span>
                        ${t.rata ? `<span class="rk-stage-avg">Rata-rata ${t.rata}</span>` : ''}
                    </div>
                    <div class="rk-stage-body">${isi}</div>
                </section>`;
        }

        function renderRekap() {
            const d = REKAP.data;
            const urut = d.tahap.map(t => t.kode);
            const idx = urut.indexOf(d.pelamar.tahap);

            const daftar = d.tahap.filter((t, i) => t.ada || i === idx);
            const tampil = daftar.filter(t => REKAP.filter === 'all' || t.kode === REKAP.filter);

            const chip = (kode, label) => `<button type="button" class="rk-chip ${REKAP.filter === kode ? 'active' : ''}" data-filter="${esc(kode)}">${esc(label)}</button>`;
            const filter = chip('all', 'Semua tahap') + daftar.map(t => chip(t.kode, t.label)).join('');

            const mini = d.tahap.filter(t => t.rata).map(t => `<span><b>${esc(t.label)}</b> ${t.rata}</span>`).join('');

            const status = t => {
                const i = urut.indexOf(t.kode);
                if (d.pelamar.tahap === 'rejected' && t.kode === 'rejected') return 'Lamaran ditolak';
                if (i < idx) return 'Selesai';
                if (i === idx) return 'Tahap saat ini';
                return 'Belum dimulai';
            };

            $('#rkBody').html(`
                <div class="rk-top">
                    <div>
                        ${d.rata ? `<div class="rk-avg">${d.rata}</div><div class="rk-lbl">Rata-rata dari 4</div>` : '<div class="text-muted">Belum ada nilai</div>'}
                    </div>
                    ${d.rata ? `<div><div class="rk-stars">${bintang(d.rata)}</div><div class="rk-lbl">${d.total} penilaian</div></div>` : ''}
                    <div><span class="tg tg-${esc(d.pelamar.tahap)}">${esc(d.pelamar.tahap_label)}</span><div class="rk-lbl">Tahap saat ini</div></div>
                    <div class="rk-mini">${mini}</div>
                </div>
                <div class="rk-filter">${filter}</div>
                ${tampil.map(t => rkBlokTahap(t, status(t))).join('') || '<div class="rk-empty">Belum ada data.</div>'}
            `);

            $('#rkBtnNilai').toggle(!!d.folder_id);
        }

        function setupDragAndDrop() {
            $('.content-table tbody tr').off('dragstart').on('dragstart', function(e) {
                draggedPelamarId = $(this).data('pelamar-id');
                draggedFolderId = null;
                e.originalEvent.dataTransfer.setData('type', 'pelamar');
                e.originalEvent.dataTransfer.setData('pelamar_id', draggedPelamarId);
                e.originalEvent.dataTransfer.effectAllowed = 'move';
                $(this).addClass('dragging');
            });

            $('.content-table tbody tr').off('dragend').on('dragend', function() {
                $(this).removeClass('dragging');
                draggedPelamarId = null;
            });

            $('.folder-tree-link').off('dragstart').on('dragstart', function(e) {
                draggedFolderId = $(this).data('folder-id');
                draggedPelamarId = null;
                e.originalEvent.dataTransfer.setData('type', 'folder');
                e.originalEvent.dataTransfer.setData('folder_id', draggedFolderId);
                e.originalEvent.dataTransfer.effectAllowed = 'move';
                $(this).addClass('dragging');
                e.stopPropagation();
            });

            $('.folder-tree-link').off('dragend').on('dragend', function() {
                $(this).removeClass('dragging');
                $('.folder-tree-link').removeClass('drag-over drag-over-invalid');
                draggedFolderId = null;
            });

            $('.folder-tree-link').off('dragover').on('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const targetFolderId = $(this).data('folder-id');

                if (draggedFolderId) {
                    if (targetFolderId == draggedFolderId || isChildFolder(draggedFolderId, targetFolderId)) {
                        $(this).addClass('drag-over-invalid').removeClass('drag-over');
                        e.originalEvent.dataTransfer.dropEffect = 'none';
                    } else {
                        $(this).addClass('drag-over').removeClass('drag-over-invalid');
                        e.originalEvent.dataTransfer.dropEffect = 'move';
                    }
                } else if (draggedPelamarId) {
                    $(this).addClass('drag-over').removeClass('drag-over-invalid');
                    e.originalEvent.dataTransfer.dropEffect = 'move';
                }
            });

            $('.folder-tree-link').off('dragleave').on('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('drag-over drag-over-invalid');
            });

            $('.folder-tree-link').off('drop').on('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('drag-over drag-over-invalid');

                const targetFolderId = $(this).data('folder-id');
                const dragType = e.originalEvent.dataTransfer.getData('type');

                if (dragType === 'folder' && draggedFolderId) {
                    if (targetFolderId == draggedFolderId) {
                        showToast('Folder tidak bisa dipindahkan ke dirinya sendiri', 'danger');
                        return;
                    }

                    if (isChildFolder(draggedFolderId, targetFolderId)) {
                        showToast('Folder tidak bisa dipindahkan ke dalam child-nya sendiri', 'danger');
                        return;
                    }

                    moveFolder(draggedFolderId, targetFolderId);
                } else if (dragType === 'pelamar' && draggedPelamarId) {
                    if (targetFolderId === 'inbox') {
                        removeFromFolder(draggedPelamarId);
                    } else {
                        movePelamar(draggedPelamarId, targetFolderId);
                    }
                }

                draggedFolderId = null;
                draggedPelamarId = null;
            });

            $('.inbox-item').off('dragover').on('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (draggedPelamarId) {
                    $(this).addClass('drag-over');
                    e.originalEvent.dataTransfer.dropEffect = 'move';
                }
            });

            $('.inbox-item').off('dragleave').on('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('drag-over');
            });

            $('.inbox-item').off('drop').on('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('drag-over');

                const dragType = e.originalEvent.dataTransfer.getData('type');
                if (dragType === 'pelamar' && draggedPelamarId) {
                    removeFromFolder(draggedPelamarId);
                }
                draggedPelamarId = null;
            });

            const mainContent = document.getElementById('mainContent');

            mainContent.removeEventListener('dragover', mainContentDragOver);
            mainContent.addEventListener('dragover', mainContentDragOver);

            mainContent.removeEventListener('dragleave', mainContentDragLeave);
            mainContent.addEventListener('dragleave', mainContentDragLeave);

            mainContent.removeEventListener('drop', mainContentDrop);
            mainContent.addEventListener('drop', mainContentDrop);
        }

        function mainContentDragOver(e) {
            if (currentFolderId !== 'inbox' && draggedPelamarId) {
                e.preventDefault();
                document.getElementById('mainContent').classList.add('drag-over');
            }
        }

        function mainContentDragLeave(e) {
            if (e.target === document.getElementById('mainContent')) {
                document.getElementById('mainContent').classList.remove('drag-over');
            }
        }

        function mainContentDrop(e) {
            e.preventDefault();
            document.getElementById('mainContent').classList.remove('drag-over');

            const dragType = e.dataTransfer.getData('type');
            if (dragType === 'pelamar' && draggedPelamarId && currentFolderId !== 'inbox') {
                movePelamar(draggedPelamarId, currentFolderId);
            }
            draggedPelamarId = null;
        }

        function isChildFolder(parentFolderId, targetFolderId) {
            function checkChildren(folderId) {
                const children = allFolders.filter(f => f.parent_id == folderId);
                for (const child of children) {
                    if (child.id == targetFolderId || checkChildren(child.id)) {
                        return true;
                    }
                }
                return false;
            }

            return checkChildren(parentFolderId);
        }

        function moveFolder(folderId, newParentId) {
            $.ajax({
                type: 'POST',
                url: `/HR-dashboard/folders/${folderId}/move`,
                data: {
                    _token: '{{ csrf_token() }}',
                    parent_id: newParentId
                },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        loadAllData();
                    } else {
                        showToast(response.message || 'Gagal memindahkan folder', 'danger');
                    }
                },
                error: gagal
            });
        }

        function movePelamar(pelamarId, folderId) {
            $.ajax({
                type: 'POST',
                url: "{{ route('HR.folders.pelamar.move') }}",
                data: {
                    _token: '{{ csrf_token() }}',
                    pelamar_id: pelamarId,
                    folder_id: folderId
                },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        loadAllData();
                    }
                },
                error: gagal
            });
        }

        function removeFromFolder(pelamarId) {
            $.ajax({
                type: 'DELETE',
                url: `/HR-dashboard/folders/pelamar/${pelamarId}/remove`,
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        loadAllData();
                    }
                },
                error: gagal
            });
        }

        function togglePin(folderId) {
            $.ajax({
                type: 'POST',
                url: `/HR-dashboard/folders/${folderId}/pin`,
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        loadAllData();
                    }
                },
                error: gagal
            });
        }

        function showRenameModal(folderId) {
            $('#renameFolderId').val(folderId);
            $('#renameFolderName').val(namaFolder(folderId));
            bukaModal('modalRenameFolder');
        }

        function archiveFolder(folderId) {
            if (!confirm('Yakin ingin mengarsipkan folder ini?')) return;

            $.ajax({
                type: 'POST',
                url: `/HR-dashboard/folders/${folderId}/archive`,
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        if (currentFolderId == folderId) {
                            selectFolder('inbox');
                        }
                        loadAllData();
                    }
                },
                error: gagal
            });
        }

        function deleteFolder(folderId) {
            if (!confirm('Yakin ingin menghapus folder ini? Semua pelamar di dalamnya akan dihapus.')) return;

            $.ajax({
                type: 'DELETE',
                url: `/HR-dashboard/folders/${folderId}`,
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        if (currentFolderId == folderId) {
                            selectFolder('inbox');
                        }
                        loadAllData();
                    }
                },
                error: gagal
            });
        }

        function showPenilaianModal(pelamarId, folderId, nama, existing = null, tahapLabel = '', dariDetail = false) {
            PN_DARI_DETAIL = dariDetail;

            $('#penilaianPelamarId').val(pelamarId);
            $('#penilaianFolderId').val(folderId || '');
            $('#penilaianNama').val(nama);

            const rating = existing ? existing.rating : 0;
            $('#ratingInput').val(rating);
            $('#starContainer .star').each(function() {
                const aktif = $(this).data('value') <= rating;
                $(this).css('color', aktif ? '#ffc107' : '#ddd').text(aktif ? '★' : '☆');
            });

            $('#formPenilaian textarea[name="catatan"]').val(existing ? (existing.catatan || '') : '');
            $('#formPenilaian input[name="file_penilaian"]').val('');
            $('#penilaianInfoFile').html(existing && existing.file_url ?
                `Dokumen saat ini: <a href="${esc(existing.file_url)}" target="_blank">buka</a>. Kosongkan jika tidak ingin mengganti.` : '');

            $('#penilaianTahap').text(tahapLabel);
            $('#penilaianHint').text(existing ? 'Anda sudah menilai di tahap ini, jadi menyimpan akan memperbarui penilaian Anda.' : '');
            $('#penilaianTahapBox').toggle(!!tahapLabel);

            bukaModal('modalPenilaian');
        }

        function bukaFormNilaiDariServer(id, nama, folderId, dariDetail = false) {
            $.getJSON(`/HR-dashboard/folders/pelamar/${id}/detail`)
                .done(res => {
                    const d = res.data;
                    showPenilaianModal(id, folderId || d.folder_id, nama, d.penilaian_saya, d.tahap_label, dariDetail);
                })
                .fail(gagal);
        }

        $(document).on('click', '.js-detail', function() {
            showDetailPelamar($(this).attr('data-id'));
        });

        $(document).on('click', '.js-rekap', function(e) {
            e.preventDefault();
            showRekap($(this).attr('data-id'), $(this).attr('data-nama'));
        });

        $(document).on('click', '.js-cv', function() {
            const id = $(this).attr('data-id');
            const nama = $(this).attr('data-nama');
            gantiModal('modalDetailPelamar', () => showCV(id, nama));
        });

        $(document).on('click', '.js-nilai-tabel', function() {
            bukaFormNilaiDariServer($(this).attr('data-id'), $(this).attr('data-nama'), $(this).attr('data-folder'));
        });

        $(document).on('click', '.js-nilai', function() {
            const d = DETAIL_AKTIF;
            gantiModal('modalDetailPelamar', () =>
                showPenilaianModal(d.pelamar.id, d.folder_id, d.pelamar.nama_lengkap, d.penilaian_saya, d.tahap_label, true));
        });

        $(document).on('click', '.rk-chip', function() {
            REKAP.filter = $(this).attr('data-filter');
            renderRekap();
        });

        $(document).on('click', '.rk-file', function() {
            const judul = $(this).attr('data-judul');
            const url = $(this).attr('data-url');
            const tipe = $(this).attr('data-tipe') || null;
            REKAP.kembali = true;
            gantiModal('modalRekap', () => bukaDokumen(judul, url, tipe));
        });

        $(document).on('click', '.dt-file', function() {
            const judul = $(this).attr('data-judul');
            const url = $(this).attr('data-url');
            const tipe = $(this).attr('data-tipe') || null;
            DETAIL_KEMBALI = DETAIL_AKTIF.pelamar.id;
            gantiModal('modalDetailPelamar', () => bukaDokumen(judul, url, tipe));
        });

        $('#rkBtnNilai').on('click', function() {
            const d = REKAP.data;
            REKAP.reopen = true;
            gantiModal('modalRekap', () => bukaFormNilaiDariServer(REKAP.id, REKAP.nama, d.folder_id));
        });

        $('#modalCVViewer').on('hidden.bs.modal', function() {
            $('#cvViewerContainer').empty();

            if (REKAP.kembali) {
                REKAP.kembali = false;
                bukaModal('modalRekap');
            } else if (DETAIL_KEMBALI) {
                const id = DETAIL_KEMBALI;
                DETAIL_KEMBALI = null;
                showDetailPelamar(id);
            }
        });

        $('#modalPenilaian').on('hidden.bs.modal', function() {
            PN_DARI_DETAIL = false;
            REKAP.reopen = false;
        });

        $(document).on('click', '.star', function() {
            const value = $(this).data('value');
            $('#ratingInput').val(value);

            $('#starContainer .star').each(function() {
                const aktif = $(this).data('value') <= value;
                $(this).css('color', aktif ? '#ffc107' : '#ddd').text(aktif ? '★' : '☆');
            });
        });

        $(document).on('mouseover', '.star', function() {
            const value = $(this).data('value');
            $('#starContainer .star').each(function() {
                $(this).css('color', $(this).data('value') <= value ? '#ffc107' : '#ddd');
            });
        });

        $(document).on('mouseleave', '#starContainer', function() {
            const currentValue = parseInt($('#ratingInput').val());
            $('#starContainer .star').each(function() {
                $(this).css('color', $(this).data('value') <= currentValue ? '#ffc107' : '#ddd');
            });
        });

        $('#formCreateFolder').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                type: 'POST',
                url: "{{ route('HR.folders.store') }}",
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        tutupModal('modalCreateFolder');
                        $('#formCreateFolder')[0].reset();
                        loadAllData();
                    }
                },
                error: gagal
            });
        });

        $('#formRenameFolder').on('submit', function(e) {
            e.preventDefault();
            const folderId = $('#renameFolderId').val();

            $.ajax({
                type: 'PUT',
                url: `/HR-dashboard/folders/${folderId}/rename`,
                data: {
                    _token: '{{ csrf_token() }}',
                    nama: $('#renameFolderName').val()
                },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        tutupModal('modalRenameFolder');
                        loadAllData();
                    }
                },
                error: gagal
            });
        });

        $('#formPenilaian').on('submit', function(e) {
            e.preventDefault();

            if (!(parseInt($('#ratingInput').val()) > 0)) {
                showToast('Pilih rating terlebih dahulu', 'danger');
                return;
            }

            $.ajax({
                type: 'POST',
                url: "{{ route('HR.folders.pelamar.penilaian') }}",
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');

                        const id = $('#penilaianPelamarId').val();
                        const kembaliKeDetail = PN_DARI_DETAIL;
                        const kembaliKeRekap = REKAP.reopen;
                        const idRekap = REKAP.id;
                        const namaRekap = REKAP.nama;

                        if (kembaliKeDetail) {
                            $('#modalPenilaian').one('hidden.bs.modal', () => showDetailPelamar(id));
                        } else if (kembaliKeRekap) {
                            $('#modalPenilaian').one('hidden.bs.modal', () => showRekap(idRekap, namaRekap));
                        }

                        tutupModal('modalPenilaian');
                        loadAllData();
                    } else {
                        showToast(response.message || 'Gagal menyimpan', 'danger');
                    }
                },
                error: gagal
            });
        });

        function showToast(message, type = 'success') {
            const toast = $(`
                <div class="alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show"
                     role="alert"
                     style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                    ${esc(message)}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
            $('body').append(toast);
            setTimeout(() => toast.fadeOut(200, function() {
                $(this).remove();
            }), 3000);
        }
    </script>
@endsection