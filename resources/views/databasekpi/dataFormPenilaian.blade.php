@extends('databasekpi.berandaKPI')

@section('contentKPI')
<style>
    @media (max-width: 768px) {
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        #table_penilaian th,
        #table_penilaian td {
            font-size: 12px;
            padding: 6px;
            white-space: nowrap;
        }

        .custom-dt-length,
        .custom-dt-search {
            width: 100% !important;
            display: block;
            margin-bottom: 5px;
            text-align: left !important;
        }

        .custom-dt-search input {
            width: 100% !important;
        }

        .custom-dt-info,
        .custom-dt-pagination {
            width: 100%;
            display: block;
            text-align: center !important;
            margin-top: 5px;
        }

        .aksi-col .btn {
            padding: 4px 6px;
            font-size: 12px;
        }

        #modalEvaluated .modal-dialog {
            max-width: 90%;
            margin: auto;
        }
    }
</style>

<div class="content-wrapper">
    @if (session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: "{{ session('error') }}",
            confirmButtonColor: '#d33',
            customClass: {
                cancelButton: 'btn btn-gradient-danger'
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
            confirmButtonColor: '#d33',
            customClass: {
                cancelButton: 'btn btn-gradient-danger'
            },
        });
    </script>
    @endif
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
                    <span></span>Data Form <i class="mdi mdi-alert-circle-outline icon-sm text-primary align-middle" data-bs-toggle="tooltip" data-bs-placement="top" title="Rubah form yang mungkin anda salah buat. hati hati dengan form yang telah dikirim ke evaluator!"></i>
                </li>
            </ul>
        </nav>
    </div>
    <div class="row">
        <div class="col">
            <div class="card p-3">
                <div class="card-head">
                    <div class="card-title text-center fw-bold mt-3">Semua Form Penilaian
                    </div>
                    <div class="d-flex flex-wrap gap-3 mt-3">
                        <div class="flex-fill">
                            <label for="quartal" class="form-label mb-1">Quartal</label>
                            <select class="form-select form-select-sm" name="quartal" id="quartal">
                                <option value="Q1">Q1</option>
                                <option value="Q2">Q2</option>
                                <option value="Q3">Q3</option>
                                <option value="Q4">Q4</option>
                            </select>
                        </div>
                        <div class="flex-fill">
                            <label for="tahun" class="form-label mb-1">Tahun</label>
                            <select class="form-select form-select-sm" name="tahun" id="tahun">
                                <option value="">Pilih Tahun</option>
                                @for($i = now()->year; $i >= 2020; $i--)
                                <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive-mobile">
                        <table class="table table-bordered table-striped text-center align-middle bg-theme" id="table_penilaian">
                            <thead class="text-center bg-theme">
                                <tr>
                                    <th>No</th>
                                    <th>Kode Form</th>
                                    <th class="text-center">Evaluated</th>
                                    <th>Quartal</th>
                                    <th>Tahun</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="body_content">
                                <tr>
                                    <td colspan="6" class="text-center">Memuat data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalEvaluated" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Daftar Evaluated</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="evaluatedContent"></div>
        </div>
    </div>
</div>
@endsection

@section('script')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<style>
    #table_penilaian tbody tr .action-btn {
        display: none;
    }

    #table_penilaian tbody tr:hover .action-btn {
        display: inline-block;
    }

    .dataTables_length label select {
        width: 15vh !important;
    }

    .custom-dt-pagination .pagination .page-item.active .page-link {
        background: linear-gradient(to right, #da8cff, #9a55ff) !important;
        color: white !important;
        border: none !important;
        border-radius: 15% !important;
    }

    .custom-dt-pagination .pagination .page-item .page-link {
        background: transparent !important;
        color: black !important;
        border: none !important;
        margin-left: 5px !important;
        margin-right: 5px !important;
    }

    .custom-dt-pagination .pagination .page-item .page-link:hover {
        background: linear-gradient(to right, #d786fcff, #9652fcff) !important;
        border-radius: 15% !important;
        color: white !important;
    }

    .custom-dt-pagination .pagination .page-item.next .page-link:hover {
        background: linear-gradient(to right, #d786fcff, #9652fcff) !important;
        border-radius: 15% !important;
        color: white !important;
    }

    .custom-dt-pagination .pagination .page-item.previous .page-link:hover {
        background: linear-gradient(to right, #d786fcff, #9652fcff) !important;
        border-radius: 15% !important;
        color: white !important;
    }

    .dataTable thead tr .sorting::before,
    .dataTable thead tr .sorting::after {
        visibility: hidden !important;
        content: none !important;
    }

    @media only screen and (max-width:500px) {

        .dataTables_length label select {
            width: 100% !important;
            margin-bottom: 5px !important;
        }
    }

    .custom-dt-length,
    .custom-dt-search,
    .custom-dt-info,
    .custom-dt-pagination {
        position: sticky;
        background: #fff;
        z-index: 10;
    }

    .custom-dt-length,
    .custom-dt-search {
        top: 0;
    }

    .custom-dt-info,
    .custom-dt-pagination {
        bottom: 0;
    }

    #table_penilaian {
        width: 100% !important;
    }

    .dataTables_wrapper .dataTables_scroll {
        width: 100% !important;
    }

    @media (max-width: 576px) {
        .table-responsive-mobile {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-responsive-mobile table {
            min-width: 600px;
        }

        #table_penilaian th,
        #table_penilaian td {
            font-size: 14px;
            padding: 10px 8px;
        }

        .dataTables_length,
        .dataTables_filter,
        .dataTables_info,
        .dataTables_paginate {
            width: 100% !important;
        }

        .dataTables_filter input,
        .dataTables_length select {
            width: 100% !important;
        }

        .dataTables_info {
            text-align: center;
            font-size: 13px;
        }

        .dataTables_paginate .pagination {
            justify-content: center;
            flex-wrap: wrap;
        }
    }
</style>

<script>
    $(document).ready(function() {
        setDefaultQuartal();
        $("#tahun").val(new Date().getFullYear());
        loadData();
        $("#quartal, #tahun").on("change", function() {
            loadData();
        });
    });

    function setDefaultQuartal() {
        const month = new Date().getMonth() + 1;
        let quartal = "Q1";
        if (month >= 4 && month <= 6) quartal = "Q2";
        else if (month >= 7 && month <= 9) quartal = "Q3";
        else if (month >= 10 && month <= 12) quartal = "Q4";
        $("#quartal").val(quartal);
    }

    function loadData() {
        const quartal = $("#quartal").val();
        const tahun = $("#tahun").val();
        $.ajax({
            url: "{{ route('penilaian.form.get') }}",
            type: 'get',
            data: {
                quartal: quartal,
                tahun: tahun
            },
            success: function(response) {
                const data = response.data ?? [];
                const content = $('#body_content');
                content.empty();
                if (data.length === 0) {
                    content.append(`<tr><td colspan="6" class="text-center">Tidak ada Form!</td></tr>`);
                } else {
                    data.forEach(function(item, index) {
                        let evaluatedArr = [];
                        if (Array.isArray(item.evaluated)) {
                            evaluatedArr = item.evaluated.map(e => e.nama);
                        } else if (item.evaluated) {
                            evaluatedArr = [item.evaluated.nama];
                        }
                        let shortList = evaluatedArr.slice(0, 3).join(", ");
                        let moreLink = evaluatedArr.length > 3 ?
                            `<a href="javascript:void(0)" class="show-more button-view-all" data-full='${JSON.stringify(evaluatedArr)}'>
                                lihat semua
                            </a>` :
                            "";
                        content.append(`
                            <tr>
                                <td>${index+1}</td>
                                <td>${item.label_kode_form}</td>
                                <td>${shortList} ${moreLink}</td>
                                <td>${item.quartal}</td>
                                <td>${item.tahun}</td>
                                <td class="aksi-col text-center">
                                    <a class="btn btn-sm btn-gradient-warning text-black" href="/penilaian/data-form/edit/${item.kode_form}">
                                        <i class="mdi mdi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        `);
                    });
                    if ($.fn.DataTable.isDataTable('#table_penilaian')) {
                        $('#table_penilaian').DataTable().destroy();
                    }
                    $('#table_penilaian').DataTable({
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
                            infoEmpty: "Tidak ada data",
                            zeroRecords: "Data tidak ditemukan",
                            paginate: {
                                first: "Awal",
                                last: "Akhir",
                                next: "›",
                                previous: "‹"
                            }
                        }
                    });
                }
            }
        })
    }

    $(document).on("click", ".show-more", function() {
        const full = JSON.parse($(this).attr("data-full"));
        let listHtml = "<ol>";
        full.forEach(nama => {
            listHtml += `<li>${nama}</li>`;
        });
        listHtml += "</ol>";
        $("#evaluatedContent").html(listHtml);
        $("#modalEvaluated").modal("show");
    });
</script>
@endsection