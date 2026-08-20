@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4 ks-page">

        <div class="d-flex flex-wrap align-items-end justify-content-between gap-2 mb-3">
            <div>
                <div class="text-uppercase text-muted small fw-bold" style="letter-spacing:.05em;">Jadwal &amp; Kebutuhan
                    Ruang</div>
                <h1 class="h4 fw-bold mb-0 mt-1">Kelas Setting</h1>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="container text-center">
                    <div class="row">
                        <div class="col">
                            <label for="ksViewMode" class="small text-muted fw-semibold mb-0">Tampilan</label>
                            <select id="ksViewMode" class="form-select form-select-sm ks-view-select">
                                <option value="week">Per Minggu</option>
                                <option value="week2">Per 2 Minggu</option>
                                <option value="month">Per Bulan</option>
                                <option value="month2">Per 2 Bulan</option>
                            </select>
                        </div>
                        <div class="col">
                            <label for="ksPeriod" class="small text-muted fw-semibold mb-0">Periode</label>
                            <select id="ksPeriod" class="form-select form-select-sm ks-period-select"></select>
                        </div>
                        <div class="col">
                            <label for="ksSort" class="small text-muted fw-semibold mb-0">Urutkan</label>
                            <select id="ksSort" class="form-select form-select-sm ks-sort-select">
                                <option value="dari_asc">Tanggal (Terdekat)</option>
                                <option value="dari_desc">Tanggal (Terjauh)</option>
                                <option value="kelas_asc">Kelas (A-Z)</option>
                                <option value="kelas_desc">Kelas (Z-A)</option>
                                <option value="instruktur_asc">Instruktur (A-Z)</option>
                                <option value="status_asc">Status (Hitam→Merah)</option>
                            </select>
                        </div>
                        <div class="col">
                            <div class="ks-col-dropdown">
                                <label for="ksColBtn" class="small text-muted fw-semibold mb-0">Entry</label>
                                <button type="button"
                                    class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1" id="ksColBtn">
                                    <i class="bi bi-sliders"></i> Kolom
                                </button>
                                <div class="ks-col-menu" id="ksColMenu"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            <div class="ks-search-wrap">
                <i class="bi bi-search"></i>
                <input type="text" id="ksSearch" class="form-control form-control-sm"
                    placeholder="Cari kelas, instruktur, atau ruangan...">
            </div>
        </div>

        <div id="ksTablesContainer"></div>

        <div class="ks-hint text-muted small mt-2">
            <i class="bi bi-cursor me-1"></i>Klik sel mana pun untuk mengedit langsung &middot;
            <i class="bi bi-chat-left-text me-1 ms-1"></i>arahkan kursor ke sel untuk berkomentar &middot;
            <i class="bi bi-arrow-left-right me-1 ms-1"></i>geser tabel untuk melihat kolom lainnya
        </div>
    </div>

    <div class="ks-toast-container" id="ksToastContainer"></div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        .ks-page {
            max-width: 100%;
        }

        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                animation-duration: .001ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .001ms !important;
            }
        }

        button:focus-visible,
        select:focus-visible,
        input:focus-visible,
        textarea:focus-visible {
            outline: 2px solid #4F46E5;
            outline-offset: 2px;
        }

        .ks-view-select,
        .ks-period-select,
        .ks-sort-select {
            min-width: 150px;
        }

        .ks-period-select {
            min-width: 190px;
        }

        .ks-sort-select {
            min-width: 180px;
        }

        .ks-search-wrap {
            position: relative;
            min-width: 260px;
        }

        .ks-search-wrap i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
            font-size: 12px;
        }

        .ks-search-wrap input {
            padding-left: 30px;
        }

        .ks-col-dropdown {
            position: relative;
        }

        .ks-col-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 6px;
            width: 220px;
            background: #fff;
            border: 1px solid #E3E7ED;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(20, 24, 32, .14);
            z-index: 70;
            padding: 8px;
            animation: ksPopIn .14s ease;
        }

        .ks-col-menu.show {
            display: block;
        }

        .ks-col-menu label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12.5px;
            padding: 5px 6px;
            border-radius: 6px;
            cursor: pointer;
            color: #374151;
        }

        .ks-col-menu label:hover {
            background: #F6F8FB;
        }

        .ks-col-reset {
            width: 100%;
            text-align: left;
            border: none;
            background: none;
            color: #4F46E5;
            font-size: 12px;
            font-weight: 600;
            padding: 6px;
            border-top: 1px solid #EDEFF3;
            margin-top: 4px;
            cursor: pointer;
        }

        .ks-table-title {
            font-weight: 700;
            font-size: 13px;
            color: #374151;
            margin: 18px 0 8px;
        }

        .ks-table-block:first-child .ks-table-title {
            margin-top: 0;
        }

        .ks-stats-bar {
            background: #fff;
            border: 1px solid #E3E7ED;
            border-radius: 10px;
            padding: 10px 16px;
        }

        .ks-stat {
            display: flex;
            align-items: baseline;
            gap: 8px;
            position: relative;
        }

        .ks-stat-label {
            font-size: 11.5px;
            font-weight: 700;
            color: #6B7280;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .ks-stat-value {
            font-size: 20px;
            font-weight: 700;
            color: #181B20;
            transition: color .15s ease;
        }

        .ks-stat-divider {
            width: 1px;
            align-self: stretch;
            background: #E3E7ED;
        }

        .ks-stat-note {
            margin-left: auto;
        }

        .ks-info-btn {
            border: none;
            background: none;
            color: #9CA3AF;
            padding: 0;
            line-height: 1;
            cursor: pointer;
            transition: color .12s ease, transform .12s ease;
        }

        .ks-info-btn:hover {
            color: #4F46E5;
            transform: scale(1.1);
        }

        .ks-info-popover {
            position: fixed;
            width: 300px;
            max-width: calc(100vw - 24px);
            background: #fff;
            border: 1px solid #E3E7ED;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(20, 24, 32, .14);
            z-index: 1050;
            padding: 12px 14px;
            font-size: 12.5px;
            color: #374151;
            line-height: 1.5;
            animation: ksPopIn .15s ease;
        }

        .ks-info-popover strong {
            color: #181B20;
        }

        .ks-info-backdrop {
            position: fixed;
            inset: 0;
            z-index: 55;
        }

        .ks-card {
            border-radius: 14px;
            border: 1px solid #E3E7ED;
            background: #fff;
        }

        .ks-card .card-body {
            overflow: hidden;
            border-radius: inherit;
            padding: 0;
        }

        .ks-scroll {
            overflow-x: auto;
            overflow-y: auto;
            width: 100%;
            display: block;
            max-height: 75vh;
        }

        .ks-scroll::-webkit-scrollbar {
            height: 8px;
            width: 8px;
        }

        .ks-scroll::-webkit-scrollbar-thumb {
            background: #D7DBE2;
            border-radius: 999px;
        }

        .ks-table {
            font-size: 13px;
            margin-bottom: 0;
            width: max-content;
            min-width: 100%;
            table-layout: auto;
        }

        .ks-table thead th {
            background: #181B20;
            color: #EDEFF3;
            font-size: 11.5px;
            font-weight: 600;
            white-space: nowrap;
            border-color: #2C303A;
            vertical-align: middle;
            position: sticky;
            top: 0;
            z-index: 3;
        }

        .ks-table td,
        .ks-table th {
            vertical-align: top;
            position: relative;
        }

        .ks-table td {
            padding: 0;
            border-color: #EDEFF3;
        }

        .ks-table td.ks-idx {
            padding: 9px 10px;
            color: #9CA3AF;
            vertical-align: middle;
            white-space: nowrap;
        }

        .ks-sticky-col {
            position: sticky;
            left: 0;
            z-index: 4;
            background: #181B20;
        }

        td.ks-sticky-col {
            background: #fff;
            z-index: 2;
            box-shadow: 2px 0 0 rgba(20, 24, 32, .05);
        }

        tbody tr:hover td.ks-sticky-col {
            background: #F6F8FB;
        }

        .ks-cell-display {
            padding: 9px 10px;
            min-height: 38px;
            display: flex;
            align-items: flex-start;
            cursor: text;
            color: #23272E;
            line-height: 1.45;
            transition: background-color .12s ease;
            white-space: pre-wrap;
            word-break: break-word;
            overflow-wrap: anywhere;
        }

        .ks-cell-display.ks-long-text {
            max-width: 480px;
            min-width: 200px;
        }

        .ks-cell-display:hover {
            background: #F6F8FB;
        }

        .ks-cell-display.empty {
            color: #B4BAC3;
            font-style: italic;
        }

        .ks-cell-select-wrap {
            padding: 8px 10px;
        }

        .ks-select {
            border: 1px solid #DDE2E8;
            border-radius: 6px;
            padding: 5px 6px;
            font-size: 13px;
            background: #F9FAFB;
            width: 100%;
            transition: border-color .12s ease;
        }

        .ks-select:hover {
            border-color: #B9C0CB;
        }

        .ks-status-select {
            border: none;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }

        .ks-select2-cell {
            padding: 6px;
            min-width: 220px;
        }

        .ks-select2-cell .select2-container {
            width: 100% !important;
        }

        .ks-select2-cell .select2-container--bootstrap-5 .select2-selection {
            min-height: 32px;
            font-size: 13px;
            border-color: #4F46E5;
        }

        .ks-cell-select-wrap .select2-container--bootstrap-5 .select2-selection {
            min-height: 30px;
            font-size: 13px;
            border: 1px solid #DDE2E8;
            background: #F9FAFB;
        }

        .ks-edit-wrap {
            padding: 6px;
        }

        .ks-edit-input {
            width: 100%;
            border: 1.5px solid #4F46E5;
            border-radius: 5px;
            padding: 7px 8px;
            font-size: 13px;
            outline: none;
            white-space: pre-wrap;
            word-break: break-word;
            font-family: inherit;
        }

        textarea.ks-edit-input {
            min-height: 80px;
            max-width: 480px;
            min-width: 200px;
            resize: vertical;
            line-height: 1.45;
        }

        @keyframes ksPulseSave {
            0% {
                background-color: #E7E4FB;
            }

            100% {
                background-color: transparent;
            }
        }

        .ks-just-saved .ks-cell-display,
        .ks-just-saved .ks-cell-select-wrap {
            animation: ksPulseSave .9s ease;
        }

        .ks-cmt-trigger {
            position: absolute;
            top: 3px;
            right: 3px;
            width: 18px;
            height: 18px;
            border-radius: 5px;
            display: none;
            align-items: center;
            justify-content: center;
            background: #fff;
            border: 1px solid #DDE2E8;
            cursor: pointer;
            z-index: 3;
            font-size: 10px;
            color: #9CA3AF;
            transition: transform .12s ease;
        }

        .ks-table td:hover .ks-cmt-trigger {
            display: flex;
        }

        .ks-cmt-trigger:hover {
            transform: scale(1.12);
        }

        .ks-cmt-trigger.has-comments {
            display: flex;
            background: #EEF2FF;
            border-color: #C7D2FE;
            color: #4F46E5;
        }

        .ks-cmt-count {
            position: absolute;
            top: -6px;
            right: -6px;
            background: #4F46E5;
            color: #fff;
            border-radius: 999px;
            font-size: 9px;
            line-height: 1;
            padding: 2px 4px;
            min-width: 14px;
            text-align: center;
            font-weight: 700;
        }

        .ks-cmt-backdrop {
            position: fixed;
            inset: 0;
            z-index: 1040;
        }

        .ks-cmt-popover {
            position: fixed;
            width: 320px;
            height: 340px;
            max-width: calc(100vw - 24px);
            max-height: calc(100vh - 24px);
            background: #fff;
            border: 1px solid #E3E7ED;
            border-radius: 12px;
            box-shadow: 0 12px 28px rgba(20, 24, 32, .18);
            z-index: 1050;
            padding: 14px;
            display: flex;
            flex-direction: column;
            animation: ksPopIn .15s ease;
            overflow: hidden;
        }

        .ks-cmt-list {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 10px;
            padding-right: 6px;
        }

        .ks-cmt-item {
            background: #F6F8FB;
            border-radius: 8px;
            padding: 7px 9px;
            position: relative;
            flex: 0 0 auto;
        }

        .ks-cmt-item .ks-cmt-del {
            position: absolute;
            top: 5px;
            right: 6px;
            border: none;
            background: none;
            color: #C6CBD3;
            cursor: pointer;
            font-size: 12px;
            padding: 0;
        }

        .ks-cmt-item .ks-cmt-del:hover {
            color: #DC3D34;
        }

        @keyframes ksPopIn {
            from {
                opacity: 0;
                transform: translateY(-4px) scale(.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes ksRowIn {
            from {
                opacity: 0;
                transform: translateY(-6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes ksRowOut {
            to {
                opacity: 0;
                transform: translateX(8px);
            }
        }

        .ks-row-enter {
            animation: ksRowIn .22s ease;
        }

        .ks-row-leaving {
            animation: ksRowOut .2s ease forwards;
        }

        .ks-skel-cell {
            padding: 12px 10px;
        }

        .ks-skel-bar {
            height: 12px;
            border-radius: 4px;
            background: linear-gradient(90deg, #EDEFF3 25%, #F6F7F9 37%, #EDEFF3 63%);
            background-size: 400% 100%;
            animation: ksShimmer 1.3s ease infinite;
        }

        @keyframes ksShimmer {
            0% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0 50%;
            }
        }

        .ks-empty-row td {
            padding: 34px 12px;
            text-align: center;
            color: #9CA3AF;
            font-size: 13px;
        }

        .ks-empty-row i {
            font-size: 20px;
            display: block;
            margin-bottom: 6px;
            color: #C6CBD3;
        }

        .ks-toast-container {
            position: fixed;
            right: 18px;
            bottom: 18px;
            z-index: 200;
            display: flex;
            flex-direction: column;
            gap: 8px;
            width: 300px;
        }

        .ks-toast {
            background: #fff;
            border: 1px solid #E3E7ED;
            border-left: 3px solid #4F46E5;
            border-radius: 10px;
            box-shadow: 0 10px 28px rgba(20, 24, 32, .16);
            padding: 10px 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12.5px;
            color: #23272E;
            animation: ksToastIn .18s ease;
        }

        .ks-toast-out {
            animation: ksToastOut .18s ease forwards;
        }

        @keyframes ksToastIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes ksToastOut {
            to {
                opacity: 0;
                transform: translateY(8px);
            }
        }

        .ks-toast-msg {
            flex: 1;
            line-height: 1.4;
        }

        .ks-toast-action {
            border: none;
            background: none;
            color: #4F46E5;
            font-weight: 700;
            font-size: 12px;
            cursor: pointer;
            white-space: nowrap;
        }

        .ks-toast-close {
            border: none;
            background: none;
            color: #C6CBD3;
            cursor: pointer;
            font-size: 14px;
            line-height: 1;
            padding: 0;
        }

        .ks-toast-close:hover {
            color: #9CA3AF;
        }

        .select2-container--bootstrap-5 .select2-dropdown {
            border-color: #E3E7ED;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(20, 24, 32, .14);
            z-index: 1060;
        }

        .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background-color: #4F46E5 !important;
        }

        .select2-container--bootstrap-5 .select2-search__field:focus {
            border-color: #4F46E5;
            box-shadow: 0 0 0 .2rem rgba(79, 70, 229, .25);
        }

        @media (max-width: 576px) {
            .ks-stats-bar {
                flex-direction: column;
                align-items: flex-start;
            }

            .ks-stat-note {
                margin-left: 0;
            }

            .ks-stat-divider {
                display: none;
            }

            .ks-toast-container {
                left: 12px;
                right: 12px;
                width: auto;
            }
        }

        .ks-select2-cell .select2-container--bootstrap-5 .select2-selection--multiple {
            min-height: 32px;
            font-size: 13px;
            border-color: #4F46E5;
        }

        .ks-select2-cell .select2-container--bootstrap-5 .select2-selection__choice {
            font-size: 12px;
            background: #EEF2FF;
            border-color: #C7D2FE;
            color: #4F46E5;
        }

        .ks-needs-wrap {
            background: #fff;
            border: 1px solid #E3E7ED;
            border-radius: 10px;
            overflow: hidden;
        }

        .ks-needs-table {
            font-size: 12.5px;
        }

        .ks-needs-table thead th {
            background: #F6F8FB;
            color: #374151;
            font-weight: 700;
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: .03em;
            border-color: #E3E7ED;
            vertical-align: middle;
        }

        .ks-needs-table td {
            vertical-align: middle;
            border-color: #EDEFF3;
        }

        .ks-needs-table .ks-need-val {
            font-weight: 700;
            color: #181B20;
        }

        .ks-needs-table th .ks-stat {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            position: relative;
        }

        .ks-needs-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 10px 14px;
            cursor: pointer;
            user-select: none;
            background: #F6F8FB;
            transition: background-color .12s ease;
        }

        .ks-needs-header:hover {
            background: #EEF1F5;
        }

        .ks-needs-header-title {
            font-weight: 700;
            font-size: 12.5px;
            color: #374151;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ks-needs-chevron {
            transition: transform .18s ease;
            color: #9CA3AF;
            font-size: 11px;
        }

        .ks-needs-wrap.open .ks-needs-chevron {
            transform: rotate(90deg);
        }

        .ks-needs-body {
            border-top: 1px solid #E3E7ED;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        (function($) {
            'use strict';

            var API_BASE = '{{ url('kelas-setting/') }}';
            var CSRF_TOKEN = '{{ csrf_token() }}';
            var CURRENT_USER = @json(auth()->check() ? (auth()->user()->username ?? 'User') : 'Anonymous');

            var LONG_TEXT_FIELDS = ['software', 'keterangan'];
            var SELECT2_FIELDS = ['kelas', 'instruktur', 'pcits', 'asset'];

            var RUANGAN_OPTIONS = [
                'Ruang 1', 'Ruang 2', 'Ruang 3', 'Ruang 4', 'Ruang 5', 'Ruang 6',
                'Adoc', 'R. Meeting Kecil', 'R. Meeting Besar',
                'Inhouse Bandung', 'Inhouse Luar Bandung', 'Workingspace', 'Virtual'
            ];

            var DAY_NAMES = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

            var DEVICE_OPTIONS = ['Laptop', 'PC', 'PC & LAPTOP'];
            var DEVICE_INSTRUCTOR_OPTIONS = ['Laptop Instruktur', 'Laptop Kelas', 'PC Kelas'];

            var COLUMNS = [{
                    key: 'kelas',
                    label: 'Kelas',
                    type: 'select2',
                    options: [],
                    width: '260px'
                },
                {
                    key: 'dari',
                    label: 'Dari',
                    type: 'date',
                    width: '110px'
                },
                {
                    key: 'sampai',
                    label: 'Sampai',
                    type: 'date',
                    width: '110px'
                },
                {
                    key: 'ruangan',
                    label: 'Ruangan',
                    type: 'select',
                    options: RUANGAN_OPTIONS,
                    width: '180px'
                },
                {
                    key: 'device',
                    label: 'Device',
                    type: 'select',
                    options: DEVICE_OPTIONS,
                    width: '110px'
                },
                {
                    key: 'deviceInstruktur',
                    label: 'Device Instruktur',
                    type: 'select',
                    options: DEVICE_INSTRUCTOR_OPTIONS,
                    width: '140px'
                },
                {
                    key: 'pax',
                    label: 'Pax',
                    type: 'number',
                    width: '65px'
                },
                {
                    key: 'instruktur',
                    label: 'Instruktur',
                    type: 'select2',
                    options: [],
                    width: '260px'
                },
                {
                    key: 'asset',
                    label: 'Asset',
                    type: 'textarea',
                    width: '240px'
                },
                {
                    key: 'software',
                    label: 'Software Install',
                    type: 'textarea',
                    width: '240px'
                },
                {
                    key: 'keterangan',
                    label: 'Keterangan',
                    type: 'textarea',
                    width: '260px'
                },
                {
                    key: 'pcits',
                    label: 'PIC TS',
                    type: 'select2',
                    options: [],
                    width: '260px'
                },
                {
                    key: 'status',
                    label: 'Status',
                    type: 'select',
                    options: ['Hitam', 'Biru', 'Merah'],
                    width: '105px'
                }
            ];

            var STATUS_STYLE = {
                Merah: {
                    bg: '#FDEBEA',
                    fg: '#B3261E'
                },
                Biru: {
                    bg: '#EAF3FF',
                    fg: '#1565C0'
                },
                Hitam: {
                    bg: '#F0F0F0',
                    fg: '#212121'
                }
            };
            var STATUS_ORDER = {
                Hitam: 1,
                Biru: 2,
                Merah: 3
            };

            var INFO_TEXT = {
                laptop: 'Kelas dikelompokkan per kombinasi <strong>Ruangan + Instruktur</strong>. Dari kelas-kelas yang memakai Device = "Laptop", diambil <strong>pax terbesar</strong> di tiap kelompok itu.',
                pc: 'Sama seperti laptop, dikelompokkan per <strong>Ruangan + Instruktur</strong>. Untuk tiap kelompok dihitung dua angka lalu dijumlah: kebutuhan peserta dan instruktur.'
            };

            var MONTHS_SHORT = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
            var MONTHS_FULL = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September',
                'Oktober', 'November', 'Desember'
            ];

            var weeksData = {};
            var metaOptions = {
                kelas: [],
                instruktur: [],
                pcits: [],
                asset: []
            };

            var currentViewMode = 'week';
            var currentPeriodValue = null;
            var currentSort = 'dari_asc';
            var editingCell = null;
            var openCommentCell = null;
            var openInfo = null;
            var needsOpenState = {};
            var colMenuOpen = false;
            var searchQuery = '';
            var isLoadingPeriod = false;
            var uidCounter = 0;
            var searchDebounce = null;

            var visibleCols = {};
            COLUMNS.forEach(function(c) {
                visibleCols[c.key] = true;
            });

            var justAddedRowId = null;
            var justSavedKey = null;
            var removingRowId = null;
            var toastActions = {};

            function uid(prefix) {
                uidCounter++;
                return prefix + Date.now().toString(36) + uidCounter;
            }

            function ck(rowId, key) {
                return rowId + ':' + key;
            }

            function esc(v) {
                return String(v == null ? '' : v)
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }

            function pad2(n) {
                return (n < 10 ? '0' : '') + n;
            }

            function fmtDate(iso) {
                if (!iso) return '';
                var p = iso.split('-');
                return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : iso;
            }

            function fmtRangeLabel(startISO, endISO) {
                var s = startISO.split('-'),
                    e = endISO.split('-');
                var sy = s[0],
                    sm = parseInt(s[1], 10),
                    sd = parseInt(s[2], 10);
                var ey = e[0],
                    em = parseInt(e[1], 10),
                    ed = parseInt(e[2], 10);
                if (sy === ey && sm === em) return pad2(sd) + ' – ' + pad2(ed) + ' ' + MONTHS_SHORT[em - 1] + ' ' + ey;
                else if (sy === ey) return pad2(sd) + ' ' + MONTHS_SHORT[sm - 1] + ' – ' + pad2(ed) + ' ' +
                    MONTHS_SHORT[em - 1] + ' ' + ey;
                return pad2(sd) + ' ' + MONTHS_SHORT[sm - 1] + ' ' + sy + ' – ' + pad2(ed) + ' ' + MONTHS_SHORT[em -
                    1] + ' ' + ey;
            }

            function monthLabel(monthKey) {
                var p = monthKey.split('-');
                return MONTHS_FULL[parseInt(p[1], 10) - 1] + ' ' + p[0];
            }

            function toApiField(jsKey) {
                if (jsKey === 'deviceInstruktur') return 'device_instruktur';
                if (jsKey === 'pcits') return 'pc_its';
                return jsKey;
            }

            function apiCall(method, url, data) {
                return $.ajax({
                    url: url,
                    method: method,
                    data: data ? JSON.stringify(data) : undefined,
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    }
                });
            }

            function saveScrollPositions() {
                var positions = {};
                $('.ks-scroll').each(function() {
                    var tableId = $(this).closest('.ks-table-block').data('table-id');
                    if (tableId) {
                        positions[tableId] = {
                            left: this.scrollLeft,
                            top: this.scrollTop
                        };
                    }
                });
                return positions;
            }

            function restoreScrollPositions(positions) {
                if (!positions) return;
                $('.ks-scroll').each(function() {
                    var tableId = $(this).closest('.ks-table-block').data('table-id');
                    if (tableId && positions[tableId]) {
                        this.scrollLeft = positions[tableId].left;
                        this.scrollTop = positions[tableId].top;
                    }
                });
            }

            function loadInitialData(opts) {
                opts = opts || {};
                return $.get(API_BASE + '?search=' + encodeURIComponent(searchQuery))
                    .done(function(res) {
                        if (res.success && res.data) {
                            weeksData = res.data;
                            if (res.meta) {
                                COLUMNS[0].options = (res.meta.materi || []).map(function(m) {
                                    return {
                                        id: m,
                                        text: m
                                    };
                                });
                                metaOptions.kelas = COLUMNS[0].options;
                                metaOptions.instruktur = res.meta.karyawan_instruktur || [];
                                metaOptions.pcits = res.meta.karyawan_ts || [];
                                metaOptions.asset = res.meta.inventaris || [];

                                COLUMNS.forEach(function(c) {
                                    if (c.key === 'instruktur') c.options = metaOptions.instruktur;
                                    if (c.key === 'pcits') c.options = metaOptions.pcits;
                                    if (c.key === 'asset') c.options = metaOptions.asset;
                                });
                            }
                            render();
                        }
                    })
                    .fail(function(xhr) {
                        if (!opts.silent) {
                            showToast('Gagal memuat data dari server.', {
                                icon: 'bi-exclamation-triangle'
                            });
                        }
                    });
            }

            function showToast(message, opts) {
                opts = opts || {};
                var id = uid('t');
                var actionHtml = opts.actionLabel ?
                    '<button type="button" class="ks-toast-action" data-toast-id="' + id + '">' + esc(opts
                        .actionLabel) + '</button>' :
                    '';
                var $t = $('<div class="ks-toast" data-toast-id="' + id + '"><i class="bi ' + (opts.icon ||
                        'bi-check-circle') + '"></i><span class="ks-toast-msg">' + esc(message) + '</span>' +
                    actionHtml + '<button type="button" class="ks-toast-close" data-toast-id="' + id +
                    '">&times;</button></div>');
                $('#ksToastContainer').append($t);
                if (opts.actionLabel && opts.onAction) toastActions[id] = opts.onAction;
                setTimeout(function() {
                    dismissToast(id);
                }, opts.duration || 4500);
            }

            function dismissToast(id) {
                var $t = $('.ks-toast[data-toast-id="' + id + '"]');
                $t.addClass('ks-toast-out');
                setTimeout(function() {
                    $t.remove();
                }, 180);
                delete toastActions[id];
            }

            $(document).on('click', '.ks-toast-action', function() {
                var id = $(this).data('toast-id');
                if (toastActions[id]) toastActions[id]();
                dismissToast(id);
            });
            $(document).on('click', '.ks-toast-close', function() {
                dismissToast($(this).data('toast-id'));
            });

            function getWeekKeysSorted() {
                return Object.keys(weeksData).sort(function(a, b) {
                    return weeksData[a].start.localeCompare(weeksData[b].start);
                });
            }

            function getPeriodOptions(viewMode) {
                var wk = getWeekKeysSorted();
                var opts = [];
                if (viewMode === 'week') {
                    wk.forEach(function(k) {
                        opts.push({
                            value: k,
                            label: 'Minggu ' + (wk.indexOf(k) + 1) + ' · ' + fmtRangeLabel(weeksData[k]
                                .start, weeksData[k].end),
                            weeks: [k]
                        });
                    });
                } else if (viewMode === 'week2') {
                    for (var i = 0; i < wk.length; i += 2) {
                        var pair = wk.slice(i, i + 2);
                        var lbl = pair.length === 2 ?
                            fmtRangeLabel(weeksData[pair[0]].start, weeksData[pair[1]].end) :
                            fmtRangeLabel(weeksData[pair[0]].start, weeksData[pair[0]].end);
                        opts.push({
                            value: pair.join('+'),
                            label: 'Minggu ' + (i + 1) + '–' + (i + pair.length) + ' · ' + lbl,
                            weeks: pair
                        });
                    }
                } else if (viewMode === 'month') {
                    var byMonth = {};
                    wk.forEach(function(k) {
                        var mk = weeksData[k].start.slice(0, 7);
                        if (!byMonth[mk]) byMonth[mk] = [];
                        byMonth[mk].push(k);
                    });
                    Object.keys(byMonth).sort().forEach(function(mk) {
                        opts.push({
                            value: mk,
                            label: monthLabel(mk),
                            weeks: byMonth[mk]
                        });
                    });
                } else if (viewMode === 'month2') {
                    var byMonth2 = {};
                    wk.forEach(function(k) {
                        var mk = weeksData[k].start.slice(0, 7);
                        if (!byMonth2[mk]) byMonth2[mk] = [];
                        byMonth2[mk].push(k);
                    });
                    var monthKeys = Object.keys(byMonth2).sort();
                    for (var j = 0; j < monthKeys.length; j += 2) {
                        var pairM = monthKeys.slice(j, j + 2);
                        var wks = [];
                        pairM.forEach(function(mk) {
                            wks = wks.concat(byMonth2[mk]);
                        });
                        var lbl2 = pairM.length === 2 ?
                            (monthLabel(pairM[0]).split(' ')[0] + ' – ' + monthLabel(pairM[1])) :
                            monthLabel(pairM[0]);
                        opts.push({
                            value: pairM.join('+'),
                            label: lbl2,
                            weeks: wks
                        });
                    }
                }
                return opts;
            }

            function computeTableContexts() {
                var opts = getPeriodOptions(currentViewMode);
                if (!opts.length) return [];
                if (!currentPeriodValue || !opts.some(function(o) {
                        return o.value === currentPeriodValue;
                    })) {
                    currentPeriodValue = opts[opts.length - 1].value;
                }
                var period = opts.filter(function(o) {
                    return o.value === currentPeriodValue;
                })[0];

                var allWeeks = getWeekKeysSorted();
                var weeks = period.weeks.slice().sort(function(a, b) {
                    return weeksData[a].start.localeCompare(weeksData[b].start);
                });

                return weeks.map(function(wk) {
                    return {
                        id: wk,
                        title: 'Minggu ' + (allWeeks.indexOf(wk) + 1) + ' · ' + fmtRangeLabel(weeksData[wk]
                            .start, weeksData[wk].end),
                        rows: weeksData[wk].rows,
                        comments: weeksData[wk].comments
                    };
                });
            }

            function findRow(rowId) {
                var found = null;
                Object.keys(weeksData).some(function(wk) {
                    var r = (weeksData[wk].rows || []).filter(function(x) {
                        return String(x.id) === String(rowId);
                    })[0];
                    if (r) {
                        found = {
                            week: wk,
                            row: r
                        };
                        return true;
                    }
                    return false;
                });
                return found;
            }

            function sortRows(rowsArr) {
                if (!currentSort) return rowsArr;
                var parts = currentSort.split('_');
                var field = parts[0];
                var dir = parts[1] === 'desc' ? -1 : 1;

                var arr = rowsArr.slice();
                arr.sort(function(a, b) {
                    var va = a[field],
                        vb = b[field];
                    if (field === 'status') {
                        var normVa = STATUS_ORDER[String(va || '').trim()] ? String(va || '').trim() : 
                            Object.keys(STATUS_ORDER).find(function(k) { return k.toLowerCase() === String(va || '').trim().toLowerCase(); });
                        var normVb = STATUS_ORDER[String(vb || '').trim()] ? String(vb || '').trim() : 
                            Object.keys(STATUS_ORDER).find(function(k) { return k.toLowerCase() === String(vb || '').trim().toLowerCase(); });
                        va = STATUS_ORDER[normVa] || 99;
                        vb = STATUS_ORDER[normVb] || 99;
                        return (va - vb) * dir;
                    }
                    if (field === 'dari' || field === 'sampai') {
                        va = va || '9999-99-99';
                        vb = vb || '9999-99-99';
                        return va.localeCompare(vb) * dir;
                    }
                    va = String(va || '').toLowerCase();
                    vb = String(vb || '').toLowerCase();
                    return va.localeCompare(vb) * dir;
                });
                return arr;
            }

            $(document).on('change', '#ksViewMode', function() {
                currentViewMode = $(this).val();
                currentPeriodValue = null;
                editingCell = null;
                openCommentCell = null;
                openInfo = null;
                loadPeriod();
            });
            $(document).on('change', '#ksPeriod', function() {
                currentPeriodValue = $(this).val();
                editingCell = null;
                openCommentCell = null;
                openInfo = null;
                loadPeriod();
            });
            $(document).on('change', '#ksSort', function() {
                currentSort = $(this).val();
                render();
            });

            function loadPeriod() {
                isLoadingPeriod = true;
                render();
                loadInitialData().always(function() {
                    isLoadingPeriod = false;
                    render();
                });
            }

            function renderPeriodOptions() {
                var opts = getPeriodOptions(currentViewMode);
                if (!currentPeriodValue || !opts.some(function(o) {
                        return o.value === currentPeriodValue;
                    })) {
                    currentPeriodValue = opts.length ? opts[opts.length - 1].value : null;
                }
                var html = '';
                opts.forEach(function(o) {
                    html += '<option value="' + esc(o.value) + '"' + (o.value === currentPeriodValue ?
                        ' selected' : '') + '>' + esc(o.label) + '</option>';
                });
                $('#ksPeriod').html(html);
                $('#ksViewMode').val(currentViewMode);
                $('#ksSort').val(currentSort);
            }

            $(document).on('input', '#ksSearch', function() {
                searchQuery = $(this).val().trim().toLowerCase();
                if (searchDebounce) clearTimeout(searchDebounce);
                isLoadingPeriod = true;
                render();
                searchDebounce = setTimeout(function() {
                    loadInitialData().always(function() {
                        isLoadingPeriod = false;
                        render();
                    });
                }, 300);
            });

            function filterRows(rowsArr) {
                if (!searchQuery) return rowsArr;
                return rowsArr.filter(function(r) {
                    var assetStr = Array.isArray(r.asset) ? r.asset.join(' ') : (r.asset || '');
                    var hay = (r.kelas + ' ' + r.instruktur + ' ' + r.ruangan + ' ' + assetStr).toLowerCase();
                    return hay.indexOf(searchQuery) > -1;
                });
            }

            function renderColMenu() {
                var html = '';
                COLUMNS.forEach(function(c) {
                    html += '<label><input type="checkbox" class="ks-col-check" data-col="' + c.key +
                        '" checked> ' + esc(c.label) + '</label>';
                });
                html += '<button type="button" class="ks-col-reset" id="ksColReset">Tampilkan semua kolom</button>';
                $('#ksColMenu').html(html);
            }

            $(document).on('click', '#ksColBtn', function(e) {
                e.stopPropagation();
                colMenuOpen = !colMenuOpen;
                $('#ksColMenu').toggleClass('show', colMenuOpen);
            });
            $(document).on('click', '#ksColMenu', function(e) {
                e.stopPropagation();
            });
            $(document).on('change', '.ks-col-check', function() {
                visibleCols[$(this).data('col')] = this.checked;
                render();
            });
            $(document).on('click', '#ksColReset', function() {
                COLUMNS.forEach(function(c) {
                    visibleCols[c.key] = true;
                });
                $('.ks-col-check').prop('checked', true);
                render();
            });
            $(document).on('click', function(e) {
                if (colMenuOpen && !$(e.target).closest('#ksColMenu, #ksColBtn').length) {
                    colMenuOpen = false;
                    $('#ksColMenu').removeClass('show');
                }
            });
            $(document).on('keydown', function(e) {
                if (e.key !== 'Escape') return;
                if (openCommentCell) {
                    openCommentCell = null;
                    render();
                } else if (openInfo) {
                    openInfo = null;
                    renderInfoPopovers();
                } else if (colMenuOpen) {
                    colMenuOpen = false;
                    $('#ksColMenu').removeClass('show');
                }
            });

            function getWeekDates(startISO) {
                var p = startISO.split('-').map(Number);
                var base = new Date(p[0], p[1] - 1, p[2]);
                var dates = [];
                for (var i = 0; i < 7; i++) {
                    var dt = new Date(base.getFullYear(), base.getMonth(), base.getDate() + i);
                    dates.push(dt.getFullYear() + '-' + pad2(dt.getMonth() + 1) + '-' + pad2(dt.getDate()));
                }
                return dates;
            }

            function computeNeedsForDate(rowsForTable, dateISO) {
                var activeRows = rowsForTable.filter(function(r) {
                    return r.dari && r.sampai && r.dari <= dateISO && r.sampai >= dateISO;
                });
                return computeNeeds(activeRows);
            }

            function computeNeeds(rowsForTable) {
                var groups = {};
                rowsForTable.forEach(function(r) {
                    var gKey = String(r.ruangan || '').trim() + '||' + String(r.instruktur || '').trim();
                    if (!groups[gKey]) groups[gKey] = [];
                    groups[gKey].push(r);
                });

                var laptopTotal = 0;
                Object.keys(groups).forEach(function(gKey) {
                    var laptopRows = groups[gKey].filter(function(r) {
                        return String(r.device || '').trim() === 'Laptop';
                    });
                    if (laptopRows.length) {
                        laptopTotal += Math.max.apply(null, laptopRows.map(function(r) {
                            return Number(r.pax) || 0;
                        }));
                    }
                });

                var pcTotal = 0;
                Object.keys(groups).forEach(function(gKey) {
                    var groupRows = groups[gKey];
                    var pcRows = groupRows.filter(function(r) {
                        return String(r.device || '').trim() === 'PC';
                    });
                    var studentPcNeed = pcRows.length ? Math.max.apply(null, pcRows.map(function(r) {
                        return Number(r.pax) || 0;
                    })) : 0;
                    var instructorPcNeed = groupRows.some(function(r) {
                        return String(r.deviceInstruktur || '').trim() === 'PC';
                    }) ? 1 : 0;
                    pcTotal += studentPcNeed + instructorPcNeed;
                });

                return { laptop: laptopTotal, pc: pcTotal };
            }

            function renderNeedsTable(ctx) {
                var isOpen = !!needsOpenState[ctx.id];
                var dates = getWeekDates(ctx.id);
                var h = [];

                h.push('<div class="ks-needs-wrap mb-2 mt-3' + (isOpen ? ' open' : '') + '">');
                h.push('<div class="ks-needs-header ks-needs-toggle" data-table="' + esc(ctx.id) + '">');
                h.push('<span class="ks-needs-header-title"><i class="bi bi-chevron-right ks-needs-chevron"></i>Kebutuhan Laptop &amp; PC per Hari</span>');
                h.push('<span class="text-muted small">' + (isOpen ? 'Sembunyikan' : 'Klik untuk lihat detail') + '</span>');
                h.push('</div>');

                if (isOpen) {
                    h.push('<div class="ks-needs-body"><table class="table table-sm ks-needs-table mb-0"><thead><tr>');
                    h.push('<th style="width:170px;">Tanggal</th>');
                    h.push('<th><span class="ks-stat">Kebutuhan Laptop<button type="button" class="ks-info-btn ks-info-toggle" data-table="' + esc(ctx.id) + '" data-info="laptop" title="Cara menghitung"><i class="bi bi-question-circle"></i></button></span></th>');
                    h.push('<th><span class="ks-stat">Kebutuhan PC<button type="button" class="ks-info-btn ks-info-toggle" data-table="' + esc(ctx.id) + '" data-info="pc" title="Cara menghitung"><i class="bi bi-question-circle"></i></button></span></th>');
                    h.push('</tr></thead><tbody>');

                    if (isLoadingPeriod) {
                        h.push('<tr><td colspan="3" class="text-muted small py-2">Memuat...</td></tr>');
                    } else {
                        dates.forEach(function(dateISO) {
                            var dayIdx = new Date(dateISO + 'T00:00:00').getDay();
                            var dayLabel = DAY_NAMES[dayIdx] + ', ' + fmtDate(dateISO);
                            var needs = computeNeedsForDate(ctx.rows, dateISO);
                            h.push('<tr><td>' + esc(dayLabel) + '</td><td class="ks-need-val">' + needs.laptop + '</td><td class="ks-need-val">' + needs.pc + '</td></tr>');
                        });
                    }
                    h.push('</tbody></table></div>');
                }

                h.push('</div>');
                return h.join('');
            }

            function renderInfoPopovers() {
                $('.ks-info-popover-wrap').remove();
                if (!openInfo) return;
                var $btn = $('.ks-info-toggle[data-table="' + openInfo.table + '"][data-info="' + openInfo.type + '"]');
                if (!$btn.length) {
                    openInfo = null;
                    return;
                }

                $('body').append(
                    '<div class="ks-info-popover-wrap"><div class="ks-info-backdrop"></div><div class="ks-info-popover">' +
                    INFO_TEXT[openInfo.type] + '</div></div>'
                );

                var $pop = $('.ks-info-popover');
                var rect = $btn[0].getBoundingClientRect();
                var gap = 8, margin = 8, popW = 300;

                var left = rect.left;
                if (left + popW + margin > window.innerWidth) left = Math.max(margin, window.innerWidth - popW - margin);

                var top = rect.bottom + gap;
                var estHeight = $pop.outerHeight() || 90;
                if (top + estHeight + margin > window.innerHeight) {
                    top = Math.max(margin, rect.top - estHeight - gap);
                }

                $pop.css({
                    top: Math.round(top) + 'px',
                    left: Math.round(left) + 'px'
                });
            }

            $(document).on('click', '.ks-info-toggle', function(e) {
                e.stopPropagation();
                var table = $(this).data('table'),
                    type = $(this).data('info');
                openInfo = (openInfo && openInfo.table === table && openInfo.type === type) ? null : {
                    table: table,
                    type: type
                };
                renderInfoPopovers();
            });

            $(document).on('click', '.ks-needs-toggle', function(e) {
                e.stopPropagation();
                var table = $(this).data('table');
                needsOpenState[table] = !needsOpenState[table];
                render();
            });

            $(document).on('click', '.ks-info-backdrop', function() {
                openInfo = null;
                renderInfoPopovers();
            });

            function visibleColumns() {
                return COLUMNS.filter(function(c) {
                    return visibleCols[c.key];
                });
            }

            function getSelect2Display(key, value) {
                if (!value) return '';
                var values = Array.isArray(value) ? value : [value];
                var labels = values.map(function(v) {
                    if (key === 'asset') {
                        var f = (metaOptions.asset || []).find(function(o) {
                            return String(o.id) === String(v);
                        });
                        return f ? f.text : v;
                    }
                    if (key === 'instruktur' || key === 'pcits') {
                        var g = (metaOptions[key] || []).find(function(o) {
                            return o.id === v || o.kode === v;
                        });
                        return g ? g.text : v;
                    }
                    return v;
                });
                return labels.join(', ');
            }

            function render() {
                var scrollPos = saveScrollPositions();

                destroyAllSelect2();
                renderPeriodOptions();
                var contexts = isLoadingPeriod ? [{
                    id: '__loading__',
                    title: 'Memuat...',
                    rows: [],
                    comments: {}
                }] : computeTableContexts();
                var html = '';
                contexts.forEach(function(ctx) {
                    html += renderTableBlock(ctx);
                });
                if (!contexts.length) html =
                    '<div class="text-muted small py-4">Tidak ada data untuk periode ini.</div>';
                $('#ksTablesContainer').html(html);
                initAllSelect2();
                renderCommentPopover();
                renderInfoPopovers();
                justAddedRowId = null;
                justSavedKey = null;
                removingRowId = null;

                requestAnimationFrame(function() {
                    restoreScrollPositions(scrollPos);
                });
            }

            function destroyAllSelect2() {
                var $els = $('.ks-select2-input.select2-hidden-accessible');
                if ($els.length > 0) {
                    try { $els.select2('destroy'); } catch (e) {}
                }
            }

            function initAllSelect2() {
                $('.ks-select2-input').each(function() {
                    var $el = $(this);
                    var field = $el.data('field');
                    var rowId = $el.data('row-id');
                    var isMultiple = (field === 'asset');
                    var options = [];

                    if (field === 'kelas') options = metaOptions.kelas;
                    else if (field === 'instruktur') options = metaOptions.instruktur;
                    else if (field === 'pcits') options = metaOptions.pcits;
                    else if (field === 'asset') options = metaOptions.asset;

                    var currentRaw = $el.attr('data-current');
                    var currentVal = currentRaw;

                    if (isMultiple) {
                        if (typeof currentRaw === 'string' && currentRaw) {
                            try {
                                currentVal = JSON.parse(currentRaw);
                            } catch (e) {
                                currentVal = [currentRaw];
                            }
                        } else if (!currentRaw) {
                            currentVal = [];
                        }
                        if (!Array.isArray(currentVal)) currentVal = [currentVal];
                        currentVal = currentVal.map(String);
                    }

                    $el.empty();
                    if (!isMultiple) $el.append('<option value="">-</option>');

                    options.forEach(function(o) {
                        var id = typeof o === 'object' ? String(o.id) : String(o);
                        var text = typeof o === 'object' ? o.text : o;
                        $el.append('<option value="' + esc(id) + '">' + esc(text) + '</option>');
                    });

                    if (isMultiple) {
                        currentVal.forEach(function(v) {
                            if (!$el.find('option[value="' + esc(v) + '"]').length) {
                                $el.append('<option value="' + esc(v) + '" selected>' + esc(v) +
                                    '</option>');
                            }
                        });
                        $el.val(currentVal);
                    } else if (currentVal) {
                        if (!$el.find('option[value="' + esc(currentVal) + '"]').length) {
                            $el.append('<option value="' + esc(currentVal) + '" selected>' + esc(currentVal) +
                                '</option>');
                        }
                        $el.val(currentVal);
                    }

                    $el.select2({
                        theme: 'bootstrap-5',
                        placeholder: 'Pilih...',
                        allowClear: true,
                        dropdownParent: $el.closest('td'),
                        width: '100%',
                        language: {
                            noResults: function() {
                                return 'Tidak ditemukan';
                            },
                            searching: function() {
                                return 'Mencari...';
                            }
                        }
                    });

                    $el.off('change.ks').on('change.ks', function() {
                        var newVal = isMultiple ? ($(this).val() || []) : ($(this).val() || '');
                        handleSelect2Change(rowId, field, newVal);
                    });

                    $el.on('select2:closing', function() {
                        setTimeout(function() {
                            render();
                        }, 50);
                    });
                });
            }

            function handleSelect2Change(rowId, field, newVal) {
                var ctx = findRow(rowId);
                var oldVal = ctx ? ctx.row[field] : null;
                if (ctx) ctx.row[field] = newVal;
                justSavedKey = ck(rowId, field);

                var payload = {};
                payload[toApiField(field)] = newVal;

                apiCall('PATCH', API_BASE + '/' + rowId, payload)
                    .done(function(res) {
                        if (res.success && res.data) {
                            $.extend(ctx.row, res.data);
                            justSavedKey = ck(rowId, key);
                            render();
                        }
                    })
                    .fail(function(xhr) {
                        if (ctx) ctx.row[key] = oldVal;
                        justSavedKey = null;
                        render();
                        var msg = 'Gagal menyimpan perubahan.';
                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        showToast(msg, { icon: 'bi-exclamation-triangle' });
                    });
            }

            function renderTableBlock(ctx) {
                var cols = visibleColumns();
                var needs = computeNeeds(ctx.rows);
                var filtered = filterRows(ctx.rows);
                var sorted = sortRows(filtered);

                var h = [];
                h.push('<div class="ks-table-block" data-table-id="' + esc(ctx.id) + '">');
                h.push('<div class="ks-table-title"><i class="bi bi-calendar-week me-1"></i>' + esc(ctx.title) + ' <span class="text-muted fw-normal small ms-2">(' + sorted.length + ' kelas)</span></div>');

                h.push(renderNeedsTable(ctx));

                // Card stats-bar terpisah, dengan margin bawah
                h.push('<div class="card shadow-sm ks-card mb-3"><div class="card-body p-3">');
                h.push('<div class="ks-stats-bar d-flex flex-wrap align-items-center gap-4">');
                h.push('<div class="ks-stat"><span class="ks-stat-label">Kebutuhan Laptop</span><span class="ks-stat-value">' + (isLoadingPeriod ? '-' : needs.laptop) + '</span><button type="button" class="ks-info-btn ks-info-toggle" data-table="' + esc(ctx.id) + '" data-info="laptop" title="Cara menghitung"><i class="bi bi-question-circle"></i></button></div>');
                h.push('<div class="ks-stat-divider"></div>');
                h.push('<div class="ks-stat"><span class="ks-stat-label">Kebutuhan PC</span><span class="ks-stat-value">' + (isLoadingPeriod ? '-' : needs.pc) + '</span><button type="button" class="ks-info-btn ks-info-toggle" data-table="' + esc(ctx.id) + '" data-info="pc" title="Cara menghitung"><i class="bi bi-question-circle"></i></button></div>');
                h.push('<div class="ks-stat-note text-muted small"><i class="bi bi-info-circle me-1"></i>Dihitung dari data tabel ini.</div>');
                h.push('</div></div></div>');

                // Card tabel utama, terpisah
                h.push('<div class="card shadow-sm ks-card"><div class="card-body p-0"><div class="ks-scroll">');
                h.push('<table class="table ks-table mb-0"><thead><tr><th class="ks-sticky-col" style="width:42px;">No</th>');
                cols.forEach(function(c) {
                    h.push('<th style="min-width:' + c.width + ';">' + esc(c.label) + '</th>');
                });
                h.push('</tr></thead><tbody>');

                if (isLoadingPeriod) {
                    for (var i = 0; i < 3; i++) {
                        h.push('<tr><td class="ks-idx">&nbsp;</td>');
                        cols.forEach(function() {
                            h.push('<td class="ks-skel-cell"><div class="ks-skel-bar" style="width:' + (55 + Math.random() * 35) + '%;"></div></td>');
                        });
                        h.push('<td></td></tr>');
                    }
                } else if (!sorted.length) {
                    h.push('<tr class="ks-empty-row"><td colspan="' + (cols.length + 2) + '"><i class="bi bi-inbox"></i>' + (searchQuery ? 'Tidak ada kelas yang cocok dengan pencarian "' + esc(searchQuery) + '".' : 'Belum ada kelas di minggu ini.') + '</td></tr>');
                } else {
                    sorted.forEach(function(row, idx) {
                        var rowClasses = [];
                        if (String(row.id) === String(justAddedRowId)) rowClasses.push('ks-row-enter');
                        if (String(row.id) === String(removingRowId)) rowClasses.push('ks-row-leaving');
                        h.push('<tr data-row-id="' + row.id + '" class="' + rowClasses.join(' ') + '"><td class="ks-idx ks-sticky-col">' + (idx + 1) + '</td>');
                        cols.forEach(function(col) {
                            h.push(renderCell(row, col, ctx.comments));
                        });
                        h.push('</tr>');
                    });
                }
                h.push('</tbody></table></div></div></div>');
                h.push('</div>'); // tutup ks-table-block
                return h.join('');
            }

            function renderCell(row, col, commentsDict) {
                var value = row[col.key];
                var comKey = ck(row.id, col.key);
                var cellComments = commentsDict[comKey] || [];
                var isEditing = editingCell && String(editingCell.rowId) === String(row.id) && editingCell.key === col.key;
                var justSaved = justSavedKey === comKey;
                var isLongText = LONG_TEXT_FIELDS.indexOf(col.key) > -1 || col.type === 'textarea';
                var isSelect2 = SELECT2_FIELDS.indexOf(col.key) > -1;

                var h = [];
                h.push('<td data-row-id="' + row.id + '" data-key="' + col.key + '"' + (justSaved ? ' class="ks-just-saved"' : '') + '>');
                h.push('<button type="button" class="ks-cmt-trigger' + (cellComments.length ? ' has-comments' : '') + ' ks-cmt-toggle" title="Komentar"><i class="bi bi-chat-left-text"></i>' + (cellComments.length ? '<span class="ks-cmt-count">' + cellComments.length + '</span>' : '') + '</button>');

                if (col.key === 'status') {
                    var rawStatus = String(value || '').trim();
                    var matchedStatus = col.options.find(function(o) {
                        return o.toLowerCase() === rawStatus.toLowerCase();
                    }) || 'Biru';

                    var st = STATUS_STYLE[matchedStatus] || STATUS_STYLE.Biru;
                    h.push('<div class="ks-cell-select-wrap"><select class="ks-status-select ks-status-select-input" style="background:' + st.bg + ';color:' + st.fg + ';">');
                    col.options.forEach(function(o) {
                        h.push('<option value="' + o + '"' + (o === matchedStatus ? ' selected' : '') + '>' + o + '</option>');
                    });
                    h.push('</select></div>');
                } else if (isSelect2) {
                    var displayLabel = getSelect2Display(col.key, value);
                    h.push('<div class="ks-cell-display ks-select2-display" data-field="' + col.key + '">');
                    if (displayLabel) {
                        h.push(esc(displayLabel));
                    } else {
                        h.push('<span class="empty">Klik untuk pilih</span>');
                    }
                    h.push('</div>');
                } else if (col.type === 'select') {
                    h.push('<div class="ks-cell-select-wrap"><select class="ks-select ks-device-select-input"><option value="">-</option>');
                    col.options.forEach(function(o) {
                        h.push('<option value="' + o + '"' + (o === value ? ' selected' : '') + '>' + o + '</option>');
                    });
                    h.push('</select></div>');
                } else if (isEditing) {
                    if (col.type === 'textarea' || isLongText) {
                        h.push('<div class="ks-edit-wrap"><textarea class="ks-edit-input ks-edit-field">' + esc(value) + '</textarea></div>');
                    } else {
                        var inputType = col.type === 'number' ? 'number' : (col.type === 'date' ? 'date' : 'text');
                        h.push('<div class="ks-edit-wrap"><input type="' + inputType + '" class="ks-edit-input ks-edit-field" value="' + esc(value) + '"></div>');
                    }
                } else {
                    var display = value ? (col.type === 'date' ? fmtDate(value) : esc(value)) : 'Klik untuk isi';
                    var classes = 'ks-cell-display ks-cell-start-edit';
                    if (!value) classes += ' empty';
                    if (isLongText) classes += ' ks-long-text';
                    h.push('<div class="' + classes + '">' + display + '</div>');
                }
                h.push('</td>');
                return h.join('');
            }

            $(document).on('click', '.ks-select2-display', function(e) {
                e.stopPropagation();
                var $td = $(this).closest('td');
                var rowId = $td.data('row-id');
                var field = $(this).data('field');
                var ctx = findRow(rowId);
                var currentVal = ctx ? (ctx.row[field] || '') : '';
                var currentAttr = Array.isArray(currentVal) ? JSON.stringify(currentVal) : currentVal;

                var $wrap = $('<div class="ks-select2-cell"></div>');
                var $select = $('<select class="ks-select2-input" data-field="' + field + '" data-row-id="' +
                    rowId +
                    '"></select>');
                $select.attr('data-current', currentAttr);
                if (field === 'asset') $select.attr('multiple', 'multiple');
                $wrap.append($select);
                $(this).replaceWith($wrap);
                initAllSelect2();
                $select.select2('open');
            });

            function renderCommentPopover() {
                $('.ks-cmt-backdrop, .ks-cmt-popover').remove();
                if (!openCommentCell) return;
                var rowId = openCommentCell.rowId,
                    key = openCommentCell.key;
                var $td = $('[data-row-id="' + rowId + '"][data-key="' + key + '"]');
                if (!$td.length) {
                    openCommentCell = null;
                    return;
                }
                var ctx = findRow(rowId);
                if (!ctx) {
                    openCommentCell = null;
                    return;
                }
                var comments = weeksData[ctx.week].comments;
                var col = null;
                COLUMNS.forEach(function(c) {
                    if (c.key === key) col = c;
                });
                var cellComments = comments[ck(rowId, key)] || [];

                var html = '<div class="ks-cmt-backdrop"></div><div class="ks-cmt-popover">';
                html +=
                    '<div class="d-flex align-items-center justify-content-between mb-2"><span class="text-uppercase text-muted small fw-bold" style="letter-spacing:.04em;">' +
                    esc(col.label) +
                    '</span><button type="button" class="btn-close btn-close-sm ks-cmt-close" style="font-size:10px;"></button></div>';
                html += '<div class="ks-cmt-list">';
                if (!cellComments.length) {
                    html += '<div class="text-muted small fst-italic">Belum ada komentar.</div>';
                } else {
                    cellComments.forEach(function(c) {
                        var initials = c.author ? c.author.charAt(0).toUpperCase() : '?';
                        html += '<div class="ks-cmt-item">' +
                            '<button type="button" class="ks-cmt-del" data-cmt-id="' + c.id + '" title="Hapus komentar">&times;</button>' +
                            '<div class="d-flex gap-2 mb-1">' +
                                '<div style="width:28px;height:28px;border-radius:50%;background:#4F46E5;color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;flex-shrink:0;">' + esc(initials) + '</div>' +
                                '<div style="flex:1;">' +
                                    '<div class="d-flex justify-content-between">' +
                                        '<span class="small fw-semibold" style="color:#374151;">' + esc(c.author || 'Anonymous') + '</span>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="small" style="color:#23272E;white-space:pre-wrap;margin-left:40px;">' + esc(c.text) + '</div>' +
                        '</div>';
                    });
                }
                html +=
                    '</div><div class="d-flex gap-2"><input type="text" class="form-control form-control-sm ks-cmt-input" placeholder="Tulis komentar..."><button type="button" class="btn btn-primary btn-sm ks-cmt-send"><i class="bi bi-send"></i></button></div></div>';
                $('body').append(html);

                var $pop = $('.ks-cmt-popover');
                var rect = $td[0].getBoundingClientRect();
                var gap = 6,
                    margin = 8;
                var popW = 320,
                    popH = 340;
                var left = rect.left;
                if (left + popW + margin > window.innerWidth) left = Math.max(margin, window.innerWidth - popW -
                margin);
                var top = rect.bottom + gap;
                if (top + popH + margin > window.innerHeight) {
                    if (rect.top - popH - gap >= margin) top = rect.top - popH - gap;
                    else top = Math.max(margin, window.innerHeight - popH - margin);
                }
                $pop.css({
                    top: Math.round(top) + 'px',
                    left: Math.round(left) + 'px',
                    width: popW + 'px',
                    height: popH + 'px'
                });
                $pop.find('.ks-cmt-input').trigger('focus');
            }

            $(window).on('resize', function() {
                if (openCommentCell) renderCommentPopover();
                if (openInfo) renderInfoPopovers();
            });

            $(document).on('click', '.ks-cell-start-edit', function() {
                var $td = $(this).closest('td');
                editingCell = {
                    rowId: $td.data('row-id'),
                    key: $td.data('key')
                };
                render();
            });
            $(document).on('blur', '.ks-edit-field', function() {
                commitEdit($(this).val());
            });
            $(document).on('keydown', '.ks-edit-field', function(e) {
                if (e.key === 'Enter' && this.tagName !== 'TEXTAREA') commitEdit($(this).val());
                else if (e.key === 'Escape') {
                    editingCell = null;
                    render();
                }
            });

            function commitEdit(newVal) {
                if (!editingCell) return;
                var rowId = editingCell.rowId,
                    key = editingCell.key;
                var oldVal = null;
                var ctx = findRow(rowId);
                if (ctx) oldVal = ctx.row[key];
                if (ctx) ctx.row[key] = newVal;
                justSavedKey = ck(rowId, key);
                editingCell = null;
                render();

                var payload = {};
                payload[toApiField(key)] = newVal;

                apiCall('PATCH', API_BASE + '/' + rowId, payload)
                    .done(function(res) {
                        if (res.success && res.data) {
                            // sinkronkan row lokal dengan hasil dari server, TANPA reload semua data
                            $.extend(ctx.row, res.data);
                            justSavedKey = ck(rowId, key);
                            render();
                        }
                    })
                    .fail(function(xhr) {
                        if (ctx) ctx.row[key] = oldVal;
                        justSavedKey = null;
                        render();
                        var msg = 'Gagal menyimpan perubahan.';
                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        showToast(msg, { icon: 'bi-exclamation-triangle' });
                    });
            }

            $(document).on('change', '.ks-status-select-input', function() {
                var $td = $(this).closest('td');
                var rowId = $td.data('row-id');
                var newVal = $(this).val();
                var ctx = findRow(rowId);
                var oldVal = ctx ? ctx.row.status : null;
                if (ctx) ctx.row.status = newVal;
                justSavedKey = ck(rowId, 'status');
                render();

                apiCall('PATCH', API_BASE + '/' + rowId, {
                        status: newVal
                    })
                    .done(function(res) {
                        if (res.success) loadInitialData({
                            silent: true
                        });
                    })
                    .fail(function(xhr) {
                        if (ctx) ctx.row.status = oldVal;
                        justSavedKey = null;
                        render();
                        showToast('Gagal mengubah status.', {
                            icon: 'bi-exclamation-triangle'
                        });
                    });
            });

            $(document).on('change', '.ks-device-select-input', function() {
                var $td = $(this).closest('td');
                var rowId = $td.data('row-id'),
                    key = $td.data('key');
                var newVal = $(this).val();
                var ctx = findRow(rowId);
                var oldVal = ctx ? ctx.row[key] : null;
                if (ctx) ctx.row[key] = newVal;
                justSavedKey = ck(rowId, key);
                render();

                var payload = {};
                payload[toApiField(key)] = newVal;

                apiCall('PATCH', API_BASE + '/' + rowId, payload)
                    .done(function(res) {
                        if (res.success && res.data) {
                            $.extend(ctx.row, res.data);
                            justSavedKey = ck(rowId, key);
                            render();
                        }
                    })
                    .fail(function(xhr) {
                        if (ctx) ctx.row[key] = oldVal;
                        justSavedKey = null;
                        render();
                        var msg = 'Gagal menyimpan perubahan.';
                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        showToast(msg, { icon: 'bi-exclamation-triangle' });
                    });
            });

            $(document).on('click', '.ks-cmt-toggle', function(e) {
                e.stopPropagation();
                var $td = $(this).closest('td');
                var target = {
                    rowId: $td.data('row-id'),
                    key: $td.data('key')
                };
                openCommentCell = (openCommentCell && String(openCommentCell.rowId) === String(target.rowId) &&
                    openCommentCell.key === target.key) ? null : target;
                render();
            });
            $(document).on('click', '.ks-cmt-backdrop, .ks-cmt-close', function() {
                openCommentCell = null;
                render();
            });
            $(document).on('click', '.ks-cmt-popover', function(e) {
                e.stopPropagation();
            });

            function addComment() {
                if (!openCommentCell) return;
                var text = $('.ks-cmt-input').val();
                if (!text || !text.trim()) return;
                var ctx = findRow(openCommentCell.rowId);
                if (!ctx) return;
                
                var tempId = uid('c');
                var newCmt = {
                    id: tempId,
                    author: CURRENT_USER,
                    text: text.trim(),
                    time: 'baru saja'
                };
                
                var comments = weeksData[ctx.week].comments;
                var key = ck(openCommentCell.rowId, openCommentCell.key);
                if (!comments[key]) comments[key] = [];
                comments[key].push(newCmt);
                $('.ks-cmt-input').val('');

                apiCall('POST', API_BASE + '/' + openCommentCell.rowId + '/comment', {
                    field: openCommentCell.key,
                    author: CURRENT_USER || 'Anonymous',
                    text: text.trim()
                }).done(function(res) {
                    if (res.success) {
                        weeksData[ctx.week].comments[key] = res.data.comments;
                        render();
                        showToast('Komentar ditambahkan.', { icon: 'bi-chat-left-text' });
                    }
                }).fail(function() {
                    weeksData[ctx.week].comments[key] = (weeksData[ctx.week].comments[key]||[]).filter(c => c.id !== tempId);
                    render();
                    showToast('Gagal mengirim komentar.', { icon: 'bi-exclamation-triangle' });
                });
            }

            $(document).on('click', '.ks-cmt-send', function(e) {
                e.stopPropagation();
                addComment();
            });
            $(document).on('keydown', '.ks-cmt-input', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    addComment();
                }
            });
            $(document).on('click', '.ks-cmt-del', function(e) {
                e.stopPropagation();
                if (!openCommentCell) return;
                var ctx = findRow(openCommentCell.rowId);
                if (!ctx) return;
                var comments = weeksData[ctx.week].comments;
                var key = ck(openCommentCell.rowId, openCommentCell.key);
                var cmtId = $(this).data('cmt-id');
                comments[key] = (comments[key] || []).filter(function(c) {
                    return c.id !== cmtId;
                });
                render();
                apiCall('DELETE', API_BASE + '/' + openCommentCell.rowId + '/comment/' + cmtId + '?field=' +
                        encodeURIComponent(openCommentCell.key))
                    .done(function() {
                        loadInitialData({
                            silent: true
                        });
                    })
                    .fail(function() {
                        loadInitialData({
                            silent: true
                        });
                        showToast('Gagal menghapus komentar.', {
                            icon: 'bi-exclamation-triangle'
                        });
                    });
            });

            $('#ksAddRow').on('click', function() {
                var opts = getPeriodOptions(currentViewMode);
                var period = opts.filter(function(o) {
                    return o.value === currentPeriodValue;
                })[0] || opts[opts.length - 1];
                if (!period) {
                    showToast('Tidak ada periode aktif.', {
                        icon: 'bi-exclamation-circle'
                    });
                    return;
                }
                var targetWeekStart = period.weeks[period.weeks.length - 1];
                var targetWeek = weeksData[targetWeekStart];
                if (!targetWeek) {
                    showToast('Data minggu tidak ditemukan.', {
                        icon: 'bi-exclamation-circle'
                    });
                    return;
                }

                var tempId = uid('r');
                var tempRow = {
                    id: tempId,
                    kelas: '',
                    dari: '',
                    sampai: '',
                    ruangan: '',
                    device: 'Laptop',
                    deviceInstruktur: '',
                    pax: 0,
                    instruktur: '',
                    asset: '',
                    software: '',
                    keterangan: '',
                    pcits: '',
                    status: 'Biru'
                };
                targetWeek.rows.push(tempRow);
                justAddedRowId = tempId;
                render();

                apiCall('POST', API_BASE, {
                    week_start: targetWeek.start,
                    week_end: targetWeek.end,
                    kelas: '',
                    device: 'Laptop',
                    status: 'Biru',
                    pax: 0
                }).done(function(res) {
                    if (res.success && res.data) {
                        targetWeek.rows = targetWeek.rows.filter(function(r) {
                            return String(r.id) !== String(tempId);
                        });
                        targetWeek.rows.push(res.data);
                        justAddedRowId = res.data.id;
                        render();
                        showToast('Baris kelas baru ditambahkan.', {
                            icon: 'bi-plus-circle'
                        });
                    }
                }).fail(function() {
                    targetWeek.rows = targetWeek.rows.filter(function(r) {
                        return String(r.id) !== String(tempId);
                    });
                    render();
                    showToast('Gagal menambahkan kelas baru.', {
                        icon: 'bi-exclamation-triangle'
                    });
                });
            });

            renderColMenu();
            loadInitialData().always(function() {
                render();
            });
        })(jQuery);
    </script>
@endsection
