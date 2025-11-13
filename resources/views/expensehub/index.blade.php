@extends('layouts.app')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

@section('content')
<div class="container-fluid">
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
</div>

<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="d-flex justify-content-end">
            @if (auth()->user()->jabatan === "SPV Sales" || auth()->user()->jabatan === "Sales")
            <a href="{{ route('expensehub.create') }}" class="btn btn-md click-primary mx-4" data-toggle="tooltip" data-placement="top" title="Ajukan Izin"><img src="{{ asset('icon/plus.svg') }}" class="" width="30px"> Tambah Pengajuan</a>
            @endif
        </div>
        <div class="card m-4 p-4">
            <div class="card-body table-responsive">
                <h3 class="card-title text-center my-1 mb-3">{{ __('Data Pengajuan Entertaint, Reimburst, & Oleh-Oleh') }}</h3>
                <table class="table" id="jabatantable">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Tanggal Pengajuan</th>
                            <th scope="col">Nama Karyawan</th>
                            <th scope="col">Divisi</th>
                            <th scope="col">Tipe</th>
                            <th scope="col">Status</th>
                            <th scope="col">Nama Pengajuan</th>
                            <th scope="col">Jumlah</th>
                            <th scope="col">Harga Pengajuan</th>
                            <th scope="col">Total Pengajuan</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="content_table">
                        <tr>
                            <td colspan="11">
                                <div class="text-muted text-center p-2">loading...</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>

<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="statusForm" action="{{ route('expensehub.approved') }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="status_input" id="statusInput" value="">
                <input type="hidden" name="id_expense" id="idExpense" value="">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Konfirmasi Status</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if (auth()->user()->jabatan === 'Finance & Accounting')
                    <div class="mb-3">
                        <select name="status_finance" id="status" class="form-select">
                            <option value="Sedang Dikonfirmasi oleh Bagian Finance kepada General Manager">Sedang Dikonfirmasi oleh Bagian Finance kepada General Manager</option>
                            <option value="Sedang Dikonfirmasi oleh Bagian Finance kepada Direksi">Sedang Dikonfirmasi oleh Bagian Finance kepada Direksi</option>
                            <option value="Finance Menunggu Approve Direksi">Finance Menunggu Approve Direksi</option>
                            <option value="Membuat Permintaan Ke Direktur Utama">Membuat Permintaan Ke Direktur Utama</option>
                            <option value="Pengajuan sedang dalam proses Pencairan">Pengajuan sedang dalam proses Pencairan</option>
                            <option value="Pencairan Sudah Selesai">Pencairan Sudah Selesai</option>
                            <option value="Selesai">Selesai</option>
                        </select>
                    </div>
                    @endif
                    <div class="btn-group mb-3" role="group" aria-label="Pilihan Status">
                        <button type="button" class="btn btn-outline-primary" data-status="1">Ya</button>
                        <button type="button" class="btn btn-outline-danger" data-status="0">Tidak</button>
                    </div>
                    <div class="mb-3" id="keteranganContainer" style="display: none;">
                        <label for="keterangan" class="form-label">Keterangan:</label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="3" placeholder="Masukkan keterangan..."></textarea>
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

    .detail-cell {
        vertical-align: top;
    }

    .detail-item {
        border-bottom: 1px solid #eee;
        padding-bottom: 4px;
        margin-bottom: 4px;
        word-wrap: break-word;
    }

    .detail-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
<script>
    $(document).ready(function() {
        loadData();

        $('#exampleModal').on('click', '.btn-outline-primary, .btn-outline-danger', function() {
            const status = $(this).data('status');
            $('#statusInput').val(status);

            if (status == '0') {
                $('#keteranganContainer').show();
            } else {
                $('#keteranganContainer').hide();
                $('#keterangan').val('');
            }
        });

        $('#statusForm').on('submit', function(e) {
            const status = $('#statusInput').val();
            const keterangan = $('#keterangan').val();

            if (status == '0' && keterangan.trim() === '') {
                alert('Keterangan wajib diisi jika status Tidak.');
                e.preventDefault();
                return false;
            }
        });
    });

    function loadData() {
        $.ajax({
            url: "{{ route('expensehub.get') }}",
            type: 'get',
            success: function(response) {
                const content = $('#content_table');
                content.empty();

                const data = response;
                let no = 1;
                let rowCounter = 0;

                if (data.length === 0) {
                    content.append(`
                        <tr>
                            <td colspan="11" class="text-center text-muted p-2">Tidak ada data!</td>
                        </tr>
                    `);
                    return;
                }

                data.forEach(function(item) {
                    const detail = item.detail || [];

                    let totalHargaPerItem = 0;
                    detail.forEach(d => {
                        const jumlah = parseInt(d.jumlah) || 0;
                        const harga = parseFloat(d.harga_pengajuan) || 0;
                        totalHargaPerItem += jumlah * harga;
                    });

                    const formattedTotalHargaPerItem = new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 2
                    }).format(totalHargaPerItem);

                    const stripeClass = (rowCounter % 2 === 0) ? 'table-light' : 'table-white';

                    let namaContent = '';
                    let jumlahContent = '';
                    let hargaContent = '';

                    if (detail.length > 0) {
                        detail.forEach(d => {
                            const jumlah = parseInt(d.jumlah) || 0;
                            const harga = parseFloat(d.harga_pengajuan) || 0;
                            const formattedHarga = new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR',
                                minimumFractionDigits: 2
                            }).format(harga);

                            namaContent += `<div class="detail-item detail-name">${d.nama_pengajuan}</div>`;
                            jumlahContent += `<div class="detail-item detail-jumlah">${jumlah}</div>`;
                            hargaContent += `<div class="detail-item detail-harga">${formattedHarga}</div>`;
                        });
                    } else {
                        namaContent = '<div class="text-muted">Tidak ada detail</div>';
                        jumlahContent = '<div class="text-muted">-</div>';
                        hargaContent = '<div class="text-muted">-</div>';
                    }

                    const userJabatan = "{{ auth()->user()->jabatan }}".trim();
                    let actionMenu = `
                        <div class="dropdown">
                            <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" style="background: transparent;" aria-expanded="false">
                                Actions
                            </button>
                            <ul class="dropdown-menu" id="action-menu-${item.id}">
                                <li><a class="dropdown-item" href="/expense-hub/show/${item.id}"><span class="me-2"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""></span> Detail</a></li>
                                    <li>
                                        <a href="#" class="dropdown-item btn-delete" data-id="${item.id}">
                                            <span class="me-2">
                                                <img src="{{ asset('icon/trash-danger.svg') }}">
                                            </span> Delete
                                        </a>
                                    </li>
                                <li><a class="dropdown-item" href="/expense-hub/invoice/${item.id}"><span class="me-2"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""></span> Upload Invoice</a></li>
                            </ul>
                        </div>
                    `;

                    const status = parseInt(item.status, 10);

                    console.log('DEBUG:', item.status, typeof item.status, userJabatan);


                    if (
                        (status === 0 && userJabatan === 'SPV Sales')
                    ) {
                        actionMenu = `
                            <div class="dropdown">
                                <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" style="background: transparent;" aria-expanded="false">
                                    Actions
                                </button>
                                <ul class="dropdown-menu" id="action-menu-${item.id}">
                                    <li><a class="dropdown-item approved-button" data-bs-toggle="modal" data-id="${item.id}" data-bs-target="#exampleModal"><span class="me-2"><img src="{{ asset('icon/check-circle.svg') }}" class=""></span> Approved</a></li>
                                    <li><a class="dropdown-item" href="/expense-hub/show/${item.id}"><span class="me-2"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""></span> Detail</a></li>
                                    <li>
                                        <a href="#" class="dropdown-item btn-delete" data-id="${item.id}">
                                            <span class="me-2">
                                                <img src="{{ asset('icon/trash-danger.svg') }}">
                                            </span> Delete
                                        </a>
                                    </li>
                                    <li><a class="dropdown-item" href="/expense-hub/invoice/${item.id}"><span class="me-2"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""></span> Upload Invoice</a></li>
                                </ul>
                            </div>
                        `;
                    } else if (
                        ((status === 1 || status === 2) && userJabatan === 'Finance &amp; Accounting')
                    ) {
                        actionMenu = `
                            <div class="dropdown">
                                <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" style="background: transparent;" aria-expanded="false">
                                    Actions
                                </button>
                                <ul class="dropdown-menu" id="action-menu-${item.id}">
                                    <li><a class="dropdown-item approved-button" data-bs-toggle="modal" data-id="${item.id}" data-bs-target="#exampleModal"><span class="me-2"><img src="{{ asset('icon/check-circle.svg') }}" class=""></span> Approved</a></li>
                                    <li><a class="dropdown-item" href="/expense-hub/show/${item.id}"><span class="me-2"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""></span> Detail</a></li>
                                    <li>
                                        <a href="#" class="dropdown-item btn-delete" data-id="${item.id}">
                                            <span class="me-2">
                                                <img src="{{ asset('icon/trash-danger.svg') }}">
                                            </span> Delete
                                        </a>
                                    </li>
                                    <li><a class="dropdown-item" href="/expense-hub/invoice/${item.id}"><span class="me-2"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""></span> Upload Invoice</a></li>
                                </ul>
                            </div>
                        `;
                    } else if (
                        (status === 4)
                    ) {
                        actionMenu = `
                            <div class="dropdown">
                                <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" style="background: transparent;" aria-expanded="false">
                                    Actions
                                </button>
                                <ul class="dropdown-menu" id="action-menu-${item.id}">
                                    <li><a class="dropdown-item" href="/expense-hub/show/${item.id}"><span class="me-2"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""></span> Detail</a></li>
                                    <li>
                                        <a href="#" class="dropdown-item btn-delete" data-id="${item.id}">
                                            <span class="me-2">
                                                <img src="{{ asset('icon/trash-danger.svg') }}">
                                            </span> Delete
                                        </a>
                                    </li> 
                                    </ul>
                            </div>
                        `;
                    } else if (
                        (status === 3)
                    ) {
                        actionMenu = `
                            <div class="dropdown">
                                <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" style="background: transparent;" aria-expanded="false">
                                    Actions
                                </button>
                                <ul class="dropdown-menu" id="action-menu-${item.id}">
                                    <li><a class="dropdown-item" href="/expense-hub/show/${item.id}"><span class="me-2"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""></span> Detail</a></li>
                                    <li>
                                        <a href="#" class="dropdown-item btn-delete" data-id="${item.id}">
                                            <span class="me-2">
                                                <img src="{{ asset('icon/trash-danger.svg') }}">
                                            </span> Delete
                                        </a>
                                    </li>     
                                   <li><a class="dropdown-item" href="/expense-hub/invoice/${item.id}"><span class="me-2"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""></span> Upload Invoice</a></li>
                                </ul>
                            </div>
                        `;
                    }


                    content.append(`
                        <tr class="${stripeClass}">
                            <td>${no++}</td>
                            <td>${item.tanggal_pengajuan}</td>
                            <td>${item.nama_karyawan}</td>
                            <td>${item.divisi}</td>
                            <td>${item.tipe}</td>
                            <td>${item.tracking}</td>
                            <td class="detail-cell">${namaContent}</td>
                            <td class="detail-cell">${jumlahContent}</td>
                            <td class="detail-cell">${hargaContent}</td>
                            <td>${formattedTotalHargaPerItem}</td>
                            <td>${actionMenu}</td>
                        </tr>
                    `);

                    rowCounter++;
                });

                if ($.fn.DataTable.isDataTable('#jabatantable')) {
                    $('#jabatantable').DataTable().destroy();
                }
                $('#jabatantable').DataTable({
                    "pageLength": 10,
                    "lengthChange": true,
                    "searching": true,
                    "ordering": true,
                    "info": true,
                    "autoWidth": false,
                    "responsive": true,
                });
            },
            error: function(xhr) {
                console.error(xhr.responseText);
            }
        });
    }

    $(document).on('click', '.approved-button', function() {
        const id = $(this).data('id');
        $('#idExpense').val(id);
    });

    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const url = `/expense-hub/destroy/${id}`;

        Swal.fire({
            title: 'Apakah kamu yakin?',
            text: "Data ini akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });
</script>
@endpush
@endsection