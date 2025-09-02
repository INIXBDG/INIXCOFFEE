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
                        <div class="col-md-4">
                            <label for="tahun" class="form-label">Tahun</label>
                            <select id="tahun" class="form-select" aria-label="tahun">
                                <option disabled>Pilih Tahun</option>
                                @php
                                $tahun_sekarang = now()->year;
                                for ($tahun = 2020; $tahun <= $tahun_sekarang + 2; $tahun++) {
                                    $selected=$tahun==$tahun_sekarang ? 'selected' : '' ;
                                    echo "<option value=\" $tahun\" $selected>$tahun</option>";
                                    }
                                    @endphp
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="bulanRange" class="form-label">Bulan</label>
                            <select id="bulanRange" class="form-select">
                                <option disabled>Pilih Bulan</option>
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
                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <button type="submit" onclick="getData()" class="btn btn-primary">Cari Data</button>
                            @if (auth()->user()->jabatan == 'HRD' || auth()->user()->jabatan == 'Koordinator Office')
                            <button type="button" onclick="sinkronData()" class="btn btn-success">Sinkron Data</button>
                            @endif
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

    /* Atur font size tabel DataTable */
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

    function loadData() {
        $.ajax({
            url: "{{ route('get.laporanInsiden') }}",
            type: 'get',
            success: function(response) {
                let content = $('#content_Tbody');
                let data = response.data
                let no = 1;

                if (data.lenght === 0) {
                    content.append(`<tr style="background-color: rgba(0, 99, 71, 0.5); color:#fff">
                                            <td colspan="9">Tidak Ada Data</td>
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
                                                <a class="dropdown-item" href="/paymantAdvance/detail/1/view"><i class="fa-solid fa-magnifying-glass me-4"></i> Detail</a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="/paymantAdvance/detail/1/view" data-toggle="modal" data-target="#exampleModal"><i class="fa-solid fa-reply me-4"></i> Respon</a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        `);
                    })
                }
            }
        })
    }
</script>
@endpush
@endsection