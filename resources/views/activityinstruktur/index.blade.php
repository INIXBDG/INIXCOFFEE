@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card" style="width: 100%">
        <div class="card-body d-block justify-content-center">
            <h3 class="mb-4">Laporan Aktivitas Instruktur</h3>
            <div id="calendar"></div>
        </div>
    </div>
    <div class="modal fade" id="activityModal" tabindex="-1" aria-labelledby="activityModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="activityFormDetail" method="POST" enctype="multipart/form-data">
                    @csrf 
                    <div class="modal-header">
                        <h5 class="modal-title" id="activityModalLabel">Aktivitas <span id="modalDate"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="lockAlert" class="alert alert-danger d-none"></div>
                        
                        <input type="hidden" name="activity_date" id="formActivityDate">
                        <input type="hidden" name="activity_id" id="formActivityId">
                        <input type="hidden" value="manual" name="activity_subtype" id="activity_subtype">

                        <div class="mb-3">
                            <label for="formActivityType" class="form-label">Tipe Aktivitas</label>
                            <select class="form-select" id="formActivityType" name="activity_type">
                                <option value="pilih" selected>Pilih Aktivitas</option>
                                <option value="Mengajar">Mengajar</option>
                                <option value="Sharing Knowledge">Sharing Knowledge</option>
                                <option value="Webinar">Webinar</option>
                                <option value="Projek">Projek</option>
                                <option value="Buat Materi">Buat Materi</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="formActivity" class="form-label">Judul Aktivitas</label>
                            <input type="text" class="form-control" id="formActivity" name="activity" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="formDesc" class="form-label">Deskripsi / Detail</label>
                            <textarea class="form-control" id="formDesc" name="desc" rows="3"></textarea>
                        </div>

                        <div id="proofUploadSection" class="mt-4 border p-3 rounded d-none">
                            <h6>Bukti Penyelesaian Aktivitas</h6>
                            <div class="mb-3">
                                <label for="formDoc" class="form-label">Link Dokumen Bukti</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="formDoc" name="doc" placeholder="https://...">
                                    <a href="#" id="btnViewDoc" target="_blank" class="btn btn-outline-primary d-none">
                                        <i class="bi bi-box-arrow-up-right"></i> Buka Link
                                    </a>
                                </div>
                                <small id="docInfo" class="form-text text-muted"></small>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary" id="saveActivityDetailBtn">Simpan Detail</button>
                    </div>
                </form>
                <div id="rkmDetailSection" class="rounded p-3 mb-3 d-none">
                    <div class="modal-header">
                        <h5 class="modal-title" id="activityModalLabel">Detail Mengajar (RKM)</h5></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <strong>Materi:</strong> <span id="rkmMateri"></span>
                        </div>
                        <div class="mb-3">
                            <strong>Tanggal:</strong> <span id="rkmTanggal"></span>
                        </div>
                        <div class="mb-3">
                            <strong>Kelas:</strong> <span id="rkmKelas"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
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

        
        function showActivityModal(tipe,date,activityId,activityData,title) 
        {
            resetActivityModal();
            $('#rkmDetailSection').addClass('d-none');
            $('#activityFormDetail').removeClass('d-none');

            $('#rkmMateri').text('');
            $('#rkmTanggal').text('');
            $('#rkmKelas').text('');

            $('#formActivity').val('');
            $('#formDesc').val('');
            $('#formActivityType').val('pilih');
            
            
            console.log(title);
            // --- Langkah 2: Set Data Awal & Variabel Kunci ---
            
            $('#modalDate').text(moment(date).format('DD MMMM YYYY'));
            $('#formActivityDate').val(date);
            $('#formActivityId').val(activityId);
            console.log(activityData);
            var isTeachingActivity = activityData && activityData.type === 'rkm';
            var activity_type = activityData && activityData.activity_type;
            var isExistingActivity = activityId !== null;
            var isCompleted = activityData && activityData.status === 'Selesai';
            var isLocked = false; 

            // Muat Data jika event diklik
            if (activityData) {
                $('#formActivity').val(title);
                $('#formDesc').val(activityData.desc);
                $('#formActivityType').val(activity_type);
                $('#formActivity').prop('disabled', true);
                // $('#formDesc').prop('disabled', true);
                $('#formActivityType').prop('disabled', true);
                
                // Ambil status lock dari data event
                if (activityData.is_locked) {
                    isLocked = true;
                }
            } else {
                // Jika dateClick (aktivitas baru), pastikan formActivityType adalah Manual
                $('#formActivityType').val('pilih');
            }
            // ===============================
            // DOKUMEN TERSIMPAN
            // ===============================
            $('#docInfo').addClass('d-none').html('');
            $('#btnViewDoc').addClass('d-none'); // Sembunyikan tombol secara default

            if (activityData && activityData.doc) {
                // Set value ke input
                $('#formDoc').val(activityData.doc);

                // Jika isinya diawali http (Link), jadikan button "Open New Tab"
                if (activityData.doc.startsWith('http')) {
                    $('#btnViewDoc')
                        .removeClass('d-none')
                        .attr('href', activityData.doc);
                } 
                // Jika isinya path file storage (asumsi kode lama Anda)
                else {
                    const fileUrl = `/storage/${activityData.doc}`;
                    const fileName = activityData.doc.split('/').pop();

                    $('#btnViewDoc')
                        .removeClass('d-none')
                        .attr('href', fileUrl);
                        
                    $('#docInfo')
                        .removeClass('d-none')
                        .html(`📎 File: ${fileName}`);
                }
            }

            
            // Cek Status Locked Berdasarkan Tanggal (Frontend Check)
            var dateCarbon = moment(date);
            const startOfThisWeek = moment().startOf('isoWeek'); // Senin minggu ini

            if (dateCarbon.isBefore(startOfThisWeek)) {
                isLocked = true; // Minggu lalu & sebelumnya
            }

            console.log({
                klik: dateCarbon.format('YYYY-MM-DD'),
                startOfThisWeek: startOfThisWeek.format('YYYY-MM-DD'),
                locked: isLocked
            });
            // --- Langkah 3: Terapkan Logika Tiga Skenario (UI/UX) ---

            if (isLocked) {
                // ===============================
                // SKENARIO 1: LOCKED
                // ===============================

                // Tampilkan alert lock
                $('#lockAlert')
                    .removeClass('d-none')
                    .addClass('alert-danger')
                    .html('🚨 Laporan minggu ini sudah <strong>dikunci</strong> dan tidak dapat diubah.');

                // Jika aktivitas mengajar (RKM)
                if (isTeachingActivity) {

                    // Tampilkan detail RKM
                    $('#rkmDetailSection').removeClass('d-none');
                    $('#activityFormDetail').addClass('d-none');

                    $('#rkmMateri').text(activityData.materi);
                    $('#rkmTanggal').text(
                        moment(activityData.tanggal_awal).format('DD MMM YYYY') +
                        ' - ' +
                        moment(activityData.tanggal_akhir).format('DD MMM YYYY')
                    );
                    $('#rkmKelas').text(activityData.metode_kelas ?? '-');

                }

                // Lock semua input (1x saja)
                $('.form-control, .form-select').prop('disabled', true);

                // Pastikan tidak ada tombol simpan
                $('#saveActivityDetailBtn').addClass('d-none');
            } else if (isExistingActivity) {
                
                // --- Aktivitas SUDAH ADA (Update/Edit Mode) ---
                
                 if (isTeachingActivity) {

                    $('#rkmDetailSection').removeClass('d-none');
                    $('#activityFormDetail').addClass('d-none');

                    $('#rkmMateri').text(activityData.materi);
                    $('#rkmTanggal').text(
                        moment(activityData.tanggal_awal).format('DD MMM YYYY') +
                        ' - ' +
                        moment(activityData.tanggal_akhir).format('DD MMM YYYY')
                    );
                    $('#rkmKelas').text(activityData.metode_kelas);

                    // Semua form jadi readonly
                    $('.form-control, .form-select').prop('disabled', true);
                    $('#saveActivityDetailBtn').addClass('d-none');
                } else {
                    // **SKENARIO 3: MANUAL + DATA ADA**
                    
                    // BISA Update Detail (Tombol Simpan Detail muncul)
                    $('#saveActivityDetailBtn').removeClass('d-none').text('Update Detail Aktivitas');
                    $('#activityFormDetail').attr('action', API_PROOF_UPDATE_URL);
                    $('#activityFormDetail').attr('method', 'POST');
                    // BISA Update Bukti/Selesai (Bagian upload muncul)
                    $('#proofUploadSection').removeClass('d-none');
                    
                    if (isCompleted) {
                        $('#uploadProofBtn').text('Bukti Sudah Diunggah').prop('disabled', true);
                    }
                }
                
            } else { 
                // **SKENARIO 4: DATA BELUM ADA (DateClick)**
                $('#activityFormDetail').attr('action', API_STORE_URL);
                $('#activityFormDetail').attr('method', 'POST');
                // HANYA BISA Store Baru (Aktivitas Manual)
                $('#saveActivityDetailBtn').removeClass('d-none').text('Simpan Aktivitas Baru');
                
                // Bagian upload bukti disembunyikan
                $('#proofUploadSection').addClass('d-none');
            }
            
            // 4. Tampilkan Modal
            $('#activityModal').modal('show');
        }
    });
</script>
@endsection