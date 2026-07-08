@extends('layout_HR.app')
@section('content_HR')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    
    <style>
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        .page-title { font-size: 1.5rem; font-weight: 700; color: var(--dark); margin-bottom: 0.25rem; }
        .page-subtitle { font-size: 0.9rem; color: var(--secondary); }
        .nav-tabs .nav-link { color: #64748b; font-weight: 500; border: none; border-bottom: 3px solid transparent; padding: 0.75rem 1.25rem; }
        .nav-tabs .nav-link.active { color: #0d6efd; background: transparent; border-bottom: 3px solid #0d6efd; }
        .nav-tabs .nav-link:hover:not(.active) { border-bottom: 3px solid #e2e8f0; }
        .table tfoot tr { background-color: #f8f9fa; font-weight: 700; color: #334155; }
        
        .filter-card {
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
        }

        .btn-danger, .text-danger, .bg-danger { background-color: #475569 !important; border-color: #475569 !important; color: #fff !important; }
        .btn-success { background-color: #10b981 !important; border-color: #10b981 !important; color: #fff !important; }
        .btn-primary { background-color: #4f46e5 !important; border-color: #4f46e5 !important; color: #fff !important; }

        /* Blob / Wave Background */
        .blob-bg {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 0; overflow: hidden; pointer-events: none;
        }
        .blob-1 {
            position: absolute; top: -50px; right: -50px; width: 450px; height: 450px;
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.15), rgba(99, 102, 241, 0.15));
            border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%;
            animation: blobAnim 12s ease-in-out infinite alternate;
        }
        .blob-2 {
            position: absolute; bottom: 10%; left: -100px; width: 350px; height: 350px;
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.12), rgba(79, 70, 229, 0.12));
            border-radius: 60% 40% 30% 70% / 50% 30% 70% 50%;
            animation: blobAnim2 15s ease-in-out infinite alternate;
        }
        @keyframes blobAnim {
            0% { transform: scale(1) translate(0, 0) rotate(0deg); border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%; }
            100% { transform: scale(1.1) translate(-30px, 30px) rotate(15deg); border-radius: 60% 40% 30% 70% / 50% 30% 70% 50%; }
        }
        @keyframes blobAnim2 {
            0% { transform: scale(1) translate(0, 0) rotate(0deg); border-radius: 60% 40% 30% 70% / 50% 30% 70% 50%; }
            100% { transform: scale(1.05) translate(40px, -20px) rotate(-15deg); border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%; }
        }
        
        .content-wrapper-relative {
            position: relative;
            z-index: 1;
        }
    </style>

    <div class="blob-bg">
        <div class="blob-1"></div>
        <div class="blob-2"></div>
    </div>

    <div class="container-fluid px-4 py-4 content-wrapper-relative">
        
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa-solid fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="page-title">Maintenance</h1>
                <p class="page-subtitle mb-0">
                    Kelola dan pantau seluruh data pemeliharaan aset dan sistem.
                    <span class="fw-semibold text-dark">{{ now()->translatedFormat('l, d F Y') }}</span>
                </p>
            </div>
            <div>
                <a href="{{ route('HR.maintenance.index') }}" class="btn btn-light border shadow-sm me-2" title="Reset/Refresh">
                    <i class="fa-solid fa-arrows-rotate"></i>
                </a>
                <a href="{{ route('HR.maintenance.export_pdf') }}" id="btn_export_pdf" class="btn btn-danger me-2" target="_blank">
                    <i class="fa-solid fa-file-pdf me-1"></i> Export PDF
                </a>
                <a href="{{ route('HR.maintenance.export_excel') }}" id="btn_export_excel" class="btn btn-success me-2" target="_blank">
                    <i class="fa-solid fa-file-excel me-1"></i> Export Excel
                </a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahJadwal">
                    <i class="fa-solid fa-plus me-1"></i> Tambah Jadwal
                </button>
            </div>
        </div>

        <div class="card filter-card mb-4">
            <div class="card-body p-4">
                <form id="form-filter-maintenance" class="row g-3" method="GET" action="{{ route('HR.maintenance.index') }}">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Jenis Aset/Sistem (Kategori)</label>
                        <select class="form-select" id="filter_jenis" name="jenis">
                            <option value="">-- Semua Jenis --</option>
                            <option value="Hardware" {{ request('jenis') == 'Hardware' ? 'selected' : '' }}>Hardware (Perangkat Keras)</option>
                            <option value="Software" {{ request('jenis') == 'Software' ? 'selected' : '' }}>Software (Sistem/Aplikasi)</option>
                            <option value="Jaringan" {{ request('jenis') == 'Jaringan' ? 'selected' : '' }}>Jaringan (Network)</option>
                            <option value="Fasilitas" {{ request('jenis') == 'Fasilitas' ? 'selected' : '' }}>Fasilitas Gedung</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Lokasi/Divisi</label>
                        <select class="form-select" id="filter_lokasi" name="lokasi">
                            <option value="">-- Semua Lokasi --</option>
                            <option value="IT" {{ request('lokasi') == 'IT' ? 'selected' : '' }}>IT & Network</option>
                            <option value="Finance" {{ request('lokasi') == 'Finance' ? 'selected' : '' }}>Finance</option>
                            <option value="Marketing" {{ request('lokasi') == 'Marketing' ? 'selected' : '' }}>Marketing</option>
                            <option value="Gedung Utama" {{ request('lokasi') == 'Gedung Utama' ? 'selected' : '' }}>Gedung Utama</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Teknisi/Penanggung Jawab</label>
                        <select class="form-select" id="filter_teknisi" name="teknisi">
                            <option value="">-- Semua Teknisi / Penanggung Jawab --</option>
                            @foreach($teknisis as $teknisi)
                                <option value="{{ $teknisi->nama_lengkap }}" {{ request('teknisi') == $teknisi->nama_lengkap ? 'selected' : '' }}>{{ $teknisi->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Tahun</label>
                        <select class="form-select" id="filter_tahun" name="tahun">
                            @for ($y = date('Y') + 1; $y >= 2023; $y--)
                                <option value="{{ $y }}" {{ request('tahun', date('Y')) == $y ? 'selected' : '' }}>
                                    {{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-9">
                        <label class="form-label fw-semibold d-block">Filter Periode</label>
                        <div class="d-flex align-items-center gap-4 mt-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode_periode" id="mode_semua"
                                    value="semua" {{ request('mode_periode', 'semua') == 'semua' ? 'checked' : '' }}>
                                <label class="form-check-label" for="mode_semua">Seluruh Tahun</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode_periode" id="mode_bulan"
                                    value="bulan" {{ request('mode_periode') == 'bulan' ? 'checked' : '' }}>
                                <label class="form-check-label" for="mode_bulan">Bulanan</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode_periode" id="mode_quartal"
                                    value="quartal" {{ request('mode_periode') == 'quartal' ? 'checked' : '' }}>
                                <label class="form-check-label" for="mode_quartal">Per 3 Bulan (Quartal)</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 {{ request('mode_periode') == 'bulan' ? '' : 'd-none' }}" id="wrapper_bulan">
                        <label class="form-label fw-semibold">Pilih Bulan</label>
                        <select class="form-select" id="filter_bulan" name="bulan">
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ request('bulan') == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-4 {{ request('mode_periode') == 'quartal' ? '' : 'd-none' }}" id="wrapper_quartal">
                        <label class="form-label fw-semibold">Pilih Quartal</label>
                        <select class="form-select" id="filter_quartal" name="quartal">
                            <option value="1" {{ request('quartal') == '1' ? 'selected' : '' }}>Quartal 1 (Jan - Mar)</option>
                            <option value="2" {{ request('quartal') == '2' ? 'selected' : '' }}>Quartal 2 (Apr - Jun)</option>
                            <option value="3" {{ request('quartal') == '3' ? 'selected' : '' }}>Quartal 3 (Jul - Sep)</option>
                            <option value="4" {{ request('quartal') == '4' ? 'selected' : '' }}>Quartal 4 (Okt - Des)</option>
                        </select>
                    </div>

                    <div class="col-12 text-end mt-3">
                        <button type="submit" class="btn btn-primary px-4" id="btn_terapkan_filter">
                            <i class="fa-solid fa-filter me-1"></i> Terapkan Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <ul class="nav nav-tabs px-4 pt-3" id="maintenanceTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-mendatang-btn" data-bs-toggle="tab"
                            data-bs-target="#tab-mendatang" type="button">
                            <i class="fa-regular fa-calendar-check me-1"></i> Jadwal Mendatang
                            <span class="badge bg-secondary ms-1">{{ count($mendatang) }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-dikerjakan-btn" data-bs-toggle="tab" data-bs-target="#tab-dikerjakan"
                            type="button">
                            <i class="fa-solid fa-spinner fa-spin me-1"></i> Sedang Dikerjakan
                            <span class="badge bg-warning text-dark ms-1">{{ count($sedangDikerjakan) }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-riwayat-btn" data-bs-toggle="tab" data-bs-target="#tab-riwayat" type="button">
                            <i class="fa-solid fa-clock-rotate-left me-1"></i> Riwayat Maintenance
                            <span class="badge bg-success ms-1">{{ count($riwayat) }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-statistik-btn" data-bs-toggle="tab"
                            data-bs-target="#tab-statistik" type="button">
                            <i class="fa-solid fa-chart-line me-1"></i> Statistik Biaya
                        </button>
                    </li>
                </ul>

                <div class="tab-content p-4" id="maintenanceTabContent">
                    
                    <!-- TAB JADWAL MENDATANG -->
                    <div class="tab-pane fade show active" id="tab-mendatang" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">NO</th>
                                        <th>NAMA BARANG</th>
                                        <th>KATEGORI</th>
                                        <th>TEKNISI</th>
                                        <th>TANGGAL MULAI</th>
                                        <th>KETERANGAN</th>
                                        <th class="text-end">BIAYA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($mendatang as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td class="fw-semibold">{{ $item->nama_barang }}</td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $item->kategori }}</span>
                                            </td>
                                            <td>{{ $item->teknisi ?? '-' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($item->tanggal_mulai)->translatedFormat('d F Y') }}</td>
                                            <td>{{ $item->keterangan }}</td>
                                            <td class="text-end fw-bold text-success">Rp {{ number_format($item->biaya, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">Belum ada jadwal mendatang.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="6" class="text-end">TOTAL BIAYA:</td>
                                        <td class="text-end fw-bold">Rp {{ number_format($mendatang->sum('biaya'), 0, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- TAB SEDANG DIKERJAKAN -->
                    <div class="tab-pane fade" id="tab-dikerjakan" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">NO</th>
                                        <th>NAMA BARANG</th>
                                        <th>KATEGORI</th>
                                        <th>TEKNISI</th>
                                        <th>TANGGAL MULAI</th>
                                        <th>KETERANGAN</th>
                                        <th class="text-end">BIAYA</th>
                                        <th class="text-center" width="15%">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($sedangDikerjakan as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td class="fw-semibold">{{ $item->nama_barang }}</td>
                                            <td>
                                                <span class="badge bg-warning text-dark">{{ $item->kategori }}</span>
                                            </td>
                                            <td>{{ $item->teknisi ?? '-' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($item->tanggal_mulai)->translatedFormat('d F Y') }}</td>
                                            <td>{{ $item->keterangan }}</td>
                                            <td class="text-end fw-bold text-success">Rp {{ number_format($item->biaya, 0, ',', '.') }}</td>
                                            <td class="text-center">
                                                <form action="{{ route('HR.maintenance.markAsDone', $item->id) }}" method="POST" onsubmit="return confirm('Apakah pekerjaan ini sudah selesai?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success text-white">
                                                        <i class="fa-solid fa-check me-1"></i> Tandai Selesai
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">Belum ada data maintenance yang sedang dikerjakan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="6" class="text-end">TOTAL BIAYA:</td>
                                        <td class="text-end fw-bold">Rp {{ number_format($sedangDikerjakan->sum('biaya'), 0, ',', '.') }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- TAB RIWAYAT MAINTENANCE -->
                    <div class="tab-pane fade" id="tab-riwayat" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">NO</th>
                                        <th>NAMA BARANG</th>
                                        <th>KATEGORI</th>
                                        <th>TEKNISI</th>
                                        <th>TANGGAL SELESAI</th>
                                        <th>KETERANGAN</th>
                                        <th class="text-end">BIAYA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($riwayat as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td class="fw-semibold">{{ $item->nama_barang }}</td>
                                            <td><span class="badge bg-success">{{ $item->kategori }}</span></td>
                                            <td>{{ $item->teknisi ?? '-' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($item->tanggal_selesai)->translatedFormat('d F Y') }}</td>
                                            <td>{{ $item->keterangan }}</td>
                                            <td class="text-end fw-bold text-success">Rp {{ number_format($item->biaya, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">Belum ada data riwayat maintenance.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="6" class="text-end">TOTAL BIAYA:</td>
                                        <td class="text-end fw-bold">Rp {{ number_format($riwayat->sum('biaya'), 0, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- TAB STATISTIK BIAYA -->
                    <div class="tab-pane fade" id="tab-statistik" role="tabpanel">
                        @if(count($statistik) > 0)
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body">
                                            <h6 class="fw-bold mb-3">Diagram Pengeluaran per Kategori</h6>
                                            <div id="chartBiaya"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped align-middle">
                                            <thead class="table-primary text-center">
                                                <tr>
                                                    <th width="5%">NO</th>
                                                    <th>KATEGORI MAINTENANCE</th>
                                                    <th>TOTAL TIKET</th>
                                                    <th class="text-end">TOTAL (Rp)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $grandTotal = 0; $grandTiket = 0; @endphp
                                                @foreach($statistik as $index => $stat)
                                                    @php 
                                                        $grandTotal += $stat->total_biaya; 
                                                        $grandTiket += $stat->total_tiket; 
                                                    @endphp
                                                    <tr>
                                                        <td class="text-center">{{ $loop->iteration }}</td>
                                                        <td class="fw-semibold">{{ $stat->kategori }}</td>
                                                        <td class="text-center"><span class="badge bg-info text-dark rounded-pill px-3">{{ $stat->total_tiket }}</span></td>
                                                        <td class="text-end fw-bold text-success">Rp {{ number_format($stat->total_biaya, 0, ',', '.') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr class="table-light">
                                                    <td colspan="2" class="text-end fw-bold">GRAND TOTAL:</td>
                                                    <td class="text-center fw-bold">{{ $grandTiket }}</td>
                                                    <td class="text-end fw-bold" style="color: var(--bs-indigo); font-size: 1.1rem;">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="py-5 text-center text-muted">
                                <i class="fa-solid fa-chart-pie fa-3x mb-3 text-secondary opacity-50"></i>
                                <p>Data statistik kosong untuk periode ini.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Jadwal -->
    <div class="modal fade" id="modalTambahJadwal" tabindex="-1" aria-labelledby="modalTambahJadwalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('HR.maintenance.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title text-white fw-bold" id="modalTambahJadwalLabel">
                            <i class="fa-solid fa-file-circle-plus me-1"></i> Tambah Jadwal / Data Maintenance
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Kategori Maintenance</label>
                                <select name="kategori" class="form-select" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <option value="Hardware">Hardware (Perangkat Keras)</option>
                                    <option value="Software">Software (Sistem/Aplikasi)</option>
                                    <option value="Jaringan">Jaringan (Network)</option>
                                    <option value="Fasilitas">Fasilitas Gedung</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Divisi / Lokasi</label>
                                <select name="divisi" class="form-select" required>
                                    <option value="">-- Pilih Divisi / Lokasi --</option>
                                    <option value="IT">IT & Network</option>
                                    <option value="Finance">Finance</option>
                                    <option value="Marketing">Marketing</option>
                                    <option value="Gedung Utama">Gedung Utama</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Teknisi / Penanggung Jawab</label>
                                <select name="teknisi" class="form-select">
                                    <option value="">-- Pilih Teknisi (Opsional) --</option>
                                    @foreach($teknisis as $teknisi)
                                        <option value="{{ $teknisi->nama_lengkap }}">{{ $teknisi->nama_lengkap }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Nama Barang / Aset</label>
                                <input type="text" name="nama_barang" list="asetList" class="form-control" placeholder="Pilih atau ketik nama barang..." required>
                                <datalist id="asetList">
                                    @foreach($inventaris as $aset)
                                        <option value="{{ $aset->name }}">({{ $aset->kodebarang }})</option>
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">No. KK / Voucher</label>
                                <input type="text" name="no_voucher" class="form-control" placeholder="Masukkan nomor referensi">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Harga / Biaya (Rp)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="biaya" class="form-control" placeholder="0" required min="0">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="3" placeholder="Tuliskan keterangan detail pemeliharaan..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fa-solid fa-xmark me-1"></i> Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i> Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            // Radio button mode periode
            $('input[name="mode_periode"]').on('change', function() {
                let mode = $(this).val();
                $('#wrapper_bulan, #wrapper_quartal').addClass('d-none');
                if (mode === 'bulan') $('#wrapper_bulan').removeClass('d-none');
                else if (mode === 'quartal') $('#wrapper_quartal').removeClass('d-none');
            });
            
            // Activate current tab based on hash (optional logic if needed)
            // if(window.location.hash) {
            //     let triggerEl = document.querySelector('button[data-bs-target="' + window.location.hash + '"]');
            //     if(triggerEl) {
            //         new bootstrap.Tab(triggerEl).show();
            //     }
            // }

            // Chart Render if stats exist
            @if(count($statistik) > 0)
                var options = {
                    series: [{
                        name: 'Total Biaya (Rp)',
                        data: [
                            @foreach($statistik as $stat)
                                {{ $stat->total_biaya }},
                            @endforeach
                        ]
                    }],
                    chart: {
                        type: 'bar',
                        height: 300,
                        toolbar: { show: false }
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            horizontal: false,
                            columnWidth: '50%'
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    xaxis: {
                        categories: [
                            @foreach($statistik as $stat)
                                '{{ $stat->kategori }}',
                            @endforeach
                        ],
                    },
                    yaxis: {
                        labels: {
                            formatter: function (value) {
                                return "Rp " + value.toLocaleString("id-ID");
                            }
                        }
                    },
                    colors: ['#4f46e5'],
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return "Rp " + val.toLocaleString("id-ID")
                            }
                        }
                    }
                };

                var chart = new ApexCharts(document.querySelector("#chartBiaya"), options);
                chart.render();
                
                // Re-render chart when tab is shown to fix visibility issues
                document.getElementById('tab-statistik-btn').addEventListener('shown.bs.tab', function () {
                    chart.windowResize();
                })
            @endif
        });
    </script>
    @endpush
@endsection
