@extends('layouts_kpi.app')

@section('kpi_contents')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
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
            margin-bottom: .25rem;
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

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .6rem 1.25rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: .875rem;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            text-decoration: none;
            transition: all .2s ease;
            margin-bottom: 1.5rem;
        }

        .btn-back:hover {
            background: #e2e8f0;
            color: #1e293b;
            transform: translateX(-2px);
        }

        .content-card {
            background: #fff;
            border-radius: 16px;
            border: 0;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .04);
            overflow: hidden;
        }

        .content-card .card-body {
            padding: 2rem;
        }

        .section-title {
            font-size: 1.1rem;
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

        .karyawan-block,
        .form-kriteria-block {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem;
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
            position: relative;
        }

        .form-group-item:hover {
            border-color: #cbd5e1;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .03);
        }

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

        .ket-tipe-section {
            background: #fff;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            padding: 1rem;
            margin-top: .75rem;
        }

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

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .6rem 1.1rem;
            border-radius: 10px;
            font-size: .875rem;
            font-weight: 600;
            border: 0;
            transition: all .2s ease;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-action.primary {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            box-shadow: 0 4px 12px rgba(99, 102, 241, .25);
        }

        .btn-action.primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(99, 102, 241, .35);
            color: #fff;
        }

        .btn-action.success {
            background: linear-gradient(135deg, #34d399, #059669);
            color: #fff;
            box-shadow: 0 4px 12px rgba(16, 185, 129, .25);
        }

        .btn-action.success:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, .35);
            color: #fff;
        }

        .btn-action.warning {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: #fff;
            box-shadow: 0 4px 12px rgba(245, 158, 11, .25);
        }

        .btn-action.light {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .btn-action.light:hover {
            background: #e2e8f0;
            color: #1e293b;
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

        @media (max-width: 768px) {
            .content-card .card-body {
                padding: 1.25rem;
            }

            .karyawan-block,
            .form-kriteria-block {
                padding: 1rem;
            }

            .form-group-item {
                padding: 1rem;
            }
        }
    </style>

    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: "{{ session('success') }}",
                        confirmButtonColor: '#6366f1'
                    });
                }
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: "{{ session('error') }}",
                        confirmButtonColor: '#ef4444'
                    });
                }
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal',
                        html: `{!! implode('<br>', $errors->all()) !!}`,
                        confirmButtonColor: '#ef4444'
                    });
                }
            });
        </script>
    @endif

    <div class="container content-wrapper mt-4">
        <a href="{{ route('penilaian.form.data') }}" class="btn-back">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Data Form
        </a>

        <div class="content-card">
            <div class="card-body">
                <h4 class="fw-bold text-dark mb-4 text-center">
                    <i class="fa-solid fa-file-pen text-primary me-2"></i>
                    {{ __('Edit Formulir') }}
                </h4>

                <form action="{{ route('penilaian.form.update') }}" method="POST">
                    @csrf
                    @if ($data['kode_form'])
                        <input type="hidden" name="kode_form" value="{{ $data['kode_form'] }}">
                    @endif

                    @if ($data['kode_form'])
                        <div class="mb-4">
                            <h5 class="section-title">
                                <i class="fa-solid fa-layer-group"></i> Jenis Form
                            </h5>
                            <div class="karyawan-block">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Jenis Form <span class="text-danger">*</span></label>
                                        <select name="jenis_form" id="jenis_form" class="form-select" required>
                                            <option value="">Pilih Jenis Form</option>
                                            <option value="Kontrak"
                                                {{ $data['jenis_form'] === 'Kontrak' ? 'selected' : '' }}>Kontrak</option>
                                            <option value="Probation"
                                                {{ $data['jenis_form'] === 'Probation' ? 'selected' : '' }}>Probation
                                            </option>
                                            <option value="Rutin" {{ $data['jenis_form'] === 'Rutin' ? 'selected' : '' }}>
                                                Rutin</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div id="kriteria-container">
                        <h5 class="section-title">
                            <i class="fa-solid fa-list-check"></i> Kriteria Penilaian
                        </h5>

                        @foreach ($data['result'] as $kIndex => $item)
                            <div class="form-kriteria-block" data-kriteria-index="{{ $kIndex }}">
                                <button type="button" class="btn-remove-block remove-kriteria-block"
                                    title="Hapus Kriteria">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>

                                <div class="mb-3">
                                    <label class="form-label">Nama Kriteria <span class="text-danger">*</span></label>
                                    <input type="hidden" name="kriteria[{{ $kIndex }}][id_nama_penilaian]"
                                        value="{{ $item['id_formPenilaian'] }}">
                                    <input type="text" name="kriteria[{{ $kIndex }}][nama_penilaian]"
                                        class="form-control" placeholder="Masukan nama kriteria..." maxlength="250"
                                        value="{{ $item['nama_penilaian'] }}" required>
                                </div>

                                <div class="form-wrapper-sub-kriteria">
                                    @foreach ($item['kategori'] as $sIndex => $itemKategori)
                                        <div class="form-group-item" data-sub-kriteria-index="{{ $sIndex }}">
                                            <button type="button" class="btn-remove-block remove-sub-kriteria-block"
                                                title="Hapus Sub Kriteria">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>

                                            <div class="row g-3 mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Judul Sub Kriteria <span
                                                            class="text-danger">*</span></label>
                                                    <input type="hidden"
                                                        name="kriteria[{{ $kIndex }}][sub_kriteria][{{ $sIndex }}][id_judul_kategori]"
                                                        value="{{ $itemKategori['id_kategori'] }}">
                                                    <input type="text"
                                                        name="kriteria[{{ $kIndex }}][sub_kriteria][{{ $sIndex }}][judul_kategori]"
                                                        maxlength="250" class="form-control"
                                                        placeholder="Masukan sub kriteria..."
                                                        value="{{ $itemKategori['judul_kategori'] }}" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Tipe <span
                                                            class="text-danger">*</span></label>
                                                    <select
                                                        name="kriteria[{{ $kIndex }}][sub_kriteria][{{ $sIndex }}][tipe_kategori]"
                                                        class="form-select tipe-kategori" required>
                                                        <option disabled
                                                            {{ empty($itemKategori['tipe_kategori']) ? 'selected' : '' }}>
                                                            Pilih tipe</option>
                                                        <option value="text"
                                                            {{ ($itemKategori['tipe_kategori'] ?? '') == 'text' ? 'selected' : '' }}>
                                                            Teks</option>
                                                        <option value="radio"
                                                            {{ ($itemKategori['tipe_kategori'] ?? '') == 'radio' ? 'selected' : '' }}>
                                                            Pilihan (Radio)</option>
                                                        <option value="checkbox"
                                                            {{ ($itemKategori['tipe_kategori'] ?? '') == 'checkbox' ? 'selected' : '' }}>
                                                            Kotak Centang</option>
                                                        <option value="number"
                                                            {{ ($itemKategori['tipe_kategori'] ?? '') == 'number' ? 'selected' : '' }}>
                                                            Angka</option>
                                                        <option value="range"
                                                            {{ ($itemKategori['tipe_kategori'] ?? '') == 'range' ? 'selected' : '' }}>
                                                            Rentang</option>
                                                        <option value="textarea"
                                                            {{ ($itemKategori['tipe_kategori'] ?? '') == 'textarea' ? 'selected' : '' }}>
                                                            Teks Panjang</option>
                                                        <option value="select"
                                                            {{ ($itemKategori['tipe_kategori'] ?? '') == 'select' ? 'selected' : '' }}>
                                                            Pilihan Dropdown</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div
                                                class="ket-tipe-section {{ in_array($itemKategori['tipe_kategori'] ?? '', ['checkbox', 'radio', 'select']) ? '' : 'd-none' }} mb-3">
                                                <label class="form-label fw-semibold">
                                                    <i class="fa-solid fa-tags text-primary me-1"></i> Keterangan Tipe
                                                </label>
                                                <div class="ket-tipe-wrapper text-end">
                                                    @if (!empty($itemKategori['dataTipeKeterangan']))
                                                        @foreach ($itemKategori['dataTipeKeterangan'] as $detail)
                                                            <div class="input-group mb-2">
                                                                <input type="hidden"
                                                                    name="kriteria[{{ $kIndex }}][sub_kriteria][{{ $sIndex }}][id_ket_tipe][]"
                                                                    value="{{ $detail['id'] }}">
                                                                <input type="text"
                                                                    name="kriteria[{{ $kIndex }}][sub_kriteria][{{ $sIndex }}][ket_tipe][]"
                                                                    class="form-control"
                                                                    placeholder="Masukkan keterangan tipe"
                                                                    value="{{ $detail['keterangan_tipe'] }}">
                                                                <input type="text"
                                                                    name="kriteria[{{ $kIndex }}][sub_kriteria][{{ $sIndex }}][nilai_ket_tipe][]"
                                                                    class="form-control" placeholder="Nilai tipe..."
                                                                    value="{{ $detail['nilai_ket_tipe'] }}">
                                                                <button type="button"
                                                                    class="btn btn-danger btn-sm remove-ket-tipe"><i
                                                                        class="fa-solid fa-trash-can"></i></button>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="input-group mb-2">
                                                            <input type="text"
                                                                name="kriteria[{{ $kIndex }}][sub_kriteria][{{ $sIndex }}][ket_tipe][]"
                                                                class="form-control"
                                                                placeholder="Masukkan keterangan tipe">
                                                            <input type="text"
                                                                name="kriteria[{{ $kIndex }}][sub_kriteria][{{ $sIndex }}][nilai_ket_tipe][]"
                                                                class="form-control" placeholder="Nilai tipe...">
                                                            <button type="button"
                                                                class="btn btn-danger btn-sm remove-ket-tipe"><i
                                                                    class="fa-solid fa-trash-can"></i></button>
                                                        </div>
                                                    @endif
                                                    <button type="button"
                                                        class="btn-action success btn-sm add-ket-tipe mt-2">
                                                        <i class="fa-solid fa-plus"></i> Tambah Keterangan
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Bobot <span
                                                            class="text-danger">*</span></label>
                                                    <input type="number"
                                                        name="kriteria[{{ $kIndex }}][sub_kriteria][{{ $sIndex }}][bobot]"
                                                        placeholder="Masukan bobot..." class="form-control"
                                                        value="{{ $itemKategori['bobot'] }}" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Level <span
                                                            class="text-danger">*</span></label>
                                                    <select
                                                        name="kriteria[{{ $kIndex }}][sub_kriteria][{{ $sIndex }}][level]"
                                                        class="form-select" required>
                                                        <option disabled
                                                            {{ empty($itemKategori['level']) ? 'selected' : '' }}>Pilih
                                                        </option>
                                                        <option value="required"
                                                            {{ ($itemKategori['level'] ?? '') == 'required' ? 'selected' : '' }}>
                                                            Harus Diisi</option>
                                                        <option value="null"
                                                            {{ ($itemKategori['level'] ?? '') == 'null' ? 'selected' : '' }}>
                                                            Tidak Harus</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="text-end mt-3">
                                    <button type="button" class="btn-action success btn-sm add-sub-kriteria-block">
                                        <i class="fa-solid fa-plus"></i> Tambah Sub Kriteria
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                        <button type="button" class="btn-action warning" id="add-kriteria-main-block">
                            <i class="fa-solid fa-plus"></i> Tambah Kriteria
                        </button>
                        <button type="submit" class="btn-action primary">
                            <i class="fa-solid fa-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const kriteriaBlocks = Array.from(document.querySelectorAll('.form-kriteria-block'));
            let kriteriaMainIndex = 0;
            const subKriteriaIndexes = {};
            let deletedFormIds = [];
            let deletedKategoriIds = [];
            let deletedTipeIds = [];

            kriteriaBlocks.forEach(block => {
                const kIdx = parseInt(block.getAttribute('data-kriteria-index'), 10);
                if (!isNaN(kIdx)) {
                    if (kIdx > kriteriaMainIndex) kriteriaMainIndex = kIdx;
                    const subItems = Array.from(block.querySelectorAll(
                        '.form-group-item[data-sub-kriteria-index]'));
                    let maxSub = -1;
                    subItems.forEach(si => {
                        const sIdx = parseInt(si.getAttribute('data-sub-kriteria-index'), 10);
                        if (!isNaN(sIdx) && sIdx > maxSub) maxSub = sIdx;
                    });
                    subKriteriaIndexes[kIdx] = maxSub >= 0 ? maxSub : 0;
                }
            });

            if (kriteriaBlocks.length === 0) {
                kriteriaMainIndex = 0;
                subKriteriaIndexes[0] = 0;
            }

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
                        if (tipeSelect && showTypes.includes(tipeSelect.value)) {
                            ketTipeSection.classList.remove('d-none');
                        } else if (ketTipeSection) {
                            ketTipeSection.classList.add('d-none');
                            if (ketTipeWrapper) {
                                ketTipeWrapper.querySelectorAll('.input-group').forEach((el, i) => {
                                    if (i > 0) el.remove();
                                });
                                const firstKet = ketTipeWrapper.querySelector('input[name*="[ket_tipe]"]');
                                const firstNilai = ketTipeWrapper.querySelector(
                                    'input[name*="[nilai_ket_tipe]"]');
                                if (firstKet) firstKet.value = '';
                                if (firstNilai) firstNilai.value = '';
                            }
                        }
                    }

                    if (tipeSelect) {
                        tipeSelect.removeEventListener('change', toggleKeterangan);
                        tipeSelect.addEventListener('change', toggleKeterangan);
                        toggleKeterangan();
                    }

                    if (addKeteranganBtn && ketTipeWrapper) {
                        const oldHandler = addKeteranganBtn.__clickHandler;
                        if (oldHandler) addKeteranganBtn.removeEventListener('click', oldHandler);
                        const newHandler = () => {
                            const inputGroup = document.createElement('div');
                            inputGroup.className = 'input-group mb-2';
                            inputGroup.innerHTML = `
                            <input type="text" name="kriteria[${currentKriteriaIndex}][sub_kriteria][${currentSubKriteriaIndex}][ket_tipe][]" class="form-control" placeholder="Masukkan keterangan tipe">
                            <input type="text" name="kriteria[${currentKriteriaIndex}][sub_kriteria][${currentSubKriteriaIndex}][nilai_ket_tipe][]" class="form-control" placeholder="Nilai tipe...">
                            <button type="button" class="btn btn-danger btn-sm remove-ket-tipe"><i class="fa-solid fa-trash-can"></i></button>
                        `;
                            ketTipeWrapper.insertBefore(inputGroup, addKeteranganBtn);
                        };
                        addKeteranganBtn.addEventListener('click', newHandler);
                        addKeteranganBtn.__clickHandler = newHandler;
                    }

                    if (ketTipeWrapper) {
                        const oldRemoveHandler = ketTipeWrapper.__removeHandler;
                        if (oldRemoveHandler) ketTipeWrapper.removeEventListener('click', oldRemoveHandler);
                        const newRemoveHandler = (e) => {
                            if (e.target.closest('.remove-ket-tipe')) {
                                const inputGroups = ketTipeWrapper.querySelectorAll('.input-group');
                                if (inputGroups.length > 1) {
                                    const ketInput = e.target.closest('.input-group').querySelector(
                                        'input[name*="[id_ket_tipe]"]');
                                    if (ketInput && ketInput.value) deletedTipeIds.push(ketInput.value);
                                    e.target.closest('.input-group').remove();
                                } else {
                                    const group = e.target.closest('.input-group');
                                    const ket = group.querySelector('input[name*="[ket_tipe]"]');
                                    const nilai = group.querySelector(
                                        'input[name*="[nilai_ket_tipe]"]');
                                    if (ket) ket.value = '';
                                    if (nilai) nilai.value = '';
                                }
                            }
                        };
                        ketTipeWrapper.addEventListener('click', newRemoveHandler);
                        ketTipeWrapper.__removeHandler = newRemoveHandler;
                    }
                });
            }

            document.querySelectorAll('.form-kriteria-block').forEach(block => bindDynamicSubKriteriaEvents(block));

            document.getElementById('kriteria-container').addEventListener('click', function(e) {
                if (e.target.classList.contains('add-sub-kriteria-block') || e.target.closest(
                        '.add-sub-kriteria-block')) {
                    const btn = e.target.classList.contains('add-sub-kriteria-block') ? e.target : e.target
                        .closest('.add-sub-kriteria-block');
                    const kriteriaBlock = btn.closest('.form-kriteria-block');
                    const currentKriteriaIndex = kriteriaBlock.getAttribute('data-kriteria-index');
                    const subWrapper = kriteriaBlock.querySelector('.form-wrapper-sub-kriteria');
                    let firstSub = kriteriaBlock.querySelector('.form-group-item[data-sub-kriteria-index]');
                    if (!firstSub) return;
                    if (typeof subKriteriaIndexes[currentKriteriaIndex] === 'undefined') subKriteriaIndexes[
                        currentKriteriaIndex] = 0;
                    subKriteriaIndexes[currentKriteriaIndex]++;
                    const newSubIndex = subKriteriaIndexes[currentKriteriaIndex];
                    const clone = firstSub.cloneNode(true);
                    clone.setAttribute('data-sub-kriteria-index', newSubIndex);
                    clone.querySelectorAll('input, select, textarea').forEach(el => {
                        const name = el.getAttribute('name');
                        if (name) {
                            const updated = name.replace(/\[sub_kriteria\]\[\d+\]/,
                                `[sub_kriteria][${newSubIndex}]`).replace(/kriteria\[\d+\]/,
                                `kriteria[${currentKriteriaIndex}]`);
                            el.setAttribute('name', updated);
                        }
                        if (el.tagName === 'SELECT') el.selectedIndex = 0;
                        else if (el.type === 'checkbox' || el.type === 'radio') el.checked = false;
                        else el.value = '';
                    });
                    const ketWrapper = clone.querySelector('.ket-tipe-wrapper');
                    if (ketWrapper) {
                        ketWrapper.innerHTML = `
                        <div class="input-group mb-2">
                            <input type="text" name="kriteria[${currentKriteriaIndex}][sub_kriteria][${newSubIndex}][ket_tipe][]" class="form-control" placeholder="Masukkan keterangan tipe">
                            <input type="text" name="kriteria[${currentKriteriaIndex}][sub_kriteria][${newSubIndex}][nilai_ket_tipe][]" class="form-control" placeholder="Nilai tipe...">
                            <button type="button" class="btn btn-danger btn-sm remove-ket-tipe"><i class="fa-solid fa-trash-can"></i></button>
                        </div>
                        <button type="button" class="btn-action success btn-sm add-ket-tipe mt-2"><i class="fa-solid fa-plus"></i> Tambah Keterangan</button>
                    `;
                    }
                    subWrapper.appendChild(clone);
                    bindDynamicSubKriteriaEvents(kriteriaBlock);
                }

                if (e.target.closest('.remove-sub-kriteria-block')) {
                    const sub = e.target.closest('.form-group-item[data-sub-kriteria-index]');
                    const parent = e.target.closest('.form-kriteria-block');
                    const subCount = parent.querySelectorAll('.form-group-item[data-sub-kriteria-index]')
                        .length;
                    if (subCount <= 1) return;
                    const id = sub.querySelector('input[name*="[id_judul_kategori]"]')?.value;
                    if (id) deletedKategoriIds.push(id);
                    sub.remove();
                }

                if (e.target.closest('.remove-kriteria-block')) {
                    const allBlocks = document.querySelectorAll('.form-kriteria-block');
                    if (allBlocks.length <= 1) return;
                    const kriteriaBlock = e.target.closest('.form-kriteria-block');
                    const id = kriteriaBlock.querySelector('input[name*="[id_nama_penilaian]"]')?.value;
                    if (id) deletedFormIds.push(id);
                    kriteriaBlock.remove();
                }
            });

            document.getElementById('add-kriteria-main-block').addEventListener('click', () => {
                const container = document.getElementById('kriteria-container');
                const template = document.querySelector('.form-kriteria-block[data-kriteria-index="0"]') ||
                    document.querySelector('.form-kriteria-block');
                const clone = template.cloneNode(true);

                kriteriaMainIndex++;
                subKriteriaIndexes[kriteriaMainIndex] = 0;
                clone.setAttribute('data-kriteria-index', kriteriaMainIndex);
                clone.querySelectorAll('input, select, textarea').forEach(el => {
                    const name = el.getAttribute('name');
                    if (name) {
                        const updated = name.replace(/kriteria\[\d+\]/,
                            `kriteria[${kriteriaMainIndex}]`);
                        el.setAttribute('name', updated);
                    }
                    if (el.tagName === 'SELECT') el.selectedIndex = 0;
                    else if (el.type === 'checkbox' || el.type === 'radio') el.checked = false;
                    else el.value = '';
                });

                const subWrapper = clone.querySelector('.form-wrapper-sub-kriteria');
                subWrapper.innerHTML = `
                <div class="form-group-item" data-sub-kriteria-index="0">
                    <button type="button" class="btn-remove-block remove-sub-kriteria-block" title="Hapus Sub Kriteria">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Judul Sub Kriteria <span class="text-danger">*</span></label>
                            <input type="text" name="kriteria[${kriteriaMainIndex}][sub_kriteria][0][judul_kategori]" class="form-control" placeholder="Masukan sub kriteria..." maxlength="250" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipe <span class="text-danger">*</span></label>
                            <select name="kriteria[${kriteriaMainIndex}][sub_kriteria][0][tipe_kategori]" class="form-select tipe-kategori" required>
                                <option disabled selected>Pilih tipe</option>
                                <option value="text">Teks</option>
                                <option value="radio">Pilihan (Radio)</option>
                                <option value="checkbox">Kotak Centang</option>
                                <option value="number">Angka</option>
                                <option value="range">Rentang</option>
                                <option value="textarea">Teks Panjang</option>
                                <option value="select">Pilihan Dropdown</option>
                            </select>
                        </div>
                    </div>
                    <div class="ket-tipe-section d-none mb-3">
                        <label class="form-label fw-semibold"><i class="fa-solid fa-tags text-primary me-1"></i> Keterangan Tipe</label>
                        <div class="ket-tipe-wrapper text-end">
                            <div class="input-group mb-2">
                                <input type="text" name="kriteria[${kriteriaMainIndex}][sub_kriteria][0][ket_tipe][]" class="form-control" placeholder="Masukkan keterangan tipe">
                                <input type="text" name="kriteria[${kriteriaMainIndex}][sub_kriteria][0][nilai_ket_tipe][]" class="form-control" placeholder="Nilai tipe...">
                                <button type="button" class="btn btn-danger btn-sm remove-ket-tipe"><i class="fa-solid fa-trash-can"></i></button>
                            </div>
                            <button type="button" class="btn-action success btn-sm add-ket-tipe mt-2"><i class="fa-solid fa-plus"></i> Tambah Keterangan</button>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Bobot <span class="text-danger">*</span></label>
                            <input type="number" name="kriteria[${kriteriaMainIndex}][sub_kriteria][0][bobot]" class="form-control" placeholder="Masukan bobot..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Level <span class="text-danger">*</span></label>
                            <select name="kriteria[${kriteriaMainIndex}][sub_kriteria][0][level]" class="form-select" required>
                                <option disabled selected>Pilih</option>
                                <option value="required">Harus Diisi</option>
                                <option value="null">Tidak Harus</option>
                            </select>
                        </div>
                    </div>
                </div>
            `;
                container.appendChild(clone);
                bindDynamicSubKriteriaEvents(clone);
            });

            document.querySelector('form').addEventListener('submit', function() {
                if (!document.querySelector('input[name="deleted_form_ids"]')) {
                    let inputForm = document.createElement('input');
                    inputForm.type = 'hidden';
                    inputForm.name = 'deleted_form_ids';
                    inputForm.value = deletedFormIds.join(',');
                    this.appendChild(inputForm);
                }

                if (!document.querySelector('input[name="deleted_kategori_ids"]')) {
                    let inputKategori = document.createElement('input');
                    inputKategori.type = 'hidden';
                    inputKategori.name = 'deleted_kategori_ids';
                    inputKategori.value = deletedKategoriIds.join(',');
                    this.appendChild(inputKategori);
                }

                if (!document.querySelector('input[name="deleted_tipe_ids"]')) {
                    let inputTipe = document.createElement('input');
                    inputTipe.type = 'hidden';
                    inputTipe.name = 'deleted_tipe_ids';
                    inputTipe.value = deletedTipeIds.join(',');
                    this.appendChild(inputTipe);
                }
            });
        });
    </script>
@endsection
