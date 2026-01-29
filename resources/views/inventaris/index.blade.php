@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Loading Modal -->
        <div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="spinnerModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="cube">
                    <div class="cube_item cube_x"></div>
                    <div class="cube_item cube_y"></div>
                    <div class="cube_item cube_x"></div>
                    <div class="cube_item cube_z"></div>
                </div>
            </div>
        </div>

        <!-- Modal Buat Kode Barnag -->
        <div class="modal fade" id="createKodeBarang" tabindex="-1" aria-labelledby="createKodeBarangLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('CreateKodeIinvetaris') }}">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createKodeBarangLabel">Tambah Kode Barang</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="nama_barang" class="form-label">Nama Barang</label>
                                <input type="text" name="nama_barang" id="nama_barang" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="kode_barang" class="form-label">Kode Barang</label>
                                <input type="text" name="kode_barang" id="kode_barang" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>


        <!-- Modal Tambah Data -->
        <div class="modal fade" id="addInventarisModal" tabindex="-1" aria-labelledby="addInventarisModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addInventarisModalLabel">Tambah Inventaris</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addInventarisForm">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Barang</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="kodebarang" class="form-label">Kode Barang</label>
                                <select class="form-control" id="kodebarang" name="kodebarang">
                                    <option value="">-- Pilih Kode Barang --</option>
                                    @foreach ($kodeBarang as $kodeBarang)
                                        <option value="{{ $kodeBarang }}">{{ $kodeBarang }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="merk_kode_seri_hardware" class="form-label">Merk / Kode Seri / Kode
                                    Hardware</label>
                                <input type="text" class="form-control" id="merk_kode_seri_hardware"
                                    name="merk_kode_seri_hardware" required>
                            </div>
                            <div class="mb-3">
                                <label for="qty" class="form-label">Qty</label>
                                <input type="number" class="form-control" id="qty" name="qty" min="1"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label for="satuan" class="form-label">Satuan</label>
                                <input type="text" class="form-control" id="satuan" name="satuan">
                            </div>
                            <div class="mb-3">
                                <label for="type" class="form-label">Tipe</label>
                                <select class="form-control" id="type" name="type" required>
                                    <option value="E">Elektronik</option>
                                    <option value="NE">Non-Elektronik</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="harga_beli" class="form-label">Harga Beli (Rp)</label>
                                <input type="number" class="form-control" id="harga_beli" name="harga_beli"
                                    min="0" step="0.01" required>
                            </div>
                            <div class="mb-3">
                                <label for="waktu_pembelian" class="form-label">Tanggal Pembelian</label>
                                <input type="date" class="form-control" id="waktu_pembelian" name="waktu_pembelian"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label for="pengguna" class="form-label">Pengguna</label>
                                <select class="form-control" id="pengguna" name="pengguna">
                                    <option value="">-- Pilih Pengguna --</option>
                                    @foreach ($usernames as $username)
                                        <option value="{{ $username }}">{{ $username }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="ruangan" class="form-label">Ruangan</label>
                                <input type="text" class="form-control" id="ruangan" name="ruangan">
                            </div>
                            <div class="mb-3">
                                <label for="kondisi" class="form-label">Kondisi</label>
                                <select class="form-control" id="kondisi" name="kondisi" required>
                                    <option value="baik">Baik</option>
                                    <option value="rusak">Rusak</option>
                                    <option value="kurang layak">Kurang Layak</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4"></textarea>
                            </div>
                            <div id="error-message" class="text-danger" style="display: none;"></div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="saveInventaris">Simpan</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Import Data -->
        <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importModalLabel">Import Inventaris</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="importInventarisForm" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="file" class="form-label">Pilih File Excel</label>
                                <input type="file" class="form-control" id="file" name="file"
                                    accept=".xlsx,.xls,.csv" required>
                            </div>
                            <div id="import-error-message" class="text-danger" style="display: none;"></div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="importInventaris">Import</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="d-flex justify-content-end mb-3">
                    <button id="exportExcel" class="btn btn-success mx-2" data-toggle="tooltip" data-placement="top" title="Export ke Excel">
                        <img src="{{ asset('icon/excel.svg') }}" class="" width="30px"> Export ke Excel
                    </button>
                    <a href="#" class="btn btn-md click-primary mx-2" data-bs-toggle="modal"
                        data-bs-target="#importModal" data-toggle="tooltip" data-placement="top"
                        title="Import Inventaris">
                        <img src="{{ asset('icon/upload-white.svg') }}" class="" width="30px"> Import Inventaris
                    </a>
                    <a href="#" class="btn btn-md click-primary mx-2" data-bs-toggle="modal"
                        data-bs-target="#addInventarisModal" data-toggle="tooltip" data-placement="top"
                        title="Tambah Inventaris">
                        <img src="{{ asset('icon/plus.svg') }}" class="" width="30px"> Data Inventaris
                    </a>
                    <a href="#" class="btn btn-md click-primary mx-2" data-bs-toggle="modal"
                        data-bs-target="#createKodeBarang" data-toggle="tooltip" data-placement="top"
                        title="Tambah Inventaris">
                        <img src="{{ asset('icon/plus.svg') }}" class="" width="30px"> Kode Barang
                    </a>
                </div>
                <h3 class="card-title text-center my-1">{{ __('Data Inventaris Inixindo') }}</h3>
                <div class="card m-4">
                    <div class="card-body table-responsive">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="elektronik-tab" data-bs-toggle="tab" data-bs-target="#elektronik" type="button" role="tab">
                                    {{ __('Tipe Elektronik') }}
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="nonElektronik-tab" data-bs-toggle="tab" data-bs-target="#nonElektronik" type="button" role="tab">
                                    {{ __('Tipe Non-Elektronik') }}
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content mt-2" id="myTabContent">
                            <div class="tab-pane fade show active" id="elektronik" role="tabpanel">
                                <h4 class="card-title text-center my-1">{{ __('Barang Elektronik') }}</h4>
                                <table class="table table-striped" id="inventaristableElektronik">
                                    <thead>
                                    <tr>
                                        <th scope="col">No</th>
                                        <th scope="col">ID</th>
                                        <th scope="col">ID Barang</th>
                                        <th scope="col">Nama Barang</th>
                                        <th scope="col">Tipe</th>
                                        <th scope="col">Pic</th>
                                        <th scope="col">Ruangan</th>
                                        <th scope="col">Kondisi</th>
                                        <th scope="col">Tanggal Pembelian</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                </table>
                            </div>
                            
                            <div class="tab-pane fade show active" id="nonElektronik" role="tabpanel">
                                <h4 class="card-title text-center my-1">{{ __('Barang Non-Elektronik') }}</h4>
                                <table class="table table-striped" id="inventaristableNonElektronik">
                                    <thead>
                                    <tr>
                                        <th scope="col">No</th>
                                        <th scope="col">ID</th>
                                        <th scope="col">ID Barang</th>
                                        <th scope="col">Nama Barang</th>
                                        <th scope="col">Tipe</th>
                                        <th scope="col">Pic</th>
                                        <th scope="col">Ruangan</th>
                                        <th scope="col">Kondisi</th>
                                        <th scope="col">Tanggal Pembelian</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .loader {
            position: relative;
            text-align: center;
            margin: 15px auto 35px auto;
            z-index: 9999;
            display: block;
            width: 80px;
            height: 80px;
            border: 10px solid rgba(0, 0, 0, .3);
            border-radius: 50%;
            border-top-color: #000;
            animation: spin 1s ease-in-out infinite;
            -webkit-animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to {
                -webkit-transform: rotate(360deg);
            }
        }

        @-webkit-keyframes spin {
            to {
                -webkit-transform: rotate(360deg);
            }
        }

        .modal-content {
            border-radius: 0px;
            box-shadow: 0 0 20px 8px rgba(0, 0, 0, 0.7);
        }

        .modal-backdrop.show {
            opacity: 0.75;
        }

        .loader-txt p {
            font-size: 13px;
            color: #666;
        }

        .loader-txt p small {
            font-size: 11.5px;
            color: #999;
        }
    </style>

    @push('js')
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
        <script src="https://cdn.datatables.net/buttons/1.5.6/js/dataTables.buttons.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.html5.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.print.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.5.6/css/buttons.dataTables.min.css">

        <script>
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            window.userRole = '{{ auth()->user()->role ?? 'Guest' }}';

            $(document).ready(function() {
                var tableIndex = 1;

                // Custom sorting for kondisi
                $.fn.dataTable.ext.order['custom-kondisi'] = function(settings, col) {
                    return this.api().column(col, {
                        order: 'index'
                    }).nodes().map(function(td, i) {
                        var kondisi = $(td).text().trim();
                        switch (kondisi) {
                            case 'baik':
                                return 1;
                            case 'sedang diperbaiki':
                                return 2;
                            case 'rusak/bermasalah':
                                return 3;
                            default:
                                return 4;
                        }
                    });
                };

                var table = $('#inventaristableElektronik').DataTable({
                    dom: 'Bfrtip',
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            title: 'Data Inventaris',
                            text: '<i class="bi bi-file-earmark-excel"></i> Export Excel',
                            className: 'btn btn-success'
                        }
                    ],
                    ajax: {
                        url: "{{ route('getInventaris') }}",
                        type: "GET",
                        dataSrc: function(json) {
                            return json.data.filter(item => item.type === 'E');
                        },
                        beforeSend: function() {
                            $('#loadingModal').modal('show');
                        },
                        complete: function() {
                            $('#loadingModal').modal('hide');
                        },
                        error: function(xhr, status, error) {
                            $('#loadingModal').modal('hide');
                            alert('Gagal memuat data: ' + (xhr.responseJSON?.message || error));
                        }
                    },
                    columns: [{
                            data: null,
                            render: function() {
                                return tableIndex++;
                            }
                        },
                        {
                            data: 'id',
                            visible: false
                        },
                        {
                            data: 'idbarang'
                        },
                        {
                            data: 'name'
                        },
                        {
                            data: 'type',
                            render: function(data) {
                                return data === 'E' ? 'Elektronik' : 'Non-Elektronik';
                            }
                        },
                        {
                            data: 'pengguna'
                        },
                        {
                            data: 'ruangan',
                            render: function(data) {
                                return data || '-';
                            }
                        },
                        {
                            data: 'kondisi'
                        },
                        {
                            "data": null,
                            "render": function (data, type, row) {
                                var created_at = moment(data.waktu_pembelian).format('dddd, DD MMMM YYYY');
                                return created_at;
                            },
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                if (window.userRole === 'TS' && row.type === 'NE') return '';
                                if (window.userRole === 'Finance' && row.type === 'E') return '';

                                var actions = '<div class="dropdown">';
                                actions +=
                                    '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton_' +
                                    row.idbarang +
                                    '" data-bs-toggle="dropdown" aria-expanded="false">Actions</button>';
                                actions +=
                                    '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton_' +
                                    row.idbarang + '">';
                                actions +=
                                    '<a class="dropdown-item" href="{{ url('/inventaris/show/data') }}/' +
                                    row.id +
                                    '" data-toggle="tooltip" data-placement="top" title="Edit Inventaris"><img src="{{ asset('icon/file-text.svg') }}" class="">Detail</a>';
                                actions +=
                                    '<form onsubmit="return confirm(\'Apakah Anda Yakin ?\');" action="{{ url('/inventaris/delete/data/') }}/' +
                                    row.id + '" method="POST">';
                                actions += '@csrf';
                                actions += '@method('DELETE')';
                                actions +=
                                    '<button type="submit" class="dropdown-item"><img src="{{ asset('icon/trash-danger.svg') }}" class=""> Hapus</button>';
                                actions += '</form>';
                                actions += '</div></div>';
                                return actions;
                            }
                        }
                    ],
                    order: [
                        [6, 'asc']
                    ],
                    columnDefs: [{
                        targets: 6,
                        orderDataType: 'custom-kondisi'
                    }]
                });

                // Type non elektronik
                var table = $('#inventaristableNonElektronik').DataTable({
                    dom: 'Bfrtip',
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            title: 'Data Inventaris',
                            text: '<i class="bi bi-file-earmark-excel"></i> Export Excel',
                            className: 'btn btn-success'
                        }
                    ],
                    ajax: {
                        url: "{{ route('getInventaris') }}",
                        type: "GET",
                        dataSrc: function(json) {
                            return json.data.filter(item => item.type != 'E');
                        },
                        beforeSend: function() {
                            $('#loadingModal').modal('show');
                        },
                        complete: function() {
                            $('#loadingModal').modal('hide');
                        },
                        error: function(xhr, status, error) {
                            $('#loadingModal').modal('hide');
                            alert('Gagal memuat data: ' + (xhr.responseJSON?.message || error));
                        }
                    },
                    columns: [{
                            data: null,
                            render: function() {
                                return tableIndex++;
                            }
                        },
                        {
                            data: 'id',
                            visible: false
                        },
                        {
                            data: 'idbarang'
                        },
                        {
                            data: 'name'
                        },
                        {
                            data: 'type',
                            render: function(data) {
                                return data === 'E' ? 'Elektronik' : 'Non-Elektronik';
                            }
                        },
                        {
                            data: 'pengguna'
                        },
                        {
                            data: 'ruangan',
                            render: function(data) {
                                return data || '-';
                            }
                        },
                        {
                            data: 'kondisi'
                        },
                        {
                            "data": null,
                            "render": function (data, type, row) {
                                var created_at = moment(data.waktu_pembelian).format('dddd, DD MMMM YYYY');
                                return created_at;
                            },
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                if (window.userRole === 'TS' && row.type === 'NE') return '';
                                if (window.userRole === 'Finance' && row.type === 'E') return '';

                                var actions = '<div class="dropdown">';
                                actions +=
                                    '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton_' +
                                    row.idbarang +
                                    '" data-bs-toggle="dropdown" aria-expanded="false">Actions</button>';
                                actions +=
                                    '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton_' +
                                    row.idbarang + '">';
                                actions +=
                                    '<a class="dropdown-item" href="{{ url('/inventaris/show/data') }}/' +
                                    row.id +
                                    '" data-toggle="tooltip" data-placement="top" title="Edit Inventaris"><img src="{{ asset('icon/file-text.svg') }}" class="">Detail</a>';
                                actions +=
                                    '<form onsubmit="return confirm(\'Apakah Anda Yakin ?\');" action="{{ url('/inventaris/delete/data/') }}/' +
                                    row.id + '" method="POST">';
                                actions += '@csrf';
                                actions += '@method('DELETE')';
                                actions +=
                                    '<button type="submit" class="dropdown-item"><img src="{{ asset('icon/trash-danger.svg') }}" class=""> Hapus</button>';
                                actions += '</form>';
                                actions += '</div></div>';
                                return actions;
                            }
                        }
                    ],
                    order: [
                        [6, 'asc']
                    ],
                    columnDefs: [{
                        targets: 6,
                        orderDataType: 'custom-kondisi'
                    }]
                });

                // Handle Simpan Data
                $('#saveInventaris').on('click', function(e) {
                    e.preventDefault();
                    var form = $('#addInventarisForm')[0];
                    if (!form.checkValidity()) {
                        form.reportValidity();
                        return;
                    }

                    $('#loadingModal').modal('show');
                    $('#error-message').hide();

                    $.ajax({
                        url: '{{ route('InputInventaris') }}',
                        type: 'POST',
                        data: $('#addInventarisForm').serialize(),
                        success: function(response) {
                            $('#loadingModal').modal('hide');
                            $('#addInventarisModal').modal('hide');
                            table.ajax.reload(function() {
                                tableIndex = 1;
                            });
                            alert('Data berhasil disimpan!');
                            $('#addInventarisForm')[0].reset();
                        },
                        error: function(xhr) {
                            $('#loadingModal').modal('hide');
                            var response = xhr.responseJSON || {
                                message: 'Terjadi kesalahan pada server.'
                            };
                            var errorMsg = response.message || 'Terjadi kesalahan pada server.';
                            if (response.errors) {
                                errorMsg = Object.values(response.errors).flat().join('<br>');
                            }
                            $('#error-message').html(errorMsg).show();
                            console.error('AJAX Error:', xhr);
                        }
                    });
                });

                // Handle Import Data
                $('#importInventaris').on('click', function(e) {
                    e.preventDefault();
                    var form = $('#importInventarisForm')[0];
                    if (!form.checkValidity()) {
                        form.reportValidity();
                        return;
                    }

                    var formData = new FormData(form);
                    $('#loadingModal').modal('show');
                    $('#import-error-message').hide();

                    $.ajax({
                        url: '{{ route('ImportDataInventaris') }}',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            $('#loadingModal').modal('hide');
                            $('#importModal').modal('hide');
                            table.ajax.reload(function() {
                                tableIndex = 1;
                            });
                            alert('Data berhasil diimpor!');
                            $('#importInventarisForm')[0].reset();
                        },
                        error: function(xhr) {
                            $('#loadingModal').modal('hide');
                            var response = xhr.responseJSON || {
                                message: 'Terjadi kesalahan pada server.'
                            };
                            var errorMsg = response.message || 'Terjadi kesalahan pada server.';
                            if (response.errors) {
                                errorMsg = Object.values(response.errors).flat().join('<br>');
                            }
                            $('#import-error-message').html(errorMsg).show();
                            console.error('AJAX Import Error:', xhr);
                        }
                    });
                });

                $('#exportExcel').on('click', function() {
                    window.location.href = "{{ route('inventaris.export') }}";
                });
            });
        </script>
    @endpush
@endsection
