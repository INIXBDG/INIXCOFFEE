@extends('databasekpi.berandaKPI')

@section('contentKPI')
    <style>
        .karyawan-block,
        .form-kriteria-block {
            background-color: #f8f9fa;
        }

        .form-group-item {
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            padding: 1rem;
            border-radius: 0.5rem;
        }

        label.form-label {
            font-weight: 500;
            font-size: 0.9rem;
        }

        button.btn-sm {
            font-size: 0.8rem;
        }

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
    </style>
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: "{{ session('success') }}",
                customClass: {
                    confirmButton: 'btn btn-gradient-info me-3',
                },
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: "{{ session('error') }}",
                confirmButtonColor: '#d33',
                customClass: {
                    cancelButton: 'btn btn-gradient-danger'
                },
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                html: `{!! implode('<br>', $errors->all()) !!}`,
                confirmButtonColor: '#d33',
                customClass: {
                    cancelButton: 'btn btn-gradient-danger'
                },
            });
        </script>
    @endif
    <div class="content-wrapper">
        <div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="loading-spinner"></div>
            </div>
        </div>
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-primary text-white me-2">
                    <i class="mdi mdi-hospital"></i>
                </span> Penilaian
            </h3>
            <nav aria-label="breadcrumb">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item active" aria-current="page">
                        <span></span>Tambah Penilaian <i
                            class="mdi mdi-alert-circle-outline icon-sm text-primary align-middle" data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="Buat Form penilaian 360° untuk karyawan. beberapa input memiliki limit text!."></i>
                    </li>
                </ul>
            </nav>
        </div>
        <div class="row">
            <div class="col">
                <a href="{{ route('ketegoriKPI.get') }}" class="btn btn-primary mb-2">
                    <i class="mdi mdi-arrow-left"></i> Penilaian
                </a>

                <div class="row justify-content-center">
                    <div class="col-md-12">
                        <div class="card shadow-lg border-0 card-rounded bg-gradient-light text-dark">
                            <div class="card-body" id="card">
                                <h5 class="card-title text-center mb-4">{{ __('Kategori Baru') }}</h5>

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

                                    <div class="mb-4">
                                        <h5>Jenis Form</h5>
                                        <div class="border rounded p-3 karyawan-block mb-3 mt-1">
                                            <div class="row g-2 mt-2">
                                                <div class="text-end" style="margin-left: 2vw; margin-top: -2.2vw;">
                                                    <button type="button"
                                                        class="btn btn-danger btn-sm remove-karyawan-block"><i
                                                            class="mdi mdi-trash-can"></i></button>
                                                </div>
                                                <div class="col">
                                                    <label class="form-label">Jenis Form</label>
                                                    <select name="jenis_form" id="jenis_form"
                                                        class="form-select divisi-select" required>
                                                        <option selected disabled>Pilih Jenis Form</option>
                                                        <option value="Rutin">Penilaian Rutin</option>
                                                        <option value="Kontrak">Penilaian Kontrak</option>
                                                        <option value="Probation">Penilaian Probation</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <h5>Pilih Template Penilaian</h5>
                                        <div class="row g-2">
                                            <div class="col-md-12">
                                                <select id="template-select" class="form-select" multiple size="6">
                                                    <option value="" disabled>Memuat daftar template...</option>
                                                </select>
                                                <small class="text-muted">Tekan Ctrl/Cmd untuk memilih beberapa template
                                                    sekaligus.</small>
                                            </div>
                                            <div class="col-md-12 d-flex align-items-end">
                                                <button type="button" id="load-template-btn"
                                                    class="btn btn-secondary mb-2">Muat Template</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="form-karyawan" class="mb-4">
                                        <h5>Daftar Yang Dinilai</h5>
                                        <div class="text-right">
                                            <button type="button" class="btn btn-success mb-2" id="add-karyawan-block">+
                                                Tambah Yang Dinilai</button>
                                        </div>
                                        <div class="border rounded p-3 karyawan-block mb-3 mt-1">
                                            <div class="row g-2 mt-2">
                                                <div class="text-end" style="margin-left: 2vw; margin-top: -2.2vw;">
                                                    <button type="button"
                                                        class="btn btn-danger btn-sm remove-karyawan-block"><i
                                                            class="mdi mdi-trash-can"></i></button>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Divisi</label>
                                                    <select name="divisi[]" class="form-select divisi-select" required>
                                                        <option selected disabled>Pilih Divisi</option>
                                                        @foreach ($divisiList as $div)
                                                            <option value="{{ $div }}">{{ $div }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Yang Dinilai</label>
                                                    <select name="nama_karyawan[]" class="form-select karyawan-select"
                                                        required>
                                                        <option selected disabled>Pilih Dinilai</option>
                                                    </select>
                                                    <input type="hidden" name="id_karyawan[]" class="id-karyawan">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="kriteria-container">
                                        <h5>Kriteria Penilaian</h5>
                                        <div class="form-kriteria-block border rounded p-3 mb-4 bg-theme"
                                            data-kriteria-index="0">
                                            <div class="text-end" style="margin-right: -2vw; margin-top: -2vw;">
                                                <button type="button"
                                                    class="btn btn-danger btn-sm remove-kriteria-block"><i
                                                        class="mdi mdi-trash-can"></i></button>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Nama Kriteria</label>
                                                <input type="text" name="kriteria[0][nama_penilaian]"
                                                    class="form-control nama-penilaian-input"
                                                    placeholder="Masukan nama kriteria..." maxlength="250"
                                                    title="Hanya huruf dan spasi, maksimal 250 karakter">
                                                <small id="kriteriaHelp" class="form-text text-muted">limit text itu tidak
                                                    lebih dari 250 kata.</small>
                                            </div>
                                            <div class="form-wrapper-sub-kriteria">
                                                <div class="form-group-item p-3 border rounded mb-4 bg-theme"
                                                    data-sub-kriteria-index="0">
                                                    <div class="text-end" style="margin-right: -1vw; margin-top: -2vw;">
                                                        <button type="button"
                                                            class="btn btn-danger btn-sm remove-sub-kriteria-block"><i
                                                                class="mdi mdi-trash-can"></i></button>
                                                    </div>
                                                    <div class="row g-2 mb-2">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Sub Kriteria</label>
                                                            <input type="text"
                                                                name="kriteria[0][sub_kriteria][0][judul_kategori]"
                                                                maxlength="250" class="form-control"
                                                                placeholder="masukan sub kriteria..." required
                                                                title="Hanya huruf dan spasi, maksimal 250 karakter">
                                                            <small id="kriteriaHelp" class="form-text text-muted">limit
                                                                text itu tidak lebih dari 250 kata.</small>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Tipe</label>
                                                            <select name="kriteria[0][sub_kriteria][0][tipe_kategori]"
                                                                class="form-select tipe-kategori" required>
                                                                <option selected disabled>Pilih tipe</option>
                                                                <option value="text">Teks</option>
                                                                <option value="radio">Pilihan (Radio)</option>
                                                                <option value="checkbox">Kotak Centang</option>
                                                                <option value="number">Angka</option>
                                                                <option value="range">Rentang</option>
                                                                <option value="textarea">Teks Panjang (untuk catatan
                                                                    evaluator)</option>
                                                                <option value="select">Pilihan Dropdown</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="ket-tipe-section d-none mb-3">
                                                        <label class="form-label">Keterangan Tipe</label>
                                                        <div class="ket-tipe-wrapper text-end">
                                                            <div class="input-group mb-2">
                                                                <input type="text"
                                                                    name="kriteria[0][sub_kriteria][0][ket_tipe][]"
                                                                    class="form-control"
                                                                    placeholder="Masukkan keterangan tipe">
                                                                <input type="text"
                                                                    name="kriteria[0][sub_kriteria][0][nilai_ket_tipe][]"
                                                                    class="form-control" placeholder="Nilai tipe...">
                                                                <button type="button"
                                                                    class="btn btn-danger btn-sm remove-ket-tipe"><i
                                                                        class="mdi mdi-trash-can"></i></button>
                                                            </div>
                                                            <button type="button"
                                                                class="btn btn-success text-end add-ket-tipe">Tambah
                                                                Keterangan</button>
                                                        </div>
                                                    </div>
                                                    <div class="row g-2 mb-2">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Bobot</label>
                                                            <input type="number"
                                                                name="kriteria[0][sub_kriteria][0][bobot]"
                                                                placeholder="masukan bobot..." class="form-control"
                                                                required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Level</label>
                                                            <select name="kriteria[0][sub_kriteria][0][level]"
                                                                class="form-select" required>
                                                                <option selected disabled>Pilih</option>
                                                                <option value="required">Harus Diisi</option>
                                                                <option value="null">Tidak Harus</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <button type="button"
                                                    class="btn btn-success add-sub-kriteria-block">Tambah Sub
                                                    Kriteria</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3 text-end">
                                        <button type="button" class="btn btn-success"
                                            id="add-kriteria-main-block">Tanbah Kriteria</button>
                                    </div>

                                    <div class="text-start">
                                        <button type="submit" class="btn btn-info">Simpan Semua</button>
                                    </div>
                                </form>

                                <script>
                                    document.addEventListener('DOMContentLoaded', () => {
                                        let kriteriaMainIndex = 0;
                                        let subKriteriaIndexes = {};

                                        const templateSelect = document.getElementById('template-select');
                                        const loadTemplateBtn = document.getElementById('load-template-btn');
                                        const baseKriteriaBlock = document.querySelector('.form-kriteria-block[data-kriteria-index="0"]');

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

                                        function renderTemplateToForm(data) {
                                            const container = document.getElementById('kriteria-container');
                                            container.innerHTML = '<h5>Kriteria Penilaian</h5>';
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
                                                    if (judulInput && sData.judul_kategori) {
                                                        judulInput.value = sData.judul_kategori;
                                                    }

                                                    const tipeSelect = subClone.querySelector('.tipe-kategori');
                                                    if (tipeSelect && sData.tipe_kategori) {
                                                        tipeSelect.value = sData.tipe_kategori;
                                                    }

                                                    const bobotInput = subClone.querySelector('input[name*="[bobot]"]');
                                                    if (bobotInput && sData.bobot) {
                                                        bobotInput.value = sData.bobot;
                                                    }

                                                    const levelSelect = subClone.querySelector('select[name*="[level]"]');
                                                    if (levelSelect && sData.level) {
                                                        levelSelect.value = sData.level;
                                                    }

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
                                                                            <input type="text" 
                                                                                name="kriteria[${kriteriaMainIndex}][sub_kriteria][${newSubIdx}][ket_tipe][]" 
                                                                                class="form-control" 
                                                                                placeholder="Masukkan keterangan tipe" 
                                                                                value="${k || ''}">
                                                                            <input type="text" 
                                                                                name="kriteria[${kriteriaMainIndex}][sub_kriteria][${newSubIdx}][nilai_ket_tipe][]" 
                                                                                class="form-control" 
                                                                                placeholder="Nilai tipe..." 
                                                                                value="${val || ''}">
                                                                            <button type="button" class="btn btn-danger btn-sm remove-ket-tipe">
                                                                                <i class="mdi mdi-trash-can"></i>
                                                                            </button>
                                                                        </div>
                                                                    `);
                                                                }
                                                            });
                                                        } else {
                                                            if (ketWrapper) {
                                                                ketWrapper.insertAdjacentHTML('beforeend', `
                                                                    <div class="input-group mb-2">
                                                                        <input type="text" 
                                                                            name="kriteria[${kriteriaMainIndex}][sub_kriteria][${newSubIdx}][ket_tipe][]" 
                                                                            class="form-control" 
                                                                            placeholder="Masukkan keterangan tipe">
                                                                        <input type="text" 
                                                                            name="kriteria[${kriteriaMainIndex}][sub_kriteria][${newSubIdx}][nilai_ket_tipe][]" 
                                                                            class="form-control" 
                                                                            placeholder="Nilai tipe...">
                                                                        <button type="button" class="btn btn-danger btn-sm remove-ket-tipe">
                                                                            <i class="mdi mdi-trash-can"></i>
                                                                        </button>
                                                                    </div>
                                                                `);
                                                            }
                                                        }

                                                        if (ketWrapper) {
                                                            ketWrapper.insertAdjacentHTML('beforeend',
                                                                `<button type="button" class="btn btn-success text-end add-ket-tipe mt-2">Tambah Keterangan</button>`
                                                            );
                                                        }
                                                    } else {
                                                        if (ketSection) ketSection.classList.add('d-none');
                                                    }

                                                    if (subWrapper) {
                                                        subWrapper.appendChild(subClone);
                                                    }
                                                });

                                                container.appendChild(kBlock);
                                                bindDynamicSubKriteriaEvents(kBlock);
                                                kriteriaMainIndex++;
                                            });
                                        }

                                        loadTemplateBtn.addEventListener('click', () => {
                                            const selected = Array.from(templateSelect.selectedOptions).map(opt => opt.value);
                                            if (selected.length === 0) {
                                                return Swal.fire('Info', 'Pilih minimal satu template.', 'info');
                                            }

                                            const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
                                            loadingModal.show();

                                            if (selected.length === 1) {
                                                fetch("{{ route('template.load', ':kode') }}".replace(':kode', selected[0]))
                                                    .then(res => res.json())
                                                    .then(data => {
                                                        if (data.error) {
                                                            Swal.fire('Error', data.error, 'error');
                                                            return;
                                                        }
                                                        renderTemplateToForm(data);
                                                        loadingModal.hide();
                                                        Swal.fire('Berhasil',
                                                            `Template "${data.nama_penilaian}" berhasil dimuat dengan ${data.kriteria.length} kriteria.`,
                                                            'success');
                                                    })
                                                    .catch(() => {
                                                        loadingModal.hide();
                                                        Swal.fire('Error', 'Gagal memuat template.', 'error');
                                                    });
                                            } else {
                                                Promise.all(selected.map(kode =>
                                                        fetch("{{ route('template.load', ':kode') }}".replace(':kode', kode)).then(
                                                            r => r.json())
                                                    ))
                                                    .then(results => {
                                                        let mergedKriteria = [];
                                                        let baseInfo = results[0] || {};

                                                        results.forEach(data => {
                                                            if (!data.kriteria) return;
                                                            data.kriteria.forEach(k => {
                                                                const existing = mergedKriteria.find(mk => mk
                                                                    .kode_kategori === k.kode_kategori);
                                                                if (!existing) {
                                                                    mergedKriteria.push(k);
                                                                } else {
                                                                    k.sub_kriteria.forEach(sk => {
                                                                        if (!existing.sub_kriteria.find(
                                                                                es => es.judul_kategori ===
                                                                                sk.judul_kategori)) {
                                                                            existing.sub_kriteria.push(sk);
                                                                        }
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
                                                        Swal.fire('Berhasil',
                                                            `${mergedKriteria.length} kriteria berhasil dimuat dari ${selected.length} template.`,
                                                            'success'
                                                        );
                                                    })
                                                    .catch(() => {
                                                        loadingModal.hide();
                                                        Swal.fire('Error', 'Gagal memuat template.', 'error');
                                                    });
                                            }
                                        });

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
                                                        if (judulInput && !judulInput.value.trim()) {
                                                            sub.remove();
                                                        }
                                                    });

                                                    const remainingSubs = block.querySelectorAll(
                                                        '.form-group-item[data-sub-kriteria-index]');
                                                    if (remainingSubs.length === 0) {
                                                        block.remove();
                                                    }
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
                                                    Swal.fire('Validasi', 'Minimal 1 kriteria dengan 1 sub kriteria harus diisi.',
                                                        'warning');
                                                    return false;
                                                }
                                            });

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
                                                    if (showTypes.includes(tipeSelect.value)) {
                                                        ketTipeSection.classList.remove('d-none');
                                                    } else {
                                                        ketTipeSection.classList.add('d-none');
                                                    }
                                                }

                                                if (tipeSelect) {
                                                    tipeSelect.onchange = toggleKeterangan;
                                                    toggleKeterangan();
                                                }

                                                if (addKeteranganBtn) {
                                                    addKeteranganBtn.onclick = () => {
                                                        ketTipeWrapper.insertAdjacentHTML('beforeend', `
                                                            <div class="input-group mb-2">
                                                                <input type="text" name="kriteria[${currentKriteriaIndex}][sub_kriteria][${currentSubKriteriaIndex}][ket_tipe][]" 
                                                                    class="form-control" placeholder="Masukkan keterangan tipe">
                                                                <input type="text" name="kriteria[${currentKriteriaIndex}][sub_kriteria][${currentSubKriteriaIndex}][nilai_ket_tipe][]" 
                                                                    class="form-control" placeholder="Nilai tipe...">
                                                                <button type="button" class="btn btn-danger btn-sm remove-ket-tipe">
                                                                    <i class="mdi mdi-trash-can"></i>
                                                                </button>
                                                            </div>
                                                        `);
                                                    };
                                                }

                                                if (ketTipeWrapper) {
                                                    ketTipeWrapper.onclick = (e) => {
                                                        if (e.target.closest('.remove-ket-tipe')) {
                                                            const inputGroups = ketTipeWrapper.querySelectorAll('.input-group');
                                                            if (inputGroups.length > 1) {
                                                                e.target.closest('.input-group').remove();
                                                            } else {
                                                                const group = e.target.closest('.input-group');
                                                                const inputs = group.querySelectorAll('input');
                                                                inputs.forEach(input => input.value = '');
                                                            }
                                                        }
                                                    };
                                                }
                                            });
                                        }

                                        if (baseKriteriaBlock) {
                                            bindDynamicSubKriteriaEvents(baseKriteriaBlock);
                                        }

                                        document.getElementById('kriteria-container').addEventListener('click', function(e) {
                                            if (e.target.classList.contains('add-sub-kriteria-block')) {
                                                const kriteriaBlock = e.target.closest('.form-kriteria-block');
                                                const currentKriteriaIndex = kriteriaBlock.getAttribute('data-kriteria-index');
                                                const subWrapper = kriteriaBlock.querySelector('.form-wrapper-sub-kriteria');
                                                const firstSub = kriteriaBlock.querySelector(
                                                    '.form-group-item[data-sub-kriteria-index]');
                                                const clone = firstSub.cloneNode(true);

                                                if (!subKriteriaIndexes[currentKriteriaIndex]) {
                                                    subKriteriaIndexes[currentKriteriaIndex] = 0;
                                                }
                                                subKriteriaIndexes[currentKriteriaIndex]++;
                                                const newSubIndex = subKriteriaIndexes[currentKriteriaIndex];

                                                clone.setAttribute('data-sub-kriteria-index', newSubIndex);
                                                clone.querySelectorAll('input, select').forEach(el => {
                                                    const name = el.getAttribute('name');
                                                    if (name) {
                                                        el.setAttribute('name', name
                                                            .replace(/\[sub_kriteria\]\[\d+\]/,
                                                                `[sub_kriteria][${newSubIndex}]`)
                                                            .replace(/\[kriteria\]\[\d+\]/,
                                                                `[kriteria][${currentKriteriaIndex}]`)
                                                        );
                                                    }
                                                    if (el.tagName === 'SELECT') el.selectedIndex = 0;
                                                    else el.value = '';
                                                });

                                                const ketWrapper = clone.querySelector('.ket-tipe-wrapper');
                                                ketWrapper.innerHTML = `
                                                    <div class="input-group mb-2">
                                                        <input type="text" name="kriteria[${currentKriteriaIndex}][sub_kriteria][${newSubIndex}][ket_tipe][]" 
                                                            class="form-control" placeholder="Masukkan keterangan tipe">
                                                        <input type="text" name="kriteria[${currentKriteriaIndex}][sub_kriteria][${newSubIndex}][nilai_ket_tipe][]" 
                                                            class="form-control" placeholder="Nilai tipe...">
                                                        <button type="button" class="btn btn-danger btn-sm remove-ket-tipe">
                                                            <i class="mdi mdi-trash-can"></i>
                                                        </button>
                                                    </div>
                                                    <button type="button" class="btn btn-success cl-blue add-ket-tipe mt-2">Tambah Keterangan</button>
                                                `;

                                                subWrapper.appendChild(clone);
                                                bindDynamicSubKriteriaEvents(kriteriaBlock);
                                            }

                                            if (e.target.closest('.remove-sub-kriteria-block')) {
                                                const sub = e.target.closest('.form-group-item[data-sub-kriteria-index]');
                                                const parent = sub.parentElement;
                                                if (parent.querySelectorAll('.form-group-item[data-sub-kriteria-index]').length > 1) {
                                                    sub.remove();
                                                }
                                            }

                                            if (e.target.closest('.remove-kriteria-block')) {
                                                const block = e.target.closest('.form-kriteria-block');
                                                if (document.querySelectorAll('.form-kriteria-block').length > 1) {
                                                    block.remove();
                                                }
                                            }
                                        });

                                        document.getElementById('add-kriteria-main-block').addEventListener('click', () => {
                                            const container = document.getElementById('kriteria-container');
                                            const clone = baseKriteriaBlock.cloneNode(true);

                                            subKriteriaIndexes[kriteriaMainIndex] = 0;
                                            clone.setAttribute('data-kriteria-index', kriteriaMainIndex);

                                            clone.querySelectorAll('[name^="kriteria[0]"]').forEach(el => {
                                                const name = el.getAttribute('name');
                                                if (name) {
                                                    el.setAttribute('name', name.replace(/kriteria\[0\]/g,
                                                        `kriteria[${kriteriaMainIndex}]`));
                                                }
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
                                                            <input type="text" name="kriteria[${kriteriaMainIndex}][sub_kriteria][0][ket_tipe][]" 
                                                                class="form-control" placeholder="Masukkan keterangan tipe">
                                                            <input type="text" name="kriteria[${kriteriaMainIndex}][sub_kriteria][0][nilai_ket_tipe][]" 
                                                                class="form-control" placeholder="Nilai tipe...">
                                                            <button type="button" class="btn btn-danger btn-sm remove-ket-tipe">
                                                                <i class="mdi mdi-trash-can"></i>
                                                            </button>
                                                        </div>
                                                        <button type="button" class="btn btn-success cl-blue add-ket-tipe mt-2">Tambah Keterangan</button>
                                                    `;
                                                } else {
                                                    item.remove();
                                                }
                                            });

                                            container.appendChild(clone);
                                            bindDynamicSubKriteriaEvents(clone);
                                            kriteriaMainIndex++;
                                        });

                                        const allKaryawan = @json($data);

                                        document.getElementById('form-karyawan').addEventListener('change', function(e) {
                                            if (e.target.classList.contains('divisi-select')) {
                                                const selectedDivisi = e.target.value;
                                                const karyawanSelect = e.target.closest('.karyawan-block').querySelector(
                                                    '.karyawan-select');
                                                const idInput = e.target.closest('.karyawan-block').querySelector('.id-karyawan');

                                                karyawanSelect.innerHTML = `<option selected disabled>Pilih Karyawan</option>`;

                                                allKaryawan.forEach(k => {
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

                                        document.getElementById('form-karyawan').addEventListener('click', function(e) {
                                            if (e.target.closest('.remove-karyawan-block')) {
                                                const block = e.target.closest('.karyawan-block');
                                                if (document.querySelectorAll('.karyawan-block').length > 1) {
                                                    block.remove();
                                                }
                                            }
                                        });
                                    });
                                </script>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
