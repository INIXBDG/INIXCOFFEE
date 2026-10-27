@extends('layout_HR.app')

@section('content_HR')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        /* =====================================================
            CREATE OVERLAY - UNIQUE FULLSCREEN EXPERIENCE
            ===================================================== */
        .create-overlay {
            position: fixed;
            inset: 0;
            z-index: 1080;
            display: flex;
            align-items: stretch;
            justify-content: center;
            pointer-events: none;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.35s ease, visibility 0.35s ease;
        }

        .create-overlay.is-open {
            pointer-events: auto;
            opacity: 1;
            visibility: visible;
        }

        .create-overlay-backdrop {
            position: absolute !important;
            inset: 0 !important;
            z-index: 0 !important;
        }

        .create-overlay-panel {
            position: relative !important;
            z-index: 1 !important;
        }

        .create-overlay.is-open .create-overlay-panel {
            transform: translateY(0) scale(1);
            opacity: 1;
        }

        #generateModal,
        #previewModal,
        #historyModal,
        #deleteConfirmModal,
        #editTemplateModal {
            z-index: 1060 !important;
        }


        /* Top Bar */
        .create-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.5rem;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            flex-shrink: 0;
            gap: 1.5rem;
        }

        .btn-close-create {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .btn-close-create:hover {
            background: #fee2e2;
            border-color: #fecaca;
            color: #dc2626;
        }

        /* Step Indicator */
        .create-steps {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .step-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            opacity: 0.45;
            transition: opacity 0.3s ease;
        }

        .step-item.active,
        .step-item.done {
            opacity: 1;
        }

        .step-circle {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #64748b;
            font-size: 0.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .step-item.active .step-circle {
            background: #3b82f6;
            color: white;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
        }

        .step-item.done .step-circle {
            background: #22c55e;
            color: white;
        }

        .step-label {
            font-size: 0.85rem;
            font-weight: 500;
            color: #475569;
        }

        .step-line {
            width: 40px;
            height: 2px;
            background: #e2e8f0;
            border-radius: 2px;
        }

        /* Skeleton */
        .create-skeleton {
            flex: 1;
            display: flex;
            gap: 1.25rem;
            padding: 1.5rem;
            overflow: hidden;
        }

        .skeleton-left {
            flex: 1.6;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .skeleton-right {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .skeleton-block {
            background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%);
            background-size: 200% 100%;
            animation: skeleton-shimmer 1.4s ease-in-out infinite;
            border-radius: 12px;
        }

        .skeleton-header {
            height: 48px;
        }

        .skeleton-preview {
            flex: 1;
            min-height: 420px;
        }

        .skeleton-card {
            height: 160px;
        }

        .skeleton-card.short {
            height: 110px;
        }

        @keyframes skeleton-shimmer {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        /* Content Area */
        .create-content {
            flex: 1;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .create-body {
            flex: 1;
            display: flex;
            gap: 1.25rem;
            padding: 1.25rem 1.5rem 1.5rem;
            overflow: hidden;
        }

        .create-preview-col {
            flex: 1.65;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        .create-sidebar-col {
            flex: 1;
            min-width: 320px;
            max-width: 420px;
            overflow-y: auto;
            padding-right: 4px;
        }

        /* Cards di dalam create */
        .create-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .create-card-header {
            padding: 0.85rem 1.1rem;
            border-bottom: 1px solid #f1f5f9;
            background: #fafbfc;
        }

        .create-card-body {
            padding: 1.1rem;
        }

        /* =====================================================
            DOCX PREVIEW & MAPPING STYLES (dari create asli)
            ===================================================== */
        #docx-container {
            border: none;
            min-height: 520px;
            max-height: calc(100vh - 220px);
            background: #ffffff !important;
            overflow: auto;
            padding: 24px;
            cursor: text;
            user-select: text;
            border-radius: 0 0 14px 14px;
        }

        .docx-wrapper {
            background: transparent !important;
        }

        .docx-wrapper>section.docx {
            box-shadow: 0 0 0 1px #e2e8f0, 0 4px 12px rgba(0, 0, 0, 0.04) !important;
            margin-bottom: 24px;
            background: white !important;
            padding: 40px;
            border-radius: 4px;
        }

        /* Mapped text highlight */
        .text-mapped {
            background-color: #d1e7dd !important;
            color: #0f5132;
            border-bottom: 2px solid #198754;
            border-radius: 2px;
            cursor: default;
        }

        .text-mapped-auto_date {
            background-color: #cfe2ff !important;
            color: #084298;
            border-bottom: 2px solid #0d6efd;
        }

        .text-mapped-formula {
            background-color: #e2d9f3 !important;
            color: #59359a;
            border-bottom: 2px solid #6f42c1;
        }

        .text-mapped-auth_field {
            background-color: #fff3cd !important;
            color: #664d03;
            border-bottom: 2px solid #ffc107;
        }

        .text-mapped-relation_single {
            background-color: #d1ecf1 !important;
            color: #055160;
            border-bottom: 2px solid #0dcaf0;
        }

        .text-mapped-loop_manual {
            background-color: #f8d7da !important;
            color: #842029;
            border-bottom: 2px solid #dc3545;
        }

        .text-mapped-loop_relation {
            background-color: #f5c2c7 !important;
            color: #58151c;
            border-bottom: 2px solid #b02a37;
        }

        .text-mapped-manual_text,
        .text-mapped-manual_textarea,
        .text-mapped-manual_number,
        .text-mapped-manual_select,
        .text-mapped-manual_checkbox,
        .text-mapped-manual_date {
            background-color: #e7f1ff !important;
            color: #0a58ca;
            border-bottom: 2px solid #6ea8fe;
        }

        /* Placeholder badge */
        .placeholder-badge {
            font-size: 9px;
            vertical-align: super;
            background: #198754;
            color: white;
            padding: 1px 5px;
            border-radius: 3px;
            margin-left: 3px;
            font-weight: bold;
            white-space: nowrap;
            user-select: none;
        }

        .placeholder-badge-auto_date {
            background: #0d6efd;
        }

        .placeholder-badge-formula {
            background: #6f42c1;
        }

        .placeholder-badge-auth_field {
            background: #ffc107;
            color: #000;
        }

        .placeholder-badge-relation_single {
            background: #0dcaf0;
            color: #000;
        }

        .placeholder-badge-loop_manual {
            background: #dc3545;
        }

        .placeholder-badge-loop_relation {
            background: #b02a37;
        }

        .placeholder-badge-manual_text,
        .placeholder-badge-manual_textarea,
        .placeholder-badge-manual_number,
        .placeholder-badge-manual_select,
        .placeholder-badge-manual_checkbox,
        .placeholder-badge-manual_date {
            background: #6ea8fe;
            color: #000;
        }

        ::selection {
            background: #0dcaf0;
            color: white;
        }

        .mapping-item {
            font-size: 0.8rem;
        }

        /* =====================================================
            UTILITIES & INDEX PAGE
            ===================================================== */
        .hover-shadow {
            transition: box-shadow 0.25s ease, transform 0.25s ease;
        }

        .hover-shadow:hover {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05) !important;
            transform: translateY(-2px);
        }

        /* Toast z-index di atas overlay */
        .toast-container {
            z-index: 1200 !important;
        }

        /* Modal field config harus di atas overlay */
        #fieldConfigModal {
            z-index: 1100 !important;
        }

        .modal-backdrop {
            z-index: 1050 !important;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .create-body {
                flex-direction: column;
                overflow-y: auto;
            }

            .create-preview-col,
            .create-sidebar-col {
                max-width: 100%;
                min-width: 100%;
            }

            #docx-container {
                max-height: 400px;
            }

            .create-steps .step-label {
                display: none;
            }

            .step-line {
                width: 24px;
            }
        }

        @media (max-width: 576px) {
            .create-topbar {
                flex-wrap: wrap;
                gap: 0.75rem;
            }

            .create-overlay-panel {
                border-radius: 0;
            }
        }

        /* =====================================================
            FIX Z-INDEX MODAL (Hapus & Generate)
            ===================================================== */

        /* Create Overlay tetap di 1000 */
        .create-overlay {
            position: fixed !important;
            inset: 0 !important;
            z-index: 1080 !important;
        }

        /* Field Config Modal (di dalam Create) */
        #fieldConfigModal {
            z-index: 1100 !important;
        }

        /* Backdrop default Bootstrap harus lebih rendah dari modal */
        .modal-backdrop {
            z-index: 1050 !important;
        }

        /* Generate Modal & Delete Modal harus di atas backdrop */
        #generateModal,
        #deleteConfirmModal,
        .modal {
            z-index: 1060 !important;
        }

        /* Kalau ada multiple backdrop, pastikan yang terakhir benar */
        .modal-backdrop+.modal-backdrop {
            z-index: 1055 !important;
        }

        /* Toast tetap paling atas */
        .toast-container {
            z-index: 1200 !important;
        }

        /* History Modal Side Panel */
        .history-list-pane {
            width: 42%;
            min-width: 320px;
            background: #f8fafc;
            transition: width 0.3s ease;
        }

        .history-side-pane {
            width: 58%;
            background: #ffffff;
            display: none;
            /* default tertutup */
            flex-direction: column;
        }

        .history-side-pane.is-open {
            display: flex;
        }

        /* Saat side pane terbuka, list mengecil sedikit */
        .history-list-pane.with-side {
            width: 38%;
        }

        @media (max-width: 992px) {
            .history-list-pane {
                width: 100%;
            }

            .history-side-pane {
                position: absolute;
                inset: 0;
                width: 100%;
                z-index: 10;
            }
        }

        /* ===== SIDEBAR EMPTY STATE ===== */
        .sidebar-empty-state {
            text-align: center;
            padding: 1.5rem 1rem;
            color: var(--secondary);
            list-style: none;
        }

        .sidebar-empty-state .empty-icon {
            width: 64px;
            height: 64px;
            background: var(--primary-soft);
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 1rem;
            border: 2px dashed rgba(0, 98, 255, 0.3);
            opacity: 0.9;
        }

        .sidebar-empty-state h6 {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }

        .sidebar-empty-state p {
            font-size: 0.75rem;
            color: var(--secondary);
            margin-bottom: 1rem;
            line-height: 1.5;
        }

        .sidebar-empty-state .btn-create-folder {
            font-size: 0.75rem;
            padding: 0.4rem 0.85rem;
            border-radius: 0.375rem;
            background: var(--primary);
            color: white;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            transition: all 0.15s;
            cursor: pointer;
            font-weight: 500;
        }

        .sidebar-empty-state .btn-create-folder:hover {
            background: #0052d4;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 98, 255, 0.2);
            color: white;
        }

        /* ===== ACTION DROPDOWN (History table) ===== */
        .action-dropdown {
            position: relative;
            display: inline-block;
        }

        .action-dropdown-btn {
            background: transparent;
            border: 1px solid transparent;
            padding: 4px 8px;
            border-radius: 6px;
            color: #64748b;
            cursor: pointer;
            line-height: 1;
            transition: all 0.15s ease;
        }

        .action-dropdown-btn:hover,
        .action-dropdown:focus-within .action-dropdown-btn {
            background: #f1f5f9;
            border-color: #e2e8f0;
            color: #334155;
        }

        .action-dropdown-menu {
            position: absolute;
            top: calc(100% + 4px);
            right: 0;
            min-width: 160px;
            padding: 6px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12), 0 4px 10px rgba(0, 0, 0, 0.06);
            z-index: 1080;
            /* di atas modal content */
            opacity: 0;
            visibility: hidden;
            transform: translateY(-6px) scale(0.98);
            transition: all 0.15s ease;
            pointer-events: none;
        }

        /* Muncul saat hover / focus */
        .action-dropdown:hover .action-dropdown-menu,
        .action-dropdown:focus-within .action-dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
            pointer-events: auto;
        }

        /* Item di dalam menu */
        .action-dropdown-menu button {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: none;
            background: transparent;
            border-radius: 7px;
            font-size: 0.875rem;
            color: #334155;
            text-align: left;
            cursor: pointer;
            transition: background 0.12s ease;
        }

        .action-dropdown-menu button:hover {
            background: #f1f5f9;
        }

        .action-dropdown-menu button.text-danger {
            color: #dc2626;
        }

        .action-dropdown-menu button.text-danger:hover {
            background: #fee2e2;
            color: #b91c1c;
        }

        /* ===== PENTING: biarkan dropdown keluar dari overflow ===== */
        #historyContentArea .table-responsive {
            overflow: visible !important;
        }

        /* Kalau list sangat panjang, biarkan area tetap bisa di-scroll,
       tapi dropdown tidak terpotong */
        #historyContentArea {
            overflow-x: hidden;
            overflow-y: auto;
        }
    </style>
    <div class="container-fluid">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">Dashboard Template</li>
            </ol>
        </nav>

        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0">Daftar Template Laporan</h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" id="btnOpenCreateModal">
                            <span class="iconify me-2" data-icon="mdi:plus"></span>Buat Template Baru
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="openHistoryModal()">
                            <span class="iconify me-2" data-icon="mdi:folders"></span>History
                        </button>
                    </div>
                </div>

                <!-- Modal Edit Template (tetap sama) -->
                <div class="modal fade" id="editTemplateModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <span class="iconify me-2" data-icon="mdi:pencil-box-outline"></span>
                                    Edit Template
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="formEditTemplate">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" id="edit_template_id" name="id">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Nama Template <span
                                                class="text-danger">*</span></label>
                                        <input type="text" id="edit_name" name="name" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Kode Template <span
                                                class="text-danger">*</span></label>
                                        <input type="text" id="edit_code" name="code" class="form-control" required>
                                        <small class="text-muted">Kode harus unik (tidak boleh sama dengan template
                                            lain).</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Kategori</label>
                                        <select id="edit_category" name="category" class="form-select">
                                            <option value="karyawan">Karyawan</option>
                                            <option value="pelamar">Rekrutan</option>
                                        </select>
                                    </div>
                                    <div class="alert alert-info small mb-0">
                                        <strong>Info:</strong> Mengedit di sini hanya mengubah metadata template. Untuk
                                        mengubah struktur dokumen/mapping field, silakan gunakan fitur "Generate" lalu edit
                                        ulang dokumennya.
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <span class="iconify me-1" data-icon="mdi:close"></span>Batal
                                </button>
                                <button type="button" class="btn btn-primary" id="btnSaveEdit"
                                    onclick="executeEditTemplate()">
                                    <span class="iconify me-1" data-icon="mdi:content-save"></span>Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($templates->isNotEmpty())
                    @foreach ($templates as $category => $items)
                        <h6 class="fw-bold text-uppercase text-muted mb-3 mt-4">{{ $category }}</h6>
                        <div class="row g-3">
                            @foreach ($items as $tpl)
                                <div class="col-md-4 col-lg-3">
                                    <div class="card h-100 border-0 shadow-sm hover-shadow">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <span class="iconify text-primary" data-icon="mdi:file-document-outline"
                                                    style="font-size: 2rem;"></span>
                                            </div>
                                            <h6 class="card-title fw-bold mb-1">{{ $tpl->name }}</h6>
                                            <p class="text-muted small mb-3">
                                                {{ Str::limit($tpl->description ?? 'Tidak ada deskripsi', 50) }}
                                            </p>
                                            <div class="d-flex gap-2 mt-auto">
                                                <button type="button" class="btn btn-sm btn-primary flex-grow-1"
                                                    onclick="openGenerateModal({{ $tpl->id }}, '{{ addslashes($tpl->name) }}')">
                                                    <span class="iconify me-1" data-icon="mdi:file-pdf-box"></span> Generate
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-warning" title="Edit"
                                                    onclick="openEditOverlay({{ $tpl->id }})">
                                                    <span class="iconify" data-icon="mdi:pencil"></span>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="confirmDeleteTemplate({{ $tpl->id }}, '{{ addslashes($tpl->name) }}')"
                                                    title="Hapus">
                                                    <span class="iconify" data-icon="mdi:delete"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <img src="{{ asset('svgundraw/emptydata.svg') }}" alt="" width="12%">
                        <h5 class="mt-3"></h5>
                        <button type="button" class="btn btn-primary mt-3" id="btnOpenCreateModalEmpty">Buat Template
                            Baru</button>
                    </div>
                @endif
            </div>
        </div>
    </div>


    <div class="modal fade" id="generateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title d-flex align-items-center gap-2">
                        <span class="iconify" data-icon="mdi:file-document-check-outline"
                            style="font-size:1.4rem;"></span>
                        <span id="generateModalTitle">Generate Laporan</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0" id="generateModalBody" style="min-height: 60vh;">
                    <div class="text-center py-5" id="generateModalLoading">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-3 text-muted">Memuat form generate...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <span class="iconify me-2" data-icon="mdi:file-eye-outline"></span>
                        <span id="previewModalTitle">Preview Dokumen</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0" style="height: 75vh;">
                    <div id="previewLoading" class="text-center py-5">
                        <div class="spinner-border text-primary"></div>
                        <p class="mt-3 text-muted">Membuat preview...</p>
                    </div>
                    <div id="previewContent" style="display: none; height: 100%;"></div>
                </div>
                <div class="modal-footer">
                    <small class="text-muted me-auto">Ini hanya preview — belum disimpan</small>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen-lg-down modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="height: 90vh;">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title d-flex align-items-center gap-2">
                        <span class="iconify" data-icon="mdi:history" style="font-size:1.3rem;"></span>
                        Riwayat Generate
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-0 d-flex overflow-hidden">
                    {{-- LEFT: List Folder / File --}}
                    <div id="historyListPane" class="history-list-pane border-end">
                        <div class="p-3 border-bottom bg-light d-flex justify-content-between align-items-center">
                            <div id="historyBreadcrumb">
                                <span class="fw-semibold">Semua Folder</span>
                            </div>
                            <button class="btn btn-sm btn-outline-secondary d-none" id="btnBackToFolders"
                                onclick="showHistoryFolders()">
                                <span class="iconify me-1" data-icon="mdi:arrow-left"></span>Kembali
                            </button>
                        </div>

                        <div class="p-3 overflow-auto" style="height: calc(90vh - 130px);" id="historyContentArea">
                            {{-- Folder / File akan di-render di sini --}}
                            <div class="text-center py-5 text-muted">
                                <div class="spinner-border spinner-border-sm"></div>
                                <span class="ms-2">Memuat...</span>
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT: Side Panel (Preview / Edit / Detail) --}}
                    <div id="historySidePane" class="history-side-pane">
                        <div class="h-100 d-flex flex-column">
                            <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-white">
                                <h6 class="mb-0" id="sidePaneTitle">Detail</h6>
                                <button type="button" class="btn-close" onclick="closeHistorySidePane()"></button>
                            </div>
                            <div class="flex-grow-1 overflow-auto p-3" id="sidePaneBody">
                                <div class="text-center text-muted py-5">
                                    <span class="iconify mb-3" data-icon="mdi:file-document-outline"
                                        style="font-size:3rem;opacity:.3;"></span>
                                    <p>Pilih file untuk melihat preview</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://unpkg.com/docx-preview@0.3.0/dist/docx-preview.min.js"></script>

    <script>
        window.APP_DATA = {
            columns: @json($allowedColumns),
            saveUrl: "{{ route('HR.reports.save.mapping') }}",
            csrfToken: "{{ csrf_token() }}",
            redirectUrl: "{{ route('HR.reports.index') }}"
        };
    </script>

    @include('HR.template.partials.smart-creator')

    <script>
        // =====================================================
        // 1. TOAST & DELETE (dari index - tidak diubah)
        // =====================================================
        function confirmDeleteTemplate(templateId, templateName) {
            const modalHtml = `
                <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header text-dark">
                                <h5 class="modal-title">
                                    <span class="iconify me-2" data-icon="mdi:alert-circle"></span>
                                    Konfirmasi Hapus
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="text-center mb-3">
                                    <span class="iconify text-danger" data-icon="mdi:trash-can-outline" style="font-size: 48px;"></span>
                                </div>
                                <p class="mb-2">Apakah Anda yakin ingin menghapus template:</p>
                                <h5 class="text-danger fw-bold text-center">"${templateName}"</h5>
                                <div class="alert alert-warning small mt-3 mb-0">
                                    <strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan. 
                                    Semua placeholder dan konfigurasi terkait akan dihapus permanen.
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <span class="iconify me-1" data-icon="mdi:close"></span>Batal
                                </button>
                                <button type="button" class="btn btn-danger" id="btnConfirmDelete" onclick="executeDeleteTemplate(${templateId})">
                                    <span class="iconify me-1" data-icon="mdi:delete"></span>Ya, Hapus Template
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            const oldModal = document.getElementById('deleteConfirmModal');
            if (oldModal) oldModal.remove();

            document.body.insertAdjacentHTML('beforeend', modalHtml);
            const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            modal.show();

            document.getElementById('deleteConfirmModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        }

        // Pastikan backdrop tidak menutupi modal
        setTimeout(() => {
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(bd => {
                bd.style.zIndex = '1050';
            });

            const deleteModal = document.getElementById('deleteConfirmModal');
            if (deleteModal) {
                deleteModal.style.zIndex = '1060';
            }
        }, 10);

        function executeDeleteTemplate(templateId) {
            const btn = document.getElementById('btnConfirmDelete');
            const originalText = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menghapus...';

            fetch(`/HR-dashboard/reports/delete/${templateId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
                        modal.hide();
                        showToast(data.message, 'success');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        showToast(data.message || 'Gagal menghapus template', 'error');
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Terjadi kesalahan koneksi', 'error');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
        }

        function showToast(message, type = 'success') {
            const toastContainer = document.getElementById('toastContainer') || createToastContainer();
            const bgClass = type === 'error' ? 'bg-danger' : 'bg-success';
            const icon = type === 'error' ? 'mdi:alert-circle' : 'mdi:check-circle';

            const toastHtml = `
                <div class="toast align-items-center text-white border-0 ${bgClass}" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">
                            <span class="iconify me-2" data-icon="${icon}"></span>
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;

            toastContainer.insertAdjacentHTML('beforeend', toastHtml);
            const toastElement = toastContainer.lastElementChild;
            const toast = new bootstrap.Toast(toastElement, {
                delay: 4000
            });
            toast.show();
            toastElement.addEventListener('hidden.bs.toast', () => toastElement.remove());
        }

        function createToastContainer() {
            const container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '1100';
            document.body.appendChild(container);
            return container;
        }

        // =====================================================
        // GENERATE MODAL (berbeda dari Create Overlay)
        // =====================================================
        async function openGenerateModal(templateId, templateName) {
            const modalEl = document.getElementById('generateModal');
            const modalBody = document.getElementById('generateModalBody');
            const modalTitle = document.getElementById('generateModalTitle');
            const loading = document.getElementById('generateModalLoading');

            modalTitle.textContent = 'Generate: ' + templateName;
            modalBody.innerHTML = `
                <div class="text-center py-5" id="generateModalLoading">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-3 text-muted">Memuat form generate...</p>
                </div>
            `;

            const modal = new bootstrap.Modal(modalEl);
            modal.show();

            try {
                const res = await fetch(`/HR-dashboard/reports/${templateId}/generate-modal`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                });

                if (!res.ok) throw new Error('Gagal memuat form');

                const html = await res.text();
                modalBody.innerHTML = html;

                // Re-init script yang ada di dalam partial (loop, preview, dll)
                // Karena script di dalam partial tidak otomatis jalan setelah innerHTML
                initGenerateFormScripts();

            } catch (err) {
                console.error(err);
                modalBody.innerHTML = `
                    <div class="alert alert-danger m-4">
                        Gagal memuat form generate.<br>
                        <small>${err.message}</small>
                    </div>
                `;
            }
        }

        setTimeout(() => {
            document.querySelectorAll('.modal-backdrop').forEach(bd => {
                bd.style.zIndex = '1050';
            });
            document.getElementById('generateModal').style.zIndex = '1060';
        }, 10);

        // Fungsi untuk mengaktifkan ulang script di dalam form yang di-load
        function initGenerateFormScripts() {
            // Re-bind tombol Preview
            const previewBtn = document.getElementById('btnPreview');
            if (previewBtn) {
                previewBtn.onclick = window.doPreview || null;
            }

            // Di dalam initGenerateFormScripts() atau di global
            document.getElementById('generateForm')?.addEventListener('submit', async function(e) {
                e.preventDefault();

                const sourceEl = this.querySelector('[name="source_id"]');
                if (!sourceEl || !sourceEl.value) {
                    alert('Silakan pilih data sumber terlebih dahulu!');
                    return;
                }

                const btn = document.getElementById('btnGenerate');
                const btnText = btn?.querySelector('.btn-text');
                const spinner = document.getElementById('loadingSpinner');

                if (btn) btn.disabled = true;
                if (btnText) btnText.classList.add('d-none');
                if (spinner) spinner.classList.remove('d-none');

                try {
                    const formData = new FormData(this);

                    const res = await fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html,application/xhtml+xml'
                        }
                    });

                    // Kalau server mengembalikan redirect (biasanya ke halaman download/pending)
                    if (res.redirected) {
                        window.location.href = res.url;
                        return;
                    }

                    // Kalau server mengembalikan HTML (halaman pending / download)
                    const html = await res.text();

                    // Coba deteksi apakah ini halaman pending atau error
                    if (html.includes('Sedang memproses') || html.includes('Generating Report')) {
                        // Buka di tab baru atau ganti lokasi
                        const newWindow = window.open('', '_blank');
                        newWindow.document.write(html);
                        newWindow.document.close();

                        // Atau langsung redirect
                        // window.location.href = res.url || this.action;
                    } else if (res.ok) {
                        // Kalau langsung file download
                        const blob = await res.blob();
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'laporan.docx'; // atau sesuaikan
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(url);
                    } else {
                        throw new Error('Gagal generate laporan');
                    }

                    // Tutup modal generate
                    const generateModal = bootstrap.Modal.getInstance(document.getElementById('generateModal'));
                    if (generateModal) generateModal.hide();

                } catch (err) {
                    console.error(err);
                    alert('Error: ' + err.message);
                } finally {
                    if (btn) btn.disabled = false;
                    if (btnText) btnText.classList.remove('d-none');
                    if (spinner) spinner.classList.add('d-none');
                }
            });

            // Re-bind form submit
            const form = document.getElementById('generateForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const sourceEl = form.querySelector('[name="source_id"]');
                    if (!sourceEl || !sourceEl.value) {
                        e.preventDefault();
                        alert('Silakan pilih data sumber terlebih dahulu!');
                        return false;
                    }

                    const btn = document.getElementById('btnGenerate');
                    if (btn) {
                        btn.disabled = true;
                        const t = btn.querySelector('.btn-text');
                        const s = btn.querySelector('#loadingSpinner');
                        if (t) t.classList.add('d-none');
                        if (s) s.classList.remove('d-none');
                    }
                });
            }
        }

        window.doPreview = function() {
            const form = document.getElementById('generateForm');
            if (!form) {
                alert('Form generate tidak ditemukan');
                return;
            }

            const sourceEl = form.querySelector('[name="source_id"]');
            const sourceId = sourceEl ? sourceEl.value : '';

            if (!sourceId) {
                alert('Silakan pilih data sumber terlebih dahulu sebelum preview!');
                return;
            }

            const btn = document.getElementById('btnPreview');
            const btnText = document.getElementById('previewBtnText');
            const spinner = document.getElementById('previewSpinner');

            if (btn) btn.disabled = true;
            if (btnText) btnText.textContent = 'Memproses...';
            if (spinner) spinner.classList.remove('d-none');

            // Ambil URL preview dari data attribute atau hardcode sesuai route
            const previewUrl = form.dataset.previewUrl ||
                form.action.replace('/generate', '/preview-generate') ||
                `/HR-dashboard/reports/${form.querySelector('[name="template_id"]').value}/preview-generate`;

            const formData = new FormData(form);

            fetch(previewUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/octet-stream, application/json'
                    }
                })
                .then(async (res) => {
                    const contentType = res.headers.get('content-type') || '';

                    if (contentType.includes('application/json')) {
                        const data = await res.json();
                        throw new Error(data.message || 'Gagal membuat preview');
                    }

                    if (!res.ok) {
                        throw new Error('Gagal membuat preview (status ' + res.status + ')');
                    }

                    return res.arrayBuffer();
                })
                .then((arrayBuffer) => {
                    // Tampilkan modal preview
                    const modalEl = document.getElementById('previewModal');
                    const modal = new bootstrap.Modal(modalEl);

                    document.getElementById('previewLoading').style.display = 'block';
                    document.getElementById('previewContent').style.display = 'none';
                    document.getElementById('previewContent').innerHTML = '';

                    modal.show();

                    const container = document.getElementById('previewContent');
                    const docxContainer = document.createElement('div');
                    docxContainer.className = 'docx-container-preview';
                    container.appendChild(docxContainer);

                    return window.docx.renderAsync(arrayBuffer, docxContainer, null, {
                        className: 'docx',
                        inWrapper: true,
                        ignoreWidth: false,
                        ignoreHeight: false,
                        breakPages: true,
                    }).then(() => {
                        document.getElementById('previewLoading').style.display = 'none';
                        container.style.display = 'block';
                    });
                })
                .catch((err) => {
                    console.error(err);
                    alert('Error Preview: ' + err.message);
                })
                .finally(() => {
                    if (btn) btn.disabled = false;
                    if (btnText) btnText.textContent = 'Preview';
                    if (spinner) spinner.classList.add('d-none');
                });
        };

        // =====================================================
        // EDIT MODE
        // =====================================================
        let isEditMode = false;
        let currentEditId = null;

        // =====================================================
        // HISTORY MODAL + SIDE PANEL
        // =====================================================
        let historyData = {};
        let currentHistoryFolder = null;

        function openHistoryModal() {
            const modal = new bootstrap.Modal(document.getElementById('historyModal'));
            modal.show();
            loadHistoryData();
        }

        function loadHistoryData() {
            const area = document.getElementById('historyContentArea');
            area.innerHTML = `
                <div class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm"></div>
                    <span class="ms-2">Memuat riwayat...</span>
                </div>`;

            fetch('{{ route('HR.reports.history.data') }}')
                .then(r => r.json())
                .then(res => {
                    if (!res.success || !res.data) {
                        area.innerHTML = '<div class="text-center py-5 text-muted">Belum ada riwayat</div>';
                        return;
                    }

                    historyData = {};
                    res.data.forEach(item => {
                        const key = item.template_id || 'unknown';
                        if (!historyData[key]) {
                            historyData[key] = {
                                name: item.template_name || 'Template',
                                files: []
                            };
                        }
                        historyData[key].files.push(item);
                    });

                    renderHistoryFolders();
                })
                .catch(() => {
                    area.innerHTML = '<div class="text-center py-5 text-danger">Gagal memuat data</div>';
                });
        }

        function renderHistoryFolders() {
            currentHistoryFolder = null;
            document.getElementById('btnBackToFolders').classList.add('d-none');
            document.getElementById('historyBreadcrumb').innerHTML = '<span class="fw-semibold">Semua Folder</span>';

            const keys = Object.keys(historyData);
            let html = '';

            if (keys.length === 0) {
                html = '<div class="text-center py-5 text-muted">Belum ada riwayat generate</div>';
            } else {
                html = '<div class="row g-3">';
                keys.forEach(key => {
                    const folder = historyData[key];
                    html += `
                        <div class="col-6 col-md-4">
                            <div class="card h-100 border-0 shadow-sm history-folder-card" 
                                style="cursor:pointer" onclick="openHistoryFolder('${key}')">
                                <div class="card-body text-center py-4">
                                    <span class="iconify text-warning mb-2" data-icon="mdi:folder" style="font-size:2.8rem;"></span>
                                    <h6 class="mb-1 text-truncate" title="${folder.name}">${folder.name}</h6>
                                    <small class="text-muted">${folder.files.length} file</small>
                                </div>
                            </div>
                        </div>`;
                });
                html += '</div>';
            }

            document.getElementById('historyContentArea').innerHTML = html;
            closeHistorySidePane();
        }

        function openHistoryFolder(key) {
            currentHistoryFolder = key;
            const folder = historyData[key];
            if (!folder) return;

            document.getElementById('btnBackToFolders').classList.remove('d-none');
            document.getElementById('historyBreadcrumb').innerHTML = `
                <span class="text-muted">Folder /</span> 
                <span class="fw-semibold">${folder.name}</span>`;

            let html = `
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Judul</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>`;

            folder.files.forEach(item => {
                let badge = 'bg-warning-subtle text-warning';
                let status = 'Pending';
                if (item.status === 'completed') {
                    badge = 'bg-success-subtle text-success';
                    status = 'Sukses';
                }
                if (item.status === 'failed') {
                    badge = 'bg-danger-subtle text-danger';
                    status = 'Gagal';
                }

                html += `
                    <tr>
                        <td><small>${item.created_at}</small></td>
                        <td>
                            <div class="text-truncate" style="max-width:180px" title="${item.report_title}">
                                ${item.report_title}
                            </div>
                        </td>
                        <td><span class="badge ${badge}">${status}</span></td>
                        <td class="text-end">
                            <div class="action-dropdown">
                                <button type="button" class="action-dropdown-btn" title="Aksi">
                                    <span class="iconify" data-icon="mdi:dots-vertical" style="font-size:1.15rem;"></span>
                                </button>

                                <div class="action-dropdown-menu">
                                    <button type="button"
                                        onclick="showHistoryPreview(${item.id}, '${item.report_title.replace(/'/g, "\\'")}', '${item.download_url}', '${(item.file_extension || '').toUpperCase()}')">
                                        <span class="iconify" data-icon="mdi:eye-outline"></span>
                                        Preview
                                    </button>

                                    <button type="button"
                                        onclick="showHistoryEdit(${item.id}, '${item.report_title.replace(/'/g, "\\'")}')">
                                        <span class="iconify" data-icon="mdi:pencil-outline"></span>
                                        Edit
                                    </button>

                                    <button type="button"
                                        class="text-danger"
                                        onclick="showHistoryDelete(${item.id}, '${item.report_title.replace(/'/g, "\\'")}')">
                                        <span class="iconify" data-icon="mdi:delete-outline"></span>
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>`;
            });

            html += `</tbody></table></div>`;
            document.getElementById('historyContentArea').innerHTML = html;
        }

        function showHistoryFolders() {
            renderHistoryFolders();
        }

        // ---------- Side Panel ----------
        function openHistorySidePane(title) {
            document.getElementById('sidePaneTitle').textContent = title;
            document.getElementById('historySidePane').classList.add('is-open');
            document.getElementById('historyListPane').classList.add('with-side');
        }

        function closeHistorySidePane() {
            document.getElementById('historySidePane').classList.remove('is-open');
            document.getElementById('historyListPane').classList.remove('with-side');
            document.getElementById('sidePaneBody').innerHTML = `
                <div class="text-center text-muted py-5">
                    <span class="iconify mb-3" data-icon="mdi:file-document-outline" style="font-size:3rem;opacity:.3;"></span>
                    <p>Pilih file untuk melihat preview</p>
                </div>`;
        }

        // ---------- Preview di Side Panel ----------
        function showHistoryPreview(id, title, downloadUrl, extension) {
            openHistorySidePane('Preview: ' + title);

            const body = document.getElementById('sidePaneBody');
            body.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-3 text-muted">Memuat preview...</p>
                </div>`;

            fetch(downloadUrl)
                .then(r => {
                    if (!r.ok) throw new Error('Gagal mengambil file');
                    return r.arrayBuffer();
                })
                .then(buffer => {
                    body.innerHTML = '';
                    const container = document.createElement('div');
                    container.className = 'docx-container-preview';
                    body.appendChild(container);
                    body.insertAdjacentHTML('beforeend', `
                        <div class="mt-3 text-end">
                            <a href="${downloadUrl}" class="btn btn-primary btn-sm" target="_blank">
                                <span class="iconify me-1" data-icon="mdi:download"></span>Download
                            </a>
                        </div>`);

                    if (extension === 'DOCX') {
                        window.docx.renderAsync(buffer, container, null, {
                            className: 'docx',
                            inWrapper: true,
                            breakPages: true
                        });
                    } else if (extension === 'PDF') {
                        const blob = new Blob([buffer], {
                            type: 'application/pdf'
                        });
                        const url = URL.createObjectURL(blob);
                        container.innerHTML =
                            `<iframe src="${url}" style="width:100%;height:70vh;border:none;"></iframe>`;
                    } else {
                        body.innerHTML = `<div class="alert alert-warning">Format tidak didukung</div>`;
                    }
                })
                .catch(err => {
                    body.innerHTML = `<div class="alert alert-danger">Gagal memuat preview: ${err.message}</div>`;
                });
        }

        // ---------- Edit di Side Panel ----------
        function showHistoryEdit(id, title) {
            openHistorySidePane('Edit Riwayat');

            document.getElementById('sidePaneBody').innerHTML = `
                <div class="mb-3">
                    <label class="form-label fw-semibold">Judul Laporan</label>
                    <input type="text" id="sideEditTitle" class="form-control" value="${title}">
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" onclick="saveHistoryEdit(${id})">
                        <span class="iconify me-1" data-icon="mdi:content-save"></span>Simpan
                    </button>
                    <button class="btn btn-light" onclick="closeHistorySidePane()">Batal</button>
                </div>`;
        }

        function saveHistoryEdit(id) {
            const title = document.getElementById('sideEditTitle').value.trim();
            if (!title) return alert('Judul wajib diisi');

            fetch(`/HR-dashboard/reports/history/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        report_title: title
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        closeHistorySidePane();
                        loadHistoryData(); // refresh
                    } else {
                        showToast(data.message || 'Gagal', 'error');
                    }
                });
        }

        // ---------- Delete di Side Panel ----------
        function showHistoryDelete(id, title) {
            openHistorySidePane('Hapus Riwayat');

            document.getElementById('sidePaneBody').innerHTML = `
                <div class="text-center mb-4">
                    <span class="iconify text-danger" data-icon="mdi:trash-can-outline" style="font-size:3rem;"></span>
                    <p class="mt-3">Yakin ingin menghapus riwayat ini?</p>
                    <h6 class="text-danger">"${title}"</h6>
                    <div class="alert alert-warning small mt-3">
                        File laporan juga akan dihapus permanen.
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-center">
                    <button class="btn btn-danger" onclick="confirmHistoryDelete(${id})">
                        Ya, Hapus
                    </button>
                    <button class="btn btn-light" onclick="closeHistorySidePane()">Batal</button>
                </div>`;
        }

        function confirmHistoryDelete(id) {
            fetch(`/HR-dashboard/reports/history/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        closeHistorySidePane();
                        loadHistoryData();
                    } else {
                        showToast(data.message || 'Gagal hapus', 'error');
                    }
                });
        }
    </script>
@endsection
