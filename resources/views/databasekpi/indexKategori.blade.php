@extends('databasekpi.berandaKPI')

@section('contentKPI')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
    #table_karyawan td.text-start {
        text-align: left;
        border: none;
    }

    #table_karyawan_wrapper .dataTables_scrollBody::-webkit-scrollbar {
        height: 10px;
        width: 8px;
        background-color: transparent;
        border: none;
    }

    .loading-spinner {
        width: 60px;
        height: 60px;
        border: 6px solid transparent;
        border-top: 6px solid #a78bfa;
        border-right: 6px solid #38bdf8;
        border-bottom: 6px solid #34d399;
        border-left: 6px solid #facc15;
        border-radius: 50%;
        animation: spin 1.2s linear infinite;
        margin: auto;
    }

    @keyframes spin {
        100% {
            transform: rotate(360deg);
        }
    }

    #table_karyawan_wrapper .dataTables_length select {
        width: 80px !important;
    }

    #table_karyawan_wrapper .dataTables_filter input {
        width: 200px !important;
    }

    #table_karyawan_wrapper .dataTables_paginate .paginate_button {
        background: transparent !important;
        color: black !important;
        border: none !important;
        margin: 0 5px !important;
    }

    #table_karyawan_wrapper .dataTables_paginate .paginate_button.current {
        background: linear-gradient(to right, #da8cff, #9a55ff) !important;
        color: white !important;
        border-radius: 15% !important;
    }

    .table-responsive {
        overflow-x: none !important;
    }
</style>
<div class="content-wrapper">
    <div class="page-header">
        <h3 class="page-title">
            <span class="page-title-icon bg-gradient-primary text-white me-2">
                <i class="mdi mdi-file-document"></i>
            </span> Penilaian
        </h3>
        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">
                    <span></span>
                    Tabel Penilaian
                    <i class="mdi mdi-alert-circle-outline icon-sm text-primary align-middle"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        title="Halaman ini berisi daftar hasil penilaian karyawan.">
                    </i>
                </li>
            </ul>
        </nav>
    </div>
    <div class="row">
        <div class="col">
            <div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="loading-spinner"></div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="text-end">
                        <a href="{{ route('ketegori.kpi.create') }}" class="btn btn-primary">Buat Penilaian</a>
                    </div>
                    <div class="card mt-2">
                        <div class="card-body table-responsive">
                            <h3 class="card-title text-center my-1 mb-5">Database Penilaian {{ $tipe }}</h3>
                            <div class="container text-start w-100">
                                <div class="row">
                                    <div class="col-md">
                                        <div class="mt-1 mb-1">
                                            <div class="form-group">
                                                <label for="divisiSelectUtama">Pilih Divisi</label>
                                                <select name="divisiSelectUtama" id="divisiSelectUtama" class="form-select w-100">
                                                    <option selected disabled>Semua Divisi</option>
                                                    @foreach ($divisi as $item)
                                                    <option value="{{ $item }}">{{ $item }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md">
                                        <div class="mt-1 mb-1">
                                            <div class="form-group">
                                                <label for="quartalSelectUtama">Pilih Semester</label>
                                                <select name="quartalSelectUtama" id="quartalSelectUtama" class="form-select w-100">
                                                    <option value="S1">S1</option>
                                                    <option value="S2">S2</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md">
                                        <div class="mt-1 mb-1">
                                            <div class="form-group">
                                                <label for="tahunSelectUtama">Pilih Tahun</label>
                                                <select name="tahunSelectUtama" id="tahunSelectUtama" class="form-select w-100">
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="jenis_form" id="jenis_form" value="{{ $tipe }}">
                                </div>
                            </div>
                            <div class="table-responsif">
                                <table id="table_karyawan" class="table table-bordered table-striped text-center bg-theme">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Evaluator</th>
                                            <th>Yang Dinilai</th>
                                            <th>Divisi</th>
                                            <th>Tanggal Pembuatan</th>
                                            <th>Kode Form</th>
                                            <th>Semester</th>
                                            <th>Tahun</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody_table" class="text-center">
                                        <tr style="color: black;">
                                            <td colspan="9">Tidak Ada Data</td>
                                        </tr>
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

@if (session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: "{{ session('success') }}",
        customClass: {
            confirmButton: 'btn btn-gradient-info me-3',
        },
    });
</script>
@endif

@if (session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: "{{ session('error') }}",
        customClass: {
            confirmButton: 'btn btn-gradient-danger me-3',
        },
    });
</script>
@endif


@if ($errors->any())
<script>
    Swal.fire({
        icon: 'error',
        title: 'Validasi Gagal',
        html: `{!! implode('<br>', $errors->all()) !!}`,
        customClass: {
            confirmButton: 'btn btn-gradient-danger me-3',
        },
    });
</script>
@endif
<div class="modal fade" id="shareEvaluatorModal" tabindex="-1" aria-labelledby="shareEvaluatorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content  border-0 rounded-4">
            <form action="{{ route('penilaian.shareForm') }}" method="post">
                @csrf

                <div class="modal-header bg-gradient-primary text-white rounded-top-4">
                    <h5 class="modal-title fw-bold" id="shareEvaluatorModalLabel">
                        <i class="mdi mdi-share-variant me-2"></i> Bagikan Formulir Penilaian
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body p-4">
                    <div id="modal-body-content"></div>
                    <div id="content_select_input"></div>
                    <input type="hidden" name="jenis_form" id="jenis_form" value="{{ $tipe }}">
                </div>

                <div class="modal-footer d-flex justify-content-between p-3">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">
                        <i class="mdi mdi-close me-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="mdi mdi-send me-1"></i> Kirim
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="evaluatorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content rounded">
            <div class="modal-header">
                <h5 class="modal-title">Daftar Evaluator</h5>
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="evaluatorModalContent">
            </div>
        </div>
    </div>
</div>

<style>
    .select2-container--default .select2-selection--multiple {
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        /* biar rounded */
        padding: 6px;
        min-height: 42px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        box-shadow: none !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #6c5ce7;
        /* ungu sesuai Purple Admin */
        border: none;
        border-radius: 0.4rem;
        color: #fff;
        padding: 3px 10px;
        margin-top: 4px;
        font-size: 0.85rem;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #fff;
        margin-right: 5px;
        font-weight: bold;
        cursor: pointer;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__rendered {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    /* Placeholder biar rapih */
    .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field {
        margin-top: 4px;
        font-size: 0.9rem;
        color: #6c757d;
    }

    /* Dropdown list */
    .select2-dropdown {
        border-radius: 0.5rem;
        border: 1px solid #dee2e6;
        box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.1);
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #6c5ce7;
        color: white;
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

    #table_karyawan td,
    #table_karyawan th {
        border: 1px solid #dee2e6 !important;
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

    #table_karyawan {
        width: 100% !important;
        font-size: 14px;
    }

    .dataTables_wrapper .dataTables_paginate {
        display: flex;
        flex-wrap: wrap;
        gap: .25rem;
        justify-content: center;
    }

    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info {
        text-align: left;
        margin-bottom: .5rem;
    }

    @media (max-width: 576px) {
        .dataTables_wrapper .dataTables_filter {
            width: 100%;
            text-align: center;
        }

        .dataTables_wrapper .dataTables_filter input {
            width: 100%;
            margin-top: .5rem;
        }
    }

    .table .dropdown-menu {
        position: absolute !important;
        z-index: 1055 !important;
        /* lebih tinggi dari modal dan elemen lain */
        transform: translate3d(0, 0, 0) !important;
    }

    .dataTables_wrapper {
        overflow: visible !important;
    }

    .table-responsive {
        overflow: visible !important;
    }
</style>
@endsection
@section('script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        const month = new Date().getMonth() + 1;
        let selectedQuarter = month <= 6 ? 'S1' : 'S2';
        $('#quartalSelectUtama').val(selectedQuarter);

        const tahunSelect = document.getElementById('tahunSelectUtama');
        const tahunSekarang = new Date().getFullYear();
        for (let tahun = 2020; tahun <= tahunSekarang; tahun++) {
            const option = document.createElement('option');
            option.value = tahun;
            option.text = tahun;
            if (tahun === tahunSekarang) option.selected = true;
            tahunSelect.appendChild(option);
        }

        $('#quartalSelectUtama, #tahunSelectUtama, #divisiSelectUtama, #jenis_form').on('change', function() {
            loadData();
        });

        loadData();
    });

    function loadData() {
        const selectedQuartal = $('#quartalSelectUtama').val();
        const selectedTahun = $('#tahunSelectUtama').val();
        const selectedDivisi = $('#divisiSelectUtama').val();
        const jenis_form = $('#jenis_form').val();

        $.ajax({
            url: "{{ route('penilaian.get.data') }}",
            type: 'get',
            data: {
                quartal: selectedQuartal,
                tahun: selectedTahun,
                divisi: selectedDivisi,
                jenis_form: jenis_form
            },
            beforeSend: function() {
                $('#loadingModal').modal('show');
            },
            success: function(response) {
                let data = response.data;

                if ($.fn.DataTable.isDataTable('#table_karyawan')) {
                    $('#table_karyawan').DataTable().destroy();
                }

                $('#table_karyawan').DataTable({
                    data: data.map((item, index) => {
                        let jenis = '';

                        if (item.jenis_penilaian === 'General Manager') {
                            jenis = 'J01P';
                        } else if (item.jenis_penilaian === 'Manager/SPV/Team Leader (Atasan Langsung)') {
                            jenis = 'J02P';
                        } else if (item.jenis_penilaian === 'Rekan Kerja (Satu Divisi)') {
                            jenis = 'J03P';
                        } else if (item.jenis_penilaian === 'Pekerja (Beda Divisi)') {
                            jenis = 'J04P';
                        } else if (item.jenis_penilaian === 'Self Apprisial') {
                            jenis = 'J05P';
                        } else {
                            jenis = 'not_found';
                        }

                        let button_evaluatorShow = (
                                item.evaluator_by_jenis &&
                                (typeof item.evaluator_by_jenis === 'object' && Object.keys(item.evaluator_by_jenis).length > 0)
                            ) ?
                            `<a href="javascript:void(0)" 
                                class="btn btn-sm btn-gradient-primary btn-show-evaluator" 
                                data-evaluator='${encodeURIComponent(JSON.stringify(item.evaluator_by_jenis))}'
                                data-kode-form="${item.kode_form}">...</a>` :
                            '';

                        return [
                            index + 1,
                            button_evaluatorShow,
                            item.evaluated,
                            item.evaluatedDivisi || '-',
                            item.tanggal,
                            item.kode_form_label,
                            item.quartal,
                            item.tahun,
                            `<div class="dropdown">
                                <button class="btn btn-light text-dark dropdown-toggle"
                                        type="button"
                                        data-bs-toggle="dropdown"
                                        data-bs-boundary="viewport" 
                                        aria-expanded="false">
                                Action
                                </button>

                                <div class="dropdown-menu">
                                    <a href="#" class="dropdown-item" data-kode="${item.kode_form}" data-id="${item.id_karyawan}" onclick="shareForm(this)" data-toggle="modal" data-target="#shareEvaluatorModal">
                                        <i class="fa-solid fa-paper-plane me-4"></i> Share
                                    </a>
                                    <a href="/penilaian/detail/data-penilaian/${item.kode_form}/${item.id_karyawan}/{{ $tipe }}" class="dropdown-item">
                                        <i class="fa-solid fa-magnifying-glass me-4"></i> Detail
                                    </a>
                                    <a href="javascript:void(0)" class="dropdown-item" id="btn-clean" data-kode_form="${item.kode_form}" data-id_karyawan="${item.id_karyawan}"  data-jenis_penilaian="${jenis}" data-quartal="${item.quartal}" data-tahun="${item.tahun}" data-jenis_form="{{ $tipe }}">
                                        <i class="fa-solid fa-brush me-4"></i> Bersihkan
                                    </a>
                                    <a href="javascript:void(0)" class="dropdown-item btn-hapus" data-kode_form="${item.kode_form}" data-id_karyawan="${item.id_karyawan}"  data-jenis_penilaian="${item.jenis_penilaian}" data-quartal="${item.quartal}" data-tahun="${item.tahun}" data-jenis_form="{{ $tipe }}">
                                        <i class="fa-solid fa-trash me-4"></i> Hapus
                                    </a>
                                </div>
                            </div>
                            `
                        ];
                    }),
                    pageLength: 10,
                    lengthMenu: [5, 10, 25, 50, 100],
                    responsive: true,
                    scrollX: true,
                    scrollCollapse: true,
                    dom: "<'row mb-2'<'col-md-6 custom-dt-length'l><'col-md-6 text-end custom-dt-search'f>>" +
                        "<'row'<'col-sm-12'tr>>" +
                        "<'row mt-2'<'col-md-5 custom-dt-info'i><'col-md-7 custom-dt-pagination'p>>",
                    language: {
                        search: "",
                        searchPlaceholder: "Cari data...",
                        lengthMenu: "_MENU_ per halaman",
                        info: "Menampilkan _START_ - _END_ dari _TOTAL_ entri",
                        infoEmpty: "Data Tidak Ditemukan",
                        paginate: {
                            first: "Awal",
                            last: "Akhir",
                            previous: "<",
                            next: ">"
                        },
                    },
                });
            },
            complete: function() {
                $('#loadingModal').modal('hide');
            }
        });
    }

    $(document).on('show.bs.dropdown', '.table .dropdown', function() {
        var $wrap = $(this);
        var $menu = $wrap.find('.dropdown-menu');

        var uid = 'dd-' + Math.random().toString(36).substr(2, 9);
        $wrap.attr('data-dd-origin', uid);
        $menu.attr('data-dd-origin', uid);

        $menu.appendTo('body');
    });

    $(document).on('hidden.bs.dropdown', '.table .dropdown', function() {
        var $wrap = $(this);
        var uid = $wrap.attr('data-dd-origin');
        if (!uid) return;

        var $menu = $('body').find('.dropdown-menu[data-dd-origin="' + uid + '"]');
        if ($menu.length) {
            $wrap.append($menu);
            $menu.removeAttr('data-dd-origin');
        }
        $wrap.removeAttr('data-dd-origin');
    });

    $(document).on('click', '.btn-hapus', function(e) {
        e.preventDefault();

        let kode_form = $(this).data('kode_form');
        let id_karyawan = $(this).data('id_karyawan');
        let jenis_penilaian = $(this).data('jenis_penilaian');
        let quartal = $(this).data('quartal');
        let tahun = $(this).data('tahun');
        let jenis_form = $(this).data('jenis_form');

        let formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        formData.append('kode_form', kode_form);
        formData.append('id_karyawan', id_karyawan);
        formData.append('jenis_penilaian', jenis_penilaian);
        formData.append('quartal', quartal);
        formData.append('tahun', tahun);
        formData.append('jenis_form', jenis_form);

        Swal.fire({
            title: 'Yakin ingin menghapus data?',
            text: "Data yang dihapus tidak bisa dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            customClass: {
                confirmButton: 'btn btn-gradient-info me-3',
                cancelButton: 'btn btn-gradient-danger'
            },
            confirmButtonText: 'Ya, hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/penilaian/hapus`,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Data berhasil dihapus.',
                            customClass: {
                                confirmButton: 'btn btn-gradient-info me-3',
                            },
                        });
                        loadData();
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan.',
                            customClass: {
                                confirmButton: 'btn btn-gradient-info me-3',
                            },
                        });
                    }
                });
            }
        });
    });

    $(document).on('click', '.btn-show-evaluator', function() {
        const rawEvaluator = $(this).attr('data-evaluator');
        const kodeFormGlobal = $(this).attr('data-kode-form');

        const evaluatorByJenis = JSON.parse(decodeURIComponent(rawEvaluator));

        const jenisPenilaianToKode = {
            'General Manager': 'JP01',
            'Manager/SPV/Team Leader (Atasan Langsung)': 'JP02',
            'Rekan Kerja (Satu Divisi)': 'JP03',
            'Pekerja (Beda Divisi)': 'JP04',
            'Self Appraisal': 'JP05',
            'Self Apprisial': 'JP05'
        };

        let html = '';
        Object.keys(evaluatorByJenis).forEach(function(jenis) {
            const kodeJenis = jenisPenilaianToKode[jenis] || jenis;

            html += `
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-light">
                <strong><i class="fa-solid fa-users me-2 text-primary"></i> ${jenis}</strong>
            </div>
            <div class="card-body">
                <div class="row">`;

            evaluatorByJenis[jenis].forEach(function(ev, index) {
                const badgeClass = ev.is_red ? 'bg-gradient-danger' : 'bg-gradient-success';
                html += `
            <div class="col-12 mb-2 p-2 card-rounded ${badgeClass} d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge me-2">${index + 1}</span> ${ev.name}
                </div>
                <button 
                    class="btn btn-sm btn-danger btn-action-hapus-evaluator"
                    data-jenis-penilaian="${kodeJenis}"
                    data-id-evaluator="${ev.id}"
                    data-kode-form="${kodeFormGlobal}">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>`;
            });

            html += `
                </div>
            </div>
        </div>`;
        });

        $('#evaluatorModalContent').html(html);
        $('#evaluatorModal').modal('show');
    });

    $(document).on('click', '.btn-action-hapus-evaluator', function() {
        const $button = $(this);
        const jenisPenilaian = $button.data('jenis-penilaian');
        const idEvaluator = $button.data('id-evaluator');
        const kodeForm = $button.data('kode-form');

        Swal.fire({
            title: 'Yakin hapus evaluator ini?',
            text: "Tindakan ini tidak bisa dibatalkan!",
            icon: 'warning',
            showCancelButton: true,
            customClass: {
                confirmButton: 'btn btn-gradient-info me-3',
                cancelButton: 'btn btn-gradient-danger'
            },
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/penilaian/hapus-evaluator/${jenisPenilaian}/${idEvaluator}/${kodeForm}`,
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Terhapus!',
                            text: response.message || 'Evaluator berhasil dihapus.',
                            icon: 'success',
                            customClass: {
                                confirmButton: 'btn btn-gradient-info'
                            },
                            buttonsStyling: false
                        });
                        
                        loadData();

                        $('#evaluatorModal').modal('hide');

                        if (window.activeEvaluatorButton) {
                            window.activeEvaluatorButton.click();
                        }
                    },
                    error: function(xhr) {
                        let msg = 'Gagal menghapus evaluator.';
                        if (xhr.responseJSON?.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire('Gagal!', msg, 'error');
                    }
                });
            }
        });
    });

    $(document).on('click', '#btn-clean', function(e) {
        e.preventDefault();

        let kode_form = $(this).data('kode_form');
        let id_karyawan = $(this).data('id_karyawan');
        let jenis_penilaian = $(this).data('jenis_penilaian');
        let quartal = $(this).data('quartal');
        let tahun = $(this).data('tahun');

        let formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        formData.append('kode_form', kode_form);
        formData.append('id_karyawan', id_karyawan);
        formData.append('jenis_penilaian', jenis_penilaian);
        formData.append('quartal', quartal);
        formData.append('tahun', tahun);

        Swal.fire({
            title: 'Yakin ingin membersihkan data?',
            text: "Data yang dibersihkan tidak bisa dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            customClass: {
                confirmButton: 'btn btn-gradient-info me-3',
                cancelButton: 'btn btn-gradient-danger'
            },
            confirmButtonText: 'Ya, bersihkan!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/penilaian/clean`,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Data berhasil dibersihkan.',
                            customClass: {
                                confirmButton: 'btn btn-gradient-info me-3',
                            },
                        });
                        loadData();
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan.',
                            customClass: {
                                confirmButton: 'btn btn-gradient-info me-3',
                            },
                        });
                    }
                });
            }
        });
    });

    function generateEvaluatorByPenilaian(item) {
        let evaluatorGroupedHTML = '';
        const evaluatorByJenis = item.evaluator_by_jenis || {};

        Object.entries(evaluatorByJenis).forEach(([jenis, evaluators]) => {
            evaluatorGroupedHTML += `– ${jenis}<br/>`;

            let namesHTML = evaluators.map(e => {
                let style = e.is_red ? 'color: red;' : '';
                let nameParts = e.name.split(" ");
                let limitedName = nameParts.slice(0, 2).join(" ");
                return `<span style="${style} ms-3">${limitedName}</span>`;
            }).join(', ');

            evaluatorGroupedHTML += `${namesHTML}<br/><br/>`;
        });

        return evaluatorGroupedHTML;
    }

    let allKaryawan = [];

    function shareForm(el) {
        const kodeForm = el.dataset.kode;
        const idKaryawan = el.dataset.id;

        const modalBody = $('#modal-body-content');
        const contentSelect = $('#content_select_input');

        modalBody.html(`
            <input type="hidden" value="${kodeForm}" name="kode_form">
            <input type="hidden" value="${idKaryawan}" name="id_evaluated">
        `);

        contentSelect.empty();

        $.ajax({
            url: "{{ route('penilaian.get.data') }}",
            type: 'GET',
            success: function(response) {
                const karyawan = response.karyawan;
                const divisiSet = new Set(karyawan.map(item => item.divisi).filter(Boolean));
                const gmList = karyawan.filter(item => item.jabatan === 'GM' && item.divisi === 'Sales & Marketing');

                const html = `
                    <label class="mt-4">Jenis Penilaian</label>
                    <select name="jenis_penilaian" class="form-select" required>
                        <option disabled selected>Pilih Jenis Penilaian</option>
                        <option value="General Manager">General Manager</option>
                        <option value="Manager/SPV/Team Leader (Atasan Langsung)">Manager/SPV/Team Leader (Atasan Langsung)</option>
                        <option value="Rekan Kerja (Satu Divisi)">Rekan Kerja (Satu Divisi)</option>
                        <option value="Pekerja (Beda Divisi)">Pekerja (Beda Divisi)</option>
                        <option value="Self Apprisial">Self Apprisial</option>
                    </select>

                    <label class="mt-3">Pilih Divisi</label>
                    <select id="multiple-select-field-divisi" name="divisi[]" multiple class="form-select"></select>

                    <label class="mt-3">Pilih Evaluator</label>
                    <select id="multiple-select-field-karyawan" name="id_karyawan[]" multiple class="form-select"></select>
                `;

                contentSelect.append(html);

                const $jenisPenilaianSelect = $('select[name="jenis_penilaian"]');
                const $divisiSelect = $('#multiple-select-field-divisi');
                const $evaluatorSelect = $('#multiple-select-field-karyawan');

                $divisiSelect.select2({
                    dropdownParent: $('#shareEvaluatorModal'),
                    width: '100%',
                    placeholder: 'Pilih Divisi',
                    closeOnSelect: false
                });

                $evaluatorSelect.select2({
                    dropdownParent: $('#shareEvaluatorModal'),
                    width: '100%',
                    placeholder: 'Pilih Evaluator',
                    closeOnSelect: false
                });

                function renderDivisiSelect(allowGm) {
                    $divisiSelect.empty();
                    const defaultDivisi = 'Sales & Marketing';

                    if (allowGm) {
                        const option = new Option(defaultDivisi, defaultDivisi, true, true);
                        $(option).attr('disabled', true);
                        $divisiSelect.append(option);

                        if (!$('#defaultDivisiInput').length) {
                            contentSelect.append(`<input type="hidden" name="divisi[]" value="${defaultDivisi}" id="defaultDivisiInput">`);
                        }
                    } else {
                        $('#defaultDivisiInput').remove();
                    }

                    divisiSet.forEach(divisi => {
                        if (!allowGm || divisi !== defaultDivisi) {
                            const opt = new Option(divisi, divisi);
                            $divisiSelect.append(opt);
                        }
                    });

                    $divisiSelect.trigger('change');
                }

                function updateEvaluatorOptions(selectedDivisi, allowGm = false) {
                    let filtered = karyawan.filter(item => selectedDivisi.includes(item.divisi));
                    modalBody.find('input[data-gm="true"]').remove();

                    if (allowGm) {
                        gmList.forEach(gm => {
                            if (!filtered.find(k => k.id === gm.id)) {
                                filtered.push(gm);
                            }
                            const gmHidden = `<input type="hidden" name="id_karyawan[]" value="${gm.id}" data-gm="true">`;
                            modalBody.append(gmHidden);
                        });
                    }

                    let options = '';
                    filtered.forEach(item => {
                        const isGM = gmList.find(gm => gm.id === item.id);
                        const isSelected = isGM && allowGm;
                        const isDisabled = isGM && allowGm;

                        options += `<option value="${item.id}" ${isSelected ? 'selected' : ''} ${isDisabled ? 'disabled' : ''}>
                        ${item.nama_lengkap} - ${item.divisi}
                    </option>`;
                    });

                    $evaluatorSelect.html(options).trigger('change');
                }

                $jenisPenilaianSelect.on('change', function() {
                    const jenis = $(this).val();
                    const allowGm = jenis === 'General Manager';
                    renderDivisiSelect(allowGm);
                    const selectedDivisi = $divisiSelect.val() || [];
                    const finalDivisi = allowGm ? [...new Set([...selectedDivisi, 'Sales & Marketing'])] : selectedDivisi;
                    updateEvaluatorOptions(finalDivisi, allowGm);
                });

                $divisiSelect.on('change', function() {
                    const selectedDivisi = $(this).val() || [];
                    const jenis = $jenisPenilaianSelect.val();
                    const allowGm = jenis === 'General Manager';
                    const finalDivisi = allowGm ? [...new Set([...selectedDivisi, 'Sales & Marketing'])] : selectedDivisi;
                    updateEvaluatorOptions(finalDivisi, allowGm);
                });

                renderDivisiSelect(false);
                updateEvaluatorOptions([], false);
            }
        });

        $('#shareEvaluatorModal').modal('show');
    }
</script>
@endsection