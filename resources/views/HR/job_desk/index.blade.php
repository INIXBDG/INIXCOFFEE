@extends('layout_HR.app')
@section('content_HR')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root {
            --pri: #4f46e5;
            --pri-light: #eef2ff;
            --pri-dark: #3730a3;
            --success: #059669;
            --success-light: #d1fae5;
            --warning: #d97706;
            --warning-light: #fef3c7;
            --info: #0284c7;
            --info-light: #e0f2fe;
            --danger: #dc2626;
            --danger-light: #fee2e2;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-400: #9ca3af;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-900: #111827;
            --radius: 10px;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, .08), 0 1px 2px rgba(0, 0, 0, .05);
            --shadow: 0 4px 6px rgba(0, 0, 0, .07), 0 2px 4px rgba(0, 0, 0, .05);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, .1), 0 4px 10px rgba(0, 0, 0, .07);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: var(--gray-900);
            background: #fafbfc;
        }

        .page-header { margin-bottom: 1.5rem; }
        .page-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: .15rem;
        }
        .page-sub { color: var(--gray-400); font-size: .875rem; }

        .nav-tabs-custom { border-bottom: 2px solid var(--gray-200); }
        .nav-tabs-custom .nav-link {
            border: none;
            color: var(--gray-400);
            font-weight: 600;
            padding: .85rem 1.25rem;
            font-size: .875rem;
            transition: color .2s;
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .nav-tabs-custom .nav-link:hover { color: var(--pri); }
        .nav-tabs-custom .nav-link.active {
            color: var(--pri);
            border-bottom: 3px solid var(--pri);
            background: transparent;
        }
        .nav-tabs-custom .nav-link .tab-count {
            background: var(--gray-100);
            color: var(--gray-600);
            font-size: .7rem;
            padding: 2px 8px;
            border-radius: 10px;
            font-weight: 700;
        }
        .nav-tabs-custom .nav-link.active .tab-count {
            background: var(--pri-light);
            color: var(--pri);
        }

        .card-shell {
            border: none;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            background: #fff;
        }
        .card-shell .card-body { padding: 1.5rem; }

        .btn-pri {
            background: var(--pri);
            border: none;
            color: #fff;
            font-weight: 600;
            padding: .5rem 1.25rem;
            border-radius: 8px;
            transition: all .25s;
            font-size: .85rem;
        }
        .btn-pri:hover {
            background: var(--pri-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, .35);
            color: #fff;
        }
        .btn-outline-sec {
            background: #fff;
            border: 1px solid var(--gray-200);
            color: var(--gray-600);
            font-weight: 500;
            padding: .4rem 1rem;
            border-radius: 8px;
            transition: all .2s;
            font-size: .85rem;
        }
        .btn-outline-sec:hover {
            background: var(--gray-50);
            border-color: var(--gray-400);
            color: var(--gray-900);
        }
        .btn-outline-danger-custom {
            background: transparent;
            border: 1px solid var(--danger-light);
            color: var(--danger);
            font-weight: 500;
            padding: .4rem .85rem;
            border-radius: 8px;
            transition: all .2s;
            font-size: .85rem;
        }
        .btn-outline-danger-custom:hover {
            background: var(--danger);
            border-color: var(--danger);
            color: #fff;
        }

        .table-modern {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }
        .table-modern thead th {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--gray-600);
            background: var(--gray-50);
            border-bottom: 2px solid var(--gray-200) !important;
            border-top: none !important;
            padding: 0.85rem 1rem;
        }
        .table-modern tbody tr { transition: background .15s; }
        .table-modern tbody tr:hover { background: var(--pri-light) !important; }
        .table-modern tbody td {
            vertical-align: middle;
            font-size: .875rem;
            border-bottom: 1px solid var(--gray-100) !important;
            border-top: none !important;
            padding: 0.85rem 1rem;
            color: var(--gray-700);
        }
        .table-modern tbody tr:last-child td { border-bottom: none !important; }

        .badge-karyawan {
            background: var(--pri-light);
            color: var(--pri);
            font-size: .72rem;
            font-weight: 600;
            padding: .3rem .65rem;
            border-radius: 6px;
            display: inline-block;
            margin: 2px;
        }
        .badge-count {
            background: var(--gray-100);
            color: var(--gray-600);
            font-size: .72rem;
            font-weight: 700;
            padding: .25rem .6rem;
            border-radius: 10px;
        }

        .accordion { --bs-accordion-border-color: var(--gray-200); }
        .accordion-item {
            border: 1px solid var(--gray-200) !important;
            border-radius: var(--radius) !important;
            margin-bottom: 8px;
            overflow: hidden;
            transition: box-shadow .2s;
        }
        .accordion-item:hover { box-shadow: var(--shadow-sm); }
        .accordion-button {
            background: #fff;
            color: var(--gray-700);
            font-weight: 600;
            font-size: .9rem;
            padding: 1rem 1.25rem;
            box-shadow: none !important;
        }
        .accordion-button:not(.collapsed) {
            background: var(--pri-light);
            color: var(--pri);
            box-shadow: none !important;
        }
        .accordion-button::after { filter: none; }
        .accordion-button:not(.collapsed)::after {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%234f46e5'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
        }
        .accordion-body { padding: 1.25rem; background: #fff; }

        .karyawan-card {
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            padding: 1.25rem;
            margin-bottom: 10px;
            background: var(--gray-50);
            transition: all .2s;
        }
        .karyawan-card:hover {
            border-color: var(--pri);
            background: #fff;
            box-shadow: var(--shadow-sm);
        }
        .karyawan-card:last-child { margin-bottom: 0; }
        .karyawan-name {
            font-weight: 700;
            color: var(--gray-900);
            font-size: .95rem;
            margin-bottom: 4px;
        }
        .karyawan-meta { color: var(--gray-400); font-size: .8rem; }

        .profile-section { margin-top: 12px; }
        .profile-section-title {
            font-size: .72rem;
            font-weight: 700;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: .6px;
            margin-bottom: .6rem;
            padding-bottom: .4rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            gap: .4rem;
        }
        .profile-list { list-style: none; padding: 0; margin: 0 0 12px 0; }
        .profile-list li {
            padding: .35rem 0 .35rem 18px;
            position: relative;
            color: var(--gray-600);
            font-size: .85rem;
            line-height: 1.6;
        }
        .profile-list li::before {
            content: "–";
            position: absolute;
            left: 0;
            color: var(--pri);
            font-weight: 700;
        }
        .empty-text { color: var(--gray-400); font-size: .82rem; font-style: italic; }

        .form-control, .form-select {
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            padding: .5rem .85rem;
            font-size: .875rem;
            color: var(--gray-700);
            transition: all .2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--pri);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, .12);
            outline: none;
        }
        .form-label {
            font-weight: 600;
            color: var(--gray-700);
            font-size: .82rem;
            margin-bottom: .4rem;
        }

        .parent-card {
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 10px;
        }
        .parent-header { display: flex; gap: 8px; margin-bottom: 8px; }
        .parent-header input { flex: 1; font-weight: 600; font-size: .875rem; }
        .details-container {
            margin-left: 16px;
            padding-left: 12px;
            border-left: 2px solid var(--pri-light);
        }
        .detail-row { display: flex; gap: 8px; margin-bottom: 6px; align-items: center; }
        .detail-row input { flex: 1; }

        .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
        }
        .modal-header-custom {
            background: linear-gradient(135deg, var(--pri) 0%, var(--pri-dark) 100%);
            color: #fff;
            border-radius: 12px 12px 0 0;
            padding: 1.1rem 1.5rem;
        }
        .modal-header-custom .modal-title { font-weight: 700; font-size: 1rem; }
        .modal-header-custom .btn-close { filter: brightness(0) invert(1); }
        .modal-body { padding: 1.5rem; }
        .modal-footer {
            border-top: 1px solid var(--gray-100);
            padding: 1rem 1.5rem;
        }

        .modal-dialog-scrollable .modal-content {
            max-height: calc(100vh - 2rem) !important;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .modal-dialog-scrollable .modal-body {
            overflow-y: auto !important;
            flex: 1 1 auto;
            min-height: 0;
        }
        .modal-dialog-scrollable form {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
            overflow: hidden;
        }
        .modal-dialog-scrollable form .modal-body { overflow-y: auto !important; }

        .alert-custom {
            border: none;
            border-radius: 8px;
            padding: .85rem 1.15rem;
            font-size: .85rem;
            font-weight: 500;
        }
        .alert-custom.alert-success { background: var(--success-light); color: var(--success); }
        .alert-custom.alert-danger { background: var(--danger-light); color: var(--danger); }
        .alert-custom.alert-info { background: var(--info-light); color: var(--info); }

        .dropdown-menu {
            border: none;
            box-shadow: var(--shadow-lg);
            border-radius: 8px;
            padding: .4rem;
            font-size: .85rem;
        }
        .dropdown-item {
            padding: .5rem .85rem;
            border-radius: 6px;
            transition: all .15s;
        }
        .dropdown-item:hover { background: var(--pri-light); color: var(--pri); }
        .dropdown-item.text-danger:hover { background: var(--danger-light); color: var(--danger); }

        .section-divider {
            border: none;
            border-top: 1px solid var(--gray-200);
            margin: 1.5rem 0;
        }
        .section-title {
            font-size: .85rem;
            font-weight: 700;
            color: var(--gray-700);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .section-title i { color: var(--pri); }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--gray-400);
        }
        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            opacity: .5;
        }
        .empty-state p { font-size: .9rem; margin: 0; }

        .detail-block {
            background: var(--gray-50);
            border-left: 3px solid var(--pri);
            padding: 1rem 1.25rem;
            margin-bottom: 10px;
            border-radius: 0 8px 8px 0;
        }
        .detail-block-title {
            font-weight: 700;
            color: var(--gray-900);
            font-size: .875rem;
            margin-bottom: 8px;
        }
        .detail-block ul {
            margin-left: 18px;
            padding-left: 12px;
            border-left: 1px dashed var(--gray-200);
            margin-bottom: 0;
        }
        .detail-block ul li {
            color: var(--gray-600);
            padding: 3px 0;
            line-height: 1.6;
            font-size: .85rem;
        }

        .info-card-mini {
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            padding: .85rem 1rem;
        }
        .info-card-mini .label {
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--gray-400);
            font-weight: 700;
            margin-bottom: 2px;
        }
        .info-card-mini .value {
            font-size: .9rem;
            font-weight: 600;
            color: var(--gray-900);
        }

        .kompetensi-tag {
            display: inline-block;
            background: var(--pri-light);
            color: var(--pri);
            padding: 4px 12px;
            border-radius: 6px;
            font-size: .78rem;
            font-weight: 600;
            margin: 3px 4px 3px 0;
        }

        .skeleton {
            background: linear-gradient(90deg, #f0f2f5 25%, #e6e9ef 50%, #f0f2f5 75%);
            background-size: 200% 100%;
            animation: skeleton-shimmer 1.4s ease-in-out infinite;
            border-radius: 8px;
        }
        @keyframes skeleton-shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>
    <div class="container-fluid px-4 py-4">
        <div class="d-sm-flex align-items-center justify-content-between page-header">
            <div>
                <h1 class="page-title">Job Management</h1>
                <p class="page-sub mb-0">Kelola Job Desk, SOP, dan Job Profile karyawan</p>
            </div>
        </div>

        <ul class="nav nav-tabs nav-tabs-custom mb-4" id="mainTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="jobdesk-tab" data-bs-toggle="tab" data-bs-target="#jobdesk-pane" type="button">
                    Job Desk
                    <span class="tab-count">{{ $jobDesks->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="sop-tab" data-bs-toggle="tab" data-bs-target="#sop-pane" type="button">
                    SOP
                    <span class="tab-count">{{ $jobDesks->filter(fn($jd) => !empty($jd->sop) && count($jd->sop) > 0)->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-pane" type="button">
                    Job Profile
                    <span class="tab-count">{{ $orgStructures->count() }}</span>
                </button>
            </li>
        </ul>

        <div class="tab-content" id="mainTabsContent">
            <div class="tab-pane fade show active" id="jobdesk-pane" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="section-title mb-0">Daftar Job Desk</h6>
                    <button class="btn btn-pri" onclick="openModalCreateJobDesk()">
                        <i class="fa-solid fa-plus me-1"></i>Tambah Job Desk
                    </button>
                </div>
                <div id="alertContainerJobDesk"></div>
                <div class="card card-shell">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table-modern mb-0">
                                <thead>
                                    <tr>
                                        <th width="40">No</th>
                                        <th>Jabatan</th>
                                        <th>Karyawan</th>
                                        <th>Fungsi Utama</th>
                                        <th width="80" class="text-center">Tugas</th>
                                        <th width="80" class="text-center">Wewenang</th>
                                        <th width="90" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyJobDesk">
                                    @php $no = 0; @endphp
                                    @forelse($jobDesks as $jobDesk)
                                        @if (!empty($jobDesk->fungsi_utama) || !empty($jobDesk->tugas_tanggung_jawab) || !empty($jobDesk->wewenang))
                                            @php $no++; @endphp
                                            <tr>
                                                <td class="text-muted">{{ $no }}</td>
                                                <td>
                                                    <strong style="color:var(--gray-900)">{{ $jobDesk->orgStructure->jabatan ?? '-' }}</strong>
                                                    @if ($jobDesk->orgStructure->divisi ?? false)
                                                        <br><small class="text-muted">{{ $jobDesk->orgStructure->divisi }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($jobDesk->orgStructure && $jobDesk->orgStructure->karyawans->count() > 0)
                                                        @foreach ($jobDesk->orgStructure->karyawans as $k)
                                                            <span class="badge-karyawan">{{ $k->nama_lengkap }}</span>
                                                        @endforeach
                                                    @else
                                                        <span class="empty-text">—</span>
                                                    @endif
                                                </td>
                                                <td style="max-width:250px">{{ Str::limit($jobDesk->fungsi_utama, 60) ?: '—' }}</td>
                                                <td class="text-center"><span class="badge-count">{{ count($jobDesk->tugas_tanggung_jawab ?? []) }}</span></td>
                                                <td class="text-center"><span class="badge-count">{{ count($jobDesk->wewenang ?? []) }}</span></td>
                                                <td class="text-center">
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-sec dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li><a class="dropdown-item" href="#" onclick="openModalDetailJobDesk({{ $jobDesk->id }}); return false;"><i class="fa-solid fa-eye me-2" style="color:var(--pri)"></i>Detail</a></li>
                                                            <li><a class="dropdown-item" href="#" onclick="openModalEditJobDesk({{ $jobDesk->id }}); return false;"><i class="fa-solid fa-pen me-2" style="color:var(--warning)"></i>Edit</a></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><a class="dropdown-item text-danger" href="#" onclick="confirmDeleteJobDesk({{ $jobDesk->id }}); return false;"><i class="fa-solid fa-trash me-2"></i>Hapus</a></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @empty
                                    @endforelse
                                    @if ($no === 0)
                                        <tr>
                                            <td colspan="7">
                                                <div class="empty-state">
                                                    <i class="fa-solid fa-clipboard-list d-block"></i>
                                                    <p class="fw-semibold">Belum ada data Job Desk</p>
                                                    <small>Klik tombol "Tambah Job Desk" untuk memulai</small>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="sop-pane" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="section-title mb-0">Standard Operating Procedure</h6>
                    <button class="btn btn-pri" onclick="openModalCreateSop()">
                        <i class="fa-solid fa-plus me-1"></i>Tambah SOP
                    </button>
                </div>
                <div id="alertContainerSop"></div>
                <div class="card card-shell">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table-modern mb-0">
                                <thead>
                                    <tr>
                                        <th width="40">No</th>
                                        <th>Jabatan</th>
                                        <th>Karyawan</th>
                                        <th width="100" class="text-center">Jumlah SOP</th>
                                        <th>SOP Utama</th>
                                        <th width="90" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodySop">
                                    @php
                                        $sopNo = 0;
                                        $sopDesks = $jobDesks->filter(fn($jd) => !empty($jd->sop) && count($jd->sop) > 0);
                                    @endphp
                                    @forelse($sopDesks as $jobDesk)
                                        @php $sopNo++; @endphp
                                        <tr>
                                            <td class="text-muted">{{ $sopNo }}</td>
                                            <td>
                                                <strong style="color:var(--gray-900)">{{ $jobDesk->orgStructure->jabatan ?? '-' }}</strong>
                                                @if ($jobDesk->orgStructure->divisi ?? false)
                                                    <br><small class="text-muted">{{ $jobDesk->orgStructure->divisi }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($jobDesk->orgStructure && $jobDesk->orgStructure->karyawans->count() > 0)
                                                    @foreach ($jobDesk->orgStructure->karyawans as $k)
                                                        <span class="badge-karyawan">{{ $k->nama_lengkap }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="empty-text">—</span>
                                                @endif
                                            </td>
                                            <td class="text-center"><span class="badge-count">{{ count($jobDesk->sop ?? []) }}</span></td>
                                            <td style="max-width:280px">
                                                @if (!empty($jobDesk->sop) && count($jobDesk->sop) > 0)
                                                    {{ Str::limit($jobDesk->sop[0]['name'] ?? '-', 50) }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-sec dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item" href="#" onclick="openModalDetailSop({{ $jobDesk->id }}); return false;"><i class="fa-solid fa-eye me-2" style="color:var(--pri)"></i>Detail</a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="openModalEditSop({{ $jobDesk->id }}); return false;"><i class="fa-solid fa-pen me-2" style="color:var(--warning)"></i>Edit</a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item text-danger" href="#" onclick="confirmDeleteSop({{ $jobDesk->id }}); return false;"><i class="fa-solid fa-trash me-2"></i>Hapus</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6">
                                                <div class="empty-state">
                                                    <i class="fa-solid fa-file-contract d-block"></i>
                                                    <p class="fw-semibold">Belum ada data SOP</p>
                                                    <small>Klik tombol "Tambah SOP" untuk memulai</small>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="profile-pane" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="section-title mb-0">Job Profile per Struktur Organisasi</h6>
                </div>
                <div id="alertContainerProfile"></div>
                <div id="profileAccordionArea">
                    <div class="accordion" id="profileAccordion">
                        @forelse($orgStructures as $org)
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading{{ $org->id }}">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $org->id }}">
                                        <span class="me-2">{{ $org->jabatan }}</span>
                                        @if ($org->divisi)
                                            <small class="text-muted">— {{ $org->divisi }}</small>
                                        @endif
                                        <span class="badge-count ms-2">{{ $org->karyawans->count() }} karyawan</span>
                                    </button>
                                </h2>
                                <div id="collapse{{ $org->id }}" class="accordion-collapse collapse" data-bs-parent="#profileAccordion">
                                    <div class="accordion-body">
                                        @forelse($org->karyawans as $karyawan)
                                            <div class="karyawan-card">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <div class="karyawan-name">{{ $karyawan->nama_lengkap }}</div>
                                                        <div class="karyawan-meta">{{ $karyawan->jabatan ?? $org->jabatan }} · {{ $org->divisi ?? '-' }}</div>
                                                    </div>
                                                    <div class="d-flex gap-2">
                                                        <button class="btn btn-sm btn-outline-sec" onclick="openModalProfile({{ $karyawan->id }}, '{{ addslashes($karyawan->nama_lengkap) }}')">
                                                            <i class="fa-solid fa-pen me-1"></i>Profile
                                                        </button>
                                                        @if ($karyawan->jobProfile ?? false)
                                                            <button class="btn btn-sm btn-outline-danger-custom" onclick="confirmDeleteProfile({{ $karyawan->id }})">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                                @if ($karyawan->jobProfile ?? false)
                                                    <div class="profile-section mt-3">
                                                        @if (!empty($karyawan->jobProfile->qualifications))
                                                            <div class="profile-section-title"><i class="fa-solid fa-graduation-cap"></i> Qualifications</div>
                                                            <ul class="profile-list">
                                                                @foreach ($karyawan->jobProfile->qualifications as $q)
                                                                    <li>{{ $q }}</li>
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                        @if (!empty($karyawan->jobProfile->descriptions))
                                                            <div class="profile-section-title"><i class="fa-solid fa-file-lines"></i> Job Description</div>
                                                            <ul class="profile-list">
                                                                @foreach ($karyawan->jobProfile->descriptions as $d)
                                                                    <li>{{ $d }}</li>
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                        @if (!empty($karyawan->jobProfile->compensation_benefit))
                                                            <div class="profile-section-title"><i class="fa-solid fa-gift"></i> Compensation & Benefit</div>
                                                            <ul class="profile-list">
                                                                @foreach ($karyawan->jobProfile->compensation_benefit as $c)
                                                                    <li>{{ $c }}</li>
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                    </div>
                                                @else
                                                    <p class="empty-text mt-2 mb-0">Belum ada Job Profile</p>
                                                @endif
                                            </div>
                                        @empty
                                            <p class="empty-text mb-0">Tidak ada karyawan pada jabatan ini</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                <i class="fa-solid fa-sitemap d-block"></i>
                                <p class="fw-semibold">Belum ada struktur organisasi</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalJobDesk" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form id="formJobDesk">
                    <div class="modal-header modal-header-custom">
                        <h5 class="modal-title" id="modalJobDeskTitle"><i class="fa-solid fa-clipboard-list me-2"></i>Tambah Job Desk</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="editJobDeskId" name="id">
                        <div class="mb-3">
                            <label class="form-label">Jabatan / Struktur Organisasi</label>
                            <select class="form-select" id="selectOrgJobDesk" name="id_org" required>
                                <option value="">— Pilih Jabatan —</option>
                                @foreach ($orgStructures as $org)
                                    <option value="{{ $org->id }}">{{ $org->jabatan }}{{ $org->divisi ? ' — ' . $org->divisi : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fungsi Utama</label>
                            <textarea class="form-control" id="fungsiUtama" name="fungsi_utama" rows="3"></textarea>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Tujuan Jabatan</label>
                                <textarea class="form-control" name="tujuan_jabatan" rows="2"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kualifikasi Pendidikan</label>
                                <textarea class="form-control" name="kualifikasi_pendidikan" rows="2"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pengalaman Kerja</label>
                                <textarea class="form-control" name="pengalaman_kerja" rows="2"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Karakteristik Pribadi</label>
                                <textarea class="form-control" name="karakteristik_pribadi" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kompetensi</label>
                            <div id="kompetensiContainer"></div>
                            <button type="button" class="btn btn-outline-sec btn-sm mt-2" onclick="addSimpleField('kompetensiContainer', 'kompetensi[]', 'Kompetensi')">
                                <i class="fa-solid fa-plus me-1"></i>Tambah Kompetensi
                            </button>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tugas & Tanggung Jawab</label>
                            <div id="tugasContainer"></div>
                            <button type="button" class="btn btn-outline-sec btn-sm mt-2" onclick="addParentField('tugasContainer', 'tugas_tanggung_jawab', 'Tugas Utama', 'Detail Tugas')">
                                <i class="fa-solid fa-plus me-1"></i>Tambah Tugas
                            </button>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Wewenang</label>
                            <div id="wewenangContainer"></div>
                            <button type="button" class="btn btn-outline-sec btn-sm mt-2" onclick="addParentField('wewenangContainer', 'wewenang', 'Wewenang Utama', 'Detail Wewenang')">
                                <i class="fa-solid fa-plus me-1"></i>Tambah Wewenang
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-sec" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-pri" id="btnSubmitJobDesk"><i class="fa-solid fa-save me-1"></i>Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalSop" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form id="formSop">
                    <div class="modal-header modal-header-custom">
                        <h5 class="modal-title" id="modalSopTitle"><i class="fa-solid fa-file-contract me-2"></i>Tambah SOP</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="editSopId" name="id">
                        <div class="mb-3">
                            <label class="form-label">Jabatan / Struktur Organisasi</label>
                            <select class="form-select" id="selectOrgSop" name="id_org" required>
                                <option value="">— Pilih Jabatan —</option>
                                @foreach ($orgStructures as $org)
                                    <option value="{{ $org->id }}">{{ $org->jabatan }}{{ $org->divisi ? ' — ' . $org->divisi : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">SOP</label>
                            <div id="sopContainer"></div>
                            <button type="button" class="btn btn-outline-sec btn-sm mt-2" onclick="addParentField('sopContainer', 'sop', 'SOP Utama', 'Detail SOP')">
                                <i class="fa-solid fa-plus me-1"></i>Tambah SOP
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-sec" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-pri" id="btnSubmitSop"><i class="fa-solid fa-save me-1"></i>Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalProfile" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form id="formProfile">
                    <div class="modal-header modal-header-custom">
                        <h5 class="modal-title" id="modalProfileTitle"><i class="fa-solid fa-user-tie me-2"></i>Job Profile</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="profileKaryawanId" name="karyawan_id">
                        <div class="mb-3">
                            <label class="form-label">Qualifications</label>
                            <div id="profQualificationContainer"></div>
                            <button type="button" class="btn btn-outline-sec btn-sm mt-2" onclick="addSimpleField('profQualificationContainer', 'qualifications[]', 'Qualification')">
                                <i class="fa-solid fa-plus me-1"></i>Tambah
                            </button>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Job Description</label>
                            <div id="profDescriptionContainer"></div>
                            <button type="button" class="btn btn-outline-sec btn-sm mt-2" onclick="addSimpleField('profDescriptionContainer', 'descriptions[]', 'Job Description')">
                                <i class="fa-solid fa-plus me-1"></i>Tambah
                            </button>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Compensation & Benefit</label>
                            <div id="profCompensationContainer"></div>
                            <button type="button" class="btn btn-outline-sec btn-sm mt-2" onclick="addSimpleField('profCompensationContainer', 'compensation_benefit[]', 'Compensation & Benefit')">
                                <i class="fa-solid fa-plus me-1"></i>Tambah
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-sec" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-pri" id="btnProfileSubmit"><i class="fa-solid fa-save me-1"></i>Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title" id="modalDetailTitle"><i class="fa-solid fa-file-lines me-2"></i>Detail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailContent">
                    <div id="detailSkeleton"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDelete" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title"><i class="fa-solid fa-trash me-2"></i>Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="deleteMessage" class="mb-0">Yakin ingin menghapus data ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-sec" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-outline-danger-custom" id="btnDeleteConfirm"><i class="fa-solid fa-trash me-1"></i>Hapus</button>
                </div>
            </div>
        </div>
    </div>
    @php
        $orgStructuresData = $orgStructures->map(function ($o) {
            return [
                'id' => $o->id,
                'jabatan' => $o->jabatan,
                'divisi' => $o->divisi,
                'karyawans' => $o->karyawans->map(function ($k) {
                    return [
                        'id' => $k->id,
                        'nama_lengkap' => $k->nama_lengkap,
                    ];
                })->values(),
            ];
        })->values();
    @endphp

    <script>
        const orgStructures = {{ Illuminate\Support\Js::from($orgStructuresData) }};

        let deleteId = null;
        let deleteType = null;
        let isProfileUpdate = false;

        function skeletonDetail() {
            return `
            <div class="row g-2 mb-4">
                <div class="col-md-6"><div class="skeleton" style="height:64px;border-radius:8px;"></div></div>
                <div class="col-md-6"><div class="skeleton" style="height:64px;border-radius:8px;"></div></div>
                <div class="col-12"><div class="skeleton" style="height:64px;border-radius:8px;"></div></div>
            </div>
            <div class="skeleton mb-3" style="height:18px;width:140px;"></div>
            <div class="skeleton mb-4" style="height:60px;width:100%;"></div>
            <div class="skeleton mb-3" style="height:18px;width:160px;"></div>
            <div class="row g-3 mb-3">
                <div class="col-md-6"><div class="skeleton" style="height:64px;border-radius:8px;"></div></div>
                <div class="col-md-6"><div class="skeleton" style="height:64px;border-radius:8px;"></div></div>
                <div class="col-md-6"><div class="skeleton" style="height:64px;border-radius:8px;"></div></div>
                <div class="col-md-6"><div class="skeleton" style="height:64px;border-radius:8px;"></div></div>
                <div class="col-12"><div class="skeleton" style="height:80px;border-radius:8px;"></div></div>
            </div>
            <div class="skeleton mb-3" style="height:18px;width:200px;"></div>
            <div class="skeleton mb-2" style="height:90px;border-radius:8px;"></div>
            <div class="skeleton mb-2" style="height:90px;border-radius:8px;"></div>
            <div class="skeleton" style="height:90px;border-radius:8px;"></div>`;
        }

        function skeletonModalForm() {
            return `
            <div class="skeleton mb-3" style="height:16px;width:40%;"></div>
            <div class="skeleton mb-4" style="height:40px;width:100%;border-radius:8px;"></div>
            <div class="skeleton mb-3" style="height:16px;width:30%;"></div>
            <div class="skeleton mb-4" style="height:80px;width:100%;border-radius:8px;"></div>
            <div class="row g-3 mb-3">
                <div class="col-md-6"><div class="skeleton" style="height:70px;border-radius:8px;"></div></div>
                <div class="col-md-6"><div class="skeleton" style="height:70px;border-radius:8px;"></div></div>
            </div>
            <div class="skeleton mb-2" style="height:16px;width:35%;"></div>
            <div class="skeleton mb-2" style="height:40px;width:100%;border-radius:8px;"></div>
            <div class="skeleton" style="height:40px;width:100%;border-radius:8px;"></div>`;
        }

        function addSimpleField(containerId, fieldName, placeholder) {
            const container = document.getElementById(containerId);
            const idx = container.children.length;
            const row = document.createElement('div');
            row.className = 'detail-row mb-2';
            row.innerHTML = `
                <input type="text" name="${fieldName}" class="form-control" placeholder="${placeholder} ${idx + 1}">
                <button type="button" class="btn btn-outline-danger-custom btn-sm" onclick="this.parentElement.remove()">
                    <i class="fa-solid fa-xmark"></i>
                </button>`;
            container.appendChild(row);
        }

        function populateSimpleField(containerId, fieldName, dataArray, placeholder) {
            const container = document.getElementById(containerId);
            container.innerHTML = '';
            if (dataArray && dataArray.length > 0) {
                dataArray.forEach((item, i) => {
                    const row = document.createElement('div');
                    row.className = 'detail-row mb-2';
                    row.innerHTML = `
                        <input type="text" name="${fieldName}" class="form-control" value="${escapeHtml(item)}" placeholder="${placeholder} ${i + 1}">
                        <button type="button" class="btn btn-outline-danger-custom btn-sm" onclick="this.parentElement.remove()">
                            <i class="fa-solid fa-xmark"></i>
                        </button>`;
                    container.appendChild(row);
                });
            }
        }

        function addParentField(containerId, fieldName, parentPlaceholder, detailPlaceholder) {
            const container = document.getElementById(containerId);
            const parentIndex = container.children.length;
            const card = document.createElement('div');
            card.className = 'parent-card';
            card.innerHTML = `
                <div class="parent-header">
                    <input type="text" name="${fieldName}[${parentIndex}][name]" class="form-control" placeholder="${parentPlaceholder}">
                    <button type="button" class="btn btn-outline-danger-custom" onclick="this.closest('.parent-card').remove()">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
                <div class="details-container" data-field-name="${fieldName}" data-parent-index="${parentIndex}" data-detail-placeholder="${detailPlaceholder}"></div>
                <button type="button" class="btn btn-outline-sec btn-sm mt-2" onclick="addDetailField(this)">
                    <i class="fa-solid fa-plus me-1"></i>Tambah ${detailPlaceholder}
                </button>`;
            container.appendChild(card);
        }

        function addDetailField(btn) {
            const detailsContainer = btn.previousElementSibling;
            const fieldName = detailsContainer.dataset.fieldName;
            const parentIndex = detailsContainer.dataset.parentIndex;
            const detailPlaceholder = detailsContainer.dataset.detailPlaceholder;
            const detailIndex = detailsContainer.children.length;
            const row = document.createElement('div');
            row.className = 'detail-row';
            row.innerHTML = `
                <span class="text-muted small" style="min-width:20px">${detailIndex + 1}.</span>
                <input type="text" name="${fieldName}[${parentIndex}][details][]" class="form-control" placeholder="${detailPlaceholder} ${detailIndex + 1}">
                <button type="button" class="btn btn-outline-danger-custom btn-sm" onclick="this.parentElement.remove()">
                    <i class="fa-solid fa-xmark"></i>
                </button>`;
            detailsContainer.appendChild(row);
        }

        function populateParentField(containerId, fieldName, dataArray, parentPlaceholder, detailPlaceholder) {
            const container = document.getElementById(containerId);
            container.innerHTML = '';
            if (dataArray && dataArray.length > 0) {
                dataArray.forEach((item, parentIndex) => {
                    const card = document.createElement('div');
                    card.className = 'parent-card';
                    card.innerHTML = `
                        <div class="parent-header">
                            <input type="text" name="${fieldName}[${parentIndex}][name]" class="form-control" value="${escapeHtml(item.name || '')}" placeholder="${parentPlaceholder}">
                            <button type="button" class="btn btn-outline-danger-custom" onclick="this.closest('.parent-card').remove()">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                        <div class="details-container" data-field-name="${fieldName}" data-parent-index="${parentIndex}" data-detail-placeholder="${detailPlaceholder}"></div>
                        <button type="button" class="btn btn-outline-sec btn-sm mt-2" onclick="addDetailField(this)">
                            <i class="fa-solid fa-plus me-1"></i>Tambah ${detailPlaceholder}
                        </button>`;
                    container.appendChild(card);
                    const detailsContainer = card.querySelector('.details-container');
                    if (item.details && item.details.length > 0) {
                        item.details.forEach((detail, detailIndex) => {
                            const row = document.createElement('div');
                            row.className = 'detail-row';
                            row.innerHTML = `
                                <span class="text-muted small" style="min-width:20px">${detailIndex + 1}.</span>
                                <input type="text" name="${fieldName}[${parentIndex}][details][]" class="form-control" value="${escapeHtml(detail)}" placeholder="${detailPlaceholder} ${detailIndex + 1}">
                                <button type="button" class="btn btn-outline-danger-custom btn-sm" onclick="this.parentElement.remove()">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>`;
                            detailsContainer.appendChild(row);
                        });
                    }
                });
            }
        }

        function escapeHtml(text) {
            if (!text) return '';
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return String(text).replace(/[&<>"']/g, m => map[m]);
        }

        function showAlert(containerId, type, message) {
            const alertContainer = document.getElementById(containerId);
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const icon = type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation';
            alertContainer.innerHTML = `<div class="alert alert-custom ${alertClass} alert-dismissible fade show"><i class="fa-solid ${icon} me-2"></i>${message}<button type="button" class="btn-close" data-bs-dismiss="alert" style="font-size:.65rem"></button></div>`;
            setTimeout(() => { alertContainer.innerHTML = ''; }, 5000);
        }

        function renderHierarchicalList(title, dataArray, icon = 'fa-list-check') {
            let html = `<h6 class="section-title mt-4"><i class="fa-solid ${icon}"></i>${title}</h6>`;
            if (dataArray && dataArray.length > 0) {
                dataArray.forEach((item, index) => {
                    html += `<div class="detail-block">`;
                    html += `<div class="detail-block-title">${index + 1}. ${escapeHtml(item.name || '-')}</div>`;
                    if (item.details && item.details.length > 0) {
                        html += `<ul>`;
                        item.details.forEach(d => html += `<li>${escapeHtml(d)}</li>`);
                        html += `</ul>`;
                    } else {
                        html += `<div class="empty-text" style="margin-left:18px">Tidak ada detail</div>`;
                    }
                    html += `</div>`;
                });
            } else {
                html += '<p class="empty-text">-</p>';
            }
            return html;
        }

        function openModalCreateJobDesk() {
            document.getElementById('modalJobDeskTitle').innerHTML = '<i class="fa-solid fa-clipboard-list me-2"></i>Tambah Job Desk';
            document.getElementById('formJobDesk').reset();
            document.getElementById('editJobDeskId').value = '';
            document.getElementById('kompetensiContainer').innerHTML = '';
            document.getElementById('tugasContainer').innerHTML = '';
            document.getElementById('wewenangContainer').innerHTML = '';
            document.getElementById('btnSubmitJobDesk').innerHTML = '<i class="fa-solid fa-save me-1"></i>Simpan';
            new bootstrap.Modal(document.getElementById('modalJobDesk')).show();
        }

        function openModalEditJobDesk(id) {
            const modal = new bootstrap.Modal(document.getElementById('modalJobDesk'));
            document.getElementById('modalJobDeskTitle').innerHTML = '<i class="fa-solid fa-pen me-2"></i>Edit Job Desk';
            document.getElementById('formJobDesk').reset();
            document.getElementById('editJobDeskId').value = id;
            document.getElementById('kompetensiContainer').innerHTML = skeletonModalForm();
            document.getElementById('tugasContainer').innerHTML = '';
            document.getElementById('wewenangContainer').innerHTML = '';
            document.getElementById('btnSubmitJobDesk').innerHTML = '<i class="fa-solid fa-save me-1"></i>Update';
            modal.show();

            fetch(`/HR-dashboard/job-desk/api/${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        document.getElementById('selectOrgJobDesk').value = data.id_org;
                        document.getElementById('fungsiUtama').value = data.fungsi_utama || '';
                        document.querySelector('#formJobDesk [name="tujuan_jabatan"]').value = data.tujuan_jabatan || '';
                        document.querySelector('#formJobDesk [name="kualifikasi_pendidikan"]').value = data.kualifikasi_pendidikan || '';
                        document.querySelector('#formJobDesk [name="pengalaman_kerja"]').value = data.pengalaman_kerja || '';
                        document.querySelector('#formJobDesk [name="karakteristik_pribadi"]').value = data.karakteristik_pribadi || '';
                        populateSimpleField('kompetensiContainer', 'kompetensi[]', data.kompetensi, 'Kompetensi');
                        populateParentField('tugasContainer', 'tugas_tanggung_jawab', data.tugas_tanggung_jawab, 'Tugas Utama', 'Detail Tugas');
                        populateParentField('wewenangContainer', 'wewenang', data.wewenang, 'Wewenang Utama', 'Detail Wewenang');
                    }
                })
                .catch(() => {
                    document.getElementById('kompetensiContainer').innerHTML = '';
                    showAlert('alertContainerJobDesk', 'error', 'Gagal memuat data Job Desk');
                });
        }

        function openModalDetailJobDesk(id) {
            document.getElementById('modalDetailTitle').innerHTML = '<i class="fa-solid fa-file-lines me-2"></i>Detail Job Desk';
            document.getElementById('detailContent').innerHTML = skeletonDetail();
            new bootstrap.Modal(document.getElementById('modalDetail')).show();

            fetch(`/HR-dashboard/job-desk/api/${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        const org = orgStructures.find(o => o.id == data.id_org);
                        const karyawanList = org && org.karyawans ? org.karyawans.map(k => k.nama_lengkap).join(', ') : '—';
                        let kompetensiHtml = '';
                        if (data.kompetensi && data.kompetensi.length > 0) {
                            data.kompetensi.forEach(k => kompetensiHtml += `<span class="kompetensi-tag">${escapeHtml(k)}</span>`);
                        } else {
                            kompetensiHtml = '<span class="empty-text">-</span>';
                        }
                        const html = `
                            <div class="row g-2 mb-4">
                                <div class="col-md-6"><div class="info-card-mini"><div class="label">Jabatan</div><div class="value">${org ? escapeHtml(org.jabatan) : '—'}</div></div></div>
                                <div class="col-md-6"><div class="info-card-mini"><div class="label">Divisi</div><div class="value">${org ? escapeHtml(org.divisi || '—') : '—'}</div></div></div>
                                <div class="col-12"><div class="info-card-mini"><div class="label">Karyawan</div><div class="value">${escapeHtml(karyawanList)}</div></div></div>
                            </div>
                            <h6 class="section-title"><i class="fa-solid fa-bullseye"></i>Fungsi Utama</h6>
                            <p style="color:var(--gray-700);line-height:1.7;font-size:.9rem">${data.fungsi_utama ? escapeHtml(data.fungsi_utama) : '<span class="empty-text">Belum diisi</span>'}</p>
                            <h6 class="section-title"><i class="fa-solid fa-user-tie"></i>Spesifikasi Jabatan</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6"><div class="info-card-mini"><div class="label">Tujuan</div><div class="value" style="font-size:.85rem">${escapeHtml(data.tujuan_jabatan || '-')}</div></div></div>
                                <div class="col-md-6"><div class="info-card-mini"><div class="label">Pendidikan</div><div class="value" style="font-size:.85rem">${escapeHtml(data.kualifikasi_pendidikan || '-')}</div></div></div>
                                <div class="col-md-6"><div class="info-card-mini"><div class="label">Pengalaman</div><div class="value" style="font-size:.85rem">${escapeHtml(data.pengalaman_kerja || '-')}</div></div></div>
                                <div class="col-md-6"><div class="info-card-mini"><div class="label">Karakteristik</div><div class="value" style="font-size:.85rem">${escapeHtml(data.karakteristik_pribadi || '-')}</div></div></div>
                                <div class="col-12"><div class="info-card-mini"><div class="label">Kompetensi</div><div class="value mt-1">${kompetensiHtml}</div></div></div>
                            </div>
                            ${renderHierarchicalList('Tugas dan Tanggung Jawab', data.tugas_tanggung_jawab, 'fa-list-check')}
                            ${renderHierarchicalList('Wewenang', data.wewenang, 'fa-gavel')}
                        `;
                        document.getElementById('detailContent').innerHTML = html;
                    }
                })
                .catch(() => {
                    document.getElementById('detailContent').innerHTML = '<div class="empty-state"><p>Gagal memuat data</p></div>';
                });
        }

        function confirmDeleteJobDesk(id) {
            deleteId = id;
            deleteType = 'jobdesk';
            document.getElementById('deleteMessage').textContent = 'Yakin ingin menghapus Job Desk ini? Tindakan tidak bisa dibatalkan.';
            new bootstrap.Modal(document.getElementById('modalDelete')).show();
        }

        document.getElementById('formJobDesk').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const editId = document.getElementById('editJobDeskId').value;
            const url = editId ? `/HR-dashboard/job-desk/${editId}` : '/HR-dashboard/job-desk/store';
            if (editId) formData.append('_method', 'PUT');
            const btn = document.getElementById('btnSubmitJobDesk');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Menyimpan...';

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalJobDesk')).hide();
                    showAlert('alertContainerJobDesk', 'success', data.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    let errorMsg = data.message || 'Terjadi kesalahan';
                    if (data.errors) errorMsg += '<br>' + Object.values(data.errors).flat().join('<br>');
                    showAlert('alertContainerJobDesk', 'error', errorMsg);
                }
            })
            .catch(error => showAlert('alertContainerJobDesk', 'error', 'Gagal menyimpan data: ' + error.message))
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = editId ? '<i class="fa-solid fa-save me-1"></i>Update' : '<i class="fa-solid fa-save me-1"></i>Simpan';
            });
        });

        function openModalCreateSop() {
            document.getElementById('modalSopTitle').innerHTML = '<i class="fa-solid fa-file-contract me-2"></i>Tambah SOP';
            document.getElementById('formSop').reset();
            document.getElementById('editSopId').value = '';
            document.getElementById('sopContainer').innerHTML = '';
            document.getElementById('btnSubmitSop').innerHTML = '<i class="fa-solid fa-save me-1"></i>Simpan';
            new bootstrap.Modal(document.getElementById('modalSop')).show();
        }

        function openModalEditSop(id) {
            const modal = new bootstrap.Modal(document.getElementById('modalSop'));
            document.getElementById('modalSopTitle').innerHTML = '<i class="fa-solid fa-pen me-2"></i>Edit SOP';
            document.getElementById('formSop').reset();
            document.getElementById('editSopId').value = id;
            document.getElementById('sopContainer').innerHTML = skeletonModalForm();
            document.getElementById('btnSubmitSop').innerHTML = '<i class="fa-solid fa-save me-1"></i>Update';
            modal.show();

            fetch(`/HR-dashboard/sop/api/${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        document.getElementById('selectOrgSop').value = data.id_org;
                        populateParentField('sopContainer', 'sop', data.sop, 'SOP Utama', 'Detail SOP');
                    }
                })
                .catch(() => {
                    document.getElementById('sopContainer').innerHTML = '';
                    showAlert('alertContainerSop', 'error', 'Gagal memuat data SOP');
                });
        }

        function openModalDetailSop(id) {
            document.getElementById('modalDetailTitle').innerHTML = '<i class="fa-solid fa-file-lines me-2"></i>Detail SOP';
            document.getElementById('detailContent').innerHTML = skeletonDetail();
            new bootstrap.Modal(document.getElementById('modalDetail')).show();

            fetch(`/HR-dashboard/sop/api/${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        const org = orgStructures.find(o => o.id == data.id_org);
                        const karyawanList = org && org.karyawans ? org.karyawans.map(k => k.nama_lengkap).join(', ') : '—';
                        const html = `
                            <div class="row g-2 mb-4">
                                <div class="col-md-6"><div class="info-card-mini"><div class="label">Jabatan</div><div class="value">${org ? escapeHtml(org.jabatan) : '—'}</div></div></div>
                                <div class="col-md-6"><div class="info-card-mini"><div class="label">Divisi</div><div class="value">${org ? escapeHtml(org.divisi || '—') : '—'}</div></div></div>
                                <div class="col-12"><div class="info-card-mini"><div class="label">Karyawan</div><div class="value">${escapeHtml(karyawanList)}</div></div></div>
                            </div>
                            ${renderHierarchicalList('Standard Operating Procedure (SOP)', data.sop, 'fa-file-contract')}
                        `;
                        document.getElementById('detailContent').innerHTML = html;
                    }
                })
                .catch(() => {
                    document.getElementById('detailContent').innerHTML = '<div class="empty-state"><p>Gagal memuat data</p></div>';
                });
        }

        function confirmDeleteSop(id) {
            deleteId = id;
            deleteType = 'sop';
            document.getElementById('deleteMessage').textContent = 'Yakin ingin menghapus SOP ini? Tindakan tidak bisa dibatalkan.';
            new bootstrap.Modal(document.getElementById('modalDelete')).show();
        }

        document.getElementById('formSop').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const editId = document.getElementById('editSopId').value;
            const url = editId ? `/HR-dashboard/sop/${editId}` : '/HR-dashboard/sop/store';
            if (editId) formData.append('_method', 'PUT');
            const btn = document.getElementById('btnSubmitSop');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Menyimpan...';

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalSop')).hide();
                    showAlert('alertContainerSop', 'success', data.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    let errorMsg = data.message || 'Terjadi kesalahan';
                    if (data.errors) errorMsg += '<br>' + Object.values(data.errors).flat().join('<br>');
                    showAlert('alertContainerSop', 'error', errorMsg);
                }
            })
            .catch(error => showAlert('alertContainerSop', 'error', 'Gagal menyimpan data: ' + error.message))
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = editId ? '<i class="fa-solid fa-save me-1"></i>Update' : '<i class="fa-solid fa-save me-1"></i>Simpan';
            });
        });

        function openModalProfile(karyawanId, namaKaryawan) {
            document.getElementById('formProfile').reset();
            document.getElementById('profileKaryawanId').value = karyawanId;
            document.getElementById('profQualificationContainer').innerHTML = skeletonModalForm();
            document.getElementById('profDescriptionContainer').innerHTML = '';
            document.getElementById('profCompensationContainer').innerHTML = '';
            document.getElementById('modalProfileTitle').innerHTML = `<i class="fa-solid fa-user-tie me-2"></i>Job Profile — ${escapeHtml(namaKaryawan)}`;
            document.getElementById('btnProfileSubmit').innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Memuat...';
            document.getElementById('btnProfileSubmit').disabled = true;
            new bootstrap.Modal(document.getElementById('modalProfile')).show();

            fetch(`/HR-dashboard/karyawan-profile/${karyawanId}`)
                .then(r => {
                    if (r.status === 204 || r.status === 404) return null;
                    return r.json();
                })
                .then(data => {
                    document.getElementById('profQualificationContainer').innerHTML = '';
                    document.getElementById('profDescriptionContainer').innerHTML = '';
                    document.getElementById('profCompensationContainer').innerHTML = '';
                    document.getElementById('btnProfileSubmit').disabled = false;

                    if (data && data.id) {
                        isProfileUpdate = true;
                        document.getElementById('modalProfileTitle').innerHTML = `<i class="fa-solid fa-pen me-2"></i>Edit Job Profile — ${escapeHtml(namaKaryawan)}`;
                        document.getElementById('btnProfileSubmit').innerHTML = '<i class="fa-solid fa-save me-1"></i>Update';
                        populateSimpleField('profQualificationContainer', 'qualifications[]', data.qualifications || [], 'Qualification');
                        populateSimpleField('profDescriptionContainer', 'descriptions[]', data.descriptions || [], 'Job Description');
                        populateSimpleField('profCompensationContainer', 'compensation_benefit[]', data.compensation_benefit || [], 'Compensation & Benefit');
                    } else {
                        isProfileUpdate = false;
                        document.getElementById('modalProfileTitle').innerHTML = `<i class="fa-solid fa-plus me-2"></i>Tambah Job Profile — ${escapeHtml(namaKaryawan)}`;
                        document.getElementById('btnProfileSubmit').innerHTML = '<i class="fa-solid fa-save me-1"></i>Simpan';
                    }
                })
                .catch(err => {
                    console.error('Error fetching profile:', err);
                    isProfileUpdate = false;
                    document.getElementById('profQualificationContainer').innerHTML = '';
                    document.getElementById('btnProfileSubmit').disabled = false;
                    document.getElementById('modalProfileTitle').innerHTML = `<i class="fa-solid fa-plus me-2"></i>Tambah Job Profile — ${escapeHtml(namaKaryawan)}`;
                    document.getElementById('btnProfileSubmit').innerHTML = '<i class="fa-solid fa-save me-1"></i>Simpan';
                });
        }

        document.getElementById('formProfile').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const karyawanId = document.getElementById('profileKaryawanId').value;
            if (!formData.has('karyawan_id')) formData.append('karyawan_id', karyawanId);
            const url = isProfileUpdate ? `/HR-dashboard/karyawan-profile/${karyawanId}` : '/HR-dashboard/karyawan-profile';
            if (isProfileUpdate) formData.append('_method', 'PUT');
            const btn = document.getElementById('btnProfileSubmit');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Menyimpan...';

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalProfile')).hide();
                    showAlert('alertContainerProfile', 'success', data.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    let errorMsg = data.message || 'Terjadi kesalahan';
                    if (data.errors) errorMsg += '<br>' + Object.values(data.errors).flat().join('<br>');
                    showAlert('alertContainerProfile', 'error', errorMsg);
                }
            })
            .catch(err => showAlert('alertContainerProfile', 'error', 'Error: ' + err.message))
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = isProfileUpdate ? '<i class="fa-solid fa-save me-1"></i>Update' : '<i class="fa-solid fa-save me-1"></i>Simpan';
            });
        });

        function confirmDeleteProfile(karyawanId) {
            if (!confirm('Yakin ingin menghapus Job Profile karyawan ini?')) return;
            fetch(`/HR-dashboard/karyawan-profile/${karyawanId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showAlert('alertContainerProfile', 'success', data.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert('alertContainerProfile', 'error', data.message);
                }
            });
        }

        document.getElementById('btnDeleteConfirm').addEventListener('click', function() {
            if (!deleteId) return;
            let url = '', containerAlert = '';
            if (deleteType === 'jobdesk') {
                url = `/HR-dashboard/job-desk/${deleteId}`;
                containerAlert = 'alertContainerJobDesk';
            } else if (deleteType === 'sop') {
                url = `/HR-dashboard/sop/${deleteId}`;
                containerAlert = 'alertContainerSop';
            }
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Menghapus...';

            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalDelete')).hide();
                    showAlert(containerAlert, 'success', data.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert(containerAlert, 'error', data.message);
                }
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-trash me-1"></i>Hapus';
            });
        });
    </script>
@endsection