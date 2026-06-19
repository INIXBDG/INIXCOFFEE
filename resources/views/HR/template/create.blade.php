@extends('layout_HR.app')

@section('content_HR')
    <div class="container-fluid">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('HR.reports.index') }}">Report Generator</a></li>
                <li class="breadcrumb-item active">Buat Template Baru</li>
            </ol>
        </nav>

        {{-- PANDUAN PENGGUNAAN --}}
        <div class="alert alert-info mb-4">
            <strong>Cara Membuat Template:</strong>
            <ol class="mb-0 mt-1">
                <li>Upload file DOCX yang sudah berisi <strong>data dummy</strong> (contoh: "Budi Santoso", "EMP-001")</li>
                <li>Pilih tabel sumber data</li>
                <li>Klik teks dummy di preview, lalu pilih field database yang sesuai</li>
                <li>Isi nama template dan simpan</li>
            </ol>
        </div>

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

            ::selection {
                background: #0dcaf0;
                color: white;
            }

            .mapping-item {
                font-size: 0.8rem;
            }
        </style>

        <div class="row g-3">
            {{-- PREVIEW PANEL --}}
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Preview Dokumen</h5>
                        <small class="text-muted" id="preview-hint" style="display:none;">
                            Seleksi teks lalu klik "Terapkan Mapping"
                        </small>
                    </div>
                    <div class="card-body p-0">
                        <div id="docx-container">
                            <div class="text-center py-5 text-muted" id="empty-state">
                                <p class="mt-2">Upload file DOCX untuk memulai</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SIDEBAR --}}
            <div class="col-md-4">
                {{-- STEP 1: Upload --}}
                <div class="card mb-3" id="upload-section">
                    <div class="card-header ext-black">
                        <h6 class="mb-0">1. Upload Template</h6>
                    </div>
                    <div class="card-body">
                        <form id="formUpload" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">File DOCX <span
                                        class="text-danger">*</span></label>
                                <input type="file" name="template_file" id="fileInput"
                                    class="form-control form-control-sm" accept=".docx" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Tabel Sumber Data <span
                                        class="text-danger">*</span></label>
                                <select name="source_table" id="source_table_select" class="form-select form-select-sm"
                                    required>
                                    <option value="karyawan">Karyawan</option>
                                    <option value="kegiatan">Kegiatan</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm w-100" id="btnLoad">
                                <span id="btnLoadText">Load Dokumen</span>
                                <span id="btnLoadSpinner" class="spinner-border spinner-border-sm d-none"></span>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- STEP 2: Mapping --}}
                <div class="card mb-3" id="mapping-section" style="display:none;">
                    <div class="card-header text-black">
                        <h6 class="mb-0">2. Mapping Field</h6>
                    </div>
                    <div class="card-body">
                        {{-- Info seleksi aktif --}}
                        <div id="selection-info" class="alert alert-warning py-2 mb-3" style="display:none;">
                            <small class="d-block text-muted">Teks Terpilih:</small>
                            <strong id="selected-text-display" class="small"></strong>
                        </div>

                        {{-- Field selector --}}
                        <div id="field-selector-group" style="display:none;">
                            <label class="form-label small fw-semibold">Ganti dengan Field:</label>
                            <select id="field-selector" class="form-select form-select-sm mb-2">
                                <option value="">-- Pilih Field Database --</option>
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
                        <div id="mappings-list" style="max-height: 220px; overflow-y: auto;">
                            <p class="text-muted small text-center">Belum ada mapping</p>
                        </div>
                    </div>
                </div>

                {{-- STEP 3: Simpan --}}
                <div class="card" id="save-section" style="display:none;">
                    <div class="card-header text-black">
                        <h6 class="mb-0">3. Simpan Template</h6>
                    </div>
                    <div class="card-body">
                        <form id="formSave">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Nama Template <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="name" id="inputName" class="form-control form-control-sm"
                                    required placeholder="cth: Laporan Data Karyawan">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Kode Template <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="code" id="inputCode" class="form-control form-control-sm"
                                    required placeholder="cth: LDK">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Kategori</label>
                                <select name="category" class="form-select form-select-sm">
                                    <option value="karyawan">Karyawan</option>
                                    <option value="kegiatan">Kegiatan</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm w-100" id="btnSave">
                                <span id="btnSaveText">Simpan Template</span>
                                <span id="btnSaveSpinner" class="spinner-border spinner-border-sm d-none"></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://unpkg.com/docx-preview@0.3.0/dist/docx-preview.min.js"></script>

    <script>
        (function() {
            // ── State ──────────────────────────────────────────────────────────
            let currentFile = null;
            let currentSourceTable = 'karyawan';
            let currentSelection = null; // { text, range }
            let mappings = []; // [{ find, replace, wrapperEl }]

            const COLUMNS = @json($allowedColumns);

            // ── DOM refs ───────────────────────────────────────────────────────
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

            // ── Auto-generate code dari name ───────────────────────────────────
            document.getElementById('inputName').addEventListener('input', function() {
                const code = this.value
                    .split(' ')
                    .filter(w => w.length > 0)
                    .map(w => w[0].toUpperCase())
                    .join('');
                document.getElementById('inputCode').value = code;
            });

            // ── Step 1: Upload & Render ────────────────────────────────────────
            document.getElementById('formUpload').addEventListener('submit', async function(e) {
                e.preventDefault();

                const fileInput = document.getElementById('fileInput');
                currentFile = fileInput.files[0];
                currentSourceTable = document.getElementById('source_table_select').value;

                if (!currentFile) return;

                // Loading state
                document.getElementById('btnLoadText').textContent = 'Memuat...';
                document.getElementById('btnLoadSpinner').classList.remove('d-none');
                document.querySelector('#btnLoad').disabled = true;

                // Reset state
                mappings = [];
                currentSelection = null;
                updateMappingsList();

                try {
                    if (!window.docx || typeof window.docx.renderAsync !== 'function') {
                        throw new Error(
                            'Library docx-preview belum ter-load. Refresh halaman dan coba lagi.');
                    }

                    emptyState.style.display = 'none';
                    docxContainer.innerHTML =
                        '<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Memuat dokumen...</p></div>';

                    const arrayBuffer = await currentFile.arrayBuffer();

                    await window.docx.renderAsync(arrayBuffer, docxContainer, null, {
                        className: 'docx',
                        inWrapper: true,
                        ignoreWidth: false,
                        ignoreHeight: false,
                        breakPages: true,
                    });

                    // Aktifkan text selection listener
                    docxContainer.addEventListener('mouseup', onMouseUp);

                    // Populate field selector
                    populateFieldSelector(currentSourceTable);

                    mappingSection.style.display = 'block';
                    saveSection.style.display = 'block';
                    previewHint.style.display = 'inline';

                } catch (err) {
                    docxContainer.innerHTML = '<div class="alert alert-danger m-3">Gagal memuat file: ' +
                        err.message + '</div>';
                    console.error(err);
                } finally {
                    document.getElementById('btnLoadText').textContent = 'Load Dokumen';
                    document.getElementById('btnLoadSpinner').classList.add('d-none');
                    document.querySelector('#btnLoad').disabled = false;
                }
            });

            // ── Text Selection ─────────────────────────────────────────────────
            function onMouseUp(e) {
                // Abaikan klik pada badge placeholder
                if (e.target.classList.contains('placeholder-badge')) return;

                const sel = window.getSelection();
                const text = sel ? sel.toString().trim() : '';

                if (text.length < 2) {
                    // Tidak ada seleksi yang berarti — biarkan state tetap
                    return;
                }

                // Simpan range sebelum hilang
                const range = sel.getRangeAt(0).cloneRange();

                currentSelection = {
                    text,
                    range
                };

                // Tampilkan info
                selectionInfo.style.display = 'block';
                selectedTextDisplay.textContent = text.length > 60 ? text.substring(0, 60) + '…' : text;
                fieldSelectorGroup.style.display = 'block';
                noSelectionHint.style.display = 'none';

                // Hapus seleksi browser agar tidak bingung
                sel.removeAllRanges();
            }

            // ── Populate field selector ────────────────────────────────────────
            function populateFieldSelector(table) {
                const cols = COLUMNS[table] || {};
                fieldSelector.innerHTML = '<option value="">-- Pilih Field Database --</option>';
                for (const [key, label] of Object.entries(cols)) {
                    const opt = document.createElement('option');
                    opt.value = key;
                    opt.textContent = label + ' (' + key + ')';
                    fieldSelector.appendChild(opt);
                }
            }

            // ── Apply Mapping ──────────────────────────────────────────────────
            document.getElementById('btnApplyMapping').addEventListener('click', function() {
                if (!currentSelection) {
                    alert('Pilih teks di preview terlebih dahulu!');
                    return;
                }
                const field = fieldSelector.value;
                if (!field) {
                    alert('Pilih field database!');
                    return;
                }

                // Cek duplikasi field
                if (mappings.some(m => m.replace === field)) {
                    if (!confirm('Field "' + field + '" sudah di-mapping sebelumnya. Lanjutkan?')) return;
                }

                // Wrap teks terpilih dengan span visual
                let wrapperEl = null;
                try {
                    wrapperEl = document.createElement('span');
                    wrapperEl.className = 'text-mapped';
                    wrapperEl.dataset.field = field;

                    currentSelection.range.surroundContents(wrapperEl);

                    // Tambahkan badge
                    const badge = document.createElement('span');
                    badge.className = 'placeholder-badge';
                    badge.textContent = field;
                    wrapperEl.appendChild(badge);

                } catch (err) {
                    // surroundContents gagal jika cross-element — wrap teks saja
                    console.warn('surroundContents failed, using text mark only:', err);
                    wrapperEl = null;
                }

                mappings.push({
                    find: currentSelection.text,
                    replace: field,
                    el: wrapperEl,
                });

                updateMappingsList();
                clearSelection();
            });

            // ── Cancel selection ───────────────────────────────────────────────
            document.getElementById('btnCancelSelection').addEventListener('click', clearSelection);

            function clearSelection() {
                currentSelection = null;
                selectionInfo.style.display = 'none';
                fieldSelectorGroup.style.display = 'none';
                noSelectionHint.style.display = 'block';
                fieldSelector.value = '';
            }

            // ── Update mappings list UI ────────────────────────────────────────
            function updateMappingsList() {
                mappingCount.textContent = mappings.length;

                if (mappings.length === 0) {
                    mappingsList.innerHTML = '<p class="text-muted small text-center">Belum ada mapping</p>';
                    return;
                }

                mappingsList.innerHTML = '';
                mappings.forEach((m, idx) => {
                    const div = document.createElement('div');
                    div.className =
                        'mapping-item d-flex justify-content-between align-items-start border-bottom py-2';
                    div.innerHTML =
                        '<div class="text-truncate me-2" style="max-width:75%;" title="' + escHtml(m.find) +
                        '">' +
                        '<del class="text-muted">' + escHtml(m.find.length > 30 ? m.find.substring(0, 30) +
                            '…' : m.find) + '</del><br>' +
                        '<code class="text-success">{{ ' + escHtml(m.replace) + ' }}</code>' +
                        '</div>' +
                        '<button class="btn btn-sm btn-outline-danger py-0 px-1" onclick="removeMapping(' +
                        idx + ')">✕</button>';
                    mappingsList.appendChild(div);
                });
            }

            window.removeMapping = function(idx) {
                const m = mappings[idx];
                if (m.el && m.el.parentNode) {
                    // Kembalikan teks asli (hapus wrapper span)
                    const parent = m.el.parentNode;
                    while (m.el.firstChild && !m.el.firstChild.classList?.contains('placeholder-badge')) {
                        parent.insertBefore(m.el.firstChild, m.el);
                    }
                    parent.removeChild(m.el);
                }
                mappings.splice(idx, 1);
                updateMappingsList();
            };

            function escHtml(str) {
                const d = document.createElement('div');
                d.textContent = str;
                return d.innerHTML;
            }

            // ── Step 3: Save Template ──────────────────────────────────────────
            document.getElementById('formSave').addEventListener('submit', function(e) {
                e.preventDefault();

                if (mappings.length === 0) {
                    if (!confirm(
                            'Belum ada mapping. Template akan disimpan tanpa placeholder — teks tidak akan diganti saat generate. Lanjutkan?'
                            )) return;
                }

                const formData = new FormData(this);
                formData.append('template_file', currentFile);
                formData.append('source_table', currentSourceTable);

                mappings.forEach((m, idx) => {
                    formData.append('replacements[' + idx + '][find]', m.find);
                    formData.append('replacements[' + idx + '][replace]', m.replace);
                });

                const btn = document.getElementById('btnSave');
                document.getElementById('btnSaveText').textContent = 'Menyimpan...';
                document.getElementById('btnSaveSpinner').classList.remove('d-none');
                btn.disabled = true;

                fetch("{{ route('HR.reports.save.mapping') }}", {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    })
                    .then(async res => {
                        const text = await res.text();
                        const ct = res.headers.get('content-type') || '';

                        if (!ct.includes('application/json')) {
                            console.error('Non-JSON response:', text.substring(0, 500));
                            throw new Error('Server error (status ' + res.status +
                                '). Cek log Laravel.');
                        }

                        const data = JSON.parse(text);
                        if (!res.ok) throw data;
                        return data;
                    })
                    .then(data => {
                        if (data.success) {
                            alert('✅ ' + data.message);
                            window.location.href = "{{ route('HR.reports.index') }}";
                        } else {
                            alert('❌ ' + (data.message || 'Terjadi kesalahan'));
                        }
                    })
                    .catch(err => {
                        console.error('Save error:', err);
                        const msg = err.errors ?
                            Object.values(err.errors).flat().join('\n') :
                            (err.message || 'Terjadi kesalahan tidak terduga');
                        alert('❌ ' + msg);
                    })
                    .finally(() => {
                        document.getElementById('btnSaveText').textContent = '💾 Simpan Template';
                        document.getElementById('btnSaveSpinner').classList.add('d-none');
                        btn.disabled = false;
                    });
            });
        })();
    </script>
@endsection
