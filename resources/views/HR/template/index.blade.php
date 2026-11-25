@extends('layout_HR.app')

@section('content_HR')
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        .modal {
            --bs-modal-zindex: 1100;
        }

        .modal-backdrop {
            --bs-backdrop-zindex: 1090;
            --bs-backdrop-bg: #0f172a;
            --bs-backdrop-opacity: .5;
        }

        .toast-container {
            z-index: 1200 !important;
        }

        .tpl-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .tpl-head h4 {
            margin: 0;
            font-weight: 700;
            color: #0f172a;
        }

        .tpl-head p {
            margin: .15rem 0 0;
            font-size: .85rem;
            color: #64748b;
        }

        .tpl-tools {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .tpl-search {
            position: relative;
            width: 240px;
        }

        .tpl-search .iconify {
            position: absolute;
            left: .7rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .tpl-search input {
            padding-left: 2.1rem;
            border-radius: 8px;
            font-size: .875rem;
        }

        .tpl-section {
            margin-bottom: 2rem;
        }

        .tpl-section-title {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin: 0 0 .85rem;
            font-size: .95rem;
            font-weight: 600;
            color: #334155;
        }

        .tpl-count {
            font-size: .75rem;
            font-weight: 500;
            color: #64748b;
            background: #f1f5f9;
            border-radius: 999px;
            padding: .05rem .55rem;
        }

        .tpl-card {
            display: flex;
            flex-direction: column;
            height: 100%;
            padding: 1rem;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            transition: border-color .15s ease;
        }

        .tpl-card:hover {
            border-color: #a5b4fc;
        }

        .tpl-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: #eef2ff;
            color: #4f46e5;
            font-size: 1.35rem;
            margin-bottom: .85rem;
        }

        .tpl-name {
            margin: 0 0 .25rem;
            font-size: .95rem;
            font-weight: 600;
            color: #0f172a;
        }

        .tpl-desc {
            margin: 0 0 1rem;
            font-size: .8rem;
            line-height: 1.5;
            color: #64748b;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .tpl-actions {
            display: flex;
            gap: .4rem;
            margin-top: auto;
        }

        .tpl-actions .btn {
            border-radius: 8px;
        }

        .tpl-empty {
            text-align: center;
            padding: 3.5rem 1rem;
            color: #64748b;
        }

        .tpl-empty img {
            width: 140px;
            max-width: 40%;
            margin-bottom: 1rem;
        }

        .tpl-empty h6 {
            color: #0f172a;
            font-weight: 600;
        }

        .docx-container-preview {
            background: #e9ecef;
            padding: 12px;
            min-height: 100%;
            overflow: auto;
        }

        .docx-container-preview .docx-wrapper {
            background: transparent !important;
            padding: 0 !important;
        }

        .docx-container-preview .docx-wrapper>section.docx {
            zoom: .8;
            margin-bottom: 12px !important;
            background: #fff;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .12);
        }

        .history-list-pane {
            width: 100%;
            background: #f8fafc;
            transition: width .25s ease;
        }

        .history-list-pane.with-side {
            width: 42%;
            min-width: 340px;
        }

        .history-side-pane {
            width: 58%;
            background: #fff;
            display: none;
            flex-direction: column;
            border-left: 1px solid #e2e8f0;
        }

        .history-side-pane.is-open {
            display: flex;
        }

        .history-folder {
            display: flex;
            align-items: center;
            gap: .75rem;
            width: 100%;
            padding: .85rem 1rem;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            text-align: left;
            transition: border-color .15s ease;
        }

        .history-folder:hover {
            border-color: #a5b4fc;
        }

        .history-folder .iconify {
            font-size: 1.6rem;
            color: #f59e0b;
            flex-shrink: 0;
        }

        .history-folder strong {
            display: block;
            font-size: .9rem;
            color: #0f172a;
        }

        .history-folder small {
            color: #64748b;
        }

        .history-row-actions {
            display: inline-flex;
            gap: .15rem;
        }

        .history-row-actions .btn {
            padding: .2rem .4rem;
            border: 0;
            color: #64748b;
        }

        .history-row-actions .btn:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .history-row-actions .btn.danger:hover {
            background: #fee2e2;
            color: #dc2626;
        }

        @media (max-width: 992px) {
            .history-list-pane.with-side {
                width: 100%;
                min-width: 0;
            }

            .history-side-pane {
                position: absolute;
                inset: 0;
                width: 100%;
                z-index: 5;
            }

            .tpl-search {
                width: 100%;
            }
        }
    </style>

    <div class="container-fluid">
        <div class="tpl-head">
            <div>
                <h4>Template Laporan</h4>
                <p>Kelola template dokumen dan buat laporan dari data karyawan atau rekrutan.</p>
            </div>
            <div class="tpl-tools">
                <div class="tpl-search">
                    <span class="iconify" data-icon="mdi:magnify"></span>
                    <input type="search" id="tplSearch" class="form-control form-control-sm" placeholder="Cari template">
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnOpenHistory">
                    <span class="iconify me-1" data-icon="mdi:history"></span>Riwayat
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="btnOpenCreateModal">
                    <span class="iconify me-1" data-icon="mdi:plus"></span>Template baru
                </button>
            </div>
        </div>

        @if ($templates->isNotEmpty())
            @foreach ($templates as $category => $items)
                <section class="tpl-section" data-section>
                    <h6 class="tpl-section-title">
                        {{ ucfirst($category ?: 'Lainnya') }}
                        <span class="tpl-count">{{ $items->count() }}</span>
                    </h6>
                    <div class="row g-3">
                        @foreach ($items as $tpl)
                            <div class="col-md-6 col-xl-4 col-xxl-3" data-item
                                data-search="{{ strtolower($tpl->name . ' ' . $tpl->code . ' ' . $category . ' ' . $tpl->description) }}">
                                <div class="tpl-card">
                                    <div class="tpl-icon">
                                        <span class="iconify" data-icon="mdi:file-document-outline"></span>
                                    </div>
                                    <h6 class="tpl-name">{{ $tpl->name }}</h6>
                                    <p class="tpl-desc">{{ $tpl->description ?: 'Tidak ada deskripsi' }}</p>
                                    <div class="tpl-actions">
                                        <button type="button" class="btn btn-sm btn-primary flex-grow-1"
                                            data-tpl-act="generate" data-id="{{ $tpl->id }}"
                                            data-name="{{ $tpl->name }}">
                                            <span class="iconify me-1" data-icon="mdi:file-document-check-outline"></span>Buat laporan
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" title="Edit"
                                            data-tpl-act="edit" data-id="{{ $tpl->id }}">
                                            <span class="iconify" data-icon="mdi:pencil-outline"></span>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus"
                                            data-tpl-act="delete" data-id="{{ $tpl->id }}"
                                            data-name="{{ $tpl->name }}">
                                            <span class="iconify" data-icon="mdi:delete-outline"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach

            <div class="tpl-empty d-none" id="tplNoResult">
                <h6>Template tidak ditemukan</h6>
                <p class="mb-0 small">Coba kata kunci lain.</p>
            </div>
        @else
            <div class="tpl-empty">
                <img src="{{ asset('svgundraw/emptydata.svg') }}" alt="">
                <h6>Belum ada template</h6>
                <p class="small">Unggah dokumen Word untuk membuat template pertama.</p>
                <button type="button" class="btn btn-primary btn-sm" id="btnOpenCreateModalEmpty">Template baru</button>
            </div>
        @endif
    </div>

    <div class="modal fade" id="generateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title" id="generateModalTitle">Buat laporan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-0" id="generateModalBody" style="min-height: 50vh;"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title">Pratinjau dokumen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-0" style="height: 75vh; overflow: auto;">
                    <div id="previewLoading" class="text-center py-5">
                        <div class="spinner-border text-primary"></div>
                        <p class="mt-3 text-muted">Membuat pratinjau...</p>
                    </div>
                    <div id="previewContent" style="display: none; min-height: 100%;"></div>
                </div>
                <div class="modal-footer">
                    <small class="text-muted me-auto">Hanya pratinjau, belum disimpan.</small>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen-lg-down modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="height: 88vh;">
                <div class="modal-header">
                    <h5 class="modal-title">Riwayat laporan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-0 d-flex overflow-hidden position-relative">
                    <div id="historyListPane" class="history-list-pane d-flex flex-column">
                        <div class="px-3 py-2 border-bottom bg-white d-flex justify-content-between align-items-center">
                            <div id="historyBreadcrumb" class="small fw-semibold text-dark">Semua folder</div>
                            <button class="btn btn-sm btn-outline-secondary d-none" id="btnBackToFolders" type="button">
                                <span class="iconify me-1" data-icon="mdi:arrow-left"></span>Kembali
                            </button>
                        </div>
                        <div class="p-3 overflow-auto flex-grow-1" id="historyContentArea"></div>
                    </div>

                    <div id="historySidePane" class="history-side-pane">
                        <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 text-truncate" id="sidePaneTitle">Detail</h6>
                            <button type="button" class="btn-close" id="btnCloseSidePane" aria-label="Tutup"></button>
                        </div>
                        <div class="flex-grow-1 overflow-auto p-3" id="sidePaneBody"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://unpkg.com/docx-preview@0.3.0/dist/docx-preview.min.js"></script>

    @include('HR.template.partials.smart-creator')

    <script>
        (function() {
            ['generateModal', 'previewModal', 'historyModal'].forEach(function(id) {
                const el = document.getElementById(id);
                if (el) document.body.appendChild(el);
            });

            document.addEventListener('show.bs.modal', function(e) {
                const open = document.querySelectorAll('.modal.show').length;
                const z = 1100 + open * 20;
                e.target.style.zIndex = z;
                requestAnimationFrame(function() {
                    const fresh = Array.from(document.querySelectorAll('.modal-backdrop')).filter(function(b) {
                        return !b.dataset.stacked;
                    });
                    const bd = fresh[fresh.length - 1];
                    if (bd) {
                        bd.style.zIndex = z - 10;
                        bd.dataset.stacked = '1';
                    }
                });
            });

            document.addEventListener('hidden.bs.modal', function() {
                if (document.querySelector('.modal.show')) return;
                document.querySelectorAll('.modal-backdrop').forEach(function(b) {
                    b.remove();
                });
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('padding-right');
                const overlay = document.getElementById('smOverlay');
                if (overlay && overlay.classList.contains('is-open')) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.removeProperty('overflow');
                }
            });
        })();

        const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        } [c]));

        const csrf = () => document.querySelector('meta[name="csrf-token"]').content;
        const modalOf = id => bootstrap.Modal.getOrCreateInstance(document.getElementById(id));

        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer') || createToastContainer();
            const bg = type === 'error' ? 'bg-danger' : 'bg-success';
            const icon = type === 'error' ? 'mdi:alert-circle' : 'mdi:check-circle';

            container.insertAdjacentHTML('beforeend', `
                <div class="toast align-items-center text-white border-0 ${bg}" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">
                            <span class="iconify me-2" data-icon="${icon}"></span>${esc(message)}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>`);

            const el = container.lastElementChild;
            new bootstrap.Toast(el, {
                delay: 4000
            }).show();
            el.addEventListener('hidden.bs.toast', () => el.remove());
        }

        function createToastContainer() {
            const c = document.createElement('div');
            c.id = 'toastContainer';
            c.className = 'toast-container position-fixed top-0 end-0 p-3';
            document.body.appendChild(c);
            return c;
        }

        document.getElementById('btnOpenHistory').addEventListener('click', openHistoryModal);

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('[data-tpl-act]');
            if (!btn) return;
            const id = btn.dataset.id;
            if (btn.dataset.tplAct === 'generate') openGenerateModal(id, btn.dataset.name);
            if (btn.dataset.tplAct === 'edit') window.openEditOverlay(id);
            if (btn.dataset.tplAct === 'delete') confirmDeleteTemplate(id, btn.dataset.name);
        });

        const searchInput = document.getElementById('tplSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const q = this.value.trim().toLowerCase();
                let total = 0;
                document.querySelectorAll('[data-section]').forEach(function(section) {
                    let visible = 0;
                    section.querySelectorAll('[data-item]').forEach(function(item) {
                        const match = !q || item.dataset.search.includes(q);
                        item.classList.toggle('d-none', !match);
                        if (match) visible++;
                    });
                    section.classList.toggle('d-none', visible === 0);
                    total += visible;
                });
                document.getElementById('tplNoResult')?.classList.toggle('d-none', total > 0);
            });
        }

        function confirmDeleteTemplate(templateId, templateName) {
            document.getElementById('deleteConfirmModal')?.remove();

            document.body.insertAdjacentHTML('beforeend', `
                <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header">
                                <h5 class="modal-title">Hapus template</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-1">Template berikut akan dihapus:</p>
                                <h6 class="fw-bold text-danger">${esc(templateName)}</h6>
                                <div class="alert alert-warning small mt-3 mb-0">
                                    Semua placeholder dan konfigurasi terkait ikut terhapus permanen.
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                <button type="button" class="btn btn-danger" id="btnConfirmDelete">Hapus template</button>
                            </div>
                        </div>
                    </div>
                </div>`);

            const el = document.getElementById('deleteConfirmModal');
            el.addEventListener('hidden.bs.modal', () => el.remove());
            document.getElementById('btnConfirmDelete').addEventListener('click', () => executeDeleteTemplate(templateId, false));
            modalOf('deleteConfirmModal').show();
        }

        function executeDeleteTemplate(templateId, force) {
            const btn = document.getElementById('btnConfirmDelete');
            const original = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menghapus';

            fetch(`/HR-dashboard/reports/delete/${templateId}` + (force ? '?force=1' : ''), {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf(),
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        modalOf('deleteConfirmModal').hide();
                        showToast(data.message, 'success');
                        setTimeout(() => window.location.reload(), 900);
                    } else if (data.has_history) {
                        showForceDeletePrompt(templateId, data.history_count);
                    } else {
                        showToast(data.message || 'Gagal menghapus template', 'error');
                        btn.disabled = false;
                        btn.innerHTML = original;
                    }
                })
                .catch(() => {
                    showToast('Terjadi kesalahan koneksi', 'error');
                    btn.disabled = false;
                    btn.innerHTML = original;
                });
        }

        function showForceDeletePrompt(templateId, historyCount) {
            const modalEl = document.getElementById('deleteConfirmModal');
            modalEl.querySelector('.modal-body').innerHTML = `
                <p class="mb-2">Template ini sudah dipakai untuk membuat laporan sebanyak <strong>${esc(historyCount)} kali</strong>.</p>
                <div class="alert alert-danger small mb-0">
                    Template, seluruh riwayat, dan file laporan hasil generate akan dihapus permanen.
                </div>`;
            modalEl.querySelector('.modal-footer').innerHTML = `
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="btnConfirmDelete">Hapus template dan riwayat</button>`;
            document.getElementById('btnConfirmDelete').addEventListener('click', () => executeDeleteTemplate(templateId, true));
        }

        async function openGenerateModal(templateId, templateName) {
            const body = document.getElementById('generateModalBody');
            document.getElementById('generateModalTitle').textContent = 'Buat laporan: ' + templateName;
            body.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-3 text-muted">Memuat form...</p>
                </div>`;

            modalOf('generateModal').show();

            try {
                const res = await fetch(`/HR-dashboard/reports/${templateId}/generate-modal`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                });
                if (!res.ok) throw new Error('Gagal memuat form');
                body.innerHTML = await res.text();
                initGenerateForm();
            } catch (err) {
                body.innerHTML = `<div class="alert alert-danger m-4">Gagal memuat form.<br><small>${esc(err.message)}</small></div>`;
            }
        }

        function initGenerateForm() {
            const form = document.getElementById('generateForm');
            if (!form) return;

            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                const source = form.querySelector('[name="source_id"]');
                if (!source || !source.value) {
                    showToast('Pilih data sumber terlebih dahulu.', 'error');
                    return;
                }

                const btn = document.getElementById('btnGenerate');
                const text = btn?.querySelector('.btn-text');
                const spinner = document.getElementById('loadingSpinner');
                if (btn) btn.disabled = true;
                text?.classList.add('d-none');
                spinner?.classList.remove('d-none');

                try {
                    const res = await fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html,application/xhtml+xml'
                        }
                    });

                    if (res.redirected) {
                        if (new URL(res.url).pathname === window.location.pathname) {
                            throw new Error('Gagal membuat laporan. Periksa isian lalu coba lagi.');
                        }
                        window.location.href = res.url;
                        modalOf('generateModal').hide();
                        return;
                    }

                    const type = res.headers.get('content-type') || '';

                    if (type.includes('text/html')) {
                        const html = await res.text();
                        const w = window.open('', '_blank');
                        if (w) {
                            w.document.write(html);
                            w.document.close();
                        }
                    } else if (res.ok) {
                        const blob = await res.blob();
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'laporan.docx';
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        URL.revokeObjectURL(url);
                    } else {
                        throw new Error('Gagal membuat laporan');
                    }

                    modalOf('generateModal').hide();
                } catch (err) {
                    showToast(err.message, 'error');
                } finally {
                    if (btn) btn.disabled = false;
                    text?.classList.remove('d-none');
                    spinner?.classList.add('d-none');
                }
            });
        }

        function reindexLoop(table) {
            table.querySelectorAll('tbody tr').forEach(function(tr, i) {
                tr.dataset.rowIndex = i;
                const num = tr.querySelector('td');
                if (num) num.textContent = i + 1;
                tr.querySelectorAll('input, select, textarea').forEach(function(input) {
                    input.name = input.name.replace(/^(manual_inputs\[[^\]]+\])\[\d+\]/, `$1[${i}]`);
                });
            });
        }

        window.addLoopRow = function(btn) {
            const table = btn.closest('.section-card').querySelector('table[data-loop-key]');
            const body = table.querySelector('tbody');
            const clone = body.querySelector('tr').cloneNode(true);
            clone.querySelectorAll('input, select, textarea').forEach(function(input) {
                input.value = '';
            });
            body.appendChild(clone);
            reindexLoop(table);
            clone.querySelector('input')?.focus();
        };

        window.removeLoopRow = function(btn) {
            const table = btn.closest('table');
            const rows = table.querySelectorAll('tbody tr');
            if (rows.length <= 1) {
                rows[0].querySelectorAll('input, select, textarea').forEach(function(input) {
                    input.value = '';
                });
                return;
            }
            btn.closest('tr').remove();
            reindexLoop(table);
        };

        window.doPreview = function() {
            const form = document.getElementById('generateForm');
            if (!form) return;

            const source = form.querySelector('[name="source_id"]');
            if (!source || !source.value) {
                showToast('Pilih data sumber terlebih dahulu.', 'error');
                return;
            }

            const btn = document.getElementById('btnPreview');
            const btnText = document.getElementById('previewBtnText');
            const spinner = document.getElementById('previewSpinner');
            if (btn) btn.disabled = true;
            if (btnText) btnText.textContent = 'Memproses';
            spinner?.classList.remove('d-none');

            fetch(form.dataset.previewUrl, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/octet-stream, application/json'
                    }
                })
                .then(async res => {
                    const type = res.headers.get('content-type') || '';
                    if (type.includes('application/json')) {
                        const data = await res.json();
                        throw new Error(data.message || 'Gagal membuat pratinjau');
                    }
                    if (!res.ok) throw new Error('Gagal membuat pratinjau (status ' + res.status + ')');
                    return res.arrayBuffer();
                })
                .then(buffer => {
                    const loading = document.getElementById('previewLoading');
                    const content = document.getElementById('previewContent');
                    loading.style.display = 'block';
                    content.style.display = 'none';
                    content.innerHTML = '';

                    modalOf('previewModal').show();

                    const holder = document.createElement('div');
                    holder.className = 'docx-container-preview';
                    content.appendChild(holder);

                    return window.docx.renderAsync(buffer, holder, null, {
                        className: 'docx',
                        inWrapper: true,
                        breakPages: true
                    }).then(() => {
                        loading.style.display = 'none';
                        content.style.display = 'block';
                    });
                })
                .catch(err => showToast(err.message, 'error'))
                .finally(() => {
                    if (btn) btn.disabled = false;
                    if (btnText) btnText.textContent = 'Pratinjau';
                    spinner?.classList.add('d-none');
                });
        };

        let historyData = {};
        let historyIndex = {};
        let currentHistoryFolder = null;

        function openHistoryModal() {
            modalOf('historyModal').show();
            loadHistoryData(null);
        }

        function loadHistoryData(keepFolder) {
            const area = document.getElementById('historyContentArea');
            area.innerHTML = `
                <div class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm"></div>
                    <span class="ms-2">Memuat riwayat...</span>
                </div>`;

            return fetch('{{ route('HR.reports.history.data') }}?_=' + Date.now(), {
                    cache: 'no-store',
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(res => {
                    historyData = {};
                    historyIndex = {};

                    (res.success && res.data ? res.data : []).forEach(item => {
                        const key = String(item.template_id || 'unknown');
                        if (!historyData[key]) {
                            historyData[key] = {
                                name: item.template_name || 'Template',
                                files: []
                            };
                        }
                        historyData[key].files.push(item);
                        historyIndex[item.id] = item;
                    });

                    if (keepFolder && historyData[keepFolder]) {
                        openHistoryFolder(keepFolder);
                    } else {
                        renderHistoryFolders();
                    }
                })
                .catch(() => {
                    area.innerHTML = '<div class="text-center py-5 text-danger">Gagal memuat data</div>';
                });
        }

        function renderHistoryFolders() {
            currentHistoryFolder = null;
            document.getElementById('btnBackToFolders').classList.add('d-none');
            document.getElementById('historyBreadcrumb').textContent = 'Semua folder';

            const keys = Object.keys(historyData);
            let html;

            if (!keys.length) {
                html = '<div class="text-center py-5 text-muted">Belum ada riwayat laporan.</div>';
            } else {
                html = '<div class="row g-2">' + keys.map(key => `
                    <div class="col-sm-6 col-xl-4">
                        <button type="button" class="history-folder" data-folder="${esc(key)}">
                            <span class="iconify" data-icon="mdi:folder"></span>
                            <span class="text-truncate">
                                <strong class="text-truncate">${esc(historyData[key].name)}</strong>
                                <small>${historyData[key].files.length} file</small>
                            </span>
                        </button>
                    </div>`).join('') + '</div>';
            }

            document.getElementById('historyContentArea').innerHTML = html;
            closeHistorySidePane();
        }

        function openHistoryFolder(key) {
            const folder = historyData[key];
            if (!folder) return;
            currentHistoryFolder = key;

            document.getElementById('btnBackToFolders').classList.remove('d-none');
            document.getElementById('historyBreadcrumb').textContent = folder.name;

            const statusMap = {
                completed: ['bg-success-subtle text-success', 'Sukses'],
                failed: ['bg-danger-subtle text-danger', 'Gagal'],
            };

            const rows = folder.files.map(item => {
                const [badge, label] = statusMap[item.status] || ['bg-warning-subtle text-warning', 'Pending'];
                return `
                    <tr>
                        <td class="small text-nowrap">${esc(item.created_at)}</td>
                        <td><div class="text-truncate" style="max-width:200px" title="${esc(item.report_title)}">${esc(item.report_title)}</div></td>
                        <td><span class="badge ${badge}">${label}</span></td>
                        <td class="text-end">
                            <div class="history-row-actions">
                                <button type="button" class="btn" title="Pratinjau" data-h-act="preview" data-id="${item.id}"><span class="iconify" data-icon="mdi:eye-outline"></span></button>
                                <button type="button" class="btn" title="Ubah judul" data-h-act="edit" data-id="${item.id}"><span class="iconify" data-icon="mdi:pencil-outline"></span></button>
                                <button type="button" class="btn danger" title="Hapus" data-h-act="delete" data-id="${item.id}"><span class="iconify" data-icon="mdi:delete-outline"></span></button>
                            </div>
                        </td>
                    </tr>`;
            }).join('');

            document.getElementById('historyContentArea').innerHTML = `
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 bg-white">
                        <thead class="table-light">
                            <tr><th>Tanggal</th><th>Judul</th><th>Status</th><th></th></tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>`;
        }

        document.getElementById('btnBackToFolders').addEventListener('click', renderHistoryFolders);
        document.getElementById('btnCloseSidePane').addEventListener('click', closeHistorySidePane);

        document.getElementById('historyContentArea').addEventListener('click', function(e) {
            const folder = e.target.closest('[data-folder]');
            if (folder) return openHistoryFolder(folder.dataset.folder);

            const btn = e.target.closest('[data-h-act]');
            if (!btn) return;
            const item = historyIndex[btn.dataset.id];
            if (!item) return;

            if (btn.dataset.hAct === 'preview') showHistoryPreview(item);
            if (btn.dataset.hAct === 'edit') showHistoryEdit(item);
            if (btn.dataset.hAct === 'delete') showHistoryDelete(item);
        });

        function openHistorySidePane(title) {
            document.getElementById('sidePaneTitle').textContent = title;
            document.getElementById('historySidePane').classList.add('is-open');
            document.getElementById('historyListPane').classList.add('with-side');
        }

        function closeHistorySidePane() {
            document.getElementById('historySidePane').classList.remove('is-open');
            document.getElementById('historyListPane').classList.remove('with-side');
            document.getElementById('sidePaneBody').innerHTML = '';
        }

        function showHistoryPreview(item) {
            openHistorySidePane('Pratinjau: ' + item.report_title);
            const body = document.getElementById('sidePaneBody');
            const ext = (item.file_extension || '').toUpperCase();

            body.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-3 text-muted">Memuat pratinjau...</p>
                </div>`;

            fetch(item.download_url)
                .then(r => {
                    if (!r.ok) throw new Error('Gagal mengambil file');
                    return r.arrayBuffer();
                })
                .then(buffer => {
                    body.innerHTML = '';

                    if (ext !== 'DOCX' && ext !== 'PDF') {
                        body.innerHTML = '<div class="alert alert-warning">Format belum didukung untuk pratinjau.</div>';
                        return;
                    }

                    const holder = document.createElement('div');
                    holder.className = 'docx-container-preview';
                    body.appendChild(holder);

                    if (ext === 'DOCX') {
                        window.docx.renderAsync(buffer, holder, null, {
                            className: 'docx',
                            inWrapper: true,
                            breakPages: true
                        });
                    } else {
                        const url = URL.createObjectURL(new Blob([buffer], {
                            type: 'application/pdf'
                        }));
                        holder.innerHTML = `<iframe src="${url}" style="width:100%;height:70vh;border:none;"></iframe>`;
                    }

                    body.insertAdjacentHTML('beforeend', `
                        <div class="mt-3 text-end">
                            <a href="${esc(item.download_url)}" class="btn btn-primary btn-sm" target="_blank">
                                <span class="iconify me-1" data-icon="mdi:download"></span>Unduh
                            </a>
                        </div>`);
                })
                .catch(err => {
                    body.innerHTML = `<div class="alert alert-danger">Gagal memuat pratinjau: ${esc(err.message)}</div>`;
                });
        }

        function showHistoryEdit(item) {
            openHistorySidePane('Ubah judul');
            document.getElementById('sidePaneBody').innerHTML = `
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Judul laporan</label>
                    <input type="text" id="sideEditTitle" class="form-control" maxlength="255" value="${esc(item.report_title)}">
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary" id="btnSaveHistoryEdit">Simpan perubahan</button>
                    <button type="button" class="btn btn-light" id="btnCancelSide">Batal</button>
                </div>`;

            document.getElementById('btnCancelSide').addEventListener('click', closeHistorySidePane);
            document.getElementById('btnSaveHistoryEdit').addEventListener('click', () => saveHistoryEdit(item.id));
            document.getElementById('sideEditTitle').focus();
        }

        function saveHistoryEdit(id) {
            const title = document.getElementById('sideEditTitle').value.trim();
            if (!title) return showToast('Judul wajib diisi.', 'error');

            const btn = document.getElementById('btnSaveHistoryEdit');
            btn.disabled = true;

            fetch(`/HR-dashboard/reports/history/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        report_title: title
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (!data.success) {
                        const detail = data.errors ? Object.values(data.errors).flat().join(' ') : '';
                        throw new Error(detail || data.message || 'Gagal menyimpan');
                    }
                    showToast(data.message, 'success');
                    closeHistorySidePane();
                    return loadHistoryData(currentHistoryFolder);
                })
                .catch(err => {
                    showToast(err.message, 'error');
                    btn.disabled = false;
                });
        }

        function showHistoryDelete(item) {
            openHistorySidePane('Hapus riwayat');
            document.getElementById('sidePaneBody').innerHTML = `
                <p class="mb-1">Riwayat berikut akan dihapus:</p>
                <h6 class="text-danger">${esc(item.report_title)}</h6>
                <div class="alert alert-warning small mt-3">File laporan juga ikut terhapus permanen.</div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-danger" id="btnConfirmHistoryDelete">Hapus riwayat</button>
                    <button type="button" class="btn btn-light" id="btnCancelSide">Batal</button>
                </div>`;

            document.getElementById('btnCancelSide').addEventListener('click', closeHistorySidePane);
            document.getElementById('btnConfirmHistoryDelete').addEventListener('click', () => confirmHistoryDelete(item.id));
        }

        function confirmHistoryDelete(id) {
            const btn = document.getElementById('btnConfirmHistoryDelete');
            btn.disabled = true;

            fetch(`/HR-dashboard/reports/history/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrf(),
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (!data.success) throw new Error(data.message || 'Gagal menghapus');
                    showToast(data.message, 'success');
                    closeHistorySidePane();
                    return loadHistoryData(currentHistoryFolder);
                })
                .catch(err => {
                    showToast(err.message, 'error');
                    btn.disabled = false;
                });
        }
    </script>
@endsection