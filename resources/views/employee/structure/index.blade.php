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
            <div id="exportWrapper" style="position: relative; background: #f8fafc;">
                <div class="structure-header" id="structureHeader">
                    <input type="text" id="chartTitle" value="Struktur Inixindo Bandung"
                        placeholder="Klik untuk ubah judul" readonly>
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
            padding: 60px 60px 100px;
            min-width: 100%;
            min-height: 100%;
            transform-origin: 0 0;
        }

        .org-node {
            position: absolute;
            width: 210px;
            background: #fff;
            border: 2px solid #3b82f6;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .08);
            user-select: none;
            z-index: 10;
            transition: border-color .15s, box-shadow .15s, opacity .15s;
        }

        .org-node .node-header {
            padding: 12px 12px 8px;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
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

        .browser-fullscreen .structure-container {
            height: calc(100vh - 130px) !important;
            max-height: calc(100vh - 130px) !important;
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
            .org-node {
                width: 170px;
            }

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
    </style>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const NODE_W = 210;
            const NODE_H = 130;
            const LEVEL_H = 220;
            const GAP = 80;
            const ZOOM_MIN = 0.2;
            const ZOOM_MAX = 3.0;
            const ZOOM_STEP = 0.12;

            let structuresData = [];
            let allPositions = [];

            // --- Transform State (single source of truth) ---
            let tx = 0,
                ty = 0,
                tz = 1.0;

            const container = document.getElementById('structureContainer');
            const chart = document.getElementById('structureChart');
            const apiTree = "{{ route('employee.structure.api.tree') }}";

            // Apply transform matrix to chart
            const applyTransform = () => {
                chart.style.transform = `matrix(${tz},0,0,${tz},${tx},${ty})`;
                chart.style.transformOrigin = '0 0';
                document.getElementById('zoomLevel').textContent = Math.round(tz * 100) + '%';
                requestAnimationFrame(drawMiniMap);
            };

            // Zoom centered on a point in container coordinates
            const zoomAt = (newZoom, cx, cy) => {
                newZoom = Math.min(ZOOM_MAX, Math.max(ZOOM_MIN, newZoom));
                newZoom = Math.round(newZoom * 100) / 100;
                // Point in chart space before zoom
                const wx = (cx - tx) / tz;
                const wy = (cy - ty) / tz;
                tz = newZoom;
                // Keep that chart-space point fixed under cursor
                tx = cx - wx * tz;
                ty = cy - wy * tz;
                applyTransform();
            };

            const zoomCenter = (newZoom) => {
                const cx = container.clientWidth / 2;
                const cy = container.clientHeight / 2;
                zoomAt(newZoom, cx, cy);
            };

            // ---- Pan with pointer events ----
            let panStart = null;

            container.addEventListener('pointerdown', e => {
                // Only pan on background, not on nodes/buttons
                if (e.target.closest('.org-node')) return;
                e.preventDefault();
                container.setPointerCapture(e.pointerId);
                panStart = {
                    px: e.clientX,
                    py: e.clientY,
                    tx,
                    ty
                };
                container.classList.add('panning');
            });

            container.addEventListener('pointermove', e => {
                if (!panStart) return;
                tx = panStart.tx + (e.clientX - panStart.px);
                ty = panStart.ty + (e.clientY - panStart.py);
                applyTransform();
            });

            container.addEventListener('pointerup', () => {
                panStart = null;
                container.classList.remove('panning');
            });
            container.addEventListener('pointercancel', () => {
                panStart = null;
                container.classList.remove('panning');
            });

            // ---- Wheel zoom ----
            container.addEventListener('wheel', e => {
                e.preventDefault();
                const rect = container.getBoundingClientRect();
                const cx = e.clientX - rect.left;
                const cy = e.clientY - rect.top;
                const delta = e.deltaY < 0 ? ZOOM_STEP : -ZOOM_STEP;
                zoomAt(tz + delta, cx, cy);
            }, {
                passive: false
            });

            // ---- Touch pinch zoom ----
            let lastPinchDist = null;

            container.addEventListener('touchstart', e => {
                if (e.touches.length === 2) {
                    const dx = e.touches[0].clientX - e.touches[1].clientX;
                    const dy = e.touches[0].clientY - e.touches[1].clientY;
                    lastPinchDist = Math.hypot(dx, dy);
                }
            }, {
                passive: true
            });

            container.addEventListener('touchmove', e => {
                if (e.touches.length === 2 && lastPinchDist) {
                    e.preventDefault();
                    const dx = e.touches[0].clientX - e.touches[1].clientX;
                    const dy = e.touches[0].clientY - e.touches[1].clientY;
                    const dist = Math.hypot(dx, dy);
                    const scale = dist / lastPinchDist;
                    const cx = (e.touches[0].clientX + e.touches[1].clientX) / 2 - container
                        .getBoundingClientRect().left;
                    const cy = (e.touches[0].clientY + e.touches[1].clientY) / 2 - container
                        .getBoundingClientRect().top;
                    zoomAt(tz * scale, cx, cy);
                    lastPinchDist = dist;
                }
            }, {
                passive: false
            });

            container.addEventListener('touchend', () => {
                lastPinchDist = null;
            });

            // ---- Zoom buttons ----
            document.getElementById('btnZoomIn').addEventListener('click', () => zoomCenter(tz + ZOOM_STEP));
            document.getElementById('btnZoomOut').addEventListener('click', () => zoomCenter(tz - ZOOM_STEP));

            // ---- Fit to screen ----
            function fitToScreen() {
                const cw = container.clientWidth - 80;
                const ch = container.clientHeight - 80;
                const iw = parseFloat(chart.style.width) || chart.scrollWidth;
                const ih = parseFloat(chart.style.height) || chart.scrollHeight;
                const target = Math.min(cw / iw, ch / ih, 1);
                tz = Math.max(ZOOM_MIN, target);
                tx = (container.clientWidth - iw * tz) / 2;
                ty = 40;
                applyTransform();
            }

            document.getElementById('btnFitScreen').addEventListener('click', fitToScreen);

            // ---- Keyboard shortcuts ----
            document.addEventListener('keydown', e => {
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
                if ((e.ctrlKey || e.metaKey) && (e.key === '=' || e.key === '+')) {
                    e.preventDefault();
                    zoomCenter(tz + ZOOM_STEP);
                }
                if ((e.ctrlKey || e.metaKey) && e.key === '-') {
                    e.preventDefault();
                    zoomCenter(tz - ZOOM_STEP);
                }
                if ((e.ctrlKey || e.metaKey) && e.key === '0') {
                    e.preventDefault();
                    fitToScreen();
                }
            });

            // ---- Level filter ----
            document.getElementById('levelFilter').addEventListener('change', applyLevelFilter);

            function applyLevelFilter() {
                const val = document.getElementById('levelFilter').value;
                document.querySelectorAll('.org-node').forEach(node => {
                    node.classList.remove('level-hidden');
                    if (val === 'all') return;
                    const lvl = parseInt(node.dataset.level, 10);
                    if (val === '4') {
                        if (lvl < 4) node.classList.add('level-hidden');
                    } else {
                        if (String(lvl) !== val) node.classList.add('level-hidden');
                    }
                });
            }

            // ---- MiniMap ----
            function drawMiniMap() {
                const canvas = document.getElementById('miniMapCanvas');
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                const cw = parseFloat(chart.style.width) || 1;
                const ch = parseFloat(chart.style.height) || 1;
                const sx = canvas.width / cw;
                const sy = canvas.height / ch;

                ctx.fillStyle = '#93c5fd';
                document.querySelectorAll('.org-node').forEach(node => {
                    ctx.fillRect(
                        parseFloat(node.style.left) * sx,
                        parseFloat(node.style.top) * sy,
                        NODE_W * sx, NODE_H * sy
                    );
                });

                // Viewport rect: what's visible in container-space mapped back to chart-space
                const vx = -tx / tz;
                const vy = -ty / tz;
                const vw = container.clientWidth / tz;
                const vh = container.clientHeight / tz;

                const vp = document.getElementById('miniMapViewport');
                vp.style.left = (vx * sx) + 'px';
                vp.style.top = (vy * sy) + 'px';
                vp.style.width = (vw * sx) + 'px';
                vp.style.height = (vh * sy) + 'px';
            }

            // ---- Fullscreen ----
            const mainCard = document.getElementById('mainCard');
            let isFullscreen = false;
            document.getElementById('btnFullscreen').addEventListener('click', () => {
                isFullscreen = !isFullscreen;
                mainCard.classList.toggle('browser-fullscreen', isFullscreen);
                document.body.style.overflow = isFullscreen ? 'hidden' : '';
                document.getElementById('btnFullscreen').innerHTML = isFullscreen ?
                    '<i class="ri-fullscreen-exit-line"></i> Kecilkan' :
                    '<i class="ri-fullscreen-line"></i> Perbesar';
                setTimeout(fitToScreen, 200);
            });

            let resizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(fitToScreen, 250);
            });

            // ======================================================
            // Tree layout — unchanged logic, same as your original
            // ======================================================
            const subtreeWidth = item => {
                if (!item.children?.length) return NODE_W;
                let total = 0;
                item.children.forEach((c, i) => {
                    total += subtreeWidth(c);
                    if (i < item.children.length - 1) total += GAP;
                });
                return Math.max(NODE_W, total);
            };

            const flattenTree = items => {
                let r = [];
                items.forEach(item => {
                    r.push(item);
                    if (item.children) r = r.concat(flattenTree(item.children));
                });
                return r;
            };

            const calcPositions = (items, level, startX) => {
                let positions = [],
                    curX = startX;
                items.forEach(item => {
                    const sw = subtreeWidth(item);
                    const cx = curX + (sw - NODE_W) / 2;
                    positions.push({
                        item,
                        x: cx,
                        y: level * LEVEL_H + 40,
                        level
                    });
                    if (item.children?.length) {
                        let childTotal = 0;
                        item.children.forEach((c, i) => {
                            childTotal += subtreeWidth(c);
                            if (i < item.children.length - 1) childTotal += GAP;
                        });
                        const childStart = cx - (childTotal - NODE_W) / 2;
                        positions = positions.concat(calcPositions(item.children, level + 1,
                            childStart));
                    }
                    curX += sw + GAP;
                });
                return positions;
            };

            const adjustMultiParentPositions = () => {
                const multiParentNodes = allPositions.filter(p =>
                    p.item.additional_parents && p.item.additional_parents.length > 0
                );
                multiParentNodes.forEach(pos => {
                    const item = pos.item;
                    const allParentIds = [];
                    if (item.parent_id) allParentIds.push(String(item.parent_id));
                    if (item.additional_parents) item.additional_parents.forEach(pid => {
                        if (!allParentIds.includes(String(pid))) allParentIds.push(String(pid));
                    });
                    if (!allParentIds.length) return;
                    const parentPositions = allParentIds
                        .map(pid => allPositions.find(ap => String(ap.item.id) === pid))
                        .filter(Boolean);
                    if (!parentPositions.length) return;
                    const maxParentLevel = Math.max(...parentPositions.map(p => p.level));
                    const targetLevel = maxParentLevel + 1;
                    const minX = Math.min(...parentPositions.map(p => p.x));
                    const maxX = Math.max(...parentPositions.map(p => p.x + NODE_W));
                    const centerX = (minX + maxX) / 2 - NODE_W / 2;
                    const offsetX = centerX - pos.x;
                    const offsetY = (targetLevel - pos.level) * LEVEL_H;
                    const shiftSubtree = nodePos => {
                        nodePos.x += offsetX;
                        nodePos.y += offsetY;
                        nodePos.level = targetLevel + (nodePos.level - pos.level);
                        allPositions
                            .filter(p => p.item.parent_id && String(p.item.parent_id) === String(
                                nodePos.item.id))
                            .forEach(shiftSubtree);
                    };
                    shiftSubtree(pos);
                });
            };

            const renderChart = items => {
                chart.innerHTML = '';
                if (!items?.length) {
                    chart.innerHTML = `<div class="text-center py-5 text-muted" style="min-width:400px">
                <i class="ri-sitemap-line fs-1 d-block mb-2"></i>Belum ada data.</div>`;
                    return;
                }
                let totalW = 0;
                items.forEach((item, i) => {
                    totalW += subtreeWidth(item);
                    if (i < items.length - 1) totalW += GAP;
                });
                const chartPadX = 60;
                const chartW = totalW + chartPadX * 2;
                allPositions = calcPositions(items, 0, chartPadX);
                adjustMultiParentPositions();
                const totalH = allPositions.reduce((m, p) => Math.max(m, p.y + NODE_H + 80), 400);

                chart.style.width = chartW + 'px';
                chart.style.height = totalH + 'px';
                chart.style.minWidth = '';
                chart.style.minHeight = '';

                const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                svg.id = 'connectorSvg';
                svg.setAttribute('width', chartW);
                svg.setAttribute('height', totalH);
                chart.appendChild(svg);

                allPositions.forEach(p => chart.appendChild(createNode(p.item, p.x, p.y, p.level)));
                requestAnimationFrame(() => drawConnectors(items, svg));
                applyLevelFilter();
                requestAnimationFrame(drawMiniMap);
            };

            const drawConnectors = (items, svg) => {
                svg.innerHTML = '';
                const posMap = {};
                allPositions.forEach(p => posMap[p.item.id] = p);

                const drawLine = (x1, y1, x2, y2) => {
                    const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                    line.setAttribute('x1', x1);
                    line.setAttribute('y1', y1);
                    line.setAttribute('x2', x2);
                    line.setAttribute('y2', y2);
                    line.setAttribute('stroke', '#93c5fd');
                    line.setAttribute('stroke-width', '2');
                    svg.appendChild(line);
                };

                const drawTreeLines = nodes => {
                    nodes.forEach(parent => {
                        if (!parent.children?.length) return;
                        const pp = posMap[parent.id];
                        if (!pp) return;
                        const px = pp.x + NODE_W / 2,
                            py = pp.y + NODE_H;
                        parent.children.forEach(child => {
                            const cp = posMap[child.id];
                            if (!cp) return;
                            const cx = cp.x + NODE_W / 2,
                                cy = cp.y;
                            const midY = py + (cy - py) / 2;
                            drawLine(px, py, px, midY);
                            drawLine(px, midY, cx, midY);
                            drawLine(cx, midY, cx, cy);
                        });
                        drawTreeLines(parent.children);
                    });
                };
                drawTreeLines(items);

                allPositions.forEach(p => {
                    const item = p.item;
                    if (!item.additional_parents?.length) return;
                    const cx = p.x + NODE_W / 2,
                        cy = p.y;
                    item.additional_parents.forEach(addParentId => {
                        const ap = posMap[String(addParentId)];
                        if (!ap) return;
                        const apx = ap.x + NODE_W / 2,
                            apy = ap.y + NODE_H;
                        const midY = apy + (cy - apy) / 2;
                        drawLine(apx, apy, apx, midY);
                        drawLine(apx, midY, cx, midY);
                        drawLine(cx, midY, cx, cy);
                    });
                });
            };

            const createNode = (item, x, y, level) => {
                const el = document.createElement('div');
                el.className = 'org-node';
                el.dataset.id = item.id;
                el.dataset.level = level + 1;
                el.style.left = x + 'px';
                el.style.top = y + 'px';

                const initial = (item.jabatan || '?').charAt(0).toUpperCase();
                const count = item.karyawans?.length ?? 0;
                const divisi = item.divisi ? `<div class="org-divisi">${item.divisi}</div>` : '';

                el.innerHTML = `
            <div class="node-header">
                <div class="org-avatar">${initial}</div>
                <div class="org-title">${item.jabatan}</div>
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

                el.querySelector('.btn-action-karyawan').addEventListener('click', e => {
                    e.stopPropagation();
                    openKaryawanModal(item);
                });
                el.querySelector('.btn-action-detail').addEventListener('click', e => {
                    e.stopPropagation();
                    openDetailModal(item);
                });
                return el;
            };

            // ---- Modals (unchanged) ----
            const openKaryawanModal = item => {
                document.getElementById('modalJabatanTitle').textContent = item.jabatan;
                const body = document.getElementById('modalKaryawanBody');
                const karyawans = item.karyawans || [];
                if (!karyawans.length) {
                    body.innerHTML = `<div class="text-center py-4 text-muted">
                <i class="ri-user-unfollow-line fs-2 d-block mb-2"></i>
                Tidak ada karyawan dengan jabatan ini</div>`;
                } else {
                    body.innerHTML = karyawans.map(k => {
                        const init = (k.nama_lengkap || '?').charAt(0).toUpperCase();
                        const foto = k.foto ? `<img src="/storage/${k.foto}" alt="${k.nama_lengkap}">` :
                            init;
                        const aktif = k.status_aktif === 'aktif' || k.status_aktif == 1;
                        const status = aktif ?
                            `<span class="status-badge" style="background:#dcfce7;color:#166534">Aktif</span>` :
                            `<span class="status-badge" style="background:#fee2e2;color:#991b1b">Nonaktif</span>`;
                        return `<div class="karyawan-card">
                    <div class="karyawan-avatar">${foto}</div>
                    <div class="flex-grow-1">
                        <div class="karyawan-name">${k.nama_lengkap}</div>
                        <div class="karyawan-meta">
                            ${k.nip    ? `NIP: ${k.nip}`  : ''}
                            ${k.divisi ? ` · ${k.divisi}` : ''}
                            ${k.email  ? ` · ${k.email}`  : ''}
                        </div>
                    </div>${status}</div>`;
                    }).join('');
                }
                new bootstrap.Modal(document.getElementById('modalKaryawan')).show();
            };

            const openDetailModal = item => {
                const children = item.children?.length ?? 0;
                const karyawans = item.karyawans?.length ?? 0;
                const pos = allPositions.find(p => String(p.item.id) === String(item.id));
                const levelText = pos ? `Level ${pos.level + 1}` : '—';
                const findParent = (nodes, targetId, parent = null) => {
                    for (const n of nodes) {
                        if (String(n.id) === String(targetId)) return parent;
                        const found = findParent(n.children || [], targetId, n);
                        if (found !== undefined) return found;
                    }
                    return undefined;
                };
                const parentNode = findParent(structuresData, item.id);
                const parentText = parentNode ? parentNode.jabatan : '— (Root)';
                document.getElementById('modalDetailBody').innerHTML =
                    `
            <div class="detail-row"><div class="detail-icon"><i class="ri-briefcase-line"></i></div>
                <div><div class="detail-label">Nama Jabatan</div><div class="detail-val">${item.jabatan}</div></div></div>
            <div class="detail-row"><div class="detail-icon"><i class="ri-building-line"></i></div>
                <div><div class="detail-label">Divisi</div><div class="detail-val">${item.divisi || '—'}</div></div></div>
            <div class="detail-row"><div class="detail-icon"><i class="ri-git-branch-line"></i></div>
                <div><div class="detail-label">Level Hierarki</div><div class="detail-val">${levelText}</div></div></div>
            <div class="detail-row"><div class="detail-icon"><i class="ri-arrow-up-line"></i></div>
                <div><div class="detail-label">Atasan Langsung</div><div class="detail-val">${parentText}</div></div></div>
            <div class="detail-row"><div class="detail-icon"><i class="ri-group-line"></i></div>
                <div><div class="detail-label">Jumlah Karyawan</div><div class="detail-val">${karyawans} orang</div></div></div>
            <div class="detail-row"><div class="detail-icon"><i class="ri-node-tree"></i></div>
                <div><div class="detail-label">Sub-jabatan Langsung</div><div class="detail-val">${children} jabatan</div></div></div>`;
                new bootstrap.Modal(document.getElementById('modalDetail')).show();
            };

            // ---- CSS: container overflow hidden (transform handles viewport) ----
            container.style.overflow = 'hidden';

            // ---- Load ----
            const loadStructure = async () => {
                chart.innerHTML = `<div class="d-flex align-items-center justify-content-center" style="min-height:300px">
            <div class="spinner-border text-primary me-2"></div>
            <span class="text-muted">Memuat struktur…</span></div>`;
                try {
                    const res = await fetch(apiTree, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await res.json();
                    structuresData = data.tree || [];
                    renderChart(structuresData);
                    setTimeout(fitToScreen, 100);
                } catch (err) {
                    console.error(err);
                    chart.innerHTML = `<div class="text-center py-5 text-muted">
                <i class="ri-error-warning-line fs-1 d-block mb-2"></i>
                <p>Gagal memuat struktur</p>
                <button class="btn btn-sm btn-primary" onclick="location.reload()">Coba Lagi</button></div>`;
                }
            };

            loadStructure();
        });
    </script>
@endsection
