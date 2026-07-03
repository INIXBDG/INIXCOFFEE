@extends($extends)

@section($section)
    <style>
        #pickupDriverCondition {
            display: none;
        }

        #pickupDriverCondition .hidePickup {
            display: none;
        }
    </style>
    <div class="container-fluid py-4">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Berhasil!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Error!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            @if ($section === 'content')
                <a class="btn btn-secondary" href="{{ url()->previous() }}">Kembali</a>
            @else
                <ul class="nav nav-tabs nav-custom" id="mainPageTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold px-4 py-2 @if($section === 'office_contents') active @endif" id="rab-page-tab"
                            data-bs-toggle="pill" data-bs-target="#rab-page" type="button" role="tab" @if($section === 'content') hidden @endif>
                            Pengajuan RAB
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold px-4 py-2 @if($section === 'content') active @endif" id="pembelian-page-tab"
                            data-bs-toggle="pill" data-bs-target="#pembelian-page" type="button" role="tab" @if($section === 'content') hidden @endif>
                            Rencana Pembelian
                        </button>
                    </li>
                </ul>
            @endif

            <div>
                <div class="d-flex gap-3">
                    <button class="btn btn-primary px-4 shadow-sm d-flex align-items-center gap-2 main-add-btn"
                        data-tab="pembelian-page" data-bs-toggle="modal" data-bs-target="#formModal" id="btnCreate">
                        <i class="bi bi-plus-circle"></i> Tambah Rencana Baru
                    </button>
                    <button class="btn btn-secondary px-4 shadow-sm d-flex align-items-center gap-2 main-add-btn"
                        data-tab="pembelian-page" data-bs-toggle="modal" data-bs-target="#rekapModal" id="btnAddRekap">
                        <i class="bi bi-plus-circle"></i> Tambah Rekap Terlaksana
                    </button>
                </div>
                @if($section === 'office_contents')
                    <button class="btn btn-primary px-4 shadow-sm d-flex align-items-center gap-2 main-add-btn d-none"
                        data-tab="rab-page" data-bs-toggle="modal" data-bs-target="#createModal">
                        <i class="bi bi-plus-circle"></i> Buat Pengajuan
                    </button>
                @endif
            </div>
        </div>

        @if($section === 'content') 
            <h4 class="mb-0 fw-semibold w-100 text-center">Rencana Pembelian</h4>
        @endif

        <div class="tab-content" id="mainPageTabContent">

            <div class="tab-pane fade @if($section === 'office_contents') show active @endif" id="rab-page" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header border-0 py-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="mb-0 fw-semibold">Daftar RAB</h5>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="text-dark fw-semibold small bg-light">
                                    <tr>
                                        <th class="ps-4 border-0" style="width: 5%;">No</th>
                                        <th class="border-0" style="width: 25%;">RAB</th>
                                        <th class="border-0" style="width: 15%;">Tipe</th>
                                        <th class="border-0" style="width: 15%;">Waktu</th>
                                        <th class="border-0" style="width: 10%;">Durasi</th>
                                        <th class="border-0" style="width: 15%;">PIC</th>
                                        <th class="border-0" style="width: 12%;">Status</th>
                                        <th class="border-0 text-center" style="width: 18%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($kegiatan as $item)
                                        <tr>
                                            <td class="ps-4 fw-medium text-muted">{{ $loop->iteration }}</td>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $item->nama_kegiatan }}</div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-dark text-uppercase">{{ $item->tipe }}</div>
                                            </td>
                                            <td>
                                                @if ($item->tipe === 'pembelian' || $item->tipe === 'rekrutmen')
                                                    <div class="text-dark fw-medium">-</div>
                                                @else
                                                    <div class="text-dark fw-medium">
                                                        {{ \Carbon\Carbon::parse($item->waktu_kegiatan)->format('d M Y') }}
                                                    </div>
                                                    <small class="text-muted">
                                                        {{ \Carbon\Carbon::parse($item->waktu_kegiatan)->format('H:i') }} WIB
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($item->tipe === 'pembelian' || $item->tipe === 'rekrutmen')
                                                    <span class="badge bg-light text-dark border">-</span>
                                                @else
                                                    <span class="badge bg-light text-dark border">
                                                        <i class="fas fa-clock me-1"></i>{{ $item->lama_kegiatan }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="fw-medium text-dark">{{ $item->pic }}</span>
                                            </td>
                                            <td>
                                                <span class="fw-medium text-dark">{{ $item->status }}</span>
                                            </td>
                                            <td class="text-center pe-4 position-relative">
                                                <div class="dropdown">
                                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle"
                                                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        Aksi
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('office.downloadPdfRab', $item->id) }}">
                                                                <i class="fas fa-file-pdf text-danger me-2"></i> Download PDF
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('office.showRincian', $item->id) }}">
                                                                <i class="fas fa-eye text-info me-2"></i> Detail
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <button type="button" class="dropdown-item"
                                                                data-bs-toggle="modal" data-bs-target="#editModal"
                                                                data-id="{{ $item->id }}"
                                                                data-nama="{{ $item->nama_kegiatan }}"
                                                                data-tipe="{{ $item->tipe }}"
                                                                data-waktu="{{ \Carbon\Carbon::parse($item->waktu_kegiatan)->format('Y-m-d\TH:i') }}"
                                                                data-durasi="{{ $item->lama_kegiatan }}"
                                                                data-pic="{{ $item->pic }}">
                                                                <i class="fas fa-pen text-primary me-2"></i> Edit
                                                            </button>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <form action="{{ route('office.deleteKegiatan', $item->id) }}"
                                                                method="POST"
                                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus kegiatan ini?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item text-danger">
                                                                    <i class="fas fa-trash me-2"></i> Hapus
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
                                                <div class="d-flex flex-column align-items-center gap-3">
                                                    <h5 class="text-muted mb-1">Belum ada RAB</h5>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if ($kegiatan->count() > 0)
                        <div class="card-footer border-0 py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Menampilkan {{ $kegiatan->count() }} RAB</small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="tab-pane fade @if($section === 'content') show active @endif" id="pembelian-page" role="tabpanel">
                <ul class="nav nav-tabs" id="pembelianTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="rencana-tab" data-bs-toggle="tab" data-bs-target="#rencana" type="button">Rencana</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="terlaksana-tab" data-bs-toggle="tab" data-bs-target="#terlaksana" type="button">Terlaksana</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="dibatalkan-tab" data-bs-toggle="tab" data-bs-target="#dibatalkan" type="button">Dibatalkan</button>
                    </li>
                </ul>

                <div class="tab-content p-3 border border-top-0 rounded-bottom bg-white shadow-sm" id="pembelianTabContent">

                    <div class="tab-pane fade show active" id="rencana" role="tabpanel">
                        <div id="accordion-rencana" class="accordion-wrapper">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="40"></th>
                                            <th width="50">No</th>
                                            <th>Tenggat</th>
                                            <th>Total Item</th>
                                            <th>Total Estimasi Harga</th>
                                            <th>Status</th>
                                            <th width="250" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($rencanas as $index => $row)
                                        @php
                                            $uid = $row->source . '-' . $row->id . '-' . $loop->index;
                                        @endphp
                                        <tr class="purchase-row" data-target="#collapseRencana{{ $uid }}" style="cursor:pointer;">
                                            <td class="text-center"><i class="bi bi-chevron-right toggle-icon"></i></td>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $row->periode ?? '-' }}</td>
                                            <td>{{ $row->details->count() }} Item</td>
                                            <td>Rp {{ number_format($row->details->sum(fn($d) => $d->qty * $d->harga), 0, ',', '.') }}</td>
                                            <td><span class="badge bg-warning">{{ $row->status_pembelian ?? '-' }}</span></td>
                                            <td class="text-center action-cell position-relative">
                                                @if ($row->source === 'pembelian_hr')
                                                <div class="dropdown">
                                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle"
                                                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        Aksi
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                        <li>
                                                            <button class="btn-edit dropdown-item"
                                                                data-id="{{ $row->id }}" data-nokk="{{ $row->no_kk }}"
                                                                data-periode="{{ $row->periode }}" data-status="{{ $row->status_pembelian }}" data-kategori="{{ $row->kategori }}"
                                                                data-items='@json($row->details)'>Edit</button>
                                                        </li>
                                                        <li>
                                                            <button class="btn-upload-invoice dropdown-item" data-invoice="{{ $row->invoice ? asset('storage/'.$row->invoice) : '' }}" data-nokk="{{ $row->no_kk }}" data-id="{{ $row->id }}">Invoice</button>
                                                        </li>
                                                        <li>
                                                            <button class="btn-update-status dropdown-item" data-id="{{ $row->id }}" data-status="{{ $row->status_pembelian }}">Status</button>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <button class="btn-link-pengajuan dropdown-item text-primary"
                                                                data-id="{{ $row->id }}"
                                                                data-tipe="{{ $row->kategori ?? 'Umum' }}"
                                                                data-periode="{{ $row->periode }}"
                                                                data-items='@json($row->details)'>
                                                                <i class="bi bi-link-45deg me-2"></i>Up ke Pengajuan
                                                            </button>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <button class="text-danger btn-delete dropdown-item" data-id="{{ $row->id }}">Hapus</button>
                                                        </li>
                                                    </ul>
                                                </div>
                                                @else
                                                    <a class="btn btn-sm btn-secondary" href="{{ route('office.showRincian', $row->id) }}">
                                                        Detail
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr class="collapse" id="collapseRencana{{ $uid }}" data-bs-parent="#accordion-rencana">
                                            <td colspan="8" class="bg-light p-3 collapse-row-content">
                                                <ul class="nav nav-tabs mb-3" role="tablist">
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#barang-r-{{ $uid }}" type="button"><i class="bi bi-box-seam"></i> Detail Barang</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tracking-r-{{ $uid }}" type="button"><i class="bi bi-clock-history"></i> Riwayat Tracking</button>
                                                    </li>
                                                </ul>
                                                <div class="tab-content p-3 border border-top-0 rounded-bottom bg-white shadow-sm">
                                                    <div class="tab-pane fade show active" id="barang-r-{{ $uid }}" role="tabpanel">
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-bordered bg-white mb-0">
                                                                <thead class="table-secondary"><tr>
                                                                    <th width="40">No</th>
                                                                    <th>Nama Barang</th>
                                                                    <th width="80">Qty</th>
                                                                    <th width="130">Harga Satuan</th>
                                                                    <th width="130">Subtotal</th>
                                                                    <th>Link Pembelian</th>
                                                                    <th>Keterangan</th>
                                                                </tr></thead>
                                                                <tbody>
                                                                    @forelse($row->details as $i => $item)
                                                                    <tr>
                                                                        <td>{{ $i + 1 }}</td>
                                                                        <td>{{ $item->nama_barang }}</td>
                                                                        <td class="text-center">{{ $item->qty }}</td>
                                                                        <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                                                        <td>Rp {{ number_format($item->qty * $item->harga, 0, ',', '.') }}</td>
                                                                        <td>{{ $item->url ?? '-' }}</td>
                                                                        <td>{{ $item->keterangan ?? '-' }}</td>
                                                                    </tr>
                                                                    @empty
                                                                    <tr><td colspan="7" class="text-center text-muted">Tidak ada item</td></tr>
                                                                    @endforelse
                                                                </tbody>
                                                                <tfoot class="table-light"><tr><th colspan="4" class="text-end">Total:</th><th class="text-end">Rp {{ number_format($row->details->sum(fn($d) => $d->qty * $d->harga), 0, ',', '.') }}</th><th></th><th></th></tr></tfoot>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane fade" id="tracking-r-{{ $uid }}" role="tabpanel">
                                                        @if ($row->source === 'pembelian_hr')
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-bordered table-hover bg-white mb-0">
                                                                    <thead class="table-secondary"><tr>
                                                                        <th width="50">No</th>
                                                                        <th>Aktivitas</th>
                                                                        <th>Karyawan</th>
                                                                        <th width="180">Tanggal</th>
                                                                    </tr></thead>
                                                                    <tbody>
                                                                        @forelse($row->tracking as $i => $item)
                                                                        <tr>
                                                                            <td class="text-center">{{ $i + 1 }}</td>
                                                                            <td>
                                                                                {{ $item->tracking }}
                                                                            </td>
                                                                            <td>{{ $item->karyawan->nama_lengkap ?? '-' }}</td>
                                                                            <td><small><i class="bi bi-clock"></i> {{ \Carbon\Carbon::parse($item->created_at)->format('d M Y H:i') }}</small></td>
                                                                        </tr>
                                                                        @empty
                                                                        <tr><td colspan="4" class="text-center text-muted py-3"><i class="bi bi-inbox fs-4 d-block mb-2"></i>Belum ada riwayat tracking</td></tr>
                                                                        @endforelse
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        @else
                                                            <div class="row g-2 text-center">
                                                                <div class="col">
                                                                    <small class="d-block text-muted">Diajukan</small>
                                                                    <small>{{ $row->created_at ? \Carbon\Carbon::parse($row->created_at)->translatedFormat('d M H:i') : '-' }}</small>
                                                                </div>
                                                                <div class="col">
                                                                    <small class="d-block text-muted">Menunggu</small>
                                                                    <small
                                                                        class="{{ $row->menunggu ? 'text-warning' : '' }}">{{ $row->menunggu ? \Carbon\Carbon::parse($row->menunggu)->translatedFormat('d M H:i') : '-' }}</small>
                                                                </div>
                                                                <div class="col">
                                                                    <small class="d-block text-muted">Approved</small>
                                                                    <small
                                                                        class="{{ $row->approved ? 'text-success' : '' }}">{{ $row->approved ? \Carbon\Carbon::parse($row->approved)->translatedFormat('d M H:i') : '-' }}</small>
                                                                </div>
                                                                <div class="col">
                                                                    <small class="d-block text-muted">Pencairan</small>
                                                                    <small
                                                                        class="{{ $row->pencairan ? 'text-info' : '' }}">{{ $row->pencairan ? \Carbon\Carbon::parse($row->pencairan)->translatedFormat('d M H:i') : '-' }}</small>
                                                                </div>
                                                                <div class="col">
                                                                    <small class="d-block text-muted">Selesai</small>
                                                                    <small
                                                                        class="{{ $row->selesai ? 'text-primary' : '' }}">{{ $row->selesai ? \Carbon\Carbon::parse($row->selesai)->translatedFormat('d M H:i') : '-' }}</small>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data rencana pembelian.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="terlaksana" role="tabpanel">
                        <div id="accordion-terlaksana" class="accordion-wrapper">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="40"></th><th width="50">No</th><th>No. KK</th><th>Tenggat</th>
                                            <th>Total Item</th><th>Total Harga</th><th>Status</th><th width="150" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($pembelian as $index => $row)
                                        @php
                                            $uid = $row->source . '-' . $row->id . '-' . $loop->index;
                                        @endphp
                                        <tr class="purchase-row" data-target="#collapseTerlaksana{{ $uid }}" style="cursor:pointer;">
                                            <td class="text-center"><i class="bi bi-chevron-right toggle-icon"></i></td>
                                            <td>{{ $index + 1 }}</td>
                                            <td><strong>{{ $row->no_kk ?? '-' }}</strong></td>
                                            <td>{{ $row->periode ?? '-' }}</td>
                                            <td>{{ $row->details->count() }} Item</td>
                                            <td>Rp {{ number_format($row->details->sum(fn($d) => $d->qty * $d->harga), 0, ',', '.') }}</td>
                                            <td><span class="badge bg-success">{{ $row->status_pembelian }}</span></td>
                                            <td class="text-center action-cell">
                                                @if ($row->source === 'pembelian_hr')
                                                    <button class="btn btn-sm btn-primary btn-upload-invoice" data-invoice="{{ $row->invoice ? asset('storage/'.$row->invoice) : '' }}" @if ($row->source === 'pembelian_hr') data-nokk="{{ $row->no_kk }}" @endif data-id="{{ $uid }}">Invoice</button>
                                                @else
                                                    <a class="btn btn-sm btn-secondary" href="{{ route('office.showRincian', $row->id) }}">
                                                        Detail
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr class="collapse" id="collapseTerlaksana{{ $uid }}" data-bs-parent="#accordion-terlaksana">
                                            <td colspan="8" class="bg-light p-3 collapse-row-content">
                                                <ul class="nav nav-tabs mb-3" role="tablist">
                                                    <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#barang-t-{{ $uid }}" type="button"><i class="bi bi-box-seam"></i> Detail Barang</button></li>
                                                    <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tracking-t-{{ $uid }}" type="button"><i class="bi bi-clock-history"></i> Riwayat Tracking</button></li>
                                                </ul>
                                                <div class="tab-content p-3 border border-top-0 rounded-bottom bg-white shadow-sm">
                                                    <div class="tab-pane fade show active" id="barang-t-{{ $uid }}" role="tabpanel">
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-bordered bg-white mb-0">
                                                                <thead class="table-secondary"><tr>
                                                                    <th width="40">No</th>
                                                                    <th>Nama Barang</th>
                                                                    <th width="80">Qty</th>
                                                                    <th width="130">Harga Satuan</th>
                                                                    <th width="130">Subtotal</th>
                                                                    <th>Link Pembelian</th>
                                                                    <th>Keterangan</th></tr></thead>
                                                                <tbody>
                                                                    @forelse($row->details as $i => $item)
                                                                    <tr>
                                                                        <td>{{ $i + 1 }}</td>
                                                                        <td>{{ $item->nama_barang }}</td>
                                                                        <td class="text-center">{{ $item->qty }}</td>
                                                                        <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                                                        <td>Rp {{ number_format($item->qty * $item->harga, 0, ',', '.') }}</td>
                                                                        <td>{{ $item->url ?? '-' }}</td>
                                                                        <td>{{ $item->keterangan ?? '-' }}</td>
                                                                    </tr>
                                                                    @empty
                                                                    <tr><td colspan="7" class="text-center text-muted">Tidak ada item</td></tr>
                                                                    @endforelse
                                                                </tbody>
                                                                <tfoot class="table-light"><tr><th colspan="4" class="text-end">Total:</th><th class="text-end">Rp {{ number_format($row->details->sum(fn($d) => $d->qty * $d->harga), 0, ',', '.') }}</th><th></th><th></th></tr></tfoot>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane fade" id="tracking-t-{{ $uid }}" role="tabpanel">
                                                        @if ($row->source === 'pembelian_hr')
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-bordered table-hover bg-white mb-0">
                                                                    <thead class="table-secondary"><tr>
                                                                        <th width="50">No</th>
                                                                        <th>Aktivitas</th>
                                                                        <th>Karyawan</th>
                                                                        <th width="180">Tanggal</th>
                                                                    </tr></thead>
                                                                    <tbody>
                                                                        @forelse($row->tracking as $i => $item)
                                                                        <tr>
                                                                            <td class="text-center">{{ $i + 1 }}</td>
                                                                            <td>
                                                                                {{ $item->tracking }}
                                                                            </td>
                                                                            <td>{{ $item->karyawan->nama_lengkap ?? '-' }}</td>
                                                                            <td><small><i class="bi bi-clock"></i> {{ \Carbon\Carbon::parse($item->created_at)->format('d M Y H:i') }}</small></td>
                                                                        </tr>
                                                                        @empty
                                                                        <tr><td colspan="4" class="text-center text-muted py-3"><i class="bi bi-inbox fs-4 d-block mb-2"></i>Belum ada riwayat tracking</td></tr>
                                                                        @endforelse
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        @else
                                                            <div class="row g-2 text-center">
                                                                <div class="col">
                                                                    <small class="d-block text-muted">Diajukan</small>
                                                                    <small>{{ $row->created_at ? \Carbon\Carbon::parse($row->created_at)->translatedFormat('d M H:i') : '-' }}</small>
                                                                </div>
                                                                <div class="col">
                                                                    <small class="d-block text-muted">Menunggu</small>
                                                                    <small
                                                                        class="{{ $row->menunggu ? 'text-warning' : '' }}">{{ $row->menunggu ? \Carbon\Carbon::parse($row->menunggu)->translatedFormat('d M H:i') : '-' }}</small>
                                                                </div>
                                                                <div class="col">
                                                                    <small class="d-block text-muted">Approved</small>
                                                                    <small
                                                                        class="{{ $row->approved ? 'text-success' : '' }}">{{ $row->approved ? \Carbon\Carbon::parse($row->approved)->translatedFormat('d M H:i') : '-' }}</small>
                                                                </div>
                                                                <div class="col">
                                                                    <small class="d-block text-muted">Pencairan</small>
                                                                    <small
                                                                        class="{{ $row->pencairan ? 'text-info' : '' }}">{{ $row->pencairan ? \Carbon\Carbon::parse($row->pencairan)->translatedFormat('d M H:i') : '-' }}</small>
                                                                </div>
                                                                <div class="col">
                                                                    <small class="d-block text-muted">Selesai</small>
                                                                    <small
                                                                        class="{{ $row->selesai ? 'text-primary' : '' }}">{{ $row->selesai ? \Carbon\Carbon::parse($row->selesai)->translatedFormat('d M H:i') : '-' }}</small>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data pembelian terlaksana.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="dibatalkan" role="tabpanel">
                        <div id="accordion-dibatalkan" class="accordion-wrapper">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="40"></th>
                                            <th width="50">No</th>
                                            <th>Tenggat</th>
                                            
                                            <th>Total Item</th>
                                            <th>Total Harga</th>
                                            <th>Status</th>
                                            <th width="300">Alasan Dibatalkan</th>
                                            <th width="100" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse(($dibatalkan ?? []) as $index => $row)
                                        <tr class="purchase-row" data-target="#collapseDibatalkan{{ $row->id }}" style="cursor:pointer;">
                                            <td class="text-center"><i class="bi bi-chevron-right toggle-icon"></i></td>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $row->periode ?? '-' }}</td>
                                            <td>{{ $row->details->count() }} Item</td>
                                            <td>Rp {{ number_format($row->details->sum(fn($d) => $d->qty * $d->harga), 0, ',', '.') }}</td>
                                            <td><span class="badge bg-danger">{{ $row->status_pembelian }}</span></td>
                                            <td>{{ $row->alasan_dibatalkan }}</td>
                                            <td class="text-center action-cell">
                                                <button class="btn btn-sm btn-danger btn-delete" data-id="{{ $row->id }}">Hapus</button>
                                            </td>
                                        </tr>
                                        <tr class="collapse" id="collapseDibatalkan{{ $row->id }}" data-bs-parent="#accordion-dibatalkan">
                                            <td colspan="8" class="bg-light p-3 collapse-row-content">
                                                <ul class="nav nav-tabs mb-3" role="tablist">
                                                    <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#barang-d-{{ $row->id }}" type="button"><i class="bi bi-box-seam"></i> Detail Barang</button></li>
                                                    <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tracking-d-{{ $row->id }}" type="button"><i class="bi bi-clock-history"></i> Riwayat Tracking</button></li>
                                                </ul>
                                                <div class="tab-content p-3 border border-top-0 rounded-bottom bg-white shadow-sm">
                                                    <div class="tab-pane fade show active" id="barang-d-{{ $row->id }}" role="tabpanel">
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-bordered bg-white mb-0">
                                                                <thead class="table-secondary"><tr>
                                                                    <th width="40">No</th>
                                                                    <th>Nama Barang</th>
                                                                    <th width="80">Qty</th>
                                                                    <th width="130">Harga Satuan</th>
                                                                    <th width="130">Subtotal</th>
                                                                    <th>Link Pembelian</th>
                                                                    <th>Keterangan</th>
                                                                </tr></thead>
                                                                <tbody>
                                                                    @forelse($row->details as $i => $item)
                                                                    <tr>
                                                                        <td>{{ $i + 1 }}</td>
                                                                        <td>{{ $item->nama_barang }}</td>
                                                                        <td class="text-center">{{ $item->qty }}</td>
                                                                        <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                                                        <td>Rp {{ number_format($item->qty * $item->harga, 0, ',', '.') }}</td>
                                                                        <td>{{ $item->url ?? '-' }}</td>
                                                                        <td>{{ $item->keterangan ?? '-' }}</td></tr>
                                                                    @empty
                                                                    <tr><td colspan="7" class="text-center text-muted">Tidak ada item</td></tr>
                                                                    @endforelse
                                                                </tbody>
                                                                <tfoot class="table-light"><tr><th colspan="5" class="text-end">Total:</th><th class="text-end">Rp {{ number_format($row->details->sum(fn($d) => $d->qty * $d->harga), 0, ',', '.') }}</th><th></th></tr></tfoot>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane fade" id="tracking-d-{{ $row->id }}" role="tabpanel">
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-bordered table-hover bg-white mb-0">
                                                                <thead class="table-secondary"><tr><th width="50">No</th><th>Aktivitas</th><th>Karyawan</th><th width="180">Tanggal</th></tr></thead>
                                                                <tbody>
                                                                    @forelse($row->tracking as $i => $item)
                                                                    <tr>
                                                                        <td class="text-center">{{ $i + 1 }}</td>
                                                                        <td>
                                                                            {{ $item->tracking }}
                                                                        </td>
                                                                        <td>{{ $item->karyawan->nama_lengkap ?? '-' }}</td>
                                                                        <td><small><i class="bi bi-clock"></i> {{ \Carbon\Carbon::parse($item->created_at)->format('d M Y H:i') }}</small></td>
                                                                    </tr>
                                                                    @empty
                                                                    <tr><td colspan="4" class="text-center text-muted py-3"><i class="bi bi-inbox fs-4 d-block mb-2"></i>Belum ada riwayat tracking</td></tr>
                                                                    @endforelse
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data pembelian dibatalkan.</td></tr>
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

    <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form action="{{ route('office.storeKegiatan') }}" method="POST"
                class="modal-content shadow-lg border-0 rounded-4">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="createModalLabel">Pengajuan RAB Baru</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-file-invoice text-primary me-2"></i>Nama RAB
                            </label>
                            <input type="text" name="nama_kegiatan" class="form-control form-control-lg"
                                placeholder="Masukkan nama" required>
                        </div>
                        <div class="col-12 mb-2">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-layer-group text-primary me-2"></i>Tipe RAB
                            </label>
                            <select name="tipe" id="tipeRAB" class="form-select form-select-lg" required>
                                <option value="" selected disabled>Pilih tipe RAB</option>
                                <option value="kegiatan">Kegiatan</option>
                                <option value="pembelian">Pembelian</option>
                                <option value="rekrutmen">Rekrutmen</option>
                            </select>
                        </div>
                        <div id="pickupDriverCondition">
                            <hr class="hidePickup">
                            <small class="hidePickup text-danger">* digunakan hanya jika kegiatan diperlukan pengantaran
                                driver!</small>
                            <div class="col-12 mb-2 hidePickup" style="display: none;">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-car text-primary me-2"></i>Pilih Driver
                                </label>
                                <select name="id_driver" class="form-select text-dark form-select-lg">
                                    <option value="" selected disabled>Pilih Driver</option>
                                    @foreach ($drivers as $driver)
                                        <option value="{{ $driver->id }}">{{ $driver->nama_lengkap }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 mb-2 hidePickup" style="display: none;">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-car text-primary me-2"></i>Budget Kendaraan
                                </label>
                                <input type="text" id="budgetInput" class="form-control form-control-lg"
                                    placeholder="Rp 0 (opsional)">
                                <input type="hidden" name="budget" id="budgetHidden">
                            </div>
                            <div class="col-12 mb-2 hidePickup" style="display: none;">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-car text-primary me-2"></i>Lokasi Pengantaran
                                </label>
                                <input type="text" class="form-control form-control-lg"
                                    placeholder="lokasi kegiatan (opsional untuk driver)" name="lokasi">
                            </div>
                            <hr class="hidePickup">
                        </div>
                        <div id="kegiatanFields" style="display: contents;">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-clock text-primary me-2"></i>Waktu Pelaksanaan
                                </label>
                                <input type="datetime-local" name="waktu_kegiatan" class="form-control form-control-lg"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-hourglass-half text-primary me-2"></i>Durasi
                                </label>
                                <input type="text" name="lama_kegiatan" class="form-control form-control-lg"
                                    placeholder="Contoh: 2 jam" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-user text-primary me-2"></i>PIC
                            </label>
                            <input type="text" name="pic" class="form-control form-control-lg"
                                placeholder="Masukkan nama PIC" value="{{ auth()->user()->karyawan->nama_lengkap }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Ajukan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form action="" method="POST" id="editForm" class="modal-content shadow-lg border-0 rounded-4">
                @csrf
                @method('PUT')
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="editModalLabel">Edit Pengajuan Kegiatan</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-calendar-alt text-primary me-2"></i>Nama Kegiatan
                            </label>
                            <input type="text" name="nama_kegiatan" id="edit_nama_kegiatan"
                                class="form-control form-control-lg" placeholder="Masukkan nama kegiatan" required>
                        </div>
                        <div id="edit_kegiatanFields" style="display: contents;">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-clock text-primary me-2"></i>Waktu Kegiatan
                                </label>
                                <input type="datetime-local" name="waktu_kegiatan" id="edit_waktu_kegiatan"
                                    class="form-control form-control-lg" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-hourglass-half text-primary me-2"></i>Durasi
                                </label>
                                <input type="text" name="lama_kegiatan" id="edit_lama_kegiatan"
                                    class="form-control form-control-lg" placeholder="Contoh: 2 jam" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-user text-primary me-2"></i>PIC
                            </label>
                            <input type="text" name="pic" id="edit_pic" class="form-control form-control-lg"
                                placeholder="Masukkan nama PIC" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Rencana pembelian modal --}}
    <div class="modal fade" id="formModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <form id="formPembelian" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="formModalLabel">Tambah Rencana Pembelian</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="deleted_ids" id="deleted_ids" value="">
                        <input type="hidden" name="status_pembelian" id="status_pembelian" value="Rencana">
                        <div class="row mb-3">
                            <div class="col-md-4" id="KKWrapper" style="display:none;">
                                <label class="form-label">No. KK / No. Pengajuan</label>
                                <input type="text" name="no_kk" id="no_kk" class="form-control">
                            </div>
                            <div class="col-md-4" id="periodeWrapper" style="display:none;">
                                <label class="form-label">Periode <span class="text-danger">*</span></label>
                                <select name="periode" id="periode" class="form-select" required>
                                    <option value="">Pilih Periode</option>
                                    <option value="Q1">Q1</option>
                                    <option value="Q2">Q2</option>
                                    <option value="Q3">Q3</option>
                                    <option value="Q4">Q4</option>
                                </select>
                            </div>
                            <div class="col-md-4" id="KategoriWrapper" style="display:none;">
                                <label class="form-label">Kategori Inventaris <span class="text-danger">*</span></label>
                                <select name="kategori" id="kategori" class="form-select" required>
                                    <option value="Inventaris Office">Inventaris Office</option>
                                    <option value="Inventaris Education">Inventaris Education</option>
                                    <option value="Inventaris Sales & Marketing">Inventaris Sales & Marketing</option>
                                    <option value="Inventaris ITSM">Inventaris ITSM</option>
                                    <option value="Inventaris Kantor">Inventaris Kantor</option>
                                    <option value="Inventaris Kelas">Inventaris Kelas</option>
                                </select>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 fw-bold">Detail Barang</h6>
                            <button type="button" class="btn btn-sm btn-success" id="btnAddItem"><i class="bi bi-plus-circle"></i> Tambah Item</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle" id="itemsTable">
                                <thead class="table-light"><tr>
                                    <th>Nama Barang</th>
                                    <th width="100">Qty</th>
                                    <th width="180">Harga Satuan</th>
                                    <th>Link pembelian</th>
                                    <th>Keterangan</th>
                                    <th width="60" class="text-center">#</th>
                                </tr></thead>
                                <tbody id="itemsBody"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rekapModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <form id="formRekap" method="POST" action="{{ route('rencanaPembelian.storeRekap') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="rekapModalLabel">Tambah Rekap Pembelian</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="status_pembelian" id="status_pembelian" value="Terlaksana">
                        <div class="row mb-3">
                            <div class="col-md-4" id="periodeWrapper">
                                <label class="form-label">Periode <span class="text-danger">*</span></label>
                                <select name="periode" id="periode" class="form-select" required>
                                    <option value="">Pilih Periode</option>
                                    <option value="Q1">Q1</option>
                                    <option value="Q2">Q2</option>
                                    <option value="Q3">Q3</option>
                                    <option value="Q4">Q4</option>
                                </select>
                            </div>
                            <div class="col-md-4" id="KategoriWrapper">
                                <label class="form-label">Kategori Inventaris <span class="text-danger">*</span></label>
                                <select name="kategori" id="kategori" class="form-select" required>
                                    <option value="Inventaris Office">Inventaris Office</option>
                                    <option value="Inventaris Education">Inventaris Education</option>
                                    <option value="Inventaris Sales & Marketing">Inventaris Sales & Marketing</option>
                                    <option value="Inventaris ITSM">Inventaris ITSM</option>
                                    <option value="Inventaris Kantor">Inventaris Kantor</option>
                                    <option value="Inventaris Kelas">Inventaris Kelas</option>
                                </select>
                            </div>
                        </div>
                        <hr>

                        <div class="mt-4">
                            <h6 class="text-muted mb-3">Daftar Data Pengajuan Barang :</h6>

                            <div class="row g-3 d-flex align-items-end mb-3">
                                <div class="col-md-4">
                                    <label class="form-label small">Pembuat Pengajuan</label>
                                    <select name="pembuat" id="pembuatPengajuan" class="form-select">
                                        <option value="" selected>Pilih Karyawan</option>
                                        @foreach ($karyawans as $karyawan)
                                            <option value="{{ $karyawan->id }}" {{ auth()->user()->karyawan->id === $karyawan->id ? 'selected' : '' }}>{{ $karyawan->nama_lengkap }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="border-0 col-md-4">
                                    <button type="button" id="cariDataPengajuanBtn" class="btn btn-primary px-4">
                                        Cari data
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive mb-4" style="max-height: 400px; overflow-y: auto;">
                                <table id="tabelDataPengajuan" class="table table-hover align-middle mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <td>Nama Barang</td>
                                            <td>Tanggal Pembelian</td>
                                            <td>Total Harga</td>
                                            <td>Diajukan Oleh</td>
                                            <td></td>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formDelete" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin menghapus data rencana pembelian ini?</p>
                        <p class="text-danger mb-0"><small><i class="bi bi-exclamation-triangle"></i> Data yang dihapus tidak dapat dikembalikan.</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Ya, Hapus!</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="invoiceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <form id="formInvoice" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-paperclip"></i> Invoice - <span id="invoiceNoKk">-</span></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-light border mb-3" id="invoiceStatusInfo">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-info-circle-fill text-primary fs-4 me-2"></i>
                                <div><strong id="invoiceStatusText">Belum ada invoice</strong><small class="d-block text-muted" id="invoiceStatusDesc">Silakan upload file invoice terlebih dahulu</small></div>
                            </div>
                        </div>
                        <div id="invoicePreviewWrapper" style="display:none;">
                            <div class="card border-primary mb-3">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <strong><i class="bi bi-eye"></i> Preview Invoice</strong>
                                    <div><a href="#" id="invoiceDownloadLink" target="_blank" class="btn btn-sm btn-light"><i class="bi bi-download"></i> Download / Buka Tab Baru</a></div>
                                </div>
                                <div class="card-body p-0 bg-light"><div id="invoicePreviewContainer" style="height: 500px; overflow: hidden;"></div></div>
                            </div>
                        </div>
                        <div class="card border-warning">
                            <div class="card-header text-dark bg-warning bg-opacity-10"><strong><i class="bi bi-cloud-upload"></i> Upload Invoice Baru</strong></div>
                            <div class="card-body">
                                <label class="form-label">Pilih File Invoice <span class="text-danger">*</span></label>
                                <input type="file" name="invoice" class="form-control" required accept=".pdf,.jpg,.jpeg,.png" id="invoiceFileInput">
                                <small class="text-muted">Format: PDF, JPG, PNG</small>
                                <div id="newFilePreview" class="mt-3" style="display:none;">
                                    <div class="alert alert-info py-2 mb-0"><i class="bi bi-file-earmark-check"></i> File terpilih: <strong id="newFileName"></strong></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Upload Invoice</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formStatus" method="POST">
                    @csrf
                    <input type="hidden" name="id_pembelian" value="">
                    <div class="modal-header">
                        <h5 class="modal-title">Ubah Status Pembelian</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Pilih Status Baru</label>
                            <select name="status" class="form-select" required>
                                <option value="Rencana">Rencana</option>
                                <option value="Terlaksana">Terlaksana</option>
                                <option value="Dibatalkan">Dibatalkan</option>
                            </select>
                        </div>
                        <div id="alasanDibatalkanWraper" style="display: none;">
                            <label class="form-label">Alasan dibatalkan</label>
                            <textarea name="alasan_dibatalkan" class="form-control" cols="30" rows="3" placeholder="Isi alasan rencana dibatalkan"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="linkPengajuanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <form id="formLinkPengajuan" method="POST" action="{{ route('pengajuanbarang.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-link-45deg me-2"></i>Link Rencana ke Pengajuan Barang
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info border-0 shadow-sm mb-3">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-info-circle-fill fs-4 me-3 mt-1"></i>
                                <div>
                                    <strong>Data dari rencana ini akan di-up ke pengajuan barang.</strong>
                                    <div class="small text-muted mt-1">
                                        Periode: <strong id="linkPeriode">-</strong> |
                                        Tipe: <strong id="linkTipe">-</strong> |
                                        Total Item: <strong id="linkTotalItem">0</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="id_rencana" id="linkIdRencana" value="">
                        <input type="hidden" name="pembelianHr" value="true">
                        <input type="text" name="id_karyawan" id="linkIdKaryawan"
                            class="form-control"
                            value="{{ auth()->user()->karyawan->id }}" required hidden>

                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-body">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-tag text-primary me-2"></i>Tipe Pengajuan
                                    <span class="text-danger">*</span>
                                </label>
                                <select name="tipe" id="linkTipeSelect" class="form-select" required>
                                    <option value="">Pilih Jenis Barang</option>
                                    <option value="ATK">ATK</option>
                                    <option value="Elektronik">Elektronik</option>
                                    <option value="Makanan">Makanan</option>
                                    <option value="Souvenir">Souvenir</option>
                                    <option value="Operasional">Operasional</option>
                                    <option value="Reimbursement">Reimbursement</option>
                                    <option value="Training & Sertifikasi">Training & Sertifikasi</option>
                                </select>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <strong><i class="bi bi-box-seam me-2"></i>Daftar Barang yang akan di-Link</strong>
                                <span class="badge bg-primary" id="linkBadgeCount">0 item</span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-secondary">
                                            <tr>
                                                <th width="40">No</th>
                                                <th>Nama Barang</th>
                                                <th width="100">Qty</th>
                                                <th width="150">Harga Barang</th>
                                                <th>Keterangan</th>
                                                <th width="60" class="text-center">Pilih</th>
                                            </tr>
                                        </thead>
                                        <tbody id="linkItemsBody">
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">
                                                    Belum ada data barang
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-link-45deg me-1"></i>Up ke Pengajuan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Rencana pembelian modal end --}}

    <style>
        .nav-custom .nav-link {
            color: var(--bs-secondary-color);
            border: 1px solid transparent;
            border-radius: .5rem .5rem 0 0;
            transition: all .2s ease;
        }

        .nav-custom .nav-link:hover {
            color: var(--bs-secondary);
            border-color: var(--bs-secondary-border-subtle);
        }

        .nav-custom {
            --bs-nav-tabs-border-color: var(--bs-secondary);
        }

        .nav-custom .nav-link.active,
        .nav-custom .nav-item.show .nav-link {
            color: var(--bs-secondary) !important;
            background-color: transparent;

            border-top: 1px solid var(--bs-secondary) !important;
            border-left: 1px solid var(--bs-secondary) !important;
            border-right: 1px solid var(--bs-secondary) !important;
            border-bottom: 2px solid var(--bs-secondary) !important;

            border-radius: .5rem .5rem 0 0;
            font-weight: 600;
        }

        /* Kembalikan perilaku card Bootstrap */
        .card {
            display: flex !important;
            flex-direction: column !important;
        }

        .card-header,
        .card-body,
        .card-footer {
            width: 100%;
            flex: 0 0 auto;
        }
        .purchase-row {
            transition: background-color 0.15s ease-in-out;
            cursor: pointer;
        }

        .purchase-row:hover {
            background-color: #f8f9fa;
        }

        .purchase-row.active {
            background-color: #e7f1ff;
            box-shadow: inset 0 -1px 0 rgba(0, 0, 0, 0.125);
        }

        .toggle-icon {
            transition: transform 0.35s ease;
            display: inline-block;
            color: #6c757d;
        }

        .purchase-row.active .toggle-icon {
            transform: rotate(90deg);
            color: #0d6efd;
        }

        tr.collapse {
            transition: none !important;
        }

        tr.collapsing {
            opacity: 0;
            height: 0;
            overflow: hidden;
            transition: height 200ms ease-in-out;
        }

        tr.collapse.show .collapse-row-content {
            opacity: 1;
            height: 120px;
        }
        tr.collapse.show {
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
        }
        .purchase-row td:first-child { 
            text-align: center; 
        }

        .modal-dialog-scrollable .modal-body { 
            max-height: calc(100vh - 200px); 
        }

        #invoicePreviewContainer iframe, 
        #invoicePreviewContainer img { 
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); 
        }

        .table-secondary th { 
            font-size: 0.85rem; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
        }

        .accordion-wrapper {
            position: relative;
            border: 1px solid rgba(0, 0, 0, 0.125);
            border-radius: 0.375rem;
            overflow: hidden;
            background: white;
        }

        .accordion-wrapper .table {
            margin-bottom: 0;
        }
        .dropdown-menu{
            z-index:99999;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <script>
        const userJabatan = @json(auth()->user()->jabatan);
        const userDivisi = @json(auth()->user()->karyawan->divisi);

        $(document).on('click', '.dropdown-toggle', function (e) {
            e.stopPropagation();

            const button = $(this);
            const dropdown = button.parent();
            const menu = dropdown.children('.dropdown-menu');

            $('body > .dropdown-menu').each(function () {
                const m = $(this);
                const parent = m.data('parent');

                if (parent) {
                    parent.append(m.detach());
                }

                m.removeAttr('style').removeClass('show');
            });

            if (menu.parent()[0] !== document.body) {
                menu.data('parent', dropdown);
                $('body').append(menu.detach());
            }

            const rect = this.getBoundingClientRect();

            menu.css({
                position: 'fixed',
                top: rect.bottom + 4,
                left: rect.left,
                zIndex: 999999
            }).addClass('show');
        });

        function closeAllDropdown() {
            $('body > .dropdown-menu').each(function () {
                const menu = $(this);
                const parent = menu.data('parent');

                if (parent) {
                    parent.append(menu.detach());
                }
                menu.removeAttr('style').removeClass('show');
            });
        }

        $(document).on('click', function () {
            closeAllDropdown();
        });

        $(document).on('click', '.dropdown-menu', function (e) {
            e.stopPropagation();
        });

        document.addEventListener("DOMContentLoaded", function() {
            const tipeSelect = document.getElementById('tipeRAB');
            const pickupDriverCondition = document.getElementById('pickupDriverCondition');
            const driverFields = pickupDriverCondition.querySelectorAll('.hidePickup');
            const driverSelect = pickupDriverCondition.querySelector('select');

            tipeSelect.addEventListener('change', function() {
                if (this.value === 'kegiatan') {
                    pickupDriverCondition.style.display = 'block';
                    driverFields.forEach(field => {
                        field.style.display = 'block';
                    });
                    driverSelect.setAttribute('required', true);
                } else {
                    pickupDriverCondition.style.display = 'none';
                    driverFields.forEach(field => {
                        field.style.display = 'none';
                    });
                    driverSelect.removeAttribute('required');
                }
            });

            const input = document.getElementById("budgetInput");
            const hidden = document.getElementById("budgetHidden");

            input.addEventListener("input", function(e) {
                let value = e.target.value.replace(/\D/g, "");
                if (!value) {
                    hidden.value = "";
                    input.value = "";
                    return;
                }
                hidden.value = value;
                input.value = "Rp " + parseInt(value).toLocaleString("id-ID");
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const tipeSelect = document.getElementById('tipeRAB');
            const createFields = document.getElementById('kegiatanFields');

            function toggleCreateFields() {
                if (!tipeSelect || !createFields) return;
                if (tipeSelect.value === 'pembelian' || tipeSelect.value === 'rekrutmen') {
                    createFields.style.display = 'none';
                    createFields.querySelectorAll('input').forEach(input => {
                        input.removeAttribute('required');
                        input.value = '';
                    });
                } else {
                    createFields.style.display = 'contents';
                    createFields.querySelectorAll('input').forEach(input => {
                        input.setAttribute('required', 'required');
                    });
                }
            }

            if (tipeSelect) {
                tipeSelect.addEventListener('change', toggleCreateFields);
                toggleCreateFields();
            }

            const editModal = document.getElementById('editModal');
            const editFields = document.getElementById('edit_kegiatanFields');

            if (editModal) {
                editModal.addEventListener('show.bs.modal', event => {
                    const button = event.relatedTarget;
                    const id = button.getAttribute('data-id');
                    const nama = button.getAttribute('data-nama');
                    const tipe = button.getAttribute('data-tipe');
                    const waktu = button.getAttribute('data-waktu');
                    const durasi = button.getAttribute('data-durasi');
                    const pic = button.getAttribute('data-pic');

                    editModal.querySelector('#edit_nama_kegiatan').value = nama;
                    editModal.querySelector('#edit_pic').value = pic;

                    const form = editModal.querySelector('#editForm');
                    form.action = `/office/kegiatan/update/${id}`;

                    if (editFields) {
                        const inputs = editFields.querySelectorAll('input');
                        if (tipe === 'pembelian' || tipe === 'rekrutmen') {
                            editFields.style.display = 'none';
                            inputs.forEach(input => {
                                input.removeAttribute('required');
                                input.value = '';
                            });
                        } else {
                            editFields.style.display = 'contents';
                            inputs.forEach(input => input.setAttribute('required', 'required'));
                            editModal.querySelector('#edit_waktu_kegiatan').value = waktu;
                            editModal.querySelector('#edit_lama_kegiatan').value = durasi;
                        }
                    }
                });
            }
        });


        // Rencana pembelian script
        $(function () {

            const section = @json($section);

            if (section === 'content') {
                $('.main-add-btn').addClass('d-none');
                $('.main-add-btn[data-tab="pembelian-page"]').removeClass('d-none');
                return;
            }

            function syncAddButton() {
                const activePane = $('#mainPageTab .nav-link.active')
                    .data('bs-target')
                    ?.replace('#', '');

                $('.main-add-btn').addClass('d-none');

                if (activePane) {
                    $('.main-add-btn[data-tab="' + activePane + '"]').removeClass('d-none');
                }
            }

            syncAddButton();

            $(document).on('shown.bs.tab', '#mainPageTab button[data-bs-toggle="pill"]', syncAddButton);

        });

        $(document).ready(function() {
            function showModal(modalId) {
                let modalEl = document.getElementById(modalId);
                if (modalEl) {
                    let modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                }
            }

            function toggleCollapse(targetSelector) {
                let collapseEl = document.querySelector(targetSelector);
                if (collapseEl) {
                    let bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false });
                    bsCollapse.toggle();
                    return collapseEl;
                }
                return null;
            }

            $(document).on('click', '.purchase-row', function(e) {
                if ($(e.target).closest('.action-cell').length) {
                    return; 
                }

                const target = $(this).data('target');
                const collapseEl = document.querySelector(target);
                
                if (collapseEl) {
                    const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false });
                    
                    if (collapseEl.classList.contains('show')) {
                        bsCollapse.hide();
                    } else {
                        bsCollapse.show();
                    }
                }
            });

            $(document).on('show.bs.collapse', function(e) {
                const id = '#' + $(e.target).attr('id');
                $(`.purchase-row[data-target="${id}"]`).addClass('active');
            });

            $(document).on('hide.bs.collapse', function(e) {
                const id = '#' + $(e.target).attr('id');
                $(`.purchase-row[data-target="${id}"]`).removeClass('active');
            });

            let itemIndex = 0;

            function formatRupiahInput(angka) {
                if (!angka) return '';
                let number_string = angka.toString().replace(/[^,\d]/g, '');
                let split = number_string.split(',');
                let sisa = split[0].length % 3;
                let rupiah = split[0].substr(0, sisa);
                let ribuan = split[0].substr(sisa).match(/\d{3}/gi);
                if (ribuan) {
                    let separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }
                return split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
            }

            function parseRupiah(rupiah) {
                if (!rupiah) return 0;
                return parseInt(rupiah.replace(/[^0-9]/g, ''), 10) || 0;
            }

            function addItemRow(item = {}) {
                let hargaFormatted = item.harga ? formatRupiahInput(Math.round(parseFloat(item.harga))) : '';
                let html = `
                    <tr class="item-row">
                        <td><input type="hidden" name="items[${itemIndex}][id]" value="${item.id || ''}"><input type="text" name="items[${itemIndex}][nama_barang]" class="form-control form-control-sm" value="${item.nama_barang || ''}" required></td>
                        <td><input type="number" name="items[${itemIndex}][qty]" class="form-control form-control-sm qty-input" value="${item.qty || 1}" min="1" required></td>
                        <td><input type="text" name="items[${itemIndex}][harga_display]" class="form-control form-control-sm harga-display" value="${hargaFormatted}" required><input type="hidden" name="items[${itemIndex}][harga]" class="harga-value" value="${item.harga ? Math.round(parseFloat(item.harga)) : 0}"></td>
                        <td><input type="text" name="items[${itemIndex}][url]" class="form-control form-control-sm" value="${item.url || ''}"></td>
                        <td><input type="text" name="items[${itemIndex}][keterangan]" class="form-control form-control-sm" value="${item.keterangan || ''}"></td>
                        <td class="text-center"><button type="button" class="btn btn-sm btn-danger btn-remove-item">&times;</button></td>
                    </tr>
                `;
                $('#itemsBody').append(html);
                itemIndex++;
            }

            $(document).on('input', '.harga-display', function() {
                let value = $(this).val();
                let parsed = parseRupiah(value);
                $(this).val(formatRupiahInput(parsed));
                $(this).closest('tr').find('.harga-value').val(parsed);
            });

            $(document).on('focus', '.harga-display', function() {
                let value = parseRupiah($(this).val());
                if (value > 0) $(this).val(formatRupiahInput(value));
            });

            function filterKategori() {
                const $kategori = $('#kategori');
                $kategori.find('option').show();

                if (['HRD', 'GM'].includes(userJabatan)) {
                    return;
                }

                const allowed = [
                    'Inventaris Kantor',
                    'Inventaris Kelas'
                ];
                let defaultKategori = '';

                switch ((userDivisi || '').toLowerCase()) {

                    case 'office':
                        allowed.push('Inventaris Office');
                        defaultKategori = 'Inventaris Office';
                        break;

                    case 'education':
                        allowed.push('Inventaris Education');
                        defaultKategori = 'Inventaris Education';
                        break;

                    case 'sales & marketing':
                        allowed.push('Inventaris Sales & Marketing');
                        defaultKategori = 'Inventaris Sales & Marketing';
                        break;

                    case 'it service management':
                        allowed.push('Inventaris ITSM');
                        defaultKategori = 'Inventaris ITSM';
                        break;
                }

                $kategori.find('option').each(function () {
                    const value = $(this).val();
                    if (value === '') return;
                    if (!allowed.includes(value)) {
                        $(this).hide();
                    }
                });

                if (!allowed.includes($kategori.val())) {
                    $kategori.val(defaultKategori);
                }
            }

            $('#btnAddRekap').click(function() {
                filterKategori();
                initKaryawanSelect2();
            });

            function initKaryawanSelect2() {
                var $select = $('#pembuatPengajuan');
                if (typeof $.fn.select2 !== 'function') {
                    console.error('Select2 belum ter-load!');
                    return;
                }
                var $closestModal = $select.closest('.modal');
                $select.select2({
                    width: '100%',
                    theme: 'bootstrap-5',
                    dropdownParent: $closestModal.length ? $closestModal : $(document.body)
                });
            }

            $('#cariDataPengajuanBtn').on('click', function() {
                let idPembuat = $('#pembuatPengajuan').val();

                $.ajax({
                    url: '/rencana-pembelian/get-jurnal',
                    type: 'GET',
                    data: {pembuat: idPembuat},
                    success: function(res) {
                        let tbody = $('#tabelDataPengajuan tbody');
                        tbody.empty();

                        if (!res.data || res.data.length === 0) {

                            tbody.append(`
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        Tidak ada data
                                    </td>
                                </tr>
                            `);

                            return;
                        }

                        res.data.forEach(function (item) {

                            tbody.append(`
                                <tr>
                                    <td>${item.nama_barang}</td>
                                    <td>${item.tanggal}</td>
                                    <td>Rp ${Number(item.total_harga).toLocaleString('id-ID')}</td>
                                    <td>${item.diajukan_oleh}</td>
                                    <td class="text-center">
                                        <div class="form-check d-flex justify-content-center">
                                            <input
                                                class="form-check-input pilihPengajuan"
                                                type="checkbox"
                                                data-jurnal="${item.id_jurnal}"
                                                data-pengajuan="${item.id_pengajuan}">
                                        </div>
                                    </td>
                                </tr>
                            `);

                        });
                    }
                })
            });

            $('#formRekap').on('submit', function(e){
                $('.selected-item').remove();
                let total = 0;

                $('.pilihPengajuan:checked').each(function(){
                    total++;

                    $('<input>')
                        .attr({
                            type:'hidden',
                            name:'items['+total+'][id_jurnal]',
                            value:$(this).data('jurnal'),
                            class:'selected-item'
                        })
                        .appendTo('#formRekap');

                    $('<input>')
                        .attr({
                            type:'hidden',
                            name:'items['+total+'][id_pengajuan]',
                            value:$(this).data('pengajuan'),
                            class:'selected-item'
                        })
                        .appendTo('#formRekap');

                });

                if(total==0){
                    e.preventDefault();
                    alert('Pilih minimal satu data.');
                    return;
                }
            });

            $('#btnCreate').click(function() {
                $('#formModalLabel').text('Tambah Rencana Pembelian');
                $('#formPembelian').attr('action', "{{ route('rencanaPembelian.store') }}");
                $('#periodeWrapper').show();
                $('#KategoriWrapper').show();
                $('#KKWrapper').hide();
                $('#formPembelian')[0].reset();
                $('#itemsBody').empty();
                $('#deleted_ids').val('');
                $('#status_pembelian').val('Rencana');
                $('#periode').val('');

                itemIndex = 0;
                addItemRow(); 

                filterKategori();
            });

            $(document).on('click', '.btn-edit', function(e) {
                e.preventDefault();
                let id = $(this).data('id');
                let noKk = $(this).data('nokk');
                let periode = $(this).data('periode');
                let status = $(this).data('status');
                let items = $(this).data('items'); 
                let kategori = $(this).data('kategori');

                $('#formModalLabel').text('Edit Rencana Pembelian');
                let url = "{{ route('rencanaPembelian.update', ':id') }}";
                $('#formPembelian').attr('action', url.replace(':id', id));
                $('#periodeWrapper').show();
                $('#KategoriWrapper').show();
                $('#KKWrapper').show();
                $('#no_kk').val(noKk);
                $('#status_pembelian').val(status || 'Rencana');
                $('#periode').val(periode);
                $('#kategori').val(kategori);
                $('#itemsBody').empty();
                $('#deleted_ids').val('');
                itemIndex = 0;

                if (items && items.length > 0) {
                    items.forEach(function(item) { addItemRow(item); });
                } else {
                    addItemRow();
                }

                showModal('formModal');
            });

            $('#btnAddItem').click(function() { addItemRow(); });
            
            $('#itemsBody').on('click', '.btn-remove-item', function() {
                let row = $(this).closest('.item-row');
                let id = row.find('input[name*="[id]"]').val();
                if (id) {
                    let deletedIds = $('#deleted_ids').val();
                    deletedIds = deletedIds ? deletedIds + ',' + id : id;
                    $('#deleted_ids').val(deletedIds);
                }
                row.remove();
            });

            $(document).on('click', '.btn-delete', function(e) {
                e.preventDefault();
                let id = $(this).data('id');
                let url = "{{ route('rencanaPembelian.delete', ':id') }}";
                $('#formDelete').attr('action', url.replace(':id', id));
                showModal('deleteModal'); 
            });

            $(document).on('click', '.btn-upload-invoice', function(e) {
                e.preventDefault();
                let id = $(this).data('id');
                let invoiceUrl = $(this).data('invoice');
                let noKk = $(this).data('nokk');
                let url = "{{ route('rencanaPembelian.updateInvoice', ':id') }}";
                
                $('#formInvoice').attr('action', url.replace(':id', id));
                $('#invoiceNoKk').text(noKk || '-');
                $('#invoicePreviewWrapper').hide();
                $('#invoicePreviewContainer').html('');
                $('#newFilePreview').hide();
                $('#invoiceFileInput').val('');
                
                if (invoiceUrl && invoiceUrl !== '') {
                    $('#invoiceStatusText').text('Invoice sudah tersedia');
                    $('#invoiceStatusDesc').text('File invoice sudah diupload. Anda dapat melihat preview di bawah atau mengupload file baru untuk mengganti.');
                    $('#invoiceStatusInfo').removeClass('alert-light').addClass('alert-success');
                    $('#invoiceDownloadLink').attr('href', invoiceUrl);
                    let ext = invoiceUrl.split('.').pop().toLowerCase().split('?')[0];
                    let previewHtml = '';
                    if (ext === 'pdf') {
                        previewHtml = `<iframe src="${invoiceUrl}" style="width:100%; height:500px; border:none;"></iframe>`;
                    } else if (['jpg', 'jpeg', 'png'].includes(ext)) {
                        previewHtml = `<div style="height:500px; overflow:auto; text-align:center; padding:10px; background:#f8f9fa;"><img src="${invoiceUrl}" style="max-width:100%; max-height:100%; object-fit:contain;" alt="Invoice Preview"></div>`;
                    } else {
                        previewHtml = `<div class="text-center p-5"><i class="bi bi-file-earmark-x fs-1 text-muted"></i><p class="text-muted mt-2">Preview tidak tersedia untuk format ini</p></div>`;
                    }
                    $('#invoicePreviewContainer').html(previewHtml);
                    $('#invoicePreviewWrapper').show();
                } else {
                    $('#invoiceStatusText').text('Belum ada invoice');
                    $('#invoiceStatusDesc').text('Silakan upload file invoice terlebih dahulu.');
                    $('#invoiceStatusInfo').removeClass('alert-success').addClass('alert-light');
                }
                showModal('invoiceModal'); 
            });

            $('#invoiceFileInput').on('change', function() {
                let fileName = $(this).val().split('\\').pop();
                if (fileName) { $('#newFileName').text(fileName); $('#newFilePreview').show(); } 
                else { $('#newFilePreview').hide(); }
            });

            $(document).on('click', '.btn-update-status', function(e) {
                e.preventDefault();
                let id = $(this).data('id');
                let currentStatus = $(this).data('status');
                let url = "{{ route('rencanaPembelian.updateStatus') }}";
                $('#formStatus').attr('action', url);
                $('input[name="id_pembelian"]').val(id);
                $('select[name="status"]').val(currentStatus);
                showModal('statusModal');
            });

            $(function () {
                function toggleAlasanDibatalkan() {
                    const status = $('select[name="status"]').val();

                    if (status === 'Dibatalkan') {
                        $('#alasanDibatalkanWraper').show();
                        $('textarea[name="alasan_dibatalkan"]')
                            .prop('required', true);

                    } else {
                        $('#alasanDibatalkanWraper').hide();
                        $('textarea[name="alasan_dibatalkan"]')
                            .prop('required', false);
                    }
                }

                toggleAlasanDibatalkan();

                $(document).on('change', 'select[name="status"]', function () {
                    toggleAlasanDibatalkan();
                });

            });

            $(document).on('click', '.btn-link-pengajuan', function(e) {
                e.preventDefault();
                e.stopPropagation(); 

                let id = $(this).data('id');
                let tipe = $(this).data('tipe') || 'Umum';
                let periode = $(this).data('periode') || '-';
                let items = $(this).data('items') || [];

                $('#linkIdRencana').val(id);
                $('#linkPeriode').text(periode);
                $('#linkTipe').text(tipe);
                $('#linkTotalItem').text(items.length);

                let $tbody = $('#linkItemsBody');
                $tbody.empty();

                if (items.length === 0) {
                    $tbody.html(`
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Tidak ada item barang pada rencana ini
                            </td>
                        </tr>
                    `);
                    $('#linkBadgeCount').text('0 item');
                } else {
                    items.forEach(function(item, index) {
                        let hargaFormatted = item.harga ? 'Rp ' + parseInt(item.harga).toLocaleString('id-ID') : 'Rp 0';
                        let row = `
                            <tr>
                                <td>${index + 1}</td>
                                <td>
                                    ${item.nama_barang || '-'}
                                    <input type="hidden" name="barang[nama_barang][]" value="${item.nama_barang || ''}">
                                </td>
                                <td>
                                    <input type="number" name="barang[qty][]" class="form-control form-control-sm"
                                        value="${item.qty || 1}" min="1" required>
                                </td>
                                <td>
                                    <input type="text" name="barang[harga_barang][]" class="form-control form-control-sm"
                                        value="${item.harga || 0}" required>
                                </td>
                                <td>
                                    <input type="text" name="barang[keterangan][]" class="form-control form-control-sm"
                                        value="${item.keterangan || ''}">
                                </td>
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input item-check"
                                        data-index="${index}" checked>
                                </td>
                            </tr>
                        `;
                        $tbody.append(row);
                    });
                    $('#linkBadgeCount').text(items.length + ' item');
                }

                let modalEl = document.getElementById('linkPengajuanModal');
                let modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            });

            $(document).on('change', '.item-check', function() {
                let index = $(this).data('index');
                let $row = $(this).closest('tr');

                if ($(this).is(':checked')) {
                    $row.find('input[type="hidden"], input[type="number"], input[type="text"]').prop('disabled', false);
                    $row.removeClass('text-muted').css('opacity', '1');
                } else {
                    $row.find('input[type="hidden"], input[type="number"], input[type="text"]').prop('disabled', true);
                    $row.addClass('text-muted').css('opacity', '0.5');
                }

                let checkedCount = $('.item-check:checked').length;
                $('#linkBadgeCount').text(checkedCount + ' item');
            });

            $('#formLinkPengajuan').on('submit', function(e) {
                let checkedCount = $('.item-check:checked').length;
                if (checkedCount === 0) {
                    e.preventDefault();
                    alert('Pilih minimal 1 barang yang akan di-link!');
                    return false;
                }

                let idKaryawan = $('#linkIdKaryawan').val().trim();
                if (!idKaryawan) {
                    e.preventDefault();
                    alert('ID Karyawan wajib diisi!');
                    return false;
                }
            });
        });
        // Rencana pembelian script
    </script>
@endsection
