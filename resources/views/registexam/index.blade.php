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
        
        <!-- Modal -->
    <div class="modal fade" id="detailPesertaModal" tabindex="-1" role="dialog" aria-labelledby="detailPesertaModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailPesertaModalLabel">Detail Peserta</h5>
                </div>
                <div class="modal-body">
                    <div id="detailPesertaContent">
                        <p><strong>Nama:</strong> <span id="nama_peserta"></span></p>
                        <p><strong>Jenis Kelamin:</strong> <span id="jenis_kelamin"></span></p>
                        <p><strong>Tanggal Lahir:</strong> <span id="tanggal_lahir_peserta"></span></p>
                        <p><strong>Email:</strong> <span id="email_peserta"></span></p>
                        <p><strong>No. HP:</strong> <span id="no_hp"></span></p>
                        <p><strong>Alamat:</strong> <span id="alamat_peserta"></span></p>
                        <p><strong>Nama Perusahaan:</strong> <span id="nama_perusahaan"></span></p>
                        <p><strong>Kategori Perusahaan:</strong> <span id="kategori_perusahaan"></span></p>
                        <p><strong>Lokasi Perusahaan:</strong> <span id="lokasi_perusahaan"></span></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


        <div class="col-md-12">
            <div class="card" style="height: auto;">
                <div class="card-body">
                    <div class="row">
                    <h4 class="text-center">Data Registrasi Exam</h4>
                        <div class="col-md-12">
                            {{-- <div class="d-flex"> --}}
                                <div class="row">
                                    <div class="col-md-7">
                                        <div class="row card mx-1">
                                            <div class="card-body col-md-12 table-responsive" style="height:80vh; overflow:auto;">
                                                <table class="table table-striped" id="registrasitablepeserta">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col">Tanggalawal</th>
                                                            <th scope="col">Nama Materi</th>
                                                            <th scope="col">Nama Perusahaan</th>
                                                            <th scope="col">Pax</th>
                                                            <th scope="col">Periode</th>
                                                            <th scope="col">Sales</th>
                                                            <th scope="col">Instruktur</th>
                                                            <th scope="col">Tanggal Pengajuan</th>
                                                            <th scope="col">Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="row card">
                                            <div class="card-body col-md-12 table-responsive" style="height:80vh; overflow:auto;" >
                                                <table class="table table-striped" id="registrasitable">
                                                    <thead>
                                                        <tr>
                                                            <th>Nama Peserta</th>
                                                            <th>Kode Exam</th>
                                                            <th>Sales</th>
                                                            <th>Instruktur</th>
                                                            <th>Email Exam</th>
                                                            <th>Akun Exam</th>
                                                            <th>Tanggal Exam</th>
                                                            <th>Pukul</th>
                                                            <th>Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            {{-- </div> --}}
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
        var tableIndex2 = 1;
        var userRole = "{{ auth()->user()->jabatan}}";
        var idInstruktur = "{{ auth()->user()->id_instruktur }}";
        var idSales = "{{ auth()->user()->id_sales }}";

        if(idInstruktur == 'AD'){
            idInstruktur = "";
        }
        if(idSales == 'AM'){
            idSales = "";
        }
        if(userRole == 'Technical Support'){
            idInstruktur = "";
        }

        var registrasiTablePeserta = $('#registrasitablepeserta').DataTable({
            "ajax": {
                "url": "{{ route('getHistoriExam') }}",
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
                "dataSrc": function(json) {
                    // console.log(json); // Debug: log JSON response
                    return json.data;
                },
                "error": function(xhr, error, code){
                    console.log("Error: ", xhr, error, code);
                }
            },
            "columns": [
                {"data": "rkm.tanggal_awal", "visible": false},
                {"data": "rkm.materi.nama_materi"},
                {"data": "rkm.perusahaan.nama_perusahaan"},
                {"data": "pax"},
                {
                "data": null,
                "render": function (data, type, row) {
                        moment.locale('id'); // Atur locale ke Bahasa Indonesia
                        if (data.rkm.tanggal_awal && data.rkm.tanggal_akhir) {
                            var tanggalAwal = moment(data.rkm.tanggal_awal).format('LL'); // Format Tanggal dalam Bahasa Indonesia
                            var tanggalAkhir = moment(data.rkm.tanggal_akhir).format('LL'); // Format Tanggal dalam Bahasa Indonesia
                            return tanggalAwal + " s/d " + tanggalAkhir;
                        } else {
                            return "";
                        }
                    }
                },
                {"data": "rkm.sales_key", "visible": true},
                {"data": "rkm.instruktur_key", "visible": true},
                {
                    "data": null,
                    "render": function(data) {
                        moment.locale('id');
                        return moment(data.tanggal_pengajuan).format('DD MMMM YYYY');
                    },
                    "visible":true,
                },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        var actions = '<div class="dropdown">';
                        actions += '<button class="btn click-primary list_peserta" type="button" id="list_peserta">List Peserta</button>';
                        return actions;
                    }
                }
            ],
            "order": [[6, 'desc']], // Ubah urutan menjadi descending untuk kolom ke-6
            "columnDefs" : [{"targets":[6], "type":"date"}],
            "initComplete": function() {
                this.api().columns(6).search(idInstruktur).draw();
                this.api().columns(5).search(idSales).draw();
            }
        });

        var registrasiTable = $('#registrasitable').DataTable({
            "ajax": {
                "url": "",
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
                "dataSrc": function(json) {
                    // console.log(json); // Debug: log JSON response
                    return json.data;
                },
                "error": function(xhr, error, code){
                    // console.log("Error: ", xhr, error, code);
                }
            },
            "columns": [
                {"data": "peserta.nama"},
                {"data": "kode_exam"},
                {"data": "rkm.sales_key", "visible": false},
                {"data": "rkm.instruktur_key", "visible": false},
                {"data": "email_exam"},
                {"data": "akun_exam"},
                {
                    "data": null,
                    "render": function(data) {
                        moment.locale('id');
                        return moment(data.tanggal_exam).format('DD MMMM YYYY');
                    }
                },
                {"data": "pukul"},
                {
                    "data": null,
                    "render": function(data, type, row) {
                        // console.log(data);
                            var actions = "";
                                actions += '<div class="dropdown">';
                                actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                                actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                                actions += '@can('UploadInvoice RegistExam')'
                                actions += '<a class="dropdown-item" href="{{ url('/uploadinvoice') }}/' + row.id + '" data-toggle="tooltip" data-placement="top" title="Upload Invoice"><img src="{{ asset('icon/edit-warning.svg') }}" class=""> Upload Invoice</a>';
                                actions += '@endcan'
                                actions += '@can('DetailPeserta RegistExam')';
                                actions += '<a class="dropdown-item detailPesertaBtn" href="#" data-id="' + data.id_peserta + '">';
                                actions += '<img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Detail Peserta</a>';
                                actions += '@endcan';
                                actions += '@can('UploadHasil RegistExam')';
                                actions += '<a class="dropdown-item" href="{{ url('/hasilexam') }}/' + row.id + '" data-toggle="tooltip" data-placement="top" title="Upload Hasil Exam"><img src="{{ asset('icon/plus_green.svg') }}" class=""> Hasil Ujian</a>';
                                actions += '<a class="dropdown-item" href="{{ url('/hasilexam') }}/' + row.id + '/edit" data-toggle="tooltip" data-placement="top" title="Edit Hasil Exam"><img src="{{ asset('icon/edit-warning.svg') }}" class=""> Edit Hasil Ujian</a>';
                                actions += '@endcan';
                                if(data.invoice === null){
                                    actions += '<a class="dropdown-item disabled" href="#" data-toggle="tooltip" data-placement="top" title="Lihat Invoice"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Detail Hasil Ujian</a>';
                                    actions += '<a class="dropdown-item disabled" href="#" data-toggle="tooltip" data-placement="top" title="Lihat Invoice"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Lihat Invoice</a>';

                                }else{
                                    actions += '<a class="dropdown-item" href="{{ url('/storage/invoiceexam') }}/' + data.invoice + '" data-toggle="tooltip" data-placement="top" title="Lihat Invoice"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Lihat Invoice</a>';
                                    actions += '<a class="dropdown-item" href="{{ url('/hasilexam') }}/' + row.id + '/detail" data-toggle="tooltip" data-placement="top" title="Lihat Invoice"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Detail Hasil Ujian</a>';
                                }
                                actions += '@can('CC RegistExam')';
                                actions += '<a class="dropdown-item" href="{{ url('/registexam/cc') }}/' + data.id + '" data-toggle="tooltip" data-placement="top" title="Tambahkan Credit Card"><img src="{{ asset('icon/plus.svg') }}" class=""> Tambahkan CC</a>';
                                actions += '@endcan';
                                actions += '</div>';
                                actions += '</div>';

                        return actions;
                    }
                }
            ],
            "initComplete": function() {
                this.api().columns(3).search(idInstruktur).draw();
                this.api().columns(2).search(idSales).draw();
            }
        });

        $('#registrasitablepeserta tbody').on('click', 'button.list_peserta', function() {
            var data = registrasiTablePeserta.row($(this).parents('tr')).data();
            var id_exam = data.id;
            registrasiTable.ajax.url('getRegistrasiexamByIdExam/' + id_exam).load();
        });

        function showAlert() {
            alert("Belum ada invoice yang diupload!");
        }
        $('#showalert').on('click', function() {
            showAlert()
        });

        $(document).on('click', '.detailPesertaBtn', function(e) {
            e.preventDefault();
            var participantId = $(this).data('id');
            
            // AJAX request
            $.ajax({
                url: 'getPesertaById/' + participantId,
                method: 'GET',
                
                success: function(response) {
                    var data = response.data;
                    // console.log(data);
                    // Isi data ke dalam modal
                    $('#nama_peserta').text(data.nama);
                    $('#jenis_kelamin').text(data.jenis_kelamin);
                    $('#email_peserta').text(data.email);
                    var formattedDate = moment(data.tanggal_lahir).format('DD MMMM YYYY');
                    $('#tanggal_lahir_peserta').text(formattedDate);
                    $('#no_hp').text(data.no_hp);
                    $('#alamat_peserta').text(data.alamat);
                    $('#nama_perusahaan').text(data.perusahaan.nama_perusahaan);
                    $('#kategori_perusahaan').text(data.perusahaan.kategori_perusahaan);
                    $('#lokasi_perusahaan').text(data.perusahaan.lokasi);

                    $('#detailPesertaModal').modal('show');
                },
                error: function(xhr, status, error) {
                    // Handle errors here
                    $('#detailPesertaContent').html('<p>Something went wrong. Please try again.</p>');
                }
            });
        });
    });


</script>
@endpush
@endsection
