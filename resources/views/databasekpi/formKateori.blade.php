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
                    <span></span>Tambah Penilaian <i class="mdi mdi-alert-circle-outline icon-sm text-primary align-middle" data-bs-toggle="tooltip" data-bs-placement="top" title="Buat Form penilaian 360° untuk karyawan. beberapa input memiliki limit text!."></i>
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

                                <div class="mb-4">
                                    <h5>Jenis Form</h5>
                                    <div class="border rounded p-3 karyawan-block mb-3 mt-1">
                                        <div class="row g-2 mt-2">
                                            <div class="text-end" style="margin-left: 2vw; margin-top: -2.2vw;">
                                                <button type="button" class="btn btn-danger btn-sm remove-karyawan-block"><i class="mdi mdi-trash-can"></i></button>
                                            </div>
                                            <div class="col">
                                                <label class="form-label">Jenis Form</label>
                                                <select name="jenis_form" class="form-select divisi-select" required>
                                                    <option selected disabled>Pilih Jenis Form</option>
                                                    <option value="Rutin">Penilaian Rutin</option>
                                                    <option value="Kontrak">Penilaian Kontrak</option>
                                                    <option value="Probation">Penilaian Probation</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="form-karyawan" class="mb-4">
                                    <h5>Daftar Yang Dinilai</h5>
                                    <div class="text-right">
                                        <button type="button" class="btn btn-success mb-2" id="add-karyawan-block">+ Tambah Yang Dinilai</button>
                                    </div>
                                    <div class="border rounded p-3 karyawan-block mb-3 mt-1">
                                        <div class="row g-2 mt-2">
                                            <div class="text-end" style="margin-left: 2vw; margin-top: -2.2vw;">
                                                <button type="button" class="btn btn-danger btn-sm remove-karyawan-block"><i class="mdi mdi-trash-can"></i></button>
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
                                                <select name="nama_karyawan[]" class="form-select karyawan-select" required>
                                                    <option selected disabled>Pilih Dinilai</option>
                                                </select>
                                                <input type="hidden" name="id_karyawan[]" class="id-karyawan">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="kriteria-container">
                                    <h5>Kriteria Penilaian</h5>
                                    <div class="form-kriteria-block border rounded p-3 mb-4 bg-theme" data-kriteria-index="0">
                                        <div class="text-end" style="margin-right: -2vw; margin-top: -2vw;">
                                            <button type="button" class="btn btn-danger btn-sm remove-kriteria-block"><i class="mdi mdi-trash-can"></i></button>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Nama Kriteria</label>
                                            <input type="text" name="kriteria[0][nama_penilaian]" class="form-control" placeholder="Masukan nama kriteria..." maxlength="250" title="Hanya huruf dan spasi, maksimal 250 karakter">
                                            <small id="kriteriaHelp" class="form-text text-muted">limit text itu tidak lebih dari 250 kata.</small>
                                        </div>
                                        <div class="form-wrapper-sub-kriteria">
                                            <div class="form-group-item p-3 border rounded mb-4 bg-theme" data-sub-kriteria-index="0">
                                                <div class="text-end" style="margin-right: -1vw; margin-top: -2vw;">
                                                    <button type="button" class="btn btn-danger btn-sm remove-sub-kriteria-block"><i class="mdi mdi-trash-can"></i></button>
                                                </div>
                                                <div class="row g-2 mb-2">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Sub Kriteria</label>
                                                        <input type="text" name="kriteria[0][sub_kriteria][0][judul_kategori]" maxlength="250" class="form-control" placeholder="masukan sub kriteria..." required title="Hanya huruf dan spasi, maksimal 250 karakter">
                                                        <small id="kriteriaHelp" class="form-text text-muted">limit text itu tidak lebih dari 250 kata.</small>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Tipe</label>
                                                        <select name="kriteria[0][sub_kriteria][0][tipe_kategori]" class="form-select tipe-kategori" required>
                                                            <option selected disabled>Pilih tipe</option>
                                                            <option value="text">Teks</option>
                                                            <option value="radio">Pilihan (Radio)</option>
                                                            <option value="checkbox">Kotak Centang</option>
                                                            <option value="number">Angka</option>
                                                            <option value="range">Rentang</option>
                                                            <option value="textarea">Teks Panjang (untuk catatan evaluator)</option>
                                                            <option value="select">Pilihan Dropdown</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="ket-tipe-section d-none mb-3">
                                                    <label class="form-label">Keterangan Tipe</label>
                                                    <div class="ket-tipe-wrapper text-end">
                                                        <div class="input-group mb-2">
                                                            <input type="text" name="kriteria[0][sub_kriteria][0][ket_tipe][]" class="form-control" placeholder="Masukkan keterangan tipe">
                                                            <input type="text" name="kriteria[0][sub_kriteria][0][nilai_ket_tipe][]" class="form-control" placeholder="Nilai tipe...">
                                                            <button type="button" class="btn btn-danger btn-sm remove-ket-tipe"><i class="mdi mdi-trash-can"></i></button>
                                                        </div>
                                                        <button type="button" class="btn btn-success text-end add-ket-tipe">Tambah Keterangan</button>
                                                    </div>
                                                </div>
                                                <div class="row g-2 mb-2">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Bobot</label>
                                                        <input type="number" name="kriteria[0][sub_kriteria][0][bobot]" placeholder="masukan bobot..." class="form-control" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Level</label>
                                                        <select name="kriteria[0][sub_kriteria][0][level]" class="form-select" required>
                                                            <option selected disabled>Pilih</option>
                                                            <option value="required">Harus Diisi</option>
                                                            <option value="null">Tidak Harus</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <button type="button" class="btn btn-success add-sub-kriteria-block">Tambah Sub Kriteria</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 text-end">
                                    <button type="button" class="btn btn-success" id="add-kriteria-main-block">Tanbah Kriteria</button>
                                </div>

                                <div class="text-start">
                                    <button type="submit" class="btn btn-info">Simpan Semua</button>
                                </div>
                            </form>

                            <script>
                                document.addEventListener('DOMContentLoaded', () => {
                                    let kriteriaMainIndex = 0;
                                    let subKriteriaIndexes = {
                                        0: 0
                                    };

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
                                                if (showTypes.includes(tipeSelect.value)) {
                                                    ketTipeSection.classList.remove('d-none');
                                                } else {
                                                    ketTipeSection.classList.add('d-none');
                                                    ketTipeWrapper.querySelectorAll('.input-group').forEach((el, i) => {
                                                        if (i > 0) el.remove();
                                                    });
                                                    const firstKet = ketTipeWrapper.querySelector('input[name^="kriteria"][name$="[ket_tipe][]"]');
                                                    const firstNilai = ketTipeWrapper.querySelector('input[name^="kriteria"][name$="[nilai_ket_tipe][]"]');
                                                    if (firstKet) firstKet.value = '';
                                                    if (firstNilai) firstNilai.value = '';
                                                }
                                            }

                                            tipeSelect.removeEventListener('change', toggleKeterangan);
                                            tipeSelect.addEventListener('change', toggleKeterangan);
                                            toggleKeterangan();

                                            if (addKeteranganBtn) {
                                                const oldHandler = addKeteranganBtn.__clickHandler;
                                                if (oldHandler) {
                                                    addKeteranganBtn.removeEventListener('click', oldHandler);
                                                }
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

                                            const oldRemoveHandler = ketTipeWrapper.__removeHandler;
                                            if (oldRemoveHandler) {
                                                ketTipeWrapper.removeEventListener('click', oldRemoveHandler);
                                            }
                                            const newRemoveHandler = (e) => {
                                                if (e.target.classList.contains('remove-ket-tipe')) {
                                                    const inputGroups = ketTipeWrapper.querySelectorAll('.input-group');
                                                    if (inputGroups.length > 1) {
                                                        e.target.closest('.input-group').remove();
                                                    } else {
                                                        const group = e.target.closest('.input-group');
                                                        const ket = group.querySelector('input[name^="kriteria"][name$="[ket_tipe][]"]');
                                                        const nilai = group.querySelector('input[name^="kriteria"][name$="[nilai_ket_tipe][]"]');
                                                        if (ket) ket.value = '';
                                                        if (nilai) nilai.value = '';
                                                    }
                                                }
                                            };
                                            ketTipeWrapper.addEventListener('click', newRemoveHandler);
                                            ketTipeWrapper.__removeHandler = newRemoveHandler;
                                        });
                                    }

                                    const firstKriteriaBlock = document.querySelector('.form-kriteria-block');
                                    bindDynamicSubKriteriaEvents(firstKriteriaBlock);

                                    document.getElementById('kriteria-container').addEventListener('click', function(e) {
                                        if (e.target.classList.contains('add-sub-kriteria-block')) {
                                            const kriteriaBlock = e.target.closest('.form-kriteria-block');
                                            const currentKriteriaIndex = kriteriaBlock.getAttribute('data-kriteria-index');
                                            const subWrapper = kriteriaBlock.querySelector('.form-wrapper-sub-kriteria');
                                            const firstSub = kriteriaBlock.querySelector('.form-group-item[data-sub-kriteria-index]');
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
                                                    const updated = name
                                                        .replace(/\[sub_kriteria\]\[\d+\]/, `[sub_kriteria][${newSubIndex}]`)
                                                        .replace(/\[kriteria\]\[\d+\]/, `[kriteria][${currentKriteriaIndex}]`);
                                                    el.setAttribute('name', updated);
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
                                            <button type="button" class="btn btn-success cl-blue add-ket-tipe mt-2">Tambah Keterangan</button>
                                        `;

                                            subWrapper.appendChild(clone);
                                            bindDynamicSubKriteriaEvents(kriteriaBlock);
                                        }

                                        if (e.target.classList.contains('remove-sub-kriteria-block')) {
                                            const sub = e.target.closest('.form-group-item[data-sub-kriteria-index]');
                                            const parent = e.target.closest('.form-kriteria-block');
                                            if (parent.querySelectorAll('.form-group-item[data-sub-kriteria-index]').length > 1) {
                                                sub.remove();
                                            }
                                        }

                                        if (e.target.classList.contains('remove-kriteria-block')) {
                                            const kriteriaBlock = e.target.closest('.form-kriteria-block');
                                            if (document.querySelectorAll('.form-kriteria-block').length > 1) {
                                                kriteriaBlock.remove();
                                            }
                                        }
                                    });

                                    document.getElementById('add-kriteria-main-block').addEventListener('click', () => {
                                        const container = document.getElementById('kriteria-container');
                                        const template = document.querySelector('.form-kriteria-block[data-kriteria-index="0"]');
                                        const clone = template.cloneNode(true);

                                        kriteriaMainIndex++;
                                        subKriteriaIndexes[kriteriaMainIndex] = 0;

                                        clone.setAttribute('data-kriteria-index', kriteriaMainIndex);

                                        clone.querySelectorAll('[name^="kriteria[0]"]').forEach(el => {
                                            const name = el.getAttribute('name');
                                            if (name) {
                                                const updated = name.replace(/kriteria\[0\]/, `kriteria[${kriteriaMainIndex}]`);
                                                el.setAttribute('name', updated);
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
                                                    if (el.tagName === 'SELECT') el.selectedIndex = 0;
                                                    else el.value = '';
                                                });

                                                const ketWrapper = item.querySelector('.ket-tipe-wrapper');
                                                ketWrapper.innerHTML = `
                                                    <div class="input-group mb-2">
                                                        <input type="text" name="kriteria[${kriteriaMainIndex}][sub_kriteria][0][ket_tipe][]" class="form-control" placeholder="Masukkan keterangan tipe">
                                                        <input type="text" name="kriteria[${kriteriaMainIndex}][sub_kriteria][0][nilai_ket_tipe][]" class="form-control" placeholder="Nilai tipe...">
                                                        <button type="button" class="btn btn-danger btn-sm remove-ket-tipe"><i class="mdi mdi-trash-can"></i></button>
                                                    </div>
                                                    <button type="button" class="btn btn-success cl-blue add-ket-tipe mt-2">Tambah Keterangan</button>
                                                `;
                                            } else {
                                                item.remove();
                                            }
                                        });

                                        container.appendChild(clone);
                                        bindDynamicSubKriteriaEvents(clone);
                                    });

                                    function addKaryawanBlock() {
                                        const wrapper = document.getElementById('form-karyawan');
                                        const first = wrapper.querySelector('.karyawan-block');
                                        const clone = first.cloneNode(true);

                                        clone.querySelector('.divisi-select').selectedIndex = 0;
                                        const karyawanSelect = clone.querySelector('.karyawan-select');
                                        karyawanSelect.innerHTML = `<option selected disabled>Yang Dinilai</option>`;
                                        clone.querySelector('.id-karyawan').value = '';

                                        wrapper.appendChild(clone);
                                    }

                                    document.getElementById('add-karyawan-block').addEventListener('click', addKaryawanBlock);

                                    document.getElementById('form-karyawan').addEventListener('change', function(e) {
                                        if (e.target.classList.contains('divisi-select')) {
                                            const selectedDivisi = e.target.value;
                                            const karyawanSelect = e.target.closest('.karyawan-block').querySelector('.karyawan-select');
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

                                    document.getElementById('form-karyawan').addEventListener('click', function(e) {
                                        if (e.target.classList.contains('remove-karyawan-block')) {
                                            const block = e.target.closest('.karyawan-block');
                                            if (document.querySelectorAll('.karyawan-block').length > 1) {
                                                block.remove();
                                            }
                                        }
                                    });
                                });

                                document.addEventListener("DOMContentLoaded", function() {
                                    Swal.fire({
                                        title: "Perhatian!",
                                        text: "Saat membuat penilaian, diharapkan agar anda berhati-hati saat pemilihan tipe input, dan jangan membuat nama sub kriteria yang sama (meskipun di kriterianya berbeda)!",
                                        icon: "warning",
                                        confirmButtonText: "Saya Mengerti"
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