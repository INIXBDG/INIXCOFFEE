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
                            <div class="row my-2">
                                <select name="status" id="status" class="form-select">
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
            
            </div>
            @php
                $jabatan = auth()->user()->jabatan;
            @endphp
            @if ($jabatan == 'Finance & Accounting')
            <div class="card" style="width: 100%">
                <div class="card-body d-flex justify-content-center">
                    <div class="col-md-4 mx-1">
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
                    <div class="col-md-4 mx-1">
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

                    <div class="col-md-4 mx-1">
                        <button type="submit" onclick="tableFinance()" class="btn click-primary" style="margin-top: 37px">Cari Data</button>
                    </div>
                </div>
            </div>
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
                    </table>
                </div>
            </div>
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Data Pengajuan Barang (Selesai)') }}</h3>
                    <table class="table table-striped" id="datasudah">
                        <thead>
                            <>
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
    }
</style>
@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
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
    });
        function tableKaryawan(){
            var tahun = $('#tahun').val();

            $('#barangTable').DataTable({
            autoWidth: false,
            "ajax": {
                url: "{{ route('getPengajuanBarang', ['month' => ':month', 'year' => ':year'] ) }}".replace(':month', 'All').replace(':year', tahun), // Ganti dengan URL yang sesuai
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
                {
                    "data": "created_at",
                    "visible": false,
                    "render": function(data, type, row) {
                            // moment.locale('id');
                            var tanggalAwal = moment(data).format('YYYY-MM-DD');
                            return tanggalAwal;
                        }
                },
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

                        // Fungsi untuk menambahkan tombol dengan kondisi
                        function addButton(label, url, condition, icon) {
                            if (condition) {
                                actions += `<a href="${url}" class="dropdown-item"><img src="{{ asset('${icon}') }}" class=""> ${label}</a>`;
                            } else {
                                actions += `<button type="button" class="dropdown-item disabled"><img src="{{ asset('${icon}') }}" class=""> ${label}</button>`;
                            }
                        }

                        // Tambahkan dropdown actions
                        actions += '<div class="dropdown">';
                        actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                        actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';

                        // Tambahkan tombol Approve jika peran pengguna diizinkan
                        if (allowedRoles.includes(userRole)) {
                            if (userRole == 'GM' && ['Sudah Disetujui dan Sedang Ditinjau oleh General Manager', 'Telah Disetujui oleh SPV Sales dan Sedang Ditinjau oleh General Manager', 'Diajukan dan Sedang Ditinjau oleh General Manager'].includes(trackingStatus)) {
                                actions += '<button type="button" class="dropdown-item" onclick="openApproveModal(' + row.id + ', \'Manager\')"><img src="{{ asset('icon/check-circle.svg') }}" class=""> Approve</button>';
                            } else if (userRole == 'Education Manager' && trackingStatus == 'Diajukan dan Sedang Ditinjau oleh Education Manager') {
                                actions += '<button type="button" class="dropdown-item" onclick="openApproveModal(' + row.id + ', \'Manager\')"><img src="{{ asset('icon/check-circle.svg') }}" class=""> Approve</button>';
                            } else if (userRole == 'Koordinator ITSM' && trackingStatus == 'Diajukan dan Sedang Ditinjau oleh Koordinator IT Service Management') {
                                actions += '<button type="button" class="dropdown-item" onclick="openApproveModal(' + row.id + ', \'Manager\')"><img src="{{ asset('icon/check-circle.svg') }}" class=""> Approve</button>';
                            } else if (userRole == 'SPV Sales' && trackingStatus == 'Diajukan dan Sedang Ditinjau oleh SPV Sales') {
                                actions += '<button type="button" class="dropdown-item" onclick="openApproveModal(' + row.id + ', \'Manager\')"><img src="{{ asset('icon/check-circle.svg') }}" class=""> Approve</button>';
                            } else if (userRole == 'Finance & Accounting' && (trackingStatus.includes('Finance') || trackingStatus.includes('Permintaan') || trackingStatus.includes('proses'))) {
                                actions += '<button type="button" class="dropdown-item" onclick="openApproveModal(' + row.id + ', \'Manager\')"><img src="{{ asset('icon/check-circle.svg') }}" class=""> Approve</button>';
                            } else {
                                actions += '<button type="button" class="dropdown-item disabled"><img src="{{ asset('icon/check-circle.svg') }}" class=""> Approve</button>';
                            }
                        }

                        // Tambahkan tombol Upload Invoice dengan kondisi
                        if (trackingStatus) {
                            var uploadInvoiceUrl = "{{ url('/pengajuanbarang/uploadinvoice') }}/" + row.id;
                            var uploadInvoiceIcon = 'icon/clipboard-primary.svg';
                            var uploadInvoiceLabel = 'Upload Invoice';
                            var uploadInvoiceCondition = userKaryawanId === karyawanId;

                            addButton(uploadInvoiceLabel, uploadInvoiceUrl, uploadInvoiceCondition, uploadInvoiceIcon);
                        }

                        // Tambahkan tombol Detail
                        var detailUrl = "{{ url('/pengajuanbarang') }}/" + row.id;
                        var detailIcon = 'icon/clipboard-primary.svg';
                        var detailLabel = 'Detail';
                        var detailCondition = true; // Selalu enabled

                        addButton(detailLabel, detailUrl, detailCondition, detailIcon);
                        if(!trackingStatus.includes('Finance')){
                            actions += '<form onsubmit="return confirm(\'Apakah Anda Yakin ?\');" action="{{ url('/pengajuanbarang') }}/' + row.id + '" method="POST">';
                            actions += '@csrf';
                            actions += '@method('DELETE')';
                            actions += '<button type="submit" class="dropdown-item"><img src="{{ asset('icon/trash-danger.svg') }}" class=""> Hapus</button>';
                            actions += '</form>';
                            actions += '</div>';
                            actions += '</div>';
                        }
                        actions += '</div>';
                        actions += '</div>';

                        return actions;
                    }
                }
            ],
            "order": [[0, 'desc']], // Ubah urutan menjadi descending untuk kolom ke-6
            "columnDefs" : [{"targets":[0], "type":"date"}],
        });
        }
        function tableFinance(){
            if ($.fn.DataTable.isDataTable('#datasudah')) {
                $('#datasudah').DataTable().clear().destroy(); // Hancurkan DataTable yang ada
            }
            if ($.fn.DataTable.isDataTable('#databelum')) {
                $('#databelum').DataTable().clear().destroy(); // Hancurkan DataTable yang ada
            }
            $('#loadingModal').modal('show');
            var tahun = $('#tahun').val();
            var bulan = $('#bulan').val();
            $.ajax({
                url: "{{ route('getPengajuanBarang', ['month' => ':month', 'year' => ':year'] ) }}".replace(':month', bulan).replace(':year',tahun), // Ganti dengan URL yang sesuai
                type: "GET",
                success: function(data) {
                    // Pisahkan data
                    $('#loadingModal').modal('hide');
                    console.log(data.data);
                    var dataSelesai = data.data.filter(item => item.tracking.tracking === 'Selesai' || item.tracking.tracking.includes("tolak"));
                    var dataBelum = data.data.filter(item => item.tracking.tracking !== 'Selesai' && !item.tracking.tracking.includes("tolak"));

                    // Inisialisasi DataTable untuk data yang sudah selesai
                    $('#datasudah').DataTable({
                        data: dataSelesai,
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
                                "data": null,
                                "render": function(data, type, row) {
                                    var actions = "";
                                    actions += '<div class="dropdown">';
                                    actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                                    actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                                    actions += '<a class="dropdown-item" disabled href="{{ url('/pengajuanbarang') }}/' + row.id + '" data-toggle="tooltip" data-placement="top" title="Detail User"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Detail</a>';
                                    actions += '<a class="dropdown-item" disabled href="{{ url('/pengajuanbarang/uploadinvoice') }}/' + row.id + '" data-toggle="tooltip" data-placement="top" title="Detail User"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Upload Invoice</a>';
                                    actions += '</div>'
                                    actions += '</div>'
                                    return actions;
                                },
                            },
                        ],
                        "order": [[0, 'desc']], // Ubah urutan menjadi descending untuk kolom ke-6
                        "columnDefs" : [{"targets":[0], "type":"date"}],

                    });

                    // Inisialisasi DataTable untuk data yang belum selesai
                    $('#databelum').DataTable({
                        data: dataBelum,
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

                                    // Fungsi untuk menambahkan tombol dengan kondisi
                                    function addButton(label, url, condition, icon) {
                                        if (condition) {
                                            actions += `<a href="${url}" class="dropdown-item"><img src="{{ asset('${icon}') }}" class=""> ${label}</a>`;
                                        } else {
                                            actions += `<button type="button" class="dropdown-item disabled"><img src="{{ asset('${icon}') }}" class=""> ${label}</button>`;
                                        }
                                    }

                                    // Tambahkan dropdown actions
                                    actions += '<div class="dropdown">';
                                    actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                                    actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';

                                    // Tambahkan tombol Approve jika peran pengguna diizinkan
                                    if (userRole == 'Finance &amp; Accounting' && (trackingStatus.includes('Finance') || trackingStatus.includes('Permintaan') || trackingStatus.includes('proses') || trackingStatus.includes('Selesai'))) {
                                            actions += '<button type="button" class="dropdown-item" onclick="openApproveModal(' + row.id + ', \'Manager\')"><img src="{{ asset('icon/check-circle.svg') }}" class=""> Approve</button>';
                                        } else {
                                            actions += '<button type="button" class="dropdown-item disabled"><img src="{{ asset('icon/check-circle.svg') }}" class=""> Approve</button>';
                                    }

                                    // Tambahkan tombol Upload Invoice dengan kondisi
                                    if (trackingStatus) {
                                        var uploadInvoiceUrl = "{{ url('/pengajuanbarang/uploadinvoice') }}/" + row.id;
                                        var uploadInvoiceIcon = 'icon/clipboard-primary.svg';
                                        var uploadInvoiceLabel = 'Upload Invoice';
                                        var uploadInvoiceCondition = userKaryawanId === karyawanId || userRole == 'Finance &amp; Accounting';

                                        addButton(uploadInvoiceLabel, uploadInvoiceUrl, uploadInvoiceCondition, uploadInvoiceIcon);
                                    }

                                    // Tambahkan tombol Detail
                                    var detailUrl = "{{ url('/pengajuanbarang') }}/" + row.id;
                                    var detailIcon = 'icon/clipboard-primary.svg';
                                    var detailLabel = 'Detail';
                                    var detailCondition = true; // Selalu enabled

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
                        "order": [[0, 'desc']], // Ubah urutan menjadi descending untuk kolom ke-6
                        "columnDefs" : [{"targets":[0], "type":"date"}],
                    });
                },
                error: function(xhr) {
                    console.error("Error fetching data:", xhr);
                }
            });
        }
        function openApproveModal(id, jabatan) {
            // Set the action URL for the approval form
            var approveUrl = "{{ url('/pengajuanbarang') }}/" + id;
            $('#approveForm').attr('action', approveUrl);
            $('#approveModal').modal('show');
        }

        function toggleAlasanManager(show) {
            if (show) {
                document.getElementById('alasanManagerInput').style.display = 'block';
            } else {
                document.getElementById('alasanManagerInput').style.display = 'none';
                document.getElementById('alasan_manager').value = ''; // Clear the input if hidden
            }
        }
        
</script>
@endpush
@endsection
