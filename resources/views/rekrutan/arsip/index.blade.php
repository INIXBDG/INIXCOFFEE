@php
    $layoutFile = $layout == 'hr'
        ? 'layout_HR.app'
        : 'layouts.app';

    $sectionName = $layout == 'hr'
        ? 'content_HR'
        : 'content';
@endphp

@extends($layoutFile)

@section($sectionName)

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        .arsip-container {
            border-radius: 16px;
            padding: 30px;
            min-height: 600px;
        }

        .arsip-header {
            margin-bottom: 30px;
        }

        .arsip-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .arsip-title i {
            color: #64748b;
            font-size: 1.8rem;
        }

        .arsip-subtitle {
            color: #64748b;
            font-size: 0.9rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 25px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
        }

        .stat-card-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin-bottom: 12px;
        }

        .stat-card-icon.folder {
            background: #fef3c7;
            color: #d97706;
        }

        .stat-card-icon.pelamar {
            background: #dbeafe;
            color: #2563eb;
        }

        .stat-card-icon.recent {
            background: #e0e7ff;
            color: #4f46e5;
        }

        .stat-card-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1e293b;
            line-height: 1;
        }

        .stat-card-label {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .arsip-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 25px;
            font-size: 0.9rem;
            flex-wrap: wrap;
        }

        .breadcrumb-item {
            cursor: pointer;
            padding: 6px 12px;
            border-radius: 6px;
            transition: all 0.15s;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .breadcrumb-item:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        .breadcrumb-item.active {
            color: #1e293b;
            font-weight: 600;
            cursor: default;
            background: #f1f5f9;
        }

        .breadcrumb-item.active:hover {
            background: #f1f5f9;
        }

        .breadcrumb-separator {
            color: #cbd5e1;
        }

        .filter-bar {
            background: white;
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            border: 1px solid #e2e8f0;
        }

        .search-input-wrapper {
            flex: 1;
            position: relative;
        }

        .search-input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .search-input {
            width: 100%;
            padding: 10px 15px 10px 42px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .search-input:focus {
            outline: none;
            border-color: #0f172a;
            box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.1);
        }

        .arsip-folder-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .arsip-folder-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            border: 2px solid #e2e8f0;
            transition: all 0.2s;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .arsip-folder-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #64748b, #94a3b8);
        }

        .arsip-folder-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            border-color: #0f172a;
        }

        .arsip-folder-card.drag-over {
            border-color: #22c55e !important;
            background: #f0fdf4 !important;
            transform: translateY(-3px);
        }

        .arsip-folder-card.dragging {
            opacity: 0.5;
        }

        .arsip-folder-card.drag-over-invalid {
            border-color: #ef4444 !important;
            background: #fef2f2 !important;
        }

        .arsip-folder-icon {
            width: 56px;
            height: 56px;
            background: #f1f5f9;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: #64748b;
            margin-bottom: 15px;
        }

        .arsip-folder-name {
            font-size: 1.05rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .arsip-folder-meta {
            font-size: 0.8rem;
            color: #64748b;
            margin-bottom: 15px;
        }

        .arsip-folder-stats {
            display: flex;
            gap: 15px;
            padding: 12px;
            background: #f8fafc;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .arsip-folder-stat {
            flex: 1;
            text-align: center;
        }

        .arsip-folder-stat-value {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1e293b;
        }

        .arsip-folder-stat-label {
            font-size: 0.7rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .arsip-folder-actions {
            display: flex;
            gap: 8px;
        }

        .arsip-btn {
            flex: 1;
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: white;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .arsip-btn.restore {
            background: #0f172a;
            color: white;
            border-color: #0f172a;
        }

        .arsip-btn.restore:hover {
            background: #1e293b;
        }

        .arsip-btn.delete {
            color: #475569;
        }

        .arsip-btn.delete:hover {
            background: #475569;
            color: white;
            border-color: #475569;
        }

        .section-title {
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .arsip-table-wrapper {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            border: 1px solid #e2e8f0;
        }

        .arsip-table {
            width: 100%;
            border-collapse: collapse;
        }

        .arsip-table thead th {
            background: #f8fafc;
            padding: 14px 18px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.5px;
            text-align: left;
            border-bottom: 2px solid #e2e8f0;
        }

        .arsip-table tbody tr {
            transition: all 0.15s;
            border-bottom: 1px solid #f1f5f9;
            cursor: move;
        }

        .arsip-table tbody tr:hover {
            background: #f8fafc;
        }

        .arsip-table tbody tr.dragging {
            opacity: 0.4;
        }

        .arsip-table tbody td {
            padding: 16px 18px;
            font-size: 0.9rem;
            vertical-align: middle;
        }

        .pelamar-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .pelamar-avatar-sm {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #64748b, #475569);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.85rem;
            flex-shrink: 0;
        }

        .pelamar-name {
            font-weight: 600;
            color: #1e293b;
        }

        .pelamar-email {
            font-size: 0.75rem;
            color: #64748b;
        }

        .rating-stars-sm {
            color: #f59e0b;
            font-size: 0.9rem;
        }

        .date-text {
            font-size: 0.85rem;
            color: #64748b;
        }

        .arsip-empty {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 12px;
            border: 2px dashed #e2e8f0;
        }

        .arsip-empty-icon {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 20px;
        }

        .arsip-empty h5 {
            color: #475569;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .arsip-empty p {
            color: #94a3b8;
            font-size: 0.9rem;
        }

        .detail-profile-header {
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
            padding: 30px;
            color: white;
            text-align: center;
            border-radius: 8px 8px 0 0;
            position: relative;
        }

        .detail-avatar-lg {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.25);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 2rem;
            margin: 0 auto 15px;
            border: 3px solid rgba(255, 255, 255, 0.4);
        }

        .detail-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .detail-position {
            font-size: 0.95rem;
            opacity: 0.9;
            margin-bottom: 15px;
        }

        .detail-section {
            padding: 20px 25px;
            border-bottom: 1px solid #e5e7eb;
        }

        .detail-section:last-child {
            border-bottom: none;
        }

        .detail-section-title {
            font-size: 0.8rem;
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .detail-item-label {
            font-size: 0.7rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 3px;
        }

        .detail-item-value {
            font-size: 0.9rem;
            color: #1e293b;
            font-weight: 500;
        }

        .penilaian-item {
            background: #f9fafb;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid #0f172a;
        }

        .penilaian-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .penilaian-interviewer {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.9rem;
        }

        .penilaian-date {
            font-size: 0.75rem;
            color: #64748b;
        }

        .penilaian-rating {
            color: #ffc107;
            font-size: 1rem;
            margin-bottom: 8px;
        }

        .penilaian-catatan {
            background: white;
            padding: 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            color: #4b5563;
            line-height: 1.5;
            margin-top: 8px;
        }

        .modal-confirm-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 20px;
        }

        .modal-confirm-icon.warning {
            background: #fef3c7;
            color: #d97706;
        }

        .modal-confirm-icon.danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .arsip-container::-webkit-scrollbar,
        .arsip-table-wrapper::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .arsip-container::-webkit-scrollbar-track,
        .arsip-table-wrapper::-webkit-scrollbar-track {
            background: transparent;
        }

        .arsip-container::-webkit-scrollbar-thumb,
        .arsip-table-wrapper::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.15);
            border-radius: 10px;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .filter-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .arsip-folder-grid {
                grid-template-columns: 1fr;
            }

            .detail-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="arsip-container">
        <div class="arsip-header">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h2 class="arsip-title">
                        <i class="fa fa-archive"></i>
                        <span>Arsip Rekrutmen</span>
                    </h2>
                    <p class="arsip-subtitle mb-0">Kelola folder yang telah diarsipkan</p>
                </div>
                <a href="{{ $backRoute }}" class="btn btn-primary">
                    <i class="fa fa-arrow-left me-3"></i> Kembali
                </a>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-icon folder">
                        <i class="fa fa-folder"></i>
                    </div>
                    <div class="stat-card-value" id="statTotalFolder">0</div>
                    <div class="stat-card-label">Folder Arsip</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon pelamar">
                        <i class="fa fa-users"></i>
                    </div>
                    <div class="stat-card-value" id="statTotalPelamar">0</div>
                    <div class="stat-card-label">Total Pelamar Terarsip</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon recent">
                        <i class="fa fa-clock"></i>
                    </div>
                    <div class="stat-card-value" id="statRecent">0</div>
                    <div class="stat-card-label">Arsip 30 Hari Terakhir</div>
                </div>
            </div>
        </div>

        <div class="arsip-breadcrumb" id="breadcrumb">
            <span class="breadcrumb-item active">
                <i class="fa fa-archive"></i> Root Arsip
            </span>
        </div>

        <div class="filter-bar">
            <div class="search-input-wrapper">
                <i class="fa fa-search"></i>
                <input type="text" class="search-input" id="searchInput" placeholder="Cari nama folder...">
            </div>
        </div>

        <div id="contentArea">
            <div class="text-center py-5">
                <i class="fa fa-spinner fa-spin fa-3x text-muted mb-3"></i>
                <p class="text-muted">Memuat data arsip...</p>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetailPelamar" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" id="detailContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-dark" role="status"></div>
                    <p class="mt-2 text-muted">Memuat detail pelamar...</p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalRestore" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="modal-confirm-icon warning">
                        <i class="fa fa-undo"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Pulihkan dari Arsip?</h5>
                    <p class="text-muted mb-4" id="restoreMessage">
                        Item yang dipulihkan akan kembali aktif dan dapat diakses kembali.
                    </p>
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-dark" id="btnConfirmRestore">
                            <i class="fa fa-check"></i> Ya, Pulihkan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDelete" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="modal-confirm-icon danger">
                        <i class="fa fa-trash"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Hapus Permanen?</h5>
                    <p class="text-muted mb-4" id="deleteMessage">
                        Tindakan ini tidak dapat dibatalkan. Semua data terkait akan dihapus selamanya.
                    </p>
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-dark" id="btnConfirmDelete">
                            <i class="fa fa-trash"></i> Ya, Hapus Permanen
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // ===== STATE MANAGEMENT =====
        let arsipFolders = [];
        let currentFolderId = null;
        let currentPelamars = [];
        let draggedPelamarId = null;
        let draggedFolderId = null;

        $(document).ready(function() {
            loadArsipData();

            // Search dengan debounce
            let searchTimeout;
            $('#searchInput').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    renderContent();
                }, 300);
            });
        });

        // ===== DATA LOADING =====
        function loadArsipData() {
            $('#contentArea').html(`
                <div class="text-center py-5">
                    <i class="fa fa-spinner fa-spin fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Memuat data arsip...</p>
                </div>
            `);

            $.ajax({
                type: 'GET',
                url: "{{ route('HR.arsip.folders') }}",
                success: function(response) {
                    if (response.success) {
                        arsipFolders = response.data || [];
                        updateStats();
                        renderBreadcrumb();
                        renderContent();
                    }
                },
                error: function() {
                    arsipFolders = [];
                    updateStats();
                    renderContent();
                    showToast('Gagal memuat data folder', 'error');
                }
            });
        }

        function loadPelamarByFolder(folderId) {
            $('#contentArea').html(`
                <div class="text-center py-5">
                    <i class="fa fa-spinner fa-spin fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Memuat pelamar...</p>
                </div>
            `);

            $.ajax({
                type: 'GET',
                url: `/HR-dashboard/arsip/folders/${folderId}/pelamar`,
                success: function(response) {
                    if (response.success) {
                        currentPelamars = response.data || [];
                        renderContent();
                    } else {
                        currentPelamars = [];
                        renderContent();
                    }
                },
                error: function() {
                    currentPelamars = [];
                    renderContent();
                    showToast('Gagal memuat pelamar folder', 'error');
                }
            });
        }

        // ===== STATS & UI =====
        function updateStats() {
            const rootFoldersCount = arsipFolders.filter(f =>
                f.parent_id == null || f.parent_id == 0 || f.parent_id === ''
            ).length;

            $('#statTotalFolder').text(rootFoldersCount);

            const totalPelamar = arsipFolders.reduce((sum, f) => sum + (f.pelamars_count || 0), 0);
            $('#statTotalPelamar').text(totalPelamar);

            const thirtyDaysAgo = new Date();
            thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);

            let recentCount = 0;
            arsipFolders.forEach(f => {
                if (new Date(f.updated_at) >= thirtyDaysAgo) recentCount++;
            });
            $('#statRecent').text(recentCount);
        }

        // ===== NAVIGATION =====
        function openFolder(folderId) {
            currentFolderId = folderId;
            currentPelamars = [];
            renderBreadcrumb();
            loadPelamarByFolder(folderId);
        }

        function goToRoot() {
            currentFolderId = null;
            currentPelamars = [];
            renderBreadcrumb();
            renderContent();
        }

        function renderBreadcrumb() {
            const breadcrumb = $('#breadcrumb');
            breadcrumb.empty();

            if (currentFolderId === null) {
                breadcrumb.append(`
                    <span class="breadcrumb-item active">
                        <i class="fa fa-archive"></i> Root Arsip
                    </span>
                `);
                return;
            }

            const path = [];
            let current = arsipFolders.find(f => f.id == currentFolderId);

            while (current) {
                path.unshift(current);
                if (current.parent_id && current.parent_id != 0) {
                    current = arsipFolders.find(f => f.id == current.parent_id);
                } else {
                    current = null;
                }
            }

            breadcrumb.append(`
                <span class="breadcrumb-item" onclick="goToRoot()">
                    <i class="fa fa-archive"></i> Root Arsip
                </span>
            `);

            path.forEach((folder, index) => {
                breadcrumb.append(`<span class="breadcrumb-separator"><i class="fa fa-chevron-right"></i></span>`);

                const isLast = index === path.length - 1;
                const escapedName = folder.nama.replace(/'/g, "\\'").replace(/"/g, '&quot;');

                breadcrumb.append(`
                    <span class="breadcrumb-item ${isLast ? 'active' : ''}" 
                          onclick="${isLast ? '' : `openFolder(${folder.id})`}">
                        <i class="fa fa-folder"></i> ${folder.nama}
                    </span>
                `);
            });
        }

        // ===== RENDER CONTENT =====
        function renderContent() {
            const container = $('#contentArea');
            const searchTerm = $('#searchInput').val().toLowerCase().trim();

            if (currentFolderId === null) {
                renderFolderGrid(container, searchTerm);
            } else {
                renderFolderDetail(container, searchTerm);
            }
        }

        function renderFolderGrid(container, searchTerm) {
            let rootFolders = arsipFolders.filter(f =>
                f.parent_id == null || f.parent_id == 0 || f.parent_id === ''
            );

            if (searchTerm) {
                rootFolders = rootFolders.filter(f =>
                    f.nama.toLowerCase().includes(searchTerm)
                );
            }

            if (rootFolders.length === 0) {
                container.html(`
                    <div class="arsip-empty">
                        <div class="arsip-empty-icon"><i class="fa fa-folder-open"></i></div>
                        <h5>${searchTerm ? 'Tidak ditemukan' : 'Tidak ada folder arsip'}</h5>
                        <p>${searchTerm ? 'Coba kata kunci pencarian lain' : 'Folder yang diarsipkan akan muncul di sini'}</p>
                    </div>
                `);
                return;
            }

            let cards = '';
            rootFolders.forEach(folder => {
                cards += renderFolderCard(folder);
            });

            container.html(`
                <div class="section-title mb-3"><i class="fa fa-folder"></i> Folder Arsip - ${rootFolders.length} folder</div>
                <div class="arsip-folder-grid">${cards}</div>
            `);
            setupDragDrop();
        }

        function renderFolderDetail(container, searchTerm) {
            const folder = arsipFolders.find(f => f.id == currentFolderId);
            if (!folder) {
                container.html(`
                    <div class="arsip-empty">
                        <div class="arsip-empty-icon"><i class="fa fa-exclamation-circle"></i></div>
                        <h5>Folder tidak ditemukan</h5>
                        <p>Folder mungkin sudah dihapus atau dipulihkan</p>
                    </div>
                `);
                return;
            }

            let subfolders = arsipFolders.filter(f => f.parent_id == currentFolderId);

            if (searchTerm) {
                subfolders = subfolders.filter(f => f.nama.toLowerCase().includes(searchTerm));
            }

            let html = '';

            if (subfolders.length > 0) {
                html += `<div class="section-title"><i class="fa fa-folder"></i> Subfolder (${subfolders.length})</div>`;
                html += '<div class="arsip-folder-grid">';
                subfolders.forEach(subfolder => {
                    html += renderFolderCard(subfolder);
                });
                html += '</div>';
            }

            let filteredPelamars = currentPelamars;
            if (searchTerm) {
                filteredPelamars = currentPelamars.filter(item =>
                    item.pelamar && (item.pelamar.nama_lengkap || '').toLowerCase().includes(searchTerm)
                );
            }

            if (filteredPelamars.length > 0) {
                if (subfolders.length > 0) {
                    html += '<hr class="my-4">';
                }
                html += `<div class="section-title"><i class="fa fa-users"></i> Pelamar (${filteredPelamars.length})</div>`;
                html += renderPelamarTable(filteredPelamars);
            } else if (subfolders.length === 0) {
                html = `
                    <div class="arsip-empty">
                        <div class="arsip-empty-icon"><i class="fa fa-folder-open"></i></div>
                        <h5>Folder Kosong</h5>
                        <p>Tidak ada subfolder atau pelamar di folder ini</p>
                    </div>
                `;
            }

            container.html(html);
            setupDragDrop();
        }

        function renderFolderCard(folder) {
            const tanggal = new Date(folder.updated_at).toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });
            const escapedName = folder.nama.replace(/'/g, "\\'").replace(/"/g, '&quot;');
            const childCount = arsipFolders.filter(f => f.parent_id == folder.id).length;
            const pelamarCount = folder.pelamars_count || 0;

            return `
                <div class="arsip-folder-card" 
                     data-folder-id="${folder.id}" 
                     draggable="true"
                     onclick="openFolder(${folder.id})">
                    <div class="arsip-folder-icon">
                        <i class="fa fa-folder"></i>
                    </div>
                    <div class="arsip-folder-name" title="${folder.nama}">${folder.nama}</div>
                    <div class="arsip-folder-meta">
                        <i class="fa fa-clock"></i> Diarsipkan: ${tanggal}
                    </div>
                    <div class="arsip-folder-stats">
                        <div class="arsip-folder-stat">
                            <div class="arsip-folder-stat-value">${pelamarCount}</div>
                            <div class="arsip-folder-stat-label">Pelamar</div>
                        </div>
                        <div class="arsip-folder-stat">
                            <div class="arsip-folder-stat-value">${childCount}</div>
                            <div class="arsip-folder-stat-label">Subfolder</div>
                        </div>
                    </div>
                    <div class="arsip-folder-actions" onclick="event.stopPropagation()">
                        <button class="arsip-btn restore" onclick="confirmRestoreFolder(${folder.id}, '${escapedName}')">
                            <i class="fa fa-undo"></i> Pulihkan
                        </button>
                        <button class="arsip-btn delete" onclick="confirmDeleteFolder(${folder.id}, '${escapedName}')">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        }

        function renderPelamarTable(pelamars) {
            let rows = '';
            pelamars.forEach((item, index) => {
                const p = item.pelamar;
                if (!p) return;

                const inisial = getInitial(p.nama_lengkap);
                const tanggal = item.diarsipkan ? new Date(item.diarsipkan).toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric'
                }) : '-';

                const ratingHtml = item.rating ?
                    `<div class="rating-stars-sm">${'★'.repeat(item.rating)}${'☆'.repeat(4 - item.rating)}</div>` :
                    '<span class="text-muted">-</span>';

                const escapedName = (p.nama_lengkap || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');

                rows += `
                    <tr draggable="true" data-pelamar-id="${p.id}">
                        <td style="width: 50px;">${index + 1}</td>
                        <td>
                            <div class="pelamar-info">
                                <div class="pelamar-avatar-sm">${inisial}</div>
                                <div>
                                    <div class="pelamar-name">${p.nama_lengkap || '-'}</div>
                                    <div class="pelamar-email">${p.email || '-'}</div>
                                </div>
                            </div>
                        </td>
                        <td>${p.jabatan || '-'}</td>
                        <td>${p.divisi || '-'}</td>
                        <td>${ratingHtml}</td>
                        <td><span class="date-text">${tanggal}</span></td>
                        <td style="width: 180px;">
                            <div class="d-flex gap-1">
                                <button class="arsip-btn" style="padding: 6px 10px; font-size: 0.8rem;"
                                    onclick="showDetailPelamar(${p.id})" title="Detail">
                                    <i class="fa fa-eye"></i>
                                </button>
                                <button class="arsip-btn restore" style="padding: 6px 10px; font-size: 0.8rem;"
                                    onclick="confirmRestorePelamar(${item.id}, '${escapedName}')">
                                    <i class="fa fa-undo"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });

            return `
                <div class="arsip-table-wrapper">
                    <table class="arsip-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Nama Pelamar</th>
                                <th>Posisi</th>
                                <th>Divisi</th>
                                <th>Rating</th>
                                <th>Tanggal Arsip</th>
                                <th style="width: 180px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            `;
        }

        function getInitial(name) {
            if (!name) return '?';
            const parts = name.trim().split(' ');
            return (parts[0][0] + (parts[1] ? parts[1][0] : '')).toUpperCase();
        }

        // ===== DRAG & DROP =====
        function setupDragDrop() {
            // Hapus handler lama untuk mencegah duplicate
            $('.arsip-table tbody tr').off('dragstart dragend');
            $('.arsip-folder-card').off('dragstart dragend dragover dragleave drop');

            // Drag pelamar
            $('.arsip-table tbody tr').on('dragstart', function(e) {
                draggedPelamarId = $(this).data('pelamar-id');
                draggedFolderId = null;
                e.originalEvent.dataTransfer.setData('type', 'pelamar');
                e.originalEvent.dataTransfer.setData('pelamar_id', draggedPelamarId);
                e.originalEvent.dataTransfer.effectAllowed = 'move';
                $(this).addClass('dragging');
            });

            $('.arsip-table tbody tr').on('dragend', function() {
                $(this).removeClass('dragging');
                $('.arsip-folder-card').removeClass('drag-over drag-over-invalid');
                draggedPelamarId = null;
            });

            // Drag folder
            $('.arsip-folder-card').on('dragstart', function(e) {
                draggedFolderId = $(this).data('folder-id');
                draggedPelamarId = null;
                e.originalEvent.dataTransfer.setData('type', 'folder');
                e.originalEvent.dataTransfer.setData('folder_id', draggedFolderId);
                e.originalEvent.dataTransfer.effectAllowed = 'move';
                $(this).addClass('dragging');
                e.stopPropagation();
            });

            $('.arsip-folder-card').on('dragend', function() {
                $(this).removeClass('dragging');
                $('.arsip-folder-card').removeClass('drag-over drag-over-invalid');
                draggedFolderId = null;
            });

            // Drop target: folder card
            $('.arsip-folder-card').on('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const targetFolderId = $(this).data('folder-id');

                if (draggedFolderId) {
                    // Jangan drop folder ke diri sendiri atau ke child-nya
                    if (targetFolderId == draggedFolderId || isChildFolder(draggedFolderId, targetFolderId)) {
                        $(this).addClass('drag-over-invalid');
                        $(this).removeClass('drag-over');
                    } else {
                        $(this).addClass('drag-over');
                        $(this).removeClass('drag-over-invalid');
                    }
                } else if (draggedPelamarId) {
                    // Jangan drop pelamar ke folder yang sama
                    if (targetFolderId == currentFolderId) {
                        $(this).addClass('drag-over-invalid');
                        $(this).removeClass('drag-over');
                    } else {
                        $(this).addClass('drag-over');
                        $(this).removeClass('drag-over-invalid');
                    }
                }
            });

            $('.arsip-folder-card').on('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('drag-over drag-over-invalid');
            });

            $('.arsip-folder-card').on('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('drag-over drag-over-invalid');

                const targetFolderId = $(this).data('folder-id');
                const dragType = e.originalEvent.dataTransfer.getData('type');

                if (dragType === 'folder' && draggedFolderId) {
                    if (targetFolderId == draggedFolderId) {
                        showToast('Folder tidak bisa dipindahkan ke dirinya sendiri', 'error');
                        return;
                    }
                    if (isChildFolder(draggedFolderId, targetFolderId)) {
                        showToast('Folder tidak bisa dipindahkan ke dalam child-nya sendiri', 'error');
                        return;
                    }
                    moveFolderArsip(draggedFolderId, targetFolderId);
                } else if (dragType === 'pelamar' && draggedPelamarId) {
                    if (targetFolderId == currentFolderId) {
                        showToast('Pelamar sudah berada di folder ini', 'error');
                        return;
                    }
                    movePelamarToFolder(draggedPelamarId, targetFolderId);
                }

                draggedFolderId = null;
                draggedPelamarId = null;
            });
        }

        function isChildFolder(parentFolderId, targetFolderId) {
            function checkChildren(folderId) {
                const children = arsipFolders.filter(f => f.parent_id == folderId);
                for (let child of children) {
                    if (child.id == targetFolderId) return true;
                    if (checkChildren(child.id)) return true;
                }
                return false;
            }
            return checkChildren(parentFolderId);
        }

        function moveFolderArsip(folderId, newParentId) {
            $.ajax({
                type: 'POST',
                url: `/HR-dashboard/arsip/folders/${folderId}/move`,
                data: {
                    _token: '{{ csrf_token() }}',
                    parent_id: newParentId
                },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        loadArsipData();
                    } else {
                        showToast(response.message || 'Gagal memindahkan folder', 'error');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    showToast(response?.message || 'Terjadi kesalahan', 'error');
                }
            });
        }

        function movePelamarToFolder(pelamarId, folderId) {
            $.ajax({
                type: 'POST',
                url: "{{ route('HR.arsip.pelamar.move') }}",
                data: {
                    _token: '{{ csrf_token() }}',
                    pelamar_id: pelamarId,
                    folder_id: folderId
                },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        loadArsipData();
                        if (currentFolderId) {
                            loadPelamarByFolder(currentFolderId);
                        }
                    } else {
                        showToast(response.message || 'Gagal memindahkan', 'error');
                    }
                },
                error: function() {
                    showToast('Terjadi kesalahan saat memindahkan', 'error');
                }
            });
        }

        // ===== MODALS =====
        function showDetailPelamar(pelamarId) {
            const modal = new bootstrap.Modal(document.getElementById('modalDetailPelamar'));

            $('#detailContent').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-dark" role="status"></div>
                    <p class="mt-2 text-muted">Memuat detail pelamar...</p>
                </div>
            `);
            modal.show();

            $.ajax({
                type: 'GET',
                url: `/HR-dashboard/folders/pelamar/${pelamarId}/detail`,
                success: function(response) {
                    if (response.success) {
                        renderDetailContent(response.data);
                    } else {
                        $('#detailContent').html(
                            '<div class="text-center py-5 text-muted">Gagal memuat data</div>');
                    }
                },
                error: function() {
                    $('#detailContent').html(
                        '<div class="text-center py-5 text-muted">Gagal memuat data</div>');
                }
            });
        }

        function renderDetailContent(data) {
            const p = data.pelamar;
            const inisial = data.inisial;

            const keahlianHtml = (p.keahlian && p.keahlian.length > 0) ?
                p.keahlian.map(k => `<span class="badge bg-secondary me-1">${k}</span>`).join('') :
                '<span class="text-muted">-</span>';

            let penilaianHtml = '';
            if (data.penilaians && data.penilaians.length > 0) {
                data.penilaians.forEach(pf => {
                    const interviewerName = pf.interviewer ? pf.interviewer.name : 'Unknown';
                    const folderName = pf.folder ? pf.folder.nama : '-';
                    const ratingStars = pf.rating ? '★'.repeat(pf.rating) + '☆'.repeat(4 - pf.rating) :
                        'Belum dinilai';
                    const tanggal = pf.tanggal_dinilai ? new Date(pf.tanggal_dinilai).toLocaleDateString('id-ID', {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric'
                    }) : '-';

                    penilaianHtml += `
                        <div class="penilaian-item">
                            <div class="penilaian-header">
                                <div class="penilaian-interviewer">
                                    <i class="fa fa-user-circle"></i> ${interviewerName}
                                    <small class="text-muted">(${folderName})</small>
                                </div>
                                <div class="penilaian-date"><i class="fa fa-clock"></i> ${tanggal}</div>
                            </div>
                            <div class="penilaian-rating">${ratingStars}</div>
                            ${pf.catatan ? `<div class="penilaian-catatan"><strong>Catatan:</strong><br>${pf.catatan}</div>` : ''}
                        </div>
                    `;
                });
            } else {
                penilaianHtml = '<p class="text-muted text-center py-3">Belum ada penilaian</p>';
            }

            const tanggalLahir = p.tanggal_lahir ? new Date(p.tanggal_lahir).toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            }) + (data.usia ? ` (${data.usia} tahun)` : '') : '-';

            const tanggalMelamar = p.tanggal_melamar ? new Date(p.tanggal_melamar).toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            }) : '-';

            $('#detailContent').html(`
                <div class="detail-profile-header">
                    <div class="detail-avatar-lg">${inisial}</div>
                    <div class="detail-name">${p.nama_lengkap || '-'}</div>
                    <div class="detail-position">${p.jabatan || '-'} · ${p.divisi || '-'}</div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" 
                            style="position: absolute; top: 15px; right: 15px;"></button>
                </div>

                <div style="max-height: 60vh; overflow-y: auto;">
                    <div class="detail-section">
                        <div class="detail-section-title"><i class="fa fa-user"></i> Informasi Pribadi</div>
                        <div class="detail-grid">
                            <div>
                                <div class="detail-item-label">Tanggal Lahir</div>
                                <div class="detail-item-value">${tanggalLahir}</div>
                            </div>
                            <div>
                                <div class="detail-item-label">Email</div>
                                <div class="detail-item-value">${p.email || '-'}</div>
                            </div>
                            <div>
                                <div class="detail-item-label">Telepon</div>
                                <div class="detail-item-value">${p.no_telepon || '-'}</div>
                            </div>
                            <div>
                                <div class="detail-item-label">Domisili</div>
                                <div class="detail-item-value">${p.domisili || '-'}</div>
                            </div>
                        </div>
                    </div>

                    <div class="detail-section">
                        <div class="detail-section-title"><i class="fa fa-briefcase"></i> Informasi Lamaran</div>
                        <div class="detail-grid">
                            <div>
                                <div class="detail-item-label">Posisi</div>
                                <div class="detail-item-value">${p.jabatan || '-'}</div>
                            </div>
                            <div>
                                <div class="detail-item-label">Divisi</div>
                                <div class="detail-item-value">${p.divisi || '-'}</div>
                            </div>
                            <div>
                                <div class="detail-item-label">Tanggal Melamar</div>
                                <div class="detail-item-value">${tanggalMelamar}</div>
                            </div>
                            <div>
                                <div class="detail-item-label">Tahapan</div>
                                <div class="detail-item-value"><span class="badge bg-dark">${data.tahap_label || '-'}</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="detail-section">
                        <div class="detail-section-title"><i class="fa fa-code"></i> Keahlian</div>
                        <div>${keahlianHtml}</div>
                    </div>

                    <div class="detail-section">
                        <div class="detail-section-title"><i class="fa fa-star"></i> Hasil Penilaian</div>
                        ${penilaianHtml}
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            `);
        }

        // ===== CONFIRM ACTIONS =====
        let pendingAction = null;

        function confirmRestoreFolder(folderId, folderName) {
            pendingAction = {
                type: 'restoreFolder',
                id: folderId
            };
            $('#restoreMessage').text(
                `Folder "${folderName}" akan dipulihkan dan dapat diakses kembali. Pelamar di dalamnya juga akan ikut dipulihkan.`
                );
            new bootstrap.Modal(document.getElementById('modalRestore')).show();
        }

        function confirmDeleteFolder(folderId, folderName) {
            pendingAction = {
                type: 'deleteFolder',
                id: folderId
            };
            $('#deleteMessage').text(
                `Folder "${folderName}" beserta semua relasi pelamar di dalamnya akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.`
                );
            new bootstrap.Modal(document.getElementById('modalDelete')).show();
        }

        function confirmRestorePelamar(pfId, nama) {
            pendingAction = {
                type: 'restorePelamar',
                id: pfId
            };
            $('#restoreMessage').text(`"${nama}" akan dikeluarkan dari folder arsip.`);
            new bootstrap.Modal(document.getElementById('modalRestore')).show();
        }

        $('#btnConfirmRestore').on('click', function() {
            if (!pendingAction) return;

            const btn = $(this);
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memproses...');

            let url = '';
            let method = 'POST';

            if (pendingAction.type === 'restoreFolder') {
                url = `/HR-dashboard/arsip/folders/${pendingAction.id}/restore`;
            } else if (pendingAction.type === 'restorePelamar') {
                url = `/HR-dashboard/arsip/pelamar/${pendingAction.id}/restore`;
                method = 'DELETE';
            }

            $.ajax({
                type: method,
                url: url,
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        bootstrap.Modal.getInstance(document.getElementById('modalRestore')).hide();
                        showToast(response.message, 'success');

                        if (pendingAction.type === 'restoreFolder' && currentFolderId == pendingAction
                            .id) {
                            goToRoot();
                        }

                        loadArsipData();
                        if (currentFolderId) {
                            loadPelamarByFolder(currentFolderId);
                        }
                    } else {
                        showToast(response.message || 'Gagal memulihkan', 'error');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    showToast(response?.message || 'Terjadi kesalahan', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fa fa-check"></i> Ya, Pulihkan');
                    pendingAction = null;
                }
            });
        });

        $('#btnConfirmDelete').on('click', function() {
            if (!pendingAction || pendingAction.type !== 'deleteFolder') return;

            const btn = $(this);
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menghapus...');

            $.ajax({
                type: 'DELETE',
                url: `/HR-dashboard/arsip/folders/${pendingAction.id}`,
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        bootstrap.Modal.getInstance(document.getElementById('modalDelete')).hide();
                        showToast(response.message, 'success');
                        if (currentFolderId == pendingAction.id) {
                            goToRoot();
                        }
                        loadArsipData();
                    } else {
                        showToast(response.message || 'Gagal menghapus', 'error');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    showToast(response?.message || 'Terjadi kesalahan', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fa fa-trash"></i> Ya, Hapus Permanen');
                    pendingAction = null;
                }
            });
        });

        // ===== TOAST =====
        function showToast(message, type = 'success') {
            const isSuccess = type === 'success';
            const bgClass = isSuccess ? 'background: #0f172a; color: white;' : 'background: #dc2626; color: white;';
            const icon = isSuccess ? 'check-circle' : 'exclamation-circle';

            const toast = $(`
                <div style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; 
                            padding: 15px 20px; border-radius: 10px; box-shadow: 0 8px 20px rgba(0,0,0,0.15);
                            ${bgClass} font-weight: 600; display: flex; align-items: center; gap: 10px;">
                    <i class="fa fa-${icon}"></i>
                    <span>${message}</span>
                </div>
            `);
            $('body').append(toast);
            setTimeout(() => toast.fadeOut(300, function() {
                $(this).remove();
            }), 3000);
        }
    </script>
@endsection
