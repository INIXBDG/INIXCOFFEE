@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="mb-4">📋 Laporan Aktivitas Instruktur</h3>
    <div id="calendar"></div>
    
    <div class="modal fade" id="activityModal" tabindex="-1" aria-labelledby="activityModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="activityForm">
                    @csrf 
                    <div class="modal-header">
                        <h5 class="modal-title" id="activityModalLabel">Aktivitas Tanggal: <span id="modalDate"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="lockAlert" class="alert alert-danger d-none"></div>
                        
                        <input type="hidden" name="activity_date" id="formActivityDate">
                        <input type="hidden" name="activity_id" id="formActivityId">

                        <div class="mb-3">
                            <label for="formActivityType" class="form-label">Tipe Aktivitas</label>
                            <select class="form-select" id="formActivityType" name="activity_type">
                                <option value="manual">Manual Input</option>
                                <option value="teaching" disabled>Mengajar (Otomatis)</option>
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
                        
                        <div class="mb-3">
                            <label for="formDoc" class="form-label">Dokumen Pendukung</label>
                            <input type="file" class="form-control" id="formDoc" name="doc" disabled>
                            <small id="docInfo" class="form-text text-muted"></small>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary" id="saveActivityBtn">Simpan Aktivitas</button>
                    </div>
                </form>
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

    $(document).ready(function() {
        var calendarEl = document.getElementById('calendar');

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

            // Ketika mengklik tanggal kosong
            dateClick: function(info) {
                showActivityModal(info.dateStr);
            },
            
            // Ketika mengklik event yang sudah ada
            eventClick: function(info) {
                // Gunakan id dan extendedProps untuk memuat data ke modal
                showActivityModal(info.event.startStr, info.event.id, info.event.extendedProps);
            }
        });

        calendar.render();

        // --- Logika Modal dan AJAX ---
        
        function showActivityModal(date, activityId = null, activityData = null) {
            // Reset form
            $('#activityForm')[0].reset();
            $('#lockAlert').addClass('d-none').removeClass('alert-danger alert-success').html('');
            $('#saveActivityBtn').prop('disabled', false);
            $('.form-control, .form-select').prop('disabled', false);
            
            // Set tanggal di modal
            $('#modalDate').text(moment(date).format('DD MMMM YYYY'));
            $('#formActivityDate').val(date);
            $('#formActivityId').val(activityId);

            var isTeachingActivity = activityData && activityData.id_rkm;

            // Mengatur status field
            if (isTeachingActivity) {
                $('#formActivity').prop('readonly', true);
                $('#formActivityType').val('teaching');
                // ... mungkin tampilkan label "Dibuat Otomatis"
            } else {
                $('#formActivity').prop('readonly', false);
                $('#formActivityType').val('manual');
            }

            // 1. Tentukan Status Locked Berdasarkan Tanggal
            // Keterangan: Logic ini SANGAT SENSITIF, sebaiknya server yang memutuskan.
            // Namun, untuk validasi awal di frontend:
            var dateCarbon = moment(date);
            // Cek apakah tanggal yang diklik sudah lebih dari 7 hari yang lalu
            var lockThreshold = moment().subtract(7, 'days').endOf('isoWeek'); // Akhir minggu lalu
            var isLocked = dateCarbon.isBefore(lockThreshold); 
            
            
            // 2. Muat Data Jika Activity Sudah Ada
            if (activityData) {
                $('#formActivity').val(activityData.title);
                $('#formDesc').val(activityData.desc);
                // Cek apakah ini aktivitas RKM (Otomatis)
                if (activityData.id_rkm) {
                    $('#formActivityType').val('teaching');
                } else {
                    $('#formActivityType').val('manual');
                }
                
                // Ambil status lock dari data event
                if (activityData.is_locked) {
                    isLocked = true;
                }
            }


            // 3. Terapkan Status Locking (Frontend)
            if (isLocked) {
                $('#lockAlert')
                    .removeClass('d-none')
                    .addClass('alert-danger')
                    .html('🚨 Minggu ini sudah dikunci! Laporan tidak dapat diubah atau ditambahkan lagi.');
                $('#saveActivityBtn').prop('disabled', true);
                // Disabled semua form control
                $('.form-control, .form-select').prop('disabled', true);
            }
            
            // 4. Tampilkan Modal
            $('#activityModal').modal('show');
        }
        
        // --- Handler Submit Form ---
        $('#activityForm').on('submit', function(e) {
            e.preventDefault();
            
            // Serialize form data
            var formData = $(this).serialize();
            
            // Tampilkan loading/disabled tombol
            $('#saveActivityBtn').text('Menyimpan...').prop('disabled', true);
            
            $.ajax({
                url: API_STORE_URL,
                method: 'POST',
                data: formData,
                success: function(response) {
                    $('#activityModal').modal('hide');
                    alert(response.message);
                    calendar.refetchEvents(); // Refresh data kalender
                },
                error: function(xhr) {
                    var errorMsg = "Terjadi kesalahan saat menyimpan laporan.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    alert('Gagal: ' + errorMsg);
                    
                    $('#saveActivityBtn').text('Simpan Aktivitas').prop('disabled', false);
                }
            });
        });
    });
</script>
@endsection