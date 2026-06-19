@extends('layout_HR.app')
@section('content_HR')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">

    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #333;
            background: #fafafa;
        }

        .nav-tabs {
            border-bottom: 1px solid #e0e0e0;
        }

        .nav-tabs .nav-link {
            color: #666;
            font-weight: 500;
            border: none;
            border-bottom: 2px solid transparent;
            padding: 12px 24px;
            transition: all 0.2s;
        }

        .nav-tabs .nav-link:hover {
            color: #333;
        }

        .nav-tabs .nav-link.active {
            color: #000;
            border-bottom: 2px solid #000;
            background: transparent;
        }

        .card {
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            box-shadow: none;
        }

        .card-header {
            background: #fff;
            border-bottom: 1px solid #e0e0e0;
            padding: 14px 18px;
        }

        .card-header h5 {
            font-size: 0.95rem;
            font-weight: 600;
            margin: 0;
            color: #333;
        }

        .accordion-item {
            border: 1px solid #e0e0e0;
            margin-bottom: 6px;
            border-radius: 6px;
            overflow: hidden;
        }

        .accordion-button {
            background: #fff;
            color: #333;
            font-weight: 500;
            font-size: 0.95rem;
            padding: 12px 16px;
            box-shadow: none !important;
        }

        .accordion-button:not(.collapsed) {
            background: #f7f7f7;
            color: #000;
        }

        .accordion-button::after {
            filter: none;
        }

        .accordion-body {
            padding: 16px;
            background: #fff;
        }

        .karyawan-card {
            border: 1px solid #eee;
            border-radius: 6px;
            padding: 14px 16px;
            margin-bottom: 10px;
            background: #fcfcfc;
        }

        .karyawan-card:last-child {
            margin-bottom: 0;
        }

        .karyawan-name {
            font-weight: 600;
            color: #222;
            font-size: 0.95rem;
            margin-bottom: 8px;
        }

        .profile-section {
            margin-top: 10px;
        }

        .profile-section-title {
            font-size: 0.8rem;
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .profile-list {
            list-style: none;
            padding: 0;
            margin: 0 0 12px 0;
        }

        .profile-list li {
            padding: 4px 0 4px 16px;
            position: relative;
            color: #444;
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .profile-list li::before {
            content: "–";
            position: absolute;
            left: 0;
            color: #999;
        }

        .empty-text {
            color: #999;
            font-size: 0.85rem;
            font-style: italic;
        }

        .btn {
            font-weight: 500;
            border-radius: 5px;
            padding: 6px 14px;
            font-size: 0.85rem;
        }

        .btn-primary {
            background: #222;
            border-color: #222;
            color: #fff;
        }

        .btn-primary:hover {
            background: #000;
            border-color: #000;
            color: #fff;
        }

        .btn-outline-secondary {
            color: #555;
            border-color: #ccc;
        }

        .btn-outline-secondary:hover {
            background: #f5f5f5;
            color: #222;
            border-color: #999;
        }

        .btn-outline-danger {
            color: #c0392b;
            border-color: #e0b4b0;
            background: transparent;
        }

        .btn-outline-danger:hover {
            background: #c0392b;
            color: #fff;
            border-color: #c0392b;
        }

        .btn-sm {
            padding: 4px 10px;
            font-size: 0.8rem;
        }

        .form-control,
        .form-select {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 8px 12px;
            font-size: 0.9rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #333;
            box-shadow: 0 0 0 0.15rem rgba(0, 0, 0, 0.05);
        }

        .table {
            color: #333;
            font-size: 0.9rem;
        }

        .table thead th {
            background: #f7f7f7;
            border-bottom: 1px solid #e0e0e0;
            font-weight: 600;
            font-size: 0.8rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .table tbody tr:hover {
            background: #fafafa;
        }

        .badge {
            font-weight: 500;
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 0.75rem;
        }

        .bg-primary {
            background: #333 !important;
            color: #fff !important;
        }

        .modal-content {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
        }

        .modal-header {
            border-bottom: 1px solid #e0e0e0;
            padding: 14px 20px;
        }

        .modal-title {
            font-size: 1rem;
            font-weight: 600;
            color: #222;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            border-top: 1px solid #e0e0e0;
            padding: 12px 20px;
        }

        .alert {
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            padding: 10px 14px;
            font-size: 0.9rem;
        }

        .alert-success {
            background: #f5f9f5;
            color: #2d5a2d;
            border-color: #d4e6d4;
        }

        .alert-danger {
            background: #fdf5f5;
            color: #5a2d2d;
            border-color: #e6d4d4;
        }

        .alert-info {
            background: #f5f7fa;
            color: #444;
            border-color: #d4dbe6;
        }

        .parent-card {
            background: #f9f9f9;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            padding: 12px;
            margin-bottom: 10px;
        }

        .parent-header {
            display: flex;
            gap: 8px;
            margin-bottom: 8px;
        }

        .parent-header input {
            flex: 1;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .details-container {
            margin-left: 16px;
            padding-left: 12px;
            border-left: 2px solid #ddd;
        }

        .detail-row {
            display: flex;
            gap: 8px;
            margin-bottom: 6px;
        }

        .detail-row input {
            flex: 1;
        }

        .modal-dialog-scrollable {
            display: flex;
            align-items: center;
            min-height: calc(100vh - 1rem);
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

        .modal-dialog-scrollable .modal-footer {
            flex-shrink: 0;
        }

        .modal-dialog-scrollable form {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
            overflow: hidden;
        }

        .modal-dialog-scrollable form .modal-body {
            overflow-y: auto !important;
        }

        .text-muted {
            color: #999 !important;
        }

        .count-badge {
            background: #f0f0f0;
            color: #555;
            font-size: 0.75rem;
            padding: 2px 8px;
            border-radius: 10px;
            margin-left: 8px;
        }
    </style>

    <div class="container-fluid py-4">
        {{-- ==================== TABS ==================== --}}
        <ul class="nav nav-tabs mb-4" id="mainTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="jobdesk-tab" data-bs-toggle="tab" data-bs-target="#jobdesk-pane"
                    type="button">Job Desk</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="sop-tab" data-bs-toggle="tab" data-bs-target="#sop-pane"
                    type="button">SOP</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-pane"
                    type="button">Job Profile</button>
            </li>
        </ul>

        <div class="tab-content" id="mainTabsContent">
            {{-- ==================== TAB JOB DESK ==================== --}}
            <div class="tab-pane fade show active" id="jobdesk-pane" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 fw-bold">Job Desk</h5>
                    <button class="btn btn-primary btn-sm" onclick="openModalCreateJobDesk()">
                        <i class="fa-solid fa-plus me-1"></i>Tambah Job Desk
                    </button>
                </div>
                <div id="alertContainerJobDesk"></div>
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th width="40">No</th>
                                        <th>Jabatan</th>
                                        <th>Karyawan</th>
                                        <th>Fungsi Utama</th>
                                        <th width="80">Tugas</th>
                                        <th width="80">Wewenang</th>
                                        <th width="90" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $no = 0; @endphp
                                    @forelse($jobDesks as $jobDesk)
                                        @if (!empty($jobDesk->fungsi_utama) || !empty($jobDesk->tugas_tanggung_jawab) || !empty($jobDesk->wewenang))
                                            @php $no++; @endphp
                                            <tr>
                                                <td>{{ $no }}</td>
                                                <td>
                                                    <strong>{{ $jobDesk->orgStructure->jabatan ?? '-' }}</strong>
                                                    @if ($jobDesk->orgStructure->divisi ?? false)
                                                        <br><small
                                                            class="text-muted">{{ $jobDesk->orgStructure->divisi }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($jobDesk->orgStructure && $jobDesk->orgStructure->karyawans->count() > 0)
                                                        @foreach ($jobDesk->orgStructure->karyawans as $k)
                                                            <span class="badge bg-primary">{{ $k->nama_lengkap }}</span>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>{{ Str::limit($jobDesk->fungsi_utama, 50) ?: '—' }}</td>
                                                <td>{{ count($jobDesk->tugas_tanggung_jawab ?? []) }}</td>
                                                <td>{{ count($jobDesk->wewenang ?? []) }}</td>
                                                <td class="text-center">
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                            type="button" data-bs-toggle="dropdown">Action</button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li><a class="dropdown-item" href="#"
                                                                    onclick="openModalDetailJobDesk({{ $jobDesk->id }}); return false;"><i
                                                                        class="fa-solid fa-eye me-2"></i>Detail</a></li>
                                                            <li><a class="dropdown-item" href="#"
                                                                    onclick="openModalEditJobDesk({{ $jobDesk->id }}); return false;"><i
                                                                        class="fa-solid fa-pen me-2"></i>Edit</a></li>
                                                            <li>
                                                                <hr class="dropdown-divider">
                                                            </li>
                                                            <li><a class="dropdown-item text-danger" href="#"
                                                                    onclick="confirmDeleteJobDesk({{ $jobDesk->id }}); return false;"><i
                                                                        class="fa-solid fa-trash me-2"></i>Hapus</a></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @empty
                                    @endforelse
                                    @if ($no === 0)
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">Belum ada data Job Desk
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ==================== TAB SOP ==================== --}}
            <div class="tab-pane fade" id="sop-pane" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 fw-bold">Standard Operating Procedure (SOP)</h5>
                    <button class="btn btn-primary btn-sm" onclick="openModalCreateSop()">
                        <i class="fa-solid fa-plus me-1"></i>Tambah SOP
                    </button>
                </div>
                <div id="alertContainerSop"></div>
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th width="40">No</th>
                                        <th>Jabatan</th>
                                        <th>Karyawan</th>
                                        <th width="80">Jumlah SOP</th>
                                        <th>SOP Utama</th>
                                        <th width="90" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sopDesks = $jobDesks
                                            ->filter(fn($jd) => !empty($jd->sop) && count($jd->sop) > 0)
                                            ->values();
                                    @endphp
                                    @forelse($sopDesks as $index => $jobDesk)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $jobDesk->orgStructure->jabatan ?? '-' }}</strong>
                                                @if ($jobDesk->orgStructure->divisi ?? false)
                                                    <br><small
                                                        class="text-muted">{{ $jobDesk->orgStructure->divisi }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($jobDesk->orgStructure && $jobDesk->orgStructure->karyawans->count() > 0)
                                                    @foreach ($jobDesk->orgStructure->karyawans as $k)
                                                        <span class="badge bg-primary">{{ $k->nama_lengkap }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td><span class="count-badge">{{ count($jobDesk->sop ?? []) }}</span></td>
                                            <td>{{ Str::limit($jobDesk->sop[0]['name'] ?? '', 50) ?: '—' }}</td>
                                            <td class="text-center">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                        type="button" data-bs-toggle="dropdown">Action</button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item" href="#"
                                                                onclick="openModalDetailSop({{ $jobDesk->id }}); return false;"><i
                                                                    class="fa-solid fa-eye me-2"></i>Detail</a></li>
                                                        <li><a class="dropdown-item" href="#"
                                                                onclick="openModalEditSop({{ $jobDesk->id }}); return false;"><i
                                                                    class="fa-solid fa-pen me-2"></i>Edit</a></li>
                                                        <li>
                                                            <hr class="dropdown-divider">
                                                        </li>
                                                        <li><a class="dropdown-item text-danger" href="#"
                                                                onclick="confirmDeleteSop({{ $jobDesk->id }}); return false;"><i
                                                                    class="fa-solid fa-trash me-2"></i>Hapus</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">Belum ada data SOP</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ==================== TAB JOB PROFILE ==================== --}}
            <div class="tab-pane fade" id="profile-pane" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 fw-bold">Job Profile</h5>
                </div>
                <div id="alertContainerProfile"></div>
                <div class="accordion" id="accordionProfile">
                    @forelse($orgStructures as $org)
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseOrg{{ $org->id }}">
                                    <span>{{ $org->jabatan }}</span>
                                    @if ($org->divisi)
                                        <span class="text-muted ms-2" style="font-weight:400; font-size:0.85rem;">—
                                            {{ $org->divisi }}</span>
                                    @endif
                                    <span class="count-badge ms-auto me-2">{{ $org->karyawans->count() }}</span>
                                </button>
                            </h2>
                            <div id="collapseOrg{{ $org->id }}" class="accordion-collapse collapse"
                                data-bs-parent="#accordionProfile">
                                <div class="accordion-body">
                                    @foreach ($org->karyawans as $karyawan)
                                        <div class="karyawan-card">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="karyawan-name">{{ $karyawan->nama_lengkap }}</div>
                                                <div class="d-flex gap-1">
                                                    <button class="btn btn-sm btn-primary"
                                                        onclick="openModalProfile({{ $karyawan->id }}, '{{ addslashes($karyawan->nama_lengkap) }}')">
                                                        <i
                                                            class="fa-solid fa-pen me-1"></i>{{ $karyawan->jobProfile ? 'Edit' : 'Tambah' }}
                                                    </button>
                                                    @if ($karyawan->jobProfile)
                                                        <button class="btn btn-sm btn-outline-danger"
                                                            onclick="confirmDeleteProfile({{ $karyawan->id }})">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>

                                            @if($karyawan->jobProfile)
                                                @php $data = $karyawan->jobProfile; $isPrivate = true; @endphp
                                                @include('HR.job_desk._profile_section')
                                            @else
                                                <div class="empty-text mt-1">Belum ada job profile</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">Belum ada data jabatan dengan karyawan.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== MODAL JOB DESK ==================== --}}
    <div class="modal fade" id="modalJobDesk" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalJobDeskTitle">Tambah Job Desk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formJobDesk">
                    @csrf
                    <input type="hidden" id="editJobDeskId" value="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Pilih Jabatan <span class="text-danger">*</span></label>
                            <select name="id_org" id="selectOrgJobDesk" class="form-select" required>
                                <option value="">— Pilih Jabatan —</option>
                            </select>
                        </div>

                        <hr>
                        <h6 class="fw-bold mb-3">Fungsi Utama</h6>
                        <div class="mb-3">
                            <textarea name="fungsi_utama" id="fungsiUtama" class="form-control" rows="3"
                                placeholder="Deskripsi fungsi utama jabatan..."></textarea>
                        </div>

                        <hr>
                        <h6 class="fw-bold mb-3">Spesifikasi Jabatan</h6>
                        <div class="mb-3"><label class="form-label">Tujuan Jabatan</label>
                            <textarea name="tujuan_jabatan" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="mb-3"><label class="form-label">Kualifikasi Pendidikan</label>
                            <textarea name="kualifikasi_pendidikan" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="mb-3"><label class="form-label">Pengalaman Kerja</label>
                            <textarea name="pengalaman_kerja" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kompetensi</label>
                            <div id="kompetensiContainer"></div>
                            <button type="button" class="btn btn-outline-secondary btn-sm mt-2"
                                onclick="addSimpleField('kompetensiContainer', 'kompetensi[]', 'Kompetensi')">
                                <i class="fa-solid fa-plus me-1"></i>Tambah Kompetensi
                            </button>
                        </div>
                        <div class="mb-3"><label class="form-label">Karakteristik Pribadi</label>
                            <textarea name="karakteristik_pribadi" class="form-control" rows="2"></textarea>
                        </div>

                        <hr>
                        <h6 class="fw-bold mb-3">Tugas dan Tanggung Jawab</h6>
                        <div id="tugasContainer"></div>
                        <button type="button" class="btn btn-outline-secondary btn-sm mt-2"
                            onclick="addParentField('tugasContainer', 'tugas_tanggung_jawab', 'Tugas Utama', 'Detail Tugas')">
                            <i class="fa-solid fa-plus me-1"></i>Tambah Tugas Utama
                        </button>

                        <hr>
                        <h6 class="fw-bold mb-3">Wewenang</h6>
                        <div id="wewenangContainer"></div>
                        <button type="button" class="btn btn-outline-secondary btn-sm mt-2"
                            onclick="addParentField('wewenangContainer', 'wewenang', 'Wewenang Utama', 'Detail Wewenang')">
                            <i class="fa-solid fa-plus me-1"></i>Tambah Wewenang Utama
                        </button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmitJobDesk">
                            <i class="fa-solid fa-save me-1"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ==================== MODAL SOP ==================== --}}
    <div class="modal fade" id="modalSop" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalSopTitle">Tambah SOP</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formSop">
                    @csrf
                    <input type="hidden" id="editSopId" value="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Pilih Jabatan <span class="text-danger">*</span></label>
                            <select name="id_org" id="selectOrgSop" class="form-select" required>
                                <option value="">— Pilih Jabatan —</option>
                            </select>
                        </div>

                        <div class="alert alert-info">
                            <i class="fa-solid fa-info-circle me-1"></i>
                            Setiap jabatan hanya memiliki 1 dokumen SOP. Tambahkan SOP utama beserta detail-detailnya di
                            bawah ini.
                        </div>

                        <hr>
                        <h6 class="fw-bold mb-3">Standard Operating Procedure (SOP)</h6>
                        <div id="sopContainer"></div>
                        <button type="button" class="btn btn-outline-secondary btn-sm mt-2"
                            onclick="addParentField('sopContainer', 'sop', 'SOP Utama', 'Detail SOP')">
                            <i class="fa-solid fa-plus me-1"></i>Tambah SOP Utama
                        </button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmitSop">
                            <i class="fa-solid fa-save me-1"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ==================== MODAL JOB PROFILE ==================== --}}
    <div class="modal fade" id="modalProfile" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalProfileTitle">Job Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formProfile">
                        @csrf
                        <input type="hidden" id="profileKaryawanId" name="karyawan_id" value="">
                        <div class="alert alert-info">Job Profile melekat pada karyawan individu, terlepas dari jabatan
                            yang diembannya.</div>

                        <h6 class="fw-bold mb-3">Qualification</h6>
                        <div id="profQualificationContainer"></div>
                        <button type="button" class="btn btn-outline-secondary btn-sm mt-2"
                            onclick="addSimpleField('profQualificationContainer', 'qualifications[]', 'Qualification')">
                            <i class="fa-solid fa-plus me-1"></i>Tambah Qualification
                        </button>

                        <hr class="my-4">

                        <h6 class="fw-bold mb-3">Job Description</h6>
                        <div id="profDescriptionContainer"></div>
                        <button type="button" class="btn btn-outline-secondary btn-sm mt-2"
                            onclick="addSimpleField('profDescriptionContainer', 'descriptions[]', 'Job Description')">
                            <i class="fa-solid fa-plus me-1"></i>Tambah Job Description
                        </button>

                        <hr class="my-4">

                        <h6 class="fw-bold mb-3">Compensation & Benefit</h6>
                        <div id="profCompensationContainer"></div>
                        <button type="button" class="btn btn-outline-secondary btn-sm mt-2"
                            onclick="addSimpleField('profCompensationContainer', 'compensation_benefit[]', 'Compensation & Benefit')">
                            <i class="fa-solid fa-plus me-1"></i>Tambah Compensation & Benefit
                        </button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="formProfile" class="btn btn-primary" id="btnProfileSubmit">
                        <i class="fa-solid fa-save me-1"></i>Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== MODAL DETAIL ==================== --}}
    <div class="modal fade" id="modalDetail" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetailTitle">Detail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailContent"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== MODAL DELETE ==================== --}}
    <div class="modal fade" id="modalDelete" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Konfirmasi Hapus</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0" id="deleteMessage">Yakin ingin menghapus data ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="btnDeleteConfirm">Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let deleteId = null;
        let deleteType = null;
        let orgStructures = [];

        document.addEventListener('DOMContentLoaded', function() {
            loadOrgStructures();
        });

        function loadOrgStructures() {
            fetch("{{ route('HR.structure.api.tree') }}")
                .then(response => response.json())
                .then(data => {
                    orgStructures = flattenTree(data.tree || []);
                    populateOrgSelect('selectOrgJobDesk');
                    populateOrgSelect('selectOrgSop');
                });
        }

        function flattenTree(items) {
            let result = [];
            items.forEach(item => {
                result.push(item);
                if (item.children) result = result.concat(flattenTree(item.children));
            });
            return result;
        }

        function populateOrgSelect(selectId) {
            const select = document.getElementById(selectId);
            if (!select) return;
            select.innerHTML = '<option value="">— Pilih Jabatan —</option>';
            orgStructures.forEach(org => {
                const option = document.createElement('option');
                option.value = org.id;
                option.textContent = org.jabatan + (org.divisi ? ' - ' + org.divisi : '');
                select.appendChild(option);
            });
        }

        // ============ UTILITY FUNCTIONS ============
        function addSimpleField(containerId, fieldName, placeholder) {
            const container = document.getElementById(containerId);
            const count = container.children.length + 1;
            const div = document.createElement('div');
            div.className = 'input-group mb-2';
            div.innerHTML = `
            <input type="text" name="${fieldName}" class="form-control" placeholder="${placeholder} ${count}">
            <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        `;
            container.appendChild(div);
        }

        function populateSimpleField(containerId, fieldName, dataArray, placeholder) {
            const container = document.getElementById(containerId);
            container.innerHTML = '';
            if (dataArray && dataArray.length > 0) {
                dataArray.forEach((item, index) => {
                    const div = document.createElement('div');
                    div.className = 'input-group mb-2';
                    div.innerHTML = `
                    <input type="text" name="${fieldName}" class="form-control" value="${escapeHtml(item)}" placeholder="${placeholder} ${index + 1}">
                    <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                `;
                    container.appendChild(div);
                });
            }
        }

        function addParentField(containerId, fieldName, parentPlaceholder, detailPlaceholder) {
            const container = document.getElementById(containerId);
            const parentIndex = container.querySelectorAll('.parent-card').length;

            const card = document.createElement('div');
            card.className = 'parent-card';
            card.innerHTML = `
            <div class="parent-header">
                <input type="text" name="${fieldName}[${parentIndex}][name]" class="form-control" placeholder="${parentPlaceholder}">
                <button type="button" class="btn btn-outline-danger" onclick="this.closest('.parent-card').remove()">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
            <div class="details-container" data-field-name="${fieldName}" data-parent-index="${parentIndex}" data-detail-placeholder="${detailPlaceholder}">
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm mt-2" onclick="addDetailField(this)">
                <i class="fa-solid fa-plus me-1"></i>Tambah ${detailPlaceholder}
            </button>
        `;
            container.appendChild(card);
            addDetailField(card.querySelector('button.btn-outline-secondary'));
        }

        function addDetailField(button) {
            const card = button.closest('.parent-card');
            const detailsContainer = card.querySelector('.details-container');
            const fieldName = detailsContainer.dataset.fieldName;
            const parentIndex = detailsContainer.dataset.parentIndex;
            const placeholder = detailsContainer.dataset.detailPlaceholder;
            const detailIndex = detailsContainer.querySelectorAll('.detail-row').length + 1;

            const row = document.createElement('div');
            row.className = 'detail-row';
            row.innerHTML = `
            <span class="align-self-center text-muted small">${detailIndex}.</span>
            <input type="text" name="${fieldName}[${parentIndex}][details][]" class="form-control" placeholder="${placeholder} ${detailIndex}">
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.parentElement.remove()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        `;
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
                        <button type="button" class="btn btn-outline-danger" onclick="this.closest('.parent-card').remove()">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                    <div class="details-container" data-field-name="${fieldName}" data-parent-index="${parentIndex}" data-detail-placeholder="${detailPlaceholder}">
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm mt-2" onclick="addDetailField(this)">
                        <i class="fa-solid fa-plus me-1"></i>Tambah ${detailPlaceholder}
                    </button>
                `;
                    container.appendChild(card);

                    const detailsContainer = card.querySelector('.details-container');
                    if (item.details && item.details.length > 0) {
                        item.details.forEach((detail, detailIndex) => {
                            const row = document.createElement('div');
                            row.className = 'detail-row';
                            row.innerHTML = `
                            <span class="align-self-center text-muted small">${detailIndex + 1}.</span>
                            <input type="text" name="${fieldName}[${parentIndex}][details][]" class="form-control" value="${escapeHtml(detail)}" placeholder="${detailPlaceholder} ${detailIndex + 1}">
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.parentElement.remove()">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        `;
                            detailsContainer.appendChild(row);
                        });
                    }
                });
            }
        }

        function escapeHtml(text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        function showAlert(containerId, type, message) {
            const alertContainer = document.getElementById(containerId);
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            alertContainer.innerHTML = `<div class="alert ${alertClass} alert-dismissible fade show">${message}</div>`;
            setTimeout(() => {
                alertContainer.innerHTML = '';
            }, 5000);
        }

        function renderHierarchicalList(title, dataArray) {
            let html = `<h6 class="fw-bold mt-4 mb-2">${title}</h6>`;
            if (dataArray && dataArray.length > 0) {
                dataArray.forEach((item, index) => {
                    html += `<div class="parent-card">`;
                    html += `<div class="fw-bold mb-2">${index + 1}. ${escapeHtml(item.name || '-')}</div>`;
                    if (item.details && item.details.length > 0) {
                        html += `<ul class="mb-0">`;
                        item.details.forEach(d => html += `<li>${escapeHtml(d)}</li>`);
                        html += `</ul>`;
                    } else {
                        html += `<div class="empty-text">Tidak ada detail</div>`;
                    }
                    html += `</div>`;
                });
            } else {
                html += '<p class="text-muted">-</p>';
            }
            return html;
        }

        // ============ JOB DESK FUNCTIONS ============
        function openModalCreateJobDesk() {
            document.getElementById('modalJobDeskTitle').innerHTML = 'Tambah Job Desk';
            document.getElementById('formJobDesk').reset();
            document.getElementById('editJobDeskId').value = '';
            document.getElementById('kompetensiContainer').innerHTML = '';
            document.getElementById('tugasContainer').innerHTML = '';
            document.getElementById('wewenangContainer').innerHTML = '';
            document.getElementById('btnSubmitJobDesk').innerHTML = '<i class="fa-solid fa-save me-1"></i>Simpan';
            new bootstrap.Modal(document.getElementById('modalJobDesk')).show();
        }

        function openModalEditJobDesk(id) {
            fetch(`/HR-dashboard/job-desk/api/${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        document.getElementById('modalJobDeskTitle').innerHTML = 'Edit Job Desk';
                        document.getElementById('editJobDeskId').value = id;
                        document.getElementById('selectOrgJobDesk').value = data.id_org;
                        document.getElementById('fungsiUtama').value = data.fungsi_utama || '';
                        document.querySelector('#formJobDesk [name="tujuan_jabatan"]').value = data.tujuan_jabatan ||
                            '';
                        document.querySelector('#formJobDesk [name="kualifikasi_pendidikan"]').value = data
                            .kualifikasi_pendidikan || '';
                        document.querySelector('#formJobDesk [name="pengalaman_kerja"]').value = data
                            .pengalaman_kerja || '';
                        document.querySelector('#formJobDesk [name="karakteristik_pribadi"]').value = data
                            .karakteristik_pribadi || '';

                        populateSimpleField('kompetensiContainer', 'kompetensi[]', data.kompetensi, 'Kompetensi');
                        populateParentField('tugasContainer', 'tugas_tanggung_jawab', data.tugas_tanggung_jawab,
                            'Tugas Utama', 'Detail Tugas');
                        populateParentField('wewenangContainer', 'wewenang', data.wewenang, 'Wewenang Utama',
                            'Detail Wewenang');

                        document.getElementById('btnSubmitJobDesk').innerHTML =
                            '<i class="fa-solid fa-save me-1"></i>Update';
                        new bootstrap.Modal(document.getElementById('modalJobDesk')).show();
                    }
                });
        }

        function openModalDetailJobDesk(id) {
            fetch(`/HR-dashboard/job-desk/api/${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        const org = orgStructures.find(o => o.id == data.id_org);
                        const karyawanList = org && org.karyawans ? org.karyawans.map(k => k.nama_lengkap).join(', ') :
                            '—';

                        let kompetensiHtml = '';
                        if (data.kompetensi && data.kompetensi.length > 0) {
                            kompetensiHtml = '<ul>';
                            data.kompetensi.forEach(k => kompetensiHtml += `<li>${escapeHtml(k)}</li>`);
                            kompetensiHtml += '</ul>';
                        } else {
                            kompetensiHtml = '<p class="text-muted">-</p>';
                        }

                        const html = `
                        <h5 class="fw-bold mb-1">${org ? org.jabatan : '—'}</h5>
                        <p class="text-muted">${org ? org.divisi : ''}</p>
                        <div class="card mb-3"><div class="card-body">
                            <h6 class="fw-bold">Karyawan</h6>
                            <p class="mb-0">${karyawanList}</p>
                        </div></div>
                        <hr>
                        <h6 class="fw-bold">Fungsi Utama</h6>
                        <p>${data.fungsi_utama || '-'}</p>

                        <h6 class="fw-bold mt-3">Spesifikasi Jabatan</h6>
                        <p><strong>Tujuan:</strong> ${data.tujuan_jabatan || '-'}</p>
                        <p><strong>Pendidikan:</strong> ${data.kualifikasi_pendidikan || '-'}</p>
                        <p><strong>Pengalaman:</strong> ${data.pengalaman_kerja || '-'}</p>
                        <p><strong>Karakteristik:</strong> ${data.karakteristik_pribadi || '-'}</p>
                        <p><strong>Kompetensi:</strong></p>${kompetensiHtml}

                        ${renderHierarchicalList('Tugas dan Tanggung Jawab', data.tugas_tanggung_jawab)}
                        ${renderHierarchicalList('Wewenang', data.wewenang)}
                    `;
                        document.getElementById('modalDetailTitle').innerHTML = 'Detail Job Desk';
                        document.getElementById('detailContent').innerHTML = html;
                        new bootstrap.Modal(document.getElementById('modalDetail')).show();
                    }
                });
        }

        function confirmDeleteJobDesk(id) {
            deleteId = id;
            deleteType = 'jobdesk';
            document.getElementById('deleteMessage').textContent = 'Yakin ingin menghapus Job Desk ini?';
            new bootstrap.Modal(document.getElementById('modalDelete')).show();
        }

        document.getElementById('formJobDesk').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const editId = document.getElementById('editJobDeskId').value;
            const url = editId ? `/HR-dashboard/job-desk/${editId}` : '/HR-dashboard/job-desk/store';

            if (editId) formData.append('_method', 'PUT');

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
                .catch(error => showAlert('alertContainerJobDesk', 'error', 'Gagal menyimpan data: ' + error
                    .message));
        });

        // ============ SOP FUNCTIONS ============
        function openModalCreateSop() {
            document.getElementById('modalSopTitle').innerHTML = 'Tambah SOP';
            document.getElementById('formSop').reset();
            document.getElementById('editSopId').value = '';
            document.getElementById('sopContainer').innerHTML = '';
            document.getElementById('btnSubmitSop').innerHTML = '<i class="fa-solid fa-save me-1"></i>Simpan';
            new bootstrap.Modal(document.getElementById('modalSop')).show();
        }

        function openModalEditSop(id) {
            fetch(`/HR-dashboard/sop/api/${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        document.getElementById('modalSopTitle').innerHTML = 'Edit SOP';
                        document.getElementById('editSopId').value = id;
                        document.getElementById('selectOrgSop').value = data.id_org;
                        populateParentField('sopContainer', 'sop', data.sop, 'SOP Utama', 'Detail SOP');
                        document.getElementById('btnSubmitSop').innerHTML =
                            '<i class="fa-solid fa-save me-1"></i>Update';
                        new bootstrap.Modal(document.getElementById('modalSop')).show();
                    }
                });
        }

        function openModalDetailSop(id) {
            fetch(`/HR-dashboard/sop/api/${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        const org = orgStructures.find(o => o.id == data.id_org);
                        const karyawanList = org && org.karyawans ? org.karyawans.map(k => k.nama_lengkap).join(', ') :
                            '—';

                        const html = `
                        <h5 class="fw-bold mb-1">${org ? org.jabatan : '—'}</h5>
                        <p class="text-muted">${org ? org.divisi : ''}</p>
                        <div class="card mb-3"><div class="card-body">
                            <h6 class="fw-bold">Karyawan</h6>
                            <p class="mb-0">${karyawanList}</p>
                        </div></div>
                        <hr>
                        ${renderHierarchicalList('Standard Operating Procedure (SOP)', data.sop)}
                    `;
                        document.getElementById('modalDetailTitle').innerHTML = 'Detail SOP';
                        document.getElementById('detailContent').innerHTML = html;
                        new bootstrap.Modal(document.getElementById('modalDetail')).show();
                    }
                });
        }

        function confirmDeleteSop(id) {
            deleteId = id;
            deleteType = 'sop';
            document.getElementById('deleteMessage').textContent = 'Yakin ingin menghapus SOP ini?';
            new bootstrap.Modal(document.getElementById('modalDelete')).show();
        }

        document.getElementById('formSop').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const editId = document.getElementById('editSopId').value;
            const url = editId ? `/HR-dashboard/sop/${editId}` : '/HR-dashboard/sop/store';

            if (editId) formData.append('_method', 'PUT');

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
                .catch(error => showAlert('alertContainerSop', 'error', 'Gagal menyimpan data: ' + error.message));
        });

        let isProfileUpdate = false; // Flag global untuk track mode

        function openModalProfile(karyawanId, namaKaryawan) {
            document.getElementById('formProfile').reset();
            document.getElementById('profileKaryawanId').value = karyawanId;
            document.getElementById('profQualificationContainer').innerHTML = '';
            document.getElementById('profDescriptionContainer').innerHTML = '';
            document.getElementById('profCompensationContainer').innerHTML = '';

            fetch(`/HR-dashboard/karyawan-profile/${karyawanId}`)
                .then(r => {
                    if (r.status === 204 || r.status === 404) return null;
                    return r.json();
                })
                .then(data => {
                    if (data && data.id) {
                        // MODE UPDATE - data ditemukan
                        isProfileUpdate = true;
                        document.getElementById('modalProfileTitle').innerHTML = `Edit Job Profile — ${namaKaryawan}`;
                        document.getElementById('btnProfileSubmit').innerHTML = '<i class="fa-solid fa-save me-1"></i>Update';
                        populateSimpleField('profQualificationContainer', 'qualifications[]', data.qualifications || [], 'Qualification');
                        populateSimpleField('profDescriptionContainer', 'descriptions[]', data.descriptions || [], 'Job Description');
                        populateSimpleField('profCompensationContainer', 'compensation_benefit[]', data.compensation_benefit || [], 'Compensation & Benefit');
                    } else {
                        // MODE CREATE - data belum ada
                        isProfileUpdate = false;
                        document.getElementById('modalProfileTitle').innerHTML = `Tambah Job Profile — ${namaKaryawan}`;
                        document.getElementById('btnProfileSubmit').innerHTML = '<i class="fa-solid fa-save me-1"></i>Simpan';
                    }
                    new bootstrap.Modal(document.getElementById('modalProfile')).show();
                })
                .catch(err => {
                    console.error('Error fetching profile:', err);
                    // Fallback: anggap mode create jika error
                    isProfileUpdate = false;
                    document.getElementById('modalProfileTitle').innerHTML = `Tambah Job Profile — ${namaKaryawan}`;
                    document.getElementById('btnProfileSubmit').innerHTML = '<i class="fa-solid fa-save me-1"></i>Simpan';
                    new bootstrap.Modal(document.getElementById('modalProfile')).show();
                });
        }

        document.getElementById('formProfile').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const karyawanId = document.getElementById('profileKaryawanId').value;
            
            // Tambahkan karyawan_id ke formData jika belum ada
            if (!formData.has('karyawan_id')) {
                formData.append('karyawan_id', karyawanId);
            }
            
            const url = isProfileUpdate 
                ? `/HR-dashboard/karyawan-profile/${karyawanId}` 
                : '/HR-dashboard/karyawan-profile';
            
            if (isProfileUpdate) {
                formData.append('_method', 'PUT');
            }

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
                .catch(err => showAlert('alertContainerProfile', 'error', 'Error: ' + err.message));
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

            let url = '';
            let containerAlert = '';

            if (deleteType === 'jobdesk') {
                url = `/HR-dashboard/job-desk/${deleteId}`;
                containerAlert = 'alertContainerJobDesk';
            } else if (deleteType === 'sop') {
                url = `/HR-dashboard/sop/${deleteId}`;
                containerAlert = 'alertContainerSop';
            }

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
                });
        });
    </script>
@endsection
