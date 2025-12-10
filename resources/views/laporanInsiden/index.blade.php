@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
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
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <div class="text-end">
                        <a href="{{ route('create.laporanInsiden') }}" class="btn btn-primary mt-2 mb-2 p-2"><i class="fa-solid fa-plus"></i> Buat Laporan</a>
                    </div>
                    <h3 class="card-title text-center my-1 mb-5">Laporan Insiden</h3>

                    <div class="row mb-4">
                        <div class="col-md-2">
                            <label for="tahun" class="form-label">Tahun</label>
                            <select id="tahun" class="form-select" aria-label="tahun">
                                <option value="" selected disabled>Pilih Tahun</option>
                                @php
                                $tahun_sekarang = now()->year;
                                for ($tahun = 2020; $tahun <= $tahun_sekarang + 2; $tahun++) {
                                    $selected=$tahun==$tahun_sekarang ? 'selected' : '' ;
                                    echo "<option value=\" $tahun\" $selected>$tahun</option>";
                                    }
                                    @endphp
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="bulan" class="form-label">Bulan</label>
                            <select id="bulan" class="form-select">
                                <option value="" selected disabled>Pilih Bulan</option>
                                @php
                                $bulan_sekarang = now()->month;
                                $nama_bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                for ($bulan = 1; $bulan <= 12; $bulan++) {
                                    $selected=$bulan==$bulan_sekarang ? 'selected' : '' ;
                                    echo "<option value=\" $bulan\" $selected>{$nama_bulan[$bulan - 1]}</option>";
                                    }
                                    @endphp
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="kategori" class="form-label">Kategori</label>
                            <select id="kategori" class="form-select">
                                <option value="" selected disabled>Pilih Kategori</option>
                                <option value="major">Major</option>
                                <option value="minor">Minor</option>
                                <option value="moderate">Moderate</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <button type="button" onclick="getData()" class="btn btn-primary">Cari Data</button>
                        </div>
                    </div>

                    <div id="content">
                        <div class="card">
                            <div class="card-body p-3">
                                <table id="exampleTable" class="table table-bordered table-striped align-middle text-center" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Pelapor</th>
                                            <th>Kategori</th>
                                            <th>Kejadian</th>
                                            <th>Deskripsi</th>
                                            <th>Tanggal Kejadian</th>
                                            <th>Waktu Kejadian</th>
                                            <th>Status</th>
                                            <th>Waktu Pegajuan</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="content_Tbody">

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalResponsyy" tabindex="-1" role="dialog" aria-labelledby="modalResponsyyLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('respon.laporanInsiden') }}" method="post">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalResponsyyLabel">Respon</h5>
                </div>
                <div class="modal-body">
                    <input type="hidden" value="" name="id" id="id">
                    <div class="form-group mb-3">
                        <label for="nama_pelapor">Nama Pelapor</label>
                        <input type="text" class="form-control" id="nama_pelapor" readonly>
                    </div>
                    <div class="form-group mb-3">
                        <label for="kejadian">Kejadian</label>
                        <input type="text" class="form-control" id="kejadian" readonly>
                    </div>
                    <div class="form-group mb-3">
                        <label for="deskripsi">Deskripsi Kejadian</label>
                        <textarea id="deskripsi" class="form-control" readonly></textarea>
                    </div>
                    <div class="form-group mb-3">
                        <label for="status">Tanggapan</label>
                        <select name="status" id="status" class="form-control" required>
                            <option selected disabled>Tanggapan</option>
                            <option value="Dalam Penanganan">Dalam Penanganan</option>
                            <option value="Selesai">Selesai</option>
                            <option value="Tidak Ditangani">Tidak Ditangani</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="solusi" id="labelSolusi">Solusi</label>
                        <textarea name="solusi" id="solusi" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="status" value="">

                    <button type="submit" class="btn btn-primary" id="btnKirim">Kirim</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </form>
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
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    .modal-content {
        border-radius: 6px;
        box-shadow: 0 0 20px 8px rgba(0, 0, 0, 0.7);
    }

    .modal-backdrop.show {
        opacity: 0.75;
    }

    table.dataTable thead th,
    table.dataTable tbody td {
        font-size: 12px !important;
    }

    #content {
        font-size: 14px;
    }
</style>
@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script>
    $(document).ready(function() {
        loadData();
    })

    function getData() {
        let tahun = $('#tahun').val();
        let bulan = $('#bulan').val();
        let kategori = $('#kategori').val();

        // panggil loadData dengan parameter
        loadData(tahun, bulan, kategori);
    }

    function loadData(tahun = '', bulan = '', kategori = '') {
        $.ajax({
            url: "{{ route('get.laporanInsiden') }}",
            type: 'get',
            data: {
                tahun: tahun,
                bulan: bulan,
                kategori: kategori
            },
            success: function(response) {
                let content = $('#content_Tbody');
                content.empty(); // kosongkan sebelum isi ulang
                let data = response.data;
                let no = 1;

                if (data.length === 0) {
                    content.append(`
                    <tr style="background-color: rgba(0, 99, 71, 0.5); color:#fff">
                        <td colspan="10">Tidak Ada Data</td>
                    </tr>`);
                } else {
                    data.forEach(function(item) {
                        content.append(`
                            <tr>
                                <td>${no++}</td>
                                <td>${item.pelapor}</td>
                                <td>${item.kategori}</td>
                                <td>${item.kejadian}</td>
                                <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${item.deskripsi}</td>
                                <td>${item.tanggal}</td>
                                <td>${item.waktu}</td>
                                <td>${item.status}</td>
                                <td>${item.waktu_pengajuan}</td>
                                <td>
                                    <div class="btn-group dropup">
                                        <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Actions</button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="/laporan-insiden/detail/${item.id}"><i class="fa-solid fa-magnifying-glass me-4"></i> Detail</a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="/laporan-insiden/edit/${item.id}"><i class="fa-solid fa-pen-to-square me-4"></i> Edit</a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="/laporan-insiden/hapus/${item.id}"><i class="fa-regular fa-trash me-4"></i> Hapus</a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalResponsyy" data-nama="${item.pelapor}" data-kejadian="${item.kejadian}" data-deskripsi="${item.deskripsi}" data-id="${item.id}" data-status="${item.status}">
                                                <i class="fa-solid fa-reply me-4"></i> Respon
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        `);
                    });
                }
            }
        });
    }

    $(document).on("click", "[data-bs-target='#modalResponsyy']", function() {
        let nama = $(this).data("nama");
        let kejadian = $(this).data("kejadian");
        let deskripsi = $(this).data("deskripsi");
        let id = $(this).data("id");
        let status = $(this).data("status");

        $("#status").val(status);
        $("#id").val(id);
        $("#nama_pelapor").val(nama);
        $("#kejadian").val(kejadian);
        $("#deskripsi").val(deskripsi);
    });

    document.getElementById('tanggapan').addEventListener('change', function() {
        let labelSolusi = document.getElementById('labelSolusi');
        if (this.value === 'Tidak Ditangani') {
            labelSolusi.textContent = 'Alasan';
        } else if (this.value === 'selesai') {
            labelSolusi.textContent = 'Catatan';
        } else {
            labelSolusi.textContent = 'Solusi';
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        let status = document.getElementById('status').value;
        if (status === 'Selesai') {
            document.getElementById('btnKirim').style.display = 'none';
        }
    });
</script>
@endpush
@endsection
