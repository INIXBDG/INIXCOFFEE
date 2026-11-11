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
            }
        }

        * { box-sizing: border-box; }
        [hidden] { display: none !important; }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font: 14px/1.5 system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        }

        h1 { margin: 0; font-size: 22px; }
        .muted { color: var(--muted); }

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

        details { margin-top: 12px; }
        summary { cursor: pointer; color: var(--muted); }

        button {
            font: inherit;
            padding: 7px 12px;
            color: var(--text);
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 8px;
            cursor: pointer;
        }
        button:hover { border-color: var(--primary); }
        button:disabled { opacity: .5; cursor: not-allowed; }

        button.primary {
            color: #fff;
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
        .badge-error   { background: var(--error-bg);   color: var(--error); }
        .badge-warning { background: var(--warning-bg); color: var(--warning); }
        .badge-ok      { background: var(--ok-bg);      color: var(--ok); }

        .toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            margin: 14px 0 6px;
        }
        .toolbar .spacer { flex: 1; }

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

        table { width: 100%; border-collapse: collapse; }

        th, td {
            padding: 9px 12px;
            text-align: left;
            vertical-align: top;
            border-bottom: 1px solid var(--line);
        }
        tr:last-child td { border-bottom: 0; }

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
        .level.ERROR   { background: var(--error-bg);   color: var(--error); }
        .level.WARNING { background: var(--warning-bg); color: var(--warning); }
        .level.OK      { background: var(--ok-bg);      color: var(--ok); }

        .actions { white-space: nowrap; }
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
        @keyframes spin { to { transform: rotate(360deg); } }
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
            <button data-sync="tab">⟳ Sinkron tab ini</button>
        </div>

        <div class="muted" id="meta"></div>
        <div class="note" id="note" hidden></div>

        <div class="panel">
            <table>
                <thead>
                    <tr>
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

    <script>
    (function () {
        'use strict';

        const BASE_PATH = @json($base);

        const URLS = {
            data: @json(route('audit.data')),
            status: @json(route('audit.status')),
            sync: @json(route('audit.sync')),
            syncFile: @json(route('audit.sync-file')),
            syncFeature: @json(route('audit.sync-feature')),
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
        const STEP_LABELS = { code: 'Code', assets: 'Assets', js: 'JS', features: 'Fitur' };
        const CATEGORY_LABELS = { model: 'Model', controller: 'Controller', other: 'Lainnya' };
        const STATUS_LABELS   = { pending: 'menunggu', running: 'berjalan…', done: 'selesai', failed: 'gagal' };
        const GROUP_LABELS = { code: 'Kategori', js: 'Halaman', assets: 'Fitur', features: 'Fitur' };

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
            if (state.tab === 'features' && issue.function) {
                return issue.function.replace(/_/g, ' ') + ' · ' + issue.type;
            }
            return issue.type;
        }

        async function fetchJson(url) {
            const response = await fetch(url, { headers: { Accept: 'application/json' } });
            return response.json();
        }

        function matchesFilters(issue, skip) {
            if (skip !== 'level' && state.level !== 'all' && issue.level !== state.level) {
                return false;
            }
            if (skip !== 'category' && state.tab === 'code' && state.category !== 'all'
                && (issue.category || 'other') !== state.category) {
                return false;
            }
            if (skip !== 'feature' && state.tab !== 'code' && state.feature !== 'all'
                && (issue.feature || '-') !== state.feature) {
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
            Object.assign(state, patch, { page: 1 });
            render();
        }

        const levelRank = (level) =>
            level === 'ERROR' ? 0 : level === 'WARNING' ? 1 : 2;

        function compareIssues(a, b) {
            return levelRank(a.level) - levelRank(b.level)
                || String(a.file).localeCompare(String(b.file))
                || (a.line || 0) - (b.line || 0);
        }

        function renderTabs() {
            const container = byId('tabs');
            container.textContent = '';

            Object.keys(TAB_LABELS).forEach((tab) => {
                const list = issuesOf(tab);

                const button = createEl('button', 'tab' + (tab === state.tab ? ' active' : ''), TAB_LABELS[tab]);
                button.appendChild(createEl('span', 'badge badge-error', countLevel(list, 'ERROR')));
                if (tab === 'features') {
                    button.appendChild(createEl('span', 'badge badge-ok', countLevel(list, 'OK')));
                } else {
                    button.appendChild(createEl('span', 'badge badge-warning', countLevel(list, 'WARNING')));
                }
                button.onclick = () => {
                    Object.assign(state, { tab: tab, level: 'all', category: 'all', feature: 'all', page: 1 });
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
            const options = state.tab === 'features'
                ? [['all', 'Semua'], ['ERROR', 'Error'], ['OK', 'Sukses']]
                : [['all', 'Semua'], ['ERROR', 'Error'], ['WARNING', 'Warning']];

            options.forEach(([value, label]) => {
                const count = value === 'all'
                    ? visible.length
                    : countLevel(visible, value);
                container.appendChild(createChip(label, count, state.level === value, () => {
                    setFilter({ level: value });
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
                setFilter({ category: 'all' });
            }));

            Object.keys(CATEGORY_LABELS).forEach((key) => {
                const count = visible.filter((i) => (i.category || 'other') === key).length;
                container.appendChild(createChip('Masalah ' + CATEGORY_LABELS[key], count, state.category === key, () => {
                    setFilter({ category: key });
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
            const absolutePath = file.charAt(0) === '/'
                ? file
                : BASE_PATH.replace(/\/+$/, '') + '/' + file;
            const target = encodeURI(absolutePath) + (line ? ':' + line : '');
            const remoteHost = (localStorage.getItem('audit.remote') || '').trim();

            window.location.href = remoteHost
                ? 'vscode://vscode-remote/ssh-remote+' + encodeURIComponent(remoteHost) + target
                : 'vscode://file' + target;
        }

        function copyToClipboard(text, button) {
            const onCopied = () => {
                button.textContent = 'Tersalin';
                setTimeout(() => { button.textContent = 'Salin'; }, 1200);
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

            addCell().appendChild(createEl('span', 'level ' + issue.level, issue.level));
            addCell().textContent = state.tab === 'code'
                ? (CATEGORY_LABELS[issue.category] || CATEGORY_LABELS.other)
                : (issue.feature || '-');

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

            if (issue.file && state.tab === 'code') {
                const recheck = createEl('button', null, 'Cek ulang');
                recheck.title = 'Audit ulang hanya file ini';
                recheck.onclick = () => recheckFile(issue, recheck);
                actionCell.appendChild(recheck);
            }
            if (state.tab === 'features' && (issue.route || issue.feature)) {
                const recheck = createEl('button', null, 'Cek ulang');
                recheck.onclick = () => recheckFeature(issue, recheck);
                actionCell.appendChild(recheck);
            }
            return row;
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
                window.alert(result.fixed
                    ? 'Masalah ini sudah tidak ditemukan.'
                    : 'Masih ada masalah di file ini.');
            } catch (e) {
                window.alert('Gagal menghubungi server');
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
                window.alert(result.fixed
                    ? 'Masalah ini sudah tidak ditemukan.'
                    : 'Masih ada masalah pada fungsi/fitur ini.');
            } catch (e) {
                window.alert('Gagal menghubungi server');
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
                empty.textContent = issuesOf(tab).length
                    ? 'Tidak ada temuan yang cocok dengan filter.'
                    : 'Tidak ada masalah ditemukan.';
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
                .map((key) => (STEP_LABELS[key] || key) + ': ' + (STATUS_LABELS[steps[key].state] || steps[key].state))
                .join(' · ');

            let text = 'Belum pernah disinkronkan';
            if (isRunning) {
                text = 'Sinkronisasi berjalan — ' + summary;
            } else if (status.finished_at) {
                text = 'Sinkronisasi terakhir: ' + formatDate(status.finished_at) + (summary ? ' — ' + summary : '');
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

        function render() {
            renderTabs();
            renderFilters();
            renderRows();
            renderStatus();
        }

        async function startSync(only) {
            try {
                const response = await postJson(URLS.sync, { only: only });

                if (!response.ok && response.status !== 409) {
                    const error = await response.json().catch(() => ({}));
                    window.alert(error.message || 'Gagal memulai sinkronisasi (' + response.status + ')');
                    return;
                }
            } catch (e) {
                window.alert('Gagal menghubungi server');
                return;
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
            setFilter({ feature: event.target.value });
        };

        byId('remote-host').value = localStorage.getItem('audit.remote') || '';
        byId('remote-host').oninput = (event) => localStorage.setItem('audit.remote', event.target.value);

        render();
        if (lastStatus && lastStatus.state === 'running') startPolling();
    })();
    </script>
</body>
</html>