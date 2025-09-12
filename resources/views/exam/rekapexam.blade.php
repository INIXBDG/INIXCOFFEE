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
                            <a href="{{ route('rekapExamExportExcel', [$tahun_sekarang, $bulan_sekarang]) }}" id="export-link" target="_blank" class="btn click-primary" style="margin-top: 37px">Export to Excel</a>
                        </div>
                    </div>
                </div>
            </div>
        <div class="col-md-12">
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Data Pengajuan Exam') }}</h3>
                    <table class="table table-striped" id="examhistoritable">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Nama Materi</th>
                                <th scope="col">Tanggal Periode</th>
                                <th scope="col">Nama Perusahaan</th>
                                <th scope="col">Pax</th>
                                <th scope="col">sales</th>
                                <th scope="col">instruktur</th>
                                <th scope="col">Peserta</th>
                                <th scope="col">Kartu Kredit</th>
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
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/id.min.js"></script>

<script>
    $(document).ready(function(){
        getDataFeedbacks();
        $('#tahun, #bulan').on('change', function() {
                updateExportLink();
                getDataFeedbacks();
        });

    });
    function getDataRekapExam() {
        var year = $('#tahun').val();
        var month = $('#bulan').val();
        if ($.fn.DataTable.isDataTable('#examhistoritable')) {
            $('#examhistoritable').DataTable().destroy();
        }
        if (year && month) {
            // $('#loadingModal').modal('show'); // Tampilkan modal sebelum memulai pemanggilan Ajax

            // $('#datafeedback').DataTable().ajax.url("{{ url('/getFeedbacksByMonth') }}/" + year + "/" + month).load(function(json) {
            //     if (!json || json.data.length === 0) {
            //         alert("Tidak ada data untuk tahun dan bulan yang dipilih.");
            //     }
            //     setTimeout(() => {
            //         $('#loadingModal').modal('hide'); 
            //     }, 100);
            // });
        var tableIndex2 = 1;

        $('#examhistoritable').DataTable({
            "ajax": {
                "url": "{{ url('/getRekapExamByMonth') }}/" + year + "/" + month,
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
                        return tableIndex2++
                    }
                },
                {"data": "materi"},
                {
                    "data": null,
                    "render": function (data, type, row) {
                        return moment(data.tanggal_pengajuan).format('LL');
                    },
                },
                {"data": "perusahaan"},
                {"data": "pax"},
                {
                        "data": "rkm.sales_key",
                        "visible": false
                },
                {
                        "data": "rkm.instruktur_key",
                        "visible": false
                },
                {
                    "data": "registexam",
                        "render": function (data, type, row) {
                            // Cek apakah data tersedia (null, undefined, atau array kosong)
                            if (!data || !Array.isArray(data) || data.length === 0) {
                                return '<span style="color: #d32f2f; font-style: italic;">Belum Mendaftar Exam</span>';
                            }

                            // Ambil semua nama peserta, dengan penanganan safety (optional chaining)
                            const names = data
                                .map(item => item.peserta?.nama || 'N/A')
                                .filter(Boolean)
                                .join('<hr style="margin: 4px 0; border: 1px solid #ccc">');

                            return names || 'N/A';
                        },
                },
                {
                    "data": "registexam",
                    "render": function (data, type, row) {
                        // Cek apakah data tersedia
                        if (!data || !Array.isArray(data) || data.length === 0) {
                            return '<span style="color: #d32f2f; font-style: italic;">Belum didaftarkan CC nya</span>';
                        }

                        // Ambil semua nama pemilik kartu
                        const ccNames = data
                            .map(item => item.creditcard?.nama_pemilik || 'N/A')
                            .filter(Boolean)
                            .join('<hr style="margin: 4px 0; border: 1px solid #ccc">');

                        return ccNames || 'N/A';
                    },
                },
                {
                    "data": null,
                    // "visible" : false,
                    "render": function(data, type, row) {
                            var actions = "";
                                actions += '<div class="dropdown">';
                                actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                                actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                                actions += '<a class="dropdown-item" disabled href="{{ url('/exam') }}/' + row.id + '" data-toggle="tooltip" data-placement="top" title="Detail User"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Detail</a>';
                                actions += '@can('Edit Exam')';
                                actions += '<a class="dropdown-item" href="{{ url('/exam') }}/' + row.id + '/edit"><img src="{{ asset('icon/edit-warning.svg') }}" class=""> Edit</a>';
                                actions += '@endcan';
                                actions += '@can('Delete Exam')';
                                actions += '<form onsubmit="return confirm(\'Apakah Anda Yakin ?\');" action="{{ url('/exam') }}/' + row.id + '" method="POST">';
                                actions += '@csrf';
                                actions += '@method('DELETE')';
                                actions += '<button type="submit" class="dropdown-item"><img src="{{ asset('icon/trash-danger.svg') }}" class=""> Hapus</button>';
                                actions += '</form>';
                                actions += '@endcan';
                                actions += '</div>';
                                actions += '</div>';
                        return actions;
                    }
                }
            ],
            // "order": [[2, 'desc']], // Ubah urutan menjadi descending untuk kolom ke-6
            // "columnDefs" : [{"targets":[2], "type":"date"}],
            // "initComplete": function() {
            //     this.api().columns(6).search(idInstruktur).draw();
            //     this.api().columns(5).search(idSales).draw();
            // }
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

        exportLink.attr('href', '/rekapExamexport/' + tahun + '/' + bulan);
    }
</script>
@endpush
@endsection
