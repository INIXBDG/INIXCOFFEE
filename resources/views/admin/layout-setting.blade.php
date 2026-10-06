@extends('layouts.app')

@section('content')
@php
    $sectionMeta = [
        'karyawan'    => ['name' => 'Karyawan','icon' => 'users.svg','color' => '#2563eb','desc' => 'Profil Saya, Data Karyawan, Absensi, Cuti, SPJ, Lembur, Gaji & Tunjangan'],
        'peserta'     => ['name' => 'Peserta', 'icon' => 'user-check.svg', 'color' => '#059669', 'desc' => 'Data Peserta, Registrasi Pelatihan, Data Perusahaan, Registrasi Exam'],
        'itsm'        => ['name' => 'IT Service Management (ITSM)', 'icon' => 'terminal.svg', 'color' => '#4f46e5', 'desc' => 'Timeline Webinar, IT Helpdesk Ticketing, Kanban, SLA, Laporan Insiden, Knowledge'],
        'rkm'         => ['name' => 'Rencana Kelas Mingguan (RKM)', 'icon' => 'calendar-days.svg', 'color' => '#d97706', 'desc' => 'Jadwal RKM, Kelas Setting, Materi, Feedback, Pengajuan Exam, List Exam'],
        'finance'     => ['name' => 'Finance', 'icon' => 'wallet.svg', 'color' => '#0d9488', 'desc' => 'Invoice, Kwitansi, Jurnal Akuntansi, Payroll, Klaim Biaya, Net Sales'],
        'performance' => ['name' => 'Performance Assesment', 'icon' => 'trending-up.svg', 'color' => '#7c3aed', 'desc' => 'KPI Personal, Nilai KPI, Target Aktivitas, Evaluasi Kinerja Karyawan'],
        'education'   => ['name' => 'Education', 'icon' => 'book-open.svg', 'color' => '#db2777', 'desc' => 'Instructor Development, Rekap Mengajar, CV & Sertifikasi, Modul Pembelajaran'],
        'office'      => ['name' => 'Office', 'icon' => 'briefcase.svg', 'color' => '#0284c7', 'desc' => 'Dashboard Office, Inventaris Aset, Souvenir, Catering, Kendaraan, OB Tools'],
        'crm'         => ['name' => 'CRM (Customer Relationship)', 'icon' => 'contact.svg', 'color' => '#ea580c', 'desc' => 'Database Kontak Klien, Peluang Sales, Aktivitas Harian, Laporan Penjualan'],
        'management'  => ['name' => 'Management', 'icon' => 'target.svg', 'color' => '#dc2626', 'desc' => 'Manajemen Target Penjualan, Analisis Kuartal, Target Tahunan'],
        'project'     => ['name' => 'Project', 'icon' => 'layout.svg', 'color' => '#475569', 'desc' => 'Administrasi Project, Berita Acara Handover, Project Tasks, Visit Client'],
    ];  
@endphp

<div class="container-fluid px-3 px-md-5 py-4" style="max-width: 1400px;">
    <!-- Top Header Card -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 14px; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff;">
        <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                    <h4 class="fw-bold mb-0 text-white">Setting Dashboard</h4>
                </div>
                <p class="text-white-50 mb-0 small">
                    Geser (drag & drop) kartu di bawah untuk mengubah tata letak. Posisi kartu tersusun otomatis <strong>Kiri - Kanan</strong> mengikuti tampilan Dashboard Home.
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('home') }}" class="btn btn-outline-light btn-sm px-3 py-2 rounded-pill fw-semibold">
                    ← Kembali ke Dashboard
                </a>
                <button type="button" class="btn btn-primary px-4 py-2 fw-semibold rounded-pill shadow-sm btn-save-action">
                    💾 Simpan Urutan
                </button>
            </div>
        </div>
    </div>

    <!-- Info Bar / Column Legend -->
    <div class="d-flex justify-content-between align-items-center px-1 mb-3 text-muted small flex-wrap gap-2">
        <div class="text-secondary">
            <span><i class="fa-solid fa-grip-vertical me-1"></i> Klik & tahan kartu untuk menggeser</span>
        </div>
    </div>

    <!-- 2-Column Draggable Grid -->
    <div class="row g-3" id="sortable-list">
        @foreach($layouts as $index => $layout)
            @php
                $meta = $sectionMeta[$layout->section_key] ?? [
                    'name' => ucfirst($layout->section_key),
                    'icon' => '📌',
                    'color' => '#475569',
                    'desc' => 'Section ' . $layout->section_key
                ];
                $posNumber = $index + 1;
            @endphp
            <div class="col-12 col-md-6 sortable-item" data-id="{{ $layout->section_key }}" data-key="{{ $layout->section_key }}">
                <div class="card h-100 shadow-sm border-0 section-card p-3" style="border-radius: 12px; border-left: 5px solid {{ $meta['color'] }} !important; cursor: grab; background: #ffffff; transition: transform 0.18s ease, box-shadow 0.18s ease;">
                    
                    <!-- Card Content (rapat) -->
                    <div class="d-flex align-items-start gap-3">
                        <!-- Left: Badge + Drag Handle + Icon (semua berdekatan) -->
                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            <span class="order-badge badge rounded-pill px-2.5 py-1 text-white fw-bold" style="background-color: {{ $meta['color'] }}; font-size: 0.85rem; min-width: 32px; text-align: center;">
                                #{{ $posNumber }}
                            </span>
                            <span class="drag-handle fs-5 text-secondary user-select-none" title="Geser kartu" style="line-height: 1; margin-left: -2px;">
                                ☰
                            </span>
                            <div class="category-icon-box d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 10px; background-color: {{ $meta['color'] }}15; margin-left: 2px;">
                                <img src="{{ asset('icon/' . $meta['icon']) }}" alt="{{ $meta['name'] }}" width="26" height="26" style="object-fit: contain;">
                            </div>
                        </div>

                        <!-- Right: Title + Description -->
                        <div class="flex-grow-1 overflow-hidden pt-1">
                            <h6 class="fw-bold text-dark mb-1 text-truncate">{{ $meta['name'] }}</h6>
                            <p class="text-muted small mb-0 text-truncate-2" style="font-size: 0.82rem; line-height: 1.4;">
                                {{ $meta['desc'] }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<style>
    .section-card {
        user-select: none;
    }
    .section-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px -2px rgba(15, 23, 42, 0.1) !important;
    }
    .sortable-ghost {
        opacity: 0.35 !important;
    }
    .sortable-ghost .section-card {
        background-color: #eff6ff !important;
        border: 2px dashed #3b82f6 !important;
        box-shadow: none !important;
    }
    .sortable-chosen .section-card {
        cursor: grabbing !important;
        transform: scale(1.02);
        box-shadow: 0 12px 20px -4px rgba(15, 23, 42, 0.15) !important;
    }
    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>

<!-- SortableJS CDN -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const sortableListEl = document.getElementById('sortable-list');
    const saveBtns = document.querySelectorAll('.btn-save-action');
    let dragStartOrder = [];
    let draggedKey = null;
    let dropTargetKey = null;

    const sortable = new Sortable(sortableListEl, {
        animation: 200,
        draggable: '.sortable-item',
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        handle: '.section-card',
        onStart: function (event) {
            dragStartOrder = sortable.toArray();
            draggedKey = event.item.dataset.id;
            dropTargetKey = null;
        },
        onMove: function (event) {
            const target = event.related?.closest('.sortable-item');
            if (target && target.dataset.id !== draggedKey) {
                dropTargetKey = target.dataset.id;
            }
        },
        onEnd: function () {
            const sourceIndex = dragStartOrder.indexOf(draggedKey);
            const targetIndex = dragStartOrder.indexOf(dropTargetKey);
            if (sourceIndex !== -1 && targetIndex !== -1 && sourceIndex !== targetIndex) {
                const nextOrder = [...dragStartOrder];
                [nextOrder[sourceIndex], nextOrder[targetIndex]] = [
                    nextOrder[targetIndex],
                    nextOrder[sourceIndex]
                ];
                sortable.sort(nextOrder);
            }
            dragStartOrder = [];
            draggedKey = null;
            dropTargetKey = null;
            updateOrderBadges();
        }
    });

    function updateOrderBadges() {
        const items = sortableListEl.querySelectorAll('.sortable-item');
        items.forEach((item, index) => {
            const pos = index + 1;
            const badge = item.querySelector('.order-badge');
            if (badge) {
                badge.textContent = '#' + pos;
            }
        });
    }

    saveBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const order = sortable.toArray();
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            saveBtns.forEach(b => {
                b.disabled = true;
            });

            Swal.fire({
                title: 'Memproses Data...',
                text: 'Mohon tunggu sejenak.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading()
            });

            fetch('{{ route("admin.layout-setting.update") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ order: order })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw err; });
                }
                return response.json();
            })
            .then(data => {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: data.message || 'Urutan section dashboard berhasil disimpan.',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    allowOutsideClick: false
                });
            })
            .catch(error => {
                console.error('Error saving dashboard layout:', error);
                const errMsg = error.message || (error.errors ? Object.values(error.errors).flat().join(', ') : 'Terjadi kesalahan saat menyimpan urutan.');
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: errMsg,
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    allowOutsideClick: false
                });
            })
            .finally(() => {
                saveBtns.forEach(b => {
                    b.disabled = false;
                });
            });
        });
    });
});
</script>
@endsection