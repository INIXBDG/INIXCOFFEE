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
        customClass: {
            cancelButton: 'btn btn-gradient-danger'
        },
    });
</script>
@endif

@if (session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
</div>
@endif

<div class="content-wrapper">
    <div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="loading-spinner"></div>
        </div>
    </div>
    <div class="page-header">
        <h3 class="page-title">
            <span class="page-title-icon bg-gradient-primary text-white me-2">
                <i class="mdi mdi-home"></i>
            </span> Penilaian
        </h3>
        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">
                    <span></span>Edit Formulir <i class="mdi mdi-alert-circle-outline icon-sm text-primary align-middle" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit isi formulir yang salah anda masukan. sesuaikan dengan kode formulirnya!"></i>
                </li>
            </ul>
        </nav>
    </div>
    <div class="row">
        <div class="col">
            <a href="{{ route('penilaian.form.data') }}" class="btn btn btn-danger text-white my-2">
                <i class="mdi mdi-left-arrow"></i> Kembali
            </a>
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body" id="card">
                            <h5 class="card-title text-center mb-4">{{ __('Edit Fromulir') }}</h5>
                            <form action="{{ route('penilaian.form.update') }}" method="POST">
                                @csrf
                                @if ($data['kode_form'])
                                <input type="hidden" name="kode_form" value="{{ $data['kode_form'] }}">
                                @endif
                                <div id="kriteria-container">
                                    @foreach ($data['result'] as $kIndex => $item)
                                    <div class="form-kriteria-block border rounded p-3 mb-4 bg-theme" data-kriteria-index="{{ $kIndex }}">
                                        <div class="text-end" style="margin-right: -1.5vw; margin-top: -1.5vw;">
                                            <button type="button" class="btn btn-danger btn-sm remove-kriteria-block"><i class="mdi mdi-trash-can"></i></button>
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label">Nama Kriteria</label>
                                            <input type="hidden" name="kriteria[{{ $kIndex }}][id_nama_penilaian]" value="{{ $item['id_formPenilaian'] }}">
                                            <input type="text" name="kriteria[{{ $kIndex }}][nama_penilaian]" class="form-control" placeholder="Masukan nama kriteria..." maxlength="250" value="{{ $item['nama_penilaian'] }}" required>
                                        </div>

                                        <div class="form-wrapper-sub-kriteria">
                                            @foreach ($item['kategori'] as $sIndex => $itemKategori)
                                            <div class="form-group-item p-3 border rounded mb-4 bg-theme" data-sub-kriteria-index="{{ $sIndex }}">
                                                <div class="text-end" style="margin-right: -1vw; margin-top: -2vw;">
                                                    <button type="button" class="btn btn-danger btn-sm remove-sub-kriteria-block"><i class="mdi mdi-trash-can"></i></button>
                                                </div>
                                                <div class="row g-2 mb-5">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Sub Kriteria</label>
                                                        <input type="hidden" name="kriteria[{{ $kIndex }}][sub_kriteria][{{ $sIndex }}][id_judul_kategori]" value="{{ $itemKategori['id_kategori'] }}">
                                                        <input type="text" name="kriteria[{{ $kIndex }}][sub_kriteria][{{ $sIndex }}][judul_kategori]" maxlength="250" class="form-control" placeholder="masukan sub kriteria..." value="{{ $itemKategori['judul_kategori'] }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Tipe</label>
                                                        <select name="kriteria[{{ $kIndex }}][sub_kriteria][{{ $sIndex }}][tipe_kategori]" class="form-select tipe-kategori" required>
                                                            <option disabled {{ empty($itemKategori['tipe_kategori']) ? 'selected' : '' }}>Pilih tipe</option>
                                                            <option value="text" {{ ($itemKategori['tipe_kategori'] ?? '') == 'text' ? 'selected' : '' }}>Teks</option>
                                                            <option value="radio" {{ ($itemKategori['tipe_kategori'] ?? '') == 'radio' ? 'selected' : '' }}>Pilihan (Radio)</option>
                                                            <option value="checkbox" {{ ($itemKategori['tipe_kategori'] ?? '') == 'checkbox' ? 'selected' : '' }}>Kotak Centang</option>
                                                            <option value="number" {{ ($itemKategori['tipe_kategori'] ?? '') == 'number' ? 'selected' : '' }}>Angka</option>
                                                            <option value="range" {{ ($itemKategori['tipe_kategori'] ?? '') == 'range' ? 'selected' : '' }}>Rentang</option>
                                                            <option value="textarea" {{ ($itemKategori['tipe_kategori'] ?? '') == 'textarea' ? 'selected' : '' }}>Teks Panjang</option>
                                                            <option value="select" {{ ($itemKategori['tipe_kategori'] ?? '') == 'select' ? 'selected' : '' }}>Pilihan Dropdown</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="ket-tipe-section {{ in_array($itemKategori['tipe_kategori'] ?? '', ['checkbox','radio','select']) ? '' : 'd-none' }} mb-3">
                                                    <label class="form-label">Keterangan Tipe</label>
                                                    <div class="ket-tipe-wrapper text-end">
                                                        @if(!empty($itemKategori['dataTipeKeterangan']))
                                                        @foreach ($itemKategori['dataTipeKeterangan'] as $detail)
                                                        <div class="input-group mb-2">
                                                            <input type="hidden" name="kriteria[{{ $kIndex }}][sub_kriteria][{{ $sIndex }}][id_ket_tipe][]" value="{{ $detail['id'] }}">
                                                            <input type="text" name="kriteria[{{ $kIndex }}][sub_kriteria][{{ $sIndex }}][ket_tipe][]" class="form-control" placeholder="Masukkan keterangan tipe" value="{{ $detail['keterangan_tipe'] }}">
                                                            <input type="text" name="kriteria[{{ $kIndex }}][sub_kriteria][{{ $sIndex }}][nilai_ket_tipe][]" class="form-control" placeholder="Nilai tipe..." value="{{ $detail['nilai_ket_tipe'] }}">
                                                            <button type="button" class="btn btn-danger btn-sm remove-ket-tipe"><i class="mdi mdi-trash-can"></i></button>
                                                        </div>
                                                        @endforeach
                                                        @else
                                                        <div class="input-group mb-2">
                                                            <input type="text" name="kriteria[{{ $kIndex }}][sub_kriteria][{{ $sIndex }}][ket_tipe][]" class="form-control" placeholder="Masukkan keterangan tipe">
                                                            <input type="text" name="kriteria[{{ $kIndex }}][sub_kriteria][{{ $sIndex }}][nilai_ket_tipe][]" class="form-control" placeholder="Nilai tipe...">
                                                            <button type="button" class="btn btn-danger btn-sm remove-ket-tipe"><i class="mdi mdi-trash-can"></i></button>
                                                        </div>
                                                        @endif
                                                        <button type="button" class="btn btn-success text-light text-end add-ket-tipe">Tambah Keterangan</button>
                                                    </div>
                                                </div>

                                                <div class="row g-2 mb-2">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Bobot</label>
                                                        <input type="number" name="kriteria[{{ $kIndex }}][sub_kriteria][{{ $sIndex }}][bobot]" placeholder="masukan bobot..." class="form-control" value="{{ $itemKategori['bobot'] }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Level</label>
                                                        <select name="kriteria[{{ $kIndex }}][sub_kriteria][{{ $sIndex }}][level]" class="form-select" required>
                                                            <option disabled {{ empty($itemKategori['level']) ? 'selected' : '' }}>Pilih</option>
                                                            <option value="required" {{ ($itemKategori['level'] ?? '') == 'required' ? 'selected' : '' }}>Harus Diisi</option>
                                                            <option value="null" {{ ($itemKategori['level'] ?? '') == 'null' ? 'selected' : '' }}>Tidak Harus</option>
                                                        </select>
                                                    </div>
                                                </div>


                                            </div>
                                            @endforeach
                                        </div>

                                        <div class="text-end">
                                            <button type="button" class="btn text-light btn-success add-sub-kriteria-block">Tambah Sub Kriteria</button>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                <div class="mb-3 text-end">
                                    <button type="button" class="btn text-light btn-success" id="add-kriteria-main-block">Tambah Kriteria</button>
                                </div>

                                <div class="text-start">
                                    <button type="submit" class="btn btn-primary text-light">Simpan Semua</button>
                                </div>
                            </form>
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
                                            const subItems = Array.from(block.querySelectorAll('.form-group-item[data-sub-kriteria-index]'));
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
                                        const subKriteriaItems = kriteriaBlock.querySelectorAll('.form-group-item[data-sub-kriteria-index]');
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
                                                        const firstNilai = ketTipeWrapper.querySelector('input[name*="[nilai_ket_tipe]"]');
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
                                                        <button type="button" class="btn btn-danger btn-sm remove-ket-tipe"><i class="mdi mdi-trash-can"></i></button>
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
                                                    if (e.target.classList.contains('remove-ket-tipe')) {
                                                        const inputGroups = ketTipeWrapper.querySelectorAll('.input-group');
                                                        if (inputGroups.length > 1) {
                                                            const ketInput = e.target.closest('.input-group').querySelector('input[name*="[id_ket_tipe]"]');
                                                            if (ketInput && ketInput.value) deletedTipeIds.push(ketInput.value);
                                                            e.target.closest('.input-group').remove();
                                                        } else {
                                                            const group = e.target.closest('.input-group');
                                                            const ket = group.querySelector('input[name*="[ket_tipe]"]');
                                                            const nilai = group.querySelector('input[name*="[nilai_ket_tipe]"]');
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
                                        if (e.target.classList.contains('add-sub-kriteria-block')) {
                                            const kriteriaBlock = e.target.closest('.form-kriteria-block');
                                            const currentKriteriaIndex = kriteriaBlock.getAttribute('data-kriteria-index');
                                            const subWrapper = kriteriaBlock.querySelector('.form-wrapper-sub-kriteria');
                                            let firstSub = kriteriaBlock.querySelector('.form-group-item[data-sub-kriteria-index]');
                                            if (!firstSub) return;
                                            if (typeof subKriteriaIndexes[currentKriteriaIndex] === 'undefined') subKriteriaIndexes[currentKriteriaIndex] = 0;
                                            subKriteriaIndexes[currentKriteriaIndex]++;
                                            const newSubIndex = subKriteriaIndexes[currentKriteriaIndex];
                                            const clone = firstSub.cloneNode(true);
                                            clone.setAttribute('data-sub-kriteria-index', newSubIndex);
                                            clone.querySelectorAll('input, select, textarea').forEach(el => {
                                                const name = el.getAttribute('name');
                                                if (name) {
                                                    const updated = name.replace(/\[sub_kriteria\]\[\d+\]/, `[sub_kriteria][${newSubIndex}]`).replace(/kriteria\[\d+\]/, `kriteria[${currentKriteriaIndex}]`);
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
                                                        <button type="button" class="btn btn-danger btn-sm remove-ket-tipe"><i class="mdi mdi-trash-can"></i></button>
                                                    </div>
                                                    <button type="button" class="btn btn-success text-light add-ket-tipe mt-2">Tambah Keterangan</button>
                                                `;
                                            }
                                            subWrapper.appendChild(clone);
                                            bindDynamicSubKriteriaEvents(kriteriaBlock);
                                        }

                                        if (e.target.classList.contains('remove-sub-kriteria-block')) {
                                            const sub = e.target.closest('.form-group-item[data-sub-kriteria-index]');
                                            const parent = e.target.closest('.form-kriteria-block');
                                            const subCount = parent.querySelectorAll('.form-group-item[data-sub-kriteria-index]').length;
                                            if (subCount <= 1) return;
                                            const id = sub.querySelector('input[name*="[id_judul_kategori]"]')?.value;
                                            if (id) deletedKategoriIds.push(id);
                                            sub.remove();
                                        }

                                        if (e.target.classList.contains('remove-kriteria-block')) {
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
                                        const template = document.querySelector('.form-kriteria-block[data-kriteria-index="0"]') || document.querySelector('.form-kriteria-block');
                                        const clone = template.cloneNode(true);

                                        kriteriaMainIndex++;
                                        subKriteriaIndexes[kriteriaMainIndex] = 0;
                                        clone.setAttribute('data-kriteria-index', kriteriaMainIndex);
                                        clone.querySelectorAll('input, select, textarea').forEach(el => {
                                            const name = el.getAttribute('name');
                                            if (name) {
                                                const updated = name.replace(/kriteria\[\d+\]/, `kriteria[${kriteriaMainIndex}]`);
                                                el.setAttribute('name', updated);
                                            }
                                            if (el.tagName === 'SELECT') el.selectedIndex = 0;
                                            else if (el.type === 'checkbox' || el.type === 'radio') el.checked = false;
                                            else el.value = '';
                                        });

                                        const subWrapper = clone.querySelector('.form-wrapper-sub-kriteria');
                                        subWrapper.innerHTML = `
                                                <div class="form-group-item p-3 border rounded mb-4 bg-theme" data-sub-kriteria-index="0">
                                                    <div class="text-end" style="margin-right: -1.5vw; margin-top: -1.5vw;">
                                                        <button type="button" class="btn btn-danger btn-sm remove-kriteria-block"><i class="mdi mdi-trash-can"></i></button>
                                                    </div>
                                                    <div class="row g-2 mb-2">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Sub Kriteria</label>
                                                            <input type="text" name="kriteria[${kriteriaMainIndex}][sub_kriteria][0][judul_kategori]" class="form-control" placeholder="masukan sub kriteria..." maxlength="250" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Tipe</label>
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
                                                        <label class="form-label">Keterangan Tipe</label>
                                                        <div class="ket-tipe-wrapper text-end">
                                                            <div class="input-group mb-2">
                                                                <input type="text" name="kriteria[${kriteriaMainIndex}][sub_kriteria][0][ket_tipe][]" class="form-control" placeholder="Masukkan keterangan tipe">
                                                                <input type="text" name="kriteria[${kriteriaMainIndex}][sub_kriteria][0][nilai_ket_tipe][]" class="form-control" placeholder="Nilai tipe...">
                                                                <button type="button" class="btn btn-danger btn-sm remove-ket-tipe"><i class="mdi mdi-trash-can"></i></button>
                                                            </div>
                                                            <button type="button" class="btn btn-success text-light add-ket-tipe mt-2">Tambah Keterangan</button>
                                                        </div>
                                                    </div>
                                                    <div class="row g-2 mb-2">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Bobot</label>
                                                            <input type="number" name="kriteria[${kriteriaMainIndex}][sub_kriteria][0][bobot]" class="form-control" placeholder="masukan bobot..." required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Level</label>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection