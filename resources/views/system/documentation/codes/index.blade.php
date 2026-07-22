@extends('system.documentation.layout')

@section('title', 'Code Documentation - ' . $feature->name)

@section('content')
    <!-- Breadcrumb -->
    <div class="breadcrumb-custom animate-fade-in"
        style="background: white; padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 2rem; box-shadow: var(--shadow-soft); display: flex; align-items: center; gap: 0.5rem;">
        <a href="{{ route('documentation.features.index') }}" style="color: var(--text-secondary); text-decoration: none;"><i
                class="fas fa-home"></i> Features</a>
        <span style="color: var(--text-secondary); opacity: 0.5;"><i class="fas fa-chevron-right"></i></span>
        <span style="color: var(--primary-navy); font-weight: 600;">{{ $feature->name }}</span>
    </div>

    <!-- Header -->
    <div class="page-header animate-fade-in">
        <div>
            <h1 class="header-title"><i class="fas fa-code"></i> Code Documentation</h1>
            <p class="header-subtitle">Dokumentasi teknis implementasi fitur</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('documentation.features.index') }}" class="btn-secondary-custom"><i
                    class="fas fa-arrow-left"></i> Kembali</a>
            <button class="btn-primary-custom" onclick="openModal()"><i class="fas fa-plus"></i> Tambah Dokumentasi</button>
        </div>
    </div>

    <!-- Feature Info Card -->
    <div class="feature-info-card animate-fade-in"
        style="background: linear-gradient(135deg, var(--primary-navy) 0%, #2c5282 100%); color: white; padding: 2rem; border-radius: 16px; margin-bottom: 2rem; box-shadow: var(--shadow-medium); position: relative; overflow: hidden;">
        <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem;">{{ $feature->name }}</h2>
        <p style="opacity: 0.9; margin: 0;">{{ $feature->short_description }}</p>
        <div style="display: flex; gap: 2rem; margin-top: 1rem; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; opacity: 0.9;"><i
                    class="fas fa-folder"></i> {{ $feature->category }}</div>
            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; opacity: 0.9;"><i
                    class="fas fa-circle" style="font-size: 0.5rem;"></i> {{ ucfirst($feature->status) }}</div>
            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; opacity: 0.9;"><i
                    class="fas fa-file-code"></i> {{ $codeDocs->total() }} Dokumentasi</div>
        </div>
    </div>

    <!-- Code Documentation Grid List -->
    <div class="row g-4">
        @forelse($codeDocs as $codeDoc)
            <div class="col-md-6 col-lg-4">
                <div class="code-doc-card animate-fade-in h-100"
                    style="background: white; border-radius: 16px; box-shadow: var(--shadow-soft); overflow: hidden; display: flex; flex-direction: column; transition: transform 0.3s ease, box-shadow 0.3s ease;">

                    <!-- Card Header -->
                    <div
                        style="padding: 1.25rem 1.5rem; background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border-bottom: 1px solid var(--border-soft); display: flex; justify-content: space-between; align-items: flex-start;">
                        <h3
                            style="font-size: 1.05rem; font-weight: 600; color: var(--primary-navy); margin: 0; display: flex; align-items: center; gap: 0.5rem; line-height: 1.4; word-break: break-word;">
                            <i class="fas fa-file-code" style="color: var(--primary-red); flex-shrink: 0;"></i>
                            <span>{{ $codeDoc->title }}</span>
                        </h3>
                        <div class="d-flex gap-1" style="flex-shrink: 0; margin-left: 0.5rem;">
                            <button class="btn btn-sm btn-light" onclick="viewDetailCodeDoc({{ $codeDoc->id }})"
                                title="Lihat Detail Lengkap">
                                <i class="fas fa-eye text-info"></i>
                            </button>
                            <button class="btn btn-sm btn-light" onclick="editCodeDoc({{ $codeDoc->id }})" title="Edit">
                                <i class="fas fa-edit text-primary"></i>
                            </button>
                            <button class="btn btn-sm btn-light" onclick="deleteCodeDoc({{ $codeDoc->id }})"
                                title="Hapus">
                                <i class="fas fa-trash text-danger"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Card Body (Compact Preview) -->
                    <div style="padding: 1.5rem; flex-grow: 1; display: flex; flex-direction: column; gap: 1.25rem;">

                        @if ($codeDoc->description)
                            <div>
                                <div
                                    style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.4rem;">
                                    <i class="fas fa-info-circle" style="color: var(--primary-navy);"></i> Deskripsi
                                </div>
                                <p style="color: var(--text-primary); line-height: 1.5; font-size: 0.875rem; margin: 0;">
                                    {{ Str::limit($codeDoc->description, 120) }}</p>
                            </div>
                        @endif

                        @if ($codeDoc->flow_program)
                            <div>
                                <div
                                    style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.4rem;">
                                    <i class="fas fa-project-diagram" style="color: var(--primary-navy);"></i> Flow
                                </div>
                                <div
                                    style="background: #f8f9fa; border-left: 3px solid var(--primary-navy); padding: 1rem; border-radius: 6px; font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; white-space: pre-wrap; color: var(--text-primary); max-height: 120px; overflow-y: auto;">
                                    @if ($codeDoc->flow_program['type'] == 'mermaid')
                                        <div class="mermaid" style="font-size: 0.7rem;">
                                            {{ $codeDoc->flow_program['content'] }}</div>
                                    @else
                                        {{ Str::limit($codeDoc->flow_program['content'], 150) }}
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if ($codeDoc->code_blocks && count($codeDoc->code_blocks) > 0)
                            <div>
                                <div
                                    style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.4rem;">
                                    <i class="fas fa-laptop-code" style="color: var(--primary-navy);"></i> Kode
                                    ({{ count($codeDoc->code_blocks) }})
                                </div>
                                @foreach (array_slice($codeDoc->code_blocks, 0, 2) as $block)
                                    <div style="margin-bottom: 0.75rem;">
                                        @if (isset($block['description']) && $block['description'])
                                            <p
                                                style="color: var(--text-secondary); font-size: 0.75rem; margin-bottom: 0.3rem; font-style: italic;">
                                                {{ Str::limit($block['description'], 50) }}</p>
                                        @endif
                                        <div
                                            style="position: relative; background: var(--code-bg); border-radius: 8px; overflow: hidden;">
                                            <div
                                                style="background: #2d2d2d; padding: 0.4rem 0.75rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #3d3d3d;">
                                                <span
                                                    style="color: var(--primary-light-blue); font-size: 0.65rem; font-weight: 600; text-transform: uppercase;">{{ $block['language'] ?? 'php' }}</span>
                                                <button class="btn btn-sm btn-outline-light"
                                                    style="font-size: 0.65rem; padding: 0.1rem 0.4rem;"
                                                    data-code="{{ addslashes($block['code']) }}" onclick="copyCode(this)">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                            <div style="padding: 0.75rem; overflow-x: auto; max-height: 100px;">
                                                <pre style="margin: 0;"><code class="language-{{ $block['language'] ?? 'php' }}" style="font-size: 0.7rem;">{{ Str::limit($block['code'], 120) }}</code></pre>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                @if (count($codeDoc->code_blocks) > 2)
                                    <div
                                        style="text-align: center; font-size: 0.75rem; color: var(--text-secondary); font-style: italic; margin-top: 0.5rem;">
                                        +{{ count($codeDoc->code_blocks) - 2 }} kode lainnya
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if ($codeDoc->relations && count($codeDoc->relations) > 0)
                            <div>
                                <div
                                    style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.4rem;">
                                    <i class="fas fa-link" style="color: var(--primary-navy);"></i> Relasi
                                </div>
                                <div style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
                                    @foreach (array_slice($codeDoc->relations, 0, 3) as $relation)
                                        <span
                                            style="background: linear-gradient(135deg, var(--primary-light-blue), #7fb3d5); color: var(--primary-navy); padding: 0.3rem 0.65rem; border-radius: 12px; font-size: 0.7rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.3rem;">
                                            <i class="fas fa-database" style="font-size: 0.6rem;"></i> {{ $relation }}
                                        </span>
                                    @endforeach
                                    @if (count($codeDoc->relations) > 3)
                                        <span
                                            style="background: #e2e8f0; color: var(--text-secondary); padding: 0.3rem 0.65rem; border-radius: 12px; font-size: 0.7rem; font-weight: 600;">
                                            +{{ count($codeDoc->relations) - 3 }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="code-doc-card animate-fade-in"
                    style="background: white; border-radius: 16px; box-shadow: var(--shadow-soft); text-align: center; padding: 4rem 2rem;">
                    <i class="fas fa-code fa-3x mb-3 d-block" style="color: var(--border-soft);"></i>
                    <h4 style="color: var(--text-primary);">Belum Ada Dokumentasi Kode</h4>
                    <p style="color: var(--text-secondary);">Mulai mendokumentasikan implementasi teknis fitur ini</p>
                    <button class="btn-primary-custom mt-3" onclick="openModal()"><i class="fas fa-plus"></i> Tambah
                        Dokumentasi Kode</button>
                </div>
            </div>
        @endforelse
    </div>

    @if ($codeDocs->hasPages())
        <div class="text-center mt-4">
            {{ $codeDocs->links() }}
        </div>
    @endif

    <!-- ========================================== -->
    <!-- MODAL DETAIL LENGKAP (FULL CONTENT)        -->
    <!-- ========================================== -->
    <div class="modal fade" id="detailCodeDocModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content modal-content-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title modal-title-custom" id="detailModalTitle">
                        <i class="fas fa-file-code"></i> Detail Dokumentasi Lengkap
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body modal-body-custom" id="detailModalBody">
                    <!-- Content will be injected here via AJAX -->
                    <div class="text-center py-5">
                        <div class="spinner-border" style="color: var(--primary-navy);" role="status"></div>
                        <p class="mt-2" style="color: var(--text-secondary);">Memuat detail...</p>
                    </div>
                </div>
                <div class="modal-footer modal-footer-custom">
                    <button type="button" class="btn-secondary-custom" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form for Add/Edit Code Doc (Simplified for brevity, keep your existing one) -->
    <div class="modal fade" id="codeDocModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title modal-title-custom" id="codeDocModalTitle"><i class="fas fa-plus-circle"></i>
                        Tambah Code Documentation</h5>
                    <button type="button" class="btn-close btn-close-white" onclick="closeModal()"></button>
                </div>
                <form id="codeDocForm">
                    @csrf
                    <div class="modal-body modal-body-custom">
                        <input type="hidden" id="codeDocId" name="id">
                        <div class="mb-3">
                            <label class="form-label-custom">Judul Dokumentasi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control-custom" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Deskripsi</label>
                            <textarea class="form-control-custom" id="description" name="description" rows="2"></textarea>
                        </div>

                        <div class="dynamic-section">
                            <div class="dynamic-section-header">
                                <h6 class="dynamic-section-title"><i class="fas fa-project-diagram"></i> Flow Program</h6>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label-custom">Tipe Flow</label>
                                    <select class="form-select-custom" id="flow_type">
                                        <option value="text">Text</option>
                                        <option value="mermaid">Mermaid</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label-custom">Konten Flow</label>
                                    <textarea class="form-control-custom" id="flow_content" rows="4"
                                        style="font-family: 'JetBrains Mono', monospace;"></textarea>
                                </div>
                            </div>
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
                    </div>
                    <div class="modal-footer modal-footer-custom">
                        <button type="button" class="btn-secondary-custom" onclick="closeModal()">Batal</button>
                        <button type="submit" class="btn-primary-custom"><i class="fas fa-save"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let codeBlockCount = 0;
        let relationCount = 0;

        function openModal() {
            $('#codeDocModalTitle').html('<i class="fas fa-plus-circle"></i> Tambah Code Documentation');
            $('#codeDocForm')[0].reset();
            $('#codeDocId').val('');
            $('#codeBlocksContainer').empty();
            $('#relationsContainer').empty();
            codeBlockCount = 0;
            relationCount = 0;
            $('#codeDocModal').modal('show');
        }

        function closeModal() {
            $('#codeDocModal').modal('hide');
        }

        function addCodeBlock() {
            codeBlockCount++;
            const html = `
        <div class="code-block-form" id="codeBlock_${codeBlockCount}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label-custom mb-0">Deskripsi</label>
                <button type="button" class="btn-remove" onclick="removeCodeBlock(${codeBlockCount})"><i class="fas fa-trash"></i></button>
            </div>
            <textarea class="form-control-custom mb-2 code-block-description" rows="2" placeholder="Deskripsi kode..."></textarea>
            <div class="row">
                <div class="col-md-3 mb-2">
                    <select class="form-select-custom code-block-language">
                        <option value="php">PHP</option><option value="javascript">JavaScript</option><option value="sql">SQL</option><option value="html">HTML</option><option value="css">CSS</option>
                    </select>
                </div>
                <div class="col-md-9 mb-2">
                    <textarea class="form-control-custom code-block-code" rows="4" placeholder="Paste kode di sini..." style="font-family: 'JetBrains Mono', monospace;"></textarea>
                </div>
            </div>
        </div>`;
            $('#codeBlocksContainer').append(html);
        }

        function removeCodeBlock(id) {
            $(`#codeBlock_${id}`).remove();
        }

        function addRelation() {
            relationCount++;
            const html = `
        <div class="d-flex align-items-center gap-2 mb-2" id="relation_${relationCount}">
            <input type="text" class="form-control-custom relation-name" placeholder="Nama Model" style="width: 200px;">
            <button type="button" class="btn-remove" onclick="removeRelation(${relationCount})"><i class="fas fa-trash"></i></button>
        </div>`;
            $('#relationsContainer').append(html);
        }

        function removeRelation(id) {
            $(`#relation_${id}`).remove();
        }

        // ==========================================
        // FUNGSI BARU: VIEW DETAIL LENGKAP
        // ==========================================
        function viewDetailCodeDoc(id) {
            $('#detailModalBody').html(`
        <div class="text-center py-5">
            <div class="spinner-border" style="color: var(--primary-navy);" role="status"></div>
            <p class="mt-2" style="color: var(--text-secondary);">Memuat detail...</p>
        </div>
    `);
            $('#detailCodeDocModal').modal('show');

            $.ajax({
                url: `/system/documentation/codes/${id}`,
                method: 'GET',
                success: function(response) {
                    let html = '';

                    // Title
                    html +=
                        `<h4 style="color: var(--primary-navy); font-weight: 700; margin-bottom: 1.5rem;">${response.title}</h4>`;

                    // Description
                    if (response.description) {
                        html += `
                <div style="margin-bottom: 1.5rem;">
                    <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-info-circle" style="color: var(--primary-navy);"></i> Deskripsi Lengkap
                    </div>
                    <p style="color: var(--text-primary); line-height: 1.6; font-size: 0.95rem;">${response.description}</p>
                </div>`;
                    }

                    // Flow Program
                    if (response.flow_program) {
                        let flowContent = response.flow_program.content;
                        if (response.flow_program.type === 'mermaid') {
                            flowContent = `<div class="mermaid">${flowContent}</div>`;
                        } else {
                            flowContent =
                                `<pre style="margin: 0; white-space: pre-wrap; font-family: 'JetBrains Mono', monospace; font-size: 0.85rem;">${flowContent}</pre>`;
                        }
                        html += `
                <div style="margin-bottom: 1.5rem;">
                    <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-project-diagram" style="color: var(--primary-navy);"></i> Flow Program
                    </div>
                    <div style="background: #f8f9fa; border-left: 4px solid var(--primary-navy); padding: 1.5rem; border-radius: 8px; color: var(--text-primary);">
                        ${flowContent}
                    </div>
                </div>`;
                    }

                    // Code Blocks
                    if (response.code_blocks && response.code_blocks.length > 0) {
                        html += `
                <div style="margin-bottom: 1.5rem;">
                    <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-laptop-code" style="color: var(--primary-navy);"></i> Kode Implementasi (${response.code_blocks.length})
                    </div>`;

                        response.code_blocks.forEach((block) => {
                            let lang = block.language || 'php';
                            let desc = block.description ?
                                `<p style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 0.5rem; font-style: italic;">${block.description}</p>` :
                                '';
                            // Escape HTML for data attribute safely
                            let escapedCode = block.code.replace(/"/g, '&quot;').replace(/'/g, '&#39;');

                            html += `
                    <div style="margin-bottom: 1.5rem;">
                        ${desc}
                        <div style="position: relative; background: var(--code-bg); border-radius: 12px; overflow: hidden;">
                            <div style="background: #2d2d2d; padding: 0.75rem 1rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #3d3d3d;">
                                <span style="color: var(--primary-light-blue); font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">${lang}</span>
                                <button class="btn btn-sm btn-outline-light" style="font-size: 0.75rem;" data-code="${block.code}" onclick="copyCode(this)">
                                    <i class="fas fa-copy"></i> Copy
                                </button>
                            </div>
                            <div style="padding: 1.5rem; overflow-x: auto;">
                                <pre style="margin: 0;"><code class="language-${lang}" style="font-size: 0.85rem;">${block.code}</code></pre>
                            </div>
                        </div>
                    </div>`;
                        });
                        html += `</div>`;
                    }

                    // Relations
                    if (response.relations && response.relations.length > 0) {
                        html += `
                <div style="margin-bottom: 1.5rem;">
                    <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-link" style="color: var(--primary-navy);"></i> Relasi Model
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">`;

                        response.relations.forEach(rel => {
                            html += `
                    <span style="background: linear-gradient(135deg, var(--primary-light-blue), #7fb3d5); color: var(--primary-navy); padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-database"></i> ${rel}
                    </span>`;
                        });
                        html += `</div></div>`;
                    }

                    // Change Logs
                    if (response.change_logs && response.change_logs.length > 0) {
                        html +=
                        `
                <div style="margin-bottom: 1.5rem;">
                    <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-history" style="color: var(--primary-navy);"></i> Change Log
                    </div>
                    <div style="position: relative; padding-left: 1.5rem; border-left: 2px solid var(--border-soft);">`;

                        response.change_logs.forEach(log => {
                            html += `
                    <div style="position: relative; margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-soft);">
                        <div style="position: absolute; left: -1.95rem; top: 0.25rem; width: 10px; height: 10px; border-radius: 50%; background: var(--primary-navy); border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"></div>
                        <div style="font-weight: 700; color: var(--primary-navy); font-size: 0.95rem;">Version ${log.version}</div>
                        <div style="color: var(--text-secondary); font-size: 0.8rem; margin-bottom: 0.5rem;">${log.date || '-'}</div>
                        ${log.summary ? `<div style="color: var(--text-primary); font-size: 0.9rem; margin-bottom: 0.25rem;">${log.summary}</div>` : ''}
                        ${log.details ? `<div style="color: var(--text-secondary); font-size: 0.85rem;">${log.details}</div>` : ''}
                    </div>`;
                        });
                        html += `</div></div>`;
                    }

                    // Future Development
                    if (response.future_development && response.future_development.length > 0) {
                        html += `
                <div>
                    <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-lightbulb" style="color: var(--primary-navy);"></i> Future Development
                    </div>
                    <ul style="list-style: none; padding: 0; margin: 0;">`;

                        response.future_development.forEach(item => {
                            html += `
                    <li style="padding: 0.75rem 1rem; background: #f8f9fa; border-radius: 8px; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.75rem; color: var(--text-primary); font-size: 0.9rem;">
                        <i class="fas fa-check-circle" style="color: #48bb78;"></i> ${item}
                    </li>`;
                        });
                        html += `</ul></div>`;
                    }

                    $('#detailModalBody').html(html);

                    // Re-initialize Prism and Mermaid for the new injected content
                    if (typeof Prism !== 'undefined') {
                        Prism.highlightAllUnder(document.getElementById('detailModalBody'));
                    }
                    if (typeof mermaid !== 'undefined') {
                        mermaid.init(undefined, document.querySelectorAll('#detailModalBody .mermaid'));
                    }
                },
                error: function() {
                    $('#detailModalBody').html(`
                <div class="text-center py-5 text-danger">
                    <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                    <p>Gagal memuat detail dokumentasi.</p>
                </div>
            `);
                }
            });
        }

        function editCodeDoc(id) {
            $.ajax({
                url: `/system/documentation/codes/${id}`,
                method: 'GET',
                success: function(response) {
                    $('#codeDocModalTitle').html('<i class="fas fa-edit"></i> Edit Code Documentation');
                    $('#codeDocId').val(response.id);
                    $('#title').val(response.title);
                    $('#description').val(response.description || '');
                    if (response.flow_program) {
                        $('#flow_type').val(response.flow_program.type);
                        $('#flow_content').val(response.flow_program.content);
                    }

                    $('#codeBlocksContainer').empty();
                    $('#relationsContainer').empty();
                    codeBlockCount = 0;
                    relationCount = 0;

                    if (response.code_blocks && response.code_blocks.length > 0) {
                        response.code_blocks.forEach(block => {
                            codeBlockCount++;
                            const html = `
                        <div class="code-block-form" id="codeBlock_${codeBlockCount}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label-custom mb-0">Deskripsi</label>
                                <button type="button" class="btn-remove" onclick="removeCodeBlock(${codeBlockCount})"><i class="fas fa-trash"></i></button>
                            </div>
                            <textarea class="form-control-custom mb-2 code-block-description" rows="2">${block.description || ''}</textarea>
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <select class="form-select-custom code-block-language">
                                        <option value="php" ${block.language === 'php' ? 'selected' : ''}>PHP</option>
                                        <option value="javascript" ${block.language === 'javascript' ? 'selected' : ''}>JavaScript</option>
                                        <option value="sql" ${block.language === 'sql' ? 'selected' : ''}>SQL</option>
                                        <option value="html" ${block.language === 'html' ? 'selected' : ''}>HTML</option>
                                        <option value="css" ${block.language === 'css' ? 'selected' : ''}>CSS</option>
                                    </select>
                                </div>
                                <div class="col-md-9 mb-2">
                                    <textarea class="form-control-custom code-block-code" rows="4" style="font-family: 'JetBrains Mono', monospace;">${block.code}</textarea>
                                </div>
                            </div>
                        </div>`;
                            $('#codeBlocksContainer').append(html);
                        });
                    }

                    if (response.relations && response.relations.length > 0) {
                        response.relations.forEach(rel => {
                            relationCount++;
                            const html = `
                        <div class="d-flex align-items-center gap-2 mb-2" id="relation_${relationCount}">
                            <input type="text" class="form-control-custom relation-name" value="${rel}" style="width: 200px;">
                            <button type="button" class="btn-remove" onclick="removeRelation(${relationCount})"><i class="fas fa-trash"></i></button>
                        </div>`;
                            $('#relationsContainer').append(html);
                        });
                    }

                    $('#codeDocModal').modal('show');
                }
            });
        }

        function deleteCodeDoc(id) {
            if (confirm('Apakah Anda yakin ingin menghapus dokumentasi kode ini?')) {
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
                const originalHtml = $(btn).html();
                $(btn).html('<i class="fas fa-check text-success"></i> Copied!');
                setTimeout(() => {
                    $(btn).html(originalHtml);
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

            const formData = {
                _token: '{{ csrf_token() }}',
                title: $('#title').val(),
                description: $('#description').val(),
                flow_type: $('#flow_type').val(),
                flow_content: $('#flow_content').val(),
                code_blocks: codeBlocks,
                relations: relations
            };

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) location.reload();
                }
            });
        });
    </script>
@endpush
