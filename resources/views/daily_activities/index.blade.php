@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <i class="fas fa-list-check me-2 text-primary"></i> Daftar Tugas & Aktivitas
        </h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#activityModal">
            <i class="fas fa-plus me-1"></i> Tambah Aktivitas
        </button>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold text-muted text-center mb-3">Status Tugas</h6>
                    <div class="d-flex justify-content-around text-center">
                        <div>
                            <h3 class="mb-0 text-warning">{{ $inProgressActivities }}</h3>
                            <small class="text-muted">On Progress</small>
                        </div>
                        <div>
                            <h3 class="mb-0 text-success">{{ $doneActivities }}</h3>
                            <small class="text-muted">Selesai</small>
                        </div>
                        <div>
                            <h3 class="mb-0 text-primary">{{ $totalActivities }}</h3>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="fw-bold text-muted mb-3">Progres Keseluruhan</h6>
                    <div class="position-relative d-inline-block">
                        <canvas id="progressChart" width="100" height="100"></canvas>
                        <div class="position-absolute top-50 start-50 translate-middle">
                            <h4 class="mb-0">{{ $progressPercentage }}%</h4>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">{{ $doneActivities }}/{{ $totalActivities }} Selesai</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold text-muted text-center mb-3">Ringkasan Tenggat Waktu</h6>
                    <div class="d-flex justify-content-around align-items-center text-center h-75">
                        <div>
                            <h3 class="mb-0 text-danger">{{ $overdueActivities }}</h3>
                            <small class="text-muted">Terlambat</small>
                        </div>
                        <div class="vr"></div>
                        <div>
                            <h3 class="mb-0 text-info">{{ $dueTodayActivities }}</h3>
                            <small class="text-muted">Jatuh Tempo Hari Ini</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="activityTable" class="table table-hover table-bordered align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th>Timestamp</th>
                            <th>Aktivitas</th>
                            <th>Task Terkait</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activities as $activity)
                        <tr data-id="{{ $activity->id }}" style="cursor: pointer;" class="activity-row-click">
                            <td>{{ $activity->created_at }}</td>
                            <td>{{ $activity->activity }}</td>
                            <td>{{ $activity->task->title ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($activity->start_date)->format('d/m/Y') }}</td>
                            <td>{{ $activity->end_date ? \Carbon\Carbon::parse($activity->end_date)->format('d/m/Y') : '-' }}</td>
                            <td>
                                @if($activity->status == 'Selesai')
                                    <span class="badge bg-success">Selesai</span>
                                @else
                                    <span class="badge bg-warning text-dark">On Progres</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($activity->status != 'Selesai')
                                <button type="button" class="btn btn-sm btn-success btn-update-status" 
                                    data-id="{{ $activity->id }}" 
                                    data-activity="{{ $activity->activity }}">
                                    <i class="fas fa-check me-1"></i> Selesaikan
                                </button>
                                @else
                                <span class="text-muted"><i class="fas fa-check-double"></i> Selesai</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="activityModal" tabindex="-1" aria-labelledby="activityModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="activityFormDetail" method="POST" action="{{ route('daily-activities.store') }}" enctype="multipart/form-data">
                    @csrf 
                    <div class="modal-header">
                        <h5 class="modal-title" id="activityModalLabel">Tambah Aktivitas Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="activityContainer">
                            <div class="activity-row border rounded p-3 mb-3 relative">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold mb-0">Detail Aktivitas</h6>
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-activity-btn d-none">
                                        <i class="fas fa-times"></i> Hapus
                                    </button>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Pilih Task Terkait <span class="text-danger">*</span></label>
                                    <select name="id_task[]" class="form-select input-id-task">
                                        <option value="">-- Pilih Task --</option>
                                        @foreach ($tasks as $task)
                                            <option value="{{ $task->id }}">{{ $task->title }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Aktivitas yang Dilakukan <span class="text-danger">*</span></label>
                                    <textarea name="activity[]" rows="3" class="form-control input-activity" placeholder="Jelaskan secara singkat aktivitas..." required></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Deskripsi Tambahan (Opsional)</label>
                                    <textarea name="description[]" rows="2" class="form-control input-description"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Unggah Dokumen (Opsional)</label>
                                    <input type="file" name="doc[]" class="form-control input-doc">
                                    <div class="form-text">Maksimal 2MB. Format: pdf, doc(x), xls(x), jpg, png.</div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Tanggal Mulai <span class="text-danger">*</span></label>
                                        <input type="date" name="start_date[]" class="form-control input-start-date" value="{{ now()->format('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Tanggal Selesai (Opsional)</label>
                                        <input type="date" name="end_date[]" class="form-control input-end-date">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="addActivityBtn" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-plus me-1"></i> Tambah Aktivitas Lain
                        </button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan Aktivitas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="updateStatusForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateStatusModalLabel">Selesaikan Aktivitas</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Aktivitas</label>
                            <input type="text" id="displayActivityName" class="form-control" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Unggah Dokumen Bukti (Opsional)</label>
                            <input type="file" name="doc" class="form-control">
                            <div class="form-text">Maksimal 2MB. Format: pdf, doc(x), xls(x), jpg, png.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Selesaikan & Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detailActivityModal" tabindex="-1" aria-labelledby="detailActivityModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailActivityModalLabel">Detail Aktivitas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tbody>
                            <tr>
                                <th style="width: 35%;">Task Terkait</th>
                                <td style="width: 5%;">:</td>
                                <td id="detailTask"></td>
                            </tr>
                            <tr>
                                <th>Aktivitas</th>
                                <td>:</td>
                                <td id="detailActivityName"></td>
                            </tr>
                            <tr>
                                <th>Deskripsi</th>
                                <td>:</td>
                                <td id="detailDescription"></td>
                            </tr>
                            <tr>
                                <th>Tanggal Mulai</th>
                                <td>:</td>
                                <td id="detailStartDate"></td>
                            </tr>
                            <tr>
                                <th>Tanggal Selesai</th>
                                <td>:</td>
                                <td id="detailEndDate"></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>:</td>
                                <td id="detailStatus"></td>
                            </tr>
                            <tr>
                                <th>Dokumen</th>
                                <td>:</td>
                                <td id="detailDoc"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    </div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    $(document).ready(function() {
        // Inisialisasi DataTables
        $('#activityTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            },
            "order": [[0, 'desc']], // Ubah urutan menjadi descending untuk kolom ke-6
            "columnDefs" : [{"targets":[0], "type":"date"}],
        });

        // Inisialisasi Chart.js untuk Donut Chart
        var ctx = document.getElementById('progressChart').getContext('2d');
        var progressPercentage = {{ $progressPercentage }};
        var remainingPercentage = 100 - progressPercentage;

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Selesai', 'Belum Selesai'],
                datasets: [{
                    data: [progressPercentage, remainingPercentage],
                    backgroundColor: ['#28a745', '#e9ecef'],
                    borderWidth: 0,
                    cutout: '80%'
                }]
            },
            options: {
                responsive: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                }
            }
        });
        // Logika Duplikasi Form Tambah Aktivitas
        $('#addActivityBtn').on('click', function() {
            let firstRow = $('.activity-row').first();
            let newRow = firstRow.clone();

            newRow.find('input[type="text"], input[type="file"], textarea, select').val('');
            newRow.find('input[type="date"]').val('');
            newRow.find('.input-start-date').val(new Date().toISOString().split('T')[0]);
            newRow.find('.remove-activity-btn').removeClass('d-none');

            $('#activityContainer').append(newRow);
        });

        // Logika Hapus Form Aktivitas
        $(document).on('click', '.remove-activity-btn', function() {
            if ($('.activity-row').length > 1) {
                $(this).closest('.activity-row').remove();
            }
        });

        // Logika Pemanggilan Modal Update Status
        $(document).on('click', '.btn-update-status', function() {
            let activityId = $(this).data('id');
            let activityName = $(this).data('activity');
            
            // Setel rute action form
            let actionUrl = `/daily-activities/${activityId}/quick-update`;
            $('#updateStatusForm').attr('action', actionUrl);
            
            // Setel nama aktivitas pada input readonly
            $('#displayActivityName').val(activityName);
            
            // Tampilkan modal
            $('#updateStatusModal').modal('show');
        });

        // Logika Klik Baris Tabel untuk Menampilkan Detail
        $('#activityTable tbody').on('click', 'tr.activity-row-click', function(e) {
            // Abaikan interaksi jika area yang diklik adalah tombol, checkbox, atau area aksi
            if ($(e.target).closest('button, input, a, td:last-child').length > 0) {
                return;
            }

            let activityId = $(this).data('id');
            
            if (!activityId) return;

            // Lakukan pemanggilan AJAX untuk mengambil data detail aktivitas
            $.ajax({
                url: `/daily-activities/${activityId}`,
                method: 'GET',
                success: function(response) {
                    // Pengisian Data Teks
                    $('#detailTask').text(response.task ? response.task.title : '-');
                    $('#detailActivityName').text(response.activity);
                    $('#detailDescription').text(response.description ? response.description : '-');
                    
                    // Format Tanggal
                    let startDate = new Date(response.start_date).toLocaleDateString('id-ID', {day: '2-digit', month: '2-digit', year: 'numeric'});
                    let endDate = response.end_date ? new Date(response.end_date).toLocaleDateString('id-ID', {day: '2-digit', month: '2-digit', year: 'numeric'}) : '-';
                    
                    $('#detailStartDate').text(startDate);
                    $('#detailEndDate').text(endDate);
                    
                    // Format Badge Status
                    let statusHtml = response.status === 'Selesai' 
                        ? '<span class="badge bg-success">Selesai</span>' 
                        : '<span class="badge bg-warning text-dark">On Progres</span>';
                    $('#detailStatus').html(statusHtml);

                    // Format Tautan Dokumen
                    if (response.doc) {
                        let fileUrl = `{{ asset('storage') }}/${response.doc}`;
                        $('#detailDoc').html(`<a href="${fileUrl}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-file-alt me-1"></i> Lihat Dokumen</a>`);
                    } else {
                        $('#detailDoc').html('<span class="text-muted">Tidak ada dokumen</span>');
                    }

                    // Eksekusi Pemanggilan Modal
                    $('#detailActivityModal').modal('show');
                },
                error: function() {
                    alert('Terjadi kesalahan saat mengambil detail data aktivitas.');
                }
            });
        });
    });
</script>
@endsection