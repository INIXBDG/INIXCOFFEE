@extends('layouts.app')

@section('content')
{{-- Memuat Font Awesome untuk Ikon --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
{{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> --}}

{{-- ✅ CSS DataTables & Integrasi Bootstrap 5 --}}
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">

<div class="container py-4"> {{-- Container Bootstrap --}}
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <i class="fas fa-clipboard-list me-2 text-primary"></i> Daily Activities
        </h4>
        <a href="{{ route('daily-activities.create') }}" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-plus-circle me-1"></i> Tambah Aktivitas
        </a>
    </div>

    {{-- Pesan Sukses --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
             {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    {{-- Pesan Error --}}
     @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
             {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    {{-- Menampilkan error validasi jika ada --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
             Gagal memperbarui status. Periksa input Anda.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Tabel Aktivitas dengan style Bootstrap --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <table id="activitiesTable" class="table table-striped table-hover w-100">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Task</th>
                        <th>User</th>
                        <th>Aktivitas</th>
                        <th>Status</th>
                        <th>Dokumen</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Loop Data --}}
                    @forelse ($activities as $activity)
                        <tr>
                            <td class="whitespace-nowrap">
                                {{ optional($activity->activity_date)->format('d M Y') ?? '-' }}
                            </td>
                            <td>
                                {{ $activity->task->title ?? 'N/A' }}
                            </td>
                            <td>
                                 {{ optional(optional($activity->user)->karyawan)->nama_lengkap ?? (optional($activity->user)->name ?? 'N/A') }}
                            </td>
                            <td>
                                <span data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $activity->activity }}">
                                    {{ \Illuminate\Support\Str::limit($activity->activity, 50) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @php
                                    $statusClass = '';
                                    switch ($activity->status) {
                                        case 'Selesai': $statusClass = 'bg-success'; break;
                                        case 'Gagal': $statusClass = 'bg-danger'; break;
                                        case 'On Progres Dilanjutkan Besok': $statusClass = 'bg-warning text-dark'; break;
                                        default: $statusClass = 'bg-primary'; break;
                                    }
                                @endphp
                                <span class="badge rounded-pill {{ $statusClass }}">
                                    {{ $activity->status }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if ($activity->doc)
                                    <a href="{{ Storage::url($activity->doc) }}" target="_blank" class="text-primary">
                                        <i class="fas fa-file-alt"></i>
                                    </a>
                                @else - @endif
                            </td>
                            <td class="text-center">
                                 {{-- Dropdown Aksi Bootstrap --}}
                                 <div class="dropdown">
                                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="dropdownMenuButton-{{ $activity->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-{{ $activity->id }}">
                                        {{-- Item Lihat Detail --}}
                                        <li>
                                            <button class="dropdown-item show-detail-btn" type="button" data-id="{{ $activity->id }}">
                                                <i class="fas fa-eye me-2"></i>Lihat Detail
                                            </button>
                                        </li>
                                        {{-- Item Edit --}}
                                        <li>
                                            <a class="dropdown-item" href="{{ route('daily-activities.edit', $activity->id) }}">
                                                <i class="fas fa-pencil-alt me-2"></i>Edit
                                            </a>
                                        </li>

                                        @if ($activity->status != 'Selesai')
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('daily-activities.updateStatus', $activity->id) }}" method="POST" style="display: block;">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="Selesai">
                                                <button type="submit" class="dropdown-item text-success">
                                                    <i class="fas fa-check-circle me-2"></i>Tandai Selesai
                                                </button>
                                            </form>
                                        </li>
                                        @endif

                                        {{-- Opsi: Lanjutkan Besok --}}
                                        @if ($activity->status != 'On Progres Dilanjutkan Besok')
                                        @if($activity->status == 'Selesai' || $activity->status == 'Gagal') <li><hr class="dropdown-divider"></li> @endif {{-- Tambah divider jika perlu --}}
                                        <li>
                                            <form action="{{ route('daily-activities.updateStatus', $activity->id) }}" method="POST" style="display: block;">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="On Progres Dilanjutkan Besok">
                                                <button type="submit" class="dropdown-item text-warning">
                                                    <i class="fas fa-history me-2"></i>Lanjutkan Besok
                                                </button>
                                            </form>
                                        </li>
                                        @endif

                                        {{-- Item Hapus --}}
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('daily-activities.destroy', $activity->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aktivitas ini?');" style="display: block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="fas fa-trash-alt me-2"></i>Hapus
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted p-4">
                                Belum ada aktivitas harian yang dicatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="activityDetailModal" tabindex="-1" aria-labelledby="activityDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg"> {{-- modal-lg untuk lebih lebar --}}
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="activityDetailModalLabel">Detail Aktivitas Harian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Konten detail akan diisi oleh JavaScript --}}
                <div id="modalActivityDetails" class="mb-4">
                    {{-- Placeholder Loading --}}
                    <div class="text-center text-muted py-5 modal-loading-indicator">
                        <div class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        Memuat detail...
                    </div>
                    {{-- Konten Aktual (Template, akan diisi JS) --}}
                    <div class="modal-content-area" style="display: none;"> {{-- Sembunyikan template --}}
                        <div class="row">
                            <div class="col-md-8"> {{-- Kolom Kiri: Detail --}}
                                <dl class="row">
                                    <dt class="col-sm-4">Task Terkait</dt>
                                    <dd class="col-sm-8 modal-task-title">-</dd>

                                    <dt class="col-sm-4">Dilakukan Oleh</dt>
                                    <dd class="col-sm-8 modal-user-name">-</dd>

                                    <dt class="col-sm-4">Tanggal Aktivitas</dt>
                                    <dd class="col-sm-8 modal-activity-date">-</dd>

                                    <dt class="col-sm-4">Aktivitas</dt>
                                    <dd class="col-sm-8 modal-activity-text whitespace-pre-wrap">-</dd> {{-- whitespace-pre-wrap --}}

                                    <dt class="col-sm-4">Deskripsi Tambahan</dt>
                                    <dd class="col-sm-8 modal-activity-desc whitespace-pre-wrap">-</dd>

                                    <dt class="col-sm-4">Dokumen</dt>
                                    <dd class="col-sm-8 modal-activity-doc">-</dd>
                                </dl>
                            </div>
                            <div class="col-md-4"> {{-- Kolom Kanan: Timeline --}}
                                <h6 class="fw-semibold mb-3 border-bottom pb-2">Timeline Status</h6>
                                {{-- Timeline akan dirender di sini oleh JS --}}
                                <div class="modal-timeline-area position-relative ps-3 border-start">
                                {{-- Konten Timeline --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
<script>
    $(document).ready(function() {
        // Inisialisasi DataTables
        const activitiesTable = $('#activitiesTable').DataTable({
            language: { /* ... bahasa indonesia ... */ },
            order: [[ 0, "desc" ]],
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
            pageLength: 10,
            columnDefs: [ { orderable: false, targets: 6 } ]
        });

        // Inisialisasi Tooltip Bootstrap
        function initializeTooltips() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                var existingTooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
                if (existingTooltip) { existingTooltip.dispose(); }
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
        activitiesTable.on('draw.dt', initializeTooltips); // Re-init on draw
        initializeTooltips(); // Initial init

        // === LOGIKA MODAL DETAIL ===
        const detailModalElement = document.getElementById('activityDetailModal');
        const detailModal = new bootstrap.Modal(detailModalElement); // Instance Modal Bootstrap
        const modalContentArea = detailModalElement.querySelector('.modal-content-area');
        const modalLoadingIndicator = detailModalElement.querySelector('.modal-loading-indicator');
        const modalTimelineArea = detailModalElement.querySelector('.modal-timeline-area');

        // Event listener untuk tombol "Lihat Detail" (gunakan event delegation)
        $('#activitiesTable tbody').on('click', '.show-detail-btn', function() {
            const activityId = $(this).data('id');
            const detailUrl = "{{ route('daily-activities.show', ':id') }}".replace(':id', activityId);

            // Tampilkan loading, sembunyikan konten
            modalLoadingIndicator.style.display = 'block';
            modalContentArea.style.display = 'none';
            modalTimelineArea.innerHTML = ''; // Kosongkan timeline lama
            detailModal.show(); // Tampilkan modal

            // Fetch data detail dari server
            fetch(detailUrl)
                .then(response => {
                    if (!response.ok) { throw new Error('Network response was not ok'); }
                    return response.json();
                })
                .then(data => {
                    // Isi detail dasar
                    modalContentArea.querySelector('.modal-task-title').textContent = data.task?.title || 'N/A';
                    let userName = 'N/A';
                    if (data.user) {
                        userName = data.user.name || 'N/A';
                        if (data.user.karyawan) {
                            userName = data.user.karyawan.nama_lengkap || userName;
                        }
                    }
                    modalContentArea.querySelector('.modal-user-name').textContent = userName;
                    modalContentArea.querySelector('.modal-activity-date').textContent = data.activity_date ? new Date(data.activity_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric'}) : '-';
                    modalContentArea.querySelector('.modal-activity-text').textContent = data.activity || '-';
                    modalContentArea.querySelector('.modal-activity-desc').textContent = data.description || '-';

                    // Isi link dokumen
                    const docLinkElement = modalContentArea.querySelector('.modal-activity-doc');
                    if (data.doc) {
                        const fileUrl = "{{ Storage::url('/') }}" + data.doc;
                        docLinkElement.innerHTML = `<a href="${fileUrl}" target="_blank" class="text-primary"><i class="fas fa-file-alt me-1"></i>Lihat Dokumen</a>`;
                    } else {
                        docLinkElement.textContent = '-';
                    }

                    // Render Timeline
                    renderTimeline(data, modalTimelineArea);

                    // Sembunyikan loading, tampilkan konten
                    modalLoadingIndicator.style.display = 'none';
                    modalContentArea.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error fetching activity details:', error);
                    modalLoadingIndicator.innerHTML = '<p class="text-danger text-center">Gagal memuat detail.</p>';
                    modalContentArea.style.display = 'none';
                });
        });

        // Fungsi untuk merender timeline di modal
        function renderTimeline(activity, timelineContainer) {
            timelineContainer.innerHTML = ''; // Kosongkan dulu
            let timelineItems = [];

            // Kumpulkan timestamp yang ada
            if (activity.on_progress_at) timelineItems.push({ status: 'On Progres', time: activity.on_progress_at, color: 'primary' });
            if (activity.on_progress_next_day_at) timelineItems.push({ status: 'Dilanjutkan Besok', time: activity.on_progress_next_day_at, color: 'warning' });
            if (activity.failed_at) timelineItems.push({ status: 'Gagal', time: activity.failed_at, color: 'danger' });
            if (activity.completed_at) timelineItems.push({ status: 'Selesai', time: activity.completed_at, color: 'success' });

            // Urutkan berdasarkan waktu
            timelineItems.sort((a, b) => new Date(a.time) - new Date(b.time));

            // Jika tidak ada timestamp sama sekali, tampilkan status saat ini
            if (timelineItems.length === 0) {
                 let currentColor = 'primary'; // Default On Progress
                 let currentStatus = activity.status || 'On Progres';
                 if (currentStatus == 'On Progres Dilanjutkan Besok') currentColor = 'warning';

                 timelineContainer.innerHTML = `
                    <div class="position-relative mb-3">
                        <div class="position-absolute start-0 translate-middle-x ms-2 mt-1">
                             <span class="d-inline-block rounded-circle border border-2 border-white bg-${currentColor}" style="width: 12px; height: 12px;"></span>
                        </div>
                        <p class="mb-0 fw-semibold text-${currentColor}">${currentStatus}</p>
                        <p class="text-xs text-muted">(Status saat ini)</p>
                    </div>`;
                 return;
            }

            // Render setiap item timeline
            timelineItems.forEach(item => {
                const time = new Date(item.time);
                const formattedTime = time.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'});

                const itemHtml = `
                    <div class="position-relative mb-3">
                        {{-- Titik Timeline --}}
                        <div class="position-absolute start-0 translate-middle-x ms-2 mt-1">
                           <span class="d-inline-block rounded-circle border border-2 border-white bg-${item.color}" style="width: 12px; height: 12px;"></span>
                        </div>
                        {{-- Info Status & Waktu --}}
                        <p class="mb-0 fw-semibold text-${item.color}">${item.status}</p>
                        <p class="text-xs text-muted">${formattedTime}</p>
                    </div>`;
                timelineContainer.insertAdjacentHTML('beforeend', itemHtml);
            });
        }

    });
</script>
@endsection
