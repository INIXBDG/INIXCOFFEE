@extends('layouts_kpi.app')

@section('kpi_contents')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    {{-- ===== PINDAHKAN CDN KE PALING ATAS ===== --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    {{-- ===== STYLE KHUSUS HALAMAN INI ===== --}}
    <style>
        /* Page Header */
        .page-header-modern {
            margin-bottom: 1.5rem;
        }

        .page-header-modern .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .page-title-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(99, 102, 241, .1), rgba(139, 92, 246, .1));
            color: #6366f1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .breadcrumb-modern {
            background: transparent;
            padding: 0;
            margin: 0;
        }

        .breadcrumb-modern .breadcrumb-item {
            font-size: .85rem;
            color: #64748b;
        }

        .breadcrumb-modern .breadcrumb-item.active {
            color: #6366f1;
            font-weight: 500;
        }

        /* Content Card */
        .content-card {
            background: #fff;
            border-radius: 16px;
            border: 0;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .04);
        }

        .content-card .card-body {
            padding: 2rem;
        }

        /* Section Title */
        .section-title {
            font-size: 1rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .section-title i {
            color: #6366f1;
        }

        .section-title .badge-info {
            font-size: .7rem;
            background: rgba(99, 102, 241, .1);
            color: #6366f1;
            padding: .25rem .6rem;
            border-radius: 6px;
            font-weight: 600;
        }

        /* Dynamic Blocks */
        .karyawan-block,
        .form-kriteria-block {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            position: relative;
            transition: all .2s ease;
        }

        .karyawan-block:hover,
        .form-kriteria-block:hover {
            border-color: #cbd5e1;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .04);
        }

        .form-group-item {
            background: #fff;
            border: 1px solid #e2e8f0;
            padding: 1.25rem;
            border-radius: 10px;
            margin-bottom: .75rem;
            transition: all .2s ease;
        }

        .form-group-item:hover {
            border-color: #cbd5e1;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .03);
        }

        /* Form Controls */
        .form-control,
        .form-select {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            padding: .6rem 1rem;
            transition: all .2s ease;
            font-size: .9rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, .1);
        }

        .form-label {
            font-weight: 600;
            color: #334155;
            font-size: .85rem;
            margin-bottom: .5rem;
        }

        .form-text {
            font-size: .75rem;
        }

        /* Remove Button (Trash) */
        .btn-remove-block {
            position: absolute;
            top: .75rem;
            right: .75rem;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(239, 68, 68, .1);
            color: #ef4444;
            border: none;
            transition: all .2s ease;
            cursor: pointer;
            z-index: 10;
        }

        .btn-remove-block:hover {
            background: #ef4444;
            color: #fff;
            transform: scale(1.05);
        }

        /* Template Select */
        .template-select-wrapper {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.25rem;
        }

        .template-select-wrapper select {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }

        .template-select-wrapper select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, .1);
        }

        /* Info Box */
        .info-box {
            background: linear-gradient(135deg, rgba(99, 102, 241, .05), rgba(139, 92, 246, .05));
            border: 1px solid rgba(99, 102, 241, .15);
            border-radius: 10px;
            padding: .75rem 1rem;
            font-size: .85rem;
            color: #475569;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .info-box i {
            color: #6366f1;
        }

        /* Loading Spinner */
        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 6px solid transparent;
            border-top: 6px solid #a78bfa;
            border-right: 6px solid #38bdf8;
            border-bottom: 6px solid #34d399;
            border-left: 6px solid #facc15;
            border-radius: 50%;
            animation: spin 1.2s linear infinite;
            margin: auto;
        }

        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }

        /* Keterangan Tipe Section */
        .ket-tipe-section {
            background: #fff;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            padding: 1rem;
            margin-top: .75rem;
        }

        /* Input Group in Ket Tipe */
        .ket-tipe-wrapper .input-group {
            border-radius: 10px;
            overflow: hidden;
        }

        .ket-tipe-wrapper .input-group .form-control {
            border-radius: 0;
        }

        .ket-tipe-wrapper .input-group .form-control:first-child {
            border-radius: 10px 0 0 10px;
        }

        .ket-tipe-wrapper .input-group .btn {
            border-radius: 0 10px 10px 0;
        }

        /* Sub Kriteria Header */
        .sub-kriteria-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: .5rem;
        }

        .sub-kriteria-header .badge {
            background: rgba(99, 102, 241, .1);
            color: #6366f1;
            font-size: .75rem;
            padding: .25rem .6rem;
            border-radius: 6px;
            font-weight: 600;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .content-card .card-body {
                padding: 1.25rem;
            }

            .karyawan-block,
            .form-kriteria-block {
                padding: 1rem;
            }
        }
    </style>

    {{-- SweetAlert Notifications --}}
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: "{{ session('success') }}",
                confirmButtonColor: '#6366f1'
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: "{{ session('error') }}",
                confirmButtonColor: '#ef4444'
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                html: `{!! implode('<br>', $errors->all()) !!}`,
                confirmButtonColor: '#ef4444'
            });
        </script>
    @endif

    <div class="container content-wrapper mt-4">

        {{-- Back Button --}}
        <a href="{{ route('ketegoriKPI.get') }}" class="btn btn-light mb-3">
            <i class="mdi mdi-arrow-left"></i> Kembali ke Penilaian
        </a>

        {{-- Main Content Card --}}
        <div class="content-card">
            <div class="card-body">
                <h4 class="fw-bold text-dark mb-4 text-center">
                    <i class="fa-solid fa-file-circle-plus text-primary me-2"></i>
                    {{ __('Kategori Baru') }}
                </h4>

                <form action="{{ route('ketegori.kpi.store') }}" method="POST">
                    @csrf
                    @php
                        $divisiList = $data->pluck('divisi')->unique();
                    @endphp

                    <script>
                        const allKaryawan = @json($data);
                    </script>

                    <input type="hidden" name="template_quartal" id="template_quartal">
                    <input type="hidden" name="template_tahun" id="template_tahun">
                    <input type="hidden" name="template_nama_evaluator" id="template_nama_evaluator">
                    <input type="hidden" name="template_tanggal" id="template_tanggal">

                    {{-- ===== SECTION 1: JENIS FORM ===== --}}
                    <div class="mb-4">
                        <h5 class="section-title">
                            <i class="fa-solid fa-layer-group"></i> Jenis Form
                        </h5>
                        <div class="karyawan-block">
                            <button type="button" class="btn-remove-block remove-karyawan-block" title="Hapus">
                                <i class="mdi mdi-trash-can"></i>
                            </button>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Jenis Form <span class="text-danger">*</span></label>
                                    <select name="jenis_form" id="jenis_form" class="form-select divisi-select" required>
                                        <option selected disabled>Pilih Jenis Form</option>
                                        <option value="Rutin">Penilaian Rutin</option>
                                        <option value="Kontrak">Penilaian Kontrak</option>
                                        <option value="Probation">Penilaian Probation</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ===== SECTION 2: TEMPLATE PENILAIAN ===== --}}
                    <div class="mb-4">
                        <h5 class="section-title">
                            <i class="fa-solid fa-copy"></i> Pilih Template Penilaian
                            <span class="badge-info">Opsional</span>
                        </h5>
                        <div class="template-select-wrapper">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-9">
                                    <label class="form-label">Daftar Template</label>
                                    <select id="template-select" class="form-select" multiple size="6">
                                        <option value="" disabled>Memuat daftar template...</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" id="load-template-btn" class="btn btn-primary w-100">
                                        <i class="fa-solid fa-download"></i> Muat Template
                                    </button>
                                    <div class="info-box mt-2">
                                        <i class="fa-solid fa-info-circle"></i>
                                        <small>Tekan Ctrl/Cmd untuk memilih beberapa template sekaligus.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ===== SECTION 3: DAFTAR YANG DINILAI ===== --}}
                    <div id="form-karyawan" class="mb-4">
                        <h5 class="section-title">
                            <i class="fa-solid fa-users"></i> Daftar Yang Dinilai
                        </h5>
                        <div class="text-end mb-3">
                            <button type="button" class="btn btn-success" id="add-karyawan-block">
                                <i class="fa-solid fa-plus"></i> Tambah Yang Dinilai
                            </button>
                        </div>
                        <div class="karyawan-block">
                            <button type="button" class="btn-remove-block remove-karyawan-block" title="Hapus">
                                <i class="mdi mdi-trash-can"></i>
                            </button>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Divisi <span class="text-danger">*</span></label>
                                    <select name="divisi[]" class="form-select divisi-select" required>
                                        <option selected disabled>Pilih Divisi</option>
                                        @foreach ($divisiList as $div)
                                            <option value="{{ $div }}">{{ $div }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Yang Dinilai <span class="text-danger">*</span></label>
                                    <select name="nama_karyawan[]" class="form-select karyawan-select" required>
                                        <option selected disabled>Pilih Karyawan</option>
                                    </select>
                                    <input type="hidden" name="id_karyawan[]" class="id-karyawan">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ===== SECTION 4: KRITERIA PENILAIAN ===== --}}
                    <div id="kriteria-container">
                        <h5 class="section-title">
                            <i class="fa-solid fa-list-check"></i> Kriteria Penilaian
                        </h5>
                        <div class="form-kriteria-block" data-kriteria-index="0">
                            <button type="button" class="btn-remove-block remove-kriteria-block" title="Hapus Kriteria">
                                <i class="mdi mdi-trash-can"></i>
                            </button>

                            <div class="mb-3">
                                <label class="form-label">Nama Kriteria <span class="text-danger">*</span></label>
                                <input type="text" name="kriteria[0][nama_penilaian]"
                                    class="form-control nama-penilaian-input" placeholder="Masukan nama kriteria..."
                                    maxlength="250" title="Hanya huruf dan spasi, maksimal 250 karakter">
                                <small class="form-text text-muted">Maksimal 250 karakter.</small>
                            </div>

                            <div class="form-wrapper-sub-kriteria">
                                <div class="form-group-item" data-sub-kriteria-index="0">
                                    <button type="button" class="btn-remove-block remove-sub-kriteria-block"
                                        title="Hapus Sub Kriteria">
                                        <i class="mdi mdi-trash-can"></i>
                                    </button>

                                    <div class="sub-kriteria-header">
                                        <span class="badge">Sub Kriteria</span>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Judul Sub Kriteria <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="kriteria[0][sub_kriteria][0][judul_kategori]"
                                                maxlength="250" class="form-control"
                                                placeholder="Masukan sub kriteria..." required
                                                title="Maksimal 250 karakter">
                                            <small class="form-text text-muted">Maksimal 250 karakter.</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Tipe <span class="text-danger">*</span></label>
                                            <select name="kriteria[0][sub_kriteria][0][tipe_kategori]"
                                                class="form-select tipe-kategori" required>
                                                <option selected disabled>Pilih tipe</option>
                                                <option value="text">Teks</option>
                                                <option value="radio">Pilihan (Radio)</option>
                                                <option value="checkbox">Kotak Centang</option>
                                                <option value="number">Angka</option>
                                                <option value="range">Rentang</option>
                                                <option value="textarea">Teks Panjang (catatan evaluator)</option>
                                                <option value="select">Pilihan Dropdown</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="ket-tipe-section d-none mb-3">
                                        <label class="form-label fw-semibold">
                                            <i class="fa-solid fa-tags text-primary me-1"></i> Keterangan Tipe
                                        </label>
                                        <div class="ket-tipe-wrapper text-end">
                                            <div class="input-group mb-2">
                                                <input type="text" name="kriteria[0][sub_kriteria][0][ket_tipe][]"
                                                    class="form-control" placeholder="Masukkan keterangan tipe">
                                                <input type="text"
                                                    name="kriteria[0][sub_kriteria][0][nilai_ket_tipe][]"
                                                    class="form-control" placeholder="Nilai tipe...">
                                                <button type="button" class="btn btn-danger btn-sm remove-ket-tipe"><i
                                                        class="mdi mdi-trash-can"></i></button>
                                            </div>
                                            <button type="button" class="btn btn-success btn-sm add-ket-tipe">
                                                <i class="fa-solid fa-plus"></i> Tambah Keterangan
                                            </button>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Bobot <span class="text-danger">*</span></label>
                                            <input type="number" name="kriteria[0][sub_kriteria][0][bobot]"
                                                placeholder="Masukan bobot..." class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Level <span class="text-danger">*</span></label>
                                            <select name="kriteria[0][sub_kriteria][0][level]" class="form-select"
                                                required>
                                                <option selected disabled>Pilih</option>
                                                <option value="required">Harus Diisi</option>
                                                <option value="null">Tidak Harus</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end mt-3">
                                <button type="button" class="btn btn-success btn-sm add-sub-kriteria-block">
                                    <i class="fa-solid fa-plus"></i> Tambah Sub Kriteria
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-warning" id="add-kriteria-main-block">
                            <i class="fa-solid fa-plus"></i> Tambah Kriteria
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-save"></i> Simpan Semua
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content text-center" style="border-radius: 16px; border: none;">
                <div class="modal-body py-4">
                    <div class="loading-spinner"></div>
                    <p class="mt-3 mb-0 fw-semibold text-secondary">Memuat template...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let kriteriaMainIndex = 0;
            let subKriteriaIndexes = {};

            const templateSelect = document.getElementById('template-select');
            const loadTemplateBtn = document.getElementById('load-template-btn');
            const baseKriteriaBlock = document.querySelector('.form-kriteria-block[data-kriteria-index="0"]');

            // Load Template List
            fetch("{{ route('template.list') }}")
                .then(res => res.json())
                .then(data => {
                    templateSelect.innerHTML = '';
                    for (const [empName, templates] of Object.entries(data)) {
                        const optgroup = document.createElement('optgroup');
                        optgroup.label = `👤 ${empName}`;
                        templates.forEach(t => {
                            const opt = document.createElement('option');
                            opt.value = t.kode_form;
                            opt.textContent =
                                `${t.nama_penilaian} | ${t.quartal || ''} ${t.tahun || ''} | ${t.jenis_form || ''}`;
                            optgroup.appendChild(opt);
                        });
                        templateSelect.appendChild(optgroup);
                    }
                })
                .catch(() => {
                    templateSelect.innerHTML = '<option selected disabled>Gagal memuat template</option>';
                });

            // Render Template to Form
            function renderTemplateToForm(data) {
                const container = document.getElementById('kriteria-container');
                container.innerHTML =
                    '<h5 class="section-title"><i class="fa-solid fa-list-check"></i> Kriteria Penilaian</h5>';
                kriteriaMainIndex = 0;
                subKriteriaIndexes = {};

                data.kriteria.forEach((kData) => {
                    const kBlock = baseKriteriaBlock.cloneNode(true);
                    kBlock.setAttribute('data-kriteria-index', kriteriaMainIndex);

                    kBlock.querySelectorAll('input, select, textarea').forEach(el => {
                        const name = el.getAttribute('name');
                        if (name) {
                            const newName = name.replace(/kriteria\[\d+\]/g,
                                `kriteria[${kriteriaMainIndex}]`);
                            el.setAttribute('name', newName);
                        }
                        if (el.type !== 'hidden') {
                            if (el.tagName === 'SELECT') el.selectedIndex = 0;
                            else el.value = '';
                        }
                    });

                    const namaInput = kBlock.querySelector('.nama-penilaian-input');
                    if (namaInput) namaInput.value = kData.nama_penilaian || '';

                    const subWrapper = kBlock.querySelector('.form-wrapper-sub-kriteria');
                    subWrapper.innerHTML = '';
                    subKriteriaIndexes[kriteriaMainIndex] = -1;

                    kData.sub_kriteria.forEach((sData) => {
                        subKriteriaIndexes[kriteriaMainIndex]++;
                        const newSubIdx = subKriteriaIndexes[kriteriaMainIndex];

                        const subClone = baseKriteriaBlock.querySelector('.form-group-item')
                            .cloneNode(true);
                        subClone.setAttribute('data-sub-kriteria-index', newSubIdx);

                        subClone.querySelectorAll('input, select, textarea').forEach(el => {
                            let name = el.getAttribute('name');
                            if (name) {
                                name = name.replace(/kriteria\[\d+\]/g,
                                    `kriteria[${kriteriaMainIndex}]`);
                                name = name.replace(/\[sub_kriteria\]\[\d+\]/g,
                                    `[sub_kriteria][${newSubIdx}]`);
                                el.setAttribute('name', name);
                            }
                            if (el.type !== 'hidden') {
                                if (el.tagName === 'SELECT') el.selectedIndex = 0;
                                else el.value = '';
                            }
                        });

                        const judulInput = subClone.querySelector(
                            'input[name*="[judul_kategori]"]');
                        if (judulInput && sData.judul_kategori) judulInput.value = sData
                            .judul_kategori;

                        const tipeSelect = subClone.querySelector('.tipe-kategori');
                        if (tipeSelect && sData.tipe_kategori) tipeSelect.value = sData
                            .tipe_kategori;

                        const bobotInput = subClone.querySelector('input[name*="[bobot]"]');
                        if (bobotInput && sData.bobot) bobotInput.value = sData.bobot;

                        const levelSelect = subClone.querySelector('select[name*="[level]"]');
                        if (levelSelect && sData.level) levelSelect.value = sData.level;

                        const ketWrapper = subClone.querySelector('.ket-tipe-wrapper');
                        const ketSection = subClone.querySelector('.ket-tipe-section');
                        if (ketWrapper) ketWrapper.innerHTML = '';

                        const showKet = ['radio', 'select', 'checkbox'];
                        if (showKet.includes(sData.tipe_kategori)) {
                            if (ketSection) ketSection.classList.remove('d-none');
                            const ketArr = Array.isArray(sData.ket_tipe) ? sData.ket_tipe : [];
                            const nilaiArr = Array.isArray(sData.nilai_ket_tipe) ? sData
                                .nilai_ket_tipe : [];

                            if (ketArr.length > 0) {
                                ketArr.forEach((k, idx) => {
                                    const val = nilaiArr[idx] !== undefined ? nilaiArr[
                                        idx] : '';
                                    if (ketWrapper) {
                                        ketWrapper.insertAdjacentHTML('beforeend', `
                                            <div class="input-group mb-2">
                                                <input type="text" name="kriteria[${kriteriaMainIndex}][sub_kriteria][${newSubIdx}][ket_tipe][]" class="form-control" placeholder="Masukkan keterangan tipe" value="${k || ''}">
                                                <input type="text" name="kriteria[${kriteriaMainIndex}][sub_kriteria][${newSubIdx}][nilai_ket_tipe][]" class="form-control" placeholder="Nilai tipe..." value="${val || ''}">
                                                <button type="button" class="btn btn-danger btn-sm remove-ket-tipe"><i class="mdi mdi-trash-can"></i></button>
                                            </div>
                                        `);
                                    }
                                });
                            } else {
                                if (ketWrapper) {
                                    ketWrapper.insertAdjacentHTML('beforeend', `
                                        <div class="input-group mb-2">
                                            <input type="text" name="kriteria[${kriteriaMainIndex}][sub_kriteria][${newSubIdx}][ket_tipe][]" class="form-control" placeholder="Masukkan keterangan tipe">
                                            <input type="text" name="kriteria[${kriteriaMainIndex}][sub_kriteria][${newSubIdx}][nilai_ket_tipe][]" class="form-control" placeholder="Nilai tipe...">
                                            <button type="button" class="btn btn-danger btn-sm remove-ket-tipe"><i class="mdi mdi-trash-can"></i></button>
                                        </div>
                                    `);
                                }
                            }
                            if (ketWrapper) {
                                ketWrapper.insertAdjacentHTML('beforeend',
                                    `<button type="button" class="btn btn-success btn-sm add-ket-tipe mt-2"><i class="fa-solid fa-plus"></i> Tambah Keterangan</button>`
                                );
                            }
                        } else {
                            if (ketSection) ketSection.classList.add('d-none');
                        }
                        if (subWrapper) subWrapper.appendChild(subClone);
                    });

                    container.appendChild(kBlock);
                    bindDynamicSubKriteriaEvents(kBlock);
                    kriteriaMainIndex++;
                });
            }

            // Load Template Button
            loadTemplateBtn.addEventListener('click', () => {
                const selected = Array.from(templateSelect.selectedOptions).map(opt => opt.value);
                if (selected.length === 0) return Swal.fire({
                    icon: 'info',
                    title: 'Info',
                    text: 'Pilih minimal satu template.',
                    confirmButtonColor: '#6366f1'
                });

                const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
                loadingModal.show();

                if (selected.length === 1) {
                    fetch("{{ route('template.load', ':kode') }}".replace(':kode', selected[0]))
                        .then(res => res.json())
                        .then(data => {
                            if (data.error) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: data.error,
                                    confirmButtonColor: '#ef4444'
                                });
                                return;
                            }
                            renderTemplateToForm(data);
                            loadingModal.hide();
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: `Template "${data.nama_penilaian}" berhasil dimuat dengan ${data.kriteria.length} kriteria.`,
                                confirmButtonColor: '#6366f1'
                            });
                        })
                        .catch(() => {
                            loadingModal.hide();
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Gagal memuat template.',
                                confirmButtonColor: '#ef4444'
                            });
                        });
                } else {
                    Promise.all(selected.map(kode => fetch("{{ route('template.load', ':kode') }}".replace(
                            ':kode', kode)).then(r => r.json())))
                        .then(results => {
                            let mergedKriteria = [];
                            let baseInfo = results[0] || {};
                            results.forEach(data => {
                                if (!data.kriteria) return;
                                data.kriteria.forEach(k => {
                                    const existing = mergedKriteria.find(mk => mk
                                        .kode_kategori === k.kode_kategori);
                                    if (!existing) mergedKriteria.push(k);
                                    else {
                                        k.sub_kriteria.forEach(sk => {
                                            if (!existing.sub_kriteria.find(
                                                    es => es.judul_kategori ===
                                                    sk.judul_kategori)) existing
                                                .sub_kriteria.push(sk);
                                        });
                                    }
                                });
                            });
                            renderTemplateToForm({
                                jenis_form: baseInfo.jenis_form || '',
                                quartal: baseInfo.quartal || '',
                                tahun: baseInfo.tahun || '',
                                nama_penilaian: baseInfo.nama_penilaian || '',
                                nama_evaluator: baseInfo.nama_evaluator || '',
                                tanggal: baseInfo.tanggal || '',
                                kriteria: mergedKriteria
                            });
                            loadingModal.hide();
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: `${mergedKriteria.length} kriteria berhasil dimuat dari ${selected.length} template.`,
                                confirmButtonColor: '#6366f1'
                            });
                        })
                        .catch(() => {
                            loadingModal.hide();
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Gagal memuat template.',
                                confirmButtonColor: '#ef4444'
                            });
                        });
                }
            });

            // Form Submit Validation
            document.querySelector('form[action="{{ route('ketegori.kpi.store') }}"]').addEventListener('submit',
                function(e) {
                    const kriteriaBlocks = document.querySelectorAll(
                        '.form-kriteria-block[data-kriteria-index]');
                    kriteriaBlocks.forEach(block => {
                        const subItems = block.querySelectorAll(
                            '.form-group-item[data-sub-kriteria-index]');
                        subItems.forEach(sub => {
                            const judulInput = sub.querySelector(
                                'input[name$="[judul_kategori]"]');
                            if (judulInput && !judulInput.value.trim()) sub.remove();
                        });
                        const remainingSubs = block.querySelectorAll(
                            '.form-group-item[data-sub-kriteria-index]');
                        if (remainingSubs.length === 0) block.remove();
                    });

                    const finalKriteria = document.querySelectorAll(
                        '.form-kriteria-block[data-kriteria-index]');
                    let hasValidSub = false;
                    finalKriteria.forEach(block => {
                        const subs = block.querySelectorAll(
                            '.form-group-item[data-sub-kriteria-index]');
                        if (subs.length > 0) hasValidSub = true;
                    });

                    if (finalKriteria.length === 0 || !hasValidSub) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'warning',
                            title: 'Validasi',
                            text: 'Minimal 1 kriteria dengan 1 sub kriteria harus diisi.',
                            confirmButtonColor: '#6366f1'
                        });
                        return false;
                    }
                });

            // Bind Dynamic Sub Kriteria Events
            function bindDynamicSubKriteriaEvents(kriteriaBlock) {
                const subKriteriaItems = kriteriaBlock.querySelectorAll(
                    '.form-group-item[data-sub-kriteria-index]');
                subKriteriaItems.forEach(container => {
                    const tipeSelect = container.querySelector('.tipe-kategori');
                    const ketTipeSection = container.querySelector('.ket-tipe-section');
                    const ketTipeWrapper = container.querySelector('.ket-tipe-wrapper');
                    const addKeteranganBtn = container.querySelector('.add-ket-tipe');
                    const currentKriteriaIndex = kriteriaBlock.getAttribute('data-kriteria-index');
                    const currentSubKriteriaIndex = container.getAttribute('data-sub-kriteria-index');

                    function toggleKeterangan() {
                        const showTypes = ['checkbox', 'radio', 'select'];
                        if (showTypes.includes(tipeSelect.value)) ketTipeSection.classList.remove('d-none');
                        else ketTipeSection.classList.add('d-none');
                    }
                    if (tipeSelect) {
                        tipeSelect.onchange = toggleKeterangan;
                        toggleKeterangan();
                    }

                    if (addKeteranganBtn) {
                        addKeteranganBtn.onclick = () => {
                            ketTipeWrapper.insertAdjacentHTML('beforeend', `
                                <div class="input-group mb-2">
                                    <input type="text" name="kriteria[${currentKriteriaIndex}][sub_kriteria][${currentSubKriteriaIndex}][ket_tipe][]" class="form-control" placeholder="Masukkan keterangan tipe">
                                    <input type="text" name="kriteria[${currentKriteriaIndex}][sub_kriteria][${currentSubKriteriaIndex}][nilai_ket_tipe][]" class="form-control" placeholder="Nilai tipe...">
                                    <button type="button" class="btn btn-danger btn-sm remove-ket-tipe"><i class="mdi mdi-trash-can"></i></button>
                                </div>
                            `);
                        };
                    }

                    if (ketTipeWrapper) {
                        ketTipeWrapper.onclick = (e) => {
                            if (e.target.closest('.remove-ket-tipe')) {
                                const inputGroups = ketTipeWrapper.querySelectorAll('.input-group');
                                if (inputGroups.length > 1) e.target.closest('.input-group').remove();
                                else {
                                    const group = e.target.closest('.input-group');
                                    const inputs = group.querySelectorAll('input');
                                    inputs.forEach(input => input.value = '');
                                }
                            }
                        };
                    }
                });
            }

            if (baseKriteriaBlock) bindDynamicSubKriteriaEvents(baseKriteriaBlock);

            // Kriteria Container Event Delegation
            document.getElementById('kriteria-container').addEventListener('click', function(e) {
                if (e.target.classList.contains('add-sub-kriteria-block') || e.target.closest(
                        '.add-sub-kriteria-block')) {
                    const btn = e.target.classList.contains('add-sub-kriteria-block') ? e.target : e.target
                        .closest('.add-sub-kriteria-block');
                    const kriteriaBlock = btn.closest('.form-kriteria-block');
                    const currentKriteriaIndex = kriteriaBlock.getAttribute('data-kriteria-index');
                    const subWrapper = kriteriaBlock.querySelector('.form-wrapper-sub-kriteria');
                    const firstSub = kriteriaBlock.querySelector(
                        '.form-group-item[data-sub-kriteria-index]');
                    const clone = firstSub.cloneNode(true);

                    if (!subKriteriaIndexes[currentKriteriaIndex]) subKriteriaIndexes[
                        currentKriteriaIndex] = 0;
                    subKriteriaIndexes[currentKriteriaIndex]++;
                    const newSubIndex = subKriteriaIndexes[currentKriteriaIndex];

                    clone.setAttribute('data-sub-kriteria-index', newSubIndex);
                    clone.querySelectorAll('input, select').forEach(el => {
                        const name = el.getAttribute('name');
                        if (name) {
                            el.setAttribute('name', name.replace(/\[sub_kriteria\]\[\d+\]/,
                                `[sub_kriteria][${newSubIndex}]`).replace(
                                /\[kriteria\]\[\d+\]/, `[kriteria][${currentKriteriaIndex}]`
                            ));
                        }
                        if (el.tagName === 'SELECT') el.selectedIndex = 0;
                        else el.value = '';
                    });

                    const ketWrapper = clone.querySelector('.ket-tipe-wrapper');
                    ketWrapper.innerHTML = `
                        <div class="input-group mb-2">
                            <input type="text" name="kriteria[${currentKriteriaIndex}][sub_kriteria][${newSubIndex}][ket_tipe][]" class="form-control" placeholder="Masukkan keterangan tipe">
                            <input type="text" name="kriteria[${currentKriteriaIndex}][sub_kriteria][${newSubIndex}][nilai_ket_tipe][]" class="form-control" placeholder="Nilai tipe...">
                            <button type="button" class="btn btn-danger btn-sm remove-ket-tipe"><i class="mdi mdi-trash-can"></i></button>
                        </div>
                        <button type="button" class="btn btn-success btn-sm add-ket-tipe mt-2"><i class="fa-solid fa-plus"></i> Tambah Keterangan</button>
                    `;

                    subWrapper.appendChild(clone);
                    bindDynamicSubKriteriaEvents(kriteriaBlock);
                }

                if (e.target.closest('.remove-sub-kriteria-block')) {
                    const sub = e.target.closest('.form-group-item[data-sub-kriteria-index]');
                    const parent = sub.parentElement;
                    if (parent.querySelectorAll('.form-group-item[data-sub-kriteria-index]').length > 1) sub
                        .remove();
                }

                if (e.target.closest('.remove-kriteria-block')) {
                    const block = e.target.closest('.form-kriteria-block');
                    if (document.querySelectorAll('.form-kriteria-block').length > 1) block.remove();
                }
            });

            // Add Main Kriteria
            document.getElementById('add-kriteria-main-block').addEventListener('click', () => {
                const container = document.getElementById('kriteria-container');
                const clone = baseKriteriaBlock.cloneNode(true);
                subKriteriaIndexes[kriteriaMainIndex] = 0;
                clone.setAttribute('data-kriteria-index', kriteriaMainIndex);

                clone.querySelectorAll('[name^="kriteria[0]"]').forEach(el => {
                    const name = el.getAttribute('name');
                    if (name) el.setAttribute('name', name.replace(/kriteria\[0\]/g,
                        `kriteria[${kriteriaMainIndex}]`));
                    if (el.tagName === 'SELECT') el.selectedIndex = 0;
                    else el.value = '';
                });

                const subWrapper = clone.querySelector('.form-wrapper-sub-kriteria');
                const subItems = subWrapper.querySelectorAll('.form-group-item[data-sub-kriteria-index]');
                subItems.forEach((item, i) => {
                    if (i === 0) {
                        item.setAttribute('data-sub-kriteria-index', 0);
                        item.querySelectorAll('input, select').forEach(el => {
                            el.value = '';
                            if (el.tagName === 'SELECT') el.selectedIndex = 0;
                        });
                        const ketWrapper = item.querySelector('.ket-tipe-wrapper');
                        ketWrapper.innerHTML = `
                            <div class="input-group mb-2">
                                <input type="text" name="kriteria[${kriteriaMainIndex}][sub_kriteria][0][ket_tipe][]" class="form-control" placeholder="Masukkan keterangan tipe">
                                <input type="text" name="kriteria[${kriteriaMainIndex}][sub_kriteria][0][nilai_ket_tipe][]" class="form-control" placeholder="Nilai tipe...">
                                <button type="button" class="btn btn-danger btn-sm remove-ket-tipe"><i class="mdi mdi-trash-can"></i></button>
                            </div>
                            <button type="button" class="btn btn-success btn-sm add-ket-tipe mt-2"><i class="fa-solid fa-plus"></i> Tambah Keterangan</button>
                        `;
                    } else {
                        item.remove();
                    }
                });

                container.appendChild(clone);
                bindDynamicSubKriteriaEvents(clone);
                kriteriaMainIndex++;
            });

            // Karyawan Selection
            const allKaryawanData = @json($data);
            document.getElementById('form-karyawan').addEventListener('change', function(e) {
                if (e.target.classList.contains('divisi-select')) {
                    const selectedDivisi = e.target.value;
                    const karyawanSelect = e.target.closest('.karyawan-block').querySelector(
                        '.karyawan-select');
                    const idInput = e.target.closest('.karyawan-block').querySelector('.id-karyawan');
                    karyawanSelect.innerHTML = `<option selected disabled>Pilih Karyawan</option>`;
                    allKaryawanData.forEach(k => {
                        if (k.divisi === selectedDivisi) {
                            const opt = document.createElement('option');
                            opt.value = k.nama_lengkap;
                            opt.textContent = k.nama_lengkap;
                            opt.dataset.id = k.id;
                            karyawanSelect.appendChild(opt);
                        }
                    });
                    idInput.value = '';
                }
                if (e.target.classList.contains('karyawan-select')) {
                    const selectedOption = e.target.options[e.target.selectedIndex];
                    const idInput = e.target.closest('.karyawan-block').querySelector('.id-karyawan');
                    idInput.value = selectedOption.dataset.id || '';
                }
            });

            // Add Karyawan Block
            document.getElementById('add-karyawan-block').addEventListener('click', () => {
                const wrapper = document.getElementById('form-karyawan');
                const first = wrapper.querySelector('.karyawan-block');
                const clone = first.cloneNode(true);
                clone.querySelector('.divisi-select').selectedIndex = 0;
                clone.querySelector('.karyawan-select').innerHTML =
                    `<option selected disabled>Pilih Karyawan</option>`;
                clone.querySelector('.id-karyawan').value = '';
                wrapper.appendChild(clone);
            });

            // Remove Karyawan Block
            document.getElementById('form-karyawan').addEventListener('click', function(e) {
                if (e.target.closest('.remove-karyawan-block')) {
                    const block = e.target.closest('.karyawan-block');
                    if (document.querySelectorAll('.karyawan-block').length > 1) block.remove();
                }
            });
        });
    </script>
@endsection
