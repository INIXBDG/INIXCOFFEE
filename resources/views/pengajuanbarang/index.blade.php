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
                        @method('PUT')
                        <p>Apakah Disetujui?</p>
                        <div id="manager-row">
                            {{-- <div id="manager-row"> --}}
                                <div class="btn-group" role="group" aria-label="Approval Options">
                                    <input type="radio" class="btn-check" name="approval" id="approveYes" value="1" autocomplete="off" checked>
                                    <label class="btn btn-outline-primary" for="approveYes" onclick="toggleAlasanManager(false)">Ya</label>

                                    <input type="radio" class="btn-check" name="approval" id="approveNo" value="2" autocomplete="off">
                                    <label class="btn btn-outline-danger" for="approveNo" onclick="toggleAlasanManager(true)">Tidak</label>
                                </div>

                                <div class="mt-3" id="alasanManagerInput" style="display: none;">
                                    <label for="alasan_manager" class="form-label">Alasan Penolakan</label>
                                    <textarea class="form-control" id="alasan_manager" name="alasan" rows="3"></textarea>
                                </div>
                            {{-- </div> --}}
                        </div>
                        @php
                            $jabatan = auth()->user()->jabatan;
                        @endphp
                        @if ($jabatan == 'Finance & Accounting')
                            <div class="row my-0 mx-0">
                                    <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select" onchange="toggleFinanceInputs(this.value)">
                                    <option value="Sedang Dikonfirmasi oleh Bagian Finance kepada General Manager">Sedang Dikonfirmasi oleh Bagian Finance kepada General Manager</option>
                                    <option value="Sedang Dikonfirmasi oleh Bagian Finance kepada Direksi">Sedang Dikonfirmasi oleh Bagian Finance kepada Direksi</option>
                                    <option value="Finance Menunggu Approve Direksi">Finance Menunggu Approve Direksi</option>
                                    <option value="Membuat Permintaan Ke Direktur Utama">Membuat Permintaan Ke Direktur Utama</option>
                                    <option value="Pengajuan sedang dalam proses Pencairan">Pengajuan sedang dalam proses Pencairan</option>
                                    <option value="Pencairan Sudah Selesai">Pencairan Sudah Selesai</option>
                                    <option value="Selesai">Selesai</option>
                                    {{-- <option value="Pencairan Sudah Selesai">Pencairan Sudah Selesai</option> --}}
                                </select>
                            </div>
                            <div id="finance_extra_inputs" style="display: none;">
                                <div class="mb-3">
                                    <label for="no_kk" class="form-label">No KK</label>
                                    <input type="text" class="form-control" name="no_kk" id="no_kk" value="KK-">
                                </div>
                                <div class="mb-3">
                                    <label for="no_akun" class="form-label">Akun</label>
                                    <select class="form-control" name="no_akun" id="no_akun">
                                        <option value=""> -- Pilih Nomor Akun -- </option>
                                        @foreach ($nomorAkun as $item)
                                            <option value="{{$item->no}}">{{$item->no}} {{$item->nama_akun}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="tanggal_pencairan" class="form-label">Tanggal Pencairan</label>
                                    <input type="date" class="form-control" name="tanggal_pencairan" id="tanggal_pencairan">
                                </div>
                            </div>
                        @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="d-flex justify-content-end">
            @if ($tracking == 'tutup')
                <button class="btn btn-md btn-secondary mx-4" disabled title="Tidak bisa mengajukan barang karena status tidak 'Selesai'">
                    <img src="{{ asset('icon/plus.svg') }}" width="30px"> Ajukan Barang
                </button>
            @else
                <a href="pengajuanbarang/create" class="btn btn-md click-primary mx-4" data-toggle="tooltip" data-placement="top" title="Ajukan Barang">
                    <img src="{{ asset('icon/plus.svg') }}" width="30px"> Ajukan Barang
                </a>
            @endif
			@if ($jabatan == 'Finance & Accounting')
                <a href="/jurnalakuntansi" class="btn btn-md click-primary mx-4" data-toggle="tooltip" data-placement="top" title="Ajukan Barang">
                    <img src="{{ asset('icon/archive-white.svg') }}" width="30px"> Jurnal Akutansi
                </a>
			@endif
            </div>
            @php
                $jabatan = auth()->user()->jabatan;
            @endphp
            <div class="card" style="width: 100%">
                <div class="card-body d-flex flex-wrap justify-content-center align-items-end">
                    <div class="col-md-3 mx-1 mb-2">
                        <label for="tahun" class="form-label">Tahun</label>
                        <select id="tahun" class="form-select" aria-label="tahun">
                            <option disabled>Pilih Tahun</option>
                            @php
                            $tahun_sekarang = now()->year;
                            for ($tahun = 2020; $tahun <= $tahun_sekarang   + 2; $tahun++) {
                                $selected = $tahun == $tahun_sekarang ? 'selected' : '';
                                echo "<option value=\"$tahun\" $selected>$tahun</option>";
                            }
                            @endphp
                        </select>

                    </div>
                    <div class="col-md-3 mx-1 mb-2">
                        <label for="bulan" class="form-label">Bulan</label>
                        <select id="bulan" class="form-select" aria-label="bulan">
                            <option disabled>Pilih Bulan</option>
                            @php
                            $bulan_sekarang = now()->month;
                            $nama_bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                            for ($bulan = 1; $bulan <= 12; $bulan++) {
                                $bulan_awal = $nama_bulan[$bulan - 1]; // Accessing the array with $bulan - 1
                                $selected = $bulan == $bulan_sekarang ? 'selected' : '';
                                echo "<option value=\"$bulan\" $selected>$bulan_awal</option>";
                            }
                            @endphp
                        </select>
                    </div>

                    @if ($jabatan == 'Finance & Accounting')
                        <div class="col-md-3 mx-1 mb-2">
                            <label for="mode_tampilan" class="form-label">Tampilan</label>
                            <select id="mode_tampilan" class="form-select" onchange="toggleMingguSelector()">
                                <option value="">Per Bulan</option>
                                <option value="minggu">Per Minggu</option>
                                <option value="bulanminggu">Per Bulan &amp; Minggu</option>
                            </select>
                        </div>
                        <div class="col-md-3 mx-1 mb-2" id="minggu_wrapper" style="display:none;">
                            <label for="minggu_pilihan" class="form-label">Pilih Minggu</label>
                            <select id="minggu_pilihan" class="form-select"></select>
                        </div>
                    @endif

                    <div class="col-md-3 mx-1 mb-2">
                        @if ($jabatan == 'Finance & Accounting')
                            <button type="submit" onclick="tableFinance()" class="btn btn-primary" style="margin-top: 4px">Cari Data</button>
                            <button type="button" onclick="exportAllToExcel()" class="btn btn-success" style="margin-top: 4px">
                                <img src="{{ asset('icon/file-text.svg') }}" width="20px"> Export All to Excel
                            </button>
                        @else
                        <button type="submit" onclick="tableKaryawan()" class="btn click-primary" style="margin-top: 4px">Cari Data</button>
                        @endif
                    </div>
                </div>
            </div>
            @if ($jabatan == 'Finance & Accounting')
                <div class="card m-4">
                    <div class="card-body table-responsive">
                        <h3 class="card-title text-center my-1">{{ __('Data Pengajuan Barang (Hold)') }}</h3>
                        <table class="table table-striped" id="databelum">
                            <thead>
                                <tr>
                                    <th scope="col">Tanggal Pengajuan</th>
                                    <th scope="col">Nama Karyawan</th>
                                    <th scope="col">Divisi</th>
                                    <th scope="col">Jabatan</th>
                                    <th scope="col">Tipe</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Nama Barang</th>
                                    <th scope="col">Total Item</th>
                                    <th scope="col">Total Pengajuan</th>
                                    <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                            <tfoot>

                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="card m-4">
                    <div class="card-body table-responsive">
                        <h3 class="card-title text-center my-1">{{ __('Data Pengajuan Barang (Has Invoice)') }}</h3>
                        <table class="table table-striped" id="datasudahinv">
                            <thead>
                                <tr>
                                    <th scope="col">Tanggal Pengajuan</th>
                                    <th scope="col">Nama Karyawan</th>
                                    <th scope="col">Divisi</th>
                                    <th scope="col">Jabatan</th>
                                    <th scope="col">Tipe</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Nama Barang</th>
                                    <th scope="col">Total Item</th>
                                    <th scope="col">Total Pengajuan</th>
                                    <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                            <tfoot>

                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="card m-4">
                    <div class="card-body table-responsive">
                        <h3 class="card-title text-center my-1">{{ __('Data Pengajuan Barang (Selesai)') }}</h3>
                        <table class="table table-striped" id="datasudah">
                            <thead>
                                <tr>
                                    <th scope="col">Tanggal Pengajuan</th>
                                    <th scope="col">Nama Karyawan</th>
                                    <th scope="col">Divisi</th>
                                    <th scope="col">Jabatan</th>
                                    <th scope="col">Tipe</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Nama Barang</th>
                                    <th scope="col">Total Item</th>
                                    <th scope="col">Total Pengajuan</th>
                                    <th scope="col">No KK</th>
                                    <th scope="col">Tanggal Pencairan</th>
                                    <th scope="col">SLA</th>
                                    <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                            <tfoot>

                            </tfoot>
                        </table>
                    </div>
                </div>
            @else
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Data Pengajuan Barang') }}</h3>
                    <table class="table table-striped" id="barangTable" style="overflow-x: scrooll; max-width: 150%;">
                        <thead>
                            <tr>
                                <th scope="col">Tanggal Pengajuan</th>
                                <th scope="col">Tanggal Pengajuan</th>
                                <th scope="col">Nama Karyawan</th>
                                <th scope="col">Divisi</th>
                                <th scope="col">Jabatan</th>
                                <th scope="col">Tipe</th>
                                <th scope="col">Status</th>
                                <th scope="col">Nama Barang</th>
                                <th scope="col">Total Item</th>
                                <th scope="col">Total Pengajuan</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
            @endif
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
    .dataTables_processing {
    font-size: 16px;
    font-weight: bold;
    }

    .dataTables_processing:before {
        content: "Mohon tunggu...";
        font-weight: bold;
        display: block;
    }
    .dataTables_processing {
        visibility: hidden;
    }
    .dataTables_processing:before {
        visibility: visible;
    }

    }

    /* Header pemisah per minggu (DataTables RowGroup) */
    tr.dtrg-group.dtrg-start td {
        background-color: #eef2f7;
        font-weight: 700;
        color: #2c3e50;
        border-top: 2px solid #b9c4d0 !important;
        border-bottom: 1px solid #b9c4d0 !important;
    }
</style>
@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/rowgroup/1.3.1/css/rowGroup.dataTables.min.css">
<script src="https://cdn.datatables.net/rowgroup/1.3.1/js/dataTables.rowGroup.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
 $(document).ready(function(){
    var userRole = '{{ auth()->user()->jabatan}}';
    var user = '{{ auth()->user()->karyawan_id }}';

    function formatRupiah(angka) {
        let number_string = angka.toString(),
            sisa = number_string.length % 3,
            rupiah = number_string.substr(0, sisa),
            ribuan = number_string.substr(sisa).match(/\d{3}/g);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        return rupiah;
    }

    if(userRole == 'Finance &amp; Accounting'){
        tableFinance();
    }else{
        tableKaryawan();
    }

    $('#no_akun').select2({
        placeholder: 'Pilih akun',
        width: '100%',
        dropdownParent: $('#approveModal')
    });

    // Jika bulan/tahun berubah dan mode tampilan sedang "Per Minggu",
    // perbarui daftar pilihan minggu supaya sesuai bulan yang baru dipilih.
    $('#tahun, #bulan').on('change', function () {
        if ($('#mode_tampilan').length && $('#mode_tampilan').val() === 'minggu') {
            generateMingguOptions();
        }
    });

});

/* =========================================================
   HELPER: Pembagian Minggu Dalam 1 Bulan (Minggu = Minggu-Sabtu)
   Minggu 1 selalu mulai dari tanggal 1 pada bulan tsb, dan
   berakhir di hari Sabtu terdekat (atau akhir bulan jika lebih dulu).
   ========================================================= */
function getWeeksInMonth(year, month) {
    year = parseInt(year);
    month = parseInt(month); // 1-12
    var start = moment([year, month - 1, 1]);
    var end = moment([year, month - 1, 1]).endOf('month');
    var weeks = [];
    var cursor = start.clone();

    while (cursor.isSameOrBefore(end, 'day')) {
        var dow = cursor.day(); // 0 = Minggu ... 6 = Sabtu
        var daysToSaturday = 6 - dow;
        var weekEnd = cursor.clone().add(daysToSaturday, 'days');
        if (weekEnd.isAfter(end)) {
            weekEnd = end.clone();
        }
        weeks.push({ start: cursor.clone(), end: weekEnd.clone() });
        cursor = weekEnd.clone().add(1, 'days');
    }
    return weeks;
}

function getMingguIndexForDate(dateStr, weeks) {
    var d = moment(dateStr);
    for (var i = 0; i < weeks.length; i++) {
        if (d.isBetween(weeks[i].start, weeks[i].end, 'day', '[]')) {
            return i;
        }
    }
    return -1;
}

function formatMingguLabel(idx, weeks) {
    if (idx < 0 || !weeks[idx]) return '-';
    moment.locale('id');
    var w = weeks[idx];
    var startFmt = w.start.format('DD MMMM');
    var endFmt = w.end.format('DD MMMM');
    return 'Minggu ' + (idx + 1) + ' (' + startFmt + ' - ' + endFmt + ')';
}

function toggleMingguSelector() {
    var mode = $('#mode_tampilan').val();
    if (mode === 'minggu') {
        $('#minggu_wrapper').show();
        generateMingguOptions();
    } else {
        $('#minggu_wrapper').hide();
    }
}

function generateMingguOptions() {
    var tahun = $('#tahun').val();
    var bulan = $('#bulan').val();
    if (!tahun || !bulan) return;

    var weeks = getWeeksInMonth(tahun, bulan);
    var $sel = $('#minggu_pilihan');
    var previousVal = $sel.val();
    $sel.empty();

    weeks.forEach(function (w, idx) {
        $sel.append('<option value="' + idx + '">' + formatMingguLabel(idx, weeks) + '</option>');
    });

    // Default: minggu berjalan (jika bulan/tahun yang dipilih adalah bulan/tahun sekarang),
    // jika tidak maka default ke Minggu 1.
    var defaultIdx = 0;
    var now = moment();
    if (now.year() == parseInt(tahun) && (now.month() + 1) == parseInt(bulan)) {
        var currentIdx = getMingguIndexForDate(now.format('YYYY-MM-DD'), weeks);
        if (currentIdx >= 0) defaultIdx = currentIdx;
    }

    if (previousVal !== null && previousVal !== undefined && weeks[previousVal] !== undefined) {
        $sel.val(previousVal);
    } else {
        $sel.val(defaultIdx);
    }
}

function buildRowGroupConfig(weeks) {
    return {
        dataSrc: function (row) {
            var idx = getMingguIndexForDate(row.created_at, weeks);
            return formatMingguLabel(idx, weeks);
        }
    };
}
/* ========================================================= */

function tableKaryawan() {
    var tahun = $('#tahun').val();
    var bulan = $('#bulan').val();

    // Jika DataTable sudah ada → destroy dulu
    if ($.fn.DataTable.isDataTable('#barangTable')) {
        $('#barangTable').DataTable().destroy();
    }

    $('#barangTable').DataTable({
        destroy: true,
        processing: true,
        language:{
            processing: "Mohon Menungu ...."
        },
        autoWidth: false,
        ajax: {
            url: "{{ route('getPengajuanBarang', ['month' => ':month', 'year' => ':year'] ) }}"
                .replace(':month', bulan)
                .replace(':year', tahun),
            type: "GET",
            beforeSend: function () {
                $('#loadingModal').modal('show');
                $('#loadingModal').on('show.bs.modal', function () {
                    $('#loadingModal').removeAttr('inert');
                });
            },
            complete: function () {
                setTimeout(() => {
                    $('#loadingModal').modal('hide');
                    $('#loadingModal').on('hidden.bs.modal', function () {
                        $('#loadingModal').attr('inert', true);
                    });
                }, 1000);
            }
        },
        columns: [
            {
                data: "created_at",
                visible: false,
                render: function (data) {
                    return moment(data).format('YYYY-MM-DD');
                }
            },
            {
                data: "created_at",
                render: function (data) {
                    moment.locale('id');
                    return moment(data).format('dddd, DD MMMM YYYY');
                }
            },
            { data: "karyawan.nama_lengkap" },
            { data: "karyawan.divisi" },
            { data: "karyawan.jabatan", visible: false },
            { data: "tipe" },
            { data: "tracking.tracking" },
            {
                data: "detail",
                render: function (data) {
                    if (data && Array.isArray(data)) {
                        return data.map(item => item.nama_barang)
                                   .join('<hr style="margin:4px 0; border:1px solid black">');
                    }
                    return '-';
                }
            },
            {
                data: "detail",
                render: function (data) {
                    if (data && Array.isArray(data)) {
                        return data.map(item => {
                            let total = item.harga * item.qty;
                            return new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR'
                            }).format(total);
                        }).join('<hr style="margin:4px 0; border:1px solid black">');
                    }
                    return '-';
                }
            },
            {
                data: "detail",
                render: function (data) {
                    if (data && Array.isArray(data)) {
                        const total = data.reduce((sum, item) => sum + (item.harga * item.qty), 0);
                        return new Intl.NumberFormat('id-ID', {
                            style: 'currency',
                            currency: 'IDR'
                        }).format(total);
                    }
                    return '-';
                }
            },
            {
                data: null,
                render: function (data, type, row) {
                    var actions = "";
                    var allowedRoles = [
                        'Office Manager', 'Education Manager', 'SPV Sales', 'GM',
                        'Koordinator Office', 'Finance & Accounting', 'Koordinator ITSM'
                    ];
                    var userRole = '{{ auth()->user()->jabatan }}';
                    var requesterRole = data.karyawan.jabatan;
                    var userKaryawanId = {{ auth()->user()->karyawan_id }};
                    var trackingStatus = data.tracking.tracking;
                    var karyawanId = data.karyawan.id;

                    function addButton(label, url, condition, icon) {
                        if (condition) {
                            actions += `<a href="${url}" class="dropdown-item">
                                <img src="{{ asset('${icon}') }}"> ${label}</a>`;
                        } else {
                            actions += `<button type="button" class="dropdown-item disabled">
                                <img src="{{ asset('${icon}') }}"> ${label}</button>`;
                        }
                    }

                    actions += '<div class="dropdown">';
                    actions += '<button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown">Actions</button>';
                    actions += '<div class="dropdown-menu">';

                    // Approve button
                    if (allowedRoles.includes(userRole)) {
                        if (userRole == 'GM' && [
                            'Sudah Disetujui dan Sedang Ditinjau oleh General Manager',
                            'Telah Disetujui oleh SPV Sales dan Sedang Ditinjau oleh General Manager',
                            'Diajukan dan Sedang Ditinjau oleh General Manager'
                        ].includes(trackingStatus)) {
                            actions += `<button type="button" class="dropdown-item"
                                onclick="openApproveModal(${row.id}, 'Manager')">
                                <img src="{{ asset('icon/check-circle.svg') }}"> Approve</button>`;
                        } else if (userRole == 'Education Manager' &&
                            trackingStatus == 'Diajukan dan Sedang Ditinjau oleh Education Manager') {
                            actions += `<button type="button" class="dropdown-item"
                                onclick="openApproveModal(${row.id}, 'Manager')">
                                <img src="{{ asset('icon/check-circle.svg') }}"> Approve</button>`;
                        } else if (userRole == 'Koordinator ITSM' &&
                            trackingStatus == 'Diajukan dan Sedang Ditinjau oleh Koordinator IT Service Management') {
                            actions += `<button type="button" class="dropdown-item"
                                onclick="openApproveModal(${row.id}, 'Manager')">
                                <img src="{{ asset('icon/check-circle.svg') }}"> Approve</button>`;
                        } else if (userRole == 'SPV Sales' &&
                            trackingStatus == 'Diajukan dan Sedang Ditinjau oleh SPV Sales') {
                            actions += `<button type="button" class="dropdown-item"
                                onclick="openApproveModal(${row.id}, 'Manager')">
                                <img src="{{ asset('icon/check-circle.svg') }}"> Approve</button>`;
                        } else if (userRole == 'Finance & Accounting' &&
                            (trackingStatus.includes('Finance') || trackingStatus.includes('Permintaan') || trackingStatus.includes('proses'))) {
                            actions += `<button type="button" class="dropdown-item"
                                onclick="openApproveModal(${row.id}, 'Manager')">
                                <img src="{{ asset('icon/check-circle.svg') }}"> Approve</button>`;
                        } else {
                            actions += `<button type="button" class="dropdown-item disabled">
                                <img src="{{ asset('icon/check-circle.svg') }}"> Approve</button>`;
                        }
                    }

                    // Upload Invoice
                    if (trackingStatus) {
                        var uploadInvoiceUrl = "{{ url('/pengajuanbarang/uploadinvoice') }}/" + row.id;
                        addButton('Upload Invoice', uploadInvoiceUrl, userKaryawanId === karyawanId, 'icon/clipboard-primary.svg');
                    }

                    // Detail
                    var detailUrl = "{{ url('/pengajuanbarang') }}/" + row.id;
                    addButton('Detail', detailUrl, true, 'icon/clipboard-primary.svg');

                    if (!trackingStatus.includes('Finance')) {
                        actions += `<form onsubmit="return confirm('Apakah Anda Yakin ?');"
                            action="{{ url('/pengajuanbarang') }}/${row.id}" method="POST">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="dropdown-item">
                                <img src="{{ asset('icon/trash-danger.svg') }}"> Hapus</button>
                        </form>`;
                    }

                    actions += '</div></div>';
                    return actions;
                }
            }
        ],
        order: [[0, 'desc']],
        columnDefs: [{ targets: [0], type: "date" }]
    });
}

function tableFinance(){
    // Get current page for both tables before destroying
    var currentPageBelum = 0;
    if ($.fn.DataTable.isDataTable('#databelum')) {
        currentPageBelum = $('#databelum').DataTable().page();
        $('#databelum').DataTable().clear().destroy();
    }

    var currentPageSudah = 0;
    if ($.fn.DataTable.isDataTable('#datasudah')) {
        currentPageSudah = $('#datasudah').DataTable().page();
        $('#datasudah').DataTable().clear().destroy();
    }
        var currentPageInvoice = 0;
    if ($.fn.DataTable.isDataTable('#datasudahinv')) {
        currentPageInvoice = $('#datasudahinv').DataTable().page();
        $('#datasudahinv').DataTable().clear().destroy();
    }


    $('#loadingModal').modal('show');
    var tahun = $('#tahun').val();
    var bulan = $('#bulan').val();

    // Mode tampilan: '' (default / per bulan), 'minggu', atau 'bulanminggu'
    var mode = $('#mode_tampilan').length ? ($('#mode_tampilan').val() || '') : '';
    var mingguIdxSelected = $('#minggu_pilihan').length ? parseInt($('#minggu_pilihan').val()) : NaN;
    var weeks = getWeeksInMonth(tahun, bulan);

    // Fungsi untuk menerapkan filter mode 'minggu' (tidak mengubah data untuk mode lain,
    // karena filter per bulan tetap menggunakan data 1 bulan penuh dari controller yang sama)
    function applyModeFilter(dataset) {
        if (mode === 'minggu') {
            var idx = isNaN(mingguIdxSelected) ? 0 : mingguIdxSelected;
            return dataset.filter(function (item) {
                return getMingguIndexForDate(item.created_at, weeks) === idx;
            });
        }
        return dataset;
    }

    $.ajax({
        url: "{{ route('getPengajuanBarang', ['month' => ':month', 'year' => ':year'] ) }}".replace(':month', bulan).replace(':year',tahun),
        type: "GET",
        success: function(data) {
            $('#loadingModal').modal('hide');
            console.log(data.data);
            // Jika sudah ada invoice DAN bukti, otomatis dianggap Selesai
            // (pindah dari tabel Has Invoice ke tabel Selesai) walaupun status tracking-nya belum "Selesai".
            var dataSelesai = data.data.filter(item =>
                item.tracking.tracking.includes("Selesai") ||
                item.tracking.tracking.includes("tolak") ||
                (item.invoice && item.bukti)
            );

            var dataHasInvoice = data.data.filter(item =>
                item.invoice && !item.bukti &&
                !item.tracking.tracking.includes('Selesai') &&
                !item.tracking.tracking.includes("tolak")
            );
            
            var dataBelum = data.data.filter(item =>
                !item.invoice && item.tracking.tracking !== 'Selesai' && !item.tracking.tracking.includes("tolak")
            );

            // Terapkan filter tampilan (hanya berefek jika mode == 'minggu')
            dataSelesai = applyModeFilter(dataSelesai);
            dataHasInvoice = applyModeFilter(dataHasInvoice);
            dataBelum = applyModeFilter(dataBelum);

            let totalItemsSelesai = dataSelesai.length;
            let totalHargaSelesai = 0;
            dataSelesai.forEach(item => {
                if (item.detail && Array.isArray(item.detail)) {
                    item.detail.forEach(detail => {
                        totalHargaSelesai += detail.qty * detail.harga;
                    });
                }
            });

            let totalItemsBelum = dataBelum.length;
            let totalHargaBelum = 0;
            dataBelum.forEach(item => {
                if (item.detail && Array.isArray(item.detail)) {
                    item.detail.forEach(detail => {
                        totalHargaBelum += detail.qty * detail.harga;
                    });
                }
            });

            // Initialize datasudah table
            var datasudahConfig = {
                data: dataSelesai,
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: 'Export Excel',
                        className: 'btn btn-success btn-sm mb-3',
                        title: 'Data_Pengajuan_Selesai_' + bulan + '_' + tahun,
                        exportOptions: {
                            columns: ':visible:not(:last-child)'
                        }
                    }
                ],
                columns: [
                    {
                        "data": "created_at",
                        "render": function(data, type, row) {
                                moment.locale('id');
                                var tanggalAwal = moment(data).format('dddd, DD MMMM YYYY');
                                return tanggalAwal;
                            }
                    },
                    {"data": "karyawan.nama_lengkap"},
                    {"data": "karyawan.divisi"},
                    {"data": "karyawan.jabatan", "visible": false},
                    {"data": "tipe"},
                    {"data": "tracking.tracking"},
                    {
                        "data": "detail",
                        "render": function (data, type, row) {
                            if (data && Array.isArray(data)) {
                                return data.map(item => item.nama_barang).join('<hr style="margin: 4px 0; border: 1px solid black">');
                            }
                            return '-';
                        }
                    },

                    {
                        "data": "detail",
                        "render": function (data, type, row) {
                            if (data && Array.isArray(data)) {
                                return data.map(item => {
                                    let total = item.harga * item.qty;
                                    return new Intl.NumberFormat('id-ID', {
                                        style: 'currency',
                                        currency: 'IDR'
                                    }).format(total);
                                }).join('<hr style="margin: 4px 0; border: 1px solid black">');
                            }
                            return '-';
                        }
                    },

                    {
                        "data": "detail",
                        "render": function (data) {
                            if (data && Array.isArray(data)) {
                                const total = data.reduce((sum, item) => sum + (item.harga * item.qty), 0);
                                return new Intl.NumberFormat('id-ID', {
                                    style: 'currency',
                                    currency: 'IDR'
                                }).format(total);
                            }
                            return '-';
                        }
                    },
                    {
                        "data": "no_kk",
                        "render": function(data) { return data ? data : '-'; }
                    },
                    // KOLOM BARU: TANGGAL PENCAIRAN
                    {
                        "data": "tanggal_pencairan",
                        "render": function(data) {
                            return data ? moment(data).format('DD-MM-YYYY') : '-';
                        }
                    },
                    // KOLOM BARU: SLA (LOGIKA HITUNG 7 HARI)
                    {
                        "data": null,
                        "render": function(data, type, row) {
                            if (!row.no_kk) {
                                return '-';
                            }

                            if (!row.tanggal_pencairan) {
                                return '-';
                            }

                            var tglCair = moment(row.tanggal_pencairan);
                            var batasAwal = moment('2026-06-01');

                            if (tglCair.isBefore(batasAwal)) {
                                return '-';
                            }

                            if (!row.tanggal_terima_finance) {
                                return '<span class="badge bg-secondary">Data Tidak Lengkap</span>';
                            }

                            let tglFinance = moment(row.tanggal_terima_finance);
                            let selisihHari = tglCair.diff(tglFinance, 'days');

                            if (selisihHari <= 7) {
                                return `<span class="badge bg-success">Berhasil (${selisihHari} Hari)</span>`;
                            } else {
                                return `<span class="badge bg-danger">Gagal (${selisihHari} Hari)</span>`;
                            }
                        }
                    },
                    {
                        "data": null,
                        "render": function(data, type, row) {
                            var actions = "";
                            var userRole = '{{ auth()->user()->jabatan }}';
                            var userKaryawanId = {{ auth()->user()->karyawan_id }};
                            var trackingStatus = (row.tracking && row.tracking.tracking) ? row.tracking.tracking : '';
                            var karyawanId = row.karyawan ? row.karyawan.id : null;

                            actions += '<div class="dropdown">';
                            actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                            actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';

                            // Approve button
                            if (userRole == 'Finance &amp; Accounting' && (trackingStatus.includes('Finance') || trackingStatus.includes('Permintaan') || trackingStatus.includes('proses') || trackingStatus.includes('Selesai'))) {
                                actions += '<button type="button" class="dropdown-item" onclick="openApproveModal(' + row.id + ', \'Manager\')"><img src="{{ asset('icon/check-circle.svg') }}" class=""> Approve</button>';
                            } else {
                                actions += '<button type="button" class="dropdown-item disabled"><img src="{{ asset('icon/check-circle.svg') }}" class=""> Approve</button>';
                            }

                            actions += '<a class="dropdown-item" disabled href="{{ url('/pengajuanbarang') }}/' + row.id + '" data-toggle="tooltip" data-placement="top" title="Detail User"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Detail</a>';
                            actions += '<a class="dropdown-item" disabled href="{{ url('/pengajuanbarang/uploadinvoice') }}/' + row.id + '" data-toggle="tooltip" data-placement="top" title="Detail User"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Upload Invoice</a>';
                            actions += '</div>'
                            actions += '</div>'
                            return actions;
                        },
                    },
                ],
                "order": [[0, 'desc']],
                "columnDefs" : [{"targets":[0], "type":"date"}],
                "drawCallback": function(settings) {
                    var api = this.api();
                    var formattedHarga = new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR'
                    }).format(totalHargaSelesai);
                    var footerHtml = `
                        <tr>
                            <th colspan="6" style="text-align: left;">
                                Total Pengajuan: ${totalItemsSelesai}
                            </th>
                            <th colspan="4" style="text-align: left;">
                                Total Harga Pengajuan: ${formattedHarga}
                            </th>
                        </tr>
                    `;
                    $('#datasudah tfoot').html(footerHtml);
                }
            };
            if (mode === 'bulanminggu') {
                datasudahConfig.rowGroup = buildRowGroupConfig(weeks);
            }
            var datasudahTable = $('#datasudah').DataTable(datasudahConfig);

            // Set page for datasudah table
            if (currentPageSudah > 0) {
                datasudahTable.page(currentPageSudah).draw('page');
            }

            // Initialize databelum table
            var databelumConfig = {
                data: dataBelum,
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: 'Export Excel',
                        className: 'btn btn-success btn-sm mb-3',
                        title: 'Data_Pengajuan_Hold_' + bulan + '_' + tahun,
                        exportOptions: {
                            columns: ':visible:not(:last-child)'
                        }
                    }
                ],
                "columns": [
                    {
                        "data": "created_at",
                        "render": function(data, type, row) {
                                moment.locale('id');
                                var tanggalAwal = moment(data).format('dddd, DD MMMM YYYY');
                                return tanggalAwal;
                            }
                    },
                    {"data": "karyawan.nama_lengkap"},
                    {"data": "karyawan.divisi"},
                    {"data": "karyawan.jabatan", "visible": false},
                    {"data": "tipe"},
                    {"data": "tracking.tracking"},
                    {
                        "data": "detail",
                        "render": function (data, type, row) {
                            if (data && Array.isArray(data)) {
                                return data.map(item => item.nama_barang).join('<hr style="margin: 4px 0; border: 1px solid black">');
                            }
                            return '-';
                        }
                    },

                    {
                        "data": "detail",
                        "render": function (data, type, row) {
                            if (data && Array.isArray(data)) {
                                return data.map(item => {
                                    let total = item.harga * item.qty;
                                    return new Intl.NumberFormat('id-ID', {
                                        style: 'currency',
                                        currency: 'IDR'
                                    }).format(total);
                                }).join('<hr style="margin: 4px 0; border: 1px solid black">');
                            }
                            return '-';
                        }
                    },

                    {
                        "data": "detail",
                        "render": function (data) {
                            if (data && Array.isArray(data)) {
                                const total = data.reduce((sum, item) => sum + (item.harga * item.qty), 0);
                                return new Intl.NumberFormat('id-ID', {
                                    style: 'currency',
                                    currency: 'IDR'
                                }).format(total);
                            }
                            return '-';
                        }
                    },
                    {
                        "data": null,
                        "render": function(data, type, row) {
                            var actions = "";
                            var allowedRoles = ['Office Manager', 'Education Manager', 'SPV Sales', 'GM', 'Koordinator Office', 'Finance & Accounting', 'Koordinator ITSM'];
                            var userRole = '{{ auth()->user()->jabatan}}';
                            var requesterRole = data.karyawan.jabatan;
                            var userKaryawanId = {{ auth()->user()->karyawan_id }};
                            var trackingStatus = data.tracking.tracking;
                            var karyawanId = data.karyawan.id;

                            function addButton(label, url, condition, icon) {
                                if (condition) {
                                    actions += `<a href="${url}" class="dropdown-item"><img src="{{ asset('${icon}') }}" class=""> ${label}</a>`;
                                } else {
                                    actions += `<button type="button" class="dropdown-item disabled"><img src="{{ asset('${icon}') }}" class=""> ${label}</button>`;
                                }
                            }

                            actions += '<div class="dropdown">';
                            actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                            actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';

                            if (userRole == 'Finance &amp; Accounting' && (trackingStatus.includes('Finance') || trackingStatus.includes('Permintaan') || trackingStatus.includes('proses') || trackingStatus.includes('Selesai'))) {
                                    actions += '<button type="button" class="dropdown-item" onclick="openApproveModal(' + row.id + ', \'Manager\')"><img src="{{ asset('icon/check-circle.svg') }}" class=""> Approve</button>';
                                } else {
                                    actions += '<button type="button" class="dropdown-item disabled"><img src="{{ asset('icon/check-circle.svg') }}" class=""> Approve</button>';
                            }

                            if (trackingStatus) {
                                var uploadInvoiceUrl = "{{ url('/pengajuanbarang/uploadinvoice') }}/" + row.id;
                                var uploadInvoiceIcon = 'icon/clipboard-primary.svg';
                                var uploadInvoiceLabel = 'Upload Invoice';
                                var uploadInvoiceCondition = userKaryawanId === karyawanId || userRole == 'Finance &amp; Accounting';

                                addButton(uploadInvoiceLabel, uploadInvoiceUrl, uploadInvoiceCondition, uploadInvoiceIcon);
                            }

                            var detailUrl = "{{ url('/pengajuanbarang') }}/" + row.id;
                            var detailIcon = 'icon/clipboard-primary.svg';
                            var detailLabel = 'Detail';
                            var detailCondition = true;

                            addButton(detailLabel, detailUrl, detailCondition, detailIcon);
                            actions += '<form onsubmit="return confirm(\'Apakah Anda Yakin ?\');" action="{{ url('/pengajuanbarang') }}/' + row.id + '" method="POST">';
                            actions += '@csrf';
                            actions += '@method('DELETE')';
                            actions += '<button type="submit" class="dropdown-item"><img src="{{ asset('icon/trash-danger.svg') }}" class=""> Hapus</button>';
                            actions += '</form>';
                            actions += '</div>';
                            actions += '</div>';

                            return actions;
                        }
                    }
                ],
                "order": [[0, 'desc']],
                "columnDefs" : [{"targets":[0], "type":"date"}],
                "drawCallback": function(settings) {
                    var api = this.api();
                    var formattedHargaBelum = new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR'
                    }).format(totalHargaBelum);
                    var footerHtml = `
                        <tr>
                            <th colspan="6" style="text-align: left;">
                                Total Pengajuan: ${totalItemsBelum}
                            </th>
                            <th colspan="4" style="text-align: left;">
                                Total Harga Pengajuan: ${formattedHargaBelum}
                            </th>
                        </tr>
                    `;
                    $('#databelum tfoot').html(footerHtml);
                }
            };
            if (mode === 'bulanminggu') {
                databelumConfig.rowGroup = buildRowGroupConfig(weeks);
            }
            var databelumTable = $('#databelum').DataTable(databelumConfig);

            // ================= HAS INVOICE
            var datasudahinvConfig = {
                data: dataHasInvoice,
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: 'Export Excel',
                        className: 'btn btn-success btn-sm mb-3',
                        title: 'Data_Pengajuan_HasInvoice_' + bulan + '_' + tahun,
                        exportOptions: {
                            columns: ':visible:not(:last-child)'
                        }
                    }
                ],
                columns: [
                    {
                        "data": "created_at",
                        "render": function(data) {
                            moment.locale('id');
                            return moment(data).format('dddd, DD MMMM YYYY');
                        }
                    },
                    {"data": "karyawan.nama_lengkap"},
                    {"data": "karyawan.divisi"},
                    {"data": "karyawan.jabatan"},
                    {"data": "tipe"},
                    {"data": "tracking.tracking"},
                    {
                        "data": "detail",
                        "render": function (data) {
                            if (data && Array.isArray(data)) {
                                return data.map(item => item.nama_barang).join('<hr>');
                            }
                            return '-';
                        }
                    },
                    {
                        "data": "detail",
                        "render": function (data) {
                            if (data && Array.isArray(data)) {
                                return data.map(item => item.qty).join('<hr>');
                            }
                            return '-';
                        }
                    },
                    {
                        "data": "detail",
                        "render": function (data) {
                            if (data && Array.isArray(data)) {
                                const total = data.reduce((sum, item) => sum + (item.harga * item.qty), 0);
                                return new Intl.NumberFormat('id-ID', {
                                    style: 'currency',
                                    currency: 'IDR'
                                }).format(total);
                            }
                            return '-';
                        }
                    },
                    {
                        "data": null,
                        "render": function(data, type, row) {
                            let userRole = '{{ auth()->user()->jabatan }}';
                            
                            // Gunakan row atau data (keduanya sama jika data: null)
                            // Tambahkan pengecekan agar tidak error jika tracking kosong
                            let trackingStatus = (row.tracking && row.tracking.tracking) ? row.tracking.tracking : '';

                            let actions = `
                                <div class="dropdown">
                                    <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown">Actions</button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="/pengajuanbarang/${row.id}">
                                            <img src="{{ asset('icon/clipboard-primary.svg') }}"> Detail
                                        </a>

                                        ${row.invoice ? `
                                        <a class="dropdown-item" href="/storage/${row.invoice}" target="_blank">
                                            <img src="{{ asset('icon/file-text.svg') }}"> Lihat Invoice
                                        </a>
                                        <a class="dropdown-item" href="/storage/${row.bukti}" target="_blank">
                                            <img src="{{ asset('icon/file-text.svg') }}"> Lihat Bukti
                                        </a>
                                        ` : ''}

                                        ${(userRole == 'Finance &amp; Accounting' && (trackingStatus.includes('Finance') || trackingStatus.includes('Permintaan') || trackingStatus.includes('proses') || trackingStatus.includes('Selesai'))) 
                                            ? `<button type="button" class="dropdown-item" onclick="openApproveModal(${row.id}, 'Manager')">
                                                    <img src="{{ asset('icon/check-circle.svg') }}"> Approve
                                            </button>` 
                                            : `<button type="button" class="dropdown-item disabled">
                                                    <img src="{{ asset('icon/check-circle.svg') }}"> Approve
                                            </button>`
                                        }

                                        <hr class="dropdown-divider">

                                        <form onsubmit="return confirm('Apakah Anda Yakin ?');" action="{{ url('/pengajuanbarang') }}/${row.id}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <img src="{{ asset('icon/trash-danger.svg') }}"> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            `;

                            return actions;
            
                        }

                    }
                ],
                order: [[0, 'desc']]
            };
            if (mode === 'bulanminggu') {
                datasudahinvConfig.rowGroup = buildRowGroupConfig(weeks);
            }
            var datasudahinvTable = $('#datasudahinv').DataTable(datasudahinvConfig);

            // Set page for databelum table
            if (currentPageBelum > 0) {
                databelumTable.page(currentPageBelum).draw('page');
            }
        },
    });
}

function exportAllToExcel() {
    const tables = [
        { id: '#databelum', title: 'DATA PENGAJUAN BARANG (HOLD)' },
        { id: '#datasudahinv', title: 'DATA PENGAJUAN BARANG (HAS INVOICE)' },
        { id: '#datasudah', title: 'DATA PENGAJUAN BARANG (SELESAI)' }
    ];

    let allData = [];
    let bulan = $('#bulan option:selected').text();
    let tahun = $('#tahun').val();

    // Judul Utama File
    allData.push([`LAPORAN REKAP PENGAJUAN BARANG - ${bulan} ${tahun}`]);
    allData.push([]); // Baris Kosong

    tables.forEach(table => {
        if ($.fn.DataTable.isDataTable(table.id)) {
            const dt = $(table.id).DataTable();
            const data = dt.rows({ search: 'applied' }).data().toArray();

            // 1. Tambahkan Judul Tabel sebagai Pemisah
            allData.push([table.title]);

            // 2. Tambahkan Header Kolom
            allData.push([
                "Tanggal Pengajuan",
                "Nama Karyawan",
                "Divisi",
                "Tipe",
                "Status",
                "Nama Barang",
                "Total Item",
                "Total Pengajuan"
            ]);

            // 3. Tambahkan Data
            if (data.length > 0) {
                data.forEach(row => {
                    let namaBarang = row.detail.map(d => d.nama_barang).join('; ');
                    let totalQty = row.detail.reduce((sum, d) => sum + parseInt(d.qty), 0);
                    let totalHarga = row.detail.reduce((sum, d) => sum + (d.harga * d.qty), 0);

                    allData.push([
                        moment(row.created_at).format('DD-MM-YYYY'),
                        row.karyawan.nama_lengkap,
                        row.karyawan.divisi,
                        row.tipe,
                        row.tracking.tracking,
                        namaBarang,
                        totalQty,
                        totalHarga
                    ]);
                });
            } else {
                allData.push(["Tidak ada data"]);
            }

            // 4. Tambahkan Baris Kosong untuk Jeda Antar Tabel
            allData.push([]);
            allData.push([]);
        }
    });

    // Proses Konversi ke CSV (agar kompatibel langsung dengan Excel)
    let csvContent = "data:text/csv;charset=utf-8,\uFEFF"; // \uFEFF untuk support karakter spesial/simbol
    allData.forEach(rowArray => {
        // Membersihkan data dari koma agar tidak merusak struktur CSV
        let row = rowArray.map(val => {
            if (typeof val === 'string') return `"${val.replace(/"/g, '""')}"`;
            return val;
        }).join(",");
        csvContent += row + "\r\n";
    });

    // Eksekusi Download
    var encodedUri = encodeURI(csvContent);
    var link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `Rekap_Semua_Tabel_${bulan}_${tahun}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function openApproveModal(id, jabatan) {
    var approveUrl = "{{ url('/pengajuanbarang') }}/" + id;
    $('#approveForm').attr('action', approveUrl);
    $('#approveModal').modal('show');

    if ($('#no_kk').length) {
        var lastNoKk = localStorage.getItem('last_no_kk');
        $('#no_kk').val(lastNoKk || 'KK-');
    }

    if ($('#no_akun').length) {
        var lastNoAkun = localStorage.getItem('last_no_akun');
        if (lastNoAkun) {
            $('#no_akun').val(lastNoAkun).trigger('change'); 
        }
    }

    if ($('#tanggal_pencairan').length) {
        var lastTanggalPencairan = localStorage.getItem('last_tanggal_pencairan');
        if (lastTanggalPencairan) {
            $('#tanggal_pencairan').val(lastTanggalPencairan);
        }
    }

    const status = document.getElementById('status');
    if (status) {
        toggleFinanceInputs(status.value);
    }
}

function toggleAlasanManager(show) {
    const alasanManagerInput = document.getElementById('alasanManagerInput');
    if (show) {
        alasanManagerInput.style.display = 'block';
    } else {
        alasanManagerInput.style.display = 'none';
        document.getElementById('alasan_manager').value = '';
    }
}

function toggleFinanceInputs(status) {
        const extraInputs = document.getElementById('finance_extra_inputs');
        if (status !== 'Selesai') {
            extraInputs.style.display = 'block';
        } else {
            extraInputs.style.display = 'none';
        }
    }

$('#approveForm').on('submit', function(e) {
        e.preventDefault();
        let form = $(this);
        let actionUrl = form.attr('action');

        var noKkVal = $('#no_kk').val();
        var noAkunVal = $('#no_akun').val();
        var tanggalPencairanVal = $('#tanggal_pencairan').val();

        if (noKkVal) localStorage.setItem('last_no_kk', noKkVal);
        if (noAkunVal) localStorage.setItem('last_no_akun', noAkunVal);
        if (tanggalPencairanVal) localStorage.setItem('last_tanggal_pencairan', tanggalPencairanVal);

        $('#loadingModal').modal('show');

        $.ajax({
            url: actionUrl,
            type: 'POST',
            data: form.serialize(),
            success: function(res) {
                $('#loadingModal').modal('hide');
                $('#approveModal').modal('hide');
                $('#approveForm')[0].reset();

                // Cek apakah response berupa JSON (dari manager) atau HTML (redirect Finance)
                if (typeof res === 'object' && res !== null && res.success !== undefined) {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message || 'Data berhasil diperbarui.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: res.message || 'Terjadi kesalahan.'
                        });
                    }
                } else {
                    // Response HTML (redirect dari Finance) — anggap sukses
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Data berhasil diperbarui!',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }

                // Reload tabel
                if ($.fn.DataTable.isDataTable('#barangTable')) {
                    $('#barangTable').DataTable().ajax.reload(null, false);
                }
                if ('{{ auth()->user()->jabatan }}' == 'Finance & Accounting') {
                    tableFinance();
                } else {
                    tableKaryawan();
                }
            },
            error: function(err) {
                $('#loadingModal').modal('hide');
                let msg = 'Gagal menyimpan data, silakan coba lagi.';
                if (err.responseJSON && err.responseJSON.message) {
                    msg = err.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: msg
                });
            }
        });
    });
</script>
@endpush
@endsection
