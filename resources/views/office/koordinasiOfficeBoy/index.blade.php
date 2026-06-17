@extends('layouts_office.app')

@section('office_contents')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    {{-- Alert Success --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    {{-- Alert error --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @php
        $auth = auth()->user()->karyawan->jabatan;
    @endphp
    <input type="hidden" name="authUser" id="authUser" value="{{ $auth }}">

    {{-- Modal Tambah --}}
    <div class="modal fade" id="createModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h1 class="modal-title fs-5">Buat Koordinasi Office Boy</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <form method="post" action="{{ route('office.KoordinasiOb.store') }}"
                        enctype="multipart/form-data">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label col-form-label">Nama Tugas <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="nama_tugas" class="form-control">
                            </div>
    
                            <div class="col-md-4">
                                <label class="form-label col-form-label">Office Boy <span
                                        class="text-danger">*</span></label>
                                <select name="karyawan" class="form-control">
                                    <option value="" disabled hidden selected>Pilih OB</option>
                                    @foreach ($officeBoy as $ob)
                                        <option value="{{ $ob->id }}">{{ $ob->nama_lengkap }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label col-form-label">Deadline <span
                                        class="text-danger">*</span></label>
                                <input type="datetime-local" name="deadline" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="mb-3">
                                <label for="catatan" class="col-md-5 col-form-label">Catatan (Optional)</label>
                                <textarea class="form-control" name="catatan"></textarea>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h1 class="modal-title fs-5">Edit Koordinasi Office Boy</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <form id="formEdit" method="post" action="{{ route('office.KoordinasiOb.update') }}"
                        enctype="multipart/form-data">
                        @csrf

                        <div class="row mb-3">
                            <input type="hidden" name="id">

                            <div class="col-md-4">
                                <label class="form-label col-form-label">Nama Tugas <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="nama_tugas" class="form-control">
                            </div>
    
                            <div class="col-md-4">
                                <label class="form-label col-form-label">Office Boy <span
                                        class="text-danger">*</span></label>
                                <select name="karyawan" class="form-control">
                                    <option value="" disabled hidden selected>Pilih OB</option>
                                    @foreach ($officeBoy as $ob)
                                        <option value="{{ $ob->id }}">{{ $ob->nama_lengkap }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label col-form-label">Deadline <span
                                        class="text-danger">*</span></label>
                                <input type="datetime-local" name="deadline" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="mb-3">
                                <label for="catatan" class="col-md-5 col-form-label">Catatan (Optional)</label>
                                <textarea class="form-control" name="catatan"></textarea>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Detail --}}
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content shadow-lg border-0 rounded-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-semibold">Detail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4 py-3" id="detailContent"></div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center">
        <h4 class="fw-bold text-dark">Koordinasi Office Boy</h4>
        <small class="text-muted fw-medium">{{ now()->translatedFormat('l, d F Y') }}</small>
    </div>

    <div class="card shadow-lg border-0 rounded-4 glass-force">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                        <i class="fa fa-plus me-1"></i> Buat Koordinasi
                    </button>


                    @if (Auth()->user()->jabatan === 'HRD' || Auth()->user()->jabatan === 'GM' || Auth()->user()->jabatan === 'Office Boy')
                        <div class="btn-group">
                            <button id="refreshBtn" onclick="refreshTable()" class="btn btn-primary">Refresh</button>
                            {{-- <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-file-export me-1"></i> Export Laporan
                            </button> --}}
                            {{-- <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                        data-bs-target="#modalExport">
                                        <i class="fas fa-cog me-2"></i> Export dengan Filter
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('office.pickupDriver.export.excel') }}">
                                        <i class="fas fa-file-excel text-success me-2"></i> Excel (Semua Data)
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('office.pickupDriver.export.pdf') }}">
                                        <i class="fas fa-file-pdf text-danger me-2"></i> PDF (Semua Data)
                                    </a>
                                </li>
                            </ul> --}}
                        </div>
                    @endif

                </div>

                <div class="modal fade" id="modalExport" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <form id="formExport" method="GET">
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="fas fa-filter me-2"></i>Filter Export Laporan
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Tanggal Mulai</label>
                                            <input type="date" name="start_date" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Tanggal Akhir</label>
                                            <input type="date" name="end_date" class="form-control">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Kendaraan</label>
                                            <select name="kendaraan" class="form-select">
                                                <option value="">Semua Kendaraan</option>
                                                <option value="H1">H1</option>
                                                <option value="Inova">Inova</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-success"
                                        formaction="{{ route('office.pickupDriver.export.excel') }}">
                                        <i class="fas fa-file-excel me-1"></i> Export Excel
                                    </button>
                                    <button type="submit" class="btn btn-danger"
                                        formaction="{{ route('office.pickupDriver.export.pdf') }}">
                                        <i class="fas fa-file-pdf me-1"></i> Export PDF
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body m-4">
            <div class="table-responsive">
                <table id="dataTable" class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tugas</th>
                            <th>Office Boy</th>
                            <th>Deadline</th>
                            <th>Pembuat</th>
                            <th>Status</th>
                            <th class="text-center pe-4">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/id.min.js"></script>

    <script>
        $(document).ready(function() {
            loadTable();

            $(document).on('click', '#refreshBtn', function() {
                loadTable();
            })

            // edit
            $(document).on('click', '.editBtn', function() {
                const table = $('#dataTable').DataTable();
                const rowData = table.row($(this).closest('tr')).data();
                const id = rowData.id;

                edit(id, rowData);
            });

            // detail
            $(document).on('click', '.detailBtn', function() {
                const table = $('#dataTable').DataTable();
                const rowData = table.row($(this).closest('tr')).data();
                const id = rowData.id;

                detail(id, rowData);
            });

            // delete 
            $(document).on('click', '.deleteBtn', function() {
                const id = $(this).data('id');
                const namaTugas = $(this).data('title');

                Swal.fire({
                    title: `Hapus Tugas ${namaTugas}?`,
                    text: 'Data yang dihapus tidak dapat dikembalikan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {

                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: `/office/koordinasi-ob/delete/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },

                        beforeSend: function() {
                            Swal.fire({
                                title: 'Menghapus...',
                                text: 'Mohon tunggu',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                        },

                        success: function(res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.message ?? 'Data berhasil dihapus'
                            });

                            $('#dataTable').DataTable().ajax.reload(null, false);
                        },

                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON?.message ?? 'Terjadi kesalahan'
                            });
                        }
                    });

                });
            });
        })

        // update status
        $(document).on('click', '.obAction', function() {
            const id = $(this).data('id');
            const action = $(this).data('action');
            const namaTugas = $(this).data('title');

            if (action === 'terima') {
                Swal.fire({
                    title: 'Terima tugas?',
                    html: `Terima tugas <b>${namaTugas}</b>?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {

                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: `/office/koordinasi-ob/update-status-${action}/${id}`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },

                        beforeSend: function() {
                            Swal.fire({
                                title: 'Memproses...',
                                text: 'Mohon tunggu',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                        },

                        success: function(res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.message ?? 'Tugas berhasil diupdate'
                            });

                            $('#dataTable').DataTable().ajax.reload(null, false);
                        },

                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON?.message ?? 'Terjadi kesalahan'
                            });
                        }
                    });

                });
            } else if (action === 'selesai') {
                Swal.fire({
                    title: 'Selesaikan tugas?',
                    html: `Selesaikan tugas <b>${namaTugas}</b>?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {

                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: `/office/koordinasi-ob/update-status-${action}/${id}`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },

                        beforeSend: function() {
                            Swal.fire({
                                title: 'Memproses...',
                                text: 'Mohon tunggu',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                        },

                        success: function(res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.message ?? 'Tugas berhasil diupdate'
                            });

                            $('#dataTable').DataTable().ajax.reload(null, false);
                        },

                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON?.message ?? 'Terjadi kesalahan'
                            });
                        }
                    });

                });
            }
        })

        function getStatusBadge(status, id, namaTugas) {
            const map = {
                'Dikerjakan': '<span class="badge bg-warning-subtle text-warning">Dikerjakan</span>',
                'Selesai': '<span class="badge bg-success-subtle text-success">Selesai</span>'
            };
            const action = {
                'Dikerjakan': `<button class="obAction btn bg-warning-subtle text-warning" data-action="selesai" data-id="${id}" data-title="${namaTugas}">Selesaikan</button>`,
                'Selesai': '<span class="badge bg-success-subtle text-success">Selesai</span>'
            }
            let auth = $('#authUser').val();

            if (auth === 'Office Boy') {
                return action[status] || `<button class="obAction btn bg-primary-subtle text-primary" data-action="terima" data-id="${id}" data-title="${namaTugas}">Terima</button>`;
            } else {
                return map[status] || '<span class="badge bg-secondary-subtle text-secondary">Menunggu Konfirmasi</span>';
            }
        }

        function loadTable() {
            if ($.fn.DataTable.isDataTable('#dataTable')) $('#dataTable').DataTable().destroy();
            $('#dataTable').DataTable({
                processing: false,
                serverSide: false,
                responsive: true,
                language: {
                    processing: '<small class="text-muted">Loading...</small>',
                    emptyTable: "Belum ada data"
                },
                ajax: {
                    url: "{{ route('office.KoordinasiOb.getData') }}",
                    type: "GET",
                    dataSrc: 'data'
                },
                columns: [
                    {
                        data: 'nama_tugas'
                    },
                    {
                        data: 'karyawan.nama_lengkap'
                    },
                    {
                        data: 'deadline',
                        render: function(data) {
                            return data
                                ? moment(data).format('DD/MM/YYYY HH:mm')
                                : '-';
                        }
                    },
                    {
                        data: 'pembuat.nama_lengkap'
                    },
                    {
                        data: null,
                        defaultContent: '-',
                        render: function (data) {
                            return getStatusBadge(data.status, data.id, data.nama_tugas);
                        }
                    },
                    {
                        data: null,
                        defaultContent: '-',
                        render: function(data, row, type) {                        
                            return `
                                <div class="dropdown">
                                    <button
                                        class="btn btn-outline-secondary btn-sm dropdown-toggle"
                                        type="button"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        Aksi
                                    </button>

                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">

                                        <li>
                                            <a class="dropdown-item detailBtn"
                                                data-id="${row.id}"
                                                >
                                                Detail
                                            </a>
                                        </li>

                                        <li>
                                            <button
                                                type="button"
                                                class="dropdown-item editBtn"
                                                data-id="${row.id}"
                                                >
                                                Edit
                                            </button>
                                        </li>

                                        <li><hr class="dropdown-divider"></li>

                                        <li>
                                            <button
                                                type="submit"
                                                class="dropdown-item text-danger deleteBtn"
                                                data-id="${data.id}"
                                                data-title="${data.nama_tugas}"
                                                >
                                                <i class="fas fa-trash me-2"></i>
                                                Hapus
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            `
                        }
                    }
                ],
                order: [
                    [0, 'desc']
                ]
            });
        };

        function edit(id, data) {
            $('#editModal [name="id"]').val(id);
            $('#editModal [name="nama_tugas"]').val(data.nama_tugas);
            $('#editModal [name="karyawan"]').val(data.karyawan.id);
            $('#editModal [name="deadline"]').val(data.deadline);
            $('#editModal [name="catatan"]').val(data.catatan);

            const modal = new bootstrap.Modal(document.getElementById('editModal'));
            modal.show();
        }

        function detail(id, data) {
            let item = data;
            let html = `
            <div class="row g-3 mb-3">
                <div class="col-md-6"><div class="border rounded-3 p-3"><small class="text-muted">Office Boy</small><h6 class="mb-0 fw-semibold">${item.karyawan?.nama_lengkap || '-'}</h6></div></div>
                <div class="col-md-6"><div class="border rounded-3 p-3"><small class="text-muted">Pembuat</small><h6 class="mb-0 fw-semibold">${item.pembuat?.nama_lengkap || '-'}</h6></div></div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6"><div class="border rounded-3 p-3"><small class="text-muted">Nama Tugas</small><h6 class="mb-0 fw-semibold">${item.nama_tugas || '-'}</h6></div></div>
                <div class="col-md-6"><div class="border rounded-3 p-3"><small class="text-muted">Deadline</small><h6 class="mb-0 fw-semibold">${moment(item.deadline).format('DD-MM-YYYY HH:mm') || '-'}</h6></div></div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6"><div class="border rounded-3 p-3"><small class="text-muted">Catatan</small><h6 class="mb-0 fw-semibold">${item.catatan || '-'}</h6></div></div>
                <div class="col-md-6"><div class="border rounded-3 p-3"><small class="text-muted">Status</small><h6 class="mb-0 fw-semibold">${item.status || '-'}</h6></div></div>
            </ul><p class="mt-3">Tracking</p><ul class="list-group">`;
            (item.tracking || []).forEach(t => {
                html +=
                    `<li class="list-group-item"><strong>${moment(t.created_at).format('DD-MM-YYYY HH:mm')}</strong><br><small class="text-muted">${t.status}</small></li>`;
            });
            html += '</ul>';
            $('#detailContent').html(html);

            const modal = new bootstrap.Modal(document.getElementById('detailModal'));
            modal.show();
        }
    </script>
@endsection
