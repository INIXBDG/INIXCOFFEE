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
                    <h3 class="card-title text-center my-1 mb-5">Database KPI <h3>
                            <div class="row">
                                <div class="col-12">
                                    <div class="card" style="width: 100%">
                                        <div class="card-body d-flex justify-content-start">
                                            <div class="col-md-4 mx-1">
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
                                            <div class="col-md-4 mx-1">
                                                <label for="bulan" class="form-label">Quartal</label>
                                                <select name="q_option" id="" class="form-select" required>
                                                    <option disabled selected>pilih quartal</option>
                                                    <option value="Q1">Q1</option>
                                                    <option value="Q2">Q2</option>
                                                    <option value="Q3">Q3</option>
                                                    <option value="Q4">Q4</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mx-1">
                                                <button type="submit" onclick="getData()" class="btn click-primary" style="margin-top: 37px">Cari Data</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row my-2">
                                        <div class="col-md-12" id="content">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <table id="table_karyawan" class="table table-bordered mt-4">
                                <thead>
                                    <tr>
                                        <th rowspan="2" style="font-size: 14px; text-align: center;">No</th>
                                        <th rowspan="2" style="font-size: 14px; text-align: center;">Nama Lengkap</th>
                                        <th rowspan="2" style="font-size: 14px; text-align: center;">NIP</th>
                                        <th rowspan="2" style="font-size: 14px; text-align: center;">Divisi</th>
                                        <th rowspan="2" style="font-size: 14px; text-align: center;">Jabatan</th>
                                        <th rowspan="2" style="font-size: 14px; text-align: center;">Status</th>
                                        <th rowspan="2" style="font-size: 14px; text-align: center;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_table">
                                    <tr style="color: black;">
                                        <td style="font-size: 14px;">Tidak Ada Data</td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" style="font-size: 14px; text-align: right; font-weight: bold;">Total Karyawan: 0</td>
                                    </tr>
                                </tbody>
                            </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveModalLabel">Confirm Approval</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="approveForm" method="POST">
                    @csrf
                    <p>Apakah Disetujui?</p>
                    <div id="manager-row">
                        <div class="btn-group" role="group" aria-label="Approval Options">
                            <input type="hidden" value="" id="id_net_sales" name="id_net_sales">
                            <button class="btn btn-outline-primary" type="submit">Ya</button>

                            <input type="radio" class="btn-check" name="approval" id="approveNo" value="2" autocomplete="off">
                            <label class="btn btn-outline-danger" for="approveNo" onclick="toggleAlasanManager(true)">Tidak</label>

                        </div>

                        <div class="mt-3" id="alasanManagerInput" style="display: none;">
                            <label for="alasan_manager" class="form-label">Alasan Penolakan</label>
                            <textarea class="form-control" id="alasan_manager" name="keterangan" rows="3"></textarea>
                            <input type="hidden" value="{{ auth()->user()->jabatan }}" name="jabatan">
                            <button class="btn btn-outline-success mt-3" type="submit">Kirim</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script>
    $('document').ready(function() {
        loadData();
    });

    function loadData() {
        let bodyTable = $('#tbody_table');

        $.ajax({
            url: "{{ route('GetDatabaseKPI') }}",
            type: 'get',
            contentType: 'json',
            success: function(response) {
                // Jika DataTable sudah diinisialisasi, hancurkan dulu
                if ($.fn.DataTable.isDataTable('#table_karyawan')) {
                    $('#table_karyawan').DataTable().destroy();
                }

                bodyTable.empty();

                let data = response.data;
                let jumlah = response.jumlah;

                if (data.length === 0) {
                    bodyTable.append(`
                    <tr>
                        <td colspan="7" style="text-align: center; color: red;">Tidak Ada Data</td>
                    </tr>
                `);
                } else {
                    data.forEach(function(item, index) {
                        let row = `
                        <tr>
                            <td style="font-size: 14px; text-align: center;">${index + 1}</td>
                            <td style="font-size: 14px; text-align: center;">${item.nama_lengkap}</td>
                            <td style="font-size: 14px; text-align: center;">${item.nip}</td>
                            <td style="font-size: 14px; text-align: center;">${item.divisi}</td>
                            <td style="font-size: 14px; text-align: center;">${item.jabatan}</td>
                            <td style="font-size: 14px; text-align: center;">${item.status}</td>
                            <td style="font-size: 14px; text-align: center;">
                                <div class="btn-group dropup">
                                    <button type="button" class="btn dropdown-toggle text-black" data-bs-toggle="dropdown">
                                        Actions
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#">Detail</a>
                                        <a class="dropdown-item" href="#">Edit Data</a>
                                        <button class="dropdown-item" type="button">Approved</button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `;
                        bodyTable.append(row);
                    });

                    bodyTable.append(`
                    <tr>
                        <td colspan="7" style="font-size: 14px; text-align: right; font-weight: bold;">
                            Total Karyawan: ${jumlah}
                        </td>
                    </tr>
                `);
                }

                $('#table_karyawan').DataTable({
                    paging: true,
                    searching: true,
                    info: true
                });
            },
            error: function(xhr) {
                console.error("Gagal memuat data:", xhr);
            }
        });
    }
</script>
@endpush
@endsection