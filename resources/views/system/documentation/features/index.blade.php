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
            --primary-hover: #4f46e5;
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

        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 0.25rem;
        }

        .page-subtitle {
            color: var(--text-muted);
            font-size: 1rem;
            margin: 0;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            padding: 1.5rem;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary);
        }

        .stat-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-main);
            line-height: 1;
        }

        .stat-icon {
            font-size: 1.5rem;
            opacity: 0.2;
        }

        .control-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            padding: 1rem 1.5rem;
            box-shadow: var(--shadow-sm);
        }

        .search-input {
            border: 1px solid var(--border-color);
            border-radius: 99px;
            padding: 0.6rem 1rem 0.6rem 2.5rem;
            font-size: 0.9rem;
            transition: all 0.2s;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: 1rem center;
        }

        .search-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            outline: none;
        }

        .filter-select {
            border: 1px solid var(--border-color);
            border-radius: 99px;
            padding: 0.6rem 2.5rem 0.6rem 1rem;
            font-size: 0.9rem;
            background-color: var(--bg-card);
            cursor: pointer;
        }

        .filter-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            outline: none;
        }

        .feature-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .feature-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .illustration-container {
            height: 120px;
            background: linear-gradient(135deg, var(--primary-soft) 0%, #ffffff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid var(--border-color);
            position: relative;
        }

        .sub-feature-count-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--primary);
            color: white;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.25rem 0.6rem;
            border-radius: 99px;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .card-body-custom {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .feature-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 0.5rem;
            text-decoration: none;
            display: inline-block;
            transition: color 0.15s;
        }

        .feature-title:hover {
            color: var(--primary);
        }

        .feature-desc {
            font-size: 0.875rem;
            color: var(--text-muted);
            line-height: 1.5;
            flex-grow: 1;
            margin-bottom: 1rem;
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
            background: var(--bg-page);
            color: var(--text-muted);
            padding: 0.3rem 0.75rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            border: 1px solid var(--border-color);
        }

        .action-btn {
            width: 34px;
            height: 34px;
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

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--bg-card);
            border: 2px dashed var(--border-color);
            border-radius: var(--radius);
        }

        .empty-icon {
            width: 80px;
            height: 80px;
            background: var(--primary-soft);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: var(--primary);
            font-size: 2rem;
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

        .modal-body::-webkit-scrollbar {
            width: 6px;
        }

        .modal-body::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .modal-body::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
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

        #how_it_works {
            min-height: 250px;
        }

        .sub-feature-list {
            border-top: 1px solid var(--border-color);
            padding-top: 0.75rem;
            margin-top: 0.75rem;
        }

        .sub-feature-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.4rem 0.6rem;
            border-radius: 8px;
            font-size: 0.8rem;
            color: var(--text-main);
            text-decoration: none;
        }

        .sub-feature-item:hover {
            background: var(--primary-soft);
            color: var(--primary);
        }

        .parent-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            background: var(--primary-soft);
            color: var(--primary);
            padding: 0.2rem 0.6rem;
            border-radius: 99px;
            font-size: 0.65rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
    </style>

    <div class="container-fluid py-4">
        <div class="page-header d-flex justify-content-between align-items-end animate-in">
            <div>
                <h1 class="page-title">Feature Documentation</h1>
                <p class="page-subtitle">Kelola dokumentasi fitur sistem Anda dengan terstruktur dan nyaman.</p>
            </div>
            <button onclick="openModal()" class="btn btn-primary d-flex align-items-center gap-2"
                style="border-radius: 10px; padding: 0.6rem 1.25rem; font-weight: 600; background: var(--primary); border: none;">
                <i class="fas fa-plus"></i> Tambah Fitur Baru
            </button>
        </div>

        <div class="d-flex gap-2 mb-2">
            <a href="{{ route('documentation.import.template') }}"
                class="btn btn-light border d-flex align-items-center gap-2"
                style="border-radius: 10px; padding: 0.6rem 1.25rem; font-weight: 600;">
                <i class="fas fa-download"></i> Template
            </a>
            <button onclick="openImportModal()" class="btn btn-outline-primary d-flex align-items-center gap-2"
                style="border-radius: 10px; padding: 0.6rem 1.25rem; font-weight: 600;">
                <i class="fas fa-file-import"></i> Import Excel
            </button>
            <a href="{{ route('documentation.export.all') }}" class="btn btn-light border d-flex align-items-center gap-2"
                style="border-radius: 10px; padding: 0.6rem 1.25rem; font-weight: 600;">
                <i class="fas fa-file-export"></i> Export Semua
            </a>
        </div>

        <div class="row g-4 mb-4 animate-in" style="animation-delay: 0.1s;">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Total Fitur Utama</div>
                            <div class="stat-value" style="color: var(--primary);">{{ $features->total() }}</div>
                        </div>
                        <i class="fas fa-cubes stat-icon" style="color: var(--primary);"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Production</div>
                            <div class="stat-value" style="color: var(--status-prod-text);">
                                {{ $features->where('status', 'production')->count() }}</div>
                        </div>
                        <i class="fas fa-check-circle stat-icon" style="color: var(--status-prod-text);"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Development</div>
                            <div class="stat-value" style="color: var(--status-dev-text);">
                                {{ $features->where('status', 'development')->count() }}</div>
                        </div>
                        <i class="fas fa-code stat-icon" style="color: var(--status-dev-text);"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Total Sub Fitur</div>
                            <div class="stat-value" style="color: var(--status-draft-text);">
                                {{ $features->sum('children_count') }}</div>
                        </div>
                        <i class="fas fa-sitemap stat-icon" style="color: var(--status-draft-text);"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="control-card mb-4 animate-in" style="animation-delay: 0.2s;">
            <div class="d-flex flex-wrap gap-3 align-items-center">
                <div class="flex-grow-1" style="min-width: 250px;">
                    <input type="text" id="searchInput" class="form-control search-input w-100"
                        placeholder="Cari nama fitur atau kategori...">
                </div>
                <div>
                    <select id="statusFilter" class="form-select filter-select">
                        <option value="">Semua Status</option>
                        <option value="production">Production</option>
                        <option value="development">Development</option>
                        <option value="draft">Draft</option>
                        <option value="deprecated">Deprecated</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row g-4" id="featureGrid">
            @forelse($features as $feature)
                <div class="col-12 col-md-6 col-xl-4 feature-item animate-in" data-name="{{ strtolower($feature->name) }}"
                    data-category="{{ strtolower($feature->category) }}" data-status="{{ $feature->status }}"
                    style="animation-delay: {{ 0.05 * $loop->index }}s;">
                    <div class="feature-card">
                        <div class="illustration-container">
                            @if ($feature->children_count > 0)
                                <span class="sub-feature-count-badge">
                                    <i class="fas fa-sitemap"></i> {{ $feature->children_count }} Sub Fitur
                                </span>
                            @endif
                            <svg width="60" height="60" viewBox="0 0 60 60" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <rect x="10" y="10" width="40" height="40" rx="12" fill="var(--primary)"
                                    fill-opacity="0.1" />
                                <path d="M20 30 L28 38 L40 22" stroke="var(--primary)" stroke-width="4"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>

                        <div class="card-body-custom">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <a href="{{ route('documentation.features.show', $feature->id) }}"
                                        class="feature-title">{{ $feature->name }}</a>
                                    <div class="d-flex gap-2 align-items-center mb-2" style="font-size: 0.7rem;">
                                        <span
                                            style="background: var(--primary-soft); color: var(--primary); padding: 0.15rem 0.5rem; border-radius: 6px; font-weight: 600;">
                                            <i class="fas fa-code-branch me-1"></i>{{ $feature->document_version }}
                                        </span>
                                        @if ($feature->last_updated_at)
                                            <span style="color: var(--text-muted);">
                                                <i class="far fa-clock me-1"></i>{{ $feature->last_updated_at }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <span class="status-badge status-{{ $feature->status }}">
                                    {{ ucfirst($feature->status) }}
                                </span>
                            </div>
                            <p class="feature-desc">{{ Str::limit($feature->short_description, 100) }}</p>

                            @if ($feature->children_count > 0)
                                <div class="sub-feature-list">
                                    @foreach ($feature->children->take(3) as $child)
                                        <a href="{{ route('documentation.features.show', $child->id) }}"
                                            class="sub-feature-item">
                                            <span>
                                                <i class="fas fa-angle-right me-1"></i>{{ $child->name }}
                                                <span
                                                    style="font-size: 0.65rem; color: var(--text-muted); margin-left: 0.3rem;">
                                                    {{ $child->document_version }}
                                                </span>
                                            </span>
                                            <span class="status-badge status-{{ $child->status }}"
                                                style="font-size: 0.6rem;">{{ ucfirst($child->status) }}</span>
                                        </a>
                                    @endforeach
                                    @if ($feature->children_count > 3)
                                        <a href="{{ route('documentation.features.show', $feature->id) }}"
                                            class="text-center d-block"
                                            style="font-size: 0.75rem; color: var(--primary); font-style: italic; margin-top: 0.25rem; text-decoration: none;">
                                            +{{ $feature->children_count - 3 }} sub fitur lainnya
                                        </a>
                                    @endif
                                </div>
                            @endif

                            <div class="d-flex align-items-center justify-content-between mt-auto pt-3"
                                style="border-top: 1px solid var(--border-color);">
                                <span class="category-pill"><i class="fas fa-tag me-1"
                                        style="font-size: 0.65rem;"></i>{{ $feature->category }}</span>

                                <div class="d-flex gap-2">
                                    <a href="{{ route('documentation.features.show', $feature->id) }}" class="action-btn"
                                        title="Lihat Detail">
                                        <i class="fas fa-eye" style="font-size: 0.8rem;"></i>
                                    </a>
                                    <button onclick="addSubFeature({{ $feature->id }})" class="action-btn"
                                        title="Tambah Sub Fitur">
                                        <i class="fas fa-sitemap" style="font-size: 0.8rem;"></i>
                                    </button>
                                    <button onclick="editFeature({{ $feature->id }})" class="action-btn"
                                        title="Edit">
                                        <i class="fas fa-pen" style="font-size: 0.8rem;"></i>
                                    </button>
                                    <a href="{{ route('documentation.codes.index', $feature->id) }}" class="action-btn"
                                        title="Lihat Code">
                                        <i class="fas fa-code" style="font-size: 0.8rem;"></i>
                                    </a>
                                    <a href="{{ route('documentation.features.manual', $feature->id) }}"
                                        class="action-btn" target="_blank" title="Manual PDF">
                                        <i class="fas fa-file-pdf" style="font-size: 0.8rem;"></i>
                                    </a>
                                    <button onclick="deleteFeature({{ $feature->id }})" class="action-btn delete"
                                        title="Hapus">
                                        <i class="fas fa-trash" style="font-size: 0.8rem;"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 animate-in">
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <h4 style="font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">
                            Belum Ada Dokumentasi Fitur
                        </h4>
                        <p
                            style="color: var(--text-muted); margin-bottom: 1.5rem; max-width: 400px; margin-left: auto; margin-right: auto;">
                            Mulai dokumentasikan fitur sistem Anda agar lebih terstruktur dan mudah dipahami oleh tim.
                        </p>
                        <button onclick="openModal()" class="btn btn-primary"
                            style="border-radius: 10px; padding: 0.6rem 1.5rem; font-weight: 600; background: var(--primary); border: none;">
                            <i class="fas fa-plus me-2"></i>Buat Fitur Pertama
                        </button>
                    </div>
                </div>
            @endforelse
        </div>

        @if ($features->hasPages())
            <div class="d-flex justify-content-center mt-5 animate-in">
                {{ $features->links() }}
            </div>
        @endif
    </div>

    <!-- Add/Edit Modal -->
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
                            <div class="form-text" style="font-size: 0.75rem;">Pilih ini jika fitur yang Anda buat adalah
                                bagian/sub-modul dari fitur lain, contoh: "Target Divisi" merupakan sub fitur dari "KPI".
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-7">
                                <label class="form-label-custom">Nama Fitur <span
                                        style="color: var(--primary);">*</span></label>
                                <input type="text" class="form-control form-control-custom" id="name"
                                    name="name" required placeholder="Contoh: Manajemen Target Karyawan">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label-custom">Kategori <span
                                        style="color: var(--primary);">*</span></label>
                                <input type="text" class="form-control form-control-custom" id="category"
                                    name="category" required placeholder="Contoh: HRD">
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
                                        required placeholder="Jelaskan fitur ini dalam 1-2 kalimat..."></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label-custom">Tujuan <span
                                            style="color: var(--primary);">*</span></label>
                                    <textarea class="form-control form-control-custom" id="purpose" name="purpose" rows="9" required
                                        placeholder="Apa yang ingin dicapai dengan fitur ini?"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="form-section-title">
                                <i class="fas fa-cogs"></i> Detail Teknis & Bisnis
                            </div>

                            <div class="mb-2">
                                <label class="form-label-custom">Masalah yang Diselesaikan</label>
                                <textarea class="form-control form-control-custom" id="problem_solved" name="problem_solved" rows="6"
                                    placeholder="Pain point yang diatasi..."></textarea>
                            </div>

                            <div class="mb-2">
                                <label class="form-label-custom">Cara Kerja</label>
                                <textarea class="form-control form-control-custom" id="how_it_works" name="how_it_works" rows="8"
                                    placeholder="Alur kerja fitur secara singkat..."></textarea>
                            </div>

                            <div class="mb-2">
                                <label class="form-label-custom">Hak Akses Pengguna</label>
                                <textarea class="form-control form-control-custom" id="user_access" name="user_access" rows="6"
                                    placeholder="Role siapa saja yang dapat mengakses..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" onclick="closeModal()" class="btn btn-light border"
                            style="border-radius: 10px; font-weight: 500;">Batal</button>
                        <button type="submit" class="btn btn-primary"
                            style="border-radius: 10px; font-weight: 600; background: var(--primary); border: none; padding: 0.6rem 1.5rem;"><i
                                class="fas fa-save me-2"></i>Simpan Fitur
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" style="color: var(--text-main);">
                        <i class="fas fa-file-import me-2" style="color: var(--primary);"></i>Import Dokumentasi dari
                        Excel
                    </h5>
                    <button type="button" class="btn-close" onclick="closeImportModal()"></button>
                </div>
                <form id="importForm">
                    <div class="modal-body">
                        <div class="alert alert-light border d-flex gap-2" style="font-size: 0.85rem;">
                            <i class="fas fa-circle-info mt-1" style="color: var(--primary);"></i>
                            <div>
                                Belum punya file? <a href="{{ route('documentation.import.template') }}"
                                    class="fw-semibold">Download template Excel</a> beserta petunjuk pengisiannya.
                            </div>
                        </div>
                        <label class="form-label-custom">Pilih File Excel (.xlsx)</label>
                        <input type="file" id="importFile" accept=".xlsx,.xls"
                            class="form-control form-control-custom" required>

                        <div id="importErrors" class="mt-3" style="display:none;">
                            <div class="alert alert-danger"
                                style="font-size: 0.8rem; max-height: 200px; overflow-y: auto;">
                                <strong>Ditemukan kesalahan:</strong>
                                <ul id="importErrorList" class="mb-0 mt-2"></ul>
                            </div>
                        </div>
                        <div id="importProgress" class="mt-3 text-center" style="display:none;">
                            <div class="spinner-border" style="color: var(--primary); width: 1.5rem; height: 1.5rem;"
                                role="status"></div>
                            <span class="ms-2" style="font-size: 0.85rem; color: var(--text-muted);">Memproses file,
                                mohon tunggu...</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" onclick="closeImportModal()" class="btn btn-light border"
                            style="border-radius: 10px;">Batal</button>
                        <button type="submit" class="btn btn-primary"
                            style="border-radius: 10px; font-weight: 600; background: var(--primary); border: none;">
                            <i class="fas fa-upload me-2"></i>Import Sekarang
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
        let currentEditId = null;

        function openImportModal() {
            $('#importForm')[0].reset();
            $('#importErrors').hide();
            $('#importErrorList').empty();
            $('#importProgress').hide();
            importModalInstance = new bootstrap.Modal(document.getElementById('importModal'));
            importModalInstance.show();
        }

        function closeImportModal() {
            if (importModalInstance) importModalInstance.hide();
        }

        $('#importForm').on('submit', function(e) {
            e.preventDefault();

            const fileInput = document.getElementById('importFile');
            if (!fileInput.files.length) return;

            const formData = new FormData();
            formData.append('file', fileInput.files[0]);
            formData.append('_token', '{{ csrf_token() }}');

            $('#importErrors').hide();
            $('#importErrorList').empty();
            $('#importProgress').show();

            const submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true);

            $.ajax({
                url: '{{ route('documentation.import') }}',
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.success) {
                        const s = response.summary;
                        alert(
                            `Import berhasil!\n${s.features} fitur, ${s.code_docs} dokumentasi kode, ${s.code_blocks} blok kode, ${s.change_logs} change log ditambahkan.`
                            );
                        location.reload();
                    }
                },
                error: function(xhr) {
                    $('#importProgress').hide();
                    submitBtn.prop('disabled', false);

                    const res = xhr.responseJSON;
                    if (res && res.errors && res.errors.length) {
                        res.errors.forEach(err => $('#importErrorList').append(`<li>${err}</li>`));
                        $('#importErrors').show();
                    } else {
                        alert(res?.message || 'Terjadi kesalahan saat import.');
                    }
                }
            });
        });

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
            currentEditId = null;

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
            // NOTE: uses the dedicated JSON endpoint (edit-data), not the
            // detail page route, since that route now renders full HTML.
            $.get(`/system/documentation/features/${id}/edit-data`, function(response) {
                document.getElementById('modalTitle').innerHTML =
                    `<i class="fas fa-pen me-2" style="color: var(--primary);"></i> Edit Fitur`;
                document.getElementById('featureId').value = response.id;
                currentEditId = response.id;
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

        function deleteFeature(id, force = false) {
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
                success: () => location.reload(),
                statusCode: {
                    409: function(xhr) {
                        const res = xhr.responseJSON;
                        if (confirm(res.message + ' Klik OK untuk menghapus beserta seluruh sub fiturnya.')) {
                            deleteFeature(id, true);
                        }
                    }
                }
            });
        }

        function filterFeatures() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;

            document.querySelectorAll('.feature-item').forEach(item => {
                const name = item.dataset.name;
                const category = item.dataset.category;
                const status = item.dataset.status;

                const matchSearch = name.includes(search) || category.includes(search);
                const matchStatus = !statusFilter || status === statusFilter;

                if (matchSearch && matchStatus) {
                    item.style.display = 'block';
                    setTimeout(() => {
                        item.style.opacity = '1';
                        item.style.transform = 'translateY(0)';
                    }, 50);
                } else {
                    item.style.opacity = '0';
                    item.style.transform = 'translateY(10px)';
                    setTimeout(() => {
                        item.style.display = 'none';
                    }, 300);
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

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('searchInput').addEventListener('keyup', filterFeatures);
            document.getElementById('statusFilter').addEventListener('change', filterFeatures);

            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    trigger: 'hover'
                });
            });
        });
    </script>
@endsection
