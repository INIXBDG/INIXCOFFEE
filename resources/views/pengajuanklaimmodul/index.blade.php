@extends('layouts.app')

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

        <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="approveModalLabel">Approve Klaim Modul</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="approveForm" method="POST">
                            @csrf
                            @method('PUT')
                            <p>Apakah Disetujui?</p>
                            <div class="btn-group" role="group" aria-label="Approval Options">
                                <input type="radio" class="btn-check" name="approval" id="approveYes" value="1"
                                    autocomplete="off" checked>
                                <label class="btn btn-outline-primary" for="approveYes"
                                    onclick="togglePriceField(true)">Ya</label>
                                <input type="radio" class="btn-check" name="approval" id="approveNo" value="2"
                                    autocomplete="off">
                                <label class="btn btn-outline-danger" for="approveNo"
                                    onclick="togglePriceField(false)">Tidak</label>
                            </div>

                            <div class="mt-3" id="priceInput">
                                <label for="price" class="form-label">Harga Modul (Rp)</label>
                                <input type="text" class="form-control format-currency" id="price" name="price"
                                    inputmode="numeric" autocomplete="off">
                            </div>

                            <div class="mt-3" id="alasanInput" style="display: none;">
                                <label for="alasan" class="form-label">Alasan Penolakan</label>
                                <textarea class="form-control" id="alasan" name="alasan" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" form="approveForm" class="btn btn-primary">Simpan</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="d-flex justify-content-end">
                    <a href="{{ route('pengajuanklaimmodul.create') }}" class="btn btn-md click-primary mx-4">
                        <img src="{{ asset('icon/plus.svg') }}" width="30px"> Ajukan Klaim Modul
                    </a>
                </div>

                <div class="card m-4">
                    <div class="card-body table-responsive">
                        <h3 class="card-title text-center my-1">{{ __('Data Klaim Modul') }}</h3>
                        <table class="table table-striped" id="klaimModulTable"
                            style="overflow-x: scroll; max-width: 150%;">
                            <thead>
                                <tr>
                                    <th scope="col">Tanggal Pengajuan</th>
                                    <th scope="col">Judul Modul</th>
                                    <th scope="col">Kategori</th>
                                    <th scope="col">Pembuat</th>
                                    <th scope="col">Instruktur</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Harga</th>
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
    </style>

    @push('js')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
        <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            $(document).ready(function () {
                loadData();

                // Format input Rupiah saat mengetik
                $('#price').on('input', function () {
                    let angka = $(this).val().replace(/[^0-9]/g, '');
                    $(this).val(angka ? 'Rp. ' + formatRupiah(angka) : '');
                });

                // Submit approve form
                $('#approveForm').on('submit', function (e) {
                    e.preventDefault();

                    let form = $(this);
                    let actionUrl = form.attr('action');

                    let formData = form.serializeArray();

                    // Bersihkan format rupiah
                    formData.forEach(function (field) {
                        if (field.name === 'price') {
                            field.value = field.value.replace(/[^0-9]/g, '');
                        }
                    });

                    $('#loadingModal').modal('show');

                    $.ajax({
                        url: actionUrl,
                        type: 'POST',
                        data: $.param(formData),

                        success: function () {
                            $('#loadingModal').modal('hide');
                            $('#approveModal').modal('hide');

                            loadData();

                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Klaim Modul berhasil diproses'
                            });
                        },

                        error: function () {
                            $('#loadingModal').modal('hide');

                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Gagal menyimpan data.'
                            });
                        }
                    });
                });
            });

            // Format angka ke ribuan
            function formatRupiah(angka) {
                return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            // Load DataTable
            function loadData() {
                var tahun = $('#tahun').val() || new Date().getFullYear();
                var bulan = $('#bulan').val() || 'All';

                if ($.fn.DataTable.isDataTable('#klaimModulTable')) {
                    $('#klaimModulTable').DataTable().destroy();
                }

                $('#klaimModulTable').DataTable({
                    destroy: true,
                    autoWidth: false,

                    ajax: {
                        url: "{{ route('pengajuanklaimmodul.data', ['month' => ':month', 'year' => ':year']) }}"
                            .replace(':month', bulan)
                            .replace(':year', tahun),

                        type: "GET",
                        dataSrc: "data",

                        error: function () {
                            $('#loadingModal').modal('hide');

                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Memuat Data',
                                text: 'Terjadi kesalahan.'
                            });
                        }
                    },

                    columns: [
                        {
                            data: "created_at",
                            render: function (data) {
                                moment.locale('id');
                                return moment(data).format('dddd, DD MMMM YYYY');
                            }
                        },
                        { data: "module.title" },
                        { data: "module.category" },
                        { data: "module.karyawan.nama_lengkap" },

                        {
                            data: "module.instructors",
                            defaultContent: "-",
                            render: function (data) {
                                if (data && Array.isArray(data) && data.length > 0) {
                                    return data.map(item => {
                                        if (item.karyawan && item.karyawan.nama_lengkap) {
                                            return item.karyawan.nama_lengkap;
                                        }
                                        return item.username || item.email || '-';
                                    }).join(', ');
                                }
                                return '-';
                            }
                        },

                        { data: "status" },

                        {
                            data: "price",
                            render: function (data) {
                                if (data !== null && data !== undefined) {
                                    return 'Rp. ' + formatRupiah(parseInt(data));
                                }
                                return '-';
                            }
                        },

                        {
                            data: null,
                            orderable: false,
                            searchable: false,

                            render: function (data, type, row) {
                                let actions = `
                                <div class="dropdown">
                                    <button class="btn btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Action
                                    </button>
                                    <div class="dropdown-menu">
                            `;

                                let jabatan = '{{ auth()->user()->karyawan->jabatan }}';
                                let kodeKaryawan = '{{ auth()->user()->karyawan->kode_karyawan }}';
                                let moduleKode = row.module?.karyawan?.kode_karyawan ?? '';
                                let status = row.status ?? '';

                                if (
                                    jabatan === 'Education Manager' &&
                                    status === 'Diajukan dan Sedang Ditinjau oleh Education Manager'
                                ) {
                                    actions += `
                                    <button type="button" class="dropdown-item"
                                        onclick="openApproveModal(${row.id})">
                                        <img src="{{ asset('icon/check-circle.svg') }}" width="16">
                                        Approve
                                    </button>
                                `;
                                }

                                actions += `
                                <a href="{{ url('/pengajuanklaimmodul') }}/${row.id}" class="dropdown-item">
                                    <img src="{{ asset('icon/clipboard-primary.svg') }}" width="16">
                                    Detail
                                </a>
                            `;

                                if (
                                    kodeKaryawan == moduleKode &&
                                    !status.includes('Disetujui') &&
                                    !status.includes('Ditolak')
                                ) {
                                    actions += `
                                    <a href="{{ url('/pengajuanklaimmodul') }}/${row.id}/edit"
                                       class="dropdown-item">
                                        <img src="{{ asset('icon/edit.svg') }}" width="16">
                                        Edit
                                    </a>
                                `;
                                }

                                if (
                                    kodeKaryawan == moduleKode ||
                                    jabatan == 'Education Manager' ||
                                    jabatan == 'GM'
                                ) {
                                    actions += `
                                    <form method="POST"
                                          action="{{ url('/pengajuanklaimmodul') }}/${row.id}"
                                          onsubmit="return confirm('Yakin hapus?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="dropdown-item">
                                            <img src="{{ asset('icon/trash-danger.svg') }}" width="16">
                                            Hapus
                                        </button>
                                    </form>
                                `;
                                }

                                actions += `</div></div>`;
                                return actions;
                            }
                        }
                    ],

                    order: [[0, 'desc']],

                    drawCallback: function () {
                        $('#loadingModal').modal('hide');
                    }
                });
            }

            // Buka modal approve
            function openApproveModal(id) {
                $('#approveForm').attr(
                    'action',
                    "{{ url('/pengajuanklaimmodul') }}/" + id + "/approve"
                );

                $('#approveModal').modal('show');
                togglePriceField(true);
            }

            // Toggle field approve / reject
            function togglePriceField(show) {
                $('#priceInput').toggle(show);
                $('#alasanInput').toggle(!show);

                if (show) {
                    $('#price').attr('required', true);
                    $('#alasan').removeAttr('required');
                } else {
                    $('#price').removeAttr('required');
                    $('#alasan').attr('required', true);
                }
            }
        </script>
    @endpush
@endsection