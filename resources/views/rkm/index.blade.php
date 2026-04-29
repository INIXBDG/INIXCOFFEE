@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
@php
    $jabatanUser = auth()->user()->jabatan ?? '';
    $bisaEditChecklist = in_array($jabatanUser, ['Customer Care', 'Admin Holding']);
@endphp
<select id="source-options-materi" style="display: none;" multiple="multiple">
    @foreach ($dataMateri as $m)
        <option value="{{ $m->id }}">{{ $m->nama_materi }}</option>
    @endforeach
</select>
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
    <div class="modal fade" id="modalRekomendasiLanjutan" tabindex="-1" role="dialog" aria-labelledby="modalRekomendasiLanjutanLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="formStoreRekomendasi">
                    @csrf
                    <input type="hidden" name="id_rekomendasi" id="id_rekomendasi">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalRekomendasiLanjutanLabel">Ajukan Rekomendasi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="container">
                            <div class="alert alert-light border mb-3">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <small class="text-muted fw-bold">Materi Sebelumnya</small>
                                        <p class="mb-0" id="Materi_View"></p>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted fw-bold">Tanggal Training</small>
                                        <p class="mb-0" id="Tanggal_View"></p>
                                    </div>
                                </div>
                            </div>

                            <div id="form-dynamic-container"></div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalChecklist" tabindex="-1" aria-labelledby="modalChecklistLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalChecklistLabel">Checklist Keperluan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="d-flex justify-content-between mb-2">
                        <button id="prevDay" class="btn btn-sm btn-secondary">←</button>
                        <span id="labelHari"></span>
                        <button id="nextDay" class="btn btn-sm btn-secondary">→</button>
                    </div>

                    <div class="progress mb-3" style="height: 20px;">
                        <div id="checklistProgress" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                    </div>

                    <form id="formChecklistKeperluan">
                        <input type="hidden" id="inputHiddenIdRkm" name="id_rkm">
                        <input type="hidden" name="tanggal" id="tanggal">
                        <input type="hidden" name="metode_kelas" id="metode_kelas">

                        <div id="checklistContainer">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>

                    @if($bisaEditChecklist)
                        {{-- <button type="button" class="btn btn-primary" id="btnSimpanChecklist">Simpan</button> --}}
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-12 d-flex my-2 justify-content-end">
            
        </div>
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
                                $bulan_akhir = $nama_bulan[$bulan % 12];
                                $selected = $bulan == $bulan_sekarang ? 'selected' : '';
                                echo "<option value=\"$bulan\" $selected>$bulan_awal - $bulan_akhir</option>";
                            }
                            @endphp
                        </select>
                    </div>
                    <div class="col-md-4 mx-1">
                        <button type="submit" onclick="getDataRKM()" class="btn click-primary" style="margin-top: 30px; height: 37px;">Cari Data</button>
                         @if (Auth()->user()->jabatan === "Adm Sales")
                        <button type="submit" onclick="excelDownloadAdmSales()" class="btn btn-success" style="margin-top: 30px">Download excel</button>
                        @elseif (Auth()->user()->jabatan === "Technical Support")
                            <button type="submit" onclick="excelDownload()" class="btn btn-success" style="margin-top: 30px">Download excel</button>
                        @endif
                    </div>
                </div>
            </div>
            <div class="row my-2">
                <div class="col-md-12" id="content">
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    #content{
        overflow-y:hidden;
    }
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
.dropdown-submenu {
    position: relative;
}
.dropdown-submenu .dropdown-menu {
    top: 100%; /* Muncul di bawah */
    left: 0; /* Align dengan induk */
    margin-top: 1px; /* Jarak kecil dari induk */
    display: none;
}
.dropdown-submenu:hover > .dropdown-menu {
    display: block;
}

/* Opsional: Kalau mau di pinggir kiri */
.dropdown-submenu.left .dropdown-menu {
    top: 100%; /* Tetap di bawah */
    right: 0; /* Muncul ke kiri */
    left: auto; /* Override left */
}
</style>

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
<script>
    $(document).ready(function(){
        getDataRKM();
    });

function excelDownloadAdmSales() {
        var tahun = document.getElementById('tahun').value;
        var bulan = document.getElementById('bulan').value;

        var form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('excel.rkmAdmSales') }}";
        form.style.display = 'none';

        var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = token;
        form.appendChild(csrf);

        var inputTahun = document.createElement('input');
        inputTahun.type = 'hidden';
        inputTahun.name = 'tahun';
        inputTahun.value = tahun;
        form.appendChild(inputTahun);

        var inputBulan = document.createElement('input');
        inputBulan.type = 'hidden';
        inputBulan.name = 'bulan';
        inputBulan.value = bulan;
        form.appendChild(inputBulan);

        document.body.appendChild(form);
        form.submit();

        setTimeout(() => {
            document.body.removeChild(form);
        }, 1000);

}

// function getDataRKM() {
//     var tahun = document.getElementById('tahun').value;
//     var bulan = document.getElementById('bulan').value;

//         var form = document.createElement('form');
//         form.method = 'POST';
//         form.action = "{{ route('excel') }}";
//         form.style.display = 'none';

//         var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
//         var csrf = document.createElement('input');
//         csrf.type = 'hidden';
//         csrf.name = '_token';
//         csrf.value = token;
//         form.appendChild(csrf);

//         var inputTahun = document.createElement('input');
//         inputTahun.type = 'hidden';
//         inputTahun.name = 'tahun';
//         inputTahun.value = tahun;
//         form.appendChild(inputTahun);

//         var inputBulan = document.createElement('input');
//         inputBulan.type = 'hidden';
//         inputBulan.name = 'bulan';
//         inputBulan.value = bulan;
//         form.appendChild(inputBulan);

//         document.body.appendChild(form);
//         form.submit();

//         setTimeout(() => {
//             document.body.removeChild(form);
//         }, 1000);
//     }

    function getDataRKM() {
        var tahun = document.getElementById('tahun').value;
        var bulan = document.getElementById('bulan').value;

        $('#loadingModal').modal('show');

        $.ajax({
            url: "api/rkmAPI/" + tahun + "/" + bulan,
            method: 'GET',
            dataType: 'json',
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
            },
            success: function(response) {
                var html = '';
                var count = 1;
                var jabatan = `{!! auth()->user()->jabatan !!}`.replace(/&amp;/g, "&").trim();
                var userKode = "{{ auth()->user()->karyawan->kode_karyawan ?? '' }}";
                response.data.forEach(function(monthData) {
                    monthData.weeksData.forEach(function(weekData) {
                        var bulanKosong = moment(weekData.start).format('M');
                        html += '<div class="card my-1">';
                        html += '<div class="card-body table-responsive">';
                        html += '<h3 class="card-title my-1">Rencana Kelas Mingguan</h3>';
                        moment.locale('id');
                        var startOfWeek = moment(weekData.start);
                        var endOfWeek = startOfWeek.clone().add(4, 'days');
                        html += '<p class="card-title my-1">Periode : ' + moment(startOfWeek).format('DD MMMM YYYY') + ' - ' + moment(endOfWeek).format('DD MMMM YYYY') + '</p>';
                        html += '<table class="table table-responsive table-striped">';
                        html += '<thead>';
                        html += '<tr>';
                        html += '<th scope="col">No</th>';
                        html += '<th scope="col">Materi</th>';
                        html += '<th scope="col">Tanggal Training</th>';
                        html += '<th scope="col">Perusahaan</th>';
                        html += '<th scope="col">Kode Sales</th>';
                        html += '<th scope="col">Instruktur</th>';
                        html += '<th scope="col">Exam</th>';
                        html += '<th scope="col">Metode Kelas</th>';
                        html += '<th scope="col">Event</th>';
                        html += '<th scope="col">Ruang</th>';
                        html += '<th scope="col">Pax</th>';
                        if (jabatan == 'Customer Care') {
                            html += '<th scope="col">Makanan</th>';
                        }
                        if (
                            jabatan == 'SPV Sales' || jabatan == 'GM' || jabatan == 'Sales' ||
                            jabatan == 'Adm Sales' || jabatan == 'Education Manager' || jabatan == 'Instruktur' ||
                            jabatan == 'Direktur' || jabatan == 'Office Manager' || jabatan == 'Customer Care' ||
                            jabatan == 'Tim Digital' || jabatan == 'Admin Holding' || jabatan == 'Technical Support' ||
                            jabatan === 'Direktur Utama' || jabatan === 'Direktur' || jabatan === 'HRD' ||
                            jabatan === 'Koordinator Office' || jabatan === 'Koordinator ITSM' ||
                            jabatan == 'Finance & Accounting'
                        ) {
                            html += '<th scope="col">Aksi</th>';
                        }
                        html += '</tr>';
                        html += '</thead>';
                        html += '<tbody>';
                        if (weekData.data.length === 0) {
                            html += '<tr>';
                            html += '<td colspan="12" class="text-center">Tidak Ada Kelas Mingguan</td>';
                            html += '</tr>';
                        } else {
                            weekData.data.forEach(function(rkm, index) {
                                const idList = rkm.id_all ? String(rkm.id_all).split(',').map(i => i.trim()) : [];
                                var tanggal = moment(rkm.tanggal_awal).format('D');
                                var lanbu = moment(rkm.tanggal_awal).format('M');
                                var hunta = moment(rkm.tanggal_awal).format('Y');
                                var kelas = (rkm.metode_kelas == 'Offline') ? 'off' :
                                            (rkm.metode_kelas == 'Inhouse Bandung') ? 'inhb' :
                                            (rkm.metode_kelas == 'Inhouse Luar Bandung') ? 'inhlb' :
                                            (rkm.metode_kelas == 'Exam Only') ? 'exam' : 'vir';
                                if (rkm.status_all == '0') {
                                    html += '<tr style="background-color: rgba(255, 0, 0, 0.5); color: #fff">';
                                } else if (rkm.status_all == '1') {
                                    html += '<tr style="background-color: rgba(0, 0, 255, 0.5); color: #fff">';
                                } else if (rkm.status_all == '3') {
                                    html += '<tr style="background-color: rgba(0, 190, 0, 0.5); color: #fff">';
                                } else {
                                    html += '<tr style="background-color: rgba(0, 0, 0, 0.5); color: #fff">';
                                }
                                html += '<td>' + (index + 1) + '</td>';
                                html += '<td>' + rkm.materi.nama_materi + '</td>';
                                if (rkm.tanggal_awal == rkm.tanggal_akhir) {
                                    html += '<td>' + moment(rkm.tanggal_awal).format('DD MMMM YYYY') + '</td>';
                                } else {
                                    html += '<td>' + moment(rkm.tanggal_awal).format('DD MMMM YYYY') + ' s/d ' + moment(rkm.tanggal_akhir).format('DD MMMM YYYY') + '</td>';
                                }
                                html += '<td>';
                                rkm.perusahaan.forEach(function(perusahaan) {
                                    html += perusahaan.nama_perusahaan + ', ';
                                });
                                html += '</td>';
                                html += '<td>';
                                rkm.sales.forEach(function(sales) {
                                    html += sales.kode_karyawan + ', ';
                                });
                                html += '</td>';
                                html += '<td>';
                                if (rkm.instruktur_all && rkm.instruktur_all.trim() !== '') {
                                    var instruktur_array = rkm.instruktur_all.split(', ');
                                    html += instruktur_array[0];
                                } else {
                                    html += 'Belum Ditentukan';
                                }
                                html += '</td>';
                                html += '<td>';
                                const examArray = rkm.exam ? String(rkm.exam).split(',').map(item => item.trim()) : [];
                                const exam = examArray.length > 0 ? Number(examArray[0]) : 0;
                                if (exam == 0 || exam == '0') {
                                    html += 'Tidak';
                                } else {
                                    html += 'Ya';
                                }
                                html += '</td>';
                                html += '<td>' + rkm.metode_kelas + '</td>';
                                if (rkm.event == null || rkm.event == '-') {
                                    html += '<td>Belum Ditentukan</td>';
                                } else {
                                    html += '<td>' + rkm.event + '</td>';
                                }
                                if (rkm.ruang == null || rkm.ruang == '-') {
                                    html += '<td>Belum Ditentukan</td>';
                                } else {
                                    html += '<td>' + rkm.ruang + '</td>';
                                }
                                html += '<td>' + rkm.total_pax + '</td>';
                                if (jabatan == 'Customer Care') {
                                    html += '<td>';
                                    // Ambil nilai makanan pertama dari daftar GROUP_CONCAT
                                    const makananList = rkm.makanan ? String(rkm.makanan).split(', ') : [];
                                    const makananValue = makananList.length > 0 ? makananList[0] : 'Tidak Ada';
                                    if (makananValue === '0' || makananValue === 'Tidak Ada') {
                                        html += 'Tidak Ada';
                                    } else if (makananValue === '1' || makananValue === 'Nasi Box') {
                                        html += 'Nasi Box';
                                    } else if (makananValue === '2' || makananValue === 'Prasmanan') {
                                        html += 'Prasmanan';
                                    } else {
                                        html += 'Belum Ditentukan';
                                    }
                                    html += '</td>';
                                }
                                if (
                                    jabatan == 'SPV Sales' || jabatan == 'GM' || jabatan == 'Sales' ||
                                    jabatan == 'Adm Sales' || jabatan == 'Education Manager' || jabatan == 'Instruktur' ||
                                    jabatan == 'Office Manager' || jabatan == 'Customer Care' || jabatan == 'Tim Digital' ||
                                    jabatan == 'Admin Holding' || jabatan == 'Technical Support' ||
                                    jabatan === 'Direktur Utama' || jabatan === 'Direktur' || jabatan === 'HRD' ||
                                    jabatan === 'Koordinator Office' || jabatan === 'Koordinator ITSM' ||
                                    jabatan == 'Finance & Accounting'
                                ) {
                                    html += '<td>';
                                    html += '<div class="btn-group dropup">';
                                    html += '<button type="button" class="btn dropdown-toggle text-white" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                                    html += 'Actions';
                                    html += '</button>';

                                    // Perbaikan Scroll: Penambahan max-height dan overflow-y
                                    html += '<div class="dropdown-menu" style="max-height: 250px; overflow-y: auto;">';

                                    // 1. Menu Detail RKM
                                    const firstId = rkm.id_all ? String(rkm.id_all).split(', ')[0] : '';
                                    html += '<a class="dropdown-item" href="/rkm/' + rkm.materi_key + 'ixb' + tanggal + 'ie' + hunta + 'ie' + lanbu + 'ixb' + kelas + '" data-toggle="tooltip" data-placement="top" title="Detail RKM">';
                                    html += '<img src="{{ asset("icon/clipboard-primary.svg") }}" class="me-1"> Detail RKM</a>';

                                    // 2. Menu Ajukan Rekomendasi
                                    var instrukturRKM = (rkm.instruktur_all || "").toUpperCase();
                                    var myKode = userKode.toUpperCase();
                                    var showButton = (jabatan === 'Instruktur' && instrukturRKM.includes(myKode) || jabatan === 'Education Manager' && instrukturRKM.includes(myKode));
                                    const idList = rkm.id_all ? String(rkm.id_all).split(',').map(i => i.trim()) : [];
                                    const perusahaanList = rkm.perusahaan.map(p => p.nama_perusahaan);

                                    let existingRecs = rkm.rekomendasi_group || [];
                                    let rawRecs = rkm.rekomendasilanjutan;

                                    if (Array.isArray(rawRecs)) {
                                        existingRecs = rawRecs;
                                    } else if (rawRecs) {
                                        existingRecs = [rawRecs];
                                    }

                                    const jsonIds = JSON.stringify(idList).replace(/"/g, '&quot;');
                                    const jsonPT = JSON.stringify(perusahaanList).replace(/"/g, '&quot;');
                                    const jsonRecs = JSON.stringify(existingRecs).replace(/"/g, '&quot;');

                                    if (showButton) {
                                        html += '<a class="dropdown-item js-ajukan-rekomendasi" href="#" ' +
                                            'data-materi="' + rkm.materi.nama_materi + '" ' +
                                            'data-tanggal="' + moment(rkm.tanggal_awal).format('DD MMMM YYYY') + '" ' +
                                            'data-ids=\'' + jsonIds + '\' ' +
                                            'data-perusahaan=\'' + jsonPT + '\' ' +
                                            'data-recs=\'' + jsonRecs + '\' ' +
                                            'data-bs-toggle="modal" data-bs-target="#modalRekomendasiLanjutan">' +
                                            '<img src="{{ asset("icon/clipboard-primary.svg") }}" class="me-1"> Ajukan Rekomendasi</a>';
                                    }

                                    // 3. Menu Checklist Keperluan (Akses Terbatas) - Dipindahkan ke atas
                                    if (jabatan === 'HRD' || jabatan === 'GM' || jabatan === 'Customer Care' || jabatan === 'Admin Holding') {
                                        html += '<div class="dropdown-divider"></div>';
                                        html += '<a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalChecklist" data-id="' + rkm.id_all + '">';
                                        html += '<img src="{{ asset("icon/clipboard-primary.svg") }}" class="me-1"> Checklist Keperluan</a>';
                                    }

                                    // 4. Submenu Makanan - Dipindahkan ke bawah
                                    if (jabatan == 'Customer Care') {
                                        html += '<div class="dropdown-divider"></div>';
                                        html += '<div class="dropdown-item dropdown-submenu left">';
                                        html += '<a class="dropdown-toggle" style="color: inherit; text-decoration: none;" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Makanan</a>';
                                        html += '<div class="dropdown-menu">';

                                        const makananList = rkm.makanan ? String(rkm.makanan).split(', ') : [];
                                        const makananValue = makananList.length > 0 ? makananList[0] : 'Tidak Ada';

                                        html += '<a class="dropdown-item js-update-makanan" href="#" data-ids=\'' + JSON.stringify(rkm.id || []) + '\' data-val="0">Tidak Ada</a>';
                                        html += '<a class="dropdown-item js-update-makanan" href="#" data-ids=\'' + JSON.stringify([rkm.id] || []) + '\' data-val="1">' + (makananValue === '1' || makananValue === 'Nasi Box' ? '✔ ' : '') + 'Nasi Box</a>';
                                        html += '<a class="dropdown-item js-update-makanan" href="#" data-ids=\'' + JSON.stringify([rkm.id] || []) + '\' data-val="2">' + (makananValue === '2' || makananValue === 'Prasmanan' ? '✔ ' : '') + 'Prasmanan</a>';

                                        html += '</div>';
                                        html += '</div>';
                                    }

                                    html += '</div>';
                                    html += '</div>';
                                    html += '</td>';
                                };
                                html += '</tr>';
                            });
                        }
                        html += '</tbody>';
                        html += '</table>';
                        html += '</div>';
                        html += '</div>';

                    });
                });
                setTimeout(() => {
                    $('#loadingModal').modal('hide');
                    $('#content').html(html);
                }, 1000);
            },
            error: function(xhr) {
                console.error("Error fetching data:", xhr);
                alert("Gagal memuat data. Silakan coba lagi.");
                $('#loadingModal').modal('hide');
            }
        });
    }

    $(document).on('click', '.js-update-makanan', function(e) {
        e.preventDefault();

        let ids = [];
        try {
            ids = JSON.parse($(this).attr('data-ids')); // ambil langsung attribute, bukan .data()
        } catch (err) {
            console.error("Gagal parse data-ids:", err);
        }

        const val = $(this).data('val');

        console.log('IDs Array:', ids);
        console.log('Selected Makanan Value:', val);

        if (!Array.isArray(ids) || ids.length === 0) {
            alert('Tidak ada ID yang valid untuk diperbarui');
            return;
        }

        if (!confirm('Ubah pilihan makanan untuk semua ID terkait?')) return;

        $.ajax({
            url: '/rkm/update-makanan',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                ids: ids,
                makanan: val
            },
            success: function(res) {
                alert(res.message || 'Makanan berhasil diperbarui!');
                getDataRKM();
            },
            error: function(xhr) {
                console.error('Error updating makanan:', xhr);
                alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal update makanan');
            }
        });
    });

    $(document).on('click', '.js-ajukan-rekomendasi', function(e) {
        e.preventDefault();

        // 1. Ambil Data Header
        $('#Materi_View').text($(this).data('materi'));
        $('#Tanggal_View').text($(this).data('tanggal'));

        // 2. Ambil & Parse Data Array
        let ids = $(this).data('ids');
        let pts = $(this).data('perusahaan');
        let recs = $(this).data('recs');

        // Parsing JSON aman
        if (typeof ids === 'string') ids = JSON.parse(ids);
        if (typeof pts === 'string') pts = JSON.parse(pts);
        if (typeof recs === 'string') recs = JSON.parse(recs);
        // Pastikan recs adalah array (handle jika null/undefined)
        if (!Array.isArray(recs)) recs = recs ? [recs] : [];

        // 3. Generate Form Loop
        let $container = $('#form-dynamic-container');
        $container.empty();

        ids.forEach((rkmId, index) => {
            let namaPT = pts[index] || 'Perusahaan Lain';

            // 1. CARI DATA LAMA (EXISTING DATA)
            // Cari data di array 'recs' yang id_rkm-nya cocok
            let currentRec = recs.find(r => r.id_rkm == rkmId) || {};

            // 2. SIAPKAN DATA MATERI (Untuk Select2)
            let selectedMateri = [];
            if (currentRec.id_materi) {
                selectedMateri = String(currentRec.id_materi).split(',').map(s => s.trim());
            }

            // 3. SIAPKAN DATA KETERANGAN (Untuk Textarea)
            // Jika null/undefined, ganti jadi string kosong ''
            let textKeterangan = currentRec.keterangan || '';

            // 4. RENDER HTML
            let htmlItem = `
                <div class="card bg-light border-0 mb-3 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title text-primary fw-bold mb-3 border-bottom pb-2">
                            ${index + 1}. ${namaPT}
                        </h6>

                        <input type="hidden" name="data[${index}][id_rkm]" value="${rkmId}">

                        <div class="form-group mb-3">
                            <label class="mb-1 fw-bold">Ajukan Rekomendasi Materi Selanjutnya</label>
                            <select name="data[${index}][rekomendasi][]"
                                    class="form-control rekomendasi-dynamic"
                                    multiple="multiple"
                                    data-selected='${JSON.stringify(selectedMateri)}'>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="mb-1">Keterangan</label>

                            <textarea name="data[${index}][keterangan]"
                                    class="form-control"
                                    rows="2"
                                    placeholder="Masukkan keterangan tambahan...">${textKeterangan}</textarea>
                        </div>
                    </div>
                </div>
            `;
            $container.append(htmlItem);
        });

        // 4. Inisialisasi Select2
        initDynamicSelect2();
    });

    function initDynamicSelect2() {
        // Ambil opsi master dari HTML tersembunyi
        let optionsHtml = $('#source-options-materi').html();

        $('.rekomendasi-dynamic').each(function() {
            let $el = $(this);

            // 1. Isi opsi select (Wajib ada sebelum .val() dipanggil)
            $el.html(optionsHtml);

            // 2. Aktifkan Select2
            $el.select2({
                placeholder: "Pilih Materi",
                allowClear: true,
                width: '100%',
                dropdownParent: $('#modalRekomendasiLanjutan')
            });

            // 3. [INTI SOLUSI] Set Value dari data lama
            let savedValues = $el.data('selected'); // Ambil array ["29", "30"]

            if (savedValues && Array.isArray(savedValues) && savedValues.length > 0) {
                // Set value dan paksakan trigger change agar UI Select2 berubah
                $el.val(savedValues).trigger('change');
            }
        });
    }
        $('#formStoreRekomendasi').on('submit', function(e) {
            e.preventDefault();

            // serialize() akan otomatis mengambil 'rekomendasi[]' dan 'id_rkm'
            // sehingga terbaca sebagai array oleh Laravel
            var formData = $(this).serialize();

            $.ajax({
                url: '/rekomendasi-lanjutan/store',
                method: 'POST',
                data: formData,
                success: function(res) {
                    if (res.success) {
                        Swal.fire('Berhasil!', res.message, 'success');
                        $('#modalRekomendasiLanjutan').modal('hide');
                        getDataRKM();
                    }
                },
                error: function(xhr) {
                    // Jika validasi gagal (422), tampilkan pesan errornya
                    var errors = xhr.responseJSON.errors;
                    if (errors && errors.rekomendasi) {
                        Swal.fire('Error!', 'Mohon pilih minimal satu materi.', 'error');
                    } else {
                        Swal.fire('Error!', 'Gagal menyimpan data.', 'error');
                    }
                }
            });
        });

        $('#modalRekomendasiLanjutan').on('hidden.bs.modal', function() {
            $(this).find('form')[0].reset();
            $('input[name="id_rkm"]').remove();
            if ($('#rekomendasi').data('select2')) {
                $('#rekomendasi').select2('destroy');
                $('#keterangan_rekomendasi').val('');
            }
        });

        $(document).ready(function() {
            // 1. Setup CSRF Token
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let dates = [];
            let currentIndex = 0;
            let checklistData = {};

            function generateDates(start, end) {
                let result = [];
                let s = new Date(start);
                let e = new Date(end);

                while (s <= e) {
                    result.push(s.toISOString().split('T')[0]);
                    s.setDate(s.getDate() + 1);
                }

                return result;
            }

            function setDefaultIndex() {
                let today = new Date().toISOString().split('T')[0];

                let index = dates.findIndex(d => d === today);

                if (index !== -1) {
                    currentIndex = index;
                } else {
                    currentIndex = 0; // fallback kalau hari ini di luar range
                }
            }

            function renderDay() {
                let tanggal = dates[currentIndex];
                let data = checklistData[tanggal] || {};
                let sub = data.sub || {};
                let metodeKelas = $('#metode_kelas').val();

                $('#tanggal').val(tanggal);
                $('#labelHari').text(`Hari ke-${currentIndex + 1} (${tanggal})`);

                if (metodeKelas === 'Offline') {
                    let html = `
                        <div class="form-check mb-2">
                            <input class="form-check-input checklist-item " type="checkbox" value="Materi" id="checkMateri" name="checklist[]" {{ $bisaEditChecklist ? '' : 'disabled' }}>
                            <label class="form-check-label" for="checkMateri">Materi</label>
                            <div class="form-check my-2">
                                <input class="form-check-input checklist-item checkSubMateri" type="checkbox" value="Module" id="checkModule" name="checklistSubMateri[]" {{ $bisaEditChecklist ? '' : 'disabled' }}>
                                <label class="form-check-label" for="checkModule">Module</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input checklist-item checkSubMateri" type="checkbox" value="E-Learning" id="checkELearning" name="checklistSubMateri[]" {{ $bisaEditChecklist ? '' : 'disabled' }}>
                                <label class="form-check-label" for="checkELearning">E-Learning</label>
                            </div>
                        </div>
    
                        <br>
    
                        <div class="form-check mb-2">
                            <input class="form-check-input checklist-item" type="checkbox" value="Kelas" id="checkKelas" name="checklist[]" {{ $bisaEditChecklist ? '' : 'disabled' }}>
                            <label class="form-check-label" for="checkKelas">Kelas</label>
                        </div>
    
                        <br>
    
                        <div class="form-check mb-2">
                            <input class="form-check-input checklist-item" type="checkbox" value="Cb" id="checkCb" name="checklist[]" {{ $bisaEditChecklist ? '' : 'disabled' }}>
                            <label class="form-check-label" for="checkCb">Coffe Break</label>
                            <div class="form-check my-2">
                                <input class="form-check-input checklist-item checkSubCb" type="checkbox" id="checkCbInstruktur" value="Instruktur" name="checklistSubCb[]" {{ $bisaEditChecklist ? '' : 'disabled' }}>
                                <label class="form-check-label" for="checkCbInstruktur">Instruktur</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input checklist-item checkSubCb" type="checkbox" id="checkCbPeserta" value="Peserta" name="checklistSubCb[]" {{ $bisaEditChecklist ? '' : 'disabled' }}>
                                <label class="form-check-label" for="checkCbPeserta">Peserta</label>
                            </div>
                        </div>
    
                        <br>
    
                        <div class="form-check mb-2">
                            <input class="form-check-input checklist-item" type="checkbox" value="Maksi" id="checkMaksi" name="checklist[]" {{ $bisaEditChecklist ? '' : 'disabled' }}>
                            <label class="form-check-label" for="checkMaksi">Makan Siang</label>
                            <div class="form-check my-2">
                                <input class="form-check-input checklist-item checkSubMaksi" type="checkbox" id="checkMaksiInstruktur" value="Instruktur" name="checklistSubMaksi[]" {{ $bisaEditChecklist ? '' : 'disabled' }}>
                                <label class="form-check-label" for="checkMaksiInstruktur">Instruktur</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input checklist-item checkSubMaksi" type="checkbox" id="checkMaksiPeserta" value="Peserta" name="checklistSubMaksi[]" {{ $bisaEditChecklist ? '' : 'disabled' }}>
                                <label class="form-check-label" for="checkMaksiPeserta">Peserta</label>
                            </div>
                        </div>
    
                        <br>
    
                        <div class="form-check mb-2">
                            <input class="form-check-input checklist-item" type="checkbox" value="Keperluan Kelas" id="checkKeperluanKelas" name="checklist[]" {{ $bisaEditChecklist ? '' : 'disabled' }}>
                            <label class="form-check-label" for="checkKeperluanKelas">Keperluan Kelas</label>
                            <div class="form-check my-2">
                                <input class="form-check-input checklist-item checkSubKeperluanKelas" type="checkbox" id="checkKeperluanKelasAC" value="AC" name="checklistSubKeperluanKelas[]" {{ $bisaEditChecklist ? '' : 'disabled' }}>
                                <label class="form-check-label" for="checkKeperluanKelasAC">AC</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input checklist-item checkSubKeperluanKelas" type="checkbox" id="checkKeperluanKelasJam" value="Jam" name="checklistSubKeperluanKelas[]" {{ $bisaEditChecklist ? '' : 'disabled' }}>
                                <label class="form-check-label" for="checkKeperluanKelasJam">Jam</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input checklist-item checkSubKeperluanKelas" type="checkbox" id="checkKeperluanKelasBuku" value="Buku" name="checklistSubKeperluanKelas[]" {{ $bisaEditChecklist ? '' : 'disabled' }}>
                                <label class="form-check-label" for="checkKeperluanKelasBuku">Buku</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input checklist-item checkSubKeperluanKelas" type="checkbox" id="checkKeperluanKelasPulpen" value="Pulpen" name="checklistSubKeperluanKelas[]" {{ $bisaEditChecklist ? '' : 'disabled' }}>
                                <label class="form-check-label" for="checkKeperluanKelasPulpen">Pulpen</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input checklist-item checkSubKeperluanKelas" type="checkbox" id="checkKeperluanKelasPermen" value="Permen" name="checklistSubKeperluanKelas[]" {{ $bisaEditChecklist ? '' : 'disabled' }}>
                                <label class="form-check-label" for="checkKeperluanKelasPermen">Permen</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input checklist-item checkSubKeperluanKelas" type="checkbox" id="checkKeperluanKelasCamilan" value="Camilan" name="checklistSubKeperluanKelas[]" {{ $bisaEditChecklist ? '' : 'disabled' }}>
                                <label class="form-check-label" for="checkKeperluanKelasCamilan">Camilan</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input checklist-item checkSubKeperluanKelas" type="checkbox" id="checkKeperluanKelasMinuman" value="Minuman" name="checklistSubKeperluanKelas[]" {{ $bisaEditChecklist ? '' : 'disabled' }}>
                                <label class="form-check-label" for="checkKeperluanKelasMinuman">Minuman</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input checklist-item checkSubKeperluanKelas" type="checkbox" id="checkKeperluanKelasLampu" value="Lampu" name="checklistSubKeperluanKelas[]" {{ $bisaEditChecklist ? '' : 'disabled' }}>
                                <label class="form-check-label" for="checkKeperluanKelasLampu">Lampu</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input checklist-item checkSubKeperluanKelas" type="checkbox" id="checkKeperluanKelasKondisiKebersihan" value="Kondisi Kebersihan" name="checklistSubKeperluanKelas[]" {{ $bisaEditChecklist ? '' : 'disabled' }}>
                                <label class="form-check-label" for="checkKeperluanKelasKondisiKebersihan">Kondisi dan Kebersihan Kelas</label>
                            </div>
                            </div>
                        </div>
                    `;

                    $('#checklistContainer').html(html);
                } else {
                    let html = `
                        <div class="form-check mb-2">
                            <input class="form-check-input checklist-item " type="checkbox" value="Materi" id="checkMateri" name="checklist[]" {{ $bisaEditChecklist ? '' : 'disabled' }}>
                            <label class="form-check-label" for="checkMateri">Materi</label>
                            <div class="form-check my-2">
                                <input class="form-check-input checklist-item checkSubMateri" type="checkbox" value="Module" id="checkModule" name="checklistSubMateri[]" {{ $bisaEditChecklist ? '' : 'disabled' }}>
                                <label class="form-check-label" for="checkModule">Module</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input checklist-item checkSubMateri" type="checkbox" value="E-Learning" id="checkELearning" name="checklistSubMateri[]" {{ $bisaEditChecklist ? '' : 'disabled' }}>
                                <label class="form-check-label" for="checkELearning">E-Learning</label>
                            </div>
                        </div>
    
                        <br>

                        <div class="form-check mb-2">
                            <input class="form-check-input checklist-item" type="checkbox" value="Cb" id="checkCb" name="checklist[]" {{ $bisaEditChecklist ? '' : 'disabled' }}>
                            <label class="form-check-label" for="checkCb">Coffe Break</label>
                            <div class="form-check my-2">
                                <input class="form-check-input checklist-item checkSubCb" type="checkbox" id="checkCbInstruktur" value="Instruktur" name="checklistSubCb[]" {{ $bisaEditChecklist ? '' : 'disabled' }}>
                                <label class="form-check-label" for="checkCbInstruktur">Instruktur</label>
                            </div>
                        </div>
    
                        <br>
    
                        <div class="form-check mb-2">
                            <input class="form-check-input checklist-item" type="checkbox" value="Maksi" id="checkMaksi" name="checklist[]" {{ $bisaEditChecklist ? '' : 'disabled' }}>
                            <label class="form-check-label" for="checkMaksi">Makan Siang</label>
                            <div class="form-check my-2">
                                <input class="form-check-input checklist-item checkSubMaksi" type="checkbox" id="checkMaksiInstruktur" value="Instruktur" name="checklistSubMaksi[]" {{ $bisaEditChecklist ? '' : 'disabled' }}>
                                <label class="form-check-label" for="checkMaksiInstruktur">Instruktur</label>
                            </div>
                        </div>
                    `;

                    $('#checklistContainer').html(html);
                }

                // parent
                $('#checkKelas').prop('checked', data.kelas == 1);

                // sub materi
                $('#checkModule').prop('checked', sub.materi_module == 1);
                $('#checkELearning').prop('checked', sub.materi_elearning == 1);

                // cb
                $('#checkCbInstruktur').prop('checked', sub.cb_instruktur == 1);
                $('#checkCbPeserta').prop('checked', sub.cb_peserta == 1);

                // maksi
                $('#checkMaksiInstruktur').prop('checked', sub.maksi_instruktur == 1);
                $('#checkMaksiPeserta').prop('checked', sub.maksi_peserta == 1);

                // kelas
                $('#checkKeperluanKelasAC').prop('checked', sub.kelas_ac == 1);
                $('#checkKeperluanKelasJam').prop('checked', sub.kelas_jam == 1);
                $('#checkKeperluanKelasBuku').prop('checked', sub.kelas_buku == 1);
                $('#checkKeperluanKelasPulpen').prop('checked', sub.kelas_pulpen == 1);
                $('#checkKeperluanKelasPermen').prop('checked', sub.kelas_permen == 1);
                $('#checkKeperluanKelasCamilan').prop('checked', sub.kelas_camilan == 1);
                $('#checkKeperluanKelasMinuman').prop('checked', sub.kelas_minuman == 1);
                $('#checkKeperluanKelasLampu').prop('checked', sub.kelas_lampu == 1);
                $('#checkKeperluanKelasKondisiKebersihan').prop('checked', sub.kelas_kondisi_kebersihan == 1);

                syncParentCheckbox();
                updateProgressBar();
            }

            //  Fungsi Kalkulasi Progress Bar
            function updateProgressBar() {
                let totalProgress = 0;
                let metodeKelas = $('#metode_kelas').val();

                if (metodeKelas === 'Offline') {
                    // ===== Materi (20%) =====
                    let materiTotal = $('.checkSubMateri').length;
                    let materiChecked = $('.checkSubMateri:checked').length;
                    if (materiTotal > 0) {
                        totalProgress += (materiChecked / materiTotal) * 20;
                    }
    
                    // ===== Kelas (20%) =====
                    if ($('#checkKelas').is(':checked')) {
                        totalProgress += 20;
                    }
    
                    // ===== CB (20%) =====
                    let cbTotal = $('.checkSubCb').length;
                    let cbChecked = $('.checkSubCb:checked').length;
                    if (cbTotal > 0) {
                        totalProgress += (cbChecked / cbTotal) * 20;
                    }
    
                    // ===== Maksi (20%) =====
                    let maksiTotal = $('.checkSubMaksi').length;
                    let maksiChecked = $('.checkSubMaksi:checked').length;
                    if (maksiTotal > 0) {
                        totalProgress += (maksiChecked / maksiTotal) * 20;
                    }
    
                    // ===== Keperluan Kelas (20%) =====
                    let kelasTotal = $('.checkSubKeperluanKelas').length;
                    let kelasChecked = $('.checkSubKeperluanKelas:checked').length;
                    if (kelasTotal > 0) {
                        totalProgress += (kelasChecked / kelasTotal) * 20;
                    }
    
                    let percentage = Math.round(totalProgress);
    
                    $('#checklistProgress')
                        .css('width', percentage + '%')
                        .attr('aria-valuenow', percentage)
                        .text(percentage + '%');

                } else {
                    let totalKategori = 3;
                    let kategoriSelesai = 0;

                    // ===== Materi =====
                    let materiTotal = $('.checkSubMateri').length;
                    let materiChecked = $('.checkSubMateri:checked').length;
                    if (materiTotal > 0) {
                         kategoriSelesai += (materiChecked / materiTotal);
                    }

                    // ===== CB (Instruktur) =====
                    if ($('#checkCbInstruktur').is(':checked')) {
                        kategoriSelesai++;
                    }

                    // ===== Maksi (Instruktur) =====
                    if ($('#checkMaksiInstruktur').is(':checked')) {
                        kategoriSelesai++;
                    }

                    let percentage = Math.round((kategoriSelesai / totalKategori) * 100);
    
                    $('#checklistProgress')
                        .css('width', percentage + '%')
                        .attr('aria-valuenow', percentage)
                        .text(percentage + '%');

                }
            }

            $('#nextDay').click(function () {
                if (currentIndex < dates.length - 1) {
                    currentIndex++;
                    storeChecklistData(false);
                    renderDay();
                }
            });

            $('#prevDay').click(function () {
                if (currentIndex > 0) {
                    currentIndex--;
                    storeChecklistData(false);
                    renderDay();
                }
            });

            // Event Listener untuk Perubahan Kotak Centang
            $(document).on('change', '.checklist-item', function() {

                // HANYA jalankan sync kalau yang berubah itu child
                if (
                    $(this).hasClass('checkSubMateri') ||
                    $(this).hasClass('checkSubCb') ||
                    $(this).hasClass('checkSubMaksi') ||
                    $(this).hasClass('checkSubKeperluanKelas')
                ) {
                    syncParentCheckbox();
                    updateProgressBar();
                }
            });

            function syncParentCheckbox() {
                // Materi -> punya Module & E-Learning
                $('#checkMateri').prop(
                    'checked',
                    $('.checkSubMateri').length > 0 &&
                    $('.checkSubMateri').length === $('.checkSubMateri:checked').length
                );

                // CB -> Instruktur & Peserta
                $('#checkCb').prop(
                    'checked',
                    $('.checkSubCb').length > 0 &&
                    $('.checkSubCb').length === $('.checkSubCb:checked').length
                );

                // Maksi -> Instruktur & Peserta
                $('#checkMaksi').prop(
                    'checked',
                    $('.checkSubMaksi').length > 0 &&
                    $('.checkSubMaksi').length === $('.checkSubMaksi:checked').length
                );

                 // Keperluan Kelas
                $('#checkKeperluanKelas').prop(
                    'checked',
                    $('.checkSubKeperluanKelas').length > 0 &&
                    $('.checkSubKeperluanKelas').length === $('.checkSubKeperluanKelas:checked').length
                );
            }

            // ================= PARENT -> CHILD =================
            $(document).on('change', '#checkMateri', function () {
                $('.checkSubMateri').prop('checked', this.checked);
                updateProgressBar();
            });

            $(document).on('change', '#checkCb', function () {
                $('.checkSubCb').prop('checked', this.checked);
                updateProgressBar();
            });

            $(document).on('change', '#checkMaksi', function () {
                $('.checkSubMaksi').prop('checked', this.checked);
                updateProgressBar();
            });

            $(document).on('change', '#checkKeperluanKelas', function () {
                $('.checkSubKeperluanKelas').prop('checked', this.checked);
                updateProgressBar();
            });

            // Logika Pembukaan Modal
            $('#modalChecklist').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var idRkmStr = button.data('id');
                var modal = $(this);

                var idRkm = String(idRkmStr).split(',')[0].trim();

                // Reset form dan progress bar
                $('#formChecklistKeperluan')[0].reset();
                modal.find('#inputHiddenIdRkm').val(idRkm);
                updateProgressBar(); // Reset ke 0%

                // Request AJAX untuk memuat data
                $.ajax({
                    url: '/rkm/checklist/' + idRkm,
                    type: 'GET',
                    success: function(response) {
                        if (response.status && response.data) {
                            checklistData = response.data || {};
                            dates = generateDates(response.tanggal_awal, response.tanggal_akhir);
                            $('#metode_kelas').val(response.metode_kelas);

                            setDefaultIndex();
                            renderDay();
                        }
                    },
                    error: function(error) {
                        console.error('Gagal mengambil data checklist dari server. ', error);
                    }
                });
            });

            $('#modalChecklist').on('hidden.bs.modal', function () {
                storeChecklistData(false); 
            });

            // Logika Penyimpanan Data
            // $('#btnSimpanChecklist').click(function() {
            //     storeChecklistData(true);
            // });
            

            function storeChecklistData(showAlert = false)
            {
                var form = $('#formChecklistKeperluan');
                var formData = form.serialize();

                $.ajax({
                    url: '{{ route("rkm.checklist.store") }}',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.status) {

                            // hanya tampil kalau diminta
                            if (showAlert) {
                                $('#modalChecklist').modal('hide');
                                alert(response.message);
                            }
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);

                        if (showAlert) {
                            alert('Terjadi kesalahan sistem saat menyimpan data.');
                        }
                    }
                });
            }
        });
</script>
@endpush
@endsection
