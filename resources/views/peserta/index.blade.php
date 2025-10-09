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
            <div class="d-flex justify-content-end">
                @can('Create Peserta')
                    <a href="{{ route('peserta.create') }}" class="btn btn-md click-primary mx-4" data-toggle="tooltip" data-placement="top" title="Tambah peserta"><img src="{{ asset('icon/plus.svg') }}" class="" width="30px"> Data Peserta</a>
                @endcan
                @if (auth()->user()->jabatan == 'Customer Care' || auth()->user()->jabatan == 'Admin Holding' || auth()->user()->jabatan == 'Education Manager' || auth()->user()->jabatan == 'Office Manager' || auth()->user()->jabatan == 'Koordinator Office' || auth()->user()->jabatan == 'SPV Sales' || auth()->user()->jabatan == 'Adm Sales' || auth()->user()->jabatan == 'GM')
                    <a href="{{ route('peserta.exportExcel') }}" class="btn btn-success">Export to Excel</a>
                    <a href="{{ route('peserta.exportPDF') }}" class="btn btn-danger">Export to PDF</a>
                @endif
                @if (auth()->user()->jabatan == 'Sales')
                    <a href="{{ route('peserta.exportExcels') }}" class="btn btn-success">Export to Excel</a>
                    <a href="{{ route('peserta.exportPDFs') }}" class="btn btn-danger">Export to PDF</a>
                @endif
            </div>
            <div class="card m-4" id="peserta">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Data Peserta') }}</h3>
                    <table class="table table-striped" id="pesertatable">
                        <thead>
                          <tr>
                            <th scope="col">Nama</th>
                            <th scope="col">Email</th>
                            <th scope="col">id</th>
                            <th scope="col">Jenis Kelamin</th>
                            <th scope="col">Nomor Handphone</th>
                            <th scope="col">Alamat</th>
                            <th scope="col">Perusahaan/Instansi</th>
                            <th scope="col">Tanggal Lahir</th>
                          </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card m-4" id="pesertaall">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Data Peserta') }}</h3>
                    <table class="table table-striped" id="pesertaalltable">
                        <thead>
                          <tr>
                            <th scope="col">Nama</th>
                            <th scope="col">Email</th>
                            <th scope="col">Jenis Kelamin</th>
                            <th scope="col">Nomor Handphone</th>
                            <th scope="col">Alamat</th>
                            <th scope="col">Perusahaan/Instansi</th>
                            <th scope="col">Tanggal Lahir</th>
                            <th scope="col">Created_at</th>
                            {{-- @if (auth()->user()->jabatan == 'Programmer') --}}
                            <th scope="col">Aksi</th>
                            {{-- @endif --}}
                          </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card m-4" id="pesertaSales">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Data Peserta') }}</h3>
                    <table class="table table-striped" id="pesertatableSales">
                        <thead>
                          <tr>
                            <th scope="col">Nama</th>
                            <th scope="col">Email</th>
                            <th scope="col">id</th>
                            <th scope="col">id</th>
                            <th scope="col">Jenis Kelamin</th>
                            <th scope="col">Nomor Handphone</th>
                            <th scope="col">Alamat</th>
                            <th scope="col">Perusahaan/Instansi</th>
                            <th scope="col">Tanggal Lahir</th>
                            <th scope="col">Aksi</th>
                          </tr>
                        </thead>
                        <tbody>

                        </tbody>
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

    .loader-txt {
    p {
        font-size: 13px;
        color: #666;
        small {
        font-size: 11.5px;
        color: #999;
        }
    }
    }
</style>
@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.5.6/css/buttons.dataTables.min.css">
<script src="https://cdn.datatables.net/buttons/1.5.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>

<script>
    $(document).ready(function(){
        var idInstruktur = "{{ auth()->user()->id_instruktur }}";
        var idSales = "{{ auth()->user()->id_sales }}";
		var jabatan = "{{ auth()->user()->jabatan }}";
        console.log(idInstruktur);
        console.log(idSales);
        if(idInstruktur == 'AD' || jabatan == "Technical Support"){
            var idInstruktur = "";
        }
        if(idSales == 'AM'){
                var idSales = "";
            }
        // console.log(idSales);
        if(idInstruktur )
		{
            $('#peserta').show();
            $('#pesertaall').hide();
            $('#pesertaSales').hide();
        }else if(idSales){
            $('#peserta').hide();
            $('#pesertaall').hide();
            $('#pesertaSales').show();
        }
        else{
           $('#peserta').hide();
           $('#pesertaall').show();
           $('#pesertaSales').hide();

        }
        $('#pesertaalltable').DataTable({
            "ajax": {
                "url": "{{ route('getPesertaall') }}", // URL API untuk mengambil data
                "type": "GET",
                "beforeSend": function () {
                    $('#loadingModal').modal('show');
                    $('#loadingModal').on('show.bs.modal', function () {
                        $('#loadingModal').removeAttr('inert');
                    });
                },
                "complete": function () {
                    setTimeout(() => {
                        $('#loadingModal').modal('hide');
                        $('#loadingModal').on('hidden.bs.modal', function () {
                            $('#loadingModal').attr('inert', true);
                        });
                    }, 1000);
                }
            },
            "columns": [
                {"data": "nama"},
                {"data": "email"},
                {
                    "data": null,
                    "render": function(data) {
                        return data.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
                    }
                },
                {"data": "no_hp"},
                {"data": "alamat"},
                {"data": "perusahaan.nama_perusahaan"},
                {
                    "data": "tanggal_lahir",
                    "render": function(data) {
                        return moment(data).format('DD MMMM YYYY');
                    }
                },
                {
                    "data": "latest_registrasi.created_at",
                    "visible": false,
                    "render": function(data, type, row) {
                        return data === null || data === undefined ? '-' : data;
                    }
                },

                {
                    "data": null,
                    "render": function(data, type, row) {
                        var actions = "";
                            actions += '@if (auth()->user()->can('Edit Peserta'))'
                            actions += '<div class="dropdown">';
                            actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                            actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                            actions += '<a class="dropdown-item" href="{{ url('/peserta') }}/' + row.id + '/edit" data-toggle="tooltip" title="Edit Peserta"><img src="{{ asset('icon/edit-warning.svg') }}" class=""> Edit</a>';
                            actions += '</div>';
                            actions += '</div>';
                            actions += '@else'
                            actions += '<div class="dropdown">';
                            actions += '<button class="btn dropdown-toggle disabled" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                            actions += '</div>';
                            actions += '@endif'
                    
                        return actions;
                    }
                }
            ],
            "order": [[7, 'desc']],
            "columnDefs": [{"targets": [7], "type": "date"}],
        });

        $('#pesertatable').DataTable({
            "ajax": {
                "url": "{{ route('getRegistrasiall') }}", // URL API untuk mengambil data
                "type": "GET",
                "beforeSend": function () {
                    $('#loadingModal').modal('show');
                    $('#loadingModal').on('show.bs.modal', function () {
                        $('#loadingModal').removeAttr('inert');
                    });
                },
                "complete": function () {
                    setTimeout(() => {
                        $('#loadingModal').modal('hide');
                        $('#loadingModal').on('hidden.bs.modal', function () {
                            $('#loadingModal').attr('inert', true);
                        });
                    }, 1000);
                }
            },
            "columns": [
                {"data": "peserta.nama"},
                {"data": "peserta.email"},
                {
                    "data": "id_instruktur",
                    "visible": false
                },
                {
                    "data": "peserta.jenis_kelamin",
                    "render": function(data) {
                        return data == 'L' ? 'Laki-laki' : 'Perempuan';
                    }
                },
                {"data": "peserta.no_hp"},
                {"data": "peserta.alamat"},
                {"data": "peserta.perusahaan.nama_perusahaan"},
                {
                    "data": "peserta.tanggal_lahir",
                    "render": function(data) {
                        moment.locale('id')
                        return moment(data).format('DD MMMM YYYY');
                    }
                }
            ],
            "initComplete": function() {
                this.api().columns(2).search(idInstruktur).draw();
                // this.api().columns(3).search(idSales).draw();
            }
        });

        $('#pesertatableSales').DataTable({
            "dom": 'Bfrtip',
           "buttons": [
                        {
                            extend: 'excel',
                            text: 'Export to Excel',
                            exportOptions: {
                                columns: [ 1, 2, 3, 4 ] // Kolom yang akan diekspor ke Excel
                            },
                            filename: 'Inixindo E-office Data Peserta', // Specify the filename here
                        },
                        {
                            extend: 'pdf',
                            text: 'Export to PDF',
                            exportOptions: {
                                columns: [ 1, 2, 3, 4 ] // Kolom yang akan diekspor ke PDF
                            },
                            customize: function(doc) {
                                doc.content[1].table.widths = ['*', '*', '*', '*']; // Menyesuaikan lebar kolom
                                doc.content.splice(0, 1, {
                                    text: 'Inixindo E-Office Data Peserta',
                                    fontSize: 12,
                                    alignment: 'center',
                                    margin: [0, 0, 0, 12] // Margin dari header
                                });
                                doc['footer'] = function(currentPage, pageCount) {
                                    return {
                                        text: 'Data Peserta ' + currentPage.toString() + ' of ' + pageCount,
                                        alignment: 'center',
                                        margin: [0, 0, 0, 12] // Margin dari footer
                                    };
                                };
                            }
                        }
                    ],
                "ajax": {
                    "url": "{{ route('getPesertaall') }}", // URL API untuk mengambil data
                    "type": "GET",
                    "beforeSend": function () {
                        $('#loadingModal').modal('show');
                        $('#loadingModal').on('show.bs.modal', function () {
                            $('#loadingModal').removeAttr('inert');
                        });
                    },
                    "complete": function () {
                        setTimeout(() => {
                            $('#loadingModal').modal('hide');
                            $('#loadingModal').on('hidden.bs.modal', function () {
                                $('#loadingModal').attr('inert', true);
                            });
                        }, 1000);
                    }
                },
                "columns": [
                    {"data": "nama"},
                    {"data": "email"},
                    {
                        "data": "id",
                        "visible": false
                    },
                    {
                        "data": "perusahaan.sales_key",
                        "visible": false
                    },
                    {
                        "data": null,
                        "render": function(data) {
                            return data.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
                        }
                    },
                    {"data": "no_hp"},
                    {"data": "alamat"},
                    {"data": "perusahaan.nama_perusahaan"},
                    {
                        "data": "tanggal_lahir",
                        "render": function(data) {
                            return moment(data).format('DD MMMM YYYY');
                        }
                    },
                    {
                    "data": null,
                    "render": function(data, type, row) {
                        var actions = "";
                            actions += '@if (auth()->user()->can('Edit Peserta'))'
                            actions += '<div class="dropdown">';
                            actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                            actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                            actions += '<a class="dropdown-item" href="{{ url('/peserta') }}/' + row.id + '/edit" data-toggle="tooltip" title="Edit Peserta"><img src="{{ asset('icon/edit-warning.svg') }}" class=""> Edit</a>';
                            actions += '</div>';
                            actions += '</div>';
                            actions += '@else'
                            actions += '<div class="dropdown">';
                            actions += '<button class="btn dropdown-toggle disabled" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                            actions += '</div>';
                            actions += '@endif'
                        return actions;
                    }
                }
                    

                ],
                "initComplete": function() {
                        this.api().columns(3).search(idSales).draw();
                }
        });
    });
</script>
@endpush
@endsection
