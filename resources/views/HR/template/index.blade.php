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
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        .create-overlay-panel {
            position: relative;
            width: 100%;
            max-width: 1440px;
            height: 100%;
            margin: 0 auto;
            background: #f8fafc;
            display: flex;
            flex-direction: column;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            transform: translateY(30px) scale(0.98);
            opacity: 0;
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1),
                        opacity 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .create-overlay.is-open .create-overlay-panel {
            transform: translateY(0) scale(1);
            opacity: 1;
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
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
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

        .docx-wrapper > section.docx {
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

        .placeholder-badge-auto_date { background: #0d6efd; }
        .placeholder-badge-formula { background: #6f42c1; }
        .placeholder-badge-auth_field { background: #ffc107; color: #000; }
        .placeholder-badge-relation_single { background: #0dcaf0; color: #000; }
        .placeholder-badge-loop_manual { background: #dc3545; }
        .placeholder-badge-loop_relation { background: #b02a37; }
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
            z-index: 1100;
        }

        .modal-backdrop {
            z-index: 1090;
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

        /* Create Overlay tetap di 1080 */
        .create-overlay {
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
        .modal-backdrop + .modal-backdrop {
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
            display: none; /* default tertutup */
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
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="formEditTemplate">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" id="edit_template_id" name="id">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Nama Template <span class="text-danger">*</span></label>
                                        <input type="text" id="edit_name" name="name" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Kode Template <span class="text-danger">*</span></label>
                                        <input type="text" id="edit_code" name="code" class="form-control" required>
                                        <small class="text-muted">Kode harus unik (tidak boleh sama dengan template lain).</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Kategori</label>
                                        <select id="edit_category" name="category" class="form-select">
                                            <option value="karyawan">Karyawan</option>
                                            <option value="pelamar">Rekrutan</option>
                                            <option value="management">Management</option>
                                            <option value="administrasi">Administrasi</option>
                                        </select>
                                    </div>
                                    <div class="alert alert-info small mb-0">
                                        <strong>Info:</strong> Mengedit di sini hanya mengubah metadata template. Untuk mengubah struktur dokumen/mapping field, silakan gunakan fitur "Generate" lalu edit ulang dokumennya.
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <span class="iconify me-1" data-icon="mdi:close"></span>Batal
                                </button>
                                <button type="button" class="btn btn-primary" id="btnSaveEdit" onclick="executeEditTemplate()">
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
                                                <span class="iconify text-primary" data-icon="mdi:file-document-outline" style="font-size: 2rem;"></span>
                                            </div>
                                            <h6 class="card-title fw-bold mb-1">{{ $tpl->name }}</h6>
                                            <p class="text-muted small mb-3">
                                                {{ Str::limit($tpl->description ?? 'Tidak ada deskripsi', 50) }}
                                            </p>
                                            <div class="d-flex gap-2 mt-auto">
                                                <button type="button"
                                                    class="btn btn-sm btn-primary flex-grow-1"
                                                    onclick="openGenerateModal({{ $tpl->id }}, '{{ addslashes($tpl->name) }}')">
                                                    <span class="iconify me-1" data-icon="mdi:file-pdf-box"></span> Generate
                                                </button>
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-warning"
                                                    title="Edit"
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
                        <button type="button" class="btn btn-primary mt-3" id="btnOpenCreateModalEmpty">Buat Template Baru</button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- ==================== CREATE TEMPLATE - UNIQUE FULLSCREEN OVERLAY ==================== -->
    <div id="createTemplateOverlay" class="create-overlay" aria-hidden="true">
        <div class="create-overlay-backdrop"></div>

        <div class="create-overlay-panel">
            <!-- Top Bar + Step Indicator -->
            <div class="create-topbar">
                <div class="d-flex align-items-center gap-3">
                    <button type="button" class="btn-close-create" id="btnCloseCreate" title="Tutup">
                        <span class="iconify" data-icon="mdi:close" style="font-size:1.4rem;"></span>
                    </button>
                    <div>
                        <h5 class="mb-0 fw-bold" id="createOverlayTitle">Buat Template Laporan</h5>
                        <small class="text-muted" id="createOverlaySubtitle">Upload → Mapping → Simpan</small>
                    </div>
                </div>

                <div class="create-steps">
                    <div class="step-item active" data-step="1">
                        <div class="step-circle">1</div>
                        <span class="step-label">Upload</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-item" data-step="2">
                        <div class="step-circle">2</div>
                        <span class="step-label">Mapping</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-item" data-step="3">
                        <div class="step-circle">3</div>
                        <span class="step-label">Simpan</span>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary" id="createSourceBadge">Karyawan</span>
                </div>
            </div>

            <!-- Skeleton Loading (muncul saat overlay dibuka) -->
            <div id="createSkeleton" class="create-skeleton">
                <div class="skeleton-left">
                    <div class="skeleton-block skeleton-header"></div>
                    <div class="skeleton-block skeleton-preview"></div>
                </div>
                <div class="skeleton-right">
                    <div class="skeleton-block skeleton-card"></div>
                    <div class="skeleton-block skeleton-card"></div>
                    <div class="skeleton-block skeleton-card short"></div>
                </div>
            </div>

            <!-- Real Content (tersembunyi sampai skeleton selesai) -->
            <div id="createContent" class="create-content d-none">
                <div class="create-body">
                    <!-- LEFT: Preview -->
                    <div class="create-preview-col">
                        <div class="create-card">
                            <div class="create-card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Preview Dokumen</h6>
                                <small class="text-muted" id="preview-hint" style="display:none;">Seleksi teks lalu klik "Terapkan Mapping"</small>
                            </div>
                            <div class="create-card-body p-0">
                                <div id="docx-container">
                                    <div class="text-center py-5 text-muted" id="empty-state">
                                        <span class="iconify mb-3" data-icon="mdi:file-document-outline" style="font-size:3rem;opacity:.4;"></span>
                                        <p class="mt-2 mb-0">Upload file DOCX untuk memulai</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT: Sidebar Steps -->
                    <div class="create-sidebar-col">
                        <!-- Step 1: Upload -->
                        <div class="create-card mb-3" id="upload-section">
                            <div class="create-card-header">
                                <h6 class="mb-0">1. Upload Template</h6>
                            </div>
                            <div class="create-card-body">
                                <form id="formUpload" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">File DOCX <span class="text-danger">*</span></label>
                                        <input type="file" name="template_file" id="fileInput" class="form-control form-control-sm" accept=".docx" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Tabel Sumber Data <span class="text-danger">*</span></label>
                                        <select name="source_table" id="source_table_select" class="form-select form-select-sm" required>
                                            <option value="karyawan">Karyawan</option>
                                            <option value="pelamar">Rekrutan</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm w-100" id="btnLoad">
                                        <span id="btnLoadText">Load Dokumen</span>
                                        <span id="btnLoadSpinner" class="spinner-border spinner-border-sm d-none"></span>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Step 2: Mapping -->
                        <div class="create-card mb-3" id="mapping-section" style="display:none;">
                            <div class="create-card-header">
                                <h6 class="mb-0">2. Mapping Field</h6>
                            </div>
                            <div class="create-card-body">
                                <div id="selection-info" class="alert alert-warning py-2 mb-3" style="display:none;">
                                    <small class="d-block text-muted">Teks Terpilih:</small>
                                    <strong id="selected-text-display" class="small"></strong>
                                </div>

                                <div id="field-selector-group" style="display:none;">
                                    <label class="form-label small fw-semibold">Ganti dengan Field:</label>
                                    <select id="field-selector" class="form-select form-select-sm mb-2">
                                        <option value="">-- Pilih Field --</option>
                                        <optgroup label="── Kolom Database ──" id="optgroup-db"></optgroup>
                                        <optgroup label="── Input Manual (Diisi saat Generate) ──">
                                            <option value="__manual_text__">Teks Manual</option>
                                            <option value="__manual_textarea__">Textarea Manual</option>
                                            <option value="__manual_date__">Tanggal Manual</option>
                                            <option value="__manual_number__">Angka Manual</option>
                                            <option value="__manual_select__">Dropdown Manual</option>
                                            <option value="__manual_checkbox__">Checkbox Manual</option>
                                        </optgroup>
                                        <optgroup label="── Otomatis / Sistem ──">
                                            <option value="__auto_date__">Tanggal Otomatis</option>
                                            <option value="__formula__">Rumus (Nomor Surat)</option>
                                            <option value="__auth_field__">Data User Login</option>
                                            <option value="__relation_single__">Relasi Single</option>
                                        </optgroup>
                                        <optgroup label="── Loop / Collection ──">
                                            <option value="__loop_manual__">Loop Manual</option>
                                            <option value="__loop_relation__">Loop dari Relasi</option>
                                        </optgroup>
                                    </select>
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-success btn-sm" id="btnApplyMapping">Terapkan Mapping</button>
                                        <button class="btn btn-outline-secondary btn-sm" id="btnCancelSelection">Batal</button>
                                    </div>
                                </div>

                                <div id="no-selection-hint" class="text-muted small text-center py-2">
                                    Seleksi teks di preview untuk mulai mapping
                                </div>

                                <hr>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="small fw-semibold mb-0">Mapping Diterapkan (<span id="mapping-count">0</span>)</h6>
                                </div>
                                <div id="mappings-list" style="max-height: 280px; overflow-y: auto;">
                                    <p class="text-muted small text-center">Belum ada mapping</p>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Simpan -->
                        <div class="create-card" id="save-section" style="display:none;">
                            <div class="create-card-header">
                                <h6 class="mb-0">3. Simpan Template</h6>
                            </div>
                            <div class="create-card-body">
                                <form id="formSave">
                                    @csrf
                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold">Nama Template <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="inputName" class="form-control form-control-sm" required placeholder="cth: Laporan Data Karyawan">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold">Kode Template <span class="text-danger">*</span></label>
                                        <input type="text" name="code" id="inputCode" class="form-control form-control-sm" required placeholder="cth: LDK">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Kategori</label>
                                        <select name="category" class="form-select form-select-sm">
                                            <option value="karyawan">Karyawan</option>
                                            <option value="pelamar">Rekrutan</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm w-100" id="btnSave"
                                        data-url="{{ route('HR.reports.save.mapping') }}"
                                        data-token="{{ csrf_token() }}"
                                        data-redirect="{{ route('HR.reports.index') }}">
                                        <span id="btnSaveText">Simpan Template</span>
                                        <span id="btnSaveSpinner" class="spinner-border spinner-border-sm d-none"></span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Field Config Modal (tetap dipakai di dalam create) -->
    <div class="modal fade" id="fieldConfigModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="configModalTitle">Konfigurasi Field</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="configModalBody"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnConfirmConfig">Terapkan</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="generateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title d-flex align-items-center gap-2">
                        <span class="iconify" data-icon="mdi:file-document-check-outline" style="font-size:1.4rem;"></span>
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
                            <button class="btn btn-sm btn-outline-secondary d-none" id="btnBackToFolders" onclick="showHistoryFolders()">
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
                                    <span class="iconify mb-3" data-icon="mdi:file-document-outline" style="font-size:3rem;opacity:.3;"></span>
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

            document.getElementById('deleteConfirmModal').addEventListener('hidden.bs.modal', function () {
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
            const toast = new bootstrap.Toast(toastElement, { delay: 4000 });
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
        // 2. CREATE OVERLAY CONTROLLER + SKELETON
        // =====================================================
        let createCreatorInitialized = false;
        let createCreatorInstance = null;

        function openCreateOverlay() {
            const overlay = document.getElementById('createTemplateOverlay');
            const skeleton = document.getElementById('createSkeleton');
            const content = document.getElementById('createContent');
            let editIndex = null;
            let userPickedNewFile = false;
            // Tampilkan overlay + skeleton dulu
            overlay.classList.add('is-open');
            overlay.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';

            skeleton.classList.remove('d-none');
            content.classList.add('d-none');

            // Simulasikan loading + init
            setTimeout(() => {
                skeleton.classList.add('d-none');
                content.classList.remove('d-none');

                // Init create logic hanya sekali (atau reset jika sudah pernah)
                if (!createCreatorInitialized) {
                    createCreatorInstance = initReportCreator();
                    createCreatorInitialized = true;
                } else {
                    // Reset state setiap kali dibuka ulang
                    if (createCreatorInstance && typeof createCreatorInstance.reset === 'function') {
                        createCreatorInstance.reset();
                    }
                }

                updateCreateSteps(1);
            }, 650); // waktu skeleton
        }

        function closeCreateOverlay() {
            const overlay = document.getElementById('createTemplateOverlay');
            overlay.classList.remove('is-open');
            overlay.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';

            // Reset visual state
            setTimeout(() => {
                document.getElementById('createSkeleton').classList.remove('d-none');
                document.getElementById('createContent').classList.add('d-none');
                if (createCreatorInstance && typeof createCreatorInstance.reset === 'function') {
                    createCreatorInstance.reset();
                }
                updateCreateSteps(1);
            }, 300);
        }

        function updateCreateSteps(activeStep) {
            document.querySelectorAll('.create-steps .step-item').forEach(item => {
                const step = parseInt(item.dataset.step);
                item.classList.toggle('active', step === activeStep);
                item.classList.toggle('done', step < activeStep);
            });
        }

        // Event tombol buka
        document.getElementById('btnOpenCreateModal')?.addEventListener('click', openCreateOverlay);
        document.getElementById('btnOpenCreateModalEmpty')?.addEventListener('click', openCreateOverlay);
        document.getElementById('btnCloseCreate')?.addEventListener('click', closeCreateOverlay);

        // Tutup dengan ESC
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && document.getElementById('createTemplateOverlay').classList.contains('is-open')) {
                closeCreateOverlay();
            }
        });

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
            document.getElementById('generateForm')?.addEventListener('submit', async function (e) {
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
                form.addEventListener('submit', function (e) {
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

        window.doPreview = function () {
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

        async function openEditOverlay(templateId) {
            try {
                // Buka overlay dulu + skeleton
                openCreateOverlay();

                // Tunggu skeleton selesai (sedikit lebih lama dari create biasa)
                await new Promise(r => setTimeout(r, 700));

                const res = await fetch(`/HR-dashboard/reports/${templateId}/edit-data`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const json = await res.json();
                if (!json.success) {
                    alert(json.message || 'Gagal memuat data template');
                    closeCreateOverlay();
                    return;
                }

                const data = json.data;
                isEditMode = true;
                currentEditId = data.id;

                // Ubah judul overlay
                document.getElementById('createOverlayTitle').textContent = 'Edit Template: ' + data.name;
                document.getElementById('createOverlaySubtitle').textContent = 'Edit mapping & metadata';

                // Ubah teks tombol save
                document.getElementById('btnSaveText').textContent = 'Update Template';
                document.getElementById('btnSave').classList.remove('btn-primary');
                document.getElementById('btnSave').classList.add('btn-warning');

                // Prefill form metadata
                document.getElementById('inputName').value = data.name || '';
                document.getElementById('inputCode').value = data.code || '';
                const categorySelect = document.querySelector('#formSave select[name="category"]');
                if (categorySelect) categorySelect.value = data.category || 'karyawan';

                // Prefill source table
                const sourceSelect = document.getElementById('source_table_select');
                if (sourceSelect) sourceSelect.value = data.source_table || 'karyawan';

                // Update badge
                document.getElementById('createSourceBadge').textContent =
                    data.source_table === 'pelamar' ? 'Rekrutan' : 'Karyawan';

                // Inject data ke window.APP_DATA supaya initReportCreator bisa pakai
                window.APP_DATA.columns = data.allowedColumns;
                window.APP_DATA.saveUrl = `/HR-dashboard/reports/${data.id}`; // route update
                window.APP_DATA.existingMappings = data.existingMappings;
                window.APP_DATA.templateUrl = data.template_url;
                window.APP_DATA.sourceTable = data.source_table;

                // Karena createCreatorInstance sudah di-init di openCreateOverlay,
                // kita panggil reset + load existing
                if (createCreatorInstance && typeof createCreatorInstance.loadForEdit === 'function') {
                    createCreatorInstance.loadForEdit(data);
                } else {
                    // Fallback: force re-init
                    createCreatorInitialized = false;
                    createCreatorInstance = initReportCreator();
                    createCreatorInitialized = true;
                    if (createCreatorInstance.loadForEdit) {
                        createCreatorInstance.loadForEdit(data);
                    }
                }

                updateCreateSteps(2); // langsung ke Mapping

            } catch (err) {
                console.error('openEditOverlay error:', err);
                alert('Gagal membuka editor: ' + err.message);
                closeCreateOverlay();
            }
        }

        // Override close agar reset mode
        const originalCloseCreateOverlay = closeCreateOverlay;
        closeCreateOverlay = function () {
            isEditMode = false;
            currentEditId = null;

            // Kembalikan judul & tombol ke mode Create
            document.getElementById('createOverlayTitle').textContent = 'Buat Template Laporan';
            document.getElementById('createOverlaySubtitle').textContent = 'Upload → Mapping → Simpan';
            document.getElementById('btnSaveText').textContent = 'Simpan Template';
            document.getElementById('btnSave').classList.remove('btn-warning');
            document.getElementById('btnSave').classList.add('btn-primary');

            originalCloseCreateOverlay();
        };

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

            fetch('{{ route("HR.reports.history.data") }}')
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
                            historyData[key] = { name: item.template_name || 'Template', files: [] };
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
                if (item.status === 'completed') { badge = 'bg-success-subtle text-success'; status = 'Sukses'; }
                if (item.status === 'failed')   { badge = 'bg-danger-subtle text-danger'; status = 'Gagal'; }

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
                            <button class="btn btn-sm btn-outline-primary" 
                                onclick="showHistoryPreview(${item.id}, '${item.report_title.replace(/'/g, "\\'")}', '${item.download_url}', '${(item.file_extension||'').toUpperCase()}')">
                                <span class="iconify" data-icon="mdi:eye"></span>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary"
                                onclick="showHistoryEdit(${item.id}, '${item.report_title.replace(/'/g, "\\'")}')">
                                <span class="iconify" data-icon="mdi:pencil"></span>
                            </button>
                            <button class="btn btn-sm btn-outline-danger"
                                onclick="showHistoryDelete(${item.id}, '${item.report_title.replace(/'/g, "\\'")}')">
                                <span class="iconify" data-icon="mdi:delete"></span>
                            </button>
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

                    if (extension === 'DOCX') {
                        window.docx.renderAsync(buffer, container, null, {
                            className: 'docx',
                            inWrapper: true,
                            breakPages: true
                        });
                    } else if (extension === 'PDF') {
                        const blob = new Blob([buffer], { type: 'application/pdf' });
                        const url = URL.createObjectURL(blob);
                        container.innerHTML = `<iframe src="${url}" style="width:100%;height:70vh;border:none;"></iframe>`;
                    } else {
                        body.innerHTML = `<div class="alert alert-warning">Format tidak didukung</div>`;
                    }

                    // Tombol download di bawah
                    body.insertAdjacentHTML('beforeend', `
                        <div class="mt-3 text-end">
                            <a href="${downloadUrl}" class="btn btn-primary btn-sm" target="_blank">
                                <span class="iconify me-1" data-icon="mdi:download"></span>Download
                            </a>
                        </div>`);
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
                body: JSON.stringify({ report_title: title })
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

        // =====================================================
        // 3. REPORT CREATOR (LOGIKA CREATE ASLI - SENSITIF)
        // =====================================================
        function initReportCreator() {
            const COLUMNS = window.APP_DATA.columns;
            const SAVE_URL = window.APP_DATA.saveUrl;
            const CSRF_TOKEN = window.APP_DATA.csrfToken;
            const REDIRECT_URL = window.APP_DATA.redirectUrl;

            const RELATIONS = {}; // isi jika ada relasi

            const AUTH_FIELDS = {
                'Data User Login': ['username', 'jabatan'],
                'Data Karyawan (Login)': ['nama_lengkap', 'nip', 'email', 'whatsapp']
            };
            const BOPEN = '{' + '{';
            const BCLOSE = '}' + '}';

            let currentFile = null;
            let currentSourceTable = 'karyawan';
            let currentSelection = null;
            let mappings = [];
            let pendingConfigType = null;
            let loopColumnIndex = 0;

            // DOM elements
            const docxContainer = document.getElementById('docx-container');
            const emptyState = document.getElementById('empty-state');
            const mappingSection = document.getElementById('mapping-section');
            const saveSection = document.getElementById('save-section');
            const selectionInfo = document.getElementById('selection-info');
            const selectedTextDisplay = document.getElementById('selected-text-display');
            const fieldSelectorGroup = document.getElementById('field-selector-group');
            const fieldSelector = document.getElementById('field-selector');
            const noSelectionHint = document.getElementById('no-selection-hint');
            const mappingsList = document.getElementById('mappings-list');
            const mappingCount = document.getElementById('mapping-count');
            const previewHint = document.getElementById('preview-hint');
            const formUpload = document.getElementById('formUpload');
            const btnLoad = document.getElementById('btnLoad');
            const btnLoadText = document.getElementById('btnLoadText');
            const btnLoadSpinner = document.getElementById('btnLoadSpinner');
            const inputName = document.getElementById('inputName');
            const inputCode = document.getElementById('inputCode');
            const formSave = document.getElementById('formSave');
            const btnSave = document.getElementById('btnSave');
            const btnSaveText = document.getElementById('btnSaveText');
            const btnSaveSpinner = document.getElementById('btnSaveSpinner');
            const btnApplyMapping = document.getElementById('btnApplyMapping');
            const btnCancelSelection = document.getElementById('btnCancelSelection');
            const sourceBadge = document.getElementById('createSourceBadge');

            if (!formUpload) {
                console.error('formUpload element not found!');
                return null;
            }

            // Auto generate code dari nama
            inputName.addEventListener('input', function () {
                const code = this.value.split(' ').filter(w => w.length > 0).map(w => w[0].toUpperCase()).join('');
                inputCode.value = code;
            });

            // ===== UPLOAD & RENDER DOCX =====
            formUpload.addEventListener('submit', async function (e) {
                e.preventDefault();

                const fileInput = document.getElementById('fileInput');
                currentFile = fileInput.files[0];
                currentSourceTable = document.getElementById('source_table_select').value;

                if (!currentFile) {
                    alert('Pilih file DOCX terlebih dahulu!');
                    return;
                }

                // Update badge
                sourceBadge.textContent = currentSourceTable === 'karyawan' ? 'Karyawan' : 'Rekrutan';

                btnLoadText.textContent = 'Memuat...';
                btnLoadSpinner.classList.remove('d-none');
                btnLoad.disabled = true;

                mappings = [];
                currentSelection = null;
                updateMappingsList();

                try {
                    if (!window.docx || typeof window.docx.renderAsync !== 'function') {
                        throw new Error('Library docx-preview belum ter-load. Refresh halaman.');
                    }

                    emptyState.style.display = 'none';
                    docxContainer.innerHTML = `
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary"></div>
                            <p class="mt-2 text-muted">Memuat dokumen...</p>
                        </div>`;

                    const arrayBuffer = await currentFile.arrayBuffer();

                    await window.docx.renderAsync(arrayBuffer, docxContainer, null, {
                        className: 'docx',
                        inWrapper: true,
                        ignoreWidth: false,
                        ignoreHeight: false,
                        breakPages: true,
                    });

                    docxContainer.addEventListener('mouseup', onMouseUp);
                    populateFieldSelector(currentSourceTable);

                    mappingSection.style.display = 'block';
                    saveSection.style.display = 'block';
                    previewHint.style.display = 'inline';

                    updateCreateSteps(2); // pindah ke step Mapping

                } catch (err) {
                    console.error('Load error:', err);
                    docxContainer.innerHTML = `<div class="alert alert-danger m-3">Gagal memuat: ${err.message}</div>`;
                } finally {
                    btnLoadText.textContent = 'Load Dokumen';
                    btnLoadSpinner.classList.add('d-none');
                    btnLoad.disabled = false;
                }
            });

            // ===== SELECTION =====
            function onMouseUp(e) {
                if (e.target.classList.contains('placeholder-badge')) return;
                const sel = window.getSelection();
                const text = sel ? sel.toString().trim() : '';
                if (text.length < 2) return;

                if (text.includes('{') || text.includes('}')) {
                    alert('Teks yang dipilih mengandung tanda "{" atau "}". Hapus dulu tanda kurung tersebut dari file docx, lalu upload ulang.');
                    sel.removeAllRanges();
                    return;
                }

                currentSelection = {
                    text: text,
                    range: sel.getRangeAt(0).cloneRange()
                };
                selectionInfo.style.display = 'block';
                selectedTextDisplay.textContent = text.length > 60 ? text.substring(0, 60) + '...' : text;
                fieldSelectorGroup.style.display = 'block';
                noSelectionHint.style.display = 'none';
                sel.removeAllRanges();
            }

            function populateFieldSelector(table) {
                const cols = COLUMNS[table] || {};
                const dbGroup = document.getElementById('optgroup-db');
                if (!dbGroup) return;
                dbGroup.innerHTML = '';
                for (const key in cols) {
                    if (cols.hasOwnProperty(key)) {
                        const opt = document.createElement('option');
                        opt.value = key;
                        opt.textContent = cols[key] + ' (' + key + ')';
                        dbGroup.appendChild(opt);
                    }
                }
            }

            // ===== APPLY MAPPING =====
            btnApplyMapping.addEventListener('click', function () {
                if (!currentSelection) {
                    alert('Pilih teks di preview!');
                    return;
                }
                const field = fieldSelector.value;
                if (!field) {
                    alert('Pilih field!');
                    return;
                }

                if (field.indexOf('__') === 0) {
                    pendingConfigType = field.replace(/^__|__$/g, '');
                    openConfigModal(pendingConfigType);
                    return;
                }

                applyMapping(field, 'db', { label: field });
            });

            // ===== CONFIG MODAL (semua tipe field) =====
            function openConfigModal(type) {
                let html = '';
                let title = '';

                try {
                    if (type === 'auto_date') {
                        title = 'Konfigurasi Tanggal Otomatis';
                        html = `
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Placeholder Key</label>
                                <input type="text" id="cfg_key" class="form-control form-control-sm" value="tanggal_${Date.now().toString().slice(-6)}" pattern="^[a-z0-9_]+$" required>
                                <small class="text-muted">Huruf kecil, angka, underscore.</small>
                            </div>
                            <hr>
                            <h6 class="small fw-semibold mb-2">Komponen & Format Tanggal</h6>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Hari / Tanggal</label>
                                <select id="cfg_day_format" class="form-select form-select-sm">
                                    <option value="none">Tidak Ditampilkan</option>
                                    <option value="number">Angka Tanggal (25)</option>
                                    <option value="word">Kata Tanggal (Dua Puluh Lima)</option>
                                    <option value="word_upper">KATA TANGGAL (DUA PULUH LIMA)</option>
                                    <option value="day_name">Nama Hari (Kamis)</option>
                                    <option value="day_name_upper">NAMA HARI (KAMIS)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Bulan</label>
                                <select id="cfg_month_format" class="form-select form-select-sm">
                                    <option value="none">Tidak Ditampilkan</option>
                                    <option value="number">Angka Bulan (06)</option>
                                    <option value="month_name">Nama Bulan (Juni)</option>
                                    <option value="month_name_upper">NAMA BULAN (JUNI)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Tahun</label>
                                <select id="cfg_year_format" class="form-select form-select-sm">
                                    <option value="none">Tidak Ditampilkan</option>
                                    <option value="number">Angka Tahun (2026)</option>
                                    <option value="word">Kata Tahun (Dua Ribu Dua Puluh Enam)</option>
                                    <option value="word_upper">KATA TAHUN (DUA RIBU DUA PULUH ENAM)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Pemisah Antar Komponen</label>
                                <input type="text" id="cfg_separator" class="form-control form-control-sm" value=" " placeholder="cth: spasi, koma, strip">
                            </div>`;
                    } else if (type === 'formula') {
                        title = 'Konfigurasi Rumus';
                        const placeholder = 'KP/{tahun}/{bulan_romawi}/{urutan:4}';
                        html = `
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Placeholder Key</label>
                                <input type="text" id="cfg_key" class="form-control form-control-sm" value="nomor_surat_${Date.now().toString().slice(-6)}" pattern="^[a-z0-9_]+$" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Template Rumus</label>
                                <input type="text" id="cfg_template" class="form-control form-control-sm" placeholder="${placeholder}">
                                <small class="text-muted d-block mt-2">
                                    <strong>Variabel tersedia:</strong><br>
                                    • {tahun} → 2026<br>
                                    • {bulan} → 06<br>
                                    • {bulan_romawi} → VI<br>
                                    • {urutan:4} → 0001<br>
                                    • {urutan_romawi} → I, II, III
                                </small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nomor Terakhir / Start From (Opsional)</label>
                                <input type="number" id="cfg_last_number" class="form-control form-control-sm" placeholder="cth: 233" min="0">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Counter Key</label>
                                <input type="text" id="cfg_counter_key" class="form-control form-control-sm" placeholder="kosongkan untuk auto">
                            </div>`;
                    } else if (type === 'auth_field') {
                        title = 'Konfigurasi Data User / Karyawan Login';
                        let optionsHtml = '';
                        for (const group in AUTH_FIELDS) {
                            optionsHtml += `<optgroup label="${group}">`;
                            AUTH_FIELDS[group].forEach(f => {
                                optionsHtml += `<option value="${f}">${f}</option>`;
                            });
                            optionsHtml += `</optgroup>`;
                        }
                        html = `
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Placeholder Key</label>
                                <input type="text" id="cfg_key" class="form-control form-control-sm" value="pembuat_${Date.now().toString().slice(-6)}" pattern="^[a-z0-9_]+$" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Field yang Diambil</label>
                                <select id="cfg_field" class="form-select form-select-sm">${optionsHtml}</select>
                            </div>`;
                    } else if (type === 'relation_single') {
                        const rels = (RELATIONS[currentSourceTable] && RELATIONS[currentSourceTable].single) || {};
                        title = 'Konfigurasi Relasi Single';
                        let relOptions = '<option value="">-- Pilih Relasi --</option>';
                        for (const k in rels) {
                            if (rels.hasOwnProperty(k)) {
                                relOptions += `<option value="${k}">${rels[k].label} (${k})</option>`;
                            }
                        }
                        html = `
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Placeholder Key</label>
                                <input type="text" id="cfg_key" class="form-control form-control-sm" pattern="^[a-z0-9_]+$" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Relasi</label>
                                <select id="cfg_relation" class="form-select form-select-sm" onchange="window._updateRelationFields()">${relOptions}</select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Field yang Diambil</label>
                                <select id="cfg_field" class="form-select form-select-sm"><option value="">-- Pilih Relasi Dulu --</option></select>
                            </div>`;
                    } else if (type === 'loop_manual') {
                        title = 'Konfigurasi Loop Manual';
                        html = `
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Placeholder Key (nama collection)</label>
                                <input type="text" id="cfg_key" class="form-control form-control-sm" value="peserta_${Date.now().toString().slice(-6)}" pattern="^[a-z0-9_]+$" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Kolom-kolom Tabel</label>
                                <div id="loop_columns"></div>
                                <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="window._addLoopColumn()">+ Tambah Kolom</button>
                            </div>`;
                    } else if (type === 'loop_relation') {
                        const rels = (RELATIONS[currentSourceTable] && RELATIONS[currentSourceTable].collection) || {};
                        title = 'Konfigurasi Loop Relasi';
                        let relOptions = '<option value="">-- Pilih Relasi --</option>';
                        for (const k in rels) {
                            if (rels.hasOwnProperty(k)) {
                                relOptions += `<option value="${k}">${rels[k].label} (${k})</option>`;
                            }
                        }
                        html = `
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Placeholder Key</label>
                                <input type="text" id="cfg_key" class="form-control form-control-sm" pattern="^[a-z0-9_]+$" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Relasi</label>
                                <select id="cfg_relation" class="form-select form-select-sm">${relOptions}</select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Field (pisahkan koma)</label>
                                <input type="text" id="cfg_fields" class="form-control form-control-sm" placeholder="nama, tanggal, status">
                            </div>`;
                    } else if (type === 'manual_text') {
                        title = 'Konfigurasi Input Teks Manual';
                        html = `
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Placeholder Key</label>
                                <input type="text" id="cfg_key" class="form-control form-control-sm" value="teks_${Date.now().toString().slice(-6)}" pattern="^[a-z0-9_]+$" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Label Field</label>
                                <input type="text" id="cfg_label" class="form-control form-control-sm" placeholder="cth: Nama Lengkap">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Default Value (Opsional)</label>
                                <input type="text" id="cfg_default" class="form-control form-control-sm">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Placeholder Text (Opsional)</label>
                                <input type="text" id="cfg_placeholder" class="form-control form-control-sm">
                            </div>`;
                    } else if (type === 'manual_textarea') {
                        title = 'Konfigurasi Textarea Manual';
                        html = `
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Placeholder Key</label>
                                <input type="text" id="cfg_key" class="form-control form-control-sm" value="textarea_${Date.now().toString().slice(-6)}" pattern="^[a-z0-9_]+$" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Label Field</label>
                                <input type="text" id="cfg_label" class="form-control form-control-sm" placeholder="cth: Alamat Lengkap">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Jumlah Baris</label>
                                <input type="number" id="cfg_rows" class="form-control form-control-sm" value="3" min="2" max="10">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Default Value (Opsional)</label>
                                <textarea id="cfg_default" class="form-control form-control-sm" rows="2"></textarea>
                            </div>`;
                    } else if (type === 'manual_date') {
                        title = 'Konfigurasi Tanggal Manual';
                        html = `
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Placeholder Key</label>
                                <input type="text" id="cfg_key" class="form-control form-control-sm" value="tanggal_manual_${Date.now().toString().slice(-6)}" pattern="^[a-z0-9_]+$" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Label Field</label>
                                <input type="text" id="cfg_label" class="form-control form-control-sm" placeholder="cth: Tanggal Lahir">
                            </div>
                            <hr>
                            <h6 class="small fw-semibold mb-2">Komponen & Format Output</h6>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Hari / Tanggal</label>
                                <select id="cfg_day_format" class="form-select form-select-sm">
                                    <option value="none">Tidak Ditampilkan</option>
                                    <option value="number">Angka Tanggal (25)</option>
                                    <option value="word">Kata Tanggal (Dua Puluh Lima)</option>
                                    <option value="word_upper">KATA TANGGAL (DUA PULUH LIMA)</option>
                                    <option value="day_name">Nama Hari (Kamis)</option>
                                    <option value="day_name_upper">NAMA HARI (KAMIS)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Bulan</label>
                                <select id="cfg_month_format" class="form-select form-select-sm">
                                    <option value="none">Tidak Ditampilkan</option>
                                    <option value="number">Angka Bulan (06)</option>
                                    <option value="month_name">Nama Bulan (Juni)</option>
                                    <option value="month_name_upper">NAMA BULAN (JUNI)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Tahun</label>
                                <select id="cfg_year_format" class="form-select form-select-sm">
                                    <option value="none">Tidak Ditampilkan</option>
                                    <option value="number">Angka Tahun (2026)</option>
                                    <option value="word">Kata Tahun (Dua Ribu Dua Puluh Enam)</option>
                                    <option value="word_upper">KATA TAHUN (DUA RIBU DUA PULUH ENAM)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Pemisah Antar Komponen</label>
                                <input type="text" id="cfg_separator" class="form-control form-control-sm" value=" ">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Default Value (Opsional)</label>
                                <input type="date" id="cfg_default" class="form-control form-control-sm">
                            </div>`;
                    } else if (type === 'manual_number') {
                        title = 'Konfigurasi Angka Manual';
                        html = `
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Placeholder Key</label>
                                <input type="text" id="cfg_key" class="form-control form-control-sm" value="angka_${Date.now().toString().slice(-6)}" pattern="^[a-z0-9_]+$" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Label Field</label>
                                <input type="text" id="cfg_label" class="form-control form-control-sm" placeholder="cth: Jumlah">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Tipe Angka</label>
                                <select id="cfg_number_type" class="form-select form-select-sm">
                                    <option value="number">Angka Biasa (1,234.56)</option>
                                    <option value="currency">Mata Uang (Rp 1.234)</option>
                                    <option value="integer">Bulat (1234)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Default Value (Opsional)</label>
                                <input type="number" id="cfg_default" class="form-control form-control-sm">
                            </div>`;
                    } else if (type === 'manual_select') {
                        title = 'Konfigurasi Dropdown Manual';
                        html = `
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Placeholder Key</label>
                                <input type="text" id="cfg_key" class="form-control form-control-sm" value="pilihan_${Date.now().toString().slice(-6)}" pattern="^[a-z0-9_]+$" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Label Field</label>
                                <input type="text" id="cfg_label" class="form-control form-control-sm" placeholder="cth: Status">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Opsi (pisahkan dengan koma)</label>
                                <textarea id="cfg_options" class="form-control form-control-sm" rows="3" placeholder="cth: Aktif, Nonaktif, Cuti"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Default Value (Opsional)</label>
                                <input type="text" id="cfg_default" class="form-control form-control-sm">
                            </div>`;
                    } else if (type === 'manual_checkbox') {
                        title = 'Konfigurasi Checkbox Manual';
                        html = `
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Placeholder Key</label>
                                <input type="text" id="cfg_key" class="form-control form-control-sm" value="checkbox_${Date.now().toString().slice(-6)}" pattern="^[a-z0-9_]+$" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Label Field</label>
                                <input type="text" id="cfg_label" class="form-control form-control-sm" placeholder="cth: Setuju">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Default Value</label>
                                <select id="cfg_default" class="form-select form-select-sm">
                                    <option value="0">Tidak Dicentang</option>
                                    <option value="1">Dicentang</option>
                                </select>
                            </div>`;
                    }

                    document.getElementById('configModalTitle').innerHTML = title;
                    document.getElementById('configModalBody').innerHTML = html;

                    const modal = new bootstrap.Modal(document.getElementById('fieldConfigModal'));
                    modal.show();

                    if (type === 'loop_manual') {
                        loopColumnIndex = 0;
                        setTimeout(() => window._addLoopColumn(), 100);
                    }
                } catch (err) {
                    console.error('Error in openConfigModal:', err);
                    alert('Error membuka modal: ' + err.message);
                }
            }

            // Helper global untuk config modal
            window._updateRelationFields = function () {
                const rel = document.getElementById('cfg_relation')?.value;
                const fieldSelect = document.getElementById('cfg_field');
                if (!fieldSelect) return;
                const rels = (RELATIONS[currentSourceTable] && RELATIONS[currentSourceTable].single) || {};
                const fields = (rels[rel] && rels[rel].fields) || [];
                fieldSelect.innerHTML = fields.length
                    ? fields.map(f => `<option value="${f}">${f}</option>`).join('')
                    : '<option value="">-- Tidak ada field --</option>';
                const keyInput = document.getElementById('cfg_key');
                if (keyInput && !keyInput.value) keyInput.value = rel + '_field';
            };

            window._addLoopColumn = function (key = '', label = '', type = 'text') {
                const container = document.getElementById('loop_columns');
                if (!container) return;
                const idx = loopColumnIndex++;
                const row = document.createElement('div');
                row.className = 'row g-2 mb-2 loop-col-row';
                row.innerHTML = `
                    <div class="col-4"><input type="text" class="form-control form-control-sm lc-key" placeholder="key" value="${key}" required></div>
                    <div class="col-4"><input type="text" class="form-control form-control-sm lc-label" placeholder="Label" value="${label}" required></div>
                    <div class="col-3">
                        <select class="form-select form-select-sm lc-type">
                            <option value="text" ${type === 'text' ? 'selected' : ''}>Text</option>
                            <option value="number" ${type === 'number' ? 'selected' : ''}>Number</option>
                            <option value="date" ${type === 'date' ? 'selected' : ''}>Date</option>
                        </select>
                    </div>
                    <div class="col-1"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.loop-col-row').remove()">✕</button></div>`;
                container.appendChild(row);
            };

            // Confirm config
            document.getElementById('btnConfirmConfig').addEventListener('click', function () {
                const type = pendingConfigType;
                let config = {};
                let key = '';

                try {
                    if (type === 'auto_date') {
                        key = document.getElementById('cfg_key').value.trim();
                        if (!key || !/^[a-z0-9_]+$/.test(key)) { alert('Key tidak valid!'); return; }
                        config = {
                            day_format: document.getElementById('cfg_day_format').value,
                            month_format: document.getElementById('cfg_month_format').value,
                            year_format: document.getElementById('cfg_year_format').value,
                            separator: document.getElementById('cfg_separator').value,
                            label: 'Tanggal Otomatis'
                        };
                    } else if (type === 'formula') {
                        key = document.getElementById('cfg_key').value.trim();
                        if (!key || !/^[a-z0-9_]+$/.test(key)) { alert('Key tidak valid!'); return; }
                        const template = document.getElementById('cfg_template').value.trim();
                        if (!template) { alert('Template rumus wajib diisi!'); return; }
                        const lastNumberStr = document.getElementById('cfg_last_number').value.trim();
                        config = {
                            template: template,
                            counter_key: document.getElementById('cfg_counter_key').value.trim() || null,
                            last_number: lastNumberStr !== '' ? parseInt(lastNumberStr) : null,
                            label: 'Rumus'
                        };
                    } else if (type === 'auth_field') {
                        key = document.getElementById('cfg_key').value.trim();
                        if (!key || !/^[a-z0-9_]+$/.test(key)) { alert('Key tidak valid!'); return; }
                        config = {
                            field: document.getElementById('cfg_field').value,
                            label: 'Data User Login'
                        };
                    } else if (type === 'relation_single') {
                        key = document.getElementById('cfg_key').value.trim();
                        if (!key || !/^[a-z0-9_]+$/.test(key)) { alert('Key tidak valid!'); return; }
                        const relation = document.getElementById('cfg_relation').value;
                        const field = document.getElementById('cfg_field').value;
                        if (!relation || !field) { alert('Relasi dan field wajib dipilih!'); return; }
                        config = { relation, field, label: 'Relasi Single' };
                    } else if (type === 'loop_manual') {
                        key = document.getElementById('cfg_key').value.trim();
                        if (!key || !/^[a-z0-9_]+$/.test(key)) { alert('Key tidak valid!'); return; }
                        const columns = [];
                        document.querySelectorAll('.loop-col-row').forEach(row => {
                            const k = row.querySelector('.lc-key').value.trim();
                            const l = row.querySelector('.lc-label').value.trim();
                            const t = row.querySelector('.lc-type').value;
                            if (k && l) columns.push({ key: k, label: l, type: t });
                        });
                        if (columns.length === 0) { alert('Minimal 1 kolom!'); return; }
                        config = { columns, label: 'Loop Manual' };
                    } else if (type === 'loop_relation') {
                        key = document.getElementById('cfg_key').value.trim();
                        if (!key || !/^[a-z0-9_]+$/.test(key)) { alert('Key tidak valid!'); return; }
                        const relation = document.getElementById('cfg_relation').value;
                        if (!relation) { alert('Relasi wajib dipilih!'); return; }
                        const fieldsStr = document.getElementById('cfg_fields').value.trim();
                        config = {
                            relation,
                            fields: fieldsStr ? fieldsStr.split(',').map(s => s.trim()).filter(Boolean) : [],
                            label: 'Loop Relasi'
                        };
                    } else if (type === 'manual_text') {
                        key = document.getElementById('cfg_key').value.trim();
                        if (!key || !/^[a-z0-9_]+$/.test(key)) { alert('Key tidak valid!'); return; }
                        config = {
                            label: document.getElementById('cfg_label').value.trim() || key,
                            default: document.getElementById('cfg_default').value.trim(),
                            placeholder: document.getElementById('cfg_placeholder').value.trim()
                        };
                    } else if (type === 'manual_textarea') {
                        key = document.getElementById('cfg_key').value.trim();
                        if (!key || !/^[a-z0-9_]+$/.test(key)) { alert('Key tidak valid!'); return; }
                        config = {
                            label: document.getElementById('cfg_label').value.trim() || key,
                            rows: parseInt(document.getElementById('cfg_rows').value) || 3,
                            default: document.getElementById('cfg_default').value.trim()
                        };
                    } else if (type === 'manual_date') {
                        key = document.getElementById('cfg_key').value.trim();
                        if (!key || !/^[a-z0-9_]+$/.test(key)) { alert('Key tidak valid!'); return; }
                        config = {
                            label: document.getElementById('cfg_label').value.trim() || key,
                            day_format: document.getElementById('cfg_day_format').value,
                            month_format: document.getElementById('cfg_month_format').value,
                            year_format: document.getElementById('cfg_year_format').value,
                            separator: document.getElementById('cfg_separator').value,
                            default: document.getElementById('cfg_default').value
                        };
                    } else if (type === 'manual_number') {
                        key = document.getElementById('cfg_key').value.trim();
                        if (!key || !/^[a-z0-9_]+$/.test(key)) { alert('Key tidak valid!'); return; }
                        config = {
                            label: document.getElementById('cfg_label').value.trim() || key,
                            number_type: document.getElementById('cfg_number_type').value,
                            default: document.getElementById('cfg_default').value
                        };
                    } else if (type === 'manual_select') {
                        key = document.getElementById('cfg_key').value.trim();
                        if (!key || !/^[a-z0-9_]+$/.test(key)) { alert('Key tidak valid!'); return; }
                        const optionsStr = document.getElementById('cfg_options').value.trim();
                        if (!optionsStr) { alert('Opsi wajib diisi!'); return; }
                        config = {
                            label: document.getElementById('cfg_label').value.trim() || key,
                            options: optionsStr.split(',').map(s => s.trim()).filter(Boolean),
                            default: document.getElementById('cfg_default').value.trim()
                        };
                    } else if (type === 'manual_checkbox') {
                        key = document.getElementById('cfg_key').value.trim();
                        if (!key || !/^[a-z0-9_]+$/.test(key)) { alert('Key tidak valid!'); return; }
                        config = {
                            label: document.getElementById('cfg_label').value.trim() || key,
                            default: document.getElementById('cfg_default').value
                        };
                    }

                    applyMapping(key, type, config);
                    const modal = bootstrap.Modal.getInstance(document.getElementById('fieldConfigModal'));
                    if (modal) modal.hide();
                } catch (err) {
                    console.error('Config error:', err);
                    alert('Error: ' + err.message);
                }
            });

            // ===== APPLY MAPPING KE DOKUMEN =====
            function applyMapping(key, type, config) {
                let wrapperEl = null;
                try {
                    wrapperEl = document.createElement('span');
                    wrapperEl.className = 'text-mapped text-mapped-' + type;
                    wrapperEl.dataset.field = key;
                    wrapperEl.dataset.type = type;

                    const contents = currentSelection.range.extractContents();
                    wrapperEl.appendChild(contents);
                    currentSelection.range.insertNode(wrapperEl);

                    const badge = document.createElement('span');
                    badge.className = 'placeholder-badge placeholder-badge-' + type;
                    badge.textContent = key;
                    wrapperEl.appendChild(badge);
                } catch (err) {
                    console.warn('surroundContents failed:', err);
                    wrapperEl = null;
                }

                mappings.push({
                    find: currentSelection.text,
                    replace: key,
                    type: type,
                    config: config,
                    el: wrapperEl
                });
                updateMappingsList();
                clearSelection();
            }

            btnCancelSelection.addEventListener('click', clearSelection);

            function clearSelection() {
                currentSelection = null;
                selectionInfo.style.display = 'none';
                fieldSelectorGroup.style.display = 'none';
                noSelectionHint.style.display = 'block';
                fieldSelector.value = '';
            }

            function getTypeLabel(type) {
                const labels = {
                    db: 'DB',
                    auto_date: 'Tanggal',
                    formula: 'Rumus',
                    auth_field: 'User',
                    relation_single: 'Relasi',
                    loop_manual: 'Loop',
                    loop_relation: 'Loop',
                    manual_text: 'Teks',
                    manual_textarea: 'Textarea',
                    manual_date: 'Tanggal',
                    manual_number: 'Angka',
                    manual_select: 'Dropdown',
                    manual_checkbox: 'Checkbox'
                };
                return labels[type] || type;
            }

            function updateMappingsList() {
                mappingCount.textContent = mappings.length;
                if (mappings.length === 0) {
                    mappingsList.innerHTML = '<p class="text-muted small text-center">Belum ada mapping</p>';
                    return;
                }
                mappingsList.innerHTML = '';
                mappings.forEach((m, idx) => {
                    const div = document.createElement('div');
                    div.className = 'mapping-item d-flex justify-content-between align-items-start border-bottom py-2';
                    const configInfo = m.type !== 'db' ? `<br><small class="text-muted">${getTypeLabel(m.type)}</small>` : '';
                    div.innerHTML = `
                        <div class="text-truncate me-2" style="max-width:75%;" title="${escHtml(m.find)}">
                            <del class="text-muted">${escHtml(m.find.length > 30 ? m.find.substring(0, 30) + '...' : m.find)}</del><br>
                            <code class="text-success">${BOPEN} ${escHtml(m.replace)} ${BCLOSE}</code>
                            <span class="badge bg-light text-dark border ms-1" style="font-size:9px;">${getTypeLabel(m.type)}</span>
                            ${configInfo}
                        </div>
                        <button class="btn btn-sm btn-outline-danger py-0 px-1" onclick="window._removeMapping(${idx})">✕</button>`;
                    mappingsList.appendChild(div);
                });
            }

            window._removeMapping = function (idx) {
                const m = mappings[idx];
                if (!m) return;

                if (m.el && m.el.parentNode) {
                    const parent = m.el.parentNode;
                    while (m.el.firstChild) {
                        const child = m.el.firstChild;
                        if (child.nodeType === 1 && child.classList && child.classList.contains('placeholder-badge')) {
                            m.el.removeChild(child);
                        } else {
                            parent.insertBefore(child, m.el);
                        }
                    }
                    if (m.el.parentNode) m.el.parentNode.removeChild(m.el);
                }
                mappings.splice(idx, 1);
                updateMappingsList();
            };

            function escHtml(str) {
                const d = document.createElement('div');
                d.textContent = str;
                return d.innerHTML;
            }

            // ===== SAVE =====
            formSave.addEventListener('submit', function (e) {
                e.preventDefault();
                if (mappings.length === 0) {
                    if (!confirm('Belum ada mapping. Lanjutkan?')) return;
                }

                const formData = new FormData(this);
                formData.append('template_file', currentFile);
                formData.append('source_table', currentSourceTable);

                const dbMappings = mappings.filter(m => m.type === 'db');
                dbMappings.forEach((m, idx) => {
                    formData.append(`replacements[${idx}][find]`, m.find);
                    formData.append(`replacements[${idx}][replace]`, m.replace);
                });

                const specialFields = mappings.filter(m => m.type !== 'db').map(m => ({
                    placeholder_key: m.replace,
                    placeholder_label: (m.config && m.config.label) || m.replace,
                    field_type: m.type,
                    is_manual: (m.type.indexOf('manual_') === 0 || m.type === 'loop_manual') ? 1 : 0,
                    config: m.config,
                    find_text: m.find
                }));

                formData.append('special_fields', JSON.stringify(specialFields));

                btnSaveText.textContent = 'Menyimpan...';
                btnSaveSpinner.classList.remove('d-none');
                btnSave.disabled = true;

                updateCreateSteps(3);

                fetch(SAVE_URL, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(async res => {
                    const text = await res.text();
                    const ct = res.headers.get('content-type') || '';
                    if (ct.indexOf('application/json') === -1) {
                        console.error('Non-JSON response:', text.substring(0, 500));
                        throw new Error('Server error (status ' + res.status + ')');
                    }
                    const data = JSON.parse(text);
                    if (!res.ok) throw data;
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        closeCreateOverlay();
                        setTimeout(() => window.location.href = REDIRECT_URL, 400);
                    } else {
                        alert(data.message || 'Terjadi kesalahan');
                    }
                })
                .catch(err => {
                    console.error('Save error:', err);
                    const msg = err.errors ? Object.values(err.errors).flat().join('\n') : (err.message || 'Terjadi kesalahan');
                    alert(msg);
                })
                .finally(() => {
                    btnSaveText.textContent = 'Simpan Template';
                    btnSaveSpinner.classList.add('d-none');
                    btnSave.disabled = false;
                });
            });

            // ===== RESET FUNCTION (penting untuk buka ulang) =====
            function reset() {
                currentFile = null;
                currentSourceTable = 'karyawan';
                currentSelection = null;
                mappings = [];
                pendingConfigType = null;
                loopColumnIndex = 0;

                document.getElementById('fileInput').value = '';
                document.getElementById('source_table_select').value = 'karyawan';
                document.getElementById('inputName').value = '';
                document.getElementById('inputCode').value = '';

                emptyState.style.display = 'block';
                docxContainer.innerHTML = `
                    <div class="text-center py-5 text-muted" id="empty-state">
                        <span class="iconify mb-3" data-icon="mdi:file-document-outline" style="font-size:3rem;opacity:.4;"></span>
                        <p class="mt-2 mb-0">Upload file DOCX untuk memulai</p>
                    </div>`;

                mappingSection.style.display = 'none';
                saveSection.style.display = 'none';
                previewHint.style.display = 'none';
                selectionInfo.style.display = 'none';
                fieldSelectorGroup.style.display = 'none';
                noSelectionHint.style.display = 'block';
                fieldSelector.value = '';
                updateMappingsList();
                sourceBadge.textContent = 'Karyawan';
                updateCreateSteps(1);
            }

            // Return public API
            // ===== LOAD FOR EDIT =====
            async function loadForEdit(data) {
                currentSourceTable = data.source_table || 'karyawan';
                mappings = [];
                currentSelection = null;
                editIndex = null; // pastikan variabel editIndex ada (lihat catatan di bawah)
                userPickedNewFile = false;

                // Load existing mappings
                if (data.existingMappings && data.existingMappings.length > 0) {
                    data.existingMappings.forEach(function (ph) {
                        let type = ph.type;
                        if (!ph.is_manual && ph.key) {
                            type = 'db';
                        }
                        mappings.push({
                            find: '',
                            replace: ph.key,
                            fileKey: ph.key,
                            type: type,
                            config: ph.config || { label: ph.label },
                            el: null
                        });
                    });
                }
                updateMappingsList();

                // Load dokumen existing
                if (data.template_url) {
                    try {
                        const response = await fetch(data.template_url);
                        if (!response.ok) throw new Error('File tidak ditemukan');

                        const arrayBuffer = await response.arrayBuffer();
                        const existingFileName = data.template_url.split('/').pop();

                        currentFile = new File([arrayBuffer], existingFileName, {
                            type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                        });

                        emptyState.style.display = 'none';
                        docxContainer.innerHTML =
                            '<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Merender dokumen...</p></div>';

                        await window.docx.renderAsync(arrayBuffer, docxContainer, null, {
                            className: 'docx',
                            inWrapper: true,
                            ignoreWidth: false,
                            ignoreHeight: false,
                            breakPages: true,
                        });

                        // Highlight placeholder yang sudah ada
                        if (typeof highlightExistingPlaceholders === 'function') {
                            highlightExistingPlaceholders();
                        }

                        docxContainer.removeEventListener('mouseup', onMouseUp);
                        docxContainer.addEventListener('mouseup', onMouseUp);

                        mappingSection.style.display = 'block';
                        saveSection.style.display = 'block';
                        previewHint.style.display = 'inline';

                    } catch (err) {
                        console.error(err);
                        docxContainer.innerHTML =
                            `<div class="alert alert-warning m-3">Gagal memuat dokumen: ${err.message}</div>`;
                    }
                }

                populateFieldSelector(currentSourceTable);
            }

            // Return public API
            return {
                reset: reset,
                loadForEdit: loadForEdit
            };
        }
    </script>
@endsection