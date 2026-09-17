document.addEventListener('DOMContentLoaded', () => {
    let kriteriaMainIndex = 0;
    let subKriteriaIndexes = {};

    const config = window.formkategoriConfig || {};
    const allKaryawanData = window.formkategoriData || [];

    // --- Template picker (name-based dropdown) ---
    let templateDataByName = {};
    let selectedTemplateNames = new Set();

    const templateDropdown = document.getElementById('formkategori-template-dropdown');
    const templateToggle = document.getElementById('formkategori-template-toggle');
    const templateToggleText = document.getElementById('formkategori-template-toggle-text');
    const templatePanel = document.getElementById('formkategori-template-panel');
    const templatePanelList = document.getElementById('formkategori-template-panel-list');
    const templateSearchInput = document.getElementById('formkategori-template-search');
    const loadTemplateBtn = document.getElementById('formkategori-load-template-btn');

    const baseKriteriaBlock = document.querySelector('.formkategori-kriteria-block[data-kriteria-index="0"]');
    const mainForm = document.getElementById('formkategori-main-form');

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function renderTemplateNameList(filterText = '') {
        const names = Object.keys(templateDataByName);
        const lower = filterText.trim().toLowerCase();
        const filtered = names.filter(n => n.toLowerCase().includes(lower));

        if (filtered.length === 0) {
            templatePanelList.innerHTML = names.length === 0
                ? '<div class="formkategori-template-panel-empty text-muted small p-2">Belum ada template.</div>'
                : '<div class="formkategori-template-panel-empty text-muted small p-2">Nama tidak ditemukan.</div>';
            return;
        }

        templatePanelList.innerHTML = filtered.map(name => {
            const count = (templateDataByName[name] || []).length;
            const checked = selectedTemplateNames.has(name) ? 'checked' : '';
            const safeId = `formkategori-tpl-${name.replace(/[^a-zA-Z0-9]/g, '-')}`;
            return `
                <label class="formkategori-template-panel-item" for="${safeId}">
                    <input type="checkbox" id="${safeId}" class="formkategori-template-name-checkbox" value="${escapeHtml(name)}" ${checked}>
                    <span class="formkategori-template-panel-name">👤 ${escapeHtml(name)}</span>
                    <span class="formkategori-template-panel-count">${count} template</span>
                </label>
            `;
        }).join('');
    }

    function updateTemplateToggleText() {
        if (selectedTemplateNames.size === 0) {
            templateToggleText.textContent = 'Pilih Nama...';
        } else if (selectedTemplateNames.size === 1) {
            templateToggleText.textContent = Array.from(selectedTemplateNames)[0];
        } else {
            templateToggleText.textContent = `${selectedTemplateNames.size} nama dipilih`;
        }
    }

    fetch(config.routes.list)
        .then(res => res.json())
        .then(data => {
            templateDataByName = data || {};
            renderTemplateNameList();
        })
        .catch(() => {
            templatePanelList.innerHTML = '<div class="formkategori-template-panel-empty text-warning small p-2">Gagal memuat daftar nama.</div>';
        });

    if (templateToggle) {
        templateToggle.addEventListener('click', () => {
            templatePanel.classList.toggle('d-none');
            if (!templatePanel.classList.contains('d-none') && templateSearchInput) {
                templateSearchInput.focus();
            }
        });
    }

    document.addEventListener('click', (e) => {
        if (templateDropdown && !templateDropdown.contains(e.target)) {
            templatePanel.classList.add('d-none');
        }
    });

    if (templateSearchInput) {
        templateSearchInput.addEventListener('input', () => {
            renderTemplateNameList(templateSearchInput.value);
        });
    }

    if (templatePanelList) {
        templatePanelList.addEventListener('change', (e) => {
            if (e.target.classList.contains('formkategori-template-name-checkbox')) {
                const name = e.target.value;
                if (e.target.checked) selectedTemplateNames.add(name);
                else selectedTemplateNames.delete(name);
                updateTemplateToggleText();
            }
        });
    }

    function renderTemplateToForm(data) {
        const container = document.getElementById('formkategori-kriteria-container');
        container.innerHTML = '<h5 class="formkategori-section-title"><i class="fa-solid fa-list-check"></i> Kriteria Penilaian</h5>';
        kriteriaMainIndex = 0;
        subKriteriaIndexes = {};

        data.kriteria.forEach((kData) => {
            const kBlock = baseKriteriaBlock.cloneNode(true);
            kBlock.setAttribute('data-kriteria-index', kriteriaMainIndex);

            kBlock.querySelectorAll('input, select, textarea').forEach(el => {
                const name = el.getAttribute('name');
                if (name) {
                    const newName = name.replace(/kriteria\[\d+\]/g, `kriteria[${kriteriaMainIndex}]`);
                    el.setAttribute('name', newName);
                }
                if (el.type !== 'hidden') {
                    if (el.tagName === 'SELECT') el.selectedIndex = 0;
                    else el.value = '';
                }
            });

            const namaInput = kBlock.querySelector('.formkategori-nama-penilaian');
            if (namaInput) namaInput.value = kData.nama_penilaian || '';

            const subWrapper = kBlock.querySelector('.formkategori-wrapper-sub');
            subWrapper.innerHTML = '';
            subKriteriaIndexes[kriteriaMainIndex] = -1;

            kData.sub_kriteria.forEach((sData) => {
                subKriteriaIndexes[kriteriaMainIndex]++;
                const newSubIdx = subKriteriaIndexes[kriteriaMainIndex];

                const subClone = baseKriteriaBlock.querySelector('.formkategori-group-item').cloneNode(true);
                subClone.setAttribute('data-sub-kriteria-index', newSubIdx);

                subClone.querySelectorAll('input, select, textarea').forEach(el => {
                    let name = el.getAttribute('name');
                    if (name) {
                        name = name.replace(/kriteria\[\d+\]/g, `kriteria[${kriteriaMainIndex}]`);
                        name = name.replace(/\[sub_kriteria\]\[\d+\]/g, `[sub_kriteria][${newSubIdx}]`);
                        el.setAttribute('name', name);
                    }
                    if (el.type !== 'hidden') {
                        if (el.tagName === 'SELECT') el.selectedIndex = 0;
                        else el.value = '';
                    }
                });

                const judulInput = subClone.querySelector('input[name*="[judul_kategori]"]');
                if (judulInput && sData.judul_kategori) judulInput.value = sData.judul_kategori;

                const tipeSelect = subClone.querySelector('.formkategori-tipe-kategori');
                if (tipeSelect && sData.tipe_kategori) tipeSelect.value = sData.tipe_kategori;

                const bobotInput = subClone.querySelector('input[name*="[bobot]"]');
                if (bobotInput && sData.bobot) bobotInput.value = sData.bobot;

                const levelSelect = subClone.querySelector('select[name*="[level]"]');
                if (levelSelect && sData.level) levelSelect.value = sData.level;

                const ketWrapper = subClone.querySelector('.formkategori-ket-tipe-wrapper');
                const ketSection = subClone.querySelector('.formkategori-ket-tipe-section');
                if (ketWrapper) ketWrapper.innerHTML = '';

                const showKet = ['radio', 'select', 'checkbox'];
                if (showKet.includes(sData.tipe_kategori)) {
                    if (ketSection) ketSection.classList.remove('d-none');
                    const ketArr = Array.isArray(sData.ket_tipe) ? sData.ket_tipe : [];
                    const nilaiArr = Array.isArray(sData.nilai_ket_tipe) ? sData.nilai_ket_tipe : [];

                    if (ketWrapper) {
                        ketWrapper.insertAdjacentHTML('beforeend',
                            `<button type="button" class="btn btn-warning btn-sm formkategori-btn-add-ket mt-2 mb-2"><i class="fa-solid fa-plus"></i> Tambah Keterangan</button>`
                        );
                    }

                    if (ketArr.length > 0) {
                        ketArr.forEach((k, idx) => {
                            const val = nilaiArr[idx] !== undefined ? nilaiArr[idx] : '';
                            if (ketWrapper) {
                                ketWrapper.insertAdjacentHTML('beforeend', `
                                    <div class="input-group mb-2">
                                        <input type="text" name="kriteria[${kriteriaMainIndex}][sub_kriteria][${newSubIdx}][ket_tipe][]" class="form-control" placeholder="Masukkan keterangan tipe" value="${k || ''}">
                                        <input type="text" name="kriteria[${kriteriaMainIndex}][sub_kriteria][${newSubIdx}][nilai_ket_tipe][]" class="form-control" placeholder="Nilai tipe..." value="${val || ''}">
                                        <button type="button" class="btn btn-warning btn-sm formkategori-btn-remove-ket"><i class="mdi mdi-trash-can"></i></button>
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
                                    <button type="button" class="btn btn-warning btn-sm formkategori-btn-remove-ket"><i class="mdi mdi-trash-can"></i></button>
                                </div>
                            `);
                        }
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

    if (loadTemplateBtn) {
        loadTemplateBtn.addEventListener('click', () => {
            if (selectedTemplateNames.size === 0) {
                return Swal.fire({
                    icon: 'info',
                    title: 'Info',
                    text: 'Pilih minimal satu nama.',
                    confirmButtonColor: '#6366f1'
                });
            }

            const selected = [];
            selectedTemplateNames.forEach(name => {
                (templateDataByName[name] || []).forEach(t => selected.push(t.kode_form));
            });

            if (selected.length === 0) {
                return Swal.fire({
                    icon: 'info',
                    title: 'Info',
                    text: 'Nama yang dipilih tidak memiliki template.',
                    confirmButtonColor: '#6366f1'
                });
            }

            const container = document.getElementById('formkategori-kriteria-container');
            
            container.innerHTML = `
                <div class="formkategori-space-reserver">
                    <h5 class="formkategori-section-title">
                        <i class="fa-solid fa-circle-notch fa-spin text-primary me-2"></i> Memuat Template...
                    </h5>
                    ${Array(10).fill(0).map(() => `
                        <div class="formkategori-skeleton-row">
                            <div class="formkategori-skeleton-cell formkategori-skeleton-title"></div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="formkategori-skeleton-cell formkategori-skeleton-input"></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="formkategori-skeleton-cell formkategori-skeleton-input"></div>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;

            const loadingModal = new bootstrap.Modal(document.getElementById('formkategori-loading-modal'));
            loadingModal.show();

            const processTemplateLoad = (data) => {
                if (data.error) {
                    Swal.fire({ icon: 'warning', title: 'Perhatian', text: data.error, confirmButtonColor: '#f59e0b' });
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
            };

            if (selected.length === 1) {
                fetch(config.routes.load.replace(':kode', selected[0]))
                    .then(res => res.json())
                    .then(processTemplateLoad)
                    .catch(() => {
                        loadingModal.hide();
                        container.innerHTML = '<h5 class="formkategori-section-title"><i class="fa-solid fa-list-check"></i> Kriteria Penilaian</h5>';
                        Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Gagal memuat template.', confirmButtonColor: '#f59e0b' });
                    });
            } else {
                Promise.all(selected.map(kode => fetch(config.routes.load.replace(':kode', kode)).then(r => r.json())))
                    .then(results => {
                        let mergedKriteria = [];
                        let baseInfo = results[0] || {};
                        results.forEach(data => {
                            if (!data.kriteria) return;
                            data.kriteria.forEach(k => {
                                const existing = mergedKriteria.find(mk => mk.kode_kategori === k.kode_kategori);
                                if (!existing) mergedKriteria.push(k);
                                else {
                                    k.sub_kriteria.forEach(sk => {
                                        if (!existing.sub_kriteria.find(es => es.judul_kategori === sk.judul_kategori)) existing.sub_kriteria.push(sk);
                                    });
                                }
                            });
                        });
                        processTemplateLoad({
                            jenis_form: baseInfo.jenis_form || '',
                            quartal: baseInfo.quartal || '',
                            tahun: baseInfo.tahun || '',
                            nama_penilaian: baseInfo.nama_penilaian || '',
                            nama_evaluator: baseInfo.nama_evaluator || '',
                            tanggal: baseInfo.tanggal || '',
                            kriteria: mergedKriteria
                        });
                    })
                    .catch(() => {
                        loadingModal.hide();
                        container.innerHTML = '<h5 class="formkategori-section-title"><i class="fa-solid fa-list-check"></i> Kriteria Penilaian</h5>';
                        Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Gagal memuat template.', confirmButtonColor: '#f59e0b' });
                    });
            }
        });
    }

    mainForm.addEventListener('submit', function(e) {
        const kriteriaBlocks = document.querySelectorAll('.formkategori-kriteria-block[data-kriteria-index]');
        kriteriaBlocks.forEach(block => {
            const subItems = block.querySelectorAll('.formkategori-group-item[data-sub-kriteria-index]');
            subItems.forEach(sub => {
                const judulInput = sub.querySelector('input[name$="[judul_kategori]"]');
                if (judulInput && !judulInput.value.trim()) sub.remove();
            });
            const remainingSubs = block.querySelectorAll('.formkategori-group-item[data-sub-kriteria-index]');
            if (remainingSubs.length === 0) block.remove();
        });

        const finalKriteria = document.querySelectorAll('.formkategori-kriteria-block[data-kriteria-index]');
        let hasValidSub = false;
        finalKriteria.forEach(block => {
            const subs = block.querySelectorAll('.formkategori-group-item[data-sub-kriteria-index]');
            if (subs.length > 0) hasValidSub = true;
        });

        if (finalKriteria.length === 0 || !hasValidSub) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Validasi', text: 'Minimal 1 kriteria dengan 1 sub kriteria harus diisi.', confirmButtonColor: '#6366f1' });
            return false;
        }
    });

    function bindDynamicSubKriteriaEvents(kriteriaBlock) {
        const subKriteriaItems = kriteriaBlock.querySelectorAll('.formkategori-group-item[data-sub-kriteria-index]');
        subKriteriaItems.forEach(container => {
            const tipeSelect = container.querySelector('.formkategori-tipe-kategori');
            const ketTipeSection = container.querySelector('.formkategori-ket-tipe-section');
            const ketTipeWrapper = container.querySelector('.formkategori-ket-tipe-wrapper');
            const currentKriteriaIndex = kriteriaBlock.getAttribute('data-kriteria-index');
            const currentSubKriteriaIndex = container.getAttribute('data-sub-kriteria-index');

            function toggleKeterangan() {
                const showTypes = ['checkbox', 'radio', 'select'];
                if (showTypes.includes(tipeSelect.value)) {
                    ketTipeSection.classList.remove('d-none');
                    if (ketTipeWrapper && ketTipeWrapper.innerHTML.trim() === '') {
                        ketTipeWrapper.innerHTML = `
                            <button type="button" class="btn btn-warning btn-sm formkategori-btn-add-ket mt-2 mb-2"><i class="fa-solid fa-plus"></i> Tambah Keterangan</button>
                            <div class="input-group mb-2">
                                <input type="text" name="kriteria[${currentKriteriaIndex}][sub_kriteria][${currentSubKriteriaIndex}][ket_tipe][]" class="form-control" placeholder="Masukkan keterangan tipe">
                                <input type="text" name="kriteria[${currentKriteriaIndex}][sub_kriteria][${currentSubKriteriaIndex}][nilai_ket_tipe][]" class="form-control" placeholder="Nilai tipe...">
                                <button type="button" class="btn btn-warning btn-sm formkategori-btn-remove-ket"><i class="mdi mdi-trash-can"></i></button>
                            </div>
                        `;
                    }
                } else {
                    ketTipeSection.classList.add('d-none');
                }
            }

            if (tipeSelect) {
                tipeSelect.addEventListener('change', toggleKeterangan);
                toggleKeterangan();
            }
            if (ketTipeWrapper) {
                ketTipeWrapper.addEventListener('click', (e) => {
                    if (e.target.closest('.formkategori-btn-add-ket')) {
                        ketTipeWrapper.insertAdjacentHTML('beforeend', `
                            <div class="input-group mb-2">
                                <input type="text" name="kriteria[${currentKriteriaIndex}][sub_kriteria][${currentSubKriteriaIndex}][ket_tipe][]" class="form-control" placeholder="Masukkan keterangan tipe">
                                <input type="text" name="kriteria[${currentKriteriaIndex}][sub_kriteria][${currentSubKriteriaIndex}][nilai_ket_tipe][]" class="form-control" placeholder="Nilai tipe...">
                                <button type="button" class="btn btn-warning btn-sm formkategori-btn-remove-ket"><i class="mdi mdi-trash-can"></i></button>
                            </div>
                        `);
                    }

                    if (e.target.closest('.formkategori-btn-remove-ket')) {
                        const inputGroups = ketTipeWrapper.querySelectorAll('.input-group');
                        if (inputGroups.length > 1) {
                            e.target.closest('.input-group').remove();
                        } else {
                            const group = e.target.closest('.input-group');
                            const inputs = group.querySelectorAll('input');
                            inputs.forEach(input => input.value = '');
                        }
                    }
                });
            }
        });
    }

    if (baseKriteriaBlock) bindDynamicSubKriteriaEvents(baseKriteriaBlock);

    document.getElementById('formkategori-kriteria-container').addEventListener('click', function(e) {
        if (e.target.classList.contains('formkategori-btn-add-sub') || e.target.closest('.formkategori-btn-add-sub')) {
            const btn = e.target.classList.contains('formkategori-btn-add-sub') ? e.target : e.target.closest('.formkategori-btn-add-sub');
            const kriteriaBlock = btn.closest('.formkategori-kriteria-block');
            const currentKriteriaIndex = kriteriaBlock.getAttribute('data-kriteria-index');
            const subWrapper = kriteriaBlock.querySelector('.formkategori-wrapper-sub');
            const firstSub = kriteriaBlock.querySelector('.formkategori-group-item[data-sub-kriteria-index]');
            const clone = firstSub.cloneNode(true);

            if (!subKriteriaIndexes[currentKriteriaIndex]) subKriteriaIndexes[currentKriteriaIndex] = 0;
            subKriteriaIndexes[currentKriteriaIndex]++;
            const newSubIndex = subKriteriaIndexes[currentKriteriaIndex];

            clone.setAttribute('data-sub-kriteria-index', newSubIndex);
            clone.querySelectorAll('input, select').forEach(el => {
                const name = el.getAttribute('name');
                if (name) {
                    el.setAttribute('name', name.replace(/\[sub_kriteria\]\[\d+\]/, `[sub_kriteria][${newSubIndex}]`).replace(/\[kriteria\]\[\d+\]/, `[kriteria][${currentKriteriaIndex}]`));
                }
                if (el.tagName === 'SELECT') el.selectedIndex = 0;
                else el.value = '';
            });

            const ketWrapper = clone.querySelector('.formkategori-ket-tipe-wrapper');
            ketWrapper.innerHTML = `
                <button type="button" class="btn btn-warning btn-sm formkategori-btn-add-ket mt-2 mb-2"><i class="fa-solid fa-plus"></i> Tambah Keterangan</button>
                <div class="input-group mb-2">
                    <input type="text" name="kriteria[${currentKriteriaIndex}][sub_kriteria][${newSubIndex}][ket_tipe][]" class="form-control" placeholder="Masukkan keterangan tipe">
                    <input type="text" name="kriteria[${currentKriteriaIndex}][sub_kriteria][${newSubIndex}][nilai_ket_tipe][]" class="form-control" placeholder="Nilai tipe...">
                    <button type="button" class="btn btn-warning btn-sm formkategori-btn-remove-ket"><i class="mdi mdi-trash-can"></i></button>
                </div>
            `;

            subWrapper.appendChild(clone);
            bindDynamicSubKriteriaEvents(kriteriaBlock);
        }

        if (e.target.closest('.formkategori-btn-remove-sub')) {
            const sub = e.target.closest('.formkategori-group-item[data-sub-kriteria-index]');
            const parent = sub.parentElement;
            if (parent.querySelectorAll('.formkategori-group-item[data-sub-kriteria-index]').length > 1) sub.remove();
        }

        if (e.target.closest('.formkategori-btn-remove-kriteria')) {
            const block = e.target.closest('.formkategori-kriteria-block');
            if (document.querySelectorAll('.formkategori-kriteria-block').length > 1) block.remove();
        }
    });

    document.getElementById('formkategori-add-kriteria-main-block').addEventListener('click', () => {
        const container = document.getElementById('formkategori-kriteria-container');
        const clone = baseKriteriaBlock.cloneNode(true);
        subKriteriaIndexes[kriteriaMainIndex] = 0;
        clone.setAttribute('data-kriteria-index', kriteriaMainIndex);

        clone.querySelectorAll('[name^="kriteria[0]"]').forEach(el => {
            const name = el.getAttribute('name');
            if (name) el.setAttribute('name', name.replace(/kriteria\[0\]/g, `kriteria[${kriteriaMainIndex}]`));
            if (el.tagName === 'SELECT') el.selectedIndex = 0;
            else el.value = '';
        });

        const subWrapper = clone.querySelector('.formkategori-wrapper-sub');
        const subItems = subWrapper.querySelectorAll('.formkategori-group-item[data-sub-kriteria-index]');
        subItems.forEach((item, i) => {
            if (i === 0) {
                item.setAttribute('data-sub-kriteria-index', 0);
                item.querySelectorAll('input, select').forEach(el => {
                    el.value = '';
                    if (el.tagName === 'SELECT') el.selectedIndex = 0;
                });
                const ketWrapper = item.querySelector('.formkategori-ket-tipe-wrapper');
                ketWrapper.innerHTML = `
                    <button type="button" class="btn btn-warning btn-sm formkategori-btn-add-ket mt-2 mb-2"><i class="fa-solid fa-plus"></i> Tambah Keterangan</button>
                    <div class="input-group mb-2">
                        <input type="text" name="kriteria[${kriteriaMainIndex}][sub_kriteria][0][ket_tipe][]" class="form-control" placeholder="Masukkan keterangan tipe">
                        <input type="text" name="kriteria[${kriteriaMainIndex}][sub_kriteria][0][nilai_ket_tipe][]" class="form-control" placeholder="Nilai tipe...">
                        <button type="button" class="btn btn-warning btn-sm formkategori-btn-remove-ket"><i class="mdi mdi-trash-can"></i></button>
                    </div>
                `;
            } else {
                item.remove();
            }
        });

        container.appendChild(clone);
        bindDynamicSubKriteriaEvents(clone);
        kriteriaMainIndex++;
    });

    document.getElementById('formkategori-form-karyawan').addEventListener('change', function(e) {
        if (e.target.classList.contains('formkategori-divisi-select')) {
            const selectedDivisi = e.target.value;
            const karyawanSelect = e.target.closest('.formkategori-karyawan-block').querySelector('.formkategori-karyawan-select');
            const idInput = e.target.closest('.formkategori-karyawan-block').querySelector('.formkategori-id-karyawan');
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
        if (e.target.classList.contains('formkategori-karyawan-select')) {
            const selectedOption = e.target.options[e.target.selectedIndex];
            const idInput = e.target.closest('.formkategori-karyawan-block').querySelector('.formkategori-id-karyawan');
            idInput.value = selectedOption.dataset.id || '';
        }
    });

    document.getElementById('formkategori-add-karyawan-block').addEventListener('click', () => {
        const wrapper = document.getElementById('formkategori-form-karyawan');
        const first = wrapper.querySelector('.formkategori-karyawan-block');
        const clone = first.cloneNode(true);
        clone.querySelector('.formkategori-divisi-select').selectedIndex = 0;
        clone.querySelector('.formkategori-karyawan-select').innerHTML = `<option selected disabled>Pilih Karyawan</option>`;
        clone.querySelector('.formkategori-id-karyawan').value = '';
        wrapper.appendChild(clone);
    });

    document.getElementById('formkategori-form-karyawan').addEventListener('click', function(e) {
        if (e.target.closest('.formkategori-btn-remove-karyawan')) {
            const block = e.target.closest('.formkategori-karyawan-block');
            if (document.querySelectorAll('.formkategori-karyawan-block').length > 1) block.remove();
        }
    });
});