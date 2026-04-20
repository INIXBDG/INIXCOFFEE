@extends('layouts_office.app')

@section('office_contents')
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h4 class="mb-0 fw-bold text-dark">Dashboard Office</h4>
            <small class="text-muted fw-medium">{{ now()->translatedFormat('l, d F Y') }}</small>
        </div>

        <!-- Total Karyawan Card -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden glass-force">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-xl bg-opacity-15 rounded-circle p-3">
                                    <i class="bx bx-user text-primary" style="font-size: 2.5rem;color:white;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-4">
                                <h6 class="text-muted mb-2 text-uppercase small tracking-wider">Total Karyawan Aktif</h6>
                                <h2 class="mb-0 text-primary fw-bold">{{ $total_karyawan }}</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Divisi Stats Cards -->
        <div class="row mb-5 g-4">
            @foreach ($divisiStats as $index => $divisi)
                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100 hover-card rounded-3 overflow-hidden glass-force" data-bs-toggle="modal"
                        data-bs-target="#modalDivisi{{ $index }}" role="button" tabindex="0">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <div class="avatar avatar-md bg-{{ $divisi['color'] }} bg-opacity-15 rounded-pill">
                                        <i class="{{ $divisi['icon'] }}" style="font-size: 1.5rem;color:white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted mb-1 small text-uppercase tracking-wider">{{ $divisi['nama'] }}
                                    </h6>
                                    <h3 class="mb-0 fw-bold text-dark">{{ $divisi['total'] }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if (Auth::user()->jabatan === 'Finance & Accounting')
         <!-- Modal Tambah -->
        <div class="modal fade" id="modalTambahTagihan" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Tambah Tagihan Perusahaan</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <form method="post" action="{{ route('storeTagihanPerusahaan') }}"
                            enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label col-form-label">Kegiatan <span class="text-danger">*</span></label>
                                <input type="text" name="kegiatan" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label col-form-label">Tipe <span class="text-danger">*</span></label>
                                <select name="tipe" id="tipe" class="form-select">
                                    <option value="tahunan">
                                        Tahunan
                                    </option>
                                    <option value="bulanan">
                                        Bulanan
                                    </option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label col-form-label">Perkiraan Tanggal <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-5">
                                        <label for="tanggal_perkiraan_mulai">Mulai</label>
                                        <input type="date" name="tanggal_perkiraan_mulai" class="form-control col-md-6 mb-2">
                                    </div>
                                    <div class="col-md-5">
                                        <label for="tanggal_perkiraan_selesai">Selesai</label>
                                        <input type="date" name="tanggal_perkiraan_selesai" class="form-control col-md-6 mb-2">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label col-form-label">Nominal <span class="text-danger">*</span></label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="text" name="nominal" id="nominal" class="form-control format-rupiah" autocomplete="off">
                                </div>
                            </div>

                            <div class="mb-3">  
                                <label for="keterangan" class="col-md-5 col-form-label">Keterangan (Optional)</label>  
                                <textarea class="form-control" name="keterangan"></textarea>  
                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        
        <!-- Modal Edit -->
        <div class="modal fade" id="modalEditTagihan" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Edit Tagihan Perusahaan</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <small class="text-muted">* Status selesai dan terlambat otomatis terupdate dari sistem</small>
                        <form method="post"
                            class="mt-5"
                            id="formEditTagihan"
                            enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3 row">
                                {{-- Status --}}
                                <div class="col-md-4">
                                    <label class="form-label text-muted small text-uppercase">
                                        Status
                                    </label>

                                    <select name="status" id="status" class="form-select">
                                        <option value="pending">
                                            Pending
                                        </option>
                                        <option value="proses">
                                            Proses
                                        </option>
                                        <option value="selesai" disabled hidden>
                                            Selesai
                                        </option>
                                        <option value="telat" disabled hidden>
                                            Terlambat
                                        </option>
                                    </select>
                                </div>

                                <!-- Tanggal Perkiraan -->
                                <div class="col-md-4">
                                    <label class="form-label text-muted small text-uppercase">
                                        Tracking
                                    </label>

                                    <select name="tracking" id="tracking" class="form-select">
                                        <option value="Sedang Dikonfirmasi oleh Bagian Finance kepada General Manager">Sedang Dikonfirmasi oleh Bagian Finance kepada General Manager</option>
                                        <option value="Sedang Dikonfirmasi oleh Bagian Finance kepada Direksi">Sedang Dikonfirmasi oleh Bagian Finance kepada Direksi</option>
                                        <option value="Finance Menunggu Approve Direksi">Finance Menunggu Approve Direksi</option>
                                        <option value="Diajukan dan Sedang Ditinjau oleh Finance">Diajukan dan Sedang Ditinjau oleh Finance</option>
                                        <option value="Membuat Permintaan Ke Direktur Utama">Membuat Permintaan Ke Direktur Utama</option>
                                        <option value="Pengajuan sedang dalam proses Pencairan">Pengajuan sedang dalam proses Pencairan</option>
                                        <option value="Pencairan Sudah Selesai">Pencairan Sudah Selesai</option>
                                        <option value="Selesai">Selesai</option>
                                    </select>
                                </div>

                                <!-- Tanggal Selesai -->
                                <div class="col-md-4">
                                    <label class="form-label text-muted small text-uppercase">
                                        Tanggal Realisasi
                                    </label>
                                    <div>
                                        <input type="date" name="tanggal_selesai" class="form-control col-md-6">
                                    </div>
                                </div>

                                {{-- Keterangan --}}
                                <div class="mb-3">  
                                <label for="keterangan" class="col-md-5 col-form-label">Keterangan (Optional)</label>  
                                    <textarea class="form-control" name="keterangan"></textarea>  
                                </div>
                                
                            </div>

                            <div class="modal-footer mt-4">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-4 mb-5">
            <div class="col-12">
                <div class="card border-0 shadow-lg h-100 rounded-4 overflow-hidden  glass-force">
                    <div class="card-header border-bottom-0 pb-0 d-flex justify-content-between">
                        <h5 class="mb-0 fw-semibold text-dark d-flex align-items-center">
                            <i class="bx bx-task text-primary me-2" style="font-size: 1.5rem;"></i>
                            Tagihan Perusahaan 
                        </h5>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahTagihan">
                            Tambah Tagihan
                        </button>
                    </div>
                    @if (session('success_tagihan'))
                        <div class="alert alert-success">{{ session('success_tagihan') }}</div>
                    @endif
                    <div class="card-body p-4 mb-4 h-100 " style="height: 320px;">


                        {{-- Table Tagihan --}}
                        <div class="table-responsive mb-4" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="border-0 ps-4"></th>
                                        <th class="border-0" style="min-width: 160px;">Due Date</th>
                                        <th class="border-0" style="min-width: 180px;">Kegiatan</th>
                                        <th class="border-0" style="min-width: 150px;">Nominal</th>
                                        <th class="border-0" style="min-width: 120px;">Tracking</th>
                                        <th class="border-0 text-center pe-4" style="min-width: 120px;">Status</th>
                                        <th class="border-0 text-center pe-4" style="min-width: 120px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($trackingTagihanPerusahaans as $tagihan)
                                        <tr class="border-bottom ">
                                            @if ($tagihan->status === 'selesai')
                                                <td class="text-center ps-4"><input class="custom-check" type="checkbox" checked disabled></td>
                                            @elseif ($tagihan->status === 'telat')
                                                <td class="text-center ps-4"><input class="custom-fail" type="checkbox" checked disabled></td>
                                            @else
                                                <td class="text-center ps-4"><input class="check-blue" data-id="{{ $tagihan->id }}" type="checkbox" id="edit-tagihan"></td>
                                            @endif
                                            <td>
                                                @if ($tagihan->tanggal_perkiraan_mulai === $tagihan->tanggal_perkiraan_selesai || $tagihan->tanggal_perkiraan_selesai === null )
                                                    <div class="small">
                                                        {{ \Carbon\Carbon::parse($tagihan->tanggal_perkiraan_mulai)->format('d F') }}
                                                    </div>
                                                @else
                                                    <div class="small">
                                                        {{ \Carbon\Carbon::parse($tagihan->tanggal_perkiraan_selesai)->format('d M') }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="text-truncate" style="max-width: 150px;">
                                                        {{ $tagihan->tagihanPerusahaan->kegiatan ?? $tagihan->kegiatan }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="">
                                                    {{ $tagihan->nominal ? 'Rp. ' . number_format($tagihan->nominal, 0, ',', '.') : '-' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 300px;">
                                                    {{ $tagihan->tracking ?? '-' }} 
                                                </div>
                                            </td>
                                            <td class="text-center pe-4">
                                                @php
                                                    $statusConfig = [
                                                        'pending' => [
                                                            'color' => 'warning',
                                                            'icon' => 'bx-time-five',
                                                        ],
                                                        'proses' => [
                                                            'color' => 'primary',
                                                            'icon' => 'bx-loader-circle',
                                                        ],
                                                        'selesai' => [
                                                            'color' => 'success',
                                                            'icon' => 'bx-check-circle',
                                                        ],
                                                        'telat' => [
                                                            'color' => 'danger',
                                                            'icon' => 'bx-info-circle',
                                                        ],
                                                    ];
                                                    $config = $statusConfig[$tagihan->status] ?? [
                                                        'color' => 'secondary',
                                                        'icon' => 'bx-info-circle',
                                                    ];
                                                @endphp
                                                <span
                                                    class="badge bg-{{ $config['color'] }}-subtle text-{{ $config['color'] }} px-3 py-text-capitalize2 ">
                                                    <i class="bx {{ $config['icon'] }} me-1"></i>
                                                    {{ $tagihan->status }}
                                                </span>
                                            </td>
                                            <td class="text-center pe-4 position-relative">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle"
                                                            type="button"
                                                            data-bs-toggle="dropdown"
                                                            data-bs-boundary="viewport"
                                                            aria-expanded="false">
                                                        Aksi
                                                    </button>

                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <button class="dropdown-item" data-id="{{ $tagihan->id }}" data-bs-toggle="modal" id="edit-tagihan" data-bs-target="#modalEditTagihan">
                                                                Edit
                                                            </button>
                                                        </li>

                                                        <li>
                                                            <a class="dropdown-item"
                                                            href="{{ route('detailTagihanPerusahaan', $tagihan->id) }}">
                                                                Detail
                                                            </a>
                                                        </li>

                                                        <li>
                                                            <form action="{{ route('hapusTagihanPerusahaan', $tagihan->id) }}" method="POST"
                                                                onsubmit="return confirm('Yakin ingin menghapus?')">
                                                                @csrf
                                                                <button type="submit" class="dropdown-item text-danger">
                                                                    Hapus
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="bx bx-message-square-x text-muted"
                                                        style="font-size: 3rem;"></i>
                                                    <p class="text-muted mt-3 mb-0">Tidak ada tagihan untuk
                                                        ditampilkan
                                                    </p>
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
        </div>

        <div id="sectionOutstanding">
        <div class="row g-4 mb-5">
            <div class="col-12">
                <div class="card border-0 shadow-lg h-100 rounded-4 overflow-hidden">
                    <!-- Card Header -->
                    <div class="card-header bg-white border-bottom-0 py-4 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-semibold text-dark d-flex align-items-center">
                            <i class="bx bx-task text-primary me-2" style="font-size: 1.5rem;"></i>
                            Data Outstanding
                        </h5>
                    </div>

                    <div class="px-4 py-3 border-bottom">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <div class="w-md-25">
                                <input type="text" id="searchOutstanding" class="form-control"
                                    placeholder="Cari data outstanding..." autocomplete="off">
                            </div>

                            <div id="paginationInfo" class="text-muted small"></div>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Perusahaan</th>
                                    <th scope="col">Kelas</th>
                                    <th scope="col">Sales</th>
                                    <th scope="col">Tanggal</th>
                                    <th scope="col">Tagihan</th>
                                    <th scope="col">Tenggat Waktu</th>
                                    <th scope="col">Tanggal Bayar</th>
                                    <th scope="col">Nominal Pembayaran</th>
                                    <th scope="col">Admin Transfer</th>
                                    <th scope="col">Nominal Pph23</th>
                                    <th scope="col">Nominal PPN</th>
                                    <th scope="col">Uang Diterima</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Info</th>
                                </tr>
                            </thead>
                            <tbody id="outstandingTableBody">
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer bg-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                        <div id="paginationInfo" class="text-muted small"></div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-secondary" id="prevPage" disabled>Sebelumnya</button>
                            <span id="pageInfo" class="align-self-center"></span>
                            <button class="btn btn-sm btn-outline-secondary" id="nextPage">Selanjutnya</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card border-0 shadow-lg h-100 rounded-4 overflow-hidden mb-6">
            <div class="card-header bg-white border-bottom-0 pb-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h5 class="mb-0 fw-semibold text-dark d-flex align-items-center">
                        <i class="bx bx-line-chart text-primary me-2" style="font-size: 1.5rem;"></i>
                        Grafik Outstanding
                    </h5>

                    <!-- Filter Tahun -->
                    <div class="d-flex align-items-center gap-2">
                        <label for="filterTahun" class="form-label mb-0 text-secondary fw-medium">Tahun :</label>
                        <select id="filterTahun" class="form-select w-auto">
                            @for ($i = 0; $i < 6; $i++)
                                <option value="{{ now()->year - $i }}" 
                                        {{ (now()->year - $i) == now()->year ? 'selected' : '' }}>
                                    {{ now()->year - $i }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="row h-100">

                    <!-- CHART -->
                    <div class="col-lg-8">
                        <div class="chart-wrapper position-relative" style="height: 380px;">
                            <canvas id="grafikOutstanding"></canvas>

                            <div id="outstandingEmpty"
                                class="d-none position-absolute top-50 start-50 translate-middle
                                d-flex flex-column align-items-center text-center w-100">
                                <i class="bx bx-x-circle text-muted" style="font-size:3rem;"></i>
                                <p class="text-muted mt-2 mb-0">
                                    Tidak ada data Outstanding
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 d-flex flex-column gap-3 mt-4 mt-lg-0">

                        <div class="p-3 rounded-3 shadow-sm">
                            <h6 class="fw-semibold mb-3 text-dark">KPI Summary</h6>
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Target</span>
                                <b>100%</b>
                            </div>

                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Progress</span>
                                <b id="kpiProgress">0%</b>
                            </div>

                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Total Client</span>
                                <b id="totalClient">0</b>
                            </div>
                        </div>

                        <div class="p-3 bg-white border rounded-3 shadow-sm">

                            <h6 class="fw-semibold mb-3 text-dark">Detail Data</h6>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Belum Bayar</span>
                                <span id="lblBelumBayar" class="fw-bold">0</span>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Tepat Waktu</span>
                                <span id="lblTepatWaktu" class="fw-bold">0</span>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Terlambat</span>
                                <span id="lblTerlambat" class="fw-bold">0</span>
                            </div>

                        </div>

                        <div class="p-3 bg-white border rounded-3 shadow-sm">
                            <h6 class="fw-semibold mb-2 text-dark">Analisis</h6>
                            <p id="kpiAnalysis" class="mb-0 text-muted small">
                                -
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-lg h-100 rounded-4 overflow-hidden mb-6">
            <div class="card-header bg-white border-bottom-0 pb-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h5 class="mb-0 fw-semibold text-dark d-flex align-items-center">
                        <i class="bx bx-time-five text-info me-2" style="font-size: 1.5rem;"></i>
                        Grafik Ketepatan Waktu
                    </h5>

                    <div class="d-flex align-items-center gap-2">
                        <label for="filterTahunKetepatan" class="form-label mb-0 text-secondary fw-medium">Tahun :</label>
                        <select id="filterTahunKetepatan" class="form-select w-auto">
                            @for ($i = 0; $i < 6; $i++)
                                <option value="{{ now()->year - $i }}"
                                        {{ (now()->year - $i) == now()->year ? 'selected' : '' }}>
                                    {{ now()->year - $i }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="row h-100">
                    <div class="col-lg-8">
                        <div class="chart-wrapper position-relative" style="height: 380px;">
                            <canvas id="grafikKetepatanWaktu"></canvas>
                            <div id="ketepatanWaktuEmpty"
                                class="d-none position-absolute top-50 start-50 translate-middle
                                d-flex flex-column align-items-center text-center w-100">
                                <i class="bx bx-x-circle text-muted" style="font-size:3rem;"></i>
                                <p class="text-muted mt-2 mb-0">
                                    Tidak ada data
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 d-flex flex-column gap-3 mt-4 mt-lg-0">
                        <div class="p-3 rounded-3 shadow-sm">
                            <h6 class="fw-semibold mb-3 text-dark">KPI Summary</h6>
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Target</span>
                                <b>100%</b>
                            </div>
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Progress</span>
                                <b id="kpiProgressKetepatan">0%</b>
                            </div>
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Total Data</span>
                                <b id="totalDataKetepatan">0</b>
                            </div>
                        </div>

                        <div class="p-3 bg-white border rounded-3 shadow-sm">
                            <h6 class="fw-semibold mb-3 text-dark">Detail Data</h6>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Sesuai</span>
                                <span id="lblSesuaiKetepatan" class="fw-bold">0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Tidak Sesuai</span>
                                <span id="lblTidakSesuaiKetepatan" class="fw-bold">0</span>
                            </div>
                        </div>

                        <div class="p-3 bg-white border rounded-3 shadow-sm">
                            <h6 class="fw-semibold mb-2 text-dark">Analisis</h6>
                            <p id="kpiAnalysisKetepatan" class="mb-0 text-muted small">
                                -
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>

        @endif

        @if (Auth::user()->jabatan === 'HRD')
        <!-- Modal Tambah -->
        <div class="modal fade" id="modalTambahHariLibur" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Tambah Hari Libur</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <form method="post" action="{{ route('storeHariLibur') }}"
                            enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label col-form-label">Nama Hari Libur <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control" autocomplete="off">
                            </div>
                            <div class="mb-3">
                                <label class="form-label col-form-label">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" class="form-control">
                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Edit -->
        <div class="modal fade" id="modalEditHariLibur" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Edit Hari Libur</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <form id="formEditHariLibur" method="POST" enctype="multipart/form-data">
                            @csrf

                            <input type="hidden" id="id">

                            <div class="mb-3">
                                <label class="form-label">Nama Hari Libur <span class="text-danger">*</span></label>
                                <input type="text" id="nama" name="nama" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" id="tanggal" name="tanggal" class="form-control">
                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    
        <div class="row g-4 mb-5">
            <div class="col-12">
                <div class="card border-0 shadow-lg h-100 rounded-4 overflow-hidden glass-force">
                    <div class="card-header border-bottom-0 pb-0 d-flex justify-content-between">
                        <h5 class="mb-0 fw-semibold text-dark d-flex align-items-center">
                            <i class="bx bx-calendar text-primary me-2" style="font-size: 1.5rem;"></i>
                            Hari Libur 
                        </h5>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahHariLibur">
                            Tambah Hari Libur
                        </button>
                    </div>

                    @if (session('success_libur'))
                        <div class="alert alert-success">{{ session('success_libur') }}</div>
                    @endif

                    <div class="card-body p-4 mb-4 h-100 row g-4">
                        <!-- Kalender -->
                        <div class="col-xl-8">
                            <div class="card border-0 shadow-lg h-100 rounded-4 overflow-hidden glass-force">
                                <div class="card-body p-4">
                                    <div id="calendar" class="fc-custom"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Detail & List Hari Libur -->
                        <div class="col-xl-4">
                            <div class="card border-0 shadow-lg h-100 rounded-4 overflow-hidden d-flex flex-column glass-force">
                                <div class="card-header border-bottom-0 pb-0">
                                    <h5 class="mb-0 fw-semibold text-dark d-flex align-items-center">
                                        <i class="bx bx-calendar-check text-success me-2" style="font-size: 1.5rem;"></i>
                                        Hari Libur Bulan Ini
                                    </h5>
                                </div>
                                <div class="card-body p-4 flex-grow-1 d-flex flex-column" style="height: auto; gap: 12px;">
                                    <div id="holiday-list" style="flex: 1; min-height: 200px; overflow-y: auto;">
                                        <div class="text-center py-4">
                                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <p class="text-muted mt-2 mb-0 small">Memuat data libur...</p>
                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <div id="holiday-detail" class="pt-2">
                                        <div class="text-center py-3">
                                            <i class="bx bx-info-circle text-muted" style="font-size: 2.5rem; opacity: 0.5;"></i>
                                            <p class="text-muted small mt-2 mb-0">Klik tanggal libur di kalender</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>        
                </div>
            </div>

        </div>


        {{-- Administrasi Karyawan --}}
        <!-- Modal Tambah -->
        <div class="modal fade" id="modalTambahAdministrasiKaryawan" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Tambah Administrasi Karyawan</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <form method="post" action="{{ route('administrasi.karyawan.store') }}"
                            enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label col-form-label">Nama Administrasi <span class="text-danger">*</span></label>
                                <input type="text" name="nama_administrasi" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label col-form-label">Dateline</label>
                                <input type="date" name="dateline" class="form-control col-md-6 mb-2">
                            </div>

                            <div class="mb-3">  
                                <label for="keterangan" class="col-md-5 col-form-label">Keterangan (Optional)</label>  
                                <textarea class="form-control" name="keterangan"></textarea>  
                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal Edit -->
        <div class="modal fade" id="modalEditAdministrasi" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Edit Administrasi Karyawan</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <form method="post" id="formEditAdministrasi" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Nama Administrasi <span class="text-danger">*</span></label>
                                <input type="text" name="nama_administrasi" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Dateline</label>
                                <input type="date" name="dateline" class="form-control">
                            </div>

                            <div class="mb-3">
                                <small class="text-muted">* Status selesai dan terlambat otomatis terupdate dari sistem</small>
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="pending">Pending</option>
                                    <option value="proses">Proses</option>
                                    <option value="selesai" disabled hidden>Selesai</option>
                                    <option value="terlambat" disabled hidden>Terlambat</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Bukti Transfer</label>
                                <small id="pathBuktiTransfer" class="text-muted"></small>
                                <input type="file" name="bukti_transfer" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea class="form-control" name="keterangan"></textarea>
                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">
                                    Simpan
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal Eksport -->
        <div class="modal fade" id="modalEksportAdministrasi" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content border-0 shadow">

                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-semibold">
                            <i class="bi bi-download me-2"></i> Eksport Data Administrasi
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body pt-3">
                        <form method="post" id="formEksportAdministrasi"
                            action="{{ route('administrasi.karyawan.eksport') }}">
                            @csrf

                            <div class="mb-4">
                                <h6 class="text-muted mb-3">Berdasarkan Periode</h6>

                                <div class="row g-3">

                                    <div class="col-md-4">
                                        <label class="form-label small">Tahun</label>
                                        <select name="tahun" id="eksportTahunanAdminis" class="form-select">
                                            <option value="" selected>Pilih Tahun</option>
                                            @php
                                                $tahun_sekarang = now()->year;
                                                for ($tahun = 2023; $tahun <= $tahun_sekarang + 2; $tahun++) {
                                                    echo "<option value=\"$tahun\">$tahun</option>";
                                                }
                                            @endphp
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label small">Bulan</label>
                                        <select name="bulan" id="eksportBulananAdminis" class="form-select">
                                            <option value="" selected>Pilih Bulan</option>
                                            @php
                                                $nama_bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                                for ($bulan = 1; $bulan <= 12; $bulan++) {
                                                    echo "<option value=\"$bulan\">{$nama_bulan[$bulan - 1]}</option>";
                                                }
                                            @endphp
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label small">Triwulan</label>
                                        <select name="quartal" id="eksportQuartalAdminis" class="form-select">
                                            <option value="" selected>Pilih Triwulan</option>
                                            <option value="1">Q1 (Jan - Mar)</option>
                                            <option value="2">Q2 (Apr - Jun)</option>
                                            <option value="3">Q3 (Jul - Sep)</option>
                                            <option value="4">Q4 (Okt - Des)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <h6 class="text-muted mb-3">Berdasarkan Rentang Tanggal</h6>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small">Dari Tanggal</label>
                                        <input type="date" name="start_date" class="form-control">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small">Sampai Tanggal</label>
                                        <input type="date" name="end_date" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer border-0 pt-3">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-download me-1"></i> Eksport
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-4 mb-5">
            <div class="col-12">
                <div class="card border-0 shadow-lg h-100 rounded-4 overflow-hidden glass-force">
                    <div class="card-header border-bottom-0 pb-0 d-flex justify-content-between">
                        <h5 class="mb-0 fw-semibold text-dark d-flex align-items-center">
                            <i class="bx bx-task text-primary me-2" style="font-size: 1.5rem;"></i>
                            Administrasi Karyawan 
                        </h5>
                        <div class="d-flex gap-4">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahAdministrasiKaryawan">
                                Tambah Administrasi Karyawan
                            </button>
                            <div class="d-flex gap-4 align-items-center">
                                <h6 class="mb-0">Export : </h6>
                                <button type="button" data-bs-toggle="modal" data-bs-target="#modalEksportAdministrasi" class="btn btn-outline-secondary btn-sm pdfBtn">
                                PDF
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    @if (session('success_administrasi'))
                        <div class="alert alert-success">{{ session('success_administrasi') }}</div>
                    @endif
                    <div class="card-body p-4 mb-4 h-100 " style="height: 320px;">                        

                        {{-- Table administrasi --}}
                        <div class="table-responsive mb-4" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="border-0 ps-4"></th>
                                        <th class="border-0" style="min-width: 160px;">Administrasi Karyawan</th>
                                        <th class="border-0" style="min-width: 180px;">Tanggal Dateline</th>
                                        <th class="border-0" style="min-width: 150px;">Tanggal Selesai</th>
                                        <th class="border-0 text-center pe-4" style="min-width: 120px;">Status</th>
                                        <th class="border-0" style="min-width: 120px;">Progress</th>
                                        <th class="border-0 text-center pe-4" style="min-width: 120px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($administrasis as $administrasi)
                                        <tr class="border-bottom ">
                                            @if ($administrasi->status === 'selesai')
                                                <td class="text-center ps-4"><input class="custom-check" type="checkbox" checked disabled></td>
                                            @elseif ($administrasi->status === 'terlambat')
                                                <td class="text-center ps-4"><input class="custom-fail" type="checkbox" checked disabled></td>
                                            @else
                                                <td class="text-center ps-4"><input class="check-blue edit-administrasi" data-id="{{ $administrasi->id }}" type="checkbox"></td>
                                            @endif
                                            <td>
                                                {{ $administrasi->nama_administrasi }}
                                            </td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($administrasi->dateline)->format('l, d F Y') }}
                                            </td>
                                            <td>
                                                {{ $administrasi->tanggal_selesai ? \Carbon\Carbon::parse($administrasi->tanggal_selesai)->format('l, d F Y') : '-'}}
                                            </td>
                                            <td class="text-center pe-4">
                                                @php
                                                    $statusConfig = [
                                                        'pending' => [
                                                            'color' => 'warning',
                                                            'icon' => 'bx-time-five',
                                                        ],
                                                        'proses' => [
                                                            'color' => 'primary',
                                                            'icon' => 'bx-loader-circle',
                                                        ],
                                                        'selesai' => [
                                                            'color' => 'success',
                                                            'icon' => 'bx-check-circle',
                                                        ],
                                                        'terlambat' => [
                                                            'color' => 'danger',
                                                            'icon' => 'bx-info-circle',
                                                        ],
                                                    ];
                                                    $config = $statusConfig[$administrasi->status] ?? [
                                                        'color' => 'secondary',
                                                        'icon' => 'bx-info-circle',
                                                    ];
                                                @endphp
                                                <span
                                                    class="badge bg-{{ $config['color'] }}-subtle text-{{ $config['color'] }} px-3 py-2 text-capitalize">
                                                    <i class="bx {{ $config['icon'] }} me-1"></i>
                                                    {{ $administrasi->status }}
                                                </span>
                                            </td>
                                            <td class="text-center pe-4">
                                                @php
                                                    if ($administrasi->tanggal_selesai) {
                                                        $diff = \Carbon\Carbon::parse($administrasi->dateline)
                                                            ->diffInDays(\Carbon\Carbon::parse($administrasi->tanggal_selesai), false);

                                                        if ($diff <= 0 || $administrasi->status === 'selesai') {
                                                            $progress = 100;
                                                            $color = 'success';
                                                        } elseif ($diff <= 3) {
                                                            $progress = 80;
                                                            $color = 'warning';
                                                        } elseif ($diff <= 7) {
                                                            $progress = 60;
                                                            $color = 'warning';
                                                        } else {
                                                            $progress = 0;
                                                            $color = 'danger';
                                                        }
                                                    } else {
                                                        $progress = 0;
                                                        $color = 'danger';
                                                    }
                                                @endphp

                                                <span class="badge bg-{{ $color }}-subtle text-{{ $color }}">
                                                    {{ $progress }}%
                                                </span>
                                            </td>
                                            <td class="text-center pe-4 position-relative">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle"
                                                            type="button"
                                                            data-bs-toggle="dropdown"
                                                            data-bs-boundary="viewport"
                                                            aria-expanded="false">
                                                        Aksi
                                                    </button>

                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <button class="dropdown-item edit-administrasi"
                                                                    data-id="{{ $administrasi->id }}"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#modalEditAdministrasi">
                                                                Edit
                                                            </button>
                                                        </li>

                                                       <li>
                                                            @if($administrasi->bukti_transfer)
                                                                <a class="dropdown-item" href="{{ asset('storage/'.$administrasi->bukti_transfer) }}" target="_blank">
                                                                    Lihat Bukti Transfer
                                                                </a>
                                                            @endif
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item"
                                                            href="{{ route('administrasi.karyawan.edit', $administrasi->id) }}">
                                                                Detail
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <form action="{{ route('administrasi.karyawan.destroy', $administrasi->id) }}"
                                                                method="POST"
                                                                onsubmit="return confirm('Yakin ingin menghapus administrasi ini?')">
                                                                @csrf
                                                                @method('DELETE')

                                                                <button type="submit" class="dropdown-item text-danger">
                                                                    <i class="bx bx-trash me-2"></i> Hapus
                                                                </button>
                                                            </form>
                                                        </li>

                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="bx bx-message-square-x text-muted"
                                                        style="font-size: 3rem;"></i>
                                                    <p class="text-muted mt-3 mb-0">Tidak ada administrasi untuk
                                                        ditampilkan
                                                    </p>
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
        </div>
        @endif

        <!-- Chart & Tidak Hadir -->
        <div class="row g-4">
            <!-- Chart Kehadiran -->
            <div class="col-xl-8">
                <div class="card border-0 shadow-lg h-100 rounded-4 overflow-hidden glass-force">
                    <div class="card-header border-bottom-0 pb-0">
                        <h5 class="mb-0 fw-semibold text-dark d-flex align-items-center">
                            <i class="bx bx-line-chart text-primary me-2" style="font-size: 1.5rem;"></i>
                            Grafik Kehadiran 7 Hari Terakhir
                        </h5>
                    </div>
                    <div class="card-body p-4"
                        style="height: 320px; display: flex; flex-direction: column; justify-content: space-between;">
                        <div style="flex-grow: 1; position: relative; height: 250px;">
                            <canvas id="kehadiranChart" style="height: 100% !important; width: 100% !important;"></canvas>
                        </div>
                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                Rata-rata kehadiran:
                                {{ round(array_sum($kehadiranChart['data']) / count($kehadiranChart['data']), 1) }}
                                dari {{ $total_karyawan }} karyawan
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Karyawan Tidak Hadir Hari Ini -->
            <div class="col-xl-4">
                <div class="card border-0 shadow-lg h-100 rounded-4 overflow-hidden glass-force">
                    <div class="card-header border-bottom-0 pb-0">
                        <h5 class="mb-0 fw-semibold text-dark d-flex align-items-center">
                            <i class="bx bx-user-x text-danger me-2" style="font-size: 1.5rem;"></i>
                            Tidak Hadir Hari Ini
                        </h5>
                    </div>
                    <div class="card-body p-4 scrollbar-custom" style="max-height: 480px; overflow-y: auto;">
                        @if (count($tidakHadirList) > 0)
                            <div class="list-group list-group-flush">
                                @foreach ($tidakHadirList as $item)
                                    <div class="list-group-item bg-transparent px-0 py-3 border-bottom">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="avatar avatar-sm bg-opacity-15 rounded-circle">
                                                    <span class="text-danger fw-bold small">
                                                        {{ strtoupper(substr($item['nama'], 0, 1)) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-1 fw-medium text-dark">{{ $item['nama'] }}</h6>
                                                <small class="text-muted d-block">{{ $item['divisi'] }}</small>
                                                <span class="badge bg-warning text-dark mt-1">Tidak Hadir</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3 text-center">
                                <small class="text-danger fw-medium">
                                    Total: {{ count($tidakHadirList) }} karyawan
                                </small>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bx bx-check-circle text-success" style="font-size: 4rem; opacity: 0.8;"></i>
                                <p class="text-success fw-bold mt-3 mb-0" style="font-size: 1.1rem;">
                                    Semua karyawan hadir hari ini!
                                </p>
                                <small class="text-muted d-block mt-2">Kehadiran 100% 👏</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Chart Cuti -->
            <div class="col-xl-12">
                <div class="card border-0 shadow-lg h-100 rounded-4 overflow-hidden glass-force">
                    <div class="card-header border-bottom-0 pb-0 d-flex justify-content-between">
                        <h5 class="mb-0 fw-semibold text-dark d-flex align-items-center">
                            <i class="bx bx-pie-chart-alt text-primary me-2" style="font-size: 1.5rem;"></i>
                            Grafik Cuti<span class="ms-2" id="rentangWaktu"></span>
                        </h5>
                        <div class="d-flex gap-4 align-items-center">
                            <h6 class="mb-0">Export : </h6>
                            <button type="button" id="exportCuti" class="btn btn-outline-secondary btn-sm pdfBtn">
                            PDF
                            </button>
                        </div>
                    </div>
                    <div class="card-body pe-5 mt-5" style="height: 320px;">
                        <div class="chart-container d-flex align-items-center" style="position: relative; height: 100%;">
                            <canvas id="dataCuti"></canvas>
                            <div id="cutiEmpty" class="d-none d-flex flex-column align-items-center container-fluid">
                                <i class="bx bx-x-circle text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3 mb-0">Tidak ada data Cuti</p>
                            </div>
                        </div>
                    </div>
                    <div class="container">
                        <div class="row">
                            <div class="col">
                                <label for="filterCutiTahun" class="mb-1 ms-1">Tahun</label>
                                <select id="filterCutiTahun" class="form-select mb-3">
                                    <option value="default" disabled selected>Berdasarkan Tahun</option>
                                    @php
                                        $tahun_sekarang = now()->year;
                                        for ($tahun = 2023; $tahun <= $tahun_sekarang + 2; $tahun++) {
                                            echo "<option value=\"$tahun\">$tahun</option>";
                                        }
                                    @endphp
                                </select>
                            </div>
                            <div class="col">
                                <label for="filterCutiBulan" class="mb-1 ms-1">Bulan</label>
                                <select id="filterCutiBulan" class="form-select mb-3">
                                    <option value="default" disabled selected>Berdasarkan Bulan</option>
                                    @php
                                    $bulan_sekarang = now()->month;
                                    $nama_bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                    for ($bulan = 1; $bulan <= 12; $bulan++) {
                                        echo "<option value=\"$bulan\">{$nama_bulan[$bulan - 1]}</option>";
                                    }
                                    @endphp
                                </select>
                            </div>
                            <div class="col">
                                <label for="filterCutiTriwulan" class="mb-1 ms-1">Triwulan</label>
                                <select id="filterCutiTriwulan" class="form-select mb-3">
                                    <option value="default" disabled selected>Berdasarkan Triwulan</option>
                                    <option value="1">Quarter 1</option>
                                    <option value="2">Quarter 2</option>
                                    <option value="3">Quarter 3</option>
                                    <option value="4">Quarter 4</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- End Chart Cuti --}}

            {{-- Total Mengajar Instruktur --}}
            <div class="col-xl-12">
                <div class="card border-0 shadow-lg h-100 rounded-4 overflow-hidden glass-force">
                    <div class="card-header border-bottom-0 pb-0 d-flex justify-content-between">
                        <h5 class="mb-0 fw-semibold text-dark d-flex align-items-center">
                            <i class="bx bx-archive text-primary me-2" style="font-size: 1.5rem;"></i>
                            Total Mengajar Instruktur <span class="ms-2" id="rentangWaktuMengajar"></span>
                        </h5>
                        <div class="d-flex gap-4 align-items-center">
                            <h6 class="mb-0">Export : </h6>
                            <button type="button" id="exportTotalMengajar" class="btn btn-outline-secondary btn-sm pdfBtn">
                            PDF
                            </button>
                        </div>
                    </div>
                    <div class="container mt-5">
                        <div class="row">
                            <div class="col">
                                <label for="filterMengajarPerTahun" class="mb-1 ms-1">Tahun</label>
                                <select id="filterMengajarPerTahun" class="form-select mb-3">
                                    <option value="default" disabled selected>Berdasarkan Tahun</option>
                                    @php
                                        $tahun_sekarang = now()->year;
                                        for ($tahun = 2023; $tahun <= $tahun_sekarang + 2; $tahun++) {
                                            echo "<option value=\"$tahun\">$tahun</option>";
                                        }
                                    @endphp
                                </select>
                            </div>
                            <div class="col">
                                <label for="filterMengajarPerBulan" class="mb-1 ms-1">Bulan</label>
                                <select id="filterMengajarPerBulan" class="form-select mb-3">
                                    <option value="default" disabled selected>Berdasarkan Bulan</option>
                                    @php
                                    $bulan_sekarang = now()->month;
                                    $nama_bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                    for ($bulan = 1; $bulan <= 12; $bulan++) {
                                        echo "<option value=\"$bulan\">{$nama_bulan[$bulan - 1]}</option>";
                                    }
                                    @endphp
                                </select>
                            </div>
                            <div class="col">
                                <label for="filterMengajarPerTriwulan" class="mb-1 ms-1">Triwulan</label>
                                <select id="filterMengajarPerTriwulan" class="form-select mb-3">
                                    <option value="default" disabled selected>Berdasarkan Triwulan</option>
                                    <option value="1">Quarter 1</option>
                                    <option value="2">Quarter 2</option>
                                    <option value="3">Quarter 3</option>
                                    <option value="4">Quarter 4</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0 mt-2">
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0" id="tabelTotalMengajar">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <td class="border-0" style="width: 5%">No</td>
                                        <td class="border-0" style="min-width: 70%">Nama Lengkap</td>
                                        <td class="border-0" style="min-width: 15%">Kode Instruktur</td>
                                        <td class="border-0" style="min-width: 10%">Total Mengajar</td>
                                    </tr>
                                </thead>
                                <tbody class="text-muted fw-medium">
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            {{-- End Total Mengajar Instrukrur --}}

            <!-- Chart nilai Feedback -->
            <div class="card border-0 shadow-lg h-100 rounded-4 overflow-hidden glass-force">
                <div class="card-header border-bottom-0 pb-0 d-flex justify-content-between">
                    <h5 class="mb-0 fw-semibold text-dark d-flex align-items-center">
                        <i class="bx bx-line-chart text-primary me-2" style="font-size: 1.5rem;"></i>
                        Grafik Feedback
                    </h5>
                    <div class="d-flex gap-4 align-items-center">
                        <h6 class="mb-0">Export : </h6>
                        <button type="button" id="exportFeedbackInstruktur" class="btn btn-outline-secondary btn-sm pdfBtn">
                            PDF
                        </button>
                    </div>
                </div>

                <div class="card-body p-4"
                    style="height: 400px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div class="card-body p-4 chart-wrapper">
                        <canvas id="feedbackChart"></canvas>
                        <div id="nilaiInstrukturEmpty" class="d-none position-absolute top-50 start-50 translate-middle
                                d-flex flex-column align-items-center text-center w-100">
                            <i class="bx bx-x-circle text-muted" style="font-size:3rem;"></i>
                            <p class="text-muted mt-2 mb-0">
                                Tidak ada data Nilai Instruktur
                            </p>
                        </div>

                    </div>

                </div>
                <div class="container">
                    <div class="row">
                        <div class="col">
                            <label for="filterFeedbackTahun" class="mb-1 ms-1">Tahun</label>
                            <select id="filterFeedbackTahun" class="form-select mb-3">
                                <option value="default" disabled selected>Berdasarkan Tahun</option>
                                @php
                                    $tahun_sekarang = now()->year;
                                    for ($tahun = 2023; $tahun <= $tahun_sekarang + 2; $tahun++) {
                                        echo "<option value=\"$tahun\">$tahun</option>";
                                    }
                                @endphp
                            </select>
                        </div>
                        <div class="col">
                            <label for="filterFeedbackBulan" class="mb-1 ms-1">Bulan</label>
                            <select id="filterFeedbackBulan" class="form-select mb-3">
                                <option value="default" disabled selected>Berdasarkan Bulan</option>
                                @php
                                    $bulan_sekarang = now()->month;
                                    $nama_bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                    for ($bulan = 1; $bulan <= 12; $bulan++) {
                                        echo "<option value=\"$bulan\">{$nama_bulan[$bulan - 1]}</option>";
                                    }
                                @endphp
                            </select>
                        </div>
                        <div class="col">
                            <label for="filterFeedbackTriwulan" class="mb-1 ms-1">Triwulan</label>
                            <select id="filterFeedbackTriwulan" class="form-select mb-3">
                                <option value="default" disabled selected>Berdasarkan Triwulan</option>
                                <option value="1">Quarter 1</option>
                                <option value="2">Quarter 2</option>
                                <option value="3">Quarter 3</option>
                                <option value="4">Quarter 4</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            {{-- End Chart nilai Feedback --}}

            <div class="row g-3 mb-4">
                {{-- RKM Berjalan Minggu Ini --}}
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="card h-100 shadow-sm border-0 rounded-3 glass-force">
                            <div class="card-header border-bottom py-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h5 class="card-title mb-0 fw-semibold">
                                        <i class="bx bx-calendar text-primary me-2"></i>
                                        Rencana Kelas Mingguan
                                    </h5>

                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge bg-primary-subtle text-primary px-3 py-2">
                                            {{ count($rkms) }} RKM
                                        </span>

                                        <span class="badge bg-success-subtle text-success px-3 py-2">
                                            {{ number_format($jumlahPeserta, 0, ',', '.') }} Peserta
                                        </span>

                                        <span class="badge bg-success-subtle text-success px-3 py-2">
                                            {{ number_format($jumlahInstruktur, 0, ',', '.') }} Instruktur
                                        </span>
                                    </div>
                                </div>
                                
                            </div>
                            
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 1000px; overflow-y: auto;">
                                    <table class="table table-hover align-middle mb-0" style="table-layout: auto;">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th scope="col" rowspan="2" class="border-0 ps-4" style="min-width: 50px;">No</th>
                                                <th scope="col" rowspan="2" class="border-0" style="min-width: 250px;">Materi</th>
                                                <th scope="col" rowspan="2" class="border-0" style="min-width: 150px;">Harga</th>
                                                <th scope="col" rowspan="2" class="border-0" style="min-width: 170px;">Tanggal Training</th>
                                                <th scope="col" rowspan="2" class="border-0" style="min-width: 170px;">Perusahaan</th>
                                                <th scope="col" rowspan="2" class="border-0" style="min-width: 100px;">Kode Sales</th>
                                                <th scope="col" rowspan="2" class="border-0" style="min-width: 100px;">Instruktur</th>
                                                <th scope="col" rowspan="2" class="border-0" style="min-width: 150px;">Ruang</th>
                                                <th scope="col" rowspan="2" class="border-0" style="min-width: 100px;">Pax</th>
                                                <th scope="col" rowspan="2" class="border-0" class="border-0 text-center pe-4" style="min-width: 100px;">Exam</th>
                                                <th scope="col" rowspan="2" class="border-0" style="min-width: 120px;">Makanan</th>
                                                {{-- CheckList --}}
                                                <th scope="col" colspan="7" class="border-bottom border-dark text-center" style="min-width: 300px;">Checklist</th>

                                                <th scope="col" rowspan="2" class="border-0">Eksport</th>
                                            </tr>
                                            <tr class="text-center">
                                                <th scope="col" class="border-0" style="min-width: 120px;">Tanggal Keperluan</th>
                                                <th scope="col" class="border-0" style="min-width: 120px;">Materi</th>
                                                <th scope="col" class="border-0" style="min-width: 120px;">Kelas</th>
                                                <th scope="col" class="border-0" style="min-width: 120px;">Coffe Break</th>
                                                <th scope="col" class="border-0" style="min-width: 120px;">Makan Siang</th>
                                                <th scope="col" class="border-0" style="min-width: 120px;">Keperluan Kelas</th>
                                                <th scope="col" class="border-0 text-center pe-4" style="min-width: 120px;">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($rkms as $detail_rkm)
                                                @php
                                                    $checklists = $detail_rkm->checklists ?? [];
                                                    $rowspan = count($checklists) > 0 ? count($checklists) : 1;
                                                @endphp
                                                @if(count($checklists) > 0)
                                                    @foreach ($checklists as $tanggal => $item)
                                                        <tr class="border-bottom">

                                                            @if ($loop->first)
                                                                <td class="ps-4" rowspan="{{ $rowspan }}">{{ $loop->parent->iteration }}</td>

                                                                <td rowspan="{{ $rowspan }}">
                                                                    {{ $detail_rkm->materi->nama_materi }}
                                                                </td>

                                                                <td rowspan="{{ $rowspan }}">
                                                                    <span class="text-success fw-semibold">
                                                                        Rp {{ number_format($detail_rkm->harga_jual, 0, ',', '.') }}
                                                                    </span>
                                                                </td>

                                                                <td rowspan="{{ $rowspan }}">
                                                                    @if ($detail_rkm->tanggal_awal == $detail_rkm->tanggal_akhir)
                                                                        {{ \Carbon\Carbon::parse($detail_rkm->tanggal_awal)->translatedFormat('d M Y') }}
                                                                    @else
                                                                        {{ \Carbon\Carbon::parse($detail_rkm->tanggal_awal)->translatedFormat('d M Y') }}
                                                                        -
                                                                        {{ \Carbon\Carbon::parse($detail_rkm->tanggal_akhir)->translatedFormat('d M Y') }}
                                                                    @endif
                                                                </td>

                                                                <td rowspan="{{ $rowspan }}">
                                                                    @foreach ($detail_rkm->perusahaan as $perusahaan)
                                                                        {{ $perusahaan->nama_perusahaan }},
                                                                    @endforeach
                                                                </td>

                                                                <td rowspan="{{ $rowspan }}">{{ $detail_rkm->sales_all }}</td>
                                                                <td rowspan="{{ $rowspan }}"> {{ implode(', ', array_filter([$detail_rkm->instruktur_key, $detail_rkm->instruktur_key2, $detail_rkm->asisten_key])) }}</td>
                                                                <td rowspan="{{ $rowspan }}">{{ $detail_rkm->ruang ?? 'Belum Ditentukan' }}</td>
                                                                <td rowspan="{{ $rowspan }}">
                                                                    <span class="badge bg-info-subtle text-info px-3 py-2">
                                                                        {{ number_format($detail_rkm->pax, 0, ',', '.') }}
                                                                    </span>
                                                                </td>

                                                                <td rowspan="{{ $rowspan }}">
                                                                    @if ($detail_rkm->exam == '1')
                                                                        <span class="badge bg-success-subtle text-success px-3 py-2">
                                                                            Ya
                                                                        </span>
                                                                    @else
                                                                        <span
                                                                            class="badge bg-secondary-subtle text-secondary px-3 py-2">
                                                                            Tidak
                                                                        </span>
                                                                    @endif
                                                                </td>

                                                                <td rowspan="{{ $rowspan }}">
                                                                    @php
                                                                        $makananList = $detail_rkm->makanan ? explode(', ', $detail_rkm->makanan) : [];
                                                                        $makananValue = count($makananList) > 0 ? $makananList[0] : 'Tidak Ada';
                                                                    @endphp

                                                                    @if ($makananValue == '0' || $makananValue == 'Tidak Ada')
                                                                        Tidak Ada
                                                                    @elseif ($makananValue == '1' || $makananValue == 'Nasi Box')
                                                                        Nasi Box
                                                                    @elseif ($makananValue == '2' || $makananValue == 'Prasmanan')
                                                                        Prasmanan
                                                                    @else
                                                                        Belum Ditentukan
                                                                    @endif
                                                                </td>
                                                            @endif

                                                            <td class="text-center">
                                                                {{ \Carbon\Carbon::parse($tanggal)->format('d M') }}
                                                            </td>

                                                            <td class="text-center">
                                                                <input type="checkbox" class="custom-check" {{ $item->materi ? 'checked' : '' }} disabled>
                                                            </td>

                                                            <td class="text-center">
                                                                @if ($detail_rkm->metode_kelas === 'Offline')
                                                                    <input type="checkbox" class="custom-check" {{ $item->kelas ? 'checked' : '' }} disabled>
                                                                @else
                                                                -
                                                                @endif
                                                            </td>

                                                            <td class="text-center">
                                                                <input type="checkbox" class="custom-check" {{ $item->cb ? 'checked' : '' }} disabled>
                                                            </td>

                                                            <td class="text-center">
                                                                <input type="checkbox" class="custom-check" {{ $item->maksi ? 'checked' : '' }} disabled>
                                                            </td>

                                                            <td class="text-center">
                                                                @if ($detail_rkm->metode_kelas === 'Offline')
                                                                    <input type="checkbox" class="custom-check" {{ $item->keperluan_kelas ? 'checked' : '' }} disabled>
                                                                @else
                                                                -
                                                                @endif
                                                            </td>

                                                            <td class="text-center">
                                                                {{ $item->progress ?? 0 }}%
                                                            </td>
                                                            @if ($loop->first)
                                                                <td rowspan="{{ $rowspan }}" class="text-center align-middle">
                                                                    <a href="{{ route('export.pdf.checklist', $detail_rkm->id) }}" id="exportPdfRkm" class="btn btn-outline-danger btn-sm mb-1">
                                                                        PDF
                                                                    </a>
                                                                    <a href="{{ route('export.excel.checklist', $detail_rkm->id) }}" id="exportExcelRkm" class="btn btn-outline-success btn-sm">
                                                                        Excel
                                                                    </a>
                                                                </td>
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr class="border-bottom">
                                                        <td class="ps-4">{{ $loop->iteration }}</td>

                                                        <td>
                                                            {{ $detail_rkm->materi->nama_materi }}
                                                        </td>

                                                        <td>
                                                            <span class="text-success fw-semibold">
                                                                Rp {{ number_format($detail_rkm->harga_jual, 0, ',', '.') }}
                                                            </span>
                                                        </td>

                                                        <td>
                                                            @if ($detail_rkm->tanggal_awal == $detail_rkm->tanggal_akhir)
                                                                {{ \Carbon\Carbon::parse($detail_rkm->tanggal_awal)->translatedFormat('d M Y') }}
                                                            @else
                                                                {{ \Carbon\Carbon::parse($detail_rkm->tanggal_awal)->translatedFormat('d M Y') }}
                                                                -
                                                                {{ \Carbon\Carbon::parse($detail_rkm->tanggal_akhir)->translatedFormat('d M Y') }}
                                                            @endif
                                                        </td>

                                                        <td>
                                                            @foreach ($detail_rkm->perusahaan as $perusahaan)
                                                                {{ $perusahaan->nama_perusahaan }},
                                                            @endforeach
                                                        </td>

                                                        <td>{{ $detail_rkm->sales_all }}</td>
                                                        <td> {{ implode(', ', array_filter([$detail_rkm->instruktur_key, $detail_rkm->instruktur_key2, $detail_rkm->asisten_key])) }}</td>
                                                        <td>{{ $detail_rkm->ruang ?? 'Belum Ditentukan' }}</td>
                                                        <td>
                                                            <span class="badge bg-info-subtle text-info px-3 py-2">
                                                                {{ number_format($detail_rkm->pax, 0, ',', '.') }}
                                                            </span>
                                                        </td>

                                                        <td>
                                                            @if ($detail_rkm->exam == '1')
                                                                <span class="badge bg-success-subtle text-success px-3 py-2">
                                                                    Ya
                                                                </span>
                                                            @else
                                                                <span
                                                                    class="badge bg-secondary-subtle text-secondary px-3 py-2">
                                                                    Tidak
                                                                </span>
                                                            @endif
                                                        </td>

                                                        <td>
                                                            @php
                                                                $makananList = $detail_rkm->makanan ? explode(', ', $detail_rkm->makanan) : [];
                                                                $makananValue = count($makananList) > 0 ? $makananList[0] : 'Tidak Ada';
                                                            @endphp

                                                            @if ($makananValue == '0' || $makananValue == 'Tidak Ada')
                                                                Tidak Ada
                                                            @elseif ($makananValue == '1' || $makananValue == 'Nasi Box')
                                                                Nasi Box
                                                            @elseif ($makananValue == '2' || $makananValue == 'Prasmanan')
                                                                Prasmanan
                                                            @else
                                                                Belum Ditentukan
                                                            @endif
                                                        </td>

                                                        {{-- Kolom checklist kosong --}}
                                                        <td colspan="8" class="text-center text-muted">
                                                            Tidak ada checklist
                                                        </td>
                                                    </tr>
                                                    @endif

                                            @empty
                                                <tr>
                                                    <td colspan="12" class="text-center py-5">
                                                        Tidak ada data
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Daftar Ticketing --}}
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="card h-100 shadow-sm border-0 rounded-3 glass-force">
                            <div class="card-header border-bottom py-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h5 class="card-title mb-0 fw-semibold">
                                        <i class="bx bx-support text-primary me-2"></i>
                                        Ticketing
                                    </h5>
                                    <span class="badge bg-primary-subtle text-primary">{{ count($ticket) }} Ticket</span>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th class="border-0 ps-4" style="min-width: 160px;">Timestamp</th>
                                                <th class="border-0" style="min-width: 180px;">Karyawan</th>
                                                <th class="border-0" style="min-width: 150px;">Divisi</th>
                                                <th class="border-0" style="min-width: 120px;">Kategori</th>
                                                <th class="border-0" style="min-width: 200px;">Keperluan</th>
                                                <th class="border-0" style="min-width: 250px;">Detail Kendala</th>
                                                <th class="border-0" style="min-width: 150px;">PIC</th>
                                                <th class="border-0 text-center pe-4" style="min-width: 120px;">Status
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($ticket as $item)
                                                <tr class="border-bottom">
                                                    <td class="ps-4">
                                                        <div class="small">
                                                            {{ \Carbon\Carbon::parse($item->timestamp)->format('d M Y, H:i') }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="text-truncate" style="max-width: 150px;"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ $item->nama_karyawan }}">
                                                                {{ $item->nama_karyawan }}
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary-subtle text-secondary">
                                                            {{ $item->divisi }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="text-truncate" style="max-width: 120px;"
                                                            data-bs-toggle="tooltip" title="{{ $item->kategori }}">
                                                            <i class="bx bx-category text-muted me-1"></i>
                                                            {{ $item->kategori }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="text-truncate" style="max-width: 200px;"
                                                            data-bs-toggle="tooltip" title="{{ $item->keperluan }}">
                                                            {{ $item->keperluan }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="text-truncate" style="max-width: 250px;"
                                                            data-bs-toggle="tooltip" title="{{ $item->detail_kendala }}">
                                                            {{ $item->detail_kendala }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="text-truncate" style="max-width: 100px;"
                                                                data-bs-toggle="tooltip" title="{{ $item->pic }}">
                                                                {{ $item->pic ?? '-' }}
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center pe-4">
                                                        @php
                                                            $statusConfig = [
                                                                'Menunggu' => [
                                                                    'color' => 'warning',
                                                                    'icon' => 'bx-time-five',
                                                                ],
                                                                'Di Proses' => [
                                                                    'color' => 'primary',
                                                                    'icon' => 'bx-loader-circle',
                                                                ],
                                                                'Selesai' => [
                                                                    'color' => 'success',
                                                                    'icon' => 'bx-check-circle',
                                                                ],
                                                                'Terkendala' => [
                                                                    'color' => 'danger',
                                                                    'icon' => 'bx-error-circle',
                                                                ],
                                                            ];
                                                            $config = $statusConfig[$item->status] ?? [
                                                                'color' => 'secondary',
                                                                'icon' => 'bx-info-circle',
                                                            ];
                                                        @endphp
                                                        <span
                                                            class="badge bg-{{ $config['color'] }}-subtle text-{{ $config['color'] }} px-3 py-2">
                                                            <i class="bx {{ $config['icon'] }} me-1"></i>
                                                            {{ $item->status }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center py-5">
                                                        <div class="d-flex flex-column align-items-center">
                                                            <i class="bx bx-message-square-x text-muted"
                                                                style="font-size: 3rem;"></i>
                                                            <p class="text-muted mt-3 mb-0">Tidak ada ticket untuk
                                                                ditampilkan
                                                            </p>
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
                </div>

            </div>
        </div>

        <!-- Modals untuk setiap Divisi -->
        @foreach ($divisiStats as $index => $divisi)
            <div class="modal fade" id="modalDivisi{{ $index }}" tabindex="-1"
                aria-labelledby="modalLabel{{ $index }}" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                        <div class="modal-header bg-{{ $divisi['color'] }} bg-opacity-10 border-bottom-0">
                            <h5 class="modal-title fw-bold" id="modalLabel{{ $index }}">
                                <i class="{{ $divisi['icon'] }} text-{{ $divisi['color'] }} me-2"
                                    style="font-size: 1.5rem;"></i>
                                Data Karyawan - {{ $divisi['nama'] }}
                                <span class="badge bg-{{ $divisi['color'] }} ms-3 mb-2">{{ $divisi['total'] }}
                                    orang</span>
                            </h5>
                            <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0">
                            @if ($divisi['data']->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped align-middle mb-0">
                                        <thead class="bg-light sticky-top">
                                            <tr>
                                                <th class="ps-4" width="60">#</th>
                                                <th class="ps-4">Nama Lengkap</th>
                                                <th>NIP</th>
                                                <th>Jabatan</th>
                                                <th>Email</th>
                                                <th width="100">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($divisi['data'] as $key => $karyawan)
                                                <tr class="hover-bg">
                                                    <td class="ps-4 fw-medium">{{ $key + 1 }}</td>
                                                    <td class="ps-4">
                                                        <div class="d-flex align-items-center">
                                                            <div
                                                                class="avatar avatar-sm bg-opacity-15 rounded-circle me-3">
                                                                <span class="text-{{ $divisi['color'] }} fw-bold small">
                                                                    {{ strtoupper(substr($karyawan->nama_lengkap, 0, 1)) }}
                                                                </span>
                                                            </div>
                                                            <span class="fw-medium">{{ $karyawan->nama_lengkap }}</span>
                                                        </div>
                                                    </td>
                                                    <td><code class="small">{{ $karyawan->nip ?? '-' }}</code></td>
                                                    <td>{{ $karyawan->jabatan ?? '-' }}</td>
                                                    <td><small class="text-muted">{{ $karyawan->email ?? '-' }}</small>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success">Aktif</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-6 text-muted">
                                    <i class="bx bx-user-x" style="font-size: 4rem; opacity: 0.5;"></i>
                                    <p class="mt-3 fw-medium">Belum ada data karyawan di divisi ini</p>
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer border-top-0 bg-light" style="padding: 6px">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                <i class="bx bx-x me-1"></i> Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

    @auth
        <style>
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }

                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }

            @keyframes fadeOut {
                from {
                    opacity: 1;
                    transform: translateX(0);
                }

                to {
                    opacity: 0;
                    transform: translateX(20px);
                }
            }
        </style>
    @endauth

    <style>
        :root {
            --bs-primary: #5b73e8;
            --bs-primary-rgb: 91, 115, 232;
        }

            .hover-card {
                transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
                cursor: pointer;
                position: relative;
                overflow: hidden;
            }

            .hover-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
                transition: 0.5s;
            }

            .hover-card:hover {
                transform: translateY(-8px) scale(1.02);
                box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
                z-index: 1;
            }

            .hover-card:hover::before {
                left: 100%;
            }

            .hover-bg:hover {
                background-color: rgba(91, 115, 232, 0.05) !important;
            }

            .avatar {
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 600;
            }

            .avatar-sm {
                width: 38px;
                height: 38px;
                font-size: 0.875rem;
            }

            .avatar-md {
                width: 48px;
                height: 48px;
                font-size: 1.125rem;
            }

            .avatar-xl {
                width: 80px;
                height: 80px;
            }

            .scrollbar-custom::-webkit-scrollbar {
                width: 8px;
            }

            .scrollbar-custom::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 10px;
            }

            .scrollbar-custom::-webkit-scrollbar-thumb {
                background: #c1c1c1;
                border-radius: 10px;
            }

            .scrollbar-custom::-webkit-scrollbar-thumb:hover {
                background: #a8a8a8;
            }

            .card {
                transition: all 0.3s ease;
            }

            .badge {
                font-size: 0.75rem;
                padding: 0.35em 0.65em;
            }

            @media (max-width: 768px) {
                .modal-xl {
                    --bs-modal-width: 95vw;
                }
            }

        .chart-container {
            max-height: 280px;
            overflow: hidden;
        }

        .chart-wrapper {
            height: 400px;
            position: relative;
        }
        .custom-check {
            appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid #71DD37;
            border-radius: 5px;
            position: relative;
        }
        .custom-fail {
            appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid #FF3E1D;
            border-radius: 5px;
            position: relative;
        }
        .check-blue {
            width: 20px;
            height: 20px;
            border: 2px solid #5B73E8;
            border-radius: 5px;
            position: relative;
        }

        .custom-check:checked {
            background-color: #71DD37;
        }
        .custom-fail:checked {
            background-color: #FF3E1D;
        }

        .custom-check:checked::after {
            content: '✓';
            color: white;
            font-weight: bold;
            position: absolute;
            left: 2px;
            top: -2px;
        }
        .custom-fail:checked::after {
            content: '✖';
            color: white;
            font-weight: bold;
            position: absolute;
            left: 2px;
            top: -2px;
        }

        /* FullCalendar Custom Styling */
        .fc-custom {
            --fc-border-color: #e9ecef;
            --fc-button-bg-color: #667eea;
            --fc-button-border-color: #667eea;
            --fc-button-hover-bg-color: #5568d3;
            --fc-button-hover-border-color: #5568d3;
            --fc-button-active-bg-color: #5568d3;
            --fc-button-active-border-color: #5568d3;
            --fc-today-bg-color: #f0f4ff;
        }

        .fc-custom .fc-button-primary {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 6px;
        }

        .fc-custom .fc-button-primary:not(:disabled).fc-button-active {
            background-color: #5568d3;
            border-color: #5568d3;
        }

        .fc-custom .fc-daygrid-day {
            border-color: #e9ecef;
        }

        .fc-custom .fc-daygrid-day:hover {
            background-color: #f8f9fa;
        }

        .fc-custom .fc-daygrid-day.fc-day-other {
            opacity: 0.4;
        }

        .fc-custom .fc-event {
            border-radius: 6px;
            padding: 4px 6px;
            font-size: 0.85rem;
            font-weight: 500;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .fc-custom .fc-event:hover {
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
            transform: translateY(-2px);
        }

        .fc-custom .fc-col-header-cell {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
            border-color: #e9ecef;
            padding: 12px 4px;
        }

        .fc-custom .fc-daygrid-day-number {
            padding: 8px 4px;
            font-size: 0.95rem;
        }

        .fc-custom .fc-daygrid-day-frame {
            min-height: 80px;
        }

        .fc-custom .fc-button-group {
            gap: 4px;
        }

        .fc-custom .fc-button {
            padding: 6px 12px;
            font-size: 0.9rem;
            border-radius: 6px;
            text-transform: capitalize;
        }

        .fc-custom .fc-button-group > button {
            border-radius: 6px;
        }

        .fc-custom .fc-button-group > .fc-button:first-child {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .fc-custom .fc-button-group > .fc-button:last-child {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        .holiday-detail-card {
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .holiday-list-item .badge {
            min-height: 35px;
            min-width: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .actions-dropdown {
            z-index: 1050 !important;
        }
    </style>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {  
            const ctx = document.getElementById('kehadiranChart')?.getContext('2d');
            if (!ctx) return;

            const labels = @json($kehadiranChart['labels']);
            const data = @json($kehadiranChart['data']);
            const totalKaryawan = {{ $total_karyawan }};

            // Gradient fill
            const gradient = ctx.createLinearGradient(0, 0, 0, 320);
            gradient.addColorStop(0, 'rgba(91, 115, 232, 0.2)');
            gradient.addColorStop(1, 'rgba(91, 115, 232, 0.05)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah Hadir',
                        data: data,
                        borderColor: '#5b73e8',
                        backgroundColor: gradient,
                        borderWidth: 3, 
                        fill: true,
                        tension: 0.45,
                        pointRadius: 6,
                        pointHoverRadius: 9,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#5b73e8',
                        pointBorderWidth: 3,
                        pointHoverBackgroundColor: '#5b73e8',
                        pointHoverBorderColor: '#fff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                color: '#333',
                                usePointStyle: true,
                                padding: 20
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            cornerRadius: 8,
                            displayColors: false,
                            padding: 12,
                            callbacks: {
                                afterLabel: function(context) {
                                    const hadir = context.parsed.y;
                                    const tidakHadir = totalKaryawan - hadir;
                                    return [
                                        '',
                                        `Tidak Hadir: ${tidakHadir}`,
                                        `Persentase: ${Math.round((hadir / totalKaryawan) * 100)}%`
                                    ];
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: totalKaryawan + 5,
                            ticks: {
                                stepSize: 1,
                                font: {
                                    size: 12
                                },
                                color: '#666'
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            },
                            title: {
                                display: true,
                                text: 'Jumlah Karyawan',
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                color: '#333'
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    size: 12
                                },
                                color: '#666',
                                maxRotation: 0
                            },
                            grid: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: 'Tanggal',
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                color: '#333'
                            }
                        }
                    },
                    animation: {
                        duration: 1500,
                        easing: 'easeOutQuart'
                    }
                }
            });

            let cuti = document.getElementById('dataCuti')?.getContext('2d');
            if (!cuti) return;

            let cutiChart = null;
            let currentFilterCuti = {
                filter: null,
                value: null,
                tahun: null,
                rentangWaktu: null
            };


            function loadDataCuti(filterType, value) {
                let tahun = $('#filterCutiTahun').val();

                if (tahun === 'default' || !tahun) {
                    tahun = new Date().getFullYear();
                }

                $.ajax({
                    url: '/office/data-cuti',
                    method: 'GET',
                    data: {
                        filter: filterType,
                        value: value,
                        tahun: tahun,
                    },
                    success: function (res) {

                        if (cutiChart) {
                            cutiChart.data.labels = res.labelCuti;
                            cutiChart.data.datasets[0].data = res.totalCuti;
                            cutiChart.update();
                        } else {
                            cutiChart = new Chart(cuti, {
                                type: 'pie',
                                data: {
                                    labels: res.labelCuti,
                                    datasets: [{
                                        label: 'Jumlah Cuti',
                                        data: res.totalCuti,
                                        backgroundColor: [
                                            'rgba(75, 192, 192, 0.8)',
                                            'rgba(255, 99, 132, 0.8)',
                                            'rgba(54, 162, 235, 0.8)',
                                            'rgba(255, 206, 86, 0.8)',
                                            'rgba(153, 102, 255, 0.8)',
                                            'rgba(255, 159, 64, 0.8)',
                                            'rgba(0, 128, 128, 0.8)',
                                        ],
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            position: 'right'
                                        }
                                    }
                                }
                            });
                        }

                        $('#rentangWaktu').text(res.rentangWaktu);

                        if (res.labelCuti.length === 0) {
                            $('#exportCuti').prop('disabled', true);
                            $('#dataCuti').addClass('d-none');
                            $('#cutiEmpty').removeClass('d-none');
                            return;
                        }

                        $('#exportCuti').prop('disabled', false);
                        $('#cutiEmpty').addClass('d-none');
                        $('#dataCuti').removeClass('d-none');

                        currentFilterCuti.filter = filterType;
                        currentFilterCuti.value = value;
                        currentFilterCuti.tahun = tahun;
                        currentFilterCuti.rentangWaktu = res.rentangWaktu;
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                    }
                });
            }

            // reset filter per tahun
            $('#filterCutiTahun').change(function () {
                $('#filterCutiBulan, #filterCutiTriwulan').val('default');
                loadDataCuti('tahun', $(this).val());
            });

            // reset Filter per bulan
            $('#filterCutiBulan').change(function () {
                $('#filterCutiTriwulan').val('default');
                loadDataCuti('bulan', $(this).val());
            });

            // reset filter per triwulan
            $('#filterCutiTriwulan').change(function () {
                $('#filterCutiBulan').val('default');
                loadDataCuti('triwulan', $(this).val());
            });

            // Export cuti
            $('#exportCuti').click(function () {

                $.ajax({
                    url: '/office/data-cuti',
                    method: 'GET',
                    data: {
                        filter: currentFilterCuti.filter,
                        value: currentFilterCuti.value,
                        tahun: currentFilterCuti.tahun,
                        export: 1
                    },
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function (blob) {

                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');

                        a.href = url;
                        a.download = `Laporan_Cuti_${currentFilterCuti.rentangWaktu}.pdf`;
                        document.body.appendChild(a);
                        a.click();

                        a.remove();
                        window.URL.revokeObjectURL(url);
                    },
                    error: function (err) {
                        alert('Gagal export PDF' + err);
                    }
                });
            });

            
            // Filter total mengajar
            let currentFilterMengajar = {
                filter: null,
                value: null,
                tahun: null,
                rentangWaktu: null
            };

            // Load tabel total mengajar
            function loadDataMengajar(filterType, value) {
                let tahun = $('#filterMengajarPerTahun').val();

                if (tahun === 'default' || !tahun) {
                    tahun = new Date().getFullYear();
                }
                
                $.ajax({
                    url: '/office/data-mengajar',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        filter: filterType,
                        value: value,
                        tahun: tahun
                    },
                    success: function (res) {
                        let tabelMengajar = $('#tabelTotalMengajar tbody');
                        tabelMengajar.empty();
                        let data = res.dataMengajar;

                        $.each(data, function (index, item) {
                            tabelMengajar.append(`
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${item.namaKaryawan}</td>
                                    <td>${item.kodeKaryawan}</td>
                                    <td>${item.totalMengajar}</td>
                                </tr>
                            `)
                        });

                        $('#rentangWaktuMengajar').text(res.rentangWaktu);

                        if (data.length === 0) {
                            $('#exportTotalMengajar').prop('disabled', true);
                            tabelMengajar.append(`
                                <tr>
                                   <td colspan="7" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bx bx-archive text-muted"
                                                style="font-size: 3rem;"></i>
                                            <p class="text-muted mt-3 mb-0">Tidak ada data total mengajar instruktur</p>
                                        </div>
                                    </td>
                                </tr>
                            `);
                            return;
                        }

                        $('#exportTotalMengajar').prop('disabled', false);

                        currentFilterMengajar.filter = filterType;
                        currentFilterMengajar.value = value;
                        currentFilterMengajar.tahun = tahun;
                        currentFilterMengajar.rentangWaktu = res.rentangWaktu;
                    },
                    error: function (err) {
                        alert(err);
                    }
                });
            };

            if ($('#sectionOutstanding').length) {

                let currentPage = 1;
                let lastPage = 1;
                let currentSearch = '';

                function loadOutstanding(page = 1, search = '') {
                    $.ajax({
                        url: "{{ route('office.table.outstanding') }}",
                        type: "GET",
                        data: {
                            page: page,
                            search: search
                        },
                        success: function(res) {
                            let rows = '';

                            currentPage = res.current_page;
                            lastPage = res.last_page;

                            if (res.data.length === 0) {
                                rows = `<tr><td colspan="12" class="text-center">Tidak ada data</td></tr>`;
                            } else {
                                res.data.forEach((item, index) => {
                                    const safeFormat = (val) => {
                                        if (val === '-' || val === null || val === undefined || val === '' || Number(val) === 0) {
                                            return '-';
                                        }
                                        return 'Rp ' + formatRupiah(val);
                                    };

                                    rows += `
                                        <tr>
                                            <td>${(currentPage - 1) * 10 + index + 1}</td>
                                            <td>${item.perusahaan || '-'}</td>
                                            <td>${item.kelas || '-'}</td>
                                            <td>${item.sales || '-'}</td>
                                            <td>${formatDate(item.tanggal)}</td>
                                            
                                            <!-- Gunakan helper safeFormat -->
                                            <td>${safeFormat(item.tagihan)}</td>
                                            
                                            <td>${formatDate(item.tenggat_waktu)}</td>
                                            <td>${formatDate(item.tanggal_bayar)}</td>
                                            <td>${safeFormat(item.nominal_pembayaran)}</td>
                                            <td>${safeFormat(item.admin_transfer)}</td>
                                            <td>${safeFormat(item.nominal_pph23)}</td>
                                            <td>${safeFormat(item.nominal_ppn)}</td>
                                            <td>${safeFormat(item.uang_diterima)}</td>
                                            
                                            <td>${renderStatus(item.status)}</td>
                                            <td>${item.info || '-'}</td>
                                        </tr>
                                    `;
                                });
                            }

                            $('#outstandingTableBody').html(rows);

                            // update info
                            $('#pageInfo').text(`Page ${currentPage} / ${lastPage}`);
                            $('#paginationInfo').text(`Total data: ${res.total}`);

                            // disable button
                            $('#prevPage').prop('disabled', currentPage === 1);
                            $('#nextPage').prop('disabled', currentPage === lastPage);
                        }
                    });
                }

                $('#nextPage').click(function() {
                    if (currentPage < lastPage) {
                        loadOutstanding(currentPage + 1, currentSearch);
                    }
                });

                $('#prevPage').click(function() {
                    if (currentPage > 1) {
                        loadOutstanding(currentPage - 1, currentSearch);
                    }
                });


                let debounceTimer;
                $('#searchOutstanding').on('keyup', function() {
                    clearTimeout(debounceTimer);
                    let value = $(this).val();
                    
                    currentSearch = value; 

                    debounceTimer = setTimeout(() => {
                        loadOutstanding(1, currentSearch); 
                    }, 400);
                });

                function formatRupiah(angka) {
                    if (!angka || angka === '-' || isNaN(Number(angka))) {
                        return '-';
                    }
                    
                    const num = Number(angka);
                    const clean = Math.round(num);
                    return clean.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                }

                function formatDate(date) {
                    if (!date || date === '-') return '-';

                    let d = new Date(date);
                    return d.toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    });
                }

                function renderPotongan(jenis, jumlah) {
                    if (!jenis || jenis === '-' || !jumlah || jumlah === '-') {
                        return '-';
                    }

                    let jenisArr = jenis.split(',');
                    let jumlahArr = jumlah.split(',');

                    let result = '';

                    jenisArr.forEach((item, index) => {
                        let nominal = jumlahArr[index] ? formatRupiah(jumlahArr[index].trim()) : '0';

                        result += `
                            <div style="font-size: 12px;">
                                ${item.trim()} (Rp ${nominal}),
                            </div>
                        `;
                    });

                    return result;
                }

                function renderStatus(status) {
                    let color = 'secondary';

                    if (status === 'Belum Bayar') color = 'warning';
                    else if (status === 'Tepat Waktu') color = 'success';
                    else if (status === 'Terlambat') color = 'danger';

                    return `<span class="badge bg-${color}">${status}</span>`;
                }

                let chartInstanceOutstanding;

                function loadChartOutstanding(year) {

                    fetch(`/office/grafik/outstanding?year=${year}`)
                        .then(res => res.json())
                        .then(data => {

                            const ctx = document.getElementById('grafikOutstanding');

                            if (chartInstanceOutstanding) {
                                chartInstanceOutstanding.destroy();
                            }

                            const total = data.total || 0;
                            const tepatWaktu = data.data[1] || 0;

                            const progress = total === 0 ? 0 : Math.round((tepatWaktu / total) * 100);

                            document.getElementById('kpiProgress').innerText = `${progress}%`;
                            document.getElementById('totalClient').innerText = total;

                            let analysis = "";

                            if (progress >= 90) {
                                analysis = `Kinerja sangat baik! Anda sudah mencapai KPI sebesar ${progress}% dari target 100%.`;
                            }
                            else if (progress >= 70) {
                                analysis = `Kinerja cukup baik. Anda mencapai KPI ${progress}% dari target 100%, masih ada ruang peningkatan.`;
                            }
                            else if (progress >= 50) {
                                analysis = `Kinerja sedang. Baru mencapai KPI ${progress}% dari target 100%, perlu perbaikan.`;
                            }
                            else {
                                analysis = `Kinerja rendah. Baru ${progress}% dari target 100%, perlu tindakan segera.`;
                            }

                            document.getElementById('kpiAnalysis').innerText = analysis;

                            const hasData = data.data.some(val => val > 0);

                            if (!hasData || total === 0) {
                                document.getElementById('outstandingEmpty').classList.remove('d-none');

                                document.getElementById('lblBelumBayar').innerText = "0% (0)";
                                document.getElementById('lblTepatWaktu').innerText = "0% (0)";
                                document.getElementById('lblTerlambat').innerText = "0% (0)";
                                return;
                            } else {
                                document.getElementById('outstandingEmpty').classList.add('d-none');
                            }

                            const belumBayarPercent = Math.round((data.data[0] / total) * 100);
                            const tepatWaktuPercent = Math.round((data.data[1] / total) * 100);
                            const terlambatPercent  = Math.round((data.data[2] / total) * 100);

                            document.getElementById('lblBelumBayar').innerText =
                                `${belumBayarPercent}% (${data.data[0]})`;

                            document.getElementById('lblTepatWaktu').innerText =
                                `${tepatWaktuPercent}% (${data.data[1]})`;

                            document.getElementById('lblTerlambat').innerText =
                                `${terlambatPercent}% (${data.data[2]})`;

                            // CHART
                            chartInstanceOutstanding = new Chart(ctx, {
                                type: 'pie',
                                data: {
                                    labels: data.labels,
                                    datasets: [{
                                        data: data.data,
                                        backgroundColor: [
                                            '#dc3545',
                                            '#198754',
                                            '#fd7e14'
                                        ]
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            position: 'bottom'
                                        },
                                        tooltip: {
                                            callbacks: {

                                                label: function(context) {

                                                    const value = context.raw;
                                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                    const percent = ((value / total) * 100).toFixed(1);

                                                    return `${context.label}: ${percent}% (${value})`;
                                                }

                                            }
                                        }
                                    }
                                }
                            });

                        });
                }

                loadChartOutstanding(new Date().getFullYear());

                document.getElementById('filterTahun').addEventListener('change', function () {
                    loadChartOutstanding(this.value);
                });

                let chartInstanceKetepatan;

                function loadChartKetepatan(year) {

                    fetch(`/office/grafik/ketepatan-waktu?year=${year}`)
                        .then(res => res.json())
                        .then(data => {

                            const ctx = document.getElementById('grafikKetepatanWaktu');

                            if (chartInstanceKetepatan) {
                                chartInstanceKetepatan.destroy();
                            }

                            const total = data.data.reduce((acc, val) => acc + val, 0); 
                            const sesuai = data.data[0] || 0; 
                            const persen = data.persen || 0; 

                            // Update elemen KPI
                            document.getElementById('kpiProgressKetepatan').innerText = `${persen}%`;
                            document.getElementById('totalDataKetepatan').innerText = total;
                            document.getElementById('lblSesuaiKetepatan').innerText = `${sesuai}`;
                            document.getElementById('lblTidakSesuaiKetepatan').innerText = `${data.data[1] || 0}`;


                            let analysis = "";
                            if (persen >= 90) {
                                analysis = `Kinerja sangat baik! Anda sudah mencapai KPI sebesar ${persen}% dari target 100%.`;
                            }
                            else if (persen >= 70) {
                                analysis = `Kinerja cukup baik. Anda mencapai KPI ${persen}% dari target 100%, masih ada ruang peningkatan.`;
                            }
                            else if (persen >= 50) {
                                analysis = `Kinerja sedang. Baru mencapai KPI ${persen}% dari target 100%, perlu perbaikan.`;
                            }
                            else {
                                analysis = `Kinerja rendah. Baru ${persen}% dari target 100%, perlu tindakan segera.`;
                            }
                            document.getElementById('kpiAnalysisKetepatan').innerText = analysis;

                            const hasData = total > 0;

                            if (!hasData) {
                                document.getElementById('ketepatanWaktuEmpty').classList.remove('d-none');
                                return;
                            } else {
                                document.getElementById('ketepatanWaktuEmpty').classList.add('d-none');
                            }

                            chartInstanceKetepatan = new Chart(ctx, {
                                type: 'pie',
                                data: {
                                    labels: data.labels,
                                    datasets: [{
                                        data: data.data,
                                        backgroundColor: [
                                            '#198754', // Hijau untuk Sesuai
                                            '#dc3545'  // Merah untuk Tidak Sesuai
                                        ]
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            position: 'bottom'
                                        },
                                        tooltip: {
                                            callbacks: {
                                                label: function(context) {
                                                    const value = context.raw;
                                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                    const percent = ((value / total) * 100).toFixed(1);

                                                    return `${context.label}: ${percent}% (${value})`;
                                                }
                                            }
                                        }
                                    }
                                }
                            });

                        })
                        .catch(error => {
                            console.error('Error loading chart:', error);
                        });
                }


                loadChartKetepatan(new Date().getFullYear());

                document.getElementById('filterTahunKetepatan').addEventListener('change', function () {
                    loadChartKetepatan(this.value);
                });
            
            }

            // reset filter per tahun
            $('#filterMengajarPerTahun').change(function () {
                $('#filterMengajarPerBulan, #filterMengajarPerTriwulan').val('default');
                loadDataMengajar('tahun', $(this).val());
            });

            // reset Filter per bulan
            $('#filterMengajarPerBulan').change(function () {
                $('#filterMengajarPerTriwulan').val('default');
                loadDataMengajar('bulan', $(this).val());
            });

            // reset filter per triwulan
            $('#filterMengajarPerTriwulan').change(function () {
                $('#filterMengajarPerBulan').val('default');
                loadDataMengajar('triwulan', $(this).val());
            });

            // Export Total Mengajar
            $('#exportTotalMengajar').click(function () {

                $.ajax({
                    url: '/office/data-mengajar',
                    method: 'GET',
                    data: {
                        filter: currentFilterMengajar.filter,
                        value: currentFilterMengajar.value,
                        tahun: currentFilterMengajar.tahun,
                        exportTotalMengajar: 1
                    },
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function (blob) {

                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');

                        a.href = url;
                        a.download = `Laporan_Total_Mengajar_${currentFilterMengajar.rentangWaktu}.pdf`;
                        document.body.appendChild(a);
                        a.click();

                        a.remove();
                        window.URL.revokeObjectURL(url);
                    },
                    error: function (xhr, status, error) {
                        console.log(xhr);
                    }
                });
            });

            // Load semua AJAX
            $(document).ready(function () {
                loadDataCuti('bulan', new Date().getMonth() + 1);
                loadDataMengajar('bulan', new Date().getMonth() + 1);
                loadOutstanding();
            });

            // Script Chart Feedback
            let feedbackChart;

            $(document).ready(function () {
                initChart();
                loadFeedback();

                $('#filterFeedbackTahun').change(function () {
                    resetSelect('#filterFeedbackBulan', '#filterFeedbackTriwulan');
                    loadFeedback('tahun', $(this).val());
                });

                $('#filterFeedbackBulan').change(function () {
                    resetSelect('#filterFeedbackTriwulan');
                    loadFeedback('bulan', $(this).val());
                });

                $('#filterFeedbackTriwulan').change(function () {
                    resetSelect('#filterFeedbackBulan');
                    loadFeedback('triwulan', $(this).val());
                });
            });

            function initChart() {
                const ctx = document.getElementById('feedbackChart').getContext('2d');

                feedbackChart = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: [],
                        datasets: [{
                            data: [],
                            backgroundColor: generateColors(30)
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right'
                            }
                        }
                    }
                });
            }

            function loadFeedback(filter = null, value = null) {
                $.ajax({
                    url: "{{ route('office.feedback.get') }}",
                    type: "GET",
                    data: {
                        filter: filter,
                        value: value,
                        tahun: $('#filterFeedbackTahun').val()
                    },
                    success: function (res) {

                        // === DATA KOSONG ===
                        if (!res || res.length === 0) {
                            feedbackChart.data.labels = [];
                            feedbackChart.data.datasets[0].data = [];
                            feedbackChart.update();

                            $('#nilaiInstrukturEmpty').removeClass('d-none');
                            $('#exportFeedbackInstruktur').prop('disabled', true);
                            return;
                        }

                        $('#nilaiInstrukturEmpty').addClass('d-none');
                        $('#exportFeedbackInstruktur').prop('disabled', false);

                        const labels = [];
                        const data = [];

                        res.forEach(item => {
                            labels.push(item.nama_instruktur);
                            data.push(item.nilai_instruktur);
                        });

                        feedbackChart.data.labels = labels;
                        feedbackChart.data.datasets[0].data = data;
                        feedbackChart.update();
                    }
                });
            }

            function resetSelect(...selectors) {
                selectors.forEach(sel => {
                    $(sel).val('default');
                });
            }

            function generateColors(count) {
                const colors = [];
                for (let i = 0; i < count; i++) {
                    colors.push(`hsl(${i * 360 / count}, 70%, 60%)`);
                }
                return colors;
            }

            // === EXPORT PDF ===
            $('#exportFeedbackInstruktur').on('click', function () {
                let tahun = $('#filterFeedbackTahun').val();
                let bulan = $('#filterFeedbackBulan').val();
                let triwulan = $('#filterFeedbackTriwulan').val();

                let filter = '';
                let value = '';

                if (bulan && bulan !== 'default') {
                    filter = 'bulan';
                    value = bulan;
                } else if (triwulan && triwulan !== 'default') {
                    filter = 'triwulan';
                    value = triwulan;
                } else if (tahun && tahun !== 'default') {
                    filter = 'tahun';
                    value = tahun;
                }

                let url = `{{ route('office.feedbackinstrukturpdf') }}?filter=${filter}&value=${value}&tahun=${tahun}`;
                window.open(url, '_blank');
            });
            // End Script Chart Feedback

            function formatRupiah(angka) {
                if (!angka) return '';

                let number = angka.toString().replace(/\D/g, '');
                if (number === '') return '';

                return number.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            function unformatRupiah(angka) {
                return (angka || '').toString().replace(/\D/g, '');
            }

            // Format saat ngetik (fix cursor & bug 5 digit)
            $(document).on('input', '.format-rupiah', function(e) {
                let cursorPos = this.selectionStart;
                let value = this.value;

                let raw = unformatRupiah(value);
                let formatted = formatRupiah(raw);

                this.value = formatted;

                // Perbaiki posisi cursor
                let diff = formatted.length - value.length;
                this.setSelectionRange(cursorPos + diff, cursorPos + diff);
            });

            // Hitung total
            function hitungTotal(jumlahEl, hargaEl, totalHidden) {
                let jml = parseInt(unformatRupiah(jumlahEl.val())) || 0;
                let hrg = parseInt(unformatRupiah(hargaEl.val())) || 0;
                totalHidden.val(jml * hrg);
            }

            // Bersihkan sebelum submit
            function bersihkanFormat(form) {
                form.find('.format-rupiah').each(function() {
                    this.value = unformatRupiah(this.value);
                });
            }

            $('#modalTambahTagihan form').on('submit', function() {
                bersihkanFormat($(this));
            });

            // Reset modal
            $(document).on('hidden.bs.modal', '.modal', function () {
                const form = $(this).find('form');

                if (form.length) {
                    form[0].reset();
                    form.removeClass('was-validated');

                    form.find('.is-invalid').removeClass('is-invalid');
                    form.find('.is-valid').removeClass('is-valid');
                    form.find('.invalid-feedback').remove();
                }
            });

            // form edit tagihan
            $(document).on('click', '#edit-tagihan', function () {
                let id = $(this).data('id');

                // jika yang diklik checkbox → buka modal manual
                if ($(this).is(':checkbox')) {
                    this.checked = false;
                    $('#modalEditTagihan').modal('show');
                }

                $.ajax({
                    url: '/office/data-tagihan/' + id,
                    type: 'GET',
                    success: function (res) {
                        let nominal = formatRupiah(parseInt(res.data.nominal))
                        // set action form
                        $('#formEditTagihan').attr('action', '/office/update-tagihan/' + id);

                        // set value input
                        $('#modalEditTagihan select[name="status"]').val(res.data.status);
                        $('#modalEditTagihan select[name="tracking"]').val(res.data.tracking);
                        $('#modalEditTagihan input[name="tanggal_selesai"]').val(res.data.tanggal_selesai);
                        $('#modalEditTagihan textarea[name="keterangan"]').val(res.data.keterangan);

                        if (res.data.status === 'selesai' || res.data.status === 'telat') {
                            $('#modalEditTagihan select[name="status"]').attr('disabled', 'disabled');
                        } else if (res.data.status === 'pending' || res.data.status === 'proses') {
                            $('#modalEditTagihan select[name="status"]').attr('disabled', false);
                        }

                        // format rupiah jika ada function
                        $('.format-rupiah').trigger('keyup');
                    }
                });
            });


            $(document).on('click', '.edit-administrasi', function () {
                let id = $(this).data('id');

                // reset checkbox supaya tidak tercentang
                if ($(this).is(':checkbox')) {
                    this.checked = false;
                }

                $('#modalEditAdministrasi').modal('show');

                $.ajax({
                    url: '/office/data-administrasi/' + id,
                    type: 'GET',

                    success: function(res) {

                        // set action form
                        $('#formEditAdministrasi').attr('action', '/office/administrasi-karyawan/update/' + id);

                        // isi input
                        $('#modalEditAdministrasi input[name="nama_administrasi"]').val(res.nama_administrasi);
                        $('#modalEditAdministrasi input[name="dateline"]').val(res.dateline).attr("disabled", "disabled");
                        $('#modalEditAdministrasi select[name="status"]').val(res.status);
                        $('#modalEditAdministrasi input[name="tanggal_selesai"]').val(res.tanggal_selesai);
                        $('#modalEditAdministrasi textarea[name="keterangan"]').val(res.keterangan);

                        if (res.status === 'selesai' || res.status === 'terlambat' ) {
                            $('#modalEditAdministrasi select[name="status"]').attr("disabled", "disabled");
                        }
                        
                        if(res.bukti_transfer){
                            $('#pathBuktiTransfer').html(
                                `<a href="/storage/${res.bukti_transfer}" target="_blank">
                                    Lihat Bukti Transfer
                                </a>`
                            );
                        }else{
                            $('#pathBuktiTransfer').html(
                                `<span class="text-muted">Tidak ada bukti transfer</span>`
                            );
                        }

                    }
                });

            });


            // Calendar Hari Libur
            $.ajax({
                url: '/office/data-hari-libur/' + new Date().getFullYear(),
                type: 'GET',
                dataType: 'json',
                success: function(holidays) {
                    let currentDisplayMonth = new Date().getMonth();
                    let currentDisplayYear = new Date().getFullYear();

                    const typeClassMap = {
                        nasional: {
                            bg: '#e74c3c',
                            br: '#c0392b',
                            badge: 'bg-danger-subtle text-danger',
                            border: 'border-danger'
                        },
                        perusahaan: {
                            bg: '#3498db',
                            br: '#2c80b4',
                            badge: 'bg-primary-subtle text-primary',
                            border: 'border-primary'
                        }
                    };
                    // Mapping data ke format FullCalendar
                    const events = holidays.map(h => {
                        const color = typeClassMap[h.tipe] || {
                            bg: '#95a5a6',
                            border: '#7f8c8d',
                        };
                        
                        return {
                            title: h.nama,
                            start: h.tanggal,
                            display: 'block',
                            backgroundColor: color.bg,
                            borderColor: color.br,
                            textColor: '#fff',
                            extendedProps: {
                                description: h.nama,
                                date: h.tanggal,
                                fullDate: new Date(h.tanggal),
                                type: h.tipe
                            }
                    }});

                    let calendar = new FullCalendar.Calendar($('#calendar')[0], {
                        initialView: 'dayGridMonth',
                        locale: 'id',
                        height: 'auto',
                        contentHeight: 'auto',
                        headerToolbar: {
                            left: 'prev,next',
                            center: 'title',
                            right: ''
                        },
                        buttonText: {
                            prev: '<< Prev',
                            next: 'Next >>',
                        },
                        events: events,
                        eventClick: function(info) {
                            let data = info.event.extendedProps;
                            const dateObj = new Date(data.date);
                            const dayName = dateObj.toLocaleDateString('id-ID', { weekday: 'long' });
                            const formattedDate = dateObj.toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });

                            const typeClass = typeClassMap[data.type] || {
                                badge: 'bg-secondary-subtle text-secondary',
                                border: 'border-secondary'
                            };

                            const detailHtml = `
                                <div class="holiday-detail-card">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="badge ${typeClass.badge} px-3 py-2 d-flex align-items-center justify-content-center" style="min-width: 50px; font-size: 1.2rem; height: 50px;">
                                            ${dateObj.getDate()}
                                        </div>
                                        <div class="ms-3">
                                            <small class="text-muted d-block text-capitalize">Hari Libur ${data.type}</small>
                                            <small class="text-muted fw-medium">${dayName}</small>
                                        </div>
                                    </div>
                                    <div class="bg-light rounded-3 p-3 border-start border-4 ${typeClass.border}">
                                        <h6 class="mb-2 fw-bold text-dark">${data.description}</h6>
                                        <small class="text-muted d-block">${formattedDate}</small>
                                    </div>
                                </div>
                            `;
                            $('#holiday-detail').html(detailHtml);
                        },
                        datesSet: function(info) {
                            const currentDate = info.view.currentStart;

                            currentDisplayMonth = currentDate.getMonth();
                            currentDisplayYear = currentDate.getFullYear();

                            updateHolidayList();
                        }
                    });

                    function updateHolidayList() {
                        const monthHolidays = holidays.filter(h => {
                            const date = new Date(h.tanggal);
                            return date.getMonth() === currentDisplayMonth && date.getFullYear() === currentDisplayYear;
                        }).sort((a, b) => new Date(a.date) - new Date(b.date));

                        const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                        const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

                        if (monthHolidays.length === 0) {
                            const emptyHtml = `
                                <div class="text-center py-4">
                                    <i class="bx bx-smile text-success" style="font-size: 2.5rem; opacity: 0.5;"></i>
                                    <p class="text-muted small mt-2 mb-0">Tidak ada libur di bulan ini</p>
                                </div>
                            `;
                            $('#holiday-list').html(emptyHtml);
                            return;
                        }

                        let html = `<div class="mb-2"><span class="badge bg-success-subtle text-success px-3 py-2">${monthHolidays.length} Hari Libur - ${monthNames[currentDisplayMonth]}</span></div>`;
                        
                        monthHolidays.forEach(holiday => {
                            const date = new Date(holiday.tanggal);
                            const dayName = dayNames[date.getDay()];
                            const isWeekend = date.getDay() === 0 || date.getDay() === 6;
                            // const isPast = new Date(holiday.tanggal) < new Date();
                            const isDisabled = holiday.tipe === 'nasional' ;

                            const typeClass = typeClassMap[holiday.tipe] || {
                                badge: 'bg-secondary-subtle text-secondary',
                                border: 'border-secondary'
                            };

                            html += `
                                <div class="holiday-list-item list-group-item bg-transparent d-flex align-items-center p-2 rounded-2 border-start border-4 ${typeClass.border}" style="transition: all 0.3s ease; cursor: pointer;">
                                    <div class="badge ${typeClass.badge} d-flex align-items-center justify-content-center" style="min-width: 35px; height: 35px; font-weight: bold;">
                                        ${date.getDate()}
                                    </div>
                                    <div class="ms-3 flex-grow-1">
                                        <small class="fw-medium text-dark d-block" style="max-width: 140px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${holiday.nama}">${holiday.nama}</small>
                                        <small class="text-muted">${dayName} ${isWeekend ? '<i class="bx bx-sm bx-info-circle"></i>' : ''}</small>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border-0 notifikasiLibur" data-id="${holiday.id}">
                                            <i class="bx bx-bell"></i>
                                        </button>
                                        <button class="btn btn-sm btn-light border-0 ${isDisabled ? 'disabled' : ''}" data-bs-toggle="dropdown" ${isDisabled ? 'disabled' : ''}>
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end actions-dropdown">
                                            <li>
                                                <a href="#" class="dropdown-item btn-edit-libur" data-id="${holiday.id}">
                                                    <i class="bx bx-edit-alt me-2"></i> Edit
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item text-danger btn-delete-libur" data-id="${holiday.id}">
                                                    <i class="bx bx-trash me-2"></i> Delete
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            `;
                        });

                        $('#holiday-list').html(html);
                    }
                        
                    $(document).on('click', '.btn-edit-libur', function(e) {
                        e.preventDefault();

                        const id = $(this).data('id');

                        $.ajax({
                            url: `/office/data-hari-libur/edit/${id}`,
                            type: 'GET',
                            success: function(res) {

                                // set action
                                $('#formEditHariLibur').attr('action', '/office/data-hari-libur/update/' + id);

                                // isi form
                                $('#id').val(res.id);
                                $('#nama').val(res.nama);
                                $('#tanggal').val(res.tanggal);

                                // tampilkan modal
                                $('#modalEditHariLibur').modal('show');
                            },
                            error: function() {
                                alert('Gagal mengambil data hari libur');
                            }
                        });
                    });
                    
                    $(document).on('click', '.btn-delete-libur', function(e) {
                        e.preventDefault();

                        const id = $(this).data('id');

                        $.ajax({
                            url: `/office/data-hari-libur/delete/${id}`,
                            type: 'POST',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(res) {
                                location.reload();
                            },
                            error: function(error) {
                                console.log(error)
                            },
                        });
                    });

                    $(document).on('click', '.notifikasiLibur', function(e) {
                        e.preventDefault();

                        const id = $(this).data('id');

                        $.ajax({
                            url: `{{ route('notif.store') }}`,
                            type: 'POST',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content'),
                                id: id
                            },
                            success: function(res) {
                                 location.reload();
                            },
                            error: function(error) {
                                console.log(error)
                            },
                        })
                    });

                    calendar.render();
                    updateHolidayList();
                },
                error: function(xhr, status, error) {
                    console.error('Error loading holidays:', error);
                    $('#holiday-list').html('<p class="text-danger small">Gagal memuat data libur</p>');
                }
            });

            // Filter eksport administrasi
            function resetAll() {
                $('#eksportTahunanAdminis, #eksportBulananAdminis, #eksportQuartalAdminis')
                    .prop('disabled', false);
                $('input[name="start_date"], input[name="end_date"]')
                    .prop('disabled', false);
            }

            function disablePeriode() {
                $('#eksportTahunanAdminis, #eksportBulananAdminis, #eksportQuartalAdminis')
                    .prop('disabled', true);
            }

            function disableTanggal() {
                $('input[name="start_date"], input[name="end_date"]')
                    .prop('disabled', true);
            }

            function handleFilter() {
                let tahun = $('#eksportTahunanAdminis').val();
                let bulan = $('#eksportBulananAdminis').val();
                let quartal = $('#eksportQuartalAdminis').val();
                let start = $('input[name="start_date"]').val();
                let end = $('input[name="end_date"]').val();

                resetAll();

                if (start || end) {
                    disablePeriode();
                    return;
                }

                if (bulan && !tahun) {
                    $('#eksportTahunanAdminis').val(new Date().getFullYear());
                    tahun = $('#eksportTahunanAdminis').val();
                }

                if (quartal && !tahun) {
                    $('#eksportTahunanAdminis').val(new Date().getFullYear());
                    tahun = $('#eksportTahunanAdminis').val();
                }

                if (tahun && bulan) {
                    $('#eksportQuartalAdminis').prop('disabled', true);
                    disableTanggal();
                }

                if (tahun && quartal) {
                    $('#eksportBulananAdminis').prop('disabled', true);
                    disableTanggal();
                }

            }

            $('#eksportTahunanAdminis, #eksportBulananAdminis, #eksportQuartalAdminis').on('change', function () {
                handleFilter();
            });

            $('input[name="start_date"], input[name="end_date"]').on('change', function () {
                handleFilter();
            });

            // end filter eksport administrasi
        });
    </script>
@endsection