@extends('layouts.app')

@section('content')
    <!-- External Resources -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #2563eb;
            --primary-soft: #eff6ff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --bg-page: #f8fafc;
            --bg-card: #ffffff;
            --border-color: #e2e8f0;
            --radius: 12px;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.05), 0 4px 6px -4px rgb(0 0 0 / 0.05);
        }

        body {
            background-color: var(--bg-page);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
        }

        /* Thin, subtle scrollbar */
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

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        /* Components */
        .breadcrumb-custom {
            background: var(--bg-card);
            padding: 0.75rem 1.25rem;
            border-radius: var(--radius);
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

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .header-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 0.25rem;
        }

        .header-subtitle {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin: 0;
        }

        .feature-info-card {
            background: linear-gradient(135deg, var(--primary-soft) 0%, #ffffff 100%);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
        }

        .feature-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted);
        }

        /* Doc Cards */
        .doc-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .doc-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .doc-card-header {
            padding: 1.25rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            background: #fafafa;
        }

        .doc-card-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-main);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            line-height: 1.4;
        }

        .doc-card-actions {
            display: flex;
            gap: 0.25rem;
            opacity: 0.6;
            transition: opacity 0.2s;
        }

        .doc-card:hover .doc-card-actions {
            opacity: 1;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background: var(--bg-card);
            color: var(--text-muted);
            transition: all 0.2s;
            padding: 0;
        }

        .btn-icon:hover {
            background: var(--primary-soft);
            color: var(--primary);
            border-color: var(--primary);
        }

        /* Neutral delete button (no red) */
        .btn-icon.delete:hover {
            background: #f1f5f9;
            color: #475569;
            border-color: #cbd5e1;
        }

        .doc-card-body {
            padding: 1.25rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .section-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            justify-content: space-between;
        }

        .text-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            font-size: 0.875rem;
            color: var(--text-muted);
            line-height: 1.5;
        }

        /* Code & Flow Blocks */
        .flow-block {
            background: #f8fafc;
            border-left: 3px solid var(--primary);
            padding: 1rem;
            border-radius: 0 8px 8px 0;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            color: var(--text-main);
            max-height: 120px;
            overflow-y: auto;
            white-space: pre-wrap;
            position: relative;
        }

        .code-preview {
            background: #1e293b;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 0.75rem;
        }

        .code-preview-header {
            background: #0f172a;
            padding: 0.5rem 0.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #334155;
        }

        .code-lang {
            color: #38bdf8;
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .code-preview-body {
            padding: 0.75rem;
            max-height: 100px;
            overflow-x: auto;
        }

        .code-preview-body pre {
            margin: 0;
        }

        .code-preview-body code {
            font-size: 0.7rem;
            font-family: 'JetBrains Mono', monospace;
        }

        /* Relation Pills */
        .relation-pill {
            background: var(--primary-soft);
            color: var(--primary);
            padding: 0.25rem 0.65rem;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            border: 1px solid #dbeafe;
        }

        /* Empty State */
        .empty-state {
            background: var(--bg-card);
            border: 2px dashed var(--border-color);
            border-radius: var(--radius);
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-muted);
        }

        .empty-state i {
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        /* Modal & Form Customization */
        .modal-content {
            border: none;
            border-radius: 16px;
            box-shadow: var(--shadow-lg);
        }

        .modal-header {
            border-bottom: 1px solid var(--border-color);
            padding: 1.25rem 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .form-label-custom {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 0.4rem;
        }

        .form-control-custom,
        .form-select-custom {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 0.6rem 0.8rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .form-control-custom:focus,
        .form-select-custom:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
        }

        .dynamic-section {
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 1.25rem;
            margin-bottom: 1rem;
        }

        .dynamic-section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .dynamic-section-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-main);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-add {
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 0.35rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            transition: all 0.2s;
        }

        .btn-add:hover {
            background: #1d4ed8;
            color: white;
        }

        .btn-remove {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .btn-remove:hover {
            background: #f1f5f9;
            color: #475569;
        }

        /* Mermaid export button */
        .btn-export-png {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            color: var(--primary);
            border-radius: 6px;
            padding: 0.2rem 0.6rem;
            font-size: 0.65rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            transition: all 0.2s;
        }

        .btn-export-png:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        /* Change history timeline */
        .history-timeline {
            position: relative;
            padding-left: 1.5rem;
            border-left: 2px solid var(--border-color);
        }

        .history-item {
            position: relative;
            margin-bottom: 1.25rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid var(--border-color);
        }

        .history-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .history-dot {
            position: absolute;
            left: -1.95rem;
            top: 0.2rem;
            width: 11px;
            height: 11px;
            border-radius: 50%;
            background: var(--primary);
            border: 3px solid white;
            box-shadow: var(--shadow-sm);
        }

        .history-action-badge {
            display: inline-block;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 0.15rem 0.55rem;
            border-radius: 20px;
            margin-right: 0.4rem;
        }

        .history-action-created {
            background: #ecfdf5;
            color: #047857;
        }

        .history-action-updated {
            background: #fffbeb;
            color: #b45309;
        }

        .history-field-chip {
            display: inline-block;
            font-size: 0.68rem;
            background: #f1f5f9;
            color: var(--text-muted);
            padding: 0.1rem 0.5rem;
            border-radius: 6px;
            margin: 0.15rem 0.25rem 0 0;
        }

        /* Copy Feedback Toast */
        .copy-feedback {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: #0f172a;
            color: white;
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: var(--shadow-lg);
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 9999;
            pointer-events: none;
        }

        .copy-feedback.show {
            opacity: 1;
            transform: translateY(0);
        }

        .modal-dialog-scrollable .modal-content {
            max-height: calc(100vh - 4rem) !important;
            display: flex !important;
            flex-direction: column !important;
        }

        .modal-dialog-scrollable .modal-body {
            overflow-y: auto !important;
            flex: 1 1 auto !important;
            max-height: calc(100vh - 160px) !important;
        }

        .modal-dialog-scrollable .modal-body::-webkit-scrollbar {
            width: 6px;
        }

        .modal-dialog-scrollable .modal-body::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .modal-dialog-scrollable .modal-body::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        #codeDocModal .modal-dialog,
        #detailCodeDocModal .modal-dialog {
            max-height: 90vh !important;
            margin: 1.75rem auto;
        }

        #codeDocModal .modal-content,
        #detailCodeDocModal .modal-content {
            max-height: 100% !important;
            display: flex !important;
            flex-direction: column !important;
        }

        #codeDocModal form {
            display: flex !important;
            flex-direction: column !important;
            flex: 1 1 auto !important;
            min-height: 0 !important;
        }

        #codeDocModal .modal-header,
        #codeDocModal .modal-footer,
        #detailCodeDocModal .modal-header,
        #detailCodeDocModal .modal-footer {
            flex: 0 0 auto !important;
            background-color: #ffffff;
            z-index: 10;
        }

        #codeDocModal .modal-body,
        #detailCodeDocModal .modal-body {
            flex: 1 1 auto !important;
            overflow-y: auto !important;
            min-height: 0 !important;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }

        #codeDocModal .modal-body::-webkit-scrollbar,
        #detailCodeDocModal .modal-body::-webkit-scrollbar {
            width: 6px;
        }

        #codeDocModal .modal-body::-webkit-scrollbar-track,
        #detailCodeDocModal .modal-body::-webkit-scrollbar-track {
            background: transparent;
        }

        #codeDocModal .modal-body::-webkit-scrollbar-thumb,
        #detailCodeDocModal .modal-body::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        #codeDocModal .modal-body::-webkit-scrollbar-thumb:hover,
        #detailCodeDocModal .modal-body::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>

    <div class="container-fluid py-4">
        <!-- Breadcrumb -->
        <div class="breadcrumb-custom animate-fade-in d-flex align-items-center gap-2 mb-4 flex-wrap">
            <a href="{{ route('documentation.features.index') }}"><i class="fas fa-home"></i> Features</a>
            @foreach ($feature->load('parentFeature.parentFeature')->parentFeature
            ? array_reverse(
                (function () use ($feature) {
                    $chain = [];
                    $current = $feature->parentFeature;
                    while ($current) {
                        $chain[] = $current;
                        $current = $current->parentFeature;
                    }
                    return $chain;
                })
    (),
            )
            : [] as $ancestor)
                <span style="color: var(--border-color);"><i class="fas fa-chevron-right"
                        style="font-size: 0.7rem;"></i></span>
                <a href="{{ route('documentation.features.show', $ancestor->id) }}">{{ $ancestor->name }}</a>
            @endforeach
            <span style="color: var(--border-color);"><i class="fas fa-chevron-right" style="font-size: 0.7rem;"></i></span>
            <a href="{{ route('documentation.features.show', $feature->id) }}">{{ $feature->name }}</a>
            <span style="color: var(--border-color);"><i class="fas fa-chevron-right" style="font-size: 0.7rem;"></i></span>
            <span style="color: var(--text-main); font-weight: 600;">Dokumentasi Kode</span>
        </div>

        <!-- Page Header -->
        <div class="page-header animate-fade-in" style="animation-delay: 0.1s;">
            <div class="d-flex gap-2">
                <a href="{{ route('documentation.features.show', $feature->id) }}" class="btn btn-light border"
                    style="border-radius: 8px; font-size: 0.875rem; font-weight: 500;">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Fitur
                </a>
                <button class="btn btn-primary" style="border-radius: 8px; font-size: 0.875rem; font-weight: 500;"
                    onclick="openModal()">
                    <i class="fas fa-plus me-1"></i> Tambah Dokumentasi
                </button>
            </div>
        </div>

        <!-- Feature Info -->
        <div class="feature-info-card animate-fade-in" style="animation-delay: 0.2s;">
            <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-main);">
                {{ $feature->name }}</h2>
            <p style="color: var(--text-muted); margin: 0 0 1rem 0; font-size: 0.95rem;">{{ $feature->short_description }}
            </p>
            <div class="d-flex gap-2 flex-wrap">
                <div class="feature-badge"><i class="fas fa-folder" style="font-size: 0.7rem;"></i> {{ $feature->category }}
                </div>
                <div class="feature-badge"><i class="fas fa-circle" style="font-size: 0.4rem;"></i>
                    {{ ucfirst($feature->status) }}</div>
                <div class="feature-badge"><i class="fas fa-file-code" style="font-size: 0.7rem;"></i>
                    {{ $codeDocs->total() }} Dokumentasi</div>
                <div class="feature-badge"><i class="fas fa-code-branch" style="font-size: 0.7rem;"></i>
                    {{ $feature->document_version }}</div>
            </div>
        </div>

        <!-- Documentation Grid -->
        <div class="row g-4">
            @forelse($codeDocs as $codeDoc)
                <div class="col-md-6 col-lg-4 animate-fade-in" style="animation-delay: {{ 0.1 * $loop->index }}s;">
                    <div class="doc-card" onclick="viewDetailCodeDoc({{ $codeDoc->id }})">
                        <div class="doc-card-header">
                            <h3 class="doc-card-title">
                                <i class="fas fa-file-code" style="color: var(--primary); flex-shrink: 0;"></i>
                                <span>{{ $codeDoc->title }}</span>
                            </h3>
                            <div class="doc-card-actions" onclick="event.stopPropagation();">
                                <button class="btn-icon" onclick="editCodeDoc({{ $codeDoc->id }})" title="Edit">
                                    <i class="fas fa-pen" style="font-size: 0.75rem;"></i>
                                </button>
                                <button class="btn-icon delete" onclick="deleteCodeDoc({{ $codeDoc->id }})"
                                    title="Hapus">
                                    <i class="fas fa-trash" style="font-size: 0.75rem;"></i>
                                </button>
                            </div>
                        </div>

                        <div class="doc-card-body">
                            @if ($codeDoc->description)
                                <div>
                                    <div class="section-label"><span><i class="fas fa-info-circle"></i> Deskripsi</span>
                                    </div>
                                    <p class="text-clamp-3" style="margin: 0;">{{ Str::limit($codeDoc->description, 120) }}
                                    </p>
                                </div>
                            @endif

                            @if ($codeDoc->flow_program)
                                <div>
                                    <div class="section-label">
                                        <span><i class="fas fa-project-diagram"></i> Flow</span>
                                        @if ($codeDoc->flow_program['type'] == 'mermaid')
                                            <button class="btn-export-png"
                                                onclick="event.stopPropagation(); exportMermaidPngFromContainer(this, '{{ Str::slug($codeDoc->title) }}-flow')"
                                                title="Export sebagai PNG">
                                                <i class="fas fa-image"></i> PNG
                                            </button>
                                        @endif
                                    </div>
                                    <div class="flow-block">
                                        @if ($codeDoc->flow_program['type'] == 'mermaid')
                                            <div class="mermaid"
                                                data-mermaid-code="{{ $codeDoc->flow_program['content'] }}"></div>
                                        @else
                                            {{ Str::limit($codeDoc->flow_program['content'], 150) }}
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if ($codeDoc->code_blocks && count($codeDoc->code_blocks) > 0)
                                <div>
                                    <div class="section-label"><span><i class="fas fa-laptop-code"></i> Kode
                                            ({{ count($codeDoc->code_blocks) }})</span></div>
                                    @foreach (array_slice($codeDoc->code_blocks, 0, 2) as $block)
                                        <div class="code-preview">
                                            <div class="code-preview-header">
                                                <span class="code-lang">{{ $block['language'] ?? 'php' }}</span>
                                                <button class="btn-icon"
                                                    style="width: 26px; height: 26px; border-color: #334155; color: #94a3b8;"
                                                    data-code="{{ addslashes($block['code']) }}"
                                                    onclick="event.stopPropagation(); copyCode(this)" title="Copy">
                                                    <i class="fas fa-copy" style="font-size: 0.7rem;"></i>
                                                </button>
                                            </div>
                                            <div class="code-preview-body">
                                                <pre><code class="language-{{ $block['language'] ?? 'php' }}">{{ Str::limit($block['code'], 120) }}</code></pre>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if (count($codeDoc->code_blocks) > 2)
                                        <div
                                            style="text-align: center; font-size: 0.75rem; color: var(--text-muted); font-style: italic; margin-top: 0.5rem;">
                                            +{{ count($codeDoc->code_blocks) - 2 }} kode lainnya
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if ($codeDoc->relations && count($codeDoc->relations) > 0)
                                <div>
                                    <div class="section-label"><span><i class="fas fa-link"></i> Relasi</span></div>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach (array_slice($codeDoc->relations, 0, 3) as $relation)
                                            <span class="relation-pill">
                                                <i class="fas fa-database" style="font-size: 0.6rem;"></i>
                                                {{ $relation }}
                                            </span>
                                        @endforeach
                                        @if (count($codeDoc->relations) > 3)
                                            <span class="relation-pill"
                                                style="background: #f1f5f9; color: var(--text-muted); border-color: var(--border-color);">
                                                +{{ count($codeDoc->relations) - 3 }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div
                                style="margin-top: auto; padding-top: 0.5rem; border-top: 1px dashed var(--border-color); font-size: 0.7rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.3rem;">
                                <i class="fas fa-history"></i>
                                {{ count($codeDoc->log_update ?? []) }} revisi tercatat
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="empty-state animate-fade-in">
                        <i class="fas fa-folder-open fa-3x"></i>
                        <h4 style="color: var(--text-main); font-weight: 600; margin-bottom: 0.5rem;">Belum Ada Dokumentasi
                            Kode</h4>
                        <p style="margin-bottom: 1.5rem;">Mulai mendokumentasikan implementasi teknis fitur ini agar lebih
                            terstruktur.</p>
                        <button class="btn btn-primary" style="border-radius: 8px;" onclick="openModal()">
                            <i class="fas fa-plus me-1"></i> Tambah Dokumentasi Kode
                        </button>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($codeDocs->hasPages())
            <div class="d-flex justify-content-center mt-5">
                {{ $codeDocs->links() }}
            </div>
        @endif
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="detailCodeDocModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" style="font-weight: 700; font-size: 1.1rem;">
                        <i class="fas fa-file-code" style="color: var(--primary);"></i> Detail Dokumentasi
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailModalBody">
                    <div class="text-center py-5">
                        <div class="spinner-border" style="color: var(--primary); width: 2rem; height: 2rem;"
                            role="status"></div>
                        <p class="mt-3" style="color: var(--text-muted); font-size: 0.9rem;">Memuat detail
                            dokumentasi...</p>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid var(--border-color);">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal"
                        style="border-radius: 8px; font-size: 0.875rem;">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal fade" id="codeDocModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="codeDocModalTitle" style="font-weight: 700; font-size: 1.1rem;">
                        <i class="fas fa-plus-circle" style="color: var(--primary);"></i> Tambah Code Documentation
                    </h5>
                    <button type="button" class="btn-close" onclick="closeModal()"></button>
                </div>
                <form id="codeDocForm">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="codeDocId" name="id">

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label-custom">Judul Dokumentasi <span
                                        style="color: var(--primary);">*</span></label>
                                <input type="text" class="form-control form-control-custom" id="title"
                                    name="title" required placeholder="Contoh: Implementasi Controller Target">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label-custom">Tipe Flow</label>
                                <select class="form-select form-select-custom" id="flow_type">
                                    <option value="text">Text / Pseudocode</option>
                                    <option value="mermaid">Mermaid Diagram</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label-custom">Deskripsi</label>
                            <textarea class="form-control form-control-custom" id="description" name="description" rows="2"
                                placeholder="Jelaskan tujuan dari dokumentasi ini..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label-custom">Konten Flow</label>
                            <textarea class="form-control form-control-custom" id="flow_content" rows="4"
                                style="font-family: 'JetBrains Mono', monospace; font-size: 0.8rem;"
                                placeholder="Tuliskan flow program atau kode mermaid di sini..."></textarea>
                        </div>

                        <div class="dynamic-section">
                            <div class="dynamic-section-header">
                                <h6 class="dynamic-section-title"><i class="fas fa-laptop-code"></i> Code Blocks</h6>
                                <button type="button" class="btn-add" onclick="addCodeBlock()"><i
                                        class="fas fa-plus"></i> Tambah Kode</button>
                            </div>
                            <div id="codeBlocksContainer"></div>
                        </div>

                        <div class="dynamic-section">
                            <div class="dynamic-section-header">
                                <h6 class="dynamic-section-title"><i class="fas fa-link"></i> Relasi Model</h6>
                                <button type="button" class="btn-add" onclick="addRelation()"><i
                                        class="fas fa-plus"></i> Tambah</button>
                            </div>
                            <div id="relationsContainer" class="d-flex flex-wrap gap-2"></div>
                        </div>

                        <div class="dynamic-section">
                            <div class="dynamic-section-header">
                                <h6 class="dynamic-section-title"><i class="fas fa-history"></i> Change Log</h6>
                                <button type="button" class="btn-add" onclick="addChangeLog()"><i
                                        class="fas fa-plus"></i> Tambah</button>
                            </div>
                            <div id="changeLogsContainer"></div>
                        </div>

                        <div class="dynamic-section">
                            <div class="dynamic-section-header">
                                <h6 class="dynamic-section-title"><i class="fas fa-lightbulb"></i> Future Development</h6>
                                <button type="button" class="btn-add" onclick="addFutureDev()"><i
                                        class="fas fa-plus"></i> Tambah</button>
                            </div>
                            <div id="futureDevContainer"></div>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid var(--border-color);">
                        <button type="button" class="btn btn-light border" onclick="closeModal()"
                            style="border-radius: 8px; font-size: 0.875rem;">Batal</button>
                        <button type="submit" class="btn btn-primary"
                            style="border-radius: 8px; font-size: 0.875rem; font-weight: 500;">
                            <i class="fas fa-save me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Copy Feedback Toast -->
    <div class="copy-feedback" id="copyFeedback">
        <i class="fas fa-check-circle" style="color: #4ade80;"></i> Kode berhasil disalin!
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-markup-templating.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-php.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>

    <script>
        let codeBlockCount = 0;
        let relationCount = 0;
        let changeLogCount = 0;
        let futureDevCount = 0;

        // Initialize Mermaid
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof mermaid !== 'undefined') {
                mermaid.initialize({
                    startOnLoad: false,
                    theme: 'default',
                    flowchart: {
                        useMaxWidth: true,
                        htmlLabels: true
                    }
                });
                renderMermaidBlocks(document);
            }
        });

        function escapeHtmlAttr(str) {
            return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        async function renderMermaidBlocks(root) {
            if (typeof mermaid === 'undefined') return;
            const container = root || document;
            const elements = container.querySelectorAll('.mermaid[data-mermaid-code]:not([data-rendered="true"])');
            for (const el of elements) {
                const code = el.getAttribute('data-mermaid-code');
                const renderId = 'mermaid-' + Math.random().toString(36).slice(2, 11);
                try {
                    const {
                        svg
                    } = await mermaid.render(renderId, code);
                    el.innerHTML = svg;
                    el.setAttribute('data-rendered', 'true');
                } catch (err) {
                    el.innerHTML =
                        `<pre style="margin:0; white-space:pre-wrap; font-family:'JetBrains Mono',monospace; font-size:0.75rem; color:#64748b;">${code}</pre>`;
                }
            }
        }

        /**
         * Export a rendered Mermaid <svg> to a downloadable PNG.
         * Finds the nearest .mermaid container relative to the clicked
         * button and rasterizes its current <svg> at 2x for crispness.
         */
        function exportMermaidPngFromContainer(btn, filename) {
            const container = btn.closest('.flow-block, .mermaid-detail-wrapper') || btn.parentElement.parentElement;
            const svgEl = container ? container.querySelector('svg') : null;
            if (!svgEl) {
                alert('Diagram belum selesai dirender, coba lagi sebentar lagi.');
                return;
            }
            exportSvgToPng(svgEl, filename);
        }

        function exportSvgToPng(svgEl, filename) {
            const scale = 2;
            const svgClone = svgEl.cloneNode(true);
            
            // 1. Pastikan namespace lengkap agar browser merender dengan benar
            svgClone.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
            svgClone.setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');

            const bbox = svgEl.getBoundingClientRect();
            const viewBox = svgEl.viewBox && svgEl.viewBox.baseVal;
            const width = (viewBox && viewBox.width) ? viewBox.width : (bbox.width || 800);
            const height = (viewBox && viewBox.height) ? viewBox.height : (bbox.height || 600);

            svgClone.setAttribute('width', width);
            svgClone.setAttribute('height', height);

            // 2. FIX KRITIS: Suntikkan @font-face lokal untuk mencegah tainting.
            // Ini menipu browser untuk menggunakan font sistem alih-alih mengunduh Google Fonts 
            // saat menggambar <foreignObject> ke canvas.
            const style = document.createElementNS('http://www.w3.org/2000/svg', 'style');
            style.textContent = `
                @font-face {
                    font-family: 'Inter';
                    src: local('Arial'), local('Helvetica'), local('sans-serif');
                }
                @font-face {
                    font-family: 'JetBrains Mono';
                    src: local('Courier New'), local('Consolas'), local('monospace');
                }
                * {
                    font-family: 'Inter', Arial, Helvetica, sans-serif !important;
                }
                .nodeLabel, .edgeLabel, tspan, pre, code, .flow-block {
                    font-family: 'JetBrains Mono', 'Courier New', Consolas, monospace !important;
                }
            `;
            svgClone.insertBefore(style, svgClone.firstChild);

            // 3. Hapus referensi eksternal lain yang mungkin tersisa (seperti @import atau url eksternal)
            let svgData = new XMLSerializer().serializeToString(svgClone);
            svgData = svgData.replace(/@import\s+url\([^)]+\);?/gi, '');
            svgData = svgData.replace(/@import\s+['"][^'"]+['"];?/gi, '');
            svgData = svgData.replace(/url\(\s*['"]?https?:\/\/[^'"]+['"]?\s*\)/gi, 'none');

            const svgBlob = new Blob([svgData], { type: 'image/svg+xml;charset=utf-8' });
            const url = URL.createObjectURL(svgBlob);

            const img = new Image();
            img.crossOrigin = 'anonymous';
            
            img.onload = function() {
                const canvas = document.createElement('canvas');
                canvas.width = width * scale;
                canvas.height = height * scale;
                const ctx = canvas.getContext('2d');
                
                // Isi background putih agar hasil PNG tidak transparan/gelap
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.scale(scale, scale);
                ctx.drawImage(img, 0, 0, width, height);
                URL.revokeObjectURL(url);

                try {
                    canvas.toBlob(function(blob) {
                        if (!blob) {
                            alert('Gagal membuat file PNG. Pastikan diagram tidak mengandung tag <img> dengan URL eksternal.');
                            return;
                        }
                        const a = document.createElement('a');
                        const blobUrl = URL.createObjectURL(blob);
                        a.href = blobUrl;
                        a.download = (filename || 'diagram') + '.png';
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        URL.revokeObjectURL(blobUrl);
                    }, 'image/png');
                } catch (e) {
                    console.error('Canvas export error:', e);
                    alert('Gagal mengekspor diagram. Browser memblokir akses canvas (Tainted Canvas).');
                }
            };
            
            img.onerror = function() {
                URL.revokeObjectURL(url);
                alert('Gagal memuat diagram untuk diekspor.');
            };
            
            img.src = url;
        }

        function openModal() {
            $('#codeDocModalTitle').html(
                '<i class="fas fa-plus-circle" style="color: var(--primary);"></i> Tambah Code Documentation');
            $('#codeDocForm')[0].reset();
            $('#codeDocId').val('');
            $('#codeBlocksContainer, #relationsContainer, #changeLogsContainer, #futureDevContainer').empty();
            codeBlockCount = relationCount = changeLogCount = futureDevCount = 0;
            new bootstrap.Modal(document.getElementById('codeDocModal')).show();
        }

        function closeModal() {
            bootstrap.Modal.getInstance(document.getElementById('codeDocModal'))?.hide();
        }

        // Dynamic Form Functions with smooth animation
        function addCodeBlock() {
            codeBlockCount++;
            const html = `
                <div class="code-block-form p-3 mb-3 bg-white rounded border" id="codeBlock_${codeBlockCount}" style="animation: fadeIn 0.3s ease;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label-custom mb-0">Deskripsi Kode</label>
                        <button type="button" class="btn-remove" onclick="removeCodeBlock(${codeBlockCount})"><i class="fas fa-times"></i></button>
                    </div>
                    <textarea class="form-control form-control-custom mb-3 code-block-description" rows="2" placeholder="Jelaskan fungsi kode ini..."></textarea>
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <select class="form-select form-select-custom code-block-language">
                                <option value="php">PHP</option>
                                <option value="javascript">JavaScript</option>
                                <option value="sql">SQL</option>
                                <option value="html">HTML</option>
                                <option value="css">CSS</option>
                                <option value="bash">Bash</option>
                            </select>
                        </div>
                        <div class="col-md-9 mb-2">
                            <textarea class="form-control form-control-custom code-block-code" rows="5" placeholder="Paste kode di sini..." style="font-family: 'JetBrains Mono', monospace; font-size: 0.8rem;"></textarea>
                        </div>
                    </div>
                </div>`;
            $('#codeBlocksContainer').append(html);
        }

        function removeCodeBlock(id) {
            $(`#codeBlock_${id}`).fadeOut(200, function() {
                $(this).remove();
            });
        }

        function addRelation() {
            relationCount++;
            const html = `
                <div class="d-flex align-items-center gap-2 mb-2" id="relation_${relationCount}" style="animation: fadeIn 0.3s ease;">
                    <input type="text" class="form-control form-control-custom relation-name" placeholder="Nama Model (cth: User)" style="width: 200px;">
                    <button type="button" class="btn-remove" onclick="removeRelation(${relationCount})"><i class="fas fa-times"></i></button>
                </div>`;
            $('#relationsContainer').append(html);
        }

        function removeRelation(id) {
            $(`#relation_${id}`).fadeOut(200, function() {
                $(this).remove();
            });
        }

        function addChangeLog() {
            changeLogCount++;
            const html = `
                <div class="code-block-form p-3 mb-3 bg-white rounded border" id="changeLog_${changeLogCount}" style="animation: fadeIn 0.3s ease;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label-custom mb-0">Detail Perubahan</label>
                        <button type="button" class="btn-remove" onclick="removeChangeLog(${changeLogCount})"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <input type="text" class="form-control form-control-custom change-log-version" placeholder="Versi (1.0)">
                        </div>
                        <div class="col-md-3 mb-2">
                            <input type="date" class="form-control form-control-custom change-log-date">
                        </div>
                        <div class="col-md-6 mb-2">
                            <input type="text" class="form-control form-control-custom change-log-summary" placeholder="Ringkasan singkat">
                        </div>
                    </div>
                    <textarea class="form-control form-control-custom change-log-details" rows="2" placeholder="Detail perubahan yang dilakukan..."></textarea>
                </div>`;
            $('#changeLogsContainer').append(html);
        }

        function removeChangeLog(id) {
            $(`#changeLog_${id}`).fadeOut(200, function() {
                $(this).remove();
            });
        }

        function addFutureDev() {
            futureDevCount++;
            const html = `
                <div class="d-flex align-items-center gap-2 mb-2" id="futureDev_${futureDevCount}" style="animation: fadeIn 0.3s ease;">
                    <input type="text" class="form-control form-control-custom future-dev-item" placeholder="Ide pengembangan selanjutnya..." style="width: 100%;">
                    <button type="button" class="btn-remove" onclick="removeFutureDev(${futureDevCount})"><i class="fas fa-times"></i></button>
                </div>`;
            $('#futureDevContainer').append(html);
        }

        function removeFutureDev(id) {
            $(`#futureDev_${id}`).fadeOut(200, function() {
                $(this).remove();
            });
        }

        function historyActionLabel(action) {
            return action === 'created' ? 'Dibuat' : 'Diperbarui';
        }

        function fieldLabel(field) {
            const labels = {
                title: 'Judul',
                description: 'Deskripsi',
                flow_program: 'Flow',
                code_blocks: 'Kode',
                relations: 'Relasi',
                change_logs: 'Change Log',
                future_development: 'Future Development'
            };
            return labels[field] || field;
        }

        function renderChangeHistory(history) {
            if (!history || history.length === 0) {
                return `<p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">Belum ada riwayat perubahan.</p>`;
            }
            let html = `<div class="history-timeline">`;
            history.forEach(entry => {
                const dateStr = entry.updated_at ?
                    new Date(entry.updated_at.replace(' ', 'T')).toLocaleString('id-ID', {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    }) :
                    '-';
                const fieldsHtml = (entry.fields || []).map(f =>
                    `<span class="history-field-chip">${fieldLabel(f)}</span>`).join('');
                html += `
                    <div class="history-item">
                        <div class="history-dot"></div>
                        <div>
                            <span class="history-action-badge history-action-${entry.action}">${historyActionLabel(entry.action)}</span>
                            <span style="font-weight: 600; color: var(--text-main); font-size: 0.85rem;">${entry.updated_by_name}</span>
                        </div>
                        <div style="color: var(--text-muted); font-size: 0.78rem; margin: 0.2rem 0 0.35rem;">${dateStr}</div>
                        ${fieldsHtml ? `<div>${fieldsHtml}</div>` : ''}
                    </div>`;
            });
            html += `</div>`;
            return html;
        }

        function viewDetailCodeDoc(id) {
            $('#detailModalBody').html(`
                <div class="text-center py-5">
                    <div class="spinner-border" style="color: var(--primary); width: 2rem; height: 2rem;" role="status"></div>
                    <p class="mt-3" style="color: var(--text-muted);">Memuat detail dokumentasi...</p>
                </div>
            `);
            new bootstrap.Modal(document.getElementById('detailCodeDocModal')).show();

            $.ajax({
                url: `/system/documentation/codes/${id}`,
                method: 'GET',
                success: function(response) {
                    let html =
                        `<h4 style="color: var(--text-main); font-weight: 700; margin-bottom: 1.5rem; font-size: 1.25rem;">${response.title}</h4>`;

                    if (response.description) {
                        html += `<div class="mb-4">
                            <div class="section-label"><span><i class="fas fa-info-circle"></i> Deskripsi Lengkap</span></div>
                            <p style="color: var(--text-muted); line-height: 1.6; font-size: 0.95rem; margin: 0;">${response.description}</p>
                        </div>`;
                    }

                    if (response.flow_program) {
                        const isMermaid = response.flow_program.type === 'mermaid';
                        let flowHtml = isMermaid ?
                            `<div class="mermaid" data-mermaid-code="${escapeHtmlAttr(response.flow_program.content)}" style="text-align: center; padding: 1rem;"></div>` :
                            `<pre style="margin: 0; white-space: pre-wrap; font-family: 'JetBrains Mono', monospace; font-size: 0.85rem; background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid var(--border-color);">${response.flow_program.content}</pre>`;

                        html += `<div class="mb-4">
                            <div class="section-label">
                                <span><i class="fas fa-project-diagram"></i> Flow Program</span>
                                ${isMermaid ? `<button type="button" class="btn-export-png" onclick="exportMermaidPngFromContainer(this, '${response.title.replace(/[^a-z0-9]+/gi, '-').toLowerCase()}-flow')"><i class="fas fa-image"></i> Export PNG</button>` : ''}
                            </div>
                            <div class="mermaid-detail-wrapper" style="background: #ffffff; border-left: 4px solid var(--primary); padding: 1.5rem; border-radius: 0 8px 8px 8px; box-shadow: var(--shadow-sm);">
                                ${flowHtml}
                            </div>
                        </div>`;
                    }

                    if (response.code_blocks && response.code_blocks.length > 0) {
                        html +=
                            `<div class="mb-4">
                            <div class="section-label"><span><i class="fas fa-laptop-code"></i> Kode Implementasi (${response.code_blocks.length})</span></div>`;
                        response.code_blocks.forEach((block, index) => {
                            let lang = block.language || 'php';
                            let desc = block.description ?
                                `<p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.5rem; font-style: italic;">${block.description}</p>` :
                                '';
                            html += `<div class="mb-3">
                                ${desc}
                                <div class="code-preview" style="border-radius: 10px;">
                                    <div class="code-preview-header">
                                        <span class="code-lang">${lang}</span>
                                        <button class="btn-icon" style="width: 28px; height: 28px; border-color: #334155; color: #94a3b8;" data-code="${block.code.replace(/"/g, '&quot;')}" onclick="copyCode(this)">
                                            <i class="fas fa-copy" style="font-size: 0.75rem;"></i>
                                        </button>
                                    </div>
                                    <div class="code-preview-body" style="min-height: 300px;">
                                        <pre><code class="language-${lang}" style="font-size: 0.85rem;">${block.code}</code></pre>
                                    </div>
                                </div>
                            </div>`;
                        });
                        html += `</div>`;
                    }

                    if (response.relations && response.relations.length > 0) {
                        html += `<div class="mb-4">
                            <div class="section-label"><span><i class="fas fa-link"></i> Relasi Model</span></div>
                            <div class="d-flex flex-wrap gap-2">`;
                        response.relations.forEach(rel => {
                            html +=
                                `<span class="relation-pill"><i class="fas fa-database" style="font-size: 0.6rem;"></i> ${rel}</span>`;
                        });
                        html += `</div></div>`;
                    }

                    if (response.change_logs && response.change_logs.length > 0) {
                        html +=
                            `<div class="mb-4">
                            <div class="section-label"><span><i class="fas fa-history"></i> Change Log</span></div>
                            <div style="position: relative; padding-left: 1.5rem; border-left: 2px solid var(--border-color);">`;
                        response.change_logs.forEach(log => {
                            html += `<div style="position: relative; margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                                <div style="position: absolute; left: -1.95rem; top: 0.25rem; width: 12px; height: 12px; border-radius: 50%; background: var(--primary); border: 3px solid white; box-shadow: var(--shadow-sm);"></div>
                                <div style="font-weight: 700; color: var(--text-main); font-size: 0.95rem;">Version ${log.version}</div>
                                <div style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 0.5rem;">${log.date || '-'}</div>
                                ${log.summary ? `<div style="color: var(--text-main); font-size: 0.9rem; margin-bottom: 0.25rem; font-weight: 500;">${log.summary}</div>` : ''}
                                ${log.details ? `<div style="color: var(--text-muted); font-size: 0.85rem; line-height: 1.5;">${log.details}</div>` : ''}
                            </div>`;
                        });
                        html += `</div></div>`;
                    }

                    if (response.future_development && response.future_development.length > 0) {
                        html += `<div class="mb-4">
                            <div class="section-label"><span><i class="fas fa-lightbulb"></i> Future Development</span></div>
                            <ul style="list-style: none; padding: 0; margin: 0;">`;
                        response.future_development.forEach(item => {
                            html += `<li style="padding: 0.75rem 1rem; background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.75rem; color: var(--text-main); font-size: 0.9rem;">
                                <i class="fas fa-check-circle" style="color: var(--primary);"></i> ${item}
                            </li>`;
                        });
                        html += `</ul></div>`;
                    }

                    // Riwayat perubahan otomatis (log_update / log_time_update / log_changes)
                    html += `<div>
                        <div class="section-label"><span><i class="fas fa-clock-rotate-left"></i> Riwayat Perubahan Sistem</span></div>
                        ${renderChangeHistory(response.change_history)}
                    </div>`;

                    $('#detailModalBody').html(html);

                    setTimeout(() => {
                        const modalBody = document.getElementById('detailModalBody');
                        if (typeof Prism !== 'undefined') Prism.highlightAllUnder(modalBody);
                        renderMermaidBlocks(modalBody);
                    }, 100);
                },
                error: function() {
                    $('#detailModalBody').html(`
                        <div class="text-center py-5">
                            <i class="fas fa-exclamation-circle fa-2x mb-3" style="color: #cbd5e1;"></i>
                            <p style="color: var(--text-muted);">Gagal memuat detail dokumentasi.</p>
                        </div>`);
                }
            });
        }

        function editCodeDoc(id) {
            $.ajax({
                url: `/system/documentation/codes/${id}`,
                method: 'GET',
                success: function(response) {
                    $('#codeDocModalTitle').html(
                        '<i class="fas fa-pen" style="color: var(--primary);"></i> Edit Code Documentation');
                    $('#codeDocId').val(response.id);
                    $('#title').val(response.title);
                    $('#description').val(response.description || '');
                    if (response.flow_program) {
                        $('#flow_type').val(response.flow_program.type);
                        $('#flow_content').val(response.flow_program.content);
                    }

                    $('#codeBlocksContainer, #relationsContainer, #changeLogsContainer, #futureDevContainer')
                        .empty();
                    codeBlockCount = relationCount = changeLogCount = futureDevCount = 0;

                    if (response.code_blocks) {
                        response.code_blocks.forEach(block => {
                            codeBlockCount++;
                            $('#codeBlocksContainer').append(`
                                <div class="code-block-form p-3 mb-3 bg-white rounded border" id="codeBlock_${codeBlockCount}">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label-custom mb-0">Deskripsi Kode</label>
                                        <button type="button" class="btn-remove" onclick="removeCodeBlock(${codeBlockCount})"><i class="fas fa-times"></i></button>
                                    </div>
                                    <textarea class="form-control form-control-custom mb-3 code-block-description" rows="2">${block.description || ''}</textarea>
                                    <div class="row">
                                        <div class="col-md-3 mb-2">
                                            <select class="form-select form-select-custom code-block-language">
                                                <option value="php" ${block.language === 'php' ? 'selected' : ''}>PHP</option>
                                                <option value="javascript" ${block.language === 'javascript' ? 'selected' : ''}>JavaScript</option>
                                                <option value="sql" ${block.language === 'sql' ? 'selected' : ''}>SQL</option>
                                                <option value="html" ${block.language === 'html' ? 'selected' : ''}>HTML</option>
                                                <option value="css" ${block.language === 'css' ? 'selected' : ''}>CSS</option>
                                            </select>
                                        </div>
                                        <div class="col-md-9 mb-2">
                                            <textarea class="form-control form-control-custom code-block-code" rows="5" style="font-family: 'JetBrains Mono', monospace; font-size: 0.8rem;">${block.code}</textarea>
                                        </div>
                                    </div>
                                </div>`);
                        });
                    }

                    if (response.relations) {
                        response.relations.forEach(rel => {
                            relationCount++;
                            $('#relationsContainer').append(`
                                <div class="d-flex align-items-center gap-2 mb-2" id="relation_${relationCount}">
                                    <input type="text" class="form-control form-control-custom relation-name" value="${rel}" style="width: 200px;">
                                    <button type="button" class="btn-remove" onclick="removeRelation(${relationCount})"><i class="fas fa-times"></i></button>
                                </div>`);
                        });
                    }

                    if (response.change_logs) {
                        response.change_logs.forEach(log => {
                            changeLogCount++;
                            $('#changeLogsContainer').append(`
                                <div class="code-block-form p-3 mb-3 bg-white rounded border" id="changeLog_${changeLogCount}">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label-custom mb-0">Detail Perubahan</label>
                                        <button type="button" class="btn-remove" onclick="removeChangeLog(${changeLogCount})"><i class="fas fa-times"></i></button>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-custom change-log-version" value="${log.version}"></div>
                                        <div class="col-md-3 mb-2"><input type="date" class="form-control form-control-custom change-log-date" value="${log.date || ''}"></div>
                                        <div class="col-md-6 mb-2"><input type="text" class="form-control form-control-custom change-log-summary" value="${log.summary || ''}"></div>
                                    </div>
                                    <textarea class="form-control form-control-custom change-log-details" rows="2">${log.details || ''}</textarea>
                                </div>`);
                        });
                    }

                    if (response.future_development) {
                        response.future_development.forEach(item => {
                            futureDevCount++;
                            $('#futureDevContainer').append(`
                                <div class="d-flex align-items-center gap-2 mb-2" id="futureDev_${futureDevCount}">
                                    <input type="text" class="form-control form-control-custom future-dev-item" value="${item}" style="width: 100%;">
                                    <button type="button" class="btn-remove" onclick="removeFutureDev(${futureDevCount})"><i class="fas fa-times"></i></button>
                                </div>`);
                        });
                    }

                    new bootstrap.Modal(document.getElementById('codeDocModal')).show();
                }
            });
        }

        function deleteCodeDoc(id) {
            if (confirm('Apakah Anda yakin ingin menghapus dokumentasi kode ini? Tindakan ini tidak dapat dibatalkan.')) {
                $.ajax({
                    url: `/system/documentation/codes/${id}`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) location.reload();
                    }
                });
            }
        }

        function copyCode(btn) {
            const code = $(btn).data('code');
            navigator.clipboard.writeText(code).then(() => {
                const toast = document.getElementById('copyFeedback');
                toast.classList.add('show');
                setTimeout(() => {
                    toast.classList.remove('show');
                }, 2000);
            });
        }

        $('#codeDocForm').on('submit', function(e) {
            e.preventDefault();
            const id = $('#codeDocId').val();
            const featureId = '{{ $feature->id }}';
            const url = id ? `/system/documentation/codes/${id}?_method=PUT` :
                `/system/documentation/features/${featureId}/codes`;

            const codeBlocks = [];
            $('.code-block-form').each(function() {
                codeBlocks.push({
                    description: $(this).find('.code-block-description').val(),
                    language: $(this).find('.code-block-language').val(),
                    code: $(this).find('.code-block-code').val()
                });
            });

            const relations = [];
            $('.relation-name').each(function() {
                if ($(this).val()) relations.push($(this).val());
            });

            const changeLogs = [];
            $('#changeLogsContainer .code-block-form').each(function() {
                changeLogs.push({
                    version: $(this).find('.change-log-version').val(),
                    date: $(this).find('.change-log-date').val(),
                    summary: $(this).find('.change-log-summary').val(),
                    details: $(this).find('.change-log-details').val()
                });
            });

            const futureDevelopment = [];
            $('.future-dev-item').each(function() {
                if ($(this).val()) futureDevelopment.push($(this).val());
            });

            const formData = {
                _token: '{{ csrf_token() }}',
                title: $('#title').val(),
                description: $('#description').val(),
                flow_type: $('#flow_type').val(),
                flow_content: $('#flow_content').val(),
                code_blocks: codeBlocks,
                relations: relations,
                change_logs: changeLogs,
                future_development: futureDevelopment
            };

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) location.reload();
                },
                error: function(xhr) {
                    alert(
                        'Terjadi kesalahan saat menyimpan data. Pastikan semua field terisi dengan benar.');
                }
            });
        });
    </script>
@endsection
