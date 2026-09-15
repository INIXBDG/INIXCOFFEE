document.addEventListener('DOMContentLoaded', () => {
    const kriteriaBlocks = Array.from(document.querySelectorAll('.editform-penilaian-form-kriteria-block'));
    let kriteriaMainIndex = 0;
    const subKriteriaIndexes = {};
    let deletedFormIds = [];
    let deletedKategoriIds = [];
    let deletedTipeIds = [];

    kriteriaBlocks.forEach(block => {
        const kIdx = parseInt(block.getAttribute('data-kriteria-index'), 10);
        if (!isNaN(kIdx)) {
            if (kIdx > kriteriaMainIndex) kriteriaMainIndex = kIdx;
            const subItems = Array.from(block.querySelectorAll('.editform-penilaian-form-group-item[data-sub-kriteria-index]'));
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
        const subKriteriaItems = kriteriaBlock.querySelectorAll('.editform-penilaian-form-group-item[data-sub-kriteria-index]');
        subKriteriaItems.forEach(container => {
            const tipeSelect = container.querySelector('.editform-penilaian-tipe-kategori');
            const ketTipeSection = container.querySelector('.editform-penilaian-ket-tipe-section');
            const ketTipeWrapper = container.querySelector('.editform-penilaian-ket-tipe-wrapper');
            const addKeteranganBtn = container.querySelector('.editform-penilaian-add-ket-tipe');
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
                        <button type="button" class="btn btn-danger btn-sm editform-penilaian-remove-ket-tipe"><i class="fa-solid fa-trash-can"></i></button>
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
                    if (e.target.closest('.editform-penilaian-remove-ket-tipe')) {
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

    document.querySelectorAll('.editform-penilaian-form-kriteria-block').forEach(block => bindDynamicSubKriteriaEvents(block));

    document.getElementById('editform-penilaian-kriteria-container').addEventListener('click', function(e) {
        if (e.target.classList.contains('editform-penilaian-add-sub-kriteria-block') || e.target.closest('.editform-penilaian-add-sub-kriteria-block')) {
            const btn = e.target.classList.contains('editform-penilaian-add-sub-kriteria-block') ? e.target : e.target.closest('.editform-penilaian-add-sub-kriteria-block');
            const kriteriaBlock = btn.closest('.editform-penilaian-form-kriteria-block');
            const currentKriteriaIndex = kriteriaBlock.getAttribute('data-kriteria-index');
            const subWrapper = kriteriaBlock.querySelector('.editform-penilaian-form-wrapper-sub-kriteria');
            let firstSub = kriteriaBlock.querySelector('.editform-penilaian-form-group-item[data-sub-kriteria-index]');
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
            const ketWrapper = clone.querySelector('.editform-penilaian-ket-tipe-wrapper');
            if (ketWrapper) {
                ketWrapper.innerHTML = `
                    <div class="input-group mb-2">
                        <input type="text" name="kriteria[${currentKriteriaIndex}][sub_kriteria][${newSubIndex}][ket_tipe][]" class="form-control" placeholder="Masukkan keterangan tipe">
                        <input type="text" name="kriteria[${currentKriteriaIndex}][sub_kriteria][${newSubIndex}][nilai_ket_tipe][]" class="form-control" placeholder="Nilai tipe...">
                        <button type="button" class="btn btn-danger btn-sm editform-penilaian-remove-ket-tipe"><i class="fa-solid fa-trash-can"></i></button>
                    </div>
                    <button type="button" class="editform-penilaian-btn-action success btn-sm editform-penilaian-add-ket-tipe mt-2"><i class="fa-solid fa-plus"></i> Tambah Keterangan</button>
                `;
            }
            subWrapper.appendChild(clone);
            bindDynamicSubKriteriaEvents(kriteriaBlock);
        }

        if (e.target.closest('.editform-penilaian-remove-sub-kriteria-block')) {
            const sub = e.target.closest('.editform-penilaian-form-group-item[data-sub-kriteria-index]');
            const parent = e.target.closest('.editform-penilaian-form-kriteria-block');
            const subCount = parent.querySelectorAll('.editform-penilaian-form-group-item[data-sub-kriteria-index]').length;
            if (subCount <= 1) return;
            const id = sub.querySelector('input[name*="[id_judul_kategori]"]')?.value;
            if (id) deletedKategoriIds.push(id);
            sub.remove();
        }

        if (e.target.closest('.editform-penilaian-remove-kriteria-block')) {
            const allBlocks = document.querySelectorAll('.editform-penilaian-form-kriteria-block');
            if (allBlocks.length <= 1) return;
            const kriteriaBlock = e.target.closest('.editform-penilaian-form-kriteria-block');
            const id = kriteriaBlock.querySelector('input[name*="[id_nama_penilaian]"]')?.value;
            if (id) deletedFormIds.push(id);
            kriteriaBlock.remove();
        }
    });

    document.getElementById('editform-penilaian-add-kriteria-main-block').addEventListener('click', () => {
        const container = document.getElementById('editform-penilaian-kriteria-container');
        const template = document.querySelector('.editform-penilaian-form-kriteria-block[data-kriteria-index="0"]') || document.querySelector('.editform-penilaian-form-kriteria-block');
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

        const subWrapper = clone.querySelector('.editform-penilaian-form-wrapper-sub-kriteria');
        subWrapper.innerHTML = `
            <div class="editform-penilaian-form-group-item" data-sub-kriteria-index="0">
                <button type="button" class="editform-penilaian-btn-remove-block editform-penilaian-remove-sub-kriteria-block" title="Hapus Sub Kriteria">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Judul Sub Kriteria <span class="text-danger">*</span></label>
                        <input type="text" name="kriteria[${kriteriaMainIndex}][sub_kriteria][0][judul_kategori]" class="form-control" placeholder="Masukan sub kriteria..." maxlength="250" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tipe <span class="text-danger">*</span></label>
                        <select name="kriteria[${kriteriaMainIndex}][sub_kriteria][0][tipe_kategori]" class="form-select editform-penilaian-tipe-kategori" required>
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
                <div class="editform-penilaian-ket-tipe-section d-none mb-3">
                    <label class="form-label fw-semibold"><i class="fa-solid fa-tags text-primary me-1"></i> Keterangan Tipe</label>
                    <div class="editform-penilaian-ket-tipe-wrapper text-end">
                        <div class="input-group mb-2">
                            <input type="text" name="kriteria[${kriteriaMainIndex}][sub_kriteria][0][ket_tipe][]" class="form-control" placeholder="Masukkan keterangan tipe">
                            <input type="text" name="kriteria[${kriteriaMainIndex}][sub_kriteria][0][nilai_ket_tipe][]" class="form-control" placeholder="Nilai tipe...">
                            <button type="button" class="btn btn-danger btn-sm editform-penilaian-remove-ket-tipe"><i class="fa-solid fa-trash-can"></i></button>
                        </div>
                        <button type="button" class="editform-penilaian-btn-action success btn-sm editform-penilaian-add-ket-tipe mt-2"><i class="fa-solid fa-plus"></i> Tambah Keterangan</button>
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