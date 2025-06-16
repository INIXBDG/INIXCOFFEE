
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
                            <a href="{{ route('pengajuancutiexport', [$tahun_sekarang, $bulan_sekarang]) }}" id="export-link" target="_blank" class="btn click-primary" style="margin-top: 37px">Export to Excel</a>
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
                        <table id="datacuti" class="display" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">No</th>
                                    <th scope="col">Nama Karyawan</th>
                                    <th scope="col">Divisi</th>
                                    <th scope="col">KODE</th>
                                    <th scope="col">Kontak</th>
                                    <th scope="col">Tipe</th>
                                    <th scope="col">Alasan</th>
                                    <th scope="col">Durasi</th>
                                    <th scope="col">Tanggal</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Alasan Manager</th>
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
    @media screen and (max-width: 768px) {
        .card {
            padding: 15px;
            max-width: 100%;
        }

        .card-body  .row {
            margin-bottom: 10px;
        }

        /* .col-xs-4, */
        .col-xs-1 {
            display: none;
        }

        .col-xs-7 {
            width: 100%;
            text-align: left;
        }
    }

        .cardname {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .click-secondary-icon {
            background:    #355C7C;
            border-radius: 1000px;
            width:         45px;
            height:        45px;
            color:         #ffffff;
            display:       flex;
            justify-content: center;
            align-items:   center;
            text-align:    center;
            text-decoration: none;
        }
        .click-secondary-icon i {
            line-height: 45px;
        }

        .click-secondary {
            background:    #355C7C;
            border-radius: 1000px;
            padding:       10px 25px;
            color:         #ffffff;
            display:       inline-block;
            font:          normal bold 18px/1 "Open Sans", sans-serif;
            text-align:    center;
            transition:    color 0.1s linear, background-color 0.2s linear;
        }

        .click-secondary:hover {
            color:         #A5C7EF;
            transition:    color 0.1s linear, background-color 0.2s linear;
        }
        .click-warning {
            background:    #f8be00;
            border-radius: 1000px;
            padding:       10px 20px;
            color:         #000000;
            display:       inline-block;
            font:          normal bold 18px/1 "Open Sans", sans-serif;
            text-align:    center;
            transition:    color 0.1s linear, background-color 0.2s linear; /
        }

        .click-warning:hover {
            background:         #A5C7EF;
            transition:    color 0.1s linear, background-color 0.2s linear;
        }
        .card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            width: auto;
            height: auto;
            border: 1px solid rgba(255, 255, 255, .25);
            border-radius: 20px;
            background-color: rgba(255, 255, 255, 0.45);
            box-shadow: 0 0 10px 1px rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(2px);
            }
            .checkmark {
        display: block;
        width: 25px;
        height: 25px;
        border: 1px solid #ccc;
        border-radius: 50%;
        position: relative;
        margin: 0 auto;
    }

    .checkmark:after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #22bb33;
        display: none;
    }

    tr.selected .checkmark:after {
        display: block;
    }
    @media print {
    @page {
        margin: 0;
    }
    body {
        margin: 0;
    }

    html, body {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
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
       var table =  $('#datacuti').DataTable({
            "paging": true,
            "aLengthMenu": [[25, 50, 100, -1], [25, 50, 100, "All"]],
            "ajax": null,
            "columns": [
                {"data": "tanggal_awal", "visible": false},
                {"data": "karyawan.nama_lengkap"},
                {"data": "karyawan.divisi"},
                {"data": "karyawan.jabatan", "visible": false},
                {"data": "kontak"},
                {"data": "tipe"},
                {"data": "alasan"},
                {
                    "data": null,
                    "render": function(data) {
                        return data.durasi + ' Hari' ;
                    }
                },
                {
                    "data": null,
                    "render": function(data) {
                        moment.locale('id');
                        return moment(data.tanggal_awal).format('DD MMMM YYYY')+ ' s/d ' + moment(data.tanggal_akhir).format('DD MMMM YYYY');
                    }
                },
                {
                    "data": null,
                    "render": function(data) {
                        if (data.approval_manager == '0') {
                            return '<span class="badge bg-warning" style="color:black;"> Menunggu Persetujuan Manager Divisi </span>';
                        } else if (data.approval_manager == '1') {
                            return '<span class="badge bg-success"> Disetujui </span>';
                        } else if (data.approval_manager == '2') {
                            return '<span class="badge bg-danger"> Ditolak </span>';
                        }
                    },
                },
                {   "data": null,
                    "render": function(data) {
                        if (data.alasan_manager == null) {
                            return '-';
                        } else 
                            return data.alasan_manager;
                        }
                },
            ],
            "order": [[0, 'desc']], // Ubah urutan menjadi descending untuk kolom ke-6
            "columnDefs" : [{"targets":[0], "type":"date"}],
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

            $('#datacuti').DataTable().ajax.url("{{ url('/getPengajuanCuti') }}/" + month + "/" + year).load(function(json) {
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

        exportLink.attr('href', '/pengajuancutiexport/' + bulan + '/' + tahun);
    }


</script>
@endsection
