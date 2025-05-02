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
    <div class="modal fade" id="hitungLemburKaryawan" tabindex="-1" aria-labelledby="hitungLemburKaryawanLabel" aria-hidden="true">
        <div class="modal-dialog" style="max-width: 90% !important">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="hitungLemburKaryawanLabel">Hitung Lembur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formHitungLembur" method="POST">
                        @csrf
                        <div id="xontainer"></div>
                        
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="approveLemburKaryawan" tabindex="-1" aria-labelledby="approveLemburKaryawanLabel" aria-hidden="true">
        <div class="modal-dialog" style="max-width: 90% !important">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="hitungLemburKaryawanLabel">Approve Hitung Lembur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formApprovalLembur" method="POST">
                        @csrf
                        @method('PUT')
                        <div id="xontainer-approve"></div>
                        
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="DetailLembur" tabindex="-1" aria-labelledby="DetailLemburLabel" aria-hidden="true">
        <div class="modal-dialog" style="max-width: 90% !important">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="hitungLemburKaryawanLabel">Approve Hitung Lembur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formApprovalLembur" method="POST">
                        @csrf
                        @method('PUT')
                        <div id="xontainer-detail"></div>
                        
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
                {{-- @can('Create Jabatan')
                    <a href="{{ route('jabatan.create') }}" class="btn btn-md click-primary mx-4"><img src="{{ asset('icon/plus.svg') }}" class="" width="30px"> Data Jabatan</a>
                @endcan --}}
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
                                <button type="button" onclick="getDataLembur()" class="btn click-primary" style="margin-top: 37px">Cari Data</button>
                                <a href="{{ route('overtime.exportExcel', [$tahun_sekarang, $bulan_sekarang]) }}" id="export-link" target="_blank" class="btn click-primary" style="margin-top: 37px">Export to Excel</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>  
            <div class="row my-2">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body table-responsive">
                            <h4 class="card-title mt-3 text-center">Hitung Lembur</h4>
                            <table id="hitunglembur" class="display" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Nama Karyawan</th>
                                        <th>Divisi</th>
                                        <th>Jabatan</th>
                                        <th>Jumlah Perintah Lembur</th>
                                        <th>Aksi</th>
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
<script>
    $(document).ready(function(){
        var year = $('#tahun').val();
        var month = $('#bulan').val();
        // console.log(year, month);
        fetchTable();
        $('#formHitungLembur').on('submit', function(e) {
        e.preventDefault(); // Mencegah form submit biasa

        var form = $(this);
        var url = form.attr('action'); // URL dari atribut action form
        var formData = form.serialize(); // Mengambil semua data form

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function(response) {
                // Tindakan jika berhasil, misal:
                alert('Data lembur berhasil disimpan!');
                $('#hitungLemburKaryawan').modal('hide');
                // Bisa juga panggil ulang fetchTable() untuk refresh data tabel
                fetchTable();
            },
            error: function(xhr, status, error) {
                // Tindakan jika error, misal:
                alert('Terjadi kesalahan saat menyimpan data: ' + error);
            }
        });
    });

    });
    function getDataLembur() {
        var year = $('#tahun').val();
        var month = $('#bulan').val();

        if (year && month) {
            $('#loadingModal').modal('show'); // Tampilkan modal sebelum memulai pemanggilan Ajax

            $('#hitunglembur').DataTable().ajax.url("{{ url('/getOvertimeLembur') }}/" + month + "/" + year).load(function(json) {
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
        function fetchTable(){
            var ApproveHitungLembur = {{ auth()->user()->can('Approve HitungLembur') ? 'true' : 'false' }};
            var JumlahHitungLembur = {{ auth()->user()->can('Jumlah HitungLembur') ? 'true' : 'false' }};
            console.log('ApproveHitungLembur:', ApproveHitungLembur);
            console.log('JumlahHitungLembur:', JumlahHitungLembur);

                var userRole = '{{ auth()->user()->jabatan}}';
                var tahun = $('#tahun').val();
                var bulan = $('#bulan').val();
                console.log(tahun, bulan);
                $('#hitunglembur').DataTable({
                    "ajax": {
                        "url": "/getOvertimeLembur/" + bulan + "/" + tahun,
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
                        // {"data": "id"},
                        {"data": "karyawan.nama_lengkap"},
                        {"data": "karyawan.divisi"},
                        {"data": "karyawan.jabatan"},
                        {"data": "total_lembur"},
                        {
                            "data": null,
                            "render": function(data, type, row) {
                                    var actions = "";
                                        actions += '<div class="dropdown">';
                                        actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                                        actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                                        if(JumlahHitungLembur){
                                            if(data.id_hitung_lembur == null){
                                                actions += '<button type="button" class="dropdown-item" onclick="openhitungLemburKaryawan(' + data.id_karyawan + ')"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Hitung</button>';
                                            }else{
                                                actions += '<button type="button" class="dropdown-item" onclick="openhitungLemburKaryawan(' + data.id_karyawan + ')"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Hitung</button>';
                                                actions += '<button type="button" class="dropdown-item" onclick="openDetailLemburKaryawan(' + data.id_karyawan + ', '+bulan+', '+tahun+')"><img src="{{ asset('icon/detail.svg') }}" class=""> Detail</button>';
                                                actions += '<a class="dropdown-item" href="/export-lembur-pdf/'+data.id_karyawan+'/'+tahun+'/'+bulan+'"><img src="{{ asset('icon/assept-document.svg') }}" style="width:24px" class=""> Form PDF</a>';
                                            }
                                        }
                                        if(ApproveHitungLembur){
                                            if(data.id_hitung_lembur == null){
                                                actions += '<button type="button" class="dropdown-item" disabled><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Approve</button>';
                                            }else{
                                                actions += '<button type="button" class="dropdown-item" onclick="openApproveLemburKaryawan(' + data.id_karyawan + ')"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Approve</button>';
                                            }
                                        }
                                        actions += '</div>';
                                        actions += '</div>';
                                    return actions;
                            }
                        }
                    ]

                });
        }

        function openApproveLemburKaryawan(id) {
            // Show the modal
            var approveUrl = "{{ url('/overtimeApproving') }}";
            $('#formApprovalLembur').attr('action', approveUrl);
            $('#approveLemburKaryawan').modal('show');
            var totalNilaiLembur = 0;
            var year = $('#tahun').val();
            var month = $('#bulan').val();
            console.log(id, month, year);
            // AJAX call to fetch overtime data
            $.ajax({
                url: "getOvertimeLemburByKaryawan/" + id + "/" + month + "/" + year,
                type: "GET",
                success: function (data) {
                    // Clear the container
                    $("#xontainer-approve").empty();

                    // Check if data is available
                    if (data.success && data.data.length > 0) {
                        // Create a table element
                        var table = $('<table class="table table-bordered table-striped">');
                        var thead = $('<thead>');
                        var tbody = $('<tbody>');
                        var totalJamLembur = 0;
                        var totalNilaiLembur = 0;

                        // Define table headers
                        thead.append(`
                            <tr>
                                <th rowspan='2'>No</th>
                                <th rowspan='2'>Tanggal</th>
                                <th rowspan='2'>Hari Biasa dan Libur</th>
                                <th rowspan='2'>Keperluan</th>
                                <th colspan='2'>Waktu Lembur</th>
                                <th rowspan='2'>Jumlah Jam Lembur</th>
                                <th rowspan='2'>Nilai Lembur per Jam</th>
                                <th rowspan='2'>Total Nilai Lembur</th>
                                <th rowspan='2'>Approve</th>
                                <th rowspan='2'>Alasan</th>
                            </tr>
                            <tr>
                                <th>Jam Mulai</th>
                                <th>Jam Selesai</th>
                            </tr>
                        `);
                        table.append(thead);

                        // Populate table rows with data
                        data.data.forEach(function (item, index) {
                            console.log(item.hitunglembur.nilai_lembur)
                            var jamLembur = calculateTotalHours(item.jam_mulai, item.jam_selesai);
                            totalJamLembur += parseFloat(jamLembur); // Accumulate total hours
                            var kalkulasi = item.hitunglembur.nilai_lembur * jamLembur;
                            totalNilaiLembur += kalkulasi; // Accumulate total
                            console.log(totalNilaiLembur)
                            var inputisCheckedYes = item.hitunglembur.approval_gm == '1' ? 'readonly' : '';
                            var isCheckedYes = item.hitunglembur.approval_gm == '1' ? 'checked' : '';
                            var isCheckedNo = (item.hitunglembur.approval_gm == '2' || item.hitunglembur.approval_gm == null) ? 'checked' : '';
                            console.log(item.hitunglembur.approval_gm, isCheckedYes, isCheckedNo);
                            // Determine if the row should be disabled
                            var isDisabled = isCheckedYes ? 'readonly' : '';
                            tbody.append(`
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${moment(item.tanggal_lembur).format('DD/MM/YYYY')}</td>
                                    <td>Hari ${item.waktu_lembur}</td>
                                    <td>${item.uraian_tugas}</td>
                                    <td>${item.jam_mulai || '-'}</td>
                                    <td>${item.jam_selesai || '-'}</td>
                                    <td>${jamLembur} Jam</td>
                                    <td><input type='hidden' name='id_lembur[${index}]' value='${item.id}'><input class='hitungtable form-control' readonly value='${item.hitunglembur.nilai_lembur}' name='nilai_lembur[${index}]' type='text'></td>
                                    <td class="">${kalkulasi.toFixed(2)}</td>
                                    <td>
                                        <div class="btn-group" role="group" aria-label="Approval Options">
                                            <input type="radio" class="btn-check" name="approval[${index}]" id="approveYes_${index}" value="1" ${isCheckedYes} ${isDisabled}>
                                            <label class="btn btn-outline-primary" for="approveYes_${index}">Ya</label>

                                            <input type="radio" class="btn-check" name="approval[${index}]" id="approveNo_${index}" value="2" ${isCheckedNo} ${isDisabled}>
                                            <label class="btn btn-outline-danger" for="approveNo_${index}" >Tidak</label>
                                        </div>
                                    </td>
                                    <td><input class='form-control' value='' name='alasan[${index}]' ${inputisCheckedYes} type='text'></td>
                                </tr>
                            `);
                            
                        });

                        table.append(tbody);

                        // Add tfoot for totals
                        var tfoot = $('<tfoot>');
                        tfoot.append(`
                            <tr>
                                <td colspan="6" class='text-right'><strong>Total Jam Lembur</strong></td>
                                <td>${totalJamLembur.toFixed(2)} Jam</td>
                                <td><strong>Total Claim</strong></td>
                                <td class="">${totalNilaiLembur.toFixed(2)}</td>
                            </tr>
                        `);
                        table.append(tfoot);

                        $("#xontainer-approve").append(table);
                    } else {
                        // Handle case where no data is returned
                        $("#xontainer-approve").append('<p class="text-center">Tidak ada data lembur untuk karyawan ini.</p>');
                    }
                },
                error: function (xhr, status, error) {
                    // Handle errors
                    console.error("Error fetching data: ", error);
                    $("#xontainer-approve").append('<p class="text-center text-danger">Terjadi kesalahan saat mengambil data lembur.</p>');
                }
            });
        }

        function openhitungLemburKaryawan(id, month, year) {
            // Show the modal
            var approveUrl = "{{ url('/overtime') }}/";
            $('#formHitungLembur').attr('action', approveUrl);
            $('#hitungLemburKaryawan').modal('show');
            var year = $('#tahun').val();
            var month = $('#bulan').val();
            console.log(id, month, year);
            // AJAX call to fetch overtime data
            $.ajax({
                url: "getOvertimeLemburByKaryawan/" + id + "/" + month + "/" + year,
                type: "GET",
                success: function (data) {
                    // Clear the container
                    $("#xontainer").empty();

                    // Check if data is available
                    if (data.success && data.data.length > 0) {
                        // Create a table element
                        var table = $('<table class="table table-bordered table-striped">');
                        var thead = $('<thead>');
                        var tbody = $('<tbody>');
                        var totalJamLembur = 0;
                        var totalNilaiLembur = 0;

                        // Define table headers
                        thead.append(`
                            <tr>
                                <th rowspan='2'>No</th>
                                <th rowspan='2'>Tanggal</th>
                                <th rowspan='2'>Hari Biasa dan Libur</th>
                                <th rowspan='2'>Keperluan</th>
                                <th colspan='2'>Waktu Lembur</th>
                                <th rowspan='2'>Jumlah Jam Lembur</th>
                                <th rowspan='2'>Nilai Lembur per Jam</th>
                                <th rowspan='2'>Total Nilai Lembur</th>
                                <th rowspan='2'>Approval GM</th>
                            </tr>
                            <tr>
                                <th>Jam Mulai</th>
                                <th>Jam Selesai</th>
                            </tr>
                        `);
                        table.append(thead);

                        // Populate table rows with data
                        data.data.forEach(function (item, index) {
                            // Use optional chaining to safely access properties
                            var nilaiLembur = item.hitunglembur?.nilai_lembur ?? 0; // Default to 0 if null
                            var approvalGm = item.hitunglembur?.approval_gm ?? '0'; // Default to '0' if null
                            var isCheckedYes = approvalGm === '1' ? 'readonly' : ''; // Set readonly if approved

                            console.log(nilaiLembur);
                            var jamLembur = calculateTotalHours(item.jam_mulai, item.jam_selesai);
                            totalJamLembur += parseFloat(jamLembur); // Accumulate total hours
                            var kalkulasi = nilaiLembur * jamLembur;
                            totalNilaiLembur += kalkulasi; // Accumulate total
                            console.log(totalNilaiLembur);

                            // Determine approval status
                            var approve;
                            if (approvalGm === '1') {
                                approve = 'Ya';
                            } else if (approvalGm === '2') {
                                approve = 'Tidak';
                            } else {
                                approve = 'Belum';
                            }

                            // Determine if the row should be disabled
                            tbody.append(`
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${moment(item.tanggal_lembur).format('DD/MM/YYYY')}</td>
                                    <td>Hari ${item.waktu_lembur}</td>
                                    <td>${item.uraian_tugas}</td>
                                    <td>${item.jam_mulai || '-'}</td>
                                    <td>${item.jam_selesai || '-'}</td>
                                    <td class="jam-lembur">${jamLembur} Jam</td> <!-- Add this class -->
                                    <td>
                                        <input type='hidden' name='id_lembur[${index}]' value='${item.id}'>
                                        <input class='hitungtable form-control' id='hitung_${index}' ${isCheckedYes} value='${nilaiLembur}' name='nilai_lembur[${index}]' type='text' oninput="calculateTotalNilai(this)">
                                    </td>
                                    <td class="total-nilai">${kalkulasi.toFixed(2)}</td>
                                    <td>${approve}</td>
                                </tr>

                            `);
                        });

                        table.append(tbody);

                        // Add tfoot for totals
                        var tfoot = $('<tfoot>');
                        tfoot.append(`
                            <tr>
                                <td colspan="6" class='text-right'><strong>Total Jam Lembur</strong></td>
                                <td>${totalJamLembur.toFixed(2)} Jam</td>
                                <td><strong>Total Claim</strong></td>
                                <td colspan="2" class="total-nilai-lembur">0</td>
                            </tr>
                        `);
                        table.append(tfoot);

                        $("#xontainer").append(table);
                    } else {
                        // Handle case where no data is returned
                        $("#xontainer").append('<p class="text-center">Tidak ada data lembur untuk karyawan ini.</p>');
                    }
                },
                error: function (xhr, status, error) {
                    // Handle errors
                    console.error("Error fetching data: ", error);
                    $("#xontainer").append('<p class="text-center text-danger">Terjadi kesalahan saat mengambil data lembur.</p>');
                }
            });
        }

        function openDetailLemburKaryawan(id, month, year) {
            // Show the modal
            // var approveUrl = "{{ url('/overtime') }}/";
            // $('#formHitungLembur').attr('action', approveUrl);
            $('#DetailLembur').modal('show');

            // AJAX call to fetch overtime data
            $.ajax({
                url: "getOvertimeLemburByKaryawan/" + id + "/" + month + "/" + year,
                type: "GET",
                success: function (data) {
                    // Clear the container
                    $("#xontainer-detail").empty();

                    // Check if data is available
                    if (data.success && data.data.length > 0) {
                        // Create a table element
                        var table = $('<table class="table table-bordered table-striped">');
                        var thead = $('<thead>');
                        var tbody = $('<tbody>');
                        var totalJamLembur = 0;
                        var totalNilaiLembur = 0;

                        // Define table headers
                        thead.append(`
                            <tr>
                                <th rowspan='2'>No</th>
                                <th rowspan='2'>Tanggal</th>
                                <th rowspan='2'>Hari Biasa dan Libur</th>
                                <th rowspan='2'>Keperluan</th>
                                <th colspan='2'>Waktu Lembur</th>
                                <th rowspan='2'>Jumlah Jam Lembur</th>
                                <th rowspan='2'>Nilai Lembur per Jam</th>
                                <th rowspan='2'>Total Nilai Lembur</th>
                                <th rowspan='2'>Approve</th>
                            </tr>
                            <tr>
                                <th>Jam Mulai</th>
                                <th>Jam Selesai</th>
                            </tr>
                        `);
                        table.append(thead);

                        // Populate table rows with data
                        data.data.forEach(function (item, index) {
                            var jamLembur = calculateTotalHours(item.jam_mulai, item.jam_selesai);
                            totalJamLembur += parseFloat(jamLembur); // Accumulate total hours
                            var kalkulasi = item.hitunglembur.nilai_lembur * jamLembur;
                            totalNilaiLembur += kalkulasi; // Accumulate total
                            if(item.hitunglembur.approval_gm == '1'){
                                var approve = 'Ya'
                            }else if(item.hitunglembur.approval_gm == '2'){
                                var approve = 'Tidak'
                            }else{
                                var approve = 'Belum'
                            }
                            // Determine if the row should be disabled
                            tbody.append(`
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${moment(item.tanggal_lembur).format('DD/MM/YYYY')}</td>
                                    <td>Hari ${item.waktu_lembur}</td>
                                    <td>${item.uraian_tugas}</td>
                                    <td>${item.jam_mulai || '-'}</td>
                                    <td>${item.jam_selesai || '-'}</td>
                                    <td>${jamLembur} Jam</td>
                                    <td><input type='hidden' name='id_lembur[${index}]' value='${item.id}'><input class='hitungtable form-control' readonly value='${item.hitunglembur.nilai_lembur}' name='nilai_lembur[${index}]' type='text'></td>
                                    <td class="total-nilai">${kalkulasi.toFixed(2)}</td>
                                    <td>${approve}</td>
                                </tr>
                            `);
                            
                        });

                        table.append(tbody);

                        // Add tfoot for totals
                        var tfoot = $('<tfoot>');
                        tfoot.append(`
                            <tr>
                                <td colspan="6" class='text-right'><strong>Total Jam Lembur</strong></td>
                                <td>${totalJamLembur.toFixed(2)} Jam</td>
                                <td><strong>Total Claim</strong></td>
                                <td colspan="2">${totalNilaiLembur.toFixed(2)}</td>
                            </tr>
                        `);
                        table.append(tfoot);

                        $("#xontainer-detail").append(table);
                    } else {
                        // Handle case where no data is returned
                        $("#xontainer-detail").append('<p class="text-center">Tidak ada data lembur untuk karyawan ini.</p>');
                    }
                },
                error: function (xhr, status, error) {
                    // Handle errors
                    console.error("Error fetching data: ", error);
                    $("#xontainer").append('<p class="text-center text-danger">Terjadi kesalahan saat mengambil data lembur.</p>');
                }
            });
        }

        // Function to calculate total hours between two times
        function calculateTotalHours(jamMulai, jamSelesai) {
            if (jamMulai && jamSelesai) {
                var start = moment(jamMulai, "HH:mm");
                var end = moment(jamSelesai, "HH:mm");
                var duration = moment.duration(end.diff(start));
                return duration.asHours().toFixed(2); // Return total hours in decimal format
            }
            return '0.00'; // Return 0 if no start or end time
        }

        // Function to calculate total nilai lembur
        function calculateTotalNilai(input) {
            // Get the value of nilai_per_jam directly from the input field
            var nilaiPerJam = parseFloat($(input).val()) || 0; // Default to 0 if NaN

            // Get the corresponding jam lembur from the same row
            var jamLembur = parseFloat($(input).closest('tr').find('.jam-lembur').text()) || 0; // Assuming you have a class for jam lembur

            // Calculate the total nilai
            var totalNilai = (jamLembur * nilaiPerJam).toFixed(2);

            // Update the total nilai display in the same row
            $(input).closest('tr').find('.total-nilai').text(totalNilai);

            // Update total nilai lembur in tfoot (if you have a specific element for total)
            updateTotalNilaiLembur();
        }

        // Function to update total nilai lembur in tfoot
        function updateTotalNilaiLembur() {
            var totalNilaiLembur = 0;
            $('.total-nilai').each(function() {
                var nilai = parseFloat($(this).text());
                if (!isNaN(nilai)) {
                    totalNilaiLembur += nilai;
                }
            });
            $('.total-nilai-lembur').text(totalNilaiLembur.toFixed(2));
        }


</script>
@endpush
@endsection
