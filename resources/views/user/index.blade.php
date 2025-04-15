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
                @can('Create DataKaryawan')
                    <a href="/user/register" class="btn btn-md click-primary mx-4" data-toggle="tooltip" data-placement="top" title="Tambah User"><img src="{{ asset('icon/user-plus.svg') }}" class="" width="30px"> Tambah Karyawan</a>
                @endcan
            </div>
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Data Karyawan') }}</h3>
                    <table class="table table-striped" id="usertable">
                        <thead>
                          <tr>
                            <th scope="col">No</th>
                            <th scope="col">NIP</th>
                            <th scope="col">Nama Lengkap</th>
                            <th scope="col">Jabatan</th>
                            <th scope="col">Divisi</th>
                            <th scope="col">Kode Karyawan</th>
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
@push('js')
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
        var tableIndex = 1;
        $('#usertable').DataTable({
            "dom": 'Bfrtip',
            "buttons": [
                        {
                            extend: 'excel',
                            text: 'Export to Excel',
                            exportOptions: {
                                columns: [ 1, 2, 3, 4 ] // Kolom yang akan diekspor ke Excel
                            },
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
                                    text: 'Inixindo E-Office Data User',
                                    fontSize: 12,
                                    alignment: 'center',
                                    margin: [0, 0, 0, 12] // Margin dari header
                                });
                                doc['footer'] = function(currentPage, pageCount) {
                                    return {
                                        text: 'Data User ' + currentPage.toString() + ' of ' + pageCount,
                                        alignment: 'center',
                                        margin: [0, 0, 0, 12] // Margin dari footer
                                    };
                                };
                            }
                        }
            ],
            "ajax": {
                "url": "{{ route('getUserall') }}", // URL API untuk mengambil data
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
                {"data": "karyawan.nip"},
                {"data": "karyawan.nama_lengkap"},
                {"data": "karyawan.jabatan"},
                {"data": "karyawan.divisi"},
                {"data": "karyawan.kode_karyawan"},
                {
                "data": null,
                "render": function(data, type, row) {
                            var actions = "";
                                actions += '@if (auth()->user()->can('Delete DataKaryawan'))'
                                actions += '<div class="dropdown">';
                                actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                                actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                                actions += '<a class="dropdown-item" href="{{ url('/profile') }}/' + row.id + '" data-toggle="tooltip" data-placement="top" title="Detail User"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Detail</a>';
                                actions += '@can('Delete DataKaryawan')';
                                actions += '<form onsubmit="return confirm(\'Apakah Anda Yakin ?\');" action="{{ url('/user') }}/' + row.id + '" method="POST">';
                                actions += '@csrf';
                                actions += '@method('DELETE')';
                                actions += '<button type="submit" class="dropdown-item"><img src="{{ asset('icon/trash-danger.svg') }}" class=""> Hapus</button>';
                                actions += '</form>';
                                actions += '@endcan';
                                actions += '</div>';
                                actions += '</div>';
                                actions += '@else';
                                actions += '<div class="dropdown">';
                                actions += '<button class="btn dropdown-toggle disabled" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                                actions += '</div>';
                                actions += '@endif';
                    
                    return actions;
                }
            }
            ],
            "createdRow": function(row, data, dataIndex) {
                $("td", row).each(function() {
                    if ($(this).html() === "" || $(this).html() === null || $(this).html() === "null") {
                        $(this).html("-");
                    }
                });
            }
        });
    });

</script>
@endpush
@endsection
