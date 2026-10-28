@extends('layouts.app')
@section('content')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root {
            --pri: #4f46e5;
            --pri-l: #eef2ff;
            --pri-d: #3730a3;
            --g1: #f9fafb;
            --g2: #e5e7eb;
            --g4: #9ca3af;
            --g6: #4b5563;
            --g9: #111827;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: var(--g9);
            background: #fafbfc;
        }

        .nav-tabs {
            border-bottom: 2px solid var(--g2);
        }

        .nav-tabs .nav-link {
            color: var(--g4);
            font-weight: 600;
            border: none;
            border-bottom: 3px solid transparent;
            padding: 12px 22px;
            font-size: .9rem;
        }

        .nav-tabs .nav-link:hover {
            color: var(--pri);
        }

        .nav-tabs .nav-link.active {
            color: var(--pri);
            border-bottom-color: var(--pri);
            background: transparent;
        }

        .tcount {
            background: #f3f4f6;
            color: var(--g6);
            font-size: .7rem;
            padding: 2px 8px;
            border-radius: 10px;
            margin-left: 6px;
            font-weight: 700;
        }

        .nav-link.active .tcount {
            background: var(--pri-l);
            color: var(--pri);
        }

        .bar {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }

        .bar h6 {
            margin: 0;
            font-weight: 700;
            font-size: .9rem;
        }

        .bar .search {
            width: 260px;
        }

        .tbl {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border: 1px solid var(--g2);
            font-size: .86rem;
        }

        .tbl th {
            background: var(--g1);
            color: var(--g6);
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .4px;
            padding: 10px 12px;
            border-bottom: 2px solid var(--g2);
            text-align: left;
        }

        .tbl td {
            padding: 10px 12px;
            border-bottom: 1px solid #f1f2f4;
            vertical-align: top;
            color: #374151;
        }

        .tbl tbody tr:hover td {
            background: #fbfbff;
        }

        .tbl tr.grp td {
            background: var(--pri-l);
            color: var(--pri);
            font-weight: 700;
            border-bottom: 1px solid var(--g2);
            padding: 8px 12px;
        }

        .tbl tr.grp:hover td {
            background: var(--pri-l);
        }

        .pl {
            margin: 0;
            padding-left: 16px;
        }

        .pl li {
            line-height: 1.5;
        }

        .empty {
            color: var(--g4);
            font-style: italic;
            font-size: .82rem;
        }

        .cnt {
            background: #f3f4f6;
            color: var(--g6);
            font-size: .72rem;
            font-weight: 700;
            padding: 2px 9px;
            border-radius: 10px;
        }

        .me {
            background: var(--pri);
            color: #fff;
            font-size: .65rem;
            padding: 1px 7px;
            border-radius: 8px;
            margin-left: 6px;
            vertical-align: middle;
        }

        .nm {
            display: inline-block;
            background: var(--pri-l);
            color: var(--pri);
            font-size: .72rem;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 5px;
            margin: 1px 2px 1px 0;
        }

        .btn-pri {
            background: var(--pri);
            border-color: var(--pri);
            color: #fff;
            font-weight: 600;
            font-size: .82rem;
        }

        .btn-pri:hover {
            background: var(--pri-d);
            border-color: var(--pri-d);
            color: #fff;
        }

        .modal-header {
            background: linear-gradient(135deg, #4f46e5, #3730a3);
            color: #fff;
            padding: 14px 20px;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }

        .modal-title {
            font-size: 1rem;
            font-weight: 700;
        }

        .modal-dialog-scrollable .modal-content {
            max-height: calc(100vh - 2rem);
        }

        .flabel {
            font-weight: 600;
            font-size: .82rem;
            margin: 14px 0 6px;
            display: block;
            color: #374151;
        }

        .irow {
            display: flex;
            gap: 6px;
            margin-bottom: 6px;
            align-items: center;
        }

        .irow input {
            flex: 1;
        }

        .pgroup {
            border-left: 3px solid var(--pri);
            background: var(--g1);
            padding: 10px 12px;
            margin-bottom: 10px;
            border-radius: 0 6px 6px 0;
        }

        .phead {
            display: flex;
            gap: 6px;
            margin-bottom: 8px;
        }

        .phead input {
            flex: 1;
        }

        .dwrap {
            margin-left: 14px;
            padding-left: 10px;
            border-left: 1px dashed #cbd5e1;
        }

        .modal-err .alert {
            font-size: .85rem;
            padding: 8px 12px;
        }

        .sec {
            font-weight: 700;
            font-size: .82rem;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: var(--pri);
            border-bottom: 1px solid var(--g2);
            padding-bottom: 4px;
            margin: 18px 0 8px;
        }

        .kv {
            width: 100%;
            font-size: .86rem;
        }

        .kv th {
            width: 150px;
            color: var(--g6);
            font-weight: 600;
            padding: 5px 10px 5px 0;
            vertical-align: top;
        }

        .kv td {
            padding: 5px 0;
        }

        .hl {
            padding-left: 20px;
            margin: 0;
        }

        .hl>li {
            margin-bottom: 8px;
        }

        .hl ul {
            margin: 3px 0 0;
            color: var(--g6);
        }

        .tag {
            display: inline-block;
            background: var(--pri-l);
            color: var(--pri);
            padding: 2px 10px;
            border-radius: 5px;
            font-size: .76rem;
            font-weight: 600;
            margin: 2px 4px 2px 0;
        }
    </style>

    @php
        $jobDeskRows = $jobDesks
            ->filter(fn($jd) => !empty($jd->fungsi_utama) || !empty($jd->tugas_tanggung_jawab) || !empty($jd->wewenang))
            ->values();
        $sopRows = $jobDesks->filter(fn($jd) => !empty($jd->sop) && count($jd->sop) > 0)->values();
        $profileOrgs = $orgStructures->filter(fn($o) => $o->karyawans->count() > 0)->values();
        $profileCount = $profileOrgs->sum(fn($o) => $o->karyawans->count());
        $creatableOrgs = $orgStructures->filter(fn($o) => !in_array($o->id, $ownOrgIds))->values();
        $canEditOrg = fn($orgId, $exists) => $isPengelola &&
            (!in_array($orgId, $ownOrgIds) || ($exists && $bolehEditSendiri));
        $orgData = $orgStructures
            ->map(
                fn($o) => [
                    'id' => $o->id,
                    'jabatan' => $o->jabatan,
                    'divisi' => $o->divisi,
                    'karyawans' => $o->karyawans
                        ->map(fn($k) => ['id' => $k->id, 'nama_lengkap' => $k->nama_lengkap])
                        ->values(),
                ],
            )
            ->values();
    @endphp

    <div class="container-fluid py-4 px-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h4 class="mb-0 fw-bold">Job Saya</h4>
                <small class="text-muted">
                    @if ($isPengelola)
                        Anda mengelola seluruh jabatan di divisi {{ $divisi ?: $karyawan->jabatan }}
                    @else
                        Job Desk, SOP, dan Job Profile untuk jabatan Anda
                    @endif
                </small>
            </div>
            <div class="d-flex gap-2 align-items-center">
                @if ($divisi)
                    <span class="nm">Divisi: {{ $divisi }}</span>
                @endif
                <span class="badge bg-dark">{{ $karyawan->jabatan }}</span>
                @if ($isPengelola)
                    <span class="badge" style="background:var(--pri)">Pengelola Divisi</span>
                @endif
            </div>
        </div>

        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#jobdesk-pane"
                    type="button">Job Desk<span class="tcount">{{ $jobDeskRows->count() }}</span></button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#sop-pane"
                    type="button">SOP<span class="tcount">{{ $sopRows->count() }}</span></button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-pane"
                    type="button">Job Profile<span class="tcount">{{ $profileCount }}</span></button></li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="jobdesk-pane">
                <div class="bar">
                    <h6>Daftar Job Desk</h6>
                    <div class="d-flex gap-2">
                        <input type="search" class="form-control form-control-sm search"
                            placeholder="Cari jabatan / fungsi..." oninput="filterRows('tblJobDesk', this.value)">
                        @if ($isPengelola)
                            <button class="btn btn-sm btn-pri" onclick="openCreateJobDesk()"><i
                                    class="fa-solid fa-plus me-1"></i>Tambah Job Desk</button>
                        @endif
                    </div>
                </div>
                <div id="alertJobDesk"></div>
                <div class="table-responsive">
                    <table class="tbl" id="tblJobDesk">
                        <thead>
                            <tr>
                                <th width="40">No</th>
                                <th>Jabatan</th>
                                @if ($isPengelola)
                                    <th>Karyawan</th>
                                @endif
                                <th>Fungsi Utama</th>
                                <th width="80" class="text-center">Tugas</th>
                                <th width="90" class="text-center">Wewenang</th>
                                <th width="160" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jobDeskRows as $jd)
                                <tr
                                    data-search="{{ strtolower(($jd->orgStructure->jabatan ?? '') . ' ' . ($jd->orgStructure->divisi ?? '') . ' ' . $jd->fungsi_utama) }}">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <strong>{{ $jd->orgStructure->jabatan ?? '-' }}</strong>
                                        @if (in_array($jd->id_org, $ownOrgIds))
                                            <span class="me">Jabatan saya</span>
                                        @endif
                                        @if ($jd->orgStructure->divisi ?? false)
                                            <br><small class="text-muted">{{ $jd->orgStructure->divisi }}</small>
                                        @endif
                                    </td>
                                    @if ($isPengelola)
                                        <td>
                                            @forelse($jd->orgStructure->karyawans ?? [] as $k)
                                                <span class="nm">{{ $k->nama_lengkap }}</span>
                                            @empty
                                                <span class="empty">—</span>
                                            @endforelse
                                        </td>
                                    @endif
                                    <td>{{ Str::limit($jd->fungsi_utama, 70) ?: '—' }}</td>
                                    <td class="text-center"><span
                                            class="cnt">{{ count($jd->tugas_tanggung_jawab ?? []) }}</span></td>
                                    <td class="text-center"><span class="cnt">{{ count($jd->wewenang ?? []) }}</span>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <button class="btn btn-sm btn-outline-secondary"
                                            onclick="openDetailJobDesk({{ $jd->id }})"><i
                                                class="fa-solid fa-eye"></i> Detail</button>
                                        @if ($canEditOrg($jd->id_org, true))
                                            <button class="btn btn-sm btn-outline-primary"
                                                onclick="openEditJobDesk({{ $jd->id }})"><i
                                                    class="fa-solid fa-pen"></i> Edit</button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isPengelola ? 7 : 6 }}" class="text-center empty py-4">Belum ada data
                                        Job Desk</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="sop-pane">
                <div class="bar">
                    <h6>Standard Operating Procedure</h6>
                    <div class="d-flex gap-2">
                        <input type="search" class="form-control form-control-sm search"
                            placeholder="Cari jabatan / SOP..." oninput="filterRows('tblSop', this.value)">
                        @if ($isPengelola)
                            <button class="btn btn-sm btn-pri" onclick="openCreateSop()"><i
                                    class="fa-solid fa-plus me-1"></i>Tambah SOP</button>
                        @endif
                    </div>
                </div>
                <div id="alertSop"></div>
                <div class="table-responsive">
                    <table class="tbl" id="tblSop">
                        <thead>
                            <tr>
                                <th width="40">No</th>
                                <th>Jabatan</th>
                                @if ($isPengelola)
                                    <th>Karyawan</th>
                                @endif
                                <th width="100" class="text-center">Jumlah SOP</th>
                                <th>Daftar SOP</th>
                                <th width="160" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sopRows as $jd)
                                <tr
                                    data-search="{{ strtolower(($jd->orgStructure->jabatan ?? '') . ' ' . ($jd->orgStructure->divisi ?? '') . ' ' . collect($jd->sop)->pluck('name')->implode(' ')) }}">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <strong>{{ $jd->orgStructure->jabatan ?? '-' }}</strong>
                                        @if (in_array($jd->id_org, $ownOrgIds))
                                            <span class="me">Jabatan saya</span>
                                        @endif
                                        @if ($jd->orgStructure->divisi ?? false)
                                            <br><small class="text-muted">{{ $jd->orgStructure->divisi }}</small>
                                        @endif
                                    </td>
                                    @if ($isPengelola)
                                        <td>
                                            @forelse($jd->orgStructure->karyawans ?? [] as $k)
                                                <span class="nm">{{ $k->nama_lengkap }}</span>
                                            @empty
                                                <span class="empty">—</span>
                                            @endforelse
                                        </td>
                                    @endif
                                    <td class="text-center"><span class="cnt">{{ count($jd->sop) }}</span></td>
                                    <td>
                                        <ul class="pl">
                                            @foreach (array_slice($jd->sop, 0, 3) as $s)
                                                <li>{{ Str::limit($s['name'] ?? '-', 60) }}</li>
                                            @endforeach
                                        </ul>
                                        @if (count($jd->sop) > 3)
                                            <small class="text-muted">+{{ count($jd->sop) - 3 }} lainnya</small>
                                        @endif
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <button class="btn btn-sm btn-outline-secondary"
                                            onclick="openDetailSop({{ $jd->id }})"><i class="fa-solid fa-eye"></i>
                                            Detail</button>
                                        @if ($canEditOrg($jd->id_org, true))
                                            <button class="btn btn-sm btn-outline-primary"
                                                onclick="openEditSop({{ $jd->id }})"><i
                                                    class="fa-solid fa-pen"></i> Edit</button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isPengelola ? 6 : 5 }}" class="text-center empty py-4">Belum ada
                                        data SOP</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="profile-pane">
                <div class="bar">
                    <h6>{{ $isPengelola ? 'Job Profile Karyawan Divisi' : 'Job Profile Saya' }}</h6>
                    @if ($isPengelola)
                        <input type="search" class="form-control form-control-sm search"
                            placeholder="Cari nama / jabatan..." oninput="filterRows('tblProfile', this.value)">
                    @endif
                </div>
                <div id="alertProfile"></div>
                <div class="table-responsive">
                    <table class="tbl" id="tblProfile">
                        <thead>
                            <tr>
                                <th width="200">Karyawan</th>
                                <th>Qualification</th>
                                <th>Job Description</th>
                                <th>Compensation &amp; Benefit</th>
                                @if ($isPengelola)
                                    <th width="130" class="text-center">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($profileOrgs as $org)
                                <tr class="grp">
                                    <td colspan="{{ $isPengelola ? 5 : 4 }}">
                                        {{ $org->jabatan }}
                                        @if ($org->divisi)
                                            <span class="fw-normal text-muted"> — {{ $org->divisi }}</span>
                                        @endif
                                        <span class="cnt ms-1">{{ $org->karyawans->count() }}</span>
                                    </td>
                                </tr>
                                @foreach ($org->karyawans as $k)
                                    @php
                                        $p = $k->jobProfile;
                                        $isSelf = $k->id == $karyawan->id;
                                    @endphp
                                    <tr
                                        data-search="{{ strtolower($org->jabatan . ' ' . $org->divisi . ' ' . $k->nama_lengkap) }}">
                                        <td>
                                            <strong>{{ $k->nama_lengkap }}</strong>
                                            @if ($isSelf)
                                                <span class="me">Saya</span>
                                            @endif
                                        </td>
                                        @foreach (['qualifications', 'descriptions', 'compensation_benefit'] as $f)
                                            <td>
                                                @if ($p && !empty($p->$f))
                                                    <ul class="pl">
                                                        @foreach ($p->$f as $item)
                                                            <li>{{ $item }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <span class="empty">{{ $p ? 'Belum diisi' : '—' }}</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        @if ($isPengelola)
                                            <td class="text-center">
                                                @if ($p && (!$isSelf || $bolehEditSendiri))
                                                    <button class="btn btn-sm btn-outline-primary"
                                                        onclick="openProfile({{ $k->id }}, '{{ addslashes($k->nama_lengkap) }}')"><i
                                                            class="fa-solid fa-pen"></i> Edit</button>
                                                @elseif (!$p && !$isSelf)
                                                    <button class="btn btn-sm btn-pri"
                                                        onclick="openProfile({{ $k->id }}, '{{ addslashes($k->nama_lengkap) }}')"><i
                                                            class="fa-solid fa-plus"></i> Isi</button>
                                                @elseif (!$p && $isSelf)
                                                    <small class="empty">Menunggu diisi HRD</small>
                                                @else
                                                    <small class="empty">—</small>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="{{ $isPengelola ? 5 : 4 }}" class="text-center empty py-4">Belum ada
                                        data karyawan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
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
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary btn-sm"
                        data-bs-dismiss="modal">Tutup</button></div>
            </div>
        </div>
    </div>

    @if ($isPengelola)
        <div class="modal fade" id="modalJobDesk" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <form id="formJobDesk" class="d-flex flex-column overflow-hidden">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalJobDeskTitle">Tambah Job Desk</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="modal-err"></div>
                            <input type="hidden" id="jdId">
                            <label class="flabel mt-0">Jabatan</label>
                            <select class="form-select form-select-sm" id="jdOrg" name="id_org" required>
                                <option value="">— Pilih Jabatan —</option>
                                @foreach ($creatableOrgs as $o)
                                    <option value="{{ $o->id }}">
                                        {{ $o->jabatan }}{{ $o->divisi ? ' — ' . $o->divisi : '' }}</option>
                                @endforeach
                            </select>

                            <label class="flabel">Fungsi Utama</label>
                            <textarea class="form-control form-control-sm" name="fungsi_utama" rows="3"></textarea>

                            <div class="row g-3">
                                <div class="col-md-6"><label class="flabel">Tujuan Jabatan</label>
                                    <textarea class="form-control form-control-sm" name="tujuan_jabatan" rows="2"></textarea>
                                </div>
                                <div class="col-md-6"><label class="flabel">Kualifikasi Pendidikan</label>
                                    <textarea class="form-control form-control-sm" name="kualifikasi_pendidikan" rows="2"></textarea>
                                </div>
                                <div class="col-md-6"><label class="flabel mt-0">Pengalaman Kerja</label>
                                    <textarea class="form-control form-control-sm" name="pengalaman_kerja" rows="2"></textarea>
                                </div>
                                <div class="col-md-6"><label class="flabel mt-0">Karakteristik Pribadi</label>
                                    <textarea class="form-control form-control-sm" name="karakteristik_pribadi" rows="2"></textarea>
                                </div>
                            </div>

                            <label class="flabel">Kompetensi</label>
                            <div id="jdKompetensi"></div>
                            <button type="button" class="btn btn-sm btn-link p-0" onclick="addSimple('jdKompetensi')"><i
                                    class="fa-solid fa-plus me-1"></i>Tambah Kompetensi</button>

                            <label class="flabel">Tugas &amp; Tanggung Jawab</label>
                            <div id="jdTugas"></div>
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                onclick="addParent('jdTugas')"><i class="fa-solid fa-plus me-1"></i>Tambah Tugas</button>

                            <label class="flabel">Wewenang</label>
                            <div id="jdWewenang"></div>
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                onclick="addParent('jdWewenang')"><i class="fa-solid fa-plus me-1"></i>Tambah
                                Wewenang</button>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-sm btn-pri" id="btnJobDesk"><i
                                    class="fa-solid fa-save me-1"></i>Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalSop" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <form id="formSop" class="d-flex flex-column overflow-hidden">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalSopTitle">Tambah SOP</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="modal-err"></div>
                            <input type="hidden" id="sopId">
                            <label class="flabel mt-0">Jabatan</label>
                            <select class="form-select form-select-sm" id="sopOrg" name="id_org" required>
                                <option value="">— Pilih Jabatan —</option>
                                @foreach ($creatableOrgs as $o)
                                    <option value="{{ $o->id }}">
                                        {{ $o->jabatan }}{{ $o->divisi ? ' — ' . $o->divisi : '' }}</option>
                                @endforeach
                            </select>
                            <label class="flabel">SOP</label>
                            <div id="sopContainer"></div>
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                onclick="addParent('sopContainer')"><i class="fa-solid fa-plus me-1"></i>Tambah
                                SOP</button>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-sm btn-pri" id="btnSop"><i
                                    class="fa-solid fa-save me-1"></i>Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalProfile" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <form id="formProfile" class="d-flex flex-column overflow-hidden">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalProfileTitle">Job Profile</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="modal-err"></div>
                            <input type="hidden" id="profKaryawanId" name="karyawan_id">
                            <label class="flabel mt-0">Qualification</label>
                            <div id="profQual"></div>
                            <button type="button" class="btn btn-sm btn-link p-0" onclick="addSimple('profQual')"><i
                                    class="fa-solid fa-plus me-1"></i>Tambah Qualification</button>

                            <label class="flabel">Job Description</label>
                            <div id="profDesc"></div>
                            <button type="button" class="btn btn-sm btn-link p-0" onclick="addSimple('profDesc')"><i
                                    class="fa-solid fa-plus me-1"></i>Tambah Job Description</button>

                            <label class="flabel">Compensation &amp; Benefit</label>
                            <div id="profComp"></div>
                            <button type="button" class="btn btn-sm btn-link p-0" onclick="addSimple('profComp')"><i
                                    class="fa-solid fa-plus me-1"></i>Tambah Compensation &amp; Benefit</button>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-sm btn-pri" id="btnProfile"><i
                                    class="fa-solid fa-save me-1"></i>Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const IS_PENGELOLA = @json($isPengelola);
        const orgStructures = {{ \Illuminate\Support\Js::from($orgData) }};
        const BASE = '/employee';
        let pIdx = 0;
        let isProfileUpdate = false;

        const $id = i => document.getElementById(i);
        const esc = t => t == null ? '' : String(t).replace(/[&<>"']/g, m => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        } [m]));
        const csrf = () => document.querySelector('meta[name="csrf-token"]').content;
        const showModal = id => bootstrap.Modal.getOrCreateInstance($id(id)).show();
        const getJson = url => fetch(url, {
            headers: {
                'Accept': 'application/json'
            }
        }).then(r => r.json());
        const loading =
            '<div class="text-center text-muted py-4"><i class="fa-solid fa-spinner fa-spin"></i> Memuat...</div>';

        function filterRows(tableId, q) {
            q = (q || '').toLowerCase();
            let grp = null,
                vis = 0;
            const flush = () => {
                if (grp) grp.style.display = vis ? '' : 'none';
            };
            document.querySelectorAll(`#${tableId} tbody tr`).forEach(r => {
                if (r.classList.contains('grp')) {
                    flush();
                    grp = r;
                    vis = 0;
                    return;
                }
                if (!r.dataset.search) return;
                const ok = r.dataset.search.includes(q);
                r.style.display = ok ? '' : 'none';
                if (ok) vis++;
            });
            flush();
        }

        function showAlert(id, type, msg) {
            const el = $id(id);
            el.innerHTML = `<div class="alert alert-${type === 'success' ? 'success' : 'danger'} py-2 small">${msg}</div>`;
            setTimeout(() => el.innerHTML = '', 5000);
        }

        function simpleRow(name, ph, val, n) {
            const d = document.createElement('div');
            d.className = 'irow';
            d.innerHTML =
                `<input type="text" name="${name}" class="form-control form-control-sm" value="${esc(val)}" placeholder="${ph} ${n}">
                <button type="button" class="btn btn-sm btn-light text-danger" title="Hapus" onclick="removeSimple(this)"><i class="fa-solid fa-xmark"></i></button>`;
            return d;
        }

        function fillSimple(id, name, ph, arr) {
            const c = $id(id);
            c.dataset.name = name;
            c.dataset.ph = ph;
            c.innerHTML = '';
            (arr && arr.length ? arr : ['']).forEach(v => c.appendChild(simpleRow(name, ph, v, c.children.length + 1)));
        }

        function addSimple(id) {
            const c = $id(id);
            c.appendChild(simpleRow(c.dataset.name, c.dataset.ph, '', c.children.length + 1));
        }

        function removeSimple(b) {
            const r = b.closest('.irow'),
                c = r.parentElement;
            r.remove();
            if (!c.children.length) c.appendChild(simpleRow(c.dataset.name, c.dataset.ph, '', 1));
        }

        function detailRow(card, val, n) {
            const d = document.createElement('div');
            d.className = 'irow';
            d.innerHTML =
                `<span class="text-muted small" style="min-width:20px">${n}.</span>
                <input type="text" name="${card.dataset.field}[${card.dataset.i}][details][]" class="form-control form-control-sm" value="${esc(val)}" placeholder="${card.dataset.dph} ${n}">
                <button type="button" class="btn btn-sm btn-light text-danger" title="Hapus" onclick="removeDetail(this)"><i class="fa-solid fa-xmark"></i></button>`;
            return d;
        }

        function parentCard(field, pph, dph, item) {
            const i = pIdx++;
            const d = document.createElement('div');
            d.className = 'pgroup';
            Object.assign(d.dataset, {
                i,
                field,
                dph
            });
            d.innerHTML =
                `<div class="phead">
                    <input type="text" name="${field}[${i}][name]" class="form-control form-control-sm fw-semibold" value="${esc(item && item.name)}" placeholder="${pph}">
                    <button type="button" class="btn btn-sm btn-light text-danger" title="Hapus" onclick="removeParent(this)"><i class="fa-solid fa-trash"></i></button>
                </div>
                <div class="dwrap"></div>
                <button type="button" class="btn btn-sm btn-link p-0 mt-1" onclick="addDetail(this)"><i class="fa-solid fa-plus me-1"></i>Tambah ${dph}</button>`;
            const w = d.querySelector('.dwrap');
            (item && item.details && item.details.length ? item.details : ['']).forEach(v => w.appendChild(detailRow(d, v, w
                .children.length + 1)));
            return d;
        }

        function fillParent(id, field, pph, dph, arr) {
            const c = $id(id);
            Object.assign(c.dataset, {
                field,
                pph,
                dph
            });
            c.innerHTML = '';
            (arr && arr.length ? arr : [null]).forEach(it => c.appendChild(parentCard(field, pph, dph, it)));
        }

        function addParent(id) {
            const c = $id(id);
            c.appendChild(parentCard(c.dataset.field, c.dataset.pph, c.dataset.dph, null));
        }

        function removeParent(b) {
            const g = b.closest('.pgroup'),
                c = g.parentElement;
            g.remove();
            if (!c.children.length) c.appendChild(parentCard(c.dataset.field, c.dataset.pph, c.dataset.dph, null));
        }

        function addDetail(b) {
            const g = b.closest('.pgroup'),
                w = g.querySelector('.dwrap');
            w.appendChild(detailRow(g, '', w.children.length + 1));
        }

        function removeDetail(b) {
            const r = b.closest('.irow'),
                w = r.parentElement,
                g = w.closest('.pgroup');
            r.remove();
            if (!w.children.length) w.appendChild(detailRow(g, '', 1));
        }

        function post(url, fd, put) {
            if (put) fd.append('_method', 'PUT');
            return fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf(),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: fd
            }).then(async r => {
                const d = await r.json().catch(() => ({}));
                if (!r.ok && !d.message && !d.errors) d.message = 'Error ' + r.status;
                return d;
            });
        }

        function bindForm(formId, btnId, modalId, alertId, target) {
            const form = $id(formId);
            if (!form) return;
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const btn = $id(btnId),
                    old = btn.innerHTML,
                    err = form.querySelector('.modal-err');
                const [url, put] = target();
                err.innerHTML = '';
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Menyimpan...';
                post(url, new FormData(this), put).then(d => {
                        if (d.success) {
                            bootstrap.Modal.getInstance($id(modalId)).hide();
                            showAlert(alertId, 'success', esc(d.message));
                            setTimeout(() => location.reload(), 900);
                        } else {
                            let m = esc(d.message || 'Terjadi kesalahan');
                            if (d.errors) m += '<br>' + Object.values(d.errors).flat().map(esc).join('<br>');
                            err.innerHTML = `<div class="alert alert-danger">${m}</div>`;
                        }
                    }).catch(e => err.innerHTML =
                        `<div class="alert alert-danger">Gagal menyimpan: ${esc(e.message)}</div>`)
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = old;
                    });
            });
        }

        function hier(title, arr) {
            let h = `<div class="sec">${title}</div>`;
            if (arr && arr.length) {
                h += '<ol class="hl">';
                arr.forEach(i => {
                    h += `<li><strong>${esc(i.name || '-')}</strong>`;
                    if (i.details && i.details.length) h += '<ul>' + i.details.map(d => `<li>${esc(d)}</li>`).join(
                        '') + '</ul>';
                    h += '</li>';
                });
                h += '</ol>';
            } else {
                h += '<div class="empty">-</div>';
            }
            return h;
        }

        function headInfo(data) {
            const org = orgStructures.find(o => o.id == data.id_org) || data.org_structure || {};
            const names = (org.karyawans || []).map(k => k.nama_lengkap).join(', ');
            return `<table class="kv">
                <tr><th>Jabatan</th><td><strong>${esc(org.jabatan || '—')}</strong></td></tr>
                <tr><th>Divisi</th><td>${esc(org.divisi || '—')}</td></tr>
                ${IS_PENGELOLA ? `<tr><th>Karyawan</th><td>${esc(names || '—')}</td></tr>` : ''}
            </table>`;
        }

        function openDetailJobDesk(id) {
            $id('modalDetailTitle').textContent = 'Detail Job Desk';
            $id('detailContent').innerHTML = loading;
            showModal('modalDetail');
            getJson(`${BASE}/job-desk/api/${id}`).then(d => {
                if (d.error) {
                    $id('detailContent').innerHTML = '<div class="empty">Tidak dapat memuat data</div>';
                    return;
                }
                const komp = (d.kompetensi || []).map(k => `<span class="tag">${esc(k)}</span>`).join('') ||
                    '<span class="empty">-</span>';
                $id('detailContent').innerHTML = headInfo(d) +
                    `<div class="sec">Fungsi Utama</div><p class="mb-0">${d.fungsi_utama ? esc(d.fungsi_utama) : '<span class="empty">Belum diisi</span>'}</p>
                    <div class="sec">Spesifikasi Jabatan</div>
                    <table class="kv">
                        <tr><th>Tujuan</th><td>${esc(d.tujuan_jabatan || '-')}</td></tr>
                        <tr><th>Pendidikan</th><td>${esc(d.kualifikasi_pendidikan || '-')}</td></tr>
                        <tr><th>Pengalaman</th><td>${esc(d.pengalaman_kerja || '-')}</td></tr>
                        <tr><th>Karakteristik</th><td>${esc(d.karakteristik_pribadi || '-')}</td></tr>
                        <tr><th>Kompetensi</th><td>${komp}</td></tr>
                    </table>` +
                    hier('Tugas dan Tanggung Jawab', d.tugas_tanggung_jawab) + hier('Wewenang', d.wewenang);
            }).catch(() => $id('detailContent').innerHTML = '<div class="empty">Gagal memuat data</div>');
        }

        function openDetailSop(id) {
            $id('modalDetailTitle').textContent = 'Detail SOP';
            $id('detailContent').innerHTML = loading;
            showModal('modalDetail');
            getJson(`${BASE}/job-desk/api/${id}`).then(d => {
                if (d.error) {
                    $id('detailContent').innerHTML = '<div class="empty">Tidak dapat memuat data</div>';
                    return;
                }
                $id('detailContent').innerHTML = headInfo(d) + hier('Standard Operating Procedure (SOP)', d.sop);
            }).catch(() => $id('detailContent').innerHTML = '<div class="empty">Gagal memuat data</div>');
        }

        function resetOrgSelect(sel, orgId) {
            sel.querySelectorAll('.extra-opt').forEach(o => o.remove());
            const known = [...sel.options].some(o => o.value == orgId);
            if (orgId && !known) {
                const org = orgStructures.find(o => o.id == orgId);
                const op = document.createElement('option');
                op.value = orgId;
                op.className = 'extra-opt';
                op.textContent = org ? org.jabatan + (org.divisi ? ' — ' + org.divisi : '') : orgId;
                sel.appendChild(op);
            }
            sel.value = orgId || '';
            sel.disabled = !!orgId;
        }

        function openCreateJobDesk() {
            const f = $id('formJobDesk');
            f.reset();
            f.querySelector('.modal-err').innerHTML = '';
            $id('jdId').value = '';
            resetOrgSelect($id('jdOrg'), '');
            $id('modalJobDeskTitle').textContent = 'Tambah Job Desk';
            fillSimple('jdKompetensi', 'kompetensi[]', 'Kompetensi', []);
            fillParent('jdTugas', 'tugas_tanggung_jawab', 'Tugas Utama', 'Detail Tugas', []);
            fillParent('jdWewenang', 'wewenang', 'Wewenang Utama', 'Detail Wewenang', []);
            showModal('modalJobDesk');
        }

        function openEditJobDesk(id) {
            const f = $id('formJobDesk');
            f.reset();
            f.querySelector('.modal-err').innerHTML = '';
            $id('jdId').value = id;
            $id('modalJobDeskTitle').textContent = 'Edit Job Desk';
            fillSimple('jdKompetensi', 'kompetensi[]', 'Kompetensi', []);
            fillParent('jdTugas', 'tugas_tanggung_jawab', 'Tugas Utama', 'Detail Tugas', []);
            fillParent('jdWewenang', 'wewenang', 'Wewenang Utama', 'Detail Wewenang', []);
            showModal('modalJobDesk');
            getJson(`${BASE}/job-desk/api/${id}`).then(d => {
                if (d.error) {
                    f.querySelector('.modal-err').innerHTML =
                        '<div class="alert alert-danger">Tidak berwenang atau data tidak ditemukan</div>';
                    return;
                }
                resetOrgSelect($id('jdOrg'), d.id_org);
                ['fungsi_utama', 'tujuan_jabatan', 'kualifikasi_pendidikan', 'pengalaman_kerja',
                    'karakteristik_pribadi'
                ]
                .forEach(n => f.querySelector(`[name="${n}"]`).value = d[n] || '');
                fillSimple('jdKompetensi', 'kompetensi[]', 'Kompetensi', d.kompetensi);
                fillParent('jdTugas', 'tugas_tanggung_jawab', 'Tugas Utama', 'Detail Tugas', d
                .tugas_tanggung_jawab);
                fillParent('jdWewenang', 'wewenang', 'Wewenang Utama', 'Detail Wewenang', d.wewenang);
            }).catch(() => f.querySelector('.modal-err').innerHTML =
                '<div class="alert alert-danger">Gagal memuat data Job Desk</div>');
        }

        function openCreateSop() {
            const f = $id('formSop');
            f.reset();
            f.querySelector('.modal-err').innerHTML = '';
            $id('sopId').value = '';
            resetOrgSelect($id('sopOrg'), '');
            $id('modalSopTitle').textContent = 'Tambah SOP';
            fillParent('sopContainer', 'sop', 'SOP Utama', 'Detail SOP', []);
            showModal('modalSop');
        }

        function openEditSop(id) {
            const f = $id('formSop');
            f.reset();
            f.querySelector('.modal-err').innerHTML = '';
            $id('sopId').value = id;
            $id('modalSopTitle').textContent = 'Edit SOP';
            fillParent('sopContainer', 'sop', 'SOP Utama', 'Detail SOP', []);
            showModal('modalSop');
            getJson(`${BASE}/job-desk/api/${id}`).then(d => {
                if (d.error) {
                    f.querySelector('.modal-err').innerHTML =
                        '<div class="alert alert-danger">Tidak berwenang atau data tidak ditemukan</div>';
                    return;
                }
                resetOrgSelect($id('sopOrg'), d.id_org);
                fillParent('sopContainer', 'sop', 'SOP Utama', 'Detail SOP', d.sop);
            }).catch(() => f.querySelector('.modal-err').innerHTML =
                '<div class="alert alert-danger">Gagal memuat data SOP</div>');
        }

        function openProfile(karyawanId, nama) {
            const f = $id('formProfile');
            f.reset();
            f.querySelector('.modal-err').innerHTML = '';
            $id('profKaryawanId').value = karyawanId;
            $id('modalProfileTitle').textContent = 'Job Profile — ' + nama;
            fillSimple('profQual', 'qualifications[]', 'Qualification', []);
            fillSimple('profDesc', 'descriptions[]', 'Job Description', []);
            fillSimple('profComp', 'compensation_benefit[]', 'Compensation & Benefit', []);
            showModal('modalProfile');
            getJson(`${BASE}/karyawan-profile/${karyawanId}`).then(d => {
                isProfileUpdate = !!(d && d.id);
                $id('modalProfileTitle').textContent = (isProfileUpdate ? 'Edit' : 'Tambah') + ' Job Profile — ' +
                    nama;
                if (isProfileUpdate) {
                    fillSimple('profQual', 'qualifications[]', 'Qualification', d.qualifications);
                    fillSimple('profDesc', 'descriptions[]', 'Job Description', d.descriptions);
                    fillSimple('profComp', 'compensation_benefit[]', 'Compensation & Benefit', d
                        .compensation_benefit);
                }
            }).catch(() => isProfileUpdate = false);
        }

        bindForm('formJobDesk', 'btnJobDesk', 'modalJobDesk', 'alertJobDesk', () => {
            const id = $id('jdId').value;
            return [id ? `${BASE}/job-desk/${id}` : `${BASE}/job-desk/store`, !!id];
        });
        bindForm('formSop', 'btnSop', 'modalSop', 'alertSop', () => {
            const id = $id('sopId').value;
            return [id ? `${BASE}/sop/${id}` : `${BASE}/sop/store`, !!id];
        });
        bindForm('formProfile', 'btnProfile', 'modalProfile', 'alertProfile', () => {
            const id = $id('profKaryawanId').value;
            return [isProfileUpdate ? `${BASE}/karyawan-profile/${id}` : `${BASE}/karyawan-profile`,
                isProfileUpdate];
        });
    </script>
@endsection
