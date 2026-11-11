<style>
    .sm-overlay {
        position: fixed;
        top: 70px;
        left: 260px;
        right: 0;
        bottom: 0;
        z-index: 1045;
        display: none;
    }

    .sm-overlay.is-open {
        display: flex;
    }

    .sm-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, .55);
        backdrop-filter: blur(6px);
    }

    .sm-panel {
        position: relative;
        margin: 0 auto;
        width: 100%;
        max-width: 1440px;
        height: 100%;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, .35);
    }

    .sm-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .85rem 1.25rem;
        background: #fff;
        border-bottom: 1px solid #e2e8f0;
        flex-shrink: 0
    }

    .sm-x {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer
    }

    .sm-x:hover {
        background: #fee2e2;
        border-color: #fecaca;
        color: #dc2626
    }

    .sm-steps {
        display: flex;
        align-items: center;
        gap: .5rem
    }

    .sm-step {
        display: flex;
        align-items: center;
        gap: .4rem;
        opacity: .45;
        font-size: .85rem;
        font-weight: 500;
        color: #475569
    }

    .sm-step b {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #e2e8f0;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .8rem
    }

    .sm-step.active,
    .sm-step.done {
        opacity: 1
    }

    .sm-step.active b {
        background: #3b82f6;
        color: #fff;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, .2)
    }

    .sm-step.done b {
        background: #22c55e;
        color: #fff
    }

    .sm-line {
        width: 30px;
        height: 2px;
        background: #e2e8f0
    }

    .sm-body {
        flex: 1;
        display: flex;
        gap: 1rem;
        padding: 1rem 1.25rem;
        overflow: hidden
    }

    .sm-main {
        flex: 1.5;
        min-width: 0;
        overflow-y: auto;
        padding-right: 4px
    }

    .sm-side {
        flex: 1;
        min-width: 0;
        overflow: auto;
        background: #e9ecef;
        border-radius: 12px;
        padding: 10px
    }

    .sm-side .docx-wrapper {
        background: transparent !important;
        padding: 0 !important
    }

    .sm-side .docx-wrapper>section.docx {
        zoom: .72;
        margin-bottom: 10px !important;
        background: #fff;
        box-shadow: 0 1px 4px rgba(0, 0, 0, .12)
    }

    .sm-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        margin-bottom: 1rem;
        overflow: hidden
    }

    .sm-card-h {
        padding: .8rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        background: #fafbfc;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: .5rem
    }

    .sm-card-b {
        padding: 1rem
    }

    .sm-blank {
        background: #fef08a;
        border-radius: 3px;
        padding: 0 3px;
        font-family: monospace;
        color: #854d0e
    }

    .sm-mark {
        background: #fef08a;
        border-radius: 3px
    }

    .sm-table td {
        vertical-align: top;
        font-size: .85rem
    }

    .sm-orig {
        max-width: 300px;
        word-break: break-word
    }

    .sm-tip {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1e40af;
        border-radius: 10px;
        padding: .7rem .9rem;
        font-size: .82rem
    }

    .sm-chips {
        display: flex;
        flex-wrap: wrap;
        gap: .4rem;
        min-height: 44px;
        padding: .5rem;
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
        background: #f8fafc
    }

    .sm-chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .2rem .5rem;
        border-radius: 8px;
        font-size: .8rem;
        border: 1px solid #cbd5e1;
        background: #fff
    }

    .sm-chip.tok {
        background: #ede9fe;
        border-color: #c4b5fd;
        color: #5b21b6
    }

    .sm-chip a {
        cursor: pointer;
        color: #64748b;
        text-decoration: none;
        font-size: .7rem
    }

    .sm-chip a:hover {
        color: #dc2626
    }

    .sm-chip-in {
        border: 0;
        background: transparent;
        outline: 0;
        min-width: 30px;
        font-family: monospace
    }

    #smCfgModal,
    #smGuideModal {
        z-index: 1070 !important
    }

    @media (max-width:992px) {
        .sm-body {
            flex-direction: column;
            overflow-y: auto
        }

        .sm-side {
            display: none
        }

        .sm-step span {
            display: none
        }
    }

    @media (max-width: 1199.98px) {
        .sm-overlay {
            left: 0;
        }
    }

    @media (max-width: 992px) {
        .sm-overlay {
            top: 70px;
            left: 0;
        }

        .sm-body {
            flex-direction: column;
            overflow-y: auto;
        }

        .sm-side {
            display: none;
        }

        .sm-step span {
            display: none;
        }
    }
</style>

<div id="smOverlay" class="sm-overlay" aria-hidden="true">
    <div class="sm-backdrop"></div>
    <div class="sm-panel">
        <div class="sm-top">
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="sm-x" id="smClose" title="Tutup"><span class="iconify"
                        data-icon="mdi:close" style="font-size:1.4rem"></span></button>
                <div>
                    <h5 class="mb-0 fw-bold" id="smTitle">Buat Template Laporan</h5>
                    <small class="text-muted" id="smSub">Upload → Tinjau → Simpan</small>
                </div>
            </div>
            <div class="sm-steps">
                <div class="sm-step active" data-s="1"><b>1</b><span>Upload</span></div>
                <div class="sm-line"></div>
                <div class="sm-step" data-s="2"><b>2</b><span>Tinjau</span></div>
                <div class="sm-line"></div>
                <div class="sm-step" data-s="3"><b>3</b><span>Simpan</span></div>
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm" id="smOpenGuide">
                <span class="iconify me-1" data-icon="mdi:help-circle-outline"></span>Panduan
            </button>
        </div>

        <div class="sm-body">
            <div class="sm-main">
                {{-- TIP --}}
                <div class="sm-tip mb-3" id="smTip">
                    <div class="d-flex justify-content-between gap-2">
                        <div>
                            <strong>Cara mudah:</strong> di Word, ketik label lalu garis bawah, contoh
                            <code>Nama Karyawan : ____________</code> dan <code>Nomor Surat : ____________</code>.
                            Upload file-nya, sistem menebak isiannya. Anda tinggal mengecek.
                            <a href="#" id="smTipGuide">Lihat panduan lengkap</a>
                        </div>
                        <button type="button" class="btn-close" id="smTipClose" aria-label="Tutup"></button>
                    </div>
                </div>

                {{-- STEP 1 --}}
                <div class="sm-card" id="smUpload">
                    <div class="sm-card-h">
                        <h6 class="mb-0">1. Upload dokumen Word</h6>
                    </div>
                    <div class="sm-card-b">
                        <form id="smForm" class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">File DOCX <span
                                        class="text-danger">*</span></label>
                                <input type="file" id="smFile" class="form-control form-control-sm" accept=".docx"
                                    required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Data diambil dari</label>
                                <select id="smTable" class="form-select form-select-sm">
                                    <option value="karyawan">Karyawan</option>
                                    <option value="pelamar">Rekrutan</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-sm w-100" id="smScanBtn">
                                    <span id="smScanTxt">Pindai</span>
                                    <span id="smScanSpin" class="spinner-border spinner-border-sm d-none"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="sm-card d-none" id="smEditInfo">
                    <div class="sm-card-b small text-muted">
                        Mode edit. Field yang sudah terpasang aman: dokumen tidak diubah kecuali Anda memetakan garis
                        bawah baru di bawah.
                    </div>
                </div>

                {{-- FIELD TERPASANG (mode edit) --}}
                <div class="sm-card d-none" id="smFields">
                    <div class="sm-card-h">
                        <h6 class="mb-0">Field yang sudah terpasang</h6><span class="badge bg-secondary rounded-pill"
                            id="smFieldCount">0</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0 sm-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama tampil</th>
                                    <th>Jenis</th>
                                    <th>Pengaturan</th>
                                </tr>
                            </thead>
                            <tbody id="smFieldRows"></tbody>
                        </table>
                    </div>
                </div>

                {{-- STEP 2 --}}
                <div class="sm-card d-none" id="smReview">
                    <div class="sm-card-h">
                        <h6 class="mb-0" id="smReviewTitle">2. Tinjau hasil tebakan</h6>
                        <div class="small text-muted" id="smStats"></div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0 sm-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Di dokumen</th>
                                    <th>Diisi dengan</th>
                                    <th>Tebakan</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="smRows"></tbody>
                        </table>
                    </div>
                    <div class="sm-card-b small text-muted border-top">
                        Baris yang <b>dilewati</b> tidak diubah: garis bawahnya tetap ada di dokumen.
                    </div>
                </div>

                {{-- STEP 3 --}}
                <div class="sm-card d-none" id="smSave">
                    <div class="sm-card-h">
                        <h6 class="mb-0">3. Simpan template</h6>
                    </div>
                    <div class="sm-card-b">
                        <form id="smSaveForm" class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label small fw-semibold">Nama Template <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="smName" class="form-control form-control-sm" required
                                    placeholder="cth: Surat Keputusan Karyawan Tetap">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Kode <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="smCode" class="form-control form-control-sm" required
                                    placeholder="otomatis">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Kategori</label>
                                <select id="smCat" class="form-select form-select-sm">
                                    <option value="karyawan">Karyawan</option>
                                    <option value="pelamar">Rekrutan</option>
                                    <option value="management">Management</option>
                                    <option value="administrasi">Administrasi</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100" id="smSaveBtn">
                                    <span id="smSaveTxt">Simpan Template</span>
                                    <span id="smSaveSpin" class="spinner-border spinner-border-sm d-none"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="sm-side" id="smPreview">
                <div class="text-center text-muted py-5">
                    <span class="iconify mb-2" data-icon="mdi:file-document-outline"
                        style="font-size:3rem;opacity:.4"></span>
                    <p class="mb-0">Pratinjau dokumen muncul di sini.<br>Garis bawah ditandai kuning.</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL KONFIGURASI --}}
<div class="modal fade" id="smCfgModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="smCfgTitle">Atur isian</h5><button type="button" class="btn-close"
                    data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="smCfgBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="smCfgOk">Terapkan</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL PANDUAN --}}
<div class="modal fade" id="smGuideModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Panduan Membuat Template</h5><button type="button" class="btn-close"
                    data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body small">
                <h6 class="fw-bold">4 langkah saja</h6>
                <ol>
                    <li><b>Ketik di Word</b> seperti biasa. Tempat yang harus diisi, beri garis bawah minimal 3
                        karakter. Contoh: <code>Nama Karyawan : ____________</code></li>
                    <li><b>Upload</b> file .docx lalu klik <b>Pindai</b>.</li>
                    <li><b>Tinjau</b> tabel tebakan. Ubah pilihan di kolom <i>Diisi dengan</i> bila salah.</li>
                    <li>Isi nama template, klik <b>Simpan</b>. Selesai.</li>
                </ol>

                <h6 class="fw-bold mt-4">Arti kolom "Tebakan"</h6>
                <ul>
                    <li><span class="badge bg-success-subtle text-success">✅ Yakin</span> sudah pernah Anda pilih
                        sebelumnya / sangat cocok.</li>
                    <li><span class="badge bg-warning-subtle text-warning">🤔 Cek dulu</span> mirip, tolong pastikan
                        benar.</li>
                    <li><span class="badge bg-secondary-subtle text-secondary">❓ Belum dikenali</span> pilih sendiri,
                        sistem akan mengingatnya.</li>
                    <li><span class="badge bg-primary-subtle text-primary">Pilihan Anda</span> hasil pilihan manual.
                    </li>
                </ul>

                <h6 class="fw-bold mt-4">Pilihan "Diisi dengan"</h6>
                <ul>
                    <li><b>Kolom database</b>: diambil otomatis dari data karyawan/rekrutan yang dipilih saat generate.
                    </li>
                    <li><b>Rumus / Nomor Surat</b>: susun dari blok, misalnya <code>SK</code> + <code>/</code> + Tahun +
                        <code>/</code> + Bulan Romawi + <code>/</code> + Urutan 4 digit menjadi
                        <code>SK/2026/X/0001</code>. Nomor urut naik otomatis setiap generate.</li>
                    <li><b>Tanggal otomatis</b>: tanggal hari ini dengan format pilihan Anda.</li>
                    <li><b>Diisi manual</b>: muncul sebagai kolom isian saat generate laporan.</li>
                    <li><b>Lewati</b>: dibiarkan apa adanya.</li>
                </ul>

                <h6 class="fw-bold mt-4">Sistem belajar dari Anda</h6>
                <p>Setiap kali template disimpan, pilihan Anda diingat. Upload surat baru bertuliskan <i>"No. Surat :
                        ____"</i> dan rumus yang dulu Anda buat akan langsung disarankan.</p>

                <h6 class="fw-bold mt-4">Template lama aman</h6>
                <p>Saat mengedit template yang sudah ada, field lama tidak dihapus. Anda bisa mengubah nama tampil,
                    kolom sumber, atau rumusnya, dan memetakan garis bawah yang belum diisi.</p>

                <h6 class="fw-bold mt-4">Tips</h6>
                <ul>
                    <li>Tulis label di depan garis bawah, dalam satu baris.</li>
                    <li>Garis bawah titik-titik (<code>.....</code>) atau <code>……</code> juga terbaca.</li>
                    <li>Jika tidak ada isian terdeteksi, pastikan garis bawah minimal 3 karakter.</li>
                </ul>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-primary"
                    data-bs-dismiss="modal">Mengerti</button></div>
        </div>
    </div>
</div>

<script>
    (function() {
        const $ = id => document.getElementById(id);
        const H = s => String(s ?? '').replace(/[&<>"']/g, c => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        } [c]));
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const URLS = {
            scan: "{{ route('HR.reports.smart.scan') }}",
            save: "{{ route('HR.reports.smart.save') }}",
            data: id => "{{ route('HR.reports.smart.data', ['template' => '__ID__']) }}".replace('__ID__', id),
            update: id => "{{ route('HR.reports.smart.update', ['template' => '__ID__']) }}".replace('__ID__',
                id),
        };
        const S = {
            mode: 'create',
            id: null,
            file: null,
            table: 'karyawan',
            columns: {},
            items: [],
            fields: [],
            cfg: null,
            chips: [],
            codeTouched: false
        };

        const TYPE_LABEL = {
            formula: 'Rumus / Nomor Surat',
            auto_date: 'Tanggal otomatis',
            auth_field: 'Data user login',
            manual_text: 'Teks manual',
            manual_textarea: 'Teks panjang manual',
            manual_date: 'Tanggal manual',
            manual_number: 'Angka manual',
            manual_select: 'Dropdown manual',
            manual_checkbox: 'Checkbox manual',
        };
        const toast = (m, t) => (window.showToast ? window.showToast(m, t || 'success') : alert(m));
        const title = s => String(s || '').replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());

        // ------------------------------------------------------------ overlay
        function show() {
            $('smOverlay').classList.add('is-open');
            $('smOverlay').setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function hide() {
            $('smOverlay').classList.remove('is-open');
            $('smOverlay').setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        function setStep(n) {
            document.querySelectorAll('.sm-step').forEach(el => {
                const s = +el.dataset.s;
                el.classList.toggle('active', s === n);
                el.classList.toggle('done', s < n);
            });
        }

        function reset() {
            Object.assign(S, {
                mode: 'create',
                id: null,
                file: null,
                table: 'karyawan',
                columns: {},
                items: [],
                fields: [],
                cfg: null,
                codeTouched: false
            });
            $('smFile').value = '';
            $('smTable').value = 'karyawan';
            $('smName').value = '';
            $('smCode').value = '';
            $('smCat').value = 'karyawan';
            ['smReview', 'smSave', 'smFields', 'smEditInfo'].forEach(i => $(i).classList.add('d-none'));
            $('smUpload').classList.remove('d-none');
            $('smTitle').textContent = 'Buat Template Laporan';
            $('smSub').textContent = 'Upload → Tinjau → Simpan';
            $('smSaveTxt').textContent = 'Simpan Template';
            $('smReviewTitle').textContent = '2. Tinjau hasil tebakan';
            $('smPreview').innerHTML =
                '<div class="text-center text-muted py-5"><p class="mb-0">Pratinjau dokumen muncul di sini.<br>Garis bawah ditandai kuning.</p></div>';
            $('smTip').classList.toggle('d-none', localStorage.getItem('sm_tip_closed') === '1');
            setStep(1);
        }
        window.openCreateOverlay = function() {
            reset();
            show();
            if (localStorage.getItem('sm_guide_seen') !== '1') {
                localStorage.setItem('sm_guide_seen', '1');
                openGuide();
            }
        };
        window.closeCreateOverlay = hide;

        function openGuide() {
            bootstrap.Modal.getOrCreateInstance($('smGuideModal')).show();
        }

        // ------------------------------------------------------------ preview
        async function renderPreview(buf) {
            const box = $('smPreview');
            box.innerHTML = '';
            try {
                await window.docx.renderAsync(buf, box, null, {
                    className: 'docx',
                    inWrapper: true,
                    breakPages: true
                });
                const w = document.createTreeWalker(box, NodeFilter.SHOW_TEXT);
                const nodes = [];
                while (w.nextNode()) nodes.push(w.currentNode);
                nodes.forEach(n => {
                    if (!/_{3,}|\.{5,}|…{2,}/.test(n.nodeValue)) return;
                    const span = document.createElement('span');
                    span.innerHTML = H(n.nodeValue).replace(/(_{3,}|\.{5,}|…{2,})/g,
                        '<span class="sm-mark">$1</span>');
                    n.parentNode.replaceChild(span, n);
                });
            } catch (e) {
                box.innerHTML =
                    '<div class="alert alert-warning m-2">Pratinjau tidak bisa ditampilkan, tapi pemindaian tetap berjalan.</div>';
            }
        }

        // ------------------------------------------------------------ items
        function mkItem(b) {
            const p = b.prediction;
            const it = {
                uid: b.id,
                original: b.original_text,
                blank: b.blank_part,
                context: b.context,
                suggestedKey: b.suggested_key,
                type: 'skip',
                key: null,
                config: null,
                confidence: 0,
                origin: null
            };
            if (p && p.confidence >= 0.5) {
                it.confidence = p.confidence;
                it.origin = p.source;
                it.type = p.type;
                if (p.type === 'db') it.key = p.key;
                else {
                    it.key = b.suggested_key;
                    it.config = p.config || {};
                }
            }
            return it;
        }
        const curVal = it => it.type === 'skip' ? 'skip' : (it.type === 'db' ? 'db:' + it.key : 'type:' + it.type);
        const labelOf = it => (it.config && it.config.label) || title(it.context);

        function badge(it) {
            if (it.origin === 'manual')
            return '<span class="badge bg-primary-subtle text-primary">Pilihan Anda</span>';
            if (it.type === 'skip')
            return '<span class="badge bg-secondary-subtle text-secondary">❓ Belum dikenali</span>';
            if (it.confidence >= 0.85) return '<span class="badge bg-success-subtle text-success">✅ Yakin</span>' +
                (it.origin === 'history' ?
                    '<div class="text-muted" style="font-size:.7rem">dari riwayat</div>' : '');
            return '<span class="badge bg-warning-subtle text-warning">🤔 Cek dulu</span>';
        }

        function summary(it) {
            if (it.type === 'db') return 'Kolom <code>' + H(it.key) + '</code>';
            if (it.type === 'formula') return '<code>' + H(it.config?.template || '') + '</code> → ' + H(fPreview(it
                .config?.template, it.config?.last_number));
            if (it.type === 'skip') return '';
            return H(TYPE_LABEL[it.type] || it.type) + (it.config?.label ? ': ' + H(it.config.label) : '');
        }

        function optionsHtml(it) {
            const cur = curVal(it);
            const o = (v, l) => `<option value="${v}" ${cur === v ? 'selected' : ''}>${l}</option>`;
            let h = o('skip', '— Lewati (biarkan garis bawah) —');
            h += '<optgroup label="Diambil dari database">' + Object.entries(S.columns).map(([k, l]) => o('db:' + k,
                H(l))).join('') + '</optgroup>';
            h += '<optgroup label="Otomatis">' + o('type:formula', '🔢 Rumus / Nomor Surat') + o('type:auto_date',
                '📅 Tanggal otomatis') + o('type:auth_field', '👤 Data user login') + '</optgroup>';
            h += '<optgroup label="Diisi manual saat generate">' + ['manual_text', 'manual_textarea', 'manual_date',
                'manual_number', 'manual_select', 'manual_checkbox'
            ].map(t => o('type:' + t, TYPE_LABEL[t])).join('') + '</optgroup>';
            return h;
        }

        function origHtml(it) {
            return it.original.split(it.blank).map(H).join('<span class="sm-blank">' + H(it.blank) + '</span>');
        }

        function renderRows() {
            const body = $('smRows');
            if (!S.items.length) {
                body.innerHTML =
                    '<tr><td colspan="4" class="text-center text-muted py-4">Tidak ada garis bawah (<code>____</code>) yang perlu dipetakan. Buka <a href="#" class="sm-open-guide">Panduan</a> jika dokumen Anda seharusnya punya isian.</td></tr>';
            } else {
                body.innerHTML = S.items.map((it, i) =>
                    `<tr>
                <td class="sm-orig">${origHtml(it)}</td>
                <td style="min-width:230px"><select class="form-select form-select-sm sm-sel" data-i="${i}">${optionsHtml(it)}</select><div class="small text-muted mt-1">${summary(it)}</div></td>
                <td>${badge(it)}</td>
                <td class="text-end">${(it.type !== 'skip' && it.type !== 'db') ? `<button type="button" class="btn btn-sm btn-outline-secondary sm-cfg" data-i="${i}">Atur</button>` : ''}</td></tr>`
                    ).join('');
            }
            const mapped = S.items.filter(i => i.type !== 'skip').length;
            $('smStats').textContent = S.items.length + ' isian · ' + mapped + ' dipetakan · ' + (S.items.length -
                mapped) + ' dilewati';
        }

        function onSelect(i, val) {
            const it = S.items[i];
            const prev = {
                type: it.type,
                key: it.key,
                config: it.config,
                origin: it.origin,
                confidence: it.confidence
            };
            if (val === 'skip') {
                it.type = 'skip';
                it.key = null;
                it.config = null;
                it.origin = 'manual';
                renderRows();
                return;
            }
            if (val.startsWith('db:')) {
                it.type = 'db';
                it.key = val.slice(3);
                it.config = null;
                it.origin = 'manual';
                renderRows();
                return;
            }
            const t = val.slice(5);
            const keep = it.type === t;
            it.type = t;
            it.key = it.suggestedKey;
            if (!keep) it.config = null;
            openConfig({
                type: t,
                config: it.config,
                label: labelOf(it)
            }, cfg => {
                it.config = cfg;
                it.origin = 'manual';
                renderRows();
            }, () => {
                Object.assign(it, prev);
                renderRows();
            });
        }

        // ------------------------------------------------------------ config modal
        const DF = {
            day: [
                ['none', 'Tidak ditampilkan'],
                ['number', 'Angka (25)'],
                ['word', 'Kata (Dua Puluh Lima)'],
                ['word_upper', 'KATA (DUA PULUH LIMA)'],
                ['day_name', 'Nama hari (Kamis)'],
                ['day_name_upper', 'NAMA HARI (KAMIS)']
            ],
            month: [
                ['none', 'Tidak ditampilkan'],
                ['number', 'Angka (06)'],
                ['month_name', 'Nama bulan (Juni)'],
                ['month_name_upper', 'NAMA BULAN (JUNI)']
            ],
            year: [
                ['none', 'Tidak ditampilkan'],
                ['number', 'Angka (2026)'],
                ['word', 'Kata (Dua Ribu Dua Puluh Enam)'],
                ['word_upper', 'KATA (DUA RIBU DUA PULUH ENAM)']
            ],
        };
        const sel = (id, opts, val) =>
            `<select id="${id}" class="form-select form-select-sm">${opts.map(([v, l]) => `<option value="${v}" ${v === val ? 'selected' : ''}>${l}</option>`).join('')}</select>`;
        const fld = (l, inner) =>
            `<div class="mb-3"><label class="form-label small fw-semibold">${l}</label>${inner}</div>`;
        const dateHtml = c => fld('Hari / Tanggal', sel('c_day', DF.day, c.day_format || 'number')) + fld('Bulan',
            sel('c_month', DF.month, c.month_format || 'month_name')) + fld('Tahun', sel('c_year', DF.year, c
            .year_format || 'number')) + fld('Pemisah antar bagian',
            `<input id="c_sep" class="form-control form-control-sm" value="${H(c.separator ?? ' ')}">`);
        const ROM = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        const BLN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September',
            'Oktober', 'November', 'Desember'
        ];

        function romanOf(n) {
            const m = [
                [1000, 'M'],
                [900, 'CM'],
                [500, 'D'],
                [400, 'CD'],
                [100, 'C'],
                [90, 'XC'],
                [50, 'L'],
                [40, 'XL'],
                [10, 'X'],
                [9, 'IX'],
                [5, 'V'],
                [4, 'IV'],
                [1, 'I']
            ];
            let r = '';
            m.forEach(([v, s]) => {
                while (n >= v) {
                    r += s;
                    n -= v;
                }
            });
            return r;
        }

        function fPreview(tpl, last) {
            const d = new Date();
            const n = (parseInt(last) || 0) + 1;
            return String(tpl || '').replace(/\{([a-z_:0-9]+)\}/gi, (m, v) => {
                if (v === 'tahun') return d.getFullYear();
                if (v === 'bulan') return String(d.getMonth() + 1).padStart(2, '0');
                if (v === 'bulan_romawi') return ROM[d.getMonth() + 1];
                if (v === 'bulan_nama') return BLN[d.getMonth()];
                if (v === 'tanggal') return String(d.getDate()).padStart(2, '0');
                const u = v.match(/^urutan:(\d+)$/);
                if (u) return String(n).padStart(+u[1], '0');
                if (v === 'urutan') return n;
                if (v === 'urutan_romawi') return romanOf(n);
                return m;
            });
        }
        const TOKS = [
            ['tahun', 'Tahun'],
            ['bulan', 'Bulan (angka)'],
            ['bulan_romawi', 'Bulan Romawi'],
            ['bulan_nama', 'Nama Bulan'],
            ['tanggal', 'Tanggal'],
            ['urutan:3', 'Urutan 3 digit'],
            ['urutan:4', 'Urutan 4 digit'],
            ['urutan_romawi', 'Urutan Romawi']
        ];
        const tokLabel = v => (TOKS.find(t => t[0] === v) || [v, v])[1];
        const PRESETS = ['SK/{tahun}/{bulan_romawi}/{urutan:4}', '{urutan:3}/HRD/{bulan_romawi}/{tahun}',
            'PKWT/{tahun}/{urutan:4}'
        ];
        const tplToChips = t => String(t || '').split(/(\{[a-z_:0-9]+\})/i).filter(x => x !== '').map(x =>
            /^\{.+\}$/.test(x) ? {
                t: 'tok',
                v: x.slice(1, -1)
            } : {
                t: 'text',
                v: x
            });
        const chipsToTpl = () => S.chips.map(c => c.t === 'tok' ? '{' + c.v + '}' : c.v).join('');

        function renderChips() {
            $('fChips').innerHTML = S.chips.length ? S.chips.map((c, i) =>
                `<span class="sm-chip ${c.t}">${c.t === 'tok' ? '<b>' + H(tokLabel(c.v)) + '</b>' : `<input class="sm-chip-in" data-ci="${i}" value="${H(c.v)}" size="${Math.max(2, c.v.length)}">`}<a data-act="l" data-ci="${i}">◀</a><a data-act="r" data-ci="${i}">▶</a><a data-act="x" data-ci="${i}">✕</a></span>`
                ).join('') : '<span class="text-muted small">Belum ada blok. Klik blok di bawah.</span>';
            updPrev();
        }

        function updPrev() {
            const el = $('fPrev');
            if (el) el.textContent = fPreview(chipsToTpl(), $('c_last')?.value) || '—';
        }

        function cfgHtml(type, c, label) {
            const L = fld('Nama tampil / label',
                `<input id="c_label" class="form-control form-control-sm" value="${H(label)}">`);
            if (type === 'formula') {
                S.chips = tplToChips(c.template);
                return L + `<label class="form-label small fw-semibold">Susun rumus dengan blok</label>
                <div class="sm-chips" id="fChips"></div>
                <div class="small text-muted mt-2 mb-1">Tambah blok:</div>
                <div class="d-flex flex-wrap gap-1 mb-2">${TOKS.map(t => `<button type="button" class="btn btn-sm btn-outline-primary" data-tok="${t[0]}">${t[1]}</button>`).join('')}</div>
                <div class="d-flex flex-wrap gap-1 mb-3"><button type="button" class="btn btn-sm btn-outline-secondary" data-text="SK">+ Teks</button><button type="button" class="btn btn-sm btn-outline-secondary" data-text="/">/</button><button type="button" class="btn btn-sm btn-outline-secondary" data-text="-">-</button><button type="button" class="btn btn-sm btn-outline-secondary" data-text=".">.</button></div>
                <div class="small text-muted mb-1">Contoh siap pakai:</div>
                <div class="d-flex flex-wrap gap-1 mb-3">${PRESETS.map((p, i) => `<button type="button" class="btn btn-sm btn-light border" data-preset="${i}"><code>${H(p)}</code></button>`).join('')}</div>
                ${fld('Nomor terakhir yang sudah dipakai (opsional)', `<input id="c_last" type="number" min="0" class="form-control form-control-sm" value="${H(c.last_number ?? '')}" placeholder="cth: 233, nomor berikutnya jadi 234">`)}
                <div class="alert alert-light border small mb-0">Hasil contoh: <b id="fPrev"></b></div>`;
            }
            if (type === 'auto_date') return L + dateHtml(c);
            if (type === 'auth_field') return L + fld('Data yang diambil',
                `<select id="c_field" class="form-select form-select-sm"><optgroup label="Data User Login">${['username', 'jabatan'].map(f => `<option ${c.field === f ? 'selected' : ''}>${f}</option>`).join('')}</optgroup><optgroup label="Data Karyawan (Login)">${['nama_lengkap', 'nip', 'email', 'whatsapp'].map(f => `<option ${c.field === f ? 'selected' : ''}>${f}</option>`).join('')}</optgroup></select>`
                );
            if (type === 'manual_text') return L + fld('Nilai awal (opsional)',
                `<input id="c_def" class="form-control form-control-sm" value="${H(c.default ?? '')}">`);
            if (type === 'manual_textarea') return L + fld('Jumlah baris',
                `<input id="c_rows" type="number" min="2" max="10" class="form-control form-control-sm" value="${H(c.rows ?? 3)}">`
                );
            if (type === 'manual_date') return L + dateHtml(c);
            if (type === 'manual_number') return L + fld('Tipe angka', sel('c_ntype', [
                ['number', 'Angka biasa (1.234,56)'],
                ['currency', 'Mata uang (Rp 1.234)'],
                ['integer', 'Bulat (1234)']
            ], c.number_type || 'number'));
            if (type === 'manual_select') return L + fld('Pilihan (pisahkan dengan koma)',
                `<textarea id="c_opts" rows="3" class="form-control form-control-sm" placeholder="Aktif, Nonaktif, Cuti">${H((c.options || []).join(', '))}</textarea>`
                );
            if (type === 'manual_checkbox') return L;
            return L;
        }

        function cfgCollect(type, base) {
            const label = $('c_label').value.trim();
            const cfg = Object.assign({}, base || {}, {
                label: label
            });
            const date = () => ({
                day_format: $('c_day').value,
                month_format: $('c_month').value,
                year_format: $('c_year').value,
                separator: $('c_sep').value
            });
            if (type === 'formula') {
                const t = chipsToTpl().trim();
                if (!t) throw new Error('Rumus masih kosong. Tambahkan blok dulu.');
                const ls = $('c_last').value.trim();
                Object.assign(cfg, {
                    template: t,
                    last_number: ls === '' ? null : parseInt(ls)
                });
            } else if (type === 'auto_date' || type === 'manual_date') Object.assign(cfg, date());
            else if (type === 'auth_field') cfg.field = $('c_field').value;
            else if (type === 'manual_text') cfg.default = $('c_def').value.trim();
            else if (type === 'manual_textarea') cfg.rows = parseInt($('c_rows').value) || 3;
            else if (type === 'manual_number') cfg.number_type = $('c_ntype').value;
            else if (type === 'manual_select') {
                cfg.options = $('c_opts').value.split(/[,\n]/).map(s => s.trim()).filter(Boolean);
                if (!cfg.options.length) throw new Error('Isi minimal satu pilihan.');
            }
            return cfg;
        }

        function openConfig(target, onOk, onCancel) {
            S.cfg = {
                target,
                onOk,
                onCancel,
                done: false
            };
            $('smCfgTitle').textContent = 'Atur: ' + (TYPE_LABEL[target.type] || target.type);
            $('smCfgBody').innerHTML = cfgHtml(target.type, target.config || {}, target.label || '');
            if (target.type === 'formula') renderChips();
            bootstrap.Modal.getOrCreateInstance($('smCfgModal')).show();
        }
        $('smCfgOk').addEventListener('click', () => {
            const c = S.cfg;
            if (!c) return;
            try {
                const cfg = cfgCollect(c.target.type, c.target.config);
                c.done = true;
                c.onOk(cfg);
                bootstrap.Modal.getOrCreateInstance($('smCfgModal')).hide();
            } catch (e) {
                alert(e.message);
            }
        });
        $('smCfgModal').addEventListener('hidden.bs.modal', () => {
            const c = S.cfg;
            if (c && !c.done && c.onCancel) c.onCancel();
            S.cfg = null;
        });
        $('smCfgBody').addEventListener('click', e => {
            const b = e.target.closest('[data-tok],[data-text],[data-preset],[data-act]');
            if (!b) return;
            if (b.dataset.tok) S.chips.push({
                t: 'tok',
                v: b.dataset.tok
            });
            else if (b.dataset.text !== undefined) S.chips.push({
                t: 'text',
                v: b.dataset.text
            });
            else if (b.dataset.preset !== undefined) S.chips = tplToChips(PRESETS[+b.dataset.preset]);
            else if (b.dataset.act) {
                const i = +b.dataset.ci;
                if (b.dataset.act === 'x') S.chips.splice(i, 1);
                if (b.dataset.act === 'l' && i > 0)[S.chips[i - 1], S.chips[i]] = [S.chips[i], S.chips[i -
                    1]];
                if (b.dataset.act === 'r' && i < S.chips.length - 1)[S.chips[i + 1], S.chips[i]] = [S.chips[
                    i], S.chips[i + 1]];
            }
            renderChips();
        });
        $('smCfgBody').addEventListener('input', e => {
            if (e.target.classList.contains('sm-chip-in')) {
                S.chips[+e.target.dataset.ci].v = e.target.value;
                e.target.size = Math.max(2, e.target.value.length);
                updPrev();
            }
            if (e.target.id === 'c_last') updPrev();
        });

        // ------------------------------------------------------------ fields (edit mode)
        function renderFields() {
            $('smFieldCount').textContent = S.fields.length;
            $('smFieldRows').innerHTML = S.fields.map((f, i) => {
                const isDb = !f.is_manual && f.field_type === 'text';
                const editable = ['auto_date', 'formula', 'auth_field', 'manual_text', 'manual_textarea',
                    'manual_date', 'manual_number', 'manual_select', 'manual_checkbox'
                ].includes(f.field_type);
                let ctl = '<span class="text-muted small">Tidak diubah dari sini</span>';
                if (isDb) ctl =
                    `<select class="form-select form-select-sm sm-fsrc" data-i="${i}">${Object.entries(S.columns).map(([k, l]) => `<option value="${k}" ${f.source_column === k ? 'selected' : ''}>${H(l)}</option>`).join('')}</select>`;
                else if (editable) ctl =
                    `<button type="button" class="btn btn-sm btn-outline-secondary sm-fcfg" data-i="${i}">Atur</button> <span class="small text-muted">${f.field_type === 'formula' ? '<code>' + H(f.config?.template || '') + '</code>' : ''}</span>`;
                return `<tr><td><input class="form-control form-control-sm sm-flabel" data-i="${i}" value="${H(f.label)}"><div class="small text-muted"><code>${H(f.key)}</code></div></td><td class="small">${isDb ? 'Kolom database' : H(TYPE_LABEL[f.field_type] || f.field_type)}</td><td>${ctl}</td></tr>`;
            }).join('');
        }

        // ------------------------------------------------------------ events
        $('smRows').addEventListener('change', e => {
            if (e.target.classList.contains('sm-sel')) onSelect(+e.target.dataset.i, e.target.value);
        });
        $('smRows').addEventListener('click', e => {
            const b = e.target.closest('.sm-cfg');
            if (b) {
                const it = S.items[+b.dataset.i];
                const prev = JSON.stringify(it.config);
                openConfig({
                    type: it.type,
                    config: it.config,
                    label: labelOf(it)
                }, cfg => {
                    it.config = cfg;
                    it.origin = 'manual';
                    renderRows();
                }, () => {
                    it.config = JSON.parse(prev);
                });
            }
            if (e.target.closest('.sm-open-guide')) {
                e.preventDefault();
                openGuide();
            }
        });
        $('smFieldRows').addEventListener('input', e => {
            if (e.target.classList.contains('sm-flabel')) {
                const f = S.fields[+e.target.dataset.i];
                f.label = e.target.value;
                f.dirty = true;
            }
        });
        $('smFieldRows').addEventListener('change', e => {
            if (e.target.classList.contains('sm-fsrc')) {
                const f = S.fields[+e.target.dataset.i];
                f.source_column = e.target.value;
                f.dirty = true;
            }
        });
        $('smFieldRows').addEventListener('click', e => {
            const b = e.target.closest('.sm-fcfg');
            if (!b) return;
            const f = S.fields[+b.dataset.i];
            openConfig({
                type: f.field_type,
                config: f.config || {},
                label: f.label
            }, cfg => {
                f.config = cfg;
                f.label = cfg.label || f.label;
                f.dirty = true;
                renderFields();
            }, () => {});
        });
        $('smClose').addEventListener('click', hide);
        $('smOpenGuide').addEventListener('click', openGuide);
        $('smTipGuide').addEventListener('click', e => {
            e.preventDefault();
            openGuide();
        });
        $('smTipClose').addEventListener('click', () => {
            $('smTip').classList.add('d-none');
            localStorage.setItem('sm_tip_closed', '1');
        });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && $('smOverlay').classList.contains('is-open') && !document
                .querySelector('.modal.show')) hide();
        });
        ['btnOpenCreateModal', 'btnOpenCreateModalEmpty'].forEach(id => $(id)?.addEventListener('click', window
            .openCreateOverlay));
        $('smName').addEventListener('input', function() {
            if (!S.codeTouched && S.mode === 'create') $('smCode').value = this.value.split(/\s+/).filter(
                Boolean).map(w => w[0].toUpperCase()).join('');
        });
        $('smCode').addEventListener('input', () => {
            S.codeTouched = true;
        });

        // ------------------------------------------------------------ scan
        $('smForm').addEventListener('submit', async e => {
            e.preventDefault();
            const f = $('smFile').files[0];
            if (!f) return alert('Pilih file DOCX dulu.');
            S.file = f;
            S.table = $('smTable').value;
            $('smCat').value = S.table;
            $('smScanTxt').textContent = 'Memindai...';
            $('smScanSpin').classList.remove('d-none');
            $('smScanBtn').disabled = true;
            try {
                const fd = new FormData();
                fd.append('template_file', f);
                fd.append('source_table', S.table);
                fd.append('_token', CSRF);
                const res = await fetch(URLS.scan, {
                    method: 'POST',
                    body: fd,
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const j = await res.json().catch(() => null);
                if (!res.ok || !j?.success) throw new Error(j?.message || (j?.errors ? Object.values(j
                    .errors).flat().join('\n') : 'Gagal memindai dokumen'));
                S.columns = j.columns || {};
                S.items = j.data.map(mkItem);
                await renderPreview(await f.arrayBuffer());
                renderRows();
                $('smReview').classList.remove('d-none');
                $('smSave').classList.remove('d-none');
                setStep(2);
                $('smReview').scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                const auto = S.items.filter(i => i.type !== 'skip').length;
                if (S.items.length) toast('Ditemukan ' + S.items.length + ' isian, ' + auto +
                    ' berhasil ditebak.');
            } catch (err) {
                alert(err.message);
            } finally {
                $('smScanTxt').textContent = 'Pindai';
                $('smScanSpin').classList.add('d-none');
                $('smScanBtn').disabled = false;
            }
        });

        // ------------------------------------------------------------ edit mode
        window.openEditOverlay = async function(id) {
            reset();
            S.mode = 'edit';
            S.id = id;
            show();
            $('smTitle').textContent = 'Memuat...';
            try {
                const res = await fetch(URLS.data(id), {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const j = await res.json();
                if (!j.success) throw new Error(j.message || 'Gagal memuat template');
                const d = j.data;
                S.table = d.source_table;
                S.columns = d.columns || {};
                S.fields = d.fields.map(f => Object.assign({
                    dirty: false
                }, f));
                S.items = d.blanks.map(mkItem);
                $('smTitle').textContent = 'Edit Template: ' + d.name;
                $('smSub').textContent = 'Field lama aman, Anda bisa memetakan isian yang tersisa';
                $('smName').value = d.name || '';
                $('smCode').value = d.code || '';
                $('smCat').value = d.category || 'karyawan';
                S.codeTouched = true;
                $('smSaveTxt').textContent = 'Simpan Perubahan';
                $('smUpload').classList.add('d-none');
                $('smEditInfo').classList.remove('d-none');
                $('smFields').classList.remove('d-none');
                $('smSave').classList.remove('d-none');
                $('smReviewTitle').textContent = 'Isian yang belum dipetakan';
                renderFields();
                renderRows();
                $('smReview').classList.toggle('d-none', S.items.length === 0);
                setStep(2);
                if (d.template_url) {
                    const r = await fetch(d.template_url);
                    if (r.ok) await renderPreview(await r.arrayBuffer());
                }
            } catch (err) {
                alert('Gagal membuka editor: ' + err.message);
                hide();
            }
        };

        // ------------------------------------------------------------ save
        $('smSaveForm').addEventListener('submit', async e => {
            e.preventDefault();
            const mapped = S.items.filter(i => i.type !== 'skip');
            const skipped = S.items.length - mapped.length;
            if (S.mode === 'create' && !S.file) return alert('Upload dan pindai dokumen dulu.');
            if (S.mode === 'create' && mapped.length === 0 && !confirm(
                    'Belum ada isian yang dipetakan. Tetap simpan?')) return;
            if (skipped > 0 && !confirm(skipped +
                    ' isian dilewati dan akan dibiarkan seperti semula (garis bawah). Lanjutkan?'))
                return;

            const items = mapped.map(i => ({
                find: i.original,
                blank: i.blank,
                key: i.key,
                type: i.type,
                config: i.config,
                label: i.type === 'db' ? null : labelOf(i),
                context: i.context
            }));
            const fd = new FormData();
            fd.append('name', $('smName').value.trim());
            fd.append('code', $('smCode').value.trim());
            fd.append('category', $('smCat').value);
            fd.append('items', JSON.stringify(items));
            fd.append('_token', CSRF);
            let url;
            if (S.mode === 'create') {
                url = URLS.save;
                fd.append('template_file', S.file);
                fd.append('source_table', S.table);
            } else {
                url = URLS.update(S.id);
                fd.append('fields', JSON.stringify(S.fields.filter(f => f.dirty).map(f => ({
                    key: f.key,
                    label: f.label,
                    source_column: f.source_column,
                    config: f.config
                }))));
            }

            $('smSaveTxt').textContent = 'Menyimpan...';
            $('smSaveSpin').classList.remove('d-none');
            $('smSaveBtn').disabled = true;
            setStep(3);
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    body: fd,
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': CSRF
                    }
                });
                const j = await res.json().catch(() => null);
                if (!res.ok || !j?.success) throw new Error(j?.message || (j?.errors ? Object.values(j
                    .errors).flat().join('\n') : 'Server error (' + res.status + ')'));
                toast(j.message);
                hide();
                setTimeout(() => window.location.reload(), 700);
            } catch (err) {
                alert(err.message);
                setStep(2);
            } finally {
                $('smSaveTxt').textContent = S.mode === 'create' ? 'Simpan Template' :
                    'Simpan Perubahan';
                $('smSaveSpin').classList.add('d-none');
                $('smSaveBtn').disabled = false;
            }
        });
    })();
</script>
