@extends('layout_HR.app')
@section('content_HR')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: .25rem;
        }

        .page-subtitle {
            font-size: .9rem;
            color: var(--secondary);
        }

        .nav-tabs .nav-link {
            color: #64748b;
            font-weight: 500;
            border: none;
            border-bottom: 3px solid transparent;
            padding: .75rem 1.25rem;
        }

        .nav-tabs .nav-link.active {
            color: #0d6efd;
            background: transparent;
            border-bottom: 3px solid #0d6efd;
        }

        .table tfoot tr {
            background-color: #f8f9fa;
            font-weight: 700;
            color: #334155;
        }

        .filter-card {
            border: 1px solid #e2e8f0;
            border-radius: .75rem;
            background: rgba(255, 255, 255, .85);
            backdrop-filter: blur(10px);
        }

        .btn-success {
            background-color: #10b981 !important;
            border-color: #10b981 !important;
            color: #fff !important;
        }

        .btn-primary {
            background-color: #4f46e5 !important;
            border-color: #4f46e5 !important;
            color: #fff !important;
        }

        .blob-bg {
            position: absolute;
            inset: 0;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .blob-1 {
            position: absolute;
            top: -50px;
            right: -50px;
            width: 450px;
            height: 450px;
            background: linear-gradient(135deg, rgba(56, 189, 248, .15), rgba(99, 102, 241, .15));
            border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%;
            animation: blobAnim 12s ease-in-out infinite alternate;
        }

        @keyframes blobAnim {
            0% {
                transform: scale(1) rotate(0);
            }

            100% {
                transform: scale(1.1) translate(-30px, 30px) rotate(15deg);
            }
        }

        .content-wrapper-relative {
            position: relative;
            z-index: 1;
        }

        /* Card & elemen yang bisa diklik untuk detail */
        .stat-card {
            cursor: pointer;
            border: 1px solid #e2e8f0;
            transition: border-color .15s, box-shadow .15s;
            height: 100%;
        }

        .stat-card:hover {
            border-color: #4f46e5;
            box-shadow: 0 4px 14px rgba(79, 70, 229, .12);
        }

        .stat-card .lbl {
            font-size: .78rem;
            color: #64748b;
        }

        .stat-card .val {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.3;
        }

        .stat-card .sub {
            font-size: .75rem;
            color: #64748b;
        }

        .stat-card .hint {
            font-size: .7rem;
            color: #4f46e5;
            opacity: 0;
            transition: opacity .15s;
        }

        .stat-card:hover .hint {
            opacity: 1;
        }

        .clickable {
            cursor: pointer;
        }

        .clickable:hover {
            background: #f1f5f9;
        }

        .up {
            color: #dc2626;
        }

        .down {
            color: #059669;
        }

        /* naik biaya = merah, turun = hijau */

        /* Pilih data jurnal dengan klik / drag */
        #jBody {
            user-select: none;
        }

        #jBody tr[data-i] {
            cursor: pointer;
        }

        #jBody tr.sel {
            background: #e0e7ff !important;
            box-shadow: inset 3px 0 0 #4f46e5;
        }

        #jBody tr.done {
            opacity: .5;
            cursor: not-allowed;
        }

        .scroll-box {
            max-height: 36vh;
            overflow: auto;
            border: 1px solid #e2e8f0;
            border-radius: .5rem;
        }

        .scroll-box thead th {
            position: sticky;
            top: 0;
            background: #f8fafc;
            z-index: 1;
        }

        #jEdit input,
        #jEdit select {
            min-width: 110px;
        }

        #jEdit .bad {
            border-color: #dc2626;
        }

        /* Dropdown Export/Import tanpa Popper */
        .header-actions {
            position: relative;
            z-index: 20;
        }

        #expWrap {
            position: relative;
        }

        .exp-menu {
            right: 0;
            left: auto;
            top: 100%;
            margin-top: .25rem;
            min-width: 12rem;
            z-index: 1060;
        }

        .exp-menu.show {
            display: block;
        }

        /* Modal Panduan */
        #modalPanduan {
            --pri: #4f46e5;
            --pri-light: #eef2ff;
            --pri-dark: #3730a3;
            --danger-light: #fee2e2;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-400: #9ca3af;
            --gray-700: #374151;
            --gray-900: #111827;
        }

        #modalPanduan .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .1), 0 4px 10px rgba(0, 0, 0, .07);
        }

        #modalPanduan .modal-header-custom {
            background: linear-gradient(135deg, var(--pri) 0%, var(--pri-dark) 100%);
            color: #fff;
            border-radius: 12px 12px 0 0;
            padding: 1.1rem 1.5rem;
        }

        #modalPanduan .modal-header-custom .modal-title {
            font-weight: 700;
            font-size: 1rem;
        }

        #modalPanduan .modal-header-custom .btn-close {
            filter: brightness(0) invert(1);
        }

        #modalPanduan .nav-tabs-custom {
            border-bottom: 2px solid var(--gray-200);
        }

        #modalPanduan .nav-tabs-custom .nav-link {
            border: none;
            color: var(--gray-400);
            font-weight: 600;
            padding: .85rem 1.25rem;
            font-size: .875rem;
            transition: color .2s;
            display: flex;
            align-items: center;
            gap: .5rem;
            background: transparent;
        }

        #modalPanduan .nav-tabs-custom .nav-link:hover {
            color: var(--pri);
        }

        #modalPanduan .nav-tabs-custom .nav-link.active {
            color: var(--pri);
            border-bottom: 3px solid var(--pri);
            background: transparent;
        }

        #modalPanduan .guide-section {
            margin-bottom: 2rem;
        }

        #modalPanduan .guide-section h5 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--gray-900);
            border-left: 4px solid var(--pri);
            padding-left: .75rem;
            margin-bottom: .75rem;
        }

        #modalPanduan .guide-section ol,
        #modalPanduan .guide-section ul {
            padding-left: 1.25rem;
            margin-bottom: .75rem;
        }

        #modalPanduan .guide-section li {
            margin-bottom: .4rem;
            font-size: .9rem;
            color: var(--gray-700);
            line-height: 1.55;
        }

        #modalPanduan .guide-section p {
            font-size: .9rem;
            color: var(--gray-700);
            line-height: 1.55;
        }

        #modalPanduan .guide-note {
            background: var(--pri-light);
            border-radius: 8px;
            padding: .75rem 1rem;
            font-size: .85rem;
            color: var(--gray-700);
        }

        #modalPanduan .guide-example {
            background: var(--gray-50);
            border: 1px dashed var(--gray-400);
            border-radius: 8px;
            padding: 1rem;
            font-size: .85rem;
        }

        #modalPanduan .guide-itsm {
            background: var(--danger-light);
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 1.25rem;
        }

        #modalPanduan .guide-qa {
            margin-bottom: 1rem;
        }

        #modalPanduan .guide-qa strong {
            display: block;
            font-size: .9rem;
            color: var(--gray-900);
            margin-bottom: .2rem;
        }

        #modalPanduan .guide-qa span {
            font-size: .88rem;
            color: var(--gray-700);
            line-height: 1.55;
        }
    </style>

    <div class="blob-bg">
        <div class="blob-1"></div>
    </div>

    <div class="container-fluid px-4 py-4 me-3 ms-3 content-wrapper-relative">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm">
                <i class="fa-solid fa-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm">
                <i class="fa-solid fa-circle-exclamation me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm">
                <strong>Data belum bisa disimpan:</strong>
                <ul class="mb-0 mt-1">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- HEADER --}}
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
            <div>
                <h1 class="page-title">Maintenance</h1>
                <p class="page-subtitle mb-0">Catat biaya maintenance kendaraan dan kantor, lalu bandingkan per bulan,
                    quarter, dan tahun.</p>
                <p class="page-subtitle fw-semibold text-dark mb-0">{{ now()->translatedFormat('l, d F Y') }}</p>
            </div>
            <div class="d-flex flex-nowrap align-items-center gap-2 flex-shrink-0 header-actions">
                <button class="btn btn-light border" data-bs-toggle="modal" data-bs-target="#modalPanduan"
                    title="Panduan fitur">
                    <i class="fa-regular fa-circle-question"></i><span class="d-none d-xl-inline ms-1">Panduan</span>
                </button>
                <button class="btn btn-primary text-nowrap" id="btnAdd"><i class="fa-solid fa-plus me-1"></i> Tambah
                    manual</button>
                <button class="btn btn-info text-white text-nowrap" data-bs-toggle="modal" data-bs-target="#modalJurnal"><i
                        class="fa-solid fa-book me-1"></i> Ambil dari jurnal</button>
                <div class="position-relative" id="expWrap">
                    <button type="button" class="btn btn-outline-secondary text-nowrap" id="expToggle"
                        aria-expanded="false">
                        <i class="fa-solid fa-file-export me-1"></i> Export / Import <i
                            class="fa-solid fa-chevron-down ms-1 small"></i>
                    </button>
                    <ul class="dropdown-menu shadow exp-menu" id="expMenu">
                        <li><a class="dropdown-item" href="{{ route('HR.maintenance.export_pdf', request()->query()) }}"
                                target="_blank"><i class="fa-solid fa-file-pdf fa-fw me-2 text-secondary"></i>Export PDF</a>
                        </li>
                        <li><a class="dropdown-item"
                                href="{{ route('HR.maintenance.export_excel', request()->query()) }}"><i
                                    class="fa-solid fa-file-excel fa-fw me-2 text-success"></i>Export Excel</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><button type="button" class="dropdown-item" data-bs-toggle="modal"
                                data-bs-target="#modalImportExcel"><i
                                    class="fa-solid fa-file-import fa-fw me-2 text-primary"></i>Import dari Excel</button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- FILTER --}}
        <div class="card filter-card mb-4">
            <div class="card-body p-3">
                <form id="form-filter" class="row g-3 align-items-end" method="GET"
                    action="{{ route('HR.maintenance.index') }}">
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-semibold">Tahun</label>
                        <select class="form-select" id="filter_tahun" name="tahun">
                            @foreach ($tahunList as $y)
                                <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold">Section</label>
                        <select class="form-select" id="filter_section" name="section">
                            <option value="">Semua section</option>
                            @foreach (\App\Models\Maintenance::SECTIONS as $s)
                                <option value="{{ $s }}" {{ request('section') == $s ? 'selected' : '' }}>
                                    {{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold">Jenis maintenance</label>
                        <select class="form-select" name="jenis">
                            <option value="">Semua jenis</option>
                            @foreach ($jenisList as $j)
                                <option value="{{ $j }}" {{ request('jenis') == $j ? 'selected' : '' }}>
                                    {{ $j }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-semibold">Bulan (tabel data)</label>
                        <select class="form-select" name="bulan">
                            <option value="">Semua bulan</option>
                            @foreach ($bulanFull as $i => $b)
                                <option value="{{ $i + 1 }}" {{ request('bulan') == $i + 1 ? 'selected' : '' }}>
                                    {{ $b }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1"><i class="fa-solid fa-filter me-1"></i>
                            Terapkan</button>
                        <a href="{{ route('HR.maintenance.index') }}" class="btn btn-light border" title="Reset"><i
                                class="fa-solid fa-arrows-rotate"></i></a>
                    </div>
                </form>
                <div class="small text-muted mt-2">Card dan grafik mengikuti filter <strong>Tahun</strong> dan
                    <strong>Section</strong>. Filter jenis dan bulan hanya untuk tabel data.</div>
            </div>
        </div>

        {{-- CARD RINGKASAN (klik untuk detail) --}}
        <div class="row g-3 mb-4">
            @php
                $cards = [
                    ['id' => 'total', 'type' => 'total', 'key' => '', 'label' => 'Total biaya'],
                    ['id' => 'count', 'type' => 'count', 'key' => '', 'label' => 'Jumlah transaksi'],
                    ['id' => 'avg', 'type' => 'average', 'key' => '', 'label' => 'Rata-rata per transaksi'],
                    ['id' => 'max', 'type' => 'max', 'key' => '', 'label' => 'Transaksi terbesar'],
                    [
                        'id' => 'kend',
                        'type' => 'section',
                        'key' => \App\Models\Maintenance::KENDARAAN,
                        'label' => 'Kendaraan',
                    ],
                    [
                        'id' => 'kantor',
                        'type' => 'section',
                        'key' => \App\Models\Maintenance::KANTOR,
                        'label' => 'Kantor',
                    ],
                    ['id' => 'growth', 'type' => 'growth', 'key' => '', 'label' => 'Vs tahun lalu'],
                ];
            @endphp
            @foreach ($cards as $c)
                <div class="col-6 col-md-4 col-xl">
                    <div class="card stat-card" data-detail="{{ $c['type'] }}" data-key="{{ $c['key'] }}"
                        data-label="{{ $c['label'] }}">
                        <div class="card-body py-3">
                            <div class="lbl">{{ $c['label'] }}</div>
                            <div class="val" id="c_{{ $c['id'] }}">...</div>
                            <div class="sub" id="s_{{ $c['id'] }}">&nbsp;</div>
                            <div class="hint mt-1">Lihat detail <i class="fa-solid fa-chevron-right"></i></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- TAB UTAMA --}}
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <ul class="nav nav-tabs px-4 pt-3">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-data"
                            type="button"><i class="fa-solid fa-table-list me-1"></i> Data maintenance</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-compare"
                            type="button"><i class="fa-solid fa-scale-balanced me-1"></i> Comparison biaya</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-trend"
                            type="button"><i class="fa-solid fa-chart-line me-1"></i> Tren biaya</button></li>
                </ul>

                <div class="tab-content p-4">

                    {{-- ========== TAB DATA ========== --}}
                    <div class="tab-pane fade show active" id="tab-data">
                        @php
                            $panes = [
                                'kend' => [
                                    'label' => 'Maintenance Kendaraan',
                                    'icon' => 'fa-car',
                                    'rows' => $kendaraan,
                                ],
                                'kantor' => [
                                    'label' => 'Maintenance Kantor',
                                    'icon' => 'fa-building',
                                    'rows' => $kantor,
                                ],
                            ];
                        @endphp
                        <ul class="nav nav-pills mb-3">
                            @foreach ($panes as $k => $p)
                                <li class="nav-item">
                                    <button class="nav-link {{ $loop->first ? 'active' : '' }}" data-bs-toggle="pill"
                                        data-bs-target="#pane-{{ $k }}" type="button">
                                        <i class="fa-solid {{ $p['icon'] }} me-1"></i> {{ $p['label'] }}
                                        <span class="badge bg-light text-dark ms-1">{{ $p['rows']->count() }}</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                        <div class="tab-content">
                            @foreach ($panes as $k => $p)
                                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                    id="pane-{{ $k }}">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="5%">NO</th>
                                                    <th>BULAN</th>
                                                    <th>JENIS MAINTENANCE</th>
                                                    <th>TANGGAL</th>
                                                    <th>NO. KK</th>
                                                    <th>KETERANGAN</th>
                                                    <th class="text-end">BIAYA</th>
                                                    <th class="text-center" width="10%">AKSI</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($p['rows'] as $i => $item)
                                                    <tr>
                                                        <td>{{ $i + 1 }}</td>
                                                        <td>{{ $bulanFull[$item->bulan - 1] ?? '-' }}</td>
                                                        <td class="fw-semibold">{{ $item->jenis_maintenance }}</td>
                                                        <td>{{ $item->tanggal->translatedFormat('d F Y') }}</td>
                                                        <td class="text-primary fw-semibold">{{ $item->no_kk ?: '-' }}
                                                        </td>
                                                        <td>{{ $item->keterangan ?: '-' }}</td>
                                                        <td class="text-end fw-bold text-success">Rp
                                                            {{ number_format($item->biaya, 0, ',', '.') }}</td>
                                                        <td class="text-center text-nowrap">
                                                            <button class="btn btn-sm btn-outline-primary btn-edit"
                                                                data-item="{{ json_encode(['id' => $item->id, 'tanggal' => $item->tanggal->format('Y-m-d'), 'jenis_maintenance' => $item->jenis_maintenance, 'section' => $item->section, 'biaya' => $item->biaya, 'no_kk' => $item->no_kk, 'keterangan' => $item->keterangan]) }}">
                                                                <i class="fa-solid fa-pen"></i></button>
                                                            <form
                                                                action="{{ route('HR.maintenance.destroy', $item->id) }}"
                                                                method="POST" class="d-inline"
                                                                onsubmit="return confirm('Hapus data maintenance ini?')">
                                                                @csrf @method('DELETE')
                                                                <button class="btn btn-sm btn-outline-danger"><i
                                                                        class="fa-solid fa-trash"></i></button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="8" class="text-center py-4 text-muted">Belum ada
                                                            data. Klik "Tambah manual" atau "Ambil dari jurnal".</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="6" class="text-end">TOTAL BIAYA:</td>
                                                    <td class="text-end">Rp
                                                        {{ number_format($p['rows']->sum('biaya'), 0, ',', '.') }}</td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- ========== TAB COMPARISON ========== --}}
                    <div class="tab-pane fade" id="tab-compare">
                        <ul class="nav nav-pills mb-3">
                            <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill"
                                    data-bs-target="#cmp-bulan" type="button">Per bulan</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill"
                                    data-bs-target="#cmp-quarter" type="button">Per quarter</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill"
                                    data-bs-target="#cmp-tahun" type="button">Per tahun</button></li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="cmp-bulan">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold mb-0">Biaya per bulan: tahun ini vs tahun lalu</h6>
                                    <button class="btn btn-sm btn-outline-primary" data-detail="growth"
                                        data-label="Pertumbuhan biaya">Detail pertumbuhan</button>
                                </div>
                                <div id="chartBulan"></div>
                                <div class="table-responsive mt-3">
                                    <table class="table table-sm table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Bulan</th>
                                                <th class="text-end">Kendaraan</th>
                                                <th class="text-end">Kantor</th>
                                                <th class="text-end">Total</th>
                                                <th class="text-end">Tahun lalu</th>
                                                <th class="text-end">Selisih</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tblBulan"></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="cmp-quarter">
                                <h6 class="fw-bold mb-2">Biaya per quarter (triwulan)</h6>
                                <div class="row g-3 mb-3" id="quarterCards"></div>
                                <div id="chartQuarter"></div>
                            </div>
                            <div class="tab-pane fade" id="cmp-tahun">
                                <h6 class="fw-bold mb-2">Biaya per tahun (Kendaraan vs Kantor)</h6>
                                <div id="chartTahun"></div>
                                <div class="table-responsive mt-3">
                                    <table class="table table-sm table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Tahun</th>
                                                <th class="text-end">Transaksi</th>
                                                <th class="text-end">Kendaraan</th>
                                                <th class="text-end">Kantor</th>
                                                <th class="text-end">Total</th>
                                                <th class="text-end">Vs tahun sebelumnya</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tblTahun"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ========== TAB TREN ========== --}}
                    <div class="tab-pane fade" id="tab-trend">
                        <div class="row g-4">
                            <div class="col-lg-8">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="fw-bold">Tren biaya bulanan</h6>
                                            <button class="btn btn-sm btn-outline-primary" data-detail="total"
                                                data-label="Total biaya">Detail</button>
                                        </div>
                                        <div id="chartTren"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="fw-bold">Komposisi section</h6>
                                            <button class="btn btn-sm btn-outline-primary" data-detail="count"
                                                data-label="Jumlah transaksi">Detail</button>
                                        </div>
                                        <div id="chartSection"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="fw-bold">Biaya per jenis maintenance</h6>
                                            <button class="btn btn-sm btn-outline-primary" data-detail="average"
                                                data-label="Rata-rata biaya">Detail</button>
                                        </div>
                                        <div id="chartJenis"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="fw-bold">Akumulasi biaya (YTD)</h6>
                                            <button class="btn btn-sm btn-outline-primary" data-detail="max"
                                                data-label="Transaksi terbesar">Detail</button>
                                        </div>
                                        <div id="chartYTD"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="small text-muted mt-3"><i class="fa-regular fa-hand-pointer me-1"></i> Klik batang,
                            titik, atau potongan grafik untuk melihat detailnya.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <datalist id="dlJenis">
        @foreach ($jenisList as $j)
            <option value="{{ $j }}">
        @endforeach
    </datalist>

    {{-- ===================== MODAL: FORM TAMBAH / EDIT ===================== --}}
    <div class="modal fade" id="modalForm" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <form id="formMaint" method="POST" action="{{ route('HR.maintenance.store') }}">
                    @csrf
                    <input type="hidden" name="_method" value="PUT" id="methodField" disabled>
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="formTitle">Tambah data maintenance</h5><button type="button"
                            class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal</label>
                                <input type="date" class="form-control" name="tanggal" id="f_tanggal" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Bulan</label>
                                <input type="text" class="form-control bg-light" id="f_bulan" readonly
                                    placeholder="Otomatis dari tanggal">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Section</label>
                                <select class="form-select" name="section" id="f_section" required>
                                    @foreach (\App\Models\Maintenance::SECTIONS as $s)
                                        <option value="{{ $s }}">{{ $s }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Jenis maintenance</label>
                                <input type="text" class="form-control" name="jenis_maintenance" id="f_jenis"
                                    list="dlJenis" maxlength="150" placeholder="Contoh: Servis berkala" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Harga / biaya (Rp)</label>
                                <input type="number" class="form-control" name="biaya" id="f_biaya" min="0"
                                    step="1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">No. KK</label>
                                <input type="text" class="form-control" name="no_kk" id="f_nokk" maxlength="50"
                                    placeholder="KK-0001">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Keterangan</label>
                                <textarea class="form-control" name="keterangan" id="f_ket" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>
                            Simpan data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===================== MODAL: AMBIL DARI JURNAL ===================== --}}
    <div class="modal fade" id="modalJurnal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-book me-2"></i> Ambil data dari jurnal akuntansi
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light border small py-2">
                        <strong>Langkah 1:</strong> pilih baris jurnal. Klik satu baris, <strong>tahan lalu geser</strong>
                        untuk memblok banyak baris sekaligus, atau <strong>Shift + klik</strong> untuk memilih rentang.
                        <strong>Langkah 2:</strong> lengkapi Section dan Jenis maintenance di tabel bawah. Tanggal, No. KK,
                        biaya, dan keterangan sudah terisi dari jurnal.
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-6 col-md-2"><input type="date" class="form-control form-control-sm"
                                id="j_start"></div>
                        <div class="col-6 col-md-2"><input type="date" class="form-control form-control-sm"
                                id="j_end"></div>
                        <div class="col-12 col-md-4"><input type="search" class="form-control form-control-sm"
                                id="j_q" placeholder="Cari keterangan atau No. KK"></div>
                        <div class="col-12 col-md-4 d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary" id="j_all">Pilih semua yang tampil</button>
                            <button class="btn btn-sm btn-outline-secondary" id="j_none">Kosongkan pilihan</button>
                        </div>
                    </div>

                    <div class="scroll-box mb-1">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th width="4%"></th>
                                    <th width="11%">Tanggal</th>
                                    <th width="12%">No. KK</th>
                                    <th>Keterangan</th>
                                    <th class="text-end" width="14%">Biaya</th>
                                </tr>
                            </thead>
                            <tbody id="jBody">
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Memuat...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="small text-muted mb-3" id="jInfo"></div>

                    <div id="jStep2" class="d-none">
                        <div class="d-flex flex-wrap align-items-end gap-2 mb-2">
                            <div><label class="form-label small fw-semibold mb-0">Section (untuk semua yang
                                    dipilih)</label>
                                <select class="form-select form-select-sm" id="bulk_section">
                                    <option value="">-- pilih --</option>
                                    @foreach (\App\Models\Maintenance::SECTIONS as $s)
                                        <option value="{{ $s }}">{{ $s }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div><label class="form-label small fw-semibold mb-0">Jenis maintenance (untuk semua)</label>
                                <input type="text" class="form-control form-control-sm" id="bulk_jenis"
                                    list="dlJenis" placeholder="Contoh: Servis berkala">
                            </div>
                            <button class="btn btn-sm btn-primary" id="bulk_apply">Terapkan ke semua</button>
                            <div class="ms-auto fw-semibold" id="jSummary"></div>
                        </div>
                        <div class="scroll-box">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>No. KK</th>
                                        <th>Section *</th>
                                        <th>Jenis *</th>
                                        <th>Biaya</th>
                                        <th>Keterangan</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="jEdit"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-info text-white" id="j_save" disabled><i
                            class="fa-solid fa-download me-1"></i> Simpan data terpilih</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== MODAL: DETAIL ===================== --}}
    <div class="modal fade" id="modalDetail" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="detailTitle">Detail</h5>
                        <div class="small text-muted" id="detailSub"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailBody"></div>
            </div>
        </div>
    </div>

    {{-- ===================== MODAL: IMPORT EXCEL ===================== --}}
    <div class="modal fade" id="modalImportExcel" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <form action="{{ route('HR.maintenance.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Import dari Excel</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <ul class="small ps-3">
                            <li>Kolom: Tanggal, Jenis Maintenance, Section, Biaya, Keterangan, No. KK.</li>
                            <li>Tanggal dan Jenis Maintenance wajib diisi. Section yang mengandung kata "kendaraan" masuk ke
                                Kendaraan, selain itu ke Kantor.</li>
                        </ul>
                        <div class="mb-3"><a href="{{ route('HR.maintenance.template_excel') }}"
                                class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-download me-1"></i> Unduh
                                template</a></div>
                        <input class="form-control" type="file" name="file_excel" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <div class="modal-footer bg-light"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Unggah data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===================== MODAL: PANDUAN ===================== --}}
    <div class="modal fade" id="modalPanduan" tabindex="-1" aria-labelledby="panduanLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header modal-header-custom border-0">
                    <h5 class="modal-title" id="panduanLabel"><i class="fa-solid fa-book-open me-2"></i>Panduan
                        Maintenance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <ul class="nav nav-tabs nav-tabs-custom mb-4">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#guide-content-1"
                                type="button">
                                <i class="fa-solid fa-list-ol"></i>Cara Penggunaan (Step by Step)
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#guide-content-2"
                                type="button">
                                <i class="fa-solid fa-circle-info"></i>Arti Istilah, Contoh & Bantuan
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        {{-- ================= TAB 1 ================= --}}
                        <div class="tab-pane fade show active" id="guide-content-1">

                            <div class="guide-section">
                                <h5>1. Untuk apa halaman ini?</h5>
                                <p>Halaman ini mencatat biaya maintenance <strong>kendaraan</strong> dan
                                    <strong>kantor</strong>, lalu membandingkannya per bulan, per quarter, dan per tahun
                                    agar kenaikan biaya mudah terlihat.</p>
                                <div class="guide-note">Cara tercepat: klik <strong>Ambil dari jurnal</strong>, blok
                                    beberapa baris jurnal, pilih Section dan Jenis, lalu klik <strong>Simpan data
                                        terpilih</strong>.</div>
                            </div>

                            <div class="guide-section">
                                <h5>2. Menambah Data Secara Manual</h5>
                                <ol>
                                    <li>Klik tombol <strong>Tambah manual</strong> di kanan atas.</li>
                                    <li>Isi <strong>Tanggal</strong>. Kolom <strong>Bulan</strong> terisi otomatis.</li>
                                    <li>Pilih <strong>Section</strong>: Maintenance Kendaraan atau Maintenance Kantor.</li>
                                    <li>Isi <strong>Jenis maintenance</strong> (contoh: Servis berkala, Service AC). Jenis
                                        yang pernah dipakai akan muncul sebagai saran.</li>
                                    <li>Isi <strong>Harga / biaya</strong>, lalu <strong>No. KK</strong> dan
                                        <strong>Keterangan</strong> bila ada.</li>
                                    <li>Klik <strong>Simpan data</strong>.</li>
                                </ol>
                                <p class="mb-0">Untuk mengubah atau menghapus, gunakan ikon pensil dan tempat sampah pada
                                    baris di tabel.</p>
                            </div>

                            <div class="guide-section">
                                <h5>3. Mengambil Data dari Jurnal Akuntansi</h5>
                                <ol>
                                    <li>Klik <strong>Ambil dari jurnal</strong>. Atur rentang tanggal, atau ketik kata kunci
                                        / No. KK pada kolom pencarian.</li>
                                    <li><strong>Pilih banyak baris sekaligus:</strong> tekan tombol mouse pada satu baris,
                                        <strong>tahan, lalu geser</strong> ke baris lain. Semua baris yang dilewati ikut
                                        terpilih.</li>
                                    <li>Gunakan <strong>Shift + klik</strong> untuk memilih rentang dari klik terakhir
                                        sampai baris yang diklik. Klik satu baris untuk memilih atau membatalkannya.</li>
                                    <li>Tombol <strong>Pilih semua yang tampil</strong> dan <strong>Kosongkan
                                            pilihan</strong> ada di atas tabel.</li>
                                    <li>Setelah ada yang terpilih, tabel <strong>Lengkapi data</strong> muncul di bawah.
                                        Tanggal, No. KK, biaya, dan keterangan sudah terisi dari jurnal, dan masih bisa
                                        diedit.</li>
                                    <li>Isi <strong>Section</strong> dan <strong>Jenis</strong>. Agar cepat, pilih satu
                                        nilai di bagian atas lalu klik <strong>Terapkan ke semua</strong>.</li>
                                    <li>Kolom yang bergaris merah belum lengkap. Setelah semua terisi, klik <strong>Simpan
                                            data terpilih</strong>.</li>
                                </ol>
                                <div class="guide-note">Baris berlabel <strong>sudah diimpor</strong> (No. KK dan tahun
                                    sama) tidak bisa dipilih lagi supaya tidak ganda. Data hanya disalin, jadi perubahan di
                                    jurnal tidak mengubah data maintenance.</div>
                            </div>

                            <div class="guide-section">
                                <h5>4. Mengatur Filter</h5>
                                <ol>
                                    <li>Pilih <strong>Tahun</strong> dan, bila perlu, <strong>Section</strong>.</li>
                                    <li>Pilih <strong>Jenis maintenance</strong> dan <strong>Bulan</strong> bila ingin
                                        mempersempit tabel data.</li>
                                    <li>Klik <strong>Terapkan</strong>. Untuk kembali ke awal, klik tombol
                                        <strong>Reset</strong> (ikon panah melingkar).</li>
                                </ol>
                                <div class="guide-note"><strong>Tahun</strong> dan <strong>Section</strong> memengaruhi
                                    tabel, card, dan semua grafik. <strong>Jenis</strong> dan <strong>Bulan</strong> hanya
                                    memengaruhi tabel data.</div>
                            </div>

                            <div class="guide-section">
                                <h5>5. Membaca Card Ringkasan</h5>
                                <p>Setiap card bisa diklik dan membuka jendela detail dengan tampilan yang berbeda:</p>
                                <ul>
                                    <li><strong>Total biaya:</strong> akumulasi bulanan, kontribusi tiap bulan, dan
                                        perubahan dari bulan sebelumnya.</li>
                                    <li><strong>Jumlah transaksi:</strong> sebaran transaksi per jenis dan bulan tersibuk.
                                    </li>
                                    <li><strong>Rata-rata per transaksi:</strong> median, nilai terkecil dan terbesar,
                                        simpangan baku, dan distribusi nilai.</li>
                                    <li><strong>Transaksi terbesar:</strong> 10 transaksi dengan biaya tertinggi.</li>
                                    <li><strong>Kendaraan / Kantor:</strong> komposisi jenis dan sebaran bulanan untuk
                                        section tersebut.</li>
                                    <li><strong>Vs tahun lalu:</strong> pertumbuhan tahunan dan perubahan bulan ke bulan.
                                    </li>
                                </ul>
                            </div>

                            <div class="guide-section">
                                <h5>6. Memakai Tab Comparison Biaya</h5>
                                <ol>
                                    <li>Buka tab <strong>Comparison biaya</strong>.</li>
                                    <li><strong>Per bulan:</strong> grafik batang tahun ini dibanding tahun lalu, dengan
                                        tabel selisih. Klik batang atau baris untuk melihat semua transaksi bulan itu.</li>
                                    <li><strong>Per quarter:</strong> empat card Q1 sampai Q4 dengan perbandingan ke quarter
                                        yang sama tahun lalu. Klik card untuk rincian.</li>
                                    <li><strong>Per tahun:</strong> total tiap tahun, dipisah Kendaraan dan Kantor. Klik
                                        batang atau baris untuk rincian per quarter dan per jenis.</li>
                                </ol>
                            </div>

                            <div class="guide-section">
                                <h5>7. Memakai Tab Tren Biaya</h5>
                                <ol>
                                    <li>Buka tab <strong>Tren biaya</strong>. Ada empat grafik: tren bulanan, komposisi
                                        section, biaya per jenis, dan akumulasi tahun berjalan.</li>
                                    <li>Klik titik, batang, atau potongan grafik untuk membuka detailnya, atau pakai tombol
                                        <strong>Detail</strong> di pojok kartu.</li>
                                </ol>
                            </div>

                            <div class="guide-section">
                                <h5>8. Mengunduh Laporan dan Import Excel</h5>
                                <ol>
                                    <li>Atur filter, lalu klik <strong>Terapkan</strong>.</li>
                                    <li>Buka menu <strong>Export / Import</strong>, lalu pilih <strong>Export PDF</strong>
                                        atau <strong>Export Excel</strong>. Isi laporan sama dengan filter yang sedang
                                        aktif.</li>
                                    <li>Untuk memasukkan banyak data dari file, pilih <strong>Import dari Excel</strong>,
                                        unduh template, isi, lalu unggah.</li>
                                </ol>
                            </div>
                        </div>

                        {{-- ================= TAB 2 ================= --}}
                        <div class="tab-pane fade" id="guide-content-2">

                            <div class="guide-section">
                                <h5>A. Data ini dari mana?</h5>
                                <p>Data berasal dari yang Anda input manual, hasil import Excel, atau salinan dari Jurnal
                                    Akuntansi. Fitur ini berdiri sendiri dan tidak terhubung ke tabel lain.</p>
                                <ul>
                                    <li>Mengubah atau menghapus jurnal akuntansi <strong>tidak</strong> memengaruhi data
                                        maintenance yang sudah disalin.</li>
                                    <li>Jika angka tidak sesuai, periksa data di tabel lalu ubah dengan ikon pensil.</li>
                                </ul>
                            </div>

                            <div class="guide-section">
                                <h5>B. Arti Istilah</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle" style="font-size:.85rem">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:170px">Istilah</th>
                                                <th>Artinya</th>
                                                <th style="width:240px">Contoh</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><span class="badge bg-primary">Section</span></td>
                                                <td>Kelompok maintenance: Kendaraan atau Kantor.</td>
                                                <td>Maintenance Kendaraan</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-secondary">Jenis maintenance</span></td>
                                                <td>Jenis pekerjaan atau barang yang dirawat.</td>
                                                <td>Servis berkala, Service AC</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-info text-dark">No. KK</span></td>
                                                <td>Nomor kas keluar pada jurnal. Dipakai untuk mencegah data ganda saat
                                                    ambil dari jurnal.</td>
                                                <td>KK-0123</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-dark">Bulan</span></td>
                                                <td>Terisi otomatis dari tanggal, tidak perlu diisi.</td>
                                                <td>Tanggal 12-07-2026 = Juli</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-success">Quarter</span></td>
                                                <td>Periode 3 bulan: Q1 Jan-Mar, Q2 Apr-Jun, Q3 Jul-Sep, Q4 Okt-Des.</td>
                                                <td>Q3 = Juli sampai September</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-warning text-dark">YoY / MoM</span></td>
                                                <td>YoY = dibanding tahun lalu. MoM = dibanding bulan sebelumnya.</td>
                                                <td>+12% YoY</td>
                                            </tr>
                                            <tr>
                                                <td>Warna persentase</td>
                                                <td><span class="up fw-semibold">Merah</span> berarti biaya naik, <span
                                                        class="down fw-semibold">hijau</span> berarti biaya turun.
                                                    <em>Baru</em> berarti periode pembanding bernilai nol.</td>
                                                <td>-8% (hijau) = lebih hemat</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="guide-note">Quarter dihitung dari tanggal transaksi, bukan dari kolom bulan.
                                </div>
                            </div>

                            <div class="guide-section">
                                <h5>C. Contoh Penggunaan</h5>
                                <div class="guide-example">
                                    <p class="mb-2"><strong>Kasus:</strong> Admin ingin tahu apakah biaya maintenance
                                        kendaraan pada Quarter 3 lebih tinggi dari tahun lalu, dan memasukkan data servis
                                        Juli yang sudah ada di jurnal.</p>
                                    <ol class="mb-3">
                                        <li>Klik <strong>Ambil dari jurnal</strong>, atur tanggal 01-07 sampai 31-07, lalu
                                            ketik <strong>servis</strong> di kolom pencarian.</li>
                                        <li>Tahan mouse pada baris pertama lalu geser sampai baris terakhir untuk memilih
                                            semuanya.</li>
                                        <li>Pilih Section <strong>Maintenance Kendaraan</strong>, ketik Jenis <strong>Servis
                                                berkala</strong>, klik <strong>Terapkan ke semua</strong>, lalu
                                            <strong>Simpan data terpilih</strong>.</li>
                                        <li>Pilih Section <strong>Maintenance Kendaraan</strong> pada filter, klik
                                            <strong>Terapkan</strong>, lalu buka tab <strong>Comparison biaya</strong> dan
                                            pilih <strong>Per quarter</strong>.</li>
                                        <li>Lihat card <strong>Q3</strong>. Klik card itu untuk melihat setiap transaksi dan
                                            perbandingannya dengan Q3 tahun lalu.</li>
                                    </ol>
                                    <p class="mb-2"><strong>Contoh isi detail Q3:</strong></p>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered bg-white mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Total Q3</th>
                                                    <th>Q3 tahun lalu</th>
                                                    <th>Perubahan</th>
                                                    <th>Kendaraan / Kantor</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Rp 7.130.000</td>
                                                    <td>Rp 5.400.000</td>
                                                    <td><span class="up fw-semibold">+32%</span></td>
                                                    <td>Rp 6.980.000 / Rp 150.000</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="guide-section">
                                <h5>D. Pertanyaan yang Sering Muncul</h5>
                                <div class="guide-qa">
                                    <strong>Mengapa baris jurnal tidak bisa saya pilih?</strong>
                                    <span>Baris itu sudah pernah diimpor (No. KK dan tahun sama). Anda tetap bisa mengubah
                                        datanya langsung di tabel maintenance.</span>
                                </div>
                                <div class="guide-qa">
                                    <strong>Mengapa kolom "Vs tahun lalu" bertanda "-"?</strong>
                                    <span>Belum ada data pada tahun sebelumnya, sehingga persentase tidak bisa
                                        dihitung.</span>
                                </div>
                                <div class="guide-qa">
                                    <strong>Mengapa card dan grafik tidak berubah saat saya memilih Bulan?</strong>
                                    <span>Filter Bulan dan Jenis hanya berlaku untuk tabel data. Card dan grafik mengikuti
                                        Tahun dan Section, karena dipakai untuk membandingkan antar periode.</span>
                                </div>
                                <div class="guide-qa">
                                    <strong>Bagaimana cara mengembalikan filter ke awal?</strong>
                                    <span>Klik tombol Reset (ikon panah melingkar) di sebelah tombol Terapkan.</span>
                                </div>
                            </div>

                            <div class="guide-section">
                                <h5>E. Permintaan Perubahan atau Laporan Error</h5>
                                <div class="guide-itsm">
                                    <p class="mb-2"><strong>Jika ada permintaan perubahan fitur atau tampilan, atau Anda
                                            menemukan error atau data yang tidak sesuai, silakan hubungi Tim ITSM.</strong>
                                    </p>
                                    <ol class="mb-3">
                                        <li>Klik tombol <strong>Hubungi Tim ITSM</strong> di bawah.</li>
                                        <li>Pilih jenis permintaan (laporan error, fitur baru, perbaikan, atau masalah
                                            data).</li>
                                        <li>Tuliskan apa yang terjadi, di bagian mana, filter apa yang dipakai, dan hasil
                                            yang Anda harapkan.</li>
                                        <li>Lampirkan tangkapan layar bila ada, lalu kirim.</li>
                                    </ol>
                                    @if (\Illuminate\Support\Facades\Route::has('tickets.index'))
                                        <a href="{{ route('tickets.index') }}" class="btn btn-danger btn-sm"
                                            target="_blank"><i class="fa-solid fa-ticket me-1"></i>Hubungi Tim ITSM</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm"
                        data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(function() {
                /* Dropdown Export/Import (tanpa Popper) */
                const expBtn = document.getElementById('expToggle'),
                    expMenu = document.getElementById('expMenu');
                const expClose = () => {
                    expMenu.classList.remove('show');
                    expBtn.setAttribute('aria-expanded', 'false');
                };
                expBtn.addEventListener('click', e => {
                    e.stopPropagation();
                    expBtn.setAttribute('aria-expanded', expMenu.classList.toggle('show'));
                });
                expMenu.addEventListener('click', expClose);
                document.addEventListener('click', expClose);
                document.addEventListener('keydown', e => {
                    if (e.key === 'Escape') expClose();
                });

                const R = {
                    analytics: "{{ route('HR.maintenance.analytics') }}",
                    detail: "{{ route('HR.maintenance.detail') }}",
                    jurnal: "{{ route('HR.maintenance.jurnal') }}",
                    importJurnal: "{{ route('HR.maintenance.importJurnal') }}",
                    store: "{{ route('HR.maintenance.store') }}",
                    update: "{{ route('HR.maintenance.update', ':id') }}"
                };
                const CSRF = "{{ csrf_token() }}";
                const HDR = {
                    'Accept': 'application/json'
                };
                const SEC_K = 'Maintenance Kendaraan',
                    SEC_O = 'Maintenance Kantor';
                const PAL = ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#a855f7', '#64748b', '#ec4899'];
                const MONTHS = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September',
                    'Oktober', 'November', 'Desember'
                ];

                const rp = n => 'Rp ' + Math.round(n || 0).toLocaleString('id-ID');
                const rpS = v => v >= 1e6 ? 'Rp ' + (v / 1e6).toFixed(1).replace('.0', '') + ' jt' : (v >= 1e3 ? 'Rp ' +
                    Math.round(v / 1e3) + ' rb' : 'Rp ' + Math.round(v));
                const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [c]));
                const pad = n => String(n).padStart(2, '0');
                const iso = d => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
                const pctTxt = (a, b) => {
                    if (!b) return a > 0 ? '<span class="text-muted">Baru</span>' : '-';
                    const d = Math.round((a - b) / b * 1000) / 10;
                    return `<span class="${d > 0 ? 'up' : (d < 0 ? 'down' : '')}">${d > 0 ? '+' : ''}${d}%</span>`;
                };
                const filters = () => ({
                    tahun: $('#filter_tahun').val(),
                    section: $('#filter_section').val()
                });

                /* ---------- Chart helper: render hanya bila container terlihat ---------- */
                const charts = {},
                    pending = {};

                function mount(id, opt) {
                    if (charts[id]) {
                        charts[id].destroy();
                        delete charts[id];
                    }
                    const el = document.getElementById(id);
                    if (!el) return;
                    if (el.offsetParent === null) {
                        pending[id] = opt;
                        return;
                    }
                    delete pending[id];
                    charts[id] = new ApexCharts(el, opt);
                    charts[id].render();
                }
                document.addEventListener('shown.bs.tab', () => Object.keys(pending).forEach(id => {
                    const el = document.getElementById(id);
                    if (el && el.offsetParent !== null) mount(id, pending[id]);
                }));

                const base = (type, extra = {}) => Object.assign({
                    chart: {
                        type,
                        height: 320,
                        toolbar: {
                            show: false
                        },
                        fontFamily: 'inherit'
                    },
                    colors: PAL,
                    dataLabels: {
                        enabled: false
                    },
                    legend: {
                        position: 'top'
                    },
                    grid: {
                        borderColor: '#e2e8f0'
                    }
                }, extra);
                const moneyTip = {
                    y: {
                        formatter: v => rp(v)
                    }
                };

                /* ---------- Detail modal ---------- */
                const detailModal = new bootstrap.Modal(document.getElementById('modalDetail'));
                $(document).on('click', '[data-detail]', function() {
                    openDetail(this.dataset.detail, this.dataset.key || '', this.dataset.label || 'Detail');
                });

                function openDetail(type, key, label) {
                    $('#detailTitle').text(label);
                    $('#detailSub').text('');
                    $('#detailBody').html(
                        '<div class="text-center py-5 text-muted"><i class="fa-solid fa-spinner fa-spin me-1"></i> Memuat detail...</div>'
                    );
                    detailModal.show();

                    $.ajax({
                        url: R.detail,
                        method: 'GET',
                        dataType: 'json',
                        headers: HDR,
                        data: { ...filters(), type, key }
                    })
                    .done(renderDetail)
                    .fail(function() {
                        $('#detailBody').html(
                            '<div class="alert alert-danger">Detail tidak dapat dimuat. Coba lagi.</div>');
                    });
                }
                function specToOptions(c) {
                    const money = c.money !== false;
                    const fmt = v => money ? rp(v) : Math.round(v).toLocaleString('id-ID');
                    if (c.type === 'donut') return base('donut', {
                        series: c.series,
                        labels: c.labels,
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            y: {
                                formatter: fmt
                            }
                        }
                    });
                    const o = base(c.type, {
                        series: c.series,
                        xaxis: {
                            categories: c.labels
                        },
                        stroke: {
                            curve: 'smooth',
                            width: c.type === 'bar' ? 0 : 3
                        },
                        plotOptions: {
                            bar: {
                                horizontal: !!c.horizontal,
                                borderRadius: 4,
                                columnWidth: '55%'
                            }
                        },
                        tooltip: {
                            y: {
                                formatter: fmt
                            }
                        }
                    });
                    o.chart.stacked = !!c.stacked;
                    if (c.horizontal) o.xaxis = {
                        categories: c.labels,
                        labels: {
                            formatter: v => money ? rpS(v) : Math.round(v)
                        }
                    };
                    else o.yaxis = {
                        labels: {
                            formatter: v => money ? rpS(v) : Math.round(v)
                        }
                    };
                    if (c.type === 'area') o.fill = {
                        type: 'gradient',
                        gradient: {
                            opacityFrom: .5,
                            opacityTo: .05
                        }
                    };
                    return o;
                }

                function renderDetail(d) {
                    $('#detailTitle').text(d.title);
                    $('#detailSub').text(d.subtitle);
                    let h = '<div class="row g-3 mb-3">';
                    d.stats.forEach(s => h +=
                        `<div class="col-6 col-lg"><div class="border rounded p-3 h-100"><div class="small text-muted">${esc(s.label)}</div><div class="fw-bold">${esc(s.value)}</div></div></div>`
                        );
                    h += '</div>';
                    if (d.chart) h += '<div id="detailChart" class="mb-3"></div>';
                    if (d.rows.length) {
                        h +=
                        '<input type="search" class="form-control form-control-sm mb-2" id="detailFilter" placeholder="Cari di tabel...">';
                        h +=
                        '<div class="table-responsive"><table class="table table-sm table-hover align-middle" id="detailTable"><thead class="table-light"><tr>';
                        d.columns.forEach((c, i) => h +=
                            `<th class="${i === d.columns.length - 1 && d.columns.length > 2 ? 'text-end' : ''}">${esc(c)}</th>`
                            );
                        h += '</tr></thead><tbody>';
                        d.rows.forEach(r => {
                            h += '<tr>';
                            r.forEach((v, i) => h +=
                                `<td class="${i === r.length - 1 && r.length > 2 ? 'text-end' : ''}">${esc(v)}</td>`
                                );
                            h += '</tr>';
                        });
                        h += '</tbody></table></div>';
                    } else h += '<div class="text-center text-muted py-4">Belum ada data pada periode ini.</div>';
                    $('#detailBody').html(h);
                    if (d.chart) mount('detailChart', specToOptions(d.chart));
                    $('#detailFilter').on('input', function() {
                        const q = this.value.toLowerCase();
                        $('#detailTable tbody tr').each(function() {
                            $(this).toggle(this.textContent.toLowerCase().includes(q));
                        });
                    });
                }

                /* ---------- Analitik: card & grafik ---------- */
                let A = null;

                function loadAnalytics() {
                    $.ajax({
                        url: R.analytics,
                        method: 'GET',
                        dataType: 'json',
                        headers: HDR,
                        data: filters()
                    })
                    .done(function(res) {
                        A = res;
                        renderCards();
                        renderCompare();
                        renderTrend();
                    })
                    .fail(function() {
                        $('[id^=c_]').text('-');
                    });
                }

                function renderCards() {
                    const t = A.total;
                    $('#c_total').text(rp(t));
                    $('#s_total').text('Tahun ' + A.tahun);
                    $('#c_count').text(A.count);
                    $('#s_count').text('transaksi');
                    $('#c_avg').text(rp(A.avg));
                    $('#s_avg').text('per transaksi');
                    $('#c_max').text(A.max ? rp(A.max.biaya) : '-');
                    $('#s_max').text(A.max ? A.max.jenis : 'Belum ada data');
                    $('#c_kend').text(rp(A.kendaraan));
                    $('#s_kend').text(t ? Math.round(A.kendaraan / t * 100) + '% dari total' : '-');
                    $('#c_kantor').text(rp(A.kantor));
                    $('#s_kantor').text(t ? Math.round(A.kantor / t * 100) + '% dari total' : '-');
                    if (A.growth === null) {
                        $('#c_growth').text('-');
                        $('#s_growth').text('Tahun lalu belum ada data');
                    } else {
                        $('#c_growth').html(
                            `<span class="${A.growth > 0 ? 'up' : 'down'}">${A.growth > 0 ? '+' : ''}${A.growth}%</span>`
                            );
                        $('#s_growth').text('dari ' + rp(A.prev_total));
                    }
                }

                function renderCompare() {
                    const m = A.months,
                        thn = A.tahun;
                    mount('chartBulan', base('bar', {
                        series: [{
                            name: 'Tahun ' + thn,
                            data: m.map(x => x.total)
                        }, {
                            name: 'Tahun ' + (thn - 1),
                            data: m.map(x => x.prev)
                        }],
                        xaxis: {
                            categories: m.map(x => x.label)
                        },
                        colors: ['#4f46e5', '#cbd5e1'],
                        plotOptions: {
                            bar: {
                                borderRadius: 4,
                                columnWidth: '60%'
                            }
                        },
                        yaxis: {
                            labels: {
                                formatter: rpS
                            }
                        },
                        tooltip: moneyTip,
                        chart: {
                            type: 'bar',
                            height: 320,
                            toolbar: {
                                show: false
                            },
                            fontFamily: 'inherit',
                            events: {
                                dataPointSelection: (e, c, cfg) => openDetail('month', cfg.dataPointIndex +
                                    1, MONTHS[cfg.dataPointIndex] + ' ' + thn)
                            }
                        }
                    }));
                    $('#tblBulan').html(m.map((x, i) =>
                        `<tr class="clickable" data-detail="month" data-key="${i + 1}" data-label="${MONTHS[i]} ${thn}">
                <td>${MONTHS[i]}</td><td class="text-end">${rp(x.kendaraan)}</td><td class="text-end">${rp(x.kantor)}</td>
                <td class="text-end fw-bold">${rp(x.total)}</td><td class="text-end">${rp(x.prev)}</td><td class="text-end">${pctTxt(x.total, x.prev)}</td></tr>`
                        ).join(''));

                    $('#quarterCards').html(A.quarters.map((q, i) => `<div class="col-6 col-lg-3"><div class="card stat-card" data-detail="quarter" data-key="${i + 1}" data-label="Quarter ${i + 1} ${thn}">
                <div class="card-body py-3"><div class="lbl">${q.label} ${['Jan-Mar', 'Apr-Jun', 'Jul-Sep', 'Okt-Des'][i]}</div>
                <div class="val">${rp(q.total)}</div>
                <div class="sub">${pctTxt(q.total, q.prev)} dari ${rp(q.prev)} tahun lalu</div>
                <div class="sub">Kendaraan ${rpS(q.kendaraan)} | Kantor ${rpS(q.kantor)}</div>
                <div class="hint mt-1">Lihat detail</div></div></div></div>`).join(''));
                    mount('chartQuarter', base('bar', {
                        series: [{
                            name: 'Tahun ' + thn,
                            data: A.quarters.map(x => x.total)
                        }, {
                            name: 'Tahun ' + (thn - 1),
                            data: A.quarters.map(x => x.prev)
                        }],
                        xaxis: {
                            categories: A.quarters.map(x => x.label)
                        },
                        colors: ['#10b981', '#cbd5e1'],
                        plotOptions: {
                            bar: {
                                borderRadius: 4,
                                columnWidth: '45%'
                            }
                        },
                        yaxis: {
                            labels: {
                                formatter: rpS
                            }
                        },
                        tooltip: moneyTip,
                        chart: {
                            type: 'bar',
                            height: 320,
                            toolbar: {
                                show: false
                            },
                            fontFamily: 'inherit',
                            events: {
                                dataPointSelection: (e, c, cfg) => openDetail('quarter', cfg
                                    .dataPointIndex + 1, 'Quarter ' + (cfg.dataPointIndex + 1) + ' ' +
                                    thn)
                            }
                        }
                    }));

                    const y = A.years;
                    mount('chartTahun', base('bar', {
                        series: [{
                            name: 'Kendaraan',
                            data: y.map(x => x.kendaraan)
                        }, {
                            name: 'Kantor',
                            data: y.map(x => x.kantor)
                        }],
                        xaxis: {
                            categories: y.map(x => String(x.year))
                        },
                        colors: ['#4f46e5', '#10b981'],
                        plotOptions: {
                            bar: {
                                borderRadius: 4,
                                columnWidth: '40%'
                            }
                        },
                        yaxis: {
                            labels: {
                                formatter: rpS
                            }
                        },
                        tooltip: moneyTip,
                        chart: {
                            type: 'bar',
                            stacked: true,
                            height: 320,
                            toolbar: {
                                show: false
                            },
                            fontFamily: 'inherit',
                            events: {
                                dataPointSelection: (e, c, cfg) => openDetail('year', y[cfg.dataPointIndex]
                                    .year, 'Tahun ' + y[cfg.dataPointIndex].year)
                            }
                        }
                    }));
                    $('#tblTahun').html(y.map((x, i) => `<tr class="clickable" data-detail="year" data-key="${x.year}" data-label="Tahun ${x.year}">
                <td class="fw-semibold">${x.year}</td><td class="text-end">${x.count}</td><td class="text-end">${rp(x.kendaraan)}</td>
                <td class="text-end">${rp(x.kantor)}</td><td class="text-end fw-bold">${rp(x.total)}</td>
                <td class="text-end">${i ? pctTxt(x.total, y[i - 1].total) : '-'}</td></tr>`).join('') ||
                        '<tr><td colspan="6" class="text-center text-muted py-3">Belum ada data.</td></tr>');
                }

                function renderTrend() {
                    const m = A.months,
                        thn = A.tahun,
                        lbl = m.map(x => x.label);
                    const click = (e, c, cfg) => openDetail('month', cfg.dataPointIndex + 1, MONTHS[cfg
                        .dataPointIndex] + ' ' + thn);

                    mount('chartTren', base('line', {
                        series: [{
                            name: 'Total',
                            data: m.map(x => x.total)
                        }, {
                            name: 'Kendaraan',
                            data: m.map(x => x.kendaraan)
                        }, {
                            name: 'Kantor',
                            data: m.map(x => x.kantor)
                        }],
                        xaxis: {
                            categories: lbl
                        },
                        stroke: {
                            curve: 'smooth',
                            width: [4, 2, 2]
                        },
                        markers: {
                            size: 4
                        },
                        colors: ['#1e293b', '#4f46e5', '#10b981'],
                        yaxis: {
                            labels: {
                                formatter: rpS
                            }
                        },
                        tooltip: moneyTip,
                        chart: {
                            type: 'line',
                            height: 320,
                            toolbar: {
                                show: false
                            },
                            fontFamily: 'inherit',
                            events: {
                                markerClick: (e, c, o) => openDetail('month', o.dataPointIndex + 1, MONTHS[o
                                    .dataPointIndex] + ' ' + thn)
                            }
                        }
                    }));

                    mount('chartSection', base('donut', {
                        series: [A.kendaraan, A.kantor],
                        labels: ['Kendaraan', 'Kantor'],
                        colors: ['#4f46e5', '#10b981'],
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: moneyTip,
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '62%'
                                }
                            }
                        },
                        chart: {
                            type: 'donut',
                            height: 320,
                            fontFamily: 'inherit',
                            events: {
                                dataPointSelection: (e, c, cfg) => openDetail('section', cfg
                                    .dataPointIndex === 0 ? SEC_K : SEC_O, cfg.dataPointIndex === 0 ?
                                    SEC_K : SEC_O)
                            }
                        }
                    }));

                    const j = A.jenis.slice(0, 10);
                    mount('chartJenis', base('bar', {
                        series: [{
                            name: 'Biaya',
                            data: j.map(x => x.total)
                        }],
                        xaxis: {
                            categories: j.map(x => x.jenis),
                            labels: {
                                formatter: rpS
                            }
                        },
                        plotOptions: {
                            bar: {
                                horizontal: true,
                                borderRadius: 4,
                                distributed: true
                            }
                        },
                        legend: {
                            show: false
                        },
                        tooltip: moneyTip,
                        chart: {
                            type: 'bar',
                            height: 320,
                            toolbar: {
                                show: false
                            },
                            fontFamily: 'inherit',
                            events: {
                                dataPointSelection: (e, c, cfg) => openDetail('jenis', j[cfg.dataPointIndex]
                                    .jenis, j[cfg.dataPointIndex].jenis)
                            }
                        }
                    }));

                    let s = 0,
                        p = 0;
                    const cum = m.map(x => s += x.total),
                        cumP = m.map(x => p += x.prev);
                    mount('chartYTD', base('area', {
                        series: [{
                            name: 'Tahun ' + thn,
                            data: cum
                        }, {
                            name: 'Tahun ' + (thn - 1),
                            data: cumP
                        }],
                        xaxis: {
                            categories: lbl
                        },
                        colors: ['#4f46e5', '#94a3b8'],
                        stroke: {
                            curve: 'smooth',
                            width: 2
                        },
                        fill: {
                            type: 'gradient',
                            gradient: {
                                opacityFrom: .35,
                                opacityTo: .02
                            }
                        },
                        yaxis: {
                            labels: {
                                formatter: rpS
                            }
                        },
                        tooltip: moneyTip,
                        chart: {
                            type: 'area',
                            height: 320,
                            toolbar: {
                                show: false
                            },
                            fontFamily: 'inherit',
                            events: {
                                dataPointSelection: click
                            }
                        }
                    }));
                }

                loadAnalytics();

                /* ---------- Form tambah / edit ---------- */
                const formModal = new bootstrap.Modal(document.getElementById('modalForm'));
                const updBulan = () => {
                    const v = $('#f_tanggal').val();
                    $('#f_bulan').val(v ? MONTHS[parseInt(v.substr(5, 2), 10) - 1] : '');
                };
                $('#f_tanggal').on('change', updBulan);

                $('#btnAdd').on('click', () => {
                    $('#formMaint')[0].reset();
                    $('#formMaint').attr('action', R.store);
                    $('#methodField').prop('disabled', true);
                    $('#formTitle').text('Tambah data maintenance');
                    $('#f_tanggal').val(iso(new Date()));
                    updBulan();
                    formModal.show();
                });
                $(document).on('click', '.btn-edit', function() {
                    const d = JSON.parse(this.dataset.item);
                    $('#formMaint').attr('action', R.update.replace(':id', d.id));
                    $('#methodField').prop('disabled', false);
                    $('#formTitle').text('Ubah data maintenance');
                    $('#f_tanggal').val(d.tanggal);
                    $('#f_section').val(d.section);
                    $('#f_jenis').val(d.jenis_maintenance);
                    $('#f_biaya').val(d.biaya);
                    $('#f_nokk').val(d.no_kk);
                    $('#f_ket').val(d.keterangan);
                    updBulan();
                    formModal.show();
                });

                /* ---------- Ambil dari jurnal: pilih banyak baris dengan klik / blok ---------- */
                let jRows = [],
                    jMap = {},
                    jSel = new Set(),
                    jEdit = {},
                    jBase = new Set();
                let jDrag = false,
                    jAnchor = 0,
                    jMode = true,
                    jLast = null,
                    jLoaded = false;

                const d0 = new Date();
                d0.setMonth(d0.getMonth() - 2);
                d0.setDate(1);
                $('#j_start').val(iso(d0));
                $('#j_end').val(iso(new Date()));

                document.getElementById('modalJurnal').addEventListener('shown.bs.modal', () => {
                    if (!jLoaded) loadJurnal();
                });
                let jTimer;
                $('#j_q').on('input', () => {
                    clearTimeout(jTimer);
                    jTimer = setTimeout(loadJurnal, 400);
                });
                $('#j_start, #j_end').on('change', loadJurnal);

                let jXhr = null;

                function loadJurnal() {
                    $('#jBody').html('<tr><td colspan="5" class="text-center py-4 text-muted">Memuat...</td></tr>');

                    if (jXhr) jXhr.abort();

                    jXhr = $.ajax({
                        url: R.jurnal,
                        method: 'GET',
                        dataType: 'json',
                        headers: HDR,
                        data: {
                            start_date: $('#j_start').val(),
                            end_date: $('#j_end').val(),
                            q: $('#j_q').val()
                        }
                    })
                    .done(function(json) {
                        jRows = json.data;
                        jLoaded = true;
                        jRows.forEach(r => jMap[r.id] = r);
                        $('#jInfo').text(jRows.length + ' baris jurnal' + (json.limited ?
                            ' (dibatasi 500 baris terbaru, persempit rentang tanggal)' : ''));
                        $('#jBody').html(jRows.map((r, i) => `<tr data-i="${i}" data-id="${r.id}" class="${r.sudah ? 'done' : ''} ${jSel.has(r.id) ? 'sel' : ''}">
                            <td class="text-center"><i class="fa-solid ${r.sudah ? 'fa-lock text-muted' : 'fa-check text-primary'}"></i></td>
                            <td>${r.tanggal}</td><td class="fw-semibold">${esc(r.no_kk)}</td>
                            <td>${esc(r.keterangan)}${r.sudah ? ' <span class="badge bg-secondary">sudah diimpor</span>' : ''}</td>
                            <td class="text-end">${rp(r.biaya)}</td></tr>`).join('') ||
                            '<tr><td colspan="5" class="text-center py-4 text-muted">Tidak ada jurnal pada rentang ini.</td></tr>'
                        );
                    })
                    .fail(function(xhr, status) {
                        if (status === 'abort') return; // dibatalkan karena ada request baru
                        $('#jBody').html(
                            '<tr><td colspan="5" class="text-center text-danger py-4">Gagal memuat jurnal.</td></tr>'
                        );
                    });
                }

                function paintJ() {
                    $('#jBody tr[data-id]').each(function() {
                        $(this).toggleClass('sel', jSel.has(+this.dataset.id));
                    });
                }

                function rangeSel(a, b) {
                    const lo = Math.min(a, b),
                        hi = Math.max(a, b);
                    jSel = new Set(jBase);
                    for (let i = lo; i <= hi; i++) {
                        const r = jRows[i];
                        if (!r || r.sudah) continue;
                        jMode ? jSel.add(r.id) : jSel.delete(r.id);
                    }
                    paintJ();
                }
                $('#jBody').on('mousedown', 'tr[data-i]', function(e) {
                    if (e.button !== 0) return;
                    e.preventDefault();
                    const i = +this.dataset.i,
                        r = jRows[i];
                    jBase = new Set(jSel);
                    if (e.shiftKey && jLast !== null) {
                        jMode = true;
                        rangeSel(jLast, i);
                        afterSelect();
                        return;
                    }
                    if (r.sudah) return;
                    jDrag = true;
                    jAnchor = i;
                    jMode = !jSel.has(r.id);
                    jLast = i;
                    rangeSel(i, i);
                });
                $('#jBody').on('mouseover', 'tr[data-i]', function() {
                    if (jDrag) rangeSel(jAnchor, +this.dataset.i);
                });
                $(document).on('mouseup', () => {
                    if (jDrag) {
                        jDrag = false;
                        afterSelect();
                    }
                });

                $('#j_all').on('click', () => {
                    jRows.forEach(r => {
                        if (!r.sudah) jSel.add(r.id);
                    });
                    paintJ();
                    afterSelect();
                });
                $('#j_none').on('click', () => {
                    jSel.clear();
                    paintJ();
                    afterSelect();
                });

                function afterSelect() {
                    jSel.forEach(id => {
                        if (!jEdit[id]) {
                            const r = jMap[id];
                            jEdit[id] = {
                                tanggal: r.tanggal,
                                no_kk: r.no_kk || '',
                                keterangan: r.keterangan || '',
                                biaya: r.biaya,
                                jenis_maintenance: '',
                                section: ''
                            };
                        }
                    });
                    renderEdit();
                }

                function renderEdit() {
                    const ids = [...jSel].sort((a, b) => jMap[a].tanggal.localeCompare(jMap[b].tanggal));
                    $('#jStep2').toggleClass('d-none', !ids.length);
                    $('#jEdit').html(ids.map(id => {
                        const e = jEdit[id];
                        return `<tr data-id="${id}">
                  <td><input type="date" class="form-control form-control-sm" data-f="tanggal" value="${esc(e.tanggal)}"></td>
                  <td><input type="text" class="form-control form-control-sm" data-f="no_kk" value="${esc(e.no_kk)}"></td>
                  <td><select class="form-select form-select-sm ${e.section ? '' : 'bad'}" data-f="section"><option value="">-- pilih --</option>
                      <option ${e.section === SEC_K ? 'selected' : ''}>${SEC_K}</option><option ${e.section === SEC_O ? 'selected' : ''}>${SEC_O}</option></select></td>
                  <td><input type="text" list="dlJenis" class="form-control form-control-sm ${e.jenis_maintenance ? '' : 'bad'}" data-f="jenis_maintenance" value="${esc(e.jenis_maintenance)}"></td>
                  <td><input type="number" min="0" class="form-control form-control-sm" data-f="biaya" value="${esc(e.biaya)}"></td>
                  <td><input type="text" class="form-control form-control-sm" data-f="keterangan" value="${esc(e.keterangan)}"></td>
                  <td><button class="btn btn-sm btn-link text-danger j-remove" title="Batalkan pilihan"><i class="fa-solid fa-xmark"></i></button></td></tr>`;
                    }).join(''));
                    updSummary();
                }
                $('#jEdit').on('input change', '[data-f]', function() {
                    const id = +$(this).closest('tr').data('id');
                    jEdit[id][this.dataset.f] = this.value;
                    if (this.dataset.f === 'section' || this.dataset.f === 'jenis_maintenance') $(this)
                        .toggleClass('bad', !this.value);
                    updSummary();
                });
                $('#jEdit').on('click', '.j-remove', function() {
                    jSel.delete(+$(this).closest('tr').data('id'));
                    paintJ();
                    renderEdit();
                });
                $('#bulk_apply').on('click', () => {
                    const s = $('#bulk_section').val(),
                        j = $('#bulk_jenis').val().trim();
                    jSel.forEach(id => {
                        if (s) jEdit[id].section = s;
                        if (j) jEdit[id].jenis_maintenance = j;
                    });
                    renderEdit();
                });

                function updSummary() {
                    let sum = 0;
                    jSel.forEach(id => sum += parseFloat(jEdit[id]?.biaya) || 0);
                    $('#jSummary').text(jSel.size + ' dipilih | ' + rp(sum));
                    $('#j_save').prop('disabled', jSel.size === 0);
                }

                $('#j_save').on('click', function() {
                    const ids = [...jSel];
                    const bad = ids.filter(id => !jEdit[id].section || !jEdit[id].jenis_maintenance.trim());
                    if (bad.length) {
                        alert('Lengkapi Section dan Jenis maintenance pada ' + bad.length +
                            ' baris yang ditandai merah.');
                        return;
                    }

                    const btn = $(this).prop('disabled', true).html(
                        '<i class="fa-solid fa-spinner fa-spin me-1"></i> Menyimpan...');

                    $.ajax({
                        url: R.importJurnal,
                        method: 'POST',
                        dataType: 'json',
                        contentType: 'application/json; charset=UTF-8',
                        headers: { ...HDR, 'X-CSRF-TOKEN': CSRF },
                        data: JSON.stringify({
                            items: ids.map(id => ({
                                ...jEdit[id],
                                biaya: parseFloat(jEdit[id].biaya) || 0
                            }))
                        })
                    })
                    .done(function(json) {
                        alert(json.message);
                        location.reload();
                    })
                    .fail(function(xhr) {
                        const msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Data tidak valid.';
                        alert('Gagal menyimpan: ' + msg);
                        btn.prop('disabled', false).html(
                            '<i class="fa-solid fa-download me-1"></i> Simpan data terpilih');
                    });
                });
            });
        </script>
    @endpush
@endsection
