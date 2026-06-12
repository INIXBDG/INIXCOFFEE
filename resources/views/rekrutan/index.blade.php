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

        /* ===== SIDEBAR ===== */
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

        /* Drag and drop styles untuk folder */
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

        /* ===== MAIN CONTENT ===== */
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

        /* ===== DETAIL MODAL STYLES ===== */
        .detail-profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

        .detail-contact {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
            font-size: 0.85rem;
        }

        .detail-contact span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
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
            color: #0062ff;
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
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 3px;
        }

        .detail-item-value {
            font-size: 0.9rem;
            color: #1f2937;
            font-weight: 500;
        }

        /* Penilaian List */
        .penilaian-item {
            background: #f9fafb;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid #0062ff;
        }

        .penilaian-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .penilaian-interviewer {
            font-weight: 600;
            color: #1f2937;
            font-size: 0.9rem;
        }

        .penilaian-date {
            font-size: 0.75rem;
            color: #6b7280;
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

        .penilaian-file {
            margin-top: 8px;
        }

        .penilaian-file a {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #0062ff;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .penilaian-file a:hover {
            text-decoration: underline;
        }

        /* Average Rating Box */
        .avg-rating-box {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            margin-bottom: 15px;
        }

        .avg-rating-value {
            font-size: 2rem;
            font-weight: 700;
            color: #92400e;
            line-height: 1;
        }

        .avg-rating-label {
            font-size: 0.75rem;
            color: #78350f;
            margin-top: 5px;
        }

        .avg-rating-stars {
            color: #f59e0b;
            font-size: 1.2rem;
            margin-top: 5px;
        }

        /* CV Viewer */
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

        /* Skill tag */
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

            .detail-grid {
                grid-template-columns: 1fr;
            }
        }

        .subfolder-card {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .subfolder-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 98, 255, 0.15);
        }

        .subfolders-section h6 {
            font-weight: 700;
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

            <div class="btn-group" role="group" aria-label="Basic example">
                <a href="{{ route('HR.arsip.index', ['layout' => 'app']) }}"
                    class="btn btn-secondary me-3">
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
                            onclick="selectFolder('inbox', 'Inbox')">
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

            {{-- ===== MAIN CONTENT ===== --}}
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

    {{-- Modal Create Folder --}}
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

    {{-- Modal Rename Folder --}}
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

    {{-- Modal Penilaian --}}
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

    {{-- ===== MODAL DETAIL PELAMAR ===== --}}
    <div class="modal fade" id="modalDetailPelamar" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" id="detailContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Memuat detail pelamar...</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== MODAL CV VIEWER ===== --}}
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
                }
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
                }
            });
        }

        // ===== RENDER SIDEBAR =====
        function renderSidebar() {
            const tree = $('#folderTreeMain');
            tree.empty();
            renderFolderTree(allFolders, null, tree);
            updateParentFolderSelect();
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
                const escapedName = folder.nama.replace(/'/g, "\\'").replace(/"/g, '&quot;');

                const li = $(`
                    <li class="folder-tree-item">
                        <a class="folder-tree-link ${folder.is_pinned ? 'pinned' : ''}" 
                           data-folder-id="${folder.id}" 
                           data-parent-id="${folder.parent_id || ''}"
                           draggable="true"
                           onclick="selectFolder(${folder.id}, '${escapedName}')">
                            ${hasChildren ? '<span class="folder-tree-toggle" onclick="event.stopPropagation(); toggleFolderChildren(this, ' + folder.id + ')"><i class="fa fa-chevron-right"></i></span>' : '<span style="width: 21px; display: inline-block;"></span>'}
                            <i class="fa fa-folder folder-icon"></i>
                            ${folder.is_pinned ? '<i class="fa fa-thumbtack pin-indicator"></i>' : ''}
                            <span class="folder-label" title="${folder.nama}">${folder.nama}</span>
                            <span class="folder-count-badge">${count}</span>
                            <span class="folder-actions-inline">
                                <button class="folder-action-mini" onclick="event.stopPropagation(); togglePin(${folder.id})" title="Pin/Unpin">
                                    <i class="fa fa-thumbtack"></i>
                                </button>
                                <button class="folder-action-mini" onclick="event.stopPropagation(); showRenameModal(${folder.id}, '${escapedName}')" title="Rename">
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
                    const nestedContainer = $(`#folder-children-${folder.id}`);
                    renderFolderTree(folders, folder.id, nestedContainer);
                }
            });
        }

        function toggleFolderChildren(toggleEl, folderId) {
            const nested = $(`#folder-children-${folderId}`);
            const toggle = $(toggleEl);
            nested.slideToggle(200);
            toggle.toggleClass('expanded');
        }

        function updateParentFolderSelect() {
            const select = $('#parentFolderSelect');
            select.find('option:not(:first)').remove();

            function addOptions(folders, parentId, level = 0) {
                const filtered = folders.filter(f => {
                    if (parentId == null) return f.parent_id == null || f.parent_id == 0 || f.parent_id === '';
                    return f.parent_id == parentId;
                });

                filtered.forEach(folder => {
                    const indent = '—'.repeat(level) + ' ';
                    select.append(`<option value="${folder.id}">${indent}${folder.nama}</option>`);
                    addOptions(allFolders, folder.id, level + 1);
                });
            }

            addOptions(allFolders, null);
        }

        function selectFolder(folderId, folderName) {
            currentFolderId = folderId;
            currentFolderName = folderName;

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
            nested.prev('li').find('.folder-tree-toggle').addClass('expanded');

            expandParentFolders(folder.parent_id);
        }

        function renderBreadcrumb(folderId) {
            const breadcrumb = $('#breadcrumb');
            breadcrumb.empty();

            if (folderId === 'inbox') {
                breadcrumb.append(`
                    <span class="breadcrumb-item active">
                        <i class="fa fa-home"></i> Pelamar
                    </span>
                `);
                return;
            }

            const path = [];
            let current = allFolders.find(f => f.id == folderId);

            while (current) {
                path.unshift(current);
                current = current.parent_id ? allFolders.find(f => f.id == current.parent_id) : null;
            }

            breadcrumb.append(`
                <span class="breadcrumb-item" onclick="selectFolder('inbox', 'Inbox')">
                    <i class="fa fa-home"></i> Pelamar
                </span>
            `);

            path.forEach((folder, index) => {
                breadcrumb.append(`<span class="breadcrumb-separator"><i class="fa fa-chevron-right"></i></span>`);

                const isLast = index === path.length - 1;
                const escapedName = folder.nama.replace(/'/g, "\\'");

                breadcrumb.append(`
                    <span class="breadcrumb-item ${isLast ? 'active' : ''}" 
                          onclick="${isLast ? '' : `selectFolder(${folder.id}, '${escapedName}')`}">
                        <i class="fa fa-folder"></i> ${folder.nama}
                    </span>
                `);
            });
        }

        // ===== RENDER CONTENT =====
        function renderContent() {
            const container = $('#contentTableContainer');
            const title = $('#contentTitle');
            const subtitle = $('#contentSubtitle');

            if (currentFolderId === 'inbox') {
                title.html('<i class="fa fa-inbox"></i> <span>Pelamar</span>').addClass('inbox-title');
                subtitle.text('Pelamar yang belum masuk folder manapun');
                renderInboxTable(container);
            } else {
                const folder = allFolders.find(f => f.id == currentFolderId);
                if (!folder) {
                    container.html(
                        '<div class="empty-state"><i class="fa fa-folder-open"></i><h5>Folder tidak ditemukan</h5></div>'
                    );
                    return;
                }

                title.html(`<i class="fa fa-folder"></i> <span>${folder.nama}</span>`).removeClass('inbox-title');
                subtitle.text(`${folder.pelamars.length} pelamar dalam folder ini`);
                renderFolderTable(container, folder);
            }
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

            let rows = '';
            unassignedPelamar.forEach((p, index) => {
                const inisial = getInitial(p.nama_lengkap);
                const tanggal = p.tanggal_melamar ? new Date(p.tanggal_melamar).toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric'
                }) : '-';

                rows += `
                    <tr draggable="true" data-pelamar-id="${p.id}">
                        <td style="width: 50px;">${index + 1}</td>
                        <td>
                            <div class="pelamar-name-cell">
                                <div class="pelamar-avatar">${inisial}</div>
                                <div>
                                    <div class="pelamar-name-text">${p.nama_lengkap}</div>
                                    <div class="pelamar-email-text">${p.email || '-'}</div>
                                </div>
                            </div>
                        </td>
                        <td>${p.jabatan || '-'}</td>
                        <td>${p.divisi || '-'}</td>
                        <td><span class="badge bg-secondary">${p.sumber_lamaran || '-'}</span></td>
                        <td>${tanggal}</td>
                        <td style="width: 180px;">
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-outline-primary" onclick="showDetailPelamar(${p.id})" title="Detail">
                                    <i class="fa fa-eye"></i>
                                </button>
                                ${p.cv_path ? `<button class="btn btn-sm btn-outline-danger" onclick="showCV(${p.id}, '${p.nama_lengkap.replace(/'/g, "\\'")}')" title="Lihat CV">
                                                    <i class="fa fa-file-pdf"></i>
                                                </button>` : ''}
                            </div>
                        </td>
                    </tr>
                `;
            });

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

        function showDetailPelamar(pelamarId) {
            const modal = new bootstrap.Modal(document.getElementById('modalDetailPelamar'));

            $('#detailContent').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
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
                            '<div class="text-center py-5 text-danger">Gagal memuat data</div>');
                    }
                },
                error: function() {
                    $('#detailContent').html(
                        '<div class="text-center py-5 text-danger">Gagal memuat data</div>');
                }
            });
        }

        function renderDetailContent(data) {
            const p = data.pelamar;
            const inisial = data.inisial;

            // Render keahlian
            const keahlianHtml = (p.keahlian && p.keahlian.length > 0) ?
                p.keahlian.map(k => `<span class="skill-tag">${k}</span>`).join('') :
                '<span class="text-muted">-</span>';

            // Render penilaian list
            let penilaianHtml = '';
            if (data.penilaians && data.penilaians.length > 0) {
                // Average rating box
                if (data.avg_rating) {
                    const fullStars = Math.floor(data.avg_rating);
                    const emptyStars = 4 - fullStars;
                    penilaianHtml += `
                        <div class="avg-rating-box">
                            <div class="avg-rating-value">${data.avg_rating}</div>
                            <div class="avg-rating-stars">
                                ${'★'.repeat(fullStars)}${'☆'.repeat(emptyStars)}
                            </div>
                            <div class="avg-rating-label">Rata-rata dari ${data.total_penilai} interviewer</div>
                        </div>
                    `;
                }

                // List penilaian per interviewer
                data.penilaians.forEach(pf => {
                    const interviewerName = pf.interviewer ? pf.interviewer.name : 'Unknown';
                    const folderName = pf.folder ? pf.folder.nama : '-';
                    const ratingStars = pf.rating ? '★'.repeat(pf.rating) + '☆'.repeat(4 - pf.rating) :
                        'Belum dinilai';
                    const tanggal = pf.tanggal_dinilai ? new Date(pf.tanggal_dinilai).toLocaleDateString('id-ID', {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    }) : '-';

                    let fileHtml = '';
                    if (pf.file_penilaian) {
                        fileHtml = `
                            <div class="penilaian-file">
                                <a href="/storage/${pf.file_penilaian}" target="_blank">
                                    <i class="fa fa-file-pdf text-danger"></i> Lihat File Penilaian
                                </a>
                            </div>
                        `;
                    }

                    penilaianHtml += `
                        <div class="penilaian-item">
                            <div class="penilaian-header">
                                <div class="penilaian-interviewer">
                                    <i class="fa fa-user-circle text-primary"></i>
                                    ${interviewerName}
                                    <small class="text-muted">(${folderName})</small>
                                </div>
                                <div class="penilaian-date">
                                    <i class="fa fa-clock"></i> ${tanggal}
                                </div>
                            </div>
                            <div class="penilaian-rating">${ratingStars}</div>
                            ${pf.catatan ? `<div class="penilaian-catatan"><strong>Catatan:</strong><br>${pf.catatan}</div>` : ''}
                            ${fileHtml}
                        </div>
                    `;
                });
            } else {
                penilaianHtml = '<p class="text-muted text-center py-3">Belum ada penilaian dari interviewer</p>';
            }

            const cvButtonHtml = p.cv_path ? `
                <button class="btn btn-danger" onclick="showCV(${p.id}, '${p.nama_lengkap.replace(/'/g, "\\'")}')">
                    <i class="fa fa-file-pdf"></i> Lihat CV
                </button>
            ` : '<span class="text-muted">CV belum diupload</span>';

            const tanggalMelamar = p.tanggal_melamar ? new Date(p.tanggal_melamar).toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            }) : '-';

            const tanggalLahir = p.tanggal_lahir ? new Date(p.tanggal_lahir).toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            }) + (data.usia ? ` (${data.usia} tahun)` : '') : '-';

            $('#detailContent').html(`
                <div class="detail-profile-header">
                    <div class="detail-avatar-lg">${inisial}</div>
                    <div class="detail-name">${p.nama_lengkap}</div>
                    <div class="detail-position">${p.jabatan || '-'} · ${p.divisi || '-'}</div>
                    <div class="detail-contact">
                        <span><i class="fa fa-envelope"></i> ${p.email || '-'}</span>
                        <span><i class="fa fa-phone"></i> ${p.no_telepon || '-'}</span>
                        <span><i class="fa fa-map-marker-alt"></i> ${p.domisili || '-'}</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" 
                            style="position: absolute; top: 15px; right: 15px;"></button>
                </div>

                <div style="max-height: 60vh; overflow-y: auto;">
                    <div class="detail-section">
                        <div class="detail-section-title">
                            <i class="fa fa-user"></i> Informasi Pribadi
                        </div>
                        <div class="detail-grid">
                            <div>
                                <div class="detail-item-label">Tanggal Lahir</div>
                                <div class="detail-item-value">${tanggalLahir}</div>
                            </div>
                            <div>
                                <div class="detail-item-label">Jenis Kelamin</div>
                                <div class="detail-item-value">${p.jenis_kelamin === 'L' ? 'Laki-laki' : (p.jenis_kelamin === 'P' ? 'Perempuan' : '-')}</div>
                            </div>
                            <div>
                                <div class="detail-item-label">Pendidikan Terakhir</div>
                                <div class="detail-item-value">${p.pendidikan_terakhir || '-'}${p.jurusan ? ' - ' + p.jurusan : ''}</div>
                            </div>
                            <div>
                                <div class="detail-item-label">Institusi</div>
                                <div class="detail-item-value">${p.institusi || '-'}</div>
                            </div>
                            <div>
                                <div class="detail-item-label">IPK</div>
                                <div class="detail-item-value">${p.ipk || '-'}</div>
                            </div>
                            <div>
                                <div class="detail-item-label">Pengalaman</div>
                                <div class="detail-item-value">${p.pengalaman_tahun ? p.pengalaman_tahun + ' tahun' : '-'}</div>
                            </div>
                        </div>
                    </div>

                    <div class="detail-section">
                        <div class="detail-section-title">
                            <i class="fa fa-briefcase"></i> Informasi Lamaran
                        </div>
                        <div class="detail-grid">
                            <div>
                                <div class="detail-item-label">Posisi Dilamar</div>
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
                                <div class="detail-item-label">Sumber Lamaran</div>
                                <div class="detail-item-value">${p.sumber_lamaran || '-'}</div>
                            </div>
                            <div>
                                <div class="detail-item-label">Tahapan Saat Ini</div>
                                <div class="detail-item-value"><span class="badge bg-primary">${data.tahap_label}</span></div>
                            </div>
                            <div>
                                <div class="detail-item-label">Gaji Diharapkan</div>
                                <div class="detail-item-value">${p.gaji_diharapkan_format || '-'}</div>
                            </div>
                        </div>
                    </div>

                    <div class="detail-section">
                        <div class="detail-section-title">
                            <i class="fa fa-code"></i> Keahlian
                        </div>
                        <div>${keahlianHtml}</div>
                    </div>

                    <div class="detail-section">
                        <div class="detail-section-title">
                            <i class="fa fa-paperclip"></i> Dokumen
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            ${cvButtonHtml}
                        </div>
                    </div>

                    <div class="detail-section">
                        <div class="detail-section-title">
                            <i class="fa fa-star"></i> Hasil Penilaian Interviewer
                        </div>
                        ${penilaianHtml}
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            `);
        }

        function showCV(pelamarId, nama) {
            $('#cvTitle').text('CV - ' + nama);
            $('#cvViewerContainer').html(`
                <div class="cv-empty">
                    <i class="fa fa-spinner fa-spin"></i>
                    <h5>Memuat CV...</h5>
                </div>
            `);

            const modal = new bootstrap.Modal(document.getElementById('modalCVViewer'));
            modal.show();

            $.ajax({
                type: 'GET',
                url: `/HR-dashboard/folders/pelamar/${pelamarId}/detail`,
                success: function(response) {
                    if (response.success && response.data.cv_url) {
                        const cvUrl = response.data.cv_url;
                        $('#cvDownloadBtn').attr('href', cvUrl);

                        if (cvUrl.toLowerCase().endsWith('.pdf')) {
                            $('#cvViewerContainer').html(`
                                <iframe src="${cvUrl}" style="width: 100%; height: 70vh; border: none;"></iframe>
                            `);
                        } else {
                            $('#cvViewerContainer').html(`
                                <div class="cv-empty">
                                    <i class="fa fa-file"></i>
                                    <h5>Preview tidak tersedia untuk format ini</h5>
                                    <p>Silakan download untuk melihat dokumen</p>
                                    <a href="${cvUrl}" class="btn btn-primary mt-3" target="_blank">
                                        <i class="fa fa-download"></i> Download Dokumen
                                    </a>
                                </div>
                            `);
                        }
                    } else {
                        $('#cvViewerContainer').html(`
                            <div class="cv-empty">
                                <i class="fa fa-file-pdf"></i>
                                <h5>CV tidak ditemukan</h5>
                                <p>Pelamar belum mengupload CV</p>
                            </div>
                        `);
                    }
                },
                error: function() {
                    $('#cvViewerContainer').html(`
                        <div class="cv-empty text-danger">
                            <i class="fa fa-exclamation-circle"></i>
                            <h5>Gagal memuat CV</h5>
                        </div>
                    `);
                }
            });
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
                            ${folder.children.map(child => {
                                const childCount = child.pelamars ? child.pelamars.length : 0;
                                const escapedName = child.nama.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                                return `
                                                <div class="col-md-4 col-lg-3 mb-3">
                                                    <div class="card subfolder-card h-100" style="cursor: pointer; border: 2px solid #e5e7eb; transition: all 0.2s;" 
                                                        onmouseover="this.style.borderColor='#0062ff'; this.style.background='#f0f7ff'" 
                                                        onmouseout="this.style.borderColor='#e5e7eb'; this.style.background='white'"
                                                        onclick="selectFolder(${child.id}, '${escapedName}')">
                                                        <div class="card-body text-center py-4">
                                                            <i class="fa fa-folder text-warning" style="font-size: 3rem; margin-bottom: 10px;"></i>
                                                            <h6 class="card-title mb-2" style="font-size: 0.9rem;" title="${child.nama}">
                                                                ${child.nama.length > 20 ? child.nama.substring(0, 20) + '...' : child.nama}
                                                            </h6>
                                                            ${child.is_pinned ? '<i class="fa fa-thumbtack text-warning ms-2" style="font-size: 0.7rem;"></i>' : ''}
                                                        </div>
                                                    </div>
                                                </div>
                                            `;
                            }).join('')}
                        </div>
                    </div>
                `;

                if (hasPelamars) {
                    html += `<hr class="my-4">`;
                }
            }

            if (hasPelamars) {
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
                            <tbody>
                                ${folder.pelamars.map((pf, index) => {
                                    const p = pf.pelamar;
                                    const inisial = getInitial(p.nama_lengkap);
                                    const ratingHtml = pf.rating ?
                                        `<div class="rating-stars">${'★'.repeat(pf.rating)}${'☆'.repeat(4 - pf.rating)}</div>` :
                                        '<span class="text-muted">-</span>';
                                    const escapedName = p.nama_lengkap.replace(/'/g, "\\'");

                                    return `
                                                    <tr draggable="true" data-pelamar-id="${p.id}">
                                                        <td style="width: 50px;">${index + 1}</td>
                                                        <td>
                                                            <div class="pelamar-name-cell">
                                                                <div class="pelamar-avatar">${inisial}</div>
                                                                <div>
                                                                    <div class="pelamar-name-text">${p.nama_lengkap}</div>
                                                                    <div class="pelamar-email-text">${p.email || '-'}</div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>${p.jabatan || '-'}</td>
                                                        <td>${p.divisi || '-'}</td>
                                                        <td>${ratingHtml}</td>
                                                        <td style="width: 180px;">
                                                            <div class="d-flex gap-1">
                                                                <button class="btn btn-sm btn-outline-primary" onclick="showDetailPelamar(${p.id})" title="Detail">
                                                                    <i class="fa fa-eye"></i>
                                                                </button>
                                                                ${p.cv_path ? `<button class="btn btn-sm btn-outline-danger" onclick="showCV(${p.id}, '${escapedName}')" title="Lihat CV">
                                                            <i class="fa fa-file-pdf"></i>
                                                        </button>` : ''}
                                                                <button class="btn btn-sm btn-outline-success" onclick="showPenilaianModal(${p.id}, ${folder.id}, '${escapedName}')" title="Nilai">
                                                                    <i class="fa fa-star"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                `;
                                }).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            }

            container.html(html);
            setupDragAndDrop();
        }

        function getInitial(name) {
            if (!name) return '?';
            const parts = name.trim().split(' ');
            return (parts[0][0] + (parts[1] ? parts[1][0] : '')).toUpperCase();
        }

        // ===== DRAG & DROP =====
        function setupDragAndDrop() {
            // Drag untuk pelamar (table rows)
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

            // Drag untuk folder
            $('.folder-tree-link').off('dragstart').on('dragstart', function(e) {
                draggedFolderId = $(this).data('folder-id');
                draggedPelamarId = null;
                e.originalEvent.dataTransfer.setData('type', 'folder');
                e.originalEvent.dataTransfer.setData('folder_id', draggedFolderId);
                e.originalEvent.dataTransfer.effectAllowed = 'move';
                $(this).addClass('dragging');

                // Prevent click event
                e.stopPropagation();
            });

            $('.folder-tree-link').off('dragend').on('dragend', function() {
                $(this).removeClass('dragging');
                $('.folder-tree-link').removeClass('drag-over drag-over-invalid');
                draggedFolderId = null;
            });

            // Drop zone untuk folder
            $('.folder-tree-link').off('dragover').on('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const targetFolderId = $(this).data('folder-id');

                // Jika yang di-drag adalah folder
                if (draggedFolderId) {
                    // Cek apakah valid (tidak drop ke diri sendiri atau child-nya)
                    if (targetFolderId == draggedFolderId || isChildFolder(draggedFolderId, targetFolderId)) {
                        $(this).addClass('drag-over-invalid');
                        $(this).removeClass('drag-over');
                        e.originalEvent.dataTransfer.dropEffect = 'none';
                    } else {
                        $(this).addClass('drag-over');
                        $(this).removeClass('drag-over-invalid');
                        e.originalEvent.dataTransfer.dropEffect = 'move';
                    }
                } else if (draggedPelamarId) {
                    // Jika yang di-drag adalah pelamar
                    $(this).addClass('drag-over');
                    $(this).removeClass('drag-over-invalid');
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
                    // Pindahkan folder
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
                    // Pindahkan pelamar
                    if (targetFolderId === 'inbox') {
                        removeFromFolder(draggedPelamarId);
                    } else {
                        movePelamar(draggedPelamarId, targetFolderId);
                    }
                }

                draggedFolderId = null;
                draggedPelamarId = null;
            });

            // Drop zone untuk inbox
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

            // Drop zone untuk main content (current folder)
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

        // Helper untuk cek apakah targetFolder adalah child dari draggedFolder
        function isChildFolder(parentFolderId, targetFolderId) {
            const parentFolder = allFolders.find(f => f.id == parentFolderId);
            if (!parentFolder) return false;

            function checkChildren(folderId) {
                const children = allFolders.filter(f => f.parent_id == folderId);
                for (let child of children) {
                    if (child.id == targetFolderId) {
                        return true;
                    }
                    if (checkChildren(child.id)) {
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
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    showToast(response?.message || 'Terjadi kesalahan pada server', 'danger');
                }
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
                }
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
                }
            });
        }

        // ===== FOLDER ACTIONS =====
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
                }
            });
        }

        function showRenameModal(folderId, currentName) {
            $('#renameFolderId').val(folderId);
            $('#renameFolderName').val(currentName);
            $('#modalRenameFolder').modal('show');
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
                            selectFolder('inbox', 'Inbox');
                        }
                        loadAllData();
                    }
                }
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
                            selectFolder('inbox', 'Inbox');
                        }
                        loadAllData();
                    }
                }
            });
        }

        // ===== PENILAIAN =====
        function showPenilaianModal(pelamarId, folderId, nama) {
            $('#penilaianPelamarId').val(pelamarId);
            $('#penilaianFolderId').val(folderId);
            $('#penilaianNama').val(nama);
            $('#ratingInput').val(0);
            $('#starContainer .star').css('color', '#ddd').text('☆');
            $('#modalPenilaian').modal('show');
        }

        $(document).on('click', '.star', function() {
            const value = $(this).data('value');
            $('#ratingInput').val(value);

            $('#starContainer .star').each(function() {
                if ($(this).data('value') <= value) {
                    $(this).css('color', '#ffc107').text('★');
                } else {
                    $(this).css('color', '#ddd').text('☆');
                }
            });
        });

        $(document).on('mouseover', '.star', function() {
            const value = $(this).data('value');
            $('#starContainer .star').each(function() {
                if ($(this).data('value') <= value) {
                    $(this).css('color', '#ffc107');
                } else {
                    $(this).css('color', '#ddd');
                }
            });
        });

        $(document).on('mouseleave', '#starContainer', function() {
            const currentValue = parseInt($('#ratingInput').val());
            $('#starContainer .star').each(function() {
                if ($(this).data('value') <= currentValue) {
                    $(this).css('color', '#ffc107');
                } else {
                    $(this).css('color', '#ddd');
                }
            });
        });

        // ===== FORM HANDLERS =====
        $('#formCreateFolder').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            $.ajax({
                type: 'POST',
                url: "{{ route('HR.folders.store') }}",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        $('#modalCreateFolder').modal('hide');
                        $('#formCreateFolder')[0].reset();
                        loadAllData();
                    }
                }
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
                        $('#modalRenameFolder').modal('hide');
                        loadAllData();
                    }
                }
            });
        });

        $('#formPenilaian').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            $.ajax({
                type: 'POST',
                url: "{{ route('HR.folders.pelamar.penilaian') }}",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        $('#modalPenilaian').modal('hide');
                        loadAllData();
                    } else {
                        showToast(response.message || 'Gagal menyimpan', 'danger');
                    }
                },
                error: function() {
                    showToast('Terjadi kesalahan pada server', 'danger');
                }
            });
        });

        function showToast(message, type = 'success') {
            const toast = $(`
                <div class="alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show" 
                     role="alert" 
                     style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
            $('body').append(toast);
            setTimeout(() => toast.alert('close'), 3000);
        }
    </script>
@endsection
