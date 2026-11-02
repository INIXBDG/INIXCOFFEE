@extends('layouts.app')
@section('content')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8f9fa;
            color: #333;
        }

        .nav-tabs .nav-link {
            color: #666;
            font-weight: 500;
            border: none;
            border-bottom: 2px solid transparent;
        }

        .nav-tabs .nav-link.active {
            color: #000;
            border-bottom: 2px solid #000;
            background: transparent;
            font-weight: 600;
        }

        .karyawan-card {
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 14px 16px;
            margin-bottom: 10px;
            background: #fff;
        }

        .karyawan-name {
            font-weight: 600;
            color: #222;
            font-size: 0.95rem;
        }

        .profile-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .profile-list li {
            padding: 4px 0 4px 16px;
            position: relative;
            color: #444;
            font-size: 0.9rem;
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

        .count-badge {
            background: #f0f0f0;
            color: #555;
            font-size: 0.75rem;
            padding: 2px 8px;
            border-radius: 10px;
            margin-left: 8px;
        }

        .parent-card {
            background: #f9f9f9;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            padding: 12px;
            margin-bottom: 10px;
        }

        .detail-row {
            display: flex;
            gap: 8px;
            margin-bottom: 6px;
        }

        .detail-row input {
            flex: 1;
        }

        .details-container {
            margin-left: 16px;
            padding-left: 12px;
            border-left: 2px solid #ddd;
            margin-top: 8px;
        }
    </style>

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0 fw-bold">Job Saya & Divisi</h4>
                <small class="text-muted">Mengelola data untuk divisi: {{ $divisi ?: $karyawan->divisi }}</small>
            </div>
            <span class="badge bg-dark">{{ $karyawan->jabatan }}</span>
        </div>

        <ul class="nav nav-tabs mb-4" id="mainTabs" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#jobdesk-pane">Job
                    Desk <span
                        class="count-badge">{{ $jobDesks->filter(fn($j) => !empty($j->fungsi_utama))->count() }}</span></button>
            </li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#sop-pane">SOP <span
                        class="count-badge">{{ $jobDesks->filter(fn($j) => !empty($j->sop))->count() }}</span></button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-pane">Job Profile
                    <span class="count-badge">{{ $profileOrgs->sum(fn($o) => $o->karyawans->count()) }}</span></button></li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="jobdesk-pane">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="mb-0 fw-bold">Daftar Job Desk</h5>
                    @if ($isPengelola)
                        <button class="btn btn-primary btn-sm" onclick="openModalJobDesk()"><i
                                class="fa-solid fa-plus me-1"></i>Tambah Job Desk</button>
                    @endif
                </div>
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th width="40">No</th>
                                    <th>Jabatan</th>
                                    <th>Fungsi Utama</th>
                                    <th width="80" class="text-center">Tugas</th>
                                    <th width="80" class="text-center">Wewenang</th>
                                    <th width="120" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($jobDesks->filter(fn($j)=>!empty($j->fungsi_utama)) as $idx => $jd)
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td><strong>{{ $jd->orgStructure->jabatan ?? '-' }}</strong><br><small
                                                class="text-muted">{{ $jd->orgStructure->divisi ?? '' }}</small></td>
                                        <td>{{ Str::limit($jd->fungsi_utama, 60) }}</td>
                                        <td class="text-center"><span
                                                class="count-badge">{{ count($jd->tugas_tanggung_jawab ?? []) }}</span>
                                        </td>
                                        <td class="text-center"><span
                                                class="count-badge">{{ count($jd->wewenang ?? []) }}</span></td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-secondary"
                                                onclick="openDetailJobDesk({{ $jd->id }})"><i
                                                    class="fa-solid fa-eye"></i></button>
                                            @if ($isPengelola)
                                                <button class="btn btn-sm btn-outline-primary"
                                                    onclick="openModalJobDesk({{ $jd->id }})"><i
                                                        class="fa-solid fa-pen"></i></button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">Belum ada data Job Desk</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="sop-pane">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="mb-0 fw-bold">Daftar SOP</h5>
                    @if ($isPengelola)
                        <button class="btn btn-primary btn-sm" onclick="openModalSop()"><i
                                class="fa-solid fa-plus me-1"></i>Tambah SOP</button>
                    @endif
                </div>
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th width="40">No</th>
                                    <th>Jabatan</th>
                                    <th>Jumlah SOP</th>
                                    <th>SOP Utama</th>
                                    <th width="120" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $sopDesks = $jobDesks->filter(fn($j)=>!empty($j->sop)); @endphp
                                @forelse($sopDesks as $idx => $jd)
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td><strong>{{ $jd->orgStructure->jabatan ?? '-' }}</strong><br><small
                                                class="text-muted">{{ $jd->orgStructure->divisi ?? '' }}</small></td>
                                        <td><span class="count-badge">{{ count($jd->sop) }}</span></td>
                                        <td>{{ Str::limit($jd->sop[0]['name'] ?? '', 50) ?: '—' }}</td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-secondary"
                                                onclick="openDetailSop({{ $jd->id }})"><i
                                                    class="fa-solid fa-eye"></i></button>
                                            @if ($isPengelola)
                                                <button class="btn btn-sm btn-outline-primary"
                                                    onclick="openModalSop({{ $jd->id }})"><i
                                                        class="fa-solid fa-pen"></i></button>
                                            @endif
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

            <div class="tab-pane fade" id="profile-pane">
                <h5 class="mb-3 fw-bold">Job Profile Karyawan Divisi</h5>
                <div id="alertContainerProfile"></div>
                @forelse($profileOrgs as $org)
                    <h6 class="mt-4 mb-2 text-muted text-uppercase"
                        style="font-size: 0.8rem; letter-spacing: 1px; border-bottom: 1px solid #eee; padding-bottom: 4px;">
                        {{ $org->jabatan }} @if ($org->divisi)
                            <span class="fw-normal text-dark">- {{ $org->divisi }}</span>
                        @endif
                        <span class="count-badge">{{ $org->karyawans->count() }}</span>
                    </h6>
                    @foreach ($org->karyawans as $k)
                        <div class="karyawan-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="karyawan-name">{{ $k->nama_lengkap }} @if ($k->id == $karyawan->id)
                                            <span class="badge bg-dark ms-2">Saya</span>
                                        @endif
                                    </div>
                                    <small class="text-muted">NIP: {{ $k->nip ?? '-' }}</small>
                                </div>
                                <div>
                                    @if ($k->jobProfile)
                                        <button class="btn btn-sm btn-outline-primary"
                                            onclick="openModalProfile({{ $k->id }}, '{{ addslashes($k->nama_lengkap) }}')"><i
                                                class="fa-solid fa-pen me-1"></i>Edit</button>
                                    @else
                                        <button class="btn btn-sm btn-primary"
                                            onclick="openModalProfile({{ $k->id }}, '{{ addslashes($k->nama_lengkap) }}')"><i
                                                class="fa-solid fa-plus me-1"></i>Isi</button>
                                    @endif
                                </div>
                            </div>
                            @if ($k->jobProfile)
                                <div class="mt-3">
                                    <div class="fw-bold" style="font-size:0.8rem; color:#666;">Qualification</div>
                                    <ul class="profile-list">
                                        @foreach (array_slice($k->jobProfile->qualifications ?? [], 0, 2) as $q)
                                            <li>{{ $q }}</li>
                                        @endforeach
                                        @if (count($k->jobProfile->qualifications ?? []) > 2)
                                            <li class="text-muted fst-italic">
                                                +{{ count($k->jobProfile->qualifications) - 2 }} lainnya</li>
                                        @endif
                                    </ul>
                                </div>
                            @else
                                <div class="empty-text mt-2">Belum ada job profile. Silakan klik tombol "Isi".</div>
                            @endif
                        </div>
                    @endforeach
                @empty
                    <div class="card">
                        <div class="card-body text-center text-muted py-5">Tidak ada data karyawan di divisi Anda.</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetail" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetailTitle">Detail</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailContent"></div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Tutup</button></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalJobDesk" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <form id="formJobDesk">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalJobDeskTitle">Job Desk</h5><button type="button"
                            class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="jdId">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Jabatan</label>
                            <select class="form-select" id="jdOrg" name="id_org" required>
                                <option value="">— Pilih Jabatan —</option>
                                @foreach ($orgStructures as $o)
                                    <option value="{{ $o->id }}">{{ $o->jabatan }} - {{ $o->divisi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3"><label class="form-label fw-bold">Fungsi Utama</label>
                            <textarea class="form-control" name="fungsi_utama" rows="3"></textarea>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6"><label class="form-label fw-bold">Tujuan Jabatan</label>
                                <textarea class="form-control" name="tujuan_jabatan" rows="2"></textarea>
                            </div>
                            <div class="col-md-6"><label class="form-label fw-bold">Kualifikasi Pendidikan</label>
                                <textarea class="form-control" name="kualifikasi_pendidikan" rows="2"></textarea>
                            </div>
                            <div class="col-md-6"><label class="form-label fw-bold">Pengalaman Kerja</label>
                                <textarea class="form-control" name="pengalaman_kerja" rows="2"></textarea>
                            </div>
                            <div class="col-md-6"><label class="form-label fw-bold">Karakteristik Pribadi</label>
                                <textarea class="form-control" name="karakteristik_pribadi" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kompetensi</label>
                            <div id="jdKompetensiContainer"></div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2"
                                onclick="addSimpleField('jdKompetensiContainer', 'kompetensi[]', 'Kompetensi')"><i
                                    class="fa-solid fa-plus"></i> Tambah Kompetensi</button>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tugas & Tanggung Jawab</label>
                            <div id="jdTugasContainer"></div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2"
                                onclick="addParentCard('jdTugasContainer', 'tugas_tanggung_jawab', 'Tugas Utama', 'Detail Tugas')"><i
                                    class="fa-solid fa-plus"></i> Tambah Tugas</button>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Wewenang</label>
                            <div id="jdWewenangContainer"></div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2"
                                onclick="addParentCard('jdWewenangContainer', 'wewenang', 'Wewenang Utama', 'Detail Wewenang')"><i
                                    class="fa-solid fa-plus"></i> Tambah Wewenang</button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnJobDeskSubmit">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalSop" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <form id="formSop">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalSopTitle">SOP</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="sopId">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Jabatan</label>
                            <select class="form-select" id="sopOrg" name="id_org" required>
                                <option value="">— Pilih Jabatan —</option>
                                @foreach ($orgStructures as $o)
                                    <option value="{{ $o->id }}">{{ $o->jabatan }} - {{ $o->divisi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Daftar SOP</label>
                            <div id="sopContainer"></div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2"
                                onclick="addParentCard('sopContainer', 'sop', 'SOP Utama', 'Detail SOP')"><i
                                    class="fa-solid fa-plus"></i> Tambah SOP</button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSopSubmit">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalProfile" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <form id="formProfile">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalProfileTitle">Job Profile</h5><button type="button"
                            class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="profileKaryawanId" name="karyawan_id">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Qualification</label>
                            <div id="profQualContainer"></div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2"
                                onclick="addSimpleField('profQualContainer', 'qualifications[]', 'Qualification')"><i
                                    class="fa-solid fa-plus"></i> Tambah</button>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Job Description</label>
                            <div id="profDescContainer"></div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2"
                                onclick="addSimpleField('profDescContainer', 'descriptions[]', 'Job Description')"><i
                                    class="fa-solid fa-plus"></i> Tambah</button>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Compensation & Benefit</label>
                            <div id="profCompContainer"></div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2"
                                onclick="addSimpleField('profCompContainer', 'compensation_benefit[]', 'Compensation & Benefit')"><i
                                    class="fa-solid fa-plus"></i> Tambah</button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnProfileSubmit">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let isProfileUpdate = false;
        const csrf = () => document.querySelector('meta[name="csrf-token"]').content;
        const escapeHtml = (text) => {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        };

        function showAlert(containerId, type, message) {
            const container = document.getElementById(containerId);
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            container.innerHTML =
                `<div class="alert ${alertClass} alert-dismissible fade show">${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
            setTimeout(() => {
                container.innerHTML = '';
            }, 5000);
        }

        function addSimpleField(containerId, fieldName, placeholder) {
            const container = document.getElementById(containerId);
            const div = document.createElement('div');
            div.className = 'input-group mb-2';
            div.innerHTML =
                `<input type="text" name="${fieldName}" class="form-control" placeholder="${placeholder}">
                         <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button>`;
            container.appendChild(div);
        }

        function populateSimpleField(containerId, fieldName, dataArray, placeholder) {
            const container = document.getElementById(containerId);
            container.innerHTML = '';
            if (dataArray && dataArray.length > 0) {
                dataArray.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'input-group mb-2';
                    div.innerHTML =
                        `<input type="text" name="${fieldName}" class="form-control" value="${escapeHtml(item)}" placeholder="${placeholder}">
                                 <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button>`;
                    container.appendChild(div);
                });
            } else {
                addSimpleField(containerId, fieldName, placeholder);
            }
        }

        function addParentCard(containerId, fieldName, parentPlaceholder, detailPlaceholder) {
            const container = document.getElementById(containerId);
            const div = document.createElement('div');
            div.className = 'parent-card';
            div.innerHTML = `
            <div class="mb-2 input-group">
                <input type="text" name="${fieldName}[][name]" class="form-control fw-bold" placeholder="${parentPlaceholder}">
                <button type="button" class="btn btn-outline-danger" onclick="this.closest('.parent-card').remove()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="details-container"></div>
            <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 mt-1" onclick="addDetailRow(this, '${detailPlaceholder}')"><i class="fa-solid fa-plus"></i> Tambah Detail</button>
        `;
            container.appendChild(div);
        }

        function populateParentCard(containerId, fieldName, dataArray, parentPlaceholder, detailPlaceholder) {
            const container = document.getElementById(containerId);
            container.innerHTML = '';
            if (dataArray && dataArray.length > 0) {
                dataArray.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'parent-card';
                    let detailsHtml = '';
                    if (item.details && item.details.length > 0) {
                        item.details.forEach(d => {
                            detailsHtml += `<div class="detail-row">
                            <input type="text" name="${fieldName}[][details][]" class="form-control form-control-sm" value="${escapeHtml(d)}" placeholder="${detailPlaceholder}">
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button>
                        </div>`;
                        });
                    }
                    div.innerHTML = `
                    <div class="mb-2 input-group">
                        <input type="text" name="${fieldName}[][name]" class="form-control fw-bold" value="${escapeHtml(item.name)}" placeholder="${parentPlaceholder}">
                        <button type="button" class="btn btn-outline-danger" onclick="this.closest('.parent-card').remove()"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <div class="details-container">${detailsHtml}</div>
                    <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 mt-1" onclick="addDetailRow(this, '${detailPlaceholder}')"><i class="fa-solid fa-plus"></i> Tambah Detail</button>
                `;
                    container.appendChild(div);
                });
            } else {
                addParentCard(containerId, fieldName, parentPlaceholder, detailPlaceholder);
            }
        }

        function addDetailRow(btn, detailPlaceholder) {
            const container = btn.previousElementSibling;
            const fieldName = btn.closest('.parent-card').querySelector('input[name$="[name]"]').name.replace('[name]', '');
            const div = document.createElement('div');
            div.className = 'detail-row';
            div.innerHTML =
                `<input type="text" name="${fieldName}[details][]" class="form-control form-control-sm" placeholder="${detailPlaceholder}">
                         <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button>`;
            container.appendChild(div);
        }

        function openDetailJobDesk(id) {
            document.getElementById('modalDetailTitle').innerText = 'Detail Job Desk';
            document.getElementById('detailContent').innerHTML =
                '<div class="text-center py-4"><i class="fa-solid fa-spinner fa-spin"></i> Memuat...</div>';
            new bootstrap.Modal(document.getElementById('modalDetail')).show();

            fetch(`/employee/job-desk/api/${id}`)
                .then(r => r.json())
                .then(data => {
                    if (data && !data.error) {
                        let kompHtml = data.kompetensi && data.kompetensi.length > 0 ? '<ul>' + data.kompetensi.map(k =>
                            `<li>${escapeHtml(k)}</li>`).join('') + '</ul>' : '<p class="text-muted">-</p>';
                        let tugasHtml = data.tugas_tanggung_jawab && data.tugas_tanggung_jawab.length > 0 ? data
                            .tugas_tanggung_jawab.map((t, i) =>
                                `<div class="parent-card"><div class="fw-bold mb-2">${i+1}. ${escapeHtml(t.name)}</div>${t.details && t.details.length > 0 ? '<ul class="mb-0">' + t.details.map(d => `<li>${escapeHtml(d)}</li>`).join('') + '</ul>' : '<div class="empty-text">Tidak ada detail</div>'}</div>`
                                ).join('') : '<p class="text-muted">-</p>';
                        let wewenangHtml = data.wewenang && data.wewenang.length > 0 ? data.wewenang.map((w, i) =>
                            `<div class="parent-card"><div class="fw-bold mb-2">${i+1}. ${escapeHtml(w.name)}</div>${w.details && w.details.length > 0 ? '<ul class="mb-0">' + w.details.map(d => `<li>${escapeHtml(d)}</li>`).join('') + '</ul>' : '<div class="empty-text">Tidak ada detail</div>'}</div>`
                            ).join('') : '<p class="text-muted">-</p>';

                        document.getElementById('detailContent').innerHTML = `
                        <h5 class="fw-bold mb-1">${data.org_structure?.jabatan || '—'}</h5>
                        <p class="text-muted">${data.org_structure?.divisi || ''}</p><hr>
                        <h6 class="fw-bold">Fungsi Utama</h6><p>${data.fungsi_utama || '-'}</p>
                        <h6 class="fw-bold mt-3">Spesifikasi Jabatan</h6>
                        <p><strong>Tujuan:</strong> ${data.tujuan_jabatan || '-'}</p>
                        <p><strong>Pendidikan:</strong> ${data.kualifikasi_pendidikan || '-'}</p>
                        <p><strong>Pengalaman:</strong> ${data.pengalaman_kerja || '-'}</p>
                        <p><strong>Karakteristik:</strong> ${data.karakteristik_pribadi || '-'}</p>
                        <p><strong>Kompetensi:</strong></p>${kompHtml}
                        <h6 class="fw-bold mt-4 mb-2">Tugas dan Tanggung Jawab</h6>${tugasHtml}
                        <h6 class="fw-bold mt-4 mb-2">Wewenang</h6>${wewenangHtml}
                    `;
                    }
                });
        }

        function openDetailSop(id) {
            document.getElementById('modalDetailTitle').innerText = 'Detail SOP';
            document.getElementById('detailContent').innerHTML =
                '<div class="text-center py-4"><i class="fa-solid fa-spinner fa-spin"></i> Memuat...</div>';
            new bootstrap.Modal(document.getElementById('modalDetail')).show();

            fetch(`/employee/job-desk/api/${id}`)
                .then(r => r.json())
                .then(data => {
                    if (data && !data.error) {
                        let sopHtml = data.sop && data.sop.length > 0 ? data.sop.map((s, i) =>
                            `<div class="parent-card"><div class="fw-bold mb-2">${i+1}. ${escapeHtml(s.name)}</div>${s.details && s.details.length > 0 ? '<ul class="mb-0">' + s.details.map(d => `<li>${escapeHtml(d)}</li>`).join('') + '</ul>' : '<div class="empty-text">Tidak ada detail</div>'}</div>`
                            ).join('') : '<p class="text-muted">-</p>';
                        document.getElementById('detailContent').innerHTML = `
                        <h5 class="fw-bold mb-1">${data.org_structure?.jabatan || '—'}</h5>
                        <p class="text-muted">${data.org_structure?.divisi || ''}</p><hr>
                        <h6 class="fw-bold mt-4 mb-2">Standard Operating Procedure (SOP)</h6>${sopHtml}
                    `;
                    }
                });
        }

        function openModalJobDesk(id = null) {
            document.getElementById('formJobDesk').reset();
            document.getElementById('jdId').value = id || '';
            document.getElementById('modalJobDeskTitle').innerText = id ? 'Edit Job Desk' : 'Tambah Job Desk';
            document.getElementById('jdKompetensiContainer').innerHTML = '';
            document.getElementById('jdTugasContainer').innerHTML = '';
            document.getElementById('jdWewenangContainer').innerHTML = '';
            new bootstrap.Modal(document.getElementById('modalJobDesk')).show();

            if (id) {
                fetch(`/employee/job-desk/api/${id}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data && !data.error) {
                            document.getElementById('jdOrg').value = data.id_org;
                            document.querySelector('[name="fungsi_utama"]').value = data.fungsi_utama || '';
                            document.querySelector('[name="tujuan_jabatan"]').value = data.tujuan_jabatan || '';
                            document.querySelector('[name="kualifikasi_pendidikan"]').value = data
                                .kualifikasi_pendidikan || '';
                            document.querySelector('[name="pengalaman_kerja"]').value = data.pengalaman_kerja || '';
                            document.querySelector('[name="karakteristik_pribadi"]').value = data
                                .karakteristik_pribadi || '';
                            populateSimpleField('jdKompetensiContainer', 'kompetensi[]', data.kompetensi || [],
                                'Kompetensi');
                            populateParentCard('jdTugasContainer', 'tugas_tanggung_jawab', data.tugas_tanggung_jawab ||
                                [], 'Tugas Utama', 'Detail Tugas');
                            populateParentCard('jdWewenangContainer', 'wewenang', data.wewenang || [], 'Wewenang Utama',
                                'Detail Wewenang');
                        }
                    });
            } else {
                addSimpleField('jdKompetensiContainer', 'kompetensi[]', 'Kompetensi');
                addParentCard('jdTugasContainer', 'tugas_tanggung_jawab', 'Tugas Utama', 'Detail Tugas');
                addParentCard('jdWewenangContainer', 'wewenang', 'Wewenang Utama', 'Detail Wewenang');
            }
        }

        function openModalSop(id = null) {
            document.getElementById('formSop').reset();
            document.getElementById('sopId').value = id || '';
            document.getElementById('sopContainer').innerHTML = '';
            document.getElementById('modalSopTitle').innerText = id ? 'Edit SOP' : 'Tambah SOP';
            new bootstrap.Modal(document.getElementById('modalSop')).show();

            if (id) {
                fetch(`/employee/job-desk/api/${id}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data && !data.error) {
                            document.getElementById('sopOrg').value = data.id_org;
                            populateParentCard('sopContainer', 'sop', data.sop || [], 'SOP Utama', 'Detail SOP');
                        }
                    });
            } else {
                addParentCard('sopContainer', 'sop', 'SOP Utama', 'Detail SOP');
            }
        }

        function openModalProfile(karyawanId, nama) {
            document.getElementById('formProfile').reset();
            document.getElementById('profileKaryawanId').value = karyawanId;
            document.getElementById('profQualContainer').innerHTML = '';
            document.getElementById('profDescContainer').innerHTML = '';
            document.getElementById('profCompContainer').innerHTML = '';

            fetch(`/employee/karyawan-profile/${karyawanId}`)
                .then(r => r.status === 204 || r.status === 404 ? null : r.json())
                .then(data => {
                    if (data && data.id) {
                        isProfileUpdate = true;
                        document.getElementById('modalProfileTitle').innerText = `Edit Job Profile — ${nama}`;
                        document.getElementById('btnProfileSubmit').innerHTML =
                            '<i class="fa-solid fa-save me-1"></i>Update';
                        populateSimpleField('profQualContainer', 'qualifications[]', data.qualifications || [],
                            'Qualification');
                        populateSimpleField('profDescContainer', 'descriptions[]', data.descriptions || [],
                            'Job Description');
                        populateSimpleField('profCompContainer', 'compensation_benefit[]', data.compensation_benefit ||
                            [], 'Compensation & Benefit');
                    } else {
                        isProfileUpdate = false;
                        document.getElementById('modalProfileTitle').innerText = `Tambah Job Profile — ${nama}`;
                        document.getElementById('btnProfileSubmit').innerHTML =
                            '<i class="fa-solid fa-save me-1"></i>Simpan';
                        addSimpleField('profQualContainer', 'qualifications[]', 'Qualification');
                        addSimpleField('profDescContainer', 'descriptions[]', 'Job Description');
                        addSimpleField('profCompContainer', 'compensation_benefit[]', 'Compensation & Benefit');
                    }
                    new bootstrap.Modal(document.getElementById('modalProfile')).show();
                });
        }

        document.getElementById('formJobDesk').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const id = document.getElementById('jdId').value;
            const url = id ? `/employee/job-desk/${id}` : '/employee/job-desk/store';
            if (id) formData.append('_method', 'PUT');

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf(),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalJobDesk')).hide();
                    location.reload();
                } else {
                    let msg = data.message || 'Gagal menyimpan';
                    if (data.errors) msg += '<br>' + Object.values(data.errors).flat().join('<br>');
                    alert(msg);
                }
            });
        });

        document.getElementById('formSop').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const id = document.getElementById('sopId').value;
            const url = id ? `/employee/sop/${id}` : '/employee/sop/store';
            if (id) formData.append('_method', 'PUT');

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf(),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalSop')).hide();
                    location.reload();
                } else {
                    let msg = data.message || 'Gagal menyimpan';
                    if (data.errors) msg += '<br>' + Object.values(data.errors).flat().join('<br>');
                    alert(msg);
                }
            });
        });

        document.getElementById('formProfile').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const karyawanId = document.getElementById('profileKaryawanId').value;
            const url = isProfileUpdate ? `/employee/karyawan-profile/${karyawanId}` : '/employee/karyawan-profile';
            if (isProfileUpdate) formData.append('_method', 'PUT');

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf(),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalProfile')).hide();
                    location.reload();
                } else {
                    let msg = data.message || 'Gagal menyimpan';
                    if (data.errors) msg += '<br>' + Object.values(data.errors).flat().join('<br>');
                    alert(msg);
                }
            });
        });
    </script>
@endsection
