@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <i class="fas fa-clipboard-list me-2 text-primary"></i> Aktivitas Harian {{ $divisionName ?? 'N/A' }}
        </h4>
    </div>

    {{-- Tabel Aktivitas dengan style Bootstrap --}}
    <div class="card shadow-sm">
        <div class="card-body">
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
                        
                        <input type="hidden" name="activity_date" id="formActivityDate">
                        <input type="hidden" name="activity_id" id="formActivityId">
                        <input type="hidden" value="manual" name="activity_subtype" id="activity_subtype">

                        {{--  --}}
                        <div class="mb-3">
                            <label for="formActivityType" class="form-label fw-bold">
                                Pilih Task Terkait <span class="text-danger">*</span>
                            </label>
                            <select name="id_task" id="formActivityType"
                                class="form-select @error('formActivityType') is-invalid @enderror">
                                @foreach ($tasks as $task)
                                    <option value="{{ $task->id }}" {{ old('id_task') == $task->id ? 'selected' : '' }}>
                                        {{ $task->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('formActivityType')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="formActivity" class="form-label fw-bold">
                                Aktivitas yang Dilakukan <span class="text-danger">*</span>
                            </label>
                            <textarea name="activity" id="formActivity" rows="3"
                                class="form-control @error('formActivity') is-invalid @enderror"
                                placeholder="Jelaskan secara singkat apa yang Anda kerjakan..." required>{{ old('activity') }}</textarea>
                            @error('formActivity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="formDesc" class="form-label fw-bold">
                                Deskripsi Tambahan (Opsional)
                            </label>
                            <textarea name="description" id="formDesc" rows="4"
                                class="form-control @error('formDesc') is-invalid @enderror"
                                placeholder="Berikan detail lebih lanjut jika diperlukan...">{{ old('description') }}</textarea>
                            @error('formDesc')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3" id="uploadDocs">
                            <label for="doc" class="form-label fw-bold">
                                Unggah Dokumen (Opsional)
                            </label>
                            <input type="file" name="doc" id="doc"
                                class="form-control @error('doc') is-invalid @enderror">
                            <div class="form-text">
                                Maksimal 2MB. Format: pdf, doc(x), xls(x), jpg, png.
                            </div>
                            @error('doc')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3" id="existDocs">
                            <label class="form-label fw-bold">Dokumen</label>
                            <div class="modal-activity-doc">-</div>
                        </div>


                        <div class="mb-3">
                            <label for="start_date" class="form-label fw-bold">
                                Tanggal Mulai Aktivitas <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="start_date" id="start_date"
                                value="{{ old('start_date', now()->format('Y-m-d')) }}"
                                class="form-control @error('start_date') is-invalid @enderror"
                                required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3" id="tanggalSelesai">
                            <label for="end_date" class="form-label fw-bold">
                                Tanggal Selesai Aktivitas
                            </label>
                            <input type="date" name="end_date" id="end_date"
                                value="{{ old('end_date', now()->format('Y-m-d')) }}"
                                class="form-control @error('end_date') is-invalid @enderror">
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 d-none" id="statusAction">
                            <label class="form-label fw-bold">Aksi Status</label>
                            <div class="d-flex gap-2" id="statusButtons"></div>
                        </div>
                        {{--  --}}

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="z-index: 200;">Tutup</button>
                        <button type="submit" class="btn btn-primary" id="saveActivityDetailBtn" style="z-index: 200;">Simpan Detail</button>
                    </div>
                </form>
                <div id="deleteButton" class="mb-4 ms-4" style="margin-top: -11%; z-index: 100">
                    <form id="deleteActivityForm" method="POST" class="me-auto" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aktivitas ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn">
                            <i class="fas fa-trash-alt me-1"></i> Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" />
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script>
    $(document).ready(function() {
        var calendarEl = document.getElementById("calendar");

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek'
            },
            locale: 'id', // Opsional: Tampilkan kalender dalam bahasa Indonesia
            firstDay: 1, // Minggu dimulai hari Senin
            height: 'auto',
            // Load data dari backend
            events: function(fetchInfo, successCallback, failureCallback) {
                $.ajax({
                    url: "/daily-activities-data",
                    method: 'GET',
                    data: {
                        start: fetchInfo.startStr,
                        end: fetchInfo.endStr
                    },
                    success: function(data) {
                        successCallback(data);
                        console.log("nih data : ", data);
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
                console.log("p : ", info);
                showActivityModal(
                    'show',
                    info.event.startStr, 
                    info.event.id, 
                    info.event.extendedProps, 
                    info.event.title);
            }
        });
       
        calendar.render();

        function resetActivityModal() {

            // reset form
            $('#activityFormDetail')[0].reset();
            $('#formActivityType').val('');

            $('#tanggalSelesai').hide();
            $('#statusAction').addClass('d-none');
            $('#statusButtons').empty();

            $('#deleteButton').hide();
            
            $('#existDocs').hide();
            $('.modal-activity-doc').html('-');

            $('#uploadDocs').show();

            $('.form-control, .form-select, button').prop('disabled', false);
            $('#activityFormDetail input[name="_method"]').remove();
        }


        function showActivityModal(tipe, date, activityId, activityData, title) 
        {
            resetActivityModal();

            $('#modalDate').text(moment(date).format('DD MMMM YYYY'));
            $('#formActivityId').val(activityId ?? '');

            if (tipe === 'store') {
                // Create Form
                $('#tanggalSelesai').hide();

                $('#activityFormDetail').attr('action', "{{ route('daily-activities.store') }}").find('input[name="_method"]').remove();

                $('#start_date').val(date);
                $('#end_date').val('');

                $('#deleteButton').hide();
                $('#deleteActivityForm').attr('action', '');
            } else if (tipe === 'show' && activityData) {
                // Update Form
                $('#tanggalSelesai').show();

                $('#activityFormDetail')
                    .attr('action', `/daily-activities/${activityId}`);

                if ($('#activityFormDetail input[name="_method"]').length === 0) {
                    $('#activityFormDetail').append(
                        '<input type="hidden" name="_method" value="PUT">'
                    );
                }

                // hapus
                $('#deleteButton').show();
                let deleteUrl = "{{ route('daily-activities.destroy', ':id') }}";
                deleteUrl = deleteUrl.replace(':id', activityId);
                $('#deleteActivityForm').attr('action', deleteUrl);


                $('#formActivityType').val(activityData.id_task);
                $('#formActivity').val(activityData.activity);
                $('#formDesc').val(activityData.description);
                $('#start_date').val(activityData.start_date); 
                $('#end_date').val(activityData.end_date);
                renderStatusActions(activityId, activityData.status);

                // Status aktivitas
                if (activityData.status === 'Selesai') {
                    $('#statusAction').hide();
                } else {
                    $('#statusAction').show();
                }

                // Dokumen
                const docLinkElement = document.querySelector('.modal-activity-doc');
                if (activityData.doc) {

                    const fileUrl = `{{ asset('storage') }}/${activityData.doc}`;

                    $('#existDocs').show();
                    docLinkElement.innerHTML = `
                        <a href="${fileUrl}" target="_blank" class="text-primary">
                            <i class="fas fa-file-alt me-1"></i> Lihat Dokumen
                        </a>
                    `;
                } else {
                    $('#existDocs').hide();
                    docLinkElement.textContent = '-';
                }
            }

            $('#activityModal').modal('show');
        }

        function renderStatusActions(activityId, status) {
            let container = $('#statusButtons');
            container.empty();

            if (status !== 'Selesai') {
                container.append(`
                    <form method="POST" action="/daily-activities/${activityId}/update-status">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="_method" value="PATCH">
                        <input type="hidden" name="status" value="Selesai">
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="fas fa-check-circle me-1"></i> Tandai Selesai
                        </button>
                    </form>
                `);
            }

            if (status !== 'On Progres Dilanjutkan Besok' && status !== 'Selesai') {
                container.append(`
                    <form method="POST" action="/daily-activities/${activityId}/update-status">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="_method" value="PATCH">
                        <input type="hidden" name="status" value="On Progres Dilanjutkan Besok">
                        <button type="submit" class="btn btn-warning btn-sm">
                            <i class="fas fa-history me-1"></i> Lanjutkan Besok
                        </button>
                    </form>
                `);
            }

            $('#statusAction').removeClass('d-none');
        }
    });
</script>
@endsection
