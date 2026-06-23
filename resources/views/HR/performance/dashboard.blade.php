@extends('layout_HR.app')

@section('content_HR')
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-gradient mb-1">Performance Dashboard</h4>
                <p class="text-muted mb-0">Monitor progress KPI dan Penilaian 360 karyawan</p>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="card glass-force mb-4 border-0">
            <div class="card-body p-4">
                <form id="filterForm" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Periode Tahun</label>
                        <select name="tahun" id="filterTahun" class="form-select">
                            @for($y = now()->year; $y >= now()->year - 5; $y--)
                                <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-muted small text-uppercase">Filter Divisi</label>
                        <select name="divisi" id="filterDivisi" class="form-select">
                            <option value="">Semua Divisi</option>
                            @foreach($divisiList as $d)
                                <option value="{{ $d }}">{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-muted small text-uppercase">Filter Jabatan</label>
                        <select name="jabatan" id="filterJabatan" class="form-select">
                            <option value="">Semua Jabatan</option>
                            @foreach($jabatanList as $j)
                                <option value="{{ $j }}">{{ $j }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100 btn-glow">
                            <i class="mdi mdi-filter-variant"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Accordion Content -->
        <div id="accordionContainer">
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Memuat data performa...</p>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function () {
                loadData();

                $('#filterForm').on('submit', function (e) {
                    e.preventDefault();
                    loadData();
                });

                function loadData() {
                    const formData = $('#filterForm').serialize();
                    $('#accordionContainer').html(`
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Memuat data...</p>
                    </div>
                `);

                    $.ajax({
                        url: "{{ route('HR.performance.dashboard.data') }}",
                        type: 'GET',
                        data: formData,
                        success: function (res) {
                            renderAccordion(res.groups);
                        },
                        error: function () {
                            $('#accordionContainer').html('<div class="alert alert-danger glass-force">Gagal memuat data.</div>');
                        }
                    });
                }

                function renderAccordion(groups) {
                    if (!groups || groups.length === 0) {
                        $('#accordionContainer').html('<div class="alert alert-info glass-force">Tidak ada data ditemukan untuk filter ini.</div>');
                        return;
                    }

                    let html = '<div class="accordion" id="jabatanAccordion">';
                    groups.forEach((group, index) => {
                        const collapseId = `collapse_${index}`;
                        const headingId = `heading_${index}`;

                        html += `
                        <div class="accordion-item glass-force mb-3 border-0">
                            <h2 class="accordion-header" id="${headingId}">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#${collapseId}" aria-expanded="false">
                                    <i class="mdi mdi-account-group-outline me-2 text-primary"></i>
                                    ${group.jabatan} 
                                    <span class="badge bg-primary ms-2 rounded-pill">${group.total_users} User</span>
                                </button>
                            </h2>
                            <div id="${collapseId}" class="accordion-collapse collapse" aria-labelledby="${headingId}">
                                <div class="accordion-body p-4">
                                    <div class="row g-3">
                    `;

                        group.users.forEach(user => {
                            html += `
                            <div class="col-md-6 col-xl-4">
                                <div class="card h-100 border-0 shadow-sm card-hover">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center mb-4">
                                            <img src="${user.foto}" class="rounded-circle me-3 border border-2 border-primary" width="55" height="55" style="object-fit: cover;">
                                            <div>
                                                <h6 class="mb-0 fw-bold">${user.nama}</h6>
                                                <small class="text-muted">${user.divisi}</small>
                                            </div>
                                        </div>
                                        <div class="row text-center g-2">
                                            <div class="col-6 border-end pe-3">
                                                <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem;">KPI Progress</small>
                                                <h4 class="fw-bold text-primary mb-1">${user.kpi.score}%</h4>
                                                <span class="badge bg-label-${getGradeColor(user.kpi.grade)}">${user.kpi.grade}</span>
                                            </div>
                                            <div class="col-6 ps-3">
                                                <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem;">Penilaian 360</small>
                                                <h4 class="fw-bold text-success mb-1">${user.assessment_360.score}%</h4>
                                                <span class="badge bg-label-${getGradeColor(user.assessment_360.grade)}">${user.assessment_360.grade}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        });

                        html += `
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    });
                    html += '</div>';
                    $('#accordionContainer').html(html);
                }

                function getGradeColor(grade) {
                    switch (grade) {
                        case 'Sangat Baik': return 'success';
                        case 'Baik': return 'info';
                        case 'Cukup': return 'warning';
                        case 'Kurang': return 'danger';
                        default: return 'secondary';
                    }
                }
            });
        </script>
    @endpush
@endsection