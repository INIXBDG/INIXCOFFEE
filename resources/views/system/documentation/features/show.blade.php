@extends('layouts.app')

@section('content')
    <!-- External Resources -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* Hanya yang benar-benar tidak bisa di-handle Bootstrap */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: skeleton-loading 1.5s ease-in-out infinite;
            border-radius: 0.25rem;
        }
        @keyframes skeleton-loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        .skeleton-text { height: 16px; width: 60%; margin-bottom: 6px; }
        .skeleton-title { height: 28px; width: 80%; }

        /* Calendar grid (Bootstrap tidak punya native calendar) */
        .kalender-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 3px;
            background: #f8f9fa;
            padding: 6px;
            border-radius: 0.5rem;
        }
        .kal-header { text-align: center; font-size: 0.7rem; font-weight: 600; color: #6c757d; padding: 4px 0; }
        .kal-cell {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            cursor: pointer;
            transition: transform 0.15s;
        }
        .kal-cell:hover { transform: scale(1.1); z-index: 2; }
        .kal-working { background: #d1e7dd; color: #0f5132; }
        .kal-cuti { background: #fff3cd; color: #664d03; }
        .kal-libur { background: #f8d7da; color: #842029; }
        .kal-weekend { background: #e2e3e5; color: #41464b; }
        .kal-empty { background: transparent; }
        .kal-legend-box {
            display: inline-block;
            width: 14px;
            height: 14px;
            border-radius: 3px;
            vertical-align: middle;
            margin-right: 4px;
        }

        .modal-dialog-scrollable {
            max-height: calc(100vh - 1rem);
        }

        .modal-dialog-scrollable .modal-content {
            max-height: calc(100vh - 1rem);
            overflow: hidden; 
        }
        .modal-dialog-scrollable .modal-body {
            overflow-y: auto !important;
            max-height: calc(100vh - 200px); 
        }

        @media (max-width: 576px) {
            .modal-dialog-scrollable .modal-body {
                max-height: calc(100vh - 160px);
            }
        }
    </style>

    <div class="container-fluid py-4">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('documentation.features.index') }}" class="text-decoration-none">
                        <i class="fas fa-home"></i> Features
                    </a>
                </li>
                @foreach ($ancestors as $ancestor)
                    <li class="breadcrumb-item">
                        <a href="{{ route('documentation.features.show', $ancestor->id) }}" class="text-decoration-none">
                            {{ $ancestor->name }}
                        </a>
                    </li>
                @endforeach
                <li class="breadcrumb-item active" aria-current="page">{{ $feature->name }}</li>
            </ol>
        </nav>

        <!-- Hero / Header -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div>
                        <h1 class="h3 fw-bold mb-2">{{ $feature->name }}</h1>
                        <p class="text-muted mb-3" style="max-width: 640px;">
                            {{ $feature->short_description }}
                        </p>
                        <div class="d-flex gap-2 flex-wrap">
                            <span class="badge rounded-pill
                                @if($feature->status === 'production') bg-success
                                @elseif($feature->status === 'development') bg-primary
                                @elseif($feature->status === 'draft') bg-secondary
                                @else bg-danger
                                @endif">
                                {{ ucfirst($feature->status) }}
                            </span>
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-tag me-1"></i>{{ $feature->category }}
                            </span>
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-code-branch me-1"></i>{{ $feature->document_version }}
                            </span>
                            @if ($feature->parentFeature)
                                <span class="badge bg-light text-dark border">
                                    <i class="fas fa-sitemap me-1"></i>Sub fitur dari {{ $feature->parentFeature->name }}
                                </span>
                            @endif
                            @if ($feature->last_updated_at)
                                <span class="badge bg-light text-dark border">
                                    <i class="far fa-clock me-1"></i>{{ $feature->last_updated_at }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('documentation.features.index') }}" class="btn btn-outline-secondary btn-sm" title="Kembali">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <button onclick="addSubFeature({{ $feature->id }})" class="btn btn-outline-secondary btn-sm" title="Tambah Sub Fitur">
                            <i class="fas fa-sitemap"></i>
                        </button>
                        <button onclick="editFeature({{ $feature->id }})" class="btn btn-outline-secondary btn-sm" title="Edit">
                            <i class="fas fa-pen"></i>
                        </button>
                        <a href="{{ route('documentation.codes.index', $feature->id) }}" class="btn btn-outline-secondary btn-sm" title="Lihat Dokumentasi Kode">
                            <i class="fas fa-code"></i>
                        </a>
                        <a href="{{ route('documentation.features.manual', $feature->id) }}" class="btn btn-outline-secondary btn-sm" target="_blank" title="Manual PDF">
                            <i class="fas fa-file-pdf"></i>
                        </a>
                        <button onclick="deleteFeature({{ $feature->id }}, {{ $feature->parent_id ?? 'null' }})" class="btn btn-outline-danger btn-sm" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Column -->
            <div class="col-lg-8">
                @if ($feature->purpose)
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom-0 pb-0">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-bullseye text-primary me-2"></i>Tujuan Fitur
                            </h5>
                        </div>
                        <div class="card-body">
                            {!! $feature->purpose !!}
                        </div>
                    </div>
                @endif

                @if ($feature->problem_solved)
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom-0 pb-0">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-triangle-exclamation text-primary me-2"></i>Masalah yang Diselesaikan
                            </h5>
                        </div>
                        <div class="card-body">
                            {!! $feature->problem_solved !!}
                        </div>
                    </div>
                @endif

                @if ($feature->how_it_works)
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom-0 pb-0">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-diagram-project text-primary me-2"></i>Cara Kerja
                            </h5>
                        </div>
                        <div class="card-body">
                            {!! $feature->how_it_works !!}
                        </div>
                    </div>
                @endif

                @if ($feature->user_access)
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom-0 pb-0">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user-shield text-primary me-2"></i>Hak Akses Pengguna
                            </h5>
                        </div>
                        <div class="card-body">
                            {!! $feature->user_access !!}
                        </div>
                    </div>
                @endif

                <!-- Sub Fitur -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom-0 d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-sitemap text-primary me-2"></i>Sub Fitur
                            <span class="badge bg-primary-subtle text-primary rounded-pill ms-2">
                                {{ $feature->children_count }}
                            </span>
                        </h5>
                    </div>
                    <div class="card-body">
                        @if ($feature->children->count() > 0)
                            <div class="row g-3">
                                @foreach ($feature->children as $child)
                                    <div class="col-md-6">
                                        <a href="{{ route('documentation.features.show', $child->id) }}"
                                           class="card h-100 text-decoration-none border shadow-sm">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="card-title mb-0 text-dark">{{ $child->name }}</h6>
                                                    <span class="badge rounded-pill
                                                        @if($child->status === 'production') bg-success
                                                        @elseif($child->status === 'development') bg-primary
                                                        @elseif($child->status === 'draft') bg-secondary
                                                        @else bg-danger
                                                        @endif"
                                                          style="font-size: 0.65rem;">
                                                        {{ ucfirst($child->status) }}
                                                    </span>
                                                </div>
                                                <p class="card-text text-muted small mb-2">
                                                    {{ Str::limit($child->short_description, 90) }}
                                                </p>
                                                <div class="d-flex gap-3 small text-muted">
                                                    <span><i class="fas fa-code-branch me-1"></i>{{ $child->document_version }}</span>
                                                    @if ($child->children_count > 0)
                                                        <span><i class="fas fa-sitemap me-1"></i>{{ $child->children_count }} sub fitur</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <div class="mb-3">
                                    <i class="fas fa-folder-open fa-3x text-secondary opacity-50"></i>
                                </div>
                                <p class="text-muted mb-3">Fitur ini belum memiliki sub fitur.</p>
                                <button onclick="addSubFeature({{ $feature->id }})" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> Tambah Sub Fitur
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Dokumentasi Kode -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom-0">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-code text-primary me-2"></i>Dokumentasi Kode
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">
                            {{ $feature->codeDocumentations->count() }} entri dokumentasi teknis/kode tersimpan untuk fitur ini.
                        </p>
                        <a href="{{ route('documentation.codes.index', $feature->id) }}"
                           class="btn btn-primary w-100">
                            Buka Dokumentasi Kode
                        </a>
                    </div>
                </div>

                <!-- Riwayat Revisi -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom-0">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-history text-primary me-2"></i>Riwayat Revisi
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        @if (count($history) > 0)
                            <div class="list-group list-group-flush">
                                @foreach ($history as $index => $entry)
                                    <div class="list-group-item px-3 py-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fw-semibold">v{{ $entry['version'] }}</span>
                                                @if ($index === 0)
                                                    <span class="badge bg-primary">
                                                        Terbaru, Diperbarui oleh {{ $entry['updated_by_name'] }}
                                                    </span>
                                                @endif
                                            </div>
                                            <small class="text-muted">
                                                <i class="far fa-clock me-1"></i>
                                                @if ($entry['updated_at'])
                                                    {{ \Carbon\Carbon::parse($entry['updated_at'])->translatedFormat('d M Y H:i') }}
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-history fa-2x text-secondary opacity-50 mb-2"></i>
                                <p class="mb-0 text-muted small">Belum ada riwayat revisi.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal (Bootstrap native) -->
    <div class="modal fade" id="featureModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalTitle">
                        <i class="fas fa-plus-circle text-primary me-2"></i> Tambah Fitur Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="featureForm">
                    @csrf
                    <input type="hidden" id="featureId" name="id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Fitur Induk (opsional)</label>
                            <select class="form-select" id="parent_id" name="parent_id">
                                <option value="">-- Tidak ada, ini fitur utama --</option>
                            </select>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-7">
                                <label class="form-label">Nama Fitur <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Kategori <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="category" name="category" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="status_radio" id="status_draft" value="draft" checked>
                                <label class="btn btn-outline-secondary" for="status_draft">Draft</label>

                                <input type="radio" class="btn-check" name="status_radio" id="status_development" value="development">
                                <label class="btn btn-outline-secondary" for="status_development">Development</label>

                                <input type="radio" class="btn-check" name="status_radio" id="status_production" value="production">
                                <label class="btn btn-outline-secondary" for="status_production">Production</label>

                                <input type="radio" class="btn-check" name="status_radio" id="status_deprecated" value="deprecated">
                                <label class="btn btn-outline-secondary" for="status_deprecated">Deprecated</label>
                            </div>
                            <input type="hidden" id="status" name="status" value="draft">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi Singkat <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="short_description" name="short_description" rows="4" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tujuan <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="purpose" name="purpose" rows="6" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Masalah yang Diselesaikan</label>
                            <textarea class="form-control" id="problem_solved" name="problem_solved" rows="4"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Cara Kerja</label>
                            <textarea class="form-control" id="how_it_works" name="how_it_works" rows="5"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Hak Akses Pengguna</label>
                            <textarea class="form-control" id="user_access" name="user_access" rows="4"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Simpan Fitur
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let featureModalInstance = null;
        const currentFeatureId = {{ $feature->id }};

        // Status radio → hidden input
        document.querySelectorAll('input[name="status_radio"]').forEach(radio => {
            radio.addEventListener('change', function () {
                document.getElementById('status').value = this.value;
            });
        });

        function loadParentOptions(excludeId = null, selectedId = null) {
            let url = '{{ route('documentation.features.options') }}';
            if (excludeId) url += `?exclude=${excludeId}`;

            $.get(url, function (response) {
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
                `<i class="fas fa-plus-circle text-primary me-2"></i> Tambah Fitur Baru`;
            document.getElementById('featureForm').reset();
            document.getElementById('featureId').value = '';
            document.getElementById('status').value = 'draft';
            document.getElementById('status_draft').checked = true;

            loadParentOptions();

            featureModalInstance = new bootstrap.Modal(document.getElementById('featureModal'));
            featureModalInstance.show();
        }

        function addSubFeature(parentId) {
            openModal();
            document.getElementById('modalTitle').innerHTML =
                `<i class="fas fa-sitemap text-primary me-2"></i> Tambah Sub Fitur`;
            loadParentOptions(null, parentId);
        }

        function editFeature(id) {
            $.get(`/system/documentation/features/${id}/edit-data`, function (response) {
                document.getElementById('modalTitle').innerHTML =
                    `<i class="fas fa-pen text-primary me-2"></i> Edit Fitur`;
                document.getElementById('featureId').value = response.id;
                document.getElementById('name').value = response.name;
                document.getElementById('category').value = response.category;
                document.getElementById('short_description').value = response.short_description || '';
                document.getElementById('purpose').value = response.purpose || '';
                document.getElementById('problem_solved').value = response.problem_solved || '';
                document.getElementById('how_it_works').value = response.how_it_works || '';
                document.getElementById('user_access').value = response.user_access || '';

                document.getElementById('status').value = response.status;
                const radio = document.getElementById(`status_${response.status}`);
                if (radio) radio.checked = true;

                loadParentOptions(response.id, response.parent_id);

                featureModalInstance = new bootstrap.Modal(document.getElementById('featureModal'));
                featureModalInstance.show();
            });
        }

        function deleteFeature(id, parentId, force = false) {
            if (!force && !confirm('Apakah Anda yakin ingin menghapus dokumentasi fitur ini? Tindakan ini tidak dapat dibatalkan.')) {
                return;
            }

            $.ajax({
                url: `/system/documentation/features/${id}${force ? '?force=1' : ''}`,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: () => {
                    window.location.href = parentId
                        ? `/system/documentation/features/${parentId}`
                        : '{{ route('documentation.features.index') }}';
                },
                statusCode: {
                    409: function (xhr) {
                        const res = xhr.responseJSON;
                        if (confirm(res.message + ' Klik OK untuk menghapus beserta seluruh sub fiturnya.')) {
                            deleteFeature(id, parentId, true);
                        }
                    }
                }
            });
        }

        $('#featureForm').on('submit', function (e) {
            e.preventDefault();
            const id = $('#featureId').val();
            const url = id
                ? `/system/documentation/features/${id}?_method=PUT`
                : '/system/documentation/features';

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