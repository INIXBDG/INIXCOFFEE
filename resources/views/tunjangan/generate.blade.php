@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Loading Modal -->
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

    <!-- Modal Detail Tunjangan (Visible only for GM) -->
    @if(auth()->user()->jabatan === 'GM')
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Detail Tunjangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6 id="modalKaryawanName"></h6>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="detailTable">
                            <thead>
                                <tr>
                                    <th>Nama Tunjangan</th>
                                    <th>Keterangan</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr style="background-color: #006A67; color:white;">
                                    <td colspan="2">Total Tunjangan:</td>
                                    <td id="modal_total_tunjangan"></td>
                                </tr>
                                <tr style="background-color: #FF2929; color:white;">
                                    <td colspan="2">Total Potongan:</td>
                                    <td id="modal_total_potongan"></td>
                                </tr>
                                <tr>
                                    <td colspan="2"><strong>Total Bersih:</strong></td>
                                    <td id="modal_total_bersih"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" onclick="rejectTunjangan()">Reject</button>
                    <button type="button" class="btn btn-success" onclick="approveTunjangan()">Approve</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Rejection Note (Visible only for GM) -->
    <div class="modal fade" id="rejectionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Alasan Penolakan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <textarea class="form-control" id="rejection_note" rows="4" placeholder="Masukkan alasan penolakan..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" onclick="confirmReject()">Reject</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Content -->
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- HRD Section: Data Tunjangan Karyawan & Detail Tunjangan Karyawan -->
            @if(auth()->user()->jabatan === 'HRD')
            <div class="d-flex flex-wrap justify-content-end gap-2 mb-3">
                @can('Create Tunjangan')
                    <a href="{{ route('penghitunganTunjangan') }}" class="btn btn-md click-primary">
                        <img src="{{ asset('icon/plus.svg') }}" width="30px" class="d-none d-md-inline"> 
                        <span class="d-none d-md-inline">Penghitungan Tunjangan Umum Otomatis</span>
                        <span class="d-md-none">Tunjangan Otomatis</span>
                    </a>
                    <a href="{{ route('tunjangan.create') }}" class="btn btn-md click-primary">
                        <img src="{{ asset('icon/plus.svg') }}" width="30px" class="d-none d-md-inline">
                        <span class="d-none d-md-inline">Buat Jenis Tunjangan</span>
                        <span class="d-md-none">Jenis Tunjangan</span>
                    </a>
                    <a href="{{ route('createManual') }}" class="btn btn-md click-primary">
                        <img src="{{ asset('icon/plus.svg') }}" width="30px" class="d-none d-md-inline">
                        <span class="d-none d-md-inline">Manual Tunjangan</span>
                        <span class="d-md-none">Manual</span>
                    </a>
                @endcan
            </div>
            <div class="card m-2 m-md-4">
                <div class="card-body">
                    <h3 class="card-title text-center my-1">{{ __('Data Tunjangan') }}</h3>
                    <div class="row">
                        <div class="col-lg-6 col-12">
                            <div class="card m-2 m-md-4">
                                <div class="card-body">
                                    <h3 class="card-title text-center my-1">{{ __('Data Tunjangan Karyawan') }}</h3>
                                    <div class="card-body">
                                        <div class="row g-2">
                                            <div class="col-6 col-md-4">
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
                                            <div class="col-6 col-md-4">
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
                                            <div class="col-12 col-md-4">
                                                <label class="form-label d-none d-md-block">&nbsp;</label>
                                                <div class="d-flex gap-2">
                                                    <a href="#" class="btn btn-danger btn-sm flex-fill" id="exportpdf" onclick="exportPDF()">PDF</a>
                                                    <a href="#" class="btn btn-success btn-sm flex-fill" id="exportexcel" onclick="exportExcel()">Excel</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="tablekaryawan">
                                            <thead>
                                                <th scope="col">No</th>
                                                <th scope="col">Nama Karyawan</th>
                                                <th scope="col">Divisi</th>
                                                <th scope="col">Detail Absensi</th>
                                            </thead>
                                            <tbody>
                                                <!-- Data Karyawan akan diisi di sini -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-12">
                            <div class="card m-2 m-md-4">
                                <div class="card-body">
                                    <h3 class="card-title text-center my-1" id="detailTunjangan">{{ __('Detail Tunjangan Karyawan') }}</h3>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="card m-2 m-md-4">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <form method="POST" action="{{ route('tunjangan.storeManual') }}" id="form-tunjangan">
                                                                @csrf
                                                                <div class="row mb-3">
                                                                    <div class="col-md-12 d-flex justify-content-end">
                                                                        <a href="#" class="btn click-primary btn-sm" id="generateTunjangan" onclick="generateTunjanganUmum()">
                                                                            <span class="d-none d-md-inline">Generate Tunjangan Umum Karyawan</span>
                                                                            <span class="d-md-none">Generate Tunjangan</span>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                                <div class="row mb-3">
                                                                    <label for="karyawan_id" class="col-md-4 col-form-label text-md-start">{{ __('Nama Karyawan') }}</label>
                                                                    <div class="col-md-6">
                                                                        <input type="text" readonly class="form-control" name="nama_karyawan" id="nama_karyawan">
                                                                        <input type="hidden" class="form-control" name="karyawan_id" id="karyawan_id">
                                                                        <input type="hidden" class="form-control" name="jumlah_absen" id="jumlah_absen">
                                                                        <input type="hidden" class="form-control" name="keterlambatan" id="keterlambatan">
                                                                        <input type="hidden" class="form-control" name="absen_pulang" id="absen_pulang">
                                                                        <input type="hidden" class="form-control" name="bulan_tunjangan" id="bulan_tunjangan">
                                                                        <input type="hidden" class="form-control" name="tahun_tunjangan" id="tahun_tunjangan">
                                                                    </div>
                                                                </div>
                                                                <div id="tunjanganContainer"></div>
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <input type="hidden" class="form-control" name="nama_tunjangan" id="nama_tunjangan">
                                                                        <input type="hidden" name="tipe_tunjangan" id="tipe_tunjangan">
                                                                        <div class="row mb-3">
                                                                            <label for="nilai" class="col-md-4 col-form-label text-md-start">{{ __('Nilai') }}</label>
                                                                            <div class="col-md-6">
                                                                                <div class="input-group mb-3">
                                                                                    <span class="input-group-text">Rp.</span>
                                                                                    <input type="text" class="form-control @error('min_harga_pelatihan') is-invalid @enderror" name="nilai" id="nilai" placeholder="Nilai Tunjangan">
                                                                                </div>
                                                                                @error('nilai')
                                                                                    <span class="invalid-feedback" role="alert">
                                                                                        <strong>{{ $message }}</strong>
                                                                                    </span>
                                                                                @enderror
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-3">
                                                                            <label for="kelipatan" class="col-md-4 col-form-label text-md-start">{{ __('Satuan') }}</label>
                                                                            <div class="col-md-6">
                                                                                <input type="text" name="kelipatan" id="kelipatan" class="form-control">
                                                                                @error('kelipatan')
                                                                                    <span class="invalid-feedback" role="alert">
                                                                                        <strong>{{ $message }}</strong>
                                                                                    </span>
                                                                                @enderror
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-3">
                                                                            <div class="col-md-12 d-flex justify-content-center">
                                                                                <button type="button" class="btn btn-success btn-md" id="btn_generate" onclick="generateData()">Generate</button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row" id="keterangan"></div>
                                                                <div class="row justify-content-center">
                                                                    <div class="col-md-12">
                                                                        <div class="card mt-4">
                                                                            <div class="card-body">
                                                                                <div class="table-responsive">
                                                                                    <table class="table table-bordered">
                                                                                        <thead>
                                                                                            <tr>
                                                                                                <th>Nama Tunjangan</th>
                                                                                                <th>Detail</th>
                                                                                                <th>Nilai</th>
                                                                                                <th>Total</th>
                                                                                            </tr>
                                                                                        </thead>
                                                                                        <tbody id="dataPreview"></tbody>
                                                                                    </table>
                                                                                </div>
                                                                                <div>
                                                                                    <strong id="total">Total: 0</strong>
                                                                                </div>
                                                                                <div class="row mb-0">
                                                                                    <div class="col-md-12 offset-md-8">
                                                                                        <button type="submit" class="btn click-primary" id="submitData" style="padding:8px"
                                                                                            onclick="event.preventDefault(); showConfirmSimpan();">
                                                                                            {{ __('Simpan') }}
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- GM Section: Approval Tunjangan Karyawan -->
                    @if(auth()->user()->jabatan === 'GM')
                    <div class="card m-2 m-md-4">
                        <div class="card-body">
                            <h3 class="card-title text-center my-1">{{ __('Approval Tunjangan Karyawan') }}</h3>
                            <div class="card my-2">
                                <div class="card-body">
                                    <div class="row g-2">
                                        <div class="col-6 col-md-3">
                                            <label for="tahun" class="form-label">Tahun</label>
                                            <select id="tahun" class="form-select">
                                                @php
                                                $tahun_sekarang = now()->year;
                                                for ($tahun = 2020; $tahun <= $tahun_sekarang + 2; $tahun++) {
                                                    $selected = $tahun == $tahun_sekarang ? 'selected' : '';
                                                    echo "<option value=\"$tahun\" $selected>$tahun</option>";
                                                }
                                                @endphp
                                            </select>
                                        </div>
                                        <div class="col-6 col-md-3">
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
                                        <div class="col-6 col-md-3">
                                            <label class="form-label d-none d-md-block">&nbsp;</label>
                                            <button class="btn click-primary w-100" onclick="loadPendingApproval()">Cari Data</button>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <label class="form-label d-none d-md-block">&nbsp;</label>
                                            <button class="btn btn-success w-100" onclick="bulkApprove()">Approve Semua</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Tab Navigation -->
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#pending">Pending Approval</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#history">History</a>
                                </li>
                            </ul>
                            <!-- Tab Content -->
                            <div class="tab-content">
                                <!-- Pending Tab -->
                                <div id="pending" class="tab-pane fade show active">
                                    <div class="card mt-3">
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered" id="tablePending">
                                                    <thead>
                                                        <tr>
                                                            <th>Nama Karyawan</th>
                                                            <th>Divisi</th>
                                                            <th>Jumlah Item</th>
                                                            <th>Total Tunjangan</th>
                                                            <th>Total Potongan</th>
                                                            <th>Total Bersih</th>
                                                            <th>Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- History Tab -->
                                <div id="history" class="tab-pane fade">
                                    <div class="card mt-3">
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered" id="tableHistory">
                                                    <thead>
                                                        <tr>
                                                            <th>Tanggal</th>
                                                            <th>Nama Karyawan</th>
                                                            <th>Tunjangan</th>
                                                            <th>Total</th>
                                                            <th>Status</th>
                                                            <th>Approved By</th>
                                                            <th>Note</th>
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
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Wrapper untuk scroll horizontal */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    /* Styling untuk tabel mobile */
    @media screen and (max-width: 768px) {
        .table {
            font-size: 11px;
        }
        
        .table thead th {
            padding: 6px 4px;
            white-space: nowrap;
        }
        
        .table tbody td {
            padding: 6px 4px;
            white-space: nowrap;
        }
        
        /* Button sizing untuk mobile */
        .btn-sm {
            padding: 4px 8px;
            font-size: 10px;
        }
        
        .btn-md {
            padding: 6px 10px;
            font-size: 11px;
        }
        
        /* Card body padding reduction */
        .card-body {
            padding: 10px;
        }
        
        .card-title {
            font-size: 16px;
        }
        
        /* Modal adjustments */
        .modal-dialog {
            margin: 10px;
        }
        
        /* Form label smaller */
        .form-label {
            font-size: 12px;
        }
        
        .form-control, .form-select {
            font-size: 12px;
        }
    }

    @media screen and (max-width: 576px) {
        .table-responsive {
            overflow-x: auto;
        }
        .table {
            font-size: 10px;
        }
        .table th, .table td {
            padding: 4px 2px;
            white-space: nowrap;
        }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            font-size: 11px;
        }
        .nav-tabs .nav-link {
            font-size: 12px;
            padding: 6px 8px;
        }
        .card-title {
            font-size: 14px;
        }
        .btn, .btn-sm, .btn-md {
            font-size: 10px;
            padding: 4px 8px;
        }
    }
    
    /* Styling untuk DataTables responsive */
    table.dataTable.dtr-inline.collapsed > tbody > tr > td.dtr-control:before,
    table.dataTable.dtr-inline.collapsed > tbody > tr > th.dtr-control:before {
        background-color: #006A67;
    }
    
    .nav-tabs {
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .nav-tabs .nav-link {
        white-space: nowrap;
    }
    
    /* Button group responsive */
    @media screen and (max-width: 768px) {
        .d-flex.gap-2 {
            gap: 0.5rem !important;
        }
        
        .flex-fill {
            font-size: 11px;
        }
    }
    @media screen and (max-width: 576px) {
    .card-body {
        overflow-x: auto;
    }
}
</style>

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<!-- DataTables Responsive -->
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
<script>
    $(document).ready(function () {
        var userRole = '{{ auth()->user()->jabatan}}';
        var divisi = '{{ auth()->user()->karyawan->divisi }}';
        
        // HRD-specific initialization
        if (userRole === 'HRD') {
            $('#generateTunjangan').attr('disabled', true);
            $('#generateTunjangan').css('pointer-events', 'none');
            $('#generateTunjangan').css('opacity', '0.5');
            $('#submitData').attr('disabled', true);
            $('#submitData').css('pointer-events', 'none');
            $('#submitData').css('opacity', '0.5');
            var today = new Date();
            var day = today.getDate();
            if (day < 10 && day > 1) {
                $('#generateTunjanganBtn').attr('disabled', false);
            } else {
                $('#generateTunjanganBtn').attr('disabled', true);
                $('#generateTunjanganBtn').css('pointer-events', 'none');
                $('#generateTunjanganBtn').css('opacity', '0.5');
            }
            getUserall();
        }

        // GM-specific initialization
        if (userRole === 'GM') {
            loadPendingApproval();
        }
    });

    function getTunjangan(divisi, karyawanId) {
        let url = '/getJenisTunjanganIndex';
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var container = $('#tunjanganContainer');
                container.empty();
                var data = response.data;
                let html = '';
                html += '<div class="row mb-3">' +
                            '<label for="id_tunjangan" class="col-md-4 col-form-label text-md-start">{{ __('Tunjangan') }}</label>' +
                            '<div class="col-md-6">' +
                            '<select id="id_tunjangan" class="form-select" name="id_tunjangan" autocomplete="id_tunjangan" autofocus>' +
                            '<option value="" selected>Pilih Jenis</option>';
                $.each(data, function(index, item) {
                    let namaTunjanganId = item.nama_tunjangan.replace(/ /g, '_');
                    html += '<option value="'+namaTunjanganId+'" data-nilai="'+item.nilai+'" data-nama="'+item.nama_tunjangan+'" data-tipe="'+item.tipe+'">'+item.nama_tunjangan+'</option>';
                });
                if(divisi === 'Education'){
                    html += '<option value="Education" data-nama="Education">Education</option>'
                };
                html += '</select>' +
                        '</div>' +
                        '</div>';

                if (response.error) {
                    html += '<span class="invalid-feedback" role="alert">' +
                                '<strong>' + response.error + '</strong>' +
                            '</span>';
                }

                container.append(html);

                $('#id_tunjangan').change(function() {
                    var selectedOption = $(this).find('option:selected');
                    var nilai = selectedOption.data('nilai');
                    var tipe = selectedOption.data('tipe');
                    var nama = selectedOption.data('nama');
                    if(nama === 'Education'){
                        tunjanganEdu(karyawanId);
                    }else{
                        $('#nilai').val(nilai);
                        $('#tipe_tunjangan').val(tipe);
                        $('#nama_tunjangan').val(nama);
                    }
                });
            },
            error: function(xhr, status, error) {
                console.error("Terjadi kesalahan: " + error);
            }
        });
    }

    function tunjanganEdu(karyawanId){
        var bulan = $('#bulan_tunjangan').val();
        var tahun = $('#tahun_tunjangan').val();
        $.ajax({
            type: "GET",
            url: "{{ route('getTunjanganEdu', ['id' => ':id', 'month' => ':month', 'year' => ':year']) }}".replace(':id', karyawanId).replace(':month', bulan).replace(':year', tahun),
            dataType: "json",
            success: function(data) {
                $('#tipe_tunjangan').val('Tunjangan');
                $('#nilai').val(data.total_tunjangan);
                $('#nama_tunjangan').val('Education');
                $('#kelipatan').val('1');
            }
        });
    }

    function getDataTunjangan(karyawanId) {
        moment.locale('id');
        var tahun = $("#tahun").val();
        var bulan = $("#bulan").val();
        var bulans;

        if (bulan == 1) {
            bulans = 12;
            tahun -= 1;
        } else {
            bulans = bulan - 1;
        }
        var idBulan = moment(bulans, 'M').format('MMMM');
        $('#keterangan').empty();
        $('#detailTunjangan').empty();
        $('#detailTunjangan').text('Detail Tunjangan Karyawan Bulan ' + idBulan + ' ' + tahun);
        $('#bulan_tunjangan').val(bulans);
        $('#tahun_tunjangan').val(tahun);
        var absenPulang = '';
        $.ajax({
            type: "GET",
            url: "{{ route('jumlahAbsensi', ['id_karyawan' => ':karyawan_id', 'bulan' => ':bulan', 'tahun' => ':tahun']) }}".replace(':karyawan_id', karyawanId).replace(':bulan', bulans).replace(':tahun', tahun),
            dataType: "json",
            success: function(response) {
                var data = response.data;
                var divisi = data.karyawan.divisi;
                var absenPulang = data.jumlah_tidak_absen_pulang;

                getTunjangan(divisi, karyawanId);
                if(response.success == false){
                    alert(response.message);
                    $('#jumlah_absen').val('0');
                    $('#keterlambatan').val('Tidak Pernah Terlambat');
                    $('#nama_karyawan').val('Error');
                    $('#karyawan_id').val(karyawanId);
                    $('#keterangan').empty();
                    $('#generateTunjangan').attr('disabled', true);
                } else {
                    $('#jumlah_absen').val(data.jumlah_absensi !== null && data.jumlah_absensi !== undefined ? data.jumlah_absensi : 0);
                    $('#nama_karyawan').val(data.karyawan.nama_lengkap);
                    $('#karyawan_id').val(data.karyawan.id);
                    $('#keterlambatan').val(data.keterangan);
                    $('#absenPulang').val(absenPulang);

                    var listItem = '<div class="col-md-12">' + 
                    '<h6>Keterangan</h6>' +
                    '<ul>' + 
                        '<li>Jumlah absen Pada Bulan Ini (Sudah Termasuk Pengurangan Cuti lebih dari 3 hari): ' + data.jumlah_absensi + '</li>' + 
                        '<li>Jumlah Tidak Absen Pulang: ' + data.jumlah_tidak_absen_pulang + '</li>' + 
                        '<li>Keterlambatan Pada Bulan Ini: ' + data.keterangan + '</li>' + 
                        '<li>Cuti Pada Bulan Ini:</li>' +
                        '<ul>';

                        data.cutikaryawan.forEach(function(cuti) {
                            listItem += '<li>' +
                                        'Tipe: ' + cuti.tipe + ', ' +
                                        'Tanggal Awal: ' + cuti.tanggal_awal + ', ' +
                                        'Tanggal Akhir: ' + cuti.tanggal_akhir + ', ' +
                                        'Durasi: ' + cuti.durasi + ' hari, ' +
                                        'Alasan: ' + (cuti.alasan || 'Tidak ada alasan') + 
                                        '</li>';
                        });

                        listItem += '</ul>' +
                                    '</ul>' +
                                    '</div>';
                        $('#keterangan').append(listItem);

                        $('#generateTunjangan').attr('disabled', false);
                        $('#generateTunjangan').css('opacity', '1.5');
                        $('#generateTunjangan').css('pointer-events', 'auto');
                }
            },
            error: function(xhr, status, error) {
                console.log("Terjadi kesalahan: " + error);
                $('#generateTunjangan').attr('disabled', true);
            }
        });
        cekdata(karyawanId, bulans, tahun, absenPulang);
        $('#dataPreview').empty();
        updateTotal();
    }

    function cekdata(karyawanId, bulan, tahun, absenPulang) {
        var url = "{{ route('getTunjanganSayaGenerate', ['id' => ':karyawan_id', 'month' => ':bulan', 'year' => ':tahun']) }}";
        url = url.replace(':karyawan_id', karyawanId).replace(':bulan', bulan).replace(':tahun', tahun);
        
        $.ajax({
            type: "GET",
            url: url,
            dataType: "json",
            success: function(response) {
                if (!response.data || response.data.length === 0) {
                    $('#generateTunjangan').attr('disabled', false);
                    $('#generateTunjangan').css('opacity', '1.5');
                    $('#generateTunjangan').css('pointer-events', 'auto');
                    return;
                }

                var data = response.data;
                data.forEach(function(item) {
                    var nama_tunjangan = item.jenistunjangan.nama_tunjangan;
                    var hitung = item.total/item.jenistunjangan.nilai;
                    var kelipatan = (item.jenistunjangan.hitung === 'Perhari') ? $('#jumlah_absen').val() : hitung;
                    if(nama_tunjangan == 'Education'){
                        var listItem =  '<tr>' +
                        '<td>' + nama_tunjangan + '</td>' +
                        '<td>Jumlah Absensi / Satuan: </td>' +
                        '<td>1</td>' +
                        '<td> = ' + formatRupiah(item.total) + 
                        '<input type="hidden" name="dataTunjangan[' + item.jenistunjangan.nama_tunjangan + ']" value="' + item.total + '">' +
                        '</td>' +
                        '<td>' +
                        '<button type="button" class="btn btn-danger btn-sm" onclick="deleteRow(this)">Hapus</button>' +
                        '</td>' +
                        '</tr>';
                    }else if(item.jenistunjangan.hitung === 'Perbulan'){
                        var listItem =  '<tr>' +
                        '<td>' + nama_tunjangan + '</td>' +
                        '<td>Jumlah Absensi / Satuan: </td>' +
                        '<td>1 x '+item.total+'</td>' +
                        '<td> = ' + formatRupiah(item.total) + 
                        '<input type="hidden" name="dataTunjangan[' + item.jenistunjangan.nama_tunjangan + ']" value="' + item.total + '">' +
                        '</td>' +
                        '<td>' +
                        '<button type="button" class="btn btn-danger btn-sm" onclick="deleteRow(this)">Hapus</button>' +
                        '</td>' +
                        '</tr>';
                    }else{
                        var listItem =  '<tr>' +
                        '<td>' + item.jenistunjangan.nama_tunjangan + '</td>' +
                        '<td>Jumlah Absensi / Satuan: </td>' +
                        '<td>' + kelipatan + ' x ' + formatRupiah(item.jenistunjangan.nilai) + '</td>' +
                        '<td> = ' + formatRupiah(item.total) + 
                        '<input type="hidden" name="dataTunjangan[' + item.jenistunjangan.nama_tunjangan + ']" value="' + item.total + '">' +
                        '</td>' +
                        '<td>' +
                        '<button type="button" class="btn btn-warning btn-sm" onclick="editRow(this)">Edit</button>' +
                        '<button type="button" class="btn btn-danger btn-sm" onclick="deleteRow(this)">Hapus</button>' +
                        '</td>' +
                        '</tr>';
                    }

                    $('#dataPreview').append(listItem);
                });

                $('#generateTunjangan').attr('disabled', true);
                $('#generateTunjangan').css('pointer-events', 'none');
                $('#generateTunjangan').css('opacity', '0.5');
                updateTotal();
                var nama_karyawan = $('#nama_karyawan').val();
                alert('Tunjangan untuk karyawan '+ nama_karyawan +' sudah digenerate sebelumya. Hati-hati pada saat mengubah atau menghapus data');
            },
            error: function(xhr, status, error) {
                console.log("Terjadi kesalahan: " + error);
            }
        });
    }

    function exportPDF(){
        var tahun = $('#tahun').val();
        var bulan = $('#bulan').val();
        if (bulan && tahun) {
            var url = "{{ url('tunjanganExportPDF') }}/" + bulan + "/" + tahun;
            window.location.href = url;
        } else {
            alert('Silakan pilih bulan dan tahun terlebih dahulu.');
        }
    }

    function exportExcel(){
        var tahun = $('#tahun').val();
        var bulan = $('#bulan').val();
        if (bulan && tahun) {
            var url = "{{ url('tunjanganExportExcel') }}/" + bulan + "/" + tahun;
            window.location.href = url;
        } else {
            alert('Silakan pilih bulan dan tahun terlebih dahulu.');
        }
    }

    function formatRupiah(angka, prefix) {
        var isNegative = angka < 0;
        var number_string = Math.abs(angka).toString();
        var split = number_string.split('.');
        var bulat = split[0];
        var desimal = split[1] || '';
        var sisa = bulat.length % 3;
        var rupiah = bulat.substr(0, sisa);
        var ribuan = bulat.substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            var separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        desimal = desimal.substr(0, 2);
        rupiah = rupiah + (desimal ? ',' + desimal : '');

        rupiah = prefix ? prefix + ' ' + rupiah : rupiah;

        if (isNegative) {
            rupiah = '-' + rupiah;
        }

        return rupiah;
    }

    function getUserall() {
        if ($.fn.DataTable.isDataTable('#tablekaryawan')) {
            $('#tablekaryawan').DataTable().destroy();
        }
        var tableKaryawan = $('#tablekaryawan').DataTable({
                responsive: true,
                 autoWidth: false,
            "ajax": {
                "url": "{{ route('getUserall') }}",
                "type": "GET",
                "beforeSend": function () {
                    $('#loadingModal').modal('show');
                },
                "complete": function () {
                    $('#loadingModal').modal('hide');
                }
            },
            "columns": [
                {"data": "id"},
                {"data": "karyawan.nama_lengkap"},
                {"data": "karyawan.divisi"},
                {
                    "data": null,
                    "render": function(data, type, row) {
                        return '<button type="button" onclick="getDataTunjangan(' + row.id + ')" class="btn click-primary btn-sm">Detail</button>';
                    },
                }
            ],
            "responsive": true,
            "autoWidth": false,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json"
            },
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Semua"]]
        });
    }

    var totalTunjangan = 0.0;
    var totalPotongan = 0.0;

    function generateData() {
        var tunjanganName = $('#id_tunjangan').val();
        var tunjanganValue = parseFloat($('#nilai').val());
        var kelipatan = parseFloat($('#kelipatan').val());
        var tipeTunjangan = $('#tipe_tunjangan').val();
        var nama = $('#nama_tunjangan').val();

        var totalTunjanganPerItem = kelipatan * tunjanganValue;

        if (tipeTunjangan === 'Potongan') {
            totalPotongan += totalTunjanganPerItem;
        } else {
            totalTunjangan += totalTunjanganPerItem;
        }

        if(nama == 'Education'){
            var listItem =  '<tr>' +
                        '<td>' + nama + '</td>' +
                        '<td>Jumlah Absensi / Satuan: </td>' +
                        '<td>1</td>' +
                        '<td> = ' + formatRupiah(totalTunjanganPerItem) + 
                        '<input type="hidden" name="dataTunjangan[' + tunjanganName + ']" value="' + totalTunjanganPerItem + '">' +
                        '</td>' +
                        '<td>' +
                        '<button type="button" class="btn btn-danger btn-sm" onclick="deleteRow(this)">Hapus</button>' +
                        '</td>' +
                        '</tr>';
        }else{
            var listItem =  '<tr>' +
                        '<td>' + nama + '</td>' +
                        '<td>Jumlah Absensi / Satuan: </td>' +
                        '<td>' + kelipatan + ' x ' + formatRupiah(tunjanganValue) + '</td>' +
                        '<td> = ' + formatRupiah(totalTunjanganPerItem) + 
                        '<input type="hidden" name="dataTunjangan[' + tunjanganName + ']" value="' + totalTunjanganPerItem + '">' +
                        '</td>' +
                        '<td>' +
                        '<button type="button" class="btn btn-warning btn-sm" onclick="editRow(this)">Edit</button>' +
                        '<button type="button" class="btn btn-danger btn-sm" onclick="deleteRow(this)">Hapus</button>' +
                        '</td>' +
                        '</tr>';
        }

        $('#dataPreview').append(listItem);
        updateTotal();
    }

    function calculateTotalHours(jamMulai, jamSelesai) {
        if (jamMulai && jamSelesai) {
            var start = moment(jamMulai, "HH:mm");
            var end = moment(jamSelesai, "HH:mm");
            var duration = moment.duration(end.diff(start));
            return duration.asHours().toFixed(2);
        }
        return '0.00';
    }

    function cekLembur(id) {
        var bulan = $('#bulan').val() - 1;
        var tahun = $('#tahun').val();
        var url = '/getOvertimeLemburByKaryawan/' + id + '/' + bulan + '/' + tahun;

        return new Promise(function(resolve, reject) {
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    var data = response.data;
                    var totalLembur = 0;

                    if (data.length > 0) {
                        data.forEach(function(item) {
                            if(item.hitunglembur.approval_gm == '1'){
                                var jamLembur = calculateTotalHours(item.jam_mulai, item.jam_selesai);
                                var nilaiPerJam = item.hitunglembur.nilai_lembur;
                                totalLembur += jamLembur * nilaiPerJam;
                            }
                        });
                    }

                    resolve(totalLembur);
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching data:", error);
                    reject(0);
                }
            });
        });
    }

    function generateTunjanganUmum() {
        var url = '/getJenisTunjanganUmum';
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var data = response.data;
                $.each(data, function(index, item) {
                    var tunjanganName = item.nama_tunjangan;
                    var tunjanganValue = parseFloat(item.nilai);
                    var keterlambatan = $('#keterlambatan').val();
                    var absenPulang = $('#absenPulang').val();
                    var id_karyawan = $('#karyawan_id').val();
                    if (tunjanganName == "Lembur") {
                        cekLembur(id_karyawan).then(function(lembur) {
                            item.nilai = lembur;
                            updateTunjanganRow(tunjanganName, lembur);
                        }).catch(function(error) {
                            console.error("Gagal mendapatkan data lembur:", error);
                        });
                        return;
                    }

                    if (keterlambatan === 'Keterlambatan > 15 Menit' && tunjanganName === 'Absensi') {
                        return;
                    }else if(absenPulang !== null && tunjanganName === "Absensi"){
                        return;
                    }

                    var kelipatan = (tunjanganName === 'Absensi' || tunjanganName === 'Lembur') ? 1 : $('#jumlah_absen').val();
                    var tipeTunjangan = item.tipe;

                    var totalTunjanganPerItem = kelipatan * tunjanganValue;

                    if (tipeTunjangan === 'Potongan') {
                        totalPotongan += totalTunjanganPerItem;
                    } else {
                        totalTunjangan += totalTunjanganPerItem;
                    }

                    var listItem =  '<tr>' +
                                    '<td>' + tunjanganName + '</td>' +
                                    '<td>Jumlah Absensi / Satuan: </td>' +
                                    '<td>' + kelipatan + ' x ' + formatRupiah(tunjanganValue) + '</td>' +
                                    '<td> = ' + formatRupiah(totalTunjanganPerItem) + 
                                    '<input type="hidden" name="dataTunjangan[' + tunjanganName + ']" value="' + totalTunjanganPerItem + '">' +
                                    '</td>' +
                                    '<td>' +
                                    '<button type="button" class="btn btn-warning btn-sm" onclick="editRow(this)">Edit</button>' +
                                    '<button type="button" class="btn btn-danger btn-sm" onclick="deleteRow(this)">Hapus</button>' +
                                    '</td>' +
                                    '</tr>';

                    $('#dataPreview').append(listItem);
                });

                updateTotal();

                $('#generateTunjangan').attr('disabled', true);
                $('#generateTunjangan').css('pointer-events', 'none');
                $('#generateTunjangan').css('opacity', '0.5');
            },
            error: function(xhr, status, error) {
                console.error("Terjadi kesalahan: " + error);
            }
        });
    }

    function updateTunjanganRow(nama, nilai) {
        var kelipatan = (nama === 'Absensi' || nama === 'Lembur') ? 1 : $('#jumlah_absen').val();
        var totalTunjanganPerItem = kelipatan * nilai;

        var listItem = '<tr>' +
                    '<td>' + nama + '</td>' +
                    '<td>Jumlah Absensi / Satuan: </td>' +
                    '<td>' + kelipatan + ' x ' + formatRupiah(nilai) + '</td>' +
                    '<td> = ' + formatRupiah(totalTunjanganPerItem) + 
                    '<input type="hidden" name="dataTunjangan[' + nama + ']" value="' + totalTunjanganPerItem + '">' +
                    '</td>' +
                    '<td>' +
                    '<button type="button" class="btn btn-warning btn-sm" onclick="editRow(this)">Edit</button>' +
                    '<button type="button" class="btn btn-danger btn-sm" onclick="deleteRow(this)">Hapus</button>' +
                    '</td>' +
                    '</tr>';

        $('#dataPreview').append(listItem);
        updateTotal();
    }

    function updateTotal() {
        totalTunjangan = 0.0;
        totalPotongan = 0.0;

        $('#dataPreview tr').each(function() {
            var totalPerItem = parseFloat($(this).find('input[type="hidden"]').val());
            var tipeTunjangan = $(this).find('td:eq(0)').text();

            if (totalPerItem < 0) {
                totalPotongan += totalPerItem;
            } else {
                totalTunjangan += totalPerItem;
            }
        });

        var totalAkhir = (totalTunjangan + totalPotongan);
        $('#submitData').attr('disabled', false);
        $('#submitData').css('pointer-events', 'auto');
        $('#submitData').css('opacity', '1');
        $('#total').text('Total Tunjangan: ' + formatRupiah(totalTunjangan) + ' | Total Potongan: ' + formatRupiah(totalPotongan) + ' | Total Bersih: ' + formatRupiah(totalAkhir));
    }

    function editRow(button) {
        var row = $(button).closest('tr');
        var tunjanganName = row.find('td:eq(0)').text();
        var kelipatan = row.find('td:eq(2)').text().split(' x ')[0];
        var nilai = row.find('td:eq(2)').text().split(' x ')[1].replace('Rp. ', '').replace('.', '').trim();

        row.find('td:eq(0)').html('<input type="text" class="form-control nilai" value="' + tunjanganName + '" />');
        row.find('td:eq(2)').html('<input type="number" class="form-control kelipatan" value="' + kelipatan + '" /> x <input type="text" class="form-control" value="' + nilai + '" /> <button type="button" class="btn btn-sm btn-primary" onclick="ambilNilaiAbsensi(this)">Ambil nilai Absensi</button>');

        $(button).text('Simpan').attr('onclick', 'saveRow(this)');
    }

    function ambilNilaiAbsensi(button) {
        var row = $(button).closest('tr');
        var tunjanganName = row.find('.nilai').val();

        if (tunjanganName === 'Absensi') {
            var keterlambatan = 'Terlambat > 15 Menit';
            if(keterlambatan === 'Terlambat > 15 Menit'){
                row.find('.kelipatan').val(0);
            }else{
                row.find('.kelipatan').val(1);
            }
        } else {
            var jumlahAbsensi = $('#jumlah_absen').val();
            row.find('.kelipatan').val(jumlahAbsensi);
        }
    }

    function saveRow(button) {
        var row = $(button).closest('tr');
        var tunjanganName = row.find('input[type="text"]').eq(0).val();
        var kelipatan = row.find('input[type="number"]').val();
        var nilai = row.find('input[type="text"]').eq(1).val();

        var totalTunjanganPerItem = kelipatan * parseFloat(nilai.replace(/\./g, '').replace(',', '.'));

        row.find('td:eq(0)').text(tunjanganName);
        row.find('td:eq(2)').html(kelipatan + ' x ' + formatRupiah(nilai));
        row.find('td:eq(3)').html(' = ' + formatRupiah(totalTunjanganPerItem) + 
            '<input type="hidden" name="dataTunjangan[' + tunjanganName + ']" value="' + totalTunjanganPerItem + '">');

        $(button).text('Edit').attr('onclick', 'editRow(this)');

        updateTotal();
    }

    function deleteRow(button) {
        var row = $(button).closest('tr');
        var tunjanganName = row.find('td:eq(0)').text();
        var deletedDataInput = '<input type="hidden" name="deletedata[]" value="' + tunjanganName + '">';

        $('#form-tunjangan').append(deletedDataInput);
        row.remove();
        updateTotal();
        checkDeletedData();
    }

    function checkDeletedData() {
        var deletedData = $('input[name="deletedata[]"]').map(function() {
            return $(this).val();
        }).get();

        var hasRequiredValues = deletedData.includes('Makan') || deletedData.includes('Transport') || deletedData.includes('Absensi');

        if (hasRequiredValues) {
            $('#generateTunjangan').attr('disabled', false);
            $('#generateTunjangan').css('pointer-events', 'auto');
            $('#generateTunjangan').css('opacity', '1');
        } else {
            $('#generateTunjangan').attr('disabled', true);
            $('#generateTunjangan').css('pointer-events', 'none');
            $('#generateTunjangan').css('opacity', '0.5');
        }
    }

    let currentKaryawan = null;

    function loadPendingApproval() {
        if ($.fn.DataTable.isDataTable('#tablePending')) {
            $('#tablePending').DataTable().destroy();
        }

        var bulan = $('#bulan').val();
        var tahun = $('#tahun').val();

        $('#tablePending').DataTable({
            responsive: true,
            autoWidth: false,
            ajax: {
                url: '/getTunjanganPendingApproval/' + bulan + '/' + tahun,
                type: 'GET',
                beforeSend: function() {
                    $('#loadingModal').modal('show');
                },
                complete: function() {
                    $('#loadingModal').modal('hide');
                },
                dataSrc: function(json) {
                    if (!json.success) {
                        alert('Error: ' + (json.message || 'Terjadi kesalahan'));
                        return [];
                    }
                    return json.data || [];
                },
                error: function(xhr, error, thrown) {
                    console.error('Ajax error:', error);
                    console.error('Status:', xhr.status);
                    console.error('Response:', xhr.responseText);
                    $('#loadingModal').modal('hide');
                    alert('Error memuat data. Cek console untuk detail.');
                }
            },
            columns: [
                { 
                    data: 'nama_karyawan',
                    defaultContent: '-'
                },
                { 
                    data: 'divisi',
                    defaultContent: '-'
                },
                { 
                    data: 'jumlah_item',
                    defaultContent: '0'
                },
                { 
                    data: 'total_tunjangan',
                    render: function(data) {
                        return formatRupiah(data || 0);
                    },
                    defaultContent: 'Rp. 0'
                },
                { 
                    data: 'total_potongan',
                    render: function(data) {
                        return formatRupiah(data || 0);
                    },
                    defaultContent: 'Rp. 0'
                },
                { 
                    data: 'total_bersih',
                    render: function(data) {
                        return formatRupiah(data || 0);
                    },
                    defaultContent: 'Rp. 0'
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        return '<button class="btn btn-sm click-primary" onclick="showDetail(' + 
                            row.id_karyawan + ', \'' + 
                            row.nama_karyawan + '\', ' + 
                            row.bulan + ', ' + 
                            row.tahun + ')">Detail</button>';
                    },
                    defaultContent: '<button class="btn btn-sm btn-secondary" disabled>No Data</button>'
                }
            ],
            "responsive": true,
            "autoWidth": false,
            "pageLength": 10,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json",
                "emptyTable": "Tidak ada data tunjangan pending untuk periode ini",
                "zeroRecords": "Tidak ada data yang cocok",
                "loadingRecords": "Memuat data..."
            }
        });
    }

    function showDetail(idKaryawan, namaKaryawan, bulan, tahun) {
        currentKaryawan = { id_karyawan: idKaryawan, bulan: bulan, tahun: tahun };
        
        $('#modalKaryawanName').text(namaKaryawan);
        
        $.ajax({
            url: '/getTunjanganPendingApproval/' + $('#bulan').val() + '/' + $('#tahun').val(),
            type: 'GET',
            success: function(response) {
                var karyawanData = response.data.find(k => k.id_karyawan == idKaryawan);
                
                var tbody = $('#detailTable tbody');
                tbody.empty();
                
                karyawanData.details.forEach(function(item) {
                    tbody.append(
                        '<tr>' +
                        '<td>' + item.nama_tunjangan + '</td>' +
                        '<td>' + item.keterangan + '</td>' +
                        '<td>' + formatRupiah(item.total) + '</td>' +
                        '</tr>'
                    );
                });
                
                $('#modal_total_tunjangan').text(formatRupiah(karyawanData.total_tunjangan));
                $('#modal_total_potongan').text(formatRupiah(karyawanData.total_potongan));
                $('#modal_total_bersih').text(formatRupiah(karyawanData.total_bersih));
                
                $('#detailModal').modal('show');
            }
        });
    }

    function approveTunjangan() {
        if (!currentKaryawan) return;

        Swal.fire({
            title: 'Approve tunjangan?',
            text: 'Approve tunjangan untuk karyawan ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Approve',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/approveTunjangan',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id_karyawan: currentKaryawan.id_karyawan,
                        bulan: currentKaryawan.bulan,
                        tahun: currentKaryawan.tahun,
                        type: 'all'
                    },
                    success: function(response) {
                        Swal.fire('Berhasil!', response.message, 'success');
                        $('#detailModal').modal('hide');
                        loadPendingApproval();
                    },
                    error: function() {
                        Swal.fire('Gagal!', 'Terjadi kesalahan', 'error');
                    }
                });
            }
        });
    }

    function rejectTunjangan() {
        $('#detailModal').modal('hide');
        $('#rejectionModal').modal('show');
    }

    function confirmReject() {
        var note = $('#rejection_note').val();

        if (!note) {
            Swal.fire('Alasan penolakan harus diisi', '', 'warning');
            return;
        }

        Swal.fire({
            title: 'Konfirmasi Penolakan',
            text: 'Yakin ingin menolak tunjangan ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Tolak',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/rejectTunjangan',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id_karyawan: currentKaryawan.id_karyawan,
                        bulan: currentKaryawan.bulan,
                        tahun: currentKaryawan.tahun,
                        type: 'all',
                        rejection_note: note
                    },
                    success: function(response) {
                        Swal.fire('Ditolak!', response.message, 'success');
                        $('#rejectionModal').modal('hide');
                        $('#rejection_note').val('');
                        loadPendingApproval();
                    },
                    error: function() {
                        Swal.fire('Gagal!', 'Terjadi kesalahan', 'error');
                    }
                });
            }
        });
    }

    function bulkApprove() {
        if (confirm('Approve SEMUA tunjangan pending di periode ini?')) {
            $.ajax({
                url: '/bulkApproveTunjangan',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    bulan: $('#bulan').val(),
                    tahun: $('#tahun').val()
                },
                success: function(response) {
                    alert(response.message);
                    loadPendingApproval();
                },
                error: function() {
                    alert('Terjadi kesalahan');
                }
            });
        }
    }

    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        if ($(e.target).attr('href') == '#history') {
            loadHistory();
        }
    });

    function loadHistory() {
        if ($.fn.DataTable.isDataTable('#tableHistory')) {
            $('#tableHistory').DataTable().destroy();
        }

        var bulan = $('#bulan').val();
        var tahun = $('#tahun').val();

        $('#tableHistory').DataTable({
                responsive: true,
                autoWidth: false,
            ajax: {
                url: '/getApprovalHistory/' + bulan + '/' + tahun,
                type: 'GET',
                dataSrc: 'data'
            },
            columns: [
                { 
                    data: 'approved_at',
                    render: function(data) {
                        return new Date(data).toLocaleString('id-ID');
                    }
                },
                { data: 'karyawan.nama_lengkap' },
                { data: 'jenistunjangan.nama_tunjangan' },
                { 
                    data: 'total',
                    render: function(data) {
                        return formatRupiah(data);
                    }
                },
                { 
                    data: 'status_approval',
                    render: function(data) {
                        if (data == 'approved') {
                            return '<span class="badge bg-success">Approved</span>';
                        } else {
                            return '<span class="badge bg-danger">Rejected</span>';
                        }
                    }
                },
                { 
                    data: 'approved_by',
                    render: function(data, type, row) {
                        return row.approved_by ? (row.approved_by.name || '-') : '-';
                    }
                },
                { 
                    data: 'rejection_note',
                    render: function(data) {
                        return data || '-';
                    }
                }
            ],
            "responsive": true,
            "autoWidth": false,
            "order": [[0, 'desc']],
            "pageLength": 10,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json"
            }
        });
    }
    function showConfirmSimpan() {
        Swal.fire({
            title: 'Konfirmasi Simpan',
            text: 'Apakah Anda Yakin ingin menyimpan data tunjangan?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('form-tunjangan').submit();
            }
        });
    }

</script>
@endpush
@endsection