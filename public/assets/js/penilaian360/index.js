(function() {
    'use strict';

    $(document).ready(function() {
        // 1. Inisialisasi Tahun
        const tahunSelect = document.getElementById('tahunSelectUtama');
        const tahunSekarang = new Date().getFullYear();
        for (let tahun = 2020; tahun <= tahunSekarang; tahun++) {
            const option = document.createElement('option');
            option.value = tahun;
            option.text = tahun;
            if (tahun === tahunSekarang) option.selected = true;
            tahunSelect.appendChild(option);
        }

        // 2. Event Listener untuk Filter
        $('#tahunSelectUtama, #divisiSelectUtama, #jenis_form').on('change', function() {
            loadData();
        });

        // 3. Load Data Awal
        loadData();

        // 4. Event Delegation: Hapus Data
        $(document).on('click', '.btn-hapus', function(e) {
            e.preventDefault();
            const $btn = $(this);
            
            Swal.fire({
                title: 'Yakin ingin menghapus data?',
                text: "Data yang dihapus tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6366f1',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Ya, hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/penilaian/hapus',
                        type: 'POST',
                        data: {
                            _token: window.PENILAIAN_CONFIG.csrfToken,
                            kode_form: $btn.data('kode_form'),
                            id_karyawan: $btn.data('id_karyawan'),
                            jenis_penilaian: $btn.data('jenis_penilaian'),
                            quartal: $btn.data('quartal'),
                            tahun: $btn.data('tahun'),
                            jenis_form: $btn.data('jenis_form')
                        },
                        success: function(response) {
                            Swal.fire({ icon: 'success', title: 'Berhasil!', text: response.message || 'Data berhasil dihapus.', confirmButtonColor: '#6366f1' });
                            loadData();
                        },
                        error: function(xhr) {
                            Swal.fire({ icon: 'error', title: 'Gagal!', text: xhr.responseJSON?.message || 'Terjadi kesalahan.', confirmButtonColor: '#ef4444' });
                        }
                    });
                }
            });
        });

        // 5. Event Delegation: Tampilkan Evaluator
        $(document).on('click', '.btn-show-evaluator', function() {
            const rawEvaluator = $(this).attr('data-evaluator');
            const kodeFormGlobal = $(this).attr('data-kode-form');
            const evaluatorByJenis = JSON.parse(decodeURIComponent(rawEvaluator));

            const jenisPenilaianToKode = {
                'General Manager': 'JP01',
                'Manager/SPV/Team Leader (Atasan Langsung)': 'JP02',
                'Rekan Kerja (Satu Divisi)': 'JP03',
                'Pekerja (Beda Divisi)': 'JP04',
                'Self Appraisal': 'JP05',
                'Self Apprisial': 'JP05'
            };

            let html = '';
            Object.keys(evaluatorByJenis).forEach(function(jenis) {
                const kodeJenis = jenisPenilaianToKode[jenis] || jenis;
                html += `<div class="mb-4"><h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-users me-2 text-primary"></i> ${jenis}</h6><div class="row">`;

                evaluatorByJenis[jenis].forEach(function(ev, index) {
                    const cardClass = ev.is_red ? 'danger' : 'success';
                    html += `
                        <div class="col-12 mb-2">
                            <div class="evaluator-card ${cardClass}">
                                <div>
                                    <span class="badge badge-modern badge-jangka me-2">${index + 1}</span> 
                                    <strong>${ev.name}</strong>
                                </div>
                                <button class="btn-table-action delete btn-action-hapus-evaluator"
                                    data-jenis-penilaian="${kodeJenis}"
                                    data-id-evaluator="${ev.id}"
                                    data-kode-form="${kodeFormGlobal}" title="Hapus Evaluator">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>`;
                });
                html += `</div></div>`;
            });

            $('#evaluatorModalContent').html(html);
            $('#evaluatorModal').modal('show');
        });

        // 6. Event Delegation: Hapus Evaluator
        $(document).on('click', '.btn-action-hapus-evaluator', function() {
            const $button = $(this);
            const jenisPenilaian = $button.data('jenis-penilaian');
            const idEvaluator = $button.data('id-evaluator');
            const kodeForm = $button.data('kode-form');

            Swal.fire({
                title: 'Yakin hapus evaluator ini?',
                text: "Tindakan ini tidak bisa dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6366f1',
                cancelButtonColor: '#ef4444',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/penilaian/hapus-evaluator/${jenisPenilaian}/${idEvaluator}/${kodeForm}`,
                        type: 'POST',
                        data: { _token: window.PENILAIAN_CONFIG.csrfToken },
                        success: function(response) {
                            Swal.fire({ title: 'Terhapus!', text: response.message || 'Evaluator berhasil dihapus.', icon: 'success', confirmButtonColor: '#6366f1' });
                            loadData();
                            $('#evaluatorModal').modal('hide');
                            // Trigger ulang tombol show evaluator yang terakhir diklik jika ada
                            if (window.activeEvaluatorButton) window.activeEvaluatorButton.click();
                        },
                        error: function(xhr) {
                            let msg = 'Gagal menghapus evaluator.';
                            if (xhr.responseJSON?.message) msg = xhr.responseJSON.message;
                            Swal.fire({ title: 'Gagal!', text: msg, icon: 'error', confirmButtonColor: '#ef4444' });
                        }
                    });
                }
            });
        });

        // 7. Event Delegation: Bersihkan Data
        $(document).on('click', '.btn-clean', function(e) {
            e.preventDefault();
            const $btn = $(this);

            Swal.fire({
                title: 'Yakin ingin membersihkan data?',
                text: "Data yang dibersihkan tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6366f1',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Ya, bersihkan!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/penilaian/clean',
                        type: 'POST',
                        data: {
                            _token: window.PENILAIAN_CONFIG.csrfToken,
                            kode_form: $btn.data('kode_form'),
                            id_karyawan: $btn.data('id_karyawan'),
                            jenis_penilaian: $btn.data('jenis_penilaian'),
                            quartal: $btn.data('quartal'),
                            tahun: $btn.data('tahun')
                        },
                        success: function(response) {
                            Swal.fire({ icon: 'success', title: 'Berhasil!', text: response.message || 'Data berhasil dibersihkan.', confirmButtonColor: '#6366f1' });
                            loadData();
                        },
                        error: function(xhr) {
                            Swal.fire({ icon: 'error', title: 'Gagal!', text: xhr.responseJSON?.message || 'Terjadi kesalahan.', confirmButtonColor: '#ef4444' });
                        }
                    });
                }
            });
        });

        // 8. Event Delegation: Share Form (Menggantikan onclick="shareForm(this)")
        $(document).on('click', '.btn-share-action', function(e) {
            e.preventDefault();
            const kodeForm = $(this).data('kode');
            const idKaryawan = $(this).data('id');
            
            const modalBody = $('#modal-body-content');
            const contentSelect = $('#content_select_input');

            modalBody.html(`
                <input type="hidden" value="${kodeForm}" name="kode_form">
                <input type="hidden" value="${idKaryawan}" name="id_evaluated">
            `);
            contentSelect.empty();

            $.ajax({
                url: window.PENILAIAN_CONFIG.getDataRoute,
                type: 'GET',
                success: function(response) {
                    const karyawan = response.karyawan;
                    const divisiSet = new Set(karyawan.map(item => item.divisi).filter(Boolean));
                    const gmList = karyawan.filter(item => item.jabatan === 'GM');

                    const html = `
                        <div class="mb-3">
                            <label class="form-label">Jenis Penilaian</label>
                            <select name="jenis_penilaian" class="form-select" required>
                                <option disabled selected>Pilih Jenis Penilaian</option>
                                <option value="General Manager">General Manager</option>
                                <option value="Manager/SPV/Team Leader (Atasan Langsung)">Manager/SPV/Team Leader (Atasan Langsung)</option>
                                <option value="Rekan Kerja (Satu Divisi)">Rekan Kerja (Satu Divisi)</option>
                                <option value="Pekerja (Beda Divisi)">Pekerja (Beda Divisi)</option>
                                <option value="Self Apprisial">Self Apprisial</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pilih Divisi</label>
                            <select id="multiple-select-field-divisi" name="divisi[]" multiple class="form-select"></select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pilih Evaluator</label>
                            <select id="multiple-select-field-karyawan" name="id_karyawan[]" multiple class="form-select"></select>
                        </div>
                    `;
                    contentSelect.append(html);

                    const $jenisPenilaianSelect = $('select[name="jenis_penilaian"]');
                    const $divisiSelect = $('#multiple-select-field-divisi');
                    const $evaluatorSelect = $('#multiple-select-field-karyawan');

                    $divisiSelect.select2({ dropdownParent: $('#shareEvaluatorModal'), width: '100%', placeholder: 'Pilih Divisi', closeOnSelect: false });
                    $evaluatorSelect.select2({ dropdownParent: $('#shareEvaluatorModal'), width: '100%', placeholder: 'Pilih Evaluator', closeOnSelect: false });

                    function renderDivisiSelect(allowGm) {
                        $divisiSelect.empty();
                        $('#defaultDivisiInput').remove();
                        $divisiSelect.prop('disabled', false);

                        divisiSet.forEach(divisi => {
                            $divisiSelect.append(new Option(divisi, divisi));
                        });

                        $divisiSelect.trigger('change');
                    }

                    function updateEvaluatorOptions(selectedDivisi, allowGm = false) {
                        let filtered = karyawan.filter(item => selectedDivisi.includes(item.divisi));
                        modalBody.find('input[data-gm="true"]').remove();
                        
                        if (allowGm) {
                            gmList.forEach(gm => {
                                if (!filtered.find(k => k.id === gm.id)) filtered.push(gm);
                                modalBody.append(`<input type="hidden" name="id_karyawan[]" value="${gm.id}" data-gm="true">`);
                            });
                        }
                        
                        let options = '';
                        filtered.forEach(item => {
                            const isGM = gmList.find(gm => gm.id === item.id);
                            const isSelected = isGM && allowGm;
                            const isDisabled = isGM && allowGm;
                            options += `<option value="${item.id}" ${isSelected ? 'selected' : ''} ${isDisabled ? 'disabled' : ''}>${item.nama_lengkap} - ${item.divisi}</option>`;
                        });
                        $evaluatorSelect.html(options).trigger('change');
                    }

                    $jenisPenilaianSelect.on('change', function() {
                        const jenis = $(this).val();
                        const allowGm = jenis === 'General Manager';
                        renderDivisiSelect(allowGm);
                        const finalDivisi = selectedDivisi;
                        updateEvaluatorOptions(finalDivisi, allowGm);
                    });

                    $divisiSelect.on('change', function() {
                        const selectedDivisi = $(this).val() || [];
                        const jenis = $jenisPenilaianSelect.val();
                        const allowGm = jenis === 'General Manager';
                        const finalDivisi = allowGm ? [] : selectedDivisi;
                        updateEvaluatorOptions(finalDivisi, allowGm);
                    });

                    renderDivisiSelect(false);
                    updateEvaluatorOptions([], false);
                }
            });
            
            $('#shareEvaluatorModal').modal('show');
        });
    });

    function loadData() {
        // 1. TAMPILKAN SKELETON
        $('#skeletonPenilaian').removeClass('d-none');

        const selectedTahun = $('#tahunSelectUtama').val();
        const selectedDivisi = $('#divisiSelectUtama').val();
        const jenis_form = $('#jenis_form').val();

        $.ajax({
            url: window.PENILAIAN_CONFIG.getDataRoute,
            type: 'GET',
            data: { 
                tahun: selectedTahun, 
                divisi: selectedDivisi, 
                jenis_form: jenis_form 
            },
            success: function(response) {
                let data = response.data;

                if ($.fn.DataTable.isDataTable('#table_karyawan')) {
                    $('#table_karyawan').DataTable().destroy();
                }

                $('#table_karyawan').DataTable({
                    data: data.map((item, index) => {
                        let jenis = 'not_found';
                        if (item.jenis_penilaian === 'General Manager') jenis = 'J01P';
                        else if (item.jenis_penilaian === 'Manager/SPV/Team Leader (Atasan Langsung)') jenis = 'J02P';
                        else if (item.jenis_penilaian === 'Rekan Kerja (Satu Divisi)') jenis = 'J03P';
                        else if (item.jenis_penilaian === 'Pekerja (Beda Divisi)') jenis = 'J04P';
                        else if (item.jenis_penilaian === 'Self Apprisial') jenis = 'J05P';

                        let button_evaluatorShow = (item.evaluator_by_jenis && Object.keys(item.evaluator_by_jenis).length > 0) ?
                            `<button type="button" class="btn-table-action edit btn-show-evaluator" data-evaluator='${encodeURIComponent(JSON.stringify(item.evaluator_by_jenis))}' data-kode-form="${item.kode_form}" title="Lihat Evaluator"><i class="fa-solid fa-users"></i></button>` :
                            `<span class="text-muted">-</span>`;

                        return [
                            index + 1,
                            button_evaluatorShow,
                            `<strong>${item.evaluated}</strong>`,
                            `<span class="badge badge-modern badge-jangka">${item.evaluatedDivisi || '-'}</span>`,
                            item.tanggal,
                            `<span class="badge badge-modern badge-kode">${item.kode_form_label}</span>`,
                            item.tahun,
                            `<div class="d-flex gap-2">
                                <button type="button" class="btn-table-action share btn-share-action" data-kode="${item.kode_form}" data-id="${item.id_karyawan}" title="Share"><i class="fa-solid fa-paper-plane"></i></button>
                                <a href="/penilaian/detail/data-penilaian/${item.kode_form}/${item.id_karyawan}/${window.PENILAIAN_CONFIG.tipe}" class="btn-table-action edit" title="Detail"><i class="fa-solid fa-magnifying-glass"></i></a>
                                <button type="button" class="btn-table-action clean btn-clean" data-kode_form="${item.kode_form}" data-id_karyawan="${item.id_karyawan}" data-jenis_penilaian="${jenis}" data-tahun="${item.tahun}" data-jenis_form="${window.PENILAIAN_CONFIG.tipe}" title="Bersihkan"><i class="fa-solid fa-brush"></i></button>
                                <button type="button" class="btn-table-action delete btn-hapus" data-kode_form="${item.kode_form}" data-id_karyawan="${item.id_karyawan}" data-jenis_penilaian="${item.jenis_penilaian}" data-tahun="${item.tahun}" data-jenis_form="${window.PENILAIAN_CONFIG.tipe}" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                            </div>`
                        ];
                    }),
                    pageLength: 10,
                    lengthMenu: [5, 10, 25, 50, 100],
                    responsive: true,
                    ordering: false,
                    dom: "<'row mb-2'<'col-md-6 custom-dt-length'l><'col-md-6 text-end custom-dt-search'f>>" +
                         "<'row'<'col-sm-12'tr>>" +
                         "<'row mt-2'<'col-md-5 custom-dt-info'i><'col-md-7 custom-dt-pagination'p>>",
                    language: {
                        search: "",
                        searchPlaceholder: "Cari data...",
                        lengthMenu: "_MENU_ per halaman",
                        info: "Menampilkan _START_ - _END_ dari _TOTAL_ entri",
                        infoEmpty: "Data Tidak Ditemukan",
                        paginate: { first: "Awal", last: "Akhir", previous: "<", next: ">" }
                    },
                    // 2. SEMBUNYIKAN SKELETON SETELAH DATATABLES SELESAI DI-RENDER
                    initComplete: function() {
                        setTimeout(() => {
                            $('#skeletonPenilaian').addClass('d-none');
                        }, 300); // Delay 300ms agar transisi terlihat lebih halus
                    }
                });
            },
            error: function() {
                // Sembunyikan skeleton juga jika terjadi error
                $('#skeletonPenilaian').addClass('d-none');
            }
        });
    }

    window.loadData = loadData;
})();