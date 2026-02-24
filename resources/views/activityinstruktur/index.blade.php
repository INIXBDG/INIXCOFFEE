@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-9">
            <div class="card" style="width: 100%">
                <div class="card-body d-block justify-content-center">
                    <h3 class="mb-4">Laporan Aktivitas Instruktur</h3>
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card" style="width: 100%">
                <div class="card-body d-block justify-content-center">
                    <h3 class="mb-4">Summary</h3>
                    <div id="summaryContent">
                        <div class="text-center py-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="activityModal" tabindex="-1" aria-labelledby="activityModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="activityFormDetail" method="POST" enctype="multipart/form-data">
                    @csrf 
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title" id="activityModalLabel">Aktivitas <span id="modalDate"></span></h5>
                            <div class="form-check form-switch mt-1 d-none" id="editModeContainer">
                                <input class="form-check-input" type="checkbox" id="enableEditMode">
                                <label class="form-check-label small text-muted" for="enableEditMode">Mode Edit</label>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body">
                        <div id="lockAlert" class="alert alert-danger d-none"></div>
                        
                        <input type="hidden" name="activity_date" id="formActivityDate">
                        <input type="hidden" name="activity_id" id="formActivityId">
                        <input type="hidden" value="manual" name="activity_subtype" id="activity_subtype">

                        <div class="mb-3">
                            <label for="formActivityType" class="form-label">Tipe Aktivitas</label>
                            <select class="form-select editable-field" id="formActivityType" name="activity_type">
                                <option value="pilih" selected>Pilih Aktivitas</option>
                                <option value="Sharing Knowledge">Sharing Knowledge</option>
                                <option value="Webinar">Webinar</option>
                                <option value="Projek">Projek</option>
                                <option value="Pembuatan Materi">Pembuatan Materi</option>
                                <option value="Pembuatan Silabus">Pembuatan Silabus</option>
                                <option value="Aktivitas Internal Kantor">Aktivitas Internal Kantor</option>
                                <option value="Meeting">Meeting</option>
                                <option value="Research / Pendalaman Materi">Research / Pendalaman Materi</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="formActivity" class="form-label">Judul Aktivitas</label>
                            <input type="text" class="form-control editable-field" id="formActivity" name="activity" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="formDesc" class="form-label">Deskripsi / Detail</label>
                            <textarea class="form-control editable-field" id="formDesc" name="desc" rows="3"></textarea>
                        </div>

                        <div id="proofUploadSection" class="mt-4 border p-3 rounded d-none">
                            <h6>Bukti Penyelesaian Aktivitas</h6>
                            <div class="mb-3">
                                <label for="formDoc" class="form-label">Link Dokumen Bukti</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="formDoc" name="doc" placeholder="https://...">
                                    <a href="#" id="btnViewDoc" target="_blank" class="btn btn-outline-primary d-none">
                                        <i class="bi bi-box-arrow-up-right"></i> Buka
                                    </a>
                                </div>
                                <small id="docInfo" class="form-text text-muted"></small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary" id="saveActivityDetailBtn">Simpan</button>
                    </div>
                </form>
                
                <div id="rkmDetailSection" class="rounded p-3 mb-3 d-none">
                    </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" />
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // URL API dari route Laravel
    const API_DATA_URL = "{{ route('api.activities') }}";
    const API_SUMMARY_URL = "{{ route('api.activities.summary') }}"; // Route Baru
    const API_STORE_URL = "{{ route('api.activities.store') }}";
    const API_PROOF_UPDATE_URL = "{{ route('api.activities.proof_update') }}"; // Untuk completed_at & doc (akan dibuat)
    $(document).ready(function() {
        var calendarEl = document.getElementById('calendar');
        $('#rkmDetailSection').addClass('d-none');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek'
            },
            locale: 'id', // Opsional: Tampilkan kalender dalam bahasa Indonesia
            firstDay: 1, // Minggu dimulai hari Senin
            // 2. Tambahkan Event Listener datesSet
            // Event ini memicu fungsi loadSummary setiap kali user ganti bulan/minggu
            datesSet: function(viewInfo) {
                loadSummary(viewInfo.startStr, viewInfo.endStr);
            },
            // Load data dari backend
            events: function(fetchInfo, successCallback, failureCallback) {
                $.ajax({
                    url: API_DATA_URL,
                    method: 'GET',
                    data: {
                        start: fetchInfo.startStr,
                        end: fetchInfo.endStr
                    },
                    success: function(data) {
                        successCallback(data);
                    },
                    error: function() {
                        failureCallback();
                    }
                });
            },

            dateClick: function(info) {
                showActivityModal(
                    'store',
                    info.dateStr,
                    null,
                    null,
                    ''
                );
            },

            // Ketika mengklik event yang sudah ada
            eventClick: function(info) {
                showActivityModal('show', info.event.startStr, info.event.id, info.event.extendedProps, info.event.title);
            }
        });
       
        calendar.render();

        function loadSummary(start, end) {
            // Tampilkan loading state
            $('#summaryContent').html(`
                <div class="text-center py-3">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <span class="ms-2 small text-muted">Memuat data...</span>
                </div>
            `);

            $.ajax({
                url: API_SUMMARY_URL,
                method: 'GET',
                data: {
                    start: start,
                    end: end
                },
                success: function(response) {
                    renderSummary(response);
                },
                error: function(xhr) {
                    $('#summaryContent').html('<div class="alert alert-danger p-2 small">Gagal memuat summary.</div>');
                }
            });
        }

        // Ganti fungsi renderSummary yang lama dengan yang ini
        function renderSummary(data) {
            let html = '';

            // --- BAGIAN 1: RKM (MENGAJAR) ---
            html += `
                <div class="mb-3">
                    <h6 class="border-bottom pb-2 mb-2 text-success">
                        <i class="bi bi-person-video3 me-1"></i> Mengajar
                        <span class="badge bg-success float-end">${data.rkm_summary.total_all}</span>
                    </h6>
                    <ul class="list-group list-group-flush small">
            `;

            if (Object.keys(data.rkm_summary.details).length > 0) {
                $.each(data.rkm_summary.details, function(name, count) {
                    html += `
                        <li class="list-group-item d-flex justify-content-between px-0 py-1">
                            <span class="text-truncate" style="max-width: 180px;" title="${name}">${name}</span>
                            <span class="fw-bold">${count}</span>
                        </li>
                    `;
                });
            } else {
                html += `<li class="list-group-item text-muted px-0 fst-italic">Tidak ada jadwal.</li>`;
            }
            html += `</ul></div>`;

            // --- BAGIAN 5: AKTIVITAS MANUAL ---
            html += `
                <div class="mb-3">
                    <h6 class="border-bottom pb-2 mb-2 text-primary">
                        <i class="bi bi-pencil-square me-1"></i> Aktivitas
                        <span class="badge bg-primary float-end">${data.manual_summary.total_all}</span>
                    </h6>
                    <ul class="list-group list-group-flush small">
            `;

            if (Object.keys(data.manual_summary.details).length > 0) {
                $.each(data.manual_summary.details, function(type, stats) {
                    
                    // List Nama Instruktur
                    let usersHtml = '<div class="mt-2 ps-3 border-start">';
                    if (stats.users) {
                        $.each(stats.users, function(userName, userCount) {
                            usersHtml += `
                                <div class="mb-1 text-secondary">
                                    ${userName} <span class="badge bg-secondary">${userCount}</span>
                                </div>
                            `;
                        });
                    }
                    usersHtml += '</div>';

                    // Item Utama
                    html += `
                        <li class="list-group-item px-0 py-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-dark" style="font-size: 1rem;">
                                    ${type}
                                </span>
                                <span class="badge bg-light text-dark border">${stats.total}</span>
                            </div>
                            <div class="progress mb-2" style="height: 3px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: ${(stats.selesai / stats.total) * 100}%"></div>
                                <div class="progress-bar bg-warning" role="progressbar" style="width: ${(stats.on_progress / stats.total) * 100}%"></div>
                            </div>
                            ${usersHtml}
                        </li>
                    `;
                });
            } else {
                html += `<li class="list-group-item text-muted px-0 fst-italic">Tidak ada aktivitas.</li>`;
            }
            
            html += `</ul></div>`;
             // --- BAGIAN 2: IZIN 3 JAM (BARU) ---
            if (data.izin_summary && data.izin_summary.total_all > 0) {
                html += `
                    <div class="mb-3">
                        <h6 class="border-bottom pb-2 mb-2 text-info">
                            <i class="bi bi-clock me-1"></i> Izin 3 Jam
                            <span class="badge bg-info text-dark float-end">${data.izin_summary.total_all}</span>
                        </h6>
                        <ul class="list-group list-group-flush small">
                `;
                $.each(data.izin_summary.details, function(name, count) {
                    html += `
                        <li class="list-group-item d-flex justify-content-between px-0 py-1">
                            <span class="text-truncate" style="max-width: 180px;">${name}</span>
                            <span class="fw-bold">${count}</span>
                        </li>
                    `;
                });
                html += `</ul></div>`;
            }

            // --- BAGIAN 3: CUTI (BARU) ---
            if (data.cuti_summary && data.cuti_summary.total_all > 0) {
                html += `
                    <div class="mb-3">
                        <h6 class="border-bottom pb-2 mb-2 text-warning">
                            <i class="bi bi-airplane me-1"></i> Cuti
                            <span class="badge bg-warning text-dark float-end">${data.cuti_summary.total_all} hari</span>
                        </h6>
                        <ul class="list-group list-group-flush small">
                `;
                $.each(data.cuti_summary.details, function(name, count) {
                    html += `
                        <li class="list-group-item d-flex justify-content-between px-0 py-1">
                            <span class="text-truncate" style="max-width: 180px;">${name}</span>
                            <span class="fw-bold">${count} hari</span>
                        </li>
                    `;
                });
                html += `</ul></div>`;
            }

            // --- BAGIAN 4: SAKIT (BARU) ---
            if (data.sakit_summary && data.sakit_summary.total_all > 0) {
                html += `
                    <div class="mb-3">
                        <h6 class="border-bottom pb-2 mb-2 text-danger">
                            <i class="bi bi-hospital me-1"></i> Sakit
                            <span class="badge bg-danger float-end">${data.sakit_summary.total_all} hari</span>
                        </h6>
                        <ul class="list-group list-group-flush small">
                `;
                $.each(data.sakit_summary.details, function(name, count) {
                    html += `
                        <li class="list-group-item d-flex justify-content-between px-0 py-1">
                            <span class="text-truncate" style="max-width: 180px;">${name}</span>
                            <span class="fw-bold">${count} hari</span>
                        </li>
                    `;
                });
                html += `</ul></div>`;
            }

            $('#summaryContent').html(html);
        }

        function resetActivityModal() {

            // Reset section visibility
            $('#rkmDetailSection').addClass('d-none');
            $('#activityFormDetail').removeClass('d-none');
            $('#proofUploadSection').addClass('d-none');

            // Reset text RKM
            $('#rkmMateri').text('');
            $('#rkmTanggal').text('');
            $('#rkmKelas').text('');

            // Reset form value
            $('#activityFormDetail')[0].reset();
            $('#formActivityType').val('pilih');

            // Reset buttons
            $('#saveActivityDetailBtn').addClass('d-none').prop('disabled', false);
            $('#uploadProofBtn').prop('disabled', false).text('Selesaikan dan Unggah Bukti');
            $('#btnViewDoc').addClass('d-none').attr('href', '#');
            // Reset alert
            $('#lockAlert')
                .addClass('d-none')
                .removeClass('alert-danger')
                .html('');

            // Enable semua input
            $('.form-control, .form-select, button').prop('disabled', false);
        }

        
        // Variable global untuk menyimpan state form
        let isEditMode = false;

        // 1. Event Listener untuk Switch Edit Mode
        $('#enableEditMode').on('change', function() {
            isEditMode = $(this).is(':checked');
            toggleFormInputs(isEditMode);
        });

        // 2. Fungsi untuk Enable/Disable Input
        function toggleFormInputs(enable) {
            // Pastikan tombol selalu muncul dulu (hapus d-none), kecuali dilock (logic lain)
            $('#saveActivityDetailBtn').removeClass('d-none');

            if (enable) {
                // --- MODE EDIT AKTIF ---
                // 1. Enable semua input field
                $('.editable-field').prop('disabled', false).removeClass('bg-light');
                
                // 2. Ubah Teks Tombol & Enable Tombol
                $('#saveActivityDetailBtn')
                    .text('Simpan Perubahan')
                    .prop('disabled', false); // Pastikan tombol bisa diklik
                    
            } else {
                // --- MODE VIEW (MATI) ---
                // 1. Disable input field & beri warna abu
                $('.editable-field').prop('disabled', true).addClass('bg-light');
                
                // 2. Ubah Teks Tombol (Untuk sekadar update bukti)
                $('#saveActivityDetailBtn')
                    .text('Update Bukti')
                    .prop('disabled', false); // Tetap enable agar bisa simpan Bukti/Link
            }
        }

        // 3. Update Fungsi showActivityModal (Integrasi Logic)
        function showActivityModal(tipe, date, activityId, activityData, title) {
            resetActivityModal();
            // ... (Logika reset section RKM dll tetap sama) ...

            $('#rkmDetailSection').addClass('d-none');
            $('#activityFormDetail').removeClass('d-none');
            
            // Set Value
            $('#modalDate').text(moment(date).format('DD MMMM YYYY'));
            $('#formActivityDate').val(date);
            $('#formActivityId').val(activityId);

            // Cek Locking
            var isLocked = false; 
            var dateCarbon = moment(date);
            const startOfThisWeek = moment().startOf('isoWeek');
            if (dateCarbon.isBefore(startOfThisWeek)) {
                isLocked = false;
            }

            // -- LOGIKA UTAMA --
            if (activityData) { // DATA SUDAH ADA (Edit / View)
                
                // Isi Form
                $('#formActivity').val(title);
                $('#formDesc').val(activityData.desc);
                $('#formActivityType').val(activityData.activity_type || 'pilih');
                $('#formDoc').val(activityData.doc);

                // Setup Edit Mode Switch
                $('#editModeContainer').removeClass('d-none'); // Tampilkan switch
                $('#enableEditMode').prop('checked', false);   // Default off
                toggleFormInputs(false);                       // Default disabled
                
                // Setup Form Action ke UPDATE
                $('#activityFormDetail').attr('action', API_PROOF_UPDATE_URL);

                // Setup Bagian Bukti
                $('#proofUploadSection').removeClass('d-none');
                
                if(activityData.doc) {
                    $('#btnViewDoc').removeClass('d-none').attr('href', activityData.doc);
                }

            } else { // DATA BARU (Create)
                
                // Setup Form Action ke STORE
                $('#activityFormDetail').attr('action', API_STORE_URL);
                
                $('#editModeContainer').addClass('d-none'); // Sembunyikan switch edit mode
                toggleFormInputs(true); // Input harus aktif untuk data baru
                $('#proofUploadSection').addClass('d-none'); // Sembunyikan upload bukti dulu
                $('#saveActivityDetailBtn').text('Simpan Aktivitas Baru');
            }

            // Jika Terkunci (Locked)
            if (isLocked) {
                $('#lockAlert').removeClass('d-none').html('Mohon maaf laporan minggu yang dipilih terkunci.');
                $('#editModeContainer').addClass('d-none'); // Tidak bisa edit
                $('.form-control, .form-select, button[type="submit"]').prop('disabled', true);
            }

            $('#activityModal').modal('show');
        }
    });
</script>
@endsection