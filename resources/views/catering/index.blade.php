@extends('layouts.app')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
    integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

@section('content')
    <div class="container-fluid">
        <div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="cube">
                    <div class="cube_item cube_x"></div>
                    <div class="cube_item cube_y"></div>
                    <div class="cube_item cube_x"></div>
                    <div class="cube_item cube_z"></div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="d-flex justify-content-end mb-3">
                    <button class="btn btn-md click-primary mx-2" data-bs-toggle="modal"
                        data-bs-target="#createCateringModal">
                        <img src="{{ asset('icon/plus.svg') }}" width="30px"> Tambah Catering
                    </button>
                    <button class="btn btn-md click-primary mx-2" data-bs-toggle="modal"
                        data-bs-target="#createRencanaModal">
                        <img src="{{ asset('icon/plus.svg') }}" width="30px"> Tambah Rencana Pembelian
                    </button>
                </div>

                <ul class="nav nav-tabs mb-3" id="cateringTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="catering-tab" data-bs-toggle="tab" data-bs-target="#catering"
                            type="button" role="tab" onclick="loadData('catering')">Catering</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="rencana-tab" data-bs-toggle="tab" data-bs-target="#rencana"
                            type="button" role="tab" onclick="loadData('rencana')">Rencana Pembelian</button>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="catering" role="tabpanel">
                        <div class="card m-4 p-4">
                            <div class="card-body table-responsive">
                                <h3 class="card-title text-center my-1 mb-3">{{ __('Data Pengajuan Catering') }}</h3>
                                <table class="table" id="cateringTable">
                                    <thead>
                                        <tr>
                                            <th scope="col">No</th>
                                            <th scope="col">Tanggal Pengajuan</th>
                                            <th scope="col">Nama Karyawan</th>
                                            <th scope="col">Divisi</th>
                                            <th scope="col">Tipe</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Detail</th>
                                            <th scope="col">Total</th>
                                            <th scope="col">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cateringContent"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="rencana" role="tabpanel">
                        <div class="card m-4 p-4">
                            <div class="card-body table-responsive">
                                <h3 class="card-title text-center my-1 mb-3">{{ __('Data Rencana Pembelian') }}</h3>
                                <table class="table" id="rencanaTable">
                                    <thead>
                                        <tr>
                                            <th scope="col">No</th>
                                            <th scope="col">Tanggal Pengajuan</th>
                                            <th scope="col">Nama Karyawan</th>
                                            <th scope="col">Divisi</th>
                                            <th scope="col">Tipe</th>
                                            <th scope="col">Status Pembelian</th>
                                            <th scope="col">Tanggal Pembelian</th>
                                            <th scope="col">Detail</th>
                                            <th scope="col">Total</th>
                                            <th scope="col">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="rencanaContent"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createCateringModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pengajuan Catering</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formCatering">
                    @csrf
                    @method('post')
                    <div class="modal-body">
                        <input type="hidden" name="id_karyawan" value="{{ auth()->user()->id }}">
                        <input type="hidden" name="tipe" value="Makanan">

                        <div class="row mb-3">
                            <label class="col-md-4">Nama Karyawan</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control"
                                    value="{{ auth()->user()->karyawan->nama_lengkap ?? '-' }}" disabled>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-md-4">Divisi</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control"
                                    value="{{ auth()->user()->karyawan->divisi ?? '-' }}" disabled>
                            </div>
                        </div>

                        <div id="itemContainerCatering">
                            <div class="item-section mb-3 p-3 border rounded">
                                <div class="row mb-2">
                                    <label class="col-md-4">Nama Makanan</label>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control makanan-input"
                                            name="barang[nama_makanan][0][]" placeholder="Masukkan nama makanan" required>
                                        <button type="button" class="btn btn-sm btn-secondary mt-1 add-makanan"
                                            data-index="0">+ Tambah Makanan</button>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-md-4">Jumlah</label>
                                    <div class="col-md-8">
                                        <input type="number" class="form-control qty-input" name="barang[qty][]"
                                            min="1" required>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-md-4">Harga (Rp.)</label>
                                    <div class="col-md-8">
                                        <div class="input-group">
                                            <span class="input-group-text">Rp.</span>
                                            <input type="text" class="form-control harga-input" name="barang[harga][]"
                                                required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-md-4">Tipe Catering</label>
                                    <div class="col-md-8">
                                        <select class="form-control tipe-detail-select" name="barang[tipe_detail][]"
                                            required>
                                            <option value="">Pilih Tipe</option>
                                            <option value="Coffee Break">Coffee Break</option>
                                            <option value="Makan Siang">Makan Siang</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-md-4">Vendor</label>
                                    <div class="col-md-8">
                                        <select class="form-control vendor-select" name="barang[vendor][]" required>
                                            <option value="">Pilih tipe dulu</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-md-4">Keterangan</label>
                                    <div class="col-md-8">
                                        <textarea class="form-control" name="barang[keterangan][]"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary" id="addItemCatering">+ Tambah Item</button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Ajukan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createRencanaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Rencana Pembelian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formRencana">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="id_karyawan" value="{{ auth()->user()->id }}">
                        <input type="hidden" name="tipe" value="Makanan">

                        <div class="row mb-3">
                            <label class="col-md-4">Nama Karyawan</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control"
                                    value="{{ auth()->user()->karyawan->nama_lengkap ?? '-' }}" disabled>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-md-4">Divisi</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control"
                                    value="{{ auth()->user()->karyawan->divisi ?? '-' }}" disabled>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-md-4">Status Pembelian</label>
                            <div class="col-md-8">
                                <input type="text" readonly value="Rencana Pembelian" class="form-control"
                                    name="status_pembelian" placeholder="Contoh: Persiapan Event Q3" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-md-4">Tanggal Pembelian</label>
                            <div class="col-md-8">
                                <input type="date" class="form-control" name="tanggal_pembelian"
                                    id="tanggalPembelian" required>
                                <small class="text-muted">Tidak boleh di minggu ini atau tanggal yang sudah lewat</small>
                            </div>
                        </div>

                        <div id="itemContainerRencana">
                            <div class="item-section mb-3 p-3 border rounded">
                                <div class="row mb-2">
                                    <label class="col-md-4">Nama Makanan</label>
                                    <div class="col-md-8">
                                        <button type="button"
                                            class="btn btn-sm btn-secondary mb-2 text-end mt-1 add-makanan"
                                            data-index="0">+ Tambah Makanan</button>
                                        <input type="text" class="form-control makanan-input"
                                            name="barang[nama_makanan][0][]" placeholder="Masukkan nama makanan" required>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-md-4">Jumlah</label>
                                    <div class="col-md-8">
                                        <input type="number" class="form-control qty-input" name="barang[qty][]"
                                            min="1" required>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-md-4">Harga (Rp.)</label>
                                    <div class="col-md-8">
                                        <div class="input-group">
                                            <span class="input-group-text">Rp.</span>
                                            <input type="text" class="form-control harga-input" name="barang[harga][]"
                                                required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-md-4">Tipe Catering</label>
                                    <div class="col-md-8">
                                        <select class="form-control tipe-detail-select" name="barang[tipe_detail][]"
                                            required>
                                            <option value="">Pilih Tipe</option>
                                            <option value="Coffee Break">Coffee Break</option>
                                            <option value="Makan Siang">Makan Siang</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-md-4">Vendor</label>
                                    <div class="col-md-8">
                                        <select class="form-control vendor-select" name="barang[vendor][]" required>
                                            <option value="">Pilih tipe dulu</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-md-4">Keterangan</label>
                                    <div class="col-md-8">
                                        <textarea class="form-control" name="barang[keterangan][]"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary" id="addItemRencana">+ Tambah Item</button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Ajukan Rencana</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">

                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">Detail Pengajuan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body bg-body-tertiary" id="detailContent">
                    <!-- dynamic content -->
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        Tutup
                    </button>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Pengajuan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEdit">
                    @csrf
                    @method('POST')
                    <input type="hidden" name="id" id="editId">
                    <div class="modal-body">
                        <div id="editItemsContainer"></div>
                        <button type="button" class="btn btn-primary mt-2" id="addEditItem">+ Tambah Item</button>
                        <input type="hidden" name="deleted_ids" id="deletedIds">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="invoiceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Invoice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formInvoice" enctype="multipart/form-data">
                    @csrf
                    @method('POST')
                    <input type="hidden" name="id" id="invoiceId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">File Invoice</label>
                            <input type="file" class="form-control" name="invoice" accept=".pdf,.jpg,.jpeg,.png"
                                required>
                        </div>
                        <div id="currentInvoice"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="statusForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status_input" id="statusInput">
                    <input type="hidden" name="id_catering" id="statusId">
                    <div class="modal-header">
                        <h5 class="modal-title">Konfirmasi Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @if (auth()->user()->jabatan === 'Finance & Accounting')
                            <div class="mb-3">
                                <select name="status_finance" class="form-select">
                                    <option value="Sedang Dikonfirmasi oleh Bagian Finance kepada General Manager">Sedang
                                        Dikonfirmasi oleh Bagian Finance kepada General Manager</option>
                                    <option value="Sedang Dikonfirmasi oleh Bagian Finance kepada Direksi">Sedang
                                        Dikonfirmasi oleh Bagian Finance kepada Direksi</option>
                                    <option value="Finance Menunggu Approve Direksi">Finance Menunggu Approve Direksi
                                    </option>
                                    <option value="Membuat Permintaan Ke Direktur Utama">Membuat Permintaan Ke Direktur
                                        Utama</option>
                                    <option value="Pengajuan sedang dalam proses Pencairan">Pengajuan sedang dalam proses
                                        Pencairan</option>
                                    <option value="Pencairan Sudah Selesai">Pencairan Sudah Selesai</option>
                                    <option value="Selesai">Selesai</option>
                                </select>
                            </div>
                        @endif
                        <div class="btn-group mb-3">
                            <button type="button" class="btn btn-outline-primary" data-status="1">Ya</button>
                            <button type="button" class="btn btn-outline-danger" data-status="0">Tidak</button>
                        </div>
                        <div class="mb-3" id="keteranganContainer" style="display:none;">
                            <label class="form-label">Keterangan</label>
                            <textarea class="form-control" name="keterangan" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Kirim</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .detail-cell {
            vertical-align: top;
        }

        .detail-item {
            border-bottom: 1px solid #eee;
            padding: 4px 0;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .makanan-list {
            margin-top: 5px;
        }

        .makanan-tag {
            display: inline-block;
            background: #e9ecef;
            padding: 3px 8px;
            border-radius: 4px;
            margin: 2px;
            font-size: 12px;
        }
    </style>

    @push('js')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
        <script>
            let cateringTable, rencanaTable;
            let vendorCB = [],
                vendorMS = [];

            $(document).ready(function() {
                loadVendors();
                loadData('catering');
                setupRupiahFormatter();
                setupDateValidation();
            });

            function loadVendors() {
                $.get("{{ route('catering.getVendors') }}", {
                    tipe: 'Coffee Break'
                }, function(data) {
                    vendorCB = data;
                });
                $.get("{{ route('catering.getVendors') }}", {
                    tipe: 'Makan Siang'
                }, function(data) {
                    vendorMS = data;
                });
            }

            function loadData(type) {
                const tableId = type === 'catering' ? '#cateringContent' : '#rencanaContent';
                const tableVar = type === 'catering' ? 'cateringTable' : 'rencanaTable';

                $(tableId).html('<tr><td colspan="10" class="text-center">Loading...</td></tr>');

                $.get("{{ route('catering.get') }}", {
                    type: type
                }, function(response) {
                    $(tableId).empty();

                    if (response.length === 0) {
                        $(tableId).html('<tr><td colspan="10" class="text-center text-muted">Tidak ada data</td></tr>');
                        return;
                    }

                    response.forEach(function(item, index) {
                        let detailHtml = '';
                        item.detail.forEach(function(d) {
                            const namaMakanan = Array.isArray(d.nama_makanan) ? d.nama_makanan.join(
                                ', ') : d.nama_makanan;
                            const formattedHarga = new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR'
                            }).format(d.harga);
                            detailHtml +=
                                `<div class="detail-item"><strong>${namaMakanan}</strong><br>Qty: ${d.jumlah} | ${formattedHarga} | ${d.vendor} | ${d.tipe_detail}${d.keterangan ? `<br><small>${d.keterangan}</small>` : ''}</div>`;
                        });

                        const formattedTotal = new Intl.NumberFormat('id-ID', {
                            style: 'currency',
                            currency: 'IDR'
                        }).format(item.total_harga);
                        const userJabatan = "{{ auth()->user()->jabatan }}".trim();
                        const isFinal = item.tracking === 'Selesai' || item.tracking === 'Ditolak';
                        const isFinance = userJabatan === 'Finance & Accounting';

                        let actions = `
                <div class="dropdown">
                    <button class="btn btn-sm dropdown-toggle" data-bs-toggle="dropdown">Aksi</button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="showDetail(${item.id})">Detail</a></li>
                        <li><a class="dropdown-item" href="#" onclick="showEditModal(${item.id})">Edit</a></li>
                        <li><a class="dropdown-item" href="#" onclick="showInvoiceModal(${item.id}, '${item.invoice}')">Invoice</a></li>
            `;

                        if (item.is_rencana && !isFinal) {
                            actions +=
                                `<li><a class="dropdown-item text-success" href="#" onclick="upgradeToCatering(${item.id})">Upgrade ke Catering</a></li>`;
                        }

                        if (isFinance && !isFinal) {
                            actions +=
                                `<li><a class="dropdown-item" href="#" onclick="openStatusModal(${item.id}, '${item.tracking}')">Approved</a></li>`;
                        }

                        actions += `
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteData(${item.id})">Hapus</a></li>
                            </ul>
                        </div>`;

                        let row = `<tr>
                            <td>${index + 1}</td>
                            <td>${item.tanggal_pengajuan}</td>
                            <td>${item.nama_karyawan}</td>
                            <td>${item.divisi}</td>
                            <td>${item.tipe}</td>
                        `;

                        if (type === 'rencana') {
                            row += `<td>${item.status_pembelian || '-'}</td><td>${item.tanggal_pembelian}</td>`;
                        } else {
                            row += `<td>${item.tracking}</td><td></td>`;
                        }

                        row +=
                            `<td class="detail-cell">${detailHtml}</td><td>${formattedTotal}</td><td>${actions}</td></tr>`;
                        $(tableId).append(row);
                    });

                    if (window[tableVar]) {
                        window[tableVar].destroy();
                    }
                    window[tableVar] = $(type === 'catering' ? '#cateringTable' : '#rencanaTable').DataTable({
                        pageLength: 10,
                        searching: true,
                        ordering: true,
                        autoWidth: false,
                        responsive: true
                    });
                });
            }

            function setupRupiahFormatter() {
                $(document).on('input', '.harga-input', function() {
                    let val = $(this).val().replace(/[^0-9]/g, '');
                    $(this).val(val ? 'Rp. ' + parseInt(val).toLocaleString('id-ID') : '');
                });
            }

            function setupDateValidation() {
                const today = new Date();
                const nextWeek = new Date();
                nextWeek.setDate(today.getDate() + 7);

                $('#tanggalPembelian').attr('min', nextWeek.toISOString().split('T')[0]);

                $('#tanggalPembelian').on('change', function() {
                    const selected = new Date($(this).val());

                    const today = new Date();
                    today.setHours(0, 0, 0, 0); 

                    if (selected < today) {
                        Swal.fire('Error', 'Tanggal tidak boleh sebelum hari ini', 'error');
                        $(this).val('');
                    }
                });
            }

            function populateVendorOptions($select, tipe, selectedId = null) {
                const vendors = tipe === 'Coffee Break' ? vendorCB : vendorMS;
                $select.empty().append('<option value="">Pilih vendor</option>');
                vendors.forEach(function(v) {
                    const sel = v.id == selectedId ? 'selected' : '';
                    $select.append(`<option value="${v.id}" ${sel}>${v.nama}</option>`);
                });
            }

            $(document).on('change', '.tipe-detail-select', function() {
                const $select = $(this).closest('.item-section').find('.vendor-select');
                populateVendorOptions($select, $(this).val());
            });

            $(document).on('click', '.add-makanan', function() {
                const index = $(this).data('index');
                const $container = $(this).closest('.col-md-8');
                $container.append(
                    `<input type="text" class="form-control makanan-input mt-1" name="barang[nama_makanan][${index}][]" placeholder="Nama makanan tambahan">`
                );
            });

            $('#addItemCatering').click(function() {
                const newIndex = $('#itemContainerCatering .item-section').length;
                const html = `
                    <div class="item-section mb-3 p-3 border rounded">
                        <div class="row mb-2">
                            <label class="col-md-4">Nama Makanan</label>
                            <div class="col-md-8">
                                <button type="button" class="btn btn-sm btn-secondary mt-1 mb-2 add-makanan" data-index="${newIndex}">+ Tambah Makanan</button>
                                <input type="text" class="form-control makanan-input" name="barang[nama_makanan][${newIndex}][]" required>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-md-4">Jumlah</label>
                            <div class="col-md-8"><input type="number" class="form-control qty-input" name="barang[qty][]" min="1" required></div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-md-4">Harga (Rp.)</label>
                            <div class="col-md-8">
                                <div class="input-group"><span class="input-group-text">Rp.</span><input type="text" class="form-control harga-input" name="barang[harga][]" required></div>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-md-4">Tipe Catering</label>
                            <div class="col-md-8">
                                <select class="form-control tipe-detail-select" name="barang[tipe_detail][]" required>
                                    <option value="">Pilih Tipe</option>
                                    <option value="Coffee Break">Coffee Break</option>
                                    <option value="Makan Siang">Makan Siang</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-md-4">Vendor</label>
                            <div class="col-md-8"><select class="form-control vendor-select" name="barang[vendor][]" required><option value="">Pilih tipe dulu</option></select></div>
                        </div>
                        <div class="row"><label class="col-md-4">Keterangan</label><div class="col-md-8"><textarea class="form-control" name="barang[keterangan][]"></textarea></div></div>
                    </div>`;
                $('#itemContainerCatering').append(html);
            });

            $('#addItemRencana').click(function() {
                const newIndex = $('#itemContainerRencana .item-section').length;
                const html = `
                    <div class="item-section mb-3 p-3 border rounded">
                        <div class="row mb-2">
                            <label class="col-md-4">Nama Makanan</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control makanan-input" name="barang[nama_makanan][${newIndex}][]" required>
                                <button type="button" class="btn btn-sm btn-secondary mt-1 add-makanan" data-index="${newIndex}">+ Tambah Makanan</button>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-md-4">Jumlah</label>
                            <div class="col-md-8"><input type="number" class="form-control qty-input" name="barang[qty][]" min="1" required></div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-md-4">Harga (Rp.)</label>
                            <div class="col-md-8">
                                <div class="input-group"><span class="input-group-text">Rp.</span><input type="text" class="form-control harga-input" name="barang[harga][]" required></div>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-md-4">Tipe Catering</label>
                            <div class="col-md-8">
                                <select class="form-control tipe-detail-select" name="barang[tipe_detail][]" required>
                                    <option value="">Pilih Tipe</option>
                                    <option value="Coffee Break">Coffee Break</option>
                                    <option value="Makan Siang">Makan Siang</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-md-4">Vendor</label>
                            <div class="col-md-8"><select class="form-control vendor-select" name="barang[vendor][]" required><option value="">Pilih tipe dulu</option></select></div>
                        </div>
                        <div class="row"><label class="col-md-4">Keterangan</label><div class="col-md-8"><textarea class="form-control" name="barang[keterangan][]"></textarea></div></div>
                    </div>`;
                $('#itemContainerRencana').append(html);
            });

            function showDetail(id) {
                const url = "{{ route('catering.detail', ['id' => ':id']) }}".replace(':id', id);

                $.get(url, function(item) {

                    let detailHtml = `
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th>Qty</th>
                                        <th>Harga</th>
                                        <th>Vendor</th>
                                        <th>Tipe</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    item.detail.forEach(function(d) {
                        const namaMakanan = Array.isArray(d.nama_makanan)
                            ? d.nama_makanan.join('<br>')
                            : d.nama_makanan;

                        const formattedHarga = new Intl.NumberFormat('id-ID', {
                            style: 'currency',
                            currency: 'IDR'
                        }).format(d.harga);

                        detailHtml += `
                            <tr>
                                <td>${namaMakanan}</td>
                                <td>${d.jumlah}</td>
                                <td>${formattedHarga}</td>
                                <td>${d.vendor}</td>
                                <td>${d.tipe_detail}</td>
                            </tr>
                        `;
                    });

                    detailHtml += `
                                </tbody>
                                <tfoot>
                                    <tr class="table-light">
                                        <th colspan="2">Total</th>
                                        <th colspan="3">
                                            ${new Intl.NumberFormat('id-ID', {
                                                style: 'currency',
                                                currency: 'IDR'
                                            }).format(item.total_harga)}
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    `;

                    let trackingHtml = `
                        <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    item.tracking_history?.forEach(function(t) {
                        trackingHtml += `
                            <tr>
                                <td>${t.tanggal}</td>
                                <td><span class="badge bg-info text-dark">${t.tracking}</span></td>
                                <td>${t.keterangan || '-'}</td>
                            </tr>
                        `;
                    });

                    trackingHtml += `
                                </tbody>
                            </table>
                        </div>
                    `;

                    let content = `
                        <div class="container-fluid">

                            <div class="row g-3">
                                
                                <!-- LEFT INFO -->
                                <div class="col-md-4">
                                    <div class="card shadow-sm border-0 h-100">
                                        <div class="card-body small">

                                            <div class="mb-2"><strong>Nama:</strong><br>${item.nama_karyawan}</div>
                                            <div class="mb-2"><strong>Divisi:</strong><br>${item.divisi}</div>
                                            <div class="mb-2"><strong>Jabatan:</strong><br>${item.jabatan}</div>
                                            <div class="mb-2"><strong>Tipe:</strong><br>${item.tipe}</div>

                                            <div class="mb-2">
                                                <strong>Status:</strong><br>
                                                <span class="badge bg-primary">${item.tracking}</span>
                                            </div>

                                            ${item.is_rencana || item.status_pembelian ? `
                                                <hr>
                                                <div class="mb-2"><strong>Status Pembelian:</strong><br>${item.status_pembelian || '-'}</div>
                                                <div class="mb-2"><strong>Tanggal:</strong><br>${item.tanggal_pembelian || '-'}</div>
                                            ` : ''}

                                            ${item.invoice ? `
                                                <hr>
                                                <a href="/storage/${item.invoice}" target="_blank" class="btn btn-sm btn-outline-primary w-100">
                                                    Lihat Invoice
                                                </a>
                                            ` : ''}

                                        </div>
                                    </div>
                                </div>

                                <!-- RIGHT DETAIL -->
                                <div class="col-md-8">
                                    <div class="card shadow-sm border-0 mb-3">
                                        <div class="card-body">
                                            <h6 class="fw-bold mb-3">Detail Item</h6>
                                            ${detailHtml}
                                        </div>
                                    </div>

                                    <div class="card shadow-sm border-0">
                                        <div class="card-body">
                                            <h6 class="fw-bold mb-3">Tracking</h6>
                                            ${trackingHtml}
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>
                    `;

                    $('#detailContent').html(content);
                    var modal = new bootstrap.Modal(document.getElementById('detailModal'));
                    modal.show();

                }).fail(function() {
                    Swal.fire('Error', 'Gagal memuat detail', 'error');
                });
            }

            $('#formCatering').on('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                $('.harga-input').each(function() {
                    const clean = $(this).val().replace(/[^0-9]/g, '');
                    $(this).val(clean);
                });

                const items = [];
                $('.item-section').each(function(idx) {
                    const makananArr = [];
                    $(this).find(`input[name="barang[nama_makanan][${idx}][]"]`).each(function() {
                        if ($(this).val().trim()) makananArr.push($(this).val().trim());
                    });
                    if (makananArr.length > 0) {
                        items.push({
                            vendor: $(this).find('select[name="barang[vendor][]"]').val(),
                            tipe_detail: $(this).find('select[name="barang[tipe_detail][]"]').val(),
                            nama_makanan: makananArr,
                            qty: $(this).find('input[name="barang[qty][]"]').val(),
                            harga: $(this).find('input[name="barang[harga][]"]').val().replace(
                                /[^0-9]/g, ''),
                            keterangan: $(this).find('textarea[name="barang[keterangan][]"]').val()
                        });
                    }
                });

                items.forEach((item, i) => {
                    formData.append(`barang[vendor][${i}]`, item.vendor);
                    formData.append(`barang[tipe_detail][${i}]`, item.tipe_detail);
                    formData.append(`barang[qty][${i}]`, item.qty);
                    formData.append(`barang[harga][${i}]`, item.harga);
                    formData.append(`barang[keterangan][${i}]`, item.keterangan);
                    item.nama_makanan.forEach((m, j) => {
                        formData.append(`barang[nama_makanan][${i}][${j}]`, m);
                    });
                });

                $.ajax({
                    url: "{{ route('catering.store') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        $('#createCateringModal').modal('hide');
                        $('#formCatering')[0].reset();
                        loadData('catering');
                        Swal.fire('Berhasil', res.success, 'success');
                    },
                    error: function(xhr) {
                        const err = xhr.responseJSON?.error || 'Terjadi kesalahan';
                        Swal.fire('Error', err, 'error');
                    }
                });
            });

            $('#formRencana').on('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                $('.harga-input').each(function() {
                    const clean = $(this).val().replace(/[^0-9]/g, '');
                    $(this).val(clean);
                });

                const items = [];
                $('.item-section').each(function(idx) {
                    const makananArr = [];
                    $(this).find(`input[name="barang[nama_makanan][${idx}][]"]`).each(function() {
                        if ($(this).val().trim()) makananArr.push($(this).val().trim());
                    });
                    if (makananArr.length > 0) {
                        items.push({
                            vendor: $(this).find('select[name="barang[vendor][]"]').val(),
                            tipe_detail: $(this).find('select[name="barang[tipe_detail][]"]').val(),
                            nama_makanan: makananArr,
                            qty: $(this).find('input[name="barang[qty][]"]').val(),
                            harga: $(this).find('input[name="barang[harga][]"]').val().replace(
                                /[^0-9]/g, ''),
                            keterangan: $(this).find('textarea[name="barang[keterangan][]"]').val()
                        });
                    }
                });

                items.forEach((item, i) => {
                    formData.append(`barang[vendor][${i}]`, item.vendor);
                    formData.append(`barang[tipe_detail][${i}]`, item.tipe_detail);
                    formData.append(`barang[qty][${i}]`, item.qty);
                    formData.append(`barang[harga][${i}]`, item.harga);
                    formData.append(`barang[keterangan][${i}]`, item.keterangan);
                    item.nama_makanan.forEach((m, j) => {
                        formData.append(`barang[nama_makanan][${i}][${j}]`, m);
                    });
                });

                $.ajax({
                    url: "{{ route('catering.store') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        $('#createRencanaModal').modal('hide');
                        $('#formRencana')[0].reset();
                        loadData('rencana');
                        Swal.fire('Berhasil', res.success, 'success');
                    },
                    error: function(xhr) {
                        const err = xhr.responseJSON?.error || 'Terjadi kesalahan';
                        Swal.fire('Error', err, 'error');
                    }
                });
            });

            function showEditModal(id) {
                $.get("{{ route('catering.get') }}", function(response) {
                    const item = response.find(d => d.id == id);
                    if (!item) return;

                    $('#editId').val(item.id);
                    $('#editItemsContainer').empty();

                    item.detail.forEach(function(d, idx) {
                        const namaMakananArr = Array.isArray(d.nama_makanan) ? d.nama_makanan : [d
                            .nama_makanan
                        ];
                        let makananInputs = '';
                        namaMakananArr.forEach((m, i) => {
                            makananInputs +=
                                `<input type="text" class="form-control makanan-input mb-1" name="nama_makanan[${idx}][]" value="${m}" ${i === 0 ? 'required' : ''}>`;
                        });

                        const html = `
                            <div class="item-section mb-3 p-3 border rounded" data-detail-id="${d.id}">
                                <input type="hidden" name="id_detail_catering[]" value="${d.id}">
                                <div class="row mb-2">
                                    <label class="col-md-4">Nama Makanan</label>
                                    <div class="col-md-8">
                                        ${makananInputs}
                                        <button type="button" class="btn btn-sm btn-secondary mt-1 add-makanan-edit" data-index="${idx}">+ Tambah Makanan</button>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-md-4">Jumlah</label>
                                    <div class="col-md-8"><input type="number" class="form-control" name="qty[]" value="${d.jumlah}" min="1" required></div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-md-4">Harga (Rp.)</label>
                                    <div class="col-md-8">
                                        <div class="input-group">
                                            <span class="input-group-text">Rp.</span>
                                            <input type="text" class="form-control harga-input edit-harga" name="harga[]" value="${parseInt(d.harga)}" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-md-4">Tipe Catering</label>
                                    <div class="col-md-8">
                                        <select class="form-control tipe-detail-select" name="tipe_detail[]" required>
                                            <option value="Coffee Break" ${d.tipe_detail === 'Coffee Break' ? 'selected' : ''}>Coffee Break</option>
                                            <option value="Makan Siang" ${d.tipe_detail === 'Makan Siang' ? 'selected' : ''}>Makan Siang</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-md-4">Vendor</label>
                                    <div class="col-md-8"><select class="form-control vendor-select" name="vendor[]" required></select></div>
                                </div>
                                <div class="row"><label class="col-md-4">Keterangan</label><div class="col-md-8"><textarea class="form-control" name="keterangan[]">${d.keterangan || ''}</textarea></div></div>
                                <button type="button" class="btn btn-danger btn-sm mt-2 delete-edit-item" data-id="${d.id}">Hapus Item</button>
                            </div>`;
                        $('#editItemsContainer').append(html);
                        populateVendorOptions($(`.item-section[data-detail-id="${d.id}"] .vendor-select`), d
                            .tipe_detail, d.id_vendor);
                    });

                    $('#editModal').modal('show');
                });
            }

            $(document).on('click', '.add-makanan-edit', function() {
                const index = $(this).data('index');
                $(this).before(
                    `<input type="text" class="form-control makanan-input mb-1" name="nama_makanan[${index}][]" placeholder="Nama makanan tambahan">`
                );
            });

            $(document).on('click', '.delete-edit-item', function() {
                const id = $(this).data('id');
                let deleted = $('#deletedIds').val() ? $('#deletedIds').val().split(',') : [];
                deleted.push(id);
                $('#deletedIds').val(deleted.join(','));
                $(this).closest('.item-section').remove();
            });

            $('#addEditItem').click(function() {
                const newIndex = $('#editItemsContainer .item-section').length;
                const html = `
    <div class="item-section mb-3 p-3 border rounded new-item">
        <div class="row mb-2">
            <label class="col-md-4">Nama Makanan</label>
            <div class="col-md-8">
                <input type="text" class="form-control makanan-input" name="nama_makanan[${newIndex}][]" required>
                <button type="button" class="btn btn-sm btn-secondary mt-1 add-makanan-edit" data-index="${newIndex}">+ Tambah Makanan</button>
            </div>
        </div>
        <div class="row mb-2">
            <label class="col-md-4">Jumlah</label>
            <div class="col-md-8"><input type="number" class="form-control" name="qty[]" min="1" required></div>
        </div>
        <div class="row mb-2">
            <label class="col-md-4">Harga (Rp.)</label>
            <div class="col-md-8">
                <div class="input-group"><span class="input-group-text">Rp.</span><input type="text" class="form-control harga-input edit-harga" name="harga[]" required></div>
            </div>
        </div>
        <div class="row mb-2">
            <label class="col-md-4">Tipe Catering</label>
            <div class="col-md-8">
                <select class="form-control tipe-detail-select" name="tipe_detail[]" required>
                    <option value="">Pilih Tipe</option>
                    <option value="Coffee Break">Coffee Break</option>
                    <option value="Makan Siang">Makan Siang</option>
                </select>
            </div>
        </div>
        <div class="row mb-2">
            <label class="col-md-4">Vendor</label>
            <div class="col-md-8"><select class="form-control vendor-select" name="vendor[]" required><option value="">Pilih tipe dulu</option></select></div>
        </div>
        <div class="row"><label class="col-md-4">Keterangan</label><div class="col-md-8"><textarea class="form-control" name="keterangan[]"></textarea></div></div>
    </div>`;
                $('#editItemsContainer').append(html);
            });

            $('#formEdit').on('submit', function(e) {
                e.preventDefault();

                const formData = new FormData();

                $('#editItemsContainer .item-section').each(function(idx) {
                    const makananArr = [];

                    $(this).find('input[name^="nama_makanan"]').each(function() {
                        if ($(this).val().trim()) {
                            makananArr.push($(this).val().trim());
                        }
                    });

                    if (makananArr.length > 0) {

                        const hargaClean = $(this).find('input[name="harga[]"]').val().replace(/[^0-9]/g, '');

                        const idDetail = $(this).find('input[name="id_detail_catering[]"]').val();

                        if (idDetail) {
                            formData.append(`id_detail_catering[${idx}]`, idDetail);
                        }

                        formData.append(`vendor[${idx}]`, $(this).find('select[name="vendor[]"]').val());
                        formData.append(`tipe_detail[${idx}]`, $(this).find('select[name="tipe_detail[]"]').val());
                        formData.append(`qty[${idx}]`, $(this).find('input[name="qty[]"]').val());
                        formData.append(`harga[${idx}]`, hargaClean);
                        formData.append(`keterangan[${idx}]`, $(this).find('textarea[name="keterangan[]"]').val());

                        makananArr.forEach((m, j) => {
                            formData.append(`nama_makanan[${idx}][${j}]`, m);
                        });
                    }
                });

                formData.append('_method', 'POST'); // atau PUT kalau route pakai PUT

                const url = "{{ route('catering.update', ['id' => ':id']) }}".replace(':id', $('#editId').val());

                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        $('#editModal').modal('hide');
                        loadData($('#catering-tab').hasClass('active') ? 'catering' : 'rencana');
                        Swal.fire('Berhasil', res.success, 'success');
                    },
                    error: function(xhr) {
                        console.log(xhr.responseJSON); // debug penting
                        Swal.fire('Error', 'Cek console', 'error');
                    }
                });
            });

            function showInvoiceModal(id, currentInvoice) {
                $('#invoiceId').val(id);
                $('#currentInvoice').html(currentInvoice ?
                    `<p class="mb-2"><small>Invoice saat ini:</small><br><a href="/storage/${currentInvoice}" target="_blank" class="btn btn-sm btn-outline-primary">Lihat</a></p>` :
                    '');
                $('#formInvoice')[0].reset();
                $('#invoiceModal').modal('show');
            }

            $('#formInvoice').on('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const url = "{{ route('catering.updateInvoice', ['id' => ':id']) }}".replace(':id', $('#invoiceId')
                    .val());

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        $('#invoiceModal').modal('hide');
                        loadData($('#catering-tab').hasClass('active') ? 'catering' : 'rencana');
                        Swal.fire('Berhasil', res.success, 'success');
                    },
                    error: function(xhr) {
                        const err = xhr.responseJSON?.error || 'Upload gagal';
                        Swal.fire('Error', err, 'error');
                    }
                });
            });


            function upgradeToCatering(id) {
                Swal.fire({
                    title: 'Upgrade ke Catering?',
                    text: 'Rencana pembelian akan diubah menjadi pengajuan catering aktif',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Upgrade',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const url = "{{ route('catering.upgrade', ['id' => ':id']) }}".replace(':id', id);
                        $.post(url, {
                            _token: "{{ csrf_token() }}"
                        }, function(res) {
                            loadData('rencana');
                            Swal.fire('Berhasil', res.success, 'success');
                        }).fail(function(xhr) {
                            Swal.fire('Error', xhr.responseJSON?.error || 'Gagal upgrade', 'error');
                        });
                    }
                });
            }

            function openStatusModal(id, currentTracking) {
                $('#statusId').val(id);
                $('#statusForm')[0].reset();
                $('#keteranganContainer').hide();
                $('#statusModal').modal('show');
            }

            $(document).on('click', '[data-status]', function() {
                const status = $(this).data('status');
                $('#statusInput').val(status);
                $('[data-status]').removeClass('btn-primary btn-danger').addClass(
                    'btn-outline-primary btn-outline-danger');
                $(this).removeClass('btn-outline-primary btn-outline-danger').addClass(status === '1' ? 'btn-primary' :
                    'btn-danger');
                $('#keteranganContainer').toggle(status === '0');
                if (status === '1') $('textarea[name="keterangan"]').val('');
            });

            $('#statusForm').on('submit', function(e) {
                e.preventDefault();
                const formData = $(this).serialize();

                $.ajax({
                    url: "{{ route('catering.approved') }}",
                    type: 'POST',
                    data: formData,
                    success: function(res) {
                        $('#statusModal').modal('hide');
                        loadData($('#catering-tab').hasClass('active') ? 'catering' : 'rencana');
                        Swal.fire('Berhasil', res.success, 'success');
                    },
                    error: function(xhr) {
                        const err = xhr.responseJSON?.error || 'Gagal update status';
                        Swal.fire('Error', err, 'error');
                    }
                });
            });

            function deleteData(id) {
                Swal.fire({
                    title: 'Hapus data?',
                    text: 'Data akan dihapus permanen!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const url = "{{ route('catering.destroy', ['id' => ':id']) }}".replace(':id', id);
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(res) {
                                loadData($('#catering-tab').hasClass('active') ? 'catering' : 'rencana');
                                Swal.fire('Berhasil', res.success, 'success');
                            },
                            error: function() {
                                Swal.fire('Error', 'Gagal menghapus data', 'error');
                            }
                        });
                    }
                });
            }

            $('#cateringTabs a').on('shown.bs.tab', function(e) {
                const type = $(e.target).attr('id') === 'catering-tab' ? 'catering' : 'rencana';
                loadData(type);
            });
        </script>
    @endpush
@endsection
