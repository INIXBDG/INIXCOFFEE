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
        <div class="row">
            <div class="col-md-12">
                <div class="card" style="width: 100%">
                    <div class="card-body d-flex justify-content-center">
                        <div class="col-md-4 mx-1">
                            <label for="tahun" class="form-label">Tahun</label>
                            <select id="tahun" class="form-select" aria-label="tahun">
                                <option disabled>Pilih Tahun</option>
                                @php
                                $tahun_sekarang = now()->year;
                                for ($tahun = 2020; $tahun <= $tahun_sekarang + 2; $tahun++) {
                                    $selected = $tahun == $tahun_sekarang ? 'selected' : '';
                                    echo "<option value=\"$tahun\" $selected>$tahun</option>";
                                }
                                @endphp
                            </select>
                        </div>
                        <div class="col-md-4 mx-1">
                            <label for="bulan" class="form-label">Bulan</label>
                            <select id="bulan" class="form-select" aria-label="bulan">
                                <option disabled>Pilih Bulan</option>
                                @php
                                $bulan_sekarang = now()->month;
                                $nama_bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                for ($bulan = 1; $bulan <= 12; $bulan++) {
                                    $bulan_awal = $nama_bulan[$bulan - 1];
                                    $selected = $bulan == $bulan_sekarang ? 'selected' : '';
                                    echo "<option value=\"$bulan\" $selected>$bulan_awal</option>";
                                }
                                @endphp
                            </select>
                        </div>
                        <div class="col-md-4 mx-1">
                            <button type="button" onclick="getDataFeedbacks()" class="btn click-primary" style="margin-top: 37px">Cari Data</button>
                            <a href="{{ route('nilaifeedbackexport', [$tahun_sekarang, $bulan_sekarang]) }}" id="export-link" target="_blank" class="btn click-primary" style="margin-top: 37px">Export to Excel</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>  
        <div class="row my-2">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body table-responsive">
                        <h4 class="card-title mt-3 text-center">&nbsp;Data Feedback</h4>
                        <table id="datafeedback" class="display" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">RKM</th>
                                    <th>Instruktur</th>
                                    <th>Sales</th>
                                    <th>Tanggal Awal</th>
                                    <th>Tanggal Akhir</th>
                                    <th>Materi</th>
                                    <th>Pelayanan</th>
                                    <th>Fasilitas</th>
                                    <th>Instuktur</th>
                                    <th>Instruktur 2</th>
                                    <th>Asisten</th>
                                    <th>Materi 1</th>
                                    <th>Materi 2</th>
                                    <th>Materi 3</th>
                                    <th>Materi 4</th>
                                    <th>Pelayanan 1</th>
                                    <th>Pelayanan 2</th>
                                    <th>Pelayanan 3</th>
                                    <th>Pelayanan 4</th>
                                    <th>Pelayanan 5</th>
                                    <th>Pelayanan 6</th>
                                    <th>Pelayanan 7</th>
                                    <th>Fasilitas 1</th>
                                    <th>Fasilitas 2</th>
                                    <th>Fasilitas 3</th>
                                    <th>Fasilitas 4</th>
                                    <th>Fasilitas 5</th>
                                    <th>Instruktur 1</th>
                                    <th>Instruktur 2</th>
                                    <th>Instruktur 3</th>
                                    <th>Instruktur 4</th>
                                    <th>Instruktur 5</th>
                                    <th>Instruktur 6</th>
                                    <th>Instruktur 7</th>
                                    <th>Instruktur 8</th>
                                    <th>Instruktur #2 1</th>
                                    <th>Instruktur #2 2</th>
                                    <th>Instruktur #2 3</th>
                                    <th>Instruktur #2 4</th>
                                    <th>Instruktur #2 5</th>
                                    <th>Instruktur #2 6</th>
                                    <th>Instruktur #2 7</th>
                                    <th>Instruktur #2 8</th>
                                    <th>Asisten 1</th>
                                    <th>Asisten 2</th>
                                    <th>Asisten 3</th>
                                    <th>Asisten 4</th>
                                    <th>Asisten 5</th>
                                    <th>Asisten 6</th>
                                    <th>Asisten 7</th>
                                    <th>Asisten 8</th>
                                    <th>created_at</th>
                                    {{-- <th>Aksi</th> --}}
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
<script>
    $(document).ready(function() {
            getDataFeedbacks()
            $('#tahun, #bulan').on('change', function() {
                updateExportLink();
            });
    });
       var table =  $('#datafeedback').DataTable({
            "dom": 'Bfrtip',
            // "bDestroy": true,
            "paging": true,
            "aLengthMenu": [[25, 50, 100, -1], [25, 50, 100, "All"]],
            "buttons": [
                {
                    extend: 'excel',
                    class: 'btn-excel',
                    text: 'Export to Excel',
                    titleAttr: 'Export Feedback',
                    // init: function (api, node, config){
                    //     $(node).hide()
                    // },
                    // Menggunakan function untuk mendapatkan bulan dari elemen input
                    title: function() {
                        var month = $('#bulan').val(); // Mengambil nilai bulan
                        var year = $('#tahun').val(); // Mengambil nilai bulan
                        return 'Feedback Bulan ' + month + ' Tahun ' + year;
                    },
                    filename: function() {
                        var month = $('#bulan').val(); // Mengambil nilai bulan
                        var year = $('#tahun').val(); // Mengambil nilai bulan

                        return 'Feedback RKM Bulan ' + month + ' Tahun ' + year;
                    },
                    // messageTop: 'Daftar Feedback untuk Bulan ' + $('#bulan').val(),
                    // messageBottom: 'Terima kasih telah menggunakan sistem kami.',
                    customize: function(xlsx) {
                        var sheet = xlsx.xl.worksheets['sheet1.xml'];
                        // Contoh: Mengubah font sel di Excel
                        // $('row c', sheet).attr('s', '2');
                    },
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10,
                            11, 12, 13, 14, 15, 16, 17, 18, 19, 20,
                            21, 22, 23, 24, 25, 26, 27, 28, 29, 30,
                            31, 32, 33, 34, 35, 36, 37, 38, 39, 40,
                            41, 42, 44, 44, 45, 46, 47, 48, 49, 50,
                        ],
                    },
                }
            ],

            "ajax": null,
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
                {"data": "averageM1", "visible": false},
                {"data": "averageM2", "visible": false},
                {"data": "averageM3", "visible": false},
                {"data": "averageM4", "visible": false},
                {"data": "averageP1", "visible": false},
                {"data": "averageP2", "visible": false},
                {"data": "averageP3", "visible": false},
                {"data": "averageP4", "visible": false},
                {"data": "averageP5", "visible": false},
                {"data": "averageP6", "visible": false},
                {"data": "averageP7", "visible": false},
                {"data": "averageF1", "visible": false},
                {"data": "averageF2", "visible": false},
                {"data": "averageF3", "visible": false},
                {"data": "averageF4", "visible": false},
                {"data": "averageF5", "visible": false},
                {"data": "averageI1", "visible": false},
                {"data": "averageI2", "visible": false},
                {"data": "averageI3", "visible": false},
                {"data": "averageI4", "visible": false},
                {"data": "averageI5", "visible": false},
                {"data": "averageI6", "visible": false},
                {"data": "averageI7", "visible": false},
                {"data": "averageI8", "visible": false},
                {"data": "averageI1b", "visible": false},
                {"data": "averageI2b", "visible": false},
                {"data": "averageI3b", "visible": false},
                {"data": "averageI4b", "visible": false},
                {"data": "averageI5b", "visible": false},
                {"data": "averageI6b", "visible": false},
                {"data": "averageI7b", "visible": false},
                {"data": "averageI8b", "visible": false},
                {"data": "averageI1as", "visible": false},
                {"data": "averageI2as", "visible": false},
                {"data": "averageI3as", "visible": false},
                {"data": "averageI4as", "visible": false},
                {"data": "averageI5as", "visible": false},
                {"data": "averageI6as", "visible": false},
                {"data": "averageI7as", "visible": false},
                {"data": "averageI8as", "visible": false},
                {"data": "tanggal_awal", "visible": false},
            ],
            "order": [[51, 'desc']], // Ubah urutan menjadi descending untuk kolom ke-6
            "columnDefs" : [{"targets":[51], "type":"date"}],
        });
        // $('#btnExcel').on('click', function(){
        //     table.button('btn-excel').trigger();
        // })
    // });
    
    function getDataFeedbacks() {
        var year = $('#tahun').val();
        var month = $('#bulan').val();

        if (year && month) {
            $('#loadingModal').modal('show'); // Tampilkan modal sebelum memulai pemanggilan Ajax

            $('#datafeedback').DataTable().ajax.url("{{ url('/getFeedbacksByMonth') }}/" + year + "/" + month).load(function(json) {
                if (!json || json.data.length === 0) {
                    alert("Tidak ada data untuk tahun dan bulan yang dipilih.");
                }
                setTimeout(() => {
                    $('#loadingModal').modal('hide'); 
                }, 100);
            });
        } else {
            alert("Pilih tahun dan bulan terlebih dahulu.");
        }
    }

    function updateExportLink() {
        var tahun = $('#tahun').val();
        var bulan = $('#bulan').val();
        var exportLink = $('#export-link');

        // Get current year and month
        var currentYear = new Date().getFullYear();
        var currentMonth = new Date().getMonth() + 1; // getMonth() returns month index (0-11), so we add 1

        // If year or month is not selected, use current year and month
        if (!tahun) {
            tahun = currentYear;
        }
        if (!bulan) {
            bulan = currentMonth;
        }

        exportLink.attr('href', '/nilaifeedbackexport/' + tahun + '/' + bulan);
    }


</script>
@endsection

