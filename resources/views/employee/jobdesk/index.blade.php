@extends('layouts.app')
@section('content')
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

        .karyawan-card {
            border: 1px solid #eee;
            border-radius: 6px;
            padding: 14px 16px;
            margin-bottom: 10px;
            background: #fcfcfc;
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

        .btn-outline-danger {
            color: #c0392b;
            border-color: #e0b4b0;
            background: transparent;
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 fw-bold">Job Saya</h4>
            <span class="badge bg-primary">{{ $karyawan->jabatan }}</span>
        </div>

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
            <div class="tab-pane fade show active" id="jobdesk-pane" role="tabpanel">
                <div id="alertContainerJobDesk"></div>
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th width="40">No</th>
                                        <th>Jabatan</th>
                                        <th>Fungsi Utama</th>
                                        <th width="80">Tugas</th>
                                        <th width="80">Wewenang</th>
                                        <th width="90" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($jobDesks as $index => $jobDesk)
                                        @if (!empty($jobDesk->fungsi_utama) || !empty($jobDesk->tugas_tanggung_jawab) || !empty($jobDesk->wewenang))
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    <strong>{{ $jobDesk->orgStructure->jabatan ?? '-' }}</strong>
                                                    @if ($jobDesk->orgStructure->divisi ?? false)
                                                        <br><small
                                                            class="text-muted">{{ $jobDesk->orgStructure->divisi }}</small>
                                                    @endif
                                                </td>
                                                <td>{{ Str::limit($jobDesk->fungsi_utama, 50) ?: '—' }}</td>
                                                <td>{{ count($jobDesk->tugas_tanggung_jawab ?? []) }}</td>
                                                <td>{{ count($jobDesk->wewenang ?? []) }}</td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-outline-secondary"
                                                        onclick="openModalDetailJobDesk({{ $jobDesk->id }})">
                                                        <i class="fa-solid fa-eye me-1"></i>Detail
                                                    </button>
                                                </td>
                                            </tr>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">Belum ada data Job Desk
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="sop-pane" role="tabpanel">
                <div id="alertContainerSop"></div>
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th width="40">No</th>
                                        <th>Jabatan</th>
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
                                            <td><span class="count-badge">{{ count($jobDesk->sop ?? []) }}</span></td>
                                            <td>{{ Str::limit($jobDesk->sop[0]['name'] ?? '', 50) ?: '—' }}</td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-secondary"
                                                    onclick="openModalDetailSop({{ $jobDesk->id }})">
                                                    <i class="fa-solid fa-eye me-1"></i>Detail
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">Belum ada data SOP</td>
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
                    <h5 class="mb-0 fw-bold">Job Profile Saya</h5>
                </div>
                <div id="alertContainerProfile"></div>

                <div class="karyawan-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="karyawan-name">{{ $karyawan->nama_lengkap }}</div>
                    </div>

                    @if ($karyawan->jobProfile)
                        @php
                            $data = $karyawan->jobProfile;
                            $isPrivate = true;
                        @endphp
                        @include('HR.job_desk._profile_section')
                    @else
                        <div class="empty-text mt-1">Belum ada job profile</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

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
                        <div class="alert alert-info">Job Profile melekat pada karyawan individu.</div>

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

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let isProfileUpdate = false;

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

        function openModalDetailJobDesk(id) {
            fetch(`/employee/job-desk/api/${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data && !data.error) {
                        let kompetensiHtml = '';
                        if (data.kompetensi && data.kompetensi.length > 0) {
                            kompetensiHtml = '<ul>';
                            data.kompetensi.forEach(k => kompetensiHtml += `<li>${escapeHtml(k)}</li>`);
                            kompetensiHtml += '</ul>';
                        } else {
                            kompetensiHtml = '<p class="text-muted">-</p>';
                        }

                        const html = `
                        <h5 class="fw-bold mb-1">${data.org_structure?.jabatan || '—'}</h5>
                        <p class="text-muted">${data.org_structure?.divisi || ''}</p>
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

        function openModalDetailSop(id) {
            fetch(`/employee/job-desk/api/${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data && !data.error) {
                        const html = `
                        <h5 class="fw-bold mb-1">${data.org_structure?.jabatan || '—'}</h5>
                        <p class="text-muted">${data.org_structure?.divisi || ''}</p>
                        <hr>
                        ${renderHierarchicalList('Standard Operating Procedure (SOP)', data.sop)}
                    `;
                        document.getElementById('modalDetailTitle').innerHTML = 'Detail SOP';
                        document.getElementById('detailContent').innerHTML = html;
                        new bootstrap.Modal(document.getElementById('modalDetail')).show();
                    }
                });
        }

        function openModalProfile(karyawanId, namaKaryawan) {
            document.getElementById('formProfile').reset();
            document.getElementById('profileKaryawanId').value = karyawanId;
            document.getElementById('profQualificationContainer').innerHTML = '';
            document.getElementById('profDescriptionContainer').innerHTML = '';
            document.getElementById('profCompensationContainer').innerHTML = '';

            fetch(`/employee/karyawan-profile/${karyawanId}`)
                .then(r => {
                    if (r.status === 204 || r.status === 404) return null;
                    return r.json();
                })
                .then(data => {
                    if (data && data.id) {
                        isProfileUpdate = true;
                        document.getElementById('modalProfileTitle').innerHTML = `Edit Job Profile — ${namaKaryawan}`;
                        document.getElementById('btnProfileSubmit').innerHTML =
                            '<i class="fa-solid fa-save me-1"></i>Update';
                        populateSimpleField('profQualificationContainer', 'qualifications[]', data.qualifications || [],
                            'Qualification');
                        populateSimpleField('profDescriptionContainer', 'descriptions[]', data.descriptions || [],
                            'Job Description');
                        populateSimpleField('profCompensationContainer', 'compensation_benefit[]', data
                            .compensation_benefit || [], 'Compensation & Benefit');
                    } else {
                        isProfileUpdate = false;
                        document.getElementById('modalProfileTitle').innerHTML = `Tambah Job Profile — ${namaKaryawan}`;
                        document.getElementById('btnProfileSubmit').innerHTML =
                            '<i class="fa-solid fa-save me-1"></i>Simpan';
                    }
                    new bootstrap.Modal(document.getElementById('modalProfile')).show();
                });
        }

        document.getElementById('formProfile').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const karyawanId = document.getElementById('profileKaryawanId').value;

            const url = isProfileUpdate ?
                `/employee/karyawan-profile/${karyawanId}` :
                '/employee/karyawan-profile';

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
            if (!confirm('Yakin ingin menghapus Job Profile?')) return;
            fetch(`/employee/karyawan-profile/${karyawanId}`, {
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
    </script>
@endsection
