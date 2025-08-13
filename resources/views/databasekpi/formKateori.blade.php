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
</style>
@if (session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: "{{ session('success') }}",
        confirmButtonColor: '#3085d6'
    });
</script>
@endif

@if (session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: "{{ session('error') }}",
        confirmButtonColor: '#d33'
    });
</script>
@endif

@if ($errors->any())
<script>
    Swal.fire({
        icon: 'error',
        title: 'Validasi Gagal',
        html: `{!! implode('<br>', $errors->all()) !!}`,
        confirmButtonColor: '#d33'
    });
</script>
@endif

<div class="container" style="margin-bottom: 40px;">
    <a href="{{ route('ketegoriKPI.get') }}" class="btn text-white cl-blue my-2">
        <i class="fa-solid fa-arrow-left"></i> Penilaian
    </a>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
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

                        <div id="form-karyawan" class="mb-4">
                            <h5>Daftar Evaluated</h5>
                            <div class="text-left">
                                <button type="button" class="btn text-white cl-green btn-sm mb-3" id="add-karyawan-block">+ Tambah Evaluated</button>
                            </div>
                            <div class="border rounded p-3 karyawan-block mb-3 bg-light">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label">Divisi</label>
                                        <select name="divisi[]" class="form-control divisi-select" required>
                                            <option selected disabled>Pilih Divisi</option>
                                            @foreach ($divisiList as $div)
                                            <option value="{{ $div }}">{{ $div }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Evaluated</label>
                                        <select name="nama_karyawan[]" class="form-control karyawan-select" required>
                                            <option selected disabled>Pilih Evaluated</option>
                                        </select>
                                        <input type="hidden" name="id_karyawan[]" class="id-karyawan">
                                    </div>
                                </div>
                                <div class="text-right mt-3">
                                    <button type="button" class="btn text-white cl-red btn-sm remove-karyawan-block">Hapus</button>
                                </div>
                            </div>
                        </div>

                        <div id="kriteria-container">
                            <h5>Kriteria Penilaian</h5>
                            <div class="form-kriteria-block border rounded p-3 mb-4 bg-light" data-kriteria-index="0">
                                <div class="text-right">
                                    <button type="button" class="btn text-white cl-red btn-sm remove-kriteria-block">Hapus Kriteria</button>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nama Kriteria</label>
                                    <input type="text" name="kriteria[0][nama_penilaian]" class="form-control" placeholder="Masukan nama kriteria...">
                                </div>
                                <div class="form-wrapper-sub-kriteria">
                                    <div class="form-group-item p-3 border rounded mb-2 bg-white" data-sub-kriteria-index="0">
                                        <div class="row g-2 mb-2">
                                            <div class="col-md-6">
                                                <label class="form-label">Sub Kriteria</label>
                                                <input type="text" name="kriteria[0][sub_kriteria][0][judul_kategori]" class="form-control" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Tipe</label>
                                                <select name="kriteria[0][sub_kriteria][0][tipe_kategori]" class="form-control tipe-kategori" required>
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
                                            <div class="ket-tipe-wrapper text-right">
                                                <div class="input-group mb-2">
                                                    <input type="text" name="kriteria[0][sub_kriteria][0][ket_tipe][]" class="form-control" placeholder="Masukkan keterangan tipe">
                                                    <input type="text" name="kriteria[0][sub_kriteria][0][nilai_ket_tipe][]" class="form-control" placeholder="Nilai tipe...">
                                                    <button type="button" class="btn text-white cl-red btn-sm remove-ket-tipe">Hapus</button>
                                                </div>
                                                <button type="button" class="btn text-white cl-blue text-end btn-sm add-ket-tipe">+ Tambah Keterangan</button>
                                            </div>
                                        </div>
                                        <div class="row g-2 mb-2">
                                            <div class="col-md-6">
                                                <label class="form-label">Bobot</label>
                                                <input type="number" name="kriteria[0][sub_kriteria][0][bobot]" class="form-control" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Level</label>
                                                <select name="kriteria[0][sub_kriteria][0][level]" class="form-control" required>
                                                    <option selected disabled>Pilih</option>
                                                    <option value="required">Harus Diisi</option>
                                                    <option value="null">Tidak Harus</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <button type="button" class="btn text-white cl-red btn-sm remove-sub-kriteria-block">Hapus Sub</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-left">
                                    <button type="button" class="btn text-white cl-green btn-sm add-sub-kriteria-block">+ Sub Kriteria</button>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 text-right">
                            <button type="button" class="btn text-white cl-green btn-sm" id="add-kriteria-main-block">+ Kriteria</button>
                        </div>

                        <div class="text-right">
                            <button type="submit" class="btn text-white cl-blue btn-sm">Simpan Semua</button>
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
                                                <button type="button" class="btn text-white cl-red btn-sm remove-ket-tipe">Hapus</button>
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
                        <button type="button" class="btn text-white cl-red btn-sm remove-ket-tipe">Hapus</button>
                    </div>
                    <button type="button" class="btn text-white cl-blue btn-sm add-ket-tipe mt-2">+ Tambah Keterangan</button>
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
                            <input type="text" name="kriteria[${kriteriaMainIndex}][sub_kriteria][0][nilai_ket_tipe][]" class="form-control" placeholder="Nilai tipe...>
                            <button type="button" class="btn text-white cl-red btn-sm remove-ket-tipe">Hapus</button>
                        </div>
                        <button type="button" class="btn text-white cl-blue btn-sm add-ket-tipe mt-2">+ Tambah Keterangan</button>
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
                                karyawanSelect.innerHTML = `<option selected disabled>Pilih Evaluated</option>`;
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
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection