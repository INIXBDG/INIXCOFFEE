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

        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card m-4">
                    <div class="card-body table-responsive">
                        <h3 class="card-title text-center my-1">{{ __('Data Instruktur') }}</h3>

                        <div class="mt-4">
                            <table class="table table-striped w-100" id="instrukturTable">
                                <thead>
                                    <tr>
                                        <th scope="col">ID Instruktur</th>
                                        <th scope="col">Nama Lengkap</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Jabatan</th>
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
            to { -webkit-transform: rotate(360deg); }
        }

        @-webkit-keyframes spin {
            to { -webkit-transform: rotate(360deg); }
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
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            $(document).ready(function () {
                loadData();
            });

            function loadData() {
                $('#loadingModal').modal('show');

                if ($.fn.DataTable.isDataTable('#instrukturTable')) {
                    $('#instrukturTable').DataTable().destroy();
                }

                $('#instrukturTable').DataTable({
                    destroy: true,
                    autoWidth: false,
                    responsive: true,
                    ajax: {
                        url: "{{ route('cv-instruktur.data') }}",
                        type: "GET",
                        dataSrc: "data",
                        error: function () {
                            $('#loadingModal').modal('hide');
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Memuat Data',
                                text: 'Terjadi kesalahan pada server.'
                            });
                        }
                    },
                    columns: [
                        { data: "id_instruktur" },
                        {
                            data: "karyawan.nama_lengkap",
                            defaultContent: "-",
                            render: function (data) {
                                return data !== null && data !== undefined ? data : '-';
                            }
                        },
                        {
                            data: "karyawan.email",
                            defaultContent: "-",
                            render: function (data) {
                                return data !== null && data !== undefined ? data : '-';
                            }
                        },
                        {
                            data: "jabatan",
                            defaultContent: "-"
                        },
                        {
                            data: null,
                            orderable: false,
                            searchable: false,
                            render: function (data, type, row) {
                                if (row !== null && row !== undefined && row.id !== undefined) {
                                    return `
                                        <a href="{{ url('/cv-instruktur') }}/${row.id}" class="btn btn-sm btn-primary">
                                            Detail
                                        </a>
                                    `;
                                }
                                return '-';
                            }
                        }
                    ],
                    order: [[0, 'asc']],
                    drawCallback: function () {
                        $('#loadingModal').modal('hide');
                    }
                });
            }
        </script>
    @endpush
@endsection
