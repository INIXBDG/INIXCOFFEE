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
            @can('Create Materi')
                <a href="{{ route('materi.create') }}" class="btn btn-md click-primary mx-4" data-toggle="tooltip" data-placement="top" title="Tambah User"><img src="{{ asset('icon/plus.svg') }}" class="" width="30px"> Data Materi</a>
            @endcan
            </div>
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Data Materi') }}</h3>
                    <table class="table table-striped" id="materitable">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">No</th>
                                <th scope="col">Nama Materi</th>
                                <th scope="col">Kode Materi</th>
                                <th scope="col">Durasi Materi</th>
                                <th scope="col">Kategori Materi</th>
                                <th scope="col">Vendor</th>
                                <th scope="col">Status</th>
                                <th scope="col">Keterangan</th>
                                <th scope="col">Tipe Materi</th>
                                <th scope="col">Silabus</th>
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

<script>
    $(document).ready(function(){
        var tableIndex = 1;
        $.fn.dataTable.ext.order['custom-status'] = function(settings, col) {
            return this.api().column(col, {order: 'index'}).nodes().map(function(td, i) {
                var status = $(td).text().trim();
                switch (status) {
                    case 'Aktif':
                        return 1;
                    case 'Retired':
                        return 2;
                    case 'Nonaktif':
                        return 3;
                    case '-':
                    case '':
                        return 4;
                    default:
                        return 5; // Untuk nilai yang tidak dikenali
                }
            });
        };

        $('#materitable').DataTable({
            "dom": 'Bfrtip',
            "buttons": [
                        {
                            extend: 'excel',
                            text: 'Export to Excel',
                            exportOptions: {
                                columns: [ 1, 2, 3, 4, 5, 6, 7 ] // Kolom yang akan diekspor ke Excel
                            },
                        },
                        {
                            extend: 'pdf',
                            text: 'Export to PDF',
                            exportOptions: {
                                columns: [ 1, 2, 3, 4, 5, 6 ] // Kolom yang akan diekspor ke PDF
                            },
                            customize: function(doc) {
                                doc.content[1].table.widths = ['*', '*', '*', '*']; // Menyesuaikan lebar kolom
                                doc.content.splice(0, 1, {
                                    text: 'Inixindo E-Office Data Materi',
                                    fontSize: 12,
                                    alignment: 'center',
                                    margin: [0, 0, 0, 12] // Margin dari header
                                });
                                doc['footer'] = function(currentPage, pageCount) {
                                    return {
                                        text: 'Data Materi ' + currentPage.toString() + ' of ' + pageCount,
                                        alignment: 'center',
                                        margin: [0, 0, 0, 12] // Margin dari footer
                                    };
                                };
                            }
                        }
            ],
            "ajax": {
                "url": "{{ route('getMateri') }}", // URL API untuk mengambil data
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
                {   "data": null,
                    "render": function (data){
                        return tableIndex++
                    }
                },
                {   "data": 'id',
                    "visible": false
                },
                {"data": "nama_materi"},
                {"data": "kode_materi"},
                {
                    "data": null,
                    "render": function(data, type, row) {
                        if(data.durasi){
                            return data.durasi + ' hari';
                        }else{
                            return '';
                        }
                    }
                },
                {"data": "kategori_materi"},
                {"data": "vendor"},
                {
                    "data": "status",
                    "render": function(data, type, row) {
                        if (data === null) {
                                return '-';
                        } else {
                                return data;
                        }

                    }
                },
                {"data": "keterangan"},
                {"data": "tipe_materi"},
                {
                    "data": null,
                    "render": function(data, type, row) {
                        if (data.silabus === null) {
                                return '<a class="btn btn-sm click-primary disabled" href="" data-toggle="tooltip" data-placement="top" title="Lihat Silabus"> Lihat Silabus</a>';
                        } else {
                                return '<a class="btn btn-sm click-primary" href="{{ url('/storage') }}/' + row.silabus + '" data-toggle="tooltip" data-placement="top" title="Lihat Silabus"> Lihat Silabus</a>';
                        }

                    }
                },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        var actions = "";
                            actions += '@if (auth()->user()->can('Edit Materi') || auth()->user()->can('Delete Materi'))'
                            actions += '<div class="dropdown">';
                            actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                            actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                            actions += '@can('Edit Materi')'
                            actions += '<a class="dropdown-item" href="{{ url('/materi') }}/' + row.id + '/edit" data-toggle="tooltip" data-placement="top" title="Edit Peserta"><img src="{{ asset('icon/edit-warning.svg') }}" class=""> Edit</a>';
                            actions += '@endcan'
                            actions += '@can('Edit Materi Status (Education Manager)')'
                            actions += '<a href="{{ url('/editstatusmateri') }}/'+ row.id + '" class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Edit Status"><img src="{{ asset('icon/check-circle.svg') }}" class=""> Edit Status</a>';
                            actions += '@endcan'
                            actions += '@can('Delete Materi')'
                            actions += '<form onsubmit="return confirm(\'Apakah Anda Yakin ?\');" action="{{ url('/materi') }}/' + row.id + '" method="POST">';
                            actions += '@csrf';
                            actions += '@method('DELETE')';
                            actions += '<button type="submit" class="dropdown-item"><img src="{{ asset('icon/trash-danger.svg') }}" class=""> Hapus</button>';
                            actions += '</form>';
                            actions += '@endcan'
                            actions += '</div>';
                            actions += '</div>';
                            actions += '@else'
                            actions += '<div class="dropdown">';
                            actions += '<button class="btn dropdown-toggle disabled" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                            actions += '</div>'
                            actions += '@endif';
                        return actions;
                    }
                }
            ],
            "order": [[7, 'asc']],
            "columnDefs": [
                {
                    "targets": 7, // Indeks kolom status
                    "orderData": 7,
                    "orderDataType": "custom-status"
                }
            ],

        });
    });
</script>
@endpush
@endsection
