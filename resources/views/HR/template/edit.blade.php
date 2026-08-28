@extends('layout_HR.app')

@section('content_HR')
    <style>
        #docx-container {
            border: 1px solid #dee2e6;
            min-height: 500px;
            background: #f8f9fa;
            overflow: auto;
            padding: 20px;
            cursor: text;
            user-select: text;
        }

        .docx-wrapper>section.docx {
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            background: white;
            padding: 40px;
        }

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
        .text-mapped-manual_checkbox {
            background-color: #e7f1ff !important;
            color: #0a58ca;
            border-bottom: 2px solid #6ea8fe;
        }

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
        .placeholder-badge-manual_checkbox {
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

        /* Hilangkan background gelap dari docx-preview */
        .docx-wrapper {
            background: transparent !important;
        }

        .docx-wrapper>section.docx {
            background: white !important;
            box-shadow: none !important;
        }

        /* Pastikan container bersih */
        #docx-container {
            background: white !important;
        }

        /* Hilangkan overlay gelap jika ada */
        .modal-backdrop {
            display: none !important;
        }

        /* Pastikan preview area bersih */
        .docx-container-preview,
        #docx-container {
            background-color: #ffffff !important;
        }
    </style>

    <div class="container-fluid">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('HR.reports.index') }}">Report Generator</a></li>
                {{--  UBAH: Judul breadcrumb jadi Edit --}}
                <li class="breadcrumb-item active">Edit Template: {{ $template->name }}</li>
            </ol>
        </nav>

        {{--  UBAH: Alert info jadi panduan edit --}}
        <div class="alert alert-warning mb-4">
            <strong>Cara Edit Template:</strong>
            <ol class="mb-0 mt-1">
                <li>Dokumen template sudah ter-load otomatis di preview</li>
                <li>Mapping yang ada sudah muncul di panel kanan</li>
                <li><strong>Opsional:</strong> Upload file DOCX baru jika ingin mengganti dokumen</li>
                <li>Edit nama/kode/kategori atau tambah mapping baru, lalu klik <strong>Update Template</strong></li>
            </ol>
        </div>
        <div class="row g-3">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Preview Dokumen</h5>
                        {{--  UBAH: hint langsung tampil (dokumen auto-load) --}}
                        <small class="text-muted" id="preview-hint">Seleksi teks lalu klik "Terapkan Mapping"</small>
                    </div>
                    <div class="card-body p-0">
                        <div id="docx-container">
                            {{--  UBAH: empty-state jadi loading (karena auto-load) --}}
                            <div class="text-center py-5 text-muted" id="empty-state">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2">Memuat dokumen template...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-3" id="upload-section">
                    {{--  UBAH: Judul jadi opsional --}}
                    <div class="card-header">
                        <h6 class="mb-0">1. Ganti File Template <span class="badge bg-secondary"
                                style="font-size:10px;">Opsional</span></h6>
                    </div>
                    <div class="card-body">
                        <form id="formUpload" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">File DOCX Saat Ini</label>
                                {{--  UBAH: hapus 'required' karena opsional --}}
                                <input type="file" name="template_file" id="fileInput"
                                    class="form-control form-control-sm" accept=".docx,.doc">
                                <small class="text-muted">Kosongkan jika tidak ingin mengganti file.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Tabel Sumber Data</label>
                                {{--  UBAH: pre-select source_table dari template, hapus required --}}
                                <select name="source_table" id="source_table_select" class="form-select form-select-sm">
                                    <option value="karyawan"
                                        {{ ($template->source_table ?? '') == 'karyawan' ? 'selected' : '' }}>Karyawan
                                    </option>
                                    <option value="pelamar"
                                        {{ ($template->source_table ?? '') == 'pelamar' ? 'selected' : '' }}>Rekrutan
                                    </option>
                                </select>
                            </div>
                            {{--  UBAH: tombol jadi warning & teks "Ganti" --}}
                            <button type="submit" class="btn btn-warning btn-sm w-100" id="btnLoad">
                                <span id="btnLoadText">Ganti & Load Dokumen Baru</span>
                                <span id="btnLoadSpinner" class="spinner-border spinner-border-sm d-none"></span>
                            </button>
                        </form>
                    </div>
                </div>

                {{--  UBAH: mapping section langsung tampil (tidak display:none) --}}
                <div class="card mb-3" id="mapping-section">
                    <div class="card-header">
                        <h6 class="mb-0">2. Mapping Field</h6>
                    </div>
                    <div class="card-body">
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

                        <div id="no-selection-hint" class="text-muted small text-center py-2">Seleksi teks di preview untuk
                            mulai mapping</div>

                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="small fw-semibold mb-0">Mapping Diterapkan (<span id="mapping-count">0</span>)</h6>
                        </div>
                        <div id="mappings-list" style="max-height: 280px; overflow-y: auto;">
                            {{--  UBAH: teks loading mapping existing --}}
                            <p class="text-muted small text-center">Memuat mapping existing...</p>
                        </div>
                    </div>
                </div>

                {{--  UBAH: save section langsung tampil --}}
                <div class="card" id="save-section">
                    <div class="card-header">
                        {{--  UBAH: judul jadi Update --}}
                        <h6 class="mb-0">3. Update Template</h6>
                    </div>
                    <div class="card-body">
                        <form id="formSave">
                            @csrf
                            {{--  TAMBAH: method PUT untuk update --}}
                            @method('PUT')
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Nama Template <span
                                        class="text-danger">*</span></label>
                                {{--  UBAH: pre-fill value dari $template->name --}}
                                <input type="text" name="name" id="inputName" class="form-control form-control-sm"
                                    required value="{{ old('name', $template->name) }}">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Kode Template <span
                                        class="text-danger">*</span></label>
                                {{--  UBAH: pre-fill value dari $template->code --}}
                                <input type="text" name="code" id="inputCode" class="form-control form-control-sm"
                                    required value="{{ old('code', $template->code) }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Kategori</label>
                                {{--  UBAH: pre-select category dari $template->category --}}
                                <select name="category" class="form-select form-select-sm">
                                    <option value="karyawan"
                                        {{ ($template->category ?? '') == 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                                    <option value="pelamar"
                                        {{ ($template->category ?? '') == 'pelamar' ? 'selected' : '' }}>Rekrutan</option>
                                </select>
                            </div>
                            {{--  UBAH: tombol jadi warning, url ke route update, teks "Update" --}}
                            <button type="submit" class="btn btn-warning btn-sm w-100" id="btnSave"
                                data-url="{{ route('HR.reports.update', $template->id) }}"
                                data-token="{{ csrf_token() }}" data-redirect="{{ route('HR.reports.index') }}">
                                <span id="btnSaveText">Update Template</span>
                                <span id="btnSaveSpinner" class="spinner-border spinner-border-sm d-none"></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://unpkg.com/docx-preview@0.3.0/dist/docx-preview.min.js"></script>

    <script>
        window.APP_DATA = {
            columns: @json($availableColumns),
            saveUrl: "{{ route('HR.reports.update', $template->id) }}",
            csrfToken: "{{ csrf_token() }}",
            redirectUrl: "{{ route('HR.reports.index') }}",
            templateUrl: @json($template->template_file_path ? asset('storage/' . $template->template_file_path) : null),
            existingMappings: @json($existingMappings),
            sourceTable: @json($template->source_table ?? 'karyawan'),
        };

        document.addEventListener('DOMContentLoaded', function() {
            try {
                initReportEditor();
            } catch (err) {
                console.error('Init error:', err);
                alert('Error init: ' + err.message);
            }
        });

        function initReportEditor() {
            const COLUMNS = window.APP_DATA.columns;
            const SAVE_URL = window.APP_DATA.saveUrl;
            const CSRF_TOKEN = window.APP_DATA.csrfToken;
            const REDIRECT_URL = window.APP_DATA.redirectUrl;
            const TEMPLATE_URL = window.APP_DATA.templateUrl;
            const EXISTING_MAPPINGS = window.APP_DATA.existingMappings;
            const SOURCE_TABLE = window.APP_DATA.sourceTable;

            const RELATIONS = {};
            const AUTH_FIELDS = {
                'Data User Login': ['username', 'jabatan'],
                'Data Karyawan (Login)': ['nama_lengkap', 'nip', 'email', 'whatsapp']
            };
            const BOPEN = '{' + '{';
            const BCLOSE = '}' + '}';

            let currentFile = null;
            let userPickedNewFile = false;
            let currentSourceTable = SOURCE_TABLE;
            let currentSelection = null;
            let mappings = [];
            let pendingConfigType = null;
            let loopColumnIndex = 0;
            let editIndex = null; // 🔥 TAMBAHAN: State untuk melacak mode edit

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

            if (!formUpload) {
                console.error('formUpload element not found!');
                return;
            }

            document.getElementById('fileInput').addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    currentFile = this.files[0];
                    userPickedNewFile = true;
                    console.log('User memilih file baru:', currentFile.name);
                }
            });

            if (TEMPLATE_URL) {
                loadExistingTemplate();
            } else {
                emptyState.innerHTML = '<p class="mt-2">Tidak ada file template. Upload file baru di panel kanan.</p>';
            }

            loadExistingMappings();
            populateFieldSelector(currentSourceTable);

            async function loadExistingTemplate() {
                try {
                    const response = await fetch(TEMPLATE_URL);
                    if (!response.ok) throw new Error('File tidak ditemukan di server');

                    const arrayBuffer = await response.arrayBuffer();
                    console.log('Existing template loaded, rendering...');

                    const existingFileName = '{{ basename($template->template_file_path) }}';
                    currentFile = new File([arrayBuffer], existingFileName, {
                        type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                    });

                    try {
                        const fileInputEl = document.getElementById('fileInput');
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(currentFile);
                        fileInputEl.files = dataTransfer.files;
                    } catch (err) {
                        console.warn('Browser tidak mendukung set files:', err);
                    }

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

                    console.log('Existing document rendered successfully');
                    highlightExistingPlaceholders();
                    docxContainer.addEventListener('mouseup', onMouseUp);
                } catch (err) {
                    console.error('Load existing template error:', err);
                    docxContainer.innerHTML =
                        '<div class="alert alert-warning m-3"><strong>Gagal memuat dokumen existing:</strong> ' + err
                        .message + '<br><small>Anda tetap bisa upload file baru di panel kanan.</small></div>';
                }
            }

            function loadExistingMappings() {
                mappings = [];
                if (EXISTING_MAPPINGS && EXISTING_MAPPINGS.length > 0) {
                    EXISTING_MAPPINGS.forEach(function(ph) {
                        let type = ph.type;
                        if (!ph.is_manual && ph.key) {
                            type = 'db';
                        }

                             mappings.push({
                            find: '',
                            replace: ph.key,
                            fileKey: ph.key, // 🔧 FIX: simpan key asli yang ada di teks fisik file DOCX
                            type: type,
                            config: ph.config || {
                                label: ph.label
                            },
                            el: null
                        });
                    });
                }
                updateMappingsList();
                console.log('Loaded ' + mappings.length + ' existing mappings');
            }

            inputName.addEventListener('input', function() {
                const code = this.value.split(' ').filter(function(w) {
                    return w.length > 0;
                }).map(function(w) {
                    return w[0].toUpperCase();
                }).join('');
                inputCode.value = code;
            });

            formUpload.addEventListener('submit', async function(e) {
                e.preventDefault();
                const fileInput = document.getElementById('fileInput');
                const newFile = fileInput.files[0];
                currentSourceTable = document.getElementById('source_table_select').value;

                if (!newFile) {
                    alert('Pilih file DOCX baru terlebih dahulu jika ingin mengganti!');
                    return;
                }

                currentFile = newFile;
                userPickedNewFile = true;
                btnLoadText.textContent = 'Memuat...';
                btnLoadSpinner.classList.remove('d-none');
                btnLoad.disabled = true;

                mappings = [];
                currentSelection = null;
                editIndex = null;
                updateMappingsList();

                try {
                    if (!window.docx || typeof window.docx.renderAsync !== 'function') {
                        throw new Error('Library docx-preview belum ter-load. Refresh halaman.');
                    }

                    emptyState.style.display = 'none';
                    docxContainer.innerHTML =
                        '<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Memuat dokumen baru...</p></div>';

                    const arrayBuffer = await currentFile.arrayBuffer();
                    await window.docx.renderAsync(arrayBuffer, docxContainer, null, {
                        className: 'docx',
                        inWrapper: true,
                        ignoreWidth: false,
                        ignoreHeight: false,
                        breakPages: true,
                    });

                    console.log('New document rendered successfully');
                    docxContainer.addEventListener('mouseup', onMouseUp);
                    populateFieldSelector(currentSourceTable);
                } catch (err) {
                    console.error('Load error:', err);
                    docxContainer.innerHTML = '<div class="alert alert-danger m-3">Gagal memuat: ' + err
                        .message + '</div>';
                } finally {
                    btnLoadText.textContent = 'Ganti & Load Dokumen Baru';
                    btnLoadSpinner.classList.add('d-none');
                    btnLoad.disabled = false;
                }
            });

            function onMouseUp(e) {
                if (e.target.classList.contains('placeholder-badge')) return;
                const sel = window.getSelection();
                const text = sel ? sel.toString().trim() : '';
                if (text.length < 2) return;

                if (text.includes('{') || text.includes('}')) {
                    alert('Teks yang dipilih mengandung tanda "{" atau "}". ' +
                        'Ini biasanya sisa placeholder lama di dokumen. ' +
                        'Hapus dulu tanda kurung tersebut dari file docx, lalu upload ulang.');
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

            function highlightExistingPlaceholders() {
                // Ambil mapping yang belum punya elemen di DOM (mapping existing dari DB)
                const keyToIndex = {};
                mappings.forEach(function(m, idx) {
                    if (!m.el) keyToIndex[m.replace] = idx;
                });
                const keys = Object.keys(keyToIndex);
                if (keys.length === 0) return;

                const escapeRegex = function(s) {
                    return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                };
                // Cocokkan variasi placeholder: dua atau tiga kurung kurawal, dengan/tanpa spasi
                const pattern = new RegExp(
                    '\\{\\{\\{?\\s*(' + keys.map(escapeRegex).join('|') + ')\\s*\\}\\}\\}?',
                    'g'
                );

                const walker = document.createTreeWalker(docxContainer, NodeFilter.SHOW_TEXT, null, false);
                const textNodes = [];
                let node;
                while ((node = walker.nextNode())) {
                    if (node.parentElement && node.parentElement.closest('.text-mapped')) continue;
                    pattern.lastIndex = 0;
                    if (pattern.test(node.nodeValue)) {
                        textNodes.push(node);
                    }
                }

                textNodes.forEach(function(textNode) {
                    const text = textNode.nodeValue;
                    pattern.lastIndex = 0;
                    let match, lastIndex = 0, found = false;
                    const frag = document.createDocumentFragment();

                    while ((match = pattern.exec(text)) !== null) {
                        const key = match[1];
                        const idx = keyToIndex[key];
                        if (idx === undefined) continue;
                        found = true;

                        if (match.index > lastIndex) {
                            frag.appendChild(document.createTextNode(text.substring(lastIndex, match.index)));
                        }

                        const m = mappings[idx];
                        const span = document.createElement('span');
                        span.className = 'text-mapped text-mapped-' + m.type;
                        span.dataset.field = m.replace;
                        span.dataset.type = m.type;
                        span.appendChild(document.createTextNode(match[0]));

                        const badge = document.createElement('span');
                        badge.className = 'placeholder-badge placeholder-badge-' + m.type;
                        badge.textContent = m.replace;
                        span.appendChild(badge);

                        frag.appendChild(span);
                        mappings[idx].el = span; // ⬅️ ini kuncinya: sekarang mapping.el terisi
                        lastIndex = pattern.lastIndex;
                    }

                    if (found) {
                        if (lastIndex < text.length) {
                            frag.appendChild(document.createTextNode(text.substring(lastIndex)));
                        }
                        textNode.parentNode.replaceChild(frag, textNode);
                    }
                });
            }

            btnApplyMapping.addEventListener('click', function() {
                const field = fieldSelector.value;
                if (!field) {
                    alert('Pilih field!');
                    return;
                }

                // Jika sedang mode EDIT (editIndex tidak null)
                if (editIndex !== null) {
                    if (field.indexOf('__') === 0) {
                        pendingConfigType = field.replace(/^__|__$/g, '');
                        openConfigModal(pendingConfigType);
                        return;
                    }

                    updateExistingMapping(editIndex, field, 'db', { label: field });

                    // Reset state
                    editIndex = null;
                    clearSelection();
                    alert('Mapping berhasil diubah');
                    return;
                }

                // Mode TAMBAH BARU (seperti biasa)
                if (!currentSelection) {
                    alert('Pilih teks di preview!');
                    return;
                }

                if (field.indexOf('__') === 0) {
                    pendingConfigType = field.replace(/^__|__$/g, '');
                    openConfigModal(pendingConfigType);
                    return;
                }

                applyMapping(field, 'db', {
                    label: field
                });
            });

            function openConfigModal(type) {
                let html = '';
                let title = '';

                try {
                    if (type === 'auto_date') {
                        title = 'Konfigurasi Tanggal Otomatis';
                        html =
                            '<div class="mb-3"><label class="form-label fw-semibold">Placeholder Key</label><input type="text" id="cfg_key" class="form-control form-control-sm" value="tanggal_' +
                            Date.now().toString().slice(-6) +
                            '" pattern="^[a-z0-9_]+$" required><small class="text-muted">Huruf kecil, angka, underscore.</small></div><hr><h6 class="small fw-semibold mb-2">Komponen & Format Tanggal</h6><div class="mb-3"><label class="form-label small fw-semibold">Hari / Tanggal</label><select id="cfg_day_format" class="form-select form-select-sm"><option value="none">Tidak Ditampilkan</option><option value="number">Angka Tanggal (25)</option><option value="word">Kata Tanggal (Dua Puluh Lima)</option><option value="word_upper">KATA TANGGAL (DUA PULUH LIMA)</option><option value="day_name">Nama Hari (Kamis)</option><option value="day_name_upper">NAMA HARI (KAMIS)</option></select></div><div class="mb-3"><label class="form-label small fw-semibold">Bulan</label><select id="cfg_month_format" class="form-select form-select-sm"><option value="none">Tidak Ditampilkan</option><option value="number">Angka Bulan (06)</option><option value="month_name">Nama Bulan (Juni)</option><option value="month_name_upper">NAMA BULAN (JUNI)</option></select></div><div class="mb-3"><label class="form-label small fw-semibold">Tahun</label><select id="cfg_year_format" class="form-select form-select-sm"><option value="none">Tidak Ditampilkan</option><option value="number">Angka Tahun (2026)</option><option value="word">Kata Tahun (Dua Ribu Dua Puluh Enam)</option><option value="word_upper">KATA TAHUN (DUA RIBU DUA PULUH ENAM)</option></select></div><div class="mb-3"><label class="form-label small fw-semibold">Pemisah Antar Komponen</label><input type="text" id="cfg_separator" class="form-control form-control-sm" value=" " placeholder="cth: spasi, koma, strip"><small class="text-muted">Karakter pemisah. Kosongkan jika ingin digabung tanpa spasi.</small></div>';
                    } else if (type === 'formula') {
                        title = 'Konfigurasi Rumus';
                        html =
                            '<div class="mb-3"><label class="form-label fw-semibold">Placeholder Key</label><input type="text" id="cfg_key" class="form-control form-control-sm" value="nomor_surat_' +
                            Date.now().toString().slice(-6) +
                            '" pattern="^[a-z0-9_]+$" required></div><div class="mb-3"><label class="form-label fw-semibold">Template Rumus</label><input type="text" id="cfg_template" class="form-control form-control-sm" placeholder="KP/{tahun}/{bulan_romawi}/{urutan:4}"><small class="text-muted d-block mt-2"><strong>Variabel tersedia:</strong><br>• {tahun} → 2026<br>• {bulan} → 06<br>• {bulan_romawi} → VI<br>• {urutan:4} → 0001 (auto increment)<br>• {urutan_romawi} → I, II, III</small></div><div class="mb-3"><label class="form-label fw-semibold">Nomor Terakhir / Start From (Opsional)</label><input type="number" id="cfg_last_number" class="form-control form-control-sm" placeholder="cth: 233" min="0"><small class="text-muted">Jika diisi (misal 233), generate berikutnya akan dimulai dari 234 (atau CCXXXIV).</small></div><div class="mb-3"><label class="form-label fw-semibold">Counter Key</label><input type="text" id="cfg_counter_key" class="form-control form-control-sm" placeholder="kosongkan untuk auto"></div>';
                    } else if (type === 'auth_field') {
                        title = 'Konfigurasi Data User / Karyawan Login';
                        let optionsHtml = '';
                        for (const group in AUTH_FIELDS) {
                            optionsHtml += '<optgroup label="' + group + '">';
                            AUTH_FIELDS[group].forEach(function(f) {
                                optionsHtml += '<option value="' + f + '">' + f + '</option>';
                            });
                            optionsHtml += '</optgroup>';
                        }
                        html =
                            '<div class="mb-3"><label class="form-label fw-semibold">Placeholder Key</label><input type="text" id="cfg_key" class="form-control form-control-sm" value="pembuat_' +
                            Date.now().toString().slice(-6) +
                            '" pattern="^[a-z0-9_]+$" required></div><div class="mb-3"><label class="form-label fw-semibold">Field yang Diambil</label><select id="cfg_field" class="form-select form-select-sm">' +
                            optionsHtml +
                            '</select><small class="text-muted d-block mt-2">Sistem akan otomatis mengambil dari tabel User atau Karyawan sesuai field yang dipilih.</small></div>';
                    } else if (type === 'relation_single') {
                        title = 'Konfigurasi Relasi Single';
                        let relOptions = '<option value="">-- Pilih Relasi --</option>';
                        const rels = (RELATIONS[currentSourceTable] && RELATIONS[currentSourceTable].single) || {};
                        for (const k in rels) {
                            if (rels.hasOwnProperty(k)) relOptions += '<option value="' + k + '">' + rels[k].label + ' (' +
                                k + ')</option>';
                        }
                        html =
                            '<div class="mb-3"><label class="form-label fw-semibold">Placeholder Key</label><input type="text" id="cfg_key" class="form-control form-control-sm" pattern="^[a-z0-9_]+$" required></div><div class="mb-3"><label class="form-label fw-semibold">Relasi</label><select id="cfg_relation" class="form-select form-select-sm" onchange="window._updateRelationFields()">' +
                            relOptions +
                            '</select></div><div class="mb-3"><label class="form-label fw-semibold">Field yang Diambil</label><select id="cfg_field" class="form-select form-select-sm"><option value="">-- Pilih Relasi Dulu --</option></select></div>';
                    } else if (type === 'loop_manual') {
                        title = 'Konfigurasi Loop Manual';
                        html =
                            '<div class="mb-3"><label class="form-label fw-semibold">Placeholder Key (nama collection)</label><input type="text" id="cfg_key" class="form-control form-control-sm" value="peserta_' +
                            Date.now().toString().slice(-6) +
                            '" pattern="^[a-z0-9_]+$" required><small class="text-muted">Akan digunakan sebagai {loop:key.field}</small></div><div class="mb-3"><label class="form-label fw-semibold">Kolom-kolom Tabel</label><div id="loop_columns"></div><button type="button" class="btn btn-sm btn-secondary mt-2" onclick="window._addLoopColumn()">+ Tambah Kolom</button></div>';
                    } else if (type === 'loop_relation') {
                        title = 'Konfigurasi Loop Relasi';
                        let relOptions = '<option value="">-- Pilih Relasi --</option>';
                        const rels = (RELATIONS[currentSourceTable] && RELATIONS[currentSourceTable].collection) || {};
                        for (const k in rels) {
                            if (rels.hasOwnProperty(k)) relOptions += '<option value="' + k + '">' + rels[k].label + ' (' +
                                k + ')</option>';
                        }
                        html =
                            '<div class="mb-3"><label class="form-label fw-semibold">Placeholder Key</label><input type="text" id="cfg_key" class="form-control form-control-sm" pattern="^[a-z0-9_]+$" required></div><div class="mb-3"><label class="form-label fw-semibold">Relasi</label><select id="cfg_relation" class="form-select form-select-sm">' +
                            relOptions +
                            '</select></div><div class="mb-3"><label class="form-label fw-semibold">Field (pisahkan koma)</label><input type="text" id="cfg_fields" class="form-control form-control-sm" placeholder="nama, tanggal, status"></div>';
                    } else if (type === 'manual_text') {
                        title = 'Konfigurasi Input Teks Manual';
                        html =
                            '<div class="mb-3"><label class="form-label fw-semibold">Placeholder Key</label><input type="text" id="cfg_key" class="form-control form-control-sm" value="teks_' +
                            Date.now().toString().slice(-6) +
                            '" pattern="^[a-z0-9_]+$" required><small class="text-muted">Huruf kecil, angka, underscore.</small></div><div class="mb-3"><label class="form-label fw-semibold">Label Field</label><input type="text" id="cfg_label" class="form-control form-control-sm" placeholder="cth: Nama Lengkap"></div><div class="mb-3"><label class="form-label fw-semibold">Default Value (Opsional)</label><input type="text" id="cfg_default" class="form-control form-control-sm" placeholder="Kosongkan jika tidak ada"></div><div class="mb-3"><label class="form-label fw-semibold">Placeholder Text (Opsional)</label><input type="text" id="cfg_placeholder" class="form-control form-control-sm" placeholder="cth: Masukkan nama..."></div>';
                    } else if (type === 'manual_textarea') {
                        title = 'Konfigurasi Textarea Manual';
                        html =
                            '<div class="mb-3"><label class="form-label fw-semibold">Placeholder Key</label><input type="text" id="cfg_key" class="form-control form-control-sm" value="textarea_' +
                            Date.now().toString().slice(-6) +
                            '" pattern="^[a-z0-9_]+$" required></div><div class="mb-3"><label class="form-label fw-semibold">Label Field</label><input type="text" id="cfg_label" class="form-control form-control-sm" placeholder="cth: Alamat Lengkap"></div><div class="mb-3"><label class="form-label fw-semibold">Jumlah Baris</label><input type="number" id="cfg_rows" class="form-control form-control-sm" value="3" min="2" max="10"></div><div class="mb-3"><label class="form-label fw-semibold">Default Value (Opsional)</label><textarea id="cfg_default" class="form-control form-control-sm" rows="2" placeholder="Kosongkan jika tidak ada"></textarea></div>';
                    } else if (type === 'manual_date') {
                        title = 'Konfigurasi Tanggal Manual';
                        html =
                            '<div class="mb-3"><label class="form-label fw-semibold">Placeholder Key</label><input type="text" id="cfg_key" class="form-control form-control-sm" value="tanggal_manual_' +
                            Date.now().toString().slice(-6) +
                            '" pattern="^[a-z0-9_]+$" required></div><div class="mb-3"><label class="form-label fw-semibold">Label Field</label><input type="text" id="cfg_label" class="form-control form-control-sm" placeholder="cth: Tanggal Lahir"></div><hr><h6 class="small fw-semibold mb-2">Komponen & Format Output</h6><div class="mb-3"><label class="form-label small fw-semibold">Hari / Tanggal</label><select id="cfg_day_format" class="form-select form-select-sm"><option value="none">Tidak Ditampilkan</option><option value="number">Angka Tanggal (25)</option><option value="word">Kata Tanggal (Dua Puluh Lima)</option><option value="word_upper">KATA TANGGAL (DUA PULUH LIMA)</option><option value="day_name">Nama Hari (Kamis)</option><option value="day_name_upper">NAMA HARI (KAMIS)</option></select></div><div class="mb-3"><label class="form-label small fw-semibold">Bulan</label><select id="cfg_month_format" class="form-select form-select-sm"><option value="none">Tidak Ditampilkan</option><option value="number">Angka Bulan (06)</option><option value="month_name">Nama Bulan (Juni)</option><option value="month_name_upper">NAMA BULAN (JUNI)</option></select></div><div class="mb-3"><label class="form-label small fw-semibold">Tahun</label><select id="cfg_year_format" class="form-select form-select-sm"><option value="none">Tidak Ditampilkan</option><option value="number">Angka Tahun (2026)</option><option value="word">Kata Tahun (Dua Ribu Dua Puluh Enam)</option><option value="word_upper">KATA TAHUN (DUA RIBU DUA PULUH ENAM)</option></select></div><div class="mb-3"><label class="form-label small fw-semibold">Pemisah Antar Komponen</label><input type="text" id="cfg_separator" class="form-control form-control-sm" value=" " placeholder="cth: spasi, koma, strip"></div><div class="mb-3"><label class="form-label fw-semibold">Default Value (Opsional)</label><input type="date" id="cfg_default" class="form-control form-control-sm"></div>';
                    } else if (type === 'manual_number') {
                        title = 'Konfigurasi Angka Manual';
                        html =
                            '<div class="mb-3"><label class="form-label fw-semibold">Placeholder Key</label><input type="text" id="cfg_key" class="form-control form-control-sm" value="angka_' +
                            Date.now().toString().slice(-6) +
                            '" pattern="^[a-z0-9_]+$" required></div><div class="mb-3"><label class="form-label fw-semibold">Label Field</label><input type="text" id="cfg_label" class="form-control form-control-sm" placeholder="cth: Jumlah"></div><div class="mb-3"><label class="form-label fw-semibold">Tipe Angka</label><select id="cfg_number_type" class="form-select form-select-sm"><option value="number">Angka Biasa (1,234.56)</option><option value="currency">Mata Uang (Rp 1.234)</option><option value="integer">Bulat (1234)</option></select></div><div class="mb-3"><label class="form-label fw-semibold">Default Value (Opsional)</label><input type="number" id="cfg_default" class="form-control form-control-sm" placeholder="Kosongkan jika tidak ada"></div>';
                    } else if (type === 'manual_select') {
                        title = 'Konfigurasi Dropdown Manual';
                        html =
                            '<div class="mb-3"><label class="form-label fw-semibold">Placeholder Key</label><input type="text" id="cfg_key" class="form-control form-control-sm" value="pilihan_' +
                            Date.now().toString().slice(-6) +
                            '" pattern="^[a-z0-9_]+$" required></div><div class="mb-3"><label class="form-label fw-semibold">Label Field</label><input type="text" id="cfg_label" class="form-control form-control-sm" placeholder="cth: Status"></div><div class="mb-3"><label class="form-label fw-semibold">Opsi (pisahkan dengan koma)</label><textarea id="cfg_options" class="form-control form-control-sm" rows="3" placeholder="cth: Aktif, Nonaktif, Cuti"></textarea><small class="text-muted">Pisahkan setiap opsi dengan koma</small></div><div class="mb-3"><label class="form-label fw-semibold">Default Value (Opsional)</label><input type="text" id="cfg_default" class="form-control form-control-sm" placeholder="Salah satu opsi di atas"></div>';
                    } else if (type === 'manual_checkbox') {
                        title = 'Konfigurasi Checkbox Manual';
                        html =
                            '<div class="mb-3"><label class="form-label fw-semibold">Placeholder Key</label><input type="text" id="cfg_key" class="form-control form-control-sm" value="checkbox_' +
                            Date.now().toString().slice(-6) +
                            '" pattern="^[a-z0-9_]+$" required></div><div class="mb-3"><label class="form-label fw-semibold">Label Field</label><input type="text" id="cfg_label" class="form-control form-control-sm" placeholder="cth: Setuju"></div><div class="mb-3"><label class="form-label fw-semibold">Default Value</label><select id="cfg_default" class="form-select form-select-sm"><option value="0">Tidak Dicentang</option><option value="1">Dicentang</option></select></div>';
                    }

                    // 🔥 UBAH: Tambahkan prefix judul jika sedang mode edit
                    document.getElementById('configModalTitle').innerHTML = (editIndex !== null ? '✏️ Edit: ' : '') + title;
                    document.getElementById('configModalBody').innerHTML = html;

                    const modalEl = document.getElementById('fieldConfigModal');
                    if (!modalEl) throw new Error('Modal element not found!');

                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();

                    // 🔥 TAMBAHAN: Isi form dengan data lama jika sedang mode edit
                    if (editIndex !== null && mappings[editIndex]) {
                        preFillModal(mappings[editIndex]);
                    }

                    if (type === 'loop_manual' && editIndex === null) {
                        loopColumnIndex = 0;
                        setTimeout(function() {
                            window._addLoopColumn();
                        }, 100);
                    }
                } catch (err) {
                    console.error('Error in openConfigModal:', err);
                    alert('Error membuka modal: ' + err.message);
                }
            }

            window._updateRelationFields = function() {
                const rel = document.getElementById('cfg_relation').value;
                const fieldSelect = document.getElementById('cfg_field');
                const rels = (RELATIONS[currentSourceTable] && RELATIONS[currentSourceTable].single) || {};
                const fields = (rels[rel] && rels[rel].fields) || [];
                fieldSelect.innerHTML = fields.length ? fields.map(function(f) {
                    return '<option value="' + f + '">' + f + '</option>';
                }).join('') : '<option value="">-- Tidak ada field --</option>';
                const keyInput = document.getElementById('cfg_key');
                if (keyInput && !keyInput.value) keyInput.value = rel + '_field';
            };

            window._addLoopColumn = function(key, label, type) {
                key = key || '';
                label = label || '';
                type = type || 'text';
                const container = document.getElementById('loop_columns');
                if (!container) return;
                const idx = loopColumnIndex++;
                const row = document.createElement('div');
                row.className = 'row g-2 mb-2 loop-col-row';
                row.innerHTML =
                    '<div class="col-4"><input type="text" class="form-control form-control-sm lc-key" placeholder="key" value="' +
                    key + '" required></div>' +
                    '<div class="col-4"><input type="text" class="form-control form-control-sm lc-label" placeholder="Label" value="' +
                    label + '" required></div>' +
                    '<div class="col-3"><select class="form-select form-select-sm lc-type"><option value="text"' + (
                        type === 'text' ? ' selected' : '') + '>Text</option><option value="number"' + (type ===
                        'number' ? ' selected' : '') + '>Number</option><option value="date"' + (type === 'date' ?
                        ' selected' : '') + '>Date</option></select></div>' +
                    '<div class="col-1"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest(\'.loop-col-row\').remove()">✕</button></div>';
                container.appendChild(row);
            };

            document.getElementById('btnConfirmConfig').addEventListener('click', function() {
                const type = pendingConfigType;
                let config = {};
                let key = '';

                try {
                    if (type === 'auto_date') {
                        key = document.getElementById('cfg_key').value.trim();
                        if (!key || !/^[a-z0-9_]+$/.test(key)) {
                            alert('Key tidak valid!');
                            return;
                        }
                        config = {
                            day_format: document.getElementById('cfg_day_format').value,
                            month_format: document.getElementById('cfg_month_format').value,
                            year_format: document.getElementById('cfg_year_format').value,
                            separator: document.getElementById('cfg_separator').value,
                            label: 'Tanggal Otomatis'
                        };
                    } else if (type === 'formula') {
                        key = document.getElementById('cfg_key').value.trim();
                        if (!key || !/^[a-z0-9_]+$/.test(key)) {
                            alert('Key tidak valid!');
                            return;
                        }
                        const template = document.getElementById('cfg_template').value.trim();
                        if (!template) {
                            alert('Template rumus wajib diisi!');
                            return;
                        }
                        const lastNumberStr = document.getElementById('cfg_last_number').value.trim();
                        config = {
                            template: template,
                            counter_key: document.getElementById('cfg_counter_key').value.trim() || null,
                            last_number: lastNumberStr !== '' ? parseInt(lastNumberStr) : null,
                            label: 'Rumus'
                        };
                    } else if (type === 'auth_field') {
                        key = document.getElementById('cfg_key').value.trim();
                        if (!key || !/^[a-z0-9_]+$/.test(key)) {
                            alert('Key tidak valid!');
                            return;
                        }
                        config = {
                            field: document.getElementById('cfg_field').value,
                            label: 'Data User Login'
                        };
                    } else if (type === 'relation_single') {
                        key = document.getElementById('cfg_key').value.trim();
                        if (!key || !/^[a-z0-9_]+$/.test(key)) {
                            alert('Key tidak valid!');
                            return;
                        }
                        const relation = document.getElementById('cfg_relation').value;
                        const field = document.getElementById('cfg_field').value;
                        if (!relation || !field) {
                            alert('Relasi dan field wajib dipilih!');
                            return;
                        }
                        config = {
                            relation: relation,
                            field: field,
                            label: 'Relasi Single'
                        };
                    } else if (type === 'loop_manual') {
                        key = document.getElementById('cfg_key').value.trim();
                        if (!key || !/^[a-z0-9_]+$/.test(key)) {
                            alert('Key tidak valid!');
                            return;
                        }
                        const columns = [];
                        document.querySelectorAll('.loop-col-row').forEach(function(row) {
                            const k = row.querySelector('.lc-key').value.trim();
                            const l = row.querySelector('.lc-label').value.trim();
                            const t = row.querySelector('.lc-type').value;
                            if (k && l) columns.push({
                                key: k,
                                label: l,
                                type: t
                            });
                        });
                        if (columns.length === 0) {
                            alert('Minimal 1 kolom!');
                            return;
                        }
                        config = {
                            columns: columns,
                            label: 'Loop Manual'
                        };
                    } else if (type === 'loop_relation') {
                        key = document.getElementById('cfg_key').value.trim();
                        if (!key || !/^[a-z0-9_]+$/.test(key)) {
                            alert('Key tidak valid!');
                            return;
                        }
                        const relation = document.getElementById('cfg_relation').value;
                        if (!relation) {
                            alert('Relasi wajib dipilih!');
                            return;
                        }
                        const fieldsStr = document.getElementById('cfg_fields').value.trim();
                        config = {
                            relation: relation,
                            fields: fieldsStr ? fieldsStr.split(',').map(function(s) {
                                return s.trim();
                            }).filter(Boolean) : [],
                            label: 'Loop Relasi'
                        };
                    } else if (type === 'manual_text') {
                        key = document.getElementById('cfg_key').value.trim();
                        if (!key || !/^[a-z0-9_]+$/.test(key)) {
                            alert('Key tidak valid!');
                            return;
                        }
                        config = {
                            label: document.getElementById('cfg_label').value.trim() || key,
                            default: document.getElementById('cfg_default').value.trim(),
                            placeholder: document.getElementById('cfg_placeholder').value.trim()
                        };
                    } else if (type === 'manual_textarea') {
                        key = document.getElementById('cfg_key').value.trim();
                        if (!key || !/^[a-z0-9_]+$/.test(key)) {
                            alert('Key tidak valid!');
                            return;
                        }
                        config = {
                            label: document.getElementById('cfg_label').value.trim() || key,
                            rows: parseInt(document.getElementById('cfg_rows').value) || 3,
                            default: document.getElementById('cfg_default').value.trim()
                        };
                    } else if (type === 'manual_date') {
                        key = document.getElementById('cfg_key').value.trim();
                        if (!key || !/^[a-z0-9_]+$/.test(key)) {
                            alert('Key tidak valid!');
                            return;
                        }
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
                        if (!key || !/^[a-z0-9_]+$/.test(key)) {
                            alert('Key tidak valid!');
                            return;
                        }
                        config = {
                            label: document.getElementById('cfg_label').value.trim() || key,
                            number_type: document.getElementById('cfg_number_type').value,
                            default: document.getElementById('cfg_default').value
                        };
                    } else if (type === 'manual_select') {
                        key = document.getElementById('cfg_key').value.trim();
                        if (!key || !/^[a-z0-9_]+$/.test(key)) {
                            alert('Key tidak valid!');
                            return;
                        }
                        const optionsStr = document.getElementById('cfg_options').value.trim();
                        if (!optionsStr) {
                            alert('Opsi wajib diisi!');
                            return;
                        }
                        config = {
                            label: document.getElementById('cfg_label').value.trim() || key,
                            options: optionsStr.split(',').map(function(s) {
                                return s.trim();
                            }).filter(Boolean),
                            default: document.getElementById('cfg_default').value.trim()
                        };
                    } else if (type === 'manual_checkbox') {
                        key = document.getElementById('cfg_key').value.trim();
                        if (!key || !/^[a-z0-9_]+$/.test(key)) {
                            alert('Key tidak valid!');
                            return;
                        }
                        config = {
                            label: document.getElementById('cfg_label').value.trim() || key,
                            default: document.getElementById('cfg_default').value
                        };
                    }

                    if (editIndex !== null) {
                        updateExistingMapping(editIndex, key, type, config);
                    } else {
                        if (!currentSelection) {
                            mappings.push({
                                find: '',
                                replace: key,
                                type: type,
                                config: config,
                                el: null
                            });
                            updateMappingsList();
                        } else {
                            applyMapping(key, type, config);
                        }
                    }

                    const modal = bootstrap.Modal.getInstance(document.getElementById('fieldConfigModal'));
                    if (modal) modal.hide();
                    editIndex = null;
                } catch (err) {
                    console.error('Config error:', err);
                    alert('Error: ' + err.message);
                }
            });

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
                mappings.forEach(function(m, idx) {
                    const div = document.createElement('div');
                    div.className =
                        'mapping-item d-flex justify-content-between align-items-start border-bottom py-2';
                    const configInfo = m.type !== 'db' ? '<br><small class="text-muted">' + getTypeLabel(m.type) +
                        '</small>' : '';
                    const findDisplay = m.find ? '<del class="text-muted">' + escHtml(m.find.length > 30 ? m.find
                            .substring(0, 30) + '...' : m.find) + '</del><br>' :
                        '<small class="text-muted">(existing)</small><br>';

                    // 🔥 UBAH: Tambahkan tombol Edit di samping tombol Hapus
                    div.innerHTML = '<div class="text-truncate me-2" style="max-width:65%;" title="' + escHtml(m
                            .find) + '">' +
                        findDisplay +
                        '<code class="text-success">' + BOPEN + ' ' + escHtml(m.replace) + ' ' + BCLOSE +
                        '</code>' +
                        '<span class="badge bg-light text-dark border ms-1" style="font-size:9px;">' + getTypeLabel(
                            m.type) + '</span>' +
                        configInfo + '</div>' +
                        '<div class="btn-group btn-group-sm">' +
                        '<button class="btn btn-sm btn-outline-primary py-0 px-1" onclick="window._editMapping(' +
                        idx + ')" title="Edit">✏️</button>' +
                        '<button class="btn btn-sm btn-outline-danger py-0 px-1" onclick="window._removeMapping(' +
                        idx + ')" title="Hapus">✕</button>' +
                        '</div>';
                    mappingsList.appendChild(div);
                });
            }

            window._removeMapping = function(idx) {
                const m = mappings[idx];
                if (!m) return;

                // 1. Kembalikan teks asli ke dokumen dan hapus highlight/badge
                if (m.el && m.el.parentNode) {
                    const parent = m.el.parentNode;

                    // Pindahkan semua isi wrapper ke parent, kecuali badge
                    while (m.el.firstChild) {
                        const child = m.el.firstChild;

                        // Cek aman: pastikan ini Element Node (bukan Text Node) sebelum cek classList
                        if (child.nodeType === 1 && child.classList && child.classList.contains('placeholder-badge')) {
                            // Jika ini badge, hapus saja
                            m.el.removeChild(child);
                        } else {
                            // Jika ini teks asli, kembalikan ke dokumen (sebelum posisi wrapper)
                            parent.insertBefore(child, m.el);
                        }
                    }

                    // Hapus wrapper span yang sekarang sudah kosong
                    if (m.el.parentNode) {
                        m.el.parentNode.removeChild(m.el);
                    }
                } else if (!m.el && m.replace) {
                    const domElements = docxContainer.querySelectorAll('[data-field="' + m.replace + '"]');

                    domElements.forEach(function(el) {
                        const parent = el.parentNode;
                        if (parent) {
                            while (el.firstChild) {
                                const child = el.firstChild;
                                if (child.nodeType === 1 && child.classList && child.classList.contains(
                                        'placeholder-badge')) {
                                    el.removeChild(child);
                                } else {
                                    parent.insertBefore(child, el);
                                }
                            }

                            if (el.parentNode) {
                                el.parentNode.removeChild(el);
                            }
                        }
                    });
                }

                mappings.splice(idx, 1);

                updateMappingsList();
            };
                function getFindTextForSave(m) {
                if (m.fileKey && m.fileKey !== m.replace) {
                    return BOPEN + ' ' + m.fileKey + ' ' + BCLOSE;
                }
                return m.find || '';
            }
            function escHtml(str) {
                const d = document.createElement('div');
                d.textContent = str;
                return d.innerHTML;
            }

            formSave.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                if (userPickedNewFile && currentFile) {
                    formData.append('template_file', currentFile, currentFile.name);
                }
                formData.append('source_table', currentSourceTable);

                                const dbMappings = mappings.filter(function(m) {
                    return m.type === 'db';
                });
                dbMappings.forEach(function(m, idx) {
                    formData.append('replacements[' + idx + '][find]', getFindTextForSave(m));
                    formData.append('replacements[' + idx + '][replace]', m.replace);
                });

                                const specialFields = mappings.filter(function(m) {
                    return m.type !== 'db';
                }).map(function(m) {
                    return {
                        placeholder_key: m.replace,
                        placeholder_label: (m.config && m.config.label) || m.replace,
                        field_type: m.type,
                        is_manual: (m.type.indexOf('manual_') === 0 || m.type === 'loop_manual') ? 1 : 0,
                        config: m.config,
                        find_text: getFindTextForSave(m) // 🔧 FIX
                    };
                });

                formData.append('special_fields', JSON.stringify(specialFields));

                btnSaveText.textContent = 'Mengupdate...';
                btnSaveSpinner.classList.remove('d-none');
                btnSave.disabled = true;

                fetch(SAVE_URL, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(async function(res) {
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
                    .then(function(data) {
                        if (data.success) {
                            alert(data.message);
                            window.location.href = REDIRECT_URL;
                        } else {
                            alert((data.message || 'Terjadi kesalahan'));
                        }
                    })
                    .catch(function(err) {
                        console.error('Save error:', err);
                        const msg = err.errors ? Object.values(err.errors).flat().join('\n') : (err.message ||
                            'Terjadi kesalahan');
                        alert(msg);
                    })
                    .finally(function() {
                        btnSaveText.textContent = 'Update Template';
                        btnSaveSpinner.classList.add('d-none');
                        btnSave.disabled = false;
                    });
            });

            // =================================================================
            // 🔥 FUNGSI TAMBAHAN KHUSUS UNTUK FITUR EDIT (Ditempel di paling bawah)
            // =================================================================

            // =================================================================
            // 🔥 FUNGSI EDIT MAPPING - MENAMPILKAN DROPDOWN FIELD
            // =================================================================

            window._editMapping = function(idx) {
                const m = mappings[idx];
                if (!m) return;

                // Simpan index yang sedang diedit
                editIndex = idx;
                pendingConfigType = m.type;

                // Jika tipe special (bukan DB column biasa), langsung buka modal konfigurasi yang sudah ter-prefill
                if (m.type !== 'db') {
                    openConfigModal(m.type);
                    return;
                }

                // Jika tipe DB, tampilkan dropdown pilihan field DB
                const dbGroup = document.getElementById('optgroup-db');
                if (!dbGroup || dbGroup.children.length === 0) {
                    populateFieldSelector(currentSourceTable);
                }

                // Tampilkan panel mapping
                selectionInfo.style.display = 'block';
                selectedTextDisplay.textContent = (m.find || m.replace) + ' (sedang diedit)';
                fieldSelectorGroup.style.display = 'block';
                noSelectionHint.style.display = 'none';

                // Set dropdown ke field yang sedang diedit
                fieldSelector.value = m.replace;

                // Scroll ke panel mapping
                mappingSection.scrollIntoView({
                    behavior: 'smooth'
                });

                // Highlight mapping yang sedang diedit
                const mappingItems = document.querySelectorAll('.mapping-item');
                if (mappingItems[idx]) {
                    mappingItems[idx].style.backgroundColor = '#fff3cd';
                    setTimeout(() => {
                        mappingItems[idx].style.backgroundColor = '';
                    }, 2000);
                }
            };

            function updateExistingMapping(idx, newKey, newType, newConfig) {
                const oldMapping = mappings[idx];
                const oldKey = oldMapping.replace;
                mappings[idx].replace = newKey;
                mappings[idx].type = newType;
                mappings[idx].config = newConfig;

                // Update tampilan di dokumen secara otomatis
                if (oldMapping.el) {
                    oldMapping.el.className = 'text-mapped text-mapped-' + newType;
                    oldMapping.el.dataset.field = newKey;
                    oldMapping.el.dataset.type = newType;
                    const badge = oldMapping.el.querySelector('.placeholder-badge');
                    if (badge) {
                        badge.className = 'placeholder-badge placeholder-badge-' + newType;
                        badge.textContent = newKey;
                    }
                } else {
                    // Fallback untuk mapping existing yang belum punya referensi 'el'
                    const domElements = docxContainer.querySelectorAll('[data-field="' + oldKey + '"]');
                    domElements.forEach(function(el) {
                        el.className = 'text-mapped text-mapped-' + newType;
                        el.dataset.field = newKey;
                        el.dataset.type = newType;
                        const badge = el.querySelector('.placeholder-badge');
                        if (badge) {
                            badge.className = 'placeholder-badge placeholder-badge-' + newType;
                            badge.textContent = newKey;
                        }
                        mappings[idx].el = el;
                    });
                }
                updateMappingsList();
            }

            function preFillModal(mapping) {
                const config = mapping.config || {};
                const keyInput = document.getElementById('cfg_key');
                if (keyInput) keyInput.value = mapping.replace;

                const setVal = (id, val) => {
                    const el = document.getElementById(id);
                    if (el && val !== undefined && val !== null) el.value = val;
                };

                if (config.label) setVal('cfg_label', config.label);
                if (config.default !== undefined) setVal('cfg_default', config.default);
                if (config.placeholder) setVal('cfg_placeholder', config.placeholder);
                if (config.template) setVal('cfg_template', config.template);
                if (config.day_format) setVal('cfg_day_format', config.day_format);
                if (config.month_format) setVal('cfg_month_format', config.month_format);
                if (config.year_format) setVal('cfg_year_format', config.year_format);
                if (config.separator) setVal('cfg_separator', config.separator);
                if (config.number_type) setVal('cfg_number_type', config.number_type);
                if (config.field) setVal('cfg_field', config.field);
                if (config.relation) setVal('cfg_relation', config.relation);

                if (mapping.type === 'loop_manual' && config.columns && config.columns.length > 0) {
                    loopColumnIndex = 0;
                    const container = document.getElementById('loop_columns');
                    if (container) container.innerHTML = '';
                    config.columns.forEach(col => window._addLoopColumn(col.key, col.label, col.type));
                }
                if (mapping.type === 'manual_select' && config.options) {
                    setVal('cfg_options', Array.isArray(config.options) ? config.options.join(', ') : config.options);
                }
            }
        }
    </script>
@endsection