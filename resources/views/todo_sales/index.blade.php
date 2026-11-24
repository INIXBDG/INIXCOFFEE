@extends('layouts.app')

@section('content')
<!-- Vendor CSS: Boxicons & FullCalendar -->
<link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" />

<style>
    .stat-card {
        border-radius: 10px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.08)!important;
    }

    /* FullCalendar Styling */
    #calendarCard {
        display: block !important;
        flex-direction: column !important;
        width: 100% !important;
    }
    #calendarBody {
        width: 100% !important;
    }
    #calendar {
        min-height: 450px;
        background: #fff;
        width: 100% !important;
    }
    .calendar-resize-handle {
        transition: background-color 0.2s, color 0.2s;
    }
    .calendar-resize-handle:hover {
        background-color: #e9ecef !important;
        color: #0d6efd !important;
    }
    .calendar-resize-handle.locked {
        cursor: not-allowed !important;
        background-color: #f8f9fa !important;
        opacity: 0.85;
    }
    .calendar-resize-handle.locked:hover {
        background-color: #f8f9fa !important;
        color: #6c757d !important;
    }
    .fc .fc-toolbar-title {
        font-size: 1.35rem;
        font-weight: 700;
        color: #212529;
    }
    .fc .fc-button-primary {
        background-color: #0d6efd;
        border-color: #0d6efd;
        text-transform: capitalize;
        border-radius: 6px;
        padding: 6px 14px;
        font-weight: 600;
    }
    .fc .fc-button-primary:hover,
    .fc .fc-button-primary:focus,
    .fc .fc-button-primary:active {
        background-color: #0b5ed7 !important;
        border-color: #0a58ca !important;
        box-shadow: none !important;
    }
    .fc .fc-button-primary:disabled {
        background-color: #8c9cb3;
        border-color: #8c9cb3;
    }
    /* Pastikan SEMUA event di kalender tampil sebagai blok/pill solid berwarna */
    .fc .fc-daygrid-event {
        display: block !important;
        border-radius: 5px !important;
        padding: 3px 6px !important;
        margin-bottom: 2px !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12) !important;
        color: #ffffff !important;
        border: none !important;
        cursor: pointer;
        transition: transform 0.15s, opacity 0.15s;
    }
    .fc .fc-daygrid-event:hover {
        transform: translateY(-1px);
        opacity: 0.92;
    }
    .fc .fc-daygrid-event-dot {
        display: none !important;
    }

    /* Warna Status Tugas: Sukses = Biru, Progres = Kuning/Amber, Gagal = Merah */
    .fc .fc-daygrid-event.task-selesai {
        background-color: #0d6efd !important; /* Sukses: Biru */
        border: 1px solid #0b5ed7 !important;
        color: #ffffff !important;
    }
    .fc .fc-daygrid-event.task-progres {
        background-color: #f59e0b !important; /* Dalam Progres: Kuning / Amber */
        border: 1px solid #d97706 !important;
        color: #ffffff !important;
    }
    .fc .fc-daygrid-event.task-gagal {
        background-color: #dc3545 !important; /* Gagal: Merah */
        border: 1px solid #b02a37 !important;
        color: #ffffff !important;
    }

    .fc-holiday-event {
        font-weight: 700 !important;
        border-radius: 5px !important;
        padding: 3px 6px !important;
        background-color: #dc2626 !important;
        border: 1px solid #b91c1c !important;
        color: #ffffff !important;
        box-shadow: 0 1px 2px rgba(220, 38, 38, 0.25);
    }
    .fc-holiday-event:hover {
        background-color: #b91c1c !important;
        color: #ffffff !important;
    }
    .fc-holiday-bg {
        background-color: #fee2e2 !important;
        opacity: 0.8 !important;
    }

    /* Hari Libur Nasional & Libur HRD Warna Merah */
    .fc .fc-daygrid-day.fc-day-holiday {
        background-color: #fef2f2 !important;
    }
    .fc .fc-daygrid-day.fc-day-holiday .fc-daygrid-day-number {
        color: #dc2626 !important;
        font-weight: 700 !important;
    }
    .fc .fc-col-header-cell.fc-day-holiday {
        background-color: #fee2e2 !important;
    }
    .fc .fc-col-header-cell.fc-day-holiday a,
    .fc .fc-col-header-cell.fc-day-holiday .fc-col-header-cell-cushion {
        color: #dc2626 !important;
        font-weight: 700 !important;
    }

    /* Checklist Button & Waktu di Event Kalender */
    .fc-task-pill {
        cursor: pointer;
        font-size: 0.84rem;
        line-height: 1.35;
    }
    .task-check-btn {
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 2px;
        transition: transform 0.15s;
    }
    .task-check-btn:hover {
        transform: scale(1.35);
    }
    .task-time-badge {
        font-size: 0.72rem;
        padding: 1px 4px;
        border-radius: 3px;
        background: rgba(0, 0, 0, 0.22);
    }

    /* Weekend (Sabtu & Minggu) Libur Warna Merah */
    .fc .fc-col-header-cell.fc-day-sat,
    .fc .fc-col-header-cell.fc-day-sun {
        background-color: #fee2e2 !important;
    }
    .fc .fc-col-header-cell.fc-day-sat a,
    .fc .fc-col-header-cell.fc-day-sun a,
    .fc .fc-col-header-cell.fc-day-sat .fc-col-header-cell-cushion,
    .fc .fc-col-header-cell.fc-day-sun .fc-col-header-cell-cushion {
        color: #dc2626 !important;
        font-weight: 700 !important;
    }
    .fc .fc-daygrid-day.fc-day-sat,
    .fc .fc-daygrid-day.fc-day-sun {
        background-color: #fef2f2 !important;
    }
    .fc .fc-daygrid-day.fc-day-sat .fc-daygrid-day-number,
    .fc .fc-daygrid-day.fc-day-sun .fc-daygrid-day-number {
        color: #dc2626 !important;
        font-weight: 700 !important;
    }
    .fc .fc-timegrid-col.fc-day-sat,
    .fc .fc-timegrid-col.fc-day-sun {
        background-color: #fef2f2 !important;
    }
    .fc-list-day.fc-day-sat,
    .fc-list-day.fc-day-sun {
        background-color: #fee2e2 !important;
    }
    .fc-list-day.fc-day-sat .fc-list-day-text,
    .fc-list-day.fc-day-sun .fc-list-day-text,
    .fc-list-day.fc-day-sat .fc-list-day-side-text,
    .fc-list-day.fc-day-sun .fc-list-day-side-text {
        color: #dc2626 !important;
        font-weight: 700 !important;
    }
    #calendarCard:fullscreen {
        padding: 20px;
        background: #ffffff;
        overflow-y: auto;
    }
</style>

<div class="container-fluid py-3 px-4">

    <!-- Flash message alerts -->
    <div id="alertContainer">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bx bx-error me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    {{-- Header Card --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <h3 class="mb-2 fw-bold text-dark">Data To Do List &amp; Jadwal Sales</h3>
            <p class="text-muted fs-6 mb-0">{{ now()->translatedFormat('l, d F Y') }}</p>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm stat-card bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-3 bg-secondary-subtle text-secondary">
                        <i class="bx bx-list-check fs-3"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-semibold">Total Tugas</span>
                        <h4 class="mb-0 fw-bold text-dark" id="statTotal">{{ $stats['total'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm stat-card bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-3 bg-info-subtle text-info">
                        <i class="bx bx-calendar-star fs-3"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-semibold">Jadwal Hari Ini</span>
                        <h4 class="mb-0 fw-bold text-dark" id="statToday">{{ $stats['today'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm stat-card bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-3 bg-warning-subtle text-warning">
                        <i class="bx bx-loader-circle fs-3"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-semibold">Dalam Progres</span>
                        <h4 class="mb-0 fw-bold text-dark" id="statProgres">{{ $stats['progres'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm stat-card bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-3 bg-primary-subtle text-primary">
                        <i class="bx bx-check-double fs-3"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-semibold">Selesai (Sukses)</span>
                        <h4 class="mb-0 fw-bold text-dark" id="statSelesai">{{ $stats['selesai'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toolbar Kontrol Kalender & Info Hari Libur (Di Atas Card) -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div class="d-flex align-items-center gap-2">
            <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                <i class="bx bx-calendar text-primary fs-4"></i>
                <span>Kalender &amp; Agenda</span>
            </h5>
                
        </div>

        <!-- Kontrol Ukuran & Kunci Ukuran Kalender -->
        <div class="d-flex align-items-center gap-2 ms-auto">
            <div class="btn-group btn-group-sm bg-white shadow-sm rounded" role="group" aria-label="Tweak Ukuran">
                <button type="button" class="btn btn-outline-secondary" onclick="adjustCalendarHeight(-50)" title="Perkecil Tinggi Kalender (-50px)">
                    <i class="bx bx-minus"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="adjustCalendarHeight(50)" title="Perbesar Tinggi Kalender (+50px)">
                    <i class="bx bx-plus"></i>
                </button>
            </div>

            <!-- Tombol Kunci Ukuran -->
            <button type="button" class="btn btn-sm btn-outline-primary shadow-sm" id="btnLockSize" onclick="toggleLockSize()">
                <i class="bx bx-lock-open me-1" id="lockIcon"></i>
                <span id="lockBtnText">Kunci Ukuran (<span id="lockedHeightDisplay">700px</span>)</span>
            </button>
        </div>
    </div>

    <!-- Main Content: Schedule Calendar Card (Full Width 100%) -->
    <div class="row g-4" id="calendarRow">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 d-block w-100" id="calendarCard">
                <div class="card-body p-4 w-100" id="calendarBody">
                    <div id="calendar" class="w-100"></div>
                </div>

                <!-- Resize Handle Bar di Bawah Kalender -->
                <div id="calendarResizeHandle" class="calendar-resize-handle text-center py-2 bg-light border-top text-muted" 
                     style="cursor: ns-resize; user-select: none; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;" 
                     title="Klik dan geser ke atas/bawah untuk mengatur tinggi kalender">
                    <i class="bx bx-grid-horizontal fs-4 align-middle"></i>
                    <small class="ms-1 fw-semibold" id="resizeHandleText">Tarik ke atas/bawah untuk memperbesar/memperkecil kalender</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah / Edit Tugas & Jadwal -->
<div class="modal fade" id="todoModal" tabindex="-1" aria-labelledby="todoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <form id="todoForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div id="methodSpoof"></div>
                <input type="hidden" name="id" id="modalTodoId">

                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="todoModalLabel">
                        <i class="bx bx-plus-circle text-primary me-2"></i>Tambah &amp; Buat Jadwal Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Judul <span class="text-danger">*</span></label>
                            <input type="text" name="case" id="modalCase" class="form-control" placeholder="Contoh: Follow up penawaran training PT XYZ" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Waktu Mulai <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="created_at" id="modalCreatedAt" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                            <select name="status" id="modalStatus" class="form-select">
                                <option value="progres">Dalam Progres (Pending)</option>
                                <option value="selesai">Selesai (Completed)</option>
                                <option value="gagal">Gagal / Dibatalkan</option>
                            </select>
                        </div>

                        <div class="col-12 d-none" id="modalWaktuSelesaiContainer">
                            <label class="form-label fw-bold text-success"><i class="bx bx-check-double me-1"></i> Waktu Selesai</label>
                            <input type="text" id="modalWaktuSelesai" class="form-control bg-light text-success fw-bold" readonly placeholder="Belum selesai">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Lampiran Dokumen (Opsional)</label>
                            <input type="file" name="dokumen" id="modalDokumen" class="form-control">
                            <div id="modalDokumenPreview" class="small mt-1 text-muted"></div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Catatan / Detail</label>
                            <textarea name="catatan" id="modalCatatan" class="form-control" rows="3" placeholder="Poin penting atau agenda follow up..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-danger px-3 d-none" id="modalDeleteBtn" onclick="deleteTaskFromModal()">
                        <i class="bx bx-trash me-1"></i> Hapus
                    </button>
                    <div class="d-flex gap-2 ms-auto">
                        <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold" id="modalSubmitBtn">
                            <i class="bx bx-check me-1"></i> Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<!-- jQuery & FullCalendar 5 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('assets/vendor/js/fullcalendar.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

<script>
    let calendar;
    const eventsApiUrl = "{{ route('todo-list.events') }}";
    const storeUrl = "{{ route('todo-list.store') }}";

    // Persistensi Ukuran & Kunci Kalender
    const STORAGE_KEY_HEIGHT = 'inix_calendar_locked_height';
    const STORAGE_KEY_LOCKED = 'inix_calendar_is_locked';

    let savedHeight = parseInt(localStorage.getItem(STORAGE_KEY_HEIGHT)) || 700;
    let isLocked = localStorage.getItem(STORAGE_KEY_LOCKED) === 'true';

    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');

        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
            },
            locale: 'id',
            firstDay: 1, // Mulai hari Senin
            buttonText: {
                today: 'Hari Ini',
                month: 'Bulan',
                week: 'Minggu',
                day: 'Hari',
                list: 'Jadwal Agenda'
            },
            events: eventsApiUrl,
            editable: false,
            selectable: true,
            dayMaxEvents: 3,
            eventDisplay: 'block', // Pastikan semua event tampil sebagai balok/pill penuh

            // Pastikan elemen event selalu memiliki latar belakang warna yang solid
            eventDidMount: function(info) {
                if (info.event.backgroundColor) {
                    info.el.style.setProperty('background-color', info.event.backgroundColor, 'important');
                    info.el.style.setProperty('border-color', info.event.borderColor || info.event.backgroundColor, 'important');
                    info.el.style.setProperty('color', '#ffffff', 'important');
                }
            },

            // Custom render event: tombol ceklis dan jam selesai/mulai
            eventContent: function(arg) {
                const props = arg.event.extendedProps || {};
                if (props.is_holiday) {
                    const icon = props.is_hrd ? 'bx-buildings' : 'bx-calendar-heart';
                    return {
                        html: '<div class="fc-holiday-pill text-truncate px-1 py-0 text-white fw-bold small" title="' + arg.event.title + '"><i class="bx ' + icon + ' me-1"></i><span>' + arg.event.title + '</span></div>'
                    };
                }
                const isSelesai = (props.status === 'selesai');
                const isGagal = (props.status === 'gagal');
                const displayTime = props.display_time || '';
                const checkIcon = isSelesai 
                    ? '<i class="bx bxs-check-circle text-white fs-6"></i>' 
                    : (isGagal ? '<i class="bx bx-x-circle text-white fs-6"></i>' : '<i class="bx bx-circle text-white fs-6"></i>');
                const titleStyle = isSelesai ? 'text-decoration: line-through; opacity: 0.9;' : 'font-weight: 600;';
                
                return {
                    html: `
                        <div class="fc-task-pill d-flex align-items-center w-100 px-1 py-0 text-truncate text-white" title="${arg.event.title}">
                            <span class="task-check-btn me-1 flex-shrink-0" 
                                  title="${isSelesai ? 'Tandai belum selesai' : (isGagal ? 'Tugas gagal' : 'Klik untuk selesaikan tugas ini langsung')}" 
                                  onclick="event.stopPropagation(); quickToggleTask(${arg.event.id});">
                                ${checkIcon}
                            </span>
                            ${displayTime ? `<span class="task-time-badge me-1 small fw-bold flex-shrink-0">${displayTime}</span>` : ''}
                            <span class="task-title-text text-truncate flex-grow-1" style="${titleStyle}">${arg.event.title}</span>
                        </div>
                    `
                };
            },

            // Tandai sel hari libur nasional & HRD dengan warna merah saat event dimuat
            eventsSet: function(events) {
                events.forEach(function(ev) {
                    if (ev.extendedProps && ev.extendedProps.is_holiday) {
                        const dateStr = ev.startStr ? ev.startStr.slice(0, 10) : '';
                        if (dateStr) {
                            const cells = document.querySelectorAll(`.fc-daygrid-day[data-date="${dateStr}"]`);
                            cells.forEach(function(cell) {
                                cell.classList.add('fc-day-holiday');
                            });
                        }
                    }
                });
            },

            // Klik tanggal kalender -> buka modal tambah jadwal untuk tanggal tsb
            select: function(info) {
                openCreateModal(info.startStr);
            },

            // Klik event di kalender -> buka modal edit atau hari libur
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                const props = info.event.extendedProps || {};
                if (props.is_holiday) {
                    const holidayDate = info.event.startStr;
                    const holidayTypeLabel = props.is_hrd ? '🏢 LIBUR PERUSAHAAN (HRD)' : '🏖️ HARI LIBUR NASIONAL';
                    const confirmAdd = confirm(
                        holidayTypeLabel + "\n\n" +
                        info.event.title.replace(/^(🏖️ Libur: |🏢 Libur HRD: )/, '') + "\n" +
                        "Tanggal: " + holidayDate + "\n\n" +
                        "Apakah Anda ingin membuat jadwal baru pada hari libur ini?"
                    );
                    if (confirmAdd) {
                        openCreateModal(holidayDate);
                    }
                    return;
                }
                openEditModal(info.event.id, info.event);
            }
        });

        calendar.render();
        applyCalendarHeight(savedHeight, false);
        updateLockUI();

        // Handle Drag Resize di batas bawah kalender
        let isDragging = false;
        let startY = 0;
        let initialHeight = 0;

        $('#calendarResizeHandle').on('mousedown', function(e) {
            if (isLocked) {
                showNotification('Ukuran kalender sedang dikunci. Klik tombol "Ukuran Terkunci" di atas untuk membuka kunci.', 'warning');
                return;
            }
            isDragging = true;
            startY = e.clientY;
            initialHeight = savedHeight;
            $('body').css('user-select', 'none').css('cursor', 'ns-resize');
        });

        $(document).on('mousemove', function(e) {
            if (!isDragging) return;
            let diff = e.clientY - startY;
            let newHeight = Math.max(450, Math.min(1800, initialHeight + diff));
            applyCalendarHeight(newHeight, false);
        });

        $(document).on('mouseup', function() {
            if (!isDragging) return;
            isDragging = false;
            $('body').css('user-select', '').css('cursor', '');
            localStorage.setItem(STORAGE_KEY_HEIGHT, savedHeight);
            updateLockUI();
        });

        // Touch support untuk drag resize
        $('#calendarResizeHandle').on('touchstart', function(e) {
            if (isLocked) return;
            isDragging = true;
            startY = e.originalEvent.touches[0].clientY;
            initialHeight = savedHeight;
        });

        $(document).on('touchmove', function(e) {
            if (!isDragging) return;
            let diff = e.originalEvent.touches[0].clientY - startY;
            let newHeight = Math.max(450, Math.min(1800, initialHeight + diff));
            applyCalendarHeight(newHeight, false);
        });

        $(document).on('touchend', function() {
            if (!isDragging) return;
            isDragging = false;
            localStorage.setItem(STORAGE_KEY_HEIGHT, savedHeight);
            updateLockUI();
        });

        // Listener saat status di modal diganti
        $('#modalStatus').on('change', function() {
            if ($(this).val() === 'selesai') {
                $('#modalWaktuSelesaiContainer').removeClass('d-none');
                if (!$('#modalWaktuSelesai').val()) {
                    $('#modalWaktuSelesai').val('Otomatis dicatat saat disimpan');
                }
            } else {
                $('#modalWaktuSelesaiContainer').addClass('d-none');
                $('#modalWaktuSelesai').val('');
            }
        });

        // Modal Form Submit Handler
        $('#todoForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this)[0];
            const formData = new FormData(form);
            const todoId = $('#modalTodoId').val();
            const isEdit = (todoId && todoId !== '');

            let url = storeUrl;
            if (isEdit) {
                url = "{{ url('/todo-list') }}/" + todoId + "/update";
            }

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    $('#todoModal').modal('hide');
                    showNotification(res.message || 'Tugas berhasil disimpan!', 'success');
                    calendar.refetchEvents();
                    setTimeout(() => location.reload(), 500);
                },
                error: function(xhr) {
                    let errMsg = 'Terjadi kesalahan validasi.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        errMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    }
                    showNotification(errMsg, 'danger');
                }
            });
        });
    });

    // Terapkan Tinggi Kalender
    function applyCalendarHeight(height, save = true) {
        savedHeight = Math.max(450, Math.min(1800, height));
        if (calendar) {
            calendar.setOption('height', savedHeight);
            calendar.updateSize();
        }
        $('#lockedHeightDisplay').text(savedHeight + 'px');
        if (save) {
            localStorage.setItem(STORAGE_KEY_HEIGHT, savedHeight);
        }
    }

    // Perbarui Tampilan Tombol Kunci & Handle Resize
    function updateLockUI() {
        if (isLocked) {
            $('#btnLockSize')
                .removeClass('btn-outline-primary')
                .addClass('btn-success')
                .attr('title', 'Ukuran kalender terkunci. Klik untuk membuka kunci jika ingin mengubah ukuran.');
            $('#lockIcon').removeClass('bx-lock-open').addClass('bx-lock-alt');
            $('#lockBtnText').html('Ukuran Terkunci (<span id="lockedHeightDisplay">' + savedHeight + 'px</span>)');
            $('#calendarResizeHandle').addClass('locked');
            $('#resizeHandleText').html('<i class="bx bx-lock-alt me-1 text-success"></i> Ukuran kalender terkunci (' + savedHeight + 'px). Klik "Ukuran Terkunci" di atas untuk membuka kunci.');
        } else {
            $('#btnLockSize')
                .removeClass('btn-success')
                .addClass('btn-outline-primary')
                .attr('title', 'Klik untuk mengunci ukuran kalender ini agar tidak berubah lagi.');
            $('#lockIcon').removeClass('bx-lock-alt').addClass('bx-lock-open');
            $('#lockBtnText').html('Kunci Ukuran (<span id="lockedHeightDisplay">' + savedHeight + 'px</span>)');
            $('#calendarResizeHandle').removeClass('locked');
            $('#resizeHandleText').html('<i class="bx bx-expand-vertical me-1"></i> Tarik ke atas/bawah untuk memperbesar/memperkecil kalender');
        }
    }

    // Toggle Kunci Ukuran
    function toggleLockSize() {
        isLocked = !isLocked;
        localStorage.setItem(STORAGE_KEY_LOCKED, isLocked ? 'true' : 'false');
        localStorage.setItem(STORAGE_KEY_HEIGHT, savedHeight);
        updateLockUI();

        if (isLocked) {
            showNotification('Ukuran kalender (' + savedHeight + 'px) berhasil dikunci! Ukuran ini tersimpan permanen dan tidak akan kembali ke awal.', 'success');
        } else {
            showNotification('Kunci ukuran dibuka. Anda dapat menarik batas bawah atau klik tombol +/- untuk mengubah ukuran.', 'info');
        }
    }

    // Tweak Ukuran +/- 50px
    function adjustCalendarHeight(delta) {
        if (isLocked) {
            showNotification('Ukuran kalender sedang dikunci. Buka kunci terlebih dahulu untuk mengubah ukuran.', 'warning');
            return;
        }
        applyCalendarHeight(savedHeight + delta);
        updateLockUI();
    }

    // Buka Modal Tambah
    function openCreateModal(dateStr = null) {
        $('#todoForm')[0].reset();
        $('#modalTodoId').val('');
        $('#methodSpoof').html('');
        $('#modalDokumenPreview').html('');
        $('#modalDeleteBtn').addClass('d-none');
        $('#modalWaktuSelesaiContainer').addClass('d-none');
        $('#modalWaktuSelesai').val('');
        $('#todoModalLabel').html('<i class="bx bx-plus-circle text-primary me-2"></i>Tambah &amp; Buat Jadwal Baru');
        $('#modalSubmitBtn').html('<i class="bx bx-check me-1"></i> Simpan Tugas');

        // Set default datetime
        let now = new Date();
        if (dateStr) {
            let selectedDate = new Date(dateStr);
            selectedDate.setHours(now.getHours(), now.getMinutes());
            now = selectedDate;
        }
        const formattedNow = formatDatetimeLocal(now);
        $('#modalCreatedAt').val(formattedNow);

        $('#todoModal').modal('show');
    }

    // Buka Modal Edit
    function openEditModal(id, eventObj = null) {
        $('#todoForm')[0].reset();
        $('#modalTodoId').val(id);
        $('#modalDeleteBtn').removeClass('d-none');
        $('#todoModalLabel').html('<i class="bx bx-edit text-warning me-2"></i>Edit Tugas & Jadwal');
        $('#modalSubmitBtn').html('<i class="bx bx-check me-1"></i> Perbarui Tugas');

        if (eventObj) {
            populateModalFromEvent(eventObj);
            $('#todoModal').modal('show');
            return;
        }

        // Ambil data detail via events list
        const event = calendar ? calendar.getEventById(id) : null;
        if (event) {
            populateModalFromEvent(event);
            $('#todoModal').modal('show');
        } else {
            $('#modalCreatedAt').val(formatDatetimeLocal(new Date()));
            $('#todoModal').modal('show');
        }
    }

    function populateModalFromEvent(event) {
        $('#modalCase').val(event.title);
        if (event.start) {
            $('#modalCreatedAt').val(formatDatetimeLocal(event.start));
        }
        const props = event.extendedProps || {};
        $('#modalStatus').val(props.status || 'progres');
        $('#modalCatatan').val(props.catatan || '');

        if (props.status === 'selesai' && props.waktu_selesai) {
            $('#modalWaktuSelesai').val(props.waktu_selesai);
            $('#modalWaktuSelesaiContainer').removeClass('d-none');
        } else {
            $('#modalWaktuSelesai').val('');
            $('#modalWaktuSelesaiContainer').addClass('d-none');
        }

        if (props.dokumen) {
            $('#modalDokumenPreview').html(`<a href="${props.dokumen}" target="_blank" class="text-primary"><i class="bx bx-file"></i> Unduh ${props.dokumen_name || 'Lampiran'}</a>`);
        } else {
            $('#modalDokumenPreview').html('');
        }
    }

    // Ceklis Instan Tugas di Kalender (Toggle Selesai / Progres)
    function quickToggleTask(id) {
        $.ajax({
            url: "{{ url('/todo-list') }}/" + id + "/toggle",
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(res) {
                showNotification(res.message || 'Status tugas diperbarui!', 'success');
                if (calendar) {
                    calendar.refetchEvents();
                }
                if (res.stats) {
                    $('#statTotal').text(res.stats.total);
                    $('#statToday').text(res.stats.today);
                    $('#statProgres').text(res.stats.progres);
                    $('#statSelesai').text(res.stats.selesai);
                }
            },
            error: function(xhr) {
                let msg = 'Gagal memperbarui status tugas.';
                if (xhr.status === 403) {
                    msg = 'Akses ditolak: Hanya SPV Sales dan Admin Sales yang diizinkan.';
                }
                showNotification(msg, 'danger');
            }
        });
    }

    // Hapus Tugas dari Modal
    function deleteTaskFromModal() {
        const id = $('#modalTodoId').val();
        if (!id) return;
        $('#todoModal').modal('hide');
        deleteTask(id);
    }

    // Hapus Tugas
    function deleteTask(id) {
        if (!confirm('Yakin ingin menghapus tugas ini?')) return;

        $.ajax({
            url: "{{ url('/todo-list') }}/" + id + "/delete",
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function() {
                showNotification('Tugas berhasil dihapus.', 'success');
                if (calendar) {
                    calendar.refetchEvents();
                }
                setTimeout(() => location.reload(), 400);
            },
            error: function() {
                showNotification('Gagal menghapus tugas.', 'danger');
            }
        });
    }

    // Helper Toast Notification
    function showNotification(message, type = 'success') {
        const icon = type === 'success' ? 'check-circle' : (type === 'danger' ? 'error' : 'info-circle');
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show position-fixed shadow-sm" 
                 style="top: 80px; right: 20px; z-index: 99999; min-width: 280px;" role="alert">
                <i class="bx bx-${icon} me-1 fs-5 align-middle"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('#alertContainer').append(alertHtml);
        setTimeout(function() {
            $('.position-fixed.alert').fadeOut(function() { $(this).remove(); });
        }, 3000);
    }

    // Helper format date for <input type="datetime-local">
    function formatDatetimeLocal(date) {
        const d = new Date(date);
        d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
        return d.toISOString().slice(0, 16);
    }
</script>
@endpush
@endsection
