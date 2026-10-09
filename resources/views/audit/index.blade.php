<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Audit Aplikasi</title>

    <style>
        :root {
            --bg: #f6f7f9;
            --card: #ffffff;
            --text: #1d2330;
            --muted: #6b7280;
            --line: #e5e7eb;
            --primary: #2563eb;
            --error: #dc2626;
            --error-bg: #fee2e2;
            --warning: #b45309;
            --warning-bg: #fef3c7;
            --ok: #15803d;
            --ok-bg: #dcfce7;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #0f1218;
                --card: #181c25;
                --text: #e5e7eb;
                --muted: #9ca3af;
                --line: #2a303c;
                --primary: #60a5fa;
                --error: #f87171;
                --error-bg: #3b1416;
                --warning: #fbbf24;
                --warning-bg: #3a2a0b;
                --ok: #86efac;
                --ok-bg: #14532d;
                --green: #6ee7b7;
                --green-bg: rgba(52, 211, 153, .12);
                --green-line: rgba(52, 211, 153, .55);
                --green-hover: rgba(52, 211, 153, .22);
            }
        }

        * {
            box-sizing: border-box;
        }

        [hidden] {
            display: none !important;
        }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font: 14px/1.5 system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        }

        h1 {
            margin: 0;
            font-size: 22px;
        }

        .muted {
            color: var(--muted);
        }

        .wrap {
            max-width: 1280px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 16px;
        }

        details {
            margin-top: 12px;
        }

        summary {
            cursor: pointer;
            color: var(--muted);
        }

        button {
            font: inherit;
            padding: 7px 12px;
            color: var(--text);
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 8px;
            cursor: pointer;
        }

        button:disabled {
            opacity: .5;
            cursor: not-allowed;
        }

        button.primary {
            color: #fff;
            background: var(--primary);
            border-color: var(--primary);
            transition: none;
        }

        button.primary:hover {
            background: var(--primary);
            border-color: var(--primary);
        }

        input[type=search],
        input[type=text],
        select {
            font: inherit;
            padding: 7px 10px;
            color: var(--text);
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 8px;
        }

        .tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 18px;
            border-bottom: 1px solid var(--line);
        }

        .tab {
            padding: 10px 14px;
            font-weight: 600;
            background: none;
            border: 0;
            border-bottom: 3px solid transparent;
            border-radius: 0;
        }

        .tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .badge {
            display: inline-block;
            min-width: 22px;
            margin-left: 5px;
            padding: 0 7px;
            font-size: 12px;
            text-align: center;
            border-radius: 99px;
        }

        .badge-error {
            background: var(--error-bg);
            color: var(--error);
        }

        .badge-warning {
            background: var(--warning-bg);
            color: var(--warning);
        }

        .badge-ok {
            background: var(--ok-bg);
            color: var(--ok);
        }

        .toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            margin: 14px 0 6px;
        }

        .toolbar .spacer {
            flex: 1;
        }

        .chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .chip {
            padding: 4px 12px;
            border-radius: 99px;
        }

        .chip.on {
            color: #fff;
            background: var(--primary);
            border-color: var(--primary);
        }

        .panel {
            margin-top: 10px;
            overflow: auto;
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 9px 12px;
            text-align: left;
            vertical-align: top;
            border-bottom: 1px solid var(--line);
        }

        tr:last-child td {
            border-bottom: 0;
        }

        th {
            font-size: 12px;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: var(--muted);
        }

        code {
            font: 12.5px ui-monospace, Menlo, Consolas, monospace;
            word-break: break-all;
        }

        .level {
            display: inline-block;
            padding: 2px 8px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 6px;
        }

        .level.ERROR {
            background: var(--error-bg);
            color: var(--error);
        }

        .level.WARNING {
            background: var(--warning-bg);
            color: var(--warning);
        }

        .level.OK {
            background: var(--ok-bg);
            color: var(--ok);
        }

        .actions {
            white-space: nowrap;
        }

        .actions button {
            margin-right: 4px;
            padding: 3px 9px;
            font-size: 12px;
        }

        .pagination {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 10px 12px;
            border-top: 1px solid var(--line);
        }

        .pagination .pages {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 4px;
        }

        .pagination .pages button {
            min-width: 34px;
            padding: 4px 8px;
        }

        .pagination .pages button.current {
            color: #fff;
            background: var(--primary);
            border-color: var(--primary);
        }

        .pagination .pages .gap {
            padding: 0 6px;
            color: var(--muted);
        }

        .pagination .size {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .note {
            margin: 10px 0;
            padding: 10px 14px;
            color: var(--error);
            background: var(--error-bg);
            border: 1px solid var(--error);
            border-radius: 8px;
            white-space: pre-wrap;
        }

        .empty {
            padding: 40px;
            text-align: center;
            color: var(--muted);
        }

        .spinner {
            display: inline-block;
            width: 12px;
            height: 12px;
            margin-right: 6px;
            vertical-align: -2px;
            border: 2px solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        th.sel,
        td.sel {
            width: 32px;
            padding-right: 0;
        }

        input[type=checkbox] {
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .level.SKIP {
            background: var(--line);
            color: var(--muted);
        }

        .modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(0, 0, 0, .55);
        }

        .modal {
            width: min(900px, 100%);
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 12px;
        }

        .modal-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 16px;
            border-bottom: 1px solid var(--line);
        }

        .modal-title {
            font-size: 16px;
            font-weight: 700;
        }

        .modal-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            padding: 0 12px;
            border-bottom: 1px solid var(--line);
        }

        .modal-body {
            padding: 14px 16px;
            overflow: auto;
        }

        .chain {
            margin-bottom: 12px;
            padding: 10px 12px;
            background: var(--bg);
            border-radius: 8px;
            line-height: 1.8;
        }

        .check {
            padding: 10px 0;
            border-bottom: 1px solid var(--line);
        }

        .check:last-child {
            border-bottom: 0;
        }

        .check-title {
            margin-left: 8px;
            font-weight: 600;
        }

        .check-detail {
            margin: 4px 0 0;
            color: var(--muted);
            white-space: pre-wrap;
        }

        .check-loc {
            margin-top: 4px;
        }

        .check-loc button {
            margin-left: 6px;
            padding: 2px 8px;
            font-size: 12px;
        }

        :root {
            --green: #047857;
            --green-bg: #ecfdf5;
            --green-line: #6ee7b7;
            --green-hover: #d1fae5;
        }

        .btn-green {
            color: var(--green);
            background: var(--green-bg);
            border: 1px solid var(--green-line);
            font-weight: 600;
            /* Hapus transisi dan efek hover */
            transition: none;
        }

        .btn-green:hover {
            background: var(--green-bg);
            border-color: var(--green-line);
            color: var(--green);
        }

        .modal-foot {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            border-top: 1px solid var(--line);
        }

        .modal-foot .spacer {
            flex: 1;
        }

        /* PERBAIKI TAMPILAN MODAL SOLUSI */
        .sol-box {
            margin-bottom: 16px;
            padding: 16px;
            border: 1px solid var(--line);
            border-left-width: 4px;
            border-radius: 8px;
            background: var(--card);
        }

        .sol-box.problem {
            border-left-color: var(--error);
            background: var(--error-bg);
        }

        .sol-box.fix {
            border-left-color: #34d399;
            background: #000000 !important;
            color: #ffffff;
        }

        .sol-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 8px;
        }

        .sol-title {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 8px;
            color: var(--text);
        }

        .sol-text {
            color: var(--muted);
            line-height: 1.6;
            white-space: pre-wrap;
            font-size: 13px;
        }

        .sol-steps {
            margin: 12px 0;
            padding-left: 20px;
            color: var(--muted);
        }

        .sol-steps li {
            margin-bottom: 6px;
            line-height: 1.5;
        }

        /* CODE BLOCK DALAM MODAL */
        .sol-box pre,
        .sol-box code {
            background: var(--bg);
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 12px;
            font-family: ui-monospace, Menlo, Consolas, monospace;
            font-size: 12px;
            overflow-x: auto;
            margin: 12px 0;
            display: block;
        }

        .sol-box pre {
            white-space: pre-wrap;
            word-break: break-all;
        }

        /* MODAL BODY SPACING */
        #sol-body {
            padding: 4px 0;
        }

        /* FOOTER MODAL */
        #sol-foot {
            padding-top: 12px;
            border-top: 1px solid var(--line);
        }

        .ctx {
            margin-top: 8px;
            overflow: auto;
            font: 12.5px ui-monospace, Menlo, Consolas, monospace;
            background: var(--bg);
            border-radius: 6px;
        }

        .ctx-row {
            display: flex;
            gap: 10px;
            padding: 1px 10px;
            white-space: pre;
        }

        .ctx-row.hit {
            background: var(--error-bg);
        }

        .ctx-n {
            min-width: 34px;
            color: var(--muted);
            text-align: right;
        }

        .code-wrap {
            position: relative;
        }

        .code-wrap button {
            position: absolute;
            top: 6px;
            right: 6px;
            padding: 2px 8px;
            font-size: 12px;
        }

        pre.code {
            margin: 8px 0 0;
            padding: 10px 12px;
            overflow: auto;
            font: 12.5px ui-monospace, Menlo, Consolas, monospace;
            background: var(--bg);
            border: 1px solid var(--line);
            border-radius: 6px;
            white-space: pre-wrap;
        }

        pre.diff-del {
            background: var(--error-bg);
        }

        pre.diff-add {
            background: var(--ok-bg);
        }
    </style>
</head>

<body>
    <div class="wrap">

        <header>
            <div>
                <h1>Audit Aplikasi</h1>
                <div id="status" class="muted"></div>
            </div>
            <button class="primary" data-sync="all">⟳ Sinkronkan semua</button>
        </header>

        <details>
            <summary>Pengaturan tombol VS Code</summary>
            <p class="muted">
                Kosongkan bila VS Code dan file proyek berada di komputer yang sama dengan browser.
                Jika proyek ada di server lain (SSH), isi nama host SSH-nya sesuai konfigurasi Remote-SSH VS Code.
            </p>
            <input type="text" id="remote-host" placeholder="nama host SSH (opsional)">
        </details>

        <nav class="tabs" id="tabs"></nav>

        <div class="toolbar">
            <div class="chips" id="level-chips"></div>
            <div class="chips" id="category-chips"></div>
            <select id="feature-select"></select>
            <input type="search" id="search" placeholder="Cari file / pesan…">
            <span class="spacer"></span>
            <button class="btn-green" id="bulk-recheck" hidden>Cek ulang terpilih</button>
            <select id="export-scope" title="Cakupan data yang diunduh">
                <option value="filtered">Unduh: sesuai filter</option>
                <option value="tab">Unduh: semua data tab ini</option>
                <option value="all">Unduh: semua tab</option>
            </select>
            <button class="btn-green" id="export-xlsx">⬇ Excel</button>
            <button class="btn-green" id="export-pdf">⬇ PDF</button>
            <button data-sync="tab">⟳ Sinkron tab ini</button>
        </div>

        <div class="muted" id="meta"></div>
        <div class="note" id="note" hidden></div>

        <div class="panel">
            <table>
                <thead>
                    <tr>
                        <th class="sel"><input type="checkbox" id="select-all"
                                title="Pilih semua hasil sesuai filter (semua halaman)"></th>
                        <th>Level</th>
                        <th id="group-column">Kategori</th>
                        <th>Jenis</th>
                        <th>File:Baris</th>
                        <th>Pesan</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="rows"></tbody>
            </table>
            <div class="empty" id="empty" hidden></div>
            <div class="pagination" id="pagination" hidden></div>
        </div>

    </div>

    <div class="modal-backdrop" id="modal" hidden>
        <div class="modal" role="dialog" aria-modal="true">
            <div class="modal-head">
                <div>
                    <div class="modal-title" id="modal-title"></div>
                    <div class="muted" id="modal-sub"></div>
                </div>
                <button id="modal-close">Tutup</button>
            </div>
            <div class="modal-tabs" id="modal-tabs"></div>
            <div class="modal-body" id="modal-body"></div>
        </div>
    </div>

    <div class="modal-backdrop" id="sol-modal" hidden>
        <div class="modal" role="dialog" aria-modal="true">
            <div class="modal-head">
                <div>
                    <div class="modal-title" id="sol-title"></div>
                    <div class="muted" id="sol-sub"></div>
                </div>
                <button id="sol-close">Tutup</button>
            </div>
            <div class="modal-body" id="sol-body"></div>
            <div class="modal-foot" id="sol-foot"></div>
        </div>
    </div>

    <script>
        (function() {
            'use strict';

            const BASE_PATH = @json($base);

            const URLS = {
                data: @json(route('audit.data')),
                status: @json(route('audit.status')),
                sync: @json(route('audit.sync')),
                syncFile: @json(route('audit.sync-file')),
                syncFeature: @json(route('audit.sync-feature')),
                syncFiles: @json(route('audit.sync-files')),
                syncFeatures: @json(route('audit.sync-features')),
                solution: @json(route('audit.solution')),
                solutionFeedback: @json(route('audit.solution-feedback')),
                export: @json(route('audit.export')),
            };

            const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
            const POLL_INTERVAL_MS = 2000;
            const PAGE_SIZES = [10, 25, 50, 100];
            const DEFAULT_PAGE_SIZE = 10;
            const TAB_LABELS = {
                code: 'Audit Code',
                js: 'Audit JS',
                assets: 'Audit Assets',
                features: 'Audit Fitur',
            };
            const STEP_LABELS = {
                code: 'Code',
                assets: 'Assets',
                js: 'JS',
                features: 'Fitur'
            };
            const CATEGORY_LABELS = {
                model: 'Model',
                controller: 'Controller',
                other: 'Lainnya'
            };
            const STATUS_LABELS = {
                pending: 'menunggu',
                running: 'berjalan…',
                done: 'selesai',
                failed: 'gagal'
            };
            const GROUP_LABELS = {
                code: 'Kategori',
                js: 'Halaman',
                assets: 'Fitur',
                features: 'Fitur'
            };

            let data = @json($payload);
            let lastStatus = data.status;
            let pollTimer = null;

            const savedPageSize = Number(localStorage.getItem('audit.pageSize'));

            const state = {
                tab: localStorage.getItem('audit.tab') || 'code',
                level: 'all',
                category: 'all',
                feature: 'all',
                query: '',
                page: 1,
                pageSize: PAGE_SIZES.indexOf(savedPageSize) !== -1 ? savedPageSize : DEFAULT_PAGE_SIZE,
            };
            if (!TAB_LABELS[state.tab]) state.tab = 'code';

            const byId = (id) => document.getElementById(id);

            const selected = new Set();

            const canRecheck = (i) =>
                state.tab === 'code' ? !!i.file && /^app[\\/]/.test(i.file) :
                state.tab === 'features' ? !!(i.route || i.feature) && i.level !== 'OK' :
                false;

            const keyOf = (i) => [state.tab, i.file, i.line, i.type, i.route, i.feature, i.message].join('|');

            const eligibleFiltered = () =>
                issuesOf(state.tab).filter((i) => matchesFilters(i) && canRecheck(i));

            function createEl(tag, className, text) {
                const node = document.createElement(tag);
                if (className) node.className = className;
                if (text != null) node.textContent = text;
                return node;
            }

            const formatDate = (iso) => (iso ? new Date(iso).toLocaleString('id-ID') : '-');
            const issuesOf = (tab) => (data[tab] && data[tab].issues) || [];
            const locationOf = (issue) => issue.file + (issue.line ? ':' + issue.line : '');
            const countLevel = (list, level) => list.filter((i) => i.level === level).length;

            function typeLabelOf(issue) {
                const type = issue.type || '-';
                const fn = issue.function ? String(issue.function) : '';

                return state.tab === 'features' && fn ?
                    fn.replace(/_/g, ' ') + ' · ' + type :
                    type;
            }

            async function fetchJson(url) {
                const response = await fetch(url, {
                    headers: {
                        Accept: 'application/json'
                    }
                });
                return response.json();
            }

            function matchesFilters(issue, skip) {
                if (skip !== 'level' && state.level !== 'all' && issue.level !== state.level) {
                    return false;
                }
                if (skip !== 'category' && state.tab === 'code' && state.category !== 'all' &&
                    (issue.category || 'other') !== state.category) {
                    return false;
                }
                if (skip !== 'feature' && state.tab !== 'code' && state.feature !== 'all' &&
                    (issue.feature || '-') !== state.feature) {
                    return false;
                }
                if (state.query) {
                    const haystack = [issue.file, issue.type, issue.message, issue.feature, issue.function]
                        .join(' ')
                        .toLowerCase();
                    if (haystack.indexOf(state.query) === -1) return false;
                }
                return true;
            }

            function setFilter(patch) {
                Object.assign(state, patch, {
                    page: 1
                });
                render();
            }

            const levelRank = (level) =>
                level === 'ERROR' ? 0 : level === 'WARNING' ? 1 : 2;

            function compareIssues(a, b) {
                return levelRank(a.level) - levelRank(b.level) ||
                    String(a.file).localeCompare(String(b.file)) ||
                    (a.line || 0) - (b.line || 0);
            }

            function renderTabs() {
                const container = byId('tabs');
                container.textContent = '';

                Object.keys(TAB_LABELS).forEach((tab) => {
                    const list = issuesOf(tab);

                    const button = createEl('button', 'tab' + (tab === state.tab ? ' active' : ''), TAB_LABELS[
                        tab]);
                    button.appendChild(createEl('span', 'badge badge-error', countLevel(list, 'ERROR')));
                    if (tab === 'features') {
                        button.appendChild(createEl('span', 'badge badge-ok', countLevel(list, 'OK')));
                    } else {
                        button.appendChild(createEl('span', 'badge badge-warning', countLevel(list,
                            'WARNING')));
                    }
                    button.onclick = () => {
                        selected.clear();
                        Object.assign(state, {
                            tab: tab,
                            level: 'all',
                            category: 'all',
                            feature: 'all',
                            page: 1,
                        });
                        localStorage.setItem('audit.tab', tab);
                        render();
                    };
                    container.appendChild(button);
                });
            }

            function createChip(label, count, isOn, onClick) {
                const chip = createEl('button', 'chip' + (isOn ? ' on' : ''), label + ' (' + count + ')');
                chip.onclick = onClick;
                return chip;
            }

            function renderLevelChips() {
                const container = byId('level-chips');
                container.textContent = '';

                const visible = issuesOf(state.tab).filter((i) => matchesFilters(i, 'level'));
                const options = state.tab === 'features' ? [
                    ['all', 'Semua'],
                    ['ERROR', 'Error'],
                    ['OK', 'Sukses']
                ] : [
                    ['all', 'Semua'],
                    ['ERROR', 'Error'],
                    ['WARNING', 'Warning']
                ];

                options.forEach(([value, label]) => {
                    const count = value === 'all' ?
                        visible.length :
                        countLevel(visible, value);
                    container.appendChild(createChip(label, count, state.level === value, () => {
                        setFilter({
                            level: value
                        });
                    }));
                });
            }

            function renderCategoryChips() {
                const container = byId('category-chips');
                container.textContent = '';
                container.hidden = state.tab !== 'code';
                if (state.tab !== 'code') return;

                const visible = issuesOf('code').filter((i) => matchesFilters(i, 'category'));

                container.appendChild(createChip('Semua masalah', visible.length, state.category === 'all', () => {
                    setFilter({
                        category: 'all'
                    });
                }));

                Object.keys(CATEGORY_LABELS).forEach((key) => {
                    const count = visible.filter((i) => (i.category || 'other') === key).length;
                    container.appendChild(createChip('Masalah ' + CATEGORY_LABELS[key], count, state
                        .category === key, () => {
                            setFilter({
                                category: key
                            });
                        }));
                });
            }

            function renderFeatureSelect() {
                const select = byId('feature-select');
                select.hidden = state.tab === 'code';
                if (state.tab === 'code') return;

                const visible = issuesOf(state.tab).filter((i) => matchesFilters(i, 'feature'));
                const features = Array.from(new Set(issuesOf(state.tab).map((i) => i.feature || '-'))).sort();

                select.textContent = '';

                const allOption = createEl('option', null, 'Semua ' + (state.tab === 'js' ? 'halaman' : 'fitur'));
                allOption.value = 'all';
                select.appendChild(allOption);

                features.forEach((feature) => {
                    const count = visible.filter((i) => (i.feature || '-') === feature).length;
                    const option = createEl('option', null, feature + ' (' + count + ')');
                    option.value = feature;
                    select.appendChild(option);
                });

                select.value = features.indexOf(state.feature) !== -1 ? state.feature : 'all';
            }

            function renderFilters() {
                renderLevelChips();
                renderCategoryChips();
                renderFeatureSelect();
            }

            function openInVsCode(file, line) {
                const isAbsolute = /^([A-Za-z]:[\\/]|\/)/.test(file);
                const base = BASE_PATH.replace(/\\/g, '/').replace(/\/+$/, '');
                const relative = file.replace(/\\/g, '/');

                let absolutePath = isAbsolute ? relative : base + '/' + relative;
                if (absolutePath.charAt(0) !== '/') {
                    absolutePath = '/' + absolutePath;
                }

                const target = encodeURI(absolutePath) + (line ? ':' + line : '');
                const remoteHost = (localStorage.getItem('audit.remote') || '').trim();

                window.location.href = remoteHost ?
                    'vscode://vscode-remote/ssh-remote+' + encodeURIComponent(remoteHost) + target :
                    'vscode://file' + target;
            }

            const LAYERS = [
                ['blade', 'Blade'],
                ['route', 'Route'],
                ['controller', 'Controller'],
                ['model', 'Model & Database'],
                ['request', 'Request'],
            ];
            const CHECK_LEVEL = {
                ok: 'OK',
                error: 'ERROR',
                warn: 'WARNING',
                skip: 'SKIP'
            };
            const CHECK_LABEL = {
                ok: 'Berhasil',
                error: 'Gagal',
                warn: 'Peringatan',
                skip: 'Dilewati'
            };
            const modalState = {
                issue: null,
                layer: 'all'
            };

            function worstStatus(checks) {
                if (checks.some((c) => c.status === 'error')) return 'error';
                if (checks.some((c) => c.status === 'warn')) return 'warn';
                if (checks.some((c) => c.status === 'ok')) return 'ok';
                return 'skip';
            }

            function openDetail(issue) {
                modalState.issue = issue;
                modalState.layer = 'all';
                renderModal();
                byId('modal').hidden = false;
            }

            function closeModal() {
                byId('modal').hidden = true;
            }

            function renderModal() {
                const issue = modalState.issue;
                if (!issue) return;
                const checks = issue.checks || [];

                byId('modal-title').textContent =
                    (issue.feature || '-') + ' · ' + String(issue.function || '').replace(/_/g, ' ');
                byId('modal-sub').textContent = issue.url || '';

                // tab: Ringkasan + per lapisan
                const tabs = byId('modal-tabs');
                tabs.textContent = '';
                const addTab = (key, label, status) => {
                    const tab = createEl('button', 'tab' + (modalState.layer === key ? ' active' : ''), label);
                    if (status) {
                        tab.appendChild(createEl('span', 'level ' + CHECK_LEVEL[status], CHECK_LABEL[status]));
                        tab.lastChild.style.marginLeft = '6px';
                    }
                    tab.onclick = () => {
                        modalState.layer = key;
                        renderModal();
                    };
                    tabs.appendChild(tab);
                };
                addTab('all', 'Ringkasan', worstStatus(checks));
                LAYERS.forEach(([key, label]) => {
                    const list = checks.filter((c) => c.layer === key);
                    addTab(key, label, list.length ? worstStatus(list) : 'skip');
                });

                // isi
                const body = byId('modal-body');
                body.textContent = '';

                if (modalState.layer === 'all') {
                    const chain = createEl('div', 'chain');
                    const parts = [
                        ['Blade', issue.blade],
                        ['Route', issue.route || issue.uri],
                        ['Controller', issue.controller],
                        ['Model', issue.model ? issue.model + (issue.table ? ' (tabel ' + issue.table + ')' : '') :
                            issue.table
                        ],
                    ].filter((p) => p[1]);
                    parts.forEach((p, idx) => {
                        if (idx) chain.appendChild(document.createTextNode('  →  '));
                        chain.appendChild(createEl('b', null, p[0] + ': '));
                        chain.appendChild(createEl('code', null, p[1]));
                    });
                    body.appendChild(chain);
                }

                const shown = modalState.layer === 'all' ?
                    checks :
                    checks.filter((c) => c.layer === modalState.layer);

                if (!shown.length) {
                    body.appendChild(createEl('div', 'empty', 'Tidak ada pemeriksaan untuk lapisan ini.'));
                    return;
                }

                const layerLabel = Object.fromEntries(LAYERS);
                shown.forEach((c) => {
                    const item = createEl('div', 'check');
                    const head = createEl('div');
                    head.appendChild(createEl('span', 'level ' + CHECK_LEVEL[c.status], CHECK_LABEL[c.status]));
                    head.appendChild(createEl('span', 'check-title',
                        (modalState.layer === 'all' ? '[' + layerLabel[c.layer] + '] ' : '') + c.title));
                    item.appendChild(head);

                    if (c.detail) item.appendChild(createEl('div', 'check-detail', c.detail));

                    if (c.file) {
                        const loc = createEl('div', 'check-loc');
                        loc.appendChild(createEl('code', null, c.file + (c.line ? ':' + c.line : '')));
                        const open = createEl('button', null, 'Buka di VS Code');
                        open.onclick = () => openInVsCode(c.file, c.line);
                        loc.appendChild(open);
                        item.appendChild(loc);
                    }
                    body.appendChild(item);
                });
            }

            function copyToClipboard(text, button) {
                const onCopied = () => {
                    button.textContent = 'Tersalin';
                    setTimeout(() => {
                        button.textContent = 'Salin';
                    }, 1200);
                };
                const fallback = () => window.prompt('Salin path:', text);

                if (navigator.clipboard) {
                    navigator.clipboard.writeText(text).then(onCopied, fallback);
                } else {
                    fallback();
                }
            }

            function postJson(url, payload) {
                return fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });
            }

            function createRow(issue) {
                const row = createEl('tr');
                const addCell = (className) => {
                    const cell = createEl('td', className);
                    row.appendChild(cell);
                    return cell;
                };

                const selCell = addCell('sel');
                if (canRecheck(issue)) {
                    const key = keyOf(issue);
                    const box = createEl('input');
                    box.type = 'checkbox';
                    box.checked = selected.has(key);
                    box.onchange = () => {
                        if (box.checked) selected.add(key);
                        else selected.delete(key);
                        renderBulk();
                    };
                    selCell.appendChild(box);
                }

                addCell().appendChild(createEl('span', 'level ' + issue.level, issue.level));
                addCell().textContent = state.tab === 'code' ?
                    (CATEGORY_LABELS[issue.category] || CATEGORY_LABELS.other) :
                    (issue.feature || '-');

                addCell().textContent = typeLabelOf(issue);

                const fileCell = addCell();
                if (issue.file) {
                    fileCell.appendChild(createEl('code', null, locationOf(issue)));
                } else {
                    fileCell.appendChild(createEl('span', 'muted', issue.url || '-'));
                }

                addCell().textContent = issue.message;

                const actionCell = addCell('actions');
                if (issue.file) {
                    const openButton = createEl('button', null, 'Buka di VS Code');
                    openButton.onclick = () => openInVsCode(issue.file, issue.line);
                    actionCell.appendChild(openButton);

                    const copyButton = createEl('button', null, 'Salin');
                    copyButton.onclick = () => copyToClipboard(locationOf(issue), copyButton);
                    actionCell.appendChild(copyButton);
                }

                if (state.tab === 'code' && canRecheck(issue)) {
                    const recheck = createEl('button', 'btn-green', 'Cek ulang');
                    recheck.title = 'Audit ulang hanya file ini';
                    recheck.onclick = () => recheckFile(issue, recheck);
                    actionCell.appendChild(recheck);
                }
                if (state.tab === 'features' && (issue.route || issue.feature)) {
                    const recheck = createEl('button', 'btn-green', 'Cek ulang');
                    recheck.onclick = () => recheckFeature(issue, recheck);
                    actionCell.appendChild(recheck);
                }

                if (issue.level !== 'OK') {
                    const fix = createEl('button', 'btn-green', 'Solusi');
                    fix.title = 'Lihat masalah dan rekomendasi perbaikan';
                    fix.onclick = () => openSolution(issue, fix);
                    actionCell.insertBefore(fix, actionCell.firstChild);
                }

                if (state.tab === 'features' && issue.checks) {
                    const detail = createEl('button', 'primary', 'Detail');
                    detail.onclick = () => openDetail(issue);
                    actionCell.insertBefore(detail, actionCell.firstChild);
                }

                return row;
            }

            function renderBulk() {
                const supported = state.tab === 'code' || state.tab === 'features';
                const button = byId('bulk-recheck');
                const all = byId('select-all');

                button.hidden = !supported || selected.size === 0;
                button.textContent = 'Cek ulang terpilih (' + selected.size + ')';
                all.hidden = !supported;
                if (!supported) return;

                const list = eligibleFiltered();
                const picked = list.filter((i) => selected.has(keyOf(i))).length;
                all.checked = list.length > 0 && picked === list.length;
                all.indeterminate = picked > 0 && picked < list.length;
            }

            async function bulkRecheck() {
                const tab = state.tab;
                const items = issuesOf(tab)
                    .filter((i) => selected.has(keyOf(i)))
                    .map((i) => ({
                        file: i.file || '',
                        route: i.route || '',
                        feature: i.feature || '',
                        line: i.line,
                        type: i.type,
                        message: i.message,
                    }));
                if (!items.length) return;

                const button = byId('bulk-recheck');
                button.disabled = true;
                button.textContent = 'Mengecek ' + items.length + ' item…';

                try {
                    const response = await postJson(tab === 'code' ? URLS.syncFiles : URLS.syncFeatures, {
                        items: items
                    });
                    const result = await response.json().catch(() => ({}));
                    if (!response.ok || !result.ok) {
                        window.alert(result.message || 'Gagal audit ulang');
                        return;
                    }

                    if (tab === 'code' && result.code) data.code = result.code;
                    else if (tab === 'features' && result.features) data.features = result.features;
                    else data = await fetchJson(URLS.data);

                    const fixed = (result.results || []).filter((r) => r.fixed).length;
                    selected.clear();
                    render();
                    window.alert(fixed + ' dari ' + items.length + ' masalah sudah tidak ditemukan, ' +
                        (items.length - fixed) + ' masih ada.');
                } catch (e) {
                    console.error(e);
                    window.alert('Terjadi kesalahan: ' + (e && e.message ? e.message : 'gagal menghubungi server'));
                } finally {
                    button.disabled = false;
                    renderBulk();
                }
            }

            function reportFixed(issue) {
                try {
                    const rule = (issue.solution && issue.solution.rule) || issue.rule || issue.type || '';
                    if (!rule) return;
                    postJson(URLS.solutionFeedback, { rule: String(rule), verdict: 'fixed' }).catch(() => {});
                } catch (e) {
                    console.error(e);
                }
            }

            async function recheckFile(issue, button) {
                const prev = button.textContent;
                button.disabled = true;
                button.textContent = 'Mengecek…';
                try {
                    const response = await postJson(URLS.syncFile, {
                        file: issue.file,
                        line: issue.line,
                        type: issue.type,
                        message: issue.message,
                    });
                    const result = await response.json().catch(() => ({}));
                    if (!response.ok || !result.ok) {
                        window.alert(result.message || 'Gagal audit ulang file');
                        return;
                    }
                    if (result.code) data.code = result.code;
                    else data = await fetchJson(URLS.data);
                    render();
                    if (result.fixed) reportFixed(issue);
                    window.alert(result.fixed ?
                        'Masalah ini sudah tidak ditemukan.' :
                        'Masih ada masalah di file ini.');
                } catch (e) {
                    console.error(e);
                    window.alert('Terjadi kesalahan: ' + (e && e.message ? e.message : 'gagal menghubungi server'));
                } finally {
                    button.disabled = false;
                    button.textContent = prev;
                }
            }

            async function recheckFeature(issue, button) {
                const prev = button.textContent;
                button.disabled = true;
                button.textContent = 'Mengecek…';
                try {
                    const response = await postJson(URLS.syncFeature, {
                        route: issue.route || '',
                        feature: issue.feature || '',
                        line: issue.line,
                        type: issue.type,
                        message: issue.message,
                    });
                    const result = await response.json().catch(() => ({}));
                    if (!response.ok || !result.ok) {
                        window.alert(result.message || 'Gagal audit ulang fitur');
                        return;
                    }
                    if (result.features) data.features = result.features;
                    else data = await fetchJson(URLS.data);
                    render();
                    if (result.fixed) reportFixed(issue);
                    window.alert(result.fixed ?
                        'Masalah ini sudah tidak ditemukan.' :
                        'Masih ada masalah pada fungsi/fitur ini.');
                } catch (e) {
                    console.error(e);
                    window.alert('Terjadi kesalahan: ' + (e && e.message ? e.message : 'gagal menghubungi server'));
                } finally {
                    button.disabled = false;
                    button.textContent = prev;
                }
            }

            function renderRows() {
                const tab = state.tab;
                const body = byId('rows');
                body.textContent = '';

                const list = issuesOf(tab).filter((i) => matchesFilters(i)).sort(compareIssues);
                const totalPages = Math.max(1, Math.ceil(list.length / state.pageSize));
                state.page = Math.min(Math.max(state.page, 1), totalPages);

                const start = (state.page - 1) * state.pageSize;
                list.slice(start, start + state.pageSize).forEach((issue) => body.appendChild(createRow(issue)));

                byId('group-column').textContent = GROUP_LABELS[tab];
                renderEmptyAndMeta(tab, list.length);
                renderPagination(list.length, totalPages);
                renderBulk();
            }

            function pageNumbers(current, last) {
                const pages = [];
                for (let page = 1; page <= last; page++) {
                    if (page === 1 || page === last || Math.abs(page - current) <= 1) {
                        pages.push(page);
                    } else if (pages[pages.length - 1] !== '…') {
                        pages.push('…');
                    }
                }
                return pages;
            }

            function goToPage(page) {
                state.page = page;
                renderRows();
            }

            function renderPagination(total, totalPages) {
                const bar = byId('pagination');
                bar.textContent = '';
                bar.hidden = total === 0;
                if (total === 0) return;

                const from = (state.page - 1) * state.pageSize + 1;
                const to = Math.min(state.page * state.pageSize, total);
                bar.appendChild(createEl('div', 'muted', 'Menampilkan ' + from + '–' + to + ' dari ' + total));

                const pages = createEl('div', 'pages');
                const addPageButton = (label, page, disabled, isCurrent) => {
                    const button = createEl('button', isCurrent ? 'current' : null, label);
                    button.disabled = disabled;
                    button.onclick = () => goToPage(page);
                    pages.appendChild(button);
                };

                addPageButton('‹', state.page - 1, state.page === 1, false);
                pageNumbers(state.page, totalPages).forEach((page) => {
                    if (page === '…') {
                        pages.appendChild(createEl('span', 'gap', page));
                    } else {
                        addPageButton(String(page), page, false, page === state.page);
                    }
                });
                addPageButton('›', state.page + 1, state.page === totalPages, false);
                bar.appendChild(pages);

                const sizeLabel = createEl('label', 'size muted', 'Per halaman');
                const sizeSelect = createEl('select');
                PAGE_SIZES.forEach((size) => {
                    const option = createEl('option', null, size);
                    option.value = size;
                    sizeSelect.appendChild(option);
                });
                sizeSelect.value = state.pageSize;
                sizeSelect.onchange = () => {
                    state.pageSize = Number(sizeSelect.value);
                    state.page = 1;
                    localStorage.setItem('audit.pageSize', state.pageSize);
                    renderRows();
                };
                sizeLabel.appendChild(sizeSelect);
                bar.appendChild(sizeLabel);
            }

            function renderEmptyAndMeta(tab, visibleCount) {
                const report = data[tab];
                const empty = byId('empty');
                empty.hidden = false;

                if (!report) {
                    empty.textContent = 'Belum ada data untuk tab ini. Klik "Sinkron tab ini" untuk menjalankan audit.';
                } else if (!visibleCount) {
                    empty.textContent = issuesOf(tab).length ?
                        'Tidak ada temuan yang cocok dengan filter.' :
                        'Tidak ada masalah ditemukan.';
                } else {
                    empty.hidden = true;
                }

                if (!report) {
                    byId('meta').textContent = '';
                    return;
                }

                const summary = report.summary || {};
                const parts = [
                    'Terakhir disinkronkan: ' + formatDate(report.generated_at),
                    (summary.errors || 0) + ' error',
                ];
                if (tab === 'features') {
                    parts.push((summary.ok || 0) + ' sukses');
                } else {
                    parts.push((summary.warnings || 0) + ' warning');
                }
                parts.push('sesuai filter: ' + visibleCount);
                if (report.partial) {
                    parts.push('(partial: ' + (report.touched || []).length + ' file)');
                }
                byId('meta').textContent = parts.join(' · ');
            }

            function renderStatus() {
                const status = lastStatus || {};
                const isRunning = status.state === 'running';
                const steps = status.steps || {};

                const summary = Object.keys(steps)
                    .map((key) => (STEP_LABELS[key] || key) + ': ' + (STATUS_LABELS[steps[key].state] || steps[key]
                        .state))
                    .join(' · ');

                let text = 'Belum pernah disinkronkan';
                if (isRunning) {
                    text = 'Sinkronisasi berjalan — ' + summary;
                } else if (status.finished_at) {
                    text = 'Sinkronisasi terakhir: ' + formatDate(status.finished_at) + (summary ? ' — ' + summary :
                        '');
                }

                const box = byId('status');
                box.textContent = '';
                if (isRunning) box.appendChild(createEl('span', 'spinner'));
                box.appendChild(document.createTextNode(text));

                document.querySelectorAll('[data-sync]').forEach((button) => {
                    button.disabled = isRunning;
                });

                const step = steps[state.tab];
                const note = byId('note');
                note.hidden = !(step && step.state === 'failed');
                if (!note.hidden) {
                    note.textContent = 'Sinkronisasi ' + TAB_LABELS[state.tab] + ' gagal:\n' + (step.message || '');
                }
            }

            function hasSolution(issue) {
                return !!(issue.solution || issue.fix || issue.rekomendasi);
            }

            async function openSolution(issue, button) {
                const prevText = button.textContent;
                button.disabled = true;
                button.textContent = 'Memuat…';

                try {
                    let sol = issue.solution;
                    
                    if (!sol) {
                        const res = await fetch(URLS.solution, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': CSRF_TOKEN,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                tab: state.tab,       // ← DITAMBAHKAN
                                issue: issue          // ← DITAMBAHKAN (mengirim seluruh objek issue)
                            })
                        });

                        if (!res.ok) {
                            const err = await res.json().catch(() => ({}));
                            throw new Error(err.message || 'Gagal memuat solusi dari server');
                        }

                        const result = await res.json();
                        sol = result.solution;
                    }

                    byId('sol-title').textContent = issue.type || 'Rekomendasi Perbaikan';
                    byId('sol-sub').textContent = (issue.file || '') + (issue.line ? ':' + issue.line : '');
                    
                    const body = byId('sol-body');
                    body.textContent = '';
                    const foot = byId('sol-foot');
                    foot.textContent = '';

                    if (sol) {
                        if (sol.problem) body.appendChild(createSolBox('problem', 'Masalah', sol.problem));
                        if (sol.fix) body.appendChild(createSolBox('fix', 'Perbaikan', sol.fix));
                        
                    if (sol.fix && sol.fix.diff) {
                        const d = createEl('div', 'sol-box');
                        d.appendChild(createEl('div', 'sol-label', 'Perubahan yang disarankan'));
                        d.appendChild(createEl('pre', 'code diff-del', '- ' + sol.fix.diff.before));
                        d.appendChild(createEl('pre', 'code diff-add', '+ ' + sol.fix.diff.after));
                        body.appendChild(d);
                    }

                    if (sol.context && sol.context.length) {
                        const c = createEl('div', 'sol-box');
                        c.appendChild(createEl('div', 'sol-label', 'Kode di lokasi ini'));
                        const wrap = createEl('div', 'ctx');
                        sol.context.forEach((r) => {
                            const row = createEl('div', 'ctx-row' + (r.hit ? ' hit' : ''));
                            row.appendChild(createEl('span', 'ctx-n', r.n));
                            row.appendChild(createEl('span', null, r.t));
                            wrap.appendChild(row);
                        });
                        c.appendChild(wrap);
                        body.appendChild(c);
                    }

                    (sol.extras || []).forEach((x) => {
                        const b = createEl('div', 'sol-box');
                        b.appendChild(createEl('div', 'sol-title', x.title));
                        b.appendChild(createEl('pre', 'code', x.detail));
                        body.appendChild(b);
                    });

                    if (sol.insights && sol.insights.length) {
                        const b = createEl('div', 'sol-box');
                        b.appendChild(createEl('div', 'sol-label', 'Wawasan dari riwayat audit'));
                        const ul = createEl('ul', 'sol-steps');
                        sol.insights.forEach((t) => ul.appendChild(createEl('li', null, t)));
                        b.appendChild(ul);
                        body.appendChild(b);
                    }
                        const closeBtn = createEl('button', 'primary', 'Tutup');
                        closeBtn.onclick = closeSolution;
                        foot.appendChild(closeBtn);
                    } else {
                        body.appendChild(createEl('div', 'empty', 'Tidak ada solusi otomatis yang tersedia untuk masalah ini.'));
                        const closeBtn = createEl('button', 'primary', 'Tutup');
                        closeBtn.onclick = closeSolution;
                        foot.appendChild(closeBtn);
                    }

                    byId('sol-modal').hidden = false;
                } catch (e) {
                    console.error(e);
                    window.alert('Gagal memuat solusi: ' + e.message);
                } finally {
                    button.disabled = false;
                    button.textContent = prevText;
                }
            }

            function closeSolution() {
                byId('sol-modal').hidden = true;
            }

            function createSolBox(type, label, content) {
                const box = createEl('div', 'sol-box ' + type);
                box.appendChild(createEl('div', 'sol-label', label));
                
                if (typeof content === 'object' && content !== null) {
                    if (content.title) {
                        box.appendChild(createEl('div', 'sol-title', content.title));
                    }
                    if (content.detail) {
                        box.appendChild(createEl('div', 'sol-text', content.detail));
                    }
                    
                    if (content.steps && Array.isArray(content.steps)) {
                        const steps = createEl('ol', 'sol-steps');
                        content.steps.forEach(step => {
                            steps.appendChild(createEl('li', null, step));
                        });
                        box.appendChild(steps);
                    }
                    
                    if (content.code) {
                        const codeBlock = createEl('pre', 'code');
                        codeBlock.textContent = content.code;
                        box.appendChild(codeBlock);
                    }
                }
                // Jika content adalah string biasa
                else if (typeof content === 'string') {
                    box.appendChild(createEl('div', 'sol-text', content));
                }
                // Jika content adalah array
                else if (Array.isArray(content)) {
                    const steps = createEl('ol', 'sol-steps');
                    content.forEach(step => {
                        steps.appendChild(createEl('li', null, step));
                    });
                    box.appendChild(steps);
                }
                
                return box;
            }

            async function exportReport(format, button) {
                const params = new URLSearchParams({
                    format: format,
                    tab: state.tab,
                    scope: byId('export-scope').value,
                    level: state.level,
                    category: state.category,
                    feature: state.feature,
                    q: state.query,
                });

                const prev = button.textContent;
                button.disabled = true;
                button.textContent = 'Menyiapkan…';

                try {
                    const response = await fetch(URLS.export + '?' + params.toString());
                    if (!response.ok) {
                        const err = await response.json().catch(() => ({}));
                        window.alert(err.message || 'Gagal membuat file (' + response.status + ')');
                        return;
                    }

                    const disposition = response.headers.get('Content-Disposition') || '';
                    const match = /filename="?([^";]+)"?/.exec(disposition);
                    const filename = match ? match[1] : 'audit-' + state.tab + '.' + format;

                    const url = URL.createObjectURL(await response.blob());
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    URL.revokeObjectURL(url);
                } catch (e) {
                    console.error(e);
                    window.alert('Terjadi kesalahan: ' + (e && e.message ? e.message : 'gagal menghubungi server'));
                } finally {
                    button.disabled = false;
                    button.textContent = prev;
                }
            }

            function render() {
                const valid = new Set(issuesOf(state.tab).map(keyOf));
                selected.forEach((k) => {
                    if (!valid.has(k)) selected.delete(k);
                });
                renderTabs();
                renderFilters();
                renderRows();
                renderStatus();
            }

            byId('modal-close').onclick = closeModal;
            byId('sol-close').onclick = closeSolution;
            byId('sol-modal').onclick = (event) => {
                if (event.target === byId('sol-modal')) closeSolution();
            };
            byId('modal').onclick = (event) => {
                if (event.target === byId('modal')) closeModal();
            };
            document.addEventListener('keydown', (event) => { if (event.key === 'Escape') { closeModal(); closeSolution(); } });

            byId('select-all').onchange = (event) => {
                eligibleFiltered().forEach((i) => {
                    if (event.target.checked) selected.add(keyOf(i));
                    else selected.delete(keyOf(i));
                });
                renderRows();
            };
            byId('export-xlsx').onclick = (e) => exportReport('xlsx', e.currentTarget);
            byId('export-pdf').onclick = (e) => exportReport('pdf', e.currentTarget);
            byId('bulk-recheck').onclick = bulkRecheck;

            byId('remote-host').value = localStorage.getItem('audit.remote') || '';

            async function startSync(only) {
                try {
                    const response = await postJson(URLS.sync, {
                        only: only
                    });

                    if (!response.ok && response.status !== 409) {
                        const error = await response.json().catch(() => ({}));
                        window.alert(error.message || 'Gagal memulai sinkronisasi (' + response.status + ')');
                        return;
                    }
                } catch (e) {
                    console.error(e);
                    window.alert('Terjadi kesalahan: ' + (e && e.message ? e.message : 'gagal menghubungi server'));
                }

                startPolling();
            }

            function startPolling() {
                clearInterval(pollTimer);
                pollTimer = setInterval(checkStatus, POLL_INTERVAL_MS);
                checkStatus();
            }

            async function checkStatus() {
                let status;
                try {
                    status = await fetchJson(URLS.status);
                } catch (e) {
                    return;
                }

                lastStatus = status;
                if (status.state === 'running') {
                    renderStatus();
                    return;
                }

                clearInterval(pollTimer);
                pollTimer = null;
                try {
                    data = await fetchJson(URLS.data);
                } catch (e) {}
                render();
            }

            document.querySelectorAll('[data-sync]').forEach((button) => {
                button.onclick = () => startSync(button.dataset.sync === 'tab' ? state.tab : 'all');
            });

            byId('search').oninput = (event) => {
                state.query = event.target.value.trim().toLowerCase();
                state.page = 1;
                renderFilters();
                renderRows();
            };

            byId('feature-select').onchange = (event) => {
                setFilter({
                    feature: event.target.value
                });
            };

            byId('remote-host').oninput = (event) => localStorage.setItem('audit.remote', event.target.value);

            render();
            if (lastStatus && lastStatus.state === 'running') startPolling();
        })();
    </script>
</body>

</html>
