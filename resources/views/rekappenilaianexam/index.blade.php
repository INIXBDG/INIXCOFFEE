@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="modal fade" id="detailPenilaianModal" tabindex="-1" aria-labelledby="detailPenilaianModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailPenilaianModalLabel">Detail Feedback Exam</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6 id="modalNamaMateri" class="text-center mb-3 font-weight-bold"></h6>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Responden:</span>
                        <span id="modalTotalResponden" class="badge bg-primary"></span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Nilai:</span>
                        <span id="modalTotalNilai" class="badge bg-secondary"></span>
                    </div>
                    <hr>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>😍 (4) Sangat Baik</span>
                            <span class="text-dark fw-bold" id="modalSangatBaik">0</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>🙂 (3) Baik</span>
                            <span class="text-dark fw-bold" id="modalBaik">0</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>😐 (2) Cukup</span>
                            <span class="text-dark fw-bold" id="modalCukup">0</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>🙁 (1) Kurang</span>
                            <span class="text-dark fw-bold" id="modalBuruk">0</span>
                        </li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
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
        <div class="col-md-12 d-flex my-2 justify-content-end">
            <a class="btn click-primary mx-1" href="{{ url('/rekap-penilaian-exam') }}">Kembali ke Data Exam</a>
        </div>
        <div class="col-md-12">
            <div class="card m-4" style="width: 96%">
                <div class="card-body d-flex justify-content-center">
                    <div class="col-md-4 mx-1">
                        <label for="tahun" class="form-label">Tahun</label>
                        <select id="tahun" class="form-select">
                            @php
                            $tahun_sekarang = now()->year;
                            // Menampilkan rentang tahun dari 2020 hingga 2 tahun ke depan
                            for ($tahun = 2020; $tahun <= $tahun_sekarang + 2; $tahun++) {
                                $selected = $tahun == $tahun_sekarang ? 'selected' : '';
                                echo "<option value=\"$tahun\" $selected>$tahun</option>";
                            }
                            @endphp
                        </select>
                    </div>
                    <div class="col-md-4 mx-1">
                        <label for="bulan" class="form-label">Bulan</label>
                        <select id="bulan" class="form-select">
                            @php
                            $bulan_sekarang = now()->month;
                            $nama_bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                            for ($bulan = 1; $bulan <= 12; $bulan++) {
                                $selected = $bulan == $bulan_sekarang ? 'selected' : '';
                                echo "<option value=\"$bulan\" $selected>{$nama_bulan[$bulan - 1]}</option>";
                            }
                            @endphp
                        </select>
                    </div>
                    <div class="col-md-2 mx-1"> 
                        <button type="button" onclick="filterData()" class="btn click-primary" style="margin-top: 32px">Cari Data</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Data Feedback Exam') }}</h3>
                    <table class="table table-striped" id="rekappenilaiantable">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Kode Exam</th>
                                <th scope="col">Nama Materi</th>
                                <th scope="col">Tanggal Periode</th>
                                <th scope="col">Nama Perusahaan</th>
                                <th scope="col">Pax</th>
                                <th scope="col">Total Nilai</th>
                                <th scope="col">Rata-rata Penilaian</th>
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
        }
        small {
            font-size: 11.5px;
            color: #999;
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
    var table; 

    $(document).ready(function(){
        var tableIndex = 1;
        table = $('#rekappenilaiantable').DataTable({
            "ajax": {
                "url": "{{ url('/rekap-penilaian-exam/data') }}",
                "type": "GET",
                "dataSrc": "data",
                "data": function (d) {
                    d.tahun = $('#tahun').val(); 
                    d.bulan = $('#bulan').val(); 
                },
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
                {
                    "data": null,
                    "render": function (data, type, row, meta){
                        return meta.row + 1;
                    }
                },
                {
                    "data": "kode_exam",
                    "render": function (data) {
                        return data ? data : '-';
                    }
                },
                {
                    "data": "nama_materi",
                    "render": function (data) {
                        return data ? data : '-';
                    }
                },
                {
                    "data": null,
                    "render": function (data, type, row) {
                        if (data.tanggal_awal && data.tanggal_akhir) {
                            var tanggalAwal = moment(data.tanggal_awal).format('LL');
                            var tanggalAkhir = moment(data.tanggal_akhir).format('LL');
                            return tanggalAwal + " s/d " + tanggalAkhir;
                        } else {
                            return "-";
                        }
                    }
                },
                {
                    "data": "nama_perusahaan",
                    "render": function (data) {
                        return data ? data : '-';
                    }
                },
                {"data": "pax"},
                {"data": "total_nilai"},
                {
                    "data": "rata_rata",
                    "render": function (data) {
                        return "<strong>" + data + "</strong> / 4";
                    }
                },
                {
                    "data": null,
                    "render": function (data, type, row) {
                        var detailJSON = row.detail ? JSON.stringify(row.detail).replace(/'/g, "&#39;") : '{}';
                        var namaMateri = row.nama_materi ? row.nama_materi.replace(/'/g, "&#39;").replace(/"/g, "&quot;") : '-';
                        
                        return `<button class="btn btn-sm click-primary btn-lihat-detail" data-materi="${namaMateri}" data-total="${row.total_nilai}" data-detail='${detailJSON}'>Detail</button>`;
                    }
                }
            ],
            "order": [[3, 'desc']] 
        });

        $('#rekappenilaiantable tbody').on('click', '.btn-lihat-detail', function () {
            var materi = $(this).data('materi');
            var total = $(this).data('total'); 
            var detail = $(this).data('detail');

            $('#modalNamaMateri').text('Materi: ' + materi);
            $('#modalTotalResponden').text(detail.total_responden + ' Orang');
            $('#modalTotalNilai').text(total); 
            $('#modalSangatBaik').text(detail.sangat_baik);
            $('#modalBaik').text(detail.baik);
            $('#modalCukup').text(detail.cukup);
            $('#modalBuruk').text(detail.buruk);

            $('#detailPenilaianModal').modal('show');
        });
    });

    function filterData() {
        table.ajax.reload();
    }
</script>
@endpush
@endsection