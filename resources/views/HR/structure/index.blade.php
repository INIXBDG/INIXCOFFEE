@extends('layout_HR.app')
@section('content_HR')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0"><i class="ri-org-chart me-2"></i>Struktur Organisasi</h4>
            <small class="text-muted">Drag &amp; drop untuk mengatur hierarki jabatan</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <div class="input-group input-group-sm" style="width:200px">
                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" id="searchInput" class="form-control" placeholder="Cari jabatan…">
            </div>
            <button class="btn btn-primary btn-sm" id="btnFitScreen">
                <i class="fa-solid fa-left-right me-2"></i> Sesuaikan
            </button>
            <button class="btn btn-secondary btn-sm" id="btnSync">
                <i class="fa-solid fa-rotate me-2"></i> Sinkronisasi
            </button>
            <button class="btn btn-success btn-sm" id="btnExport">
                <i class="fa-solid fa-file-arrow-down me-2"></i> Export PNG
            </button>
        </div>
    </div>

    <div class="row g-3 mb-3" id="statsPanel">
        <div class="col-6 col-md-3">
            <div class="card stat-card">
                <div class="card-body py-2 px-3">
                    <div class="stat-label"><i class="ri-briefcase-line me-1"></i>Total Jabatan</div>
                    <div class="stat-value" id="statJabatan">—</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card">
                <div class="card-body py-2 px-3">
                    <div class="stat-label"><i class="ri-group-line me-1"></i>Total Karyawan</div>
                    <div class="stat-value" id="statKaryawan">—</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card">
                <div class="card-body py-2 px-3">
                    <div class="stat-label"><i class="ri-git-branch-line me-1"></i>Level Terdalam</div>
                    <div class="stat-value" id="statLevel">—</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card">
                <div class="card-body py-2 px-3">
                    <div class="stat-label"><i class="ri-user-heart-line me-1"></i>Jabatan Kosong</div>
                    <div class="stat-value" id="statKosong">—</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm" id="mainCard">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span><i class="ri-sitemap-line me-1"></i>Visualisasi Struktur</span>
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
                <span class="badge bg-light text-dark" id="zoomLevel" style="min-width:48px;text-align:center">100%</span>
                <button class="btn btn-sm btn-outline-secondary" id="btnZoomIn" title="Zoom In"><i
                        class="fa-solid fa-plus"></i></button>
                <div class="vr mx-1"></div>
                <button class="btn btn-sm btn-outline-dark" id="btnFullscreen">
                    <i class="fa-solid fa-maximize"></i> Perbesar
                </button>
                <button class="btn btn-sm btn-outline-secondary" id="btnRefresh">
                    <i class="fa-solid fa-arrows-rotate"></i>
                </button>
                <button class="btn btn-primary btn-sm" id="btnAddPosition">
                    <i class="ri-add-line me-1"></i>Tambah Jabatan
                </button>
            </div>
        </div>
            <div class="card-body p-0 position-relative">
                <div id="exportWrapper" style="position: relative; background: #f8fafc;">
                    <div class="structure-header" id="structureHeader">
                        <input type="text" id="chartTitle" value="Struktur Inixindo Bandung" placeholder="Klik untuk ubah judul">
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

    <div class="modal fade" id="modalDelete" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white py-2">
                    <h6 class="modal-title"><i class="ri-delete-bin-line me-2"></i>Hapus Jabatan</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-1">Hapus jabatan <strong id="deleteJabatanNama">—</strong>?</p>
                    <p class="text-danger small mb-0"><i class="ri-error-warning-line me-1"></i>Karyawan yang terhubung
                        akan kehilangan jabatan ini.</p>
                </div>
                <div class="modal-footer py-2">
                    <button class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-sm btn-danger" id="btnDeleteConfirm">
                        <i class="ri-delete-bin-line me-1"></i>Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalAddPosition" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Jabatan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Jabatan</label>
                        <input type="text" id="addJabatan" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Divisi</label>
                        <input type="text" id="addDivisi" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Atasan (Opsional)</label>
                        <select id="addParent" class="form-select">
                            <option value="">— Root —</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Karyawan (Opsional)</label>
                        <select id="addKaryawan" class="form-select" multiple>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSaveNewPosition">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalMultiParent" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-code-branch me-2"></i>Atur Bawahan Bersama</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Posisi</label>
                        <input type="text" id="multiParentPositionDisplay" class="form-control" disabled>
                        <input type="hidden" id="multiParentPosition">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Atasan Tambahan</label>
                        <select id="multiParentAdditional" class="form-select" multiple size="6">
                        </select>
                        <small class="text-muted">Tahan Ctrl (Windows) atau Cmd (Mac) untuk pilih lebih dari satu</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSaveMultiParent">
                        <i class="fa-solid fa-save me-1"></i>Simpan
                    </button>
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
            gap: 8px;  /* Ubah dari 4px menjadi 8px atau lebih */
            justify-content: center;
            padding: 8px 12px;  /* Tambah padding vertikal & horizontal */
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

        .org-node.dragging {
            opacity: .5;
            border-style: dashed;
            cursor: grabbing !important;
            z-index: 9000;
            box-shadow: 0 12px 32px rgba(59, 130, 246, .35);
        }

        .org-node.drop-hover {
            border-color: #22c55e;
            background: #f0fdf4;
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
            border-bottom: 2px dashed #cbd5e1;
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

        .structure-header input:focus {
            border-bottom-color: #3b82f6;
        }

        .org-avatar, .karyawan-avatar {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8) !important;
        }

        .org-avatar img, .karyawan-avatar img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            border-radius: 50% !important;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const NODE_W = 210;
            const NODE_H = 130;
            const LEVEL_H = 220;
            const GAP = 80;
            const ZOOM_MIN = 0.25;
            const ZOOM_MAX = 2.5;
            const ZOOM_STEP = 0.12;

            let structuresData = [];
            let allPositions = [];
            let currentZoom = 1;
            let currentDeleteId = null;
            let currentDetailItem = null;

            let drag = null;
            let pan = null;

            const container = document.getElementById('structureContainer');
            const chart = document.getElementById('structureChart');
            const csrf = document.querySelector('meta[name="csrf-token"]').content;

            const apiTree = "{{ route('HR.structure.api.tree') }}";
            const apiSync = "{{ route('HR.structure.sync') }}";
            const apiOrder = "{{ route('HR.structure.reorder') }}";
            const apiUpdate = "{{ route('HR.structure.update', 0) }}".replace(/\/0$/, '');
            const apiDelete = "{{ route('HR.structure.destroy', 0) }}".replace(/\/0$/, '');

            const loadStructure = async () => {
                chart.innerHTML = `<div class="d-flex align-items-center justify-content-center" style="min-height:300px">
                <div class="spinner-border text-primary me-2"></div>
                <span class="text-muted">Memuat struktur…</span>
            </div>`;
                try {
                    const res = await fetch(apiTree, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await res.json();
                    structuresData = data.tree || [];
                    renderChart(structuresData);
                    updateStats(structuresData);
                    setTimeout(fitToScreen, 100);
                } catch (err) {
                    console.error(err);
                    chart.innerHTML = `<div class="text-center py-5 text-muted">
                    <i class="ri-error-warning-line fs-1 d-block mb-2"></i>
                    <p>Gagal memuat struktur</p>
                    <button class="btn btn-sm btn-primary" onclick="location.reload()">Coba Lagi</button>
                </div>`;
                }
            };

            const getAllParents = (item, allItems) => {
                const parents = [];
                if (item.parent_id) {
                    const parent = allItems.find(n => String(n.id) === String(item.parent_id));
                    if (parent) parents.push(parent);
                }
                if (item.additional_parents && item.additional_parents.length > 0) {
                    item.additional_parents.forEach(pid => {
                        const parent = allItems.find(n => String(n.id) === String(pid));
                        if (parent && !parents.find(p => String(p.id) === String(pid))) {
                            parents.push(parent);
                        }
                    });
                }
                return parents;
            };

            const subtreeWidth = (item) => {
                if (!item.children?.length) return NODE_W;
                let total = 0;
                item.children.forEach((c, i) => {
                    total += subtreeWidth(c);
                    if (i < item.children.length - 1) total += GAP;
                });
                return Math.max(NODE_W, total);
            };

            const flattenTree = (items) => {
                let result = [];
                items.forEach(item => {
                    result.push(item);
                    if (item.children) {
                        result = result.concat(flattenTree(item.children));
                    }
                });
                return result;
            };

            const calcPositions = (items, level, startX) => {
                let positions = [];
                let curX = startX;

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
                        positions = positions.concat(calcPositions(item.children, level + 1, childStart));
                    }
                    curX += sw + GAP;
                });

                return positions;
            };

            const adjustMultiParentPositions = () => {
                const allItems = flattenTree(structuresData);
                
                const multiParentNodes = allPositions.filter(p => 
                    p.item.additional_parents && p.item.additional_parents.length > 0
                );
                
                multiParentNodes.forEach(pos => {
                    const item = pos.item;
                    
                    const allParentIds = [];
                    if (item.parent_id) {
                        allParentIds.push(String(item.parent_id));
                    }
                    if (item.additional_parents) {
                        item.additional_parents.forEach(pid => {
                            if (!allParentIds.includes(String(pid))) {
                                allParentIds.push(String(pid));
                            }
                        });
                    }
                    
                    if (allParentIds.length > 0) {
                        const parentPositions = allParentIds
                            .map(pid => allPositions.find(ap => String(ap.item.id) === pid))
                            .filter(p => p);
                        
                        if (parentPositions.length > 0) {
                            // Hitung level berdasarkan parent terdalam + 1
                            const maxParentLevel = Math.max(...parentPositions.map(p => p.level));
                            const targetLevel = maxParentLevel + 1;
                            
                            // Hitung posisi X di tengah-tengah parent
                            const minX = Math.min(...parentPositions.map(p => p.x));
                            const maxX = Math.max(...parentPositions.map(p => p.x + NODE_W));
                            const centerX = (minX + maxX) / 2 - NODE_W / 2;
                            
                            // Hitung offset
                            const offsetX = centerX - pos.x;
                            const offsetY = (targetLevel - pos.level) * LEVEL_H;
                            
                            // Geser node dan seluruh subtree-nya
                            const shiftSubtree = (nodePos) => {
                                nodePos.x += offsetX;
                                nodePos.y += offsetY;
                                nodePos.level = targetLevel + (nodePos.level - pos.level);
                                
                                const children = allPositions.filter(p => 
                                    p.item.parent_id && String(p.item.parent_id) === String(nodePos.item.id)
                                );
                                
                                children.forEach(childPos => {
                                    shiftSubtree(childPos);
                                });
                            };
                            
                            shiftSubtree(pos);
                        }
                    }
                });
            };

            const renderChart = (items) => {
                chart.innerHTML = '';

                if (!items?.length) {
                    chart.innerHTML = `<div class="text-center py-5 text-muted" style="min-width:400px">
                        <i class="ri-sitemap-line fs-1 d-block mb-2"></i>
                        Belum ada data. Klik <b>Sinkronisasi</b> untuk memulai.
                    </div>`;
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
                
                // Adjust posisi untuk multi-parent
                adjustMultiParentPositions();
                
                // Hitung ulang tinggi chart berdasarkan posisi terbaru
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

                allPositions.forEach(p => {
                    chart.appendChild(createNode(p.item, p.x, p.y, p.level));
                });

                requestAnimationFrame(() => drawConnectors(items, svg));

                applyLevelFilter();

                requestAnimationFrame(drawMiniMap);
            };

            const drawConnectors = (items, svg) => {
                svg.innerHTML = '';
                const posMap = {};
                allPositions.forEach(p => posMap[p.item.id] = p);
                
                const allItems = flattenTree(items);

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

                const drawTreeLines = (nodes) => {
                    nodes.forEach(parent => {
                        if (!parent.children?.length) return;
                        const pp = posMap[parent.id];
                        if (!pp) return;
                        const px = pp.x + NODE_W / 2;
                        const py = pp.y + NODE_H;
                        
                        parent.children.forEach(child => {
                            const cp = posMap[child.id];
                            if (!cp) return;
                            const cx = cp.x + NODE_W / 2;
                            const cy = cp.y;
                            const midY = py + (cy - py) / 2;

                            drawLine(px, py, px, midY);
                            drawLine(px, midY, cx, midY);
                            drawLine(cx, midY, cx, cy);
                        });
                        
                        drawTreeLines(parent.children);
                    });
                };

                drawTreeLines(items);
                
                // Draw lines for additional parents
                allPositions.forEach(p => {
                    const item = p.item;
                    if (item.additional_parents && item.additional_parents.length > 0) {
                        const cx = p.x + NODE_W / 2;
                        const cy = p.y;
                        
                        item.additional_parents.forEach(addParentId => {
                            const addParentPos = posMap[String(addParentId)];
                            if (!addParentPos) return;
                            
                            const apx = addParentPos.x + NODE_W / 2;
                            const apy = addParentPos.y + NODE_H;
                            const midY = apy + (cy - apy) / 2;

                            drawLine(apx, apy, apx, midY);
                            drawLine(apx, midY, cx, midY);
                            drawLine(cx, midY, cx, cy);
                        });
                    }
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
                        <button class="btn btn-outline-warning btn-action-multiparent" title="Atur Bawahan Bersama">
                            <i class="fa-solid fa-code-branch"></i>
                        </button>
                        <button class="btn btn-outline-danger btn-action-delete" title="Hapus">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                `;

                el.querySelector('.btn-action-karyawan').addEventListener('click', e => {
                    e.stopPropagation();
                    openKaryawanModal(item);
                });
                el.querySelector('.btn-action-detail').addEventListener('click', e => {
                    e.stopPropagation();
                    openDetailModal(item);
                });
                el.querySelector('.btn-action-multiparent').addEventListener('click', e => {
                    e.stopPropagation();
                    openMultiParentModal(item);
                });
                el.querySelector('.btn-action-delete').addEventListener('click', e => {
                    e.stopPropagation();
                    openDeleteModal(item);
                });

                el.addEventListener('mousedown', e => {
                    if (e.target.closest('button')) return;
                    e.preventDefault();
                    e.stopPropagation();

                    drag = {
                        el,
                        id: item.id,
                        startMouseX: e.clientX,
                        startMouseY: e.clientY,
                        startElLeft: parseFloat(el.style.left),
                        startElTop: parseFloat(el.style.top),
                    };
                    el.classList.add('dragging');
                });

                return el;
            };

            const openMultiParentModal = async (item) => {
                const positionInput = document.getElementById('multiParentPosition');
                const displayInput = document.getElementById('multiParentPositionDisplay');
                const additionalSelect = document.getElementById('multiParentAdditional');

                positionInput.value = item.id;
                displayInput.value = item.jabatan;
                additionalSelect.innerHTML = '';

                try {
                    const res = await fetch(apiTree);
                    const data = await res.json();

                    const allPos = [];
                    const collect = (nodes) => {
                        nodes.forEach(n => {
                            if (String(n.id) !== String(item.id)) {
                                allPos.push({
                                    id: n.id,
                                    jabatan: n.jabatan
                                });
                            }
                            if (n.children) collect(n.children);
                        });
                    };
                    collect(data.tree);

                    allPos.forEach(n => {
                        const opt = document.createElement('option');
                        opt.value = n.id;
                        opt.textContent = n.jabatan;
                        if (item.additional_parents && item.additional_parents.includes(String(n.id))) {
                            opt.selected = true;
                        }
                        additionalSelect.appendChild(opt);
                    });
                } catch (e) {
                    console.error(e);
                }

                new bootstrap.Modal(document.getElementById('modalMultiParent')).show();
            };

            document.getElementById('btnSaveMultiParent').addEventListener('click', async () => {
                const positionId = document.getElementById('multiParentPosition').value;
                const additionalParents = [...document.getElementById('multiParentAdditional')
                    .selectedOptions
                ].map(o => o.value);

                if (!positionId) return toast('error', 'Posisi tidak valid');

                try {
                    const res = await fetch("{{ route('HR.structure.multiParent') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({
                            position_id: positionId,
                            additional_parents: additionalParents
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('modalMultiParent')).hide();
                        toast('success', data.message);
                        loadStructure();
                    } else {
                        toast('error', data.message);
                    }
                } catch (e) {
                    toast('error', 'Gagal menyimpan: ' + e.message);
                }
            });

            let hasDragged = false;

            document.addEventListener('mousemove', e => {
                if (!drag) return;

                const dx = (e.clientX - drag.startMouseX) / currentZoom;
                const dy = (e.clientY - drag.startMouseY) / currentZoom;

                if (!hasDragged && (Math.abs(dx) > 4 || Math.abs(dy) > 4)) {
                    hasDragged = true;
                }
                if (!hasDragged) return;

                drag.el.style.left = (drag.startElLeft + dx) + 'px';
                drag.el.style.top = (drag.startElTop + dy) + 'px';

                document.querySelectorAll('.org-node').forEach(n => n.classList.remove('drop-hover'));
                const target = getDropTarget(e);
                if (target) target.classList.add('drop-hover');
            });

            document.addEventListener('mouseup', e => {
                if (!drag) return;
                drag.el.classList.remove('dragging');
                document.querySelectorAll('.org-node').forEach(n => n.classList.remove('drop-hover'));

                if (hasDragged) {
                    const target = getDropTarget(e);
                    if (target) {
                        const newParentId = target.dataset.id;
                        if (isDescendant(drag.id, newParentId)) {
                            toast('error', 'Tidak bisa memindahkan ke dalam bawahan sendiri');
                            loadStructure();
                        } else {
                            reorder(drag.id, newParentId);
                        }
                    } else {
                        reorder(drag.id, null);
                    }
                }

                drag = null;
                hasDragged = false;
            });

            const getDropTarget = (e) => {
                return document.elementsFromPoint(e.clientX, e.clientY)
                    .map(el => el.closest('.org-node'))
                    .find(el => el && el.dataset.id !== String(drag?.id)) || null;
            };

            const isDescendant = (draggedId, targetId) => {
                draggedId = String(draggedId);
                targetId = String(targetId);
                const find = (items, id) => {
                    for (const n of items) {
                        if (String(n.id) === id) return n;
                        const f = find(n.children || [], id);
                        if (f) return f;
                    }
                    return null;
                };
                const hasChild = (node, id) => {
                    for (const c of (node.children || [])) {
                        if (String(c.id) === id) return true;
                        if (hasChild(c, id)) return true;
                    }
                    return false;
                };
                const dn = find(structuresData, draggedId);
                return dn ? hasChild(dn, targetId) : false;
            };

            container.addEventListener('mousedown', e => {
                if (e.target !== container && e.target !== chart) return;
                pan = {
                    startX: e.clientX,
                    startY: e.clientY,
                    scrollLeft: container.scrollLeft,
                    scrollTop: container.scrollTop,
                };
                container.classList.add('panning');
            });

            document.addEventListener('mousemove', e => {
                if (!pan) return;
                container.scrollLeft = pan.scrollLeft - (e.clientX - pan.startX);
                container.scrollTop = pan.scrollTop - (e.clientY - pan.startY);
                drawMiniMap();
            });

            document.addEventListener('mouseup', () => {
                if (!pan) return;
                pan = null;
                container.classList.remove('panning');
            });

            const zoomTo = (newZoom, cx, cy) => {
                newZoom = Math.min(ZOOM_MAX, Math.max(ZOOM_MIN, newZoom));
                newZoom = Math.round(newZoom * 100) / 100;

                const ratio = newZoom / currentZoom;

                const newScrollLeft = (container.scrollLeft + cx) * ratio - cx;
                const newScrollTop = (container.scrollTop + cy) * ratio - cy;

                currentZoom = newZoom;
                chart.style.transform = `scale(${currentZoom})`;
                chart.style.transformOrigin = '0 0';

                container.scrollLeft = newScrollLeft;
                container.scrollTop = newScrollTop;

                document.getElementById('zoomLevel').textContent = Math.round(currentZoom * 100) + '%';
                requestAnimationFrame(drawMiniMap);
            };

            const applyZoom = (newZoom) => {
                const cx = container.clientWidth / 2;
                const cy = container.clientHeight / 2;
                zoomTo(newZoom, cx, cy);
            };

            container.addEventListener('wheel', e => {
                e.preventDefault();
                const rect = container.getBoundingClientRect();
                const cx = e.clientX - rect.left;
                const cy = e.clientY - rect.top;
                const delta = e.deltaY < 0 ? ZOOM_STEP : -ZOOM_STEP;
                zoomTo(currentZoom + delta, cx, cy);
            }, {
                passive: false
            });

            document.getElementById('btnZoomIn').addEventListener('click', () => applyZoom(currentZoom +
                ZOOM_STEP));
            document.getElementById('btnZoomOut').addEventListener('click', () => applyZoom(currentZoom -
                ZOOM_STEP));

            function fitToScreen() {
                const cw = container.clientWidth - 80;
                const ch = container.clientHeight - 80;
                const iw = parseFloat(chart.style.width) || chart.scrollWidth;
                const ih = parseFloat(chart.style.height) || chart.scrollHeight;
                const scaleX = cw / iw;
                const scaleY = ch / ih;
                const target = Math.min(scaleX, scaleY, 1);

                if (target > ZOOM_MIN) {
                    currentZoom = target;
                    chart.style.transform = `scale(${currentZoom})`;
                    chart.style.transformOrigin = '0 0';
                    document.getElementById('zoomLevel').textContent = Math.round(currentZoom * 100) + '%';
                    container.scrollTo({
                        left: 0,
                        top: 0,
                        behavior: 'smooth'
                    });
                }
                requestAnimationFrame(drawMiniMap);
            }

            document.getElementById('btnAddPosition').addEventListener('click', async () => {
                const parentSelect = document.getElementById('addParent');
                const karyawanSelect = document.getElementById('addKaryawan');

                parentSelect.innerHTML = '<option value="">— Root —</option>';
                karyawanSelect.innerHTML = '';

                try {
                    const res = await fetch(apiTree);
                    const data = await res.json();
                    const allNodes = [];
                    const collect = (nodes) => {
                        nodes.forEach(n => {
                            allNodes.push({
                                id: n.id,
                                jabatan: n.jabatan
                            });
                            if (n.children) collect(n.children);
                        });
                    };
                    collect(data.tree);
                    allNodes.forEach(n => {
                        const opt = document.createElement('option');
                        opt.value = n.id;
                        opt.textContent = n.jabatan;
                        parentSelect.appendChild(opt);
                    });
                } catch (e) {}

                try {
                    const res = await fetch("{{ route('HR.structure.karyawans') }}");
                    const karyawans = await res.json();
                    karyawans.forEach(k => {
                        const opt = document.createElement('option');
                        opt.value = k.id;
                        opt.textContent = `${k.nama_lengkap} (${k.nip})`;
                        karyawanSelect.appendChild(opt);
                    });
                } catch (e) {}

                new bootstrap.Modal(document.getElementById('modalAddPosition')).show();
            });

            document.getElementById('btnSaveNewPosition').addEventListener('click', async () => {
                const jabatan = document.getElementById('addJabatan').value.trim();
                const divisi = document.getElementById('addDivisi').value.trim() || null;
                const parentId = document.getElementById('addParent').value || null;
                const karyawanIds = [...document.getElementById('addKaryawan').selectedOptions].map(o =>
                    o.value);

                if (!jabatan) return toast('error', 'Nama jabatan wajib diisi');

                try {
                    const res = await fetch("{{ route('HR.structure.store') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({
                            jabatan,
                            divisi,
                            parent_id: parentId,
                            karyawan_ids: karyawanIds
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('modalAddPosition')).hide();
                        toast('success', data.message);
                        loadStructure();
                    } else {
                        toast('error', data.message);
                    }
                } catch (e) {
                    toast('error', 'Gagal menyimpan');
                }
            });

            document.getElementById('btnFitScreen').addEventListener('click', fitToScreen);

            document.getElementById('searchInput').addEventListener('input', e => {
                const q = e.target.value.trim().toLowerCase();
                document.querySelectorAll('.org-node').forEach(node => {
                    if (!q) {
                        node.classList.remove('highlighted', 'dimmed');
                        return;
                    }
                    const title = node.querySelector('.org-title')?.textContent.toLowerCase() || '';
                    const divisi = node.querySelector('.org-divisi')?.textContent.toLowerCase() ||
                        '';
                    if (title.includes(q) || divisi.includes(q)) {
                        node.classList.add('highlighted');
                        node.classList.remove('dimmed');
                    } else {
                        node.classList.add('dimmed');
                        node.classList.remove('highlighted');
                    }
                });
            });

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

            function updateStats(tree) {
                let jabatanCount = 0,
                    karyawanCount = 0,
                    maxLevel = 0,
                    kosongCount = 0;
                const walk = (nodes, level) => {
                    nodes.forEach(n => {
                        jabatanCount++;
                        const k = n.karyawans?.length ?? 0;
                        karyawanCount += k;
                        if (k === 0) kosongCount++;
                        if (level > maxLevel) maxLevel = level;
                        if (n.children?.length) walk(n.children, level + 1);
                    });
                };
                walk(tree, 1);
                document.getElementById('statJabatan').textContent = jabatanCount;
                document.getElementById('statKaryawan').textContent = karyawanCount;
                document.getElementById('statLevel').textContent = maxLevel;
                document.getElementById('statKosong').textContent = kosongCount;
            }

            function drawMiniMap() {
                const canvas = document.getElementById('miniMapCanvas');
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                const cw = parseFloat(chart.style.width) || 1;
                const ch = parseFloat(chart.style.height) || 1;
                const scaleX = canvas.width / cw;
                const scaleY = canvas.height / ch;

                ctx.fillStyle = '#93c5fd';
                document.querySelectorAll('.org-node').forEach(node => {
                    const l = parseFloat(node.style.left);
                    const t = parseFloat(node.style.top);
                    ctx.fillRect(l * scaleX, t * scaleY, NODE_W * scaleX, NODE_H * scaleY);
                });

                const vl = container.scrollLeft / currentZoom;
                const vt = container.scrollTop / currentZoom;
                const vw = container.clientWidth / currentZoom;
                const vh = container.clientHeight / currentZoom;
                const vp = document.getElementById('miniMapViewport');
                vp.style.left = (vl * scaleX) + 'px';
                vp.style.top = (vt * scaleY) + 'px';
                vp.style.width = (vw * scaleX) + 'px';
                vp.style.height = (vh * scaleY) + 'px';
            }

            container.addEventListener('scroll', drawMiniMap);

            const openKaryawanModal = (item) => {
                document.getElementById('modalJabatanTitle').textContent = item.jabatan;
                const body = document.getElementById('modalKaryawanBody');
                const karyawans = item.karyawans || [];

                if (!karyawans.length) {
                    body.innerHTML = `<div class="text-center py-4 text-muted">
                                <i class="ri-user-unfollow-line fs-2 d-block mb-2"></i>
                                Tidak ada karyawan dengan jabatan ini
                            </div>`;
                } else {
                    body.innerHTML = karyawans.map(k => {
                        const init = (k.nama_lengkap || '?').charAt(0).toUpperCase();
                        const foto = k.foto ?
                            `<img src="/storage/${k.foto}" alt="${k.nama_lengkap}">` :
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
                                    ${k.nip    ? `NIP: ${k.nip}`      : ''}
                                    ${k.divisi ? ` · ${k.divisi}`     : ''}
                                    ${k.email  ? ` · ${k.email}`      : ''}
                                </div>
                            </div>
                            ${status}
                        </div>`;
                    }).join('');
                }
                new bootstrap.Modal(document.getElementById('modalKaryawan')).show();
            };

            const openDetailModal = (item) => {
                currentDetailItem = item;
                const children = item.children?.length ?? 0;
                const karyawans = item.karyawans?.length ?? 0;
                const body = document.getElementById('modalDetailBody');

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

                body.innerHTML = `
                <div class="detail-row">
                    <div class="detail-icon"><i class="ri-briefcase-line"></i></div>
                    <div>
                        <div class="detail-label">Nama Jabatan</div>
                        <div class="detail-val">${item.jabatan}</div>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-icon"><i class="ri-building-line"></i></div>
                    <div>
                        <div class="detail-label">Divisi</div>
                        <div class="detail-val">${item.divisi || '—'}</div>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-icon"><i class="ri-git-branch-line"></i></div>
                    <div>
                        <div class="detail-label">Level Hierarki</div>
                        <div class="detail-val">${levelText}</div>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-icon"><i class="ri-arrow-up-line"></i></div>
                    <div>
                        <div class="detail-label">Atasan Langsung</div>
                        <div class="detail-val">${parentText}</div>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-icon"><i class="ri-group-line"></i></div>
                    <div>
                        <div class="detail-label">Jumlah Karyawan</div>
                        <div class="detail-val">${karyawans} orang</div>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-icon"><i class="ri-node-tree"></i></div>
                    <div>
                        <div class="detail-label">Sub-jabatan Langsung</div>
                        <div class="detail-val">${children} jabatan</div>
                    </div>
                </div>
            `;
                new bootstrap.Modal(document.getElementById('modalDetail')).show();
            };

            const openDeleteModal = (item) => {
                currentDeleteId = item.id;
                document.getElementById('deleteJabatanNama').textContent = item.jabatan;
                new bootstrap.Modal(document.getElementById('modalDelete')).show();
            };

            document.getElementById('btnDeleteConfirm').addEventListener('click', async () => {
                if (!currentDeleteId) return;
                const btn = document.getElementById('btnDeleteConfirm');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menghapus…';

                try {
                    const res = await fetch(`${apiDelete}/${currentDeleteId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const data = await res.json();
                    bootstrap.Modal.getInstance(document.getElementById('modalDelete'))?.hide();
                    toast(data.success ? 'success' : 'error', data.message);
                    if (data.success) loadStructure();
                } catch {
                    toast('error', 'Kesalahan koneksi. Coba lagi.');
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ri-delete-bin-line me-1"></i>Ya, Hapus';
                    currentDeleteId = null;
                }
            });

            const reorder = async (id, parentId) => {
                try {
                    const res = await fetch(apiOrder, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({
                            items: [{
                                id,
                                parent_id: parentId,
                                sort_order: 0
                            }]
                        }),
                    });
                    const data = await res.json();
                    toast(data.success ? 'success' : 'error', data.message);
                } catch {
                    toast('error', 'Kesalahan koneksi');
                } finally {
                    loadStructure();
                }
            };

            document.getElementById('btnSync').addEventListener('click', async () => {
                const btn = document.getElementById('btnSync');
                btn.disabled = true;
                btn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-1"></span>Sinkronisasi…';
                try {
                    const res = await fetch(apiSync, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                    });
                    const data = await res.json();
                    toast('success', data.message);
                    loadStructure();
                } catch {
                    toast('error', 'Gagal sinkronisasi');
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ri-refresh-line me-1"></i>Sinkronisasi';
                }
            });

            const mainCard = document.getElementById('mainCard');
            let isFullscreen = false;

            document.getElementById('btnFullscreen').addEventListener('click', () => {
                isFullscreen = !isFullscreen;
                mainCard.classList.toggle('browser-fullscreen', isFullscreen);
                document.body.style.overflow = isFullscreen ? 'hidden' : '';
                document.getElementById('btnFullscreen').innerHTML = isFullscreen ?
                    '<i class="ri-fullscreen-exit-line"></i> Kecilkan' :
                    '<i class="ri-fullscreen-line"></i> Perbesar';
                setTimeout(() => {
                    fitToScreen();
                }, 200);
            });

            document.getElementById('btnRefresh').addEventListener('click', () => {
                currentZoom = 1;
                chart.style.transform = 'scale(1)';
                document.getElementById('zoomLevel').textContent = '100%';
                loadStructure();
            });

            document.getElementById('btnExport').addEventListener('click', async () => {
                const btn = document.getElementById('btnExport');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Export…';

                const savedZoom = currentZoom;
                const savedScrollLeft = container.scrollLeft;
                const savedScrollTop = container.scrollTop;

                try {
                    chart.style.transform = 'scale(1)';
                    container.scrollLeft = 0;
                    container.scrollTop = 0;

                    await new Promise((resolve) => {
                        const images = chart.querySelectorAll('img');
                        if (images.length === 0) return resolve();
                        
                        let loaded = 0;
                        images.forEach(img => {
                            if (img.complete) {
                                loaded++;
                            } else {
                                img.onload = img.onerror = () => {
                                    loaded++;
                                    if (loaded === images.length) resolve();
                                };
                            }
                        });
                        
                        if (loaded === images.length) resolve();
                        else setTimeout(resolve, 2000);
                    });

                    // Hitung bounding box aktual dari semua node
                    const nodes = chart.querySelectorAll('.org-node');
                    let minX = Infinity, minY = Infinity, maxX = 0, maxY = 0;
                    nodes.forEach(node => {
                        const left = parseFloat(node.style.left);
                        const top = parseFloat(node.style.top);
                        if (left < minX) minX = left;
                        if (top < minY) minY = top;
                        if (left + node.offsetWidth > maxX) maxX = left + node.offsetWidth;
                        if (top + node.offsetHeight > maxY) maxY = top + node.offsetHeight;
                    });

                    const padding = 60;
                    const structureW = maxX - minX + padding * 2;
                    const structureH = maxY - minY + padding * 2;
                    const headerH = 80;
                    const totalW = structureW;
                    const totalH = structureH + headerH;

                    // Buat clone
                    const wrapper = document.getElementById('exportWrapper');
                    const clone = wrapper.cloneNode(true);
                    
                    clone.style.position = 'absolute';
                    clone.style.left = '-10000px';
                    clone.style.top = '0';
                    clone.style.width = totalW + 'px';
                    clone.style.height = totalH + 'px';
                    clone.style.background = '#f8fafc';
                    clone.style.overflow = 'visible';

                    // Geser semua node ke kiri agar minX = padding
                    const offsetX = padding - minX;
                    const offsetY = padding + headerH - minY;
                    
                    const cloneNodes = clone.querySelectorAll('.org-node');
                    cloneNodes.forEach(node => {
                        const left = parseFloat(node.style.left) + offsetX;
                        const top = parseFloat(node.style.top) + offsetY;
                        node.style.left = left + 'px';
                        node.style.top = top + 'px';
                    });

                    // Update SVG connector
                    const cloneSvg = clone.querySelector('#connectorSvg');
                    if (cloneSvg) {
                        cloneSvg.setAttribute('width', structureW);
                        cloneSvg.setAttribute('height', structureH + headerH);
                        
                        // Geser semua line di SVG
                        const lines = cloneSvg.querySelectorAll('line');
                        lines.forEach(line => {
                            ['x1', 'x2'].forEach(attr => {
                                const val = parseFloat(line.getAttribute(attr));
                                if (!isNaN(val)) line.setAttribute(attr, val + offsetX);
                            });
                            ['y1', 'y2'].forEach(attr => {
                                const val = parseFloat(line.getAttribute(attr));
                                if (!isNaN(val)) line.setAttribute(attr, val + offsetY);
                            });
                        });
                    }

                    // Update container
                    const cloneContainer = clone.querySelector('#structureContainer');
                    if (cloneContainer) {
                        cloneContainer.style.overflow = 'visible';
                        cloneContainer.style.height = (structureH + headerH) + 'px';
                        cloneContainer.style.width = structureW + 'px';
                    }

                    const cloneChart = clone.querySelector('#structureChart');
                    if (cloneChart) {
                        cloneChart.style.width = structureW + 'px';
                        cloneChart.style.height = (structureH + headerH) + 'px';
                        cloneChart.style.transform = 'scale(1)';
                        cloneChart.style.transformOrigin = '0 0';
                        cloneChart.style.position = 'relative';
                    }

                    // Center header
                    const cloneHeader = clone.querySelector('#structureHeader');
                    if (cloneHeader) {
                        cloneHeader.style.width = '100%';
                        cloneHeader.style.textAlign = 'center';
                        cloneHeader.style.padding = '20px 0 10px';
                    }

                    document.body.appendChild(clone);
                    await new Promise(r => setTimeout(r, 500));

                    const canvas = await html2canvas(clone, {
                        backgroundColor: '#f8fafc',
                        scale: 2,
                        useCORS: true,
                        allowTaint: true,
                        logging: false,
                        width: totalW,
                        height: totalH,
                        scrollX: 0,
                        scrollY: 0,
                        x: 0,
                        y: 0,
                    });

                    document.body.removeChild(clone);

                    const link = document.createElement('a');
                    link.download = `struktur-organisasi-${Date.now()}.png`;
                    link.href = canvas.toDataURL('image/png');
                    link.click();
                } catch (err) {
                    console.error(err);
                    toast('error', 'Gagal export: ' + err.message);
                } finally {
                    chart.style.transform = `scale(${savedZoom})`;
                    container.scrollLeft = savedScrollLeft;
                    container.scrollTop = savedScrollTop;
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ri-download-line me-1"></i>Export PNG';
                }
            });

            const toast = (type, msg) => {
                const el = document.createElement('div');
                el.className =
                    `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed bottom-0 end-0 m-3 shadow`;
                el.style.cssText = 'z-index:99999;min-width:260px;font-size:.88rem';
                el.innerHTML =
                    `<i class="ri-${type === 'success' ? 'checkbox-circle' : 'error-warning'}-fill me-2"></i>${msg}`;
                document.body.appendChild(el);
                setTimeout(() => el.remove(), 4500);
            };

            let resizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(fitToScreen, 250);
            });

            document.addEventListener('keydown', e => {
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
                if ((e.ctrlKey || e.metaKey) && e.key === '=') {
                    e.preventDefault();
                    applyZoom(currentZoom + ZOOM_STEP);
                }
                if ((e.ctrlKey || e.metaKey) && e.key === '-') {
                    e.preventDefault();
                    applyZoom(currentZoom - ZOOM_STEP);
                }
                if ((e.ctrlKey || e.metaKey) && e.key === '0') {
                    e.preventDefault();
                    fitToScreen();
                }
            });

            loadStructure();
        });
    </script>
@endsection