@extends('layouts_office.app')

@section('office_contents')
    {{-- @include('layouts_office.skeleton_tabs') --}}
    <div id="real-dashboard" class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 fw-bold">Rekap Certificate</h4>
            <button type="button" class="btn btn-primary hover-scale" data-bs-toggle="modal"
                data-bs-target="#modalTambahSertifikat" onclick="resetFormTambah()">
                <i class="bx bx-plus me-1"></i>Tambah Sertifikat
            </button>
        </div>

        <div class="modal fade" id="modalTambahSertifikat" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow-lg">
                    <form id="formTambahSertifikat" action="{{ route('office.certificate.storeSummary') }}" method="POST">
                        @csrf
                        <input type="hidden" name="_method" id="formMethodField" value="POST">
                        <input type="hidden" name="id" id="formIdField" value="">

                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold" id="modalTambahSertifikatTitle">
                                <i class="bx bx-certification text-primary me-2"></i>Tambah Sertifikat
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body pt-3">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">No. Sertifikat</label>
                                    <input type="text" name="no_sertifikat" class="form-control"
                                        placeholder="Contoh: 001/CERT/IX/2026">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Nama Peserta</label>
                                    <input type="text" name="nama_peserta" class="form-control" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Perusahaan</label>
                                    <select name="perusahaan" id="selectPerusahaan" class="form-control"
                                        style="width:100%" required>
                                        <option value=""></option>
                                        @foreach ($perusahaans as $perusahaan)
                                            <option value="{{ $perusahaan->nama_perusahaan }}">
                                                {{ $perusahaan->nama_perusahaan }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Materi</label>
                                    <select name="materi" id="selectMateri" class="form-control"
                                        style="width:100%" required>
                                        <option value=""></option>
                                        @foreach ($materis as $materi)
                                            <option value="{{ $materi->nama_materi }}">
                                                {{ $materi->nama_materi }} ({{ $materi->kode_materi ?? '-' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Awal Training</label>
                                    <input type="date" name="awal_training" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Akhir Training</label>
                                    <input type="date" name="akhir_training" class="form-control" required>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-medium">Type</label>
                                    <select name="type" class="form-select" required>
                                        <option value="">Pilih Type</option>
                                        <option value="Reg Digital">Reg Digital</option>
                                        <option value="Webinar">Webinar</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-medium">Keterangan</label>
                                    <textarea name="keterangan" class="form-control" rows="3"></textarea>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary hover-scale">
                                <i class="bx bx-save me-1"></i>Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Tahun</label>
                        <select id="filter-tahun" class="form-select">
                            @for ($y = date('Y'); $y >= 2022; $y--)
                                <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Tipe Periode</label>
                        <select id="filter-tipe" class="form-select">
                            <option value="year">Sepanjang tahun</option>
                            <option value="month" selected>Bulan</option>
                            <option value="quarter">Triwulan</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Detail</label>
                        <select id="filter-sub" class="form-select">
                            <option value="">Pilih...</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex">
                        <button class="btn btn-primary w-100" onclick="loadRekap()">Tampilkan</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4" id="summary-cards"></div>

        <ul class="nav nav-tabs mb-3" id="rekap-tabs" role="tablist"></ul>
        <div class="tab-content" id="rekap-tabs-content"></div>
    </div>

    
    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        const URL_REKAP = "{{ route('office.certificate.certificateSummaryJson') }}";
        const STORE_URL = "{{ route('office.certificate.storeSummary') }}";
        // Template URL update/delete, id-nya masih placeholder karena baru diketahui
        // saat runtime di JS. route() dipanggil dengan argumen posisional supaya
        // tetap benar walau nama parameter route bukan 'id'.
        const UPDATE_URL_TEMPLATE = "{{ route('office.certificate.updateSummary', 'ID_PLACEHOLDER') }}";
        const DELETE_URL_TEMPLATE = "{{ route('office.certificate.deleteSummary', 'ID_PLACEHOLDER') }}";

        function buildUpdateUrl(id) {
            return UPDATE_URL_TEMPLATE.replace('ID_PLACEHOLDER', id);
        }

        function buildDeleteUrl(id) {
            return DELETE_URL_TEMPLATE.replace('ID_PLACEHOLDER', id);
        }
        const PER_PAGE = 20;

        // Konfigurasi tiap kategori: key harus sama persis dengan key di response JSON.
        // 'actions: true' cuma dipasang di kategori yang datanya benar-benar row
        // CertificateSummary (regDigital, webinar) sehingga punya 'id' yang bisa
        // dikirim ke route updateSummary/deleteSummary. Kategori lain
        // (authorized/bnsp/inixcert/workshop) datanya berupa baris peserta hasil
        // pluck, bukan record yang diedit di sini.
        const CATEGORIES = [
            { key: 'regDigital', label: 'Reg Digital', color: '#0d6efd', actions: true },
            { key: 'authorized', label: 'Authorized', color: '#ffc107' },
            { key: 'bandung', label: 'Bandung', color: '#20c997' },
            { key: 'bnsp', label: 'BNSP', color: '#6f42c1' },
            { key: 'inixcert', label: 'Inixcert', color: '#fd7e14' },
            { key: 'workshop', label: 'Workshop', color: '#0dcaf0' },
            { key: 'webinar', label: 'Webinar', color: '#d63384', actions: true },
        ];

        // State per kategori: baris raw dari API + halaman aktif
        const tableState = {};
        CATEGORIES.forEach(c => { tableState[c.key] = { rows: [], page: 1 }; });

        let summaryChart = null;
        let formMode = 'create'; // 'create' | 'edit'

        $(function () {
            $('#selectPerusahaan').select2({
                dropdownParent: $('#modalTambahSertifikat'),
                placeholder: 'Pilih atau ketik perusahaan',
                allowClear: true,
                width: '100%',
                tags: true,
                createTag: function (params) {
                    const term = $.trim(params.term);
                    if (term === '') return null;
                    return { id: term, text: term, newTag: true };
                }
            });

            $('#selectMateri').select2({
                dropdownParent: $('#modalTambahSertifikat'),
                placeholder: 'Pilih atau ketik materi',
                allowClear: true,
                width: '100%',
                tags: true,
                createTag: function (params) {
                    const term = $.trim(params.term);
                    if (term === '') return null;
                    return { id: term, text: term, newTag: true };
                }
            });

            // Reset form & balik ke mode "Tambah" tiap modal ditutup
            $('#modalTambahSertifikat').on('hidden.bs.modal', function () {
                resetFormTambah();
            });

            $('#formTambahSertifikat').on('submit', handleFormSubmit);
        });

        // Set nilai select2 walau value-nya belum ada di daftar <option> (mis. saat
        // edit data lama yang perusahaan/materinya sudah tidak ada di master saat ini).
        // Option yang disisipkan ditandai data-dynamic="1" supaya bisa dibersihkan
        // lagi saat form direset, biar tidak menumpuk di dropdown selamanya.
        function setSelect2Value($select, value) {
            if (!value) {
                $select.val(null).trigger('change');
                return;
            }

            const exists = $select.find('option').filter(function () {
                return this.value === String(value);
            }).length > 0;

            if (!exists) {
                const newOption = new Option(value, value, true, true);
                $(newOption).attr('data-dynamic', '1');
                $select.append(newOption);
            }

            $select.val(value).trigger('change');
        }

        // Reset form ke mode tambah baru (dipanggil saat buka modal via tombol "Tambah Sertifikat")
        function resetFormTambah() {
            formMode = 'create';
            document.getElementById('modalTambahSertifikatTitle').innerHTML =
                '<i class="bx bx-certification text-primary me-2"></i>Tambah Sertifikat';
            document.getElementById('formTambahSertifikat').reset();
            document.getElementById('formIdField').value = '';
            document.getElementById('formMethodField').value = 'POST';

            // Buang option sementara yang disisipkan lewat setSelect2Value saat edit
            $('#selectPerusahaan option[data-dynamic="1"]').remove();
            $('#selectMateri option[data-dynamic="1"]').remove();

            $('#selectPerusahaan').val(null).trigger('change');
            $('#selectMateri').val(null).trigger('change');
        }

        // Buka modal dalam mode edit, isi form dari data row yang sudah ada di tableState
        function editSertifikat(key, id) {
            const row = (tableState[key].rows || []).find(r => r.id === id);
            if (!row) return;

            formMode = 'edit';
            document.getElementById('modalTambahSertifikatTitle').innerHTML =
                '<i class="bx bx-certification text-primary me-2"></i>Edit Sertifikat';
            document.getElementById('formIdField').value = row.id;
            document.getElementById('formMethodField').value = 'PUT';

            const form = document.getElementById('formTambahSertifikat');
            form.querySelector('[name="no_sertifikat"]').value = row.no_sertifikat || '';
            form.querySelector('[name="nama_peserta"]').value = row.nama_peserta || '';
            form.querySelector('[name="awal_training"]').value = row.awal_training ? String(row.awal_training).substring(0, 10) : '';
            form.querySelector('[name="akhir_training"]').value = row.akhir_training ? String(row.akhir_training).substring(0, 10) : '';
            form.querySelector('[name="type"]').value = row.type || '';
            form.querySelector('[name="keterangan"]').value = row.keterangan || '';

            setSelect2Value($('#selectPerusahaan'), row.perusahaan);
            setSelect2Value($('#selectMateri'), row.materi);

            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalTambahSertifikat')).show();
        }

        function deleteSertifikat(key, id) {
            if (!confirm('Yakin ingin menghapus data ini?')) return;

            fetch(buildDeleteUrl(id), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('#formTambahSertifikat input[name="_token"]').value,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then((res) => {
                    if (!res.ok) throw new Error('Gagal menghapus data');
                    loadRekap();
                })
                .catch((err) => alert(err.message));
        }

        function handleFormSubmit(e) {
            e.preventDefault();
            const form = e.target;

            const url = formMode === 'edit'
                ? buildUpdateUrl(document.getElementById('formIdField').value)
                : STORE_URL;

            fetch(url, {
                method: 'POST', // request fisik tetap POST, PUT di-spoof lewat field _method
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then((res) => {
                    if (!res.ok) throw new Error('Gagal menyimpan data');
                    return res.json().catch(() => null);
                })
                .then(() => {
                    window.location.reload();
                })
                .catch((err) => alert(err.message));
        }

        // Ambil nilai tampilan yang masuk akal dari 1 row, apapun bentuk modelnya:
        // - regDigital / webinar -> CertificateSummary (punya 'type', id, 'materi' teks)
        // - bandung -> Certificate
        // - authorized / bnsp / inixcert / workshop -> baris peserta (hasil pluck('peserta'))
        function extractDisplay(row) {
            const nama =
                row.nama_peserta || row.peserta_nama || row.nama || row.name ||
                row.no_sertifikat || row.nomor || row.kode || `#${row.id ?? '-'}`;

            // Untuk baris peserta: tampilkan email/no hp/instansi kalau ada.
            const info =
                row.email || row.no_hp || row.no_telp ||
                row.instansi || row.perusahaan ||
                (row.registrasi ? `${row.registrasi.length} peserta` : null) ||
                row.event || row.status || '-';

            return { nama, materi: extractMateri(row), info };
        }

        // Ambil nama materi dari berbagai bentuk row:
        // - CertificateSummary (regDigital, webinar): kolom 'materi' berupa teks
        // - ModelsEksam -> registexam (authorized): $peserta->materi berupa object relasi Materi
        // - fallback ke relasi rkm.materi kalau ada
        function extractMateri(row) {
            const materiRelasi = (row.materi && typeof row.materi === 'object')
                ? row.materi
                : (row.rkm && row.rkm.materi ? row.rkm.materi : null);

            if (materiRelasi) {
                return materiRelasi.nama_materi || materiRelasi.kode_materi || '-';
            }

            if (typeof row.materi === 'string' && row.materi.trim() !== '') {
                return row.materi;
            }

            if (typeof row.nama_materi === 'string' && row.nama_materi.trim() !== '') {
                return row.nama_materi;
            }

            return '-';
        }

        function formatDate(value) {
            if (!value) return null;
            const d = new Date(value);
            if (isNaN(d)) return null;
            return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        }

        // Tentukan kolom "Periode" berdasarkan sumber model row-nya, karena tiap
        // kategori pakai nama kolom tanggal yang berbeda:
        // - CertificateSummary (regDigital, webinar): awal_training / akhir_training
        // - ModelsEksam -> registexam (authorized, bnsp, inixcert): tanggal_exam (1 tanggal)
        // - Certificate (bandung): tanggal_pelatihan, sudah berbentuk "2026-02-23 - 2026-02-25"
        // - RKM (workshop): tanggal_awal / tanggal_akhir
        function formatPeriode(row) {
            if (row.awal_training || row.akhir_training) {
                const a = formatDate(row.awal_training);
                const b = formatDate(row.akhir_training);
                return (a && b) ? `${a} - ${b}` : (a || b || '-');
            }

            if (row.tanggal_mulai || row.tanggal_selesai) {
                const a = formatDate(row.tanggal_mulai);
                const b = formatDate(row.tanggal_selesai);
                return (a && b) ? `${a} - ${b}` : (a || b || '-');
            }

            // ModelsEksam -> registexam (authorized, bnsp, inixcert): tanggal_exam,
            // cuma 1 tanggal (bukan rentang mulai/selesai)
            if (row.tanggal_exam) {
                return formatDate(row.tanggal_exam) || '-';
            }

            if (row.tanggal_pelatihan) {
                const parts = String(row.tanggal_pelatihan).split(' - ').map((p) => formatDate(p.trim()) || p.trim());
                return parts.join(' - ');
            }

            if (row.tanggal_awal || row.tanggal_akhir) {
                const a = formatDate(row.tanggal_awal);
                const b = formatDate(row.tanggal_akhir);
                return (a && b) ? `${a} - ${b}` : (a || b || '-');
            }

            // Fallback untuk baris peserta yang masih membawa relasi rkm (kalau nanti
            // controller ikut menyertakan info periode dari parent-nya)
            if (row.rkm && (row.rkm.tanggal_awal || row.rkm.tanggal_akhir)) {
                const a = formatDate(row.rkm.tanggal_awal);
                const b = formatDate(row.rkm.tanggal_akhir);
                if (a && b) return `${a} - ${b}`;
            }

            return '-';
        }

        function populateSubFilter(tipe) {
            const sub = document.getElementById('filter-sub');
            sub.innerHTML = '<option value="">Pilih...</option>';

            if (tipe === 'quarter') {
                ['Triwulan 1 (Jan-Mar)', 'Triwulan 2 (Apr-Jun)', 'Triwulan 3 (Jul-Sep)', 'Triwulan 4 (Okt-Des)']
                    .forEach((label, index) => {
                        sub.innerHTML += `<option value="${index + 1}">${label}</option>`;
                    });
                sub.disabled = false;
                return;
            }

            if (tipe === 'month') {
                [
                    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                ].forEach((label, index) => {
                    sub.innerHTML += `<option value="${index + 1}">${label}</option>`;
                });
                const currentMonth = new Date().getMonth() + 1;
                sub.value = String(currentMonth);
                sub.disabled = false;
                return;
            }

            // tipe === 'year' -> tidak butuh detail
            sub.disabled = true;
        }

        document.getElementById('filter-tipe').addEventListener('change', function () {
            populateSubFilter(this.value);
        });

        function buildTabsSkeleton() {
            const tabsEl = document.getElementById('rekap-tabs');
            const contentEl = document.getElementById('rekap-tabs-content');

            let tabsHtml = '';
            let contentHtml = '';

            CATEGORIES.forEach((cat, idx) => {
                const active = idx === 0;
                const totalCols = cat.actions ? 6 : 5;
                const aksiTh = cat.actions ? '<th class="text-end">Aksi</th>' : '';

                tabsHtml += `
                    <li class="nav-item" role="presentation">
                        <button class="nav-link ${active ? 'active' : ''}" id="tab-${cat.key}-btn"
                            data-bs-toggle="tab" data-bs-target="#tab-${cat.key}" type="button" role="tab">
                            ${cat.label} <span class="badge bg-light text-dark ms-1" id="badge-${cat.key}">0</span>
                        </button>
                    </li>`;

                contentHtml += `
                    <div class="tab-pane fade ${active ? 'show active' : ''}" id="tab-${cat.key}" role="tabpanel">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Nama / Referensi</th>
                                                <th>Materi</th>
                                                <th>Kontak / Info</th>
                                                <th>Periode</th>
                                                ${aksiTh}
                                            </tr>
                                        </thead>
                                        <tbody id="tbody-${cat.key}">
                                            <tr><td colspan="${totalCols}" class="text-muted text-center py-4">Belum ada data</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <nav>
                                    <ul class="pagination pagination-sm justify-content-end flex-wrap mb-0 mt-2" id="pagination-${cat.key}"></ul>
                                </nav>
                            </div>
                        </div>
                    </div>`;
            });

            // Tab terakhir: ringkasan donut chart, cuma nampilin count per kategori
            tabsHtml += `
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-summary-btn" data-bs-toggle="tab" data-bs-target="#tab-summary" type="button" role="tab">
                        Ringkasan
                    </button>
                </li>`;

            contentHtml += `
                <div class="tab-pane fade" id="tab-summary" role="tabpanel">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-0 pt-3 pb-2">
                            <h6 class="mb-0 fw-bold">Perbandingan Jumlah per Kategori</h6>
                        </div>
                        <div class="card-body">
                            <div style="max-width: 420px; margin: 0 auto;">
                                <canvas id="chart-summary"></canvas>
                            </div>
                            <div class="text-muted small text-center py-4 d-none" id="chart-summary-empty">Tidak ada data</div>
                        </div>
                    </div>
                </div>`;

            tabsEl.innerHTML = tabsHtml;
            contentEl.innerHTML = contentHtml;
        }

        function buildSummaryCards(counts) {
            const total = CATEGORIES.reduce((sum, c) => sum + (counts[c.key] || 0), 0);
            const el = document.getElementById('summary-cards');

            let html = `
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body">
                            <div class="text-muted small">Total Sertifikat</div>
                            <div class="fs-3 fw-bold mt-2">${total}</div>
                        </div>
                    </div>
                </div>`;

            CATEGORIES.slice(0, 7).forEach((cat) => {
                html += `
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body">
                                <div class="text-muted small">${cat.label}</div>
                                <div class="fs-3 fw-bold mt-2" style="color:${cat.color}">${counts[cat.key] || 0}</div>
                            </div>
                        </div>
                    </div>`;
            });

            el.innerHTML = html;
        }

        function renderTable(key) {
            const state = tableState[key];
            const tbody = document.getElementById(`tbody-${key}`);
            const pager = document.getElementById(`pagination-${key}`);
            const catConfig = CATEGORIES.find(c => c.key === key) || {};
            const totalCols = catConfig.actions ? 6 : 5;

            if (state.rows.length === 0) {
                tbody.innerHTML = `<tr><td colspan="${totalCols}" class="text-muted text-center py-4">Tidak ada data</td></tr>`;
                pager.innerHTML = '';
                return;
            }

            const totalPages = Math.ceil(state.rows.length / PER_PAGE);
            state.page = Math.min(Math.max(state.page, 1), totalPages);

            const start = (state.page - 1) * PER_PAGE;
            const pageRows = state.rows.slice(start, start + PER_PAGE);

            tbody.innerHTML = pageRows.map((row, idx) => {
                const d = extractDisplay(row);
                const periode = formatPeriode(row);
                const aksiCell = catConfig.actions ? `
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick='editSertifikat("${key}", ${row.id})'>
                            <i class="bx bx-edit"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick='deleteSertifikat("${key}", ${row.id})'>
                            <i class="bx bx-trash"></i>
                        </button>
                    </td>` : '';

                return `
                    <tr>
                        <td>${start + idx + 1}</td>
                        <td>${d.nama}</td>
                        <td>${d.materi}</td>
                        <td>${d.info}</td>
                        <td>${periode}</td>
                        ${aksiCell}
                    </tr>`;
            }).join('');

            renderPagination(key, totalPages);
        }

        // Bangun daftar halaman yang ditampilkan: selalu halaman pertama & terakhir,
        // beberapa halaman di sekitar halaman aktif, sisanya diringkas jadi '...'.
        // Ini mencegah pagination meluber keluar card/table waktu total halaman
        // bisa lebih dari 50 (kalau ditampilkan penuh 1..50, tombolnya tidak muat).
        function buildPageList(current, total) {
            const delta = 2; // jumlah halaman tetangga kiri/kanan yang ditampilkan
            const pages = [];

            for (let i = 1; i <= total; i++) {
                if (i === 1 || i === total || (i >= current - delta && i <= current + delta)) {
                    pages.push(i);
                }
            }

            const withDots = [];
            let prev = null;
            pages.forEach((p) => {
                if (prev !== null) {
                    if (p - prev === 2) {
                        withDots.push(prev + 1);
                    } else if (p - prev > 2) {
                        withDots.push('...');
                    }
                }
                withDots.push(p);
                prev = p;
            });

            return withDots;
        }

        function renderPagination(key, totalPages) {
            const pager = document.getElementById(`pagination-${key}`);
            const state = tableState[key];

            if (totalPages <= 1) {
                pager.innerHTML = '';
                return;
            }

            let html = '';
            html += pageItem(key, state.page - 1, '&laquo;', state.page === 1);

            buildPageList(state.page, totalPages).forEach((p) => {
                if (p === '...') {
                    html += `<li class="page-item disabled"><span class="page-link">&hellip;</span></li>`;
                } else {
                    html += pageItem(key, p, p, false, p === state.page);
                }
            });

            html += pageItem(key, state.page + 1, '&raquo;', state.page === totalPages);
            pager.innerHTML = html;
        }

        function pageItem(key, targetPage, label, disabled, active = false) {
            const cls = ['page-item'];
            if (disabled) cls.push('disabled');
            if (active) cls.push('active');
            return `
                <li class="${cls.join(' ')}">
                    <a class="page-link" href="#" onclick="goToPage('${key}', ${targetPage}); return false;">${label}</a>
                </li>`;
        }

        function goToPage(key, page) {
            tableState[key].page = page;
            renderTable(key);
        }

        function renderSummaryChart(counts) {
            const canvas = document.getElementById('chart-summary');
            const emptyEl = document.getElementById('chart-summary-empty');
            const total = CATEGORIES.reduce((sum, c) => sum + (counts[c.key] || 0), 0);

            if (summaryChart) {
                summaryChart.destroy();
                summaryChart = null;
            }

            if (total === 0) {
                canvas.classList.add('d-none');
                emptyEl.classList.remove('d-none');
                return;
            }

            canvas.classList.remove('d-none');
            emptyEl.classList.add('d-none');

            summaryChart = new Chart(canvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: CATEGORIES.map(c => c.label),
                    datasets: [{
                        data: CATEGORIES.map(c => counts[c.key] || 0),
                        backgroundColor: CATEGORIES.map(c => c.color),
                        borderWidth: 0,
                    }],
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => `${ctx.label}: ${ctx.parsed}`,
                            },
                        },
                    },
                },
            });
        }

        function resetAll() {
            CATEGORIES.forEach(c => { tableState[c.key] = { rows: [], page: 1 }; });
            const counts = {};
            CATEGORIES.forEach(c => { counts[c.key] = 0; });
            buildSummaryCards(counts);
            CATEGORIES.forEach(c => {
                renderTable(c.key);
                document.getElementById(`badge-${c.key}`).textContent = 0;
            });
            renderSummaryChart(counts);
        }

        function loadRekap() {
            const params = new URLSearchParams();
            const tahun = document.getElementById('filter-tahun').value;
            const tipe = document.getElementById('filter-tipe').value; // year | month | quarter
            const sub = document.getElementById('filter-sub').value;

            params.append('year', tahun);
            params.append('period', tipe);
            if (tipe === 'month' && sub) params.append('month', sub);
            if (tipe === 'quarter' && sub) params.append('quarter', sub);

            fetch(`${URL_REKAP}?${params.toString()}`)
                .then((response) => response.json())
                .then((data) => {
                    const counts = {};

                    CATEGORIES.forEach((cat) => {
                        const section = data[cat.key] || { count: 0, data: [] };
                        counts[cat.key] = section.count || 0;
                        tableState[cat.key] = { rows: section.data || [], page: 1 };
                        document.getElementById(`badge-${cat.key}`).textContent = section.count || 0;
                        renderTable(cat.key);
                    });

                    buildSummaryCards(counts);
                    renderSummaryChart(counts);
                })
                .catch(() => resetAll());
        }

        buildTabsSkeleton();
        populateSubFilter(document.getElementById('filter-tipe').value);
        loadRekap();
    </script>
    </div> <!-- End of real-dashboard -->
@endsection