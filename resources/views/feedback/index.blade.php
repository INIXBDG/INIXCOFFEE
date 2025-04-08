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
        <div class="col-md-12">
            <div class="d-flex justify-content-end">
                @can('Create Feedback')
                    <a href="{{ route('nilaifeedback.create') }}" class="btn btn-md click-primary mx-4" data-toggle="tooltip" data-placement="top" title="Tambah Perusahaan"><img src="{{ asset('icon/plus.svg') }}" class="" width="30px"> Isi Feedback</a>
                @endcan
                @can('Detail Feedback Per Bulan')
                    <a href="{{ route('detailfeedbacks') }}" class="btn btn-md click-primary mx-4" data-toggle="tooltip" data-placement="top" title="Detail Feedback Per Bulan"><img src="{{ asset('icon/clipboard.svg') }}" class="" width="30px"> Detail Feedback Per Bulan</a>
                @endcan
            </div>
            <div class="card">
                <div class="card-body table-responsive">
                    <h4 class="card-title mt-3 text-center">&nbsp;Data Feedback</h4>
                    <table id="datafeedback" class="display" style="width:100%">
                        <thead>
                            <tr>
                                <th scope="col">RKM</th>
                                <th>Instruktur</th>
                                <th >Sales</th>
                                <th>Tanggal Awal</th>
                                <th>Tanggal Akhir</th>
                                <th>Materi</th>
                                <th>Pelayanan</th>
                                <th>Fasilitas</th>
                                <th>Instuktur</th>
                                <th>Instruktur 2</th>
                                <th>Asisten</th>
                                <th>created_at</th>
                                <th>Aksi</th>
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
<script src="https://cdn.jsdelivr.net/gh/ashl1/datatables-rowsgroup@fbd569b8768155c7a9a62568e66a64115887d7d0/dataTables.rowsGroup.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script><script>
        $(document).ready(function(){
            var idInstruktur = "{{ auth()->user()->id_instruktur }}";
            var idSales = "{{ auth()->user()->id_sales }}";
            if(idInstruktur == 'AD'){
                var idInstruktur = "";
            }
            if(idSales == 'AM'){
                var idSales = "";
            }
            $('#datafeedback').DataTable({
                // 'rowsGroup': [0,1],
                "dom": 'Bfrtip',
                "buttons": [
                    {
                        extend: 'excel',
                        text: 'Export to Excel',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9] // Kolom yang akan diekspor ke Excel
                        },
                    },
                    {
                        extend: 'pdf',
                        text: 'Export to PDF',
                        exportOptions: {
                            columns: [0, 2, 3, 4, 5, 6, 7, 8, 9] // Kolom yang akan diekspor ke PDF
                        },
                        customize: function(doc) {
                            doc.content[1].table.widths = ['*', '*', '*', '*', '*', '*', '*', '*', '*']; // Menyesuaikan lebar kolom
                            doc.content.splice(0, 1, {
                                text: 'Inixindo E-Office Data Feedback',
                                fontSize: 12,
                                alignment: 'center',
                                margin: [0, 0, 0, 12] // Margin dari header
                            });
                            doc['footer'] = function(currentPage, pageCount) {
                                return {
                                    text: 'Data Feedback ' + currentPage.toString() + ' of ' + pageCount,
                                    alignment: 'center',
                                    margin: [0, 0, 0, 12] // Margin dari footer
                                };
                            };
                        }
                    }
                ],
                "ajax": {
                    "url": "{{ route('getFeedbacks') }}", // URL API untuk mengambil data
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
                    {"data": "nama_materi"},
                    {"data": "instruktur_key"},
                    {"data": "sales_key", "visible": true},
                    {
                        "data": null,
                        "render": function(data, type, row) {
                            moment.locale('id');
                            var tanggalAwal = moment(data.tanggal_awal).format('DD MMMM YYYY');
                            return tanggalAwal;
                        }
                    },
                    {
                        "data": null,
                        "render": function(data, type, row) {
                            moment.locale('id');
                            var tanggalAkhir = moment(data.tanggal_akhir).format('DD MMMM YYYY');
                            return tanggalAkhir;
                        }
                    },
                    {"data": "averageM"},
                    {"data": "averageP"},
                    {"data": "averageF"},
                    {"data": "averageI"},
                    {"data": "averageIb"},
                    {"data": "averageIas"},
                    {"data": "tanggal_awal", "visible": false},

                    {
                        "data": null,
                        "render": function(data, type, row) {
                            moment.locale('en');
                            var tanggalAwal = moment(data.tanggal_awal).format('YYYY-MM-DD');
                            var parts = tanggalAwal.split('-');
                            var tahun = parts[0];
                            var bulan = parts[1];
                            var hari = parts[2];
                            var actions = "";
                            actions += '<div class="dropdown">';
                            actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                            actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                            actions += '<a class="dropdown-item" href="{{ url('/feedback') }}/' + row.materi_key +'ixb'+ bulan + 'ixb'+ tahun +'ixb'+ hari +'" data-toggle="tooltip" data-placement="top" title="Detail Feedback"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Detail</a>';
                            actions += '</div>';
                            actions += '</div>';
                            return actions;
                        }
                    }
                ],
                "columnDefs" : [{"targets":[11], "type":"date"}],
                "order": [[11, 'desc']], // Ubah urutan menjadi descending untuk kolom ke-6

                "initComplete": function() {
                    this.api().columns(1).search(idInstruktur).draw();
                    this.api().columns(2).search(idSales).draw();
                }
            });
        });
</script>
@endsection

