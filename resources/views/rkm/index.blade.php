@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
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
    <div class="row justify-content-center">
        <div class="col-md-12 d-flex my-2 justify-content-end">
            @can('Create RKM')
                <a class="btn click-primary mx-1" href="{{ route('rkm.create') }}">Tambah RKM</a>
            @endcan
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
                        <button type="submit" onclick="excelDownload()" class="btn btn-success" style="margin-top: 30px">Download excel</button>
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

    function excelDownload() {
        var tahun = document.getElementById('tahun').value;
        var bulan = document.getElementById('bulan').value;

        var form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('excel') }}";
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
                                const examArray = rkm.exam.split(',').map(item => item.trim());
                                const exam = Number(examArray[0]);
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
                                    html += '<div class="dropdown-menu">';
                                    // Gunakan ID pertama untuk link Detail RKM
                                    const firstId = rkm.id_all ? String(rkm.id_all).split(', ')[0] : '';
                                    html += '<a class="dropdown-item" href="/rkm/' + rkm.materi_key + 'ixb' + tanggal + 'ie' + hunta + 'ie' + lanbu + 'ixb' + kelas + '" data-toggle="tooltip" data-placement="top" title="Detail RKM">';
                                    html += '<img src="{{ asset('icon/clipboard-primary.svg') }}" class="me-1"> Detail RKM</a>';
                                    // 1. Siapkan Array ID RKM (Asumsi backend mengirim string "1, 2, 3")
                                    const idList = rkm.id_all ? String(rkm.id_all).split(',').map(i => i.trim()) : [];

                                    // 2. Siapkan Array Nama Perusahaan
                                    const perusahaanList = rkm.perusahaan.map(p => p.nama_perusahaan);
                                    // console.log(perusahaanList);
                                    const existingRecs = rkm.rekomendasi_group || [];
                                    // Encode data ke JSON String untuk data-attribute
                                    const jsonIds = JSON.stringify(idList).replace(/"/g, '&quot;');
                                    const jsonPT = JSON.stringify(perusahaanList).replace(/"/g, '&quot;');
                                    const jsonRecs = JSON.stringify(existingRecs).replace(/"/g, '&quot;');

                                    // Render Tombol
                                    html += '<a class="dropdown-item js-ajukan-rekomendasi" href="#" ' +
                                        'data-materi="' + rkm.materi.nama_materi + '" ' +
                                        'data-tanggal="' + moment(rkm.tanggal_awal).format('DD MMMM YYYY') + '" ' +
                                        
                                        // Kirim 3 Data Array Penting ini
                                        'data-ids=\'' + jsonIds + '\' ' +
                                        'data-perusahaan=\'' + jsonPT + '\' ' +
                                        'data-recs=\'' + jsonRecs + '\' ' +

                                        'data-bs-toggle="modal" data-bs-target="#modalRekomendasiLanjutan">' +
                                        '<img src="{{ asset("icon/clipboard-primary.svg") }}" class="me-1"> Ajukan Rekomendasi</a>';
                                    html += '</div></div></td>';
                                    if (jabatan == 'Customer Care') {
                                        html += '<div class="dropdown-divider"></div>';
                                        html += '<div class="dropdown-item dropdown-submenu left">';
                                        html += '<a class="dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Makanan</a>';
                                        html += '<div class="dropdown-menu">';
                                        // Ambil nilai makanan pertama untuk penandaan centang
                                        const makananList = rkm.makanan ? String(rkm.makanan).split(', ') : [];
                                        const makananValue = makananList.length > 0 ? makananList[0] : 'Tidak Ada';
                                        const idList = rkm.id ? String(rkm.id).split(',').map(i => i.trim()) : [];
                                            // html += '<p class="text-muted small">IDs: ' + JSON.stringify(idList) + '</p>';
                                        html += '<a class="dropdown-item js-update-makanan" href="#" ' + 'data-ids=\'' + JSON.stringify(rkm.id || []) + '\' ' + 'data-val="0">Tidak Ada</a>';
                                        html += '<a class="dropdown-item js-update-makanan" href="#" ' + 'data-ids=\'' + JSON.stringify([rkm.id] || []) + '\' ' + 'data-val="1">' + (makananValue === '1' || makananValue === 'Nasi Box' ? '✔ ' : '') + 'Nasi Box</a>';
                                        html += '<a class="dropdown-item js-update-makanan" href="#" ' + 'data-ids=\'' + JSON.stringify([rkm.id] || []) + '\' ' + 'data-val="2">' + (makananValue === '2' || makananValue === 'Prasmanan' ? '✔ ' : '') + 'Prasmanan</a>';
                                        html += '</div>';
                                        html += '</div>';
                                    }
                                    html += '</div>';
                                    html += '</div>';
                                    html += '</td>';
                                }
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
        let ids = $(this).data('ids');           // Array ID
        let pts = $(this).data('perusahaan');    // Array PT
        let recs = $(this).data('recs');         // Array Existing Data

        // Fallback parsing (aman jika data sudah berupa object)
        if (typeof ids === 'string') ids = JSON.parse(ids);
        if (typeof pts === 'string') pts = JSON.parse(pts);
        if (typeof recs === 'string') recs = JSON.parse(recs);

        // 3. Generate Form Loop
        let $container = $('#form-dynamic-container');
        $container.empty(); // Reset container sebelum isi ulang

        ids.forEach((rkmId, index) => {
            let namaPT = pts[index] || 'Perusahaan Lain';

            // Cari data rekomendasi lama
            let currentRec = recs.find(r => r.id_rkm == rkmId) || {};
            // Pastikan selectedMateri array valid
            let selectedMateri = currentRec.id_materi ? String(currentRec.id_materi).split(',') : [];
            let textKeterangan = currentRec.keterangan || '';

            // Template HTML Per Item
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
                            <textarea name="data[${index}][keterangan]" class="form-control" rows="2" placeholder="Keterangan untuk ${namaPT}...">${textKeterangan}</textarea>
                        </div>
                    </div>
                </div>
            `;
            $container.append(htmlItem);
        });

        // 4. Inisialisasi Select2 setelah elemen masuk ke DOM
        initDynamicSelect2(); 
    });

    // Fungsi Helper Init Select2
    function initDynamicSelect2() {
        // Ambil HTML options dari sumber tersembunyi (Langkah 1)
        let optionsHtml = $('#source-options-materi').html();

        $('.rekomendasi-dynamic').each(function() {
            let $el = $(this);
            
            // 1. Masukkan Option Materi ke dalam Select
            $el.html(optionsHtml);

            // 2. Ambil data value yang tersimpan (jika ada)
            // Kita parse JSON string array yang kita simpan di data-selected tadi
            let selectedValues = $el.data('selected'); 

            // 3. Aktifkan Select2
            $el.select2({
                placeholder: "Pilih Materi",
                allowClear: true,
                width: '100%',
                dropdownParent: $('#modalRekomendasiLanjutan') // Wajib agar input search bisa diklik di modal
            });

            // 4. Set Value Terpilih (Trigger change agar Select2 update tampilan)
            if (selectedValues && selectedValues.length > 0) {
                $el.val(selectedValues).trigger('change');
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
</script>
@endpush
@endsection