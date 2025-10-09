@extends('databasekpi.berandaKPI')

@section('contentKPI')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    #table-fixed thead th {
        position: sticky;
        top: 0;
        background-color: white;
        z-index: 1;
    }

    #scrollable-table {
        max-height: 650px;
        overflow-y: auto;
        display: block;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    #scrollable-table::-webkit-scrollbar {
        width: 0;
        height: 0;
    }

    @media (max-width: 768px) {
        #jenis-penilaian-tab {
            flex-direction: column !important;
            overflow-x: unset;
            width: 100%;
        }

        #jenis-penilaian-tab .list-group-item {
            text-align: left;
        }
    }

    #table-fixed {
        width: 100%;
        table-layout: auto;
        border-collapse: collapse;
    }

    #table-fixed th,
    #table-fixed td {
        word-wrap: break-word;
        overflow-wrap: break-word;
        white-space: normal;
        text-align: center;
        padding: 0.25rem 0.5rem;
        font-size: 12px;
    }

    #table-fixed td.text-left,
    #table-fixed th.text-left {
        text-align: left;
    }

    @media (max-width: 480px) {

        #table-fixed th,
        #table-fixed td {
            font-size: 10px;
            padding: 0.2rem 0.3rem;
        }
    }

    .stylish-textarea {
        border: 1.5px solid #ced4da;
        border-radius: 12px;
        padding: 12px 15px;
        font-size: 15px;
        background-color: #f9f9f9;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease-in-out;
        resize: vertical;
    }

    .stylish-textarea:focus {
        border-color: #007bff;
        background-color: #ffffff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        outline: none;
    }

    #table-fixed {
        width: 100%;
        border-collapse: collapse;
    }

    #table-fixed thead th {
        white-space: nowrap;
        background-color: var(--bs-body-bg) !important;
        color: var(--bs-body-color) !important;

    }

    #table-fixed tbody td {
        white-space: nowrap;
    }

    .table-responsive-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .form-group label {
        font-weight: bold;
    }

    @media (max-width: 768px) {
        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 14px;
        }

        .form-group ul.list-group {
            font-size: 14px;
        }

        .stylish-textarea {
            font-size: 14px;
        }
    }

    .evaluator-list {
        max-height: 200px;
        overflow-y: scroll;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .evaluator-list::-webkit-scrollbar {
        display: none;
    }

    .active-tab {
        background-color: #198ae3 !important;
        color: white !important;
        border-color: #198ae3;
    }

    .active-tab:hover {
        color: black;
        background: #3c9ce7;
        border-color: #3096e6;
    }

    .active-tab:focus {
        box-shadow: 0 0 0 0.25rem rgba(var(21, 117, 193), .5);
    }

    .active-tab:active {
        color: black;
        background: #47a1e9;
        border-color: #3096e6;
        box-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125);
    }

    .active-tab:disabled {
        color: black;
        background: #198ae3;
        border-color: #198ae3;
    }
</style>
<div class="content-wrapper">
    <div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="loading-spinner"></div>
        </div>
    </div>
    <div class="page-header">
        <h3 class="page-title">
            <span class="page-title-icon bg-gradient-primary text-white me-2">
                <i class="mdi mdi-file-document"></i>
            </span> Penilaian
        </h3>
        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">
                    <span></span>Detail Penilaian <i class="mdi mdi-alert-circle-outline icon-sm text-primary align-middle" data-bs-toggle="tooltip" data-bs-placement="top" title="Anda hanya dapat mereview hasil penilaian, tapi tidak dengan melakukan aksi!"></i>
                </li>
            </ul>
        </nav>
    </div>
    <div class="row">
        <div class="col">
            @if (session('success'))
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: "{{ session('success') }}",
                    confirmButtonColor: '#3085d6'
                });
            </script>
            @endif

            @if (session('error'))
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: "{{ session('error') }}",
                    confirmButtonColor: '#d33'
                });
            </script>
            @endif

            @if ($errors->any())
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    html: `{!! implode('<br>', $errors->all()) !!}`,
                    confirmButtonColor: '#d33'
                });
            </script>
            @endif
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
            <a href="{{ route('ketegoriKPI.get') }}" class="btn text-white btn-danger mb-4"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
            <div class="text-center mb-3">
                <h3>Detail Penilaian</h3>
            </div>
            @if ($kodeForm)
            <input type="hidden" id="kodeForm" name="kodeForm" value="{{ $kodeForm }}">
            @endif
            @if ($id_karyawan)
            <input type="hidden" id="id_karyawan" name="id_karyawan" value="{{ $id_karyawan }}">
            @endif
            <div id="shareEmail" class="mb-2 text-start fixed justify-content-start"></div>

            <div class="container mt-4 text-center">
                <div class="row justify-content-center">
                    <div class="col-sm-4 mx-auto">
                        <div class="card p-4 ms-4 mb-4">
                            <div class="text-left" id="content_utama">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-8">
                        <div class="card p-3">
                            <div id="scrollable-table">
                                <div class="d-flex justify-content-end me-5">
                                    <div>
                                        <div class="list-group text-center list-group-horizontal mb-3" id="jenis-penilaian-tab" role="tablist">
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive-wrap">
                                    <table class="table" id="table-fixed bg-theme">
                                        <thead class="bg-theme">
                                            <tr>
                                                <th scope="col">Kriteria</th>
                                                <th scope="col">Sub Kriteria</th>
                                                <th scope="col">Bobot</th>
                                                <th scope="col">Nilai</th>
                                                <th scope="col">Nilai Rata-Rata</th>
                                                <th scope="col">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body_content">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="card  bg-theme p-3 mt-4">
                            <div class="title">Data Jumlah Absensi</div>
                            <div id="scrollable-table">
                                <table class="table bg-theme" id="table-fixed">
                                    <thead class="bg-theme">
                                        <tr>
                                            <th scope="col">Telat</th>
                                            <th scope="col">Sakit</th>
                                            <th scope="col">Izin</th>
                                        </tr>
                                    </thead>
                                    <tbody id="body_content_absensi">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container mt-5">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Trendline Tahun Ini</h4>
                                    <canvas id="barChart" style="height: 139px; display: block; box-sizing: border-box; width: 278px;" width="834" height="417"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Trendline Progress</h4>
                                    <canvas id="lineChart" style="height: 139px; display: block; box-sizing: border-box; width: 278px;" width="834" height="417"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" name="jenis_form" id="jenis_form" value="{{ $tipe }}">
</div>
@if (session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if (session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
@endsection
@section('script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('template_KPI/dist/assets/vendors/chart.js/chart.umd.js') }}"></script>
<script>
    let globalEvaluators = [];
    let globalKriteria = [];
    let globalEvaluated = {};
    let globalTahun = '';

    $(document).ready(function() {
        loadData();
        loadChartData();
    });

    function pengubahFormat(angka) {
        return angka.toLocaleString('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }


    $('#jenis-penilaian-tab').on('click', '.list-group-item', function() {
        $('#jenis-penilaian-tab .list-group-item').removeClass('active-tab');
        $(this).addClass('active-tab');

        const jenis = $(this).data('jenis');
        renderTabel(jenis);
    });

    function loadData() {
        let formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('kodeForm', $('#kodeForm').val());
        formData.append('id_karyawan', $('#id_karyawan').val());
        formData.append('tahun', $('#selectTahun').val());
        formData.append('jenis_form', $('#jenis_form').val());

        $.ajax({
            url: "{{ route('penilaian.detail.get') }}",
            type: 'POST',
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function(response) {
                const data = response.data[0];
                globalEvaluators = data.data.evaluator;
                globalKriteria = data.data.dataKriteria;
                globalEvaluated = data.evaluated;
                globalChart = data.chart;
                globalAbsensi = data.dataAbsen;
                globalTahun = data.evaluated.tahun;

                let content_utama = $('#content_utama');
                content_utama.empty();

                let content_absensi = $('#body_content_absensi');
                content_absensi.empty();

                if (!globalAbsensi.isEmpty) {
                    content_absensi.append(`
                        <tr>
                            <td>${globalAbsensi.telat}</td>
                            <td>${globalAbsensi.sakit}</td>
                            <td>${globalAbsensi.izin}</td>
                        </tr>
                    `);
                } else {
                    content_absensi.append(`
                        <tr>
                            <td colspan="3" class="text-center text-muted">Tidak ada data absensi</td>
                        </tr>
                    `);
                }


                const jenisList = [...new Set(globalEvaluators.map(ev => ev.jenis_penilaian))];

                let kodeForm = globalKriteria.length > 0 ? globalKriteria[0].kodeForm : '';
                let id_karyawan = globalEvaluated.id_karyawan ?? '';

                let jenisHTML = `
                    <a class="list-group-item list-group-item-action selectable-jenis active-tab" data-jenis="all">
                        Semua
                    </a>
                `;

                let emailSend = `
                    <div class="d-flex align-items-start gap-2">
                        <button id="kirimEmail" class="btn text-white btn-success me-3" style="margin-right: 10px;" data-kodeform="${kodeForm}" data-id="${id_karyawan}">
                            <i class="fa-solid fa-paper-plane"></i> Email
                        </button>

                        <form method="POST" action="{{ route('penilaian.download.pdf') }}">
                            @csrf
                            <input type="hidden" name="kodeForm" value="${kodeForm}">
                            <input type="hidden" name="id_karyawan" value="${id_karyawan}">

                            <div class="btn-group" role="group">
                                <button type="submit" name="tipe" value="office" class="btn text-white btn-danger">
                                    <i class="fa-solid fa-file-pdf"></i> Office
                                </button>
                                <button type="submit" name="tipe" value="non_office" class="btn text-white btn-danger">
                                    <i class="fa-solid fa-file-pdf"></i> Non Office
                                </button>
                            </div>
                        </form>
                    </div>
                `;

                jenisList.forEach(jenis => {
                    let label;

                    if (jenis === 'General Manager') {
                        label = 'General Manager';
                    } else if (jenis === 'Manager/SPV/Team Leader (Atasan Langsung)') {
                        label = 'Koordinator';
                    } else if (jenis === 'Rekan Kerja (Satu Divisi)') {
                        label = 'Satu Divisi';
                    } else if (jenis === 'Pekerja (Beda Divisi)') {
                        label = 'Beda Divisi';
                    } else if (jenis === 'Self Apprisial') {
                        label = 'Self Apprisial';
                    } else {
                        label = jenis;
                    }

                    jenisHTML += `
                        <a class="list-group-item list-group-item-action selectable-jenis" data-jenis="${jenis}" style="white-space: nowrap;">
                            ${label}
                        </a>                     
                        `;
                });

                $('#shareEmail').html(emailSend);
                $('#jenis-penilaian-tab').html(jenisHTML);

                let listEvaluatorHTML = globalEvaluators.map(ev =>
                    `<li data-target="${ev.nama}-${ev.jenis_penilaian}" class="list-group-item">${ev.nama}</li>`
                ).join('');
                let jenisPenilaian = globalEvaluators.length > 0 ? globalEvaluators[0].jenis_penilaian : '-';

                content_utama.append(`
                    <div class="form-group mb-3 text-start">
                        <label class="mb-2">Jenis Form</label>
                        <ul class="list-group ms-2"><li class="list-group-item">{{ $tipe }}</li></ul>
                    </div>
                    <div class="form-group mb-3 text-start">
                        <label class="mb-2">Evaluator</label>
                        <ul class="list-group ms-2 evaluator-list">${listEvaluatorHTML}</ul>
                    </div>
                    <div class="form-group mb-3 text-start">
                        <label class="mb-2">Yang Dinilai</label>
                        <ul class="list-group ms-2"><li class="list-group-item">${globalEvaluated.nama}</li></ul>
                    </div>
                    <div class="form-group mb-3 text-start">
                        <label class="mb-2">Quartal</label>
                        <ul class="list-group ms-2"><li class="list-group-item">${globalEvaluated.quartal}</li></ul>
                    </div>
                    <div class="form-group mb-3 text-start">
                        <label class="mb-2">Tahun</label>
                        <ul class="list-group ms-2"><li class="list-group-item">${globalEvaluated.tahun}</li></ul>
                    </div>
                    <form method="post" action="{{ route('penilaian.sendCatatan') }}">
                        @csrf
                        <input type="hidden" name="id_karyawan" value="${globalEvaluated.id_karyawan}">
                        <input type="hidden" name="quartal" value="${globalEvaluated.quartal}">
                        <input type="hidden" name="tahun" value="${globalEvaluated.tahun}">
                        <input type="hidden" name="kode_form" value="${globalEvaluated.kode_form}">

                        <div class="form-group mb-3 text-start">
                            <label class="mb-2">Catatan</label>
                            <textarea class="list-group ms-2 form-control stylish-textarea bg-theme border-none" placeholder="berikan catatan..." rows="4" name="catatan">${globalEvaluated.catatan === 'null' || globalEvaluated.catatan === null ? '' : globalEvaluated.catatan}</textarea>
                        </div>
                        <div class="form-group mb-3 text-end">
                            <button type="submit" class="btn btn-info text-white">Simpan</button>
                        </div>
                    </form>
                `);

                renderTabel('all');
            }
        });
    }

    function renderTabel(filterJenis) {
        let content = $('#body_content');
        content.empty();

        const persentaseJenis = {
            'General Manager': 35,
            'Manager/SPV/Team Leader (Atasan Langsung)': 30,
            'Rekan Kerja (Satu Divisi)': 20,
            'Pekerja (Beda Divisi)': 10,
            'Self Apprisial': 5
        };

        let groupRata2 = {};
        let filteredEvaluators = filterJenis === 'all' ? globalEvaluators : globalEvaluators.filter(ev => ev.jenis_penilaian === filterJenis);

        if (filteredEvaluators.length === 0) {
            content.append(`<tr><td colspan="6" class="text-center">Tidak Ada Data</td></tr>`);
            return;
        }

        filteredEvaluators.forEach(evaluator => {
            let nilaiList = evaluator.nilai;
            let nilaiIndex = 0;
            globalKriteria.forEach(kriteria => {
                kriteria.detailKriteria.forEach(sub => {
                    const nilaiItem = nilaiList[nilaiIndex++] || {
                        nilai: '-',
                        pesan: '-'
                    };
                    const nilai = parseFloat(nilaiItem.nilai);
                    if (sub.tipe_input !== 'textarea' && !isNaN(nilai)) {
                        groupRata2[evaluator.jenis_penilaian] = groupRata2[evaluator.jenis_penilaian] || {};
                        groupRata2[evaluator.jenis_penilaian][kriteria.kriteria] = groupRata2[evaluator.jenis_penilaian][kriteria.kriteria] || {};
                        groupRata2[evaluator.jenis_penilaian][kriteria.kriteria][sub.sub_kriteria] = groupRata2[evaluator.jenis_penilaian][kriteria.kriteria][sub.sub_kriteria] || [];
                        groupRata2[evaluator.jenis_penilaian][kriteria.kriteria][sub.sub_kriteria].push(nilai);
                    }
                });
            });
        });

        let rata2Hasil = {};
        for (const jenis in groupRata2) {
            rata2Hasil[jenis] = {};
            for (const kriteria in groupRata2[jenis]) {
                rata2Hasil[jenis][kriteria] = {};
                for (const sub in groupRata2[jenis][kriteria]) {
                    let arr = groupRata2[jenis][kriteria][sub];
                    let avg = arr.reduce((a, b) => a + b, 0) / arr.length;
                    rata2Hasil[jenis][kriteria][sub] = avg;
                }
            }
        }

        let jenisTotalRaw = {};

        filteredEvaluators.forEach(evaluator => {
            content.append(`
                <tr id="${evaluator.nama}-${evaluator.jenis_penilaian}" class="text-start">
                    <td colspan="6" class="bg-theme fw-bold">
                        ${evaluator.nama} - ${evaluator.jenis_penilaian}
                    </td>
                </tr>
            `);

            let nilaiList = evaluator.nilai;
            let nilaiIndex = 0;
            let totalSkorEvaluator = 0;

            globalKriteria.forEach(kriteria => {
                const subKriteriaList = kriteria.detailKriteria;
                const rowspan = subKriteriaList.length;
                subKriteriaList.forEach((sub, idxSub) => {
                    const nilaiItem = nilaiList[nilaiIndex++] || {
                        nilai: '-',
                        pesan: '-'
                    };
                    const nilai = nilaiItem.nilai;
                    const pesan = nilaiItem.pesan;
                    const tipe = sub.tipe_input;
                    const bobot = parseFloat(sub.bobot);
                    let dataNilai = '';
                    if (tipe === 'textarea') {
                        dataNilai = `<td colspan="4">${pesan && pesan.trim() !== '' ? pesan : '-'}</td>`;
                    } else {
                        const nilaiAngka = isNaN(parseFloat(nilai)) ? 0 : parseFloat(nilai);
                        const rata = rata2Hasil[evaluator.jenis_penilaian]?.[kriteria.kriteria]?.[sub.sub_kriteria] ?? nilaiAngka;
                        const skor = (rata * bobot) / 100;
                        totalSkorEvaluator += skor;
                        dataNilai = `
                        <td>${bobot} %</td>
                        <td>${nilai}</td>
                        <td>${pengubahFormat(rata)}</td>
                        <td>${pengubahFormat(skor)}</td>`;
                    }
                    content.append(`
                    <tr>
                        ${idxSub === 0 ? `<td rowspan="${rowspan}" class="text-left">${kriteria.kriteria}</td>` : ''}
                        <td style="text-align: left;">${sub.sub_kriteria}</td>
                        ${dataNilai}
                    </tr>
                `);
                });
            });

            content.append(`
            <tr class="fw-bold text-center text-white" style="background: #7F8CAA">
                <td colspan="5" class="text-center mb-3">
                Total (${evaluator.nama})
                </td>
                <td>${pengubahFormat(totalSkorEvaluator)}</td>
            </tr>
        `);

            const jenis = evaluator.jenis_penilaian;
            if (!jenisTotalRaw.hasOwnProperty(jenis)) {
                jenisTotalRaw[jenis] = totalSkorEvaluator;
            }
        });

        let jenisTotalPost = {};
        for (const jenis in jenisTotalRaw) {
            const persen = persentaseJenis[jenis] || 0;
            jenisTotalPost[jenis] = (jenisTotalRaw[jenis] * persen) / 100;
        }

        let totalSemuaSkor = 0;
        if (filterJenis === 'all') {
            totalSemuaSkor = Object.values(jenisTotalPost).reduce((a, b) => a + b, 0);
        } else {
            totalSemuaSkor = jenisTotalPost[filterJenis] || 0;
        }

        let grade = '';
        let keterangan = '';
        if (totalSemuaSkor >= 90) {
            grade = 'A';
            keterangan = 'Sangat Baik';
        } else if (totalSemuaSkor >= 80) {
            grade = 'B';
            keterangan = 'Baik';
        } else if (totalSemuaSkor >= 70) {
            grade = 'C';
            keterangan = 'Cukup';
        } else if (totalSemuaSkor >= 60) {
            grade = 'D';
            keterangan = 'Kurang';
        } else {
            grade = 'E';
            keterangan = 'Sangat Kurang';
        }

        content.append(`
            <tr class="fw-bold text-white" style="background: #546E7A">
                <td colspan="5" class="text-end">Total Semua Nilai</td>
                <td>${pengubahFormat(totalSemuaSkor)}</td>
            </tr>
            <tr class="fw-bold text-white" style="background: #546E7A">
                <td colspan="5" class="text-end">Kriteria</td>
                <td class="text-center">${keterangan}</td>
            </tr>
            <tr class="fw-bold text-white" style="background: #546E7A">
                <td colspan="5" class="text-end">Grade</td>
                <td class="text-center">${grade}</td>
            </tr>
        `);

        tampilkanChartTahunIni(globalChart.quartal);
        tampilkanChartSemuaTahun(globalChart.all);
    }

    $(document).on('click', '.evaluator-list .list-group-item', function() {
        const targetId = $(this).data('target');
        const evaluatorRow = document.getElementById(targetId);

        if (evaluatorRow) {
            $('html, body').animate({
                scrollTop: $(evaluatorRow).offset().top
            }, 500);
        }
    });

    $(document).on('click', '#kirimEmail', function(e) {
        e.preventDefault();

        let kodeForm = $(this).data('kodeform');
        let id_karyawan = $(this).data('id');
        console.log('id:', id_karyawan, 'kodeForm:', kodeForm, 'ini kirim ke gmail');

        $.ajax({
            url: "{{ route('penilaian.email') }}",
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                kodeForm: kodeForm,
                id_karyawan: id_karyawan
            },
            dataType: 'json',
            success: function(response) {
                alert('Email berhasil dikirim!');
            },
            error: function(xhr) {
                console.error('Gagal:', xhr.responseText);

                alert(`Gagal mengirim email: ${xhr.responseText}`);
            }
        });
    });
</script>
<script>
    let chartTahunIni = null;
    let chartAllYears = null;

    function loadChartData() {
        let formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('kodeForm', $('#kodeForm').val());
        formData.append('id_karyawan', $('#id_karyawan').val());
        formData.append('tahun', $('#selectTahun').val());

        $.ajax({
            url: "{{ route('penilaian.detailChart.get') }}",
            type: 'POST',
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function(res) {
                if (res.chartQuartal) {
                    tampilkanChartTahunIni(res.chartQuartal);
                }
                if (res.chartAllYears) {
                    tampilkanChartSemuaTahun(res.chartAllYears);
                }
            },
            error: function(xhr) {
                console.error(xhr.responseText);
            }
        });
    }

    const selectTahun = document.getElementById("selectTahun");
    const tahunSekarang = new Date().getFullYear();
    for (let tahun = tahunSekarang; tahun <= tahunSekarang + 10; tahun++) {
        const option = document.createElement("option");
        option.value = tahun;
        option.textContent = tahun;
        if (tahun === tahunSekarang) {
            option.selected = true;
        }
        selectTahun.appendChild(option);
    }

    $('#selectTahun').on('change', function() {
        loadChartData();
    });

    function tampilkanChartTahunIni(quartalData) {
        const ctx = document.getElementById("barChart").getContext("2d");
        if (chartTahunIni) chartTahunIni.destroy();

        const labels = [];
        const dataValues = [];

        Object.entries(quartalData).forEach(([quartal, forms]) => {
            if (typeof forms === "object") {
                Object.entries(forms).forEach(([kodeForm, skor]) => {
                    labels.push(`${quartal}`);
                    dataValues.push(parseFloat(skor).toFixed(2));
                });
            } else {
                labels.push(quartal);
                dataValues.push(parseFloat(forms).toFixed(2));
            }
        });

        chartTahunIni = new Chart(ctx, {
            type: "bar",
            data: {
                labels: labels,
                datasets: [{
                    label: "Total Skor per Quartal (Tahun Ini)",
                    data: dataValues,
                    backgroundColor: "rgba(54, 162, 235, 0.6)",
                    borderColor: "rgba(54, 162, 235, 1)",
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toFixed(2);
                            }
                        }
                    }
                }
            }
        });
    }

    function tampilkanChartSemuaTahun(allData) {
        const ctx = document.getElementById("lineChart").getContext("2d");
        if (chartAllYears) chartAllYears.destroy();

        const quartals = [...new Set(
            Object.values(allData).flatMap(yearData => Object.keys(yearData))
        )];

        const datasets = Object.keys(allData).map(year => {
            const data = quartals.map(q => {
                const forms = allData[year]?.[q];
                if (!forms) return null;

                if (typeof forms === "object") {
                    return Object.values(forms).reduce((a, b) => a + parseFloat(b), 0);
                }

                return parseFloat(forms);
            });

            return {
                label: `Tahun ${year}`,
                data: data,
                borderWidth: 2,
                fill: false,
                tension: 0.2
            };
        });

        chartAllYears = new Chart(ctx, {
            type: "line",
            data: {
                labels: quartals,
                datasets: datasets
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toFixed(2);
                            }
                        }
                    }
                }
            }
        });
    }
</script>

@endsection