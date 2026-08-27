@extends('layouts.app')

@section('content')
    <!-- External Resources -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #6366f1;
            --primary-soft: #eef2ff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --bg-page: #f8fafc;
            --bg-card: #ffffff;
            --border-color: #e2e8f0;
            --radius: 16px;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.05), 0 4px 6px -4px rgb(0 0 0 / 0.05);

            --status-prod-bg: #ecfdf5;
            --status-prod-text: #047857;
            --status-dev-bg: #fffbeb;
            --status-dev-text: #b45309;
            --status-draft-bg: #f1f5f9;
            --status-draft-text: #475569;
            --status-dep-bg: #f8fafc;
            --status-dep-text: #94a3b8;
        }

        body {
            background: var(--bg-page);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-in {
            animation: fadeInUp 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        .breadcrumb-custom {
            background: var(--bg-card);
            padding: 0.75rem 1.25rem;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            font-size: 0.875rem;
        }

        .breadcrumb-custom a {
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.2s;
        }

        .breadcrumb-custom a:hover {
            color: var(--primary);
        }

        .hero-card {
            background: linear-gradient(135deg, var(--primary-soft) 0%, #ffffff 100%);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            padding: 1.75rem;
            box-shadow: var(--shadow-sm);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 99px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .status-production {
            background: var(--status-prod-bg);
            color: var(--status-prod-text);
            border: 1px solid #d1fae5;
        }

        .status-development {
            background: var(--status-dev-bg);
            color: var(--status-dev-text);
            border: 1px solid #fde68a;
        }

        .status-draft {
            background: var(--status-draft-bg);
            color: var(--status-draft-text);
            border: 1px solid #e2e8f0;
        }

        .status-deprecated {
            background: var(--status-dep-bg);
            color: var(--status-dep-text);
            border: 1px solid #e2e8f0;
            text-decoration: line-through;
        }

        .category-pill {
            background: var(--bg-card);
            color: var(--text-muted);
            padding: 0.3rem 0.85rem;
            border-radius: 8px;
            font-size: 0.78rem;
            font-weight: 600;
            border: 1px solid var(--border-color);
        }

        .version-pill {
            background: var(--bg-card);
            color: var(--primary);
            padding: 0.3rem 0.85rem;
            border-radius: 8px;
            font-size: 0.78rem;
            font-weight: 700;
            border: 1px solid #dbeafe;
        }

        .action-btn {
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background: var(--bg-card);
            color: var(--text-muted);
            transition: all 0.2s;
            text-decoration: none;
        }

        .action-btn:hover {
            background: var(--primary-soft);
            color: var(--primary);
            border-color: var(--primary);
        }

        .action-btn.delete:hover {
            background: #f1f5f9;
            color: #475569;
            border-color: #cbd5e1;
        }

        .doc-section {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .doc-section-title {
            background: #f8fafc;
            padding: 1rem 1.5rem;
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--text-main);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .doc-section-body {
            padding: 1.5rem;
            font-size: 0.9rem;
            line-height: 1.7;
            color: var(--text-main);
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .doc-section-body p {
            margin: 0 0 0.75rem;
        }

        .doc-section-body:last-child p:last-child {
            margin-bottom: 0;
        }

        .subfeature-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.1rem;
            height: 100%;
            transition: all 0.25s ease;
            text-decoration: none;
            display: block;
        }

        .subfeature-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary);
        }

        .subfeature-title {
            font-weight: 700;
            color: var(--text-main);
            font-size: 0.95rem;
            margin-bottom: 0.4rem;
        }

        .subfeature-desc {
            color: var(--text-muted);
            font-size: 0.8rem;
            line-height: 1.5;
            margin-bottom: 0.75rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1.5rem;
            background: var(--bg-card);
            border: 2px dashed var(--border-color);
            border-radius: var(--radius);
        }

        .empty-icon {
            width: 64px;
            height: 64px;
            background: var(--primary-soft);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: var(--primary);
            font-size: 1.5rem;
        }

        .modal-content {
            border: none;
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
        }

        .modal-header {
            border-bottom: 1px solid var(--border-color);
            padding: 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid var(--border-color);
            padding: 1.5rem;
        }

        .modal-dialog-scrollable .modal-body {
            overflow-y: auto !important;
            max-height: calc(100vh - 220px) !important;
        }

        .form-label-custom {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 0.4rem;
        }

        .form-control-custom {
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 0.65rem 1rem;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .form-control-custom:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            outline: none;
        }

        .status-selector {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .status-option {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background: var(--bg-card);
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .status-option:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .status-option.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .form-section {
            background: var(--bg-page);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1rem;
        }

        .form-section-title {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* --- Custom Bootstrap Accordion Styling --- */
        .accordion-button:not(.collapsed) {
            background-color: var(--primary-soft) !important;
            color: var(--primary) !important;
            box-shadow: none !important;
        }

        .accordion-button:focus {
            box-shadow: none !important;
            border-color: var(--border-color) !important;
        }

        .accordion-button::after {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%236366f1'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e") !important;
        }
    </style>

    <div class="container-fluid py-4">
        <!-- Breadcrumb -->
        <div class="breadcrumb-custom animate-in d-flex align-items-center gap-2 mb-4 flex-wrap">
            <a href="{{ route('documentation.features.index') }}"><i class="fas fa-home"></i> Features</a>
            @foreach ($ancestors as $ancestor)
                <span style="color: var(--border-color);"><i class="fas fa-chevron-right"
                        style="font-size: 0.7rem;"></i></span>
                <a href="{{ route('documentation.features.show', $ancestor->id) }}">{{ $ancestor->name }}</a>
            @endforeach
            <span style="color: var(--border-color);"><i class="fas fa-chevron-right" style="font-size: 0.7rem;"></i></span>
            <span style="color: var(--text-main); font-weight: 600;">{{ $feature->name }}</span>
        </div>

        <!-- Hero -->
        <div class="hero-card animate-in mb-4" style="animation-delay: 0.1s;">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h1 style="font-size: 1.6rem; font-weight: 700; margin-bottom: 0.5rem;">{{ $feature->name }}</h1>
                    <p style="color: var(--text-muted); max-width: 640px; margin-bottom: 0.85rem;">
                        {{ $feature->short_description }}</p>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="status-badge status-{{ $feature->status }}">{{ ucfirst($feature->status) }}</span>
                        <span class="category-pill"><i class="fas fa-tag me-1"
                                style="font-size: 0.65rem;"></i>{{ $feature->category }}</span>
                        <span class="version-pill"><i class="fas fa-code-branch me-1"
                                style="font-size: 0.65rem;"></i>{{ $feature->document_version }}</span>
                        @if ($feature->parentFeature)
                            <span class="category-pill"><i class="fas fa-sitemap me-1" style="font-size: 0.65rem;"></i>Sub
                                fitur dari {{ $feature->parentFeature->name }}</span>
                        @endif
                        @if ($feature->last_updated_at)
                            <span class="category-pill"><i class="far fa-clock me-1"
                                    style="font-size: 0.65rem;"></i>{{ $feature->last_updated_at }}</span>
                        @endif
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('documentation.features.index') }}" class="action-btn" title="Kembali"><i
                            class="fas fa-arrow-left"></i></a>
                    <button onclick="addSubFeature({{ $feature->id }})" class="action-btn" title="Tambah Sub Fitur"><i
                            class="fas fa-sitemap"></i></button>
                    <button onclick="editFeature({{ $feature->id }})" class="action-btn" title="Edit"><i
                            class="fas fa-pen"></i></button>
                    <a href="{{ route('documentation.codes.index', $feature->id) }}" class="action-btn"
                        title="Lihat Dokumentasi Kode"><i class="fas fa-code"></i></a>
                    <a href="{{ route('documentation.features.manual', $feature->id) }}" class="action-btn" target="_blank"
                        title="Manual PDF"><i class="fas fa-file-pdf"></i></a>
                    <button onclick="deleteFeature({{ $feature->id }}, {{ $feature->parent_id ?? 'null' }})"
                        class="action-btn delete" title="Hapus"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <!-- Documentation sections -->
                @if ($feature->purpose)
                    <div class="doc-section animate-in">
                        <div class="doc-section-title"><i class="fas fa-bullseye" style="color: var(--primary);"></i> Tujuan
                            Fitur</div>
                        <div class="doc-section-body">{!! $feature->purpose !!}</div>
                    </div>
                @endif

                @if ($feature->problem_solved)
                    <div class="doc-section animate-in">
                        <div class="doc-section-title"><i class="fas fa-triangle-exclamation"
                                style="color: var(--primary);"></i> Masalah yang Diselesaikan</div>
                        <div class="doc-section-body">{!! $feature->problem_solved !!}</div>
                    </div>
                @endif

                @if ($feature->how_it_works)
                    <div class="doc-section animate-in">
                        <div class="doc-section-title"><i class="fas fa-diagram-project" style="color: var(--primary);"></i>
                            Cara Kerja</div>
                        <div class="doc-section-body">{!! $feature->how_it_works !!}</div>
                    </div>
                @endif

                @if ($feature->user_access)
                    <div class="doc-section animate-in">
                        <div class="doc-section-title"><i class="fas fa-user-shield" style="color: var(--primary);"></i> Hak
                            Akses Pengguna</div>
                        <div class="doc-section-body">{!! $feature->user_access !!}</div>
                    </div>
                @endif

                <!-- Sub Fitur -->
                <div class="doc-section animate-in">
                    <div class="doc-section-title">
                        <i class="fas fa-sitemap" style="color: var(--primary);"></i> Sub Fitur
                        <span class="badge rounded-pill"
                            style="background: var(--primary-soft); color: var(--primary); font-size: 0.7rem;">{{ $feature->children_count }}</span>
                    </div>
                    <div class="doc-section-body">
                        @if ($feature->children->count() > 0)
                            <div class="row g-3">
                                @foreach ($feature->children as $child)
                                    <div class="col-md-6">
                                        <a href="{{ route('documentation.features.show', $child->id) }}"
                                            class="subfeature-card">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <div class="subfeature-title">{{ $child->name }}</div>
                                                <span class="status-badge status-{{ $child->status }}"
                                                    style="font-size: 0.6rem;">{{ ucfirst($child->status) }}</span>
                                            </div>
                                            <div class="subfeature-desc">{{ Str::limit($child->short_description, 90) }}
                                            </div>
                                            <div class="d-flex gap-2 align-items-center"
                                                style="font-size: 0.72rem; color: var(--text-muted);">
                                                <span><i
                                                        class="fas fa-code-branch me-1"></i>{{ $child->document_version }}</span>
                                                @if ($child->children_count > 0)
                                                    <span><i class="fas fa-sitemap me-1"></i>{{ $child->children_count }}
                                                        sub fitur</span>
                                                @endif
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fas fa-folder-open"></i></div>
                                <p style="color: var(--text-muted); margin-bottom: 1rem;">Fitur ini belum memiliki sub
                                    fitur.</p>
                                <button onclick="addSubFeature({{ $feature->id }})" class="btn btn-primary"
                                    style="border-radius: 10px; background: var(--primary); border: none; font-weight: 600; font-size: 0.85rem;">
                                    <i class="fas fa-plus me-1"></i> Tambah Sub Fitur
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Code documentation summary -->
                <div class="doc-section animate-in">
                    <div class="doc-section-title"><i class="fas fa-file-code" style="color: var(--primary);"></i>
                        Dokumentasi Kode</div>
                    <div class="doc-section-body">
                        <p style="color: var(--text-muted); margin-bottom: 1rem;">
                            {{ $feature->codeDocumentations->count() }} entri dokumentasi teknis/kode tersimpan untuk fitur
                            ini.
                        </p>
                        <a href="{{ route('documentation.codes.index', $feature->id) }}"
                            class="btn btn-outline-primary w-100"
                            style="border-radius: 10px; font-size: 0.85rem; font-weight: 600;">
                            <i class="fas fa-code me-1"></i> Buka Dokumentasi Kode
                        </a>
                    </div>
                </div>

                <!-- Revision history (FULL BOOTSTRAP ACCORDION) -->
                <div class="doc-section animate-in">
                    <div class="doc-section-title">
                        <i class="fas fa-history" style="color: var(--primary);"></i> Riwayat Revisi
                    </div>
                    <div class="doc-section-body" style="padding: 0;">
                        @if (count($history) > 0)
                            @foreach ($history as $index => $entry)
                                <div class="accordion-item" style="border-color: var(--border-color);">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button {{ $index !== 0 ? 'collapsed' : '' }}"
                                            style="font-size: 0.9rem; padding: 0.85rem 1.25rem; background-color: transparent; color: var(--text-main);">
                                            <div class="d-flex justify-content-between align-items-center w-100 pe-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="fw-bold">v{{ $entry['version'] }}</span>
                                                    @if ($index === 0)
                                                        <span class="badge"
                                                            style="background: var(--primary); color: white; font-size: 0.65rem; font-weight: 600; padding: 0.25rem 0.5rem; border-radius: 4px;">Terbaru,
                                                            Diperbarui oleh <strong
                                                                style="color: var(--text-main);">{{ $entry['updated_by_name'] }}</strong></span>
                                                    @endif
                                                </div>

                                                <small class="text-muted"
                                                    style="font-size: 0.75rem; white-space: nowrap;">
                                                    <i class="far fa-clock me-1"></i>
                                                    @if ($entry['updated_at'])
                                                        {{ \Carbon\Carbon::parse($entry['updated_at'])->translatedFormat('d M Y H:i') }}
                                                    @endif
                                                </small>
                                            </div>
                                        </button>
                                    </h2>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-history mb-2" style="font-size: 1.5rem; color: var(--border-color);"></i>
                                <p class="mb-0" style="color: var(--text-muted); font-size: 0.85rem;">Belum ada riwayat
                                    revisi.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal (same structure as features index, reused here) -->
    <div class="modal fade" id="featureModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalTitle" style="color: var(--text-main);">
                        <i class="fas fa-plus-circle me-2" style="color: var(--primary);"></i> Tambah Fitur Baru
                    </h5>
                    <button type="button" class="btn-close" onclick="closeModal()"></button>
                </div>
                <form id="featureForm">
                    @csrf
                    <input type="hidden" id="featureId" name="id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label-custom">Fitur Induk (opsional)</label>
                            <select class="form-select form-control-custom" id="parent_id" name="parent_id">
                                <option value="">-- Tidak ada, ini fitur utama --</option>
                            </select>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-7">
                                <label class="form-label-custom">Nama Fitur <span
                                        style="color: var(--primary);">*</span></label>
                                <input type="text" class="form-control form-control-custom" id="name"
                                    name="name" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label-custom">Kategori <span
                                        style="color: var(--primary);">*</span></label>
                                <input type="text" class="form-control form-control-custom" id="category"
                                    name="category" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label-custom">Status <span style="color: var(--primary);">*</span></label>
                            <div class="status-selector">
                                <div onclick="selectStatus(this, 'draft')" class="status-option active"
                                    data-value="draft">Draft</div>
                                <div onclick="selectStatus(this, 'development')" class="status-option"
                                    data-value="development">Development</div>
                                <div onclick="selectStatus(this, 'production')" class="status-option"
                                    data-value="production">Production</div>
                                <div onclick="selectStatus(this, 'deprecated')" class="status-option"
                                    data-value="deprecated">Deprecated</div>
                            </div>
                            <input type="hidden" id="status" name="status" value="draft">
                        </div>

                        <div class="form-section">
                            <div class="form-section-title"><i class="fas fa-info-circle"></i> Informasi Dasar</div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label-custom">Deskripsi Singkat <span
                                            style="color: var(--primary);">*</span></label>
                                    <textarea class="form-control form-control-custom" id="short_description" name="short_description" rows="6"
                                        required></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label-custom">Tujuan <span
                                            style="color: var(--primary);">*</span></label>
                                    <textarea class="form-control form-control-custom" id="purpose" name="purpose" rows="9" required></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="form-section-title"><i class="fas fa-cogs"></i> Detail Teknis & Bisnis</div>
                            <div class="mb-2">
                                <label class="form-label-custom">Masalah yang Diselesaikan</label>
                                <textarea class="form-control form-control-custom" id="problem_solved" name="problem_solved" rows="6"></textarea>
                            </div>
                            <div class="mb-2">
                                <label class="form-label-custom">Cara Kerja</label>
                                <textarea class="form-control form-control-custom" id="how_it_works" name="how_it_works" rows="8"></textarea>
                            </div>
                            <div class="mb-2">
                                <label class="form-label-custom">Hak Akses Pengguna</label>
                                <textarea class="form-control form-control-custom" id="user_access" name="user_access" rows="6"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" onclick="closeModal()" class="btn btn-light border"
                            style="border-radius: 10px; font-weight: 500;">Batal</button>
                        <button type="submit" class="btn btn-primary"
                            style="border-radius: 10px; font-weight: 600; background: var(--primary); border: none; padding: 0.6rem 1.5rem;">
                            <i class="fas fa-save me-2"></i>Simpan Fitur
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let featureModalInstance = null;
        const currentFeatureId = {{ $feature->id }};
        const currentFeatureParentId = {{ $feature->parent_id ?? 'null' }};

        function selectStatus(el, value) {
            document.querySelectorAll('.status-option').forEach(opt => opt.classList.remove('active'));
            el.classList.add('active');
            document.getElementById('status').value = value;
        }

        function loadParentOptions(excludeId = null, selectedId = null) {
            let url = '{{ route('documentation.features.options') }}';
            if (excludeId) url += `?exclude=${excludeId}`;

            $.get(url, function(response) {
                const select = document.getElementById('parent_id');
                select.innerHTML = '<option value="">-- Tidak ada, ini fitur utama --</option>';
                response.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = item.parent_id ? `↳ ${item.name}` : item.name;
                    if (selectedId && item.id == selectedId) opt.selected = true;
                    select.appendChild(opt);
                });
            });
        }

        function openModal() {
            document.getElementById('modalTitle').innerHTML =
                `<i class="fas fa-plus-circle me-2" style="color: var(--primary);"></i> Tambah Fitur Baru`;
            document.getElementById('featureForm').reset();
            document.getElementById('featureId').value = '';

            document.querySelectorAll('.status-option').forEach(opt => opt.classList.remove('active'));
            document.querySelector('.status-option[data-value="draft"]').classList.add('active');
            document.getElementById('status').value = 'draft';

            loadParentOptions();

            featureModalInstance = new bootstrap.Modal(document.getElementById('featureModal'));
            featureModalInstance.show();
        }

        function addSubFeature(parentId) {
            openModal();
            document.getElementById('modalTitle').innerHTML =
                `<i class="fas fa-sitemap me-2" style="color: var(--primary);"></i> Tambah Sub Fitur`;
            loadParentOptions(null, parentId);
        }

        function closeModal() {
            if (featureModalInstance) featureModalInstance.hide();
        }

        function editFeature(id) {
            $.get(`/system/documentation/features/${id}/edit-data`, function(response) {
                document.getElementById('modalTitle').innerHTML =
                    `<i class="fas fa-pen me-2" style="color: var(--primary);"></i> Edit Fitur`;
                document.getElementById('featureId').value = response.id;
                document.getElementById('name').value = response.name;
                document.getElementById('category').value = response.category;
                document.getElementById('short_description').value = response.short_description || '';
                document.getElementById('purpose').value = response.purpose || '';
                document.getElementById('problem_solved').value = response.problem_solved || '';
                document.getElementById('how_it_works').value = response.how_it_works || '';
                document.getElementById('user_access').value = response.user_access || '';

                selectStatus(document.querySelector(`.status-option[data-value="${response.status}"]`), response
                    .status);
                loadParentOptions(response.id, response.parent_id);

                featureModalInstance = new bootstrap.Modal(document.getElementById('featureModal'));
                featureModalInstance.show();
            });
        }

        function deleteFeature(id, parentId, force = false) {
            if (!force && !confirm(
                    'Apakah Anda yakin ingin menghapus dokumentasi fitur ini? Tindakan ini tidak dapat dibatalkan.')) {
                return;
            }

            $.ajax({
                url: `/system/documentation/features/${id}${force ? '?force=1' : ''}`,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: () => {
                    window.location.href = parentId ? `/system/documentation/features/${parentId}` :
                        '{{ route('documentation.features.index') }}';
                },
                statusCode: {
                    409: function(xhr) {
                        const res = xhr.responseJSON;
                        if (confirm(res.message + ' Klik OK untuk menghapus beserta seluruh sub fiturnya.')) {
                            deleteFeature(id, parentId, true);
                        }
                    }
                }
            });
        }

        $('#featureForm').on('submit', function(e) {
            e.preventDefault();
            const id = $('#featureId').val();
            const url = id ? `/system/documentation/features/${id}?_method=PUT` : '/system/documentation/features';

            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...').prop('disabled', true);

            $.ajax({
                url: url,
                method: 'POST',
                data: $(this).serialize(),
                success: () => location.reload(),
                error: (xhr) => {
                    const msg = xhr.responseJSON?.message || 'Terjadi kesalahan. Periksa input Anda.';
                    alert(msg);
                    submitBtn.html(originalText).prop('disabled', false);
                }
            });
        });
    </script>
@endsection
