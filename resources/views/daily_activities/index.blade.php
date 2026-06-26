@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-12">

            <div class="d-flex justify-content-end mb-4">
                <button class="btn btn-md btn-primary mx-4" data-bs-toggle="modal" data-bs-target="#activityModal" data-toggle="tooltip" title="Tambah Aktivitas Baru">
                    <i class="fas fa-plus me-1"></i> Tambah Aktivitas Baru
                </button>
            </div>

            <div class="row mb-4 mx-2">
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

            <div class="card m-4 shadow-sm">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-3">{{ __('Data Daftar Tugas & Aktivitas') }}</h3>
                    <table id="activityTable" class="table table-striped text-nowrap" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>Timestamp</th>
                                <th>Aktivitas</th>
                                <th>Task Terkait</th>
                                <th>PIC</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Selesai</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activities as $activity)
                            <tr data-id="{{ $activity->id }}">
                                <td>{{ $activity->created_at }}</td>
                                <td>{{ $activity->activity }}</td>
                                <td>{{ $activity->task->title ?? '-' }}</td>
                                <td>{{ $activity->user->karyawan->kode_karyawan ?? '-' }}</td>
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
                                    @php
                                        $user = auth()->user();
                                        $picTask = $activity->user->karyawan->kode_karyawan ?? null;
                                        $kodeKaryawan = $user->karyawan->kode_karyawan ?? null;
                                        $isPicMatch = $picTask === $kodeKaryawan;
                                    @endphp

                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">Aksi</button>
                                        <ul class="dropdown-menu shadow">
                                            <li>
                                                <button type="button" class="dropdown-item btn-detail-activity" data-id="{{ $activity->id }}">
                                                    <i class="fas fa-eye me-2"></i> Detail
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            @if($activity->status != 'Selesai')
                                                <li>
                                                    <button type="button"
                                                        class="dropdown-item text-success btn-update-status"
                                                        data-id="{{ $activity->id }}"
                                                        data-activity="{{ $activity->activity }}"
                                                        {{ !$isPicMatch ? 'disabled' : '' }}
                                                        title="{{ !$isPicMatch ? 'Hanya PIC task yang dapat menyelesaikan aktivitas ini' : '' }}">
                                                        <i class="fas fa-check-circle me-2"></i> Selesaikan
                                                    </button>
                                                </li>
                                            @else
                                                <li>
                                                    <span class="dropdown-item text-muted">
                                                        <i class="fas fa-check-double me-2"></i> Selesai
                                                    </span>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="detailActivityModalLabel">
                        <i class="fas fa-clipboard-list me-2"></i> Detail Aktivitas
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
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
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup Detail</button>
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
        // Konfigurasi Inisialisasi DataTables Tanpa Parameter ScrollX
        // Mengingat class table-responsive Bootstrap 5 menangani luapan DOM secara native
        $('#activityTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            },
            "order": [[0, 'desc']],
            "columnDefs": [{"targets": [0], "type": "date"}]
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

        // Logika Pemanggilan Modal Update Status Melalui Dropdown
        $(document).on('click', '.btn-update-status', function() {
            let activityId = $(this).data('id');
            let activityName = $(this).data('activity');

            let actionUrl = `/daily-activities/${activityId}/quick-update`;
            $('#updateStatusForm').attr('action', actionUrl);
            $('#displayActivityName').val(activityName);
            $('#updateStatusModal').modal('show');
        });

        // Logika Klik Tombol Detail pada Dropdown untuk Menampilkan Modal
        $('#activityTable tbody').on('click', '.btn-detail-activity', function() {
            let activityId = $(this).data('id');

            if (!activityId) return;

            $.ajax({
                url: `/daily-activities/${activityId}`,
                method: 'GET',
                success: function(response) {
                    $('#detailTask').text(response.task ? response.task.title : '-');
                    $('#detailActivityName').text(response.activity);
                    $('#detailDescription').text(response.description ? response.description : '-');

                    let startDate = new Date(response.start_date).toLocaleDateString('id-ID', {day: '2-digit', month: '2-digit', year: 'numeric'});
                    let endDate = response.end_date ? new Date(response.end_date).toLocaleDateString('id-ID', {day: '2-digit', month: '2-digit', year: 'numeric'}) : '-';

                    $('#detailStartDate').text(startDate);
                    $('#detailEndDate').text(endDate);

                    let statusHtml = response.status === 'Selesai'
                        ? '<span class="badge bg-success">Selesai</span>'
                        : '<span class="badge bg-warning text-dark">On Progres</span>';
                    $('#detailStatus').html(statusHtml);

                    if (response.doc) {
                        let fileUrl = `{{ asset('storage') }}/${response.doc}`;
                        $('#detailDoc').html(`<a href="${fileUrl}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-file-alt me-1"></i> Lihat Dokumen</a>`);
                    } else {
                        $('#detailDoc').html('<span class="text-muted">Tidak ada dokumen</span>');
                    }

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
