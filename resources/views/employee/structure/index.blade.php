@extends('layouts.app')
@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0"><i class="ri-org-chart me-2"></i>Struktur Organisasi</h4>
            <small class="text-muted">Visualisasi hierarki jabatan perusahaan</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-primary btn-sm" id="btnFitScreen">
                <i class="fa-solid fa-left-right me-2"></i> Sesuaikan
            </button>
        </div>
    </div>

    <div class="card shadow-sm" id="mainCard">
        <div class="card-body p-0 position-relative">
            <div class="d-flex justify-content-between align-items-center flex-wrap p-4">
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <select class="form-select form-select-sm" id="levelFilter" style="width:140px">
                        <option value="all">Semua Level</option>
                        <option value="1">Level 1</option>
                        <option value="2">Level 2</option>
                        <option value="3">Level 3</option>
                        <option value="4">Level 4+</option>
                    </select>
                    <div class="vr mx-1"></div>
                    <button class="btn btn-sm btn-outline-secondary" id="btnZoomOut" title="Zoom Out"><i
                            class="fa-solid fa-minus"></i></button>
                    <span class="badge bg-light text-dark" id="zoomLevel"
                        style="min-width:48px;text-align:center">100%</span>
                    <button class="btn btn-sm btn-outline-secondary" id="btnZoomIn" title="Zoom In"><i
                            class="fa-solid fa-plus"></i></button>
                    <div class="vr mx-1"></div>
                    <button class="btn btn-sm btn-outline-dark" id="btnFullscreen">
                        <i class="fa-solid fa-maximize"></i> Perbesar
                    </button>
                </div>
            </div>
            <div class="px-4 pb-2 d-none" id="chartTabs"></div>
            <div id="exportWrapper" style="position: relative; background: #f8fafc;">
                <div class="structure-header" id="structureHeader">
                    <input type="text" id="chartTitle" value="" placeholder="Struktur Organisasi" readonly>
                </div>
                <div id="structureContainer" class="structure-container">
                    <div id="structureChart" class="structure-chart"></div>
                </div>
            </div>
            <div id="miniMap" class="mini-map d-none d-md-block">
                <canvas id="miniMapCanvas" width="160" height="90"></canvas>
                <div id="miniMapViewport"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalKaryawan" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ri-group-line me-2"></i><span id="modalJabatanTitle">—</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalKaryawanBody"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetail" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ri-information-line me-2"></i>Detail Jabatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalDetailBody"></div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .stat-card {
            border: none;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .07);
        }

        .stat-label {
            font-size: .72rem;
            color: #64748b;
            font-weight: 500;
        }

        .stat-value {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.2;
        }

        .structure-container {
            position: relative;
            width: 100%;
            height: 680px;
            max-height: 72vh;
            overflow: auto;
            background:
                radial-gradient(circle, #cbd5e1 1px, transparent 1px) 0 0 / 28px 28px,
                #f8fafc;
            border-radius: 0 0 8px 8px;
            cursor: grab;
            user-select: none;
        }

        .structure-container.panning {
            cursor: grabbing;
        }

        .structure-chart {
            position: relative;
            display: inline-block;
            padding: 0;
            min-width: 100%;
            min-height: 100%;
            transform-origin: 0 0;
        }

        .org-node {
            position: absolute;
            width: 210px;
            height: 186px;
            display: flex;
            flex-direction: column;
            background: #fff;
            border: 2px solid #3b82f6;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .08);
            user-select: none;
            z-index: 10;
            transition: border-color .15s, box-shadow .15s, opacity .15s;
        }

        .org-node .node-header {
            flex: 1;
            min-height: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 10px 12px 6px;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
        }

        .org-title {
            font-weight: 700;
            font-size: .83rem;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            word-break: break-word;
        }

        .org-node .node-actions {
            display: flex;
            gap: 8px;
            justify-content: center;
            padding: 8px 12px;
            background: #f8fafc;
            border-radius: 0 0 10px 10px;
        }

        .org-node .node-actions button {
            flex: 1;
            font-size: .68rem;
            padding: 3px 4px;
            border-radius: 6px;
        }

        .org-node:hover {
            border-color: #1d4ed8;
            box-shadow: 0 6px 18px rgba(59, 130, 246, .22);
        }

        .org-node.highlighted {
            border-color: #f59e0b !important;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, .25);
        }

        .org-node.dimmed {
            opacity: .25;
        }

        .org-node.level-hidden {
            display: none !important;
        }

        .org-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: #fff;
            font-weight: 700;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            overflow: hidden;
        }

        .org-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .org-title {
            font-weight: 700;
            font-size: .83rem;
            line-height: 1.3;
        }

        .org-divisi {
            font-size: .7rem;
            color: #64748b;
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .org-count {
            display: inline-block;
            margin-top: 6px;
            font-size: .7rem;
            background: #eff6ff;
            color: #1d4ed8;
            border-radius: 999px;
            padding: 2px 10px;
            font-weight: 600;
        }

        #connectorSvg {
            position: absolute;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: 1;
            overflow: visible;
        }

        .mini-map {
            position: absolute;
            bottom: 16px;
            right: 16px;
            width: 160px;
            height: 90px;
            background: rgba(255, 255, 255, .9);
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            overflow: hidden;
            z-index: 50;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .1);
        }

        #miniMapCanvas {
            display: block;
        }

        #miniMapViewport {
            position: absolute;
            border: 2px solid #3b82f6;
            background: rgba(59, 130, 246, .08);
            pointer-events: none;
            top: 0;
            left: 0;
        }

        .browser-fullscreen {
            position: fixed !important;
            inset: 8px;
            z-index: 99999;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, .22);
        }

.browser-fullscreen {
    display: flex;
    flex-direction: column;
}

.browser-fullscreen .card-body {
    flex: 1 1 auto;
    min-height: 0;
    display: flex;
    flex-direction: column;
}

.browser-fullscreen .card-body > :not(#exportWrapper) {
    flex: 0 0 auto;
}

.browser-fullscreen #exportWrapper {
    flex: 1 1 auto;
    min-height: 0;
    display: flex;
    flex-direction: column;
}

.browser-fullscreen .structure-header {
    flex: 0 0 auto;
}

.browser-fullscreen .structure-container {
    flex: 1 1 auto;
    min-height: 0;
    height: auto !important;
    max-height: none !important;
}

        .karyawan-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 10px;
            background: #f8fafc;
            margin-bottom: 8px;
            transition: background .12s;
        }

        .karyawan-card:hover {
            background: #eff6ff;
        }

        .karyawan-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: #fff;
            font-weight: 700;
            font-size: .95rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
        }

        .karyawan-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .karyawan-name {
            font-weight: 600;
            font-size: .9rem;
        }

        .karyawan-meta {
            font-size: .76rem;
            color: #64748b;
        }

        .status-badge {
            font-size: .68rem;
            padding: 3px 9px;
            border-radius: 999px;
            font-weight: 600;
        }

        .detail-row {
            display: flex;
            gap: 12px;
            margin-bottom: 10px;
            align-items: flex-start;
        }

        .detail-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #eff6ff;
            color: #3b82f6;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .detail-label {
            font-size: .72rem;
            color: #64748b;
        }

        .detail-val {
            font-size: .88rem;
            font-weight: 600;
            color: #1e293b;
        }

        .structure-container::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .structure-container::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .structure-container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .structure-container::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        @media (max-width: 768px) {
            .structure-container {
                height: 480px;
                max-height: 60vh;
            }
        }

        .structure-header {
            text-align: center;
            padding: 20px 20px 10px;
            background: #fff;
        }

        .structure-header input {
            border: none;
            background: transparent;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            width: 100%;
            max-width: 500px;
            padding: 5px 10px;
            outline: none;
        }

        .org-avatar,
        .karyawan-avatar {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8) !important;
        }

        .org-avatar img,
        .karyawan-avatar img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            border-radius: 50% !important;
        }

        /* FIX: clip canvas agar tidak tembus card */
        #mainCard {
            overflow: hidden;
            /* tambahkan ini */
        }

        #mainCard .card-body {
            overflow: hidden;
            /* tambahkan ini */
            border-radius: inherit;
        }

        #exportWrapper {
            overflow: hidden;
            /* tambahkan ini */
            border-radius: 0 0 8px 8px;
        }

        .structure-container {
            /* pastikan border-radius bottom ada */
            border-radius: 0 0 8px 8px;
            overflow: hidden;
            /* ubah dari 'auto' ke 'hidden' */
        }

        .chart-tab {
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #475569;
            font-size: .8rem;
            font-weight: 500;
            padding: .35rem .85rem;
            border-radius: 8px;
            margin-right: .35rem;
        }

        .chart-tab.active {
            background: #eff6ff;
            border-color: #3b82f6;
            color: #1d4ed8;
            font-weight: 600;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const NS = 'http://www.w3.org/2000/svg';
            const NODE_W = 210;
            const NODE_H = 186;
            const GAP_X = 40;
            const GAP_Y = 90;
            const PAD = 60;
            const ZOOM_MIN = 0.1;
            const ZOOM_MAX = 3.0;
            const ZOOM_STEP = 0.12;
            const STORE_KEY = 'employee_structure_active_chart';

            const apiTree = "{{ route('employee.structure.api.tree') }}";
            const container = document.getElementById('structureContainer');
            const chart = document.getElementById('structureChart');

            const state = {
                charts: [],
                chartId: null,
                tree: [],
                flat: [],
                byId: new Map(),
                layout: new Map(),
                W: 0,
                H: 0,
            };

            let tx = 0, ty = 0, tz = 1.0;

            const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
            }[c]));

            // ---------- Pan / zoom ----------
            const applyTransform = () => {
                chart.style.transform = `matrix(${tz},0,0,${tz},${tx},${ty})`;
                chart.style.transformOrigin = '0 0';
                document.getElementById('zoomLevel').textContent = Math.round(tz * 100) + '%';
                requestAnimationFrame(drawMiniMap);
            };

            const zoomAt = (newZoom, cx, cy) => {
                newZoom = Math.min(ZOOM_MAX, Math.max(ZOOM_MIN, newZoom));
                newZoom = Math.round(newZoom * 100) / 100;
                const wx = (cx - tx) / tz;
                const wy = (cy - ty) / tz;
                tz = newZoom;
                tx = cx - wx * tz;
                ty = cy - wy * tz;
                applyTransform();
            };

            const zoomCenter = (z) => zoomAt(z, container.clientWidth / 2, container.clientHeight / 2);

            let panStart = null;

            container.addEventListener('pointerdown', (e) => {
                if (e.target.closest('.org-node')) return;
                e.preventDefault();
                container.setPointerCapture(e.pointerId);
                panStart = { px: e.clientX, py: e.clientY, tx, ty };
                container.classList.add('panning');
            });

            container.addEventListener('pointermove', (e) => {
                if (!panStart) return;
                tx = panStart.tx + (e.clientX - panStart.px);
                ty = panStart.ty + (e.clientY - panStart.py);
                applyTransform();
            });

            const endPan = () => {
                panStart = null;
                container.classList.remove('panning');
            };
            container.addEventListener('pointerup', endPan);
            container.addEventListener('pointercancel', endPan);

            container.addEventListener('wheel', (e) => {
                e.preventDefault();
                const rect = container.getBoundingClientRect();
                zoomAt(tz + (e.deltaY < 0 ? ZOOM_STEP : -ZOOM_STEP), e.clientX - rect.left, e.clientY - rect.top);
            }, { passive: false });

            let lastPinchDist = null;

            container.addEventListener('touchstart', (e) => {
                if (e.touches.length === 2) {
                    lastPinchDist = Math.hypot(
                        e.touches[0].clientX - e.touches[1].clientX,
                        e.touches[0].clientY - e.touches[1].clientY
                    );
                }
            }, { passive: true });

            container.addEventListener('touchmove', (e) => {
                if (e.touches.length !== 2 || !lastPinchDist) return;
                e.preventDefault();
                const dist = Math.hypot(
                    e.touches[0].clientX - e.touches[1].clientX,
                    e.touches[0].clientY - e.touches[1].clientY
                );
                const rect = container.getBoundingClientRect();
                const cx = (e.touches[0].clientX + e.touches[1].clientX) / 2 - rect.left;
                const cy = (e.touches[0].clientY + e.touches[1].clientY) / 2 - rect.top;
                zoomAt(tz * (dist / lastPinchDist), cx, cy);
                lastPinchDist = dist;
            }, { passive: false });

            container.addEventListener('touchend', () => { lastPinchDist = null; });

            document.getElementById('btnZoomIn').addEventListener('click', () => zoomCenter(tz + ZOOM_STEP));
            document.getElementById('btnZoomOut').addEventListener('click', () => zoomCenter(tz - ZOOM_STEP));

            function fitToScreen() {
                if (!state.flat.length) return;
                const cw = container.clientWidth - 80;
                const ch = container.clientHeight - 80;
                tz = Math.max(ZOOM_MIN, Math.min(cw / state.W, ch / state.H, 1));
                tx = (container.clientWidth - state.W * tz) / 2;
                ty = 40;
                applyTransform();
            }

            document.getElementById('btnFitScreen').addEventListener('click', fitToScreen);

            document.addEventListener('keydown', (e) => {
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
                if ((e.ctrlKey || e.metaKey) && (e.key === '=' || e.key === '+')) { e.preventDefault(); zoomCenter(tz + ZOOM_STEP); }
                if ((e.ctrlKey || e.metaKey) && e.key === '-') { e.preventDefault(); zoomCenter(tz - ZOOM_STEP); }
                if ((e.ctrlKey || e.metaKey) && e.key === '0') { e.preventDefault(); fitToScreen(); }
            });

            // ---------- Level filter ----------
            function applyLevelFilter() {
                const val = document.getElementById('levelFilter').value;
                document.querySelectorAll('.org-node').forEach((node) => {
                    node.classList.remove('level-hidden');
                    if (val === 'all') return;
                    const lvl = parseInt(node.dataset.level, 10);
                    if (val === '4') {
                        if (lvl < 4) node.classList.add('level-hidden');
                    } else if (String(lvl) !== val) {
                        node.classList.add('level-hidden');
                    }
                });
            }
            document.getElementById('levelFilter').addEventListener('change', applyLevelFilter);

            // ---------- MiniMap ----------
            function drawMiniMap() {
                const canvas = document.getElementById('miniMapCanvas');
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                if (!state.W || !state.H) return;

                const sx = canvas.width / state.W;
                const sy = canvas.height / state.H;

                ctx.fillStyle = '#93c5fd';
                state.layout.forEach((p) => ctx.fillRect(p.x * sx, p.y * sy, NODE_W * sx, NODE_H * sy));

                const vp = document.getElementById('miniMapViewport');
                vp.style.left = (-tx / tz * sx) + 'px';
                vp.style.top = (-ty / tz * sy) + 'px';
                vp.style.width = (container.clientWidth / tz * sx) + 'px';
                vp.style.height = (container.clientHeight / tz * sy) + 'px';
            }

            // ---------- Fullscreen ----------

            let fitTimer;
            new ResizeObserver(() => {
                clearTimeout(fitTimer);
                fitTimer = setTimeout(fitToScreen, 120);
            }).observe(container);

            const mainCard = document.getElementById('mainCard');
            let isFullscreen = false;
            document.getElementById('btnFullscreen').addEventListener('click', () => {
                isFullscreen = !isFullscreen;
                mainCard.classList.toggle('browser-fullscreen', isFullscreen);
                document.body.style.overflow = isFullscreen ? 'hidden' : '';
                document.getElementById('btnFullscreen').innerHTML = isFullscreen
                    ? '<i class="fa-solid fa-minimize"></i> Kecilkan'
                    : '<i class="fa-solid fa-maximize"></i> Perbesar';
                setTimeout(fitToScreen, 200);
            });

            let resizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(fitToScreen, 250);
            });

            // ---------- Data & layout (sama dengan halaman admin) ----------
            const indexTree = () => {
                state.flat = [];
                state.byId = new Map();
                const walk = (nodes) => nodes.forEach((n) => {
                    state.flat.push(n);
                    state.byId.set(n.id, n);
                    walk(n.children || []);
                });
                walk(state.tree);
            };

            const parentIdsOf = (n) => {
                const ids = [n.parent_id, ...(n.additional_parents || [])]
                    .filter((v) => v !== null && v !== undefined)
                    .map(Number)
                    .filter((v) => v !== n.id && state.byId.has(v));
                return [...new Set(ids)];
            };

            const computeLayout = () => {
                state.layout = new Map();
                const widths = new Map();
                const depths = new Map();
                const visiting = new Set();
                const usable = new Map();
                const seen = new Set();
                const kids = new Map();
                const roots = [];
                const order = (a, b) => (a.sort_order - b.sort_order) || (a.id - b.id);

                const depthOf = (n) => {
                    if (depths.has(n.id)) return depths.get(n.id);
                    if (visiting.has(n.id)) return 0;
                    visiting.add(n.id);
                    const ps = parentIdsOf(n);
                    const d = ps.length ? 1 + Math.max(...ps.map((p) => depthOf(state.byId.get(p)))) : 0;
                    visiting.delete(n.id);
                    depths.set(n.id, d);
                    return d;
                };

                state.flat.forEach((n) => depthOf(n));

                state.flat.forEach((n) => {
                    const ps = parentIdsOf(n);
                    if (!ps.length) { roots.push(n); return; }
                    if (ps.length > 1) return;
                    if (!kids.has(ps[0])) kids.set(ps[0], []);
                    kids.get(ps[0]).push(n);
                });

                roots.sort(order);
                kids.forEach((list) => list.sort(order));

                const measure = (n) => {
                    seen.add(n.id);
                    const list = [];
                    let total = 0;
                    (kids.get(n.id) || []).forEach((c) => {
                        if (seen.has(c.id)) return;
                        total += (list.length ? GAP_X : 0) + measure(c);
                        list.push(c);
                    });
                    usable.set(n.id, list);
                    const w = Math.max(NODE_W, total);
                    widths.set(n.id, w);
                    return w;
                };

                const place = (n, left) => {
                    const sw = widths.get(n.id);
                    const depth = depths.get(n.id) || 0;
                    state.layout.set(n.id, {
                        x: left + (sw - NODE_W) / 2,
                        y: depth * (NODE_H + GAP_Y),
                        depth,
                    });
                    const list = usable.get(n.id) || [];
                    const childrenW = list.reduce((a, c, i) => a + widths.get(c.id) + (i ? GAP_X : 0), 0);
                    let cx = left + (sw - childrenW) / 2;
                    list.forEach((c) => {
                        place(c, cx);
                        cx += widths.get(c.id) + GAP_X;
                    });
                };

                let cursor = 0;
                const placeRoot = (root) => {
                    if (seen.has(root.id)) return;
                    measure(root);
                    place(root, cursor);
                    cursor += widths.get(root.id) + GAP_X;
                };

                roots.forEach(placeRoot);

                const collect = (n, out) => {
                    out.push(n.id);
                    (usable.get(n.id) || []).forEach((c) => collect(c, out));
                    return out;
                };

                const pending = state.flat
                    .filter((n) => parentIdsOf(n).length > 1)
                    .sort((a, b) => depths.get(a.id) - depths.get(b.id));

                let progress = true;
                while (pending.length && progress) {
                    progress = false;
                    for (let i = 0; i < pending.length; i++) {
                        const n = pending[i];
                        const parentPos = parentIdsOf(n).map((p) => state.layout.get(p));
                        if (parentPos.some((p) => !p)) continue;

                        const centers = parentPos.map((p) => p.x + NODE_W / 2);
                        const target = (Math.min(...centers) + Math.max(...centers)) / 2;
                        const sw = measure(n);
                        place(n, target - sw / 2);

                        const ids = new Set(collect(n, []));
                        const mine = [...ids].map((id) => state.layout.get(id));

                        for (let guard = 0; guard < 300; guard++) {
                            let shift = 0;
                            state.layout.forEach((b, bid) => {
                                if (ids.has(bid)) return;
                                mine.forEach((a) => {
                                    if (a.depth !== b.depth) return;
                                    if (a.x < b.x + NODE_W + GAP_X && b.x < a.x + NODE_W + GAP_X) {
                                        shift = Math.max(shift, b.x + NODE_W + GAP_X - a.x);
                                    }
                                });
                            });
                            if (!shift) break;
                            mine.forEach((a) => { a.x += shift; });
                        }

                        pending.splice(i, 1);
                        i--;
                        progress = true;
                    }
                }

                state.flat.forEach(placeRoot);

                if (!state.layout.size) {
                    state.W = 0;
                    state.H = 0;
                    return;
                }

                // posisi manual dari halaman admin (offset dari posisi otomatis)
                state.flat.forEach((n) => {
                    const p = state.layout.get(n.id);
                    if (!p) return;
                    p.x += Number(n.pos_x) || 0;
                    p.y += Number(n.pos_y) || 0;
                });

                let minX = Infinity, maxX = -Infinity, minY = Infinity, maxY = -Infinity;
                state.layout.forEach((p) => {
                    minX = Math.min(minX, p.x);
                    maxX = Math.max(maxX, p.x + NODE_W);
                    minY = Math.min(minY, p.y);
                    maxY = Math.max(maxY, p.y + NODE_H);
                });

                const shiftX = PAD - minX;
                const shiftY = PAD - minY;
                state.layout.forEach((p) => {
                    p.x += shiftX;
                    p.y += shiftY;
                });

                state.W = Math.ceil(maxX - minX + PAD * 2);
                state.H = Math.ceil(maxY - minY + PAD * 2);
            };

            // ---------- Garis penghubung ----------
            const connectorPath = (x1, y1, x2, y2) => {
                if (x1 === x2) return `M ${x1} ${y1} V ${y2}`;
                const my = y1 + (y2 - y1) / 2;
                const dir = Math.sign(x2 - x1);
                const sy1 = Math.sign(my - y1) || 1;
                const sy2 = Math.sign(y2 - my) || 1;
                const r = Math.max(0, Math.min(10, Math.abs(x2 - x1) / 2, Math.abs(my - y1), Math.abs(y2 - my)));
                return `M ${x1} ${y1} V ${my - sy1 * r} Q ${x1} ${my} ${x1 + dir * r} ${my} H ${x2 - dir * r} Q ${x2} ${my} ${x2} ${my + sy2 * r} V ${y2}`;
            };

            const linkPath = (pp, cp) => {
                const pcx = pp.x + NODE_W / 2;
                const ccx = cp.x + NODE_W / 2;
                const pBottom = pp.y + NODE_H;
                const cBottom = cp.y + NODE_H;

                if (cp.y >= pBottom + 16) return connectorPath(pcx, pBottom, ccx, cp.y);
                if (cBottom + 16 <= pp.y) return connectorPath(pcx, pp.y, ccx, cBottom);

                const right = ccx >= pcx;
                const x1 = right ? pp.x + NODE_W : pp.x;
                const y1 = pp.y + NODE_H / 2;
                const x2 = right ? cp.x : cp.x + NODE_W;
                const y2 = cp.y + NODE_H / 2;
                if (right ? x2 > x1 + 8 : x2 < x1 - 8) {
                    const mx = (x1 + x2) / 2;
                    return `M ${x1} ${y1} H ${mx} V ${y2} H ${x2}`;
                }
                return `M ${pcx} ${y1} L ${ccx} ${y2}`;
            };

            const drawConnectors = (svg) => {
                state.flat.forEach((n) => {
                    const cp = state.layout.get(n.id);
                    if (!cp) return;
                    parentIdsOf(n).forEach((pid) => {
                        const pp = state.layout.get(pid);
                        if (!pp) return;
                        const p = document.createElementNS(NS, 'path');
                        p.setAttribute('d', linkPath(pp, cp));
                        p.setAttribute('fill', 'none');
                        p.setAttribute('stroke', '#93c5fd');
                        p.setAttribute('stroke-width', '2');
                        p.setAttribute('stroke-linecap', 'round');
                        p.setAttribute('stroke-linejoin', 'round');
                        svg.appendChild(p);
                    });
                });
            };

            // ---------- Render ----------
            const createNode = (item, p) => {
                const el = document.createElement('div');
                el.className = 'org-node';
                el.dataset.id = item.id;
                el.dataset.level = p.depth + 1;
                el.style.left = p.x + 'px';
                el.style.top = p.y + 'px';
                el.style.height = NODE_H + 'px';

                const initial = (item.jabatan || '?').charAt(0).toUpperCase();
                const count = item.karyawans?.length ?? 0;
                const divisi = item.divisi ? `<div class="org-divisi">${esc(item.divisi)}</div>` : '';

                el.innerHTML = `
                    <div class="node-header">
                        <div class="org-avatar">${esc(initial)}</div>
                        <div class="org-title" title="${esc(item.jabatan)}">${esc(item.jabatan)}</div>
                        ${divisi}
                        <span class="org-count">${count} karyawan</span>
                    </div>
                    <div class="node-actions">
                        <button class="btn btn-outline-primary btn-action-karyawan" title="Lihat Karyawan">
                            <i class="fa-solid fa-user-tie"></i>
                        </button>
                        <button class="btn btn-outline-info btn-action-detail" title="Detail">
                            <i class="fa-solid fa-circle-info"></i>
                        </button>
                    </div>`;

                el.querySelector('.btn-action-karyawan').addEventListener('click', (e) => {
                    e.stopPropagation();
                    openKaryawanModal(item);
                });
                el.querySelector('.btn-action-detail').addEventListener('click', (e) => {
                    e.stopPropagation();
                    openDetailModal(item);
                });
                return el;
            };

            const renderChart = () => {
                chart.innerHTML = '';

                if (!state.flat.length) {
                    chart.style.width = '';
                    chart.style.height = '';
                    chart.innerHTML = `<div class="text-center py-5 text-muted" style="min-width:400px">
                        <i class="fa-solid fa-sitemap fs-1 d-block mb-2"></i>Belum ada data.</div>`;
                    drawMiniMap();
                    return;
                }

                chart.style.width = state.W + 'px';
                chart.style.height = state.H + 'px';

                const svg = document.createElementNS(NS, 'svg');
                svg.id = 'connectorSvg';
                svg.setAttribute('width', state.W);
                svg.setAttribute('height', state.H);
                svg.setAttribute('viewBox', `0 0 ${state.W} ${state.H}`);
                chart.appendChild(svg);
                drawConnectors(svg);

                state.flat.forEach((n) => chart.appendChild(createNode(n, state.layout.get(n.id))));
                applyLevelFilter();
            };

            const renderTabs = () => {
                const wrap = document.getElementById('chartTabs');
                wrap.classList.toggle('d-none', state.charts.length <= 1);
                wrap.innerHTML = state.charts.map((c) =>
                    `<button type="button" class="chart-tab ${c.id === state.chartId ? 'active' : ''}" data-id="${c.id}">${esc(c.name)}</button>`
                ).join('');
            };

            document.getElementById('chartTabs').addEventListener('click', (e) => {
                const btn = e.target.closest('.chart-tab');
                if (!btn) return;
                const id = Number(btn.dataset.id);
                if (id !== state.chartId) loadStructure(id);
            });

            // ---------- Modals ----------
            const openKaryawanModal = (item) => {
                document.getElementById('modalJabatanTitle').textContent = item.jabatan;
                const body = document.getElementById('modalKaryawanBody');
                const karyawans = item.karyawans || [];

                if (!karyawans.length) {
                    body.innerHTML = `<div class="text-center py-4 text-muted">
                        <i class="fa-solid fa-user-slash fs-2 d-block mb-2"></i>
                        Tidak ada karyawan dengan jabatan ini</div>`;
                } else {
                    body.innerHTML = karyawans.map((k) => {
                        const init = (k.nama_lengkap || '?').charAt(0).toUpperCase();
                        const foto = k.foto ? `<img src="/storage/${esc(k.foto)}" alt="${esc(k.nama_lengkap)}">` : esc(init);
                        const aktif = k.status_aktif === 'aktif' || k.status_aktif == 1;
                        const status = aktif
                            ? `<span class="status-badge" style="background:#dcfce7;color:#166534">Aktif</span>`
                            : `<span class="status-badge" style="background:#fee2e2;color:#991b1b">Nonaktif</span>`;
                        return `<div class="karyawan-card">
                            <div class="karyawan-avatar">${foto}</div>
                            <div class="flex-grow-1">
                                <div class="karyawan-name">${esc(k.nama_lengkap)}</div>
                                <div class="karyawan-meta">
                                    ${k.nip ? `NIP: ${esc(k.nip)}` : ''}
                                    ${k.divisi ? ` · ${esc(k.divisi)}` : ''}
                                    ${k.email ? ` · ${esc(k.email)}` : ''}
                                </div>
                            </div>${status}</div>`;
                    }).join('');
                }
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalKaryawan')).show();
            };

            const openDetailModal = (item) => {
                const children = item.children?.length ?? 0;
                const karyawans = item.karyawans?.length ?? 0;
                const p = state.layout.get(item.id);
                const levelText = p ? `Level ${p.depth + 1}` : '—';
                const parents = parentIdsOf(item).map((id) => state.byId.get(id)).filter(Boolean);
                const parentText = parents.length ? parents.map((x) => esc(x.jabatan)).join(', ') : '— (Root)';

                document.getElementById('modalDetailBody').innerHTML = `
                    <div class="detail-row"><div class="detail-icon"><i class="fa-solid fa-briefcase"></i></div>
                        <div><div class="detail-label">Nama Jabatan</div><div class="detail-val">${esc(item.jabatan)}</div></div></div>
                    <div class="detail-row"><div class="detail-icon"><i class="fa-solid fa-building"></i></div>
                        <div><div class="detail-label">Divisi</div><div class="detail-val">${esc(item.divisi || '—')}</div></div></div>
                    <div class="detail-row"><div class="detail-icon"><i class="fa-solid fa-code-branch"></i></div>
                        <div><div class="detail-label">Level Hierarki</div><div class="detail-val">${levelText}</div></div></div>
                    <div class="detail-row"><div class="detail-icon"><i class="fa-solid fa-arrow-up"></i></div>
                        <div><div class="detail-label">Atasan</div><div class="detail-val">${parentText}</div></div></div>
                    <div class="detail-row"><div class="detail-icon"><i class="fa-solid fa-users"></i></div>
                        <div><div class="detail-label">Jumlah Karyawan</div><div class="detail-val">${karyawans} orang</div></div></div>
                    <div class="detail-row"><div class="detail-icon"><i class="fa-solid fa-sitemap"></i></div>
                        <div><div class="detail-label">Sub-jabatan Langsung</div><div class="detail-val">${children} jabatan</div></div></div>`;
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetail')).show();
            };

            // ---------- Load ----------
            container.style.overflow = 'hidden';

            const loadStructure = async (chartId = null) => {
                chart.innerHTML = `<div class="d-flex align-items-center justify-content-center" style="min-height:300px;min-width:300px">
                    <div class="spinner-border text-primary me-2"></div>
                    <span class="text-muted">Memuat struktur…</span></div>`;
                try {
                    let id = chartId;
                    if (id === null) {
                        try { id = Number(localStorage.getItem(STORE_KEY)) || null; } catch (e) { id = null; }
                    }

                    const res = await fetch(id ? `${apiTree}?chart=${id}` : apiTree, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    const data = await res.json();

                    state.charts = data.charts || [];
                    state.chartId = data.chart ? data.chart.id : null;
                    state.tree = data.tree || [];
                    document.getElementById('chartTitle').value = data.chart ? data.chart.name : '';
                    try { if (state.chartId) localStorage.setItem(STORE_KEY, String(state.chartId)); } catch (e) {}

                    indexTree();
                    computeLayout();
                    renderTabs();
                    renderChart();
                    setTimeout(fitToScreen, 100);
                } catch (err) {
                    console.error(err);
                    chart.innerHTML = `<div class="text-center py-5 text-muted" style="min-width:400px">
                        <i class="fa-solid fa-triangle-exclamation fs-1 d-block mb-2"></i>
                        <p>Gagal memuat struktur</p>
                        <button class="btn btn-sm btn-primary" onclick="location.reload()">Coba Lagi</button></div>`;
                }
            };

            loadStructure();
        });
    </script>
@endsection
