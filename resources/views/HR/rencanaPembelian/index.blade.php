@extends('layout_HR.app')

@section('content_HR')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Manajemen Rencana Pembelian HR</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#formModal" id="btnCreate">
            <i class="bi bi-plus-circle"></i> Tambah Rencana Baru
        </button>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

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
                                <th width="40"></th><th width="50">No</th><th>No. KK</th><th>Tanggal</th>
                                <th>Total Item</th><th>Total Estimasi Harga</th><th>Status</th><th width="250" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rencanas as $index => $row)
                            <tr class="purchase-row" data-bs-toggle="collapse" data-target="#collapseRencana{{ $row->id }}" style="cursor:pointer;">
                                <td class="text-center"><i class="bi bi-chevron-right toggle-icon"></i></td>
                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $row->no_kk }}</strong></td>
                                <td>{{ $row->tanggal_pembelian ? \Carbon\Carbon::parse($row->tanggal_pembelian)->format('d M Y') : '-' }}</td>
                                <td>{{ $row->details->count() }} Item</td>
                                <td>Rp {{ number_format($row->details->sum(fn($d) => $d->qty * $d->harga), 0, ',', '.') }}</td>
                                <td><span class="badge bg-warning text-dark">{{ $row->status_pembelian }}</span></td>
                                <td class="text-center action-cell">
                                    <button class="btn btn-sm btn-warning btn-edit text-white" 
                                        data-id="{{ $row->id }}" data-nokk="{{ $row->no_kk }}" 
                                        data-tanggal="{{ $row->tanggal_pembelian }}" data-status="{{ $row->status_pembelian }}"
                                        data-items='@json($row->details)'>Edit</button>
                                    <button class="btn btn-sm btn-primary btn-upload-invoice" data-invoice="{{ $row->invoice ? asset('storage/'.$row->invoice) : '' }}" data-nokk="{{ $row->no_kk }}" data-id="{{ $row->id }}">Invoice</button>
                                    <button class="btn btn-sm btn-secondary btn-update-status" data-id="{{ $row->id }}" data-status="{{ $row->status_pembelian }}">Status</button>
                                    <button class="btn btn-sm btn-danger btn-delete" data-id="{{ $row->id }}">Hapus</button>
                                </td>
                            </tr>
                            <tr class="collapse" id="collapseRencana{{ $row->id }}" data-bs-parent="#accordion-rencana">
                                <td colspan="8" class="bg-light p-3 collapse-row-content">
                                    <ul class="nav nav-tabs mb-3" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#barang-r-{{ $row->id }}" type="button"><i class="bi bi-box-seam"></i> Detail Barang</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tracking-r-{{ $row->id }}" type="button"><i class="bi bi-clock-history"></i> Riwayat Tracking</button>
                                        </li>
                                    </ul>
                                    <div class="tab-content p-3 border border-top-0 rounded-bottom bg-white shadow-sm">
                                        <div class="tab-pane fade show active" id="barang-r-{{ $row->id }}" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered bg-white mb-0">
                                                    <thead class="table-secondary"><tr><th width="40">No</th><th>Nama Barang</th><th>Kategori</th><th width="80">Qty</th><th width="130">Harga Satuan</th><th width="130">Subtotal</th><th>Keterangan</th></tr></thead>
                                                    <tbody>
                                                        @forelse($row->details as $i => $item)
                                                        <tr>
                                                            <td>{{ $i + 1 }}</td><td>{{ $item->nama_barang }}</td>
                                                            <td><span class="badge bg-secondary">{{ $item->kategori ?? '-' }}</span></td>
                                                            <td class="text-center">{{ $item->qty }}</td>
                                                            <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                                            <td>Rp {{ number_format($item->qty * $item->harga, 0, ',', '.') }}</td>
                                                            <td>{{ $item->keterangan ?? '-' }}</td>
                                                        </tr>
                                                        @empty
                                                        <tr><td colspan="7" class="text-center text-muted">Tidak ada item</td></tr>
                                                        @endforelse
                                                    </tbody>
                                                    <tfoot class="table-light"><tr><th colspan="5" class="text-end">Total:</th><th class="text-end">Rp {{ number_format($row->details->sum(fn($d) => $d->qty * $d->harga), 0, ',', '.') }}</th><th></th></tr></tfoot>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="tracking-r-{{ $row->id }}" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered table-hover bg-white mb-0">
                                                    <thead class="table-secondary"><tr><th width="50">No</th><th>Aktivitas</th><th>Karyawan</th><th width="180">Tanggal</th></tr></thead>
                                                    <tbody>
                                                        @forelse($row->tracking as $i => $item)
                                                        <tr>
                                                            <td class="text-center">{{ $i + 1 }}</td>
                                                            <td>
                                                                @if(str_contains($item->tracking, 'membuat')) <span class="badge bg-success me-1"><i class="bi bi-plus-circle"></i></span>
                                                                @elseif(str_contains($item->tracking, 'merubah') || str_contains($item->tracking, 'mengupdate')) <span class="badge bg-warning me-1"><i class="bi bi-pencil"></i></span>
                                                                @else <span class="badge bg-info me-1"><i class="bi bi-info-circle"></i></span> @endif
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
                                <th width="40"></th><th width="50">No</th><th>No. KK</th><th>Tanggal</th>
                                <th>Total Item</th><th>Total Harga</th><th>Status</th><th width="150" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pembelian as $index => $row)
                            <tr class="purchase-row" data-bs-toggle="collapse" data-target="#collapseTerlaksana{{ $row->id }}" style="cursor:pointer;">
                                <td class="text-center"><i class="bi bi-chevron-right toggle-icon"></i></td>
                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $row->no_kk }}</strong></td>
                                <td>{{ $row->tanggal_pembelian ? \Carbon\Carbon::parse($row->tanggal_pembelian)->format('d M Y') : '-' }}</td>
                                <td>{{ $row->details->count() }} Item</td>
                                <td>Rp {{ number_format($row->details->sum(fn($d) => $d->qty * $d->harga), 0, ',', '.') }}</td>
                                <td><span class="badge bg-success">{{ $row->status_pembelian }}</span></td>
                                <td class="text-center action-cell">
                                    <button class="btn btn-sm btn-primary btn-upload-invoice" data-invoice="{{ $row->invoice ? asset('storage/'.$row->invoice) : '' }}" data-nokk="{{ $row->no_kk }}" data-id="{{ $row->id }}">Invoice</button>
                                </td>
                            </tr>
                            <tr class="collapse" id="collapseTerlaksana{{ $row->id }}" data-bs-parent="#accordion-terlaksana">
                                <td colspan="8" class="bg-light p-3 collapse-row-content">
                                    <ul class="nav nav-tabs mb-3" role="tablist">
                                        <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#barang-t-{{ $row->id }}" type="button"><i class="bi bi-box-seam"></i> Detail Barang</button></li>
                                        <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tracking-t-{{ $row->id }}" type="button"><i class="bi bi-clock-history"></i> Riwayat Tracking</button></li>
                                    </ul>
                                    <div class="tab-content p-3 border border-top-0 rounded-bottom bg-white shadow-sm">
                                        <div class="tab-pane fade show active" id="barang-t-{{ $row->id }}" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered bg-white mb-0">
                                                    <thead class="table-secondary"><tr><th width="40">No</th><th>Nama Barang</th><th>Kategori</th><th width="80">Qty</th><th width="130">Harga Satuan</th><th width="130">Subtotal</th><th>Keterangan</th></tr></thead>
                                                    <tbody>
                                                        @forelse($row->details as $i => $item)
                                                        <tr><td>{{ $i + 1 }}</td><td>{{ $item->nama_barang }}</td><td><span class="badge bg-secondary">{{ $item->kategori ?? '-' }}</span></td><td class="text-center">{{ $item->qty }}</td><td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td><td>Rp {{ number_format($item->qty * $item->harga, 0, ',', '.') }}</td><td>{{ $item->keterangan ?? '-' }}</td></tr>
                                                        @empty
                                                        <tr><td colspan="7" class="text-center text-muted">Tidak ada item</td></tr>
                                                        @endforelse
                                                    </tbody>
                                                    <tfoot class="table-light"><tr><th colspan="5" class="text-end">Total:</th><th class="text-end">Rp {{ number_format($row->details->sum(fn($d) => $d->qty * $d->harga), 0, ',', '.') }}</th><th></th></tr></tfoot>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="tracking-t-{{ $row->id }}" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered table-hover bg-white mb-0">
                                                    <thead class="table-secondary"><tr><th width="50">No</th><th>Aktivitas</th><th>Karyawan</th><th width="180">Tanggal</th></tr></thead>
                                                    <tbody>
                                                        @forelse($row->tracking as $i => $item)
                                                        <tr>
                                                            <td class="text-center">{{ $i + 1 }}</td>
                                                            <td>
                                                                @if(str_contains($item->tracking, 'membuat')) <span class="badge bg-success me-1"><i class="bi bi-plus-circle"></i></span>
                                                                @elseif(str_contains($item->tracking, 'merubah') || str_contains($item->tracking, 'mengupdate')) <span class="badge bg-warning me-1"><i class="bi bi-pencil"></i></span>
                                                                @else <span class="badge bg-info me-1"><i class="bi bi-info-circle"></i></span> @endif
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
                                <th width="40"></th><th width="50">No</th><th>No. KK</th><th>Tanggal</th>
                                <th>Total Item</th><th>Total Harga</th><th>Status</th><th width="100" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($dibatalkan ?? []) as $index => $row)
                            <tr class="purchase-row" data-bs-toggle="collapse" data-target="#collapseDibatalkan{{ $row->id }}" style="cursor:pointer;">
                                <td class="text-center"><i class="bi bi-chevron-right toggle-icon"></i></td>
                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $row->no_kk }}</strong></td>
                                <td>{{ $row->tanggal_pembelian ? \Carbon\Carbon::parse($row->tanggal_pembelian)->format('d M Y') : '-' }}</td>
                                <td>{{ $row->details->count() }} Item</td>
                                <td>Rp {{ number_format($row->details->sum(fn($d) => $d->qty * $d->harga), 0, ',', '.') }}</td>
                                <td><span class="badge bg-danger">{{ $row->status_pembelian }}</span></td>
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
                                                    <thead class="table-secondary"><tr><th width="40">No</th><th>Nama Barang</th><th>Kategori</th><th width="80">Qty</th><th width="130">Harga Satuan</th><th width="130">Subtotal</th><th>Keterangan</th></tr></thead>
                                                    <tbody>
                                                        @forelse($row->details as $i => $item)
                                                        <tr><td>{{ $i + 1 }}</td><td>{{ $item->nama_barang }}</td><td><span class="badge bg-secondary">{{ $item->kategori ?? '-' }}</span></td><td class="text-center">{{ $item->qty }}</td><td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td><td>Rp {{ number_format($item->qty * $item->harga, 0, ',', '.') }}</td><td>{{ $item->keterangan ?? '-' }}</td></tr>
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
                                                                @if(str_contains($item->tracking, 'membuat')) <span class="badge bg-success me-1"><i class="bi bi-plus-circle"></i></span>
                                                                @elseif(str_contains($item->tracking, 'merubah') || str_contains($item->tracking, 'mengupdate')) <span class="badge bg-warning me-1"><i class="bi bi-pencil"></i></span>
                                                                @else <span class="badge bg-info me-1"><i class="bi bi-info-circle"></i></span> @endif
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
                        <div class="col-md-6">
                            <label class="form-label">No. KK / No. Pengajuan <span class="text-danger">*</span></label>
                            <input type="text" name="no_kk" id="no_kk" class="form-control" required>
                        </div>
                        <div class="col-md-6" id="tanggalWrapper" style="display:none;">
                            <label class="form-label">Tanggal Pembelian <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_pembelian" class="form-control">
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 fw-bold">Detail Barang</h6>
                        <button type="button" class="btn btn-sm btn-success" id="btnAddItem"><i class="bi bi-plus-circle"></i> Tambah Item</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle" id="itemsTable">
                            <thead class="table-light"><tr><th>Nama Barang</th><th>Kategori</th><th width="100">Qty</th><th width="180">Harga Satuan</th><th>Keterangan</th><th width="60" class="text-center">#</th></tr></thead>
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
                            <small class="text-muted">Format: PDF, JPG, PNG. Maks: 2MB</small>
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
                    <label class="form-label">Pilih Status Baru</label>
                    <select name="status" class="form-select" required>
                        <option value="Rencana">Rencana</option>
                        <option value="Terlaksana">Terlaksana</option>
                        <option value="Dibatalkan">Dibatalkan</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
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
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
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
    const nextKK = "{{ $nextKK ?? 'KK-0001' }}";

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
                <td><input type="text" name="items[${itemIndex}][kategori]" class="form-control form-control-sm" value="${item.kategori || ''}" required></td>
                <td><input type="number" name="items[${itemIndex}][qty]" class="form-control form-control-sm qty-input" value="${item.qty || 1}" min="1" required></td>
                <td><input type="text" name="items[${itemIndex}][harga_display]" class="form-control form-control-sm harga-display" value="${hargaFormatted}" required><input type="hidden" name="items[${itemIndex}][harga]" class="harga-value" value="${item.harga ? Math.round(parseFloat(item.harga)) : 0}"></td>
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

    $('#btnCreate').click(function() {
        $('#formModalLabel').text('Tambah Rencana Pembelian');
        $('#formPembelian').attr('action', "{{ route('HR.rencanaPembelian.store') }}");
        $('#tanggalWrapper').hide();
        $('#formPembelian')[0].reset();
        $('#itemsBody').empty();
        $('#deleted_ids').val('');
        $('#status_pembelian').val('Rencana');
        $('#no_kk').val(nextKK);
        itemIndex = 0;
        addItemRow(); 
    });

    $(document).on('click', '.btn-edit', function(e) {
        e.preventDefault();
        let id = $(this).data('id');
        let noKk = $(this).data('nokk');
        let tanggal = $(this).data('tanggal');
        let status = $(this).data('status');
        let items = $(this).data('items'); 

        $('#formModalLabel').text('Edit Rencana Pembelian');
        let url = "{{ route('HR.rencanaPembelian.update', ':id') }}";
        $('#formPembelian').attr('action', url.replace(':id', id));
        $('#tanggalWrapper').show();
        $('#no_kk').val(noKk);
        $('#status_pembelian').val(status || 'Rencana');
        $('input[name="tanggal_pembelian"]').val(tanggal ? tanggal.substring(0, 10) : '');
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
        let url = "{{ route('HR.rencanaPembelian.delete', ':id') }}";
        $('#formDelete').attr('action', url.replace(':id', id));
        showModal('deleteModal'); 
    });

    $(document).on('click', '.btn-upload-invoice', function(e) {
        e.preventDefault();
        let id = $(this).data('id');
        let invoiceUrl = $(this).data('invoice');
        let noKk = $(this).data('nokk');
        let url = "{{ route('HR.rencanaPembelian.updateInvoice', ':id') }}";
        
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
        let url = "{{ route('HR.rencanaPembelian.updateStatus') }}";
        $('#formStatus').attr('action', url);
        $('input[name="id_pembelian"]').val(id);
        $('select[name="status"]').val(currentStatus);
        showModal('statusModal'); 
    });

    @if ($errors->any())
        @if ($errors->has('no_kk') || $errors->has('items.*') || $errors->has('tanggal_pembelian'))
            showModal('formModal');
        @endif
    @endif
});
</script>
@endsection