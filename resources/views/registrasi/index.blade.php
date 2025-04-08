@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
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
        {{-- <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a> --}}
        <div class="col-md-12">
            <div class="d-flex justify-content-end">
                @can('Create Registrasi')
                    <a href="{{ route('registrasi.create') }}" class="btn btn-md click-primary mx-4" data-toggle="tooltip" data-placement="top" title="Tambah registrasi"><img src="{{ asset('icon/plus.svg') }}" class="" width="30px"> Registrasi Peserta</a>
                @endcan
                @if (auth()->user()->jabatan == 'Customer Care' || auth()->user()->jabatan == 'Admin Holding' || auth()->user()->jabatan == 'Education Manager' || auth()->user()->jabatan == 'Office Manager' || auth()->user()->jabatan == 'Koordinator Office' || auth()->user()->jabatan == 'SPV Sales' || auth()->user()->jabatan == 'Adm Sales' || auth()->user()->jabatan == 'GM')
                    <a href="{{ route('registrasi.exportExcel') }}" class="btn btn-success">Export to Excel</a>
                    <a href="{{ route('registrasi.exportPDF') }}" class="btn btn-danger">Export to PDF</a>
                @endif
                @if (auth()->user()->jabatan == 'Sales' || auth()->user()->jabatan == 'Instruktur')
                    <a href="{{ route('registrasi.exportExcels') }}" class="btn btn-success">Export to Excel</a>
                    <a href="{{ route('registrasi.exportPDFs') }}" class="btn btn-danger">Export to PDF</a>
                @endif
            </div>
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Data Registrasi') }}</h3>
                    <table class="table table-striped" id="registrasitable">
                        <thead>
                          <tr>
                            <th scope="col">Nama Peserta</th>
                            <th scope="col">Perusahaan/Instansi</th>
                            <th scope="col">Materi Pelatihan</th>
                            <th scope="col">Periode Pelatihan</th>
                            <th scope="col">Instruktur</th>
                            <th scope="col">Sales</th>
                            <th scope="col">Souvenir</th>
                            <th scope="col">created_at</th>
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

        if(idInstruktur == 'AD'){
            var idInstruktur = "";
        }
        if(idSales == 'AM'){
                var idSales = "";
            }

        $('#registrasitable').DataTable({
            "dom": 'Bfrtip',
            "buttons": [
                {
                    extend: 'excel',
                    text: 'Export to Excel',
                    exportOptions: {
                        columns: [ 0, 1, 2, 3, 4, 5 ] // Kolom yang akan diekspor ke Excel
                    },
                    filename: 'Inixindo E-office Data Registrasi', // Specify the filename here
                },
                {
                    extend: 'pdf',
                    text: 'Export to PDF',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5] // Kolom yang akan diekspor ke PDF
                    },
                    filename: 'Inixindo E-office Data Registrasi', // Specify the filename here
                    customize: function(doc) {
                        doc.content[1].table.widths = ['*', '*', '*', '*', '*', '*']; // Menyesuaikan lebar kolom
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
                "url": "{{ route('getRegistrasiall') }}",
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
                },
                "error": function(xhr, error, code) {
                    console.log("Error Code: " + code);
                    console.log("Error Message: " + error);
                    console.log("Response Text: " + xhr.responseText);
                }
            },
            "columns": [
                {
                    "data": "peserta.nama",
                    "render": function(data) {
                        return data ? data : '-';
                    }
                },
                {
                    "data": "peserta.perusahaan.nama_perusahaan",
                    "render": function(data) {
                        return data ? data : '-';
                    }
                },
                {
                    "data": "materi.nama_materi",
                    "render": function(data) {
                        return data ? data : '-';
                    }
                },
                {
                    "data": null,
                    "render": function(data) {
                        moment.locale('id');
                        if (data && data.rkm && data.rkm.tanggal_awal && data.rkm.tanggal_akhir) {
                            return moment(data.rkm.tanggal_awal).format('DD MMMM YYYY') + ' s/d ' + moment(data.rkm.tanggal_akhir).format('DD MMMM YYYY');
                        }
                        return '-';
                    }
                },
                {
                    "data": "id_instruktur",
                    "render": function(data) {
                        return data ? data : '-';
                    }
                },
                {
                    "data": "id_sales",
                    "render": function(data) {
                        return data ? data : '-';
                    }
                },
                {
                    "data": "souvenirpeserta.souvenir.nama_souvenir",
                    "render": function(data) {
                        if (!data) {
                            return '-';
                        } else {
                            return data;
                        }
                    }
                },
                {
                    "data": null,
                    "render": function(data) {
                        moment.locale('id');
                        return data && data.tanggal_awal ? moment(data.tanggal_awal).format('DD MMMM YYYY') : '-';
                    },
                    "visible": false,
                },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        var actions = "";
                                actions += '@if (auth()->user()->can('Edit Registrasi') || auth()->user()->can('Delete Registrasi'))'
                                actions += '<div class="dropdown">';
                                actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                                actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                                actions += '@can('Edit Registrasi')';
                                actions += '<a class="dropdown-item" href="{{ url('/registrasi') }}/' + row.id + '/edit"><img src="{{ asset('icon/edit-warning.svg') }}" class=""> Edit</a>';
                                actions += '@endcan';
                                actions += '@can('Delete Registrasi')';
                                actions += '<form onsubmit="return confirm(\'Apakah Anda Yakin ?\');" action="{{ url('/registrasi') }}/' + row.id + '" method="POST">';
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
                },
            ],
            "order": [[7, 'desc']],
            "columnDefs": [{"targets": [7], "type": "date"}],
            "initComplete": function() {
                this.api().columns(4).search(idInstruktur).draw();
                this.api().columns(5).search(idSales).draw();
            }
        });



    });
</script>
@endpush
@endsection
