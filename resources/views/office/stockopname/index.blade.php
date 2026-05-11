@extends('layouts_office.app')

@section('office_contents')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.css">
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.js"></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        .table>:not(caption)>*>* {
            border-bottom-width: 0 !important;
        }

        .table th {
            background: #f8fafc;
            font-weight: 600;
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
        }

        .stock-card {
            border-radius: 14px;
            background: white;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            padding: 20px;
        }

        .editable {
            cursor: pointer;
            min-width: 120px;
        }

        .editable:hover {
            background: #f8fafc;
        }

        .saving {
            background: #fff3cd !important;
        }

        .saved {
            background: #d1e7dd !important;
        }

        .table-responsive {
            overflow-x: auto;
        }

        td[contenteditable=true]:empty:before {
            content: attr(data-placeholder);
            color: #adb5bd;
        }

        .dataTables_filter input {
            border-radius: 8px !important;
            padding: 5px 10px !important;
        }

        .dataTables_length select {
            border-radius: 8px !important;
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #0d6efd;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            min-width: 300px;
        }
    </style>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Stock Opname Barang</h4>
            <small class="text-muted">
                {{ now()->translatedFormat('l, d F Y') }}
            </small>
        </div>
    </div>
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button"
                role="tab" aria-controls="home" aria-selected="true">Tabel Stock</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button"
                role="tab" aria-controls="profile" aria-selected="false">Log Stock</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="keluar-tab" data-bs-toggle="tab" data-bs-target="#keluar" type="button"
                role="tab" aria-controls="keluar" aria-selected="false">Form Stock Keluar</button>
        </li>
    </ul>
    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
            <div class="d-flex gap-2 mb-2">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahBarang">
                    <i class="fas fa-plus"></i> Tambah Barang
                </button>
                <a href="{{ route('office.stockOpname.exportExcel') }}" class="btn btn-success">
                    <i class="fas fa-file-excel"></i>
                    Export Excel
                </a>
                <a href="{{ route('office.stockOpname.exportPdf') }}" class="btn btn-danger">
                    <i class="fas fa-file-pdf"></i>
                    Export PDF
                </a>
            </div>
            <div class="stock-card">
                <div class="table-responsive">
                    <table id="tableStock" class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Stock Awal</th>
                                <th>Stock Masuk</th>
                                <th>Stock Keluar</th>
                                <th>Stock Sekarang</th>
                                <th>Kategori</th>
                                <th>Satuan</th>
                                <th>Notes</th>
                                <th>PIC</th>
                                <th>Updated</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div></div>
                <div class="d-flex gap-2">
                    <div class="dropdown d-inline-block">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fas fa-cogs"></i> Actions
                        </button>

                        <ul class="dropdown-menu">
                            <li>
                                <button type="button" class="dropdown-item text-warning" data-bs-toggle="modal"
                                    data-bs-target="#modalCleanLog">
                                    <i class="fas fa-broom me-2"></i> Clean Up Log
                                </button>
                            </li>

                            <li>
                                <a href="{{ route('office.stockOpname.exportLogExcel') }}"
                                    class="dropdown-item text-success">
                                    <i class="fas fa-file-excel me-2"></i> Export Log Excel
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('office.stockOpname.exportLogPdf') }}" class="dropdown-item text-danger">
                                    <i class="fas fa-file-pdf me-2"></i> Export Log PDF
                                </a>
                            </li>
                        </ul>
                    </div>
                    <button id="refreshButton" class="btn btn-light">
                        Refresh
                    </button>
                </div>
            </div>
            <div class="stock-card">
                <div class="table-responsive">
                    <table id="tableLog" class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal Update</th>
                                <th>Nama Barang</th>
                                <th>Jenis</th>
                                <th>Nilai Sebelumnya</th>
                                <th>Nilai Hari Ini</th>
                                <th>Selisih</th>
                                <th>Notes</th>
                                <th>PIC</th>
                            </tr>
                        </thead>
                        <tbody id="tableStockOpnameLog"></tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="keluar" role="tabpanel" aria-labelledby="keluar-tab">
            <div class="stock-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Catat Stock Keluar</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddRow">
                        <i class="fas fa-plus"></i> Tambah Baris
                    </button>
                </div>
                <form id="formStockKeluar">
                    @csrf
                    <div class="table-responsive">
                        <table class="table align-middle" id="tableKeluar">
                            <thead>
                                <tr>
                                    <th width="40%">Barang</th>
                                    <th width="20%">Stock Saat Ini</th>
                                    <th width="15%">Qty Keluar</th>
                                    <th width="25%">Catatan</th>
                                    <th width="5%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="row-keluar">
                                    <td>
                                        <select name="barang_id[]" class="form-select select-barang" required>
                                            <option value="">-- Pilih Barang --</option>
                                        </select>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark current-stock">0</span>
                                    </td>
                                    <td>
                                        <input type="number" name="qty_keluar[]" class="form-control qty-input"
                                            min="1" placeholder="0" required>
                                    </td>
                                    <td>
                                        <input type="text" name="notes_keluar[]" class="form-control"
                                            placeholder="Opsional">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"
                                            title="Hapus baris">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="button" class="btn btn-secondary" id="btnResetForm">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-paper-plane"></i> Catat Semua Keluar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTambahBarang" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formTambahBarang">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Barang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mt-2">
                            <label>Nama Barang</label>
                            <input type="text" name="nama_barang" class="form-control" required>
                        </div>
                        <div class="form-group mt-3">
                            <label>Stock Awal</label>
                            <input type="number" name="stock_awal" class="form-control" required>
                        </div>
                        <div class="form-group mt-3">
                            <label>Kategori</label>
                            <select name="kategori" class="form-select" required>
                                <option value="">Pilih Kategori</option>
                                <option value="Kebutuhan Kantor">Kebutuhan Kantor</option>
                                <option value="Perlengkapan Dapur">Perlengkapan Dapur</option>
                                <option value="Mobil">Mobil</option>
                            </select>
                        </div>
                        <div class="form-group mt-3">
                            <label>Satuan</label>
                            <select name="satuan" class="form-select" required>
                                <option value="">Pilih Satuan</option>
                                <option value="Pcs">Pcs</option>
                                <option value="Renceng">Renceng</option>
                                <option value="Pack">Pack</option>
                                <option value="Botol">Botol</option>
                                <option value="Lusin">Lusin</option>
                            </select>
                        </div>
                        <div class="form-group mt-3">
                            <label>PIC</label>
                            <select name="pic" class="form-select">
                                @foreach ($karyawan as $k)
                                    <option value="{{ $k->kode_karyawan }}"
                                        {{ auth()->user()->id == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama_lengkap }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditBarang" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formEditBarang">
                    @csrf
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Barang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mt-2">
                            <label>Kode Barang</label>
                            <input type="text" name="kode_barang" id="edit_kode_barang" class="form-control"
                                required>
                        </div>
                        <div class="form-group mt-3">
                            <label>Nama Barang</label>
                            <input type="text" name="nama_barang" id="edit_nama_barang" class="form-control"
                                required>
                        </div>
                        <div class="form-group mt-3">
                            <label>Stock Awal</label>
                            <input type="number" name="stock_awal" id="edit_stock_awal" class="form-control" required>
                        </div>
                        <div class="form-group mt-3">
                            <label>Stock Masuk</label>
                            <input type="number" name="stock_masuk" id="edit_stock_masuk" class="form-control"
                                required>
                        </div>
                        <div class="form-group mt-3">
                            <label>Stock Keluar</label>
                            <input type="number" name="stock_keluar" id="edit_stock_keluar" class="form-control"
                                required>
                        </div>
                        <div class="form-group mt-3">
                            <label>Kategori</label>
                            <select name="kategori" id="edit_kategori" class="form-select" required>
                                <option value="Kebutuhan Kantor">Kebutuhan Kantor</option>
                                <option value="Perlengkapan Dapur">Perlengkapan Dapur</option>
                                <option value="Mobil">Mobil</option>
                            </select>
                        </div>
                        <div class="form-group mt-3">
                            <label>Satuan</label>
                            <select name="satuan" id="edit_satuan" class="form-select" required>
                                <option value="Pcs">Pcs</option>
                                <option value="Renceng">Renceng</option>
                                <option value="Pack">Pack</option>
                                <option value="Botol">Botol</option>
                                <option value="Lusin">Lusin</option>
                            </select>
                        </div>
                        <div class="form-group mt-3">
                            <label>Notes</label>
                            <textarea name="notes" id="edit_notes" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="form-group mt-3">
                            <label>PIC</label>
                            <select name="pic" id="edit_pic" class="form-select">
                                @foreach ($karyawan as $k)
                                    <option value="{{ $k->kode_karyawan }}">{{ $k->nama_lengkap }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCleanLog" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formCleanLog">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Clean Up Log</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">Pilih filter log yang ingin dihapus.</div>
                        <div class="form-group mt-3">
                            <label>Tanggal</label>
                            <input type="date" name="tanggal" class="form-control">
                        </div>
                        <div class="form-group mt-3">
                            <label>Bulan</label>
                            <select name="bulan" class="form-select">
                                <option value="">Pilih Bulan</option>
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group mt-3">
                            <label>Tahun</label>
                            <input type="number" name="tahun" class="form-control" value="{{ now()->year }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-danger">Hapus Log</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-spinner"></div>
    </div>

    <div id="toastNotification" class="toast-notification"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/id.min.js"></script>
    <script>
        moment.locale('id');
    </script>
    <script>
        $(document).ready(function() {
            let oldValue = '';
            let tableStock = null;
            let tableLog = null;

            function showLoading() {
                $('#loadingOverlay').fadeIn();
            }

            function hideLoading() {
                $('#loadingOverlay').fadeOut();
            }

            function showToast(message, type = 'success') {
                const colors = {
                    success: '#198754',
                    error: '#dc3545',
                    warning: '#ffc107',
                    info: '#0dcaf0'
                };
                const toast = $(`
                    <div class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true" style="background: ${colors[type]}">
                        <div class="d-flex">
                            <div class="toast-body">${message}</div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                        </div>
                    </div>
                `);
                $('#toastNotification').html(toast);
                const bsToast = new bootstrap.Toast(toast[0], {
                    delay: 3000
                });
                bsToast.show();
            }

            function reloadTableStock() {
                showLoading();
                $.ajax({
                    url: "{{ route('office.stockOpname.getData') }}",
                    type: 'GET',
                    success: function(response) {
                        let html = '';

                        if (response.length === 0) {
                            html =
                                `<tr><td colspan="13" class="text-center py-5 text-muted">Belum ada data stock opname</td></tr>`;
                        } else {
                            response.forEach(function(item, index) {
                                html += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${item.kode_barang}</td>
                                <td class="editable" contenteditable="true" data-id="${item.id}" data-field="nama_barang">${item.nama_barang}</td>
                                <td><span class="badge bg-light text-dark border">${numberFormat(item.stock_awal)}</span></td>
                                <td class="editable" contenteditable="true" data-id="${item.id}" data-field="stock_masuk" data-placeholder="Isi stock masuk">${item.stock_masuk}</td>
                                <td><span class="badge bg-secondary text-white">${numberFormat(item.stock_keluar)}</span></td>
                                <td><span class="badge bg-primary">${numberFormat(item.stock_sekarang)}</span></td>
                                <td class="editable" contenteditable="true" data-id="${item.id}" data-field="kategori">${item.kategori}</td>
                                <td class="editable" contenteditable="true" data-id="${item.id}" data-field="satuan">${item.satuan}</td>
                                <td class="editable" style="min-width: 300px; max-width: 300px; white-space: normal;" contenteditable="true" data-id="${item.id}" data-field="notes" data-placeholder="Kosong">${item.notes || ''}</td>
                                <td>${item.picData?.nama_lengkap || ''}</td>
                                <td>${item.updated_at ? moment(item.updated_at).locale('id').fromNow() : '-'}</td>
                                <td>
                                    <div class="input-group mb-3">
                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Action</button>
                                        <ul class="dropdown-menu">
                                            <li><button type="button" class="dropdown-item btnEdit" data-bs-toggle="modal" data-bs-target="#modalEditBarang" data-id="${item.id}" data-kode="${item.kode_barang}" data-nama="${item.nama_barang}" data-stock_awal="${item.stock_awal}" data-stock_masuk="${item.stock_masuk}" data-stock_keluar="${item.stock_keluar}" data-kategori="${item.kategori}" data-satuan="${item.satuan}" data-notes="${item.notes}" data-pic="${item.pic}"><i class="fas fa-edit"></i> Edit</button></li>
                                            <li><button type="button" class="dropdown-item btnSyncBaseline" data-id="${item.id}" data-stock="${item.stock_awal}"><i class="fas fa-sync"></i> Jadikan Baseline</button></li>
                                            <li><button type="button" class="dropdown-item btnDelete" data-id="${item.id}"><i class="fas fa-trash"></i> Hapus</button></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        `;
                            });
                        }

                        if ($.fn.DataTable.isDataTable('#tableStock')) {
                            $('#tableStock').DataTable().destroy();
                        }

                        $('#tableStock tbody').html(html);

                        tableStock = $('#tableStock').DataTable({
                            pageLength: 10,
                            responsive: true,
                            ordering: true,
                            destroy: true,
                            language: {
                                search: "Cari:",
                                lengthMenu: "Tampilkan _MENU_ data",
                                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                                paginate: {
                                    previous: "Sebelumnya",
                                    next: "Selanjutnya"
                                },
                                zeroRecords: "Data tidak ditemukan"
                            }
                        });

                        bindEditableEvents();
                        initTooltips();
                        hideLoading();
                    },
                    error: function() {
                        showToast('Gagal memuat data', 'error');
                        hideLoading();
                    }
                });
            }

            function numberFormat(num) {
                return new Intl.NumberFormat('id-ID').format(num);
            }

            function initTooltips() {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.forEach(function(tooltipTriggerEl) {
                    bootstrap.Tooltip.getInstance(tooltipTriggerEl)?.dispose();
                    new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }

            function reloadLogTable() {
                loadLog();
            }

            function bindEditableEvents() {
                $('.editable').off('focus').on('focus', function() {
                    oldValue = $(this).text().trim();
                });

                $('.editable').off('blur').on('blur', function() {
                    let cell = $(this);
                    let newValue = cell.text().trim();
                    let id = cell.data('id');
                    let field = cell.data('field');

                    if (oldValue === newValue) return;

                    if (field === 'stock_masuk' && parseInt(newValue) < 0) {
                        showToast('Stock masuk tidak boleh negatif', 'error');
                        cell.text(oldValue);
                        return;
                    }

                    cell.addClass('saving');

                    $.ajax({
                        url: "{{ route('office.stockOpname.inlineUpdate') }}",
                        type: "POST",
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            id: id,
                            field: field,
                            value: newValue
                        },
                        success: function(response) {
                            cell.removeClass('saving');
                            cell.addClass('saved');
                            setTimeout(() => cell.removeClass('saved'), 1000);

                            if (field === 'stock_masuk') {
                                reloadTableStock();
                                reloadLogTable();
                            }
                        },
                        error: function() {
                            showToast('Gagal update data', 'error');
                            cell.text(oldValue);
                            cell.removeClass('saving');
                        }
                    });
                });

                $('.editable').off('keypress').on('keypress', function(e) {
                    if (e.which === 13) {
                        e.preventDefault();
                        $(this).blur();
                    }
                });
            }

            bindEditableEvents();
            initTooltips();

            $('#formTambahBarang').off('submit').on('submit', function(e) {
                e.preventDefault();
                showLoading();

                $.ajax({
                    url: "{{ route('office.stockOpname.store') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        showToast('Barang berhasil ditambahkan');
                        $('#modalTambahBarang').modal('hide');
                        $('#formTambahBarang')[0].reset();
                        reloadTableStock();
                        reloadLogTable();
                    },
                    error: function(xhr) {
                        showToast(xhr.responseJSON?.message || 'Gagal menambah barang',
                            'error');
                        hideLoading();
                    }
                });
            });

            $('#refreshButton').on('click', function(e) {
                e.preventDefault();
                loadLog();
            });

            $(document).on('click', '.btnEdit', function() {
                $('#edit_id').val($(this).data('id'));
                $('#edit_kode_barang').val($(this).data('kode'));
                $('#edit_nama_barang').val($(this).data('nama'));
                $('#edit_stock_awal').val($(this).data('stock_awal'));
                $('#edit_stock_masuk').val($(this).data('stock_masuk'));
                $('#edit_stock_keluar').val($(this).data('stock_keluar'));
                $('#edit_kategori').val($(this).data('kategori'));
                $('#edit_satuan').val($(this).data('satuan'));
                $('#edit_notes').val($(this).data('notes'));
                $('#edit_pic').val($(this).data('pic'));
            });

            $('#formEditBarang').off('submit').on('submit', function(e) {
                e.preventDefault();
                showLoading();

                $.ajax({
                    url: "{{ route('office.stockOpname.update') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        showToast('Data berhasil diupdate');
                        $('#modalEditBarang').modal('hide');
                        reloadTableStock();
                        reloadLogTable();
                    },
                    error: function(xhr) {
                        showToast(xhr.responseJSON?.message || 'Gagal update data', 'error');
                        hideLoading();
                    }
                });
            });

            $(document).on('click', '.btnSyncBaseline', function() {
                let id = $(this).data('id');
                let newBaseline = $(this).data('stock');

                showLoading();
                $.ajax({
                    url: "{{ route('office.stockOpname.syncBaseline') }}",
                    type: "POST",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        id: id,
                        stock_awal: newBaseline
                    },
                    success: function(response) {
                        showToast('Baseline berhasil diperbarui');
                        reloadTableStock();
                    },
                    error: function() {
                        showToast('Gagal memperbarui baseline', 'error');
                        hideLoading();
                    }
                });
            });

            $(document).on('click', '.btnDelete', function() {
                if (!confirm('Hapus barang ini?')) return;

                let id = $(this).data('id');
                showLoading();

                $.ajax({
                    url: "{{ route('office.stockOpname.delete', '') }}/" + id,
                    type: "GET",
                    success: function(response) {
                        showToast('Barang berhasil dihapus');
                        reloadTableStock();
                        reloadLogTable();
                    },
                    error: function() {
                        showToast('Gagal menghapus barang', 'error');
                        hideLoading();
                    }
                });
            });

            $('#formCleanLog').off('submit').on('submit', function(e) {
                e.preventDefault();

                if (!confirm(
                        'Yakin ingin menghapus log stock opname? Data yang dihapus tidak bisa dikembalikan!'
                    )) {
                    return;
                }

                showLoading();

                $.ajax({
                    url: "{{ route('office.stockOpname.cleanLog') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        showToast(response.message || 'Log berhasil dihapus');
                        $('#modalCleanLog').modal('hide');
                        reloadLogTable();
                    },
                    error: function(xhr) {
                        showToast(xhr.responseJSON?.message || 'Gagal menghapus log', 'error');
                        hideLoading();
                    }
                });
            });

            function loadLog() {
                showLoading();

                $.ajax({
                    url: "{{ route('office.stockOpname.getLog') }}",
                    type: 'GET',
                    success: function(response) {

                        let html = '';

                        response.forEach(function(item, index) {
                            const jenisLabel = item.jenis_transaksi === 'masuk' ?
                                '<span class="badge bg-success">Masuk</span>' :
                                '<span class="badge bg-danger">Keluar</span>';

                            html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.updated_at ? moment(item.updated_at).locale('id').format('DD MMMM YYYY HH:mm') : '-'}</td>
                        <td>${item.barang?.nama_barang ?? '-'}</td>
                        <td>${jenisLabel}</td>
                        <td>${item.stock_sebelumnya ?? 0}</td>
                        <td>${item.stock_hari_ini ?? 0}</td>
                        <td>${item.selisih ?? 0}</td>
                        <td>${item.notes ?? '-'}</td>
                        <td>${item.karyawan?.nama_lengkap ?? '-'}</td>
                    </tr>
                `;
                        });

                        if ($.fn.DataTable.isDataTable('#tableLog')) {
                            $('#tableLog').DataTable().clear().destroy();
                        }

                        $('#tableStockOpnameLog').html(html);

                        tableLog = $('#tableLog').DataTable({
                            destroy: true,
                            pageLength: 10,
                            responsive: true,
                            ordering: true,
                            language: {
                                search: "Cari:",
                                lengthMenu: "Tampilkan _MENU_ data",
                                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                                paginate: {
                                    previous: "Sebelumnya",
                                    next: "Selanjutnya"
                                },
                                zeroRecords: "Data tidak ditemukan"
                            }
                        });

                        hideLoading();
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                        showToast('Gagal memuat log', 'error');
                        hideLoading();
                    }
                });
            }

            $('#profile-tab, #keluar-tab').on('shown.bs.tab', function() {
                loadLog();
            });

            $('#btnAddRow').on('click', function() {
                const newRow = `
                    <tr class="row-keluar">
                        <td>
                            <select name="barang_id[]" class="form-select select-barang" required>
                                <option value="">-- Pilih Barang --</option>
                            </select>
                        </td>
                        <td><span class="badge bg-light text-dark current-stock">0</span></td>
                        <td>
                            <input type="number" name="qty_keluar[]" class="form-control qty-input" 
                                   min="1" placeholder="0" required>
                        </td>
                        <td>
                            <input type="text" name="notes_keluar[]" class="form-control" placeholder="Opsional">
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                $('#tableKeluar tbody').append(newRow);
                populateSelects();
                bindSelectEvent();
                bindRemoveEvent();
            });

            function populateSelects() {
                const options =
                    `@foreach ($barang as $b)<option value="{{ $b->id }}" data-stock="{{ $b->stock_sekarang }}" data-nama="{{ $b->nama_barang }}">{{ $b->nama_barang }}</option>@endforeach`;
                $('.select-barang').each(function() {
                    const currentVal = $(this).val();
                    $(this).html('<option value="">-- Pilih Barang --</option>' + options);
                    if (currentVal) $(this).val(currentVal);
                });
            }

            function bindRemoveEvent() {
                $(document).off('click', '.btn-remove-row').on('click', '.btn-remove-row', function() {
                    const rows = $('#tableKeluar tbody tr');
                    if (rows.length > 1) {
                        $(this).closest('tr').fadeOut(200, function() {
                            $(this).remove();
                        });
                    } else {
                        showToast('Minimal harus ada 1 baris', 'warning');
                    }
                });
            }

            function bindSelectEvent() {
                $(document).off('change', '.select-barang').on('change', '.select-barang', function() {
                    const option = $(this).find('option:selected');
                    const stock = option.data('stock') ?? 0;
                    $(this).closest('tr').find('.current-stock').text(numberFormat(stock));
                    $(this).closest('tr').find('.qty-input').val('');
                });
            }

            $('#btnResetForm').on('click', function() {
                $('#tableKeluar tbody').html(`
                    <tr class="row-keluar">
                        <td>
                            <select name="barang_id[]" class="form-select select-barang" required>
                                <option value="">-- Pilih Barang --</option>
                            </select>
                        </td>
                        <td><span class="badge bg-light text-dark current-stock">0</span></td>
                        <td>
                            <input type="number" name="qty_keluar[]" class="form-control qty-input" 
                                   min="1" placeholder="0" required>
                        </td>
                        <td>
                            <input type="text" name="notes_keluar[]" class="form-control" placeholder="Opsional">
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
                populateSelects();
                bindSelectEvent();
                bindRemoveEvent();
            });

            $('#formStockKeluar').off('submit').on('submit', function(e) {
                e.preventDefault();

                const rows = $('#tableKeluar tbody tr');
                let isValid = true;
                let items = [];

                rows.each(function() {
                    const barangId = $(this).find('select[name="barang_id[]"]').val();
                    const qty = parseInt($(this).find('input[name="qty_keluar[]"]').val());
                    const notes = $(this).find('input[name="notes_keluar[]"]').val() || '';
                    const currentStock = parseInt($(this).find('.current-stock').text().replace(
                        /\./g, '')) || 0;

                    if (!barangId) {
                        isValid = false;
                        showToast('Pilih barang terlebih dahulu', 'warning');
                        return false;
                    }

                    if (!qty || qty <= 0) {
                        isValid = false;
                        showToast('Qty keluar harus diisi dan lebih dari 0', 'warning');
                        return false;
                    }

                    if (qty > currentStock) {
                        showToast('Qty keluar melebihi stock tersedia', 'error');
                        isValid = false;
                        return false;
                    }

                    items.push({
                        barang_id: barangId,
                        qty_keluar: qty,
                        notes_keluar: notes
                    });
                });

                if (!isValid || items.length === 0) return;

                showLoading();

                $.ajax({
                    url: "{{ route('office.stockOpname.storeKeluar') }}",
                    type: "POST",
                    contentType: 'application/json',
                    data: JSON.stringify({
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        items: items
                    }),
                    success: function(response) {
                        showToast(response.message || 'Stock keluar berhasil dicatat',
                            'success');
                        $('#btnResetForm').trigger('click');
                        reloadTableStock();
                        reloadLogTable();
                    },
                    error: function(xhr) {
                        showToast(xhr.responseJSON?.error || xhr.responseJSON?.message ||
                            'Gagal mencatat stock keluar', 'error');
                        hideLoading();
                    }
                });
            });

            reloadTableStock();
            loadLog();
            populateSelects();
            bindSelectEvent();
            bindRemoveEvent();
        });
    </script>
@endsection
